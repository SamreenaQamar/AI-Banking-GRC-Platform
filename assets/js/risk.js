/**
 * AI Banking GRC Platform - Risk Module JavaScript
 * 
 * This file contains risk-specific functionality including:
 * - Risk register management
 * - Risk assessment
 * - Risk heatmap
 * - Risk mitigation
 * - Basel III dashboard
 */

'use strict';

// ============================================================
// DOM READY
// ============================================================

document.addEventListener('DOMContentLoaded', function() {
    initRisk();
});

/**
 * Initialize risk functionality
 */
function initRisk() {
    // Risk filters
    initRiskFilters();
    
    // Risk assessment
    initRiskAssessment();
    
    // Risk heatmap
    initRiskHeatmap();
    
    // Risk mitigation
    initRiskMitigation();
    
    // Basel III dashboard
    initBaselDashboard();
}

// ============================================================
// RISK FILTERS
// ============================================================

/**
 * Initialize risk filters
 */
function initRiskFilters() {
    // Search
    const searchInput = document.getElementById('riskSearch');
    if (searchInput) {
        searchInput.addEventListener('keyup', function() {
            const searchTerm = this.value.toLowerCase();
            const rows = document.querySelectorAll('.risk-table tbody tr');
            
            rows.forEach(row => {
                const text = row.textContent.toLowerCase();
                row.style.display = text.includes(searchTerm) ? '' : 'none';
            });
        });
    }
    
    // Filters
    const filters = {
        status: document.getElementById('filterStatus'),
        level: document.getElementById('filterLevel'),
        category: document.getElementById('filterCategory')
    };
    
    Object.values(filters).forEach(filter => {
        if (filter) {
            filter.addEventListener('change', applyRiskFilters);
        }
    });
}

/**
 * Apply risk filters
 */
function applyRiskFilters() {
    const status = document.getElementById('filterStatus')?.value;
    const level = document.getElementById('filterLevel')?.value;
    const category = document.getElementById('filterCategory')?.value;
    
    const rows = document.querySelectorAll('.risk-table tbody tr');
    
    rows.forEach(row => {
        let show = true;
        
        if (status) {
            const rowStatus = row.querySelector('.status-badge')?.textContent.toLowerCase().trim();
            if (rowStatus !== status) show = false;
        }
        
        if (level) {
            const rowLevel = row.querySelector('.risk-level')?.textContent.toLowerCase().trim();
            if (rowLevel !== level) show = false;
        }
        
        row.style.display = show ? '' : 'none';
    });
}

// ============================================================
// RISK ASSESSMENT
// ============================================================

/**
 * Initialize risk assessment
 */
function initRiskAssessment() {
    const form = document.getElementById('riskAssessmentForm');
    if (!form) return;
    
    // Auto-calculate score
    const likelihood = document.getElementById('likelihood');
    const impact = document.getElementById('impact');
    const scoreDisplay = document.getElementById('riskScore');
    
    if (likelihood && impact && scoreDisplay) {
        likelihood.addEventListener('change', calculateRiskScore);
        impact.addEventListener('change', calculateRiskScore);
    }
    
    form.addEventListener('submit', function(e) {
        e.preventDefault();
        
        const formData = getFormData(this);
        const csrfToken = document.querySelector('meta[name="csrf-token"]')?.content;
        
        const btn = form.querySelector('button[type="submit"]');
        const originalText = btn.innerHTML;
        btn.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span> Submitting...';
        btn.disabled = true;
        
        fetch(this.action, {
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
                showToast('Risk assessment completed', 'success');
                setTimeout(() => {
                    window.location.reload();
                }, 1500);
            } else {
                showToast(data.message || 'Assessment failed', 'error');
                btn.innerHTML = originalText;
                btn.disabled = false;
            }
        })
        .catch(error => {
            showToast('An error occurred', 'error');
            btn.innerHTML = originalText;
            btn.disabled = false;
        });
    });
}

/**
 * Calculate risk score
 */
