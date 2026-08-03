<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>404 - Page Not Found</title>
    
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    
    <style>
        body {
            font-family: 'Poppins', sans-serif;
            min-height: 100vh;
            background: #F4F7FC;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 20px;
        }
        
        .error-container {
            text-align: center;
            max-width: 500px;
        }
        
        .error-code {
            font-size: 120px;
            font-weight: 700;
            color: #0B3D91;
            line-height: 1;
            margin-bottom: 10px;
        }
        
        .error-icon {
            font-size: 60px;
            color: #2563EB;
            margin-bottom: 20px;
        }
        
        .error-title {
            font-size: 28px;
            font-weight: 600;
            color: #1E293B;
            margin-bottom: 12px;
        }
        
        .error-message {
            color: #64748B;
            font-size: 16px;
            margin-bottom: 30px;
        }
        
        .btn-home {
            padding: 12px 32px;
            border: none;
            border-radius: 10px;
            background: #2563EB;
            color: #fff;
            font-weight: 500;
            font-family: 'Poppins', sans-serif;
            text-decoration: none;
            transition: all 0.3s;
            display: inline-block;
        }
        
        .btn-home:hover {
            background: #0B3D91;
            transform: translateY(-2px);
            box-shadow: 0 8px 24px rgba(37, 99, 235, 0.3);
            color: #fff;
        }
    </style>
</head>
<body>
    <div class="error-container">
        <div class="error-code">404</div>
        <div class="error-icon">
            <i class="fas fa-map-signs"></i>
        </div>
        <h1 class="error-title">Page Not Found</h1>
        <p class="error-message">
            The page you are looking for might have been removed, had its name changed, or is temporarily unavailable.
        </p>
        <a href="<?php echo BASE_URL; ?>/dashboard" class="btn-home">
            <i class="fas fa-home me-2"></i> Back to Dashboard
        </a>
    </div>
</body>
</html>