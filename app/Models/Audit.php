<?php
/**
 * AI Banking GRC Platform - Audit Model
 * 
 * @package    AI-Banking-GRC-Platform
 * @version    1.0.0
 * @author     GRC Platform Team
 * @copyright  2026 AI Banking GRC Platform
 * @license    Proprietary
 * 
 * This model handles:
 * - Audit plan management
 * - Audit finding tracking
 * - Evidence management
 * - Report generation
 */

declare(strict_types=1);

namespace App\Models;

use PDO;

class Audit extends BaseModel
{
    /**
     * Table name
     * @var string
     */
    protected string $table = 'audit_plans';
    
    /**
     * Fillable fields
     * @var array
     */
    protected array $fillable = [
        'title',
        'reference_number',
        'audit_type',
        'audit_frequency',
        'scope_description',
        'department_id',
        'start_date',
        'end_date',
        'lead_auditor_id',
        'audit_team',
        'estimated_budget',
        'actual_cost',
        'status'
    ];
    
    /**
     * Constructor
     */
    public function __construct()
    {
        parent::__construct();
    }
    
    /**
     * Start audit
     * 
     * @param int $auditId
     * @return bool
     */
    public function startAudit(int $auditId): bool
    {
        return $this->update($auditId, [
            'status' => AUDIT_STATUS_IN_PROGRESS,
            'started_at' => date('Y-m-d H:i:s')
        ]);
    }
    
    /**
     * Complete audit
     * 
     * @param int $auditId
     * @param string $summary
     * @return bool
     */
    public function completeAudit(int $auditId, string $summary): bool
    {
        return $this->update($auditId, [
            'status' => AUDIT_STATUS_COMPLETED,
            'completed_at' => date('Y-m-d H:i:s'),
            'summary' => $summary
        ]);
    }
    
    /**
     * Upload evidence for audit
     * 
     * @param int $auditId
     * @param string $filePath
     * @param string $description
     * @param string $type
     * @return int|false
     */
    public function uploadEvidence(int $auditId, string $filePath, string $description, string $type = 'general')
    {
        $sql = "INSERT INTO audit_evidence 
                (audit_id, file_path, description, type, uploaded_by, created_at) 
                VALUES (:audit_id, :file_path, :description, :type, :uploaded_by, :created_at)";
        
        $stmt = $this->db->prepare($sql);
        $result = $stmt->execute([
            'audit_id' => $auditId,
            'file_path' => $filePath,
            'description' => $description,
            'type' => $type,
            'uploaded_by' => $_SESSION['user_id'] ?? null,
            'created_at' => date('Y-m-d H:i:s')
        ]);
        
        return $result ? (int)$this->db->lastInsertId() : false;
    }
    
    /**
     * Get audit findings
     * 
     * @param int $auditId
     * @return array
     */
    public function getFindings(int $auditId): array
    {
        $sql = "SELECT f.*, u.username as assigned_to_username 
                FROM audit_findings f 
                LEFT JOIN users u ON u.id = f.assigned_to 
                WHERE f.audit_plan_id = :audit_id 
                AND f.deleted_at IS NULL 
                ORDER BY f.severity DESC, f.created_at DESC";
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute(['audit_id' => $auditId]);
        
        return $stmt->fetchAll(PDO::FETCH_OBJ);
    }
    
    /**
     * Get audit evidence
     * 
     * @param int $auditId
     * @return array
     */
    public function getEvidence(int $auditId): array
    {
        $sql = "SELECT e.*, u.username 
                FROM audit_evidence e 
                LEFT JOIN users u ON u.id = e.uploaded_by 
                WHERE e.audit_id = :audit_id 
                ORDER BY e.created_at DESC";
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute(['audit_id' => $auditId]);
        
        return $stmt->fetchAll(PDO::FETCH_OBJ);
    }
    
    /**
     * Get audits by type
     * 
     * @param string $type
     * @return array
     */
    public function getByType(string $type): array
    {
        return $this->where(['audit_type' => $type]);
    }
    
    /**
     * Get audits by status
     * 
     * @param string $status
     * @return array
     */
    public function getByStatus(string $status): array
    {
        return $this->where(['status' => $status]);
    }
    
    /**
     * Get upcoming audits
     * 
     * @param int $days
     * @return array
     */
    public function getUpcoming(int $days = 30): array
    {
        $sql = "SELECT * FROM {$this->table} 
                WHERE start_date BETWEEN CURDATE() AND DATE_ADD(CURDATE(), INTERVAL :days DAY) 
                AND status = 'planned' 
                AND deleted_at IS NULL 
                ORDER BY start_date ASC";
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute(['days' => $days]);
        
        return $stmt->fetchAll(PDO::FETCH_OBJ);
    }
}