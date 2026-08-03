<?php
/**
 * Company Settings Page
 * 
 * @var string $title
 * @var array $company_data
 */
?>

<?php $page_title = 'Company Settings'; ?>
<?php $active_page = 'settings'; ?>

<div class="company-settings-container">
    <div class="row">
        <div class="col-lg-10 mx-auto">
            <!-- Header -->
            <div class="d-flex justify-content-between align-items-center mb-4">
                <div>
                    <h5><i class="fas fa-building me-2 text-primary"></i> Company Settings</h5>
                    <p class="text-muted">Manage your company profile and branding</p>
                </div>
                <a href="<?php echo BASE_URL; ?>/settings" class="btn btn-outline-secondary">
                    <i class="fas fa-arrow-left me-2"></i> Back to Settings
                </a>
            </div>
            
            <!-- Company Form -->
            <div class="card">
                <div class="card-header-gradient">
                    <h6 class="mb-0 text-white"><i class="fas fa-building me-2"></i> Company Information</h6>
                    <small class="text-white-50">Update your company details</small>
                </div>
                <div class="card-body p-4">
                    <form method="POST" action="<?php echo BASE_URL; ?>/settings/company/update" enctype="multipart/form-data">
                        <input type="hidden" name="csrf_token" value="<?php echo $csrf_token ?? ''; ?>">
                        
                        <div class="row g-4">
                            <!-- Company Logo -->
                            <div class="col-12">
                                <div class="form-group">
                                    <label class="form-label">Company Logo</label>
                                    <div class="logo-upload">
                                        <?php if (!empty($company_data['logo'])): ?>
                                            <div class="current-logo">
                                                <img src="<?php echo UPLOADS_URL; ?>/<?php echo $company_data['logo']; ?>" alt="Company Logo">
                                                <button type="button" class="btn btn-sm btn-outline-danger remove-logo">
                                                    <i class="fas fa-times"></i>
                                                </button>
                                            </div>
                                        <?php else: ?>
                                            <div class="logo-placeholder">
                                                <i class="fas fa-building"></i>
                                                <span>No logo uploaded</span>
                                            </div>
                                        <?php endif; ?>
                                        <input type="file" class="form-control mt-2" name="logo" accept="image/*">
                                        <small class="text-muted">Recommended size: 200x200px. Max 2MB.</small>
                                    </div>
                                </div>
                            </div>
                            
                            <!-- Company Name -->
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label class="form-label required">Company Name</label>
                                    <input type="text" class="form-control" name="company_name" 
                                           value="<?php echo htmlspecialchars($company_data['name'] ?? ''); ?>" required>
                                </div>
                            </div>
                            
                            <!-- Company Short Name -->
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label class="form-label">Short Name</label>
                                    <input type="text" class="form-control" name="short_name" 
                                           value="<?php echo htmlspecialchars($company_data['short_name'] ?? ''); ?>">
                                </div>
                            </div>
                            
                            <!-- Registration Number -->
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label class="form-label">Registration Number</label>
                                    <input type="text" class="form-control" name="registration_number" 
                                           value="<?php echo htmlspecialchars($company_data['registration_number'] ?? ''); ?>">
                                </div>
                            </div>
                            
                            <!-- Tax ID -->
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label class="form-label">Tax ID / NTN</label>
                                    <input type="text" class="form-control" name="tax_id" 
                                           value="<?php echo htmlspecialchars($company_data['tax_id'] ?? ''); ?>">
                                </div>
                            </div>
                            
                            <!-- Industry -->
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label class="form-label">Industry</label>
                                    <select class="form-select" name="industry">
                                        <option value="banking" <?php echo ($company_data['industry'] ?? '') === 'banking' ? 'selected' : ''; ?>>Banking</option>
                                        <option value="finance" <?php echo ($company_data['industry'] ?? '') === 'finance' ? 'selected' : ''; ?>>Finance</option>
                                        <option value="insurance" <?php echo ($company_data['industry'] ?? '') === 'insurance' ? 'selected' : ''; ?>>Insurance</option>
                                        <option value="technology" <?php echo ($company_data['industry'] ?? '') === 'technology' ? 'selected' : ''; ?>>Technology</option>
                                        <option value="other" <?php echo ($company_data['industry'] ?? '') === 'other' ? 'selected' : ''; ?>>Other</option>
                                    </select>
                                </div>
                            </div>
                            
                            <!-- Founded Year -->
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label class="form-label">Founded Year</label>
                                    <input type="number" class="form-control" name="founded_year" 
                                           value="<?php echo htmlspecialchars($company_data['founded_year'] ?? ''); ?>" min="1900" max="<?php echo date('Y'); ?>">
                                </div>
                            </div>
                            
                            <!-- Address -->
                            <div class="col-12">
                                <div class="form-group">
                                    <label class="form-label">Address</label>
                                    <textarea class="form-control" name="address" rows="2"><?php echo htmlspecialchars($company_data['address'] ?? ''); ?></textarea>
                                </div>
                            </div>
                            
                            <!-- City, State, Country -->
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label class="form-label">City</label>
                                    <input type="text" class="form-control" name="city" 
                                           value="<?php echo htmlspecialchars($company_data['city'] ?? ''); ?>">
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label class="form-label">State/Province</label>
                                    <input type="text" class="form-control" name="state" 
                                           value="<?php echo htmlspecialchars($company_data['state'] ?? ''); ?>">
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label class="form-label">Country</label>
                                    <input type="text" class="form-control" name="country" 
                                           value="<?php echo htmlspecialchars($company_data['country'] ?? 'Pakistan'); ?>">
                                </div>
                            </div>
                            
                            <!-- Contact Information -->
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label class="form-label">Phone</label>
                                    <input type="text" class="form-control" name="phone" 
                                           value="<?php echo htmlspecialchars($company_data['phone'] ?? ''); ?>">
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label class="form-label">Email</label>
                                    <input type="email" class="form-control" name="email" 
                                           value="<?php echo htmlspecialchars($company_data['email'] ?? ''); ?>">
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label class="form-label">Website</label>
                                    <input type="url" class="form-control" name="website" 
                                           value="<?php echo htmlspecialchars($company_data['website'] ?? ''); ?>">
                                </div>
                            </div>
                        </div>
                        
                        <!-- Form Actions -->
                        <div class="form-actions mt-4 pt-3 border-top">
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-save me-2"></i> Save Settings
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
.company-settings-container {
    padding: 0;
}

