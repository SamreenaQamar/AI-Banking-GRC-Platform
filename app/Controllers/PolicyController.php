<?php
/**
 * AI Banking GRC Platform - Policy Management Controller
 * 
 * @package    AI-Banking-GRC-Platform
 * @version    1.0.0
 * @author     GRC Platform Team
 * @copyright  2026 AI Banking GRC Platform
 * @license    Proprietary
 * 
 * This controller handles:
 * - Policy CRUD operations
 * - Policy versioning
 * - Policy approval workflow
 * - Policy acknowledgements
 * - Policy compliance tracking
 */

declare(strict_types=1);

namespace App\Controllers;

use App\Models\Policy;
use App\Models\PolicyAcknowledgement;
use App\Helpers\Auth;
use App\Services\PolicyService;
use App\Services\NotificationService;
use Exception;

class PolicyController extends BaseController
{
    /**
     * @var Policy
     */
    private Policy $policyModel;
    
    /**
     * @var PolicyAcknowledgement
     */
    private PolicyAcknowledgement $acknowledgementModel;
    
    /**
     * @var PolicyService
     */
    private PolicyService $policyService;
    
    /**
     * @var NotificationService
     */
    private NotificationService $notificationService;
    
    /**
     * Constructor
     */
    public function __construct()
    {
        parent::__construct();
        $this->controllerName = 'Policy';
        $this->policyModel = new Policy();
        $this->acknowledgementModel = new PolicyAcknowledgement();
        $this->policyService = new PolicyService();
        $this->notificationService = new NotificationService();
        
        $this->requireAuth();
        $this->requirePermission(PERM_POLICY_VIEW);
    }
    
    /**
     * Policy dashboard
     * 
     * @return void
     */
    public function index(): void
    {
        try {
            $stats = $this->getDashboardStats();
            $activePolicies = $this->policyModel->getActive(5);
            $pendingApprovals = $this->policyModel->getPendingApproval();
            
            $this->render('index', [
                'title' => 'Policy Management - ' . APP_NAME,
                'stats' => $stats,
                'active_policies' => $activePolicies,
                'pending_approvals' => $pendingApprovals,
                'policy_statuses' => POLICY_STATUSES
            ]);
            
        } catch (Exception $e) {
            $this->setFlashMessage('error', 'Failed to load policy dashboard: ' . $e->getMessage());
            $this->redirectToRoute('dashboard');
        }
    }
    
    /**
     * Show create policy form
     * 
     * @return void
     */
    public function create(): void
    {
        $this->requirePermission(PERM_POLICY_CREATE);
        
        $this->render('create', [
            'title' => 'Create Policy - ' . APP_NAME,
            'policy_statuses' => POLICY_STATUSES,
            'policy_categories' => $this->getPolicyCategories()
        ]);
    }
    
    /**
     * Store a new policy
     * 
     * @return void
     */
    public function store(): void
    {
        try {
            $this->requirePermission(PERM_POLICY_CREATE);
            
            // Validate CSRF token
            $this->validateCSRF($_POST['csrf_token'] ?? '');
            
            // Validate input
            $validationRules = [
                'title' => 'required|min:3|max:255',
                'category' => 'required|in:' . implode(',', array_keys($this->getPolicyCategories())),
                'description' => 'required|min:10',
                'effective_date' => 'required|date',
                'review_date' => 'date|after:effective_date',
                'expiry_date' => 'date|after:review_date',
                'mandatory' => 'boolean',
                'acknowledges_required' => 'boolean',
                'status' => 'required|in:' . implode(',', array_keys(POLICY_STATUSES))
            ];
            
            $validated = $this->validate($_POST, $validationRules);
            
            // Generate policy number
            $policyNumber = $this->generatePolicyNumber();
            
            // Handle file upload
            $documentPath = null;
            if (isset($_FILES['document']) && $_FILES['document']['error'] === UPLOAD_ERR_OK) {
                $documentPath = $this->uploadPolicyDocument($_FILES['document']);
            }
            
            // Create policy
            $policyData = [
                'policy_number' => $policyNumber,
                'title' => $validated['title'],
                'category' => $validated['category'],
                'description' => $validated['description'],
                'version' => '1.0',
                'effective_date' => $validated['effective_date'],
                'review_date' => $validated['review_date'] ?? null,
                'expiry_date' => $validated['expiry_date'] ?? null,
                'document_path' => $documentPath,
                'document_type' => $documentPath ? pathinfo($documentPath, PATHINFO_EXTENSION) : null,
                'mandatory' => $validated['mandatory'] ?? true,
                'acknowledges_required' => $validated['acknowledges_required'] ?? true,
                'status' => $validated['status'],
                'created_by' => Auth::id(),
                'created_at' => date('Y-m-d H:i:s')
            ];
            
            $policyId = $this->policyModel->create($policyData);
            
            if (!$policyId) {
                throw new Exception('Failed to create policy.');
            }
            
            // Log activity
            $this->logActivity('policy_create', 'Created policy: ' . $policyNumber);
            
            $this->setFlashMessage('success', 'Policy created successfully.');
            $this->redirectToRoute('policies.index');
            
        } catch (Exception $e) {
            $this->setFlashMessage('error', $e->getMessage());
            $this->redirectToRoute('policies.create');
        }
    }
    
