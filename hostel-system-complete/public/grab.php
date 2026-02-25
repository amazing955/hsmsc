<?php
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../app/models/Product.php';

requireLogin();

$page_title = 'Grab Corner';
$database = new Database();
$db = $database->getConnection();
$productModel = new Product($db);

$products = $productModel->getAll();

require_once __DIR__ . '/../includes/header.php';
?>

<div class="container-fluid">
    <div class="row">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0"><i class="fas fa-shopping-cart"></i> Grab Corner - Mini Market</h5>
                </div>
                <div class="card-body">
                    <div class="alert alert-info">
                        <i class="fas fa-info-circle"></i> Order items and get them delivered to your hostel!
                    </div>

                    <?php if (count($products) > 0): ?>
                        <div class="row">
                            <?php foreach ($products as $product): ?>
                                <div class="col-md-6 col-lg-4 mb-4">
                                    <div class="card h-100">
                                        <div class="card-body">
                                            <div class="text-center mb-3">
                                                <i class="fas fa-box fa-4x text-primary"></i>
                                            </div>
                                            <h5 class="card-title"><?php echo htmlspecialchars($product['name']); ?></h5>
                                            <p class="card-text text-muted"><?php echo htmlspecialchars($product['description']); ?></p>
                                            <p class="card-text">
                                                <strong>Price:</strong> 
                                                <span class="text-success fw-bold">UGX <?php echo number_format($product['price'], 0); ?></span>
                                            </p>
                                            <p class="card-text">
                                                <small class="text-muted">
                                                    <i class="fas fa-box"></i> Stock: <?php echo $product['stock']; ?> available
                                                </small>
                                            </p>
                                            <button class="btn btn-primary w-100" onclick="addToCart(<?php echo $product['id']; ?>)">
                                                <i class="fas fa-cart-plus"></i> Add to Cart
                                            </button>
                                        </div>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    <?php else: ?>
                        <div class="text-center py-5">
                            <i class="fas fa-shopping-basket fa-4x text-muted mb-3"></i>
                            <p class="text-muted">No products available at the moment</p>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Floating Cart Button -->
<style>
#cartFab {
    position: fixed;
    right: 20px;
    bottom: 20px;
    z-index: 1050;
}
#cartFab .badge { position: absolute; top: -6px; right: -6px; }
</style>

<button id="cartFab" class="btn btn-warning btn-lg rounded-circle" onclick="openCartModal()" title="Open Cart">
    <i class="fas fa-shopping-cart"></i>
    <span id="cart-count-badge" class="badge bg-danger">0</span>
</button>

<!-- Cart Modal -->
<div class="modal fade" id="cartModal" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-lg">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title"><i class="fas fa-shopping-cart"></i> Your Cart</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
      </div>
      <div class="modal-body">
        <div id="cart-items-container">
            <p class="text-muted">Loading...</p>
        </div>
        <div id="cart-total" class="mt-3 text-end fw-bold"></div>
      </div>
      <div class="modal-footer">
        <button class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
        <button id="checkoutBtn" class="btn btn-success" onclick="checkout()" disabled>Checkout</button>
      </div>
    </div>
  </div>
</div>

<script>
// determine API URL relative to current page
const apiUrl = (function(){
    try {
        var base = window.location.pathname.replace(/\/[^\/]*$/, '/');
        return base + 'cart_action.php';
    } catch(e) {
        return 'cart_action.php';
    }
})();

function showToast(msg, success=true) {
    // very simple toast using alert for now
    if (success) {
        // update count and show small inline message
        console.log(msg);
    } else {
        console.error(msg);
    }
    // Optional: replace with nicer toast UI
}

function addToCart(productId) {
    fetch(apiUrl, {
        method: 'POST',
        credentials: 'same-origin',
        headers: {'Content-Type':'application/x-www-form-urlencoded'},
        body: new URLSearchParams({action:'add', product_id: productId, quantity: 1})
    })
    .then(r=>r.json())
    .then(json=>{
        if (json.success) {
            if (window.updateCartCount) window.updateCartCount(); else refreshCartCount();
            showToast(json.message, true);
        } else {
            showToast(json.message, false);
        }
    }).catch(e=>showToast('Request failed', false));
}

function refreshCartCount() {
    // keep local fallback (in case global update not available)
    fetch(apiUrl + '?action=get_count', { credentials: 'same-origin' })
        .then(r=>r.json())
        .then(json=>{
            const count = json.count || 0;
            const el = document.getElementById('cart-count-badge');
            if (el) el.textContent = count;
            if (window.updateCartCount) window.updateCartCount();
        }).catch(function(){
            if (window.updateCartCount) window.updateCartCount();
        });
}

