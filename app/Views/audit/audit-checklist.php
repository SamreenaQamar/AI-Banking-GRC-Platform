<?php
/**
 * Audit Checklist Page
 * 
 * @var string $title
 * @var array $checklist_items
 * @var array $progress
 * @var object $audit
 */
?>

<?php $page_title = 'Audit Checklist'; ?>
<?php $active_page = 'audit'; ?>

<div class="audit-checklist-container">
    <!-- Audit Info -->
    <?php if (isset($audit)): ?>
    <div class="audit-info-banner mb-4">
        <div class="audit-info-content">
            <div>
                <h5><?php echo htmlspecialchars($audit->title); ?></h5>
                <p class="mb-0">
                    <span class="badge bg-primary me-2"><?php echo ucfirst($audit->audit_type); ?></span>
                    <span class="badge bg-<?php echo $audit->status === 'in_progress' ? 'warning' : 'secondary'; ?>">
                        <?php echo ucfirst($audit->status); ?>
                    </span>
                    <span class="text-muted ms-3">
                        <i class="far fa-calendar-alt me-1"></i>
                        <?php echo date('d M Y', strtotime($audit->start_date)); ?> - 
                        <?php echo date('d M Y', strtotime($audit->end_date)); ?>
                    </span>
                </p>
            </div>
            <div>
                <span class="progress-label">Overall Progress</span>
                <div class="progress" style="width: 200px; height: 8px;">
                    <div class="progress-bar" style="width: <?php echo $progress['overall'] ?? 0; ?>%; 
                         background: <?php echo ($progress['overall'] ?? 0) >= 80 ? '#22C55E' : (($progress['overall'] ?? 0) >= 50 ? '#F59E0B' : '#EF4444'); ?>;">
                    </div>
                </div>
                <span class="progress-text"><?php echo $progress['overall'] ?? 0; ?>%</span>
            </div>
        </div>
    </div>
    <?php endif; ?>
    
    <!-- Progress Summary -->
    <div class="row g-4 mb-4">
        <div class="col-xl-3 col-lg-6">
            <div class="summary-card">
                <div class="summary-number"><?php echo $progress['total'] ?? 0; ?></div>
                <div class="summary-label">Total Items</div>
            </div>
        </div>
        <div class="col-xl-3 col-lg-6">
            <div class="summary-card">
                <div class="summary-number text-success"><?php echo $progress['completed'] ?? 0; ?></div>
                <div class="summary-label">Completed</div>
            </div>
        </div>
        <div class="col-xl-3 col-lg-6">
            <div class="summary-card">
                <div class="summary-number text-warning"><?php echo $progress['in_progress'] ?? 0; ?></div>
                <div class="summary-label">In Progress</div>
            </div>
        </div>
        <div class="col-xl-3 col-lg-6">
            <div class="summary-card">
                <div class="summary-number text-danger"><?php echo $progress['pending'] ?? 0; ?></div>
                <div class="summary-label">Pending</div>
            </div>
        </div>
    </div>
    
    <!-- Checklist Items -->
    <div class="card">
        <div class="card-header d-flex justify-content-between align-items-center">
            <span><i class="fas fa-list-check me-2"></i> Audit Checklist</span>
            <div>
                <button class="btn btn-sm btn-outline-primary" id="exportChecklist">
                    <i class="fas fa-download me-1"></i> Export
                </button>
                <button class="btn btn-sm btn-outline-secondary" id="resetProgress">
                    <i class="fas fa-undo me-1"></i> Reset Progress
                </button>
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
                                    <span class="checklist-category badge bg-secondary"><?php echo ucfirst($item->category); ?></span>
                                    <?php if ($item->required): ?>
                                        <span class="badge bg-danger">Required</span>
                                    <?php endif; ?>
                                </div>
                                <div class="checklist-text"><?php echo htmlspecialchars($item->description); ?></div>
                                <?php if ($item->reference): ?>
                                    <div class="checklist-reference">
                                        <small class="text-muted"><i class="fas fa-book me-1"></i> Reference: <?php echo htmlspecialchars($item->reference); ?></small>
                                    </div>
                                <?php endif; ?>
                                <?php if ($item->status === 'completed' && $item->completed_at): ?>
                                    <div class="checklist-meta">
                                        <span class="text-success">
                                            <i class="fas fa-check-circle me-1"></i>
                                            Completed: <?php echo date('d M Y h:i A', strtotime($item->completed_at)); ?>
                                        </span>
                                        <?php if ($item->completed_by): ?>
                                            <span>by <?php echo htmlspecialchars($item->completed_by_name); ?></span>
                                        <?php endif; ?>
                                    </div>
                                <?php endif; ?>
                            </div>
                            <div class="checklist-actions">
                                <?php if ($item->status !== 'completed'): ?>
                                    <button class="btn btn-sm btn-outline-success start-task" data-id="<?php echo $item->id; ?>">
                                        <i class="fas fa-play"></i>
                                    </button>
                                <?php endif; ?>
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
                        <p>No checklist items found for this audit.</p>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<style>
