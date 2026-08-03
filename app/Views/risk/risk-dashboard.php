<?php
/**
 * Risk Dashboard Page
 * 
 * @var string $title
 * @var array $stats
 * @var array $chart_data
 * @var array $recent_risks
 */
?>

<?php $page_title = 'Risk Dashboard'; ?>
<?php $active_page = 'risk'; ?>

<div class="risk-dashboard-container">
    <!-- Welcome Banner -->
    <div class="welcome-banner mb-4">
        <div class="welcome-content">
            <h5><i class="fas fa-shield-alt me-2"></i> Risk Management Dashboard</h5>
            <p class="mb-0">Overview of your organization's risk posture and key metrics</p>
        </div>
        <div class="welcome-actions">
            <button class="btn btn-light btn-sm" data-bs-toggle="modal" data-bs-target="#addRiskModal">
                <i class="fas fa-plus me-1"></i> Add Risk
            </button>
            <button class="btn btn-light btn-sm" id="refreshRiskData">
                <i class="fas fa-sync-alt me-1"></i> Refresh
            </button>
        </div>
    </div>
    
    <!-- KPI Widgets -->
    <div class="row g-4 mb-4">
        <div class="col-xl-3 col-lg-6">
            <div class="widget-card">
                <div class="widget-header">
                    <span class="widget-title">Total Risks</span>
                    <div class="widget-icon" style="background: rgba(37, 99, 235, 0.1); color: #2563EB;">
                        <i class="fas fa-list"></i>
                    </div>
                </div>
                <div class="widget-value"><?php echo $stats['total_risks'] ?? 156; ?></div>
                <div class="widget-change positive">
                    <i class="fas fa-arrow-up"></i> 8 from last month
                </div>
            </div>
        </div>
        <div class="col-xl-3 col-lg-6">
            <div class="widget-card">
                <div class="widget-header">
                    <span class="widget-title">Critical Risks</span>
                    <div class="widget-icon" style="background: rgba(239, 68, 68, 0.1); color: #EF4444;">
                        <i class="fas fa-exclamation-triangle"></i>
                    </div>
                </div>
                <div class="widget-value"><?php echo $stats['critical_risks'] ?? 12; ?></div>
                <div class="widget-change negative">
                    <i class="fas fa-arrow-up"></i> 3 from last month
                </div>
            </div>
        </div>
        <div class="col-xl-3 col-lg-6">
            <div class="widget-card">
                <div class="widget-header">
                    <span class="widget-title">Mitigation Rate</span>
                    <div class="widget-icon" style="background: rgba(34, 197, 94, 0.1); color: #22C55E;">
                        <i class="fas fa-check-circle"></i>
                    </div>
                </div>
                <div class="widget-value"><?php echo $stats['mitigation_rate'] ?? 68; ?>%</div>
                <div class="widget-change positive">
                    <i class="fas fa-arrow-up"></i> 5.2% improvement
                </div>
            </div>
        </div>
        <div class="col-xl-3 col-lg-6">
            <div class="widget-card">
                <div class="widget-header">
                    <span class="widget-title">Avg Risk Score</span>
                    <div class="widget-icon" style="background: rgba(245, 158, 11, 0.1); color: #F59E0B;">
                        <i class="fas fa-chart-line"></i>
                    </div>
                </div>
                <div class="widget-value"><?php echo $stats['avg_risk_score'] ?? 65; ?>%</div>
                <div class="widget-change negative">
                    <i class="fas fa-arrow-down"></i> 2.1% from last month
                </div>
            </div>
        </div>
    </div>
    
    <!-- Charts Row -->
    <div class="row g-4 mb-4">
        <div class="col-xl-8">
            <div class="card">
                <div class="card-header">
                    <i class="fas fa-chart-area me-2"></i> Risk Trend
                    <span class="badge bg-primary ms-2">Last 6 Months</span>
                </div>
                <div class="card-body">
                    <canvas id="riskTrendChart" height="250"></canvas>
                </div>
            </div>
        </div>
        <div class="col-xl-4">
            <div class="card">
                <div class="card-header">
                    <i class="fas fa-chart-pie me-2"></i> Risk Distribution
                </div>
                <div class="card-body">
                    <canvas id="riskDistributionChart" height="250"></canvas>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Bottom Row -->
    <div class="row g-4">
        <!-- Recent Risks -->
        <div class="col-xl-8">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <span><i class="fas fa-clock me-2"></i> Recent Risks</span>
                    <a href="<?php echo BASE_URL; ?>/risk/register" class="btn btn-sm btn-outline-primary">View All</a>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover enterprise-table mb-0">
                            <thead>
                                <tr>
                                    <th>Risk Code</th>
                                    <th>Title</th>
                                    <th>Level</th>
                                    <th>Status</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if (!empty($recent_risks)): ?>
                                    <?php foreach ($recent_risks as $risk): ?>
                                        <tr>
                                            <td><span class="risk-code"><?php echo htmlspecialchars($risk->risk_code); ?></span></td>
                                            <td><?php echo htmlspecialchars(substr($risk->title, 0, 40)) . '...'; ?></td>
                                            <td>
                                                <span class="risk-level <?php echo $risk->risk_level; ?>">
                                                    <?php echo ucfirst($risk->risk_level); ?>
                                                </span>
                                            </td>
                                            <td>
                                                <span class="status-badge <?php echo $risk->status; ?>">
                                                    <?php echo ucfirst($risk->status); ?>
                                                </span>
                                            </td>
                                            <td>
                                                <a href="<?php echo BASE_URL; ?>/risk/<?php echo $risk->id; ?>" 
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
                                            No recent risks
                                        </td>
                                    </tr>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Quick Actions -->
        <div class="col-xl-4">
            <div class="card">
                <div class="card-header">
                    <i class="fas fa-bolt me-2"></i> Quick Actions
                </div>
                <div class="card-body">
                    <div class="d-grid gap-2">
                        <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addRiskModal">
                            <i class="fas fa-plus me-2"></i> Add New Risk
                        </button>
                        <button class="btn btn-outline-primary">
                            <i class="fas fa-clipboard-check me-2"></i> Perform Assessment
                        </button>
                        <button class="btn btn-outline-success">
                            <i class="fas fa-file-alt me-2"></i> Generate Risk Report
                        </button>
                        <button class="btn btn-outline-warning">
                            <i class="fas fa-download me-2"></i> Export Risk Register
                        </button>
                        <button class="btn btn-outline-secondary">
                            <i class="fas fa-upload me-2"></i> Import Risks
                        </button>
                    </div>
                </div>
            </div>
            
            <!-- Risk Summary -->
            <div class="card mt-4">
                <div class="card-header">
                    <i class="fas fa-chart-simple me-2"></i> Risk Summary
                </div>
                <div class="card-body">
                    <div class="risk-summary-items">
                        <div class="summary-item">
                            <span class="summary-label">Open Risks</span>
                            <span class="summary-value"><?php echo $stats['open_risks'] ?? 45; ?></span>
                        </div>
                        <div class="summary-item">
                            <span class="summary-label">In Progress</span>
                            <span class="summary-value"><?php echo $stats['in_progress'] ?? 28; ?></span>
                        </div>
                        <div class="summary-item">
                            <span class="summary-label">Mitigated</span>
                            <span class="summary-value"><?php echo $stats['mitigated'] ?? 52; ?></span>
                        </div>
                        <div class="summary-item">
                            <span class="summary-label">Closed</span>
                            <span class="summary-value"><?php echo $stats['closed'] ?? 31; ?></span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Add Risk Modal -->
