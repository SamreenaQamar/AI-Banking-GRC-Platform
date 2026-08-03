<?php
/**
 * AI Banking GRC Platform - AI Copilot Controller
 * 
 * @package    AI-Banking-GRC-Platform
 * @version    1.0.0
 * @author     GRC Platform Team
 * @copyright  2026 AI Banking GRC Platform
 * @license    Proprietary
 * 
 * This controller handles:
 * - AI chat interface
 * - AI-powered analytics
 * - Recommendations and insights
 * - Document summarization
 * - Compliance assistance
 */

declare(strict_types=1);

namespace App\Controllers;

use App\Helpers\Auth;
use App\Services\AIService;
use App\Services\AnalyticsService;
use Exception;

class AICopilotController extends BaseController
{
    /**
     * @var AIService
     */
    private AIService $aiService;
    
    /**
     * @var AnalyticsService
     */
    private AnalyticsService $analyticsService;
    
    /**
     * Constructor
     */
    public function __construct()
    {
        parent::__construct();
        $this->controllerName = 'AI';
        $this->aiService = new AIService();
        $this->analyticsService = new AnalyticsService();
        
        $this->requireAuth();
        $this->requirePermission('ai_view');
    }
    
    /**
     * AI Copilot dashboard
     * 
     * @return void
     */
    public function index(): void
    {
        try {
            $insights = $this->getAIInsights();
            $recentQueries = $this->getRecentQueries();
            
            $this->render('index', [
                'title' => 'AI Copilot - ' . APP_NAME,
                'insights' => $insights,
                'recent_queries' => $recentQueries,
                'ai_use_cases' => AI_USE_CASES
            ]);
            
        } catch (Exception $e) {
            $this->setFlashMessage('error', 'Failed to load AI Copilot: ' . $e->getMessage());
            $this->redirectToRoute('dashboard');
        }
    }
    
    /**
     * AI Chat interface
     * 
     * @return void
     */
    public function chat(): void
    {
        try {
            $message = $this->input('message');
            $context = $this->input('context', 'general');
            $useCase = $this->input('use_case', 'general');
            
            if (empty($message)) {
                $this->jsonError('Message is required.');
            }
            
            // Validate CSRF token
            $this->validateCSRF($_POST['csrf_token'] ?? '');
            
            // Get AI response
            $response = $this->aiService->chat($message, $context, $useCase);
            
            // Log query
            $this->logAIQuery($message, $response, $useCase);
            
            $this->jsonSuccess('AI response generated.', [
                'response' => $response,
                'use_case' => $useCase,
                'timestamp' => date('Y-m-d H:i:s')
            ]);
            
        } catch (Exception $e) {
            $this->jsonError('AI service error: ' . $e->getMessage());
        }
    }
    
    /**
     * Analyze data
     * 
     * @return void
     */
    public function analyze(): void
    {
        try {
            $dataType = $this->input('data_type');
            $parameters = $this->input('parameters', []);
            
            if (empty($dataType)) {
                $this->jsonError('Data type is required.');
            }
            
            // Validate CSRF token
            $this->validateCSRF($_POST['csrf_token'] ?? '');
            
            // Perform analysis
            $analysis = $this->aiService->analyze($dataType, $parameters);
            
            $this->jsonSuccess('Analysis completed.', [
                'analysis' => $analysis,
                'data_type' => $dataType,
                'timestamp' => date('Y-m-d H:i:s')
            ]);
            
        } catch (Exception $e) {
            $this->jsonError('Analysis failed: ' . $e->getMessage());
        }
    }
    
    /**
     * Get recommendations
     * 
     * @return void
     */
    public function recommend(): void
    {
        try {
            $type = $this->input('type');
            $context = $this->input('context', []);
            
            if (empty($type)) {
                $this->jsonError('Recommendation type is required.');
            }
            
            // Validate CSRF token
            $this->validateCSRF($_POST['csrf_token'] ?? '');
            
            // Get recommendations
            $recommendations = $this->aiService->recommend($type, $context);
            
            $this->jsonSuccess('Recommendations generated.', [
                'recommendations' => $recommendations,
                'type' => $type,
                'timestamp' => date('Y-m-d H:i:s')
            ]);
            
        } catch (Exception $e) {
            $this->jsonError('Failed to generate recommendations: ' . $e->getMessage());
        }
    }
    
