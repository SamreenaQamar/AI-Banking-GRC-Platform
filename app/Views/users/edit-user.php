<?php
/**
 * Edit User Page
 * 
 * @var string $title
 * @var object $user
 * @var array $roles
 * @var array $departments
 * @var array $statuses
 */
?>

<?php $page_title = 'Edit User'; ?>
<?php $active_page = 'users'; ?>

<div class="edit-user-container">
    <div class="row">
        <div class="col-lg-10 mx-auto">
            <!-- Header -->
            <div class="d-flex justify-content-between align-items-center mb-4">
                <div>
                    <h5><i class="fas fa-user-edit me-2 text-primary"></i> Edit User</h5>
                    <p class="text-muted">Update user information and settings</p>
                </div>
                <div>
                    <a href="<?php echo BASE_URL; ?>/users" class="btn btn-outline-secondary me-2">
                        <i class="fas fa-arrow-left me-2"></i> Back
                    </a>
                    <a href="<?php echo BASE_URL; ?>/users/<?php echo $user->id; ?>" class="btn btn-outline-primary">
                        <i class="fas fa-eye me-2"></i> View Profile
                    </a>
                </div>
            </div>
            
            <!-- Status Banner -->
            <?php if ($user->status === 'suspended'): ?>
                <div class="alert alert-danger mb-4">
                    <i class="fas fa-exclamation-triangle me-2"></i>
                    This user account is <strong>Suspended</strong>. 
                    <a href="#" class="alert-link">Reactivate account</a>
                </div>
            <?php endif; ?>
            
            <!-- Form Card -->
            <div class="card">
                <div class="card-header-gradient">
                    <h6 class="mb-0 text-white"><i class="fas fa-user me-2"></i> Edit User: <?php echo htmlspecialchars($user->full_name ?? $user->username); ?></h6>
                    <small class="text-white-50">User ID: #<?php echo $user->id; ?></small>
                </div>
                <div class="card-body p-4">
                    <form method="POST" action="<?php echo BASE_URL; ?>/users/<?php echo $user->id; ?>/update" 
                          enctype="multipart/form-data" id="editUserForm">
                        <input type="hidden" name="csrf_token" value="<?php echo $csrf_token ?? ''; ?>">
                        <input type="hidden" name="id" value="<?php echo $user->id; ?>">
                        
                        <div class="row g-4">
                            <!-- Personal Information -->
                            <div class="col-12">
                                <h6 class="section-title"><i class="fas fa-id-card me-2"></i> Personal Information</h6>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label class="form-label required">First Name</label>
                                    <input type="text" class="form-control" name="first_name" 
                                           value="<?php echo htmlspecialchars($user->first_name); ?>" required>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label class="form-label required">Last Name</label>
                                    <input type="text" class="form-control" name="last_name" 
                                           value="<?php echo htmlspecialchars($user->last_name); ?>" required>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label class="form-label required">Email Address</label>
                                    <input type="email" class="form-control" name="email" 
                                           value="<?php echo htmlspecialchars($user->email); ?>" required>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label class="form-label required">Username</label>
                                    <input type="text" class="form-control" name="username" 
                                           value="<?php echo htmlspecialchars($user->username); ?>" required>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label class="form-label">Change Password</label>
                                    <input type="password" class="form-control" name="password" 
                                           placeholder="Leave blank to keep current" minlength="8">
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label class="form-label">Confirm Password</label>
                                    <input type="password" class="form-control" name="password_confirmation" 
                                           placeholder="Confirm new password">
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label class="form-label">Phone Number</label>
                                    <input type="text" class="form-control" name="phone" 
                                           value="<?php echo htmlspecialchars($user->phone ?? ''); ?>">
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label class="form-label">Employee ID</label>
                                    <input type="text" class="form-control" name="employee_id" 
                                           value="<?php echo htmlspecialchars($user->employee_id ?? ''); ?>">
                                </div>
                            </div>
                            
                            <!-- Account Information -->
                            <div class="col-12 mt-3">
                                <h6 class="section-title"><i class="fas fa-cog me-2"></i> Account Information</h6>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label class="form-label required">Role</label>
                                    <select class="form-select" name="role_id" required>
                                        <option value="">Select Role</option>
                                        <?php foreach ($roles ?? [] as $role): ?>
                                            <option value="<?php echo $role->id; ?>" 
                                                    <?php echo $user->role_id == $role->id ? 'selected' : ''; ?>>
                                                <?php echo htmlspecialchars($role->display_name); ?>
                                            </option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label class="form-label required">Department</label>
                                    <select class="form-select" name="department_id" required>
                                        <option value="">Select Department</option>
                                        <?php foreach ($departments ?? [] as $dept): ?>
                                            <option value="<?php echo $dept->id; ?>" 
                                                    <?php echo $user->department_id == $dept->id ? 'selected' : ''; ?>>
                                                <?php echo htmlspecialchars($dept->name); ?>
                                            </option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label class="form-label required">Status</label>
                                    <select class="form-select" name="status" required>
                                        <option value="active" <?php echo $user->status === 'active' ? 'selected' : ''; ?>>Active</option>
                                        <option value="inactive" <?php echo $user->status === 'inactive' ? 'selected' : ''; ?>>Inactive</option>
                                        <option value="pending" <?php echo $user->status === 'pending' ? 'selected' : ''; ?>>Pending</option>
                                        <option value="suspended" <?php echo $user->status === 'suspended' ? 'selected' : ''; ?>>Suspended</option>
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label class="form-label">Profile Image</label>
                                    <input type="file" class="form-control" name="profile_image" accept="image/*">
                                    <?php if ($user->profile_image): ?>
                                        <small class="text-muted">Current: <?php echo basename($user->profile_image); ?></small>
                                    <?php endif; ?>
                                </div>
                            </div>
                            
                            <!-- Additional Information -->
                            <div class="col-12 mt-3">
                                <h6 class="section-title"><i class="fas fa-address-card me-2"></i> Additional Information</h6>
                            </div>
                            <div class="col-12">
                                <div class="form-group">
                                    <label class="form-label">Address</label>
                                    <textarea class="form-control" name="address" rows="2"><?php echo htmlspecialchars($user->address ?? ''); ?></textarea>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label class="form-label">City</label>
                                    <input type="text" class="form-control" name="city" 
                                           value="<?php echo htmlspecialchars($user->city ?? ''); ?>">
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label class="form-label">State/Province</label>
                                    <input type="text" class="form-control" name="state" 
                                           value="<?php echo htmlspecialchars($user->state ?? ''); ?>">
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label class="form-label">Postal Code</label>
                                    <input type="text" class="form-control" name="postal_code" 
                                           value="<?php echo htmlspecialchars($user->postal_code ?? ''); ?>">
                                </div>
                            </div>
                        </div>
                        
                        <!-- Form Actions -->
                        <div class="form-actions mt-4 pt-3 border-top">
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-save me-2"></i> Update User
                            </button>
                            <a href="<?php echo BASE_URL; ?>/users" class="btn btn-secondary">
                                <i class="fas fa-times me-2"></i> Cancel
                            </a>
                            <?php if ($user->id != $current_user->id ?? 0): ?>
                                <button type="button" class="btn btn-danger delete-user" 
                                        data-id="<?php echo $user->id; ?>">
                                    <i class="fas fa-trash me-2"></i> Delete
                                </button>
                            <?php endif; ?>
                        </div>
                    </form>
                </div>
            </div>
            
            <!-- User Activity -->
            <div class="card mt-4">
                <div class="card-header">
                    <i class="fas fa-history me-2"></i> Recent Activity
                </div>
                <div class="card-body">
                    <?php if (!empty($user_activities)): ?>
                        <div class="activity-timeline">
                            <?php foreach ($user_activities as $activity): ?>
                                <div class="activity-item">
                                    <span class="activity-time"><?php echo date('d M Y h:i A', strtotime($activity->created_at)); ?></span>
                                    <span class="activity-action"><?php echo htmlspecialchars($activity->action); ?></span>
                                    <span class="activity-description"><?php echo htmlspecialchars($activity->description); ?></span>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    <?php else: ?>
                        <div class="text-center text-muted py-3">
                            <i class="fas fa-inbox fa-2x mb-2"></i>
                            <p>No recent activity</p>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
