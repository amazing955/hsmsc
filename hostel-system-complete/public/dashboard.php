<?php
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../app/models/Booking.php';

requireLogin();

// Redirect business owners to their dashboards
if (isBusinessOwner()) {
    header('Location: owner-dashboard.php');
    exit();
} elseif (isAdmin()) {
    header('Location: admin.php');
    exit();
}

$page_title = 'Dashboard';
$database = new Database();
$db = $database->getConnection();
$bookingModel = new Booking($db);

$userBookings = $bookingModel->getByUser($_SESSION['user_id']);

require_once __DIR__ . '/../includes/header.php';
?>

<div class="container-fluid">
    <div class="row mb-4">
        <div class="col-md-4">
            <div class="stat-card primary">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <p class="mb-0">My Bookings</p>
                        <h3><?php echo count($userBookings); ?></h3>
                    </div>
                    <i class="fas fa-bed fa-3x" style="opacity: 0.3;"></i>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="stat-card success">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <p class="mb-0">Profile Status</p>
                        <h3>Active</h3>
                    </div>
                    <i class="fas fa-user-check fa-3x" style="opacity: 0.3;"></i>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="stat-card info">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <p class="mb-0">Account Type</p>
                        <h3><?php echo ucfirst($_SESSION['role']); ?></h3>
                    </div>
                    <i class="fas fa-id-card fa-3x" style="opacity: 0.3;"></i>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0"><i class="fas fa-calendar-alt"></i> My Bookings</h5>
                </div>
                <div class="card-body">
                    <?php if (count($userBookings) > 0): ?>
                        <div class="table-responsive">
                            <table class="table table-hover">
                                <thead>
                                    <tr>
                                        <th>Hostel</th>
                                        <th>Room</th>
                                        <th>Check In</th>
                                        <th>Check Out</th>
                                        <th>Amount</th>
                                        <th>Status</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($userBookings as $booking): ?>
                                        <tr>
                                            <td><?php echo htmlspecialchars($booking['hostel_name']); ?></td>
                                            <td><?php echo htmlspecialchars($booking['room_number'] . ' - ' . $booking['room_type']); ?></td>
                                            <td><?php echo date('M d, Y', strtotime($booking['check_in'])); ?></td>
                                            <td><?php echo date('M d, Y', strtotime($booking['check_out'])); ?></td>
                                            <td>UGX <?php echo number_format($booking['total_amount'], 0); ?></td>
                                            <td>
                                                <?php
                                                $badgeClass = 'secondary';
                                                if ($booking['status'] == 'confirmed') $badgeClass = 'success';
                                                elseif ($booking['status'] == 'pending') $badgeClass = 'warning';
                                                elseif ($booking['status'] == 'cancelled') $badgeClass = 'danger';
                                                ?>
                                                <span class="badge bg-<?php echo $badgeClass; ?>">
                                                    <?php echo ucfirst($booking['status']); ?>
                                                </span>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    <?php else: ?>
                        <div class="text-center py-5">
                            <i class="fas fa-inbox fa-4x text-muted mb-3"></i>
                            <p class="text-muted">You don't have any bookings yet</p>
                            <a href="booking.php" class="btn btn-primary">
                                <i class="fas fa-plus"></i> Make a Booking
                            </a>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>

    <div class="row mt-4">
        <div class="col-md-6">
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0"><i class="fas fa-th"></i> Quick Actions</h5>
                </div>
                <div class="card-body">
                    <div class="d-grid gap-2">
                        <a href="hostels.php" class="btn btn-outline-primary">
                            <i class="fas fa-search-location"></i> Find Hostels
                        </a>
                        <a href="booking.php" class="btn btn-outline-success">
                            <i class="fas fa-bed"></i> Book a Room
                        </a>
                        <a href="transport.php" class="btn btn-outline-info">
                            <i class="fas fa-motorcycle"></i> Request Transport
                        </a>
                        <a href="grab.php" class="btn btn-outline-warning">
                            <i class="fas fa-shopping-cart"></i> Visit Grab Corner
                        </a>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-6">
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0"><i class="fas fa-info-circle"></i> Account Information</h5>
                </div>
                <div class="card-body">
                    <p><strong>Name:</strong> <?php echo htmlspecialchars($_SESSION['user_name']); ?></p>
                    <p><strong>Email:</strong> <?php echo htmlspecialchars($_SESSION['user_email']); ?></p>
                    <p><strong>Role:</strong> <?php echo ucfirst($_SESSION['role']); ?></p>
                    <a href="feedback.php" class="btn btn-primary mt-2">
                        <i class="fas fa-comment"></i> Send Feedback
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
