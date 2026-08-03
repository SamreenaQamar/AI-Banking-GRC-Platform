<?php
namespace App\Controllers;

use App\Models\Report;
use App\Helpers\Auth;
use App\Services\ReportService;
use Exception;

class ReportController extends BaseController
{
    private Report $reportModel;
    private ReportService $reportService;
    
    public function __construct()
    {
        parent::__construct();
        $this->controllerName = 'Report';
        $this->reportModel = new Report();
        $this->reportService = new ReportService();
        $this->requireAuth();
        $this->requirePermission(PERM_REPORT_VIEW);
    }
    
    public function index(): void
    {
        $reports = $this->reportModel->getUserReports(Auth::id());
        $this->render('index', [
            'title' => 'Reports - ' . APP_NAME,
            'reports' => $reports
        ]);
    }
    
    public function create(): void
    {
        $this->requirePermission(PERM_REPORT_CREATE);
        $this->render('create', [
            'title' => 'Create Report - ' . APP_NAME,
            'report_types' => ['compliance', 'risk', 'audit', 'sbp', 'custom']
        ]);
    }
    
    public function generate(): void
    {
        try {
            $this->requirePermission(PERM_REPORT_CREATE);
            $this->validateCSRF($_POST['csrf_token'] ?? '');
            
            $reportData = $this->validate($_POST, [
                'name' => 'required|min:3|max:255',
                'report_type' => 'required|in:compliance,risk,audit,sbp,custom',
                'parameters' => 'json',
                'format' => 'in:pdf,xlsx,csv'
            ]);
            
            $reportId = $this->reportService->generate($reportData);
            $this->jsonSuccess('Report generated successfully.', ['id' => $reportId]);
            
        } catch (Exception $e) {
            $this->jsonError('Report generation failed: ' . $e->getMessage());
        }
    }
    
    public function download(array $params): void
    {
        try {
            $reportId = (int)$params['id'];
            $report = $this->reportModel->find($reportId);
            
            if (!$report) {
                throw new Exception('Report not found.');
            }
            
            $this->reportService->download($report);
            
        } catch (Exception $e) {
            $this->setFlashMessage('error', $e->getMessage());
            $this->redirectToRoute('reports.index');
        }
    }
}