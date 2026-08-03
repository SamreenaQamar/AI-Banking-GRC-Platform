<?php
/**
 * Users Module - Controller
 * 
 * @package    AI-Banking-GRC-Platform
 * @subpackage modules/users
 * @version    1.0.0
 * @author     GRC Platform Team
 * @copyright  2026 AI Banking GRC Platform
 * @license    Proprietary
 * 
 * This controller handles:
 * - User management
 * - Role management
 * - Profile management
 * - Password management
 * - User activity
 */

declare(strict_types=1);

namespace Modules\Users\Controllers;

use App\Controllers\BaseController;
use App\Helpers\Auth;
use App\Helpers\CSRF;
use App\Helpers\Validation;
use Modules\Users\Services\UserService;
use App\Models\User;
use App\Models\Role;
use Exception;

class UserController extends BaseController
{
    /**
     * @var UserService
     */
    private UserService $userService;
    
    /**
     * @var User
     */
    private User $userModel;
    
    /**
     * @var Role
     */
    private Role $roleModel;
    
    /**
     * Constructor
     */
    public function __construct()
    {
        parent::__construct();
        $this->controllerName = 'Users';
        $this->userService = new UserService();
        $this->userModel = new User();
        $this->roleModel = new Role();
        
        $this->requireAuth();
        $this->requirePermission('user_view');
    }
    
    /**
     * Users dashboard
     * 
     * @return void
     */
    public function index(): void
    {
        try {
            $dashboardData = $this->userService->getDashboardData();
            
            $this->render('users/dashboard', [
                'title' => 'User Management - ' . APP_NAME,
                'data' => $dashboardData,
                'user_roles' => USER_ROLES,
                'user_status' => USER_STATUS
            ]);
            
        } catch (Exception $e) {
            $this->setFlashMessage('error', 'Failed to load user dashboard: ' . $e->getMessage());
            $this->redirectToRoute('dashboard');
        }
    }
    
    /**
     * User list
     * 
     * @return void
     */
    public function list(): void
    {
        try {
            $filters = [
                'status' => $this->input('status'),
                'role_id' => $this->input('role_id'),
                'department_id' => $this->input('department_id'),
                'search' => $this->input('search')
            ];
            
            $page = (int)$this->input('page', 1);
            $perPage = (int)$this->input('per_page', 15);
            
            $users = $this->userModel->getFiltered($filters, $page, $perPage);
            $total = $this->userModel->countFiltered($filters);
            $roles = $this->roleModel->getAll();
            
            $this->render('users/list', [
                'title' => 'Users - ' . APP_NAME,
                'users' => $users,
                'total' => $total,
                'page' => $page,
                'per_page' => $perPage,
                'total_pages' => ceil($total / $perPage),
                'filters' => $filters,
                'roles' => $roles,
                'user_status' => USER_STATUS
            ]);
            
        } catch (Exception $e) {
            $this->setFlashMessage('error', 'Failed to load users: ' . $e->getMessage());
            $this->redirectToRoute('users.index');
        }
    }
    
    /**
     * Create user
     * 
     * @return void
     */
    public function create(): void
    {
        try {
            $this->requirePermission('user_create');
            
            $roles = $this->roleModel->getAll();
            
            $this->render('users/create', [
                'title' => 'Create User - ' . APP_NAME,
                'roles' => $roles,
                'user_status' => USER_STATUS
            ]);
            
        } catch (Exception $e) {
            $this->setFlashMessage('error', $e->getMessage());
            $this->redirectToRoute('users.list');
        }
    }
    
    /**
     * Store user
     * 
     * @return void
     */
    public function store(): void
    {
        try {
            $this->requirePermission('user_create');
            CSRF::validate($_POST['csrf_token'] ?? '');
            
            $validationRules = [
                'username' => 'required|min:3|max:30|unique:users',
                'email' => 'required|email|unique:users',
                'first_name' => 'required|min:2|max:50',
                'last_name' => 'required|min:2|max:50',
                'password' => 'required|min:8',
                'role_id' => 'required|exists:roles,id',
                'status' => 'required|in:' . implode(',', array_keys(USER_STATUS))
            ];
            
            $validated = $this->validate($_POST, $validationRules);
            
            $userId = $this->userService->createUser($validated, Auth::id());
            
            if (!$userId) {
                throw new Exception('Failed to create user.');
            }
            
            $this->setFlashMessage('success', 'User created successfully.');
            $this->redirectToRoute('users.list');
            
        } catch (Exception $e) {
            $this->setFlashMessage('error', $e->getMessage());
            $this->redirectToRoute('users.create');
        }
    }
    
    /**
     * View user
     * 
     * @param array $params
     * @return void
     */
    public function view(array $params): void
    {
        try {
            $userId = (int)($params['id'] ?? 0);
            $user = $this->userModel->find($userId);
            
            if (!$user) {
                throw new Exception('User not found.');
            }
            
            $permissions = $this->userService->getUserPermissions($userId);
            $activities = $this->userModel->getActivityLogs($userId, 20);
            
            $this->render('users/view', [
                'title' => 'User Details - ' . APP_NAME,
                'user' => $user,
                'permissions' => $permissions,
                'activities' => $activities,
                'user_status' => USER_STATUS
            ]);
            
        } catch (Exception $e) {
            $this->setFlashMessage('error', $e->getMessage());
            $this->redirectToRoute('users.list');
        }
    }
    
