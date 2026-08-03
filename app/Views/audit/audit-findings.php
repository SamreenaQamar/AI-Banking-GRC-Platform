<?php
/**
 * Audit Findings Page
 * 
 * @var string $title
 * @var array $findings
 * @var array $filters
 */
?>

<?php $page_title = 'Audit Findings'; ?>
<?php $active_page = 'audit'; ?>

<div class="audit-findings-container">
    <!-- Header -->
    <div class="row mb-4">
        <div class="col-md-8">
            <h5><i class="fas fa-search me-2 text-primary"></i> Audit Findings</h5>
            <p class="text-muted">Track and manage audit findings with severity classification</p>
        </div>
        <div class="col-md-4 text-end">
            <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#newFindingModal">
                <i class="fas fa-plus me-2"></i> Add Finding
            </button>
            <button class="btn btn-outline-primary">
                <i class="fas fa-download me-2"></i> Export
            </button>
        </div>
    </div>
    
    <!-- Filters -->
    <div class="filter-section mb-4">
        <div class="row g-3">
            <div class="col-md-2">
                <select class="form-select" id="filterSeverity">
                    <option value="">All Severity</option>
                    <option value="critical">Critical</option>
                    <option value="high">High</option>
                    <option value="medium">Medium</option>
                    <option value="low">Low</option>
                </select>
            </div>
            <div class="col-md-2">
                <select class="form-select" id="filterStatus">
                    <option value="">All Status</option>
                    <option value="open">Open</option>
                    <option value="in_progress">In Progress</option>
                    <option value="resolved">Resolved</option>
                    <option value="closed">Closed</option>
                </select>
            </div>
            <div class="col-md-3">
                <select class="form-select" id="filterAudit">
                    <option value="">All Audits</option>
                    <?php foreach ($audits ?? [] as $audit): ?>
                        <option value="<?php echo $audit->id; ?>"><?php echo htmlspecialchars($audit->title); ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="col-md-3">
                <select class="form-select" id="filterAssignee">
                    <option value="">All Assignees</option>
                    <?php foreach ($assignees ?? [] as $user): ?>
                        <option value="<?php echo $user->id; ?>"><?php echo htmlspecialchars($user->full_name); ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="col-md-2">
                <div class="search-box">
                    <i class="fas fa-search"></i>
                    <input type="text" class="form-control" id="searchFinding" placeholder="Search...">
                </div>
            </div>
        </div>
    </div>
    
    <!-- Findings Table -->
    <div class="card">
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover enterprise-table mb-0">
                    <thead>
                        <tr>
                            <th>Finding ID</th>
                            <th>Title</th>
                            <th>Audit</th>
                            <th>Severity</th>
                            <th>Status</th>
                            <th>Assigned To</th>
                            <th>Due Date</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (!empty($findings)): ?>
                            <?php foreach ($findings as $finding): ?>
                                <tr>
                                    <td><span class="finding-id">#<?php echo str_pad($finding->id, 4, '0', STR_PAD_LEFT); ?></span></td>
                                    <td>
                                        <a href="<?php echo BASE_URL; ?>/audit/findings/<?php echo $finding->id; ?>" class="finding-title">
                                            <?php echo htmlspecialchars(substr($finding->title, 0, 40)) . '...'; ?>
                                        </a>
                                    </td>
                                    <td><?php echo htmlspecialchars($finding->audit_title ?? 'N/A'); ?></td>
                                    <td>
                                        <span class="severity-badge <?php echo $finding->severity; ?>">
                                            <?php echo ucfirst($finding->severity); ?>
                                        </span>
                                    </td>
                                    <td>
                                        <span class="status-badge <?php echo $finding->status; ?>">
                                            <?php echo ucfirst(str_replace('_', ' ', $finding->status)); ?>
                                        </span>
                                    </td>
                                    <td><?php echo htmlspecialchars($finding->assignee_name ?? 'Unassigned'); ?></td>
                                    <td>
                                        <?php if ($finding->due_date): ?>
                                            <span class="<?php echo strtotime($finding->due_date) < time() ? 'text-danger' : 'text-muted'; ?>">
                                                <?php echo date('d M Y', strtotime($finding->due_date)); ?>
                                            </span>
                                        <?php else: ?>
                                            <span class="text-muted">Not set</span>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <div class="btn-group btn-group-sm">
                                            <a href="<?php echo BASE_URL; ?>/audit/findings/<?php echo $finding->id; ?>" 
                                               class="btn btn-outline-primary" title="View">
                                                <i class="fas fa-eye"></i>
                                            </a>
                                            <button class="btn btn-outline-secondary update-status" 
                                                    data-id="<?php echo $finding->id; ?>" title="Update Status">
                                                <i class="fas fa-sync-alt"></i>
                                            </button>
                                        </div>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="8" class="text-center py-4 text-muted">
                                    <i class="fas fa-inbox fa-2x d-block mb-2"></i>
                                    No findings found
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
                <a class="page-link" href="<?php echo BASE_URL; ?>/audit/findings?page=<?php echo $page - 1; ?>">Previous</a>
            </li>
            <?php for ($i = 1; $i <= $total_pages; $i++): ?>
                <li class="page-item <?php echo $i == $page ? 'active' : ''; ?>">
                    <a class="page-link" href="<?php echo BASE_URL; ?>/audit/findings?page=<?php echo $i; ?>"><?php echo $i; ?></a>
                </li>
            <?php endfor; ?>
            <li class="page-item <?php echo $page >= $total_pages ? 'disabled' : ''; ?>">
                <a class="page-link" href="<?php echo BASE_URL; ?>/audit/findings?page=<?php echo $page + 1; ?>">Next</a>
            </li>
        </ul>
    </nav>
    <?php endif; ?>
