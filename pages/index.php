<?php
require_once '../includes/config.php';
requireLogin();

// Get user role from session
$user_role = $_SESSION['role'] ?? 'user';

// Get today's stats
$today = date('Y-m-d');
$today_sales_query = "SELECT COALESCE(SUM(total_amount), 0) as total, COALESCE(SUM(profit), 0) as profit 
                      FROM sales WHERE DATE(sale_date) = '$today'";
$today_sales_result = mysqli_query($conn, $today_sales_query);
$today_sales = $today_sales_result ? mysqli_fetch_assoc($today_sales_result) : ['total' => 0, 'profit' => 0];

// Get week stats
$week_start = date('Y-m-d', strtotime('monday this week'));
$week_sales_query = "SELECT COALESCE(SUM(total_amount), 0) as total, COALESCE(SUM(profit), 0) as profit 
                     FROM sales WHERE DATE(sale_date) >= '$week_start'";
$week_sales_result = mysqli_query($conn, $week_sales_query);
$week_sales = $week_sales_result ? mysqli_fetch_assoc($week_sales_result) : ['total' => 0, 'profit' => 0];

// Get month stats
$month_start = date('Y-m-01');
$month_sales_query = "SELECT COALESCE(SUM(total_amount), 0) as total, COALESCE(SUM(profit), 0) as profit 
                      FROM sales WHERE DATE(sale_date) >= '$month_start'";
$month_sales_result = mysqli_query($conn, $month_sales_query);
$month_sales = $month_sales_result ? mysqli_fetch_assoc($month_sales_result) : ['total' => 0, 'profit' => 0];

// Get low stock products (only if admin)
$low_stock_result = null;
if ($user_role === 'admin') {
    $low_stock_query = "SELECT * FROM products WHERE quantity <= low_stock_threshold ORDER BY quantity ASC LIMIT 5";
    $low_stock_result = mysqli_query($conn, $low_stock_query);
}

// Get recent sales (visible to all)
$recent_sales_query = "SELECT s.*, p.product_name, u.full_name 
                       FROM sales s 
                       JOIN products p ON s.product_id = p.id 
                       LEFT JOIN users u ON s.sold_by = u.id 
                       ORDER BY s.sale_date DESC LIMIT 10";
$recent_sales_result = mysqli_query($conn, $recent_sales_query);

// Get total transactions count for the period (for badges)
$today_transactions_query = "SELECT COUNT(*) as count FROM sales WHERE DATE(sale_date) = '$today'";
$today_transactions_result = mysqli_query($conn, $today_transactions_query);
$today_transactions = $today_transactions_result ? mysqli_fetch_assoc($today_transactions_result)['count'] : 0;

$week_transactions_query = "SELECT COUNT(*) as count FROM sales WHERE DATE(sale_date) >= '$week_start'";
$week_transactions_result = mysqli_query($conn, $week_transactions_query);
$week_transactions = $week_transactions_result ? mysqli_fetch_assoc($week_transactions_result)['count'] : 0;

$month_transactions_query = "SELECT COUNT(*) as count FROM sales WHERE DATE(sale_date) >= '$month_start'";
$month_transactions_result = mysqli_query($conn, $month_transactions_query);
$month_transactions = $month_transactions_result ? mysqli_fetch_assoc($month_transactions_result)['count'] : 0;

// Get current page name for active menu
$current_page = basename($_SERVER['PHP_SELF']);

