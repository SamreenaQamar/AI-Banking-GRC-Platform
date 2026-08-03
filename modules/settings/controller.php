<?php
/**
 * Settings Module - Controller
 * 
 * @package    AI-Banking-GRC-Platform
 * @subpackage modules/settings
 * @version    1.0.0
 * @author     GRC Platform Team
 * @copyright  2026 AI Banking GRC Platform
 * @license    Proprietary
 * 
 * This controller handles:
 * - System settings
 * - Company settings
 * - Security settings
 * - API settings
 * - Backup settings
 */

declare(strict_types=1);

namespace Modules\Settings\Controllers;

use App\Controllers\BaseController;
use App\Helpers\Auth;
use App\Helpers\CSRF;
use App\Helpers\Validation;
use Modules\Settings\Services\SettingsService;
use Exception;

class SettingsController extends BaseController
{
    /**
     * @var SettingsService
     */
    private SettingsService $settingsService;
    
    /**
     * Constructor
     */
    public function __construct()
    {
        parent::__construct();
        $this->controllerName = 'Settings';
        $this->settingsService = new SettingsService();
        
        $this->requireAuth();
        $this->requireRole(['admin', 'super_admin']);
    }
    
    /**
     * Settings dashboard
     * 
     * @return void
     */
    public function index(): void
    {
        try {
            $settings = $this->settingsService->getAllSettings();
            
            $this->render('settings/dashboard', [
                'title' => 'Settings - ' . APP_NAME,
                'settings' => $settings,
                'sections' => SETTINGS_SECTIONS
            ]);
            
        } catch (Exception $e) {
            $this->setFlashMessage('error', 'Failed to load settings: ' . $e->getMessage());
            $this->redirectToRoute('dashboard');
        }
    }
    
    /**
     * Company settings
     * 
     * @return void
     */
    public function company(): void
    {
        try {
            $this->requirePermission('settings_company');
            $settings = $this->settingsService->getSettings('company');
            
            $this->render('settings/company', [
                'title' => 'Company Settings - ' . APP_NAME,
                'settings' => $settings,
                'section' => 'company'
            ]);
            
        } catch (Exception $e) {
            $this->setFlashMessage('error', $e->getMessage());
            $this->redirectToRoute('settings.index');
        }
    }
    
    /**
     * Update company settings
     * 
     * @return void
     */
    public function updateCompany(): void
    {
        try {
            $this->requirePermission('settings_company');
            CSRF::validate($_POST['csrf_token'] ?? '');
            
            $data = $this->allInput();
            
            // Handle logo upload
            if (isset($_FILES['company_logo']) && $_FILES['company_logo']['error'] === UPLOAD_ERR_OK) {
                $logo = $this->uploadLogo($_FILES['company_logo']);
                if ($logo) {
                    $data['company_logo'] = $logo;
                }
            }
            
            // Validate
            $errors = $this->settingsService->validateSettings('company', $data);
            if (!empty($errors)) {
                throw new Exception('Validation failed: ' . implode(', ', $errors));
            }
            
            $result = $this->settingsService->updateSettings('company', $data, Auth::id());
            
            if (!$result) {
                throw new Exception('Failed to update settings.');
            }
            
            $this->setFlashMessage('success', 'Company settings updated successfully.');
            $this->redirectToRoute('settings.company');
            
        } catch (Exception $e) {
            $this->setFlashMessage('error', $e->getMessage());
            $this->redirectToRoute('settings.company');
        }
    }
    
    /**
     * Security settings
     * 
     * @return void
     */
    public function security(): void
    {
        try {
            $this->requirePermission('settings_security');
            $settings = $this->settingsService->getSettings('security');
            
            $this->render('settings/security', [
                'title' => 'Security Settings - ' . APP_NAME,
                'settings' => $settings,
                'section' => 'security'
            ]);
            
        } catch (Exception $e) {
            $this->setFlashMessage('error', $e->getMessage());
            $this->redirectToRoute('settings.index');
        }
    }
    
    /**
     * Update security settings
     * 
     * @return void
     */
    public function updateSecurity(): void
    {
        try {
            $this->requirePermission('settings_security');
            CSRF::validate($_POST['csrf_token'] ?? '');
            
            $data = $this->allInput();
            
            // Validate
            $errors = $this->settingsService->validateSettings('security', $data);
            if (!empty($errors)) {
                throw new Exception('Validation failed: ' . implode(', ', $errors));
            }
            
            $result = $this->settingsService->updateSettings('security', $data, Auth::id());
            
            if (!$result) {
                throw new Exception('Failed to update settings.');
            }
            
            $this->setFlashMessage('success', 'Security settings updated successfully.');
            $this->redirectToRoute('settings.security');
            
        } catch (Exception $e) {
            $this->setFlashMessage('error', $e->getMessage());
            $this->redirectToRoute('settings.security');
        }
    }
    
