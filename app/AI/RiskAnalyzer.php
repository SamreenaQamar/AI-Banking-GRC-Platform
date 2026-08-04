<?php
/**
 * AI Banking GRC Platform - Risk Analyzer
 * 
 * @package    AI-Banking-GRC-Platform
 * @subpackage app/AI
 * @version    1.0.0
 * @author     GRC Platform Team
 * @copyright  2026 AI Banking GRC Platform
 * @license    Proprietary
 * 
 * This class provides AI risk analysis:
 * - Risk detection
 * - Risk classification
 * - Impact assessment
 * - Likelihood assessment
 * - Priority calculation
 * - Risk scoring
 */

declare(strict_types=1);

namespace App\AI;

use App\Libraries\Logger;
use App\Libraries\Cache;

class RiskAnalyzer
{
    /**
     * @var Logger Logger instance
     */
    private Logger $logger;

    /**
     * @var Cache Cache instance
     */
    private Cache $cache;

    /**
     * @var PromptEngine Prompt engine
     */
    private PromptEngine $promptEngine;

    /**
     * @var OpenAI OpenAI instance
     */
    private OpenAI $openAI;

    /**
     * @var array Risk categories
     */
    private array $riskCategories = [
        'operational',
        'financial',
        'compliance',
        'strategic',
        'reputational',
        'cyber',
        'credit',
        'market',
        'liquidity',
        'legal'
    ];

    /**
     * Constructor
     */
    public function __construct()
    {
        $this->logger = new Logger();
        $this->cache = new Cache();
        $this->promptEngine = new PromptEngine();
        $this->openAI = new OpenAI();
    }

    /**
     * Analyze a risk
     * 
     * @param string $description
     * @param array $context
     * @return array
     */
    public function analyze(string $description, array $context = []): array
    {
        try {
            // Check cache
            $cacheKey = 'risk_analyze_' . md5($description . json_encode($context));
            if ($this->cache->has($cacheKey)) {
                $this->logger->debug('Risk analysis from cache');
                return $this->cache->get($cacheKey);
            }

            $variables = [
                'description' => $description,
                'context' => json_encode($context)
            ];

            $messages = $this->promptEngine->build('risk_assessment', $variables);

            $result = $this->openAI->chat($messages, [
                'temperature' => 0.4,
                'max_tokens' => 3000
            });

            if (!$result['success']) {
                return $this->errorResponse('Failed to analyze risk: ' . ($result['error'] ?? 'Unknown error'));
            }

            $analysis = $this->parseRiskAnalysis($result['content']);

            // Cache results
            $this->cache->put($cacheKey, $analysis, 3600);

            return [
                'success' => true,
                'analysis' => $analysis,
                'raw' => $result['content'],
                'confidence' => 0.85,
                'timestamp' => time()
            ];

        } catch (\Exception $e) {
            $this->logger->error('Risk analysis error: ' . $e->getMessage());
            return $this->errorResponse($e->getMessage());
        }
    }

    /**
     * Classify a risk
     * 
     * @param string $description
     * @return array
     */
    public function classify(string $description): array
    {
        try {
            $prompt = "Classify the following risk into one or more categories from: " .
                      implode(', ', $this->riskCategories) .
                      "\n\nRisk: " . $description .
                      "\n\nProvide only the category names, one per line.";

            $result = $this->openAI->completion($prompt, [
                'temperature' => 0.2,
                'max_tokens' => 500
            ]);

            if (!$result['success']) {
                return $this->errorResponse('Failed to classify risk');
            }

            $categories = array_filter(array_map('trim', explode("\n", $result['content'])));

            return [
                'success' => true,
                'categories' => $categories,
                'all_categories' => $this->riskCategories,
                'timestamp' => time()
            ];

        } catch (\Exception $e) {
            $this->logger->error('Risk classification error: ' . $e->getMessage());
            return $this->errorResponse($e->getMessage());
        }
    }

    /**
     * Score a risk
     * 
     * @param string $description
     * @param array $factors
     * @return array
     */
    public function score(string $description, array $factors = []): array
    {
        try {
            $likelihood = $factors['likelihood'] ?? $this->assessLikelihood($description);
            $impact = $factors['impact'] ?? $this->assessImpact($description);
            $velocity = $factors['velocity'] ?? $this->assessVelocity($description);
            $persistence = $factors['persistence'] ?? $this->assessPersistence($description);

            $score = ($likelihood * $impact * $velocity * $persistence) / 25;

            $level = $this->getRiskLevel($score);

            return [
                'success' => true,
                'score' => round($score, 2),
                'likelihood' => $likelihood,
                'impact' => $impact,
                'velocity' => $velocity,
                'persistence' => $persistence,
                'level' => $level,
                'timestamp' => time()
            ];

        } catch (\Exception $e) {
            $this->logger->error('Risk scoring error: ' . $e->getMessage());
            return $this->errorResponse($e->getMessage());
        }
    }

