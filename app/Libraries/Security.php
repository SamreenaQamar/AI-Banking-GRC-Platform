<?php
/**
 * AI Banking GRC Platform - Security Library
 * 
 * @package    AI-Banking-GRC-Platform
 * @subpackage app/Libraries
 * @version    1.0.0
 * @author     GRC Platform Team
 * @copyright  2026 AI Banking GRC Platform
 * @license    Proprietary
 * 
 * This library provides enterprise security functionality:
 * - CSRF protection
 * - XSS prevention
 * - SQL injection protection
 * - Security headers
 * - Rate limiting
 * - Input sanitization
 * - Token generation
 * - Secure cookies
 */

declare(strict_types=1);

namespace App\Libraries;

use App\Libraries\Session;
use App\Libraries\Logger;

class Security
{
    /**
     * @var Session Session instance
     */
    private Session $session;

    /**
     * @var Logger Logger instance
     */
    private Logger $logger;

    /**
     * @var string CSRF token name
     */
    private string $csrfTokenName = 'csrf_token';

    /**
     * @var int CSRF token lifetime
     */
    private int $csrfLifetime = 3600;

    /**
     * Constructor
     */
    public function __construct()
    {
        $this->session = new Session();
        $this->logger = new Logger();
    }

    /**
     * Generate CSRF token
     * 
     * @return string
     */
    public function csrf(): string
    {
        if (!$this->session->has($this->csrfTokenName)) {
            $token = $this->generateToken(32);
            $this->session->set($this->csrfTokenName, $token);
            $this->session->set($this->csrfTokenName . '_time', time());
        }
        return $this->session->get($this->csrfTokenName);
    }

    /**
     * Verify CSRF token
     * 
     * @param string $token
     * @return bool
     */
    public function verifyCSRF(string $token): bool
    {
        $storedToken = $this->session->get($this->csrfTokenName);
        $tokenTime = $this->session->get($this->csrfTokenName . '_time');

        if (!$storedToken || !$tokenTime) {
            return false;
        }

        // Check if token is expired
        if (time() - $tokenTime > $this->csrfLifetime) {
            $this->session->remove($this->csrfTokenName);
            $this->session->remove($this->csrfTokenName . '_time');
            return false;
        }

        return hash_equals($storedToken, $token);
    }

    /**
     * Get CSRF field
     * 
     * @return string
     */
    public function csrfField(): string
    {
        $token = $this->csrf();
        return '<input type="hidden" name="' . $this->csrfTokenName . '" value="' . $token . '">';
    }

    /**
     * Get CSRF meta tag
     * 
     * @return string
     */
    public function csrfMeta(): string
    {
        $token = $this->csrf();
        return '<meta name="csrf-token" content="' . $token . '">';
    }

