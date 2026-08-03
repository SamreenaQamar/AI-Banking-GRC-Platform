<?php
/**
 * AI Banking GRC Platform - AI Request Model
 * 
 * @package    AI-Banking-GRC-Platform
 * @version    1.0.0
 * @author     GRC Platform Team
 * @copyright  2026 AI Banking GRC Platform
 * @license    Proprietary
 * 
 * This model handles:
 * - AI request tracking
 * - Request timing
 * - Response correlation
 */

declare(strict_types=1);

namespace App\Models;

use PDO;

class AIRequest extends BaseModel
{
    /**
     * Table name
     * @var string
     */
    protected string $table = 'ai_requests';
    
    /**
     * Fillable fields
     * @var array
     */
    protected array $fillable = [
        'prompt',
        'module',
        'user_id',
        'tokens',
        'response_time',
        'status',
        'ip_address',
        'user_agent'
    ];
    
    /**
     * Constructor
     */
    public function __construct()
    {
        parent::__construct();
    }
    
    /**
     * Get response for request
     * 
     * @param int $requestId
     * @return object|null
     */
    public function getResponse(int $requestId): ?object
    {
        $sql = "SELECT * FROM ai_responses WHERE request_id = :request_id";
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute(['request_id' => $requestId]);
        $result = $stmt->fetch(PDO::FETCH_OBJ);
        
        return $result ?: null;
    }
}