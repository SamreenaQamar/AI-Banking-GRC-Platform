<?php
/**
 * Risk Heatmap Page
 * 
 * @var string $title
 * @var array $heatmap_data
 * @var array $risk_summary
 */
?>

<?php $page_title = 'Risk Heatmap'; ?>
<?php $active_page = 'risk'; ?>

<div class="heatmap-container">
    <!-- Header -->
    <div class="row mb-4">
        <div class="col-md-8">
            <div class="heatmap-header">
                <h5><i class="fas fa-fire me-2 text-danger"></i> Risk Heatmap</h5>
                <p class="text-muted">Visual representation of risks by likelihood and impact</p>
            </div>
        </div>
        <div class="col-md-4 text-end">
            <button class="btn btn-outline-primary" id="exportHeatmap">
                <i class="fas fa-download me-2"></i> Export
            </button>
            <button class="btn btn-outline-secondary" id="refreshHeatmap">
                <i class="fas fa-sync-alt"></i>
            </button>
        </div>
    </div>
    
    <!-- Main Heatmap -->
    <div class="card mb-4">
        <div class="card-body">
            <div class="heatmap-wrapper">
                <div class="heatmap-grid">
                    <?php
                    $levels = ['Very Low', 'Low', 'Medium', 'High', 'Very High'];
                    $colors = ['#22C55E', '#F59E0B', '#F97316', '#EF4444', '#DC2626'];
                    $risk_matrix = $heatmap_data['matrix'] ?? [];
                    ?>
                    
                    <?php for ($impact = 5; $impact >= 1; $impact--): ?>
                        <div class="heatmap-row">
                            <div class="heatmap-label">
                                <?php echo $levels[$impact - 1]; ?>
                            </div>
                            <?php for ($likelihood = 1; $likelihood <= 5; $likelihood++): ?>
                                <?php
                                $riskCount = $risk_matrix[$impact][$likelihood] ?? 0;
                                $cellClass = $riskCount > 0 ? 'has-risk' : '';
                                $riskLevel = $this->getRiskLevel($impact * $likelihood);
                                $color = $riskCount > 0 ? $colors[$riskLevel] : '#F1F5F9';
                                ?>
                                <div class="heatmap-cell <?php echo $cellClass; ?>" 
                                     style="background: <?php echo $color; ?>; 
                                            <?php echo $riskCount > 0 ? 'cursor: pointer;' : ''; ?>"
                                     data-impact="<?php echo $impact; ?>"
                                     data-likelihood="<?php echo $likelihood; ?>"
                                     data-count="<?php echo $riskCount; ?>"
                                     title="<?php echo $riskCount > 0 ? $riskCount . ' risks' : 'No risks'; ?>">
                                    <?php if ($riskCount > 0): ?>
                                        <span class="cell-count"><?php echo $riskCount; ?></span>
                                    <?php endif; ?>
                                </div>
                            <?php endfor; ?>
                        </div>
                    <?php endfor; ?>
                    
                    <div class="heatmap-labels">
                        <div class="heatmap-label">Impact</div>
                        <?php for ($i = 1; $i <= 5; $i++): ?>
                            <div class="heatmap-label">
                                <?php echo $levels[$i - 1]; ?>
                            </div>
                        <?php endfor; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Risk Summary Cards -->
    <div class="row g-4">
        <div class="col-xl-3 col-lg-6">
            <div class="summary-card">
                <div class="summary-header">
                    <span class="summary-title">Critical Risks</span>
                    <span class="summary-badge" style="background: #DC2626;"><?php echo $risk_summary['critical'] ?? 0; ?></span>
                </div>
                <div class="summary-description">Requires immediate action</div>
            </div>
        </div>
        <div class="col-xl-3 col-lg-6">
            <div class="summary-card">
                <div class="summary-header">
                    <span class="summary-title">High Risks</span>
                    <span class="summary-badge" style="background: #EF4444;"><?php echo $risk_summary['high'] ?? 0; ?></span>
                </div>
                <div class="summary-description">High priority mitigation needed</div>
            </div>
        </div>
        <div class="col-xl-3 col-lg-6">
            <div class="summary-card">
                <div class="summary-header">
                    <span class="summary-title">Medium Risks</span>
                    <span class="summary-badge" style="background: #F59E0B;"><?php echo $risk_summary['medium'] ?? 0; ?></span>
                </div>
                <div class="summary-description">Plan for mitigation</div>
            </div>
        </div>
        <div class="col-xl-3 col-lg-6">
            <div class="summary-card">
                <div class="summary-header">
                    <span class="summary-title">Low Risks</span>
                    <span class="summary-badge" style="background: #22C55E;"><?php echo $risk_summary['low'] ?? 0; ?></span>
                </div>
                <div class="summary-description">Monitor and review</div>
            </div>
        </div>
    </div>
