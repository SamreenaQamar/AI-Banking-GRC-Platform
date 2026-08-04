<?php
/**
 * AI Banking GRC Platform - Validator Library
 * 
 * @package    AI-Banking-GRC-Platform
 * @subpackage app/Libraries
 * @version    1.0.0
 * @author     GRC Platform Team
 * @copyright  2026 AI Banking GRC Platform
 * @license    Proprietary
 * 
 * This library provides enterprise validation functionality:
 * - Validation rules (required, email, min, max, etc.)
 * - Custom validation rules
 * - Error messages
 * - Validation chaining
 * - Rule registration
 * - Multiple validation scenarios
 */

declare(strict_types=1);

namespace App\Libraries;

use App\Libraries\Database;

class Validator
{
    /**
     * @var array Validation errors
     */
    private array $errors = [];

    /**
     * @var array Validation rules
     */
    private array $rules = [];

    /**
     * @var array Custom error messages
     */
    private array $customMessages = [];

    /**
     * @var array Validated data
     */
    private array $validated = [];

    /**
     * @var Database Database instance
     */
    private Database $db;

    /**
     * @var bool Validation passed flag
     */
    private bool $passed = true;

    /**
     * Constructor
     */
    public function __construct()
    {
        $this->db = Database::getInstance();
    }

    /**
     * Validate data against rules
     * 
     * @param array $data
     * @param array $rules
     * @param array $messages
     * @return bool
     */
    public function validate(array $data, array $rules, array $messages = []): bool
    {
        $this->errors = [];
        $this->validated = [];
        $this->rules = $rules;
        $this->customMessages = $messages;
        $this->passed = true;

        foreach ($rules as $field => $fieldRules) {
            $value = $data[$field] ?? null;
            $fieldRules = $this->parseRules($fieldRules);

            foreach ($fieldRules as $rule) {
                $this->validateRule($field, $value, $rule);
            }

            if (!isset($this->errors[$field])) {
                $this->validated[$field] = $value;
            }
        }

        return $this->passed;
    }

    /**
     * Parse rule string into array
     * 
     * @param string|array $rules
     * @return array
     */
    private function parseRules($rules): array
    {
        if (is_array($rules)) {
            return $rules;
        }

        return explode('|', $rules);
    }

    /**
     * Validate a single rule
     * 
     * @param string $field
     * @param mixed $value
     * @param string $rule
     * @return void
     */
    private function validateRule(string $field, $value, string $rule): void
    {
        $ruleParts = explode(':', $rule);
        $ruleName = $ruleParts[0];
        $parameters = isset($ruleParts[1]) ? explode(',', $ruleParts[1]) : [];

        $method = 'validate' . ucfirst($ruleName);

        if (method_exists($this, $method)) {
            if (!$this->$method($value, $parameters, $field)) {
                $this->addError($field, $ruleName, $parameters);
                $this->passed = false;
            }
        }
    }

    /**
     * Add validation error
     * 
     * @param string $field
     * @param string $rule
     * @param array $parameters
     * @return void
     */
    private function addError(string $field, string $rule, array $parameters = []): void
    {
        // Check for custom message
        $messageKey = $field . '.' . $rule;
        if (isset($this->customMessages[$messageKey])) {
            $message = $this->customMessages[$messageKey];
        } elseif (isset($this->customMessages[$rule])) {
            $message = $this->customMessages[$rule];
        } else {
            $message = $this->getDefaultMessage($rule);
        }

        // Replace placeholders
        $message = str_replace(':field', ucfirst(str_replace('_', ' ', $field)), $message);
        $message = str_replace(':attribute', ucfirst(str_replace('_', ' ', $field)), $message);
        
        foreach ($parameters as $index => $param) {
            $message = str_replace(':param' . ($index + 1), $param, $message);
            $message = str_replace(':' . ($index + 1), $param, $message);
        }

        if (!isset($this->errors[$field])) {
            $this->errors[$field] = [];
        }
        $this->errors[$field][] = $message;
    }

