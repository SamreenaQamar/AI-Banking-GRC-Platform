<?php
namespace App\Controllers;

use App\Models\Settings;
use App\Helpers\Auth;
use Exception;

class SettingsController extends BaseController
{
    private Settings $settingsModel;
    
    public function __construct()
    {
        parent::__construct();
        $this->controllerName = 'Settings';
        $this->settingsModel = new Settings();
        $this->requireAuth();
        $this->requireRole([ROLE_ADMIN, ROLE_SUPER_ADMIN]);
    }
    
    public function index(): void
    {
        $settings = $this->settingsModel->getAllGrouped();
        $this->render('index', [
            'title' => 'Settings - ' . APP_NAME,
            'settings' => $settings
        ]);
    }
    
    public function updateGeneral(): void
    {
        try {
            $this->validateCSRF($_POST['csrf_token'] ?? '');
            $settings = $this->allInput();
            $this->settingsModel->updateGroup('general', $settings);
            
            $this->setFlashMessage('success', 'General settings updated successfully.');
            $this->jsonSuccess('Settings updated.');
            
        } catch (Exception $e) {
            $this->jsonError($e->getMessage());
        }
    }
    
    public function updateSecurity(): void
    {
        try {
            $this->validateCSRF($_POST['csrf_token'] ?? '');
            $settings = $this->allInput();
            $this->settingsModel->updateGroup('security', $settings);
            
            $this->setFlashMessage('success', 'Security settings updated successfully.');
            $this->jsonSuccess('Security settings updated.');
            
        } catch (Exception $e) {
            $this->jsonError($e->getMessage());
        }
    }
    
    public function updateEmail(): void
    {
        try {
            $this->validateCSRF($_POST['csrf_token'] ?? '');
            $settings = $this->allInput();
            $this->settingsModel->updateGroup('email', $settings);
            
            $this->setFlashMessage('success', 'Email settings updated successfully.');
            $this->jsonSuccess('Email settings updated.');
            
        } catch (Exception $e) {
            $this->jsonError($e->getMessage());
        }
    }
    
    public function updateAI(): void
    {
        try {
            $this->requirePermission('settings_update');
            $this->validateCSRF($_POST['csrf_token'] ?? '');
            $settings = $this->allInput();
            $this->settingsModel->updateGroup('ai', $settings);
            
            $this->setFlashMessage('success', 'AI settings updated successfully.');
            $this->jsonSuccess('AI settings updated.');
            
        } catch (Exception $e) {
            $this->jsonError($e->getMessage());
        }
    }
}