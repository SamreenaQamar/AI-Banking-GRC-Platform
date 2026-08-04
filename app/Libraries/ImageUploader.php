<?php
/**
 * AI Banking GRC Platform - Image Uploader Library
 * 
 * @package    AI-Banking-GRC-Platform
 * @subpackage app/Libraries
 * @version    1.0.0
 * @author     GRC Platform Team
 * @copyright  2026 AI Banking GRC Platform
 * @license    Proprietary
 * 
 * This library provides enterprise image uploading functionality:
 * - Image upload with validation
 * - Image resize
 * - Image compression
 * - Image cropping
 * - Thumbnail generation
 * - WebP conversion
 * - Delete images
 * - Secure file naming
 */

declare(strict_types=1);

namespace App\Libraries;

use App\Libraries\Logger;

class ImageUploader
{
    /**
     * @var Logger Logger instance
     */
    private Logger $logger;

    /**
     * @var string Upload directory
     */
    private string $uploadDir;

    /**
     * @var array Allowed MIME types
     */
    private array $allowedMimes = [
        'image/jpeg',
        'image/png',
        'image/gif',
        'image/webp',
        'image/svg+xml'
    ];

    /**
     * @var int Maximum file size (bytes)
     */
    private int $maxSize = 5242880; // 5MB

    /**
     * @var int Quality for JPEG/WebP (0-100)
     */
    private int $quality = 85;

    /**
     * @var int Default width
     */
    private int $defaultWidth = 1200;

    /**
     * @var int Default height
     */
    private int $defaultHeight = 1200;

    /**
     * Constructor
     */
    public function __construct()
    {
        $this->logger = new Logger();
        $this->uploadDir = UPLOADS_PATH . '/images/';

        // Create upload directory if not exists
        if (!is_dir($this->uploadDir)) {
            mkdir($this->uploadDir, 0755, true);
        }
    }

    /**
     * Upload image
     * 
     * @param array $file
     * @param array $options
     * @return array
     */
    public function upload(array $file, array $options = []): array
    {
        try {
            // Validate file
            $this->validateFile($file);

            // Generate filename
            $filename = $this->generateFilename($file, $options);

            // Move uploaded file
            $targetPath = $this->uploadDir . $filename;
            move_uploaded_file($file['tmp_name'], $targetPath);

            // Process image
            if (isset($options['resize']) || isset($options['thumbnail'])) {
                $this->processImage($targetPath, $options);
            }

            $this->logger->info('Image uploaded', [
                'filename' => $filename,
                'size' => $file['size']
            ]);

            return [
                'success' => true,
                'filename' => $filename,
                'path' => $targetPath,
                'url' => UPLOADS_URL . '/images/' . $filename,
                'size' => $file['size']
            ];

        } catch (\Exception $e) {
            $this->logger->error('Image upload error: ' . $e->getMessage());
            return [
                'success' => false,
                'error' => $e->getMessage()
            ];
        }
    }

    /**
     * Validate uploaded file
     * 
     * @param array $file
     * @return void
     * @throws \RuntimeException
     */
    private function validateFile(array $file): void
    {
        // Check if file was uploaded
        if (!isset($file['error']) || $file['error'] !== UPLOAD_ERR_OK) {
            throw new \RuntimeException('File upload error: ' . $this->getUploadErrorMessage($file['error'] ?? 0));
        }

        // Check file size
        if ($file['size'] > $this->maxSize) {
            throw new \RuntimeException('File size exceeds maximum allowed (' . ($this->maxSize / 1048576) . 'MB)');
        }

        // Check MIME type
        $finfo = finfo_open(FILEINFO_MIME_TYPE);
        $mimeType = finfo_file($finfo, $file['tmp_name']);
        finfo_close($finfo);

        if (!in_array($mimeType, $this->allowedMimes)) {
            throw new \RuntimeException('File type not allowed. Allowed: ' . implode(', ', $this->allowedMimes));
        }

        // Check for PHP image
        $imageInfo = getimagesize($file['tmp_name']);
        if ($imageInfo === false) {
            throw new \RuntimeException('Invalid image file');
        }
    }

    /**
     * Generate filename
     * 
     * @param array $file
     * @param array $options
     * @return string
     */
    private function generateFilename(array $file, array $options = []): string
    {
        $extension = pathinfo($file['name'], PATHINFO_EXTENSION);
        $extension = strtolower($extension);

        // Convert to WebP if requested
        if (isset($options['webp']) && $options['webp'] && function_exists('imagewebp')) {
            $extension = 'webp';
        }

        $prefix = $options['prefix'] ?? 'img';
        $name = $options['name'] ?? uniqid($prefix . '_');
        $name = $this->sanitizeFilename($name);

        return $name . '.' . $extension;
    }