.card-header-gradient {
    background: linear-gradient(135deg, #0B3D91, #2563EB);
    padding: 20px 24px;
    border-radius: 12px 12px 0 0;
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

.logo-upload {
    padding: 12px;
    border: 1px dashed #E2E8F0;
    border-radius: 8px;
}

.current-logo {
    position: relative;
    display: inline-block;
}

.current-logo img {
    width: 100px;
    height: 100px;
    object-fit: contain;
    border-radius: 8px;
    background: #F8FAFC;
    padding: 8px;
}

.current-logo .remove-logo {
    position: absolute;
    top: -8px;
    right: -8px;
    width: 24px;
    height: 24px;
    border-radius: 50%;
    padding: 0;
    display: flex;
    align-items: center;
    justify-content: center;
}

.logo-placeholder {
    width: 100px;
    height: 100px;
    border-radius: 8px;
    background: #F8FAFC;
    display: flex;
    flex-direction: column;
    align-items: center;
    justify-content: center;
    color: #94A3B8;
}

.logo-placeholder i {
    font-size: 32px;
    margin-bottom: 4px;
}

.logo-placeholder span {
    font-size: 12px;
}

.form-actions {
    display: flex;
    gap: 12px;
    flex-wrap: wrap;
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
    // Remove logo
    $('.remove-logo').on('click', function() {
        if (confirm('Remove the current logo?')) {
            const btn = $(this);
            btn.prop('disabled', true);
            
            $.ajax({
                url: '<?php echo BASE_URL; ?>/settings/company/remove-logo',
                method: 'POST',
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