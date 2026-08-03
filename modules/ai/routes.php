<?php
/**
 * AI Module - Routes
 * 
 * @package    AI-Banking-GRC-Platform
 * @subpackage modules/ai
 * @version    1.0.0
 * @author     GRC Platform Team
 * @copyright  2026 AI Banking GRC Platform
 * @license    Proprietary
 * 
 * This file contains all route definitions for the AI module
 */

declare(strict_types=1);

// ============================================================
// AI ROUTES
// ============================================================

$router->group(['middleware' => ['auth']], function($router) {
    // Dashboard
    $router->get('/ai', 'AIController@index', [
        'name' => 'ai.index'
    ]);
    
    $router->get('/ai/dashboard', 'AIController@index', [
        'name' => 'ai.dashboard'
    ]);
    
    // Chat
    $router->get('/ai/chat', 'AIController@chat', [
        'name' => 'ai.chat'
    ]);
    
    $router->post('/ai/chat/process', 'AIController@processChat', [
        'name' => 'ai.chat.process'
    ]);
    
    // Policy Generator
    $router->get('/ai/policy-generator', 'AIController@policyGenerator', [
        'name' => 'ai.policy-generator',
        'permission' => 'ai_policy_generate'
    ]);
    
    $router->post('/ai/policy-generate', 'AIController@generatePolicy', [
        'name' => 'ai.policy-generate',
        'permission' => 'ai_policy_generate'
    ]);
    
    // Risk Analyzer
    $router->get('/ai/risk-analyzer', 'AIController@riskAnalyzer', [
        'name' => 'ai.risk-analyzer',
        'permission' => 'ai_risk_analyze'
    ]);
    
    $router->post('/ai/risk-analyze', 'AIController@analyzeRisk', [
        'name' => 'ai.risk-analyze',
        'permission' => 'ai_risk_analyze'
    ]);
    
    // Gap Analysis
    $router->get('/ai/gap-analysis', 'AIController@gapAnalysis', [
        'name' => 'ai.gap-analysis',
        'permission' => 'ai_gap_analyze'
    ]);
    
    $router->post('/ai/gap-analyze', 'AIController@performGapAnalysis', [
        'name' => 'ai.gap-analyze',
        'permission' => 'ai_gap_analyze'
    ]);
    
    // Recommendations
    $router->get('/ai/recommendations', 'AIController@recommendations', [
        'name' => 'ai.recommendations'
    ]);
    
    // History
    $router->get('/ai/history', 'AIController@history', [
        'name' => 'ai.history'
    ]);
});

// ============================================================
// API ROUTES
// ============================================================

$router->group(['prefix' => 'api/v1/ai', 'middleware' => ['auth:api']], function($router) {
    // Dashboard
    $router->get('/dashboard', 'AIController@index', [
        'name' => 'api.ai.dashboard'
    ]);
    
    // Chat
    $router->post('/chat', 'AIController@processChat', [
        'name' => 'api.ai.chat'
    ]);
    
    // Policy
    $router->post('/policy/generate', 'AIController@generatePolicy', [
        'name' => 'api.ai.policy.generate'
    ]);
    
    // Risk
    $router->post('/risk/analyze', 'AIController@analyzeRisk', [
        'name' => 'api.ai.risk.analyze'
    ]);
    
    // Gap Analysis
    $router->post('/gap/analyze', 'AIController@performGapAnalysis', [
        'name' => 'api.ai.gap.analyze'
    ]);
    
    // Recommendations
    $router->get('/recommendations', 'AIController@recommendations', [
        'name' => 'api.ai.recommendations'
    ]);
    
    // History
    $router->get('/history', 'AIController@history', [
        'name' => 'api.ai.history'
    ]);
});

// ============================================================
// RETURN ROUTES
// ============================================================

return $router;