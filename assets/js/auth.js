/**
 * AI Banking GRC Platform - Authentication JavaScript
 * 
 * This file contains authentication-specific functionality including:
 * - Login form handling
 * - Registration form handling
 * - Password reset
 * - Two-factor authentication
 * - Password strength validation
 */

'use strict';

// ============================================================
// DOM READY
// ============================================================

document.addEventListener('DOMContentLoaded', function() {
    initAuth();
});

/**
 * Initialize authentication functionality
 */
function initAuth() {
    // Login form
    const loginForm = document.getElementById('loginForm');
    if (loginForm) {
        initLoginForm(loginForm);
    }
    
    // Registration form
    const registerForm = document.getElementById('registerForm');
    if (registerForm) {
        initRegisterForm(registerForm);
    }
    
    // Password reset form
    const resetForm = document.getElementById('resetForm');
    if (resetForm) {
        initResetForm(resetForm);
    }
    
    // Two-factor authentication
    const twoFactorForm = document.getElementById('twoFactorForm');
    if (twoFactorForm) {
        initTwoFactorForm(twoFactorForm);
    }
    
    // Password strength checker
    const passwordInput = document.getElementById('password');
    if (passwordInput) {
        initPasswordStrength(passwordInput);
    }
    
    // Password confirmation
    const passwordConfirm = document.getElementById('password_confirmation');
    if (passwordConfirm) {
        initPasswordConfirmation(passwordConfirm);
    }
}

// ============================================================
// LOGIN FORM
// ============================================================

/**
 * Initialize login form
 */
function initLoginForm(form) {
    form.addEventListener('submit', function(e) {
        e.preventDefault();
        
        const username = document.getElementById('username');
        const password = document.getElementById('password');
        const remember = document.getElementById('remember');
        const csrfToken = document.querySelector('meta[name="csrf-token"]')?.content;
        
        // Validate
        let isValid = true;
        
        if (!username.value.trim()) {
            showFieldError(username, 'Username is required');
            isValid = false;
        } else {
            clearFieldError(username);
        }
        
        if (!password.value.trim()) {
            showFieldError(password, 'Password is required');
            isValid = false;
        } else {
            clearFieldError(password);
        }
        
        if (!isValid) return;
        
        // Show loading
        const btn = form.querySelector('button[type="submit"]');
        const originalText = btn.innerHTML;
        btn.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span> Signing in...';
        btn.disabled = true;
        
        // Submit
        const formData = new FormData();
        formData.append('username', username.value);
        formData.append('password', password.value);
        formData.append('remember', remember.checked ? 'on' : '');
        formData.append('csrf_token', csrfToken || '');
        
        fetch('/login', {
            method: 'POST',
            body: formData
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                // Redirect to dashboard
                window.location.href = data.redirect || '/dashboard';
            } else {
                // Show error
                showToast(data.message || 'Login failed', 'error');
                btn.innerHTML = originalText;
                btn.disabled = false;
            }
        })
        .catch(error => {
            showToast('An error occurred. Please try again.', 'error');
            btn.innerHTML = originalText;
            btn.disabled = false;
        });
    });
}

// ============================================================
// REGISTRATION FORM
// ============================================================

/**
 * Initialize registration form
 */
