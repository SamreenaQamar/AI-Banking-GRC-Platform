<?php
/**
 * Compliance Status Page
 * 
 * @var string $title
 * @var array $status_data
 * @var array $department_data
 * @var array $framework_data
 */
?>

<?php $page_title = 'Compliance Status'; ?>
<?php $active_page = 'compliance'; ?>

<div class="compliance-status-container">
    <!-- Status Overview -->
    <div class="row g-4 mb-4">
        <div class="col-xl-3 col-lg-6">
            <div class="status-card compliant">
                <div class="status-card-header">
                    <i class="fas fa-check-circle"></i>
                    <span>Compliant</span>
                </div>
                <div class="status-value"><?php echo $status_data['compliant'] ?? 68; ?>%</div>
                <div class="status-count"><?php echo $status_data['compliant_count'] ?? 124; ?> controls</div>
            </div>
        </div>
        <div class="col-xl-3 col-lg-6">
            <div class="status-card partial">
                <div class="status-card-header">
                    <i class="fas fa-exclamation-circle"></i>
                    <span>Partially Compliant</span>
                </div>
                <div class="status-value"><?php echo $status_data['partial'] ?? 22; ?>%</div>
                <div class="status-count"><?php echo $status_data['partial_count'] ?? 46; ?> controls</div>
            </div>
        </div>
        <div class="col-xl-3 col-lg-6">
            <div class="status-card non-compliant">
                <div class="status-card-header">
                    <i class="fas fa-times-circle"></i>
                    <span>Non-Compliant</span>
                </div>
                <div class="status-value"><?php echo $status_data['non_compliant'] ?? 10; ?>%</div>
                <div class="status-count"><?php echo $status_data['non_compliant_count'] ?? 18; ?> controls</div>
            </div>
        </div>
        <div class="col-xl-3 col-lg-6">
            <div class="status-card total">
                <div class="status-card-header">
                    <i class="fas fa-clipboard-list"></i>
                    <span>Total Controls</span>
                </div>
                <div class="status-value"><?php echo $status_data['total'] ?? 188; ?></div>
                <div class="status-count">Across all frameworks</div>
            </div>
        </div>
    </div>
    
    <!-- Framework Status -->
    <div class="card mb-4">
        <div class="card-header">
            <i class="fas fa-layer-group me-2"></i> Framework Compliance
        </div>
        <div class="card-body">
            <?php if (!empty($framework_data)): ?>
                <?php foreach ($framework_data as $framework): ?>
                    <div class="framework-status mb-4">
                        <div class="d-flex justify-content-between align-items-center mb-2">
                            <div>
                                <h6 class="mb-0"><?php echo htmlspecialchars($framework->name); ?></h6>
                                <small class="text-muted">Version <?php echo htmlspecialchars($framework->version); ?></small>
                            </div>
                            <div class="framework-score">
                                <span class="badge bg-<?php echo $framework->score >= 80 ? 'success' : ($framework->score >= 60 ? 'warning' : 'danger'); ?>">
                                    <?php echo $framework->score; ?>%
                                </span>
                            </div>
                        </div>
                        <div class="progress" style="height: 8px;">
                            <div class="progress-bar" style="width: <?php echo $framework->score; ?>%; 
                                 background: <?php echo $framework->score >= 80 ? '#22C55E' : ($framework->score >= 60 ? '#F59E0B' : '#EF4444'); ?>;">
                            </div>
                        </div>
                        <div class="framework-details mt-2">
                            <span class="text-success"><i class="fas fa-check-circle"></i> <?php echo $framework->compliant; ?></span>
                            <span class="text-warning ms-3"><i class="fas fa-exclamation-circle"></i> <?php echo $framework->partial; ?></span>
                            <span class="text-danger ms-3"><i class="fas fa-times-circle"></i> <?php echo $framework->non_compliant; ?></span>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php else: ?>
                <div class="text-center text-muted py-4">
                    <i class="fas fa-inbox fa-2x mb-2"></i>
                    <p>No framework data available</p>
                </div>
            <?php endif; ?>
        </div>
    </div>
    
    <!-- Department Status -->
    <div class="card">
        <div class="card-header">
            <i class="fas fa-building me-2"></i> Department Status
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover enterprise-table mb-0">
                    <thead>
                        <tr>
                            <th>Department</th>
                            <th>Compliance Score</th>
                            <th>Compliant</th>
                            <th>Partial</th>
                            <th>Non-Compliant</th>
                            <th>Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (!empty($department_data)): ?>
                            <?php foreach ($department_data as $dept): ?>
                                <tr>
                                    <td><strong><?php echo htmlspecialchars($dept->name); ?></strong></td>
                                    <td>
                                        <span class="department-score"><?php echo $dept->score; ?>%</span>
                                    </td>
                                    <td><span class="text-success"><?php echo $dept->compliant; ?></span></td>
                                    <td><span class="text-warning"><?php echo $dept->partial; ?></span></td>
                                    <td><span class="text-danger"><?php echo $dept->non_compliant; ?></span></td>
                                    <td>
                                        <span class="badge bg-<?php echo $dept->score >= 80 ? 'success' : ($dept->score >= 60 ? 'warning' : 'danger'); ?>">
                                            <?php echo $dept->score >= 80 ? 'Good' : ($dept->score >= 60 ? 'Needs Improvement' : 'Critical'); ?>
                                        </span>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="6" class="text-center py-4 text-muted">
                                    <i class="fas fa-inbox fa-2x d-block mb-2"></i>
                                    No department data available
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
.compliance-status-container {
    padding: 0;
}

.status-card {
    padding: 20px;
    border-radius: 12px;
    background: #fff;
    box-shadow: 0 1px 3px rgba(0,0,0,0.06);
    height: 100%;
    transition: all 0.3s;
    border-left: 4px solid transparent;
}

.status-card:hover {
    box-shadow: 0 4px 12px rgba(0,0,0,0.1);
    transform: translateY(-2px);
}

.status-card.compliant { border-left-color: #22C55E; }
.status-card.partial { border-left-color: #F59E0B; }
.status-card.non-compliant { border-left-color: #EF4444; }
.status-card.total { border-left-color: #2563EB; }

.status-card-header {
    display: flex;
    align-items: center;
    gap: 8px;
    font-size: 14px;
    color: #64748B;
    margin-bottom: 4px;
}

.status-card-header i {
    font-size: 16px;
}

.status-card.compliant .status-card-header i { color: #22C55E; }
.status-card.partial .status-card-header i { color: #F59E0B; }
.status-card.non-compliant .status-card-header i { color: #EF4444; }
.status-card.total .status-card-header i { color: #2563EB; }

.status-value {
    font-size: 32px;
    font-weight: 700;
    color: #1E293B;
}

.status-count {
    font-size: 13px;
    color: #94A3B8;
}

.framework-status {
    padding: 12px 0;
    border-bottom: 1px solid #F1F5F9;
}

.framework-status:last-child {
    border-bottom: none;
}

.framework-details {
    font-size: 13px;
}

.department-score {
    font-weight: 600;
    color: #1E293B;
}

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
    .status-value {
        font-size: 24px;
    }
}
</style>