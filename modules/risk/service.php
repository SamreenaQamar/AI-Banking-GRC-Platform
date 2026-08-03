<?php
/**
 * Risk Module - Service Layer
 * 
 * @package    AI-Banking-GRC-Platform
 * @subpackage modules/risk
 * @version    1.0.0
 * @author     GRC Platform Team
 * @copyright  2026 AI Banking GRC Platform
 * @license    Proprietary
 * 
 * This service handles risk business logic:
 * - Risk scoring and calculation
 * - Risk matrix generation
 * - Risk heatmap data
 * - Risk trend analysis
 * - AI risk recommendations
 * - Basel III compliance analysis
 */

declare(strict_types=1);

namespace Modules\Risk\Services;

use App\Models\RiskRegister;
use App\Models\RiskAssessment;
use App\Models\RiskCategory;
use App\Models\ActivityLog;
use App\Helpers\Auth;
use App\Helpers\Database;
use Exception;
use PDO;

class RiskService
{
    /**
     * @var PDO
     */
    private PDO $db;
    
    /**
     * @var RiskRegister
     */
    private RiskRegister $riskModel;
    
    /**
     * @var RiskAssessment
     */
    private RiskAssessment $assessmentModel;
    
    /**
     * @var RiskCategory
     */
    private RiskCategory $categoryModel;
    
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
        $this->riskModel = new RiskRegister();
        $this->assessmentModel = new RiskAssessment();
        $this->categoryModel = new RiskCategory();
        $this->activityLogModel = new ActivityLog();
    }
    
    /**
     * Calculate risk score
     * 
     * @param int $likelihood
     * @param int $impact
     * @param float $velocity
     * @param float $persistence
     * @return float
     */
    public function calculateRiskScore(int $likelihood, int $impact, float $velocity = 1, float $persistence = 1): float
    {
        // Base score from likelihood and impact
        $baseScore = ($likelihood * $impact / 25) * 100;
        
        // Adjust for velocity (speed of occurrence)
        $velocityFactor = $velocity / 5;
        
        // Adjust for persistence (duration of impact)
        $persistenceFactor = $persistence / 5;
        
        // Final score with adjustments
        $adjustedScore = $baseScore * (1 + ($velocityFactor + $persistenceFactor) * 0.1);
        
        return round(min($adjustedScore, 100), 2);
    }
    
    /**
     * Calculate inherent risk score
     * 
     * @param int $likelihood
     * @param int $impact
     * @return float
     */
    public function calculateInherentRisk(int $likelihood, int $impact): float
    {
        return ($likelihood * $impact / 25) * 100;
    }
    
    /**
     * Calculate residual risk score
     * 
     * @param int $likelihood
     * @param int $impact
     * @param float $controlEffectiveness
     * @return float
     */
    public function calculateResidualRisk(int $likelihood, int $impact, float $controlEffectiveness = 0.5): float
    {
        $inherentScore = $this->calculateInherentRisk($likelihood, $impact);
        return round($inherentScore * (1 - $controlEffectiveness), 2);
    }
    
    /**
     * Get risk statistics
     * 
     * @return array
     */
    public function getRiskStats(): array
    {
        $total = $this->riskModel->countAll();
        $critical = $this->riskModel->countByLevel('critical');
        $high = $this->riskModel->countByLevel('high');
        $medium = $this->riskModel->countByLevel('medium');
        $low = $this->riskModel->countByLevel('low');
        $veryLow = $this->riskModel->countByLevel('very_low');
        
        $identified = $this->riskModel->countByStatus('identified');
        $assessed = $this->riskModel->countByStatus('assessed');
        $mitigated = $this->riskModel->countByStatus('mitigated');
        $monitoring = $this->riskModel->countByStatus('monitoring');
        $closed = $this->riskModel->countByStatus('closed');
        
        $avgScore = $this->riskModel->getAverageRiskScore();
        $mitigationRate = $total > 0 ? round(($mitigated / $total) * 100, 2) : 0;
        
        return [
            'total' => $total,
            'critical' => $critical,
            'high' => $high,
            'medium' => $medium,
            'low' => $low,
            'very_low' => $veryLow,
            'identified' => $identified,
            'assessed' => $assessed,
            'mitigated' => $mitigated,
            'monitoring' => $monitoring,
            'closed' => $closed,
            'avg_score' => round($avgScore, 2),
            'mitigation_rate' => $mitigationRate
        ];
    }
    
    /**
     * Get risk dashboard data
     * 
     * @param int $userId
     * @return array
     */
    public function getDashboardData(int $userId): array
    {
        $stats = $this->getRiskStats();
        $recentRisks = $this->getRecentRisks($userId);
        $heatmapData = $this->getHeatmapData();
        $riskTrend = $this->getRiskTrend();
        $baselMetrics = $this->getBaselIIIMetrics();
        $highRisks = $this->getHighRisks($userId);
        $pendingReviews = $this->getPendingReviews($userId);
        
        return [
            'stats' => $stats,
            'recent_risks' => $recentRisks,
            'heatmap_data' => $heatmapData,
            'risk_trend' => $riskTrend,
            'basel_metrics' => $baselMetrics,
            'high_risks' => $highRisks,
            'pending_reviews' => $pendingReviews
        ];
    }
    
    /**
     * Get recent risks
     * 
     * @param int $userId
     * @param int $limit
     * @return array
     */
    public function getRecentRisks(int $userId, int $limit = 10): array
    {
        $sql = "SELECT r.*, 
                       c.name as category_name,
                       d.name as department_name,
                       CONCAT(u.first_name, ' ', u.last_name) as owner_name
                FROM risk_register r
                LEFT JOIN risk_categories c ON c.id = r.category_id
                LEFT JOIN departments d ON d.id = r.owner_department_id
                LEFT JOIN users u ON u.id = r.owner_user_id
                WHERE r.deleted_at IS NULL
                ORDER BY r.created_at DESC
                LIMIT :limit";
        
        $stmt = $this->db->prepare($sql);
        $stmt->bindParam('limit', $limit, PDO::PARAM_INT);
        $stmt->execute();
        
        return $stmt->fetchAll(PDO::FETCH_OBJ);
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
                    AVG(inherent_risk_score) as avg_score
                FROM risk_register
                WHERE deleted_at IS NULL
                GROUP BY inherent_likelihood, inherent_impact
                ORDER BY inherent_likelihood, inherent_impact";
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute();
        $results = $stmt->fetchAll(PDO::FETCH_OBJ);
        
        $heatmap = [];
        for ($i = 1; $i <= 5; $i++) {
            for ($j = 1; $j <= 5; $j++) {
                $heatmap[$i][$j] = [
                    'count' => 0,
                    'avg_score' => 0,
                    'level' => get_risk_level_from_matrix($i, $j)
                ];
            }
        }
        
        foreach ($results as $row) {
            $heatmap[$row->inherent_likelihood][$row->inherent_impact] = [
                'count' => (int)$row->count,
                'avg_score' => round((float)$row->avg_score, 2),
                'level' => get_risk_level_from_matrix(
                    (int)$row->inherent_likelihood,
                    (int)$row->inherent_impact
                )
            ];
        }
        
        return $heatmap;
    }
    
    /**
     * Get risk trend
     * 
     * @param int $months
     * @return array
     */
    public function getRiskTrend(int $months = 12): array
    {
        $data = [];
        
        for ($i = $months - 1; $i >= 0; $i--) {
            $startDate = date('Y-m-01', strtotime("-$i months"));
            $endDate = date('Y-m-t', strtotime($startDate));
            
            $sql = "SELECT 
                        COUNT(*) as total,
                        AVG(inherent_risk_score) as avg_score,
                        SUM(CASE WHEN inherent_risk_score >= 80 THEN 1 ELSE 0 END) as critical
                    FROM risk_register
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
                'avg_score' => round((float)($row->avg_score ?? 0), 2),
                'critical' => (int)($row->critical ?? 0)
            ];
        }
        
        return $data;
    }
    
    /**
     * Get Basel III metrics
     * 
     * @return array
     */
    public function getBaselIIIMetrics(): array
    {
        // This would calculate from actual data
        // For now, return sample metrics
        return [
            'cet1_ratio' => 12.5,
            'tier1_ratio' => 14.2,
            'car_ratio' => 16.8,
            'leverage_ratio' => 5.2,
            'liquidity_coverage_ratio' => 145,
            'net_stable_funding_ratio' => 112,
            'status' => 'compliant'
        ];
    }
    
    /**
     * Get high risks
     * 
     * @param int $userId
     * @param int $limit
     * @return array
     */
    public function getHighRisks(int $userId, int $limit = 5): array
    {
        $sql = "SELECT r.*, 
                       c.name as category_name,
                       CONCAT(u.first_name, ' ', u.last_name) as owner_name
                FROM risk_register r
                LEFT JOIN risk_categories c ON c.id = r.category_id
                LEFT JOIN users u ON u.id = r.owner_user_id
                WHERE r.deleted_at IS NULL
                AND r.inherent_risk_score >= 60
                AND r.status NOT IN ('mitigated', 'closed')
                ORDER BY r.inherent_risk_score DESC
                LIMIT :limit";
        
        $stmt = $this->db->prepare($sql);
        $stmt->bindParam('limit', $limit, PDO::PARAM_INT);
        $stmt->execute();
        
        return $stmt->fetchAll(PDO::FETCH_OBJ);
    }
    
    /**
     * Get pending reviews
     * 
     * @param int $userId
     * @return array
     */
    public function getPendingReviews(int $userId): array
    {
        $sql = "SELECT r.*, 
                       c.name as category_name,
                       CONCAT(u.first_name, ' ', u.last_name) as owner_name
                FROM risk_register r
                LEFT JOIN risk_categories c ON c.id = r.category_id
                LEFT JOIN users u ON u.id = r.owner_user_id
                WHERE r.deleted_at IS NULL
                AND r.status = 'review'
                AND (r.owner_user_id = :user_id OR r.created_by = :user_id)
                ORDER BY r.updated_at DESC";
        
        $stmt = $this->db->prepare($sql);
        $stmt->bindParam('user_id', $userId, PDO::PARAM_INT);
        $stmt->execute();
        
        return $stmt->fetchAll(PDO::FETCH_OBJ);
    }
    
    /**
     * Get risk assessment history
     * 
     * @param int $riskId
     * @return array
     */
    public function getAssessmentHistory(int $riskId): array
    {
        $sql = "SELECT a.*, 
                       CONCAT(u.first_name, ' ', u.last_name) as assessor_name
                FROM risk_assessments a
                LEFT JOIN users u ON u.id = a.assessor_id
                WHERE a.risk_id = :risk_id
                ORDER BY a.assessment_date DESC";
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute(['risk_id' => $riskId]);
        
        return $stmt->fetchAll(PDO::FETCH_OBJ);
    }
    
    /**
     * Get AI risk recommendations
     * 
     * @param int $userId
     * @param int $limit
     * @return array
     */
    public function getAIRecommendations(int $userId, int $limit = 5): array
    {
        // This would integrate with AI service
        // For now, return sample recommendations based on risk data
        
        $highRisks = $this->getHighRisks($userId, 10);
        $recommendations = [];
        
        foreach ($highRisks as $risk) {
            if ($risk->inherent_risk_score >= 80) {
                $recommendations[] = [
                    'id' => uniqid(),
                    'title' => 'Immediate action required for ' . $risk->title,
                    'description' => 'Critical risk identified. Implement immediate mitigation measures.',
                    'priority' => 'critical',
                    'risk_id' => $risk->id,
                    'risk_title' => $risk->title,
                    'suggested_action' => 'Escalate to senior management and implement immediate controls'
                ];
            } elseif ($risk->inherent_risk_score >= 60) {
                $recommendations[] = [
                    'id' => uniqid(),
                    'title' => 'High priority mitigation for ' . $risk->title,
                    'description' => 'High risk requires prompt mitigation planning.',
                    'priority' => 'high',
                    'risk_id' => $risk->id,
                    'risk_title' => $risk->title,
                    'suggested_action' => 'Develop mitigation plan within 30 days'
                ];
            }
        }
        
        // Sort by priority and limit
        usort($recommendations, function($a, $b) {
            $priorities = ['critical' => 0, 'high' => 1, 'medium' => 2, 'low' => 3];
            return $priorities[$a['priority']] - $priorities[$b['priority']];
        });
        
        return array_slice($recommendations, 0, $limit);
    }
    
    /**
     * Perform risk assessment
     * 
     * @param int $riskId
     * @param array $data
     * @param int $userId
     * @return int|false
     */
    public function performAssessment(int $riskId, array $data, int $userId)
    {
        // Validate input
        $likelihood = (int)($data['likelihood_score'] ?? 0);
        $impact = (int)($data['impact_score'] ?? 0);
        $velocity = (int)($data['velocity_score'] ?? 3);
        $persistence = (int)($data['persistence_score'] ?? 3);
        
        if ($likelihood < 1 || $likelihood > 5 || $impact < 1 || $impact > 5) {
            throw new Exception('Invalid likelihood or impact scores.');
        }
        
        // Calculate risk scores
        $inherentScore = $this->calculateRiskScore($likelihood, $impact, $velocity, $persistence);
        $riskLevel = get_risk_level($inherentScore);
        
        // Create assessment record
        $assessmentData = [
            'risk_id' => $riskId,
            'assessment_date' => date('Y-m-d'),
            'assessor_id' => $userId,
            'likelihood_score' => $likelihood,
            'impact_score' => $impact,
            'velocity_score' => $velocity,
            'persistence_score' => $persistence,
            'inherent_risk_score' => $inherentScore,
            'mitigation_plans' => $data['mitigation_plans'] ?? null,
            'recommendations' => $data['recommendations'] ?? null,
            'action_required' => $data['action_required'] ?? false,
            'action_deadline' => $data['action_deadline'] ?? null,
            'created_by' => $userId,
            'created_at' => date('Y-m-d H:i:s')
        ];
        
        $assessmentId = $this->assessmentModel->create($assessmentData);
        
        if (!$assessmentId) {
            throw new Exception('Failed to create risk assessment.');
        }
        
        // Update risk register
        $this->riskModel->update($riskId, [
            'inherent_likelihood' => $likelihood,
            'inherent_impact' => $impact,
            'inherent_risk_score' => $inherentScore,
            'risk_level' => $riskLevel,
            'status' => 'assessed',
            'assessment_date' => date('Y-m-d')
        ]);
        
        return $assessmentId;
    }
    
    /**
     * Create mitigation plan
     * 
     * @param int $riskId
     * @param array $data
     * @param int $userId
     * @return bool
     */
    public function createMitigationPlan(int $riskId, array $data, int $userId): bool
    {
        $risk = $this->riskModel->find($riskId);
        
        if (!$risk) {
            throw new Exception('Risk not found.');
        }
        
        $mitigationData = [
            'mitigation_plan' => $data['mitigation_plan'],
            'mitigation_date' => $data['mitigation_date'] ?? date('Y-m-d'),
            'status' => 'mitigating',
            'updated_at' => date('Y-m-d H:i:s')
        ];
        
        $result = $this->riskModel->update($riskId, $mitigationData);
        
        if (!$result) {
            throw new Exception('Failed to create mitigation plan.');
        }
        
        // Add history
        $this->addRiskHistory($riskId, 'mitigation_plan', $data['mitigation_plan'], $userId);
        
        return true;
    }
    
    /**
     * Add risk history
     * 
     * @param int $riskId
     * @param string $action
     * @param string $details
     * @param int $userId
     * @return bool
     */
    public function addRiskHistory(int $riskId, string $action, string $details, int $userId): bool
    {
        $sql = "INSERT INTO risk_history (risk_id, action, details, user_id, created_at) 
                VALUES (:risk_id, :action, :details, :user_id, :created_at)";
        
        $stmt = $this->db->prepare($sql);
        
        return $stmt->execute([
            'risk_id' => $riskId,
            'action' => $action,
            'details' => $details,
            'user_id' => $userId,
            'created_at' => date('Y-m-d H:i:s')
        ]);
    }
}