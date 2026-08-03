<?php
/**
 * Reminders Page
 * 
 * @var string $title
 * @var array $reminders
 * @var array $stats
 */
?>

<?php $page_title = 'Reminders'; ?>
<?php $active_page = 'notifications'; ?>

<div class="reminders-container">
    <!-- Header -->
    <div class="row mb-4">
        <div class="col-md-8">
            <h5><i class="fas fa-clock me-2 text-primary"></i> Reminders</h5>
            <p class="text-muted">Manage your task reminders and deadlines</p>
        </div>
        <div class="col-md-4 text-end">
            <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addReminderModal">
                <i class="fas fa-plus me-2"></i> Add Reminder
            </button>
        </div>
    </div>
    
    <!-- Stats -->
    <div class="row g-4 mb-4">
        <div class="col-xl-3 col-lg-6">
            <div class="stat-card">
                <div class="stat-icon" style="background: rgba(37, 99, 235, 0.1); color: #2563EB;">
                    <i class="fas fa-list"></i>
                </div>
                <div class="stat-info">
                    <div class="stat-value"><?php echo $stats['total'] ?? 24; ?></div>
                    <div class="stat-label">Total Reminders</div>
                </div>
            </div>
        </div>
        <div class="col-xl-3 col-lg-6">
            <div class="stat-card">
                <div class="stat-icon" style="background: rgba(239, 68, 68, 0.1); color: #EF4444;">
                    <i class="fas fa-exclamation-circle"></i>
                </div>
                <div class="stat-info">
                    <div class="stat-value text-danger"><?php echo $stats['overdue'] ?? 5; ?></div>
                    <div class="stat-label">Overdue</div>
                </div>
            </div>
        </div>
        <div class="col-xl-3 col-lg-6">
            <div class="stat-card">
                <div class="stat-icon" style="background: rgba(245, 158, 11, 0.1); color: #F59E0B;">
                    <i class="fas fa-clock"></i>
                </div>
                <div class="stat-info">
                    <div class="stat-value"><?php echo $stats['upcoming'] ?? 8; ?></div>
                    <div class="stat-label">Upcoming</div>
                </div>
            </div>
        </div>
        <div class="col-xl-3 col-lg-6">
            <div class="stat-card">
                <div class="stat-icon" style="background: rgba(34, 197, 94, 0.1); color: #22C55E;">
                    <i class="fas fa-check-circle"></i>
                </div>
                <div class="stat-info">
                    <div class="stat-value"><?php echo $stats['completed'] ?? 11; ?></div>
                    <div class="stat-label">Completed</div>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Reminders List -->
    <div class="card">
        <div class="card-header d-flex justify-content-between align-items-center">
            <span><i class="fas fa-list me-2"></i> All Reminders</span>
            <div>
                <select class="form-select form-select-sm" id="filterReminderStatus" style="width: auto; display: inline-block;">
                    <option value="all">All Status</option>
                    <option value="pending">Pending</option>
                    <option value="completed">Completed</option>
                    <option value="overdue">Overdue</option>
                </select>
            </div>
        </div>
        <div class="card-body p-0">
            <?php if (!empty($reminders)): ?>
                <?php foreach ($reminders as $reminder): ?>
                    <div class="reminder-item <?php echo $reminder->status; ?>" data-status="<?php echo $reminder->status; ?>">
                        <div class="reminder-check">
                            <input type="checkbox" class="form-check-input reminder-complete" 
                                   data-id="<?php echo $reminder->id; ?>" 
                                   <?php echo $reminder->status === 'completed' ? 'checked' : ''; ?>>
                        </div>
                        <div class="reminder-content">
                            <div class="reminder-header">
                                <div class="reminder-title <?php echo $reminder->status === 'completed' ? 'completed-text' : ''; ?>">
                                    <?php echo htmlspecialchars($reminder->title); ?>
                                </div>
                                <div class="reminder-priority">
                                    <span class="badge bg-<?php echo $reminder->priority === 'high' ? 'danger' : ($reminder->priority === 'medium' ? 'warning' : 'info'); ?>">
                                        <?php echo ucfirst($reminder->priority); ?>
                                    </span>
                                </div>
                            </div>
                            <?php if ($reminder->description): ?>
                                <div class="reminder-description">
                                    <?php echo htmlspecialchars($reminder->description); ?>
                                </div>
                            <?php endif; ?>
                            <div class="reminder-meta">
                                <span class="reminder-date">
                                    <i class="far fa-calendar-alt me-1"></i>
                                    <?php echo date('d M Y', strtotime($reminder->due_date)); ?>
                                </span>
                                <?php if ($reminder->assigned_to): ?>
                                    <span class="reminder-assignee">
                                        <i class="fas fa-user me-1"></i>
                                        <?php echo htmlspecialchars($reminder->assigned_to_name); ?>
                                    </span>
                                <?php endif; ?>
                                <?php if ($reminder->status === 'overdue'): ?>
                                    <span class="text-danger"><i class="fas fa-exclamation-triangle me-1"></i> Overdue</span>
                                <?php endif; ?>
                            </div>
                        </div>
                        <div class="reminder-actions">
                            <button class="btn btn-sm btn-outline-secondary edit-reminder" data-id="<?php echo $reminder->id; ?>">
                                <i class="fas fa-edit"></i>
                            </button>
                            <button class="btn btn-sm btn-outline-danger delete-reminder" data-id="<?php echo $reminder->id; ?>">
                                <i class="fas fa-trash"></i>
                            </button>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php else: ?>
                <div class="text-center text-muted py-5">
                    <i class="fas fa-clock fa-3x mb-3"></i>
                    <h5>No Reminders</h5>
                    <p>Create a reminder to stay on track</p>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<!-- Add Reminder Modal -->
