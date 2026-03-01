<?php
require_once '../includes/config.php';
requireAdmin();

if (!isset($_POST['user_id'])) {
    die('User ID not provided');
}

$user_id = intval($_POST['user_id']);

// Get user details for display
$user_query = "SELECT full_name FROM users WHERE id = ?";
$stmt = mysqli_prepare($conn, $user_query);
mysqli_stmt_bind_param($stmt, 'i', $user_id);
mysqli_stmt_execute($stmt);
$user_result = mysqli_stmt_get_result($stmt);
$user = mysqli_fetch_assoc($user_result);

// Get activity logs
$activity_query = "SELECT * FROM activity_log WHERE user_id = ? ORDER BY created_at DESC LIMIT 50";
$stmt = mysqli_prepare($conn, $activity_query);
mysqli_stmt_bind_param($stmt, 'i', $user_id);
mysqli_stmt_execute($stmt);
$activity_result = mysqli_stmt_get_result($stmt);
?>

<div class="activity-log">
    <h6 class="mb-3">Activity for: <?php echo htmlspecialchars($user['full_name']); ?></h6>
    
    <?php if (mysqli_num_rows($activity_result) > 0): ?>
        <table class="table table-sm table-bordered">
            <thead>
                <tr>
                    <th>Action</th>
                    <th>Details</th>
                    <th>IP Address</th>
                    <th>Date/Time</th>
                </tr>
            </thead>
            <tbody>
                <?php while ($log = mysqli_fetch_assoc($activity_result)): ?>
                <tr>
                    <td><?php echo htmlspecialchars($log['action']); ?></td>
                    <td><?php echo htmlspecialchars($log['details'] ?: '-'); ?></td>
                    <td><?php echo htmlspecialchars($log['ip_address'] ?: '-'); ?></td>
                    <td><?php echo date('Y-m-d H:i:s', strtotime($log['created_at'])); ?></td>
                </tr>
                <?php endwhile; ?>
            </tbody>
        </table>
    <?php else: ?>
        <p class="text-muted text-center">No activity recorded for this user.</p>
    <?php endif; ?>
</div>