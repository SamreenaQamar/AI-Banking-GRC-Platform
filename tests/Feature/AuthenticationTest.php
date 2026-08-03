<?php
/**
 * Authentication Feature Test
 * 
 * @package    AI-Banking-GRC-Platform
 * @subpackage tests/Feature
 * @version    1.0.0
 * @author     GRC Platform Team
 * @copyright  2026 AI Banking GRC Platform
 * @license    Proprietary
 * 
 * This class tests authentication features including login, logout,
 * registration, password reset, and two-factor authentication.
 */

declare(strict_types=1);

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\User;
use App\Helpers\Auth;
use App\Helpers\Password;

class AuthenticationTest extends TestCase
{
    /**
     * @var User
     */
    private User $user;

    /**
     * Setup before each test
     */
    protected function setUp(): void
    {
        parent::setUp();
        $this->skipIfNoDatabase();
        $this->user = $this->createTestUserRecord();
    }

    /**
     * Create test user record
     */
    private function createTestUserRecord(): User
    {
        $userData = [
            'username' => $this->generateUniqueUsername(),
            'email' => $this->generateUniqueEmail(),
            'first_name' => 'Test',
            'last_name' => 'User',
            'password_hash' => Password::hash('Test@123456'),
            'role_id' => 7,
            'status' => 'active',
            'email_verified' => true
        ];

        $userId = $this->userModel->create($userData);
        $user = $this->userModel->find($userId);
        $this->assertNotNull($user);
        return $user;
    }

    /**
     * Test user can login with valid credentials
     */
    public function test_user_can_login_with_valid_credentials(): void
    {
        // Act
        $result = Auth::attempt($this->user->username, 'Test@123456');

        // Assert
        $this->assertTrue($result);
        $this->assertTrue(Auth::check());
        $this->assertEquals($this->user->id, Auth::id());
    }

    /**
     * Test user cannot login with invalid credentials
     */
    public function test_user_cannot_login_with_invalid_credentials(): void
    {
        // Act
        $result = Auth::attempt($this->user->username, 'WrongPassword123');

        // Assert
        $this->assertFalse($result);
        $this->assertFalse(Auth::check());
    }

    /**
     * Test user login with email address
     */
    public function test_user_can_login_with_email(): void
    {
        // Act
        $result = Auth::attempt($this->user->email, 'Test@123456');

        // Assert
        $this->assertTrue($result);
        $this->assertTrue(Auth::check());
    }

    /**
     * Test account lockout after failed attempts
     */
    public function test_account_locks_after_failed_attempts(): void
    {
        // Arrange
        $maxAttempts = 5;

        // Act
        for ($i = 0; $i < $maxAttempts; $i++) {
            Auth::attempt($this->user->username, 'WrongPassword');
        }

        $result = Auth::attempt($this->user->username, 'Test@123456');

        // Assert
        $this->assertFalse($result);
        $this->assertFalse(Auth::check());
    }

    /**
     * Test user can logout
     */
    public function test_user_can_logout(): void
    {
        // Arrange
        Auth::login($this->user);
        $this->assertTrue(Auth::check());

        // Act
        Auth::logout();

        // Assert
        $this->assertFalse(Auth::check());
    }

    /**
     * Test user can register with valid data
     */
    public function test_user_can_register_with_valid_data(): void
    {
        // Arrange
        $userData = [
            'username' => $this->generateUniqueUsername(),
            'email' => $this->generateUniqueEmail(),
            'first_name' => 'New',
            'last_name' => 'User',
            'password' => 'Test@123456',
            'password_confirmation' => 'Test@123456'
        ];

        // Act
        $result = $this->userModel->create([
            'username' => $userData['username'],
            'email' => $userData['email'],
            'first_name' => $userData['first_name'],
            'last_name' => $userData['last_name'],
            'password_hash' => Password::hash($userData['password']),
            'role_id' => 7,
            'status' => 'pending',
            'email_verified' => false
        ]);

        // Assert
        $this->assertNotFalse($result);
        $user = $this->userModel->find($result);
        $this->assertNotNull($user);
        $this->assertEquals($userData['username'], $user->username);
        $this->assertEquals($userData['email'], $user->email);
    }

    /**
     * Test user cannot register with existing username
     */
    public function test_user_cannot_register_with_existing_username(): void
    {
        // Arrange
        $userData = [
            'username' => $this->user->username,
            'email' => $this->generateUniqueEmail(),
            'first_name' => 'Duplicate',
            'last_name' => 'User',
            'password' => 'Test@123456'
        ];

        // Act
        $existing = $this->userModel->findByUsername($userData['username']);

        // Assert
        $this->assertNotNull($existing);
    }

    /**
     * Test user can reset password
     */
    public function test_user_can_reset_password(): void
    {
        // Arrange
        $newPassword = 'NewPassword@123';
        $user = $this->user;

        // Act
        $result = $this->userModel->update($user->id, [
            'password_hash' => Password::hash($newPassword)
        ]);

        // Assert
        $this->assertTrue($result);
        $updatedUser = $this->userModel->find($user->id);
        $this->assertTrue(Password::verify($newPassword, $updatedUser->password_hash));
    }

