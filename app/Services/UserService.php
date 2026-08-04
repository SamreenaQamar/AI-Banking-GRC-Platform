<?php
/**
 * AI Banking GRC Platform - User Service
 * 
 * @package    AI-Banking-GRC-Platform
 * @subpackage app/Services
 * @version    1.0.0
 * @author     GRC Platform Team
 * @copyright  2026 AI Banking GRC Platform
 * @license    Proprietary
 * 
 * This service handles user management business logic:
 * - User CRUD operations
 * - Profile management
 * - Role assignment
 * - Permission assignment
 * - Status management
 * - Search and pagination
 */

declare(strict_types=1);

namespace App\Services;

use App\Models\User;
use App\Models\Role;
use App\Models\ActivityLog;
use App\Libraries\Authentication;
use App\Libraries\Authorization;
use App\Libraries\Validator;
use App\Libraries\Logger;
use App\Libraries\Mail;
use App\Libraries\Security;

class UserService
{
    /**
     * @var User User model
     */
    private User $userModel;

    /**
     * @var Role Role model
     */
    private Role $roleModel;

    /**
     * @var ActivityLog Activity log model
     */
    private ActivityLog $activityLogModel;

    /**
     * @var Authentication Authentication library
     */
    private Authentication $auth;

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
     * @var Mail Mail library
     */
    private Mail $mail;

    /**
     * @var Security Security library
     */
    private Security $security;

    /**
     * Constructor
     */
    public function __construct()
    {
        $this->userModel = new User();
        $this->roleModel = new Role();
        $this->activityLogModel = new ActivityLog();
        $this->auth = new Authentication();
        $this->authorization = new Authorization();
        $this->validator = new Validator();
        $this->logger = new Logger();
        $this->mail = new Mail();
        $this->security = new Security();
    }

    /**
     * Create a new user
     * 
     * @param array $data
     * @param int $createdBy
     * @return array
     */
    public function create(array $data, int $createdBy): array
    {
        try {
            // Validate input
            $rules = [
                'username' => ['required', 'min:3', 'max:50', 'unique:users,username'],
                'email' => ['required', 'email', 'unique:users,email'],
                'first_name' => ['required', 'min:2', 'max:50'],
                'last_name' => ['required', 'min:2', 'max:50'],
                'password' => ['required', 'min:8'],
                'role_id' => ['required', 'exists:roles,id'],
                'status' => ['in:active,inactive,suspended,pending'],
                'mobile' => ['phone']
            ];

            if (!$this->validator->validate($data, $rules)) {
                return $this->errorResponse('Validation failed.', 'VALIDATION_ERROR', [
                    'errors' => $this->validator->getErrors()
                ]);
            }

            // Check if user already exists
            if ($this->userModel->findByUsername($data['username'])) {
                return $this->errorResponse('Username already taken.', 'USERNAME_TAKEN');
            }

            if ($this->userModel->findByEmail($data['email'])) {
                return $this->errorResponse('Email already registered.', 'EMAIL_TAKEN');
            }

            // Hash password
            $hashedPassword = $this->auth->hashPassword($data['password']);

            // Create user
            $userData = [
                'username' => $data['username'],
                'email' => $data['email'],
                'first_name' => $data['first_name'],
                'last_name' => $data['last_name'],
                'password_hash' => $hashedPassword,
                'role_id' => $data['role_id'],
                'status' => $data['status'] ?? 'active',
                'mobile' => $data['mobile'] ?? null,
                'employee_id' => $data['employee_id'] ?? null,
                'phone' => $data['phone'] ?? null,
                'address' => $data['address'] ?? null,
                'city' => $data['city'] ?? null,
                'state' => $data['state'] ?? null,
                'postal_code' => $data['postal_code'] ?? null,
                'country' => $data['country'] ?? 'Pakistan',
                'created_by' => $createdBy,
                'created_at' => date('Y-m-d H:i:s')
            ];

            $userId = $this->userModel->create($userData);

            if (!$userId) {
                return $this->errorResponse('Failed to create user.', 'CREATE_FAILED');
            }

            // Log activity
            $this->activityLogModel->logCreate($createdBy, 'users', 'user', $userId, $userData);

            // Send welcome email
            if (isset($data['send_welcome']) && $data['send_welcome']) {
                $this->mail->sendWelcome($data['email'], $data['first_name'], $data['username']);
            }

            $this->logger->info('User created', [
                'user_id' => $userId,
                'username' => $data['username'],
                'created_by' => $createdBy
            ]);

            return $this->successResponse('User created successfully.', [
                'user_id' => $userId
            ]);

        } catch (\Exception $e) {
            $this->logger->error('Create user error: ' . $e->getMessage());
            return $this->errorResponse('An error occurred creating user.', 'ERROR');
        }
    }

