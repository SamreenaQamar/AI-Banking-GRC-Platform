<?php
/**
 * AI Banking GRC Platform - Audit Service
 * 
 * @package    AI-Banking-GRC-Platform
 * @subpackage app/Services
 * @version    1.0.0
 * @author     GRC Platform Team
 * @copyright  2026 AI Banking GRC Platform
 * @license    Proprietary
 * 
 * This service handles audit management business logic:
 * - Audit CRUD operations
 * - Audit findings management
 * - Audit reports
 * - Audit statistics
 * - Audit scheduling
 */

declare(strict_types=1);

namespace App\Services;

use App\Models\AuditPlan;
use App\Models\AuditFinding;
use App\Models\AuditEvidence;
use App\Models\ActivityLog;
use App\Libraries\Logger;
use App\Libraries\Validator;
use App\Libraries\Cache;

class AuditService
{
    /**
     * @var AuditPlan Audit plan model
     */
    private AuditPlan $auditModel;

    /**
     * @var AuditFinding Finding model
     */
    private AuditFinding $findingModel;

    /**
     * @var AuditEvidence Evidence model
     */
    private AuditEvidence $evidenceModel;

    /**
     * @var ActivityLog Activity log model
     */
    private ActivityLog $activityLogModel;

    /**
     * @var Logger Logger instance
     */
    private Logger $logger;

    /**
     * @var Validator Validator instance
     */
    private Validator $validator;

    /**
     * @var Cache Cache instance
     */
    private Cache $cache;

    /**
     * Constructor
     */
    public function __construct()
    {
        $this->auditModel = new AuditPlan();
        $this->findingModel = new AuditFinding();
        $this->evidenceModel = new AuditEvidence();
        $this->activityLogModel = new ActivityLog();
        $this->logger = new Logger();
        $this->validator = new Validator();
        $this->cache = new Cache();
    }

    /**
     * Create a new audit
     * 
     * @param array $data
     * @param int $createdBy
     * @return array
     */
    public function create(array $data, int $createdBy): array
    {
        try {
            $rules = [
                'title' => ['required', 'min:3', 'max:255'],
                'scope_description' => ['required', 'min:10'],
                'audit_type' => ['required', 'in:internal,external,regulatory,forensic,compliance,it,operational,financial'],
                'department_id' => ['required', 'exists:departments,id'],
                'lead_auditor_id' => ['required', 'exists:users,id'],
                'start_date' => ['required', 'date', 'after:today'],
                'end_date' => ['required', 'date', 'after:start_date'],
                'estimated_budget' => ['numeric', 'min:0']
            ];

            if (!$this->validator->validate($data, $rules)) {
                return $this->errorResponse('Validation failed.', 'VALIDATION_ERROR', [
                    'errors' => $this->validator->getErrors()
                ]);
            }

            // Generate reference number
            $referenceNumber = $this->generateReferenceNumber();

            $auditData = [
                'title' => $data['title'],
                'reference_number' => $referenceNumber,
                'audit_type' => $data['audit_type'],
                'audit_frequency' => $data['audit_frequency'] ?? 'annual',
                'scope_description' => $data['scope_description'],
                'department_id' => $data['department_id'],
                'start_date' => $data['start_date'],
                'end_date' => $data['end_date'],
                'lead_auditor_id' => $data['lead_auditor_id'],
                'audit_team' => $data['audit_team'] ?? null,
                'estimated_budget' => $data['estimated_budget'] ?? null,
                'status' => 'planned',
                'created_by' => $createdBy,
                'created_at' => date('Y-m-d H:i:s')
            ];

            $auditId = $this->auditModel->create($auditData);

            if (!$auditId) {
                return $this->errorResponse('Failed to create audit.', 'CREATE_FAILED');
            }

            // Log activity
            $this->activityLogModel->logCreate($createdBy, 'audit', 'audit', $auditId, $auditData);

            $this->logger->info('Audit created', [
                'audit_id' => $auditId,
                'reference_number' => $referenceNumber,
                'created_by' => $createdBy
            ]);

            return $this->successResponse('Audit created successfully.', [
                'audit_id' => $auditId,
                'reference_number' => $referenceNumber
            ]);

        } catch (\Exception $e) {
            $this->logger->error('Create audit error: ' . $e->getMessage());
            return $this->errorResponse('An error occurred creating audit.', 'ERROR');
        }
    }

