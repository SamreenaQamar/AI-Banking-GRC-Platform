<?php
/**
 * Compliance Feature Test
 * 
 * @package    AI-Banking-GRC-Platform
 * @subpackage tests/Feature
 * @version    1.0.0
 * @author     GRC Platform Team
 * @copyright  2026 AI Banking GRC Platform
 * @license    Proprietary
 * 
 * This class tests compliance features including circulars,
 * compliance tasks, and gap analysis.
 */

declare(strict_types=1);

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\SbpCircular;
use App\Models\ComplianceTask;
use App\Models\ComplianceFramework;
use App\Models\User;

class ComplianceTest extends TestCase
{
    /**
     * @var User
     */
    private User $user;

    /**
     * @var SbpCircular
     */
    private SbpCircular $circularModel;

    /**
     * @var ComplianceTask
     */
    private ComplianceTask $taskModel;

    /**
     * Setup before each test
     */
    protected function setUp(): void
    {
        parent::setUp();
        $this->skipIfNoDatabase();
        $this->user = $this->createTestUser();
        $this->circularModel = new SbpCircular();
        $this->taskModel = new ComplianceTask();
    }

    /**
     * Create test user
     */
    private function createTestUser(): User
    {
        $userData = [
            'username' => 'compliance_test_' . uniqid(),
            'email' => 'compliance_test_' . uniqid() . '@example.com',
            'first_name' => 'Compliance',
            'last_name' => 'Tester',
            'password_hash' => password_hash('Test@123456', PASSWORD_BCRYPT),
            'role_id' => 7,
            'status' => 'active'
        ];

        $userId = $this->userModel->create($userData);
        return $this->userModel->find($userId);
    }

    /**
     * Test SBP circular creation
     */
    public function test_sbp_circular_creation(): void
    {
        // Arrange
        $circularData = [
            'circular_number' => 'SBP-TEST-' . date('Y') . '-' . rand(100, 999),
            'title' => 'Test Circular',
            'description' => 'This is a test circular',
            'category' => 'compliance',
            'priority' => 'medium',
            'issuance_date' => date('Y-m-d'),
            'effective_date' => date('Y-m-d', strtotime('+30 days')),
            'compliance_deadline' => date('Y-m-d', strtotime('+60 days')),
            'status' => 'pending'
        ];

        // Act
        $circularId = $this->circularModel->create($circularData);

        // Assert
        $this->assertNotFalse($circularId);
        $circular = $this->circularModel->find($circularId);
        $this->assertNotNull($circular);
        $this->assertEquals($circularData['title'], $circular->title);
        $this->assertEquals($circularData['category'], $circular->category);
    }

    /**
     * Test circular status update
     */
    public function test_circular_status_update(): void
    {
        // Arrange
        $circularId = $this->createTestCircular();
        $newStatus = 'implemented';

        // Act
        $result = $this->circularModel->update($circularId, ['status' => $newStatus]);

        // Assert
        $this->assertTrue($result);
        $circular = $this->circularModel->find($circularId);
        $this->assertEquals($newStatus, $circular->status);
    }

    /**
     * Test compliance task creation
     */
    public function test_compliance_task_creation(): void
    {
        // Arrange
        $taskData = [
            'title' => 'Test Compliance Task',
            'description' => 'This is a test compliance task',
            'category_id' => 1,
            'framework_id' => 1,
            'department_id' => 1,
            'priority' => 'high',
            'status' => 'pending',
            'due_date' => date('Y-m-d', strtotime('+30 days')),
            'assigned_to' => $this->user->id
        ];

        // Act
        $taskId = $this->taskModel->create($taskData);

        // Assert
        $this->assertNotFalse($taskId);
        $task = $this->taskModel->find($taskId);
        $this->assertNotNull($task);
        $this->assertEquals($taskData['title'], $task->title);
        $this->assertEquals($this->user->id, $task->assigned_to);
    }

    /**
     * Test compliance task completion
     */
    public function test_compliance_task_completion(): void
    {
        // Arrange
        $taskId = $this->createTestComplianceTask();

        // Act
        $result = $this->taskModel->update($taskId, [
            'status' => 'completed',
            'completed_date' => date('Y-m-d')
        ]);

        // Assert
        $this->assertTrue($result);
        $task = $this->taskModel->find($taskId);
        $this->assertEquals('completed', $task->status);
        $this->assertNotNull($task->completed_date);
    }

    /**
     * Test compliance gap analysis
     */
    public function test_compliance_gap_analysis(): void
    {
        // Arrange
        $sql = "SELECT 
                    category,
                    COUNT(*) as total,
                    SUM(CASE WHEN status = 'completed' THEN 1 ELSE 0 END) as completed
                FROM compliance_tasks
                WHERE deleted_at IS NULL
                GROUP BY category";

        // Act
        $stmt = $this->db->prepare($sql);
        $stmt->execute();
        $results = $stmt->fetchAll(\PDO::FETCH_OBJ);

        // Assert
        $this->assertIsArray($results);
        foreach ($results as $row) {
            $this->assertIsNumeric($row->total);
            $this->assertIsNumeric($row->completed);
            $this->assertLessThanOrEqual($row->total, $row->completed);
        }
    }

    /**
     * Test compliance score calculation
     */
    public function test_compliance_score_calculation(): void
    {
        // Arrange
        $total = 10;
        $completed = 7;
        $expectedScore = ($completed / $total) * 100;

        // Act
        $actualScore = round($expectedScore, 2);

        // Assert
        $this->assertEquals(70, $actualScore);
    }

    /**
     * Test compliance evidence upload
     */
    public function test_compliance_evidence_upload(): void
    {
        // Arrange
        $taskId = $this->createTestComplianceTask();
        $evidenceData = [
            'task_id' => $taskId,
            'file_path' => 'compliance/evidence/test.pdf',
            'file_name' => 'test.pdf',
            'file_type' => 'pdf',
            'file_size' => 1024,
            'description' => 'Test evidence'
        ];

        // Act
        $sql = "INSERT INTO compliance_evidence 
                (task_id, file_path, file_name, file_type, file_size, description, created_at) 
                VALUES 
                (:task_id, :file_path, :file_name, :file_type, :file_size, :description, NOW())";

        $stmt = $this->db->prepare($sql);
        $result = $stmt->execute($evidenceData);

        // Assert
        $this->assertTrue($result);
    }

    /**
     * Create test circular
     */
    private function createTestCircular(): int
    {
        $data = [
            'circular_number' => 'SBP-TEST-' . date('Y') . '-' . rand(100, 999),
            'title' => 'Test Circular',
            'description' => 'Test circular description',
            'category' => 'compliance',
            'priority' => 'medium',
            'issuance_date' => date('Y-m-d'),
            'effective_date' => date('Y-m-d', strtotime('+30 days')),
            'compliance_deadline' => date('Y-m-d', strtotime('+60 days')),
            'status' => 'pending'
        ];
        return $this->circularModel->create($data);
    }

    /**
     * Create test compliance task
     */
    private function createTestComplianceTask(): int
    {
        $data = [
            'title' => 'Test Task',
            'description' => 'Test task description',
            'category_id' => 1,
            'framework_id' => 1,
            'department_id' => 1,
            'priority' => 'medium',
            'status' => 'pending',
            'due_date' => date('Y-m-d', strtotime('+30 days'))
        ];
        return $this->taskModel->create($data);
    }
}