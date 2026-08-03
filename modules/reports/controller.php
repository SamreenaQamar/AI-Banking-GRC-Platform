<?php
/**
 * Reports Module - Controller
 * 
 * @package    AI-Banking-GRC-Platform
 * @subpackage modules/reports
 * @version    1.0.0
 * @author     GRC Platform Team
 * @copyright  2026 AI Banking GRC Platform
 * @license    Proprietary
 * 
 * This controller handles:
 * - Report dashboard
 * - Report generation
 * - Report export
 * - Scheduling
 * - Analytics
 */

declare(strict_types=1);

namespace Modules\Reports\Controllers;

use App\Controllers\BaseController;
use App\Helpers\Auth;
use App\Helpers\CSRF;
use App\Helpers\Validation;
use Modules\Reports\Services\ReportService;
use App\Models\Report;
use Exception;

class ReportController extends BaseController
{
    /**
     * @var ReportService
     */
    private ReportService $reportService;
    
    /**
     * @var Report
     */
    private Report $reportModel;
    
    /**
     * Constructor
     */
    public function __construct()
    {
        parent::__construct();
        $this->controllerName = 'Reports';
        $this->reportService = new ReportService();
        $this->reportModel = new Report();
        
        $this->requireAuth();
        $this->requirePermission('report_view');
    }
    
    /**
     * Reports dashboard
     * 
     * @return void
     */
    public function index(): void
    {
        try {
            $userId = Auth::id();
            $dashboardData = $this->reportService->getDashboardData($userId);
            
            $this->render('reports/dashboard', [
                'title' => 'Reports Dashboard - ' . APP_NAME,
                'data' => $dashboardData,
                'report_types' => REPORT_TYPES,
                'report_formats' => REPORT_FORMATS,
                'report_periods' => REPORT_PERIODS
            ]);
            
        } catch (Exception $e) {
            $this->setFlashMessage('error', 'Failed to load reports dashboard: ' . $e->getMessage());
            $this->redirectToRoute('dashboard');
        }
    }
    
    /**
     * Generate report
     * 
     * @return void
     */
    public function generate(): void
    {
        try {
            $this->requirePermission('report_create');
            CSRF::validate($_POST['csrf_token'] ?? '');
            
            $reportType = $this->input('report_type');
            $format = $this->input('format', report_setting('default_format', 'pdf'));
            $period = $this->input('period', report_setting('default_period', 'month'));
            $name = $this->input('name');
            $dateFrom = $this->input('date_from');
            $dateTo = $this->input('date_to');
            
            if (empty($name)) {
                throw new Exception('Report name is required.');
            }
            
            if (!in_array($reportType, array_keys(REPORT_TYPES))) {
                throw new Exception('Invalid report type.');
            }
            
            $params = [
                'period' => $period,
                'date_from' => $dateFrom,
                'date_to' => $dateTo,
                'report_type' => $reportType,
                'format' => $format
            ];
            
            // Generate report based on type
            $reportData = $this->generateReportByType($reportType, $params);
            
            // Save report
            $reportData['name'] = $name;
            $reportData['generated_by'] = Auth::id();
            $reportData['format'] = $format;
            
            $reportId = $this->reportModel->create($reportData);
            
            if (!$reportId) {
                throw new Exception('Failed to save report.');
            }
            
            $this->setFlashMessage('success', 'Report generated successfully.');
            $this->redirectToRoute('reports.index');
            
        } catch (Exception $e) {
            $this->setFlashMessage('error', $e->getMessage());
            $this->redirectToRoute('reports.index');
        }
    }
    
    /**
     * Generate report by type
     * 
     * @param string $type
     * @param array $params
     * @return array
     */
    private function generateReportByType(string $type, array $params): array
    {
        $userId = Auth::id();
        
        switch ($type) {
            case 'executive':
                return $this->reportService->generateExecutiveReport($params, $userId);
            case 'compliance':
                return $this->reportService->generateComplianceReport($params, $userId);
            case 'risk':
                return $this->reportService->generateRiskReport($params, $userId);
            case 'audit':
                return $this->reportService->generateAuditReport($params, $userId);
            default:
                return $this->reportService->generateCustomReport($params, $userId);
        }
    }
    