// Set user info from session
$user_name = $_SESSION['full_name'] ?? 'User';
$username = $_SESSION['username'] ?? 'Not logged in';
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
                
                <?php if ($user_role === 'admin'): ?>
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
                    <i class="fas fa-map-marker-alt me-1"></i> Gatsibo District
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
            <!-- Welcome Message with Role (styled like sales page alert) -->
            <div class="row mb-4">
                <div class="col-12">
                    <div class="alert alert-info d-flex align-items-center" role="alert">
                        <i class="fas fa-user-circle fa-2x me-3"></i>
                        <div>
                            Welcome back, <strong><?php echo htmlspecialchars($_SESSION['full_name'] ?? 'User'); ?></strong>!
                            You are logged in as <span class="badge bg-<?php echo $user_role === 'admin' ? 'danger' : 'primary'; ?>"><?php echo ucfirst($user_role); ?></span>.
                        </div>
                    </div>
                </div>
            </div>

            <!-- Statistics Cards (styled exactly like sales.php) -->
            <div class="row g-4 mb-4">
                <!-- Today's Sales Card -->
                <div class="col-xl-4 col-md-6">
                    <div class="stat-card today-sales-card">
                        <div class="stat-icon">
                            <i class="fas fa-calendar-day"></i>
                        </div>
                        <div class="stat-details">
                            <span class="stat-label">Today's Sales</span>
                            <h3 class="stat-value"><?php echo number_format($today_sales['total'], 0); ?> RWF</h3>
                            <div class="stat-footer">
                                <small>
                                    <i class="fas fa-chart-line me-1"></i>
                                    Profit: <?php echo number_format($today_sales['profit'], 0); ?> RWF
                                </small>
                                <span class="stat-badge"><?php echo $today_transactions; ?> transactions</span>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- This Week Card -->
                <div class="col-xl-4 col-md-6">
                    <div class="stat-card this-week-card">
                        <div class="stat-icon">
                            <i class="fas fa-chart-line"></i>
                        </div>
                        <div class="stat-details">
                            <span class="stat-label">This Week</span>
                            <h3 class="stat-value"><?php echo number_format($week_sales['total'], 0); ?> RWF</h3>
                            <div class="stat-footer">
                                <small>
                                    <i class="fas fa-calendar-week me-1"></i>
                                    Profit: <?php echo number_format($week_sales['profit'], 0); ?> RWF
                                </small>
                                <span class="stat-badge"><?php echo $week_transactions; ?> transactions</span>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- This Month Card -->
                <div class="col-xl-4 col-md-6">
                    <div class="stat-card this-month-card">
                        <div class="stat-icon">
                            <i class="fas fa-calendar-alt"></i>
                        </div>
                        <div class="stat-details">
                            <span class="stat-label">This Month</span>
                            <h3 class="stat-value"><?php echo number_format($month_sales['total'], 0); ?> RWF</h3>
                            <div class="stat-footer">
                                <small>
                                    <i class="fas fa-calendar-check me-1"></i>
                                    Profit: <?php echo number_format($month_sales['profit'], 0); ?> RWF
                                </small>
                                <span class="stat-badge"><?php echo $month_transactions; ?> transactions</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Charts Row -->
            <div class="row g-4 mb-4">
                <div class="col-md-<?php echo $user_role === 'admin' ? '8' : '12'; ?>">
                    <div class="card sales-card">
                        <div class="card-header">
                            <h5 class="mb-0">
                                <i class="fas fa-chart-bar me-2 text-primary"></i>
                                Sales Overview (Last 7 Days)
                            </h5>
                        </div>
                        <div class="card-body">
                            <canvas id="salesChart" height="300"></canvas>
                        </div>
                    </div>
                </div>
                
                <?php if ($user_role === 'admin'): ?>
                <div class="col-md-4">
                    <div class="card sales-card">
                        <div class="card-header">
                            <h5 class="mb-0">
                                <i class="fas fa-exclamation-triangle me-2 text-warning"></i>
                                Low Stock Alert
                            </h5>
                        </div>
                        <div class="card-body">
                            <?php if ($low_stock_result && mysqli_num_rows($low_stock_result) > 0): ?>
                                <div class="stock-alert-list">
                                    <?php while ($product = mysqli_fetch_assoc($low_stock_result)): ?>
                                        <div class="stock-alert-item">
                                            <div class="item-info">
                                                <h6 class="mb-1"><?php echo htmlspecialchars($product['product_name']); ?></h6>
                                                <small class="text-muted">
                                                    <i class="fas fa-thermometer-half me-1"></i>
                                                    Threshold: <?php echo $product['low_stock_threshold']; ?> | 
                                                    Current: <span class="text-danger fw-bold"><?php echo $product['quantity']; ?></span>
                                                </small>
                                            </div>
                                            <span class="badge bg-danger"><?php echo $product['quantity']; ?> left</span>
                                        </div>
                                    <?php endwhile; ?>
                                </div>
                            <?php else: ?>
                                <div class="text-center py-4">
                                    <i class="fas fa-check-circle text-success fa-3x mb-3"></i>
                                    <p class="mb-0">No low stock products</p>
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
                <?php endif; ?>
            </div>
            
            <!-- Recent Sales Table (styled like sales.php) -->
            <div class="row">
                <div class="col-12">
                    <div class="card sales-card">
                        <div class="card-header">
                            <h5 class="mb-0">
                                <i class="fas fa-history me-2 text-info"></i>
                                Recent Sales
                            </h5>
                        </div>
                        <div class="card-body">
                            <div class="table-responsive">
                                <table class="table table-hover sales-table">
                                    <thead>
                                        <tr>
                                            <th>Time</th>
                                            <th>Product</th>
                                            <th>Quantity</th>
                                            <th>Unit Price</th>
                                            <th>Total</th>
                                            <th>Profit</th>
                                            <th>Sold By</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php if ($recent_sales_result && mysqli_num_rows($recent_sales_result) > 0): ?>
                                            <?php while ($sale = mysqli_fetch_assoc($recent_sales_result)): ?>
                                            <tr>
                                                <td><?php echo date('H:i', strtotime($sale['sale_date'])); ?></td>
                                                <td class="product-name"><?php echo htmlspecialchars($sale['product_name']); ?></td>
                                                <td><?php echo $sale['quantity']; ?></td>
                                                <td><?php echo number_format($sale['unit_price'], 0); ?> RWF</td>
                                                <td class="text-primary fw-bold"><?php echo number_format($sale['total_amount'], 0); ?> RWF</td>
                                                <td class="text-success fw-bold"><?php echo number_format($sale['profit'], 0); ?> RWF</td>
                                                <td><?php echo htmlspecialchars($sale['full_name'] ?? 'N/A'); ?></td>
                                            </tr>
                                            <?php endwhile; ?>
                                        <?php else: ?>
                                            <tr>
                                                <td colspan="7" class="text-center py-4">
                                                    <i class="fas fa-receipt text-muted fa-3x mb-3"></i>
                                                    <p class="mb-0">No recent sales found</p>
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

