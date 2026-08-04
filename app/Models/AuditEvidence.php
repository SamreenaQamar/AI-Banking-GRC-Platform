<?php

declare(strict_types=1);

namespace App\Models;

use PDO;
use PDOException;
use DateTime;
use App\Helpers\Logger;
use App\Helpers\FileUploader;
use App\Exceptions\ModelException;

/**
 * AuditEvidence Model
 * 
 * Manages audit evidence files with secure file handling,
 * verification workflows, and banking compliance standards.
 */
class AuditEvidence
{
    /**
     * @var PDO Database connection instance
     */
    private PDO $db;

    /**
     * @var string Table name
     */
    private string $table = 'audit_evidence';

    /**
     * @var array Allowed statuses
     */
    private array $allowedStatuses = ['pending', 'verified', 'approved', 'rejected'];

    /**
     * @var array Allowed file types
     */
    private array $allowedFileTypes = [
        'pdf', 'doc', 'docx', 'xls', 'xlsx', 
        'jpg', 'jpeg', 'png', 'txt', 'zip'
    ];

    /**
     * @var int Maximum file size in bytes (10MB)
     */
    private int $maxFileSize = 10485760;

    /**
     * @var FileUploader File upload helper
     */
    private FileUploader $fileUploader;

    /**
     * Constructor
     * 
     * @param PDO $db Database connection
     */
    public function __construct(PDO $db)
    {
        $this->db = $db;
        $this->fileUploader = new FileUploader([
            'upload_dir' => $_ENV['UPLOAD_DIR'] ?? '/var/www/uploads/audit_evidence/',
            'max_size' => $this->maxFileSize,
            'allowed_types' => $this->allowedFileTypes
        ]);
    }

