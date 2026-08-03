<?php
/**
 * AI Policy Generator Page
 * 
 * @var string $title
 * @var array $frameworks
 * @var array $categories
 */
?>

<?php $page_title = 'AI Policy Generator'; ?>
<?php $active_page = 'policies'; ?>

<div class="policy-generator-container">
    <div class="row">
        <div class="col-lg-12">
            <!-- Header -->
            <div class="d-flex justify-content-between align-items-center mb-4">
                <div>
                    <h5><i class="fas fa-robot me-2 text-primary"></i> AI Policy Generator</h5>
                    <p class="text-muted">Generate professional policies using AI assistance</p>
                </div>
                <a href="<?php echo BASE_URL; ?>/policies" class="btn btn-outline-secondary">
                    <i class="fas fa-arrow-left me-2"></i> Back to Policies
                </a>
            </div>
            
            <!-- Generator Interface -->
            <div class="row g-4">
                <!-- Input Panel -->
                <div class="col-xl-5">
                    <div class="card">
                        <div class="card-header">
                            <i class="fas fa-cogs me-2"></i> Policy Configuration
                        </div>
                        <div class="card-body">
                            <form id="generatePolicyForm">
                                <input type="hidden" name="csrf_token" value="<?php echo $csrf_token ?? ''; ?>">
                                
                                <div class="form-group mb-3">
                                    <label class="form-label">Policy Type</label>
                                    <select class="form-select" name="policy_type" id="policyType">
                                        <option value="governance">Corporate Governance</option>
                                        <option value="risk_management">Risk Management</option>
                                        <option value="compliance">Compliance</option>
                                        <option value="information_security">Information Security</option>
                                        <option value="data_privacy">Data Privacy</option>
                                        <option value="hr">Human Resources</option>
                                        <option value="finance">Finance & Accounting</option>
                                        <option value="operations">Operations</option>
                                        <option value="it">Information Technology</option>
                                        <option value="bcm">Business Continuity</option>
                                        <option value="aml">Anti-Money Laundering</option>
                                        <option value="fraud">Fraud Prevention</option>
                                    </select>
                                </div>
                                
                                <div class="form-group mb-3">
                                    <label class="form-label">Framework</label>
                                    <select class="form-select" name="framework" id="framework">
                                        <option value="iso27001">ISO 27001</option>
                                        <option value="nist">NIST CSF</option>
                                        <option value="sbp">SBP Regulations</option>
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
                                    <label class="form-label">Description / Requirements</label>
                                    <textarea class="form-control" name="requirements" rows="4" 
                                              placeholder="Describe the policy requirements, scope, and specific needs..." required></textarea>
                                </div>
                                
                                <div class="form-group mb-3">
                                    <label class="form-label">Tone & Style</label>
                                    <select class="form-select" name="tone">
                                        <option value="formal">Formal & Professional</option>
                                        <option value="standard">Standard Corporate</option>
                                        <option value="concise">Concise & Direct</option>
                                        <option value="comprehensive">Comprehensive & Detailed</option>
                                    </select>
                                </div>
                                
                                <div class="form-group mb-3">
                                    <label class="form-label">Additional Instructions</label>
                                    <textarea class="form-control" name="instructions" rows="2" 
                                              placeholder="Any specific requirements or focus areas..."></textarea>
                                </div>
                                
                                <button type="submit" class="btn btn-primary w-100" id="generateBtn">
                                    <i class="fas fa-wand-magic-sparkles me-2"></i> Generate Policy
                                </button>
                            </form>
                        </div>
                    </div>
                    
                    <!-- Recent Generations -->
                    <div class="card mt-4">
                        <div class="card-header">
                            <i class="fas fa-clock me-2"></i> Recent Generations
                        </div>
                        <div class="card-body p-0">
                            <div class="recent-list">
                                <div class="recent-item">
                                    <div class="recent-info">
                                        <div class="recent-title">Information Security Policy</div>
                                        <div class="recent-meta">Generated 2 hours ago</div>
                                    </div>
                                    <div class="recent-actions">
                                        <button class="btn btn-sm btn-outline-primary">Load</button>
                                    </div>
                                </div>
                                <div class="recent-item">
                                    <div class="recent-info">
                                        <div class="recent-title">Data Privacy Policy</div>
                                        <div class="recent-meta">Generated 1 day ago</div>
                                    </div>
                                    <div class="recent-actions">
                                        <button class="btn btn-sm btn-outline-primary">Load</button>
                                    </div>
                                </div>
                                <div class="recent-item">
                                    <div class="recent-info">
                                        <div class="recent-title">Business Continuity Policy</div>
                                        <div class="recent-meta">Generated 3 days ago</div>
                                    </div>
                                    <div class="recent-actions">
                                        <button class="btn btn-sm btn-outline-primary">Load</button>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                
                <!-- Output Panel -->
                <div class="col-xl-7">
                    <div class="card">
                        <div class="card-header d-flex justify-content-between align-items-center">
                            <span><i class="fas fa-file-alt me-2"></i> Generated Policy</span>
                            <div class="btn-group btn-group-sm">
                                <button class="btn btn-outline-primary" id="copyPolicy">
                                    <i class="fas fa-copy me-1"></i> Copy
                                </button>
                                <button class="btn btn-outline-success" id="downloadPolicy">
                                    <i class="fas fa-download me-1"></i> Download
                                </button>
                                <button class="btn btn-outline-secondary" id="savePolicy">
                                    <i class="fas fa-save me-1"></i> Save
                                </button>
                            </div>
                        </div>
                        <div class="card-body">
                            <div class="generated-content" id="generatedContent">
                                <div class="placeholder-content">
                                    <i class="fas fa-file-contract fa-4x text-muted mb-3"></i>
                                    <h5 class="text-muted">No Policy Generated</h5>
                                    <p class="text-muted">Configure your policy requirements and click Generate</p>
                                    <div class="features-list">
                                        <span><i class="fas fa-check-circle text-success"></i> AI-Powered Generation</span>
                                        <span><i class="fas fa-check-circle text-success"></i> Framework Compliance</span>
                                        <span><i class="fas fa-check-circle text-success"></i> Professional Formatting</span>
                                        <span><i class="fas fa-check-circle text-success"></i> Ready for Review</span>
                                    </div>
                                </div>
                            </div>
                            
                            <!-- Loading State -->
                            <div class="loading-state d-none" id="loadingState">
                                <div class="text-center py-5">
                                    <div class="spinner-border text-primary mb-3" role="status">
                                        <span class="visually-hidden">Generating...</span>
                                    </div>
                                    <h6>AI is generating your policy...</h6>
                                    <p class="text-muted">This may take a few moments</p>
                                </div>
                            </div>
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

