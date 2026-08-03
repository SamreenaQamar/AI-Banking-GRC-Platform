<?php
/**
 * Profile Settings Page
 * 
 * @var string $title
 * @var object $user
 */
?>

<?php $page_title = 'Profile Settings'; ?>
<?php $active_page = 'settings'; ?>

<div class="profile-settings-container">
    <div class="row">
        <div class="col-lg-10 mx-auto">
            <!-- Header -->
            <div class="d-flex justify-content-between align-items-center mb-4">
                <div>
                    <h5><i class="fas fa-user-circle me-2 text-primary"></i> Profile Settings</h5>
                    <p class="text-muted">Update your personal information and preferences</p>
                </div>
                <a href="<?php echo BASE_URL; ?>/settings" class="btn btn-outline-secondary">
                    <i class="fas fa-arrow-left me-2"></i> Back to Settings
                </a>
            </div>
            
            <!-- Profile Form -->
            <div class="card">
                <div class="card-header-gradient">
                    <h6 class="mb-0 text-white"><i class="fas fa-user me-2"></i> Personal Information</h6>
                    <small class="text-white-50">Update your profile details</small>
                </div>
                <div class="card-body p-4">
                    <form method="POST" action="<?php echo BASE_URL; ?>/settings/profile/update" enctype="multipart/form-data">
                        <input type="hidden" name="csrf_token" value="<?php echo $csrf_token ?? ''; ?>">
                        
                        <div class="row g-4">
                            <!-- Profile Image -->
                            <div class="col-12">
                                <div class="form-group">
                                    <label class="form-label">Profile Image</label>
                                    <div class="profile-image-upload">
                                        <div class="current-image">
                                            <?php if ($user->profile_image): ?>
                                                <img src="<?php echo UPLOADS_URL; ?>/<?php echo $user->profile_image; ?>" alt="Profile">
                                            <?php else: ?>
                                                <div class="image-placeholder">
                                                    <span><?php echo strtoupper(substr($user->first_name ?? 'U', 0, 1) . substr($user->last_name ?? '', 0, 1)); ?></span>
                                                </div>
                                            <?php endif; ?>
                                        </div>
                                        <div class="upload-controls">
                                            <input type="file" class="form-control" name="profile_image" accept="image/*">
                                            <small class="text-muted">Max 2MB. JPG, PNG, GIF</small>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            
                            <!-- Personal Information -->
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
                            
                            <!-- Contact Information -->
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label class="form-label required">Email Address</label>
                                    <input type="email" class="form-control" name="email" 
                                           value="<?php echo htmlspecialchars($user->email); ?>" required>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label class="form-label">Phone Number</label>
                                    <input type="text" class="form-control" name="phone" 
                                           value="<?php echo htmlspecialchars($user->phone ?? ''); ?>">
                                </div>
                            </div>
                            
                            <!-- Address -->
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
                            
                            <!-- Preferences -->
                            <div class="col-12">
                                <h6 class="section-title"><i class="fas fa-cog me-2"></i> Preferences</h6>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label class="form-label">Language</label>
                                    <select class="form-select" name="language">
                                        <option value="en" <?php echo ($user->language ?? 'en') === 'en' ? 'selected' : ''; ?>>English</option>
                                        <option value="ur" <?php echo ($user->language ?? '') === 'ur' ? 'selected' : ''; ?>>Urdu</option>
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label class="form-label">Timezone</label>
                                    <select class="form-select" name="timezone">
                                        <option value="Asia/Karachi" <?php echo ($user->timezone ?? 'Asia/Karachi') === 'Asia/Karachi' ? 'selected' : ''; ?>>Asia/Karachi (GMT+5)</option>
                                        <option value="Asia/Dubai" <?php echo ($user->timezone ?? '') === 'Asia/Dubai' ? 'selected' : ''; ?>>Asia/Dubai (GMT+4)</option>
                                        <option value="Asia/Kolkata" <?php echo ($user->timezone ?? '') === 'Asia/Kolkata' ? 'selected' : ''; ?>>Asia/Kolkata (GMT+5:30)</option>
                                        <option value="UTC">UTC</option>
                                    </select>
                                </div>
                            </div>
                            
                            <!-- Notifications Preferences -->
                            <div class="col-12">
                                <h6 class="section-title"><i class="fas fa-bell me-2"></i> Notification Preferences</h6>
                            </div>
                            <div class="col-md-4">
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" name="notify_compliance" id="notifyCompliance" 
                                           <?php echo ($user->notify_compliance ?? true) ? 'checked' : ''; ?>>
                                    <label class="form-check-label" for="notifyCompliance">Compliance Updates</label>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" name="notify_risk" id="notifyRisk" 
                                           <?php echo ($user->notify_risk ?? true) ? 'checked' : ''; ?>>
                                    <label class="form-check-label" for="notifyRisk">Risk Alerts</label>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" name="notify_audit" id="notifyAudit" 
                                           <?php echo ($user->notify_audit ?? true) ? 'checked' : ''; ?>>
                                    <label class="form-check-label" for="notifyAudit">Audit Notifications</label>
                                </div>
                            </div>
                        </div>
                        
                        <!-- Form Actions -->
                        <div class="form-actions mt-4 pt-3 border-top">
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-save me-2"></i> Save Profile
                            </button>
                            <button type="reset" class="btn btn-secondary">
                                <i class="fas fa-undo me-2"></i> Reset
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
.profile-settings-container {
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
    margin-bottom: 12px;
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

.profile-image-upload {
    display: flex;
    align-items: center;
    gap: 20px;
    flex-wrap: wrap;
}

.current-image {
    width: 80px;
    height: 80px;
    border-radius: 50%;
    overflow: hidden;
    background: #F8FAFC;
    flex-shrink: 0;
}

.current-image img {
    width: 100%;
    height: 100%;
    object-fit: cover;
}

.image-placeholder {
    width: 100%;
    height: 100%;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 32px;
    font-weight: 700;
    color: #2563EB;
    background: #DBEAFE;
}

.upload-controls {
    flex: 1;
}

.upload-controls .form-control {
    max-width: 300px;
}

.form-actions {
    display: flex;
    gap: 12px;
    flex-wrap: wrap;
}

@media (max-width: 768px) {
    .profile-image-upload {
        flex-direction: column;
        align-items: flex-start;
    }
    
    .form-actions {
        flex-direction: column;
    }
    
    .form-actions .btn {
        width: 100%;
    }
}
</style>