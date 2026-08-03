<?php
/**
 * Forgot Password Page
 */
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Forgot Password - AI Banking GRC Platform</title>
    
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    
    <style>
        :root {
            --primary: #0B3D91;
            --secondary: #2563EB;
        }
        
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body {
            font-family: 'Poppins', sans-serif;
            min-height: 100vh;
            background: linear-gradient(135deg, #0B3D91 0%, #2563EB 100%);
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 20px;
        }
        
        .auth-container {
            width: 100%;
            max-width: 420px;
        }
        
        .auth-card {
            background: #FFFFFF;
            border-radius: 20px;
            padding: 40px 36px;
            box-shadow: 0 20px 60px rgba(0,0,0,0.3);
        }
        
        .auth-brand {
            text-align: center;
            margin-bottom: 32px;
        }
        
        .auth-brand h2 {
            color: var(--primary);
            font-weight: 700;
            font-size: 22px;
            margin: 0;
        }
        
        .auth-brand p {
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
        
        .btn-submit {
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
        
        .btn-submit:hover {
            background: var(--primary);
            transform: translateY(-2px);
            box-shadow: 0 8px 24px rgba(37, 99, 235, 0.3);
        }
        
        .auth-footer {
            text-align: center;
            margin-top: 24px;
            padding-top: 20px;
            border-top: 1px solid #E2E8F0;
        }
        
        .auth-footer a {
            color: var(--secondary);
            text-decoration: none;
            font-size: 14px;
            font-weight: 500;
        }
        
        .auth-footer a:hover {
            color: var(--primary);
        }
        
        .alert {
            border-radius: 10px;
            font-size: 14px;
            padding: 12px 16px;
        }
    </style>
</head>
<body>
    <div class="auth-container">
        <div class="auth-card">
            <div class="auth-brand">
                <h2>Reset Password</h2>
                <p>Enter your email to receive reset instructions</p>
            </div>
            
            <?php if (isset($_SESSION['flash_messages'])): ?>
                <?php foreach ($_SESSION['flash_messages'] as $type => $messages): ?>
                    <?php foreach ($messages as $message): ?>
                        <div class="alert alert-<?php echo $type === 'error' ? 'danger' : 'success'; ?>">
                            <i class="fas fa-<?php echo $type === 'error' ? 'exclamation-circle' : 'check-circle'; ?> me-2"></i>
                            <?php echo htmlspecialchars($message); ?>
                        </div>
                    <?php endforeach; ?>
                <?php endforeach; ?>
                <?php unset($_SESSION['flash_messages']); ?>
            <?php endif; ?>
            
            <form method="POST" action="<?php echo BASE_URL; ?>/password/forgot">
                <input type="hidden" name="csrf_token" value="<?php echo $csrf_token ?? ''; ?>">
                
                <div class="form-group">
                    <label for="email">Email Address</label>
                    <div class="input-group">
                        <span class="input-group-text bg-transparent border-end-0">
                            <i class="fas fa-envelope text-muted"></i>
                        </span>
                        <input type="email" class="form-control border-start-0" id="email" 
                               name="email" placeholder="Enter your registered email" required autofocus>
                    </div>
                </div>
                
                <button type="submit" class="btn-submit">Send Reset Link</button>
            </form>
            
            <div class="auth-footer">
                <a href="<?php echo BASE_URL; ?>/login">Back to Login</a>
            </div>
        </div>
    </div>
</body>
</html>