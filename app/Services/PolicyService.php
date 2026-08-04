<?php
/**
 * AI Banking GRC Platform - Policy Service
 * 
 * @package    AI-Banking-GRC-Platform
 * @subpackage app/Services
 * @version    1.0.0
 * @author     GRC Platform Team
 * @copyright  2026 AI Banking GRC Platform
 * @license    Proprietary
 * 
 * This service handles policy management business logic:
 * - Policy CRUD operations
 * - Policy approval workflow
 * - Policy status management
 * - Policy version history
 */

declare(strict_types=1);

namespace App\Services;

use App\Models\Policy;
use App\Models\PolicyVersion;
use App\Models\PolicyLibrary;
use App\Models\ActivityLog;
use App\Libraries\Logger;
use App\Libraries\Validator;
use App\Libraries\Cache;

class PolicyService
{
    /**
     * @var Policy Policy model
     */
    private Policy $policyModel;

    /**
     * @var PolicyVersion Version model
     */
    private PolicyVersion $versionModel;

    /**
     * @var PolicyLibrary Library model
     */
    private PolicyLibrary $libraryModel;

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
        $this->policyModel = new Policy();
        $this->versionModel = new PolicyVersion();
        $this->libraryModel = new PolicyLibrary();
        $this->activityLogModel = new ActivityLog();
        $this->logger = new Logger();
        $this->validator = new Validator();
        $this->cache = new Cache();
    }

    /**
     * Create a new policy
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
                'category' => ['required', 'in:governance,risk_management,compliance,information_security,data_privacy,human_resources,finance,operations,it,business_continuity,aml,fraud'],
                'description' => ['required', 'min:10'],
                'effective_date' => ['required', 'date'],
                'version' => ['required', 'min:1'],
                'status' => ['in:draft,review,approved,active,archived,expired']
            ];

            if (!$this->validator->validate($data, $rules)) {
                return $this->errorResponse('Validation failed.', 'VALIDATION_ERROR', [
                    'errors' => $this->validator->getErrors()
                ]);
            }

            // Generate policy number
            $policyNumber = $this->generatePolicyNumber();

            $policyData = [
                'policy_number' => $policyNumber,
                'title' => $data['title'],
                'category' => $data['category'],
                'description' => $data['description'],
                'version' => $data['version'] ?? '1.0',
                'effective_date' => $data['effective_date'],
                'review_date' => $data['review_date'] ?? null,
                'expiry_date' => $data['expiry_date'] ?? null,
                'mandatory' => $data['mandatory'] ?? true,
                'acknowledges_required' => $data['acknowledges_required'] ?? true,
                'status' => $data['status'] ?? 'draft',
                'created_by' => $createdBy,
                'created_at' => date('Y-m-d H:i:s')
            ];

            $policyId = $this->policyModel->create($policyData);

            if (!$policyId) {
                return $this->errorResponse('Failed to create policy.', 'CREATE_FAILED');
            }

            // Create initial version
            $this->createVersion($policyId, $policyData['version'], $createdBy);

            // Create library entry
            $this->createLibraryEntry($policyId, $data);

            // Log activity
            $this->activityLogModel->logCreate($createdBy, 'policy', 'policy', $policyId, $policyData);

            $this->logger->info('Policy created', [
                'policy_id' => $policyId,
                'policy_number' => $policyNumber,
                'created_by' => $createdBy
            ]);

            return $this->successResponse('Policy created successfully.', [
                'policy_id' => $policyId,
                'policy_number' => $policyNumber
            ]);

        } catch (\Exception $e) {
            $this->logger->error('Create policy error: ' . $e->getMessage());
            return $this->errorResponse('An error occurred creating policy.', 'ERROR');
        }
    }

    /**
     * Update policy
     * 
     * @param int $policyId
     * @param array $data
     * @param int $updatedBy
     * @return array
     */
    public function update(int $policyId, array $data, int $updatedBy): array
    {
        try {
            $policy = $this->policyModel->find($policyId);
            if (!$policy) {
                return $this->errorResponse('Policy not found.', 'POLICY_NOT_FOUND');
            }

            $rules = [
                'title' => ['required', 'min:3', 'max:255'],
                'category' => ['required', 'in:governance,risk_management,compliance,information_security,data_privacy,human_resources,finance,operations,it,business_continuity,aml,fraud'],
                'description' => ['required', 'min:10'],
                'effective_date' => ['required', 'date'],
                'version' => ['required', 'min:1'],
                'status' => ['in:draft,review,approved,active,archived,expired']
            ];

            if (!$this->validator->validate($data, $rules)) {
                return $this->errorResponse('Validation failed.', 'VALIDATION_ERROR', [
                    'errors' => $this->validator->getErrors()
                ]);
            }

            $updateData = [
                'title' => $data['title'],
                'category' => $data['category'],
                'description' => $data['description'],
                'version' => $data['version'] ?? $policy->version,
                'effective_date' => $data['effective_date'],
                'review_date' => $data['review_date'] ?? null,
                'expiry_date' => $data['expiry_date'] ?? null,
                'mandatory' => $data['mandatory'] ?? $policy->mandatory,
                'acknowledges_required' => $data['acknowledges_required'] ?? $policy->acknowledges_required,
                'status' => $data['status'] ?? $policy->status,
                'updated_at' => date('Y-m-d H:i:s')
            ];

            // Create new version if status changed to active
            if ($data['status'] === 'active' && $policy->status !== 'active') {
                $this->createVersion($policyId, $data['version'] ?? $policy->version, $updatedBy);
            }

            $result = $this->policyModel->update($policyId, $updateData);

            if (!$result) {
                return $this->errorResponse('Failed to update policy.', 'UPDATE_FAILED');
            }

            // Update library entry
            $this->updateLibraryEntry($policyId, $data);

            // Log activity
            $this->activityLogModel->logChange($updatedBy, 'policy', 'policy', $policyId, (array)$policy, $updateData);

            $this->logger->info('Policy updated', [
                'policy_id' => $policyId,
                'updated_by' => $updatedBy
            ]);

            return $this->successResponse('Policy updated successfully.');

        } catch (\Exception $e) {
            $this->logger->error('Update policy error: ' . $e->getMessage());
            return $this->errorResponse('An error occurred updating policy.', 'ERROR');
        }
    }

    /**
     * Delete policy
     * 
     * @param int $policyId
     * @param int $deletedBy
     * @return array
     */
    public function delete(int $policyId, int $deletedBy): array
    {
        try {
            $policy = $this->policyModel->find($policyId);
            if (!$policy) {
                return $this->errorResponse('Policy not found.', 'POLICY_NOT_FOUND');
            }

            // Only allow deletion of draft policies
            if ($policy->status !== 'draft') {
                return $this->errorResponse('Only draft policies can be deleted.', 'DELETE_NOT_ALLOWED');
            }

            $result = $this->policyModel->softDelete($policyId);

            if (!$result) {
                return $this->errorResponse('Failed to delete policy.', 'DELETE_FAILED');
            }

            // Log activity
            $this->activityLogModel->logDelete($deletedBy, 'policy', 'policy', $policyId, (array)$policy);

            $this->logger->info('Policy deleted', [
                'policy_id' => $policyId,
                'deleted_by' => $deletedBy
            ]);

            return $this->successResponse('Policy deleted successfully.');

        } catch (\Exception $e) {
            $this->logger->error('Delete policy error: ' . $e->getMessage());
            return $this->errorResponse('An error occurred deleting policy.', 'ERROR');
        }
    }

    /**
     * Approve policy
     * 
     * @param int $policyId
     * @param int $approvedBy
     * @return array
     */
    public function approve(int $policyId, int $approvedBy): array
    {
        try {
            $policy = $this->policyModel->find($policyId);
            if (!$policy) {
                return $this->errorResponse('Policy not found.', 'POLICY_NOT_FOUND');
            }

            if ($policy->status !== 'review') {
                return $this->errorResponse('Policy must be under review to approve.', 'INVALID_STATUS');
            }

            $result = $this->policyModel->update($policyId, [
                'status' => 'approved',
                'approved_by' => $approvedBy,
                'approval_date' => date('Y-m-d'),
                'updated_at' => date('Y-m-d H:i:s')
            ]);

            if (!$result) {
                return $this->errorResponse('Failed to approve policy.', 'APPROVE_FAILED');
            }

            // Log activity
            $this->activityLogModel->logAction($approvedBy, 'policy_approve', 'policy',
                "Policy {$policy->policy_number} approved");

            $this->logger->info('Policy approved', [
                'policy_id' => $policyId,
                'approved_by' => $approvedBy
            ]);

            return $this->successResponse('Policy approved successfully.');

        } catch (\Exception $e) {
            $this->logger->error('Approve policy error: ' . $e->getMessage());
            return $this->errorResponse('An error occurred approving policy.', 'ERROR');
        }
    }

    /**
     * Get policy history
     * 
     * @param int $policyId
     * @return array
     */
    public function history(int $policyId): array
    {
        try {
            $policy = $this->policyModel->find($policyId);
            if (!$policy) {
                return $this->errorResponse('Policy not found.', 'POLICY_NOT_FOUND');
            }

            $versions = $this->versionModel->getByPolicyId($policyId);
            $activities = $this->activityLogModel->getByTarget('policy', $policyId, 20);

            return $this->successResponse('Policy history retrieved.', [
                'policy' => $policy,
                'versions' => $versions,
                'activities' => $activities
            ]);

        } catch (\Exception $e) {
            $this->logger->error('Get policy history error: ' . $e->getMessage());
            return $this->errorResponse('An error occurred.', 'ERROR');
        }
    }

    /**
     * Create policy version
     * 
     * @param int $policyId
     * @param string $version
     * @param int $createdBy
     * @return bool
     */
    private function createVersion(int $policyId, string $version, int $createdBy): bool
    {
        $data = [
            'policy_id' => $policyId,
            'version' => $version,
            'created_by' => $createdBy,
            'created_at' => date('Y-m-d H:i:s')
        ];

        return $this->versionModel->create($data);
    }

    /**
     * Create library entry
     * 
     * @param int $policyId
     * @param array $data
     * @return bool
     */
    private function createLibraryEntry(int $policyId, array $data): bool
    {
        $libraryData = [
            'policy_id' => $policyId,
            'keywords' => $data['keywords'] ?? $data['title'],
            'summary' => $data['summary'] ?? substr($data['description'], 0, 200),
            'created_at' => date('Y-m-d H:i:s')
        ];

        return $this->libraryModel->create($libraryData);
    }

    /**
     * Update library entry
     * 
     * @param int $policyId
     * @param array $data
     * @return bool
     */
    private function updateLibraryEntry(int $policyId, array $data): bool
    {
        $library = $this->libraryModel->findByPolicyId($policyId);
        if (!$library) {
            return $this->createLibraryEntry($policyId, $data);
        }

        $updateData = [
            'keywords' => $data['keywords'] ?? $data['title'],
            'summary' => $data['summary'] ?? substr($data['description'], 0, 200),
            'updated_at' => date('Y-m-d H:i:s')
        ];

        return $this->libraryModel->update($library->id, $updateData);
    }

    /**
     * Generate policy number
     * 
     * @return string
     */
    private function generatePolicyNumber(): string
    {
        $year = date('Y');
        $random = str_pad(random_int(1, 9999), 4, '0', STR_PAD_LEFT);
        return 'POL-' . $year . '-' . $random;
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