    /**
     * Update audit
     * 
     * @param int $auditId
     * @param array $data
     * @param int $updatedBy
     * @return array
     */
    public function update(int $auditId, array $data, int $updatedBy): array
    {
        try {
            $audit = $this->auditModel->find($auditId);
            if (!$audit) {
                return $this->errorResponse('Audit not found.', 'AUDIT_NOT_FOUND');
            }

            $rules = [
                'title' => ['required', 'min:3', 'max:255'],
                'scope_description' => ['required', 'min:10'],
                'audit_type' => ['required', 'in:internal,external,regulatory,forensic,compliance,it,operational,financial'],
                'department_id' => ['required', 'exists:departments,id'],
                'lead_auditor_id' => ['required', 'exists:users,id'],
                'start_date' => ['required', 'date'],
                'end_date' => ['required', 'date', 'after:start_date'],
                'status' => ['in:planned,scheduled,in_progress,review,completed,closed,cancelled'],
                'estimated_budget' => ['numeric', 'min:0'],
                'actual_cost' => ['numeric', 'min:0']
            ];

            if (!$this->validator->validate($data, $rules)) {
                return $this->errorResponse('Validation failed.', 'VALIDATION_ERROR', [
                    'errors' => $this->validator->getErrors()
                ]);
            }

            $updateData = [
                'title' => $data['title'],
                'audit_type' => $data['audit_type'],
                'audit_frequency' => $data['audit_frequency'] ?? $audit->audit_frequency,
                'scope_description' => $data['scope_description'],
                'department_id' => $data['department_id'],
                'start_date' => $data['start_date'],
                'end_date' => $data['end_date'],
                'lead_auditor_id' => $data['lead_auditor_id'],
                'audit_team' => $data['audit_team'] ?? null,
                'estimated_budget' => $data['estimated_budget'] ?? null,
                'actual_cost' => $data['actual_cost'] ?? null,
                'status' => $data['status'] ?? $audit->status,
                'updated_at' => date('Y-m-d H:i:s')
            ];

            $result = $this->auditModel->update($auditId, $updateData);

            if (!$result) {
                return $this->errorResponse('Failed to update audit.', 'UPDATE_FAILED');
            }

            // Log activity
            $this->activityLogModel->logChange($updatedBy, 'audit', 'audit', $auditId, (array)$audit, $updateData);

            $this->logger->info('Audit updated', [
                'audit_id' => $auditId,
                'updated_by' => $updatedBy
            ]);

            return $this->successResponse('Audit updated successfully.');

        } catch (\Exception $e) {
            $this->logger->error('Update audit error: ' . $e->getMessage());
            return $this->errorResponse('An error occurred updating audit.', 'ERROR');
        }
    }

    /**
     * Delete audit
     * 
     * @param int $auditId
     * @param int $deletedBy
     * @return array
     */
    public function delete(int $auditId, int $deletedBy): array
    {
        try {
            $audit = $this->auditModel->find($auditId);
            if (!$audit) {
                return $this->errorResponse('Audit not found.', 'AUDIT_NOT_FOUND');
            }

            $result = $this->auditModel->softDelete($auditId);

            if (!$result) {
                return $this->errorResponse('Failed to delete audit.', 'DELETE_FAILED');
            }

            // Log activity
            $this->activityLogModel->logDelete($deletedBy, 'audit', 'audit', $auditId, (array)$audit);

            $this->logger->info('Audit deleted', [
                'audit_id' => $auditId,
                'deleted_by' => $deletedBy
            ]);

            return $this->successResponse('Audit deleted successfully.');

        } catch (\Exception $e) {
            $this->logger->error('Delete audit error: ' . $e->getMessage());
            return $this->errorResponse('An error occurred deleting audit.', 'ERROR');
        }
    }

    /**
     * Get audit by ID
     * 
     * @param int $auditId
     * @return array
     */
    public function find(int $auditId): array
    {
        try {
            $audit = $this->auditModel->find($auditId);
            if (!$audit) {
                return $this->errorResponse('Audit not found.', 'AUDIT_NOT_FOUND');
            }

            $findings = $this->findingModel->getByAuditId($auditId);
            $evidence = $this->evidenceModel->getByAuditId($auditId);

            return $this->successResponse('Audit retrieved.', [
                'audit' => $audit,
                'findings' => $findings,
                'evidence' => $evidence
            ]);

        } catch (\Exception $e) {
            $this->logger->error('Find audit error: ' . $e->getMessage());
            return $this->errorResponse('An error occurred.', 'ERROR');
        }
    }

