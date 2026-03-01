<?php
// Ensure session is started
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Get current page name without path
$current_page = basename($_SERVER['PHP_SELF']);

// Use correct session keys (set in login.php)
$user_role = $_SESSION['role'] ?? 'guest';
$user_name = $_SESSION['full_name'] ?? 'User';

// Determine the correct path based on where sidebar is being included
$base_path = '';
$pages_path = '';

if (strpos($_SERVER['PHP_SELF'], '/pages/') !== false) {
    // We're inside the pages folder
    $base_path = '../';
    $pages_path = '';
} else {
    // We're in the root
    $base_path = '';
    $pages_path = 'pages/';
}
?>

<!-- Fixed Left Sidebar -->
<div class="sidebar">
    <!-- Business Logo/Name -->
    <div class="sidebar-header">
        <div class="business-logo">
            <i class="fas fa-store fa-2x mb-2"></i>
            <h4 class="text-white mb-0">MUVU FX</h4>
            <small class="text-white-50">Inventory System</small>
        </div>
    </div>
    
    <!-- User Info -->
    <div class="user-info">
        <div class="user-avatar">
            <i class="fas fa-user-circle fa-3x"></i>
        </div>
        <div class="user-details">
            <h6 class="text-white mb-1"><?php echo htmlspecialchars($user_name); ?></h6>
            <small class="text-white-50">
                <i class="fas fa-circle text-success me-1" style="font-size: 8px;"></i>
                <?php echo ucfirst(htmlspecialchars($user_role)); ?>
            </small>
        </div>
    </div>
    
    <!-- Navigation Menu -->
    <div class="sidebar-menu">
        <ul class="nav flex-column">
            <li class="nav-item">
                <a class="nav-link <?php echo $current_page == 'dashboard.php' ? 'active' : ''; ?>" 
                   href="<?php echo $pages_path; ?>dashboard.php">
                    <i class="fas fa-tachometer-alt"></i>
                    <span>Dashboard</span>
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link <?php echo $current_page == 'products.php' ? 'active' : ''; ?>" 
                   href="<?php echo $pages_path; ?>products.php">
                    <i class="fas fa-box"></i>
                    <span>Products</span>
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link <?php echo $current_page == 'sales.php' ? 'active' : ''; ?>" 
                   href="<?php echo $pages_path; ?>sales.php">
                    <i class="fas fa-shopping-cart"></i>
                    <span>Sales</span>
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link <?php echo $current_page == 'inventory.php' ? 'active' : ''; ?>" 
                   href="<?php echo $pages_path; ?>inventory.php">
                    <i class="fas fa-warehouse"></i>
                    <span>Inventory</span>
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link <?php echo $current_page == 'reports.php' ? 'active' : ''; ?>" 
                   href="<?php echo $pages_path; ?>reports.php">
                    <i class="fas fa-chart-line"></i>
                    <span>Reports</span>
                </a>
            </li>
            
            <?php if (isset($_SESSION['role']) && $_SESSION['role'] === 'admin'): ?>
            <li class="nav-divider">
                <span class="divider-text">ADMIN</span>
            </li>
            <li class="nav-item">
                <a class="nav-link <?php echo $current_page == 'users.php' ? 'active' : ''; ?>" 
                   href="<?php echo $pages_path; ?>users.php">
                    <i class="fas fa-users"></i>
                    <span>Users</span>
                </a>
            </li>
            <?php endif; ?>
        </ul>
    </div>
    
    <!-- Bottom Menu -->
    <div class="sidebar-footer">
        <ul class="nav flex-column">
            <li class="nav-item">
                <a class="nav-link <?php echo $current_page == 'profile.php' ? 'active' : ''; ?>" 
                   href="<?php echo $pages_path; ?>profile.php">
                    <i class="fas fa-user"></i>
                    <span>Profile</span>
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link text-danger" href="<?php echo $base_path; ?>actions/logout.php">
                    <i class="fas fa-sign-out-alt"></i>
                    <span>Logout</span>
                </a>
            </li>
        </ul>
        
        <!-- Business Info -->
        <div class="business-info">
            <small class="text-white-50 d-block">
                <i class="fas fa-map-marker-alt me-1"></i> Gatsibo District
            </small>
            <small class="text-white-50 d-block">
                <i class="fas fa-phone me-1"></i> 0786874837
            </small>
        </div>
    </div>
</div>