<?php
/**
 * AI Banking GRC Platform - Base Test Case
 * 
 * @package    AI-Banking-GRC-Platform
 * @subpackage tests
 * @version    1.0.0
 * @author     GRC Platform Team
 * @copyright  2026 AI Banking GRC Platform
 * @license    Proprietary
 * 
 * This is the base test case class that all test classes extend.
 * It provides common setup, teardown, and helper methods.
 */

declare(strict_types=1);

namespace Tests;

use PHPUnit\Framework\TestCase as BaseTestCase;
use App\Helpers\Database;
use App\Helpers\Auth;
use App\Models\User;
use PDO;

abstract class TestCase extends BaseTestCase
{
    /**
     * @var PDO
     */
    protected PDO $db;

    /**
     * @var User
     */
    protected User $userModel;

    /**
     * @var array
     */
    protected array $testData = [];

    /**
     * @var string
     */
    protected string $testUserToken;

    /**
     * Setup before each test
     */
    protected function setUp(): void
    {
        parent::setUp();

        // Initialize database connection
        $this->db = Database::getInstance()->getConnection();
        $this->userModel = new User();

        // Start transaction for test isolation
        $this->db->beginTransaction();

        // Create test user
        $this->createTestUser();

        // Generate test data
        $this->generateTestData();
    }

    /**
     * Cleanup after each test
     */
    protected function tearDown(): void
    {
        // Rollback transaction
        $this->db->rollBack();

        // Clear session
        $_SESSION = [];

        parent::tearDown();
    }

    /**
     * Create test user
     */
    protected function createTestUser(): void
    {
        $userData = [
            'username' => 'testuser_' . uniqid(),
            'email' => 'test_' . uniqid() . '@example.com',
            'first_name' => 'Test',
            'last_name' => 'User',
            'password_hash' => password_hash('Test@123456', PASSWORD_BCRYPT),
            'role_id' => 7,
            'status' => 'active',
            'email_verified' => true
        ];

        $this->testUserToken = $userData['username'];
        $this->testData['user'] = $userData;
    }

    /**
     * Generate test data
     */
    protected function generateTestData(): void
    {
        $this->testData['risk'] = [
            'title' => 'Test Risk',
            'description' => 'This is a test risk for unit testing',
            'category_id' => 1,
            'inherent_likelihood' => 3,
            'inherent_impact' => 4,
            'owner_department_id' => 1
        ];

        $this->testData['compliance'] = [
            'title' => 'Test Compliance Task',
            'description' => 'This is a test compliance task',
            'category_id' => 1,
            'framework_id' => 1,
            'department_id' => 1,
            'priority' => 'medium',
            'due_date' => date('Y-m-d', strtotime('+30 days'))
        ];

        $this->testData['audit'] = [
            'title' => 'Test Audit',
            'scope_description' => 'This is a test audit for testing',
            'audit_type' => 'internal',
            'department_id' => 1,
            'start_date' => date('Y-m-d'),
            'end_date' => date('Y-m-d', strtotime('+7 days'))
        ];
    }

    /**
     * Assert that response is JSON
     */
    protected function assertJsonResponse(string $response): void
    {
        $this->assertIsString($response);
        $this->assertJson($response);
    }

    /**
     * Assert that response contains expected data
     */
    protected function assertJsonContains(array $expected, string $response): void
    {
        $data = json_decode($response, true);
        foreach ($expected as $key => $value) {
            $this->assertArrayHasKey($key, $data);
            $this->assertEquals($value, $data[$key]);
        }
    }

    /**
     * Assert that response is successful
     */
    protected function assertSuccessResponse(string $response): void
    {
        $data = json_decode($response, true);
        $this->assertArrayHasKey('success', $data);
        $this->assertTrue($data['success']);
    }

    /**
     * Assert that response has error
     */
    protected function assertErrorResponse(string $response): void
    {
        $data = json_decode($response, true);
        $this->assertArrayHasKey('success', $data);
        $this->assertFalse($data['success']);
        $this->assertArrayHasKey('message', $data);
    }

    /**
     * Create mock request object
     */
    protected function createMockRequest(array $data = [], string $method = 'POST'): object
    {
        return (object)[
            'method' => $method,
            'data' => $data,
            'server' => ['REMOTE_ADDR' => '127.0.0.1'],
            'headers' => ['Content-Type' => 'application/json']
        ];
    }

