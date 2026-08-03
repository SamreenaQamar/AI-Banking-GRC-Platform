<?php
/**
 * AI Banking GRC Platform - Audit Management Controller
 * 
 * @package    AI-Banking-GRC-Platform
 * @version    1.0.0
 * @author     GRC Platform Team
 * @copyright  2026 AI Banking GRC Platform
 * @license    Proprietary
 * 
 * This controller handles:
 * - Audit plan CRUD operations
 * - Audit findings management
 * - Audit scheduling and tracking
 * - Audit reporting and dashboards
 */

declare(strict_types=1);

namespace App\Controllers;

use App\Models\AuditPlan;
use App\Models\AuditFinding;
use App\Helpers\Auth;
use App\Services\AuditService;
use App\Services\NotificationService;
use Exception;

class AuditController extends BaseController
{
    /**
     * @var AuditPlan
     */
    private AuditPlan $planModel;
    
    /**
     * @var AuditFinding
     */
    private AuditFinding $findingModel;
    
    /**
     * @var AuditService
     */
    private AuditService $auditService;
    
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
        $this->controllerName = 'Audit';
        $this->planModel = new AuditPlan();
        $this->findingModel = new AuditFinding();
        $this->auditService = new AuditService();
        $this->notificationService = new NotificationService();
        
