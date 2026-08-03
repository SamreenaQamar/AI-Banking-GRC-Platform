<?php
/**
 * AI Banking GRC Platform - User Model
 * 
 * @package    AI-Banking-GRC-Platform
 * @version    1.0.0
 * @author     GRC Platform Team
 * @copyright  2026 AI Banking GRC Platform
 * @license    Proprietary
 * 
 * This model handles:
 * - User authentication
 * - Profile management
 * - Role and permission relationships
 * - Activity logging
 * - Notification management
 */

declare(strict_types=1);

namespace App\Models;

use PDO;
use App\Helpers\Password;

class User extends BaseModel
{
    /**
     * Table name
     * @var string
     */
    protected string $table = 'users';
    
    /**
     * Fillable fields
     * @var array
     */
    protected array $fillable = [
        'employee_id',
        'username',
        'email',
        'first_name',
        'last_name',
        'full_name',
        'password_hash',
        'phone',
        'mobile',
        'department_id',
        'role_id',
        'status',
        'email_verified',
        'mobile_verified',
        'two_factor_enabled',
        'two_factor_secret',
        'profile_image',
        'last_login',
        'last_login_ip',
        'login_attempts',
        'locked_until',
        'remember_token',
        'api_token',
        'created_by'
    ];
    
    /**
     * Hidden fields
     * @var array
     */
    protected array $hidden = [
        'password_hash',
        'two_factor_secret',
        'remember_token',
        'api_token'
    ];
    
    /**
     * Constructor
     */
    public function __construct()
    {
        parent::__construct();
    }
    
    /**
     * Authenticate user by credentials
     * 
     * @param string $username
     * @param string $password
     * @return object|false
     */
    public function authenticate(string $username, string $password)
    {
        $sql = "SELECT * FROM {$this->table} 
                WHERE (username = :username OR email = :username) 
                AND deleted_at IS NULL 
                LIMIT 1";
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute(['username' => $username]);
        $user = $stmt->fetch(PDO::FETCH_OBJ);
        
        if ($user && Password::verify($password, $user->password_hash)) {
            // Update last login
            $this->update($user->id, [
                'last_login' => date('Y-m-d H:i:s'),
                'last_login_ip' => $_SERVER['REMOTE_ADDR'] ?? null,
                'login_attempts' => 0
            ]);
            
            return $user;
        }
        
        // Increment login attempts
        if ($user) {
            $this->incrementLoginAttempts($user->id);
        }
        
        return false;
    }
    
    /**
     * Increment login attempts
     * 
     * @param int $userId
     * @return void
     */
    private function incrementLoginAttempts(int $userId): void
    {
        $sql = "UPDATE {$this->table} SET login_attempts = login_attempts + 1 
                WHERE id = :id";
        $stmt = $this->db->prepare($sql);
        $stmt->execute(['id' => $userId]);
        
        // Lock account after 5 failed attempts
        $sql = "SELECT login_attempts FROM {$this->table} WHERE id = :id";
        $stmt = $this->db->prepare($sql);
        $stmt->execute(['id' => $userId]);
        $attempts = (int)$stmt->fetchColumn();
        
        if ($attempts >= 5) {
            $this->update($userId, [
                'locked_until' => date('Y-m-d H:i:s', strtotime('+15 minutes'))
            ]);
        }
    }
    
    /**
     * Logout user
     * 
     * @param int $userId
     * @return void
     */
    public function logout(int $userId): void
    {
        $this->update($userId, [
            'remember_token' => null
        ]);
    }
    
    /**
     * Change user password
     * 
     * @param int $userId
     * @param string $currentPassword
     * @param string $newPassword
     * @return bool
     */
    public function changePassword(int $userId, string $currentPassword, string $newPassword): bool
    {
        $user = $this->find($userId);
        
        if (!$user || !Password::verify($currentPassword, $user->password_hash)) {
            return false;
        }
        
        $hashedPassword = Password::hash($newPassword);
        return $this->update($userId, [
            'password_hash' => $hashedPassword
        ]);
    }
    
    /**
     * Update user profile
     * 
     * @param int $userId
     * @param array $data
     * @return bool
     */
    public function updateProfile(int $userId, array $data): bool
    {
        $allowedFields = [
            'first_name',
            'last_name',
            'email',
            'phone',
            'mobile',
            'address',
            'city',
            'state',
            'postal_code',
            'country',
            'profile_image'
        ];
        
        $data = array_intersect_key($data, array_flip($allowedFields));
        
        // Check unique email
        if (isset($data['email'])) {
            $existing = $this->findBy('email', $data['email']);
            if ($existing && $existing->id !== $userId) {
                return false;
            }
        }
        
        return $this->update($userId, $data);
    }
    
