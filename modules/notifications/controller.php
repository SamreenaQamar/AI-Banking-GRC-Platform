<?php
/**
 * Notifications Module - Controller
 * 
 * @package    AI-Banking-GRC-Platform
 * @subpackage modules/notifications
 * @version    1.0.0
 * @author     GRC Platform Team
 * @copyright  2026 AI Banking GRC Platform
 * @license    Proprietary
 * 
 * This controller handles:
 * - Notification management
 * - Alerts
 * - Calendar
 * - Reminders
 * - Email queue
 */

declare(strict_types=1);

namespace Modules\Notifications\Controllers;

use App\Controllers\BaseController;
use App\Helpers\Auth;
use App\Helpers\CSRF;
use App\Helpers\Validation;
use Modules\Notifications\Services\NotificationService;
use App\Models\Notification;
use Exception;

class NotificationController extends BaseController
{
    /**
     * @var NotificationService
     */
    private NotificationService $notificationService;
    
    /**
     * @var Notification
     */
    private Notification $notificationModel;
    
    /**
     * Constructor
     */
    public function __construct()
    {
        parent::__construct();
        $this->controllerName = 'Notifications';
        $this->notificationService = new NotificationService();
        $this->notificationModel = new Notification();
        
        $this->requireAuth();
    }
    
    /**
     * Notifications dashboard
     * 
     * @return void
     */
    public function index(): void
    {
        try {
            $userId = Auth::id();
            $dashboardData = $this->notificationService->getDashboardData($userId);
            
            $this->render('notifications/dashboard', [
                'title' => 'Notifications - ' . APP_NAME,
                'data' => $dashboardData,
                'notification_types' => NOTIFICATION_TYPES,
                'notification_channels' => NOTIFICATION_CHANNELS,
                'notification_priorities' => NOTIFICATION_PRIORITIES
            ]);
            
        } catch (Exception $e) {
            $this->setFlashMessage('error', 'Failed to load notifications: ' . $e->getMessage());
            $this->redirectToRoute('dashboard');
        }
    }
    
    /**
     * List all notifications
     * 
     * @return void
     */
    public function list(): void
    {
        try {
            $userId = Auth::id();
            $filters = [
                'type' => $this->input('type'),
                'priority' => $this->input('priority'),
                'status' => $this->input('status'),
                'search' => $this->input('search')
            ];
            
            $page = (int)$this->input('page', 1);
            $perPage = (int)$this->input('per_page', 15);
            
            $notifications = $this->notificationModel->getFiltered($filters, $page, $perPage, $userId);
            $total = $this->notificationModel->countFiltered($filters, $userId);
            
            $this->render('notifications/list', [
                'title' => 'All Notifications - ' . APP_NAME,
                'notifications' => $notifications,
                'total' => $total,
                'page' => $page,
                'per_page' => $perPage,
                'total_pages' => ceil($total / $perPage),
                'filters' => $filters,
                'notification_types' => NOTIFICATION_TYPES,
                'notification_priorities' => NOTIFICATION_PRIORITIES
            ]);
            
        } catch (Exception $e) {
            $this->setFlashMessage('error', 'Failed to load notifications: ' . $e->getMessage());
            $this->redirectToRoute('notifications.index');
        }
    }
    
    /**
     * Mark notification as read (AJAX)
     * 
     * @param array $params
     * @return void
     */
    public function markRead(array $params): void
    {
        try {
            CSRF::validate($_POST['csrf_token'] ?? '');
            
            $notificationId = (int)($params['id'] ?? 0);
            $userId = Auth::id();
            
            $result = $this->notificationService->markAsRead($notificationId, $userId);
            
            if (!$result) {
                throw new Exception('Failed to mark notification as read.');
            }
            
            $this->jsonSuccess('Notification marked as read.');
            
        } catch (Exception $e) {
            $this->jsonError($e->getMessage());
        }
    }
    
