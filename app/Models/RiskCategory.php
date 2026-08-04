<?php

declare(strict_types=1);

namespace App\Models;

use PDO;
use PDOException;
use App\Helpers\Logger;
use App\Exceptions\ModelException;

/**
 * RiskCategory Model
 * 
 * Manages risk categories with status tracking,
 * color coding, and comprehensive search capabilities.
 */
class RiskCategory
{
    /**
     * @var PDO Database connection instance
     */
    private PDO $db;

    /**
     * @var string Table name
     */
    private string $table = 'risk_categories';

    /**
     * @var array Allowed statuses
     */
    private array $allowedStatuses = ['active', 'inactive', 'archived'];

    /**
     * Constructor
     * 
     * @param PDO $db Database connection
     */
    public function __construct(PDO $db)
    {
        $this->db = $db;
    }

    /**
     * Create a new risk category
     * 
     * @param array $data Category data
     * @return array Created category
     * @throws ModelException
     */
    public function create(array $data): array
    {
        try {
            $this->validateCreateData($data);

            // Check for duplicate category name
            if ($this->isDuplicateName($data['category_name'])) {
                throw new ModelException("Category name already exists", 409);
            }

            $sql = "INSERT INTO {$this->table} (
                category_name,
                description,
                color,
                status,
                created_at,
                updated_at
            ) VALUES (
                :category_name,
                :description,
                :color,
                :status,
                NOW(),
                NOW()
            )";

            $stmt = $this->db->prepare($sql);
            
            $stmt->execute([
                ':category_name' => $data['category_name'],
                ':description' => $data['description'] ?? '',
                ':color' => $data['color'] ?? '#000000',
                ':status' => $data['status'] ?? 'active'
            ]);

            $id = (int) $this->db->lastInsertId();
            
            Logger::info("Risk category created", ['id' => $id, 'name' => $data['category_name']]);
            
            return $this->findById($id);

        } catch (PDOException $e) {
            Logger::error("Failed to create risk category", ['error' => $e->getMessage()]);
            throw new ModelException("Failed to create risk category: " . $e->getMessage(), 500, $e);
        }
    }

    /**
     * Update an existing risk category
     * 
     * @param int $id Category ID
     * @param array $data Updated data
     * @return array Updated category
     * @throws ModelException
     */
    public function update(int $id, array $data): array
    {
        try {
            $existing = $this->findById($id);
            
            $this->validateUpdateData($data);

            // Check for duplicate name if name is being changed
            if (!empty($data['category_name']) && 
                $data['category_name'] !== $existing['category_name'] &&
                $this->isDuplicateName($data['category_name'], $id)) {
                throw new ModelException("Category name already exists", 409);
            }

            $allowedFields = ['category_name', 'description', 'color', 'status'];
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

            Logger::info("Risk category updated", ['id' => $id]);

            return $this->findById($id);

        } catch (PDOException $e) {
            Logger::error("Failed to update risk category", ['id' => $id, 'error' => $e->getMessage()]);
            throw new ModelException("Failed to update risk category: " . $e->getMessage(), 500, $e);
        }
    }

    /**
     * Delete a risk category
     * 
     * @param int $id Category ID
     * @return bool Success status
     * @throws ModelException
     */
    public function delete(int $id): bool
    {
        try {
            $this->findById($id);

            // Check if category is in use
            if ($this->isCategoryInUse($id)) {
                throw new ModelException("Category is in use and cannot be deleted", 409);
            }

            $sql = "DELETE FROM {$this->table} WHERE id = :id";
            $stmt = $this->db->prepare($sql);
            $result = $stmt->execute([':id' => $id]);

            Logger::info("Risk category deleted", ['id' => $id]);

            return $result;

        } catch (PDOException $e) {
            Logger::error("Failed to delete risk category", ['id' => $id, 'error' => $e->getMessage()]);
            throw new ModelException("Failed to delete risk category: " . $e->getMessage(), 500, $e);
        }
    }

    /**
     * Find risk category by ID
     * 
     * @param int $id Category ID
     * @return array Category data
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
                throw new ModelException("Risk category not found with ID: {$id}", 404);
            }

            return $result;

        } catch (PDOException $e) {
            Logger::error("Failed to find risk category", ['id' => $id, 'error' => $e->getMessage()]);
            throw new ModelException("Failed to find risk category: " . $e->getMessage(), 500, $e);
        }
    }

    /**
     * Find category by criteria
     * 
     * @param array $criteria Search criteria
     * @return array|null Category data or null
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
            Logger::error("Failed to find risk category", ['error' => $e->getMessage()]);
            return null;
        }
    }

    /**
     * Get all risk categories
     * 
     * @param array $options Query options (order, limit, offset)
     * @return array List of categories
     */
    public function all(array $options = []): array
    {
        try {
            $orderBy = $options['order_by'] ?? 'category_name';
            $orderDir = $options['order_dir'] ?? 'ASC';
            $limit = isset($options['limit']) ? (int) $options['limit'] : null;
            $offset = isset($options['offset']) ? (int) $options['offset'] : null;

            $sql = "SELECT * FROM {$this->table} ORDER BY {$orderBy} {$orderDir}";
            
            if ($limit !== null) {
                $sql .= " LIMIT :limit";
                if ($offset !== null) {
                    $sql .= " OFFSET :offset";
                }
            }

            $stmt = $this->db->prepare($sql);
            
            if ($limit !== null) {
                $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
                if ($offset !== null) {
                    $stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
                }
            }

            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_ASSOC);

        } catch (PDOException $e) {
            Logger::error("Failed to fetch all risk categories", ['error' => $e->getMessage()]);
            return [];
        }
    }

    /**
     * Get active categories only
     * 
     * @return array Active categories
     */
    public function active(): array
    {
        return $this->findByStatus('active');
    }

    /**
     * Get inactive categories
     * 
     * @return array Inactive categories
     */
    public function inactive(): array
    {
        return $this->findByStatus('inactive');
    }

    /**
     * Find categories by status
     * 
     * @param string $status Status to filter
     * @return array Categories
     */
    private function findByStatus(string $status): array
    {
        try {
            $sql = "SELECT * FROM {$this->table} WHERE status = :status ORDER BY category_name ASC";
            $stmt = $this->db->prepare($sql);
            $stmt->execute([':status' => $status]);
            
            return $stmt->fetchAll(PDO::FETCH_ASSOC);

        } catch (PDOException $e) {
            Logger::error("Failed to fetch categories by status", ['status' => $status, 'error' => $e->getMessage()]);
            return [];
        }
    }

    /**
     * Search categories with filters
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

            if (!empty($filters['category_name'])) {
                $where[] = "category_name LIKE :category_name";
                $params[':category_name'] = '%' . $filters['category_name'] . '%';
            }

            if (!empty($filters['description'])) {
                $where[] = "description LIKE :description";
                $params[':description'] = '%' . $filters['description'] . '%';
            }

            if (!empty($filters['status'])) {
                if (is_array($filters['status'])) {
                    $placeholders = [];
                    foreach ($filters['status'] as $index => $status) {
                        $key = ":status_{$index}";
                        $placeholders[] = $key;
                        $params[$key] = $status;
                    }
                    $where[] = "status IN (" . implode(', ', $placeholders) . ")";
                } else {
                    $where[] = "status = :status";
                    $params[':status'] = $filters['status'];
                }
            }

            if (!empty($filters['color'])) {
                $where[] = "color = :color";
                $params[':color'] = $filters['color'];
            }

            $orderBy = $options['order_by'] ?? 'category_name';
            $orderDir = $options['order_dir'] ?? 'ASC';
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
            Logger::error("Failed to search risk categories", ['error' => $e->getMessage()]);
            return [];
        }
    }

    /**
     * Count categories
     * 
     * @param array $filters Filters to apply
     * @return int Total count
     */
    public function count(array $filters = []): int
    {
        try {
            $where = ['1 = 1'];
            $params = [];

            if (!empty($filters['status'])) {
                if (is_array($filters['status'])) {
                    $placeholders = [];
                    foreach ($filters['status'] as $index => $status) {
                        $key = ":status_{$index}";
                        $placeholders[] = $key;
                        $params[$key] = $status;
                    }
                    $where[] = "status IN (" . implode(', ', $placeholders) . ")";
                } else {
                    $where[] = "status = :status";
                    $params[':status'] = $filters['status'];
                }
            }

            if (!empty($filters['category_name'])) {
                $where[] = "category_name LIKE :category_name";
                $params[':category_name'] = '%' . $filters['category_name'] . '%';
            }

            $sql = "SELECT COUNT(*) as total FROM {$this->table} WHERE " . implode(' AND ', $where);
            $stmt = $this->db->prepare($sql);
            $stmt->execute($params);
            
            $result = $stmt->fetch(PDO::FETCH_ASSOC);
            return (int) ($result['total'] ?? 0);

        } catch (PDOException $e) {
            Logger::error("Failed to count risk categories", ['error' => $e->getMessage()]);
            return 0;
        }
    }

    /**
     * Check if category name is a duplicate
     * 
     * @param string $name Category name
     * @param int|null $excludeId ID to exclude from check
     * @return bool True if duplicate exists
     */
    private function isDuplicateName(string $name, ?int $excludeId = null): bool
    {
        try {
            $sql = "SELECT COUNT(*) as count FROM {$this->table} WHERE category_name = :name";
            $params = [':name' => $name];

            if ($excludeId !== null) {
                $sql .= " AND id != :exclude_id";
                $params[':exclude_id'] = $excludeId;
            }

            $stmt = $this->db->prepare($sql);
            $stmt->execute($params);
            
            $result = $stmt->fetch(PDO::FETCH_ASSOC);
            return ($result['count'] ?? 0) > 0;

        } catch (PDOException $e) {
            Logger::error("Failed to check duplicate category name", ['name' => $name, 'error' => $e->getMessage()]);
            return true; // Assume duplicate on error
        }
    }

    /**
     * Check if category is in use
     * 
     * @param int $categoryId Category ID
     * @return bool True if category is in use
     */
    private function isCategoryInUse(int $categoryId): bool
    {
        try {
            // Check in risks table
            $sql = "SELECT COUNT(*) as count FROM risks WHERE category_id = :category_id";
            $stmt = $this->db->prepare($sql);
            $stmt->execute([':category_id' => $categoryId]);
            $result = $stmt->fetch(PDO::FETCH_ASSOC);
            
            return ($result['count'] ?? 0) > 0;

        } catch (PDOException $e) {
            Logger::error("Failed to check if category is in use", ['category_id' => $categoryId, 'error' => $e->getMessage()]);
            return true; // Assume in use on error
        }
    }

    /**
     * Validate create data
     * 
     * @param array $data Data to validate
     * @throws ModelException
     */
    private function validateCreateData(array $data): void
    {
        if (empty($data['category_name']) || strlen($data['category_name']) < 2) {
            throw new ModelException("Category name is required and must be at least 2 characters", 400);
        }

        if (strlen($data['category_name']) > 100) {
            throw new ModelException("Category name must not exceed 100 characters", 400);
        }

        if (!empty($data['status']) && !in_array($data['status'], $this->allowedStatuses)) {
            throw new ModelException("Invalid status value", 400);
        }

        // Validate color format (hex color)
        if (!empty($data['color']) && !preg_match('/^#[a-fA-F0-9]{6}$/', $data['color'])) {
            throw new ModelException("Invalid color format. Use hex color (e.g., #FF0000)", 400);
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
        if (!empty($data['category_name'])) {
            if (strlen($data['category_name']) < 2) {
                throw new ModelException("Category name must be at least 2 characters", 400);
            }
            if (strlen($data['category_name']) > 100) {
                throw new ModelException("Category name must not exceed 100 characters", 400);
            }
        }

        if (!empty($data['status']) && !in_array($data['status'], $this->allowedStatuses)) {
            throw new ModelException("Invalid status value", 400);
        }

        // Validate color format (hex color)
        if (!empty($data['color']) && !preg_match('/^#[a-fA-F0-9]{6}$/', $data['color'])) {
            throw new ModelException("Invalid color format. Use hex color (e.g., #FF0000)", 400);
        }
    }
}