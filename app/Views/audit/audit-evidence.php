<?php
/**
 * Audit Evidence Page
 * 
 * @var string $title
 * @var array $evidence_items
 * @var array $categories
 */
?>

<?php $page_title = 'Audit Evidence'; ?>
<?php $active_page = 'audit'; ?>

<div class="audit-evidence-container">
    <!-- Header -->
    <div class="row mb-4">
        <div class="col-md-8">
            <h5><i class="fas fa-paperclip me-2 text-primary"></i> Audit Evidence Management</h5>
            <p class="text-muted">Upload and manage evidence for audit findings</p>
        </div>
        <div class="col-md-4 text-end">
            <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#uploadEvidenceModal">
                <i class="fas fa-upload me-2"></i> Upload Evidence
            </button>
        </div>
    </div>
    
    <!-- Filters -->
    <div class="filter-section mb-4">
        <div class="row g-3">
            <div class="col-md-3">
                <select class="form-select" id="filterAudit">
                    <option value="">All Audits</option>
                    <?php foreach ($audits ?? [] as $audit): ?>
                        <option value="<?php echo $audit->id; ?>"><?php echo htmlspecialchars($audit->title); ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="col-md-3">
                <select class="form-select" id="filterStatus">
                    <option value="">All Status</option>
                    <option value="pending">Pending</option>
                    <option value="verified">Verified</option>
                    <option value="rejected">Rejected</option>
                </select>
            </div>
            <div class="col-md-3">
                <select class="form-select" id="filterType">
                    <option value="">All Types</option>
                    <option value="document">Document</option>
                    <option value="image">Image</option>
                    <option value="video">Video</option>
                    <option value="audio">Audio</option>
                </select>
            </div>
            <div class="col-md-3">
                <div class="search-box">
                    <i class="fas fa-search"></i>
                    <input type="text" class="form-control" id="searchEvidence" placeholder="Search evidence...">
                </div>
            </div>
        </div>
    </div>
    
    <!-- Evidence Grid -->
    <div class="row g-4">
        <?php if (!empty($evidence_items)): ?>
            <?php foreach ($evidence_items as $evidence): ?>
                <div class="col-xl-3 col-lg-4 col-md-6">
                    <div class="evidence-card <?php echo $evidence->status; ?>" data-audit="<?php echo $evidence->audit_id; ?>" data-type="<?php echo $evidence->file_type; ?>">
                        <div class="evidence-preview">
                            <?php if ($evidence->file_type === 'image'): ?>
                                <img src="<?php echo UPLOADS_URL; ?>/<?php echo $evidence->file_path; ?>" alt="Evidence">
                            <?php else: ?>
                                <div class="file-icon">
                                    <i class="fas fa-<?php echo $evidence->file_type === 'pdf' ? 'file-pdf' : ($evidence->file_type === 'docx' ? 'file-word' : 'file'); ?>"></i>
                                </div>
                            <?php endif; ?>
                            <div class="evidence-overlay">
                                <button class="btn btn-light btn-sm view-evidence" data-id="<?php echo $evidence->id; ?>">
                                    <i class="fas fa-eye"></i>
                                </button>
                            </div>
                        </div>
                        <div class="evidence-body">
                            <div class="evidence-title"><?php echo htmlspecialchars($evidence->title); ?></div>
                            <div class="evidence-meta">
                                <span class="evidence-audit">
                                    <i class="fas fa-clipboard me-1"></i>
                                    <?php echo htmlspecialchars($evidence->audit_title); ?>
                                </span>
                                <span class="evidence-status">
                                    <span class="badge bg-<?php echo $evidence->status === 'verified' ? 'success' : ($evidence->status === 'pending' ? 'warning' : 'danger'); ?>">
                                        <?php echo ucfirst($evidence->status); ?>
                                    </span>
                                </span>
                            </div>
                            <div class="evidence-footer">
                                <span class="evidence-date">
                                    <i class="far fa-clock me-1"></i>
                                    <?php echo date('d M Y', strtotime($evidence->created_at)); ?>
                                </span>
                                <span class="evidence-user">
                                    <i class="fas fa-user me-1"></i>
                                    <?php echo htmlspecialchars($evidence->uploaded_by_name ?? 'Unknown'); ?>
                                </span>
                            </div>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        <?php else: ?>
            <div class="col-12">
                <div class="empty-state text-center py-5">
                    <i class="fas fa-paperclip fa-3x text-muted mb-3"></i>
                    <h5>No Evidence Found</h5>
                    <p class="text-muted">Upload evidence to support your audit findings</p>
                </div>
            </div>
        <?php endif; ?>
    </div>
</div>

