<?php
/**
 * Charts Page
 * 
 * @var string $title
 * @var array $chart_data
 */
?>

<?php $page_title = 'Analytics Charts'; ?>
<?php $active_page = 'dashboard'; ?>

<div class="charts-container">
    <!-- Chart Controls -->
    <div class="chart-controls mb-4">
        <div class="row g-3">
            <div class="col-md-3">
                <label class="form-label">Chart Type</label>
                <select class="form-select" id="chartType">
                    <option value="line">Line Chart</option>
                    <option value="bar">Bar Chart</option>
                    <option value="area">Area Chart</option>
                    <option value="pie">Pie Chart</option>
                    <option value="doughnut">Doughnut Chart</option>
                </select>
            </div>
            <div class="col-md-3">
                <label class="form-label">Data Source</label>
                <select class="form-select" id="dataSource">
                    <option value="compliance">Compliance</option>
                    <option value="risk">Risk</option>
                    <option value="audit">Audit</option>
                    <option value="sbp">SBP Circulars</option>
                </select>
            </div>
            <div class="col-md-3">
                <label class="form-label">Time Period</label>
                <select class="form-select" id="timePeriod">
                    <option value="7d">Last 7 Days</option>
                    <option value="30d" selected>Last 30 Days</option>
                    <option value="90d">Last 90 Days</option>
                    <option value="1y">Last Year</option>
                </select>
            </div>
            <div class="col-md-3 d-flex align-items-end">
                <button class="btn btn-primary w-100" id="updateChartBtn">
                    <i class="fas fa-sync-alt me-2"></i> Update Chart
                </button>
            </div>
        </div>
    </div>
    
    <!-- Main Chart -->
    <div class="card mb-4">
        <div class="card-header d-flex justify-content-between align-items-center">
            <span><i class="fas fa-chart-area me-2"></i> <span id="chartTitle">Compliance Trend</span></span>
            <div>
                <button class="btn btn-sm btn-outline-secondary" id="zoomInBtn">
                    <i class="fas fa-search-plus"></i>
                </button>
                <button class="btn btn-sm btn-outline-secondary" id="zoomOutBtn">
                    <i class="fas fa-search-minus"></i>
                </button>
                <button class="btn btn-sm btn-outline-secondary" id="resetZoomBtn">
                    <i class="fas fa-home"></i>
                </button>
            </div>
        </div>
        <div class="card-body">
            <canvas id="mainChart" height="350"></canvas>
        </div>
    </div>
    
    <!-- Secondary Charts -->
    <div class="row g-4">
        <div class="col-xl-4">
            <div class="card">
                <div class="card-header">
                    <i class="fas fa-chart-pie me-2"></i> Distribution
                </div>
                <div class="card-body">
                    <canvas id="pieChart" height="220"></canvas>
                </div>
            </div>
        </div>
        <div class="col-xl-4">
            <div class="card">
                <div class="card-header">
                    <i class="fas fa-chart-bar me-2"></i> Comparison
                </div>
                <div class="card-body">
                    <canvas id="barChart" height="220"></canvas>
                </div>
            </div>
        </div>
        <div class="col-xl-4">
            <div class="card">
                <div class="card-header">
                    <i class="fas fa-dashboard me-2"></i> Performance
                </div>
                <div class="card-body">
                    <canvas id="gaugeChart" height="220"></canvas>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Chart Legend -->
    <div class="card mt-4">
        <div class="card-header">
            <i class="fas fa-info-circle me-2"></i> Chart Legend
        </div>
        <div class="card-body">
            <div class="row g-3">
                <div class="col-md-6">
                    <div class="legend-item">
                        <span class="legend-color" style="background: #2563EB;"></span>
                        <span class="legend-text">Compliance Score</span>
                    </div>
                    <div class="legend-item">
                        <span class="legend-color" style="background: #EF4444;"></span>
                        <span class="legend-text">Risk Score</span>
                    </div>
                    <div class="legend-item">
                        <span class="legend-color" style="background: #22C55E;"></span>
                        <span class="legend-text">Audit Completion</span>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="legend-item">
                        <span class="legend-color" style="background: #F59E0B;"></span>
                        <span class="legend-text">SBP Compliance</span>
                    </div>
                    <div class="legend-item">
                        <span class="legend-color" style="background: #8B5CF6;"></span>
                        <span class="legend-text">AI Analysis</span>
                    </div>
                    <div class="legend-item">
                        <span class="legend-color" style="background: #EC4899;"></span>
                        <span class="legend-text">User Activity</span>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
.charts-container {
    padding: 0;
}

.chart-controls {
    background: #fff;
    padding: 20px;
    border-radius: 12px;
    box-shadow: 0 1px 3px rgba(0,0,0,0.06);
}

.chart-controls .form-label {
    font-size: 13px;
    font-weight: 500;
    color: #64748B;
    margin-bottom: 4px;
}

.chart-controls .form-select,
.chart-controls .form-control {
    border-radius: 8px;
    border-color: #E2E8F0;
    font-size: 14px;
}

.legend-item {
    display: flex;
    align-items: center;
    padding: 6px 0;
}

.legend-color {
    width: 16px;
    height: 16px;
    border-radius: 4px;
    margin-right: 12px;
    flex-shrink: 0;
}

.legend-text {
    font-size: 14px;
    color: #1E293B;
}

@media (max-width: 768px) {
    .chart-controls .row .col-md-3 {
        margin-bottom: 12px;
    }
}
</style>

