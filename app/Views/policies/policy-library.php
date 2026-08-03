<?php
/**
 * Policy Library Page
 * 
 * @var string $title
 * @var array $policies
 * @var array $categories
 * @var array $recent_policies
 */
?>

<?php $page_title = 'Policy Library'; ?>
<?php $active_page = 'policies'; ?>

<div class="policy-library-container">
    <!-- Header -->
    <div class="row mb-4">
        <div class="col-md-8">
            <h5><i class="fas fa-book me-2 text-primary"></i> Policy Library</h5>
            <p class="text-muted">Browse and access all organizational policies</p>
        </div>
        <div class="col-md-4 text-end">
            <a href="<?php echo BASE_URL; ?>/policies" class="btn btn-outline-primary me-2">
                <i class="fas fa-list me-2"></i> List View
            </a>
            <a href="<?php echo BASE_URL; ?>/policies/create" class="btn btn-primary">
                <i class="fas fa-plus me-2"></i> New Policy
            </a>
        </div>
    </div>
    
    <!-- Category Filters -->
    <div class="category-filters mb-4">
        <div class="d-flex flex-wrap gap-2">
            <button class="btn btn-primary btn-sm category-filter active" data-category="all">
                <i class="fas fa-th-large me-1"></i> All
            </button>
            <?php foreach ($categories ?? [] as $key => $label): ?>
                <button class="btn btn-outline-secondary btn-sm category-filter" data-category="<?php echo $key; ?>">
                    <i class="fas fa-tag me-1"></i> <?php echo $label; ?>
                </button>
            <?php endforeach; ?>
        </div>
        
        <div class="search-box mt-3">
            <i class="fas fa-search"></i>
            <input type="text" class="form-control" id="searchLibrary" placeholder="Search policies...">
        </div>
    </div>
    
    <!-- Policy Cards Grid -->
    <div class="row g-4" id="policyGrid">
        <?php if (!empty($policies)): ?>
            <?php foreach ($policies as $policy): ?>
                <div class="col-xl-4 col-lg-6 policy-card-item" data-category="<?php echo $policy->category; ?>">
                    <div class="policy-card <?php echo $policy->status; ?>">
                        <div class="policy-card-header">
                            <div class="policy-type">
                                <i class="fas fa-<?php echo getPolicyIcon($policy->category); ?>"></i>
                            </div>
                            <div class="policy-status">
                                <span class="badge <?php echo $policy->status === 'active' ? 'bg-success' : ($policy->status === 'draft' ? 'bg-secondary' : ($policy->status === 'review' ? 'bg-warning' : 'bg-danger')); ?>">
                                    <?php echo ucfirst($policy->status); ?>
                                </span>
                            </div>
                        </div>
                        <div class="policy-card-body">
                            <h6 class="policy-title"><?php echo htmlspecialchars($policy->title); ?></h6>
                            <div class="policy-meta">
                                <span class="policy-number"><?php echo htmlspecialchars($policy->policy_number); ?></span>
                                <span class="policy-version">v<?php echo $policy->version; ?></span>
                            </div>
                            <div class="policy-category">
                                <span class="badge bg-secondary"><?php echo ucfirst($policy->category); ?></span>
                            </div>
                            <div class="policy-description">
                                <?php echo htmlspecialchars(substr($policy->description, 0, 80)) . (strlen($policy->description) > 80 ? '...' : ''); ?>
                            </div>
                            <div class="policy-footer">
                                <span class="policy-date">
                                    <i class="far fa-calendar-alt me-1"></i>
                                    Effective: <?php echo date('d M Y', strtotime($policy->effective_date)); ?>
                                </span>
                                <span class="policy-owner">
                                    <i class="fas fa-user me-1"></i>
                                    <?php echo htmlspecialchars($policy->owner_name ?? 'Unassigned'); ?>
                                </span>
                            </div>
                        </div>
                        <div class="policy-card-actions">
                            <a href="<?php echo BASE_URL; ?>/policies/<?php echo $policy->id; ?>" class="btn btn-sm btn-outline-primary">
                                <i class="fas fa-eye me-1"></i> View
                            </a>
                            <?php if ($policy->document_path): ?>
                                <a href="<?php echo UPLOADS_URL; ?>/<?php echo $policy->document_path; ?>" 
                                   class="btn btn-sm btn-outline-success" target="_blank">
                                    <i class="fas fa-download me-1"></i> Download
                                </a>
                            <?php endif; ?>
                            <button class="btn btn-sm btn-outline-secondary preview-policy" 
                                    data-id="<?php echo $policy->id; ?>">
                                <i class="fas fa-eye me-1"></i> Preview
                            </button>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        <?php else: ?>
            <div class="col-12">
                <div class="empty-state text-center py-5">
                    <i class="fas fa-book fa-3x text-muted mb-3"></i>
                    <h5>No Policies Found</h5>
                    <p class="text-muted">The policy library is empty. Create your first policy!</p>
                    <a href="<?php echo BASE_URL; ?>/policies/create" class="btn btn-primary">
                        <i class="fas fa-plus me-2"></i> Create Policy
                    </a>
                </div>
            </div>
        <?php endif; ?>
    </div>
    
    <!-- Pagination -->
    <?php if (isset($total_pages) && $total_pages > 1): ?>
    <nav class="mt-4">
        <ul class="pagination justify-content-center">
            <li class="page-item <?php echo $page <= 1 ? 'disabled' : ''; ?>">
                <a class="page-link" href="<?php echo BASE_URL; ?>/policies/library?page=<?php echo $page - 1; ?>">Previous</a>
            </li>
            <?php for ($i = 1; $i <= $total_pages; $i++): ?>
                <li class="page-item <?php echo $i == $page ? 'active' : ''; ?>">
                    <a class="page-link" href="<?php echo BASE_URL; ?>/policies/library?page=<?php echo $i; ?>"><?php echo $i; ?></a>
                </li>
            <?php endfor; ?>
            <li class="page-item <?php echo $page >= $total_pages ? 'disabled' : ''; ?>">
                <a class="page-link" href="<?php echo BASE_URL; ?>/policies/library?page=<?php echo $page + 1; ?>">Next</a>
            </li>
        </ul>
    </nav>
    <?php endif; ?>
