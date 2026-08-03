<?php
/**
 * Reports Dashboard Page
 * 
 * @var string $title
 * @var array $recent_reports
 * @var array $report_types
 * @var array $stats
 */
?>

<?php $page_title = 'Reports Dashboard'; ?>
<?php $active_page = 'reports'; ?>

<div class="reports-container">
    <!-- Header -->
    <div class="row mb-4">
        <div class="col-md-8">
            <h5><i class="fas fa-file-alt me-2 text-primary"></i> Reports Dashboard</h5>
            <p class="text-muted">Generate and manage compliance, risk, and audit reports</p>
        </div>
        <div class="col-md-4 text-end">
            <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#generateReportModal">
                <i class="fas fa-plus me-2"></i> Generate Report
            </button>
            <button class="btn btn-outline-primary" id="refreshReports">
                <i class="fas fa-sync-alt"></i>
            </button>
        </div>
    </div>
    
    <!-- Stats Cards -->
    <div class="row g-4 mb-4">
        <div class="col-xl-3 col-lg-6">
            <div class="stat-card">
                <div class="stat-icon" style="background: rgba(37, 99, 235, 0.1); color: #2563EB;">
                    <i class="fas fa-file-pdf"></i>
                </div>
                <div class="stat-info">
                    <div class="stat-value"><?php echo $stats['total_reports'] ?? 156; ?></div>
                    <div class="stat-label">Total Reports</div>
                </div>
            </div>
        </div>
        <div class="col-xl-3 col-lg-6">
            <div class="stat-card">
                <div class="stat-icon" style="background: rgba(34, 197, 94, 0.1); color: #22C55E;">
                    <i class="fas fa-check-circle"></i>
                </div>
                <div class="stat-info">
                    <div class="stat-value"><?php echo $stats['generated_this_month'] ?? 28; ?></div>
                    <div class="stat-label">Generated This Month</div>
                </div>
            </div>
        </div>
        <div class="col-xl-3 col-lg-6">
            <div class="stat-card">
                <div class="stat-icon" style="background: rgba(245, 158, 11, 0.1); color: #F59E0B;">
                    <i class="fas fa-clock"></i>
                </div>
                <div class="stat-info">
                    <div class="stat-value"><?php echo $stats['scheduled'] ?? 12; ?></div>
                    <div class="stat-label">Scheduled Reports</div>
                </div>
            </div>
        </div>
        <div class="col-xl-3 col-lg-6">
            <div class="stat-card">
                <div class="stat-icon" style="background: rgba(239, 68, 68, 0.1); color: #EF4444;">
                    <i class="fas fa-download"></i>
                </div>
                <div class="stat-info">
                    <div class="stat-value"><?php echo $stats['downloads'] ?? 89; ?></div>
                    <div class="stat-label">Total Downloads</div>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Report Types -->
    <div class="row g-4 mb-4">
        <div class="col-xl-3 col-lg-6">
            <a href="<?php echo BASE_URL; ?>/reports/compliance" class="report-type-card">
                <div class="report-icon" style="background: #DBEAFE; color: #2563EB;">
                    <i class="fas fa-check-circle"></i>
                </div>
                <h6>Compliance Report</h6>
                <p class="text-muted small">Compliance status and framework analysis</p>
                <span class="badge bg-primary">Generate</span>
            </a>
        </div>
        <div class="col-xl-3 col-lg-6">
            <a href="<?php echo BASE_URL; ?>/reports/risk" class="report-type-card">
                <div class="report-icon" style="background: #FEF3C7; color: #F59E0B;">
                    <i class="fas fa-shield-alt"></i>
                </div>
                <h6>Risk Report</h6>
                <p class="text-muted small">Risk register and heatmap analysis</p>
                <span class="badge bg-primary">Generate</span>
            </a>
        </div>
        <div class="col-xl-3 col-lg-6">
            <a href="<?php echo BASE_URL; ?>/reports/audit" class="report-type-card">
                <div class="report-icon" style="background: #D1FAE5; color: #10B981;">
                    <i class="fas fa-clipboard-list"></i>
                </div>
                <h6>Audit Report</h6>
                <p class="text-muted small">Audit findings and recommendations</p>
                <span class="badge bg-primary">Generate</span>
            </a>
        </div>
        <div class="col-xl-3 col-lg-6">
            <a href="<?php echo BASE_URL; ?>/reports/executive" class="report-type-card">
                <div class="report-icon" style="background: #F1F5F9; color: #64748B;">
                    <i class="fas fa-chart-line"></i>
                </div>
                <h6>Executive Report</h6>
                <p class="text-muted small">High-level GRC overview for leadership</p>
                <span class="badge bg-primary">Generate</span>
            </a>
        </div>
    </div>
    
    <!-- Recent Reports -->
    <div class="card">
        <div class="card-header d-flex justify-content-between align-items-center">
            <span><i class="fas fa-history me-2"></i> Recent Reports</span>
            <button class="btn btn-sm btn-outline-primary" id="exportAllBtn">
                <i class="fas fa-download me-1"></i> Export All
            </button>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover enterprise-table mb-0">
                    <thead>
                        <tr>
                            <th>Report Name</th>
                            <th>Type</th>
                            <th>Format</th>
                            <th>Generated By</th>
                            <th>Date</th>
                            <th>Status</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (!empty($recent_reports)): ?>
                            <?php foreach ($recent_reports as $report): ?>
                                <tr>
                                    <td>
                                        <span class="report-name"><?php echo htmlspecialchars($report->name); ?></span>
                                    </td>
                                    <td>
                                        <span class="badge bg-secondary"><?php echo ucfirst($report->type); ?></span>
                                    </td>
                                    <td>
                                        <span class="badge bg-light text-dark">
                                            <?php echo strtoupper($report->format ?? 'PDF'); ?>
                                        </span>
                                    </td>
                                    <td><?php echo htmlspecialchars($report->generated_by_name ?? 'System'); ?></td>
                                    <td><?php echo date('d M Y h:i A', strtotime($report->generated_at)); ?></td>
                                    <td>
                                        <span class="status-badge <?php echo $report->status; ?>">
                                            <?php echo ucfirst($report->status); ?>
                                        </span>
                                    </td>
                                    <td>
                                        <div class="btn-group btn-group-sm">
                                            <a href="<?php echo BASE_URL; ?>/reports/<?php echo $report->id; ?>/download" 
                                               class="btn btn-outline-primary" title="Download">
                                                <i class="fas fa-download"></i>
                                            </a>
                                            <a href="<?php echo BASE_URL; ?>/reports/<?php echo $report->id; ?>" 
                                               class="btn btn-outline-secondary" title="View">
                                                <i class="fas fa-eye"></i>
                                            </a>
                                            <button class="btn btn-outline-danger delete-report" 
                                                    data-id="<?php echo $report->id; ?>" title="Delete">
                                                <i class="fas fa-trash"></i>
                                            </button>
                                        </div>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="7" class="text-center py-4 text-muted">
                                    <i class="fas fa-inbox fa-2x d-block mb-2"></i>
                                    No reports generated yet
                                </td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
    
    <!-- Pagination -->
    <?php if (isset($total_pages) && $total_pages > 1): ?>
    <nav class="mt-4">
        <ul class="pagination justify-content-center">
            <li class="page-item <?php echo $page <= 1 ? 'disabled' : ''; ?>">
                <a class="page-link" href="<?php echo BASE_URL; ?>/reports?page=<?php echo $page - 1; ?>">Previous</a>
            </li>
            <?php for ($i = 1; $i <= $total_pages; $i++): ?>
                <li class="page-item <?php echo $i == $page ? 'active' : ''; ?>">
                    <a class="page-link" href="<?php echo BASE_URL; ?>/reports?page=<?php echo $i; ?>"><?php echo $i; ?></a>
                </li>
            <?php endfor; ?>
            <li class="page-item <?php echo $page >= $total_pages ? 'disabled' : ''; ?>">
                <a class="page-link" href="<?php echo BASE_URL; ?>/reports?page=<?php echo $page + 1; ?>">Next</a>
            </li>
        </ul>
    </nav>
    <?php endif; ?>
