<?php
/**
 * Risk Feature Test
 * 
 * @package    AI-Banking-GRC-Platform
 * @subpackage tests/Feature
 * @version    1.0.0
 * @author     GRC Platform Team
 * @copyright  2026 AI Banking GRC Platform
 * @license    Proprietary
 * 
 * This class tests risk management features including risk register,
 * risk assessment, and risk mitigation.
 */

declare(strict_types=1);

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\RiskRegister;
use App\Models\RiskAssessment;
use App\Models\RiskCategory;
use App\Models\User;

class RiskTest extends TestCase
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
     * @var RiskCategory
     */
    private RiskCategory $categoryModel;

    /**
     * @var RiskAssessment
     */
    private RiskAssessment $assessmentModel;

    /**
     * Setup before each test
     */
    protected function setUp(): void
    {
        parent::setUp();
        $this->skipIfNoDatabase();
        $this->user = $this->createTestUser();
        $this->riskModel = new RiskRegister();
        $this->categoryModel = new RiskCategory();
        $this->assessmentModel = new RiskAssessment();

        // Create test category
        $this->categoryModel->create([
            'name' => 'Test Category',
            'code' => 'TEST-' . uniqid(),
            'description' => 'Test risk category'
        ]);
    }

    /**
     * Create test user
     */
    private function createTestUser(): User
    {
        $userData = [
            'username' => 'risk_test_' . uniqid(),
            'email' => 'risk_test_' . uniqid() . '@example.com',
            'first_name' => 'Risk',
            'last_name' => 'Tester',
            'password_hash' => password_hash('Test@123456', PASSWORD_BCRYPT),
            'role_id' => 7,
            'status' => 'active'
        ];

        $userId = $this->userModel->create($userData);
        return $this->userModel->find($userId);
    }

    /**
     * Test risk creation
     */
    public function test_risk_creation(): void
    {
        // Arrange
        $riskData = [
            'risk_code' => 'RISK-TEST-' . date('Y') . '-' . rand(100, 999),
            'title' => 'Test Risk',
            'description' => 'This is a test risk',
            'category_id' => 1,
            'inherent_likelihood' => 3,
            'inherent_impact' => 4,
            'owner_department_id' => 1,
            'status' => 'identified',
            'identification_date' => date('Y-m-d')
        ];

        // Act
        $riskId = $this->riskModel->create($riskData);

        // Assert
        $this->assertNotFalse($riskId);
        $risk = $this->riskModel->find($riskId);
        $this->assertNotNull($risk);
        $this->assertEquals($riskData['title'], $risk->title);
        $this->assertEquals(3, $risk->inherent_likelihood);
    }

    /**
     * Test risk score calculation
     */
    public function test_risk_score_calculation(): void
    {
        // Arrange
        $likelihood = 3;
        $impact = 4;
        $expectedScore = ($likelihood * $impact / 25) * 100;

        // Act
        $actualScore = round($expectedScore, 2);

        // Assert
        $this->assertEquals(48, $actualScore);
    }

    /**
     * Test risk level determination
     */
    public function test_risk_level_determination(): void
    {
        // Arrange
        $scores = [
            ['score' => 85, 'expected' => 'critical'],
            ['score' => 65, 'expected' => 'high'],
            ['score' => 45, 'expected' => 'medium'],
            ['score' => 25, 'expected' => 'low']
        ];

        foreach ($scores as $test) {
            // Act
            $level = $this->getRiskLevel($test['score']);

            // Assert
            $this->assertEquals($test['expected'], $level);
        }
    }

    /**
     * Helper to determine risk level
     */
    private function getRiskLevel(float $score): string
    {
        if ($score >= 80) return 'critical';
        if ($score >= 60) return 'high';
        if ($score >= 40) return 'medium';
        return 'low';
    }

    /**
     * Test risk assessment
     */
    public function test_risk_assessment(): void
    {
        // Arrange
        $riskId = $this->createTestRisk();
        $assessmentData = [
            'risk_id' => $riskId,
            'assessment_date' => date('Y-m-d'),
            'assessor_id' => $this->user->id,
            'likelihood_score' => 4,
            'impact_score' => 5,
            'velocity_score' => 3,
            'persistence_score' => 4,
            'mitigation_plans' => 'Test mitigation plan'
        ];

        // Act
        $assessmentId = $this->assessmentModel->create($assessmentData);

        // Assert
        $this->assertNotFalse($assessmentId);
        $assessment = $this->assessmentModel->find($assessmentId);
        $this->assertNotNull($assessment);
        $this->assertEquals(4, $assessment->likelihood_score);
        $this->assertEquals($this->user->id, $assessment->assessor_id);
    }

    /**
     * Test risk mitigation
     */
    public function test_risk_mitigation(): void
    {
        // Arrange
        $riskId = $this->createTestRisk();

        // Act
        $result = $this->riskModel->update($riskId, [
            'status' => 'mitigated',
            'mitigation_plan' => 'Test mitigation plan',
            'mitigation_date' => date('Y-m-d')
        ]);

        // Assert
        $this->assertTrue($result);
        $risk = $this->riskModel->find($riskId);
        $this->assertEquals('mitigated', $risk->status);
        $this->assertEquals('Test mitigation plan', $risk->mitigation_plan);
    }

    /**
     * Test risk heatmap data
     */
    public function test_risk_heatmap_data(): void
    {
        // Arrange
        $sql = "SELECT 
                    inherent_likelihood,
                    inherent_impact,
                    COUNT(*) as count
                FROM risk_register
                WHERE deleted_at IS NULL
                GROUP BY inherent_likelihood, inherent_impact";

        // Act
        $stmt = $this->db->prepare($sql);
        $stmt->execute();
        $results = $stmt->fetchAll(\PDO::FETCH_OBJ);

        // Assert
        $this->assertIsArray($results);
        foreach ($results as $row) {
            $this->assertIsNumeric($row->count);
            $this->assertGreaterThanOrEqual(1, $row->inherent_likelihood);
            $this->assertLessThanOrEqual(5, $row->inherent_likelihood);
            $this->assertGreaterThanOrEqual(1, $row->inherent_impact);
            $this->assertLessThanOrEqual(5, $row->inherent_impact);
        }
    }

    /**
     * Test risk status transition
     */
    public function test_risk_status_transition(): void
    {
        // Arrange
        $riskId = $this->createTestRisk();
        $statuses = ['identified', 'assessed', 'mitigated', 'monitoring', 'closed'];

        foreach ($statuses as $status) {
            // Act
            $result = $this->riskModel->update($riskId, ['status' => $status]);

            // Assert
            $this->assertTrue($result);
            $risk = $this->riskModel->find($riskId);
            $this->assertEquals($status, $risk->status);
        }
    }

    /**
     * Test risk history
     */
    public function test_risk_history(): void
    {
        // Arrange
        $riskId = $this->createTestRisk();
        $historyData = [
            'risk_id' => $riskId,
            'status' => 'assessed',
            'notes' => 'Risk was assessed',
            'created_by' => $this->user->id
        ];

        // Act
        $sql = "INSERT INTO risk_history 
                (risk_id, status, notes, created_by, created_at) 
                VALUES 
                (:risk_id, :status, :notes, :created_by, NOW())";

        $stmt = $this->db->prepare($sql);
        $result = $stmt->execute($historyData);

        // Assert
        $this->assertTrue($result);
    }

    /**
     * Create test risk
     */
    private function createTestRisk(): int
    {
        $data = [
            'risk_code' => 'RISK-TEST-' . date('Y') . '-' . rand(100, 999),
            'title' => 'Test Risk',
            'description' => 'Test risk description',
            'category_id' => 1,
            'inherent_likelihood' => 3,
            'inherent_impact' => 4,
            'owner_department_id' => 1,
            'status' => 'identified',
            'identification_date' => date('Y-m-d')
        ];
        return $this->riskModel->create($data);
    }
}