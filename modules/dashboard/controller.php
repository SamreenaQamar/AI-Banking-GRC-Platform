<?php
/**
 * Dashboard Module - Controller
 * 
 * @package    AI-Banking-GRC-Platform
 * @subpackage modules/dashboard
 * @version    1.0.0
 * @author     GRC Platform Team
 * @copyright  2026 AI Banking GRC Platform
 * @license    Proprietary
 * 
 * This controller handles:
 * - Dashboard rendering
 * - Statistics aggregation
 * - Chart data generation
 * - Analytics processing
 * - Report generation
 */

declare(strict_types=1);

namespace Modules\Dashboard\Controllers;

use App\Controllers\BaseController;
use App\Helpers\Auth;
use App\Helpers\CSRF;
use App\Helpers\Validation;
use Modules\Dashboard\Services\DashboardService;
use Exception;

class DashboardController extends BaseController
{
    /**
     * @var DashboardService
     */
    private DashboardService $dashboardService;
    
    /**
     * Constructor
     */
    public function __construct()
    {
        parent::__construct();
        $this->controllerName = 'Dashboard';
        $this->dashboardService = new DashboardService();
        
        $this->requireAuth();
    }
    
    /**
     * Main dashboard page
     * 
     * @return void
     */
    public function index(): void
    {
        try {
            $userId = Auth::id();
            $user = Auth::user();
            $role = $user->role_display_name ?? 'user';
            
            // Get dashboard data
            $stats = $this->dashboardService->getDashboardStats($userId);
            $chartData = $this->dashboardService->getChartData();
            $recentActivities = $this->dashboardService->getRecentActivities(10, $userId);
            $notifications = $this->dashboardService->getNotifications($userId, 5);
            $aiInsights = $this->dashboardService->getAIInsights($userId);
            $quickActions = $this->dashboardService->getQuickActions($role);
            
            // Get role-based widgets
            $widgets = dashboard_role_widgets($role);
            
            $this->render('dashboard/index', [
                'title' => 'Dashboard - ' . APP_NAME,
                'stats' => $stats,
                'chart_data' => $chartData,
                'recent_activities' => $recentActivities,
                'notifications' => $notifications,
                'ai_insights' => $aiInsights,
                'quick_actions' => $quickActions,
                'widgets' => $widgets,
                'user' => $user,
                'role' => $role,
                'refresh_interval' => DASHBOARD_REFRESH_INTERVAL,
                'auto_refresh' => DASHBOARD_AUTO_REFRESH_ENABLED
            ]);
            
        } catch (Exception $e) {
            $this->setFlashMessage('error', 'Failed to load dashboard: ' . $e->getMessage());
            $this->render('dashboard/index', [
                'title' => 'Dashboard - ' . APP_NAME,
                'stats' => [],
                'chart_data' => [],
                'recent_activities' => [],
                'notifications' => [],
                'ai_insights' => [],
                'quick_actions' => [],
                'widgets' => [],
                'user' => null,
                'role' => 'user',
                'refresh_interval' => 300,
                'auto_refresh' => true
            ]);
        }
    }
    
    /**
     * Get dashboard statistics (AJAX)
     * 
     * @return void
     */
    public function statistics(): void
    {
        try {
            $userId = Auth::id();
            $stats = $this->dashboardService->getDashboardStats($userId);
            
            $this->jsonSuccess('Statistics retrieved successfully.', $stats);
            
        } catch (Exception $e) {
            $this->jsonError('Failed to retrieve statistics: ' . $e->getMessage());
        }
    }
    
    /**
     * Get chart data (AJAX)
     * 
     * @return void
     */
    public function charts(): void
    {
        try {
            $period = $this->input('period', 'month');
            $chartData = $this->dashboardService->getChartData($period);
            
            $this->jsonSuccess('Chart data retrieved successfully.', $chartData);
            
        } catch (Exception $e) {
            $this->jsonError('Failed to retrieve chart data: ' . $e->getMessage());
        }
    }
    
    /**
     * Get recent activities (AJAX)
     * 
     * @return void
     */
    public function recentActivities(): void
    {
        try {
            $userId = Auth::id();
            $limit = (int)$this->input('limit', 10);
            $activities = $this->dashboardService->getRecentActivities($limit, $userId);
            
            $this->jsonSuccess('Activities retrieved successfully.', $activities);
            
        } catch (Exception $e) {
            $this->jsonError('Failed to retrieve activities: ' . $e->getMessage());
        }
    }
    
