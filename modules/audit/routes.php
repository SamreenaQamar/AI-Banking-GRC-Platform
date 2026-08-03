<?php
/**
 * Audit Module - Routes
 * 
 * @package    AI-Banking-GRC-Platform
 * @subpackage modules/audit
 * @version    1.0.0
 * @author     GRC Platform Team
 * @copyright  2026 AI Banking GRC Platform
 * @license    Proprietary
 * 
 * This file contains all route definitions for the audit module
 */

declare(strict_types=1);

// ============================================================
// AUDIT ROUTES
// ============================================================

$router->group(['middleware' => ['auth']], function($router) {
    // Dashboard
    $router->get('/audit', 'AuditController@index', [
        'name' => 'audit.index'
    ]);
    
    $router->get('/audit/dashboard', 'AuditController@index', [
        'name' => 'audit.dashboard'
    ]);
    
    // Schedule Audit
    $router->get('/audit/schedule', 'AuditController@schedule', [
        'name' => 'audit.schedule',
        'permission' => 'audit_create'
    ]);
    
    $router->post('/audit', 'AuditController@create', [
        'name' => 'audit.create',
        'permission' => 'audit_create'
    ]);
    
    // Audit Details
    $router->get('/audit/{id}', 'AuditController@details', [
        'name' => 'audit.details'
    ]);
    
    // Edit Audit
    $router->get('/audit/{id}/edit', 'AuditController@edit', [
        'name' => 'audit.edit',
        'permission' => 'audit_update'
    ]);
    
    $router->put('/audit/{id}', 'AuditController@update', [
        'name' => 'audit.update',
        'permission' => 'audit_update'
    ]);
    
    $router->delete('/audit/{id}', 'AuditController@delete', [
        'name' => 'audit.delete',
        'permission' => 'audit_delete'
    ]);
    
    // Findings
    $router->get('/audit/findings', 'AuditController@findings', [
        'name' => 'audit.findings'
    ]);
    
    $router->post('/audit/{id}/findings', 'AuditController@addFinding', [
        'name' => 'audit.findings.add',
        'permission' => 'audit_execute'
    ]);
    
    $router->post('/audit/findings/{id}/status', 'AuditController@updateFindingStatus', [
        'name' => 'audit.findings.status',
        'permission' => 'audit_execute'
    ]);
    
    // Evidence
    $router->post('/audit/{id}/evidence', 'AuditController@uploadEvidence', [
        'name' => 'audit.evidence.upload',
        'permission' => 'audit_execute'
    ]);
    
    // Reports
    $router->get('/audit/reports', 'AuditController@reports', [
        'name' => 'audit.reports'
    ]);
    
    // History
    $router->get('/audit/history', 'AuditController@history', [
        'name' => 'audit.history'
    ]);
});

// ============================================================
// API ROUTES
// ============================================================

$router->group(['prefix' => 'api/v1/audit', 'middleware' => ['auth:api']], function($router) {
    // Dashboard
    $router->get('/dashboard', 'AuditController@index', [
        'name' => 'api.audit.dashboard'
    ]);
    
    // Audits
    $router->get('/list', 'AuditController@list', [
        'name' => 'api.audit.list'
    ]);
    
    $router->post('/', 'AuditController@create', [
        'name' => 'api.audit.create'
    ]);
    
    $router->get('/{id}', 'AuditController@details', [
        'name' => 'api.audit.details'
    ]);
    
    $router->put('/{id}', 'AuditController@update', [
        'name' => 'api.audit.update'
    ]);
    
    // Findings
    $router->get('/findings', 'AuditController@findings', [
        'name' => 'api.audit.findings'
    ]);
    
    $router->post('/{id}/findings', 'AuditController@addFinding', [
        'name' => 'api.audit.findings.add'
    ]);
    
    $router->put('/findings/{id}', 'AuditController@updateFinding', [
        'name' => 'api.audit.findings.update'
    ]);
    
    // Evidence
    $router->post('/{id}/evidence', 'AuditController@uploadEvidence', [
        'name' => 'api.audit.evidence.upload'
    ]);
    
    // Reports
    $router->get('/reports', 'AuditController@reports', [
        'name' => 'api.audit.reports'
    ]);
});

// ============================================================
// RETURN ROUTES
// ============================================================

return $router;