<?php
/**
 * Compliance Checker Page
 * 
 * @var string $title
 * @var array $frameworks
 * @var array $assessment_data
 */
?>

<?php $page_title = 'Compliance Checker'; ?>
<?php $active_page = 'compliance'; ?>

<div class="compliance-checker-container">
    <!-- Header -->
    <div class="row mb-4">
        <div class="col-md-8">
            <div class="checker-header">
                <h5><i class="fas fa-search me-2 text-primary"></i> Compliance Checker</h5>
                <p class="text-muted">Assess your compliance status against regulatory frameworks</p>
            </div>
        </div>
        <div class="col-md-4 text-end">
            <button class="btn btn-primary" id="newAssessmentBtn">
                <i class="fas fa-plus me-2"></i> New Assessment
            </button>
        </div>
    </div>
    
    <!-- Framework Selection -->
    <div class="card mb-4">
        <div class="card-header">
            <i class="fas fa-layer-group me-2"></i> Select Framework
        </div>
        <div class="card-body">
            <div class="row g-3">
                <div class="col-md-4">
                    <label class="form-label">Framework</label>
                    <select class="form-select" id="frameworkSelect">
                        <option value="">Select a framework...</option>
                        <?php foreach ($frameworks ?? [] as $framework): ?>
                            <option value="<?php echo $framework->id; ?>">
                                <?php echo htmlspecialchars($framework->name); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="col-md-4">
                    <label class="form-label">Version</label>
                    <select class="form-select" id="versionSelect">
                        <option value="latest">Latest Version</option>
                    </select>
                </div>
                <div class="col-md-4">
                    <label class="form-label">&nbsp;</label>
                    <button class="btn btn-primary w-100" id="startCheckerBtn">
                        <i class="fas fa-play me-2"></i> Start Assessment
                    </button>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Assessment Results -->
    <?php if (!empty($assessment_data)): ?>
    <div class="assessment-results">
        <!-- Score Overview -->
        <div class="row g-4 mb-4">
            <div class="col-xl-3 col-lg-6">
                <div class="score-card">
                    <div class="score-label">Overall Compliance</div>
                    <div class="score-value"><?php echo $assessment_data['overall_score'] ?? 68; ?>%</div>
                    <div class="score-bar">
                        <div class="score-fill" style="width: <?php echo $assessment_data['overall_score'] ?? 68; ?>%; 
                             background: <?php echo ($assessment_data['overall_score'] ?? 68) >= 80 ? '#22C55E' : (($assessment_data['overall_score'] ?? 68) >= 60 ? '#F59E0B' : '#EF4444'); ?>;">
                        </div>
                    </div>
                    <div class="score-status">
                        <span class="badge bg-<?php echo ($assessment_data['overall_score'] ?? 68) >= 80 ? 'success' : (($assessment_data['overall_score'] ?? 68) >= 60 ? 'warning' : 'danger'); ?>">
                            <?php echo ($assessment_data['overall_score'] ?? 68) >= 80 ? 'Compliant' : (($assessment_data['overall_score'] ?? 68) >= 60 ? 'Partially Compliant' : 'Non-Compliant'); ?>
                        </span>
                    </div>
                </div>
            </div>
            <div class="col-xl-3 col-lg-6">
                <div class="score-card">
                    <div class="score-label">Controls Implemented</div>
                    <div class="score-value"><?php echo $assessment_data['controls_implemented'] ?? 45; ?></div>
                    <div class="score-meta">of <?php echo $assessment_data['total_controls'] ?? 64; ?> controls</div>
                </div>
            </div>
            <div class="col-xl-3 col-lg-6">
                <div class="score-card">
                    <div class="score-label">Gaps Identified</div>
                    <div class="score-value text-danger"><?php echo $assessment_data['gaps'] ?? 12; ?></div>
                    <div class="score-meta"><?php echo $assessment_data['critical_gaps'] ?? 3; ?> critical</div>
                </div>
            </div>
            <div class="col-xl-3 col-lg-6">
                <div class="score-card">
                    <div class="score-label">Recommendations</div>
                    <div class="score-value text-primary"><?php echo $assessment_data['recommendations'] ?? 8; ?></div>
                    <div class="score-meta"><?php echo $assessment_data['high_priority'] ?? 4; ?> high priority</div>
                </div>
            </div>
        </div>
        
        <!-- Control Assessment Table -->
        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <span><i class="fas fa-list-check me-2"></i> Control Assessment</span>
                <div class="d-flex gap-2">
                    <span class="badge bg-success">Compliant: <?php echo $assessment_data['compliant_count'] ?? 32; ?></span>
                    <span class="badge bg-warning">Partial: <?php echo $assessment_data['partial_count'] ?? 16; ?></span>
                    <span class="badge bg-danger">Non-Compliant: <?php echo $assessment_data['non_compliant_count'] ?? 16; ?></span>
                </div>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover enterprise-table mb-0">
                        <thead>
                            <tr>
                                <th>Control ID</th>
                                <th>Control Description</th>
                                <th>Category</th>
                                <th>Status</th>
                                <th>Evidence</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (!empty($assessment_data['controls'])): ?>
                                <?php foreach ($assessment_data['controls'] as $control): ?>
                                    <tr>
                                        <td><span class="control-id"><?php echo htmlspecialchars($control->id); ?></span></td>
                                        <td><?php echo htmlspecialchars(substr($control->description, 0, 60)) . '...'; ?></td>
                                        <td><?php echo htmlspecialchars($control->category); ?></td>
                                        <td>
                                            <span class="status-badge <?php echo $control->status; ?>">
                                                <?php echo ucfirst($control->status); ?>
                                            </span>
                                        </td>
                                        <td>
                                            <?php if ($control->evidence_count > 0): ?>
                                                <span class="text-success"><i class="fas fa-check-circle me-1"></i> <?php echo $control->evidence_count; ?></span>
                                            <?php else: ?>
                                                <span class="text-muted"><i class="fas fa-times-circle me-1"></i> None</span>
                                            <?php endif; ?>
                                        </td>
                                        <td>
                                            <button class="btn btn-sm btn-outline-primary assess-control" data-id="<?php echo $control->id; ?>">
                                                <i class="fas fa-edit"></i>
                                            </button>
                                            <button class="btn btn-sm btn-outline-secondary" title="Add Evidence">
                                                <i class="fas fa-paperclip"></i>
                                            </button>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <tr>
                                    <td colspan="6" class="text-center py-4 text-muted">
                                        <i class="fas fa-inbox fa-2x d-block mb-2"></i>
                                        No controls assessed yet
                                    </td>
                                </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
    <?php endif; ?>
