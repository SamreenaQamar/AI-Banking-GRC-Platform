<?php
/**
 * AI Banking GRC Platform - Logger Library
 * 
 * @package    AI-Banking-GRC-Platform
 * @subpackage app/Libraries
 * @version    1.0.0
 * @author     GRC Platform Team
 * @copyright  2026 AI Banking GRC Platform
 * @license    Proprietary
 * 
 * This library provides enterprise logging functionality:
 * - Error logs
 * - Audit logs
 * - Security logs
 * - Authentication logs
 * - Database logs
 * - Daily log rotation
 * - Log levels
 */

declare(strict_types=1);

namespace App\Libraries;

class Logger
{
    /**
     * @var string Log directory
     */
    private string $logDir;

    /**
     * @var array Log levels
     */
    private array $levels = [
        'debug' => 100,
        'info' => 200,
        'notice' => 250,
        'warning' => 300,
        'error' => 400,
        'critical' => 500,
        'alert' => 550,
        'emergency' => 600
    ];

    /**
     * @var int Current log level
     */
    private int $currentLevel = 100;

    /**
     * @var bool Whether logging is enabled
     */
    private bool $enabled = true;

    /**
     * Constructor
     */
    public function __construct()
    {
        $this->logDir = STORAGE_PATH . '/logs/';
        $this->currentLevel = $this->levels[LOG_LEVEL] ?? 100;
        $this->enabled = LOG_ENABLED;

        // Create log directory if not exists
        if (!is_dir($this->logDir)) {
            mkdir($this->logDir, 0755, true);
        }
    }

    /**
     * Log debug message
     * 
     * @param string $message
     * @param array $context
     * @return void
     */
    public function debug(string $message, array $context = []): void
    {
        $this->log('debug', $message, $context);
    }

    /**
     * Log info message
     * 
     * @param string $message
     * @param array $context
     * @return void
     */
    public function info(string $message, array $context = []): void
    {
        $this->log('info', $message, $context);
    }

    /**
     * Log notice message
     * 
     * @param string $message
     * @param array $context
     * @return void
     */
    public function notice(string $message, array $context = []): void
    {
        $this->log('notice', $message, $context);
    }

    /**
     * Log warning message
     * 
     * @param string $message
     * @param array $context
     * @return void
     */
    public function warning(string $message, array $context = []): void
    {
        $this->log('warning', $message, $context);
    }

    /**
     * Log error message
     * 
     * @param string $message
     * @param array $context
     * @return void
     */
    public function error(string $message, array $context = []): void
    {
        $this->log('error', $message, $context);
    }

    /**
     * Log critical message
     * 
     * @param string $message
     * @param array $context
     * @return void
     */
    public function critical(string $message, array $context = []): void
    {
        $this->log('critical', $message, $context);
    }

    /**
     * Log alert message
     * 
     * @param string $message
     * @param array $context
     * @return void
     */
    public function alert(string $message, array $context = []): void
    {
        $this->log('alert', $message, $context);
    }

    /**
     * Log emergency message
     * 
     * @param string $message
     * @param array $context
     * @return void
     */
    public function emergency(string $message, array $context = []): void
    {
        $this->log('emergency', $message, $context);
    }

    /**
     * Log audit event
     * 
     * @param string $message
     * @param array $context
     * @return void
     */
    public function audit(string $message, array $context = []): void
    {
        $this->log('info', '[AUDIT] ' . $message, $context, 'audit.log');
    }

    /**
     * Log security event
     * 
     * @param string $message
     * @param array $context
     * @return void
     */
    public function security(string $message, array $context = []): void
    {
        $this->log('info', '[SECURITY] ' . $message, $context, 'security.log');
    }

    /**
     * Log authentication event
     * 
     * @param string $message
     * @param array $context
     * @return void
     */
    public function auth(string $message, array $context = []): void
    {
        $this->log('info', '[AUTH] ' . $message, $context, 'auth.log');
    }

    /**
     * Log database event
     * 
     * @param string $message
     * @param array $context
     * @return void
     */
    public function database(string $message, array $context = []): void
    {
        $this->log('info', '[DATABASE] ' . $message, $context, 'database.log');
    }

