<?php
/**
 * Audit Report Page
 * 
 * @var string $title
 * @var array $report_data
 * @var array $audit_metrics
 * @var array $findings
 */
?>

<?php $page_title = 'Audit Report'; ?>
<?php $active_page = 'reports'; ?>

<div class="report-container">
    <!-- Report Header -->
    <div class="report-header mb-4">
        <div class="row">
            <div class="col-md-8">
                <h5><i class="fas fa-clipboard-list me-2 text-primary"></i> Audit Report</h5>
                <p class="text-muted">Comprehensive audit findings and recommendations</p>
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
    
    <!-- Audit Summary -->
    <div class="row g-4 mb-4">
        <div class="col-xl-3 col-lg-6">
            <div class="summary-card">
                <div class="summary-label">Total Audits</div>
                <div class="summary-value"><?php echo $audit_metrics['total_audits'] ?? 48; ?></div>
                <div class="summary-sub"><?php echo $audit_metrics['completed'] ?? 32; ?> completed</div>
            </div>
        </div>
        <div class="col-xl-3 col-lg-6">
            <div class="summary-card">
                <div class="summary-label">Open Findings</div>
                <div class="summary-value text-danger"><?php echo $audit_metrics['open_findings'] ?? 23; ?></div>
                <div class="summary-sub"><?php echo $audit_metrics['critical_findings'] ?? 5; ?> critical</div>
            </div>
        </div>
        <div class="col-xl-3 col-lg-6">
            <div class="summary-card">
                <div class="summary-label">Resolution Rate</div>
                <div class="summary-value text-success"><?php echo $audit_metrics['resolution_rate'] ?? 78; ?>%</div>
                <div class="summary-sub"><?php echo $audit_metrics['resolved'] ?? 42; ?> findings resolved</div>
            </div>
        </div>
        <div class="col-xl-3 col-lg-6">
            <div class="summary-card">
                <div class="summary-label">Avg Resolution Time</div>
                <div class="summary-value"><?php echo $audit_metrics['avg_resolution_time'] ?? 14; ?> days</div>
                <div class="summary-sub"><?php echo $audit_metrics['target_time'] ?? 30; ?> day target</div>
            </div>
        </div>
    </div>
    
    <!-- Findings by Severity -->
    <div class="row g-4 mb-4">
        <div class="col-xl-6">
            <div class="card">
                <div class="card-header">
                    <i class="fas fa-chart-pie me-2"></i> Findings by Severity
                </div>
                <div class="card-body">
                    <canvas id="severityChart" height="220"></canvas>
                </div>
            </div>
        </div>
        <div class="col-xl-6">
            <div class="card">
                <div class="card-header">
                    <i class="fas fa-chart-bar me-2"></i> Findings by Status
                </div>
                <div class="card-body">
                    <canvas id="statusChart" height="220"></canvas>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Findings Table -->
    <div class="card">
        <div class="card-header d-flex justify-content-between align-items-center">
            <span><i class="fas fa-search me-2"></i> Audit Findings</span>
            <div>
                <span class="badge bg-danger me-1">Critical: <?php echo $audit_metrics['critical_findings'] ?? 5; ?></span>
                <span class="badge bg-warning me-1">High: <?php echo $audit_metrics['high_findings'] ?? 12; ?></span>
                <span class="badge bg-info me-1">Medium: <?php echo $audit_metrics['medium_findings'] ?? 18; ?></span>
                <span class="badge bg-secondary">Low: <?php echo $audit_metrics['low_findings'] ?? 8; ?></span>
            </div>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover enterprise-table mb-0">
                    <thead>
                        <tr>
                            <th>Finding ID</th>
                            <th>Title</th>
                            <th>Audit</th>
                            <th>Severity</th>
                            <th>Status</th>
                            <th>Assigned To</th>
                            <th>Due Date</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (!empty($findings)): ?>
                            <?php foreach ($findings as $finding): ?>
                                <tr>
                                    <td><span class="finding-id">#<?php echo str_pad($finding->id, 4, '0', STR_PAD_LEFT); ?></span></td>
                                    <td><?php echo htmlspecialchars(substr($finding->title, 0, 40)) . '...'; ?></td>
                                    <td><?php echo htmlspecialchars($finding->audit_title); ?></td>
                                    <td>
                                        <span class="severity-badge <?php echo $finding->severity; ?>">
                                            <?php echo ucfirst($finding->severity); ?>
                                        </span>
                                    </td>
                                    <td>
                                        <span class="status-badge <?php echo $finding->status; ?>">
                                            <?php echo ucfirst(str_replace('_', ' ', $finding->status)); ?>
                                        </span>
                                    </td>
                                    <td><?php echo htmlspecialchars($finding->assignee_name ?? 'Unassigned'); ?></td>
                                    <td>
                                        <?php if ($finding->due_date): ?>
                                            <?php echo date('d M Y', strtotime($finding->due_date)); ?>
                                        <?php else: ?>
                                            <span class="text-muted">N/A</span>
                                        <?php endif; ?>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="7" class="text-center py-4 text-muted">No findings available</td>
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

.finding-id {
    font-family: 'Courier New', monospace;
    color: #2563EB;
    font-weight: 600;
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
</style>

<script>
$(document).ready(function() {
    // Severity Chart
    <?php if (isset($audit_metrics['severity_data'])): ?>
    const severityCtx = document.getElementById('severityChart').getContext('2d');
    new Chart(severityCtx, {
        type: 'doughnut',
        data: {
            labels: ['Critical', 'High', 'Medium', 'Low'],
            datasets: [{
                data: <?php echo json_encode($audit_metrics['severity_data'] ?? [5, 12, 18, 8]); ?>,
                backgroundColor: ['#DC2626', '#F59E0B', '#3B82F6', '#22C55E'],
                borderWidth: 0
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            cutout: '65%',
            plugins: {
                legend: {
                    position: 'bottom',
                    labels: {
                        usePointStyle: true,
                        padding: 15
                    }
                }
            }
        }
    });
    <?php endif; ?>
    
    // Status Chart
    <?php if (isset($audit_metrics['status_data'])): ?>
    const statusCtx = document.getElementById('statusChart').getContext('2d');
    new Chart(statusCtx, {
        type: 'bar',
        data: {
            labels: ['Open', 'In Progress', 'Resolved', 'Closed'],
            datasets: [{
                label: 'Findings',
                data: <?php echo json_encode($audit_metrics['status_data'] ?? [23, 12, 28, 14]); ?>,
                backgroundColor: ['#EF4444', '#F59E0B', '#22C55E', '#64748B'],
                borderRadius: 6
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: {
                    display: false
                }
            },
            scales: {
                y: {
                    beginAtZero: true,
                    grid: {
                        color: '#F1F5F9'
                    }
                },
                x: {
                    grid: {
                        display: false
                    }
                }
            }
        }
    });
    <?php endif; ?>
    
    $('#downloadReport').on('click', function() {
        window.location.href = '<?php echo BASE_URL; ?>/reports/audit/download';
    });
    
    $('#exportExcel').on('click', function() {
        window.location.href = '<?php echo BASE_URL; ?>/reports/audit/export/excel';
    });
});
</script>