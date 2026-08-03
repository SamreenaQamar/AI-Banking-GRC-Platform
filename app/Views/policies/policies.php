<?php
/**
 * Policies Management Page
 * 
 * @var string $title
 * @var array $policies
 * @var array $filters
 * @var array $categories
 * @var array $statuses
 */
?>

<?php $page_title = 'Policy Management'; ?>
<?php $active_page = 'policies'; ?>

<div class="policies-container">
    <!-- Header -->
    <div class="row mb-4">
        <div class="col-md-8">
            <h5><i class="fas fa-file-contract me-2 text-primary"></i> Policy Management</h5>
            <p class="text-muted">Manage organizational policies, versions, and compliance</p>
        </div>
        <div class="col-md-4 text-end">
            <a href="<?php echo BASE_URL; ?>/policies/create" class="btn btn-primary">
                <i class="fas fa-plus me-2"></i> Create Policy
            </a>
            <a href="<?php echo BASE_URL; ?>/policies/policy-generator" class="btn btn-outline-primary">
                <i class="fas fa-robot me-2"></i> AI Generator
            </a>
        </div>
    </div>
    
    <!-- Filters -->
    <div class="filter-section mb-4">
        <div class="row g-3">
            <div class="col-md-3">
                <select class="form-select" id="filterCategory">
                    <option value="">All Categories</option>
                    <?php foreach ($categories ?? [] as $key => $label): ?>
                        <option value="<?php echo $key; ?>"><?php echo $label; ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="col-md-2">
                <select class="form-select" id="filterStatus">
                    <option value="">All Status</option>
                    <?php foreach ($statuses ?? [] as $key => $label): ?>
                        <option value="<?php echo $key; ?>"><?php echo $label; ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="col-md-3">
                <select class="form-select" id="filterDepartment">
                    <option value="">All Departments</option>
                    <?php foreach ($departments ?? [] as $dept): ?>
                        <option value="<?php echo $dept->id; ?>"><?php echo htmlspecialchars($dept->name); ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="col-md-4">
                <div class="search-box">
                    <i class="fas fa-search"></i>
                    <input type="text" class="form-control" id="searchPolicy" placeholder="Search policies by title or number...">
                </div>
            </div>
        </div>
    </div>
    
    <!-- Policies Table -->
    <div class="card">
        <div class="card-header d-flex justify-content-between align-items-center">
            <span><i class="fas fa-list me-2"></i> All Policies</span>
            <span class="text-muted small"><?php echo count($policies ?? []); ?> policies</span>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover enterprise-table mb-0">
                    <thead>
                        <tr>
                            <th>Policy Number</th>
                            <th>Title</th>
                            <th>Category</th>
                            <th>Version</th>
                            <th>Status</th>
                            <th>Effective Date</th>
                            <th>Owner</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (!empty($policies)): ?>
                            <?php foreach ($policies as $policy): ?>
                                <tr>
                                    <td>
                                        <span class="policy-number"><?php echo htmlspecialchars($policy->policy_number); ?></span>
                                    </td>
                                    <td>
                                        <a href="<?php echo BASE_URL; ?>/policies/<?php echo $policy->id; ?>" class="policy-title">
                                            <?php echo htmlspecialchars(substr($policy->title, 0, 40)) . (strlen($policy->title) > 40 ? '...' : ''); ?>
                                        </a>
                                    </td>
                                    <td>
                                        <span class="badge bg-secondary"><?php echo ucfirst($policy->category); ?></span>
                                    </td>
                                    <td>
                                        <span class="version-badge">v<?php echo $policy->version; ?></span>
                                    </td>
                                    <td>
                                        <span class="status-badge <?php echo $policy->status; ?>">
                                            <?php echo ucfirst($policy->status); ?>
                                        </span>
                                    </td>
                                    <td>
                                        <?php if ($policy->effective_date): ?>
                                            <?php echo date('d M Y', strtotime($policy->effective_date)); ?>
                                        <?php else: ?>
                                            <span class="text-muted">N/A</span>
                                        <?php endif; ?>
                                    </td>
                                    <td><?php echo htmlspecialchars($policy->owner_name ?? 'Unassigned'); ?></td>
                                    <td>
                                        <div class="btn-group btn-group-sm">
                                            <a href="<?php echo BASE_URL; ?>/policies/<?php echo $policy->id; ?>" 
                                               class="btn btn-outline-primary" title="View">
                                                <i class="fas fa-eye"></i>
                                            </a>
                                            <a href="<?php echo BASE_URL; ?>/policies/<?php echo $policy->id; ?>/edit" 
                                               class="btn btn-outline-secondary" title="Edit">
                                                <i class="fas fa-edit"></i>
                                            </a>
                                            <?php if ($policy->document_path): ?>
                                                <a href="<?php echo UPLOADS_URL; ?>/<?php echo $policy->document_path; ?>" 
                                                   class="btn btn-outline-success" title="Download" target="_blank">
                                                    <i class="fas fa-download"></i>
                                                </a>
                                            <?php endif; ?>
                                            <?php if ($policy->status === 'draft'): ?>
                                                <button class="btn btn-outline-danger delete-policy" 
                                                        data-id="<?php echo $policy->id; ?>" title="Delete">
                                                    <i class="fas fa-trash"></i>
                                                </button>
                                            <?php endif; ?>
                                        </div>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="8" class="text-center py-4 text-muted">
                                    <i class="fas fa-file-contract fa-2x d-block mb-2"></i>
                                    No policies found. Create your first policy!
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
                <a class="page-link" href="<?php echo BASE_URL; ?>/policies?page=<?php echo $page - 1; ?>">Previous</a>
            </li>
            <?php for ($i = 1; $i <= $total_pages; $i++): ?>
                <li class="page-item <?php echo $i == $page ? 'active' : ''; ?>">
                    <a class="page-link" href="<?php echo BASE_URL; ?>/policies?page=<?php echo $i; ?>"><?php echo $i; ?></a>
                </li>
            <?php endfor; ?>
            <li class="page-item <?php echo $page >= $total_pages ? 'disabled' : ''; ?>">
                <a class="page-link" href="<?php echo BASE_URL; ?>/policies?page=<?php echo $page + 1; ?>">Next</a>
            </li>
        </ul>
    </nav>
    <?php endif; ?>
