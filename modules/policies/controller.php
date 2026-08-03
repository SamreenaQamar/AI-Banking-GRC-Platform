<?php
/**
 * Policies Module - Controller
 * 
 * @package    AI-Banking-GRC-Platform
 * @subpackage modules/policies
 * @version    1.0.0
 * @author     GRC Platform Team
 * @copyright  2026 AI Banking GRC Platform
 * @license    Proprietary
 * 
 * This controller handles:
 * - Policy management
 * - Version control
 * - Approval workflow
 * - Policy library
 * - AI policy generation
 */

declare(strict_types=1);

namespace Modules\Policies\Controllers;

use App\Controllers\BaseController;
use App\Helpers\Auth;
use App\Helpers\CSRF;
use App\Helpers\Validation;
use Modules\Policies\Services\PolicyService;
use App\Models\Policy;
use Exception;

class PolicyController extends BaseController
{
    /**
     * @var PolicyService
     */
    private PolicyService $policyService;
    
    /**
     * @var Policy
     */
    private Policy $policyModel;
    
    /**
     * Constructor
     */
    public function __construct()
    {
        parent::__construct();
        $this->controllerName = 'Policies';
        $this->policyService = new PolicyService();
        $this->policyModel = new Policy();
        
        $this->requireAuth();
        $this->requirePermission('policy_view');
    }
    
    /**
     * Policy dashboard
     * 
     * @return void
     */
    public function index(): void
    {
        try {
            $userId = Auth::id();
            $dashboardData = $this->policyService->getDashboardData($userId);
            
            $this->render('policies/dashboard', [
                'title' => 'Policy Dashboard - ' . APP_NAME,
                'data' => $dashboardData,
                'categories' => POLICY_CATEGORIES,
                'statuses' => POLICY_STATUS
            ]);
            
        } catch (Exception $e) {
            $this->setFlashMessage('error', 'Failed to load policy dashboard: ' . $e->getMessage());
            $this->redirectToRoute('dashboard');
        }
    }
    
    /**
     * Policy library
     * 
     * @return void
     */
    public function library(): void
    {
        try {
            $filters = [
                'category' => $this->input('category'),
                'status' => $this->input('status'),
                'search' => $this->input('search'),
                'department_id' => $this->input('department_id')
            ];
            
            $page = (int)$this->input('page', 1);
            $perPage = (int)$this->input('per_page', 15);
            
            $policies = $this->policyModel->getFiltered($filters, $page, $perPage);
            $total = $this->policyModel->countFiltered($filters);
            
            $this->render('policies/library', [
                'title' => 'Policy Library - ' . APP_NAME,
                'policies' => $policies,
                'total' => $total,
                'page' => $page,
                'per_page' => $perPage,
                'total_pages' => ceil($total / $perPage),
                'filters' => $filters,
                'categories' => POLICY_CATEGORIES,
                'statuses' => POLICY_STATUS
            ]);
            
        } catch (Exception $e) {
            $this->setFlashMessage('error', 'Failed to load policy library: ' . $e->getMessage());
            $this->redirectToRoute('policies.index');
        }
    }
    
    /**
     * Create policy
     * 
     * @return void
     */
    public function create(): void
    {
        try {
            $this->requirePermission('policy_create');
            
            $this->render('policies/create', [
                'title' => 'Create Policy - ' . APP_NAME,
                'categories' => POLICY_CATEGORIES,
                'statuses' => POLICY_STATUS
            ]);
            
        } catch (Exception $e) {
            $this->setFlashMessage('error', $e->getMessage());
            $this->redirectToRoute('policies.index');
        }
    }
    
    /**
     * Store policy
     * 
     * @return void
     */
    public function store(): void
    {
        try {
            $this->requirePermission('policy_create');
            CSRF::validate($_POST['csrf_token'] ?? '');
            
            $validationRules = [
                'title' => 'required|min:3|max:255',
                'category' => 'required|in:' . implode(',', array_keys(POLICY_CATEGORIES)),
                'description' => 'required|min:10',
                'status' => 'required|in:' . implode(',', array_keys(POLICY_STATUS))
            ];
            
            $validated = $this->validate($_POST, $validationRules);
            
            // Generate policy number
            $policyNumber = $this->generatePolicyNumber();
            
            // Handle file upload
            $documentPath = null;
            if (isset($_FILES['document']) && $_FILES['document']['error'] === UPLOAD_ERR_OK) {
                $documentPath = $this->uploadDocument($_FILES['document']);
            }
            
            $policyData = [
                'policy_number' => $policyNumber,
                'title' => $validated['title'],
                'category' => $validated['category'],
                'description' => $validated['description'],
                'version' => '1.0',
                'status' => $validated['status'],
                'effective_date' => $this->input('effective_date') ?: null,
                'review_date' => $this->input('review_date') ?: null,
                'expiry_date' => $this->input('expiry_date') ?: null,
                'mandatory' => $this->input('mandatory') ? true : false,
                'acknowledges_required' => $this->input('acknowledges_required') ? true : false,
                'document_path' => $documentPath,
                'document_type' => $documentPath ? pathinfo($documentPath, PATHINFO_EXTENSION) : null,
                'created_by' => Auth::id(),
                'created_at' => date('Y-m-d H:i:s')
            ];
            
            $policyId = $this->policyModel->create($policyData);
            
            if (!$policyId) {
                throw new Exception('Failed to create policy.');
            }
            
            $this->setFlashMessage('success', 'Policy created successfully.');
            $this->redirectToRoute('policies.library');
            
        } catch (Exception $e) {
            $this->setFlashMessage('error', $e->getMessage());
            $this->redirectToRoute('policies.create');
        }
    }
    
