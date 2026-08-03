<?php
/**
 * Compliance Report Page
 * 
 * @var string $title
 * @var array $report_data
 * @var array $framework_data
 * @var array $compliance_metrics
 */
?>

<?php $page_title = 'Compliance Report'; ?>
<?php $active_page = 'reports'; ?>

<div class="report-container">
    <!-- Report Header -->
    <div class="report-header mb-4">
        <div class="row">
            <div class="col-md-8">
                <h5><i class="fas fa-check-circle me-2 text-primary"></i> Compliance Report</h5>
                <p class="text-muted">Comprehensive compliance status across all frameworks</p>
            </div>
            <div class="col-md-4 text-end">
                <button class="btn btn-primary" id="downloadReport">
                    <i class="fas fa-download me-2"></i> Download PDF
                </button>
                <button class="btn btn-outline-primary" id="exportExcel">
                    <i class="fas fa-file-excel me-2"></i> Export Excel
                </button>
            </div>
        </div>
    </div>
    
    <!-- Report Summary -->
    <div class="row g-4 mb-4">
        <div class="col-xl-3 col-lg-6">
            <div class="summary-card">
                <div class="summary-label">Overall Compliance</div>
                <div class="summary-value <?php echo ($compliance_metrics['overall'] ?? 68) >= 80 ? 'text-success' : (($compliance_metrics['overall'] ?? 68) >= 60 ? 'text-warning' : 'text-danger'); ?>">
                    <?php echo $compliance_metrics['overall'] ?? 68; ?>%
                </div>
                <div class="progress mt-2" style="height: 6px;">
                    <div class="progress-bar" style="width: <?php echo $compliance_metrics['overall'] ?? 68; ?>%; 
                         background: <?php echo ($compliance_metrics['overall'] ?? 68) >= 80 ? '#22C55E' : (($compliance_metrics['overall'] ?? 68) >= 60 ? '#F59E0B' : '#EF4444'); ?>;">
                    </div>
                </div>
            </div>
        </div>
        <div class="col-xl-3 col-lg-6">
            <div class="summary-card">
                <div class="summary-label">Compliant Controls</div>
                <div class="summary-value text-success"><?php echo $compliance_metrics['compliant'] ?? 124; ?></div>
                <div class="summary-sub">of <?php echo $compliance_metrics['total_controls'] ?? 188; ?> controls</div>
            </div>
        </div>
        <div class="col-xl-3 col-lg-6">
            <div class="summary-card">
                <div class="summary-label">Non-Compliant</div>
                <div class="summary-value text-danger"><?php echo $compliance_metrics['non_compliant'] ?? 18; ?></div>
                <div class="summary-sub"><?php echo $compliance_metrics['critical_findings'] ?? 5; ?> critical</div>
            </div>
        </div>
        <div class="col-xl-3 col-lg-6">
            <div class="summary-card">
                <div class="summary-label">Frameworks</div>
                <div class="summary-value text-primary"><?php echo $compliance_metrics['frameworks'] ?? 4; ?></div>
                <div class="summary-sub">Active frameworks</div>
            </div>
        </div>
    </div>
    
    <!-- Framework Status -->
    <div class="card mb-4">
        <div class="card-header">
            <i class="fas fa-layer-group me-2"></i> Framework Compliance Status
        </div>
        <div class="card-body">
            <?php if (!empty($framework_data)): ?>
                <?php foreach ($framework_data as $framework): ?>
                    <div class="framework-item mb-3">
                        <div class="d-flex justify-content-between align-items-center mb-1">
                            <div>
                                <strong><?php echo htmlspecialchars($framework->name); ?></strong>
                                <small class="text-muted ms-2">v<?php echo $framework->version; ?></small>
                            </div>
                            <span class="framework-score badge bg-<?php echo $framework->score >= 80 ? 'success' : ($framework->score >= 60 ? 'warning' : 'danger'); ?>">
                                <?php echo $framework->score; ?>%
                            </span>
                        </div>
                        <div class="progress" style="height: 8px;">
                            <div class="progress-bar" style="width: <?php echo $framework->score; ?>%; 
                                 background: <?php echo $framework->score >= 80 ? '#22C55E' : ($framework->score >= 60 ? '#F59E0B' : '#EF4444'); ?>;">
                            </div>
                        </div>
                        <div class="framework-details mt-1">
                            <span class="text-success"><i class="fas fa-check-circle"></i> <?php echo $framework->compliant; ?></span>
                            <span class="text-warning ms-3"><i class="fas fa-exclamation-circle"></i> <?php echo $framework->partial; ?></span>
                            <span class="text-danger ms-3"><i class="fas fa-times-circle"></i> <?php echo $framework->non_compliant; ?></span>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php else: ?>
                <div class="text-center text-muted py-3">No framework data available</div>
            <?php endif; ?>
        </div>
    </div>
    
    <!-- Controls Table -->
    <div class="card">
        <div class="card-header d-flex justify-content-between align-items-center">
            <span><i class="fas fa-list-check me-2"></i> Control Status</span>
            <div>
                <span class="badge bg-success me-1">Compliant: <?php echo $compliance_metrics['compliant'] ?? 124; ?></span>
                <span class="badge bg-warning me-1">Partial: <?php echo $compliance_metrics['partial'] ?? 46; ?></span>
                <span class="badge bg-danger">Non-Compliant: <?php echo $compliance_metrics['non_compliant'] ?? 18; ?></span>
            </div>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover enterprise-table mb-0">
                    <thead>
                        <tr>
                            <th>Control ID</th>
                            <th>Description</th>
                            <th>Framework</th>
                            <th>Status</th>
                            <th>Evidence</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (!empty($report_data['controls'])): ?>
                            <?php foreach ($report_data['controls'] as $control): ?>
                                <tr>
                                    <td><span class="control-id"><?php echo htmlspecialchars($control->id); ?></span></td>
                                    <td><?php echo htmlspecialchars(substr($control->description, 0, 50)) . '...'; ?></td>
                                    <td><?php echo htmlspecialchars($control->framework); ?></td>
                                    <td>
                                        <span class="status-badge <?php echo $control->status; ?>">
                                            <?php echo ucfirst($control->status); ?>
                                        </span>
                                    </td>
                                    <td>
                                        <?php if ($control->evidence_count > 0): ?>
                                            <span class="text-success"><i class="fas fa-check-circle"></i> <?php echo $control->evidence_count; ?></span>
                                        <?php else: ?>
                                            <span class="text-muted"><i class="fas fa-times-circle"></i> None</span>
                                        <?php endif; ?>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="5" class="text-center py-4 text-muted">No control data available</td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<style>
