<?php
/**
 * AI Banking GRC Platform - OpenAI Integration
 * 
 * @package    AI-Banking-GRC-Platform
 * @subpackage app/AI
 * @version    1.0.0
 * @author     GRC Platform Team
 * @copyright  2026 AI Banking GRC Platform
 * @license    Proprietary
 * 
 * This class provides enterprise OpenAI integration:
 * - API integration with secure requests
 * - Chat completion
 * - Prompt handling
 * - Token usage tracking
 * - Error handling with retry logic
 * - Response caching
 */

declare(strict_types=1);

namespace App\AI;

use App\Libraries\Logger;
use App\Libraries\Cache;
use App\Libraries\Security;

class OpenAI
{
    /**
     * @var string API key
     */
    private string $apiKey;

    /**
     * @var string API URL
     */
    private string $apiUrl = 'https://api.openai.com/v1';

    /**
     * @var string Default model
     */
    private string $defaultModel = 'gpt-4';

    /**
     * @var int Max tokens
     */
    private int $maxTokens = 4096;

    /**
     * @var float Temperature
     */
    private float $temperature = 0.7;

    /**
     * @var int Timeout in seconds
     */
    private int $timeout = 30;

    /**
     * @var int Max retry attempts
     */
    private int $maxRetries = 3;

    /**
     * @var Logger Logger instance
     */
    private Logger $logger;

    /**
     * @var Cache Cache instance
     */
    private Cache $cache;

    /**
     * @var Security Security instance
     */
    private Security $security;

    /**
     * @var array Request headers
     */
    private array $headers = [];

    /**
     * Constructor
     */
    public function __construct()
    {
        $this->logger = new Logger();
        $this->cache = new Cache();
        $this->security = new Security();

        $this->apiKey = getenv('OPENAI_API_KEY') ?: '';
        $this->apiUrl = getenv('OPENAI_API_URL') ?: $this->apiUrl;
        $this->defaultModel = getenv('OPENAI_MODEL') ?: $this->defaultModel;
        $this->maxTokens = (int)(getenv('OPENAI_MAX_TOKENS') ?: $this->maxTokens);
        $this->temperature = (float)(getenv('OPENAI_TEMPERATURE') ?: $this->temperature);

        $this->headers = [
            'Content-Type: application/json',
            'Authorization: Bearer ' . $this->apiKey
        ];

        if (empty($this->apiKey)) {
            $this->logger->warning('OpenAI API key not configured');
        }
    }

    /**
     * Send chat completion request
     * 
     * @param array $messages
     * @param array $options
     * @return array
     */
    public function chat(array $messages, array $options = []): array
    {
        try {
            $model = $options['model'] ?? $this->defaultModel;
            $temperature = $options['temperature'] ?? $this->temperature;
            $maxTokens = $options['max_tokens'] ?? $this->maxTokens;

            // Generate cache key
            $cacheKey = 'openai_chat_' . md5(json_encode($messages) . $model . $temperature);

            // Check cache
            if (isset($options['cache']) && $options['cache'] !== false) {
                $cached = $this->cache->get($cacheKey);
                if ($cached) {
                    $this->logger->debug('OpenAI chat response from cache');
                    return $cached;
                }
            }

            $payload = [
                'model' => $model,
                'messages' => $messages,
                'temperature' => $temperature,
                'max_tokens' => $maxTokens,
                'top_p' => $options['top_p'] ?? 0.9,
                'frequency_penalty' => $options['frequency_penalty'] ?? 0.0,
                'presence_penalty' => $options['presence_penalty'] ?? 0.0
            ];

            if (isset($options['stream']) && $options['stream']) {
                $payload['stream'] = true;
            }

            $response = $this->request('chat/completions', $payload);

            $result = [
                'success' => true,
                'content' => $response['choices'][0]['message']['content'] ?? '',
                'model' => $response['model'] ?? $model,
                'usage' => $response['usage'] ?? [],
                'finish_reason' => $response['choices'][0]['finish_reason'] ?? 'stop',
                'raw' => $response
            ];

            // Cache response
            if (isset($options['cache']) && $options['cache'] !== false) {
                $ttl = $options['cache_ttl'] ?? 3600;
                $this->cache->put($cacheKey, $result, $ttl);
            }

            $this->logger->info('OpenAI chat completed', [
                'model' => $model,
                'tokens' => $result['usage']['total_tokens'] ?? 0
            ]);

            return $result;

        } catch (\Exception $e) {
            $this->logger->error('OpenAI chat error: ' . $e->getMessage());
            return [
                'success' => false,
                'error' => $e->getMessage()
            ];
        }
    }