    /**
     * Sanitize input
     * 
     * @param string $input
     * @return string
     */
    public function sanitize(string $input): string
    {
        // Remove HTML tags
        $input = strip_tags($input);
        
        // Convert special characters to HTML entities
        $input = htmlspecialchars($input, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
        
        // Remove null bytes
        $input = str_replace(chr(0), '', $input);
        
        return $input;
    }

    /**
     * Sanitize array
     * 
     * @param array $input
     * @return array
     */
    public function sanitizeArray(array $input): array
    {
        foreach ($input as $key => $value) {
            if (is_array($value)) {
                $input[$key] = $this->sanitizeArray($value);
            } else {
                $input[$key] = $this->sanitize((string)$value);
            }
        }
        return $input;
    }

    /**
     * Escape HTML
     * 
     * @param string $input
     * @return string
     */
    public function escape(string $input): string
    {
        return htmlspecialchars($input, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
    }

    /**
     * Escape JavaScript
     * 
     * @param string $input
     * @return string
     */
    public function escapeJs(string $input): string
    {
        return json_encode($input, JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT);
    }

    /**
     * Clean filename
     * 
     * @param string $filename
     * @return string
     */
    public function cleanFilename(string $filename): string
    {
        // Remove any path information
        $filename = basename($filename);
        
        // Remove non-ASCII characters
        $filename = preg_replace('/[^\w\-\.]/', '', $filename);
        
        // Prevent null bytes
        $filename = str_replace(chr(0), '', $filename);
        
        return $filename;
    }

    /**
     * Generate secure token
     * 
     * @param int $length
     * @return string
     */
    public function generateToken(int $length = 32): string
    {
        return bin2hex(random_bytes($length));
    }

    /**
     * Verify token
     * 
     * @param string $token
     * @param string $storedToken
     * @return bool
     */
    public function verifyToken(string $token, string $storedToken): bool
    {
        return hash_equals($storedToken, $token);
    }

    /**
     * Set security headers
     * 
     * @return void
     */
    public function securityHeaders(): void
    {
        // Prevent XSS
        header('X-XSS-Protection: 1; mode=block');
        
        // Prevent MIME type sniffing
        header('X-Content-Type-Options: nosniff');
        
        // Prevent clickjacking
        header('X-Frame-Options: DENY');
        
        // Referrer policy
        header('Referrer-Policy: strict-origin-when-cross-origin');
        
        // Permissions policy
        header('Permissions-Policy: geolocation=(), microphone=(), camera=(), payment=(), usb=()');
        
        // HSTS (production only)
        if (getenv('APP_ENV') === 'production') {
            header('Strict-Transport-Security: max-age=31536000; includeSubDomains; preload');
        }
    }

    /**
     * Rate limit check
     * 
     * @param string $key
     * @param int $limit
     * @param int $window
     * @return bool
     */
    public function rateLimit(string $key, int $limit = 60, int $window = 60): bool
    {
        $rateLimiter = new RateLimiter();
        return !$rateLimiter->isLimited($key, $limit, $window);
    }

    /**
     * Set secure cookie
     * 
     * @param string $name
     * @param string $value
     * @param int $expires
     * @param string $path
     * @param bool $secure
     * @param bool $httponly
     * @return bool
     */
    public function setSecureCookie(
        string $name,
        string $value,
        int $expires = 0,
        string $path = '/',
        bool $secure = true,
        bool $httponly = true
    ): bool {
        return setcookie(
            $name,
            $value,
            $expires,
            $path,
            '',
            $secure,
            $httponly
        );
    }

    /**
     * Delete secure cookie
     * 
     * @param string $name
     * @param string $path
     * @return bool
     */
    public function deleteCookie(string $name, string $path = '/'): bool
    {
        return setcookie($name, '', time() - 3600, $path);
    }

    /**
     * Get client IP
     * 
     * @return string
     */
    public function getClientIp(): string
    {
        $ipAddresses = [
            'HTTP_CLIENT_IP',
            'HTTP_X_FORWARDED_FOR',
            'HTTP_X_FORWARDED',
            'HTTP_X_CLUSTER_CLIENT_IP',
            'HTTP_FORWARDED_FOR',
            'HTTP_FORWARDED',
            'REMOTE_ADDR'
        ];

        foreach ($ipAddresses as $key) {
            if (isset($_SERVER[$key])) {
                $ips = explode(',', $_SERVER[$key]);
                $ip = trim($ips[0]);
                if (filter_var($ip, FILTER_VALIDATE_IP)) {
                    return $ip;
                }
            }
        }

        return '0.0.0.0';
    }

    /**
     * Get user agent
     * 
     * @return string
     */
    public function getUserAgent(): string
    {
        return $_SERVER['HTTP_USER_AGENT'] ?? '';
    }

    /**
     * Check if request is AJAX
     * 
     * @return bool
     */
    public function isAjax(): bool
    {
        return isset($_SERVER['HTTP_X_REQUESTED_WITH']) &&
               strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest';
    }

    /**
     * Check if request is HTTPS
     * 
     * @return bool
     */
    public function isHttps(): bool
    {
        return isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on';
    }

    /**
     * Get request method
     * 
     * @return string
     */
    public function getMethod(): string
    {
        return $_SERVER['REQUEST_METHOD'] ?? 'GET';
    }

    /**
     * Check if method is allowed
     * 
     * @param array $allowedMethods
     * @return bool
     */
    public function isMethodAllowed(array $allowedMethods): bool
    {
        return in_array($this->getMethod(), $allowedMethods);
    }

    /**
     * Log security event
     * 
     * @param string $event
     * @param array $data
     * @return void
     */
    public function logSecurityEvent(string $event, array $data = []): void
    {
        $this->logger->security($event, $data);
    }
}