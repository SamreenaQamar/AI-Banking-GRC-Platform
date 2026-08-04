<?php
/**
 * AI Banking GRC Platform - Report Service
 * 
 * @package    AI-Banking-GRC-Platform
 * @subpackage app/Services
 * @version    1.0.0
 * @author     GRC Platform Team
 * @copyright  2026 AI Banking GRC Platform
 * @license    Proprietary
 * 
 * This service handles report generation business logic:
 * - Dashboard reports
 * - Risk reports
 * - Compliance reports
 * - Audit reports
 * - User reports
 * - Export (PDF, Excel, CSV, JSON)
 */

declare(strict_types=1);

namespace App\Services;

use App\Models\Report;
use App\Models\GeneratedReport;
use App\Libraries\Logger;
use App\Libraries\Validator;
use App\Libraries\Cache;
use App\Libraries\PDFGenerator;
use App\Libraries\ExcelExporter;
use App\Libraries\Security;

class ReportService
{
    /**
     * @var Report Report model
     */
    private Report $reportModel;

    /**
     * @var GeneratedReport Generated report model
     */
    private GeneratedReport $generatedReportModel;

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
     * @var PDFGenerator PDF generator
     */
    private PDFGenerator $pdfGenerator;

    /**
     * @var ExcelExporter Excel exporter
     */
    private ExcelExporter $excelExporter;

    /**
     * @var Security Security instance
     */
    private Security $security;

    /**
     * @var array Report types
     */
    private array $reportTypes = [
        'dashboard', 'risk', 'compliance', 'audit', 'user', 'policy', 'sbp'
    ];

    /**
     * @var array Export formats
     */
    private array $exportFormats = ['pdf', 'excel', 'csv', 'json'];

    /**
     * Constructor
     */
    public function __construct()
    {
        $this->reportModel = new Report();
        $this->generatedReportModel = new GeneratedReport();
        $this->logger = new Logger();
        $this->validator = new Validator();
        $this->cache = new Cache();
        $this->pdfGenerator = new PDFGenerator();
        $this->excelExporter = new ExcelExporter();
        $this->security = new Security();
    }

    /**
     * Generate a report
     * 
     * @param string $type
     * @param array $params
     * @param int $userId
     * @return array
     */
    public function generate(string $type, array $params, int $userId): array
    {
        try {
            if (!in_array($type, $this->reportTypes)) {
                return $this->errorResponse('Invalid report type.', 'INVALID_TYPE');
            }

            // Generate report data
            $data = $this->generateReportData($type, $params);

            // Create report record
            $reportData = [
                'name' => $params['name'] ?? ucfirst($type) . ' Report - ' . date('Y-m-d'),
                'description' => $params['description'] ?? null,
                'report_type' => $type,
                'parameters' => json_encode($params),
                'generated_by' => $userId,
                'created_at' => date('Y-m-d H:i:s')
            ];

            $reportId = $this->reportModel->create($reportData);

            if (!$reportId) {
                return $this->errorResponse('Failed to save report record.', 'SAVE_FAILED');
            }

            // Generate file
            $format = $params['format'] ?? 'pdf';
            $filePath = $this->generateFile($reportId, $data, $format);

            // Create generated report record
            $generatedData = [
                'report_id' => $reportId,
                'file_path' => $filePath,
                'file_type' => $format,
                'file_size' => filesize($filePath),
                'parameters_used' => json_encode($params),
                'generated_by' => $userId,
                'generated_at' => date('Y-m-d H:i:s')
            ];

            $this->generatedReportModel->create($generatedData);

            // Log activity
            $this->logger->info('Report generated', [
                'report_id' => $reportId,
                'type' => $type,
                'format' => $format,
                'user_id' => $userId
            ]);

            return $this->successResponse('Report generated successfully.', [
                'report_id' => $reportId,
                'file_path' => $filePath,
                'format' => $format
            ]);

        } catch (\Exception $e) {
            $this->logger->error('Generate report error: ' . $e->getMessage());
            return $this->errorResponse('An error occurred generating report.', 'ERROR');
        }
    }

    /**
     * Generate report data
     * 
     * @param string $type
     * @param array $params
     * @return array
     */
    private function generateReportData(string $type, array $params): array
    {
        switch ($type) {
            case 'dashboard':
                return $this->getDashboardData($params);
            case 'risk':
                return $this->getRiskData($params);
            case 'compliance':
                return $this->getComplianceData($params);
            case 'audit':
                return $this->getAuditData($params);
            case 'user':
                return $this->getUserData($params);
            case 'policy':
                return $this->getPolicyData($params);
            case 'sbp':
                return $this->getSBPData($params);
            default:
                return [];
        }
    }