<div class="modal fade" id="addReminderModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title"><i class="fas fa-plus-circle me-2"></i> Add Reminder</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form method="POST" action="<?php echo BASE_URL; ?>/notifications/reminders">
                <div class="modal-body">
                    <input type="hidden" name="csrf_token" value="<?php echo $csrf_token ?? ''; ?>">
                    
                    <div class="form-group mb-3">
                        <label class="form-label">Title</label>
                        <input type="text" class="form-control" name="title" required>
                    </div>
                    
                    <div class="form-group mb-3">
                        <label class="form-label">Description</label>
                        <textarea class="form-control" name="description" rows="3"></textarea>
                    </div>
                    
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label">Due Date</label>
                            <input type="date" class="form-control" name="due_date" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Priority</label>
                            <select class="form-select" name="priority">
                                <option value="low">Low</option>
                                <option value="medium" selected>Medium</option>
                                <option value="high">High</option>
                            </select>
                        </div>
                    </div>
                    
                    <div class="form-group mt-3">
                        <label class="form-label">Assigned To</label>
                        <select class="form-select" name="assigned_to">
                            <option value="">Myself</option>
                            <?php foreach ($users ?? [] as $user): ?>
                                <option value="<?php echo $user->id; ?>"><?php echo htmlspecialchars($user->full_name); ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">Create Reminder</button>
                </div>
            </form>
        </div>
    </div>
</div>

<style>
.reminders-container {
    padding: 0;
}

.stat-card {
    background: #fff;
    padding: 16px 20px;
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
    width: 44px;
    height: 44px;
    border-radius: 10px;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 18px;
    flex-shrink: 0;
}

.stat-info {
    flex: 1;
}

.stat-value {
    font-size: 22px;
    font-weight: 700;
    color: #1E293B;
}

.stat-value.text-danger { color: #EF4444; }

.stat-label {
    font-size: 14px;
    color: #64748B;
}

.reminder-item {
    display: flex;
    align-items: flex-start;
    padding: 16px 20px;
    border-bottom: 1px solid #F1F5F9;
    transition: background 0.2s;
}

.reminder-item:hover {
    background: #F8FAFC;
}

.reminder-item.overdue {
    background: #FEF2F2;
}

.reminder-item.overdue:hover {
    background: #FEE2E2;
}

.reminder-item.completed {
    background: #F0FDF4;
}

.reminder-item.completed:hover {
    background: #D1FAE5;
}

.reminder-check {
    margin-right: 12px;
    padding-top: 4px;
}

.reminder-check .form-check-input {
    cursor: pointer;
    width: 18px;
    height: 18px;
}

.reminder-content {
    flex: 1;
}

.reminder-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    flex-wrap: wrap;
    gap: 8px;
    margin-bottom: 4px;
}

.reminder-title {
    font-weight: 500;
    color: #1E293B;
    font-size: 15px;
}

.reminder-title.completed-text {
    text-decoration: line-through;
    color: #94A3B8;
}

.reminder-description {
    color: #64748B;
    font-size: 14px;
    margin-bottom: 4px;
}

.reminder-meta {
    display: flex;
    flex-wrap: wrap;
    gap: 16px;
    font-size: 13px;
    color: #94A3B8;
}

.reminder-meta i {
    width: 14px;
}

.reminder-actions {
    display: flex;
    gap: 4px;
    margin-left: 12px;
}

@media (max-width: 768px) {
    .reminder-item {
        flex-wrap: wrap;
    }
    
    .reminder-check {
        width: 100%;
        margin-bottom: 8px;
    }
    
    .reminder-actions {
        margin-left: 0;
        width: 100%;
    }
}
</style>

<script>
$(document).ready(function() {
    // Complete reminder
    $('.reminder-complete').on('change', function() {
        const id = $(this).data('id');
        const checked = $(this).is(':checked');
        const item = $(this).closest('.reminder-item');
        
        $.ajax({
            url: '<?php echo BASE_URL; ?>/api/reminders/' + id + '/complete',
            method: 'POST',
            data: {
                _csrf: '<?php echo $csrf_token ?? ''; ?>',
                completed: checked
            },
            success: function(response) {
                if (response.success) {
                    item.toggleClass('completed');
                    item.find('.reminder-title').toggleClass('completed-text');
                    if (checked) {
                        item.removeClass('overdue');
                    }
                }
            }
        });
    });
    
    // Filter
    $('#filterReminderStatus').on('change', function() {
        const status = $(this).val();
        $('.reminder-item').each(function() {
            if (status === 'all' || $(this).data('status') === status) {
                $(this).show();
            } else {
                $(this).hide();
            }
        });
    });
    
    // Delete reminder
    $('.delete-reminder').on('click', function() {
        const id = $(this).data('id');
        if (confirm('Delete this reminder?')) {
            $.ajax({
                url: '<?php echo BASE_URL; ?>/api/reminders/' + id,
                method: 'DELETE',
                data: {
                    _csrf: '<?php echo $csrf_token ?? ''; ?>'
                },
                success: function(response) {
                    if (response.success) {
                        $(this).closest('.reminder-item').fadeOut(300, function() {
                            $(this).remove();
                        });
                    }
                }.bind(this)
            });
        }
    });
});
</script>