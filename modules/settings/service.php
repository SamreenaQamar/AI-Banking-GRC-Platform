<?php
/**
 * Settings Module - Service Layer
 * 
 * @package    AI-Banking-GRC-Platform
 * @subpackage modules/settings
 * @version    1.0.0
 * @author     GRC Platform Team
 * @copyright  2026 AI Banking GRC Platform
 * @license    Proprietary
 * 
 * This service handles settings operations:
 * - CRUD operations
 * - Validation
 * - Backup/Restore
 * - Security management
 */

declare(strict_types=1);

namespace Modules\Settings\Services;

use App\Models\Settings;
use App\Models\ActivityLog;
use App\Helpers\Auth;
use App\Helpers\Database;
use Exception;
use PDO;

class SettingsService
{
    /**
     * @var PDO
     */
    private PDO $db;
    
    /**
     * @var Settings
     */
    private Settings $settingsModel;
    
    /**
     * @var ActivityLog
     */
    private ActivityLog $activityLogModel;
    
    /**
     * Constructor
     */
    public function __construct()
    {
        $this->db = Database::getInstance()->getConnection();
        $this->settingsModel = new Settings();
        $this->activityLogModel = new ActivityLog();
    }
    
    /**
     * Get settings by section
     * 
     * @param string $section
     * @return array
     */
    public function getSettings(string $section): array
    {
        $settings = $this->settingsModel->getBySection($section);
        $defaults = get_section_defaults($section);
        
        // Merge with defaults
        $result = [];
        foreach ($defaults as $key => $default) {
            $result[$key] = $settings[$key] ?? $default;
        }
        
        return $result;
    }
    
    /**
     * Get all settings
     * 
     * @return array
     */
    public function getAllSettings(): array
    {
        $allSettings = [];
        $sections = array_keys(SETTINGS_SECTIONS);
        
        foreach ($sections as $section) {
            $allSettings[$section] = $this->getSettings($section);
        }
        
        return $allSettings;
    }
    
    /**
     * Update settings
     * 
     * @param string $section
     * @param array $data
     * @param int $userId
     * @return bool
     */
    public function updateSettings(string $section, array $data, int $userId): bool
    {
        $defaults = get_section_defaults($section);
        $filteredData = [];
        
        // Only update keys that exist in defaults
        foreach ($data as $key => $value) {
            if (array_key_exists($key, $defaults)) {
                $filteredData[$key] = $value;
            }
        }
        
        // Save each setting
        foreach ($filteredData as $key => $value) {
            $this->settingsModel->set($section, $key, $value);
        }
        
        // Log activity
        $this->logActivity('settings_update', "Updated settings for section: {$section}", $userId);
        
        return true;
    }
    
    /**
     * Create backup
     * 
     * @param int $userId
     * @return array
     */
    public function createBackup(int $userId): array
    {
        // Get all settings
        $settings = $this->getAllSettings();
        
        // Add metadata
        $backup = [
            'metadata' => [
                'version' => SETTINGS_MODULE_VERSION,
                'created_at' => date('Y-m-d H:i:s'),
                'created_by' => $userId,
                'app_version' => APP_VERSION
            ],
            'settings' => $settings
        ];
        
        // Save backup file
        $filename = 'settings_backup_' . date('Ymd_His') . '.json';
        $path = STORAGE_PATH . '/backups/settings/';
        
        if (!is_dir($path)) {
            mkdir($path, 0755, true);
        }
        
        file_put_contents($path . $filename, json_encode($backup, JSON_PRETTY_PRINT));
        
        // Log activity
        $this->logActivity('settings_backup', "Created settings backup: {$filename}", $userId);
        
        return [
            'filename' => $filename,
            'path' => $path . $filename,
            'size' => filesize($path . $filename)
        ];
    }
    
    /**
     * Restore backup
     * 
     * @param string $filename
     * @param int $userId
     * @return bool
     */
    public function restoreBackup(string $filename, int $userId): bool
    {
        $path = STORAGE_PATH . '/backups/settings/' . $filename;
        
        if (!file_exists($path)) {
            throw new Exception('Backup file not found.');
        }
        
        $content = file_get_contents($path);
        $data = json_decode($content, true);
        
        if (!isset($data['settings'])) {
            throw new Exception('Invalid backup file format.');
        }
        
        // Restore settings
        foreach ($data['settings'] as $section => $settings) {
            foreach ($settings as $key => $value) {
                $this->settingsModel->set($section, $key, $value);
            }
        }
        
        // Log activity
        $this->logActivity('settings_restore', "Restored settings from backup: {$filename}", $userId);
        
        return true;
    }
    
    /**
     * Get backup list
     * 
     * @return array
     */
    public function getBackupList(): array
    {
        $path = STORAGE_PATH . '/backups/settings/';
        
        if (!is_dir($path)) {
            return [];
        }
        
        $files = glob($path . '*.json');
        $backups = [];
        
        foreach ($files as $file) {
            $backups[] = [
                'filename' => basename($file),
                'size' => filesize($file),
                'created_at' => date('Y-m-d H:i:s', filectime($file))
            ];
        }
        
        // Sort by date descending
        usort($backups, function($a, $b) {
            return strtotime($b['created_at']) - strtotime($a['created_at']);
        });
        
        return $backups;
    }
    
    /**
     * Delete backup
     * 
     * @param string $filename
     * @param int $userId
     * @return bool
     */
    public function deleteBackup(string $filename, int $userId): bool
    {
        $path = STORAGE_PATH . '/backups/settings/' . $filename;
        
        if (!file_exists($path)) {
            throw new Exception('Backup file not found.');
        }
        
        unlink($path);
        
        // Log activity
        $this->logActivity('settings_backup_delete', "Deleted settings backup: {$filename}", $userId);
        
        return true;
    }
    
    /**
     * Validate settings
     * 
     * @param string $section
     * @param array $data
     * @return array
     */
    public function validateSettings(string $section, array $data): array
    {
        $errors = [];
        
        switch ($section) {
            case 'general':
                if (empty($data['app_name'])) {
                    $errors['app_name'] = 'Application name is required.';
                }
                break;
            case 'company':
                if (empty($data['company_name'])) {
                    $errors['company_name'] = 'Company name is required.';
                }
                if (!empty($data['company_email']) && !filter_var($data['company_email'], FILTER_VALIDATE_EMAIL)) {
                    $errors['company_email'] = 'Invalid email address.';
                }
                break;
            case 'email':
                if (!empty($data['smtp_host']) && !empty($data['smtp_username'])) {
                    if (empty($data['smtp_password'])) {
                        $errors['smtp_password'] = 'Password is required when SMTP is configured.';
                    }
                }
                break;
            case 'security':
                if (isset($data['session_lifetime']) && $data['session_lifetime'] < 300) {
                    $errors['session_lifetime'] = 'Session lifetime must be at least 300 seconds.';
                }
                if (isset($data['password_min_length']) && $data['password_min_length'] < 8) {
                    $errors['password_min_length'] = 'Password minimum length must be at least 8.';
                }
                break;
        }
        
        return $errors;
    }
    
    /**
     * Log activity
     * 
     * @param string $action
     * @param string $description
     * @param int $userId
     * @return void
     */
    private function logActivity(string $action, string $description, int $userId): void
    {
        $sql = "INSERT INTO activity_logs (user_id, action, module, description, created_at) 
                VALUES (:user_id, :action, 'settings', :description, :created_at)";
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute([
            'user_id' => $userId,
            'action' => $action,
            'description' => $description,
            'created_at' => date('Y-m-d H:i:s')
        ]);
    }
}