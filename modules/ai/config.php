<?php
/**
 * AI Module - Configuration File
 * 
 * @package    AI-Banking-GRC-Platform
 * @subpackage modules/ai
 * @version    1.0.0
 * @author     GRC Platform Team
 * @copyright  2026 AI Banking GRC Platform
 * @license    Proprietary
 * 
 * This file contains all configuration for the AI module
 */

declare(strict_types=1);

// ============================================================
// MODULE METADATA
// ============================================================

define('AI_MODULE_NAME', 'AI Assistant');
define('AI_MODULE_VERSION', '1.0.0');
define('AI_MODULE_AUTHOR', 'GRC Platform Team');
define('AI_MODULE_DESCRIPTION', 'AI-powered assistant for GRC operations');

// ============================================================
// AI PROVIDERS
// ============================================================

define('AI_PROVIDERS', [
    'openai' => [
        'name' => 'OpenAI',
        'models' => ['gpt-4', 'gpt-3.5-turbo'],
        'default_model' => 'gpt-4',
        'api_url' => 'https://api.openai.com/v1'
    ],
    'huggingface' => [
        'name' => 'Hugging Face',
        'models' => ['llama2', 'mistral'],
        'default_model' => 'llama2',
        'api_url' => 'https://api-inference.huggingface.co/models'
    ],
    'custom' => [
        'name' => 'Custom AI',
        'models' => ['custom'],
        'default_model' => 'custom',
        'api_url' => null
    ]
]);

// ============================================================
// AI FEATURES
// ============================================================

define('AI_FEATURES', [
    'chat' => [
        'enabled' => true,
        'description' => 'AI chat assistant for GRC queries',
        'icon' => 'fa-comment-dots'
    ],
    'policy_generator' => [
        'enabled' => true,
        'description' => 'Generate policies using AI',
        'icon' => 'fa-file-alt'
    ],
    'risk_analyzer' => [
        'enabled' => true,
        'description' => 'Analyze and assess risks',
        'icon' => 'fa-shield-alt'
    ],
    'gap_analysis' => [
        'enabled' => true,
        'description' => 'Identify compliance gaps',
        'icon' => 'fa-search'
    ],
    'recommendations' => [
        'enabled' => true,
        'description' => 'Get AI recommendations',
        'icon' => 'fa-lightbulb'
    ],
    'compliance_checker' => [
        'enabled' => true,
        'description' => 'Check compliance status',
        'icon' => 'fa-check-circle'
    ],
    'report_generator' => [
        'enabled' => true,
        'description' => 'Generate AI-powered reports',
        'icon' => 'fa-file-pdf'
    ],
    'sentiment_analysis' => [
        'enabled' => true,
        'description' => 'Analyze sentiment of documents',
        'icon' => 'fa-smile'
    ]
]);

// ============================================================
// AI SETTINGS
// ============================================================

define('AI_SETTINGS', [
    'default_provider' => 'openai',
    'default_model' => 'gpt-4',
    'max_tokens' => 4096,
    'temperature' => 0.7,
    'top_p' => 0.9,
    'frequency_penalty' => 0.0,
    'presence_penalty' => 0.0,
    'timeout' => 30,
    'retry_attempts' => 3,
    'cache_enabled' => true,
    'cache_ttl' => 3600,
    'rate_limit' => 100,
    'rate_limit_window' => 60
]);

// ============================================================
// AI USE CASES
// ============================================================

define('AI_USE_CASES', [
    'compliance_assistance' => [
        'label' => 'Compliance Assistance',
        'description' => 'Get help with compliance questions',
        'prompt_template' => 'You are a compliance expert. Answer the following question: {query}'
    ],
    'risk_assessment' => [
        'label' => 'Risk Assessment',
        'description' => 'Assess risks based on description',
        'prompt_template' => 'Analyze the following risk and provide assessment: {risk_description}'
    ],
    'policy_generation' => [
        'label' => 'Policy Generation',
        'description' => 'Generate policies from requirements',
        'prompt_template' => 'Create a policy based on these requirements: {requirements}'
    ],
    'gap_analysis' => [
        'label' => 'Gap Analysis',
        'description' => 'Identify compliance gaps',
        'prompt_template' => 'Identify gaps in the following compliance status: {data}'
    ],
    'report_summary' => [
        'label' => 'Report Summary',
        'description' => 'Summarize GRC reports',
        'prompt_template' => 'Summarize the following report: {report_content}'
    ],
    'recommendations' => [
        'label' => 'Recommendations',
        'description' => 'Get AI recommendations',
        'prompt_template' => 'Provide recommendations for: {context}'
    ]
]);

// ============================================================
// AI RESPONSE FORMATS
// ============================================================

define('AI_RESPONSE_FORMATS', [
    'json' => 'JSON',
    'markdown' => 'Markdown',
    'plain' => 'Plain Text',
    'html' => 'HTML'
]);

// ============================================================
// NOTIFICATION SETTINGS
// ============================================================

define('AI_NOTIFICATIONS', [
    'query_completed' => 'AI query completed: {query}',
    'query_failed' => 'AI query failed: {error}',
    'analysis_ready' => 'AI analysis ready: {type}',
    'recommendations_ready' => 'AI recommendations available'
]);

// ============================================================
// HELPER FUNCTIONS
// ============================================================

/**
 * Get AI provider configuration
 * 
 * @param string $provider
 * @return array|null
 */
function ai_provider_config(string $provider): ?array
{
    $providers = AI_PROVIDERS;
    return $providers[$provider] ?? null;
}

/**
 * Get AI feature status
 * 
 * @param string $feature
 * @return bool
 */
function ai_feature_enabled(string $feature): bool
{
    $features = AI_FEATURES;
    return $features[$feature]['enabled'] ?? false;
}

/**
 * Get AI setting
 * 
 * @param string $key
 * @param mixed $default
 * @return mixed
 */
function ai_setting(string $key, $default = null)
{
    $settings = AI_SETTINGS;
    return $settings[$key] ?? $default;
}

/**
 * Get AI use case
 * 
 * @param string $useCase
 * @return array|null
 */
function ai_use_case(string $useCase): ?array
{
    $useCases = AI_USE_CASES;
    return $useCases[$useCase] ?? null;
}