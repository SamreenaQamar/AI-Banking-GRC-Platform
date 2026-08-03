/**
 * AI Banking GRC Platform - Settings Module JavaScript
 * 
 * This file contains settings-specific functionality including:
 * - General settings
 * - Company settings
 * - Security settings
 * - API settings
 * - Backup settings
 */

'use strict';

// ============================================================
// DOM READY
// ============================================================

document.addEventListener('DOMContentLoaded', function() {
    initSettings();
});

/**
 * Initialize settings functionality
 */
function initSettings() {
    // Logo upload
    initLogoUpload();
    
    // Backup management
    initBackupManagement();
    
    // API key management
    initApiKeyManagement();
    
    // Security settings
    initSecuritySettings();
}

// ============================================================
// LOGO UPLOAD
// ============================================================

/**
 * Initialize logo upload
 */
function initLogoUpload() {
    const uploadArea = document.getElementById('logoUpload');
    const fileInput = document.getElementById('logoFile');
    
    if (!uploadArea || !fileInput) return;
    
    // Click to upload
    uploadArea.addEventListener('click', () => fileInput.click());
    
    // Drag and drop
    uploadArea.addEventListener('dragover', function(e) {
        e.preventDefault();
        this.classList.add('dragover');
    });
    
    uploadArea.addEventListener('dragleave', function(e) {
        e.preventDefault();
        this.classList.remove('dragover');
    });
    
    uploadArea.addEventListener('drop', function(e) {
        e.preventDefault();
        this.classList.remove('dragover');
        
        const files = e.dataTransfer.files;
        if (files.length) {
            handleLogoUpload(files[0]);
        }
    });
    
    fileInput.addEventListener('change', function() {
        if (this.files.length) {
            handleLogoUpload(this.files[0]);
        }
    });
}

/**
 * Handle logo upload
 */
