<?php
/**
 * AI Banking GRC Platform - SBP Circular Model
 * 
 * @package    AI-Banking-GRC-Platform
 * @version    1.0.0
 * @author     GRC Platform Team
 * @copyright  2026 AI Banking GRC Platform
 * @license    Proprietary
 * 
 * This model handles:
 * - SBP circular management
 * - Compliance tracking
 * - AI analysis integration
 * - Checklist generation
 */

declare(strict_types=1);

namespace App\Models;

use PDO;

class SBPCircular extends BaseModel
{
    /**
     * Table name
     * @var string
     */
    protected string $table = 'sbp_circulars';
    
    /**
     * Fillable fields
     * @var array
     */
    protected array $fillable = [
        'circular_number',
        'title',
        'description',
        'category',
        'priority',
        'issuance_date',
        'effective_date',
        'compliance_deadline',
        'document_path',
        'document_type',
        'status',
        'implemented_by',
        'implementation_date',
        'implementation_notes',
        'supersedes_circular_id',
        'ai_summary',
        'ai_analysis'
    ];
    
    /**
     * Constructor
     */
    public function __construct()
    {
        parent::__construct();
    }
    
    /**
     * Upload circular document
     * 
     * @param int $circularId
     * @param string $filePath
     * @param string $fileType
     * @return bool
     */
    public function uploadCircular(int $circularId, string $filePath, string $fileType): bool
    {
        return $this->update($circularId, [
            'document_path' => $filePath,
            'document_type' => $fileType
        ]);
    }
    
    /**
     * Analyze circular with AI
     * 
     * @param int $circularId
     * @param string $analysis
     * @param string $summary
     * @return bool
     */
    public function analyze(int $circularId, string $analysis, string $summary): bool
    {
        return $this->update($circularId, [
            'ai_analysis' => $analysis,
            'ai_summary' => $summary,
            'ai_analyzed_at' => date('Y-m-d H:i:s')
        ]);
    }
    
    /**
     * Extract controls from circular
     * 
     * @param int $circularId
     * @return array
     */
    public function extractControls(int $circularId): array
    {
        $sql = "SELECT * FROM sbp_controls 
                WHERE circular_id = :circular_id 
                AND deleted_at IS NULL";
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute(['circular_id' => $circularId]);
        
        return $stmt->fetchAll(PDO::FETCH_OBJ);
    }
    
    /**
     * Generate compliance checklist
     * 
     * @param int $circularId
     * @return array
     */
    public function generateChecklist(int $circularId): array
    {
        $controls = $this->extractControls($circularId);
        $checklist = [];
        
        foreach ($controls as $control) {
            $checklist[] = [
                'id' => $control->id,
                'control' => $control->description,
                'status' => 'pending',
                'notes' => '',
                'completed_at' => null
            ];
        }
        
        return $checklist;
    }
    
    /**
     * Implement circular
     * 
     * @param int $circularId
     * @param int $userId
     * @param string $notes
     * @return bool
     */
    public function implement(int $circularId, int $userId, string $notes): bool
    {
        return $this->update($circularId, [
            'status' => SBP_STATUS_IMPLEMENTED,
            'implemented_by' => $userId,
            'implementation_date' => date('Y-m-d'),
            'implementation_notes' => $notes
        ]);
    }
    
    /**
     * Get active circulars
     * 
     * @return array
     */
    public function getActive(): array
    {
        return $this->where(['status' => SBP_STATUS_ACTIVE]);
    }
    
    /**
     * Get pending circulars
     * 
     * @return array
     */
    public function getPending(): array
    {
        return $this->where(['status' => SBP_STATUS_PENDING]);
    }
    
    /**
     * Get circulars by category
     * 
     * @param string $category
     * @return array
     */
    public function getByCategory(string $category): array
    {
        return $this->where(['category' => $category]);
    }
    
    /**
     * Get circulars by priority
     * 
     * @param string $priority
     * @return array
     */
    public function getByPriority(string $priority): array
    {
        return $this->where(['priority' => $priority]);
    }
    
    /**
     * Get compliance rate
     * 
     * @return float
     */
    public function getComplianceRate(): float
    {
        $total = $this->count();
        $implemented = $this->count(['status' => SBP_STATUS_IMPLEMENTED]);
        
        if ($total === 0) {
            return 100.0;
        }
        
        return round(($implemented / $total) * 100, 2);
    }
}