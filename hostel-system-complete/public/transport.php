<?php
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../app/models/Transport.php';
require_once __DIR__ . '/../app/models/Rider.php';

requireLogin();

$page_title = 'Hostel Boda Transport';
$database = new Database();
$db = $database->getConnection();
$transportModel = new Transport($db);
$riderModel = new Rider($db);

$success = '';
$error = '';

// Get available riders
$availableRiders = $riderModel->getAvailable();

// Get pending ride count
$pendingCount = $transportModel->countPending();


// New: handle cancel ride request
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['action']) && $_POST['action'] === 'cancel_ride') {
    $rideId = isset($_POST['ride_id']) ? intval($_POST['ride_id']) : 0;
    if ($rideId <= 0) {
        $error = 'Invalid ride selected.';
    } else {
        // verify ownership and fetch rider_id
        $stmt = $db->prepare("SELECT id, user_id, status, rider_id FROM transport WHERE id = :id LIMIT 1");
        $stmt->bindParam(':id', $rideId, PDO::PARAM_INT);
        $stmt->execute();
        $ride = $stmt->fetch(PDO::FETCH_ASSOC);
        if (!$ride) {
            $error = 'Ride not found.';
        } elseif ($ride['user_id'] != $_SESSION['user_id']) {
            $error = 'You are not authorized to cancel this ride.';
        } elseif (in_array($ride['status'], ['cancelled','completed'])) {
            $error = 'Ride cannot be cancelled (already '.$ride['status'].').';
        } else {
            // perform cancellation: set status to 'cancelled' and clear rider assignment
            $u = $db->prepare("UPDATE transport SET status = 'cancelled', rider_id = NULL WHERE id = :id");
            $u->bindParam(':id', $rideId, PDO::PARAM_INT);
            if ($u->execute()) {
                // if a rider was assigned, mark them available again
                if (!empty($ride['rider_id'])) {
                    $r = $db->prepare("UPDATE riders SET is_available = 1 WHERE id = :rid");
                    $r->bindParam(':rid', $ride['rider_id'], PDO::PARAM_INT);
                    $r->execute();
                }

                $success = 'Ride cancelled successfully.';

                // refresh dependent data so UI reflects removal from pending list
                $pendingCount = $transportModel->countPending();
                $availableRiders = $riderModel->getAvailable();
                $myRides = $transportModel->getByUser($_SESSION['user_id']);
            } else {
                $error = 'Failed to cancel ride. Please try again.';
            }
        }
    }
}
// existing ride request handler
if ($_SERVER['REQUEST_METHOD'] == 'POST' && (!isset($_POST['action']) || $_POST['action'] !== 'cancel_ride')) {
    $pickup = sanitize($_POST['pickup']);
    $destination = sanitize($_POST['destination']);
    
    $distance = rand(1, 10);
    $cost = $distance * 500;
    
    $result = $transportModel->create($_SESSION['user_id'], $pickup, $destination, $cost);
    if ($result) {
        $success = 'Ride request submitted! Estimated Cost: UGX ' . number_format($cost, 0) . '. A rider will confirm shortly.';
    } else {
        $error = 'Failed to submit request. Please try again.';
    }
}

// Get user's ride history
$myRides = $transportModel->getByUser($_SESSION['user_id']);

require_once __DIR__ . '/../includes/header.php';
?>

