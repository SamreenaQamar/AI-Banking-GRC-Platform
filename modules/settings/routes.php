<?php
/**
 * Settings Module - Routes
 * 
 * @package    AI-Banking-GRC-Platform
 * @subpackage modules/settings
 * @version    1.0.0
 * @author     GRC Platform Team
 * @copyright  2026 AI Banking GRC Platform
 * @license    Proprietary
 * 
 * This file contains all route definitions for the settings module
 */

declare(strict_types=1);

// ============================================================
// SETTINGS ROUTES
// ============================================================

$router->group(['middleware' => ['auth', 'admin']], function($router) {
    // Dashboard
    $router->get('/settings', 'SettingsController@index', [
        'name' => 'settings.index'
    ]);
    
    // Company Settings
    $router->get('/settings/company', 'SettingsController@company', [
        'name' => 'settings.company',
        'permission' => 'settings_company'
    ]);
    
    $router->post('/settings/company/update', 'SettingsController@updateCompany', [
        'name' => 'settings.company.update',
        'permission' => 'settings_company'
    ]);
    
    // Security Settings
    $router->get('/settings/security', 'SettingsController@security', [
        'name' => 'settings.security',
        'permission' => 'settings_security'
    ]);
    
    $router->post('/settings/security/update', 'SettingsController@updateSecurity', [
        'name' => 'settings.security.update',
        'permission' => 'settings_security'
    ]);
    
    // API Settings
    $router->get('/settings/api', 'SettingsController@api', [
        'name' => 'settings.api',
        'permission' => 'settings_api'
    ]);
    
    $router->post('/settings/api/update', 'SettingsController@updateApi', [
        'name' => 'settings.api.update',
        'permission' => 'settings_api'
    ]);
    
    // Backup Settings
    $router->get('/settings/backup', 'SettingsController@backup', [
        'name' => 'settings.backup',
        'permission' => 'settings_backup'
    ]);
    
    $router->post('/settings/backup/create', 'SettingsController@createBackup', [
        'name' => 'settings.backup.create',
        'permission' => 'settings_backup'
    ]);
    
    $router->post('/settings/backup/restore/{filename}', 'SettingsController@restoreBackup', [
        'name' => 'settings.backup.restore',
        'permission' => 'settings_backup'
    ]);
    
    $router->delete('/settings/backup/{filename}', 'SettingsController@deleteBackup', [
        'name' => 'settings.backup.delete',
        'permission' => 'settings_backup'
    ]);
});

// ============================================================
// API ROUTES
// ============================================================

$router->group(['prefix' => 'api/v1/settings', 'middleware' => ['auth:api', 'admin']], function($router) {
    // Get settings
    $router->get('/', 'SettingsController@index', [
        'name' => 'api.settings.index'
    ]);
    
    $router->get('/{section}', 'SettingsController@getSection', [
        'name' => 'api.settings.section'
    ]);
    
    // Update settings
    $router->put('/{section}', 'SettingsController@update', [
        'name' => 'api.settings.update'
    ]);
    
    // Backup
    $router->post('/backup', 'SettingsController@createBackup', [
        'name' => 'api.settings.backup.create'
    ]);
    
    $router->post('/backup/restore/{filename}', 'SettingsController@restoreBackup', [
        'name' => 'api.settings.backup.restore'
    ]);
    
    $router->delete('/backup/{filename}', 'SettingsController@deleteBackup', [
        'name' => 'api.settings.backup.delete'
    ]);
});

// ============================================================
// RETURN ROUTES
// ============================================================

return $router;