<?php
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../app/models/User.php';
require_once __DIR__ . '/../app/models/Hostel.php';
require_once __DIR__ . '/../app/models/Room.php';
require_once __DIR__ . '/../app/models/Booking.php';
require_once __DIR__ . '/../app/models/Feedback.php';
require_once __DIR__ . '/../app/models/Transport.php';

requireAdmin();

$page_title = 'Admin Dashboard';
$database = new Database();
$db = $database->getConnection();

$userModel = new User($db);
$hostelModel = new Hostel($db);
$roomModel = new Room($db);
$bookingModel = new Booking($db);
$feedbackModel = new Feedback($db);
$transportModel = new Transport($db);

$totalUsers = $userModel->count();
$totalHostels = $hostelModel->count();
$totalRooms = $roomModel->count();
$totalBookings = $bookingModel->count();
$totalFeedback = $feedbackModel->count();
$totalTransport = $transportModel->count();

$allBookings = $bookingModel->getAll();
$allFeedback = $feedbackModel->getAll();

require_once __DIR__ . '/../includes/header.php';
?>

<div class="container-fluid">
    <div class="row mb-4">
        <div class="col-md-4 col-lg-2">
            <div class="stat-card primary">
                <h3><?php echo $totalUsers; ?></h3>
                <p>Total Users</p>
            </div>
        </div>
        <div class="col-md-4 col-lg-2">
            <div class="stat-card success">
                <h3><?php echo $totalHostels; ?></h3>
                <p>Hostels</p>
            </div>
        </div>
        <div class="col-md-4 col-lg-2">
            <div class="stat-card warning">
                <h3><?php echo $totalRooms; ?></h3>
                <p>Rooms</p>
            </div>
        </div>
        <div class="col-md-4 col-lg-2">
            <div class="stat-card info">
                <h3><?php echo $totalBookings; ?></h3>
                <p>Bookings</p>
            </div>
        </div>
        <div class="col-md-4 col-lg-2">
            <div class="stat-card danger">
                <h3><?php echo $totalFeedback; ?></h3>
                <p>Feedback</p>
            </div>
        </div>
        <div class="col-md-4 col-lg-2">
            <div class="stat-card primary" style="background: linear-gradient(135deg, #6f42c1 0%, #563d7c 100%);">
                <h3><?php echo $totalTransport; ?></h3>
                <p>Transport</p>
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0"><i class="fas fa-calendar-check"></i> Recent Bookings</h5>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-hover">
                            <thead>
                                <tr>
                                    <th>ID</th>
                                    <th>User</th>
                                    <th>Hostel</th>
                                    <th>Room</th>
                                    <th>Check In</th>
                                    <th>Check Out</th>
                                    <th>Amount</th>
                                    <th>Status</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach (array_slice($allBookings, 0, 10) as $booking): ?>
                                    <tr>
                                        <td>#<?php echo $booking['id']; ?></td>
                                        <td><?php echo htmlspecialchars($booking['user_name']); ?></td>
                                        <td><?php echo htmlspecialchars($booking['hostel_name']); ?></td>
                                        <td><?php echo htmlspecialchars($booking['room_number']); ?></td>
                                        <td><?php echo date('M d, Y', strtotime($booking['check_in'])); ?></td>
                                        <td><?php echo date('M d, Y', strtotime($booking['check_out'])); ?></td>
                                        <td>UGX <?php echo number_format($booking['total_amount'], 0); ?></td>
                                        <td>
                                            <?php
                                            $badgeClass = 'secondary';
                                            if ($booking['status'] == 'confirmed') $badgeClass = 'success';
                                            elseif ($booking['status'] == 'pending') $badgeClass = 'warning';
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
                </div>
            </div>
        </div>
    </div>

    <div class="row mt-4">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0"><i class="fas fa-comments"></i> Recent Feedback</h5>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-hover">
                            <thead>
                                <tr>
                                    <th>ID</th>
                                    <th>User</th>
                                    <th>Subject</th>
                                    <th>Type</th>
                                    <th>Message</th>
                                    <th>Date</th>
                                    <th>Status</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach (array_slice($allFeedback, 0, 10) as $feedback): ?>
                                    <tr>
                                        <td>#<?php echo $feedback['id']; ?></td>
                                        <td><?php echo htmlspecialchars($feedback['user_name']); ?></td>
                                        <td><?php echo htmlspecialchars($feedback['subject']); ?></td>
                                        <td>
                                            <?php
                                            $badgeClass = 'info';
                                            if ($feedback['type'] == 'complaint') $badgeClass = 'danger';
                                            elseif ($feedback['type'] == 'suggestion') $badgeClass = 'warning';
                                            ?>
                                            <span class="badge bg-<?php echo $badgeClass; ?>">
                                                <?php echo ucfirst($feedback['type']); ?>
                                            </span>
                                        </td>
                                        <td><?php echo htmlspecialchars(substr($feedback['message'], 0, 50)) . '...'; ?></td>
                                        <td><?php echo date('M d, Y', strtotime($feedback['created_at'])); ?></td>
                                        <td>
                                            <span class="badge bg-secondary">
                                                <?php echo ucfirst($feedback['status']); ?>
                                            </span>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
