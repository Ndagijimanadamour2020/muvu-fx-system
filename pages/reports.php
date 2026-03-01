<?php
require_once '../includes/config.php';
requireLogin();

// Get current page name for active menu
$current_page = basename($_SERVER['PHP_SELF']);

// Set user info from session
$user_role = $_SESSION['role'] ?? 'guest';
$user_name = $_SESSION['full_name'] ?? 'User';
$username = $_SESSION['username'] ?? 'Not logged in';

// Get date filters
$start_date = isset($_GET['start_date']) ? $_GET['start_date'] : date('Y-m-01');
$end_date = isset($_GET['end_date']) ? $_GET['end_date'] : date('Y-m-d');
$report_type = isset($_GET['report_type']) ? $_GET['report_type'] : 'monthly';

// Get sales data for the period
$sales_query = "SELECT s.*, p.product_name, u.full_name 
                FROM sales s 
                JOIN products p ON s.product_id = p.id 
                LEFT JOIN users u ON s.sold_by = u.id 
                WHERE DATE(s.sale_date) BETWEEN '$start_date' AND '$end_date'
                ORDER BY s.sale_date ASC";
$sales_result = mysqli_query($conn, $sales_query);

// Get summary statistics
$summary_query = "SELECT 
                    COUNT(*) as total_transactions,
                    COALESCE(SUM(total_amount), 0) as total_sales,
                    COALESCE(SUM(profit), 0) as total_profit,
                    COALESCE(AVG(profit), 0) as avg_profit,
                    COALESCE(AVG(total_amount), 0) as avg_transaction_value
                  FROM sales 
                  WHERE DATE(sale_date) BETWEEN '$start_date' AND '$end_date'";
$summary_result = mysqli_query($conn, $summary_query);
$summary = mysqli_fetch_assoc($summary_result);

// Calculate profit margin
$profit_margin = $summary['total_sales'] > 0 ? round(($summary['total_profit'] / $summary['total_sales']) * 100, 1) : 0;

// Get top products
$top_products_query = "SELECT 
                        p.product_name,
                        COUNT(*) as sales_count,
                        SUM(s.quantity) as total_quantity,
                        SUM(s.total_amount) as total_revenue,
                        SUM(s.profit) as total_profit,
                        AVG(s.unit_price) as avg_price
                      FROM sales s
                      JOIN products p ON s.product_id = p.id
                      WHERE DATE(s.sale_date) BETWEEN '$start_date' AND '$end_date'
                      GROUP BY s.product_id
                      ORDER BY total_revenue DESC
                      LIMIT 10";
$top_products_result = mysqli_query($conn, $top_products_query);

// Fetch top products into an array for reuse
$top_products = [];
while ($row = mysqli_fetch_assoc($top_products_result)) {
    $top_products[] = $row;
}

// Get daily breakdown
$daily_query = "SELECT 
                  DATE(sale_date) as sale_day,
                  COUNT(*) as transactions,
                  SUM(total_amount) as daily_total,
                  SUM(profit) as daily_profit
                FROM sales 
                WHERE DATE(sale_date) BETWEEN '$start_date' AND '$end_date'
                GROUP BY DATE(sale_date)
                ORDER BY sale_day DESC";
$daily_result = mysqli_query($conn, $daily_query);

// Fetch daily data for charts and table
$daily_data = [];
while ($row = mysqli_fetch_assoc($daily_result)) {
    $daily_data[] = $row;
}

// Get user performance
$user_performance_query = "SELECT 
                            u.full_name,
                            COUNT(*) as sales_count,
                            SUM(s.total_amount) as total_sales,
                            SUM(s.profit) as total_profit
                          FROM sales s
                          JOIN users u ON s.sold_by = u.id
                          WHERE DATE(s.sale_date) BETWEEN '$start_date' AND '$end_date'
                          GROUP BY s.sold_by
                          ORDER BY total_sales DESC";
$user_performance_result = mysqli_query($conn, $user_performance_query);

