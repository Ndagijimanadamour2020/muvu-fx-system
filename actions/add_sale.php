<?php
session_start();
require_once '../includes/config.php';
requireLogin();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: ../pages/sales.php');
    exit;
}

$product_id = (int)$_POST['product_id'];
$quantity = (int)$_POST['quantity'];

// Basic validation
if ($product_id <= 0 || $quantity <= 0) {
    $_SESSION['error'] = "Invalid product or quantity.";
    header('Location: ../pages/sales.php');
    exit;
}

// Get product details (buying price, selling price, current stock)
$product_query = "SELECT buying_price, selling_price, quantity FROM products WHERE id = ?";
$stmt = mysqli_prepare($conn, $product_query);
mysqli_stmt_bind_param($stmt, 'i', $product_id);
mysqli_stmt_execute($stmt);
$product_result = mysqli_stmt_get_result($stmt);
$product = mysqli_fetch_assoc($product_result);

if (!$product) {
    $_SESSION['error'] = "Product not found.";
    header('Location: ../pages/sales.php');
    exit;
}

// Check stock
if ($product['quantity'] < $quantity) {
    $_SESSION['error'] = "Insufficient stock! Available: " . $product['quantity'];
    header('Location: ../pages/sales.php');
    exit;
}

$unit_price = $product['selling_price'];
$buying_price = $product['buying_price'];
$total_amount = $quantity * $unit_price;
$profit = ($unit_price - $buying_price) * $quantity;
$sold_by = $_SESSION['user_id'];

// Start transaction
mysqli_begin_transaction($conn);

try {
    // Insert sale
    $insert_query = "INSERT INTO sales (product_id, quantity, unit_price, total_amount, profit, sold_by) 
                     VALUES (?, ?, ?, ?, ?, ?)";
    $stmt = mysqli_prepare($conn, $insert_query);
    mysqli_stmt_bind_param($stmt, 'iidddi', $product_id, $quantity, $unit_price, $total_amount, $profit, $sold_by);
    mysqli_stmt_execute($stmt);

    // Update product quantity
    $update_query = "UPDATE products SET quantity = quantity - ? WHERE id = ?";
    $stmt = mysqli_prepare($conn, $update_query);
    mysqli_stmt_bind_param($stmt, 'ii', $quantity, $product_id);
    mysqli_stmt_execute($stmt);

    mysqli_commit($conn);

    logActivity($_SESSION['user_id'], 'Add Sale', "Sold $quantity of product ID $product_id");
    $_SESSION['success'] = "Sale recorded successfully.";
} catch (Exception $e) {
    mysqli_rollback($conn);
    $_SESSION['error'] = "Failed to record sale: " . $e->getMessage();
}

header('Location: ../pages/sales.php');
exit;
?>