    /**
     * View policy
     * 
     * @param array $params
     * @return void
     */
    public function view(array $params): void
    {
        try {
            $policyId = (int)($params['id'] ?? 0);
            $policy = $this->policyModel->find($policyId);
            
            if (!$policy) {
                throw new Exception('Policy not found.');
            }
            
            $versions = $this->policyModel->getVersions($policyId);
            $acknowledgements = $this->policyModel->getAcknowledgements($policyId);
            
            $this->render('policies/view', [
                'title' => 'Policy Details - ' . APP_NAME,
                'policy' => $policy,
                'versions' => $versions,
                'acknowledgements' => $acknowledgements,
                'categories' => POLICY_CATEGORIES,
                'statuses' => POLICY_STATUS
            ]);
            
        } catch (Exception $e) {
            $this->setFlashMessage('error', $e->getMessage());
            $this->redirectToRoute('policies.library');
        }
    }
    
    /**
     * Edit policy
     * 
     * @param array $params
     * @return void
     */
    public function edit(array $params): void
    {
        try {
            $this->requirePermission('policy_update');
            
            $policyId = (int)($params['id'] ?? 0);
            $policy = $this->policyModel->find($policyId);
            
            if (!$policy) {
                throw new Exception('Policy not found.');
            }
            
            if (!in_array($policy->status, ['draft', 'review'])) {
                throw new Exception('Only draft or review policies can be edited.');
            }
            
            $this->render('policies/edit', [
                'title' => 'Edit Policy - ' . APP_NAME,
                'policy' => $policy,
                'categories' => POLICY_CATEGORIES,
                'statuses' => POLICY_STATUS
            ]);
            
        } catch (Exception $e) {
            $this->setFlashMessage('error', $e->getMessage());
            $this->redirectToRoute('policies.library');
        }
    }
    
    /**
     * Update policy
     * 
     * @param array $params
     * @return void
     */
    public function update(array $params): void
    {
        try {
            $this->requirePermission('policy_update');
            CSRF::validate($_POST['csrf_token'] ?? '');
            
            $policyId = (int)($params['id'] ?? 0);
            $policy = $this->policyModel->find($policyId);
            
            if (!$policy) {
                throw new Exception('Policy not found.');
            }
            
            if (!in_array($policy->status, ['draft', 'review'])) {
                throw new Exception('Only draft or review policies can be edited.');
            }
            
            $validationRules = [
                'title' => 'required|min:3|max:255',
                'category' => 'required|in:' . implode(',', array_keys(POLICY_CATEGORIES)),
                'description' => 'required|min:10',
                'status' => 'required|in:' . implode(',', array_keys(POLICY_STATUS))
            ];
            
            $validated = $this->validate($_POST, $validationRules);
            
            $policyData = [
                'title' => $validated['title'],
                'category' => $validated['category'],
                'description' => $validated['description'],
                'status' => $validated['status'],
                'effective_date' => $this->input('effective_date') ?: null,
                'review_date' => $this->input('review_date') ?: null,
                'expiry_date' => $this->input('expiry_date') ?: null,
                'mandatory' => $this->input('mandatory') ? true : false,
                'acknowledges_required' => $this->input('acknowledges_required') ? true : false,
                'updated_at' => date('Y-m-d H:i:s')
            ];
            
            // Handle file upload
            if (isset($_FILES['document']) && $_FILES['document']['error'] === UPLOAD_ERR_OK) {
                if ($policy->document_path && file_exists(UPLOADS_PATH . '/' . $policy->document_path)) {
                    unlink(UPLOADS_PATH . '/' . $policy->document_path);
                }
                $policyData['document_path'] = $this->uploadDocument($_FILES['document']);
                $policyData['document_type'] = pathinfo($policyData['document_path'], PATHINFO_EXTENSION);
            }
            
            $result = $this->policyModel->update($policyId, $policyData);
            
            if (!$result) {
                throw new Exception('Failed to update policy.');
            }
            
            $this->setFlashMessage('success', 'Policy updated successfully.');
            $this->redirectToRoute('policies.library');
            
        } catch (Exception $e) {
            $this->setFlashMessage('error', $e->getMessage());
            $this->redirectToRoute('policies.edit', ['id' => $params['id']]);
        }
    }
    
