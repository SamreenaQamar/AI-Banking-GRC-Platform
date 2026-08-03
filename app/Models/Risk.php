<?php
/**
 * AI Banking GRC Platform - Risk Model
 * 
 * @package    AI-Banking-GRC-Platform
 * @version    1.0.0
 * @author     GRC Platform Team
 * @copyright  2026 AI Banking GRC Platform
 * @license    Proprietary
 * 
 * This model handles:
 * - Risk CRUD operations
 * - Risk scoring calculation
 * - Risk assessment
 * - Risk history tracking
 */

declare(strict_types=1);

namespace App\Models;

use PDO;

class Risk extends BaseModel
{
    /**
     * Table name
     * @var string
     */
    protected string $table = 'risk_register';
    
    /**
     * Fillable fields
     * @var array
     */
    protected array $fillable = [
        'risk_code',
        'title',
        'description',
        'category_id',
        'sub_category',
        'inherent_likelihood',
        'inherent_impact',
        'residual_likelihood',
        'residual_impact',
        'control_description',
        'control_effectiveness',
        'owner_department_id',
        'owner_user_id',
        'status',
        'identification_date',
        'assessment_date',
        'review_date',
        'closure_date',
        'mitigation_plan',
        'mitigation_date'
    ];
    
    /**
     * Constructor
     */
    public function __construct()
    {
        parent::__construct();
    }
    
    /**
     * Calculate risk score
     * 
     * @param int $likelihood
     * @param int $impact
     * @return float
     */
    public function calculateRiskScore(int $likelihood, int $impact): float
    {
        return ($likelihood * $impact / 25) * 100;
    }
    
    /**
     * Get risk level based on score
     * 
     * @param float $score
     * @return string
     */
    public function getRiskLevel(float $score): string
    {
        if ($score >= 80) return RISK_LEVEL_CRITICAL;
        if ($score >= 60) return RISK_LEVEL_HIGH;
        if ($score >= 40) return RISK_LEVEL_MEDIUM;
        return RISK_LEVEL_LOW;
    }
    
    /**
     * Assign owner to risk
     * 
     * @param int $riskId
     * @param int $userId
     * @return bool
     */
    public function assignOwner(int $riskId, int $userId): bool
    {
        return $this->update($riskId, ['owner_user_id' => $userId]);
    }
    
    /**
     * Change risk status
     * 
     * @param int $riskId
     * @param string $status
     * @param string|null $notes
     * @return bool
     */
    public function changeStatus(int $riskId, string $status, ?string $notes = null): bool
    {
        $allowedStatuses = ['identified', 'assessed', 'mitigated', 'monitored', 'closed'];
        
        if (!in_array($status, $allowedStatuses)) {
            return false;
        }
        
        $data = ['status' => $status];
        
        if ($status === 'closed') {
            $data['closure_date'] = date('Y-m-d');
        }
        
        if ($notes) {
            // Add to history
            $this->addHistory($riskId, $status, $notes);
        }
        
        return $this->update($riskId, $data);
    }
    
    /**
     * Add risk history
     * 
     * @param int $riskId
     * @param string $status
     * @param string $notes
     * @return bool
     */
    public function addHistory(int $riskId, string $status, string $notes): bool
    {
        $sql = "INSERT INTO risk_history (risk_id, status, notes, created_by, created_at) 
                VALUES (:risk_id, :status, :notes, :created_by, :created_at)";
        
        $stmt = $this->db->prepare($sql);
        
        return $stmt->execute([
            'risk_id' => $riskId,
            'status' => $status,
            'notes' => $notes,
            'created_by' => $_SESSION['user_id'] ?? null,
            'created_at' => date('Y-m-d H:i:s')
        ]);
    }
    
    /**
     * Get risk history
     * 
     * @param int $riskId
     * @return array
     */
    public function getHistory(int $riskId): array
    {
        $sql = "SELECT h.*, u.username as created_by_username 
                FROM risk_history h 
                LEFT JOIN users u ON u.id = h.created_by 
                WHERE h.risk_id = :risk_id 
                ORDER BY h.created_at DESC";
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute(['risk_id' => $riskId]);
        
        return $stmt->fetchAll(PDO::FETCH_OBJ);
    }
    
    /**
     * Get risk comments
     * 
     * @param int $riskId
     * @return array
     */
    public function getComments(int $riskId): array
    {
        $sql = "SELECT c.*, u.username, u.full_name 
                FROM risk_comments c 
                LEFT JOIN users u ON u.id = c.user_id 
                WHERE c.risk_id = :risk_id 
                ORDER BY c.created_at DESC";
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute(['risk_id' => $riskId]);
        
        return $stmt->fetchAll(PDO::FETCH_OBJ);
    }
    
    /**
     * Add risk comment
     * 
     * @param int $riskId
     * @param string $comment
     * @return bool
     */
    public function addComment(int $riskId, string $comment): bool
    {
        $sql = "INSERT INTO risk_comments (risk_id, user_id, comment, created_at) 
                VALUES (:risk_id, :user_id, :comment, :created_at)";
        
        $stmt = $this->db->prepare($sql);
        
        return $stmt->execute([
            'risk_id' => $riskId,
            'user_id' => $_SESSION['user_id'] ?? null,
            'comment' => $comment,
            'created_at' => date('Y-m-d H:i:s')
        ]);
    }
    
    /**
     * Get risk attachments
     * 
     * @param int $riskId
     * @return array
     */
    public function getAttachments(int $riskId): array
    {
        $sql = "SELECT * FROM risk_attachments 
                WHERE risk_id = :risk_id 
                ORDER BY created_at DESC";
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute(['risk_id' => $riskId]);
        
        return $stmt->fetchAll(PDO::FETCH_OBJ);
    }
    
    /**
     * Get risks by category
     * 
     * @param int $categoryId
     * @return array
     */
    public function getByCategory(int $categoryId): array
    {
        return $this->where(['category_id' => $categoryId]);
    }
    
    /**
     * Get risks by owner
     * 
     * @param int $userId
     * @return array
     */
    public function getByOwner(int $userId): array
    {
        return $this->where(['owner_user_id' => $userId]);
    }
    
    /**
     * Get risks by status
     * 
     * @param string $status
     * @return array
     */
    public function getByStatus(string $status): array
    {
        return $this->where(['status' => $status]);
    }
    
    /**
     * Get risk heatmap data
     * 
     * @return array
     */
    public function getHeatmapData(): array
    {
        $sql = "SELECT 
                    inherent_likelihood,
                    inherent_impact,
                    COUNT(*) as count,
                    AVG(inherent_likelihood * inherent_impact) as avg_score
                FROM {$this->table}
                WHERE deleted_at IS NULL
                GROUP BY inherent_likelihood, inherent_impact
                ORDER BY inherent_likelihood, inherent_impact";
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute();
        
        return $stmt->fetchAll(PDO::FETCH_OBJ);
    }
}