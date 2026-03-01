<?php
session_start();
require_once '../includes/config.php';
requireAdmin(); // <-- changed from requireLogin()

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $product_name = mysqli_real_escape_string($conn, $_POST['product_name']);
    $description = mysqli_real_escape_string($conn, $_POST['description']);
    $quantity = (int)$_POST['quantity'];
    $low_stock_threshold = (int)$_POST['low_stock_threshold'];
    $buying_price = (float)$_POST['buying_price'];
    $selling_price = (float)$_POST['selling_price'];
    
    $query = "INSERT INTO products (product_name, description, quantity, low_stock_threshold, buying_price, selling_price) 
              VALUES ('$product_name', '$description', $quantity, $low_stock_threshold, $buying_price, $selling_price)";
    
    if (mysqli_query($conn, $query)) {
        $product_id = mysqli_insert_id($conn);
        logActivity($_SESSION['user_id'], 'Add Product', "Added product: $product_name");
        $_SESSION['success'] = "Product added successfully";
    } else {
        $_SESSION['error'] = "Failed to add product";
    }
    
    header('Location: ../pages/products.php');
    exit();
} else {
    header('Location: ../pages/products.php');
    exit();
}
?>