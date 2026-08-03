<?php
/**
 * Compliance Module - Controller
 * 
 * @package    AI-Banking-GRC-Platform
 * @subpackage modules/compliance
 * @version    1.0.0
 * @author     GRC Platform Team
 * @copyright  2026 AI Banking GRC Platform
 * @license    Proprietary
 * 
 * This controller handles:
 * - Compliance dashboard
 * - SBP circular management
 * - Compliance monitoring
 * - Gap analysis
 * - Recommendations
 * - Evidence management
 */

declare(strict_types=1);

namespace Modules\Compliance\Controllers;

use App\Controllers\BaseController;
use App\Helpers\Auth;
use App\Helpers\CSRF;
use App\Helpers\Validation;
use Modules\Compliance\Services\ComplianceService;
use App\Models\SbpCircular;
use App\Models\ComplianceTask;
use Exception;

class ComplianceController extends BaseController
{
    /**
     * @var ComplianceService
     */
    private ComplianceService $complianceService;
    
    /**
     * @var SbpCircular
     */
    private SbpCircular $circularModel;
    
    /**
     * @var ComplianceTask
     */
    private ComplianceTask $taskModel;
    
    /**
     * Constructor
     */
    public function __construct()
    {
        parent::__construct();
        $this->controllerName = 'Compliance';
        $this->complianceService = new ComplianceService();
        $this->circularModel = new SbpCircular();
        $this->taskModel = new ComplianceTask();
        
        $this->requireAuth();
        $this->requirePermission('compliance_view');
    }
    
    /**
     * Compliance dashboard
     * 
     * @return void
     */
    public function index(): void
    {
        try {
            $userId = Auth::id();
            $dashboardData = $this->complianceService->getDashboardData($userId);
            $frameworks = COMPLIANCE_FRAMEWORKS;
            $categories = COMPLIANCE_CATEGORIES;
            
            $this->render('compliance/dashboard', [
                'title' => 'Compliance Dashboard - ' . APP_NAME,
                'data' => $dashboardData,
                'frameworks' => $frameworks,
                'categories' => $categories,
                'compliance_status' => COMPLIANCE_STATUS,
                'compliance_levels' => COMPLIANCE_LEVELS
            ]);
            
        } catch (Exception $e) {
            $this->setFlashMessage('error', 'Failed to load compliance dashboard: ' . $e->getMessage());
            $this->redirectToRoute('dashboard');
        }
    }
    
    /**
     * SBP Circulars management
     * 
     * @return void
     */
    public function circulars(): void
    {
        try {
            $this->requirePermission('sbp_view');
            
            $filters = [
                'status' => $this->input('status'),
                'category' => $this->input('category'),
                'priority' => $this->input('priority'),
                'search' => $this->input('search')
            ];
            
            $page = (int)$this->input('page', 1);
            $perPage = (int)$this->input('per_page', 15);
            
            $circulars = $this->circularModel->getFiltered($filters, $page, $perPage);
            $total = $this->circularModel->countFiltered($filters);
            
            $this->render('compliance/circulars', [
                'title' => 'SBP Circulars - ' . APP_NAME,
                'circulars' => $circulars,
                'total' => $total,
                'page' => $page,
                'per_page' => $perPage,
                'total_pages' => ceil($total / $perPage),
                'filters' => $filters,
                'categories' => SBP_CIRCULAR_CATEGORIES,
                'statuses' => COMPLIANCE_STATUS,
                'frameworks' => COMPLIANCE_FRAMEWORKS
            ]);
            
        } catch (Exception $e) {
            $this->setFlashMessage('error', 'Failed to load circulars: ' . $e->getMessage());
            $this->redirectToRoute('compliance.index');
        }
    }
    
