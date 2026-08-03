/**
 * AI Banking GRC Platform - Form Utilities
 * 
 * This file contains form utility functions
 */

'use strict';

// ============================================================
// FORM HELPERS
// ============================================================

/**
 * Get form data as object
 */
function getFormData(form) {
    const formData = new FormData(form);
    const data = {};
    
    for (const [key, value] of formData.entries()) {
        if (data[key] !== undefined) {
            if (!Array.isArray(data[key])) {
                data[key] = [data[key]];
            }
            data[key].push(value);
        } else {
            data[key] = value;
        }
    }
    
    return data;
}

/**
 * Populate form with data
 */
function populateForm(form, data) {
    for (const [key, value] of Object.entries(data)) {
        const field = form.querySelector(`[name="${key}"]`);
        if (!field) continue;
        
        if (field.type === 'checkbox') {
            field.checked = !!value;
        } else if (field.type === 'radio') {
            const radio = form.querySelector(`[name="${key}"][value="${value}"]`);
            if (radio) radio.checked = true;
        } else if (field.tagName === 'SELECT') {
            field.value = value;
        } else {
            field.value = value;
        }
    }
}

/**
 * Reset form
 */
function resetForm(form) {
    form.reset();
    form.querySelectorAll('.is-valid, .is-invalid').forEach(el => {
        el.classList.remove('is-valid', 'is-invalid');
    });
    form.querySelectorAll('.invalid-feedback, .valid-feedback').forEach(el => {
        el.remove();
    });
}

/**
 * Disable form
 */
function disableForm(form, disabled = true) {
    form.querySelectorAll('input, select, textarea, button').forEach(el => {
        el.disabled = disabled;
    });
}

/**
 * Show field error
 */
function showFieldError(field, message) {
    const formGroup = field.closest('.form-group') || field.closest('.mb-3');
    if (!formGroup) return;
    
    field.classList.add('is-invalid');
    
    let errorDiv = formGroup.querySelector('.invalid-feedback');
    if (!errorDiv) {
        errorDiv = document.createElement('div');
        errorDiv.className = 'invalid-feedback';
        formGroup.appendChild(errorDiv);
    }
    errorDiv.textContent = message;
}

/**
 * Clear field error
 */
function clearFieldError(field) {
    field.classList.remove('is-invalid');
    const formGroup = field.closest('.form-group') || field.closest('.mb-3');
    if (formGroup) {
        const errorDiv = formGroup.querySelector('.invalid-feedback');
        if (errorDiv) {
            errorDiv.remove();
        }
    }
}

/**
 * Show field success
 */
function showFieldSuccess(field) {
    field.classList.remove('is-invalid');
    field.classList.add('is-valid');
}

/**
 * Show toast message
 */
function showToast(message, type = 'info', duration = 3000) {
    const toast = document.createElement('div');
    toast.className = `toast-notification ${type}`;
    const icons = {
        success: 'fa-check-circle',
        error: 'fa-exclamation-circle',
        warning: 'fa-exclamation-triangle',
        info: 'fa-info-circle'
    };
    
    toast.innerHTML = `
        <i class="fas ${icons[type] || icons.info}"></i>
        ${message}
    `;
    
    document.body.appendChild(toast);
    
    setTimeout(() => {
        toast.classList.add('fade-out');
        setTimeout(() => {
            toast.remove();
        }, 300);
    }, duration);
}

// ============================================================
// EXPOSE FUNCTIONS GLOBALLY
// ============================================================

window.getFormData = getFormData;
window.populateForm = populateForm;
window.resetForm = resetForm;
window.disableForm = disableForm;
window.showFieldError = showFieldError;
window.clearFieldError = clearFieldError;
window.showFieldSuccess = showFieldSuccess;
window.showToast = showToast;