<?php
session_start();
require_once '../includes/config.php';
requireAdmin();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $user_id = (int)$_POST['user_id'];
    
    // Don't allow deleting yourself
    if ($user_id == $_SESSION['user_id']) {
        echo "cannot_delete_self";
        exit();
    }
    
    $query = "DELETE FROM users WHERE id = $user_id";
    
    if (mysqli_query($conn, $query)) {
        logActivity($_SESSION['user_id'], 'Delete User', "Deleted user ID: $user_id");
        echo "success";
    } else {
        echo "error";
    }
}
?>