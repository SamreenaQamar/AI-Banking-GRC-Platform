<?php
/**
 * Compliance Module - Routes
 * 
 * @package    AI-Banking-GRC-Platform
 * @subpackage modules/compliance
 * @version    1.0.0
 * @author     GRC Platform Team
 * @copyright  2026 AI Banking GRC Platform
 * @license    Proprietary
 * 
 * This file contains all route definitions for the compliance module
 */

declare(strict_types=1);

// ============================================================
// COMPLIANCE ROUTES
// ============================================================

$router->group(['middleware' => ['auth']], function($router) {
    // Dashboard
    $router->get('/compliance', 'ComplianceController@index', [
        'name' => 'compliance.index'
    ]);
    
    $router->get('/compliance/dashboard', 'ComplianceController@index', [
        'name' => 'compliance.dashboard'
    ]);
    
    // SBP Circulars
    $router->get('/compliance/circulars', 'ComplianceController@circulars', [
        'name' => 'compliance.circulars'
    ]);
    
    $router->get('/compliance/circulars/{id}', 'ComplianceController@circularDetails', [
        'name' => 'compliance.circulars.details'
    ]);
    
    // Compliance Checklist
    $router->get('/compliance/checklist', 'ComplianceController@checklist', [
        'name' => 'compliance.checklist'
    ]);
    
    // Compliance Status
    $router->get('/compliance/status', 'ComplianceController@status', [
        'name' => 'compliance.status'
    ]);
    
    // Gap Analysis
    $router->get('/compliance/gap-analysis', 'ComplianceController@gapAnalysis', [
        'name' => 'compliance.gap-analysis'
    ]);
    
    // Recommendations
    $router->get('/compliance/recommendations', 'ComplianceController@recommendations', [
        'name' => 'compliance.recommendations'
    ]);
    
    // Calendar
    $router->get('/compliance/calendar', 'ComplianceController@calendar', [
        'name' => 'compliance.calendar'
    ]);
    
    // Evidence
    $router->get('/compliance/evidence/{id}', 'ComplianceController@evidence', [
        'name' => 'compliance.evidence'
    ]);
    
    $router->post('/compliance/evidence/{id}/upload', 'ComplianceController@uploadEvidence', [
        'name' => 'compliance.evidence.upload'
    ]);
    
    $router->post('/compliance/evidence/{id}/verify', 'ComplianceController@verifyEvidence', [
        'name' => 'compliance.evidence.verify'
    ]);
});

// ============================================================
// API ROUTES
// ============================================================

$router->group(['prefix' => 'api/v1/compliance', 'middleware' => ['auth:api']], function($router) {
    // Dashboard
    $router->get('/dashboard', 'ComplianceController@index', [
        'name' => 'api.compliance.dashboard'
    ]);
    
    // Circulars
    $router->get('/circulars', 'ComplianceController@circulars', [
        'name' => 'api.compliance.circulars'
    ]);
    
    $router->get('/circulars/{id}', 'ComplianceController@circularDetails', [
        'name' => 'api.compliance.circulars.details'
    ]);
    
    // Status
    $router->get('/status', 'ComplianceController@status', [
        'name' => 'api.compliance.status'
    ]);
    
    // Gap Analysis
    $router->get('/gap-analysis', 'ComplianceController@gapAnalysis', [
        'name' => 'api.compliance.gap-analysis'
    ]);
    
    // Recommendations
    $router->get('/recommendations', 'ComplianceController@recommendations', [
        'name' => 'api.compliance.recommendations'
    ]);
    
    // Calendar
    $router->get('/calendar', 'ComplianceController@calendar', [
        'name' => 'api.compliance.calendar'
    ]);
    
    // Evidence
    $router->post('/evidence/{id}/upload', 'ComplianceController@uploadEvidence', [
        'name' => 'api.compliance.evidence.upload'
    ]);
    
    $router->post('/evidence/{id}/verify', 'ComplianceController@verifyEvidence', [
        'name' => 'api.compliance.evidence.verify'
    ]);
});

// ============================================================
// RETURN ROUTES
// ============================================================

return $router;