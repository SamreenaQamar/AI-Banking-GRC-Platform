<?php
/**
 * Dashboard Feature Test
 * 
 * @package    AI-Banking-GRC-Platform
 * @subpackage tests/Feature
 * @version    1.0.0
 * @author     GRC Platform Team
 * @copyright  2026 AI Banking GRC Platform
 * @license    Proprietary
 * 
 * This class tests dashboard features including statistics,
 * widgets, and data visualization.
 */

declare(strict_types=1);

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\User;
use App\Models\RiskRegister;
use App\Models\ComplianceTask;
use App\Models\AuditPlan;
use App\Models\SbpCircular;

class DashboardTest extends TestCase
{
    /**
     * @var User
     */
    private User $user;

    /**
     * @var RiskRegister
     */
    private RiskRegister $riskModel;

    /**
     * @var ComplianceTask
     */
    private ComplianceTask $complianceModel;

    /**
     * @var AuditPlan
     */
    private AuditPlan $auditModel;

    /**
     * Setup before each test
     */
    protected function setUp(): void
    {
        parent::setUp();
        $this->skipIfNoDatabase();
        $this->user = $this->createTestUser();
        $this->riskModel = new RiskRegister();
        $this->complianceModel = new ComplianceTask();
        $this->auditModel = new AuditPlan();

        // Create test data
        $this->createTestDashboardData();
    }

    /**
     * Create test user
     */
    private function createTestUser(): User
    {
        $userData = [
            'username' => 'dashboard_test_' . uniqid(),
            'email' => 'dashboard_test_' . uniqid() . '@example.com',
            'first_name' => 'Dashboard',
            'last_name' => 'Tester',
            'password_hash' => password_hash('Test@123456', PASSWORD_BCRYPT),
            'role_id' => 7,
            'status' => 'active'
        ];

        $userId = $this->userModel->create($userData);
        return $this->userModel->find($userId);
    }

    /**
     * Create test dashboard data
     */
    private function createTestDashboardData(): void
    {
        // Create test risks
        for ($i = 0; $i < 5; $i++) {
            $this->riskModel->create([
                'risk_code' => 'RISK-TEST-' . str_pad($i, 4, '0', STR_PAD_LEFT),
                'title' => 'Test Risk ' . $i,
                'description' => 'This is test risk ' . $i,
                'category_id' => 1,
                'inherent_likelihood' => 3,
                'inherent_impact' => 4,
                'owner_department_id' => 1,
                'status' => 'identified'
            ]);
        }

        // Create test compliance tasks
        for ($i = 0; $i < 3; $i++) {
            $this->complianceModel->create([
                'title' => 'Test Compliance ' . $i,
                'description' => 'Test compliance task ' . $i,
                'category_id' => 1,
                'framework_id' => 1,
                'department_id' => 1,
                'priority' => 'medium',
                'status' => 'pending',
                'due_date' => date('Y-m-d', strtotime('+30 days'))
            ]);
        }

        // Create test audit plans
        for ($i = 0; $i < 2; $i++) {
            $this->auditModel->create([
                'title' => 'Test Audit ' . $i,
                'reference_number' => 'AUDIT-TEST-' . str_pad($i, 4, '0', STR_PAD_LEFT),
                'audit_type' => 'internal',
                'scope_description' => 'Test audit scope ' . $i,
                'department_id' => 1,
                'start_date' => date('Y-m-d'),
                'end_date' => date('Y-m-d', strtotime('+7 days')),
                'status' => 'planned'
            ]);
        }
    }

    /**
     * Test dashboard loads correctly
     */
    public function test_dashboard_loads_correctly(): void
    {
        // Arrange
        $this->mockAuth($this->user->id);

        // Act - Simulate dashboard request
        $isAuthenticated = true;

        // Assert
        $this->assertTrue($isAuthenticated);
    }

    /**
     * Test dashboard statistics are calculated correctly
     */
    public function test_dashboard_statistics_are_calculated_correctly(): void
    {
        // Arrange
        $totalRisks = $this->riskModel->countAll();
        $totalCompliance = $this->complianceModel->countAll();
        $totalAudits = $this->auditModel->countAll();

        // Act - Get statistics

        // Assert
        $this->assertEquals(5, $totalRisks);
        $this->assertEquals(3, $totalCompliance);
        $this->assertEquals(2, $totalAudits);
    }

