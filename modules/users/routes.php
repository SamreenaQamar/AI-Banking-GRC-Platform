<?php
/**
 * Users Module - Routes
 * 
 * @package    AI-Banking-GRC-Platform
 * @subpackage modules/users
 * @version    1.0.0
 * @author     GRC Platform Team
 * @copyright  2026 AI Banking GRC Platform
 * @license    Proprietary
 * 
 * This file contains all route definitions for the users module
 */

declare(strict_types=1);

// ============================================================
// USERS ROUTES
// ============================================================

$router->group(['middleware' => ['auth']], function($router) {
    // Dashboard
    $router->get('/users', 'UserController@index', [
        'name' => 'users.index'
    ]);
    
    $router->get('/users/dashboard', 'UserController@index', [
        'name' => 'users.dashboard'
    ]);
    
    // User List
    $router->get('/users/list', 'UserController@list', [
        'name' => 'users.list'
    ]);
    
    // Create User
    $router->get('/users/create', 'UserController@create', [
        'name' => 'users.create',
        'permission' => 'user_create'
    ]);
    
    $router->post('/users', 'UserController@store', [
        'name' => 'users.store',
        'permission' => 'user_create'
    ]);
    
    // View User
    $router->get('/users/{id}', 'UserController@view', [
        'name' => 'users.view'
    ]);
    
    // Edit User
    $router->get('/users/{id}/edit', 'UserController@edit', [
        'name' => 'users.edit',
        'permission' => 'user_update'
    ]);
    
    $router->put('/users/{id}', 'UserController@update', [
        'name' => 'users.update',
        'permission' => 'user_update'
    ]);
    
    $router->delete('/users/{id}', 'UserController@delete', [
        'name' => 'users.delete',
        'permission' => 'user_delete'
    ]);
    
    // User Status
    $router->post('/users/{id}/status', 'UserController@updateStatus', [
        'name' => 'users.status',
        'permission' => 'user_update'
    ]);
    
    // Assign Role
    $router->post('/users/{id}/role', 'UserController@assignRole', [
        'name' => 'users.role',
        'permission' => 'user_role_assign'
    ]);
    
    // Profile
    $router->get('/profile', 'UserController@profile', [
        'name' => 'users.profile'
    ]);
});

// ============================================================
// API ROUTES
// ============================================================

$router->group(['prefix' => 'api/v1/users', 'middleware' => ['auth:api']], function($router) {
    // Dashboard
    $router->get('/dashboard', 'UserController@index', [
        'name' => 'api.users.dashboard'
    ]);
    
    // List
    $router->get('/list', 'UserController@list', [
        'name' => 'api.users.list'
    ]);
    
    // CRUD
    $router->post('/', 'UserController@store', [
        'name' => 'api.users.store'
    ]);
    
    $router->get('/{id}', 'UserController@view', [
        'name' => 'api.users.view'
    ]);
    
    $router->put('/{id}', 'UserController@update', [
        'name' => 'api.users.update'
    ]);
    
    $router->delete('/{id}', 'UserController@delete', [
        'name' => 'api.users.delete'
    ]);
    
    // Status
    $router->post('/{id}/status', 'UserController@updateStatus', [
        'name' => 'api.users.status'
    ]);
    
    // Role
    $router->post('/{id}/role', 'UserController@assignRole', [
        'name' => 'api.users.role'
    ]);
    
    // Profile
    $router->get('/profile', 'UserController@profile', [
        'name' => 'api.users.profile'
    ]);
});

// ============================================================
// RETURN ROUTES
// ============================================================

return $router;