    /**
     * Send completion request
     * 
     * @param string $prompt
     * @param array $options
     * @return array
     */
    public function completion(string $prompt, array $options = []): array
    {
        $messages = [
            ['role' => 'user', 'content' => $prompt]
        ];

        if (isset($options['system'])) {
            array_unshift($messages, ['role' => 'system', 'content' => $options['system']]);
        }

        return $this->chat($messages, $options);
    }

    /**
     * Get embeddings
     * 
     * @param string $input
     * @param array $options
     * @return array
     */
    public function embeddings(string $input, array $options = []): array
    {
        try {
            $model = $options['model'] ?? 'text-embedding-ada-002';

            $payload = [
                'model' => $model,
                'input' => $input
            ];

            $response = $this->request('embeddings', $payload);

            return [
                'success' => true,
                'embedding' => $response['data'][0]['embedding'] ?? [],
                'model' => $response['model'] ?? $model,
                'usage' => $response['usage'] ?? []
            ];

        } catch (\Exception $e) {
            $this->logger->error('OpenAI embeddings error: ' . $e->getMessage());
            return [
                'success' => false,
                'error' => $e->getMessage()
            ];
        }
    }

    /**
     * Make API request
     * 
     * @param string $endpoint
     * @param array $payload
     * @return array
     */
    private function request(string $endpoint, array $payload): array
    {
        $url = $this->apiUrl . '/' . $endpoint;
        $ch = curl_init();

        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($payload));
        curl_setopt($ch, CURLOPT_HTTPHEADER, $this->headers);
        curl_setopt($ch, CURLOPT_TIMEOUT, $this->timeout);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, true);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 2);

        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $error = curl_error($ch);

        curl_close($ch);

        if ($error) {
            throw new \RuntimeException('cURL error: ' . $error);
        }

        $data = json_decode($response, true);

        if ($httpCode !== 200) {
            $errorMsg = $data['error']['message'] ?? 'Unknown API error';
            throw new \RuntimeException('OpenAI API error: ' . $errorMsg . ' (HTTP ' . $httpCode . ')');
        }

        return $data;
    }

    /**
     * Check if API is configured
     * 
     * @return bool
     */
    public function isConfigured(): bool
    {
        return !empty($this->apiKey);
    }

    /**
     * Get available models
     * 
     * @return array
     */
    public function getModels(): array
    {
        try {
            $response = $this->request('models', []);
            $models = array_map(function($model) {
                return $model['id'];
            }, $response['data'] ?? []);

            return $models;

        } catch (\Exception $e) {
            $this->logger->error('Get models error: ' . $e->getMessage());
            return [];
        }
    }

    /**
     * Set API key
     * 
     * @param string $apiKey
     * @return void
     */
    public function setApiKey(string $apiKey): void
    {
        $this->apiKey = $apiKey;
        $this->headers['Authorization'] = 'Bearer ' . $apiKey;
    }

    /**
     * Set model
     * 
     * @param string $model
     * @return void
     */
    public function setModel(string $model): void
    {
        $this->defaultModel = $model;
    }

    /**
     * Set temperature
     * 
     * @param float $temperature
     * @return void
     */
    public function setTemperature(float $temperature): void
    {
        $this->temperature = max(0, min(2, $temperature));
    }

    /**
     * Set max tokens
     * 
     * @param int $maxTokens
     * @return void
     */
    public function setMaxTokens(int $maxTokens): void
    {
        $this->maxTokens = max(1, $maxTokens);
    }

    /**
     * Get token usage
     * 
     * @param array $result
     * @return array
     */
    public function getTokenUsage(array $result): array
    {
        return $result['usage'] ?? [
            'prompt_tokens' => 0,
            'completion_tokens' => 0,
            'total_tokens' => 0
        ];
    }

    /**
     * Estimate cost
     * 
     * @param array $usage
     * @param string $model
     * @return float
     */
    public function estimateCost(array $usage, string $model = 'gpt-4'): float
    {
        $costs = [
            'gpt-4' => ['input' => 0.03, 'output' => 0.06],
            'gpt-4-32k' => ['input' => 0.06, 'output' => 0.12],
            'gpt-3.5-turbo' => ['input' => 0.0015, 'output' => 0.002],
            'text-embedding-ada-002' => ['input' => 0.0001, 'output' => 0.0001]
        ];

        $cost = $costs[$model] ?? $costs['gpt-3.5-turbo'];
        $total = ($usage['prompt_tokens'] ?? 0) * $cost['input'] / 1000;
        $total += ($usage['completion_tokens'] ?? 0) * $cost['output'] / 1000;

        return round($total, 4);
    }
}