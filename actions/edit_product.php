<?php
session_start();
require_once '../includes/config.php';
requireAdmin();

header('Content-Type: text/plain');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo 'Method not allowed';
    exit;
}

$product_id = isset($_POST['product_id']) ? (int)$_POST['product_id'] : 0;
$product_name = isset($_POST['product_name']) ? trim($_POST['product_name']) : '';
$description = isset($_POST['description']) ? trim($_POST['description']) : '';
$quantity = isset($_POST['quantity']) ? (int)$_POST['quantity'] : 0;
$low_stock_threshold = isset($_POST['low_stock_threshold']) ? (int)$_POST['low_stock_threshold'] : 0;
$buying_price = isset($_POST['buying_price']) ? (float)$_POST['buying_price'] : 0;
$selling_price = isset($_POST['selling_price']) ? (float)$_POST['selling_price'] : 0;

if ($product_id <= 0 || empty($product_name) || $selling_price <= 0) {
    http_response_code(400);
    echo 'Invalid input';
    exit;
}

$query = "UPDATE products SET 
          product_name = ?,
          description = ?,
          quantity = ?,
          low_stock_threshold = ?,
          buying_price = ?,
          selling_price = ?
          WHERE id = ?";

$stmt = mysqli_prepare($conn, $query);
mysqli_stmt_bind_param($stmt, 'ssiiddi', $product_name, $description, $quantity, $low_stock_threshold, $buying_price, $selling_price, $product_id);

if (mysqli_stmt_execute($stmt)) {
    logActivity($_SESSION['user_id'], 'Edit Product', "Edited product ID: $product_id");
    echo 'success';
} else {
    http_response_code(500);
    echo 'error: ' . mysqli_error($conn);
}
?>