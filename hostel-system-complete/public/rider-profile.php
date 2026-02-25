<?php
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../app/models/Rider.php';

requireBodarider();

$page_title = 'My Rider Profile';
$database = new Database();
$db = $database->getConnection();
$riderModel = new Rider($db);

$error = '';
$success = '';

// Get or create rider profile
$rider = $riderModel->getByUserId($_SESSION['user_id']);

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $license_plate = sanitize($_POST['license_plate']);
    $bike_type = sanitize($_POST['bike_type']);
    $phone = sanitize($_POST['phone']);
    $location = sanitize($_POST['location']);
    $is_available = isset($_POST['is_available']) ? 1 : 0;
    
    if (empty($license_plate) || empty($phone) || empty($location)) {
        $error = 'Please fill in all required fields';
    } else {
        if ($rider) {
            // Update existing rider profile
            $data = [
                'license_plate' => $license_plate,
                'bike_type' => $bike_type,
                'phone' => $phone,
                'location' => $location,
                'is_available' => $is_available
            ];
            if ($riderModel->update($_SESSION['user_id'], $data)) {
                $success = 'Profile updated successfully!';
                $rider = $riderModel->getByUserId($_SESSION['user_id']);
            } else {
                $error = 'Failed to update profile';
            }
        } else {
            // Create new rider profile
            $data = [
                'user_id' => $_SESSION['user_id'],
                'license_plate' => $license_plate,
                'bike_type' => $bike_type,
                'phone' => $phone,
                'location' => $location
            ];
            if ($riderModel->create($data)) {
                $success = 'Profile created successfully!';
                $rider = $riderModel->getByUserId($_SESSION['user_id']);
            } else {
                $error = 'Failed to create profile';
            }
        }
    }
}

require_once __DIR__ . '/../includes/header.php';
?>

<div class="container-fluid">
    <div class="row">
        <div class="col-md-8">
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0"><i class="fas fa-motorcycle"></i> Rider Profile</h5>
                </div>
                <div class="card-body">
                    <?php if ($error): ?>
                        <div class="alert alert-danger"><?php echo $error; ?></div>
                    <?php endif; ?>
                    
                    <?php if ($success): ?>
                        <div class="alert alert-success"><?php echo $success; ?></div>
                    <?php endif; ?>
                    
                    <form method="POST" action="">
                        <div class="mb-3">
                            <label class="form-label">License Plate *</label>
                            <input type="text" name="license_plate" class="form-control" required
                                   value="<?php echo $rider ? htmlspecialchars($rider['license_plate']) : ''; ?>">
                        </div>
                        
                        <div class="mb-3">
                            <label class="form-label">Bike Type *</label>
                            <input type="text" name="bike_type" class="form-control" required placeholder="e.g., Motorcycle, Scooter"
                                   value="<?php echo $rider ? htmlspecialchars($rider['bike_type']) : 'Motorcycle'; ?>">
                        </div>
                        
                        <div class="mb-3">
                            <label class="form-label">Phone Number *</label>
                            <input type="tel" name="phone" class="form-control" required
                                   value="<?php echo $rider ? htmlspecialchars($rider['phone']) : htmlspecialchars($_SESSION['user_phone']); ?>">
                        </div>
                        
                        <div class="mb-3">
                            <label class="form-label">Current Location *</label>
                            <input type="text" name="location" class="form-control" required placeholder="e.g., Kampala City Center"
                                   value="<?php echo $rider ? htmlspecialchars($rider['location']) : ''; ?>">
                        </div>
                        
                        <div class="mb-3">
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" name="is_available" id="availability"
                                       <?php echo $rider && $rider['is_available'] ? 'checked' : ''; ?>>
                                <label class="form-check-label" for="availability">
                                    I am available for rides
                                </label>
                            </div>
                        </div>
                        
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-save"></i> 
                            <?php echo $rider ? 'Update Profile' : 'Create Profile'; ?>
                        </button>
                    </form>
                </div>
            </div>
        </div>
        
        <div class="col-md-4">
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0"><i class="fas fa-stats"></i> Your Statistics</h5>
                </div>
                <div class="card-body">
                    <?php if ($rider): ?>
                        <div class="mb-3">
                            <p><strong>Total Rides:</strong> <span class="badge bg-primary"><?php echo $rider['total_rides']; ?></span></p>
                            <p><strong>Rating:</strong> <span class="badge bg-success"><?php echo $rider['rating']; ?>/5.0</span></p>
                            <p><strong>Status:</strong> 
                                <span class="badge <?php echo $rider['is_available'] ? 'bg-success' : 'bg-danger'; ?>">
                                    <?php echo $rider['is_available'] ? 'Available' : 'Unavailable'; ?>
                                </span>
                            </p>
                        </div>
                    <?php else: ?>
                        <p class="text-muted">Complete your profile to start accepting rides</p>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
