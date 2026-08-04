<?php
/**
 * AI Banking GRC Platform - Security Helper
 * 
 * @package    AI-Banking-GRC-Platform
 * @subpackage app/Helpers
 * @version    1.0.0
 * @author     GRC Platform Team
 * @copyright  2026 AI Banking GRC Platform
 * @license    Proprietary
 * 
 * This helper provides security-related functionality:
 * - Input sanitization and escaping
 * - CSRF token generation and verification
 * - Password hashing and verification
 * - Random string generation
 * - XSS protection
 */

declare(strict_types=1);

namespace App\Helpers;

class SecurityHelper
{
    /**
     * Sanitize input data
     * 
     * @param mixed $data
     * @return mixed
     */
    public static function sanitize($data)
    {
        if (is_array($data)) {
            return array_map([self::class, 'sanitize'], $data);
        }

        if (is_string($data)) {
            // Remove HTML tags
            $data = strip_tags($data);
            // Convert special characters to HTML entities
            $data = htmlspecialchars($data, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
            // Remove null bytes
            $data = str_replace(chr(0), '', $data);
        }

        return $data;
    }

    /**
     * Escape output for HTML
     * 
     * @param string $string
     * @return string
     */
    public static function escape(string $string): string
    {
        return htmlspecialchars($string, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
    }

    /**
     * Escape output for JavaScript
     * 
     * @param string $string
     * @return string
     */
    public static function escapeJs(string $string): string
    {
        return json_encode($string, JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT);
    }

    /**
     * Escape output for URL
     * 
     * @param string $string
     * @return string
     */
    public static function escapeUrl(string $string): string
    {
        return urlencode($string);
    }

    /**
     * Generate CSRF token
     * 
     * @return string
     */
    public static function csrfToken(): string
    {
        if (!SessionHelper::has('csrf_token')) {
            $token = self::generateRandomString(32);
            SessionHelper::set('csrf_token', $token);
        }
        return SessionHelper::get('csrf_token');
    }

    /**
     * Verify CSRF token
     * 
     * @param string $token
     * @return bool
     */
    public static function verifyCsrf(string $token): bool
    {
        $storedToken = SessionHelper::get('csrf_token');
        if (empty($storedToken)) {
            return false;
        }
        return hash_equals($storedToken, $token);
    }

    /**
     * Generate random string
     * 
     * @param int $length
     * @param bool $cryptographicallySecure
     * @return string
     */
    public static function generateRandomString(int $length = 32, bool $cryptographicallySecure = true): string
    {
        if ($cryptographicallySecure) {
            return bin2hex(random_bytes($length));
        }

        $characters = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
        $randomString = '';
        $maxIndex = strlen($characters) - 1;

        for ($i = 0; $i < $length; $i++) {
            $randomString .= $characters[mt_rand(0, $maxIndex)];
        }

        return $randomString;
    }

    /**
     * Hash password using bcrypt
     * 
     * @param string $password
     * @param int $cost
     * @return string
     */
    public static function hashPassword(string $password, int $cost = 12): string
    {
        return password_hash($password, PASSWORD_BCRYPT, ['cost' => $cost]);
    }

    /**
     * Verify password against hash
     * 
     * @param string $password
     * @param string $hash
     * @return bool
     */
    public static function verifyPassword(string $password, string $hash): bool
    {
        return password_verify($password, $hash);
    }

    /**
     * Check if password needs rehash
     * 
     * @param string $hash
     * @param int $cost
     * @return bool
     */
    public static function passwordNeedsRehash(string $hash, int $cost = 12): bool
    {
        return password_needs_rehash($hash, PASSWORD_BCRYPT, ['cost' => $cost]);
    }

    /**
     * Generate secure token
     * 
     * @return string
     */
    public static function generateToken(): string
    {
        return bin2hex(random_bytes(32));
    }

    /**
     * Generate UUID v4
     * 
     * @return string
     */
    public static function generateUuid(): string
    {
        $data = random_bytes(16);
        $data[6] = chr(ord($data[6]) & 0x0f | 0x40);
        $data[8] = chr(ord($data[8]) & 0x3f | 0x80);
        return vsprintf('%s%s-%s-%s-%s-%s%s%s', str_split(bin2hex($data), 4));
    }

    /**
     * Validate CSRF token from request
     * 
     * @param string $token
     * @return bool
     */
    public static function validateCsrfToken(string $token): bool
    {
        return self::verifyCsrf($token);
    }

    /**
     * Get CSRF token field HTML
     * 
     * @return string
     */
    public static function csrfField(): string
    {
        return '<input type="hidden" name="csrf_token" value="' . self::csrfToken() . '">';
    }

    /**
     * Get CSRF token meta tag
     * 
     * @return string
     */
    public static function csrfMeta(): string
    {
        return '<meta name="csrf-token" content="' . self::csrfToken() . '">';
    }

    /**
     * Validate email address
     * 
     * @param string $email
     * @return bool
     */
    public static function validateEmail(string $email): bool
    {
        return filter_var($email, FILTER_VALIDATE_EMAIL) !== false;
    }

    /**
     * Validate URL
     * 
     * @param string $url
     * @return bool
     */
    public static function validateUrl(string $url): bool
    {
        return filter_var($url, FILTER_VALIDATE_URL) !== false;
    }

    /**
     * Validate IP address
     * 
     * @param string $ip
     * @return bool
     */
    public static function validateIp(string $ip): bool
    {
        return filter_var($ip, FILTER_VALIDATE_IP) !== false;
    }

    /**
     * Clean filename for security
     * 
     * @param string $filename
     * @return string
     */
    public static function cleanFilename(string $filename): string
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
     * Get client IP address
     * 
     * @return string
     */
    public static function getClientIp(): string
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
                if (self::validateIp($ip)) {
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
    public static function getUserAgent(): string
    {
        return $_SERVER['HTTP_USER_AGENT'] ?? '';
    }

    /**
     * Check if request is AJAX
     * 
     * @return bool
     */
    public static function isAjax(): bool
    {
        return isset($_SERVER['HTTP_X_REQUESTED_WITH']) &&
               strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest';
    }

    /**
     * Check if request is HTTPS
     * 
     * @return bool
     */
    public static function isHttps(): bool
    {
        return isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on';
    }
}