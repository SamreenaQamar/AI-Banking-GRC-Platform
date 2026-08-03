<?php
/**
 * AI Banking GRC Platform - User Management Controller
 * 
 * @package    AI-Banking-GRC-Platform
 * @version    1.0.0
 * @author     GRC Platform Team
 * @copyright  2026 AI Banking GRC Platform
 * @license    Proprietary
 * 
 * This controller handles:
 * - User CRUD operations
 * - Role assignment
 * - User status management
 * - Profile management
 * - User search and filtering
 * - Bulk operations
 */

declare(strict_types=1);

namespace App\Controllers;

use App\Models\User;
use App\Models\Role;
use App\Helpers\Auth;
use App\Helpers\Validation;
use App\Services\EmailService;
use Exception;

class UserController extends BaseController
{
    /**
     * @var User
     */
    private User $userModel;
    
    /**
     * @var Role
     */
    private Role $roleModel;
    
    /**
     * @var EmailService
     */
    private EmailService $emailService;
    
    /**
     * Constructor
     */
    public function __construct()
    {
        parent::__construct();
        $this->controllerName = 'Users';
        $this->userModel = new User();
        $this->roleModel = new Role();
        $this->emailService = new EmailService();
        
        $this->requireAuth();
        $this->requireRole([ROLE_ADMIN, ROLE_SUPER_ADMIN]);
    }
    
    /**
     * List all users
     * 
     * @return void
     */
    public function index(): void
    {
        try {
            // Get filter parameters
            $filters = [
                'status' => $this->input('status'),
                'role_id' => $this->input('role_id'),
                'department_id' => $this->input('department_id'),
                'search' => $this->input('search')
            ];
            
            // Get pagination parameters
            $page = (int)$this->input('page', 1);
            $perPage = (int)$this->input('per_page', PAGINATION_DEFAULT);
            
            // Get users
            $users = $this->userModel->getFiltered($filters, $page, $perPage);
            $total = $this->userModel->countFiltered($filters);
            
            // Get roles for filter
            $roles = $this->roleModel->getAll();
            
            $this->render('index', [
                'title' => 'User Management - ' . APP_NAME,
                'users' => $users,
                'total' => $total,
                'page' => $page,
                'per_page' => $perPage,
                'total_pages' => ceil($total / $perPage),
                'filters' => $filters,
                'roles' => $roles,
                'statuses' => USER_STATUSES
            ]);
            
        } catch (Exception $e) {
            $this->setFlashMessage('error', 'Failed to load users: ' . $e->getMessage());
            $this->redirectToRoute('dashboard');
        }
    }
    
    /**
     * Show create user form
     * 
     * @return void
     */
    public function create(): void
    {
        $roles = $this->roleModel->getAll();
        $departments = $this->getDepartments();
        
        $this->render('create', [
            'title' => 'Create User - ' . APP_NAME,
            'roles' => $roles,
            'departments' => $departments,
            'statuses' => USER_STATUSES
        ]);
    }
    
    /**
     * Store a new user
     * 
     * @return void
     */
    public function store(): void
    {
        try {
            // Validate CSRF token
            $this->validateCSRF($_POST['csrf_token'] ?? '');
            
            // Validate input
            $validationRules = [
                'first_name' => 'required|min:2|max:50',
                'last_name' => 'required|min:2|max:50',
                'email' => 'required|email|unique:users,email',
                'username' => 'required|min:3|max:30|unique:users,username',
                'password' => 'required|min:8',
                'role_id' => 'required|exists:roles,id',
                'status' => 'required|in:' . implode(',', array_keys(USER_STATUSES)),
                'mobile' => 'regex:/^(\+92|0)[0-9]{10,12}$/',
                'department_id' => 'exists:departments,id'
            ];
            
            $validated = $this->validate($_POST, $validationRules);
            
            // Create user
            $userData = [
                'username' => $validated['username'],
                'email' => $validated['email'],
                'first_name' => $validated['first_name'],
                'last_name' => $validated['last_name'],
                'mobile' => $validated['mobile'] ?? null,
                'password_hash' => password_hash($validated['password'], PASSWORD_BCRYPT, ['cost' => HASH_COST]),
                'role_id' => $validated['role_id'],
                'department_id' => $validated['department_id'] ?? null,
                'status' => $validated['status'],
                'email_verified' => $validated['status'] === USER_STATUS_ACTIVE,
                'created_by' => Auth::id(),
                'created_at' => date('Y-m-d H:i:s')
            ];
            
            $userId = $this->userModel->create($userData);
            
            if (!$userId) {
                throw new Exception('Failed to create user.');
            }
            
            // Send welcome email
            $this->emailService->sendWelcomeEmail($userId);
            
            // Log activity
            $this->logActivity('user_create', 'Created user: ' . $validated['username']);
            
            $this->setFlashMessage('success', 'User created successfully.');
            $this->redirectToRoute('users.index');
            
        } catch (Exception $e) {
            $this->setFlashMessage('error', $e->getMessage());
            $this->redirectToRoute('users.create');
        }
    }
    