    /**
     * Update user
     * 
     * @param int $userId
     * @param array $data
     * @param int $updatedBy
     * @return array
     */
    public function update(int $userId, array $data, int $updatedBy): array
    {
        try {
            $user = $this->userModel->find($userId);
            if (!$user) {
                return $this->errorResponse('User not found.', 'USER_NOT_FOUND');
            }

            // Validate input
            $rules = [
                'username' => ['required', 'min:3', 'max:50', 'unique:users,username,' . $userId],
                'email' => ['required', 'email', 'unique:users,email,' . $userId],
                'first_name' => ['required', 'min:2', 'max:50'],
                'last_name' => ['required', 'min:2', 'max:50'],
                'role_id' => ['exists:roles,id'],
                'status' => ['in:active,inactive,suspended,pending'],
                'mobile' => ['phone']
            ];

            if (!$this->validator->validate($data, $rules)) {
                return $this->errorResponse('Validation failed.', 'VALIDATION_ERROR', [
                    'errors' => $this->validator->getErrors()
                ]);
            }

            // Check if username or email is taken
            if (isset($data['username']) && $data['username'] !== $user->username) {
                if ($this->userModel->findByUsername($data['username'])) {
                    return $this->errorResponse('Username already taken.', 'USERNAME_TAKEN');
                }
            }

            if (isset($data['email']) && $data['email'] !== $user->email) {
                if ($this->userModel->findByEmail($data['email'])) {
                    return $this->errorResponse('Email already registered.', 'EMAIL_TAKEN');
                }
            }

            // Prepare update data
            $updateData = [];
            $allowedFields = ['username', 'email', 'first_name', 'last_name', 'role_id', 'status', 
                             'mobile', 'employee_id', 'phone', 'address', 'city', 'state', 'postal_code', 'country'];

            foreach ($allowedFields as $field) {
                if (isset($data[$field])) {
                    $updateData[$field] = $data[$field];
                }
            }

            // Update password if provided
            if (!empty($data['password'])) {
                if (strlen($data['password']) < 8) {
                    return $this->errorResponse('Password must be at least 8 characters.', 'INVALID_PASSWORD');
                }
                $updateData['password_hash'] = $this->auth->hashPassword($data['password']);
            }

            $updateData['updated_at'] = date('Y-m-d H:i:s');

            $result = $this->userModel->update($userId, $updateData);

            if (!$result) {
                return $this->errorResponse('Failed to update user.', 'UPDATE_FAILED');
            }

            // Log activity
            $this->activityLogModel->logChange($updatedBy, 'users', 'user', $userId, (array)$user, $updateData);

            // Clear cache
            $this->authorization->clearUserCache($userId);

            $this->logger->info('User updated', [
                'user_id' => $userId,
                'updated_by' => $updatedBy
            ]);

            return $this->successResponse('User updated successfully.');

        } catch (\Exception $e) {
            $this->logger->error('Update user error: ' . $e->getMessage());
            return $this->errorResponse('An error occurred updating user.', 'ERROR');
        }
    }

    /**
     * Delete user (soft delete)
     * 
     * @param int $userId
     * @param int $deletedBy
     * @return array
     */
    public function delete(int $userId, int $deletedBy): array
    {
        try {
            $user = $this->userModel->find($userId);
            if (!$user) {
                return $this->errorResponse('User not found.', 'USER_NOT_FOUND');
            }

            // Prevent self-deletion
            if ($userId === $deletedBy) {
                return $this->errorResponse('Cannot delete your own account.', 'SELF_DELETE');
            }

            $result = $this->userModel->softDelete($userId);

            if (!$result) {
                return $this->errorResponse('Failed to delete user.', 'DELETE_FAILED');
            }

            // Log activity
            $this->activityLogModel->logDelete($deletedBy, 'users', 'user', $userId, (array)$user);

            // Clear cache
            $this->authorization->clearUserCache($userId);

            $this->logger->info('User deleted', [
                'user_id' => $userId,
                'deleted_by' => $deletedBy
            ]);

            return $this->successResponse('User deleted successfully.');

        } catch (\Exception $e) {
            $this->logger->error('Delete user error: ' . $e->getMessage());
            return $this->errorResponse('An error occurred deleting user.', 'ERROR');
        }
    }