</div>

<style>
.policies-container {
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

.policy-number {
    font-family: 'Courier New', monospace;
    font-size: 13px;
    color: #2563EB;
    font-weight: 600;
}

.policy-title {
    color: #1E293B;
    text-decoration: none;
    font-weight: 500;
}

.policy-title:hover {
    color: #2563EB;
}

.version-badge {
    padding: 2px 10px;
    border-radius: 10px;
    background: #E2E8F0;
    color: #475569;
    font-size: 12px;
    font-weight: 600;
}

.status-badge {
    padding: 4px 12px;
    border-radius: 12px;
    font-size: 12px;
    font-weight: 500;
}

.status-badge.draft { background: #F1F5F9; color: #64748B; }
.status-badge.review { background: #FEF3C7; color: #F59E0B; }
.status-badge.approved { background: #DBEAFE; color: #3B82F6; }
.status-badge.active { background: #D1FAE5; color: #10B981; }
.status-badge.archived { background: #F1F5F9; color: #64748B; }
.status-badge.expired { background: #FEE2E2; color: #DC2626; }

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
    .filter-section .row .col-md-3,
    .filter-section .row .col-md-2,
    .filter-section .row .col-md-4 {
        margin-bottom: 8px;
    }
}
</style>

<script>
$(document).ready(function() {
    // Filter functionality
    $('#filterCategory, #filterStatus, #filterDepartment').on('change', applyFilters);
    $('#searchPolicy').on('keyup', applyFilters);
    
    function applyFilters() {
        const category = $('#filterCategory').val();
        const status = $('#filterStatus').val();
        const department = $('#filterDepartment').val();
        const search = $('#searchPolicy').val().toLowerCase();
        
        $('.enterprise-table tbody tr').each(function() {
            const row = $(this);
            let show = true;
            
            if (category) {
                const rowCategory = row.find('td:eq(2)').text().trim().toLowerCase();
                if (rowCategory !== category) show = false;
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
    
    // Delete policy
    $('.delete-policy').on('click', function() {
        const id = $(this).data('id');
        if (confirm('Are you sure you want to delete this policy?')) {
            $.ajax({
                url: '<?php echo BASE_URL; ?>/api/policies/' + id,
                method: 'DELETE',
                data: {
                    _csrf: '<?php echo $csrf_token ?? ''; ?>'
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