    /**
     * Upload new audit evidence
     * 
     * @param array $file Uploaded file data
     * @param array $data Evidence metadata
     * @return array Created evidence record
     * @throws ModelException
     */
    public function upload(array $file, array $data): array
    {
        try {
            $this->validateUploadData($file, $data);

            $uploadedFile = $this->fileUploader->upload($file);

            $sql = "INSERT INTO {$this->table} (
                audit_id,
                title,
                description,
                file_name,
                file_path,
                file_type,
                uploaded_by,
                uploaded_at,
                status,
                remarks,
                created_at,
                updated_at
            ) VALUES (
                :audit_id,
                :title,
                :description,
                :file_name,
                :file_path,
                :file_type,
                :uploaded_by,
                NOW(),
                :status,
                :remarks,
                NOW(),
                NOW()
            )";

            $stmt = $this->db->prepare($sql);
            
            $stmt->execute([
                ':audit_id' => $data['audit_id'],
                ':title' => $data['title'],
                ':description' => $data['description'] ?? '',
                ':file_name' => $uploadedFile['original_name'],
                ':file_path' => $uploadedFile['saved_path'],
                ':file_type' => $uploadedFile['file_type'],
                ':uploaded_by' => $data['uploaded_by'] ?? 1,
                ':status' => $data['status'] ?? 'pending',
                ':remarks' => $data['remarks'] ?? null
            ]);

            $id = (int) $this->db->lastInsertId();
            
            Logger::info("Audit evidence uploaded", [
                'id' => $id, 
                'audit_id' => $data['audit_id'],
                'file_name' => $uploadedFile['original_name']
            ]);
            
            return $this->findById($id);

        } catch (PDOException $e) {
            Logger::error("Failed to upload audit evidence", ['error' => $e->getMessage()]);
            throw new ModelException("Failed to upload audit evidence: " . $e->getMessage(), 500, $e);
        }
    }

    /**
     * Update evidence metadata
     * 
     * @param int $id Evidence ID
     * @param array $data Updated data
     * @return array Updated evidence
     * @throws ModelException
     */
    public function update(int $id, array $data): array
    {
        try {
            $existing = $this->findById($id);
            
            $this->validateUpdateData($data);

            $allowedFields = ['title', 'description', 'remarks', 'status', 'file_name', 'file_path'];
            $updates = [];
            $params = [':id' => $id];

            foreach ($allowedFields as $field) {
                if (array_key_exists($field, $data)) {
                    $updates[] = "{$field} = :{$field}";
                    $params[":{$field}"] = $data[$field];
                }
            }

            if (empty($updates)) {
                return $existing;
            }

            $updates[] = "updated_at = NOW()";
            
            $sql = "UPDATE {$this->table} SET " . implode(', ', $updates) . " WHERE id = :id";
            
            $stmt = $this->db->prepare($sql);
            $stmt->execute($params);

            Logger::info("Audit evidence updated", ['id' => $id]);

            return $this->findById($id);

        } catch (PDOException $e) {
            Logger::error("Failed to update audit evidence", ['id' => $id, 'error' => $e->getMessage()]);
            throw new ModelException("Failed to update audit evidence: " . $e->getMessage(), 500, $e);
        }
    }

    /**
     * Delete evidence (including physical file)
     * 
     * @param int $id Evidence ID
     * @param bool $deleteFile Whether to delete the physical file
     * @return bool Success status
     * @throws ModelException
     */
    public function delete(int $id, bool $deleteFile = true): bool
    {
        try {
            $evidence = $this->findById($id);

            if ($deleteFile && !empty($evidence['file_path'])) {
                $this->fileUploader->deleteFile($evidence['file_path']);
            }

            $sql = "DELETE FROM {$this->table} WHERE id = :id";
            $stmt = $this->db->prepare($sql);
            $result = $stmt->execute([':id' => $id]);

            Logger::info("Audit evidence deleted", ['id' => $id]);

            return $result;

        } catch (PDOException $e) {
            Logger::error("Failed to delete audit evidence", ['id' => $id, 'error' => $e->getMessage()]);
            throw new ModelException("Failed to delete audit evidence: " . $e->getMessage(), 500, $e);
        }
    }

    /**
     * Find evidence by ID
     * 
     * @param int $id Evidence ID
     * @return array Evidence data
     * @throws ModelException
     */
    public function findById(int $id): array
    {
        try {
            $sql = "SELECT * FROM {$this->table} WHERE id = :id";
            $stmt = $this->db->prepare($sql);
            $stmt->execute([':id' => $id]);

            $result = $stmt->fetch(PDO::FETCH_ASSOC);

            if (!$result) {
                throw new ModelException("Audit evidence not found with ID: {$id}", 404);
            }

            return $result;

        } catch (PDOException $e) {
            Logger::error("Failed to find audit evidence", ['id' => $id, 'error' => $e->getMessage()]);
            throw new ModelException("Failed to find audit evidence: " . $e->getMessage(), 500, $e);
        }
    }

    /**
     * Find evidence by criteria
     * 
     * @param array $criteria Search criteria
     * @return array|null Evidence data or null
     */
    public function find(array $criteria): ?array
    {
        try {
            $where = [];
            $params = [];

            foreach ($criteria as $key => $value) {
                $where[] = "{$key} = :{$key}";
                $params[":{$key}"] = $value;
            }

            $sql = "SELECT * FROM {$this->table} WHERE " . implode(' AND ', $where) . " LIMIT 1";
            $stmt = $this->db->prepare($sql);
            $stmt->execute($params);

            $result = $stmt->fetch(PDO::FETCH_ASSOC);
            return $result ?: null;

        } catch (PDOException $e) {
            Logger::error("Failed to find audit evidence", ['error' => $e->getMessage()]);
            return null;
        }
    }

    /**
     * Find evidence by audit ID
     * 
     * @param int $auditId Audit ID
     * @return array List of evidence
     */
    public function findByAudit(int $auditId): array
    {
        try {
            $sql = "SELECT * FROM {$this->table} 
                    WHERE audit_id = :audit_id 
                    ORDER BY uploaded_at DESC";
            
            $stmt = $this->db->prepare($sql);
            $stmt->execute([':audit_id' => $auditId]);
            
            return $stmt->fetchAll(PDO::FETCH_ASSOC);

        } catch (PDOException $e) {
            Logger::error("Failed to find evidence by audit", ['audit_id' => $auditId, 'error' => $e->getMessage()]);
            return [];
        }
    }

    /**
     * Download evidence file
     * 
     * @param int $id Evidence ID
     * @return array File data with path and metadata
     * @throws ModelException
     */
    public function download(int $id): array
    {
        try {
            $evidence = $this->findById($id);

            if (empty($evidence['file_path']) || !file_exists($evidence['file_path'])) {
                throw new ModelException("File not found", 404);
            }

            Logger::info("Audit evidence downloaded", ['id' => $id]);

            return [
                'file_path' => $evidence['file_path'],
                'file_name' => $evidence['file_name'],
                'file_type' => $evidence['file_type'],
                'evidence' => $evidence
            ];

        } catch (PDOException $e) {
            Logger::error("Failed to download audit evidence", ['id' => $id, 'error' => $e->getMessage()]);
            throw new ModelException("Failed to download audit evidence: " . $e->getMessage(), 500, $e);
        }
    }

    /**
     * Search evidence with filters
     * 
     * @param array $filters Search filters
     * @param array $options Pagination and sorting options
     * @return array Search results
     */
    public function search(array $filters = [], array $options = []): array
    {
        try {
            $where = ['1 = 1'];
            $params = [];

            if (!empty($filters['title'])) {
                $where[] = "title LIKE :title";
                $params[':title'] = '%' . $filters['title'] . '%';
            }

            if (!empty($filters['audit_id'])) {
                $where[] = "audit_id = :audit_id";
                $params[':audit_id'] = $filters['audit_id'];
            }

            if (!empty($filters['status'])) {
                $where[] = "status = :status";
                $params[':status'] = $filters['status'];
            }

            if (!empty($filters['uploaded_by'])) {
                $where[] = "uploaded_by = :uploaded_by";
                $params[':uploaded_by'] = $filters['uploaded_by'];
            }

            if (!empty($filters['file_type'])) {
                $where[] = "file_type = :file_type";
                $params[':file_type'] = $filters['file_type'];
            }

            if (!empty($filters['date_from'])) {
                $where[] = "uploaded_at >= :date_from";
                $params[':date_from'] = $filters['date_from'];
            }

            if (!empty($filters['date_to'])) {
                $where[] = "uploaded_at <= :date_to";
                $params[':date_to'] = $filters['date_to'];
            }

            $orderBy = $options['order_by'] ?? 'uploaded_at';
            $orderDir = $options['order_dir'] ?? 'DESC';
            $limit = $options['limit'] ?? 20;
            $offset = $options['offset'] ?? 0;

            $sql = "SELECT * FROM {$this->table} 
                    WHERE " . implode(' AND ', $where) . "
                    ORDER BY {$orderBy} {$orderDir}
                    LIMIT :limit OFFSET :offset";

            $stmt = $this->db->prepare($sql);
            
            foreach ($params as $key => $value) {
                $stmt->bindValue($key, $value);
            }
            
            $stmt->bindValue(':limit', (int) $limit, PDO::PARAM_INT);
            $stmt->bindValue(':offset', (int) $offset, PDO::PARAM_INT);
            
            $stmt->execute();
            
            return $stmt->fetchAll(PDO::FETCH_ASSOC);

        } catch (PDOException $e) {
            Logger::error("Failed to search audit evidence", ['error' => $e->getMessage()]);
            return [];
        }
    }

    /**
     * Verify evidence
     * 
     * @param int $id Evidence ID
     * @param int $userId User ID verifying
     * @param string $remarks Verification remarks
     * @return array Updated evidence
     * @throws ModelException
     */
    public function verify(int $id, int $userId, string $remarks = ''): array
    {
        return $this->update($id, [
            'status' => 'verified',
            'remarks' => $remarks,
            'updated_by' => $userId
        ]);
    }

    /**
     * Approve evidence
     * 
     * @param int $id Evidence ID
     * @param int $userId User ID approving
     * @param string $remarks Approval remarks
     * @return array Updated evidence
     * @throws ModelException
     */
    public function approve(int $id, int $userId, string $remarks = ''): array
    {
        return $this->update($id, [
            'status' => 'approved',
            'remarks' => $remarks,
            'updated_by' => $userId
        ]);
    }

    /**
     * Reject evidence
     * 
     * @param int $id Evidence ID
     * @param int $userId User ID rejecting
     * @param string $remarks Rejection remarks (required)
     * @return array Updated evidence
     * @throws ModelException
     */
    public function reject(int $id, int $userId, string $remarks): array
    {
        if (empty($remarks)) {
            throw new ModelException("Rejection remarks are required", 400);
        }

        return $this->update($id, [
            'status' => 'rejected',
            'remarks' => $remarks,
            'updated_by' => $userId
        ]);
    }

    /**
     * Count evidence
     * 
     * @param array $filters Filters to apply
     * @return int Total count
     */
    public function count(array $filters = []): int
    {
        try {
            $where = ['1 = 1'];
            $params = [];

            if (!empty($filters['audit_id'])) {
                $where[] = "audit_id = :audit_id";
                $params[':audit_id'] = $filters['audit_id'];
            }

            if (!empty($filters['status'])) {
                $where[] = "status = :status";
                $params[':status'] = $filters['status'];
            }

            if (!empty($filters['uploaded_by'])) {
                $where[] = "uploaded_by = :uploaded_by";
                $params[':uploaded_by'] = $filters['uploaded_by'];
            }

            $sql = "SELECT COUNT(*) as total FROM {$this->table} WHERE " . implode(' AND ', $where);
            $stmt = $this->db->prepare($sql);
            $stmt->execute($params);
            
            $result = $stmt->fetch(PDO::FETCH_ASSOC);
            return (int) ($result['total'] ?? 0);

        } catch (PDOException $e) {
            Logger::error("Failed to count audit evidence", ['error' => $e->getMessage()]);
            return 0;
        }
    }

    /**
     * Validate upload data
     * 
     * @param array $file File data
     * @param array $data Metadata
     * @throws ModelException
     */
    private function validateUploadData(array $file, array $data): void
    {
        if (empty($data['audit_id'])) {
            throw new ModelException("Audit ID is required", 400);
        }

        if (empty($data['title'])) {
            throw new ModelException("Title is required", 400);
        }

        if (empty($file['tmp_name']) || empty($file['name'])) {
            throw new ModelException("No file uploaded", 400);
        }

        if ($file['size'] > $this->maxFileSize) {
            throw new ModelException("File size exceeds maximum limit of 10MB", 400);
        }

        $fileExtension = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
        if (!in_array($fileExtension, $this->allowedFileTypes)) {
            throw new ModelException("File type not allowed: {$fileExtension}", 400);
        }

        if (!empty($data['status']) && !in_array($data['status'], $this->allowedStatuses)) {
            throw new ModelException("Invalid status value", 400);
        }
    }

    /**
     * Validate update data
     * 
     * @param array $data Data to validate
     * @throws ModelException
     */
    private function validateUpdateData(array $data): void
    {
        if (!empty($data['status']) && !in_array($data['status'], $this->allowedStatuses)) {
            throw new ModelException("Invalid status value", 400);
        }
    }
}