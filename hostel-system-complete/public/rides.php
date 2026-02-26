<?php
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../app/models/Rider.php';
require_once __DIR__ . '/../app/models/Transport.php';

requireBodarider();

$page_title = 'My Rides';
$database = new Database();
$db = $database->getConnection();
$riderModel = new Rider($db);
$transportModel = new Transport($db);

// Get rider profile
$rider = $riderModel->getByUserId($_SESSION['user_id']);

if (!$rider) {
    header('Location: rider-profile.php');
    exit();
}

$success = '';
$error = '';

// We'll fetch pending rides and "my rides" after handling any POST actions

// Handle accept ride
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['action'])) {
    $transport_id = intval($_POST['transport_id']);
    
    if ($_POST['action'] == 'accept') {
        if ($transportModel->assignToRider($transport_id, $rider['id'])) {
            // fetch passenger name and phone for this transport
            $pquery = "SELECT u.name as user_name, u.phone as user_phone
                       FROM transport t
                       JOIN users u ON t.user_id = u.id
                       WHERE t.id = :id LIMIT 1";
            $pstmt = $db->prepare($pquery);
            $pstmt->bindParam(':id', $transport_id);
            if ($pstmt->execute()) {
                $prow = $pstmt->fetch(PDO::FETCH_ASSOC);
                $pname = isset($prow['user_name']) ? htmlspecialchars($prow['user_name']) : 'Passenger';
                $pphone = isset($prow['user_phone']) ? htmlspecialchars($prow['user_phone']) : 'N/A';
                $success = 'Ride accepted! You are now assigned to this request. Passenger: ' . $pname . ' (' . $pphone . ')';
            } else {
                $success = 'Ride accepted! You are now assigned to this request.';
            }
        } else {
            $error = 'Failed to accept ride. It may have been taken by another rider.';
        }
    } elseif ($_POST['action'] == 'complete') {
        if ($transportModel->completeRide($transport_id)) {
            $riderModel->incrementRides($_SESSION['user_id']);
            $success = 'Ride completed! Thank you for the safe ride.';
        } else {
            $error = 'Failed to complete ride';
        }
    } elseif ($_POST['action'] == 'cancel') {
        $query = "UPDATE transport SET status = 'cancelled' WHERE id = :id";
        $stmt = $db->prepare($query);
        $stmt->bindParam(':id', $transport_id);
        if ($stmt->execute()) {
            $success = 'Ride cancelled';
        }
    }
}

// Get all pending rides (not assigned yet)
$pending_rides = $transportModel->getPending();

// Get rides assigned to this rider (show student info here)
$query = "SELECT t.*, u.name as user_name, u.phone as user_phone 
          FROM transport t
          JOIN users u ON t.user_id = u.id
          WHERE t.rider_id = :rider_id
          ORDER BY t.created_at DESC";

$stmt = $db->prepare($query);
$stmt->bindParam(':rider_id', $rider['id']);
$stmt->execute();
$my_rides = $stmt->fetchAll(PDO::FETCH_ASSOC);

require_once __DIR__ . '/../includes/header.php';
?>

