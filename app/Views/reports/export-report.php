<?php
/**
 * Export Report Page
 * 
 * @var string $title
 * @var array $export_options
 * @var array $available_reports
 */
?>

<?php $page_title = 'Export Report'; ?>
<?php $active_page = 'reports'; ?>

<div class="export-container">
    <div class="row">
        <div class="col-lg-8 mx-auto">
            <!-- Header -->
            <div class="d-flex justify-content-between align-items-center mb-4">
                <div>
                    <h5><i class="fas fa-download me-2 text-primary"></i> Export Report</h5>
                    <p class="text-muted">Configure and export reports in your preferred format</p>
                </div>
                <a href="<?php echo BASE_URL; ?>/reports" class="btn btn-outline-secondary">
                    <i class="fas fa-arrow-left me-2"></i> Back to Reports
                </a>
            </div>
            
            <!-- Export Form -->
            <div class="card">
                <div class="card-header-gradient">
                    <h6 class="mb-0 text-white"><i class="fas fa-cog me-2"></i> Export Configuration</h6>
                    <small class="text-white-50">Select report type and export options</small>
                </div>
                <div class="card-body p-4">
                    <form id="exportForm" method="POST" action="<?php echo BASE_URL; ?>/reports/export">
                        <input type="hidden" name="csrf_token" value="<?php echo $csrf_token ?? ''; ?>">
                        
                        <div class="row g-4">
                            <!-- Report Selection -->
                            <div class="col-12">
                                <div class="form-group">
                                    <label class="form-label required">Report to Export</label>
                                    <select class="form-select" name="report_id" required>
                                        <option value="">Select a report...</option>
                                        <?php foreach ($available_reports ?? [] as $report): ?>
                                            <option value="<?php echo $report->id; ?>">
                                                <?php echo htmlspecialchars($report->name); ?> 
                                                (<?php echo date('d M Y', strtotime($report->generated_at)); ?>)
                                            </option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                            </div>
                            
                            <!-- Export Format -->
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label class="form-label required">Export Format</label>
                                    <select class="form-select" name="format" required>
                                        <option value="pdf">PDF Document</option>
                                        <option value="xlsx">Excel (XLSX)</option>
                                        <option value="csv">CSV File</option>
                                        <option value="json">JSON Data</option>
                                        <option value="html">HTML Document</option>
                                    </select>
                                </div>
                            </div>
                            
                            <!-- Page Size -->
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label class="form-label">Page Size</label>
                                    <select class="form-select" name="page_size">
                                        <option value="A4">A4</option>
                                        <option value="A3">A3</option>
                                        <option value="Letter">Letter</option>
                                        <option value="Legal">Legal</option>
                                    </select>
                                </div>
                            </div>
                            
                            <!-- Orientation -->
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label class="form-label">Orientation</label>
                                    <select class="form-select" name="orientation">
                                        <option value="portrait">Portrait</option>
                                        <option value="landscape">Landscape</option>
                                    </select>
                                </div>
                            </div>
                            
                            <!-- Include Options -->
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label class="form-label">Include</label>
                                    <div class="d-flex flex-wrap gap-3 mt-2">
                                        <div class="form-check">
                                            <input class="form-check-input" type="checkbox" name="include_charts" id="includeCharts" checked>
                                            <label class="form-check-label" for="includeCharts">Charts</label>
                                        </div>
                                        <div class="form-check">
                                            <input class="form-check-input" type="checkbox" name="include_tables" id="includeTables" checked>
                                            <label class="form-check-label" for="includeTables">Tables</label>
                                        </div>
                                        <div class="form-check">
                                            <input class="form-check-input" type="checkbox" name="include_summary" id="includeSummary" checked>
                                            <label class="form-check-label" for="includeSummary">Summary</label>
                                        </div>
                                        <div class="form-check">
                                            <input class="form-check-input" type="checkbox" name="include_appendix" id="includeAppendix">
                                            <label class="form-check-label" for="includeAppendix">Appendix</label>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            
                            <!-- Email Options -->
                            <div class="col-12">
                                <div class="form-group">
                                    <label class="form-label">Email Report To</label>
                                    <input type="email" class="form-control" name="email_to" 
                                           placeholder="Enter email address (optional)">
                                    <small class="text-muted">If provided, report will be sent via email</small>
                                </div>
                            </div>
                            
                            <!-- Advanced Options -->
                            <div class="col-12">
                                <div class="form-group">
                                    <label class="form-label">Additional Options</label>
                                    <div class="row g-2">
                                        <div class="col-md-4">
                                            <div class="form-check">
                                                <input class="form-check-input" type="checkbox" name="password_protect" id="passwordProtect">
                                                <label class="form-check-label" for="passwordProtect">Password Protect</label>
                                            </div>
                                        </div>
                                        <div class="col-md-4">
                                            <div class="form-check">
                                                <input class="form-check-input" type="checkbox" name="watermark" id="watermark">
                                                <label class="form-check-label" for="watermark">Add Watermark</label>
                                            </div>
                                        </div>
                                        <div class="col-md-4">
                                            <div class="form-check">
                                                <input class="form-check-input" type="checkbox" name="compress" id="compress" checked>
                                                <label class="form-check-label" for="compress">Compress File</label>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        
                        <!-- Form Actions -->
                        <div class="form-actions mt-4 pt-3 border-top">
                            <button type="submit" class="btn btn-primary" id="exportBtn">
                                <i class="fas fa-download me-2"></i> Export Report
                            </button>
                            <button type="reset" class="btn btn-secondary">
                                <i class="fas fa-undo me-2"></i> Reset
                            </button>
                            <a href="<?php echo BASE_URL; ?>/reports" class="btn btn-outline-secondary">
                                <i class="fas fa-times me-2"></i> Cancel
                            </a>
                        </div>
                    </form>
                </div>
            </div>
            
            <!-- Supported Formats -->
            <div class="card mt-4">
                <div class="card-body">
                    <h6><i class="fas fa-info-circle me-2 text-primary"></i> Supported Export Formats</h6>
                    <div class="row g-3 mt-2">
                        <div class="col-md-3">
                            <div class="format-card">
                                <i class="fas fa-file-pdf text-danger"></i>
                                <span>PDF</span>
                                <small>Print-ready documents</small>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="format-card">
                                <i class="fas fa-file-excel text-success"></i>
                                <span>Excel</span>
                                <small>Data analysis</small>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="format-card">
                                <i class="fas fa-file-csv text-primary"></i>
                                <span>CSV</span>
                                <small>Raw data export</small>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="format-card">
                                <i class="fas fa-file-code text-warning"></i>
                                <span>JSON</span>
                                <small>API integration</small>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
