<?php
/**
 * Risk Mitigation Plan Page
 * 
 * @var string $title
 * @var array $mitigation_plans
 * @var array $stats
 */
?>

<?php $page_title = 'Mitigation Plans'; ?>
<?php $active_page = 'risk'; ?>

<div class="mitigation-container">
    <!-- Stats -->
    <div class="row g-4 mb-4">
        <div class="col-xl-3 col-lg-6">
            <div class="stat-card">
                <div class="stat-label">Total Plans</div>
                <div class="stat-number"><?php echo $stats['total'] ?? 45; ?></div>
            </div>
        </div>
        <div class="col-xl-3 col-lg-6">
            <div class="stat-card">
                <div class="stat-label">In Progress</div>
                <div class="stat-number text-warning"><?php echo $stats['in_progress'] ?? 18; ?></div>
            </div>
        </div>
        <div class="col-xl-3 col-lg-6">
            <div class="stat-card">
                <div class="stat-label">Completed</div>
                <div class="stat-number text-success"><?php echo $stats['completed'] ?? 22; ?></div>
            </div>
        </div>
        <div class="col-xl-3 col-lg-6">
            <div class="stat-card">
                <div class="stat-label">Overdue</div>
                <div class="stat-number text-danger"><?php echo $stats['overdue'] ?? 5; ?></div>
            </div>
        </div>
    </div>
    
    <!-- Mitigation Plans List -->
    <div class="card">
        <div class="card-header d-flex justify-content-between align-items-center">
            <span><i class="fas fa-shield-alt me-2"></i> Mitigation Plans</span>
            <button class="btn btn-primary btn-sm" data-bs-toggle="modal" data-bs-target="#addPlanModal">
                <i class="fas fa-plus me-1"></i> Add Plan
            </button>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover enterprise-table mb-0">
                    <thead>
                        <tr>
                            <th>Plan ID</th>
                            <th>Risk</th>
                            <th>Description</th>
                            <th>Status</th>
                            <th>Progress</th>
                            <th>Due Date</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (!empty($mitigation_plans)): ?>
                            <?php foreach ($mitigation_plans as $plan): ?>
                                <tr>
                                    <td><span class="plan-id">#<?php echo str_pad($plan->id, 4, '0', STR_PAD_LEFT); ?></span></td>
                                    <td>
                                        <a href="<?php echo BASE_URL; ?>/risk/<?php echo $plan->risk_id; ?>" class="risk-link">
                                            <?php echo htmlspecialchars($plan->risk_code); ?>
                                        </a>
                                    </td>
                                    <td><?php echo htmlspecialchars(substr($plan->description, 0, 40)) . '...'; ?></td>
                                    <td>
                                        <span class="status-badge <?php echo $plan->status; ?>">
                                            <?php echo ucfirst($plan->status); ?>
                                        </span>
                                    </td>
                                    <td>
                                        <div class="progress" style="height: 6px; width: 100px;">
                                            <div class="progress-bar" style="width: <?php echo $plan->progress; ?>%; 
                                                 background: <?php echo $plan->progress >= 80 ? '#22C55E' : ($plan->progress >= 50 ? '#F59E0B' : '#EF4444'); ?>;">
                                            </div>
                                        </div>
                                        <span class="progress-text"><?php echo $plan->progress; ?>%</span>
                                    </td>
                                    <td>
                                        <?php if ($plan->due_date): ?>
                                            <span class="<?php echo strtotime($plan->due_date) < time() ? 'text-danger' : 'text-muted'; ?>">
                                                <?php echo date('d M Y', strtotime($plan->due_date)); ?>
                                            </span>
                                        <?php else: ?>
                                            <span class="text-muted">Not set</span>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <button class="btn btn-sm btn-outline-primary update-progress" data-id="<?php echo $plan->id; ?>">
                                            <i class="fas fa-chart-line"></i>
                                        </button>
                                        <button class="btn btn-sm btn-outline-secondary view-details" data-id="<?php echo $plan->id; ?>">
                                            <i class="fas fa-eye"></i>
                                        </button>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="7" class="text-center py-4 text-muted">
                                    <i class="fas fa-inbox fa-2x d-block mb-2"></i>
                                    No mitigation plans found
                                </td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<!-- Add Plan Modal -->
