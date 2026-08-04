<?php
/**
 * AI Policy Generator View
 * 
 * @var string $title
 * @var array $frameworks
 * @var array $categories
 */
?>

<?php $page_title = 'AI Policy Generator'; ?>
<?php $active_page = 'ai'; ?>

<div class="policy-generator-container">
    <div class="row g-4">
        <!-- Generator Input Panel -->
        <div class="col-xl-5">
            <div class="card">
                <div class="card-header">
                    <i class="fas fa-cogs me-2"></i> Policy Configuration
                </div>
                <div class="card-body">
                    <form id="policyGeneratorForm">
                        <input type="hidden" name="csrf_token" value="<?php echo $csrf_token ?? ''; ?>">

                        <div class="form-group mb-3">
                            <label class="form-label">Policy Type</label>
                            <select class="form-select" name="policy_type" id="policyType">
                                <option value="aml">Anti-Money Laundering (AML)</option>
                                <option value="risk" selected>Risk Management</option>
                                <option value="compliance">Compliance</option>
                                <option value="audit">Internal Audit</option>
                                <option value="security">Cyber Security</option>
                                <option value="governance">Corporate Governance</option>
                            </select>
                        </div>

                        <div class="form-group mb-3">
                            <label class="form-label">Framework</label>
                            <select class="form-select" name="framework" id="framework">
                                <option value="iso27001">ISO 27001:2022</option>
                                <option value="nist">NIST CSF</option>
                                <option value="sbp" selected>SBP Regulations</option>
                                <option value="basel">Basel III</option>
                                <option value="custom">Custom Framework</option>
                            </select>
                        </div>

                        <div class="form-group mb-3">
                            <label class="form-label">Policy Name</label>
                            <input type="text" class="form-control" name="policy_name" 
                                   placeholder="e.g., Information Security Policy" required>
                        </div>

                        <div class="form-group mb-3">
                            <label class="form-label">Requirements / Description</label>
                            <textarea class="form-control" name="requirements" rows="4" 
                                      placeholder="Describe the policy requirements, scope, and specific needs..." required></textarea>
                        </div>

                        <div class="form-group mb-3">
                            <label class="form-label">Tone & Style</label>
                            <select class="form-select" name="tone">
                                <option value="formal">Formal & Professional</option>
                                <option value="standard" selected>Standard Corporate</option>
                                <option value="concise">Concise & Direct</option>
                                <option value="comprehensive">Comprehensive & Detailed</option>
                            </select>
                        </div>

                        <div class="form-group mb-3">
                            <label class="form-label">Additional Instructions</label>
                            <textarea class="form-control" name="instructions" rows="2" 
                                      placeholder="Any specific requirements or focus areas..."></textarea>
                        </div>

                        <div class="d-grid gap-2">
                            <button type="submit" class="btn btn-primary" id="generateBtn">
                                <i class="fas fa-wand-magic-sparkles me-2"></i> Generate Policy
                            </button>
                            <button type="reset" class="btn btn-outline-secondary">
                                <i class="fas fa-undo me-2"></i> Reset
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <!-- Output Panel -->
        <div class="col-xl-7">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <span><i class="fas fa-file-alt me-2"></i> Generated Policy</span>
                    <div class="btn-group btn-group-sm">
                        <button class="btn btn-outline-primary" id="copyPolicy" disabled>
                            <i class="fas fa-copy me-1"></i> Copy
                        </button>
                        <button class="btn btn-outline-success" id="downloadPolicy" disabled>
                            <i class="fas fa-download me-1"></i> Download
                        </button>
                        <button class="btn btn-outline-secondary" id="savePolicy" disabled>
                            <i class="fas fa-save me-1"></i> Save
                        </button>
                    </div>
                </div>
                <div class="card-body">
                    <div class="generated-content" id="generatedContent">
                        <div class="placeholder-content text-center py-5">
                            <i class="fas fa-file-contract fa-4x text-muted mb-3"></i>
                            <h5 class="text-muted">No Policy Generated</h5>
                            <p class="text-muted">Configure your policy requirements and click Generate</p>
                            <div class="features-list d-flex flex-wrap justify-content-center gap-2 mt-3">
                                <span class="badge bg-light text-dark"><i class="fas fa-check-circle text-success"></i> AI-Powered</span>
                                <span class="badge bg-light text-dark"><i class="fas fa-check-circle text-success"></i> Framework Compliant</span>
                                <span class="badge bg-light text-dark"><i class="fas fa-check-circle text-success"></i> Professional Formatting</span>
                                <span class="badge bg-light text-dark"><i class="fas fa-check-circle text-success"></i> Ready for Review</span>
                            </div>
                        </div>
                    </div>

                    <!-- Loading State -->
                    <div class="loading-state d-none text-center py-5" id="loadingState">
                        <div class="spinner-border text-primary mb-3" role="status">
                            <span class="visually-hidden">Generating...</span>
                        </div>
                        <h6>AI is generating your policy...</h6>
                        <p class="text-muted">This may take a few moments</p>
                        <div class="progress" style="height: 4px; max-width: 300px; margin: 0 auto;">
                            <div class="progress-bar progress-bar-striped progress-bar-animated" style="width: 100%;"></div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
.policy-generator-container {
    padding: 0;
}

.features-list .badge {
    padding: 6px 12px;
    border: 1px solid #E2E8F0;
}

.generated-policy-content {
    padding: 20px;
    background: #F8FAFC;
    border-radius: 8px;
    max-height: 500px;
    overflow-y: auto;
    line-height: 1.8;
    font-size: 14px;
}

