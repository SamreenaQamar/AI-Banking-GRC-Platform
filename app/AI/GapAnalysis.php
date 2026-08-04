<?php
/**
 * AI Banking GRC Platform - Gap Analysis Engine
 * 
 * @package    AI-Banking-GRC-Platform
 * @subpackage app/AI
 * @version    1.0.0
 * @author     GRC Platform Team
 * @copyright  2026 AI Banking GRC Platform
 * @license    Proprietary
 * 
 * This class provides AI gap analysis:
 * - Missing controls detection
 * - Missing policies detection
 * - Compliance gaps
 * - Risk gaps
 * - AI suggestions
 * - Gap prioritization
 */

declare(strict_types=1);

namespace App\AI;

use App\Libraries\Logger;
use App\Libraries\Cache;

class GapAnalysis
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
     * @var array Gap categories
     */
    private array $gapCategories = [
        'compliance',
        'risk',
        'policy',
        'control',
        'process',
        'technology',
        'resource',
        'training'
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
     * Analyze gaps
     * 
     * @param array $currentState
     * @param array $requiredState
     * @param array $options
     * @return array
     */
    public function analyze(array $currentState, array $requiredState, array $options = []): array
    {
        try {
            $cacheKey = 'gap_analysis_' . md5(json_encode($currentState) . json_encode($requiredState));
            if ($this->cache->has($cacheKey)) {
                $this->logger->debug('Gap analysis from cache');
                return $this->cache->get($cacheKey);
            }

            $variables = [
                'current' => json_encode($currentState, JSON_PRETTY_PRINT),
                'required' => json_encode($requiredState, JSON_PRETTY_PRINT)
            ];

            $messages = $this->promptEngine->build('gap_analysis', $variables, $options);

            $result = $this->openAI->chat($messages, [
                'temperature' => 0.3,
                'max_tokens' => 4000
            ]);

            if (!$result['success']) {
                return $this->errorResponse('Failed to analyze gaps: ' . ($result['error'] ?? 'Unknown error'));
            }

            $gaps = $this->parseGaps($result['content']);

            // Prioritize gaps
            $prioritized = $this->prioritizeGaps($gaps);

            $response = [
                'success' => true,
                'gaps' => $prioritized,
                'summary' => $this->generateSummary($prioritized),
                'total_gaps' => count($prioritized),
                'critical_gaps' => $this->countCritical($prioritized),
                'confidence' => 0.85,
                'timestamp' => time()
            ];

            $this->cache->put($cacheKey, $response, 3600);

            return $response;

        } catch (\Exception $e) {
            $this->logger->error('Gap analysis error: ' . $e->getMessage());
            return $this->errorResponse($e->getMessage());
        }
    }

    /**
     * Compare two states
     * 
     * @param array $state1
     * @param array $state2
     * @return array
     */
    public function compare(array $state1, array $state2): array
    {
        try {
            $differences = [];

            // Compare arrays recursively
            foreach ($state2 as $key => $value) {
                if (!isset($state1[$key])) {
                    $differences['missing'][] = $key;
                    continue;
                }

                if (is_array($value) && is_array($state1[$key])) {
                    $diff = $this->compareArrays($state1[$key], $value);
                    if (!empty($diff)) {
                        $differences[$key] = $diff;
                    }
                } elseif ($state1[$key] != $value) {
                    $differences['changed'][] = [
                        'field' => $key,
                        'old' => $state1[$key],
                        'new' => $value
                    ];
                }
            }

            // Check for extra items in state1
            foreach ($state1 as $key => $value) {
                if (!isset($state2[$key])) {
                    $differences['extra'][] = $key;
                }
            }

            return [
                'success' => true,
                'differences' => $differences,
                'has_gaps' => !empty($differences),
                'timestamp' => time()
            ];

        } catch (\Exception $e) {
            $this->logger->error('Compare error: ' . $e->getMessage());
            return $this->errorResponse($e->getMessage());
        }
    }

    /**
     * Compare arrays recursively
     * 
     * @param array $array1
     * @param array $array2
     * @return array
     */
    private function compareArrays(array $array1, array $array2): array
    {
        $diff = [];

        foreach ($array2 as $key => $value) {
            if (!isset($array1[$key])) {
                $diff['missing'][] = $key;
            } elseif (is_array($value) && is_array($array1[$key])) {
                $subDiff = $this->compareArrays($array1[$key], $value);
                if (!empty($subDiff)) {
                    $diff[$key] = $subDiff;
                }
            } elseif ($array1[$key] != $value) {
                $diff['changed'][] = [
                    'field' => $key,
                    'old' => $array1[$key],
                    'new' => $value
                ];
            }
        }

        return $diff;
    }

    /**
     * Get recommendations for gaps
     * 
     * @param array $gaps
     * @return array
     */
    public function recommend(array $gaps): array
    {
        try {
            $prompt = "Based on the following compliance gaps, provide specific recommendations:\n\n";
            $prompt .= "Gaps:\n" . json_encode($gaps, JSON_PRETTY_PRINT) . "\n\n";
            $prompt .= "Provide recommendations for each gap with priority and timeline.";

            $result = $this->openAI->completion($prompt, [
                'temperature' => 0.4,
                'max_tokens' => 3000
            ]);

            if (!$result['success']) {
                return $this->errorResponse('Failed to generate recommendations');
            }

            $recommendations = $this->parseRecommendations($result['content']);

            return [
                'success' => true,
                'recommendations' => $recommendations,
                'count' => count($recommendations),
                'timestamp' => time()
            ];

        } catch (\Exception $e) {
            $this->logger->error('Recommendations error: ' . $e->getMessage());
            return $this->errorResponse($e->getMessage());
        }
    }

    /**
     * Parse gaps from text
     * 
     * @param string $text
     * @return array
     */
    private function parseGaps(string $text): array
    {
        $gaps = [];
        $current = null;

        $lines = explode("\n", $text);

        foreach ($lines as $line) {
            $line = trim($line);
            if (empty($line)) continue;

            // Check for gap identification
            if (preg_match('/^[\d]+[\.\)]\s*(.+)/', $line, $matches)) {
                if ($current) {
                    $gaps[] = $current;
                }
                $current = [
                    'description' => $matches[1],
                    'category' => 'unknown',
                    'priority' => 'medium',
                    'recommendation' => '',
                    'impact' => 'medium'
                ];
                continue;
            }

            // Check for category
            if (strpos(strtolower($line), 'category') !== false) {
                $parts = explode(':', $line, 2);
                if (count($parts) === 2) {
                    $current['category'] = trim(strtolower($parts[1]));
                }
                continue;
            }

            // Check for priority
            if (strpos(strtolower($line), 'priority') !== false) {
                $parts = explode(':', $line, 2);
                if (count($parts) === 2) {
                    $current['priority'] = trim(strtolower($parts[1]));
                }
                continue;
            }

            // Check for recommendation
            if (strpos(strtolower($line), 'recommendation') !== false) {
                $parts = explode(':', $line, 2);
                if (count($parts) === 2) {
                    $current['recommendation'] = trim($parts[1]);
                }
                continue;
            }

            // Append to description if current
            if ($current) {
                $current['description'] .= ' ' . $line;
            }
        }

        if ($current) {
            $gaps[] = $current;
        }

        return $gaps;
    }

    /**
     * Parse recommendations from text
     * 
     * @param string $text
     * @return array
     */
    private function parseRecommendations(string $text): array
    {
        $recommendations = [];
        $lines = explode("\n", $text);

        foreach ($lines as $line) {
            $line = trim($line);
            if (empty($line)) continue;

            if (preg_match('/^[\d]+[\.\)]\s*(.+)/', $line, $matches)) {
                $recommendations[] = $matches[1];
            } elseif (preg_match('/^[\-\*]\s*(.+)/', $line, $matches)) {
                $recommendations[] = $matches[1];
            }
        }

        return $recommendations;
    }

    /**
     * Prioritize gaps
     * 
     * @param array $gaps
     * @return array
     */
    private function prioritizeGaps(array $gaps): array
    {
        $priorityOrder = ['critical' => 4, 'high' => 3, 'medium' => 2, 'low' => 1];

        usort($gaps, function($a, $b) use ($priorityOrder) {
            $aPriority = $priorityOrder[strtolower($a['priority'] ?? 'medium')] ?? 2;
            $bPriority = $priorityOrder[strtolower($b['priority'] ?? 'medium')] ?? 2;

            return $bPriority - $aPriority;
        });

        return $gaps;
    }

    /**
     * Generate summary
     * 
     * @param array $gaps
     * @return array
     */
    private function generateSummary(array $gaps): array
    {
        $summary = [
            'total' => count($gaps),
            'by_category' => [],
            'by_priority' => [
                'critical' => 0,
                'high' => 0,
                'medium' => 0,
                'low' => 0
            ]
        ];

        foreach ($gaps as $gap) {
            $category = $gap['category'] ?? 'unknown';
            if (!isset($summary['by_category'][$category])) {
                $summary['by_category'][$category] = 0;
            }
            $summary['by_category'][$category]++;

            $priority = strtolower($gap['priority'] ?? 'medium');
            if (isset($summary['by_priority'][$priority])) {
                $summary['by_priority'][$priority]++;
            }
        }

        return $summary;
    }

    /**
     * Count critical gaps
     * 
     * @param array $gaps
     * @return int
     */
    private function countCritical(array $gaps): int
    {
        $count = 0;
        foreach ($gaps as $gap) {
            if (strtolower($gap['priority'] ?? '') === 'critical') {
                $count++;
            }
        }
        return $count;
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