<div class="container-fluid">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0"><i class="fas fa-motorcycle"></i> Request Hostel Boda</h5>
                </div>
                <div class="card-body">
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

                    <div class="alert alert-info">
                        <i class="fas fa-info-circle"></i> Our transport service connects you with reliable riders for quick and safe trips.
                        <br><strong>Rate:</strong> UGX 250 per km
                        <br><strong>Available Riders:</strong> <?php echo count($availableRiders); ?> | <strong>Pending Requests:</strong> <span class="badge bg-warning"><?php echo $pendingCount; ?></span>
                    </div>

                    <form method="POST">
                        <div class="mb-3">
                            <label class="form-label">Pickup Location</label>
                            <input type="text" name="pickup" class="form-control" 
                                placeholder="e.g., Makerere Main Gate" required>
                        </div>
                        
                        <div class="mb-3">
                            <label class="form-label">Destination</label>
                            <input type="text" name="destination" class="form-control" 
                                placeholder="e.g., Your Hostel Name" required>
                        </div>
                        
                        <button type="submit" class="btn btn-primary btn-lg w-100">
                            <i class="fas fa-motorcycle"></i> Request Ride
                        </button>
                    </form>
                </div>
            </div>

            <div class="card mt-4">
                <div class="card-header">
                    <h5 class="mb-0"><i class="fas fa-users"></i> Available Riders (<?php echo count($availableRiders); ?>)</h5>
                </div>
                <div class="card-body">
                    <?php if (empty($availableRiders)): ?>
                        <div class="alert alert-warning">
                            <i class="fas fa-info-circle"></i> No riders available at the moment. Please try again later.
                        </div>
                    <?php else: ?>
                        <div class="list-group">
                            <?php foreach ($availableRiders as $rider): ?>
                                <div class="list-group-item">
                                    <div class="d-flex align-items-center">
                                        <i class="fas fa-user-circle fa-3x text-primary me-3"></i>
                                        <div>
                                            <h6 class="mb-0"><?php echo htmlspecialchars($rider['rider_name']); ?></h6>
                                            <small class="text-muted">Bike: <?php echo htmlspecialchars($rider['bike_type']); ?></small>
                                            <br>
                                            <small class="text-muted">Rating: ⭐ <?php echo $rider['rating']; ?>/5.0 | Rides: <?php echo $rider['total_rides']; ?></small>
                                            <br>
                                            <small class="text-success">📍 <?php echo htmlspecialchars($rider['location']); ?></small>
                                        </div>
                                        <span class="badge bg-success ms-auto">Available</span>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    <?php endif; ?>
                </div>
            </div>

            <?php if (!empty($myRides)): ?>
            <div class="card mt-4">
                <div class="card-header">
                    <h5 class="mb-0"><i class="fas fa-history"></i> Your Ride History</h5>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-sm">
                            <thead>
                                <tr>
                                    <th>From</th>
                                    <th>To</th>
                                    <th>Cost</th>
                                    <th>Status</th>
                                    <th>Date</th>
                                    <th>Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach (array_slice($myRides, 0, 5) as $ride): ?>
                                <tr>
                                    <td><small><?php echo htmlspecialchars(substr($ride['pickup_location'], 0, 20)); ?></small></td>
                                    <td><small><?php echo htmlspecialchars(substr($ride['destination'], 0, 20)); ?></small></td>
                                    <td><strong>UGX <?php echo number_format($ride['cost'], 0); ?></strong></td>
                                    <td>
                                        <span class="badge bg-<?php echo $ride['status'] == 'completed' ? 'success' : ($ride['status'] == 'assigned' ? 'info' : 'warning'); ?>">
                                            <?php echo ucfirst($ride['status']); ?>
                                        </span>
                                    </td>
                                    <td><small><?php echo date('M d', strtotime($ride['created_at'])); ?></small></td>
                                    <td>
                                        <?php if (!in_array($ride['status'], ['completed','cancelled'])): ?>
                                            <form method="POST" style="display:inline;" onsubmit="return confirm('Cancel this ride?');">
                                                <input type="hidden" name="action" value="cancel_ride">
                                                <input type="hidden" name="ride_id" value="<?php echo intval($ride['id']); ?>">
                                                <button type="submit" class="btn btn-sm btn-danger">Cancel</button>
                                            </form>
                                        <?php else: ?>
                                            <small class="text-muted">—</small>
                                        <?php endif; ?>
                                   </td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
