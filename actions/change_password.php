<?php
session_start();
require_once '../includes/config.php';
requireLogin();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $user_id = $_SESSION['user_id'];
    $current_password = $_POST['current_password'];
    $new_password = $_POST['new_password'];
    $confirm_password = $_POST['confirm_password'];
    
    // Verify new passwords match
    if ($new_password !== $confirm_password) {
        $_SESSION['error'] = "New passwords do not match";
        header('Location: ../pages/profile.php');
        exit();
    }
    
    // Get current password from database
    $query = "SELECT password FROM users WHERE id = $user_id";
    $result = mysqli_query($conn, $query);
    $user = mysqli_fetch_assoc($result);
    
    // Verify current password
    if (!password_verify($current_password, $user['password'])) {
        $_SESSION['error'] = "Current password is incorrect";
        header('Location: ../pages/profile.php');
        exit();
    }
    
    // Update password
    $hashed_password = password_hash($new_password, PASSWORD_DEFAULT);
    $update_query = "UPDATE users SET password = '$hashed_password' WHERE id = $user_id";
    
    if (mysqli_query($conn, $update_query)) {
        logActivity($_SESSION['user_id'], 'Change Password', 'Password changed successfully');
        $_SESSION['success'] = "Password changed successfully";
    } else {
        $_SESSION['error'] = "Failed to change password";
    }
    
    header('Location: ../pages/profile.php');
    exit();
}
?>