<?php
namespace App\Controllers;

use App\Models\Notification;
use App\Helpers\Auth;
use Exception;

class NotificationController extends BaseController
{
    private Notification $notificationModel;
    
    public function __construct()
    {
        parent::__construct();
        $this->controllerName = 'Notification';
        $this->notificationModel = new Notification();
        $this->requireAuth();
    }
    
    public function index(): void
    {
        $page = (int)$this->input('page', 1);
        $notifications = $this->notificationModel->getUserNotifications(
            Auth::id(),
            $page,
            PAGINATION_DEFAULT
        );
        $unreadCount = $this->notificationModel->countUnread(Auth::id());
        
        $this->render('index', [
            'title' => 'Notifications - ' . APP_NAME,
            'notifications' => $notifications,
            'unread_count' => $unreadCount
        ]);
    }
    
    public function unread(): void
    {
        $notifications = $this->notificationModel->getUnread(Auth::id());
        $this->jsonSuccess('Unread notifications retrieved.', $notifications);
    }
    
    public function markRead(array $params): void
    {
        try {
            $notificationId = (int)$params['id'];
            $result = $this->notificationModel->markAsRead($notificationId, Auth::id());
            
            if (!$result) {
                throw new Exception('Failed to mark notification as read.');
            }
            
            $this->jsonSuccess('Notification marked as read.');
            
        } catch (Exception $e) {
            $this->jsonError($e->getMessage());
        }
    }
    
    public function markAllRead(): void
    {
        try {
            $result = $this->notificationModel->markAllAsRead(Auth::id());
            $this->jsonSuccess('All notifications marked as read.');
        } catch (Exception $e) {
            $this->jsonError($e->getMessage());
        }
    }
}