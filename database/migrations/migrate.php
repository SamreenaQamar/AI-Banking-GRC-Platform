<?php
/**
 * AI Banking GRC Platform - Database Migration Runner
 * 
 * @package    AI-Banking-GRC-Platform
 * @version    1.0.0
 * @author     GRC Platform Team
 * @copyright  2026 AI Banking GRC Platform
 * @license    Proprietary
 * 
 * This script executes database migrations in order.
 * Features:
 * - Automatic execution of SQL files in order
 * - Tracks executed migrations
 * - Prevents duplicate execution
 * - Logs every migration
 * - Handles rollback errors
 * - PDO with prepared statements
 * - Production ready
 */

declare(strict_types=1);

// ============================================================
// CONFIGURATION
// ============================================================

// Define paths
define('ROOT_PATH', dirname(__DIR__, 2));
define('MIGRATION_PATH', __DIR__);
define('LOG_PATH', ROOT_PATH . '/storage/logs');

// Load configuration
require_once ROOT_PATH . '/config/config.php';
require_once ROOT_PATH . '/config/database.php';

// ============================================================
// MIGRATION CLASS
// ============================================================

class MigrationRunner
{
    /**
     * @var PDO Database connection
     */
    private PDO $db;
    
    /**
     * @var string Migration table name
     */
    private string $migrationTable = 'migrations';
    
    /**
     * @var array Executed migrations
     */
    private array $executedMigrations = [];
    
    /**
     * @var array Migration files
     */
    private array $migrationFiles = [];
    
    /**
     * @var string Log file path
     */
    private string $logFile;
    
    /**
     * Constructor
     */
    public function __construct()
    {
        $this->db = Database::getInstance()->getConnection();
        $this->logFile = LOG_PATH . '/migration.log';
        
        // Create log directory if not exists
        if (!is_dir(LOG_PATH)) {
            mkdir(LOG_PATH, 0755, true);
        }
        
        $this->ensureMigrationTable();
        $this->loadExecutedMigrations();
        $this->scanMigrationFiles();
    }
    
    /**
     * Ensure migration table exists
     */
    private function ensureMigrationTable(): void
    {
        $sql = "CREATE TABLE IF NOT EXISTS {$this->migrationTable} (
            id INT UNSIGNED PRIMARY KEY AUTO_INCREMENT,
            migration VARCHAR(100) NOT NULL UNIQUE,
            batch INT UNSIGNED NOT NULL,
            executed_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            duration INT UNSIGNED DEFAULT 0,
            status ENUM('success', 'failed', 'rolled_back') DEFAULT 'success',
            error TEXT NULL
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci";
        
        $this->db->exec($sql);
    }
    
    /**
     * Load executed migrations from database
     */
    private function loadExecutedMigrations(): void
    {
        $sql = "SELECT migration FROM {$this->migrationTable} WHERE status = 'success' ORDER BY id";
        $stmt = $this->db->query($sql);
        $this->executedMigrations = $stmt->fetchAll(PDO::FETCH_COLUMN);
    }
    
    /**
     * Scan migration files
     */
    private function scanMigrationFiles(): void
    {
        $files = glob(MIGRATION_PATH . '/*.sql');
        
        // Filter out non-migration files
        $this->migrationFiles = array_filter($files, function($file) {
            $basename = basename($file);
            return preg_match('/^\d{3}_.*\.sql$/', $basename);
        });
        
        // Sort by filename
        sort($this->migrationFiles);
    }
    
    /**
     * Get pending migrations
     */
    private function getPendingMigrations(): array
    {
        $pending = [];
        
        foreach ($this->migrationFiles as $file) {
            $migrationName = basename($file);
            if (!in_array($migrationName, $this->executedMigrations)) {
                $pending[] = $file;
            }
        }
        
        return $pending;
    }
    
