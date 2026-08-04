<?php
/**
 * AI Banking GRC Platform - Recommendation Engine
 * 
 * @package    AI-Banking-GRC-Platform
 * @subpackage app/AI
 * @version    1.0.0
 * @author     GRC Platform Team
 * @copyright  2026 AI Banking GRC Platform
 * @license    Proprietary
 * 
 * This class provides AI recommendations:
 * - Risk recommendations
 * - Compliance recommendations
 * - Policy recommendations
 * - Audit recommendations
 * - Security recommendations
 * - Control recommendations
 */

declare(strict_types=1);

namespace App\AI;

use App\Libraries\Logger;
use App\Libraries\Cache;

class RecommendationEngine
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
     * @var array Recommendation weights
     */
    private array $weights = [
        'risk' => 1.0,
        'compliance' => 1.0,
        'policy' => 0.8,
        'audit' => 0.9,
        'security' => 1.0,
        'control' => 0.8
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
     * Generate recommendations
     * 
     * @param string $type
     * @param array $data
     * @param array $options
     * @return array
     */
    public function recommend(string $type, array $data, array $options = []): array
    {
        try {
            $cacheKey = 'recommend_' . $type . '_' . md5(json_encode($data));
            if ($this->cache->has($cacheKey)) {
                $this->logger->debug('Recommendations from cache');
                return $this->cache->get($cacheKey);
            }

            $prompt = $this->buildRecommendationPrompt($type, $data, $options);

            $result = $this->openAI->completion($prompt, [
                'temperature' => 0.5,
                'max_tokens' => 3000
            ]);

            if (!$result['success']) {
                return $this->errorResponse('Failed to generate recommendations: ' . ($result['error'] ?? 'Unknown error'));
            }

            $recommendations = $this->parseRecommendations($result['content'], $type);

            // Rank recommendations
            $ranked = $this->rank($recommendations, $type);

            // Score recommendations
            $scored = $this->score($ranked, $type);

            $response = [
                'success' => true,
                'recommendations' => $scored,
                'type' => $type,
                'count' => count($scored),
                'confidence' => 0.8,
                'timestamp' => time()
            ];

            // Cache results
            $this->cache->put($cacheKey, $response, 3600);

            return $response;

        } catch (\Exception $e) {
            $this->logger->error('Recommendation error: ' . $e->getMessage());
            return $this->errorResponse($e->getMessage());
        }
    }

    /**
     * Build recommendation prompt
     * 
     * @param string $type
     * @param array $data
     * @param array $options
     * @return string
     */
    private function buildRecommendationPrompt(string $type, array $data, array $options): string
    {
        $typeLabels = [
            'risk' => 'Risk Management',
            'compliance' => 'Compliance',
            'policy' => 'Policy Development',
            'audit' => 'Audit',
            'security' => 'Security',
            'control' => 'Internal Controls'
        ];

        $label = $typeLabels[$type] ?? ucfirst($type);

        $prompt = "You are a banking " . strtolower($label) . " expert. ";
        $prompt .= "Provide specific, actionable recommendations based on the following data.\n\n";

        $prompt .= "Data:\n" . json_encode($data, JSON_PRETTY_PRINT) . "\n\n";

        if (!empty($options['context'])) {
            $prompt .= "Context:\n" . $options['context'] . "\n\n";
        }

        if (!empty($options['constraints'])) {
            $prompt .= "Constraints:\n" . $options['constraints'] . "\n\n";
        }

        $prompt .= "Provide recommendations in the following format:\n";
        $prompt .= "1. Title: [Recommendation title]\n";
        $prompt .= "   Description: [Detailed description]\n";
        $prompt .= "   Priority: [High|Medium|Low]\n";
        $prompt .= "   Impact: [High|Medium|Low]\n";
        $prompt .= "   Effort: [High|Medium|Low]\n\n";

        $prompt .= "Provide 5-10 recommendations.";

        return $prompt;
    }

    /**
     * Parse recommendations from text
     * 
     * @param string $text
     * @param string $type
     * @return array
     */
    private function parseRecommendations(string $text, string $type): array
    {
        $recommendations = [];
        $current = null;

        $lines = explode("\n", $text);

        foreach ($lines as $line) {
            $line = trim($line);
            if (empty($line)) continue;

            // Check for new recommendation
            if (preg_match('/^[\d]+[\.\)]\s*Title:\s*(.+)/i', $line, $matches)) {
                if ($current) {
                    $recommendations[] = $current;
                }
                $current = [
                    'title' => $matches[1],
                    'description' => '',
                    'priority' => 'medium',
                    'impact' => 'medium',
                    'effort' => 'medium',
                    'type' => $type
                ];
                continue;
            }

            // Check for description
            if (preg_match('/^Description:\s*(.+)/i', $line, $matches)) {
                if ($current) {
                    $current['description'] = $matches[1];
                }
                continue;
            }

            // Check for priority
            if (preg_match('/^Priority:\s*(.+)/i', $line, $matches)) {
                if ($current) {
                    $current['priority'] = strtolower(trim($matches[1]));
                }
                continue;
            }

            // Check for impact
            if (preg_match('/^Impact:\s*(.+)/i', $line, $matches)) {
                if ($current) {
                    $current['impact'] = strtolower(trim($matches[1]));
                }
                continue;
            }

            // Check for effort
            if (preg_match('/^Effort:\s*(.+)/i', $line, $matches)) {
                if ($current) {
                    $current['effort'] = strtolower(trim($matches[1]));
                }
                continue;
            }

            // Append to description if current
            if ($current) {
                $current['description'] .= ' ' . $line;
            }
        }

        // Add last recommendation
        if ($current) {
            $recommendations[] = $current;
        }

        return $recommendations;
    }

    /**
     * Rank recommendations
     * 
     * @param array $recommendations
     * @param string $type
     * @return array
     */
    private function rank(array $recommendations, string $type): array
    {
        $priorityOrder = ['critical' => 4, 'high' => 3, 'medium' => 2, 'low' => 1];
        $weight = $this->weights[$type] ?? 1.0;

        usort($recommendations, function($a, $b) use ($priorityOrder, $weight) {
            $aPriority = $priorityOrder[strtolower($a['priority'] ?? 'medium')] ?? 2;
            $bPriority = $priorityOrder[strtolower($b['priority'] ?? 'medium')] ?? 2;

            return ($bPriority - $aPriority) * $weight;
        });

        return $recommendations;
    }

    /**
     * Score recommendations
     * 
     * @param array $recommendations
     * @param string $type
     * @return array
     */
    private function score(array $recommendations, string $type): array
    {
        $priorityScores = ['critical' => 100, 'high' => 80, 'medium' => 60, 'low' => 40];
        $impactScores = ['high' => 90, 'medium' => 70, 'low' => 50];
        $effortScores = ['low' => 80, 'medium' => 60, 'high' => 40];

        foreach ($recommendations as &$rec) {
            $priority = strtolower($rec['priority'] ?? 'medium');
            $impact = strtolower($rec['impact'] ?? 'medium');
            $effort = strtolower($rec['effort'] ?? 'medium');

            $score = ($priorityScores[$priority] ?? 60) * 0.4;
            $score += ($impactScores[$impact] ?? 70) * 0.35;
            $score += ($effortScores[$effort] ?? 60) * 0.25;

            $rec['score'] = round($score, 2);
            $rec['weight'] = $this->weights[$type] ?? 1.0;
        }

        return $recommendations;
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