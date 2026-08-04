<?php
/**
 * AI Risk Analyzer View
 * 
 * @var string $title
 * @var array $risks
 * @var array $stats
 * @var array $heatmap_data
 * @var array $chart_data
 */
?>

<?php $page_title = 'AI Risk Analyzer'; ?>
<?php $active_page = 'ai'; ?>

<div class="risk-analyzer-container">
    <!-- Page Header -->
    <div class="page-header">
        <div class="page-header-left">
            <h1 class="page-title">
                <i class="fas fa-shield-alt text-primary me-2"></i> AI Risk Analyzer
            </h1>
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="/dashboard">Dashboard</a></li>
                    <li class="breadcrumb-item"><a href="/ai">AI</a></li>
                    <li class="breadcrumb-item active" aria-current="page">Risk Analyzer</li>
                </ol>
            </nav>
        </div>
        <div class="page-header-right">
            <div class="quick-actions">
                <button class="btn btn-primary btn-sm" data-bs-toggle="modal" data-bs-target="#analyzeRiskModal">
                    <i class="fas fa-plus me-1"></i> Analyze Risk
                </button>
                <button class="btn btn-outline-primary btn-sm" id="exportRiskReport">
                    <i class="fas fa-download me-1"></i> Export
                </button>
            </div>
        </div>
    </div>

    <!-- Stats Cards -->
    <div class="row g-4 mb-4">
        <div class="col-xl-3 col-lg-6">
            <div class="stat-card">
                <div class="stat-icon" style="background: rgba(37, 99, 235, 0.1); color: #2563EB;">
                    <i class="fas fa-list"></i>
                </div>
                <div class="stat-info">
                    <div class="stat-value"><?php echo $stats['total'] ?? 0; ?></div>
                    <div class="stat-label">Total Risks</div>
                </div>
            </div>
        </div>
        <div class="col-xl-3 col-lg-6">
            <div class="stat-card">
                <div class="stat-icon" style="background: rgba(239, 68, 68, 0.1); color: #EF4444;">
                    <i class="fas fa-exclamation-triangle"></i>
                </div>
                <div class="stat-info">
                    <div class="stat-value text-danger"><?php echo $stats['critical'] ?? 0; ?></div>
                    <div class="stat-label">Critical Risks</div>
                </div>
            </div>
        </div>
        <div class="col-xl-3 col-lg-6">
            <div class="stat-card">
                <div class="stat-icon" style="background: rgba(34, 197, 94, 0.1); color: #22C55E;">
                    <i class="fas fa-shield-alt"></i>
                </div>
                <div class="stat-info">
                    <div class="stat-value"><?php echo $stats['mitigated'] ?? 0; ?>%</div>
                    <div class="stat-label">Mitigation Rate</div>
                </div>
            </div>
        </div>
        <div class="col-xl-3 col-lg-6">
            <div class="stat-card">
                <div class="stat-icon" style="background: rgba(245, 158, 11, 0.1); color: #F59E0B;">
                    <i class="fas fa-chart-line"></i>
                </div>
                <div class="stat-info">
                    <div class="stat-value"><?php echo $stats['avg_score'] ?? 0; ?>%</div>
                    <div class="stat-label">Average Risk Score</div>
                </div>
            </div>
        </div>
    </div>

    <!-- Heatmap & Matrix -->
    <div class="row g-4 mb-4">
        <div class="col-xl-8">
            <div class="card">
                <div class="card-header">
                    <i class="fas fa-fire me-2"></i> Risk Heatmap
                </div>
                <div class="card-body">
                    <div class="heatmap-wrapper">
                        <div class="heatmap-grid">
                            <?php for ($impact = 5; $impact >= 1; $impact--): ?>
                                <div class="heatmap-row">
                                    <div class="heatmap-label">
                                        <?php echo ['Very High', 'High', 'Medium', 'Low', 'Very Low'][$impact - 1]; ?>
                                    </div>
                                    <?php for ($likelihood = 1; $likelihood <= 5; $likelihood++): ?>
                                        <?php
                                        $count = $heatmap_data[$impact][$likelihood] ?? 0;
                                        $level = $impact * $likelihood;
                                        $color = $count > 0 ? 
                                            ($level >= 20 ? '#DC2626' : 
                                             ($level >= 12 ? '#EF4444' : 
                                              ($level >= 8 ? '#F59E0B' : 
                                               ($level >= 4 ? '#3B82F6' : '#22C55E')))) : '#F1F5F9';
                                        ?>
                                        <div class="heatmap-cell <?php echo $count > 0 ? 'active' : ''; ?>" 
                                             style="background: <?php echo $color; ?>;"
                                             data-impact="<?php echo $impact; ?>"
                                             data-likelihood="<?php echo $likelihood; ?>"
                                             data-count="<?php echo $count; ?>"
                                             title="<?php echo $count > 0 ? $count . ' risks' : 'No risks'; ?>">
                                            <?php if ($count > 0): ?>
                                                <span class="cell-count"><?php echo $count; ?></span>
                                            <?php endif; ?>
                                        </div>
                                    <?php endfor; ?>
                                </div>
                            <?php endfor; ?>
                            <div class="heatmap-labels">
                                <span></span>
                                <span>Very Low</span>
                                <span>Low</span>
                                <span>Medium</span>
                                <span>High</span>
                                <span>Very High</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-xl-4">
            <div class="card">
                <div class="card-header">
                    <i class="fas fa-chart-pie me-2"></i> Severity Distribution
                </div>
                <div class="card-body">
                    <canvas id="severityChart" height="250"></canvas>
                </div>
            </div>
        </div>
    </div>

    <!-- Risk Table -->
    <div class="card">
        <div class="card-header d-flex justify-content-between align-items-center">
            <span><i class="fas fa-table me-2"></i> Risk Analysis Results</span>
            <div>
                <span class="badge bg-danger me-1">Critical: <?php echo $stats['critical'] ?? 0; ?></span>
                <span class="badge bg-warning me-1">High: <?php echo $stats['high'] ?? 0; ?></span>
                <span class="badge bg-info me-1">Medium: <?php echo $stats['medium'] ?? 0; ?></span>
                <span class="badge bg-secondary">Low: <?php echo $stats['low'] ?? 0; ?></span>
            </div>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover enterprise-table mb-0">
                    <thead>
                        <tr>
                            <th>Risk ID</th>
                            <th>Title</th>
                            <th>Category</th>
                            <th>Likelihood</th>
                            <th>Impact</th>
                            <th>Score</th>
                            <th>Level</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (!empty($risks)): ?>
                            <?php foreach ($risks as $risk): ?>
                                <tr>
                                    <td><span class="risk-code"><?php echo htmlspecialchars($risk->risk_code); ?></span></td>
                                    <td><?php echo htmlspecialchars(substr($risk->title, 0, 40)) . '...'; ?></td>
                                    <td><?php echo htmlspecialchars($risk->category_name ?? 'N/A'); ?></td>
                                    <td><?php echo $risk->inherent_likelihood ?? 0; ?>/5</td>
                                    <td><?php echo $risk->inherent_impact ?? 0; ?>/5</td>
                                    <td><strong><?php echo $risk->inherent_risk_score ?? 0; ?>%</strong></td>
                                    <td>
                                        <span class="risk-level <?php echo $risk->risk_level ?? 'low'; ?>">
                                            <?php echo ucfirst($risk->risk_level ?? 'Low'); ?>
                                        </span>
                                    </td>
                                    <td>
                                        <div class="btn-group btn-group-sm">
                                            <button class="btn btn-outline-primary view-risk" data-id="<?php echo $risk->id; ?>">
                                                <i class="fas fa-eye"></i>
                                            </button>
                                            <button class="btn btn-outline-success assess-risk" data-id="<?php echo $risk->id; ?>">
                                                <i class="fas fa-clipboard-check"></i>
                                            </button>
                                            <button class="btn btn-outline-warning mitigate-risk" data-id="<?php echo $risk->id; ?>">
                                                <i class="fas fa-shield"></i>
                                            </button>
                                        </div>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="8" class="text-center py-4 text-muted">
                                    <i class="fas fa-inbox fa-2x d-block mb-2"></i>
                                    No risks found
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
                <a class="page-link" href="?page=<?php echo $page - 1; ?>">Previous</a>
            </li>
            <?php for ($i = 1; $i <= $total_pages; $i++): ?>
                <li class="page-item <?php echo $i == $page ? 'active' : ''; ?>">
                    <a class="page-link" href="?page=<?php echo $i; ?>"><?php echo $i; ?></a>
                </li>
            <?php endfor; ?>
            <li class="page-item <?php echo $page >= $total_pages ? 'disabled' : ''; ?>">
                <a class="page-link" href="?page=<?php echo $page + 1; ?>">Next</a>
            </li>
        </ul>
    </nav>
    <?php endif; ?>