    /**
     * Show policy details
     * 
     * @param array $params Route parameters
     * @return void
     */
    public function show(array $params): void
    {
        try {
            $policyId = (int)($params['id'] ?? 0);
            $policy = $this->policyModel->find($policyId);
            
            if (!$policy) {
                throw new Exception('Policy not found.');
            }
            
            // Get policy versions
            $versions = $this->policyModel->getVersions($policyId);
            
            // Get acknowledgements
            $acknowledgements = $this->acknowledgementModel->getByPolicyId($policyId);
            
            $this->render('show', [
                'title' => 'Policy Details - ' . APP_NAME,
                'policy' => $policy,
                'versions' => $versions,
                'acknowledgements' => $acknowledgements,
                'policy_statuses' => POLICY_STATUSES,
                'can_acknowledge' => $this->canAcknowledge($policyId)
            ]);
            
        } catch (Exception $e) {
            $this->setFlashMessage('error', $e->getMessage());
            $this->redirectToRoute('policies.index');
        }
    }
    
    /**
     * Show edit policy form
     * 
     * @param array $params Route parameters
     * @return void
     */
    public function edit(array $params): void
    {
        try {
            $this->requirePermission(PERM_POLICY_UPDATE);
            
            $policyId = (int)($params['id'] ?? 0);
            $policy = $this->policyModel->find($policyId);
            
            if (!$policy) {
                throw new Exception('Policy not found.');
            }
            
            $this->render('edit', [
                'title' => 'Edit Policy - ' . APP_NAME,
                'policy' => $policy,
                'policy_statuses' => POLICY_STATUSES,
                'policy_categories' => $this->getPolicyCategories()
            ]);
            
        } catch (Exception $e) {
            $this->setFlashMessage('error', $e->getMessage());
            $this->redirectToRoute('policies.index');
        }
    }
    
    /**
     * Update policy
     * 
     * @param array $params Route parameters
     * @return void
     */
    public function update(array $params): void
    {
        try {
            $this->requirePermission(PERM_POLICY_UPDATE);
            
            $policyId = (int)($params['id'] ?? 0);
            $policy = $this->policyModel->find($policyId);
            
            if (!$policy) {
                throw new Exception('Policy not found.');
            }
            
            // Validate CSRF token
            $this->validateCSRF($_POST['csrf_token'] ?? '');
            
            // Validate input
            $validationRules = [
                'title' => 'required|min:3|max:255',
                'category' => 'required|in:' . implode(',', array_keys($this->getPolicyCategories())),
                'description' => 'required|min:10',
                'effective_date' => 'required|date',
                'review_date' => 'date|after:effective_date',
                'expiry_date' => 'date|after:review_date',
                'mandatory' => 'boolean',
                'acknowledges_required' => 'boolean',
                'status' => 'required|in:' . implode(',', array_keys(POLICY_STATUSES))
            ];
            
            $validated = $this->validate($_POST, $validationRules);
            
            // Handle file upload
            $documentPath = $policy->document_path;
            if (isset($_FILES['document']) && $_FILES['document']['error'] === UPLOAD_ERR_OK) {
                // Delete old document if exists
                if ($documentPath && file_exists(UPLOADS_PATH . '/' . $documentPath)) {
                    unlink(UPLOADS_PATH . '/' . $documentPath);
                }
                $documentPath = $this->uploadPolicyDocument($_FILES['document']);
            }
            
            // Update policy
            $policyData = [
                'title' => $validated['title'],
                'category' => $validated['category'],
                'description' => $validated['description'],
                'effective_date' => $validated['effective_date'],
                'review_date' => $validated['review_date'] ?? null,
                'expiry_date' => $validated['expiry_date'] ?? null,
                'document_path' => $documentPath,
                'document_type' => $documentPath ? pathinfo($documentPath, PATHINFO_EXTENSION) : null,
                'mandatory' => $validated['mandatory'] ?? true,
                'acknowledges_required' => $validated['acknowledges_required'] ?? true,
                'status' => $validated['status'],
                'updated_at' => date('Y-m-d H:i:s')
            ];
            
            $result = $this->policyModel->update($policyId, $policyData);
            
            if (!$result) {
                throw new Exception('Failed to update policy.');
            }
            
            // Log activity
            $this->logActivity('policy_update', 'Updated policy: ' . $policy->policy_number);
            
            $this->setFlashMessage('success', 'Policy updated successfully.');
            $this->redirectToRoute('policies.index');
            
        } catch (Exception $e) {
            $this->setFlashMessage('error', $e->getMessage());
            $this->redirectToRoute('policies.edit', ['id' => $params['id']]);
        }
    }
    
