<?php
/**
 * AI Banking GRC Platform - Permission Model
 * 
 * @package    AI-Banking-GRC-Platform
 * @version    1.0.0
 * @author     GRC Platform Team
 * @copyright  2026 AI Banking GRC Platform
 * @license    Proprietary
 * 
 * This model handles:
 * - Permission CRUD operations
 * - Role relationships
 * - Module-based grouping
 */

declare(strict_types=1);

namespace App\Models;

use PDO;

class Permission extends BaseModel
{
    /**
     * Table name
     * @var string
     */
    protected string $table = 'permissions';
    
    /**
     * Fillable fields
     * @var array
     */
    protected array $fillable = [
        'name',
        'display_name',
        'module',
        'description'
    ];
    
    /**
     * Constructor
     */
    public function __construct()
    {
        parent::__construct();
    }
    
    /**
     * Get roles with this permission
     * 
     * @param int $permissionId
     * @return array
     */
    public function getRoles(int $permissionId): array
    {
        $sql = "SELECT r.* FROM roles r 
                INNER JOIN role_permissions rp ON rp.role_id = r.id 
                WHERE rp.permission_id = :permission_id 
                AND r.deleted_at IS NULL";
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute(['permission_id' => $permissionId]);
        
        return $stmt->fetchAll(PDO::FETCH_OBJ);
    }
    
    /**
     * Get permissions by module
     * 
     * @param string $module
     * @return array
     */
    public function getByModule(string $module): array
    {
        return $this->where(['module' => $module]);
    }
    
    /**
     * Get all modules with permissions
     * 
     * @return array
     */
    public function getModulesWithPermissions(): array
    {
        $sql = "SELECT module, GROUP_CONCAT(id) as permission_ids, 
                GROUP_CONCAT(name) as permission_names 
                FROM {$this->table} 
                WHERE deleted_at IS NULL 
                GROUP BY module 
                ORDER BY module";
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute();
        
        return $stmt->fetchAll(PDO::FETCH_OBJ);
    }
    
    /**
     * Get all unique modules
     * 
     * @return array
     */
    public function getModules(): array
    {
        $sql = "SELECT DISTINCT module FROM {$this->table} 
                WHERE deleted_at IS NULL 
                ORDER BY module";
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute();
        
        return $stmt->fetchAll(PDO::FETCH_COLUMN);
    }
    
    /**
     * Check if permission exists
     * 
     * @param string $name
     * @return bool
     */
    public function exists(string $name): bool
    {
        return $this->count(['name' => $name]) > 0;
    }
    
    /**
     * Get or create permission
     * 
     * @param string $name
     * @param string $displayName
     * @param string $module
     * @param string $description
     * @return int
     */
    public function getOrCreate(string $name, string $displayName, string $module, string $description = ''): int
    {
        $existing = $this->findBy('name', $name);
        
        if ($existing) {
            return (int)$existing->id;
        }
        
        $data = [
            'name' => $name,
            'display_name' => $displayName,
            'module' => $module,
            'description' => $description
        ];
        
        return (int)$this->create($data);
    }
}