</div>

<style>
.compliance-checker-container {
    padding: 0;
}

.checker-header h5 {
    margin: 0;
}

.score-card {
    background: #fff;
    padding: 20px;
    border-radius: 12px;
    box-shadow: 0 1px 3px rgba(0,0,0,0.06);
    height: 100%;
}

.score-label {
    font-size: 14px;
    color: #64748B;
    font-weight: 500;
    margin-bottom: 4px;
}

.score-value {
    font-size: 32px;
    font-weight: 700;
    color: #1E293B;
}

.score-bar {
    height: 6px;
    background: #E2E8F0;
    border-radius: 3px;
    overflow: hidden;
    margin: 8px 0 4px;
}

.score-fill {
    height: 100%;
    border-radius: 3px;
    transition: width 1s ease;
}

.score-meta {
    font-size: 13px;
    color: #94A3B8;
}

.score-status {
    margin-top: 8px;
}

.control-id {
    font-family: 'Courier New', monospace;
    font-size: 13px;
    color: #2563EB;
    font-weight: 600;
}

.status-badge {
    padding: 4px 12px;
    border-radius: 12px;
    font-size: 12px;
    font-weight: 500;
}

.status-badge.compliant { background: #D1FAE5; color: #10B981; }
.status-badge.partial { background: #FEF3C7; color: #F59E0B; }
.status-badge.non_compliant { background: #FEE2E2; color: #EF4444; }
.status-badge.pending { background: #F1F5F9; color: #64748B; }

.enterprise-table thead th {
    font-weight: 600;
    font-size: 12px;
    color: #64748B;
    text-transform: uppercase;
    letter-spacing: 0.5px;
    padding: 12px 16px;
    background: #F8FAFC;
}

.enterprise-table tbody td {
    padding: 12px 16px;
    vertical-align: middle;
    border-bottom: 1px solid #F1F5F9;
}

@media (max-width: 768px) {
    .score-value {
        font-size: 24px;
    }
}
</style>

<script>
$(document).ready(function() {
    // Framework selection
    $('#frameworkSelect').on('change', function() {
        const frameworkId = $(this).val();
        if (frameworkId) {
            // Load versions for this framework
            // This would be an AJAX call
        }
    });
    
    // Start assessment
    $('#startCheckerBtn').on('click', function() {
        const frameworkId = $('#frameworkSelect').val();
        if (!frameworkId) {
            alert('Please select a framework first.');
            return;
        }
        window.location.href = '<?php echo BASE_URL; ?>/compliance/checker/start/' + frameworkId;
    });
    
    // Assess control
    $('.assess-control').on('click', function() {
        const controlId = $(this).data('id');
        // Open assessment modal
    });
});
</script>