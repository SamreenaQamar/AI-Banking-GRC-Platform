<?php
/**
 * AI Dashboard View
 * 
 * @var string $title
 * @var array $stats
 * @var array $chart_data
 * @var array $recommendations
 * @var array $recent_activities
 * @var array $predictions
 */
?>

<?php $page_title = 'AI Dashboard'; ?>
<?php $active_page = 'ai'; ?>

<div class="ai-dashboard-container">
    <!-- Page Header -->
    <div class="page-header">
        <div class="page-header-left">
            <h1 class="page-title">
                <i class="fas fa-robot text-primary me-2"></i> AI Dashboard
            </h1>
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="/dashboard">Dashboard</a></li>
                    <li class="breadcrumb-item active" aria-current="page">AI Dashboard</li>
                </ol>
            </nav>
        </div>
        <div class="page-header-right">
            <div class="quick-actions">
                <button class="btn btn-primary btn-sm" id="refreshAI">
                    <i class="fas fa-sync-alt me-1"></i> Refresh
                </button>
                <button class="btn btn-outline-primary btn-sm" data-bs-toggle="modal" data-bs-target="#aiChatModal">
                    <i class="fas fa-comment-dots me-1"></i> Ask AI
                </button>
            </div>
            <div class="last-updated">
                <i class="far fa-clock"></i>
                <span>Last updated: <?php echo date('h:i A'); ?></span>
            </div>
        </div>
    </div>

    <!-- Stats Widgets -->
    <div class="row g-4 mb-4">
        <div class="col-xl-3 col-lg-6">
            <div class="widget-card">
                <div class="widget-header">
                    <span class="widget-title">AI Requests</span>
                    <div class="widget-icon" style="background: rgba(37, 99, 235, 0.1); color: #2563EB;">
                        <i class="fas fa-brain"></i>
                    </div>
                </div>
                <div class="widget-value"><?php echo $stats['total_requests'] ?? 0; ?></div>
                <div class="widget-change positive">
                    <i class="fas fa-arrow-up"></i> <?php echo $stats['requests_change'] ?? 0; ?>% from last month
                </div>
                <div class="progress mt-2" style="height: 6px;">
                    <div class="progress-bar" style="width: <?php echo $stats['requests_progress'] ?? 75; ?>%; background: #2563EB;"></div>
                </div>
            </div>
        </div>
        <div class="col-xl-3 col-lg-6">
            <div class="widget-card">
                <div class="widget-header">
                    <span class="widget-title">Prediction Accuracy</span>
                    <div class="widget-icon" style="background: rgba(34, 197, 94, 0.1); color: #22C55E;">
                        <i class="fas fa-chart-line"></i>
                    </div>
                </div>
                <div class="widget-value"><?php echo $stats['accuracy'] ?? 94; ?>%</div>
                <div class="widget-change positive">
                    <i class="fas fa-arrow-up"></i> <?php echo $stats['accuracy_change'] ?? 2.5; ?>% improvement
                </div>
                <div class="progress mt-2" style="height: 6px;">
                    <div class="progress-bar" style="width: <?php echo $stats['accuracy'] ?? 94; ?>%; background: #22C55E;"></div>
                </div>
            </div>
        </div>
        <div class="col-xl-3 col-lg-6">
            <div class="widget-card">
                <div class="widget-header">
                    <span class="widget-title">High Risks Detected</span>
                    <div class="widget-icon" style="background: rgba(239, 68, 68, 0.1); color: #EF4444;">
                        <i class="fas fa-exclamation-triangle"></i>
                    </div>
                </div>
                <div class="widget-value"><?php echo $stats['high_risks'] ?? 12; ?></div>
                <div class="widget-change negative">
                    <i class="fas fa-arrow-up"></i> <?php echo $stats['risks_change'] ?? 3; ?> from last week
                </div>
                <div class="progress mt-2" style="height: 6px;">
                    <div class="progress-bar" style="width: <?php echo ($stats['high_risks'] ?? 12) * 5; ?>%; background: #EF4444;"></div>
                </div>
            </div>
        </div>
        <div class="col-xl-3 col-lg-6">
            <div class="widget-card">
                <div class="widget-header">
                    <span class="widget-title">Resolved Issues</span>
                    <div class="widget-icon" style="background: rgba(34, 197, 94, 0.1); color: #22C55E;">
                        <i class="fas fa-check-circle"></i>
                    </div>
                </div>
                <div class="widget-value"><?php echo $stats['resolved'] ?? 89; ?></div>
                <div class="widget-change positive">
                    <i class="fas fa-arrow-up"></i> <?php echo $stats['resolved_change'] ?? 12; ?>% resolved
                </div>
                <div class="progress mt-2" style="height: 6px;">
                    <div class="progress-bar" style="width: <?php echo $stats['resolved'] ?? 89; ?>%; background: #22C55E;"></div>
                </div>
            </div>
        </div>
    </div>

    <!-- Charts Row -->
    <div class="row g-4 mb-4">
        <div class="col-xl-8">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <span><i class="fas fa-chart-area me-2"></i> AI Performance Trends</span>
                    <div class="btn-group btn-group-sm">
                        <button class="btn btn-outline-primary active" data-period="week">Week</button>
                        <button class="btn btn-outline-primary" data-period="month">Month</button>
                        <button class="btn btn-outline-primary" data-period="quarter">Quarter</button>
                    </div>
                </div>
                <div class="card-body">
                    <canvas id="aiTrendChart" height="250"></canvas>
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

    <!-- Predictions & Recommendations -->
    <div class="row g-4">
        <!-- AI Predictions -->
        <div class="col-xl-6">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <span><i class="fas fa-crystal-ball me-2"></i> AI Predictions</span>
                    <span class="badge bg-primary"><?php echo count($predictions ?? []); ?> new</span>
                </div>
                <div class="card-body p-0">
                    <div class="predictions-list">
                        <?php if (!empty($predictions)): ?>
                            <?php foreach ($predictions as $prediction): ?>
                                <div class="prediction-item <?php echo $prediction->confidence >= 80 ? 'high' : 'medium'; ?>">
                                    <div class="prediction-icon">
                                        <i class="fas fa-<?php echo $prediction->icon ?? 'brain'; ?>"></i>
                                    </div>
                                    <div class="prediction-content">
                                        <div class="prediction-title"><?php echo htmlspecialchars($prediction->title); ?></div>
                                        <div class="prediction-meta">
                                            <span class="confidence"><?php echo $prediction->confidence; ?>% confidence</span>
                                            <span class="prediction-date"><?php echo timeAgo($prediction->created_at); ?></span>
                                        </div>
                                    </div>
                                    <div class="prediction-status">
                                        <span class="badge bg-<?php echo $prediction->confidence >= 80 ? 'success' : 'warning'; ?>">
                                            <?php echo $prediction->confidence >= 80 ? 'High' : 'Medium'; ?>
                                        </span>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <div class="text-center text-muted py-4">
                                <i class="fas fa-inbox fa-2x mb-2"></i>
                                <p>No predictions available</p>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>

        <!-- AI Recommendations -->
        <div class="col-xl-6">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <span><i class="fas fa-lightbulb me-2"></i> AI Recommendations</span>
                    <a href="/ai/recommendations" class="btn btn-sm btn-outline-primary">View All</a>
                </div>
                <div class="card-body p-0">
                    <div class="recommendations-list">
                        <?php if (!empty($recommendations)): ?>
                            <?php foreach (array_slice($recommendations, 0, 5) as $rec): ?>
                                <div class="recommendation-item priority-<?php echo $rec->priority; ?>">
                                    <div class="recommendation-icon">
                                        <i class="fas fa-<?php echo $rec->icon ?? 'check'; ?>"></i>
                                    </div>
                                    <div class="recommendation-content">
                                        <div class="recommendation-title"><?php echo htmlspecialchars($rec->title); ?></div>
                                        <div class="recommendation-description"><?php echo htmlspecialchars($rec->description); ?></div>
                                    </div>
                                    <div class="recommendation-actions">
                                        <button class="btn btn-sm btn-outline-success accept-rec" data-id="<?php echo $rec->id; ?>">
                                            <i class="fas fa-check"></i>
                                        </button>
                                        <button class="btn btn-sm btn-outline-danger dismiss-rec" data-id="<?php echo $rec->id; ?>">
                                            <i class="fas fa-times"></i>
                                        </button>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <div class="text-center text-muted py-4">
                                <i class="fas fa-lightbulb fa-2x mb-2"></i>
                                <p>No recommendations available</p>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
