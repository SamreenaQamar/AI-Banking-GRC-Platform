<?php
/**
 * Dashboard Widgets
 * 
 * Reusable widget components for the dashboard
 * 
 * @var array $stats
 */
?>

<!-- Widget: Compliance Score -->
<div class="widget-card">
    <div class="widget-header">
        <span class="widget-title">Compliance Score</span>
        <div class="widget-icon" style="background: rgba(37, 99, 235, 0.1); color: #2563EB;">
            <i class="fas fa-check-circle"></i>
        </div>
    </div>
    <div class="widget-value"><?php echo $stats['compliance_completion_rate'] ?? 68; ?>%</div>
    <div class="widget-change positive">
        <i class="fas fa-arrow-up"></i> 5.2% from last month
    </div>
    <div class="progress mt-2" style="height: 6px;">
        <div class="progress-bar" style="width: <?php echo $stats['compliance_completion_rate'] ?? 68; ?>%; background: #2563EB;"></div>
    </div>
</div>

<!-- Widget: Risk Score -->
<div class="widget-card">
    <div class="widget-header">
        <span class="widget-title">Risk Score</span>
        <div class="widget-icon" style="background: rgba(239, 68, 68, 0.1); color: #EF4444;">
            <i class="fas fa-shield-alt"></i>
        </div>
    </div>
    <div class="widget-value"><?php echo $stats['avg_risk_score'] ?? 65; ?>%</div>
    <div class="widget-change negative">
        <i class="fas fa-arrow-up"></i> 3.1% from last month
    </div>
    <div class="progress mt-2" style="height: 6px;">
        <div class="progress-bar" style="width: <?php echo $stats['avg_risk_score'] ?? 65; ?>%; background: #EF4444;"></div>
    </div>
</div>

<!-- Widget: Open Risks -->
<div class="widget-card">
    <div class="widget-header">
        <span class="widget-title">Open Risks</span>
        <div class="widget-icon" style="background: rgba(245, 158, 11, 0.1); color: #F59E0B;">
            <i class="fas fa-exclamation-triangle"></i>
        </div>
    </div>
    <div class="widget-value"><?php echo $stats['open_risks'] ?? 23; ?></div>
    <div class="widget-change negative">
        <i class="fas fa-arrow-up"></i> 2 from last week
    </div>
    <div class="risk-breakdown mt-2">
        <span class="badge bg-danger">Critical: <?php echo $stats['critical_risks'] ?? 5; ?></span>
        <span class="badge bg-warning">High: <?php echo $stats['high_risks'] ?? 8; ?></span>
    </div>
</div>

<!-- Widget: Audit Findings -->
<div class="widget-card">
    <div class="widget-header">
        <span class="widget-title">Audit Findings</span>
        <div class="widget-icon" style="background: rgba(16, 185, 129, 0.1); color: #10B981;">
            <i class="fas fa-clipboard-list"></i>
        </div>
    </div>
    <div class="widget-value"><?php echo $stats['audit_findings'] ?? 12; ?></div>
    <div class="widget-change positive">
        <i class="fas fa-arrow-down"></i> 4 from last week
    </div>
    <div class="findings-status mt-2">
        <span class="text-success"><i class="fas fa-check-circle"></i> Resolved: <?php echo $stats['resolved_findings'] ?? 8; ?></span>
        <span class="text-warning ms-3"><i class="fas fa-clock"></i> Open: <?php echo $stats['open_findings'] ?? 4; ?></span>
    </div>
</div>

<!-- Widget: SBP Circulars -->
<div class="widget-card">
    <div class="widget-header">
        <span class="widget-title">SBP Circulars</span>
        <div class="widget-icon" style="background: rgba(37, 99, 235, 0.1); color: #2563EB;">
            <i class="fas fa-newspaper"></i>
        </div>
    </div>
    <div class="widget-value"><?php echo $stats['total_circulars'] ?? 45; ?></div>
    <div class="widget-change">
        <i class="fas fa-clock"></i> Pending: <?php echo $stats['pending_circulars'] ?? 12; ?>
    </div>
    <div class="circular-status mt-2">
        <span class="badge bg-success">Implemented: <?php echo $stats['implemented_circulars'] ?? 28; ?></span>
        <span class="badge bg-warning ms-2">Active: <?php echo $stats['active_circulars'] ?? 5; ?></span>
    </div>
</div>

