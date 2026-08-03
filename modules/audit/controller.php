<?php
/**
 * Audit Module - Controller
 * 
 * @package    AI-Banking-GRC-Platform
 * @subpackage modules/audit
 * @version    1.0.0
 * @author     GRC Platform Team
 * @copyright  2026 AI Banking GRC Platform
 * @license    Proprietary
 * 
 * This controller handles:
 * - Audit dashboard
 * - Audit planning and scheduling
 * - Audit findings management
 * - Evidence management
 * - Audit reports
 */

declare(strict_types=1);

namespace Modules\Audit\Controllers;

use App\Controllers\BaseController;
use App\Helpers\Auth;
use App\Helpers\CSRF;
use App\Helpers\Validation;
use Modules\Audit\Services\AuditService;
use App\Models\AuditPlan;
use App\Models\AuditFinding;
use Exception;

class AuditController extends BaseController
{
    /**
     * @var AuditService
     */
    private AuditService $auditService;
    
    /**
     * @var AuditPlan
     */
    private AuditPlan $planModel;
    
    /**
     * @var AuditFinding
     */
    private AuditFinding $findingModel;
    
    /**
     * Constructor
     */
    public function __construct()
    {
        parent::__construct();
        $this->controllerName = 'Audit';
        $this->auditService = new AuditService();
        $this->planModel = new AuditPlan();
        $this->findingModel = new AuditFinding();
        
        $this->requireAuth();
        $this->requirePermission('audit_view');
    }
    
    /**
     * Audit dashboard
     * 
     * @return void
     */
    public function index(): void
    {
        try {
            $userId = Auth::id();
            $dashboardData = $this->auditService->getDashboardData($userId);
            
            $this->render('audit/dashboard', [
                'title' => 'Audit Dashboard - ' . APP_NAME,
                'data' => $dashboardData,
                'audit_types' => AUDIT_TYPES,
                'audit_status' => AUDIT_STATUS,
                'finding_severity' => AUDIT_FINDING_SEVERITY,
                'finding_status' => AUDIT_FINDING_STATUS
            ]);
            
        } catch (Exception $e) {
            $this->setFlashMessage('error', 'Failed to load audit dashboard: ' . $e->getMessage());
            $this->redirectToRoute('dashboard');
        }
    }
    
    /**
     * Schedule audit
     * 
     * @return void
     */
    public function schedule(): void
    {
        try {
            $this->requirePermission('audit_create');
            
            $departments = $this->getDepartments();
            $users = $this->getUsers();
            
            $this->render('audit/schedule', [
                'title' => 'Schedule Audit - ' . APP_NAME,
                'departments' => $departments,
                'users' => $users,
                'audit_types' => AUDIT_TYPES,
                'audit_status' => AUDIT_STATUS,
                'audit_priority' => AUDIT_PRIORITY,
                'audit_frequency' => AUDIT_FREQUENCY
            ]);
            
        } catch (Exception $e) {
            $this->setFlashMessage('error', $e->getMessage());
            $this->redirectToRoute('audit.index');
        }
    }
    
    /**
     * Create audit
     * 
     * @return void
     */
    public function create(): void
    {
        try {
            $this->requirePermission('audit_create');
            CSRF::validate($_POST['csrf_token'] ?? '');
            
            $validationRules = [
                'title' => 'required|min:3|max:255',
                'scope_description' => 'required|min:10',
                'audit_type' => 'required|in:' . implode(',', array_keys(AUDIT_TYPES)),
                'audit_frequency' => 'required|in:' . implode(',', array_keys(AUDIT_FREQUENCY)),
                'department_id' => 'required|exists:departments,id',
                'lead_auditor_id' => 'required|exists:users,id',
                'start_date' => 'required|date|after:today',
                'end_date' => 'required|date|after:start_date'
            ];
            
            $validated = $this->validate($_POST, $validationRules);
            
            // Generate reference number
            $referenceNumber = $this->generateAuditReference();
            
            $planData = [
                'title' => $validated['title'],
                'reference_number' => $referenceNumber,
                'audit_type' => $validated['audit_type'],
                'audit_frequency' => $validated['audit_frequency'],
                'scope_description' => $validated['scope_description'],
                'department_id' => $validated['department_id'],
                'lead_auditor_id' => $validated['lead_auditor_id'],
                'start_date' => $validated['start_date'],
                'end_date' => $validated['end_date'],
                'status' => 'planned',
                'created_by' => Auth::id(),
                'created_at' => date('Y-m-d H:i:s')
            ];
            
            $planId = $this->planModel->create($planData);
            
            if (!$planId) {
                throw new Exception('Failed to create audit plan.');
            }
            
            $this->setFlashMessage('success', 'Audit scheduled successfully.');
            $this->redirectToRoute('audit.index');
            
        } catch (Exception $e) {
            $this->setFlashMessage('error', $e->getMessage());
            $this->redirectToRoute('audit.schedule');
        }
    }
    