function handleLogoUpload(file) {
    const maxSize = 2 * 1024 * 1024; // 2MB
    const allowedTypes = ['image/jpeg', 'image/png', 'image/gif', 'image/svg+xml'];
    
    if (file.size > maxSize) {
        showToast('Logo size must be less than 2MB', 'error');
        return;
    }
    
    if (!allowedTypes.includes(file.type)) {
        showToast('Please upload JPEG, PNG, GIF, or SVG file', 'error');
        return;
    }
    
    const formData = new FormData();
    formData.append('logo', file);
    formData.append('csrf_token', document.querySelector('meta[name="csrf-token"]')?.content || '');
    
    const csrfToken = document.querySelector('meta[name="csrf-token"]')?.content;
    
    showToast('Uploading logo...', 'info');
    
    fetch('/api/settings/company/logo', {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            showToast('Logo uploaded successfully', 'success');
            // Update logo preview
            const preview = document.querySelector('.logo-preview');
            if (preview) {
                preview.src = data.url + '?' + Date.now();
            }
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
// BACKUP MANAGEMENT
// ============================================================

/**
 * Initialize backup management
 */
function initBackupManagement() {
    // Create backup
    const createBtn = document.getElementById('createBackupBtn');
    if (createBtn) {
        createBtn.addEventListener('click', function() {
            const csrfToken = document.querySelector('meta[name="csrf-token"]')?.content;
            
            if (!confirm('Create a new backup?')) return;
            
            const originalText = this.innerHTML;
            this.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span> Creating...';
            this.disabled = true;
            
            fetch('/api/settings/backup/create', {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': csrfToken || ''
                }
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    showToast('Backup created successfully', 'success');
                    location.reload();
                } else {
                    showToast(data.message || 'Backup failed', 'error');
                }
            })
            .catch(error => {
                showToast('An error occurred', 'error');
            })
            .finally(() => {
                this.innerHTML = originalText;
                this.disabled = false;
            });
        });
    }
    
    // Restore backup
    document.querySelectorAll('.restore-backup').forEach(btn => {
        btn.addEventListener('click', function() {
            const filename = this.dataset.filename;
            const csrfToken = document.querySelector('meta[name="csrf-token"]')?.content;
            
            if (!confirm(`Restore backup ${filename}? This will overwrite current settings.`)) return;
            
            const originalText = this.innerHTML;
            this.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span> Restoring...';
            this.disabled = true;
            
            fetch(`/api/settings/backup/restore/${filename}`, {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': csrfToken || ''
                }
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    showToast('Backup restored successfully', 'success');
                    location.reload();
                } else {
                    showToast(data.message || 'Restore failed', 'error');
                }
            })
            .catch(error => {
                showToast('An error occurred', 'error');
            })
            .finally(() => {
                this.innerHTML = originalText;
                this.disabled = false;
            });
        });
    });
    
    // Delete backup
    document.querySelectorAll('.delete-backup').forEach(btn => {
        btn.addEventListener('click', function() {
            const filename = this.dataset.filename;
            const csrfToken = document.querySelector('meta[name="csrf-token"]')?.content;
            
            if (!confirm(`Delete backup ${filename}?`)) return;
            
            fetch(`/api/settings/backup/${filename}`, {
                method: 'DELETE',
                headers: {
                    'X-CSRF-TOKEN': csrfToken || ''
                }
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    showToast('Backup deleted', 'success');
                    location.reload();
                } else {
                    showToast(data.message || 'Delete failed', 'error');
                }
            })
            .catch(error => {
                showToast('An error occurred', 'error');
            });
        });
    });
    
    // Cleanup old backups
    const cleanupBtn = document.getElementById('cleanupBackupsBtn');
    if (cleanupBtn) {
        cleanupBtn.addEventListener('click', function() {
            const csrfToken = document.querySelector('meta[name="csrf-token"]')?.content;
            
            if (!confirm('Delete all backups older than 30 days?')) return;
            
            const originalText = this.innerHTML;
            this.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span> Cleaning...';
            this.disabled = true;
            
            fetch('/api/settings/backup/cleanup', {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': csrfToken || ''
                },
                body: JSON.stringify({ days: 30 })
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    showToast('Old backups cleaned up', 'success');
                    location.reload();
                } else {
                    showToast(data.message || 'Cleanup failed', 'error');
                }
            })
            .catch(error => {
                showToast('An error occurred', 'error');
            })
            .finally(() => {
                this.innerHTML = originalText;
                this.disabled = false;
            });
        });
    }
}

// ============================================================
// API KEY MANAGEMENT
// ============================================================

/**
 * Initialize API key management
 */
