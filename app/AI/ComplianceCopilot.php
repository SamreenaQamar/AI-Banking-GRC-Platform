<?php
/**
 * AI Banking GRC Platform - Compliance Copilot
 * 
 * @package    AI-Banking-GRC-Platform
 * @subpackage app/AI
 * @version    1.0.0
 * @author     GRC Platform Team
 * @copyright  2026 AI Banking GRC Platform
 * @license    Proprietary
 * 
 * This class provides AI compliance assistance:
 * - Explain regulations
 * - Compliance recommendations
 * - AI guidance
 * - Compliance Q&A
 * - Regulatory mapping
 */

declare(strict_types=1);

namespace App\AI;

use App\Libraries\Logger;
use App\Libraries\Cache;

class ComplianceCopilot
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
     * @var array Conversation history
     */
    private array $history = [];

    /**
     * @var int Max history length
     */
    private int $maxHistory = 20;

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
     * Ask a compliance question
     * 
     * @param string $question
     * @param array $context
     * @return array
     */
    public function ask(string $question, array $context = []): array
    {
        try {
            // Build prompt
            $variables = [
                'question' => $question,
                'context' => json_encode($context)
            ];

            // Check cache
            $cacheKey = 'copilot_ask_' . md5($question . json_encode($context));
            if ($this->cache->has($cacheKey)) {
                $this->logger->debug('Compliance copilot response from cache');
                return $this->cache->get($cacheKey);
            }

            // Add conversation history
            $options = [
                'history' => $this->getHistoryForPrompt()
            ];

            $messages = $this->promptEngine->build('compliance_analysis', $variables, $options);

            // Get AI response
            $result = $this->openAI->chat($messages, [
                'temperature' => 0.3,
                'max_tokens' => 2000
            ]);

            if (!$result['success']) {
                return $this->errorResponse('Failed to get response: ' . ($result['error'] ?? 'Unknown error'));
            }

            // Store in history
            $this->addToHistory($question, $result['content']);

            // Cache response
            $this->cache->put($cacheKey, $result, 3600);

            return [
                'success' => true,
                'response' => $result['content'],
                'confidence' => 0.9,
                'source' => 'AI',
                'timestamp' => time()
            ];

        } catch (\Exception $e) {
            $this->logger->error('Compliance copilot error: ' . $e->getMessage());
            return $this->errorResponse($e->getMessage());
        }
    }

    /**
     * Get reply to a specific question
     * 
     * @param string $question
     * @param array $options
     * @return array
     */
    public function reply(string $question, array $options = []): array
    {
        return $this->ask($question, $options);
    }

    /**
     * Get compliance recommendations
     * 
     * @param array $data
     * @return array
     */
    public function recommend(array $data): array
    {
        try {
            $variables = [
                'data' => json_encode($data)
            ];

            $messages = $this->promptEngine->build('compliance_analysis', $variables, [
                'system' => 'You are a compliance expert. Provide specific recommendations based on the compliance data provided.'
            ]);

            $result = $this->openAI->chat($messages, [
                'temperature' => 0.4,
                'max_tokens' => 3000
            ]);

            if (!$result['success']) {
                return $this->errorResponse('Failed to generate recommendations');
            }

            return [
                'success' => true,
                'recommendations' => $this->parseRecommendations($result['content']),
                'confidence' => 0.85,
                'timestamp' => time()
            ];

        } catch (\Exception $e) {
            $this->logger->error('Compliance recommendations error: ' . $e->getMessage());
            return $this->errorResponse($e->getMessage());
        }
    }

    /**
     * Explain a regulation
     * 
     * @param string $regulation
     * @param string $simplify
     * @return array
     */
    public function explainRegulation(string $regulation, bool $simplify = true): array
    {
        try {
            $prompt = "Explain the following banking regulation in " .
                      ($simplify ? "simple" : "detailed") .
                      " terms:\n\nRegulation: " . $regulation;

            $result = $this->openAI->completion($prompt, [
                'temperature' => 0.3,
                'max_tokens' => 2000
            ]);

            if (!$result['success']) {
                return $this->errorResponse('Failed to explain regulation');
            }

            return [
                'success' => true,
                'explanation' => $result['content'],
                'simplified' => $simplify,
                'regulation' => $regulation,
                'timestamp' => time()
            ];

        } catch (\Exception $e) {
            $this->logger->error('Explain regulation error: ' . $e->getMessage());
            return $this->errorResponse($e->getMessage());
        }
    }

    /**
     * Map regulatory requirements
     * 
     * @param array $requirements
     * @return array
     */
    public function mapRegulations(array $requirements): array
    {
        try {
            $prompt = "Map the following regulatory requirements to compliance controls:\n\n" .
                      json_encode($requirements, JSON_PRETTY_PRINT) .
                      "\n\nProvide a mapping of each requirement to specific controls and documentation.";

            $result = $this->openAI->completion($prompt, [
                'temperature' => 0.3,
                'max_tokens' => 3000
            ]);

            if (!$result['success']) {
                return $this->errorResponse('Failed to map regulations');
            }

            return [
                'success' => true,
                'mapping' => $result['content'],
                'timestamp' => time()
            ];

        } catch (\Exception $e) {
            $this->logger->error('Regulation mapping error: ' . $e->getMessage());
            return $this->errorResponse($e->getMessage());
        }
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

            // Check for numbered or bulleted items
            if (preg_match('/^[\d]+[\.\)]\s*(.+)/', $line, $matches)) {
                $recommendations[] = $matches[1];
            } elseif (preg_match('/^[\-\*]\s*(.+)/', $line, $matches)) {
                $recommendations[] = $matches[1];
            } elseif (strpos($line, ':') !== false) {
                // Check for key-value pairs
                $parts = explode(':', $line, 2);
                if (count($parts) === 2) {
                    $recommendations[] = trim($parts[1]);
                }
            }
        }

        // If no structured recommendations found, return whole text as one
        if (empty($recommendations)) {
            $recommendations = [$text];
        }

        return $recommendations;
    }

    /**
     * Add to conversation history
     * 
     * @param string $question
     * @param string $response
     * @return void
     */
    private function addToHistory(string $question, string $response): void
    {
        $this->history[] = [
            'question' => $question,
            'response' => $response,
            'timestamp' => time()
        ];

        if (count($this->history) > $this->maxHistory) {
            array_shift($this->history);
        }
    }

    /**
     * Get history for prompt
     * 
     * @return array
     */
    private function getHistoryForPrompt(): array
    {
        $history = [];
        $last = array_slice($this->history, -5);

        foreach ($last as $item) {
            $history[] = ['role' => 'user', 'content' => $item['question']];
            $history[] = ['role' => 'assistant', 'content' => $item['response']];
        }

        return $history;
    }

    /**
     * Get conversation history
     * 
     * @param int $limit
     * @return array
     */
    public function getHistory(int $limit = 10): array
    {
        return array_slice($this->history, -$limit);
    }

    /**
     * Clear conversation history
     * 
     * @return void
     */
    public function clearHistory(): void
    {
        $this->history = [];
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