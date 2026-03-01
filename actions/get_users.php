<?php
require_once '../includes/config.php';
requireAdmin(); // Only admin can fetch users

if (!isset($_POST['user_id'])) {
    http_response_code(400);
    echo json_encode(['error' => 'User ID not provided']);
    exit;
}

$user_id = intval($_POST['user_id']);

$query = "SELECT id, username, full_name, email, phone, role FROM users WHERE id = ?";
$stmt = mysqli_prepare($conn, $query);
mysqli_stmt_bind_param($stmt, 'i', $user_id);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);
$user = mysqli_fetch_assoc($result);

if (!$user) {
    http_response_code(404);
    echo json_encode(['error' => 'User not found']);
    exit;
}

// Set proper JSON header
header('Content-Type: application/json');
echo json_encode($user);
?>