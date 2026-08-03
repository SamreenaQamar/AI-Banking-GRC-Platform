/**
 * AI Banking GRC Platform - Compliance Module JavaScript
 * 
 * This file contains compliance-specific functionality including:
 * - Circular management
 * - Compliance checklist
 * - Gap analysis
 * - Recommendations
 * - Compliance calendar
 */

'use strict';

// ============================================================
// DOM READY
// ============================================================

document.addEventListener('DOMContentLoaded', function() {
    initCompliance();
});

/**
 * Initialize compliance functionality
 */
function initCompliance() {
    // Circular filters
    initCircularFilters();
    
    // Compliance checklist
    initComplianceChecklist();
    
    // Gap analysis
    initGapAnalysis();
    
    // Recommendations
    initRecommendations();
    
    // Compliance calendar
    initComplianceCalendar();
}

// ============================================================
// CIRCULAR FILTERS
// ============================================================

/**
 * Initialize circular filters
 */
function initCircularFilters() {
    const filterBtns = document.querySelectorAll('.circular-filter');
    if (!filterBtns.length) return;
    
    filterBtns.forEach(btn => {
        btn.addEventListener('click', function() {
            // Update active state
            filterBtns.forEach(b => b.classList.remove('active'));
            this.classList.add('active');
            
            // Filter circulars
            const filter = this.dataset.filter;
            const circulars = document.querySelectorAll('.circular-card');
            
            circulars.forEach(card => {
                if (filter === 'all' || card.dataset.status === filter) {
                    card.style.display = 'flex';
                } else {
                    card.style.display = 'none';
                }
            });
        });
    });
}

// ============================================================
// COMPLIANCE CHECKLIST
// ============================================================

/**
 * Initialize compliance checklist
 */