    /**
     * Get all audits with pagination
     * 
     * @param array $filters
     * @param int $page
     * @param int $perPage
     * @return array
     */
    public function all(array $filters = [], int $page = 1, int $perPage = 15): array
    {
        try {
            $audits = $this->auditModel->getFiltered($filters, $page, $perPage);
            $total = $this->auditModel->countFiltered($filters);

            return $this->successResponse('Audits retrieved.', [
                'audits' => $audits,
                'total' => $total,
                'page' => $page,
                'per_page' => $perPage,
                'total_pages' => ceil($total / $perPage)
            ]);

        } catch (\Exception $e) {
            $this->logger->error('Get all audits error: ' . $e->getMessage());
            return $this->errorResponse('An error occurred.', 'ERROR');
        }
    }

    /**
     * Create audit finding
     * 
     * @param int $auditId
     * @param array $data
     * @param int $userId
     * @return array
     */
    public function createFinding(int $auditId, array $data, int $userId): array
    {
        try {
            $audit = $this->auditModel->find($auditId);
            if (!$audit) {
                return $this->errorResponse('Audit not found.', 'AUDIT_NOT_FOUND');
            }

            $rules = [
                'title' => ['required', 'min:3', 'max:255'],
                'description' => ['required', 'min:10'],
                'severity' => ['required', 'in:critical,high,medium,low'],
                'recommendation' => ['required', 'min:10'],
                'assigned_to' => ['exists:users,id'],
                'finding_date' => ['required', 'date']
            ];

            if (!$this->validator->validate($data, $rules)) {
                return $this->errorResponse('Validation failed.', 'VALIDATION_ERROR', [
                    'errors' => $this->validator->getErrors()
                ]);
            }

            // Generate finding code
            $findingCode = $this->generateFindingCode();

            $findingData = [
                'audit_plan_id' => $auditId,
                'finding_code' => $findingCode,
                'title' => $data['title'],
                'description' => $data['description'],
                'severity' => $data['severity'],
                'impact_description' => $data['impact_description'] ?? null,
                'root_cause' => $data['root_cause'] ?? null,
                'recommendation' => $data['recommendation'],
                'assigned_to' => $data['assigned_to'] ?? null,
                'assigned_by' => $userId,
                'finding_date' => $data['finding_date'],
                'status' => 'open',
                'created_by' => $userId,
                'created_at' => date('Y-m-d H:i:s')
            ];

            $findingId = $this->findingModel->create($findingData);

            if (!$findingId) {
                return $this->errorResponse('Failed to create finding.', 'CREATE_FAILED');
            }

            // Log activity
            $this->activityLogModel->logCreate($userId, 'audit', 'finding', $findingId, $findingData);

            $this->logger->info('Audit finding created', [
                'audit_id' => $auditId,
                'finding_id' => $findingId,
                'finding_code' => $findingCode
            ]);

            return $this->successResponse('Audit finding created successfully.', [
                'finding_id' => $findingId,
                'finding_code' => $findingCode
            ]);

        } catch (\Exception $e) {
            $this->logger->error('Create finding error: ' . $e->getMessage());
            return $this->errorResponse('An error occurred creating finding.', 'ERROR');
        }
    }

    /**
     * Update finding status
     * 
     * @param int $findingId
     * @param string $status
     * @param int $userId
     * @return array
     */
    public function updateFindingStatus(int $findingId, string $status, int $userId): array
    {
        try {
            $finding = $this->findingModel->find($findingId);
            if (!$finding) {
                return $this->errorResponse('Finding not found.', 'FINDING_NOT_FOUND');
            }

            $validStatuses = ['open', 'in_progress', 'resolved', 'verified', 'closed', 'accepted_risk'];
            if (!in_array($status, $validStatuses)) {
                return $this->errorResponse('Invalid status.', 'INVALID_STATUS');
            }

            $updateData = [
                'status' => $status,
                'updated_at' => date('Y-m-d H:i:s')
            ];

            if ($status === 'resolved' || $status === 'closed') {
                $updateData['resolution_date'] = date('Y-m-d');
                $updateData['resolved_by'] = $userId;
            }

            if ($status === 'verified') {
                $updateData['verified_by'] = $userId;
                $updateData['verification_date'] = date('Y-m-d');
            }

            $result = $this->findingModel->update($findingId, $updateData);

            if (!$result) {
                return $this->errorResponse('Failed to update finding status.', 'UPDATE_FAILED');
            }

            // Log activity
            $this->activityLogModel->logAction($userId, 'finding_status', 'audit',
                "Finding {$finding->finding_code} status changed to {$status}");

            $this->logger->info('Finding status updated', [
                'finding_id' => $findingId,
                'status' => $status,
                'user_id' => $userId
            ]);

            return $this->successResponse('Finding status updated successfully.');

        } catch (\Exception $e) {
            $this->logger->error('Update finding status error: ' . $e->getMessage());
            return $this->errorResponse('An error occurred updating finding status.', 'ERROR');
        }
    }

