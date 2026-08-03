/**
 * AI Banking GRC Platform - Audit Module JavaScript
 * 
 * This file contains audit-specific functionality including:
 * - Audit management
 * - Finding tracking
 * - Evidence upload
 * - Audit checklist
 * - Audit history
 */

'use strict';

// ============================================================
// DOM READY
// ============================================================

document.addEventListener('DOMContentLoaded', function() {
    initAudit();
});

/**
 * Initialize audit functionality
 */
function initAudit() {
    // Audit filters
    initAuditFilters();
    
    // Finding management
    initFindingManagement();
    
    // Evidence upload
    initEvidenceUpload();
    
    // Audit checklist
    initAuditChecklist();
}

// ============================================================
// AUDIT FILTERS
// ============================================================

/**
 * Initialize audit filters
 */
function initAuditFilters() {
    const filters = {
        severity: document.getElementById('filterSeverity'),
        status: document.getElementById('filterStatus'),
        audit: document.getElementById('filterAudit')
    };
    
    Object.values(filters).forEach(filter => {
        if (filter) {
            filter.addEventListener('change', applyAuditFilters);
        }
    });
}

/**
 * Apply audit filters
 */
function applyAuditFilters() {
    const severity = document.getElementById('filterSeverity')?.value;
    const status = document.getElementById('filterStatus')?.value;
    const audit = document.getElementById('filterAudit')?.value;
    
    const rows = document.querySelectorAll('.findings-table tbody tr');
    
    rows.forEach(row => {
        let show = true;
        
        if (severity) {
            const rowSeverity = row.querySelector('.severity-badge')?.textContent.toLowerCase().trim();
            if (rowSeverity !== severity) show = false;
        }
        
        if (status) {
            const rowStatus = row.querySelector('.status-badge')?.textContent.toLowerCase().trim();
            if (rowStatus !== status) show = false;
        }
        
        row.style.display = show ? '' : 'none';
    });
}

// ============================================================
// FINDING MANAGEMENT
// ============================================================

/**
 * Initialize finding management
 */
function initFindingManagement() {
    // Update finding status
    document.querySelectorAll('.update-finding-status').forEach(btn => {
        btn.addEventListener('click', function() {
            const id = this.dataset.id;
            const csrfToken = document.querySelector('meta[name="csrf-token"]')?.content;
            
            // Show status options
            const statusOptions = ['Open', 'In Progress', 'Resolved', 'Closed'];
            const currentStatus = this.closest('tr').querySelector('.status-badge').textContent.trim();
            
            const select = document.createElement('select');
            select.className = 'form-select form-select-sm';
            statusOptions.forEach(status => {
                const option = document.createElement('option');
                option.value = status.toLowerCase().replace(' ', '_');
                option.textContent = status;
                if (status === currentStatus) option.selected = true;
                select.appendChild(option);
            });
            
            const container = this.parentNode;
            const originalContent = container.innerHTML;
            container.innerHTML = '';
            container.appendChild(select);
            
            select.addEventListener('change', function() {
                const newStatus = this.value;
                
                fetch(`/api/audit/findings/${id}/status`, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': csrfToken || ''
                    },
                    body: JSON.stringify({ status: newStatus })
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        showToast('Status updated', 'success');
                        location.reload();
                    } else {
                        container.innerHTML = originalContent;
                        showToast('Failed to update status', 'error');
                    }
                })
                .catch(error => {
                    container.innerHTML = originalContent;
                    showToast('An error occurred', 'error');
                });
            });
        });
    });
}

// ============================================================
// EVIDENCE UPLOAD
// ============================================================

/**
 * Initialize evidence upload
 */
