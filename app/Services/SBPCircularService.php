<?php
/**
 * AI Banking GRC Platform - SBP Circular Service
 * 
 * @package    AI-Banking-GRC-Platform
 * @subpackage app/Services
 * @version    1.0.0
 * @author     GRC Platform Team
 * @copyright  2026 AI Banking GRC Platform
 * @license    Proprietary
 * 
 * This service handles SBP Circular business logic:
 * - SBP Circular upload
 * - Circular parsing
 * - Compliance mapping
 * - Circular reports
 * - Circular comparison
 * - AI analysis integration
 */

declare(strict_types=1);

namespace App\Services;

use App\Models\SbpCircular;
use App\Models\ActivityLog;
use App\AI\SBPCircularAnalyzer;
use App\Libraries\Logger;
use App\Libraries\Validator;
use App\Libraries\FileManager;
use App\Libraries\Cache;

class SBPCircularService
{
    /**
     * @var SbpCircular SBP model
     */
    private SbpCircular $circularModel;

    /**
     * @var ActivityLog Activity log model
     */
    private ActivityLog $activityLogModel;

    /**
     * @var SBPCircularAnalyzer SBP analyzer
     */
    private SBPCircularAnalyzer $analyzer;

    /**
     * @var Logger Logger instance
     */
    private Logger $logger;

    /**
     * @var Validator Validator instance
     */
    private Validator $validator;

    /**
     * @var FileManager File manager instance
     */
    private FileManager $fileManager;

    /**
     * @var Cache Cache instance
     */
    private Cache $cache;

    /**
     * @var array Allowed file types
     */
    private array $allowedTypes = ['pdf', 'doc', 'docx', 'txt'];

    /**
     * @var int Max file size (10MB)
     */
    private int $maxFileSize = 10485760;

    /**
     * Constructor
     */
    public function __construct()
    {
        $this->circularModel = new SbpCircular();
        $this->activityLogModel = new ActivityLog();
        $this->analyzer = new SBPCircularAnalyzer();
        $this->logger = new Logger();
        $this->validator = new Validator();
        $this->fileManager = new FileManager();
        $this->cache = new Cache();
    }

    /**
     * Upload a new SBP circular
     * 
     * @param array $file
     * @param array $data
     * @param int $userId
     * @return array
     */
    public function upload(array $file, array $data, int $userId): array
    {
        try {
            // Validate file
            if (!isset($file['error']) || $file['error'] !== UPLOAD_ERR_OK) {
                return $this->errorResponse('File upload error.', 'UPLOAD_ERROR');
            }

            if ($file['size'] > $this->maxFileSize) {
                return $this->errorResponse('File size exceeds 10MB limit.', 'FILE_TOO_LARGE');
            }

            $extension = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
            if (!in_array($extension, $this->allowedTypes)) {
                return $this->errorResponse('Invalid file type. Allowed: ' . implode(', ', $this->allowedTypes), 'INVALID_TYPE');
            }

            // Read file content
            $content = file_get_contents($file['tmp_name']);
            if (!$content) {
                return $this->errorResponse('Failed to read file.', 'READ_FAILED');
            }

            // Analyze with AI
            $analysis = $this->analyzer->analyze($content);

            if (!$analysis['success']) {
                return $this->errorResponse('AI analysis failed: ' . ($analysis['error'] ?? 'Unknown error'), 'AI_ANALYSIS_FAILED');
            }

            // Save file
            $filename = 'sbp_' . time() . '_' . bin2hex(random_bytes(8)) . '.' . $extension;
            $uploadPath = UPLOADS_PATH . '/sbp/';

            if (!is_dir($uploadPath)) {
                mkdir($uploadPath, 0755, true);
            }

            $targetPath = $uploadPath . $filename;
            move_uploaded_file($file['tmp_name'], $targetPath);

            // Save to database
            $circularData = [
                'circular_number' => $analysis['circular']['circular_number'] ?? $data['circular_number'] ?? '',
                'title' => $analysis['circular']['title'] ?? $data['title'] ?? '',
                'description' => $analysis['summary']['summary'] ?? $data['description'] ?? '',
                'category' => $analysis['circular']['category'] ?? $data['category'] ?? 'compliance',
                'priority' => $analysis['circular']['priority'] ?? $data['priority'] ?? 'medium',
                'issuance_date' => $analysis['circular']['issuance_date'] ?? $data['issuance_date'] ?? date('Y-m-d'),
                'effective_date' => $analysis['circular']['effective_date'] ?? $data['effective_date'] ?? date('Y-m-d'),
                'compliance_deadline' => $analysis['circular']['compliance_deadline'] ?? $data['compliance_deadline'] ?? date('Y-m-d', strtotime('+90 days')),
                'document_path' => 'sbp/' . $filename,
                'document_type' => $extension,
                'ai_summary' => json_encode($analysis['summary']),
                'ai_analysis' => json_encode($analysis),
                'status' => 'pending',
                'created_by' => $userId,
                'created_at' => date('Y-m-d H:i:s')
            ];

            $circularId = $this->circularModel->create($circularData);

            if (!$circularId) {
                return $this->errorResponse('Failed to save circular.', 'SAVE_FAILED');
            }

            // Log activity
            $this->activityLogModel->logCreate($userId, 'sbp', 'circular', $circularId, $circularData);

            $this->logger->info('SBP Circular uploaded', [
                'circular_id' => $circularId,
                'circular_number' => $circularData['circular_number'],
                'user_id' => $userId
            ]);

            return $this->successResponse('SBP Circular uploaded successfully.', [
                'circular_id' => $circularId,
                'analysis' => $analysis
            ]);

        } catch (\Exception $e) {
            $this->logger->error('Upload SBP circular error: ' . $e->getMessage());
            return $this->errorResponse('An error occurred uploading circular.', 'ERROR');
        }
    }