.audit-checklist-container {
    padding: 0;
}

.audit-info-banner {
    background: linear-gradient(135deg, #0B3D91, #2563EB);
    color: #fff;
    padding: 20px 24px;
    border-radius: 12px;
}

.audit-info-content {
    display: flex;
    justify-content: space-between;
    align-items: center;
    flex-wrap: wrap;
    gap: 12px;
}

.audit-info-content h5 {
    margin: 0;
}

.audit-info-content .progress-label {
    font-size: 13px;
    opacity: 0.85;
    margin-right: 8px;
}

.audit-info-content .progress-text {
    font-size: 14px;
    font-weight: 600;
    margin-left: 8px;
}

.summary-card {
    background: #fff;
    padding: 20px;
    border-radius: 12px;
    box-shadow: 0 1px 3px rgba(0,0,0,0.06);
    text-align: center;
    transition: all 0.3s;
    height: 100%;
}

.summary-card:hover {
    box-shadow: 0 4px 12px rgba(0,0,0,0.1);
    transform: translateY(-2px);
}

.summary-number {
    font-size: 28px;
    font-weight: 700;
    color: #1E293B;
}

.summary-number.text-success { color: #22C55E; }
.summary-number.text-warning { color: #F59E0B; }
.summary-number.text-danger { color: #EF4444; }

.summary-label {
    font-size: 14px;
    color: #64748B;
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

.checklist-item.in_progress {
    background: #FEFCE8;
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

.checklist-reference {
    margin-bottom: 4px;
}

.checklist-meta {
    display: flex;
    gap: 16px;
    flex-wrap: wrap;
    font-size: 13px;
}

.checklist-actions {
    display: flex;
    gap: 4px;
    margin-left: 12px;
}

@media (max-width: 768px) {
    .audit-info-content {
        flex-direction: column;
        align-items: flex-start;
    }
    
    .audit-info-content .progress {
        width: 100% !important;
    }
    
    .checklist-item {
        flex-direction: column;
        gap: 12px;
    }
    
    .checklist-actions {
        margin-left: 0;
        width: 100%;
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
            url: '<?php echo BASE_URL; ?>/api/audit/checklist/' + id,
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
                        updateProgress();
                    }
                }
            }
        });
    });
    
    // Start task button
    $('.start-task').on('click', function() {
        const id = $(this).data('id');
        const item = $(this).closest('.checklist-item');
        
        $.ajax({
            url: '<?php echo BASE_URL; ?>/api/audit/checklist/' + id + '/start',
            method: 'POST',
            data: {
                _csrf: '<?php echo $csrf_token ?? ''; ?>'
            },
            success: function(response) {
                if (response.success) {
                    item.removeClass('pending');
                    item.addClass('in_progress');
                    $(this).remove();
                }
            }.bind(this)
        });
    });
    
    // Update progress
    function updateProgress() {
        const total = $('.checklist-item').length;
        const completed = $('.checklist-item.completed').length;
        const progress = total > 0 ? Math.round((completed / total) * 100) : 0;
        
        $('.progress-bar').css('width', progress + '%').text(progress + '%');
        $('.summary-number.text-success').text(completed);
        $('.progress-text').text(progress + '%');
    }
});
</script>