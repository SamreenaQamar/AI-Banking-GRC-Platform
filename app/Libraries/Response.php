<?php
/**
 * AI Banking GRC Platform - Response Library
 * 
 * @package    AI-Banking-GRC-Platform
 * @subpackage app/Libraries
 * @version    1.0.0
 * @author     GRC Platform Team
 * @copyright  2026 AI Banking GRC Platform
 * @license    Proprietary
 * 
 * This library provides enterprise response handling:
 * - JSON responses
 * - Success/Error responses
 * - API responses
 * - HTTP status codes
 * - Redirects
 * - File downloads
 * - View rendering
 */

declare(strict_types=1);

namespace App\Libraries;

use App\Libraries\Security;
use App\Libraries\Session;

class Response
{
    /**
     * @var array Response data
     */
    private array $data = [];

    /**
     * @var int HTTP status code
     */
    private int $statusCode = 200;

    /**
     * @var array Headers
     */
    private array $headers = [];

    /**
     * @var Security Security instance
     */
    private Security $security;

    /**
     * @var Session Session instance
     */
    private Session $session;

    /**
     * Constructor
     */
    public function __construct()
    {
        $this->security = new Security();
        $this->session = new Session();
    }

    /**
     * JSON response
     * 
     * @param mixed $data
     * @param int $statusCode
     * @return void
     */
    public static function json($data, int $statusCode = 200): void
    {
        http_response_code($statusCode);
        header('Content-Type: application/json; charset=utf-8');
        
        // Add security headers
        $security = new Security();
        $security->securityHeaders();

        echo json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
        exit;
    }

    /**
     * Success response
     * 
     * @param string $message
     * @param array $data
     * @param int $statusCode
     * @return void
     */
    public static function success(string $message, array $data = [], int $statusCode = 200): void
    {
        self::json([
            'success' => true,
            'message' => $message,
            'data' => $data
        ], $statusCode);
    }

    /**
     * Error response
     * 
     * @param string $message
     * @param array $errors
     * @param int $statusCode
     * @return void
     */
    public static function error(string $message, array $errors = [], int $statusCode = 400): void
    {
        self::json([
            'success' => false,
            'message' => $message,
            'errors' => $errors
        ], $statusCode);
    }

    /**
     * API response
     * 
     * @param array $data
     * @param int $statusCode
     * @return void
     */
    public static function api(array $data, int $statusCode = 200): void
    {
        self::json($data, $statusCode);
    }

    /**
     * Redirect to URL
     * 
     * @param string $url
     * @param int $statusCode
     * @return void
     */
    public static function redirect(string $url, int $statusCode = 302): void
    {
        if (!headers_sent()) {
            header("Location: {$url}", true, $statusCode);
        } else {
            echo "<script>window.location.href='{$url}';</script>";
        }
        exit;
    }

    /**
     * Redirect back
     * 
     * @param string $default
     * @return void
     */
    public static function back(string $default = '/'): void
    {
        $referer = $_SERVER['HTTP_REFERER'] ?? $default;
        self::redirect($referer);
    }

    /**
     * Redirect with flash
     * 
     * @param string $url
     * @param string $type
     * @param string $message
     * @return void
     */
    public static function redirectWithFlash(string $url, string $type, string $message): void
    {
        $session = new Session();
        $session->flash($type, $message);
        self::redirect($url);
    }

    /**
     * Download file
     * 
     * @param string $filePath
     * @param string|null $filename
     * @param bool $deleteAfter
     * @return void
     */
    public static function download(string $filePath, ?string $filename = null, bool $deleteAfter = false): void
    {
        if (!file_exists($filePath)) {
            self::error('File not found', [], 404);
        }

        $filename = $filename ?? basename($filePath);
        $mimeType = mime_content_type($filePath) ?: 'application/octet-stream';
        $fileSize = filesize($filePath);

        // Clear output buffers
        while (ob_get_level()) {
            ob_end_clean();
        }

        header('Content-Type: ' . $mimeType);
        header('Content-Disposition: attachment; filename="' . $filename . '"');
        header('Content-Length: ' . $fileSize);
        header('Cache-Control: private, max-age=0, must-revalidate');
        header('Pragma: public');

        readfile($filePath);

        if ($deleteAfter) {
            unlink($filePath);
        }

        exit;
    }

    /**
     * View response
     * 
     * @param string $view
     * @param array $data
     * @param int $statusCode
     * @return void
     */
    public static function view(string $view, array $data = [], int $statusCode = 200): void
    {
        http_response_code($statusCode);
        
        // Extract data for view
        extract($data);
        
        // Include view file
        $viewPath = VIEW_PATH . '/' . str_replace('.', '/', $view) . '.php';
        
        if (!file_exists($viewPath)) {
            self::error('View not found: ' . $view, [], 404);
        }
        
        require_once $viewPath;
        exit;
    }

    /**
     * Set status code
     * 
     * @param int $code
     * @return self
     */
    public function status(int $code): self
    {
        $this->statusCode = $code;
        return $this;
    }

    /**
     * Set header
     * 
     * @param string $name
     * @param string $value
     * @return self
     */
    public function header(string $name, string $value): self
    {
        $this->headers[$name] = $value;
        return $this;
    }

    /**
     * Set data
     * 
     * @param array $data
     * @return self
     */
    public function data(array $data): self
    {
        $this->data = $data;
        return $this;
    }

    /**
     * Send response
     * 
     * @return void
     */
    public function send(): void
    {
        // Apply headers
        foreach ($this->headers as $name => $value) {
            header("{$name}: {$value}");
        }

        http_response_code($this->statusCode);
        
        // Security headers
        $this->security->securityHeaders();

        echo json_encode($this->data, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
        exit;
    }

    /**
     * Send no content
     * 
     * @param int $statusCode
     * @return void
     */
    public static function noContent(int $statusCode = 204): void
    {
        http_response_code($statusCode);
        exit;
    }

    /**
     * Send not found
     * 
     * @param string $message
     * @return void
     */
    public static function notFound(string $message = 'Not Found'): void
    {
        self::error($message, [], 404);
    }

    /**
     * Send unauthorized
     * 
     * @param string $message
     * @return void
     */
    public static function unauthorized(string $message = 'Unauthorized'): void
    {
        self::error($message, [], 401);
    }

    /**
     * Send forbidden
     * 
     * @param string $message
     * @return void
     */
    public static function forbidden(string $message = 'Forbidden'): void
    {
        self::error($message, [], 403);
    }
}