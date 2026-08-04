<?php
/**
 * AI Banking GRC Platform - Prompt Engine
 * 
 * @package    AI-Banking-GRC-Platform
 * @subpackage app/AI
 * @version    1.0.0
 * @author     GRC Platform Team
 * @copyright  2026 AI Banking GRC Platform
 * @license    Proprietary
 * 
 * This class manages AI prompts:
 * - Prompt templates
 * - Prompt variables
 * - Prompt history
 * - Prompt optimization
 * - Banking-specific prompts
 */

declare(strict_types=1);

namespace App\AI;

use App\Libraries\Logger;
use App\Libraries\Cache;

class PromptEngine
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
     * @var array Prompt templates
     */
    private array $templates = [];

    /**
     * @var array Prompt history
     */
    private array $history = [];

    /**
     * @var int Max history size
     */
    private int $maxHistory = 100;

    /**
     * Constructor
     */
    public function __construct()
    {
        $this->logger = new Logger();
        $this->cache = new Cache();
        $this->loadTemplates();
    }

    /**
     * Load prompt templates
     * 
     * @return void
     */
    private function loadTemplates(): void
    {
        $this->templates = [
            'compliance_analysis' => [
                'system' => 'You are a banking compliance expert. Analyze the following compliance data and provide insights.',
                'template' => "Compliance Data:\n{data}\n\nProvide a detailed compliance analysis including:\n1. Compliance status\n2. Key findings\n3. Recommendations\n4. Risk level"
            ],
            'risk_assessment' => [
                'system' => 'You are a banking risk management expert. Assess the following risk scenario.',
                'template' => "Risk Description:\n{description}\n\nProvide a comprehensive risk assessment including:\n1. Risk category\n2. Likelihood (1-5)\n3. Impact (1-5)\n4. Risk score\n5. Mitigation strategies"
            ],
            'policy_generation' => [
                'system' => 'You are a banking policy expert. Generate a professional policy document.',
                'template' => "Policy Type: {type}\nRequirements:\n{requirements}\n\nGenerate a comprehensive policy document including:\n1. Purpose\n2. Scope\n3. Policy Statement\n4. Key Requirements\n5. Roles and Responsibilities\n6. Compliance and Enforcement"
            ],
            'audit_finding' => [
                'system' => 'You are a banking audit expert. Analyze audit findings.',
                'template' => "Audit Finding:\n{finding}\n\nProvide:\n1. Severity assessment\n2. Root cause analysis\n3. Recommendations\n4. Corrective actions"
            ],
            'gap_analysis' => [
                'system' => 'You are a banking compliance expert. Identify compliance gaps.',
                'template' => "Current State:\n{current}\n\nRequired State:\n{required}\n\nIdentify:\n1. Compliance gaps\n2. Priority level\n3. Recommendations\n4. Implementation timeline"
            ],
            'sbp_circular' => [
                'system' => 'You are a banking regulatory expert. Analyze SBP circular.',
                'template' => "SBP Circular:\n{circular}\n\nProvide:\n1. Summary\n2. Key requirements\n3. Impact analysis\n4. Implementation plan\n5. Compliance checklist"
            ],
            'aml_check' => [
                'system' => 'You are a banking AML/CFT expert. Analyze transaction for AML risk.',
                'template' => "Transaction Details:\n{transaction}\n\nProvide:\n1. AML risk assessment\n2. Suspicious indicators\n3. Risk score\n4. Recommended actions"
            ],
            'basel_analysis' => [
                'system' => 'You are a banking Basel compliance expert. Analyze Basel compliance.',
                'template' => "Banking Data:\n{data}\n\nProvide:\n1. Basel III compliance assessment\n2. Capital adequacy analysis\n3. Liquidity analysis\n4. Recommendations\n5. Risk exposure"
            ]
        ];
    }

    /**
     * Build a prompt
     * 
     * @param string $type
     * @param array $variables
     * @param array $options
     * @return array
     */
    public function build(string $type, array $variables = [], array $options = []): array
    {
        try {
            $template = $this->getTemplate($type);
            if (!$template) {
                throw new \RuntimeException("Prompt template not found: {$type}");
            }

            // Build system prompt
            $system = $template['system'];
            if (isset($options['system'])) {
                $system = $options['system'];
            }

            // Replace variables in template
            $content = $template['template'];
            foreach ($variables as $key => $value) {
                $content = str_replace('{' . $key . '}', $value, $content);
            }

            // Add context if provided
            if (!empty($options['context'])) {
                $content .= "\n\nContext: " . $options['context'];
            }

            // Add examples if provided
            if (!empty($options['examples'])) {
                $content .= "\n\nExamples:\n" . $options['examples'];
            }

            // Build messages array
            $messages = [];
            if ($system) {
                $messages[] = ['role' => 'system', 'content' => $system];
            }
            $messages[] = ['role' => 'user', 'content' => $content];

            // Add history if provided
            if (!empty($options['history'])) {
                $messages = array_merge($messages, $options['history']);
            }

            // Log prompt
            $this->logPrompt($type, $messages, $variables);

            // Cache built prompt
            if (isset($options['cache']) && $options['cache']) {
                $cacheKey = 'prompt_' . md5($type . json_encode($variables));
                $this->cache->put($cacheKey, $messages, 3600);
            }

            return $messages;

        } catch (\Exception $e) {
            $this->logger->error('Prompt build error: ' . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Get a template
     * 
     * @param string $type
     * @return array|null
     */
    public function getTemplate(string $type): ?array
    {
        return $this->templates[$type] ?? null;
    }

    /**
     * Add a template
     * 
     * @param string $type
     * @param string $system
     * @param string $template
     * @return void
     */
    public function addTemplate(string $type, string $system, string $template): void
    {
        $this->templates[$type] = [
            'system' => $system,
            'template' => $template
        ];
    }

    /**
     * Execute a prompt
     * 
     * @param string $type
     * @param array $variables
     * @param array $options
     * @return array
     */
    public function execute(string $type, array $variables = [], array $options = []): array
    {
        try {
            $openAI = new OpenAI();
            $messages = $this->build($type, $variables, $options);

            $result = $openAI->chat($messages, $options);

            // Store history
            $this->addHistory($type, $messages, $result);

            return $result;

        } catch (\Exception $e) {
            $this->logger->error('Prompt execute error: ' . $e->getMessage());
            return [
                'success' => false,
                'error' => $e->getMessage()
            ];
        }
    }

    /**
     * Optimize prompt
     * 
     * @param string $prompt
     * @param array $options
     * @return string
     */
    public function optimize(string $prompt, array $options = []): string
    {
        // Remove excessive whitespace
        $prompt = preg_replace('/\s+/', ' ', $prompt);

        // Truncate if too long
        $maxLength = $options['max_length'] ?? 4000;
        if (strlen($prompt) > $maxLength) {
            $prompt = substr($prompt, 0, $maxLength) . '...';
        }

        // Add banking context if missing
        if (!str_contains($prompt, 'banking') && !str_contains($prompt, 'finance')) {
            $prompt = "Banking context: " . $prompt;
        }

        return $prompt;
    }

    /**
     * Add to history
     * 
     * @param string $type
     * @param array $messages
     * @param array $result
     * @return void
     */
    private function addHistory(string $type, array $messages, array $result): void
    {
        $this->history[] = [
            'type' => $type,
            'messages' => $messages,
            'result' => $result,
            'timestamp' => time()
        ];

        if (count($this->history) > $this->maxHistory) {
            array_shift($this->history);
        }
    }

    /**
     * Get history
     * 
     * @param int $limit
     * @return array
     */
    public function getHistory(int $limit = 10): array
    {
        return array_slice($this->history, -$limit);
    }

    /**
     * Clear history
     * 
     * @return void
     */
    public function clearHistory(): void
    {
        $this->history = [];
    }

    /**
     * Log prompt
     * 
     * @param string $type
     * @param array $messages
     * @param array $variables
     * @return void
     */
    private function logPrompt(string $type, array $messages, array $variables): void
    {
        $this->logger->debug('Prompt executed', [
            'type' => $type,
            'variables' => $variables,
            'message_count' => count($messages)
        ]);
    }

    /**
     * Get template variables
     * 
     * @param string $type
     * @return array
     */
    public function getTemplateVariables(string $type): array
    {
        $template = $this->getTemplate($type);
        if (!$template) {
            return [];
        }

        preg_match_all('/\{([^}]+)\}/', $template['template'], $matches);
        return $matches[1] ?? [];
    }

    /**
     * Validate prompt variables
     * 
     * @param string $type
     * @param array $variables
     * @return bool
     */
    public function validateVariables(string $type, array $variables): bool
    {
        $required = $this->getTemplateVariables($type);
        foreach ($required as $var) {
            if (!isset($variables[$var]) || empty($variables[$var])) {
                return false;
            }
        }
        return true;
    }
}