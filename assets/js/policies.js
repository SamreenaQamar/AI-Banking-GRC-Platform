/**
 * AI Banking GRC Platform - Policies Module JavaScript
 * 
 * This file contains policies-specific functionality including:
 * - Policy management
 * - Policy generator
 * - Policy approval workflow
 * - Policy acknowledgment
 */

'use strict';

// ============================================================
// DOM READY
// ============================================================

document.addEventListener('DOMContentLoaded', function() {
    initPolicies();
});

/**
 * Initialize policies functionality
 */
function initPolicies() {
    // Policy filters
    initPolicyFilters();
    
    // Policy generator
    initPolicyGenerator();
    
    // Policy approval
    initPolicyApproval();
    
    // Policy acknowledgment
    initPolicyAcknowledgment();
}

// ============================================================
// POLICY FILTERS
// ============================================================

/**
 * Initialize policy filters
 */
function initPolicyFilters() {
    const filters = {
        category: document.getElementById('filterCategory'),
        status: document.getElementById('filterStatus'),
        department: document.getElementById('filterDepartment')
    };
    
    Object.values(filters).forEach(filter => {
        if (filter) {
            filter.addEventListener('change', applyPolicyFilters);
        }
    });
    
    const searchInput = document.getElementById('searchPolicy');
    if (searchInput) {
        searchInput.addEventListener('keyup', applyPolicyFilters);
    }
}

/**
 * Apply policy filters
 */
function applyPolicyFilters() {
    const category = document.getElementById('filterCategory')?.value;
    const status = document.getElementById('filterStatus')?.value;
    const department = document.getElementById('filterDepartment')?.value;
    const search = document.getElementById('searchPolicy')?.value.toLowerCase() || '';
    
    const rows = document.querySelectorAll('.policies-table tbody tr');
    
    rows.forEach(row => {
        let show = true;
        
        if (category) {
            const rowCategory = row.querySelector('.policy-category')?.textContent.trim().toLowerCase();
            if (rowCategory !== category) show = false;
        }
        
        if (status) {
            const rowStatus = row.querySelector('.status-badge')?.textContent.trim().toLowerCase();
            if (rowStatus !== status) show = false;
        }
        
        if (search) {
            const text = row.textContent.toLowerCase();
            if (!text.includes(search)) show = false;
        }
        
        row.style.display = show ? '' : 'none';
    });
}

// ============================================================
// POLICY GENERATOR
// ============================================================

/**
 * Initialize policy generator
 */
function initPolicyGenerator() {
    const form = document.getElementById('policyGeneratorForm');
    if (!form) return;
    
    form.addEventListener('submit', function(e) {
        e.preventDefault();
        
        const formData = getFormData(this);
        const csrfToken = document.querySelector('meta[name="csrf-token"]')?.content;
        
        const btn = form.querySelector('button[type="submit"]');
        const originalText = btn.innerHTML;
        btn.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span> Generating...';
        btn.disabled = true;
        
        const output = document.getElementById('generatedPolicy');
        if (output) {
            output.innerHTML = '';
        }
        
        fetch('/api/policies/generate-ai', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': csrfToken || ''
            },
            body: JSON.stringify(formData)
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                displayGeneratedPolicy(data.data);
                showToast('Policy generated successfully', 'success');
            } else {
                showToast(data.message || 'Generation failed', 'error');
            }
        })
        .catch(error => {
            showToast('An error occurred', 'error');
        })
        .finally(() => {
            btn.innerHTML = originalText;
            btn.disabled = false;
        });
    });
}

/**
 * Display generated policy
 */
