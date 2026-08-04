<?php
/**
 * AI Banking GRC Platform - Session Helper
 * 
 * @package    AI-Banking-GRC-Platform
 * @subpackage app/Helpers
 * @version    1.0.0
 * @author     GRC Platform Team
 * @copyright  2026 AI Banking GRC Platform
 * @license    Proprietary
 * 
 * This helper provides session management functionality:
 * - Session data manipulation
 * - Flash messages
 * - Session security
 * - Session destruction
 */

declare(strict_types=1);

namespace App\Helpers;

class SessionHelper
{
    /**
     * Start session if not already started
     * 
     * @return void
     */
    private static function ensureStarted(): void
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
    }

    /**
     * Set a session value
     * 
     * @param string $key
     * @param mixed $value
     * @return void
     */
    public static function set(string $key, $value): void
    {
        self::ensureStarted();
        $_SESSION[$key] = $value;
    }

    /**
     * Get a session value
     * 
     * @param string $key
     * @param mixed $default
     * @return mixed
     */
    public static function get(string $key, $default = null)
    {
        self::ensureStarted();
        return $_SESSION[$key] ?? $default;
    }

    /**
     * Check if session key exists
     * 
     * @param string $key
     * @return bool
     */
    public static function has(string $key): bool
    {
        self::ensureStarted();
        return isset($_SESSION[$key]);
    }

    /**
     * Remove a session value
     * 
     * @param string $key
     * @return void
     */
    public static function remove(string $key): void
    {
        self::ensureStarted();
        unset($_SESSION[$key]);
    }

    /**
     * Destroy the entire session
     * 
     * @return void
     */
    public static function destroy(): void
    {
        self::ensureStarted();
        $_SESSION = [];
        session_destroy();
    }

    /**
     * Regenerate session ID
     * 
     * @param bool $deleteOldSession
     * @return void
     */
    public static function regenerate(bool $deleteOldSession = true): void
    {
        self::ensureStarted();
        session_regenerate_id($deleteOldSession);
    }

    /**
     * Set a flash message
     * 
     * @param string $type
     * @param string $message
     * @return void
     */
    public static function flash(string $type, string $message): void
    {
        self::ensureStarted();
        if (!isset($_SESSION['_flash'])) {
            $_SESSION['_flash'] = [];
        }
        $_SESSION['_flash'][$type][] = $message;
    }

    /**
     * Get flash messages and clear them
     * 
     * @param string|null $type
     * @return array
     */
    public static function getFlash(?string $type = null): array
    {
        self::ensureStarted();
        
        if (!isset($_SESSION['_flash'])) {
            return [];
        }

        if ($type !== null) {
            $messages = $_SESSION['_flash'][$type] ?? [];
            unset($_SESSION['_flash'][$type]);
            return $messages;
        }

        $flash = $_SESSION['_flash'];
        unset($_SESSION['_flash']);
        return $flash;
    }

    /**
     * Check if flash messages exist
     * 
     * @param string|null $type
     * @return bool
     */
    public static function hasFlash(?string $type = null): bool
    {
        self::ensureStarted();
        
        if (!isset($_SESSION['_flash'])) {
            return false;
        }

        if ($type !== null) {
            return isset($_SESSION['_flash'][$type]) && !empty($_SESSION['_flash'][$type]);
        }

        return !empty($_SESSION['_flash']);
    }

    /**
     * Get all session data
     * 
     * @return array
     */
    public static function all(): array
    {
        self::ensureStarted();
        return $_SESSION;
    }

    /**
     * Clear all session data but keep session active
     * 
     * @return void
     */
    public static function clear(): void
    {
        self::ensureStarted();
        $_SESSION = [];
    }

    /**
     * Get session ID
     * 
     * @return string
     */
    public static function getId(): string
    {
        self::ensureStarted();
        return session_id();
    }

    /**
     * Set session ID
     * 
     * @param string $id
     * @return void
     */
    public static function setId(string $id): void
    {
        self::ensureStarted();
        session_id($id);
    }

    /**
     * Push value to array session key
     * 
     * @param string $key
     * @param mixed $value
     * @return void
     */
    public static function push(string $key, $value): void
    {
        self::ensureStarted();
        if (!isset($_SESSION[$key])) {
            $_SESSION[$key] = [];
        }
        if (!is_array($_SESSION[$key])) {
            $_SESSION[$key] = [$_SESSION[$key]];
        }
        $_SESSION[$key][] = $value;
    }

    /**
     * Pull a value from session and remove it
     * 
     * @param string $key
     * @param mixed $default
     * @return mixed
     */
    public static function pull(string $key, $default = null)
    {
        self::ensureStarted();
        $value = $_SESSION[$key] ?? $default;
        unset($_SESSION[$key]);
        return $value;
    }
}