</div>

<!-- Preview Modal -->
<div class="modal fade" id="previewModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title"><i class="fas fa-file-contract me-2"></i> Policy Preview</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body" id="previewContent">
                <div class="text-center py-4">
                    <div class="spinner-border text-primary" role="status">
                        <span class="visually-hidden">Loading...</span>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                <button type="button" class="btn btn-primary" id="viewFullPolicy">
                    <i class="fas fa-external-link-alt me-2"></i> View Full Policy
                </button>
            </div>
        </div>
    </div>
</div>

<?php
/**
 * Helper function to get icon for policy category
 */
function getPolicyIcon($category) {
    $icons = [
        'governance' => 'building',
        'risk_management' => 'shield-alt',
        'compliance' => 'check-circle',
        'information_security' => 'lock',
        'data_privacy' => 'user-secret',
        'human_resources' => 'users',
        'finance' => 'coins',
        'operations' => 'cogs',
        'it' => 'server',
        'business_continuity' => 'recycle',
        'anti_money_laundering' => 'money-bill-wave',
        'fraud_prevention' => 'fingerprint'
    ];
    return $icons[$category] ?? 'file-contract';
}
?>

<style>
.policy-library-container {
    padding: 0;
}

.category-filters {
    background: #fff;
    padding: 16px 20px;
    border-radius: 12px;
    box-shadow: 0 1px 3px rgba(0,0,0,0.06);
}

.category-filter {
    border-radius: 20px;
    padding: 4px 16px;
    font-size: 13px;
}

.category-filter.active {
    background: #2563EB;
    color: #fff;
    border-color: #2563EB;
}

