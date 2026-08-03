# Dashboard Module

## Overview

The Dashboard Module provides a comprehensive, enterprise-grade dashboard for the AI Banking GRC Platform. It offers real-time insights into compliance, risk, audit, and regulatory activities across the organization.

## Features

### Dashboard Widgets

- **Compliance Score**: Overall compliance percentage with trend
- **Risk Score**: Risk assessment score with distribution
- **Open Risks**: Count of open risks by severity
- **Audit Findings**: Audit findings by severity and status
- **SBP Circulars**: Regulatory circular status
- **Pending Tasks**: User's pending compliance tasks
- **Recent Activities**: Latest system activities
- **AI Insights**: AI-powered recommendations and alerts
- **Risk Heatmap**: Visual risk distribution
- **Audit Progress**: Audit completion tracking
- **System Health**: System status monitoring

### Charts

- **Compliance Trend**: Compliance score over time
- **Risk Distribution**: Risk distribution by severity
- **Audit Status**: Audit plan status breakdown
- **Risk Heatmap**: Likelihood vs impact matrix
- **SBP Compliance**: Circular compliance by category
- **User Activity**: User activity trends

### Role-Based Dashboards

| Role | Key Widgets |
|------|-------------|
| Super Admin | All widgets + system health |
| Admin | Compliance, risk, audit, users |
| Compliance Officer | Compliance, tasks, SBP, AI insights |
| Risk Manager | Risk score, heatmap, assessments |
| Internal Auditor | Audit findings, progress |
| Department Head | Department compliance, tasks |
| User | Tasks, activities, notifications |

### Quick Actions

Role-specific quick action buttons for common tasks:
- Add User
- Generate Report
- New Task
- Add Risk
- New Audit
- View Profile

## Installation

1. Copy module to `modules/dashboard/`
2. Load module in application bootstrap
3. Configure settings in `config.php`
4. Add routes to main router

## Configuration

### Module Settings

```php
// Refresh interval (seconds)
define('DASHBOARD_REFRESH_INTERVAL', 300);

// Widget configuration
define('DASHBOARD_WIDGETS_CONFIG', [
    'compliance_score' => [
        'title' => 'Compliance Score',
        'icon' => 'fa-check-circle',
        'color' => '#2563EB'
    ]
]);

// Role-based widgets
define('DASHBOARD_ROLE_WIDGETS', [
    'super_admin' => ['compliance_score', 'risk_score', ...]
]);