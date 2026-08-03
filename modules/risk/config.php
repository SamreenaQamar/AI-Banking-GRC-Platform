<?php
/**
 * Risk Module - Configuration File
 * 
 * @package    AI-Banking-GRC-Platform
 * @subpackage modules/risk
 * @version    1.0.0
 * @author     GRC Platform Team
 * @copyright  2026 AI Banking GRC Platform
 * @license    Proprietary
 * 
 * This file contains all configuration for the risk module
 */

declare(strict_types=1);

// ============================================================
// MODULE METADATA
// ============================================================

define('RISK_MODULE_NAME', 'Risk Management');
define('RISK_MODULE_VERSION', '1.0.0');
define('RISK_MODULE_AUTHOR', 'GRC Platform Team');
define('RISK_MODULE_DESCRIPTION', 'Enterprise risk management for banking operations');

// ============================================================
// RISK LEVELS
// ============================================================

define('RISK_LEVELS', [
    'critical' => [
        'label' => 'Critical',
        'score_min' => 80,
        'color' => '#DC2626',
        'bg_color' => 'rgba(220, 38, 38, 0.1)',
        'icon' => 'fa-exclamation-circle',
        'description' => 'Immediate action required',
        'priority' => 1
    ],
    'high' => [
        'label' => 'High',
        'score_min' => 60,
        'color' => '#EF4444',
        'bg_color' => 'rgba(239, 68, 68, 0.1)',
        'icon' => 'fa-exclamation-triangle',
        'description' => 'High priority mitigation needed',
        'priority' => 2
    ],
    'medium' => [
        'label' => 'Medium',
        'score_min' => 40,
        'color' => '#F59E0B',
        'bg_color' => 'rgba(245, 158, 11, 0.1)',
        'icon' => 'fa-shield',
        'description' => 'Plan for mitigation',
        'priority' => 3
    ],
    'low' => [
        'label' => 'Low',
        'score_min' => 20,
        'color' => '#22C55E',
        'bg_color' => 'rgba(34, 197, 94, 0.1)',
        'icon' => 'fa-check-circle',
        'description' => 'Monitor and review',
        'priority' => 4
    ],
    'very_low' => [
        'label' => 'Very Low',
        'score_min' => 0,
        'color' => '#3B82F6',
        'bg_color' => 'rgba(59, 130, 246, 0.1)',
        'icon' => 'fa-info-circle',
        'description' => 'Acceptable risk level',
        'priority' => 5
    ]
]);

// ============================================================
// RISK CATEGORIES
// ============================================================

define('RISK_CATEGORIES', [
    'operational' => [
        'label' => 'Operational Risk',
        'description' => 'Risks arising from operational failures',
        'icon' => 'fa-cogs',
        'color' => '#2563EB'
    ],
    'cyber' => [
        'label' => 'Cyber Risk',
        'description' => 'Risks from cyber threats and attacks',
        'icon' => 'fa-shield-alt',
        'color' => '#8B5CF6'
    ],
    'financial' => [
        'label' => 'Financial Risk',
        'description' => 'Risks affecting financial stability',
        'icon' => 'fa-coins',
        'color' => '#22C55E'
    ],
    'compliance' => [
        'label' => 'Compliance Risk',
        'description' => 'Risks from regulatory non-compliance',
        'icon' => 'fa-gavel',
        'color' => '#F59E0B'
    ],
    'strategic' => [
        'label' => 'Strategic Risk',
        'description' => 'Risks affecting strategic objectives',
        'icon' => 'fa-bullseye',
        'color' => '#EF4444'
    ],
    'reputational' => [
        'label' => 'Reputational Risk',
        'description' => 'Risks affecting organization reputation',
        'icon' => 'fa-star',
        'color' => '#EC4899'
    ],
    'credit' => [
        'label' => 'Credit Risk',
        'description' => 'Risks from counterparty default',
        'icon' => 'fa-credit-card',
        'color' => '#3B82F6'
    ],
    'market' => [
        'label' => 'Market Risk',
        'description' => 'Risks from market fluctuations',
        'icon' => 'fa-chart-line',
        'color' => '#F97316'
    ]
]);

