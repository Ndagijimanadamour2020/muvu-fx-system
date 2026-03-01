<?php
require_once '../includes/config.php';
requireLogin();

// Get all sales with product and user details
$sales_query = "SELECT s.*, p.product_name, u.full_name 
                FROM sales s 
                JOIN products p ON s.product_id = p.id 
                LEFT JOIN users u ON s.sold_by = u.id 
                ORDER BY s.sale_date ASC";
$sales_result = mysqli_query($conn, $sales_query);

// Get products for dropdown
$products_query = "SELECT id, product_name, selling_price, quantity, buying_price FROM products ORDER BY product_name";
$products_result = mysqli_query($conn, $products_query);

// Get current page name for active menu
$current_page = basename($_SERVER['PHP_SELF']);

// Set user info from session
$user_role = $_SESSION['role'] ?? 'guest';
$user_name = $_SESSION['full_name'] ?? 'User';
$username = $_SESSION['username'] ?? 'Not logged in';

// Get sales statistics
$today = date('Y-m-d');
$today_sales_query = "SELECT COALESCE(COUNT(*), 0) as count, COALESCE(SUM(total_amount), 0) as total, COALESCE(SUM(profit), 0) as profit 
                      FROM sales WHERE DATE(sale_date) = '$today'";
$today_sales_result = mysqli_query($conn, $today_sales_query);
$today_sales = mysqli_fetch_assoc($today_sales_result);

// Get total sales
$total_sales_query = "SELECT COALESCE(COUNT(*), 0) as count, COALESCE(SUM(total_amount), 0) as total, COALESCE(SUM(profit), 0) as profit FROM sales";
$total_sales_result = mysqli_query($conn, $total_sales_query);
$total_sales = mysqli_fetch_assoc($total_sales_result);

// Calculate totals for footer
$totals_query = "SELECT 
                  COALESCE(SUM(total_amount), 0) as total_sales,
                  COALESCE(SUM(profit), 0) as total_profit
                FROM sales";