</div>

<style>
.heatmap-container {
    padding: 0;
}

.heatmap-header h5 {
    margin: 0;
}

.heatmap-wrapper {
    padding: 20px;
    overflow-x: auto;
}

.heatmap-grid {
    display: grid;
    gap: 4px;
    min-width: 600px;
}

.heatmap-row {
    display: grid;
    grid-template-columns: 80px repeat(5, 1fr);
    gap: 4px;
    align-items: center;
}

.heatmap-label {
    font-size: 13px;
    color: #64748B;
    font-weight: 500;
    text-align: right;
    padding-right: 12px;
}

.heatmap-cell {
    aspect-ratio: 1;
    border-radius: 4px;
    display: flex;
    align-items: center;
    justify-content: center;
    transition: all 0.3s;
    position: relative;
    min-height: 50px;
}

.heatmap-cell.has-risk:hover {
    transform: scale(1.05);
    box-shadow: 0 4px 12px rgba(0,0,0,0.2);
    z-index: 1;
}

.heatmap-cell .cell-count {
    font-size: 16px;
    font-weight: 700;
    color: #fff;
    text-shadow: 0 1px 3px rgba(0,0,0,0.3);
}

.heatmap-labels {
    display: grid;
    grid-template-columns: 80px repeat(5, 1fr);
    gap: 4px;
    margin-top: 8px;
}

.heatmap-labels .heatmap-label {
    text-align: center;
    font-size: 12px;
    font-weight: 400;
    color: #94A3B8;
}

.summary-card {
    background: #fff;
    padding: 20px;
    border-radius: 12px;
    box-shadow: 0 1px 3px rgba(0,0,0,0.06);
    transition: all 0.3s;
    height: 100%;
}

.summary-card:hover {
    box-shadow: 0 4px 12px rgba(0,0,0,0.1);
    transform: translateY(-2px);
}

.summary-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 4px;
}

.summary-title {
    font-size: 14px;
    color: #64748B;
    font-weight: 500;
}

.summary-badge {
    padding: 4px 12px;
    border-radius: 12px;
    color: #fff;
    font-weight: 600;
    font-size: 14px;
}

.summary-description {
    font-size: 13px;
    color: #94A3B8;
}

@media (max-width: 768px) {
    .heatmap-row {
        grid-template-columns: 60px repeat(5, 1fr);
    }
    
    .heatmap-label {
        font-size: 11px;
        padding-right: 8px;
    }
    
    .heatmap-cell {
        min-height: 40px;
    }
    
    .heatmap-cell .cell-count {
        font-size: 13px;
    }
}
</style>

<script>
$(document).ready(function() {
    // Click on heatmap cell
    $('.heatmap-cell.has-risk').on('click', function() {
        const impact = $(this).data('impact');
        const likelihood = $(this).data('likelihood');
        window.location.href = '<?php echo BASE_URL; ?>/risk/register?impact=' + impact + '&likelihood=' + likelihood;
    });
    
    // Export heatmap
    $('#exportHeatmap').on('click', function() {
        window.location.href = '<?php echo BASE_URL; ?>/risk/heatmap/export';
    });
    
    // Refresh heatmap
    $('#refreshHeatmap').on('click', function() {
        const btn = $(this);
        btn.html('<i class="fas fa-spinner fa-spin"></i>');
        setTimeout(function() {
            location.reload();
        }, 1000);
    });
});
</script>