<!-- Widget: User Activity -->
<div class="widget-card">
    <div class="widget-header">
        <span class="widget-title">Active Users</span>
        <div class="widget-icon" style="background: rgba(139, 92, 246, 0.1); color: #8B5CF6;">
            <i class="fas fa-users"></i>
        </div>
    </div>
    <div class="widget-value"><?php echo $stats['active_users'] ?? 42; ?></div>
    <div class="widget-change positive">
        <i class="fas fa-arrow-up"></i> 3 new this week
    </div>
    <div class="user-status mt-2">
        <span class="text-muted">Total: <?php echo $stats['total_users'] ?? 56; ?></span>
        <span class="text-success ms-3"><i class="fas fa-circle" style="font-size: 8px;"></i> Online: <?php echo $stats['online_users'] ?? 12; ?></span>
    </div>
</div>

<!-- Widget: AI Queries -->
<div class="widget-card">
    <div class="widget-header">
        <span class="widget-title">AI Assistant</span>
        <div class="widget-icon" style="background: linear-gradient(135deg, #0B3D91, #2563EB); color: #fff;">
            <i class="fas fa-robot"></i>
        </div>
    </div>
    <div class="widget-value"><?php echo $stats['ai_queries'] ?? 156; ?></div>
    <div class="widget-change positive">
        <i class="fas fa-arrow-up"></i> 12% from last week
    </div>
    <div class="ai-status mt-2">
        <span class="text-primary"><i class="fas fa-brain"></i> Accuracy: <?php echo $stats['ai_accuracy'] ?? 94; ?>%</span>
    </div>
</div>

<!-- Widget: Upcoming Deadlines -->
<div class="widget-card">
    <div class="widget-header">
        <span class="widget-title">Upcoming Deadlines</span>
        <div class="widget-icon" style="background: rgba(245, 158, 11, 0.1); color: #F59E0B;">
            <i class="fas fa-clock"></i>
        </div>
    </div>
    <div class="widget-value"><?php echo $stats['upcoming_deadlines'] ?? 8; ?></div>
    <div class="widget-change">
        <i class="fas fa-calendar"></i> This week
    </div>
    <div class="deadline-list mt-2">
        <?php if (!empty($stats['deadline_items'])): ?>
            <?php foreach (array_slice($stats['deadline_items'], 0, 3) as $item): ?>
                <div class="deadline-item">
                    <span class="deadline-name"><?php echo htmlspecialchars($item['name']); ?></span>
                    <span class="deadline-date <?php echo $item['days'] <= 2 ? 'text-danger' : 'text-muted'; ?>">
                        <?php echo $item['days']; ?> days
                    </span>
                </div>
            <?php endforeach; ?>
        <?php else: ?>
            <span class="text-muted">No upcoming deadlines</span>
        <?php endif; ?>
    </div>
</div>

<style>
.widget-card {
    padding: 20px;
    border-radius: 12px;
    background: #fff;
    box-shadow: 0 1px 3px rgba(0,0,0,0.06);
    transition: all 0.3s;
    height: 100%;
}

.widget-card:hover {
    box-shadow: 0 4px 12px rgba(0,0,0,0.1);
    transform: translateY(-2px);
}

.widget-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 8px;
}

.widget-title {
    font-size: 14px;
    color: #64748B;
    font-weight: 500;
}

.widget-icon {
    width: 40px;
    height: 40px;
    border-radius: 10px;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 18px;
}

.widget-value {
    font-size: 28px;
    font-weight: 700;
    color: #1E293B;
}

.widget-change {
    font-size: 13px;
    font-weight: 500;
    margin-top: 4px;
}

.widget-change.positive { color: #22C55E; }
.widget-change.negative { color: #EF4444; }

.risk-breakdown .badge,
.circular-status .badge {
    font-size: 11px;
    padding: 3px 10px;
    margin-right: 4px;
}

.findings-status {
    font-size: 13px;
}

.deadline-item {
    display: flex;
    justify-content: space-between;
    font-size: 13px;
    padding: 3px 0;
    border-bottom: 1px solid #F1F5F9;
}

.deadline-item:last-child {
    border-bottom: none;
}

.deadline-name {
    color: #1E293B;
}

.deadline-date {
    font-weight: 500;
    font-size: 12px;
}

.user-status,
.ai-status {
    font-size: 13px;
}

@media (max-width: 768px) {
    .widget-value {
        font-size: 22px;
    }
}
</style>