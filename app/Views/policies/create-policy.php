<?php
/**
 * Create Policy Page
 * 
 * @var string $title
 * @var array $categories
 * @var array $departments
 * @var array $users
 * @var array $statuses
 */
?>

<?php $page_title = 'Create Policy'; ?>
<?php $active_page = 'policies'; ?>

<div class="create-policy-container">
    <div class="row">
        <div class="col-lg-10 mx-auto">
            <!-- Header -->
            <div class="d-flex justify-content-between align-items-center mb-4">
                <div>
                    <h5><i class="fas fa-plus-circle me-2 text-primary"></i> Create New Policy</h5>
                    <p class="text-muted">Define a new organizational policy</p>
                </div>
                <a href="<?php echo BASE_URL; ?>/policies" class="btn btn-outline-secondary">
                    <i class="fas fa-arrow-left me-2"></i> Back to Policies
                </a>
            </div>
            
            <!-- Form Card -->
            <div class="card">
                <div class="card-header-gradient">
                    <h6 class="mb-0 text-white"><i class="fas fa-file-contract me-2"></i> Policy Details</h6>
                    <small class="text-white-50">Fill in the policy information below</small>
                </div>
                <div class="card-body p-4">
                    <form method="POST" action="<?php echo BASE_URL; ?>/policies" enctype="multipart/form-data" id="createPolicyForm">
                        <input type="hidden" name="csrf_token" value="<?php echo $csrf_token ?? ''; ?>">
                        
                        <div class="row g-4">
                            <!-- Policy Title -->
                            <div class="col-12">
                                <div class="form-group">
                                    <label class="form-label required">Policy Title</label>
                                    <input type="text" class="form-control" name="title" 
                                           placeholder="Enter policy title" required>
                                    <small class="text-muted">Clear and descriptive title for the policy</small>
                                </div>
                            </div>
                            
                            <!-- Category and Department -->
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label class="form-label required">Category</label>
                                    <select class="form-select" name="category" required>
                                        <option value="">Select Category</option>
                                        <?php foreach ($categories ?? [] as $key => $label): ?>
                                            <option value="<?php echo $key; ?>"><?php echo $label; ?></option>
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
                            
                            <!-- Version and Status -->
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label class="form-label required">Version</label>
                                    <input type="text" class="form-control" name="version" 
                                           value="1.0" required>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label class="form-label required">Status</label>
                                    <select class="form-select" name="status" required>
                                        <?php foreach ($statuses ?? [] as $key => $label): ?>
                                            <option value="<?php echo $key; ?>" <?php echo $key === 'draft' ? 'selected' : ''; ?>>
                                                <?php echo $label; ?>
                                            </option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                            </div>
                            
                            <!-- Owner and Description -->
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label class="form-label">Policy Owner</label>
                                    <select class="form-select" name="owner_id">
                                        <option value="">Select Owner</option>
                                        <?php foreach ($users ?? [] as $user): ?>
                                            <option value="<?php echo $user->id; ?>"><?php echo htmlspecialchars($user->full_name); ?></option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label class="form-label required">Effective Date</label>
                                    <input type="date" class="form-control" name="effective_date" 
                                           value="<?php echo date('Y-m-d'); ?>" required>
                                </div>
                            </div>
                            
                            <!-- Description -->
                            <div class="col-12">
                                <div class="form-group">
                                    <label class="form-label required">Description</label>
                                    <textarea class="form-control" name="description" rows="4" 
                                              placeholder="Describe the policy purpose and scope" required></textarea>
                                </div>
                            </div>
                            
                            <!-- Review and Expiry Dates -->
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label class="form-label">Review Date</label>
                                    <input type="date" class="form-control" name="review_date">
                                    <small class="text-muted">Date for next policy review</small>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label class="form-label">Expiry Date</label>
                                    <input type="date" class="form-control" name="expiry_date">
                                    <small class="text-muted">Date when policy expires</small>
                                </div>
                            </div>
                            
                            <!-- Mandatory and Acknowledgement -->
                            <div class="col-md-6">
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" name="mandatory" id="mandatory" checked>
                                    <label class="form-check-label" for="mandatory">
                                        <i class="fas fa-exclamation-circle text-warning me-1"></i>
                                        Mandatory Policy
                                    </label>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" name="acknowledges_required" id="acknowledge" checked>
                                    <label class="form-check-label" for="acknowledge">
                                        <i class="fas fa-check-circle text-success me-1"></i>
                                        Requires Acknowledgement
                                    </label>
                                </div>
                            </div>
                            
                            <!-- File Upload -->
                            <div class="col-12">
                                <div class="form-group">
                                    <label class="form-label">Upload Policy Document</label>
                                    <div class="upload-area" id="uploadArea">
                                        <div class="upload-content">
                                            <i class="fas fa-cloud-upload-alt"></i>
                                            <p>Drag & drop your file here or <span class="browse-link">browse</span></p>
                                            <small class="text-muted">Supports PDF, DOCX, DOC (Max 10MB)</small>
                                        </div>
                                        <input type="file" class="upload-input" name="document" 
                                               accept=".pdf,.docx,.doc">
                                    </div>
                                    <div class="upload-preview d-none" id="uploadPreview">
                                        <div class="file-preview">
                                            <i class="fas fa-file-pdf file-icon"></i>
                                            <div class="file-info">
                                                <span class="file-name">document.pdf</span>
                                                <span class="file-size">2.4 MB</span>
                                            </div>
                                            <button type="button" class="btn btn-sm btn-outline-danger remove-file">
                                                <i class="fas fa-times"></i>
                                            </button>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        
                        <!-- Form Actions -->
                        <div class="form-actions mt-4 pt-3 border-top">
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-save me-2"></i> Create Policy
                            </button>
                            <a href="<?php echo BASE_URL; ?>/policies" class="btn btn-secondary">
                                <i class="fas fa-times me-2"></i> Cancel
                            </a>
                            <button type="reset" class="btn btn-outline-secondary">
                                <i class="fas fa-undo me-2"></i> Reset
                            </button>
                        </div>
                    </form>
                </div>
            </div>
            
            <!-- Help Card -->
            <div class="card mt-4">
                <div class="card-body">
                    <h6><i class="fas fa-lightbulb me-2 text-warning"></i> Policy Creation Tips</h6>
                    <ul class="tips-list">
                        <li>Use clear and descriptive titles for easy identification</li>
                        <li>Define the policy scope and applicability clearly</li>
                        <li>Include references to relevant regulations or standards</li>
                        <li>Set appropriate review dates to ensure policy stays current</li>
                        <li>Upload supporting documents for complete context</li>
                    </ul>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
