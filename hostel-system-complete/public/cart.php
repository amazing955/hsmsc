<?php
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../config/database.php';
requireLogin();

$page_title = 'My Cart';
$database = new Database();
$db = $database->getConnection();

$cart = $_SESSION['cart'] ?? [];
$items = [];
$total = 0;
if (!empty($cart)) {
    $ids = array_keys($cart);
    $placeholders = implode(',', array_fill(0, count($ids), '?'));
    $stmt = $db->prepare("SELECT id, name, price, stock FROM products WHERE id IN ($placeholders)");
    foreach ($ids as $i => $id) $stmt->bindValue($i+1, $id, PDO::PARAM_INT);
    $stmt->execute();
    $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
    foreach ($rows as $r) {
        $qty = $cart[$r['id']] ?? 0;
        $amount = $r['price'] * $qty;
        $items[] = ['id'=>$r['id'],'name'=>$r['name'],'price'=>$r['price'],'stock'=>$r['stock'],'qty'=>$qty,'amount'=>$amount];
        $total += $amount;
    }
}

require_once __DIR__ . '/../includes/header.php';
?>

<div class="container">
    <h3 class="mt-4">My Cart</h3>

    <?php if (empty($items)): ?>
        <div class="alert alert-info">Your cart is empty. <a href="grab.php">Browse products</a></div>
    <?php else: ?>
        <div class="table-responsive">
            <table class="table">
                <thead><tr><th>Product</th><th>Price</th><th>Qty</th><th>Amount</th></tr></thead>
                <tbody>
                    <?php foreach ($items as $it): ?>
                    <tr>
                        <td><?php echo htmlspecialchars($it['name']); ?></td>
                        <td>UGX <?php echo number_format($it['price'],0); ?></td>
                        <td><?php echo intval($it['qty']); ?></td>
                        <td>UGX <?php echo number_format($it['amount'],0); ?></td>
                    </tr>
                    <?php endforeach; ?>
                    <tr><td colspan="3" class="text-end"><strong>Total</strong></td><td><strong>UGX <?php echo number_format($total,0); ?></strong></td></tr>
                </tbody>
            </table>
        </div>

        <div class="d-flex gap-2">
            <a href="grab.php" class="btn btn-secondary">Continue Shopping</a>
            <button id="checkoutBtn" class="btn btn-success" onclick="doCheckout()">Checkout</button>
        </div>
    <?php endif; ?>
</div>

<script>
function doCheckout(){
    if (!confirm('Proceed to checkout?')) return;
    fetch('cart_action.php', {
        method:'POST',
        headers:{'Content-Type':'application/x-www-form-urlencoded'},
        body: new URLSearchParams({action:'checkout'})
    }).then(r=>r.json()).then(json=>{
        if (json.success) {
            alert(json.message);
            window.location.href = 'grab.php';
        } else {
            alert('Checkout failed: ' + json.message);
        }
    }).catch(()=> alert('Checkout failed'));
}
</script>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
