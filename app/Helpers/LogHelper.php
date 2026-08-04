<?php
/**
 * AI Banking GRC Platform - Log Helper
 * 
 * @package    AI-Banking-GRC-Platform
 * @subpackage app/Helpers
 * @version    1.0.0
 * @author     GRC Platform Team
 * @copyright  2026 AI Banking GRC Platform
 * @license    Proprietary
 * 
 * This helper provides logging functionality:
 * - Multi-level logging
 * - File-based logging
 * - Context data support
 * - Error logging
 */

declare(strict_types=1);

namespace App\Helpers;

class LogHelper
{
    /**
     * @var string Log directory
     */
    private static string $logDir = STORAGE_PATH . '/logs';

    /**
     * @var string Default log file
     */
    private static string $defaultFile = 'app.log';

    /**
     * Set log directory
     * 
     * @param string $dir
     * @return void
     */
    public static function setLogDir(string $dir): void
    {
        self::$logDir = $dir;
        if (!is_dir($dir)) {
            mkdir($dir, 0755, true);
        }
    }

    /**
     * Set default log file
     * 
     * @param string $file
     * @return void
     */
    public static function setDefaultFile(string $file): void
    {
        self::$defaultFile = $file;
    }

    /**
     * Write log message
     * 
     * @param string $level
     * @param string $message
     * @param array $context
     * @param string|null $file
     * @return void
     */
    private static function write(string $level, string $message, array $context = [], ?string $file = null): void
    {
        if (!self::isLoggable($level)) {
            return;
        }

        $file = $file ?? self::$defaultFile;
        $logPath = self::$logDir . '/' . $file;

        // Create directory if not exists
        if (!is_dir(self::$logDir)) {
            mkdir(self::$logDir, 0755, true);
        }

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

        // Also write to error log for critical levels
        if (in_array($level, ['error', 'critical', 'alert', 'emergency'])) {
            error_log($logEntry);
        }
    }

    /**
     * Check if level is loggable
     * 
     * @param string $level
     * @return bool
     */
    private static function isLoggable(string $level): bool
    {
        if (!defined('LOG_LEVEL')) {
            return true;
        }

        $levels = [
            'debug' => 100,
            'info' => 200,
            'notice' => 250,
            'warning' => 300,
            'error' => 400,
            'critical' => 500,
            'alert' => 550,
            'emergency' => 600
        ];

        $currentLevel = $levels[LOG_LEVEL] ?? 100;
        $messageLevel = $levels[$level] ?? 100;

        return $messageLevel >= $currentLevel;
    }

    /**
     * Log debug message
     * 
     * @param string $message
     * @param array $context
     * @param string|null $file
     * @return void
     */
    public static function debug(string $message, array $context = [], ?string $file = null): void
    {
        self::write('debug', $message, $context, $file);
    }

    /**
     * Log info message
     * 
     * @param string $message
     * @param array $context
     * @param string|null $file
     * @return void
     */
    public static function info(string $message, array $context = [], ?string $file = null): void
    {
        self::write('info', $message, $context, $file);
    }

    /**
     * Log notice message
     * 
     * @param string $message
     * @param array $context
     * @param string|null $file
     * @return void
     */
    public static function notice(string $message, array $context = [], ?string $file = null): void
    {
        self::write('notice', $message, $context, $file);
    }

    /**
     * Log warning message
     * 
     * @param string $message
     * @param array $context
     * @param string|null $file
     * @return void
     */
    public static function warning(string $message, array $context = [], ?string $file = null): void
    {
        self::write('warning', $message, $context, $file);
    }

    /**
     * Log error message
     * 
     * @param string $message
     * @param array $context
     * @param string|null $file
     * @return void
     */
    public static function error(string $message, array $context = [], ?string $file = null): void
    {
        self::write('error', $message, $context, $file);
    }

    /**
     * Log critical message
     * 
     * @param string $message
     * @param array $context
     * @param string|null $file
     * @return void
     */
    public static function critical(string $message, array $context = [], ?string $file = null): void
    {
        self::write('critical', $message, $context, $file);
    }

