<?php
require_once '../includes/config.php';
requireLogin();

// Get today's stats
$today = date('Y-m-d');
$today_sales_query = "SELECT 
                        COALESCE(SUM(total_amount), 0) as total_sales,
                        COALESCE(SUM(profit), 0) as total_profit,
                        COUNT(*) as transaction_count
                      FROM sales 
                      WHERE DATE(sale_date) = '$today'";
$today_sales_result = mysqli_query($conn, $today_sales_query);
$today_sales = mysqli_fetch_assoc($today_sales_result);

// Get week stats (current week)
$week_start = date('Y-m-d', strtotime('monday this week'));
$week_end = date('Y-m-d', strtotime('sunday this week'));
$week_sales_query = "SELECT 
                      COALESCE(SUM(total_amount), 0) as total_sales,
                      COALESCE(SUM(profit), 0) as total_profit
                    FROM sales 
                    WHERE DATE(sale_date) BETWEEN '$week_start' AND '$week_end'";
$week_sales_result = mysqli_query($conn, $week_sales_query);
$week_sales = mysqli_fetch_assoc($week_sales_result);

// Get month stats
$month_start = date('Y-m-01');
$month_end = date('Y-m-t');
$month_sales_query = "SELECT 
                        COALESCE(SUM(total_amount), 0) as total_sales,
                        COALESCE(SUM(profit), 0) as total_profit
                      FROM sales 
                      WHERE DATE(sale_date) BETWEEN '$month_start' AND '$month_end'";
$month_sales_result = mysqli_query($conn, $month_sales_query);
$month_sales = mysqli_fetch_assoc($month_sales_result);

// Get low stock products
$low_stock_query = "SELECT * FROM products WHERE quantity <= low_stock_threshold ORDER BY quantity ASC LIMIT 5";
$low_stock_result = mysqli_query($conn, $low_stock_query);
$low_stock_count = mysqli_num_rows($low_stock_result);

// Get total products count
$products_query = "SELECT COUNT(*) as total FROM products";
$products_result = mysqli_query($conn, $products_query);
$products_count = mysqli_fetch_assoc($products_result)['total'];

// Get recent sales
$recent_sales_query = "SELECT 
                        s.*, 
                        p.product_name, 
                        u.full_name 
                      FROM sales s 
                      JOIN products p ON s.product_id = p.id 
                      LEFT JOIN users u ON s.sold_by = u.id 
                      ORDER BY s.sale_date ASC 
                      LIMIT 10";
$recent_sales_result = mysqli_query($conn, $recent_sales_query);

// Get top selling products
$top_products_query = "SELECT 
                        p.product_name,
                        SUM(s.quantity) as total_quantity,
                        SUM(s.total_amount) as total_revenue
                      FROM sales s
                      JOIN products p ON s.product_id = p.id
                      WHERE DATE(s.sale_date) BETWEEN '$month_start' AND '$month_end'
                      GROUP BY s.product_id
                      ORDER BY total_revenue ASC
                      LIMIT 5";
$top_products_result = mysqli_query($conn, $top_products_query);

// Get daily sales for chart (last 7 days)
$chart_labels = [];
$chart_sales = [];
$chart_profits = [];

for ($i = 6; $i >= 0; $i--) {
    $date = date('Y-m-d', strtotime("-$i days"));
    $chart_labels[] = date('D', strtotime($date));
    
    $day_query = "SELECT 
                    COALESCE(SUM(total_amount), 0) as day_sales,
                    COALESCE(SUM(profit), 0) as day_profit
                  FROM sales 
                  WHERE DATE(sale_date) = '$date'";
    $day_result = mysqli_query($conn, $day_query);
    $day_data = mysqli_fetch_assoc($day_result);
    
    $chart_sales[] = $day_data['day_sales'];
    $chart_profits[] = $day_data['day_profit'];
}

// Get current page name for active menu
$current_page = basename($_SERVER['PHP_SELF']);

