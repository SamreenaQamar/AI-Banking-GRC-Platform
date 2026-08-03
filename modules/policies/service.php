<?php
/**
 * Policies Module - Service Layer
 * 
 * @package    AI-Banking-GRC-Platform
 * @subpackage modules/policies
 * @version    1.0.0
 * @author     GRC Platform Team
 * @copyright  2026 AI Banking GRC Platform
 * @license    Proprietary
 * 
 * This service handles policy business logic:
 * - Policy CRUD operations
 * - Version control
 * - Approval workflow
 * - Policy library management
 * - AI policy generation
 */

declare(strict_types=1);

namespace Modules\Policies\Services;

use App\Models\Policy;
use App\Models\PolicyAcknowledgement;
use App\Models\ActivityLog;
use App\Helpers\Auth;
use App\Helpers\Database;
use Exception;
use PDO;

class PolicyService
{
    /**
     * @var PDO
     */
    private PDO $db;
    
    /**
     * @var Policy
     */
    private Policy $policyModel;
    
    /**
     * @var PolicyAcknowledgement
     */
    private PolicyAcknowledgement $acknowledgementModel;
    
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
        $this->policyModel = new Policy();
        $this->acknowledgementModel = new PolicyAcknowledgement();
        $this->activityLogModel = new ActivityLog();
    }
    
    /**
     * Get policy statistics
     * 
     * @return array
     */
    public function getPolicyStats(): array
    {
        $total = $this->policyModel->countAll();
        $active = $this->policyModel->countByStatus('active');
        $draft = $this->policyModel->countByStatus('draft');
        $review = $this->policyModel->countByStatus('review');
        $approved = $this->policyModel->countByStatus('approved');
        $archived = $this->policyModel->countByStatus('archived');
        $expired = $this->policyModel->countByStatus('expired');
        
        $byCategory = $this->policyModel->countByCategory();
        $totalAcknowledgements = $this->acknowledgementModel->countAll();
        $acknowledgementRate = $total > 0 ? round(($totalAcknowledgements / $total) * 100, 2) : 0;
        
        return [
            'total' => $total,
            'active' => $active,
            'draft' => $draft,
            'review' => $review,
            'approved' => $approved,
            'archived' => $archived,
            'expired' => $expired,
            'by_category' => $byCategory,
            'total_acknowledgements' => $totalAcknowledgements,
            'acknowledgement_rate' => $acknowledgementRate
        ];
    }
    
    /**
     * Get policy dashboard data
     * 
     * @param int $userId
     * @return array
     */
    public function getDashboardData(int $userId): array
    {
        $stats = $this->getPolicyStats();
        $recentPolicies = $this->getRecentPolicies(5);
        $pendingApprovals = $this->getPendingApprovals($userId);
        $expiringPolicies = $this->getExpiringPolicies();
        $acknowledgements = $this->getRecentAcknowledgements($userId);
        
        return [
            'stats' => $stats,
            'recent_policies' => $recentPolicies,
            'pending_approvals' => $pendingApprovals,
            'expiring_policies' => $expiringPolicies,
            'acknowledgements' => $acknowledgements
        ];
    }
    
    /**
     * Get recent policies
     * 
     * @param int $limit
     * @return array
     */
    public function getRecentPolicies(int $limit = 5): array
    {
        $sql = "SELECT p.*, 
                       CONCAT(u.first_name, ' ', u.last_name) as owner_name
                FROM policies p
                LEFT JOIN users u ON u.id = p.created_by
                WHERE p.deleted_at IS NULL
                ORDER BY p.created_at DESC
                LIMIT :limit";
        
        $stmt = $this->db->prepare($sql);
        $stmt->bindParam('limit', $limit, PDO::PARAM_INT);
        $stmt->execute();
        
        return $stmt->fetchAll(PDO::FETCH_OBJ);
    }
    
    /**
     * Get pending approvals
     * 
     * @param int $userId
     * @return array
     */
    public function getPendingApprovals(int $userId): array
    {
        $sql = "SELECT p.*, 
                       CONCAT(u.first_name, ' ', u.last_name) as owner_name
                FROM policies p
                LEFT JOIN users u ON u.id = p.created_by
                WHERE p.status = 'review'
                AND p.deleted_at IS NULL
                ORDER BY p.created_at ASC
                LIMIT 10";
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute();
        
        return $stmt->fetchAll(PDO::FETCH_OBJ);
    }
    
    /**
     * Get expiring policies
     * 
     * @param int $days
     * @return array
     */
    public function getExpiringPolicies(int $days = 30): array
    {
        $sql = "SELECT p.*, 
                       CONCAT(u.first_name, ' ', u.last_name) as owner_name,
                       DATEDIFF(p.expiry_date, CURDATE()) as days_until_expiry
                FROM policies p
                LEFT JOIN users u ON u.id = p.created_by
                WHERE p.status = 'active'
                AND p.expiry_date IS NOT NULL
                AND p.expiry_date BETWEEN CURDATE() AND DATE_ADD(CURDATE(), INTERVAL :days DAY)
                AND p.deleted_at IS NULL
                ORDER BY p.expiry_date ASC";
        
        $stmt = $this->db->prepare($sql);
        $stmt->bindParam('days', $days, PDO::PARAM_INT);
        $stmt->execute();
        
        return $stmt->fetchAll(PDO::FETCH_OBJ);
    }
    
    /**
     * Get recent acknowledgements
     * 
     * @param int $userId
     * @param int $limit
     * @return array
     */
    public function getRecentAcknowledgements(int $userId, int $limit = 5): array
    {
        $sql = "SELECT a.*, 
                       p.title as policy_title,
                       CONCAT(u.first_name, ' ', u.last_name) as acknowledged_by_name
                FROM policy_acknowledgements a
                LEFT JOIN policies p ON p.id = a.policy_id
                LEFT JOIN users u ON u.id = a.user_id
                ORDER BY a.acknowledged_at DESC
                LIMIT :limit";
        
        $stmt = $this->db->prepare($sql);
        $stmt->bindParam('limit', $limit, PDO::PARAM_INT);
        $stmt->execute();
        
        return $stmt->fetchAll(PDO::FETCH_OBJ);
    }
    
    /**
     * Create new policy version
     * 
     * @param int $policyId
     * @param array $data
     * @param int $userId
     * @return int|false
     */
    public function createVersion(int $policyId, array $data, int $userId)
    {
        $current = $this->policyModel->find($policyId);
        
        if (!$current) {
            throw new Exception('Policy not found.');
        }
        
        // Increment version
        $versionParts = explode('.', $current->version);
        $major = (int)($versionParts[0] ?? 1);
        $minor = (int)($versionParts[1] ?? 0);
        $newVersion = $major . '.' . ($minor + 1);
        
        $policyData = [
            'policy_number' => $current->policy_number,
            'title' => $data['title'] ?? $current->title,
            'category' => $data['category'] ?? $current->category,
            'description' => $data['description'] ?? $current->description,
            'version' => $newVersion,
            'status' => 'draft',
            'effective_date' => $data['effective_date'] ?? null,
            'review_date' => $data['review_date'] ?? null,
            'expiry_date' => $data['expiry_date'] ?? null,
            'mandatory' => $data['mandatory'] ?? $current->mandatory,
            'acknowledges_required' => $data['acknowledges_required'] ?? $current->acknowledges_required,
            'created_by' => $userId,
            'created_at' => date('Y-m-d H:i:s')
        ];
        
        if (isset($data['document'])) {
            $policyData['document_path'] = $data['document'];
            $policyData['document_type'] = pathinfo($data['document'], PATHINFO_EXTENSION);
        }
        
        return $this->policyModel->create($policyData);
    }
    
    /**
     * Approve policy
     * 
     * @param int $policyId
     * @param int $userId
     * @return bool
     */
    public function approvePolicy(int $policyId, int $userId): bool
    {
        $policy = $this->policyModel->find($policyId);
        
        if (!$policy) {
            throw new Exception('Policy not found.');
        }
        
        if ($policy->status !== 'review') {
            throw new Exception('Policy must be under review to approve.');
        }
        
        return $this->policyModel->update($policyId, [
            'status' => 'approved',
            'approved_by' => $userId,
            'approval_date' => date('Y-m-d')
        ]);
    }
    
    /**
     * Publish policy
     * 
     * @param int $policyId
     * @param int $userId
     * @return bool
     */
    public function publishPolicy(int $policyId, int $userId): bool
    {
        $policy = $this->policyModel->find($policyId);
        
        if (!$policy) {
            throw new Exception('Policy not found.');
        }
        
        if ($policy->status !== 'approved') {
            throw new Exception('Policy must be approved before publishing.');
        }
        
        return $this->policyModel->update($policyId, [
            'status' => 'active',
            'effective_date' => date('Y-m-d')
        ]);
    }
    
    /**
     * Archive policy
     * 
     * @param int $policyId
     * @return bool
     */
    public function archivePolicy(int $policyId): bool
    {
        $policy = $this->policyModel->find($policyId);
        
        if (!$policy) {
            throw new Exception('Policy not found.');
        }
        
        return $this->policyModel->update($policyId, [
            'status' => 'archived'
        ]);
    }
    
    /**
     * Acknowledge policy
     * 
     * @param int $policyId
     * @param int $userId
     * @return bool
     */
    public function acknowledgePolicy(int $policyId, int $userId): bool
    {
        // Check if already acknowledged
        if ($this->acknowledgementModel->hasAcknowledged($policyId, $userId)) {
            throw new Exception('Policy already acknowledged.');
        }
        
        $ackData = [
            'policy_id' => $policyId,
            'user_id' => $userId,
            'acknowledged_at' => date('Y-m-d H:i:s'),
            'ip_address' => $_SERVER['REMOTE_ADDR'] ?? null,
            'user_agent' => $_SERVER['HTTP_USER_AGENT'] ?? null
        ];
        
        return (bool)$this->acknowledgementModel->create($ackData);
    }
    
    /**
     * Generate AI policy
     * 
     * @param array $data
     * @return array
     */
    public function generateAIPolicy(array $data): array
    {
        // This would integrate with AI service
        // For now, return sample generated policy
        
        $frameworks = [
            'iso27001' => 'ISO 27001:2022',
            'nist' => 'NIST CSF',
            'sbp' => 'SBP Regulations',
            'basel' => 'Basel III'
        ];
        
        $framework = $frameworks[$data['framework']] ?? 'Framework';
        $policyName = $data['policy_name'] ?? 'Policy Document';
        $type = $data['policy_type'] ?? 'Policy';
        
        return [
            'title' => $policyName,
            'content' => $this->generatePolicyContent($policyName, $type, $framework),
            'version' => '1.0',
            'category' => $data['category'] ?? 'governance',
            'suggestions' => [
                'Review and customize the content',
                'Add specific procedures for your organization',
                'Include references to applicable regulations',
                'Define roles and responsibilities'
            ]
        ];
    }
    
    /**
     * Generate policy content
     * 
     * @param string $policyName
     * @param string $type
     * @param string $framework
     * @return string
     */
    private function generatePolicyContent(string $policyName, string $type, string $framework): string
    {
        return <<<EOT
<h1>{$policyName}</h1>

<h2>1. Purpose</h2>
<p>This {$type} Policy establishes the framework and requirements for {$type} management within the organization. It is designed to ensure compliance with {$framework} and other applicable regulations.</p>

<h2>2. Scope</h2>
<p>This policy applies to all employees, contractors, and third-party vendors who have access to organizational systems and data.</p>

<h2>3. Policy Statement</h2>
<p>The organization is committed to maintaining a robust {$type} posture that protects assets, ensures business continuity, and complies with regulatory requirements.</p>

<h2>4. Key Requirements</h2>
<ul>
    <li>All personnel must comply with this policy</li>
    <li>Regular reviews and updates will be conducted</li>
    <li>Violations may result in disciplinary action</li>
    <li>All incidents must be reported immediately</li>
    <li>Training will be provided to all employees</li>
</ul>

<h2>5. Roles and Responsibilities</h2>
<ul>
    <li><strong>Policy Owner:</strong> Responsible for policy maintenance and updates</li>
    <li><strong>Compliance Team:</strong> Monitors compliance with policy requirements</li>
    <li><strong>All Employees:</strong> Must adhere to policy requirements</li>
</ul>

<h2>6. Compliance and Enforcement</h2>
<p>Compliance with this policy is mandatory. Non-compliance will be addressed through the organization's disciplinary process.</p>

<h2>7. Review and Updates</h2>
<p>This policy will be reviewed annually or when significant changes occur in the regulatory environment.</p>

<h2>8. Related Documents</h2>
<ul>
    <li>{$framework} Compliance Checklist</li>
    <li>Implementation Guidelines</li>
    <li>Training Materials</li>
</ul>
EOT;
    }
}