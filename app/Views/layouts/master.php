<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="<?php echo $description ?? 'AI Banking GRC Platform - Governance, Risk & Compliance Management for Pakistani Banks'; ?>">
    <meta name="keywords" content="GRC, Banking, Compliance, Risk Management, Audit, Pakistan, SBP">
    <meta name="author" content="<?php echo COMPANY_NAME; ?>">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    
    <!-- CSRF Token -->
    <meta name="csrf-token" content="<?php echo $csrf_token ?? ''; ?>">
    
    <title><?php echo $title ?? 'AI Banking GRC Platform'; ?></title>
    
    <!-- Favicon -->
    <link rel="icon" type="image/x-icon" href="<?php echo ASSETS_URL; ?>/favicon.ico">
    
    <!-- Google Fonts - Poppins -->
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    
    <!-- Bootstrap 5 -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    
    <!-- Font Awesome 6 -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    
    <!-- Chart.js -->
    <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>
    
    <!-- Custom Styles -->
    <link rel="stylesheet" href="<?php echo ASSETS_URL; ?>/css/style.css">
    
    <?php if (isset($additional_styles)): ?>
        <?php echo $additional_styles; ?>
    <?php endif; ?>
</head>
<body>
    <div class="app-wrapper">
        <!-- Top Bar -->
        <?php require_once VIEW_PATH . '/layouts/topbar.php'; ?>
        
        <div class="app-main">
            <!-- Sidebar -->
            <?php require_once VIEW_PATH . '/layouts/sidebar.php'; ?>
            
            <!-- Main Content -->
            <div class="app-content">
                <!-- Header -->
                <?php require_once VIEW_PATH . '/layouts/header.php'; ?>
                
                <!-- Page Content -->
                <div class="page-content">
                    <?php echo $content ?? ''; ?>
                </div>
                
                <!-- Footer -->
                <?php require_once VIEW_PATH . '/layouts/footer.php'; ?>
            </div>
        </div>
    </div>
    
    <!-- Scripts -->
    <?php require_once VIEW_PATH . '/layouts/scripts.php'; ?>
    
    <?php if (isset($additional_scripts)): ?>
        <?php echo $additional_scripts; ?>
    <?php endif; ?>
</body>
</html>