<?php
/**
 * AI Banking GRC Platform - Dashboard Service
 * 
 * @package    AI-Banking-GRC-Platform
 * @subpackage app/Services
 * @version    1.0.0
 * @author     GRC Platform Team
 * @copyright  2026 AI Banking GRC Platform
 * @license    Proprietary
 * 
 * This service handles dashboard business logic:
 * - Dashboard statistics and KPIs
 * - Chart data generation
 * - Cards data
 * - Recent activities
 * - AI insights
 * - Summary data
 */

declare(strict_types=1);

namespace App\Services;

use App\Models\User;
use App\Models\RiskRegister;
use App\Models\ComplianceTask;
use App\Models\AuditPlan;
use App\Models\AuditFinding;
use App\Models\SbpCircular;
use App\Models\Policy;
use App\Models\Notification;
use App\Models\ActivityLog;
use App\Libraries\Logger;
use App\Libraries\Cache;

class DashboardService
{
    /**
     * @var User User model
     */
    private User $userModel;

    /**
     * @var RiskRegister Risk model
     */
    private RiskRegister $riskModel;

    /**
     * @var ComplianceTask Compliance model
     */
    private ComplianceTask $complianceModel;

    /**
     * @var AuditPlan Audit model
     */
    private AuditPlan $auditModel;

    /**
     * @var AuditFinding Finding model
     */
    private AuditFinding $findingModel;

    /**
     * @var SbpCircular SBP model
     */
    private SbpCircular $sbpModel;

    /**
     * @var Policy Policy model
     */
    private Policy $policyModel;

    /**
     * @var Notification Notification model
     */
    private Notification $notificationModel;

    /**
     * @var ActivityLog Activity log model
     */
    private ActivityLog $activityLogModel;

    /**
     * @var Logger Logger instance
     */
    private Logger $logger;

    /**
     * @var Cache Cache instance
     */
    private Cache $cache;

    /**
     * @var int Cache TTL in seconds
     */
    private int $cacheTTL = 300; // 5 minutes

    /**
     * Constructor
     */
    public function __construct()
    {
        $this->userModel = new User();
        $this->riskModel = new RiskRegister();
        $this->complianceModel = new ComplianceTask();
        $this->auditModel = new AuditPlan();
        $this->findingModel = new AuditFinding();
        $this->sbpModel = new SbpCircular();
        $this->policyModel = new Policy();
        $this->notificationModel = new Notification();
        $this->activityLogModel = new ActivityLog();
        $this->logger = new Logger();
        $this->cache = new Cache();
    }

    /**
     * Get dashboard overview
     * 
     * @param int $userId
     * @return array
     */
    public function overview(int $userId): array
    {
        try {
            $cacheKey = 'dashboard_overview_' . $userId;
            if ($this->cache->has($cacheKey)) {
                return $this->cache->get($cacheKey);
            }

            $stats = $this->statistics();
            $charts = $this->charts();
            $activities = $this->recentActivities($userId);
            $notifications = $this->getNotifications($userId);
            $insights = $this->getAIInsights($userId);

            $data = [
                'success' => true,
                'stats' => $stats,
                'charts' => $charts,
                'recent_activities' => $activities,
                'notifications' => $notifications,
                'ai_insights' => $insights,
                'timestamp' => time()
            ];

            $this->cache->put($cacheKey, $data, $this->cacheTTL);

            return $data;

        } catch (\Exception $e) {
            $this->logger->error('Dashboard overview error: ' . $e->getMessage());
            return $this->errorResponse('Failed to load dashboard overview.');
        }
    }

