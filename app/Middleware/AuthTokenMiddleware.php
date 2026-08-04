<?php
/**
 * AI Banking GRC Platform - Auth Token Middleware
 * 
 * @package    AI-Banking-GRC-Platform
 * @subpackage app/Middleware
 * @version    1.0.0
 * @author     GRC Platform Team
 * @copyright  2026 AI Banking GRC Platform
 * @license    Proprietary
 * 
 * This middleware handles token-based authentication:
 * - JWT validation
 * - API token validation
 * - Bearer token support
 * - Token expiry checking
 * - Refresh token support
 */

declare(strict_types=1);

namespace App\Middleware;

use App\Libraries\JWTManager;
use App\Libraries\TokenManager;
use App\Libraries\Logger;
use App\Libraries\Response;
use App\Models\User;

class AuthTokenMiddleware
{
    /**
     * @var JWTManager JWT manager instance
     */
    private JWTManager $jwt;

    /**
     * @var TokenManager Token manager instance
     */
    private TokenManager $tokenManager;

    /**
     * @var Logger Logger instance
     */
    private Logger $logger;

    /**
     * @var User User model
     */
    private User $userModel;

    /**
     * @var string Token type
     */
    private string $tokenType = 'bearer';

    /**
     * Constructor
     */
    public function __construct()
    {
        $this->jwt = new JWTManager();
        $this->tokenManager = new TokenManager();
        $this->logger = new Logger();
        $this->userModel = new User();
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
            // Get token from request
            $token = $this->getTokenFromRequest();

            if (!$token) {
                $this->logger->warning('Token authentication failed - no token provided', [
                    'ip' => $_SERVER['REMOTE_ADDR'] ?? '0.0.0.0',
                    'uri' => $_SERVER['REQUEST_URI'] ?? '/'
                ]);

                return Response::error('Authentication token required.', [], 401);
            }

            // Validate token
            $tokenType = $params['type'] ?? $this->tokenType;

            if ($tokenType === 'jwt') {
                $result = $this->validateJWT($token);
            } else {
                $result = $this->validateToken($token);
            }

            if (!$result['valid']) {
                $this->logger->warning('Token validation failed', [
                    'error' => $result['error'] ?? 'Unknown error',
                    'ip' => $_SERVER['REMOTE_ADDR'] ?? '0.0.0.0'
                ]);

                return Response::error($result['error'] ?? 'Invalid token.', [], 401);
            }

            // Load user from token
            $user = $this->loadUser($result['user_id']);

            if (!$user) {
                $this->logger->warning('Token user not found', [
                    'user_id' => $result['user_id'],
                    'ip' => $_SERVER['REMOTE_ADDR'] ?? '0.0.0.0'
                ]);

                return Response::error('User not found.', [], 401);
            }

            // Check if token needs refresh
            if (isset($result['needs_refresh']) && $result['needs_refresh']) {
                // Generate new token
                $newToken = $this->generateNewToken($user);
                $_SERVER['HTTP_X_NEW_TOKEN'] = $newToken;
            }

            // Store user in request
            $_SERVER['auth_user'] = $user;
            $_SERVER['auth_user_id'] = $user->id;

            $this->logger->info('Token authentication successful', [
                'user_id' => $user->id,
                'username' => $user->username,
                'token_type' => $tokenType
            ]);

            return null;

        } catch (\Exception $e) {
            $this->logger->error('AuthTokenMiddleware error: ' . $e->getMessage(), [
                'trace' => $e->getTraceAsString()
            ]);

            return Response::error('Token authentication error occurred.', [], 500);
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
        // Check if new token should be sent
        if (isset($_SERVER['HTTP_X_NEW_TOKEN'])) {
            header('X-New-Token: ' . $_SERVER['HTTP_X_NEW_TOKEN']);
        }

        $this->logger->debug('AuthTokenMiddleware terminated');
    }

    /**
     * Get token from request
     * 
     * @return string|null
     */
    private function getTokenFromRequest(): ?string
    {
        // Check Authorization header
        $headers = getallheaders();
        $authHeader = $headers['Authorization'] ?? $headers['authorization'] ?? '';

        if (!empty($authHeader)) {
            // Bearer token
            if (strpos($authHeader, 'Bearer ') === 0) {
                return substr($authHeader, 7);
            }

            // Basic token
            if (strpos($authHeader, 'Basic ') === 0) {
                return substr($authHeader, 6);
            }

            return $authHeader;
        }

        // Check query parameter
        if (isset($_GET['api_token'])) {
            return $_GET['api_token'];
        }

        // Check cookie
        if (isset($_COOKIE['api_token'])) {
            return $_COOKIE['api_token'];
        }

        return null;
    }

    /**
     * Validate JWT token
     * 
     * @param string $token
     * @return array
     */
    private function validateJWT(string $token): array
    {
        try {
            $decoded = $this->jwt->validate($token);

            if (!$decoded) {
                return ['valid' => false, 'error' => 'Invalid JWT token'];
            }

            // Check if token is expired
            if (isset($decoded['exp']) && $decoded['exp'] < time()) {
                return ['valid' => false, 'error' => 'Token has expired'];
            }

            // Check if token needs refresh
            $needsRefresh = isset($decoded['exp']) && ($decoded['exp'] - time()) < 300; // 5 minutes

            return [
                'valid' => true,
                'user_id' => $decoded['user_id'] ?? null,
                'needs_refresh' => $needsRefresh,
                'data' => $decoded
            ];

        } catch (\Exception $e) {
            return ['valid' => false, 'error' => $e->getMessage()];
        }
    }

    /**
     * Validate API token
     * 
     * @param string $token
     * @return array
     */
    private function validateToken(string $token): array
    {
        try {
            // Find user by API token
            $user = $this->userModel->findByApiToken($token);

            if (!$user) {
                return ['valid' => false, 'error' => 'Invalid API token'];
            }

            // Check if token is expired
            $tokenData = $this->tokenManager->getTokenData($token);
            if ($tokenData && isset($tokenData['expires'])) {
                if ($tokenData['expires'] < time()) {
                    return ['valid' => false, 'error' => 'Token has expired'];
                }
            }

            return [
                'valid' => true,
                'user_id' => $user->id,
                'needs_refresh' => false
            ];

        } catch (\Exception $e) {
            return ['valid' => false, 'error' => $e->getMessage()];
        }
    }

    /**
     * Load user by ID
     * 
     * @param int $userId
     * @return object|null
     */
    private function loadUser(int $userId): ?object
    {
        return $this->userModel->find($userId);
    }

    /**
     * Generate new token
     * 
     * @param object $user
     * @return string
     */
    private function generateNewToken(object $user): string
    {
        $payload = [
            'user_id' => $user->id,
            'username' => $user->username,
            'email' => $user->email,
            'role' => $user->role_id,
            'exp' => time() + 3600 // 1 hour
        ];

        return $this->jwt->generate($payload);
    }
}