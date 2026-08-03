<?php
/**
 * Risk Report Page
 * 
 * @var string $title
 * @var array $report_data
 * @var array $risk_metrics
 * @var array $heatmap_data
 */
?>

<?php $page_title = 'Risk Report'; ?>
<?php $active_page = 'reports'; ?>

<div class="report-container">
    <!-- Report Header -->
    <div class="report-header mb-4">
        <div class="row">
            <div class="col-md-8">
                <h5><i class="fas fa-shield-alt me-2 text-primary"></i> Risk Report</h5>
                <p class="text-muted">Comprehensive risk analysis and mitigation status</p>
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
    
    <!-- Risk Summary -->
    <div class="row g-4 mb-4">
        <div class="col-xl-3 col-lg-6">
            <div class="summary-card">
                <div class="summary-label">Total Risks</div>
                <div class="summary-value"><?php echo $risk_metrics['total_risks'] ?? 156; ?></div>
                <div class="summary-sub"><?php echo $risk_metrics['new_risks'] ?? 12; ?> new this month</div>
            </div>
        </div>
        <div class="col-xl-3 col-lg-6">
            <div class="summary-card">
                <div class="summary-label">Critical Risks</div>
                <div class="summary-value text-danger"><?php echo $risk_metrics['critical'] ?? 12; ?></div>
                <div class="summary-sub"><?php echo $risk_metrics['high'] ?? 23; ?> high risks</div>
            </div>
        </div>
        <div class="col-xl-3 col-lg-6">
            <div class="summary-card">
                <div class="summary-label">Mitigation Rate</div>
                <div class="summary-value text-success"><?php echo $risk_metrics['mitigation_rate'] ?? 68; ?>%</div>
                <div class="summary-sub"><?php echo $risk_metrics['mitigated'] ?? 89; ?> risks mitigated</div>
            </div>
        </div>
        <div class="col-xl-3 col-lg-6">
            <div class="summary-card">
                <div class="summary-label">Avg Risk Score</div>
                <div class="summary-value"><?php echo $risk_metrics['avg_score'] ?? 65; ?>%</div>
                <div class="summary-sub"><?php echo $risk_metrics['trend'] ?? 'Stable'; ?> trend</div>
            </div>
        </div>
    </div>
    
    <!-- Risk Heatmap -->
    <div class="card mb-4">
        <div class="card-header">
            <i class="fas fa-fire me-2"></i> Risk Heatmap
        </div>
        <div class="card-body">
            <div class="heatmap-wrapper">
                <div class="heatmap-grid">
                    <?php for ($impact = 5; $impact >= 1; $impact--): ?>
                        <div class="heatmap-row">
                            <div class="heatmap-label"><?php echo ['Very High', 'High', 'Medium', 'Low', 'Very Low'][$impact - 1]; ?></div>
                            <?php for ($likelihood = 1; $likelihood <= 5; $likelihood++): ?>
                                <?php
                                $count = $heatmap_data[$impact][$likelihood] ?? 0;
                                $level = $impact * $likelihood;
                                $color = $count > 0 ? ($level >= 20 ? '#DC2626' : ($level >= 12 ? '#EF4444' : ($level >= 8 ? '#F59E0B' : ($level >= 4 ? '#3B82F6' : '#22C55E')))) : '#F1F5F9';
                                ?>
                                <div class="heatmap-cell <?php echo $count > 0 ? 'active' : ''; ?>" 
                                     style="background: <?php echo $color; ?>;">
                                    <?php if ($count > 0): ?>
                                        <span class="cell-count"><?php echo $count; ?></span>
                                    <?php endif; ?>
                                </div>
                            <?php endfor; ?>
                        </div>
                    <?php endfor; ?>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Risk Register Table -->
    <div class="card">
        <div class="card-header d-flex justify-content-between align-items-center">
            <span><i class="fas fa-list me-2"></i> Risk Register</span>
            <div>
                <span class="badge bg-danger me-1">Critical: <?php echo $risk_metrics['critical'] ?? 12; ?></span>
                <span class="badge bg-warning me-1">High: <?php echo $risk_metrics['high'] ?? 23; ?></span>
                <span class="badge bg-info me-1">Medium: <?php echo $risk_metrics['medium'] ?? 45; ?></span>
                <span class="badge bg-secondary">Low: <?php echo $risk_metrics['low'] ?? 76; ?></span>
            </div>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover enterprise-table mb-0">
                    <thead>
                        <tr>
                            <th>Risk Code</th>
                            <th>Title</th>
                            <th>Category</th>
                            <th>Level</th>
                            <th>Score</th>
                            <th>Status</th>
                            <th>Owner</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (!empty($report_data['risks'])): ?>
                            <?php foreach ($report_data['risks'] as $risk): ?>
                                <tr>
                                    <td><span class="risk-code"><?php echo htmlspecialchars($risk->risk_code); ?></span></td>
                                    <td><?php echo htmlspecialchars(substr($risk->title, 0, 35)) . '...'; ?></td>
                                    <td><?php echo htmlspecialchars($risk->category); ?></td>
                                    <td>
                                        <span class="risk-level <?php echo $risk->level; ?>">
                                            <?php echo ucfirst($risk->level); ?>
                                        </span>
                                    </td>
                                    <td><?php echo $risk->score; ?>%</td>
                                    <td>
                                        <span class="status-badge <?php echo $risk->status; ?>">
                                            <?php echo ucfirst($risk->status); ?>
                                        </span>
                                    </td>
                                    <td><?php echo htmlspecialchars($risk->owner_name); ?></td>
                                </tr>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="7" class="text-center py-4 text-muted">No risks available</td>
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

