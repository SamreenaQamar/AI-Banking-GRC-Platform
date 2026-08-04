<?php
/**
 * AI Gap Analysis View
 * 
 * @var string $title
 * @var array $gaps
 * @var array $stats
 * @var array $chart_data
 * @var array $departments
 */
?>

<?php $page_title = 'AI Gap Analysis'; ?>
<?php $active_page = 'ai'; ?>

<div class="gap-analysis-container">
    <!-- Page Header -->
    <div class="page-header">
        <div class="page-header-left">
            <h1 class="page-title">
                <i class="fas fa-search-plus text-primary me-2"></i> AI Gap Analysis
            </h1>
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="/dashboard">Dashboard</a></li>
                    <li class="breadcrumb-item"><a href="/ai">AI</a></li>
                    <li class="breadcrumb-item active" aria-current="page">Gap Analysis</li>
                </ol>
            </nav>
        </div>
        <div class="page-header-right">
            <div class="quick-actions">
                <button class="btn btn-primary btn-sm" id="runAnalysisBtn">
                    <i class="fas fa-play me-1"></i> Run Analysis
                </button>
                <button class="btn btn-outline-primary btn-sm" id="exportGapReport">
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
                    <div class="stat-value"><?php echo $stats['total_gaps'] ?? 0; ?></div>
                    <div class="stat-label">Total Gaps</div>
                </div>
            </div>
        </div>
        <div class="col-xl-3 col-lg-6">
            <div class="stat-card">
                <div class="stat-icon" style="background: rgba(239, 68, 68, 0.1); color: #EF4444;">
                    <i class="fas fa-exclamation-circle"></i>
                </div>
                <div class="stat-info">
                    <div class="stat-value text-danger"><?php echo $stats['critical_gaps'] ?? 0; ?></div>
                    <div class="stat-label">Critical Gaps</div>
                </div>
            </div>
        </div>
        <div class="col-xl-3 col-lg-6">
            <div class="stat-card">
                <div class="stat-icon" style="background: rgba(245, 158, 11, 0.1); color: #F59E0B;">
                    <i class="fas fa-clock"></i>
                </div>
                <div class="stat-info">
                    <div class="stat-value"><?php echo $stats['in_progress'] ?? 0; ?></div>
                    <div class="stat-label">In Progress</div>
                </div>
            </div>
        </div>
        <div class="col-xl-3 col-lg-6">
            <div class="stat-card">
                <div class="stat-icon" style="background: rgba(34, 197, 94, 0.1); color: #22C55E;">
                    <i class="fas fa-check-circle"></i>
                </div>
                <div class="stat-info">
                    <div class="stat-value text-success"><?php echo $stats['resolved'] ?? 0; ?></div>
                    <div class="stat-label">Resolved</div>
                </div>
            </div>
        </div>
    </div>

    <!-- Filters -->
    <div class="filter-section mb-4">
        <div class="row g-3">
            <div class="col-md-3">
                <select class="form-select" id="filterDepartment">
                    <option value="">All Departments</option>
                    <?php foreach ($departments ?? [] as $dept): ?>
                        <option value="<?php echo $dept->id; ?>"><?php echo htmlspecialchars($dept->name); ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="col-md-3">
                <select class="form-select" id="filterSeverity">
                    <option value="">All Severity</option>
                    <option value="critical">Critical</option>
                    <option value="high">High</option>
                    <option value="medium">Medium</option>
                    <option value="low">Low</option>
                </select>
            </div>
            <div class="col-md-3">
                <select class="form-select" id="filterStatus">
                    <option value="">All Status</option>
                    <option value="open">Open</option>
                    <option value="in_progress">In Progress</option>
                    <option value="resolved">Resolved</option>
                    <option value="closed">Closed</option>
                </select>
            </div>
            <div class="col-md-3">
                <div class="search-box">
                    <i class="fas fa-search"></i>
                    <input type="text" class="form-control" id="searchGap" placeholder="Search gaps...">
                </div>
            </div>
        </div>
    </div>

    <!-- Charts Row -->
    <div class="row g-4 mb-4">
        <div class="col-xl-6">
            <div class="card">
                <div class="card-header">
                    <i class="fas fa-chart-bar me-2"></i> Gap Distribution by Severity
                </div>
                <div class="card-body">
                    <canvas id="gapSeverityChart" height="250"></canvas>
                </div>
            </div>
        </div>
        <div class="col-xl-6">
            <div class="card">
                <div class="card-header">
                    <i class="fas fa-chart-pie me-2"></i> Gap Status Distribution
                </div>
                <div class="card-body">
                    <canvas id="gapStatusChart" height="250"></canvas>
                </div>
            </div>
        </div>
    </div>

    <!-- Gaps List -->
    <div class="card">
        <div class="card-header d-flex justify-content-between align-items-center">
            <span><i class="fas fa-list-check me-2"></i> Identified Gaps</span>
            <span class="text-muted small"><?php echo count($gaps ?? []); ?> gaps</span>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover enterprise-table mb-0">
                    <thead>
                        <tr>
                            <th>Gap ID</th>
                            <th>Description</th>
                            <th>Category</th>
                            <th>Severity</th>
                            <th>Status</th>
                            <th>Department</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (!empty($gaps)): ?>
                            <?php foreach ($gaps as $gap): ?>
                                <tr>
                                    <td><span class="gap-id">#<?php echo str_pad($gap->id, 4, '0', STR_PAD_LEFT); ?></span></td>
                                    <td><?php echo htmlspecialchars(substr($gap->description, 0, 60)) . '...'; ?></td>
                                    <td><span class="badge bg-secondary"><?php echo ucfirst($gap->category); ?></span></td>
                                    <td>
                                        <span class="severity-badge <?php echo $gap->severity; ?>">
                                            <?php echo ucfirst($gap->severity); ?>
                                        </span>
                                    </td>
                                    <td>
                                        <span class="status-badge <?php echo $gap->status; ?>">
                                            <?php echo ucfirst(str_replace('_', ' ', $gap->status)); ?>
                                        </span>
                                    </td>
                                    <td><?php echo htmlspecialchars($gap->department_name ?? 'N/A'); ?></td>
                                    <td>
                                        <div class="btn-group btn-group-sm">
                                            <button class="btn btn-outline-primary view-gap" data-id="<?php echo $gap->id; ?>">
                                                <i class="fas fa-eye"></i>
                                            </button>
                                            <button class="btn btn-outline-success remediate-gap" data-id="<?php echo $gap->id; ?>">
                                                <i class="fas fa-check"></i>
                                            </button>
                                            <button class="btn btn-outline-secondary add-note" data-id="<?php echo $gap->id; ?>">
                                                <i class="fas fa-sticky-note"></i>
                                            </button>
                                        </div>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="7" class="text-center py-4 text-muted">
                                    <i class="fas fa-check-circle fa-2x text-success d-block mb-2"></i>
                                    No gaps found. All controls are compliant!
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