.ai-dashboard-container {
    padding: 0;
}

.predictions-list {
    padding: 8px 0;
}

.prediction-item {
    display: flex;
    align-items: center;
    padding: 12px 16px;
    border-bottom: 1px solid var(--border-light);
    gap: 14px;
}

.prediction-item.high {
    border-left: 3px solid #22C55E;
}

.prediction-item.medium {
    border-left: 3px solid #F59E0B;
}

.prediction-icon {
    width: 36px;
    height: 36px;
    border-radius: 50%;
    background: #DBEAFE;
    color: #2563EB;
    display: flex;
    align-items: center;
    justify-content: center;
    flex-shrink: 0;
}

.prediction-content {
    flex: 1;
}

.prediction-title {
    font-weight: 500;
    color: var(--text-dark);
    font-size: 14px;
}

.prediction-meta {
    display: flex;
    gap: 12px;
    font-size: 12px;
    color: var(--text-muted);
}

.prediction-status {
    flex-shrink: 0;
}

.recommendations-list {
    padding: 8px 0;
}

.recommendation-item {
    display: flex;
    align-items: flex-start;
    padding: 12px 16px;
    border-bottom: 1px solid var(--border-light);
    gap: 14px;
}

.recommendation-item.priority-high {
    border-left: 3px solid #EF4444;
}

