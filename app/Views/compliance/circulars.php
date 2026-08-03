<?php
/**
 * SBP Circulars Page
 * 
 * @var string $title
 * @var array $circulars
 * @var array $filters
 * @var array $categories
 */
?>

<?php $page_title = 'SBP Circulars'; ?>
<?php $active_page = 'compliance'; ?>

<div class="circulars-container">
    <!-- Header Actions -->
    <div class="row mb-4">
        <div class="col-md-8">
            <div class="filter-section">
                <div class="btn-group" role="group">
                    <button class="btn btn-outline-primary active" data-filter="all">All</button>
                    <button class="btn btn-outline-primary" data-filter="active">Active</button>
                    <button class="btn btn-outline-primary" data-filter="pending">Pending</button>
                    <button class="btn btn-outline-primary" data-filter="implemented">Implemented</button>
                </div>
            </div>
        </div>
        <div class="col-md-4 text-end">
            <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addCircularModal">
                <i class="fas fa-plus me-2"></i> Add Circular
            </button>
            <button class="btn btn-outline-primary" id="exportBtn">
                <i class="fas fa-download me-2"></i> Export
            </button>
        </div>
    </div>
    
    <!-- Circulars Grid -->
    <div class="row g-4">
        <?php if (!empty($circulars)): ?>
            <?php foreach ($circulars as $circular): ?>
                <div class="col-xl-4 col-lg-6">
                    <div class="circular-card">
                        <div class="circular-header">
                            <div class="circular-badge <?php echo $circular->status; ?>">
                                <?php echo ucfirst($circular->status); ?>
                            </div>
                            <div class="circular-category">
                                <i class="fas fa-tag"></i>
                                <?php echo htmlspecialchars($circular->category); ?>
                            </div>
                        </div>
                        
                        <div class="circular-body">
                            <h5 class="circular-title">
                                <?php echo htmlspecialchars($circular->title); ?>
                            </h5>
                            <div class="circular-number">
                                <i class="fas fa-hashtag"></i>
                                <?php echo htmlspecialchars($circular->circular_number); ?>
                            </div>
                            <p class="circular-description">
                                <?php echo htmlspecialchars(substr($circular->description, 0, 120)) . '...'; ?>
                            </p>
                        </div>
                        
                        <div class="circular-footer">
                            <div class="circular-meta">
                                <span>
                                    <i class="far fa-calendar-alt"></i>
                                    Issued: <?php echo date('d M Y', strtotime($circular->issuance_date)); ?>
                                </span>
                                <span>
                                    <i class="far fa-clock"></i>
                                    Due: <?php echo date('d M Y', strtotime($circular->compliance_deadline)); ?>
                                </span>
                            </div>
                            <div class="circular-actions">
                                <a href="<?php echo BASE_URL; ?>/sbp-circulars/<?php echo $circular->id; ?>" 
                                   class="btn btn-sm btn-outline-primary">
                                    <i class="fas fa-eye"></i> View
                                </a>
                                <?php if ($circular->status === 'pending'): ?>
                                    <button class="btn btn-sm btn-success implement-btn" 
                                            data-id="<?php echo $circular->id; ?>">
                                        <i class="fas fa-check"></i> Implement
                                    </button>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        <?php else: ?>
            <div class="col-12">
                <div class="empty-state">
                    <i class="fas fa-newspaper fa-3x text-muted"></i>
                    <h5>No Circulars Found</h5>
                    <p class="text-muted">No SBP circulars have been added yet.</p>
                </div>
            </div>
        <?php endif; ?>
    </div>
    
    <!-- Pagination -->
    <?php if (isset($total_pages) && $total_pages > 1): ?>
    <nav class="mt-4">
        <ul class="pagination justify-content-center">
            <li class="page-item <?php echo $page <= 1 ? 'disabled' : ''; ?>">
                <a class="page-link" href="<?php echo BASE_URL; ?>/sbp-circulars?page=<?php echo $page - 1; ?>">Previous</a>
            </li>
            <?php for ($i = 1; $i <= $total_pages; $i++): ?>
                <li class="page-item <?php echo $i == $page ? 'active' : ''; ?>">
                    <a class="page-link" href="<?php echo BASE_URL; ?>/sbp-circulars?page=<?php echo $i; ?>"><?php echo $i; ?></a>
                </li>
            <?php endfor; ?>
            <li class="page-item <?php echo $page >= $total_pages ? 'disabled' : ''; ?>">
                <a class="page-link" href="<?php echo BASE_URL; ?>/sbp-circulars?page=<?php echo $page + 1; ?>">Next</a>
            </li>
        </ul>
    </nav>
    <?php endif; ?>
</div>