.create-policy-container {
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

.upload-area {
    border: 2px dashed #E2E8F0;
    border-radius: 10px;
    padding: 40px 20px;
    text-align: center;
    cursor: pointer;
    transition: all 0.3s;
    position: relative;
    background: #F8FAFC;
}

.upload-area:hover {
    border-color: #2563EB;
    background: #F0F7FF;
}

.upload-area.dragover {
    border-color: #2563EB;
    background: #DBEAFE;
}

.upload-content i {
    font-size: 48px;
    color: #94A3B8;
    margin-bottom: 12px;
}

.upload-content p {
    color: #64748B;
    margin: 0;
}

.upload-content .browse-link {
    color: #2563EB;
    font-weight: 500;
    cursor: pointer;
}

.upload-input {
    position: absolute;
    top: 0;
    left: 0;
    right: 0;
    bottom: 0;
    opacity: 0;
    cursor: pointer;
}

.upload-preview {
    margin-top: 12px;
}

.file-preview {
    display: flex;
    align-items: center;
    padding: 12px 16px;
    background: #F8FAFC;
    border-radius: 8px;
    border: 1px solid #E2E8F0;
}

.file-preview .file-icon {
    font-size: 32px;
    color: #2563EB;
    margin-right: 12px;
}

.file-preview .file-info {
    flex: 1;
}

.file-preview .file-name {
    display: block;
    font-weight: 500;
    color: #1E293B;
}

.file-preview .file-size {
    font-size: 12px;
    color: #94A3B8;
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
    // File upload handling
    const uploadArea = $('#uploadArea');
    const uploadInput = $('.upload-input');
    const uploadPreview = $('#uploadPreview');
    
    uploadArea.on('click', function() {
        uploadInput.click();
    });
    
    uploadInput.on('change', function() {
        const file = this.files[0];
        if (file) {
            showFilePreview(file);
        }
    });
    
    // Drag and drop
    uploadArea.on('dragover', function(e) {
        e.preventDefault();
        $(this).addClass('dragover');
    });
    
    uploadArea.on('dragleave', function(e) {
        e.preventDefault();
        $(this).removeClass('dragover');
    });
    
    uploadArea.on('drop', function(e) {
        e.preventDefault();
        $(this).removeClass('dragover');
        const file = e.originalEvent.dataTransfer.files[0];
        if (file) {
            uploadInput[0].files = e.originalEvent.dataTransfer.files;
            showFilePreview(file);
        }
    });
    
    function showFilePreview(file) {
        const validTypes = ['application/pdf', 'application/vnd.openxmlformats-officedocument.wordprocessingml.document', 'application/msword'];
        if (!validTypes.includes(file.type)) {
            alert('Please upload a PDF, DOCX, or DOC file.');
            return;
        }
        
        const sizeInMB = file.size / (1024 * 1024);
        if (sizeInMB > 10) {
            alert('File size must be less than 10MB.');
            return;
        }
        
        uploadPreview.removeClass('d-none');
        uploadPreview.find('.file-name').text(file.name);
        uploadPreview.find('.file-size').text(sizeInMB.toFixed(1) + ' MB');
        uploadArea.hide();
    }
    
    // Remove file
    $('.remove-file').on('click', function() {
        uploadInput.val('');
        uploadPreview.addClass('d-none');
        uploadArea.show();
    });
});
</script>