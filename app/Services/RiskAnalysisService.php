<?php
/**
 * AI Banking GRC Platform - Risk Analysis Service
 * 
 * @package    AI-Banking-GRC-Platform
 * @subpackage app/Services
 * @version    1.0.0
 * @author     GRC Platform Team
 * @copyright  2026 AI Banking GRC Platform
 * @license    Proprietary
 * 
 * This service handles risk analysis business logic:
 * - Risk analysis
 * - Risk matrix
 * - Risk probability
 * - Risk impact
 * - Risk recommendations
 * - Risk heatmap
 */

declare(strict_types=1);

namespace App\Services;

use App\AI\RiskAnalyzer;
use App\AI\RiskHeatmap;
use App\Models\RiskRegister;
use App\Models\ActivityLog;
use App\Libraries\Logger;
use App\Libraries\Validator;
use App\Libraries\Cache;

class RiskAnalysisService
{
    /**
     * @var RiskAnalyzer Risk analyzer
     */
    private RiskAnalyzer $riskAnalyzer;

    /**
     * @var RiskHeatmap Risk heatmap
     */
    private RiskHeatmap $riskHeatmap;

    /**
     * @var RiskRegister Risk model
     */
    private RiskRegister $riskModel;

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
        $this->riskAnalyzer = new RiskAnalyzer();
        $this->riskHeatmap = new RiskHeatmap();
        $this->riskModel = new RiskRegister();
        $this->activityLogModel = new ActivityLog();
        $this->logger = new Logger();
        $this->validator = new Validator();
        $this->cache = new Cache();
    }

    /**
     * Analyze risk
     * 
     * @param string $description
     * @param array $context
     * @param int $userId
     * @return array
     */
    public function analyze(string $description, array $context, int $userId): array
    {
        try {
            $cacheKey = 'risk_analysis_' . md5($description . json_encode($context));
            if ($this->cache->has($cacheKey)) {
                return $this->cache->get($cacheKey);
            }

            $result = $this->riskAnalyzer->analyze($description, $context);

            if (!$result['success']) {
                return $this->errorResponse('Risk analysis failed: ' . ($result['error'] ?? 'Unknown error'));
            }

            $this->cache->put($cacheKey, $result, 3600);

            $this->activityLogModel->logAction($userId, 'risk_analyze', 'risk',
                "Analyzed risk: " . substr($description, 0, 50));

            return $result;

        } catch (\Exception $e) {
            $this->logger->error('Risk analysis error: ' . $e->getMessage());
            return $this->errorResponse('An error occurred during risk analysis.');
        }
    }

    /**
     * Score risk
     * 
     * @param string $description
     * @param array $factors
     * @param int $userId
     * @return array
     */
    public function score(string $description, array $factors, int $userId): array
    {
        try {
            $result = $this->riskAnalyzer->score($description, $factors);

            if (!$result['success']) {
                return $this->errorResponse('Risk scoring failed: ' . ($result['error'] ?? 'Unknown error'));
            }

            $this->activityLogModel->logAction($userId, 'risk_score', 'risk',
                "Scored risk: " . substr($description, 0, 50));

            return $result;

        } catch (\Exception $e) {
            $this->logger->error('Risk scoring error: ' . $e->getMessage());
            return $this->errorResponse('An error occurred during risk scoring.');
        }
    }

    /**
     * Classify risk
     * 
     * @param string $description
     * @param int $userId
     * @return array
     */
    public function classify(string $description, int $userId): array
    {
        try {
            $result = $this->riskAnalyzer->classify($description);

            if (!$result['success']) {
                return $this->errorResponse('Risk classification failed: ' . ($result['error'] ?? 'Unknown error'));
            }

            $this->activityLogModel->logAction($userId, 'risk_classify', 'risk',
                "Classified risk: " . substr($description, 0, 50));

            return $result;

        } catch (\Exception $e) {
            $this->logger->error('Risk classification error: ' . $e->getMessage());
            return $this->errorResponse('An error occurred during risk classification.');
        }
    }

    /**
     * Generate risk heatmap
     * 
     * @param array $filters
     * @param int $userId
     * @return array
     */
    public function heatmap(array $filters, int $userId): array
    {
        try {
            // Get risks
            $risks = $this->riskModel->getFiltered($filters, 1, 1000);
            $riskData = [];

            foreach ($risks as $risk) {
                $riskData[] = [
                    'id' => $risk->id,
                    'title' => $risk->title,
                    'likelihood' => $risk->inherent_likelihood ?? 3,
                    'impact' => $risk->inherent_impact ?? 3,
                    'score' => $risk->inherent_risk_score ?? 0,
                    'level' => $risk->risk_level ?? 'medium'
                ];
            }

            $heatmap = $this->riskHeatmap->generate($riskData);

            $this->activityLogModel->logAction($userId, 'risk_heatmap', 'risk',
                "Generated risk heatmap with " . count($riskData) . " risks");

            return [
                'success' => true,
                'heatmap' => $heatmap,
                'risks' => $riskData,
                'count' => count($riskData),
                'generated_at' => date('Y-m-d H:i:s')
            ];

        } catch (\Exception $e) {
            $this->logger->error('Risk heatmap error: ' . $e->getMessage());
            return $this->errorResponse('An error occurred generating risk heatmap.');
        }
    }

    /**
     * Get risk matrix
     * 
     * @return array
     */
    public function matrix(): array
    {
        try {
            $risks = $this->riskModel->getAll(1, 1000);
            $riskData = [];

            foreach ($risks as $risk) {
                $riskData[] = [
                    'likelihood' => $risk->inherent_likelihood ?? 3,
                    'impact' => $risk->inherent_impact ?? 3,
                    'score' => $risk->inherent_risk_score ?? 0
                ];
            }

            $matrix = $this->riskHeatmap->matrix($riskData);

            return [
                'success' => true,
                'matrix' => $matrix,
                'generated_at' => date('Y-m-d H:i:s')
            ];

        } catch (\Exception $e) {
            $this->logger->error('Risk matrix error: ' . $e->getMessage());
            return $this->errorResponse('An error occurred generating risk matrix.');
        }
    }

    /**
     * Get risk recommendations
     * 
     * @param int $riskId
     * @param int $userId
     * @return array
     */
    public function getRecommendations(int $riskId, int $userId): array
    {
        try {
            $risk = $this->riskModel->find($riskId);
            if (!$risk) {
                return $this->errorResponse('Risk not found.');
            }

            $description = $risk->title . "\n" . $risk->description;
            $context = [
                'likelihood' => $risk->inherent_likelihood ?? 3,
                'impact' => $risk->inherent_impact ?? 3,
                'category' => $risk->category_id,
                'department' => $risk->owner_department_id
            ];

            $analysis = $this->riskAnalyzer->analyze($description, $context);

            if (!$analysis['success']) {
                return $this->errorResponse('Failed to generate recommendations.');
            }

            $recommendations = $analysis['analysis']['mitigations'] ?? [];

            return [
                'success' => true,
                'recommendations' => $recommendations,
                'risk' => $risk,
                'generated_at' => date('Y-m-d H:i:s')
            ];

        } catch (\Exception $e) {
            $this->logger->error('Risk recommendations error: ' . $e->getMessage());
            return $this->errorResponse('An error occurred generating risk recommendations.');
        }
    }

    /**
     * Get risk dashboard data
     * 
     * @return array
     */
    public function getDashboardData(): array
    {
        try {
            $risks = $this->riskModel->getAll(1, 1000);
            $riskData = [];

            foreach ($risks as $risk) {
                $riskData[] = [
                    'id' => $risk->id,
                    'title' => $risk->title,
                    'likelihood' => $risk->inherent_likelihood ?? 3,
                    'impact' => $risk->inherent_impact ?? 3,
                    'score' => $risk->inherent_risk_score ?? 0,
                    'level' => $risk->risk_level ?? 'medium'
                ];
            }

            $dashboardData = $this->riskHeatmap->getDashboardData($riskData);

            return [
                'success' => true,
                'data' => $dashboardData,
                'total_risks' => count($riskData),
                'generated_at' => date('Y-m-d H:i:s')
            ];

        } catch (\Exception $e) {
            $this->logger->error('Risk dashboard data error: ' . $e->getMessage());
            return $this->errorResponse('An error occurred loading risk dashboard data.');
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