<script>
$(document).ready(function() {
    let mainChart = null;
    let pieChart = null;
    let barChart = null;
    let gaugeChart = null;
    
    // Initialize charts
    initCharts();
    
    function initCharts() {
        // Main Chart
        const mainCtx = document.getElementById('mainChart').getContext('2d');
        mainChart = new Chart(mainCtx, {
            type: 'line',
            data: {
                labels: <?php echo json_encode($chart_data['main']['labels'] ?? ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun']); ?>,
                datasets: [
                    {
                        label: 'Compliance Score',
                        data: <?php echo json_encode($chart_data['main']['compliance'] ?? [65, 68, 70, 72, 68, 74]); ?>,
                        borderColor: '#2563EB',
                        backgroundColor: 'rgba(37, 99, 235, 0.1)',
                        fill: true,
                        tension: 0.4,
                        pointRadius: 5,
                        pointBackgroundColor: '#2563EB'
                    },
                    {
                        label: 'Risk Score',
                        data: <?php echo json_encode($chart_data['main']['risk'] ?? [60, 62, 58, 65, 68, 65]); ?>,
                        borderColor: '#EF4444',
                        backgroundColor: 'rgba(239, 68, 68, 0.1)',
                        fill: true,
                        tension: 0.4,
                        pointRadius: 5,
                        pointBackgroundColor: '#EF4444'
                    }
                ]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                interaction: {
                    intersect: false,
                    mode: 'index'
                },
                plugins: {
                    legend: {
                        position: 'top',
                        labels: {
                            usePointStyle: true,
                            boxWidth: 8,
                            padding: 20
                        }
                    },
                    tooltip: {
                        backgroundColor: '#FFFFFF',
                        titleColor: '#1E293B',
                        bodyColor: '#64748B',
                        borderColor: '#E2E8F0',
                        borderWidth: 1,
                        padding: 12,
                        cornerRadius: 8
                    }
                },
                scales: {
                    y: {
                        beginAtZero: true,
                        max: 100,
                        grid: {
                            color: '#F1F5F9'
                        },
                        ticks: {
                            callback: function(value) {
                                return value + '%';
                            }
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
        
        // Pie Chart
        const pieCtx = document.getElementById('pieChart').getContext('2d');
        pieChart = new Chart(pieCtx, {
            type: 'doughnut',
            data: {
                labels: ['Compliant', 'Partially Compliant', 'Non-Compliant'],
                datasets: [{
                    data: [68, 22, 10],
                    backgroundColor: ['#22C55E', '#F59E0B', '#EF4444'],
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
        
        // Bar Chart
        const barCtx = document.getElementById('barChart').getContext('2d');
        barChart = new Chart(barCtx, {
            type: 'bar',
            data: {
                labels: ['Q1', 'Q2', 'Q3', 'Q4'],
                datasets: [
                    {
                        label: 'Audits Completed',
                        data: [18, 22, 20, 29],
                        backgroundColor: '#2563EB',
                        borderRadius: 6
                    },
                    {
                        label: 'Risks Mitigated',
                        data: [12, 15, 18, 22],
                        backgroundColor: '#22C55E',
                        borderRadius: 6
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
        
        // Gauge Chart (Simulated)
        const gaugeCtx = document.getElementById('gaugeChart').getContext('2d');
        gaugeChart = new Chart(gaugeCtx, {
            type: 'doughnut',
            data: {
                labels: ['Progress', 'Remaining'],
                datasets: [{
                    data: [78, 22],
                    backgroundColor: ['#2563EB', '#E2E8F0'],
                    borderWidth: 0
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                cutout: '80%',
                plugins: {
                    legend: {
                        display: false
                    },
                    tooltip: {
                        callbacks: {
                            label: function(context) {
                                return context.parsed + '%';
                            }
                        }
                    }
                }
            },
            plugins: [{
                id: 'gaugeText',
                beforeDraw: function(chart) {
                    const width = chart.width;
                    const height = chart.height;
                    const ctx = chart.ctx;
                    
                    ctx.restore();
                    const fontSize = (height / 114).toFixed(2);
                    ctx.font = fontSize + 'em Poppins, sans-serif';
                    ctx.textBaseline = 'middle';
                    
                    const text = '78%';
                    const textX = Math.round((width - ctx.measureText(text).width) / 2);
                    const textY = height / 2;
                    
                    ctx.fillStyle = '#1E293B';
                    ctx.font = 'bold ' + (parseFloat(fontSize) * 2) + 'em Poppins, sans-serif';
                    ctx.fillText(text, textX, textY);
                    ctx.restore();
                }
            }]
        });
    }
    
    // Chart Type Change
    $('#chartType').on('change', function() {
        const type = $(this).val();
        if (mainChart) {
            mainChart.destroy();
            const ctx = document.getElementById('mainChart').getContext('2d');
            mainChart = new Chart(ctx, {
                type: type,
                data: mainChart.data,
                options: mainChart.options
            });
        }
    });
    
    // Update Chart
    $('#updateChartBtn').on('click', function() {
        const source = $('#dataSource').val();
        const period = $('#timePeriod').val();
        
        // Update chart title
        const titles = {
            'compliance': 'Compliance Trend',
            'risk': 'Risk Analysis',
            'audit': 'Audit Performance',
            'sbp': 'SBP Circular Compliance'
        };
        $('#chartTitle').text(titles[source] || 'Compliance Trend');
        
        // Simulate data update
        $(this).html('<i class="fas fa-spinner fa-spin me-2"></i> Updating...');
        setTimeout(function() {
            $('#updateChartBtn').html('<i class="fas fa-sync-alt me-2"></i> Update Chart');
        }, 1500);
    });
});
</script>