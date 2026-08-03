<?php
/**
 * Compliance Module - Service Layer
 * 
 * @package    AI-Banking-GRC-Platform
 * @subpackage modules/compliance
 * @version    1.0.0
 * @author     GRC Platform Team
 * @copyright  2026 AI Banking GRC Platform
 * @license    Proprietary
 * 
 * This service handles compliance business logic:
 * - Circular management
 * - Compliance scoring
 * - Gap analysis
 * - Recommendation engine
 * - Evidence management
 */

declare(strict_types=1);

namespace Modules\Compliance\Services;

use App\Models\SbpCircular;
use App\Models\ComplianceTask;
use App\Models\ComplianceEvidence;
use App\Models\ActivityLog;
use App\Helpers\Auth;
use App\Helpers\Database;
use Exception;
use PDO;

class ComplianceService
{
    /**
     * @var PDO
     */
    private PDO $db;
    
    /**
     * @var SbpCircular
     */
    private SbpCircular $circularModel;
    
    /**
     * @var ComplianceTask
     */
    private ComplianceTask $taskModel;
    
    /**
     * @var ComplianceEvidence
     */
    private ComplianceEvidence $evidenceModel;
    
    /**
     * @var ActivityLog
     */
    private ActivityLog $activityLogModel;
    
    /**
     * Constructor
     */
    public function __construct()
    {
        $this->db = Database::getInstance()->getConnection();
        $this->circularModel = new SbpCircular();
        $this->taskModel = new ComplianceTask();
        $this->evidenceModel = new ComplianceEvidence();
        $this->activityLogModel = new ActivityLog();
    }
    
    /**
     * Get compliance statistics
     * 
     * @return array
     */
    public function getComplianceStats(): array
    {
        $totalTasks = $this->taskModel->countAll();
        $completed = $this->taskModel->countByStatus('completed');
        $inProgress = $this->taskModel->countByStatus('in_progress');
        $pending = $this->taskModel->countByStatus('pending');
        $overdue = $this->taskModel->countOverdue();
        
        $totalCirculars = $this->circularModel->countAll();
        $implementedCirculars = $this->circularModel->countByStatus('implemented');
        $pendingCirculars = $this->circularModel->countByStatus('pending');
        $activeCirculars = $this->circularModel->countByStatus('active');
        
        $completionRate = $totalTasks > 0 ? round(($completed / $totalTasks) * 100, 2) : 0;
        $circularCompliance = $totalCirculars > 0 ? round(($implementedCirculars / $totalCirculars) * 100, 2) : 0;
        
        return [
            'total_tasks' => $totalTasks,
            'completed' => $completed,
            'in_progress' => $inProgress,
            'pending' => $pending,
            'overdue' => $overdue,
            'completion_rate' => $completionRate,
            'total_circulars' => $totalCirculars,
            'implemented' => $implementedCirculars,
            'pending_circulars' => $pendingCirculars,
            'active_circulars' => $activeCirculars,
            'circular_compliance' => $circularCompliance
        ];
    }
    
    /**
     * Get compliance dashboard data
     * 
     * @param int $userId
     * @return array
     */
    public function getDashboardData(int $userId): array
    {
        $stats = $this->getComplianceStats();
        $recentTasks = $this->getRecentTasks($userId);
        $upcomingDeadlines = $this->getUpcomingDeadlines($userId);
        $overdueTasks = $this->getOverdueTasks($userId);
        $recentCirculars = $this->getRecentCirculars();
        $gapAnalysis = $this->getGapAnalysis();
        $recommendations = $this->getRecommendations($userId);
        $calendarData = $this->getComplianceCalendar($userId);
        $complianceTrend = $this->getComplianceTrend();
        
        return [
            'stats' => $stats,
            'recent_tasks' => $recentTasks,
            'upcoming_deadlines' => $upcomingDeadlines,
            'overdue_tasks' => $overdueTasks,
            'recent_circulars' => $recentCirculars,
            'gap_analysis' => $gapAnalysis,
            'recommendations' => $recommendations,
            'calendar_data' => $calendarData,
            'compliance_trend' => $complianceTrend
        ];
    }
    