    /**
     * Parse circular content
     * 
     * @param string $content
     * @return array
     */
    public function parse(string $content): array
    {
        try {
            $result = $this->analyzer->parse($content);

            return $this->successResponse('Circular parsed successfully.', [
                'parsed' => $result
            ]);

        } catch (\Exception $e) {
            $this->logger->error('Parse circular error: ' . $e->getMessage());
            return $this->errorResponse('An error occurred parsing circular.', 'ERROR');
        }
    }

    /**
     * Analyze circular
     * 
     * @param int $circularId
     * @return array
     */
    public function analyze(int $circularId): array
    {
        try {
            $circular = $this->circularModel->find($circularId);
            if (!$circular) {
                return $this->errorResponse('Circular not found.', 'CIRCULAR_NOT_FOUND');
            }

            // Get file content
            $filePath = UPLOADS_PATH . '/' . $circular->document_path;
            if (!file_exists($filePath)) {
                return $this->errorResponse('File not found.', 'FILE_NOT_FOUND');
            }

            $content = file_get_contents($filePath);
            if (!$content) {
                return $this->errorResponse('Failed to read file.', 'READ_FAILED');
            }

            // Analyze
            $analysis = $this->analyzer->analyze($content);

            if (!$analysis['success']) {
                return $this->errorResponse('AI analysis failed.', 'AI_ANALYSIS_FAILED');
            }

            // Update circular
            $this->circularModel->update($circularId, [
                'ai_summary' => json_encode($analysis['summary']),
                'ai_analysis' => json_encode($analysis),
                'ai_analyzed_at' => date('Y-m-d H:i:s')
            ]);

            $this->logger->info('Circular analyzed', [
                'circular_id' => $circularId,
                'analysis_version' => '1.0'
            ]);

            return $this->successResponse('Circular analyzed successfully.', [
                'analysis' => $analysis
            ]);

        } catch (\Exception $e) {
            $this->logger->error('Analyze circular error: ' . $e->getMessage());
            return $this->errorResponse('An error occurred analyzing circular.', 'ERROR');
        }
    }

    /**
     * Compare circulars
     * 
     * @param int $circularId1
     * @param int $circularId2
     * @return array
     */
    public function compare(int $circularId1, int $circularId2): array
    {
        try {
            $circular1 = $this->circularModel->find($circularId1);
            $circular2 = $this->circularModel->find($circularId2);

            if (!$circular1 || !$circular2) {
                return $this->errorResponse('One or both circulars not found.', 'CIRCULAR_NOT_FOUND');
            }

            // Get file contents
            $file1 = UPLOADS_PATH . '/' . $circular1->document_path;
            $file2 = UPLOADS_PATH . '/' . $circular2->document_path;

            $content1 = file_exists($file1) ? file_get_contents($file1) : '';
            $content2 = file_exists($file2) ? file_get_contents($file2) : '';

            // Compare using AI
            $comparison = $this->analyzer->compare($content1, $content2);

            return $this->successResponse('Circulars compared.', [
                'comparison' => $comparison,
                'circular_1' => $circular1->circular_number,
                'circular_2' => $circular2->circular_number
            ]);

        } catch (\Exception $e) {
            $this->logger->error('Compare circulars error: ' . $e->getMessage());
            return $this->errorResponse('An error occurred comparing circulars.', 'ERROR');
        }
    }

