<?php
/**
 * Dashboard Page
 * 
 * @var string $title
 * @var array $stats
 * @var array $chart_data
 * @var array $recent_activities
 * @var array $upcoming_tasks
 * @var array $notifications
 */
?>

<?php $page_title = 'Dashboard'; ?>
<?php $active_page = 'dashboard'; ?>
<?php $auto_refresh = true; ?>

<div class="dashboard-container">
    <!-- Welcome Banner -->
    <div class="welcome-banner">
        <div class="welcome-content">
            <h2>Welcome back, <?php echo isset($current_user) ? htmlspecialchars($current_user->first_name) : 'User'; ?>!</h2>
            <p>Here's what's happening with your GRC activities today.</p>
        </div>
        <div class="welcome-date">
            <i class="far fa-calendar-alt"></i>
            <?php echo date('l, d F Y'); ?>
        </div>
    </div>
    
    <!-- KPI Widgets -->
    <div class="row g-4 mb-4">
        <div class="col-xl-3 col-lg-6">
            <div class="widget-card">
                <div class="widget-header">
                    <span class="widget-title">Compliance Score</span>
                    <div class="widget-icon" style="background: rgba(37, 99, 235, 0.1); color: #2563EB;">
                        <i class="fas fa-check-circle"></i>
                    </div>
                </div>
                <div class="widget-value"><?php echo $stats['compliance_completion_rate'] ?? 68; ?>%</div>
                <div class="widget-change positive">
                    <i class="fas fa-arrow-up"></i> 5.2% from last month
                </div>
                <div class="progress mt-2" style="height: 6px;">
                    <div class="progress-bar" style="width: <?php echo $stats['compliance_completion_rate'] ?? 68; ?>%; background: #2563EB;"></div>
                </div>
            </div>
        </div>
        
        <div class="col-xl-3 col-lg-6">
            <div class="widget-card">
                <div class="widget-header">
                    <span class="widget-title">Risk Score</span>
                    <div class="widget-icon" style="background: rgba(239, 68, 68, 0.1); color: #EF4444;">
                        <i class="fas fa-shield-alt"></i>
                    </div>
                </div>
                <div class="widget-value"><?php echo $stats['avg_risk_score'] ?? 65; ?>%</div>
                <div class="widget-change negative">
                    <i class="fas fa-arrow-up"></i> 3.1% from last month
                </div>
                <div class="progress mt-2" style="height: 6px;">
                    <div class="progress-bar" style="width: <?php echo $stats['avg_risk_score'] ?? 65; ?>%; background: #EF4444;"></div>
                </div>
            </div>
        </div>
        
        <div class="col-xl-3 col-lg-6">
            <div class="widget-card">
                <div class="widget-header">
                    <span class="widget-title">Open Risks</span>
                    <div class="widget-icon" style="background: rgba(245, 158, 11, 0.1); color: #F59E0B;">
                        <i class="fas fa-exclamation-triangle"></i>
                    </div>
                </div>
                <div class="widget-value"><?php echo $stats['open_risks'] ?? 23; ?></div>
                <div class="widget-change negative">
                    <i class="fas fa-arrow-up"></i> 2 from last week
                </div>
            </div>
        </div>
        
        <div class="col-xl-3 col-lg-6">
            <div class="widget-card">
                <div class="widget-header">
                    <span class="widget-title">Audit Findings</span>
                    <div class="widget-icon" style="background: rgba(16, 185, 129, 0.1); color: #10B981;">
                        <i class="fas fa-clipboard-list"></i>
                    </div>
                </div>
                <div class="widget-value"><?php echo $stats['audit_findings'] ?? 12; ?></div>
                <div class="widget-change positive">
                    <i class="fas fa-arrow-down"></i> 4 from last week
                </div>
            </div>
        </div>
    </div>
    
    <!-- Charts Row -->
    <div class="row g-4 mb-4">
        <!-- Risk Heatmap -->
        <div class="col-xl-6">
            <div class="card">
                <div class="card-header">
                    <i class="fas fa-fire me-2"></i> Risk Heatmap
                </div>
                <div class="card-body">
                    <div class="heatmap-grid">
                        <div class="heatmap-container">
                            <?php if (isset($chart_data['heatmap'])): ?>
                                <canvas id="riskHeatmapChart" height="250"></canvas>
                            <?php else: ?>
                                <div class="text-center text-muted py-4">
                                    <i class="fas fa-chart-area fa-3x"></i>
                                    <p class="mt-2">No risk data available</p>
                                </div>
                            <?php endif; ?>
                        </div>
                        <div class="heatmap-legend mt-3">
                            <span class="legend-label">Very Low</span>
                            <span class="legend-item" style="background: #22C55E;"></span>
                            <span class="legend-item" style="background: #F59E0B;"></span>
                            <span class="legend-item" style="background: #EF4444;"></span>
                            <span class="legend-item" style="background: #DC2626;"></span>
                            <span class="legend-label">Very High</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Compliance Status -->
        <div class="col-xl-6">
            <div class="card">
                <div class="card-header">
                    <i class="fas fa-chart-pie me-2"></i> Compliance Status
                </div>
                <div class="card-body">
                    <canvas id="complianceStatusChart" height="250"></canvas>
                    <div class="compliance-stats mt-3">
                        <div class="stat-item">
                            <span class="stat-dot" style="background: #22C55E;"></span>
                            Compliant: <?php echo $stats['compliant_percentage'] ?? 68; ?>%
                        </div>
                        <div class="stat-item">
                            <span class="stat-dot" style="background: #F59E0B;"></span>
                            Partially Compliant: <?php echo $stats['partial_compliant'] ?? 22; ?>%
                        </div>
                        <div class="stat-item">
                            <span class="stat-dot" style="background: #EF4444;"></span>
                            Non-Compliant: <?php echo $stats['non_compliant'] ?? 10; ?>%
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Bottom Row -->
    <div class="row g-4">
        <!-- Upcoming Tasks -->
        <div class="col-xl-6">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <span><i class="fas fa-tasks me-2"></i> Upcoming Tasks</span>
                    <a href="<?php echo BASE_URL; ?>/compliance/tasks" class="btn btn-sm btn-outline-primary">View All</a>
                </div>
                <div class="card-body p-0">
                    <div class="task-list">
                        <?php if (!empty($upcoming_tasks)): ?>
                            <?php foreach ($upcoming_tasks as $task): ?>
                                <div class="task-item">
                                    <div class="task-status <?php echo $task->priority ?? 'medium'; ?>">
                                        <span class="status-dot"></span>
                                    </div>
                                    <div class="task-content">
                                        <div class="task-title"><?php echo htmlspecialchars($task->title); ?></div>
                                        <div class="task-meta">
                                            <span class="task-date">
                                                <i class="far fa-calendar-alt"></i> Due: <?php echo date('d M Y', strtotime($task->due_date)); ?>
                                            </span>
                                            <span class="task-assignee">
                                                <i class="fas fa-user"></i> <?php echo htmlspecialchars($task->assigned_to_name ?? 'Unassigned'); ?>
                                            </span>
                                        </div>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <div class="text-center text-muted py-4">
                                <i class="fas fa-check-circle fa-2x"></i>
                                <p class="mt-2">No upcoming tasks</p>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Recent Activities -->
        <div class="col-xl-6">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <span><i class="fas fa-clock me-2"></i> Recent Activities</span>
                    <a href="<?php echo BASE_URL; ?>/audit" class="btn btn-sm btn-outline-primary">View All</a>
                </div>
                <div class="card-body p-0">
                    <div class="activity-feed">
                        <?php if (!empty($recent_activities)): ?>
                            <?php foreach ($recent_activities as $activity): ?>
                                <div class="activity-item">
                                    <div class="activity-icon <?php echo $activity->type ?? 'info'; ?>">
                                        <i class="fas fa-<?php echo $activity->icon ?? 'circle'; ?>"></i>
                                    </div>
                                    <div class="activity-content">
                                        <p class="activity-text"><?php echo htmlspecialchars($activity->description); ?></p>
                                        <span class="activity-time"><?php echo $activity->time_ago ?? 'Just now'; ?></span>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <div class="text-center text-muted py-4">
                                <i class="fas fa-inbox fa-2x"></i>
                                <p class="mt-2">No recent activities</p>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Dashboard CSS -->
