<?php
/**
 * AI Banking GRC Platform - Role Service
 * 
 * @package    AI-Banking-GRC-Platform
 * @subpackage app/Services
 * @version    1.0.0
 * @author     GRC Platform Team
 * @copyright  2026 AI Banking GRC Platform
 * @license    Proprietary
 * 
 * This service handles role management business logic:
 * - Role CRUD operations
 * - Permission assignment
 * - RBAC management
 * - Access control
 */

declare(strict_types=1);

namespace App\Services;

use App\Models\Role;
use App\Models\Permission;
use App\Models\ActivityLog;
use App\Libraries\Authorization;
use App\Libraries\Validator;
use App\Libraries\Logger;

class RoleService
{
    /**
     * @var Role Role model
     */
    private Role $roleModel;

    /**
     * @var Permission Permission model
     */
    private Permission $permissionModel;

    /**
     * @var ActivityLog Activity log model
     */
    private ActivityLog $activityLogModel;

    /**
     * @var Authorization Authorization library
     */
    private Authorization $authorization;

    /**
     * @var Validator Validator library
     */
    private Validator $validator;

    /**
     * @var Logger Logger library
     */
    private Logger $logger;

    /**
     * Constructor
     */
    public function __construct()
    {
        $this->roleModel = new Role();
        $this->permissionModel = new Permission();
        $this->activityLogModel = new ActivityLog();
        $this->authorization = new Authorization();
        $this->validator = new Validator();
        $this->logger = new Logger();
    }

    /**
     * Create a new role
     * 
     * @param array $data
     * @param int $createdBy
     * @return array
     */
    public function create(array $data, int $createdBy): array
    {
        try {
            $rules = [
                'name' => ['required', 'min:3', 'max:50', 'unique:roles,name'],
                'display_name' => ['required', 'min:3', 'max:100'],
                'description' => ['max:500'],
                'level' => ['numeric', 'min:1', 'max:10']
            ];

            if (!$this->validator->validate($data, $rules)) {
                return $this->errorResponse('Validation failed.', 'VALIDATION_ERROR', [
                    'errors' => $this->validator->getErrors()
                ]);
            }

            $roleData = [
                'name' => $data['name'],
                'display_name' => $data['display_name'],
                'description' => $data['description'] ?? '',
                'level' => $data['level'] ?? 1,
                'is_system' => false,
                'created_by' => $createdBy,
                'created_at' => date('Y-m-d H:i:s')
            ];

            $roleId = $this->roleModel->create($roleData);

            if (!$roleId) {
                return $this->errorResponse('Failed to create role.', 'CREATE_FAILED');
            }

            // Assign permissions if provided
            if (!empty($data['permissions'])) {
                $this->assignPermissions($roleId, $data['permissions']);
            }

            // Log activity
            $this->activityLogModel->logCreate($createdBy, 'roles', 'role', $roleId, $roleData);

            $this->logger->info('Role created', [
                'role_id' => $roleId,
                'name' => $data['name'],
                'created_by' => $createdBy
            ]);

            return $this->successResponse('Role created successfully.', [
                'role_id' => $roleId
            ]);

        } catch (\Exception $e) {
            $this->logger->error('Create role error: ' . $e->getMessage());
            return $this->errorResponse('An error occurred creating role.', 'ERROR');
        }
    }