<div class="modal fade" id="addRiskModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title"><i class="fas fa-plus-circle me-2"></i> Add New Risk</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form method="POST" action="<?php echo BASE_URL; ?>/risk">
                <div class="modal-body">
                    <input type="hidden" name="csrf_token" value="<?php echo $csrf_token ?? ''; ?>">
                    
                    <div class="row g-3">
                        <div class="col-12">
                            <label class="form-label">Risk Title</label>
                            <input type="text" class="form-control" name="title" required>
                        </div>
                        <div class="col-12">
                            <label class="form-label">Description</label>
                            <textarea class="form-control" name="description" rows="3" required></textarea>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Category</label>
                            <select class="form-select" name="category_id" required>
                                <option value="">Select Category</option>
                                <?php foreach ($categories ?? [] as $category): ?>
                                    <option value="<?php echo $category->id; ?>"><?php echo htmlspecialchars($category->name); ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Department</label>
                            <select class="form-select" name="owner_department_id" required>
                                <option value="">Select Department</option>
                                <?php foreach ($departments ?? [] as $dept): ?>
                                    <option value="<?php echo $dept->id; ?>"><?php echo htmlspecialchars($dept->name); ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Likelihood (1-5)</label>
                            <input type="number" class="form-control" name="inherent_likelihood" min="1" max="5" required>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Impact (1-5)</label>
                            <input type="number" class="form-control" name="inherent_impact" min="1" max="5" required>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Identification Date</label>
                            <input type="date" class="form-control" name="identification_date" value="<?php echo date('Y-m-d'); ?>" required>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">Add Risk</button>
                </div>
            </form>
        </div>
    </div>
