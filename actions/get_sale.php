<?php
session_start();
require_once '../includes/config.php';
requireLogin();

header('Content-Type: application/json');

if (!isset($_POST['sale_id'])) {
    http_response_code(400);
    echo json_encode(['error' => 'Sale ID not provided']);
    exit;
}

$sale_id = (int)$_POST['sale_id'];

$query = "SELECT s.*, p.product_name, p.quantity as current_stock, u.full_name 
          FROM sales s 
          JOIN products p ON s.product_id = p.id 
          LEFT JOIN users u ON s.sold_by = u.id 
          WHERE s.id = ?";
$stmt = mysqli_prepare($conn, $query);
mysqli_stmt_bind_param($stmt, 'i', $sale_id);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);

if ($row = mysqli_fetch_assoc($result)) {
    echo json_encode($row);
} else {
    http_response_code(404);
    echo json_encode(['error' => 'Sale not found']);
}
?>