    /**
     * Delete policy
     * 
     * @param array $params
     * @return void
     */
    public function delete(array $params): void
    {
        try {
            $this->requirePermission('policy_delete');
            CSRF::validate($_POST['csrf_token'] ?? '');
            
            $policyId = (int)($params['id'] ?? 0);
            $policy = $this->policyModel->find($policyId);
            
            if (!$policy) {
                throw new Exception('Policy not found.');
            }
            
            if ($policy->status === 'active') {
                throw new Exception('Active policies cannot be deleted. Archive them first.');
            }
            
            $result = $this->policyModel->softDelete($policyId);
            
            if (!$result) {
                throw new Exception('Failed to delete policy.');
            }
            
            $this->jsonSuccess('Policy deleted successfully.');
            
        } catch (Exception $e) {
            $this->jsonError($e->getMessage());
        }
    }
    
    /**
     * Approve policy
     * 
     * @param array $params
     * @return void
     */
    public function approve(array $params): void
    {
        try {
            $this->requirePermission('policy_approve');
            CSRF::validate($_POST['csrf_token'] ?? '');
            
            $policyId = (int)($params['id'] ?? 0);
            $result = $this->policyService->approvePolicy($policyId, Auth::id());
            
            if (!$result) {
                throw new Exception('Failed to approve policy.');
            }
            
            $this->jsonSuccess('Policy approved successfully.');
            
        } catch (Exception $e) {
            $this->jsonError($e->getMessage());
        }
    }
    
    /**
     * Publish policy
     * 
     * @param array $params
     * @return void
     */
    public function publish(array $params): void
    {
        try {
            $this->requirePermission('policy_publish');
            CSRF::validate($_POST['csrf_token'] ?? '');
            
            $policyId = (int)($params['id'] ?? 0);
            $result = $this->policyService->publishPolicy($policyId, Auth::id());
            
            if (!$result) {
                throw new Exception('Failed to publish policy.');
            }
            
            $this->jsonSuccess('Policy published successfully.');
            
        } catch (Exception $e) {
            $this->jsonError($e->getMessage());
        }
    }
    
    /**
     * Acknowledge policy
     * 
     * @param array $params
     * @return void
     */
    public function acknowledge(array $params): void
    {
        try {
            CSRF::validate($_POST['csrf_token'] ?? '');
            
            $policyId = (int)($params['id'] ?? 0);
            $result = $this->policyService->acknowledgePolicy($policyId, Auth::id());
            
            if (!$result) {
                throw new Exception('Failed to acknowledge policy.');
            }
            
            $this->jsonSuccess('Policy acknowledged successfully.');
            
        } catch (Exception $e) {
            $this->jsonError($e->getMessage());
        }
    }
    
    /**
     * Generate AI policy
     * 
     * @return void
     */
    public function generate(): void
    {
        try {
            $this->requirePermission('policy_create');
            
            $this->render('policies/generator', [
                'title' => 'AI Policy Generator - ' . APP_NAME,
                'categories' => POLICY_CATEGORIES,
                'frameworks' => [
                    'iso27001' => 'ISO 27001:2022',
                    'nist' => 'NIST CSF',
                    'sbp' => 'SBP Regulations',
                    'basel' => 'Basel III',
                    'custom' => 'Custom Framework'
                ]
            ]);
            
        } catch (Exception $e) {
            $this->setFlashMessage('error', $e->getMessage());
            $this->redirectToRoute('policies.index');
        }
    }
    
    /**
     * Generate AI policy (AJAX)
     * 
     * @return void
     */
    public function generateAI(): void
    {
        try {
            $this->requirePermission('policy_create');
            CSRF::validate($_POST['csrf_token'] ?? '');
            
            $data = [
                'policy_name' => $this->input('policy_name'),
                'policy_type' => $this->input('policy_type'),
                'framework' => $this->input('framework'),
                'category' => $this->input('category'),
                'requirements' => $this->input('requirements'),
                'tone' => $this->input('tone')
            ];
            
            $result = $this->policyService->generateAIPolicy($data);
            
            $this->jsonSuccess('Policy generated successfully.', $result);
            
        } catch (Exception $e) {
            $this->jsonError($e->getMessage());
        }
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
     * Upload document
     * 
     * @param array $file
     * @return string
     * @throws Exception
     */
    private function uploadDocument(array $file): string
    {
        $maxSize = policy_setting('max_file_size') * 1024 * 1024;
        $allowedTypes = policy_setting('allowed_file_types', ['pdf', 'doc', 'docx']);
        
        if ($file['size'] > $maxSize) {
            throw new Exception('File size exceeds maximum allowed.');
        }
        
        $extension = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
        if (!in_array($extension, $allowedTypes)) {
            throw new Exception('File type not allowed.');
        }
        
        $filename = 'policy_' . date('Ymd_His') . '_' . uniqid() . '.' . $extension;
        $uploadPath = UPLOADS_PATH . '/policies/';
        
        if (!is_dir($uploadPath)) {
            mkdir($uploadPath, 0755, true);
        }
        
        $targetPath = $uploadPath . $filename;
        
        if (!move_uploaded_file($file['tmp_name'], $targetPath)) {
            throw new Exception('Failed to upload file.');
        }
        
        return 'policies/' . $filename;
    }
}