$totals_result = mysqli_query($conn, $totals_query);
$totals = mysqli_fetch_assoc($totals_result);
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
                    <i class="fas fa-shopping-cart me-2 text-success"></i>
                    Sales Management
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
            <!-- Alert Messages -->
            <?php if (isset($_SESSION['success'])): ?>
                <div class="alert alert-success alert-dismissible fade show" role="alert">
                    <i class="fas fa-check-circle me-2"></i>
                    <?php 
                    echo $_SESSION['success']; 
                    unset($_SESSION['success']);
                    ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            <?php endif; ?>
            
            <?php if (isset($_SESSION['error'])): ?>
                <div class="alert alert-danger alert-dismissible fade show" role="alert">
                    <i class="fas fa-exclamation-circle me-2"></i>
                    <?php 
                    echo $_SESSION['error']; 
                    unset($_SESSION['error']);
                    ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            <?php endif; ?>

            <!-- Welcome Section -->
            <div class="welcome-section mb-4">
                <div class="row align-items-center">
                    <div class="col-md-8">
                        <h2 class="mb-2">
                            <i class="fas fa-shopping-cart text-success me-2"></i>
                            Sales Management
                        </h2>
                        <p class="text-muted">
                            <i class="fas fa-store me-2"></i>
                            Record and manage all your sales transactions
                        </p>
                    </div>
                    <div class="col-md-4 text-end">
                        <div class="business-badge">
                            <span class="badge bg-success p-3">
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
                                <span class="stat-badge"><?php echo $today_sales['count']; ?> transactions</span>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Today's Profit Card -->
                <div class="col-xl-3 col-md-6">
                    <div class="stat-card today-profit-card">
                        <div class="stat-icon">
                            <i class="fas fa-wallet"></i>
                        </div>
                        <div class="stat-details">
                            <span class="stat-label">Today's Profit</span>
                            <h3 class="stat-value"><?php echo number_format($today_sales['profit'], 0); ?> RWF</h3>
                            <div class="stat-footer">
                                <small>
                                    <i class="fas fa-percentage me-1"></i>
                                    Margin: <?php echo $today_sales['total'] > 0 ? round(($today_sales['profit'] / $today_sales['total']) * 100, 1) : 0; ?>%
                                </small>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Total Sales Card -->
                <div class="col-xl-3 col-md-6">
                    <div class="stat-card total-sales-card">
                        <div class="stat-icon">
                            <i class="fas fa-chart-bar"></i>
                        </div>
                        <div class="stat-details">
                            <span class="stat-label">Total Sales</span>
                            <h3 class="stat-value"><?php echo number_format($total_sales['total'], 0); ?> RWF</h3>
                            <div class="stat-footer">
                                <small>
                                    <i class="fas fa-history me-1"></i>
                                    Lifetime sales
                                </small>
                                <span class="stat-badge"><?php echo $total_sales['count']; ?> transactions</span>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Total Profit Card -->
                <div class="col-xl-3 col-md-6">
                    <div class="stat-card total-profit-card">
                        <div class="stat-icon">
                            <i class="fas fa-coins"></i>
                        </div>
                        <div class="stat-details">
                            <span class="stat-label">Total Profit</span>
                            <h3 class="stat-value"><?php echo number_format($total_sales['profit'], 0); ?> RWF</h3>
                            <div class="stat-footer">
                                <small>
                                    <i class="fas fa-check-circle me-1"></i>
                                    Net earnings
                                </small>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Sales Table -->
            <div class="row">
                <div class="col-12">
                    <div class="card sales-card">
                        <div class="card-header">
                            <h5 class="mb-0">
                                <i class="fas fa-list me-2"></i>
                                Sales Records
                            </h5>
                            <div class="header-actions">
                                <button class="btn btn-success btn-sm me-2" data-bs-toggle="modal" data-bs-target="#addSaleModal">
                                    <i class="fas fa-plus me-2"></i>Record New Sale
                                </button>
                                <button class="btn btn-info btn-sm me-2" onclick="exportSales()">
                                    <i class="fas fa-download me-2"></i>Export
                                </button>
                                <div class="search-box">
                                    <i class="fas fa-search"></i>
                                    <input type="text" class="form-control form-control-sm" 
                                           id="searchInput" placeholder="Search sales...">
                                </div>
                            </div>
                        </div>
                        <div class="card-body">
                            <div class="table-responsive">
                                <table class="table table-hover sales-table" id="salesTable">
                                    <thead>
                                        <tr>
                                            <th>#</th>
                                            <th>Date & Time</th>
                                            <th>Product Name</th>
                                            <th>Quantity</th>
                                            <th>Unit Price</th>
                                            <th>Total</th>
                                            <th>Profit</th>
                                            <th>Sold By</th>
                                            <th>Actions</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php if (mysqli_num_rows($sales_result) > 0): ?>
                                            <?php while ($sale = mysqli_fetch_assoc($sales_result)): ?>
                                            <tr>
                                                <td><span class="badge bg-secondary">#<?php echo $sale['id']; ?></span></td>
                                                <td><?php echo date('Y-m-d H:i', strtotime($sale['sale_date'])); ?></td>
                                                <td class="product-name"><?php echo htmlspecialchars($sale['product_name']); ?></td>
                                                <td><?php echo $sale['quantity']; ?> Pc(s)</td>
                                                <td><?php echo number_format($sale['unit_price'], 0); ?> RWF</td>
                                                <td class="text-success fw-bold"><?php echo number_format($sale['total_amount'], 0); ?> RWF</td>
                                                <td class="text-info fw-bold"><?php echo number_format($sale['profit'], 0); ?> RWF</td>
                                                <td><?php echo htmlspecialchars($sale['full_name']); ?></td>
                                                <td class="action-buttons">
                                                    <button class="btn btn-sm btn-info" onclick="viewSale(<?php echo $sale['id']; ?>)" title="View">
                                                        <i class="fas fa-eye"></i>
                                                    </button>
                                                    <button class="btn btn-sm btn-warning" onclick="editSale(<?php echo $sale['id']; ?>)" title="Edit">
                                                        <i class="fas fa-edit"></i>
                                                    </button>
                                                    <?php if (isAdmin()): ?>
                                                    <button class="btn btn-sm btn-danger" onclick="deleteSale(<?php echo $sale['id']; ?>)" title="Delete">
                                                        <i class="fas fa-trash"></i>
                                                    </button>
                                                    <?php endif; ?>
                                                </td>
                                            </tr>
                                            <?php endwhile; ?>
                                        <?php else: ?>
                                            <tr>
                                                <td colspan="9" class="text-center py-5">
                                                    <i class="fas fa-shopping-cart fa-4x text-muted mb-3"></i>
                                                    <h5 class="text-muted">No Sales Records Found</h5>
                                                    <p class="text-muted mb-3">Get started by recording your first sale</p>
                                                    <button class="btn btn-success" data-bs-toggle="modal" data-bs-target="#addSaleModal">
                                                        <i class="fas fa-plus me-2"></i>Record New Sale
                                                    </button>
                                                </td>
                                            </tr>
                                        <?php endif; ?>
                                    </tbody>
                                    <tfoot class="table-primary">
                                        <tr>
                                            <th colspan="5" class="text-end">Totals:</th>
                                            <th><?php echo number_format($totals['total_sales'], 0); ?> RWF</th>
                                            <th><?php echo number_format($totals['total_profit'], 0); ?> RWF</th>
                                            <th colspan="2"></th>
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