<style>
.dashboard-container {
    max-width: 100%;
}

.welcome-banner {
    background: linear-gradient(135deg, #0B3D91, #2563EB);
    color: #fff;
    padding: 24px 30px;
    border-radius: 16px;
    margin-bottom: 24px;
    display: flex;
    justify-content: space-between;
    align-items: center;
    flex-wrap: wrap;
}

.welcome-banner h2 {
    font-size: 24px;
    font-weight: 600;
    margin: 0;
}

.welcome-banner p {
    margin: 0;
    opacity: 0.8;
}

.welcome-date {
    font-size: 14px;
    opacity: 0.9;
}

.welcome-date i {
    margin-right: 8px;
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

/* Heatmap */
.heatmap-container {
    padding: 10px 0;
}

.heatmap-legend {
    display: flex;
    align-items: center;
    gap: 8px;
}

.legend-label {
    font-size: 12px;
    color: #64748B;
}

.legend-item {
    width: 20px;
    height: 8px;
    border-radius: 4px;
}

/* Task List */
.task-list {
    padding: 8px 0;
}

.task-item {
    display: flex;
    align-items: flex-start;
    padding: 12px 20px;
    border-bottom: 1px solid #F1F5F9;
    transition: background 0.2s;
}

.task-item:hover {
    background: #F8FAFC;
}

.task-status {
    margin-right: 14px;
    padding-top: 4px;
}

.status-dot {
    display: block;
    width: 10px;
    height: 10px;
    border-radius: 50%;
}

.task-status.critical .status-dot { background: #DC2626; }
.task-status.high .status-dot { background: #EF4444; }
.task-status.medium .status-dot { background: #F59E0B; }
.task-status.low .status-dot { background: #22C55E; }

.task-content {
    flex: 1;
}

.task-title {
    font-weight: 500;
    color: #1E293B;
    font-size: 14px;
}

.task-meta {
    display: flex;
    gap: 16px;
    margin-top: 4px;
    font-size: 12px;
    color: #64748B;
}

.task-meta i {
    margin-right: 4px;
}

/* Activity Feed */
.activity-feed {
    padding: 8px 0;
}

.activity-item {
    display: flex;
    align-items: flex-start;
    padding: 12px 20px;
    border-bottom: 1px solid #F1F5F9;
}

.activity-icon {
    width: 36px;
    height: 36px;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    margin-right: 14px;
    font-size: 14px;
    flex-shrink: 0;
}

.activity-icon.success { background: #D1FAE5; color: #10B981; }
.activity-icon.warning { background: #FEF3C7; color: #F59E0B; }
.activity-icon.danger { background: #FEE2E2; color: #EF4444; }
.activity-icon.info { background: #DBEAFE; color: #3B82F6; }

.activity-content {
    flex: 1;
}

.activity-text {
    margin: 0;
    font-size: 14px;
    color: #1E293B;
}

.activity-time {
    font-size: 12px;
    color: #94A3B8;
}

/* Compliance Stats */
.compliance-stats {
    display: flex;
    gap: 20px;
    justify-content: center;
}

.stat-item {
    font-size: 13px;
    color: #64748B;
}

.stat-dot {
    display: inline-block;
    width: 10px;
    height: 10px;
    border-radius: 50%;
    margin-right: 6px;
}

/* Responsive */
@media (max-width: 768px) {
    .welcome-banner {
        padding: 16px 20px;
    }
    
    .welcome-banner h2 {
        font-size: 20px;
    }
    
    .widget-value {
        font-size: 22px;
    }
    
    .task-meta {
        flex-direction: column;
        gap: 2px;
    }
}
</style>

<!-- Dashboard JavaScript -->
<script>
$(document).ready(function() {
    // Risk Heatmap Chart
    <?php if (isset($chart_data['heatmap'])): ?>
    const heatmapCtx = document.getElementById('riskHeatmapChart').getContext('2d');
    new Chart(heatmapCtx, {
        type: 'bar',
        data: {
            labels: ['Very Low', 'Low', 'Medium', 'High', 'Very High'],
            datasets: [{
                label: 'Risk Level',
                data: <?php echo json_encode($chart_data['heatmap']); ?>,
                backgroundColor: ['#22C55E', '#F59E0B', '#F97316', '#EF4444', '#DC2626'],
                borderRadius: 8
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
    
    // Compliance Status Chart
    <?php if (isset($chart_data['compliance_status'])): ?>
    const complianceCtx = document.getElementById('complianceStatusChart').getContext('2d');
    new Chart(complianceCtx, {
        type: 'doughnut',
        data: {
            labels: ['Compliant', 'Partially Compliant', 'Non-Compliant'],
            datasets: [{
                data: <?php echo json_encode($chart_data['compliance_status']); ?>,
                backgroundColor: ['#22C55E', '#F59E0B', '#EF4444'],
                borderWidth: 0
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            cutout: '70%',
            plugins: {
                legend: {
                    position: 'bottom',
                    labels: {
                        usePointStyle: true,
                        padding: 20
                    }
                }
            }
        }
    });
    <?php endif; ?>
});
</script>