    /**
     * Delete policy
     * 
     * @param array $params Route parameters
     * @return void
     */
    public function delete(array $params): void
    {
        try {
            $this->requirePermission(PERM_POLICY_DELETE);
            
            $policyId = (int)($params['id'] ?? 0);
            $policy = $this->policyModel->find($policyId);
            
            if (!$policy) {
                throw new Exception('Policy not found.');
            }
            
            // Validate CSRF token
            $this->validateCSRF($_POST['csrf_token'] ?? '');
            
            $result = $this->policyModel->softDelete($policyId);
            
            if (!$result) {
                throw new Exception('Failed to delete policy.');
            }
            
            // Log activity
            $this->logActivity('policy_delete', 'Deleted policy: ' . $policy->policy_number);
            
            $this->jsonSuccess('Policy deleted successfully.');
            
        } catch (Exception $e) {
            $this->jsonError($e->getMessage());
        }
    }
    
    /**
     * Approve policy
     * 
     * @param array $params Route parameters
     * @return void
     */
    public function approve(array $params): void
    {
        try {
            $this->requirePermission(PERM_POLICY_APPROVE);
            
            $policyId = (int)($params['id'] ?? 0);
            $policy = $this->policyModel->find($policyId);
            
            if (!$policy) {
                throw new Exception('Policy not found.');
            }
            
            // Validate CSRF token
            $this->validateCSRF($_POST['csrf_token'] ?? '');
            
            $result = $this->policyModel->approve($policyId, Auth::id());
            
            if (!$result) {
                throw new Exception('Failed to approve policy.');
            }
            
            // Log activity
            $this->logActivity('policy_approve', 'Approved policy: ' . $policy->policy_number);
            
            $this->jsonSuccess('Policy approved successfully.');
            
        } catch (Exception $e) {
            $this->jsonError($e->getMessage());
        }
    }
    
    /**
     * Acknowledge policy
     * 
     * @param array $params Route parameters
     * @return void
     */
    public function acknowledge(array $params): void
    {
        try {
            $policyId = (int)($params['id'] ?? 0);
            $policy = $this->policyModel->find($policyId);
            
            if (!$policy) {
                throw new Exception('Policy not found.');
            }
            
            // Check if already acknowledged
            if ($this->acknowledgementModel->hasAcknowledged($policyId, Auth::id())) {
                throw new Exception('You have already acknowledged this policy.');
            }
            
            // Create acknowledgement
            $ackData = [
                'policy_id' => $policyId,
                'user_id' => Auth::id(),
                'acknowledged_at' => date('Y-m-d H:i:s'),
                'ip_address' => $_SERVER['REMOTE_ADDR'] ?? null,
                'user_agent' => $_SERVER['HTTP_USER_AGENT'] ?? null
            ];
            
            $result = $this->acknowledgementModel->create($ackData);
            
            if (!$result) {
                throw new Exception('Failed to acknowledge policy.');
            }
            
            // Log activity
            $this->logActivity('policy_acknowledge', 
                'Acknowledged policy: ' . $policy->policy_number
            );
            
            $this->jsonSuccess('Policy acknowledged successfully.');
            
        } catch (Exception $e) {
            $this->jsonError($e->getMessage());
        }
    }
    