    /**
     * Get default error message
     * 
     * @param string $rule
     * @return string
     */
    private function getDefaultMessage(string $rule): string
    {
        $messages = [
            'required' => 'The :field field is required.',
            'email' => 'The :field must be a valid email address.',
            'min' => 'The :field must be at least :param1 characters.',
            'max' => 'The :field may not be greater than :param1 characters.',
            'min_value' => 'The :field must be at least :param1.',
            'max_value' => 'The :field may not be greater than :param1.',
            'numeric' => 'The :field must be a number.',
            'integer' => 'The :field must be an integer.',
            'float' => 'The :field must be a float.',
            'alpha' => 'The :field must contain only letters.',
            'alphanumeric' => 'The :field must contain only letters and numbers.',
            'date' => 'The :field must be a valid date.',
            'url' => 'The :field must be a valid URL.',
            'phone' => 'The :field must be a valid phone number.',
            'password' => 'The :field must be at least :param1 characters and contain uppercase, lowercase, number and special character.',
            'confirmed' => 'The :field confirmation does not match.',
            'unique' => 'The :field has already been taken.',
            'exists' => 'The selected :field is invalid.',
            'accepted' => 'The :field must be accepted.',
            'boolean' => 'The :field must be true or false.',
            'array' => 'The :field must be an array.',
            'in' => 'The selected :field is invalid.',
            'not_in' => 'The selected :field is invalid.',
            'regex' => 'The :field format is invalid.',
            'image' => 'The :field must be an image.',
            'mimes' => 'The :field must be a file of type: :param1.',
            'max_size' => 'The :field may not be greater than :param1 KB.',
            'min_size' => 'The :field must be at least :param1 KB.',
            'cnic' => 'The :field must be a valid CNIC number.',
            'iban' => 'The :field must be a valid IBAN number.',
            'swift' => 'The :field must be a valid SWIFT code.',
        ];

        return $messages[$rule] ?? 'The :field field is invalid.';
    }

    // ============================================================
    // VALIDATION RULES
    // ============================================================

    /**
     * Required validation
     * 
     * @param mixed $value
     * @param array $parameters
     * @return bool
     */
    protected function validateRequired($value, array $parameters = []): bool
    {
        if (is_null($value)) return false;
        if (is_string($value) && trim($value) === '') return false;
        if (is_array($value) && empty($value)) return false;
        return true;
    }

    /**
     * Email validation
     * 
     * @param mixed $value
     * @param array $parameters
     * @return bool
     */
    protected function validateEmail($value, array $parameters = []): bool
    {
        return filter_var($value, FILTER_VALIDATE_EMAIL) !== false;
    }

    /**
     * Min length validation
     * 
     * @param mixed $value
     * @param array $parameters
     * @return bool
     */
    protected function validateMin($value, array $parameters = []): bool
    {
        $min = (int)($parameters[0] ?? 0);
        return strlen((string)$value) >= $min;
    }

    /**
     * Max length validation
     * 
     * @param mixed $value
     * @param array $parameters
     * @return bool
     */
    protected function validateMax($value, array $parameters = []): bool
    {
        $max = (int)($parameters[0] ?? 0);
        return strlen((string)$value) <= $max;
    }

    /**
     * Min value validation
     * 
     * @param mixed $value
     * @param array $parameters
     * @return bool
     */
    protected function validateMinValue($value, array $parameters = []): bool
    {
        $min = (float)($parameters[0] ?? 0);
        return (float)$value >= $min;
    }

    /**
     * Max value validation
     * 
     * @param mixed $value
     * @param array $parameters
     * @return bool
     */
    protected function validateMaxValue($value, array $parameters = []): bool
    {
        $max = (float)($parameters[0] ?? 0);
        return (float)$value <= $max;
    }

    /**
     * Numeric validation
     * 
     * @param mixed $value
     * @param array $parameters
     * @return bool
     */
    protected function validateNumeric($value, array $parameters = []): bool
    {
        return is_numeric($value);
    }

    /**
     * Integer validation
     * 
     * @param mixed $value
     * @param array $parameters
     * @return bool
     */
    protected function validateInteger($value, array $parameters = []): bool
    {
        return filter_var($value, FILTER_VALIDATE_INT) !== false;
    }

    /**
     * Float validation
     * 
     * @param mixed $value
     * @param array $parameters
     * @return bool
     */
    protected function validateFloat($value, array $parameters = []): bool
    {
        return filter_var($value, FILTER_VALIDATE_FLOAT) !== false;
    }

    /**
     * Alpha validation
     * 
     * @param mixed $value
     * @param array $parameters
     * @return bool
     */
    protected function validateAlpha($value, array $parameters = []): bool
    {
        return preg_match('/^[a-zA-Z]+$/', (string)$value) === 1;
    }

    /**
     * Alphanumeric validation
     * 
     * @param mixed $value
     * @param array $parameters
     * @return bool
     */
    protected function validateAlphanumeric($value, array $parameters = []): bool
    {
        return preg_match('/^[a-zA-Z0-9]+$/', (string)$value) === 1;
    }

    /**
     * Date validation
     * 
     * @param mixed $value
     * @param array $parameters
     * @return bool
     */
    protected function validateDate($value, array $parameters = []): bool
    {
        $format = $parameters[0] ?? 'Y-m-d';
        $date = \DateTime::createFromFormat($format, (string)$value);
        return $date && $date->format($format) === (string)$value;
    }