<div class="modal fade" id="addPlanModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title"><i class="fas fa-plus-circle me-2"></i> Add Mitigation Plan</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form method="POST" action="<?php echo BASE_URL; ?>/risk/mitigation">
                <div class="modal-body">
                    <input type="hidden" name="csrf_token" value="<?php echo $csrf_token ?? ''; ?>">
                    
                    <div class="row g-3">
                        <div class="col-12">
                            <label class="form-label">Risk</label>
                            <select class="form-select" name="risk_id" required>
                                <option value="">Select Risk</option>
                                <?php foreach ($risks ?? [] as $risk): ?>
                                    <option value="<?php echo $risk->id; ?>"><?php echo htmlspecialchars($risk->risk_code . ' - ' . $risk->title); ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-12">
                            <label class="form-label">Description</label>
                            <textarea class="form-control" name="description" rows="3" required></textarea>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Status</label>
                            <select class="form-select" name="status">
                                <option value="planned">Planned</option>
                                <option value="in_progress">In Progress</option>
                                <option value="completed">Completed</option>
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Progress (%)</label>
                            <input type="number" class="form-control" name="progress" min="0" max="100" value="0">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Due Date</label>
                            <input type="date" class="form-control" name="due_date">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Assigned To</label>
                            <select class="form-select" name="assigned_to">
                                <option value="">Select User</option>
                                <?php foreach ($users ?? [] as $user): ?>
                                    <option value="<?php echo $user->id; ?>"><?php echo htmlspecialchars($user->full_name); ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">Create Plan</button>
                </div>
            </form>
        </div>
    </div>
</div>

<style>
.mitigation-container {
    padding: 0;
}

.stat-card {
    background: #fff;
    padding: 20px;
    border-radius: 12px;
    box-shadow: 0 1px 3px rgba(0,0,0,0.06);
    height: 100%;
    transition: all 0.3s;
}

.stat-card:hover {
    box-shadow: 0 4px 12px rgba(0,0,0,0.1);
    transform: translateY(-2px);
}

.stat-label {
    font-size: 14px;
    color: #64748B;
    font-weight: 500;
}

.stat-number {
    font-size: 28px;
    font-weight: 700;
    color: #1E293B;
}

.stat-number.text-warning { color: #F59E0B; }
.stat-number.text-success { color: #22C55E; }
.stat-number.text-danger { color: #EF4444; }

.plan-id {
    font-family: 'Courier New', monospace;
    font-weight: 600;
    color: #2563EB;
}

.risk-link {
    color: #1E293B;
    text-decoration: none;
    font-weight: 500;
}

.risk-link:hover {
    color: #2563EB;
}

.status-badge {
    padding: 4px 12px;
    border-radius: 12px;
    font-size: 12px;
    font-weight: 500;
}

.status-badge.planned { background: #F1F5F9; color: #64748B; }
.status-badge.in_progress { background: #DBEAFE; color: #3B82F6; }
.status-badge.completed { background: #D1FAE5; color: #10B981; }
.status-badge.overdue { background: #FEE2E2; color: #DC2626; }

.progress-text {
    font-size: 12px;
    color: #94A3B8;
    margin-left: 8px;
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

@media (max-width: 768px) {
    .stat-number {
        font-size: 22px;
    }
}
</style>

<script>
$(document).ready(function() {
    // Update progress button
    $('.update-progress').on('click', function() {
        const id = $(this).data('id');
        const currentProgress = $(this).closest('tr').find('.progress-bar').css('width');
        const newProgress = prompt('Enter new progress percentage (0-100):', 
            parseInt(currentProgress) || 0);
        if (newProgress !== null && newProgress >= 0 && newProgress <= 100) {
            $.ajax({
                url: '<?php echo BASE_URL; ?>/api/risk/mitigation/' + id + '/progress',
                method: 'POST',
                data: {
                    _csrf: '<?php echo $csrf_token ?? ''; ?>',
                    progress: newProgress
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