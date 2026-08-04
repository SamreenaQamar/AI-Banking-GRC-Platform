<?php
/**
 * AI Banking GRC Platform - File Helper
 * 
 * @package    AI-Banking-GRC-Platform
 * @subpackage app/Helpers
 * @version    1.0.0
 * @author     GRC Platform Team
 * @copyright  2026 AI Banking GRC Platform
 * @license    Proprietary
 * 
 * This helper provides file system functionality:
 * - Directory management
 * - File operations
 * - File information
 * - File upload handling
 */

declare(strict_types=1);

namespace App\Helpers;

class FileHelper
{
    /**
     * Create directory recursively
     * 
     * @param string $path
     * @param int $permissions
     * @return bool
     */
    public static function createDirectory(string $path, int $permissions = 0755): bool
    {
        if (is_dir($path)) {
            return true;
        }

        return mkdir($path, $permissions, true);
    }

    /**
     * Delete directory recursively
     * 
     * @param string $path
     * @return bool
     */
    public static function deleteDirectory(string $path): bool
    {
        if (!is_dir($path)) {
            return false;
        }

        $files = array_diff(scandir($path), ['.', '..']);
        foreach ($files as $file) {
            $filePath = $path . DIRECTORY_SEPARATOR . $file;
            if (is_dir($filePath)) {
                self::deleteDirectory($filePath);
            } else {
                unlink($filePath);
            }
        }

        return rmdir($path);
    }

    /**
     * Delete file
     * 
     * @param string $path
     * @return bool
     */
    public static function deleteFile(string $path): bool
    {
        if (!file_exists($path)) {
            return false;
        }

        return unlink($path);
    }