    /**
     * URL validation
     * 
     * @param mixed $value
     * @param array $parameters
     * @return bool
     */
    protected function validateUrl($value, array $parameters = []): bool
    {
        return filter_var($value, FILTER_VALIDATE_URL) !== false;
    }

    /**
     * Phone validation (Pakistan format)
     * 
     * @param mixed $value
     * @param array $parameters
     * @return bool
     */
    protected function validatePhone($value, array $parameters = []): bool
    {
        return preg_match('/^(\+92|0)[0-9]{10,12}$/', (string)$value) === 1;
    }

    /**
     * Password validation
     * 
     * @param mixed $value
     * @param array $parameters
     * @return bool
     */
    protected function validatePassword($value, array $parameters = []): bool
    {
        $min = (int)($parameters[0] ?? 8);
        $password = (string)$value;

        if (strlen($password) < $min) return false;
        if (!preg_match('/[A-Z]/', $password)) return false;
        if (!preg_match('/[a-z]/', $password)) return false;
        if (!preg_match('/[0-9]/', $password)) return false;
        if (!preg_match('/[!@#$%^&*(),.?":{}|<>]/', $password)) return false;

        return true;
    }

    /**
     * Confirmed validation
     * 
     * @param mixed $value
     * @param array $parameters
     * @return bool
     */
    protected function validateConfirmed($value, array $parameters = []): bool
    {
        $field = $parameters[0] ?? '';
        $confirmField = $field ? $field . '_confirmation' : 'confirmation';
        $confirmValue = $_POST[$confirmField] ?? null;
        return (string)$value === (string)$confirmValue;
    }

    /**
     * Unique validation
     * 
     * @param mixed $value
     * @param array $parameters
     * @return bool
     */
    protected function validateUnique($value, array $parameters = []): bool
    {
        $table = $parameters[0] ?? '';
        $column = $parameters[1] ?? 'id';
        $excludeId = $parameters[2] ?? null;

        if (empty($table)) return true;

        $sql = "SELECT COUNT(*) FROM {$table} WHERE {$column} = :value";
        $params = ['value' => $value];

        if ($excludeId) {
            $sql .= " AND id != :id";
            $params['id'] = $excludeId;
        }

        $count = $this->db->fetchColumn($sql, $params);
        return $count == 0;
    }

    /**
     * Exists validation
     * 
     * @param mixed $value
     * @param array $parameters
     * @return bool
     */
    protected function validateExists($value, array $parameters = []): bool
    {
        $table = $parameters[0] ?? '';
        $column = $parameters[1] ?? 'id';

        if (empty($table)) return true;

        $sql = "SELECT COUNT(*) FROM {$table} WHERE {$column} = :value";
        $count = $this->db->fetchColumn($sql, ['value' => $value]);
        return $count > 0;
    }

    /**
     * Accepted validation
     * 
     * @param mixed $value
     * @param array $parameters
     * @return bool
     */
    protected function validateAccepted($value, array $parameters = []): bool
    {
        return in_array($value, ['yes', 'on', 1, '1', 'true', true], true);
    }

    /**
     * Boolean validation
     * 
     * @param mixed $value
     * @param array $parameters
     * @return bool
     */
    protected function validateBoolean($value, array $parameters = []): bool
    {
        return filter_var($value, FILTER_VALIDATE_BOOLEAN, FILTER_NULL_ON_FAILURE) !== null;
    }

    /**
     * Array validation
     * 
     * @param mixed $value
     * @param array $parameters
     * @return bool
     */
    protected function validateArray($value, array $parameters = []): bool
    {
        return is_array($value);
    }

    /**
     * In validation
     * 
     * @param mixed $value
     * @param array $parameters
     * @return bool
     */
    protected function validateIn($value, array $parameters = []): bool
    {
        return in_array($value, $parameters, true);
    }

    /**
     * Not in validation
     * 
     * @param mixed $value
     * @param array $parameters
     * @return bool
     */
    protected function validateNotIn($value, array $parameters = []): bool
    {
        return !in_array($value, $parameters, true);
    }

    /**
     * Regex validation
     * 
     * @param mixed $value
     * @param array $parameters
     * @return bool
     */
    protected function validateRegex($value, array $parameters = []): bool
    {
        $pattern = $parameters[0] ?? '';
        if (empty($pattern)) return true;
        return preg_match($pattern, (string)$value) === 1;
    }

