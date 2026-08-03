<?php
/**
 * Audit Dashboard Page
 * 
 * @var string $title
 * @var array $stats
 * @var array $chart_data
 * @var array $recent_findings
 * @var array $upcoming_audits
 */
?>

<?php $page_title = 'Audit Dashboard'; ?>
<?php $active_page = 'audit'; ?>

<div class="audit-dashboard-container">
    <!-- Welcome Banner -->
    <div class="welcome-banner mb-4">
        <div class="welcome-content">
            <h5><i class="fas fa-clipboard-check me-2"></i> Audit Management Dashboard</h5>
            <p class="mb-0">Overview of audit activities, findings, and compliance status</p>
        </div>
        <div class="welcome-actions">
            <button class="btn btn-light btn-sm" data-bs-toggle="modal" data-bs-target="#newAuditModal">
                <i class="fas fa-plus me-1"></i> New Audit
            </button>
            <button class="btn btn-light btn-sm" id="refreshAuditData">
                <i class="fas fa-sync-alt me-1"></i> Refresh
            </button>
        </div>
    </div>
    
    <!-- KPI Widgets -->
    <div class="row g-4 mb-4">
        <div class="col-xl-3 col-lg-6">
            <div class="widget-card">
                <div class="widget-header">
                    <span class="widget-title">Total Audits</span>
                    <div class="widget-icon" style="background: rgba(37, 99, 235, 0.1); color: #2563EB;">
                        <i class="fas fa-clipboard-list"></i>
                    </div>
                </div>
                <div class="widget-value"><?php echo $stats['total_audits'] ?? 48; ?></div>
                <div class="widget-change positive">
                    <i class="fas fa-arrow-up"></i> 6 from last quarter
                </div>
            </div>
        </div>
        <div class="col-xl-3 col-lg-6">
            <div class="widget-card">
                <div class="widget-header">
                    <span class="widget-title">In Progress</span>
                    <div class="widget-icon" style="background: rgba(245, 158, 11, 0.1); color: #F59E0B;">
                        <i class="fas fa-spinner"></i>
                    </div>
                </div>
                <div class="widget-value"><?php echo $stats['in_progress'] ?? 12; ?></div>
                <div class="widget-change negative">
                    <i class="fas fa-arrow-up"></i> 2 from last week
                </div>
            </div>
        </div>
        <div class="col-xl-3 col-lg-6">
            <div class="widget-card">
                <div class="widget-header">
                    <span class="widget-title">Open Findings</span>
                    <div class="widget-icon" style="background: rgba(239, 68, 68, 0.1); color: #EF4444;">
                        <i class="fas fa-exclamation-circle"></i>
                    </div>
                </div>
                <div class="widget-value"><?php echo $stats['open_findings'] ?? 23; ?></div>
                <div class="widget-change negative">
                    <i class="fas fa-arrow-up"></i> 5 from last month
                </div>
            </div>
        </div>
        <div class="col-xl-3 col-lg-6">
            <div class="widget-card">
                <div class="widget-header">
                    <span class="widget-title">Resolution Rate</span>
                    <div class="widget-icon" style="background: rgba(34, 197, 94, 0.1); color: #22C55E;">
                        <i class="fas fa-check-circle"></i>
                    </div>
                </div>
                <div class="widget-value"><?php echo $stats['resolution_rate'] ?? 78; ?>%</div>
                <div class="widget-change positive">
                    <i class="fas fa-arrow-up"></i> 4.2% improvement
                </div>
            </div>
        </div>
    </div>
    
    <!-- Charts Row -->
    <div class="row g-4 mb-4">
        <div class="col-xl-8">
            <div class="card">
                <div class="card-header">
                    <i class="fas fa-chart-bar me-2"></i> Audit Activity
                    <span class="badge bg-primary ms-2">Last 12 Months</span>
                </div>
                <div class="card-body">
                    <canvas id="auditActivityChart" height="250"></canvas>
                </div>
            </div>
        </div>
        <div class="col-xl-4">
            <div class="card">
                <div class="card-header">
                    <i class="fas fa-chart-pie me-2"></i> Finding Severity
                </div>
                <div class="card-body">
                    <canvas id="severityChart" height="250"></canvas>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Bottom Row -->
    <div class="row g-4">
        <!-- Recent Findings -->
        <div class="col-xl-7">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <span><i class="fas fa-search me-2"></i> Recent Findings</span>
                    <a href="<?php echo BASE_URL; ?>/audit/findings" class="btn btn-sm btn-outline-primary">View All</a>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover enterprise-table mb-0">
                            <thead>
                                <tr>
                                    <th>Finding ID</th>
                                    <th>Title</th>
                                    <th>Severity</th>
                                    <th>Status</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if (!empty($recent_findings)): ?>
                                    <?php foreach ($recent_findings as $finding): ?>
                                        <tr>
                                            <td><span class="finding-id">#<?php echo str_pad($finding->id, 4, '0', STR_PAD_LEFT); ?></span></td>
                                            <td><?php echo htmlspecialchars(substr($finding->title, 0, 40)) . '...'; ?></td>
                                            <td>
                                                <span class="severity-badge <?php echo $finding->severity; ?>">
                                                    <?php echo ucfirst($finding->severity); ?>
                                                </span>
                                            </td>
                                            <td>
                                                <span class="status-badge <?php echo $finding->status; ?>">
                                                    <?php echo ucfirst($finding->status); ?>
                                                </span>
                                            </td>
                                            <td>
                                                <a href="<?php echo BASE_URL; ?>/audit/findings/<?php echo $finding->id; ?>" 
                                                   class="btn btn-sm btn-outline-primary">
                                                    <i class="fas fa-eye"></i>
                                                </a>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                <?php else: ?>
                                    <tr>
                                        <td colspan="5" class="text-center py-4 text-muted">
                                            <i class="fas fa-inbox fa-2x d-block mb-2"></i>
                                            No findings found
                                        </td>
                                    </tr>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Upcoming Audits -->
        <div class="col-xl-5">
            <div class="card">
                <div class="card-header">
                    <i class="fas fa-calendar-alt me-2"></i> Upcoming Audits
                </div>
                <div class="card-body p-0">
                    <div class="audit-list">
                        <?php if (!empty($upcoming_audits)): ?>
                            <?php foreach ($upcoming_audits as $audit): ?>
                                <div class="audit-item">
                                    <div class="audit-date">
                                        <span class="date-day"><?php echo date('d', strtotime($audit->start_date)); ?></span>
                                        <span class="date-month"><?php echo date('M', strtotime($audit->start_date)); ?></span>
                                    </div>
                                    <div class="audit-info">
                                        <div class="audit-title"><?php echo htmlspecialchars($audit->title); ?></div>
                                        <div class="audit-meta">
                                            <span><i class="fas fa-user me-1"></i> <?php echo htmlspecialchars($audit->auditor_name ?? 'Unassigned'); ?></span>
                                            <span><i class="fas fa-building me-1"></i> <?php echo htmlspecialchars($audit->department_name ?? 'N/A'); ?></span>
                                        </div>
                                    </div>
                                    <div class="audit-status">
                                        <span class="badge bg-<?php echo $audit->status === 'planned' ? 'primary' : ($audit->status === 'in_progress' ? 'warning' : 'success'); ?>">
                                            <?php echo ucfirst($audit->status); ?>
                                        </span>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <div class="text-center text-muted py-4">
                                <i class="fas fa-calendar fa-2x mb-2"></i>
                                <p>No upcoming audits</p>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- New Audit Modal -->