    /**
     * Audit details
     * 
     * @param array $params
     * @return void
     */
    public function details(array $params): void
    {
        try {
            $auditId = (int)($params['id'] ?? 0);
            $audit = $this->planModel->find($auditId);
            
            if (!$audit) {
                throw new Exception('Audit not found.');
            }
            
            $findings = $this->findingModel->getByAuditId($auditId);
            $evidence = $this->planModel->getEvidence($auditId);
            $recommendations = $this->auditService->getAIRecommendations($auditId);
            
            $this->render('audit/details', [
                'title' => 'Audit Details - ' . APP_NAME,
                'audit' => $audit,
                'findings' => $findings,
                'evidence' => $evidence,
                'recommendations' => $recommendations,
                'audit_status' => AUDIT_STATUS,
                'finding_severity' => AUDIT_FINDING_SEVERITY,
                'finding_status' => AUDIT_FINDING_STATUS
            ]);
            
        } catch (Exception $e) {
            $this->setFlashMessage('error', $e->getMessage());
            $this->redirectToRoute('audit.index');
        }
    }
    
    /**
     * Audit findings
     * 
     * @return void
     */
    public function findings(): void
    {
        try {
            $filters = [
                'severity' => $this->input('severity'),
                'status' => $this->input('status'),
                'audit_plan_id' => $this->input('audit_id'),
                'assigned_to' => $this->input('assigned_to')
            ];
            
            $page = (int)$this->input('page', 1);
            $perPage = (int)$this->input('per_page', 15);
            
            $findings = $this->findingModel->getFiltered($filters, $page, $perPage);
            $total = $this->findingModel->countFiltered($filters);
            
            $this->render('audit/findings', [
                'title' => 'Audit Findings - ' . APP_NAME,
                'findings' => $findings,
                'total' => $total,
                'page' => $page,
                'per_page' => $perPage,
                'total_pages' => ceil($total / $perPage),
                'filters' => $filters,
                'audit_types' => AUDIT_TYPES,
                'finding_severity' => AUDIT_FINDING_SEVERITY,
                'finding_status' => AUDIT_FINDING_STATUS
            ]);
            
        } catch (Exception $e) {
            $this->setFlashMessage('error', 'Failed to load findings: ' . $e->getMessage());
            $this->redirectToRoute('audit.index');
        }
    }
    
    /**
     * Add finding
     * 
     * @param array $params
     * @return void
     */
    public function addFinding(array $params): void
    {
        try {
            $this->requirePermission('audit_execute');
            CSRF::validate($_POST['csrf_token'] ?? '');
            
            $auditId = (int)($params['id'] ?? 0);
            $audit = $this->planModel->find($auditId);
            
            if (!$audit) {
                throw new Exception('Audit not found.');
            }
            
            $validationRules = [
                'title' => 'required|min:3|max:255',
                'description' => 'required|min:10',
                'severity' => 'required|in:' . implode(',', array_keys(AUDIT_FINDING_SEVERITY)),
                'recommendation' => 'required|min:10'
            ];
            
            $validated = $this->validate($_POST, $validationRules);
            
            $findingId = $this->auditService->createFinding($auditId, $validated, Auth::id());
            
            if (!$findingId) {
                throw new Exception('Failed to create finding.');
            }
            
            $this->setFlashMessage('success', 'Finding added successfully.');
            $this->redirectToRoute('audit.details', ['id' => $auditId]);
            
        } catch (Exception $e) {
            $this->setFlashMessage('error', $e->getMessage());
            $this->redirectToRoute('audit.details', ['id' => $params['id']]);
        }
    }
    