<!-- Include Chart.js -->
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
// Sales Chart (sample data – you can replace with dynamic data if needed)
const ctx = document.getElementById('salesChart').getContext('2d');
new Chart(ctx, {
    type: 'line',
    data: {
        labels: ['Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat', 'Sun'],
        datasets: [{
            label: 'Sales (RWF)',
            data: [12000, 19000, 15000, 25000, 22000, 30000, 28000],
            borderColor: '#667eea',
            backgroundColor: 'rgba(102, 126, 234, 0.1)',
            tension: 0.4,
            fill: true
        }]
    },
    options: {
        responsive: true,
        maintainAspectRatio: false,
        plugins: {
            legend: {
                display: false
            }
        }
    }
});

// ========== SIDEBAR FUNCTIONS ==========
function toggleSidebar() {
    const sidebar = document.querySelector('.sidebar');
    
    if (window.innerWidth <= 768) {
        sidebar.classList.toggle('mobile-visible');
    } else {
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
</script>

<!-- Copy the style block from sales.php to ensure identical appearance -->
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

/* Alert styling */
.alert-info {
    background: linear-gradient(135deg, #17a2b8 0%, #009688 100%);
    color: white;
    border: none;
    border-radius: 15px;
    box-shadow: 0 10px 30px rgba(23, 162, 184, 0.3);
}

.alert-info .badge {
    font-size: 12px;
    padding: 5px 10px;
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

/* Stat Card Colors */
.today-sales-card { background: linear-gradient(135deg, #28a745 0%, #20c997 100%); }
.this-week-card { background: linear-gradient(135deg, #ffc107 0%, #fd7e14 100%); }
.this-month-card { background: linear-gradient(135deg, #17a2b8 0%, #009688 100%); }

/* Sales Card */
.sales-card {
    border: none;
    border-radius: 15px;
    box-shadow: 0 5px 20px rgba(0, 0, 0, 0.05);
    animation: fadeIn 0.5s ease;
}

.sales-card .card-header {
    background: white;
    border-bottom: 2px solid #f1f5f9;
    padding: 20px;
    display: flex;
    justify-content: space-between;
    align-items: center;
    border-radius: 15px 15px 0 0;
}

.sales-card .card-header h5 {
    color: #2c3e50;
    font-weight: 600;
}

/* Sales Table */
.sales-table {
    margin-bottom: 0;
}

.sales-table thead th {
    background: #f8f9fa;
    color: #495057;
    font-weight: 600;
    font-size: 13px;
    border-bottom: 2px solid #dee2e6;
    text-transform: uppercase;
    letter-spacing: 0.5px;
}

.sales-table tbody tr {
    transition: all 0.3s ease;
}

.sales-table tbody tr:hover {
    background: #f8f9fa;
    transform: scale(1.01);
    box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
}

.sales-table .product-name {
    font-weight: 600;
    color: #2c3e50;
}

/* Stock Alert List */
.stock-alert-list {
    max-height: 300px;
    overflow-y: auto;
}

.stock-alert-item {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 12px;
    border-bottom: 1px solid #f1f5f9;
    transition: all 0.3s ease;
}

.stock-alert-item:hover {
    background: #f8f9fa;
    transform: translateX(5px);
}

.stock-alert-item:last-child {
    border-bottom: none;
}

.stock-alert-item .item-info {
    flex: 1;
}

.stock-alert-item .item-info h6 {
    margin: 0;
    font-size: 14px;
    font-weight: 600;
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
    
    .stock-alert-item {
        flex-direction: column;
        align-items: flex-start;
        gap: 10px;
    }
}
</style>

<?php include '../includes/footer.php'; ?>