</div>

<!-- New Finding Modal -->
<div class="modal fade" id="newFindingModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title"><i class="fas fa-plus-circle me-2"></i> Add Audit Finding</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form method="POST" action="<?php echo BASE_URL; ?>/audit/findings">
                <div class="modal-body">
                    <input type="hidden" name="csrf_token" value="<?php echo $csrf_token ?? ''; ?>">
                    
                    <div class="row g-3">
                        <div class="col-12">
                            <label class="form-label">Audit</label>
                            <select class="form-select" name="audit_plan_id" required>
                                <option value="">Select Audit</option>
                                <?php foreach ($audits ?? [] as $audit): ?>
                                    <option value="<?php echo $audit->id; ?>"><?php echo htmlspecialchars($audit->title); ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-12">
                            <label class="form-label">Title</label>
                            <input type="text" class="form-control" name="title" required>
                        </div>
                        <div class="col-12">
                            <label class="form-label">Description</label>
                            <textarea class="form-control" name="description" rows="3" required></textarea>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Severity</label>
                            <select class="form-select" name="severity" required>
                                <option value="low">Low</option>
                                <option value="medium">Medium</option>
                                <option value="high">High</option>
                                <option value="critical">Critical</option>
                            </select>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Status</label>
                            <select class="form-select" name="status">
                                <option value="open">Open</option>
                                <option value="in_progress">In Progress</option>
                            </select>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Finding Date</label>
                            <input type="date" class="form-control" name="finding_date" value="<?php echo date('Y-m-d'); ?>" required>
                        </div>
                        <div class="col-12">
                            <label class="form-label">Recommendation</label>
                            <textarea class="form-control" name="recommendation" rows="2" required></textarea>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Assigned To</label>
                            <select class="form-select" name="assigned_to">
                                <option value="">Unassigned</option>
                                <?php foreach ($assignees ?? [] as $user): ?>
                                    <option value="<?php echo $user->id; ?>"><?php echo htmlspecialchars($user->full_name); ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Due Date</label>
                            <input type="date" class="form-control" name="due_date">
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">Add Finding</button>
                </div>
            </form>
        </div>
    </div>
</div>

<style>
.audit-findings-container {
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

.finding-id {
    font-family: 'Courier New', monospace;
    font-weight: 600;
    color: #2563EB;
}

.finding-title {
    color: #1E293B;
    text-decoration: none;
    font-weight: 500;
}

.finding-title:hover {
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
</style>

<script>
$(document).ready(function() {
    // Filter functionality
    $('#filterSeverity, #filterStatus, #filterAudit, #filterAssignee').on('change', applyFilters);
    $('#searchFinding').on('keyup', applyFilters);
    
    function applyFilters() {
        const severity = $('#filterSeverity').val();
        const status = $('#filterStatus').val();
        const audit = $('#filterAudit').val();
        const assignee = $('#filterAssignee').val();
        const search = $('#searchFinding').val().toLowerCase();
        
        $('.enterprise-table tbody tr').each(function() {
            const row = $(this);
            let show = true;
            
            if (severity && !row.find('.severity-badge').hasClass(severity)) show = false;
            if (status && !row.find('.status-badge').hasClass(status)) show = false;
            if (audit && row.find('td:eq(2)').text().trim() !== $('#filterAudit option:selected').text()) show = false;
            if (assignee && row.find('td:eq(5)').text().trim() !== $('#filterAssignee option:selected').text()) show = false;
            if (search) {
                const text = row.text().toLowerCase();
                if (!text.includes(search)) show = false;
            }
            
            row.toggle(show);
        });
    }
    
    // Update status button
    $('.update-status').on('click', function() {
        const id = $(this).data('id');
        const statuses = ['Open', 'In Progress', 'Resolved', 'Closed'];
        const currentStatus = $(this).closest('tr').find('.status-badge').text().trim().toLowerCase();
        const currentIndex = statuses.findIndex(s => s.toLowerCase() === currentStatus);
        const nextIndex = (currentIndex + 1) % statuses.length;
        const newStatus = statuses[nextIndex].toLowerCase().replace(' ', '_');
        
        if (confirm('Update status to ' + statuses[nextIndex] + '?')) {
            $.ajax({
                url: '<?php echo BASE_URL; ?>/api/audit/findings/' + id + '/status',
                method: 'POST',
                data: {
                    _csrf: '<?php echo $csrf_token ?? ''; ?>',
                    status: newStatus
                },
                success: function(response) {
                    if (response.success) {
                        location.reload();
                    }
                }
            });
        }
    });
});
</script>