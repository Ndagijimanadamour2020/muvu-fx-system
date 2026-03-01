<?php
session_start();
require_once '../includes/config.php';
requireAdmin(); // Only admin can delete

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo "Method not allowed";
    exit;
}

$sale_id = (int)$_POST['sale_id'];
if ($sale_id <= 0) {
    http_response_code(400);
    echo "Invalid sale ID";
    exit;
}

// Get sale details before deletion
$sale_query = "SELECT product_id, quantity FROM sales WHERE id = ?";
$stmt = mysqli_prepare($conn, $sale_query);
mysqli_stmt_bind_param($stmt, 'i', $sale_id);
mysqli_stmt_execute($stmt);
$sale_result = mysqli_stmt_get_result($stmt);
$sale = mysqli_fetch_assoc($sale_result);

if (!$sale) {
    http_response_code(404);
    echo "Sale not found";
    exit;
}

// Start transaction
mysqli_begin_transaction($conn);

try {
    // Restore product quantity
    $update_query = "UPDATE products SET quantity = quantity + ? WHERE id = ?";
    $stmt = mysqli_prepare($conn, $update_query);
    mysqli_stmt_bind_param($stmt, 'ii', $sale['quantity'], $sale['product_id']);
    mysqli_stmt_execute($stmt);

    // Delete sale
    $delete_query = "DELETE FROM sales WHERE id = ?";
    $stmt = mysqli_prepare($conn, $delete_query);
    mysqli_stmt_bind_param($stmt, 'i', $sale_id);
    mysqli_stmt_execute($stmt);

    mysqli_commit($conn);

    logActivity($_SESSION['user_id'], 'Delete Sale', "Deleted sale ID: $sale_id");
    echo "success";
} catch (Exception $e) {
    mysqli_rollback($conn);
    http_response_code(500);
    echo "error";
}
?>