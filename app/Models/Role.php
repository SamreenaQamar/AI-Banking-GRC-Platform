<?php
/**
 * AI Banking GRC Platform - Role Model
 * 
 * @package    AI-Banking-GRC-Platform
 * @version    1.0.0
 * @author     GRC Platform Team
 * @copyright  2026 AI Banking GRC Platform
 * @license    Proprietary
 * 
 * This model handles:
 * - Role CRUD operations
 * - Permission assignment
 * - User relationships
 */

declare(strict_types=1);

namespace App\Models;

use PDO;

class Role extends BaseModel
{
    /**
     * Table name
     * @var string
     */
    protected string $table = 'roles';
    
    /**
     * Fillable fields
     * @var array
     */
    protected array $fillable = [
        'name',
        'display_name',
        'description',
        'level',
        'is_system',
        'permissions'
    ];
    
    /**
     * Constructor
     */
    public function __construct()
    {
        parent::__construct();
    }
    
    /**
     * Assign permission to role
     * 
     * @param int $roleId
     * @param int $permissionId
     * @return bool
     */
    public function assignPermission(int $roleId, int $permissionId): bool
    {
        // Check if permission already assigned
        $sql = "SELECT * FROM role_permissions 
                WHERE role_id = :role_id AND permission_id = :permission_id";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([
            'role_id' => $roleId,
            'permission_id' => $permissionId
        ]);
        
        if ($stmt->fetch()) {
            return true;
        }
        
        // Assign permission
        $sql = "INSERT INTO role_permissions (role_id, permission_id, created_at) 
                VALUES (:role_id, :permission_id, :created_at)";
        $stmt = $this->db->prepare($sql);
        
        return $stmt->execute([
            'role_id' => $roleId,
            'permission_id' => $permissionId,
            'created_at' => date('Y-m-d H:i:s')
        ]);
    }
    
    /**
     * Remove permission from role
     * 
     * @param int $roleId
     * @param int $permissionId
     * @return bool
     */
    public function removePermission(int $roleId, int $permissionId): bool
    {
        $sql = "DELETE FROM role_permissions 
                WHERE role_id = :role_id AND permission_id = :permission_id";
        $stmt = $this->db->prepare($sql);
        
        return $stmt->execute([
            'role_id' => $roleId,
            'permission_id' => $permissionId
        ]);
    }
    
    /**
     * Sync permissions for role
     * 
     * @param int $roleId
     * @param array $permissionIds
     * @return bool
     */
    public function syncPermissions(int $roleId, array $permissionIds): bool
    {
        // Remove all existing permissions
        $sql = "DELETE FROM role_permissions WHERE role_id = :role_id";
        $stmt = $this->db->prepare($sql);
        $stmt->execute(['role_id' => $roleId]);
        
        // Assign new permissions
        if (empty($permissionIds)) {
            return true;
        }
        
        $values = [];
        $params = [];
        foreach ($permissionIds as $index => $permissionId) {
            $values[] = "(:role_id_{$index}, :permission_id_{$index}, :created_at_{$index})";
            $params["role_id_{$index}"] = $roleId;
            $params["permission_id_{$index}"] = $permissionId;
            $params["created_at_{$index}"] = date('Y-m-d H:i:s');
        }
        
        $sql = "INSERT INTO role_permissions (role_id, permission_id, created_at) VALUES " 
               . implode(', ', $values);
        
        $stmt = $this->db->prepare($sql);
        return $stmt->execute($params);
    }
    
    /**
     * Get users with this role
     * 
     * @param int $roleId
     * @return array
     */
    public function getUsers(int $roleId): array
    {
        $sql = "SELECT u.* FROM users u 
                WHERE u.role_id = :role_id 
                AND u.deleted_at IS NULL";
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute(['role_id' => $roleId]);
        
        return $stmt->fetchAll(PDO::FETCH_OBJ);
    }
    
    /**
     * Get permissions for role
     * 
     * @param int $roleId
     * @return array
     */
    public function getPermissions(int $roleId): array
    {
        $sql = "SELECT p.* FROM permissions p 
                INNER JOIN role_permissions rp ON rp.permission_id = p.id 
                WHERE rp.role_id = :role_id 
                AND p.deleted_at IS NULL 
                ORDER BY p.module, p.display_name";
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute(['role_id' => $roleId]);
        
        return $stmt->fetchAll(PDO::FETCH_OBJ);
    }
    
    /**
     * Get permissions grouped by module
     * 
     * @param int $roleId
     * @return array
     */
    public function getPermissionsGrouped(int $roleId): array
    {
        $permissions = $this->getPermissions($roleId);
        $grouped = [];
        
        foreach ($permissions as $permission) {
            $module = $permission->module ?? 'general';
            if (!isset($grouped[$module])) {
                $grouped[$module] = [];
            }
            $grouped[$module][] = $permission;
        }
        
        return $grouped;
    }
    
    /**
     * Check if role has permission
     * 
     * @param int $roleId
     * @param string $permission
     * @return bool
     */
    public function hasPermission(int $roleId, string $permission): bool
    {
        $permissions = $this->getPermissions($roleId);
        
        foreach ($permissions as $perm) {
            if ($perm->name === $permission || $perm->name === '*') {
                return true;
            }
        }
        
        return false;
    }
    
    /**
     * Get system roles
     * 
     * @return array
     */
    public function getSystemRoles(): array
    {
        return $this->where(['is_system' => 1]);
    }
    
    /**
     * Get roles by level
     * 
     * @param int $level
     * @param string $operator
     * @return array
     */
    public function getByLevel(int $level, string $operator = '>='): array
    {
        $sql = "SELECT * FROM {$this->table} 
                WHERE level {$operator} :level 
                AND deleted_at IS NULL 
                ORDER BY level DESC";
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute(['level' => $level]);
        
        return $stmt->fetchAll(PDO::FETCH_OBJ);
    }
}