<div class="modal fade" id="newAuditModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title"><i class="fas fa-plus-circle me-2"></i> Schedule New Audit</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form method="POST" action="<?php echo BASE_URL; ?>/audit">
                <div class="modal-body">
                    <input type="hidden" name="csrf_token" value="<?php echo $csrf_token ?? ''; ?>">
                    
                    <div class="row g-3">
                        <div class="col-12">
                            <label class="form-label">Audit Title</label>
                            <input type="text" class="form-control" name="title" required>
                        </div>
                        <div class="col-12">
                            <label class="form-label">Scope Description</label>
                            <textarea class="form-control" name="scope_description" rows="3" required></textarea>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Audit Type</label>
                            <select class="form-select" name="audit_type" required>
                                <option value="">Select Type</option>
                                <option value="internal">Internal</option>
                                <option value="external">External</option>
                                <option value="regulatory">Regulatory</option>
                                <option value="forensic">Forensic</option>
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Department</label>
                            <select class="form-select" name="department_id" required>
                                <option value="">Select Department</option>
                                <?php foreach ($departments ?? [] as $dept): ?>
                                    <option value="<?php echo $dept->id; ?>"><?php echo htmlspecialchars($dept->name); ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Start Date</label>
                            <input type="date" class="form-control" name="start_date" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">End Date</label>
                            <input type="date" class="form-control" name="end_date" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Lead Auditor</label>
                            <select class="form-select" name="lead_auditor_id" required>
                                <option value="">Select Auditor</option>
                                <?php foreach ($auditors ?? [] as $auditor): ?>
                                    <option value="<?php echo $auditor->id; ?>"><?php echo htmlspecialchars($auditor->full_name); ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Estimated Budget</label>
                            <input type="number" class="form-control" name="estimated_budget" step="0.01">
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">Schedule Audit</button>
                </div>
            </form>
        </div>
    </div>