    /**
     * Assign role to user
     * 
     * @param int $userId
     * @param int $roleId
     * @return bool
     */
    public function assignRole(int $userId, int $roleId): bool
    {
        $roleModel = new Role();
        $role = $roleModel->find($roleId);
        
        if (!$role) {
            return false;
        }
        
        return $this->update($userId, ['role_id' => $roleId]);
    }
    
    /**
     * Get user's role
     * 
     * @param int $userId
     * @return object|null
     */
    public function getRole(int $userId): ?object
    {
        $sql = "SELECT r.* FROM roles r 
                INNER JOIN {$this->table} u ON u.role_id = r.id 
                WHERE u.id = :user_id AND r.deleted_at IS NULL";
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute(['user_id' => $userId]);
        $result = $stmt->fetch(PDO::FETCH_OBJ);
        
        return $result ?: null;
    }
    
    /**
     * Get user's permissions
     * 
     * @param int $userId
     * @return array
     */
    public function getPermissions(int $userId): array
    {
        $sql = "SELECT p.* FROM permissions p 
                INNER JOIN role_permissions rp ON rp.permission_id = p.id 
                INNER JOIN {$this->table} u ON u.role_id = rp.role_id 
                WHERE u.id = :user_id AND p.deleted_at IS NULL 
                GROUP BY p.id";
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute(['user_id' => $userId]);
        
        return $stmt->fetchAll(PDO::FETCH_OBJ);
    }
    
    /**
     * Check if user has permission
     * 
     * @param int $userId
     * @param string $permission
     * @return bool
     */
    public function hasPermission(int $userId, string $permission): bool
    {
        $permissions = $this->getPermissions($userId);
        
        foreach ($permissions as $perm) {
            if ($perm->name === $permission || $perm->name === '*') {
                return true;
            }
        }
        
        return false;
    }
    
    /**
     * Get user's department
     * 
     * @param int $userId
     * @return object|null
     */
    public function getDepartment(int $userId): ?object
    {
        $sql = "SELECT d.* FROM departments d 
                INNER JOIN {$this->table} u ON u.department_id = d.id 
                WHERE u.id = :user_id AND d.deleted_at IS NULL";
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute(['user_id' => $userId]);
        $result = $stmt->fetch(PDO::FETCH_OBJ);
        
        return $result ?: null;
    }
    
    /**
     * Get user's activity logs
     * 
     * @param int $userId
     * @param int $limit
     * @return array
     */
    public function getActivityLogs(int $userId, int $limit = 10): array
    {
        $sql = "SELECT * FROM activity_logs 
                WHERE user_id = :user_id 
                ORDER BY created_at DESC 
                LIMIT :limit";
        
        $stmt = $this->db->prepare($sql);
        $stmt->bindValue('user_id', $userId, PDO::PARAM_INT);
        $stmt->bindValue('limit', $limit, PDO::PARAM_INT);
        $stmt->execute();
        
        return $stmt->fetchAll(PDO::FETCH_OBJ);
    }
    
    /**
     * Get user's notifications
     * 
     * @param int $userId
     * @param int $limit
     * @return array
     */
    public function getNotifications(int $userId, int $limit = 10): array
    {
        $sql = "SELECT * FROM notifications 
                WHERE user_id = :user_id 
                ORDER BY created_at DESC 
                LIMIT :limit";
        
        $stmt = $this->db->prepare($sql);
        $stmt->bindValue('user_id', $userId, PDO::PARAM_INT);
        $stmt->bindValue('limit', $limit, PDO::PARAM_INT);
        $stmt->execute();
        
        return $stmt->fetchAll(PDO::FETCH_OBJ);
    }
    
    /**
     * Get unread notifications count
     * 
     * @param int $userId
     * @return int
     */
    public function getUnreadNotificationsCount(int $userId): int
    {
        $sql = "SELECT COUNT(*) FROM notifications 
                WHERE user_id = :user_id AND is_read = 0";
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute(['user_id' => $userId]);
        
        return (int)$stmt->fetchColumn();
    }
    
    /**
     * Get users by status
     * 
     * @param string $status
     * @return array
     */
    public function getByStatus(string $status): array
    {
        return $this->where(['status' => $status]);
    }
    
    /**
     * Get active users
     * 
     * @return array
     */
    public function getActive(): array
    {
        return $this->getByStatus('active');
    }
    
    /**
     * Count users by role
     * 
     * @param int $roleId
     * @return int
     */
    public function countByRole(int $roleId): int
    {
        return $this->count(['role_id' => $roleId]);
    }
    
    /**
     * Search users
     * 
     * @param string $query
     * @return array
     */
    public function search(string $query): array
    {
        $sql = "SELECT * FROM {$this->table} 
                WHERE (first_name LIKE :query 
                OR last_name LIKE :query 
                OR email LIKE :query 
                OR username LIKE :query 
                OR employee_id LIKE :query) 
                AND deleted_at IS NULL";
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute(['query' => '%' . $query . '%']);
        
        return $stmt->fetchAll(PDO::FETCH_OBJ);
    }
}