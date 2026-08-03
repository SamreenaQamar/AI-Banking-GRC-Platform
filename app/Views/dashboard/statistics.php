<?php
/**
 * Statistics Page
 * 
 * @var string $title
 * @var array $statistics
 * @var array $chart_data
 */
?>

<?php $page_title = 'Statistics'; ?>
<?php $active_page = 'dashboard'; ?>

<div class="statistics-container">
    <!-- Period Selector -->
    <div class="period-selector mb-4">
        <div class="btn-group" role="group">
            <button class="btn btn-outline-primary" data-period="today">Today</button>
            <button class="btn btn-outline-primary active" data-period="week">This Week</button>
            <button class="btn btn-outline-primary" data-period="month">This Month</button>
            <button class="btn btn-outline-primary" data-period="quarter">This Quarter</button>
            <button class="btn btn-outline-primary" data-period="year">This Year</button>
        </div>
        <div class="date-range ms-3">
            <input type="date" class="form-control form-control-sm" id="dateFrom" style="width: 150px; display: inline-block;">
            <span class="mx-2">to</span>
            <input type="date" class="form-control form-control-sm" id="dateTo" style="width: 150px; display: inline-block;">
        </div>
    </div>
    
    <!-- Summary Cards -->
    <div class="row g-4 mb-4">
        <div class="col-xl-3 col-lg-6">
            <div class="stat-card">
                <div class="stat-card-header">
                    <span class="stat-label">Total Compliance Tasks</span>
                    <i class="fas fa-check-circle text-primary"></i>
                </div>
                <div class="stat-value"><?php echo $statistics['total_compliance'] ?? 1,234; ?></div>
                <div class="stat-change positive">
                    <i class="fas fa-arrow-up"></i> 12.5% from previous period
                </div>
            </div>
        </div>
        <div class="col-xl-3 col-lg-6">
            <div class="stat-card">
                <div class="stat-card-header">
                    <span class="stat-label">Risk Assessments</span>
                    <i class="fas fa-shield-alt text-danger"></i>
                </div>
                <div class="stat-value"><?php echo $statistics['risk_assessments'] ?? 567; ?></div>
                <div class="stat-change positive">
                    <i class="fas fa-arrow-up"></i> 8.3% from previous period
                </div>
            </div>
        </div>
        <div class="col-xl-3 col-lg-6">
            <div class="stat-card">
                <div class="stat-card-header">
                    <span class="stat-label">Audits Completed</span>
                    <i class="fas fa-clipboard-check text-success"></i>
                </div>
                <div class="stat-value"><?php echo $statistics['audits_completed'] ?? 89; ?></div>
                <div class="stat-change negative">
                    <i class="fas fa-arrow-down"></i> 2.1% from previous period
                </div>
            </div>
        </div>
        <div class="col-xl-3 col-lg-6">
            <div class="stat-card">
                <div class="stat-card-header">
                    <span class="stat-label">Average Response Time</span>
                    <i class="fas fa-clock text-warning"></i>
                </div>
                <div class="stat-value"><?php echo $statistics['avg_response_time'] ?? '2.4'; ?>h</div>
                <div class="stat-change positive">
                    <i class="fas fa-arrow-up"></i> 15.2% improvement
                </div>
            </div>
        </div>
    </div>
    
    <!-- Charts Row -->
    <div class="row g-4 mb-4">
        <div class="col-xl-6">
            <div class="card">
                <div class="card-header">
                    <i class="fas fa-chart-line me-2"></i> Compliance Trend
                    <span class="badge bg-primary ms-2">Last 12 Months</span>
                </div>
                <div class="card-body">
                    <canvas id="complianceTrendChart" height="250"></canvas>
                </div>
            </div>
        </div>
        <div class="col-xl-6">
            <div class="card">
                <div class="card-header">
                    <i class="fas fa-chart-bar me-2"></i> Risk Distribution
                    <span class="badge bg-danger ms-2">By Category</span>
                </div>
                <div class="card-body">
                    <canvas id="riskDistributionChart" height="250"></canvas>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Detailed Statistics Table -->
    <div class="card">
        <div class="card-header d-flex justify-content-between align-items-center">
            <span><i class="fas fa-table me-2"></i> Detailed Statistics</span>
            <button class="btn btn-sm btn-outline-primary" id="exportStatsBtn">
                <i class="fas fa-download me-1"></i> Export
            </button>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover enterprise-table mb-0">
                    <thead>
                        <tr>
                            <th>Metric</th>
                            <th>Current Period</th>
                            <th>Previous Period</th>
                            <th>Change</th>
                            <th>Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td>Compliance Score</td>
                            <td><?php echo $statistics['compliance_score'] ?? 68; ?>%</td>
                            <td><?php echo $statistics['prev_compliance_score'] ?? 62; ?>%</td>
                            <td class="text-success">+6.0%</td>
                            <td><span class="badge bg-success">Improving</span></td>
                        </tr>
                        <tr>
                            <td>Risk Score</td>
                            <td><?php echo $statistics['risk_score'] ?? 65; ?>%</td>
                            <td><?php echo $statistics['prev_risk_score'] ?? 60; ?>%</td>
                            <td class="text-danger">+5.0%</td>
                            <td><span class="badge bg-danger">Needs Attention</span></td>
                        </tr>
                        <tr>
                            <td>Open Risks</td>
                            <td><?php echo $statistics['open_risks'] ?? 23; ?></td>
                            <td><?php echo $statistics['prev_open_risks'] ?? 21; ?></td>
                            <td class="text-danger">+2</td>
                            <td><span class="badge bg-warning">Monitoring</span></td>
                        </tr>
                        <tr>
                            <td>Audit Findings</td>
                            <td><?php echo $statistics['audit_findings'] ?? 12; ?></td>
                            <td><?php echo $statistics['prev_audit_findings'] ?? 16; ?></td>
                            <td class="text-success">-4</td>
                            <td><span class="badge bg-success">Improving</span></td>
                        </tr>
                        <tr>
                            <td>Resolution Rate</td>
                            <td><?php echo $statistics['resolution_rate'] ?? 78; ?>%</td>
                            <td><?php echo $statistics['prev_resolution_rate'] ?? 72; ?>%</td>
                            <td class="text-success">+6.0%</td>
                            <td><span class="badge bg-success">On Track</span></td>
                        </tr>
                        <tr>
                            <td>User Satisfaction</td>
                            <td><?php echo $statistics['user_satisfaction'] ?? 4.2; ?>/5</td>
                            <td><?php echo $statistics['prev_user_satisfaction'] ?? 4.0; ?>/5</td>
                            <td class="text-success">+0.2</td>
                            <td><span class="badge bg-success">Good</span></td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<style>
