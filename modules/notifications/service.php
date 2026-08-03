<?php
/**
 * Notifications Module - Service Layer
 * 
 * @package    AI-Banking-GRC-Platform
 * @subpackage modules/notifications
 * @version    1.0.0
 * @author     GRC Platform Team
 * @copyright  2026 AI Banking GRC Platform
 * @license    Proprietary
 * 
 * This service handles notification operations:
 * - Send notifications
 * - Manage alerts
 * - Calendar events
 * - Reminders
 * - Email queue
 */

declare(strict_types=1);

namespace Modules\Notifications\Services;

use App\Models\Notification;
use App\Models\ActivityLog;
use App\Helpers\Auth;
use App\Helpers\Database;
use Exception;
use PDO;

class NotificationService
{
    /**
     * @var PDO
     */
    private PDO $db;
    
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
        $this->notificationModel = new Notification();
        $this->activityLogModel = new ActivityLog();
    }
    
    /**
     * Get notification statistics
     * 
     * @param int $userId
     * @return array
     */
    public function getNotificationStats(int $userId): array
    {
        $total = $this->notificationModel->countUserNotifications($userId);
        $unread = $this->notificationModel->countUnread($userId);
        $read = $total - $unread;
        $today = $this->notificationModel->countToday($userId);
        
        return [
            'total' => $total,
            'unread' => $unread,
            'read' => $read,
            'today' => $today
        ];
    }
    
    /**
     * Get dashboard data
     * 
     * @param int $userId
     * @return array
     */
    public function getDashboardData(int $userId): array
    {
        $stats = $this->getNotificationStats($userId);
        $recent = $this->getRecentNotifications($userId, 10);
        $unreadList = $this->getUnreadNotifications($userId, 5);
        $alerts = $this->getActiveAlerts($userId);
        $upcomingReminders = $this->getUpcomingReminders($userId);
        
        return [
            'stats' => $stats,
            'recent' => $recent,
            'unread_list' => $unreadList,
            'alerts' => $alerts,
            'upcoming_reminders' => $upcomingReminders
        ];
    }
    
    /**
     * Get recent notifications
     * 
     * @param int $userId
     * @param int $limit
     * @return array
     */
    public function getRecentNotifications(int $userId, int $limit = 10): array
    {
        $sql = "SELECT * FROM notifications 
                WHERE user_id = :user_id
                AND (expires_at IS NULL OR expires_at > NOW())
                ORDER BY created_at DESC
                LIMIT :limit";
        
        $stmt = $this->db->prepare($sql);
        $stmt->bindParam('user_id', $userId, PDO::PARAM_INT);
        $stmt->bindParam('limit', $limit, PDO::PARAM_INT);
        $stmt->execute();
        
        return $stmt->fetchAll(PDO::FETCH_OBJ);
    }
    
    /**
     * Get unread notifications
     * 
     * @param int $userId
     * @param int $limit
     * @return array
     */
    public function getUnreadNotifications(int $userId, int $limit = 5): array
    {
        $sql = "SELECT * FROM notifications 
                WHERE user_id = :user_id 
                AND is_read = 0
                AND (expires_at IS NULL OR expires_at > NOW())
                ORDER BY priority ASC, created_at DESC
                LIMIT :limit";
        
        $stmt = $this->db->prepare($sql);
        $stmt->bindParam('user_id', $userId, PDO::PARAM_INT);
        $stmt->bindParam('limit', $limit, PDO::PARAM_INT);
        $stmt->execute();
        
        return $stmt->fetchAll(PDO::FETCH_OBJ);
    }
    
    /**
     * Get active alerts
     * 
     * @param int $userId
     * @return array
     */
    public function getActiveAlerts(int $userId): array
    {
        $sql = "SELECT * FROM notifications 
                WHERE user_id = :user_id 
                AND type IN ('alert', 'risk')
                AND is_read = 0
                AND (expires_at IS NULL OR expires_at > NOW())
                ORDER BY created_at DESC";
        
        $stmt = $this->db->prepare($sql);
        $stmt->bindParam('user_id', $userId, PDO::PARAM_INT);
        $stmt->execute();
        
        return $stmt->fetchAll(PDO::FETCH_OBJ);
    }
    
    /**
     * Get upcoming reminders
     * 
     * @param int $userId
     * @param int $days
     * @return array
     */
    public function getUpcomingReminders(int $userId, int $days = 7): array
    {
        $sql = "SELECT * FROM notifications 
                WHERE user_id = :user_id 
                AND type = 'reminder'
                AND is_read = 0
                AND expires_at <= DATE_ADD(NOW(), INTERVAL :days DAY)
                AND (expires_at IS NOT NULL)
                ORDER BY expires_at ASC";
        
        $stmt = $this->db->prepare($sql);
        $stmt->bindParam('user_id', $userId, PDO::PARAM_INT);
        $stmt->bindParam('days', $days, PDO::PARAM_INT);
        $stmt->execute();
        
        return $stmt->fetchAll(PDO::FETCH_OBJ);
    }
    
    /**
     * Send notification
     * 
     * @param int $userId
     * @param string $title
     * @param string $message
     * @param array $options
     * @return int|false
     */
    public function sendNotification(int $userId, string $title, string $message, array $options = [])
    {
        $data = [
            'user_id' => $userId,
            'title' => $title,
            'message' => $message,
            'type' => $options['type'] ?? 'system',
            'priority' => $options['priority'] ?? 'medium',
            'action_url' => $options['action_url'] ?? null,
            'action_label' => $options['action_label'] ?? null,
            'expires_at' => $options['expires_at'] ?? null,
            'created_by' => $options['created_by'] ?? Auth::id(),
            'created_at' => date('Y-m-d H:i:s')
        ];
        
        $id = $this->notificationModel->create($data);
        
        if ($id && $options['channels'] ?? false) {
            $this->sendToChannels($id, $data, $options['channels']);
        }
        
        return $id;
    }
    
    /**
     * Send notification to multiple users
     * 
     * @param array $userIds
     * @param string $title
     * @param string $message
     * @param array $options
     * @return int
     */
    public function sendBulkNotification(array $userIds, string $title, string $message, array $options = []): int
    {
        $count = 0;
        
        foreach ($userIds as $userId) {
            $result = $this->sendNotification($userId, $title, $message, $options);
            if ($result) {
                $count++;
            }
        }
        
        return $count;
    }
    
    /**
     * Send to channels
     * 
     * @param int $notificationId
     * @param array $data
     * @param array $channels
     * @return void
     */
    private function sendToChannels(int $notificationId, array $data, array $channels): void
    {
        foreach ($channels as $channel) {
            switch ($channel) {
                case 'email':
                    $this->sendEmail($notificationId, $data);
                    break;
                case 'sms':
                    $this->sendSMS($notificationId, $data);
                    break;
                case 'push':
                    $this->sendPush($notificationId, $data);
                    break;
            }
        }
    }
    
    /**
     * Send email notification
     * 
     * @param int $notificationId
     * @param array $data
     * @return void
     */
    private function sendEmail(int $notificationId, array $data): void
    {
        // This would integrate with email service
        // For now, queue email
        $this->queueEmail($notificationId, $data);
    }
    
    /**
     * Send SMS notification
     * 
     * @param int $notificationId
     * @param array $data
     * @return void
     */
    private function sendSMS(int $notificationId, array $data): void
    {
        // This would integrate with SMS service
    }
    
    /**
     * Send push notification
     * 
     * @param int $notificationId
     * @param array $data
     * @return void
     */
    private function sendPush(int $notificationId, array $data): void
    {
        // This would integrate with push notification service
    }
    
    /**
     * Queue email
     * 
     * @param int $notificationId
     * @param array $data
     * @return void
     */
    private function queueEmail(int $notificationId, array $data): void
    {
        $sql = "INSERT INTO email_queue (notification_id, user_id, subject, body, status, created_at) 
                VALUES (:notification_id, :user_id, :subject, :body, 'pending', :created_at)";
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute([
            'notification_id' => $notificationId,
            'user_id' => $data['user_id'],
            'subject' => $data['title'],
            'body' => $data['message'],
            'created_at' => date('Y-m-d H:i:s')
        ]);
    }
    
    /**
     * Mark notification as read
     * 
     * @param int $notificationId
     * @param int $userId
     * @return bool
     */
    public function markAsRead(int $notificationId, int $userId): bool
    {
        $notification = $this->notificationModel->find($notificationId);
        
        if (!$notification || $notification->user_id != $userId) {
            return false;
        }
        
        return $this->notificationModel->update($notificationId, [
            'is_read' => 1,
            'read_at' => date('Y-m-d H:i:s')
        ]);
    }
    
    /**
     * Mark all as read
     * 
     * @param int $userId
     * @return bool
     */
    public function markAllAsRead(int $userId): bool
    {
        $sql = "UPDATE notifications 
                SET is_read = 1, read_at = :read_at 
                WHERE user_id = :user_id AND is_read = 0";
        
        $stmt = $this->db->prepare($sql);
        return $stmt->execute([
            'user_id' => $userId,
            'read_at' => date('Y-m-d H:i:s')
        ]);
    }
    
    /**
     * Delete notification
     * 
     * @param int $notificationId
     * @param int $userId
     * @return bool
     */
    public function deleteNotification(int $notificationId, int $userId): bool
    {
        $notification = $this->notificationModel->find($notificationId);
        
        if (!$notification || $notification->user_id != $userId) {
            return false;
        }
        
        return $this->notificationModel->delete($notificationId);
    }
    
    /**
     * Create reminder
     * 
     * @param int $userId
     * @param string $title
     * @param string $description
     * @param string $remindAt
     * @param array $options
     * @return int|false
     */
    public function createReminder(int $userId, string $title, string $description, string $remindAt, array $options = [])
    {
        return $this->sendNotification($userId, $title, $description, [
            'type' => 'reminder',
            'priority' => $options['priority'] ?? 'medium',
            'expires_at' => $remindAt,
            'action_url' => $options['action_url'] ?? null,
            'channels' => $options['channels'] ?? ['in_app']
        ]);
    }
}