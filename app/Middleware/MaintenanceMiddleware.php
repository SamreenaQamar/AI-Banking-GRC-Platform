<?php
/**
 * AI Banking GRC Platform - Maintenance Middleware
 * 
 * @package    AI-Banking-GRC-Platform
 * @subpackage app/Middleware
 * @version    1.0.0
 * @author     GRC Platform Team
 * @copyright  2026 AI Banking GRC Platform
 * @license    Proprietary
 * 
 * This middleware handles maintenance mode:
 * - Maintenance mode detection
 * - Allow admin access during maintenance
 * - Maintenance page display
 * - Scheduled maintenance support
 * - IP whitelisting
 */

declare(strict_types=1);

namespace App\Middleware;

use App\Libraries\Authentication;
use App\Libraries\Authorization;
use App\Libraries\Logger;
use App\Libraries\Response;
use App\Libraries\Session;

class MaintenanceMiddleware
{
    /**
     * @var Authentication Authentication instance
     */
    private Authentication $auth;

    /**
     * @var Authorization Authorization instance
     */
    private Authorization $authorization;

    /**
     * @var Logger Logger instance
     */
    private Logger $logger;

    /**
     * @var Session Session instance
     */
    private Session $session;

    /**
     * @var bool Maintenance mode status
     */
    private bool $maintenanceMode = false;

    /**
     * @var string Maintenance message
     */
    private string $maintenanceMessage = 'The system is currently undergoing maintenance. Please check back later.';

    /**
     * @var array Whitelisted IPs
     */
    private array $whitelistedIps = [];

    /**
     * @var array Whitelisted routes
     */
    private array $whitelistedRoutes = ['/login', '/logout'];

    /**
     * @var int Maintenance start time
     */
    private ?int $maintenanceStart = null;

    /**
     * @var int Maintenance end time
     */
    private ?int $maintenanceEnd = null;

    /**
     * Constructor
     */
    public function __construct()
    {
        $this->auth = new Authentication();
        $this->authorization = new Authorization();
        $this->logger = new Logger();
        $this->session = new Session();

        // Check maintenance mode from settings
        $this->loadMaintenanceConfig();
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
            // Check if maintenance mode is enabled
            if (!$this->maintenanceMode) {
                return null;
            }

            $uri = $_SERVER['REQUEST_URI'] ?? '/';
            $ip = $_SERVER['REMOTE_ADDR'] ?? '0.0.0.0';

            // Check if maintenance is scheduled
            if ($this->isScheduledMaintenance()) {
                $this->logger->info('Scheduled maintenance in effect', [
                    'start' => $this->maintenanceStart,
                    'end' => $this->maintenanceEnd
                ]);
            }

            // Allow admin access during maintenance
            if ($this->isAdmin()) {
                $this->logger->info('Admin access granted during maintenance', [
                    'user_id' => $this->auth->id(),
                    'ip' => $ip
                ]);
                return null;
            }

            // Allow whitelisted IPs
            if (in_array($ip, $this->whitelistedIps)) {
                $this->logger->info('Whitelisted IP access during maintenance', ['ip' => $ip]);
                return null;
            }

            // Allow whitelisted routes
            foreach ($this->whitelistedRoutes as $route) {
                if (strpos($uri, $route) !== false) {
                    return null;
                }
            }

            // Check if maintenance is over
            if ($this->maintenanceEnd && time() > $this->maintenanceEnd) {
                $this->disableMaintenance();
                return null;
            }

            // Display maintenance page
            $this->logger->info('Maintenance page displayed', [
                'ip' => $ip,
                'uri' => $uri
            ]);

            // Check if AJAX request
            if ($this->isAjaxRequest()) {
                return Response::error($this->maintenanceMessage, [], 503);
            }

            // Render maintenance view
            return Response::view('errors.maintenance', [
                'message' => $this->maintenanceMessage,
                'start' => $this->maintenanceStart,
                'end' => $this->maintenanceEnd,
                'app_name' => APP_NAME ?? 'AI Banking GRC Platform'
            ], 503);

        } catch (\Exception $e) {
            $this->logger->error('MaintenanceMiddleware error: ' . $e->getMessage());
            
            // If maintenance mode breaks, disable it
            $this->disableMaintenance();
            
            if ($this->isAjaxRequest()) {
                return Response::error('Maintenance check error occurred.', [], 500);
            }

            return Response::view('errors.maintenance', [
                'message' => 'System maintenance check failed. Please try again later.',
                'app_name' => APP_NAME ?? 'AI Banking GRC Platform'
            ], 500);
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
        $this->logger->debug('MaintenanceMiddleware terminated');
    }