    /**
     * Get dashboard statistics
     * 
     * @return array
     */
    public function statistics(): array
    {
        try {
            $cacheKey = 'dashboard_stats';
            if ($this->cache->has($cacheKey)) {
                return $this->cache->get($cacheKey);
            }

            $stats = [
                'users' => [
                    'total' => $this->userModel->countAll(),
                    'active' => $this->userModel->countByStatus('active'),
                    'pending' => $this->userModel->countByStatus('pending'),
                    'today_active' => $this->userModel->countActiveToday()
                ],
                'risks' => [
                    'total' => $this->riskModel->countAll(),
                    'critical' => $this->riskModel->countByLevel('critical'),
                    'high' => $this->riskModel->countByLevel('high'),
                    'medium' => $this->riskModel->countByLevel('medium'),
                    'low' => $this->riskModel->countByLevel('low'),
                    'open' => $this->riskModel->countByStatus('identified'),
                    'mitigated' => $this->riskModel->countByStatus('mitigated')
                ],
                'compliance' => [
                    'total' => $this->complianceModel->countAll(),
                    'completed' => $this->complianceModel->countByStatus('completed'),
                    'in_progress' => $this->complianceModel->countByStatus('in_progress'),
                    'overdue' => $this->complianceModel->countOverdue(),
                    'completion_rate' => $this->getComplianceRate()
                ],
                'audits' => [
                    'total' => $this->auditModel->countAll(),
                    'planned' => $this->auditModel->countByStatus('planned'),
                    'in_progress' => $this->auditModel->countByStatus('in_progress'),
                    'completed' => $this->auditModel->countByStatus('completed'),
                    'findings' => $this->findingModel->countAll(),
                    'open_findings' => $this->findingModel->countByStatus('open'),
                    'resolution_rate' => $this->getResolutionRate()
                ],
                'sbp' => [
                    'total' => $this->sbpModel->countAll(),
                    'active' => $this->sbpModel->countByStatus('active'),
                    'pending' => $this->sbpModel->countByStatus('pending'),
                    'implemented' => $this->sbpModel->countByStatus('implemented')
                ],
                'policies' => [
                    'total' => $this->policyModel->countAll(),
                    'active' => $this->policyModel->countByStatus('active'),
                    'draft' => $this->policyModel->countByStatus('draft'),
                    'review' => $this->policyModel->countByStatus('review')
                ]
            ];

            $this->cache->put($cacheKey, $stats, $this->cacheTTL);

            return $stats;

        } catch (\Exception $e) {
            $this->logger->error('Dashboard statistics error: ' . $e->getMessage());
            return [];
        }
    }

    /**
     * Get chart data
     * 
     * @return array
     */
    public function charts(): array
    {
        try {
            $cacheKey = 'dashboard_charts';
            if ($this->cache->has($cacheKey)) {
                return $this->cache->get($cacheKey);
            }

            // Risk distribution
            $riskDistribution = [
                'Critical' => $this->riskModel->countByLevel('critical'),
                'High' => $this->riskModel->countByLevel('high'),
                'Medium' => $this->riskModel->countByLevel('medium'),
                'Low' => $this->riskModel->countByLevel('low')
            ];

            // Compliance trend (last 6 months)
            $complianceTrend = [];
            for ($i = 5; $i >= 0; $i--) {
                $month = date('Y-m', strtotime("-$i months"));
                $complianceTrend[$month] = $this->complianceModel->countByMonth($month);
            }

            // Audit status
            $auditStatus = [
                'Planned' => $this->auditModel->countByStatus('planned'),
                'In Progress' => $this->auditModel->countByStatus('in_progress'),
                'Completed' => $this->auditModel->countByStatus('completed'),
                'Closed' => $this->auditModel->countByStatus('closed')
            ];

            // SBP Circular status
            $sbpStatus = [
                'Active' => $this->sbpModel->countByStatus('active'),
                'Pending' => $this->sbpModel->countByStatus('pending'),
                'Implemented' => $this->sbpModel->countByStatus('implemented')
            ];

            // User activity trend
            $userActivity = [];
            for ($i = 6; $i >= 0; $i--) {
                $date = date('Y-m-d', strtotime("-$i days"));
                $userActivity[$date] = $this->activityLogModel->countByDate($date);
            }

            $charts = [
                'risk_distribution' => $riskDistribution,
                'compliance_trend' => $complianceTrend,
                'audit_status' => $auditStatus,
                'sbp_status' => $sbpStatus,
                'user_activity' => $userActivity
            ];

            $this->cache->put($cacheKey, $charts, $this->cacheTTL);

            return $charts;

        } catch (\Exception $e) {
            $this->logger->error('Dashboard charts error: ' . $e->getMessage());
            return [];
        }
    }

    /**
     * Get recent activities
     * 
     * @param int $userId
     * @param int $limit
     * @return array
     */
    public function recentActivities(int $userId, int $limit = 10): array
    {
        try {
            return $this->activityLogModel->getRecent($limit, $userId);

        } catch (\Exception $e) {
            $this->logger->error('Recent activities error: ' . $e->getMessage());
            return [];
        }
    }