function initEvidenceUpload() {
    const dropZone = document.getElementById('evidenceDropZone');
    const fileInput = document.getElementById('evidenceFile');
    
    if (!dropZone || !fileInput) return;
    
    // Click to upload
    dropZone.addEventListener('click', () => fileInput.click());
    
    // Drag and drop
    dropZone.addEventListener('dragover', function(e) {
        e.preventDefault();
        this.classList.add('dragover');
    });
    
    dropZone.addEventListener('dragleave', function(e) {
        e.preventDefault();
        this.classList.remove('dragover');
    });
    
    dropZone.addEventListener('drop', function(e) {
        e.preventDefault();
        this.classList.remove('dragover');
        
        const files = e.dataTransfer.files;
        if (files.length) {
            handleEvidenceFiles(files);
        }
    });
    
    // File input change
    fileInput.addEventListener('change', function() {
        if (this.files.length) {
            handleEvidenceFiles(this.files);
        }
    });
}

/**
 * Handle evidence files
 */
function handleEvidenceFiles(files) {
    const file = files[0];
    const maxSize = 10 * 1024 * 1024; // 10MB
    const allowedTypes = ['application/pdf', 'image/jpeg', 'image/png', 'application/msword', 'application/vnd.openxmlformats-officedocument.wordprocessingml.document'];
    
    // Validate
    if (file.size > maxSize) {
        showToast('File size exceeds 10MB limit', 'error');
        return;
    }
    
    if (!allowedTypes.includes(file.type)) {
        showToast('File type not supported. Please upload PDF, DOC, DOCX, JPG, or PNG.', 'error');
        return;
    }
    
    // Upload
    const formData = new FormData();
    formData.append('evidence', file);
    formData.append('description', document.getElementById('evidenceDescription')?.value || '');
    formData.append('csrf_token', document.querySelector('meta[name="csrf-token"]')?.content || '');
    
    const csrfToken = document.querySelector('meta[name="csrf-token"]')?.content;
    const auditId = document.querySelector('[data-audit-id]')?.dataset.auditId;
    
    showToast('Uploading evidence...', 'info');
    
    fetch(`/api/audit/${auditId}/evidence`, {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            showToast('Evidence uploaded successfully', 'success');
            location.reload();
        } else {
            showToast(data.message || 'Upload failed', 'error');
        }
    })
    .catch(error => {
        showToast('An error occurred', 'error');
    });
}

// ============================================================
// AUDIT CHECKLIST
// ============================================================

/**
 * Initialize audit checklist
 */
function initAuditChecklist() {
    const checkboxes = document.querySelectorAll('.audit-checklist-checkbox');
    if (!checkboxes.length) return;
    
    checkboxes.forEach(checkbox => {
        checkbox.addEventListener('change', function() {
            const id = this.dataset.id;
            const checked = this.checked;
            const item = this.closest('.audit-checklist-item');
            const csrfToken = document.querySelector('meta[name="csrf-token"]')?.content;
            
            // Update UI
            item.classList.toggle('completed', checked);
            
            fetch(`/api/audit/checklist/${id}`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': csrfToken || ''
                },
                body: JSON.stringify({ status: checked ? 'completed' : 'pending' })
            })
            .then(response => response.json())
            .then(data => {
                if (!data.success) {
                    // Revert on error
                    item.classList.toggle('completed', !checked);
                    checkbox.checked = !checked;
                    showToast('Failed to update checklist', 'error');
                } else {
                    updateAuditProgress();
                }
            })
            .catch(error => {
                showToast('An error occurred', 'error');
                item.classList.toggle('completed', !checked);
                checkbox.checked = !checked;
            });
        });
    });
}

/**
 * Update audit progress
 */
function updateAuditProgress() {
    const total = document.querySelectorAll('.audit-checklist-item').length;
    const completed = document.querySelectorAll('.audit-checklist-item.completed').length;
    const progress = total > 0 ? Math.round((completed / total) * 100) : 0;
    
    const progressBar = document.querySelector('.audit-progress-bar');
    if (progressBar) {
        progressBar.style.width = progress + '%';
        progressBar.textContent = progress + '%';
    }
}