<?php
/**
 * Basel Dashboard Page
 * 
 * @var string $title
 * @var array $basel_metrics
 * @var array $capital_data
 */
?>

<?php $page_title = 'Basel Dashboard'; ?>
<?php $active_page = 'risk'; ?>

<div class="basel-container">
    <!-- Header -->
    <div class="row mb-4">
        <div class="col-md-8">
            <h5><i class="fas fa-university me-2 text-primary"></i> Basel III Dashboard</h5>
            <p class="text-muted">Capital adequacy and risk-weighted assets monitoring</p>
        </div>
        <div class="col-md-4 text-end">
            <button class="btn btn-outline-primary" id="exportBaselReport">
                <i class="fas fa-download me-2"></i> Export Report
            </button>
        </div>
    </div>
    
    <!-- Basel Metrics -->
    <div class="row g-4 mb-4">
        <div class="col-xl-3 col-lg-6">
            <div class="basel-card">
                <div class="basel-header">
                    <span class="basel-label">CET1 Ratio</span>
                    <span class="basel-value <?php echo ($basel_metrics['cet1'] ?? 12.5) >= 10.5 ? 'text-success' : 'text-danger'; ?>">
                        <?php echo $basel_metrics['cet1'] ?? 12.5; ?>%
                    </span>
                </div>
                <div class="basel-requirement">Minimum: 10.5%</div>
                <div class="progress mt-2" style="height: 6px;">
                    <div class="progress-bar bg-success" style="width: <?php echo ($basel_metrics['cet1'] ?? 12.5) / 15 * 100; ?>%;"></div>
                </div>
            </div>
        </div>
        <div class="col-xl-3 col-lg-6">
            <div class="basel-card">
                <div class="basel-header">
                    <span class="basel-label">Tier 1 Ratio</span>
                    <span class="basel-value <?php echo ($basel_metrics['tier1'] ?? 14.2) >= 12 ? 'text-success' : 'text-danger'; ?>">
                        <?php echo $basel_metrics['tier1'] ?? 14.2; ?>%
                    </span>
                </div>
                <div class="basel-requirement">Minimum: 12.0%</div>
                <div class="progress mt-2" style="height: 6px;">
                    <div class="progress-bar bg-success" style="width: <?php echo ($basel_metrics['tier1'] ?? 14.2) / 16 * 100; ?>%;"></div>
                </div>
            </div>
        </div>
        <div class="col-xl-3 col-lg-6">
            <div class="basel-card">
                <div class="basel-header">
                    <span class="basel-label">Capital Adequacy Ratio</span>
                    <span class="basel-value <?php echo ($basel_metrics['car'] ?? 16.8) >= 14 ? 'text-success' : 'text-danger'; ?>">
                        <?php echo $basel_metrics['car'] ?? 16.8; ?>%
                    </span>
                </div>
                <div class="basel-requirement">Minimum: 14.0%</div>
                <div class="progress mt-2" style="height: 6px;">
                    <div class="progress-bar bg-success" style="width: <?php echo ($basel_metrics['car'] ?? 16.8) / 18 * 100; ?>%;"></div>
                </div>
            </div>
        </div>
        <div class="col-xl-3 col-lg-6">
            <div class="basel-card">
                <div class="basel-header">
                    <span class="basel-label">Leverage Ratio</span>
                    <span class="basel-value <?php echo ($basel_metrics['leverage'] ?? 5.2) >= 4.5 ? 'text-success' : 'text-danger'; ?>">
                        <?php echo $basel_metrics['leverage'] ?? 5.2; ?>%
                    </span>
                </div>
                <div class="basel-requirement">Minimum: 4.5%</div>
                <div class="progress mt-2" style="height: 6px;">
                    <div class="progress-bar bg-success" style="width: <?php echo ($basel_metrics['leverage'] ?? 5.2) / 7 * 100; ?>%;"></div>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Charts -->
    <div class="row g-4">
        <div class="col-xl-8">
            <div class="card">
                <div class="card-header">
                    <i class="fas fa-chart-area me-2"></i> Capital Adequacy Trend
                </div>
                <div class="card-body">
                    <canvas id="capitalTrendChart" height="250"></canvas>
                </div>
            </div>
        </div>
        <div class="col-xl-4">
            <div class="card">
                <div class="card-header">
                    <i class="fas fa-chart-pie me-2"></i> Risk-Weighted Assets
                </div>
                <div class="card-body">
                    <canvas id="rwaChart" height="250"></canvas>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
.basel-container {
    padding: 0;
}

.basel-card {
    background: #fff;
    padding: 20px;
    border-radius: 12px;
    box-shadow: 0 1px 3px rgba(0,0,0,0.06);
    transition: all 0.3s;
    height: 100%;
}

.basel-card:hover {
    box-shadow: 0 4px 12px rgba(0,0,0,0.1);
    transform: translateY(-2px);
}

.basel-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 4px;
}

.basel-label {
    font-size: 14px;
    color: #64748B;
    font-weight: 500;
}

.basel-value {
    font-size: 24px;
    font-weight: 700;
}

.basel-value.text-success { color: #22C55E; }
.basel-value.text-danger { color: #EF4444; }

.basel-requirement {
    font-size: 13px;
    color: #94A3B8;
}
</style>

<script>
$(document).ready(function() {
    // Capital Adequacy Trend Chart
    <?php if (isset($capital_data['trend'])): ?>
    const trendCtx = document.getElementById('capitalTrendChart').getContext('2d');
    new Chart(trendCtx, {
        type: 'line',
        data: {
            labels: <?php echo json_encode($capital_data['trend']['labels'] ?? ['Q1', 'Q2', 'Q3', 'Q4']); ?>,
            datasets: [
                {
                    label: 'CET1 Ratio',
                    data: <?php echo json_encode($capital_data['trend']['cet1'] ?? [11.2, 11.8, 12.2, 12.5]); ?>,
                    borderColor: '#2563EB',
                    backgroundColor: 'rgba(37, 99, 235, 0.1)',
                    fill: true,
                    tension: 0.4
                },
                {
                    label: 'Tier 1 Ratio',
                    data: <?php echo json_encode($capital_data['trend']['tier1'] ?? [12.8, 13.5, 13.8, 14.2]); ?>,
                    borderColor: '#22C55E',
                    backgroundColor: 'rgba(34, 197, 94, 0.1)',
                    fill: true,
                    tension: 0.4
                },
                {
                    label: 'CAR',
                    data: <?php echo json_encode($capital_data['trend']['car'] ?? [15.2, 15.8, 16.2, 16.8]); ?>,
                    borderColor: '#F59E0B',
                    backgroundColor: 'rgba(245, 158, 11, 0.1)',
                    fill: true,
                    tension: 0.4
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
                    max: 20,
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
    
    // RWA Chart
    <?php if (isset($capital_data['rwa'])): ?>
    const rwaCtx = document.getElementById('rwaChart').getContext('2d');
    new Chart(rwaCtx, {
        type: 'doughnut',
        data: {
            labels: ['Credit Risk', 'Market Risk', 'Operational Risk'],
            datasets: [{
                data: <?php echo json_encode($capital_data['rwa'] ?? [45, 30, 25]); ?>,
                backgroundColor: ['#2563EB', '#F59E0B', '#22C55E'],
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