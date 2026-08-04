<?php
/**
 * AI Banking GRC Platform - AI Chat Assistant
 * 
 * @package    AI-Banking-GRC-Platform
 * @subpackage app/AI
 * @version    1.0.0
 * @author     GRC Platform Team
 * @copyright  2026 AI Banking GRC Platform
 * @license    Proprietary
 * 
 * This class provides enterprise AI chat functionality:
 * - Banking AI Chat
 * - Compliance Chat
 * - Risk Chat
 * - Audit Chat
 * - Conversation History
 * - Context Memory
 * - AI Suggestions
 * - Chat Logs
 */

declare(strict_types=1);

namespace App\AI;

use App\Libraries\Logger;
use App\Libraries\Cache;
use App\Libraries\Database;

class AIChat
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
     * @var array Conversation context
     */
    private array $context = [];

    /**
     * @var array Chat history
     */
    private array $history = [];

    /**
     * @var int Max history length
     */
    private int $maxHistory = 50;

    /**
     * @var string Current session ID
     */
    private string $sessionId;

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
        $this->sessionId = uniqid('chat_');
    }

    /**
     * Send a chat message
     * 
     * @param string $message
     * @param string $context
     * @param array $options
     * @return array
     */
    public function chat(string $message, string $context = 'general', array $options = []): array
    {
        try {
            // Validate input
            if (empty(trim($message))) {
                return $this->errorResponse('Message cannot be empty.');
            }

            // Check cache
            $cacheKey = 'chat_' . md5($message . $context . json_encode($options));
            if ($this->cache->has($cacheKey)) {
                $this->logger->debug('Chat response from cache');
                return $this->cache->get($cacheKey);
            }

            // Build context
            $this->buildContext($context, $options);

            // Build prompt
            $prompt = $this->buildPrompt($message, $context);

            // Get AI response
            $result = $this->openAI->completion($prompt, [
                'temperature' => $options['temperature'] ?? 0.7,
                'max_tokens' => $options['max_tokens'] ?? 2000,
                'system' => $this->getSystemPrompt($context)
            ]);

            if (!$result['success']) {
                return $this->errorResponse('Failed to get response: ' . ($result['error'] ?? 'Unknown error'));
            }

            // Save to history
            $this->addToHistory($message, $result['content'], $context);

            // Save to database
            $this->saveChat($message, $result['content'], $context);

            $response = [
                'success' => true,
                'response' => $result['content'],
                'context' => $context,
                'session_id' => $this->sessionId,
                'timestamp' => time(),
                'suggestions' => $this->getSuggestions($result['content'])
            ];

            // Cache response
            $this->cache->put($cacheKey, $response, 3600);

            return $response;

        } catch (\Exception $e) {
            $this->logger->error('Chat error: ' . $e->getMessage());
            return $this->errorResponse($e->getMessage());
        }
    }

    /**
     * Get chat reply
     * 
     * @param string $message
     * @param string $context
     * @return array
     */
    public function reply(string $message, string $context = 'general'): array
    {
        return $this->chat($message, $context);
    }

    /**
     * Get chat history
     * 
     * @param int $limit
     * @param string $context
     * @return array
     */
    public function history(int $limit = 10, string $context = ''): array
    {
        try {
            $sql = "SELECT * FROM chat_history WHERE session_id = :session_id";
            $params = ['session_id' => $this->sessionId];

            if ($context) {
                $sql .= " AND context = :context";
                $params['context'] = $context;
            }

            $sql .= " ORDER BY created_at DESC LIMIT :limit";

            $stmt = $this->db->prepare($sql);
            foreach ($params as $key => $value) {
                $type = is_int($value) ? \PDO::PARAM_INT : \PDO::PARAM_STR;
                $stmt->bindValue($key, $value, $type);
            }
            $stmt->bindValue('limit', $limit, \PDO::PARAM_INT);
            $stmt->execute();

            $history = $stmt->fetchAll();

            return [
                'success' => true,
                'history' => array_reverse($history),
                'count' => count($history),
                'session_id' => $this->sessionId
            ];

        } catch (\Exception $e) {
            $this->logger->error('Get history error: ' . $e->getMessage());
            return $this->errorResponse($e->getMessage());
        }
    }

    /**
     * Clear chat history
     * 
     * @param string $context
     * @return array
     */
    public function clear(string $context = ''): array
    {
        try {
            $sql = "DELETE FROM chat_history WHERE session_id = :session_id";
            $params = ['session_id' => $this->sessionId];

            if ($context) {
                $sql .= " AND context = :context";
                $params['context'] = $context;
            }

            $stmt = $this->db->prepare($sql);
            $stmt->execute($params);

            $this->history = [];
            $this->cache->forget('chat_history_' . $this->sessionId);

            return [
                'success' => true,
                'message' => 'Chat history cleared.',
                'session_id' => $this->sessionId
            ];

        } catch (\Exception $e) {
            $this->logger->error('Clear history error: ' . $e->getMessage());
            return $this->errorResponse($e->getMessage());
        }
    }

    /**
     * Save chat to database
     * 
     * @param string $message
     * @param string $response
     * @param string $context
     * @return bool
     */
    private function saveChat(string $message, string $response, string $context): bool
    {
        try {
            $sql = "INSERT INTO chat_history 
                    (session_id, user_id, message, response, context, created_at) 
                    VALUES 
                    (:session_id, :user_id, :message, :response, :context, NOW())";

            $userId = $_SESSION['user_id'] ?? null;

            $stmt = $this->db->prepare($sql);
            return $stmt->execute([
                'session_id' => $this->sessionId,
                'user_id' => $userId,
                'message' => $message,
                'response' => $response,
                'context' => $context
            ]);

        } catch (\Exception $e) {
            $this->logger->error('Save chat error: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Add to history
     * 
     * @param string $message
     * @param string $response
     * @param string $context
     * @return void
     */
    private function addToHistory(string $message, string $response, string $context): void
    {
        $this->history[] = [
            'message' => $message,
            'response' => $response,
            'context' => $context,
            'timestamp' => time()
        ];

        if (count($this->history) > $this->maxHistory) {
            array_shift($this->history);
        }

        // Cache history
        $this->cache->put('chat_history_' . $this->sessionId, $this->history, 3600);
    }

    /**
     * Build context
     * 
     * @param string $context
     * @param array $options
     * @return void
     */
    private function buildContext(string $context, array $options): void
    {
        $this->context = [
            'type' => $context,
            'user_id' => $_SESSION['user_id'] ?? null,
            'timestamp' => time(),
            'options' => $options
        ];

        // Load previous history from cache
        $cachedHistory = $this->cache->get('chat_history_' . $this->sessionId);
        if ($cachedHistory) {
            $this->history = $cachedHistory;
        }
    }

    /**
     * Build prompt
     * 
     * @param string $message
     * @param string $context
     * @return string
     */
    private function buildPrompt(string $message, string $context): string
    {
        $prompt = "You are an AI banking assistant specialized in " . $context . ".\n\n";

        // Add context
        if (!empty($this->context)) {
            $prompt .= "Context: " . json_encode($this->context) . "\n\n";
        }

        // Add recent history
        $recent = array_slice($this->history, -5);
        foreach ($recent as $item) {
            $prompt .= "User: " . $item['message'] . "\n";
            $prompt .= "Assistant: " . $item['response'] . "\n\n";
        }

        $prompt .= "User: " . $message . "\n";
        $prompt .= "Assistant: ";

        return $prompt;
    }

    /**
     * Get system prompt
     * 
     * @param string $context
     * @return string
     */
    private function getSystemPrompt(string $context): string
    {
        $prompts = [
            'general' => 'You are a helpful AI banking assistant. Provide accurate, professional, and concise responses.',
            'compliance' => 'You are a banking compliance expert. Provide detailed compliance guidance and regulatory insights.',
            'risk' => 'You are a banking risk management expert. Provide risk assessment and mitigation strategies.',
            'audit' => 'You are a banking audit expert. Provide audit recommendations and best practices.',
            'policy' => 'You are a banking policy expert. Provide policy guidance and recommendations.',
            'sbp' => 'You are a SBP regulatory expert. Provide insights on SBP circulars and regulations.'
        ];

        return $prompts[$context] ?? $prompts['general'];
    }

    /**
     * Get suggestions
     * 
     * @param string $response
     * @return array
     */
    private function getSuggestions(string $response): array
    {
        $suggestions = [];

        // Check for common topics
        $topics = [
            'risk' => ['risk assessment', 'risk mitigation', 'risk management'],
            'compliance' => ['compliance check', 'regulatory requirements', 'compliance framework'],
            'audit' => ['audit planning', 'audit findings', 'audit recommendations'],
            'policy' => ['policy creation', 'policy review', 'policy implementation']
        ];

        $responseLower = strtolower($response);

        foreach ($topics as $topic => $keywords) {
            foreach ($keywords as $keyword) {
                if (strpos($responseLower, $keyword) !== false) {
                    $suggestions[] = 'Tell me more about ' . $keyword;
                    break;
                }
            }
        }

        // Add default suggestions if none found
        if (empty($suggestions)) {
            $suggestions = [
                'How can I improve compliance?',
                'What are the main risks?',
                'Tell me about audit best practices'
            ];
        }

        return array_slice($suggestions, 0, 5);
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
            'session_id' => $this->sessionId,
            'timestamp' => time()
        ];
    }
}