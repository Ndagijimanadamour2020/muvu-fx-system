<?php
require_once '../includes/config.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: ../login.php');
    exit;
}

$username = trim($_POST['username'] ?? '');
$password = $_POST['password'] ?? '';

if (empty($username) || empty($password)) {
    header('Location: ../login.php?error=Username and password required');
    exit;
}

$query = "SELECT * FROM users WHERE username = ?";
$stmt = mysqli_prepare($conn, $query);
mysqli_stmt_bind_param($stmt, 's', $username);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);
$user = mysqli_fetch_assoc($result);

if ($user && password_verify($password, $user['password'])) {
    $_SESSION['user_id'] = $user['id'];
    $_SESSION['username'] = $user['username'];
    $_SESSION['full_name'] = $user['full_name'];
    $_SESSION['role'] = $user['role'];

    // Log activity
    $log_query = "INSERT INTO activity_log (user_id, action, details, ip_address) VALUES (?, 'Login', 'User logged in', ?)";
    $log_stmt = mysqli_prepare($conn, $log_query);
    $ip = $_SERVER['REMOTE_ADDR'] ?? '';
    mysqli_stmt_bind_param($log_stmt, 'is', $user['id'], $ip);
    mysqli_stmt_execute($log_stmt);

    header('Location: ../pages/dashboard.php');
    exit;
} else {
    header('Location: ../login.php?error=Invalid username or password');
    exit;
}