    /**
     * Get recent compliance tasks
     * 
     * @param int $userId
     * @param int $limit
     * @return array
     */
    public function getRecentTasks(int $userId, int $limit = 5): array
    {
        $sql = "SELECT t.*, 
                       CONCAT(u.first_name, ' ', u.last_name) as assigned_to_name,
                       d.name as department_name,
                       c.name as category_name
                FROM compliance_tasks t
                LEFT JOIN users u ON u.id = t.assigned_to
                LEFT JOIN departments d ON d.id = t.department_id
                LEFT JOIN compliance_categories c ON c.id = t.category_id
                WHERE t.deleted_at IS NULL
                AND (t.assigned_to = :user_id OR t.created_by = :user_id)
                ORDER BY t.created_at DESC
                LIMIT :limit";
        
        $stmt = $this->db->prepare($sql);
        $stmt->bindParam('user_id', $userId, PDO::PARAM_INT);
        $stmt->bindParam('limit', $limit, PDO::PARAM_INT);
        $stmt->execute();
        
        return $stmt->fetchAll(PDO::FETCH_OBJ);
    }
    
    /**
     * Get upcoming deadlines
     * 
     * @param int $userId
     * @param int $days
     * @return array
     */
    public function getUpcomingDeadlines(int $userId, int $days = 7): array
    {
        $sql = "SELECT t.*, 
                       CONCAT(u.first_name, ' ', u.last_name) as assigned_to_name
                FROM compliance_tasks t
                LEFT JOIN users u ON u.id = t.assigned_to
                WHERE t.deleted_at IS NULL
                AND t.assigned_to = :user_id
                AND t.status NOT IN ('completed', 'cancelled')
                AND t.due_date BETWEEN CURDATE() AND DATE_ADD(CURDATE(), INTERVAL :days DAY)
                ORDER BY t.due_date ASC";
        
        $stmt = $this->db->prepare($sql);
        $stmt->bindParam('user_id', $userId, PDO::PARAM_INT);
        $stmt->bindParam('days', $days, PDO::PARAM_INT);
        $stmt->execute();
        
        return $stmt->fetchAll(PDO::FETCH_OBJ);
    }
    
    /**
     * Get overdue tasks
     * 
     * @param int $userId
     * @return array
     */
    public function getOverdueTasks(int $userId): array
    {
        $sql = "SELECT t.*, 
                       CONCAT(u.first_name, ' ', u.last_name) as assigned_to_name,
                       DATEDIFF(CURDATE(), t.due_date) as days_overdue
                FROM compliance_tasks t
                LEFT JOIN users u ON u.id = t.assigned_to
                WHERE t.deleted_at IS NULL
                AND t.assigned_to = :user_id
                AND t.status NOT IN ('completed', 'cancelled')
                AND t.due_date < CURDATE()
                ORDER BY t.due_date ASC";
        
        $stmt = $this->db->prepare($sql);
        $stmt->bindParam('user_id', $userId, PDO::PARAM_INT);
        $stmt->execute();
        
        return $stmt->fetchAll(PDO::FETCH_OBJ);
    }
    
    /**
     * Get recent SBP circulars
     * 
     * @param int $limit
     * @return array
     */
    public function getRecentCirculars(int $limit = 5): array
    {
        $sql = "SELECT * FROM sbp_circulars 
                WHERE deleted_at IS NULL 
                ORDER BY issuance_date DESC 
                LIMIT :limit";
        
        $stmt = $this->db->prepare($sql);
        $stmt->bindParam('limit', $limit, PDO::PARAM_INT);
        $stmt->execute();
        
        return $stmt->fetchAll(PDO::FETCH_OBJ);
    }
    
