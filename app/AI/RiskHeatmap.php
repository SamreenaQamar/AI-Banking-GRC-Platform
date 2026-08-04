<?php
/**
 * AI Banking GRC Platform - Risk Heatmap Engine
 * 
 * @package    AI-Banking-GRC-Platform
 * @subpackage app/AI
 * @version    1.0.0
 * @author     GRC Platform Team
 * @copyright  2026 AI Banking GRC Platform
 * @license    Proprietary
 * 
 * This class provides risk heatmap generation:
 * - Heatmap matrix
 * - Risk matrix
 * - Color coding
 * - Export heatmap
 * - Dashboard data
 */

declare(strict_types=1);

namespace App\AI;

use App\Libraries\Logger;
use App\Libraries\Cache;

class RiskHeatmap
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
     * @var array Heatmap matrix
     */
    private array $matrix = [];

    /**
     * @var array Risk colors
     */
    private array $riskColors = [
        'critical' => '#DC2626',
        'high' => '#EF4444',
        'medium' => '#F59E0B',
        'low' => '#22C55E',
        'very_low' => '#3B82F6'
    ];

    /**
     * @var array Risk levels
     */
    private array $riskLevels = [
        5 => ['label' => 'Very High', 'score' => 80],
        4 => ['label' => 'High', 'score' => 60],
        3 => ['label' => 'Medium', 'score' => 40],
        2 => ['label' => 'Low', 'score' => 20],
        1 => ['label' => 'Very Low', 'score' => 0]
    ];

    /**
     * Constructor
     */
    public function __construct()
    {
        $this->logger = new Logger();
        $this->cache = new Cache();
        $this->initializeMatrix();
    }

    /**
     * Initialize heatmap matrix
     */
    private function initializeMatrix(): void
    {
        for ($impact = 1; $impact <= 5; $impact++) {
            for ($likelihood = 1; $likelihood <= 5; $likelihood++) {
                $score = ($likelihood * $impact / 25) * 100;
                $level = $this->getRiskLevel($score);
                $this->matrix[$likelihood][$impact] = [
                    'score' => $score,
                    'level' => $level,
                    'color' => $this->riskColors[$level],
                    'count' => 0,
                    'risks' => []
                ];
            }
        }
    }

    /**
     * Generate heatmap
     * 
     * @param array $risks
     * @param array $options
     * @return array
     */
    public function generate(array $risks, array $options = []): array
    {
        try {
            $cacheKey = 'heatmap_' . md5(json_encode($risks));
            if ($this->cache->has($cacheKey)) {
                $this->logger->debug('Heatmap from cache');
                return $this->cache->get($cacheKey);
            }

            // Reset matrix counts
            $this->initializeMatrix();

            // Populate matrix with risks
            foreach ($risks as $risk) {
                $likelihood = (int)($risk['likelihood'] ?? 3);
                $impact = (int)($risk['impact'] ?? 3);
                $likelihood = max(1, min(5, $likelihood));
                $impact = max(1, min(5, $impact));

                $this->matrix[$likelihood][$impact]['count']++;
                $this->matrix[$likelihood][$impact]['risks'][] = $risk;
            }

            // Generate heatmap data
            $heatmap = [
                'matrix' => $this->matrix,
                'stats' => $this->getStats($risks),
                'colors' => $this->riskColors,
                'levels' => $this->riskLevels,
                'total_risks' => count($risks),
                'generated_at' => date('Y-m-d H:i:s')
            ];

            $this->cache->put($cacheKey, $heatmap, 3600);

            return $heatmap;

        } catch (\Exception $e) {
            $this->logger->error('Heatmap generation error: ' . $e->getMessage());
            return ['error' => $e->getMessage()];
        }
    }

    /**
     * Get risk matrix
     * 
     * @param array $risks
     * @return array
     */
    public function matrix(array $risks): array
    {
        $matrix = [];

        for ($impact = 1; $impact <= 5; $impact++) {
            for ($likelihood = 1; $likelihood <= 5; $likelihood++) {
                $score = ($likelihood * $impact / 25) * 100;
                $level = $this->getRiskLevel($score);
                $matrix[$likelihood][$impact] = [
                    'level' => $level,
                    'label' => $this->riskLevels[$impact]['label'],
                    'color' => $this->riskColors[$level],
                    'score' => round($score, 2)
                ];
            }
        }

        return $matrix;
    }

    /**
     * Export heatmap
     * 
     * @param array $heatmap
     * @param string $format
     * @return string
     */
    public function export(array $heatmap, string $format = 'json'): string
    {
        if ($format === 'json') {
            return json_encode($heatmap, JSON_PRETTY_PRINT);
        } elseif ($format === 'html') {
            return $this->exportHTML($heatmap);
        } elseif ($format === 'csv') {
            return $this->exportCSV($heatmap);
        }

        return json_encode($heatmap);
    }

    /**
     * Export as HTML
     * 
     * @param array $heatmap
     * @return string
     */
    private function exportHTML(array $heatmap): string
    {
        $html = '<!DOCTYPE html><html><head>';
        $html .= '<meta charset="UTF-8">';
        $html .= '<title>Risk Heatmap</title>';
        $html .= '<style>';
        $html .= 'body{font-family: Arial, sans-serif; padding: 20px;}';
        $html .= 'table{border-collapse: collapse; margin: 20px auto;}';
        $html .= 'td,th{padding: 15px; text-align: center; border: 1px solid #ddd; min-width: 60px;}';
        $html .= '.label{font-weight: bold; background: #f5f5f5;}';
        $html .= '.count{font-size: 18px; font-weight: bold;}';
        $html .= '</style>';
        $html .= '</head><body>';
        $html .= '<h1>Risk Heatmap</h1>';
        $html .= '<p>Generated: ' . date('Y-m-d H:i:s') . '</p>';
        $html .= '<p>Total Risks: ' . ($heatmap['total_risks'] ?? 0) . '</p>';

        $html .= '<table>';
        $html .= '<tr><th>Likelihood \\ Impact</th>';
        for ($i = 1; $i <= 5; $i++) {
            $html .= '<th>' . $this->riskLevels[$i]['label'] . '</th>';
        }
        $html .= '</tr>';

        for ($likelihood = 5; $likelihood >= 1; $likelihood--) {
            $html .= '<tr>';
            $html .= '<td class="label">' . $this->riskLevels[$likelihood]['label'] . '</td>';
            for ($impact = 1; $impact <= 5; $impact++) {
                $cell = $heatmap['matrix'][$likelihood][$impact] ?? ['count' => 0, 'color' => '#e0e0e0'];
                $count = $cell['count'] ?? 0;
                $color = $cell['color'] ?? '#e0e0e0';
                $textColor = $this->getTextColor($color);

                $html .= '<td style="background: ' . $color . '; color: ' . $textColor . ';">';
                if ($count > 0) {
                    $html .= '<div class="count">' . $count . '</div>';
                } else {
                    $html .= '&nbsp;';
                }
                $html .= '</td>';
            }
            $html .= '</tr>';
        }

        $html .= '</table>';

        // Legend
        $html .= '<div style="margin-top: 20px; text-align: center;">';
        $html .= '<h3>Risk Levels</h3>';
        foreach ($this->riskColors as $level => $color) {
            $html .= '<span style="display: inline-block; padding: 5px 15px; margin: 2px; background: ' . $color . '; color: #fff; border-radius: 3px;">' . ucfirst(str_replace('_', ' ', $level)) . '</span>';
        }
        $html .= '</div>';

        $html .= '</body></html>';

        return $html;
    }

    /**
     * Export as CSV
     * 
     * @param array $heatmap
     * @return string
     */
    private function exportCSV(array $heatmap): string
    {
        $csv = "Likelihood\\Impact,";
        for ($i = 1; $i <= 5; $i++) {
            $csv .= $this->riskLevels[$i]['label'] . ',';
        }
        $csv .= "\n";

        for ($likelihood = 5; $likelihood >= 1; $likelihood--) {
            $csv .= $this->riskLevels[$likelihood]['label'] . ',';
            for ($impact = 1; $impact <= 5; $impact++) {
                $cell = $heatmap['matrix'][$likelihood][$impact] ?? ['count' => 0];
                $csv .= ($cell['count'] ?? 0) . ',';
            }
            $csv .= "\n";
        }

        return $csv;
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
     * Get text color based on background
     * 
     * @param string $hexColor
     * @return string
     */
    private function getTextColor(string $hexColor): string
    {
        $hex = ltrim($hexColor, '#');
        $r = hexdec(substr($hex, 0, 2));
        $g = hexdec(substr($hex, 2, 2));
        $b = hexdec(substr($hex, 4, 2));

        $luminance = (0.299 * $r + 0.587 * $g + 0.114 * $b) / 255;

        return $luminance > 0.5 ? '#000000' : '#FFFFFF';
    }

    /**
     * Get heatmap statistics
     * 
     * @param array $risks
     * @return array
     */
    private function getStats(array $risks): array
    {
        $stats = [
            'total' => count($risks),
            'by_level' => [],
            'avg_score' => 0
        ];

        $totalScore = 0;

        foreach ($this->riskColors as $level => $color) {
            $stats['by_level'][$level] = 0;
        }

        foreach ($risks as $risk) {
            $score = $risk['score'] ?? 50;
            $level = $this->getRiskLevel($score);
            $stats['by_level'][$level]++;
            $totalScore += $score;
        }

        $stats['avg_score'] = $stats['total'] > 0 ? round($totalScore / $stats['total'], 2) : 0;

        return $stats;
    }

    /**
     * Get dashboard data
     * 
     * @param array $risks
     * @return array
     */
    public function getDashboardData(array $risks): array
    {
        $heatmap = $this->generate($risks);
        $stats = $this->getStats($risks);

        return [
            'total' => $stats['total'],
            'by_level' => $stats['by_level'],
            'avg_score' => $stats['avg_score'],
            'heatmap' => $heatmap['matrix'],
            'colors' => $this->riskColors,
            'levels' => $this->riskLevels,
            'timestamp' => time()
        ];
    }
}