<div class="container-fluid">
    <?php if ($success): ?>
        <div class="alert alert-success alert-dismissible fade show mb-3">
            <i class="fas fa-check-circle"></i> <?php echo $success; ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    <?php endif; ?>
    
    <?php if ($error): ?>
        <div class="alert alert-danger alert-dismissible fade show mb-3">
            <i class="fas fa-exclamation-circle"></i> <?php echo $error; ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    <?php endif; ?>

    <ul class="nav nav-tabs mb-4" role="tablist">
        <li class="nav-item">
            <a class="nav-link active" data-bs-toggle="tab" href="#pending">
                <i class="fas fa-bell"></i> Pending Requests 
                <span class="badge bg-danger ms-2"><?php echo count($pending_rides); ?></span>
            </a>
        </li>
        <li class="nav-item">
            <a class="nav-link" data-bs-toggle="tab" href="#my-rides">
                <i class="fas fa-tasks"></i> My Rides
            </a>
        </li>
    </ul>

    <div class="tab-content">
        <!-- Pending Requests Tab -->
        <div id="pending" class="tab-pane fade show active">
            <div class="row">
                <?php if (empty($pending_rides)): ?>
                    <div class="col-md-12">
                        <div class="alert alert-info">
                            <i class="fas fa-info-circle"></i> No pending ride requests at the moment. Check back soon!
                        </div>
                    </div>
                <?php else: ?>
                    <?php foreach ($pending_rides as $ride): ?>
                        <div class="col-md-6 mb-3">
                            <div class="card h-100 border-warning">
                                <div class="card-body">
                                    <h5 class="card-title">
                                        <i class="fas fa-map-pin"></i> New Ride Request
                                    </h5>
                                    <div class="mb-3">
                                        <p class="mb-1">
                                            <strong>📍 From:</strong> <?php echo htmlspecialchars($ride['pickup_location']); ?>
                                        </p>
                                        <p class="mb-2">
                                            <strong>📍 To:</strong> <?php echo htmlspecialchars($ride['destination']); ?>
                                        </p>
                                    </div>
                                    
                                    <div class="mb-3 bg-light p-2 rounded">
                                        <p class="mb-1"><strong>Passenger:</strong> Hidden until you accept the ride</p>
                                        <p class="mb-1"><strong>Phone:</strong> Hidden</p>
                                        <p class="mb-0"><strong>Cost:</strong> <span class="badge bg-success">UGX <?php echo number_format($ride['cost'], 0); ?></span></p>
                                    </div>
                                    
                                    <small class="text-muted d-block mb-3">
                                        Requested: <?php echo date('H:i', strtotime($ride['created_at'])); ?>
                                    </small>
                                    
                                    <form method="POST" class="d-grid gap-2">
                                        <input type="hidden" name="transport_id" value="<?php echo $ride['id']; ?>">
                                        <input type="hidden" name="action" value="accept">
                                        <button type="submit" class="btn btn-success btn-lg">
                                            <i class="fas fa-check"></i> Accept Ride
                                        </button>
                                    </form>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>
        </div>

        <!-- My Rides Tab -->
        <div id="my-rides" class="tab-pane fade">
            <div class="table-responsive">
                <table class="table table-hover">
                    <thead class="table-light">
                        <tr>
                            <th>From</th>
                            <th>To</th>
                            <th>Passenger</th>
                            <th>Cost</th>
                            <th>Status</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($my_rides)): ?>
                            <tr>
                                <td colspan="6" class="text-center text-muted py-4">
                                    You haven't accepted any rides yet. Check the pending requests!
                                </td>
                            </tr>
                        <?php else: ?>
                            <?php foreach ($my_rides as $ride): ?>
                                <tr>
                                    <td>
                                        <small><?php echo htmlspecialchars(substr($ride['pickup_location'], 0, 25)); ?></small>
                                    </td>
                                    <td>
                                        <small><?php echo htmlspecialchars(substr($ride['destination'], 0, 25)); ?></small>
                                    </td>
                                    <td><?php echo htmlspecialchars($ride['user_name']); ?></td>
                                    <td>
                                        <strong>UGX <?php echo number_format($ride['cost'], 0); ?></strong>
                                    </td>
                                    <td>
                                        <span class="badge bg-<?php echo $ride['status'] == 'completed' ? 'success' : ($ride['status'] == 'assigned' ? 'info' : 'secondary'); ?>">
                                            <?php echo ucfirst($ride['status']); ?>
                                        </span>
                                    </td>
                                    <td>
                                        <?php if ($ride['status'] == 'assigned'): ?>
                                            <form method="POST" class="d-inline">
                                                <input type="hidden" name="transport_id" value="<?php echo $ride['id']; ?>">
                                                <input type="hidden" name="action" value="complete">
                                                <button type="submit" class="btn btn-sm btn-success" title="Mark ride as completed">
                                                    <i class="fas fa-check"></i> Complete
                                                </button>
                                            </form>
                                        <?php endif; ?>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