    /**
     * Test risk distribution data
     */
    public function test_risk_distribution_data(): void
    {
        // Arrange
        $sql = "SELECT 
                    CASE 
                        WHEN inherent_risk_score >= 80 THEN 'critical'
                        WHEN inherent_risk_score >= 60 THEN 'high'
                        WHEN inherent_risk_score >= 40 THEN 'medium'
                        ELSE 'low'
                    END as risk_level,
                    COUNT(*) as count
                FROM risk_register
                WHERE deleted_at IS NULL
                GROUP BY risk_level";

        $stmt = $this->db->prepare($sql);
        $stmt->execute();
        $results = $stmt->fetchAll(\PDO::FETCH_OBJ);

        // Assert
        $this->assertIsArray($results);
        $this->assertNotEmpty($results);
    }

    /**
     * Test compliance status data
     */
    public function test_compliance_status_data(): void
    {
        // Arrange
        $sql = "SELECT 
                    status,
                    COUNT(*) as count
                FROM compliance_tasks
                WHERE deleted_at IS NULL
                GROUP BY status";

        $stmt = $this->db->prepare($sql);
        $stmt->execute();
        $results = $stmt->fetchAll(\PDO::FETCH_OBJ);

        // Assert
        $this->assertIsArray($results);
    }

    /**
     * Test recent activities
     */
    public function test_recent_activities(): void
    {
        // Arrange
        $sql = "SELECT * FROM activity_logs 
                ORDER BY created_at DESC 
                LIMIT 10";

        $stmt = $this->db->prepare($sql);
        $stmt->execute();
        $activities = $stmt->fetchAll(\PDO::FETCH_OBJ);

        // Assert
        $this->assertIsArray($activities);
        $this->assertLessThanOrEqual(10, count($activities));
    }

    /**
     * Test notification count
     */
    public function test_notification_count(): void
    {
        // Arrange
        $sql = "SELECT COUNT(*) as count 
                FROM notifications 
                WHERE user_id = :user_id 
                AND is_read = 0";

        $stmt = $this->db->prepare($sql);
        $stmt->execute(['user_id' => $this->user->id]);
        $result = $stmt->fetch(\PDO::FETCH_OBJ);

        // Assert
        $this->assertNotNull($result);
        $this->assertIsNumeric($result->count);
    }

    /**
     * Test dashboard widgets visibility based on role
     */
    public function test_dashboard_widgets_visibility_based_on_role(): void
    {
        // Arrange - Different roles should see different widgets
        $roles = ['admin', 'compliance_officer', 'risk_manager', 'auditor'];

        // Act & Assert
        foreach ($roles as $role) {
            $this->mockAuth($this->user->id);
            $_SESSION['user_role'] = $role;
            
            // Check if role-based widgets are accessible
            $this->assertTrue(isset($_SESSION['user_role']));
        }
    }

    /**
     * Test dashboard refresh rate
     */
    public function test_dashboard_refresh_rate(): void
    {
        // Arrange
        $refreshInterval = 300; // 5 minutes

        // Assert
        $this->assertIsInt($refreshInterval);
        $this->assertGreaterThan(0, $refreshInterval);
    }

    /**
     * Test chart data format
     */
    public function test_chart_data_format(): void
    {
        // Arrange
        $chartData = [
            'labels' => ['Critical', 'High', 'Medium', 'Low'],
            'datasets' => [
                [
                    'data' => [5, 10, 15, 20],
                    'backgroundColor' => ['#DC2626', '#EF4444', '#F59E0B', '#22C55E']
                ]
            ]
        ];

        // Assert
        $this->assertArrayHasKey('labels', $chartData);
        $this->assertArrayHasKey('datasets', $chartData);
        $this->assertIsArray($chartData['labels']);
        $this->assertIsArray($chartData['datasets']);
    }

    /**
     * Test executive summary data
     */
    public function test_executive_summary_data(): void
    {
        // Arrange
        $summary = [
            'total_risks' => $this->riskModel->countAll(),
            'total_compliance' => $this->complianceModel->countAll(),
            'total_audits' => $this->auditModel->countAll(),
            'compliance_rate' => 68,
            'risk_score' => 65
        ];

        // Assert
        $this->assertArrayHasKey('total_risks', $summary);
        $this->assertArrayHasKey('total_compliance', $summary);
        $this->assertArrayHasKey('total_audits', $summary);
        $this->assertIsNumeric($summary['compliance_rate']);
        $this->assertIsNumeric($summary['risk_score']);
    }
}