.recent-list {
    padding: 4px 0;
}

.recent-item {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 10px 16px;
    border-bottom: 1px solid #F1F5F9;
}

.recent-item:last-child {
    border-bottom: none;
}

.recent-title {
    font-weight: 500;
    color: #1E293B;
    font-size: 14px;
}

.recent-meta {
    font-size: 12px;
    color: #94A3B8;
}

.generated-content {
    min-height: 400px;
}

.placeholder-content {
    text-align: center;
    padding: 60px 20px;
}

.features-list {
    display: flex;
    flex-wrap: wrap;
    gap: 12px;
    justify-content: center;
    margin-top: 16px;
}

.features-list span {
    background: #F8FAFC;
    padding: 4px 12px;
    border-radius: 20px;
    font-size: 13px;
    color: #1E293B;
}

.features-list span i {
    margin-right: 4px;
}

.loading-state {
    min-height: 400px;
    display: flex;
    align-items: center;
    justify-content: center;
}

.generated-policy-content {
    padding: 20px;
    background: #F8FAFC;
    border-radius: 8px;
    line-height: 1.8;
    font-size: 14px;
    max-height: 500px;
    overflow-y: auto;
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

@media (max-width: 768px) {
    .recent-item {
        flex-direction: column;
        gap: 4px;
        align-items: flex-start;
    }
}
</style>

<script>
$(document).ready(function() {
    // Form submission
    $('#generatePolicyForm').on('submit', function(e) {
        e.preventDefault();
        
        const btn = $('#generateBtn');
        const content = $('#generatedContent');
        const loading = $('#loadingState');
        
        // Show loading
        content.addClass('d-none');
        loading.removeClass('d-none');
        btn.html('<i class="fas fa-spinner fa-spin me-2"></i> Generating...');
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
        
        // Simulate AI generation
        setTimeout(function() {
            // Hide loading, show content
            loading.addClass('d-none');
            content.removeClass('d-none');
            btn.html('<i class="fas fa-wand-magic-sparkles me-2"></i> Generate Policy');
            btn.prop('disabled', false);
            
            // Generate sample content
            const policyContent = generateSamplePolicy(formData);
            content.html(`
                <div class="generated-policy-content">
                    ${policyContent}
                </div>
            `);
            
        }, 2500);
    });
    
    function generateSamplePolicy(data) {
        const frameworkLabels = {
            'iso27001': 'ISO 27001:2022',
            'nist': 'NIST CSF',
            'sbp': 'SBP Regulations',
            'basel': 'Basel III',
            'custom': 'Custom Framework'
        };
        
        const policyTypes = {
            'information_security': 'Information Security',
            'data_privacy': 'Data Privacy',
            'risk_management': 'Risk Management',
            'compliance': 'Compliance',
            'governance': 'Corporate Governance'
        };
        
        const policyName = data.policy_name || 'Policy Document';
        const type = policyTypes[data.policy_type] || 'Policy';
        const framework = frameworkLabels[data.framework] || 'Framework';
        
        return `
            <h1>${policyName}</h1>
            
            <h2>1. Purpose</h2>
            <p>This ${type} Policy establishes the framework and requirements for ${type.toLowerCase()} management within the organization. It is designed to ensure compliance with ${framework} and other applicable regulations.</p>
            
            <h2>2. Scope</h2>
            <p>This policy applies to all employees, contractors, and third-party vendors who have access to organizational systems and data.</p>
            
            <h2>3. Policy Statement</h2>
            <p>The organization is committed to maintaining a robust ${type.toLowerCase()} posture that protects assets, ensures business continuity, and complies with regulatory requirements.</p>
            
            <h2>4. Key Requirements</h2>
            <ul>
                <li>All personnel must comply with this policy</li>
                <li>Regular reviews and updates will be conducted</li>
                <li>Violations may result in disciplinary action</li>
                <li>All incidents must be reported immediately</li>
                <li>Training will be provided to all employees</li>
            </ul>
            
            <h2>5. Roles and Responsibilities</h2>
            <ul>
                <li><strong>Policy Owner:</strong> Responsible for policy maintenance and updates</li>
                <li><strong>Compliance Team:</strong> Monitors compliance with policy requirements</li>
                <li><strong>All Employees:</strong> Must adhere to policy requirements</li>
                <li><strong>Management:</strong> Ensures resources are available for policy implementation</li>
            </ul>
            
            <h2>6. Compliance and Enforcement</h2>
            <p>Compliance with this policy is mandatory. Non-compliance will be addressed through the organization's disciplinary process.</p>
            
            <h2>7. Review and Updates</h2>
            <p>This policy will be reviewed annually or when significant changes occur in the regulatory environment.</p>
            
            <h2>8. Related Documents</h2>
            <ul>
                <li>${framework} Compliance Checklist</li>
                <li>Implementation Guidelines</li>
                <li>Training Materials</li>
            </ul>
            
            <hr>
            <p><em>Generated by AI Banking GRC Platform - AI Policy Generator</em></p>
            <p><strong>Policy Number:</strong> POL-${new Date().getFullYear()}-${String(Math.floor(Math.random() * 9999)).padStart(4, '0')}</p>
            <p><strong>Effective Date:</strong> ${new Date().toLocaleDateString()}</p>
        `;
    }
    
    // Copy policy
    $('#copyPolicy').on('click', function() {
        const content = $('#generatedContent .generated-policy-content');
        if (content.length) {
            navigator.clipboard.writeText(content.text()).then(function() {
                showToast('Policy copied to clipboard!', 'success');
            });
        } else {
            showToast('No policy to copy', 'warning');
        }
    });
    
    // Download policy
    $('#downloadPolicy').on('click', function() {
        const content = $('#generatedContent .generated-policy-content');
        if (content.length) {
            const blob = new Blob([content.html()], { type: 'text/html' });
            const url = URL.createObjectURL(blob);
            const a = document.createElement('a');
            a.href = url;
            a.download = 'generated-policy.html';
            document.body.appendChild(a);
            a.click();
            document.body.removeChild(a);
            URL.revokeObjectURL(url);
            showToast('Policy downloaded successfully!', 'success');
        } else {
            showToast('No policy to download', 'warning');
        }
    });
    
    // Save policy
    $('#savePolicy').on('click', function() {
        const content = $('#generatedContent .generated-policy-content');
        if (content.length) {
            showToast('Policy saved to library!', 'success');
        } else {
            showToast('No policy to save', 'warning');
        }
    });
    
    // Toast notification
    function showToast(message, type) {
        // Simple toast implementation
        const toast = $(`
            <div class="toast-notification ${type}">
                <i class="fas fa-${type === 'success' ? 'check-circle' : 'exclamation-circle'}"></i>
                ${message}
            </div>
        `);
        $('body').append(toast);
        setTimeout(function() {
            toast.fadeOut(300, function() { $(this).remove(); });
        }, 3000);
    }
    
    // Toast styles
    const toastStyles = `
    <style>
        .toast-notification {
            position: fixed;
            bottom: 30px;
            right: 30px;
            padding: 12px 24px;
            border-radius: 8px;
            color: #fff;
            font-size: 14px;
            font-weight: 500;
            z-index: 9999;
            animation: slideIn 0.3s ease;
            box-shadow: 0 4px 12px rgba(0,0,0,0.15);
        }
        .toast-notification.success { background: #22C55E; }
        .toast-notification.warning { background: #F59E0B; }
        .toast-notification.error { background: #EF4444; }
        .toast-notification i { margin-right: 8px; }
        @keyframes slideIn {
            from { transform: translateX(100%); opacity: 0; }
            to { transform: translateX(0); opacity: 1; }
        }
    </style>
    `;
    $('head').append(toastStyles);
});
</script>