    /**
     * Get user by ID
     * 
     * @param int $userId
     * @return array
     */
    public function find(int $userId): array
    {
        try {
            $user = $this->userModel->find($userId);

            if (!$user) {
                return $this->errorResponse('User not found.', 'USER_NOT_FOUND');
            }

            // Get role and permissions
            $role = $this->roleModel->find($user->role_id);
            $permissions = $this->authorization->getUserPermissions($userId);

            return $this->successResponse('User retrieved.', [
                'user' => $user,
                'role' => $role,
                'permissions' => $permissions
            ]);

        } catch (\Exception $e) {
            $this->logger->error('Find user error: ' . $e->getMessage());
            return $this->errorResponse('An error occurred.', 'ERROR');
        }
    }

    /**
     * Get all users with pagination
     * 
     * @param array $filters
     * @param int $page
     * @param int $perPage
     * @return array
     */
    public function all(array $filters = [], int $page = 1, int $perPage = 15): array
    {
        try {
            $users = $this->userModel->getFiltered($filters, $page, $perPage);
            $total = $this->userModel->countFiltered($filters);

            return $this->successResponse('Users retrieved.', [
                'users' => $users,
                'total' => $total,
                'page' => $page,
                'per_page' => $perPage,
                'total_pages' => ceil($total / $perPage)
            ]);

        } catch (\Exception $e) {
            $this->logger->error('Get all users error: ' . $e->getMessage());
            return $this->errorResponse('An error occurred.', 'ERROR');
        }
    }

    /**
     * Search users
     * 
     * @param string $query
     * @param int $limit
     * @return array
     */
    public function search(string $query, int $limit = 20): array
    {
        try {
            $users = $this->userModel->search($query, $limit);

            return $this->successResponse('Users found.', [
                'users' => $users,
                'count' => count($users)
            ]);

        } catch (\Exception $e) {
            $this->logger->error('Search users error: ' . $e->getMessage());
            return $this->errorResponse('An error occurred.', 'ERROR');
        }
    }

    /**
     * Update user status
     * 
     * @param int $userId
     * @param string $status
     * @param int $updatedBy
     * @return array
     */
    public function updateStatus(int $userId, string $status, int $updatedBy): array
    {
        try {
            $user = $this->userModel->find($userId);
            if (!$user) {
                return $this->errorResponse('User not found.', 'USER_NOT_FOUND');
            }

            $validStatuses = ['active', 'inactive', 'suspended', 'pending'];
            if (!in_array($status, $validStatuses)) {
                return $this->errorResponse('Invalid status.', 'INVALID_STATUS');
            }

            $result = $this->userModel->update($userId, [
                'status' => $status,
                'updated_at' => date('Y-m-d H:i:s')
            ]);

            if (!$result) {
                return $this->errorResponse('Failed to update status.', 'UPDATE_FAILED');
            }

            // Log activity
            $this->activityLogModel->logAction($updatedBy, 'status_change', 'users', 
                "User status changed from {$user->status} to {$status}");

            $this->logger->info('User status updated', [
                'user_id' => $userId,
                'old_status' => $user->status,
                'new_status' => $status,
                'updated_by' => $updatedBy
            ]);

            return $this->successResponse('User status updated successfully.');

        } catch (\Exception $e) {
            $this->logger->error('Update status error: ' . $e->getMessage());
            return $this->errorResponse('An error occurred.', 'ERROR');
        }
    }

    /**
     * Assign role to user
     * 
     * @param int $userId
     * @param int $roleId
     * @param int $assignedBy
     * @return array
     */
    public function assignRole(int $userId, int $roleId, int $assignedBy): array
    {
        try {
            $user = $this->userModel->find($userId);
            if (!$user) {
                return $this->errorResponse('User not found.', 'USER_NOT_FOUND');
            }

            $role = $this->roleModel->find($roleId);
            if (!$role) {
                return $this->errorResponse('Role not found.', 'ROLE_NOT_FOUND');
            }

            $result = $this->userModel->update($userId, ['role_id' => $roleId]);

            if (!$result) {
                return $this->errorResponse('Failed to assign role.', 'ASSIGN_FAILED');
            }

            // Log activity
            $this->activityLogModel->logAction($assignedBy, 'role_assign', 'users',
                "Role {$role->name} assigned to user {$user->username}");

            // Clear cache
            $this->authorization->clearUserCache($userId);

            $this->logger->info('Role assigned to user', [
                'user_id' => $userId,
                'role_id' => $roleId,
                'assigned_by' => $assignedBy
            ]);

            return $this->successResponse('Role assigned successfully.');

        } catch (\Exception $e) {
            $this->logger->error('Assign role error: ' . $e->getMessage());
            return $this->errorResponse('An error occurred.', 'ERROR');
        }
    }