    /**
     * Update role
     * 
     * @param int $roleId
     * @param array $data
     * @param int $updatedBy
     * @return array
     */
    public function update(int $roleId, array $data, int $updatedBy): array
    {
        try {
            $role = $this->roleModel->find($roleId);
            if (!$role) {
                return $this->errorResponse('Role not found.', 'ROLE_NOT_FOUND');
            }

            // Prevent system role modification
            if ($role->is_system) {
                return $this->errorResponse('System roles cannot be modified.', 'SYSTEM_ROLE');
            }

            $rules = [
                'name' => ['required', 'min:3', 'max:50', 'unique:roles,name,' . $roleId],
                'display_name' => ['required', 'min:3', 'max:100'],
                'description' => ['max:500'],
                'level' => ['numeric', 'min:1', 'max:10']
            ];

            if (!$this->validator->validate($data, $rules)) {
                return $this->errorResponse('Validation failed.', 'VALIDATION_ERROR', [
                    'errors' => $this->validator->getErrors()
                ]);
            }

            $updateData = [
                'name' => $data['name'],
                'display_name' => $data['display_name'],
                'description' => $data['description'] ?? '',
                'level' => $data['level'] ?? 1,
                'updated_at' => date('Y-m-d H:i:s')
            ];

            $result = $this->roleModel->update($roleId, $updateData);

            if (!$result) {
                return $this->errorResponse('Failed to update role.', 'UPDATE_FAILED');
            }

            // Update permissions if provided
            if (isset($data['permissions'])) {
                $this->syncPermissions($roleId, $data['permissions']);
            }

            // Log activity
            $this->activityLogModel->logChange($updatedBy, 'roles', 'role', $roleId, (array)$role, $updateData);

            // Clear cache
            $this->authorization->clearRoleCache($roleId);

            $this->logger->info('Role updated', [
                'role_id' => $roleId,
                'updated_by' => $updatedBy
            ]);

            return $this->successResponse('Role updated successfully.');

        } catch (\Exception $e) {
            $this->logger->error('Update role error: ' . $e->getMessage());
            return $this->errorResponse('An error occurred updating role.', 'ERROR');
        }
    }

    /**
     * Delete role
     * 
     * @param int $roleId
     * @param int $deletedBy
     * @return array
     */
    public function delete(int $roleId, int $deletedBy): array
    {
        try {
            $role = $this->roleModel->find($roleId);
            if (!$role) {
                return $this->errorResponse('Role not found.', 'ROLE_NOT_FOUND');
            }

            // Prevent system role deletion
            if ($role->is_system) {
                return $this->errorResponse('System roles cannot be deleted.', 'SYSTEM_ROLE');
            }

            // Check if role has users
            $userCount = $this->roleModel->countUsers($roleId);
            if ($userCount > 0) {
                return $this->errorResponse('Role has users assigned. Cannot delete.', 'ROLE_IN_USE');
            }

            $result = $this->roleModel->softDelete($roleId);

            if (!$result) {
                return $this->errorResponse('Failed to delete role.', 'DELETE_FAILED');
            }

            // Log activity
            $this->activityLogModel->logDelete($deletedBy, 'roles', 'role', $roleId, (array)$role);

            // Clear cache
            $this->authorization->clearRoleCache($roleId);

            $this->logger->info('Role deleted', [
                'role_id' => $roleId,
                'name' => $role->name,
                'deleted_by' => $deletedBy
            ]);

            return $this->successResponse('Role deleted successfully.');

        } catch (\Exception $e) {
            $this->logger->error('Delete role error: ' . $e->getMessage());
            return $this->errorResponse('An error occurred deleting role.', 'ERROR');
        }
    }

    /**
     * Get role by ID
     * 
     * @param int $roleId
     * @return array
     */
    public function find(int $roleId): array
    {
        try {
            $role = $this->roleModel->find($roleId);
            if (!$role) {
                return $this->errorResponse('Role not found.', 'ROLE_NOT_FOUND');
            }

            $permissions = $this->authorization->getRolePermissions($roleId);
            $users = $this->roleModel->getUsers($roleId);

            return $this->successResponse('Role retrieved.', [
                'role' => $role,
                'permissions' => $permissions,
                'users' => $users
            ]);

        } catch (\Exception $e) {
            $this->logger->error('Find role error: ' . $e->getMessage());
            return $this->errorResponse('An error occurred.', 'ERROR');
        }
    }

    /**
     * Get all roles
     * 
     * @param bool $includeSystem
     * @return array
     */
    public function all(bool $includeSystem = true): array
    {
        try {
            $roles = $this->roleModel->getAll($includeSystem);

            // Get permissions for each role
            foreach ($roles as &$role) {
                $role->permissions = $this->authorization->getRolePermissions($role->id);
                $role->user_count = $this->roleModel->countUsers($role->id);
            }

            return $this->successResponse('Roles retrieved.', [
                'roles' => $roles
            ]);

        } catch (\Exception $e) {
            $this->logger->error('Get all roles error: ' . $e->getMessage());
            return $this->errorResponse('An error occurred.', 'ERROR');
        }
    }