function initComplianceChecklist() {
    const checkboxes = document.querySelectorAll('.checklist-checkbox');
    if (!checkboxes.length) return;
    
    checkboxes.forEach(checkbox => {
        checkbox.addEventListener('change', function() {
            const id = this.dataset.id;
            const checked = this.checked;
            const item = this.closest('.checklist-item');
            const csrfToken = document.querySelector('meta[name="csrf-token"]')?.content;
            
            // Update UI immediately
            item.classList.toggle('completed', checked);
            
            // Save to server
            fetch(`/api/compliance/checklist/${id}`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': csrfToken || ''
                },
                body: JSON.stringify({ status: checked ? 'completed' : 'pending' })
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    updateChecklistProgress();
                } else {
                    // Revert on error
                    item.classList.toggle('completed', !checked);
                    checkbox.checked = !checked;
                    showToast('Failed to update checklist', 'error');
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
 * Update checklist progress
 */
function updateChecklistProgress() {
    const total = document.querySelectorAll('.checklist-item').length;
    const completed = document.querySelectorAll('.checklist-item.completed').length;
    const progress = total > 0 ? Math.round((completed / total) * 100) : 0;
    
    const progressBar = document.querySelector('.checklist-progress-bar');
    if (progressBar) {
        progressBar.style.width = progress + '%';
        progressBar.textContent = progress + '%';
    }
    
    const progressText = document.querySelector('.checklist-progress-text');
    if (progressText) {
        progressText.textContent = `${completed}/${total} completed`;
    }
}

// ============================================================
// GAP ANALYSIS
// ============================================================

/**
 * Initialize gap analysis
 */
function initGapAnalysis() {
    // Framework selector
    const frameworkSelect = document.getElementById('frameworkSelect');
    if (frameworkSelect) {
        frameworkSelect.addEventListener('change', function() {
            loadGapAnalysis(this.value);
        });
    }
    
    // Remediate buttons
    document.querySelectorAll('.remediate-gap').forEach(btn => {
        btn.addEventListener('click', function() {
            const id = this.dataset.id;
            const csrfToken = document.querySelector('meta[name="csrf-token"]')?.content;
            
            if (!confirm('Mark this gap as remediated?')) return;
            
            fetch(`/api/compliance/gap/${id}/remediate`, {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': csrfToken || ''
                }
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    showToast('Gap marked as remediated', 'success');
                    loadGapAnalysis(frameworkSelect.value);
                } else {
                    showToast('Failed to remediate gap', 'error');
                }
            })
            .catch(error => {
                showToast('An error occurred', 'error');
            });
        });
    });
}

/**
 * Load gap analysis data
 */
function loadGapAnalysis(framework) {
    const csrfToken = document.querySelector('meta[name="csrf-token"]')?.content;
    
    fetch(`/api/compliance/gap-analysis?framework=${framework || 'all'}`, {
        headers: {
            'X-CSRF-TOKEN': csrfToken || ''
        }
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            renderGapAnalysis(data.data);
        }
    })
    .catch(error => {
        showToast('Failed to load gap analysis', 'error');
    });
}

/**
 * Render gap analysis
 */
function renderGapAnalysis(gaps) {
    const container = document.getElementById('gapAnalysisContainer');
    if (!container) return;
    
    container.innerHTML = '';
    
    if (!gaps || gaps.length === 0) {
        container.innerHTML = `
            <div class="text-center py-4">
                <i class="fas fa-check-circle fa-3x text-success mb-3"></i>
                <h5>No Gaps Found</h5>
                <p class="text-muted">All compliance controls are implemented</p>
            </div>
        `;
        return;
    }
    
    gaps.forEach(gap => {
        const card = document.createElement('div');
        card.className = 'gap-card mb-3';
        card.innerHTML = `
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <h6 class="mb-0">${gap.category}</h6>
                    <small class="text-muted">${gap.gaps} gaps identified</small>
                </div>
                <span class="badge bg-${gap.compliance_rate >= 80 ? 'success' : gap.compliance_rate >= 60 ? 'warning' : 'danger'}">
                    ${gap.compliance_rate}%
                </span>
            </div>
            <div class="progress mt-2" style="height: 6px;">
                <div class="progress-bar" style="width: ${gap.compliance_rate}%; 
                     background: ${gap.compliance_rate >= 80 ? '#22C55E' : gap.compliance_rate >= 60 ? '#F59E0B' : '#EF4444'};">
                </div>
            </div>
            <div class="d-flex gap-3 mt-2">
                <small>Implemented: ${gap.implemented}</small>
                <small>In Progress: ${gap.in_progress}</small>
                <small>Pending: ${gap.pending}</small>
            </div>
        `;
        container.appendChild(card);
    });
}

// ============================================================
// RECOMMENDATIONS
// ============================================================

/**
 * Initialize recommendations
 */
function initRecommendations() {
    // Implement recommendation
    document.querySelectorAll('.implement-rec').forEach(btn => {
        btn.addEventListener('click', function() {
            const id = this.dataset.id;
            const csrfToken = document.querySelector('meta[name="csrf-token"]')?.content;
            
            if (!confirm('Mark this recommendation as implemented?')) return;
            
            fetch(`/api/compliance/recommendation/${id}/implement`, {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': csrfToken || ''
                }
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    showToast('Recommendation implemented', 'success');
                    location.reload();
                } else {
                    showToast('Failed to implement recommendation', 'error');
                }
            })
            .catch(error => {
                showToast('An error occurred', 'error');
            });
        });
    });
    
    // Dismiss recommendation
    document.querySelectorAll('.dismiss-rec').forEach(btn => {
        btn.addEventListener('click', function() {
            const id = this.dataset.id;
            const csrfToken = document.querySelector('meta[name="csrf-token"]')?.content;
            
            if (!confirm('Dismiss this recommendation?')) return;
            
            fetch(`/api/compliance/recommendation/${id}/dismiss`, {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': csrfToken || ''
                }
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    showToast('Recommendation dismissed', 'info');
                    location.reload();
                } else {
                    showToast('Failed to dismiss recommendation', 'error');
                }
            })
            .catch(error => {
                showToast('An error occurred', 'error');
            });
        });
    });
}