// ============================================================
// RISK SCORING
// ============================================================

define('RISK_SCORING', [
    'probability' => [
        1 => 'Very Unlikely',
        2 => 'Unlikely',
        3 => 'Possible',
        4 => 'Likely',
        5 => 'Very Likely'
    ],
    'impact' => [
        1 => 'Very Low',
        2 => 'Low',
        3 => 'Medium',
        4 => 'High',
        5 => 'Very High'
    ],
    'velocity' => [
        1 => 'Slow (> 1 year)',
        2 => 'Moderate (6-12 months)',
        3 => 'Fast (3-6 months)',
        4 => 'Very Fast (1-3 months)',
        5 => 'Immediate (< 1 month)'
    ],
    'persistence' => [
        1 => 'Very Short (< 1 day)',
        2 => 'Short (1-7 days)',
        3 => 'Medium (1-4 weeks)',
        4 => 'Long (1-6 months)',
        5 => 'Very Long (> 6 months)'
    ]
]);

// ============================================================
// RISK MATRIX
// ============================================================

define('RISK_MATRIX', [
    '5' => [
        '5' => 'critical',
        '4' => 'critical',
        '3' => 'high',
        '2' => 'medium',
        '1' => 'low'
    ],
    '4' => [
        '5' => 'critical',
        '4' => 'critical',
        '3' => 'high',
        '2' => 'medium',
        '1' => 'low'
    ],
    '3' => [
        '5' => 'critical',
        '4' => 'high',
        '3' => 'high',
        '2' => 'medium',
        '1' => 'low'
    ],
    '2' => [
        '5' => 'high',
        '4' => 'high',
        '3' => 'medium',
        '2' => 'low',
        '1' => 'very_low'
    ],
    '1' => [
        '5' => 'medium',
        '4' => 'medium',
        '3' => 'low',
        '2' => 'very_low',
        '1' => 'very_low'
    ]
]);

// ============================================================
// RISK TREATMENT STRATEGIES
// ============================================================

define('RISK_TREATMENT_STRATEGIES', [
    'avoid' => [
        'label' => 'Avoid',
        'description' => 'Eliminate the risk by avoiding the activity',
        'icon' => 'fa-ban'
    ],
    'reduce' => [
        'label' => 'Reduce',
        'description' => 'Reduce the likelihood or impact of the risk',
        'icon' => 'fa-arrow-down'
    ],
    'transfer' => [
        'label' => 'Transfer',
        'description' => 'Transfer the risk to a third party',
        'icon' => 'fa-exchange-alt'
    ],
    'accept' => [
        'label' => 'Accept',
        'description' => 'Accept the risk within defined tolerance',
        'icon' => 'fa-check'
    ],
    'mitigate' => [
        'label' => 'Mitigate',
        'description' => 'Implement controls to reduce risk',
        'icon' => 'fa-shield'
    ]
]);

// ============================================================
// RISK STATUS
// ============================================================

define('RISK_STATUS', [
    'identified' => 'Identified',
    'assessed' => 'Assessed',
    'mitigating' => 'Mitigating',
    'mitigated' => 'Mitigated',
    'monitoring' => 'Monitoring',
    'review' => 'Under Review',
    'approved' => 'Approved',
    'closed' => 'Closed',
    'rejected' => 'Rejected'
]);

// ============================================================
// BASEL III SETTINGS
// ============================================================

define('BASEL_III_SETTINGS', [
    'cet1_ratio_min' => 4.5,
    'tier1_ratio_min' => 6.0,
    'car_ratio_min' => 8.0,
    'leverage_ratio_min' => 3.0,
    'liquidity_coverage_ratio_min' => 100,
    'net_stable_funding_ratio_min' => 100
]);

// ============================================================
// RISK THRESHOLDS
// ============================================================

