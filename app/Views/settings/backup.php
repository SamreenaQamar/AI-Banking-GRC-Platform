<?php
/**
 * Backup Settings Page
 * 
 * @var string $title
 * @var array $backups
 * @var array $stats
 */
?>

<?php $page_title = 'Backup Settings'; ?>
<?php $active_page = 'settings'; ?>

<div class="backup-settings-container">
    <div class="row">
        <div class="col-lg-10 mx-auto">
            <!-- Header -->
            <div class="d-flex justify-content-between align-items-center mb-4">
                <div>
                    <h5><i class="fas fa-database me-2 text-primary"></i> Backup Settings</h5>
                    <p class="text-muted">Manage database backups and restore operations</p>
                </div>
                <a href="<?php echo BASE_URL; ?>/settings" class="btn btn-outline-secondary">
                    <i class="fas fa-arrow-left me-2"></i> Back to Settings
                </a>
            </div>
            
            <!-- Backup Stats -->
            <div class="row g-4 mb-4">
                <div class="col-xl-3 col-lg-6">
                    <div class="stat-card">
                        <div class="stat-icon" style="background: rgba(37, 99, 235, 0.1); color: #2563EB;">
                            <i class="fas fa-database"></i>
                        </div>
                        <div class="stat-info">
                            <div class="stat-value"><?php echo $stats['total'] ?? 12; ?></div>
                            <div class="stat-label">Total Backups</div>
                        </div>
                    </div>
                </div>
                <div class="col-xl-3 col-lg-6">
                    <div class="stat-card">
                        <div class="stat-icon" style="background: rgba(34, 197, 94, 0.1); color: #22C55E;">
                            <i class="fas fa-check-circle"></i>
                        </div>
                        <div class="stat-info">
                            <div class="stat-value"><?php echo $stats['last_backup_size'] ?? '124'; ?> MB</div>
                            <div class="stat-label">Last Backup Size</div>
                        </div>
                    </div>
                </div>
                <div class="col-xl-3 col-lg-6">
                    <div class="stat-card">
                        <div class="stat-icon" style="background: rgba(245, 158, 11, 0.1); color: #F59E0B;">
                            <i class="fas fa-clock"></i>
                        </div>
                        <div class="stat-info">
                            <div class="stat-value"><?php echo $stats['last_backup'] ?? '2 hours ago'; ?></div>
                            <div class="stat-label">Last Backup</div>
                        </div>
                    </div>
                </div>
                <div class="col-xl-3 col-lg-6">
                    <div class="stat-card">
                        <div class="stat-icon" style="background: rgba(139, 92, 246, 0.1); color: #8B5CF6;">
                            <i class="fas fa-archive"></i>
                        </div>
                        <div class="stat-info">
                            <div class="stat-value"><?php echo $stats['storage_used'] ?? '2.4'; ?> GB</div>
                            <div class="stat-label">Storage Used</div>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Backup Actions -->
            <div class="card mb-4">
                <div class="card-header">
                    <i class="fas fa-cog me-2"></i> Backup Actions
                </div>
                <div class="card-body">
                    <div class="row g-3">
                        <div class="col-md-4">
                            <button class="btn btn-primary w-100" id="createBackupBtn">
                                <i class="fas fa-database me-2"></i> Create Backup
                            </button>
                        </div>
                        <div class="col-md-4">
                            <button class="btn btn-outline-primary w-100" id="scheduleBackupBtn" data-bs-toggle="modal" data-bs-target="#scheduleModal">
                                <i class="fas fa-clock me-2"></i> Schedule Backup
                            </button>
                        </div>
                        <div class="col-md-4">
                            <button class="btn btn-outline-secondary w-100" id="cleanupBackupsBtn">
                                <i class="fas fa-trash me-2"></i> Cleanup Old Backups
                            </button>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Backup List -->
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <span><i class="fas fa-list me-2"></i> Backup History</span>
                    <span class="text-muted small"><?php echo count($backups ?? []); ?> backups</span>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover enterprise-table mb-0">
                            <thead>
                                <tr>
                                    <th>Backup Name</th>
                                    <th>Size</th>
                                    <th>Type</th>
                                    <th>Created</th>
                                    <th>Status</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if (!empty($backups)): ?>
                                    <?php foreach ($backups as $backup): ?>
                                        <tr>
                                            <td>
                                                <i class="fas fa-file-archive me-2 text-warning"></i>
                                                <?php echo htmlspecialchars($backup->name); ?>
                                            </td>
                                            <td><?php echo formatSize($backup->size); ?></td>
                                            <td>
                                                <span class="badge bg-<?php echo $backup->type === 'full' ? 'primary' : 'secondary'; ?>">
                                                    <?php echo ucfirst($backup->type); ?>
                                                </span>
                                            </td>
                                            <td><?php echo date('d M Y h:i A', strtotime($backup->created_at)); ?></td>
                                            <td>
                                                <span class="status-badge <?php echo $backup->status; ?>">
                                                    <?php echo ucfirst($backup->status); ?>
                                                </span>
                                            </td>
                                            <td>
                                                <div class="btn-group btn-group-sm">
                                                    <a href="<?php echo BASE_URL; ?>/settings/backup/<?php echo $backup->id; ?>/download" 
                                                       class="btn btn-outline-primary" title="Download">
                                                        <i class="fas fa-download"></i>
                                                    </a>
                                                    <button class="btn btn-outline-warning restore-backup" 
                                                            data-id="<?php echo $backup->id; ?>" title="Restore">
                                                        <i class="fas fa-undo"></i>
                                                    </button>
                                                    <button class="btn btn-outline-danger delete-backup" 
                                                            data-id="<?php echo $backup->id; ?>" title="Delete">
                                                        <i class="fas fa-trash"></i>
                                                    </button>
                                                </div>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                <?php else: ?>
                                    <tr>
                                        <td colspan="6" class="text-center py-4 text-muted">
                                            <i class="fas fa-database fa-2x d-block mb-2"></i>
                                            No backups found
                                        </td>
                                    </tr>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Schedule Backup Modal -->
