/**
 * AI Banking GRC Platform - Reports Module JavaScript
 * 
 * This file contains reports-specific functionality including:
 * - Report generation
 * - Report export
 * - Report scheduling
 * - Report download
 */

'use strict';

// ============================================================
// DOM READY
// ============================================================

document.addEventListener('DOMContentLoaded', function() {
    initReports();
});

/**
 * Initialize reports functionality
 */
function initReports() {
    // Report generation
    initReportGeneration();
    
    // Report export
    initReportExport();
    
    // Report scheduling
    initReportScheduling();
    
    // Report download
    initReportDownload();
}

// ============================================================
// REPORT GENERATION
// ============================================================

/**
 * Initialize report generation
 */
function initReportGeneration() {
    const form = document.getElementById('generateReportForm');
    if (!form) return;
    
    // Date range toggle
    const dateRange = document.getElementById('dateRange');
    const customDates = document.getElementById('customDateRange');
    
    if (dateRange && customDates) {
        dateRange.addEventListener('change', function() {
            customDates.style.display = this.value === 'custom' ? 'block' : 'none';
        });
    }
    
    // Schedule toggle
    const scheduleCheck = document.getElementById('scheduleReport');
    const scheduleOptions = document.getElementById('scheduleOptions');
    
    if (scheduleCheck && scheduleOptions) {
        scheduleCheck.addEventListener('change', function() {
            scheduleOptions.style.display = this.checked ? 'block' : 'none';
        });
    }
    
    form.addEventListener('submit', function(e) {
        e.preventDefault();
        
        const formData = getFormData(this);
        const csrfToken = document.querySelector('meta[name="csrf-token"]')?.content;
        
        const btn = form.querySelector('button[type="submit"]');
        const originalText = btn.innerHTML;
        btn.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span> Generating...';
        btn.disabled = true;
        
        fetch('/api/reports/generate', {
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
                showToast('Report generated successfully', 'success');
                setTimeout(() => {
                    window.location.href = data.download_url || '/reports';
                }, 1500);
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

// ============================================================
// REPORT EXPORT
// ============================================================

/**
 * Initialize report export
 */
function initReportExport() {
    document.querySelectorAll('.export-report').forEach(btn => {
        btn.addEventListener('click', function() {
            const reportId = this.dataset.id;
            const format = this.dataset.format || 'pdf';
            
            // Show loading
            const originalText = this.innerHTML;
            this.innerHTML = '<span class="spinner-border spinner-border-sm me-1"></span> Exporting...';
            this.disabled = true;
            
            fetch(`/api/reports/${reportId}/export?format=${format}`)
                .then(response => response.blob())
                .then(blob => {
                    const url = window.URL.createObjectURL(blob);
                    const a = document.createElement('a');
                    a.href = url;
                    a.download = `report-${reportId}.${format}`;
                    document.body.appendChild(a);
                    a.click();
                    document.body.removeChild(a);
                    window.URL.revokeObjectURL(url);
                    
                    showToast('Report exported successfully', 'success');
                })
                .catch(error => {
                    showToast('Export failed', 'error');
                })
                .finally(() => {
                    this.innerHTML = originalText;
                    this.disabled = false;
                });
        });
    });
}

// ============================================================
// REPORT SCHEDULING
// ============================================================

/**
 * Initialize report scheduling
 */
function initReportScheduling() {
    const form = document.getElementById('scheduleReportForm');
    if (!form) return;
    
    form.addEventListener('submit', function(e) {
        e.preventDefault();
        
        const formData = getFormData(this);
        const csrfToken = document.querySelector('meta[name="csrf-token"]')?.content;
        
        const btn = form.querySelector('button[type="submit"]');
        const originalText = btn.innerHTML;
        btn.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span> Scheduling...';
        btn.disabled = true;
        
        fetch('/api/reports/schedule', {
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
                showToast('Report scheduled successfully', 'success');
                setTimeout(() => window.location.reload(), 1500);
            } else {
                showToast(data.message || 'Scheduling failed', 'error');
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

// ============================================================
// REPORT DOWNLOAD
// ============================================================

/**
 * Initialize report download
 */
function initReportDownload() {
    document.querySelectorAll('.download-report').forEach(btn => {
        btn.addEventListener('click', function(e) {
            e.preventDefault();
            
            const reportId = this.dataset.id;
            
            // Show loading
            const originalText = this.innerHTML;
            this.innerHTML = '<span class="spinner-border spinner-border-sm me-1"></span> Downloading...';
            this.disabled = true;
            
            window.location.href = `/api/reports/${reportId}/download`;
            
            setTimeout(() => {
                this.innerHTML = originalText;
                this.disabled = false;
            }, 2000);
        });
    });
}