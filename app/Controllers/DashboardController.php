<?php
/**
 * AI Banking GRC Platform - Dashboard Controller
 * 
 * @package    AI-Banking-GRC-Platform
 * @version    1.0.0
 * @author     GRC Platform Team
 * @copyright  2026 AI Banking GRC Platform
 * @license    Proprietary
 * 
 * This controller handles:
 * - Main dashboard display
 * - KPI metrics and statistics
 * - Chart data generation
 * - Recent activities
 * - Real-time notifications
 */

declare(strict_types=1);

namespace App\Controllers;

use App\Models\ComplianceTask;
use App\Models\RiskRegister;
use App\Models\AuditPlan;
use App\Models\SbpCircular;
use App\Models\User;
use App\Services\DashboardService;
use App\Services\AnalyticsService;
use Exception;

class DashboardController extends BaseController
{
    /**
     * @var DashboardService
     */
    private DashboardService $dashboardService;
    
    /**
     * @var AnalyticsService
     */
    private AnalyticsService $analyticsService;
    
    /**
     * @var ComplianceTask
     */
    private ComplianceTask $complianceModel;
    
    /**
     * @var RiskRegister
     */
    private RiskRegister $riskModel;
    
    /**
     * @var AuditPlan
     */
    private AuditPlan $auditModel;
    
    /**
     * @var SbpCircular
     */
    private SbpCircular $sbpModel;
    
    /**
     * Constructor
     */
    public function __construct()
    {
        parent::__construct();
        $this->controllerName = 'Dashboard';
        $this->dashboardService = new DashboardService();
        $this->analyticsService = new AnalyticsService();
        $this->complianceModel = new ComplianceTask();
        $this->riskModel = new RiskRegister();
        $this->auditModel = new AuditPlan();
        $this->sbpModel = new SbpCircular();
        
        $this->requireAuth();
    }
    
    /**
     * Display main dashboard
     * 
     * @return void
     */
    public function index(): void
    {
        try {
            // Get dashboard statistics
            $stats = $this->getDashboardStats();
            
            // Get chart data
            $chartData = $this->getChartData();
            
            // Get recent activities
            $recentActivities = $this->getRecentActivities();
            
            // Get upcoming tasks
            $upcomingTasks = $this->getUpcomingTasks();
            
            // Get notifications
            $notifications = $this->getNotifications();
            
            $this->render('index', [
                'title' => 'Dashboard - ' . APP_NAME,
                'stats' => $stats,
                'chart_data' => $chartData,
                'recent_activities' => $recentActivities,
                'upcoming_tasks' => $upcomingTasks,
                'notifications' => $notifications,
                'user' => $this->getCurrentUser()
            ]);
            
        } catch (Exception $e) {
            $this->setFlashMessage('error', 'Failed to load dashboard: ' . $e->getMessage());
            $this->render('index', [
                'title' => 'Dashboard - ' . APP_NAME,
                'stats' => [],
                'chart_data' => [],
                'recent_activities' => [],
                'upcoming_tasks' => [],
                'notifications' => []
            ]);
        }
    }
    
    /**
     * Get dashboard statistics
     * 
     * @return array
     */
    private function getDashboardStats(): array
    {
        return [
            'total_users' => User::count(),
            'active_users' => User::countActive(),
            'pending_users' => User::countPending(),
            
            'compliance_tasks' => $this->complianceModel->countAll(),
            'compliance_completed' => $this->complianceModel->countCompleted(),
            'compliance_overdue' => $this->complianceModel->countOverdue(),
            'compliance_completion_rate' => $this->complianceModel->getCompletionRate(),
            
            'total_risks' => $this->riskModel->countAll(),
            'critical_risks' => $this->riskModel->countByLevel('critical'),
            'high_risks' => $this->riskModel->countByLevel('high'),
            'medium_risks' => $this->riskModel->countByLevel('medium'),
            'low_risks' => $this->riskModel->countByLevel('low'),
            'risk_trend' => $this->riskModel->getRiskTrend(),
            
            'audits_in_progress' => $this->auditModel->countByStatus('in_progress'),
            'audits_completed' => $this->auditModel->countByStatus('completed'),
            'audit_findings' => $this->auditModel->countFindings(),
            'audit_findings_resolved' => $this->auditModel->countResolvedFindings(),
            
            'sbp_circulars_active' => $this->sbpModel->countActive(),
            'sbp_circulars_pending' => $this->sbpModel->countPending(),
            'sbp_circulars_implemented' => $this->sbpModel->countImplemented(),
            
            'policies_total' => $this->getPoliciesCount(),
            'policies_acknowledged' => $this->getPolicyAcknowledgements(),
            
            'system_health' => $this->getSystemHealth()
        ];
    }
    
