<?php
/**
 * AI Banking GRC Platform - Rate Limiter Library
 * 
 * @package    AI-Banking-GRC-Platform
 * @subpackage app/Libraries
 * @version    1.0.0
 * @author     GRC Platform Team
 * @copyright  2026 AI Banking GRC Platform
 * @license    Proprietary
 * 
 * This library provides rate limiting functionality:
 * - Login attempts
 * - API requests
 * - OTP requests
 * - Brute force protection
 * - IP-based limiting
 * - User-based limiting
 */

declare(strict_types=1);

namespace App\Libraries;

use App\Libraries\Cache;
use App\Libraries\Logger;

class RateLimiter
{
    /**
     * @var Cache Cache instance
     */
    private Cache $cache;

    /**
     * @var Logger Logger instance
     */
    private Logger $logger;

    /**
     * @var int Default limit
     */
    private int $defaultLimit = 60;

    /**
     * @var int Default window in seconds
     */
    private int $defaultWindow = 60;

    /**
     * Constructor
     */
    public function __construct()
    {
        $this->cache = new Cache();
        $this->logger = new Logger();
    }

    /**
     * Check if request is rate limited
     * 
     * @param string $key
     * @param int $limit
     * @param int $window
     * @return bool
     */
    public function isLimited(string $key, int $limit = 60, int $window = 60): bool
    {
        $cacheKey = 'rate_limit_' . $key;
        $count = $this->cache->get($cacheKey, 0);

        if ($count >= $limit) {
            $this->logger->warning('Rate limit exceeded', [
                'key' => $key,
                'limit' => $limit,
                'window' => $window
            ]);
            return true;
        }

        return false;
    }

    /**
     * Increment rate limit counter
     * 
     * @param string $key
     * @param int $window
     * @return int
     */
    public function increment(string $key, int $window = 60): int
    {
        $cacheKey = 'rate_limit_' . $key;
        $count = $this->cache->get($cacheKey, 0);
        $count++;
        $this->cache->put($cacheKey, $count, $window);
        return $count;
    }

    /**
     * Reset rate limit counter
     * 
     * @param string $key
     * @return void
     */
    public function reset(string $key): void
    {
        $cacheKey = 'rate_limit_' . $key;
        $this->cache->forget($cacheKey);
    }

    /**
     * Get remaining attempts
     * 
     * @param string $key
     * @param int $limit
     * @return int
     */
    public function remaining(string $key, int $limit = 60): int
    {
        $cacheKey = 'rate_limit_' . $key;
        $count = $this->cache->get($cacheKey, 0);
        return max(0, $limit - $count);
    }

    /**
     * Get reset time
     * 
     * @param string $key
     * @return int
     */
    public function resetTime(string $key): int
    {
        $cacheKey = 'rate_limit_' . $key;
        $ttl = $this->cache->getTtl($cacheKey);
        return $ttl ? time() + $ttl : 0;
    }

    /**
     * Check login attempts
     * 
     * @param string $username
     * @param string $ip
     * @param int $limit
     * @param int $window
     * @return bool
     */
    public function checkLogin(string $username, string $ip, int $limit = 5, int $window = 300): bool
    {
        $key = 'login_' . md5($username . $ip);
        return $this->isLimited($key, $limit, $window);
    }

    /**
     * Increment login attempts
     * 
     * @param string $username
     * @param string $ip
     * @param int $window
     * @return int
     */
    public function incrementLogin(string $username, string $ip, int $window = 300): int
    {
        $key = 'login_' . md5($username . $ip);
        return $this->increment($key, $window);
    }

    /**
     * Reset login attempts
     * 
     * @param string $username
     * @param string $ip
     * @return void
     */
    public function resetLogin(string $username, string $ip): void
    {
        $key = 'login_' . md5($username . $ip);
        $this->reset($key);
    }

    /**
     * Check API rate limit
     * 
     * @param string $apiKey
     * @param string $ip
     * @param int $limit
     * @param int $window
     * @return bool
     */
    public function checkApi(string $apiKey, string $ip, int $limit = 100, int $window = 60): bool
    {
        $key = 'api_' . md5($apiKey . $ip);
        return $this->isLimited($key, $limit, $window);
    }

    /**
     * Increment API count
     * 
     * @param string $apiKey
     * @param string $ip
     * @param int $window
     * @return int
     */
    public function incrementApi(string $apiKey, string $ip, int $window = 60): int
    {
        $key = 'api_' . md5($apiKey . $ip);
        return $this->increment($key, $window);
    }

    /**
     * Check OTP rate limit
     * 
     * @param string $identifier
     * @param string $ip
     * @param int $limit
     * @param int $window
     * @return bool
     */
    public function checkOTP(string $identifier, string $ip, int $limit = 3, int $window = 300): bool
    {
        $key = 'otp_' . md5($identifier . $ip);
        return $this->isLimited($key, $limit, $window);
    }

    /**
     * Increment OTP count
     * 
     * @param string $identifier
     * @param string $ip
     * @param int $window
     * @return int
     */
    public function incrementOTP(string $identifier, string $ip, int $window = 300): int
    {
        $key = 'otp_' . md5($identifier . $ip);
        return $this->increment($key, $window);
    }

    /**
     * Reset OTP attempts
     * 
     * @param string $identifier
     * @param string $ip
     * @return void
     */
    public function resetOTP(string $identifier, string $ip): void
    {
        $key = 'otp_' . md5($identifier . $ip);
        $this->reset($key);
    }

    /**
     * Get IP-based key
     * 
     * @param string $prefix
     * @return string
     */
    private function getIpKey(string $prefix): string
    {
        $ip = $_SERVER['REMOTE_ADDR'] ?? '0.0.0.0';
        return $prefix . '_' . md5($ip);
    }

    /**
     * Check IP-based limit
     * 
     * @param string $prefix
     * @param int $limit
     * @param int $window
     * @return bool
     */
    public function checkIp(string $prefix, int $limit = 60, int $window = 60): bool
    {
        $key = $this->getIpKey($prefix);
        return $this->isLimited($key, $limit, $window);
    }

    /**
     * Increment IP-based counter
     * 
     * @param string $prefix
     * @param int $window
     * @return int
     */
    public function incrementIp(string $prefix, int $window = 60): int
    {
        $key = $this->getIpKey($prefix);
        return $this->increment($key, $window);
    }

    /**
     * Reset IP-based counter
     * 
     * @param string $prefix
     * @return void
     */
    public function resetIp(string $prefix): void
    {
        $key = $this->getIpKey($prefix);
        $this->reset($key);
    }

    /**
     * Clear all rate limits
     * 
     * @return void
     */
    public function clearAll(): void
    {
        $this->cache->flush();
        $this->logger->info('All rate limits cleared');
    }

    /**
     * Get rate limit status
     * 
     * @param string $key
     * @param int $limit
     * @return array
     */
    public function status(string $key, int $limit = 60): array
    {
        $cacheKey = 'rate_limit_' . $key;
        $count = $this->cache->get($cacheKey, 0);
        $remaining = max(0, $limit - $count);
        $resetTime = $this->resetTime($key);

        return [
            'limit' => $limit,
            'current' => $count,
            'remaining' => $remaining,
            'reset' => $resetTime,
            'reset_in' => $resetTime ? max(0, $resetTime - time()) : 0
        ];
    }
}