<!-- Add Sale Modal -->
<div class="modal fade" id="addSaleModal" tabindex="-1" aria-labelledby="addSaleModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header bg-success text-white">
                <h5 class="modal-title" id="addSaleModalLabel">
                    <i class="fas fa-plus-circle me-2"></i>Record New Sale
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form id="addSaleForm" action="../actions/add_sale.php" method="POST">
                    <div class="mb-3">
                        <label for="add_product_id" class="form-label">Select Product <span class="text-danger">*</span></label>
                        <select class="form-select" id="add_product_id" name="product_id" required onchange="getAddProductPrice()">
                            <option value="">-- Choose a product --</option>
                            <?php 
                            mysqli_data_seek($products_result, 0);
                            while ($product = mysqli_fetch_assoc($products_result)): 
                                if ($product['quantity'] > 0):
                            ?>
                            <option value="<?php echo $product['id']; ?>" 
                                    data-price="<?php echo $product['selling_price']; ?>"
                                    data-stock="<?php echo $product['quantity']; ?>"
                                    data-buying="<?php echo $product['buying_price']; ?>">
                                <?php echo htmlspecialchars($product['product_name']); ?> 
                                (Quantity in Stock: <?php echo $product['quantity']; ?> Piece(s)) 
                            </option>
                            <?php 
                                endif;
                            endwhile; 
                            ?>
                        </select>
                    </div>
                    
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="add_quantity" class="form-label">Quantity <span class="text-danger">*</span></label>
                            <input type="number" class="form-control" id="add_quantity" name="quantity" min="1" required onkeyup="calculateAddTotal()" onchange="calculateAddTotal()">
                        </div>
                        
                        <div class="col-md-6 mb-3">
                            <label for="add_unit_price" class="form-label">Unit Price (RWF)</label>
                            <input type="number" class="form-control" id="add_unit_price" name="unit_price" step="0.01" readonly>
                        </div>
                    </div>
                    
                    <div class="mb-3">
                        <label for="add_total_amount" class="form-label">Total Amount (RWF)</label>
                        <div class="input-group">
                            <input type="number" class="form-control" id="add_total_amount" name="total_amount" step="0.01" readonly>
                            <span class="input-group-text bg-light" id="profit_preview">Profit: 0 RWF</span>
                        </div>
                    </div>
                    
                    <div class="alert alert-info d-flex align-items-center">
                        <i class="fas fa-info-circle me-2 fa-lg"></i>
                        <div>
                            <strong>Available stock:</strong> <span id="add_available_stock">0</span> Piece(s)<br>
                            <small class="text-muted" id="buying_price_display">Buying price: 0 RWF/Piece(s)</small>
                        </div>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                    <i class="fas fa-times me-2"></i>Cancel
                </button>
                <button type="submit" form="addSaleForm" class="btn btn-success">
                    <i class="fas fa-save me-2"></i>Record Sale
                </button>
            </div>
        </div>
    </div>
</div>

