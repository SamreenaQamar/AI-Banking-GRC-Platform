<?php
/**
 * AI Module - Controller
 * 
 * @package    AI-Banking-GRC-Platform
 * @subpackage modules/ai
 * @version    1.0.0
 * @author     GRC Platform Team
 * @copyright  2026 AI Banking GRC Platform
 * @license    Proprietary
 * 
 * This controller handles:
 * - AI dashboard
 * - AI chat
 * - Policy generation
 * - Risk analysis
 * - Gap analysis
 * - Recommendations
 */

declare(strict_types=1);

namespace Modules\AI\Controllers;

use App\Controllers\BaseController;
use App\Helpers\Auth;
use App\Helpers\CSRF;
use App\Helpers\Validation;
use Modules\AI\Services\AIService;
use Exception;

class AIController extends BaseController
{
    /**
     * @var AIService
     */
    private AIService $aiService;
    
    /**
     * Constructor
     */
    public function __construct()
    {
        parent::__construct();
        $this->controllerName = 'AI';
        $this->aiService = new AIService();
        
        $this->requireAuth();
        $this->requirePermission('ai_view');
    }
    
    /**
     * AI dashboard
     * 
     * @return void
     */
    public function index(): void
    {
        try {
            $userId = Auth::id();
            $dashboardData = $this->aiService->getDashboardData($userId);
            
            $this->render('ai/dashboard', [
                'title' => 'AI Dashboard - ' . APP_NAME,
                'data' => $dashboardData,
                'features' => AI_FEATURES,
                'use_cases' => AI_USE_CASES
            ]);
            
        } catch (Exception $e) {
            $this->setFlashMessage('error', 'Failed to load AI dashboard: ' . $e->getMessage());
            $this->redirectToRoute('dashboard');
        }
    }
    
    /**
     * AI Chat
     * 
     * @return void
     */
    public function chat(): void
    {
        try {
            $userId = Auth::id();
            $history = $this->aiService->getRecentQueries($userId, 20);
            
            $this->render('ai/chat', [
                'title' => 'AI Chat - ' . APP_NAME,
                'history' => $history,
                'use_cases' => AI_USE_CASES
            ]);
            
        } catch (Exception $e) {
            $this->setFlashMessage('error', 'Failed to load AI chat: ' . $e->getMessage());
            $this->redirectToRoute('ai.index');
        }
    }
    
    /**
     * Process chat message (AJAX)
     * 
     * @return void
     */
    public function processChat(): void
    {
        try {
            CSRF::validate($_POST['csrf_token'] ?? '');
            
            $message = $this->input('message');
            $context = $this->input('context', 'general');
            $userId = Auth::id();
            
            if (empty($message)) {
                throw new Exception('Message is required.');
            }
            
            $result = $this->aiService->chat($message, $context, $userId);
            
            if (!$result['success']) {
                throw new Exception($result['error']);
            }
            
            $this->jsonSuccess('AI response generated.', $result);
            
        } catch (Exception $e) {
            $this->jsonError($e->getMessage());
        }
    }
    
    /**
     * AI Policy Generator
     * 
     * @return void
     */
    public function policyGenerator(): void
    {
        try {
            $this->requirePermission('ai_policy_generate');
            
            $this->render('ai/policy-generator', [
                'title' => 'AI Policy Generator - ' . APP_NAME,
                'frameworks' => [
                    'iso27001' => 'ISO 27001:2022',
                    'nist' => 'NIST CSF',
                    'sbp' => 'SBP Regulations',
                    'basel' => 'Basel III',
                    'custom' => 'Custom Framework'
                ],
                'categories' => [
                    'governance' => 'Corporate Governance',
                    'security' => 'Information Security',
                    'compliance' => 'Compliance',
                    'risk' => 'Risk Management',
                    'hr' => 'Human Resources'
                ]
            ]);
            
        } catch (Exception $e) {
            $this->setFlashMessage('error', $e->getMessage());
            $this->redirectToRoute('ai.index');
        }
    }
    
    /**
     * Generate policy (AJAX)
     * 
     * @return void
     */
    public function generatePolicy(): void
    {
        try {
            $this->requirePermission('ai_policy_generate');
            CSRF::validate($_POST['csrf_token'] ?? '');
            
            $data = [
                'policy_name' => $this->input('policy_name'),
                'policy_type' => $this->input('policy_type'),
                'framework' => $this->input('framework'),
                'category' => $this->input('category'),
                'requirements' => $this->input('requirements'),
                'tone' => $this->input('tone')
            ];
            
            // This would use AI service for generation
            $result = $this->aiService->processWithAI(
                $data['requirements'],
                'policy'
            );
            
            $this->jsonSuccess('Policy generated successfully.', $result);
            
        } catch (Exception $e) {
            $this->jsonError($e->getMessage());
        }
    }
    
