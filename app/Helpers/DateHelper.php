<?php
/**
 * AI Banking GRC Platform - Date Helper
 * 
 * @package    AI-Banking-GRC-Platform
 * @subpackage app/Helpers
 * @version    1.0.0
 * @author     GRC Platform Team
 * @copyright  2026 AI Banking GRC Platform
 * @license    Proprietary
 * 
 * This helper provides date and time functionality:
 * - Date formatting
 * - Time ago calculations
 * - Current date/time retrieval
 * - Timezone handling
 */

declare(strict_types=1);

namespace App\Helpers;

class DateHelper
{
    /**
     * @var string Default date format
     */
    private const DEFAULT_DATE_FORMAT = 'Y-m-d';

    /**
     * @var string Default datetime format
     */
    private const DEFAULT_DATETIME_FORMAT = 'Y-m-d H:i:s';

    /**
     * @var string Default timezone
     */
    private static string $timezone = 'Asia/Karachi';

    /**
     * Set timezone
     * 
     * @param string $timezone
     * @return void
     */
    public static function setTimezone(string $timezone): void
    {
        self::$timezone = $timezone;
        date_default_timezone_set($timezone);
    }

    /**
     * Get current timezone
     * 
     * @return string
     */
    public static function getTimezone(): string
    {
        return self::$timezone;
    }

    /**
     * Format date
     * 
     * @param string|int|null $timestamp
     * @param string $format
     * @return string
     */
    public static function formatDate($timestamp = null, string $format = self::DEFAULT_DATE_FORMAT): string
    {
        $timestamp = self::normalizeTimestamp($timestamp);
        return date($format, $timestamp);
    }

    /**
     * Format datetime
     * 
     * @param string|int|null $timestamp
     * @param string $format
     * @return string
     */
    public static function formatDateTime($timestamp = null, string $format = self::DEFAULT_DATETIME_FORMAT): string
    {
        $timestamp = self::normalizeTimestamp($timestamp);
        return date($format, $timestamp);
    }

    /**
     * Get time ago string
     * 
     * @param string|int $timestamp
     * @return string
     */
    public static function timeAgo($timestamp): string
    {
        $timestamp = self::normalizeTimestamp($timestamp);
        $now = time();
        $diff = $now - $timestamp;

        if ($diff < 60) {
            return $diff <= 1 ? 'Just now' : $diff . ' seconds ago';
        }

        $diff = floor($diff / 60);
        if ($diff < 60) {
            return $diff <= 1 ? '1 minute ago' : $diff . ' minutes ago';
        }

        $diff = floor($diff / 60);
        if ($diff < 24) {
            return $diff <= 1 ? '1 hour ago' : $diff . ' hours ago';
        }

        $diff = floor($diff / 24);
        if ($diff < 7) {
            return $diff <= 1 ? '1 day ago' : $diff . ' days ago';
        }

        $diff = floor($diff / 7);
        if ($diff < 4) {
            return $diff <= 1 ? '1 week ago' : $diff . ' weeks ago';
        }

        $diff = floor($diff / 4);
        if ($diff < 12) {
            return $diff <= 1 ? '1 month ago' : $diff . ' months ago';
        }

        $diff = floor($diff / 12);
        return $diff <= 1 ? '1 year ago' : $diff . ' years ago';
    }

    /**
     * Get current date
     * 
     * @param string $format
     * @return string
     */
    public static function currentDate(string $format = self::DEFAULT_DATE_FORMAT): string
    {
        return date($format);
    }

    /**
     * Get current timestamp
     * 
     * @return int
     */
    public static function currentTimestamp(): int
    {
        return time();
    }

    /**
     * Get current datetime
     * 
     * @param string $format
     * @return string
     */
    public static function currentDateTime(string $format = self::DEFAULT_DATETIME_FORMAT): string
    {
        return date($format);
    }

    /**
     * Normalize timestamp
     * 
     * @param string|int|null $timestamp
     * @return int
     */
    private static function normalizeTimestamp($timestamp): int
    {
        if ($timestamp === null) {
            return time();
        }

        if (is_int($timestamp)) {
            return $timestamp;
        }

        if (is_string($timestamp)) {
            $parsed = strtotime($timestamp);
            if ($parsed !== false) {
                return $parsed;
            }
        }

        return time();
    }

    /**
     * Parse date string
     * 
     * @param string $date
     * @return int|false
     */
    public static function parse(string $date)
    {
        return strtotime($date);
    }

    /**
     * Validate date string
     * 
     * @param string $date
     * @param string $format
     * @return bool
     */
    public static function validate(string $date, string $format = self::DEFAULT_DATE_FORMAT): bool
    {
        $d = \DateTime::createFromFormat($format, $date);
        return $d && $d->format($format) === $date;
    }

    /**
     * Add days to date
     * 
     * @param string|int|null $timestamp
     * @param int $days
     * @return int
     */
    public static function addDays($timestamp, int $days): int
    {
        $timestamp = self::normalizeTimestamp($timestamp);
        return strtotime("+{$days} days", $timestamp);
    }