    /**
     * Get gap analysis
     * 
     * @param string $framework
     * @return array
     */
    public function getGapAnalysis(string $framework = 'all'): array
    {
        $sql = "SELECT 
                    category,
                    COUNT(*) as total_controls,
                    SUM(CASE WHEN status = 'completed' THEN 1 ELSE 0 END) as implemented,
                    SUM(CASE WHEN status = 'in_progress' THEN 1 ELSE 0 END) as in_progress,
                    SUM(CASE WHEN status = 'pending' THEN 1 ELSE 0 END) as pending,
                    SUM(CASE WHEN status = 'overdue' THEN 1 ELSE 0 END) as overdue
                FROM compliance_tasks
                WHERE deleted_at IS NULL";
        
        if ($framework !== 'all') {
            $sql .= " AND framework_id = (SELECT id FROM compliance_frameworks WHERE code = :framework)";
        }
        
        $sql .= " GROUP BY category";
        
        $stmt = $this->db->prepare($sql);
        
        if ($framework !== 'all') {
            $stmt->bindParam('framework', $framework, PDO::PARAM_STR);
        }
        
        $stmt->execute();
        $results = $stmt->fetchAll(PDO::FETCH_OBJ);
        
        $gaps = [];
        foreach ($results as $row) {
            $complianceRate = $row->total_controls > 0 
                ? round(($row->implemented / $row->total_controls) * 100, 2) 
                : 0;
            
            $gaps[] = [
                'category' => $row->category,
                'total' => (int)$row->total_controls,
                'implemented' => (int)$row->implemented,
                'in_progress' => (int)$row->in_progress,
                'pending' => (int)$row->pending,
                'overdue' => (int)$row->overdue,
                'compliance_rate' => $complianceRate,
                'gaps' => $row->total_controls - $row->implemented
            ];
        }
        
        return $gaps;
    }
    
    /**
     * Get compliance recommendations
     * 
     * @param int $userId
     * @param int $limit
     * @return array
     */
    public function getRecommendations(int $userId, int $limit = 5): array
    {
        // This would integrate with AI service for real recommendations
        // For now, return sample recommendations based on gaps
        
        $gaps = $this->getGapAnalysis();
        $recommendations = [];
        
        foreach ($gaps as $gap) {
            if ($gap['gaps'] > 0) {
                $recommendations[] = [
                    'id' => uniqid(),
                    'title' => 'Address gaps in ' . $gap['category'],
                    'description' => $gap['gaps'] . ' compliance gaps identified. Review and implement required controls.',
                    'priority' => $gap['gaps'] > 5 ? 'high' : 'medium',
                    'category' => $gap['category'],
                    'deadline' => date('Y-m-d', strtotime('+30 days'))
                ];
            }
        }
        
        // Sort by priority
        usort($recommendations, function($a, $b) {
            $priorities = ['high' => 0, 'medium' => 1, 'low' => 2];
            return $priorities[$a['priority']] - $priorities[$b['priority']];
        });
        
        return array_slice($recommendations, 0, $limit);
    }
    
    /**
     * Get compliance calendar data
     * 
     * @param int $userId
     * @param int $month
     * @param int $year
     * @return array
     */
    public function getComplianceCalendar(int $userId, int $month = null, int $year = null): array
    {
        if (!$month) $month = date('n');
        if (!$year) $year = date('Y');
        
        $startDate = date('Y-m-01', strtotime("$year-$month-01"));
        $endDate = date('Y-m-t', strtotime($startDate));
        
        $sql = "SELECT 
                    t.*,
                    DATE(t.due_date) as event_date,
                    t.title as event_title,
                    t.status as event_status
                FROM compliance_tasks t
                WHERE t.deleted_at IS NULL
                AND t.assigned_to = :user_id
                AND t.due_date BETWEEN :start_date AND :end_date
                ORDER BY t.due_date ASC";
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute([
            'user_id' => $userId,
            'start_date' => $startDate,
            'end_date' => $endDate
        ]);
        
        $tasks = $stmt->fetchAll(PDO::FETCH_OBJ);
        
        $calendar = [];
        foreach ($tasks as $task) {
            $date = $task->event_date;
            if (!isset($calendar[$date])) {
                $calendar[$date] = [];
            }
            $calendar[$date][] = [
                'id' => $task->id,
                'title' => $task->event_title,
                'status' => $task->event_status,
                'priority' => $task->priority
            ];
        }
        
        return $calendar;
    }
    