    /**
     * Generate circular report
     * 
     * @param array $filters
     * @return array
     */
    public function report(array $filters = []): array
    {
        try {
            $circulars = $this->circularModel->getFiltered($filters, 1, 1000);
            $stats = $this->getCircularStats();

            return [
                'success' => true,
                'data' => $circulars,
                'statistics' => $stats,
                'generated_at' => date('Y-m-d H:i:s'),
                'count' => count($circulars)
            ];

        } catch (\Exception $e) {
            $this->logger->error('Circular report error: ' . $e->getMessage());
            return $this->errorResponse('Failed to generate report.', 'ERROR');
        }
    }

    /**
     * Get circular statistics
     * 
     * @return array
     */
    private function getCircularStats(): array
    {
        return [
            'total' => $this->circularModel->countAll(),
            'active' => $this->circularModel->countByStatus('active'),
            'pending' => $this->circularModel->countByStatus('pending'),
            'implemented' => $this->circularModel->countByStatus('implemented'),
            'by_category' => $this->circularModel->countByCategory(),
            'by_priority' => $this->circularModel->countByPriority(),
            'compliance_rate' => $this->circularModel->getComplianceRate()
        ];
    }

    /**
     * Implement circular
     * 
     * @param int $circularId
     * @param int $userId
     * @param string $notes
     * @return array
     */
    public function implement(int $circularId, int $userId, string $notes = ''): array
    {
        try {
            $circular = $this->circularModel->find($circularId);
            if (!$circular) {
                return $this->errorResponse('Circular not found.', 'CIRCULAR_NOT_FOUND');
            }

            $result = $this->circularModel->update($circularId, [
                'status' => 'implemented',
                'implemented_by' => $userId,
                'implementation_date' => date('Y-m-d'),
                'implementation_notes' => $notes,
                'updated_at' => date('Y-m-d H:i:s')
            ]);

            if (!$result) {
                return $this->errorResponse('Failed to implement circular.', 'IMPLEMENT_FAILED');
            }

            // Log activity
            $this->activityLogModel->logAction($userId, 'sbp_implement', 'sbp',
                "Circular {$circular->circular_number} marked as implemented");

            $this->logger->info('Circular implemented', [
                'circular_id' => $circularId,
                'user_id' => $userId
            ]);

            return $this->successResponse('Circular marked as implemented.');

        } catch (\Exception $e) {
            $this->logger->error('Implement circular error: ' . $e->getMessage());
            return $this->errorResponse('An error occurred implementing circular.', 'ERROR');
        }
    }

    /**
     * Get circular by ID
     * 
     * @param int $circularId
     * @return array
     */
    public function find(int $circularId): array
    {
        try {
            $circular = $this->circularModel->find($circularId);
            if (!$circular) {
                return $this->errorResponse('Circular not found.', 'CIRCULAR_NOT_FOUND');
            }

            return $this->successResponse('Circular retrieved.', [
                'circular' => $circular
            ]);

        } catch (\Exception $e) {
            $this->logger->error('Find circular error: ' . $e->getMessage());
            return $this->errorResponse('An error occurred.', 'ERROR');
        }
    }

    /**
     * Get all circulars with pagination
     * 
     * @param array $filters
     * @param int $page
     * @param int $perPage
     * @return array
     */
    public function all(array $filters = [], int $page = 1, int $perPage = 15): array
    {
        try {
            $circulars = $this->circularModel->getFiltered($filters, $page, $perPage);
            $total = $this->circularModel->countFiltered($filters);

            return $this->successResponse('Circulars retrieved.', [
                'circulars' => $circulars,
                'total' => $total,
                'page' => $page,
                'per_page' => $perPage,
                'total_pages' => ceil($total / $perPage)
            ]);

        } catch (\Exception $e) {
            $this->logger->error('Get all circulars error: ' . $e->getMessage());
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