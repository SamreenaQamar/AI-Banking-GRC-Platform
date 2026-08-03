<?php
/**
 * Compliance Checklist Page
 * 
 * @var string $title
 * @var array $checklist_items
 * @var array $progress
 */
?>

<?php $page_title = 'Compliance Checklist'; ?>
<?php $active_page = 'compliance'; ?>

<div class="checklist-container">
    <!-- Progress Overview -->
    <div class="row g-4 mb-4">
        <div class="col-md-8">
            <div class="progress-overview">
                <h5><i class="fas fa-clipboard-check me-2 text-primary"></i> Checklist Progress</h5>
                <div class="progress" style="height: 12px;">
                    <div class="progress-bar" style="width: <?php echo $progress['completed'] ?? 0; ?>%; 
                         background: linear-gradient(90deg, #2563EB, #22C55E);">
                        <?php echo $progress['completed'] ?? 0; ?>%
                    </div>
                </div>
                <div class="progress-stats">
                    <span><i class="fas fa-check-circle text-success"></i> Completed: <?php echo $progress['completed_items'] ?? 0; ?></span>
                    <span><i class="fas fa-circle text-warning"></i> In Progress: <?php echo $progress['in_progress'] ?? 0; ?></span>
                    <span><i class="fas fa-circle text-danger"></i> Pending: <?php echo $progress['pending'] ?? 0; ?></span>
                    <span><i class="fas fa-flag text-primary"></i> Total: <?php echo $progress['total'] ?? 0; ?></span>
                </div>
            </div>
        </div>
        <div class="col-md-4 text-end">
            <button class="btn btn-primary" id="exportChecklist">
                <i class="fas fa-download me-2"></i> Export Checklist
            </button>
            <button class="btn btn-outline-secondary" id="resetChecklist">
                <i class="fas fa-undo me-2"></i> Reset
            </button>
        </div>
    </div>
    
    <!-- Checklist Items -->
    <div class="card">
        <div class="card-header d-flex justify-content-between align-items-center">
            <span><i class="fas fa-list-check me-2"></i> Compliance Controls</span>
            <div class="filter-group">
                <select class="form-select form-select-sm" id="filterStatus" style="width: auto; display: inline-block;">
                    <option value="all">All Status</option>
                    <option value="completed">Completed</option>
                    <option value="in_progress">In Progress</option>
                    <option value="pending">Pending</option>
                </select>
                <select class="form-select form-select-sm" id="filterCategory" style="width: auto; display: inline-block;">
                    <option value="all">All Categories</option>
                    <?php foreach ($categories ?? [] as $category): ?>
                        <option value="<?php echo $category; ?>"><?php echo ucfirst($category); ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
        </div>
        <div class="card-body p-0">
            <div class="checklist-items">
                <?php if (!empty($checklist_items)): ?>
                    <?php foreach ($checklist_items as $item): ?>
                        <div class="checklist-item <?php echo $item->status; ?>" data-category="<?php echo $item->category; ?>">
                            <div class="checklist-checkbox-wrapper">
                                <input type="checkbox" class="checklist-checkbox" 
                                       <?php echo $item->status === 'completed' ? 'checked' : ''; ?>
                                       data-id="<?php echo $item->id; ?>">
                            </div>
                            <div class="checklist-content">
                                <div class="checklist-header">
                                    <span class="checklist-id">#<?php echo str_pad($item->id, 4, '0', STR_PAD_LEFT); ?></span>
                                    <span class="checklist-category badge bg-secondary">
                                        <?php echo ucfirst($item->category); ?>
                                    </span>
                                    <?php if ($item->priority): ?>
                                        <span class="badge bg-<?php echo $item->priority === 'high' ? 'danger' : ($item->priority === 'medium' ? 'warning' : 'info'); ?>">
                                            <?php echo ucfirst($item->priority); ?>
                                        </span>
                                    <?php endif; ?>
                                </div>
                                <div class="checklist-text"><?php echo htmlspecialchars($item->description); ?></div>
                                <?php if ($item->notes): ?>
                                    <div class="checklist-notes">
                                        <small class="text-muted"><i class="fas fa-sticky-note me-1"></i> <?php echo htmlspecialchars($item->notes); ?></small>
                                    </div>
                                <?php endif; ?>
                                <div class="checklist-meta">
                                    <?php if ($item->assigned_to): ?>
                                        <span><i class="fas fa-user me-1"></i> <?php echo htmlspecialchars($item->assigned_to); ?></span>
                                    <?php endif; ?>
                                    <?php if ($item->due_date): ?>
                                        <span><i class="far fa-calendar-alt me-1"></i> Due: <?php echo date('d M Y', strtotime($item->due_date)); ?></span>
                                    <?php endif; ?>
                                    <?php if ($item->status === 'completed' && $item->completed_at): ?>
                                        <span class="text-success"><i class="fas fa-check-circle me-1"></i> Completed: <?php echo date('d M Y', strtotime($item->completed_at)); ?></span>
                                    <?php endif; ?>
                                </div>
                            </div>
                            <div class="checklist-actions">
                                <button class="btn btn-sm btn-outline-primary edit-item" data-id="<?php echo $item->id; ?>">
                                    <i class="fas fa-edit"></i>
                                </button>
                                <button class="btn btn-sm btn-outline-secondary add-note" data-id="<?php echo $item->id; ?>">
                                    <i class="fas fa-sticky-note"></i>
                                </button>
                            </div>
                        </div>
                    <?php endforeach; ?>
                <?php else: ?>
                    <div class="text-center text-muted py-5">
                        <i class="fas fa-clipboard-list fa-3x mb-3"></i>
                        <h5>No Checklist Items</h5>
                        <p>No compliance checklist items found.</p>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<style>
.checklist-container {
    padding: 0;
}

.progress-overview {
    background: #fff;
    padding: 20px;
    border-radius: 12px;
    box-shadow: 0 1px 3px rgba(0,0,0,0.06);
}

.progress-overview h5 {
    margin-bottom: 12px;
}

.progress-stats {
    display: flex;
    gap: 20px;
    margin-top: 12px;
    flex-wrap: wrap;
}

.progress-stats span {
    font-size: 14px;
    color: #64748B;
}

.progress-stats i {
    margin-right: 4px;
}

.checklist-items {
    padding: 8px 0;
}

.checklist-item {
    display: flex;
    align-items: flex-start;
    padding: 16px 20px;
    border-bottom: 1px solid #F1F5F9;
    transition: background 0.2s;
}

.checklist-item:hover {
    background: #F8FAFC;
}

.checklist-item.completed {
    background: #F0FDF4;
}

.checklist-item.completed .checklist-text {
    text-decoration: line-through;
    color: #94A3B8;
}

.checklist-checkbox-wrapper {
    margin-right: 16px;
    padding-top: 4px;
}

.checklist-checkbox {
    width: 20px;
    height: 20px;
    cursor: pointer;
    accent-color: #2563EB;
}

.checklist-content {
    flex: 1;
}

.checklist-header {
    display: flex;
    align-items: center;
    gap: 8px;
    margin-bottom: 4px;
    flex-wrap: wrap;
}

.checklist-id {
    font-family: 'Courier New', monospace;
    font-size: 13px;
    color: #2563EB;
    font-weight: 600;
}

.checklist-text {
    font-size: 14px;
    color: #1E293B;
    margin-bottom: 4px;
}

.checklist-notes {
    margin-bottom: 4px;
}

.checklist-meta {
    display: flex;
    gap: 16px;
    flex-wrap: wrap;
    font-size: 13px;
    color: #94A3B8;
}

.checklist-actions {
    display: flex;
    gap: 4px;
    margin-left: 12px;
}

.filter-group {
    display: flex;
    gap: 8px;
}

.filter-group .form-select {
    border-radius: 8px;
    border-color: #E2E8F0;
    font-size: 13px;
}

@media (max-width: 768px) {
    .checklist-item {
        flex-direction: column;
        gap: 12px;
    }
    
    .checklist-checkbox-wrapper {
        margin-right: 0;
    }
    
    .checklist-actions {
        margin-left: 0;
        width: 100%;
    }
    
    .checklist-actions .btn {
        flex: 1;
    }
    
    .filter-group {
        flex-direction: column;
        width: 100%;
    }
    
    .filter-group .form-select {
        width: 100% !important;
    }
}
</style>

<script>
$(document).ready(function() {
    // Checklist checkbox toggle
    $('.checklist-checkbox').on('change', function() {
        const item = $(this).closest('.checklist-item');
        const id = $(this).data('id');
        const checked = $(this).is(':checked');
        const status = checked ? 'completed' : 'pending';
        
        $.ajax({
            url: '<?php echo BASE_URL; ?>/api/compliance/checklist/' + id,
            method: 'POST',
            data: {
                _csrf: '<?php echo $csrf_token ?? ''; ?>',
                status: status
            },
            success: function(response) {
                if (response.success) {
                    item.removeClass('completed in_progress pending');
                    item.addClass(status);
                    if (checked) {
                        item.addClass('completed');
                        // Update progress
                        updateProgress();
                    }
                }
            }
        });
    });
    
    // Filter functionality
    $('#filterStatus, #filterCategory').on('change', function() {
        const status = $('#filterStatus').val();
        const category = $('#filterCategory').val();
        
        $('.checklist-item').each(function() {
            const item = $(this);
            let show = true;
            
            if (status !== 'all' && !item.hasClass(status)) {
                show = false;
            }
            
            if (category !== 'all' && item.data('category') !== category) {
                show = false;
            }
            
            item.toggle(show);
        });
    });
    
    // Update progress
    function updateProgress() {
        const total = $('.checklist-item').length;
        const completed = $('.checklist-item.completed').length;
        const progress = total > 0 ? Math.round((completed / total) * 100) : 0;
        
        $('.progress-bar').css('width', progress + '%').text(progress + '%');
        $('.progress-stats span:first').text('Completed: ' + completed);
    }
    
    // Export checklist
    $('#exportChecklist').on('click', function() {
        window.location.href = '<?php echo BASE_URL; ?>/compliance/checklist/export';
    });
});
</script>