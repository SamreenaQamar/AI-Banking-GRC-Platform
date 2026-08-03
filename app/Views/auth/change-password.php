<?php
/**
 * Change Password Page
 * 
 * @var string $title
 * @var object $user
 */
?>

<?php $page_title = 'Change Password'; ?>
<?php $active_page = 'profile'; ?>

<div class="container-fluid">
    <div class="row">
        <div class="col-lg-8 mx-auto">
            <div class="card">
                <div class="card-header-gradient">
                    <h5 class="mb-0"><i class="fas fa-key me-2"></i> Change Password</h5>
                    <small>Update your account password</small>
                </div>
                <div class="card-body p-4">
                    <?php if (isset($flash_messages)): ?>
                        <?php foreach ($flash_messages as $type => $messages): ?>
                            <?php foreach ($messages as $message): ?>
                                <div class="alert alert-<?php echo $type === 'error' ? 'danger' : 'success'; ?> alert-dismissible fade show">
                                    <i class="fas fa-<?php echo $type === 'error' ? 'exclamation-circle' : 'check-circle'; ?> me-2"></i>
                                    <?php echo htmlspecialchars($message); ?>
                                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                                </div>
                            <?php endforeach; ?>
                        <?php endforeach; ?>
                    <?php endif; ?>
                    
                    <form method="POST" action="<?php echo BASE_URL; ?>/profile/password">
                        <input type="hidden" name="csrf_token" value="<?php echo $csrf_token ?? ''; ?>">
                        
                        <div class="form-group mb-3">
                            <label for="current_password">Current Password</label>
                            <div class="input-group">
                                <span class="input-group-text bg-transparent border-end-0">
                                    <i class="fas fa-lock text-muted"></i>
                                </span>
                                <input type="password" class="form-control border-start-0" id="current_password" 
                                       name="current_password" placeholder="Enter current password" required>
                            </div>
                        </div>
                        
                        <div class="form-group mb-3">
                            <label for="new_password">New Password</label>
                            <div class="input-group">
                                <span class="input-group-text bg-transparent border-end-0">
                                    <i class="fas fa-key text-muted"></i>
                                </span>
                                <input type="password" class="form-control border-start-0" id="new_password" 
                                       name="new_password" placeholder="Enter new password" required minlength="8">
                            </div>
                            <div class="password-requirements mt-2">
                                <small class="text-muted">Password must contain:</small>
                                <div class="requirement-list">
                                    <span class="requirement-item" id="reqLength">
                                        <i class="fas fa-circle"></i> At least 8 characters
                                    </span>
                                    <span class="requirement-item" id="reqLower">
                                        <i class="fas fa-circle"></i> One lowercase letter
                                    </span>
                                    <span class="requirement-item" id="reqUpper">
                                        <i class="fas fa-circle"></i> One uppercase letter
                                    </span>
                                    <span class="requirement-item" id="reqSpecial">
                                        <i class="fas fa-circle"></i> One number or special character
                                    </span>
                                </div>
                            </div>
                        </div>
                        
                        <div class="form-group mb-4">
                            <label for="new_password_confirmation">Confirm New Password</label>
                            <div class="input-group">
                                <span class="input-group-text bg-transparent border-end-0">
                                    <i class="fas fa-check-circle text-muted"></i>
                                </span>
                                <input type="password" class="form-control border-start-0" id="new_password_confirmation" 
                                       name="new_password_confirmation" placeholder="Confirm new password" required>
                            </div>
                        </div>
                        
                        <div class="d-flex gap-2">
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-save me-2"></i> Update Password
                            </button>
                            <a href="<?php echo BASE_URL; ?>/profile" class="btn btn-outline-secondary">
                                <i class="fas fa-times me-2"></i> Cancel
                            </a>
                        </div>
                    </form>
                </div>
            </div>
            
            <!-- Security Tips -->
            <div class="card mt-4">
                <div class="card-body">
                    <h6><i class="fas fa-shield-alt me-2 text-primary"></i> Security Tips</h6>
                    <ul class="security-tips">
                        <li>Use a unique password that you don't use for other accounts</li>
                        <li>Make your password at least 12 characters long</li>
                        <li>Include a mix of uppercase, lowercase, numbers, and symbols</li>
                        <li>Enable two-factor authentication for extra security</li>
                    </ul>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
.password-requirements {
    font-size: 13px;
}

.requirement-list {
    display: flex;
    flex-wrap: wrap;
    gap: 12px;
    margin-top: 4px;
}

.requirement-item {
    display: flex;
    align-items: center;
    gap: 6px;
    color: #64748B;
    font-size: 12px;
}

.requirement-item i {
    font-size: 8px;
}

.requirement-item.valid {
    color: #22C55E;
}

.requirement-item.valid i {
    color: #22C55E;
}

.security-tips {
    list-style: none;
    padding: 0;
    margin: 8px 0 0;
}

.security-tips li {
    padding: 4px 0;
    padding-left: 24px;
    position: relative;
    color: #64748B;
    font-size: 14px;
}

.security-tips li::before {
    content: '✓';
    position: absolute;
    left: 0;
    color: #22C55E;
    font-weight: bold;
}
</style>

<script>
$(document).ready(function() {
    // Password validation
    $('#new_password').on('keyup', function() {
        const password = $(this).val();
        
        // Length check
        $('#reqLength').toggleClass('valid', password.length >= 8);
        
        // Lowercase check
        $('#reqLower').toggleClass('valid', /[a-z]/.test(password));
        
        // Uppercase check
        $('#reqUpper').toggleClass('valid', /[A-Z]/.test(password));
        
        // Special character check
        $('#reqSpecial').toggleClass('valid', /[0-9!@#$%^&*]/.test(password));
    });
});
</script>