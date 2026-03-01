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

// Expect POST data like: restock[1][quantity]=10&restock[1][buying_price]=500&...
if (empty($_POST['restock']) || !is_array($_POST['restock'])) {
    http_response_code(400);
    echo 'No restock data provided';
    exit;
}

$restock_data = $_POST['restock'];

// Start transaction
mysqli_begin_transaction($conn);

try {
    foreach ($restock_data as $product_id => $item) {
        $product_id = (int)$product_id;
        $quantity = isset($item['quantity']) ? (int)$item['quantity'] : 0;
        $buying_price = isset($item['buying_price']) && $item['buying_price'] !== '' ? (float)$item['buying_price'] : null;

        if ($quantity <= 0) {
            continue; // skip zero quantities
        }

        // Update quantity
        $update_query = "UPDATE products SET quantity = quantity + ? WHERE id = ?";
        $stmt = mysqli_prepare($conn, $update_query);
        mysqli_stmt_bind_param($stmt, 'ii', $quantity, $product_id);
        if (!mysqli_stmt_execute($stmt)) {
            throw new Exception("Failed to update product ID $product_id");
        }

        // Update buying price if provided
        if ($buying_price !== null && $buying_price > 0) {
            $price_query = "UPDATE products SET buying_price = ? WHERE id = ?";
            $stmt = mysqli_prepare($conn, $price_query);
            mysqli_stmt_bind_param($stmt, 'di', $buying_price, $product_id);
            if (!mysqli_stmt_execute($stmt)) {
                throw new Exception("Failed to update buying price for product ID $product_id");
            }
        }

        // Record in product_history
        $total_cost = $quantity * ($buying_price ?? 0);
        $history_query = "INSERT INTO product_history (product_id, quantity, total_cost, restock_date) VALUES (?, ?, ?, NOW())";
        $stmt = mysqli_prepare($conn, $history_query);
        mysqli_stmt_bind_param($stmt, 'iid', $product_id, $quantity, $total_cost);
        if (!mysqli_stmt_execute($stmt)) {
            throw new Exception("Failed to record history for product ID $product_id");
        }
    }

    mysqli_commit($conn);
    logActivity($_SESSION['user_id'], 'Bulk Restock', 'Processed bulk restock');
    echo 'success';
} catch (Exception $e) {
    mysqli_rollback($conn);
    http_response_code(500);
    echo 'error: ' . $e->getMessage();
}
?>