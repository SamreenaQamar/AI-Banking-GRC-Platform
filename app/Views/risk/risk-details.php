<?php
/**
 * Risk Details Page
 * 
 * @var string $title
 * @var object $risk
 * @var array $assessments
 * @var array $history
 * @var array $comments
 */
?>

<?php $page_title = 'Risk Details'; ?>
<?php $active_page = 'risk'; ?>

<div class="risk-details-container">
    <!-- Navigation -->
    <nav aria-label="breadcrumb" class="mb-4">
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="<?php echo BASE_URL; ?>/risk">Risk</a></li>
            <li class="breadcrumb-item"><a href="<?php echo BASE_URL; ?>/risk/register">Risk Register</a></li>
            <li class="breadcrumb-item active" aria-current="page"><?php echo htmlspecialchars($risk->risk_code ?? ''); ?></li>
        </ol>
    </nav>
    
    <div class="row g-4">
        <!-- Main Content -->
        <div class="col-xl-8">
            <!-- Risk Header -->
            <div class="card">
                <div class="card-header-gradient">
                    <div class="d-flex justify-content-between align-items-start">
                        <div>
                            <h5 class="mb-1 text-white"><?php echo htmlspecialchars($risk->title ?? ''); ?></h5>
                            <small class="text-white-50">
                                <i class="fas fa-hashtag me-1"></i>
                                <?php echo htmlspecialchars($risk->risk_code ?? 'N/A'); ?>
                            </small>
                        </div>
                        <div class="d-flex gap-2">
                            <span class="risk-level <?php echo $risk->risk_level ?? 'low'; ?>">
                                <?php echo ucfirst($risk->risk_level ?? 'Low'); ?>
                            </span>
                            <span class="status-badge <?php echo $risk->status ?? 'identified'; ?>">
                                <?php echo ucfirst($risk->status ?? 'Identified'); ?>
                            </span>
                        </div>
                    </div>
                </div>
                <div class="card-body">
                    <div class="risk-meta mb-4">
                        <div class="row g-3">
                            <div class="col-md-3">
                                <div class="meta-item">
                                    <label>Category</label>
                                    <div class="meta-value"><?php echo htmlspecialchars($risk->category_name ?? 'N/A'); ?></div>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="meta-item">
                                    <label>Department</label>
                                    <div class="meta-value"><?php echo htmlspecialchars($risk->department_name ?? 'N/A'); ?></div>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="meta-item">
                                    <label>Owner</label>
                                    <div class="meta-value"><?php echo htmlspecialchars($risk->owner_name ?? 'Unassigned'); ?></div>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="meta-item">
                                    <label>Identified</label>
                                    <div class="meta-value"><?php echo date('d M Y', strtotime($risk->identification_date ?? 'now')); ?></div>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <div class="risk-description">
                        <h6>Description</h6>
                        <p><?php echo nl2br(htmlspecialchars($risk->description ?? '')); ?></p>
                    </div>
                    
                    <!-- Risk Scores -->
                    <div class="risk-scores mt-4">
                        <div class="row g-3">
                            <div class="col-md-6">
                                <div class="score-box inherent">
                                    <div class="score-label">Inherent Risk</div>
                                    <div class="score-value"><?php echo $risk->inherent_risk_score ?? 0; ?>%</div>
                                    <div class="score-details">
                                        <span>Likelihood: <?php echo $risk->inherent_likelihood ?? 0; ?>/5</span>
                                        <span>Impact: <?php echo $risk->inherent_impact ?? 0; ?>/5</span>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="score-box residual">
                                    <div class="score-label">Residual Risk</div>
                                    <div class="score-value"><?php echo $risk->residual_risk_score ?? 0; ?>%</div>
                                    <div class="score-details">
                                        <span>Likelihood: <?php echo $risk->residual_likelihood ?? 'N/A'; ?>/5</span>
                                        <span>Impact: <?php echo $risk->residual_impact ?? 'N/A'; ?>/5</span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Assessments -->
            <div class="card mt-4">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <span><i class="fas fa-clipboard-check me-2"></i> Risk Assessments</span>
                    <button class="btn btn-sm btn-primary" data-bs-toggle="modal" data-bs-target="#assessRiskModal">
                        <i class="fas fa-plus me-1"></i> New Assessment
                    </button>
                </div>
                <div class="card-body p-0">
                    <?php if (!empty($assessments)): ?>
                        <div class="assessment-timeline">
                            <?php foreach ($assessments as $assessment): ?>
                                <div class="assessment-item">
                                    <div class="assessment-date">
                                        <span class="date-day"><?php echo date('d', strtotime($assessment->assessment_date)); ?></span>
                                        <span class="date-month"><?php echo date('M', strtotime($assessment->assessment_date)); ?></span>
                                    </div>
                                    <div class="assessment-content">
                                        <div class="assessment-header">
                                            <span class="assessment-scorer">
                                                <i class="fas fa-user me-1"></i>
                                                <?php echo htmlspecialchars($assessment->assessor_name ?? 'System'); ?>
                                            </span>
                                            <span class="assessment-score">
                                                Score: <?php echo $assessment->inherent_risk_score ?? 0; ?>%
                                            </span>
                                        </div>
                                        <?php if ($assessment->mitigation_plans): ?>
                                            <div class="assessment-plans">
                                                <small class="text-muted">Mitigation Plans:</small>
                                                <p><?php echo nl2br(htmlspecialchars($assessment->mitigation_plans)); ?></p>
                                            </div>
                                        <?php endif; ?>
                                        <?php if ($assessment->recommendations): ?>
                                            <div class="assessment-recommendations">
                                                <small class="text-muted">Recommendations:</small>
                                                <p><?php echo nl2br(htmlspecialchars($assessment->recommendations)); ?></p>
                                            </div>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    <?php else: ?>
                        <div class="text-center text-muted py-4">
                            <i class="fas fa-clipboard fa-2x mb-2"></i>
                            <p>No assessments yet</p>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
            
            <!-- History -->
            <div class="card mt-4">
                <div class="card-header">
                    <i class="fas fa-history me-2"></i> Risk History
                </div>
                <div class="card-body p-0">
                    <?php if (!empty($history)): ?>
                        <div class="history-timeline">
                            <?php foreach ($history as $entry): ?>
                                <div class="history-item">
                                    <div class="history-icon <?php echo $entry->action_type ?? 'info'; ?>">
                                        <i class="fas fa-<?php echo $entry->icon ?? 'circle'; ?>"></i>
                                    </div>
                                    <div class="history-content">
                                        <div class="history-action"><?php echo htmlspecialchars($entry->action); ?></div>
                                        <div class="history-details"><?php echo htmlspecialchars($entry->details); ?></div>
                                        <div class="history-meta">
                                            <span><i class="fas fa-user me-1"></i> <?php echo htmlspecialchars($entry->user_name ?? 'System'); ?></span>
                                            <span><i class="far fa-clock me-1"></i> <?php echo date('d M Y h:i A', strtotime($entry->created_at)); ?></span>
                                        </div>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    <?php else: ?>
                        <div class="text-center text-muted py-4">
                            <i class="fas fa-inbox fa-2x mb-2"></i>
                            <p>No history available</p>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
        
        <!-- Sidebar -->
        <div class="col-xl-4">
            <!-- Actions -->
            <div class="card">
                <div class="card-header">
                    <i class="fas fa-tasks me-2"></i> Actions
                </div>
                <div class="card-body">
                    <div class="d-grid gap-2">
                        <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#assessRiskModal">
                            <i class="fas fa-clipboard-check me-2"></i> Assess Risk
                        </button>
                        <button class="btn btn-outline-success" data-bs-toggle="modal" data-bs-target="#mitigateRiskModal">
                            <i class="fas fa-shield-alt me-2"></i> Mitigate Risk
                        </button>
                        <button class="btn btn-outline-warning">
                            <i class="fas fa-edit me-2"></i> Edit Risk
                        </button>
                        <button class="btn btn-outline-secondary">
                            <i class="fas fa-file-pdf me-2"></i> Generate Report
                        </button>
                    </div>
                </div>
            </div>
            
            <!-- Risk Heatmap -->
            <div class="card mt-4">
                <div class="card-header">
                    <i class="fas fa-fire me-2"></i> Risk Heatmap
                </div>
                <div class="card-body">
                    <div class="heatmap-grid">
                        <div class="heatmap-row">
                            <span class="heatmap-label">5</span>
                            <div class="heatmap-cells">
                                <div class="heatmap-cell" style="background: #22C55E;"></div>
                                <div class="heatmap-cell" style="background: #F59E0B;"></div>
                                <div class="heatmap-cell" style="background: #F97316;"></div>
                                <div class="heatmap-cell" style="background: #EF4444;"></div>
                                <div class="heatmap-cell active" style="background: #DC2626;"></div>
                            </div>
                        </div>
                        <div class="heatmap-row">
                            <span class="heatmap-label">4</span>
                            <div class="heatmap-cells">
                                <div class="heatmap-cell" style="background: #22C55E;"></div>
                                <div class="heatmap-cell" style="background: #F59E0B;"></div>
                                <div class="heatmap-cell" style="background: #F97316;"></div>
                                <div class="heatmap-cell active" style="background: #EF4444;"></div>
                                <div class="heatmap-cell" style="background: #DC2626;"></div>
                            </div>
                        </div>
                        <div class="heatmap-row">
                            <span class="heatmap-label">3</span>
                            <div class="heatmap-cells">
                                <div class="heatmap-cell" style="background: #22C55E;"></div>
                                <div class="heatmap-cell" style="background: #F59E0B;"></div>
                                <div class="heatmap-cell active" style="background: #F97316;"></div>
                                <div class="heatmap-cell" style="background: #EF4444;"></div>
                                <div class="heatmap-cell" style="background: #DC2626;"></div>
                            </div>
                        </div>
                        <div class="heatmap-row">
                            <span class="heatmap-label">2</span>
                            <div class="heatmap-cells">
                                <div class="heatmap-cell" style="background: #22C55E;"></div>
                                <div class="heatmap-cell active" style="background: #F59E0B;"></div>
                                <div class="heatmap-cell" style="background: #F97316;"></div>
                                <div class="heatmap-cell" style="background: #EF4444;"></div>
                                <div class="heatmap-cell" style="background: #DC2626;"></div>
                            </div>
                        </div>
                        <div class="heatmap-row">
                            <span class="heatmap-label">1</span>
                            <div class="heatmap-cells">
                                <div class="heatmap-cell active" style="background: #22C55E;"></div>
                                <div class="heatmap-cell" style="background: #F59E0B;"></div>
                                <div class="heatmap-cell" style="background: #F97316;"></div>
                                <div class="heatmap-cell" style="background: #EF4444;"></div>
                                <div class="heatmap-cell" style="background: #DC2626;"></div>
                            </div>
                        </div>
                        <div class="heatmap-labels">
                            <span></span>
                            <span>1</span>
                            <span>2</span>
                            <span>3</span>
                            <span>4</span>
                            <span>5</span>
                        </div>
                    </div>
                    <div class="heatmap-legend mt-3">
                        <span class="legend-label">Impact</span>
                        <span class="legend-item" style="background: #22C55E;">Low</span>
                        <span class="legend-item" style="background: #F59E0B;">Medium</span>
                        <span class="legend-item" style="background: #EF4444;">High</span>
                        <span class="legend-item" style="background: #DC2626;">Critical</span>
                    </div>
                </div>
            </div>
            
            <!-- Comments -->
            <div class="card mt-4">
                <div class="card-header">
                    <i class="fas fa-comments me-2"></i> Comments
                    <span class="badge bg-primary ms-2"><?php echo count($comments ?? []); ?></span>
                </div>
                <div class="card-body">
                    <?php if (!empty($comments)): ?>
                        <?php foreach ($comments as $comment): ?>
                            <div class="comment-item">
                                <div class="comment-header">
                                    <span class="comment-author"><?php echo htmlspecialchars($comment->user_name ?? 'Anonymous'); ?></span>
                                    <span class="comment-time"><?php echo date('d M Y h:i A', strtotime($comment->created_at)); ?></span>
                                </div>
                                <div class="comment-text"><?php echo nl2br(htmlspecialchars($comment->comment)); ?></div>
                            </div>
                        <?php endforeach; ?>
                    <?php endif; ?>
                    
                    <form method="POST" action="<?php echo BASE_URL; ?>/risk/<?php echo $risk->id ?? 0; ?>/comment" class="mt-3">
                        <input type="hidden" name="csrf_token" value="<?php echo $csrf_token ?? ''; ?>">
                        <div class="input-group">
                            <input type="text" class="form-control" name="comment" placeholder="Add a comment..." required>
                            <button class="btn btn-primary" type="submit">
                                <i class="fas fa-paper-plane"></i>
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Assess Risk Modal -->
<div class="modal fade" id="assessRiskModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title"><i class="fas fa-clipboard-check me-2"></i> Assess Risk</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form method="POST" action="<?php echo BASE_URL; ?>/risk/<?php echo $risk->id ?? 0; ?>/assess">
                <div class="modal-body">
                    <input type="hidden" name="csrf_token" value="<?php echo $csrf_token ?? ''; ?>">
                    
                    <div class="row g-3">
                        <div class="col-md-3">
                            <label class="form-label">Likelihood</label>
                            <input type="number" class="form-control" name="likelihood_score" min="1" max="5" required>
                        </div>
                        <div class="col-md-3">
                            <label class="form-label">Impact</label>
                            <input type="number" class="form-control" name="impact_score" min="1" max="5" required>
                        </div>
                        <div class="col-md-3">
                            <label class="form-label">Velocity</label>
                            <input type="number" class="form-control" name="velocity_score" min="1" max="5">
                        </div>
                        <div class="col-md-3">
                            <label class="form-label">Persistence</label>
                            <input type="number" class="form-control" name="persistence_score" min="1" max="5">
                        </div>
                        <div class="col-12">
                            <label class="form-label">Mitigation Plans</label>
                            <textarea class="form-control" name="mitigation_plans" rows="3"></textarea>
                        </div>
                        <div class="col-12">
                            <label class="form-label">Recommendations</label>
                            <textarea class="form-control" name="recommendations" rows="3"></textarea>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Action Deadline</label>
                            <input type="date" class="form-control" name="action_deadline">
                        </div>
                        <div class="col-md-6">
                            <div class="form-check mt-4">
                                <input class="form-check-input" type="checkbox" name="action_required" id="actionRequired">
                                <label class="form-check-label" for="actionRequired">Action Required</label>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">Submit Assessment</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Mitigate Risk Modal -->