    /**
     * Sanitize filename
     * 
     * @param string $filename
     * @return string
     */
    private function sanitizeFilename(string $filename): string
    {
        $filename = preg_replace('/[^a-zA-Z0-9_-]/', '', $filename);
        return substr($filename, 0, 50);
    }

    /**
     * Get upload error message
     * 
     * @param int $errorCode
     * @return string
     */
    private function getUploadErrorMessage(int $errorCode): string
    {
        $messages = [
            UPLOAD_ERR_INI_SIZE => 'File exceeds upload_max_filesize directive.',
            UPLOAD_ERR_FORM_SIZE => 'File exceeds MAX_FILE_SIZE directive.',
            UPLOAD_ERR_PARTIAL => 'File was only partially uploaded.',
            UPLOAD_ERR_NO_FILE => 'No file was uploaded.',
            UPLOAD_ERR_NO_TMP_DIR => 'Missing temporary folder.',
            UPLOAD_ERR_CANT_WRITE => 'Failed to write file to disk.',
            UPLOAD_ERR_EXTENSION => 'File upload stopped by extension.'
        ];

        return $messages[$errorCode] ?? 'Unknown upload error.';
    }

    /**
     * Process image (resize, thumbnail)
     * 
     * @param string $path
     * @param array $options
     * @return void
     */
    private function processImage(string $path, array $options = []): void
    {
        $imageInfo = getimagesize($path);
        if (!$imageInfo) {
            return;
        }

        list($width, $height, $type) = $imageInfo;

        // Create image resource
        $source = $this->createImageResource($path, $type);
        if (!$source) {
            return;
        }

        // Resize
        if (isset($options['resize'])) {
            $resizeOptions = $options['resize'];
            $newWidth = $resizeOptions['width'] ?? $this->defaultWidth;
            $newHeight = $resizeOptions['height'] ?? $this->defaultHeight;
            $maintainAspect = $resizeOptions['maintain_aspect'] ?? true;

            if ($maintainAspect) {
                $ratio = min($newWidth / $width, $newHeight / $height);
                $newWidth = (int)($width * $ratio);
                $newHeight = (int)($height * $ratio);
            }

            $source = $this->resizeImage($source, $width, $height, $newWidth, $newHeight);
            $width = $newWidth;
            $height = $newHeight;
        }

        // Create thumbnail
        if (isset($options['thumbnail'])) {
            $thumbOptions = $options['thumbnail'];
            $thumbWidth = $thumbOptions['width'] ?? 150;
            $thumbHeight = $thumbOptions['height'] ?? 150;
            $thumbPath = dirname($path) . '/thumb_' . basename($path);

            $thumb = $this->resizeImage($source, $width, $height, $thumbWidth, $thumbHeight, true);
            $this->saveImage($thumb, $thumbPath, $type, 70);
            imagedestroy($thumb);
        }

        // Save image
        $this->saveImage($source, $path, $type, $this->quality);
        imagedestroy($source);
    }

    /**
     * Create image resource from file
     * 
     * @param string $path
     * @param int $type
     * @return resource|false
     */
    private function createImageResource(string $path, int $type)
    {
        return match($type) {
            IMAGETYPE_JPEG => imagecreatefromjpeg($path),
            IMAGETYPE_PNG => imagecreatefrompng($path),
            IMAGETYPE_GIF => imagecreatefromgif($path),
            IMAGETYPE_WEBP => function_exists('imagecreatefromwebp') ? imagecreatefromwebp($path) : false,
            default => false
        };
    }

    /**
     * Resize image
     * 
     * @param resource $source
     * @param int $width
     * @param int $height
     * @param int $newWidth
     * @param int $newHeight
     * @param bool $crop
     * @return resource
     */
    private function resizeImage($source, int $width, int $height, int $newWidth, int $newHeight, bool $crop = false)
    {
        $result = imagecreatetruecolor($newWidth, $newHeight);

        // Preserve transparency for PNG
        imagealphablending($result, false);
        imagesavealpha($result, true);

        if ($crop) {
            // Crop image
            $cropX = 0;
            $cropY = 0;
            $aspect = $width / $height;
            $newAspect = $newWidth / $newHeight;

            if ($aspect > $newAspect) {
                $cropWidth = (int)($height * $newAspect);
                $cropHeight = $height;
                $cropX = (int)(($width - $cropWidth) / 2);
            } else {
                $cropWidth = $width;
                $cropHeight = (int)($width / $newAspect);
                $cropY = (int)(($height - $cropHeight) / 2);
            }

            imagecopyresampled(
                $result, $source,
                0, 0,
                $cropX, $cropY,
                $newWidth, $newHeight,
                $cropWidth, $cropHeight
            );
        } else {
            imagecopyresampled(
                $result, $source,
                0, 0,
                0, 0,
                $newWidth, $newHeight,
                $width, $height
            );
        }

        return $result;
    }