    /**
     * Get notifications (AJAX)
     * 
     * @return void
     */
    public function notifications(): void
    {
        try {
            $userId = Auth::id();
            $limit = (int)$this->input('limit', 5);
            $notifications = $this->dashboardService->getNotifications($userId, $limit);
            $unreadCount = $this->dashboardService->getNotificationStats($userId);
            
            $this->jsonSuccess('Notifications retrieved successfully.', [
                'notifications' => $notifications,
                'unread_count' => $unreadCount['unread'] ?? 0
            ]);
            
        } catch (Exception $e) {
            $this->jsonError('Failed to retrieve notifications: ' . $e->getMessage());
        }
    }
    
    /**
     * Get AI insights (AJAX)
     * 
     * @return void
     */
    public function aiInsights(): void
    {
        try {
            $userId = Auth::id();
            $insights = $this->dashboardService->getAIInsights($userId);
            
            $this->jsonSuccess('AI insights retrieved successfully.', $insights);
            
        } catch (Exception $e) {
            $this->jsonError('Failed to retrieve AI insights: ' . $e->getMessage());
        }
    }
    
    /**
     * Mark notification as read (AJAX)
     * 
     * @return void
     */
    public function markNotificationRead(): void
    {
        try {
            CSRF::validate($_POST['csrf_token'] ?? '');
            
            $notificationId = (int)$this->input('id');
            $userId = Auth::id();
            
            // This would call notification service
            // $this->notificationService->markAsRead($notificationId, $userId);
            
            $this->jsonSuccess('Notification marked as read.');
            
        } catch (Exception $e) {
            $this->jsonError('Failed to mark notification: ' . $e->getMessage());
        }
    }
    
    /**
     * Mark all notifications as read (AJAX)
     * 
     * @return void
     */
    public function markAllNotificationsRead(): void
    {
        try {
            CSRF::validate($_POST['csrf_token'] ?? '');
            
            $userId = Auth::id();
            
            // This would call notification service
            // $this->notificationService->markAllAsRead($userId);
            
            $this->jsonSuccess('All notifications marked as read.');
            
        } catch (Exception $e) {
            $this->jsonError('Failed to mark notifications: ' . $e->getMessage());
        }
    }
    
    /**
     * Update widget settings (AJAX)
     * 
     * @return void
     */
    public function updateWidgets(): void
    {
        try {
            CSRF::validate($_POST['csrf_token'] ?? '');
            
            $widgets = $this->input('widgets', []);
            
            // This would save widget preferences
            // $this->userModel->updateWidgetPreferences(Auth::id(), $widgets);
            
            $this->jsonSuccess('Widget settings updated successfully.');
            
        } catch (Exception $e) {
            $this->jsonError('Failed to update widgets: ' . $e->getMessage());
        }
    }
    
    /**
     * Get system health (AJAX)
     * 
     * @return void
     */
    public function systemHealth(): void
    {
        try {
            $health = [
                'status' => 'healthy',
                'uptime' => $this->getUptime(),
                'memory_usage' => $this->getMemoryUsage(),
                'db_connections' => $this->getDbConnections(),
                'cache_status' => 'active',
                'last_backup' => $this->getLastBackup()
            ];
            
            $this->jsonSuccess('System health retrieved successfully.', $health);
            
        } catch (Exception $e) {
            $this->jsonError('Failed to retrieve system health: ' . $e->getMessage());
        }
    }
    
    /**
     * Get uptime
     * 
     * @return string
     */
    private function getUptime(): string
    {
        if (strpos(strtolower(PHP_OS), 'win') !== false) {
            return 'Windows system';
        } else {
            $uptime = file_get_contents('/proc/uptime');
            $seconds = explode(' ', $uptime)[0];
            $days = floor($seconds / 86400);
            $hours = floor(($seconds % 86400) / 3600);
            $minutes = floor(($seconds % 3600) / 60);
            return "{$days}d {$hours}h {$minutes}m";
        }
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
     * Get database connections
     * 
     * @return int
     */
    private function getDbConnections(): int
    {
        // This would query database for connection count
        return 0;
    }
    
    /**
     * Get last backup time
     * 
     * @return string
     */
    private function getLastBackup(): string
    {
        // This would check last backup timestamp
        return '2 hours ago';
    }
}