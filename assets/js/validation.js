/**
 * AI Banking GRC Platform - Form Validation
 * 
 * This file contains form validation functions
 */

'use strict';

// ============================================================
// VALIDATION RULES
// ============================================================

const VALIDATION_RULES = {
    required: {
        validate: (value) => {
            if (Array.isArray(value)) return value.length > 0;
            if (typeof value === 'string') return value.trim().length > 0;
            return value !== null && value !== undefined;
        },
        message: 'This field is required'
    },
    email: {
        validate: (value) => {
            const emailRegex = /^[a-zA-Z0-9._%+-]+@[a-zA-Z0-9.-]+\.[a-zA-Z]{2,}$/;
            return emailRegex.test(value);
        },
        message: 'Please enter a valid email address'
    },
    min: {
        validate: (value, params) => {
            if (typeof value === 'string') return value.length >= parseInt(params[0]);
            if (typeof value === 'number') return value >= parseInt(params[0]);
            return true;
        },
        message: 'Minimum length is {0} characters'
    },
    max: {
        validate: (value, params) => {
            if (typeof value === 'string') return value.length <= parseInt(params[0]);
            if (typeof value === 'number') return value <= parseInt(params[0]);
            return true;
        },
        message: 'Maximum length is {0} characters'
    },
    minValue: {
        validate: (value, params) => {
            return parseFloat(value) >= parseFloat(params[0]);
        },
        message: 'Value must be at least {0}'
    },
    maxValue: {
        validate: (value, params) => {
            return parseFloat(value) <= parseFloat(params[0]);
        },
        message: 'Value must not exceed {0}'
    },
    numeric: {
        validate: (value) => {
            return !isNaN(parseFloat(value)) && isFinite(value);
        },
        message: 'Please enter a valid number'
    },
    alpha: {
        validate: (value) => {
            return /^[a-zA-Z]+$/.test(value);
        },
        message: 'Please enter only letters'
    },
    alphanumeric: {
        validate: (value) => {
            return /^[a-zA-Z0-9]+$/.test(value);
        },
        message: 'Please enter only letters and numbers'
    },
    phone: {
        validate: (value) => {
            const phoneRegex = /^(\+92|0)[0-9]{10,12}$/;
            return phoneRegex.test(value);
        },
        message: 'Please enter a valid phone number'
    },
    url: {
        validate: (value) => {
            try {
                new URL(value);
                return true;
            } catch {
                return false;
            }
        },
        message: 'Please enter a valid URL'
    },
    confirmed: {
        validate: (value, params, form) => {
            const fieldName = params[0];
            const confirmValue = form.querySelector(`[name="${fieldName}"]`)?.value;
            return value === confirmValue;
        },
        message: 'Fields do not match'
    },
    password: {
        validate: (value) => {
            const hasMinLength = value.length >= 8;
            const hasUppercase = /[A-Z]/.test(value);
            const hasLowercase = /[a-z]/.test(value);
            const hasNumber = /[0-9]/.test(value);
            const hasSpecial = /[!@#$%^&*(),.?":{}|<>]/.test(value);
            return hasMinLength && hasUppercase && hasLowercase && hasNumber && hasSpecial;
        },
        message: 'Password must be at least 8 characters with uppercase, lowercase, number, and special character'
    },
    cnic: {
        validate: (value) => {
            const cnicRegex = /^[0-9]{5}-[0-9]{7}-[0-9]$/;
            return cnicRegex.test(value);
        },
        message: 'Please enter a valid CNIC (xxxxx-xxxxxxx-x)'
    }
};

// ============================================================
// VALIDATOR CLASS
// ============================================================

class Validator {
    constructor(formElement) {
        this.form = formElement;
        this.rules = {};
        this.errors = {};
        this.fields = new Map();
    }

    /**
     * Add validation rule for a field
     */
    addRule(fieldName, ruleName, params = []) {
        if (!this.fields.has(fieldName)) {
            this.fields.set(fieldName, []);
        }
        this.fields.get(fieldName).push({
            rule: ruleName,
            params: params
        });
        return this;
    }

    /**
     * Add multiple validation rules for a field
     */
    addRules(fieldName, rules) {
        if (!Array.isArray(rules)) {
            rules = [rules];
        }
        rules.forEach(rule => {
            if (typeof rule === 'string') {
                this.addRule(fieldName, rule, []);
            } else if (Array.isArray(rule)) {
                this.addRule(fieldName, rule[0], rule.slice(1));
            }
        });
        return this;
    }

    /**
     * Validate the form
     */
    validate() {
        this.errors = {};
        let isValid = true;

        this.fields.forEach((rules, fieldName) => {
            const field = this.form.querySelector(`[name="${fieldName}"]`);
            if (!field) return;

            const value = field.value;
            const error = this.validateField(fieldName, value, rules);

            if (error) {
                this.errors[fieldName] = error;
                isValid = false;
                this.showFieldError(field, error);
            } else {
                this.clearFieldError(field);
            }
        });

        return isValid;
    }

    /**
     * Validate a single field
     */
    validateField(fieldName, value, rules) {
        for (const ruleData of rules) {
            const ruleName = ruleData.rule;
            const params = ruleData.params;
            const rule = VALIDATION_RULES[ruleName];

            if (!rule) continue;

            const isValid = rule.validate(value, params, this.form);
            if (!isValid) {
                let message = rule.message;
                message = message.replace(/\{(\d+)\}/g, (match, index) => {
                    return params[parseInt(index)] || match;
                });
                return message;
            }
        }
        return null;
    }

    /**
     * Show field error
     */
    showFieldError(field, message) {
        const formGroup = field.closest('.form-group') || field.closest('.mb-3');
        if (!formGroup) return;

        // Remove existing error
        this.clearFieldError(field);

        // Add error class
        field.classList.add('is-invalid');

        // Create error message
        const errorDiv = document.createElement('div');
        errorDiv.className = 'invalid-feedback';
        errorDiv.textContent = message;
        formGroup.appendChild(errorDiv);
    }

    /**
     * Clear field error
     */
    clearFieldError(field) {
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
     * Get all errors
     */
    getErrors() {
        return this.errors;
    }

    /**
     * Check if form has errors
     */
    hasErrors() {
        return Object.keys(this.errors).length > 0;
    }
}

// ============================================================
// FORM HELPERS
// ============================================================

/**
 * Validate form
 */
function validateForm(formId) {
    const form = document.getElementById(formId);
    if (!form) return false;

    const validator = new Validator(form);
    const inputs = form.querySelectorAll('[data-validate]');

    inputs.forEach(input => {
        const rules = input.dataset.validate.split('|');
        const fieldName = input.name;
        rules.forEach(rule => {
            const parts = rule.split(':');
            const ruleName = parts[0];
            const params = parts.slice(1);
            validator.addRule(fieldName, ruleName, params);
        });
    });

    return validator.validate();
}

/**
 * Initialize form validation
 */
function initFormValidation() {
    document.querySelectorAll('[data-validate-form]').forEach(form => {
        form.addEventListener('submit', function(e) {
            if (!validateForm(this.id)) {
                e.preventDefault();
            }
        });

        // Real-time validation
        form.querySelectorAll('[data-validate]').forEach(input => {
            input.addEventListener('blur', function() {
                const formId = this.closest('form').id;
                validateForm(formId);
            });

            input.addEventListener('input', function() {
                // Clear error on input
                const formGroup = this.closest('.form-group') || this.closest('.mb-3');
                if (formGroup) {
                    const errorDiv = formGroup.querySelector('.invalid-feedback');
                    if (errorDiv) {
                        errorDiv.remove();
                    }
                    this.classList.remove('is-invalid');
                }
            });
        });
    });
}

// ============================================================
// INITIALIZE ON DOM READY
// ============================================================

document.addEventListener('DOMContentLoaded', function() {
    initFormValidation();
});

// ============================================================
// EXPOSE CLASS
// ============================================================

window.Validator = Validator;
window.validateForm = validateForm;