</div>

<!-- Analyze Risk Modal -->
<div class="modal fade" id="analyzeRiskModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title"><i class="fas fa-plus-circle me-2"></i> Analyze New Risk</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form id="analyzeRiskForm">
                <div class="modal-body">
                    <input type="hidden" name="csrf_token" value="<?php echo $csrf_token ?? ''; ?>">
                    
                    <div class="form-group mb-3">
                        <label class="form-label">Risk Description</label>
                        <textarea class="form-control" name="description" rows="4" 
                                  placeholder="Describe the risk in detail..." required></textarea>
                    </div>
                    
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label">Category</label>
                            <select class="form-select" name="category">
                                <option value="operational">Operational</option>
                                <option value="financial">Financial</option>
                                <option value="compliance">Compliance</option>
                                <option value="strategic">Strategic</option>
                                <option value="cyber">Cyber</option>
                                <option value="reputational">Reputational</option>
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Department</label>
                            <select class="form-select" name="department">
                                <option value="">Select Department</option>
                                <?php foreach ($departments ?? [] as $dept): ?>
                                    <option value="<?php echo $dept->id; ?>"><?php echo htmlspecialchars($dept->name); ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">Analyze Risk</button>
                </div>
            </form>
        </div>
    </div>
</div>

<style>
.risk-analyzer-container {
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
    height: 100%;
    transition: all 0.3s;
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

.heatmap-wrapper {
    padding: 10px 0;
    overflow-x: auto;
}

.heatmap-grid {
    display: grid;
    gap: 4px;
    min-width: 500px;
}

.heatmap-row {
    display: grid;
    grid-template-columns: 100px repeat(5, 1fr);
    gap: 4px;
    align-items: center;
}

.heatmap-label {
    font-size: 13px;
    color: #64748B;
    font-weight: 500;
    text-align: right;
    padding-right: 12px;
}

.heatmap-cell {
    aspect-ratio: 1;
    border-radius: 4px;
    display: flex;
    align-items: center;
    justify-content: center;
    min-height: 40px;
    transition: all 0.2s;
}

.heatmap-cell.active {
    cursor: pointer;
    box-shadow: 0 2px 4px rgba(0,0,0,0.1);
}

.heatmap-cell.active:hover {
    transform: scale(1.05);
    box-shadow: 0 4px 12px rgba(0,0,0,0.2);
    z-index: 1;
}

.heatmap-cell .cell-count {
    font-size: 14px;
    font-weight: 700;
    color: #fff;
    text-shadow: 0 1px 3px rgba(0,0,0,0.3);
}

.heatmap-labels {
    display: grid;
    grid-template-columns: 100px repeat(5, 1fr);
    gap: 4px;
    margin-top: 8px;
    padding-left: 0;
    font-size: 12px;
    color: #94A3B8;
}

.heatmap-labels span {
    text-align: center;
}

.risk-code {
    font-family: 'Courier New', monospace;
    color: #2563EB;
    font-weight: 600;
    font-size: 13px;
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
    .stat-value {
        font-size: 20px;
    }
}
</style>

<script>
$(document).ready(function() {
    // Severity Chart
    const severityCtx = document.getElementById('severityChart').getContext('2d');
    new Chart(severityCtx, {
        type: 'doughnut',
        data: {
            labels: ['Critical', 'High', 'Medium', 'Low'],
            datasets: [{
                data: <?php echo json_encode($chart_data['severity'] ?? [12, 23, 45, 76]); ?>,
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

    // Heatmap cell click
    $('.heatmap-cell.active').on('click', function() {
        const impact = $(this).data('impact');
        const likelihood = $(this).data('likelihood');
        window.location.href = '/risk/register?impact=' + impact + '&likelihood=' + likelihood;
    });

    // Analyze risk form
    $('#analyzeRiskForm').on('submit', function(e) {
        e.preventDefault();
        const btn = $(this).find('button[type="submit"]');
        const originalText = btn.text();
        btn.html('<span class="spinner-border spinner-border-sm me-2"></span> Analyzing...');
        btn.prop('disabled', true);

        const csrfToken = $('input[name="csrf_token"]').val();
        const description = $('textarea[name="description"]').val();
        const category = $('select[name="category"]').val();
        const department = $('select[name="department"]').val();

        $.ajax({
            url: '/api/ai/risk/analyze',
            method: 'POST',
            data: {
                _csrf: csrfToken,
                description: description,
                category: category,
                department: department
            },
            success: function(response) {
                if (response.success) {
                    showToast('Risk analyzed successfully!', 'success');
                    setTimeout(function() {
                        location.reload();
                    }, 1500);
                } else {
                    showToast(response.message || 'Analysis failed', 'error');
                    btn.html(originalText);
                    btn.prop('disabled', false);
                }
            },
            error: function() {
                showToast('An error occurred', 'error');
                btn.html(originalText);
                btn.prop('disabled', false);
            }
        });
    });

    // View risk details
    $('.view-risk').on('click', function() {
        const id = $(this).data('id');
        window.location.href = '/risk/' + id;
    });

    // Toast notification
    function showToast(message, type) {
        const toast = $(`
            <div class="toast-notification ${type}">
                <i class="fas fa-${type === 'success' ? 'check-circle' : 'exclamation-circle'}"></i>
                ${message}
            </div>
        `);
        $('body').append(toast);
        setTimeout(() => {
            toast.fadeOut(300, function() { $(this).remove(); });
        }, 3000);
    }
});
</script>