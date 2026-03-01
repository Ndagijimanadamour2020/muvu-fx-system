<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

define('DB_HOST', 'localhost');
define('DB_USER', 'root');
define('DB_PASS', '');
define('DB_NAME', 'muvu_fx_db');

$conn = mysqli_connect(DB_HOST, DB_USER, DB_PASS, DB_NAME);
if (!$conn) {
    die("Connection failed: " . mysqli_connect_error());
}

date_default_timezone_set('Africa/Kigali');

define('BUSINESS_NAME', 'MUVU FX');
define('BUSINESS_ADDRESS', 'Gatsibo District, Kabarore Sector, Malimba Cell');
define('BUSINESS_PHONE', '0786874837');

function isLoggedIn() {
    return isset($_SESSION['user_id']);
}

function isAdmin() {
    return isset($_SESSION['role']) && $_SESSION['role'] === 'admin';
}

function requireLogin() {
    if (!isLoggedIn()) {
        header('Location: ' . getBaseUrl() . '/login.php');
        exit();
    }
}

function requireAdmin() {
    requireLogin();
    if (!isAdmin()) {
        header('Location: ' . getBaseUrl() . '/index.php');
        exit();
    }
}

function getBaseUrl() {
    $protocol = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https' : 'http';
    $host = $_SERVER['HTTP_HOST'];
    $script = dirname($_SERVER['SCRIPT_NAME']);
    return rtrim($protocol . '://' . $host . $script, '/');
}

/**
 * Log user activity into the activity_log table
 * 
 * @param int $user_id The ID of the user performing the action
 * @param string $action A short description of the action (e.g., 'Add Sale', 'Login')
 * @param string|null $details Additional details about the action (optional)
 * @return bool True on success, false on failure
 */
function logActivity($user_id, $action, $details = null) {
    global $conn;
    
    $ip_address = $_SERVER['REMOTE_ADDR'] ?? '';
    $created_at = date('Y-m-d H:i:s');
    
    $query = "INSERT INTO activity_log (user_id, action, details, ip_address, created_at) 
              VALUES (?, ?, ?, ?, ?)";
    
    $stmt = mysqli_prepare($conn, $query);
    if (!$stmt) {
        error_log("logActivity prepare failed: " . mysqli_error($conn));
        return false;
    }
    
    mysqli_stmt_bind_param($stmt, 'issss', $user_id, $action, $details, $ip_address, $created_at);
    
    $result = mysqli_stmt_execute($stmt);
    if (!$result) {
        error_log("logActivity execute failed: " . mysqli_stmt_error($stmt));
    }
    
    mysqli_stmt_close($stmt);
    return $result;
}
?>