// ============================================================
// COMPLIANCE CALENDAR
// ============================================================

/**
 * Initialize compliance calendar
 */
function initComplianceCalendar() {
    const prevBtn = document.getElementById('calendarPrev');
    const nextBtn = document.getElementById('calendarNext');
    const monthDisplay = document.getElementById('calendarMonth');
    
    if (!prevBtn || !nextBtn || !monthDisplay) return;
    
    let currentMonth = new Date().getMonth();
    let currentYear = new Date().getFullYear();
    
    prevBtn.addEventListener('click', function() {
        currentMonth--;
        if (currentMonth < 0) {
            currentMonth = 11;
            currentYear--;
        }
        loadCalendar(currentMonth, currentYear);
    });
    
    nextBtn.addEventListener('click', function() {
        currentMonth++;
        if (currentMonth > 11) {
            currentMonth = 0;
            currentYear++;
        }
        loadCalendar(currentMonth, currentYear);
    });
    
    loadCalendar(currentMonth, currentYear);
}

/**
 * Load calendar
 */
function loadCalendar(month, year) {
    const csrfToken = document.querySelector('meta[name="csrf-token"]')?.content;
    
    fetch(`/api/compliance/calendar?month=${month + 1}&year=${year}`, {
        headers: {
            'X-CSRF-TOKEN': csrfToken || ''
        }
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            renderCalendar(data.data, month, year);
        }
    })
    .catch(error => {
        showToast('Failed to load calendar', 'error');
    });
}

/**
 * Render calendar
 */
function renderCalendar(events, month, year) {
    const container = document.querySelector('.calendar-days');
    if (!container) return;
    
    const monthNames = ['January', 'February', 'March', 'April', 'May', 'June', 
                        'July', 'August', 'September', 'October', 'November', 'December'];
    document.getElementById('calendarMonth').textContent = `${monthNames[month]} ${year}`;
    
    container.innerHTML = '';
    
    const firstDay = new Date(year, month, 1).getDay();
    const daysInMonth = new Date(year, month + 1, 0).getDate();
    const today = new Date();
    
    // Empty days
    for (let i = 0; i < firstDay; i++) {
        const empty = document.createElement('div');
        empty.className = 'calendar-day empty';
        container.appendChild(empty);
    }
    
    // Days
    for (let day = 1; day <= daysInMonth; day++) {
        const date = `${year}-${String(month + 1).padStart(2, '0')}-${String(day).padStart(2, '0')}`;
        const isToday = date === today.toISOString().split('T')[0];
        const hasEvent = events && events[date] && events[date].length > 0;
        
        const dayEl = document.createElement('div');
        dayEl.className = `calendar-day${isToday ? ' today' : ''}${hasEvent ? ' has-event' : ''}`;
        dayEl.dataset.date = date;
        dayEl.innerHTML = `
            <span class="day-number">${day}</span>
            ${hasEvent ? '<span class="event-dot"></span>' : ''}
            ${isToday ? '<span class="today-badge">Today</span>' : ''}
        `;
        
        if (hasEvent) {
            dayEl.addEventListener('click', function() {
                showDayEvents(events[date]);
            });
        }
        
        container.appendChild(dayEl);
    }
}

/**
 * Show day events
 */
function showDayEvents(events) {
    const modal = document.getElementById('eventModal');
    if (!modal) return;
    
    const list = modal.querySelector('.event-list');
    if (!list) return;
    
    list.innerHTML = '';
    events.forEach(event => {
        const item = document.createElement('div');
        item.className = 'event-item';
        item.innerHTML = `
            <div class="event-time">${event.time || 'All Day'}</div>
            <div class="event-content">
                <div class="event-title">${event.title}</div>
                <div class="event-description">${event.description || ''}</div>
            </div>
            <span class="badge bg-${event.status === 'completed' ? 'success' : 'warning'}">
                ${event.status || 'Pending'}
            </span>
        `;
        list.appendChild(item);
    });
    
    const modalInstance = new bootstrap.Modal(modal);
    modalInstance.show();
}