    /**
     * Show user details
     * 
     * @param array $params Route parameters
     * @return void
     */
    public function show(array $params): void
    {
        try {
            $userId = (int)($params['id'] ?? 0);
            $user = $this->userModel->find($userId);
            
            if (!$user) {
                throw new Exception('User not found.');
            }
            
            // Get user's activity log
            $activities = $this->getUserActivities($userId);
            
            // Get user's statistics
            $stats = $this->getUserStats($userId);
            
            $this->render('show', [
                'title' => 'User Details - ' . APP_NAME,
                'user' => $user,
                'activities' => $activities,
                'stats' => $stats
            ]);
            
        } catch (Exception $e) {
            $this->setFlashMessage('error', $e->getMessage());
            $this->redirectToRoute('users.index');
        }
    }
    
    /**
     * Show edit user form
     * 
     * @param array $params Route parameters
     * @return void
     */
    public function edit(array $params): void
    {
        try {
            $userId = (int)($params['id'] ?? 0);
            $user = $this->userModel->find($userId);
            
            if (!$user) {
                throw new Exception('User not found.');
            }
            
            $roles = $this->roleModel->getAll();
            $departments = $this->getDepartments();
            
            $this->render('edit', [
                'title' => 'Edit User - ' . APP_NAME,
                'user' => $user,
                'roles' => $roles,
                'departments' => $departments,
                'statuses' => USER_STATUSES
            ]);
            
        } catch (Exception $e) {
            $this->setFlashMessage('error', $e->getMessage());
            $this->redirectToRoute('users.index');
        }
    }
    
    /**
     * Update user
     * 
     * @param array $params Route parameters
     * @return void
     */
    public function update(array $params): void
    {
        try {
            $userId = (int)($params['id'] ?? 0);
            $user = $this->userModel->find($userId);
            
            if (!$user) {
                throw new Exception('User not found.');
            }
            
            // Validate CSRF token
            $this->validateCSRF($_POST['csrf_token'] ?? '');
            
            // Validate input
            $validationRules = [
                'first_name' => 'required|min:2|max:50',
                'last_name' => 'required|min:2|max:50',
                'email' => 'required|email|unique:users,email,' . $userId,
                'username' => 'required|min:3|max:30|unique:users,username,' . $userId,
                'role_id' => 'required|exists:roles,id',
                'status' => 'required|in:' . implode(',', array_keys(USER_STATUSES)),
                'mobile' => 'regex:/^(\+92|0)[0-9]{10,12}$/',
                'department_id' => 'exists:departments,id'
            ];
            
            $validated = $this->validate($_POST, $validationRules);
            
            // Update user
            $userData = [
                'username' => $validated['username'],
                'email' => $validated['email'],
                'first_name' => $validated['first_name'],
                'last_name' => $validated['last_name'],
                'mobile' => $validated['mobile'] ?? null,
                'role_id' => $validated['role_id'],
                'department_id' => $validated['department_id'] ?? null,
                'status' => $validated['status'],
                'updated_at' => date('Y-m-d H:i:s')
            ];
            
            // If password provided, update it
            if (!empty($_POST['password'])) {
                if (strlen($_POST['password']) < 8) {
                    throw new Exception('Password must be at least 8 characters.');
                }
                $userData['password_hash'] = password_hash($_POST['password'], PASSWORD_BCRYPT, ['cost' => HASH_COST]);
            }
            
            $result = $this->userModel->update($userId, $userData);
            
            if (!$result) {
                throw new Exception('Failed to update user.');
            }
            
            // Log activity
            $this->logActivity('user_update', 'Updated user: ' . $validated['username']);
            
            $this->setFlashMessage('success', 'User updated successfully.');
            $this->redirectToRoute('users.index');
            
        } catch (Exception $e) {
            $this->setFlashMessage('error', $e->getMessage());
            $this->redirectToRoute('users.edit', ['id' => $params['id']]);
        }
    }
    
    /**
     * Delete user
     * 
     * @param array $params Route parameters
     * @return void
     */
    public function delete(array $params): void
    {
        try {
            $userId = (int)($params['id'] ?? 0);
            $user = $this->userModel->find($userId);
            
            if (!$user) {
                throw new Exception('User not found.');
            }
            
            // Prevent deleting own account
            if ($userId === Auth::id()) {
                throw new Exception('Cannot delete your own account.');
            }
            
            // Validate CSRF token
            $this->validateCSRF($_POST['csrf_token'] ?? '');
            
            $result = $this->userModel->softDelete($userId);
            
            if (!$result) {
                throw new Exception('Failed to delete user.');
            }
            
            // Log activity
            $this->logActivity('user_delete', 'Deleted user: ' . $user->username);
            
            $this->jsonSuccess('User deleted successfully.');
            
        } catch (Exception $e) {
            $this->jsonError($e->getMessage());
        }
    }
    
