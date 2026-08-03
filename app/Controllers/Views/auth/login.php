<?php
/**
 * Login Page
 * 
 * @var string $title
 * @var bool $has_error
 */
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $title ?? 'Login - AI Banking GRC Platform'; ?></title>
    
    <!-- Google Fonts - Poppins -->
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    
    <!-- Bootstrap 5 -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    
    <!-- Font Awesome 6 -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    
    <style>
        :root {
            --primary: #0B3D91;
            --secondary: #2563EB;
            --accent: #00B894;
            --bg-light: #F4F7FC;
        }
        
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body {
            font-family: 'Poppins', sans-serif;
            min-height: 100vh;
            background: linear-gradient(135deg, #0B3D91 0%, #1a5bbf 50%, #2563EB 100%);
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 20px;
        }
        
        .login-container {
            width: 100%;
            max-width: 420px;
        }
        
        .login-card {
            background: #FFFFFF;
            border-radius: 20px;
            padding: 40px 36px;
            box-shadow: 0 20px 60px rgba(0,0,0,0.3);
        }
        
        .login-brand {
            text-align: center;
            margin-bottom: 32px;
        }
        
        .login-brand img {
            height: 48px;
            margin-bottom: 12px;
        }
        
        .login-brand h2 {
            color: var(--primary);
            font-weight: 700;
            font-size: 22px;
            margin: 0;
        }
        
        .login-brand p {
            color: #64748B;
            font-size: 14px;
            margin: 4px 0 0;
        }
        
        .form-group {
            margin-bottom: 20px;
        }
        
        .form-group label {
            font-weight: 500;
            font-size: 14px;
            color: #1E293B;
            margin-bottom: 6px;
        }
        
        .form-control {
            border-radius: 10px;
            border: 1px solid #E2E8F0;
            padding: 12px 16px;
            font-size: 14px;
            transition: all 0.3s;
            font-family: 'Poppins', sans-serif;
        }
        
        .form-control:focus {
            border-color: var(--secondary);
            box-shadow: 0 0 0 3px rgba(37, 99, 235, 0.1);
        }
        
        .form-control-icon {
            position: relative;
        }
        
        .form-control-icon i {
            position: absolute;
            left: 14px;
            top: 50%;
            transform: translateY(-50%);
            color: #94A3B8;
        }
        
        .form-control-icon .form-control {
            padding-left: 40px;
        }
        
        .form-options {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 24px;
        }
        
        .form-options .form-check {
            margin: 0;
        }
        
        .form-options .form-check-label {
            font-size: 14px;
            color: #64748B;
        }
        
        .btn-login {
            width: 100%;
            padding: 12px;
            border: none;
            border-radius: 10px;
            background: var(--secondary);
            color: #fff;
            font-weight: 600;
            font-size: 16px;
            font-family: 'Poppins', sans-serif;
            transition: all 0.3s;
            cursor: pointer;
        }
        
        .btn-login:hover {
            background: var(--primary);
            transform: translateY(-2px);
            box-shadow: 0 8px 24px rgba(37, 99, 235, 0.3);
        }
        
        .btn-login:disabled {
            opacity: 0.7;
            cursor: not-allowed;
            transform: none;
        }
        
        .login-footer {
            text-align: center;
            margin-top: 24px;
            padding-top: 20px;
            border-top: 1px solid #E2E8F0;
        }
        
        .login-footer a {
            color: var(--secondary);
            text-decoration: none;
            font-size: 14px;
            font-weight: 500;
            transition: color 0.3s;
        }
        
        .login-footer a:hover {
            color: var(--primary);
        }
        
        .login-footer .register-link {
            display: block;
            margin-top: 8px;
            color: #64748B;
            font-weight: 400;
        }
        
        .alert {
            border-radius: 10px;
            font-size: 14px;
            padding: 12px 16px;
        }
        
        .alert-danger {
            background: #FEF2F2;
            border-color: #FECACA;
            color: #DC2626;
        }
        
        .alert-success {
            background: #F0FDF4;
            border-color: #BBF7D0;
            color: #16A34A;
        }
    </style>
</head>
<body>
    <div class="login-container">
        <div class="login-card">
            <!-- Brand -->
            <div class="login-brand">
                <img src="<?php echo ASSETS_URL; ?>/images/logo-primary.svg" alt="GRC Platform">
                <h2>AI Banking GRC</h2>
                <p>Governance, Risk &amp; Compliance Platform</p>
            </div>
            
            <!-- Flash Messages -->
            <?php if (isset($_SESSION['flash_messages'])): ?>
                <?php foreach ($_SESSION['flash_messages'] as $type => $messages): ?>
                    <?php foreach ($messages as $message): ?>
                        <div class="alert alert-<?php echo $type === 'error' ? 'danger' : 'success'; ?> alert-dismissible fade show">
                            <i class="fas fa-<?php echo $type === 'error' ? 'exclamation-circle' : 'check-circle'; ?> me-2"></i>
                            <?php echo htmlspecialchars($message); ?>
                            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                        </div>
                    <?php endforeach; ?>
                <?php endforeach; ?>
                <?php unset($_SESSION['flash_messages']); ?>
            <?php endif; ?>
            
            <!-- Login Form -->
            <form method="POST" action="<?php echo BASE_URL; ?>/login" id="loginForm">
                <input type="hidden" name="csrf_token" value="<?php echo $csrf_token ?? ''; ?>">
                
                <div class="form-group">
                    <label for="username">Username or Email</label>
                    <div class="form-control-icon">
                        <i class="fas fa-user"></i>
                        <input type="text" class="form-control" id="username" name="username" 
                               placeholder="Enter your username or email" required autofocus>
                    </div>
                </div>
                
                <div class="form-group">
                    <label for="password">Password</label>
                    <div class="form-control-icon">
                        <i class="fas fa-lock"></i>
                        <input type="password" class="form-control" id="password" name="password" 
                               placeholder="Enter your password" required>
                    </div>
                </div>
                
                <div class="form-options">
                    <div class="form-check">
                        <input class="form-check-input" type="checkbox" id="remember" name="remember">
                        <label class="form-check-label" for="remember">Remember me</label>
                    </div>
                    <a href="<?php echo BASE_URL; ?>/password/forgot">Forgot password?</a>
                </div>
                
                <button type="submit" class="btn-login" id="loginBtn">
                    <span id="loginText">Sign In</span>
                    <span id="loginSpinner" class="spinner-border spinner-border-sm d-none" role="status"></span>
                </button>
            </form>
            
            <div class="login-footer">
                <a href="<?php echo BASE_URL; ?>/register">Create an account</a>
                <span class="register-link">Secure banking GRC platform</span>
            </div>
        </div>
    </div>
    
    <!-- Scripts -->
    <script src="https://code.jquery.com/jquery-3.7.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    
    <script>
    $(document).ready(function() {
        // Login form submission
        $('#loginForm').on('submit', function() {
            const btn = $('#loginBtn');
            const text = $('#loginText');
            const spinner = $('#loginSpinner');
            
            btn.prop('disabled', true);
            text.addClass('d-none');
            spinner.removeClass('d-none');
        });
        
        // Enter key support
        $('#password').on('keypress', function(e) {
            if (e.key === 'Enter') {
                $('#loginForm').submit();
            }
        });
    });
    </script>
</body>
</html>