function displayGeneratedPolicy(data) {
    const output = document.getElementById('generatedPolicy');
    if (!output) return;
    
    output.innerHTML = `
        <div class="generated-policy">
            <div class="d-flex justify-content-between align-items-start mb-3">
                <h5>${data.title || 'Generated Policy'}</h5>
                <div>
                    <button class="btn btn-sm btn-outline-primary" onclick="copyPolicyContent()">
                        <i class="fas fa-copy"></i> Copy
                    </button>
                    <button class="btn btn-sm btn-outline-success" onclick="downloadPolicyContent()">
                        <i class="fas fa-download"></i> Download
                    </button>
                </div>
            </div>
            <div class="policy-content">
                ${data.content || ''}
            </div>
            ${data.suggestions ? `
                <div class="suggestions mt-3 p-3 bg-light rounded">
                    <h6>Suggestions</h6>
                    <ul>
                        ${data.suggestions.map(s => `<li>${s}</li>`).join('')}
                    </ul>
                </div>
            ` : ''}
        </div>
    `;
}

/**
 * Copy policy content
 */
function copyPolicyContent() {
    const content = document.querySelector('#generatedPolicy .policy-content');
    if (!content) return;
    
    navigator.clipboard.writeText(content.textContent)
        .then(() => showToast('Policy copied to clipboard', 'success'))
        .catch(() => showToast('Failed to copy', 'error'));
}

/**
 * Download policy content
 */
function downloadPolicyContent() {
    const content = document.querySelector('#generatedPolicy .policy-content');
    if (!content) return;
    
    const blob = new Blob([content.innerHTML], { type: 'text/html' });
    const url = URL.createObjectURL(blob);
    const a = document.createElement('a');
    a.href = url;
    a.download = 'generated-policy.html';
    document.body.appendChild(a);
    a.click();
    document.body.removeChild(a);
    URL.revokeObjectURL(url);
}

// ============================================================
// POLICY APPROVAL
// ============================================================

/**
 * Initialize policy approval
 */
function initPolicyApproval() {
    document.querySelectorAll('.approve-policy').forEach(btn => {
        btn.addEventListener('click', function() {
            const id = this.dataset.id;
            const csrfToken = document.querySelector('meta[name="csrf-token"]')?.content;
            
            if (!confirm('Approve this policy?')) return;
            
            fetch(`/api/policies/${id}/approve`, {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': csrfToken || ''
                }
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    showToast('Policy approved', 'success');
                    location.reload();
                } else {
                    showToast(data.message || 'Approval failed', 'error');
                }
            })
            .catch(error => {
                showToast('An error occurred', 'error');
            });
        });
    });
    
    document.querySelectorAll('.publish-policy').forEach(btn => {
        btn.addEventListener('click', function() {
            const id = this.dataset.id;
            const csrfToken = document.querySelector('meta[name="csrf-token"]')?.content;
            
            if (!confirm('Publish this policy?')) return;
            
            fetch(`/api/policies/${id}/publish`, {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': csrfToken || ''
                }
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    showToast('Policy published', 'success');
                    location.reload();
                } else {
                    showToast(data.message || 'Publication failed', 'error');
                }
            })
            .catch(error => {
                showToast('An error occurred', 'error');
            });
        });
    });
}

// ============================================================
// POLICY ACKNOWLEDGMENT
// ============================================================

/**
 * Initialize policy acknowledgment
 */
function initPolicyAcknowledgment() {
    document.querySelectorAll('.acknowledge-policy').forEach(btn => {
        btn.addEventListener('click', function() {
            const id = this.dataset.id;
            const csrfToken = document.querySelector('meta[name="csrf-token"]')?.content;
            
            if (!confirm('Acknowledge this policy?')) return;
            
            fetch(`/api/policies/${id}/acknowledge`, {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': csrfToken || ''
                }
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    showToast('Policy acknowledged', 'success');
                    this.disabled = true;
                    this.textContent = '✓ Acknowledged';
                    this.className = 'btn btn-sm btn-success';
                } else {
                    showToast(data.message || 'Acknowledgment failed', 'error');
                }
            })
            .catch(error => {
                showToast('An error occurred', 'error');
            });
        });
    });
}