function initRegisterForm(form) {
    // Password confirmation
    const password = form.querySelector('#password');
    const passwordConfirm = form.querySelector('#password_confirmation');
    
    if (password && passwordConfirm) {
        passwordConfirm.addEventListener('keyup', function() {
            if (this.value && this.value !== password.value) {
                showFieldError(this, 'Passwords do not match');
            } else {
                clearFieldError(this);
            }
        });
    }
    
    form.addEventListener('submit', function(e) {
        e.preventDefault();
        
        const formData = getFormData(this);
        const csrfToken = document.querySelector('meta[name="csrf-token"]')?.content;
        
        // Validate
        let isValid = true;
        
        // Required fields
        const required = this.querySelectorAll('[required]');
        required.forEach(field => {
            if (!field.value.trim()) {
                showFieldError(field, 'This field is required');
                isValid = false;
            } else {
                clearFieldError(field);
            }
        });
        
        // Password validation
        const passwordField = form.querySelector('#password');
        if (passwordField && passwordField.value.length < 8) {
            showFieldError(passwordField, 'Password must be at least 8 characters');
            isValid = false;
        }
        
        // Password confirmation
        const confirmField = form.querySelector('#password_confirmation');
        if (passwordField && confirmField && passwordField.value !== confirmField.value) {
            showFieldError(confirmField, 'Passwords do not match');
            isValid = false;
        }
        
        // Email validation
        const emailField = form.querySelector('#email');
        if (emailField && emailField.value) {
            const emailRegex = /^[a-zA-Z0-9._%+-]+@[a-zA-Z0-9.-]+\.[a-zA-Z]{2,}$/;
            if (!emailRegex.test(emailField.value)) {
                showFieldError(emailField, 'Please enter a valid email address');
                isValid = false;
            }
        }
        
        if (!isValid) return;
        
        // Show loading
        const btn = form.querySelector('button[type="submit"]');
        const originalText = btn.innerHTML;
        btn.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span> Creating account...';
        btn.disabled = true;
        
        // Add CSRF token
        formData._csrf = csrfToken || '';
        
        fetch('/register', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json'
            },
            body: JSON.stringify(formData)
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                showToast(data.message || 'Registration successful!', 'success');
                setTimeout(() => {
                    window.location.href = '/login';
                }, 2000);
            } else {
                showToast(data.message || 'Registration failed', 'error');
                btn.innerHTML = originalText;
                btn.disabled = false;
            }
        })
        .catch(error => {
            showToast('An error occurred. Please try again.', 'error');
            btn.innerHTML = originalText;
            btn.disabled = false;
        });
    });
}

// ============================================================
// PASSWORD RESET FORM
// ============================================================

/**
 * Initialize password reset form
 */
function initResetForm(form) {
    form.addEventListener('submit', function(e) {
        e.preventDefault();
        
        const email = document.getElementById('email');
        const csrfToken = document.querySelector('meta[name="csrf-token"]')?.content;
        
        if (!email.value.trim()) {
            showFieldError(email, 'Email is required');
            return;
        }
        
        const btn = form.querySelector('button[type="submit"]');
        const originalText = btn.innerHTML;
        btn.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span> Sending...';
        btn.disabled = true;
        
        const formData = new FormData();
        formData.append('email', email.value);
        formData.append('csrf_token', csrfToken || '');
        
        fetch('/password/forgot', {
            method: 'POST',
            body: formData
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                showToast(data.message || 'Reset link sent to your email', 'success');
                btn.innerHTML = originalText;
                btn.disabled = false;
            } else {
                showToast(data.message || 'Failed to send reset link', 'error');
                btn.innerHTML = originalText;
                btn.disabled = false;
            }
        })
        .catch(error => {
            showToast('An error occurred. Please try again.', 'error');
            btn.innerHTML = originalText;
            btn.disabled = false;
        });
    });
}

// ============================================================
// TWO-FACTOR AUTHENTICATION
// ============================================================

/**
 * Initialize two-factor authentication form
 */
