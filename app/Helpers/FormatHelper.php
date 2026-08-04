<?php
/**
 * AI Banking GRC Platform - Format Helper
 * 
 * @package    AI-Banking-GRC-Platform
 * @subpackage app/Helpers
 * @version    1.0.0
 * @author     GRC Platform Team
 * @copyright  2026 AI Banking GRC Platform
 * @license    Proprietary
 * 
 * This helper provides data formatting functionality:
 * - Currency formatting
 * - Percentage formatting
 * - Number formatting
 * - Status badges
 * - File size formatting
 */

declare(strict_types=1);

namespace App\Helpers;

class FormatHelper
{
    /**
     * Format currency
     * 
     * @param float $amount
     * @param string $currency
     * @param int $decimals
     * @return string
     */
    public static function currency(float $amount, string $currency = 'PKR', int $decimals = 2): string
    {
        $formatter = new \NumberFormatter('en_PK', \NumberFormatter::CURRENCY);
        $formatter->setAttribute(\NumberFormatter::FRACTION_DIGITS, $decimals);
        
        return $formatter->formatCurrency($amount, $currency);
    }

    /**
     * Format currency short (PKR)
     * 
     * @param float $amount
     * @return string
     */
    public static function currencyShort(float $amount): string
    {
        $symbol = '₨';
        
        if ($amount >= 10000000) {
            return $symbol . number_format($amount / 10000000, 2) . ' Cr';
        }
        
        if ($amount >= 100000) {
            return $symbol . number_format($amount / 100000, 2) . ' Lac';
        }
        
        if ($amount >= 1000) {
            return $symbol . number_format($amount / 1000, 2) . ' K';
        }
        
        return $symbol . number_format($amount, 0);
    }

    /**
     * Format percentage
     * 
     * @param float $value
     * @param int $decimals
     * @return string
     */
    public static function percentage(float $value, int $decimals = 1): string
    {
        return number_format($value, $decimals) . '%';
    }

    /**
     * Format number
     * 
     * @param float $number
     * @param int $decimals
     * @param string $decimalSeparator
     * @param string $thousandSeparator
     * @return string
     */
    public static function number(
        float $number,
        int $decimals = 0,
        string $decimalSeparator = '.',
        string $thousandSeparator = ','
    ): string {
        return number_format($number, $decimals, $decimalSeparator, $thousandSeparator);
    }

    /**
     * Format file size
     * 
     * @param int $bytes
     * @param int $decimals
     * @return string
     */
    public static function fileSize(int $bytes, int $decimals = 2): string
    {
        $units = ['B', 'KB', 'MB', 'GB', 'TB', 'PB'];
        $factor = floor((strlen($bytes) - 1) / 3);
        
        if ($factor === 0) {
            return $bytes . ' B';
        }
        
        return self::number($bytes / pow(1024, $factor), $decimals) . ' ' . $units[$factor];
    }

    /**
     * Format phone number
     * 
     * @param string $phone
     * @return string
     */
    public static function phone(string $phone): string
    {
        // Remove non-numeric characters
        $phone = preg_replace('/[^0-9]/', '', $phone);
        
        if (strlen($phone) === 11 && substr($phone, 0, 1) === '0') {
            return '+92-' . substr($phone, 1, 4) . '-' . substr($phone, 5);
        }
        
        if (strlen($phone) === 12 && substr($phone, 0, 2) === '92') {
            return '+' . substr($phone, 0, 2) . '-' . substr($phone, 2, 4) . '-' . substr($phone, 6);
        }
        
        return $phone;
    }

    /**
     * Format status badge
     * 
     * @param string $status
     * @param string $type
     * @return string
     */
    public static function statusBadge(string $status, string $type = 'default'): string
    {
        $colors = [
            'active' => 'success',
            'inactive' => 'secondary',
            'pending' => 'warning',
            'suspended' => 'danger',
            'locked' => 'danger',
            'completed' => 'success',
            'in_progress' => 'info',
            'overdue' => 'danger',
            'cancelled' => 'secondary',
            'approved' => 'success',
            'rejected' => 'danger',
            'review' => 'warning',
            'draft' => 'secondary',
            'archived' => 'secondary',
            'expired' => 'danger',
            'high' => 'danger',
            'medium' => 'warning',
            'low' => 'info',
            'critical' => 'danger',
        ];

        $color = $colors[$status] ?? 'secondary';
        
        return sprintf(
            '<span class="badge bg-%s">%s</span>',
            $color,
            ucfirst(str_replace('_', ' ', $status))
        );
    }