    /**
     * Assess likelihood
     * 
     * @param string $description
     * @return int
     */
    private function assessLikelihood(string $description): int
    {
        $prompt = "Rate the likelihood of the following risk on a scale of 1-5 (1=Very Unlikely, 5=Very Likely).\n\nRisk: " . $description . "\n\nProvide only the number.";

        $result = $this->openAI->completion($prompt, [
            'temperature' => 0.2,
            'max_tokens' => 10
        ]);

        if ($result['success']) {
            $score = (int)trim($result['content']);
            return max(1, min(5, $score));
        }

        return 3; // Default medium likelihood
    }

    /**
     * Assess impact
     * 
     * @param string $description
     * @return int
     */
    private function assessImpact(string $description): int
    {
        $prompt = "Rate the impact of the following risk on a scale of 1-5 (1=Very Low, 5=Very High).\n\nRisk: " . $description . "\n\nProvide only the number.";

        $result = $this->openAI->completion($prompt, [
            'temperature' => 0.2,
            'max_tokens' => 10
        ]);

        if ($result['success']) {
            $score = (int)trim($result['content']);
            return max(1, min(5, $score));
        }

        return 3; // Default medium impact
    }

    /**
     * Assess velocity
     * 
     * @param string $description
     * @return int
     */
    private function assessVelocity(string $description): int
    {
        $prompt = "Rate the velocity (speed of occurrence) of the following risk on a scale of 1-5 (1=Slow, 5=Immediate).\n\nRisk: " . $description . "\n\nProvide only the number.";

        $result = $this->openAI->completion($prompt, [
            'temperature' => 0.2,
            'max_tokens' => 10
        ]);

        if ($result['success']) {
            $score = (int)trim($result['content']);
            return max(1, min(5, $score));
        }

        return 3;
    }

    /**
     * Assess persistence
     * 
     * @param string $description
     * @return int
     */
    private function assessPersistence(string $description): int
    {
        $prompt = "Rate the persistence (duration of impact) of the following risk on a scale of 1-5 (1=Short-term, 5=Long-term).\n\nRisk: " . $description . "\n\nProvide only the number.";

        $result = $this->openAI->completion($prompt, [
            'temperature' => 0.2,
            'max_tokens' => 10
        ]);

        if ($result['success']) {
            $score = (int)trim($result['content']);
            return max(1, min(5, $score));
        }

        return 3;
    }

    /**
     * Get risk level
     * 
     * @param float $score
     * @return string
     */
    private function getRiskLevel(float $score): string
    {
        if ($score >= 80) return 'critical';
        if ($score >= 60) return 'high';
        if ($score >= 40) return 'medium';
        if ($score >= 20) return 'low';
        return 'very_low';
    }

    /**
     * Parse risk analysis
     * 
     * @param string $text
     * @return array
     */
    private function parseRiskAnalysis(string $text): array
    {
        $analysis = [
            'category' => 'unknown',
            'likelihood' => 3,
            'impact' => 3,
            'score' => 0,
            'level' => 'low',
            'factors' => [],
            'mitigations' => []
        ];

        $lines = explode("\n", $text);

        foreach ($lines as $line) {
            $line = trim($line);

            if (strpos(strtolower($line), 'category') !== false) {
                $parts = explode(':', $line, 2);
                if (count($parts) === 2) {
                    $analysis['category'] = trim($parts[1]);
                }
            }

            if (strpos(strtolower($line), 'likelihood') !== false) {
                $parts = explode(':', $line, 2);
                if (count($parts) === 2) {
                    $analysis['likelihood'] = (int)trim($parts[1]);
                }
            }

            if (strpos(strtolower($line), 'impact') !== false) {
                $parts = explode(':', $line, 2);
                if (count($parts) === 2) {
                    $analysis['impact'] = (int)trim($parts[1]);
                }
            }

            if (strpos(strtolower($line), 'score') !== false) {
                $parts = explode(':', $line, 2);
                if (count($parts) === 2) {
                    $analysis['score'] = (float)trim($parts[1]);
                }
            }

            if (strpos(strtolower($line), 'level') !== false) {
                $parts = explode(':', $line, 2);
                if (count($parts) === 2) {
                    $analysis['level'] = trim(strtolower($parts[1]));
                }
            }

            if (strpos(strtolower($line), 'factor') !== false && strpos($line, '-') !== false) {
                $analysis['factors'][] = trim($line);
            }

            if (strpos(strtolower($line), 'mitigation') !== false && strpos($line, '-') !== false) {
                $analysis['mitigations'][] = trim($line);
            }
        }

        // Calculate score if not provided
        if ($analysis['score'] === 0) {
            $analysis['score'] = ($analysis['likelihood'] * $analysis['impact'] / 25) * 100;
        }

        return $analysis;
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