    /**
     * Get dashboard report data
     * 
     * @param array $params
     * @return array
     */
    private function getDashboardData(array $params): array
    {
        $dashboardService = new DashboardService();
        $stats = $dashboardService->statistics();
        $charts = $dashboardService->charts();

        return [
            'title' => 'Dashboard Report',
            'generated_at' => date('Y-m-d H:i:s'),
            'statistics' => $stats,
            'charts' => $charts,
            'summary' => $dashboardService->summary(0)['summary'] ?? []
        ];
    }

    /**
     * Get risk report data
     * 
     * @param array $params
     * @return array
     */
    private function getRiskData(array $params): array
    {
        $riskService = new RiskService();
        $filters = $params['filters'] ?? [];
        $risks = $riskService->all($filters, 1, 1000);

        return [
            'title' => 'Risk Report',
            'generated_at' => date('Y-m-d H:i:s'),
            'risks' => $risks['data']['risks'] ?? [],
            'statistics' => $risks['data']['statistics'] ?? [],
            'filters' => $filters
        ];
    }

    /**
     * Get compliance report data
     * 
     * @param array $params
     * @return array
     */
    private function getComplianceData(array $params): array
    {
        $complianceService = new ComplianceService();
        $filters = $params['filters'] ?? [];
        $report = $complianceService->report($filters);

        return [
            'title' => 'Compliance Report',
            'generated_at' => date('Y-m-d H:i:s'),
            'data' => $report['data'] ?? [],
            'statistics' => $report['statistics'] ?? [],
            'filters' => $filters
        ];
    }

    /**
     * Get audit report data
     * 
     * @param array $params
     * @return array
     */
    private function getAuditData(array $params): array
    {
        $auditService = new AuditService();
        $auditId = $params['audit_id'] ?? 0;

        if ($auditId > 0) {
            return $auditService->report($auditId)['data'] ?? [];
        }

        $filters = $params['filters'] ?? [];
        $audits = $auditService->all($filters, 1, 1000);

        return [
            'title' => 'Audit Report',
            'generated_at' => date('Y-m-d H:i:s'),
            'audits' => $audits['data']['audits'] ?? [],
            'filters' => $filters
        ];
    }

    /**
     * Get user report data
     * 
     * @param array $params
     * @return array
     */
    private function getUserData(array $params): array
    {
        $userService = new UserService();
        $filters = $params['filters'] ?? [];
        $users = $userService->all($filters, 1, 1000);

        return [
            'title' => 'User Report',
            'generated_at' => date('Y-m-d H:i:s'),
            'users' => $users['data']['users'] ?? [],
            'filters' => $filters
        ];
    }

    /**
     * Get policy report data
     * 
     * @param array $params
     * @return array
     */
    private function getPolicyData(array $params): array
    {
        $policyModel = new \App\Models\Policy();
        $filters = $params['filters'] ?? [];
        $policies = $policyModel->getFiltered($filters, 1, 1000);

        return [
            'title' => 'Policy Report',
            'generated_at' => date('Y-m-d H:i:s'),
            'policies' => $policies,
            'filters' => $filters
        ];
    }

    /**
     * Get SBP report data
     * 
     * @param array $params
     * @return array
     */
    private function getSBPData(array $params): array
    {
        $sbpModel = new \App\Models\SbpCircular();
        $filters = $params['filters'] ?? [];
        $circulars = $sbpModel->getFiltered($filters, 1, 1000);

        return [
            'title' => 'SBP Circular Report',
            'generated_at' => date('Y-m-d H:i:s'),
            'circulars' => $circulars,
            'filters' => $filters
        ];
    }

    /**
     * Generate file for report
     * 
     * @param int $reportId
     * @param array $data
     * @param string $format
     * @return string
     */
    private function generateFile(int $reportId, array $data, string $format): string
    {
        $filename = 'report_' . $reportId . '_' . time() . '.' . $this->getExtension($format);
        $filePath = STORAGE_PATH . '/reports/' . $filename;

        if (!is_dir(dirname($filePath))) {
            mkdir(dirname($filePath), 0755, true);
        }

        switch ($format) {
            case 'pdf':
                $content = $this->pdfGenerator->generateDashboardReport($data);
                file_put_contents($filePath, $content);
                break;

            case 'excel':
                $this->excelExporter->setData($data['data'] ?? $data)->setFilename($filename);
                $content = $this->excelExporter->export();
                file_put_contents($filePath, $content);
                break;

            case 'csv':
                $this->excelExporter->setData($data['data'] ?? $data)->setFormat('csv')->setFilename($filename);
                $content = $this->excelExporter->export();
                file_put_contents($filePath, $content);
                break;

            case 'json':
                file_put_contents($filePath, json_encode($data, JSON_PRETTY_PRINT));
                break;

            default:
                file_put_contents($filePath, json_encode($data, JSON_PRETTY_PRINT));
        }

        return $filePath;
    }

