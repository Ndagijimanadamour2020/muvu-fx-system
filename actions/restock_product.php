<?php
session_start();
require_once '../includes/config.php';
requireAdmin();

header('Content-Type: text/plain'); // for simple success/error

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo 'Method not allowed';
    exit;
}

$product_id = isset($_POST['product_id']) ? (int)$_POST['product_id'] : 0;
$quantity = isset($_POST['quantity']) ? (int)$_POST['quantity'] : 0;
$buying_price = isset($_POST['buying_price']) && $_POST['buying_price'] !== '' ? (float)$_POST['buying_price'] : null;

if ($product_id <= 0 || $quantity <= 0) {
    http_response_code(400);
    echo 'Invalid product ID or quantity';
    exit;
}

// Start transaction
mysqli_begin_transaction($conn);

try {
    // Update quantity
    $update_query = "UPDATE products SET quantity = quantity + ? WHERE id = ?";
    $stmt = mysqli_prepare($conn, $update_query);
    mysqli_stmt_bind_param($stmt, 'ii', $quantity, $product_id);
    if (!mysqli_stmt_execute($stmt)) {
        throw new Exception('Failed to update quantity');
    }

    // Update buying price if provided
    if ($buying_price !== null && $buying_price > 0) {
        $price_query = "UPDATE products SET buying_price = ? WHERE id = ?";
        $stmt = mysqli_prepare($conn, $price_query);
        mysqli_stmt_bind_param($stmt, 'di', $buying_price, $product_id);
        if (!mysqli_stmt_execute($stmt)) {
            throw new Exception('Failed to update buying price');
        }
    }

    // Insert into product_history
    $history_query = "INSERT INTO product_history (product_id, quantity, total_cost, restock_date) VALUES (?, ?, ?, NOW())";
    $total_cost = $quantity * ($buying_price ?? 0);
    $stmt = mysqli_prepare($conn, $history_query);
    mysqli_stmt_bind_param($stmt, 'iid', $product_id, $quantity, $total_cost);
    if (!mysqli_stmt_execute($stmt)) {
        throw new Exception('Failed to record history');
    }

    mysqli_commit($conn);

    logActivity($_SESSION['user_id'], 'Restock Product', "Added $quantity units to product ID: $product_id");
    echo 'success';
} catch (Exception $e) {
    mysqli_rollback($conn);
    http_response_code(500);
    echo 'error: ' . $e->getMessage();
}
?>