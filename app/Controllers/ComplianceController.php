<?php
/**
 * AI Banking GRC Platform - Compliance Management Controller
 * 
 * @package    AI-Banking-GRC-Platform
 * @version    1.0.0
 * @author     GRC Platform Team
 * @copyright  2026 AI Banking GRC Platform
 * @license    Proprietary
 * 
 * This controller handles:
 * - Compliance task CRUD operations
 * - Compliance frameworks management
 * - Compliance categories management
 * - Task assignment and tracking
 * - Evidence management
 * - Compliance dashboard and reports
 */

declare(strict_types=1);

namespace App\Controllers;

use App\Models\ComplianceTask;
use App\Models\ComplianceFramework;
use App\Models\ComplianceCategory;
use App\Models\ComplianceEvidence;
use App\Helpers\Auth;
use App\Services\ComplianceService;
use App\Services\NotificationService;
use Exception;

class ComplianceController extends BaseController
{
    /**
     * @var ComplianceTask
     */
    private ComplianceTask $taskModel;
    
    /**
     * @var ComplianceFramework
     */
    private ComplianceFramework $frameworkModel;
    
    /**
     * @var ComplianceCategory
     */
    private ComplianceCategory $categoryModel;
    
    /**
     * @var ComplianceEvidence
     */
    private ComplianceEvidence $evidenceModel;
    
    /**
     * @var ComplianceService
     */
    private ComplianceService $complianceService;
    
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
        $this->controllerName = 'Compliance';
        $this->taskModel = new ComplianceTask();
        $this->frameworkModel = new ComplianceFramework();
        $this->categoryModel = new ComplianceCategory();
        $this->evidenceModel = new ComplianceEvidence();
        $this->complianceService = new ComplianceService();
        $this->notificationService = new NotificationService();
        