    /**
     * Save image
     * 
     * @param resource $image
     * @param string $path
     * @param int $type
     * @param int $quality
     * @return void
     */
    private function saveImage($image, string $path, int $type, int $quality): void
    {
        $extension = strtolower(pathinfo($path, PATHINFO_EXTENSION));

        if ($extension === 'webp' && function_exists('imagewebp')) {
            imagewebp($image, $path, $quality);
        } else {
            match($type) {
                IMAGETYPE_JPEG => imagejpeg($image, $path, $quality),
                IMAGETYPE_PNG => imagepng($image, $path, 9),
                IMAGETYPE_GIF => imagegif($image, $path),
                default => imagejpeg($image, $path, $quality)
            };
        }
    }

    /**
     * Delete image
     * 
     * @param string $filename
     * @return bool
     */
    public function delete(string $filename): bool
    {
        $path = $this->uploadDir . $filename;
        if (!file_exists($path)) {
            return false;
        }

        // Delete thumbnail if exists
        $thumbPath = dirname($path) . '/thumb_' . basename($path);
        if (file_exists($thumbPath)) {
            unlink($thumbPath);
        }

        $result = unlink($path);

        if ($result) {
            $this->logger->info('Image deleted', ['filename' => $filename]);
        }

        return $result;
    }

    /**
     * Set upload directory
     * 
     * @param string $dir
     * @return void
     */
    public function setUploadDir(string $dir): void
    {
        $this->uploadDir = rtrim($dir, '/') . '/';
        if (!is_dir($this->uploadDir)) {
            mkdir($this->uploadDir, 0755, true);
        }
    }

    /**
     * Set allowed MIME types
     * 
     * @param array $mimes
     * @return void
     */
    public function setAllowedMimes(array $mimes): void
    {
        $this->allowedMimes = $mimes;
    }

    /**
     * Set max file size
     * 
     * @param int $size
     * @return void
     */
    public function setMaxSize(int $size): void
    {
        $this->maxSize = $size;
    }

    /**
     * Set image quality
     * 
     * @param int $quality
     * @return void
     */
    public function setQuality(int $quality): void
    {
        $this->quality = max(0, min(100, $quality));
    }

    /**
     * Get image info
     * 
     * @param string $filename
     * @return array|null
     */
    public function getInfo(string $filename): ?array
    {
        $path = $this->uploadDir . $filename;
        if (!file_exists($path)) {
            return null;
        }

        $imageInfo = getimagesize($path);
        if (!$imageInfo) {
            return null;
        }

        return [
            'filename' => $filename,
            'path' => $path,
            'url' => UPLOADS_URL . '/images/' . $filename,
            'width' => $imageInfo[0],
            'height' => $imageInfo[1],
            'mime' => $imageInfo['mime'],
            'size' => filesize($path),
            'modified' => filemtime($path)
        ];
    }

    /**
     * Resize existing image
     * 
     * @param string $filename
     * @param int $width
     * @param int $height
     * @param bool $maintainAspect
     * @return bool
     */
    public function resize(string $filename, int $width, int $height, bool $maintainAspect = true): bool
    {
        $path = $this->uploadDir . $filename;
        if (!file_exists($path)) {
            return false;
        }

        try {
            $this->processImage($path, [
                'resize' => [
                    'width' => $width,
                    'height' => $height,
                    'maintain_aspect' => $maintainAspect
                ]
            ]);
            return true;
        } catch (\Exception $e) {
            $this->logger->error('Image resize error: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Compress image
     * 
     * @param string $filename
     * @param int $quality
     * @return bool
     */
    public function compress(string $filename, int $quality = 70): bool
    {
        $path = $this->uploadDir . $filename;
        if (!file_exists($path)) {
            return false;
        }

        try {
            $this->setQuality($quality);
            $imageInfo = getimagesize($path);
            if (!$imageInfo) {
                return false;
            }

            $source = $this->createImageResource($path, $imageInfo[2]);
            if (!$source) {
                return false;
            }

            $this->saveImage($source, $path, $imageInfo[2], $quality);
            imagedestroy($source);

            return true;

        } catch (\Exception $e) {
            $this->logger->error('Image compress error: ' . $e->getMessage());
            return false;
        }
    }
}