<?php
/**
 * AI Banking GRC Platform - SBP Circular Analyzer
 * 
 * @package    AI-Banking-GRC-Platform
 * @subpackage app/AI
 * @version    1.0.0
 * @author     GRC Platform Team
 * @copyright  2026 AI Banking GRC Platform
 * @license    Proprietary
 * 
 * This class provides SBP Circular analysis:
 * - SBP Circular upload and parsing
 * - Circular parsing
 * - Compliance mapping
 * - AI summary
 * - Regulation comparison
 * - Impact analysis
 * - Implementation recommendations
 */

declare(strict_types=1);

namespace App\AI;

use App\Libraries\Logger;
use App\Libraries\Cache;
use App\Libraries\FileManager;
use App\Libraries\Database;

class SBPCircularAnalyzer
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
     * @var FileManager File manager instance
     */
    private FileManager $fileManager;

    /**
     * @var Database Database instance
     */
    private Database $db;

    /**
     * @var PromptEngine Prompt engine
     */
    private PromptEngine $promptEngine;

    /**
     * @var OpenAI OpenAI instance
     */
    private OpenAI $openAI;

    /**
     * @var array SBP circular categories
     */
    private array $categories = [
        'prudential' => 'Prudential Regulations',
        'operational' => 'Operational Guidelines',
        'compliance' => 'Compliance Requirements',
        'risk' => 'Risk Management',
        'governance' => 'Corporate Governance',
        'reporting' => 'Reporting Requirements',
        'aml' => 'AML/CFT',
        'consumer' => 'Consumer Protection'
    ];

    /**
     * @var array Circular priorities
     */
    private array $priorities = [
        'critical' => 4,
        'high' => 3,
        'medium' => 2,
        'low' => 1
    ];

    /**
     * Constructor
     */
    public function __construct()
    {
        $this->logger = new Logger();
        $this->cache = new Cache();
        $this->fileManager = new FileManager();
        $this->db = Database::getInstance();
        $this->promptEngine = new PromptEngine();
        $this->openAI = new OpenAI();
    }

    /**
     * Analyze an SBP circular
     * 
     * @param string $circularText
     * @param array $options
     * @return array
     */
    public function analyze(string $circularText, array $options = []): array
    {
        try {
            $cacheKey = 'sbp_analyze_' . md5($circularText);
            if ($this->cache->has($cacheKey)) {
                $this->logger->debug('SBP circular analysis from cache');
                return $this->cache->get($cacheKey);
            }

            // Parse circular
            $parsed = $this->parse($circularText);

            // Generate summary
            $summary = $this->summary($parsed, $options);

            // Compare with regulations
            $comparison = $this->compare($parsed, $options);

            // Generate recommendations
            $recommendations = $this->recommend($parsed, $comparison);

            // Determine impact
            $impact = $this->assessImpact($parsed, $comparison);

            $result = [
                'success' => true,
                'circular' => $parsed,
                'summary' => $summary,
                'comparison' => $comparison,
                'recommendations' => $recommendations,
                'impact_analysis' => $impact,
                'compliance_score' => $this->calculateComplianceScore($parsed, $comparison),
                'confidence' => 0.85,
                'timestamp' => time()
            ];

            $this->cache->put($cacheKey, $result, 7200);

            return $result;

        } catch (\Exception $e) {
            $this->logger->error('SBP circular analysis error: ' . $e->getMessage());
            return $this->errorResponse($e->getMessage());
        }
    }

    /**
     * Parse circular text
     * 
     * @param string $text
     * @return array
     */
    public function parse(string $text): array
    {
        try {
            $prompt = "Parse the following SBP circular and extract key information:\n\n";
            $prompt .= $text . "\n\n";
            $prompt .= "Extract:\n";
            $prompt .= "1. Circular Number\n";
            $prompt .= "2. Title\n";
            $prompt .= "3. Issuance Date\n";
            $prompt .= "4. Effective Date\n";
            $prompt .= "5. Category\n";
            $prompt .= "6. Priority\n";
            $prompt .= "7. Key Requirements\n";
            $prompt .= "8. Applicable Departments\n";
            $prompt .= "9. Compliance Deadline\n";
            $prompt .= "10. Summary\n";

            $result = $this->openAI->completion($prompt, [
                'temperature' => 0.2,
                'max_tokens' => 3000
            ]);

            if (!$result['success']) {
                throw new \RuntimeException('Failed to parse circular: ' . ($result['error'] ?? 'Unknown error'));
            }

            return $this->parseResponse($result['content']);

        } catch (\Exception $e) {
            $this->logger->error('Parse error: ' . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Parse AI response
     * 
     * @param string $response
     * @return array
     */
    private function parseResponse(string $response): array
    {
        $parsed = [
            'circular_number' => '',
            'title' => '',
            'issuance_date' => '',
            'effective_date' => '',
            'category' => 'compliance',
            'priority' => 'medium',
            'key_requirements' => [],
            'applicable_departments' => [],
            'compliance_deadline' => '',
            'summary' => '',
            'raw' => $response
        ];

        $lines = explode("\n", $response);

        foreach ($lines as $line) {
            $line = trim($line);

            if (strpos($line, 'Circular Number:') !== false) {
                $parsed['circular_number'] = trim(str_replace('Circular Number:', '', $line));
            } elseif (strpos($line, 'Title:') !== false) {
                $parsed['title'] = trim(str_replace('Title:', '', $line));
            } elseif (strpos($line, 'Issuance Date:') !== false) {
                $parsed['issuance_date'] = trim(str_replace('Issuance Date:', '', $line));
            } elseif (strpos($line, 'Effective Date:') !== false) {
                $parsed['effective_date'] = trim(str_replace('Effective Date:', '', $line));
            } elseif (strpos($line, 'Category:') !== false) {
                $category = trim(str_replace('Category:', '', $line));
                $parsed['category'] = $this->normalizeCategory($category);
            } elseif (strpos($line, 'Priority:') !== false) {
                $parsed['priority'] = trim(str_replace('Priority:', '', $line));
            } elseif (strpos($line, 'Compliance Deadline:') !== false) {
                $parsed['compliance_deadline'] = trim(str_replace('Compliance Deadline:', '', $line));
            } elseif (strpos($line, 'Summary:') !== false) {
                $parsed['summary'] = trim(str_replace('Summary:', '', $line));
            } elseif (strpos($line, 'Key Requirements:') !== false) {
                // Parse subsequent lines as requirements
                $parsed['key_requirements'] = $this->parseListItems($lines, $line);
            } elseif (strpos($line, 'Applicable Departments:') !== false) {
                $parsed['applicable_departments'] = $this->parseListItems($lines, $line);
            }
        }

        return $parsed;
    }

    /**
     * Parse list items
     * 
     * @param array $lines
     * @param string $startLine
     * @return array
     */
    private function parseListItems(array $lines, string $startLine): array
    {
        $items = [];
        $started = false;
        $lineIndex = array_search($startLine, $lines);

        if ($lineIndex === false) {
            return $items;
        }

        for ($i = $lineIndex + 1; $i < count($lines); $i++) {
            $line = trim($lines[$i]);
            if (empty($line)) continue;

            // Check if line starts with a bullet or number
            if (preg_match('/^[\d]+[\.\)]\s*(.+)/', $line, $matches)) {
                $items[] = trim($matches[1]);
            } elseif (preg_match('/^[\-\*]\s*(.+)/', $line, $matches)) {
                $items[] = trim($matches[1]);
            } elseif (strpos($line, ':') !== false) {
                // Stop at next section
                break;
            } else {
                // Add to last item if it's a continuation
                if (!empty($items)) {
                    $items[count($items) - 1] .= ' ' . $line;
                }
            }
        }

        return $items;
    }

    /**
     * Generate summary
     * 
     * @param array $parsed
     * @param array $options
     * @return array
     */
    public function summary(array $parsed, array $options = []): array
    {
        try {
            $prompt = "Generate a concise summary of the following SBP circular:\n\n";
            $prompt .= "Title: " . ($parsed['title'] ?? 'N/A') . "\n";
            $prompt .= "Circular Number: " . ($parsed['circular_number'] ?? 'N/A') . "\n";
            $prompt .= "Category: " . ($parsed['category'] ?? 'N/A') . "\n";
            $prompt .= "Key Requirements: " . implode(', ', $parsed['key_requirements'] ?? []) . "\n\n";
            $prompt .= "Provide a clear, concise summary covering:\n";
            $prompt .= "1. What the circular is about\n";
            $prompt .= "2. Key requirements for banks\n";
            $prompt .= "3. Compliance timeline\n";
            $prompt .= "4. Who it applies to\n";

            $result = $this->openAI->completion($prompt, [
                'temperature' => 0.3,
                'max_tokens' => 2000
            ]);

            if (!$result['success']) {
                return ['error' => 'Failed to generate summary: ' . ($result['error'] ?? 'Unknown error')];
            }

            return [
                'summary' => $result['content'],
                'key_points' => $this->extractKeyPoints($result['content']),
                'timestamp' => time()
            ];

        } catch (\Exception $e) {
            $this->logger->error('Summary generation error: ' . $e->getMessage());
            return ['error' => $e->getMessage()];
        }
    }

    /**
     * Extract key points from summary
     * 
     * @param string $text
     * @return array
     */
    private function extractKeyPoints(string $text): array
    {
        $points = [];
        $lines = explode("\n", $text);

        foreach ($lines as $line) {
            $line = trim($line);
            if (preg_match('/^[\d]+[\.\)]\s*(.+)/', $line, $matches)) {
                $points[] = trim($matches[1]);
            } elseif (preg_match('/^[\-\*]\s*(.+)/', $line, $matches)) {
                $points[] = trim($matches[1]);
            }
        }

        return $points;
    }

    /**
     * Compare with regulations
     * 
     * @param array $parsed
     * @param array $options
     * @return array
     */
    public function compare(array $parsed, array $options = []): array
    {
        try {
            $prompt = "Compare the following SBP circular with existing banking regulations:\n\n";
            $prompt .= "Circular Details:\n";
            $prompt .= "Title: " . ($parsed['title'] ?? 'N/A') . "\n";
            $prompt .= "Category: " . ($parsed['category'] ?? 'N/A') . "\n";
            $prompt .= "Requirements: " . implode(', ', $parsed['key_requirements'] ?? []) . "\n\n";
            $prompt .= "Provide:\n";
            $prompt .= "1. Similar existing regulations\n";
            $prompt .= "2. Differences from existing regulations\n";
            $prompt .= "3. Conflicts with current practices\n";
            $prompt .= "4. New requirements introduced\n";

            $result = $this->openAI->completion($prompt, [
                'temperature' => 0.3,
                'max_tokens' => 3000
            ]);

            if (!$result['success']) {
                return ['error' => 'Failed to compare regulations: ' . ($result['error'] ?? 'Unknown error')];
            }

            return [
                'comparison' => $result['content'],
                'timestamp' => time()
            ];

        } catch (\Exception $e) {
            $this->logger->error('Comparison error: ' . $e->getMessage());
            return ['error' => $e->getMessage()];
        }
    }

    /**
     * Generate recommendations
     * 
     * @param array $parsed
     * @param array $comparison
     * @return array
     */
    public function recommend(array $parsed, array $comparison): array
    {
        try {
            $prompt = "Based on the following SBP circular analysis, provide implementation recommendations:\n\n";
            $prompt .= "Circular: " . ($parsed['title'] ?? 'N/A') . "\n";
            $prompt .= "Category: " . ($parsed['category'] ?? 'N/A') . "\n";
            $prompt .= "Key Requirements: " . implode(', ', $parsed['key_requirements'] ?? []) . "\n";
            $prompt .= "Compliance Deadline: " . ($parsed['compliance_deadline'] ?? 'N/A') . "\n\n";
            $prompt .= "Provide specific, actionable recommendations for:\n";
            $prompt .= "1. Implementation steps\n";
            $prompt .= "2. Priority order\n";
            $prompt .= "3. Responsible departments\n";
            $prompt .= "4. Timeline\n";
            $prompt .= "5. Resources needed\n";

            $result = $this->openAI->completion($prompt, [
                'temperature' => 0.4,
                'max_tokens' => 3000
            ]);

            if (!$result['success']) {
                return ['error' => 'Failed to generate recommendations: ' . ($result['error'] ?? 'Unknown error')];
            }

            return [
                'recommendations' => $this->parseRecommendations($result['content']),
                'timestamp' => time()
            ];

        } catch (\Exception $e) {
            $this->logger->error('Recommendations error: ' . $e->getMessage());
            return ['error' => $e->getMessage()];
        }
    }

    /**
     * Parse recommendations
     * 
     * @param string $text
     * @return array
     */
    private function parseRecommendations(string $text): array
    {
        $recommendations = [];
        $current = null;

        $lines = explode("\n", $text);

        foreach ($lines as $line) {
            $line = trim($line);

            if (preg_match('/^[\d]+[\.\)]\s*(.+)/', $line, $matches)) {
                if ($current) {
                    $recommendations[] = $current;
                }
                $current = [
                    'action' => $matches[1],
                    'priority' => 'medium',
                    'department' => '',
                    'timeline' => '',
                    'resources' => []
                ];
            } elseif ($current && strpos($line, 'Priority:') !== false) {
                $current['priority'] = trim(str_replace('Priority:', '', $line));
            } elseif ($current && strpos($line, 'Department:') !== false) {
                $current['department'] = trim(str_replace('Department:', '', $line));
            } elseif ($current && strpos($line, 'Timeline:') !== false) {
                $current['timeline'] = trim(str_replace('Timeline:', '', $line));
            } elseif ($current && strpos($line, 'Resources:') !== false) {
                $resources = trim(str_replace('Resources:', '', $line));
                $current['resources'] = array_filter(array_map('trim', explode(',', $resources)));
            } elseif ($current) {
                // Append to action description
                $current['action'] .= ' ' . $line;
            }
        }

        if ($current) {
            $recommendations[] = $current;
        }

        return $recommendations;
    }

    /**
     * Assess impact
     * 
     * @param array $parsed
     * @param array $comparison
     * @return array
     */
    private function assessImpact(array $parsed, array $comparison): array
    {
        $impact = [
            'level' => 'medium',
            'factors' => [],
            'affected_departments' => $parsed['applicable_departments'] ?? [],
            'implementation_effort' => 'medium',
            'cost_estimate' => 'medium'
        ];

        // Determine impact based on priority
        $priority = strtolower($parsed['priority'] ?? 'medium');
        if ($priority === 'critical') {
            $impact['level'] = 'high';
            $impact['implementation_effort'] = 'high';
            $impact['cost_estimate'] = 'high';
        } elseif ($priority === 'high') {
            $impact['level'] = 'high';
            $impact['implementation_effort'] = 'medium';
            $impact['cost_estimate'] = 'high';
        }

        // Check number of requirements
        $requirements = count($parsed['key_requirements'] ?? []);
        if ($requirements > 10) {
            $impact['implementation_effort'] = 'high';
        } elseif ($requirements > 5) {
            $impact['implementation_effort'] = 'medium';
        }

        return $impact;
    }

    /**
     * Calculate compliance score
     * 
     * @param array $parsed
     * @param array $comparison
     * @return float
     */
    private function calculateComplianceScore(array $parsed, array $comparison): float
    {
        $score = 0;
        $totalWeight = 0;

        // Requirements implementation
        $requirements = count($parsed['key_requirements'] ?? []);
        if ($requirements > 0) {
            $score += min($requirements * 5, 40);
            $totalWeight += 40;
        }

        // Clarity of circular
        if (!empty($parsed['summary'])) {
            $score += 10;
            $totalWeight += 10;
        }

        // Clear deadline
        if (!empty($parsed['compliance_deadline'])) {
            $score += 15;
            $totalWeight += 15;
        }

        // Defined departments
        $departments = count($parsed['applicable_departments'] ?? []);
        if ($departments > 0) {
            $score += min($departments * 5, 15);
            $totalWeight += 15;
        }

        // Priority consideration
        $priority = strtolower($parsed['priority'] ?? 'medium');
        $priorityScores = ['critical' => 20, 'high' => 15, 'medium' => 10, 'low' => 5];
        $score += $priorityScores[$priority] ?? 10;
        $totalWeight += 20;

        return $totalWeight > 0 ? round(($score / $totalWeight) * 100, 2) : 0;
    }

    /**
     * Normalize category
     * 
     * @param string $category
     * @return string
     */
    private function normalizeCategory(string $category): string
    {
        $category = strtolower(trim($category));

        foreach ($this->categories as $key => $label) {
            if (strpos($category, $key) !== false || strpos($category, strtolower($label)) !== false) {
                return $key;
            }
        }

        return 'compliance';
    }

    /**
     * Get all categories
     * 
     * @return array
     */
    public function getCategories(): array
    {
        return $this->categories;
    }

    /**
     * Get priorities
     * 
     * @return array
     */
    public function getPriorities(): array
    {
        return $this->priorities;
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

    /**
     * Upload and analyze circular
     * 
     * @param string $filePath
     * @param array $options
     * @return array
     */
    public function uploadAndAnalyze(string $filePath, array $options = []): array
    {
        try {
            // Read file content
            $content = $this->fileManager->read($filePath);
            if (!$content) {
                return $this->errorResponse('Failed to read file: ' . $filePath);
            }

            // Analyze content
            return $this->analyze($content, $options);

        } catch (\Exception $e) {
            $this->logger->error('Upload and analyze error: ' . $e->getMessage());
            return $this->errorResponse($e->getMessage());
        }
    }

    /**
     * Save circular to database
     * 
     * @param array $circularData
     * @return int|false
     */
    public function save(array $circularData)
    {
        try {
            $sql = "INSERT INTO sbp_circulars 
                    (circular_number, title, description, category, priority, 
                     issuance_date, effective_date, compliance_deadline, 
                     ai_summary, ai_analysis, status, created_at, updated_at) 
                    VALUES 
                    (:circular_number, :title, :description, :category, :priority,
                     :issuance_date, :effective_date, :compliance_deadline,
                     :ai_summary, :ai_analysis, 'pending', NOW(), NOW())";

            $stmt = $this->db->prepare($sql);
            $result = $stmt->execute([
                'circular_number' => $circularData['circular_number'] ?? '',
                'title' => $circularData['title'] ?? '',
                'description' => $circularData['summary'] ?? '',
                'category' => $circularData['category'] ?? 'compliance',
                'priority' => $circularData['priority'] ?? 'medium',
                'issuance_date' => $circularData['issuance_date'] ?? date('Y-m-d'),
                'effective_date' => $circularData['effective_date'] ?? date('Y-m-d'),
                'compliance_deadline' => $circularData['compliance_deadline'] ?? date('Y-m-d', strtotime('+90 days')),
                'ai_summary' => json_encode($circularData['summary'] ?? []),
                'ai_analysis' => json_encode($circularData)
            ]);

            if ($result) {
                $id = $this->db->lastInsertId();
                $this->logger->info('SBP circular saved', ['id' => $id, 'number' => $circularData['circular_number'] ?? '']);
                return $id;
            }

            return false;

        } catch (\Exception $e) {
            $this->logger->error('Save circular error: ' . $e->getMessage());
            return false;
        }
    }
}