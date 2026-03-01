<?php
require_once '../includes/config.php';
requireAdmin();

// Get current page name for active menu
$current_page = basename($_SERVER['PHP_SELF']);

// User info for sidebar
$user_role = $_SESSION['role'] ?? 'guest';
$user_name = $_SESSION['full_name'] ?? 'User';

// Get all users
$users_query = "SELECT * FROM users ORDER BY created_at ASC";
$users_result = mysqli_query($conn, $users_query);

// Get user statistics
$stats_query = "SELECT 
                  COUNT(*) as total_users,
                  SUM(CASE WHEN role = 'admin' THEN 1 ELSE 0 END) as admin_count,
                  SUM(CASE WHEN role = 'staff' THEN 1 ELSE 0 END) as staff_count,
                  COUNT(DISTINCT DATE(created_at)) as active_days
                FROM users";
$stats_result = mysqli_query($conn, $stats_query);
$stats = mysqli_fetch_assoc($stats_result);
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
                <h5 class="page-title">
                    <i class="fas fa-users-cog me-2 text-primary"></i>
                    User Management
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
                            <i class="fas fa-users-cog text-primary me-2"></i>
                            User Management
                        </h2>
                        <p class="text-white-50">
                            <i class="fas fa-store me-2"></i>
                            Manage system users, roles, and permissions
                        </p>
                    </div>
                    <div class="col-md-4 text-end">
                        <div class="business-badge">
                            <span class="badge bg-white text-primary p-3">
                                <i class="fas fa-phone-alt me-2"></i><?php echo BUSINESS_PHONE; ?>
                            </span>
                        </div>
                    </div>
                </div>
            </div>

            <!-- User Statistics -->
            <div class="row g-4 mb-4">
                <div class="col-md-3">
                    <div class="stat-card">
                        <div class="stat-icon">
                            <i class="fas fa-users"></i>
                        </div>
                        <h6 class="text-muted">Total Users</h6>
                        <h3 class="mb-2"><?php echo $stats['total_users']; ?></h3>
                        <small class="text-primary">Registered users</small>
                    </div>
                </div>
                
                <div class="col-md-3">
                    <div class="stat-card">
                        <div class="stat-icon">
                            <i class="fas fa-user-shield"></i>
                        </div>
                        <h6 class="text-muted">Administrators</h6>
                        <h3 class="mb-2"><?php echo $stats['admin_count']; ?></h3>
                        <small class="text-danger">Admin role</small>
                    </div>
                </div>
                
                <div class="col-md-3">
                    <div class="stat-card">
                        <div class="stat-icon">
                            <i class="fas fa-user-tie"></i>
                        </div>
                        <h6 class="text-muted">Staff Members</h6>
                        <h3 class="mb-2"><?php echo $stats['staff_count']; ?></h3>
                        <small class="text-info">Staff role</small>
                    </div>
                </div>
                
                <div class="col-md-3">
                    <div class="stat-card">
                        <div class="stat-icon">
                            <i class="fas fa-calendar-check"></i>
                        </div>
                        <h6 class="text-muted">Active Days</h6>
                        <h3 class="mb-2"><?php echo $stats['active_days']; ?></h3>
                        <small class="text-success">Registration days</small>
                    </div>
                </div>
            </div>
            
            <!-- Users Table -->
            <div class="row">
                <div class="col-12">
                    <div class="card">
                        <div class="card-header bg-primary text-white d-flex justify-content-between align-items-center">
                            <h5 class="mb-0"><i class="fas fa-user-cog me-2"></i>User Management</h5>
                            <button class="btn btn-light btn-sm" data-bs-toggle="modal" data-bs-target="#addUserModal">
                                <i class="fas fa-plus me-1"></i>Add New User
                            </button>
                        </div>
                        <div class="card-body">
                            <div class="table-responsive">
                                <table class="table table-hover">
                                    <thead>
                                        <tr>
                                            <th>#</th>
                                            <th>Full Name</th>
                                            <th>Username</th>
                                            <th>Email</th>
                                            <th>Phone</th>
                                            <th>Role</th>
                                            <th>Joined Date</th>
                                            <th>Last Active</th>
                                            <th>Actions</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php while ($user = mysqli_fetch_assoc($users_result)): 
                                            // Get last activity
                                            $last_activity_query = "SELECT created_at FROM activity_log WHERE user_id = " . $user['id'] . " ORDER BY created_at DESC LIMIT 1";
                                            $last_activity_result = mysqli_query($conn, $last_activity_query);
                                            $last_activity = mysqli_fetch_assoc($last_activity_result);
                                        ?>
                                        <tr>
                                            <td><?php echo $user['id']; ?></td>
                                            <td><?php echo htmlspecialchars($user['full_name']); ?></td>
                                            <td><?php echo htmlspecialchars($user['username']); ?></td>
                                            <td><?php echo htmlspecialchars($user['email']); ?></td>
                                            <td><?php echo htmlspecialchars($user['phone'] ?: '-'); ?></td>
                                            <td>
                                                <span class="badge bg-<?php echo $user['role'] == 'admin' ? 'danger' : 'info'; ?>">
                                                    <?php echo ucfirst($user['role']); ?>
                                                </span>
                                            </td>
                                            <td><?php echo date('Y-m-d', strtotime($user['created_at'])); ?></td>
                                            <td>
                                                <?php if ($last_activity): ?>
                                                    <?php echo date('Y-m-d H:i', strtotime($last_activity['created_at'])); ?>
                                                <?php else: ?>
                                                    Never
                                                <?php endif; ?>
                                            </td>
                                            <td>
                                                <button class="btn btn-sm btn-warning" onclick="editUser(<?php echo $user['id']; ?>)">
                                                    <i class="fas fa-edit"></i>
                                                </button>
                                                <?php if ($user['id'] != $_SESSION['user_id']): ?>
                                                <button class="btn btn-sm btn-danger" onclick="deleteUser(<?php echo $user['id']; ?>)">
                                                    <i class="fas fa-trash"></i>
                                                </button>
                                                <?php endif; ?>
                                                <button class="btn btn-sm btn-info" onclick="viewUserActivity(<?php echo $user['id']; ?>)">
                                                    <i class="fas fa-history"></i>
                                                </button>
                                            </td>
                                        </tr>
                                        <?php endwhile; ?>
                                    </tbody>
                                </table>
                            </div>
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