<!-- Upload Evidence Modal -->
<div class="modal fade" id="uploadEvidenceModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title"><i class="fas fa-upload me-2"></i> Upload Evidence</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form method="POST" action="<?php echo BASE_URL; ?>/audit/evidence" enctype="multipart/form-data">
                <div class="modal-body">
                    <input type="hidden" name="csrf_token" value="<?php echo $csrf_token ?? ''; ?>">
                    
                    <div class="form-group mb-3">
                        <label class="form-label">Audit</label>
                        <select class="form-select" name="audit_id" required>
                            <option value="">Select Audit</option>
                            <?php foreach ($audits ?? [] as $audit): ?>
                                <option value="<?php echo $audit->id; ?>"><?php echo htmlspecialchars($audit->title); ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    
                    <div class="form-group mb-3">
                        <label class="form-label">Title</label>
                        <input type="text" class="form-control" name="title" required>
                    </div>
                    
                    <div class="form-group mb-3">
                        <label class="form-label">Description</label>
                        <textarea class="form-control" name="description" rows="3"></textarea>
                    </div>
                    
                    <div class="form-group mb-3">
                        <label class="form-label">File</label>
                        <input type="file" class="form-control" name="evidence_file" required>
                        <small class="text-muted">Max file size: 10MB. Allowed: PDF, DOCX, JPG, PNG</small>
                    </div>
                    
                    <div class="form-group">
                        <label class="form-label">Category</label>
                        <select class="form-select" name="category">
                            <option value="general">General</option>
                            <option value="documentation">Documentation</option>
                            <option value="screenshot">Screenshot</option>
                            <option value="recording">Recording</option>
                        </select>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">Upload</button>
                </div>
            </form>
        </div>
    </div>
</div>

<style>
.audit-evidence-container {
    padding: 0;
}

.filter-section {
    background: #fff;
    padding: 16px 20px;
    border-radius: 12px;
    box-shadow: 0 1px 3px rgba(0,0,0,0.06);
}

.search-box {
    position: relative;
}

.search-box i {
    position: absolute;
    left: 14px;
    top: 50%;
    transform: translateY(-50%);
    color: #94A3B8;
}

.search-box .form-control {
    padding-left: 40px;
    border-radius: 8px;
}

.evidence-card {
    background: #fff;
    border-radius: 12px;
    overflow: hidden;
    box-shadow: 0 1px 3px rgba(0,0,0,0.06);
    transition: all 0.3s;
    height: 100%;
}

.evidence-card:hover {
    box-shadow: 0 4px 12px rgba(0,0,0,0.1);
    transform: translateY(-2px);
}

.evidence-card.pending { border-top: 3px solid #F59E0B; }
.evidence-card.verified { border-top: 3px solid #22C55E; }
.evidence-card.rejected { border-top: 3px solid #EF4444; }

.evidence-preview {
    position: relative;
    height: 160px;
    background: #F1F5F9;
    display: flex;
    align-items: center;
    justify-content: center;
    overflow: hidden;
}

.evidence-preview img {
    width: 100%;
    height: 100%;
    object-fit: cover;
}

.file-icon {
    font-size: 48px;
    color: #94A3B8;
}

.evidence-overlay {
    position: absolute;
    top: 0;
    left: 0;
    right: 0;
    bottom: 0;
    background: rgba(0,0,0,0.4);
    display: flex;
    align-items: center;
    justify-content: center;
    opacity: 0;
    transition: opacity 0.3s;
}

.evidence-card:hover .evidence-overlay {
    opacity: 1;
}

.evidence-body {
    padding: 16px;
}

.evidence-title {
    font-weight: 500;
    color: #1E293B;
    font-size: 14px;
    margin-bottom: 8px;
}

.evidence-meta {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 8px;
}

.evidence-audit {
    font-size: 12px;
    color: #64748B;
}

.evidence-footer {
    display: flex;
    justify-content: space-between;
    font-size: 12px;
    color: #94A3B8;
    padding-top: 8px;
    border-top: 1px solid #F1F5F9;
}

.empty-state i {
    display: block;
}

@media (max-width: 768px) {
    .evidence-preview {
        height: 120px;
    }
}
</style>

<script>
$(document).ready(function() {
    // Filter functionality
    $('#filterAudit, #filterStatus, #filterType').on('change', applyFilters);
    $('#searchEvidence').on('keyup', applyFilters);
    
    function applyFilters() {
        const audit = $('#filterAudit').val();
        const status = $('#filterStatus').val();
        const type = $('#filterType').val();
        const search = $('#searchEvidence').val().toLowerCase();
        
        $('.evidence-card').each(function() {
            const card = $(this);
            let show = true;
            
            if (audit && card.data('audit') != audit) show = false;
            if (status && !card.hasClass(status)) show = false;
            if (type && card.data('type') !== type) show = false;
            if (search) {
                const title = card.find('.evidence-title').text().toLowerCase();
                if (!title.includes(search)) show = false;
            }
            
            card.toggle(show);
        });
    }
});
</script>