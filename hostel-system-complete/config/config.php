<?php
session_start();

define('BASE_URL', '/');
define('SITE_NAME', 'Hostel Management System');

date_default_timezone_set('Africa/Kampala');

// -------------------- Session & Role Checks --------------------

function isLoggedIn() {
    return isset($_SESSION['user_id']);
}

function isAdmin() {
    return isset($_SESSION['role']) && $_SESSION['role'] === 'admin';
}

function isBusinessOwner() {
    return isset($_SESSION['role']) && in_array($_SESSION['role'], ['hostel_owner', 'hotel_owner', 'boda_rider']);
}

function isHostelOwner() {
    return isset($_SESSION['role']) && $_SESSION['role'] === 'hostel_owner';
}

function isHotelOwner() {
    return isset($_SESSION['role']) && $_SESSION['role'] === 'hotel_owner';
}

function isBodarider() {
    return isset($_SESSION['role']) && $_SESSION['role'] === 'boda_rider';
}

function isStudent() {
    return isset($_SESSION['role']) && $_SESSION['role'] === 'student';
}

// -------------------- Access Requirements --------------------

function requireLogin() {
    if (!isLoggedIn()) {
        header('Location: ' . BASE_URL . 'login.php');
        exit();
    }
}

function requireAdmin() {
    requireLogin();
    if (!isAdmin()) {
        header('Location: ' . BASE_URL . 'dashboard.php');
        exit();
    }
}

function requireBusinessOwner() {
    requireLogin();
    if (!isBusinessOwner()) {
        header('Location: ' . BASE_URL . 'dashboard.php');
        exit();
    }
}

function requireHostelOwner() {
    requireLogin();
    if (!isHostelOwner()) {
        header('Location: ' . BASE_URL . 'dashboard.php');
        exit();
    }
}

function requireBodarider() {
    requireLogin();
    if (!isBodarider()) {
        header('Location: ' . BASE_URL . 'dashboard.php');
        exit();
    }
}

// -------------------- Utility Functions --------------------

function sanitize($data) {
    return htmlspecialchars(strip_tags(trim($data)));
}

function jsonResponse($data, $statusCode = 200) {
    http_response_code($statusCode);
    header('Content-Type: application/json');
    echo json_encode($data);
    exit();
}
?>
