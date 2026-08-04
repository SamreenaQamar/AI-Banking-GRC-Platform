<?php
/**
 * AI Banking GRC Platform - Settings Model
 * 
 * @package    AI-Banking-GRC-Platform
 * @subpackage app/Models
 * @version    1.0.0
 * @author     GRC Platform Team
 * @copyright  2026 AI Banking GRC Platform
 * @license    Proprietary
 * 
 * This model handles system settings management:
 * - CRUD operations for settings
 * - Get/Set settings by key
 * - Group-based settings
 * - Cache integration
 * - Setting validation
 */

declare(strict_types=1);

namespace App\Models;

use PDO;
use App\Helpers\LogHelper;
use App\Helpers\CacheHelper;

class Setting extends BaseModel
{
    /**
     * Table name
     * @var string
     */
    protected string $table = 'settings';

    /**
     * Fillable fields
     * @var array
     */
    protected array $fillable = [
        'setting_key',
        'setting_value',
        'setting_group',
        'setting_type',
        'description',
        'is_editable',
        'is_encrypted',
        'created_by'
    ];

    /**
     * Cache instance
     * @var CacheHelper|null
     */
    private ?CacheHelper $cache = null;

    /**
     * Settings cache
     * @var array
     */
    private static array $settingsCache = [];

    /**
     * Constructor
     */
    public function __construct()
    {
        parent::__construct();
        $this->cache = new CacheHelper();
    }

    /**
     * Get a setting value by key
     * 
     * @param string $key
     * @param mixed $default
     * @return mixed
     */
    public function get(string $key, $default = null)
    {
        // Check memory cache first
        if (isset(self::$settingsCache[$key])) {
            return self::$settingsCache[$key];
        }

        // Check file cache
        if ($this->cache && $this->cache->has('setting_' . $key)) {
            $value = $this->cache->get('setting_' . $key);
            self::$settingsCache[$key] = $value;
            return $value;
        }

        // Get from database
        $sql = "SELECT setting_value, setting_type FROM {$this->table} 
                WHERE setting_key = :key AND deleted_at IS NULL";
        $stmt = $this->db->prepare($sql);
        $stmt->execute(['key' => $key]);
        $result = $stmt->fetch(PDO::FETCH_OBJ);

        if (!$result) {
            return $default;
        }

        $value = $this->castValue($result->setting_value, $result->setting_type);
        
        // Store in caches
        self::$settingsCache[$key] = $value;
        if ($this->cache) {
            $this->cache->set('setting_' . $key, $value, 3600);
        }

        return $value;
    }

    /**
     * Get all settings grouped by group
     * 
     * @param string|null $group
     * @return array
     */
    public function getAllGrouped(?string $group = null): array
    {
        $sql = "SELECT setting_key, setting_value, setting_type, setting_group, description 
                FROM {$this->table} 
                WHERE deleted_at IS NULL";
        
        if ($group) {
            $sql .= " AND setting_group = :group";
        }
        
        $sql .= " ORDER BY setting_group, setting_key";

        $stmt = $this->db->prepare($sql);
        if ($group) {
            $stmt->execute(['group' => $group]);
        } else {
            $stmt->execute();
        }

        $results = $stmt->fetchAll(PDO::FETCH_OBJ);
        $grouped = [];

        foreach ($results as $row) {
            $value = $this->castValue($row->setting_value, $row->setting_type);
            if (!isset($grouped[$row->setting_group])) {
                $grouped[$row->setting_group] = [];
            }
            $grouped[$row->setting_group][$row->setting_key] = $value;
        }

        return $grouped;
    }

    /**
     * Get settings by group
     * 
     * @param string $group
     * @return array
     */
    public function getByGroup(string $group): array
    {
        $all = $this->getAllGrouped($group);
        return $all[$group] ?? [];
    }