        $this->requireAuth();
        $this->requirePermission(PERM_COMPLIANCE_VIEW);
    }
    
    /**
     * Compliance dashboard
     * 
     * @return void
     */
    public function index(): void
    {
        try {
            $stats = $this->getDashboardStats();
            $recentTasks = $this->taskModel->getRecent(10);
            $overdueTasks = $this->taskModel->getOverdue();
            $upcomingDeadlines = $this->taskModel->getUpcomingDeadlines(7);
            
            $this->render('index', [
                'title' => 'Compliance Dashboard - ' . APP_NAME,
                'stats' => $stats,
                'recent_tasks' => $recentTasks,
                'overdue_tasks' => $overdueTasks,
                'upcoming_deadlines' => $upcomingDeadlines,
                'frameworks' => $this->frameworkModel->getAll(),
                'categories' => $this->categoryModel->getAll()
            ]);
            
        } catch (Exception $e) {
            $this->setFlashMessage('error', 'Failed to load compliance dashboard: ' . $e->getMessage());
            $this->redirectToRoute('dashboard');
        }
    }
    
    /**
     * List all compliance tasks
     * 
     * @return void
     */
    public function tasks(): void
    {
        try {
            $filters = [
                'status' => $this->input('status'),
                'priority' => $this->input('priority'),
                'category_id' => $this->input('category_id'),
                'framework_id' => $this->input('framework_id'),
                'department_id' => $this->input('department_id'),
                'assigned_to' => $this->input('assigned_to'),
                'search' => $this->input('search'),
                'date_from' => $this->input('date_from'),
                'date_to' => $this->input('date_to')
            ];
            
            $page = (int)$this->input('page', 1);
            $perPage = (int)$this->input('per_page', PAGINATION_DEFAULT);
            $sortBy = $this->input('sort_by', 'created_at');
            $sortOrder = $this->input('sort_order', 'DESC');
            
            $tasks = $this->taskModel->getFiltered($filters, $page, $perPage, $sortBy, $sortOrder);
            $total = $this->taskModel->countFiltered($filters);
            
            $this->render('tasks', [
                'title' => 'Compliance Tasks - ' . APP_NAME,
                'tasks' => $tasks,
                'total' => $total,
                'page' => $page,
                'per_page' => $perPage,
                'total_pages' => ceil($total / $perPage),
                'filters' => $filters,
                'sort_by' => $sortBy,
                'sort_order' => $sortOrder,
                'statuses' => COMPLIANCE_STATUSES,
                'priorities' => COMPLIANCE_PRIORITIES,
                'categories' => $this->categoryModel->getAll(),
                'frameworks' => $this->frameworkModel->getAll(),
                'departments' => $this->getDepartments(),
                'users' => $this->getUsers()
            ]);
            
        } catch (Exception $e) {
            $this->setFlashMessage('error', 'Failed to load tasks: ' . $e->getMessage());
            $this->redirectToRoute('compliance.index');
        }
    }
    
    /**
     * Show create compliance task form
     * 
     * @return void
     */
    public function create(): void
    {
        $this->requirePermission(PERM_COMPLIANCE_CREATE);
        
        $categories = $this->categoryModel->getAll();
        $frameworks = $this->frameworkModel->getAll();
        $departments = $this->getDepartments();
        $users = $this->getUsers();
        
        $this->render('create', [
            'title' => 'Create Compliance Task - ' . APP_NAME,
            'categories' => $categories,
            'frameworks' => $frameworks,
            'departments' => $departments,
            'users' => $users,
            'statuses' => COMPLIANCE_STATUSES,
            'priorities' => COMPLIANCE_PRIORITIES
        ]);
    }
    
    /**
     * Store a new compliance task
     * 
     * @return void
     */
    public function store(): void
    {
        try {
            $this->requirePermission(PERM_COMPLIANCE_CREATE);
            
            // Validate CSRF token
            $this->validateCSRF($_POST['csrf_token'] ?? '');
            
            // Validate input
            $validationRules = [
                'title' => 'required|min:3|max:255',
                'description' => 'required|min:10',
                'category_id' => 'required|exists:compliance_categories,id',
                'framework_id' => 'required|exists:compliance_frameworks,id',
                'department_id' => 'required|exists:departments,id',
                'priority' => 'required|in:' . implode(',', array_keys(COMPLIANCE_PRIORITIES)),
                'due_date' => 'required|date|after:today',
                'assigned_to' => 'exists:users,id',
                'evidence_required' => 'boolean',
                'auto_review' => 'boolean'
            ];
            
            $validated = $this->validate($_POST, $validationRules);
            
            // Generate reference number
            $referenceNumber = $this->generateReferenceNumber();
            
            // Create task
            $taskData = [
                'title' => $validated['title'],
                'description' => $validated['description'],
                'reference_number' => $referenceNumber,
                'category_id' => $validated['category_id'],
                'framework_id' => $validated['framework_id'],
                'department_id' => $validated['department_id'],
                'priority' => $validated['priority'],
                'status' => COMPLIANCE_STATUS_PENDING,
                'due_date' => $validated['due_date'],
                'assigned_to' => $validated['assigned_to'] ?? null,
                'assigned_by' => Auth::id(),
                'evidence_required' => $validated['evidence_required'] ?? true,
                'auto_review' => $validated['auto_review'] ?? false,
                'created_by' => Auth::id(),
                'created_at' => date('Y-m-d H:i:s')
            ];
            
            $taskId = $this->taskModel->create($taskData);
            
            if (!$taskId) {
                throw new Exception('Failed to create compliance task.');
            }
            
            // Send notification to assigned user
            if ($validated['assigned_to']) {
                $this->notificationService->sendTaskAssignment(
                    $validated['assigned_to'],
                    $taskId,
                    $referenceNumber
                );
            }
            
            // Log activity
            $this->logActivity('compliance_create', 'Created compliance task: ' . $referenceNumber);
            
            $this->setFlashMessage('success', 'Compliance task created successfully.');
            $this->redirectToRoute('compliance.tasks');
            
        } catch (Exception $e) {
            $this->setFlashMessage('error', $e->getMessage());
            $this->redirectToRoute('compliance.create');
        }
    }
    
    /**
     * Show compliance task details
     * 
     * @param array $params Route parameters
     * @return void
     */
    public function show(array $params): void
    {
        try {
            $taskId = (int)($params['id'] ?? 0);
            $task = $this->taskModel->find($taskId);
            
            if (!$task) {
                throw new Exception('Compliance task not found.');
            }
            
            // Get task history
            $history = $this->taskModel->getHistory($taskId);
            
            // Get evidence
            $evidence = $this->evidenceModel->getByTaskId($taskId);
            
            $this->render('show', [
                'title' => 'Compliance Task Details - ' . APP_NAME,
                'task' => $task,
                'history' => $history,
                'evidence' => $evidence,
                'statuses' => COMPLIANCE_STATUSES,
                'priorities' => COMPLIANCE_PRIORITIES
            ]);
            
        } catch (Exception $e) {
            $this->setFlashMessage('error', $e->getMessage());
            $this->redirectToRoute('compliance.tasks');
        }
    }
    
    /**
     * Show edit compliance task form
     * 
     * @param array $params Route parameters
     * @return void
     */
    public function edit(array $params): void
    {
        try {
            $this->requirePermission(PERM_COMPLIANCE_UPDATE);
            
            $taskId = (int)($params['id'] ?? 0);
            $task = $this->taskModel->find($taskId);
            
            if (!$task) {
                throw new Exception('Compliance task not found.');
            }
            
            $categories = $this->categoryModel->getAll();
            $frameworks = $this->frameworkModel->getAll();
            $departments = $this->getDepartments();
            $users = $this->getUsers();
            
            $this->render('edit', [
                'title' => 'Edit Compliance Task - ' . APP_NAME,
                'task' => $task,
                'categories' => $categories,
                'frameworks' => $frameworks,
                'departments' => $departments,
                'users' => $users,
                'statuses' => COMPLIANCE_STATUSES,
                'priorities' => COMPLIANCE_PRIORITIES
            ]);
            
        } catch (Exception $e) {
            $this->setFlashMessage('error', $e->getMessage());
            $this->redirectToRoute('compliance.tasks');
        }
    }
    
    /**
     * Update compliance task
     * 
     * @param array $params Route parameters
     * @return void
     */
    public function update(array $params): void
    {
        try {
            $this->requirePermission(PERM_COMPLIANCE_UPDATE);
            
            $taskId = (int)($params['id'] ?? 0);
            $task = $this->taskModel->find($taskId);
            
            if (!$task) {
                throw new Exception('Compliance task not found.');
            }
            
            // Validate CSRF token
            $this->validateCSRF($_POST['csrf_token'] ?? '');
            
            // Validate input
            $validationRules = [
                'title' => 'required|min:3|max:255',
                'description' => 'required|min:10',
                'category_id' => 'required|exists:compliance_categories,id',
                'framework_id' => 'required|exists:compliance_frameworks,id',
                'department_id' => 'required|exists:departments,id',
                'priority' => 'required|in:' . implode(',', array_keys(COMPLIANCE_PRIORITIES)),
                'status' => 'required|in:' . implode(',', array_keys(COMPLIANCE_STATUSES)),
                'due_date' => 'required|date',
                'assigned_to' => 'exists:users,id',
                'evidence_required' => 'boolean',
                'auto_review' => 'boolean'
            ];
            
            $validated = $this->validate($_POST, $validationRules);
            
            // Update task
            $taskData = [
                'title' => $validated['title'],
                'description' => $validated['description'],
                'category_id' => $validated['category_id'],
                'framework_id' => $validated['framework_id'],
                'department_id' => $validated['department_id'],
                'priority' => $validated['priority'],
                'status' => $validated['status'],
                'due_date' => $validated['due_date'],
                'assigned_to' => $validated['assigned_to'] ?? null,
                'evidence_required' => $validated['evidence_required'] ?? true,
                'auto_review' => $validated['auto_review'] ?? false,
                'updated_at' => date('Y-m-d H:i:s')
            ];
            
            // If completed, set completion date
            if ($validated['status'] === COMPLIANCE_STATUS_COMPLETED) {
                $taskData['completed_date'] = date('Y-m-d');
            }
            
            $result = $this->taskModel->update($taskId, $taskData);
            
            if (!$result) {
                throw new Exception('Failed to update compliance task.');
            }
            
            // Log activity
            $this->logActivity('compliance_update', 'Updated compliance task: ' . $task->reference_number);
            
            $this->setFlashMessage('success', 'Compliance task updated successfully.');
            $this->redirectToRoute('compliance.tasks');
            
        } catch (Exception $e) {
            $this->setFlashMessage('error', $e->getMessage());
            $this->redirectToRoute('compliance.edit', ['id' => $params['id']]);
        }
    }
    
    /**
     * Delete compliance task
     * 
     * @param array $params Route parameters
     * @return void
     */
    public function delete(array $params): void
    {
        try {
            $this->requirePermission(PERM_COMPLIANCE_DELETE);
            
            $taskId = (int)($params['id'] ?? 0);
            $task = $this->taskModel->find($taskId);
            
            if (!$task) {
                throw new Exception('Compliance task not found.');
            }
            
            // Validate CSRF token
            $this->validateCSRF($_POST['csrf_token'] ?? '');
            
            $result = $this->taskModel->softDelete($taskId);
            
            if (!$result) {
                throw new Exception('Failed to delete compliance task.');
            }
            
            // Log activity
            $this->logActivity('compliance_delete', 'Deleted compliance task: ' . $task->reference_number);
            
            $this->jsonSuccess('Compliance task deleted successfully.');
            
        } catch (Exception $e) {
            $this->jsonError($e->getMessage());
        }
    }
    
    /**
     * Update task status
     * 
     * @param array $params Route parameters
     * @return void
     */
    public function updateStatus(array $params): void
    {
        try {
            $taskId = (int)($params['id'] ?? 0);
            $task = $this->taskModel->find($taskId);
            
            if (!$task) {
                throw new Exception('Compliance task not found.');
            }
            
            $status = $this->input('status');
            
            if (!in_array($status, array_keys(COMPLIANCE_STATUSES))) {
                throw new Exception('Invalid status.');
            }
            
            $result = $this->taskModel->updateStatus($taskId, $status);
            
            if (!$result) {
                throw new Exception('Failed to update task status.');
            }
            
            // Log history
            $this->taskModel->addHistory($taskId, $status, $task->status, Auth::id());
            
            // Log activity
            $this->logActivity('compliance_update_status', 
                'Updated status of task ' . $task->reference_number . ' to ' . $status
            );
            
            $this->jsonSuccess('Task status updated successfully.', [
                'status' => $status,
                'status_label' => COMPLIANCE_STATUSES[$status]
            ]);
            
        } catch (Exception $e) {
            $this->jsonError($e->getMessage());
        }
    }
    
    /**
     * Upload evidence for compliance task
     * 
     * @param array $params Route parameters
     * @return void
     */
    public function uploadEvidence(array $params): void
    {
        try {
            $taskId = (int)($params['id'] ?? 0);
            $task = $this->taskModel->find($taskId);
            
            if (!$task) {
                throw new Exception('Compliance task not found.');
            }
            
            // Validate file upload
            if (!isset($_FILES['evidence']) || $_FILES['evidence']['error'] !== UPLOAD_ERR_OK) {
                throw new Exception('File upload failed.');
            }
            
            $file = $_FILES['evidence'];
            
            // Validate file type
            $extension = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
            if (!in_array($extension, EXTENSIONS_ALLOWED)) {
                throw new Exception('Invalid file type. Allowed: ' . implode(', ', EXTENSIONS_ALLOWED));
            }
            
            // Validate file size
            if ($file['size'] > MAX_FILE_SIZE) {
                throw new Exception('File size exceeds limit of ' . MAX_FILE_SIZE_DISPLAY);
            }
            
            // Generate unique filename
            $filename = $this->generateUniqueFilename($file['name']);
            $uploadPath = UPLOADS_PATH . '/compliance/' . $taskId . '/';
            
            // Create directory if not exists
            if (!is_dir($uploadPath)) {
                mkdir($uploadPath, 0755, true);
            }
            
            // Move uploaded file
            $targetPath = $uploadPath . $filename;
            if (!move_uploaded_file($file['tmp_name'], $targetPath)) {
                throw new Exception('Failed to upload file.');
            }
            
            // Save evidence record
            $evidenceData = [
                'task_id' => $taskId,
                'file_name' => $file['name'],
                'file_path' => 'compliance/' . $taskId . '/' . $filename,
                'file_type' => $extension,
                'file_size' => $file['size'],
                'description' => $this->input('description'),
                'uploaded_by' => Auth::id(),
                'status' => 'pending',
                'created_at' => date('Y-m-d H:i:s')
            ];
            
            $evidenceId = $this->evidenceModel->create($evidenceData);
            
            if (!$evidenceId) {
                throw new Exception('Failed to save evidence record.');
            }
            
            // Log activity
            $this->logActivity('compliance_upload_evidence', 
                'Uploaded evidence for task ' . $task->reference_number . ': ' . $file['name']
            );
            
            $this->jsonSuccess('Evidence uploaded successfully.', [
                'id' => $evidenceId,
                'filename' => $file['name'],
                'path' => $targetPath
            ]);
            
        } catch (Exception $e) {
            $this->jsonError($e->getMessage());
        }
    }
    
    /**
     * Get compliance frameworks
     * 
     * @return void
     */
    public function frameworks(): void
    {
        try {
            $frameworks = $this->frameworkModel->getAll();
            $this->render('frameworks', [
                'title' => 'Compliance Frameworks - ' . APP_NAME,
                'frameworks' => $frameworks
            ]);
        } catch (Exception $e) {
            $this->setFlashMessage('error', 'Failed to load frameworks: ' . $e->getMessage());
            $this->redirectToRoute('compliance.index');
        }
    }
    
    /**
     * Get compliance categories
     * 
     * @return void
     */
    public function categories(): void
    {
        try {
            $categories = $this->categoryModel->getAll();
            $this->render('categories', [
                'title' => 'Compliance Categories - ' . APP_NAME,
                'categories' => $categories
            ]);
        } catch (Exception $e) {
            $this->setFlashMessage('error', 'Failed to load categories: ' . $e->getMessage());
            $this->redirectToRoute('compliance.index');
        }
    }
    
    /**
     * Export compliance data
     * 
     * @return void
     */
    public function export(): void
    {
        try {
            $this->requirePermission(PERM_REPORT_EXPORT);
            
            $format = $this->input('format', EXPORT_FORMAT_CSV);
            $filters = $this->allInput();
            
            $data = $this->taskModel->getExportData($filters);
            
            // This will be implemented in ExportService
            // $this->exportService->export($data, $format);
            
            $this->jsonSuccess('Export initiated successfully.');
            
        } catch (Exception $e) {
            $this->jsonError('Export failed: ' . $e->getMessage());
        }
    }
    
    /**
     * Get dashboard statistics
     * 
     * @return array
     */
    private function getDashboardStats(): array
    {
        return [
            'total_tasks' => $this->taskModel->countAll(),
            'pending_tasks' => $this->taskModel->countByStatus(COMPLIANCE_STATUS_PENDING),
            'in_progress' => $this->taskModel->countByStatus(COMPLIANCE_STATUS_IN_PROGRESS),
            'completed_tasks' => $this->taskModel->countByStatus(COMPLIANCE_STATUS_COMPLETED),
            'overdue_tasks' => $this->taskModel->countOverdue(),
            'completion_rate' => $this->taskModel->getCompletionRate(),
            'by_priority' => $this->taskModel->countByPriority(),
            'by_category' => $this->taskModel->countByCategory(),
            'by_department' => $this->taskModel->countByDepartment()
        ];
    }
    
    /**
     * Generate unique reference number
     * 
     * @return string
     */
    private function generateReferenceNumber(): string
    {
        return 'COMP-' . date('Y') . '-' . str_pad(random_int(1, 99999), 5, '0', STR_PAD_LEFT);
    }
    
    /**
     * Generate unique filename
     * 
     * @param string $originalName
     * @return string
     */
    private function generateUniqueFilename(string $originalName): string
    {
        $extension = strtolower(pathinfo($originalName, PATHINFO_EXTENSION));
        $basename = pathinfo($originalName, PATHINFO_FILENAME);
        $timestamp = date('Ymd_His');
        $random = bin2hex(random_bytes(8));
        
        return $this->sanitizeFilename($basename) . '_' . $timestamp . '_' . $random . '.' . $extension;
    }
    
    /**
     * Sanitize filename
     * 
     * @param string $filename
     * @return string
     */
    private function sanitizeFilename(string $filename): string
    {
        // Remove special characters
        $filename = preg_replace('/[^a-zA-Z0-9_-]/', '', $filename);
        return substr($filename, 0, 50);
    }
    
    /**
     * Get departments for dropdown
     * 
     * @return array
     */
    private function getDepartments(): array
    {
        // This will be implemented in DepartmentModel
        return [];
    }
    
    /**
     * Get users for dropdown
     * 
     * @return array
     */
    private function getUsers(): array
    {
        // This will be implemented in UserModel
        return [];
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