    /**
     * Log message
     * 
     * @param string $level
     * @param string $message
     * @param array $context
     * @param string|null $file
     * @return void
     */
    private function log(string $level, string $message, array $context = [], ?string $file = null): void
    {
        if (!$this->enabled) {
            return;
        }

        $levelValue = $this->levels[$level] ?? 100;
        if ($levelValue < $this->currentLevel) {
            return;
        }

        $file = $file ?? 'app.log';
        $logPath = $this->logDir . $file;

        // Format log entry
        $timestamp = date('Y-m-d H:i:s');
        $contextStr = empty($context) ? '' : ' ' . json_encode($context, JSON_UNESCAPED_UNICODE);
        $logEntry = sprintf(
            "[%s] %s: %s%s\n",
            $timestamp,
            strtoupper($level),
            $message,
            $contextStr
        );

        // Write to file
        file_put_contents($logPath, $logEntry, FILE_APPEND | LOCK_EX);

        // Also log to error_log for critical levels
        if (in_array($level, ['error', 'critical', 'alert', 'emergency'])) {
            error_log($logEntry);
        }
    }

    /**
     * Log exception
     * 
     * @param \Throwable $exception
     * @param string $level
     * @return void
     */
    public function exception(\Throwable $exception, string $level = 'error'): void
    {
        $message = sprintf(
            "%s in %s:%d\nStack trace:\n%s",
            $exception->getMessage(),
            $exception->getFile(),
            $exception->getLine(),
            $exception->getTraceAsString()
        );

        $this->log($level, $message, [], 'exceptions.log');
    }

    /**
     * Get log file content
     * 
     * @param string $file
     * @param int $lines
     * @return string
     */
    public function getLogs(string $file = 'app.log', int $lines = 100): string
    {
        $logPath = $this->logDir . $file;

        if (!file_exists($logPath)) {
            return '';
        }

        $handle = fopen($logPath, 'r');
        $buffer = '';
        $lineCount = 0;
        $line = '';
        fseek($handle, -1, SEEK_END);

        while ($lineCount < $lines && ftell($handle) > 0) {
            $char = fgetc($handle);
            if ($char === "\n") {
                $lineCount++;
                if ($lineCount < $lines) {
                    $buffer = $line . "\n" . $buffer;
                }
                $line = '';
            } else {
                $line = $char . $line;
            }
            fseek($handle, -2, SEEK_CUR);
        }

        if ($lineCount < $lines && !empty($line)) {
            $buffer = $line . "\n" . $buffer;
        }

        fclose($handle);
        return $buffer;
    }

    /**
     * Clear log file
     * 
     * @param string $file
     * @return bool
     */
    public function clearLogs(string $file = 'app.log'): bool
    {
        $logPath = $this->logDir . $file;
        if (!file_exists($logPath)) {
            return true;
        }
        return file_put_contents($logPath, '') !== false;
    }

    /**
     * Rotate log file
     * 
     * @param string $file
     * @param int $maxSize
     * @return bool
     */
    public function rotateLogs(string $file = 'app.log', int $maxSize = 10485760): bool
    {
        $logPath = $this->logDir . $file;

        if (!file_exists($logPath) || filesize($logPath) < $maxSize) {
            return true;
        }

        $timestamp = date('Y-m-d_H-i-s');
        $archivePath = $this->logDir . $file . '.' . $timestamp . '.bak';

        return rename($logPath, $archivePath) && touch($logPath);
    }

    /**
     * Get log files
     * 
     * @return array
     */
    public function getLogFiles(): array
    {
        if (!is_dir($this->logDir)) {
            return [];
        }

        $files = scandir($this->logDir);
        return array_filter($files, function($file) {
            return $file !== '.' && $file !== '..' && pathinfo($file, PATHINFO_EXTENSION) === 'log';
        });
    }

    /**
     * Set log level
     * 
     * @param string $level
     * @return void
     */
    public function setLevel(string $level): void
    {
        $this->currentLevel = $this->levels[$level] ?? 100;
    }

    /**
     * Enable logging
     * 
     * @return void
     */
    public function enable(): void
    {
        $this->enabled = true;
    }

    /**
     * Disable logging
     * 
     * @return void
     */
    public function disable(): void
    {
        $this->enabled = false;
    }

    /**
     * Check if logging is enabled
     * 
     * @return bool
     */
    public function isEnabled(): bool
    {
        return $this->enabled;
    }
}