    /**
     * Set a setting value
     * 
     * @param string $key
     * @param mixed $value
     * @param string $group
     * @param string $description
     * @return bool
     */
    public function set(string $key, $value, string $group = 'general', string $description = ''): bool
    {
        // Determine setting type
        $type = $this->detectType($value);
        $value = $this->castToString($value);

        // Check if setting exists
        $sql = "SELECT id FROM {$this->table} WHERE setting_key = :key AND deleted_at IS NULL";
        $stmt = $this->db->prepare($sql);
        $stmt->execute(['key' => $key]);
        $existing = $stmt->fetch();

        try {
            if ($existing) {
                // Update existing setting
                $sql = "UPDATE {$this->table} 
                        SET setting_value = :value, 
                            setting_type = :type,
                            setting_group = :group,
                            description = :description,
                            updated_at = NOW()
                        WHERE setting_key = :key";
                $result = $this->db->prepare($sql)->execute([
                    'value' => $value,
                    'type' => $type,
                    'group' => $group,
                    'description' => $description,
                    'key' => $key
                ]);
            } else {
                // Create new setting
                $result = $this->create([
                    'setting_key' => $key,
                    'setting_value' => $value,
                    'setting_type' => $type,
                    'setting_group' => $group,
                    'description' => $description
                ]);
            }

            if ($result) {
                // Clear caches
                $this->clearCache($key);
                return true;
            }

            return false;

        } catch (\Exception $e) {
            LogHelper::error('Failed to set setting: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Update settings in bulk by group
     * 
     * @param string $group
     * @param array $settings
     * @return bool
     */
    public function updateGroup(string $group, array $settings): bool
    {
        $success = true;
        foreach ($settings as $key => $value) {
            if (!$this->set($key, $value, $group)) {
                $success = false;
            }
        }
        return $success;
    }

    /**
     * Delete a setting
     * 
     * @param string $key
     * @return bool
     */
    public function delete(string $key): bool
    {
        try {
            $result = $this->softDeleteByKey($key);
            if ($result) {
                $this->clearCache($key);
                return true;
            }
            return false;
        } catch (\Exception $e) {
            LogHelper::error('Failed to delete setting: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Soft delete setting by key
     * 
     * @param string $key
     * @return bool
     */
    private function softDeleteByKey(string $key): bool
    {
        $sql = "UPDATE {$this->table} 
                SET deleted_at = NOW() 
                WHERE setting_key = :key";
        $stmt = $this->db->prepare($sql);
        return $stmt->execute(['key' => $key]);
    }

    /**
     * Clear setting cache
     * 
     * @param string $key
     * @return void
     */
    private function clearCache(string $key): void
    {
        unset(self::$settingsCache[$key]);
        if ($this->cache) {
            $this->cache->delete('setting_' . $key);
        }
    }

    /**
     * Clear all settings cache
     * 
     * @return void
     */
    public function clearAllCache(): void
    {
        self::$settingsCache = [];
        if ($this->cache) {
            $this->cache->clear();
        }
    }

    /**
     * Cast value based on type
     * 
     * @param string $value
     * @param string $type
     * @return mixed
     */
    private function castValue(string $value, string $type)
    {
        return match($type) {
            'integer' => (int)$value,
            'boolean' => filter_var($value, FILTER_VALIDATE_BOOLEAN),
            'json' => json_decode($value, true),
            'email' => $value,
            'url' => $value,
            'text' => $value,
            default => $value
        };
    }

    /**
     * Cast value to string for storage
     * 
     * @param mixed $value
     * @return string
     */
    private function castToString($value): string
    {
        if (is_bool($value)) {
            return $value ? 'true' : 'false';
        }
        if (is_array($value) || is_object($value)) {
            return json_encode($value);
        }
        return (string)$value;
    }

    /**
     * Detect setting type from value
     * 
     * @param mixed $value
     * @return string
     */
    private function detectType($value): string
    {
        if (is_bool($value)) {
            return 'boolean';
        }
        if (is_int($value)) {
            return 'integer';
        }
        if (is_array($value) || is_object($value)) {
            return 'json';
        }
        if (filter_var($value, FILTER_VALIDATE_EMAIL)) {
            return 'email';
        }
        if (filter_var($value, FILTER_VALIDATE_URL)) {
            return 'url';
        }
        if (is_string($value) && strlen($value) > 255) {
            return 'text';
        }
        return 'string';
    }

    /**
     * Check if a setting exists
     * 
     * @param string $key
     * @return bool
     */
    public function exists(string $key): bool
    {
        $sql = "SELECT COUNT(*) FROM {$this->table} 
                WHERE setting_key = :key AND deleted_at IS NULL";
        $stmt = $this->db->prepare($sql);
        $stmt->execute(['key' => $key]);
        return (int)$stmt->fetchColumn() > 0;
    }

    /**
     * Get all setting keys
     * 
     * @return array
     */
    public function getAllKeys(): array
    {
        $sql = "SELECT setting_key FROM {$this->table} WHERE deleted_at IS NULL";
        $stmt = $this->db->prepare($sql);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_COLUMN);
    }

    /**
     * Get settings count
     * 
     * @param string|null $group
     * @return int
     */
    public function count(?string $group = null): int
    {
        $sql = "SELECT COUNT(*) FROM {$this->table} WHERE deleted_at IS NULL";
        if ($group) {
            $sql .= " AND setting_group = :group";
        }
        $stmt = $this->db->prepare($sql);
        if ($group) {
            $stmt->execute(['group' => $group]);
        } else {
            $stmt->execute();
        }
        return (int)$stmt->fetchColumn();
    }

    /**
     * Get settings for export
     * 
     * @param string|null $group
     * @return array
     */
    public function getForExport(?string $group = null): array
    {
        $sql = "SELECT setting_key, setting_value, setting_type, setting_group, description 
                FROM {$this->table} 
                WHERE deleted_at IS NULL";
        
        if ($group) {
            $sql .= " AND setting_group = :group";
        }

        $sql .= " ORDER BY setting_group, setting_key";

        $stmt = $this->db->prepare($sql);
        if ($group) {
            $stmt->execute(['group' => $group]);
        } else {
            $stmt->execute();
        }

        return $stmt->fetchAll(PDO::FETCH_OBJ);
    }

    /**
     * Import settings from array
     * 
     * @param array $settings
     * @param bool $overwrite
     * @return array Import results
     */
    public function import(array $settings, bool $overwrite = false): array
    {
        $results = [
            'total' => count($settings),
            'imported' => 0,
            'skipped' => 0,
            'errors' => 0,
            'error_messages' => []
        ];

        foreach ($settings as $setting) {
            try {
                if (!isset($setting['setting_key']) || !isset($setting['setting_value'])) {
                    $results['errors']++;
                    $results['error_messages'][] = 'Missing key or value in setting';
                    continue;
                }

                if (!$overwrite && $this->exists($setting['setting_key'])) {
                    $results['skipped']++;
                    continue;
                }

                $result = $this->set(
                    $setting['setting_key'],
                    $setting['setting_value'],
                    $setting['setting_group'] ?? 'general',
                    $setting['description'] ?? ''
                );

                if ($result) {
                    $results['imported']++;
                } else {
                    $results['errors']++;
                }

            } catch (\Exception $e) {
                $results['errors']++;
                $results['error_messages'][] = $e->getMessage();
            }
        }

        return $results;
    }

    /**
     * Get default settings for a group
     * 
     * @param string $group
     * @return array
     */
    public function getDefaults(string $group): array
    {
        $defaults = [
            'general' => [
                'app_name' => 'AI Banking GRC Platform',
                'app_version' => '1.0.0',
                'app_timezone' => 'Asia/Karachi',
                'app_locale' => 'en',
                'maintenance_mode' => false
            ],
            'company' => [
                'company_name' => 'AI Banking GRC Solutions',
                'company_email' => 'info@grc-platform.com',
                'company_phone' => '+92-21-1234567',
                'company_address' => '',
                'company_logo' => ''
            ],
            'security' => [
                'session_lifetime' => 3600,
                'max_login_attempts' => 5,
                'lockout_duration' => 15,
                'password_min_length' => 8,
                'require_special_chars' => true,
                'two_factor_enabled' => false
            ],
            'email' => [
                'smtp_host' => 'smtp.gmail.com',
                'smtp_port' => 587,
                'smtp_username' => '',
                'smtp_password' => '',
                'smtp_encryption' => 'tls',
                'from_address' => 'noreply@grc-platform.com',
                'from_name' => 'AI Banking GRC Platform'
            ],
            'api' => [
                'api_enabled' => true,
                'rate_limit' => 100,
                'rate_limit_window' => 60,
                'api_version' => 'v1'
            ],
            'ai' => [
                'ai_enabled' => true,
                'ai_provider' => 'openai',
                'ai_model' => 'gpt-4',
                'ai_api_key' => '',
                'ai_max_tokens' => 4096,
                'ai_temperature' => 0.7
            ],
            'notifications' => [
                'in_app_enabled' => true,
                'email_enabled' => true,
                'sms_enabled' => false,
                'push_enabled' => false,
                'retention_days' => 90
            ],
            'system' => [
                'audit_log_retention' => 365,
                'max_file_size' => 10485760,
                'allowed_file_types' => 'pdf,docx,xlsx,jpeg,png,jpg',
                'pagination_default' => 15
            ]
        ];

        return $defaults[$group] ?? [];
    }

    /**
     * Initialize default settings for a group
     * 
     * @param string $group
     * @param int $createdBy
     * @return int Number of settings created
     */
    public function initializeDefaults(string $group, int $createdBy = 1): int
    {
        $defaults = $this->getDefaults($group);
        $count = 0;

        foreach ($defaults as $key => $value) {
            if (!$this->exists($key)) {
                $this->set($key, $value, $group, 'Default ' . $key);
                $count++;
            }
        }

        return $count;
    }

    /**
     * Get setting with description
     * 
     * @param string $key
     * @return object|null
     */
    public function getWithDescription(string $key): ?object
    {
        $sql = "SELECT setting_key, setting_value, setting_type, setting_group, description, is_editable 
                FROM {$this->table} 
                WHERE setting_key = :key AND deleted_at IS NULL";
        $stmt = $this->db->prepare($sql);
        $stmt->execute(['key' => $key]);
        $result = $stmt->fetch(PDO::FETCH_OBJ);

        if ($result) {
            $result->setting_value = $this->castValue($result->setting_value, $result->setting_type);
        }

        return $result;
    }

    /**
     * Get all settings with descriptions
     * 
     * @param string|null $group
     * @return array
     */
    public function getAllWithDescriptions(?string $group = null): array
    {
        $sql = "SELECT setting_key, setting_value, setting_type, setting_group, description, is_editable 
                FROM {$this->table} 
                WHERE deleted_at IS NULL";
        
        if ($group) {
            $sql .= " AND setting_group = :group";
        }
        
        $sql .= " ORDER BY setting_group, setting_key";

        $stmt = $this->db->prepare($sql);
        if ($group) {
            $stmt->execute(['group' => $group]);
        } else {
            $stmt->execute();
        }

        $results = $stmt->fetchAll(PDO::FETCH_OBJ);
        
        foreach ($results as $row) {
            $row->setting_value = $this->castValue($row->setting_value, $row->setting_type);
        }

        return $results;
    }

    /**
     * Check if setting is editable
     * 
     * @param string $key
     * @return bool
     */
    public function isEditable(string $key): bool
    {
        $sql = "SELECT is_editable FROM {$this->table} 
                WHERE setting_key = :key AND deleted_at IS NULL";
        $stmt = $this->db->prepare($sql);
        $stmt->execute(['key' => $key]);
        $result = $stmt->fetchColumn();
        return $result !== false ? (bool)$result : true;
    }

    /**
     * Toggle maintenance mode
     * 
     * @param bool $enabled
     * @return bool
     */
    public function setMaintenanceMode(bool $enabled): bool
    {
        return $this->set('maintenance_mode', $enabled, 'general');
    }

    /**
     * Check if maintenance mode is enabled
     * 
     * @return bool
     */
    public function isMaintenanceMode(): bool
    {
        return (bool)$this->get('maintenance_mode', false);
    }

    /**
     * Get all groups
     * 
     * @return array
     */
    public function getGroups(): array
    {
        $sql = "SELECT DISTINCT setting_group FROM {$this->table} 
                WHERE deleted_at IS NULL 
                ORDER BY setting_group";
        $stmt = $this->db->prepare($sql);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_COLUMN);
    }
}