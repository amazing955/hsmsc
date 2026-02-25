<?php
session_start();
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../config/database.php';

header('Content-Type: application/json');

if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'message' => 'Not authenticated']);
    exit;
}

$database = new Database();
$db = $database->getConnection();

$action = $_POST['action'] ?? $_GET['action'] ?? '';

if (!isset($_SESSION['cart']) || !is_array($_SESSION['cart'])) {
    $_SESSION['cart'] = [];
}

function jsonResponse($data) {
    echo json_encode($data);
    exit;
}

switch ($action) {
    case 'add':
        $productId = intval($_POST['product_id'] ?? 0);
        $qty = max(1, intval($_POST['quantity'] ?? 1));
        if ($productId <= 0) jsonResponse(['success' => false, 'message' => 'Invalid product']);
        // check stock
        $p = $db->prepare("SELECT id, stock FROM products WHERE id = :id LIMIT 1");
        $p->bindParam(':id', $productId, PDO::PARAM_INT);
        $p->execute();
        $prod = $p->fetch(PDO::FETCH_ASSOC);
        if (!$prod) jsonResponse(['success' => false, 'message' => 'Product not found']);
        $currentQty = $_SESSION['cart'][$productId] ?? 0;
        if ($prod['stock'] < $currentQty + $qty) {
            jsonResponse(['success' => false, 'message' => 'Insufficient stock']);
        }
        $_SESSION['cart'][$productId] = $currentQty + $qty;
        jsonResponse(['success' => true, 'message' => 'Added to cart', 'count' => array_sum($_SESSION['cart'])]);
        break;

    case 'remove':
        $productId = intval($_POST['product_id'] ?? 0);
        if ($productId <= 0) jsonResponse(['success' => false, 'message' => 'Invalid product']);
        if (isset($_SESSION['cart'][$productId])) unset($_SESSION['cart'][$productId]);
        jsonResponse(['success' => true, 'message' => 'Removed', 'count' => array_sum($_SESSION['cart'])]);
        break;

    case 'update':
        $productId = intval($_POST['product_id'] ?? 0);
        $qty = max(0, intval($_POST['quantity'] ?? 0));
        if ($productId <= 0) jsonResponse(['success' => false, 'message' => 'Invalid product']);
        if ($qty === 0) {
            if (isset($_SESSION['cart'][$productId])) unset($_SESSION['cart'][$productId]);
            jsonResponse(['success' => true, 'message' => 'Removed', 'count' => array_sum($_SESSION['cart'])]);
        }
        // check stock
        $p = $db->prepare("SELECT id, stock FROM products WHERE id = :id LIMIT 1");
        $p->bindParam(':id', $productId, PDO::PARAM_INT);
        $p->execute();
        $prod = $p->fetch(PDO::FETCH_ASSOC);
        if (!$prod) jsonResponse(['success' => false, 'message' => 'Product not found']);
        if ($prod['stock'] < $qty) jsonResponse(['success' => false, 'message' => 'Insufficient stock']);
        $_SESSION['cart'][$productId] = $qty;
        jsonResponse(['success' => true, 'message' => 'Updated', 'count' => array_sum($_SESSION['cart'])]);
        break;

    case 'get_count':
        jsonResponse(['success' => true, 'count' => array_sum($_SESSION['cart'])]);
        break;

    case 'get':
        $cart = $_SESSION['cart'];
        if (empty($cart)) jsonResponse(['success' => true, 'items' => [], 'total' => 0]);
        $ids = array_keys($cart);
        $placeholders = implode(',', array_fill(0, count($ids), '?'));
        $stmt = $db->prepare("SELECT id, name, price, stock FROM products WHERE id IN ($placeholders)");
        foreach ($ids as $i => $id) $stmt->bindValue($i+1, $id, PDO::PARAM_INT);
        $stmt->execute();
        $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
        $items = [];
        $total = 0;
        foreach ($rows as $r) {
            $pid = $r['id'];
            $quantity = $cart[$pid] ?? 0;
            $amount = $r['price'] * $quantity;
            $items[] = [
                'id' => $pid,
                'name' => $r['name'],
                'price' => $r['price'],
                'stock' => $r['stock'],
                'quantity' => $quantity,
                'amount' => $amount
            ];
            $total += $amount;
        }
        jsonResponse(['success' => true, 'items' => $items, 'total' => $total]);
        break;

    case 'clear':
        $_SESSION['cart'] = [];
        jsonResponse(['success' => true, 'message' => 'Cart cleared', 'count' => 0]);
        break;

    case 'checkout':
        $cart = $_SESSION['cart'];
        if (empty($cart)) jsonResponse(['success' => false, 'message' => 'Cart is empty']);
        // fetch product details and validate stock
        $ids = array_keys($cart);
        $placeholders = implode(',', array_fill(0, count($ids), '?'));
        $stmt = $db->prepare("SELECT id, name, price, stock FROM products WHERE id IN ($placeholders) FOR UPDATE");
        $db->beginTransaction();
        try {
            foreach ($ids as $i => $id) $stmt->bindValue($i+1, $id, PDO::PARAM_INT);
            $stmt->execute();
            $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
            $total = 0;
            $priceMap = [];
            $stockMap = [];
            foreach ($rows as $r) {
                $priceMap[$r['id']] = $r['price'];
                $stockMap[$r['id']] = $r['stock'];
            }
            // validate stock
            foreach ($cart as $pid => $qty) {
                if (!isset($stockMap[$pid]) || $stockMap[$pid] < $qty) {
                    $db->rollBack();
                    jsonResponse(['success' => false, 'message' => 'Insufficient stock for product ID ' . intval($pid)]);
                }
                $total += $priceMap[$pid] * $qty;
            }
            // create order
            $orderStmt = $db->prepare("INSERT INTO orders (user_id, total_amount, status, created_at) VALUES (:uid, :total, 'pending', NOW())");
            $orderStmt->bindParam(':uid', $_SESSION['user_id'], PDO::PARAM_INT);
            $orderStmt->bindParam(':total', $total);
            $orderStmt->execute();
            $orderId = $db->lastInsertId();

            // insert order_items and decrement stock
            $oiStmt = $db->prepare("INSERT INTO order_items (`order_id`, `product_id`, `quantity`, `price`) VALUES (:oid, :pid, :qty, :price)");
            $updStock = $db->prepare("UPDATE products SET stock = stock - :qty WHERE id = :pid");
            foreach ($cart as $pid => $qty) {
                $price = $priceMap[$pid];
                $oiStmt->bindParam(':oid', $orderId, PDO::PARAM_INT);
                $oiStmt->bindParam(':pid', $pid, PDO::PARAM_INT);
                $oiStmt->bindParam(':qty', $qty, PDO::PARAM_INT);
                $oiStmt->bindParam(':price', $price);
                $oiStmt->execute();
                $updStock->bindParam(':qty', $qty, PDO::PARAM_INT);
                $updStock->bindParam(':pid', $pid, PDO::PARAM_INT);
                $updStock->execute();
            }

            $db->commit();
            // clear cart
            $_SESSION['cart'] = [];
            jsonResponse(['success' => true, 'message' => 'Order placed successfully', 'order_id' => $orderId]);
        } catch (Exception $e) {
            if ($db->inTransaction()) $db->rollBack();
            jsonResponse(['success' => false, 'message' => 'Checkout failed: ' . $e->getMessage()]);
        }
        break;

    default:
        jsonResponse(['success' => false, 'message' => 'Unknown action']);
}