    /**
     * Mock authentication
     */
    protected function mockAuth(int $userId = 1): void
    {
        $_SESSION['user_id'] = $userId;
        $_SESSION['authenticated'] = true;
        $_SESSION['user_role'] = 'user';
    }

    /**
     * Mock admin authentication
     */
    protected function mockAdminAuth(int $userId = 1): void
    {
        $_SESSION['user_id'] = $userId;
        $_SESSION['authenticated'] = true;
        $_SESSION['user_role'] = 'admin';
    }

    /**
     * Get test database connection
     */
    protected function getTestDb(): PDO
    {
        return $this->db;
    }

    /**
     * Create test table
     */
    protected function createTestTable(string $tableName, array $schema): void
    {
        $columns = [];
        foreach ($schema as $column => $definition) {
            $columns[] = "$column $definition";
        }
        $sql = "CREATE TABLE IF NOT EXISTS $tableName (" . implode(', ', $columns) . ")";
        $this->db->exec($sql);
    }

    /**
     * Drop test table
     */
    protected function dropTestTable(string $tableName): void
    {
        $sql = "DROP TABLE IF EXISTS $tableName";
        $this->db->exec($sql);
    }

    /**
     * Insert test data into table
     */
    protected function insertTestData(string $tableName, array $data): int
    {
        $columns = array_keys($data);
        $placeholders = array_map(fn($col) => ":$col", $columns);
        $sql = "INSERT INTO $tableName (" . implode(', ', $columns) . ") 
                VALUES (" . implode(', ', $placeholders) . ")";
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute($data);
        return (int)$this->db->lastInsertId();
    }

    /**
     * Generate random string
     */
    protected function generateRandomString(int $length = 10): string
    {
        return bin2hex(random_bytes($length));
    }

    /**
     * Generate unique email
     */
    protected function generateUniqueEmail(): string
    {
        return 'test_' . uniqid() . '@example.com';
    }

    /**
     * Generate unique username
     */
    protected function generateUniqueUsername(): string
    {
        return 'testuser_' . uniqid();
    }

    /**
     * Get test environment variable
     */
    protected function getTestEnv(string $key, $default = null)
    {
        return $_ENV[$key] ?? getenv($key) ?: $default;
    }

    /**
     * Skip test if database is not available
     */
    protected function skipIfNoDatabase(): void
    {
        try {
            $this->db->query('SELECT 1');
        } catch (\PDOException $e) {
            $this->markTestSkipped('Database not available: ' . $e->getMessage());
        }
    }

    /**
     * Skip test if environment is not testing
     */
    protected function skipIfNotTesting(): void
    {
        if ($this->getTestEnv('APP_ENV') !== 'testing') {
            $this->markTestSkipped('Environment is not testing');
        }
    }

    /**
     * Assert that arrays are equal with strict comparison
     */
    protected function assertArrayEquals(array $expected, array $actual): void
    {
        $this->assertEquals($expected, $actual);
        $this->assertSameSize($expected, $actual);
    }

    /**
     * Assert that string contains all given substrings
     */
    protected function assertStringContainsAll(array $substrings, string $string): void
    {
        foreach ($substrings as $substring) {
            $this->assertStringContainsString($substring, $string);
        }
    }

    /**
     * Assert that array contains all given keys
     */
    protected function assertArrayHasKeys(array $keys, array $array): void
    {
        foreach ($keys as $key) {
            $this->assertArrayHasKey($key, $array);
        }
    }

    /**
     * Assert that values are numeric between min and max
     */
    protected function assertNumericBetween(int|float $min, int|float $max, int|float $value): void
    {
        $this->assertIsNumeric($value);
        $this->assertGreaterThanOrEqual($min, $value);
        $this->assertLessThanOrEqual($max, $value);
    }

    /**
     * Assert that date is in valid format
     */
    protected function assertValidDate(string $date, string $format = 'Y-m-d H:i:s'): void
    {
        $d = \DateTime::createFromFormat($format, $date);
        $this->assertInstanceOf(\DateTime::class, $d);
        $this->assertEquals($date, $d->format($format));
    }
}