.edit-user-container {
    padding: 0;
}

.card-header-gradient {
    background: linear-gradient(135deg, #0B3D91, #2563EB);
    padding: 20px 24px;
    border-radius: 12px 12px 0 0;
}

.section-title {
    font-weight: 600;
    color: #1E293B;
    margin-bottom: 16px;
    padding-bottom: 8px;
    border-bottom: 2px solid #F1F5F9;
}

.required::after {
    content: ' *';
    color: #EF4444;
}

.form-group {
    margin-bottom: 0;
}

.form-group .form-label {
    font-weight: 500;
    font-size: 14px;
    color: #1E293B;
    margin-bottom: 6px;
}

.form-group .form-control,
.form-group .form-select {
    border-radius: 8px;
    border-color: #E2E8F0;
    font-size: 14px;
    transition: all 0.3s;
}

.form-group .form-control:focus,
.form-group .form-select:focus {
    border-color: #2563EB;
    box-shadow: 0 0 0 3px rgba(37, 99, 235, 0.1);
}

.form-actions {
    display: flex;
    gap: 12px;
    flex-wrap: wrap;
}

.activity-timeline {
    padding: 0;
}

.activity-item {
    display: flex;
    gap: 16px;
    padding: 8px 0;
    border-bottom: 1px solid #F1F5F9;
    font-size: 14px;
}

.activity-item:last-child {
    border-bottom: none;
}

.activity-time {
    color: #94A3B8;
    min-width: 150px;
    font-size: 13px;
}

.activity-action {
    font-weight: 500;
    color: #1E293B;
    min-width: 100px;
}

.activity-description {
    color: #64748B;
}

@media (max-width: 768px) {
    .form-actions {
        flex-direction: column;
    }
    
    .form-actions .btn {
        width: 100%;
    }
    
    .activity-item {
        flex-direction: column;
        gap: 4px;
    }
    
    .activity-time {
        min-width: auto;
    }
}
</style>

<script>
$(document).ready(function() {
    // Password validation
    $('input[name="password"]').on('keyup', function() {
        const password = $(this).val();
        const confirm = $('input[name="password_confirmation"]');
        
        if (password && confirm.val() && password !== confirm.val()) {
            confirm.addClass('is-invalid');
        } else {
            confirm.removeClass('is-invalid');
        }
    });
    
    $('input[name="password_confirmation"]').on('keyup', function() {
        const password = $('input[name="password"]').val();
        const confirm = $(this).val();
        
        if (password && password !== confirm) {
            $(this).addClass('is-invalid');
        } else {
            $(this).removeClass('is-invalid');
        }
    });
    
    // Delete user
    $('.delete-user').on('click', function() {
        const id = $(this).data('id');
        if (confirm('Are you sure you want to delete this user?')) {
            $.ajax({
                url: '<?php echo BASE_URL; ?>/api/users/' + id,
                method: 'DELETE',
                data: {
                    _csrf: '<?php echo $csrf_token ?? ''; ?>'
                },
                success: function(response) {
                    if (response.success) {
                        window.location.href = '<?php echo BASE_URL; ?>/users';
                    }
                }
            });
        }
    });
});
</script>