<?php
require_once '../includes/config.php';
requireLogin();

$user_id = $_SESSION['user_id'];

// Get user details
$user_query = "SELECT * FROM users WHERE id = ?";
$stmt = mysqli_prepare($conn, $user_query);
mysqli_stmt_bind_param($stmt, 'i', $user_id);
mysqli_stmt_execute($stmt);
$user_result = mysqli_stmt_get_result($stmt);
$user = mysqli_fetch_assoc($user_result);

// Get user activity (last 20 actions)
$activity_query = "SELECT * FROM activity_log WHERE user_id = ? ORDER BY created_at ASC LIMIT 20";
$stmt = mysqli_prepare($conn, $activity_query);
mysqli_stmt_bind_param($stmt, 'i', $user_id);
mysqli_stmt_execute($stmt);
$activity_result = mysqli_stmt_get_result($stmt);

// Get user sales stats
$sales_stats_query = "SELECT 
                        COUNT(*) as total_sales,
                        COALESCE(SUM(total_amount), 0) as total_amount,
                        COALESCE(SUM(profit), 0) as total_profit,
                        COUNT(DISTINCT DATE(sale_date)) as active_days
                      FROM sales 
                      WHERE sold_by = ?";
$stmt = mysqli_prepare($conn, $sales_stats_query);
mysqli_stmt_bind_param($stmt, 'i', $user_id);
mysqli_stmt_execute($stmt);
$sales_stats_result = mysqli_stmt_get_result($stmt);
$sales_stats = mysqli_fetch_assoc($sales_stats_result);

// Get user's recent sales
$my_sales_query = "SELECT s.*, p.product_name 
                  FROM sales s 
                  JOIN products p ON s.product_id = p.id 
                  WHERE s.sold_by = ? 
                  ORDER BY s.sale_date DESC 
                  LIMIT 10";
$stmt = mysqli_prepare($conn, $my_sales_query);
mysqli_stmt_bind_param($stmt, 'i', $user_id);
mysqli_stmt_execute($stmt);
$my_sales_result = mysqli_stmt_get_result($stmt);

// Current page for active menu
$current_page = basename($_SERVER['PHP_SELF']);

// User info from session
$user_role = $_SESSION['role'] ?? 'guest';
$user_name = $_SESSION['full_name'] ?? 'User';
?>
<?php include '../includes/header.php'; ?>

