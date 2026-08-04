<?php
/**
 * AI Banking GRC Platform - Policy Generator Service
 * 
 * @package    AI-Banking-GRC-Platform
 * @subpackage app/Services
 * @version    1.0.0
 * @author     GRC Platform Team
 * @copyright  2026 AI Banking GRC Platform
 * @license    Proprietary
 * 
 * This service handles AI policy generation:
 * - Generate AML policies
 * - Generate risk policies
 * - Generate compliance policies
 * - Generate audit policies
 * - Generate cyber security policies
 * - Policy update and export
 */

declare(strict_types=1);

namespace App\Services;

use App\AI\PolicyGenerator;
use App\Models\Policy;
use App\Models\ActivityLog;
use App\Libraries\Logger;
use App\Libraries\Validator;
use App\Libraries\Cache;

class PolicyGeneratorService
{
    /**
     * @var PolicyGenerator Policy generator AI
     */
    private PolicyGenerator $policyGenerator;

    /**
     * @var Policy Policy model
     */
    private Policy $policyModel;

    /**
     * @var ActivityLog Activity log model
     */
    private ActivityLog $activityLogModel;

    /**
     * @var Logger Logger instance
     */
    private Logger $logger;

    /**
     * @var Validator Validator instance
     */
    private Validator $validator;

    /**
     * @var Cache Cache instance
     */
    private Cache $cache;

    /**
     * @var array Policy types
     */
    private array $policyTypes = [
        'aml' => 'Anti-Money Laundering',
        'risk' => 'Risk Management',
        'compliance' => 'Compliance',
        'audit' => 'Internal Audit',
        'security' => 'Cyber Security'
    ];

    /**
     * Constructor
     */
    public function __construct()
    {
        $this->policyGenerator = new PolicyGenerator();
        $this->policyModel = new Policy();
        $this->activityLogModel = new ActivityLog();
        $this->logger = new Logger();
        $this->validator = new Validator();
        $this->cache = new Cache();
    }

    /**
     * Generate a policy
     * 
     * @param string $type
     * @param array $requirements
     * @param int $userId
     * @param array $options
     * @return array
     */
    public function generate(string $type, array $requirements, int $userId, array $options = []): array
    {
        try {
            if (!isset($this->policyTypes[$type])) {
                return $this->errorResponse('Invalid policy type. Available: ' . implode(', ', array_keys($this->policyTypes)));
            }

            $cacheKey = 'policy_gen_' . $type . '_' . md5(json_encode($requirements));
            if ($this->cache->has($cacheKey)) {
                $cached = $this->cache->get($cacheKey);
                if ($cached['success']) {
                    return $cached;
                }
            }

            // Generate policy
            $result = $this->policyGenerator->generate($type, $requirements, $options);

            if (!$result['success']) {
                return $this->errorResponse('Policy generation failed: ' . ($result['error'] ?? 'Unknown error'));
            }

            // Save to cache
            $this->cache->put($cacheKey, $result, 7200);

            // Log activity
            $this->activityLogModel->logAction($userId, 'policy_generate', 'ai',
                "Generated {$type} policy using AI");

            $this->logger->info('Policy generated', [
                'type' => $type,
                'user_id' => $userId,
                'title' => $result['title'] ?? 'Generated Policy'
            ]);

            return [
                'success' => true,
                'policy' => $result['policy'],
                'title' => $result['title'] ?? $this->policyTypes[$type] . ' Policy',
                'type' => $type,
                'version' => $result['version'] ?? '1.0',
                'generated_at' => date('Y-m-d H:i:s')
            ];

        } catch (\Exception $e) {
            $this->logger->error('Generate policy error: ' . $e->getMessage());
            return $this->errorResponse('An error occurred generating policy.');
        }
    }

    /**
     * Update a policy
     * 
     * @param string $policy
     * @param array $updates
     * @param int $userId
     * @return array
     */
    public function update(string $policy, array $updates, int $userId): array
    {
        try {
            $result = $this->policyGenerator->update($policy, $updates);

            if (!$result['success']) {
                return $this->errorResponse('Policy update failed: ' . ($result['error'] ?? 'Unknown error'));
            }

            $this->activityLogModel->logAction($userId, 'policy_update', 'ai',
                "Updated policy using AI");

            return [
                'success' => true,
                'policy' => $result['policy'],
                'version' => $result['version'] ?? '1.1',
                'updated_at' => date('Y-m-d H:i:s')
            ];

        } catch (\Exception $e) {
            $this->logger->error('Update policy error: ' . $e->getMessage());
            return $this->errorResponse('An error occurred updating policy.');
        }
    }

    /**
     * Download policy
     * 
     * @param string $policy
     * @param string $format
     * @param int $userId
     * @return array
     */
    public function download(string $policy, string $format, int $userId): array
    {
        try {
            $content = $this->policyGenerator->export($policy, $format);

            $this->activityLogModel->logAction($userId, 'policy_download', 'ai',
                "Downloaded policy in {$format} format");

            $this->logger->info('Policy downloaded', [
                'format' => $format,
                'user_id' => $userId
            ]);

            return [
                'success' => true,
                'content' => $content,
                'format' => $format,
                'filename' => 'policy_' . time() . '.' . ($format === 'html' ? 'html' : ($format === 'markdown' ? 'md' : 'txt'))
            ];

        } catch (\Exception $e) {
            $this->logger->error('Download policy error: ' . $e->getMessage());
            return $this->errorResponse('An error occurred downloading policy.');
        }
    }

    /**
     * Get available policy types
     * 
     * @return array
     */
    public function getPolicyTypes(): array
    {
        return $this->policyTypes;
    }

    /**
     * Get policy templates
     * 
     * @param string $type
     * @return array
     */
    public function getTemplates(string $type = ''): array
    {
        if ($type) {
            $template = $this->policyGenerator->getTemplate($type);
            return $template ? [$type => $template] : [];
        }

        return $this->policyGenerator->getPolicyTypes();
    }

    /**
     * Save generated policy
     * 
     * @param string $type
     * @param string $content
     * @param int $userId
     * @return array
     */
    public function save(string $type, string $content, int $userId): array
    {
        try {
            $policyData = [
                'title' => $this->policyTypes[$type] . ' Policy',
                'category' => $type,
                'description' => $content,
                'version' => '1.0',
                'status' => 'draft',
                'effective_date' => date('Y-m-d'),
                'created_by' => $userId,
                'created_at' => date('Y-m-d H:i:s')
            ];

            $policyId = $this->policyModel->create($policyData);

            if (!$policyId) {
                return $this->errorResponse('Failed to save policy.');
            }

            $this->activityLogModel->logAction($userId, 'policy_save', 'ai',
                "Saved generated {$type} policy to database");

            return [
                'success' => true,
                'policy_id' => $policyId,
                'message' => 'Policy saved successfully.'
            ];

        } catch (\Exception $e) {
            $this->logger->error('Save policy error: ' . $e->getMessage());
            return $this->errorResponse('An error occurred saving policy.');
        }
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