.generated-policy-content h1 {
    font-size: 24px;
    color: #0B3D91;
    margin-bottom: 16px;
}

.generated-policy-content h2 {
    font-size: 18px;
    color: #1E293B;
    margin: 16px 0 8px;
}

.generated-policy-content h3 {
    font-size: 16px;
    color: #1E293B;
    margin: 12px 0 6px;
}

.generated-policy-content p {
    color: #64748B;
    margin-bottom: 8px;
}

.generated-policy-content ul,
.generated-policy-content ol {
    color: #64748B;
    padding-left: 20px;
    margin-bottom: 8px;
}

.generated-policy-content li {
    margin-bottom: 4px;
}

.generated-policy-content .policy-meta {
    background: #FFFFFF;
    padding: 12px 16px;
    border-radius: 6px;
    margin-bottom: 16px;
    border: 1px solid #E2E8F0;
}

.generated-policy-content .policy-meta span {
    display: inline-block;
    margin-right: 20px;
    font-size: 13px;
}

@media (max-width: 768px) {
    .generated-policy-content {
        max-height: 400px;
        padding: 12px;
    }
}
</style>

<script>
$(document).ready(function() {
    let generatedPolicy = '';

    // Form submission
    $('#policyGeneratorForm').on('submit', function(e) {
        e.preventDefault();

        const btn = $('#generateBtn');
        const content = $('#generatedContent');
        const loading = $('#loadingState');
        const copyBtn = $('#copyPolicy');
        const downloadBtn = $('#downloadPolicy');
        const saveBtn = $('#savePolicy');

        // Show loading
        content.addClass('d-none');
        loading.removeClass('d-none');
        btn.html('<span class="spinner-border spinner-border-sm me-2"></span> Generating...');
        btn.prop('disabled', true);

        // Get form data
        const formData = {
            policy_type: $('#policyType').val(),
            framework: $('#framework').val(),
            policy_name: $('input[name="policy_name"]').val(),
            requirements: $('textarea[name="requirements"]').val(),
            tone: $('select[name="tone"]').val(),
            instructions: $('textarea[name="instructions"]').val(),
            _csrf: $('input[name="csrf_token"]').val()
        };

        // Send to server
        $.ajax({
            url: '/api/ai/policy/generate',
            method: 'POST',
            data: formData,
            success: function(response) {
                loading.addClass('d-none');
                content.removeClass('d-none');
                btn.html('<i class="fas fa-wand-magic-sparkles me-2"></i> Generate Policy');
                btn.prop('disabled', false);

                if (response.success) {
                    generatedPolicy = response.data.content;
                    
                    // Display generated policy
                    content.html(`
                        <div class="generated-policy-content">
                            <div class="policy-meta">
                                <span><strong>Title:</strong> ${response.data.title || 'Generated Policy'}</span>
                                <span><strong>Version:</strong> 1.0</span>
                                <span><strong>Generated:</strong> ${new Date().toLocaleDateString()}</span>
                            </div>
                            ${response.data.content}
                            <div class="text-muted mt-3" style="font-size: 12px;">
                                <i class="fas fa-info-circle"></i> 
                                Generated by AI Banking GRC Platform. Please review and customize.
                            </div>
                        </div>
                    `);

                    // Enable buttons
                    copyBtn.prop('disabled', false);
                    downloadBtn.prop('disabled', false);
                    saveBtn.prop('disabled', false);

                    showToast('Policy generated successfully!', 'success');
                } else {
                    showToast(response.message || 'Generation failed', 'error');
                }
            },
            error: function() {
                loading.addClass('d-none');
                content.removeClass('d-none');
                btn.html('<i class="fas fa-wand-magic-sparkles me-2"></i> Generate Policy');
                btn.prop('disabled', false);
                showToast('An error occurred generating policy', 'error');
            }
        });
    });

    // Copy policy
    $('#copyPolicy').on('click', function() {
        if (generatedPolicy) {
            navigator.clipboard.writeText(generatedPolicy).then(function() {
                showToast('Policy copied to clipboard!', 'success');
            });
        }
    });

    // Download policy
    $('#downloadPolicy').on('click', function() {
        if (generatedPolicy) {
            const blob = new Blob([generatedPolicy], { type: 'text/html' });
            const url = URL.createObjectURL(blob);
            const a = document.createElement('a');
            a.href = url;
            a.download = 'generated-policy.html';
            document.body.appendChild(a);
            a.click();
            document.body.removeChild(a);
            URL.revokeObjectURL(url);
            showToast('Policy downloaded!', 'success');
        }
    });

    // Save policy
    $('#savePolicy').on('click', function() {
        if (generatedPolicy) {
            const csrfToken = $('input[name="csrf_token"]').val();
            $.ajax({
                url: '/api/ai/policy/save',
                method: 'POST',
                data: {
                    _csrf: csrfToken,
                    type: $('#policyType').val(),
                    title: $('input[name="policy_name"]').val(),
                    content: generatedPolicy
                },
                success: function(response) {
                    if (response.success) {
                        showToast('Policy saved successfully!', 'success');
                    }
                },
                error: function() {
                    showToast('Failed to save policy', 'error');
                }
            });
        }
    });

    // Toast notification
    function showToast(message, type) {
        const toast = $(`
            <div class="toast-notification ${type}">
                <i class="fas fa-${type === 'success' ? 'check-circle' : type === 'error' ? 'exclamation-circle' : 'info-circle'}"></i>
                ${message}
            </div>
        `);
        $('body').append(toast);
        setTimeout(() => {
            toast.fadeOut(300, function() { $(this).remove(); });
        }, 3000);
    }
});
</script>