<?php
session_start();
require_once '../includes/config.php';
requireLogin();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: ../pages/sales.php');
    exit;
}

$sale_id = (int)$_POST['sale_id'];
$product_id = (int)$_POST['product_id'];
$quantity = (int)$_POST['quantity'];

// Basic validation
if ($sale_id <= 0 || $product_id <= 0 || $quantity <= 0) {
    $_SESSION['error'] = "Invalid input.";
    header('Location: ../pages/sales.php');
    exit;
}

// Get original sale details
$original_query = "SELECT * FROM sales WHERE id = ?";
$stmt = mysqli_prepare($conn, $original_query);
mysqli_stmt_bind_param($stmt, 'i', $sale_id);
mysqli_stmt_execute($stmt);
$original_result = mysqli_stmt_get_result($stmt);
$original_sale = mysqli_fetch_assoc($original_result);

if (!$original_sale) {
    $_SESSION['error'] = "Sale not found.";
    header('Location: ../pages/sales.php');
    exit;
}

// Get current product details (including stock and prices)
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

// Calculate stock difference: first return old quantity, then deduct new quantity
$quantity_difference = $quantity - $original_sale['quantity'];

// If increasing quantity, check if enough stock
if ($quantity_difference > 0 && $product['quantity'] < $quantity_difference) {
    $_SESSION['error'] = "Insufficient stock! Available: " . $product['quantity'];
    header('Location: ../pages/sales.php');
    exit;
}

// Use current selling price for unit price (consistent with frontend)
$unit_price = $product['selling_price'];
$total_amount = $quantity * $unit_price;
$profit = ($unit_price - $product['buying_price']) * $quantity;

// Start transaction
mysqli_begin_transaction($conn);

try {
    // Adjust stock: first return old quantity, then deduct new quantity
    // Better: net adjustment
    if ($quantity_difference != 0) {
        if ($quantity_difference > 0) {
            // Need more stock – deduct the difference
            $stock_query = "UPDATE products SET quantity = quantity - ? WHERE id = ?";
            $stmt = mysqli_prepare($conn, $stock_query);
            mysqli_stmt_bind_param($stmt, 'ii', $quantity_difference, $product_id);
        } else {
            // Returning stock – add back the absolute difference
            $abs_diff = abs($quantity_difference);
            $stock_query = "UPDATE products SET quantity = quantity + ? WHERE id = ?";
            $stmt = mysqli_prepare($conn, $stock_query);
            mysqli_stmt_bind_param($stmt, 'ii', $abs_diff, $product_id);
        }
        mysqli_stmt_execute($stmt);
    }

    // Update sale record
    $update_query = "UPDATE sales SET 
                      product_id = ?,
                      quantity = ?,
                      unit_price = ?,
                      total_amount = ?,
                      profit = ?
                    WHERE id = ?";
    $stmt = mysqli_prepare($conn, $update_query);
    mysqli_stmt_bind_param($stmt, 'iiddii', $product_id, $quantity, $unit_price, $total_amount, $profit, $sale_id);
    mysqli_stmt_execute($stmt);

    mysqli_commit($conn);

    logActivity($_SESSION['user_id'], 'Edit Sale', "Edited sale ID $sale_id: quantity $original_sale[quantity] → $quantity");
    $_SESSION['success'] = "Sale updated successfully.";
} catch (Exception $e) {
    mysqli_rollback($conn);
    $_SESSION['error'] = "Failed to update sale: " . $e->getMessage();
}

header('Location: ../pages/sales.php');
exit;
?>