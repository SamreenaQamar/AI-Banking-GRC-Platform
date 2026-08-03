<?php
/**
 * SBP Circular Details Page
 * 
 * @var string $title
 * @var object $circular
 * @var array $related_circulars
 * @var array $compliance_checklist
 */
?>

<?php $page_title = 'SBP Circular Details'; ?>
<?php $active_page = 'compliance'; ?>

<div class="circular-details-container">
    <!-- Navigation -->
    <nav aria-label="breadcrumb" class="mb-4">
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="<?php echo BASE_URL; ?>/compliance">Compliance</a></li>
            <li class="breadcrumb-item"><a href="<?php echo BASE_URL; ?>/sbp-circulars">SBP Circulars</a></li>
            <li class="breadcrumb-item active" aria-current="page"><?php echo htmlspecialchars($circular->circular_number ?? ''); ?></li>
        </ol>
    </nav>
    
    <div class="row g-4">
        <!-- Main Content -->
        <div class="col-xl-8">
            <!-- Circular Header -->
            <div class="card">
                <div class="card-header-gradient">
                    <div class="d-flex justify-content-between align-items-start">
                        <div>
                            <h5 class="mb-1 text-white"><?php echo htmlspecialchars($circular->title ?? ''); ?></h5>
                            <small class="text-white-50">
                                <i class="far fa-calendar-alt me-1"></i>
                                Issued: <?php echo date('d M Y', strtotime($circular->issuance_date ?? 'now')); ?>
                            </small>
                        </div>
                        <div class="d-flex gap-2">
                            <span class="badge bg-<?php echo $circular->status === 'active' ? 'success' : ($circular->status === 'pending' ? 'warning' : 'secondary'); ?> fs-6">
                                <?php echo ucfirst($circular->status ?? 'Draft'); ?>
                            </span>
                        </div>
                    </div>
                </div>
                <div class="card-body">
                    <div class="circular-meta mb-4">
                        <div class="row g-3">
                            <div class="col-md-3">
                                <div class="meta-item">
                                    <label>Circular Number</label>
                                    <div class="meta-value"><?php echo htmlspecialchars($circular->circular_number ?? 'N/A'); ?></div>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="meta-item">
                                    <label>Category</label>
                                    <div class="meta-value">
                                        <span class="badge bg-primary"><?php echo ucfirst($circular->category ?? 'General'); ?></span>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="meta-item">
                                    <label>Priority</label>
                                    <div class="meta-value">
                                        <span class="badge bg-<?php echo $circular->priority === 'critical' ? 'danger' : ($circular->priority === 'high' ? 'warning' : 'secondary'); ?>">
                                            <?php echo ucfirst($circular->priority ?? 'Medium'); ?>
                                        </span>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="meta-item">
                                    <label>Effective Date</label>
                                    <div class="meta-value"><?php echo date('d M Y', strtotime($circular->effective_date ?? 'now')); ?></div>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <div class="circular-description">
                        <h6>Description</h6>
                        <p><?php echo nl2br(htmlspecialchars($circular->description ?? '')); ?></p>
                    </div>
                    
                    <?php if (!empty($circular->implementation_notes)): ?>
                    <div class="circular-implementation mt-4">
                        <h6>Implementation Notes</h6>
                        <div class="implementation-notes">
                            <?php echo nl2br(htmlspecialchars($circular->implementation_notes)); ?>
                        </div>
                    </div>
                    <?php endif; ?>
                </div>
            </div>
            
            <!-- Compliance Checklist -->
            <div class="card mt-4">
                <div class="card-header">
                    <i class="fas fa-check-double me-2"></i> Compliance Checklist
                    <span class="badge bg-primary ms-2"><?php echo $compliance_checklist['completed'] ?? 0; ?>/<?php echo $compliance_checklist['total'] ?? 0; ?></span>
                </div>
                <div class="card-body p-0">
                    <div class="checklist-container">
                        <?php if (!empty($compliance_checklist['items'])): ?>
                            <?php foreach ($compliance_checklist['items'] as $index => $item): ?>
                                <div class="checklist-item <?php echo $item['status'] === 'completed' ? 'completed' : ''; ?>">
                                    <div class="checklist-status">
                                        <input type="checkbox" class="checklist-checkbox" 
                                               <?php echo $item['status'] === 'completed' ? 'checked' : ''; ?>
                                               data-id="<?php echo $item['id']; ?>">
                                        <span class="checklist-number"><?php echo $index + 1; ?></span>
                                    </div>
                                    <div class="checklist-content">
                                        <div class="checklist-text"><?php echo htmlspecialchars($item['description']); ?></div>
                                        <?php if ($item['status'] === 'completed' && !empty($item['completed_at'])): ?>
                                            <div class="checklist-meta">
                                                <i class="far fa-calendar-check me-1"></i>
                                                Completed: <?php echo date('d M Y', strtotime($item['completed_at'])); ?>
                                            </div>
                                        <?php endif; ?>
                                    </div>
                                    <?php if ($item['status'] === 'completed'): ?>
                                        <div class="checklist-badge">
                                            <span class="badge bg-success"><i class="fas fa-check me-1"></i> Done</span>
                                        </div>
                                    <?php endif; ?>
                                </div>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <div class="text-center text-muted py-4">
                                <i class="fas fa-clipboard-list fa-2x mb-2"></i>
                                <p>No checklist items found for this circular.</p>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
            
            <!-- Related Circulars -->
            <?php if (!empty($related_circulars)): ?>
            <div class="card mt-4">
                <div class="card-header">
                    <i class="fas fa-link me-2"></i> Related Circulars
                </div>
                <div class="card-body p-0">
                    <div class="related-list">
                        <?php foreach ($related_circulars as $related): ?>
                            <div class="related-item">
                                <a href="<?php echo BASE_URL; ?>/sbp-circulars/<?php echo $related->id; ?>" class="related-link">
                                    <div class="related-number"><?php echo htmlspecialchars($related->circular_number); ?></div>
                                    <div class="related-title"><?php echo htmlspecialchars($related->title); ?></div>
                                    <div class="related-status">
                                        <span class="badge bg-<?php echo $related->status === 'active' ? 'success' : 'secondary'; ?>">
                                            <?php echo ucfirst($related->status ?? 'Draft'); ?>
                                        </span>
                                    </div>
                                </a>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </div>
            </div>
            <?php endif; ?>
        </div>
        
        <!-- Sidebar -->
        <div class="col-xl-4">
            <!-- Actions -->
            <div class="card">
                <div class="card-header">
                    <i class="fas fa-tasks me-2"></i> Actions
                </div>
                <div class="card-body">
                    <div class="d-grid gap-2">
                        <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#implementModal">
                            <i class="fas fa-check-circle me-2"></i> Mark as Implemented
                        </button>
                        <button class="btn btn-outline-primary">
                            <i class="fas fa-file-pdf me-2"></i> Download PDF
                        </button>
                        <button class="btn btn-outline-secondary">
                            <i class="fas fa-share-alt me-2"></i> Share
                        </button>
                        <button class="btn btn-outline-secondary">
                            <i class="fas fa-print me-2"></i> Print
                        </button>
                    </div>
                </div>
            </div>
            
            <!-- Quick Stats -->
            <div class="card mt-4">
                <div class="card-header">
                    <i class="fas fa-chart-simple me-2"></i> Quick Stats
                </div>
                <div class="card-body">
                    <div class="stat-items">
                        <div class="stat-row">
                            <span class="stat-label">Compliance Rate</span>
                            <span class="stat-value"><?php echo $circular->compliance_rate ?? 68; ?>%</span>
                        </div>
                        <div class="stat-row">
                            <span class="stat-label">Days Remaining</span>
                            <span class="stat-value <?php echo ($circular->days_remaining ?? 0) < 7 ? 'text-danger' : ''; ?>">
                                <?php echo $circular->days_remaining ?? 15; ?> days
                            </span>
                        </div>
                        <div class="stat-row">
                            <span class="stat-label">Implementation Status</span>
                            <span class="stat-value">
                                <span class="badge bg-<?php echo $circular->status === 'implemented' ? 'success' : 'warning'; ?>">
                                    <?php echo $circular->status === 'implemented' ? 'Completed' : 'In Progress'; ?>
                                </span>
                            </span>
                        </div>
                        <div class="stat-row">
                            <span class="stat-label">AI Analysis</span>
                            <span class="stat-value">
                                <span class="badge bg-info">
                                    <i class="fas fa-robot me-1"></i> Available
                                </span>
                            </span>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- AI Analysis Summary -->
            <?php if (!empty($circular->ai_summary)): ?>
            <div class="card mt-4">
                <div class="card-header">
                    <i class="fas fa-robot me-2"></i> AI Analysis
                </div>
                <div class="card-body">
                    <p class="ai-summary"><?php echo htmlspecialchars($circular->ai_summary); ?></p>
                    <button class="btn btn-sm btn-outline-primary w-100 mt-2">
                        <i class="fas fa-chevron-circle-right me-1"></i> View Full Analysis
                    </button>
                </div>
            </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<!-- Implement Modal -->