    /**
     * Upload audit evidence
     * 
     * @param int $auditId
     * @param array $file
     * @param string $description
     * @param int $userId
     * @return array
     */
    public function uploadEvidence(int $auditId, array $file, string $description, int $userId): array
    {
        try {
            $audit = $this->auditModel->find($auditId);
            if (!$audit) {
                return $this->errorResponse('Audit not found.', 'AUDIT_NOT_FOUND');
            }

            // Validate file
            $allowedTypes = ['application/pdf', 'image/jpeg', 'image/png', 'application/msword', 
                            'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
                            'application/vnd.ms-excel', 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
                            'video/mp4', 'audio/mpeg', 'application/zip'];
            $maxSize = 20 * 1024 * 1024; // 20MB

            if (!isset($file['error']) || $file['error'] !== UPLOAD_ERR_OK) {
                return $this->errorResponse('File upload error.', 'UPLOAD_ERROR');
            }

            if ($file['size'] > $maxSize) {
                return $this->errorResponse('File size exceeds 20MB limit.', 'FILE_TOO_LARGE');
            }

            $finfo = finfo_open(FILEINFO_MIME_TYPE);
            $mimeType = finfo_file($finfo, $file['tmp_name']);
            finfo_close($finfo);

            if (!in_array($mimeType, $allowedTypes)) {
                return $this->errorResponse('Invalid file type.', 'INVALID_TYPE');
            }

            // Generate filename and save
            $extension = pathinfo($file['name'], PATHINFO_EXTENSION);
            $filename = 'evidence_' . $auditId . '_' . time() . '_' . bin2hex(random_bytes(8)) . '.' . $extension;
            $uploadPath = UPLOADS_PATH . '/audit/evidence/' . $auditId . '/';

            if (!is_dir($uploadPath)) {
                mkdir($uploadPath, 0755, true);
            }

            $targetPath = $uploadPath . $filename;
            move_uploaded_file($file['tmp_name'], $targetPath);

            // Save evidence record
            $evidenceData = [
                'audit_id' => $auditId,
                'file_path' => 'audit/evidence/' . $auditId . '/' . $filename,
                'file_name' => $file['name'],
                'file_type' => $extension,
                'file_size' => $file['size'],
                'description' => $description,
                'type' => $this->getEvidenceType($mimeType),
                'uploaded_by' => $userId,
                'created_at' => date('Y-m-d H:i:s')
            ];

            $evidenceId = $this->evidenceModel->create($evidenceData);

            if (!$evidenceId) {
                return $this->errorResponse('Failed to save evidence record.', 'SAVE_FAILED');
            }

            // Log activity
            $this->activityLogModel->logAction($userId, 'evidence_upload', 'audit',
                "Evidence uploaded for audit {$audit->reference_number}");

            $this->logger->info('Audit evidence uploaded', [
                'audit_id' => $auditId,
                'evidence_id' => $evidenceId,
                'filename' => $file['name']
            ]);

            return $this->successResponse('Evidence uploaded successfully.', [
                'evidence_id' => $evidenceId,
                'filename' => $file['name']
            ]);

        } catch (\Exception $e) {
            $this->logger->error('Upload evidence error: ' . $e->getMessage());
            return $this->errorResponse('An error occurred uploading evidence.', 'ERROR');
        }
    }

