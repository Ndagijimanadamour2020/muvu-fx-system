<?php
session_start();
require_once '../includes/config.php';
requireLogin();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $user_id = $_SESSION['user_id'];
    $full_name = mysqli_real_escape_string($conn, $_POST['full_name']);
    $email = mysqli_real_escape_string($conn, $_POST['email']);
    $phone = mysqli_real_escape_string($conn, $_POST['phone']);
    
    $query = "UPDATE users SET 
              full_name = '$full_name',
              email = '$email',
              phone = '$phone'
              WHERE id = $user_id";
    
    if (mysqli_query($conn, $query)) {
        $_SESSION['full_name'] = $full_name; // update session
        logActivity($_SESSION['user_id'], 'Edit Profile', 'Profile updated successfully');
        $_SESSION['success'] = "Profile updated successfully";
    } else {
        $_SESSION['error'] = "Failed to update profile";
    }
    
    header('Location: ../pages/profile.php');
    exit();
}
?>