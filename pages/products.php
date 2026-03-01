<?php
require_once '../includes/config.php';
requireAdmin();

// Get all products
$products_query = "SELECT * FROM products ORDER BY created_at ASC";
$products_result = mysqli_query($conn, $products_query);

// Get current page name for active menu
$current_page = basename($_SERVER['PHP_SELF']);

// Set user info from session (correct keys)
$user_role = $_SESSION['role'] ?? 'guest';
$user_name = $_SESSION['full_name'] ?? 'User';
$username = $_SESSION['username'] ?? 'Not logged in';
// Get total products count for stats
$total_products_query = "SELECT COUNT(*) as total FROM products";
$total_products_result = mysqli_query($conn, $total_products_query);
$total_products = mysqli_fetch_assoc($total_products_result)['total'];

// Get low stock count
$low_stock_count_query = "SELECT COUNT(*) as count FROM products WHERE quantity <= low_stock_threshold";
$low_stock_count_result = mysqli_query($conn, $low_stock_count_query);
$low_stock_count = mysqli_fetch_assoc($low_stock_count_result)['count'];

// Get out of stock count
$out_stock_query = "SELECT COUNT(*) as count FROM products WHERE quantity = 0";
$out_stock_result = mysqli_query($conn, $out_stock_query);
$out_stock_count = mysqli_fetch_assoc($out_stock_result)['count'];
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
                    <i class="fas fa-box me-2 text-primary"></i>
                    Products Management
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
                            <i class="fas fa-boxes text-primary me-2"></i>
                            Product Management
                        </h2>
                        <p class="text-muted">
                            <i class="fas fa-store me-2"></i>
                            Manage your products, stock levels, and pricing
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
                <!-- Total Products Card -->
                <div class="col-xl-4 col-md-4">
                    <div class="stat-card total-products-card">
                        <div class="stat-icon">
                            <i class="fas fa-boxes"></i>
                        </div>
                        <div class="stat-details">
                            <span class="stat-label">Total Products</span>
                            <h3 class="stat-value"><?php echo $total_products; ?></h3>
                            <div class="stat-footer">
                                <small>Active in inventory</small>
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
                            <h3 class="stat-value"><?php echo $low_stock_count; ?></h3>
                            <div class="stat-footer">
                                <small>Need attention</small>
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
                            <h3 class="stat-value"><?php echo $out_stock_count; ?></h3>
                            <div class="stat-footer">
                                <small>Need restock</small>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Products Table -->
            <div class="row">
                <div class="col-12">
                    <div class="card products-card">
                        <div class="card-header">
                            <h5 class="mb-0">
                                <i class="fas fa-list me-2"></i>
                                Products List
                            </h5>
                            <button class="btn btn-primary btn-sm" data-bs-toggle="modal" data-bs-target="#addProductModal">
                                <i class="fas fa-plus me-2"></i>Add New Product
                            </button>
                        </div>
                        <div class="card-body">
                            <div class="table-responsive">
                                <table class="table table-hover products-table" id="productsTable">
                                    <thead>
                                        <tr>
                                            <th>#</th>
                                            <th>Product Name</th>
                                            <th>Description</th>
                                            <th>Quantity</th>
                                            <th>Buying Price</th>
                                            <th>Selling Price</th>
                                            <th>Status</th>
                                            <th>Actions</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php if (mysqli_num_rows($products_result) > 0): ?>
                                            <?php while ($product = mysqli_fetch_assoc($products_result)): ?>
                                            <tr>
                                                <td><?php echo $product['id']; ?></td>
                                                <td class="product-name"><?php echo htmlspecialchars($product['product_name']); ?></td>
                                                <td><?php echo htmlspecialchars(substr($product['description'], 0, 50)) . (strlen($product['description']) > 50 ? '...' : ''); ?></td>
                                                <td class="<?php echo $product['quantity'] <= $product['low_stock_threshold'] ? 'text-danger fw-bold' : ''; ?>">
                                                    <?php echo $product['quantity']; ?> Pc(s)
                                                </td>
                                                <td><?php echo number_format($product['buying_price'], 0); ?> RWF</td>
                                                <td><?php echo number_format($product['selling_price'], 0); ?> RWF</td>
                                                <td>
                                                    <?php
                                                    if ($product['quantity'] <= $product['low_stock_threshold'] && $product['quantity'] > 0) {
                                                        echo '<span class="badge bg-warning text-dark">Low Stock</span>';
                                                    } elseif ($product['quantity'] == 0) {
                                                        echo '<span class="badge bg-secondary">Out of Stock</span>';
                                                    } else {
                                                        echo '<span class="badge bg-success">In Stock</span>';
                                                    }
                                                    ?>
                                                </td>
                                                <td class="action-buttons">
                                                    <button class="btn btn-sm btn-info" onclick="viewProduct(<?php echo $product['id']; ?>)" title="View">
                                                        <i class="fas fa-eye"></i>
                                                    </button>
                                                    <button class="btn btn-sm btn-warning" onclick="editProduct(<?php echo $product['id']; ?>)" title="Edit">
                                                        <i class="fas fa-edit"></i>
                                                    </button>
                                                    <button class="btn btn-sm btn-danger" onclick="deleteProduct(<?php echo $product['id']; ?>)" title="Delete">
                                                        <i class="fas fa-trash"></i>
                                                    </button>
                                                </td>
                                            </tr>
                                            <?php endwhile; ?>
                                        <?php else: ?>
                                            <tr>
                                                <td colspan="8" class="text-center py-5">
                                                    <i class="fas fa-box-open fa-4x text-muted mb-3"></i>
                                                    <h5 class="text-muted">No Products Found</h5>
                                                    <p class="text-muted mb-3">Get started by adding your first product</p>
                                                    <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addProductModal">
                                                        <i class="fas fa-plus me-2"></i>Add New Product
                                                    </button>
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