    /**
     * Get chart data for dashboard
     * 
     * @return array
     */
    private function getChartData(): array
    {
        return [
            'compliance_trend' => $this->analyticsService->getComplianceTrend(),
            'risk_distribution' => $this->analyticsService->getRiskDistribution(),
            'audit_status' => $this->analyticsService->getAuditStatusDistribution(),
            'sbp_compliance' => $this->analyticsService->getSBPComplianceStatus(),
            'user_growth' => $this->analyticsService->getUserGrowthTrend(),
            'department_performance' => $this->analyticsService->getDepartmentPerformance()
        ];
    }
    
    /**
     * Get recent activities
     * 
     * @return array
     */
    private function getRecentActivities(): array
    {
        // Get recent activities from the activity log
        // This will be implemented in ActivityLogService
        return [];
    }
    
    /**
     * Get upcoming tasks
     * 
     * @return array
     */
    private function getUpcomingTasks(): array
    {
        $user = $this->getCurrentUser();
        
        return [
            'compliance' => $this->complianceModel->getUpcomingTasks($user->id, 5),
            'audits' => $this->auditModel->getUpcomingAudits($user->id, 5),
            'sbp_circulars' => $this->sbpModel->getUpcomingDeadlines(5)
        ];
    }
    
    /**
     * Get user notifications
     * 
     * @return array
     */
    private function getNotifications(): array
    {
        // This will be implemented in NotificationService
        return [];
    }
    
    /**
     * Get policies count
     * 
     * @return int
     */
    private function getPoliciesCount(): int
    {
        // This will be implemented in PolicyModel
        return 0;
    }
    
    /**
     * Get policy acknowledgements
     * 
     * @return int
     */
    private function getPolicyAcknowledgements(): int
    {
        // This will be implemented in PolicyModel
        return 0;
    }
    
    /**
     * Get system health status
     * 
     * @return array
     */
    private function getSystemHealth(): array
    {
        return [
            'status' => 'healthy',
            'uptime' => $this->getUptime(),
            'db_connections' => $this->getDbConnections(),
            'cache_status' => $this->getCacheStatus(),
            'memory_usage' => $this->getMemoryUsage()
        ];
    }
    
    /**
     * Get system uptime
     * 
     * @return string
     */
    private function getUptime(): string
    {
        if (strpos(strtolower(PHP_OS), 'win') !== false) {
            $uptime = system('wmic os get lastbootuptime', $output);
            return 'Windows system';
        } else {
            $uptime = file_get_contents('/proc/uptime');
            $seconds = explode(' ', $uptime)[0];
            $days = floor($seconds / 86400);
            $hours = floor(($seconds % 86400) / 3600);
            return "{$days}d {$hours}h";
        }
    }
    
    /**
     * Get database connections
     * 
     * @return int
     */
    private function getDbConnections(): int
    {
        // This will be implemented in Database class
        return 0;
    }
    
    /**
     * Get cache status
     * 
     * @return string
     */
    private function getCacheStatus(): string
    {
        return 'active';
    }
    
    /**
     * Get memory usage
     * 
     * @return string
     */
    private function getMemoryUsage(): string
    {
        $memory = memory_get_peak_usage(true);
        $size = ['B', 'KB', 'MB', 'GB'];
        $factor = floor((strlen($memory) - 1) / 3);
        return sprintf("%.2f", $memory / pow(1024, $factor)) . ' ' . $size[$factor];
    }
    
    /**
     * Get dashboard stats for API
     * 
     * @return void
     */
    public function stats(): void
    {
        try {
            $stats = $this->getDashboardStats();
            $this->jsonSuccess('Dashboard stats retrieved successfully.', $stats);
        } catch (Exception $e) {
            $this->jsonError('Failed to retrieve dashboard stats: ' . $e->getMessage());
        }
    }
    
    /**
     * Get chart data for API
     * 
     * @return void
     */
    public function charts(): void
    {
        try {
            $chartData = $this->getChartData();
            $this->jsonSuccess('Chart data retrieved successfully.', $chartData);
        } catch (Exception $e) {
            $this->jsonError('Failed to retrieve chart data: ' . $e->getMessage());
        }
    }
}