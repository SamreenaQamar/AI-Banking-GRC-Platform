<?php
/**
 * AI Banking GRC Platform - Notification Library
 * 
 * @package    AI-Banking-GRC-Platform
 * @subpackage app/Libraries
 * @version    1.0.0
 * @author     GRC Platform Team
 * @copyright  2026 AI Banking GRC Platform
 * @license    Proprietary
 * 
 * This library provides enterprise notification functionality:
 * - Database notifications
 * - Email notifications
 * - Role-based notifications
 * - Unread count
 * - Mark as read
 * - Notification types
 */

declare(strict_types=1);

namespace App\Libraries;

use App\Models\Notification as NotificationModel;
use App\Libraries\Mail;
use App\Libraries\Logger;

class Notification
{
    /**
     * @var NotificationModel Notification model
     */
    private NotificationModel $notificationModel;

    /**
     * @var Mail Mail instance
     */
    private Mail $mail;

    /**
     * @var Logger Logger instance
     */
    private Logger $logger;

    /**
     * Constructor
     */
    public function __construct()
    {
        $this->notificationModel = new NotificationModel();
        $this->mail = new Mail();
        $this->logger = new Logger();
    }

    /**
     * Send notification to user
     * 
     * @param int $userId
     * @param string $title
     * @param string $message
     * @param array $options
     * @return int|false
     */
    public function sendUser(int $userId, string $title, string $message, array $options = []): int|false
    {
        try {
            // Save to database
            $notificationId = $this->notificationModel->send($userId, $title, $message, $options);

            // Send email if enabled
            if ($options['email'] ?? false) {
                $this->sendEmail($userId, $title, $message, $options);
            }

            $this->logger->info('Notification sent to user', [
                'user_id' => $userId,
                'title' => $title
            ]);

            return $notificationId;

        } catch (\Exception $e) {
            $this->logger->error('Send notification error: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Send notification to role
     * 
     * @param string $role
     * @param string $title
     * @param string $message
     * @param array $options
     * @return int
     */
    public function sendRole(string $role, string $title, string $message, array $options = []): int
    {
        try {
            $userModel = new \App\Models\User();
            $users = $userModel->getByRole($role);

            $count = 0;
            foreach ($users as $user) {
                $result = $this->sendUser($user->id, $title, $message, $options);
                if ($result) {
                    $count++;
                }
            }

            $this->logger->info('Notification sent to role', [
                'role' => $role,
                'count' => $count
            ]);

            return $count;

        } catch (\Exception $e) {
            $this->logger->error('Send role notification error: ' . $e->getMessage());
            return 0;
        }
    }

    /**
     * Send notification to all users
     * 
     * @param string $title
     * @param string $message
     * @param array $options
     * @return int
     */
    public function sendAll(string $title, string $message, array $options = []): int
    {
        try {
            $userModel = new \App\Models\User();
            $users = $userModel->getAll();

            $count = 0;
            foreach ($users as $user) {
                $result = $this->sendUser($user->id, $title, $message, $options);
                if ($result) {
                    $count++;
                }
            }

            $this->logger->info('Notification sent to all users', [
                'count' => $count
            ]);

            return $count;

        } catch (\Exception $e) {
            $this->logger->error('Send all notification error: ' . $e->getMessage());
            return 0;
        }
    }

    /**
     * Mark notification as read
     * 
     * @param int $notificationId
     * @param int $userId
     * @return bool
     */
    public function markRead(int $notificationId, int $userId): bool
    {
        try {
            return $this->notificationModel->markAsRead($notificationId, $userId);
        } catch (\Exception $e) {
            $this->logger->error('Mark read error: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Mark all notifications as read
     * 
     * @param int $userId
     * @return bool
     */
    public function markAllRead(int $userId): bool
    {
        try {
            return $this->notificationModel->markAllAsRead($userId);
        } catch (\Exception $e) {
            $this->logger->error('Mark all read error: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Get unread count
     * 
     * @param int $userId
     * @return int
     */
    public function count(int $userId): int
    {
        try {
            return $this->notificationModel->countUnread($userId);
        } catch (\Exception $e) {
            $this->logger->error('Count unread error: ' . $e->getMessage());
            return 0;
        }
    }

    /**
     * Get notifications for user
     * 
     * @param int $userId
     * @param int $limit
     * @param int $offset
     * @return array
     */
    public function getForUser(int $userId, int $limit = 20, int $offset = 0): array
    {
        try {
            return $this->notificationModel->getUserNotifications($userId, $limit, $offset);
        } catch (\Exception $e) {
            $this->logger->error('Get notifications error: ' . $e->getMessage());
            return [];
        }
    }

    /**
     * Get unread notifications
     * 
     * @param int $userId
     * @param int $limit
     * @return array
     */
    public function getUnread(int $userId, int $limit = 10): array
    {
        try {
            return $this->notificationModel->getUnread($userId, $limit);
        } catch (\Exception $e) {
            $this->logger->error('Get unread notifications error: ' . $e->getMessage());
            return [];
        }
    }

    /**
     * Delete notification
     * 
     * @param int $notificationId
     * @param int $userId
     * @return bool
     */
    public function delete(int $notificationId, int $userId): bool
    {
        try {
            return $this->notificationModel->deleteNotification($notificationId, $userId);
        } catch (\Exception $e) {
            $this->logger->error('Delete notification error: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Delete all notifications for user
     * 
     * @param int $userId
     * @return bool
     */
    public function deleteAll(int $userId): bool
    {
        try {
            return $this->notificationModel->deleteAll($userId);
        } catch (\Exception $e) {
            $this->logger->error('Delete all notifications error: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Send email notification
     * 
     * @param int $userId
     * @param string $title
     * @param string $message
     * @param array $options
     * @return void
     */
    private function sendEmail(int $userId, string $title, string $message, array $options): void
    {
        try {
            $userModel = new \App\Models\User();
            $user = $userModel->find($userId);

            if (!$user || empty($user->email)) {
                return;
            }

            $this->mail->sendNotification(
                $user->email,
                $title,
                $message,
                [
                    'user_name' => $user->first_name,
                    'app_name' => APP_NAME,
                    'action_url' => $options['action_url'] ?? null
                ]
            );

        } catch (\Exception $e) {
            $this->logger->error('Send email notification error: ' . $e->getMessage());
        }
    }

    /**
     * Create notification types
     * 
     * @return array
     */
    public function getTypes(): array
    {
        return [
            'compliance' => 'Compliance Alert',
            'risk' => 'Risk Alert',
            'audit' => 'Audit Notification',
            'policy' => 'Policy Update',
            'sbp' => 'SBP Circular',
            'system' => 'System Notification',
            'task' => 'Task Assignment',
            'reminder' => 'Reminder',
            'alert' => 'Alert'
        ];
    }

    /**
     * Get notification priority colors
     * 
     * @return array
     */
    public function getPriorityColors(): array
    {
        return [
            'critical' => '#DC2626',
            'high' => '#EF4444',
            'medium' => '#F59E0B',
            'low' => '#22C55E'
        ];
    }

    /**
     * Get notification icon
     * 
     * @param string $type
     * @return string
     */
    public function getIcon(string $type): string
    {
        $icons = [
            'compliance' => 'fa-check-circle',
            'risk' => 'fa-exclamation-triangle',
            'audit' => 'fa-clipboard-list',
            'policy' => 'fa-file-contract',
            'sbp' => 'fa-newspaper',
            'system' => 'fa-server',
            'task' => 'fa-tasks',
            'reminder' => 'fa-clock',
            'alert' => 'fa-bell'
        ];

        return $icons[$type] ?? 'fa-bell';
    }
}