    /**
     * Download report
     * 
     * @param array $params
     * @return void
     */
    public function download(array $params): void
    {
        try {
            $reportId = (int)($params['id'] ?? 0);
            $report = $this->reportModel->find($reportId);
            
            if (!$report) {
                throw new Exception('Report not found.');
            }
            
            if ($report->generated_by != Auth::id() && !Auth::hasRole('admin')) {
                throw new Exception('Unauthorized access.');
            }
            
            // Get report data
            $reportData = json_decode($report->data ?? '{}', true);
            $format = $report->file_type ?? report_setting('default_format', 'pdf');
            
            // Export report
            $export = $this->reportService->exportReport($reportData, $format);
            
            // Set headers for download
            header('Content-Type: ' . $export['mime']);
            header('Content-Disposition: attachment; filename="' . $export['filename'] . '"');
            header('Content-Length: ' . strlen($export['content']));
            header('Cache-Control: private, max-age=0, must-revalidate');
            header('Pragma: public');
            
            echo $export['content'];
            exit;
            
        } catch (Exception $e) {
            $this->setFlashMessage('error', $e->getMessage());
            $this->redirectToRoute('reports.index');
        }
    }
    
    /**
     * Delete report
     * 
     * @param array $params
     * @return void
     */
    public function delete(array $params): void
    {
        try {
            $this->requirePermission('report_delete');
            CSRF::validate($_POST['csrf_token'] ?? '');
            
            $reportId = (int)($params['id'] ?? 0);
            $report = $this->reportModel->find($reportId);
            
            if (!$report) {
                throw new Exception('Report not found.');
            }
            
            $result = $this->reportModel->softDelete($reportId);
            
            if (!$result) {
                throw new Exception('Failed to delete report.');
            }
            
            $this->jsonSuccess('Report deleted successfully.');
            
        } catch (Exception $e) {
            $this->jsonError($e->getMessage());
        }
    }
    
    /**
     * Schedule report
     * 
     * @return void
     */
    public function schedule(): void
    {
        try {
            $this->requirePermission('report_create');
            CSRF::validate($_POST['csrf_token'] ?? '');
            
            $reportType = $this->input('report_type');
            $scheduleFrequency = $this->input('schedule_frequency');
            $recipients = $this->input('recipients');
            $name = $this->input('name');
            $format = $this->input('format', report_setting('default_format', 'pdf'));
            
            if (empty($name)) {
                throw new Exception('Report name is required.');
            }
            
            $scheduleData = [
                'name' => $name,
                'report_type' => $reportType,
                'format' => $format,
                'is_scheduled' => 1,
                'schedule_config' => json_encode([
                    'frequency' => $scheduleFrequency,
                    'recipients' => $recipients
                ]),
                'next_run' => $this->calculateNextRun($scheduleFrequency),
                'generated_by' => Auth::id(),
                'created_at' => date('Y-m-d H:i:s')
            ];
            
            $reportId = $this->reportModel->create($scheduleData);
            
            if (!$reportId) {
                throw new Exception('Failed to schedule report.');
            }
            
            $this->setFlashMessage('success', 'Report scheduled successfully.');
            $this->redirectToRoute('reports.index');
            
        } catch (Exception $e) {
            $this->setFlashMessage('error', $e->getMessage());
            $this->redirectToRoute('reports.index');
        }
    }
    
    /**
     * Calculate next run time
     * 
     * @param string $frequency
     * @return string
     */
    private function calculateNextRun(string $frequency): string
    {
        switch ($frequency) {
            case 'daily':
                return date('Y-m-d H:i:s', strtotime('tomorrow 00:00:00'));
            case 'weekly':
                return date('Y-m-d H:i:s', strtotime('next monday 00:00:00'));
            case 'monthly':
                return date('Y-m-d H:i:s', strtotime('first day of next month 00:00:00'));
            case 'quarterly':
                return date('Y-m-d H:i:s', strtotime('first day of next quarter 00:00:00'));
            default:
                return date('Y-m-d H:i:s', strtotime('+1 day'));
        }
    }
}