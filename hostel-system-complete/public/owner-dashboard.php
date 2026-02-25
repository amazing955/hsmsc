<?php
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../app/models/Hostel.php';
require_once __DIR__ . '/../app/models/Room.php';
require_once __DIR__ . '/../app/models/Booking.php';
require_once __DIR__ . '/../app/models/Product.php';
require_once __DIR__ . '/../app/models/Rider.php';

requireBusinessOwner();

$page_title = 'Business Dashboard';
$database = new Database();
$db = $database->getConnection();

$stats = [];

if (isHostelOwner()) {
    $hostelModel = new Hostel($db);
    $roomModel = new Room($db);
    $bookingModel = new Booking($db);
    
    $hostels = $hostelModel->getByOwner($_SESSION['user_id']);
    $stats['hostels'] = count($hostels);
    $stats['rooms'] = $roomModel->countByOwner($_SESSION['user_id']);
    
    $hostel_ids = array_column($hostels, 'id');
    if (!empty($hostel_ids)) {
        $bookings = $bookingModel->getAll();
        $owned_bookings = array_filter($bookings, function($b) use ($hostel_ids) {
            return in_array($b['hostel_id'] ?? 0, $hostel_ids);
        });
        $stats['bookings'] = count($owned_bookings);
        $stats['revenue'] = array_sum(array_column($owned_bookings, 'total_amount'));
    } else {
        $stats['bookings'] = 0;
        $stats['revenue'] = 0;
    }
} elseif (isHotelOwner()) {
    $stats['hotels'] = 1;
    $stats['rooms'] = 0;
    $stats['bookings'] = 0;
    $stats['revenue'] = 0;
} elseif (isBodarider()) {
    $riderModel = new Rider($db);
    $rider = $riderModel->getByUserId($_SESSION['user_id']);
    
    if ($rider) {
        $stats['total_rides'] = $rider['total_rides'];
        $stats['rating'] = $rider['rating'];
        $stats['available'] = $rider['is_available'] ? 'Yes' : 'No';
    } else {
        $stats['total_rides'] = 0;
        $stats['rating'] = 0;
        $stats['available'] = 'Not Setup';
    }
}

require_once __DIR__ . '/../includes/header.php';
?>

<div class="container-fluid">
    <div class="row mb-4">
        <?php if (isHostelOwner()): ?>
        <div class="col-md-3 col-lg-3">
            <div class="stat-card primary">
                <h3><?php echo $stats['hostels']; ?></h3>
                <p>My Hostels</p>
            </div>
        </div>
        <div class="col-md-3 col-lg-3">
            <div class="stat-card success">
                <h3><?php echo $stats['rooms']; ?></h3>
                <p>Total Rooms</p>
            </div>
        </div>
        <div class="col-md-3 col-lg-3">
            <div class="stat-card warning">
                <h3><?php echo $stats['bookings']; ?></h3>
                <p>Bookings</p>
            </div>
        </div>
        <div class="col-md-3 col-lg-3">
            <div class="stat-card info">
                <h3>UGX <?php echo number_format($stats['revenue'], 0); ?></h3>
                <p>Total Revenue</p>
            </div>
        </div>
        <?php elseif (isBodarider()): ?>
        <div class="col-md-4 col-lg-4">
            <div class="stat-card primary">
                <h3><?php echo $stats['total_rides']; ?></h3>
                <p>Total Rides</p>
            </div>
        </div>
        <div class="col-md-4 col-lg-4">
            <div class="stat-card success">
                <h3><?php echo $stats['rating']; ?>/5.0</h3>
                <p>Your Rating</p>
            </div>
        </div>
        <div class="col-md-4 col-lg-4">
            <div class="stat-card warning">
                <h3><?php echo $stats['available']; ?></h3>
                <p>Availability Status</p>
            </div>
        </div>
        <?php endif; ?>
    </div>

    <div class="row">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0"><i class="fas fa-info-circle"></i> Business Information</h5>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6">
                            <p><strong>Name:</strong> <?php echo htmlspecialchars($_SESSION['user_name']); ?></p>
                            <p><strong>Email:</strong> <?php echo htmlspecialchars($_SESSION['user_email']); ?></p>
                        </div>
                        <div class="col-md-6">
                            <p><strong>Phone:</strong> <?php echo htmlspecialchars($_SESSION['user_phone']); ?></p>
                            <p><strong>Account Type:</strong> 
                                <?php 
                                $role_labels = [
                                    'hostel_owner' => 'Hostel Owner',
                                    'hotel_owner' => 'Hotel Owner',
                                    'boda_rider' => 'Boda Rider'
                                ];
                                echo $role_labels[$_SESSION['role']] ?? ucfirst($_SESSION['role']);
                                ?>
                            </p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <?php if (isHostelOwner() && $stats['hostels'] > 0): ?>
    <div class="row mt-4">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0"><i class="fas fa-list"></i> Your Hostels</h5>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-hover">
                            <thead>
                                <tr>
                                    <th>Name</th>
                                    <th>Location</th>
                                    <th>Contact</th>
                                    <th>Rooms</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($hostels as $hostel): ?>
                                <tr>
                                    <td><?php echo htmlspecialchars($hostel['name']); ?></td>
                                    <td><?php echo htmlspecialchars($hostel['location']); ?></td>
                                    <td><?php echo htmlspecialchars($hostel['contact']); ?></td>
                                    <td>
                                        <span class="badge bg-info">
                                            <?php echo count($roomModel->getByHostel($hostel['id'])); ?>
                                        </span>
                                    </td>
                                    <td>
                                        <a href="/hostel-management.php?edit=<?php echo $hostel['id']; ?>" class="btn btn-sm btn-primary">
                                            <i class="fas fa-edit"></i>
                                        </a>
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
    <?php elseif (isHostelOwner() && $stats['hostels'] == 0): ?>
    <div class="row mt-4">
        <div class="col-md-12">
            <div class="alert alert-info">
                <i class="fas fa-info-circle"></i>
                <strong>No hostels yet.</strong> 
                <a href="hostel-management.php" class="btn btn-sm btn-primary ms-2">
                    <i class="fas fa-plus"></i> Add Your First Hostel
                </a>
            </div>
        </div>
    </div>
    <?php endif; ?>
</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