    /**
     * Load maintenance configuration
     * 
     * @return void
     */
    private function loadMaintenanceConfig(): void
    {
        // Check if maintenance file exists
        $maintenanceFile = STORAGE_PATH . '/maintenance.lock';
        
        if (file_exists($maintenanceFile)) {
            $this->maintenanceMode = true;
            
            // Read maintenance configuration
            $config = @file_get_contents($maintenanceFile);
            if ($config) {
                $data = json_decode($config, true);
                if ($data) {
                    $this->maintenanceMessage = $data['message'] ?? $this->maintenanceMessage;
                    $this->maintenanceStart = $data['start'] ?? null;
                    $this->maintenanceEnd = $data['end'] ?? null;
                    $this->whitelistedIps = $data['whitelisted_ips'] ?? [];
                    $this->whitelistedRoutes = $data['whitelisted_routes'] ?? $this->whitelistedRoutes;
                }
            }
        }

        // Also check environment variable
        if (getenv('MAINTENANCE_MODE') === 'true') {
            $this->maintenanceMode = true;
            $this->maintenanceMessage = getenv('MAINTENANCE_MESSAGE') ?: $this->maintenanceMessage;
        }
    }

    /**
     * Check if maintenance is scheduled
     * 
     * @return bool
     */
    private function isScheduledMaintenance(): bool
    {
        if (!$this->maintenanceStart) {
            return false;
        }

        return time() >= $this->maintenanceStart;
    }

    /**
     * Check if user is admin
     * 
     * @return bool
     */
    private function isAdmin(): bool
    {
        if (!$this->auth->check()) {
            return false;
        }

        return $this->authorization->isAdmin($this->auth->id());
    }

    /**
     * Disable maintenance mode
     * 
     * @return void
     */
    private function disableMaintenance(): void
    {
        $this->maintenanceMode = false;
        
        $maintenanceFile = STORAGE_PATH . '/maintenance.lock';
        if (file_exists($maintenanceFile)) {
            @unlink($maintenanceFile);
        }

        $this->logger->info('Maintenance mode disabled');
    }

    /**
     * Check if request is AJAX
     * 
     * @return bool
     */
    private function isAjaxRequest(): bool
    {
        return isset($_SERVER['HTTP_X_REQUESTED_WITH']) &&
               strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest';
    }

    /**
     * Enable maintenance mode
     * 
     * @param string $message
     * @param int|null $duration
     * @param array $whitelistedIps
     * @return void
     */
    public function enableMaintenance(string $message = '', ?int $duration = null, array $whitelistedIps = []): void
    {
        $config = [
            'message' => $message ?: $this->maintenanceMessage,
            'start' => time(),
            'end' => $duration ? time() + $duration : null,
            'whitelisted_ips' => $whitelistedIps
        ];

        $maintenanceFile = STORAGE_PATH . '/maintenance.lock';
        file_put_contents($maintenanceFile, json_encode($config, JSON_PRETTY_PRINT));

        $this->maintenanceMode = true;
        $this->maintenanceMessage = $config['message'];
        $this->maintenanceStart = $config['start'];
        $this->maintenanceEnd = $config['end'];
        $this->whitelistedIps = $config['whitelisted_ips'];

        $this->logger->info('Maintenance mode enabled', $config);
    }

    /**
     * Add whitelisted IP
     * 
     * @param string $ip
     * @return void
     */
    public function addWhitelistedIp(string $ip): void
    {
        $this->whitelistedIps[] = $ip;
    }

    /**
     * Add whitelisted route
     * 
     * @param string $route
     * @return void
     */
    public function addWhitelistedRoute(string $route): void
    {
        $this->whitelistedRoutes[] = $route;
    }
}