    /**
     * Get user profile
     * 
     * @param int $userId
     * @return array
     */
    public function profile(int $userId): array
    {
        try {
            $user = $this->userModel->find($userId);
            if (!$user) {
                return $this->errorResponse('User not found.', 'USER_NOT_FOUND');
            }

            // Get additional data
            $role = $this->roleModel->find($user->role_id);
            $permissions = $this->authorization->getUserPermissions($userId);
            $activities = $this->activityLogModel->getUserActivities($userId, 10);
            $stats = $this->getUserStats($userId);

            return $this->successResponse('User profile retrieved.', [
                'user' => $user,
                'role' => $role,
                'permissions' => $permissions,
                'recent_activities' => $activities,
                'stats' => $stats
            ]);

        } catch (\Exception $e) {
            $this->logger->error('Profile error: ' . $e->getMessage());
            return $this->errorResponse('An error occurred.', 'ERROR');
        }
    }

    /**
     * Update user profile
     * 
     * @param int $userId
     * @param array $data
     * @return array
     */
    public function updateProfile(int $userId, array $data): array
    {
        try {
            $user = $this->userModel->find($userId);
            if (!$user) {
                return $this->errorResponse('User not found.', 'USER_NOT_FOUND');
            }

            $allowedFields = ['first_name', 'last_name', 'phone', 'mobile', 'address', 'city', 'state', 'postal_code', 'country'];
            $updateData = [];

            foreach ($allowedFields as $field) {
                if (isset($data[$field])) {
                    $updateData[$field] = $data[$field];
                }
            }

            if (empty($updateData)) {
                return $this->errorResponse('No fields to update.', 'NO_DATA');
            }

            $result = $this->userModel->update($userId, $updateData);

            if (!$result) {
                return $this->errorResponse('Failed to update profile.', 'UPDATE_FAILED');
            }

            $this->logger->info('User profile updated', ['user_id' => $userId]);

            return $this->successResponse('Profile updated successfully.');

        } catch (\Exception $e) {
            $this->logger->error('Update profile error: ' . $e->getMessage());
            return $this->errorResponse('An error occurred.', 'ERROR');
        }
    }

    /**
     * Update user avatar
     * 
     * @param int $userId
     * @param array $file
     * @return array
     */
    public function updateAvatar(int $userId, array $file): array
    {
        try {
            $user = $this->userModel->find($userId);
            if (!$user) {
                return $this->errorResponse('User not found.', 'USER_NOT_FOUND');
            }

            // Validate file
            $allowedTypes = ['image/jpeg', 'image/png', 'image/gif', 'image/webp'];
            $maxSize = 2 * 1024 * 1024; // 2MB

            if (!isset($file['error']) || $file['error'] !== UPLOAD_ERR_OK) {
                return $this->errorResponse('File upload error.', 'UPLOAD_ERROR');
            }

            if ($file['size'] > $maxSize) {
                return $this->errorResponse('File size exceeds 2MB limit.', 'FILE_TOO_LARGE');
            }

            $finfo = finfo_open(FILEINFO_MIME_TYPE);
            $mimeType = finfo_file($finfo, $file['tmp_name']);
            finfo_close($finfo);

            if (!in_array($mimeType, $allowedTypes)) {
                return $this->errorResponse('Invalid file type. Allowed: JPEG, PNG, GIF, WebP.', 'INVALID_TYPE');
            }

            // Generate filename and save
            $extension = pathinfo($file['name'], PATHINFO_EXTENSION);
            $filename = 'avatar_' . $userId . '_' . time() . '.' . $extension;
            $uploadPath = UPLOADS_PATH . '/avatars/';

            if (!is_dir($uploadPath)) {
                mkdir($uploadPath, 0755, true);
            }

            $targetPath = $uploadPath . $filename;
            move_uploaded_file($file['tmp_name'], $targetPath);

            // Update user record
            $this->userModel->update($userId, ['profile_image' => 'avatars/' . $filename]);

            $this->logger->info('User avatar updated', ['user_id' => $userId]);

            return $this->successResponse('Avatar updated successfully.', [
                'avatar' => 'avatars/' . $filename
            ]);

        } catch (\Exception $e) {
            $this->logger->error('Update avatar error: ' . $e->getMessage());
            return $this->errorResponse('An error occurred.', 'ERROR');
        }
    }

    /**
     * Get user statistics
     * 
     * @param int $userId
     * @return array
     */
    private function getUserStats(int $userId): array
    {
        return [
            'total_activities' => $this->activityLogModel->countByUser($userId),
            'last_login' => $this->userModel->getLastLogin($userId),
            'total_compliance' => 0, // Will be implemented in ComplianceService
            'total_risks' => 0, // Will be implemented in RiskService
            'total_audits' => 0 // Will be implemented in AuditService
        ];
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