<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo isset($page_title) ? $page_title . ' - ' : ''; ?><?php echo SITE_NAME; ?></title>
    
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="css/style.css">
</head>
<body>
    <div class="wrapper">
        <nav id="sidebar">
            <div class="sidebar-header">
                <h3><i class="fas fa-hotel"></i> <span>HMS</span></h3>
            </div>
            <ul class="list-unstyled components">
                <?php if (isLoggedIn()): ?>
                    <li>
                        <a href="dashboard.php" <?php echo (basename($_SERVER['PHP_SELF']) == 'dashboard.php') ? 'class="active"' : ''; ?>>
                            <i class="fas fa-home"></i> <span>Dashboard</span>
                        </a>
                    </li>
                    <li>
                        <a href="hostels.php" <?php echo (basename($_SERVER['PHP_SELF']) == 'hostels.php') ? 'class="active"' : ''; ?>>
                            <i class="fas fa-search-location"></i> <span>Find Hostels</span>
                        </a>
                    </li>
                    <li>
                        <a href="booking.php" <?php echo (basename($_SERVER['PHP_SELF']) == 'booking.php') ? 'class="active"' : ''; ?>>
                            <i class="fas fa-bed"></i> <span>Book Room</span>
                        </a>
                    </li>
                    <li>
                        <a href="transport.php" <?php echo (basename($_SERVER['PHP_SELF']) == 'transport.php') ? 'class="active"' : ''; ?>>
                            <i class="fas fa-motorcycle"></i> <span>Transport</span>
                        </a>
                    </li>
                    <li>
                        <a href="grab.php" <?php echo (basename($_SERVER['PHP_SELF']) == 'grab.php') ? 'class="active"' : ''; ?>>
                            <i class="fas fa-shopping-cart"></i> <span>Grab Corner</span>
                        </a>
                    </li>
                    <li>
                        <a href="feedback.php" <?php echo (basename($_SERVER['PHP_SELF']) == 'feedback.php') ? 'class="active"' : ''; ?>>
                            <i class="fas fa-comment"></i> <span>Feedback</span>
                        </a>
                    </li>
                    <?php if (isAdmin()): ?>
                    <li>
                        <a href="admin.php" <?php echo (basename($_SERVER['PHP_SELF']) == 'admin.php') ? 'class="active"' : ''; ?>>
                            <i class="fas fa-user-shield"></i> <span>Admin Panel</span>
                        </a>
                    </li>
                    <?php elseif (isBusinessOwner()): ?>
                    <li>
                        <a href="owner-dashboard.php" <?php echo (basename($_SERVER['PHP_SELF']) == 'owner-dashboard.php') ? 'class="active"' : ''; ?>>
                            <i class="fas fa-chart-line"></i> <span>Business Dashboard</span>
                        </a>
                    </li>
                    <?php if (isHostelOwner()): ?>
                    <li>
                        <a href="hostel-management.php" <?php echo (basename($_SERVER['PHP_SELF']) == 'hostel-management.php') ? 'class="active"' : ''; ?>>
                            <i class="fas fa-building"></i> <span>Manage Hostels</span>
                        </a>
                    </li>
                    <li>
                        <a href="room-management.php" <?php echo (basename($_SERVER['PHP_SELF']) == 'room-management.php') ? 'class="active"' : ''; ?>>
                            <i class="fas fa-door-open"></i> <span>Manage Rooms</span>
                        </a>
                    </li>
                    <?php endif; ?>
                    <?php if (isBodarider()): ?>
                    <li>
                        <a href="rider-profile.php" <?php echo (basename($_SERVER['PHP_SELF']) == 'rider-profile.php') ? 'class="active"' : ''; ?>>
                            <i class="fas fa-motorcycle"></i> <span>My Profile</span>
                        </a>
                    </li>
                    <li>
                        <a href="rides.php" <?php echo (basename($_SERVER['PHP_SELF']) == 'rides.php') ? 'class="active"' : ''; ?>>
                            <i class="fas fa-tasks"></i> <span>My Rides</span>
                        </a>
                    </li>
                    <?php endif; ?>
                    <?php endif; ?>
                    <li>
                        <a href="logout.php">
                            <i class="fas fa-sign-out-alt"></i> <span>Logout</span>
                        </a>
                    </li>
                <?php else: ?>
                    <li>
                        <a href="index.php" <?php echo (basename($_SERVER['PHP_SELF']) == 'index.php') ? 'class="active"' : ''; ?>>
                            <i class="fas fa-home"></i> <span>Home</span>
                        </a>
                    </li>
                    <li>
                        <a href="login.php" <?php echo (basename($_SERVER['PHP_SELF']) == 'login.php') ? 'class="active"' : ''; ?>>
                            <i class="fas fa-sign-in-alt"></i> <span>Login</span>
                        </a>
                    </li>
                    <li>
                        <a href="register.php" <?php echo (basename($_SERVER['PHP_SELF']) == 'register.php') ? 'class="active"' : ''; ?>>
                            <i class="fas fa-user-plus"></i> <span>Register</span>
                        </a>
                    </li>
                <?php endif; ?>
            </ul>
        </nav>

        <div id="content">
            <nav class="top-navbar">
                <div class="d-flex justify-content-between align-items-center">
                    <h4 class="mb-0"><?php echo isset($page_title) ? htmlspecialchars($page_title) : 'Dashboard'; ?></h4>
                    <?php if (isLoggedIn()): ?>
                        <div>
                            <span class="me-3">
                                <i class="fas fa-user-circle"></i>
                                <?php echo htmlspecialchars($_SESSION['user_name']); ?>
                                <?php if (isAdmin()): ?>
                                    <span class="badge bg-danger ms-2">Admin</span>
                                <?php elseif (function_exists('isHostelOwner') && isHostelOwner()): ?>
                                    <span class="badge bg-info ms-2">Hostel Owner</span>
                                <?php endif; ?>
                            </span>

                            <!-- Cart link / count -->
                            <a href="cart.php" class="btn btn-sm btn-outline-primary ms-2" title="View Cart">
                                <i class="fas fa-shopping-cart"></i>
                                <span id="header-cart-count" class="badge bg-danger ms-1"><?php echo isset($_SESSION['cart']) ? array_sum($_SESSION['cart']) : 0; ?></span>
                            </a>
                        </div>
                    <?php endif; ?>
                </div>
            </nav>

            <div class="main-content">
                <script>
                    // update cart count in header and any floating cart badge
                    window.updateCartCount = function() {
                        // derive cart_action.php path relative to current page directory
                        try {
                            var base = window.location.pathname.replace(/\/[^\/]*$/, '/');
                            var url = base + 'cart_action.php?action=get_count';
                            // fallback to root path if not found
                            fetch(url, { credentials: 'same-origin' })
                                .then(function(res){ return res.json(); })
                                .then(function(json){
                                    if (!json || !json.success) {
                                        // try root fallback
                                        return fetch('/cart_action.php?action=get_count', { credentials: 'same-origin' })
                                            .then(function(r){ return r.json(); })
                                            .then(function(j){ return j; });
                                    }
                                    return json;
                                })
                                .then(function(finalJson){
                                    if (!finalJson || !finalJson.success) return;
                                    var count = parseInt(finalJson.count) || 0;
                                    var headerEl = document.getElementById('header-cart-count');
                                    if (headerEl) headerEl.textContent = count;
                                    var fabEl = document.getElementById('cart-count-badge');
                                    if (fabEl) fabEl.textContent = count;
                                })
                                .catch(function(){
                                    // silent
                                });
                        } catch (e) { /* silent */ }
                    };

                    // initialize on load so header shows current count after any AJAX activity
                    document.addEventListener('DOMContentLoaded', function(){
                        window.updateCartCount();
                    });
                </script>
