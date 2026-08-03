/**
 * AI Banking GRC Platform - Users Module JavaScript
 * 
 * This file contains users-specific functionality including:
 * - User management
 * - Role management
 * - Profile management
 * - Password management
 */

'use strict';

// ============================================================
// DOM READY
// ============================================================

document.addEventListener('DOMContentLoaded', function() {
    initUsers();
});

/**
 * Initialize users functionality
 */
function initUsers() {
    // User filters
    initUserFilters();
    
    // Role management
    initRoleManagement();
    
    // Password management
    initPasswordManagement();
}

// ============================================================
// USER FILTERS
// ============================================================

/**
 * Initialize user filters
 */
function initUserFilters() {
    const filters = {
        role: document.getElementById('filterRole'),
        status: document.getElementById('filterStatus'),
        department: document.getElementById('filterDepartment')
    };
    
    Object.values(filters).forEach(filter => {
        if (filter) {
            filter.addEventListener('change', applyUserFilters);
        }
    });
    
    const searchInput = document.getElementById('searchUser');
    if (searchInput) {
        searchInput.addEventListener('keyup', applyUserFilters);
    }
}

/**
 * Apply user filters
 */
function applyUserFilters() {
    const role = document.getElementById('filterRole')?.value;
    const status = document.getElementById('filterStatus')?.value;
    const department = document.getElementById('filterDepartment')?.value;
    const search = document.getElementById('searchUser')?.value.toLowerCase() || '';
    
    const rows = document.querySelectorAll('.users-table tbody tr');
    
    rows.forEach(row => {
        let show = true;
        
        if (role) {
            const rowRole = row.querySelector('.role-badge')?.textContent.trim();
            if (rowRole !== document.getElementById('filterRole')?.options[document.getElementById('filterRole')?.selectedIndex]?.text) {
                show = false;
            }
        }
        
        if (status) {
            const rowStatus = row.querySelector('.user-status-badge')?.textContent.trim().toLowerCase();
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
// ROLE MANAGEMENT
// ============================================================

/**
 * Initialize role management
 */
function initRoleManagement() {
    // Edit role permissions
    document.querySelectorAll('.edit-role-permissions').forEach(btn => {
        btn.addEventListener('click', function() {
            const roleId = this.dataset.id;
            const csrfToken = document.querySelector('meta[name="csrf-token"]')?.content;
            
            fetch(`/api/roles/${roleId}/permissions`)
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        showRolePermissionsModal(data.data);
                    }
                })
                .catch(error => {
                    showToast('Failed to load permissions', 'error');
                });
        });
    });
}

/**
 * Show role permissions modal
 */
function showRolePermissionsModal(data) {
    const modal = document.getElementById('rolePermissionsModal');
    if (!modal) return;
    
    const container = modal.querySelector('.permissions-container');
    if (!container) return;
    
    container.innerHTML = '';
    
    // Group permissions by module
    const grouped = {};
    data.permissions.forEach(perm => {
        const module = perm.module || 'General';
        if (!grouped[module]) grouped[module] = [];
        grouped[module].push(perm);
    });
    
    Object.keys(grouped).forEach(module => {
        const group = document.createElement('div');
        group.className = 'permission-group mb-3';
        group.innerHTML = `
            <h6 class="permission-group-title">${module}</h6>
            <div class="row">
                ${grouped[module].map(perm => `
                    <div class="col-md-6">
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" name="permissions[]" 
                                   value="${perm.id}" ${perm.assigned ? 'checked' : ''}>
                            <label class="form-check-label">${perm.display_name}</label>
                        </div>
                    </div>
                `).join('')}
            </div>
        `;
        container.appendChild(group);
    });
    
    const modalInstance = new bootstrap.Modal(modal);
    modalInstance.show();
}

// ============================================================
// PASSWORD MANAGEMENT
// ============================================================

/**
 * Initialize password management
 */
function initPasswordManagement() {
    const form = document.getElementById('changePasswordForm');
    if (!form) return;
    
    // Password strength
    const newPassword = document.getElementById('new_password');
    if (newPassword) {
        newPassword.addEventListener('keyup', function() {
            const password = this.value;
            const bar = document.querySelector('.password-strength-bar');
            const text = document.querySelector('.password-strength-text');
            
            if (!bar || !text) return;
            
            let strength = 0;
            let message = '';
            let color = '';
            
            if (password.length >= 8) strength += 25;
            if (/[a-z]/.test(password)) strength += 25;
            if (/[A-Z]/.test(password)) strength += 25;
            if (/[0-9!@#$%^&*]/.test(password)) strength += 25;
            
            if (strength <= 25) { message = 'Weak'; color = '#EF4444'; }
            else if (strength <= 50) { message = 'Fair'; color = '#F59E0B'; }
            else if (strength <= 75) { message = 'Good'; color = '#3B82F6'; }
            else { message = 'Strong'; color = '#22C55E'; }
            
            bar.style.width = strength + '%';
            bar.style.background = color;
            text.textContent = message;
            text.style.color = color;
        });
    }
    
    // Password confirmation
    const confirmPassword = document.getElementById('new_password_confirmation');
    if (confirmPassword && newPassword) {
        confirmPassword.addEventListener('keyup', function() {
            if (this.value && this.value !== newPassword.value) {
                this.classList.add('is-invalid');
            } else {
                this.classList.remove('is-invalid');
            }
        });
    }
    
    form.addEventListener('submit', function(e) {
        e.preventDefault();
        
        const formData = getFormData(this);
        const csrfToken = document.querySelector('meta[name="csrf-token"]')?.content;
        
        const btn = form.querySelector('button[type="submit"]');
        const originalText = btn.innerHTML;
        btn.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span> Updating...';
        btn.disabled = true;
        
        fetch('/api/users/password', {
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
                showToast('Password updated successfully', 'success');
                setTimeout(() => window.location.reload(), 1500);
            } else {
                showToast(data.message || 'Update failed', 'error');
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