<!-- Main Layout Container -->
<div class="main-layout">
    <!-- Fixed Left Sidebar -->
    <?php include '../includes/sidebar.php'; ?>

    <!-- Mobile Toggle -->
    <div class="sidebar-toggle" onclick="toggleSidebar()">
        <i class="fas fa-bars"></i>
    </div>

    <!-- Right Main Content -->
    <div class="main-content-right">
        <!-- Top Header -->
        <div class="top-header">
            <div class="header-left">
                <button class="sidebar-toggle-btn" onclick="toggleSidebar()">
                    <i class="fas fa-bars"></i>
                </button>
                <h5 class="page-title"><i class="fas fa-user me-2 text-primary"></i>My Profile</h5>
            </div>
            <div class="header-right">
                <div class="datetime-display">
                    <span class="date me-3"><i class="far fa-calendar-alt text-primary me-1"></i><?php echo date('l, F j, Y'); ?></span>
                    <span class="time"><i class="far fa-clock text-success me-1"></i><?php echo date('h:i A'); ?></span>
                </div>
                <div class="user-profile-dropdown">
                    <div class="dropdown">
                        <button class="btn btn-link dropdown-toggle text-dark" type="button" data-bs-toggle="dropdown">
                            <i class="fas fa-user-circle fa-2x text-primary"></i>
                        </button>
                        <ul class="dropdown-menu dropdown-menu-end">
                            <li><a class="dropdown-item" href="profile.php"><i class="fas fa-user me-2"></i>Profile</a></li>
                            <li><a class="dropdown-item" href="#"><i class="fas fa-cog me-2"></i>Settings</a></li>
                            <li><hr class="dropdown-divider"></li>
                            <li><a class="dropdown-item text-danger" href="../actions/logout.php"><i class="fas fa-sign-out-alt me-2"></i>Logout</a></li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>

        <!-- Content Area -->
        <div class="content-area">
            <!-- Welcome Section (like dashboard) -->
            <div class="welcome-section mb-4">
                <div class="row align-items-center">
                    <div class="col-md-8">
                        <h2 class="mb-2"><i class="fas fa-id-card text-primary me-2"></i>My Profile</h2>
                        <p class="text-white-50"><i class="fas fa-store me-2"></i>Manage your personal information, password, and view your activity</p>
                    </div>
                    <div class="col-md-4 text-end">
                        <div class="business-badge">
                            <span class="badge bg-white text-primary p-3"><i class="fas fa-phone-alt me-2"></i><?php echo BUSINESS_PHONE; ?></span>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Two‑column layout -->
            <div class="row g-4">
                <!-- Left Column: Profile Info + Change Password -->
                <div class="col-lg-4">
                    <!-- Profile Card -->
                    <div class="card profile-card mb-4">
                        <div class="card-header bg-primary text-white text-center py-3">
                            <h5 class="mb-0"><i class="fas fa-id-badge me-2"></i>Profile Information</h5>
                        </div>
                        <div class="card-body text-center">
                            <div class="profile-image mb-3">
                                <i class="fas fa-user-circle fa-6x" style="color: #667eea;"></i>
                            </div>
                            <h4><?php echo htmlspecialchars($user['full_name']); ?></h4>
                            <p class="text-muted">
                                <span class="badge bg-<?php echo $user['role'] == 'admin' ? 'danger' : 'info'; ?>">
                                    <?php echo strtoupper($user['role']); ?>
                                </span>
                            </p>
                            <hr>
                            <div class="text-start">
                                <p><i class="fas fa-user me-2 text-primary"></i> <strong>Username:</strong> <?php echo htmlspecialchars($user['username']); ?></p>
                                <p><i class="fas fa-envelope me-2 text-primary"></i> <strong>Email:</strong> <?php echo htmlspecialchars($user['email']); ?></p>
                                <p><i class="fas fa-phone me-2 text-primary"></i> <strong>Phone:</strong> <?php echo htmlspecialchars($user['phone'] ?: 'Not provided'); ?></p>
                                <p><i class="fas fa-calendar me-2 text-primary"></i> <strong>Member Since:</strong> <?php echo date('F j, Y', strtotime($user['created_at'])); ?></p>
                            </div>
                            <button class="btn btn-warning w-100" data-bs-toggle="modal" data-bs-target="#editProfileModal">
                                <i class="fas fa-edit me-2"></i>Edit Profile
                            </button>
                        </div>
                    </div>

                    <!-- Change Password Card -->
                    <div class="card password-card mb-4">
                        <div class="card-header bg-warning text-white">
                            <h5 class="mb-0"><i class="fas fa-lock me-2"></i>Change Password</h5>
                        </div>
                        <div class="card-body">
                            <form id="changePasswordForm" action="../actions/change_password.php" method="POST">
                                <div class="mb-3">
                                    <label for="current_password" class="form-label">Current Password</label>
                                    <input type="password" class="form-control" id="current_password" name="current_password" required>
                                </div>
                                <div class="mb-3">
                                    <label for="new_password" class="form-label">New Password</label>
                                    <input type="password" class="form-control" id="new_password" name="new_password" required>
                                </div>
                                <div class="mb-3">
                                    <label for="confirm_password" class="form-label">Confirm New Password</label>
                                    <input type="password" class="form-control" id="confirm_password" name="confirm_password" required>
                                </div>
                                <button type="submit" class="btn btn-warning w-100"><i class="fas fa-sync-alt me-2"></i>Update Password</button>
                            </form>
                        </div>
                    </div>
                </div>

                <!-- Right Column: Activity + Recent Sales -->
                <div class="col-lg-8">
                    <!-- Recent Activity Card -->
                    <div class="card mb-4">
                        <div class="card-header bg-info text-white">
                            <h5 class="mb-0"><i class="fas fa-history me-2"></i>Recent Activity</h5>
                        </div>
                        <div class="card-body">
                            <?php if (mysqli_num_rows($activity_result) > 0): ?>
                                <div class="timeline">
                                    <?php while ($activity = mysqli_fetch_assoc($activity_result)): ?>
                                        <div class="timeline-item">
                                            <div class="timeline-badge"><i class="fas fa-circle text-primary"></i></div>
                                            <div class="timeline-content">
                                                <h6><?php echo htmlspecialchars($activity['action']); ?></h6>
                                                <p class="text-muted">
                                                    <small>
                                                        <i class="fas fa-clock me-1"></i><?php echo date('Y-m-d H:i:s', strtotime($activity['created_at'])); ?>
                                                        <?php if ($activity['details']): ?>
                                                            <br><i class="fas fa-info-circle me-1"></i><?php echo htmlspecialchars($activity['details']); ?>
                                                        <?php endif; ?>
                                                    </small>
                                                </p>
                                            </div>
                                        </div>
                                    <?php endwhile; ?>
                                </div>
                            <?php else: ?>
                                <p class="text-center text-muted my-3">No recent activity found</p>
                            <?php endif; ?>
                        </div>
                    </div>

                    <!-- My Recent Sales Card -->
                    <div class="card">
                        <div class="card-header bg-success text-white">
                            <h5 class="mb-0"><i class="fas fa-history me-2"></i>My Recent Sales</h5>
                        </div>
                        <div class="card-body">
                            <?php if (mysqli_num_rows($my_sales_result) > 0): ?>
                                <div class="table-responsive">
                                    <table class="table table-hover">
                                        <thead>
                                            <tr>
                                                <th>Date & Time</th>
                                                <th>Product</th>
                                                <th>Qty</th>
                                                <th>Total</th>
                                                <th>Profit</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php while ($sale = mysqli_fetch_assoc($my_sales_result)): ?>
                                                <tr>
                                                    <td><?php echo date('Y-m-d H:i', strtotime($sale['sale_date'])); ?></td>
                                                    <td><?php echo htmlspecialchars($sale['product_name']); ?></td>
                                                    <td><?php echo $sale['quantity']; ?></td>
                                                    <td class="text-success fw-bold"><?php echo number_format($sale['total_amount'], 0); ?> RWF</td>
                                                    <td class="text-info"><?php echo number_format($sale['profit'], 0); ?> RWF</td>
                                                </tr>
                                            <?php endwhile; ?>
                                        </tbody>
                                    </table>
                                </div>
                            <?php else: ?>
                                <p class="text-center text-muted my-3">No sales recorded yet</p>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Footer -->
        <footer class="main-footer">
            <div class="footer-content">
                <span>&copy; <?php echo date('Y'); ?> MUVU FX. All rights reserved.</span>
                <span class="text-muted">Developed for MUVU FX - Gatsibo District</span>
            </div>
        </footer>
    </div>