// Set user info from session (with fallbacks)
$user_role = isset($_SESSION['role']) ? $_SESSION['role'] : 'guest';
$user_name = isset($_SESSION['full_name']) ? $_SESSION['full_name'] : 'User';
$username = isset($_SESSION['username']) ? $_SESSION['username'] : 'Not logged in';
?>
<?php include '../includes/header.php'; ?>

<!-- Main Layout Container -->
<div class="main-layout">
    <!-- Fixed Left Sidebar (Integrated) -->
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
                       href="dashboard.php">
                        <i class="fas fa-tachometer-alt"></i>
                        <span>Dashboard</span>
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link <?php echo $current_page == 'products.php' ? 'active' : ''; ?>" 
                       href="products.php">
                        <i class="fas fa-box"></i>
                        <span>Products</span>
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link <?php echo $current_page == 'sales.php' ? 'active' : ''; ?>" 
                       href="sales.php">
                        <i class="fas fa-shopping-cart"></i>
                        <span>Sales</span>
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link <?php echo $current_page == 'inventory.php' ? 'active' : ''; ?>" 
                       href="inventory.php">
                        <i class="fas fa-warehouse"></i>
                        <span>Inventory</span>
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link <?php echo $current_page == 'reports.php' ? 'active' : ''; ?>" 
                       href="reports.php">
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
                       href="users.php">
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
                       href="profile.php">
                        <i class="fas fa-user"></i>
                        <span>Profile</span>
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link text-danger" href="../actions/logout.php">
                        <i class="fas fa-sign-out-alt"></i>
                        <span>Logout</span>
                    </a>
                </li>
            </ul>
            
            <!-- Business Info -->
            <div class="business-info">
                <small class="text-white-50 d-block">
                    <i class="fas fa-map-marker-alt me-1"></i> Gatsibo District/Malimba City
                </small>
                <small class="text-white-50 d-block">
                    <i class="fas fa-phone me-1"></i> 0786874837
                </small>
            </div>
        </div>
    </div>

    <!-- Toggle Button for Mobile -->
    <div class="sidebar-toggle" onclick="toggleSidebar()">
        <i class="fas fa-bars"></i>
    </div>
    
    <!-- Right Main Content -->
    <div class="main-content-right">
        <!-- Fixed Header -->
        <div class="top-header">
            <div class="header-left">
                <button class="sidebar-toggle-btn" onclick="toggleSidebar()">
                    <i class="fas fa-bars"></i>
                </button>
                <h5 class="page-title">
                    <i class="fas fa-tachometer-alt me-2 text-primary"></i>
                    Dashboard
                </h5>
            </div>
            <div class="header-right">
                <div class="datetime-display">
                    <span class="date me-3">
                        <i class="far fa-calendar-alt text-primary me-1"></i>
                        <?php echo date('l, F j, Y'); ?>
                    </span>
                    <span class="time">
                        <i class="far fa-clock text-success me-1"></i>
                        <?php echo date('h:i A'); ?>
                    </span>
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

        <!-- Main Content Area -->
        <div class="content-area">
            <!-- Welcome Section -->
            <div class="welcome-section mb-4">
                <div class="row align-items-center">
                    <div class="col-md-8">
                        <h2 class="mb-2">
                            <i class="fas fa-hand-wave text-primary me-2"></i>
                            Welcome back, <span class="text-primary"><?php echo htmlspecialchars($user_name); ?></span>!
                        </h2>
                        <p class="text-muted">
                            <i class="fas fa-store me-2"></i>
                            MUVU FX - Gatsibo District, Kabarore Sector, Malimba City.
                        </p>
                    </div>
                    <div class="col-md-4 text-end">
                        <div class="business-badge">
                            <span class="badge bg-primary p-3">
                                <i class="fas fa-phone-alt me-2"></i>0786874837
                            </span>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Statistics Cards Row -->
            <div class="row g-4 mb-4">
                <!-- Today's Sales Card -->
                <div class="col-xl-3 col-md-6">
                    <div class="stat-card today-card">
                        <div class="stat-icon">
                            <i class="fas fa-shopping-cart"></i>
                        </div>
                        <div class="stat-details">
                            <span class="stat-label">Today's Sales</span>
                            <h3 class="stat-value"><?php echo number_format($today_sales['total_sales'], 0); ?> RWF</h3>
                            <div class="stat-footer">
                                <small>
                                    <i class="fas fa-chart-line me-1"></i>
                                    Profit: <?php echo number_format($today_sales['total_profit'], 0); ?> RWF
                                </small>
                                <span class="stat-badge"><?php echo $today_sales['transaction_count']; ?> transactions</span>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- This Week Card -->
                <div class="col-xl-3 col-md-6">
                    <div class="stat-card week-card">
                        <div class="stat-icon">
                            <i class="fas fa-calendar-week"></i>
                        </div>
                        <div class="stat-details">
                            <span class="stat-label">This Week</span>
                            <h3 class="stat-value"><?php echo number_format($week_sales['total_sales'], 0); ?> RWF</h3>
                            <div class="stat-footer">
                                <small>
                                    <i class="fas fa-chart-line me-1"></i>
                                    Profit: <?php echo number_format($week_sales['total_profit'], 0); ?> RWF
                                </small>
                                <span class="stat-badge"><?php echo date('M d', strtotime($week_start)); ?> - <?php echo date('M d', strtotime($week_end)); ?></span>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- This Month Card -->
                <div class="col-xl-3 col-md-6">
                    <div class="stat-card month-card">
                        <div class="stat-icon">
                            <i class="fas fa-calendar-alt"></i>
                        </div>
                        <div class="stat-details">
                            <span class="stat-label">This Month</span>
                            <h3 class="stat-value"><?php echo number_format($month_sales['total_sales'], 0); ?> RWF</h3>
                            <div class="stat-footer">
                                <small>
                                    <i class="fas fa-chart-line me-1"></i>
                                    Profit: <?php echo number_format($month_sales['total_profit'], 0); ?> RWF
                                </small>
                                <span class="stat-badge"><?php echo date('F Y'); ?></span>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Products & Stock Card -->
                <div class="col-xl-3 col-md-6">
                    <div class="stat-card stock-card">
                        <div class="stat-icon">
                            <i class="fas fa-boxes"></i>
                        </div>
                        <div class="stat-details">
                            <span class="stat-label">Products</span>
                            <h3 class="stat-value"><?php echo $products_count; ?></h3>
                            <div class="stat-footer">
                                <small>
                                    <i class="fas fa-exclamation-triangle me-1"></i>
                                    Low Stock: <?php echo $low_stock_count; ?>
                                </small>
                                <span class="stat-badge">Active Items</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Charts Row -->
            <div class="row g-4 mb-4">
                <!-- Sales Chart -->
                <div class="col-lg-8">
                    <div class="card chart-card">
                        <div class="card-header">
                            <h5 class="mb-0">
                                <i class="fas fa-chart-line text-primary me-2"></i>
                                Sales Overview (Last 7 Days)
                            </h5>
                            <button class="btn btn-sm btn-outline-primary" onclick="refreshChart()">
                                <i class="fas fa-sync-alt"></i>
                            </button>
                        </div>
                        <div class="card-body">
                            <canvas id="salesChart" height="300"></canvas>
                        </div>
                    </div>
                </div>

                <!-- Low Stock Alert & Quick Actions -->
                <div class="col-lg-4">
                    <!-- Low Stock Alert -->
                    <div class="card alert-card mb-4">
                        <div class="card-header bg-warning text-dark">
                            <h5 class="mb-0">
                                <i class="fas fa-exclamation-triangle me-2"></i>
                                Low Stock Alert
                                <span class="badge bg-dark ms-2"><?php echo $low_stock_count; ?></span>
                            </h5>
                        </div>
                        <div class="card-body">
                            <?php if ($low_stock_count > 0): ?>
                                <div class="low-stock-list">
                                    <?php 
                                    mysqli_data_seek($low_stock_result, 0);
                                    while ($product = mysqli_fetch_assoc($low_stock_result)): 
                                    ?>
                                        <div class="low-stock-item">
                                            <div class="item-info">
                                                <h6 class="mb-1"><?php echo htmlspecialchars($product['product_name']); ?></h6>
                                                <small class="text-muted">
                                                    Current Stock: <?php echo $product['quantity']; ?> / 
                                                    Threshold: <?php echo $product['low_stock_threshold']; ?>
                                                </small>
                                            </div>
                                            <a href="inventory.php?restock=<?php echo $product['id']; ?>" 
                                               class="btn btn-sm btn-warning">
                                                <i class="fas fa-plus"></i>
                                            </a>
                                        </div>
                                    <?php endwhile; ?>
                                </div>
                            <?php else: ?>
                                <div class="text-center py-4">
                                    <i class="fas fa-check-circle text-success fa-3x mb-3"></i>
                                    <p class="mb-0">All products are well stocked!</p>
                                </div>
                            <?php endif; ?>
                        </div>
                        <div class="card-footer">
                            <a href="inventory.php" class="btn btn-outline-warning w-100">
                                View All Inventory <i class="fas fa-arrow-right ms-2"></i>
                            </a>
                        </div>
                    </div>

                    <!-- Quick Actions -->
                    <div class="card quick-actions-card">
                        <div class="card-header bg-primary text-white">
                            <h5 class="mb-0">
                                <i class="fas fa-bolt me-2"></i>
                                Quick Actions
                            </h5>
                        </div>
                        <div class="card-body">
                            <div class="row g-2">
                                <div class="col-6">
                                    <a href="sales.php?action=new" class="quick-action-btn">
                                        <i class="fas fa-plus-circle"></i>
                                        <span>New Sale</span>
                                    </a>
                                </div>
                                <div class="col-6">
                                    <a href="products.php?action=add" class="quick-action-btn">
                                        <i class="fas fa-box"></i>
                                        <span>Add Product</span>
                                    </a>
                                </div>
                                <div class="col-6">
                                    <a href="inventory.php?action=restock" class="quick-action-btn">
                                        <i class="fas fa-truck"></i>
                                        <span>Restock</span>
                                    </a>
                                </div>
                                <div class="col-6">
                                    <a href="reports.php" class="quick-action-btn">
                                        <i class="fas fa-chart-bar"></i>
                                        <span>Reports</span>
                                    </a>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Tables Row -->
            <div class="row g-4">
                <!-- Recent Sales Table -->
                <div class="col-lg-7">
                    <div class="card table-card">
                        <div class="card-header bg-info text-white">
                            <h5 class="mb-0">
                                <i class="fas fa-history me-2"></i>
                                Recent Sales
                            </h5>
                            <a href="sales.php" class="btn btn-sm btn-light">
                                View All <i class="fas fa-arrow-right ms-2"></i>
                            </a>
                        </div>
                        <div class="card-body">
                            <div class="table-responsive">
                                <table class="table table-hover">
                                    <thead>
                                        <tr>
                                            <th>Time</th>
                                            <th>Product</th>
                                            <th>Qty</th>
                                            <th>Total</th>
                                            <th>Sold By</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php if (mysqli_num_rows($recent_sales_result) > 0): ?>
                                            <?php while ($sale = mysqli_fetch_assoc($recent_sales_result)): ?>
                                            <tr>
                                                <td><?php echo date('H:i', strtotime($sale['sale_date'])); ?></td>
                                                <td><?php echo htmlspecialchars($sale['product_name']); ?></td>
                                                <td><?php echo $sale['quantity']; ?> Pc(s)</td>
                                                <td class="text-success fw-bold">
                                                    <?php echo number_format($sale['total_amount'], 0); ?> RWF
                                                </td>
                                                <td><?php echo htmlspecialchars($sale['full_name']); ?></td>
                                            </tr>
                                            <?php endwhile; ?>
                                        <?php else: ?>
                                            <tr>
                                                <td colspan="5" class="text-center py-4">
                                                    <i class="fas fa-shopping-cart text-muted fa-2x mb-3"></i>
                                                    <p class="mb-0">No sales recorded yet</p>
                                                </td>
                                            </tr>
                                        <?php endif; ?>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Top Products Table -->
                <div class="col-lg-5">
                    <div class="card table-card">
                        <div class="card-header bg-success text-white">
                            <h5 class="mb-0">
                                <i class="fas fa-star me-2"></i>
                                Top Products (This Month)
                            </h5>
                        </div>
                        <div class="card-body">
                            <div class="table-responsive">
                                <table class="table table-hover">
                                    <thead>
                                        <tr>
                                            <th>Product</th>
                                            <th>Qty Sold</th>
                                            <th>Revenue</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php if (mysqli_num_rows($top_products_result) > 0): ?>
                                            <?php while ($product = mysqli_fetch_assoc($top_products_result)): ?>
                                            <tr>
                                                <td><?php echo htmlspecialchars($product['product_name']); ?></td>
                                                <td><?php echo $product['total_quantity']; ?> Pc(s)</td>
                                                <td class="text-primary fw-bold">
                                                    <?php echo number_format($product['total_revenue'], 0); ?> RWF
                                                </td>
                                            </tr>
                                            <?php endwhile; ?>
                                        <?php else: ?>
                                            <tr>
                                                <td colspan="3" class="text-center py-4">
                                                    <i class="fas fa-chart-line text-muted fa-2x mb-3"></i>
                                                    <p class="mb-0">No data available</p>
                                                </td>
                                            </tr>
                                        <?php endif; ?>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Fixed Footer -->
        <footer class="main-footer">
            <div class="footer-content">
                <span>&copy; <?php echo date('Y'); ?> MUVU FX. All rights reserved.</span>
                <span class="text-muted">Developed for MUVU FX - Gatsibo District</span>
            </div>
        </footer>
    </div>
