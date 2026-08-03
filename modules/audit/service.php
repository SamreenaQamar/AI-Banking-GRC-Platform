<?php
/**
 * Audit Module - Service Layer
 * 
 * @package    AI-Banking-GRC-Platform
 * @subpackage modules/audit
 * @version    1.0.0
 * @author     GRC Platform Team
 * @copyright  2026 AI Banking GRC Platform
 * @license    Proprietary
 * 
 * This service handles audit business logic:
 * - Audit plan management
 * - Scheduling and assignment
 * - Finding tracking
 * - Evidence management
 * - Report generation
 */

declare(strict_types=1);

namespace Modules\Audit\Services;

use App\Models\AuditPlan;
use App\Models\AuditFinding;
use App\Models\AuditEvidence;
use App\Models\ActivityLog;
use App\Helpers\Auth;
use App\Helpers\Database;
use Exception;
use PDO;

class AuditService
{
    /**
     * @var PDO
     */
    private PDO $db;
    
    /**
     * @var AuditPlan
     */
    private AuditPlan $planModel;
    
    /**
     * @var AuditFinding
     */
    private AuditFinding $findingModel;
    
    /**
     * @var AuditEvidence
     */
    private AuditEvidence $evidenceModel;
    
    /**
     * @var ActivityLog
     */
    private ActivityLog $activityLogModel;
    
    /**
     * Constructor
     */
    public function __construct()
    {
        $this->db = Database::getInstance()->getConnection();
        $this->planModel = new AuditPlan();
        $this->findingModel = new AuditFinding();
        $this->evidenceModel = new AuditEvidence();
        $this->activityLogModel = new ActivityLog();
    }
    
    /**
     * Get audit statistics
     * 
     * @return array
     */
    public function getAuditStats(): array
    {
        $totalPlans = $this->planModel->countAll();
        $planned = $this->planModel->countByStatus('planned');
        $scheduled = $this->planModel->countByStatus('scheduled');
        $inProgress = $this->planModel->countByStatus('in_progress');
        $completed = $this->planModel->countByStatus('completed');
        $closed = $this->planModel->countByStatus('closed');
        $cancelled = $this->planModel->countByStatus('cancelled');
        
        $totalFindings = $this->findingModel->countAll();
        $openFindings = $this->findingModel->countByStatus('open');
        $inProgressFindings = $this->findingModel->countByStatus('in_progress');
        $resolved = $this->findingModel->countByStatus('resolved');
        $closedFindings = $this->findingModel->countByStatus('closed');
        
        $criticalFindings = $this->findingModel->countBySeverity('critical');
        $highFindings = $this->findingModel->countBySeverity('high');
        
        $resolutionRate = $totalFindings > 0 ? round(($resolved / $totalFindings) * 100, 2) : 0;
        $completionRate = $totalPlans > 0 ? round((($completed + $closed) / $totalPlans) * 100, 2) : 0;
        
        return [
            'total_audits' => $totalPlans,
            'planned' => $planned,
            'scheduled' => $scheduled,
            'in_progress' => $inProgress,
            'completed' => $completed,
            'closed' => $closed,
            'cancelled' => $cancelled,
            'total_findings' => $totalFindings,
            'open_findings' => $openFindings,
            'in_progress_findings' => $inProgressFindings,
            'resolved' => $resolved,
            'closed_findings' => $closedFindings,
            'critical_findings' => $criticalFindings,
            'high_findings' => $highFindings,
            'resolution_rate' => $resolutionRate,
            'completion_rate' => $completionRate
        ];
    }
    
    /**
     * Get audit dashboard data
     * 
     * @param int $userId
     * @return array
     */
    public function getDashboardData(int $userId): array
    {
        $stats = $this->getAuditStats();
        $upcomingAudits = $this->getUpcomingAudits($userId);
        $inProgressAudits = $this->getInProgressAudits($userId);
        $recentFindings = $this->getRecentFindings($userId);
        $trendData = $this->getAuditTrend();
        $findingsBySeverity = $this->getFindingsBySeverity();
        $findingsByStatus = $this->getFindingsByStatus();
        $auditTypes = $this->getAuditTypesDistribution();
        
        return [
            'stats' => $stats,
            'upcoming_audits' => $upcomingAudits,
            'in_progress_audits' => $inProgressAudits,
            'recent_findings' => $recentFindings,
            'trend_data' => $trendData,
            'findings_by_severity' => $findingsBySeverity,
            'findings_by_status' => $findingsByStatus,
            'audit_types' => $auditTypes
        ];
    }
    
