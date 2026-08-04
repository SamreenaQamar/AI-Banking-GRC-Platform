<?php
/**
 * AI Banking GRC Platform - PDF Generator Library
 * 
 * @package    AI-Banking-GRC-Platform
 * @subpackage app/Libraries
 * @version    1.0.0
 * @author     GRC Platform Team
 * @copyright  2026 AI Banking GRC Platform
 * @license    Proprietary
 * 
 * This library provides enterprise PDF generation functionality:
 * - Dashboard reports
 * - Audit reports
 * - Compliance reports
 * - Risk reports
 * - User reports
 * - Charts and tables
 */

declare(strict_types=1);

namespace App\Libraries;

use Dompdf\Dompdf;
use Dompdf\Options;
use App\Libraries\Logger;

class PDFGenerator
{
    /**
     * @var Dompdf Dompdf instance
     */
    private Dompdf $dompdf;

    /**
     * @var Logger Logger instance
     */
    private Logger $logger;

    /**
     * @var array PDF options
     */
    private array $options = [];

    /**
     * Constructor
     */
    public function __construct()
    {
        $this->logger = new Logger();
        $this->options = [
            'default_font' => 'dejavu sans',
            'isHtml5ParserEnabled' => true,
            'isRemoteEnabled' => true,
            'isPhpEnabled' => false,
            'enable_css_float' => true,
            'enable_font_subsetting' => true
        ];

        try {
            $options = new Options($this->options);
            $this->dompdf = new Dompdf($options);
        } catch (\Exception $e) {
            $this->logger->error('PDF Generator initialization error: ' . $e->getMessage());
        }
    }

    /**
     * Generate PDF from HTML
     * 
     * @param string $html
     * @param array $options
     * @return string
     */
    public function generate(string $html, array $options = []): string
    {
        try {
            $this->dompdf->loadHtml($html);
            $this->dompdf->setPaper(
                $options['paper'] ?? 'A4',
                $options['orientation'] ?? 'portrait'
            );
            $this->dompdf->render();
            return $this->dompdf->output();

        } catch (\Exception $e) {
            $this->logger->error('PDF generation error: ' . $e->getMessage());
            throw new \RuntimeException('Failed to generate PDF: ' . $e->getMessage());
        }
    }

    /**
     * Generate dashboard report PDF
     * 
     * @param array $data
     * @return string
     */
    public function generateDashboardReport(array $data): string
    {
        $html = $this->renderDashboardReport($data);
        return $this->generate($html);
    }

    /**
     * Generate risk report PDF
     * 
     * @param array $data
     * @return string
     */
    public function generateRiskReport(array $data): string
    {
        $html = $this->renderRiskReport($data);
        return $this->generate($html);
    }

    /**
     * Generate audit report PDF
     * 
     * @param array $data
     * @return string
     */
    public function generateAuditReport(array $data): string
    {
        $html = $this->renderAuditReport($data);
        return $this->generate($html);
    }

    /**
     * Generate compliance report PDF
     * 
     * @param array $data
     * @return string
     */
    public function generateComplianceReport(array $data): string
    {
        $html = $this->renderComplianceReport($data);
        return $this->generate($html);
    }

    /**
     * Generate user report PDF
     * 
     * @param array $data
     * @return string
     */
    public function generateUserReport(array $data): string
    {
        $html = $this->renderUserReport($data);
        return $this->generate($html);
    }

    /**
     * Download PDF
     * 
     * @param string $content
     * @param string $filename
     * @return void
     */
    public function download(string $content, string $filename): void
    {
        header('Content-Type: application/pdf');
        header('Content-Disposition: attachment; filename="' . $filename . '"');
        header('Content-Length: ' . strlen($content));
        echo $content;
        exit;
    }

    /**
     * Save PDF to file
     * 
     * @param string $content
     * @param string $path
     * @return bool
     */
    public function save(string $content, string $path): bool
    {
        return file_put_contents($path, $content) !== false;
    }