    /**
     * Assign roles to user
     * 
     * @param array $params Route parameters
     * @return void
     */
    public function assignRoles(array $params): void
    {
        try {
            $userId = (int)($params['id'] ?? 0);
            $user = $this->userModel->find($userId);
            
            if (!$user) {
                throw new Exception('User not found.');
            }
            
            $roleId = (int)$this->input('role_id');
            $role = $this->roleModel->find($roleId);
            
            if (!$role) {
                throw new Exception('Role not found.');
            }
            
            $this->userModel->assignRole($userId, $roleId);
            
            // Log activity
            $this->logActivity('user_assign_role', 'Assigned role ' . $role->name . ' to user: ' . $user->username);
            
            $this->jsonSuccess('Role assigned successfully.', [
                'user' => $user->username,
                'role' => $role->display_name
            ]);
            
        } catch (Exception $e) {
            $this->jsonError($e->getMessage());
        }
    }
    
    /**
     * Update user status
     * 
     * @param array $params Route parameters
     * @return void
     */
    public function updateStatus(array $params): void
    {
        try {
            $userId = (int)($params['id'] ?? 0);
            $user = $this->userModel->find($userId);
            
            if (!$user) {
                throw new Exception('User not found.');
            }
            
            $status = $this->input('status');
            
            if (!in_array($status, array_keys(USER_STATUSES))) {
                throw new Exception('Invalid status.');
            }
            
            // Prevent disabling own account
            if ($userId === Auth::id() && $status === USER_STATUS_INACTIVE) {
                throw new Exception('Cannot deactivate your own account.');
            }
            
            $this->userModel->updateStatus($userId, $status);
            
            // Log activity
            $this->logActivity('user_update_status', 'Updated status of user ' . $user->username . ' to ' . $status);
            
            $this->jsonSuccess('User status updated successfully.', [
                'status' => $status,
                'status_label' => USER_STATUSES[$status]
            ]);
            
        } catch (Exception $e) {
            $this->jsonError($e->getMessage());
        }
    }
    
    /**
     * Reset user password
     * 
     * @param array $params Route parameters
     * @return void
     */
    public function resetPassword(array $params): void
    {
        try {
            $userId = (int)($params['id'] ?? 0);
            $user = $this->userModel->find($userId);
            
            if (!$user) {
                throw new Exception('User not found.');
            }
            
            // Generate random password
            $newPassword = $this->generateRandomPassword();
            
            $hashedPassword = password_hash($newPassword, PASSWORD_BCRYPT, ['cost' => HASH_COST]);
            $this->userModel->updatePassword($userId, $hashedPassword);
            
            // Send new password email
            $this->emailService->sendPasswordResetEmail($user->email, $newPassword);
            
            // Log activity
            $this->logActivity('user_reset_password', 'Reset password for user: ' . $user->username);
            
            $this->jsonSuccess('Password reset successfully. New password has been sent to user\'s email.', [
                'username' => $user->username
            ]);
            
        } catch (Exception $e) {
            $this->jsonError($e->getMessage());
        }
    }
    
    /**
     * Get departments for dropdown
     * 
     * @return array
     */
    private function getDepartments(): array
    {
        // This will be implemented in DepartmentModel
        return [];
    }
    
    /**
     * Get user activities
     * 
     * @param int $userId
     * @return array
     */
    private function getUserActivities(int $userId): array
    {
        // This will be implemented in ActivityLogService
        return [];
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
            'compliance_tasks' => $this->userModel->countComplianceTasks($userId),
            'risk_assessments' => $this->userModel->countRiskAssessments($userId),
            'audit_findings' => $this->userModel->countAuditFindings($userId),
            'policy_acknowledgements' => $this->userModel->countPolicyAcknowledgements($userId)
        ];
    }
    
    /**
     * Generate random password
     * 
     * @param int $length
     * @return string
     */
    private function generateRandomPassword(int $length = 12): string
    {
        $characters = 'ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789!@#$%^&*()';
        $password = '';
        
        for ($i = 0; $i < $length; $i++) {
            $password .= $characters[random_int(0, strlen($characters) - 1)];
        }
        
        return $password;
    }
    
    /**
     * Log activity
     * 
     * @param string $action
     * @param string $description
     * @return void
     */
    private function logActivity(string $action, string $description): void
    {
        // This will be implemented in ActivityLogService
    }
}