<?php
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../app/models/Transport.php';
require_once __DIR__ . '/../app/models/Rider.php';
require_once __DIR__ . '/../app/models/Location.php';

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
    
    // Calculate real distance and fare using SafeBoda pricing
    $transportCost = Location::calculateTransportCost($pickup, $destination);
    $distance = $transportCost['distance'];
    $cost = $transportCost['cost'];
    
    $result = $transportModel->create($_SESSION['user_id'], $pickup, $destination, $cost);
    if ($result) {
        $success = 'Ride request submitted! Distance: ' . $distance . ' km | Estimated Cost: UGX ' . number_format($cost, 0) . '. A rider will confirm shortly.';
    } else {
        $error = 'Failed to submit request. Please try again.';
    }
}

// Get user's ride history with assigned rider details
$query = "SELECT t.*, 
          COALESCE(u_rider.name, t.rider_name) as rider_name, 
          r.location as rider_location,
          r.rating as rider_rating,
          r.total_rides as rider_total_rides,
          u_customer.name as user_name 
          FROM transport t 
          LEFT JOIN users u_customer ON t.user_id = u_customer.id
          LEFT JOIN riders r ON t.rider_id = r.id
          LEFT JOIN users u_rider ON r.user_id = u_rider.id
          WHERE t.user_id = :user_id
          ORDER BY t.created_at DESC";
$stmt = $db->prepare($query);
$stmt->bindParam(':user_id', $_SESSION['user_id']);
$stmt->execute();
$myRides = $stmt->fetchAll(PDO::FETCH_ASSOC);

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
                        <br>
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

            <?php 
            // Separate assigned/pending rides from completed/cancelled
            $activeRides = array_filter($myRides, function($r) { return in_array($r['status'], ['pending', 'assigned']); });
            $pastRides = array_filter($myRides, function($r) { return in_array($r['status'], ['completed', 'cancelled']); });
            ?>
            
            <?php if (!empty($activeRides)): ?>
                <div class="card mt-4 border-info">
                    <div class="card-header bg-info text-white">
                        <h5 class="mb-0"><i class="fas fa-motorcycle"></i> Active Rides</h5>
                    </div>
                    <div class="card-body">
                        <?php foreach ($activeRides as $ride): ?>
                            <div class="card mb-3 <?php echo $ride['status'] == 'assigned' ? 'border-success' : 'border-warning'; ?>">
                                <div class="card-body">
                                    <div class="row">
                                        <div class="col-md-6">
                                            <h6><i class="fas fa-map-pin"></i> <strong>Route</strong></h6>
                                            <p class="mb-1"><strong>From:</strong> <?php echo htmlspecialchars($ride['pickup_location']); ?></p>
                                            <p class="mb-2"><strong>To:</strong> <?php echo htmlspecialchars($ride['destination']); ?></p>
                                            <?php 
                                                $rideDistance = Location::calculateDistance($ride['pickup_location'], $ride['destination']);
                                                $rideCost = Location::calculateFare($rideDistance);
                                            ?>
                                            <p class="mb-0">
                                                <strong>Distance:</strong> <span class="badge bg-primary"><?php echo $rideDistance; ?> km</span>
                                                <strong>Cost:</strong> <span class="badge bg-success">UGX <?php echo number_format($rideCost, 0); ?></span>
                                            </p>
                                        </div>
                                        <div class="col-md-6">
                                            <h6><i class="fas fa-info-circle"></i> <strong>Status</strong></h6>
                                            <p class="mb-1">
                                                <span class="badge bg-<?php echo $ride['status'] == 'assigned' ? 'success' : 'warning'; ?>" style="font-size: 0.9rem;">
                                                    <?php echo ucfirst($ride['status']); ?>
                                                </span>
                                            </p>
                                            <p class="text-muted"><small>Requested: <?php echo date('H:i, M d', strtotime($ride['created_at'])); ?></small></p>
                                        </div>
                                    </div>
                                    
                                    <?php if ($ride['status'] == 'assigned' && !empty($ride['rider_name'])): ?>
                                        <hr>
                                        <div class="row">
                                            <div class="col-md-6">
                                                <h6><i class="fas fa-user"></i> <strong>Boda Driver</strong></h6>
                                                <p class="mb-1"><strong>Name:</strong> <?php echo htmlspecialchars($ride['rider_name']); ?></p>
                                                <p class="mb-1"><strong>Rating:</strong> ⭐ <?php echo $ride['rider_rating'] ?? 'N/A'; ?>/5.0</p>
                                                <p class="mb-0"><strong>Total Rides:</strong> <?php echo $ride['rider_total_rides'] ?? 0; ?></p>
                                            </div>
                                            <div class="col-md-6">
                                                <h6><i class="fas fa-map-marker-alt"></i> <strong>Tracking</strong></h6>
                                                <p class="mb-1"><strong>Current Location:</strong> <?php echo htmlspecialchars($ride['rider_location'] ?? 'Updating...'); ?></p>
                                                <p class="mb-0"><strong>Est. Arrival:</strong> <span class="badge bg-info">~<?php echo rand(3, 12); ?> mins</span></p>
                                            </div>
                                        </div>
                                    <?php else: ?>
                                        <hr>
                                        <div class="alert alert-warning mb-0">
                                            <i class="fas fa-clock"></i> Waiting for a rider to accept your request...
                                        </div>
                                    <?php endif; ?>
                                    
                                    <div class="mt-3">
                                        <?php if (!in_array($ride['status'], ['completed','cancelled'])): ?>
                                            <form method="POST" style="display:inline;" onsubmit="return confirm('Cancel this ride?');">
                                                <input type="hidden" name="action" value="cancel_ride">
                                                <input type="hidden" name="ride_id" value="<?php echo intval($ride['id']); ?>">
                                                <button type="submit" class="btn btn-sm btn-danger"><i class="fas fa-times"></i> Cancel</button>
                                            </form>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </div>
            <?php endif; ?>
            
            <?php if (!empty($pastRides)): ?>
            <div class="card mt-4">
                <div class="card-header">
                    <h5 class="mb-0"><i class="fas fa-history"></i> Ride History</h5>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-sm">
                            <thead>
                                <tr>
                                    <th>From</th>
                                    <th>To</th>
                                    <th>Distance</th>
                                    <th>Cost</th>
                                    <th>Status</th>
                                    <th>Date</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($pastRides as $ride): ?>
                                <tr>
                                    <td><small><?php echo htmlspecialchars(substr($ride['pickup_location'], 0, 20)); ?></small></td>
                                    <td><small><?php echo htmlspecialchars(substr($ride['destination'], 0, 20)); ?></small></td>
                                    <td>
                                        <small>
                                            <?php echo Location::calculateDistance($ride['pickup_location'], $ride['destination']); ?> km
                                        </small>
                                    </td>
                                    <td><strong>UGX <?php echo number_format($ride['cost'], 0); ?></strong></td>
                                    <td>
                                        <span class="badge bg-<?php echo $ride['status'] == 'completed' ? 'success' : 'secondary'; ?>">
                                            <?php echo ucfirst($ride['status']); ?>
                                        </span>
                                    </td>
                                    <td><small><?php echo date('M d', strtotime($ride['created_at'])); ?></small></td>
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
