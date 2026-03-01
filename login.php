<?php
require_once 'includes/config.php';
if (isLoggedIn()) {
    header('Location: index.php');
    exit;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - <?php echo BUSINESS_NAME; ?></title>
    <!-- Favicon (PNG) -->
    <link rel="icon" type="image/png" href="assets/images/favicon-16x16.png">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        /* (all existing styles remain unchanged) */
        body {
            margin: 0;
            padding: 0;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            overflow-x: hidden;
        }
        .split-screen {
            display: flex;
            min-height: 100vh;
            flex-wrap: wrap;
        }
        .left-panel {
            flex: 1 1 50%;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 2rem;
            color: white;
            text-align: center;
            position: relative;
            overflow: hidden;
        }
        .left-panel::before {
            content: '';
            position: absolute;
            top: -50%;
            left: -50%;
            width: 200%;
            height: 200%;
            background: radial-gradient(circle, rgba(255,255,255,0.1) 0%, transparent 70%);
            animation: rotate 30s linear infinite;
        }
        @keyframes rotate {
            from { transform: rotate(0deg); }
            to { transform: rotate(360deg); }
        }
        .left-content {
            position: relative;
            z-index: 2;
            max-width: 500px;
        }
        .left-content img {
            max-width: 80%;
            height: 80%;
            margin-bottom: 2rem;
            border-radius: 10px;
            box-shadow: 0 20px 40px rgba(0,0,0,0.2);
        }
        .left-content h1 {
            font-size: 2.5rem;
            font-weight: 700;
            margin-bottom: 1rem;
            text-shadow: 2px 2px 4px rgba(0,0,0,0.2);
        }
        .left-content p {
            font-size: 1.1rem;
            opacity: 0.9;
            line-height: 1.6;
        }
        .feature-list {
            list-style: none;
            padding: 0;
            margin-top: 2rem;
            text-align: left;
        }
        .feature-list li {
            margin-bottom: 0.75rem;
            display: flex;
            align-items: center;
            gap: 10px;
        }
        .feature-list i {
            font-size: 1.2rem;
            color: #ffd700;
        }
        .right-panel {
            flex: 1 1 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 2rem;
            background: #f8fafc;
        }
        .login-card {
            background: #f17142;
            border-radius: 15px;
            box-shadow: 0 10px 40px rgba(0,0,0,0.1);
            max-width: 600px;
            width: 100%;
            padding: 2.5rem;
        }
        .login-header {
            text-align: center;
            margin-bottom: 2rem;
        }
        .login-header img {
            height: 190px;
            width: 190px;
            border-radius: 50%;
            object-fit: cover;
            margin-bottom: 1rem;
        }
        .login-header h2 {
            color: #333;
            font-weight: 600;
            font-size: 1.8rem;
        }
        .login-header p {
            color: #666;
            margin-bottom: 0;
        }
        .btn-login {
            background: linear-gradient(135deg, #667eea 0%, #11f73f 100%);
            border: none;
            color: white;
            padding: 12px;
            border-radius: 8px;
            width: 100%;
            font-weight: 600;
            transition: all 0.2s;
            margin-top: 1rem;
        }
        .btn-login:hover {
            transform: translateY(-2px);
            box-shadow: 0 5px 20px rgba(102, 126, 234, 0.4);
            color: white;
        }
        .form-control {
            border-radius: 8px;
            padding: 12px;
            border: 1px solid #ddd;
            margin-bottom: 1rem;
            transition: border-color 0.2s, box-shadow 0.2s;
        }
        .form-control:focus {
            border-color: #667eea;
            box-shadow: 0 0 0 0.2rem rgba(102, 126, 234, 0.25);
        }
        .input-group-text {
            background: transparent;
            border-right: none;
        }
        .input-group .form-control {
            border-left: none;
        }
        .input-group .form-control:focus {
            border-color: #667eea;
            box-shadow: none;
        }
        .input-group:focus-within {
            box-shadow: 0 0 0 0.2rem rgba(102, 126, 234, 0.25);
            border-radius: 8px;
        }
        .alert {
            border-radius: 8px;
            margin-bottom: 1.5rem;
        }
        @media (max-width: 768px) {
            .split-screen {
                flex-direction: column;
            }
            .left-panel {
                padding: 3rem 1.5rem;
            }
            .left-content h1 {
                font-size: 2rem;
            }
            .right-panel {
                padding: 2rem 1rem;
            }
            .login-card {
                padding: 1.5rem;
            }
        }
    </style>
</head>
<body>
    <div class="split-screen">
        <!-- Left Panel with Illustration and Business Info -->
        <div class="left-panel">
            <div class="left-content">
                <!-- Main Illustration (replace with your actual image) -->
                <img src="assets/images/inventory-illustration.png" 
                     alt="Inventory Management" 
                     onerror="this.style.display='none'; this.nextElementSibling.style.display='block';">
                <div style="display: none;" class="fallback-icon">
                    <i class="fas fa-warehouse fa-6x mb-4"></i>
                </div>
                
                <h1>Manage Your Business Efficiently</h1>
                <p>MUVU FX Inventory System helps you track products, sales, and profits in real time.</p>
                
                <ul class="feature-list">
                    <li><i class="fas fa-check-circle"></i> Real-time inventory tracking</li>
                    <li><i class="fas fa-check-circle"></i> Sales and profit analytics</li>
                    <li><i class="fas fa-check-circle"></i> Low stock alerts & restock management</li>
                    <li><i class="fas fa-check-circle"></i> Detailed reports & insights</li>
                </ul>
                
                <div class="mt-4">
                    <p class="mb-1"><i class="fas fa-phone-alt me-2"></i> 0786874837</p>
                    <p><i class="fas fa-map-marker-alt me-2"></i> Gatsibo District, Malimba Cell</p>
                </div>
            </div>
        </div>

        <!-- Right Panel with Login Form -->
        <div class="right-panel">
            <div class="login-card">
                <div class="login-header">
                    <!-- Business Logo (optional) - now circular -->
                    <img src="assets/images/logo.png" alt="MUVU FX" onerror="this.style.display='none';">
                    <h2>Welcome Back</h2>
                    <p>Sign in to <?php echo BUSINESS_NAME; ?> Dashboard</p>
                </div>

                <?php if (isset($_GET['error'])): ?>
                    <div class="alert alert-danger">
                        <i class="fas fa-exclamation-circle me-2"></i>
                        <?php echo htmlspecialchars($_GET['error']); ?>
                    </div>
                <?php endif; ?>
                <?php if (isset($_GET['success']) && $_GET['success'] == 'registered'): ?>
                    <div class="alert alert-success">
                        <i class="fas fa-check-circle me-2"></i>
                        Registration successful! You can now log in.
                    </div>
                <?php endif; ?>

                <form action="actions/login.php" method="POST">
                    <div class="mb-3">
                        <label for="username" class="form-label">Username</label>
                        <div class="input-group">
                            <span class="input-group-text"><i class="fas fa-user"></i></span>
                            <input type="text" class="form-control" id="username" name="username" required autofocus>
                        </div>
                    </div>
                    <div class="mb-3">
                        <label for="password" class="form-label">Password</label>
                        <div class="input-group">
                            <span class="input-group-text"><i class="fas fa-lock"></i></span>
                            <input type="password" class="form-control" id="password" name="password" required>
                        </div>
                    </div>
                    <button type="submit" class="btn btn-login">
                        <i class="fas fa-sign-in-alt me-2"></i>Sign In
                    </button>
                </form>

                <div class="text-center mt-4">
                    <small class="text-muted">&copy; <?php echo date('Y'); ?> MUVU FX. All rights reserved.</small>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Fallback if images fail to load: show Font Awesome icons
        document.querySelectorAll('img').forEach(img => {
            img.addEventListener('error', function() {
                this.style.display = 'none';
                const fallback = this.nextElementSibling;
                if (fallback && fallback.classList.contains('fallback-icon')) {
                    fallback.style.display = 'block';
                }
            });
        });
    </script>
</body>
</html>