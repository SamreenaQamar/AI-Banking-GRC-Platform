<?php
/**
 * AI Banking GRC Platform - Activity Log Model
 * 
 * @package    AI-Banking-GRC-Platform
 * @subpackage app/Models
 * @version    1.0.0
 * @author     GRC Platform Team
 * @copyright  2026 AI Banking GRC Platform
 * @license    Proprietary
 * 
 * This model handles activity logging and audit trail:
 * - Log user activities
 * - Retrieve activity logs
 * - Filter and search logs
 * - Clean up old logs
 * - Export logs
 */

declare(strict_types=1);

namespace App\Models;

use PDO;
use App\Helpers\DateHelper;
use App\Helpers\LogHelper;

class ActivityLog extends BaseModel
{
    /**
     * Table name
     * @var string
     */
    protected string $table = 'activity_logs';

    /**
     * Primary key
     * @var string
     */
    protected string $primaryKey = 'id';

    /**
     * Fillable fields
     * @var array
     */
    protected array $fillable = [
        'user_id',
        'action',
        'module',
        'description',
        'target_type',
        'target_id',
        'target_name',
        'ip_address',
        'user_agent',
        'referer',
        'old_data',
        'new_data',
        'diff_data'
    ];

    /**
     * Hidden fields
     * @var array
     */
    protected array $hidden = [];

