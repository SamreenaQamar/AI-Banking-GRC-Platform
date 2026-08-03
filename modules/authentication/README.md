# Authentication Module

## Overview

The Authentication Module provides secure user authentication, authorization, and session management for the AI Banking GRC Platform.

## Features

- **User Authentication**
  - Login with username or email
  - Password hashing with bcrypt
  - Remember me functionality
  - Account lockout after failed attempts
  - Session management with regeneration

- **Registration**
  - User registration with validation
  - Email verification
  - Role assignment
  - Profile creation

- **Password Management**
  - Password reset with email verification
  - Password change for authenticated users
  - Password strength requirements
  - Password history tracking

- **Two-Factor Authentication**
  - TOTP-based 2FA
  - QR code generation for authenticator apps
  - Recovery codes
  - 2FA enable/disable

- **Session Management**
  - Active session listing
  - Session termination
  - Device tracking
  - IP-based session validation

- **Security Features**
  - CSRF protection
  - XSS protection
  - SQL injection prevention
  - Rate limiting
  - Secure cookie settings

## Installation

1. Copy the module to the `modules/authentication` directory
2. Load the module in the application bootstrap
3. Configure the module settings in `config.php`
4. Run database migrations

## Configuration

The module can be configured through the `config.php` file:

```php
// Session configuration
define('AUTH_SESSION_LIFETIME', 3600);
define('AUTH_MAX_LOGIN_ATTEMPTS', 5);
define('AUTH_LOCKOUT_DURATION', 15);

// Password settings
define('AUTH_PASSWORD_MIN_LENGTH', 8);
define('AUTH_PASSWORD_REQUIRE_UPPERCASE', true);

// Two-factor authentication
define('AUTH_2FA_ENABLED', true);
define('AUTH_2FA_ISSUER', 'AI Banking GRC Platform');