    /**
     * Upload file
     * 
     * @param array $file
     * @param string $destination
     * @param string|null $filename
     * @return string|false
     */
    public static function uploadFile(array $file, string $destination, ?string $filename = null): string|false
    {
        if (!isset($file['error']) || $file['error'] !== UPLOAD_ERR_OK) {
            return false;
        }

        if (!is_uploaded_file($file['tmp_name'])) {
            return false;
        }

        // Create destination directory if not exists
        if (!self::createDirectory($destination)) {
            return false;
        }

        // Generate filename if not provided
        if ($filename === null) {
            $extension = pathinfo($file['name'], PATHINFO_EXTENSION);
            $filename = uniqid() . '.' . $extension;
        }

        // Clean filename
        $filename = SecurityHelper::cleanFilename($filename);
        $targetPath = rtrim($destination, DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR . $filename;

        if (move_uploaded_file($file['tmp_name'], $targetPath)) {
            return $filename;
        }

        return false;
    }

    /**
     * Get file size in bytes
     * 
     * @param string $path
     * @return int|false
     */
    public static function fileSize(string $path): int|false
    {
        if (!file_exists($path)) {
            return false;
        }

        return filesize($path);
    }

    /**
     * Get file extension
     * 
     * @param string $path
     * @return string
     */
    public static function fileExtension(string $path): string
    {
        return strtolower(pathinfo($path, PATHINFO_EXTENSION));
    }

    /**
     * Get file name without extension
     * 
     * @param string $path
     * @return string
     */
    public static function fileName(string $path): string
    {
        return pathinfo($path, PATHINFO_FILENAME);
    }

    /**
     * Get MIME type
     * 
     * @param string $path
     * @return string|false
     */
    public static function mimeType(string $path): string|false
    {
        if (!file_exists($path)) {
            return false;
        }

        return mime_content_type($path);
    }

    /**
     * Copy file
     * 
     * @param string $source
     * @param string $destination
     * @return bool
     */
    public static function copyFile(string $source, string $destination): bool
    {
        if (!file_exists($source)) {
            return false;
        }

        $destinationDir = dirname($destination);
        if (!self::createDirectory($destinationDir)) {
            return false;
        }

        return copy($source, $destination);
    }

    /**
     * Move file
     * 
     * @param string $source
     * @param string $destination
     * @return bool
     */
    public static function moveFile(string $source, string $destination): bool
    {
        if (!file_exists($source)) {
            return false;
        }

        $destinationDir = dirname($destination);
        if (!self::createDirectory($destinationDir)) {
            return false;
        }

        return rename($source, $destination);
    }

    /**
     * Read file contents
     * 
     * @param string $path
     * @return string|false
     */
    public static function readFile(string $path): string|false
    {
        if (!file_exists($path) || !is_readable($path)) {
            return false;
        }

        return file_get_contents($path);
    }

    /**
     * Write file contents
     * 
     * @param string $path
     * @param string $content
     * @param int $flags
     * @return bool
     */
    public static function writeFile(string $path, string $content, int $flags = 0): bool
    {
        $directory = dirname($path);
        if (!self::createDirectory($directory)) {
            return false;
        }

        return file_put_contents($path, $content, $flags) !== false;
    }

    /**
     * Append to file
     * 
     * @param string $path
     * @param string $content
     * @return bool
     */
    public static function appendFile(string $path, string $content): bool
    {
        return self::writeFile($path, $content, FILE_APPEND);
    }

    /**
     * Check if file exists
     * 
     * @param string $path
     * @return bool
     */
    public static function exists(string $path): bool
    {
        return file_exists($path);
    }

    /**
     * Get file last modified time
     * 
     * @param string $path
     * @return int|false
     */
    public static function lastModified(string $path): int|false
    {
        if (!file_exists($path)) {
            return false;
        }

        return filemtime($path);
    }

    /**
     * Get files in directory
     * 
     * @param string $path
     * @param array $extensions
     * @param bool $recursive
     * @return array
     */
    public static function getFiles(string $path, array $extensions = [], bool $recursive = false): array
    {
        if (!is_dir($path)) {
            return [];
        }

        $files = [];
        $iterator = $recursive ? new \RecursiveIteratorIterator(
            new \RecursiveDirectoryIterator($path, \RecursiveDirectoryIterator::SKIP_DOTS)
        ) : new \DirectoryIterator($path);

        foreach ($iterator as $file) {
            if ($file->isFile()) {
                $extension = $file->getExtension();
                if (empty($extensions) || in_array($extension, $extensions)) {
                    $files[] = $file->getPathname();
                }
            }
        }

        return $files;
    }

    /**
     * Get directory size
     * 
     * @param string $path
     * @return int
     */
    public static function directorySize(string $path): int
    {
        if (!is_dir($path)) {
            return 0;
        }

        $size = 0;
        $files = new \RecursiveIteratorIterator(
            new \RecursiveDirectoryIterator($path, \RecursiveDirectoryIterator::SKIP_DOTS)
        );

        foreach ($files as $file) {
            if ($file->isFile()) {
                $size += $file->getSize();
            }
        }

        return $size;
    }

    /**
     * Check if path is writable
     * 
     * @param string $path
     * @return bool
     */
    public static function isWritable(string $path): bool
    {
        if (file_exists($path)) {
            return is_writable($path);
        }

        $dir = dirname($path);
        return is_writable($dir);
    }

    /**
     * Get safe filename
     * 
     * @param string $filename
     * @param string $replacement
     * @return string
     */
    public static function safeFilename(string $filename, string $replacement = '-'): string
    {
        // Remove any path information
        $filename = basename($filename);
        
        // Convert to lowercase
        $filename = strtolower($filename);
        
        // Replace spaces and special characters
        $filename = preg_replace('/[^a-z0-9.-]/', $replacement, $filename);
        
        // Remove multiple separators
        $filename = preg_replace('/' . preg_quote($replacement, '/') . '+/', $replacement, $filename);
        
        // Remove leading/trailing separators
        $filename = trim($filename, $replacement);

        return $filename;
    }

    /**
     * Get unique filename
     * 
     * @param string $path
     * @param string $filename
     * @return string
     */
    public static function uniqueFilename(string $path, string $filename): string
    {
        $extension = pathinfo($filename, PATHINFO_EXTENSION);
        $basename = pathinfo($filename, PATHINFO_FILENAME);
        
        $counter = 1;
        $newFilename = $filename;
        
        while (file_exists($path . DIRECTORY_SEPARATOR . $newFilename)) {
            $newFilename = $basename . '_' . $counter . '.' . $extension;
            $counter++;
        }
        
        return $newFilename;
    }

    /**
     * Get file permission in octal
     * 
     * @param string $path
     * @return int|false
     */
    public static function getPermissions(string $path): int|false
    {
        if (!file_exists($path)) {
            return false;
        }

        return fileperms($path) & 0777;
    }

    /**
     * Set file permissions
     * 
     * @param string $path
     * @param int $permissions
     * @return bool
     */
    public static function setPermissions(string $path, int $permissions): bool
    {
        if (!file_exists($path)) {
            return false;
        }

        return chmod($path, $permissions);
    }

    /**
     * Get file info
     * 
     * @param string $path
     * @return array
     */
    public static function getFileInfo(string $path): array
    {
        if (!file_exists($path)) {
            return [];
        }

        return [
            'name' => basename($path),
            'path' => $path,
            'size' => filesize($path),
            'extension' => self::fileExtension($path),
            'mime_type' => self::mimeType($path),
            'last_modified' => filemtime($path),
            'permissions' => self::getPermissions($path),
            'is_readable' => is_readable($path),
            'is_writable' => is_writable($path)
        ];
    }
}