// Get hourly breakdown
$hourly_query = "SELECT 
                  HOUR(sale_date) as hour,
                  COUNT(*) as transactions,
                  SUM(total_amount) as hourly_total
                FROM sales 
                WHERE DATE(sale_date) BETWEEN '$start_date' AND '$end_date'
                GROUP BY HOUR(sale_date)
                ORDER BY hour ASC";
$hourly_result = mysqli_query($conn, $hourly_query);

// Prepare chart data for daily sales
$chart_labels = [];
$chart_sales = [];
$chart_profits = [];

foreach ($daily_data as $daily) {
    $chart_labels[] = date('M d', strtotime($daily['sale_day']));
    $chart_sales[] = $daily['daily_total'];
    $chart_profits[] = $daily['daily_profit'];
}

// If no data, provide empty arrays for charts
if (empty($chart_labels)) {
    $chart_labels = ['No Data'];
    $chart_sales = [0];
    $chart_profits = [0];
}

// Prepare hour labels for hourly chart
$hour_labels = [];
$hour_data = [];
for ($i = 0; $i < 24; $i++) {
    $hour_labels[] = sprintf("%02d:00", $i);
    $hour_data[$i] = 0;
}

mysqli_data_seek($hourly_result, 0);
while ($hour = mysqli_fetch_assoc($hourly_result)) {
    $hour_data[$hour['hour']] = $hour['hourly_total'];
}