</div>

<!-- Edit Profile Modal -->
<div class="modal fade" id="editProfileModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header bg-warning text-white">
                <h5 class="modal-title"><i class="fas fa-edit me-2"></i>Edit Profile</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <form id="editProfileForm" action="../actions/edit_profile.php" method="POST">
                    <div class="mb-3">
                        <label for="full_name" class="form-label">Full Name</label>
                        <input type="text" class="form-control" id="full_name" name="full_name" 
                               value="<?php echo htmlspecialchars($user['full_name']); ?>" required>
                    </div>
                    <div class="mb-3">
                        <label for="email" class="form-label">Email</label>
                        <input type="email" class="form-control" id="email" name="email" 
                               value="<?php echo htmlspecialchars($user['email']); ?>" required>
                    </div>
                    <div class="mb-3">
                        <label for="phone" class="form-label">Phone Number</label>
                        <input type="tel" class="form-control" id="phone" name="phone" 
                               value="<?php echo htmlspecialchars($user['phone']); ?>">
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal"><i class="fas fa-times me-2"></i>Cancel</button>
                <button type="submit" form="editProfileForm" class="btn btn-warning"><i class="fas fa-save me-2"></i>Save Changes</button>
            </div>
        </div>
    </div>
</div>

<style>
/* ========== MAIN LAYOUT (copied from products.php) ========== */
.main-layout {
    display: flex;
    min-height: 100vh;
    background: #f4f7fc;
}

