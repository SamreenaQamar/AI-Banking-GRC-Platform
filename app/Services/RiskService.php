<?php
/**
 * AI Banking GRC Platform - Risk Service
 * 
 * @package    AI-Banking-GRC-Platform
 * @subpackage app/Services
 * @version    1.0.0
 * @author     GRC Platform Team
 * @copyright  2026 AI Banking GRC Platform
 * @license    Proprietary
 * 
 * This service handles risk management business logic:
 * - Risk CRUD operations
 * - Risk register management
 * - Risk owner assignment
 * - Risk status management
 * - Risk reports
 * - Risk assessment
 */

declare(strict_types=1);

namespace App\Services;

use App\Models\RiskRegister;
use App\Models\RiskCategory;
use App\Models\RiskAssessment;
use App\Models\RiskTreatment;
use App\Models\ActivityLog;
use App\Libraries\Logger;
use App\Libraries\Validator;
use App\Libraries\Cache;

class RiskService
{
    /**
     * @var RiskRegister Risk model
     */
    private RiskRegister $riskModel;

    /**
     * @var RiskCategory Category model
     */
    private RiskCategory $categoryModel;

    /**
     * @var RiskAssessment Assessment model
     */
    private RiskAssessment $assessmentModel;

    /**
     * @var RiskTreatment Treatment model
     */
    private RiskTreatment $treatmentModel;

    /**
     * @var ActivityLog Activity log model
     */
    private ActivityLog $activityLogModel;

    /**
     * @var Logger Logger instance
     */
    private Logger $logger;

    /**
     * @var Validator Validator instance
     */
    private Validator $validator;

    /**
     * @var Cache Cache instance
     */
    private Cache $cache;

    /**
     * Constructor
     */
    public function __construct()
    {
        $this->riskModel = new RiskRegister();
        $this->categoryModel = new RiskCategory();
        $this->assessmentModel = new RiskAssessment();
        $this->treatmentModel = new RiskTreatment();
        $this->activityLogModel = new ActivityLog();
        $this->logger = new Logger();
        $this->validator = new Validator();
        $this->cache = new Cache();
    }

    /**
     * Create a new risk
     * 
     * @param array $data
     * @param int $createdBy
     * @return array
     */
    public function create(array $data, int $createdBy): array
    {
        try {
            $rules = [
                'title' => ['required', 'min:3', 'max:255'],
                'description' => ['required', 'min:10'],
                'category_id' => ['required', 'exists:risk_categories,id'],
                'owner_department_id' => ['required', 'exists:departments,id'],
                'inherent_likelihood' => ['required', 'numeric', 'between:1,5'],
                'inherent_impact' => ['required', 'numeric', 'between:1,5'],
                'identification_date' => ['required', 'date']
            ];

            if (!$this->validator->validate($data, $rules)) {
                return $this->errorResponse('Validation failed.', 'VALIDATION_ERROR', [
                    'errors' => $this->validator->getErrors()
                ]);
            }

            // Generate risk code
            $riskCode = $this->generateRiskCode();

            // Calculate risk score
            $likelihood = (int)$data['inherent_likelihood'];
            $impact = (int)$data['inherent_impact'];
            $score = ($likelihood * $impact / 25) * 100;
            $level = $this->getRiskLevel($score);

            $riskData = [
                'risk_code' => $riskCode,
                'title' => $data['title'],
                'description' => $data['description'],
                'category_id' => $data['category_id'],
                'owner_department_id' => $data['owner_department_id'],
                'owner_user_id' => $data['owner_user_id'] ?? null,
                'inherent_likelihood' => $likelihood,
                'inherent_impact' => $impact,
                'inherent_risk_score' => $score,
                'risk_level' => $level,
                'status' => 'identified',
                'identification_date' => $data['identification_date'],
                'created_by' => $createdBy,
                'created_at' => date('Y-m-d H:i:s')
            ];

            $riskId = $this->riskModel->create($riskData);

            if (!$riskId) {
                return $this->errorResponse('Failed to create risk.', 'CREATE_FAILED');
            }

            // Log activity
            $this->activityLogModel->logCreate($createdBy, 'risk', 'risk', $riskId, $riskData);

            $this->logger->info('Risk created', [
                'risk_id' => $riskId,
                'risk_code' => $riskCode,
                'created_by' => $createdBy
            ]);

            return $this->successResponse('Risk created successfully.', [
                'risk_id' => $riskId,
                'risk_code' => $riskCode
            ]);

        } catch (\Exception $e) {
            $this->logger->error('Create risk error: ' . $e->getMessage());
            return $this->errorResponse('An error occurred creating risk.', 'ERROR');
        }
    }

