<?php
/**
 * Risk Module - Routes
 * 
 * @package    AI-Banking-GRC-Platform
 * @subpackage modules/risk
 * @version    1.0.0
 * @author     GRC Platform Team
 * @copyright  2026 AI Banking GRC Platform
 * @license    Proprietary
 * 
 * This file contains all route definitions for the risk module
 */

declare(strict_types=1);

// ============================================================
// RISK ROUTES
// ============================================================

$router->group(['middleware' => ['auth']], function($router) {
    // Dashboard
    $router->get('/risk', 'RiskController@index', [
        'name' => 'risk.index'
    ]);
    
    $router->get('/risk/dashboard', 'RiskController@index', [
        'name' => 'risk.dashboard'
    ]);
    
    // Risk Register
    $router->get('/risk/register', 'RiskController@register', [
        'name' => 'risk.register'
    ]);
    
    // Create Risk
    $router->get('/risk/create', 'RiskController@create', [
        'name' => 'risk.create',
        'permission' => 'risk_create'
    ]);
    
    $router->post('/risk', 'RiskController@store', [
        'name' => 'risk.store',
        'permission' => 'risk_create'
    ]);
    
    // Edit Risk
    $router->get('/risk/{id}/edit', 'RiskController@edit', [
        'name' => 'risk.edit',
        'permission' => 'risk_update'
    ]);
    
    $router->put('/risk/{id}', 'RiskController@update', [
        'name' => 'risk.update',
        'permission' => 'risk_update'
    ]);
    
    // Risk Details
    $router->get('/risk/{id}', 'RiskController@details', [
        'name' => 'risk.details'
    ]);
    
    // Risk Assessment
    $router->post('/risk/{id}/assess', 'RiskController@assess', [
        'name' => 'risk.assess',
        'permission' => 'risk_assess'
    ]);
    
    // Risk Mitigation
    $router->post('/risk/{id}/mitigate', 'RiskController@mitigate', [
        'name' => 'risk.mitigate',
        'permission' => 'risk_update'
    ]);
    
    // Risk Heatmap
    $router->get('/risk/heatmap', 'RiskController@heatmap', [
        'name' => 'risk.heatmap'
    ]);
    
    // Basel III Dashboard
    $router->get('/risk/basel', 'RiskController@basel', [
        'name' => 'risk.basel'
    ]);
});

// ============================================================
// API ROUTES
// ============================================================

$router->group(['prefix' => 'api/v1/risk', 'middleware' => ['auth:api']], function($router) {
    // Dashboard
    $router->get('/dashboard', 'RiskController@index', [
        'name' => 'api.risk.dashboard'
    ]);
    
    // Register
    $router->get('/register', 'RiskController@register', [
        'name' => 'api.risk.register'
    ]);
    
    // CRUD
    $router->post('/', 'RiskController@store', [
        'name' => 'api.risk.store'
    ]);
    
    $router->get('/{id}', 'RiskController@details', [
        'name' => 'api.risk.details'
    ]);
    
    $router->put('/{id}', 'RiskController@update', [
        'name' => 'api.risk.update'
    ]);
    
    $router->delete('/{id}', 'RiskController@delete', [
        'name' => 'api.risk.delete'
    ]);
    
    // Assessment
    $router->post('/{id}/assess', 'RiskController@assess', [
        'name' => 'api.risk.assess'
    ]);
    
    // Mitigation
    $router->post('/{id}/mitigate', 'RiskController@mitigate', [
        'name' => 'api.risk.mitigate'
    ]);
    
    // Heatmap
    $router->get('/heatmap', 'RiskController@heatmap', [
        'name' => 'api.risk.heatmap'
    ]);
    
    // Basel
    $router->get('/basel', 'RiskController@basel', [
        'name' => 'api.risk.basel'
    ]);
});

// ============================================================
// RETURN ROUTES
// ============================================================

return $router;