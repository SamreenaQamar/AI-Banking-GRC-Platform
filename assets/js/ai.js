/**
 * AI Banking GRC Platform - AI Module JavaScript
 * 
 * This file contains AI-specific functionality including:
 * - AI chat
 * - AI policy generator
 * - AI risk analyzer
 * - AI gap analysis
 * - AI recommendations
 */

'use strict';

// ============================================================
// DOM READY
// ============================================================

document.addEventListener('DOMContentLoaded', function() {
    initAI();
});

/**
 * Initialize AI functionality
 */
function initAI() {
    // AI Chat
    initAIChat();
    
    // AI Policy Generator
    initAIPolicyGenerator();
    
    // AI Risk Analyzer
    initAIRiskAnalyzer();
    
    // AI Gap Analysis
    initAIGapAnalysis();
    
    // AI Recommendations
    initAIRecommendations();
}

// ============================================================
// AI CHAT
// ============================================================

/**
 * Initialize AI chat
 */
function initAIChat() {
    const form = document.getElementById('chatForm');
    const input = document.getElementById('chatInput');
    const messages = document.getElementById('chatMessages');
    const suggestions = document.querySelectorAll('.suggestion-badge');
    
    if (!form || !input || !messages) return;
    
    // Suggestion click
    suggestions.forEach(suggestion => {
        suggestion.addEventListener('click', function() {
            input.value = this.textContent;
            form.dispatchEvent(new Event('submit'));
        });
    });
    
    // Form submit
    form.addEventListener('submit', function(e) {
        e.preventDefault();
        
        const message = input.value.trim();
        if (!message) return;
        
        // Add user message
        addChatMessage('user', message);
        input.value = '';
        
        // Show typing indicator
        showTypingIndicator();
        
        // Send to server
        const csrfToken = document.querySelector('meta[name="csrf-token"]')?.content;
        const context = document.getElementById('chatContext')?.value || 'general';
        
        fetch('/api/ai/chat', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': csrfToken || ''
            },
            body: JSON.stringify({
                message: message,
                context: context
            })
        })
        .then(response => response.json())
        .then(data => {
            removeTypingIndicator();
            
            if (data.success) {
                addChatMessage('ai', data.response);
            } else {
                addChatMessage('ai', 'Sorry, I encountered an error. Please try again.');
            }
        })
        .catch(error => {
            removeTypingIndicator();
            addChatMessage('ai', 'Sorry, I encountered an error. Please try again.');
        });
    });
    
    // Enter key support
    input.addEventListener('keypress', function(e) {
        if (e.key === 'Enter' && !e.shiftKey) {
            e.preventDefault();
            form.dispatchEvent(new Event('submit'));
        }
    });
}

/**
 * Add chat message
 */
function addChatMessage(type, text) {
    const messages = document.getElementById('chatMessages');
    if (!messages) return;
    
    const messageDiv = document.createElement('div');
    messageDiv.className = `message-item ${type}-message`;
    messageDiv.innerHTML = `
        <div class="message-avatar">
            <i class="fas fa-${type === 'ai' ? 'robot' : 'user'}"></i>
        </div>
        <div class="message-content">
            <div class="message-bubble">
                ${text.replace(/\n/g, '<br>')}
            </div>
            <span class="message-time">Just now</span>
        </div>
    `;
    
    messages.appendChild(messageDiv);
    messages.scrollTop = messages.scrollHeight;
}

/**
 * Show typing indicator
 */
function showTypingIndicator() {
    const messages = document.getElementById('chatMessages');
    if (!messages) return;
    
    const indicator = document.createElement('div');
    indicator.id = 'typingIndicator';
    indicator.className = 'message-item ai-message';
    indicator.innerHTML = `
        <div class="message-avatar">
            <i class="fas fa-robot"></i>
        </div>
        <div class="message-content">
            <div class="message-bubble">
                <span class="typing-dots">
                    <span></span><span></span><span></span>
                </span>
            </div>
        </div>
    `;
    
    messages.appendChild(indicator);
    messages.scrollTop = messages.scrollHeight;
}

/**
 * Remove typing indicator
 */
