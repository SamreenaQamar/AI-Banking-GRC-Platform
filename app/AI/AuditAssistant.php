<?php
/**
 * AI Banking GRC Platform - Audit Assistant
 * 
 * @package    AI-Banking-GRC-Platform
 * @subpackage app/AI
 * @version    1.0.0
 * @author     GRC Platform Team
 * @copyright  2026 AI Banking GRC Platform
 * @license    Proprietary
 * 
 * This class provides AI-powered audit assistance:
 * - Audit suggestions
 * - Missing controls detection
 * - Audit findings analysis
 * - Audit risk assessment
 * - Audit summary generation
 */

declare(strict_types=1);

namespace App\AI;

use App\Libraries\Logger;
use App\Libraries\Cache;
use App\Libraries\Database;

class AuditAssistant
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
     * Constructor
     */
    public function __construct()
    {
        $this->logger = new Logger();
        $this->cache = new Cache();
        $this->db = Database::getInstance();
        $this->promptEngine = new PromptEngine();
        $this->openAI = new OpenAI();
    }

    /**
     * Analyze audit data
     * 
     * @param array $auditData
     * @param array $options
     * @return array
     */
    public function analyze(array $auditData, array $options = []): array
    {
        try {
            $cacheKey = 'audit_analyze_' . md5(json_encode($auditData));
            if ($this->cache->has($cacheKey)) {
                $this->logger->debug('Audit analysis from cache');
                return $this->cache->get($cacheKey);
            }

            $prompt = "Analyze the following audit data and provide insights:\n\n";
            $prompt .= json_encode($auditData, JSON_PRETTY_PRINT) . "\n\n";
            $prompt .= "Provide:\n";
            $prompt .= "1. Overall audit health assessment\n";
            $prompt .= "2. Key findings summary\n";
            $prompt .= "3. Risk areas identified\n";
            $prompt .= "4. Recommendations for improvement\n";
            $prompt .= "5. Priority actions\n";

            $result = $this->openAI->completion($prompt, [
                'temperature' => 0.3,
                'max_tokens' => 3000
            ]);

            if (!$result['success']) {
                return $this->errorResponse('Failed to analyze audit: ' . ($result['error'] ?? 'Unknown error'));
            }

            $analysis = $this->parseAuditAnalysis($result['content']);

            $response = [
                'success' => true,
                'analysis' => $analysis,
                'raw' => $result['content'],
                'confidence' => 0.85,
                'timestamp' => time()
            ];

            $this->cache->put($cacheKey, $response, 3600);

            return $response;

        } catch (\Exception $e) {
            $this->logger->error('Audit analysis error: ' . $e->getMessage());
            return $this->errorResponse($e->getMessage());
        }
    }

    /**
     * Generate audit recommendations
     * 
     * @param array $findings
     * @param array $options
     * @return array
     */
    public function recommend(array $findings, array $options = []): array
    {
        try {
            $prompt = "Based on the following audit findings, provide specific recommendations:\n\n";
            $prompt .= json_encode($findings, JSON_PRETTY_PRINT) . "\n\n";
            $prompt .= "Provide:\n";
            $prompt .= "1. Corrective actions\n";
            $prompt .= "2. Preventive measures\n";
            $prompt .= "3. Controls to implement\n";
            $prompt .= "4. Priority order\n";
            $prompt .= "5. Timeline estimates\n";

            $result = $this->openAI->completion($prompt, [
                'temperature' => 0.4,
                'max_tokens' => 3000
            ]);

            if (!$result['success']) {
                return $this->errorResponse('Failed to generate recommendations: ' . ($result['error'] ?? 'Unknown error'));
            }

            $recommendations = $this->parseRecommendations($result['content']);

            return [
                'success' => true,
                'recommendations' => $recommendations,
                'count' => count($recommendations),
                'timestamp' => time()
            ];

        } catch (\Exception $e) {
            $this->logger->error('Audit recommendations error: ' . $e->getMessage());
            return $this->errorResponse($e->getMessage());
        }
    }

    /**
     * Generate audit report
     * 
     * @param array $data
     * @param string $type
     * @return array
     */
    public function generate(array $data, string $type = 'summary'): array
    {
        try {
            $prompt = "Generate an audit " . $type . " report based on the following data:\n\n";
            $prompt .= json_encode($data, JSON_PRETTY_PRINT) . "\n\n";
            $prompt .= "The report should include:\n";
            $prompt .= "1. Executive summary\n";
            $prompt .= "2. Key findings\n";
            $prompt .= "3. Recommendations\n";
            $prompt .= "4. Risk assessment\n";
            $prompt .= "5. Next steps\n";

            $result = $this->openAI->completion($prompt, [
                'temperature' => 0.3,
                'max_tokens' => 4000
            ]);

            if (!$result['success']) {
                return $this->errorResponse('Failed to generate report: ' . ($result['error'] ?? 'Unknown error'));
            }

            return [
                'success' => true,
                'report' => $result['content'],
                'type' => $type,
                'timestamp' => time()
            ];

        } catch (\Exception $e) {
            $this->logger->error('Audit report generation error: ' . $e->getMessage());
            return $this->errorResponse($e->getMessage());
        }
    }

    /**
     * Parse audit analysis
     * 
     * @param string $text
     * @return array
     */
    private function parseAuditAnalysis(string $text): array
    {
        $analysis = [
            'health' => 'medium',
            'findings' => [],
            'risks' => [],
            'recommendations' => [],
            'priority_actions' => []
        ];

        $lines = explode("\n", $text);
        $currentSection = '';

        foreach ($lines as $line) {
            $line = trim($line);
            if (empty($line)) continue;

            if (strpos(strtolower($line), 'health') !== false && strpos($line, ':') !== false) {
                $parts = explode(':', $line, 2);
                if (count($parts) === 2) {
                    $analysis['health'] = trim(strtolower($parts[1]));
                }
            }

            if (strpos(strtolower($line), 'finding') !== false && strpos($line, ':') !== false) {
                $parts = explode(':', $line, 2);
                if (count($parts) === 2) {
                    $analysis['findings'][] = trim($parts[1]);
                }
            }

            if (strpos(strtolower($line), 'risk') !== false && strpos($line, ':') !== false) {
                $parts = explode(':', $line, 2);
                if (count($parts) === 2) {
                    $analysis['risks'][] = trim($parts[1]);
                }
            }

            if (strpos(strtolower($line), 'recommendation') !== false && strpos($line, ':') !== false) {
                $parts = explode(':', $line, 2);
                if (count($parts) === 2) {
                    $analysis['recommendations'][] = trim($parts[1]);
                }
            }

            if (strpos(strtolower($line), 'priority') !== false && strpos($line, ':') !== false) {
                $parts = explode(':', $line, 2);
                if (count($parts) === 2) {
                    $analysis['priority_actions'][] = trim($parts[1]);
                }
            }
        }

        return $analysis;
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
     * Detect missing controls
     * 
     * @param array $controls
     * @param array $requiredControls
     * @return array
     */
    public function detectMissingControls(array $controls, array $requiredControls): array
    {
        try {
            $prompt = "Compare the following controls with required controls and identify missing ones:\n\n";
            $prompt .= "Existing Controls:\n" . json_encode($controls, JSON_PRETTY_PRINT) . "\n\n";
            $prompt .= "Required Controls:\n" . json_encode($requiredControls, JSON_PRETTY_PRINT) . "\n\n";
            $prompt .= "List only the missing controls with their descriptions.";

            $result = $this->openAI->completion($prompt, [
                'temperature' => 0.2,
                'max_tokens' => 2000
            ]);

            if (!$result['success']) {
                return $this->errorResponse('Failed to detect missing controls');
            }

            $missing = $this->parseMissingControls($result['content']);

            return [
                'success' => true,
                'missing_controls' => $missing,
                'count' => count($missing),
                'timestamp' => time()
            ];

        } catch (\Exception $e) {
            $this->logger->error('Detect missing controls error: ' . $e->getMessage());
            return $this->errorResponse($e->getMessage());
        }
    }

    /**
     * Parse missing controls
     * 
     * @param string $text
     * @return array
     */
    private function parseMissingControls(string $text): array
    {
        $controls = [];
        $lines = explode("\n", $text);

        foreach ($lines as $line) {
            $line = trim($line);
            if (empty($line)) continue;

            if (preg_match('/^[\d]+[\.\)]\s*(.+)/', $line, $matches)) {
                $controls[] = $matches[1];
            } elseif (preg_match('/^[\-\*]\s*(.+)/', $line, $matches)) {
                $controls[] = $matches[1];
            }
        }

        return $controls;
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