</div>

<!-- Generate Report Modal -->
<div class="modal fade" id="generateReportModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title"><i class="fas fa-plus-circle me-2"></i> Generate Report</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form method="POST" action="<?php echo BASE_URL; ?>/reports/generate" id="generateReportForm">
                <div class="modal-body">
                    <input type="hidden" name="csrf_token" value="<?php echo $csrf_token ?? ''; ?>">
                    
                    <div class="row g-3">
                        <div class="col-12">
                            <label class="form-label">Report Type</label>
                            <select class="form-select" name="report_type" required>
                                <option value="">Select Report Type</option>
                                <option value="compliance">Compliance Report</option>
                                <option value="risk">Risk Report</option>
                                <option value="audit">Audit Report</option>
                                <option value="executive">Executive Report</option>
                                <option value="custom">Custom Report</option>
                            </select>
                        </div>
                        <div class="col-12">
                            <label class="form-label">Report Name</label>
                            <input type="text" class="form-control" name="name" placeholder="Enter report name" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Format</label>
                            <select class="form-select" name="format">
                                <option value="pdf">PDF</option>
                                <option value="xlsx">Excel (XLSX)</option>
                                <option value="csv">CSV</option>
                                <option value="json">JSON</option>
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Date Range</label>
                            <select class="form-select" name="date_range">
                                <option value="this_month">This Month</option>
                                <option value="last_month">Last Month</option>
                                <option value="this_quarter">This Quarter</option>
                                <option value="this_year">This Year</option>
                                <option value="custom">Custom Range</option>
                            </select>
                        </div>
                        <div class="col-md-6 d-none" id="customDateRange">
                            <label class="form-label">From Date</label>
                            <input type="date" class="form-control" name="date_from">
                        </div>
                        <div class="col-md-6 d-none" id="customDateRangeTo">
                            <label class="form-label">To Date</label>
                            <input type="date" class="form-control" name="date_to">
                        </div>
                        <div class="col-12">
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" name="include_charts" id="includeCharts" checked>
                                <label class="form-check-label" for="includeCharts">
                                    Include Charts and Visualizations
                                </label>
                            </div>
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" name="schedule_report" id="scheduleReport">
                                <label class="form-check-label" for="scheduleReport">
                                    Schedule Report (Recurring)
                                </label>
                            </div>
                        </div>
                        <div class="col-12 d-none" id="scheduleOptions">
                            <label class="form-label">Schedule Frequency</label>
                            <select class="form-select" name="schedule_frequency">
                                <option value="daily">Daily</option>
                                <option value="weekly">Weekly</option>
                                <option value="monthly">Monthly</option>
                                <option value="quarterly">Quarterly</option>
                            </select>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary" id="generateBtn">
                        <i class="fas fa-file-alt me-2"></i> Generate Report
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<style>
.reports-container {
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
    transition: all 0.3s;
    height: 100%;
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

.report-type-card {
    background: #fff;
    padding: 24px;
    border-radius: 12px;
    box-shadow: 0 1px 3px rgba(0,0,0,0.06);
    text-align: center;
    transition: all 0.3s;
    text-decoration: none;
    display: block;
    height: 100%;
}

.report-type-card:hover {
    box-shadow: 0 4px 12px rgba(0,0,0,0.1);
    transform: translateY(-4px);
    text-decoration: none;
}

.report-icon {
    width: 56px;
    height: 56px;
    border-radius: 12px;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 24px;
    margin: 0 auto 12px;
}

.report-type-card h6 {
    color: #1E293B;
    margin: 0 0 4px;
}

.report-type-card p {
    margin: 0 0 12px;
}

.report-name {
    font-weight: 500;
    color: #1E293B;
}

.status-badge {
    padding: 4px 12px;
    border-radius: 12px;
    font-size: 12px;
    font-weight: 500;
}

.status-badge.generated { background: #D1FAE5; color: #10B981; }
.status-badge.pending { background: #FEF3C7; color: #F59E0B; }
.status-badge.failed { background: #FEE2E2; color: #DC2626; }

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
    .stat-card {
        padding: 16px;
    }
    
    .stat-value {
        font-size: 20px;
    }
}
</style>

<script>
$(document).ready(function() {
    // Date range toggle
    $('select[name="date_range"]').on('change', function() {
        if ($(this).val() === 'custom') {
            $('#customDateRange, #customDateRangeTo').removeClass('d-none');
        } else {
            $('#customDateRange, #customDateRangeTo').addClass('d-none');
        }
    });
    
    // Schedule toggle
    $('#scheduleReport').on('change', function() {
        if ($(this).is(':checked')) {
            $('#scheduleOptions').removeClass('d-none');
        } else {
            $('#scheduleOptions').addClass('d-none');
        }
    });
    
    // Generate report form submission
    $('#generateReportForm').on('submit', function(e) {
        const btn = $('#generateBtn');
        btn.html('<i class="fas fa-spinner fa-spin me-2"></i> Generating...');
        btn.prop('disabled', true);
        
        // Simulate generation
        setTimeout(function() {
            btn.html('<i class="fas fa-file-alt me-2"></i> Generate Report');
            btn.prop('disabled', false);
            $('#generateReportModal').modal('hide');
            location.reload();
        }, 2000);
    });
    
    // Delete report
    $('.delete-report').on('click', function() {
        const id = $(this).data('id');
        if (confirm('Are you sure you want to delete this report?')) {
            $.ajax({
                url: '<?php echo BASE_URL; ?>/api/reports/' + id,
                method: 'DELETE',
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