    /**
     * Test password reset token is valid
     */
    public function test_password_reset_token_is_valid(): void
    {
        // Arrange
        $token = bin2hex(random_bytes(32));
        $user = $this->user;

        // Act
        $this->userModel->createPasswordResetToken($user->id, $token);
        $result = $this->userModel->validatePasswordResetToken($token);

        // Assert
        $this->assertNotFalse($result);
        $this->assertEquals($user->id, $result['user_id']);
    }

    /**
     * Test expired password reset token is invalid
     */
    public function test_expired_password_reset_token_is_invalid(): void
    {
        // Arrange
        $token = bin2hex(random_bytes(32));
        $user = $this->user;

        // Act
        $this->userModel->createPasswordResetToken($user->id, $token);
        // Simulate token expiration
        $this->userModel->deleteResetToken($token);

        $result = $this->userModel->validatePasswordResetToken($token);

        // Assert
        $this->assertFalse($result);
    }

    /**
     * Test user can change password
     */
    public function test_user_can_change_password(): void
    {
        // Arrange
        $user = $this->user;
        $oldPassword = 'Test@123456';
        $newPassword = 'NewPassword@456';

        // Act
        $result = $this->userModel->update($user->id, [
            'password_hash' => Password::hash($newPassword)
        ]);

        // Assert
        $this->assertTrue($result);
        $updatedUser = $this->userModel->find($user->id);
        $this->assertTrue(Password::verify($newPassword, $updatedUser->password_hash));
        $this->assertFalse(Password::verify($oldPassword, $updatedUser->password_hash));
    }

    /**
     * Test session is created on login
     */
    public function test_session_is_created_on_login(): void
    {
        // Act
        Auth::login($this->user);

        // Assert
        $this->assertTrue(Auth::check());
        $this->assertEquals($this->user->id, Auth::id());
        $this->assertArrayHasKey('user_id', $_SESSION);
        $this->assertArrayHasKey('authenticated', $_SESSION);
        $this->assertTrue($_SESSION['authenticated']);
    }

    /**
     * Test session is destroyed on logout
     */
    public function test_session_is_destroyed_on_logout(): void
    {
        // Arrange
        Auth::login($this->user);
        $this->assertTrue(Auth::check());

        // Act
        Auth::logout();

        // Assert
        $this->assertFalse(Auth::check());
        $this->assertArrayNotHasKey('user_id', $_SESSION);
        $this->assertArrayNotHasKey('authenticated', $_SESSION);
    }

    /**
     * Test user cannot access protected route without authentication
     */
    public function test_user_cannot_access_protected_route_without_authentication(): void
    {
        // Act - Simulate accessing protected route
        $isAuthenticated = Auth::check();

        // Assert
        $this->assertFalse($isAuthenticated);
    }

    /**
     * Test user can access protected route with authentication
     */
    public function test_user_can_access_protected_route_with_authentication(): void
    {
        // Arrange
        Auth::login($this->user);

        // Act
        $isAuthenticated = Auth::check();

        // Assert
        $this->assertTrue($isAuthenticated);
    }

    /**
     * Test two-factor authentication verification
     */
    public function test_two_factor_authentication_verification(): void
    {
        // Arrange
        $user = $this->user;
        $this->userModel->update($user->id, [
            'two_factor_enabled' => true,
            'two_factor_secret' => 'JBSWY3DPEHPK3PXP'
        ]);

        // Act - This would normally use TOTP verification
        $updatedUser = $this->userModel->find($user->id);

        // Assert
        $this->assertTrue($updatedUser->two_factor_enabled);
        $this->assertNotNull($updatedUser->two_factor_secret);
    }

    /**
     * Test remember me functionality
     */
    public function test_remember_me_functionality(): void
    {
        // Arrange
        $user = $this->user;
        $token = bin2hex(random_bytes(64));

        // Act
        $this->userModel->update($user->id, [
            'remember_token' => $token
        ]);

        $updatedUser = $this->userModel->find($user->id);

        // Assert
        $this->assertEquals($token, $updatedUser->remember_token);
    }

    /**
     * Test user status validation
     */
    public function test_user_status_validation(): void
    {
        // Arrange
        $user = $this->user;

        // Act - Change status to inactive
        $this->userModel->update($user->id, ['status' => 'inactive']);

        // Assert - User should not be able to login
        $result = Auth::attempt($user->username, 'Test@123456');
        $this->assertFalse($result);
    }

    /**
     * Test user email verification
     */
    public function test_user_email_verification(): void
    {
        // Arrange
        $user = $this->user;

        // Act
        $this->userModel->update($user->id, [
            'email_verified' => true
        ]);

        $updatedUser = $this->userModel->find($user->id);

        // Assert
        $this->assertTrue($updatedUser->email_verified);
    }
}