<!-- Add Product Modal -->
<div class="modal fade" id="addProductModal" tabindex="-1" aria-labelledby="addProductModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header bg-primary text-white">
                <h5 class="modal-title" id="addProductModalLabel">
                    <i class="fas fa-plus-circle me-2"></i>Add New Product
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form id="addProductForm" action="../actions/add_product.php" method="POST">
                    <div class="mb-3">
                        <label for="product_name" class="form-label">Product Name <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" id="product_name" name="product_name" required 
                               placeholder="Enter product name">
                    </div>
                    
                    <div class="mb-3">
                        <label for="description" class="form-label">Description</label>
                        <textarea class="form-control" id="description" name="description" rows="3" 
                                  placeholder="Enter product description"></textarea>
                    </div>
                    
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="quantity" class="form-label">Initial Quantity</label>
                            <input type="number" class="form-control" id="quantity" name="quantity" value="0" min="0">
                        </div>
                        
                        <div class="col-md-6 mb-3">
                            <label for="low_stock_threshold" class="form-label">Low Stock Alert</label>
                            <input type="number" class="form-control" id="low_stock_threshold" name="low_stock_threshold" value="10" min="1">
                        </div>
                    </div>
                    
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="buying_price" class="form-label">Buying Price (RWF)</label>
                            <input type="number" class="form-control" id="buying_price" name="buying_price" value="0" min="0" step="0.01"
                                   placeholder="0.00">
                        </div>
                        
                        <div class="col-md-6 mb-3">
                            <label for="selling_price" class="form-label">Selling Price (RWF) <span class="text-danger">*</span></label>
                            <input type="number" class="form-control" id="selling_price" name="selling_price" value="0" min="0" step="0.01" required
                                   placeholder="0.00">
                        </div>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                    <i class="fas fa-times me-2"></i>Cancel
                </button>
                <button type="submit" form="addProductForm" class="btn btn-primary">
                    <i class="fas fa-save me-2"></i>Save Product
                </button>
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

