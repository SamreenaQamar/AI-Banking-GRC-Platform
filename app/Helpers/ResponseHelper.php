<?php
/**
 * AI Banking GRC Platform - Response Helper
 * 
 * @package    AI-Banking-GRC-Platform
 * @subpackage app/Helpers
 * @version    1.0.0
 * @author     GRC Platform Team
 * @copyright  2026 AI Banking GRC Platform
 * @license    Proprietary
 * 
 * This helper provides HTTP response functionality:
 * - JSON responses
 * - Redirects
 - File downloads
 * - Status responses
 */

declare(strict_types=1);

namespace App\Helpers;

class ResponseHelper
{
    /**
     * Send JSON success response
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
     * Send JSON error response
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
     * Send JSON response
     * 
     * @param array $data
     * @param int $statusCode
     * @param array $headers
     * @return void
     */
    public static function json(array $data, int $statusCode = 200, array $headers = []): void
    {
        // Clear any output buffers
        while (ob_get_level()) {
            ob_end_clean();
        }

        http_response_code($statusCode);
        header('Content-Type: application/json; charset=utf-8');

        foreach ($headers as $name => $value) {
            header("{$name}: {$value}");
        }

        echo json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
        exit;
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
     * Redirect back to previous page
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
     * Redirect to route
     * 
     * @param string $route
     * @param array $params
     * @return void
     */
    public static function route(string $route, array $params = []): void
    {
        $url = BASE_URL . '/' . ltrim($route, '/');
        if (!empty($params)) {
            $url .= '?' . http_build_query($params);
        }
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
     * Send file to browser for viewing
     * 
     * @param string $filePath
     * @param string|null $filename
     * @return void
     */
    public static function inline(string $filePath, ?string $filename = null): void
    {
        if (!file_exists($filePath)) {
            self::error('File not found', [], 404);
        }

        $filename = $filename ?? basename($filePath);
        $mimeType = mime_content_type($filePath) ?: 'application/octet-stream';

        // Clear output buffers
        while (ob_get_level()) {
            ob_end_clean();
        }

        header('Content-Type: ' . $mimeType);
        header('Content-Disposition: inline; filename="' . $filename . '"');
        header('Cache-Control: private, max-age=0, must-revalidate');

        readfile($filePath);
        exit;
    }

    /**
     * Send file to browser as CSV
     * 
     * @param array $data
     * @param string $filename
     * @return void
     */
    public static function csv(array $data, string $filename = 'export.csv'): void
    {
        // Clear output buffers
        while (ob_get_level()) {
            ob_end_clean();
        }

        header('Content-Type: text/csv; charset=utf-8');
        header('Content-Disposition: attachment; filename="' . $filename . '"');

        $output = fopen('php://output', 'w');
        
        // Add BOM for UTF-8
        fwrite($output, "\xEF\xBB\xBF");

        // Headers
        if (!empty($data)) {
            fputcsv($output, array_keys($data[0]));
        }

        // Data
        foreach ($data as $row) {
            fputcsv($output, $row);
        }

        fclose($output);
        exit;
    }

    /**
     * Send HTML response
     * 
     * @param string $html
     * @param int $statusCode
     * @return void
     */
    public static function html(string $html, int $statusCode = 200): void
    {
        http_response_code($statusCode);
        header('Content-Type: text/html; charset=utf-8');
        echo $html;
        exit;
    }

    /**
     * Send plain text response
     * 
     * @param string $text
     * @param int $statusCode
     * @return void
     */
    public static function text(string $text, int $statusCode = 200): void
    {
        http_response_code($statusCode);
        header('Content-Type: text/plain; charset=utf-8');
        echo $text;
        exit;
    }

    /**
     * Send 404 Not Found response
     * 
     * @param string $message
     * @return void
     */
    public static function notFound(string $message = 'Page not found'): void
    {
        http_response_code(404);
        if (SecurityHelper::isAjax()) {
            self::error($message, [], 404);
        } else {
            // Load 404 view
            require_once VIEW_PATH . '/errors/404.php';
        }
        exit;
    }

    /**
     * Send 403 Forbidden response
     * 
     * @param string $message
     * @return void
     */
    public static function forbidden(string $message = 'Access denied'): void
    {
        http_response_code(403);
        if (SecurityHelper::isAjax()) {
            self::error($message, [], 403);
        } else {
            // Load 403 view
            require_once VIEW_PATH . '/errors/403.php';
        }
        exit;
    }

    /**
     * Send 401 Unauthorized response
     * 
     * @param string $message
     * @return void
     */
    public static function unauthorized(string $message = 'Unauthorized'): void
    {
        http_response_code(401);
        if (SecurityHelper::isAjax()) {
            self::error($message, [], 401);
        } else {
            // Load 401 view
            require_once VIEW_PATH . '/errors/401.php';
        }
        exit;
    }

    /**
     * Send 500 Internal Server Error response
     * 
     * @param string $message
     * @return void
     */
    public static function serverError(string $message = 'Internal server error'): void
    {
        http_response_code(500);
        if (SecurityHelper::isAjax()) {
            self::error($message, [], 500);
        } else {
            // Load 500 view
            require_once VIEW_PATH . '/errors/500.php';
        }
        exit;
    }

    /**
     * Redirect with flash message
     * 
     * @param string $url
     * @param string $type
     * @param string $message
     * @return void
     */
    public static function redirectWithFlash(string $url, string $type, string $message): void
    {
        SessionHelper::flash($type, $message);
        self::redirect($url);
    }

    /**
     * Back with flash message
     * 
     * @param string $type
     * @param string $message
     * @param string $default
     * @return void
     */
    public static function backWithFlash(string $type, string $message, string $default = '/'): void
    {
        SessionHelper::flash($type, $message);
        self::back($default);
    }

    /**
     * Route with flash message
     * 
     * @param string $route
     * @param string $type
     * @param string $message
     * @param array $params
     * @return void
     */
    public static function routeWithFlash(string $route, string $type, string $message, array $params = []): void
    {
        SessionHelper::flash($type, $message);
        self::route($route, $params);
    }

    /**
     * Send view response
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
            self::notFound('View not found: ' . $view);
        }
        
        require_once $viewPath;
        exit;
    }
}