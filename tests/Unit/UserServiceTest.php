<?php
/**
 * User Service Unit Test
 * 
 * @package    AI-Banking-GRC-Platform
 * @subpackage tests/Unit
 * @version    1.0.0
 * @author     GRC Platform Team
 * @copyright  2026 AI Banking GRC Platform
 * @license    Proprietary
 * 
 * This class tests the UserService class methods.
 */

declare(strict_types=1);

namespace Tests\Unit;

use Tests\TestCase;
use Modules\Users\Services\UserService;
use App\Models\User;
use App\Models\Role;
use App\Helpers\Password;

class UserServiceTest extends TestCase
{
    /**
     * @var UserService
     */
    private UserService $userService;

    /**
     * @var User
     */
    private User $user;

    /**
     * @var Role
     */
    private Role $role;

    /**
     * Setup before each test
     */
    protected function setUp(): void
    {
        parent::setUp();
        $this->skipIfNoDatabase();

        $this->userService = new UserService();
        $this->role = new Role();

        // Create test user
        $this->user = $this->createTestUser();
    }

    /**
     * Create test user
     */
    private function createTestUser(): User
    {
        $userData = [
            'username' => 'service_test_' . uniqid(),
            'email' => 'service_test_' . uniqid() . '@example.com',
            'first_name' => 'Service',
            'last_name' => 'Tester',
            'password_hash' => Password::hash('Test@123456'),
            'role_id' => 7,
            'status' => 'active'
        ];

        $userId = $this->userModel->create($userData);
        return $this->userModel->find($userId);
    }

    /**
     * Test get user statistics
     */
    public function test_get_user_stats(): void
    {
        // Act
        $stats = $this->userService->getUserStats();

        // Assert
        $this->assertArrayHasKey('total', $stats);
        $this->assertArrayHasKey('active', $stats);
        $this->assertArrayHasKey('inactive', $stats);
        $this->assertArrayHasKey('suspended', $stats);
        $this->assertArrayHasKey('pending', $stats);
        $this->assertArrayHasKey('today_active', $stats);
        $this->assertArrayHasKey('by_role', $stats);
        
        $this->assertIsNumeric($stats['total']);
        $this->assertIsNumeric($stats['active']);
    }

    /**
     * Test get recent users
     */
    public function test_get_recent_users(): void
    {
        // Act
        $recentUsers = $this->userService->getRecentUsers(5);

        // Assert
        $this->assertIsArray($recentUsers);
        $this->assertLessThanOrEqual(5, count($recentUsers));
        foreach ($recentUsers as $user) {
            $this->assertIsObject($user);
            $this->assertObjectHasProperty('id', $user);
            $this->assertObjectHasProperty('username', $user);
        }
    }

    /**
     * Test create user
     */
    public function test_create_user(): void
    {
        // Arrange
        $userData = [
            'username' => 'newuser_' . uniqid(),
            'email' => 'newuser_' . uniqid() . '@example.com',
            'first_name' => 'New',
            'last_name' => 'User',
            'password' => 'Test@123456',
            'role_id' => 7,
            'status' => 'active'
        ];

        // Act
        $userId = $this->userService->createUser($userData, 1);

        // Assert
        $this->assertNotFalse($userId);
        $user = $this->userModel->find($userId);
        $this->assertNotNull($user);
        $this->assertEquals($userData['username'], $user->username);
        $this->assertEquals($userData['email'], $user->email);
    }

    /**
     * Test update user
     */
    public function test_update_user(): void
    {
        // Arrange
        $newEmail = 'updated_' . uniqid() . '@example.com';

        // Act
        $result = $this->userService->updateUser(
            $this->user->id,
            ['email' => $newEmail],
            1
        );

        // Assert
        $this->assertTrue($result);
        $user = $this->userModel->find($this->user->id);
        $this->assertEquals($newEmail, $user->email);
    }

    /**
     * Test update user password
     */
    public function test_update_user_password(): void
    {
        // Arrange
        $newPassword = 'NewPassword@789';

        // Act
        $result = $this->userService->updateUser(
            $this->user->id,
            ['password' => $newPassword],
            1
        );

        // Assert
        $this->assertTrue($result);
        $user = $this->userModel->find($this->user->id);
        $this->assertTrue(Password::verify($newPassword, $user->password_hash));
    }

    /**
     * Test delete user
     */
    public function test_delete_user(): void
    {
        // Arrange
        $user = $this->createTestUser();

        // Act
        $result = $this->userService->deleteUser($user->id, 1);

        // Assert
        $this->assertTrue($result);
        $deletedUser = $this->userModel->find($user->id);
        $this->assertNull($deletedUser);
    }

    /**
     * Test update user status
     */
    public function test_update_user_status(): void
    {
        // Arrange
        $newStatus = 'suspended';

        // Act
        $result = $this->userService->updateUserStatus(
            $this->user->id,
            $newStatus,
            1
        );

        // Assert
        $this->assertTrue($result);
        $user = $this->userModel->find($this->user->id);
        $this->assertEquals($newStatus, $user->status);
    }

    /**
     * Test assign role to user
     */
    public function test_assign_role_to_user(): void
    {
        // Arrange
        $roleId = 2;

        // Act
        $result = $this->userService->assignRole(
            $this->user->id,
            $roleId,
            1
        );

        // Assert
        $this->assertTrue($result);
        $user = $this->userModel->find($this->user->id);
        $this->assertEquals($roleId, $user->role_id);
    }

    /**
     * Test get user permissions
     */
    public function test_get_user_permissions(): void
    {
        // Act
        $permissions = $this->userService->getUserPermissions($this->user->id);

        // Assert
        $this->assertIsArray($permissions);
    }

    /**
     * Test has permission
     */
    public function test_has_permission(): void
    {
        // Act
        $hasPermission = $this->userService->hasPermission(
            $this->user->id,
            'user_view'
        );

        // Assert
        $this->assertIsBool($hasPermission);
    }

    /**
     * Test user creation with duplicate username
     */
    public function test_create_user_with_duplicate_username(): void
    {
        // Arrange
        $userData = [
            'username' => $this->user->username,
            'email' => 'unique_' . uniqid() . '@example.com',
            'first_name' => 'Duplicate',
            'last_name' => 'User',
            'password' => 'Test@123456',
            'role_id' => 7,
            'status' => 'active'
        ];

        // Assert
        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('Username already exists.');

        // Act
        $this->userService->createUser($userData, 1);
    }

    /**
     * Test user creation with duplicate email
     */
    public function test_create_user_with_duplicate_email(): void
    {
        // Arrange
        $userData = [
            'username' => 'unique_' . uniqid(),
            'email' => $this->user->email,
            'first_name' => 'Duplicate',
            'last_name' => 'User',
            'password' => 'Test@123456',
            'role_id' => 7,
            'status' => 'active'
        ];

        // Assert
        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('Email already exists.');

        // Act
        $this->userService->createUser($userData, 1);
    }

    /**
     * Test delete non-existent user
     */
    public function test_delete_non_existent_user(): void
    {
        // Assert
        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('User not found.');

        // Act
        $this->userService->deleteUser(99999, 1);
    }

    /**
     * Test update non-existent user
     */
    public function test_update_non_existent_user(): void
    {
        // Assert
        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('User not found.');

        // Act
        $this->userService->updateUser(99999, ['email' => 'test@example.com'], 1);
    }
}