/* ========== FIXED LEFT SIDEBAR ========== */
.sidebar {
    position: fixed;
    top: 0;
    left: 0;
    width: 280px;
    height: 100vh;
    background: linear-gradient(180deg, #2c3e50 0%, #1a2634 100%);
    color: white;
    z-index: 1000;
    overflow-y: auto;
    overflow-x: hidden;
    transition: all 0.3s ease;
    box-shadow: 2px 0 20px rgba(0, 0, 0, 0.1);
}

/* Sidebar Header */
.sidebar-header {
    padding: 25px 20px;
    text-align: center;
    border-bottom: 1px solid rgba(255, 255, 255, 0.1);
}

.business-logo i {
    color: #667eea;
    background: rgba(255, 255, 255, 0.1);
    width: 60px;
    height: 60px;
    line-height: 60px;
    border-radius: 50%;
    margin-bottom: 15px;
}

.business-logo h4 {
    font-weight: 600;
    letter-spacing: 1px;
}

/* User Info */
.user-info {
    padding: 20px;
    display: flex;
    align-items: center;
    gap: 15px;
    border-bottom: 1px solid rgba(255, 255, 255, 0.1);
}

.user-avatar i {
    color: #667eea;
    font-size: 45px;
}

.user-details {
    flex: 1;
}

.user-details h6 {
    font-weight: 600;
    margin: 0;
}

/* Sidebar Menu */
.sidebar-menu {
    padding: 20px 0;
    flex: 1;
}

.nav-divider {
    padding: 15px 20px 5px;
}

.divider-text {
    font-size: 12px;
    color: rgba(255, 255, 255, 0.4);
    letter-spacing: 1px;
    font-weight: 600;
}

.sidebar .nav-link {
    display: flex;
    align-items: center;
    padding: 12px 25px;
    color: rgba(255, 255, 255, 0.7);
    transition: all 0.3s ease;
    position: relative;
    margin: 2px 10px;
    border-radius: 8px;
}

.sidebar .nav-link i {
    width: 30px;
    font-size: 18px;
    transition: all 0.3s ease;
}

.sidebar .nav-link span {
    font-size: 14px;
    font-weight: 500;
}

.sidebar .nav-link:hover {
    color: white;
    background: rgba(255, 255, 255, 0.1);
    transform: translateX(5px);
}

.sidebar .nav-link.active {
    color: white;
    background: linear-gradient(90deg, #667eea 0%, #764ba2 100%);
    box-shadow: 0 5px 15px rgba(102, 126, 234, 0.4);
}

.sidebar .nav-link.active::before {
    content: '';
    position: absolute;
    left: 0;
    top: 0;
    height: 100%;
    width: 4px;
    background: white;
    border-radius: 0 4px 4px 0;
}

/* Sidebar Footer */
.sidebar-footer {
    padding: 20px 0;
    border-top: 1px solid rgba(255, 255, 255, 0.1);
}

.business-info {
    padding: 20px 25px;
    font-size: 12px;
    background: rgba(0, 0, 0, 0.2);
    margin-top: 10px;
}

/* Sidebar Collapsed State */
.sidebar.collapsed {
    width: 80px;
}

.sidebar.collapsed .business-logo h4,
.sidebar.collapsed .business-logo small,
.sidebar.collapsed .user-details,
.sidebar.collapsed .nav-link span,
.sidebar.collapsed .divider-text,
.sidebar.collapsed .business-info {
    display: none;
}

.sidebar.collapsed .user-info {
    justify-content: center;
}

.sidebar.collapsed .user-avatar i {
    font-size: 35px;
}

.sidebar.collapsed .nav-link {
    justify-content: center;
    padding: 15px;
}

.sidebar.collapsed .nav-link i {
    width: auto;
    font-size: 20px;
    margin: 0;
}

/* Mobile Toggle Button */
.sidebar-toggle {
    position: fixed;
    bottom: 20px;
    right: 20px;
    width: 50px;
    height: 50px;
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    color: white;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    cursor: pointer;
    z-index: 1001;
    box-shadow: 0 5px 20px rgba(102, 126, 234, 0.4);
    display: none;
    transition: all 0.3s ease;
}

.sidebar-toggle:hover {
    transform: scale(1.1);
}

/* ========== RIGHT MAIN CONTENT ========== */
.main-content-right {
    flex: 1;
    margin-left: 280px;
    display: flex;
    flex-direction: column;
    min-height: 100vh;
    transition: margin-left 0.3s ease;
}

.sidebar.collapsed + .main-content-right {
    margin-left: 80px;
}

/* Top Header */
.top-header {
    background: white;
    padding: 15px 25px;
    display: flex;
    justify-content: space-between;
    align-items: center;
    box-shadow: 0 2px 20px rgba(0, 0, 0, 0.05);
    position: sticky;
    top: 0;
    z-index: 99;
}

.header-left {
    display: flex;
    align-items: center;
    gap: 15px;
}

.sidebar-toggle-btn {
    background: none;
    border: none;
    font-size: 20px;
    color: #667eea;
    cursor: pointer;
    transition: transform 0.3s ease;
}

.sidebar-toggle-btn:hover {
    transform: scale(1.1);
}

.page-title {
    margin: 0;
    font-weight: 600;
    color: #2c3e50;
}

.header-right {
    display: flex;
    align-items: center;
    gap: 20px;
}

.datetime-display {
    background: #f8f9fa;
    padding: 8px 15px;
    border-radius: 20px;
    font-size: 14px;
}

.user-profile-dropdown .dropdown-toggle {
    padding: 0;
    text-decoration: none;
}

.user-profile-dropdown .dropdown-toggle::after {
    display: none;
}

/* Content Area */
.content-area {
    flex: 1;
    padding: 25px;
    background: #f4f7fc;
}

/* Welcome Section */
.welcome-section {
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    border-radius: 15px;
    padding: 25px;
    color: white;
    box-shadow: 0 10px 30px rgba(102, 126, 234, 0.3);
    margin-bottom: 25px;
    animation: slideInDown 0.5s ease;
}

.welcome-section h2 {
    color: white;
    font-weight: 600;
}

.welcome-section .text-muted {
    color: rgba(255, 255, 255, 0.8) !important;
}

.business-badge .badge {
    background: rgba(255, 255, 255, 0.2);
    backdrop-filter: blur(10px);
    font-size: 16px;
}

/* Statistics Cards */
.stat-card {
    border-radius: 15px;
    padding: 20px;
    display: flex;
    align-items: center;
    gap: 20px;
    box-shadow: 0 5px 20px rgba(0, 0, 0, 0.05);
    transition: all 0.3s ease;
    height: 100%;
    position: relative;
    overflow: hidden;
    animation: fadeIn 0.5s ease;
}

.stat-card:hover {
    transform: translateY(-5px);
    box-shadow: 0 10px 30px rgba(0, 0, 0, 0.1);
}

.stat-card::before {
    content: '';
    position: absolute;
    top: -50%;
    left: -50%;
    width: 200%;
    height: 200%;
    background: linear-gradient(45deg, transparent, rgba(255, 255, 255, 0.1), transparent);
    transform: rotate(45deg);
    animation: shimmer 3s infinite;
}

.stat-icon {
    width: 60px;
    height: 60px;
    background: rgba(255, 255, 255, 0.2);
    border-radius: 12px;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 30px;
    color: white;
}

.stat-details {
    flex: 1;
}

.stat-label {
    font-size: 14px;
    color: rgba(255, 255, 255, 0.9);
    text-transform: uppercase;
    letter-spacing: 0.5px;
}

.stat-value {
    font-size: 28px;
    font-weight: 700;
    color: white;
    margin: 5px 0;
}

.stat-footer {
    display: flex;
    justify-content: space-between;
    align-items: center;
    font-size: 12px;
    color: rgba(255, 255, 255, 0.8);
}

/* Cards (generic) */
.card {
    border: none;
    border-radius: 15px;
    box-shadow: 0 5px 20px rgba(0, 0, 0, 0.05);
}

.card-header {
    border-radius: 15px 15px 0 0;
    padding: 15px 20px;
}

/* Fixed Footer */
.main-footer {
    background: white;
    padding: 15px 25px;
    box-shadow: 0 -2px 20px rgba(0, 0, 0, 0.05);
    margin-top: auto;
}

.footer-content {
    display: flex;
    justify-content: space-between;
    align-items: center;
    font-size: 13px;
    color: #6c757d;
}

/* Modal Styles (copied) */
.modal-content {
    border: none;
    border-radius: 15px;
    box-shadow: 0 20px 40px rgba(0, 0, 0, 0.2);
}

.modal-header {
    border-radius: 15px 15px 0 0;
    padding: 15px 20px;
}

.modal-header.bg-primary { background: linear-gradient(135deg, #667eea 0%, #764ba2 100%) !important; }
.modal-header.bg-warning { background: linear-gradient(135deg, #f093fb 0%, #f5576c 100%) !important; }
.modal-header.bg-info { background: linear-gradient(135deg, #4facfe 0%, #00f2fe 100%) !important; }
.modal-header.bg-success { background: linear-gradient(135deg, #43e97b 0%, #38f9d7 100%) !important; }

.modal-footer {
    border-top: 2px solid #f1f5f9;
    padding: 15px 20px;
}

/* Animations */
@keyframes shimmer {
    0% { transform: translateX(-100%) rotate(45deg); }
    100% { transform: translateX(100%) rotate(45deg); }
}

@keyframes fadeIn {
    from { opacity: 0; transform: translateY(20px); }
    to { opacity: 1; transform: translateY(0); }
}

@keyframes slideInDown {
    from { transform: translateY(-30px); opacity: 0; }
    to { transform: translateY(0); opacity: 1; }
}

/* Custom Scrollbar */
.sidebar::-webkit-scrollbar {
    width: 5px;
}

.sidebar::-webkit-scrollbar-track {
    background: rgba(255, 255, 255, 0.1);
}

.sidebar::-webkit-scrollbar-thumb {
    background: rgba(255, 255, 255, 0.3);
    border-radius: 10px;
}

.sidebar::-webkit-scrollbar-thumb:hover {
    background: rgba(255, 255, 255, 0.5);
}

/* Responsive Design */
@media (max-width: 768px) {
    .sidebar {
        left: -280px;
    }
    
    .sidebar.mobile-visible {
        left: 0;
    }
    
    .main-content-right {
        margin-left: 0 !important;
    }
    
    .sidebar-toggle {
        display: flex;
    }
    
    .top-header {
        padding: 10px 15px;
    }
    
    .datetime-display {
        display: none;
    }
    
    .content-area {
        padding: 15px;
    }
    
    .stat-card {
        padding: 15px;
    }
    
    .stat-value {
        font-size: 24px;
    }
    
    .footer-content {
        flex-direction: column;
        gap: 5px;
        text-align: center;
    }
}

@media (min-width: 769px) {
    .sidebar.mobile-visible {
        left: 0;
    }
}

/* Timeline styling (profile specific) */
.timeline {
    position: relative;
    padding-left: 30px;
}
.timeline-item {
    position: relative;
    padding-bottom: 20px;
}
.timeline-badge {
    position: absolute;
    left: -30px;
    top: 0;
}
.timeline-content {
    padding-bottom: 10px;
    border-bottom: 1px solid #e9ecef;
}
.timeline-item:last-child .timeline-content {
    border-bottom: none;
}

/* Profile card specific */
.profile-card, .password-card {
    border: none;
    border-radius: 15px;
    box-shadow: 0 5px 20px rgba(0,0,0,0.05);
}
.profile-card .card-header, .password-card .card-header {
    border-radius: 15px 15px 0 0;
}
.profile-image i {
    transition: transform 0.3s ease;
}
.profile-image i:hover {
    transform: scale(1.05);
}
</style>

<script>
// Toggle sidebar (same as dashboard)
function toggleSidebar() {
    const sidebar = document.querySelector('.sidebar');
    if (window.innerWidth <= 768) {
        sidebar.classList.toggle('mobile-visible');
    } else {
        sidebar.classList.toggle('collapsed');
    }
}
document.addEventListener('click', function(event) {
    const sidebar = document.querySelector('.sidebar');
    const toggleBtn = document.querySelector('.sidebar-toggle');
    if (window.innerWidth <= 768 && !sidebar.contains(event.target) && !toggleBtn.contains(event.target)) {
        sidebar.classList.remove('mobile-visible');
    }
});
window.addEventListener('resize', function() {
    const sidebar = document.querySelector('.sidebar');
    if (window.innerWidth > 768) sidebar.classList.remove('mobile-visible');
});
document.addEventListener('DOMContentLoaded', function() {
    const currentPage = window.location.pathname.split('/').pop();
    document.querySelectorAll('.sidebar .nav-link').forEach(link => {
        if (link.getAttribute('href').includes(currentPage)) link.classList.add('active');
    });
});
</script>

<?php include '../includes/footer.php'; ?>