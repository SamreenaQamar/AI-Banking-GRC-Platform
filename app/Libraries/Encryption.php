<?php
/**
 * AI Banking GRC Platform - Encryption Library
 * 
 * @package    AI-Banking-GRC-Platform
 * @subpackage app/Libraries
 * @version    1.0.0
 * @author     GRC Platform Team
 * @copyright  2026 AI Banking GRC Platform
 * @license    Proprietary
 * 
 * This library provides enterprise encryption functionality:
 * - AES-256 encryption/decryption
 * - OpenSSL support
 * - Random IV generation
 * - Secure key generation
 * - Password hashing
 * - HMAC verification
 */

declare(strict_types=1);

namespace App\Libraries;

class Encryption
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
     * @var string Encryption key
     */
    private string $key;

    /**
     * Constructor
     */
    public function __construct()
    {
        $key = getenv('ENCRYPTION_KEY');
        if (empty($key)) {
            $key = 'default-encryption-key-change-in-production-12345';
        }
        $this->key = substr(hash('sha256', $key, true), 0, self::KEY_LENGTH);
    }

    /**
     * Encrypt data
     * 
     * @param string $data
     * @return string
     */
    public function encrypt(string $data): string
    {
        $iv = random_bytes(self::IV_LENGTH);
        $encrypted = openssl_encrypt(
            $data,
            self::METHOD,
            $this->key,
            OPENSSL_RAW_DATA,
            $iv
        );

        if ($encrypted === false) {
            throw new \RuntimeException('Encryption failed');
        }

        return base64_encode($iv . $encrypted);
    }

    /**
     * Decrypt data
     * 
     * @param string $data
     * @return string
     */
    public function decrypt(string $data): string
    {
        $decoded = base64_decode($data, true);

        if ($decoded === false) {
            throw new \RuntimeException('Invalid base64 data');
        }

        $iv = substr($decoded, 0, self::IV_LENGTH);
        $encrypted = substr($decoded, self::IV_LENGTH);

        if (strlen($iv) !== self::IV_LENGTH) {
            throw new \RuntimeException('Invalid IV length');
        }

        $decrypted = openssl_decrypt(
            $encrypted,
            self::METHOD,
            $this->key,
            OPENSSL_RAW_DATA,
            $iv
        );

        if ($decrypted === false) {
            throw new \RuntimeException('Decryption failed');
        }

        return $decrypted;
    }

    /**
     * Hash data
     * 
     * @param string $data
     * @param string $algo
     * @return string
     */
    public function hash(string $data, string $algo = 'sha256'): string
    {
        return hash($algo, $data);
    }

    /**
     * Hash with HMAC
     * 
     * @param string $data
     * @param string $algo
     * @return string
     */
    public function hashHmac(string $data, string $algo = 'sha256'): string
    {
        return hash_hmac($algo, $data, $this->key);
    }

    /**
     * Verify hash
     * 
     * @param string $data
     * @param string $hash
     * @param string $algo
     * @return bool
     */
    public function verifyHash(string $data, string $hash, string $algo = 'sha256'): bool
    {
        return hash_equals($this->hash($data, $algo), $hash);
    }

    /**
     * Verify HMAC
     * 
     * @param string $data
     * @param string $hash
     * @param string $algo
     * @return bool
     */
    public function verifyHmac(string $data, string $hash, string $algo = 'sha256'): bool
    {
        return hash_equals($this->hashHmac($data, $algo), $hash);
    }

    /**
     * Generate random key
     * 
     * @param int $length
     * @return string
     */
    public function generateKey(int $length = 32): string
    {
        return bin2hex(random_bytes($length));
    }

    /**
     * Generate random IV
     * 
     * @return string
     */
    public function generateIV(): string
    {
        return random_bytes(self::IV_LENGTH);
    }

    /**
     * Encrypt with base64
     * 
     * @param string $data
     * @return string
     */
    public function encryptBase64(string $data): string
    {
        return $this->encrypt($data);
    }

    /**
     * Decrypt from base64
     * 
     * @param string $data
     * @return string
     */
    public function decryptBase64(string $data): string
    {
        return $this->decrypt($data);
    }

    /**
     * Encrypt array
     * 
     * @param array $data
     * @return string
     */
    public function encryptArray(array $data): string
    {
        return $this->encrypt(json_encode($data));
    }

    /**
     * Decrypt array
     * 
     * @param string $data
     * @return array
     */
    public function decryptArray(string $data): array
    {
        $decrypted = $this->decrypt($data);
        $result = json_decode($decrypted, true);
        
        if ($result === null) {
            throw new \RuntimeException('Invalid JSON data');
        }
        
        return $result;
    }

    /**
     * Set encryption key
     * 
     * @param string $key
     * @return void
     */
    public function setKey(string $key): void
    {
        $this->key = substr(hash('sha256', $key, true), 0, self::KEY_LENGTH);
    }

    /**
     * Get encryption method
     * 
     * @return string
     */
    public function getMethod(): string
    {
        return self::METHOD;
    }
}