<div class="modal fade" id="mitigateRiskModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title"><i class="fas fa-shield-alt me-2"></i> Mitigate Risk</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form method="POST" action="<?php echo BASE_URL; ?>/risk/<?php echo $risk->id ?? 0; ?>/mitigate">
                <div class="modal-body">
                    <input type="hidden" name="csrf_token" value="<?php echo $csrf_token ?? ''; ?>">
                    
                    <div class="form-group">
                        <label class="form-label">Mitigation Plan</label>
                        <textarea class="form-control" name="mitigation_plan" rows="4" required></textarea>
                    </div>
                    <div class="form-group">
                        <label class="form-label">Mitigation Date</label>
                        <input type="date" class="form-control" name="mitigation_date" value="<?php echo date('Y-m-d'); ?>">
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-success">Confirm Mitigation</button>
                </div>
            </form>
        </div>
    </div>
</div>

<style>
.risk-details-container {
    padding: 0;
}

.card-header-gradient {
    background: linear-gradient(135deg, #0B3D91, #2563EB);
    padding: 20px 24px;
    border-radius: 12px 12px 0 0;
}

.risk-level {
    padding: 4px 16px;
    border-radius: 12px;
    font-size: 13px;
    font-weight: 600;
}

.risk-level.critical { background: #FEE2E2; color: #DC2626; }
.risk-level.high { background: #FEF3C7; color: #F59E0B; }
.risk-level.medium { background: #DBEAFE; color: #3B82F6; }
.risk-level.low { background: #D1FAE5; color: #10B981; }

.status-badge {
    padding: 4px 16px;
    border-radius: 12px;
    font-size: 13px;
    font-weight: 500;
}

.status-badge.identified { background: #F1F5F9; color: #64748B; }
.status-badge.assessed { background: #DBEAFE; color: #3B82F6; }
.status-badge.mitigated { background: #FEF3C7; color: #F59E0B; }
.status-badge.monitored { background: #D1FAE5; color: #10B981; }
.status-badge.closed { background: #E2E8F0; color: #475569; }

.meta-item {
    padding: 4px 0;
}

.meta-item label {
    display: block;
    font-size: 12px;
    color: #94A3B8;
    font-weight: 500;
    margin-bottom: 2px;
}

.meta-item .meta-value {
    font-size: 15px;
    color: #1E293B;
    font-weight: 500;
}

.score-box {
    padding: 16px 20px;
    border-radius: 10px;
    text-align: center;
}

.score-box.inherent {
    background: #F0F7FF;
    border: 1px solid #DBEAFE;
}

.score-box.residual {
    background: #F0FDF4;
    border: 1px solid #BBF7D0;
}

.score-box .score-label {
    font-size: 13px;
    color: #64748B;
    font-weight: 500;
}

.score-box .score-value {
    font-size: 32px;
    font-weight: 700;
    color: #1E293B;
    margin: 4px 0;
}

.score-details {
    display: flex;
    gap: 16px;
    justify-content: center;
    font-size: 13px;
    color: #64748B;
}

.assessment-item {
    display: flex;
    gap: 16px;
    padding: 16px 20px;
    border-bottom: 1px solid #F1F5F9;
}

.assessment-date {
    text-align: center;
    min-width: 56px;
    padding-top: 4px;
}

.assessment-date .date-day {
    display: block;
    font-size: 24px;
    font-weight: 700;
    color: #1E293B;
}

.assessment-date .date-month {
    display: block;
    font-size: 12px;
    color: #94A3B8;
    text-transform: uppercase;
}

.assessment-content {
    flex: 1;
}

.assessment-header {
    display: flex;
    justify-content: space-between;
    margin-bottom: 8px;
}

.assessment-scorer {
    font-weight: 500;
    color: #1E293B;
    font-size: 14px;
}

.assessment-score {
    font-weight: 600;
    color: #2563EB;
}

.assessment-plans,
.assessment-recommendations {
    margin-top: 4px;
}

.assessment-plans p,
.assessment-recommendations p {
    font-size: 14px;
    color: #64748B;
    margin: 4px 0 0;
}

.history-item {
    display: flex;
    gap: 16px;
    padding: 12px 20px;
    border-bottom: 1px solid #F1F5F9;
}

.history-icon {
    width: 32px;
    height: 32px;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    flex-shrink: 0;
    font-size: 12px;
}

.history-icon.success { background: #D1FAE5; color: #10B981; }
.history-icon.warning { background: #FEF3C7; color: #F59E0B; }
.history-icon.danger { background: #FEE2E2; color: #EF4444; }
.history-icon.info { background: #DBEAFE; color: #3B82F6; }

.history-content {
    flex: 1;
}

.history-action {
    font-weight: 500;
    color: #1E293B;
    font-size: 14px;
}

.history-details {
    font-size: 13px;
    color: #64748B;
}

.history-meta {
    display: flex;
    gap: 16px;
    font-size: 12px;
    color: #94A3B8;
    margin-top: 4px;
}

.heatmap-grid {
    display: flex;
    flex-direction: column;
    gap: 2px;
}

.heatmap-row {
    display: flex;
    align-items: center;
    gap: 8px;
}

.heatmap-label {
    width: 20px;
    font-size: 12px;
    color: #94A3B8;
    text-align: right;
}

.heatmap-cells {
    display: flex;
    gap: 2px;
    flex: 1;
}

.heatmap-cell {
    width: 100%;
    aspect-ratio: 1;
    border-radius: 3px;
    transition: all 0.3s;
}

.heatmap-cell.active {
    transform: scale(1.1);
    box-shadow: 0 0 0 2px #fff, 0 0 0 4px #2563EB;
}

.heatmap-labels {
    display: flex;
    padding-left: 28px;
    gap: 2px;
    font-size: 11px;
    color: #94A3B8;
}

.heatmap-labels span {
    flex: 1;
    text-align: center;
}

.heatmap-legend {
    display: flex;
    align-items: center;
    gap: 8px;
    font-size: 12px;
}

.legend-label {
    color: #64748B;
    margin-right: 4px;
}

.legend-item {
    padding: 2px 10px;
    border-radius: 4px;
    color: #fff;
    font-size: 11px;
}

.comment-item {
    padding: 8px 0;
    border-bottom: 1px solid #F1F5F9;
}

.comment-item:last-child {
    border-bottom: none;
}

.comment-header {
    display: flex;
    justify-content: space-between;
    font-size: 13px;
}

.comment-author {
    font-weight: 500;
    color: #1E293B;
}

.comment-time {
    color: #94A3B8;
    font-size: 12px;
}

.comment-text {
    font-size: 14px;
    color: #64748B;
    margin-top: 4px;
}

@media (max-width: 768px) {
    .score-box .score-value {
        font-size: 24px;
    }
    
    .assessment-item {
        flex-direction: column;
        gap: 8px;
    }
    
    .assessment-date {
        display: flex;
        gap: 8px;
        align-items: center;
    }
    
    .assessment-date .date-day {
        font-size: 18px;
    }
}
</style>

<script>
$(document).ready(function() {
    // Assessment form submission
    $('#assessRiskModal form').on('submit', function(e) {
        const btn = $(this).find('button[type="submit"]');
        btn.html('<i class="fas fa-spinner fa-spin me-1"></i> Submitting...');
        btn.prop('disabled', true);
    });
});
</script>