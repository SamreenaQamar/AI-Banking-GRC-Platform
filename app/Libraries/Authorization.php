<?php
/**
 * AI Banking GRC Platform - Authorization Library
 * 
 * @package    AI-Banking-GRC-Platform
 * @subpackage app/Libraries
 * @version    1.0.0
 * @author     GRC Platform Team
 * @copyright  2026 AI Banking GRC Platform
 * @license    Proprietary
 * 
 * This library provides enterprise Role-Based Access Control (RBAC):
 * - Permission checking
 * - Role checking with hierarchy
 * - Multiple roles and permissions support
 * - Dynamic permission loading
 * - Role assignment/removal
 * - Permission assignment/removal
 * - Authorization middleware support
 */

declare(strict_types=1);

namespace App\Libraries;

use App\Models\User;
use App\Models\Role;
use App\Models\Permission;
use App\Libraries\Authentication;
use App\Libraries\Cache;
use App\Libraries\Logger;

class Authorization
{
    /**
     * @var Authentication Authentication instance
     */
    private Authentication $auth;

    /**
     * @var Cache Cache instance
     */
    private Cache $cache;

    /**
     * @var Logger Logger instance
     */
    private Logger $logger;

    /**
     * @var User User model
     */
    private User $userModel;

    /**
     * @var Role Role model
     */
    private Role $roleModel;

    /**
     * @var Permission Permission model
     */
    private Permission $permissionModel;

    /**
     * @var array Permission cache
     */
    private array $permissionCache = [];

    /**
     * @var array User permission cache
     */
    private array $userPermissionCache = [];

    /**
     * @var int Cache lifetime in seconds
     */
    private int $cacheLifetime = 3600;

    /**
     * Constructor
     */
    public function __construct()
    {
        $this->auth = new Authentication();
        $this->cache = new Cache();
        $this->logger = new Logger();
        $this->userModel = new User();
        $this->roleModel = new Role();
        $this->permissionModel = new Permission();
    }

    /**
     * Check if user can perform action
     * 
     * @param string $permission
     * @param int|null $userId
     * @return bool
     */
    public function can(string $permission, ?int $userId = null): bool
    {
        if ($userId === null) {
            if (!$this->auth->check()) {
                return false;
            }
            $userId = $this->auth->id();
        }

        return $this->userHasPermission($userId, $permission);
    }

    /**
     * Check if user cannot perform action
     * 
     * @param string $permission
     * @param int|null $userId
     * @return bool
     */
    public function cannot(string $permission, ?int $userId = null): bool
    {
        return !$this->can($permission, $userId);
    }

    /**
     * Check if user has role
     * 
     * @param string|array $roles
     * @param int|null $userId
     * @return bool
     */
    public function hasRole($roles, ?int $userId = null): bool
    {
        if ($userId === null) {
            return $this->auth->hasRole($roles);
        }

        $user = $this->userModel->find($userId);
        if (!$user) {
            return false;
        }

        if (is_array($roles)) {
            return in_array($user->role_id, $roles);
        }

        return $user->role_id === $roles;
    }