    /**
     * Get file extension
     * 
     * @param string $format
     * @return string
     */
    private function getExtension(string $format): string
    {
        $extensions = [
            'pdf' => 'pdf',
            'excel' => 'xlsx',
            'csv' => 'csv',
            'json' => 'json'
        ];
        return $extensions[$format] ?? 'txt';
    }

    /**
     * Download report
     * 
     * @param int $reportId
     * @return array
     */
    public function download(int $reportId): array
    {
        try {
            $generated = $this->generatedReportModel->findByReportId($reportId);

            if (!$generated || !file_exists($generated->file_path)) {
                return $this->errorResponse('Report file not found.', 'FILE_NOT_FOUND');
            }

            // Increment download count
            $this->generatedReportModel->incrementDownloadCount($generated->id);

            $this->logger->info('Report downloaded', [
                'report_id' => $reportId,
                'file' => basename($generated->file_path)
            ]);

            return $this->successResponse('Report ready for download.', [
                'file_path' => $generated->file_path,
                'filename' => basename($generated->file_path),
                'file_type' => $generated->file_type
            ]);

        } catch (\Exception $e) {
            $this->logger->error('Download report error: ' . $e->getMessage());
            return $this->errorResponse('An error occurred downloading report.', 'ERROR');
        }
    }

    /**
     * Export report
     * 
     * @param int $reportId
     * @param string $format
     * @return array
     */
    public function export(int $reportId, string $format): array
    {
        try {
            if (!in_array($format, $this->exportFormats)) {
                return $this->errorResponse('Invalid export format.', 'INVALID_FORMAT');
            }

            $report = $this->reportModel->find($reportId);
            if (!$report) {
                return $this->errorResponse('Report not found.', 'REPORT_NOT_FOUND');
            }

            $generated = $this->generatedReportModel->findByReportId($reportId);
            if (!$generated) {
                return $this->errorResponse('Generated report not found.', 'GENERATED_NOT_FOUND');
            }

            // If format is same as original, return existing file
            if ($generated->file_type === $format) {
                return $this->download($reportId);
            }

            // Re-export in different format
            $data = json_decode(file_get_contents($generated->file_path), true);
            if (!$data) {
                return $this->errorResponse('Failed to read report data.', 'READ_FAILED');
            }

            $newFilePath = $this->generateFile($reportId, $data, $format);

            // Create new generated report record
            $exportData = [
                'report_id' => $reportId,
                'file_path' => $newFilePath,
                'file_type' => $format,
                'file_size' => filesize($newFilePath),
                'parameters_used' => $report->parameters,
                'generated_by' => $_SESSION['user_id'] ?? 0,
                'generated_at' => date('Y-m-d H:i:s')
            ];

            $this->generatedReportModel->create($exportData);

            $this->logger->info('Report exported', [
                'report_id' => $reportId,
                'format' => $format
            ]);

            return $this->successResponse('Report exported successfully.', [
                'file_path' => $newFilePath,
                'filename' => basename($newFilePath),
                'file_type' => $format
            ]);

        } catch (\Exception $e) {
            $this->logger->error('Export report error: ' . $e->getMessage());
            return $this->errorResponse('An error occurred exporting report.', 'ERROR');
        }
    }

    /**
     * List reports
     * 
     * @param array $filters
     * @param int $page
     * @param int $perPage
     * @return array
     */
    public function list(array $filters = [], int $page = 1, int $perPage = 15): array
    {
        try {
            $reports = $this->reportModel->getFiltered($filters, $page, $perPage);
            $total = $this->reportModel->countFiltered($filters);

            return $this->successResponse('Reports retrieved.', [
                'reports' => $reports,
                'total' => $total,
                'page' => $page,
                'per_page' => $perPage,
                'total_pages' => ceil($total / $perPage)
            ]);

        } catch (\Exception $e) {
            $this->logger->error('List reports error: ' . $e->getMessage());
            return $this->errorResponse('An error occurred.', 'ERROR');
        }
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