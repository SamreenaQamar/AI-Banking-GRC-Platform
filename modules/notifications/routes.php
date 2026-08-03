<?php
/**
 * Notifications Module - Routes
 * 
 * @package    AI-Banking-GRC-Platform
 * @subpackage modules/notifications
 * @version    1.0.0
 * @author     GRC Platform Team
 * @copyright  2026 AI Banking GRC Platform
 * @license    Proprietary
 * 
 * This file contains all route definitions for the notifications module
 */

declare(strict_types=1);

// ============================================================
// NOTIFICATIONS ROUTES
// ============================================================

$router->group(['middleware' => ['auth']], function($router) {
    // Dashboard
    $router->get('/notifications', 'NotificationController@index', [
        'name' => 'notifications.index'
    ]);
    
    $router->get('/notifications/dashboard', 'NotificationController@index', [
        'name' => 'notifications.dashboard'
    ]);
    
    // List
    $router->get('/notifications/list', 'NotificationController@list', [
        'name' => 'notifications.list'
    ]);
    
    // Actions
    $router->post('/notifications/{id}/read', 'NotificationController@markRead', [
        'name' => 'notifications.read'
    ]);
    
    $router->post('/notifications/read-all', 'NotificationController@markAllRead', [
        'name' => 'notifications.read-all'
    ]);
    
    $router->delete('/notifications/{id}', 'NotificationController@delete', [
        'name' => 'notifications.delete'
    ]);
    
    // Reminders
    $router->post('/notifications/reminder', 'NotificationController@createReminder', [
        'name' => 'notifications.reminder.create'
    ]);
    
    // Calendar
    $router->get('/notifications/calendar', 'NotificationController@calendar', [
        'name' => 'notifications.calendar'
    ]);
});

// ============================================================
// API ROUTES
// ============================================================

$router->group(['prefix' => 'api/v1/notifications', 'middleware' => ['auth:api']], function($router) {
    // Dashboard
    $router->get('/dashboard', 'NotificationController@index', [
        'name' => 'api.notifications.dashboard'
    ]);
    
    // List
    $router->get('/list', 'NotificationController@list', [
        'name' => 'api.notifications.list'
    ]);
    
    // Actions
    $router->post('/{id}/read', 'NotificationController@markRead', [
        'name' => 'api.notifications.read'
    ]);
    
    $router->post('/read-all', 'NotificationController@markAllRead', [
        'name' => 'api.notifications.read-all'
    ]);
    
    $router->delete('/{id}', 'NotificationController@delete', [
        'name' => 'api.notifications.delete'
    ]);
    
    // Reminders
    $router->post('/reminder', 'NotificationController@createReminder', [
        'name' => 'api.notifications.reminder'
    ]);
    
    // Calendar
    $router->get('/calendar', 'NotificationController@calendar', [
        'name' => 'api.notifications.calendar'
    ]);
});

// ============================================================
// RETURN ROUTES
// ============================================================

return $router;