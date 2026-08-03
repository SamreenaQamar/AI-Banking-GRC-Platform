<?php
/**
 * Executive Report Page
 * 
 * @var string $title
 * @var array $report_data
 * @var array $executive_metrics
 * @var array $insights
 */
?>

<?php $page_title = 'Executive Report'; ?>
<?php $active_page = 'reports'; ?>

<div class="report-container">
    <!-- Report Header -->
    <div class="report-header mb-4">
        <div class="row">
            <div class="col-md-8">
                <h5><i class="fas fa-chart-line me-2 text-primary"></i> Executive Report</h5>
                <p class="text-muted">High-level GRC overview for leadership</p>
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
    
    <!-- Executive Summary Cards -->
    <div class="row g-4 mb-4">
        <div class="col-xl-3 col-lg-6">
            <div class="executive-card">
                <div class="executive-icon" style="background: #2563EB;">
                    <i class="fas fa-check-circle"></i>
                </div>
                <div class="executive-info">
                    <div class="executive-label">Compliance Score</div>
                    <div class="executive-value"><?php echo $executive_metrics['compliance_score'] ?? 68; ?>%</div>
                    <div class="executive-trend <?php echo ($executive_metrics['compliance_trend'] ?? 0) > 0 ? 'positive' : 'negative'; ?>">
                        <i class="fas fa-arrow-<?php echo ($executive_metrics['compliance_trend'] ?? 0) > 0 ? 'up' : 'down'; ?>"></i>
                        <?php echo abs($executive_metrics['compliance_trend'] ?? 0); ?>% from last quarter
                    </div>
                </div>
            </div>
        </div>
        <div class="col-xl-3 col-lg-6">
            <div class="executive-card">
                <div class="executive-icon" style="background: #EF4444;">
                    <i class="fas fa-shield-alt"></i>
                </div>
                <div class="executive-info">
                    <div class="executive-label">Risk Score</div>
                    <div class="executive-value"><?php echo $executive_metrics['risk_score'] ?? 65; ?>%</div>
                    <div class="executive-trend <?php echo ($executive_metrics['risk_trend'] ?? 0) < 0 ? 'positive' : 'negative'; ?>">
                        <i class="fas fa-arrow-<?php echo ($executive_metrics['risk_trend'] ?? 0) < 0 ? 'down' : 'up'; ?>"></i>
                        <?php echo abs($executive_metrics['risk_trend'] ?? 0); ?>% from last quarter
                    </div>
                </div>
            </div>
        </div>
        <div class="col-xl-3 col-lg-6">
            <div class="executive-card">
                <div class="executive-icon" style="background: #22C55E;">
                    <i class="fas fa-clipboard-list"></i>
                </div>
                <div class="executive-info">
                    <div class="executive-label">Audit Resolution</div>
                    <div class="executive-value"><?php echo $executive_metrics['audit_resolution'] ?? 78; ?>%</div>
                    <div class="executive-trend <?php echo ($executive_metrics['audit_trend'] ?? 0) > 0 ? 'positive' : 'negative'; ?>">
                        <i class="fas fa-arrow-<?php echo ($executive_metrics['audit_trend'] ?? 0) > 0 ? 'up' : 'down'; ?>"></i>
                        <?php echo abs($executive_metrics['audit_trend'] ?? 0); ?>% from last quarter
                    </div>
                </div>
            </div>
        </div>
        <div class="col-xl-3 col-lg-6">
            <div class="executive-card">
                <div class="executive-icon" style="background: #F59E0B;">
                    <i class="fas fa-flag"></i>
                </div>
                <div class="executive-info">
                    <div class="executive-label">Open Issues</div>
                    <div class="executive-value"><?php echo $executive_metrics['open_issues'] ?? 23; ?></div>
                    <div class="executive-trend <?php echo ($executive_metrics['issues_trend'] ?? 0) < 0 ? 'positive' : 'negative'; ?>">
                        <i class="fas fa-arrow-<?php echo ($executive_metrics['issues_trend'] ?? 0) < 0 ? 'down' : 'up'; ?>"></i>
                        <?php echo abs($executive_metrics['issues_trend'] ?? 0); ?> from last quarter
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Key Insights -->
    <div class="row g-4 mb-4">
        <div class="col-xl-8">
            <div class="card">
                <div class="card-header">
                    <i class="fas fa-chart-area me-2"></i> GRC Performance Trend
                </div>
                <div class="card-body">
                    <canvas id="grcTrendChart" height="250"></canvas>
                </div>
            </div>
        </div>
        <div class="col-xl-4">
            <div class="card">
                <div class="card-header">
                    <i class="fas fa-lightbulb me-2"></i> Key Insights
                </div>
                <div class="card-body">
                    <?php if (!empty($insights)): ?>
                        <?php foreach ($insights as $insight): ?>
                            <div class="insight-item <?php echo $insight->type; ?>">
                                <div class="insight-icon">
                                    <i class="fas fa-<?php echo $insight->icon ?? 'circle'; ?>"></i>
                                </div>
                                <div class="insight-content">
                                    <div class="insight-title"><?php echo htmlspecialchars($insight->title); ?></div>
                                    <div class="insight-description"><?php echo htmlspecialchars($insight->description); ?></div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <div class="text-center text-muted py-3">No insights available</div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Executive Recommendations -->
    <div class="card">
        <div class="card-header">
            <i class="fas fa-list-check me-2"></i> Recommendations for Leadership
        </div>
        <div class="card-body">
            <div class="row g-3">
                <?php if (!empty($report_data['recommendations'])): ?>
                    <?php foreach ($report_data['recommendations'] as $rec): ?>
                        <div class="col-md-6">
                            <div class="recommendation-item priority-<?php echo $rec->priority; ?>">
                                <div class="recommendation-header">
                                    <span class="recommendation-badge badge bg-<?php echo $rec->priority === 'critical' ? 'danger' : ($rec->priority === 'high' ? 'warning' : 'info'); ?>">
                                        <?php echo ucfirst($rec->priority); ?>
                                    </span>
                                    <span class="recommendation-category"><?php echo ucfirst($rec->category); ?></span>
                                </div>
                                <div class="recommendation-text"><?php echo htmlspecialchars($rec->text); ?></div>
                                <div class="recommendation-meta">
                                    <span><i class="fas fa-user me-1"></i> <?php echo htmlspecialchars($rec->owner ?? 'Unassigned'); ?></span>
                                    <span><i class="far fa-calendar-alt me-1"></i> <?php echo date('d M Y', strtotime($rec->deadline ?? 'now')); ?></span>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                <?php else: ?>
                    <div class="col-12 text-center text-muted py-3">No recommendations available</div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<style>
