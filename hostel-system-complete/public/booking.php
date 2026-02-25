<?php
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../app/models/Room.php';
require_once __DIR__ . '/../app/models/Booking.php';

requireLogin();

$page_title = 'Book a Room';
$database = new Database();
$db = $database->getConnection();
$roomModel = new Room($db);
$bookingModel = new Booking($db);

$success = '';
$error = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $room_id = $_POST['room_id'];
    $check_in = $_POST['check_in'];
    $check_out = $_POST['check_out'];
    
    $check_in_date = new DateTime($check_in);
    $check_out_date = new DateTime($check_out);
    $today = new DateTime();
    $today->setTime(0, 0, 0);
    
    if ($check_in_date < $today) {
        $error = 'Check-in date cannot be in the past';
    } elseif ($check_out_date <= $check_in_date) {
        $error = 'Check-out date must be after check-in date';
    } else {
        $room = $roomModel->getById($room_id);
        
        if ($room && $room['availability']) {
            $interval = $check_in_date->diff($check_out_date);
            $days = $interval->days;
            
            if ($days < 1) {
                $error = 'Minimum booking duration is 1 day';
            } else {
                $total_amount = $room['price'] * $days;
                
                if ($bookingModel->create($_SESSION['user_id'], $room_id, $check_in, $check_out, $total_amount)) {
                    $roomModel->updateAvailability($room_id, false);
                    $success = 'Booking successful! Duration: ' . $days . ' day(s), Total Amount: UGX ' . number_format($total_amount, 0);
                } else {
                    $error = 'Booking failed. Please try again.';
                }
            }
        } else {
            $error = 'Room not available';
        }
    }
}

$availableRooms = $roomModel->getAvailable();

require_once __DIR__ . '/../includes/header.php';
?>

<div class="container-fluid">
    <?php if ($success): ?>
        <div class="alert alert-success alert-dismissible fade show">
            <i class="fas fa-check-circle"></i> <?php echo $success; ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    <?php endif; ?>
    
    <?php if ($error): ?>
        <div class="alert alert-danger alert-dismissible fade show">
            <i class="fas fa-exclamation-circle"></i> <?php echo $error; ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    <?php endif; ?>

    <div class="row">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0"><i class="fas fa-bed"></i> Available Rooms</h5>
                </div>
                <div class="card-body">
                    <?php if (count($availableRooms) > 0): ?>
                        <div class="row">
                            <?php foreach ($availableRooms as $room): ?>
                                <div class="col-md-6 col-lg-4 mb-4">
                                    <div class="card h-100">
                                        <div class="card-body">
                                            <h5 class="card-title"><?php echo htmlspecialchars($room['hostel_name']); ?></h5>
                                            <p class="card-text">
                                                <strong>Room:</strong> <?php echo htmlspecialchars($room['room_number']); ?><br>
                                                <strong>Type:</strong> 
                                                <span class="badge bg-info"><?php echo htmlspecialchars($room['room_type']); ?></span><br>
                                                <strong>Capacity:</strong> <?php echo $room['capacity']; ?> person(s)<br>
                                                <strong>Location:</strong> <?php echo htmlspecialchars($room['location']); ?><br>
                                                <strong>Price:</strong> 
                                                <span class="text-success fw-bold">UGX <?php echo number_format($room['price'], 0); ?>/month</span>
                                            </p>
                                            <button class="btn btn-primary w-100" data-bs-toggle="modal" 
                                                data-bs-target="#bookingModal<?php echo $room['id']; ?>">
                                                <i class="fas fa-calendar-check"></i> Book Now
                                            </button>
                                        </div>
                                    </div>

                                    <div class="modal fade" id="bookingModal<?php echo $room['id']; ?>" tabindex="-1">
                                        <div class="modal-dialog">
                                            <div class="modal-content">
                                                <div class="modal-header">
                                                    <h5 class="modal-title">Book Room <?php echo htmlspecialchars($room['room_number']); ?></h5>
                                                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                                </div>
                                                <form method="POST">
                                                    <div class="modal-body">
                                                        <input type="hidden" name="room_id" value="<?php echo $room['id']; ?>">
                                                        
                                                        <div class="mb-3">
                                                            <label class="form-label">Check-in Date</label>
                                                            <input type="date" name="check_in" class="form-control" 
                                                                min="<?php echo date('Y-m-d'); ?>" required>
                                                        </div>
                                                        
                                                        <div class="mb-3">
                                                            <label class="form-label">Check-out Date</label>
                                                            <input type="date" name="check_out" class="form-control" 
                                                                min="<?php echo date('Y-m-d', strtotime('+1 day')); ?>" required>
                                                        </div>
                                                        
                                                        <div class="alert alert-info">
                                                            <strong>Price:</strong> UGX <?php echo number_format($room['price'], 0); ?> per month
                                                        </div>
                                                    </div>
                                                    <div class="modal-footer">
                                                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                                                        <button type="submit" class="btn btn-primary">Confirm Booking</button>
                                                    </div>
                                                </form>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    <?php else: ?>
                        <div class="text-center py-5">
                            <i class="fas fa-bed fa-4x text-muted mb-3"></i>
                            <p class="text-muted">No rooms available at the moment</p>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
