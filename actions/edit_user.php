<?php
require_once '../includes/config.php';
requireAdmin();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    die('Method not allowed');
}

$user_id = intval($_POST['user_id'] ?? 0);
$full_name = trim($_POST['full_name'] ?? '');
$email = trim($_POST['email'] ?? '');
$phone = trim($_POST['phone'] ?? '');
$role = $_POST['role'] ?? 'staff';

if (!$user_id || !$full_name || !$email) {
    http_response_code(400);
    die('Missing required fields');
}

// Update user
$query = "UPDATE users SET full_name = ?, email = ?, phone = ?, role = ? WHERE id = ?";
$stmt = mysqli_prepare($conn, $query);
mysqli_stmt_bind_param($stmt, 'ssssi', $full_name, $email, $phone, $role, $user_id);

if (mysqli_stmt_execute($stmt)) {
    // Log activity
    logActivity($_SESSION['user_id'], 'Edit User', 'Edited user ID: ' . $user_id);
    echo 'success';
} else {
    http_response_code(500);
    echo 'Update failed: ' . mysqli_error($conn);
}
?>