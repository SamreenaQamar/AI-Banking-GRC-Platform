<?php
/**
 * Compliance Recommendations Page
 * 
 * @var string $title
 * @var array $recommendations
 * @var array $ai_insights
 */
?>

<?php $page_title = 'Compliance Recommendations'; ?>
<?php $active_page = 'compliance'; ?>

<div class="recommendations-container">
    <!-- AI Insights Banner -->
    <div class="ai-banner mb-4">
        <div class="ai-banner-content">
            <div class="ai-icon">
                <i class="fas fa-robot"></i>
            </div>
            <div class="ai-text">
                <h6><i class="fas fa-brain me-2"></i> AI-Powered Insights</h6>
                <p class="mb-0">Based on your compliance data, here are <?php echo count($recommendations ?? []); ?> recommended actions to improve your compliance posture.</p>
            </div>
            <div class="ai-actions">
                <button class="btn btn-light btn-sm" id="refreshRecommendations">
                    <i class="fas fa-sync-alt me-1"></i> Refresh
                </button>
            </div>
        </div>
    </div>
    
    <!-- Recommendations Grid -->
    <div class="row g-4">
        <?php if (!empty($recommendations)): ?>
            <?php foreach ($recommendations as $rec): ?>
                <div class="col-xl-6">
                    <div class="recommendation-card priority-<?php echo $rec->priority; ?>">
                        <div class="recommendation-header">
                            <div class="recommendation-type">
                                <span class="badge bg-<?php echo $rec->priority === 'critical' ? 'danger' : ($rec->priority === 'high' ? 'warning' : ($rec->priority === 'medium' ? 'info' : 'secondary')); ?>">
                                    <?php echo ucfirst($rec->priority); ?> Priority
                                </span>
                                <span class="badge bg-light text-dark ms-2">
                                    <i class="far fa-clock me-1"></i>
                                    <?php echo date('d M Y', strtotime($rec->created_at)); ?>
                                </span>
                            </div>
                            <div class="recommendation-status">
                                <span class="badge bg-<?php echo $rec->status === 'implemented' ? 'success' : 'warning'; ?>">
                                    <?php echo ucfirst($rec->status ?? 'Pending'); ?>
                                </span>
                            </div>
                        </div>
                        
                        <div class="recommendation-body">
                            <h6 class="recommendation-title">
                                <?php echo htmlspecialchars($rec->title); ?>
                            </h6>
                            <p class="recommendation-description">
                                <?php echo htmlspecialchars($rec->description); ?>
                            </p>
                            
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
                                <span>
                                    <i class="fas fa-tag me-1"></i>
                                    <?php echo htmlspecialchars($rec->category ?? 'General'); ?>
                                </span>
                                <?php if ($rec->estimated_effort): ?>
                                    <span>
                                        <i class="fas fa-clock me-1"></i>
                                        Effort: <?php echo htmlspecialchars($rec->estimated_effort); ?>
                                    </span>
                                <?php endif; ?>
                            </div>
                        </div>
                        
                        <div class="recommendation-footer">
                            <button class="btn btn-primary btn-sm implement-rec" data-id="<?php echo $rec->id; ?>">
                                <i class="fas fa-check me-1"></i> Implement
                            </button>
                            <button class="btn btn-outline-secondary btn-sm dismiss-rec" data-id="<?php echo $rec->id; ?>">
                                <i class="fas fa-times me-1"></i> Dismiss
                            </button>
                            <button class="btn btn-outline-info btn-sm view-details" data-id="<?php echo $rec->id; ?>">
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
</div>

<style>
.recommendations-container {
    padding: 0;
}

.ai-banner {
    background: linear-gradient(135deg, #0B3D91, #2563EB);
    border-radius: 12px;
    padding: 20px 24px;
    color: #fff;
}

.ai-banner-content {
    display: flex;
    align-items: center;
    gap: 20px;
    flex-wrap: wrap;
}

.ai-icon {
    width: 48px;
    height: 48px;
    border-radius: 50%;
    background: rgba(255,255,255,0.15);
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 24px;
}

.ai-text {
    flex: 1;
}

.ai-text h6 {
    margin: 0;
    font-weight: 600;
}

.ai-text p {
    opacity: 0.85;
    font-size: 14px;
}

.ai-actions .btn-light {
    color: #0B3D91;
    font-weight: 500;
}

.recommendation-card {
    background: #fff;
    border-radius: 12px;
    padding: 20px;
    box-shadow: 0 1px 3px rgba(0,0,0,0.06);
    transition: all 0.3s;
    border-left: 4px solid #E2E8F0;
    height: 100%;
    display: flex;
    flex-direction: column;
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

.recommendation-type .badge {
    font-size: 11px;
    padding: 4px 10px;
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
    gap: 16px;
    font-size: 13px;
    color: #94A3B8;
    flex-wrap: wrap;
}

.recommendation-footer {
    display: flex;
    gap: 8px;
    margin-top: 16px;
    padding-top: 12px;
    border-top: 1px solid #F1F5F9;
}

.empty-state i {
    display: block;
}

@media (max-width: 768px) {
    .ai-banner-content {
        flex-direction: column;
        text-align: center;
    }
    
    .recommendation-header {
        flex-direction: column;
    }
    
    .recommendation-footer {
        flex-wrap: wrap;
    }
}
</style>

<script>
$(document).ready(function() {
    // Implement recommendation
    $('.implement-rec').on('click', function() {
        const id = $(this).data('id');
        if (confirm('Mark this recommendation as implemented?')) {
            $.ajax({
                url: '<?php echo BASE_URL; ?>/api/compliance/recommendation/' + id + '/implement',
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
    
    // Dismiss recommendation
    $('.dismiss-rec').on('click', function() {
        const id = $(this).data('id');
        if (confirm('Dismiss this recommendation?')) {
            $.ajax({
                url: '<?php echo BASE_URL; ?>/api/compliance/recommendation/' + id + '/dismiss',
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
    
    // Refresh recommendations
    $('#refreshRecommendations').on('click', function() {
        const btn = $(this);
        btn.html('<i class="fas fa-spinner fa-spin me-1"></i> Refreshing...');
        setTimeout(function() {
            location.reload();
        }, 1000);
    });
});
</script>