    /**
     * Format status badge with icon
     * 
     * @param string $status
     * @param string $type
     * @return string
     */
    public static function statusBadgeWithIcon(string $status, string $type = 'default'): string
    {
        $icons = [
            'active' => 'fa-check-circle',
            'inactive' => 'fa-times-circle',
            'pending' => 'fa-clock',
            'suspended' => 'fa-ban',
            'locked' => 'fa-lock',
            'completed' => 'fa-check-circle',
            'in_progress' => 'fa-spinner',
            'overdue' => 'fa-exclamation-circle',
            'cancelled' => 'fa-times-circle',
            'approved' => 'fa-check-circle',
            'rejected' => 'fa-times-circle',
            'review' => 'fa-eye',
            'draft' => 'fa-file',
            'archived' => 'fa-archive',
            'expired' => 'fa-clock',
            'high' => 'fa-arrow-up',
            'medium' => 'fa-minus',
            'low' => 'fa-arrow-down',
            'critical' => 'fa-exclamation-triangle',
        ];

        $icon = $icons[$status] ?? 'fa-circle';
        $badge = self::statusBadge($status, $type);
        
        return str_replace('>', '><i class="fas ' . $icon . ' me-1"></i> ', $badge);
    }

    /**
     * Format risk level
     * 
     * @param string $level
     * @return string
     */
    public static function riskLevel(string $level): string
    {
        $colors = [
            'critical' => 'danger',
            'high' => 'danger',
            'medium' => 'warning',
            'low' => 'info',
            'very_low' => 'success'
        ];

        $color = $colors[$level] ?? 'secondary';
        
        return sprintf(
            '<span class="badge bg-%s">%s</span>',
            $color,
            ucfirst(str_replace('_', ' ', $level))
        );
    }

    /**
     * Format priority
     * 
     * @param string $priority
     * @return string
     */
    public static function priority(string $priority): string
    {
        $colors = [
            'critical' => 'danger',
            'high' => 'warning',
            'medium' => 'info',
            'low' => 'secondary'
        ];

        $color = $colors[$priority] ?? 'secondary';
        
        return sprintf(
            '<span class="badge bg-%s">%s</span>',
            $color,
            ucfirst($priority)
        );
    }

    /**
     * Format JSON for display
     * 
     * @param array $data
     * @param bool $pretty
     * @return string
     */
    public static function json(array $data, bool $pretty = true): string
    {
        $flags = JSON_UNESCAPED_UNICODE;
        if ($pretty) {
            $flags |= JSON_PRETTY_PRINT;
        }
        
        return json_encode($data, $flags);
    }

    /**
     * Format time duration
     * 
     * @param int $seconds
     * @return string
     */
    public static function duration(int $seconds): string
    {
        $hours = floor($seconds / 3600);
        $minutes = floor(($seconds % 3600) / 60);
        $secs = $seconds % 60;

        $parts = [];
        if ($hours > 0) {
            $parts[] = $hours . 'h';
        }
        if ($minutes > 0) {
            $parts[] = $minutes . 'm';
        }
        if ($secs > 0 || empty($parts)) {
            $parts[] = $secs . 's';
        }

        return implode(' ', $parts);
    }

    /**
     * Format text with ellipsis
     * 
     * @param string $text
     * @param int $length
     * @param string $suffix
     * @return string
     */
    public static function truncate(string $text, int $length = 100, string $suffix = '...'): string
    {
        if (mb_strlen($text) <= $length) {
            return $text;
        }

        return mb_substr($text, 0, $length) . $suffix;
    }

    /**
     * Format email for display (hide part)
     * 
     * @param string $email
     * @return string
     */
    public static function emailMask(string $email): string
    {
        list($user, $domain) = explode('@', $email);
        
        $userLength = strlen($user);
        if ($userLength <= 2) {
            return $email;
        }
        
        $hidden = substr($user, 0, 2) . str_repeat('*', $userLength - 2) . '@' . $domain;
        return $hidden;
    }

    /**
     * Format phone number for display (hide part)
     * 
     * @param string $phone
     * @return string
     */
    public static function phoneMask(string $phone): string
    {
        $cleaned = preg_replace('/[^0-9]/', '', $phone);
        $length = strlen($cleaned);
        
        if ($length <= 4) {
            return $phone;
        }
        
        $visible = substr($cleaned, -4);
        return str_repeat('*', $length - 4) . $visible;
    }

    /**
     * Format array as HTML list
     * 
     * @param array $items
     * @param string $type
     * @param string $class
     * @return string
     */
    public static function arrayList(array $items, string $type = 'ul', string $class = ''): string
    {
        if (empty($items)) {
            return '';
        }
        
        $html = '<' . $type . ' class="' . $class . '">';
        foreach ($items as $item) {
            $html .= '<li>' . htmlspecialchars($item) . '</li>';
        }
        $html .= '</' . $type . '>';
        
        return $html;
    }

    /**
     * Format boolean as Yes/No
     * 
     * @param bool $value
     * @return string
     */
    public static function bool(bool $value): string
    {
        return $value ? 'Yes' : 'No';
    }

    /**
     * Format boolean with badge
     * 
     * @param bool $value
     * @return string
     */
    public static function boolBadge(bool $value): string
    {
        if ($value) {
            return '<span class="badge bg-success">Yes</span>';
        }
        return '<span class="badge bg-secondary">No</span>';
    }
}