    /**
     * Update risk
     * 
     * @param int $riskId
     * @param array $data
     * @param int $updatedBy
     * @return array
     */
    public function update(int $riskId, array $data, int $updatedBy): array
    {
        try {
            $risk = $this->riskModel->find($riskId);
            if (!$risk) {
                return $this->errorResponse('Risk not found.', 'RISK_NOT_FOUND');
            }

            $rules = [
                'title' => ['required', 'min:3', 'max:255'],
                'description' => ['required', 'min:10'],
                'category_id' => ['required', 'exists:risk_categories,id'],
                'owner_department_id' => ['required', 'exists:departments,id'],
                'inherent_likelihood' => ['required', 'numeric', 'between:1,5'],
                'inherent_impact' => ['required', 'numeric', 'between:1,5'],
                'status' => ['in:identified,assessed,mitigating,mitigated,monitored,review,closed,rejected']
            ];

            if (!$this->validator->validate($data, $rules)) {
                return $this->errorResponse('Validation failed.', 'VALIDATION_ERROR', [
                    'errors' => $this->validator->getErrors()
                ]);
            }

            // Calculate risk score
            $likelihood = (int)$data['inherent_likelihood'];
            $impact = (int)$data['inherent_impact'];
            $score = ($likelihood * $impact / 25) * 100;
            $level = $this->getRiskLevel($score);

            $updateData = [
                'title' => $data['title'],
                'description' => $data['description'],
                'category_id' => $data['category_id'],
                'owner_department_id' => $data['owner_department_id'],
                'owner_user_id' => $data['owner_user_id'] ?? null,
                'inherent_likelihood' => $likelihood,
                'inherent_impact' => $impact,
                'inherent_risk_score' => $score,
                'risk_level' => $level,
                'status' => $data['status'] ?? $risk->status,
                'updated_at' => date('Y-m-d H:i:s')
            ];

            $result = $this->riskModel->update($riskId, $updateData);

            if (!$result) {
                return $this->errorResponse('Failed to update risk.', 'UPDATE_FAILED');
            }

            // Log activity
            $this->activityLogModel->logChange($updatedBy, 'risk', 'risk', $riskId, (array)$risk, $updateData);

            $this->logger->info('Risk updated', [
                'risk_id' => $riskId,
                'updated_by' => $updatedBy
            ]);

            return $this->successResponse('Risk updated successfully.');

        } catch (\Exception $e) {
            $this->logger->error('Update risk error: ' . $e->getMessage());
            return $this->errorResponse('An error occurred updating risk.', 'ERROR');
        }
    }

    /**
     * Delete risk
     * 
     * @param int $riskId
     * @param int $deletedBy
     * @return array
     */
    public function delete(int $riskId, int $deletedBy): array
    {
        try {
            $risk = $this->riskModel->find($riskId);
            if (!$risk) {
                return $this->errorResponse('Risk not found.', 'RISK_NOT_FOUND');
            }

            $result = $this->riskModel->softDelete($riskId);

            if (!$result) {
                return $this->errorResponse('Failed to delete risk.', 'DELETE_FAILED');
            }

            // Log activity
            $this->activityLogModel->logDelete($deletedBy, 'risk', 'risk', $riskId, (array)$risk);

            $this->logger->info('Risk deleted', [
                'risk_id' => $riskId,
                'deleted_by' => $deletedBy
            ]);

            return $this->successResponse('Risk deleted successfully.');

        } catch (\Exception $e) {
            $this->logger->error('Delete risk error: ' . $e->getMessage());
            return $this->errorResponse('An error occurred deleting risk.', 'ERROR');
        }
    }

    /**
     * Get risk by ID
     * 
     * @param int $riskId
     * @return array
     */
    public function find(int $riskId): array
    {
        try {
            $risk = $this->riskModel->find($riskId);
            if (!$risk) {
                return $this->errorResponse('Risk not found.', 'RISK_NOT_FOUND');
            }

            $assessments = $this->assessmentModel->getByRiskId($riskId);
            $treatments = $this->treatmentModel->getByRiskId($riskId);
            $history = $this->riskModel->getHistory($riskId);

            return $this->successResponse('Risk retrieved.', [
                'risk' => $risk,
                'assessments' => $assessments,
                'treatments' => $treatments,
                'history' => $history
            ]);

        } catch (\Exception $e) {
            $this->logger->error('Find risk error: ' . $e->getMessage());
            return $this->errorResponse('An error occurred.', 'ERROR');
        }
    }

    /**
     * Get all risks with pagination
     * 
     * @param array $filters
     * @param int $page
     * @param int $perPage
     * @return array
     */
    public function all(array $filters = [], int $page = 1, int $perPage = 15): array
    {
        try {
            $risks = $this->riskModel->getFiltered($filters, $page, $perPage);
            $total = $this->riskModel->countFiltered($filters);

            return $this->successResponse('Risks retrieved.', [
                'risks' => $risks,
                'total' => $total,
                'page' => $page,
                'per_page' => $perPage,
                'total_pages' => ceil($total / $perPage)
            ]);

        } catch (\Exception $e) {
            $this->logger->error('Get all risks error: ' . $e->getMessage());
            return $this->errorResponse('An error occurred.', 'ERROR');
        }
    }