<!-- Add User Modal -->
<div class="modal fade" id="addUserModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header bg-success text-white">
                <h5 class="modal-title">Add New User</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <form id="addUserForm" action="../actions/register.php" method="POST">
                    <div class="mb-3">
                        <label for="full_name" class="form-label">Full Name</label>
                        <input type="text" class="form-control" id="full_name" name="full_name" required>
                    </div>
                    
                    <div class="mb-3">
                        <label for="username" class="form-label">Username</label>
                        <input type="text" class="form-control" id="username" name="username" required>
                    </div>
                    
                    <div class="mb-3">
                        <label for="email" class="form-label">Email</label>
                        <input type="email" class="form-control" id="email" name="email" required>
                    </div>
                    
                    <div class="mb-3">
                        <label for="phone" class="form-label">Phone Number</label>
                        <input type="tel" class="form-control" id="phone" name="phone">
                    </div>
                    
                    <div class="mb-3">
                        <label for="password" class="form-label">Password</label>
                        <input type="password" class="form-control" id="password" name="password" required>
                    </div>
                    
                    <div class="mb-3">
                        <label for="confirm_password" class="form-label">Confirm Password</label>
                        <input type="password" class="form-control" id="confirm_password" name="confirm_password" required>
                    </div>
                    
                    <div class="mb-3">
                        <label for="role" class="form-label">User Role</label>
                        <select class="form-select" id="role" name="role" required>
                            <option value="staff">Staff</option>
                            <option value="admin">Admin</option>
                        </select>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="submit" form="addUserForm" class="btn btn-success">Add User</button>
            </div>
        </div>
    </div>
</div>

<!-- Edit User Modal -->
<div class="modal fade" id="editUserModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header bg-warning text-white">
                <h5 class="modal-title">Edit User</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <form id="editUserForm">
                    <input type="hidden" id="edit_user_id" name="user_id">
                    
                    <div class="mb-3">
                        <label for="edit_full_name" class="form-label">Full Name</label>
                        <input type="text" class="form-control" id="edit_full_name" name="full_name" required>
                    </div>
                    
                    <div class="mb-3">
                        <label for="edit_username" class="form-label">Username</label>
                        <input type="text" class="form-control" id="edit_username" name="username" readonly>
                    </div>
                    
                    <div class="mb-3">
                        <label for="edit_email" class="form-label">Email</label>
                        <input type="email" class="form-control" id="edit_email" name="email" required>
                    </div>
                    
                    <div class="mb-3">
                        <label for="edit_phone" class="form-label">Phone Number</label>
                        <input type="tel" class="form-control" id="edit_phone" name="phone">
                    </div>
                    
                    <div class="mb-3">
                        <label for="edit_role" class="form-label">User Role</label>
                        <select class="form-select" id="edit_role" name="role">
                            <option value="staff">Staff</option>
                            <option value="admin">Admin</option>
                        </select>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-warning" onclick="updateUser()">Update User</button>
            </div>
        </div>
    </div>
</div>

