<?php
/**
 * AI Banking GRC Platform - Policy Model
 * 
 * @package    AI-Banking-GRC-Platform
 * @version    1.0.0
 * @author     GRC Platform Team
 * @copyright  2026 AI Banking GRC Platform
 * @license    Proprietary
 * 
 * This model handles:
 * - Policy CRUD operations
 * - Version management
 * - Approval workflow
 * - Policy acknowledgements
 */

declare(strict_types=1);

namespace App\Models;

use PDO;

class Policy extends BaseModel
{
    /**
     * Table name
     * @var string
     */
    protected string $table = 'policies';
    
    /**
     * Fillable fields
     * @var array
     */
    protected array $fillable = [
        'policy_number',
        'title',
        'category',
        'description',
        'version',
        'effective_date',
        'review_date',
        'expiry_date',
        'approved_by',
        'approval_date',
        'status',
        'document_path',
        'document_type',
        'mandatory',
        'acknowledges_required'
    ];
    
    /**
     * Constructor
     */
    public function __construct()
    {
        parent::__construct();
    }
    
    /**
     * Publish policy
     * 
     * @param int $policyId
     * @return bool
     */
    public function publish(int $policyId): bool
    {
        return $this->update($policyId, [
            'status' => POLICY_STATUS_ACTIVE,
            'effective_date' => date('Y-m-d')
        ]);
    }
    
    /**
     * Approve policy
     * 
     * @param int $policyId
     * @param int $userId
     * @return bool
     */
    public function approve(int $policyId, int $userId): bool
    {
        return $this->update($policyId, [
            'status' => POLICY_STATUS_APPROVED,
            'approved_by' => $userId,
            'approval_date' => date('Y-m-d')
        ]);
    }
    
    /**
     * Archive policy
     * 
     * @param int $policyId
     * @return bool
     */
    public function archive(int $policyId): bool
    {
        return $this->update($policyId, [
            'status' => POLICY_STATUS_ARCHIVED,
            'archived_at' => date('Y-m-d H:i:s')
        ]);
    }
    
    /**
     * Create new version of policy
     * 
     * @param int $policyId
     * @param array $data
     * @return int|false
     */
    public function createVersion(int $policyId, array $data)
    {
        $current = $this->find($policyId);
        
        if (!$current) {
            return false;
        }
        
        // Increment version
        $versionParts = explode('.', $current->version);
        $major = (int)($versionParts[0] ?? 1);
        $minor = (int)($versionParts[1] ?? 0);
        $newVersion = $major . '.' . ($minor + 1);
        
        // Create new version
        $data['policy_number'] = $current->policy_number;
        $data['version'] = $newVersion;
        $data['status'] = POLICY_STATUS_DRAFT;
        $data['created_by'] = $_SESSION['user_id'] ?? null;
        
        return $this->create($data);
    }
    
    /**
     * Get policy versions
     * 
     * @param int $policyId
     * @return array
     */
    public function getVersions(int $policyId): array
    {
        $policy = $this->find($policyId);
        
        if (!$policy) {
            return [];
        }
        
        $sql = "SELECT * FROM {$this->table} 
                WHERE policy_number = :policy_number 
                AND deleted_at IS NULL 
                ORDER BY 
                    CAST(SUBSTRING_INDEX(version, '.', 1) AS UNSIGNED) DESC,
                    CAST(SUBSTRING_INDEX(version, '.', -1) AS UNSIGNED) DESC";
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute(['policy_number' => $policy->policy_number]);
        
        return $stmt->fetchAll(PDO::FETCH_OBJ);
    }
    
    /**
     * Get policy acknowledgements
     * 
     * @param int $policyId
     * @return array
     */
    public function getAcknowledgements(int $policyId): array
    {
        $sql = "SELECT pa.*, u.username, u.full_name 
                FROM policy_acknowledgements pa 
                LEFT JOIN users u ON u.id = pa.user_id 
                WHERE pa.policy_id = :policy_id 
                ORDER BY pa.acknowledged_at DESC";
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute(['policy_id' => $policyId]);
        
        return $stmt->fetchAll(PDO::FETCH_OBJ);
    }
    
    /**
     * Check if user acknowledged policy
     * 
     * @param int $policyId
     * @param int $userId
     * @return bool
     */
    public function hasAcknowledged(int $policyId, int $userId): bool
    {
        $sql = "SELECT COUNT(*) FROM policy_acknowledgements 
                WHERE policy_id = :policy_id AND user_id = :user_id";
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute([
            'policy_id' => $policyId,
            'user_id' => $userId
        ]);
        
        return (int)$stmt->fetchColumn() > 0;
    }
}