    /**
     * Summarize content
     * 
     * @return void
     */
    public function summarize(): void
    {
        try {
            $content = $this->input('content');
            $maxLength = (int)$this->input('max_length', 200);
            
            if (empty($content)) {
                $this->jsonError('Content is required.');
            }
            
            // Validate CSRF token
            $this->validateCSRF($_POST['csrf_token'] ?? '');
            
            // Generate summary
            $summary = $this->aiService->summarize($content, $maxLength);
            
            $this->jsonSuccess('Summary generated.', [
                'summary' => $summary,
                'original_length' => strlen($content),
                'summary_length' => strlen($summary),
                'timestamp' => date('Y-m-d H:i:s')
            ]);
            
        } catch (Exception $e) {
            $this->jsonError('Summarization failed: ' . $e->getMessage());
        }
    }
    
    /**
     * Get AI insights dashboard
     * 
     * @return void
     */
    public function insights(): void
    {
        try {
            $insights = $this->getAIInsights();
            
            $this->render('insights', [
                'title' => 'AI Insights - ' . APP_NAME,
                'insights' => $insights,
                'ai_metrics' => $this->getAIMetrics()
            ]);
            
        } catch (Exception $e) {
            $this->setFlashMessage('error', 'Failed to load insights: ' . $e->getMessage());
            $this->redirectToRoute('ai.index');
        }
    }
    
    /**
     * Submit AI feedback
     * 
     * @return void
     */
    public function feedback(): void
    {
        try {
            $queryId = $this->input('query_id');
            $rating = (int)$this->input('rating');
            $feedback = $this->input('feedback');
            
            if (!$queryId || !$rating) {
                $this->jsonError('Query ID and rating are required.');
            }
            
            // Validate CSRF token
            $this->validateCSRF($_POST['csrf_token'] ?? '');
            
            // Save feedback
            $this->aiService->saveFeedback($queryId, $rating, $feedback);
            
            $this->jsonSuccess('Feedback submitted successfully.');
            
        } catch (Exception $e) {
            $this->jsonError('Failed to submit feedback: ' . $e->getMessage());
        }
    }
    
    /**
     * Get AI insights
     * 
     * @return array
     */
    private function getAIInsights(): array
    {
        return [
            'compliance_insights' => $this->aiService->getComplianceInsights(),
            'risk_insights' => $this->aiService->getRiskInsights(),
            'audit_insights' => $this->aiService->getAuditInsights(),
            'trend_analysis' => $this->aiService->getTrendAnalysis(),
            'anomaly_detection' => $this->aiService->getAnomalies(),
            'predictive_analytics' => $this->aiService->getPredictions()
        ];
    }
    
    /**
     * Get AI metrics
     * 
     * @return array
     */
    private function getAIMetrics(): array
    {
        return [
            'total_queries' => $this->aiService->countQueries(),
            'avg_response_time' => $this->aiService->getAverageResponseTime(),
            'accuracy_rate' => $this->aiService->getAccuracyRate(),
            'user_satisfaction' => $this->aiService->getUserSatisfaction(),
            'top_use_cases' => $this->aiService->getTopUseCases(),
            'daily_usage' => $this->aiService->getDailyUsage()
        ];
    }
    
    /**
     * Get recent queries
     * 
     * @return array
     */
    private function getRecentQueries(): array
    {
        return $this->aiService->getRecentQueries(Auth::id(), 10);
    }
    
    /**
     * Log AI query
     * 
     * @param string $query
     * @param string $response
     * @param string $useCase
     * @return void
     */
    private function logAIQuery(string $query, string $response, string $useCase): void
    {
        $this->aiService->logQuery([
            'user_id' => Auth::id(),
            'query' => $query,
            'response' => $response,
            'use_case' => $useCase,
            'ip_address' => $_SERVER['REMOTE_ADDR'] ?? null,
            'user_agent' => $_SERVER['HTTP_USER_AGENT'] ?? null,
            'created_at' => date('Y-m-d H:i:s')
        ]);
    }
}