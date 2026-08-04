<?php
/**
 * AI Banking GRC Platform - Encryption Helper
 * 
 * @package    AI-Banking-GRC-Platform
 * @subpackage app/Helpers
 * @version    1.0.0
 * @author     GRC Platform Team
 * @copyright  2026 AI Banking GRC Platform
 * @license    Proprietary
 * 
 * This helper provides encryption functionality:
 * - AES-256-CBC encryption/decryption
 * - Base64 encoding/decoding
 * - Secure key generation
 * - Data protection
 */

declare(strict_types=1);

namespace App\Helpers;

class EncryptionHelper
{
    /**
     * @var string Encryption method
     */
    private const METHOD = 'AES-256-CBC';

    /**
     * @var int Key length in bytes
     */
    private const KEY_LENGTH = 32;

    /**
     * @var int IV length in bytes
     */
    private const IV_LENGTH = 16;

    /**
     * @var string|null Encryption key
     */
    private static ?string $key = null;

    /**
     * Set encryption key
     * 
     * @param string $key
     * @return void
     */
    public static function setKey(string $key): void
    {
        self::$key = $key;
    }

    /**
     * Get encryption key
     * 
     * @return string
     */
    private static function getKey(): string
    {
        if (self::$key === null) {
            // Get key from environment or generate one
            $key = getenv('ENCRYPTION_KEY');
            if (empty($key)) {
                $key = 'default-encryption-key-change-this-in-production';
            }
            self::$key = substr(hash('sha256', $key, true), 0, self::KEY_LENGTH);
        }
        return self::$key;
    }

    /**
     * Encrypt data
     * 
     * @param string $data
     * @return string
     */
    public static function encrypt(string $data): string
    {
        $key = self::getKey();
        $iv = random_bytes(self::IV_LENGTH);
        
        $encrypted = openssl_encrypt(
            $data,
            self::METHOD,
            $key,
            OPENSSL_RAW_DATA,
            $iv
        );

        if ($encrypted === false) {
            throw new \RuntimeException('Encryption failed');
        }

        // Combine IV and encrypted data
        $combined = $iv . $encrypted;
        
        return base64_encode($combined);
    }

    /**
     * Decrypt data
     * 
     * @param string $data
     * @return string
     */
    public static function decrypt(string $data): string
    {
        $key = self::getKey();
        $decoded = base64_decode($data, true);

        if ($decoded === false) {
            throw new \RuntimeException('Invalid base64 data');
        }

        // Extract IV and encrypted data
        $iv = substr($decoded, 0, self::IV_LENGTH);
        $encrypted = substr($decoded, self::IV_LENGTH);

        if (strlen($iv) !== self::IV_LENGTH) {
            throw new \RuntimeException('Invalid IV length');
        }

        $decrypted = openssl_decrypt(
            $encrypted,
            self::METHOD,
            $key,
            OPENSSL_RAW_DATA,
            $iv
        );

        if ($decrypted === false) {
            throw new \RuntimeException('Decryption failed');
        }

        return $decrypted;
    }

    /**
     * Base64 encode data
     * 
     * @param string $data
     * @return string
     */
    public static function base64Encode(string $data): string
    {
        return base64_encode($data);
    }

    /**
     * Base64 decode data
     * 
     * @param string $data
     * @return string
     */
    public static function base64Decode(string $data): string
    {
        $decoded = base64_decode($data, true);
        if ($decoded === false) {
            throw new \RuntimeException('Invalid base64 data');
        }
        return $decoded;
    }

    /**
     * Generate secure random key
     * 
     * @param int $length
     * @return string
     */
    public static function generateKey(int $length = 32): string
    {
        return bin2hex(random_bytes($length));
    }

    /**
     * Encrypt data and return base64 encoded
     * 
     * @param string $data
     * @return string
     */
    public static function encryptBase64(string $data): string
    {
        return self::base64Encode(self::encrypt($data));
    }

    /**
     * Decrypt base64 encoded data
     * 
     * @param string $data
     * @return string
     */
    public static function decryptBase64(string $data): string
    {
        return self::decrypt(self::base64Decode($data));
    }

    /**
     * Hash data with HMAC
     * 
     * @param string $data
     * @param string $algo
     * @return string
     */
    public static function hashHmac(string $data, string $algo = 'sha256'): string
    {
        $key = self::getKey();
        return hash_hmac($algo, $data, $key);
    }

    /**
     * Verify HMAC
     * 
     * @param string $data
     * @param string $hash
     * @param string $algo
     * @return bool
     */
    public static function verifyHmac(string $data, string $hash, string $algo = 'sha256'): bool
    {
        $expected = self::hashHmac($data, $algo);
        return hash_equals($expected, $hash);
    }

    /**
     * Encrypt array to JSON
     * 
     * @param array $data
     * @return string
     */
    public static function encryptArray(array $data): string
    {
        return self::encrypt(json_encode($data));
    }

    /**
     * Decrypt array from JSON
     * 
     * @param string $data
     * @return array
     */
    public static function decryptArray(string $data): array
    {
        $decrypted = self::decrypt($data);
        $result = json_decode($decrypted, true);
        
        if ($result === null) {
            throw new \RuntimeException('Invalid JSON data');
        }
        
        return $result;
    }
}