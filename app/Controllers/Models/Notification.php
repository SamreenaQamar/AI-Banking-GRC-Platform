<?php
/**
 * AI Banking GRC Platform - Notification Model
 * 
 * @package    AI-Banking-GRC-Platform
 * @version    1.0.0
 * @author     GRC Platform Team
 * @copyright  2026 AI Banking GRC Platform
 * @license    Proprietary
 * 
 * This model handles:
 * - Notification creation and delivery
 * - Read/unread status
 * - Notification preferences
 */

declare(strict_types=1);

namespace App\Models;

use PDO;

class Notification extends BaseModel
{
    /**
     * Table name
     * @var string
     */
    protected string $table = 'notifications';
    
    /**
     * Fillable fields
     * @var array
     */
    protected array $fillable = [
        'user_id',
        'title',
        'message',
        'type',
        'is_read',
        'read_at',
        'action_url',
        'action_label',
        'priority',
        'expires_at'
    ];
    
    /**
     * Constructor
     */
    public function __construct()
    {
        parent::__construct();
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
    public function send(int $userId, string $title, string $message, array $options = [])
    {
        $data = [
            'user_id' => $userId,
            'title' => $title,
            'message' => $message,
            'type' => $options['type'] ?? 'info',
            'priority' => $options['priority'] ?? 'medium',
            'action_url' => $options['action_url'] ?? null,
            'action_label' => $options['action_label'] ?? null,
            'expires_at' => $options['expires_at'] ?? null,
            'created_by' => $options['created_by'] ?? null,
            'created_at' => date('Y-m-d H:i:s')
        ];
        
        return $this->create($data);
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
    public function sendToMany(array $userIds, string $title, string $message, array $options = []): int
    {
        $count = 0;
        
        foreach ($userIds as $userId) {
            $result = $this->send($userId, $title, $message, $options);
            if ($result) {
                $count++;
            }
        }
        
        return $count;
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
        $notification = $this->find($notificationId);
        
        if (!$notification || $notification->user_id != $userId) {
            return false;
        }
        
        return $this->update($notificationId, [
            'is_read' => 1,
            'read_at' => date('Y-m-d H:i:s')
        ]);
    }
    
    /**
     * Mark all notifications as read
     * 
     * @param int $userId
     * @return bool
     */
    public function markAllRead(int $userId): bool
    {
        $sql = "UPDATE {$this->table} 
                SET is_read = 1, read_at = :read_at 
                WHERE user_id = :user_id AND is_read = 0";
        
        $stmt = $this->db->prepare($sql);
        return $stmt->execute([
            'user_id' => $userId,
            'read_at' => date('Y-m-d H:i:s')
        ]);
    }
    
    /**
     * Get unread notifications for user
     * 
     * @param int $userId
     * @param int $limit
     * @return array
     */
    public function getUnread(int $userId, int $limit = 50): array
    {
        $sql = "SELECT * FROM {$this->table} 
                WHERE user_id = :user_id AND is_read = 0 
                AND (expires_at IS NULL OR expires_at > NOW()) 
                ORDER BY priority ASC, created_at DESC 
                LIMIT :limit";
        
        $stmt = $this->db->prepare($sql);
        $stmt->bindValue('user_id', $userId, PDO::PARAM_INT);
        $stmt->bindValue('limit', $limit, PDO::PARAM_INT);
        $stmt->execute();
        
        return $stmt->fetchAll(PDO::FETCH_OBJ);
    }
    
    /**
     * Get unread count
     * 
     * @param int $userId
     * @return int
     */
    public function getUnreadCount(int $userId): int
    {
        $sql = "SELECT COUNT(*) FROM {$this->table} 
                WHERE user_id = :user_id AND is_read = 0 
                AND (expires_at IS NULL OR expires_at > NOW())";
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute(['user_id' => $userId]);
        
        return (int)$stmt->fetchColumn();
    }
}