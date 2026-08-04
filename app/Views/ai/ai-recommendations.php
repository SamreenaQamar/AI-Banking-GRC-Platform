<?php
/**
 * AI Recommendations View
 * 
 * @var string $title
 * @var array $recommendations
 * @var array $stats
 * @var array $filters
 */
?>

<?php $page_title = 'AI Recommendations'; ?>
<?php $active_page = 'ai'; ?>

<div class="recommendations-container">
    <!-- Page Header -->
    <div class="page-header">
        <div class="page-header-left">
            <h1 class="page-title">
                <i class="fas fa-lightbulb text-primary me-2"></i> AI Recommendations
            </h1>
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="/dashboard">Dashboard</a></li>
                    <li class="breadcrumb-item"><a href="/ai">AI</a></li>
                    <li class="breadcrumb-item active" aria-current="page">Recommendations</li>
                </ol>
            </nav>
        </div>
        <div class="page-header-right">
            <div class="quick-actions">
                <button class="btn btn-primary btn-sm" id="refreshRecommendations">
                    <i class="fas fa-sync-alt me-1"></i> Refresh
                </button>
                <button class="btn btn-outline-primary btn-sm" id="exportRecommendations">
                    <i class="fas fa-download me-1"></i> Export
                </button>
            </div>
        </div>
    </div>

    <!-- Stats Cards -->
    <div class="row g-4 mb-4">
        <div class="col-xl-3 col-lg-6">
            <div class="stat-card">
                <div class="stat-icon" style="background: rgba(37, 99, 235, 0.1); color: #2563EB;">
                    <i class="fas fa-list"></i>
                </div>
                <div class="stat-info">
                    <div class="stat-value"><?php echo $stats['total'] ?? 0; ?></div>
                    <div class="stat-label">Total Recommendations</div>
                </div>
            </div>
        </div>
        <div class="col-xl-3 col-lg-6">
            <div class="stat-card">
                <div class="stat-icon" style="background: rgba(239, 68, 68, 0.1); color: #EF4444;">
                    <i class="fas fa-exclamation-circle"></i>
                </div>
                <div class="stat-info">
                    <div class="stat-value text-danger"><?php echo $stats['high_priority'] ?? 0; ?></div>
                    <div class="stat-label">High Priority</div>
                </div>
            </div>
        </div>
        <div class="col-xl-3 col-lg-6">
            <div class="stat-card">
                <div class="stat-icon" style="background: rgba(34, 197, 94, 0.1); color: #22C55E;">
                    <i class="fas fa-check-circle"></i>
                </div>
                <div class="stat-info">
                    <div class="stat-value text-success"><?php echo $stats['implemented'] ?? 0; ?></div>
                    <div class="stat-label">Implemented</div>
                </div>
            </div>
        </div>
        <div class="col-xl-3 col-lg-6">
            <div class="stat-card">
                <div class="stat-icon" style="background: rgba(245, 158, 11, 0.1); color: #F59E0B;">
                    <i class="fas fa-clock"></i>
                </div>
                <div class="stat-info">
                    <div class="stat-value"><?php echo $stats['pending'] ?? 0; ?></div>
                    <div class="stat-label">Pending Review</div>
                </div>
            </div>
        </div>
    </div>

    <!-- Filters -->
    <div class="filter-section mb-4">
        <div class="row g-3">
            <div class="col-md-3">
                <select class="form-select" id="filterType">
                    <option value="">All Types</option>
                    <option value="risk">Risk</option>
                    <option value="compliance">Compliance</option>
                    <option value="audit">Audit</option>
                    <option value="policy">Policy</option>
                    <option value="security">Security</option>
                </select>
            </div>
            <div class="col-md-3">
                <select class="form-select" id="filterPriority">
                    <option value="">All Priority</option>
                    <option value="critical">Critical</option>
                    <option value="high">High</option>
                    <option value="medium">Medium</option>
                    <option value="low">Low</option>
                </select>
            </div>
            <div class="col-md-3">
                <select class="form-select" id="filterStatus">
                    <option value="">All Status</option>
                    <option value="pending">Pending</option>
                    <option value="accepted">Accepted</option>
                    <option value="implemented">Implemented</option>
                    <option value="rejected">Rejected</option>
                </select>
            </div>
            <div class="col-md-3">
                <div class="search-box">
                    <i class="fas fa-search"></i>
                    <input type="text" class="form-control" id="searchRecommendation" placeholder="Search recommendations...">
                </div>
            </div>
        </div>
    </div>

    <!-- Recommendations Grid -->
    <div class="row g-4">
        <?php if (!empty($recommendations)): ?>
            <?php foreach ($recommendations as $rec): ?>
                <div class="col-xl-4 col-lg-6">
                    <div class="recommendation-card priority-<?php echo $rec->priority; ?>" 
                         data-type="<?php echo $rec->type; ?>"
                         data-priority="<?php echo $rec->priority; ?>"
                         data-status="<?php echo $rec->status; ?>">
                        <div class="recommendation-header">
                            <div class="recommendation-type">
                                <span class="badge bg-<?php echo $rec->priority === 'critical' ? 'danger' : ($rec->priority === 'high' ? 'warning' : ($rec->priority === 'medium' ? 'info' : 'secondary')); ?>">
                                    <?php echo ucfirst($rec->priority); ?> Priority
                                </span>
                                <span class="badge bg-light text-dark ms-2">
                                    <i class="fas fa-tag me-1"></i> <?php echo ucfirst($rec->type); ?>
                                </span>
                            </div>
                            <div class="recommendation-status">
                                <span class="badge bg-<?php echo $rec->status === 'implemented' ? 'success' : ($rec->status === 'accepted' ? 'primary' : ($rec->status === 'rejected' ? 'danger' : 'warning')); ?>">
                                    <?php echo ucfirst($rec->status ?? 'Pending'); ?>
                                </span>
                            </div>
                        </div>

                        <div class="recommendation-body">
                            <h6 class="recommendation-title"><?php echo htmlspecialchars($rec->title); ?></h6>
                            <p class="recommendation-description"><?php echo htmlspecialchars($rec->description); ?></p>
                            
                            <?php if (!empty($rec->action_items)): ?>
                                <div class="action-items">
                                    <small class="text-muted">Action Items:</small>
                                    <ul class="action-list">
                                        <?php foreach ($rec->action_items as $item): ?>
                                            <li><?php echo htmlspecialchars($item); ?></li>
                                        <?php endforeach; ?>
                                    </ul>
                                </div>
                            <?php endif; ?>
                            
                            <div class="recommendation-meta">
                                <span><i class="fas fa-user me-1"></i> <?php echo htmlspecialchars($rec->assigned_to ?? 'Unassigned'); ?></span>
                                <?php if ($rec->due_date): ?>
                                    <span><i class="far fa-calendar-alt me-1"></i> Due: <?php echo date('d M Y', strtotime($rec->due_date)); ?></span>
                                <?php endif; ?>
                                <span><i class="fas fa-chart-line me-1"></i> Confidence: <?php echo $rec->confidence ?? 85; ?>%</span>
                            </div>
                        </div>

                        <div class="recommendation-footer">
                            <?php if ($rec->status === 'pending' || $rec->status === 'accepted'): ?>
                                <button class="btn btn-sm btn-success implement-rec" data-id="<?php echo $rec->id; ?>">
                                    <i class="fas fa-check me-1"></i> Implement
                                </button>
                                <button class="btn btn-sm btn-outline-secondary accept-rec" data-id="<?php echo $rec->id; ?>">
                                    <i class="fas fa-thumbs-up me-1"></i> Accept
                                </button>
                                <button class="btn btn-sm btn-outline-danger reject-rec" data-id="<?php echo $rec->id; ?>">
                                    <i class="fas fa-times me-1"></i> Reject
                                </button>
                            <?php elseif ($rec->status === 'implemented'): ?>
                                <span class="badge bg-success"><i class="fas fa-check-circle me-1"></i> Implemented</span>
                            <?php elseif ($rec->status === 'rejected'): ?>
                                <span class="badge bg-danger"><i class="fas fa-times-circle me-1"></i> Rejected</span>
                            <?php endif; ?>
                            <button class="btn btn-sm btn-outline-primary view-details" data-id="<?php echo $rec->id; ?>">
                                <i class="fas fa-chevron-circle-right me-1"></i> Details
                            </button>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        <?php else: ?>
            <div class="col-12">
                <div class="empty-state text-center py-5">
                    <i class="fas fa-check-circle fa-3x text-success mb-3"></i>
                    <h5>All Clear!</h5>
                    <p class="text-muted">No recommendations at this time. Your compliance posture is looking good!</p>
                </div>
            </div>
        <?php endif; ?>
    </div>

    <!-- Pagination -->
    <?php if (isset($total_pages) && $total_pages > 1): ?>
    <nav class="mt-4">
        <ul class="pagination justify-content-center">
            <li class="page-item <?php echo $page <= 1 ? 'disabled' : ''; ?>">
                <a class="page-link" href="?page=<?php echo $page - 1; ?>">Previous</a>
            </li>
            <?php for ($i = 1; $i <= $total_pages; $i++): ?>
                <li class="page-item <?php echo $i == $page ? 'active' : ''; ?>">
                    <a class="page-link" href="?page=<?php echo $i; ?>"><?php echo $i; ?></a>
                </li>
            <?php endfor; ?>
            <li class="page-item <?php echo $page >= $total_pages ? 'disabled' : ''; ?>">
                <a class="page-link" href="?page=<?php echo $page + 1; ?>">Next</a>
            </li>
        </ul>
    </nav>
    <?php endif; ?>