    /**
     * Mark all as read (AJAX)
     * 
     * @return void
     */
    public function markAllRead(): void
    {
        try {
            CSRF::validate($_POST['csrf_token'] ?? '');
            
            $userId = Auth::id();
            $result = $this->notificationService->markAllAsRead($userId);
            
            if (!$result) {
                throw new Exception('Failed to mark all notifications as read.');
            }
            
            $this->jsonSuccess('All notifications marked as read.');
            
        } catch (Exception $e) {
            $this->jsonError($e->getMessage());
        }
    }
    
    /**
     * Delete notification (AJAX)
     * 
     * @param array $params
     * @return void
     */
    public function delete(array $params): void
    {
        try {
            CSRF::validate($_POST['csrf_token'] ?? '');
            
            $notificationId = (int)($params['id'] ?? 0);
            $userId = Auth::id();
            
            $result = $this->notificationService->deleteNotification($notificationId, $userId);
            
            if (!$result) {
                throw new Exception('Failed to delete notification.');
            }
            
            $this->jsonSuccess('Notification deleted successfully.');
            
        } catch (Exception $e) {
            $this->jsonError($e->getMessage());
        }
    }
    
    /**
     * Create reminder
     * 
     * @return void
     */
    public function createReminder(): void
    {
        try {
            CSRF::validate($_POST['csrf_token'] ?? '');
            
            $userId = Auth::id();
            $title = $this->input('title');
            $description = $this->input('description');
            $remindAt = $this->input('remind_at');
            $priority = $this->input('priority', 'medium');
            
            if (empty($title) || empty($description) || empty($remindAt)) {
                throw new Exception('Title, description, and reminder date are required.');
            }
            
            $result = $this->notificationService->createReminder(
                $userId,
                $title,
                $description,
                $remindAt,
                ['priority' => $priority]
            );
            
            if (!$result) {
                throw new Exception('Failed to create reminder.');
            }
            
            $this->setFlashMessage('success', 'Reminder created successfully.');
            $this->redirectToRoute('notifications.index');
            
        } catch (Exception $e) {
            $this->setFlashMessage('error', $e->getMessage());
            $this->redirectToRoute('notifications.index');
        }
    }
    
    /**
     * Calendar view
     * 
     * @return void
     */
    public function calendar(): void
    {
        try {
            $userId = Auth::id();
            $month = (int)$this->input('month', date('n'));
            $year = (int)$this->input('year', date('Y'));
            
            $calendarData = $this->getCalendarData($userId, $month, $year);
            
            $this->render('notifications/calendar', [
                'title' => 'Calendar - ' . APP_NAME,
                'calendar_data' => $calendarData,
                'month' => $month,
                'year' => $year,
                'notification_types' => NOTIFICATION_TYPES
            ]);
            
        } catch (Exception $e) {
            $this->setFlashMessage('error', 'Failed to load calendar: ' . $e->getMessage());
            $this->redirectToRoute('notifications.index');
        }
    }
    
    /**
     * Get calendar data
     * 
     * @param int $userId
     * @param int $month
     * @param int $year
     * @return array
     */
    private function getCalendarData(int $userId, int $month, int $year): array
    {
        $startDate = date('Y-m-01', strtotime("$year-$month-01"));
        $endDate = date('Y-m-t', strtotime($startDate));
        
        $sql = "SELECT 
                    DATE(created_at) as event_date,
                    title,
                    message,
                    type,
                    priority,
                    action_url
                FROM notifications
                WHERE user_id = :user_id
                AND created_at BETWEEN :start_date AND :end_date
                ORDER BY created_at ASC";
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute([
            'user_id' => $userId,
            'start_date' => $startDate . ' 00:00:00',
            'end_date' => $endDate . ' 23:59:59'
        ]);
        
        $events = $stmt->fetchAll(PDO::FETCH_OBJ);
        
        $calendar = [];
        foreach ($events as $event) {
            $date = $event->event_date;
            if (!isset($calendar[$date])) {
                $calendar[$date] = [];
            }
            $calendar[$date][] = [
                'title' => $event->title,
                'message' => $event->message,
                'type' => $event->type,
                'priority' => $event->priority,
                'url' => $event->action_url
            ];
        }
        
        return $calendar;
    }
}