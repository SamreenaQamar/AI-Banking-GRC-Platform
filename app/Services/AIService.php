<?php
/**
 * AI Banking GRC Platform - AI Service
 * 
 * @package    AI-Banking-GRC-Platform
 * @subpackage app/Services
 * @version    1.0.0
 * @author     GRC Platform Team
 * @copyright  2026 AI Banking GRC Platform
 * @license    Proprietary
 * 
 * This service handles AI integration business logic:
 * - AI Request Manager
 * - AI Chat Integration
 * - AI Prediction
 * - AI Recommendation
 * - AI Response Formatting
 * - AI Logs
 */

declare(strict_types=1);

namespace App\Services;

use App\AI\AIChat;
use App\AI\ComplianceCopilot;
use App\AI\RiskAnalyzer;
use App\AI\RecommendationEngine;
use App\AI\OpenAI;
use App\Models\AIRequest;
use App\Models\AIResponse;
use App\Models\ActivityLog;
use App\Libraries\Logger;
use App\Libraries\Validator;
use App\Libraries\Cache;

class AIService
{
    /**
     * @var AIChat AI Chat instance
     */
    private AIChat $aiChat;

    /**
     * @var ComplianceCopilot Compliance copilot
     */
    private ComplianceCopilot $complianceCopilot;

    /**
     * @var RiskAnalyzer Risk analyzer
     */
    private RiskAnalyzer $riskAnalyzer;

    /**
     * @var RecommendationEngine Recommendation engine
     */
    private RecommendationEngine $recommendationEngine;

    /**
     * @var OpenAI OpenAI instance
     */
    private OpenAI $openAI;

    /**
     * @var AIRequest AI request model
     */
    private AIRequest $aiRequestModel;

