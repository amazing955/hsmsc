<?php
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../app/models/Hostel.php';
require_once __DIR__ . '/../app/models/Room.php';

requireHostelOwner();

$page_title = 'Manage Rooms';
$database = new Database();
$db = $database->getConnection();
$hostelModel = new Hostel($db);
$roomModel = new Room($db);

$error = '';
$success = '';

// Get owner's hostels
$hostels = $hostelModel->getByOwner($_SESSION['user_id']);
$hostel_ids = array_column($hostels, 'id');

$selected_hostel_id = isset($_GET['hostel']) ? intval($_GET['hostel']) : (count($hostels) > 0 ? $hostels[0]['id'] : null);

// Verify owner has access to this hostel
if ($selected_hostel_id && !in_array($selected_hostel_id, $hostel_ids)) {
    $selected_hostel_id = null;
}

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $room_number = sanitize($_POST['room_number']);
    $room_type = sanitize($_POST['room_type']);
    $capacity = intval($_POST['capacity']);
    $price = floatval($_POST['price']);
    $hostel_id = intval($_POST['hostel_id']);
    
    // Verify owner has access
    if (!in_array($hostel_id, $hostel_ids)) {
        $error = 'You do not have access to this hostel';
    } elseif (empty($room_number) || empty($room_type) || $capacity < 1 || $price < 0) {
        $error = 'Please fill in all required fields correctly';
    } else {
        if (isset($_POST['room_id']) && !empty($_POST['room_id'])) {
            // Update room
            $room_id = intval($_POST['room_id']);
            $availability = isset($_POST['availability']) ? 1 : 0;
            if ($roomModel->update($room_id, $room_number, $room_type, $capacity, $price, $availability)) {
                $success = 'Room updated successfully!';
            } else {
                $error = 'Failed to update room';
            }
        } else {
            // Create new room
            if ($roomModel->create($hostel_id, $room_number, $room_type, $capacity, $price)) {
                $success = 'Room created successfully!';
            } else {
                $error = 'Failed to create room';
            }
        }
    }
}

$rooms = [];
if ($selected_hostel_id) {
    $rooms = $roomModel->getByHostel($selected_hostel_id);
}

require_once __DIR__ . '/../includes/header.php';
?>

<div class="container-fluid">
    <div class="row">
        <div class="col-md-8">
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0"><i class="fas fa-door-open"></i> Add Room</h5>
                </div>
                <div class="card-body">
                    <?php if ($error): ?>
                        <div class="alert alert-danger"><?php echo $error; ?></div>
                    <?php endif; ?>
                    
                    <?php if ($success): ?>
                        <div class="alert alert-success"><?php echo $success; ?></div>
                    <?php endif; ?>
                    
                    <?php if (empty($hostels)): ?>
                        <div class="alert alert-warning">
                            <i class="fas fa-info-circle"></i> You need to create a hostel first.
                            <a href="/hostel-management.php" class="btn btn-sm btn-primary ms-2">Create Hostel</a>
                        </div>
                    <?php else: ?>
                        <form method="POST" action="">
                            <div class="mb-3">
                                <label class="form-label">Select Hostel *</label>
                                <select name="hostel_id" class="form-select" required onchange="document.location='?hostel='+this.value;">
                                    <?php foreach ($hostels as $hostel): ?>
                                        <option value="<?php echo $hostel['id']; ?>" 
                                                <?php echo $selected_hostel_id == $hostel['id'] ? 'selected' : ''; ?>>
                                            <?php echo htmlspecialchars($hostel['name']); ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            
                            <div class="mb-3">
                                <label class="form-label">Room Number *</label>
                                <input type="text" name="room_number" class="form-control" required placeholder="e.g., R101">
                            </div>
                            
                            <div class="mb-3">
                                <label class="form-label">Room Type *</label>
                                <select name="room_type" class="form-select" required>
                                    <option value="">-- Select Type --</option>
                                    <option value="Single">Single</option>
                                    <option value="Double">Double</option>
                                    <option value="Triple">Triple</option>
                                </select>
                            </div>
                            
                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label class="form-label">Capacity (Persons) *</label>
                                    <input type="number" name="capacity" class="form-control" required min="1">
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label class="form-label">Price (UGX) *</label>
                                    <input type="number" name="price" class="form-control" required min="0" step="100">
                                </div>
                            </div>
                            
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-plus"></i> Add Room
                            </button>
                        </form>
                    <?php endif; ?>
                </div>
            </div>
        </div>
        
        <div class="col-md-4">
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0"><i class="fas fa-list"></i> Rooms</h5>
                </div>
                <div class="card-body" style="max-height: 500px; overflow-y: auto;">
                    <?php if (empty($rooms)): ?>
                        <p class="text-muted">No rooms added yet</p>
                    <?php else: ?>
                        <?php foreach ($rooms as $room): ?>
                            <div class="mb-2 p-2 bg-light rounded">
                                <p class="mb-1"><strong><?php echo htmlspecialchars($room['room_number']); ?></strong></p>
                                <small class="text-muted"><?php echo $room['room_type']; ?> - <?php echo $room['capacity']; ?> person(s)</small>
                                <p class="mb-1 text-info"><strong>UGX <?php echo number_format($room['price']); ?></strong></p>
                                <small class="badge <?php echo $room['availability'] ? 'bg-success' : 'bg-danger'; ?>">
                                    <?php echo $room['availability'] ? 'Available' : 'Unavailable'; ?>
                                </small>
                            </div>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