    /**
     * Add months to date
     * 
     * @param string|int|null $timestamp
     * @param int $months
     * @return int
     */
    public static function addMonths($timestamp, int $months): int
    {
        $timestamp = self::normalizeTimestamp($timestamp);
        return strtotime("+{$months} months", $timestamp);
    }

    /**
     * Add years to date
     * 
     * @param string|int|null $timestamp
     * @param int $years
     * @return int
     */
    public static function addYears($timestamp, int $years): int
    {
        $timestamp = self::normalizeTimestamp($timestamp);
        return strtotime("+{$years} years", $timestamp);
    }

    /**
     * Get date difference in days
     * 
     * @param string|int $from
     * @param string|int $to
     * @return int
     */
    public static function daysDiff($from, $to): int
    {
        $from = self::normalizeTimestamp($from);
        $to = self::normalizeTimestamp($to);
        return (int)floor(($to - $from) / 86400);
    }

    /**
     * Check if date is today
     * 
     * @param string|int $timestamp
     * @return bool
     */
    public static function isToday($timestamp): bool
    {
        $timestamp = self::normalizeTimestamp($timestamp);
        return date('Y-m-d', $timestamp) === date('Y-m-d');
    }

    /**
     * Check if date is past
     * 
     * @param string|int $timestamp
     * @return bool
     */
    public static function isPast($timestamp): bool
    {
        $timestamp = self::normalizeTimestamp($timestamp);
        return $timestamp < time();
    }

    /**
     * Check if date is future
     * 
     * @param string|int $timestamp
     * @return bool
     */
    public static function isFuture($timestamp): bool
    {
        $timestamp = self::normalizeTimestamp($timestamp);
        return $timestamp > time();
    }

    /**
     * Get start of day
     * 
     * @param string|int|null $timestamp
     * @return int
     */
    public static function startOfDay($timestamp = null): int
    {
        $timestamp = self::normalizeTimestamp($timestamp);
        return strtotime(date('Y-m-d 00:00:00', $timestamp));
    }

    /**
     * Get end of day
     * 
     * @param string|int|null $timestamp
     * @return int
     */
    public static function endOfDay($timestamp = null): int
    {
        $timestamp = self::normalizeTimestamp($timestamp);
        return strtotime(date('Y-m-d 23:59:59', $timestamp));
    }

    /**
     * Get start of week
     * 
     * @param string|int|null $timestamp
     * @param int $weekStart
     * @return int
     */
    public static function startOfWeek($timestamp = null, int $weekStart = 1): int
    {
        $timestamp = self::normalizeTimestamp($timestamp);
        $dayOfWeek = date('N', $timestamp);
        $diff = $dayOfWeek - $weekStart;
        if ($diff < 0) {
            $diff += 7;
        }
        return strtotime("-{$diff} days", $timestamp);
    }

    /**
     * Get end of week
     * 
     * @param string|int|null $timestamp
     * @param int $weekEnd
     * @return int
     */
    public static function endOfWeek($timestamp = null, int $weekEnd = 7): int
    {
        $timestamp = self::normalizeTimestamp($timestamp);
        $startOfWeek = self::startOfWeek($timestamp);
        return strtotime("+6 days", $startOfWeek);
    }

    /**
     * Get start of month
     * 
     * @param string|int|null $timestamp
     * @return int
     */
    public static function startOfMonth($timestamp = null): int
    {
        $timestamp = self::normalizeTimestamp($timestamp);
        return strtotime(date('Y-m-01 00:00:00', $timestamp));
    }

    /**
     * Get end of month
     * 
     * @param string|int|null $timestamp
     * @return int
     */
    public static function endOfMonth($timestamp = null): int
    {
        $timestamp = self::normalizeTimestamp($timestamp);
        return strtotime(date('Y-m-t 23:59:59', $timestamp));
    }

    /**
     * Get start of year
     * 
     * @param string|int|null $timestamp
     * @return int
     */
    public static function startOfYear($timestamp = null): int
    {
        $timestamp = self::normalizeTimestamp($timestamp);
        return strtotime(date('Y-01-01 00:00:00', $timestamp));
    }

    /**
     * Get end of year
     * 
     * @param string|int|null $timestamp
     * @return int
     */
    public static function endOfYear($timestamp = null): int
    {
        $timestamp = self::normalizeTimestamp($timestamp);
        return strtotime(date('Y-12-31 23:59:59', $timestamp));
    }

    /**
     * Get human readable date range
     * 
     * @param string|int $from
     * @param string|int $to
     * @return string
     */
    public static function dateRange($from, $to): string
    {
        $fromDate = self::formatDate($from);
        $toDate = self::formatDate($to);
        
        if ($fromDate === $toDate) {
            return $fromDate;
        }
        
        return $fromDate . ' - ' . $toDate;
    }
}