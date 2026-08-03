<?php
/**
 * Dashboard Module - Service Layer
 * 
 * @package    AI-Banking-GRC-Platform
 * @subpackage modules/dashboard
 * @version    1.0.0
 * @author     GRC Platform Team
 * @copyright  2026 AI Banking GRC Platform
 * @license    Proprietary
 * 
 * This service handles dashboard business logic:
 * - Statistics aggregation
 * - Chart data generation
 * - Analytics processing
 * - Report generation
 */

declare(strict_types=1);

namespace Modules\Dashboard\Services;

use App\Models\User;
use App\Models\RiskRegister;
use App\Models\ComplianceTask;
use App\Models\AuditPlan;
use App\Models\AuditFinding;
use App\Models\Policy;
use App\Models\SbpCircular;
use App\Models\Notification;
use App\Models\ActivityLog;
use App\Helpers\Auth;
use App\Helpers\Database;
use Exception;
use PDO;

class DashboardService
{
    /**
     * @var PDO
     */
    private PDO $db;
    
    /**
     * @var User
     */
    private User $userModel;
    
    /**
     * @var RiskRegister
     */
    private RiskRegister $riskModel;
    
    /**
     * @var ComplianceTask
     */
    private ComplianceTask $complianceModel;
    
    /**
     * @var AuditPlan
     */
    private AuditPlan $auditModel;
    
    /**
     * @var AuditFinding
     */
    private AuditFinding $findingModel;
    
    /**
     * @var Policy
     */
    private Policy $policyModel;
    
    /**
     * @var SbpCircular
     */
    private SbpCircular $sbpModel;
    
