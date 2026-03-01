<?php
session_start();
require_once '../includes/config.php';
requireAdmin();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $product_id = (int)$_POST['product_id'];
    
    // Check if product has sales
    $check_query = "SELECT COUNT(*) as count FROM sales WHERE product_id = $product_id";
    $check_result = mysqli_query($conn, $check_query);
    $check = mysqli_fetch_assoc($check_result);
    
    if ($check['count'] > 0) {
        echo "has_sales";
        exit();
    }
    
    $query = "DELETE FROM products WHERE id = $product_id";
    
    if (mysqli_query($conn, $query)) {
        logActivity($_SESSION['user_id'], 'Delete Product', "Deleted product ID: $product_id");
        echo "success";
    } else {
        echo "error";
    }
}
?>