    /**
     * Circular details
     * 
     * @param array $params
     * @return void
     */
    public function circularDetails(array $params): void
    {
        try {
            $this->requirePermission('sbp_view');
            
            $circularId = (int)($params['id'] ?? 0);
            $circular = $this->circularModel->find($circularId);
            
            if (!$circular) {
                throw new Exception('Circular not found.');
            }
            
            // Get related circulars
            $related = $this->circularModel->getRelated($circularId);
            
            // Get compliance checklist
            $checklist = $this->circularModel->generateChecklist($circularId);
            
            $this->render('compliance/circular-details', [
                'title' => 'Circular Details - ' . APP_NAME,
                'circular' => $circular,
                'related' => $related,
                'checklist' => $checklist,
                'categories' => SBP_CIRCULAR_CATEGORIES,
                'statuses' => COMPLIANCE_STATUS
            ]);
            
        } catch (Exception $e) {
            $this->setFlashMessage('error', $e->getMessage());
            $this->redirectToRoute('compliance.circulars');
        }
    }
    
    /**
     * Compliance checklist
     * 
     * @return void
     */
    public function checklist(): void
    {
        try {
            $userId = Auth::id();
            $filters = [
                'status' => $this->input('status'),
                'category' => $this->input('category'),
                'priority' => $this->input('priority')
            ];
            
            $tasks = $this->taskModel->getFiltered($filters, 1, 50);
            $progress = $this->getChecklistProgress($userId);
            
            $this->render('compliance/checklist', [
                'title' => 'Compliance Checklist - ' . APP_NAME,
                'tasks' => $tasks,
                'progress' => $progress,
                'categories' => COMPLIANCE_CATEGORIES,
                'statuses' => COMPLIANCE_STATUS
            ]);
            
        } catch (Exception $e) {
            $this->setFlashMessage('error', 'Failed to load checklist: ' . $e->getMessage());
            $this->redirectToRoute('compliance.index');
        }
    }
    
    /**
     * Get checklist progress
     * 
     * @param int $userId
     * @return array
     */
    private function getChecklistProgress(int $userId): array
    {
        $total = $this->taskModel->countUserTasks($userId);
        $completed = $this->taskModel->countUserTasks($userId, 'completed');
        $inProgress = $this->taskModel->countUserTasks($userId, 'in_progress');
        $pending = $this->taskModel->countUserTasks($userId, 'pending');
        
        return [
            'total' => $total,
            'completed' => $completed,
            'in_progress' => $inProgress,
            'pending' => $pending,
            'percentage' => $total > 0 ? round(($completed / $total) * 100, 2) : 0
        ];
    }
    
    /**
     * Compliance status
     * 
     * @return void
     */
    public function status(): void
    {
        try {
            $stats = $this->complianceService->getComplianceStats();
            $frameworks = $this->getFrameworkStatus();
            $gaps = $this->complianceService->getGapAnalysis();
            
            $this->render('compliance/status', [
                'title' => 'Compliance Status - ' . APP_NAME,
                'stats' => $stats,
                'frameworks' => $frameworks,
                'gaps' => $gaps,
                'compliance_levels' => COMPLIANCE_LEVELS,
                'categories' => COMPLIANCE_CATEGORIES
            ]);
            
        } catch (Exception $e) {
            $this->setFlashMessage('error', 'Failed to load compliance status: ' . $e->getMessage());
            $this->redirectToRoute('compliance.index');
        }
    }
    
    /**
     * Get framework status
     * 
     * @return array
     */
    private function getFrameworkStatus(): array
    {
        $frameworks = [];
        
        foreach (COMPLIANCE_FRAMEWORKS as $key => $framework) {
            $sql = "SELECT 
                        COUNT(*) as total,
                        SUM(CASE WHEN status = 'completed' THEN 1 ELSE 0 END) as compliant
                    FROM compliance_tasks
                    WHERE framework_id = (SELECT id FROM compliance_frameworks WHERE code = :code)
                    AND deleted_at IS NULL";
            
            $stmt = $this->db->prepare($sql);
            $stmt->execute(['code' => $key]);
            $row = $stmt->fetch(PDO::FETCH_OBJ);
            
            $total = (int)($row->total ?? 0);
            $compliant = (int)($row->compliant ?? 0);
            
            $frameworks[] = [
                'name' => $framework['name'],
                'version' => $framework['version'],
                'total' => $total,
                'compliant' => $compliant,
                'score' => $total > 0 ? round(($compliant / $total) * 100, 2) : 0
            ];
        }
        
        return $frameworks;
    }
    