    /**
     * AI Risk Analyzer
     * 
     * @return void
     */
    public function riskAnalyzer(): void
    {
        try {
            $this->requirePermission('ai_risk_analyze');
            
            $this->render('ai/risk-analyzer', [
                'title' => 'AI Risk Analyzer - ' . APP_NAME,
                'risk_categories' => RISK_CATEGORIES
            ]);
            
        } catch (Exception $e) {
            $this->setFlashMessage('error', $e->getMessage());
            $this->redirectToRoute('ai.index');
        }
    }
    
    /**
     * Analyze risk (AJAX)
     * 
     * @return void
     */
    public function analyzeRisk(): void
    {
        try {
            $this->requirePermission('ai_risk_analyze');
            CSRF::validate($_POST['csrf_token'] ?? '');
            
            $riskDescription = $this->input('risk_description');
            $category = $this->input('category');
            
            if (empty($riskDescription)) {
                throw new Exception('Risk description is required.');
            }
            
            // This would use AI service for analysis
            $result = $this->aiService->processWithAI(
                $riskDescription,
                'risk'
            );
            
            $this->jsonSuccess('Risk analysis completed.', $result);
            
        } catch (Exception $e) {
            $this->jsonError($e->getMessage());
        }
    }
    
    /**
     * AI Gap Analysis
     * 
     * @return void
     */
    public function gapAnalysis(): void
    {
        try {
            $this->requirePermission('ai_gap_analyze');
            
            $this->render('ai/gap-analysis', [
                'title' => 'AI Gap Analysis - ' . APP_NAME,
                'frameworks' => [
                    'sbp' => 'SBP Regulations',
                    'iso27001' => 'ISO 27001',
                    'nist' => 'NIST CSF'
                ]
            ]);
            
        } catch (Exception $e) {
            $this->setFlashMessage('error', $e->getMessage());
            $this->redirectToRoute('ai.index');
        }
    }
    
    /**
     * Perform gap analysis (AJAX)
     * 
     * @return void
     */
    public function performGapAnalysis(): void
    {
        try {
            $this->requirePermission('ai_gap_analyze');
            CSRF::validate($_POST['csrf_token'] ?? '');
            
            $framework = $this->input('framework');
            $data = $this->input('data');
            
            if (empty($framework)) {
                throw new Exception('Framework is required.');
            }
            
            // This would use AI service for gap analysis
            $result = [
                'gaps' => [
                    [
                        'id' => 'GAP-001',
                        'description' => 'Missing compliance documentation',
                        'severity' => 'high',
                        'recommendation' => 'Create compliance documentation'
                    ],
                    [
                        'id' => 'GAP-002',
                        'description' => 'Incomplete risk assessment',
                        'severity' => 'critical',
                        'recommendation' => 'Complete risk assessment'
                    ]
                ],
                'summary' => '2 critical gaps identified. 3 high priority recommendations.'
            ];
            
            $this->jsonSuccess('Gap analysis completed.', $result);
            
        } catch (Exception $e) {
            $this->jsonError($e->getMessage());
        }
    }
    
    /**
     * AI Recommendations
     * 
     * @return void
     */
    public function recommendations(): void
    {
        try {
            $userId = Auth::id();
            $recommendations = $this->aiService->getAIRecommendations($userId);
            
            $this->render('ai/recommendations', [
                'title' => 'AI Recommendations - ' . APP_NAME,
                'recommendations' => $recommendations
            ]);
            
        } catch (Exception $e) {
            $this->setFlashMessage('error', $e->getMessage());
            $this->redirectToRoute('ai.index');
        }
    }
    
    /**
     * AI History
     * 
     * @return void
     */
    public function history(): void
    {
        try {
            $userId = Auth::id();
            $page = (int)$this->input('page', 1);
            $perPage = (int)$this->input('per_page', 20);
            
            $history = $this->aiService->getRecentQueries($userId, $perPage);
            $total = $this->aiService->getAIStats($userId);
            
            $this->render('ai/history', [
                'title' => 'AI History - ' . APP_NAME,
                'history' => $history,
                'total' => $total['total_queries'] ?? 0,
                'page' => $page,
                'per_page' => $perPage,
                'total_pages' => ceil(($total['total_queries'] ?? 0) / $perPage)
            ]);
            
        } catch (Exception $e) {
            $this->setFlashMessage('error', 'Failed to load AI history: ' . $e->getMessage());
            $this->redirectToRoute('ai.index');
        }
    }
}