    /**
     * Get policy versions
     * 
     * @param array $params Route parameters
     * @return void
     */
    public function versions(array $params): void
    {
        try {
            $policyId = (int)($params['id'] ?? 0);
            $policy = $this->policyModel->find($policyId);
            
            if (!$policy) {
                throw new Exception('Policy not found.');
            }
            
            $versions = $this->policyModel->getVersions($policyId);
            
            $this->render('versions', [
                'title' => 'Policy Versions - ' . APP_NAME,
                'policy' => $policy,
                'versions' => $versions
            ]);
            
        } catch (Exception $e) {
            $this->setFlashMessage('error', $e->getMessage());
            $this->redirectToRoute('policies.index');
        }
    }
    
    /**
     * Check if user can acknowledge policy
     * 
     * @param int $policyId
     * @return bool
     */
    private function canAcknowledge(int $policyId): bool
    {
        if (!Auth::check()) {
            return false;
        }
        
        return !$this->acknowledgementModel->hasAcknowledged($policyId, Auth::id());
    }
    
    /**
     * Upload policy document
     * 
     * @param array $file
     * @return string
     * @throws Exception
     */
    private function uploadPolicyDocument(array $file): string
    {
        // Validate file type
        $extension = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
        if (!in_array($extension, EXTENSIONS_DOCUMENTS)) {
            throw new Exception('Invalid document type. Allowed: ' . implode(', ', EXTENSIONS_DOCUMENTS));
        }
        
        // Validate file size
        if ($file['size'] > MAX_FILE_SIZE) {
            throw new Exception('File size exceeds limit of ' . MAX_FILE_SIZE_DISPLAY);
        }
        
        // Generate unique filename
        $filename = 'policy_' . date('Ymd_His') . '_' . bin2hex(random_bytes(8)) . '.' . $extension;
        $uploadPath = UPLOADS_PATH . '/policies/';
        
        // Create directory if not exists
        if (!is_dir($uploadPath)) {
            mkdir($uploadPath, 0755, true);
        }
        
        // Move uploaded file
        $targetPath = $uploadPath . $filename;
        if (!move_uploaded_file($file['tmp_name'], $targetPath)) {
            throw new Exception('Failed to upload file.');
        }
        
        return 'policies/' . $filename;
    }
    
    /**
     * Get dashboard statistics
     * 
     * @return array
     */
    private function getDashboardStats(): array
    {
        return [
            'total_policies' => $this->policyModel->countAll(),
            'active_policies' => $this->policyModel->countByStatus(POLICY_STATUS_ACTIVE),
            'pending_approval' => $this->policyModel->countByStatus(POLICY_STATUS_REVIEW),
            'draft_policies' => $this->policyModel->countByStatus(POLICY_STATUS_DRAFT),
            'expired_policies' => $this->policyModel->countExpired(),
            'policies_by_category' => $this->policyModel->countByCategory(),
            'acknowledgement_rate' => $this->policyModel->getAcknowledgementRate(),
            'total_acknowledgements' => $this->acknowledgementModel->countAll()
        ];
    }
    
    /**
     * Get policy categories
     * 
     * @return array
     */
    private function getPolicyCategories(): array
    {
        return [
            'governance' => 'Corporate Governance',
            'risk_management' => 'Risk Management',
            'compliance' => 'Compliance',
            'information_security' => 'Information Security',
            'data_privacy' => 'Data Privacy',
            'human_resources' => 'Human Resources',
            'finance' => 'Finance & Accounting',
            'operations' => 'Operations',
            'it' => 'Information Technology',
            'business_continuity' => 'Business Continuity',
            'anti_money_laundering' => 'Anti-Money Laundering',
            'fraud_prevention' => 'Fraud Prevention'
        ];
    }
    
    /**
     * Generate policy number
     * 
     * @return string
     */
    private function generatePolicyNumber(): string
    {
        return 'POL-' . date('Y') . '-' . str_pad(random_int(1, 9999), 4, '0', STR_PAD_LEFT);
    }
    
    /**
     * Log activity
     * 
     * @param string $action
     * @param string $description
     * @return void
     */
    private function logActivity(string $action, string $description): void
    {
        // This will be implemented in ActivityLogService
    }
}