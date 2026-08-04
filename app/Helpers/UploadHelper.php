<?php
/**
 * AI Banking GRC Platform - Upload Helper
 * 
 * @package    AI-Banking-GRC-Platform
 * @subpackage app/Helpers
 * @version    1.0.0
 * @author     GRC Platform Team
 * @copyright  2026 AI Banking GRC Platform
 * @license    Proprietary
 * 
 * This helper provides file upload functionality:
 * - Image upload
 * - Document upload
 * - File validation
 * - Upload management
 */

declare(strict_types=1);

namespace App\Helpers;

class UploadHelper
{
    /**
     * @var array Upload errors
     */
    private static array $errors = [];

    /**
     * Upload image
     * 
     * @param array $file
     * @param string $destination
     * @param array $options
     * @return string|false
     */
    public static function uploadImage(array $file, string $destination, array $options = []): string|false
    {
        $maxWidth = $options['max_width'] ?? 2048;
        $maxHeight = $options['max_height'] ?? 2048;
        $maxSize = $options['max_size'] ?? 5242880; // 5MB
        $allowedTypes = $options['allowed_types'] ?? ['jpg', 'jpeg', 'png', 'gif', 'webp'];

        // Validate
        if (!self::validateUpload($file, $maxSize, $allowedTypes)) {
            return false;
        }

        // Check image dimensions
        $imageInfo = getimagesize($file['tmp_name']);
        if (!$imageInfo) {
            self::$errors[] = 'Invalid image file.';
            return false;
        }

        list($width, $height) = $imageInfo;

        if ($width > $maxWidth || $height > $maxHeight) {
            self::$errors[] = "Image dimensions exceed maximum allowed ({$maxWidth}x{$maxHeight}).";
            return false;
        }

        // Generate filename
        $extension = pathinfo($file['name'], PATHINFO_EXTENSION);
        $filename = $options['filename'] ?? uniqid('img_') . '.' . $extension;
        $filename = SecurityHelper::cleanFilename($filename);

        // Upload
        $result = FileHelper::uploadFile($file, $destination, $filename);
        
        if ($result === false) {
            self::$errors[] = 'Failed to upload image.';
            return false;
        }

        return $result;
    }

    /**
     * Upload document
     * 
     * @param array $file
     * @param string $destination
     * @param array $options
     * @return string|false
     */
    public static function uploadDocument(array $file, string $destination, array $options = []): string|false
    {
        $maxSize = $options['max_size'] ?? 10485760; // 10MB
        $allowedTypes = $options['allowed_types'] ?? ['pdf', 'doc', 'docx', 'xls', 'xlsx', 'txt'];

        // Validate
        if (!self::validateUpload($file, $maxSize, $allowedTypes)) {
            return false;
        }

        // Generate filename
        $extension = pathinfo($file['name'], PATHINFO_EXTENSION);
        $filename = $options['filename'] ?? uniqid('doc_') . '.' . $extension;
        $filename = SecurityHelper::cleanFilename($filename);

        // Upload
        $result = FileHelper::uploadFile($file, $destination, $filename);
        
        if ($result === false) {
            self::$errors[] = 'Failed to upload document.';
            return false;
        }

        return $result;
    }

    /**
     * Validate upload
     * 
     * @param array $file
     * @param int $maxSize
     * @param array $allowedTypes
     * @return bool
     */
    public static function validateUpload(array $file, int $maxSize = 10485760, array $allowedTypes = []): bool
    {
        self::$errors = [];

        // Check if file was uploaded
        if (!isset($file['error']) || $file['error'] !== UPLOAD_ERR_OK) {
            $errorMessages = [
                UPLOAD_ERR_INI_SIZE => 'File exceeds upload_max_filesize directive.',
                UPLOAD_ERR_FORM_SIZE => 'File exceeds MAX_FILE_SIZE directive.',
                UPLOAD_ERR_PARTIAL => 'File was only partially uploaded.',
                UPLOAD_ERR_NO_FILE => 'No file was uploaded.',
                UPLOAD_ERR_NO_TMP_DIR => 'Missing temporary folder.',
                UPLOAD_ERR_CANT_WRITE => 'Failed to write file to disk.',
                UPLOAD_ERR_EXTENSION => 'File upload stopped by extension.'
            ];
            
            $errorCode = $file['error'];
            self::$errors[] = $errorMessages[$errorCode] ?? 'Unknown upload error.';
            return false;
        }

        // Check file size
        if ($file['size'] > $maxSize) {
            $maxSizeMB = $maxSize / 1048576;
            self::$errors[] = "File size exceeds maximum allowed ({$maxSizeMB}MB).";
            return false;
        }

        // Check file type
        if (!empty($allowedTypes)) {
            $extension = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
            if (!in_array($extension, $allowedTypes)) {
                $allowed = implode(', ', $allowedTypes);
                self::$errors[] = "File type not allowed. Allowed types: {$allowed}.";
                return false;
            }
        }

        return true;
    }

    /**
     * Delete uploaded file
     * 
     * @param string $path
     * @return bool
     */
    public static function deleteUpload(string $path): bool
    {
        if (empty($path)) {
            return false;
        }

        $fullPath = UPLOADS_PATH . '/' . ltrim($path, '/');
        return FileHelper::deleteFile($fullPath);
    }