</div>

<style>
.audit-dashboard-container {
    padding: 0;
}

.welcome-banner {
    background: linear-gradient(135deg, #0B3D91, #2563EB);
    color: #fff;
    padding: 20px 24px;
    border-radius: 12px;
    display: flex;
    justify-content: space-between;
    align-items: center;
    flex-wrap: wrap;
}

.welcome-banner h5 {
    font-weight: 600;
    margin: 0;
}

.welcome-banner p {
    opacity: 0.85;
    font-size: 14px;
}

.welcome-actions {
    display: flex;
    gap: 8px;
}

.welcome-actions .btn-light {
    color: #0B3D91;
    font-weight: 500;
}

.widget-card {
    padding: 20px;
    border-radius: 12px;
    background: #fff;
    box-shadow: 0 1px 3px rgba(0,0,0,0.06);
    transition: all 0.3s;
    height: 100%;
}

.widget-card:hover {
    box-shadow: 0 4px 12px rgba(0,0,0,0.1);
    transform: translateY(-2px);
}

.widget-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 8px;
}

.widget-title {
    font-size: 14px;
    color: #64748B;
    font-weight: 500;
}

.widget-icon {
    width: 40px;
    height: 40px;
    border-radius: 10px;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 18px;
}

.widget-value {
    font-size: 28px;
    font-weight: 700;
    color: #1E293B;
}

.widget-change {
    font-size: 13px;
    font-weight: 500;
}

.widget-change.positive { color: #22C55E; }
.widget-change.negative { color: #EF4444; }

.finding-id {
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

.audit-list {
    padding: 8px 0;
}

.audit-item {
    display: flex;
    align-items: center;
    padding: 12px 20px;
    border-bottom: 1px solid #F1F5F9;
    transition: background 0.2s;
}

.audit-item:hover {
    background: #F8FAFC;
}

.audit-date {
    text-align: center;
    min-width: 56px;
    margin-right: 16px;
}

.audit-date .date-day {
    display: block;
    font-size: 20px;
    font-weight: 700;
    color: #1E293B;
}

.audit-date .date-month {
    display: block;
    font-size: 11px;
    color: #94A3B8;
    text-transform: uppercase;
}

.audit-info {
    flex: 1;
}

.audit-title {
    font-weight: 500;
    color: #1E293B;
    font-size: 14px;
}

.audit-meta {
    display: flex;
    gap: 16px;
    font-size: 12px;
    color: #94A3B8;
    margin-top: 2px;
}

.audit-status {
    margin-left: 12px;
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
    .welcome-banner {
        flex-direction: column;
        text-align: center;
        gap: 12px;
    }
    
    .audit-item {
        flex-wrap: wrap;
        gap: 8px;
    }
    
    .audit-date {
        min-width: 40px;
    }
    
    .audit-date .date-day {
        font-size: 16px;
    }
}
</style>

<script>
$(document).ready(function() {
    // Audit Activity Chart
    <?php if (isset($chart_data['activity'])): ?>
    const activityCtx = document.getElementById('auditActivityChart').getContext('2d');
    new Chart(activityCtx, {
        type: 'bar',
        data: {
            labels: <?php echo json_encode($chart_data['activity']['labels'] ?? ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec']); ?>,
            datasets: [
                {
                    label: 'Planned',
                    data: <?php echo json_encode($chart_data['activity']['planned'] ?? [3, 4, 2, 5, 3, 4, 6, 3, 5, 4, 3, 5]); ?>,
                    backgroundColor: 'rgba(37, 99, 235, 0.7)',
                    borderRadius: 4
                },
                {
                    label: 'Completed',
                    data: <?php echo json_encode($chart_data['activity']['completed'] ?? [2, 3, 2, 4, 3, 3, 5, 2, 4, 3, 3, 4]); ?>,
                    backgroundColor: 'rgba(34, 197, 94, 0.7)',
                    borderRadius: 4
                }
            ]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: {
                    position: 'top',
                    labels: {
                        usePointStyle: true,
                        boxWidth: 8
                    }
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
    
    // Severity Chart
    <?php if (isset($chart_data['severity'])): ?>
    const severityCtx = document.getElementById('severityChart').getContext('2d');
    new Chart(severityCtx, {
        type: 'doughnut',
        data: {
            labels: ['Critical', 'High', 'Medium', 'Low'],
            datasets: [{
                data: <?php echo json_encode($chart_data['severity'] ?? [5, 12, 18, 8]); ?>,
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
});
</script>