<!-- Edit Sale Modal -->
<div class="modal fade" id="editSaleModal" tabindex="-1" aria-labelledby="editSaleModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header bg-warning text-white">
                <h5 class="modal-title" id="editSaleModalLabel">
                    <i class="fas fa-edit me-2"></i>Edit Sale
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form id="editSaleForm">
                    <input type="hidden" id="edit_sale_id" name="sale_id">
                    
                    <div class="mb-3">
                        <label for="edit_product_id" class="form-label">Select Product <span class="text-danger">*</span></label>
                        <select class="form-select" id="edit_product_id" name="product_id" required onchange="getEditProductPrice()">
                            <option value="">-- Choose a product --</option>
                            <?php 
                            mysqli_data_seek($products_result, 0);
                            while ($product = mysqli_fetch_assoc($products_result)): 
                            ?>
                            <option value="<?php echo $product['id']; ?>" 
                                    data-price="<?php echo $product['selling_price']; ?>"
                                    data-stock="<?php echo $product['quantity']; ?>"
                                    data-buying="<?php echo $product['buying_price']; ?>">
                                <?php echo htmlspecialchars($product['product_name']); ?> 
                                (Stock: <?php echo $product['quantity']; ?>)
                            </option>
                            <?php endwhile; ?>
                        </select>
                    </div>
                    
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="edit_quantity" class="form-label">Quantity <span class="text-danger">*</span></label>
                            <input type="number" class="form-control" id="edit_quantity" name="quantity" min="1" required onkeyup="calculateEditTotal()" onchange="calculateEditTotal()">
                        </div>
                        
                        <div class="col-md-6 mb-3">
                            <label for="edit_unit_price" class="form-label">Unit Price (RWF)</label>
                            <input type="number" class="form-control" id="edit_unit_price" name="unit_price" step="0.01" readonly>
                        </div>
                    </div>
                    
                    <div class="mb-3">
                        <label for="edit_total_amount" class="form-label">Total Amount (RWF)</label>
                        <div class="input-group">
                            <input type="number" class="form-control" id="edit_total_amount" name="total_amount" step="0.01" readonly>
                            <span class="input-group-text bg-light" id="edit_profit_preview">Profit: 0 RWF</span>
                        </div>
                    </div>
                    
                    <div class="alert alert-warning" id="stock_warning" style="display: none;">
                        <i class="fas fa-exclamation-triangle me-2"></i>
                        <span id="stock_warning_message"></span>
                    </div>
                    
                    <div class="alert alert-info">
                        <i class="fas fa-info-circle me-2"></i>
                        <strong>Current available stock:</strong> <span id="edit_available_stock">0</span> units<br>
                        <small class="text-muted" id="original_quantity_display">Original quantity: <span id="original_quantity">0</span></small>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                    <i class="fas fa-times me-2"></i>Cancel
                </button>
                <button type="button" class="btn btn-warning" onclick="submitEditSale()">
                    <i class="fas fa-save me-2"></i>Update Sale
                </button>
            </div>
        </div>
    </div>
</div>