.report-container {
    padding: 0;
}

.summary-card {
    background: #fff;
    padding: 20px;
    border-radius: 12px;
    box-shadow: 0 1px 3px rgba(0,0,0,0.06);
    height: 100%;
}

.summary-label {
    font-size: 14px;
    color: #64748B;
    font-weight: 500;
}

.summary-value {
    font-size: 28px;
    font-weight: 700;
}

.summary-sub {
    font-size: 13px;
    color: #94A3B8;
}

.framework-item {
    padding: 12px 0;
    border-bottom: 1px solid #F1F5F9;
}

.framework-item:last-child {
    border-bottom: none;
}

.framework-score {
    font-size: 14px;
    padding: 4px 12px;
}

.framework-details {
    font-size: 13px;
}

.control-id {
    font-family: 'Courier New', monospace;
    color: #2563EB;
    font-weight: 600;
}

.status-badge {
    padding: 4px 12px;
    border-radius: 12px;
    font-size: 12px;
    font-weight: 500;
}

.status-badge.compliant { background: #D1FAE5; color: #10B981; }
.status-badge.partial { background: #FEF3C7; color: #F59E0B; }
.status-badge.non_compliant { background: #FEE2E2; color: #EF4444; }

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
</style>

<script>
$(document).ready(function() {
    $('#downloadReport').on('click', function() {
        window.location.href = '<?php echo BASE_URL; ?>/reports/compliance/download';
    });
    
    $('#exportExcel').on('click', function() {
        window.location.href = '<?php echo BASE_URL; ?>/reports/compliance/export/excel';
    });
});
</script>