function initApiKeyManagement() {
    // Generate API key
    const form = document.getElementById('generateApiKeyForm');
    if (form) {
        form.addEventListener('submit', function(e) {
            e.preventDefault();
            
            const formData = getFormData(this);
            const csrfToken = document.querySelector('meta[name="csrf-token"]')?.content;
            
            const btn = form.querySelector('button[type="submit"]');
            const originalText = btn.innerHTML;
            btn.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span> Generating...';
            btn.disabled = true;
            
            fetch('/api/settings/api/generate', {
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
                    showApiKeyModal(data.data);
                    showToast('API key generated', 'success');
                    location.reload();
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
    
    // Revoke API key
    document.querySelectorAll('.revoke-api-key').forEach(btn => {
        btn.addEventListener('click', function() {
            const id = this.dataset.id;
            const csrfToken = document.querySelector('meta[name="csrf-token"]')?.content;
            
            if (!confirm('Revoke this API key?')) return;
            
            fetch(`/api/settings/api/${id}/revoke`, {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': csrfToken || ''
                }
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    showToast('API key revoked', 'success');
                    location.reload();
                } else {
                    showToast(data.message || 'Revoke failed', 'error');
                }
            })
            .catch(error => {
                showToast('An error occurred', 'error');
            });
        });
    });
}

/**
 * Show API key modal
 */
function showApiKeyModal(data) {
    const modal = document.getElementById('apiKeyModal');
    if (!modal) return;
    
    modal.querySelector('.api-key-display').textContent = data.key;
    modal.querySelector('.copy-api-key').addEventListener('click', function() {
        navigator.clipboard.writeText(data.key)
            .then(() => showToast('API key copied', 'success'))
            .catch(() => showToast('Failed to copy', 'error'));
    });
    
    const modalInstance = new bootstrap.Modal(modal);
    modalInstance.show();
}

// ============================================================
// SECURITY SETTINGS
// ============================================================

/**
 * Initialize security settings
 */
function initSecuritySettings() {
    // Two-factor authentication
    const enable2faBtn = document.getElementById('enable2faBtn');
    const disable2faBtn = document.getElementById('disable2faBtn');
    
    if (enable2faBtn) {
        enable2faBtn.addEventListener('click', function() {
            const csrfToken = document.querySelector('meta[name="csrf-token"]')?.content;
            
            fetch('/api/settings/security/2fa/enable', {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': csrfToken || ''
                }
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    show2faSetupModal(data.secret, data.qr);
                } else {
                    showToast(data.message || 'Failed to enable 2FA', 'error');
                }
            })
            .catch(error => {
                showToast('An error occurred', 'error');
            });
        });
    }
    
    if (disable2faBtn) {
        disable2faBtn.addEventListener('click', function() {
            const csrfToken = document.querySelector('meta[name="csrf-token"]')?.content;
            
            if (!confirm('Disable two-factor authentication?')) return;
            
            fetch('/api/settings/security/2fa/disable', {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': csrfToken || ''
                }
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    showToast('2FA disabled', 'success');
                    location.reload();
                } else {
                    showToast(data.message || 'Failed to disable 2FA', 'error');
                }
            })
            .catch(error => {
                showToast('An error occurred', 'error');
            });
        });
    }
    
    // Session management
    document.querySelectorAll('.terminate-session').forEach(btn => {
        btn.addEventListener('click', function() {
            const id = this.dataset.id;
            const csrfToken = document.querySelector('meta[name="csrf-token"]')?.content;
            
            if (!confirm('Terminate this session?')) return;
            
            fetch(`/api/settings/security/session/${id}/terminate`, {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': csrfToken || ''
                }
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    showToast('Session terminated', 'success');
                    location.reload();
                } else {
                    showToast(data.message || 'Failed to terminate session', 'error');
                }
            })
            .catch(error => {
                showToast('An error occurred', 'error');
            });
        });
    });
}

/**
 * Show 2FA setup modal
 */
function show2faSetupModal(secret, qrCode) {
    const modal = document.getElementById('setup2faModal');
    if (!modal) return;
    
    modal.querySelector('.secret-display').textContent = secret;
    modal.querySelector('.qr-display').innerHTML = `<img src="${qrCode}" alt="QR Code">`;
    
    const verifyBtn = modal.querySelector('.verify-2fa');
    verifyBtn.addEventListener('click', function() {
        const code = modal.querySelector('.verification-code').value;
        const csrfToken = document.querySelector('meta[name="csrf-token"]')?.content;
        
        if (code.length !== 6) {
            showToast('Please enter a valid 6-digit code', 'warning');
            return;
        }
        
        fetch('/api/settings/security/2fa/verify', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': csrfToken || ''
            },
            body: JSON.stringify({ code: code })
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                showToast('2FA enabled successfully', 'success');
                const modalInstance = bootstrap.Modal.getInstance(modal);
                modalInstance.hide();
                location.reload();
            } else {
                showToast(data.message || 'Invalid verification code', 'error');
            }
        })
        .catch(error => {
            showToast('An error occurred', 'error');
        });
    });
    
    const modalInstance = new bootstrap.Modal(modal);
    modalInstance.show();
}