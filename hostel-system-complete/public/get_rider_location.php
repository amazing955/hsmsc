<?php
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../app/models/Location.php';

header('Content-Type: application/json');

if (!isset($_GET['transport_id'])) {
    echo json_encode(['error' => 'transport_id required']);
    exit;
}

$transport_id = intval($_GET['transport_id']);
$database = new Database();
$db = $database->getConnection();

$query = "SELECT t.id, t.rider_id, r.location as rider_location, u.name as rider_name, u.phone as rider_phone
          FROM transport t
          LEFT JOIN riders r ON t.rider_id = r.id
          LEFT JOIN users u ON r.user_id = u.id
          WHERE t.id = :id LIMIT 1";
$stmt = $db->prepare($query);
$stmt->bindParam(':id', $transport_id, PDO::PARAM_INT);
$stmt->execute();
$ride = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$ride || empty($ride['rider_id'])) {
    echo json_encode(['error' => 'No rider assigned']);
    exit;
}

$lat = null;
$lon = null;

// If rider_location is stored as lat,lon use it
if (!empty($ride['rider_location'])) {
    $loc = trim($ride['rider_location']);
    if (preg_match('/^(-?\d+(?:\.\d+)?)\s*,\s*(-?\d+(?:\.\d+)?)$/', $loc, $m)) {
        $lat = floatval($m[1]);
        $lon = floatval($m[2]);
    } else {
        // Try to geocode the textual location
        $coords = Location::getCoordinates($loc);
        if ($coords) {
            $lat = $coords[0];
            $lon = $coords[1];
        }
    }
}

// Last resort: use Kampala center
if ($lat === null || $lon === null) {
    $lat = Location::KAMPALA_CENTER_LAT;
    $lon = Location::KAMPALA_CENTER_LON;
}

echo json_encode([
    'lat' => $lat,
    'lon' => $lon,
    'name' => $ride['rider_name'] ?? 'Driver',
    'phone' => $ride['rider_phone'] ?? null,
    'timestamp' => date('c')
]);

?>
