<?php
/**
 * Gap Analysis Page
 * 
 * @var string $title
 * @var array $gaps
 * @var array $summary
 */
?>

<?php $page_title = 'Gap Analysis'; ?>
<?php $active_page = 'compliance'; ?>

<div class="gap-analysis-container">
    <!-- Summary Cards -->
    <div class="row g-4 mb-4">
        <div class="col-xl-3 col-lg-6">
            <div class="gap-card">
                <div class="gap-card-header">
                    <i class="fas fa-exclamation-triangle text-danger"></i>
                    <span>Critical Gaps</span>
                </div>
                <div class="gap-value"><?php echo $summary['critical'] ?? 3; ?></div>
                <div class="gap-change">Requires immediate attention</div>
            </div>
        </div>
        <div class="col-xl-3 col-lg-6">
            <div class="gap-card">
                <div class="gap-card-header">
                    <i class="fas fa-exclamation-circle text-warning"></i>
                    <span>High Gaps</span>
                </div>
                <div class="gap-value"><?php echo $summary['high'] ?? 5; ?></div>
                <div class="gap-change">High priority remediation</div>
            </div>
        </div>
        <div class="col-xl-3 col-lg-6">
            <div class="gap-card">
                <div class="gap-card-header">
                    <i class="fas fa-info-circle text-primary"></i>
                    <span>Medium Gaps</span>
                </div>
                <div class="gap-value"><?php echo $summary['medium'] ?? 8; ?></div>
                <div class="gap-change">Plan for remediation</div>
            </div>
        </div>
        <div class="col-xl-3 col-lg-6">
            <div class="gap-card">
                <div class="gap-card-header">
                    <i class="fas fa-check-circle text-success"></i>
                    <span>Closed Gaps</span>
                </div>
                <div class="gap-value"><?php echo $summary['closed'] ?? 12; ?></div>
                <div class="gap-change">Successfully remediated</div>
            </div>
        </div>
    </div>
    
    <!-- Gap List -->
    <div class="card">
        <div class="card-header d-flex justify-content-between align-items-center">
            <span><i class="fas fa-list me-2"></i> Identified Gaps</span>
            <div>
                <button class="btn btn-sm btn-outline-primary" id="exportGapReport">
                    <i class="fas fa-download me-1"></i> Export Report
                </button>
            </div>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover enterprise-table mb-0">
                    <thead>
                        <tr>
                            <th>Gap ID</th>
                            <th>Description</th>
                            <th>Framework</th>
                            <th>Severity</th>
                            <th>Status</th>
                            <th>Assigned To</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (!empty($gaps)): ?>
                            <?php foreach ($gaps as $gap): ?>
                                <tr>
                                    <td><span class="gap-id">#<?php echo str_pad($gap->id, 4, '0', STR_PAD_LEFT); ?></span></td>
                                    <td><?php echo htmlspecialchars(substr($gap->description, 0, 50)) . '...'; ?></td>
                                    <td><?php echo htmlspecialchars($gap->framework); ?></td>
                                    <td>
                                        <span class="severity-badge <?php echo $gap->severity; ?>">
                                            <?php echo ucfirst($gap->severity); ?>
                                        </span>
                                    </td>
                                    <td>
                                        <span class="status-badge <?php echo $gap->status; ?>">
                                            <?php echo ucfirst($gap->status); ?>
                                        </span>
                                    </td>
                                    <td><?php echo htmlspecialchars($gap->assigned_to ?? 'Unassigned'); ?></td>
                                    <td>
                                        <button class="btn btn-sm btn-outline-primary view-gap" data-id="<?php echo $gap->id; ?>">
                                            <i class="fas fa-eye"></i>
                                        </button>
                                        <button class="btn btn-sm btn-outline-success remediate-btn" data-id="<?php echo $gap->id; ?>">
                                            <i class="fas fa-check"></i>
                                        </button>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="7" class="text-center py-4 text-muted">
                                    <i class="fas fa-check-circle fa-2x text-success d-block mb-2"></i>
                                    No gaps identified. All controls are compliant!
                                </td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<style>
.gap-analysis-container {
    padding: 0;
}

.gap-card {
    background: #fff;
    padding: 20px;
    border-radius: 12px;
    box-shadow: 0 1px 3px rgba(0,0,0,0.06);
    height: 100%;
    transition: all 0.3s;
}

.gap-card:hover {
    box-shadow: 0 4px 12px rgba(0,0,0,0.1);
    transform: translateY(-2px);
}

.gap-card-header {
    display: flex;
    align-items: center;
    gap: 8px;
    font-size: 14px;
    color: #64748B;
    margin-bottom: 4px;
}

.gap-value {
    font-size: 32px;
    font-weight: 700;
    color: #1E293B;
}

.gap-change {
    font-size: 13px;
    color: #94A3B8;
}

.gap-id {
    font-family: 'Courier New', monospace;
    font-weight: 600;
    color: #2563EB;
}

.severity-badge {
    padding: 4px 12px;
    border-radius: 12px;
    font-size: 12px;
    font-weight: 500;
}

.severity-badge.critical { background: #FEE2E2; color: #DC2626; }
.severity-badge.high { background: #FEF3C7; color: #F59E0B; }
.severity-badge.medium { background: #DBEAFE; color: #3B82F6; }
.severity-badge.low { background: #D1FAE5; color: #10B981; }

.status-badge {
    padding: 4px 12px;
    border-radius: 12px;
    font-size: 12px;
    font-weight: 500;
}

.status-badge.open { background: #FEE2E2; color: #DC2626; }
.status-badge.in_progress { background: #FEF3C7; color: #F59E0B; }
.status-badge.resolved { background: #D1FAE5; color: #10B981; }
.status-badge.closed { background: #F1F5F9; color: #64748B; }

.enterprise-table thead th {
    font-weight: 600;
    font-size: 12px;
    color: #64748B;
    text-transform: uppercase;
    letter-spacing: 0.5px;
    padding: 12px 16px;
    background: #F8FAFC;
}

.enterprise-table tbody td {
    padding: 12px 16px;
    vertical-align: middle;
    border-bottom: 1px solid #F1F5F9;
}

@media (max-width: 768px) {
    .gap-value {
        font-size: 24px;
    }
}
</style>

<script>
$(document).ready(function() {
    // View gap details
    $('.view-gap').on('click', function() {
        const gapId = $(this).data('id');
        // Open gap details modal
        window.location.href = '<?php echo BASE_URL; ?>/compliance/gap/' + gapId;
    });
    
    // Remediate gap
    $('.remediate-btn').on('click', function() {
        const gapId = $(this).data('id');
        if (confirm('Mark this gap as remediated?')) {
            $.ajax({
                url: '<?php echo BASE_URL; ?>/api/compliance/gap/' + gapId + '/remediate',
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
    
    // Export report
    $('#exportGapReport').on('click', function() {
        window.location.href = '<?php echo BASE_URL; ?>/compliance/gap/export';
    });
});
</script>