// Prepare top 5 products for pie chart
$top_products_labels = [];
$top_products_data = [];
$top_count = 0;
foreach ($top_products as $product) {
    if ($top_count < 5) {
        $top_products_labels[] = $product['product_name'];
        $top_products_data[] = $product['total_revenue'];
        $top_count++;
    }
}
if (empty($top_products_labels)) {
    $top_products_labels = ['No Data'];
    $top_products_data = [0];
}
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
                    <i class="fas fa-chart-line me-2 text-warning"></i>
                    Reports & Analytics
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
                            <i class="fas fa-chart-pie text-warning me-2"></i>
                            Reports & Analytics
                        </h2>
                        <p class="text-muted">
                            <i class="fas fa-store me-2"></i>
                            Analyze your sales performance, profits, and business insights
                        </p>
                    </div>
                    <div class="col-md-4 text-end">
                        <div class="business-badge">
                            <span class="badge bg-warning text-dark p-3">
                                <i class="fas fa-phone-alt me-2"></i>0786874837
                            </span>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Report Filters Card -->
            <div class="card filter-card mb-4">
                <div class="card-header">
                    <h5 class="mb-0">
                        <i class="fas fa-filter me-2 text-primary"></i>
                        Report Filters
                    </h5>
                    <span class="badge bg-primary"><?php echo ucfirst($report_type); ?> Report</span>
                </div>
                <div class="card-body">
                    <form method="GET" action="" class="row g-3" id="reportForm">
                        <div class="col-md-3">
                            <label for="report_type" class="form-label">Report Type</label>
                            <select class="form-select" id="report_type" name="report_type">
                                <option value="daily" <?php echo $report_type == 'daily' ? 'selected' : ''; ?>>Daily Report</option>
                                <option value="weekly" <?php echo $report_type == 'weekly' ? 'selected' : ''; ?>>Weekly Report</option>
                                <option value="monthly" <?php echo $report_type == 'monthly' ? 'selected' : ''; ?>>Monthly Report</option>
                                <option value="quarterly" <?php echo $report_type == 'quarterly' ? 'selected' : ''; ?>>Quarterly Report</option>
                                <option value="yearly" <?php echo $report_type == 'yearly' ? 'selected' : ''; ?>>Yearly Report</option>
                                <option value="custom" <?php echo $report_type == 'custom' ? 'selected' : ''; ?>>Custom Range</option>
                            </select>
                        </div>
                        
                        <div class="col-md-3">
                            <label for="start_date" class="form-label">Start Date</label>
                            <input type="date" class="form-control" id="start_date" name="start_date" value="<?php echo $start_date; ?>">
                        </div>
                        
                        <div class="col-md-3">
                            <label for="end_date" class="form-label">End Date</label>
                            <input type="date" class="form-control" id="end_date" name="end_date" value="<?php echo $end_date; ?>">
                        </div>
                        
                        <div class="col-md-3 d-flex align-items-end">
                            <button type="submit" class="btn btn-primary w-100">
                                <i class="fas fa-search me-2"></i>Generate Report
                            </button>
                        </div>
                    </form>
                </div>
            </div>

            <!-- Summary Statistics Cards -->
            <div class="row g-4 mb-4">
                <!-- Total Sales Card -->
                <div class="col-xl-3 col-md-6">
                    <div class="stat-card total-sales-report-card">
                        <div class="stat-icon">
                            <i class="fas fa-chart-line"></i>
                        </div>
                        <div class="stat-details">
                            <span class="stat-label">Total Sales</span>
                            <h3 class="stat-value"><?php echo number_format($summary['total_sales'], 0); ?> RWF</h3>
                            <div class="stat-footer">
                                <small>Period total</small>
                                <span class="stat-badge"><?php echo $summary['total_transactions']; ?> transactions</span>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Total Profit Card -->
                <div class="col-xl-3 col-md-6">
                    <div class="stat-card total-profit-report-card">
                        <div class="stat-icon">
                            <i class="fas fa-wallet"></i>
                        </div>
                        <div class="stat-details">
                            <span class="stat-label">Total Profit</span>
                            <h3 class="stat-value"><?php echo number_format($summary['total_profit'], 0); ?> RWF</h3>
                            <div class="stat-footer">
                                <small>Net profit</small>
                                <span class="stat-badge">Margin: <?php echo $profit_margin; ?>%</span>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Average Transaction Card -->
                <div class="col-xl-3 col-md-6">
                    <div class="stat-card avg-transaction-card">
                        <div class="stat-icon">
                            <i class="fas fa-receipt"></i>
                        </div>
                        <div class="stat-details">
                            <span class="stat-label">Avg Transaction</span>
                            <h3 class="stat-value"><?php echo number_format($summary['avg_transaction_value'], 0); ?> RWF</h3>
                            <div class="stat-footer">
                                <small>Per sale</small>
                                <span class="stat-badge">Avg profit: <?php echo number_format($summary['avg_profit'], 0); ?> RWF</span>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Period Summary Card -->
                <div class="col-xl-3 col-md-6">
                    <div class="stat-card period-summary-card">
                        <div class="stat-icon">
                            <i class="fas fa-calendar-alt"></i>
                        </div>
                        <div class="stat-details">
                            <span class="stat-label">Period Summary</span>
                            <h3 class="stat-value"><?php echo date('M d', strtotime($start_date)); ?> - <?php echo date('M d', strtotime($end_date)); ?></h3>
                            <div class="stat-footer">
                                <small><?php echo $summary['total_transactions']; ?> transactions</small>
                                <span class="stat-badge"><?php echo ceil((strtotime($end_date) - strtotime($start_date)) / (60 * 60 * 24)) + 1; ?> days</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Charts Row -->
            <div class="row g-4 mb-4">
                <!-- Daily Sales Chart -->
                <div class="col-lg-8">
                    <div class="card chart-card">
                        <div class="card-header">
                            <h5 class="mb-0">
                                <i class="fas fa-chart-bar text-primary me-2"></i>
                                Daily Sales & Profit
                            </h5>
                            <button class="btn btn-sm btn-outline-primary" onclick="refreshCharts()">
                                <i class="fas fa-sync-alt"></i>
                            </button>
                        </div>
                        <div class="card-body">
                            <canvas id="dailySalesChart" style="height: 300px; width: 100%;"></canvas>
                        </div>
                    </div>
                </div>
                <!-- Profit Distribution Chart -->
                <div class="col-lg-4">
                    <div class="card chart-card">
                        <div class="card-header">
                            <h5 class="mb-0">
                                <i class="fas fa-chart-pie text-success me-2"></i>
                                Profit Distribution
                            </h5>
                        </div>
                        <div class="card-body">
                            <canvas id="profitChart" style="height: 250px; width: 100%;"></canvas>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Second Row of Charts -->
            <div class="row g-4 mb-4">
                <!-- Hourly Sales Chart -->
                <div class="col-lg-6">
                    <div class="card chart-card">
                        <div class="card-header">
                            <h5 class="mb-0">
                                <i class="fas fa-clock text-warning me-2"></i>
                                Hourly Sales Pattern
                            </h5>
                        </div>
                        <div class="card-body">
                            <canvas id="hourlyChart" style="height: 250px; width: 100%;"></canvas>
                        </div>
                    </div>
                </div>
                <!-- Top Products Chart -->
                <div class="col-lg-6">
                    <div class="card chart-card">
                        <div class="card-header">
                            <h5 class="mb-0">
                                <i class="fas fa-crown text-danger me-2"></i>
                                Top 5 Products by Revenue
                            </h5>
                        </div>
                        <div class="card-body">
                            <canvas id="topProductsChart" style="height: 250px; width: 100%;"></canvas>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Top Products and User Performance Tables -->
            <div class="row g-4 mb-4">
                <!-- Top Products Table -->
                <div class="col-lg-6">
                    <div class="card table-card">
                        <div class="card-header">
                            <h5 class="mb-0">
                                <i class="fas fa-star text-warning me-2"></i>
                                Top Selling Products
                            </h5>
                            <span class="badge bg-warning">Top 10</span>
                        </div>
                        <div class="card-body">
                            <div class="table-responsive">
                                <table class="table table-hover reports-table">
                                    <thead>
                                        <tr>
                                            <th>Product Name</th>
                                            <th>Sales</th>
                                            <th>Quantity</th>
                                            <th>Revenue</th>
                                            <th>Profit</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php if (count($top_products) > 0): ?>
                                            <?php foreach ($top_products as $product): ?>
                                            <tr>
                                                <td class="product-name"><?php echo htmlspecialchars($product['product_name']); ?></td>
                                                <td><?php echo $product['sales_count']; ?></td>
                                                <td><?php echo $product['total_quantity']; ?> Pc(s)</td>
                                                <td class="text-primary fw-bold"><?php echo number_format($product['total_revenue'], 0); ?> RWF</td>
                                                <td class="text-success fw-bold"><?php echo number_format($product['total_profit'], 0); ?> RWF</td>
                                            </tr>
                                            <?php endforeach; ?>
                                        <?php else: ?>
                                            <tr>
                                                <td colspan="5" class="text-center py-3">No data available</td>
                                            </tr>
                                        <?php endif; ?>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- User Performance Table -->
                <div class="col-lg-6">
                    <div class="card table-card">
                        <div class="card-header">
                            <h5 class="mb-0">
                                <i class="fas fa-users text-info me-2"></i>
                                Staff Performance
                            </h5>
                            <span class="badge bg-info">Sales by staff</span>
                        </div>
                        <div class="card-body">
                            <div class="table-responsive">
                                <table class="table table-hover reports-table">
                                    <thead>
                                        <tr>
                                            <th>Staff Name</th>
                                            <th>Sales Count</th>
                                            <th>Total Sales</th>
                                            <th>Total Profit</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php if (mysqli_num_rows($user_performance_result) > 0): ?>
                                            <?php while ($user = mysqli_fetch_assoc($user_performance_result)): ?>
                                            <tr>
                                                <td><?php echo htmlspecialchars($user['full_name']); ?></td>
                                                <td><?php echo $user['sales_count']; ?></td>
                                                <td class="text-primary fw-bold"><?php echo number_format($user['total_sales'], 0); ?> RWF</td>
                                                <td class="text-success fw-bold"><?php echo number_format($user['total_profit'], 0); ?> RWF</td>
                                            </tr>
                                            <?php endwhile; ?>
                                        <?php else: ?>
                                            <tr>
                                                <td colspan="4" class="text-center py-3">No data available</td>
                                            </tr>
                                        <?php endif; ?>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Daily Summary Table -->
            <div class="row g-4 mb-4">
                <div class="col-12">
                    <div class="card table-card">
                        <div class="card-header">
                            <h5 class="mb-0">
                                <i class="fas fa-calendar-alt text-secondary me-2"></i>
                                Daily Summary
                            </h5>
                        </div>
                        <div class="card-body">
                            <div class="table-responsive">
                                <table class="table table-hover reports-table">
                                    <thead>
                                        <tr>
                                            <th>Date</th>
                                            <th>Transactions</th>
                                            <th>Total Sales</th>
                                            <th>Total Profit</th>
                                            <th>Avg Sale Value</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php if (count($daily_data) > 0): ?>
                                            <?php foreach ($daily_data as $daily): 
                                                $avg_sale = $daily['transactions'] > 0 ? $daily['daily_total'] / $daily['transactions'] : 0;
                                            ?>
                                            <tr>
                                                <td><?php echo date('Y-m-d', strtotime($daily['sale_day'])); ?></td>
                                                <td><?php echo $daily['transactions']; ?></td>
                                                <td class="text-primary fw-bold"><?php echo number_format($daily['daily_total'], 0); ?> RWF</td>
                                                <td class="text-success fw-bold"><?php echo number_format($daily['daily_profit'], 0); ?> RWF</td>
                                                <td><?php echo number_format($avg_sale, 0); ?> RWF</td>
                                            </tr>
                                            <?php endforeach; ?>
                                        <?php else: ?>
                                            <tr>
                                                <td colspan="5" class="text-center py-4">
                                                    <i class="fas fa-calendar-times text-muted fa-2x mb-3"></i>
                                                    <p class="mb-0">No data available for the selected period</p>
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

            <!-- Detailed Sales Table -->
            <div class="row">
                <div class="col-12">
                    <div class="card detailed-table-card">
                        <div class="card-header">
                            <h5 class="mb-0">
                                <i class="fas fa-list me-2 text-primary"></i>
                                Detailed Sales Report
                            </h5>
                            <div class="header-actions">
                                <button class="btn btn-success btn-sm me-2" onclick="exportToExcel()">
                                    <i class="fas fa-file-excel me-2"></i>Export to Excel
                                </button>
                                <button class="btn btn-info btn-sm me-2" onclick="printReport()">
                                    <i class="fas fa-print me-2"></i>Print
                                </button>
                                <div class="search-box">
                                    <i class="fas fa-search"></i>
                                    <input type="text" class="form-control form-control-sm" 
                                           id="searchInput" placeholder="Search transactions...">
                                </div>
                            </div>
                        </div>
                        <div class="card-body">
                            <div class="table-responsive">
                                <table class="table table-hover detailed-table" id="salesTable">
                                    <thead>
                                        <tr>
                                            <th>Date & Time</th>
                                            <th>Product Name</th>
                                            <th>Quantity</th>
                                            <th>Unit Price</th>
                                            <th>Total</th>
                                            <th>Profit</th>
                                            <th>Sold By</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php if (mysqli_num_rows($sales_result) > 0): ?>
                                            <?php while ($sale = mysqli_fetch_assoc($sales_result)): ?>
                                            <tr>
                                                <td><?php echo date('Y-m-d H:i', strtotime($sale['sale_date'])); ?></td>
                                                <td class="product-name"><?php echo htmlspecialchars($sale['product_name']); ?></td>
                                                <td><?php echo $sale['quantity']; ?> Pc(s)</td>
                                                <td><?php echo number_format($sale['unit_price'], 0); ?> RWF</td>
                                                <td class="text-primary fw-bold"><?php echo number_format($sale['total_amount'], 0); ?> RWF</td>
                                                <td class="text-success fw-bold"><?php echo number_format($sale['profit'], 0); ?> RWF</td>
                                                <td><?php echo htmlspecialchars($sale['full_name']); ?></td>
                                            </tr>
                                            <?php endwhile; ?>
                                        <?php else: ?>
                                            <tr>
                                                <td colspan="7" class="text-center py-5">
                                                    <i class="fas fa-file-invoice fa-4x text-muted mb-3"></i>
                                                    <h5 class="text-muted">No Transactions Found</h5>
                                                    <p class="text-muted mb-3">No sales records for the selected period</p>
                                                    <button class="btn btn-primary" onclick="window.location.href='sales.php'">
                                                        <i class="fas fa-plus me-2"></i>Record Sale
                                                    </button>
                                                </td>
                                            </tr>
                                        <?php endif; ?>
                                    </tbody>
                                    <tfoot class="table-primary">
                                        <tr>
                                            <th colspan="4" class="text-end">Period Totals:</th>
                                            <th><?php echo number_format($summary['total_sales'], 0); ?> RWF</th>
                                            <th><?php echo number_format($summary['total_profit'], 0); ?> RWF</th>
                                            <th></th>
                                        </tr>
                                    </tfoot>
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
    background: linear-gradient(135deg, #f39c12 0%, #f1c40f 100%);
    border-radius: 15px;
    padding: 25px;
    color: white;
    box-shadow: 0 10px 30px rgba(243, 156, 18, 0.3);
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
    color: white;
}