    /**
     * Get user notifications
     * 
     * @param int $userId
     * @param int $limit
     * @return array
     */
    public function getNotifications(int $userId, int $limit = 5): array
    {
        try {
            return $this->notificationModel->getRecentForDashboard($userId, $limit);

        } catch (\Exception $e) {
            $this->logger->error('Get notifications error: ' . $e->getMessage());
            return [];
        }
    }

    /**
     * Get AI insights
     * 
     * @param int $userId
     * @return array
     */
    public function getAIInsights(int $userId): array
    {
        try {
            // This would call AI service
            // For now, return placeholder insights based on data

            $insights = [];
            $stats = $this->statistics();

            // Compliance insights
            if (($stats['compliance']['completion_rate'] ?? 0) < 70) {
                $insights[] = [
                    'type' => 'warning',
                    'title' => 'Compliance Score Low',
                    'description' => 'Compliance completion rate is below 70%. Review pending tasks.',
                    'priority' => 'high'
                ];
            }

            // Risk insights
            if (($stats['risks']['critical'] ?? 0) > 5) {
                $insights[] = [
                    'type' => 'critical',
                    'title' => 'Critical Risks Detected',
                    'description' => 'There are ' . $stats['risks']['critical'] . ' critical risks that need immediate attention.',
                    'priority' => 'critical'
                ];
            }

            // Audit insights
            if (($stats['audits']['open_findings'] ?? 0) > 10) {
                $insights[] = [
                    'type' => 'warning',
                    'title' => 'Audit Findings Open',
                    'description' => 'There are ' . $stats['audits']['open_findings'] . ' open audit findings.',
                    'priority' => 'medium'
                ];
            }

            // SBP insights
            if (($stats['sbp']['pending'] ?? 0) > 3) {
                $insights[] = [
                    'type' => 'info',
                    'title' => 'SBP Circulars Pending',
                    'description' => 'There are ' . $stats['sbp']['pending'] . ' SBP circulars pending implementation.',
                    'priority' => 'medium'
                ];
            }

            // If no insights, add a positive one
            if (empty($insights)) {
                $insights[] = [
                    'type' => 'success',
                    'title' => 'All Systems Go!',
                    'description' => 'Your compliance and risk metrics are looking good. Keep up the great work!',
                    'priority' => 'low'
                ];
            }

            return $insights;

        } catch (\Exception $e) {
            $this->logger->error('AI insights error: ' . $e->getMessage());
            return [];
        }
    }

    /**
     * Get dashboard summary
     * 
     * @param int $userId
     * @return array
     */
    public function summary(int $userId): array
    {
        try {
            $stats = $this->statistics();

            return [
                'success' => true,
                'summary' => [
                    'total_risks' => $stats['risks']['total'] ?? 0,
                    'open_risks' => $stats['risks']['open'] ?? 0,
                    'compliance_rate' => $stats['compliance']['completion_rate'] ?? 0,
                    'audit_findings' => $stats['audits']['findings'] ?? 0,
                    'pending_tasks' => $stats['compliance']['in_progress'] ?? 0,
                    'sbp_circulars' => $stats['sbp']['pending'] ?? 0,
                    'active_users' => $stats['users']['active'] ?? 0,
                    'last_updated' => date('Y-m-d H:i:s')
                ],
                'timestamp' => time()
            ];

        } catch (\Exception $e) {
            $this->logger->error('Dashboard summary error: ' . $e->getMessage());
            return $this->errorResponse('Failed to load dashboard summary.');
        }
    }

    /**
     * Get compliance rate
     * 
     * @return float
     */
    private function getComplianceRate(): float
    {
        $total = $this->complianceModel->countAll();
        $completed = $this->complianceModel->countByStatus('completed');

        return $total > 0 ? round(($completed / $total) * 100, 2) : 0;
    }

    /**
     * Get resolution rate
     * 
     * @return float
     */
    private function getResolutionRate(): float
    {
        $total = $this->findingModel->countAll();
        $resolved = $this->findingModel->countByStatus('resolved') + 
                    $this->findingModel->countByStatus('closed');

        return $total > 0 ? round(($resolved / $total) * 100, 2) : 0;
    }

    /**
     * Error response
     * 
     * @param string $message
     * @return array
     */
    private function errorResponse(string $message): array
    {
        return [
            'success' => false,
            'error' => $message,
            'timestamp' => time()
        ];
    }
}