<?php
require_once '../includes/config.php';
requireAdmin();

// Get current page name for active menu
$current_page = basename($_SERVER['PHP_SELF']);

// Set user info from session
$user_role = $_SESSION['role'] ?? 'guest';
$user_name = $_SESSION['full_name'] ?? 'User';
$username = $_SESSION['username'] ?? 'Not logged in';

// Get inventory statistics
$stats_query = "SELECT 
                  COUNT(*) as total_products,
                  COALESCE(SUM(quantity), 0) as total_items,
                  COALESCE(SUM(quantity * buying_price), 0) as total_investment,
                  COALESCE(SUM(quantity * selling_price), 0) as total_value,
                  COUNT(CASE WHEN quantity <= low_stock_threshold AND quantity > 0 THEN 1 END) as low_stock_count,
                  COUNT(CASE WHEN quantity = 0 THEN 1 END) as out_of_stock_count,
                  COALESCE(AVG(selling_price - buying_price), 0) as avg_profit_margin
                FROM products";
$stats_result = mysqli_query($conn, $stats_query);
$stats = mysqli_fetch_assoc($stats_result);

// Get all products with stock status
$products_query = "SELECT * FROM products ORDER BY 
                    CASE 
                        WHEN quantity <= low_stock_threshold AND quantity > 0 THEN 1
                        WHEN quantity = 0 THEN 2
                        ELSE 3
                    END, 
                    product_name ASC";
$products_result = mysqli_query($conn, $products_query);

// Get stock movement history
$movement_query = "SELECT 
                    s.*, 
                    p.product_name,
                    'SALE' as movement_type,
                    s.quantity as movement_quantity,
                    s.sale_date as movement_date,
                    s.total_amount as amount
                  FROM sales s
                  JOIN products p ON s.product_id = p.id
                  UNION ALL
                  SELECT 
                    NULL as id,
                    NULL as product_id,
                    NULL as quantity,
                    NULL as unit_price,
                    NULL as total_amount,
                    NULL as profit,
                    NULL as sold_by,
                    NULL as sale_date,
                    p.product_name,
                    'RESTOCK' as movement_type,
                    ph.quantity as movement_quantity,
                    ph.restock_date as movement_date,
                    ph.total_cost as amount
                  FROM product_history ph
                  JOIN products p ON ph.product_id = p.id
                  ORDER BY movement_date DESC
                  LIMIT 50";
$movement_result = mysqli_query($conn, $movement_query);