/* Filter Card */
.filter-card {
    border: none;
    border-radius: 15px;
    box-shadow: 0 5px 20px rgba(0, 0, 0, 0.05);
    margin-bottom: 25px;
}

.filter-card .card-header {
    background: white;
    border-bottom: 2px solid #f1f5f9;
    padding: 20px;
    display: flex;
    justify-content: space-between;
    align-items: center;
    border-radius: 15px 15px 0 0;
}

.filter-card .card-header h5 {
    color: #2c3e50;
    font-weight: 600;
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
    font-size: 22px;
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
.total-sales-report-card { background: linear-gradient(135deg, #3498db 0%, #2980b9 100%); }
.total-profit-report-card { background: linear-gradient(135deg, #27ae60 0%, #229954 100%); }
.avg-transaction-card { background: linear-gradient(135deg, #9b59b6 0%, #8e44ad 100%); }
.period-summary-card { background: linear-gradient(135deg, #e67e22 0%, #d35400 100%); }

/* Chart Cards */
.chart-card {
    border: none;
    border-radius: 15px;
    box-shadow: 0 5px 20px rgba(0, 0, 0, 0.05);
    height: 100%;
}

.chart-card .card-header {
    background: white;
    border-bottom: 2px solid #f1f5f9;
    padding: 15px 20px;
    display: flex;
    justify-content: space-between;
    align-items: center;
    border-radius: 15px 15px 0 0;
}

.chart-card .card-body {
    padding: 20px;
}

/* Table Cards */
.table-card {
    border: none;
    border-radius: 15px;
    box-shadow: 0 5px 20px rgba(0, 0, 0, 0.05);
    height: 100%;
}

.table-card .card-header {
    background: white;
    border-bottom: 2px solid #f1f5f9;
    padding: 15px 20px;
    display: flex;
    justify-content: space-between;
    align-items: center;
    border-radius: 15px 15px 0 0;
}

/* Detailed Table Card */
.detailed-table-card {
    border: none;
    border-radius: 15px;
    box-shadow: 0 5px 20px rgba(0, 0, 0, 0.05);
}

.detailed-table-card .card-header {
    background: white;
    border-bottom: 2px solid #f1f5f9;
    padding: 20px;
    display: flex;
    justify-content: space-between;
    align-items: center;
    border-radius: 15px 15px 0 0;
}

.header-actions {
    display: flex;
    align-items: center;
    gap: 10px;
}

.search-box {
    position: relative;
}

.search-box i {
    position: absolute;
    left: 10px;
    top: 50%;
    transform: translateY(-50%);
    color: #6c757d;
    font-size: 12px;
}

.search-box input {
    padding-left: 30px;
    width: 200px;
}

/* Reports Tables */
.reports-table {
    margin-bottom: 0;
}

.reports-table thead th {
    background: #f8f9fa;
    color: #495057;
    font-weight: 600;
    font-size: 13px;
    border-bottom: 2px solid #dee2e6;
    text-transform: uppercase;
    letter-spacing: 0.5px;
}

.reports-table tbody tr {
    transition: all 0.3s ease;
}

.reports-table tbody tr:hover {
    background: #f8f9fa;
}

.reports-table .product-name {
    font-weight: 600;
    color: #2c3e50;
}

/* Detailed Table */
.detailed-table {
    margin-bottom: 0;
}

.detailed-table thead th {
    background: #f8f9fa;
    color: #495057;
    font-weight: 600;
    font-size: 13px;
    border-bottom: 2px solid #dee2e6;
    text-transform: uppercase;
    letter-spacing: 0.5px;
}

.detailed-table tbody tr {
    transition: all 0.3s ease;
}

.detailed-table tbody tr:hover {
    background: #f8f9fa;
    transform: scale(1.01);
    box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
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
        font-size: 18px;
    }
    
    .header-actions {
        flex-wrap: wrap;
    }
    
    .search-box input {
        width: 150px;
    }
    
    .footer-content {
        flex-direction: column;
        gap: 5px;
        text-align: center;
    }
    
    .filter-card .card-header {
        flex-direction: column;
        gap: 10px;
    }
}

@media (min-width: 769px) {
    .sidebar.mobile-visible {
        left: 0;
    }
}
</style>

<!-- Include Chart.js and SheetJS -->
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/xlsx/0.18.5/xlsx.full.min.js"></script>

<script>
// ========== CHART INITIALIZATIONS ==========
document.addEventListener('DOMContentLoaded', function() {
    // Daily Sales Chart
    if (document.getElementById('dailySalesChart')) {
        const dailyCtx = document.getElementById('dailySalesChart').getContext('2d');
        new Chart(dailyCtx, {
            type: 'bar',
            data: {
                labels: <?php echo json_encode($chart_labels); ?>,
                datasets: [{
                    label: 'Sales (RWF)',
                    data: <?php echo json_encode($chart_sales); ?>,
                    backgroundColor: 'rgba(52, 152, 219, 0.5)',
                    borderColor: '#3498db',
                    borderWidth: 1
                }, {
                    label: 'Profit (RWF)',
                    data: <?php echo json_encode($chart_profits); ?>,
                    backgroundColor: 'rgba(46, 204, 113, 0.5)',
                    borderColor: '#2ecc71',
                    borderWidth: 1
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                scales: {
                    y: {
                        beginAtZero: true,
                        ticks: {
                            callback: function(value) {
                                return value.toLocaleString() + ' RWF';
                            }
                        }
                    }
                },
                plugins: {
                    tooltip: {
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
                                        minimumFractionDigits: 0
                                    }).format(context.parsed.y);
                                }
                                return label;
                            }
                        }
                    }
                }
            }
        });
    }

    // Profit Distribution Chart (Doughnut)
    if (document.getElementById('profitChart')) {
        const profitCtx = document.getElementById('profitChart').getContext('2d');
        new Chart(profitCtx, {
            type: 'doughnut',
            data: {
                labels: ['Sales', 'Profit'],
                datasets: [{
                    data: [<?php echo $summary['total_sales']; ?>, <?php echo $summary['total_profit']; ?>],
                    backgroundColor: ['#3498db', '#2ecc71'],
                    borderWidth: 0,
                    hoverOffset: 4
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        position: 'bottom',
                        labels: {
                            usePointStyle: true,
                            padding: 20
                        }
                    },
                    tooltip: {
                        callbacks: {
                            label: function(context) {
                                let label = context.label || '';
                                if (label) {
                                    label += ': ';
                                }
                                if (context.raw !== null) {
                                    label += new Intl.NumberFormat('en-RW', {
                                        style: 'currency',
                                        currency: 'RWF',
                                        minimumFractionDigits: 0
                                    }).format(context.raw);
                                }
                                return label;
                            }
                        }
                    }
                },
                cutout: '60%'
            }
        });
    }

    // Hourly Sales Chart
    if (document.getElementById('hourlyChart')) {
        const hourlyCtx = document.getElementById('hourlyChart').getContext('2d');
        new Chart(hourlyCtx, {
            type: 'line',
            data: {
                labels: <?php echo json_encode($hour_labels); ?>,
                datasets: [{
                    label: 'Sales (RWF)',
                    data: <?php echo json_encode(array_values($hour_data)); ?>,
                    borderColor: '#e67e22',
                    backgroundColor: 'rgba(230, 126, 34, 0.1)',
                    borderWidth: 3,
                    tension: 0.4,
                    fill: true,
                    pointBackgroundColor: '#e67e22',
                    pointBorderColor: '#fff',
                    pointBorderWidth: 2,
                    pointRadius: 4,
                    pointHoverRadius: 6
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                scales: {
                    y: {
                        beginAtZero: true,
                        ticks: {
                            callback: function(value) {
                                return value.toLocaleString() + ' RWF';
                            }
                        }
                    }
                }
            }
        });
    }

    // Top Products Chart (Pie)
    if (document.getElementById('topProductsChart')) {
        const topProductsCtx = document.getElementById('topProductsChart').getContext('2d');
        new Chart(topProductsCtx, {
            type: 'pie',
            data: {
                labels: <?php echo json_encode($top_products_labels); ?>,
                datasets: [{
                    data: <?php echo json_encode($top_products_data); ?>,
                    backgroundColor: [
                        '#3498db',
                        '#2ecc71',
                        '#e74c3c',
                        '#f39c12',
                        '#9b59b6'
                    ],
                    borderWidth: 0
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        position: 'bottom',
                        labels: {
                            boxWidth: 12,
                            padding: 15
                        }
                    }
                }
            }
        });
    }
});

// ========== BUTTON FUNCTIONS ==========

// Export to Excel
function exportToExcel() {
    const table = document.getElementById('salesTable');
    if (!table) {
        alert('Table not found!');
        return;
    }
    const wb = XLSX.utils.table_to_book(table, {sheet: "Sales Report"});
    XLSX.writeFile(wb, `MUVU_FX_Report_${new Date().toISOString().slice(0,10)}.xlsx`);
}

// Print Report
function printReport() {
    window.print();
}

// Refresh Charts (reloads page with same filters)
function refreshCharts() {
    location.reload();
}

// ========== REPORT TYPE HANDLER ==========
document.getElementById('report_type').addEventListener('change', function() {
    const today = new Date();
    const startDate = document.getElementById('start_date');
    const endDate = document.getElementById('end_date');
    
    switch(this.value) {
        case 'daily':
            startDate.value = today.toISOString().slice(0,10);
            endDate.value = today.toISOString().slice(0,10);
            break;
        case 'weekly':
            const weekStart = new Date(today);
            weekStart.setDate(today.getDate() - today.getDay() + (today.getDay() === 0 ? -6 : 1));
            const weekEnd = new Date(weekStart);
            weekEnd.setDate(weekStart.getDate() + 6);
            startDate.value = weekStart.toISOString().slice(0,10);
            endDate.value = weekEnd.toISOString().slice(0,10);
            break;
        case 'monthly':
            const monthStart = new Date(today.getFullYear(), today.getMonth(), 1);
            const monthEnd = new Date(today.getFullYear(), today.getMonth() + 1, 0);
            startDate.value = monthStart.toISOString().slice(0,10);
            endDate.value = monthEnd.toISOString().slice(0,10);
            break;
        case 'quarterly':
            const quarter = Math.floor(today.getMonth() / 3);
            const quarterStart = new Date(today.getFullYear(), quarter * 3, 1);
            const quarterEnd = new Date(today.getFullYear(), (quarter + 1) * 3, 0);
            startDate.value = quarterStart.toISOString().slice(0,10);
            endDate.value = quarterEnd.toISOString().slice(0,10);
            break;
        case 'yearly':
            const yearStart = new Date(today.getFullYear(), 0, 1);
            const yearEnd = new Date(today.getFullYear(), 11, 31);
            startDate.value = yearStart.toISOString().slice(0,10);
            endDate.value = yearEnd.toISOString().slice(0,10);
            break;
    }
});

// Search functionality
document.getElementById('searchInput').addEventListener('keyup', function() {
    const value = this.value.toLowerCase();
    const rows = document.querySelectorAll('#salesTable tbody tr');
    
    rows.forEach(row => {
        const text = row.textContent.toLowerCase();
        row.style.display = text.includes(value) ? '' : 'none';
    });
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

// 3D Hover Effects for cards (optional)
document.querySelectorAll('.stat-card, .chart-card, .table-card, .btn').forEach(element => {
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