    /**
     * Get audit report
     * 
     * @param int $auditId
     * @return array
     */
    public function report(int $auditId): array
    {
        try {
            $audit = $this->auditModel->find($auditId);
            if (!$audit) {
                return $this->errorResponse('Audit not found.', 'AUDIT_NOT_FOUND');
            }

            $findings = $this->findingModel->getByAuditId($auditId);
            $evidence = $this->evidenceModel->getByAuditId($auditId);

            $stats = [
                'total_findings' => count($findings),
                'by_severity' => $this->getFindingSeverityStats($findings),
                'by_status' => $this->getFindingStatusStats($findings),
                'critical_findings' => $this->countFindingsBySeverity($findings, 'critical'),
                'open_findings' => $this->countFindingsByStatus($findings, 'open'),
                'resolved_findings' => $this->countFindingsByStatus($findings, 'resolved')
            ];

            return [
                'success' => true,
                'audit' => $audit,
                'findings' => $findings,
                'evidence' => $evidence,
                'statistics' => $stats,
                'generated_at' => date('Y-m-d H:i:s')
            ];

        } catch (\Exception $e) {
            $this->logger->error('Audit report error: ' . $e->getMessage());
            return $this->errorResponse('Failed to generate audit report.', 'ERROR');
        }
    }

    /**
     * Generate reference number
     * 
     * @return string
     */
    private function generateReferenceNumber(): string
    {
        $year = date('Y');
        $random = str_pad(random_int(1, 9999), 4, '0', STR_PAD_LEFT);
        return 'AUDIT-' . $year . '-' . $random;
    }

    /**
     * Generate finding code
     * 
     * @return string
     */
    private function generateFindingCode(): string
    {
        $year = date('Y');
        $random = str_pad(random_int(1, 9999), 4, '0', STR_PAD_LEFT);
        return 'FIND-' . $year . '-' . $random;
    }

    /**
     * Get evidence type from MIME type
     * 
     * @param string $mimeType
     * @return string
     */
    private function getEvidenceType(string $mimeType): string
    {
        if (strpos($mimeType, 'image/') === 0) return 'screenshot';
        if (strpos($mimeType, 'video/') === 0) return 'video';
        if (strpos($mimeType, 'audio/') === 0) return 'audio';
        if (strpos($mimeType, 'pdf') !== false) return 'document';
        if (strpos($mimeType, 'word') !== false || strpos($mimeType, 'document') !== false) return 'document';
        if (strpos($mimeType, 'excel') !== false || strpos($mimeType, 'spreadsheet') !== false) return 'report';
        return 'other';
    }

    /**
     * Get finding severity statistics
     * 
     * @param array $findings
     * @return array
     */
    private function getFindingSeverityStats(array $findings): array
    {
        $stats = ['critical' => 0, 'high' => 0, 'medium' => 0, 'low' => 0];
        foreach ($findings as $finding) {
            if (isset($stats[$finding->severity])) {
                $stats[$finding->severity]++;
            }
        }
        return $stats;
    }

    /**
     * Get finding status statistics
     * 
     * @param array $findings
     * @return array
     */
    private function getFindingStatusStats(array $findings): array
    {
        $stats = ['open' => 0, 'in_progress' => 0, 'resolved' => 0, 'verified' => 0, 'closed' => 0, 'accepted_risk' => 0];
        foreach ($findings as $finding) {
            if (isset($stats[$finding->status])) {
                $stats[$finding->status]++;
            }
        }
        return $stats;
    }

    /**
     * Count findings by severity
     * 
     * @param array $findings
     * @param string $severity
     * @return int
     */
    private function countFindingsBySeverity(array $findings, string $severity): int
    {
        return count(array_filter($findings, function($f) use ($severity) {
            return $f->severity === $severity;
        }));
    }

    /**
     * Count findings by status
     * 
     * @param array $findings
     * @param string $status
     * @return int
     */
    private function countFindingsByStatus(array $findings, string $status): int
    {
        return count(array_filter($findings, function($f) use ($status) {
            return $f->status === $status;
        }));
    }

    /**
     * Success response
     * 
     * @param string $message
     * @param array $data
     * @return array
     */
    private function successResponse(string $message, array $data = []): array
    {
        return [
            'success' => true,
            'message' => $message,
            'data' => $data
        ];
    }

    /**
     * Error response
     * 
     * @param string $message
     * @param string $code
     * @param array $data
     * @return array
     */
    private function errorResponse(string $message, string $code = 'ERROR', array $data = []): array
    {
        return [
            'success' => false,
            'message' => $message,
            'code' => $code,
            'data' => $data
        ];
    }
}