<!-- View Sale Modal -->
<div class="modal fade" id="viewSaleModal" tabindex="-1" aria-labelledby="viewSaleModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header bg-info text-white">
                <h5 class="modal-title" id="viewSaleModalLabel">
                    <i class="fas fa-info-circle me-2"></i>Sale Details
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body" id="viewSaleContent">
                <!-- Content will be loaded via AJAX -->
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                    <i class="fas fa-times me-2"></i>Close
                </button>
            </div>
        </div>
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
    background: linear-gradient(135deg, #28a745 0%, #20c997 100%);
    border-radius: 15px;
    padding: 25px;
    color: white;
    box-shadow: 0 10px 30px rgba(40, 167, 69, 0.3);
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
.today-profit-card { background: linear-gradient(135deg, #ffc107 0%, #fd7e14 100%); }
.total-sales-card { background: linear-gradient(135deg, #17a2b8 0%, #009688 100%); }
.total-profit-card { background: linear-gradient(135deg, #6610f2 0%, #6f42c1 100%); }

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

.sales-table .action-buttons {
    white-space: nowrap;
}

.sales-table .action-buttons .btn {
    margin: 0 3px;
    padding: 5px 10px;
    transition: all 0.3s ease;
}

.sales-table .action-buttons .btn:hover {
    transform: translateY(-2px);
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

/* Modal Styles */
.modal-content {
    border: none;
    border-radius: 15px;
    box-shadow: 0 20px 40px rgba(0, 0, 0, 0.2);
}

.modal-header {
    border-radius: 15px 15px 0 0;
    padding: 15px 20px;
}

.modal-header.bg-success { background: linear-gradient(135deg, #28a745 0%, #20c997 100%) !important; }
.modal-header.bg-warning { background: linear-gradient(135deg, #ffc107 0%, #fd7e14 100%) !important; }
.modal-header.bg-info { background: linear-gradient(135deg, #17a2b8 0%, #009688 100%) !important; }

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
        font-size: 20px;
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
}

@media (min-width: 769px) {
    .sidebar.mobile-visible {
        left: 0;
    }
}
</style>

<!-- jQuery (ensure it's loaded) -->
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script>
// Ensure jQuery is loaded even if the CDN fails
if (typeof jQuery === 'undefined') {
    console.error('jQuery failed to load from CDN. Falling back to local.');
    var script = document.createElement('script');
    script.src = '../assets/js/jquery.min.js'; // adjust path if needed
    document.head.appendChild(script);
}

$(document).ready(function() {
    console.log('Sales page ready.');
});

// ==================== ADD SALE FUNCTIONS ====================

function getAddProductPrice() {
    const select = document.getElementById('add_product_id');
    const option = select.options[select.selectedIndex];
    
    if (option.value) {
        const price = option.dataset.price;
        const stock = option.dataset.stock;
        const buyingPrice = option.dataset.buying;
        
        document.getElementById('add_unit_price').value = price;
        document.getElementById('add_available_stock').textContent = stock;
        document.getElementById('buying_price_display').innerHTML = `Buying price: ${Number(buyingPrice).toLocaleString()} RWF/unit`;
        
        // Store buying price for profit calculation
        select.dataset.currentBuying = buyingPrice;
        
        calculateAddTotal();
    } else {
        // Reset fields
        document.getElementById('add_unit_price').value = '';
        document.getElementById('add_available_stock').textContent = '0';
        document.getElementById('buying_price_display').innerHTML = 'Buying price: 0 RWF/unit';
        document.getElementById('add_total_amount').value = '';
        document.getElementById('profit_preview').textContent = 'Profit: 0 RWF';
    }
}

function calculateAddTotal() {
    const quantity = document.getElementById('add_quantity').value;
    const unitPrice = document.getElementById('add_unit_price').value;
    const select = document.getElementById('add_product_id');
    const option = select.options[select.selectedIndex];
    
    if (quantity && unitPrice && option && option.value) {
        const buyingPrice = parseFloat(option.dataset.buying) || 0;
        const total = quantity * unitPrice;
        const profit = (unitPrice - buyingPrice) * quantity;
        
        document.getElementById('add_total_amount').value = total.toFixed(2);
        document.getElementById('profit_preview').textContent = `Profit: ${profit.toLocaleString(undefined, {minimumFractionDigits: 0, maximumFractionDigits: 0})} RWF`;
        
        // Optional: Check stock
        const availableStock = parseInt(option.dataset.stock) || 0;
        if (parseInt(quantity) > availableStock) {
            document.getElementById('profit_preview').innerHTML += ' (⚠️ Exceeds stock!)';
        }
    }
}

// ==================== EDIT SALE FUNCTIONS ====================

let originalSaleData = {};

function editSale(id) {
    console.log('editSale called with ID:', id);
    $.ajax({
        url: '../actions/get_sale.php',
        type: 'POST',
        data: { sale_id: id },
        dataType: 'json',
        success: function(data) {
            console.log('Sale data received:', data);
            originalSaleData = data;
            
            $('#edit_sale_id').val(data.id);
            $('#edit_product_id').val(data.product_id);
            $('#edit_quantity').val(data.quantity);
            $('#edit_unit_price').val(data.unit_price);
            $('#edit_total_amount').val(data.total_amount);
            $('#original_quantity').text(data.quantity);
            $('#original_quantity_display').html(`Original quantity: <strong>${data.quantity}</strong>`);
            
            // Update available stock display
            updateEditStockInfo();
            
            // Open the modal using Bootstrap's modal API
            var modalElement = document.getElementById('editSaleModal');
            if (modalElement) {
                try {
                    var editModal = new bootstrap.Modal(modalElement);
                    editModal.show();
                } catch (e) {
                    console.error('Bootstrap modal error:', e);
                    // Fallback to jQuery if Bootstrap fails
                    if (typeof $ !== 'undefined' && $.fn.modal) {
                        $('#editSaleModal').modal('show');
                    } else {
                        alert('Could not open edit modal. Bootstrap may not be loaded.');
                    }
                }
            } else {
                alert('Edit modal element not found.');
            }
        },
        error: function(xhr, status, error) {
            console.error('AJAX error:', status, error);
            console.log('Response:', xhr.responseText);
            alert('Error loading sale data. Check the console.');
        }
    });
}

function getEditProductPrice() {
    updateEditStockInfo();
    calculateEditTotal();
    checkStockAvailability();
}

function updateEditStockInfo() {
    const select = document.getElementById('edit_product_id');
    const option = select.options[select.selectedIndex];
    
    if (option.value) {
        const price = option.dataset.price;
        const stock = option.dataset.stock;
        const buyingPrice = option.dataset.buying;
        const originalQty = parseInt(document.getElementById('original_quantity').textContent);
        const currentStock = parseInt(stock);
        const effectiveStock = currentStock + originalQty; // Add back original quantity
        
        document.getElementById('edit_unit_price').value = price;
        document.getElementById('edit_available_stock').textContent = effectiveStock;
        
        // Store buying price for profit calculation
        select.dataset.currentBuying = buyingPrice;
    }
}

function calculateEditTotal() {
    const quantity = document.getElementById('edit_quantity').value;
    const unitPrice = document.getElementById('edit_unit_price').value;
    const select = document.getElementById('edit_product_id');
    const option = select.options[select.selectedIndex];
    
    if (quantity && unitPrice && option && option.value) {
        const buyingPrice = parseFloat(option.dataset.buying) || 0;
        const total = quantity * unitPrice;
        const profit = (unitPrice - buyingPrice) * quantity;
        
        document.getElementById('edit_total_amount').value = total.toFixed(2);
        document.getElementById('edit_profit_preview').textContent = `Profit: ${profit.toLocaleString(undefined, {minimumFractionDigits: 0, maximumFractionDigits: 0})} RWF`;
        
        checkStockAvailability();
    }
}

function checkStockAvailability() {
    const newQuantity = parseInt(document.getElementById('edit_quantity').value) || 0;
    const originalQuantity = parseInt(document.getElementById('original_quantity').textContent) || 0;
    const availableStock = parseInt(document.getElementById('edit_available_stock').textContent) || 0;
    
    const quantityDifference = newQuantity - originalQuantity;
    const warningDiv = document.getElementById('stock_warning');
    const warningMessage = document.getElementById('stock_warning_message');
    
    if (quantityDifference > availableStock) {
        warningDiv.style.display = 'block';
        warningMessage.textContent = `Insufficient stock! Need ${quantityDifference} more units but only ${availableStock} available.`;
        document.getElementById('edit_quantity').value = originalQuantity + availableStock;
        calculateEditTotal();
    } else {
        warningDiv.style.display = 'none';
    }
}

function submitEditSale() {
    console.log('submitEditSale called');
    const formData = $('#editSaleForm').serialize();
    console.log('Form data:', formData);
    
    $.ajax({
        url: '../actions/edit_sale.php',
        type: 'POST',
        data: formData,
        success: function(response) {
            console.log('Update response:', response);
            // Close modal
            try {
                var editModal = bootstrap.Modal.getInstance(document.getElementById('editSaleModal'));
                if (editModal) editModal.hide();
            } catch (e) {
                console.warn('Error closing modal, falling back to jQuery:', e);
                $('#editSaleModal').modal('hide');
            }
            // Reload page to show updated data
            location.reload();
        },
        error: function(xhr, status, error) {
            console.error('Update error:', status, error);
            console.log('Response:', xhr.responseText);
            alert('Error updating sale. Check the console.');
        }
    });
}

// View Sale Function
function viewSale(id) {
    console.log('viewSale called with ID:', id);
    $.ajax({
        url: '../actions/get_sale.php',
        type: 'POST',
        data: { sale_id: id },
        dataType: 'json',
        success: function(data) {
            console.log('Sale details received:', data);
            let profitMargin = ((data.profit / data.total_amount) * 100).toFixed(2);
            
            let html = `
                <div class="sale-details">
                    <div class="text-center mb-4">
                        <i class="fas fa-receipt fa-4x text-info"></i>
                        <h4 class="mt-3">Sale #${data.id}</h4>
                    </div>
                    
                    <table class="table table-borderless">
                        <tr>
                            <th width="40%">Date & Time:</th>
                            <td><strong>${new Date(data.sale_date).toLocaleString()}</strong></td>
                        </tr>
                        <tr>
                            <th>Product:</th>
                            <td><strong class="text-primary">${data.product_name}</strong></td>
                        </tr>
                        <tr>
                            <th>Quantity:</th>
                            <td>${data.quantity} units</td>
                        </tr>
                        <tr>
                            <th>Unit Price:</th>
                            <td>${Number(data.unit_price).toLocaleString()} RWF</td>
                        </tr>
                        <tr>
                            <th>Total Amount:</th>
                            <td class="text-success fw-bold">${Number(data.total_amount).toLocaleString()} RWF</td>
                        </tr>
                        <tr>
                            <th>Profit:</th>
                            <td class="text-info fw-bold">${Number(data.profit).toLocaleString()} RWF</td>
                        </tr>
                        <tr>
                            <th>Profit Margin:</th>
                            <td class="text-info fw-bold">${profitMargin}%</td>
                        </tr>
                        <tr>
                            <th>Sold By:</th>
                            <td>${data.full_name ? data.full_name : 'Unknown'}</td>
                        </tr>
                    </table>
                </div>
            `;
            
            $('#viewSaleContent').html(html);
            
            // Open the modal
            var modalElement = document.getElementById('viewSaleModal');
            if (modalElement) {
                try {
                    var viewModal = new bootstrap.Modal(modalElement);
                    viewModal.show();
                } catch (e) {
                    console.error('Error opening view modal:', e);
                    if (typeof $ !== 'undefined' && $.fn.modal) {
                        $('#viewSaleModal').modal('show');
                    }
                }
            }
        },
        error: function(xhr, status, error) {
            console.error('View error:', status, error);
            console.log('Response:', xhr.responseText);
            alert('Error loading sale details. Check the console.');
        }
    });
}

// Delete Sale Function
function deleteSale(id) {
    console.log('deleteSale called with ID:', id);
    if (confirm('Are you sure you want to delete this sale? This will restore the products to inventory.')) {
        $.ajax({
            url: '../actions/delete_sale.php',
            type: 'POST',
            data: { sale_id: id },
            success: function(response) {
                console.log('Delete response:', response);
                if (response.trim() === 'success') {
                    location.reload();
                } else {
                    alert('Unexpected response: ' + response);
                }
            },
            error: function(xhr, status, error) {
                console.error('Delete error:', status, error);
                console.log('Response:', xhr.responseText);
                alert('Error deleting sale. Check the console.');
            }
        });
    }
}

// Export Sales to Excel
function exportSales() {
    const table = document.getElementById('salesTable');
    const wb = XLSX.utils.table_to_book(table, {sheet: "Sales"});
    XLSX.writeFile(wb, `MUVU_FX_Sales_${new Date().toISOString().slice(0,10)}.xlsx`);
}

// Search functionality
document.getElementById('searchInput').addEventListener('keyup', function() {
    const value = this.value.toLowerCase();
    const rows = document.querySelectorAll('#salesTable tbody tr');
    
    rows.forEach(row => {
        const text = row.textContent.toLowerCase();
        row.style.display = text.includes(value) ? '' : 'none';
    });
});

// ==================== SIDEBAR FUNCTIONS ====================
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
document.querySelectorAll('.stat-card, .sales-card, .btn').forEach(element => {
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

<!-- Include SheetJS for Excel export -->
<script src="https://cdn.sheetjs.com/xlsx-0.19.2/package/dist/xlsx.full.min.js"></script>

<?php include '../includes/footer.php'; ?>