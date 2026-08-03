<?php
/**
 * Risk Register Page
 * 
 * @var string $title
 * @var array $risks
 * @var array $filters
 */
?>

<?php $page_title = 'Risk Register'; ?>
<?php $active_page = 'risk'; ?>

<div class="risk-register-container">
    <!-- Toolbar -->
    <div class="row mb-4">
        <div class="col-md-6">
            <div class="search-box">
                <i class="fas fa-search"></i>
                <input type="text" class="form-control" id="riskSearch" 
                       placeholder="Search risks by title or code...">
            </div>
        </div>
        <div class="col-md-6 text-end">
            <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addRiskModal">
                <i class="fas fa-plus me-2"></i> Add Risk
            </button>
            <button class="btn btn-outline-primary">
                <i class="fas fa-download me-2"></i> Export
            </button>
        </div>
    </div>
    
    <!-- Filter Row -->
    <div class="filter-row mb-4">
        <div class="filter-group">
            <label>Status</label>
            <select class="form-select form-select-sm" id="filterStatus">
                <option value="">All Status</option>
                <option value="identified">Identified</option>
                <option value="assessed">Assessed</option>
                <option value="mitigated">Mitigated</option>
                <option value="monitored">Monitored</option>
                <option value="closed">Closed</option>
            </select>
        </div>
        <div class="filter-group">
            <label>Risk Level</label>
            <select class="form-select form-select-sm" id="filterLevel">
                <option value="">All Levels</option>
                <option value="critical">Critical</option>
                <option value="high">High</option>
                <option value="medium">Medium</option>
                <option value="low">Low</option>
            </select>
        </div>
        <div class="filter-group">
            <label>Category</label>
            <select class="form-select form-select-sm" id="filterCategory">
                <option value="">All Categories</option>
                <?php foreach ($categories ?? [] as $category): ?>
                    <option value="<?php echo $category->id; ?>"><?php echo htmlspecialchars($category->name); ?></option>
                <?php endforeach; ?>
            </select>
        </div>
        <div class="filter-group">
            <label>Department</label>
            <select class="form-select form-select-sm" id="filterDepartment">
                <option value="">All Departments</option>
                <?php foreach ($departments ?? [] as $department): ?>
                    <option value="<?php echo $department->id; ?>"><?php echo htmlspecialchars($department->name); ?></option>
                <?php endforeach; ?>
            </select>
        </div>
    </div>
    
    <!-- Risk Table -->
    <div class="table-responsive">
        <table class="table table-hover enterprise-table">
            <thead>
                <tr>
                    <th>Risk Code</th>
                    <th>Title</th>
                    <th>Category</th>
                    <th>Level</th>
                    <th>Score</th>
                    <th>Status</th>
                    <th>Owner</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php if (!empty($risks)): ?>
                    <?php foreach ($risks as $risk): ?>
                        <tr>
                            <td>
                                <span class="risk-code"><?php echo htmlspecialchars($risk->risk_code); ?></span>
                            </td>
                            <td>
                                <a href="<?php echo BASE_URL; ?>/risk/<?php echo $risk->id; ?>" class="risk-title">
                                    <?php echo htmlspecialchars($risk->title); ?>
                                </a>
                            </td>
                            <td><?php echo htmlspecialchars($risk->category_name ?? 'N/A'); ?></td>
                            <td>
                                <span class="risk-level <?php echo $risk->risk_level ?? 'low'; ?>">
                                    <?php echo ucfirst($risk->risk_level ?? 'Low'); ?>
                                </span>
                            </td>
                            <td>
                                <div class="score-display">
                                    <span class="score-value"><?php echo $risk->inherent_risk_score ?? 0; ?>%</span>
                                    <div class="score-bar">
                                        <div class="score-fill" style="width: <?php echo $risk->inherent_risk_score ?? 0; ?>%; 
                                             background: <?php echo $risk->inherent_risk_score >= 80 ? '#DC2626' : ($risk->inherent_risk_score >= 60 ? '#EF4444' : ($risk->inherent_risk_score >= 40 ? '#F59E0B' : '#22C55E')); ?>">
                                        </div>
                                    </div>
                                </div>
                            </td>
                            <td>
                                <span class="status-badge <?php echo $risk->status; ?>">
                                    <?php echo ucfirst($risk->status ?? 'Identified'); ?>
                                </span>
                            </td>
                            <td><?php echo htmlspecialchars($risk->owner_name ?? 'Unassigned'); ?></td>
                            <td>
                                <div class="btn-group btn-group-sm">
                                    <a href="<?php echo BASE_URL; ?>/risk/<?php echo $risk->id; ?>" 
                                       class="btn btn-outline-primary" title="View">
                                        <i class="fas fa-eye"></i>
                                    </a>
                                    <a href="<?php echo BASE_URL; ?>/risk/<?php echo $risk->id; ?>/edit" 
                                       class="btn btn-outline-secondary" title="Edit">
                                        <i class="fas fa-edit"></i>
                                    </a>
                                    <?php if ($risk->status !== 'closed'): ?>
                                        <button class="btn btn-outline-success assess-btn" 
                                                data-id="<?php echo $risk->id; ?>" title="Assess">
                                            <i class="fas fa-clipboard-check"></i>
                                        </button>
                                    <?php endif; ?>
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
    
    <!-- Pagination -->
    <?php if (isset($total_pages) && $total_pages > 1): ?>
    <nav>
        <ul class="pagination justify-content-end">
            <li class="page-item <?php echo $page <= 1 ? 'disabled' : ''; ?>">
                <a class="page-link" href="<?php echo BASE_URL; ?>/risk/register?page=<?php echo $page - 1; ?>">Previous</a>
            </li>
            <?php for ($i = 1; $i <= $total_pages; $i++): ?>
                <li class="page-item <?php echo $i == $page ? 'active' : ''; ?>">
                    <a class="page-link" href="<?php echo BASE_URL; ?>/risk/register?page=<?php echo $i; ?>"><?php echo $i; ?></a>
                </li>
            <?php endfor; ?>
            <li class="page-item <?php echo $page >= $total_pages ? 'disabled' : ''; ?>">
                <a class="page-link" href="<?php echo BASE_URL; ?>/risk/register?page=<?php echo $page + 1; ?>">Next</a>
            </li>
        </ul>
    </nav>
    <?php endif; ?>