    /**
     * API settings
     * 
     * @return void
     */
    public function api(): void
    {
        try {
            $this->requirePermission('settings_api');
            $settings = $this->settingsService->getSettings('api');
            
            $this->render('settings/api', [
                'title' => 'API Settings - ' . APP_NAME,
                'settings' => $settings,
                'section' => 'api'
            ]);
            
        } catch (Exception $e) {
            $this->setFlashMessage('error', $e->getMessage());
            $this->redirectToRoute('settings.index');
        }
    }
    
    /**
     * Update API settings
     * 
     * @return void
     */
    public function updateApi(): void
    {
        try {
            $this->requirePermission('settings_api');
            CSRF::validate($_POST['csrf_token'] ?? '');
            
            $data = $this->allInput();
            
            $result = $this->settingsService->updateSettings('api', $data, Auth::id());
            
            if (!$result) {
                throw new Exception('Failed to update settings.');
            }
            
            $this->setFlashMessage('success', 'API settings updated successfully.');
            $this->redirectToRoute('settings.api');
            
        } catch (Exception $e) {
            $this->setFlashMessage('error', $e->getMessage());
            $this->redirectToRoute('settings.api');
        }
    }
    
    /**
     * Backup settings
     * 
     * @return void
     */
    public function backup(): void
    {
        try {
            $this->requirePermission('settings_backup');
            $settings = $this->settingsService->getSettings('backup');
            $backups = $this->settingsService->getBackupList();
            
            $this->render('settings/backup', [
                'title' => 'Backup Settings - ' . APP_NAME,
                'settings' => $settings,
                'backups' => $backups,
                'section' => 'backup'
            ]);
            
        } catch (Exception $e) {
            $this->setFlashMessage('error', $e->getMessage());
            $this->redirectToRoute('settings.index');
        }
    }
    
    /**
     * Create backup (AJAX)
     * 
     * @return void
     */
    public function createBackup(): void
    {
        try {
            $this->requirePermission('settings_backup');
            CSRF::validate($_POST['csrf_token'] ?? '');
            
            $backup = $this->settingsService->createBackup(Auth::id());
            
            $this->jsonSuccess('Backup created successfully.', $backup);
            
        } catch (Exception $e) {
            $this->jsonError($e->getMessage());
        }
    }
    
    /**
     * Restore backup (AJAX)
     * 
     * @param array $params
     * @return void
     */
    public function restoreBackup(array $params): void
    {
        try {
            $this->requirePermission('settings_backup');
            CSRF::validate($_POST['csrf_token'] ?? '');
            
            $filename = $params['filename'] ?? '';
            
            if (empty($filename)) {
                throw new Exception('Filename is required.');
            }
            
            $result = $this->settingsService->restoreBackup($filename, Auth::id());
            
            if (!$result) {
                throw new Exception('Failed to restore backup.');
            }
            
            $this->jsonSuccess('Backup restored successfully.');
            
        } catch (Exception $e) {
            $this->jsonError($e->getMessage());
        }
    }
    
    /**
     * Delete backup (AJAX)
     * 
     * @param array $params
     * @return void
     */
    public function deleteBackup(array $params): void
    {
        try {
            $this->requirePermission('settings_backup');
            CSRF::validate($_POST['csrf_token'] ?? '');
            
            $filename = $params['filename'] ?? '';
            
            if (empty($filename)) {
                throw new Exception('Filename is required.');
            }
            
            $result = $this->settingsService->deleteBackup($filename, Auth::id());
            
            if (!$result) {
                throw new Exception('Failed to delete backup.');
            }
            
            $this->jsonSuccess('Backup deleted successfully.');
            
        } catch (Exception $e) {
            $this->jsonError($e->getMessage());
        }
    }
    
    /**
     * Upload logo
     * 
     * @param array $file
     * @return string|null
     */
    private function uploadLogo(array $file): ?string
    {
        $allowedTypes = ['image/jpeg', 'image/png', 'image/gif', 'image/svg+xml'];
        $maxSize = 2 * 1024 * 1024; // 2MB
        
        if (!in_array($file['type'], $allowedTypes)) {
            throw new Exception('Invalid image type. Allowed: JPEG, PNG, GIF, SVG');
        }
        
        if ($file['size'] > $maxSize) {
            throw new Exception('Image size exceeds 2MB limit.');
        }
        
        $extension = pathinfo($file['name'], PATHINFO_EXTENSION);
        $filename = 'logo_' . date('Ymd_His') . '.' . $extension;
        $path = UPLOADS_PATH . '/settings/';
        
        if (!is_dir($path)) {
            mkdir($path, 0755, true);
        }
        
        $targetPath = $path . $filename;
        
        if (!move_uploaded_file($file['tmp_name'], $targetPath)) {
            throw new Exception('Failed to upload logo.');
        }
        
        return 'settings/' . $filename;
    }
}