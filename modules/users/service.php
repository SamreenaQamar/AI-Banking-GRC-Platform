<?php
/**
 * Users Module - Service Layer
 * 
 * @package    AI-Banking-GRC-Platform
 * @subpackage modules/users
 * @version    1.0.0
 * @author     GRC Platform Team
 * @copyright  2026 AI Banking GRC Platform
 * @license    Proprietary
 * 
 * This service handles user operations:
 * - User CRUD
 * - Role management
 * - Permission management
 * - Profile management
 * - Activity logging
 */

declare(strict_types=1);

namespace Modules\Users\Services;

use App\Models\User;
use App\Models\Role;
use App\Models\ActivityLog;
use App\Helpers\Auth;
use App\Helpers\Database;
use App\Helpers\Password;
use Exception;
use PDO;

class UserService
{
    /**
     * @var PDO
     */
    private PDO $db;
    
    /**
     * @var User
     */
    private User $userModel;
    
    /**
     * @var Role
     */
    private Role $roleModel;
    
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
        $this->userModel = new User();
        $this->roleModel = new Role();
        $this->activityLogModel = new ActivityLog();
    }
    
    /**
     * Get user statistics
     * 
     * @return array
     */
    public function getUserStats(): array
    {
        $total = $this->userModel->countAll();
        $active = $this->userModel->countByStatus('active');
        $inactive = $this->userModel->countByStatus('inactive');
        $suspended = $this->userModel->countByStatus('suspended');
        $pending = $this->userModel->countByStatus('pending');
        
        $todayActive = $this->userModel->countActiveToday();
        $byRole = $this->userModel->countByRole();
        
        return [
            'total' => $total,
            'active' => $active,
            'inactive' => $inactive,
            'suspended' => $suspended,
            'pending' => $pending,
            'today_active' => $todayActive,
            'by_role' => $byRole
        ];
    }
    
    /**
     * Get dashboard data
     * 
     * @return array
     */
    public function getDashboardData(): array
    {
        $stats = $this->getUserStats();
        $recentUsers = $this->getRecentUsers(5);
        $recentActivities = $this->getRecentActivities();
        
        return [
            'stats' => $stats,
            'recent_users' => $recentUsers,
            'recent_activities' => $recentActivities
        ];
    }
    
    /**
     * Get recent users
     * 
     * @param int $limit
     * @return array
     */
    public function getRecentUsers(int $limit = 5): array
    {
        $sql = "SELECT u.*, 
                       r.display_name as role_name
                FROM users u
                LEFT JOIN roles r ON r.id = u.role_id
                WHERE u.deleted_at IS NULL
                ORDER BY u.created_at DESC
                LIMIT :limit";
        
        $stmt = $this->db->prepare($sql);
        $stmt->bindParam('limit', $limit, PDO::PARAM_INT);
        $stmt->execute();
        
        return $stmt->fetchAll(PDO::FETCH_OBJ);
    }
    
    /**
     * Get recent activities
     * 
     * @param int $limit
     * @return array
     */
    public function getRecentActivities(int $limit = 5): array
    {
        $sql = "SELECT al.*, 
                       u.username, 
                       u.full_name
                FROM activity_logs al
                LEFT JOIN users u ON u.id = al.user_id
                ORDER BY al.created_at DESC
                LIMIT :limit";
        
        $stmt = $this->db->prepare($sql);
        $stmt->bindParam('limit', $limit, PDO::PARAM_INT);
        $stmt->execute();
        
        return $stmt->fetchAll(PDO::FETCH_OBJ);
    }
    
    /**
     * Create user
     * 
     * @param array $data
     * @param int $createdBy
     * @return int|false
     */
    public function createUser(array $data, int $createdBy)
    {
        // Validate unique fields
        if ($this->userModel->findByUsername($data['username'])) {
            throw new Exception('Username already exists.');
        }
        
        if ($this->userModel->findByEmail($data['email'])) {
            throw new Exception('Email already exists.');
        }
        
        // Hash password
        $hashedPassword = Password::hash($data['password']);
        
        $userData = [
            'username' => $data['username'],
            'email' => $data['email'],
            'first_name' => $data['first_name'],
            'last_name' => $data['last_name'],
            'password_hash' => $hashedPassword,
            'role_id' => $data['role_id'] ?? 7,
            'department_id' => $data['department_id'] ?? null,
            'status' => $data['status'] ?? user_setting('default_status', 'pending'),
            'employee_id' => $data['employee_id'] ?? null,
            'phone' => $data['phone'] ?? null,
            'mobile' => $data['mobile'] ?? null,
            'address' => $data['address'] ?? null,
            'city' => $data['city'] ?? null,
            'state' => $data['state'] ?? null,
            'postal_code' => $data['postal_code'] ?? null,
            'country' => $data['country'] ?? 'Pakistan',
            'created_by' => $createdBy,
            'created_at' => date('Y-m-d H:i:s')
        ];
        
        $userId = $this->userModel->create($userData);
        
        if ($userId) {
            $this->logActivity('user_create', "Created user: {$data['username']}", $createdBy);
        }
        
        return $userId;
    }
    
    /**
     * Update user
     * 
     * @param int $userId
     * @param array $data
     * @param int $updatedBy
     * @return bool
     */
    public function updateUser(int $userId, array $data, int $updatedBy): bool
    {
        $user = $this->userModel->find($userId);
        
        if (!$user) {
            throw new Exception('User not found.');
        }
        
        // Check unique constraints
        if (isset($data['username']) && $data['username'] !== $user->username) {
            if ($this->userModel->findByUsername($data['username'])) {
                throw new Exception('Username already exists.');
            }
        }
        
        if (isset($data['email']) && $data['email'] !== $user->email) {
            if ($this->userModel->findByEmail($data['email'])) {
                throw new Exception('Email already exists.');
            }
        }
        
        // If password is being updated
        if (isset($data['password']) && !empty($data['password'])) {
            $data['password_hash'] = Password::hash($data['password']);
            unset($data['password']);
        }
        
        $data['updated_at'] = date('Y-m-d H:i:s');
        
        $result = $this->userModel->update($userId, $data);
        
        if ($result) {
            $this->logActivity('user_update', "Updated user: {$user->username}", $updatedBy);
        }
        
        return $result;
    }
    
    /**
     * Delete user
     * 
     * @param int $userId
     * @param int $deletedBy
     * @return bool
     */
    public function deleteUser(int $userId, int $deletedBy): bool
    {
        $user = $this->userModel->find($userId);
        
        if (!$user) {
            throw new Exception('User not found.');
        }
        
        // Prevent deleting own account
        if ($userId === $deletedBy) {
            throw new Exception('Cannot delete your own account.');
        }
        
        $result = $this->userModel->softDelete($userId);
        
        if ($result) {
            $this->logActivity('user_delete', "Deleted user: {$user->username}", $deletedBy);
        }
        
        return $result;
    }
    
    /**
     * Update user status
     * 
     * @param int $userId
     * @param string $status
     * @param int $updatedBy
     * @return bool
     */
    public function updateUserStatus(int $userId, string $status, int $updatedBy): bool
    {
        $user = $this->userModel->find($userId);
        
        if (!$user) {
            throw new Exception('User not found.');
        }
        
        if (!array_key_exists($status, USER_STATUS)) {
            throw new Exception('Invalid status.');
        }
        
        $result = $this->userModel->update($userId, [
            'status' => $status,
            'updated_at' => date('Y-m-d H:i:s')
        ]);
        
        if ($result) {
            $this->logActivity('user_status', "Updated user status: {$user->username} -> {$status}", $updatedBy);
        }
        
        return $result;
    }
    
    /**
     * Assign role to user
     * 
     * @param int $userId
     * @param int $roleId
     * @param int $assignedBy
     * @return bool
     */
    public function assignRole(int $userId, int $roleId, int $assignedBy): bool
    {
        $user = $this->userModel->find($userId);
        $role = $this->roleModel->find($roleId);
        
        if (!$user) {
            throw new Exception('User not found.');
        }
        
        if (!$role) {
            throw new Exception('Role not found.');
        }
        
        $result = $this->userModel->update($userId, [
            'role_id' => $roleId,
            'updated_at' => date('Y-m-d H:i:s')
        ]);
        
        if ($result) {
            $this->logActivity('user_role', "Assigned role {$role->name} to user: {$user->username}", $assignedBy);
        }
        
        return $result;
    }
    
    /**
     * Get user permissions
     * 
     * @param int $userId
     * @return array
     */
    public function getUserPermissions(int $userId): array
    {
        return $this->userModel->getPermissions($userId);
    }
    
    /**
     * Check user permission
     * 
     * @param int $userId
     * @param string $permission
     * @return bool
     */
    public function hasPermission(int $userId, string $permission): bool
    {
        return $this->userModel->hasPermission($userId, $permission);
    }
    
    /**
     * Log activity
     * 
     * @param string $action
     * @param string $description
     * @param int $userId
     * @return void
     */
    private function logActivity(string $action, string $description, int $userId): void
    {
        $sql = "INSERT INTO activity_logs (user_id, action, module, description, created_at) 
                VALUES (:user_id, :action, 'users', :description, :created_at)";
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute([
            'user_id' => $userId,
            'action' => $action,
            'description' => $description,
            'created_at' => date('Y-m-d H:i:s')
        ]);
    }
}