<!-- User Activity Modal -->
<div class="modal fade" id="userActivityModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header bg-info text-white">
                <h5 class="modal-title">User Activity Log</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body" id="userActivityContent">
                <!-- Content will be loaded via AJAX -->
            </div>
        </div>
    </div>
</div>

<!-- Include layout CSS (already in header, but we keep it for completeness) -->
<style>
/* ========== MAIN LAYOUT ========== */
.main-layout {
    display: flex;
    min-height: 100vh;
    background: #f4f7fc;
}
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
    transition: all 0.3s ease;
    box-shadow: 2px 0 20px rgba(0, 0, 0, 0.1);
}
/* ... (full layout CSS as before, omitted for brevity - it's the same) ... */
/* We'll keep the full CSS from previous answer */
</style>

<script>
// Ensure jQuery is loaded (fallback if not)
if (typeof jQuery === 'undefined') {
    console.error('jQuery is not loaded! Loading dynamically...');
    var script = document.createElement('script');
    script.src = 'https://code.jquery.com/jquery-3.6.0.min.js';
    script.onload = function() {
        console.log('jQuery loaded successfully.');
    };
    document.head.appendChild(script);
}

// Toggle sidebar (same as products)
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
    if (window.innerWidth <= 768 && !sidebar.contains(event.target) && !toggleBtn.contains(event.target)) {
        sidebar.classList.remove('mobile-visible');
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
    document.querySelectorAll('.sidebar .nav-link').forEach(link => {
        if (link.getAttribute('href').includes(currentPage)) {
            link.classList.add('active');
        }
    });
});

// ========== USER MANAGEMENT FUNCTIONS ==========

// Edit user: load data into modal
function editUser(id) {
    console.log('editUser called with ID:', id);
    $.ajax({
        url: '../actions/get_user.php',
        type: 'POST',
        data: { user_id: id },
        dataType: 'json',
        success: function(data) {
            console.log('User data received:', data);
            $('#edit_user_id').val(data.id);
            $('#edit_full_name').val(data.full_name);
            $('#edit_username').val(data.username);
            $('#edit_email').val(data.email);
            $('#edit_phone').val(data.phone);
            $('#edit_role').val(data.role);
            $('#editUserModal').modal('show');
        },
        error: function(xhr, status, error) {
            console.error('AJAX error:', status, error);
            console.log('Response:', xhr.responseText);
            alert('Error loading user data. Please check the console for details.');
        }
    });
}

// Update user: send edited data
function updateUser() {
    console.log('updateUser called');
    var formData = $('#editUserForm').serialize();
    console.log('Form data:', formData);
    
    $.ajax({
        url: '../actions/edit_user.php',
        type: 'POST',
        data: formData,
        success: function(response) {
            console.log('Update response:', response);
            $('#editUserModal').modal('hide');
            location.reload(); // Refresh to show updated info
        },
        error: function(xhr, status, error) {
            console.error('Update error:', status, error);
            console.log('Response:', xhr.responseText);
            alert('Error updating user. Please check the console.');
        }
    });
}

// Delete user: confirm and delete
function deleteUser(id) {
    console.log('deleteUser called with ID:', id);
    if (confirm('Are you sure you want to delete this user? This action cannot be undone.')) {
        $.ajax({
            url: '../actions/delete_user.php',
            type: 'POST',
            data: { user_id: id },
            success: function(response) {
                console.log('Delete response:', response);
                location.reload();
            },
            error: function(xhr, status, error) {
                console.error('Delete error:', status, error);
                console.log('Response:', xhr.responseText);
                alert('Error deleting user. Please check the console.');
            }
        });
    }
}

// View user activity: load activity log into modal
function viewUserActivity(id) {
    console.log('viewUserActivity called with ID:', id);
    $('#userActivityContent').html('<div class="text-center"><i class="fas fa-spinner fa-spin fa-3x"></i><p>Loading activity...</p></div>');
    $('#userActivityModal').modal('show');
    
    $.ajax({
        url: '../actions/get_user_activity.php',
        type: 'POST',
        data: { user_id: id },
        success: function(response) {
            console.log('Activity response received');
            $('#userActivityContent').html(response);
        },
        error: function(xhr, status, error) {
            console.error('Activity error:', status, error);
            console.log('Response:', xhr.responseText);
            $('#userActivityContent').html('<div class="alert alert-danger">Error loading activity. Please try again.</div>');
        }
    });
}
</script>

<!-- Include the full layout CSS (if not already in header) -->
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

.welcome-section .text-white-50 {
    color: rgba(255,255,255,0.8) !important;
}

.business-badge .badge {
    background: rgba(255, 255, 255, 0.2);
    backdrop-filter: blur(10px);
    font-size: 16px;
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

/* Stat Cards (original) */
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

/* Animations */
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

<?php include '../includes/footer.php'; ?>