<!-- Gap Details Modal -->
<div class="modal fade" id="gapDetailsModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title"><i class="fas fa-search me-2"></i> Gap Details</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body" id="gapDetailsContent">
                <div class="text-center py-4">
                    <div class="spinner-border text-primary" role="status">
                        <span class="visually-hidden">Loading...</span>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                <button type="button" class="btn btn-success" id="remediateFromModal">
                    <i class="fas fa-check me-1"></i> Remediate
                </button>
            </div>
        </div>
    </div>
</div>

<style>
.gap-analysis-container {
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

.filter-section {
    background: #fff;
    padding: 16px 20px;
    border-radius: 12px;
    box-shadow: 0 1px 3px rgba(0,0,0,0.06);
}

.search-box {
    position: relative;
}

.search-box i {
    position: absolute;
    left: 14px;
    top: 50%;
    transform: translateY(-50%);
    color: #94A3B8;
}

.search-box .form-control {
    padding-left: 40px;
    border-radius: 8px;
}

.gap-id {
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
    // Gap Severity Chart
    const severityCtx = document.getElementById('gapSeverityChart').getContext('2d');
    new Chart(severityCtx, {
        type: 'bar',
        data: {
            labels: ['Critical', 'High', 'Medium', 'Low'],
            datasets: [{
                label: 'Gaps',
                data: <?php echo json_encode($chart_data['severity'] ?? [5, 12, 18, 8]); ?>,
                backgroundColor: ['#DC2626', '#EF4444', '#F59E0B', '#22C55E'],
                borderRadius: 6
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

    // Gap Status Chart
    const statusCtx = document.getElementById('gapStatusChart').getContext('2d');
    new Chart(statusCtx, {
        type: 'doughnut',
        data: {
            labels: ['Open', 'In Progress', 'Resolved', 'Closed'],
            datasets: [{
                data: <?php echo json_encode($chart_data['status'] ?? [12, 8, 15, 10]); ?>,
                backgroundColor: ['#DC2626', '#F59E0B', '#22C55E', '#64748B'],
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

    // Filter functionality
    $('#filterDepartment, #filterSeverity, #filterStatus').on('change', applyFilters);
    $('#searchGap').on('keyup', applyFilters);

    function applyFilters() {
        const department = $('#filterDepartment').val();
        const severity = $('#filterSeverity').val();
        const status = $('#filterStatus').val();
        const search = $('#searchGap').val().toLowerCase();

        $('.enterprise-table tbody tr').each(function() {
            const row = $(this);
            let show = true;

            if (department) {
                const rowDept = row.find('td:eq(5)').text().trim();
                const selectedDept = $('#filterDepartment option:selected').text();
                if (rowDept !== selectedDept) show = false;
            }
            if (severity) {
                const rowSeverity = row.find('.severity-badge').text().trim().toLowerCase();
                if (rowSeverity !== severity) show = false;
            }
            if (status) {
                const rowStatus = row.find('.status-badge').text().trim().toLowerCase().replace(' ', '_');
                if (rowStatus !== status) show = false;
            }
            if (search) {
                const text = row.text().toLowerCase();
                if (!text.includes(search)) show = false;
            }

            row.toggle(show);
        });
    }

    // View gap
    $('.view-gap').on('click', function() {
        const id = $(this).data('id');
        const modal = $('#gapDetailsModal');
        const content = $('#gapDetailsContent');

        content.html('<div class="text-center py-4"><div class="spinner-border text-primary"></div></div>');
        modal.modal('show');

        $.ajax({
            url: '/api/ai/gap/' + id,
            method: 'GET',
            success: function(response) {
                if (response.success) {
                    const gap = response.data;
                    content.html(`
                        <div class="gap-details">
                            <h6>${gap.title}</h6>
                            <p><strong>Description:</strong> ${gap.description}</p>
                            <p><strong>Category:</strong> ${gap.category}</p>
                            <p><strong>Severity:</strong> <span class="severity-badge ${gap.severity}">${gap.severity}</span></p>
                            <p><strong>Status:</strong> <span class="status-badge ${gap.status}">${gap.status}</span></p>
                            <p><strong>Department:</strong> ${gap.department_name || 'N/A'}</p>
                            <p><strong>Recommendation:</strong> ${gap.recommendation || 'No recommendation available'}</p>
                        </div>
                    `);
                }
            }
        });
    });

    // Remediate gap
    $('.remediate-gap').on('click', function() {
        const id = $(this).data('id');
        if (confirm('Mark this gap as remediated?')) {
            const csrfToken = $('input[name="csrf_token"]').val();
            $.ajax({
                url: '/api/ai/gap/' + id + '/remediate',
                method: 'POST',
                data: { _csrf: csrfToken },
                success: function(response) {
                    if (response.success) {
                        showToast('Gap marked as remediated', 'success');
                        location.reload();
                    }
                }
            });
        }
    });

    // Run analysis
    $('#runAnalysisBtn').on('click', function() {
        const btn = $(this);
        btn.html('<span class="spinner-border spinner-border-sm me-2"></span> Analyzing...');
        btn.prop('disabled', true);
        
        setTimeout(function() {
            location.reload();
        }, 2000);
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