    /**
     * Log alert message
     * 
     * @param string $message
     * @param array $context
     * @param string|null $file
     * @return void
     */
    public static function alert(string $message, array $context = [], ?string $file = null): void
    {
        self::write('alert', $message, $context, $file);
    }

    /**
     * Log emergency message
     * 
     * @param string $message
     * @param array $context
     * @param string|null $file
     * @return void
     */
    public static function emergency(string $message, array $context = [], ?string $file = null): void
    {
        self::write('emergency', $message, $context, $file);
    }

    /**
     * Log with exception
     * 
     * @param \Throwable $exception
     * @param string $level
     * @param string|null $file
     * @return void
     */
    public static function exception(\Throwable $exception, string $level = 'error', ?string $file = null): void
    {
        $message = sprintf(
            "%s in %s:%d\nStack trace:\n%s",
            $exception->getMessage(),
            $exception->getFile(),
            $exception->getLine(),
            $exception->getTraceAsString()
        );
        
        self::write($level, $message, [], $file);
    }

    /**
     * Log security event
     * 
     * @param string $message
     * @param array $context
     * @return void
     */
    public static function security(string $message, array $context = []): void
    {
        self::write('info', '[SECURITY] ' . $message, $context, 'security.log');
    }

    /**
     * Log audit event
     * 
     * @param string $message
     * @param array $context
     * @return void
     */
    public static function audit(string $message, array $context = []): void
    {
        self::write('info', '[AUDIT] ' . $message, $context, 'audit.log');
    }

    /**
     * Log access event
     * 
     * @param string $message
     * @param array $context
     * @return void
     */
    public static function access(string $message, array $context = []): void
    {
        self::write('info', '[ACCESS] ' . $message, $context, 'access.log');
    }

    /**
     * Log performance data
     * 
     * @param string $message
     * @param array $context
     * @return void
     */
    public static function performance(string $message, array $context = []): void
    {
        self::write('info', '[PERFORMANCE] ' . $message, $context, 'performance.log');
    }

    /**
     * Get log file content
     * 
     * @param string $file
     * @param int $lines
     * @return string
     */
    public static function getLogs(string $file, int $lines = 100): string
    {
        $logPath = self::$logDir . '/' . $file;
        
        if (!file_exists($logPath)) {
            return '';
        }

        $handle = fopen($logPath, 'r');
        $buffer = '';
        
        // Get last N lines
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
    public static function clearLogs(string $file): bool
    {
        $logPath = self::$logDir . '/' . $file;
        
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
    public static function rotateLogs(string $file, int $maxSize = 10485760): bool
    {
        $logPath = self::$logDir . '/' . $file;
        
        if (!file_exists($logPath) || filesize($logPath) < $maxSize) {
            return true;
        }

        $timestamp = date('Y-m-d_H-i-s');
        $archivePath = self::$logDir . '/' . $file . '.' . $timestamp . '.bak';
        
        return rename($logPath, $archivePath) && touch($logPath);
    }

    /**
     * Get log file size
     * 
     * @param string $file
     * @return int
     */
    public static function getLogSize(string $file): int
    {
        $logPath = self::$logDir . '/' . $file;
        
        if (!file_exists($logPath)) {
            return 0;
        }

        return filesize($logPath);
    }

    /**
     * Check if log file exists
     * 
     * @param string $file
     * @return bool
     */
    public static function logExists(string $file): bool
    {
        return file_exists(self::$logDir . '/' . $file);
    }

    /**
     * Get all log files
     * 
     * @return array
     */
    public static function getLogFiles(): array
    {
        if (!is_dir(self::$logDir)) {
            return [];
        }

        $files = scandir(self::$logDir);
        return array_filter($files, function($file) {
            return $file !== '.' && $file !== '..' && pathinfo($file, PATHINFO_EXTENSION) === 'log';
        });
    }

    /**
     * Get log file path
     * 
     * @param string $file
     * @return string
     */
    public static function getLogPath(string $file): string
    {
        return self::$logDir . '/' . $file;
    }
}