    /**
     * Constructor
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Log an activity
     * 
     * @param array $data Activity data
     * @return int|false
     */
    public function log(array $data): int|false
    {
        try {
            $logData = [
                'user_id' => $data['user_id'] ?? null,
                'action' => $data['action'] ?? '',
                'module' => $data['module'] ?? 'system',
                'description' => $data['description'] ?? '',
                'target_type' => $data['target_type'] ?? null,
                'target_id' => $data['target_id'] ?? null,
                'target_name' => $data['target_name'] ?? null,
                'ip_address' => $data['ip_address'] ?? ($_SERVER['REMOTE_ADDR'] ?? null),
                'user_agent' => $data['user_agent'] ?? ($_SERVER['HTTP_USER_AGENT'] ?? null),
                'referer' => $data['referer'] ?? ($_SERVER['HTTP_REFERER'] ?? null),
                'old_data' => isset($data['old_data']) ? json_encode($data['old_data']) : null,
                'new_data' => isset($data['new_data']) ? json_encode($data['new_data']) : null,
                'diff_data' => isset($data['diff_data']) ? json_encode($data['diff_data']) : null,
                'created_at' => date('Y-m-d H:i:s')
            ];

            return $this->create($logData);

        } catch (\Exception $e) {
            LogHelper::error('Failed to log activity: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Log user login
     * 
     * @param int $userId
     * @return int|false
     */
    public function logLogin(int $userId): int|false
    {
        return $this->log([
            'user_id' => $userId,
            'action' => 'login',
            'module' => 'authentication',
            'description' => 'User logged in successfully',
            'target_type' => 'user',
            'target_id' => $userId
        ]);
    }

    /**
     * Log user logout
     * 
     * @param int $userId
     * @return int|false
     */
    public function logLogout(int $userId): int|false
    {
        return $this->log([
            'user_id' => $userId,
            'action' => 'logout',
            'module' => 'authentication',
            'description' => 'User logged out',
            'target_type' => 'user',
            'target_id' => $userId
        ]);
    }

    /**
     * Log failed login
     * 
     * @param string $username
     * @param string $ip
     * @return int|false
     */
    public function logFailedLogin(string $username, string $ip): int|false
    {
        return $this->log([
            'user_id' => null,
            'action' => 'login_failed',
            'module' => 'authentication',
            'description' => "Failed login attempt for user: {$username}",
            'target_type' => 'user',
            'target_name' => $username,
            'ip_address' => $ip
        ]);
    }

    /**
     * Log user action
     * 
     * @param int $userId
     * @param string $action
     * @param string $module
     * @param string $description
     * @param array $data
     * @return int|false
     */
    public function logAction(int $userId, string $action, string $module, string $description, array $data = []): int|false
    {
        return $this->log([
            'user_id' => $userId,
            'action' => $action,
            'module' => $module,
            'description' => $description,
            'new_data' => $data
        ]);
    }

    /**
     * Log data change
     * 
     * @param int $userId
     * @param string $module
     * @param string $targetType
     * @param int $targetId
     * @param array $oldData
     * @param array $newData
     * @return int|false
     */
    public function logChange(
        int $userId,
        string $module,
        string $targetType,
        int $targetId,
        array $oldData,
        array $newData
    ): int|false {
        // Calculate diff
        $diff = [];
        foreach ($newData as $key => $value) {
            if (!isset($oldData[$key]) || $oldData[$key] !== $value) {
                $diff[$key] = [
                    'old' => $oldData[$key] ?? null,
                    'new' => $value
                ];
            }
        }

        if (empty($diff)) {
            return false;
        }

        return $this->log([
            'user_id' => $userId,
            'action' => 'update',
            'module' => $module,
            'description' => "Updated {$targetType} record #{$targetId}",
            'target_type' => $targetType,
            'target_id' => $targetId,
            'old_data' => $oldData,
            'new_data' => $newData,
            'diff_data' => $diff
        ]);
    }

    /**
     * Log data creation
     * 
     * @param int $userId
     * @param string $module
     * @param string $targetType
     * @param int $targetId
     * @param array $data
     * @return int|false
     */
    public function logCreate(int $userId, string $module, string $targetType, int $targetId, array $data): int|false
    {
        return $this->log([
            'user_id' => $userId,
            'action' => 'create',
            'module' => $module,
            'description' => "Created {$targetType} record #{$targetId}",
            'target_type' => $targetType,
            'target_id' => $targetId,
            'new_data' => $data
        ]);
    }

    /**
     * Log data deletion
     * 
     * @param int $userId
     * @param string $module
     * @param string $targetType
     * @param int $targetId
     * @param array $data
     * @return int|false
     */
    public function logDelete(int $userId, string $module, string $targetType, int $targetId, array $data): int|false
    {
        return $this->log([
            'user_id' => $userId,
            'action' => 'delete',
            'module' => $module,
            'description' => "Deleted {$targetType} record #{$targetId}",
            'target_type' => $targetType,
            'target_id' => $targetId,
            'old_data' => $data
        ]);
    }

    /**
     * Log data export
     * 
     * @param int $userId
     * @param string $module
     * @param string $type
     * @param array $params
     * @return int|false
     */
    public function logExport(int $userId, string $module, string $type, array $params = []): int|false
    {
        return $this->log([
            'user_id' => $userId,
            'action' => 'export',
            'module' => $module,
            'description' => "Exported {$type} data",
            'target_type' => 'export',
            'new_data' => $params
        ]);
    }

    /**
     * Log data import
     * 
     * @param int $userId
     * @param string $module
     * @param string $type
     * @param array $params
     * @return int|false
     */
    public function logImport(int $userId, string $module, string $type, array $params = []): int|false
    {
        return $this->log([
            'user_id' => $userId,
            'action' => 'import',
            'module' => $module,
            'description' => "Imported {$type} data",
            'target_type' => 'import',
            'new_data' => $params
        ]);
    }

    /**
     * Get activities by user
     * 
     * @param int $userId
     * @param int $limit
     * @param int $offset
     * @return array
     */
    public function getByUser(int $userId, int $limit = 50, int $offset = 0): array
    {
        $sql = "SELECT * FROM {$this->table} 
                WHERE user_id = :user_id 
                ORDER BY created_at DESC 
                LIMIT :limit OFFSET :offset";

        $stmt = $this->db->prepare($sql);
        $stmt->bindParam('user_id', $userId, PDO::PARAM_INT);
        $stmt->bindParam('limit', $limit, PDO::PARAM_INT);
        $stmt->bindParam('offset', $offset, PDO::PARAM_INT);
        $stmt->execute();

        return $stmt->fetchAll(PDO::FETCH_OBJ);
    }

    /**
     * Get activities by module
     * 
     * @param string $module
     * @param int $limit
     * @param int $offset
     * @return array
     */
    public function getByModule(string $module, int $limit = 50, int $offset = 0): array
    {
        $sql = "SELECT * FROM {$this->table} 
                WHERE module = :module 
                ORDER BY created_at DESC 
                LIMIT :limit OFFSET :offset";

        $stmt = $this->db->prepare($sql);
        $stmt->bindParam('module', $module, PDO::PARAM_STR);
        $stmt->bindParam('limit', $limit, PDO::PARAM_INT);
        $stmt->bindParam('offset', $offset, PDO::PARAM_INT);
        $stmt->execute();

        return $stmt->fetchAll(PDO::FETCH_OBJ);
    }

    /**
     * Get activities by action
     * 
     * @param string $action
     * @param int $limit
     * @param int $offset
     * @return array
     */
    public function getByAction(string $action, int $limit = 50, int $offset = 0): array
    {
        $sql = "SELECT * FROM {$this->table} 
                WHERE action = :action 
                ORDER BY created_at DESC 
                LIMIT :limit OFFSET :offset";

        $stmt = $this->db->prepare($sql);
        $stmt->bindParam('action', $action, PDO::PARAM_STR);
        $stmt->bindParam('limit', $limit, PDO::PARAM_INT);
        $stmt->bindParam('offset', $offset, PDO::PARAM_INT);
        $stmt->execute();

        return $stmt->fetchAll(PDO::FETCH_OBJ);
    }

    /**
     * Get filtered activities
     * 
     * @param array $filters
     * @param int $page
     * @param int $perPage
     * @return array
     */
    public function getFiltered(array $filters = [], int $page = 1, int $perPage = 20): array
    {
        $sql = "SELECT * FROM {$this->table} WHERE 1=1";
        $params = [];

        if (!empty($filters['user_id'])) {
            $sql .= " AND user_id = :user_id";
            $params['user_id'] = $filters['user_id'];
        }

        if (!empty($filters['module'])) {
            $sql .= " AND module = :module";
            $params['module'] = $filters['module'];
        }

        if (!empty($filters['action'])) {
            $sql .= " AND action = :action";
            $params['action'] = $filters['action'];
        }

        if (!empty($filters['date_from'])) {
            $sql .= " AND created_at >= :date_from";
            $params['date_from'] = $filters['date_from'] . ' 00:00:00';
        }

        if (!empty($filters['date_to'])) {
            $sql .= " AND created_at <= :date_to";
            $params['date_to'] = $filters['date_to'] . ' 23:59:59';
        }

        if (!empty($filters['search'])) {
            $sql .= " AND (description LIKE :search OR target_name LIKE :search)";
            $params['search'] = '%' . $filters['search'] . '%';
        }

        $sql .= " ORDER BY created_at DESC";

        $offset = ($page - 1) * $perPage;
        $sql .= " LIMIT :limit OFFSET :offset";
        $params['limit'] = $perPage;
        $params['offset'] = $offset;

        $stmt = $this->db->prepare($sql);
        foreach ($params as $key => $value) {
            $type = is_int($value) ? PDO::PARAM_INT : PDO::PARAM_STR;
            $stmt->bindValue($key, $value, $type);
        }
        $stmt->execute();

        return $stmt->fetchAll(PDO::FETCH_OBJ);
    }

    /**
     * Count filtered activities
     * 
     * @param array $filters
     * @return int
     */
    public function countFiltered(array $filters = []): int
    {
        $sql = "SELECT COUNT(*) FROM {$this->table} WHERE 1=1";
        $params = [];

        if (!empty($filters['user_id'])) {
            $sql .= " AND user_id = :user_id";
            $params['user_id'] = $filters['user_id'];
        }

        if (!empty($filters['module'])) {
            $sql .= " AND module = :module";
            $params['module'] = $filters['module'];
        }

        if (!empty($filters['action'])) {
            $sql .= " AND action = :action";
            $params['action'] = $filters['action'];
        }

        if (!empty($filters['date_from'])) {
            $sql .= " AND created_at >= :date_from";
            $params['date_from'] = $filters['date_from'] . ' 00:00:00';
        }

        if (!empty($filters['date_to'])) {
            $sql .= " AND created_at <= :date_to";
            $params['date_to'] = $filters['date_to'] . ' 23:59:59';
        }

        if (!empty($filters['search'])) {
            $sql .= " AND (description LIKE :search OR target_name LIKE :search)";
            $params['search'] = '%' . $filters['search'] . '%';
        }

        $stmt = $this->db->prepare($sql);
        foreach ($params as $key => $value) {
            $type = is_int($value) ? PDO::PARAM_INT : PDO::PARAM_STR;
            $stmt->bindValue($key, $value, $type);
        }
        $stmt->execute();

        return (int)$stmt->fetchColumn();
    }

    /**
     * Get user activity summary
     * 
     * @param int $userId
     * @param int $days
     * @return array
     */
    public function getUserSummary(int $userId, int $days = 30): array
    {
        $sql = "SELECT 
                    DATE(created_at) as date,
                    COUNT(*) as total,
                    COUNT(DISTINCT action) as actions,
                    module
                FROM {$this->table}
                WHERE user_id = :user_id 
                AND created_at >= DATE_SUB(NOW(), INTERVAL :days DAY)
                GROUP BY DATE(created_at), module
                ORDER BY date DESC";

        $stmt = $this->db->prepare($sql);
        $stmt->bindParam('user_id', $userId, PDO::PARAM_INT);
        $stmt->bindParam('days', $days, PDO::PARAM_INT);
        $stmt->execute();

        return $stmt->fetchAll(PDO::FETCH_OBJ);
    }

    /**
     * Get module activity summary
     * 
     * @param string $module
     * @param int $days
     * @return array
     */
    public function getModuleSummary(string $module, int $days = 30): array
    {
        $sql = "SELECT 
                    DATE(created_at) as date,
                    COUNT(*) as total,
                    action,
                    COUNT(DISTINCT user_id) as users
                FROM {$this->table}
                WHERE module = :module 
                AND created_at >= DATE_SUB(NOW(), INTERVAL :days DAY)
                GROUP BY DATE(created_at), action
                ORDER BY date DESC";

        $stmt = $this->db->prepare($sql);
        $stmt->bindParam('module', $module, PDO::PARAM_STR);
        $stmt->bindParam('days', $days, PDO::PARAM_INT);
        $stmt->execute();

        return $stmt->fetchAll(PDO::FETCH_OBJ);
    }

    /**
     * Get activity statistics
     * 
     * @param int $days
     * @return array
     */
    public function getStats(int $days = 30): array
    {
        $sql = "SELECT 
                    COUNT(*) as total,
                    COUNT(DISTINCT user_id) as active_users,
                    COUNT(DISTINCT module) as active_modules,
                    MIN(created_at) as earliest,
                    MAX(created_at) as latest
                FROM {$this->table}
                WHERE created_at >= DATE_SUB(NOW(), INTERVAL :days DAY)";

        $stmt = $this->db->prepare($sql);
        $stmt->bindParam('days', $days, PDO::PARAM_INT);
        $stmt->execute();

        $stats = $stmt->fetch(PDO::FETCH_OBJ);

        // Get top actions
        $sql2 = "SELECT action, COUNT(*) as count 
                FROM {$this->table}
                WHERE created_at >= DATE_SUB(NOW(), INTERVAL :days DAY)
                GROUP BY action
                ORDER BY count DESC
                LIMIT 5";

        $stmt2 = $this->db->prepare($sql2);
        $stmt2->bindParam('days', $days, PDO::PARAM_INT);
        $stmt2->execute();
        $topActions = $stmt2->fetchAll(PDO::FETCH_OBJ);

        // Get top modules
        $sql3 = "SELECT module, COUNT(*) as count 
                FROM {$this->table}
                WHERE created_at >= DATE_SUB(NOW(), INTERVAL :days DAY)
                GROUP BY module
                ORDER BY count DESC
                LIMIT 5";

        $stmt3 = $this->db->prepare($sql3);
        $stmt3->bindParam('days', $days, PDO::PARAM_INT);
        $stmt3->execute();
        $topModules = $stmt3->fetchAll(PDO::FETCH_OBJ);

        return [
            'total' => (int)$stats->total,
            'active_users' => (int)$stats->active_users,
            'active_modules' => (int)$stats->active_modules,
            'earliest' => $stats->earliest,
            'latest' => $stats->latest,
            'top_actions' => $topActions,
            'top_modules' => $topModules
        ];
    }

    /**
     * Clean up old logs
     * 
     * @param int $days
     * @return int Number of deleted records
     */
    public function cleanOldLogs(int $days = 90): int
    {
        $sql = "DELETE FROM {$this->table} 
                WHERE created_at < DATE_SUB(NOW(), INTERVAL :days DAY)";

        $stmt = $this->db->prepare($sql);
        $stmt->bindParam('days', $days, PDO::PARAM_INT);
        $stmt->execute();

        return $stmt->rowCount();
    }

    /**
     * Export activities to CSV
     * 
     * @param array $filters
     * @return string CSV content
     */
    public function exportCSV(array $filters = []): string
    {
        $activities = $this->getFiltered($filters, 1, 10000);

        $csv = "ID,User,Action,Module,Description,Target,IP Address,Date\n";
        
        foreach ($activities as $activity) {
            $csv .= sprintf(
                "%d,%s,%s,%s,%s,%s,%s,%s\n",
                $activity->id,
                $activity->user_id ?? 'System',
                $activity->action,
                $activity->module,
                str_replace(',', ';', $activity->description ?? ''),
                $activity->target_name ?? $activity->target_type ?? '',
                $activity->ip_address ?? '',
                $activity->created_at
            );
        }

        return $csv;
    }

    /**
     * Get activities by target
     * 
     * @param string $targetType
     * @param int $targetId
     * @param int $limit
     * @return array
     */
    public function getByTarget(string $targetType, int $targetId, int $limit = 50): array
    {
        $sql = "SELECT * FROM {$this->table} 
                WHERE target_type = :target_type 
                AND target_id = :target_id 
                ORDER BY created_at DESC 
                LIMIT :limit";

        $stmt = $this->db->prepare($sql);
        $stmt->bindParam('target_type', $targetType, PDO::PARAM_STR);
        $stmt->bindParam('target_id', $targetId, PDO::PARAM_INT);
        $stmt->bindParam('limit', $limit, PDO::PARAM_INT);
        $stmt->execute();

        return $stmt->fetchAll(PDO::FETCH_OBJ);
    }

    /**
     * Get recent activities for dashboard
     * 
     * @param int $limit
     * @param int|null $userId
     * @return array
     */
    public function getRecent(int $limit = 10, ?int $userId = null): array
    {
        $sql = "SELECT a.*, 
                       u.username, 
                       u.full_name,
                       u.profile_image
                FROM {$this->table} a
                LEFT JOIN users u ON u.id = a.user_id
                WHERE 1=1";

        if ($userId) {
            $sql .= " AND a.user_id = :user_id";
        }

        $sql .= " ORDER BY a.created_at DESC LIMIT :limit";

        $stmt = $this->db->prepare($sql);
        if ($userId) {
            $stmt->bindParam('user_id', $userId, PDO::PARAM_INT);
        }
        $stmt->bindParam('limit', $limit, PDO::PARAM_INT);
        $stmt->execute();

        return $stmt->fetchAll(PDO::FETCH_OBJ);
    }

    /**
     * Get activity timeline for a specific period
     * 
     * @param string $period (today, week, month)
     * @param int|null $userId
     * @return array
     */
    public function getTimeline(string $period = 'today', ?int $userId = null): array
    {
        $interval = match($period) {
            'today' => '1 DAY',
            'week' => '7 DAY',
            'month' => '30 DAY',
            default => '1 DAY'
        };

        $sql = "SELECT 
                    DATE_FORMAT(created_at, '%Y-%m-%d %H:00:00') as hour,
                    COUNT(*) as count
                FROM {$this->table}
                WHERE created_at >= DATE_SUB(NOW(), INTERVAL {$interval})
                AND 1=1";

        if ($userId) {
            $sql .= " AND user_id = :user_id";
        }

        $sql .= " GROUP BY hour ORDER BY hour ASC";

        $stmt = $this->db->prepare($sql);
        if ($userId) {
            $stmt->bindParam('user_id', $userId, PDO::PARAM_INT);
        }
        $stmt->execute();

        return $stmt->fetchAll(PDO::FETCH_OBJ);
    }

    /**
     * Get user activity for a specific user
     * 
     * @param int $userId
     * @param int $limit
     * @param int $offset
     * @return array
     */
    public function getUserActivities(int $userId, int $limit = 20, int $offset = 0): array
    {
        return $this->getByUser($userId, $limit, $offset);
    }

    /**
     * Get total activities count
     * 
     * @return int
     */
    public function getTotalCount(): int
    {
        $sql = "SELECT COUNT(*) FROM {$this->table}";
        $stmt = $this->db->prepare($sql);
        $stmt->execute();
        return (int)$stmt->fetchColumn();
    }

    /**
     * Get activities by date range
     * 
     * @param string $startDate
     * @param string $endDate
     * @param int $limit
     * @return array
     */
    public function getByDateRange(string $startDate, string $endDate, int $limit = 100): array
    {
        $sql = "SELECT * FROM {$this->table} 
                WHERE DATE(created_at) BETWEEN :start_date AND :end_date 
                ORDER BY created_at DESC 
                LIMIT :limit";

        $stmt = $this->db->prepare($sql);
        $stmt->bindParam('start_date', $startDate, PDO::PARAM_STR);
        $stmt->bindParam('end_date', $endDate, PDO::PARAM_STR);
        $stmt->bindParam('limit', $limit, PDO::PARAM_INT);
        $stmt->execute();

        return $stmt->fetchAll(PDO::FETCH_OBJ);
    }

    /**
     * Get activities by IP address
     * 
     * @param string $ip
     * @param int $limit
     * @return array
     */
    public function getByIP(string $ip, int $limit = 50): array
    {
        $sql = "SELECT * FROM {$this->table} 
                WHERE ip_address = :ip 
                ORDER BY created_at DESC 
                LIMIT :limit";

        $stmt = $this->db->prepare($sql);
        $stmt->bindParam('ip', $ip, PDO::PARAM_STR);
        $stmt->bindParam('limit', $limit, PDO::PARAM_INT);
        $stmt->execute();

        return $stmt->fetchAll(PDO::FETCH_OBJ);
    }
}