    /**
     * Get upcoming audits
     * 
     * @param int $userId
     * @param int $limit
     * @return array
     */
    public function getUpcomingAudits(int $userId, int $limit = 5): array
    {
        $sql = "SELECT a.*, 
                       CONCAT(u.first_name, ' ', u.last_name) as lead_auditor_name,
                       d.name as department_name
                FROM audit_plans a
                LEFT JOIN users u ON u.id = a.lead_auditor_id
                LEFT JOIN departments d ON d.id = a.department_id
                WHERE a.deleted_at IS NULL
                AND a.status IN ('planned', 'scheduled')
                AND a.start_date >= CURDATE()
                ORDER BY a.start_date ASC
                LIMIT :limit";
        
        $stmt = $this->db->prepare($sql);
        $stmt->bindParam('limit', $limit, PDO::PARAM_INT);
        $stmt->execute();
        
        return $stmt->fetchAll(PDO::FETCH_OBJ);
    }
    
    /**
     * Get in-progress audits
     * 
     * @param int $userId
     * @param int $limit
     * @return array
     */
    public function getInProgressAudits(int $userId, int $limit = 5): array
    {
        $sql = "SELECT a.*, 
                       CONCAT(u.first_name, ' ', u.last_name) as lead_auditor_name,
                       d.name as department_name
                FROM audit_plans a
                LEFT JOIN users u ON u.id = a.lead_auditor_id
                LEFT JOIN departments d ON d.id = a.department_id
                WHERE a.deleted_at IS NULL
                AND a.status = 'in_progress'
                ORDER BY a.start_date ASC
                LIMIT :limit";
        
        $stmt = $this->db->prepare($sql);
        $stmt->bindParam('limit', $limit, PDO::PARAM_INT);
        $stmt->execute();
        
        return $stmt->fetchAll(PDO::FETCH_OBJ);
    }
    
    /**
     * Get recent findings
     * 
     * @param int $userId
     * @param int $limit
     * @return array
     */
    public function getRecentFindings(int $userId, int $limit = 5): array
    {
        $sql = "SELECT f.*, 
                       a.title as audit_title,
                       CONCAT(u.first_name, ' ', u.last_name) as assigned_to_name
                FROM audit_findings f
                LEFT JOIN audit_plans a ON a.id = f.audit_plan_id
                LEFT JOIN users u ON u.id = f.assigned_to
                WHERE f.deleted_at IS NULL
                ORDER BY f.created_at DESC
                LIMIT :limit";
        
        $stmt = $this->db->prepare($sql);
        $stmt->bindParam('limit', $limit, PDO::PARAM_INT);
        $stmt->execute();
        
        return $stmt->fetchAll(PDO::FETCH_OBJ);
    }
    
    /**
     * Get audit trend
     * 
     * @param int $months
     * @return array
     */
    public function getAuditTrend(int $months = 12): array
    {
        $data = [];
        
        for ($i = $months - 1; $i >= 0; $i--) {
            $startDate = date('Y-m-01', strtotime("-$i months"));
            $endDate = date('Y-m-t', strtotime($startDate));
            
            $sql = "SELECT 
                        COUNT(*) as total,
                        SUM(CASE WHEN status IN ('completed', 'closed') THEN 1 ELSE 0 END) as completed
                    FROM audit_plans
                    WHERE created_at BETWEEN :start_date AND :end_date
                    AND deleted_at IS NULL";
            
            $stmt = $this->db->prepare($sql);
            $stmt->execute([
                'start_date' => $startDate . ' 00:00:00',
                'end_date' => $endDate . ' 23:59:59'
            ]);
            $row = $stmt->fetch(PDO::FETCH_OBJ);
            
            $data[] = [
                'month' => date('M Y', strtotime($startDate)),
                'total' => (int)($row->total ?? 0),
                'completed' => (int)($row->completed ?? 0),
                'completion_rate' => $row && $row->total > 0 
                    ? round(($row->completed / $row->total) * 100, 2) 
                    : 0
            ];
        }
        
        return $data;
    }
    
    /**
     * Get findings by severity
     * 
     * @return array
     */
    public function getFindingsBySeverity(): array
    {
        $sql = "SELECT severity, COUNT(*) as count 
                FROM audit_findings 
                WHERE deleted_at IS NULL 
                GROUP BY severity";
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute();
        $results = $stmt->fetchAll(PDO::FETCH_OBJ);
        
        $severities = [
            'critical' => 0,
            'high' => 0,
            'medium' => 0,
            'low' => 0
        ];
        
        foreach ($results as $row) {
            $severities[$row->severity] = (int)$row->count;
        }
        
        return $severities;
    }
    
    /**
     * Get findings by status
     * 
     * @return array
     */
    public function getFindingsByStatus(): array
    {
        $sql = "SELECT status, COUNT(*) as count 
                FROM audit_findings 
                WHERE deleted_at IS NULL 
                GROUP BY status";
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute();
        $results = $stmt->fetchAll(PDO::FETCH_OBJ);
        
        $statuses = [
            'open' => 0,
            'in_progress' => 0,
            'resolved' => 0,
            'verified' => 0,
            'closed' => 0,
            'accepted_risk' => 0
        ];
        
        foreach ($results as $row) {
            $statuses[$row->status] = (int)$row->count;
        }
        
        return $statuses;
    }
    
