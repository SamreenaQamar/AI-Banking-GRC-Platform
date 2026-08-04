<?php
/**
 * AI Banking GRC Platform - Notification Service
 * 
 * @package    AI-Banking-GRC-Platform
 * @subpackage app/Services
 * @version    1.0.0
 * @author     GRC Platform Team
 * @copyright  2026 AI Banking GRC Platform
 * @license    Proprietary
 * 
 * This service handles notification management:
 * - Send notifications (email, in-app, SMS)
 * - Broadcast notifications
 * - Read/Unread status
 * - Notification counts
 * - Notification deletion
 */

declare(strict_types=1);

namespace App\Services;

use App\Models\Notification;
use App\Models\User;
use App\Models\ActivityLog;
use App\Libraries\Mail;
use App\Libraries\Logger;
use App\Libraries\Validator;

class NotificationService
{
    /**
     * @var Notification Notification model
     */
    private Notification $notificationModel;

    /**
     * @var User User model
     */
    private User $userModel;

    /**
     * @var ActivityLog Activity log model
     */
    private ActivityLog $activityLogModel;

    /**
     * @var Mail Mail library
     */
    private Mail $mail;

    /**
     * @var Logger Logger library
     */
    private Logger $logger;

    /**
     * @var Validator Validator library
     */
    private Validator $validator;

    /**
     * Constructor
     */
    public function __construct()
    {
        $this->notificationModel = new Notification();
        $this->userModel = new User();
        $this->activityLogModel = new ActivityLog();
        $this->mail = new Mail();
        $this->logger = new Logger();
        $this->validator = new Validator();
    }

    /**
     * Send a notification
     * 
     * @param int $userId
     * @param string $title
     * @param string $message
     * @param array $options
     * @return array
     */
    public function send(int $userId, string $title, string $message, array $options = []): array
    {
        try {
            // Validate input
            $rules = [
                'user_id' => ['required', 'exists:users,id'],
                'title' => ['required', 'min:3', 'max:255'],
                'message' => ['required', 'min:5']
            ];

            $data = ['user_id' => $userId, 'title' => $title, 'message' => $message];
            if (!$this->validator->validate($data, $rules)) {
                return $this->errorResponse('Validation failed.', 'VALIDATION_ERROR', [
                    'errors' => $this->validator->getErrors()
                ]);
            }

            // Check if user exists
            $user = $this->userModel->find($userId);
            if (!$user) {
                return $this->errorResponse('User not found.', 'USER_NOT_FOUND');
            }

            // Create notification
            $notificationId = $this->notificationModel->send($userId, $title, $message, $options);

            if (!$notificationId) {
                return $this->errorResponse('Failed to send notification.', 'SEND_FAILED');
            }

            // Send email if requested
            if (isset($options['email']) && $options['email']) {
                $this->sendEmail($user, $title, $message, $options);
            }

            // Send SMS if requested (placeholder)
            if (isset($options['sms']) && $options['sms']) {
                $this->sendSMS($user, $message);
            }

            // Log activity
            $this->activityLogModel->logAction(
                $options['created_by'] ?? 0,
                'notification_send',
                'notifications',
                "Notification sent to user {$user->username}: {$title}"
            );

            $this->logger->info('Notification sent', [
                'notification_id' => $notificationId,
                'user_id' => $userId,
                'title' => $title
            ]);

            return $this->successResponse('Notification sent successfully.', [
                'notification_id' => $notificationId
            ]);

        } catch (\Exception $e) {
            $this->logger->error('Send notification error: ' . $e->getMessage());
            return $this->errorResponse('An error occurred sending notification.', 'ERROR');
        }
    }

    /**
     * Broadcast notification to multiple users
     * 
     * @param array $userIds
     * @param string $title
     * @param string $message
     * @param array $options
     * @return array
     */
    public function broadcast(array $userIds, string $title, string $message, array $options = []): array
    {
        try {
            $successCount = 0;
            $failedCount = 0;

            foreach ($userIds as $userId) {
                $result = $this->send($userId, $title, $message, $options);
                if ($result['success']) {
                    $successCount++;
                } else {
                    $failedCount++;
                }
            }

            $this->logger->info('Notification broadcast', [
                'total' => count($userIds),
                'success' => $successCount,
                'failed' => $failedCount,
                'title' => $title
            ]);

            return $this->successResponse('Notifications broadcasted.', [
                'success_count' => $successCount,
                'failed_count' => $failedCount
            ]);

        } catch (\Exception $e) {
            $this->logger->error('Broadcast error: ' . $e->getMessage());
            return $this->errorResponse('An error occurred broadcasting notifications.', 'ERROR');
        }
    }

    /**
     * Send notification to role
     * 
     * @param string $role
     * @param string $title
     * @param string $message
     * @param array $options
     * @return array
     */
    public function sendToRole(string $role, string $title, string $message, array $options = []): array
    {
        try {
            $users = $this->userModel->getByRole($role);
            $userIds = array_map(function($user) {
                return $user->id;
            }, $users);

            return $this->broadcast($userIds, $title, $message, $options);

        } catch (\Exception $e) {
            $this->logger->error('Send to role error: ' . $e->getMessage());
            return $this->errorResponse('An error occurred.', 'ERROR');
        }
    }