    /**
     * Assess a risk
     * 
     * @param int $riskId
     * @param array $data
     * @param int $userId
     * @return array
     */
    public function assess(int $riskId, array $data, int $userId): array
    {
        try {
            $risk = $this->riskModel->find($riskId);
            if (!$risk) {
                return $this->errorResponse('Risk not found.', 'RISK_NOT_FOUND');
            }

            $rules = [
                'likelihood_score' => ['required', 'numeric', 'between:1,5'],
                'impact_score' => ['required', 'numeric', 'between:1,5'],
                'velocity_score' => ['numeric', 'between:1,5'],
                'persistence_score' => ['numeric', 'between:1,5'],
                'mitigation_plans' => ['string', 'max:2000'],
                'recommendations' => ['string', 'max:2000']
            ];

            if (!$this->validator->validate($data, $rules)) {
                return $this->errorResponse('Validation failed.', 'VALIDATION_ERROR', [
                    'errors' => $this->validator->getErrors()
                ]);
            }

            $assessmentData = [
                'risk_id' => $riskId,
                'assessment_date' => date('Y-m-d'),
                'assessor_id' => $userId,
                'likelihood_score' => $data['likelihood_score'],
                'impact_score' => $data['impact_score'],
                'velocity_score' => $data['velocity_score'] ?? 3,
                'persistence_score' => $data['persistence_score'] ?? 3,
                'mitigation_plans' => $data['mitigation_plans'] ?? null,
                'recommendations' => $data['recommendations'] ?? null,
                'action_required' => $data['action_required'] ?? false,
                'action_deadline' => $data['action_deadline'] ?? null,
                'created_by' => $userId,
                'created_at' => date('Y-m-d H:i:s')
            ];

            $assessmentId = $this->assessmentModel->create($assessmentData);

            if (!$assessmentId) {
                return $this->errorResponse('Failed to create risk assessment.', 'ASSESSMENT_FAILED');
            }

            // Update risk status
            $this->riskModel->update($riskId, [
                'status' => 'assessed',
                'assessment_date' => date('Y-m-d')
            ]);

            // Log activity
            $this->activityLogModel->logCreate($userId, 'risk', 'assessment', $assessmentId, $assessmentData);

            $this->logger->info('Risk assessment created', [
                'risk_id' => $riskId,
                'assessment_id' => $assessmentId,
                'user_id' => $userId
            ]);

            return $this->successResponse('Risk assessment completed.', [
                'assessment_id' => $assessmentId
            ]);

        } catch (\Exception $e) {
            $this->logger->error('Assess risk error: ' . $e->getMessage());
            return $this->errorResponse('An error occurred assessing risk.', 'ERROR');
        }
    }

    /**
     * Generate risk report
     * 
     * @param array $filters
     * @return array
     */
    public function report(array $filters = []): array
    {
        try {
            $risks = $this->riskModel->getFiltered($filters, 1, 1000);
            $stats = $this->getRiskStats();

            return [
                'success' => true,
                'data' => $risks,
                'statistics' => $stats,
                'generated_at' => date('Y-m-d H:i:s'),
                'count' => count($risks)
            ];

        } catch (\Exception $e) {
            $this->logger->error('Risk report error: ' . $e->getMessage());
            return $this->errorResponse('Failed to generate risk report.');
        }
    }

    /**
     * Get risk statistics
     * 
     * @return array
     */
    private function getRiskStats(): array
    {
        return [
            'total' => $this->riskModel->countAll(),
            'critical' => $this->riskModel->countByLevel('critical'),
            'high' => $this->riskModel->countByLevel('high'),
            'medium' => $this->riskModel->countByLevel('medium'),
            'low' => $this->riskModel->countByLevel('low'),
            'by_status' => [
                'identified' => $this->riskModel->countByStatus('identified'),
                'assessed' => $this->riskModel->countByStatus('assessed'),
                'mitigating' => $this->riskModel->countByStatus('mitigating'),
                'mitigated' => $this->riskModel->countByStatus('mitigated'),
                'monitored' => $this->riskModel->countByStatus('monitored'),
                'closed' => $this->riskModel->countByStatus('closed')
            ],
            'avg_score' => $this->riskModel->getAverageRiskScore()
        ];
    }

    /**
     * Generate risk code
     * 
     * @return string
     */
    private function generateRiskCode(): string
    {
        $year = date('Y');
        $random = str_pad(random_int(1, 9999), 4, '0', STR_PAD_LEFT);
        return 'RISK-' . $year . '-' . $random;
    }

    /**
     * Get risk level
     * 
     * @param float $score
     * @return string
     */
    private function getRiskLevel(float $score): string
    {
        if ($score >= 80) return 'critical';
        if ($score >= 60) return 'high';
        if ($score >= 40) return 'medium';
        if ($score >= 20) return 'low';
        return 'very_low';
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