</div>

<style>
.recommendations-container {
    padding: 0;
}

.stat-card {
    background: #fff;
    padding: 20px;
    border-radius: 12px;
    box-shadow: 0 1px 3px rgba(0,0,0,0.06);
    display: flex;
    align-items: center;
    gap: 16px;
    height: 100%;
    transition: all 0.3s;
}

.stat-card:hover {
    box-shadow: 0 4px 12px rgba(0,0,0,0.1);
    transform: translateY(-2px);
}

.stat-icon {
    width: 48px;
    height: 48px;
    border-radius: 10px;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 20px;
    flex-shrink: 0;
}

.stat-info {
    flex: 1;
}

.stat-value {
    font-size: 24px;
    font-weight: 700;
    color: #1E293B;
}

.stat-label {
    font-size: 14px;
    color: #64748B;
}

.filter-section {
    background: #fff;
    padding: 16px 20px;
    border-radius: 12px;
    box-shadow: 0 1px 3px rgba(0,0,0,0.06);
}

.search-box {
    position: relative;
}

.search-box i {
    position: absolute;
    left: 14px;
    top: 50%;
    transform: translateY(-50%);
    color: #94A3B8;
}

.search-box .form-control {
    padding-left: 40px;
    border-radius: 8px;
}

.recommendation-card {
    background: #fff;
    border-radius: 12px;
    padding: 20px;
    box-shadow: 0 1px 3px rgba(0,0,0,0.06);
    transition: all 0.3s;
    height: 100%;
    display: flex;
    flex-direction: column;
    border-left: 4px solid #E2E8F0;
}