.report-container {
    padding: 0;
}

.executive-card {
    background: #fff;
    padding: 20px;
    border-radius: 12px;
    box-shadow: 0 1px 3px rgba(0,0,0,0.06);
    display: flex;
    align-items: center;
    gap: 16px;
    height: 100%;
    transition: all 0.3s;
}

.executive-card:hover {
    box-shadow: 0 4px 12px rgba(0,0,0,0.1);
    transform: translateY(-2px);
}

.executive-icon {
    width: 48px;
    height: 48px;
    border-radius: 10px;
    display: flex;
    align-items: center;
    justify-content: center;
    color: #fff;
    font-size: 20px;
    flex-shrink: 0;
}

.executive-info {
    flex: 1;
}

.executive-label {
    font-size: 13px;
    color: #64748B;
}

.executive-value {
    font-size: 24px;
    font-weight: 700;
    color: #1E293B;
}

.executive-trend {
    font-size: 13px;
    font-weight: 500;
}

.executive-trend.positive { color: #22C55E; }
.executive-trend.negative { color: #EF4444; }

.insight-item {
    display: flex;
    gap: 12px;
    padding: 12px 0;
    border-bottom: 1px solid #F1F5F9;
}

.insight-item:last-child {
    border-bottom: none;
}

.insight-icon {
    width: 32px;
    height: 32px;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    flex-shrink: 0;
    font-size: 14px;
}

.insight-item.positive .insight-icon { background: #D1FAE5; color: #10B981; }
.insight-item.warning .insight-icon { background: #FEF3C7; color: #F59E0B; }
.insight-item.negative .insight-icon { background: #FEE2E2; color: #EF4444; }
.insight-item.info .insight-icon { background: #DBEAFE; color: #3B82F6; }

.insight-title {
    font-weight: 500;
    color: #1E293B;
    font-size: 14px;
}

.insight-description {
    font-size: 13px;
    color: #64748B;
}

.recommendation-item {
    background: #F8FAFC;
    padding: 16px;
    border-radius: 10px;
    border-left: 4px solid #E2E8F0;
    height: 100%;
}

.recommendation-item.priority-critical { border-left-color: #DC2626; }
.recommendation-item.priority-high { border-left-color: #F59E0B; }
.recommendation-item.priority-medium { border-left-color: #3B82F6; }
.recommendation-item.priority-low { border-left-color: #22C55E; }

.recommendation-header {
    display: flex;
    gap: 8px;
    margin-bottom: 8px;
}

.recommendation-text {
    font-size: 14px;
    color: #1E293B;
    margin-bottom: 8px;
}

.recommendation-meta {
    display: flex;
    gap: 16px;
    font-size: 12px;
    color: #94A3B8;
}

@media (max-width: 768px) {
    .executive-card {
        padding: 16px;
    }
    
    .executive-value {
        font-size: 20px;
    }
}
</style>

<script>
$(document).ready(function() {
    // GRC Trend Chart
    <?php if (isset($executive_metrics['trend_data'])): ?>
    const trendCtx = document.getElementById('grcTrendChart').getContext('2d');
    new Chart(trendCtx, {
        type: 'line',
        data: {
            labels: <?php echo json_encode($executive_metrics['trend_data']['labels'] ?? ['Q1', 'Q2', 'Q3', 'Q4']); ?>,
            datasets: [
                {
                    label: 'Compliance',
                    data: <?php echo json_encode($executive_metrics['trend_data']['compliance'] ?? [62, 65, 68, 72]); ?>,
                    borderColor: '#2563EB',
                    backgroundColor: 'rgba(37, 99, 235, 0.1)',
                    fill: true,
                    tension: 0.4
                },
                {
                    label: 'Risk',
                    data: <?php echo json_encode($executive_metrics['trend_data']['risk'] ?? [58, 60, 63, 65]); ?>,
                    borderColor: '#EF4444',
                    backgroundColor: 'rgba(239, 68, 68, 0.1)',
                    fill: true,
                    tension: 0.4
                },
                {
                    label: 'Audit Resolution',
                    data: <?php echo json_encode($executive_metrics['trend_data']['audit'] ?? [70, 74, 76, 78]); ?>,
                    borderColor: '#22C55E',
                    backgroundColor: 'rgba(34, 197, 94, 0.1)',
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
    
    $('#downloadReport').on('click', function() {
        window.location.href = '<?php echo BASE_URL; ?>/reports/executive/download';
    });
    
    $('#exportExcel').on('click', function() {
        window.location.href = '<?php echo BASE_URL; ?>/reports/executive/export/excel';
    });
});
</script>