function calculateRiskScore() {
    const likelihood = parseInt(document.getElementById('likelihood').value) || 0;
    const impact = parseInt(document.getElementById('impact').value) || 0;
    
    if (likelihood > 0 && impact > 0) {
        const score = Math.round((likelihood * impact / 25) * 100);
        document.getElementById('riskScore').value = score;
        
        // Update risk level
        const level = document.getElementById('riskLevel');
        if (level) {
            if (score >= 80) {
                level.value = 'critical';
                level.style.color = '#DC2626';
            } else if (score >= 60) {
                level.value = 'high';
                level.style.color = '#EF4444';
            } else if (score >= 40) {
                level.value = 'medium';
                level.style.color = '#F59E0B';
            } else {
                level.value = 'low';
                level.style.color = '#22C55E';
            }
        }
    }
}

// ============================================================
// RISK HEATMAP
// ============================================================

/**
 * Initialize risk heatmap
 */
function initRiskHeatmap() {
    const cells = document.querySelectorAll('.heatmap-cell.has-risk');
    if (!cells.length) return;
    
    cells.forEach(cell => {
        cell.addEventListener('click', function() {
            const likelihood = this.dataset.likelihood;
            const impact = this.dataset.impact;
            
            window.location.href = `/risk/register?likelihood=${likelihood}&impact=${impact}`;
        });
    });
}

// ============================================================
// RISK MITIGATION
// ============================================================

/**
 * Initialize risk mitigation
 */
function initRiskMitigation() {
    // Update progress
    document.querySelectorAll('.update-progress').forEach(btn => {
        btn.addEventListener('click', function() {
            const id = this.dataset.id;
            const currentProgress = parseInt(this.closest('tr').querySelector('.progress-bar').style.width) || 0;
            
            const newProgress = prompt('Enter new progress percentage (0-100):', currentProgress);
            if (newProgress === null) return;
            
            const progress = parseInt(newProgress);
            if (isNaN(progress) || progress < 0 || progress > 100) {
                showToast('Please enter a valid number between 0 and 100', 'warning');
                return;
            }
            
            const csrfToken = document.querySelector('meta[name="csrf-token"]')?.content;
            
            fetch(`/api/risk/mitigation/${id}/progress`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': csrfToken || ''
                },
                body: JSON.stringify({ progress: progress })
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    location.reload();
                } else {
                    showToast('Failed to update progress', 'error');
                }
            })
            .catch(error => {
                showToast('An error occurred', 'error');
            });
        });
    });
}

// ============================================================
// BASEL III DASHBOARD
// ============================================================

/**
 * Initialize Basel III dashboard
 */
function initBaselDashboard() {
    // Refresh button
    const refreshBtn = document.getElementById('refreshBasel');
    if (refreshBtn) {
        refreshBtn.addEventListener('click', function() {
            const icon = this.querySelector('i');
            icon.classList.add('fa-spin');
            
            fetch('/api/risk/basel/refresh')
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        updateBaselMetrics(data.data);
                        showToast('Basel metrics updated', 'success');
                    }
                })
                .finally(() => {
                    icon.classList.remove('fa-spin');
                });
        });
    }
}

/**
 * Update Basel metrics
 */
function updateBaselMetrics(metrics) {
    const updates = {
        cet1: document.querySelector('.basel-cet1 .basel-value'),
        tier1: document.querySelector('.basel-tier1 .basel-value'),
        car: document.querySelector('.basel-car .basel-value'),
        leverage: document.querySelector('.basel-leverage .basel-value')
    };
    
    if (metrics.cet1 && updates.cet1) {
        updates.cet1.textContent = metrics.cet1 + '%';
        updates.cet1.className = 'basel-value ' + (metrics.cet1 >= 10.5 ? 'text-success' : 'text-danger');
    }
    
    if (metrics.tier1 && updates.tier1) {
        updates.tier1.textContent = metrics.tier1 + '%';
        updates.tier1.className = 'basel-value ' + (metrics.tier1 >= 12 ? 'text-success' : 'text-danger');
    }
    
    if (metrics.car && updates.car) {
        updates.car.textContent = metrics.car + '%';
        updates.car.className = 'basel-value ' + (metrics.car >= 14 ? 'text-success' : 'text-danger');
    }
    
    if (metrics.leverage && updates.leverage) {
        updates.leverage.textContent = metrics.leverage + '%';
        updates.leverage.className = 'basel-value ' + (metrics.leverage >= 4.5 ? 'text-success' : 'text-danger');
    }
}