.search-box {
    position: relative;
    max-width: 400px;
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

.policy-card {
    background: #fff;
    border-radius: 12px;
    overflow: hidden;
    box-shadow: 0 1px 3px rgba(0,0,0,0.06);
    transition: all 0.3s;
    height: 100%;
    display: flex;
    flex-direction: column;
}

.policy-card:hover {
    box-shadow: 0 4px 12px rgba(0,0,0,0.1);
    transform: translateY(-4px);
}

.policy-card.draft { border-top: 3px solid #94A3B8; }
.policy-card.review { border-top: 3px solid #F59E0B; }
.policy-card.approved { border-top: 3px solid #3B82F6; }
.policy-card.active { border-top: 3px solid #22C55E; }
.policy-card.archived { border-top: 3px solid #94A3B8; }
.policy-card.expired { border-top: 3px solid #EF4444; }

.policy-card-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 16px 20px 0;
}

.policy-type {
    width: 40px;
    height: 40px;
    border-radius: 10px;
    background: #DBEAFE;
    color: #2563EB;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 18px;
}

.policy-card-body {
    padding: 16px 20px 12px;
    flex: 1;
}

.policy-title {
    font-weight: 600;
    color: #1E293B;
    margin: 0 0 4px;
}

.policy-meta {
    display: flex;
    gap: 12px;
    font-size: 13px;
    color: #94A3B8;
    margin-bottom: 8px;
}

.policy-number {
    font-family: 'Courier New', monospace;
}

.policy-category {
    margin-bottom: 8px;
}

.policy-description {
    font-size: 14px;
    color: #64748B;
    line-height: 1.6;
    margin-bottom: 12px;
}

.policy-footer {
    display: flex;
    justify-content: space-between;
    font-size: 12px;
    color: #94A3B8;
    padding-top: 8px;
    border-top: 1px solid #F1F5F9;
}

.policy-card-actions {
    display: flex;
    gap: 8px;
    padding: 12px 20px 16px;
    border-top: 1px solid #F1F5F9;
}

.policy-card-actions .btn {
    flex: 1;
    font-size: 13px;
}

.empty-state i {
    display: block;
}

/* Preview Modal */
#previewContent {
    max-height: 500px;
    overflow-y: auto;
}

#previewContent .policy-preview {
    padding: 20px;
    background: #F8FAFC;
    border-radius: 8px;
    line-height: 1.8;
}

#previewContent .policy-preview h1 {
    font-size: 22px;
    color: #0B3D91;
}

#previewContent .policy-preview h2 {
    font-size: 18px;
    color: #1E293B;
    margin-top: 16px;
}

@media (max-width: 768px) {
    .category-filters .d-flex {
        flex-wrap: nowrap;
        overflow-x: auto;
        padding-bottom: 8px;
    }
    
    .policy-card-actions {
        flex-wrap: wrap;
    }
    
    .policy-card-actions .btn {
        flex: auto;
        min-width: 80px;
    }
}
</style>

<script>
$(document).ready(function() {
    // Category filter
    $('.category-filter').on('click', function() {
        $('.category-filter').removeClass('active btn-primary').addClass('btn-outline-secondary');
        $(this).removeClass('btn-outline-secondary').addClass('active btn-primary');
        
        const category = $(this).data('category');
        filterPolicies(category);
    });
    
    // Search filter
    $('#searchLibrary').on('keyup', function() {
        const search = $(this).val().toLowerCase();
        const activeCategory = $('.category-filter.active').data('category');
        
        $('.policy-card-item').each(function() {
            const card = $(this);
            const title = card.find('.policy-title').text().toLowerCase();
            const number = card.find('.policy-number').text().toLowerCase();
            const description = card.find('.policy-description').text().toLowerCase();
            const category = card.data('category');
            
            let show = true;
            
            // Category filter
            if (activeCategory !== 'all' && category !== activeCategory) {
                show = false;
            }
            
            // Search filter
            if (search && !title.includes(search) && !number.includes(search) && !description.includes(search)) {
                show = false;
            }
            
            card.toggle(show);
        });
    });
    
    function filterPolicies(category) {
        $('.policy-card-item').each(function() {
            const card = $(this);
            if (category === 'all' || card.data('category') === category) {
                card.show();
            } else {
                card.hide();
            }
        });
    }
    
    // Preview policy
    $('.preview-policy').on('click', function() {
        const id = $(this).data('id');
        const modal = $('#previewModal');
        const content = $('#previewContent');
        
        // Show loading
        content.html(`
            <div class="text-center py-4">
                <div class="spinner-border text-primary" role="status">
                    <span class="visually-hidden">Loading...</span>
                </div>
            </div>
        `);
        
        modal.modal('show');
        
        // Simulate loading policy preview
        setTimeout(function() {
            content.html(`
                <div class="policy-preview">
                    <h1>Sample Policy Document</h1>
                    <p><strong>Policy Number:</strong> POL-2024-0001</p>
                    <p><strong>Version:</strong> 1.0</p>
                    <p><strong>Effective Date:</strong> 01 Jan 2024</p>
                    
                    <h2>Purpose</h2>
                    <p>This policy establishes the framework for compliance management...</p>
                    
                    <h2>Scope</h2>
                    <p>This policy applies to all departments and personnel...</p>
                    
                    <h2>Key Requirements</h2>
                    <ul>
                        <li>All personnel must comply with this policy</li>
                        <li>Regular reviews will be conducted</li>
                        <li>Violations may result in disciplinary action</li>
                    </ul>
                    
                    <div class="text-muted mt-3">
                        <small><i class="fas fa-info-circle"></i> This is a preview. View the full policy for complete details.</small>
                    </div>
                </div>
            `);
        }, 1000);
    });
    
    // View full policy from preview
    $('#viewFullPolicy').on('click', function() {
        const modal = $('#previewModal');
        const policyId = modal.data('policy-id');
        if (policyId) {
            window.location.href = '<?php echo BASE_URL; ?>/policies/' + policyId;
        }
    });
});
</script>