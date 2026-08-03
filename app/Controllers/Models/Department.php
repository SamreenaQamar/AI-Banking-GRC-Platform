<?php
/**
 * AI Banking GRC Platform - Department Model
 * 
 * @package    AI-Banking-GRC-Platform
 * @version    1.0.0
 * @author     GRC Platform Team
 * @copyright  2026 AI Banking GRC Platform
 * @license    Proprietary
 * 
 * This model handles:
 * - Department CRUD operations
 * - User relationships
 * - Risk and compliance relationships
 * - Audit relationships
 */

declare(strict_types=1);

namespace App\Models;

use PDO;

class Department extends BaseModel
{
    /**
     * Table name
     * @var string
     */
    protected string $table = 'departments';
    
    /**
     * Fillable fields
     * @var array
     */
    protected array $fillable = [
        'bank_id',
        'parent_id',
        'name',
        'code',
        'description',
        'head_user_id',
        'status',
        'level'
    ];
    
    /**
     * Constructor
     */
    public function __construct()
    {
        parent::__construct();
    }
    
    /**
     * Get users in department
     * 
     * @param int $departmentId
     * @return array
     */
    public function getUsers(int $departmentId): array
    {
        $sql = "SELECT u.* FROM users u 
                WHERE u.department_id = :department_id 
                AND u.deleted_at IS NULL 
                AND u.status = 'active'";
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute(['department_id' => $departmentId]);
        
        return $stmt->fetchAll(PDO::FETCH_OBJ);
    }
    
    /**
     * Get department head
     * 
     * @param int $departmentId
     * @return object|null
     */
    public function getHead(int $departmentId): ?object
    {
        $sql = "SELECT u.* FROM users u 
                WHERE u.id = (SELECT head_user_id FROM departments WHERE id = :department_id)";
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute(['department_id' => $departmentId]);
        $result = $stmt->fetch(PDO::FETCH_OBJ);
        
        return $result ?: null;
    }
    
    /**
     * Get risks for department
     * 
     * @param int $departmentId
     * @return array
     */
    public function getRisks(int $departmentId): array
    {
        $sql = "SELECT r.* FROM risk_register r 
                WHERE r.owner_department_id = :department_id 
                AND r.deleted_at IS NULL";
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute(['department_id' => $departmentId]);
        
        return $stmt->fetchAll(PDO::FETCH_OBJ);
    }
    
    /**
     * Get audits for department
     * 
     * @param int $departmentId
     * @return array
     */
    public function getAudits(int $departmentId): array
    {
        $sql = "SELECT a.* FROM audit_plans a 
                WHERE a.department_id = :department_id 
                AND a.deleted_at IS NULL 
                ORDER BY a.start_date DESC";
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute(['department_id' => $departmentId]);
        
        return $stmt->fetchAll(PDO::FETCH_OBJ);
    }
    
    /**
     * Get compliance tasks for department
     * 
     * @param int $departmentId
     * @return array
     */
    public function getCompliance(int $departmentId): array
    {
        $sql = "SELECT c.* FROM compliance_tasks c 
                WHERE c.department_id = :department_id 
                AND c.deleted_at IS NULL 
                ORDER BY c.due_date ASC";
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute(['department_id' => $departmentId]);
        
        return $stmt->fetchAll(PDO::FETCH_OBJ);
    }
    
    /**
     * Get child departments
     * 
     * @param int $departmentId
     * @return array
     */
    public function getChildren(int $departmentId): array
    {
        return $this->where(['parent_id' => $departmentId]);
    }
    
    /**
     * Get department tree
     * 
     * @param int|null $parentId
     * @param int $level
     * @return array
     */
    public function getTree(?int $parentId = null, int $level = 0): array
    {
        $sql = "SELECT * FROM {$this->table} 
                WHERE parent_id " . ($parentId === null ? "IS NULL" : "= :parent_id") . 
                " AND deleted_at IS NULL 
                ORDER BY name";
        
        $stmt = $this->db->prepare($sql);
        $params = $parentId === null ? [] : ['parent_id' => $parentId];
        $stmt->execute($params);
        $departments = $stmt->fetchAll(PDO::FETCH_OBJ);
        
        $tree = [];
        foreach ($departments as $department) {
            $department->level = $level;
            $department->children = $this->getTree((int)$department->id, $level + 1);
            $tree[] = $department;
        }
        
        return $tree;
    }
}