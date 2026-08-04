<?php

declare(strict_types=1);

namespace App\Models;

use PDO;
use PDOException;
use DateTime;
use App\Helpers\Logger;
use App\Exceptions\ModelException;

/**
 * ComplianceTask Model
 * 
 * Handles all compliance task operations with enterprise-grade security
 * and banking standards compliance.
 */
class ComplianceTask
{
    /**
     * @var PDO Database connection instance
     */
    private PDO $db;

    /**
     * @var string Table name
     */
    private string $table = 'compliance_tasks';

    /**
     * @var array Allowed statuses
     */
    private array $allowedStatuses = ['pending', 'in_progress', 'completed', 'overdue', 'cancelled'];

    /**
     * @var array Allowed priorities
     */
    private array $allowedPriorities = ['low', 'medium', 'high', 'critical'];

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
     * Create a new compliance task
     * 
     * @param array $data Task data
     * @return array Created task
     * @throws ModelException
     */
    public function create(array $data): array
    {
        try {
            $this->validateCreateData($data);

            $sql = "INSERT INTO {$this->table} (
                title,
                description,
                department_id,
                assigned_to,
                priority,
                status,
                due_date,
                completed_date,
                created_by,
                updated_by,
                created_at,
                updated_at
            ) VALUES (
                :title,
                :description,
                :department_id,
                :assigned_to,
                :priority,
                :status,
                :due_date,
                :completed_date,
                :created_by,
                :updated_by,
                NOW(),
                NOW()
            )";

            $stmt = $this->db->prepare($sql);
            
            $dueDate = !empty($data['due_date']) ? $data['due_date'] : null;
            $completedDate = !empty($data['completed_date']) ? $data['completed_date'] : null;
            
            $stmt->execute([
                ':title' => $data['title'],
                ':description' => $data['description'] ?? '',
                ':department_id' => $data['department_id'],
                ':assigned_to' => $data['assigned_to'] ?? null,
                ':priority' => $data['priority'] ?? 'medium',
                ':status' => $data['status'] ?? 'pending',
                ':due_date' => $dueDate,
                ':completed_date' => $completedDate,
                ':created_by' => $data['created_by'] ?? 1,
                ':updated_by' => $data['updated_by'] ?? 1
            ]);

            $id = (int) $this->db->lastInsertId();
            
            Logger::info("Compliance task created", ['id' => $id, 'title' => $data['title']]);
            
            return $this->findById($id);

        } catch (PDOException $e) {
            Logger::error("Failed to create compliance task", ['error' => $e->getMessage()]);
            throw new ModelException("Failed to create compliance task: " . $e->getMessage(), 500, $e);
        }
    }

    /**
     * Update an existing compliance task
     * 
     * @param int $id Task ID
     * @param array $data Updated data
     * @return array Updated task
     * @throws ModelException
     */
    public function update(int $id, array $data): array
    {
        try {
            $existing = $this->findById($id);
            
            $this->validateUpdateData($data, $existing);

            $allowedFields = [
                'title', 'description', 'department_id', 'assigned_to',
                'priority', 'status', 'due_date', 'completed_date', 'updated_by'
            ];

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

            Logger::info("Compliance task updated", ['id' => $id]);

            return $this->findById($id);

        } catch (PDOException $e) {
            Logger::error("Failed to update compliance task", ['id' => $id, 'error' => $e->getMessage()]);
            throw new ModelException("Failed to update compliance task: " . $e->getMessage(), 500, $e);
        }
    }

    /**
     * Delete a compliance task (hard delete)
     * 
     * @param int $id Task ID
     * @return bool Success status
     * @throws ModelException
     */
    public function delete(int $id): bool
    {
        try {
            $this->findById($id);

            $sql = "DELETE FROM {$this->table} WHERE id = :id";
            $stmt = $this->db->prepare($sql);
            $result = $stmt->execute([':id' => $id]);

            Logger::info("Compliance task deleted", ['id' => $id]);

            return $result;

        } catch (PDOException $e) {
            Logger::error("Failed to delete compliance task", ['id' => $id, 'error' => $e->getMessage()]);
            throw new ModelException("Failed to delete compliance task: " . $e->getMessage(), 500, $e);
        }
    }

    /**
     * Find a compliance task by ID
     * 
     * @param int $id Task ID
     * @return array Task data
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
                throw new ModelException("Compliance task not found with ID: {$id}", 404);
            }

            return $result;

        } catch (PDOException $e) {
            Logger::error("Failed to find compliance task", ['id' => $id, 'error' => $e->getMessage()]);
            throw new ModelException("Failed to find compliance task: " . $e->getMessage(), 500, $e);
        }
    }

    /**
     * Find a single task by criteria
     * 
     * @param array $criteria Search criteria
     * @return array|null Task data or null if not found
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
            Logger::error("Failed to find compliance task", ['error' => $e->getMessage()]);
            return null;
        }
    }

    /**
     * Get all compliance tasks
     * 
     * @param array $options Query options (order, limit, offset)
     * @return array List of tasks
     */
    public function all(array $options = []): array
    {
        try {
            $orderBy = $options['order_by'] ?? 'created_at';
            $orderDir = $options['order_dir'] ?? 'DESC';
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
            Logger::error("Failed to fetch all compliance tasks", ['error' => $e->getMessage()]);
            return [];
        }
    }

    /**
     * Get pending tasks
     * 
     * @return array Pending tasks
     */
    public function pending(): array
    {
        return $this->findByStatus('pending');
    }

    /**
     * Get completed tasks
     * 
     * @return array Completed tasks
     */
    public function completed(): array
    {
        return $this->findByStatus('completed');
    }

    /**
     * Get overdue tasks
     * 
     * @return array Overdue tasks
     */
    public function overdue(): array
    {
        try {
            $sql = "SELECT * FROM {$this->table} 
                    WHERE due_date < NOW() 
                    AND status NOT IN ('completed', 'cancelled')
                    ORDER BY due_date ASC";
            
            $stmt = $this->db->prepare($sql);
            $stmt->execute();
            
            return $stmt->fetchAll(PDO::FETCH_ASSOC);

        } catch (PDOException $e) {
            Logger::error("Failed to fetch overdue tasks", ['error' => $e->getMessage()]);
            return [];
        }
    }

    /**
     * Find tasks by status
     * 
     * @param string $status Status to filter
     * @return array Tasks
     */
    private function findByStatus(string $status): array
    {
        try {
            $sql = "SELECT * FROM {$this->table} WHERE status = :status ORDER BY due_date ASC";
            $stmt = $this->db->prepare($sql);
            $stmt->execute([':status' => $status]);
            
            return $stmt->fetchAll(PDO::FETCH_ASSOC);

        } catch (PDOException $e) {
            Logger::error("Failed to fetch tasks by status", ['status' => $status, 'error' => $e->getMessage()]);
            return [];
        }
    }

    /**
     * Assign task to a user
     * 
     * @param int $taskId Task ID
     * @param int $userId User ID
     * @return array Updated task
     * @throws ModelException
     */
    public function assignUser(int $taskId, int $userId): array
    {
        return $this->update($taskId, ['assigned_to' => $userId]);
    }

    /**
     * Change task status
     * 
     * @param int $taskId Task ID
     * @param string $status New status
     * @param int $userId User ID making the change
     * @return array Updated task
     * @throws ModelException
     */
    public function changeStatus(int $taskId, string $status, int $userId = 1): array
    {
        if (!in_array($status, $this->allowedStatuses)) {
            throw new ModelException("Invalid status: {$status}", 400);
        }

        $data = ['status' => $status, 'updated_by' => $userId];
        
        if ($status === 'completed') {
            $data['completed_date'] = (new DateTime())->format('Y-m-d H:i:s');
        }

        return $this->update($taskId, $data);
    }

    /**
     * Search tasks with filters
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

            // Apply filters
            if (!empty($filters['title'])) {
                $where[] = "title LIKE :title";
                $params[':title'] = '%' . $filters['title'] . '%';
            }

            if (!empty($filters['department_id'])) {
                $where[] = "department_id = :department_id";
                $params[':department_id'] = $filters['department_id'];
            }

            if (!empty($filters['assigned_to'])) {
                $where[] = "assigned_to = :assigned_to";
                $params[':assigned_to'] = $filters['assigned_to'];
            }

            if (!empty($filters['priority'])) {
                $where[] = "priority = :priority";
                $params[':priority'] = $filters['priority'];
            }

            if (!empty($filters['status'])) {
                $where[] = "status = :status";
                $params[':status'] = $filters['status'];
            }

            if (!empty($filters['date_from'])) {
                $where[] = "due_date >= :date_from";
                $params[':date_from'] = $filters['date_from'];
            }

            if (!empty($filters['date_to'])) {
                $where[] = "due_date <= :date_to";
                $params[':date_to'] = $filters['date_to'];
            }

            $orderBy = $options['order_by'] ?? 'created_at';
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
            Logger::error("Failed to search compliance tasks", ['error' => $e->getMessage()]);
            return [];
        }
    }

    /**
     * Paginate tasks
     * 
     * @param int $page Page number
     * @param int $perPage Items per page
     * @param array $filters Filters to apply
     * @return array Paginated results with metadata
     */
    public function paginate(int $page = 1, int $perPage = 20, array $filters = []): array
    {
        $offset = ($page - 1) * $perPage;
        
        $items = $this->search($filters, [
            'limit' => $perPage,
            'offset' => $offset,
            'order_by' => $filters['order_by'] ?? 'created_at',
            'order_dir' => $filters['order_dir'] ?? 'DESC'
        ]);

        $total = $this->count($filters);

        return [
            'data' => $items,
            'meta' => [
                'current_page' => $page,
                'per_page' => $perPage,
                'total' => $total,
                'total_pages' => ceil($total / $perPage),
                'from' => $offset + 1,
                'to' => min($offset + $perPage, $total)
            ]
        ];
    }

    /**
     * Count tasks
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
                $where[] = "status = :status";
                $params[':status'] = $filters['status'];
            }

            if (!empty($filters['priority'])) {
                $where[] = "priority = :priority";
                $params[':priority'] = $filters['priority'];
            }

            if (!empty($filters['department_id'])) {
                $where[] = "department_id = :department_id";
                $params[':department_id'] = $filters['department_id'];
            }

            $sql = "SELECT COUNT(*) as total FROM {$this->table} WHERE " . implode(' AND ', $where);
            $stmt = $this->db->prepare($sql);
            $stmt->execute($params);
            
            $result = $stmt->fetch(PDO::FETCH_ASSOC);
            return (int) ($result['total'] ?? 0);

        } catch (PDOException $e) {
            Logger::error("Failed to count compliance tasks", ['error' => $e->getMessage()]);
            return 0;
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
        if (empty($data['title'])) {
            throw new ModelException("Title is required", 400);
        }

        if (empty($data['department_id'])) {
            throw new ModelException("Department ID is required", 400);
        }

        if (!empty($data['priority']) && !in_array($data['priority'], $this->allowedPriorities)) {
            throw new ModelException("Invalid priority value", 400);
        }

        if (!empty($data['status']) && !in_array($data['status'], $this->allowedStatuses)) {
            throw new ModelException("Invalid status value", 400);
        }
    }

    /**
     * Validate update data
     * 
     * @param array $data Data to validate
     * @param array $existing Existing data
     * @throws ModelException
     */
    private function validateUpdateData(array $data, array $existing): void
    {
        if (!empty($data['priority']) && !in_array($data['priority'], $this->allowedPriorities)) {
            throw new ModelException("Invalid priority value", 400);
        }

        if (!empty($data['status']) && !in_array($data['status'], $this->allowedStatuses)) {
            throw new ModelException("Invalid status value", 400);
        }

        // Check if trying to complete a task that's already completed
        if (!empty($data['status']) && $data['status'] === 'completed' && $existing['status'] === 'completed') {
            throw new ModelException("Task is already completed", 400);
        }
    }
}