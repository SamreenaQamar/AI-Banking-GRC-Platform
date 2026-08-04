<?php
/**
 * AI Banking GRC Platform - Compliance Service
 * 
 * @package    AI-Banking-GRC-Platform
 * @subpackage app/Services
 * @version    1.0.0
 * @author     GRC Platform Team
 * @copyright  2026 AI Banking GRC Platform
 * @license    Proprietary
 * 
 * This service handles compliance business logic:
 * - Compliance checking
 * - Compliance dashboard
 * - Compliance scoring
 * - Compliance reports
 * - Gap analysis
 * - Recommendations
 */

declare(strict_types=1);

namespace App\Services;

use App\Models\ComplianceTask;
use App\Models\ComplianceCategory;
use App\Models\ComplianceFramework;
use App\Models\ComplianceEvidence;
use App\Models\ActivityLog;
use App\Libraries\Logger;
use App\Libraries\Validator;
use App\Libraries\Cache;

class ComplianceService
{
    /**
     * @var ComplianceTask Task model
     */
    private ComplianceTask $taskModel;

    /**
     * @var ComplianceCategory Category model
     */
    private ComplianceCategory $categoryModel;

    /**
     * @var ComplianceFramework Framework model
     */
    private ComplianceFramework $frameworkModel;

    /**
     * @var ComplianceEvidence Evidence model
     */
    private ComplianceEvidence $evidenceModel;

    /**
     * @var ActivityLog Activity log model
     */
    private ActivityLog $activityLogModel;

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
     * Constructor
     */
    public function __construct()
    {
        $this->taskModel = new ComplianceTask();
        $this->categoryModel = new ComplianceCategory();
        $this->frameworkModel = new ComplianceFramework();
        $this->evidenceModel = new ComplianceEvidence();
        $this->activityLogModel = new ActivityLog();
        $this->logger = new Logger();
        $this->validator = new Validator();
        $this->cache = new Cache();
    }

    /**
     * Check compliance status
     * 
     * @param int $taskId
     * @return array
     */
    public function check(int $taskId): array
    {
        try {
            $task = $this->taskModel->find($taskId);
            if (!$task) {
                return $this->errorResponse('Compliance task not found.');
            }

            $status = $this->determineStatus($task);
            $score = $this->calculateScore($task);
            $gaps = $this->identifyGaps($task);

            return [
                'success' => true,
                'task' => $task,
                'status' => $status,
                'score' => $score,
                'gaps' => $gaps,
                'timestamp' => time()
            ];

        } catch (\Exception $e) {
            $this->logger->error('Compliance check error: ' . $e->getMessage());
            return $this->errorResponse('Failed to check compliance.');
        }
    }

    /**
     * Get compliance score
     * 
     * @param int|null $frameworkId
     * @return array
     */
    public function score(?int $frameworkId = null): array
    {
        try {
            $cacheKey = 'compliance_score_' . ($frameworkId ?? 'all');
            if ($this->cache->has($cacheKey)) {
                return $this->cache->get($cacheKey);
            }

            $total = $this->taskModel->countAll();
            $completed = $this->taskModel->countByStatus('completed');
            $inProgress = $this->taskModel->countByStatus('in_progress');
            $overdue = $this->taskModel->countOverdue();

            $score = [
                'total' => $total,
                'completed' => $completed,
                'in_progress' => $inProgress,
                'overdue' => $overdue,
                'completion_rate' => $total > 0 ? round(($completed / $total) * 100, 2) : 0,
                'health_status' => $this->getHealthStatus($completed, $total, $overdue)
            ];

            $this->cache->put($cacheKey, $score, 300);

            return $score;

        } catch (\Exception $e) {
            $this->logger->error('Compliance score error: ' . $e->getMessage());
            return $this->errorResponse('Failed to calculate compliance score.');
        }
    }