</div>

<style>
.risk-register-container {
    padding: 0;
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
    border-radius: 10px;
}

.filter-row {
    display: flex;
    gap: 16px;
    flex-wrap: wrap;
    background: #fff;
    padding: 16px 20px;
    border-radius: 12px;
    box-shadow: 0 1px 3px rgba(0,0,0,0.06);
}

.filter-group {
    display: flex;
    flex-direction: column;
    gap: 4px;
    min-width: 140px;
}

.filter-group label {
    font-size: 12px;
    color: #64748B;
    font-weight: 500;
}

.filter-group .form-select {
    border-radius: 8px;
    border-color: #E2E8F0;
}

.enterprise-table {
    background: #fff;
    border-radius: 12px;
    overflow: hidden;
    box-shadow: 0 1px 3px rgba(0,0,0,0.06);
}

.enterprise-table thead {
    background: #F8FAFC;
    border-bottom: 1px solid #E2E8F0;
}

.enterprise-table thead th {
    font-weight: 600;
    font-size: 12px;
    color: #64748B;
    text-transform: uppercase;
    letter-spacing: 0.5px;
    padding: 12px 16px;
}

.enterprise-table tbody td {
    padding: 14px 16px;
    vertical-align: middle;
    border-bottom: 1px solid #F1F5F9;
}

.enterprise-table tbody tr:hover {
    background: #F8FAFC;
}

.risk-code {
    font-family: 'Courier New', monospace;
    font-size: 13px;
    color: #0B3D91;
    font-weight: 600;
}

.risk-title {
    color: #1E293B;
    text-decoration: none;
    font-weight: 500;
}

.risk-title:hover {
    color: #2563EB;
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

.score-display {
    min-width: 100px;
}

.score-value {
    font-weight: 600;
    font-size: 14px;
}

.score-bar {
    width: 100%;
    height: 4px;
    background: #E2E8F0;
    border-radius: 2px;
    margin-top: 4px;
    overflow: hidden;
}

.score-fill {
    height: 100%;
    border-radius: 2px;
    transition: width 0.6s ease;
}

.status-badge {
    padding: 4px 12px;
    border-radius: 12px;
    font-size: 12px;
    font-weight: 500;
}

.status-badge.identified { background: #F1F5F9; color: #64748B; }
.status-badge.assessed { background: #DBEAFE; color: #3B82F6; }
.status-badge.mitigated { background: #FEF3C7; color: #F59E0B; }
.status-badge.monitored { background: #D1FAE5; color: #10B981; }
.status-badge.closed { background: #E2E8F0; color: #475569; }

@media (max-width: 768px) {
    .filter-row {
        flex-direction: column;
    }
    
    .filter-group {
        min-width: 100%;
    }
    
    .search-box {
        max-width: 100%;
    }
}
</style>

<script>
$(document).ready(function() {
    // Search functionality
    $('#riskSearch').on('keyup', function() {
        const value = $(this).val().toLowerCase();
        $('.enterprise-table tbody tr').filter(function() {
            $(this).toggle($(this).text().toLowerCase().indexOf(value) > -1);
        });
    });
    
    // Filter functionality
    $('#filterStatus, #filterLevel, #filterCategory, #filterDepartment').on('change', function() {
        const status = $('#filterStatus').val();
        const level = $('#filterLevel').val();
        const category = $('#filterCategory').val();
        const department = $('#filterDepartment').val();
        
        $('.enterprise-table tbody tr').each(function() {
            let show = true;
            
            if (status) {
                const rowStatus = $(this).find('.status-badge').text().toLowerCase().trim();
                if (rowStatus !== status) show = false;
            }
            
            if (level) {
                const rowLevel = $(this).find('.risk-level').text().toLowerCase().trim();
                if (rowLevel !== level) show = false;
            }
            
            $(this).toggle(show);
        });
    });
});
</script>