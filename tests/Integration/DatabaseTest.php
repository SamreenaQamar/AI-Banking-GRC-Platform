<?php
/**
 * Database Integration Test
 * 
 * @package    AI-Banking-GRC-Platform
 * @subpackage tests/Integration
 * @version    1.0.0
 * @author     GRC Platform Team
 * @copyright  2026 AI Banking GRC Platform
 * @license    Proprietary
 * 
 * This class tests database integration including connections,
 * queries, transactions, and migrations.
 */

declare(strict_types=1);

namespace Tests\Integration;

use Tests\TestCase;
use App\Helpers\Database;
use PDO;

class DatabaseTest extends TestCase
{
    /**
     * @var PDO
     */
    private PDO $db;

    /**
     * Setup before each test
     */
    protected function setUp(): void
    {
        parent::setUp();
        $this->skipIfNoDatabase();
        $this->db = Database::getInstance()->getConnection();
    }

    /**
     * Test database connection
     */
    public function test_database_connection(): void
    {
        // Act
        $result = $this->db->query('SELECT 1');

        // Assert
        $this->assertNotFalse($result);
        $row = $result->fetch();
        $this->assertEquals(1, (int)$row[0]);
    }

    /**
     * Test database transaction
     */
    public function test_database_transaction(): void
    {
        // Act
        $this->db->beginTransaction();
        $this->db->query("INSERT INTO test_table (id, name) VALUES (1, 'test')");
        $this->db->rollBack();

        // Assert - Transaction was rolled back
        $stmt = $this->db->query("SELECT COUNT(*) FROM test_table WHERE id = 1");
        $count = $stmt->fetchColumn();
        $this->assertEquals(0, (int)$count);
    }

    /**
     * Test prepared statement
     */
    public function test_prepared_statement(): void
    {
        // Arrange
        $name = 'test_user';
        $email = 'test@example.com';

        // Act
        $sql = "SELECT * FROM users WHERE username = :username";
        $stmt = $this->db->prepare($sql);
        $stmt->execute(['username' => $name]);

        // Assert
        $this->assertInstanceOf(PDOStatement::class, $stmt);
    }

    /**
     * Test query with parameters
     */
    public function test_query_with_parameters(): void
    {
        // Arrange
        $userId = 1;

        // Act
        $sql = "SELECT id, username FROM users WHERE id = :id";
        $stmt = $this->db->prepare($sql);
        $stmt->execute(['id' => $userId]);
        $result = $stmt->fetch(PDO::FETCH_OBJ);

        // Assert
        if ($result) {
            $this->assertIsObject($result);
            $this->assertObjectHasProperty('id', $result);
            $this->assertObjectHasProperty('username', $result);
        }
    }

    /**
     * Test database insert
     */
    public function test_database_insert(): void
    {
        // Arrange
        $data = [
            'username' => 'test_insert_' . uniqid(),
            'email' => 'test_insert_' . uniqid() . '@example.com',
            'first_name' => 'Insert',
            'last_name' => 'Test',
            'password_hash' => password_hash('test123', PASSWORD_BCRYPT),
            'role_id' => 7,
            'status' => 'active',
            'created_at' => date('Y-m-d H:i:s')
        ];

        // Act
        $columns = array_keys($data);
        $placeholders = array_map(fn($col) => ":$col", $columns);
        $sql = "INSERT INTO users (" . implode(', ', $columns) . ") 
                VALUES (" . implode(', ', $placeholders) . ")";
        
        $stmt = $this->db->prepare($sql);
        $result = $stmt->execute($data);
        $insertId = (int)$this->db->lastInsertId();

        // Assert
        $this->assertTrue($result);
        $this->assertGreaterThan(0, $insertId);
    }

    /**
     * Test database update
     */
    public function test_database_update(): void
    {
        // Arrange
        $userId = $this->insertTestUser();
        $newEmail = 'updated_' . uniqid() . '@example.com';

        // Act
        $sql = "UPDATE users SET email = :email WHERE id = :id";
        $stmt = $this->db->prepare($sql);
        $result = $stmt->execute([
            'email' => $newEmail,
            'id' => $userId
        ]);

        // Assert
        $this->assertTrue($result);
    }

    /**
     * Test database delete
     */
    public function test_database_delete(): void
    {
        // Arrange
        $userId = $this->insertTestUser();

        // Act
        $sql = "DELETE FROM users WHERE id = :id";
        $stmt = $this->db->prepare($sql);
        $result = $stmt->execute(['id' => $userId]);

        // Assert
        $this->assertTrue($result);
    }

    /**
     * Test foreign key constraint
     */
    public function test_foreign_key_constraint(): void
    {
        // Arrange
        $this->expectException(\PDOException::class);

        // Act - Try to insert invalid foreign key
        $sql = "INSERT INTO users (username, email, first_name, last_name, password_hash, role_id) 
                VALUES ('test', 'test@example.com', 'Test', 'User', 'hash', 999)";
        $this->db->query($sql);
    }

    /**
     * Test unique constraint
     */
    public function test_unique_constraint(): void
    {
        // Arrange
        $this->expectException(\PDOException::class);

        // Act - Try to insert duplicate username
        $sql = "INSERT INTO users (username, email, first_name, last_name, password_hash, role_id) 
                VALUES ('testuser', 'test1@example.com', 'Test', 'User', 'hash', 7)";
        $this->db->query($sql);
        
        $sql2 = "INSERT INTO users (username, email, first_name, last_name, password_hash, role_id) 
                VALUES ('testuser', 'test2@example.com', 'Test', 'User', 'hash', 7)";
        $this->db->query($sql2);
    }

    /**
     * Insert test user
     */
    private function insertTestUser(): int
    {
        $sql = "INSERT INTO users (username, email, first_name, last_name, password_hash, role_id, status, created_at) 
                VALUES (:username, :email, :first_name, :last_name, :password, :role_id, :status, NOW())";
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute([
            'username' => 'test_' . uniqid(),
            'email' => 'test_' . uniqid() . '@example.com',
            'first_name' => 'Test',
            'last_name' => 'User',
            'password' => password_hash('test123', PASSWORD_BCRYPT),
            'role_id' => 7,
            'status' => 'active'
        ]);
        
        return (int)$this->db->lastInsertId();
    }
}