<!-- Add Circular Modal -->
<div class="modal fade" id="addCircularModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title"><i class="fas fa-plus-circle me-2"></i> Add SBP Circular</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form method="POST" action="<?php echo BASE_URL; ?>/sbp-circulars" enctype="multipart/form-data">
                <div class="modal-body">
                    <input type="hidden" name="csrf_token" value="<?php echo $csrf_token ?? ''; ?>">
                    
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label">Circular Number</label>
                            <input type="text" class="form-control" name="circular_number" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Category</label>
                            <select class="form-select" name="category" required>
                                <option value="">Select Category</option>
                                <?php foreach ($categories as $key => $label): ?>
                                    <option value="<?php echo $key; ?>"><?php echo $label; ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-12">
                            <label class="form-label">Title</label>
                            <input type="text" class="form-control" name="title" required>
                        </div>
                        <div class="col-12">
                            <label class="form-label">Description</label>
                            <textarea class="form-control" name="description" rows="4" required></textarea>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Issuance Date</label>
                            <input type="date" class="form-control" name="issuance_date" required>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Effective Date</label>
                            <input type="date" class="form-control" name="effective_date" required>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Compliance Deadline</label>
                            <input type="date" class="form-control" name="compliance_deadline" required>
                        </div>
                        <div class="col-12">
                            <label class="form-label">Priority</label>
                            <select class="form-select" name="priority">
                                <option value="medium">Medium</option>
                                <option value="high">High</option>
                                <option value="critical">Critical</option>
                                <option value="low">Low</option>
                            </select>
                        </div>
                        <div class="col-12">
                            <label class="form-label">Upload Document</label>
                            <input type="file" class="form-control" name="document" accept=".pdf,.docx,.xlsx">
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">Add Circular</button>
                </div>
            </form>
        </div>
    </div>
</div>

<style>
.circular-card {
    background: #FFFFFF;
    border-radius: 12px;
    padding: 20px;
    box-shadow: 0 1px 3px rgba(0,0,0,0.06);
    transition: all 0.3s;
    height: 100%;
    display: flex;
    flex-direction: column;
}

.circular-card:hover {
    box-shadow: 0 4px 12px rgba(0,0,0,0.1);
    transform: translateY(-2px);
}

.circular-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 12px;
}

.circular-badge {
    padding: 4px 12px;
    border-radius: 12px;
    font-size: 11px;
    font-weight: 600;
}

.circular-badge.active { background: #D1FAE5; color: #10B981; }
.circular-badge.pending { background: #FEF3C7; color: #F59E0B; }
.circular-badge.implemented { background: #DBEAFE; color: #3B82F6; }
.circular-badge.withdrawn { background: #FEE2E2; color: #EF4444; }

.circular-category {
    font-size: 12px;
    color: #64748B;
}

.circular-title {
    font-size: 16px;
    font-weight: 600;
    color: #1E293B;
    margin: 0 0 4px;
}

.circular-number {
    font-size: 13px;
    color: #64748B;
    margin-bottom: 8px;
}

.circular-description {
    font-size: 14px;
    color: #64748B;
    margin: 0;
    flex: 1;
}

.circular-footer {
    margin-top: 16px;
    padding-top: 12px;
    border-top: 1px solid #F1F5F9;
}

.circular-meta {
    display: flex;
    gap: 16px;
    font-size: 12px;
    color: #94A3B8;
}

.circular-meta i {
    margin-right: 4px;
}

.circular-actions {
    margin-top: 12px;
    display: flex;
    gap: 8px;
}

.filter-section .btn-group .btn {
    border-radius: 8px;
    margin-right: 4px;
}

.empty-state {
    text-align: center;
    padding: 60px 20px;
}

.empty-state i {
    display: block;
    margin-bottom: 16px;
}

.empty-state h5 {
    color: #1E293B;
    margin-bottom: 8px;
}
</style>

<script>
$(document).ready(function() {
    // Filter buttons
    $('.filter-section .btn').on('click', function() {
        $('.filter-section .btn').removeClass('active');
        $(this).addClass('active');
        // Apply filter logic
        const filter = $(this).data('filter');
        if (filter === 'all') {
            $('.circular-card').show();
        } else {
            $('.circular-card').hide();
            $(`.circular-card:has(.circular-badge.${filter})`).show();
        }
    });
    
    // Implement circular
    $('.implement-btn').on('click', function() {
        const id = $(this).data('id');
        if (confirm('Mark this circular as implemented?')) {
            $.ajax({
                url: '<?php echo BASE_URL; ?>/sbp-circulars/' + id + '/implement',
                method: 'POST',
                data: {
                    _csrf: '<?php echo $csrf_token ?? ''; ?>'
                },
                success: function(response) {
                    if (response.success) {
                        location.reload();
                    }
                }
            });
        }
    });
});
</script>