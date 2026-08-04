<?php
/**
 * AI Banking GRC Platform - AML Checker
 * 
 * @package    AI-Banking-GRC-Platform
 * @subpackage app/AI
 * @version    1.0.0
 * @author     GRC Platform Team
 * @copyright  2026 AI Banking GRC Platform
 * @license    Proprietary
 * 
 * This class provides AML (Anti-Money Laundering) analysis:
 * - AML risk detection
 * - Suspicious transaction detection
 * - Customer risk scoring
 * - Watchlist checking
 * - Sanction screening
 * - AML alert generation
 */

declare(strict_types=1);

namespace App\AI;

use App\Libraries\Logger;
use App\Libraries\Cache;

class AMLChecker
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
     * @var array AML risk factors
     */
    private array $riskFactors = [
        'transaction_amount' => 0.3,
        'transaction_frequency' => 0.2,
        'customer_location' => 0.15,
        'transaction_type' => 0.15,
        'customer_history' => 0.1,
        'unusual_pattern' => 0.1
    ];

    /**
     * @var array High-risk countries
     */
    private array $highRiskCountries = [
        'AF', 'BS', 'BY', 'KH', 'KY', 'CN', 'CU', 'IR', 'IQ', 'KP',
        'LB', 'LY', 'MM', 'NI', 'PK', 'PA', 'RU', 'SA', 'SY', 'TR',
        'UA', 'VE', 'YE', 'ZW'
    ];

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
     * Analyze transaction for AML risk
     * 
     * @param array $transaction
     * @param array $options
     * @return array
     */
    public function analyze(array $transaction, array $options = []): array
    {
        try {
            $cacheKey = 'aml_' . md5(json_encode($transaction));
            if ($this->cache->has($cacheKey)) {
                $this->logger->debug('AML analysis from cache');
                return $this->cache->get($cacheKey);
            }

            $riskScore = $this->calculateRiskScore($transaction);
            $riskLevel = $this->getRiskLevel($riskScore);
            $detections = $this->detect($transaction);
            $alerts = $this->generateAlerts($transaction, $detections);
            $watchlistCheck = $this->checkWatchlist($transaction);
            $sanctionCheck = $this->checkSanctions($transaction);

            $result = [
                'success' => true,
                'risk_score' => $riskScore,
                'risk_level' => $riskLevel,
                'detections' => $detections,
                'alerts' => $alerts,
                'watchlist_check' => $watchlistCheck,
                'sanction_check' => $sanctionCheck,
                'recommendations' => $this->getRecommendations($riskScore, $detections),
                'timestamp' => time()
            ];

            $this->cache->put($cacheKey, $result, 3600);

            return $result;

        } catch (\Exception $e) {
            $this->logger->error('AML analysis error: ' . $e->getMessage());
            return $this->errorResponse($e->getMessage());
        }
    }

    /**
     * Calculate AML risk score
     * 
     * @param array $transaction
     * @return float
     */
    private function calculateRiskScore(array $transaction): float
    {
        $score = 0;

        // Transaction amount
        $amount = $transaction['amount'] ?? 0;
        if ($amount > 1000000) $score += 25;
        elseif ($amount > 500000) $score += 20;
        elseif ($amount > 100000) $score += 15;
        elseif ($amount > 50000) $score += 10;
        else $score += 5;

        // Transaction frequency
        $frequency = $transaction['frequency'] ?? 'low';
        if ($frequency === 'high') $score += 20;
        elseif ($frequency === 'medium') $score += 10;
        else $score += 5;

        // Customer location
        $country = $transaction['country'] ?? '';
        if (in_array($country, $this->highRiskCountries)) {
            $score += 15;
        }

        // Transaction type
        $type = $transaction['type'] ?? '';
        $highRiskTypes = ['cryptocurrency', 'wire_transfer', 'cash_withdrawal'];
        if (in_array($type, $highRiskTypes)) {
            $score += 15;
        }

        // Customer history
        $history = $transaction['customer_history'] ?? 'clean';
        if ($history === 'suspicious') $score += 15;
        elseif ($history === 'flagged') $score += 10;

        // Unusual pattern
        $pattern = $transaction['pattern'] ?? 'normal';
        if ($pattern === 'unusual') $score += 15;
        elseif ($pattern === 'very_unusual') $score += 20;

        // Apply weights
        $weightedScore = 0;
        foreach ($this->riskFactors as $factor => $weight) {
            $factorScore = $score * $weight;
            $weightedScore += $factorScore;
        }

        return min(100, max(0, $weightedScore));
    }

    /**
     * Detect suspicious activities
     * 
     * @param array $transaction
     * @return array
     */
    private function detect(array $transaction): array
    {
        $detections = [];

        // Detect unusual amount
        $amount = $transaction['amount'] ?? 0;
        $averageAmount = $transaction['average_amount'] ?? 50000;
        if ($amount > $averageAmount * 10) {
            $detections[] = 'Unusual transaction amount detected';
        }

        // Detect frequent transactions
        $frequency = $transaction['frequency'] ?? 'low';
        if ($frequency === 'high') {
            $detections[] = 'High frequency transaction pattern detected';
        }

        // Detect high-risk country
        $country = $transaction['country'] ?? '';
        if (in_array($country, $this->highRiskCountries)) {
            $detections[] = 'Transaction involving high-risk country';
        }

        // Detect unusual pattern
        $pattern = $transaction['pattern'] ?? 'normal';
        if ($pattern === 'unusual' || $pattern === 'very_unusual') {
            $detections[] = 'Unusual transaction pattern detected';
        }

        // Detect large cash transactions
        if ($amount > 500000 && $transaction['type'] === 'cash') {
            $detections[] = 'Large cash transaction detected';
        }

        return $detections;
    }

    /**
     * Generate AML alerts
     * 
     * @param array $transaction
     * @param array $detections
     * @return array
     */
    private function generateAlerts(array $transaction, array $detections): array
    {
        $alerts = [];

        if (empty($detections)) {
            return $alerts;
        }

        foreach ($detections as $detection) {
            $severity = 'medium';

            if (strpos($detection, 'high-risk') !== false) {
                $severity = 'high';
            } elseif (strpos($detection, 'Unusual') !== false) {
                $severity = 'high';
            } elseif (strpos($detection, 'Large cash') !== false) {
                $severity = 'high';
            }

            $alerts[] = [
                'detection' => $detection,
                'severity' => $severity,
                'timestamp' => time(),
                'transaction_id' => $transaction['id'] ?? null
            ];
        }

        return $alerts;
    }

    /**
     * Check watchlist
     * 
     * @param array $transaction
     * @return array
     */
    private function checkWatchlist(array $transaction): array
    {
        $customerName = $transaction['customer_name'] ?? '';
        $isOnWatchlist = $this->openAI->completion(
            "Check if '{$customerName}' is on any banking watchlist. Return only 'yes' or 'no'.",
            ['temperature' => 0.1, 'max_tokens' => 10]
        );

        return [
            'checked' => true,
            'customer' => $customerName,
            'on_watchlist' => strtolower(trim($isOnWatchlist['content'] ?? '')) === 'yes',
            'timestamp' => time()
        ];
    }

    /**
     * Check sanctions
     * 
     * @param array $transaction
     * @return array
     */
    private function checkSanctions(array $transaction): array
    {
        $customerName = $transaction['customer_name'] ?? '';
        $sanctionResult = $this->openAI->completion(
            "Is '{$customerName}' or any related entity under sanctions? Return only 'yes' or 'no'.",
            ['temperature' => 0.1, 'max_tokens' => 10]
        );

        return [
            'checked' => true,
            'customer' => $customerName,
            'under_sanctions' => strtolower(trim($sanctionResult['content'] ?? '')) === 'yes',
            'timestamp' => time()
        ];
    }

    /**
     * Get risk level
     * 
     * @param float $score
     * @return string
     */
    private function getRiskLevel(float $score): string
    {
        if ($score >= 80) return 'critical';
        if ($score >= 60) return 'high';
        if ($score >= 40) return 'medium';
        if ($score >= 20) return 'low';
        return 'very_low';
    }

    /**
     * Get recommendations
     * 
     * @param float $score
     * @param array $detections
     * @return array
     */
    private function getRecommendations(float $score, array $detections): array
    {
        $recommendations = [];

        if ($score >= 80) {
            $recommendations[] = 'Immediate investigation required';
            $recommendations[] = 'File Suspicious Transaction Report (STR)';
            $recommendations[] = 'Escalate to AML compliance officer';
        } elseif ($score >= 60) {
            $recommendations[] = 'Conduct enhanced due diligence';
            $recommendations[] = 'Review transaction history';
            $recommendations[] = 'Document findings';
        } elseif ($score >= 40) {
            $recommendations[] = 'Monitor future transactions';
            $recommendations[] = 'Update customer risk profile';
        } else {
            $recommendations[] = 'Continue regular monitoring';
        }

        foreach ($detections as $detection) {
            $recommendations[] = 'Address: ' . $detection;
        }

        return $recommendations;
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