<!-- View Product Modal -->
<div class="modal fade" id="viewProductModal" tabindex="-1" aria-labelledby="viewProductModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header bg-info text-white">
                <h5 class="modal-title" id="viewProductModalLabel">
                    <i class="fas fa-info-circle me-2"></i>Product Details
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body" id="viewProductContent">
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

/* Stat Card Colors */
.total-products-card { background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); }
.low-stock-stat-card { background: linear-gradient(135deg, #f093fb 0%, #f5576c 100%); }
.out-stock-card { background: linear-gradient(135deg, #4facfe 0%, #00f2fe 100%); }

/* Products Card */
.products-card {
    border: none;
    border-radius: 15px;
    box-shadow: 0 5px 20px rgba(0, 0, 0, 0.05);
    animation: fadeIn 0.5s ease;
}

.products-card .card-header {
    background: white;
    border-bottom: 2px solid #f1f5f9;
    padding: 20px;
    display: flex;
    justify-content: space-between;
    align-items: center;
    border-radius: 15px 15px 0 0;
}

.products-card .card-header h5 {
    color: #2c3e50;
    font-weight: 600;
}

/* Products Table */
.products-table {
    margin-bottom: 0;
}

.products-table thead th {
    background: #f8f9fa;
    color: #495057;
    font-weight: 600;
    font-size: 13px;
    border-bottom: 2px solid #dee2e6;
    text-transform: uppercase;
    letter-spacing: 0.5px;
}

.products-table tbody tr {
    transition: all 0.3s ease;
}

.products-table tbody tr:hover {
    background: #f8f9fa;
    transform: scale(1.01);
    box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
}

.products-table .product-name {
    font-weight: 600;
    color: #2c3e50;
}

.products-table .action-buttons {
    white-space: nowrap;
}

.products-table .action-buttons .btn {
    margin: 0 3px;
    padding: 5px 10px;
    transition: all 0.3s ease;
}

.products-table .action-buttons .btn:hover {
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

.modal-header.bg-primary { background: linear-gradient(135deg, #667eea 0%, #764ba2 100%) !important; }
.modal-header.bg-warning { background: linear-gradient(135deg, #f093fb 0%, #f5576c 100%) !important; }
.modal-header.bg-info { background: linear-gradient(135deg, #4facfe 0%, #00f2fe 100%) !important; }

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
    
    .products-card .card-header {
        flex-direction: column;
        gap: 10px;
    }
    
    .products-card .card-header button {
        width: 100%;
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
    console.log('Products page ready.');

    // Global AJAX error handler (optional)
    $(document).ajaxError(function(event, jqxhr, settings, thrownError) {
        console.error('AJAX error:', settings.url, thrownError);
        console.log('Response:', jqxhr.responseText);
        alert('An error occurred. Check the console for details.');
    });
});

// ==================== CRUD FUNCTIONS ====================

// Edit Product: load data into modal
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
            
            // Open the modal using Bootstrap's modal method
            var editModal = new bootstrap.Modal(document.getElementById('editProductModal'));
            editModal.show();
        },
        error: function(xhr, status, error) {
            console.error('AJAX error:', status, error);
            console.log('Response:', xhr.responseText);
            alert('Error loading product data. Please check the console.');
        }
    });
}

// Update Product: send edited data
function updateProduct() {
    console.log('updateProduct called');
    var formData = $('#editProductForm').serialize();
    console.log('Form data:', formData);
    
    $.ajax({
        url: '../actions/edit_product.php',
        type: 'POST',
        data: formData,
        success: function(response) {
            console.log('Update response:', response);
            // Close modal
            var editModal = bootstrap.Modal.getInstance(document.getElementById('editProductModal'));
            if (editModal) editModal.hide();
            // Reload page to show updated data
            location.reload();
        },
        error: function(xhr, status, error) {
            console.error('Update error:', status, error);
            console.log('Response:', xhr.responseText);
            alert('Error updating product. Check the console.');
        }
    });
}

// Delete Product: confirm and delete
function deleteProduct(id) {
    console.log('deleteProduct called with ID:', id);
    if (confirm('Are you sure you want to delete this product? This action cannot be undone.')) {
        $.ajax({
            url: '../actions/delete_product.php',
            type: 'POST',
            data: { product_id: id },
            success: function(response) {
                console.log('Delete response:', response);
                if (response.trim() === 'has_sales') {
                    alert('Cannot delete product because it has sales records.');
                } else if (response.trim() === 'success') {
                    location.reload();
                } else {
                    alert('Unexpected response: ' + response);
                }
            },
            error: function(xhr, status, error) {
                console.error('Delete error:', status, error);
                console.log('Response:', xhr.responseText);
                alert('Error deleting product. Check the console.');
            }
        });
    }
}

// View Product: load details into modal
function viewProduct(id) {
    console.log('viewProduct called with ID:', id);
    $.ajax({
        url: '../actions/get_product.php',
        type: 'POST',
        data: { product_id: id },
        dataType: 'json',
        success: function(data) {
            console.log('Product details received:', data);
            let profit = data.selling_price - data.buying_price;
            let profitMargin = ((profit / data.selling_price) * 100).toFixed(2);
            
            let html = `
                <div class="product-details">
                    <div class="text-center mb-4">
                        <i class="fas fa-box fa-4x text-info"></i>
                        <h4 class="mt-3">${data.product_name}</h4>
                    </div>
                    
                    <table class="table table-borderless">
                        <tr>
                            <th width="40%">Product ID:</th>
                            <td><strong>${data.id}</strong></td>
                        </tr>
                        <tr>
                            <th>Description:</th>
                            <td>${data.description ? data.description : 'No description'}</td>
                        </tr>
                        <tr>
                            <th>Current Stock:</th>
                            <td>
                                <span class="badge bg-${data.quantity <= data.low_stock_threshold ? 'warning' : 'success'} fs-6">
                                    ${data.quantity} units
                                </span>
                            </td>
                        </tr>
                        <tr>
                            <th>Low Stock Threshold:</th>
                            <td>${data.low_stock_threshold} units</td>
                        </tr>
                        <tr>
                            <th>Buying Price:</th>
                            <td>${Number(data.buying_price).toLocaleString()} RWF</td>
                        </tr>
                        <tr>
                            <th>Selling Price:</th>
                            <td>${Number(data.selling_price).toLocaleString()} RWF</td>
                        </tr>
                        <tr>
                            <th>Profit per Unit:</th>
                            <td class="text-success fw-bold">${profit.toLocaleString()} RWF</td>
                        </tr>
                        <tr>
                            <th>Profit Margin:</th>
                            <td class="text-success fw-bold">${profitMargin}%</td>
                        </tr>
                        <tr>
                            <th>Total Value (Cost):</th>
                            <td>${(data.quantity * data.buying_price).toLocaleString()} RWF</td>
                        </tr>
                        <tr>
                            <th>Total Value (Selling):</th>
                            <td>${(data.quantity * data.selling_price).toLocaleString()} RWF</td>
                        </tr>
                        <tr>
                            <th>Created:</th>
                            <td>${new Date(data.created_at).toLocaleString()}</td>
                        </tr>
                        <tr>
                            <th>Last Updated:</th>
                            <td>${new Date(data.updated_at).toLocaleString()}</td>
                        </tr>
                    </table>
                </div>
            `;
            
            $('#viewProductContent').html(html);
            
            // Open the modal
            var viewModal = new bootstrap.Modal(document.getElementById('viewProductModal'));
            viewModal.show();
        },
        error: function(xhr, status, error) {
            console.error('View error:', status, error);
            console.log('Response:', xhr.responseText);
            alert('Error loading product details. Check the console.');
        }
    });
}

// ==================== SIDEBAR FUNCTIONS (unchanged) ====================
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
    if (window.innerWidth <= 768) {
        if (!sidebar.contains(event.target) && !toggleBtn.contains(event.target)) {
            sidebar.classList.remove('mobile-visible');
        }
    }
});

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

// 3D Hover Effects (unchanged)
document.querySelectorAll('.stat-card, .products-card, .btn').forEach(element => {
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