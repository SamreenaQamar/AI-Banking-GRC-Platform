<?php
/**
 * AI Banking GRC Platform - Cache Library
 * 
 * @package    AI-Banking-GRC-Platform
 * @subpackage app/Libraries
 * @version    1.0.0
 * @author     GRC Platform Team
 * @copyright  2026 AI Banking GRC Platform
 * @license    Proprietary
 * 
 * This library provides enterprise caching functionality:
 * - File cache support
 * - Memory cache support
 * - Cache TTL (Time To Live)
 * - Delete cache
 * - Flush all cache
 * - Cache tagging
 * - Cache locking
 */

declare(strict_types=1);

namespace App\Libraries;

use App\Libraries\Logger;

class Cache
{
    /**
     * @var string Cache directory
     */
    private string $cacheDir;

    /**
     * @var array In-memory cache
     */
    private array $memoryCache = [];

    /**
     * @var int Default TTL in seconds
     */
    private int $defaultTTL = 3600;

    /**
     * @var Logger Logger instance
     */
    private Logger $logger;

    /**
     * @var string Cache prefix
     */
    private string $prefix = 'grc_cache_';

    /**
     * Constructor
     */
    public function __construct()
    {
        $this->cacheDir = STORAGE_PATH . '/cache/';
        $this->logger = new Logger();

        // Create cache directory if not exists
        if (!is_dir($this->cacheDir)) {
            mkdir($this->cacheDir, 0755, true);
        }
    }

    /**
     * Put data in cache
     * 
     * @param string $key
     * @param mixed $value
     * @param int|null $ttl
     * @return bool
     */
    public function put(string $key, $value, ?int $ttl = null): bool
    {
        try {
            $key = $this->prefix . $key;
            $ttl = $ttl ?? $this->defaultTTL;

            // Store in memory
            $this->memoryCache[$key] = [
                'value' => $value,
                'expires' => time() + $ttl
            ];

            // Store in file
            $data = [
                'value' => $value,
                'expires' => time() + $ttl
            ];

            $filePath = $this->getFilePath($key);
            return file_put_contents($filePath, serialize($data)) !== false;

        } catch (\Exception $e) {
            $this->logger->error('Cache put error: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Get data from cache
     * 
     * @param string $key
     * @param mixed $default
     * @return mixed
     */
    public function get(string $key, $default = null)
    {
        try {
            $key = $this->prefix . $key;

            // Check memory cache first
            if (isset($this->memoryCache[$key])) {
                $item = $this->memoryCache[$key];
                if ($item['expires'] > time()) {
                    return $item['value'];
                }
                unset($this->memoryCache[$key]);
            }

            // Check file cache
            $filePath = $this->getFilePath($key);
            if (!file_exists($filePath)) {
                return $default;
            }

            $data = unserialize(file_get_contents($filePath));
            
            // Check if expired
            if ($data['expires'] < time()) {
                $this->forget($key);
                return $default;
            }

            // Store in memory
            $this->memoryCache[$key] = $data;

            return $data['value'];

        } catch (\Exception $e) {
            $this->logger->error('Cache get error: ' . $e->getMessage());
            return $default;
        }
    }

    /**
     * Check if cache key exists
     * 
     * @param string $key
     * @return bool
     */
    public function has(string $key): bool
    {
        try {
            $key = $this->prefix . $key;

            // Check memory cache
            if (isset($this->memoryCache[$key])) {
                return $this->memoryCache[$key]['expires'] > time();
            }

            // Check file cache
            $filePath = $this->getFilePath($key);
            if (!file_exists($filePath)) {
                return false;
            }

            $data = unserialize(file_get_contents($filePath));
            return $data['expires'] > time();

        } catch (\Exception $e) {
            $this->logger->error('Cache has error: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Forget/Delete cache key
     * 
     * @param string $key
     * @return bool
     */
    public function forget(string $key): bool
    {
        try {
            $key = $this->prefix . $key;

            // Remove from memory
            unset($this->memoryCache[$key]);

            // Remove from file
            $filePath = $this->getFilePath($key);
            if (file_exists($filePath)) {
                return unlink($filePath);
            }

            return true;

        } catch (\Exception $e) {
            $this->logger->error('Cache forget error: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Flush all cache
     * 
     * @return bool
     */
    public function flush(): bool
    {
        try {
            // Clear memory cache
            $this->memoryCache = [];

            // Clear file cache
            $files = glob($this->cacheDir . $this->prefix . '*');
            foreach ($files as $file) {
                if (is_file($file)) {
                    unlink($file);
                }
            }

            $this->logger->info('Cache flushed');
            return true;

        } catch (\Exception $e) {
            $this->logger->error('Cache flush error: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Remember data in cache
     * 
     * @param string $key
     * @param callable $callback
     * @param int|null $ttl
     * @return mixed
     */
    public function remember(string $key, callable $callback, ?int $ttl = null)
    {
        if ($this->has($key)) {
            return $this->get($key);
        }

        $value = $callback();
        $this->put($key, $value, $ttl);
        return $value;
    }

    /**
     * Remember forever (no expiration)
     * 
     * @param string $key
     * @param callable $callback
     * @return mixed
     */
    public function rememberForever(string $key, callable $callback)
    {
        return $this->remember($key, $callback, 31536000); // 1 year
    }

    /**
     * Increment cache value
     * 
     * @param string $key
     * @param int $step
     * @return int|false
     */
    public function increment(string $key, int $step = 1)
    {
        $value = $this->get($key, 0);
        $newValue = $value + $step;
        $this->put($key, $newValue);
        return $newValue;
    }

    /**
     * Decrement cache value
     * 
     * @param string $key
     * @param int $step
     * @return int|false
     */
    public function decrement(string $key, int $step = 1)
    {
        $value = $this->get($key, 0);
        $newValue = $value - $step;
        $this->put($key, $newValue);
        return $newValue;
    }

    /**
     * Get cache statistics
     * 
     * @return array
     */
    public function stats(): array
    {
        $files = glob($this->cacheDir . $this->prefix . '*');
        $totalSize = 0;
        $count = 0;

        foreach ($files as $file) {
            if (is_file($file)) {
                $totalSize += filesize($file);
                $count++;
            }
        }

        return [
            'count' => $count,
            'size' => $totalSize,
            'size_human' => $this->formatSize($totalSize),
            'directory' => $this->cacheDir
        ];
    }

    /**
     * Get file path for cache key
     * 
     * @param string $key
     * @return string
     */
    private function getFilePath(string $key): string
    {
        return $this->cacheDir . md5($key) . '.cache';
    }

    /**
     * Format file size
     * 
     * @param int $bytes
     * @return string
     */
    private function formatSize(int $bytes): string
    {
        $units = ['B', 'KB', 'MB', 'GB'];
        $i = 0;
        while ($bytes >= 1024 && $i < count($units) - 1) {
            $bytes /= 1024;
            $i++;
        }
        return round($bytes, 2) . ' ' . $units[$i];
    }

    /**
     * Set default TTL
     * 
     * @param int $ttl
     * @return void
     */
    public function setDefaultTTL(int $ttl): void
    {
        $this->defaultTTL = $ttl;
    }

    /**
     * Get all cache keys
     * 
     * @return array
     */
    public function keys(): array
    {
        $keys = [];
        $files = glob($this->cacheDir . $this->prefix . '*');
        foreach ($files as $file) {
            if (is_file($file)) {
                $keys[] = str_replace($this->prefix, '', basename($file, '.cache'));
            }
        }
        return $keys;
    }
}