    /**
     * @var AIResponse AI response model
     */
    private AIResponse $aiResponseModel;

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
        $this->aiChat = new AIChat();
        $this->complianceCopilot = new ComplianceCopilot();
        $this->riskAnalyzer = new RiskAnalyzer();
        $this->recommendationEngine = new RecommendationEngine();
        $this->openAI = new OpenAI();
        $this->aiRequestModel = new AIRequest();
        $this->aiResponseModel = new AIResponse();
        $this->activityLogModel = new ActivityLog();
        $this->logger = new Logger();
        $this->validator = new Validator();
        $this->cache = new Cache();
    }

    /**
     * Chat with AI
     * 
     * @param string $message
     * @param string $context
     * @param int $userId
     * @param array $options
     * @return array
     */
    public function chat(string $message, string $context, int $userId, array $options = []): array
    {
        try {
            // Create request record
            $requestId = $this->createRequest($message, $context, $userId, 'chat');

            // Get AI response
            $result = $this->aiChat->chat($message, $context, $options);

            if (!$result['success']) {
                $this->updateRequestStatus($requestId, 'failed', $result['error'] ?? 'Unknown error');
                return $this->errorResponse('AI chat failed: ' . ($result['error'] ?? 'Unknown error'));
            }

            // Save response
            $this->saveResponse($requestId, $result['response'], $result);

            // Log activity
            $this->activityLogModel->logAction($userId, 'ai_chat', 'ai',
                "AI chat: {$message} (context: {$context})");

            return $this->successResponse('AI chat response received.', [
                'response' => $result['response'],
                'session_id' => $result['session_id'] ?? null,
                'suggestions' => $result['suggestions'] ?? [],
                'request_id' => $requestId
            ]);

        } catch (\Exception $e) {
            $this->logger->error('AI chat error: ' . $e->getMessage());
            return $this->errorResponse('An error occurred during AI chat.');
        }
    }

    /**
     * Get AI prediction
     * 
     * @param string $type
     * @param array $data
     * @param int $userId
     * @return array
     */
    public function predict(string $type, array $data, int $userId): array
    {
        try {
            $requestId = $this->createRequest(json_encode($data), $type, $userId, 'prediction');

            $prompt = "Based on the following data, provide a prediction for {$type}:\n\n";
            $prompt .= json_encode($data, JSON_PRETTY_PRINT);

            $result = $this->openAI->completion($prompt, [
                'temperature' => 0.3,
                'max_tokens' => 2000
            ]);

            if (!$result['success']) {
                $this->updateRequestStatus($requestId, 'failed', $result['error'] ?? 'Unknown error');
                return $this->errorResponse('AI prediction failed: ' . ($result['error'] ?? 'Unknown error'));
            }

            $this->saveResponse($requestId, $result['content'], $result);

            $this->activityLogModel->logAction($userId, 'ai_predict', 'ai',
                "AI prediction: {$type}");

            return $this->successResponse('AI prediction generated.', [
                'prediction' => $result['content'],
                'type' => $type,
                'request_id' => $requestId
            ]);

        } catch (\Exception $e) {
            $this->logger->error('AI prediction error: ' . $e->getMessage());
            return $this->errorResponse('An error occurred during AI prediction.');
        }
    }

    /**
     * Get AI recommendations
     * 
     * @param string $type
     * @param array $data
     * @param int $userId
     * @return array
     */
    public function recommend(string $type, array $data, int $userId): array
    {
        try {
            $requestId = $this->createRequest(json_encode($data), $type, $userId, 'recommendation');

            $result = $this->recommendationEngine->recommend($type, $data);

            if (!$result['success']) {
                $this->updateRequestStatus($requestId, 'failed', $result['error'] ?? 'Unknown error');
                return $this->errorResponse('AI recommendation failed: ' . ($result['error'] ?? 'Unknown error'));
            }

            $this->saveResponse($requestId, json_encode($result['recommendations']), $result);

            $this->activityLogModel->logAction($userId, 'ai_recommend', 'ai',
                "AI recommendation: {$type}");

            return $this->successResponse('AI recommendations generated.', [
                'recommendations' => $result['recommendations'],
                'type' => $type,
                'count' => $result['count'] ?? 0,
                'request_id' => $requestId
            ]);

        } catch (\Exception $e) {
            $this->logger->error('AI recommendation error: ' . $e->getMessage());
            return $this->errorResponse('An error occurred during AI recommendation.');
        }
    }

    /**
     * Get AI history
     * 
     * @param int $userId
     * @param int $page
     * @param int $perPage
     * @return array
     */
    public function history(int $userId, int $page = 1, int $perPage = 20): array
    {
        try {
            $cacheKey = 'ai_history_' . $userId . '_' . $page;
            if ($this->cache->has($cacheKey)) {
                return $this->cache->get($cacheKey);
            }

            $history = $this->aiRequestModel->getByUser($userId, $page, $perPage);
            $total = $this->aiRequestModel->countByUser($userId);

            $result = $this->successResponse('AI history retrieved.', [
                'history' => $history,
                'total' => $total,
                'page' => $page,
                'per_page' => $perPage,
                'total_pages' => ceil($total / $perPage)
            ]);

            $this->cache->put($cacheKey, $result, 300);

            return $result;

        } catch (\Exception $e) {
            $this->logger->error('AI history error: ' . $e->getMessage());
            return $this->errorResponse('An error occurred retrieving AI history.');
        }
    }

    /**
     * Create AI request record
     * 
     * @param string $prompt
     * @param string $module
     * @param int $userId
     * @param string $type
     * @return int
     */
    private function createRequest(string $prompt, string $module, int $userId, string $type): int
    {
        $data = [
            'prompt' => $prompt,
            'module' => $module,
            'user_id' => $userId,
            'type' => $type,
            'status' => 'pending',
            'created_at' => date('Y-m-d H:i:s')
        ];

        return $this->aiRequestModel->create($data);
    }

    /**
     * Update request status
     * 
     * @param int $requestId
     * @param string $status
     * @param string $error
     * @return void
     */
    private function updateRequestStatus(int $requestId, string $status, string $error = ''): void
    {
        $data = ['status' => $status];

        if ($error) {
            $data['error'] = $error;
        }

        $this->aiRequestModel->update($requestId, $data);
    }

    /**
     * Save AI response
     * 
     * @param int $requestId
     * @param string $response
     * @param array $metadata
     * @return void
     */
    private function saveResponse(int $requestId, string $response, array $metadata = []): void
    {
        $data = [
            'request_id' => $requestId,
            'response' => $response,
            'confidence' => $metadata['confidence'] ?? 0.8,
            'model' => $metadata['model'] ?? 'gpt-4',
            'processing_time' => $metadata['processing_time'] ?? 0,
            'tokens_used' => $metadata['usage']['total_tokens'] ?? 0,
            'success' => true,
            'created_at' => date('Y-m-d H:i:s')
        ];

        $this->aiResponseModel->create($data);
        $this->updateRequestStatus($requestId, 'completed');
    }

    /**
     * Success response
     * 
     * @param string $message
     * @param array $data
     * @return array
     */
    private function successResponse(string $message, array $data = []): array
    {
        return [
            'success' => true,
            'message' => $message,
            'data' => $data
        ];
    }

    /**
     * Error response
     * 
     * @param string $message
     * @param array $data
     * @return array
     */
    private function errorResponse(string $message, array $data = []): array
    {
        return [
            'success' => false,
            'message' => $message,
            'data' => $data
        ];
    }
}