function initTwoFactorForm(form) {
    // Auto-advance code inputs
    const inputs = form.querySelectorAll('.code-input');
    if (inputs.length > 0) {
        inputs.forEach((input, index) => {
            input.addEventListener('input', function(e) {
                // Only allow numbers
                this.value = this.value.replace(/\D/g, '');
                
                // Auto-advance
                if (this.value.length === 1 && index < inputs.length - 1) {
                    inputs[index + 1].focus();
                }
            });
            
            input.addEventListener('keydown', function(e) {
                if (e.key === 'Backspace' && this.value === '' && index > 0) {
                    inputs[index - 1].focus();
                }
            });
            
            // Paste support
            input.addEventListener('paste', function(e) {
                e.preventDefault();
                const paste = (e.clipboardData || window.clipboardData).getData('text');
                const digits = paste.replace(/\D/g, '').slice(0, inputs.length);
                digits.split('').forEach((digit, i) => {
                    if (i < inputs.length) {
                        inputs[i].value = digit;
                    }
                });
                if (digits.length === inputs.length) {
                    inputs[inputs.length - 1].focus();
                }
            });
        });
    }
    
    form.addEventListener('submit', function(e) {
        e.preventDefault();
        
        // Collect code
        let code = '';
        form.querySelectorAll('.code-input').forEach(input => {
            code += input.value;
        });
        
        if (code.length < 6) {
            showToast('Please enter the complete verification code', 'warning');
            return;
        }
        
        const csrfToken = document.querySelector('meta[name="csrf-token"]')?.content;
        const btn = form.querySelector('button[type="submit"]');
        const originalText = btn.innerHTML;
        btn.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span> Verifying...';
        btn.disabled = true;
        
        const formData = new FormData();
        formData.append('code', code);
        formData.append('csrf_token', csrfToken || '');
        
        fetch('/2fa/verify', {
            method: 'POST',
            body: formData
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                window.location.href = data.redirect || '/dashboard';
            } else {
                showToast(data.message || 'Invalid verification code', 'error');
                btn.innerHTML = originalText;
                btn.disabled = false;
            }
        })
        .catch(error => {
            showToast('An error occurred. Please try again.', 'error');
            btn.innerHTML = originalText;
            btn.disabled = false;
        });
    });
}

// ============================================================
// PASSWORD STRENGTH
// ============================================================

/**
 * Initialize password strength checker
 */
function initPasswordStrength(input) {
    const bar = document.querySelector('.password-strength-bar');
    const text = document.querySelector('.password-strength-text');
    
    if (!bar || !text) return;
    
    input.addEventListener('keyup', function() {
        const password = this.value;
        let strength = 0;
        let message = '';
        let color = '';
        
        // Length
        if (password.length >= 8) strength += 25;
        if (password.length >= 12) strength += 10;
        
        // Complexity
        if (/[a-z]/.test(password)) strength += 15;
        if (/[A-Z]/.test(password)) strength += 15;
        if (/[0-9]/.test(password)) strength += 15;
        if (/[!@#$%^&*(),.?":{}|<>]/.test(password)) strength += 20;
        
        // Determine level
        if (strength <= 25) {
            message = 'Weak';
            color = '#EF4444';
        } else if (strength <= 50) {
            message = 'Fair';
            color = '#F59E0B';
        } else if (strength <= 75) {
            message = 'Good';
            color = '#3B82F6';
        } else {
            message = 'Strong';
            color = '#22C55E';
        }
        
        bar.style.width = Math.min(strength, 100) + '%';
        bar.style.background = color;
        text.textContent = message;
        text.style.color = color;
    });
}

// ============================================================
// PASSWORD CONFIRMATION
// ============================================================

/**
 * Initialize password confirmation
 */
function initPasswordConfirmation(input) {
    const password = document.getElementById('password');
    if (!password) return;
    
    input.addEventListener('keyup', function() {
        if (this.value && this.value !== password.value) {
            showFieldError(this, 'Passwords do not match');
            this.classList.remove('is-valid');
            this.classList.add('is-invalid');
        } else {
            clearFieldError(this);
            this.classList.remove('is-invalid');
            this.classList.add('is-valid');
        }
    });
}

// ============================================================
// PASSWORD TOGGLE
// ============================================================

/**
 * Toggle password visibility
 */
document.addEventListener('click', function(e) {
    const toggleBtn = e.target.closest('.toggle-password');
    if (!toggleBtn) return;
    
    const input = toggleBtn.closest('.input-group').querySelector('input');
    if (!input) return;
    
    const icon = toggleBtn.querySelector('i');
    if (input.type === 'password') {
        input.type = 'text';
        icon.classList.remove('fa-eye');
        icon.classList.add('fa-eye-slash');
    } else {
        input.type = 'password';
        icon.classList.remove('fa-eye-slash');
        icon.classList.add('fa-eye');
    }
});