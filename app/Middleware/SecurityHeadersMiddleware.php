<?php
/**
 * AI Banking GRC Platform - Security Headers Middleware
 * 
 * @package    AI-Banking-GRC-Platform
 * @subpackage app/Middleware
 * @version    1.0.0
 * @author     GRC Platform Team
 * @copyright  2026 AI Banking GRC Platform
 * @license    Proprietary
 * 
 * This middleware automatically sends security headers:
 * - X-Frame-Options
 * - X-XSS-Protection
 * - Content-Security-Policy
 * - Strict-Transport-Security
 * - Referrer-Policy
 * - Permissions-Policy
 * - X-Content-Type-Options
 */

declare(strict_types=1);

namespace App\Middleware;

use App\Libraries\Logger;

class SecurityHeadersMiddleware
{
    /**
     * @var Logger Logger instance
     */
    private Logger $logger;

    /**
     * @var array Security headers
     */
    private array $headers = [];

    /**
     * @var bool Whether headers have been sent
     */
    private bool $sent = false;

    /**
     * Constructor
     */
    public function __construct()
    {
        $this->logger = new Logger();
        $this->loadDefaultHeaders();
    }

    /**
     * Handle the request
     * 
     * @param array $params
     * @return mixed
     */
    public function handle(array $params = []): mixed
    {
        try {
            // Send security headers
            $this->sendHeaders();

            $this->logger->debug('Security headers sent');

            return null;

        } catch (\Exception $e) {
            $this->logger->error('SecurityHeadersMiddleware error: ' . $e->getMessage());
            return null; // Continue on error
        }
    }

    /**
     * Terminate the request
     * 
     * @param mixed $response
     * @return void
     */
    public function terminate($response): void
    {
        $this->logger->debug('SecurityHeadersMiddleware terminated');
    }

    /**
     * Load default security headers
     * 
     * @return void
     */
    private function loadDefaultHeaders(): void
    {
        // X-Frame-Options - Prevent clickjacking
        $this->headers['X-Frame-Options'] = 'DENY';

        // X-XSS-Protection - Enable XSS filtering
        $this->headers['X-XSS-Protection'] = '1; mode=block';

        // X-Content-Type-Options - Prevent MIME type sniffing
        $this->headers['X-Content-Type-Options'] = 'nosniff';

        // Referrer-Policy - Control referrer information
        $this->headers['Referrer-Policy'] = 'strict-origin-when-cross-origin';

        // Permissions-Policy - Restrict browser features
        $this->headers['Permissions-Policy'] = 'geolocation=(), microphone=(), camera=(), payment=(), usb=()';

        // Content-Security-Policy (CSP) - Basic security policy
        $this->headers['Content-Security-Policy'] = $this->getDefaultCSP();

        // Strict-Transport-Security (HSTS) - Only in production
        if (getenv('APP_ENV') === 'production') {
            $this->headers['Strict-Transport-Security'] = 'max-age=31536000; includeSubDomains; preload';
        }

        // Remove server information
        header_remove('X-Powered-By');
    }

    /**
     * Send headers
     * 
     * @return void
     */
    private function sendHeaders(): void
    {
        if ($this->sent || headers_sent()) {
            return;
        }

        foreach ($this->headers as $name => $value) {
            header("{$name}: {$value}");
        }

        $this->sent = true;
    }

    /**
     * Get default CSP
     * 
     * @return string
     */
    private function getDefaultCSP(): string
    {
        $csp = "default-src 'self'";
        $csp .= "; script-src 'self' 'unsafe-inline' 'unsafe-eval' https://cdn.jsdelivr.net https://code.jquery.com https://cdnjs.cloudflare.com";
        $csp .= "; style-src 'self' 'unsafe-inline' https://cdn.jsdelivr.net https://fonts.googleapis.com";
        $csp .= "; font-src 'self' https://fonts.gstatic.com https://cdnjs.cloudflare.com";
        $csp .= "; img-src 'self' data: https:";
        $csp .= "; connect-src 'self'";
        $csp .= "; frame-src 'none'";
        $csp .= "; object-src 'none'";
        $csp .= "; base-uri 'self'";
        $csp .= "; form-action 'self'";

        return $csp;
    }

    /**
     * Add custom header
     * 
     * @param string $name
     * @param string $value
     * @return void
     */
    public function addHeader(string $name, string $value): void
    {
        $this->headers[$name] = $value;
    }

    /**
     * Remove header
     * 
     * @param string $name
     * @return void
     */
    public function removeHeader(string $name): void
    {
        unset($this->headers[$name]);
    }

    /**
     * Set CSP
     * 
     * @param string $csp
     * @return void
     */
    public function setCSP(string $csp): void
    {
        $this->headers['Content-Security-Policy'] = $csp;
    }

    /**
     * Enable HSTS
     * 
     * @param int $maxAge
     * @param bool $includeSubDomains
     * @param bool $preload
     * @return void
     */
    public function enableHSTS(int $maxAge = 31536000, bool $includeSubDomains = true, bool $preload = true): void
    {
        $hsts = "max-age={$maxAge}";
        if ($includeSubDomains) {
            $hsts .= "; includeSubDomains";
        }
        if ($preload) {
            $hsts .= "; preload";
        }
        $this->headers['Strict-Transport-Security'] = $hsts;
    }

    /**
     * Disable HSTS
     * 
     * @return void
     */
    public function disableHSTS(): void
    {
        unset($this->headers['Strict-Transport-Security']);
    }

    /**
     * Get all headers
     * 
     * @return array
     */
    public function getHeaders(): array
    {
        return $this->headers;
    }
}