<div class="modal fade" id="implementModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title"><i class="fas fa-check-circle me-2"></i> Mark as Implemented</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form method="POST" action="<?php echo BASE_URL; ?>/sbp-circulars/<?php echo $circular->id ?? 0; ?>/implement">
                <div class="modal-body">
                    <input type="hidden" name="csrf_token" value="<?php echo $csrf_token ?? ''; ?>">
                    
                    <p>Are you sure you want to mark this circular as implemented?</p>
                    
                    <div class="form-group">
                        <label class="form-label">Implementation Notes</label>
                        <textarea class="form-control" name="implementation_notes" rows="4" 
                                  placeholder="Provide details about the implementation..."></textarea>
                    </div>
                    
                    <div class="form-group">
                        <label class="form-label">Implementation Date</label>
                        <input type="date" class="form-control" name="implementation_date" 
                               value="<?php echo date('Y-m-d'); ?>">
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-success">Confirm Implementation</button>
                </div>
            </form>
        </div>
    </div>
</div>

<style>
.circular-details-container {
    padding: 0;
}

.card-header-gradient {
    background: linear-gradient(135deg, #0B3D91, #2563EB);
    padding: 20px 24px;
    border-radius: 12px 12px 0 0;
}

.meta-item {
    padding: 8px 0;
}

.meta-item label {
    display: block;
    font-size: 12px;
    color: #94A3B8;
    font-weight: 500;
    margin-bottom: 2px;
}

.meta-item .meta-value {
    font-size: 15px;
    color: #1E293B;
    font-weight: 500;
}

.circular-description {
    padding-top: 16px;
    border-top: 1px solid #F1F5F9;
}

.circular-description h6 {
    font-weight: 600;
    color: #1E293B;
    margin-bottom: 8px;
}

.circular-description p {
    color: #64748B;
    line-height: 1.8;
    margin: 0;
}

.implementation-notes {
    background: #F8FAFC;
    padding: 16px;
    border-radius: 8px;
    color: #1E293B;
    line-height: 1.8;
}

.checklist-container {
    padding: 8px 0;
}

.checklist-item {
    display: flex;
    align-items: flex-start;
    padding: 12px 20px;
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

.checklist-status {
    display: flex;
    align-items: center;
    gap: 12px;
    margin-right: 16px;
    padding-top: 2px;
}

.checklist-checkbox {
    width: 18px;
    height: 18px;
    cursor: pointer;
    accent-color: #2563EB;
}

.checklist-number {
    font-size: 12px;
    color: #94A3B8;
    font-weight: 500;
    min-width: 24px;
}

.checklist-content {
    flex: 1;
}

.checklist-text {
    font-size: 14px;
    color: #1E293B;
}

.checklist-meta {
    font-size: 12px;
    color: #94A3B8;
    margin-top: 4px;
}

.checklist-badge {
    margin-left: 12px;
}

.related-list {
    padding: 4px 0;
}

.related-item {
    padding: 4px 0;
}

.related-link {
    display: flex;
    align-items: center;
    gap: 16px;
    padding: 10px 16px;
    border-radius: 8px;
    text-decoration: none;
    transition: background 0.2s;
}

.related-link:hover {
    background: #F8FAFC;
}

.related-number {
    font-weight: 600;
    color: #2563EB;
    font-size: 13px;
    min-width: 100px;
}

.related-title {
    flex: 1;
    color: #1E293B;
    font-size: 14px;
}

.stat-items {
    display: flex;
    flex-direction: column;
    gap: 12px;
}

.stat-row {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 8px 0;
    border-bottom: 1px solid #F1F5F9;
}

.stat-row:last-child {
    border-bottom: none;
}

.stat-label {
    color: #64748B;
    font-size: 14px;
}

.stat-value {
    font-weight: 600;
    color: #1E293B;
    font-size: 14px;
}

.ai-summary {
    color: #64748B;
    font-size: 14px;
    line-height: 1.8;
    margin: 0;
}

@media (max-width: 768px) {
    .meta-item {
        padding: 4px 0;
    }
    
    .related-link {
        flex-wrap: wrap;
        gap: 8px;
    }
    
    .related-number {
        min-width: auto;
        font-size: 12px;
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
        
        $.ajax({
            url: '<?php echo BASE_URL; ?>/api/compliance/checklist/' + id,
            method: 'POST',
            data: {
                _csrf: '<?php echo $csrf_token ?? ''; ?>',
                status: checked ? 'completed' : 'pending'
            },
            success: function(response) {
                if (response.success) {
                    item.toggleClass('completed', checked);
                    // Update completion count
                    const total = $('.checklist-item').length;
                    const completed = $('.checklist-item.completed').length;
                    $('.card-header .badge').text(completed + '/' + total);
                }
            }
        });
    });
});
</script>