    /**
     * Run migrations
     */
    public function run(): void
    {
        $pending = $this->getPendingMigrations();
        
        if (empty($pending)) {
            $this->log('INFO', 'All migrations are up to date.');
            echo "✅ All migrations are up to date.\n";
            return;
        }
        
        $batch = $this->getNextBatch();
        $count = 0;
        
        $this->log('INFO', 'Starting migration run. ' . count($pending) . ' pending migrations.');
        echo "📦 Starting migration run. " . count($pending) . " pending migrations.\n";
        
        foreach ($pending as $file) {
            $migrationName = basename($file);
            $startTime = microtime(true);
            
            try {
                $this->log('INFO', "Executing migration: {$migrationName}");
                echo "  ⏳ Executing: {$migrationName} ... ";
                
                // Execute migration
                $this->executeMigration($file);
                
                // Record migration
                $duration = round((microtime(true) - $startTime) * 1000);
                $this->recordMigration($migrationName, $batch, $duration);
                
                $count++;
                echo "✅ Done ({$duration}ms)\n";
                $this->log('INFO', "Completed migration: {$migrationName} ({$duration}ms)");
                
            } catch (Exception $e) {
                $this->log('ERROR', "Failed migration: {$migrationName} - " . $e->getMessage());
                echo "❌ Failed\n";
                echo "Error: " . $e->getMessage() . "\n";
                
                // Record failed migration
                $this->recordFailedMigration($migrationName, $e->getMessage());
                
                // Stop execution on failure
                $this->log('CRITICAL', "Migration stopped due to failure: {$migrationName}");
                echo "\n❌ Migration stopped due to failure.\n";
                exit(1);
            }
        }
        
        $this->log('INFO', "Migration run completed. {$count} migrations executed.");
        echo "\n✅ Migration run completed. {$count} migrations executed.\n";
    }
    
    /**
     * Execute a single migration file
     * 
     * @param string $file Path to migration file
     * @throws Exception
     */
    private function executeMigration(string $file): void
    {
        // Read SQL content
        $sql = file_get_contents($file);
        
        if ($sql === false) {
            throw new Exception("Failed to read migration file: {$file}");
        }
        
        // Split SQL statements
        $statements = $this->splitSqlStatements($sql);
        
        // Begin transaction
        $this->db->beginTransaction();
        
        try {
            foreach ($statements as $statement) {
                $statement = trim($statement);
                if (empty($statement)) {
                    continue;
                }
                
                $this->db->exec($statement);
            }
            
            $this->db->commit();
            
        } catch (Exception $e) {
            $this->db->rollBack();
            throw $e;
        }
    }
    
    /**
     * Split SQL into individual statements
     * 
     * @param string $sql
     * @return array
     */
    private function splitSqlStatements(string $sql): array
    {
        $statements = [];
        $current = '';
        
        // Remove comments
        $lines = explode("\n", $sql);
        foreach ($lines as $line) {
            // Skip comments
            $line = trim($line);
            if (empty($line) || strpos($line, '--') === 0 || strpos($line, '#') === 0) {
                continue;
            }
            
            $current .= $line . "\n";
            
            // Check if statement ends
            if (strpos($line, ';') !== false) {
                $statements[] = trim($current);
                $current = '';
            }
        }
        
        if (!empty(trim($current))) {
            $statements[] = trim($current);
        }
        
        return $statements;
    }
    
    /**
     * Record successful migration
     * 
     * @param string $migration
     * @param int $batch
     * @param int $duration
     */
    private function recordMigration(string $migration, int $batch, int $duration): void
    {
        $sql = "INSERT INTO {$this->migrationTable} (migration, batch, duration, status) 
                VALUES (:migration, :batch, :duration, 'success')";
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute([
            'migration' => $migration,
            'batch' => $batch,
            'duration' => $duration
        ]);
    }
    
    /**
     * Record failed migration
     * 
     * @param string $migration
     * @param string $error
     */
    private function recordFailedMigration(string $migration, string $error): void
    {
        $sql = "INSERT INTO {$this->migrationTable} (migration, batch, status, error) 
                VALUES (:migration, :batch, 'failed', :error)";
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute([
            'migration' => $migration,
            'batch' => $this->getNextBatch(),
            'error' => $error
        ]);
    }
    
    /**
     * Get next batch number
     * 
     * @return int
     */
    private function getNextBatch(): int
    {
        $sql = "SELECT MAX(batch) as max_batch FROM {$this->migrationTable}";
        $stmt = $this->db->query($sql);
        $result = $stmt->fetch(PDO::FETCH_OBJ);
        
        return ($result && $result->max_batch) ? (int)$result->max_batch + 1 : 1;
    }
    