    /**
     * Image validation
     * 
     * @param mixed $value
     * @param array $parameters
     * @return bool
     */
    protected function validateImage($value, array $parameters = []): bool
    {
        if (!is_array($value) || !isset($value['tmp_name'])) return false;
        
        $finfo = finfo_open(FILEINFO_MIME_TYPE);
        $mimeType = finfo_file($finfo, $value['tmp_name']);
        finfo_close($finfo);

        return in_array($mimeType, [
            'image/jpeg',
            'image/png',
            'image/gif',
            'image/webp',
            'image/svg+xml'
        ]);
    }

    /**
     * Mimes validation
     * 
     * @param mixed $value
     * @param array $parameters
     * @return bool
     */
    protected function validateMimes($value, array $parameters = []): bool
    {
        if (!is_array($value) || !isset($value['tmp_name'])) return false;

        $allowed = $parameters;
        if (empty($allowed)) return true;

        $extension = strtolower(pathinfo($value['name'], PATHINFO_EXTENSION));
        return in_array($extension, $allowed);
    }

    /**
     * Max file size validation
     * 
     * @param mixed $value
     * @param array $parameters
     * @return bool
     */
    protected function validateMaxSize($value, array $parameters = []): bool
    {
        if (!is_array($value) || !isset($value['size'])) return false;

        $maxSize = (int)($parameters[0] ?? 2048) * 1024; // Convert KB to bytes
        return $value['size'] <= $maxSize;
    }

    /**
     * Min file size validation
     * 
     * @param mixed $value
     * @param array $parameters
     * @return bool
     */
    protected function validateMinSize($value, array $parameters = []): bool
    {
        if (!is_array($value) || !isset($value['size'])) return false;

        $minSize = (int)($parameters[0] ?? 0) * 1024; // Convert KB to bytes
        return $value['size'] >= $minSize;
    }

    /**
     * CNIC validation (Pakistan format)
     * 
     * @param mixed $value
     * @param array $parameters
     * @return bool
     */
    protected function validateCnic($value, array $parameters = []): bool
    {
        return preg_match('/^[0-9]{5}-[0-9]{7}-[0-9]$/', (string)$value) === 1;
    }

    /**
     * IBAN validation
     * 
     * @param mixed $value
     * @param array $parameters
     * @return bool
     */
    protected function validateIban($value, array $parameters = []): bool
    {
        return preg_match('/^PK[0-9]{2}[A-Z]{4}[0-9A-Z]{16}$/', (string)$value) === 1;
    }

    /**
     * SWIFT validation
     * 
     * @param mixed $value
     * @param array $parameters
     * @return bool
     */
    protected function validateSwift($value, array $parameters = []): bool
    {
        return preg_match('/^[A-Z]{6}[A-Z0-9]{2}([A-Z0-9]{3})?$/', (string)$value) === 1;
    }

    // ============================================================
    // HELPER METHODS
    // ============================================================

    /**
     * Get validation errors
     * 
     * @return array
     */
    public function getErrors(): array
    {
        return $this->errors;
    }

    /**
     * Get first error for field
     * 
     * @param string $field
     * @return string|null
     */
    public function getFirstError(string $field): ?string
    {
        return $this->errors[$field][0] ?? null;
    }

    /**
     * Get all errors for field
     * 
     * @param string $field
     * @return array
     */
    public function getFieldErrors(string $field): array
    {
        return $this->errors[$field] ?? [];
    }

    /**
     * Check if validation failed
     * 
     * @return bool
     */
    public function fails(): bool
    {
        return !$this->passed;
    }

    /**
     * Check if validation passed
     * 
     * @return bool
     */
    public function passes(): bool
    {
        return $this->passed;
    }

    /**
     * Get validated data
     * 
     * @return array
     */
    public function validated(): array
    {
        return $this->validated;
    }

    /**
     * Add custom validation rule
     * 
     * @param string $rule
     * @param callable $callback
     * @param string $message
     * @return void
     */
    public function addRule(string $rule, callable $callback, string $message = ''): void
    {
        $method = 'validate' . ucfirst($rule);
        if (!method_exists($this, $method)) {
            $this->$method = $callback;
        }
        
        if ($message) {
            $this->customMessages[$rule] = $message;
        }
    }

    /**
     * Add custom message
     * 
     * @param string $rule
     * @param string $message
     * @return void
     */
    public function addMessage(string $rule, string $message): void
    {
        $this->customMessages[$rule] = $message;
    }

    /**
     * Add multiple custom messages
     * 
     * @param array $messages
     * @return void
     */
    public function addMessages(array $messages): void
    {
        $this->customMessages = array_merge($this->customMessages, $messages);
    }

    /**
     * Set custom messages for field
     * 
     * @param string $field
     * @param array $messages
     * @return void
     */
    public function setFieldMessages(string $field, array $messages): void
    {
        foreach ($messages as $rule => $message) {
            $this->customMessages[$field . '.' . $rule] = $message;
        }
    }
}