    /**
     * Get compliance summary
     * 
     * @param int $userId
     * @return array
     */
    public function summary(int $userId): array
    {
        try {
            $total = $this->taskModel->countAll();
            $assigned = $this->taskModel->countUserTasks($userId);
            $completed = $this->taskModel->countUserTasks($userId, 'completed');
            $overdue = $this->taskModel->countUserOverdueTasks($userId);

            $frameworks = $this->frameworkModel->getAll();
            $frameworkSummary = [];

            foreach ($frameworks as $framework) {
                $frameworkTotal = $this->taskModel->countByFramework($framework->id);
                $frameworkCompleted = $this->taskModel->countByFrameworkAndStatus($framework->id, 'completed');
                
                $frameworkSummary[] = [
                    'id' => $framework->id,
                    'name' => $framework->name,
                    'total' => $frameworkTotal,
                    'completed' => $frameworkCompleted,
                    'rate' => $frameworkTotal > 0 ? round(($frameworkCompleted / $frameworkTotal) * 100, 2) : 0
                ];
            }

            return [
                'success' => true,
                'summary' => [
                    'total_tasks' => $total,
                    'assigned_tasks' => $assigned,
                    'completed_tasks' => $completed,
                    'overdue_tasks' => $overdue,
                    'completion_rate' => $total > 0 ? round(($completed / $total) * 100, 2) : 0,
                    'frameworks' => $frameworkSummary,
                    'status_distribution' => $this->getStatusDistribution()
                ],
                'timestamp' => time()
            ];

        } catch (\Exception $e) {
            $this->logger->error('Compliance summary error: ' . $e->getMessage());
            return $this->errorResponse('Failed to load compliance summary.');
        }
    }

    /**
     * Get compliance report
     * 
     * @param array $filters
     * @return array
     */
    public function report(array $filters = []): array
    {
        try {
            $tasks = $this->taskModel->getFiltered($filters, 1, 1000);
            $stats = $this->score();

            return [
                'success' => true,
                'data' => $tasks,
                'statistics' => $stats,
                'generated_at' => date('Y-m-d H:i:s'),
                'count' => count($tasks)
            ];

        } catch (\Exception $e) {
            $this->logger->error('Compliance report error: ' . $e->getMessage());
            return $this->errorResponse('Failed to generate compliance report.');
        }
    }

    /**
     * Get status distribution
     * 
     * @return array
     */
    private function getStatusDistribution(): array
    {
        $statuses = ['pending', 'in_progress', 'completed', 'overdue', 'cancelled'];
        $distribution = [];

        foreach ($statuses as $status) {
            $distribution[$status] = $this->taskModel->countByStatus($status);
        }

        return $distribution;
    }

    /**
     * Determine compliance status
     * 
     * @param object $task
     * @return string
     */
    private function determineStatus(object $task): string
    {
        if ($task->status === 'completed') {
            return 'compliant';
        }

        if (strtotime($task->due_date) < time()) {
            return 'non_compliant';
        }

        if ($task->status === 'in_progress') {
            return 'partial';
        }

        return 'pending';
    }

    /**
     * Calculate compliance score
     * 
     * @param object $task
     * @return float
     */
    private function calculateScore(object $task): float
    {
        // Base score from compliance_score field
        $score = $task->compliance_score ?? 0;

        // Adjust based on status
        if ($task->status === 'completed') {
            $score = max($score, 80);
        } elseif ($task->status === 'in_progress') {
            $score = max($score, 50);
        } elseif (strtotime($task->due_date) < time()) {
            $score = min($score, 30);
        }

        return min(100, max(0, $score));
    }

    /**
     * Identify compliance gaps
     * 
     * @param object $task
     * @return array
     */
    private function identifyGaps(object $task): array
    {
        $gaps = [];

        // Check if evidence is required but missing
        if ($task->evidence_required) {
            $evidenceCount = $this->evidenceModel->countByTask($task->id);
            if ($evidenceCount == 0) {
                $gaps[] = 'Missing required evidence';
            }
        }

        // Check if overdue
        if (strtotime($task->due_date) < time() && $task->status !== 'completed') {
            $gaps[] = 'Task is overdue';
        }

        // Check if review is required
        if ($task->auto_review && $task->status === 'completed') {
            if (!$task->reviewed_by) {
                $gaps[] = 'Pending review';
            }
        }

        return $gaps;
    }

    /**
     * Get health status
     * 
     * @param int $completed
     * @param int $total
     * @param int $overdue
     * @return string
     */
    private function getHealthStatus(int $completed, int $total, int $overdue): string
    {
        $rate = $total > 0 ? ($completed / $total) * 100 : 0;

        if ($rate >= 80 && $overdue == 0) {
            return 'excellent';
        } elseif ($rate >= 60 && $overdue < 3) {
            return 'good';
        } elseif ($rate >= 40 || $overdue < 5) {
            return 'fair';
        } else {
            return 'poor';
        }
    }

    /**
     * Error response
     * 
     * @param string $message
     * @return array
     */
    private function errorResponse(string $message): array
    {
        return [
            'success' => false,
            'error' => $message,
            'timestamp' => time()
        ];
    }
}