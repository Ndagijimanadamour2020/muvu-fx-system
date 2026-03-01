<?php
require_once '../includes/config.php';

if (isset($_SESSION['user_id'])) {
    $log_query = "INSERT INTO activity_log (user_id, action, details, ip_address) VALUES (?, 'Logout', 'User logged out', ?)";
    $log_stmt = mysqli_prepare($conn, $log_query);
    $ip = $_SERVER['REMOTE_ADDR'] ?? '';
    mysqli_stmt_bind_param($log_stmt, 'is', $_SESSION['user_id'], $ip);
    mysqli_stmt_execute($log_stmt);
}

$_SESSION = array();
if (ini_get("session.use_cookies")) {
    $params = session_get_cookie_params();
    setcookie(session_name(), '', time() - 42000,
        $params["path"], $params["domain"],
        $params["secure"], $params["httponly"]
    );
}
session_destroy();

header('Location: ../login.php');
exit;