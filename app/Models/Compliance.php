<?php
/**
 * AI Banking GRC Platform - Compliance Model
 * 
 * @package    AI-Banking-GRC-Platform
 * @version    1.0.0
 * @author     GRC Platform Team
 * @copyright  2026 AI Banking GRC Platform
 * @license    Proprietary
 * 
 * This model handles:
 * - Compliance task management
 * - Control tracking
 * - Evidence management
 * - Task assignment
 */

declare(strict_types=1);

namespace App\Models;

use PDO;

class Compliance extends BaseModel
{
    /**
     * Table name
     * @var string
     */
    protected string $table = 'compliance_tasks';
    
    /**
     * Fillable fields
     * @var array
     */
    protected array $fillable = [
        'title',
        'description',
        'reference_number',
        'category_id',
        'framework_id',
        'department_id',
        'priority',
        'status',
        'due_date',
        'completed_date',
        'reminder_date',
        'compliance_score',
        'evidence_required',
        'auto_review',
        'assigned_to',
        'assigned_by',
        'reviewed_by',
        'review_date'
    ];
    
    /**
     * Constructor
     */
    public function __construct()
    {
        parent::__construct();
    }
    
    /**
     * Mark compliance task as completed
     * 
     * @param int $taskId
     * @param string $notes
     * @return bool
     */
    public function markCompleted(int $taskId, string $notes = ''): bool
    {
        return $this->update($taskId, [
            'status' => COMPLIANCE_STATUS_COMPLETED,
            'completed_date' => date('Y-m-d'),
            'notes' => $notes
        ]);
    }
    
    /**
     * Generate evidence for compliance task
     * 
     * @param int $taskId
     * @param string $filePath
     * @param string $description
     * @return int|false
     */
    public function generateEvidence(int $taskId, string $filePath, string $description)
    {
        $sql = "INSERT INTO compliance_evidence 
                (task_id, file_path, description, uploaded_by, created_at) 
                VALUES (:task_id, :file_path, :description, :uploaded_by, :created_at)";
        
        $stmt = $this->db->prepare($sql);
        $result = $stmt->execute([
            'task_id' => $taskId,
            'file_path' => $filePath,
            'description' => $description,
            'uploaded_by' => $_SESSION['user_id'] ?? null,
            'created_at' => date('Y-m-d H:i:s')
        ]);
        
        return $result ? (int)$this->db->lastInsertId() : false;
    }
    
    /**
     * Assign task to user
     * 
     * @param int $taskId
     * @param int $userId
     * @return bool
     */
    public function assignTask(int $taskId, int $userId): bool
    {
        return $this->update($taskId, [
            'assigned_to' => $userId,
            'assigned_by' => $_SESSION['user_id'] ?? null,
            'assigned_at' => date('Y-m-d H:i:s')
        ]);
    }
    
    /**
     * Get task history
     * 
     * @param int $taskId
     * @return array
     */
    public function getHistory(int $taskId): array
    {
        $sql = "SELECT h.*, u.username 
                FROM compliance_status_history h 
                LEFT JOIN users u ON u.id = h.changed_by 
                WHERE h.task_id = :task_id 
                ORDER BY h.changed_at DESC";
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute(['task_id' => $taskId]);
        
        return $stmt->fetchAll(PDO::FETCH_OBJ);
    }
    
    /**
     * Get evidence for task
     * 
     * @param int $taskId
     * @return array
     */
    public function getEvidence(int $taskId): array
    {
        $sql = "SELECT e.*, u.username 
                FROM compliance_evidence e 
                LEFT JOIN users u ON u.id = e.uploaded_by 
                WHERE e.task_id = :task_id 
                ORDER BY e.created_at DESC";
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute(['task_id' => $taskId]);
        
        return $stmt->fetchAll(PDO::FETCH_OBJ);
    }
    
    /**
     * Get tasks by status
     * 
     * @param string $status
     * @return array
     */
    public function getByStatus(string $status): array
    {
        return $this->where(['status' => $status]);
    }
    
    /**
     * Get tasks by priority
     * 
     * @param string $priority
     * @return array
     */
    public function getByPriority(string $priority): array
    {
        return $this->where(['priority' => $priority]);
    }
    
    /**
     * Get overdue tasks
     * 
     * @return array
     */
    public function getOverdue(): array
    {
        $sql = "SELECT * FROM {$this->table} 
                WHERE due_date < CURDATE() 
                AND status NOT IN ('completed', 'cancelled') 
                AND deleted_at IS NULL";
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute();
        
        return $stmt->fetchAll(PDO::FETCH_OBJ);
    }
    
    /**
     * Get upcoming tasks
     * 
     * @param int $days
     * @return array
     */
    public function getUpcoming(int $days = 7): array
    {
        $sql = "SELECT * FROM {$this->table} 
                WHERE due_date BETWEEN CURDATE() AND DATE_ADD(CURDATE(), INTERVAL :days DAY) 
                AND status NOT IN ('completed', 'cancelled') 
                AND deleted_at IS NULL 
                ORDER BY due_date ASC";
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute(['days' => $days]);
        
        return $stmt->fetchAll(PDO::FETCH_OBJ);
    }
}