// Get potential profit (total value - total investment)
$potential_profit = $stats['total_value'] - $stats['total_investment'];
$profit_margin_percentage = $stats['total_value'] > 0 ? round(($potential_profit / $stats['total_value']) * 100, 1) : 0;
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
                    <i class="fas fa-map-marker-alt me-1"></i> Gatsibo District,Malimba City.
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
                    <i class="fas fa-warehouse me-2 text-info"></i>
                    Inventory Management
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
                            <i class="fas fa-warehouse text-info me-2"></i>
                            Inventory Overview
                        </h2>
                        <p class="text-muted">
                            <i class="fas fa-store me-2"></i>
                            Track and manage your stock levels, investments, and inventory value
                        </p>
                    </div>
                    <div class="col-md-4 text-end">
                        <div class="business-badge">
                            <span class="badge bg-info p-3">
                                <i class="fas fa-phone-alt me-2"></i>0786874837
                            </span>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Inventory Statistics Cards -->
            <div class="row g-4 mb-4">
                <!-- Total Products Card -->
                <div class="col-xl-3 col-md-6">
                    <div class="stat-card total-products-card">
                        <div class="stat-icon">
                            <i class="fas fa-boxes"></i>
                        </div>
                        <div class="stat-details">
                            <span class="stat-label">Total Products</span>
                            <h3 class="stat-value"><?php echo $stats['total_products']; ?></h3>
                            <div class="stat-footer">
                                <small>Different items</small>
                                <span class="stat-badge">Active</span>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Total Items Card -->
                <div class="col-xl-3 col-md-6">
                    <div class="stat-card total-items-card">
                        <div class="stat-icon">
                            <i class="fas fa-cubes"></i>
                        </div>
                        <div class="stat-details">
                            <span class="stat-label">Total Items</span>
                            <h3 class="stat-value"><?php echo number_format($stats['total_items']); ?></h3>
                            <div class="stat-footer">
                                <small>Units in stock</small>
                                <span class="stat-badge">Quantity</span>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Investment Card -->
                <div class="col-xl-3 col-md-6">
                    <div class="stat-card investment-card">
                        <div class="stat-icon">
                            <i class="fas fa-dollar-sign"></i>
                        </div>
                        <div class="stat-details">
                            <span class="stat-label">Investment</span>
                            <h3 class="stat-value"><?php echo number_format($stats['total_investment'], 0); ?> RWF</h3>
                            <div class="stat-footer">
                                <small>Total cost</small>
                                <span class="stat-badge">Buying price</span>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Stock Value Card -->
                <div class="col-xl-3 col-md-6">
                    <div class="stat-card value-card">
                        <div class="stat-icon">
                            <i class="fas fa-chart-line"></i>
                        </div>
                        <div class="stat-details">
                            <span class="stat-label">Stock Value</span>
                            <h3 class="stat-value"><?php echo number_format($stats['total_value'], 0); ?> RWF</h3>
                            <div class="stat-footer">
                                <small>Selling price</small>
                                <span class="stat-badge">Market value</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Second Row Stats -->
            <div class="row g-4 mb-4">
                <!-- Potential Profit Card -->
                <div class="col-xl-4 col-md-4">
                    <div class="stat-card profit-card">
                        <div class="stat-icon">
                            <i class="fas fa-coins"></i>
                        </div>
                        <div class="stat-details">
                            <span class="stat-label">Potential Profit</span>
                            <h3 class="stat-value"><?php echo number_format($potential_profit, 0); ?> RWF</h3>
                            <div class="stat-footer">
                                <small>If all sold</small>
                                <span class="stat-badge">Margin: <?php echo $profit_margin_percentage; ?>%</span>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Low Stock Card -->
                <div class="col-xl-4 col-md-4">
                    <div class="stat-card low-stock-stat-card">
                        <div class="stat-icon">
                            <i class="fas fa-exclamation-triangle"></i>
                        </div>
                        <div class="stat-details">
                            <span class="stat-label">Low Stock</span>
                            <h3 class="stat-value"><?php echo $stats['low_stock_count']; ?> Pc(s)</h3>
                            <div class="stat-footer">
                                <small>Need attention</small>
                                <span class="stat-badge">Alert</span>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Out of Stock Card -->
                <div class="col-xl-4 col-md-4">
                    <div class="stat-card out-stock-card">
                        <div class="stat-icon">
                            <i class="fas fa-times-circle"></i>
                        </div>
                        <div class="stat-details">
                            <span class="stat-label">Out of Stock</span>
                            <h3 class="stat-value"><?php echo $stats['out_of_stock_count']; ?> Pc(s)</h3>
                            <div class="stat-footer">
                                <small>Need restock</small>
                                <span class="stat-badge">Critical</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Alert Cards Row -->
            <div class="row g-4 mb-4">
                <!-- Low Stock Alert Card -->
                <div class="col-md-6">
                    <div class="card alert-card border-danger">
                        <div class="card-header bg-danger text-white">
                            <h5 class="mb-0">
                                <i class="fas fa-exclamation-triangle me-2"></i>
                                Low Stock Alert
                                <span class="badge bg-light text-danger ms-2"><?php echo $stats['low_stock_count']; ?></span>
                            </h5>
                        </div>
                        <div class="card-body">
                            <?php
                            $low_stock_query = "SELECT * FROM products WHERE quantity <= low_stock_threshold AND quantity > 0 ORDER BY quantity ASC LIMIT 10";
                            $low_stock_result = mysqli_query($conn, $low_stock_query);
                            
                            if (mysqli_num_rows($low_stock_result) > 0):
                            ?>
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
                                    <div class="item-actions">
                                        <span class="badge bg-danger me-2"><?php echo $product['quantity']; ?> left</span>
                                        <button class="btn btn-sm btn-warning" onclick="restockProduct(<?php echo $product['id']; ?>)">
                                            <i class="fas fa-plus"></i> Restock
                                        </button>
                                    </div>
                                </div>
                                <?php endwhile; ?>
                            </div>
                            <?php else: ?>
                            <div class="text-center py-4">
                                <i class="fas fa-check-circle text-success fa-3x mb-3"></i>
                                <p class="mb-0">No low stock products</p>
                                <small class="text-muted">All products are well stocked</small>
                            </div>
                            <?php endif; ?>
                        </div>
                        <div class="card-footer">
                            <button class="btn btn-outline-danger w-100" data-bs-toggle="modal" data-bs-target="#bulkRestockModal">
                                <i class="fas fa-truck me-2"></i>Bulk Restock
                            </button>
                        </div>
                    </div>
                </div>

                <!-- Out of Stock Card -->
                <div class="col-md-6">
                    <div class="card alert-card border-warning">
                        <div class="card-header bg-warning text-dark">
                            <h5 class="mb-0">
                                <i class="fas fa-times-circle me-2"></i>
                                Out of Stock
                                <span class="badge bg-dark text-white ms-2"><?php echo $stats['out_of_stock_count']; ?> Pc(s)</span>
                            </h5>
                        </div>
                        <div class="card-body">
                            <?php
                            $out_stock_query = "SELECT * FROM products WHERE quantity = 0 ORDER BY product_name ASC";
                            $out_stock_result = mysqli_query($conn, $out_stock_query);
                            
                            if (mysqli_num_rows($out_stock_result) > 0):
                            ?>
                            <div class="stock-alert-list">
                                <?php while ($product = mysqli_fetch_assoc($out_stock_result)): 
                                    $last_sale_query = "SELECT MAX(sale_date) as last_sale FROM sales WHERE product_id = " . $product['id'];
                                    $last_sale_result = mysqli_query($conn, $last_sale_query);
                                    $last_sale = mysqli_fetch_assoc($last_sale_result);
                                ?>
                                <div class="stock-alert-item">
                                    <div class="item-info">
                                        <h6 class="mb-1"><?php echo htmlspecialchars($product['product_name']); ?></h6>
                                        <small class="text-muted">
                                            <i class="fas fa-clock me-1"></i>
                                            Last sold: <?php echo $last_sale['last_sale'] ? date('Y-m-d', strtotime($last_sale['last_sale'])) : 'Never'; ?>
                                        </small>
                                    </div>
                                    <div class="item-actions">
                                        <button class="btn btn-sm btn-success" onclick="restockProduct(<?php echo $product['id']; ?>)">
                                            <i class="fas fa-plus"></i> Restock
                                        </button>
                                    </div>
                                </div>
                                <?php endwhile; ?>
                            </div>
                            <?php else: ?>
                            <div class="text-center py-4">
                                <i class="fas fa-check-circle text-success fa-3x mb-3"></i>
                                <p class="mb-0">No out of stock products</p>
                                <small class="text-muted">All products are in stock</small>
                            </div>
                            <?php endif; ?>
                        </div>
                        <div class="card-footer">
                            <button class="btn btn-outline-warning w-100" onclick="window.location.href='products.php'">
                                <i class="fas fa-box me-2"></i>Manage Products
                            </button>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Inventory Table -->
            <div class="row mb-4">
                <div class="col-12">
                    <div class="card inventory-card">
                        <div class="card-header">
                            <h5 class="mb-0">
                                <i class="fas fa-warehouse me-2"></i>
                                Current Inventory
                            </h5>
                            <div class="header-actions">
                                <button class="btn btn-primary btn-sm me-2" data-bs-toggle="modal" data-bs-target="#bulkRestockModal">
                                    <i class="fas fa-truck me-2"></i>Bulk Restock
                                </button>
                                <button class="btn btn-info btn-sm me-2" onclick="exportInventory()">
                                    <i class="fas fa-download me-2"></i>Export
                                </button>
                                <div class="search-box">
                                    <i class="fas fa-search"></i>
                                    <input type="text" class="form-control form-control-sm" 
                                           id="searchInput" placeholder="Search inventory...">
                                </div>
                            </div>
                        </div>
                        <div class="card-body">
                            <div class="table-responsive">
                                <table class="table table-hover inventory-table" id="inventoryTable">
                                    <thead>
                                        <tr>
                                            <th>Product Name</th>
                                            <th>Description</th>
                                            <th>In Stock</th>
                                            <th>Threshold</th>
                                            <th>Buying Price</th>
                                            <th>Selling Price</th>
                                            <th>Potential Profit</th>
                                            <th>Status</th>
                                            <th>Actions</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php if (mysqli_num_rows($products_result) > 0): ?>
                                            <?php while ($product = mysqli_fetch_assoc($products_result)): 
                                                $potential_profit = ($product['selling_price'] - $product['buying_price']) * $product['quantity'];
                                                $stock_status = '';
                                                $status_class = '';
                                                
                                                if ($product['quantity'] == 0) {
                                                    $stock_status = 'Out of Stock';
                                                    $status_class = 'bg-secondary';
                                                } elseif ($product['quantity'] <= $product['low_stock_threshold']) {
                                                    $stock_status = 'Low Stock';
                                                    $status_class = 'bg-danger';
                                                } else {
                                                    $stock_status = 'In Stock';
                                                    $status_class = 'bg-success';
                                                }
                                            ?>
                                            <tr>
                                                <td class="product-name"><?php echo htmlspecialchars($product['product_name']); ?></td>
                                                <td><?php echo htmlspecialchars(substr($product['description'], 0, 30)) . (strlen($product['description']) > 30 ? '...' : ''); ?></td>
                                                <td class="<?php echo $product['quantity'] <= $product['low_stock_threshold'] ? 'text-danger fw-bold' : ''; ?>">
                                                    <?php echo $product['quantity']; ?>
                                                </td>
                                                <td><?php echo $product['low_stock_threshold']; ?></td>
                                                <td><?php echo number_format($product['buying_price'], 0); ?> RWF</td>
                                                <td><?php echo number_format($product['selling_price'], 0); ?> RWF</td>
                                                <td class="text-success fw-bold"><?php echo number_format($potential_profit, 0); ?> RWF</td>
                                                <td><span class="badge <?php echo $status_class; ?>"><?php echo $stock_status; ?></span></td>
                                                <td class="action-buttons">
                                                    <button class="btn btn-sm btn-info" onclick="viewStockHistory(<?php echo $product['id']; ?>)" title="History">
                                                        <i class="fas fa-history"></i>
                                                    </button>
                                                    <button class="btn btn-sm btn-warning" onclick="editProduct(<?php echo $product['id']; ?>)" title="Edit">
                                                        <i class="fas fa-edit"></i>
                                                    </button>
                                                    <button class="btn btn-sm btn-success" onclick="restockProduct(<?php echo $product['id']; ?>)" title="Restock">
                                                        <i class="fas fa-plus"></i>
                                                    </button>
                                                </td>
                                            </tr>
                                            <?php endwhile; ?>
                                        <?php else: ?>
                                            <tr>
                                                <td colspan="9" class="text-center py-5">
                                                    <i class="fas fa-warehouse fa-4x text-muted mb-3"></i>
                                                    <h5 class="text-muted">No Products in Inventory</h5>
                                                    <p class="text-muted mb-3">Get started by adding products</p>
                                                    <button class="btn btn-primary" onclick="window.location.href='products.php'">
                                                        <i class="fas fa-plus me-2"></i>Add Products
                                                    </button>
                                                </td>
                                            </tr>
                                        <?php endif; ?>
                                    </tbody>
                                    <tfoot class="table-primary">
                                        <tr>
                                            <th colspan="4" class="text-end">Totals:</th>
                                            <th><?php echo number_format($stats['total_investment'], 0); ?> RWF</th>
                                            <th><?php echo number_format($stats['total_value'], 0); ?> RWF</th>
                                            <th><?php echo number_format($potential_profit, 0); ?> RWF</th>
                                            <th colspan="2"></th>
                                        </tr>
                                    </tfoot>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Stock Movement History -->
            <div class="row">
                <div class="col-12">
                    <div class="card movement-card">
                        <div class="card-header">
                            <h5 class="mb-0">
                                <i class="fas fa-history me-2"></i>
                                Recent Stock Movements
                            </h5>
                            <span class="badge bg-info">Last 50 movements</span>
                        </div>
                        <div class="card-body">
                            <div class="table-responsive">
                                <table class="table table-hover movement-table">
                                    <thead>
                                        <tr>
                                            <th>Date & Time</th>
                                            <th>Product Name</th>
                                            <th>Movement Type</th>
                                            <th>Quantity</th>
                                            <th>Amount</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php if (mysqli_num_rows($movement_result) > 0): ?>
                                            <?php while ($movement = mysqli_fetch_assoc($movement_result)): ?>
                                            <tr>
                                                <td><?php echo date('Y-m-d H:i', strtotime($movement['movement_date'])); ?></td>
                                                <td class="product-name"><?php echo htmlspecialchars($movement['product_name']); ?></td>
                                                <td>
                                                    <?php if ($movement['movement_type'] == 'SALE'): ?>
                                                        <span class="badge bg-danger">Sale</span>
                                                    <?php else: ?>
                                                        <span class="badge bg-success">Restock</span>
                                                    <?php endif; ?>
                                                </td>
                                                <td class="<?php echo $movement['movement_type'] == 'SALE' ? 'text-danger' : 'text-success'; ?> fw-bold">
                                                    <?php echo $movement['movement_type'] == 'SALE' ? '-' : '+'; ?><?php echo $movement['movement_quantity']; ?>
                                                Pc(s) </td>
                                                <td><?php echo isset($movement['amount']) ? number_format($movement['amount'], 0) . ' RWF' : '-'; ?></td>
                                            </tr>
                                            <?php endwhile; ?>
                                        <?php else: ?>
                                            <tr>
                                                <td colspan="5" class="text-center py-4">
                                                    <i class="fas fa-history text-muted fa-2x mb-3"></i>
                                                    <p class="mb-0">No stock movements yet</p>
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

