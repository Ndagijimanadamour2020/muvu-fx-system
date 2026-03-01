<?php
session_start();
require_once '../includes/config.php';
requireLogin();

if (!isset($_POST['product_id']) || !is_numeric($_POST['product_id'])) {
    http_response_code(400);
    echo 'Invalid product ID';
    exit;
}

$product_id = (int)$_POST['product_id'];

// Get product name
$prod_query = "SELECT product_name FROM products WHERE id = ?";
$stmt = mysqli_prepare($conn, $prod_query);
mysqli_stmt_bind_param($stmt, 'i', $product_id);
mysqli_stmt_execute($stmt);
$prod_result = mysqli_stmt_get_result($stmt);
$product = mysqli_fetch_assoc($prod_result);
$product_name = $product ? $product['product_name'] : 'Unknown';

// Combine sales and restocks
$query = "SELECT 
            'SALE' as type,
            sale_date as movement_date,
            quantity,
            total_amount as amount
          FROM sales
          WHERE product_id = ?
          UNION ALL
          SELECT 
            'RESTOCK' as type,
            restock_date as movement_date,
            quantity,
            total_cost as amount
          FROM product_history
          WHERE product_id = ?
          ORDER BY movement_date DESC";

$stmt = mysqli_prepare($conn, $query);
mysqli_stmt_bind_param($stmt, 'ii', $product_id, $product_id);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);

// Build HTML table
$html = '<h5 class="mb-3">Stock History for ' . htmlspecialchars($product_name) . '</h5>';
$html .= '<div class="table-responsive">';
$html .= '<table class="table table-sm table-hover">';
$html .= '<thead><tr><th>Date</th><th>Type</th><th>Quantity</th><th>Amount</th></tr></thead>';
$html .= '<tbody>';

if (mysqli_num_rows($result) > 0) {
    while ($row = mysqli_fetch_assoc($result)) {
        $date = date('Y-m-d H:i', strtotime($row['movement_date']));
        $type = $row['type'] == 'SALE' ? '<span class="badge bg-danger">Sale</span>' : '<span class="badge bg-success">Restock</span>';
        $quantity = $row['type'] == 'SALE' ? '-' . $row['quantity'] : '+' . $row['quantity'];
        $amount = number_format($row['amount'], 0) . ' RWF';
        $html .= "<tr>
                    <td>{$date}</td>
                    <td>{$type}</td>
                    <td class=\"" . ($row['type'] == 'SALE' ? 'text-danger' : 'text-success') . " fw-bold\">{$quantity}</td>
                    <td>{$amount}</td>
                  </tr>";
    }
} else {
    $html .= '<tr><td colspan="4" class="text-center py-3">No stock movements found.</td></tr>';
}

$html .= '</tbody></table></div>';

echo $html;
?>