    /**
     * Render dashboard report HTML
     * 
     * @param array $data
     * @return string
     */
    private function renderDashboardReport(array $data): string
    {
        return $this->renderTemplate('dashboard', $data);
    }

    /**
     * Render risk report HTML
     * 
     * @param array $data
     * @return string
     */
    private function renderRiskReport(array $data): string
    {
        return $this->renderTemplate('risk', $data);
    }

    /**
     * Render audit report HTML
     * 
     * @param array $data
     * @return string
     */
    private function renderAuditReport(array $data): string
    {
        return $this->renderTemplate('audit', $data);
    }

    /**
     * Render compliance report HTML
     * 
     * @param array $data
     * @return string
     */
    private function renderComplianceReport(array $data): string
    {
        return $this->renderTemplate('compliance', $data);
    }

    /**
     * Render user report HTML
     * 
     * @param array $data
     * @return string
     */
    private function renderUserReport(array $data): string
    {
        return $this->renderTemplate('user', $data);
    }

    /**
     * Render report template
     * 
     * @param string $type
     * @param array $data
     * @return string
     */
    private function renderTemplate(string $type, array $data): string
    {
        $templatePath = VIEW_PATH . '/pdf/' . $type . '.php';

        if (file_exists($templatePath)) {
            extract($data);
            ob_start();
            require $templatePath;
            return ob_get_clean();
        }

        // Fallback template
        return $this->renderFallbackTemplate($type, $data);
    }

    /**
     * Render fallback template
     * 
     * @param string $type
     * @param array $data
     * @return string
     */
    private function renderFallbackTemplate(string $type, array $data): string
    {
        $html = '<!DOCTYPE html><html><head><meta charset="UTF-8">';
        $html .= '<style>body{font-family: Arial, sans-serif; padding: 20px;}';
        $html .= 'h1{color: #0B3D91;} h2{color: #2563EB;}';
        $html .= 'table{width:100%; border-collapse: collapse;}';
        $html .= 'th,td{padding: 8px; border: 1px solid #ddd; text-align: left;}';
        $html .= 'th{background: #f4f4f4;}</style>';
        $html .= '</head><body>';
        $html .= '<h1>' . APP_NAME . '</h1>';
        $html .= '<h2>' . ucfirst($type) . ' Report</h2>';
        $html .= '<p>Generated: ' . date('Y-m-d H:i:s') . '</p>';

        if (!empty($data['data'])) {
            $html .= '<table>';
            $html .= '<tr>';
            foreach (array_keys((array)$data['data'][0]) as $header) {
                $html .= '<th>' . $header . '</th>';
            }
            $html .= '</tr>';
            foreach ($data['data'] as $row) {
                $html .= '<tr>';
                foreach ((array)$row as $value) {
                    $html .= '<td>' . $value . '</td>';
                }
                $html .= '</tr>';
            }
            $html .= '</table>';
        }

        if (!empty($data['charts'])) {
            $html .= '<h3>Charts</h3>';
            foreach ($data['charts'] as $chart) {
                $html .= '<div style="margin: 20px 0;">';
                $html .= '<img src="' . ($chart['url'] ?? '') . '" style="max-width: 100%;">';
                $html .= '</div>';
            }
        }

        $html .= '<p style="margin-top: 30px; font-size: 12px; color: #666;">';
        $html .= 'This report was generated automatically. For any queries, please contact support.</p>';
        $html .= '</body></html>';

        return $html;
    }

    /**
     * Set PDF options
     * 
     * @param array $options
     * @return void
     */
    public function setOptions(array $options): void
    {
        $this->options = array_merge($this->options, $options);
    }

    /**
     * Get PDF options
     * 
     * @return array
     */
    public function getOptions(): array
    {
        return $this->options;
    }

    /**
     * Stream PDF to browser
     * 
     * @param string $content
     * @param string $filename
     * @return void
     */
    public function stream(string $content, string $filename): void
    {
        header('Content-Type: application/pdf');
        header('Content-Disposition: inline; filename="' . $filename . '"');
        echo $content;
        exit;
    }
}