    /**
     * Update finding status
     * 
     * @param array $params
     * @return void
     */
    public function updateFindingStatus(array $params): void
    {
        try {
            $this->requirePermission('audit_execute');
            CSRF::validate($_POST['csrf_token'] ?? '');
            
            $findingId = (int)($params['id'] ?? 0);
            $status = $this->input('status');
            $notes = $this->input('notes');
            
            $result = $this->auditService->updateFindingStatus($findingId, $status, Auth::id(), $notes);
            
            if (!$result) {
                throw new Exception('Failed to update finding status.');
            }
            
            $this->jsonSuccess('Finding status updated successfully.');
            
        } catch (Exception $e) {
            $this->jsonError($e->getMessage());
        }
    }
    
    /**
     * Upload evidence
     * 
     * @param array $params
     * @return void
     */
    public function uploadEvidence(array $params): void
    {
        try {
            $this->requirePermission('audit_execute');
            CSRF::validate($_POST['csrf_token'] ?? '');
            
            $auditId = (int)($params['id'] ?? 0);
            $userId = Auth::id();
            $description = $this->input('description');
            $type = $this->input('type', 'document');
            
            if (!isset($_FILES['evidence'])) {
                throw new Exception('No file uploaded.');
            }
            
            $result = $this->auditService->uploadEvidence(
                $auditId,
                $_FILES['evidence'],
                $description,
                $userId,
                $type
            );
            
            if (!$result) {
                throw new Exception('Failed to upload evidence.');
            }
            
            $this->jsonSuccess('Evidence uploaded successfully.', ['id' => $result]);
            
        } catch (Exception $e) {
            $this->jsonError($e->getMessage());
        }
    }
    
    /**
     * Audit reports
     * 
     * @return void
     */
    public function reports(): void
    {
        try {
            $stats = $this->auditService->getAuditStats();
            $recentAudits = $this->planModel->getRecent(10);
            
            $this->render('audit/reports', [
                'title' => 'Audit Reports - ' . APP_NAME,
                'stats' => $stats,
                'recent_audits' => $recentAudits,
                'audit_status' => AUDIT_STATUS
            ]);
            
        } catch (Exception $e) {
            $this->setFlashMessage('error', 'Failed to load reports: ' . $e->getMessage());
            $this->redirectToRoute('audit.index');
        }
    }
    
    /**
     * Audit history
     * 
     * @return void
     */
    public function history(): void
    {
        try {
            $auditId = (int)$this->input('audit_id', 0);
            $history = $this->planModel->getHistory($auditId);
            
            $this->render('audit/history', [
                'title' => 'Audit History - ' . APP_NAME,
                'history' => $history,
                'audit_id' => $auditId
            ]);
            
        } catch (Exception $e) {
            $this->setFlashMessage('error', 'Failed to load history: ' . $e->getMessage());
            $this->redirectToRoute('audit.index');
        }
    }
    
    /**
     * Generate audit reference
     * 
     * @return string
     */
    private function generateAuditReference(): string
    {
        $year = date('Y');
        $random = str_pad(random_int(1, 9999), 4, '0', STR_PAD_LEFT);
        return 'AUDIT-' . $year . '-' . $random;
    }
    
    /**
     * Get departments
     * 
     * @return array
     */
    private function getDepartments(): array
    {
        // This would get departments from DepartmentModel
        return [];
    }
    
    /**
     * Get users
     * 
     * @return array
     */
    private function getUsers(): array
    {
        // This would get users from UserModel
        return [];
    }
}