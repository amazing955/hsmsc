<?php
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../app/models/Hostel.php';
require_once __DIR__ . '/../app/models/Room.php';

requireHostelOwner();

$page_title = 'Manage Hostels';
$database = new Database();
$db = $database->getConnection();
$hostelModel = new Hostel($db);
$roomModel = new Room($db);

$error = '';
$success = '';

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $name = sanitize($_POST['name']);
    $location = sanitize($_POST['location']);
    $latitude = floatval($_POST['latitude']);
    $longitude = floatval($_POST['longitude']);
    $description = sanitize($_POST['description']);
    $contact = sanitize($_POST['contact']);
    
    if (empty($name) || empty($location) || empty($contact)) {
        $error = 'Please fill in all required fields';
    } else {
        if (isset($_POST['hostel_id']) && !empty($_POST['hostel_id'])) {
            // Update existing hostel
            $hostel_id = intval($_POST['hostel_id']);
            if ($hostelModel->update($hostel_id, $name, $location, $latitude, $longitude, $description, $contact)) {
                $success = 'Hostel updated successfully!';
            } else {
                $error = 'Failed to update hostel';
            }
        } else {
            // Create new hostel
            $result = $hostelModel->create($name, $location, $latitude, $longitude, $description, $contact, $_SESSION['user_id']);
            if ($result) {
                $success = 'Hostel created successfully! You can now add rooms.';
            } else {
                $error = 'Failed to create hostel';
            }
        }
    }
}

$hostels = $hostelModel->getByOwner($_SESSION['user_id']);
$edit_hostel = null;

if (isset($_GET['edit'])) {
    $hostel_id = intval($_GET['edit']);
    $edit_hostel = $hostelModel->getById($hostel_id);
    if (!$edit_hostel || $edit_hostel['owner_id'] != $_SESSION['user_id']) {
        $edit_hostel = null;
    }
}

require_once __DIR__ . '/../includes/header.php';
?>

<div class="container-fluid">
    <div class="row">
        <div class="col-md-8">
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0">
                        <i class="fas fa-building"></i> 
                        <?php echo $edit_hostel ? 'Edit Hostel' : 'Add New Hostel'; ?>
                    </h5>
                </div>
                <div class="card-body">
                    <?php if ($error): ?>
                        <div class="alert alert-danger"><?php echo $error; ?></div>
                    <?php endif; ?>
                    
                    <?php if ($success): ?>
                        <div class="alert alert-success"><?php echo $success; ?></div>
                    <?php endif; ?>
                    
                    <form method="POST" action="">
                        <?php if ($edit_hostel): ?>
                            <input type="hidden" name="hostel_id" value="<?php echo $edit_hostel['id']; ?>">
                        <?php endif; ?>
                        
                        <div class="mb-3">
                            <label class="form-label">Hostel Name *</label>
                            <input type="text" name="name" class="form-control" required 
                                   value="<?php echo $edit_hostel ? htmlspecialchars($edit_hostel['name']) : ''; ?>">
                        </div>
                        
                        <div class="mb-3">
                            <label class="form-label">Location *</label>
                            <input type="text" name="location" class="form-control" required
                                   value="<?php echo $edit_hostel ? htmlspecialchars($edit_hostel['location']) : ''; ?>">
                        </div>
                        
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Latitude</label>
                                <input type="number" step="0.000001" name="latitude" class="form-control"
                                       value="<?php echo $edit_hostel ? $edit_hostel['latitude'] : '0.3476'; ?>">
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Longitude</label>
                                <input type="number" step="0.000001" name="longitude" class="form-control"
                                       value="<?php echo $edit_hostel ? $edit_hostel['longitude'] : '32.5825'; ?>">
                            </div>
                        </div>
                        
                        <div class="mb-3">
                            <label class="form-label">Contact Phone *</label>
                            <input type="tel" name="contact" class="form-control" required
                                   value="<?php echo $edit_hostel ? htmlspecialchars($edit_hostel['contact']) : ''; ?>">
                        </div>
                        
                        <div class="mb-3">
                            <label class="form-label">Description</label>
                            <textarea name="description" class="form-control" rows="3"><?php echo $edit_hostel ? htmlspecialchars($edit_hostel['description']) : ''; ?></textarea>
                        </div>
                        
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-save"></i> 
                            <?php echo $edit_hostel ? 'Update Hostel' : 'Create Hostel'; ?>
                        </button>
                        <?php if ($edit_hostel): ?>
                            <a href="hostel-management.php" class="btn btn-secondary">Cancel</a>
                        <?php endif; ?>
                    </form>
                </div>
            </div>
        </div>
        
        <div class="col-md-4">
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0"><i class="fas fa-list"></i> My Hostels</h5>
                </div>
                <div class="card-body">
                    <?php if (empty($hostels)): ?>
                        <p class="text-muted">No hostels yet</p>
                    <?php else: ?>
                        <?php foreach ($hostels as $hostel): ?>
                            <div class="mb-2 p-2 bg-light rounded">
                                <p class="mb-1"><strong><?php echo htmlspecialchars($hostel['name']); ?></strong></p>
                                <small class="text-muted"><?php echo htmlspecialchars($hostel['location']); ?></small>
                                <div class="mt-2">
                                    <a href="hostel-management.php?edit=<?php echo $hostel['id']; ?>" class="btn btn-xs btn-primary">Edit</a>
                                    <a href="room-management.php?hostel=<?php echo $hostel['id']; ?>" class="btn btn-xs btn-info">Rooms</a>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