.heatmap-wrapper {
    padding: 10px 0;
    overflow-x: auto;
}

.heatmap-grid {
    display: grid;
    gap: 4px;
    min-width: 500px;
}

.heatmap-row {
    display: grid;
    grid-template-columns: 100px repeat(5, 1fr);
    gap: 4px;
    align-items: center;
}

.heatmap-label {
    font-size: 13px;
    color: #64748B;
    font-weight: 500;
    text-align: right;
    padding-right: 12px;
}

.heatmap-cell {
    aspect-ratio: 1;
    border-radius: 4px;
    display: flex;
    align-items: center;
    justify-content: center;
    min-height: 40px;
}

.heatmap-cell.active {
    box-shadow: 0 2px 4px rgba(0,0,0,0.1);
}

.heatmap-cell .cell-count {
    font-size: 14px;
    font-weight: 700;
    color: #fff;
    text-shadow: 0 1px 3px rgba(0,0,0,0.3);
}

.risk-code {
    font-family: 'Courier New', monospace;
    color: #2563EB;
    font-weight: 600;
    font-size: 13px;
}

.risk-level {
    padding: 4px 12px;
    border-radius: 12px;
    font-size: 12px;
    font-weight: 600;
}

.risk-level.critical { background: #FEE2E2; color: #DC2626; }
.risk-level.high { background: #FEF3C7; color: #F59E0B; }
.risk-level.medium { background: #DBEAFE; color: #3B82F6; }
.risk-level.low { background: #D1FAE5; color: #10B981; }

.status-badge {
    padding: 4px 12px;
    border-radius: 12px;
    font-size: 12px;
    font-weight: 500;
}

.status-badge.identified { background: #F1F5F9; color: #64748B; }
.status-badge.assessed { background: #DBEAFE; color: #3B82F6; }
.status-badge.mitigated { background: #FEF3C7; color: #F59E0B; }
.status-badge.monitored { background: #D1FAE5; color: #10B981; }
.status-badge.closed { background: #E2E8F0; color: #475569; }

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
        window.location.href = '<?php echo BASE_URL; ?>/reports/risk/download';
    });
    
    $('#exportExcel').on('click', function() {
        window.location.href = '<?php echo BASE_URL; ?>/reports/risk/export/excel';
    });
});
</script>