.recommendation-item.priority-medium {
    border-left: 3px solid #F59E0B;
}

.recommendation-item.priority-low {
    border-left: 3px solid #22C55E;
}

.recommendation-icon {
    width: 32px;
    height: 32px;
    border-radius: 50%;
    background: #FEF3C7;
    color: #F59E0B;
    display: flex;
    align-items: center;
    justify-content: center;
    flex-shrink: 0;
    font-size: 14px;
}

.recommendation-content {
    flex: 1;
}

.recommendation-title {
    font-weight: 500;
    color: var(--text-dark);
    font-size: 14px;
}

.recommendation-description {
    font-size: 13px;
    color: var(--text-light);
}

.recommendation-actions {
    display: flex;
    gap: 4px;
    flex-shrink: 0;
}

@media (max-width: 768px) {
    .prediction-item,
    .recommendation-item {
        flex-wrap: wrap;
    }
    
    .prediction-status {
        width: 100%;
        margin-left: 50px;
    }
}
</style>

<script>
$(document).ready(function() {
    // AI Trend Chart
    const trendCtx = document.getElementById('aiTrendChart').getContext('2d');
    new Chart(trendCtx, {
        type: 'line',
        data: {
            labels: ['Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat', 'Sun'],
            datasets: [
                {
                    label: 'Predictions',
                    data: [12, 19, 15, 22, 18, 25, 30],
                    borderColor: '#2563EB',
                    backgroundColor: 'rgba(37, 99, 235, 0.1)',
                    fill: true,
                    tension: 0.4
                },
                {
                    label: 'Accuracy',
                    data: [85, 88, 92, 90, 94, 91, 95],
                    borderColor: '#22C55E',
                    backgroundColor: 'rgba(34, 197, 94, 0.1)',
                    fill: true,
                    tension: 0.4
                },
                {
                    label: 'Resolved Issues',
                    data: [5, 8, 6, 10, 7, 12, 9],
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

    // Risk Distribution Chart
    const riskCtx = document.getElementById('riskDistributionChart').getContext('2d');
    new Chart(riskCtx, {
        type: 'doughnut',
        data: {
            labels: ['Critical', 'High', 'Medium', 'Low'],
            datasets: [{
                data: [12, 23, 45, 76],
                backgroundColor: ['#DC2626', '#EF4444', '#F59E0B', '#22C55E'],
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

    // Period buttons
    $('[data-period]').on('click', function() {
        $('[data-period]').removeClass('active');
        $(this).addClass('active');
        // Refresh chart with new period
    });

    // Accept recommendation
    $('.accept-rec').on('click', function() {
        const id = $(this).data('id');
        const item = $(this).closest('.recommendation-item');
        
        $.ajax({
            url: '/api/ai/recommendations/' + id + '/accept',
            method: 'POST',
            data: {
                _csrf: '<?php echo $csrf_token ?? ''; ?>'
            },
            success: function(response) {
                if (response.success) {
                    item.fadeOut(300, function() { $(this).remove(); });
                    showToast('Recommendation accepted', 'success');
                }
            }
        });
    });

    // Dismiss recommendation
    $('.dismiss-rec').on('click', function() {
        const id = $(this).data('id');
        const item = $(this).closest('.recommendation-item');
        
        $.ajax({
            url: '/api/ai/recommendations/' + id + '/dismiss',
            method: 'POST',
            data: {
                _csrf: '<?php echo $csrf_token ?? ''; ?>'
            },
            success: function(response) {
                if (response.success) {
                    item.fadeOut(300, function() { $(this).remove(); });
                    showToast('Recommendation dismissed', 'info');
                }
            }
        });
    });

    // Refresh button
    $('#refreshAI').on('click', function() {
        const btn = $(this);
        btn.html('<i class="fas fa-spinner fa-spin me-1"></i> Refreshing...');
        setTimeout(function() {
            location.reload();
        }, 1000);
    });
});

// Helper function for time ago
function timeAgo(date) {
    const diff = Math.floor((Date.now() - new Date(date)) / 1000);
    if (diff < 60) return 'Just now';
    if (diff < 3600) return Math.floor(diff / 60) + ' minutes ago';
    if (diff < 86400) return Math.floor(diff / 3600) + ' hours ago';
    return Math.floor(diff / 86400) + ' days ago';
}

// Toast notification
function showToast(message, type) {
    const toast = $(`
        <div class="toast-notification ${type}">
            <i class="fas fa-${type === 'success' ? 'check-circle' : type === 'error' ? 'exclamation-circle' : 'info-circle'}"></i>
            ${message}
        </div>
    `);
    $('body').append(toast);
    setTimeout(() => {
        toast.fadeOut(300, function() { $(this).remove(); });
    }, 3000);
}
</script>