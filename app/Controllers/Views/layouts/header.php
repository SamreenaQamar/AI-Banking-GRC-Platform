<!-- Page Header Start -->
<div class="page-header">
    <div class="page-header-left">
        <h1 class="page-title">
            <?php echo $page_title ?? 'Dashboard'; ?>
        </h1>
        
        <!-- Breadcrumbs -->
        <?php if (isset($breadcrumbs) && !empty($breadcrumbs)): ?>
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb">
                <?php foreach ($breadcrumbs as $key => $breadcrumb): ?>
                    <?php if ($key === array_key_last($breadcrumbs)): ?>
                        <li class="breadcrumb-item active" aria-current="page">
                            <?php echo $breadcrumb; ?>
                        </li>
                    <?php else: ?>
                        <li class="breadcrumb-item">
                            <a href="<?php echo $breadcrumb['url'] ?? '#'; ?>">
                                <?php echo $breadcrumb; ?>
                            </a>
                        </li>
                    <?php endif; ?>
                <?php endforeach; ?>
            </ol>
        </nav>
        <?php endif; ?>
    </div>
    
    <div class="page-header-right">
        <!-- Quick Actions -->
        <div class="quick-actions">
            <?php if (isset($quick_actions) && !empty($quick_actions)): ?>
                <?php foreach ($quick_actions as $action): ?>
                    <a href="<?php echo $action['url'] ?? '#'; ?>" 
                       class="btn <?php echo $action['class'] ?? 'btn-primary'; ?> btn-sm">
                        <i class="<?php echo $action['icon'] ?? ''; ?>"></i>
                        <?php echo $action['label'] ?? ''; ?>
                    </a>
                <?php endforeach; ?>
            <?php endif; ?>
            
            <!-- Export Button -->
            <button class="btn btn-outline-primary btn-sm" id="exportBtn">
                <i class="fas fa-download"></i> Export
            </button>
            
            <!-- Refresh Button -->
            <button class="btn btn-outline-secondary btn-sm" id="refreshBtn">
                <i class="fas fa-sync-alt"></i>
            </button>
        </div>
        
        <!-- Last Updated -->
        <div class="last-updated">
            <i class="far fa-clock"></i>
            <span>Last updated: <?php echo date('h:i A'); ?></span>
        </div>
    </div>
</div>
<!-- Page Header End -->