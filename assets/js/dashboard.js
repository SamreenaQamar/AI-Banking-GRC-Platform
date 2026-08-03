/**
 * AI Banking GRC Platform - Dashboard JavaScript
 * 
 * This file contains dashboard-specific functionality including:
 * - Chart initialization
 * - Widget updates
 * - Real-time data refresh
 * - Activity feed
 */

'use strict';

// ============================================================
// DOM READY
// ============================================================

document.addEventListener('DOMContentLoaded', function() {
    initDashboard();
});

/**
 * Initialize dashboard
 */
function initDashboard() {
    // Initialize charts
    initDashboardCharts();
    
    // Initialize widget refresh
    initWidgetRefresh();
    
    // Initialize activity feed
    initActivityFeed();
    
    // Initialize notifications
    initDashboardNotifications();
}

// ============================================================
// CHARTS
// ============================================================

/**
 * Initialize dashboard charts
 */
function initDashboardCharts() {
    // Compliance Trend Chart
    initComplianceTrendChart();
    
    // Risk Distribution Chart
    initRiskDistributionChart();
    
    // Audit Status Chart
    initAuditStatusChart();
    
    // Risk Heatmap
    initRiskHeatmap();
}

/**
 * Compliance Trend Chart
 */
function initComplianceTrendChart() {
    const canvas = document.getElementById('complianceTrendChart');
    if (!canvas) return;
    
    const ctx = canvas.getContext('2d');
    
    // Sample data - would come from server
    const data = {
        labels: ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun'],
        datasets: [
            {
                label: 'Compliance Score',
                data: [65, 68, 72, 70, 74, 78],
                borderColor: '#2563EB',
                backgroundColor: 'rgba(37, 99, 235, 0.1)',
                fill: true,
                tension: 0.4,
                pointRadius: 5,
                pointBackgroundColor: '#2563EB'
            },
            {
                label: 'Risk Score',
                data: [60, 62, 58, 65, 68, 65],
                borderColor: '#EF4444',
                backgroundColor: 'rgba(239, 68, 68, 0.1)',
                fill: true,
                tension: 0.4,
                pointRadius: 5,
                pointBackgroundColor: '#EF4444'
            }
        ]
    };
    
    new Chart(ctx, {
        type: 'line',
        data: data,
        options: {
            responsive: true,
            maintainAspectRatio: false,
            interaction: {
                intersect: false,
                mode: 'index'
            },
            plugins: {
                legend: {
                    position: 'top',
                    labels: {
                        usePointStyle: true,
                        boxWidth: 8,
                        padding: 20
                    }
                },
                tooltip: {
                    backgroundColor: '#FFFFFF',
                    titleColor: '#1E293B',
                    bodyColor: '#64748B',
                    borderColor: '#E2E8F0',
                    borderWidth: 1,
                    padding: 12,
                    cornerRadius: 8
                }
            },
            scales: {
                y: {
                    beginAtZero: true,
                    max: 100,
                    grid: {
                        color: '#F1F5F9'
                    },
                    ticks: {
                        callback: function(value) {
                            return value + '%';
                        }
                    }
                },
                x: {
                    grid: {
                        display: false
                    }
                }
            }
        }
    });
}

/**
 * Risk Distribution Chart
 */
function initRiskDistributionChart() {
    const canvas = document.getElementById('riskDistributionChart');
    if (!canvas) return;
    
    const ctx = canvas.getContext('2d');
    
    const data = {
        labels: ['Critical', 'High', 'Medium', 'Low'],
        datasets: [{
            data: [12, 23, 45, 76],
            backgroundColor: ['#DC2626', '#EF4444', '#F59E0B', '#22C55E'],
            borderWidth: 0
        }]
    };
    
    new Chart(ctx, {
        type: 'doughnut',
        data: data,
        options: {
            responsive: true,
            maintainAspectRatio: false,
            cutout: '65%',
            plugins: {
                legend: {
                    position: 'bottom',
                    labels: {
                        usePointStyle: true,
                        padding: 15
                    }
                }
            }
        }
    });
}

/**
 * Audit Status Chart
 */
function initAuditStatusChart() {
    const canvas = document.getElementById('auditStatusChart');
    if (!canvas) return;
    
    const ctx = canvas.getContext('2d');
    
    const data = {
        labels: ['Planned', 'In Progress', 'Completed', 'Closed'],
        datasets: [{
            label: 'Audits',
            data: [12, 8, 15, 10],
            backgroundColor: ['#3B82F6', '#F59E0B', '#22C55E', '#64748B'],
            borderRadius: 6
        }]
    };
    
    new Chart(ctx, {
        type: 'bar',
        data: data,
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: {
                    display: false
                }
            },
            scales: {
                y: {
                    beginAtZero: true,
                    grid: {
                        color: '#F1F5F9'
                    }
                },
                x: {
                    grid: {
                        display: false
                    }
                }
            }
        }
    });
}

/**
 * Risk Heatmap
 */
function initRiskHeatmap() {
    // Heatmap is rendered with CSS in the dashboard view
}

// ============================================================
// WIDGET REFRESH
// ============================================================

/**
 * Initialize widget refresh
 */
function initWidgetRefresh() {
    const refreshInterval = 30000; // 30 seconds
    
    setInterval(() => {
        refreshWidgets();
    }, refreshInterval);
}

/**
 * Refresh widgets
 */
function refreshWidgets() {
    // This would fetch fresh data from server
    // For now, we'll simulate a refresh
    console.log('Refreshing widgets...');
    
    // Update widget values with animation
    document.querySelectorAll('.widget-value').forEach(widget => {
        const currentValue = parseInt(widget.textContent);
        const newValue = currentValue + Math.floor(Math.random() * 3) - 1;
        if (newValue > 0) {
            animateValue(widget, currentValue, newValue, 500);
        }
    });
}

/**
 * Animate numeric value change
 */
function animateValue(element, start, end, duration) {
    const range = end - start;
    const startTime = performance.now();
    
    function update(currentTime) {
        const elapsed = currentTime - startTime;
        const progress = Math.min(elapsed / duration, 1);
        const current = start + range * progress;
        element.textContent = Math.round(current);
        
        if (progress < 1) {
            requestAnimationFrame(update);
        }
    }
    
    requestAnimationFrame(update);
}

// ============================================================
// ACTIVITY FEED
// ============================================================

/**
 * Initialize activity feed
 */
function initActivityFeed() {
    // Auto-refresh activity feed
    const refreshInterval = 60000; // 1 minute
    
    setInterval(() => {
        refreshActivityFeed();
    }, refreshInterval);
}

/**
 * Refresh activity feed
 */
function refreshActivityFeed() {
    // This would fetch recent activities from server
    console.log('Refreshing activity feed...');
}

// ============================================================
// NOTIFICATIONS
// ============================================================

/**
 * Initialize dashboard notifications
 */
function initDashboardNotifications() {
    // Load unread notifications count
    loadUnreadCount();
    
    // Refresh count periodically
    setInterval(() => {
        loadUnreadCount();
    }, 30000);
}

/**
 * Load unread notifications count
 */
function loadUnreadCount() {
    const csrfToken = document.querySelector('meta[name="csrf-token"]')?.content;
    
    fetch('/api/notifications/unread-count', {
        method: 'GET',
        headers: {
            'X-CSRF-TOKEN': csrfToken || ''
        }
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            const badge = document.getElementById('notificationBadge');
            if (badge) {
                badge.textContent = data.count;
                badge.style.display = data.count > 0 ? 'flex' : 'none';
            }
        }
    })
    .catch(error => console.error('Error loading notifications:', error));
}