    /**
     * Edit user
     * 
     * @param array $params
     * @return void
     */
    public function edit(array $params): void
    {
        try {
            $this->requirePermission('user_update');
            
            $userId = (int)($params['id'] ?? 0);
            $user = $this->userModel->find($userId);
            
            if (!$user) {
                throw new Exception('User not found.');
            }
            
            $roles = $this->roleModel->getAll();
            
            $this->render('users/edit', [
                'title' => 'Edit User - ' . APP_NAME,
                'user' => $user,
                'roles' => $roles,
                'user_status' => USER_STATUS
            ]);
            
        } catch (Exception $e) {
            $this->setFlashMessage('error', $e->getMessage());
            $this->redirectToRoute('users.list');
        }
    }
    
    /**
     * Update user
     * 
     * @param array $params
     * @return void
     */
    public function update(array $params): void
    {
        try {
            $this->requirePermission('user_update');
            CSRF::validate($_POST['csrf_token'] ?? '');
            
            $userId = (int)($params['id'] ?? 0);
            $user = $this->userModel->find($userId);
            
            if (!$user) {
                throw new Exception('User not found.');
            }
            
            $validationRules = [
                'username' => 'required|min:3|max:30|unique:users,' . $userId,
                'email' => 'required|email|unique:users,' . $userId,
                'first_name' => 'required|min:2|max:50',
                'last_name' => 'required|min:2|max:50',
                'role_id' => 'required|exists:roles,id',
                'status' => 'required|in:' . implode(',', array_keys(USER_STATUS))
            ];
            
            $validated = $this->validate($_POST, $validationRules);
            
            // Handle password if provided
            if (!empty($_POST['password'])) {
                $validated['password'] = $_POST['password'];
            }
            
            $result = $this->userService->updateUser($userId, $validated, Auth::id());
            
            if (!$result) {
                throw new Exception('Failed to update user.');
            }
            
            $this->setFlashMessage('success', 'User updated successfully.');
            $this->redirectToRoute('users.list');
            
        } catch (Exception $e) {
            $this->setFlashMessage('error', $e->getMessage());
            $this->redirectToRoute('users.edit', ['id' => $params['id']]);
        }
    }
    
    /**
     * Delete user (AJAX)
     * 
     * @param array $params
     * @return void
     */
    public function delete(array $params): void
    {
        try {
            $this->requirePermission('user_delete');
            CSRF::validate($_POST['csrf_token'] ?? '');
            
            $userId = (int)($params['id'] ?? 0);
            
            $result = $this->userService->deleteUser($userId, Auth::id());
            
            if (!$result) {
                throw new Exception('Failed to delete user.');
            }
            
            $this->jsonSuccess('User deleted successfully.');
            
        } catch (Exception $e) {
            $this->jsonError($e->getMessage());
        }
    }
    
    /**
     * Update user status (AJAX)
     * 
     * @param array $params
     * @return void
     */
    public function updateStatus(array $params): void
    {
        try {
            $this->requirePermission('user_update');
            CSRF::validate($_POST['csrf_token'] ?? '');
            
            $userId = (int)($params['id'] ?? 0);
            $status = $this->input('status');
            
            $result = $this->userService->updateUserStatus($userId, $status, Auth::id());
            
            if (!$result) {
                throw new Exception('Failed to update user status.');
            }
            
            $this->jsonSuccess('User status updated successfully.');
            
        } catch (Exception $e) {
            $this->jsonError($e->getMessage());
        }
    }
    
    /**
     * Assign role (AJAX)
     * 
     * @param array $params
     * @return void
     */
    public function assignRole(array $params): void
    {
        try {
            $this->requirePermission('user_role_assign');
            CSRF::validate($_POST['csrf_token'] ?? '');
            
            $userId = (int)($params['id'] ?? 0);
            $roleId = (int)$this->input('role_id');
            
            $result = $this->userService->assignRole($userId, $roleId, Auth::id());
            
            if (!$result) {
                throw new Exception('Failed to assign role.');
            }
            
            $this->jsonSuccess('Role assigned successfully.');
            
        } catch (Exception $e) {
            $this->jsonError($e->getMessage());
        }
    }
    
    /**
     * Profile
     * 
     * @return void
     */
    public function profile(): void
    {
        try {
            $userId = Auth::id();
            $user = $this->userModel->find($userId);
            
            if (!$user) {
                throw new Exception('User not found.');
            }
            
            $this->render('users/profile', [
                'title' => 'My Profile - ' . APP_NAME,
                'user' => $user
            ]);
            
        } catch (Exception $e) {
            $this->setFlashMessage('error', $e->getMessage());
            $this->redirectToRoute('dashboard');
        }
    }
}