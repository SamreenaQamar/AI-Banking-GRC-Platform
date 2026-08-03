<?php
/**
 * AI Banking GRC Platform - AI Response Model
 * 
 * @package    AI-Banking-GRC-Platform
 * @version    1.0.0
 * @author     GRC Platform Team
 * @copyright  2026 AI Banking GRC Platform
 * @license    Proprietary
 * 
 * This model handles:
 * - AI response storage
 * - Response tracking
 * - Performance metrics
 */

declare(strict_types=1);

namespace App\Models;

use PDO;

class AIResponse extends BaseModel
{
    /**
     * Table name
     * @var string
     */
    protected string $table = 'ai_responses';
    
    /**
     * Fillable fields
     * @var array
     */
    protected array $fillable = [
        'request_id',
        'response',
        'confidence',
        'model',
        'processing_time',
        'tokens_used',
        'success'
    ];
    
    /**
     * Constructor
     */
    public function __construct()
    {
        parent::__construct();
    }
    
    /**
     * Get request for response
     * 
     * @param int $responseId
     * @return object|null
     */
    public function getRequest(int $responseId): ?object
    {
        $sql = "SELECT r.* FROM ai_requests r 
                INNER JOIN ai_responses res ON res.request_id = r.id 
                WHERE res.id = :response_id";
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute(['response_id' => $responseId]);
        $result = $stmt->fetch(PDO::FETCH_OBJ);
        
        return $result ?: null;
    }
}