    /**
     * @var Notification
     */
    private Notification $notificationModel;
    
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
        $this->userModel = new User();
        $this->riskModel = new RiskRegister();
        $this->complianceModel = new ComplianceTask();
        $this->auditModel = new AuditPlan();
        $this->findingModel = new AuditFinding();
        $this->policyModel = new Policy();
        $this->sbpModel = new SbpCircular();
        $this->notificationModel = new Notification();
        $this->activityLogModel = new ActivityLog();
    }
    
    /**
     * Get dashboard statistics
     * 
     * @param int $userId
     * @return array
     */
    public function getDashboardStats(int $userId): array
    {
        return [
            'compliance' => $this->getComplianceStats(),
            'risk' => $this->getRiskStats(),
            'audit' => $this->getAuditStats(),
            'sbp' => $this->getSBPStats(),
            'user' => $this->getUserStats(),
            'tasks' => $this->getTaskStats($userId),
            'notifications' => $this->getNotificationStats($userId)
        ];
    }
    
    /**
     * Get compliance statistics
     * 
     * @return array
     */
    private function getComplianceStats(): array
    {
        $total = $this->complianceModel->countAll();
        $completed = $this->complianceModel->countByStatus('completed');
        $inProgress = $this->complianceModel->countByStatus('in_progress');
        $overdue = $this->complianceModel->countOverdue();
        
        $completionRate = $total > 0 ? round(($completed / $total) * 100, 2) : 0;
        
        return [
            'total' => $total,
            'completed' => $completed,
            'in_progress' => $inProgress,
            'overdue' => $overdue,
            'completion_rate' => $completionRate
        ];
    }
    
    /**
     * Get risk statistics
     * 
     * @return array
     */
    private function getRiskStats(): array
    {
        $total = $this->riskModel->countAll();
        $critical = $this->riskModel->countByLevel('critical');
        $high = $this->riskModel->countByLevel('high');
        $medium = $this->riskModel->countByLevel('medium');
        $low = $this->riskModel->countByLevel('low');
        $open = $this->riskModel->countByStatus('identified');
        $mitigated = $this->riskModel->countByStatus('mitigated');
        
        $avgScore = $this->riskModel->getAverageRiskScore();
        
        return [
            'total' => $total,
            'critical' => $critical,
            'high' => $high,
            'medium' => $medium,
            'low' => $low,
            'open' => $open,
            'mitigated' => $mitigated,
            'avg_score' => round($avgScore, 2),
            'mitigation_rate' => $total > 0 ? round(($mitigated / $total) * 100, 2) : 0
        ];
    }
    
    /**
     * Get audit statistics
     * 
     * @return array
     */
    private function getAuditStats(): array
    {
        $totalAudits = $this->auditModel->countAll();
        $inProgress = $this->auditModel->countByStatus('in_progress');
        $completed = $this->auditModel->countByStatus('completed');
        $planned = $this->auditModel->countByStatus('planned');
        
        $totalFindings = $this->findingModel->countAll();
        $openFindings = $this->findingModel->countByStatus('open');
        $resolved = $this->findingModel->countByStatus('resolved');
        $criticalFindings = $this->findingModel->countBySeverity('critical');
        
        $resolutionRate = $totalFindings > 0 ? round(($resolved / $totalFindings) * 100, 2) : 0;
        
        return [
            'total_audits' => $totalAudits,
            'in_progress' => $inProgress,
            'completed' => $completed,
            'planned' => $planned,
            'total_findings' => $totalFindings,
            'open_findings' => $openFindings,
            'resolved' => $resolved,
            'critical_findings' => $criticalFindings,
            'resolution_rate' => $resolutionRate
        ];
    }
    
    /**
     * Get SBP statistics
     * 
     * @return array
     */
    private function getSBPStats(): array
    {
        $total = $this->sbpModel->countAll();
        $active = $this->sbpModel->countByStatus('active');
        $pending = $this->sbpModel->countByStatus('pending');
        $implemented = $this->sbpModel->countByStatus('implemented');
        $withdrawn = $this->sbpModel->countByStatus('withdrawn');
        
        $complianceRate = $total > 0 ? round(($implemented / $total) * 100, 2) : 0;
        
        return [
            'total' => $total,
            'active' => $active,
            'pending' => $pending,
            'implemented' => $implemented,
            'withdrawn' => $withdrawn,
            'compliance_rate' => $complianceRate
        ];
    }
    
    /**
     * Get user statistics
     * 
     * @return array
     */
    private function getUserStats(): array
    {
        $total = $this->userModel->countAll();
        $active = $this->userModel->countByStatus('active');
        $pending = $this->userModel->countByStatus('pending');
        $suspended = $this->userModel->countByStatus('suspended');
        
        $todayActive = $this->userModel->countActiveToday();
        
        return [
            'total' => $total,
            'active' => $active,
            'pending' => $pending,
            'suspended' => $suspended,
            'today_active' => $todayActive
        ];
    }
    
    /**
     * Get task statistics
     * 
     * @param int $userId
     * @return array
     */
    private function getTaskStats(int $userId): array
    {
        $pending = $this->complianceModel->countUserTasks($userId, 'pending');
        $inProgress = $this->complianceModel->countUserTasks($userId, 'in_progress');
        $completed = $this->complianceModel->countUserTasks($userId, 'completed');
        $overdue = $this->complianceModel->countUserOverdueTasks($userId);
        
        return [
            'pending' => $pending,
            'in_progress' => $inProgress,
            'completed' => $completed,
            'overdue' => $overdue,
            'total' => $pending + $inProgress + $completed
        ];
    }
    
    /**
     * Get notification statistics
     * 
     * @param int $userId
     * @return array
     */
    private function getNotificationStats(int $userId): array
    {
        $total = $this->notificationModel->countUserNotifications($userId);
        $unread = $this->notificationModel->countUnread($userId);
        $read = $total - $unread;
        
        return [
            'total' => $total,
            'unread' => $unread,
            'read' => $read
        ];
    }
    
    /**
     * Get chart data
     * 
     * @param string $period
     * @return array
     */
    public function getChartData(string $period = 'month'): array
    {
        return [
            'compliance_trend' => $this->getComplianceTrend($period),
            'risk_distribution' => $this->getRiskDistribution(),
            'audit_status' => $this->getAuditStatus(),
            'risk_heatmap' => $this->getRiskHeatmapData(),
            'sbp_compliance' => $this->getSBPCompliance(),
            'user_activity' => $this->getUserActivity($period)
        ];
    }
    
    /**
     * Get compliance trend data
     * 
     * @param string $period
     * @return array
     */
    private function getComplianceTrend(string $period): array
    {
        $months = $this->getPeriodMonths($period);
        $data = [];
        
        foreach ($months as $month) {
            $start = $month['start'];
            $end = $month['end'];
            
            $sql = "SELECT 
                        COUNT(*) as total,
                        SUM(CASE WHEN status = 'completed' THEN 1 ELSE 0 END) as completed
                    FROM compliance_tasks
                    WHERE created_at BETWEEN :start AND :end
                    AND deleted_at IS NULL";
            
            $stmt = $this->db->prepare($sql);
            $stmt->execute(['start' => $start, 'end' => $end]);
            $row = $stmt->fetch(PDO::FETCH_OBJ);
            
            $completionRate = $row && $row->total > 0 
                ? round(($row->completed / $row->total) * 100, 2) 
                : 0;
            
            $data[] = [
                'month' => date('M Y', strtotime($month['start'])),
                'total' => (int)($row->total ?? 0),
                'completed' => (int)($row->completed ?? 0),
                'completion_rate' => $completionRate
            ];
        }
        
        return $data;
    }
    
    /**
     * Get risk distribution data
     * 
     * @return array
     */
    private function getRiskDistribution(): array
    {
        $sql = "SELECT 
                    CASE 
                        WHEN inherent_risk_score >= 80 THEN 'critical'
                        WHEN inherent_risk_score >= 60 THEN 'high'
                        WHEN inherent_risk_score >= 40 THEN 'medium'
                        ELSE 'low'
                    END as risk_level,
                    COUNT(*) as count
                FROM risk_register
                WHERE deleted_at IS NULL
                GROUP BY risk_level";
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute();
        $results = $stmt->fetchAll(PDO::FETCH_OBJ);
        
        $distribution = [
            'critical' => 0,
            'high' => 0,
            'medium' => 0,
            'low' => 0
        ];
        
        foreach ($results as $row) {
            $distribution[$row->risk_level] = (int)$row->count;
        }
        
        return $distribution;
    }
    
    /**
     * Get audit status data
     * 
     * @return array
     */
    private function getAuditStatus(): array
    {
        $sql = "SELECT status, COUNT(*) as count 
                FROM audit_plans 
                WHERE deleted_at IS NULL 
                GROUP BY status";
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute();
        $results = $stmt->fetchAll(PDO::FETCH_OBJ);
        
        $statuses = [
            'planned' => 0,
            'in_progress' => 0,
            'completed' => 0,
            'cancelled' => 0
        ];
        
        foreach ($results as $row) {
            $statuses[$row->status] = (int)$row->count;
        }
        
        return $statuses;
    }
    
    /**
     * Get risk heatmap data
     * 
     * @return array
     */
    private function getRiskHeatmapData(): array
    {
        $sql = "SELECT 
                    inherent_likelihood,
                    inherent_impact,
                    COUNT(*) as count
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
                $heatmap[$i][$j] = 0;
            }
        }
        
        foreach ($results as $row) {
            $heatmap[$row->inherent_likelihood][$row->inherent_impact] = (int)$row->count;
        }
        
        return $heatmap;
    }
    
    /**
     * Get SBP compliance data
     * 
     * @return array
     */
    private function getSBPCompliance(): array
    {
        $sql = "SELECT 
                    category,
                    COUNT(*) as total,
                    SUM(CASE WHEN status = 'implemented' THEN 1 ELSE 0 END) as implemented
                FROM sbp_circulars
                WHERE deleted_at IS NULL
                GROUP BY category";
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute();
        $results = $stmt->fetchAll(PDO::FETCH_OBJ);
        
        $compliance = [];
        foreach ($results as $row) {
            $compliance[$row->category] = [
                'total' => (int)$row->total,
                'implemented' => (int)$row->implemented,
                'compliance_rate' => $row->total > 0 
                    ? round(($row->implemented / $row->total) * 100, 2) 
                    : 0
            ];
        }
        
        return $compliance;
    }
    
    /**
     * Get user activity data
     * 
     * @param string $period
     * @return array
     */
    private function getUserActivity(string $period): array
    {
        $months = $this->getPeriodMonths($period);
        $data = [];
        
        foreach ($months as $month) {
            $start = $month['start'];
            $end = $month['end'];
            
            $sql = "SELECT COUNT(*) as count 
                    FROM activity_logs 
                    WHERE created_at BETWEEN :start AND :end";
            
            $stmt = $this->db->prepare($sql);
            $stmt->execute(['start' => $start, 'end' => $end]);
            $row = $stmt->fetch(PDO::FETCH_OBJ);
            
            $data[] = [
                'month' => date('M Y', strtotime($month['start'])),
                'count' => (int)($row->count ?? 0)
            ];
        }
        
        return $data;
    }
    
    /**
     * Get period months
     * 
     * @param string $period
     * @return array
     */
    private function getPeriodMonths(string $period): array
    {
        $months = [];
        $count = $period === 'year' ? 12 : 6;
        
        for ($i = $count - 1; $i >= 0; $i--) {
            $date = date('Y-m-d', strtotime("-$i months"));
            $start = date('Y-m-01', strtotime($date));
            $end = date('Y-m-t', strtotime($date));
            $months[] = [
                'start' => $start . ' 00:00:00',
                'end' => $end . ' 23:59:59'
            ];
        }
        
        return $months;
    }
    
    /**
     * Get recent activities
     * 
     * @param int $limit
     * @param int $userId
     * @return array
     */
    public function getRecentActivities(int $limit = 10, int $userId = null): array
    {
        $sql = "SELECT al.*, u.username, u.full_name 
                FROM activity_logs al
                LEFT JOIN users u ON u.id = al.user_id
                WHERE 1=1";
        
        if ($userId) {
            $sql .= " AND al.user_id = :user_id";
        }
        
        $sql .= " ORDER BY al.created_at DESC LIMIT :limit";
        
        $stmt = $this->db->prepare($sql);
        
        if ($userId) {
            $stmt->bindParam('user_id', $userId, PDO::PARAM_INT);
        }
        $stmt->bindParam('limit', $limit, PDO::PARAM_INT);
        $stmt->execute();
        
        return $stmt->fetchAll(PDO::FETCH_OBJ);
    }
    
    /**
     * Get notifications for dashboard
     * 
     * @param int $userId
     * @param int $limit
     * @return array
     */
    public function getNotifications(int $userId, int $limit = 5): array
    {
        $sql = "SELECT * FROM notifications 
                WHERE user_id = :user_id 
                AND (expires_at IS NULL OR expires_at > NOW())
                ORDER BY is_read ASC, created_at DESC 
                LIMIT :limit";
        
        $stmt = $this->db->prepare($sql);
        $stmt->bindParam('user_id', $userId, PDO::PARAM_INT);
        $stmt->bindParam('limit', $limit, PDO::PARAM_INT);
        $stmt->execute();
        
        return $stmt->fetchAll(PDO::FETCH_OBJ);
    }
    
    /**
     * Get AI insights
     * 
     * @param int $userId
     * @return array
     */
    public function getAIInsights(int $userId): array
    {
        // This would connect to AI service
        // For now, return placeholder insights
        return [
            [
                'type' => 'compliance',
                'title' => 'Compliance Improvement Opportunity',
                'description' => '3 compliance tasks are approaching deadline',
                'priority' => 'high'
            ],
            [
                'type' => 'risk',
                'title' => 'Risk Trend Alert',
                'description' => 'Risk score increased by 5% this month',
                'priority' => 'medium'
            ],
            [
                'type' => 'audit',
                'title' => 'Audit Resolution Progress',
                'description' => '78% of findings resolved, 4 critical remaining',
                'priority' => 'high'
            ],
            [
                'type' => 'sbp',
                'title' => 'New SBP Circular',
                'description' => '2 new circulars require implementation by next month',
                'priority' => 'medium'
            ]
        ];
    }
    
    /**
     * Get quick actions based on role
     * 
     * @param string $role
     * @return array
     */
    public function getQuickActions(string $role): array
    {
        $actions = [];
        
        switch ($role) {
            case 'super_admin':
            case 'admin':
                $actions = [
                    ['label' => 'Add User', 'url' => '/users/create', 'icon' => 'fa-user-plus'],
                    ['label' => 'Generate Report', 'url' => '/reports', 'icon' => 'fa-file-alt'],
                    ['label' => 'Settings', 'url' => '/settings', 'icon' => 'fa-cogs']
                ];
                break;
            case 'compliance_officer':
                $actions = [
                    ['label' => 'New Task', 'url' => '/compliance/create', 'icon' => 'fa-plus'],
                    ['label' => 'SBP Circulars', 'url' => '/sbp-circulars', 'icon' => 'fa-newspaper'],
                    ['label' => 'Generate Report', 'url' => '/reports/compliance', 'icon' => 'fa-file-alt']
                ];
                break;
            case 'risk_manager':
                $actions = [
                    ['label' => 'Add Risk', 'url' => '/risk/create', 'icon' => 'fa-plus'],
                    ['label' => 'Assess Risk', 'url' => '/risk/assess', 'icon' => 'fa-clipboard-check'],
                    ['label' => 'Heatmap', 'url' => '/risk/heatmap', 'icon' => 'fa-fire']
                ];
                break;
            case 'internal_auditor':
                $actions = [
                    ['label' => 'New Audit', 'url' => '/audit/create', 'icon' => 'fa-plus'],
                    ['label' => 'Findings', 'url' => '/audit/findings', 'icon' => 'fa-search'],
                    ['label' => 'Evidence', 'url' => '/audit/evidence', 'icon' => 'fa-paperclip']
                ];
                break;
            default:
                $actions = [
                    ['label' => 'My Tasks', 'url' => '/compliance/tasks', 'icon' => 'fa-tasks'],
                    ['label' => 'Profile', 'url' => '/profile', 'icon' => 'fa-user'],
                    ['label' => 'Notifications', 'url' => '/notifications', 'icon' => 'fa-bell']
                ];
        }
        
        return $actions;
    }
}