define('RISK_THRESHOLDS', [
    'critical' => [
        'score_min' => 80,
        'action' => 'immediate_intervention',
        'notification' => true,
        'escalation' => true
    ],
    'high' => [
        'score_min' => 60,
        'action' => 'high_priority_review',
        'notification' => true,
        'escalation' => false
    ],
    'medium' => [
        'score_min' => 40,
        'action' => 'scheduled_review',
        'notification' => false,
        'escalation' => false
    ],
    'low' => [
        'score_min' => 20,
        'action' => 'routine_monitoring',
        'notification' => false,
        'escalation' => false
    ]
]);

// ============================================================
// HEATMAP COLORS
// ============================================================

define('HEATMAP_COLORS', [
    'very_low' => '#22C55E',
    'low' => '#3B82F6',
    'medium' => '#F59E0B',
    'high' => '#EF4444',
    'critical' => '#DC2626'
]);

// ============================================================
// RISK NOTIFICATIONS
// ============================================================

define('RISK_NOTIFICATIONS', [
    'risk_created' => 'New risk identified: {title}',
    'risk_assessed' => 'Risk {title} has been assessed',
    'risk_mitigated' => 'Risk {title} has been mitigated',
    'risk_escalated' => 'Risk {title} has been escalated',
    'high_risk_alert' => 'High risk alert: {title}',
    'critical_risk_alert' => 'CRITICAL risk alert: {title}',
    'risk_approved' => 'Risk {title} has been approved',
    'risk_rejected' => 'Risk {title} has been rejected'
]);

// ============================================================
// AI RISK SETTINGS
// ============================================================

define('AI_RISK_SETTINGS', [
    'enabled' => true,
    'prediction_enabled' => true,
    'recommendation_enabled' => true,
    'trend_analysis_enabled' => true,
    'min_confidence_threshold' => 70,
    'max_recommendations' => 5
]);

// ============================================================
// HELPER FUNCTIONS
// ============================================================

/**
 * Get risk level by score
 * 
 * @param float $score
 * @return string
 */
function get_risk_level(float $score): string
{
    $levels = RISK_LEVELS;
    
    foreach ($levels as $key => $level) {
        if ($score >= $level['score_min']) {
            return $key;
        }
    }
    
    return 'very_low';
}

/**
 * Get risk level label
 * 
 * @param string $level
 * @return string
 */
function get_risk_level_label(string $level): string
{
    $levels = RISK_LEVELS;
    return $levels[$level]['label'] ?? $level;
}

/**
 * Get risk level color
 * 
 * @param string $level
 * @return string
 */
function get_risk_level_color(string $level): string
{
    $levels = RISK_LEVELS;
    return $levels[$level]['color'] ?? '#64748B';
}

/**
 * Get risk level from matrix
 * 
 * @param int $likelihood
 * @param int $impact
 * @return string
 */
function get_risk_level_from_matrix(int $likelihood, int $impact): string
{
    $matrix = RISK_MATRIX;
    
    if (isset($matrix[$likelihood][$impact])) {
        return $matrix[$likelihood][$impact];
    }
    
    return 'low';
}

/**
 * Get risk category label
 * 
 * @param string $category
 * @return string
 */
function get_risk_category_label(string $category): string
{
    $categories = RISK_CATEGORIES;
    return $categories[$category]['label'] ?? $category;
}

/**
 * Get risk status label
 * 
 * @param string $status
 * @return string
 */
function get_risk_status_label(string $status): string
{
    $statuses = RISK_STATUS;
    return $statuses[$status] ?? $status;
}

/**
 * Get threshold by score
 * 
 * @param float $score
 * @return array
 */
function get_risk_threshold(float $score): array
{
    $thresholds = RISK_THRESHOLDS;
    
    foreach ($thresholds as $key => $threshold) {
        if ($score >= $threshold['score_min']) {
            return $threshold;
        }
    }
    
    return $thresholds['low'];
}