<!-- Restock Modal -->
<div class="modal fade" id="restockModal" tabindex="-1" aria-labelledby="restockModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header bg-success text-white">
                <h5 class="modal-title" id="restockModalLabel">
                    <i class="fas fa-truck me-2"></i>Restock Product
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form id="restockForm">
                    <input type="hidden" id="restock_product_id" name="product_id">
                    
                    <div class="mb-3">
                        <label for="restock_product_name" class="form-label">Product</label>
                        <input type="text" class="form-control" id="restock_product_name" readonly>
                    </div>
                    
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="current_stock" class="form-label">Current Stock</label>
                            <input type="number" class="form-control" id="current_stock" readonly>
                        </div>
                        
                        <div class="col-md-6 mb-3">
                            <label for="restock_quantity" class="form-label">Quantity to Add <span class="text-danger">*</span></label>
                            <input type="number" class="form-control" id="restock_quantity" name="quantity" min="1" required onchange="calculateRestockTotal()" onkeyup="calculateRestockTotal()">
                        </div>
                    </div>
                    
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="new_buying_price" class="form-label">New Buying Price (RWF)</label>
                            <input type="number" class="form-control" id="new_buying_price" name="buying_price" step="0.01" min="0" onchange="calculateRestockTotal()" onkeyup="calculateRestockTotal()">
                        </div>
                        
                        <div class="col-md-6 mb-3">
                            <label for="restock_total" class="form-label">Total Cost</label>
                            <input type="text" class="form-control" id="restock_total" readonly>
                        </div>
                    </div>
                    
                    <div class="alert alert-info">
                        <i class="fas fa-info-circle me-2"></i>
                        <small>After restock, new quantity will be: <span id="new_quantity_preview">0</span></small>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                    <i class="fas fa-times me-2"></i>Cancel
                </button>
                <button type="button" class="btn btn-success" onclick="processRestock()">
                    <i class="fas fa-check me-2"></i>Confirm Restock
                </button>
            </div>
        </div>
    </div>