</div>

<style>
/* ========== MAIN LAYOUT ========== */
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

.welcome-section .text-primary {
    color: #ffd700 !important;
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
    font-size: 24px;
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

.stat-badge {
    background: rgba(255, 255, 255, 0.2);
    padding: 3px 8px;
    border-radius: 12px;
    font-size: 11px;
}

/* Card Colors */
.today-card { background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); }
.week-card { background: linear-gradient(135deg, #f093fb 0%, #f5576c 100%); }
.month-card { background: linear-gradient(135deg, #4facfe 0%, #00f2fe 100%); }
.stock-card { background: linear-gradient(135deg, #43e97b 0%, #38f9d7 100%); }

/* Chart Card */
.chart-card {
    border: none;
    border-radius: 15px;
    box-shadow: 0 5px 20px rgba(0, 0, 0, 0.05);
    animation: fadeIn 0.5s ease;
}

.chart-card .card-header {
    background: white;
    border-bottom: 2px solid #f1f5f9;
    padding: 20px;
    display: flex;
    justify-content: space-between;
    align-items: center;
    border-radius: 15px 15px 0 0;
}

/* Alert Card */
.alert-card {
    border: none;
    border-radius: 15px;
    box-shadow: 0 5px 20px rgba(0, 0, 0, 0.05);
    animation: fadeIn 0.5s ease;
}

.alert-card .card-header {
    border-radius: 15px 15px 0 0;
    padding: 15px 20px;
}

.low-stock-list {
    max-height: 250px;
    overflow-y: auto;
}

.low-stock-list::-webkit-scrollbar {
    width: 5px;
}

.low-stock-list::-webkit-scrollbar-track {
    background: #f1f1f1;
    border-radius: 10px;
}

.low-stock-list::-webkit-scrollbar-thumb {
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    border-radius: 10px;
}

.low-stock-item {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 10px;
    background: #fff3cd;
    border-radius: 8px;
    border-left: 4px solid #ffc107;
    margin-bottom: 8px;
    transition: transform 0.3s ease;
}

.low-stock-item:hover {
    transform: translateX(5px);
}

.low-stock-item .item-info {
    flex: 1;
}

.low-stock-item h6 {
    margin: 0;
    color: #856404;
    font-size: 14px;
}

/* Quick Actions */
.quick-actions-card {
    border: none;
    border-radius: 15px;
    box-shadow: 0 5px 20px rgba(0, 0, 0, 0.05);
    animation: fadeIn 0.5s ease;
}

.quick-actions-card .card-header {
    border-radius: 15px 15px 0 0;
    padding: 15px 20px;
}

.quick-action-btn {
    display: flex;
    flex-direction: column;
    align-items: center;
    justify-content: center;
    padding: 15px;
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    color: white;
    border-radius: 10px;
    text-decoration: none;
    transition: all 0.3s ease;
    text-align: center;
}

.quick-action-btn:hover {
    transform: translateY(-3px);
    box-shadow: 0 10px 20px rgba(102, 126, 234, 0.4);
    color: white;
}

.quick-action-btn i {
    font-size: 24px;
    margin-bottom: 5px;
}

.quick-action-btn span {
    font-size: 12px;
    font-weight: 500;
}

/* Table Card */
.table-card {
    border: none;
    border-radius: 15px;
    box-shadow: 0 5px 20px rgba(0, 0, 0, 0.05);
    animation: fadeIn 0.5s ease;
}

.table-card .card-header {
    border-radius: 15px 15px 0 0;
    padding: 15px 20px;
    display: flex;
    justify-content: space-between;
    align-items: center;
}

.table-card .table {
    margin-bottom: 0;
}

.table-card .table thead th {
    background: #f8f9fa;
    color: #495057;
    font-weight: 600;
    font-size: 13px;
    border-bottom: 2px solid #dee2e6;
}

.table-card .table tbody tr:hover {
    background: #f8f9fa;
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

/* Custom Scrollbar for Sidebar */
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
        font-size: 20px;
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
</style>

<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
// Sales Chart
const ctx = document.getElementById('salesChart').getContext('2d');
const salesChart = new Chart(ctx, {
    type: 'line',
    data: {
        labels: <?php echo json_encode($chart_labels); ?>,
        datasets: [
            {
                label: 'Sales (RWF)',
                data: <?php echo json_encode($chart_sales); ?>,
                borderColor: '#667eea',
                backgroundColor: 'rgba(102, 126, 234, 0.1)',
                borderWidth: 3,
                tension: 0.4,
                fill: true,
                pointBackgroundColor: '#667eea',
                pointBorderColor: '#fff',
                pointBorderWidth: 2,
                pointRadius: 5,
                pointHoverRadius: 7
            },
            {
                label: 'Profit (RWF)',
                data: <?php echo json_encode($chart_profits); ?>,
                borderColor: '#10b981',
                backgroundColor: 'rgba(16, 185, 129, 0.1)',
                borderWidth: 3,
                tension: 0.4,
                fill: true,
                pointBackgroundColor: '#10b981',
                pointBorderColor: '#fff',
                pointBorderWidth: 2,
                pointRadius: 5,
                pointHoverRadius: 7
            }
        ]
    },
    options: {
        responsive: true,
        maintainAspectRatio: false,
        plugins: {
            legend: {
                display: true,
                position: 'top',
                labels: {
                    usePointStyle: true,
                    boxWidth: 8
                }
            },
            tooltip: {
                mode: 'index',
                intersect: false,
                callbacks: {
                    label: function(context) {
                        let label = context.dataset.label || '';
                        if (label) {
                            label += ': ';
                        }
                        if (context.parsed.y !== null) {
                            label += new Intl.NumberFormat('en-RW', {
                                style: 'currency',
                                currency: 'RWF',
                                minimumFractionDigits: 0,
                                maximumFractionDigits: 0
                            }).format(context.parsed.y);
                        }
                        return label;
                    }
                }
            }
        },
        scales: {
            y: {
                beginAtZero: true,
                ticks: {
                    callback: function(value, index, values) {
                        return value.toLocaleString() + ' RWF';
                    }
                }
            }
        },
        interaction: {
            intersect: false,
            mode: 'index'
        }
    }
});

// Refresh chart function
function refreshChart() {
    salesChart.update();
}

// Toggle sidebar function
function toggleSidebar() {
    const sidebar = document.querySelector('.sidebar');
    
    if (window.innerWidth <= 768) {
        // Mobile: toggle mobile-visible class
        sidebar.classList.toggle('mobile-visible');
    } else {
        // Desktop: toggle collapsed class
        sidebar.classList.toggle('collapsed');
    }
}

// Close sidebar when clicking outside on mobile
document.addEventListener('click', function(event) {
    const sidebar = document.querySelector('.sidebar');
    const toggleBtn = document.querySelector('.sidebar-toggle');
    
    if (window.innerWidth <= 768) {
        if (!sidebar.contains(event.target) && !toggleBtn.contains(event.target)) {
            sidebar.classList.remove('mobile-visible');
        }
    }
});

// Handle window resize
window.addEventListener('resize', function() {
    const sidebar = document.querySelector('.sidebar');
    
    if (window.innerWidth > 768) {
        sidebar.classList.remove('mobile-visible');
    }
});

// Animate counters on page load
document.addEventListener('DOMContentLoaded', function() {
    const counters = document.querySelectorAll('.stat-value');
    counters.forEach(counter => {
        const target = parseInt(counter.innerText.replace(/[^0-9]/g, ''));
        if (!isNaN(target)) {
            animateCounter(counter, 0, target, 2000);
        }
    });
});

// Counter animation
function animateCounter(element, start, end, duration) {
    let startTimestamp = null;
    const step = (timestamp) => {
        if (!startTimestamp) startTimestamp = timestamp;
        const progress = Math.min((timestamp - startTimestamp) / duration, 1);
        const current = Math.floor(progress * (end - start) + start);
        element.innerText = element.innerText.replace(/[0-9,]+/, current.toLocaleString());
        if (progress < 1) {
            window.requestAnimationFrame(step);
        }
    };
    window.requestAnimationFrame(step);
}

// Auto-refresh data every 5 minutes
setTimeout(function() {
    location.reload();
}, 300000); // 5 minutes

// Add active class to current page link
document.addEventListener('DOMContentLoaded', function() {
    const currentPage = window.location.pathname.split('/').pop();
    const navLinks = document.querySelectorAll('.sidebar .nav-link');
    
    navLinks.forEach(link => {
        if (link.getAttribute('href').includes(currentPage)) {
            link.classList.add('active');
        }
    });
});

// 3D Hover Effects for cards
document.querySelectorAll('.stat-card, .quick-action-btn, .table-card').forEach(element => {
    element.addEventListener('mousemove', function(e) {
        const rect = this.getBoundingClientRect();
        const x = e.clientX - rect.left;
        const y = e.clientY - rect.top;
        
        const centerX = rect.width / 2;
        const centerY = rect.height / 2;
        
        const rotateX = (y - centerY) / 20;
        const rotateY = (centerX - x) / 20;
        
        this.style.transform = `perspective(1000px) rotateX(${rotateX}deg) rotateY(${rotateY}deg) translateZ(10px)`;
    });
    
    element.addEventListener('mouseleave', function() {
        this.style.transform = 'perspective(1000px) rotateX(0) rotateY(0) translateZ(0)';
    });
});
</script>

<?php include '../includes/footer.php'; ?>