<div class="modal fade" id="scheduleModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title"><i class="fas fa-clock me-2"></i> Schedule Backup</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form method="POST" action="<?php echo BASE_URL; ?>/settings/backup/schedule">
                <div class="modal-body">
                    <input type="hidden" name="csrf_token" value="<?php echo $csrf_token ?? ''; ?>">
                    
                    <div class="form-group mb-3">
                        <label class="form-label">Frequency</label>
                        <select class="form-select" name="frequency">
                            <option value="daily">Daily</option>
                            <option value="weekly">Weekly</option>
                            <option value="monthly">Monthly</option>
                        </select>
                    </div>
                    
                    <div class="form-group mb-3">
                        <label class="form-label">Time</label>
                        <input type="time" class="form-control" name="time" value="02:00">
                    </div>
                    
                    <div class="form-group mb-3">
                        <label class="form-label">Backup Type</label>
                        <select class="form-select" name="type">
                            <option value="full">Full Backup</option>
                            <option value="incremental">Incremental Backup</option>
                        </select>
                    </div>
                    
                    <div class="form-group">
                        <label class="form-label">Retention Policy</label>
                        <select class="form-select" name="retention">
                            <option value="7">7 days</option>
                            <option value="14">14 days</option>
                            <option value="30" selected>30 days</option>
                            <option value="60">60 days</option>
                            <option value="90">90 days</option>
                        </select>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">Save Schedule</button>
                </div>
            </form>
        </div>
    </div>
</div>

<style>
.backup-settings-container {
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
    font-size: 20px;
    font-weight: 700;
    color: #1E293B;
}

.stat-label {
    font-size: 14px;
    color: #64748B;
}

.status-badge {
    padding: 4px 12px;
    border-radius: 12px;
    font-size: 12px;
    font-weight: 500;
}

.status-badge.success { background: #D1FAE5; color: #10B981; }
.status-badge.failed { background: #FEE2E2; color: #DC2626; }
.status-badge.in_progress { background: #FEF3C7; color: #F59E0B; }

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

<?php
/**
 * Helper function to format file size
 */
function formatSize($bytes) {
    $units = ['B', 'KB', 'MB', 'GB', 'TB'];
    $i = 0;
    while ($bytes >= 1024 && $i < count($units) - 1) {
        $bytes /= 1024;
        $i++;
    }
    return round($bytes, 2) . ' ' . $units[$i];
}
?>
</style>

<script>
$(document).ready(function() {
    // Create backup
    $('#createBackupBtn').on('click', function() {
        const btn = $(this);
        btn.html('<i class="fas fa-spinner fa-spin me-2"></i> Creating...');
        btn.prop('disabled', true);
        
        $.ajax({
            url: '<?php echo BASE_URL; ?>/api/settings/backup/create',
            method: 'POST',
            data: {
                _csrf: '<?php echo $csrf_token ?? ''; ?>'
            },
            success: function(response) {
                if (response.success) {
                    location.reload();
                } else {
                    btn.html('<i class="fas fa-database me-2"></i> Create Backup');
                    btn.prop('disabled', false);
                    alert('Failed to create backup: ' + response.message);
                }
            },
            error: function() {
                btn.html('<i class="fas fa-database me-2"></i> Create Backup');
                btn.prop('disabled', false);
                alert('An error occurred while creating the backup.');
            }
        });
    });
    
    // Restore backup
    $('.restore-backup').on('click', function() {
        const id = $(this).data('id');
        if (confirm('Restore this backup? This will overwrite current data.')) {
            $.ajax({
                url: '<?php echo BASE_URL; ?>/api/settings/backup/' + id + '/restore',
                method: 'POST',
                data: {
                    _csrf: '<?php echo $csrf_token ?? ''; ?>'
                },
                success: function(response) {
                    if (response.success) {
                        alert('Backup restored successfully!');
                        location.reload();
                    } else {
                        alert('Failed to restore backup: ' + response.message);
                    }
                }
            });
        }
    });
    
    // Delete backup
    $('.delete-backup').on('click', function() {
        const id = $(this).data('id');
        if (confirm('Delete this backup?')) {
            $.ajax({
                url: '<?php echo BASE_URL; ?>/api/settings/backup/' + id,
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
    
    // Cleanup old backups
    $('#cleanupBackupsBtn').on('click', function() {
        if (confirm('Delete all backups older than 30 days?')) {
            $.ajax({
                url: '<?php echo BASE_URL; ?>/api/settings/backup/cleanup',
                method: 'POST',
                data: {
                    _csrf: '<?php echo $csrf_token ?? ''; ?>',
                    days: 30
                },
                success: function(response) {
                    if (response.success) {
                        alert('Old backups cleaned up successfully!');
                        location.reload();
                    }
                }
            });
        }
    });
});
</script>