        $this->requireAuth();
        $this->requireRole([ROLE_INTERNAL_AUDITOR, ROLE_ADMIN, ROLE_SUPER_ADMIN]);
    }
    
    /**
     * Audit dashboard
     * 
     * @return void
     */
    public function index(): void
    {
        try {
            $stats = $this->getDashboardStats();
            $upcomingAudits = $this->planModel->getUpcoming(5);
            $recentFindings = $this->findingModel->getRecent(5);
            
            $this->render('index', [
                'title' => 'Audit Dashboard - ' . APP_NAME,
                'stats' => $stats,
                'upcoming_audits' => $upcomingAudits,
                'recent_findings' => $recentFindings,
                'audit_types' => AUDIT_TYPES,
                'audit_statuses' => AUDIT_STATUSES,
                'finding_severities' => FINDING_SEVERITIES
            ]);
            
        } catch (Exception $e) {
            $this->setFlashMessage('error', 'Failed to load audit dashboard: ' . $e->getMessage());
            $this->redirectToRoute('dashboard');
        }
    }
    
    /**
     * List audit plans
     * 
     * @return void
     */
    public function plans(): void
    {
        try {
            $this->requirePermission(PERM_AUDIT_VIEW);
            
            $filters = [
                'status' => $this->input('status'),
                'audit_type' => $this->input('audit_type'),
                'department_id' => $this->input('department_id'),
                'search' => $this->input('search')
            ];
            
            $page = (int)$this->input('page', 1);
            $perPage = (int)$this->input('per_page', PAGINATION_DEFAULT);
            
            $plans = $this->planModel->getFiltered($filters, $page, $perPage);
            $total = $this->planModel->countFiltered($filters);
            
            $this->render('plans', [
                'title' => 'Audit Plans - ' . APP_NAME,
                'plans' => $plans,
                'total' => $total,
                'page' => $page,
                'per_page' => $perPage,
                'total_pages' => ceil($total / $perPage),
                'filters' => $filters,
                'audit_types' => AUDIT_TYPES,
                'audit_statuses' => AUDIT_STATUSES,
                'departments' => $this->getDepartments()
            ]);
            
        } catch (Exception $e) {
            $this->setFlashMessage('error', 'Failed to load audit plans: ' . $e->getMessage());
            $this->redirectToRoute('audit.index');
        }
    }
    
    /**
     * Show create audit plan form
     * 
     * @return void
     */
    public function createPlan(): void
    {
        $this->requirePermission(PERM_AUDIT_CREATE);
        
        $departments = $this->getDepartments();
        $users = $this->getUsers();
        
        $this->render('create-plan', [
            'title' => 'Create Audit Plan - ' . APP_NAME,
            'departments' => $departments,
            'users' => $users,
            'audit_types' => AUDIT_TYPES,
            'audit_statuses' => AUDIT_STATUSES
        ]);
    }
    
    /**
     * Store audit plan
     * 
     * @return void
     */
    public function storePlan(): void
    {
        try {
            $this->requirePermission(PERM_AUDIT_CREATE);
            
            // Validate CSRF token
            $this->validateCSRF($_POST['csrf_token'] ?? '');
            
            // Validate input
            $validationRules = [
                'title' => 'required|min:3|max:255',
                'scope_description' => 'required|min:10',
                'audit_type' => 'required|in:' . implode(',', array_keys(AUDIT_TYPES)),
                'audit_frequency' => 'required|in:annual,semi_annual,quarterly,monthly,adhoc',
                'department_id' => 'required|exists:departments,id',
                'start_date' => 'required|date|after:today',
                'end_date' => 'required|date|after:start_date',
                'lead_auditor_id' => 'required|exists:users,id',
                'audit_team' => 'string|max:500',
                'estimated_budget' => 'numeric|min:0'
            ];
            
            $validated = $this->validate($_POST, $validationRules);
            
            // Generate reference number
            $referenceNumber = $this->generateAuditReference();
            
            // Create audit plan
            $planData = [
                'title' => $validated['title'],
                'reference_number' => $referenceNumber,
                'audit_type' => $validated['audit_type'],
                'audit_frequency' => $validated['audit_frequency'],
                'scope_description' => $validated['scope_description'],
                'department_id' => $validated['department_id'],
                'start_date' => $validated['start_date'],
                'end_date' => $validated['end_date'],
                'lead_auditor_id' => $validated['lead_auditor_id'],
                'audit_team' => $validated['audit_team'] ?? null,
                'estimated_budget' => $validated['estimated_budget'] ?? null,
                'status' => AUDIT_STATUS_PLANNED,
                'created_by' => Auth::id(),
                'created_at' => date('Y-m-d H:i:s')
            ];
            
            $planId = $this->planModel->create($planData);
            
            if (!$planId) {
                throw new Exception('Failed to create audit plan.');
            }
            
            // Send notification to lead auditor
            $this->notificationService->sendAuditAssignment(
                $validated['lead_auditor_id'],
                $planId,
                $referenceNumber
            );
            
            // Log activity
            $this->logActivity('audit_plan_create', 'Created audit plan: ' . $referenceNumber);
            
            $this->setFlashMessage('success', 'Audit plan created successfully.');
            $this->redirectToRoute('audit.plans');
            
        } catch (Exception $e) {
            $this->setFlashMessage('error', $e->getMessage());
            $this->redirectToRoute('audit.plan.create');
        }
    }
    
    /**
     * Show audit plan details
     * 
     * @param array $params Route parameters
     * @return void
     */
    public function showPlan(array $params): void
    {
        try {
            $planId = (int)($params['id'] ?? 0);
            $plan = $this->planModel->find($planId);
            
            if (!$plan) {
                throw new Exception('Audit plan not found.');
            }
            
            // Get findings for this audit
            $findings = $this->findingModel->getByAuditId($planId);
            
            $this->render('show-plan', [
                'title' => 'Audit Plan Details - ' . APP_NAME,
                'plan' => $plan,
                'findings' => $findings,
                'audit_types' => AUDIT_TYPES,
                'audit_statuses' => AUDIT_STATUSES,
                'finding_severities' => FINDING_SEVERITIES
            ]);
            
        } catch (Exception $e) {
            $this->setFlashMessage('error', $e->getMessage());
            $this->redirectToRoute('audit.plans');
        }
    }
    
    /**
     * Show edit audit plan form
     * 
     * @param array $params Route parameters
     * @return void
     */
    public function editPlan(array $params): void
    {
        try {
            $this->requirePermission(PERM_AUDIT_UPDATE);
            
            $planId = (int)($params['id'] ?? 0);
            $plan = $this->planModel->find($planId);
            
            if (!$plan) {
                throw new Exception('Audit plan not found.');
            }
            
            $departments = $this->getDepartments();
            $users = $this->getUsers();
            
            $this->render('edit-plan', [
                'title' => 'Edit Audit Plan - ' . APP_NAME,
                'plan' => $plan,
                'departments' => $departments,
                'users' => $users,
                'audit_types' => AUDIT_TYPES,
                'audit_statuses' => AUDIT_STATUSES
            ]);
            
        } catch (Exception $e) {
            $this->setFlashMessage('error', $e->getMessage());
            $this->redirectToRoute('audit.plans');
        }
    }
    
    /**
     * Update audit plan
     * 
     * @param array $params Route parameters
     * @return void
     */
    public function updatePlan(array $params): void
    {
        try {
            $this->requirePermission(PERM_AUDIT_UPDATE);
            
            $planId = (int)($params['id'] ?? 0);
            $plan = $this->planModel->find($planId);
            
            if (!$plan) {
                throw new Exception('Audit plan not found.');
            }
            
            // Validate CSRF token
            $this->validateCSRF($_POST['csrf_token'] ?? '');
            
            // Validate input
            $validationRules = [
                'title' => 'required|min:3|max:255',
                'scope_description' => 'required|min:10',
                'audit_type' => 'required|in:' . implode(',', array_keys(AUDIT_TYPES)),
                'audit_frequency' => 'required|in:annual,semi_annual,quarterly,monthly,adhoc',
                'department_id' => 'required|exists:departments,id',
                'start_date' => 'required|date',
                'end_date' => 'required|date|after:start_date',
                'lead_auditor_id' => 'required|exists:users,id',
                'audit_team' => 'string|max:500',
                'estimated_budget' => 'numeric|min:0',
                'actual_cost' => 'numeric|min:0',
                'status' => 'required|in:' . implode(',', array_keys(AUDIT_STATUSES))
            ];
            
            $validated = $this->validate($_POST, $validationRules);
            
            // Update audit plan
            $planData = [
                'title' => $validated['title'],
                'audit_type' => $validated['audit_type'],
                'audit_frequency' => $validated['audit_frequency'],
                'scope_description' => $validated['scope_description'],
                'department_id' => $validated['department_id'],
                'start_date' => $validated['start_date'],
                'end_date' => $validated['end_date'],
                'lead_auditor_id' => $validated['lead_auditor_id'],
                'audit_team' => $validated['audit_team'] ?? null,
                'estimated_budget' => $validated['estimated_budget'] ?? null,
                'actual_cost' => $validated['actual_cost'] ?? null,
                'status' => $validated['status'],
                'updated_at' => date('Y-m-d H:i:s')
            ];
            
            $result = $this->planModel->update($planId, $planData);
            
            if (!$result) {
                throw new Exception('Failed to update audit plan.');
            }
            
            // Log activity
            $this->logActivity('audit_plan_update', 'Updated audit plan: ' . $plan->reference_number);
            
            $this->setFlashMessage('success', 'Audit plan updated successfully.');
            $this->redirectToRoute('audit.plans');
            
        } catch (Exception $e) {
            $this->setFlashMessage('error', $e->getMessage());
            $this->redirectToRoute('audit.plan.edit', ['id' => $params['id']]);
        }
    }
    
    /**
     * Delete audit plan
     * 
     * @param array $params Route parameters
     * @return void
     */
    public function deletePlan(array $params): void
    {
        try {
            $this->requirePermission(PERM_AUDIT_DELETE);
            
            $planId = (int)($params['id'] ?? 0);
            $plan = $this->planModel->find($planId);
            
            if (!$plan) {
                throw new Exception('Audit plan not found.');
            }
            
            // Validate CSRF token
            $this->validateCSRF($_POST['csrf_token'] ?? '');
            
            $result = $this->planModel->softDelete($planId);
            
            if (!$result) {
                throw new Exception('Failed to delete audit plan.');
            }
            
            // Log activity
            $this->logActivity('audit_plan_delete', 'Deleted audit plan: ' . $plan->reference_number);
            
            $this->jsonSuccess('Audit plan deleted successfully.');
            
        } catch (Exception $e) {
            $this->jsonError($e->getMessage());
        }
    }
    
    /**
     * List audit findings
     * 
     * @return void
     */
    public function findings(): void
    {
        try {
            $this->requirePermission(PERM_AUDIT_VIEW);
            
            $filters = [
                'severity' => $this->input('severity'),
                'status' => $this->input('status'),
                'audit_plan_id' => $this->input('audit_plan_id'),
                'assigned_to' => $this->input('assigned_to'),
                'search' => $this->input('search')
            ];
            
            $page = (int)$this->input('page', 1);
            $perPage = (int)$this->input('per_page', PAGINATION_DEFAULT);
            
            $findings = $this->findingModel->getFiltered($filters, $page, $perPage);
            $total = $this->findingModel->countFiltered($filters);
            
            $this->render('findings', [
                'title' => 'Audit Findings - ' . APP_NAME,
                'findings' => $findings,
                'total' => $total,
                'page' => $page,
                'per_page' => $perPage,
                'total_pages' => ceil($total / $perPage),
                'filters' => $filters,
                'severities' => FINDING_SEVERITIES,
                'finding_statuses' => [
                    'open' => 'Open',
                    'in_progress' => 'In Progress',
                    'resolved' => 'Resolved',
                    'closed' => 'Closed',
                    'accepted_risk' => 'Accepted Risk'
                ],
                'audit_plans' => $this->planModel->getAll(),
                'users' => $this->getUsers()
            ]);
            
        } catch (Exception $e) {
            $this->setFlashMessage('error', 'Failed to load audit findings: ' . $e->getMessage());
            $this->redirectToRoute('audit.index');
        }
    }
    
    /**
     * Show create finding form
     * 
     * @return void
     */
    public function createFinding(): void
    {
        $this->requirePermission(PERM_AUDIT_EXECUTE);
        
        $auditPlans = $this->planModel->getActive();
        $users = $this->getUsers();
        
        $this->render('create-finding', [
            'title' => 'Create Audit Finding - ' . APP_NAME,
            'audit_plans' => $auditPlans,
            'users' => $users,
            'severities' => FINDING_SEVERITIES
        ]);
    }
    
    /**
     * Store audit finding
     * 
     * @return void
     */
    public function storeFinding(): void
    {
        try {
            $this->requirePermission(PERM_AUDIT_EXECUTE);
            
            // Validate CSRF token
            $this->validateCSRF($_POST['csrf_token'] ?? '');
            
            // Validate input
            $validationRules = [
                'audit_plan_id' => 'required|exists:audit_plans,id',
                'title' => 'required|min:3|max:255',
                'description' => 'required|min:10',
                'severity' => 'required|in:' . implode(',', array_keys(FINDING_SEVERITIES)),
                'impact_description' => 'string|max:1000',
                'root_cause' => 'string|max:1000',
                'recommendation' => 'required|min:10',
                'assigned_to' => 'exists:users,id',
                'finding_date' => 'required|date'
            ];
            
            $validated = $this->validate($_POST, $validationRules);
            
            // Generate finding code
            $findingCode = $this->generateFindingCode();
            
            // Create finding
            $findingData = [
                'audit_plan_id' => $validated['audit_plan_id'],
                'finding_code' => $findingCode,
                'title' => $validated['title'],
                'description' => $validated['description'],
                'severity' => $validated['severity'],
                'impact_description' => $validated['impact_description'] ?? null,
                'root_cause' => $validated['root_cause'] ?? null,
                'recommendation' => $validated['recommendation'],
                'assigned_to' => $validated['assigned_to'] ?? null,
                'assigned_by' => Auth::id(),
                'finding_date' => $validated['finding_date'],
                'status' => 'open',
                'created_by' => Auth::id(),
                'created_at' => date('Y-m-d H:i:s')
            ];
            
            $findingId = $this->findingModel->create($findingData);
            
            if (!$findingId) {
                throw new Exception('Failed to create audit finding.');
            }
            
            // Send notification to assigned user
            if ($validated['assigned_to']) {
                $this->notificationService->sendFindingAssignment(
                    $validated['assigned_to'],
                    $findingId,
                    $findingCode
                );
            }
            
            // Log activity
            $this->logActivity('audit_finding_create', 'Created audit finding: ' . $findingCode);
            
            $this->setFlashMessage('success', 'Audit finding created successfully.');
            $this->redirectToRoute('audit.findings');
            
        } catch (Exception $e) {
            $this->setFlashMessage('error', $e->getMessage());
            $this->redirectToRoute('audit.finding.create');
        }
    }
    
    /**
     * Show finding details
     * 
     * @param array $params Route parameters
     * @return void
     */
    public function showFinding(array $params): void
    {
        try {
            $findingId = (int)($params['id'] ?? 0);
            $finding = $this->findingModel->find($findingId);
            
            if (!$finding) {
                throw new Exception('Audit finding not found.');
            }
            
            // Get finding history
            $history = $this->findingModel->getHistory($findingId);
            
            $this->render('show-finding', [
                'title' => 'Audit Finding Details - ' . APP_NAME,
                'finding' => $finding,
                'history' => $history,
                'severities' => FINDING_SEVERITIES
            ]);
            
        } catch (Exception $e) {
            $this->setFlashMessage('error', $e->getMessage());
            $this->redirectToRoute('audit.findings');
        }
    }
    
    /**
     * Show edit finding form
     * 
     * @param array $params Route parameters
     * @return void
     */
    public function editFinding(array $params): void
    {
        try {
            $this->requirePermission(PERM_AUDIT_UPDATE);
            
            $findingId = (int)($params['id'] ?? 0);
            $finding = $this->findingModel->find($findingId);
            
            if (!$finding) {
                throw new Exception('Audit finding not found.');
            }
            
            $auditPlans = $this->planModel->getAll();
            $users = $this->getUsers();
            
            $this->render('edit-finding', [
                'title' => 'Edit Audit Finding - ' . APP_NAME,
                'finding' => $finding,
                'audit_plans' => $auditPlans,
                'users' => $users,
                'severities' => FINDING_SEVERITIES
            ]);
            
        } catch (Exception $e) {
            $this->setFlashMessage('error', $e->getMessage());
            $this->redirectToRoute('audit.findings');
        }
    }
    
    /**
     * Update audit finding
     * 
     * @param array $params Route parameters
     * @return void
     */
    public function updateFinding(array $params): void
    {
        try {
            $this->requirePermission(PERM_AUDIT_UPDATE);
            
            $findingId = (int)($params['id'] ?? 0);
            $finding = $this->findingModel->find($findingId);
            
            if (!$finding) {
                throw new Exception('Audit finding not found.');
            }
            
            // Validate CSRF token
            $this->validateCSRF($_POST['csrf_token'] ?? '');
            
            // Validate input
            $validationRules = [
                'title' => 'required|min:3|max:255',
                'description' => 'required|min:10',
                'severity' => 'required|in:' . implode(',', array_keys(FINDING_SEVERITIES)),
                'impact_description' => 'string|max:1000',
                'root_cause' => 'string|max:1000',
                'recommendation' => 'required|min:10',
                'assigned_to' => 'exists:users,id',
                'status' => 'required|in:open,in_progress,resolved,closed,accepted_risk',
                'resolution_date' => 'date|after:finding_date',
                'review_date' => 'date|after:resolution_date'
            ];
            
            $validated = $this->validate($_POST, $validationRules);
            
            // Update finding
            $findingData = [
                'title' => $validated['title'],
                'description' => $validated['description'],
                'severity' => $validated['severity'],
                'impact_description' => $validated['impact_description'] ?? null,
                'root_cause' => $validated['root_cause'] ?? null,
                'recommendation' => $validated['recommendation'],
                'assigned_to' => $validated['assigned_to'] ?? null,
                'status' => $validated['status'],
                'resolution_date' => $validated['resolution_date'] ?? null,
                'review_date' => $validated['review_date'] ?? null,
                'updated_at' => date('Y-m-d H:i:s')
            ];
            
            $result = $this->findingModel->update($findingId, $findingData);
            
            if (!$result) {
                throw new Exception('Failed to update audit finding.');
            }
            
            // Log activity
            $this->logActivity('audit_finding_update', 'Updated audit finding: ' . $finding->finding_code);
            
            $this->setFlashMessage('success', 'Audit finding updated successfully.');
            $this->redirectToRoute('audit.findings');
            
        } catch (Exception $e) {
            $this->setFlashMessage('error', $e->getMessage());
            $this->redirectToRoute('audit.finding.edit', ['id' => $params['id']]);
        }
    }
    
    /**
     * Delete audit finding
     * 
     * @param array $params Route parameters
     * @return void
     */
    public function deleteFinding(array $params): void
    {
        try {
            $this->requirePermission(PERM_AUDIT_DELETE);
            
            $findingId = (int)($params['id'] ?? 0);
            $finding = $this->findingModel->find($findingId);
            
            if (!$finding) {
                throw new Exception('Audit finding not found.');
            }
            
            // Validate CSRF token
            $this->validateCSRF($_POST['csrf_token'] ?? '');
            
            $result = $this->findingModel->softDelete($findingId);
            
            if (!$result) {
                throw new Exception('Failed to delete audit finding.');
            }
            
            // Log activity
            $this->logActivity('audit_finding_delete', 'Deleted audit finding: ' . $finding->finding_code);
            
            $this->jsonSuccess('Audit finding deleted successfully.');
            
        } catch (Exception $e) {
            $this->jsonError($e->getMessage());
        }
    }
    
    /**
     * Update finding status
     * 
     * @param array $params Route parameters
     * @return void
     */
    public function updateFindingStatus(array $params): void
    {
        try {
            $findingId = (int)($params['id'] ?? 0);
            $finding = $this->findingModel->find($findingId);
            
            if (!$finding) {
                throw new Exception('Audit finding not found.');
            }
            
            $status = $this->input('status');
            $remarks = $this->input('remarks');
            
            $validStatuses = ['open', 'in_progress', 'resolved', 'closed', 'accepted_risk'];
            if (!in_array($status, $validStatuses)) {
                throw new Exception('Invalid status.');
            }
            
            $result = $this->findingModel->updateStatus($findingId, $status, $remarks, Auth::id());
            
            if (!$result) {
                throw new Exception('Failed to update finding status.');
            }
            
            // Log activity
            $this->logActivity('audit_finding_status', 
                'Updated finding ' . $finding->finding_code . ' status to ' . $status
            );
            
            $this->jsonSuccess('Finding status updated successfully.');
            
        } catch (Exception $e) {
            $this->jsonError($e->getMessage());
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
            'total_plans' => $this->planModel->countAll(),
            'plans_in_progress' => $this->planModel->countByStatus(AUDIT_STATUS_IN_PROGRESS),
            'plans_completed' => $this->planModel->countByStatus(AUDIT_STATUS_COMPLETED),
            'total_findings' => $this->findingModel->countAll(),
            'open_findings' => $this->findingModel->countByStatus('open'),
            'resolved_findings' => $this->findingModel->countByStatus('resolved'),
            'critical_findings' => $this->findingModel->countBySeverity(FINDING_SEVERITY_CRITICAL),
            'high_findings' => $this->findingModel->countBySeverity(FINDING_SEVERITY_HIGH),
            'findings_by_type' => $this->findingModel->countByType(),
            'resolution_rate' => $this->findingModel->getResolutionRate()
        ];
    }
    
    /**
     * Generate audit reference number
     * 
     * @return string
     */
    private function generateAuditReference(): string
    {
        return 'AUDIT-' . date('Y') . '-' . str_pad(random_int(1, 9999), 4, '0', STR_PAD_LEFT);
    }
    
    /**
     * Generate finding code
     * 
     * @return string
     */
    private function generateFindingCode(): string
    {
        return 'FIND-' . date('Y') . '-' . str_pad(random_int(1, 9999), 4, '0', STR_PAD_LEFT);
    }
    
    /**
     * Get departments
     * 
     * @return array
     */
    private function getDepartments(): array
    {
        return [];
    }
    
    /**
     * Get users
     * 
     * @return array
     */
    private function getUsers(): array
    {
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