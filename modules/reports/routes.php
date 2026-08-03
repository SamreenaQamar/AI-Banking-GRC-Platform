<?php
/**
 * Reports Module - Routes
 * 
 * @package    AI-Banking-GRC-Platform
 * @subpackage modules/reports
 * @version    1.0.0
 * @author     GRC Platform Team
 * @copyright  2026 AI Banking GRC Platform
 * @license    Proprietary
 * 
 * This file contains all route definitions for the reports module
 */

declare(strict_types=1);

// ============================================================
// REPORTS ROUTES
// ============================================================

$router->group(['middleware' => ['auth']], function($router) {
    // Dashboard
    $router->get('/reports', 'ReportController@index', [
        'name' => 'reports.index'
    ]);
    
    $router->get('/reports/dashboard', 'ReportController@index', [
        'name' => 'reports.dashboard'
    ]);
    
    // Generate Report
    $router->post('/reports/generate', 'ReportController@generate', [
        'name' => 'reports.generate',
        'permission' => 'report_create'
    ]);
    
    // Schedule Report
    $router->post('/reports/schedule', 'ReportController@schedule', [
        'name' => 'reports.schedule',
        'permission' => 'report_create'
    ]);
    
    // Download Report
    $router->get('/reports/{id}/download', 'ReportController@download', [
        'name' => 'reports.download'
    ]);
    
    // Delete Report
    $router->delete('/reports/{id}', 'ReportController@delete', [
        'name' => 'reports.delete',
        'permission' => 'report_delete'
    ]);
});

// ============================================================
// API ROUTES
// ============================================================

$router->group(['prefix' => 'api/v1/reports', 'middleware' => ['auth:api']], function($router) {
    // Dashboard
    $router->get('/dashboard', 'ReportController@index', [
        'name' => 'api.reports.dashboard'
    ]);
    
    // Generate
    $router->post('/generate', 'ReportController@generate', [
        'name' => 'api.reports.generate'
    ]);
    
    // Schedule
    $router->post('/schedule', 'ReportController@schedule', [
        'name' => 'api.reports.schedule'
    ]);
    
    // Download
    $router->get('/{id}/download', 'ReportController@download', [
        'name' => 'api.reports.download'
    ]);
    
    // Delete
    $router->delete('/{id}', 'ReportController@delete', [
        'name' => 'api.reports.delete'
    ]);
});

// ============================================================
// RETURN ROUTES
// ============================================================

return $router;