</div>

<style>
.risk-dashboard-container {
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

.risk-code {
    font-family: 'Courier New', monospace;
    font-size: 13px;
    color: #2563EB;
    font-weight: 600;
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

.risk-summary-items {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 8px;
}

.summary-item {
    display: flex;
    justify-content: space-between;
    padding: 8px 12px;
    background: #F8FAFC;
    border-radius: 8px;
}

.summary-label {
    color: #64748B;
    font-size: 13px;
}

.summary-value {
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
    .welcome-banner {
        flex-direction: column;
        text-align: center;
        gap: 12px;
    }
    
    .widget-value {
        font-size: 22px;
    }
}
</style>

<script>
$(document).ready(function() {
    // Risk Trend Chart
    <?php if (isset($chart_data['risk_trend'])): ?>
    const trendCtx = document.getElementById('riskTrendChart').getContext('2d');
    new Chart(trendCtx, {
        type: 'line',
        data: {
            labels: <?php echo json_encode($chart_data['risk_trend']['labels'] ?? ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun']); ?>,
            datasets: [
                {
                    label: 'Critical',
                    data: <?php echo json_encode($chart_data['risk_trend']['critical'] ?? [5, 7, 6, 8, 9, 12]); ?>,
                    borderColor: '#DC2626',
                    backgroundColor: 'rgba(220, 38, 38, 0.1)',
                    fill: true,
                    tension: 0.4,
                    pointRadius: 4
                },
                {
                    label: 'High',
                    data: <?php echo json_encode($chart_data['risk_trend']['high'] ?? [8, 10, 9, 11, 10, 8]); ?>,
                    borderColor: '#F59E0B',
                    backgroundColor: 'rgba(245, 158, 11, 0.1)',
                    fill: true,
                    tension: 0.4,
                    pointRadius: 4
                },
                {
                    label: 'Medium',
                    data: <?php echo json_encode($chart_data['risk_trend']['medium'] ?? [15, 14, 16, 13, 12, 10]); ?>,
                    borderColor: '#3B82F6',
                    backgroundColor: 'rgba(59, 130, 246, 0.1)',
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
    const distCtx = document.getElementById('riskDistributionChart').getContext('2d');
    new Chart(distCtx, {
        type: 'doughnut',
        data: {
            labels: ['Critical', 'High', 'Medium', 'Low'],
            datasets: [{
                data: <?php echo json_encode($chart_data['risk_distribution'] ?? [12, 23, 45, 76]); ?>,
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
    
    // Refresh button
    $('#refreshRiskData').on('click', function() {
        const btn = $(this);
        btn.html('<i class="fas fa-spinner fa-spin me-1"></i> Refreshing...');
        setTimeout(function() {
            location.reload();
        }, 1000);
    });
});
</script>