    /**
     * Get compliance trend
     * 
     * @param int $months
     * @return array
     */
    public function getComplianceTrend(int $months = 12): array
    {
        $data = [];
        
        for ($i = $months - 1; $i >= 0; $i--) {
            $startDate = date('Y-m-01', strtotime("-$i months"));
            $endDate = date('Y-m-t', strtotime($startDate));
            
            $sql = "SELECT 
                        COUNT(*) as total,
                        SUM(CASE WHEN status = 'completed' THEN 1 ELSE 0 END) as completed
                    FROM compliance_tasks
                    WHERE created_at BETWEEN :start_date AND :end_date
                    AND deleted_at IS NULL";
            
            $stmt = $this->db->prepare($sql);
            $stmt->execute([
                'start_date' => $startDate . ' 00:00:00',
                'end_date' => $endDate . ' 23:59:59'
            ]);
            $row = $stmt->fetch(PDO::FETCH_OBJ);
            
            $completionRate = $row && $row->total > 0 
                ? round(($row->completed / $row->total) * 100, 2) 
                : 0;
            
            $data[] = [
                'month' => date('M Y', strtotime($startDate)),
                'total' => (int)($row->total ?? 0),
                'completed' => (int)($row->completed ?? 0),
                'completion_rate' => $completionRate
            ];
        }
        
        return $data;
    }
    
    /**
     * Get compliance evidence
     * 
     * @param int $taskId
     * @return array
     */
    public function getEvidence(int $taskId): array
    {
        $sql = "SELECT e.*, 
                       CONCAT(u.first_name, ' ', u.last_name) as uploaded_by_name
                FROM compliance_evidence e
                LEFT JOIN users u ON u.id = e.uploaded_by
                WHERE e.task_id = :task_id
                AND e.deleted_at IS NULL
                ORDER BY e.created_at DESC";
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute(['task_id' => $taskId]);
        
        return $stmt->fetchAll(PDO::FETCH_OBJ);
    }
    
    /**
     * Verify compliance evidence
     * 
     * @param int $evidenceId
     * @param int $userId
     * @param string $status
     * @param string $notes
     * @return bool
     */
    public function verifyEvidence(int $evidenceId, int $userId, string $status, string $notes = ''): bool
    {
        $allowedStatuses = ['verified', 'rejected'];
        
        if (!in_array($status, $allowedStatuses)) {
            throw new Exception('Invalid verification status.');
        }
        
        $sql = "UPDATE compliance_evidence 
                SET status = :status, 
                    verified_by = :user_id, 
                    verification_date = NOW(),
                    verification_notes = :notes
                WHERE id = :id";
        
        $stmt = $this->db->prepare($sql);
        return $stmt->execute([
            'status' => $status,
            'user_id' => $userId,
            'notes' => $notes,
            'id' => $evidenceId
        ]);
    }
    
    /**
     * Upload compliance evidence
     * 
     * @param int $taskId
     * @param array $file
     * @param string $description
     * @param int $userId
     * @return int|false
     */
    public function uploadEvidence(int $taskId, array $file, string $description, int $userId)
    {
        // Validate file
        $maxSize = compliance_setting('max_evidence_size') * 1024 * 1024;
        $allowedTypes = compliance_setting('allowed_evidence_types', ['pdf', 'doc', 'docx']);
        
        if ($file['size'] > $maxSize) {
            throw new Exception('File size exceeds maximum allowed.');
        }
        
        $extension = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
        if (!in_array($extension, $allowedTypes)) {
            throw new Exception('File type not allowed.');
        }
        
        // Generate unique filename
        $filename = 'evidence_' . $taskId . '_' . date('Ymd_His') . '_' . uniqid() . '.' . $extension;
        $uploadPath = UPLOADS_PATH . '/compliance/evidence/' . $taskId . '/';
        
        if (!is_dir($uploadPath)) {
            mkdir($uploadPath, 0755, true);
        }
        
        $targetPath = $uploadPath . $filename;
        
        if (!move_uploaded_file($file['tmp_name'], $targetPath)) {
            throw new Exception('Failed to upload file.');
        }
        
        $evidenceData = [
            'task_id' => $taskId,
            'file_path' => 'compliance/evidence/' . $taskId . '/' . $filename,
            'file_name' => $file['name'],
            'file_type' => $extension,
            'file_size' => $file['size'],
            'description' => $description,
            'uploaded_by' => $userId,
            'status' => 'pending',
            'created_at' => date('Y-m-d H:i:s')
        ];
        
        return $this->evidenceModel->create($evidenceData);
    }
}