.statistics-container {
    padding: 0;
}

.period-selector {
    display: flex;
    align-items: center;
    flex-wrap: wrap;
    background: #fff;
    padding: 12px 20px;
    border-radius: 12px;
    box-shadow: 0 1px 3px rgba(0,0,0,0.06);
}

.period-selector .btn-group .btn {
    border-radius: 8px;
    margin-right: 4px;
    font-size: 13px;
    padding: 6px 16px;
}

.period-selector .btn-group .btn.active {
    background: #2563EB;
    color: #fff;
    border-color: #2563EB;
}

.date-range .form-control {
    display: inline-block;
    width: auto;
    border-radius: 8px;
}

.stat-card {
    background: #fff;
    padding: 20px;
    border-radius: 12px;
    box-shadow: 0 1px 3px rgba(0,0,0,0.06);
    transition: all 0.3s;
    height: 100%;
}

.stat-card:hover {
    box-shadow: 0 4px 12px rgba(0,0,0,0.1);
    transform: translateY(-2px);
}

.stat-card-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 8px;
}

.stat-label {
    font-size: 14px;
    color: #64748B;
    font-weight: 500;
}

.stat-card-header i {
    font-size: 20px;
    opacity: 0.6;
}

.stat-value {
    font-size: 32px;
    font-weight: 700;
    color: #1E293B;
}

.stat-change {
    font-size: 13px;
    font-weight: 500;
    margin-top: 4px;
}

.stat-change.positive { color: #22C55E; }
.stat-change.negative { color: #EF4444; }

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
    .period-selector {
        flex-direction: column;
        gap: 12px;
        align-items: stretch;
    }
    
    .date-range {
        display: flex;
        align-items: center;
        gap: 8px;
        flex-wrap: wrap;
    }
    
    .date-range .form-control {
        flex: 1;
        min-width: 120px;
    }
    
    .stat-value {
        font-size: 24px;
    }
}
</style>

<script>
$(document).ready(function() {
    // Period selector
    $('.period-selector .btn').on('click', function() {
        $('.period-selector .btn').removeClass('active');
        $(this).addClass('active');
        // Load data for selected period
    });
    
    // Compliance Trend Chart
    <?php if (isset($chart_data['compliance_trend'])): ?>
    const trendCtx = document.getElementById('complianceTrendChart').getContext('2d');
    new Chart(trendCtx, {
        type: 'line',
        data: {
            labels: <?php echo json_encode($chart_data['compliance_trend']['labels'] ?? []); ?>,
            datasets: [
                {
                    label: 'Compliance Score',
                    data: <?php echo json_encode($chart_data['compliance_trend']['compliance'] ?? []); ?>,
                    borderColor: '#2563EB',
                    backgroundColor: 'rgba(37, 99, 235, 0.1)',
                    fill: true,
                    tension: 0.4,
                    pointRadius: 4
                },
                {
                    label: 'Risk Score',
                    data: <?php echo json_encode($chart_data['compliance_trend']['risk'] ?? []); ?>,
                    borderColor: '#EF4444',
                    backgroundColor: 'rgba(239, 68, 68, 0.1)',
                    fill: true,
                    tension: 0.4,
                    pointRadius: 4
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
                    max: 100,
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
    
    // Risk Distribution Chart
    <?php if (isset($chart_data['risk_distribution'])): ?>
    const riskCtx = document.getElementById('riskDistributionChart').getContext('2d');
    new Chart(riskCtx, {
        type: 'bar',
        data: {
            labels: <?php echo json_encode($chart_data['risk_distribution']['labels'] ?? []); ?>,
            datasets: [{
                label: 'Risks',
                data: <?php echo json_encode($chart_data['risk_distribution']['data'] ?? []); ?>,
                backgroundColor: [
                    '#22C55E',
                    '#F59E0B',
                    '#F97316',
                    '#EF4444',
                    '#DC2626'
                ],
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
});
</script>