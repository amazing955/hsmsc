<?php
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../config/database.php';

header('Content-Type: application/json');

// Only allow logged-in boda riders
if (!isset($_SESSION['user_id']) || !isset($_SESSION['role']) || $_SESSION['role'] !== 'boda_rider') {
    echo json_encode(['error' => 'Unauthorized']);
    http_response_code(401);
    exit;
}

$input = json_decode(file_get_contents('php://input'), true);
if (!$input || !isset($input['lat']) || !isset($input['lon'])) {
    echo json_encode(['error' => 'lat and lon required']);
    http_response_code(400);
    exit;
}

$lat = floatval($input['lat']);
$lon = floatval($input['lon']);

$locationValue = $lat . ',' . $lon;

$database = new Database();
$db = $database->getConnection();

// Update riders table for current user
$query = "UPDATE riders SET location = :loc, is_available = 1 WHERE user_id = :uid";
$stmt = $db->prepare($query);
$stmt->bindParam(':loc', $locationValue);
$stmt->bindParam(':uid', $_SESSION['user_id']);

if ($stmt->execute()) {
    echo json_encode(['success' => true, 'location' => $locationValue]);
} else {
    echo json_encode(['error' => 'Failed to update']);
    http_response_code(500);
}

?>
