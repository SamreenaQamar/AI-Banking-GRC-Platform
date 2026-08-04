<?php
/**
 * AI Banking GRC Platform - Session Library
 * 
 * @package    AI-Banking-GRC-Platform
 * @subpackage app/Libraries
 * @version    1.0.0
 * @author     GRC Platform Team
 * @copyright  2026 AI Banking GRC Platform
 * @license    Proprietary
 * 
 * This library provides enterprise session management:
 * - Secure session handling
 * - Flash messages
 * - Session regeneration
 * - Session expiration
 * - Session data management
 */

declare(strict_types=1);

namespace App\Libraries;

class Session
{
    /**
     * @var bool Whether session is started
     */
    private bool $started = false;

    /**
     * @var string Session name
     */
    private string $name = 'grc_session';

    /**
     * @var int Session lifetime in seconds
     */
    private int $lifetime = 3600;

    /**
     * Constructor
     */
    public function __construct()
    {
        $this->start();
    }

    /**
     * Start session
     * 
     * @return bool
     */
    public function start(): bool
    {
        if ($this->started) {
            return true;
        }

        if (session_status() === PHP_SESSION_ACTIVE) {
            $this->started = true;
            return true;
        }

        // Set session parameters
        session_name($this->name);
        session_set_cookie_params([
            'lifetime' => $this->lifetime,
            'path' => '/',
            'domain' => '',
            'secure' => isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on',
            'httponly' => true,
            'samesite' => 'Strict'
        ]);

        $this->started = session_start();
        return $this->started;
    }

    /**
     * Set session value
     * 
     * @param string $key
     * @param mixed $value
     * @return void
     */
    public function set(string $key, $value): void
    {
        $_SESSION[$key] = $value;
    }

    /**
     * Get session value
     * 
     * @param string $key
     * @param mixed $default
     * @return mixed
     */
    public function get(string $key, $default = null)
    {
        return $_SESSION[$key] ?? $default;
    }

    /**
     * Check if session key exists
     * 
     * @param string $key
     * @return bool
     */
    public function has(string $key): bool
    {
        return isset($_SESSION[$key]);
    }

    /**
     * Remove session key
     * 
     * @param string $key
     * @return void
     */
    public function remove(string $key): void
    {
        unset($_SESSION[$key]);
    }

    /**
     * Destroy session
     * 
     * @return bool
     */
    public function destroy(): bool
    {
        if (!$this->started) {
            return true;
        }

        $_SESSION = [];

        if (ini_get('session.use_cookies')) {
            $params = session_get_cookie_params();
            setcookie(
                session_name(),
                '',
                time() - 42000,
                $params['path'],
                $params['domain'],
                $params['secure'],
                $params['httponly']
            );
        }

        $this->started = false;
        return session_destroy();
    }

    /**
     * Regenerate session ID
     * 
     * @param bool $deleteOldSession
     * @return bool
     */
    public function regenerate(bool $deleteOldSession = true): bool
    {
        return session_regenerate_id($deleteOldSession);
    }

    /**
     * Set flash message
     * 
     * @param string $type
     * @param string $message
     * @return void
     */
    public function flash(string $type, string $message): void
    {
        if (!isset($_SESSION['_flash'])) {
            $_SESSION['_flash'] = [];
        }
        $_SESSION['_flash'][$type][] = $message;
    }

    /**
     * Get flash messages
     * 
     * @param string|null $type
     * @return array
     */
    public function getFlash(?string $type = null): array
    {
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
     * Check if flash exists
     * 
     * @param string|null $type
     * @return bool
     */
    public function hasFlash(?string $type = null): bool
    {
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
    public function all(): array
    {
        return $_SESSION;
    }

    /**
     * Clear all session data
     * 
     * @return void
     */
    public function clear(): void
    {
        $_SESSION = [];
    }

    /**
     * Get session ID
     * 
     * @return string
     */
    public function getId(): string
    {
        return session_id();
    }

    /**
     * Set session expiration
     * 
     * @param int $lifetime
     * @return void
     */
    public function setExpiration(int $lifetime): void
    {
        $this->lifetime = $lifetime;
        session_set_cookie_params($lifetime);
    }

    /**
     * Get session name
     * 
     * @return string
     */
    public function getName(): string
    {
        return $this->name;
    }

    /**
     * Set session name
     * 
     * @param string $name
     * @return void
     */
    public function setName(string $name): void
    {
        $this->name = $name;
    }

    /**
     * Check if session is started
     * 
     * @return bool
     */
    public function isStarted(): bool
    {
        return $this->started;
    }

    /**
     * Push value to array session key
     * 
     * @param string $key
     * @param mixed $value
     * @return void
     */
    public function push(string $key, $value): void
    {
        if (!isset($_SESSION[$key])) {
            $_SESSION[$key] = [];
        }
        if (!is_array($_SESSION[$key])) {
            $_SESSION[$key] = [$_SESSION[$key]];
        }
        $_SESSION[$key][] = $value;
    }

    /**
     * Pull value from session
     * 
     * @param string $key
     * @param mixed $default
     * @return mixed
     */
    public function pull(string $key, $default = null)
    {
        $value = $this->get($key, $default);
        $this->remove($key);
        return $value;
    }
}