    /**
     * Assign permissions to role
     * 
     * @param int $roleId
     * @param array $permissionIds
     * @param int $assignedBy
     * @return array
     */
    public function assignPermissions(int $roleId, array $permissionIds, int $assignedBy = 0): array
    {
        try {
            $role = $this->roleModel->find($roleId);
            if (!$role) {
                return $this->errorResponse('Role not found.', 'ROLE_NOT_FOUND');
            }

            $count = 0;
            foreach ($permissionIds as $permissionId) {
                $permission = $this->permissionModel->find($permissionId);
                if ($permission) {
                    $result = $this->roleModel->assignPermission($roleId, $permissionId);
                    if ($result) {
                        $count++;
                    }
                }
            }

            // Log activity
            if ($assignedBy > 0) {
                $this->activityLogModel->logAction($assignedBy, 'permission_assign', 'roles',
                    "Assigned {$count} permissions to role {$role->name}");
            }

            // Clear cache
            $this->authorization->clearRoleCache($roleId);

            $this->logger->info('Permissions assigned to role', [
                'role_id' => $roleId,
                'permission_count' => $count
            ]);

            return $this->successResponse('Permissions assigned successfully.', [
                'assigned' => $count
            ]);

        } catch (\Exception $e) {
            $this->logger->error('Assign permissions error: ' . $e->getMessage());
            return $this->errorResponse('An error occurred.', 'ERROR');
        }
    }

    /**
     * Remove permission from role
     * 
     * @param int $roleId
     * @param int $permissionId
     * @param int $removedBy
     * @return array
     */
    public function removePermission(int $roleId, int $permissionId, int $removedBy = 0): array
    {
        try {
            $role = $this->roleModel->find($roleId);
            if (!$role) {
                return $this->errorResponse('Role not found.', 'ROLE_NOT_FOUND');
            }

            $permission = $this->permissionModel->find($permissionId);
            if (!$permission) {
                return $this->errorResponse('Permission not found.', 'PERMISSION_NOT_FOUND');
            }

            $result = $this->roleModel->removePermission($roleId, $permissionId);

            if (!$result) {
                return $this->errorResponse('Failed to remove permission.', 'REMOVE_FAILED');
            }

            // Log activity
            if ($removedBy > 0) {
                $this->activityLogModel->logAction($removedBy, 'permission_remove', 'roles',
                    "Removed permission {$permission->name} from role {$role->name}");
            }

            // Clear cache
            $this->authorization->clearRoleCache($roleId);

            $this->logger->info('Permission removed from role', [
                'role_id' => $roleId,
                'permission_id' => $permissionId
            ]);

            return $this->successResponse('Permission removed successfully.');

        } catch (\Exception $e) {
            $this->logger->error('Remove permission error: ' . $e->getMessage());
            return $this->errorResponse('An error occurred.', 'ERROR');
        }
    }

    /**
     * Sync permissions for role
     * 
     * @param int $roleId
     * @param array $permissionIds
     * @param int $syncedBy
     * @return array
     */
    public function syncPermissions(int $roleId, array $permissionIds, int $syncedBy = 0): array
    {
        try {
            $role = $this->roleModel->find($roleId);
            if (!$role) {
                return $this->errorResponse('Role not found.', 'ROLE_NOT_FOUND');
            }

            $result = $this->roleModel->syncPermissions($roleId, $permissionIds);

            if (!$result) {
                return $this->errorResponse('Failed to sync permissions.', 'SYNC_FAILED');
            }

            // Log activity
            if ($syncedBy > 0) {
                $this->activityLogModel->logAction($syncedBy, 'permission_sync', 'roles',
                    "Synced permissions for role {$role->name}");
            }

            // Clear cache
            $this->authorization->clearRoleCache($roleId);

            $this->logger->info('Permissions synced for role', [
                'role_id' => $roleId,
                'permission_count' => count($permissionIds)
            ]);

            return $this->successResponse('Permissions synced successfully.');

        } catch (\Exception $e) {
            $this->logger->error('Sync permissions error: ' . $e->getMessage());
            return $this->errorResponse('An error occurred.', 'ERROR');
        }
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