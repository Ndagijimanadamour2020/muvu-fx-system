<?php
require_once '../includes/config.php';
requireAdmin(); // Double-check

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: ../register.php');
    exit;
}

$username = trim($_POST['username'] ?? '');
$password = $_POST['password'] ?? '';
$full_name = trim($_POST['full_name'] ?? '');
$email = trim($_POST['email'] ?? '');
$phone = trim($_POST['phone'] ?? '');
$role = $_POST['role'] ?? 'staff';

if (empty($username) || empty($password) || empty($full_name) || empty($email)) {
    header('Location: ../register.php?error=All fields except phone are required');
    exit;
}

if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    header('Location: ../register.php?error=Invalid email format');
    exit;
}

// Check uniqueness
$check_query = "SELECT id FROM users WHERE username = ? OR email = ?";
$check_stmt = mysqli_prepare($conn, $check_query);
mysqli_stmt_bind_param($check_stmt, 'ss', $username, $email);
mysqli_stmt_execute($check_stmt);
mysqli_stmt_store_result($check_stmt);
if (mysqli_stmt_num_rows($check_stmt) > 0) {
    header('Location: ../register.php?error=Username or email already exists');
    exit;
}

$hashed_password = password_hash($password, PASSWORD_DEFAULT);

$insert_query = "INSERT INTO users (username, password, full_name, email, phone, role) VALUES (?, ?, ?, ?, ?, ?)";
$insert_stmt = mysqli_prepare($conn, $insert_query);
mysqli_stmt_bind_param($insert_stmt, 'ssssss', $username, $hashed_password, $full_name, $email, $phone, $role);

if (mysqli_stmt_execute($insert_stmt)) {
    $new_user_id = mysqli_insert_id($conn);

    // Log activity using the helper function
    logActivity($_SESSION['user_id'], 'User Registration', "Registered new user: $username");

    header('Location: ../register.php?success=1');
    exit;
} else {
    header('Location: ../register.php?error=Registration failed: ' . mysqli_error($conn));
    exit;
}
?>