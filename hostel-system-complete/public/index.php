<?php
require_once __DIR__ . '/../config/config.php';

if (isLoggedIn()) {
    // use relative path so redirect works when the project is served from a subfolder
    header('Location: dashboard.php');
    exit();
}

$page_title = 'Welcome';
require_once __DIR__ . '/../includes/header.php';
?>

<div class="container">
    <div class="row justify-content-center mt-5">
        <div class="col-md-10">
            <div class="card">
                <div class="card-body text-center p-5">
                    <h1 class="display-4 mb-4">
                        <i class="fas fa-hotel text-primary"></i>
                        Welcome to Hostel Management System
                    </h1>
                    <p class="lead mb-4">Your one-stop solution for hostel accommodation, transport, and services</p>
                    
                    <div class="row mt-5">
                        <div class="col-md-4 mb-3">
                            <div class="card h-100 border-primary">
                                <div class="card-body">
                                    <i class="fas fa-search-location fa-3x text-primary mb-3"></i>
                                    <h5>Find Hostels</h5>
                                    <p>Search and discover nearby hostels with our interactive map</p>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-4 mb-3">
                            <div class="card h-100 border-success">
                                <div class="card-body">
                                    <i class="fas fa-bed fa-3x text-success mb-3"></i>
                                    <h5>Book Rooms</h5>
                                    <p>Easy room booking system with instant confirmation</p>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-4 mb-3">
                            <div class="card h-100 border-info">
                                <div class="card-body">
                                    <i class="fas fa-motorcycle fa-3x text-info mb-3"></i>
                                    <h5>Transportation</h5>
                                    <p>Quick and reliable hostel boda services</p>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <div class="mt-5">
                        <a href="login.php" class="btn btn-primary btn-lg me-3">
                            <i class="fas fa-sign-in-alt"></i> Login
                        </a>
                        <a href="register.php" class="btn btn-outline-primary btn-lg">
                            <i class="fas fa-user-plus"></i> Register
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