.recommendation-card:hover {
    box-shadow: 0 4px 12px rgba(0,0,0,0.1);
    transform: translateY(-2px);
}

.recommendation-card.priority-critical { border-left-color: #DC2626; }
.recommendation-card.priority-high { border-left-color: #F59E0B; }
.recommendation-card.priority-medium { border-left-color: #3B82F6; }
.recommendation-card.priority-low { border-left-color: #22C55E; }

.recommendation-header {
    display: flex;
    justify-content: space-between;
    align-items: flex-start;
    margin-bottom: 12px;
    flex-wrap: wrap;
    gap: 8px;
}

.recommendation-body {
    flex: 1;
}

.recommendation-title {
    font-weight: 600;
    color: #1E293B;
    margin-bottom: 8px;
}

.recommendation-description {
    color: #64748B;
    font-size: 14px;
    line-height: 1.6;
    margin-bottom: 12px;
}

.action-items {
    background: #F8FAFC;
    padding: 12px 16px;
    border-radius: 8px;
    margin-bottom: 12px;
}

.action-list {
    margin: 4px 0 0;
    padding-left: 20px;
}

.action-list li {
    font-size: 13px;
    color: #64748B;
    margin-bottom: 2px;
}

.recommendation-meta {
    display: flex;
    flex-wrap: wrap;
    gap: 12px;
    font-size: 13px;
    color: #94A3B8;
}

.recommendation-footer {
    display: flex;
    gap: 8px;
    margin-top: 16px;
    padding-top: 12px;
    border-top: 1px solid #F1F5F9;
    flex-wrap: wrap;
}

.empty-state i {
    display: block;
}

@media (max-width: 768px) {
    .stat-value {
        font-size: 20px;
    }
    
    .recommendation-footer {
        flex-wrap: wrap;
    }
}
</style>

<script>
$(document).ready(function() {
    // Filter functionality
    $('#filterType, #filterPriority, #filterStatus').on('change', applyFilters);
    $('#searchRecommendation').on('keyup', applyFilters);

    function applyFilters() {
        const type = $('#filterType').val();
        const priority = $('#filterPriority').val();
        const status = $('#filterStatus').val();
        const search = $('#searchRecommendation').val().toLowerCase();

        $('.recommendation-card').each(function() {
            const card = $(this);
            let show = true;

            if (type && card.data('type') !== type) show = false;
            if (priority && card.data('priority') !== priority) show = false;
            if (status && card.data('status') !== status) show = false;
            if (search) {
                const text = card.text().toLowerCase();
                if (!text.includes(search)) show = false;
            }

            card.toggle(show);
        });
    }

    // Implement recommendation
    $('.implement-rec').on('click', function() {
        const id = $(this).data('id');
        const card = $(this).closest('.recommendation-card');
        const csrfToken = $('input[name="csrf_token"]').val();

        if (!confirm('Mark this recommendation as implemented?')) return;

        $.ajax({
            url: '/api/ai/recommendations/' + id + '/implement',
            method: 'POST',
            data: { _csrf: csrfToken },
            success: function(response) {
                if (response.success) {
                    showToast('Recommendation marked as implemented', 'success');
                    location.reload();
                }
            }
        });
    });

    // Accept recommendation
    $('.accept-rec').on('click', function() {
        const id = $(this).data('id');
        const card = $(this).closest('.recommendation-card');
        const csrfToken = $('input[name="csrf_token"]').val();

        $.ajax({
            url: '/api/ai/recommendations/' + id + '/accept',
            method: 'POST',
            data: { _csrf: csrfToken },
            success: function(response) {
                if (response.success) {
                    showToast('Recommendation accepted', 'success');
                    location.reload();
                }
            }
        });
    });

    // Reject recommendation
    $('.reject-rec').on('click', function() {
        const id = $(this).data('id');
        const card = $(this).closest('.recommendation-card');
        const csrfToken = $('input[name="csrf_token"]').val();

        if (!confirm('Reject this recommendation?')) return;

        $.ajax({
            url: '/api/ai/recommendations/' + id + '/reject',
            method: 'POST',
            data: { _csrf: csrfToken },
            success: function(response) {
                if (response.success) {
                    showToast('Recommendation rejected', 'info');
                    location.reload();
                }
            }
        });
    });

    // Refresh button
    $('#refreshRecommendations').on('click', function() {
        const btn = $(this);
        btn.html('<span class="spinner-border spinner-border-sm me-2"></span> Refreshing...');
        btn.prop('disabled', true);

        setTimeout(function() {
            location.reload();
        }, 1500);
    });

    // Toast notification
    function showToast(message, type) {
        const toast = $(`
            <div class="toast-notification ${type}">
                <i class="fas fa-${type === 'success' ? 'check-circle' : type === 'error' ? 'exclamation-circle' : 'info-circle'}"></i>
                ${message}
            </div>
        `);
        $('body').append(toast);
        setTimeout(() => {
            toast.fadeOut(300, function() { $(this).remove(); });
        }, 3000);
    }
});
</script>