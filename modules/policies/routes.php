<?php
/**
 * Policies Module - Routes
 * 
 * @package    AI-Banking-GRC-Platform
 * @subpackage modules/policies
 * @version    1.0.0
 * @author     GRC Platform Team
 * @copyright  2026 AI Banking GRC Platform
 * @license    Proprietary
 * 
 * This file contains all route definitions for the policies module
 */

declare(strict_types=1);

// ============================================================
// POLICIES ROUTES
// ============================================================

$router->group(['middleware' => ['auth']], function($router) {
    // Dashboard
    $router->get('/policies', 'PolicyController@index', [
        'name' => 'policies.index'
    ]);
    
    $router->get('/policies/dashboard', 'PolicyController@index', [
        'name' => 'policies.dashboard'
    ]);
    
    // Policy Library
    $router->get('/policies/library', 'PolicyController@library', [
        'name' => 'policies.library'
    ]);
    
    // Create Policy
    $router->get('/policies/create', 'PolicyController@create', [
        'name' => 'policies.create',
        'permission' => 'policy_create'
    ]);
    
    $router->post('/policies', 'PolicyController@store', [
        'name' => 'policies.store',
        'permission' => 'policy_create'
    ]);
    
    // View Policy
    $router->get('/policies/{id}', 'PolicyController@view', [
        'name' => 'policies.view'
    ]);
    
    // Edit Policy
    $router->get('/policies/{id}/edit', 'PolicyController@edit', [
        'name' => 'policies.edit',
        'permission' => 'policy_update'
    ]);
    
    $router->put('/policies/{id}', 'PolicyController@update', [
        'name' => 'policies.update',
        'permission' => 'policy_update'
    ]);
    
    $router->delete('/policies/{id}', 'PolicyController@delete', [
        'name' => 'policies.delete',
        'permission' => 'policy_delete'
    ]);
    
    // Approve Policy
    $router->post('/policies/{id}/approve', 'PolicyController@approve', [
        'name' => 'policies.approve',
        'permission' => 'policy_approve'
    ]);
    
    // Publish Policy
    $router->post('/policies/{id}/publish', 'PolicyController@publish', [
        'name' => 'policies.publish',
        'permission' => 'policy_publish'
    ]);
    
    // Acknowledge Policy
    $router->post('/policies/{id}/acknowledge', 'PolicyController@acknowledge', [
        'name' => 'policies.acknowledge',
        'permission' => 'policy_acknowledge'
    ]);
    
    // AI Policy Generator
    $router->get('/policies/generate', 'PolicyController@generate', [
        'name' => 'policies.generate',
        'permission' => 'policy_create'
    ]);
    
    $router->post('/policies/generate-ai', 'PolicyController@generateAI', [
        'name' => 'policies.generate-ai',
        'permission' => 'policy_create'
    ]);
});

// ============================================================
// API ROUTES
// ============================================================

$router->group(['prefix' => 'api/v1/policies', 'middleware' => ['auth:api']], function($router) {
    // Dashboard
    $router->get('/dashboard', 'PolicyController@index', [
        'name' => 'api.policies.dashboard'
    ]);
    
    // Library
    $router->get('/library', 'PolicyController@library', [
        'name' => 'api.policies.library'
    ]);
    
    // CRUD
    $router->post('/', 'PolicyController@store', [
        'name' => 'api.policies.store'
    ]);
    
    $router->get('/{id}', 'PolicyController@view', [
        'name' => 'api.policies.view'
    ]);
    
    $router->put('/{id}', 'PolicyController@update', [
        'name' => 'api.policies.update'
    ]);
    
    $router->delete('/{id}', 'PolicyController@delete', [
        'name' => 'api.policies.delete'
    ]);
    
    // Workflow
    $router->post('/{id}/approve', 'PolicyController@approve', [
        'name' => 'api.policies.approve'
    ]);
    
    $router->post('/{id}/publish', 'PolicyController@publish', [
        'name' => 'api.policies.publish'
    ]);
    
    $router->post('/{id}/acknowledge', 'PolicyController@acknowledge', [
        'name' => 'api.policies.acknowledge'
    ]);
    
    // AI
    $router->post('/generate-ai', 'PolicyController@generateAI', [
        'name' => 'api.policies.generate-ai'
    ]);
});

// ============================================================
// RETURN ROUTES
// ============================================================

return $router;