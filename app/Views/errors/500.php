<?php
/**
 * 500 Internal Server Error Page
 * 
 * @var string $title
 * @var string $message
 * @var string $error_id
 */
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>500 - Server Error</title>
    
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
        
        .error-container {
            text-align: center;
            max-width: 500px;
            width: 100%;
        }
        
        .error-card {
            background: #FFFFFF;
            border-radius: 20px;
            padding: 50px 40px;
            box-shadow: 0 20px 60px rgba(0,0,0,0.3);
        }
        
        .error-icon {
            font-size: 72px;
            color: #EF4444;
            margin-bottom: 20px;
        }
        
        .error-icon .fa-exclamation-triangle {
            background: #FEE2E2;
            padding: 20px;
            border-radius: 50%;
        }
        
        .error-code {
            font-size: 72px;
            font-weight: 700;
            color: var(--primary);
            line-height: 1;
            margin-bottom: 8px;
        }
        
        .error-title {
            font-size: 24px;
            font-weight: 600;
            color: #1E293B;
            margin-bottom: 12px;
        }
        
        .error-message {
            color: #64748B;
            font-size: 16px;
            line-height: 1.6;
            margin-bottom: 30px;
        }
        
        .error-id {
            font-size: 12px;
            color: #94A3B8;
            font-family: 'Courier New', monospace;
            margin-bottom: 20px;
        }
        
        .error-actions {
            display: flex;
            gap: 12px;
            justify-content: center;
            flex-wrap: wrap;
        }
        
        .btn-primary-custom {
            padding: 12px 32px;
            border: none;
            border-radius: 10px;
            background: var(--secondary);
            color: #fff;
            font-weight: 500;
            font-family: 'Poppins', sans-serif;
            text-decoration: none;
            transition: all 0.3s;
            display: inline-flex;
            align-items: center;
            gap: 8px;
        }
        
        .btn-primary-custom:hover {
            background: var(--primary);
            transform: translateY(-2px);
            box-shadow: 0 8px 24px rgba(37, 99, 235, 0.3);
            color: #fff;
        }
        
        .btn-secondary-custom {
            padding: 12px 32px;
            border: 2px solid #E2E8F0;
            border-radius: 10px;
            background: transparent;
            color: #64748B;
            font-weight: 500;
            font-family: 'Poppins', sans-serif;
            text-decoration: none;
            transition: all 0.3s;
            display: inline-flex;
            align-items: center;
            gap: 8px;
        }
        
        .btn-secondary-custom:hover {
            border-color: var(--secondary);
            color: var(--secondary);
        }
        
        .error-footer {
            margin-top: 20px;
            color: rgba(255,255,255,0.6);
            font-size: 14px;
        }
        
        .error-footer a {
            color: rgba(255,255,255,0.8);
            text-decoration: none;
        }
        
        .error-footer a:hover {
            color: #fff;
        }
        
        .error-support {
            margin-top: 16px;
            padding-top: 16px;
            border-top: 1px solid #F1F5F9;
            font-size: 14px;
            color: #94A3B8;
        }
        
        .error-support a {
            color: var(--secondary);
            text-decoration: none;
        }
        
        .error-support a:hover {
            text-decoration: underline;
        }
        
        .auto-refresh {
            margin-top: 12px;
            font-size: 13px;
            color: #94A3B8;
        }
        
        .auto-refresh .countdown {
            font-weight: 600;
            color: var(--secondary);
        }
        
        @media (max-width: 576px) {
            .error-card {
                padding: 30px 20px;
            }
            
            .error-code {
                font-size: 48px;
            }
            
            .error-icon {
                font-size: 48px;
            }
            
            .error-title {
                font-size: 20px;
            }
            
            .error-actions {
                flex-direction: column;
            }
            
            .btn-primary-custom,
            .btn-secondary-custom {
                width: 100%;
                justify-content: center;
            }
        }
    </style>
</head>
<body>
    <div class="error-container">
        <div class="error-card">
            <div class="error-icon">
                <i class="fas fa-exclamation-triangle"></i>
            </div>
            <div class="error-code">500</div>
            <h1 class="error-title">Something Went Wrong</h1>
            <p class="error-message">
                <?php echo $message ?? 'We\'re experiencing some technical difficulties. Our team has been notified and is working to resolve the issue.'; ?>
            </p>
            
            <?php if (isset($error_id)): ?>
                <div class="error-id">
                    <i class="fas fa-hashtag me-1"></i> Error ID: <?php echo htmlspecialchars($error_id); ?>
                </div>
            <?php endif; ?>
            
            <div class="error-actions">
                <a href="javascript:location.reload()" class="btn-primary-custom">
                    <i class="fas fa-sync-alt"></i> Try Again
                </a>
                <a href="<?php echo BASE_URL; ?>/dashboard" class="btn-secondary-custom">
                    <i class="fas fa-home"></i> Dashboard
                </a>
            </div>
            
            <div class="auto-refresh">
                <i class="fas fa-clock me-1"></i>
                Auto-refreshing in <span class="countdown" id="countdown">30</span> seconds
            </div>
            
            <div class="error-support">
                If the problem persists, <a href="<?php echo BASE_URL; ?>/support">contact our support team</a>
            </div>
        </div>
        <div class="error-footer">
            <a href="<?php echo BASE_URL; ?>/">Home</a> &bull;
            <a href="<?php echo BASE_URL; ?>/status">System Status</a>
        </div>
    </div>
    
    <script>
        // Auto-refresh countdown
        let seconds = 30;
        const countdownEl = document.getElementById('countdown');
        
        const interval = setInterval(function() {
            seconds--;
            countdownEl.textContent = seconds;
            
            if (seconds <= 0) {
                clearInterval(interval);
                location.reload();
            }
        }, 1000);
        
        // Manual refresh with loading state
        document.querySelector('.btn-primary-custom').addEventListener('click', function(e) {
            e.preventDefault();
            const btn = this;
            const originalText = btn.innerHTML;
            btn.innerHTML = '<i class="fas fa-spinner fa-spin me-2"></i> Refreshing...';
            btn.disabled = true;
            
            setTimeout(function() {
                location.reload();
            }, 1000);
        });
    </script>
</body>
</html>