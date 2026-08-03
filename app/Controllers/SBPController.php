<?php
/**
 * AI Banking GRC Platform - SBP Circulars Controller
 * 
 * @package    AI-Banking-GRC-Platform
 * @version    1.0.0
 * @author     GRC Platform Team
 * @copyright  2026 AI Banking GRC Platform
 * @license    Proprietary
 * 
 * This controller handles:
 * - SBP circular management
 * - Circular compliance tracking
 * - Implementation monitoring
 * - Circular categorization and searching
 */

declare(strict_types=1);

namespace App\Controllers;

use App\Models\SbpCircular;
use App\Helpers\Auth;
use App\Services\SBPService;
use App\Services\NotificationService;
use Exception;

class SBPController extends BaseController
{
    /**
     * @var SbpCircular
     */
    private SbpCircular $circularModel;
    
    /**
     * @var SBPService
     */
    private SBPService $sbpService;
    
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
        $this->controllerName = 'SBP';
        $this->circularModel = new SbpCircular();
        $this->sbpService = new SBPService();
        $this->notificationService = new NotificationService();
        
        $this->requireAuth();
        $this->requirePermission(PERM_SBP_VIEW);
    }
    
    /**
     * SBP dashboard
     * 
     * @return void
     */
    public function index(): void
    {
        try {
            $stats = $this->getDashboardStats();
            $activeCirculars = $this->circularModel->getActive(5);
            $pendingImplementation = $this->circularModel->getPending();
            $recentCirculars = $this->circularModel->getRecent(5);
            
            $this->render('index', [
                'title' => 'SBP Circulars - ' . APP_NAME,
                'stats' => $stats,
                'active_circulars' => $activeCirculars,
                'pending_implementation' => $pendingImplementation,
                'recent_circulars' => $recentCirculars,
                'sbp_categories' => SBP_CATEGORIES,
                'sbp_statuses' => SBP_STATUSES
            ]);
            
        } catch (Exception $e) {
            $this->setFlashMessage('error', 'Failed to load SBP dashboard: ' . $e->getMessage());
            $this->redirectToRoute('dashboard');
        }
    }
    
    /**
     * List SBP circulars
     * 
     * @return void
     */
    public function list(): void
    {
        try {
            $filters = [
                'status' => $this->input('status'),
                'category' => $this->input('category'),
                'priority' => $this->input('priority'),
                'search' => $this->input('search'),
                'date_from' => $this->input('date_from'),
                'date_to' => $this->input('date_to')
            ];
            
            $page = (int)$this->input('page', 1);
            $perPage = (int)$this->input('per_page', PAGINATION_DEFAULT);
            
            $circulars = $this->circularModel->getFiltered($filters, $page, $perPage);
            $total = $this->circularModel->countFiltered($filters);
            
            $this->render('list', [
                'title' => 'SBP Circulars List - ' . APP_NAME,
                'circulars' => $circulars,
                'total' => $total,
                'page' => $page,
                'per_page' => $perPage,
                'total_pages' => ceil($total / $perPage),
                'filters' => $filters,
                'sbp_categories' => SBP_CATEGORIES,
                'sbp_statuses' => SBP_STATUSES
            ]);
            
        } catch (Exception $e) {
            $this->setFlashMessage('error', 'Failed to load SBP circulars: ' . $e->getMessage());
            $this->redirectToRoute('sbp.index');
        }
    }
    
    /**
     * Show create circular form
     * 
     * @return void
     */
    public function create(): void
    {
        $this->requirePermission(PERM_SBP_CREATE);
        
        $this->render('create', [
            'title' => 'Create SBP Circular - ' . APP_NAME,
            'sbp_categories' => SBP_CATEGORIES,
            'sbp_statuses' => SBP_STATUSES
        ]);
    }
    
    /**
     * Store a new circular
     * 
     * @return void
     */
    public function store(): void
    {
        try {
            $this->requirePermission(PERM_SBP_CREATE);
            
            // Validate CSRF token
            $this->validateCSRF($_POST['csrf_token'] ?? '');
            
            // Validate input
            $validationRules = [
                'circular_number' => 'required|unique:sbp_circulars,circular_number',
                'title' => 'required|min:3|max:255',
                'description' => 'required|min:10',
                'category' => 'required|in:' . implode(',', array_keys(SBP_CATEGORIES)),
                'priority' => 'required|in:critical,high,medium,low',
                'issuance_date' => 'required|date',
                'effective_date' => 'required|date|after:issuance_date',
                'compliance_deadline' => 'required|date|after:effective_date',
                'status' => 'required|in:' . implode(',', array_keys(SBP_STATUSES))
            ];
            
            $validated = $this->validate($_POST, $validationRules);
            
            // Handle document upload
            $documentPath = null;
            if (isset($_FILES['document']) && $_FILES['document']['error'] === UPLOAD_ERR_OK) {
                $documentPath = $this->uploadCircularDocument($_FILES['document']);
            }
            
            // Create circular
            $circularData = [
                'circular_number' => $validated['circular_number'],
                'title' => $validated['title'],
                'description' => $validated['description'],
                'category' => $validated['category'],
                'priority' => $validated['priority'],
                'issuance_date' => $validated['issuance_date'],
                'effective_date' => $validated['effective_date'],
                'compliance_deadline' => $validated['compliance_deadline'],
                'document_path' => $documentPath,
                'document_type' => $documentPath ? pathinfo($documentPath, PATHINFO_EXTENSION) : null,
                'status' => $validated['status'],
                'created_by' => Auth::id(),
                'created_at' => date('Y-m-d H:i:s')
            ];
            
            $circularId = $this->circularModel->create($circularData);
            
            if (!$circularId) {
                throw new Exception('Failed to create SBP circular.');
            }
            
            // Log activity
            $this->logActivity('sbp_create', 'Created SBP circular: ' . $validated['circular_number']);
            
            $this->setFlashMessage('success', 'SBP circular created successfully.');
            $this->redirectToRoute('sbp.list');
            
        } catch (Exception $e) {
            $this->setFlashMessage('error', $e->getMessage());
            $this->redirectToRoute('sbp.create');
        }
    }
    
    /**
     * Show circular details
     * 
     * @param array $params Route parameters
     * @return void
     */
    public function show(array $params): void
    {
        try {
            $circularId = (int)($params['id'] ?? 0);
            $circular = $this->circularModel->find($circularId);
            
            if (!$circular) {
                throw new Exception('SBP circular not found.');
            }
            
            // Get related circulars
            $related = $this->circularModel->getRelated($circularId);
            
            $this->render('show', [
                'title' => 'SBP Circular Details - ' . APP_NAME,
                'circular' => $circular,
                'related' => $related,
                'sbp_categories' => SBP_CATEGORIES,
                'sbp_statuses' => SBP_STATUSES
            ]);
            
        } catch (Exception $e) {
            $this->setFlashMessage('error', $e->getMessage());
            $this->redirectToRoute('sbp.list');
        }
    }
    
    /**
     * Show edit circular form
     * 
     * @param array $params Route parameters
     * @return void
     */
    public function edit(array $params): void
    {
        try {
            $this->requirePermission(PERM_SBP_UPDATE);
            
            $circularId = (int)($params['id'] ?? 0);
            $circular = $this->circularModel->find($circularId);
            
            if (!$circular) {
                throw new Exception('SBP circular not found.');
            }
            
            $this->render('edit', [
                'title' => 'Edit SBP Circular - ' . APP_NAME,
                'circular' => $circular,
                'sbp_categories' => SBP_CATEGORIES,
                'sbp_statuses' => SBP_STATUSES
            ]);
            
        } catch (Exception $e) {
            $this->setFlashMessage('error', $e->getMessage());
            $this->redirectToRoute('sbp.list');
        }
    }
    
    /**
     * Update circular
     * 
     * @param array $params Route parameters
     * @return void
     */
    public function update(array $params): void
    {
        try {
            $this->requirePermission(PERM_SBP_UPDATE);
            
            $circularId = (int)($params['id'] ?? 0);
            $circular = $this->circularModel->find($circularId);
            
            if (!$circular) {
                throw new Exception('SBP circular not found.');
            }
            
            // Validate CSRF token
            $this->validateCSRF($_POST['csrf_token'] ?? '');
            
            // Validate input
            $validationRules = [
                'title' => 'required|min:3|max:255',
                'description' => 'required|min:10',
                'category' => 'required|in:' . implode(',', array_keys(SBP_CATEGORIES)),
                'priority' => 'required|in:critical,high,medium,low',
                'issuance_date' => 'required|date',
                'effective_date' => 'required|date|after:issuance_date',
                'compliance_deadline' => 'required|date|after:effective_date',
                'status' => 'required|in:' . implode(',', array_keys(SBP_STATUSES))
            ];
            
            $validated = $this->validate($_POST, $validationRules);
            
            // Handle document upload
            $documentPath = $circular->document_path;
            if (isset($_FILES['document']) && $_FILES['document']['error'] === UPLOAD_ERR_OK) {
                if ($documentPath && file_exists(UPLOADS_PATH . '/' . $documentPath)) {
                    unlink(UPLOADS_PATH . '/' . $documentPath);
                }
                $documentPath = $this->uploadCircularDocument($_FILES['document']);
            }
            
            // Update circular
            $circularData = [
                'title' => $validated['title'],
                'description' => $validated['description'],
                'category' => $validated['category'],
                'priority' => $validated['priority'],
                'issuance_date' => $validated['issuance_date'],
                'effective_date' => $validated['effective_date'],
                'compliance_deadline' => $validated['compliance_deadline'],
                'document_path' => $documentPath,
                'document_type' => $documentPath ? pathinfo($documentPath, PATHINFO_EXTENSION) : null,
                'status' => $validated['status'],
                'updated_at' => date('Y-m-d H:i:s')
            ];
            
            $result = $this->circularModel->update($circularId, $circularData);
            
            if (!$result) {
                throw new Exception('Failed to update SBP circular.');
            }
            
            // Log activity
            $this->logActivity('sbp_update', 'Updated SBP circular: ' . $circular->circular_number);
            
            $this->setFlashMessage('success', 'SBP circular updated successfully.');
            $this->redirectToRoute('sbp.list');
            
        } catch (Exception $e) {
            $this->setFlashMessage('error', $e->getMessage());
            $this->redirectToRoute('sbp.edit', ['id' => $params['id']]);
        }
    }
    
    /**
     * Delete circular
     * 
     * @param array $params Route parameters
     * @return void
     */
    public function delete(array $params): void
    {
        try {
            $this->requirePermission(PERM_SBP_UPDATE);
            
            $circularId = (int)($params['id'] ?? 0);
            $circular = $this->circularModel->find($circularId);
            
            if (!$circular) {
                throw new Exception('SBP circular not found.');
            }
            
            // Validate CSRF token
            $this->validateCSRF($_POST['csrf_token'] ?? '');
            
            $result = $this->circularModel->softDelete($circularId);
            
            if (!$result) {
                throw new Exception('Failed to delete SBP circular.');
            }
            
            // Log activity
            $this->logActivity('sbp_delete', 'Deleted SBP circular: ' . $circular->circular_number);
            
            $this->jsonSuccess('SBP circular deleted successfully.');
            
        } catch (Exception $e) {
            $this->jsonError($e->getMessage());
        }
    }
    
    /**
     * Implement circular
     * 
     * @param array $params Route parameters
     * @return void
     */
    public function implement(array $params): void
    {
        try {
            $this->requirePermission(PERM_SBP_IMPLEMENT);
            
            $circularId = (int)($params['id'] ?? 0);
            $circular = $this->circularModel->find($circularId);
            
            if (!$circular) {
                throw new Exception('SBP circular not found.');
            }
            
            // Validate CSRF token
            $this->validateCSRF($_POST['csrf_token'] ?? '');
            
            $implementationNotes = $this->input('implementation_notes');
            
            $result = $this->circularModel->implement(
                $circularId,
                Auth::id(),
                $implementationNotes
            );
            
            if (!$result) {
                throw new Exception('Failed to mark circular as implemented.');
            }
            
            // Log activity
            $this->logActivity('sbp_implement', 
                'Implemented SBP circular: ' . $circular->circular_number
            );
            
            $this->jsonSuccess('SBP circular marked as implemented.');
            
        } catch (Exception $e) {
            $this->jsonError($e->getMessage());
        }
    }
    
    /**
     * Upload circular document
     * 
     * @param array $file
     * @return string
     * @throws Exception
     */
    private function uploadCircularDocument(array $file): string
    {
        $extension = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
        if (!in_array($extension, array_merge(EXTENSIONS_DOCUMENTS, EXTENSIONS_IMAGES))) {
            throw new Exception('Invalid document type. Allowed: ' . 
                implode(', ', array_merge(EXTENSIONS_DOCUMENTS, EXTENSIONS_IMAGES))
            );
        }
        
        if ($file['size'] > MAX_FILE_SIZE) {
            throw new Exception('File size exceeds limit of ' . MAX_FILE_SIZE_DISPLAY);
        }
        
        $filename = 'sbp_' . date('Ymd_His') . '_' . bin2hex(random_bytes(8)) . '.' . $extension;
        $uploadPath = UPLOADS_PATH . '/sbp/';
        
        if (!is_dir($uploadPath)) {
            mkdir($uploadPath, 0755, true);
        }
        
        $targetPath = $uploadPath . $filename;
        if (!move_uploaded_file($file['tmp_name'], $targetPath)) {
            throw new Exception('Failed to upload file.');
        }
        
        return 'sbp/' . $filename;
    }
    
    /**
     * Get dashboard statistics
     * 
     * @return array
     */
    private function getDashboardStats(): array
    {
        return [
            'total_circulars' => $this->circularModel->countAll(),
            'active_circulars' => $this->circularModel->countByStatus(SBP_STATUS_ACTIVE),
            'pending_implementation' => $this->circularModel->countByStatus(SBP_STATUS_PENDING),
            'implemented_circulars' => $this->circularModel->countByStatus(SBP_STATUS_IMPLEMENTED),
            'circulars_by_category' => $this->circularModel->countByCategory(),
            'circulars_by_priority' => $this->circularModel->countByPriority(),
            'compliance_rate' => $this->circularModel->getComplianceRate(),
            'upcoming_deadlines' => $this->circularModel->countUpcomingDeadlines(30)
        ];
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