</div>

<!-- Bulk Restock Modal -->
<div class="modal fade" id="bulkRestockModal" tabindex="-1" aria-labelledby="bulkRestockModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-scrollable">
        <div class="modal-content">
            <div class="modal-header bg-primary text-white">
                <h5 class="modal-title" id="bulkRestockModalLabel">
                    <i class="fas fa-truck me-2"></i>Bulk Restock
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form id="bulkRestockForm" action="../actions/bulk_restock.php" method="POST">
                    <div class="table-responsive">
                        <table class="table table-bordered bulk-restock-table">
                            <thead class="table-light">
                                <tr>
                                    <th>Product Name</th>
                                    <th>Current Stock</th>
                                    <th>Quantity to Add</th>
                                    <th>New Buying Price</th>
                                    <th>New Total</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php 
                                $all_products = mysqli_query($conn, "SELECT * FROM products ORDER BY product_name");
                                while ($product = mysqli_fetch_assoc($all_products)): 
                                ?>
                                <tr>
                                    <td class="product-name"><?php echo htmlspecialchars($product['product_name']); ?></td>
                                    <td><?php echo $product['quantity']; ?> Pc(s)</td>
                                    <td>
                                        <input type="number" class="form-control form-control-sm bulk-quantity" 
                                               name="restock[<?php echo $product['id']; ?>][quantity]" 
                                               min="0" value="0" data-price="<?php echo $product['buying_price']; ?>"
                                               onchange="updateBulkTotal(this)" onkeyup="updateBulkTotal(this)">
                                    </td>
                                    <td>
                                        <input type="number" class="form-control form-control-sm bulk-price" 
                                               name="restock[<?php echo $product['id']; ?>][buying_price]" 
                                               step="0.01" min="0" value="<?php echo $product['buying_price']; ?>"
                                               onchange="updateBulkTotal(this)" onkeyup="updateBulkTotal(this)">
                                    </td>
                                    <td class="bulk-total">0 RWF</td>
                                </tr>
                                <?php endwhile; ?>
                            </tbody>
                            <tfoot class="table-primary">
                                <tr>
                                    <th colspan="4" class="text-end">Total Cost:</th>
                                    <th id="bulkTotalCost">0 RWF</th>
                                </tr>
                            </tfoot>
                        </table>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                    <i class="fas fa-times me-2"></i>Cancel
                </button>
                <button type="button" class="btn btn-primary" onclick="processBulkRestock()">
                    <i class="fas fa-check me-2"></i>Process Bulk Restock
                </button>
            </div>
        </div>
    </div>
