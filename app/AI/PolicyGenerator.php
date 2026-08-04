<?php
/**
 * AI Banking GRC Platform - Policy Generator
 * 
 * @package    AI-Banking-GRC-Platform
 * @subpackage app/AI
 * @version    1.0.0
 * @author     GRC Platform Team
 * @copyright  2026 AI Banking GRC Platform
 * @license    Proprietary
 * 
 * This class provides AI policy generation:
 * - AML Policy
 * - Risk Policy
 * - Audit Policy
 * - Compliance Policy
 * - Security Policy
 * - Policy update
 * - Policy export
 */

declare(strict_types=1);

namespace App\AI;

use App\Libraries\Logger;
use App\Libraries\Cache;

class PolicyGenerator
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
     * @var array Policy templates
     */
    private array $policyTemplates = [];

    /**
     * Constructor
     */
    public function __construct()
    {
        $this->logger = new Logger();
        $this->cache = new Cache();
        $this->promptEngine = new PromptEngine();
        $this->openAI = new OpenAI();
        $this->loadTemplates();
    }

    /**
     * Load policy templates
     */
    private function loadTemplates(): void
    {
        $this->policyTemplates = [
            'aml' => [
                'title' => 'Anti-Money Laundering (AML) Policy',
                'sections' => [
                    'purpose' => 'Establish framework for AML compliance',
                    'scope' => 'Applies to all employees and operations',
                    'policy' => 'Commitment to AML compliance',
                    'requirements' => ['KYC', 'Transaction Monitoring', 'Reporting'],
                    'responsibilities' => ['Compliance Officer', 'Branch Managers', 'All Staff'],
                    'compliance' => 'Consequences of non-compliance'
                ]
            ],
            'risk' => [
                'title' => 'Risk Management Policy',
                'sections' => [
                    'purpose' => 'Establish risk management framework',
                    'scope' => 'Enterprise-wide risk management',
                    'policy' => 'Risk management commitment',
                    'requirements' => ['Risk Identification', 'Risk Assessment', 'Risk Mitigation'],
                    'responsibilities' => ['Risk Manager', 'Department Heads', 'All Staff'],
                    'compliance' => 'Risk management compliance'
                ]
            ],
            'audit' => [
                'title' => 'Internal Audit Policy',
                'sections' => [
                    'purpose' => 'Establish internal audit framework',
                    'scope' => 'Internal audit activities',
                    'policy' => 'Audit independence and objectivity',
                    'requirements' => ['Audit Planning', 'Audit Execution', 'Reporting'],
                    'responsibilities' => ['Audit Committee', 'Internal Auditor', 'Department Heads'],
                    'compliance' => 'Audit compliance'
                ]
            ],
            'compliance' => [
                'title' => 'Compliance Policy',
                'sections' => [
                    'purpose' => 'Establish compliance framework',
                    'scope' => 'Regulatory compliance',
                    'policy' => 'Compliance commitment',
                    'requirements' => ['Regulatory Monitoring', 'Compliance Training', 'Reporting'],
                    'responsibilities' => ['Compliance Officer', 'Department Heads', 'All Staff'],
                    'compliance' => 'Compliance consequences'
                ]
            ],
            'security' => [
                'title' => 'Information Security Policy',
                'sections' => [
                    'purpose' => 'Establish information security framework',
                    'scope' => 'Information security management',
                    'policy' => 'Security commitment',
                    'requirements' => ['Access Control', 'Data Protection', 'Incident Response'],
                    'responsibilities' => ['CISO', 'IT Department', 'All Staff'],
                    'compliance' => 'Security compliance'
                ]
            ]
        ];
    }

    /**
     * Generate a policy
     * 
     * @param string $type
     * @param array $requirements
     * @param array $options
     * @return array
     */
    public function generate(string $type, array $requirements = [], array $options = []): array
    {
        try {
            $cacheKey = 'policy_' . $type . '_' . md5(json_encode($requirements));
            if ($this->cache->has($cacheKey)) {
                $this->logger->debug('Policy from cache');
                return $this->cache->get($cacheKey);
            }

            $template = $this->getTemplate($type);
            if (!$template) {
                return $this->errorResponse("Policy template not found: {$type}");
            }

            $variables = [
                'type' => $type,
                'requirements' => json_encode($requirements, JSON_PRETTY_PRINT),
                'company_name' => $options['company_name'] ?? APP_NAME ?? 'Our Organization',
                'date' => date('F d, Y')
            ];

            $prompt = $this->buildPolicyPrompt($template, $variables, $options);

            $result = $this->openAI->completion($prompt, [
                'temperature' => 0.3,
                'max_tokens' => 4000
            ]);

            if (!$result['success']) {
                return $this->errorResponse('Failed to generate policy: ' . ($result['error'] ?? 'Unknown error'));
            }

            $policy = $this->formatPolicy($result['content'], $template, $variables);

            $response = [
                'success' => true,
                'policy' => $policy,
                'title' => $template['title'],
                'type' => $type,
                'version' => '1.0',
                'timestamp' => time()
            ];

            $this->cache->put($cacheKey, $response, 7200);

            return $response;

        } catch (\Exception $e) {
            $this->logger->error('Policy generation error: ' . $e->getMessage());
            return $this->errorResponse($e->getMessage());
        }
    }

    /**
     * Update a policy
     * 
     * @param string $policy
     * @param array $updates
     * @return array
     */
    public function update(string $policy, array $updates): array
    {
        try {
            $prompt = "Update the following policy based on the requested changes:\n\n";
            $prompt .= "Current Policy:\n" . $policy . "\n\n";
            $prompt .= "Requested Updates:\n" . json_encode($updates, JSON_PRETTY_PRINT) . "\n\n";
            $prompt .= "Provide the updated policy with all changes applied.";

            $result = $this->openAI->completion($prompt, [
                'temperature' => 0.3,
                'max_tokens' => 4000
            ]);

            if (!$result['success']) {
                return $this->errorResponse('Failed to update policy: ' . ($result['error'] ?? 'Unknown error'));
            }

            return [
                'success' => true,
                'policy' => $result['content'],
                'version' => '1.1',
                'timestamp' => time()
            ];

        } catch (\Exception $e) {
            $this->logger->error('Policy update error: ' . $e->getMessage());
            return $this->errorResponse($e->getMessage());
        }
    }

    /**
     * Export policy
     * 
     * @param string $policy
     * @param string $format
     * @return string
     */
    public function export(string $policy, string $format = 'html'): string
    {
        if ($format === 'html') {
            return $this->exportHTML($policy);
        } elseif ($format === 'markdown') {
            return $this->exportMarkdown($policy);
        }

        return $policy;
    }

    /**
     * Export as HTML
     * 
     * @param string $policy
     * @return string
     */
    private function exportHTML(string $policy): string
    {
        $html = '<!DOCTYPE html><html><head>';
        $html .= '<meta charset="UTF-8">';
        $html .= '<title>Generated Policy</title>';
        $html .= '<style>';
        $html .= 'body{font-family: Arial, sans-serif; padding: 40px; max-width: 900px; margin: 0 auto;}';
        $html .= 'h1{color: #0B3D91; border-bottom: 2px solid #2563EB; padding-bottom: 10px;}';
        $html .= 'h2{color: #2563EB; margin-top: 30px;}';
        $html .= 'p{line-height: 1.6;}';
        $html .= 'ul{line-height: 1.8;}';
        $html .= 'hr{margin: 30px 0; border: 1px solid #E2E8F0;}';
        $html .= '.footer{text-align: center; color: #64748B; font-size: 12px; margin-top: 40px;}';
        $html .= '</style>';
        $html .= '</head><body>';
        $html .= nl2br($policy);
        $html .= '<div class="footer">Generated by AI Banking GRC Platform</div>';
        $html .= '</body></html>';

        return $html;
    }

    /**
     * Export as Markdown
     * 
     * @param string $policy
     * @return string
     */
    private function exportMarkdown(string $policy): string
    {
        return "# Generated Policy\n\n" . $policy . "\n\n---\n*Generated by AI Banking GRC Platform*";
    }

    /**
     * Get policy template
     * 
     * @param string $type
     * @return array|null
     */
    public function getTemplate(string $type): ?array
    {
        return $this->policyTemplates[$type] ?? null;
    }

    /**
     * Build policy prompt
     * 
     * @param array $template
     * @param array $variables
     * @param array $options
     * @return string
     */
    private function buildPolicyPrompt(array $template, array $variables, array $options): string
    {
        $prompt = "Generate a professional banking policy document based on the following template:\n\n";
        $prompt .= "Policy Title: " . $template['title'] . "\n\n";

        $prompt .= "Sections:\n";
        foreach ($template['sections'] as $key => $value) {
            $prompt .= "- " . ucfirst($key) . ": " . (is_array($value) ? implode(', ', $value) : $value) . "\n";
        }

        if (!empty($variables['requirements'])) {
            $prompt .= "\nSpecific Requirements:\n" . $variables['requirements'] . "\n";
        }

        $prompt .= "\nCompany: " . $variables['company_name'] . "\n";
        $prompt .= "Date: " . $variables['date'] . "\n\n";

        $prompt .= "Generate a comprehensive, professional policy document following banking industry standards.";
        $prompt .= " Include all sections with detailed content.";

        return $prompt;
    }

    /**
     * Format policy
     * 
     * @param string $content
     * @param array $template
     * @param array $variables
     * @return string
     */
    private function formatPolicy(string $content, array $template, array $variables): string
    {
        $policy = "## " . $template['title'] . "\n\n";
        $policy .= "**Policy Number:** " . date('Y') . '-' . strtoupper(substr($template['title'], 0, 3)) . '-001' . "\n";
        $policy .= "**Version:** 1.0\n";
        $policy .= "**Effective Date:** " . $variables['date'] . "\n";
        $policy .= "**Approved By:** Board of Directors\n\n";
        $policy .= "---\n\n";
        $policy .= $content;

        return $policy;
    }

    /**
     * Get available policy types
     * 
     * @return array
     */
    public function getPolicyTypes(): array
    {
        return array_keys($this->policyTemplates);
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