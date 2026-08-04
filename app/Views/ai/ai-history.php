<?php
/**
 * AI History View
 * 
 * @var string $title
 * @var array $history
 * @var int $total
 * @var int $page
 * @var int $per_page
 * @var int $total_pages
 * @var array $filters
 */
?>

<?php $page_title = 'AI History'; ?>
<?php $active_page = 'ai'; ?>

<div class="ai-history-container">
    <!-- Page Header -->
    <div class="page-header">
        <div class="page-header-left">
            <h1 class="page-title">
                <i class="fas fa-history text-primary me-2"></i> AI Activity History
            </h1>
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="/dashboard">Dashboard</a></li>
                    <li class="breadcrumb-item"><a href="/ai">AI</a></li>
                    <li class="breadcrumb-item active" aria-current="page">History</li>
                </ol>
            </nav>
        </div>
        <div class="page-header-right">
            <div class="quick-actions">
                <button class="btn btn-outline-primary btn-sm" id="exportHistory">
                    <i class="fas fa-download me-1"></i> Export
                </button>
                <button class="btn btn-outline-secondary btn-sm" id="refreshHistory">
                    <i class="fas fa-sync-alt"></i>
                </button>
            </div>
            <div class="last-updated">
                <i class="far fa-clock"></i>
                <span>Last updated: <?php echo date('h:i A'); ?></span>
            </div>
        </div>
    </div>

    <!-- Filters -->
    <div class="filter-section mb-4">
        <div class="row g-3">
            <div class="col-md-3">
                <select class="form-select" id="filterModule">
                    <option value="">All Modules</option>
                    <option value="compliance">Compliance</option>
                    <option value="risk">Risk</option>
                    <option value="audit">Audit</option>
                    <option value="policy">Policy</option>
                    <option value="general">General</option>
                </select>
            </div>
            <div class="col-md-3">
                <select class="form-select" id="filterStatus">
                    <option value="">All Status</option>
                    <option value="success">Success</option>
                    <option value="failed">Failed</option>
                    <option value="pending">Pending</option>
                </select>
            </div>
            <div class="col-md-3">
                <div class="search-box">
                    <i class="fas fa-search"></i>
                    <input type="text" class="form-control" id="searchHistory" placeholder="Search history...">
                </div>
            </div>
            <div class="col-md-3">
                <div class="d-flex gap-2">
                    <input type="date" class="form-control" id="dateFrom" placeholder="From">
                    <span class="text-muted">to</span>
                    <input type="date" class="form-control" id="dateTo" placeholder="To">
                </div>
            </div>
        </div>
    </div>

    <!-- History Table -->
    <div class="card">
        <div class="card-header d-flex justify-content-between align-items-center">
            <span><i class="fas fa-list me-2"></i> AI Activity Log</span>
            <span class="text-muted small"><?php echo $total; ?> records</span>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover enterprise-table mb-0">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Module</th>
                            <th>Prompt</th>
                            <th>Status</th>
                            <th>Response Time</th>
                            <th>User</th>
                            <th>Date</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (!empty($history)): ?>
                            <?php foreach ($history as $item): ?>
                                <tr>
                                    <td>
                                        <span class="history-id">#<?php echo str_pad($item->id, 4, '0', STR_PAD_LEFT); ?></span>
                                    </td>
                                    <td>
                                        <span class="badge bg-secondary"><?php echo ucfirst($item->module); ?></span>
                                    </td>
                                    <td>
                                        <div class="prompt-text">
                                            <?php echo htmlspecialchars(substr($item->prompt, 0, 50)) . (strlen($item->prompt) > 50 ? '...' : ''); ?>
                                        </div>
                                    </td>
                                    <td>
                                        <span class="status-badge <?php echo $item->status; ?>">
                                            <i class="fas fa-<?php echo $item->status === 'success' ? 'check-circle' : ($item->status === 'failed' ? 'times-circle' : 'clock'); ?> me-1"></i>
                                            <?php echo ucfirst($item->status); ?>
                                        </span>
                                    </td>
                                    <td>
                                        <?php if ($item->response_time): ?>
                                            <?php echo number_format($item->response_time, 2); ?>s
                                        <?php else: ?>
                                            <span class="text-muted">N/A</span>
                                        <?php endif; ?>
                                    </td>
                                    <td><?php echo htmlspecialchars($item->username ?? 'System'); ?></td>
                                    <td>
                                        <?php if ($item->created_at): ?>
                                            <?php echo date('d M Y h:i A', strtotime($item->created_at)); ?>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <button class="btn btn-sm btn-outline-primary view-details" 
                                                data-id="<?php echo $item->id; ?>" title="View Details">
                                            <i class="fas fa-eye"></i>
                                        </button>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="8" class="text-center py-4 text-muted">
                                    <i class="fas fa-inbox fa-2x d-block mb-2"></i>
                                    No history records found
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

<!-- Details Modal -->
<div class="modal fade" id="historyDetailModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title"><i class="fas fa-info-circle me-2"></i> AI Request Details</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body" id="detailContent">
                <div class="text-center py-4">
                    <div class="spinner-border text-primary" role="status">
                        <span class="visually-hidden">Loading...</span>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>