</div>

<!-- Stock History Modal -->
<div class="modal fade" id="stockHistoryModal" tabindex="-1" aria-labelledby="stockHistoryModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-scrollable">
        <div class="modal-content">
            <div class="modal-header bg-info text-white">
                <h5 class="modal-title" id="stockHistoryModalLabel">
                    <i class="fas fa-history me-2"></i>Stock Movement History
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body" id="stockHistoryContent">
                <!-- Content will be loaded via AJAX -->
                <div class="text-center py-4">
                    <div class="spinner-border text-info" role="status">
                        <span class="visually-hidden">Loading...</span>
                    </div>
                    <p class="mt-2">Loading history...</p>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Edit Product Modal -->
<div class="modal fade" id="editProductModal" tabindex="-1" aria-labelledby="editProductModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header bg-warning text-white">
                <h5 class="modal-title" id="editProductModalLabel">
                    <i class="fas fa-edit me-2"></i>Edit Product
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form id="editProductForm">
                    <input type="hidden" id="edit_product_id" name="product_id">
                    
                    <div class="mb-3">
                        <label for="edit_product_name" class="form-label">Product Name <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" id="edit_product_name" name="product_name" required>
                    </div>
                    
                    <div class="mb-3">
                        <label for="edit_description" class="form-label">Description</label>
                        <textarea class="form-control" id="edit_description" name="description" rows="3"></textarea>
                    </div>
                    
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="edit_quantity" class="form-label">Quantity</label>
                            <input type="number" class="form-control" id="edit_quantity" name="quantity" min="0">
                        </div>
                        
                        <div class="col-md-6 mb-3">
                            <label for="edit_low_stock_threshold" class="form-label">Low Stock Alert</label>
                            <input type="number" class="form-control" id="edit_low_stock_threshold" name="low_stock_threshold" min="1">
                        </div>
                    </div>
                    
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="edit_buying_price" class="form-label">Buying Price (RWF)</label>
                            <input type="number" class="form-control" id="edit_buying_price" name="buying_price" min="0" step="0.01">
                        </div>
                        
                        <div class="col-md-6 mb-3">
                            <label for="edit_selling_price" class="form-label">Selling Price (RWF) <span class="text-danger">*</span></label>
                            <input type="number" class="form-control" id="edit_selling_price" name="selling_price" min="0" step="0.01" required>
                        </div>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                    <i class="fas fa-times me-2"></i>Cancel
                </button>
                <button type="button" class="btn btn-warning" onclick="updateProduct()">
                    <i class="fas fa-save me-2"></i>Update Product
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
    background: linear-gradient(135deg, #17a2b8 0%, #009688 100%);
    border-radius: 15px;
    padding: 25px;
    color: white;
    box-shadow: 0 10px 30px rgba(23, 162, 184, 0.3);
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
.total-products-card { background: linear-gradient(135deg, #6c5ce7 0%, #a463f5 100%); }
.total-items-card { background: linear-gradient(135deg, #00b894 0%, #00cec9 100%); }
.investment-card { background: linear-gradient(135deg, #e17055 0%, #d63031 100%); }
.value-card { background: linear-gradient(135deg, #0984e3 0%, #74b9ff 100%); }
.profit-card { background: linear-gradient(135deg, #fdcb6e 0%, #e17055 100%); }
.low-stock-stat-card { background: linear-gradient(135deg, #e74c3c 0%, #c0392b 100%); }
.out-stock-card { background: linear-gradient(135deg, #95a5a6 0%, #7f8c8d 100%); }

/* Alert Cards */
.alert-card {
    border: none;
    border-radius: 15px;
    box-shadow: 0 5px 20px rgba(0, 0, 0, 0.05);
    height: 100%;
}

.alert-card .card-header {
    border-radius: 15px 15px 0 0;
    padding: 15px 20px;
}

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

.stock-alert-item .item-actions {
    display: flex;
    align-items: center;
    gap: 10px;
}

/* Inventory Card */
.inventory-card {
    border: none;
    border-radius: 15px;
    box-shadow: 0 5px 20px rgba(0, 0, 0, 0.05);
}

.inventory-card .card-header {
    background: white;
    border-bottom: 2px solid #f1f5f9;
    padding: 20px;
    display: flex;
    justify-content: space-between;
    align-items: center;
    border-radius: 15px 15px 0 0;
}

.inventory-card .card-header h5 {
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

/* Inventory Table */
.inventory-table {
    margin-bottom: 0;
}

.inventory-table thead th {
    background: #f8f9fa;
    color: #495057;
    font-weight: 600;
    font-size: 13px;
    border-bottom: 2px solid #dee2e6;
    text-transform: uppercase;
    letter-spacing: 0.5px;
}

.inventory-table tbody tr {
    transition: all 0.3s ease;
}

.inventory-table tbody tr:hover {
    background: #f8f9fa;
    transform: scale(1.01);
    box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
}

.inventory-table .product-name {
    font-weight: 600;
    color: #2c3e50;
}

.inventory-table .action-buttons {
    white-space: nowrap;
}

.inventory-table .action-buttons .btn {
    margin: 0 3px;
    padding: 5px 10px;
    transition: all 0.3s ease;
}

.inventory-table .action-buttons .btn:hover {
    transform: translateY(-2px);
}

/* Movement Card */
.movement-card {
    border: none;
    border-radius: 15px;
    box-shadow: 0 5px 20px rgba(0, 0, 0, 0.05);
}

.movement-card .card-header {
    background: white;
    border-bottom: 2px solid #f1f5f9;
    padding: 20px;
    display: flex;
    justify-content: space-between;
    align-items: center;
    border-radius: 15px 15px 0 0;
}

.movement-table {
    margin-bottom: 0;
}

.movement-table thead th {
    background: #f8f9fa;
    color: #495057;
    font-weight: 600;
    font-size: 13px;
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

.modal-header.bg-success { background: linear-gradient(135deg, #00b894 0%, #00cec9 100%) !important; }
.modal-header.bg-primary { background: linear-gradient(135deg, #0984e3 0%, #74b9ff 100%) !important; }
.modal-header.bg-info { background: linear-gradient(135deg, #00b894 0%, #00cec9 100%) !important; }
.modal-header.bg-warning { background: linear-gradient(135deg, #fdcb6e 0%, #e17055 100%) !important; }

.modal-footer {
    border-top: 2px solid #f1f5f9;
    padding: 15px 20px;
}

/* Bulk Restock Table */
.bulk-restock-table {
    font-size: 14px;
}

.bulk-restock-table th {
    background: #f8f9fa;
}

.bulk-restock-table .product-name {
    font-weight: 600;
    color: #2c3e50;
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
.sidebar::-webkit-scrollbar,
.stock-alert-list::-webkit-scrollbar {
    width: 5px;
}

.sidebar::-webkit-scrollbar-track,
.stock-alert-list::-webkit-scrollbar-track {
    background: rgba(255, 255, 255, 0.1);
}

.sidebar::-webkit-scrollbar-thumb,
.stock-alert-list::-webkit-scrollbar-thumb {
    background: rgba(255, 255, 255, 0.3);
    border-radius: 10px;
}

.sidebar::-webkit-scrollbar-thumb:hover,
.stock-alert-list::-webkit-scrollbar-thumb:hover {
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
    
    .stock-alert-item {
        flex-direction: column;
        align-items: flex-start;
        gap: 10px;
    }
    
    .stock-alert-item .item-actions {
        width: 100%;
        justify-content: space-between;
    }
}

@media (min-width: 769px) {
    .sidebar.mobile-visible {
        left: 0;
    }
}
</style>

<!-- jQuery -->
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<!-- Bootstrap Bundle (for modals) -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
<!-- SheetJS for Excel export -->
<script src="https://cdn.sheetjs.com/xlsx-0.19.2/package/dist/xlsx.full.min.js"></script>

<script>
// Ensure jQuery is loaded
if (typeof jQuery === 'undefined') {
    console.error('jQuery failed to load.');
}

$(document).ready(function() {
    console.log('Inventory page ready.');
});

// ==================== CRUD FUNCTIONS ====================

// Restock Functions
function restockProduct(id) {
    console.log('restockProduct called with ID:', id);
    $.ajax({
        url: '../actions/get_product.php',
        type: 'POST',
        data: { product_id: id },
        dataType: 'json',
        success: function(data) {
            console.log('Product data received:', data);
            $('#restock_product_id').val(data.id);
            $('#restock_product_name').val(data.product_name);
            $('#current_stock').val(data.quantity);
            $('#new_buying_price').val(data.buying_price);
            $('#restock_quantity').val(1);
            calculateRestockTotal();
            
            var modal = new bootstrap.Modal(document.getElementById('restockModal'));
            modal.show();
        },
        error: function(xhr, status, error) {
            console.error('AJAX error:', status, error);
            alert('Error loading product data. Check console.');
        }
    });
}

function calculateRestockTotal() {
    const quantity = parseFloat($('#restock_quantity').val()) || 0;
    const price = parseFloat($('#new_buying_price').val()) || 0;
    const currentStock = parseInt($('#current_stock').val()) || 0;
    
    const total = quantity * price;
    $('#restock_total').val(total.toLocaleString() + ' RWF');
    $('#new_quantity_preview').text(currentStock + quantity);
}

function processRestock() {
    console.log('processRestock called');
    const formData = $('#restockForm').serialize();
    
    $.ajax({
        url: '../actions/restock_product.php',
        type: 'POST',
        data: formData,
        success: function(response) {
            console.log('Restock response:', response);
            var modal = bootstrap.Modal.getInstance(document.getElementById('restockModal'));
            modal.hide();
            location.reload();
        },
        error: function(xhr, status, error) {
            console.error('Restock error:', status, error);
            alert('Error processing restock. Check console.');
        }
    });
}

// Bulk Restock Functions
function updateBulkTotal(element) {
    const row = $(element).closest('tr');
    const quantity = parseFloat(row.find('.bulk-quantity').val()) || 0;
    const price = parseFloat(row.find('.bulk-price').val()) || 0;
    const total = quantity * price;
    
    row.find('.bulk-total').text(total.toLocaleString() + ' RWF');
    
    let grandTotal = 0;
    $('.bulk-total').each(function() {
        const val = $(this).text().replace(/[^0-9]/g, '');
        grandTotal += parseInt(val) || 0;
    });
    $('#bulkTotalCost').text(grandTotal.toLocaleString() + ' RWF');
}

function processBulkRestock() {
    console.log('processBulkRestock called');
    const formData = $('#bulkRestockForm').serialize();
    
    $.ajax({
        url: '../actions/bulk_restock.php',
        type: 'POST',
        data: formData,
        success: function(response) {
            console.log('Bulk restock response:', response);
            var modal = bootstrap.Modal.getInstance(document.getElementById('bulkRestockModal'));
            modal.hide();
            location.reload();
        },
        error: function(xhr, status, error) {
            console.error('Bulk restock error:', status, error);
            alert('Error processing bulk restock. Check console.');
        }
    });
}

// View Stock History
function viewStockHistory(id) {
    console.log('viewStockHistory called with ID:', id);
    $('#stockHistoryContent').html('<div class="text-center py-4"><div class="spinner-border text-info" role="status"></div><p class="mt-2">Loading history...</p></div>');
    
    var modal = new bootstrap.Modal(document.getElementById('stockHistoryModal'));
    modal.show();
    
    $.ajax({
        url: '../actions/get_stock_history.php',
        type: 'POST',
        data: { product_id: id },
        success: function(response) {
            $('#stockHistoryContent').html(response);
        },
        error: function(xhr, status, error) {
            console.error('History error:', status, error);
            $('#stockHistoryContent').html('<div class="alert alert-danger">Error loading history. Check console.</div>');
        }
    });
}

// Edit Product Functions
function editProduct(id) {
    console.log('editProduct called with ID:', id);
    $.ajax({
        url: '../actions/get_product.php',
        type: 'POST',
        data: { product_id: id },
        dataType: 'json',
        success: function(data) {
            console.log('Product data received:', data);
            $('#edit_product_id').val(data.id);
            $('#edit_product_name').val(data.product_name);
            $('#edit_description').val(data.description);
            $('#edit_quantity').val(data.quantity);
            $('#edit_low_stock_threshold').val(data.low_stock_threshold);
            $('#edit_buying_price').val(data.buying_price);
            $('#edit_selling_price').val(data.selling_price);
            
            var modal = new bootstrap.Modal(document.getElementById('editProductModal'));
            modal.show();
        },
        error: function(xhr, status, error) {
            console.error('AJAX error:', status, error);
            alert('Error loading product data. Check console.');
        }
    });
}

function updateProduct() {
    console.log('updateProduct called');
    const formData = $('#editProductForm').serialize();
    
    $.ajax({
        url: '../actions/edit_product.php',
        type: 'POST',
        data: formData,
        success: function(response) {
            console.log('Update response:', response);
            var modal = bootstrap.Modal.getInstance(document.getElementById('editProductModal'));
            modal.hide();
            location.reload();
        },
        error: function(xhr, status, error) {
            console.error('Update error:', status, error);
            alert('Error updating product. Check console.');
        }
    });
}

// Export Inventory to Excel
function exportInventory() {
    const table = document.getElementById('inventoryTable');
    if (!table) {
        alert('Table not found!');
        return;
    }
    const wb = XLSX.utils.table_to_book(table, {sheet: "Inventory"});
    XLSX.writeFile(wb, `MUVU_FX_Inventory_${new Date().toISOString().slice(0,10)}.xlsx`);
}

// Search functionality
document.getElementById('searchInput')?.addEventListener('keyup', function() {
    const value = this.value.toLowerCase();
    const rows = document.querySelectorAll('#inventoryTable tbody tr');
    
    rows.forEach(row => {
        const text = row.textContent.toLowerCase();
        row.style.display = text.includes(value) ? '' : 'none';
    });
});

// ==================== SIDEBAR FUNCTIONS ====================
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
document.querySelectorAll('.stat-card, .alert-card, .inventory-card, .btn').forEach(element => {
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