function openCartModal() {
    const modalEl = new bootstrap.Modal(document.getElementById('cartModal'));
    modalEl.show();
    loadCartItems();
}

function loadCartItems() {
    const container = document.getElementById('cart-items-container');
    container.innerHTML = '<p class="text-muted">Loading...</p>';
    fetch(apiUrl + '?action=get', { credentials: 'same-origin' })
        .then(r=>r.json())
        .then(json=>{
            if (!json.success) {
                container.innerHTML = '<p class="text-danger">Failed to load cart</p>';
                return;
            }
            const items = json.items || [];
            if (items.length === 0) {
                container.innerHTML = '<p class="text-muted">Cart is empty</p>';
                document.getElementById('cart-total').textContent = '';
                document.getElementById('checkoutBtn').disabled = true;
                return;
            }
            let html = '<div class="list-group">';
            items.forEach(it=>{
                html += `
                    <div class="list-group-item d-flex align-items-center">
                        <div>
                            <strong>${escapeHtml(it.name)}</strong><br>
                            <small class="text-muted">UGX ${numberFormat(it.price,0)} each</small>
                        </div>
                        <div class="ms-auto d-flex align-items-center">
                            <input type="number" min="1" max="${it.stock}" value="${it.quantity}" style="width:80px" onchange="updateQty(${it.id}, this.value)" class="form-control form-control-sm me-2">
                            <button class="btn btn-sm btn-danger" onclick="removeFromCart(${it.id})">Remove</button>
                        </div>
                    </div>
                `;
            });
            html += '</div>';
            container.innerHTML = html;
            document.getElementById('cart-total').textContent = 'Total: UGX ' + numberFormat(json.total,0);
            document.getElementById('checkoutBtn').disabled = false;
        });
}

function removeFromCart(pid) {
    fetch(apiUrl, {
        method:'POST',
        credentials: 'same-origin',
        headers:{'Content-Type':'application/x-www-form-urlencoded'},
        body: new URLSearchParams({action:'remove', product_id: pid})
    }).then(r=>r.json()).then(json=>{
        if (json.success) {
            if (window.updateCartCount) window.updateCartCount(); else refreshCartCount();
            loadCartItems();
            showToast(json.message, true);
        } else {
            showToast(json.message, false);
        }
    });
}

function updateQty(pid, qty) {
    qty = parseInt(qty) || 0;
    if (qty <= 0) {
        removeFromCart(pid);
        return;
    }
    fetch(apiUrl, {
        method:'POST',
        credentials: 'same-origin',
        headers:{'Content-Type':'application/x-www-form-urlencoded'},
        body: new URLSearchParams({action:'update', product_id: pid, quantity: qty})
    }).then(r=>r.json()).then(json=>{
        if (json.success) {
            if (window.updateCartCount) window.updateCartCount(); else refreshCartCount();
            loadCartItems();
        } else {
            showToast(json.message, false);
        }
    });
}

function checkout() {
    if (!confirm('Proceed to checkout?')) return;
    document.getElementById('checkoutBtn').disabled = true;
    fetch(apiUrl, {
        method:'POST',
        credentials: 'same-origin',
        headers:{'Content-Type':'application/x-www-form-urlencoded'},
        body: new URLSearchParams({action:'checkout'})
    }).then(r=>r.json()).then(json=>{
        if (json.success) {
            if (window.updateCartCount) window.updateCartCount(); else refreshCartCount();
            loadCartItems();
            showToast(json.message, true);
            // optionally redirect to order page
            // window.location.href = 'orders.php?id=' + json.order_id;
        } else {
            showToast(json.message, false);
            document.getElementById('checkoutBtn').disabled = false;
        }
    }).catch(err=>{
        showToast('Checkout failed', false);
        document.getElementById('checkoutBtn').disabled = false;
    });
}

// helpers
function escapeHtml(s){ if(!s) return ''; return String(s).replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;').replace(/"/g,'&quot;'); }
function numberFormat(n, d){ return new Intl.NumberFormat().format(n); }

// initialize count on page load
document.addEventListener('DOMContentLoaded', function(){ 
    if (window.updateCartCount) window.updateCartCount(); else refreshCartCount();
});
</script>
<?php require_once __DIR__ . '/../includes/footer.php'; ?>