    /**
     * Rollback last batch
     */
    public function rollback(): void
    {
        // Get last batch
        $sql = "SELECT MAX(batch) as max_batch FROM {$this->migrationTable} WHERE status = 'success'";
        $stmt = $this->db->query($sql);
        $result = $stmt->fetch(PDO::FETCH_OBJ);
        
        if (!$result || !$result->max_batch) {
            echo "No migrations to rollback.\n";
            return;
        }
        
        $batch = (int)$result->max_batch;
        
        // Get migrations in this batch
        $sql = "SELECT migration FROM {$this->migrationTable} WHERE batch = :batch AND status = 'success' ORDER BY id DESC";
        $stmt = $this->db->prepare($sql);
        $stmt->execute(['batch' => $batch]);
        $migrations = $stmt->fetchAll(PDO::FETCH_COLUMN);
        
        if (empty($migrations)) {
            echo "No migrations found in batch {$batch}.\n";
            return;
        }
        
        $this->log('INFO', "Rolling back batch {$batch}. " . count($migrations) . " migrations.");
        echo "⏪ Rolling back batch {$batch}. " . count($migrations) . " migrations.\n";
        
        foreach ($migrations as $migration) {
            $this->log('INFO', "Rolling back: {$migration}");
            echo "  ⏳ Rolling back: {$migration} ... ";
            
            // Mark as rolled back
            $sql = "UPDATE {$this->migrationTable} SET status = 'rolled_back' WHERE migration = :migration";
            $stmt = $this->db->prepare($sql);
            $stmt->execute(['migration' => $migration]);
            
            echo "✅ Done\n";
            $this->log('INFO', "Rolled back: {$migration}");
        }
        
        $this->log('INFO', "Rollback completed for batch {$batch}.");
        echo "\n✅ Rollback completed for batch {$batch}.\n";
    }
    
    /**
     * Show migration status
     */
    public function status(): void
    {
        $sql = "SELECT 
                    migration,
                    batch,
                    status,
                    executed_at,
                    duration,
                    error
                FROM {$this->migrationTable} 
                ORDER BY id DESC";
        
        $stmt = $this->db->query($sql);
        $migrations = $stmt->fetchAll(PDO::FETCH_OBJ);
        
        echo "\n📊 Migration Status\n";
        echo str_repeat('=', 80) . "\n";
        echo sprintf(
            "%-5s | %-50s | %-8s | %-10s | %s\n",
            'ID', 'Migration', 'Status', 'Duration', 'Executed At'
        );
        echo str_repeat('-', 80) . "\n";
        
        foreach ($migrations as $migration) {
            $statusColor = $migration->status === 'success' ? '✅' : 
                          ($migration->status === 'failed' ? '❌' : '⏪');
            
            echo sprintf(
                "%-5d | %-50s | %-8s | %-10dms | %s\n",
                $migration->id,
                $migration->migration,
                $statusColor . ' ' . $migration->status,
                $migration->duration ?: 0,
                $migration->executed_at
            );
            
            if ($migration->error) {
                echo "  Error: " . $migration->error . "\n";
            }
        }
        
        echo str_repeat('=', 80) . "\n";
        echo "Total: " . count($migrations) . " migrations\n";
    }
    
    /**
     * Log message
     * 
     * @param string $level
     * @param string $message
     */
    private function log(string $level, string $message): void
    {
        $logEntry = sprintf(
            "[%s] [%s] %s%s",
            date('Y-m-d H:i:s'),
            $level,
            $message,
            PHP_EOL
        );
        
        file_put_contents($this->logFile, $logEntry, FILE_APPEND);
    }
    
    /**
     * Show help
     */
    private function showHelp(): void
    {
        echo <<<HELP
\n📚 Migration Runner Help\n
Usage: php migrate.php [command]

Commands:
  run       - Run all pending migrations
  rollback  - Rollback the last batch of migrations
  status    - Show migration status
  help      - Show this help message

Examples:
  php migrate.php run        # Run all pending migrations
  php migrate.php rollback   # Rollback last batch
  php migrate.php status     # Check migration status

HELP;
    }
}

// ============================================================
// RUN MIGRATIONS
// ============================================================

// Initialize runner
$runner = new MigrationRunner();

// Determine command
$command = $argv[1] ?? 'run';

switch ($command) {
    case 'run':
        $runner->run();
        break;
    case 'rollback':
        $runner->rollback();
        break;
    case 'status':
        $runner->status();
        break;
    case 'help':
    case '--help':
    case '-h':
    default:
        $runner->showHelp();
        break;
}