function removeTypingIndicator() {
    const indicator = document.getElementById('typingIndicator');
    if (indicator) {
        indicator.remove();
    }
}

// ============================================================
// AI POLICY GENERATOR
// ============================================================

/**
 * Initialize AI policy generator
 */
function initAIPolicyGenerator() {
    const form = document.getElementById('aiPolicyForm');
    if (!form) return;
    
    form.addEventListener('submit', function(e) {
        e.preventDefault();
        
        const formData = getFormData(this);
        const csrfToken = document.querySelector('meta[name="csrf-token"]')?.content;
        
        const btn = form.querySelector('button[type="submit"]');
        const originalText = btn.innerHTML;
        btn.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span> Generating...';
        btn.disabled = true;
        
        fetch('/api/ai/policy/generate', {
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
                displayAIPolicy(data.data);
                showToast('Policy generated successfully', 'success');
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

/**
 * Display AI policy
 */
function displayAIPolicy(data) {
    const output = document.getElementById('aiPolicyOutput');
    if (!output) return;
    
    output.innerHTML = `
        <div class="generated-policy">
            <div class="d-flex justify-content-between align-items-start mb-3">
                <h5>${data.title || 'Generated Policy'}</h5>
                <div>
                    <button class="btn btn-sm btn-outline-primary" onclick="copyAIPolicy()">
                        <i class="fas fa-copy"></i> Copy
                    </button>
                    <button class="btn btn-sm btn-outline-success" onclick="downloadAIPolicy()">
                        <i class="fas fa-download"></i> Download
                    </button>
                </div>
            </div>
            <div class="policy-content">
                ${data.content || ''}
            </div>
        </div>
    `;
}

/**
 * Copy AI policy
 */
function copyAIPolicy() {
    const content = document.querySelector('#aiPolicyOutput .policy-content');
    if (!content) return;
    
    navigator.clipboard.writeText(content.textContent)
        .then(() => showToast('Policy copied to clipboard', 'success'))
        .catch(() => showToast('Failed to copy', 'error'));
}

/**
 * Download AI policy
 */
function downloadAIPolicy() {
    const content = document.querySelector('#aiPolicyOutput .policy-content');
    if (!content) return;
    
    const blob = new Blob([content.innerHTML], { type: 'text/html' });
    const url = URL.createObjectURL(blob);
    const a = document.createElement('a');
    a.href = url;
    a.download = 'ai-generated-policy.html';
    document.body.appendChild(a);
    a.click();
    document.body.removeChild(a);
    URL.revokeObjectURL(url);
}

// ============================================================
// AI RISK ANALYZER
// ============================================================

/**
 * Initialize AI risk analyzer
 */
function initAIRiskAnalyzer() {
    const form = document.getElementById('aiRiskForm');
    if (!form) return;
    
    form.addEventListener('submit', function(e) {
        e.preventDefault();
        
        const formData = getFormData(this);
        const csrfToken = document.querySelector('meta[name="csrf-token"]')?.content;
        
        const btn = form.querySelector('button[type="submit"]');
        const originalText = btn.innerHTML;
        btn.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span> Analyzing...';
        btn.disabled = true;
        
        const output = document.getElementById('aiRiskOutput');
        if (output) {
            output.innerHTML = '<div class="text-center py-4"><div class="spinner-border text-primary"></div></div>';
        }
        
        fetch('/api/ai/risk/analyze', {
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
                displayAIRiskAnalysis(data.data);
                showToast('Risk analysis completed', 'success');
            } else {
                showToast(data.message || 'Analysis failed', 'error');
                if (output) output.innerHTML = '';
            }
        })
        .catch(error => {
            showToast('An error occurred', 'error');
            if (output) output.innerHTML = '';
        })
        .finally(() => {
            btn.innerHTML = originalText;
            btn.disabled = false;
        });
    });
}

/**
 * Display AI risk analysis
 */
function displayAIRiskAnalysis(data) {
    const output = document.getElementById('aiRiskOutput');
    if (!output) return;
    
    output.innerHTML = `
        <div class="risk-analysis-result">
            <h6>Risk Analysis Results</h6>
            <div class="row g-3 mb-3">
                <div class="col-md-4">
                    <div class="card bg-light">
                        <div class="card-body text-center">
                            <h6>Risk Level</h6>
                            <span class="badge bg-${data.level === 'Critical' ? 'danger' : data.level === 'High' ? 'warning' : 'info'}">
                                ${data.level || 'Medium'}
                            </span>
                        </div>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="card bg-light">
                        <div class="card-body text-center">
                            <h6>Risk Score</h6>
                            <h5 class="mb-0">${data.score || 0}%</h5>
                        </div>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="card bg-light">
                        <div class="card-body text-center">
                            <h6>Confidence</h6>
                            <h5 class="mb-0">${data.confidence || 0}%</h5>
                        </div>
                    </div>
                </div>
            </div>
            <div class="analysis-details">
                ${data.analysis || ''}
            </div>
            ${data.recommendations ? `
                <div class="recommendations mt-3">
                    <h6>Recommendations</h6>
                    <ul>
                        ${data.recommendations.map(r => `<li>${r}</li>`).join('')}
                    </ul>
                </div>
            ` : ''}
        </div>
    `;
}

// ============================================================
// AI GAP ANALYSIS
// ============================================================

/**
 * Initialize AI gap analysis
 */
function initAIGapAnalysis() {
    const form = document.getElementById('aiGapForm');
    if (!form) return;
    
    form.addEventListener('submit', function(e) {
        e.preventDefault();
        
        const formData = getFormData(this);
        const csrfToken = document.querySelector('meta[name="csrf-token"]')?.content;
        
        const btn = form.querySelector('button[type="submit"]');
        const originalText = btn.innerHTML;
        btn.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span> Analyzing...';
        btn.disabled = true;
        
        const output = document.getElementById('aiGapOutput');
        if (output) {
            output.innerHTML = '<div class="text-center py-4"><div class="spinner-border text-primary"></div></div>';
        }
        
        fetch('/api/ai/gap/analyze', {
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
                displayAIGapAnalysis(data.data);
                showToast('Gap analysis completed', 'success');
            } else {
                showToast(data.message || 'Analysis failed', 'error');
                if (output) output.innerHTML = '';
            }
        })
        .catch(error => {
            showToast('An error occurred', 'error');
            if (output) output.innerHTML = '';
        })
        .finally(() => {
            btn.innerHTML = originalText;
            btn.disabled = false;
        });
    });
}

/**
 * Display AI gap analysis
 */
function displayAIGapAnalysis(data) {
    const output = document.getElementById('aiGapOutput');
    if (!output) return;
    
    output.innerHTML = `
        <div class="gap-analysis-result">
            <h6>Gap Analysis Results</h6>
            ${data.summary ? `<p class="text-muted">${data.summary}</p>` : ''}
            <div class="table-responsive">
                <table class="table table-hover">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Description</th>
                            <th>Severity</th>
                            <th>Recommendation</th>
                        </tr>
                    </thead>
                    <tbody>
                        ${data.gaps ? data.gaps.map(gap => `
                            <tr>
                                <td>${gap.id}</td>
                                <td>${gap.description}</td>
                                <td><span class="badge bg-${gap.severity === 'critical' ? 'danger' : gap.severity === 'high' ? 'warning' : 'info'}">${gap.severity}</span></td>
                                <td>${gap.recommendation}</td>
                            </tr>
                        `).join('') : ''}
                    </tbody>
                </table>
            </div>
        </div>
    `;
}

// ============================================================
// AI RECOMMENDATIONS
// ============================================================

/**
 * Initialize AI recommendations
 */
function initAIRecommendations() {
    // Refresh recommendations
    const refreshBtn = document.getElementById('refreshRecommendations');
    if (refreshBtn) {
        refreshBtn.addEventListener('click', function() {
            const icon = this.querySelector('i');
            icon.classList.add('fa-spin');
            
            fetch('/api/ai/recommendations/refresh')
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        location.reload();
                    }
                })
                .finally(() => {
                    icon.classList.remove('fa-spin');
                });
        });
    }
}