.export-container {
    padding: 0;
}

.card-header-gradient {
    background: linear-gradient(135deg, #0B3D91, #2563EB);
    padding: 20px 24px;
    border-radius: 12px 12px 0 0;
}

.required::after {
    content: ' *';
    color: #EF4444;
}

.form-group {
    margin-bottom: 0;
}

.form-group .form-label {
    font-weight: 500;
    font-size: 14px;
    color: #1E293B;
    margin-bottom: 6px;
}

.form-group .form-control,
.form-group .form-select {
    border-radius: 8px;
    border-color: #E2E8F0;
    font-size: 14px;
    transition: all 0.3s;
}

.form-group .form-control:focus,
.form-group .form-select:focus {
    border-color: #2563EB;
    box-shadow: 0 0 0 3px rgba(37, 99, 235, 0.1);
}

.form-actions {
    display: flex;
    gap: 12px;
    flex-wrap: wrap;
}

.format-card {
    text-align: center;
    padding: 16px;
    border-radius: 10px;
    border: 1px solid #E2E8F0;
    transition: all 0.3s;
}

.format-card:hover {
    border-color: #2563EB;
    background: #F0F7FF;
    transform: translateY(-2px);
}

.format-card i {
    display: block;
    font-size: 32px;
    margin-bottom: 4px;
}

.format-card span {
    display: block;
    font-weight: 600;
    color: #1E293B;
}

.format-card small {
    display: block;
    font-size: 11px;
    color: #94A3B8;
}

@media (max-width: 768px) {
    .form-actions {
        flex-direction: column;
    }
    
    .form-actions .btn {
        width: 100%;
    }
}
</style>

<script>
$(document).ready(function() {
    // Password protection toggle
    $('#passwordProtect').on('change', function() {
        if ($(this).is(':checked')) {
            // Show password input
            const passwordHtml = `
                <div class="col-12 mt-2" id="passwordFields">
                    <div class="row g-2">
                        <div class="col-md-6">
                            <input type="password" class="form-control" name="password" placeholder="Enter password">
                        </div>
                        <div class="col-md-6">
                            <input type="password" class="form-control" name="password_confirm" placeholder="Confirm password">
                        </div>
                    </div>
                </div>
            `;
            $(this).closest('.row').append(passwordHtml);
        } else {
            $('#passwordFields').remove();
        }
    });
    
    // Export form submission
    $('#exportForm').on('submit', function(e) {
        e.preventDefault();
        
        const btn = $('#exportBtn');
        btn.html('<i class="fas fa-spinner fa-spin me-2"></i> Exporting...');
        btn.prop('disabled', true);
        
        // Simulate export process
        setTimeout(function() {
            btn.html('<i class="fas fa-download me-2"></i> Export Report');
            btn.prop('disabled', false);
            
            // Show success message
            showSuccessMessage();
        }, 2000);
    });
    
    function showSuccessMessage() {
        const message = `
            <div class="alert alert-success alert-dismissible fade show mt-3">
                <i class="fas fa-check-circle me-2"></i>
                Report exported successfully! 
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        `;
        $('.export-container .row .col-lg-8').prepend(message);
    }
});
</script>