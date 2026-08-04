<?php
/**
 * AI Banking GRC Platform - Validation Helper
 * 
 * @package    AI-Banking-GRC-Platform
 * @subpackage app/Helpers
 * @version    1.0.0
 * @author     GRC Platform Team
 * @copyright  2026 AI Banking GRC Platform
 * @license    Proprietary
 * 
 * This helper provides validation functionality:
 * - Data validation methods
 * - Type checking
 * - Length validation
 * - File validation
 * - Custom validation rules
 */

declare(strict_types=1);

namespace App\Helpers;

use App\Helpers\SecurityHelper;

class ValidationHelper
{
    /**
     * @var array Validation errors
     */
    private static array $errors = [];

    /**
     * Validate email address
     * 
     * @param string $email
     * @return bool
     */
    public static function validateEmail(string $email): bool
    {
        return filter_var($email, FILTER_VALIDATE_EMAIL) !== false;
    }

    /**
     * Validate password strength
     * 
     * @param string $password
     * @param int $minLength
     * @param bool $requireSpecial
     * @return bool
     */
    public static function validatePassword(
        string $password,
        int $minLength = 8,
        bool $requireSpecial = true
    ): bool {
        if (strlen($password) < $minLength) {
            return false;
        }

        if (!preg_match('/[A-Z]/', $password)) {
            return false;
        }

        if (!preg_match('/[a-z]/', $password)) {
            return false;
        }

        if (!preg_match('/[0-9]/', $password)) {
            return false;
        }

        if ($requireSpecial && !preg_match('/[!@#$%^&*(),.?":{}|<>]/', $password)) {
            return false;
        }

        return true;
    }

    /**
     * Validate phone number (Pakistan format)
     * 
     * @param string $phone
     * @return bool
     */
    public static function validatePhone(string $phone): bool
    {
        return (bool)preg_match('/^(\+92|0)[0-9]{10,12}$/', $phone);
    }

    /**
     * Validate CNIC (Pakistan format)
     * 
     * @param string $cnic
     * @return bool
     */
    public static function validateCnic(string $cnic): bool
    {
        return (bool)preg_match('/^[0-9]{5}-[0-9]{7}-[0-9]$/', $cnic);
    }

    /**
     * Validate required field
     * 
     * @param mixed $value
     * @return bool
     */
    public static function validateRequired($value): bool
    {
        if (is_null($value)) {
            return false;
        }

        if (is_string($value) && trim($value) === '') {
            return false;
        }

        if (is_array($value) && empty($value)) {
            return false;
        }

        return true;
    }

    /**
     * Validate integer
     * 
     * @param mixed $value
     * @param int|null $min
     * @param int|null $max
     * @return bool
     */
    public static function validateInteger($value, ?int $min = null, ?int $max = null): bool
    {
        if (!filter_var($value, FILTER_VALIDATE_INT)) {
            return false;
        }

        $value = (int)$value;

        if ($min !== null && $value < $min) {
            return false;
        }

        if ($max !== null && $value > $max) {
            return false;
        }

        return true;
    }

    /**
     * Validate float
     * 
     * @param mixed $value
     * @param float|null $min
     * @param float|null $max
     * @return bool
     */
    public static function validateFloat($value, ?float $min = null, ?float $max = null): bool
    {
        if (!filter_var($value, FILTER_VALIDATE_FLOAT)) {
            return false;
        }

        $value = (float)$value;

        if ($min !== null && $value < $min) {
            return false;
        }

        if ($max !== null && $value > $max) {
            return false;
        }

        return true;
    }

    /**
     * Validate string length
     * 
     * @param string $value
     * @param int $min
     * @param int|null $max
     * @return bool
     */
    public static function validateLength(string $value, int $min, ?int $max = null): bool
    {
        $length = mb_strlen($value);

        if ($length < $min) {
            return false;
        }

        if ($max !== null && $length > $max) {
            return false;
        }

        return true;
    }

