<?php
/**
 * Create User Page
 * 
 * @var string $title
 * @var array $roles
 * @var array $departments
 */
?>

<?php $page_title = 'Create User'; ?>
<?php $active_page = 'users'; ?>

<div class="create-user-container">
    <div class="row">
        <div class="col-lg-10 mx-auto">
            <!-- Header -->
            <div class="d-flex justify-content-between align-items-center mb-4">
                <div>
                    <h5><i class="fas fa-user-plus me-2 text-primary"></i> Create New User</h5>
                    <p class="text-muted">Add a new user to the system</p>
                </div>
                <a href="<?php echo BASE_URL; ?>/users" class="btn btn-outline-secondary">
                    <i class="fas fa-arrow-left me-2"></i> Back to Users
                </a>
            </div>
            
            <!-- Form Card -->
            <div class="card">
                <div class="card-header-gradient">
                    <h6 class="mb-0 text-white"><i class="fas fa-user me-2"></i> User Information</h6>
                    <small class="text-white-50">Fill in the user details below</small>
                </div>
                <div class="card-body p-4">
                    <form method="POST" action="<?php echo BASE_URL; ?>/users" enctype="multipart/form-data" id="createUserForm">
                        <input type="hidden" name="csrf_token" value="<?php echo $csrf_token ?? ''; ?>">
                        
                        <div class="row g-4">
                            <!-- Personal Information -->
                            <div class="col-12">
                                <h6 class="section-title"><i class="fas fa-id-card me-2"></i> Personal Information</h6>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label class="form-label required">First Name</label>
                                    <input type="text" class="form-control" name="first_name" required>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label class="form-label required">Last Name</label>
                                    <input type="text" class="form-control" name="last_name" required>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label class="form-label required">Email Address</label>
                                    <input type="email" class="form-control" name="email" required>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label class="form-label required">Username</label>
                                    <input type="text" class="form-control" name="username" required>
                                    <small class="text-muted">Unique username for login</small>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label class="form-label required">Password</label>
                                    <input type="password" class="form-control" name="password" required minlength="8">
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label class="form-label required">Confirm Password</label>
                                    <input type="password" class="form-control" name="password_confirmation" required>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label class="form-label">Phone Number</label>
                                    <input type="text" class="form-control" name="phone">
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label class="form-label">Employee ID</label>
                                    <input type="text" class="form-control" name="employee_id">
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
                                            <option value="<?php echo $role->id; ?>"><?php echo htmlspecialchars($role->display_name); ?></option>
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
                                            <option value="<?php echo $dept->id; ?>"><?php echo htmlspecialchars($dept->name); ?></option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label class="form-label required">Status</label>
                                    <select class="form-select" name="status" required>
                                        <option value="active">Active</option>
                                        <option value="inactive">Inactive</option>
                                        <option value="pending">Pending</option>
                                        <option value="suspended">Suspended</option>
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label class="form-label">Profile Image</label>
                                    <input type="file" class="form-control" name="profile_image" accept="image/*">
                                </div>
                            </div>
                            
                            <!-- Additional Information -->
                            <div class="col-12 mt-3">
                                <h6 class="section-title"><i class="fas fa-address-card me-2"></i> Additional Information</h6>
                            </div>
                            <div class="col-12">
                                <div class="form-group">
                                    <label class="form-label">Address</label>
                                    <textarea class="form-control" name="address" rows="2"></textarea>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label class="form-label">City</label>
                                    <input type="text" class="form-control" name="city">
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label class="form-label">State/Province</label>
                                    <input type="text" class="form-control" name="state">
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label class="form-label">Postal Code</label>
                                    <input type="text" class="form-control" name="postal_code">
                                </div>
                            </div>
                        </div>
                        
                        <!-- Form Actions -->
                        <div class="form-actions mt-4 pt-3 border-top">
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-save me-2"></i> Create User
                            </button>
                            <a href="<?php echo BASE_URL; ?>/users" class="btn btn-secondary">
                                <i class="fas fa-times me-2"></i> Cancel
                            </a>
                            <button type="reset" class="btn btn-outline-secondary">
                                <i class="fas fa-undo me-2"></i> Reset
                            </button>
                        </div>
                    </form>
                </div>
            </div>
            
            <!-- Tips Card -->
            <div class="card mt-4">
                <div class="card-body">
                    <h6><i class="fas fa-lightbulb me-2 text-warning"></i> User Creation Tips</h6>
                    <ul class="tips-list">
                        <li>Use a strong password with at least 8 characters</li>
                        <li>Assign appropriate role based on user's responsibilities</li>
                        <li>Set status to "Pending" if user needs to verify email</li>
                        <li>Employee ID should match HR records</li>
                        <li>Upload a profile image for better identification</li>
                    </ul>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
.create-user-container {
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

.tips-list {
    margin: 8px 0 0;
    padding-left: 20px;
}

.tips-list li {
    padding: 4px 0;
    color: #64748B;
    font-size: 14px;
}

@media (max-width: 768px) {
    .form-actions {
        flex-direction: column;
    }
    
    .form-actions .btn {
        width: 100%;
    }
}
</style>

<script>
$(document).ready(function() {
    // Password validation
    $('#createUserForm input[name="password"]').on('keyup', function() {
        const password = $(this).val();
        const confirm = $('input[name="password_confirmation"]');
        
        if (confirm.val() && password !== confirm.val()) {
            confirm.addClass('is-invalid');
        } else {
            confirm.removeClass('is-invalid');
        }
    });
    
    // Confirm password validation
    $('#createUserForm input[name="password_confirmation"]').on('keyup', function() {
        const password = $('input[name="password"]').val();
        const confirm = $(this).val();
        
        if (password !== confirm) {
            $(this).addClass('is-invalid');
        } else {
            $(this).removeClass('is-invalid');
        }
    });
});
</script>