    /**
     * Check if user has permission
     * 
     * @param int $userId
     * @param string $permission
     * @return bool
     */
    public function userHasPermission(int $userId, string $permission): bool
    {
        // Check cache
        $cacheKey = $userId . '_' . $permission;
        if (isset($this->userPermissionCache[$cacheKey])) {
            return $this->userPermissionCache[$cacheKey];
        }

        // Check cache storage
        $cacheKey = 'user_perm_' . md5($userId . $permission);
        if ($this->cache->has($cacheKey)) {
            $result = $this->cache->get($cacheKey);
            $this->userPermissionCache[$cacheKey] = $result;
            return $result;
        }

        // Get user permissions
        $permissions = $this->getUserPermissions($userId);

        // Check if user has permission
        $hasPermission = in_array($permission, $permissions) || in_array('*', $permissions);

        // Cache result
        $this->userPermissionCache[$cacheKey] = $hasPermission;
        $this->cache->put($cacheKey, $hasPermission, $this->cacheLifetime);

        return $hasPermission;
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
        try {
            $role = $this->roleModel->find($roleId);
            if (!$role) {
                return false;
            }

            $user = $this->userModel->find($userId);
            if (!$user) {
                return false;
            }

            $result = $this->userModel->update($userId, ['role_id' => $roleId]);

            if ($result) {
                // Clear cache
                $this->clearUserCache($userId);
                $this->logger->info('Role assigned to user', [
                    'user_id' => $userId,
                    'role_id' => $roleId,
                    'role_name' => $role->name
                ]);
            }

            return $result;

        } catch (\Exception $e) {
            $this->logger->error('Assign role error: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Remove role from user
     * 
     * @param int $userId
     * @param int $roleId
     * @return bool
     */
    public function removeRole(int $userId, int $roleId): bool
    {
        try {
            $user = $this->userModel->find($userId);
            if (!$user) {
                return false;
            }

            if ($user->role_id != $roleId) {
                return false;
            }

            // Assign default role
            $defaultRole = $this->roleModel->findByName('user');
            if (!$defaultRole) {
                return false;
            }

            $result = $this->userModel->update($userId, ['role_id' => $defaultRole->id]);

            if ($result) {
                // Clear cache
                $this->clearUserCache($userId);
                $this->logger->info('Role removed from user', [
                    'user_id' => $userId,
                    'role_id' => $roleId
                ]);
            }

            return $result;

        } catch (\Exception $e) {
            $this->logger->error('Remove role error: ' . $e->getMessage());
            return false;
        }
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
        try {
            $result = $this->roleModel->assignPermission($roleId, $permissionId);

            if ($result) {
                // Clear cache
                $this->clearRoleCache($roleId);
                $this->logger->info('Permission assigned to role', [
                    'role_id' => $roleId,
                    'permission_id' => $permissionId
                ]);
            }

            return $result;

        } catch (\Exception $e) {
            $this->logger->error('Assign permission error: ' . $e->getMessage());
            return false;
        }
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
        try {
            $result = $this->roleModel->removePermission($roleId, $permissionId);

            if ($result) {
                // Clear cache
                $this->clearRoleCache($roleId);
                $this->logger->info('Permission removed from role', [
                    'role_id' => $roleId,
                    'permission_id' => $permissionId
                ]);
            }

            return $result;

        } catch (\Exception $e) {
            $this->logger->error('Remove permission error: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Authorize user for action
     * 
     * @param string $permission
     * @param int|null $userId
     * @return void
     * @throws \RuntimeException
     */
    public function authorize(string $permission, ?int $userId = null): void
    {
        if (!$this->can($permission, $userId)) {
            $this->logger->warning('Authorization failed', [
                'permission' => $permission,
                'user_id' => $userId ?? $this->auth->id()
            ]);
            throw new \RuntimeException('You do not have permission to perform this action.', 403);
        }
    }

    /**
     * Get user permissions
     * 
     * @param int $userId
     * @return array
     */
    public function getUserPermissions(int $userId): array
    {
        // Check cache
        $cacheKey = 'user_perms_' . $userId;
        if (isset($this->userPermissionCache[$cacheKey])) {
            return $this->userPermissionCache[$cacheKey];
        }

        // Check cache storage
        if ($this->cache->has($cacheKey)) {
            $permissions = $this->cache->get($cacheKey);
            $this->userPermissionCache[$cacheKey] = $permissions;
            return $permissions;
        }

        try {
            $permissions = $this->userModel->getPermissionNames($userId);
            
            // Cache result
            $this->userPermissionCache[$cacheKey] = $permissions;
            $this->cache->put($cacheKey, $permissions, $this->cacheLifetime);

            return $permissions;

        } catch (\Exception $e) {
            $this->logger->error('Get user permissions error: ' . $e->getMessage());
            return [];
        }
    }

    /**
     * Get role permissions
     * 
     * @param int $roleId
     * @return array
     */
    public function getRolePermissions(int $roleId): array
    {
        // Check cache
        $cacheKey = 'role_perms_' . $roleId;
        if (isset($this->permissionCache[$cacheKey])) {
            return $this->permissionCache[$cacheKey];
        }

        // Check cache storage
        if ($this->cache->has($cacheKey)) {
            $permissions = $this->cache->get($cacheKey);
            $this->permissionCache[$cacheKey] = $permissions;
            return $permissions;
        }

        try {
            $permissions = $this->roleModel->getPermissionNames($roleId);
            
            // Cache result
            $this->permissionCache[$cacheKey] = $permissions;
            $this->cache->put($cacheKey, $permissions, $this->cacheLifetime);

            return $permissions;

        } catch (\Exception $e) {
            $this->logger->error('Get role permissions error: ' . $e->getMessage());
            return [];
        }
    }

    /**
     * Get all permissions
     * 
     * @return array
     */
    public function getAllPermissions(): array
    {
        try {
            return $this->permissionModel->getAll();
        } catch (\Exception $e) {
            $this->logger->error('Get all permissions error: ' . $e->getMessage());
            return [];
        }
    }

    /**
     * Get all roles
     * 
     * @return array
     */
    public function getAllRoles(): array
    {
        try {
            return $this->roleModel->getAll();
        } catch (\Exception $e) {
            $this->logger->error('Get all roles error: ' . $e->getMessage());
            return [];
        }
    }

    /**
     * Get users with role
     * 
     * @param int $roleId
     * @return array
     */
    public function getUsersWithRole(int $roleId): array
    {
        try {
            return $this->roleModel->getUsers($roleId);
        } catch (\Exception $e) {
            $this->logger->error('Get users with role error: ' . $e->getMessage());
            return [];
        }
    }

    /**
     * Get users with permission
     * 
     * @param int $permissionId
     * @return array
     */
    public function getUsersWithPermission(int $permissionId): array
    {
        try {
            return $this->permissionModel->getUsers($permissionId);
        } catch (\Exception $e) {
            $this->logger->error('Get users with permission error: ' . $e->getMessage());
            return [];
        }
    }

    /**
     * Clear user cache
     * 
     * @param int $userId
     * @return void
     */
    public function clearUserCache(int $userId): void
    {
        $cacheKey = 'user_perms_' . $userId;
        unset($this->userPermissionCache[$cacheKey]);
        $this->cache->forget($cacheKey);
        
        // Clear individual permission cache
        $this->userPermissionCache = array_filter($this->userPermissionCache, function($key) use ($userId) {
            return strpos($key, $userId . '_') !== 0;
        }, ARRAY_FILTER_USE_KEY);
    }

    /**
     * Clear role cache
     * 
     * @param int $roleId
     * @return void
     */
    public function clearRoleCache(int $roleId): void
    {
        $cacheKey = 'role_perms_' . $roleId;
        unset($this->permissionCache[$cacheKey]);
        $this->cache->forget($cacheKey);
    }

    /**
     * Clear all caches
     * 
     * @return void
     */
    public function clearAllCache(): void
    {
        $this->userPermissionCache = [];
        $this->permissionCache = [];
        $this->cache->flush();
    }

    /**
     * Create a new role
     * 
     * @param array $data
     * @return int|false
     */
    public function createRole(array $data)
    {
        try {
            $roleId = $this->roleModel->create($data);
            if ($roleId) {
                $this->logger->info('Role created', ['role_name' => $data['name']]);
            }
            return $roleId;
        } catch (\Exception $e) {
            $this->logger->error('Create role error: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Update role
     * 
     * @param int $roleId
     * @param array $data
     * @return bool
     */
    public function updateRole(int $roleId, array $data): bool
    {
        try {
            $result = $this->roleModel->update($roleId, $data);
            if ($result) {
                $this->clearRoleCache($roleId);
                $this->logger->info('Role updated', ['role_id' => $roleId]);
            }
            return $result;
        } catch (\Exception $e) {
            $this->logger->error('Update role error: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Delete role
     * 
     * @param int $roleId
     * @return bool
     */
    public function deleteRole(int $roleId): bool
    {
        try {
            $result = $this->roleModel->delete($roleId);
            if ($result) {
                $this->clearRoleCache($roleId);
                $this->logger->info('Role deleted', ['role_id' => $roleId]);
            }
            return $result;
        } catch (\Exception $e) {
            $this->logger->error('Delete role error: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Create permission
     * 
     * @param array $data
     * @return int|false
     */
    public function createPermission(array $data)
    {
        try {
            $permissionId = $this->permissionModel->create($data);
            if ($permissionId) {
                $this->logger->info('Permission created', ['permission_name' => $data['name']]);
            }
            return $permissionId;
        } catch (\Exception $e) {
            $this->logger->error('Create permission error: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Update permission
     * 
     * @param int $permissionId
     * @param array $data
     * @return bool
     */
    public function updatePermission(int $permissionId, array $data): bool
    {
        try {
            $result = $this->permissionModel->update($permissionId, $data);
            if ($result) {
                $this->logger->info('Permission updated', ['permission_id' => $permissionId]);
            }
            return $result;
        } catch (\Exception $e) {
            $this->logger->error('Update permission error: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Delete permission
     * 
     * @param int $permissionId
     * @return bool
     */
    public function deletePermission(int $permissionId): bool
    {
        try {
            $result = $this->permissionModel->delete($permissionId);
            if ($result) {
                $this->logger->info('Permission deleted', ['permission_id' => $permissionId]);
            }
            return $result;
        } catch (\Exception $e) {
            $this->logger->error('Delete permission error: ' . $e->getMessage());
            return false;
        }
    }
}