<style>
.ai-history-container {
    padding: 0;
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

.history-id {
    font-family: 'Courier New', monospace;
    font-weight: 600;
    color: #2563EB;
}

.prompt-text {
    max-width: 200px;
    overflow: hidden;
    text-overflow: ellipsis;
    white-space: nowrap;
}

.status-badge {
    padding: 4px 12px;
    border-radius: 12px;
    font-size: 12px;
    font-weight: 500;
    display: inline-flex;
    align-items: center;
    gap: 4px;
}

.status-badge.success {
    background: #D1FAE5;
    color: #10B981;
}

.status-badge.failed {
    background: #FEE2E2;
    color: #DC2626;
}

.status-badge.pending {
    background: #FEF3C7;
    color: #F59E0B;
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

.detail-section {
    margin-bottom: 16px;
}

.detail-section h6 {
    font-weight: 600;
    color: #1E293B;
    margin-bottom: 8px;
}

.detail-section .detail-box {
    background: #F8FAFC;
    padding: 12px 16px;
    border-radius: 8px;
    font-family: 'Courier New', monospace;
    font-size: 13px;
    white-space: pre-wrap;
    word-break: break-word;
    max-height: 200px;
    overflow-y: auto;
}

@media (max-width: 768px) {
    .filter-section .row .col-md-3 {
        margin-bottom: 8px;
    }
    
    .prompt-text {
        max-width: 120px;
    }
}
</style>

<script>
$(document).ready(function() {
    // Filter functionality
    $('#filterModule, #filterStatus, #dateFrom, #dateTo').on('change', applyFilters);
    $('#searchHistory').on('keyup', applyFilters);
    
    function applyFilters() {
        const module = $('#filterModule').val();
        const status = $('#filterStatus').val();
        const search = $('#searchHistory').val().toLowerCase();
        const dateFrom = $('#dateFrom').val();
        const dateTo = $('#dateTo').val();
        
        $('.enterprise-table tbody tr').each(function() {
            const row = $(this);
            let show = true;
            
            if (module) {
                const rowModule = row.find('.badge').text().trim().toLowerCase();
                if (rowModule !== module) show = false;
            }
            
            if (status) {
                const rowStatus = row.find('.status-badge').text().trim().toLowerCase();
                if (rowStatus !== status) show = false;
            }
            
            if (search) {
                const text = row.text().toLowerCase();
                if (!text.includes(search)) show = false;
            }
            
            row.toggle(show);
        });
    }
    
    // View details
    $('.view-details').on('click', function() {
        const id = $(this).data('id');
        const modal = $('#historyDetailModal');
        const content = $('#detailContent');
        
        content.html(`
            <div class="text-center py-4">
                <div class="spinner-border text-primary" role="status">
                    <span class="visually-hidden">Loading...</span>
                </div>
            </div>
        `);
        
        modal.modal('show');
        
        $.ajax({
            url: '/api/ai/history/' + id,
            method: 'GET',
            success: function(response) {
                if (response.success) {
                    const data = response.data;
                    content.html(`
                        <div class="detail-section">
                            <h6>Request Details</h6>
                            <div class="row g-2">
                                <div class="col-md-6">
                                    <strong>ID:</strong> #${String(data.id).padStart(4, '0')}
                                </div>
                                <div class="col-md-6">
                                    <strong>Module:</strong> ${data.module || 'N/A'}
                                </div>
                                <div class="col-md-6">
                                    <strong>Status:</strong> 
                                    <span class="status-badge ${data.status}">
                                        ${data.status || 'N/A'}
                                    </span>
                                </div>
                                <div class="col-md-6">
                                    <strong>Response Time:</strong> ${data.response_time || 'N/A'}s
                                </div>
                                <div class="col-md-6">
                                    <strong>User:</strong> ${data.username || 'System'}
                                </div>
                                <div class="col-md-6">
                                    <strong>Date:</strong> ${data.created_at || 'N/A'}
                                </div>
                            </div>
                        </div>
                        
                        <div class="detail-section">
                            <h6>Prompt</h6>
                            <div class="detail-box">${data.prompt || 'N/A'}</div>
                        </div>
                        
                        <div class="detail-section">
                            <h6>Response</h6>
                            <div class="detail-box">${data.response || 'N/A'}</div>
                        </div>
                        
                        ${data.error ? `
                            <div class="detail-section">
                                <h6 class="text-danger">Error</h6>
                                <div class="detail-box text-danger">${data.error}</div>
                            </div>
                        ` : ''}
                    `);
                }
            },
            error: function() {
                content.html(`
                    <div class="alert alert-danger">
                        <i class="fas fa-exclamation-circle me-2"></i>
                        Failed to load details. Please try again.
                    </div>
                `);
            }
        });
    });
    
    // Export history
    $('#exportHistory').on('click', function() {
        const filters = {
            module: $('#filterModule').val(),
            status: $('#filterStatus').val(),
            date_from: $('#dateFrom').val(),
            date_to: $('#dateTo').val()
        };
        
        window.location.href = '/api/ai/history/export?' + $.param(filters);
    });
    
    // Refresh
    $('#refreshHistory').on('click', function() {
        const btn = $(this);
        btn.html('<i class="fas fa-spinner fa-spin"></i>');
        setTimeout(function() {
            location.reload();
        }, 1000);
    });
});
</script>