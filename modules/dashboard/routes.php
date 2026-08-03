<?php
/**
 * Dashboard Module - Routes
 * 
 * @package    AI-Banking-GRC-Platform
 * @subpackage modules/dashboard
 * @version    1.0.0
 * @author     GRC Platform Team
 * @copyright  2026 AI Banking GRC Platform
 * @license    Proprietary
 * 
 * This file contains all route definitions for the dashboard module
 */

declare(strict_types=1);

// ============================================================
// DASHBOARD ROUTES
// ============================================================

// Main dashboard
$router->get('/dashboard', 'DashboardController@index', [
    'name' => 'dashboard.index',
    'middleware' => ['auth']
]);

// Dashboard AJAX endpoints
$router->get('/dashboard/statistics', 'DashboardController@statistics', [
    'name' => 'dashboard.statistics',
    'middleware' => ['auth']
]);

$router->get('/dashboard/charts', 'DashboardController@charts', [
    'name' => 'dashboard.charts',
    'middleware' => ['auth']
]);

$router->get('/dashboard/recent-activities', 'DashboardController@recentActivities', [
    'name' => 'dashboard.activities',
    'middleware' => ['auth']
]);

$router->get('/dashboard/notifications', 'DashboardController@notifications', [
    'name' => 'dashboard.notifications',
    'middleware' => ['auth']
]);

$router->post('/dashboard/notifications/read', 'DashboardController@markNotificationRead', [
    'name' => 'dashboard.notifications.read',
    'middleware' => ['auth']
]);

$router->post('/dashboard/notifications/read-all', 'DashboardController@markAllNotificationsRead', [
    'name' => 'dashboard.notifications.read-all',
    'middleware' => ['auth']
]);

$router->get('/dashboard/ai-insights', 'DashboardController@aiInsights', [
    'name' => 'dashboard.ai-insights',
    'middleware' => ['auth']
]);

$router->post('/dashboard/widgets', 'DashboardController@updateWidgets', [
    'name' => 'dashboard.widgets',
    'middleware' => ['auth']
]);

$router->get('/dashboard/system-health', 'DashboardController@systemHealth', [
    'name' => 'dashboard.health',
    'middleware' => ['auth', 'admin']
]);

// ============================================================
// API ROUTES
// ============================================================

$router->group(['prefix' => 'api/v1/dashboard', 'middleware' => ['auth:api']], function($router) {
    // Dashboard API endpoints
    $router->get('/stats', 'DashboardController@statistics', [
        'name' => 'api.dashboard.stats'
    ]);
    
    $router->get('/charts', 'DashboardController@charts', [
        'name' => 'api.dashboard.charts'
    ]);
    
    $router->get('/activities', 'DashboardController@recentActivities', [
        'name' => 'api.dashboard.activities'
    ]);
    
    $router->get('/notifications', 'DashboardController@notifications', [
        'name' => 'api.dashboard.notifications'
    ]);
    
    $router->get('/ai-insights', 'DashboardController@aiInsights', [
        'name' => 'api.dashboard.ai-insights'
    ]);
    
    $router->get('/system-health', 'DashboardController@systemHealth', [
        'name' => 'api.dashboard.health'
    ]);
});

// ============================================================
// RETURN ROUTES
// ============================================================

return $router;