    /**
     * Gap analysis
     * 
     * @return void
     */
    public function gapAnalysis(): void
    {
        try {
            $framework = $this->input('framework', 'all');
            $gaps = $this->complianceService->getGapAnalysis($framework);
            
            $this->render('compliance/gap-analysis', [
                'title' => 'Gap Analysis - ' . APP_NAME,
                'gaps' => $gaps,
                'framework' => $framework,
                'frameworks' => COMPLIANCE_FRAMEWORKS
            ]);
            
        } catch (Exception $e) {
            $this->setFlashMessage('error', 'Failed to load gap analysis: ' . $e->getMessage());
            $this->redirectToRoute('compliance.index');
        }
    }
    
    /**
     * Recommendations
     * 
     * @return void
     */
    public function recommendations(): void
    {
        try {
            $userId = Auth::id();
            $recommendations = $this->complianceService->getRecommendations($userId);
            
            $this->render('compliance/recommendations', [
                'title' => 'Compliance Recommendations - ' . APP_NAME,
                'recommendations' => $recommendations
            ]);
            
        } catch (Exception $e) {
            $this->setFlashMessage('error', 'Failed to load recommendations: ' . $e->getMessage());
            $this->redirectToRoute('compliance.index');
        }
    }
    
    /**
     * Compliance calendar
     * 
     * @return void
     */
    public function calendar(): void
    {
        try {
            $userId = Auth::id();
            $month = (int)$this->input('month', date('n'));
            $year = (int)$this->input('year', date('Y'));
            
            $calendarData = $this->complianceService->getComplianceCalendar($userId, $month, $year);
            
            $this->render('compliance/calendar', [
                'title' => 'Compliance Calendar - ' . APP_NAME,
                'calendar_data' => $calendarData,
                'month' => $month,
                'year' => $year,
                'statuses' => COMPLIANCE_STATUS
            ]);
            
        } catch (Exception $e) {
            $this->setFlashMessage('error', 'Failed to load calendar: ' . $e->getMessage());
            $this->redirectToRoute('compliance.index');
        }
    }
    
    /**
     * Evidence management
     * 
     * @param array $params
     * @return void
     */
    public function evidence(array $params): void
    {
        try {
            $taskId = (int)($params['id'] ?? 0);
            $evidence = $this->complianceService->getEvidence($taskId);
            
            $this->render('compliance/evidence', [
                'title' => 'Evidence Management - ' . APP_NAME,
                'evidence' => $evidence,
                'task_id' => $taskId,
                'evidence_types' => EVIDENCE_TYPES
            ]);
            
        } catch (Exception $e) {
            $this->setFlashMessage('error', 'Failed to load evidence: ' . $e->getMessage());
            $this->redirectToRoute('compliance.index');
        }
    }
    
    /**
     * Upload evidence (AJAX)
     * 
     * @param array $params
     * @return void
     */
    public function uploadEvidence(array $params): void
    {
        try {
            CSRF::validate($_POST['csrf_token'] ?? '');
            
            $taskId = (int)($params['id'] ?? 0);
            $userId = Auth::id();
            $description = $this->input('description');
            
            if (!isset($_FILES['evidence'])) {
                throw new Exception('No file uploaded.');
            }
            
            $result = $this->complianceService->uploadEvidence(
                $taskId,
                $_FILES['evidence'],
                $description,
                $userId
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
     * Verify evidence (AJAX)
     * 
     * @param array $params
     * @return void
     */
    public function verifyEvidence(array $params): void
    {
        try {
            CSRF::validate($_POST['csrf_token'] ?? '');
            $this->requirePermission('compliance_approve');
            
            $evidenceId = (int)($params['id'] ?? 0);
            $userId = Auth::id();
            $status = $this->input('status');
            $notes = $this->input('notes');
            
            $result = $this->complianceService->verifyEvidence($evidenceId, $userId, $status, $notes);
            
            if (!$result) {
                throw new Exception('Failed to verify evidence.');
            }
            
            $this->jsonSuccess('Evidence verified successfully.');
            
        } catch (Exception $e) {
            $this->jsonError($e->getMessage());
        }
    }
}