    /**
     * Validate file
     * 
     * @param array $file
     * @param array $allowedTypes
     * @param int|null $maxSize
     * @return bool
     */
    public static function validateFile(array $file, array $allowedTypes = [], ?int $maxSize = null): bool
    {
        // Check if file was uploaded
        if (!isset($file['error']) || $file['error'] !== UPLOAD_ERR_OK) {
            return false;
        }

        // Check file size
        if ($maxSize !== null && $file['size'] > $maxSize) {
            return false;
        }

        // Check file type
        if (!empty($allowedTypes)) {
            $fileInfo = finfo_open(FILEINFO_MIME_TYPE);
            $mimeType = finfo_file($fileInfo, $file['tmp_name']);
            finfo_close($fileInfo);

            if (!in_array($mimeType, $allowedTypes)) {
                return false;
            }
        }

        return true;
    }

    /**
     * Validate date format
     * 
     * @param string $date
     * @param string $format
     * @return bool
     */
    public static function validateDate(string $date, string $format = 'Y-m-d'): bool
    {
        $d = \DateTime::createFromFormat($format, $date);
        return $d && $d->format($format) === $date;
    }

    /**
     * Validate URL
     * 
     * @param string $url
     * @return bool
     */
    public static function validateUrl(string $url): bool
    {
        return filter_var($url, FILTER_VALIDATE_URL) !== false;
    }

    /**
     * Validate boolean
     * 
     * @param mixed $value
     * @return bool
     */
    public static function validateBoolean($value): bool
    {
        return filter_var($value, FILTER_VALIDATE_BOOLEAN, FILTER_NULL_ON_FAILURE) !== null;
    }

    /**
     * Validate in array
     * 
     * @param mixed $value
     * @param array $allowed
     * @return bool
     */
    public static function validateIn($value, array $allowed): bool
    {
        return in_array($value, $allowed, true);
    }

    /**
     * Validate unique field in database
     * 
     * @param string $table
     * @param string $field
     * @param string $value
     * @param int|null $excludeId
     * @return bool
     */
    public static function validateUnique(string $table, string $field, string $value, ?int $excludeId = null): bool
    {
        $db = \Database::getInstance()->getConnection();
        
        $sql = "SELECT COUNT(*) FROM {$table} WHERE {$field} = :value";
        $params = ['value' => $value];

        if ($excludeId !== null) {
            $sql .= " AND id != :id";
            $params['id'] = $excludeId;
        }

        $stmt = $db->prepare($sql);
        $stmt->execute($params);
        $count = $stmt->fetchColumn();

        return $count == 0;
    }

    /**
     * Validate alpha characters only
     * 
     * @param string $value
     * @return bool
     */
    public static function validateAlpha(string $value): bool
    {
        return (bool)preg_match('/^[a-zA-Z]+$/', $value);
    }

    /**
     * Validate alphanumeric characters only
     * 
     * @param string $value
     * @return bool
     */
    public static function validateAlnum(string $value): bool
    {
        return (bool)preg_match('/^[a-zA-Z0-9]+$/', $value);
    }

    /**
     * Get validation errors
     * 
     * @return array
     */
    public static function getErrors(): array
    {
        return self::$errors;
    }

    /**
     * Add validation error
     * 
     * @param string $field
     * @param string $message
     * @return void
     */
    public static function addError(string $field, string $message): void
    {
        self::$errors[$field] = $message;
    }

    /**
     * Clear validation errors
     * 
     * @return void
     */
    public static function clearErrors(): void
    {
        self::$errors = [];
    }

    /**
     * Check if validation passed
     * 
     * @return bool
     */
    public static function passes(): bool
    {
        return empty(self::$errors);
    }

    /**
     * Check if validation failed
     * 
     * @return bool
     */
    public static function fails(): bool
    {
        return !empty(self::$errors);
    }
}