    /**
     * Get audit types distribution
     * 
     * @return array
     */
    public function getAuditTypesDistribution(): array
    {
        $sql = "SELECT audit_type, COUNT(*) as count 
                FROM audit_plans 
                WHERE deleted_at IS NULL 
                GROUP BY audit_type";
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute();
        $results = $stmt->fetchAll(PDO::FETCH_OBJ);
        
        $distribution = [];
        foreach ($results as $row) {
            $distribution[$row->audit_type] = (int)$row->count;
        }
        
        return $distribution;
    }
    
    /**
     * Upload audit evidence
     * 
     * @param int $auditId
     * @param array $file
     * @param string $description
     * @param int $userId
     * @param string $type
     * @return int|false
     */
    public function uploadEvidence(int $auditId, array $file, string $description, int $userId, string $type = 'document')
    {
        // Validate file
        $maxSize = audit_setting('max_evidence_size') * 1024 * 1024;
        $allowedTypes = audit_setting('allowed_evidence_types', ['pdf', 'doc', 'docx']);
        
        if ($file['size'] > $maxSize) {
            throw new Exception('File size exceeds maximum allowed.');
        }
        
        $extension = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
        if (!in_array($extension, $allowedTypes)) {
            throw new Exception('File type not allowed.');
        }
        
        // Generate unique filename
        $filename = 'evidence_' . $auditId . '_' . date('Ymd_His') . '_' . uniqid() . '.' . $extension;
        $uploadPath = UPLOADS_PATH . '/audit/evidence/' . $auditId . '/';
        
        if (!is_dir($uploadPath)) {
            mkdir($uploadPath, 0755, true);
        }
        
        $targetPath = $uploadPath . $filename;
        
        if (!move_uploaded_file($file['tmp_name'], $targetPath)) {
            throw new Exception('Failed to upload file.');
        }
        
        $evidenceData = [
            'audit_id' => $auditId,
            'file_path' => 'audit/evidence/' . $auditId . '/' . $filename,
            'file_name' => $file['name'],
            'file_type' => $extension,
            'file_size' => $file['size'],
            'description' => $description,
            'type' => $type,
            'uploaded_by' => $userId,
            'created_at' => date('Y-m-d H:i:s')
        ];
        
        return $this->evidenceModel->create($evidenceData);
    }
    
    /**
     * Create audit finding
     * 
     * @param int $auditId
     * @param array $data
     * @param int $userId
     * @return int|false
     */
    public function createFinding(int $auditId, array $data, int $userId)
    {
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
            'finding_date' => $data['finding_date'] ?? date('Y-m-d'),
            'status' => 'open',
            'created_by' => $userId,
            'created_at' => date('Y-m-d H:i:s')
        ];
        
        $findingId = $this->findingModel->create($findingData);
        
        if (!$findingId) {
            throw new Exception('Failed to create finding.');
        }
        
        return $findingId;
    }
    
    /**
     * Update finding status
     * 
     * @param int $findingId
     * @param string $status
     * @param int $userId
     * @param string $notes
     * @return bool
     */
    public function updateFindingStatus(int $findingId, string $status, int $userId, string $notes = ''): bool
    {
        $finding = $this->findingModel->find($findingId);
        
        if (!$finding) {
            throw new Exception('Finding not found.');
        }
        
        $validStatuses = array_keys(AUDIT_FINDING_STATUS);
        if (!in_array($status, $validStatuses)) {
            throw new Exception('Invalid status.');
        }
        
        $data = ['status' => $status];
        
        if ($status === 'resolved' || $status === 'closed') {
            $data['resolution_date'] = date('Y-m-d');
            $data['resolved_by'] = $userId;
        }
        
        if ($status === 'verified') {
            $data['verified_by'] = $userId;
            $data['verification_date'] = date('Y-m-d');
        }
        
        if ($notes) {
            $data['resolution_notes'] = $notes;
        }
        
        $result = $this->findingModel->update($findingId, $data);
        
        if (!$result) {
            throw new Exception('Failed to update finding status.');
        }
        
        return true;
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
     * Get AI audit recommendations
     * 
     * @param int $auditId
     * @param int $limit
     * @return array
     */
    public function getAIRecommendations(int $auditId, int $limit = 5): array
    {
        // This would integrate with AI service
        // For now, return sample recommendations based on findings
        
        $findings = $this->findingModel->getByAuditId($auditId);
        $recommendations = [];
        
        foreach ($findings as $finding) {
            if ($finding->severity === 'critical' || $finding->severity === 'high') {
                $recommendations[] = [
                    'id' => uniqid(),
                    'title' => 'Immediate action: ' . $finding->title,
                    'description' => 'Critical finding requires immediate attention. Recommended action: ' . $finding->recommendation,
                    'priority' => $finding->severity,
                    'finding_id' => $finding->id,
                    'deadline' => date('Y-m-d', strtotime('+' . ($finding->severity === 'critical' ? 7 : 14) . ' days'))
                ];
            }
        }
        
        return array_slice($recommendations, 0, $limit);
    }
}