    /**
     * Get upload errors
     * 
     * @return array
     */
    public static function getErrors(): array
    {
        return self::$errors;
    }

    /**
     * Get upload URL
     * 
     * @param string $path
     * @return string
     */
    public static function getUrl(string $path): string
    {
        return UPLOADS_URL . '/' . ltrim($path, '/');
    }

    /**
     * Get upload path
     * 
     * @param string $path
     * @return string
     */
    public static function getPath(string $path): string
    {
        return UPLOADS_PATH . '/' . ltrim($path, '/');
    }

    /**
     * Upload avatar
     * 
     * @param array $file
     * @param int $userId
     * @return string|false
     */
    public static function uploadAvatar(array $file, int $userId): string|false
    {
        $destination = UPLOADS_PATH . '/avatars/' . $userId;
        $options = [
            'max_width' => 400,
            'max_height' => 400,
            'max_size' => 2097152, // 2MB
            'allowed_types' => ['jpg', 'jpeg', 'png', 'gif'],
            'filename' => $userId . '.' . pathinfo($file['name'], PATHINFO_EXTENSION)
        ];

        return self::uploadImage($file, $destination, $options);
    }

    /**
     * Upload policy document
     * 
     * @param array $file
     * @param int $policyId
     * @return string|false
     */
    public static function uploadPolicy(array $file, int $policyId): string|false
    {
        $destination = UPLOADS_PATH . '/policies/' . $policyId;
        $options = [
            'max_size' => 10485760, // 10MB
            'allowed_types' => ['pdf', 'doc', 'docx', 'txt'],
            'filename' => $policyId . '_' . date('Ymd_His') . '.' . pathinfo($file['name'], PATHINFO_EXTENSION)
        ];

        return self::uploadDocument($file, $destination, $options);
    }

    /**
     * Upload compliance evidence
     * 
     * @param array $file
     * @param int $taskId
     * @return string|false
     */
    public static function uploadEvidence(array $file, int $taskId): string|false
    {
        $destination = UPLOADS_PATH . '/compliance/evidence/' . $taskId;
        $options = [
            'max_size' => 20971520, // 20MB
            'allowed_types' => ['pdf', 'doc', 'docx', 'xls', 'xlsx', 'jpg', 'jpeg', 'png', 'txt'],
            'filename' => $taskId . '_' . date('Ymd_His') . '.' . pathinfo($file['name'], PATHINFO_EXTENSION)
        ];

        return self::uploadDocument($file, $destination, $options);
    }

    /**
     * Upload audit evidence
     * 
     * @param array $file
     * @param int $auditId
     * @return string|false
     */
    public static function uploadAuditEvidence(array $file, int $auditId): string|false
    {
        $destination = UPLOADS_PATH . '/audit/evidence/' . $auditId;
        $options = [
            'max_size' => 20971520, // 20MB
            'allowed_types' => ['pdf', 'doc', 'docx', 'xls', 'xlsx', 'jpg', 'jpeg', 'png', 'mp4', 'zip'],
            'filename' => $auditId . '_' . date('Ymd_His') . '.' . pathinfo($file['name'], PATHINFO_EXTENSION)
        ];

        return self::uploadDocument($file, $destination, $options);
    }

    /**
     * Resize image
     * 
     * @param string $sourcePath
     * @param string $destinationPath
     * @param int $maxWidth
     * @param int $maxHeight
     * @param int $quality
     * @return bool
     */
    public static function resizeImage(
        string $sourcePath,
        string $destinationPath,
        int $maxWidth = 800,
        int $maxHeight = 800,
        int $quality = 85
    ): bool {
        if (!file_exists($sourcePath)) {
            return false;
        }

        $imageInfo = getimagesize($sourcePath);
        if (!$imageInfo) {
            return false;
        }

        list($width, $height, $type) = $imageInfo;

        // Calculate new dimensions
        $ratio = min($maxWidth / $width, $maxHeight / $height);
        if ($ratio < 1) {
            $newWidth = (int)($width * $ratio);
            $newHeight = (int)($height * $ratio);
        } else {
            $newWidth = $width;
            $newHeight = $height;
        }

        // Create image resource
        $source = match ($type) {
            IMAGETYPE_JPEG => imagecreatefromjpeg($sourcePath),
            IMAGETYPE_PNG => imagecreatefrompng($sourcePath),
            IMAGETYPE_GIF => imagecreatefromgif($sourcePath),
            IMAGETYPE_WEBP => imagecreatefromwebp($sourcePath),
            default => false
        };

        if (!$source) {
            return false;
        }

        // Create new image
        $destination = imagecreatetruecolor($newWidth, $newHeight);

        // Preserve transparency for PNG
        if ($type === IMAGETYPE_PNG) {
            imagealphablending($destination, false);
            imagesavealpha($destination, true);
        }

        // Resize
        imagecopyresampled($destination, $source, 0, 0, 0, 0, $newWidth, $newHeight, $width, $height);

        // Save
        $result = match ($type) {
            IMAGETYPE_JPEG => imagejpeg($destination, $destinationPath, $quality),
            IMAGETYPE_PNG => imagepng($destination, $destinationPath, 9),
            IMAGETYPE_GIF => imagegif($destination, $destinationPath),
            IMAGETYPE_WEBP => imagewebp($destination, $destinationPath, $quality),
            default => false
        };

        imagedestroy($source);
        imagedestroy($destination);

        return $result;
    }
}