    /**
     * Mark notification as read
     * 
     * @param int $notificationId
     * @param int $userId
     * @return array
     */
    public function markRead(int $notificationId, int $userId): array
    {
        try {
            $result = $this->notificationModel->markAsRead($notificationId, $userId);

            if (!$result) {
                return $this->errorResponse('Failed to mark notification as read.', 'MARK_READ_FAILED');
            }

            $this->logger->info('Notification marked as read', [
                'notification_id' => $notificationId,
                'user_id' => $userId
            ]);

            return $this->successResponse('Notification marked as read.');

        } catch (\Exception $e) {
            $this->logger->error('Mark read error: ' . $e->getMessage());
            return $this->errorResponse('An error occurred.', 'ERROR');
        }
    }

    /**
     * Mark all notifications as read
     * 
     * @param int $userId
     * @return array
     */
    public function markAllRead(int $userId): array
    {
        try {
            $result = $this->notificationModel->markAllAsRead($userId);

            if (!$result) {
                return $this->errorResponse('Failed to mark all notifications as read.', 'MARK_ALL_READ_FAILED');
            }

            $this->logger->info('All notifications marked as read', ['user_id' => $userId]);

            return $this->successResponse('All notifications marked as read.');

        } catch (\Exception $e) {
            $this->logger->error('Mark all read error: ' . $e->getMessage());
            return $this->errorResponse('An error occurred.', 'ERROR');
        }
    }

    /**
     * Delete notification
     * 
     * @param int $notificationId
     * @param int $userId
     * @return array
     */
    public function delete(int $notificationId, int $userId): array
    {
        try {
            $result = $this->notificationModel->deleteNotification($notificationId, $userId);

            if (!$result) {
                return $this->errorResponse('Failed to delete notification.', 'DELETE_FAILED');
            }

            $this->logger->info('Notification deleted', [
                'notification_id' => $notificationId,
                'user_id' => $userId
            ]);

            return $this->successResponse('Notification deleted successfully.');

        } catch (\Exception $e) {
            $this->logger->error('Delete notification error: ' . $e->getMessage());
            return $this->errorResponse('An error occurred.', 'ERROR');
        }
    }

    /**
     * Get unread count
     * 
     * @param int $userId
     * @return array
     */
    public function count(int $userId): array
    {
        try {
            $count = $this->notificationModel->countUnread($userId);

            return $this->successResponse('Unread count retrieved.', [
                'count' => $count
            ]);

        } catch (\Exception $e) {
            $this->logger->error('Count error: ' . $e->getMessage());
            return $this->errorResponse('An error occurred.', 'ERROR');
        }
    }

    /**
     * Get user notifications
     * 
     * @param int $userId
     * @param int $page
     * @param int $perPage
     * @return array
     */
    public function getUserNotifications(int $userId, int $page = 1, int $perPage = 20): array
    {
        try {
            $notifications = $this->notificationModel->getUserNotifications($userId, $page, $perPage);
            $total = $this->notificationModel->countUserNotifications($userId);
            $unread = $this->notificationModel->countUnread($userId);

            return $this->successResponse('Notifications retrieved.', [
                'notifications' => $notifications,
                'total' => $total,
                'unread' => $unread,
                'page' => $page,
                'per_page' => $perPage,
                'total_pages' => ceil($total / $perPage)
            ]);

        } catch (\Exception $e) {
            $this->logger->error('Get user notifications error: ' . $e->getMessage());
            return $this->errorResponse('An error occurred.', 'ERROR');
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
            $notifications = $this->notificationModel->getUnread($userId, $limit);

            return $this->successResponse('Unread notifications retrieved.', [
                'notifications' => $notifications,
                'count' => count($notifications)
            ]);

        } catch (\Exception $e) {
            $this->logger->error('Get unread error: ' . $e->getMessage());
            return $this->errorResponse('An error occurred.', 'ERROR');
        }
    }

    /**
     * Send email notification
     * 
     * @param object $user
     * @param string $title
     * @param string $message
     * @param array $options
     * @return void
     */
    private function sendEmail(object $user, string $title, string $message, array $options): void
    {
        try {
            if (empty($user->email)) {
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
     * Send SMS notification (placeholder)
     * 
     * @param object $user
     * @param string $message
     * @return void
     */
    private function sendSMS(object $user, string $message): void
    {
        // SMS integration placeholder
        // Implement SMS service here
        $this->logger->debug('SMS notification placeholder', [
            'user_id' => $user->id,
            'phone' => $user->mobile ?? 'N/A'
        ]);
    }

    /**
     * Success response
     * 
     * @param string $message
     * @param array $data
     * @return array
     */
    private function successResponse(string $message, array $data = []): array
    {
        return [
            'success' => true,
            'message' => $message,
            'data' => $data
        ];
    }

    /**
     * Error response
     * 
     * @param string $message
     * @param string $code
     * @param array $data
     * @return array
     */
    private function errorResponse(string $message, string $code = 'ERROR', array $data = []): array
    {
        return [
            'success' => false,
            'message' => $message,
            'code' => $code,
            'data' => $data
        ];
    }
}