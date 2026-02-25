<?php
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../app/models/Hostel.php';

requireLogin();

$page_title = 'Find Hostels';
$database = new Database();
$db = $database->getConnection();
$hostelModel = new Hostel($db);

$hostels = $hostelModel->getAll();

require_once __DIR__ . '/../includes/header.php';
?>

<!-- Leaflet CSS -->
<link rel="stylesheet" href="https://unpkg.com/leaflet/dist/leaflet.css" />

<div class="container-fluid">
    <div class="row">
        <div class="col-md-4">

            <!-- SEARCH CARD -->
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0"><i class="fas fa-search"></i> Search Hostels</h5>
                </div>
                <div class="card-body">

                    <div class="mb-3">
                        <label class="form-label">Search by Name</label>
                        <input type="text" id="searchInput" class="form-control" 
                            placeholder="Enter hostel name...">
                    </div>

                    <button class="btn btn-primary w-100" onclick="searchHostels()">
                        <i class="fas fa-search"></i> Search
                    </button>

                    <!-- ADDED: USER LOCATION BUTTON -->
                    <button class="btn btn-success w-100 mt-2" onclick="locateUser()">
                        <i class="fas fa-location"></i> Locate Me
                    </button>
                    <!-- END ADDED -->
                </div>
            </div>

            <!-- HOSTEL LIST -->
            <div class="card mt-3">
                <div class="card-header">
                    <h5 class="mb-0"><i class="fas fa-list"></i> Hostel List</h5>
                </div>
                <div class="card-body hostel-list p-0">
                    <div id="hostelList">
                        <?php foreach ($hostels as $hostel): ?>
                            <div class="hostel-item" onclick="selectHostel(<?php echo $hostel['id']; ?>, 
                                <?php echo $hostel['latitude']; ?>, 
                                <?php echo $hostel['longitude']; ?>)">
                                <h6><?php echo htmlspecialchars($hostel['name']); ?></h6>

                                <p class="mb-1 small">
                                    <i class="fas fa-map-marker-alt text-danger"></i>
                                    <?php echo htmlspecialchars($hostel['location']); ?>
                                </p>

                                <p class="mb-0 small text-muted">
                                    <?php echo htmlspecialchars($hostel['description']); ?>
                                </p>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </div>
            </div>

        </div>

        <!-- MAP AREA -->
        <div class="col-md-8">
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0"><i class="fas fa-map"></i> Map View</h5>
                </div>
                <div class="card-body p-0">
                    <div id="map" style="height: 500px;"></div>
                </div>
            </div>

            <div class="alert alert-info mt-3">
                <i class="fas fa-info-circle"></i> 
                <strong>Tip:</strong> Click on a hostel from the list or click on a map marker to see details.
                The map shows all hostels in the Kampala area.
            </div>
        </div>
    </div>
</div>

<script src="https://unpkg.com/leaflet/dist/leaflet.js"></script>
<script>
const hostels = <?php echo json_encode($hostels); ?>;
let map;
let markers = [];
let userMarker = null; // ADDED

function initMap() {
    // Initialize map centered on Kampala
    map = L.map('map').setView([0.3476, 32.5825], 13);

    // OpenStreetMap tiles
    L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
        attribution: '&copy; OpenStreetMap contributors'
    }).addTo(map);

    // Add hostel markers
    hostels.forEach(hostel => {
        const marker = L.marker([parseFloat(hostel.latitude), parseFloat(hostel.longitude)])
            .addTo(map)
            .bindPopup(`
                <div style="padding: 10px;">
                    <h6>${hostel.name}</h6>
                    <p class="mb-1"><strong>Location:</strong> ${hostel.location}</p>
                    <p class="mb-1"><strong>Contact:</strong> ${hostel.contact}</p>
                    <p class="mb-0">${hostel.description}</p>
                </div>
            `);

        markers.push(marker);
    });
}

function selectHostel(id, lat, lng) {
    const position = [parseFloat(lat), parseFloat(lng)];
    map.setView(position, 15);

    const marker = markers.find(m => 
        m.getLatLng().lat === position[0] && 
        m.getLatLng().lng === position[1]
    );
    if (marker) marker.openPopup();
}

function searchHostels() {
    const searchTerm = document.getElementById('searchInput').value.toLowerCase();
    const hostelItems = document.querySelectorAll('.hostel-item');

    hostelItems.forEach(item => {
        const text = item.textContent.toLowerCase();
        item.style.display = text.includes(searchTerm) ? 'block' : 'none';
    });
}

document.getElementById('searchInput').addEventListener('keyup', function(e) {
    if (e.key === 'Enter') searchHostels();
});

// ADDED: USER LOCATION FUNCTION
function locateUser() {
    if (!navigator.geolocation) {
        alert("Your browser does not support location access.");
        return;
    }

    navigator.geolocation.getCurrentPosition(
        (position) => {
            const lat = position.coords.latitude;
            const lng = position.coords.longitude;

            if (userMarker) {
                map.removeLayer(userMarker);
            }

            userMarker = L.marker([lat, lng], {
                title: "You are here"
            }).addTo(map)
              .bindPopup("<strong>You are here</strong>")
              .openPopup();

            map.setView([lat, lng], 15);
        },
        () => {
            alert("Failed to get your current location.");
        }
    );
}
// END ADDED

document.addEventListener('DOMContentLoaded', initMap);
</script>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
