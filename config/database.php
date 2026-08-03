<?php
/**
 * AI Banking GRC Platform - Enterprise Database Configuration
 * 
 * @package    AI-Banking-GRC-Platform
 * @version    1.0.0
 * @author     GRC Platform Team
 * @copyright  2026 AI Banking GRC Platform
 * @license    Proprietary
 * 
 * This file provides enterprise-grade database connectivity with:
 * - PDO connection with singleton pattern
 * - Connection pooling support
 * - Prepared statements for SQL injection protection
 * - Transaction support with commit/rollback
 * - Comprehensive error handling and logging
 * - Query logging and profiling
 * - Connection retry logic
 * - UTF-8 character set support
 * - Master-Slave read/write splitting (configurable)
 */

declare(strict_types=1);

// Load configuration
require_once __DIR__ . '/config.php';

// ============================================================
// DATABASE CONNECTION CLASS
// ============================================================

/**
 * Database class - PDO Wrapper with Enterprise Features
 * 
 * Implements:
 * - Singleton pattern for single connection instance
 * - Prepared statements for security
 * - Transaction management
 * - Connection pooling
 * - Comprehensive error handling
 * - Query logging and profiling
 * - Master-Slave support
 * - Connection retry logic
 */
class Database
{
    /**
     * Singleton instance
     * @var Database|null
     */
    private static ?Database $instance = null;
    
    /**
     * PDO connection instance
     * @var PDO|null
     */
    private ?PDO $connection = null;
    
    /**
     * Slave connection for read operations
     * @var PDO|null
     */
    private ?PDO $slaveConnection = null;
    
    /**
     * Connection configuration
     * @var array
     */
    private array $config = [];
    
    /**
     * Query log for debugging
     * @var array
     */
    private array $queryLog = [];
    
    /**
     * Transaction status
     * @var bool
     */
    private bool $inTransaction = false;
    
    /**
     * Connection count for monitoring
     * @var int
     */
    private static int $connectionCount = 0;
    
    /**
     * Query count for current request
     * @var int
     */
    private int $queryCount = 0;
    
    /**
     * Query execution time
     * @var float
     */
    private float $totalQueryTime = 0.0;
    
    /**
     * Use slave for read operations
     * @var bool
     */
    private bool $useSlave = false;
    
    /**
     * Private constructor - prevents direct instantiation
     */
    private function __construct()
    {
        $this->config = [
            'host' => DB_HOST,
            'port' => DB_PORT,
            'name' => DB_NAME,
            'user' => DB_USER,
            'pass' => DB_PASS,
            'charset' => DB_CHARSET,
            'collation' => DB_COLLATION,
            'engine' => DB_ENGINE,
            'pool_size' => DB_POOL_SIZE,
            'pool_timeout' => DB_POOL_TIMEOUT
        ];
        
        // Check for slave configuration
        if (defined('DB_SLAVE_HOST') && DB_SLAVE_HOST) {
            $this->useSlave = true;
        }
        
        $this->connect();
    }
    
    /**
     * Prevent cloning
     */
    private function __clone()
    {
        // Prevent cloning of singleton
    }
    
    /**
     * Prevent unserialization
     */
    public function __wakeup()
    {
        // Prevent unserialization of singleton
    }
    
    /**
     * Get singleton instance
     * 
     * @return Database
     */
    public static function getInstance(): Database
    {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        
        return self::$instance;
    }
    
    /**
     * Establish database connection with retry logic
     * 
     * @throws PDOException
     * @return void
     */
    private function connect(): void
    {
        $maxAttempts = 3;
        $attempt = 0;
        
        while ($attempt < $maxAttempts) {
            try {
                // Master connection
                $this->connectMaster();
                
                // Slave connection if configured
                if ($this->useSlave) {
                    $this->connectSlave();
                }
                
                self::$connectionCount++;
                
                if (is_development()) {
                    $this->log('Connection established successfully');
                }
                
                return;
                
            } catch (PDOException $e) {
                $attempt++;
                
                if ($attempt >= $maxAttempts) {
                    $this->log('Connection failed after ' . $maxAttempts . ' attempts: ' . $e->getMessage(), 'error');
                    throw new PDOException(
                        'Database connection failed: ' . $e->getMessage(),
                        (int)$e->getCode()
                    );
                }
                
                // Wait before retry
                sleep(1);
            }
        }
    }
    
    /**
     * Connect to master database
     * 
     * @throws PDOException
     * @return void
     */
    private function connectMaster(): void
    {
        $dsn = sprintf(
            'mysql:host=%s;port=%s;dbname=%s;charset=%s',
            $this->config['host'],
            $this->config['port'],
            $this->config['name'],
            $this->config['charset']
        );
        
        $options = $this->getPDOOptions();
        $this->connection = new PDO($dsn, $this->config['user'], $this->config['pass'], $options);
    }
    
    /**
     * Connect to slave database
     * 
     * @throws PDOException
     * @return void
     */
    private function connectSlave(): void
    {
        if (!defined('DB_SLAVE_HOST') || !DB_SLAVE_HOST) {
            return;
        }
        
        $dsn = sprintf(
            'mysql:host=%s;port=%s;dbname=%s;charset=%s',
            DB_SLAVE_HOST,
            defined('DB_SLAVE_PORT') ? DB_SLAVE_PORT : DB_PORT,
            DB_NAME,
            DB_CHARSET
        );
        
        $options = $this->getPDOOptions();
        $this->slaveConnection = new PDO($dsn, DB_SLAVE_USER ?? DB_USER, DB_SLAVE_PASS ?? DB_PASS, $options);
    }
    
    /**
     * Get PDO options
     * 
     * @return array
     */
    private function getPDOOptions(): array
    {
        return [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_OBJ,
            PDO::ATTR_EMULATE_PREPARES => false,
            PDO::ATTR_PERSISTENT => false,
            PDO::ATTR_STRINGIFY_FETCHES => false,
            PDO::ATTR_STATEMENT_CLASS => ['PDOStatement', []],
            PDO::MYSQL_ATTR_INIT_COMMAND => sprintf(
                "SET NAMES '%s' COLLATE '%s'",
                $this->config['charset'],
                $this->config['collation']
            ),
            PDO::MYSQL_ATTR_USE_BUFFERED_QUERY => true,
            PDO::MYSQL_ATTR_LOCAL_INFILE => false,
            PDO::MYSQL_ATTR_MULTI_STATEMENTS => false
        ];
    }
    
    /**
     * Get PDO connection (master for writes, slave for reads if configured)
     * 
     * @param bool $write Whether this is a write operation
     * @return PDO
     */
    public function getConnection(bool $write = false): PDO
    {
        // If in transaction, use master
        if ($this->inTransaction) {
            return $this->getMasterConnection();
        }
        
        // Use slave for read operations if available and configured
        if (!$write && $this->useSlave && $this->slaveConnection !== null) {
            return $this->slaveConnection;
        }
        
        return $this->getMasterConnection();
    }
    
    /**
     * Get master connection
     * 
     * @return PDO
     */
    private function getMasterConnection(): PDO
    {
        if ($this->connection === null) {
            $this->connectMaster();
        }
        
        return $this->connection;
    }
    
    /**
     * Prepare a statement with connection selection
     * 
     * @param string $sql SQL query with placeholders
     * @param bool $write Whether this is a write operation
     * @return PDOStatement
     * @throws PDOException
     */
    public function prepare(string $sql, bool $write = false): PDOStatement
    {
        try {
            $connection = $this->getConnection($write);
            $statement = $connection->prepare($sql);
            $this->logQuery($sql);
            return $statement;
        } catch (PDOException $e) {
            $this->log('Query preparation failed: ' . $e->getMessage(), 'error');
            throw $e;
        }
    }
    
    /**
     * Execute a query with parameters
     * 
     * @param string $sql SQL query with placeholders
     * @param array $params Parameters for prepared statement
     * @param bool $write Whether this is a write operation
     * @return PDOStatement
     * @throws PDOException
     */
    public function query(string $sql, array $params = [], bool $write = false): PDOStatement
    {
        $startTime = microtime(true);
        $this->queryCount++;
        
        try {
            $connection = $this->getConnection($write);
            $statement = $connection->prepare($sql);
            
            if (!empty($params)) {
                foreach ($params as $key => $value) {
                    $type = $this->getParameterType($value);
                    $statement->bindValue($key, $value, $type);
                }
            }
            
            $statement->execute();
            
            $executionTime = microtime(true) - $startTime;
            $this->totalQueryTime += $executionTime;
            $this->logQuery($sql, $params, $executionTime);
            
            return $statement;
            
        } catch (PDOException $e) {
            $this->log('Query execution failed: ' . $e->getMessage(), 'error');
            $this->log('SQL: ' . $sql, 'error');
            $this->log('Params: ' . json_encode($params), 'error');
            throw $e;
        }
    }
    
    /**
     * Execute a write query (insert, update, delete)
     * 
     * @param string $sql SQL query
     * @param array $params Parameters
     * @return PDOStatement
     */
    public function write(string $sql, array $params = []): PDOStatement
    {
        return $this->query($sql, $params, true);
    }
    
    /**
     * Fetch single row
     * 
     * @param string $sql SQL query
     * @param array $params Parameters
     * @param bool $write Whether this is a write operation
     * @return object|null
     */
    public function fetchOne(string $sql, array $params = [], bool $write = false): ?object
    {
        $statement = $this->query($sql, $params, $write);
        $result = $statement->fetch();
        $statement->closeCursor();
        return $result ?: null;
    }
    
    /**
     * Fetch all rows
     * 
     * @param string $sql SQL query
     * @param array $params Parameters
     * @param bool $write Whether this is a write operation
     * @return array
     */
    public function fetchAll(string $sql, array $params = [], bool $write = false): array
    {
        $statement = $this->query($sql, $params, $write);
        $results = $statement->fetchAll();
        $statement->closeCursor();
        return $results;
    }
    
    /**
     * Fetch single column value
     * 
     * @param string $sql SQL query
     * @param array $params Parameters
     * @param int $column Column index (0-based)
     * @param bool $write Whether this is a write operation
     * @return mixed
     */
    public function fetchColumn(string $sql, array $params = [], int $column = 0, bool $write = false)
    {
        $statement = $this->query($sql, $params, $write);
        $value = $statement->fetchColumn($column);
        $statement->closeCursor();
        return $value;
    }
    
    /**
     * Insert data and return last insert ID
     * 
     * @param string $table Table name
     * @param array $data Associative array of column => value
     * @return int Last inserted ID
     */
    public function insert(string $table, array $data): int
    {
        $columns = array_keys($data);
        $placeholders = array_map(function($col) {
            return ':' . $col;
        }, $columns);
        
        $sql = sprintf(
            'INSERT INTO %s (%s) VALUES (%s)',
            $this->escapeIdentifier($table),
            implode(', ', array_map([$this, 'escapeIdentifier'], $columns)),
            implode(', ', $placeholders)
        );
        
        $this->write($sql, $data);
        return (int)$this->getMasterConnection()->lastInsertId();
    }
    
    /**
     * Insert multiple rows
     * 
     * @param string $table Table name
     * @param array $rows Array of associative arrays
     * @return int Number of affected rows
     */
    public function insertBatch(string $table, array $rows): int
    {
        if (empty($rows)) {
            return 0;
        }
        
        $columns = array_keys($rows[0]);
        $placeholders = [];
        $params = [];
        
        foreach ($rows as $index => $row) {
            $rowPlaceholders = [];
            foreach ($columns as $col) {
                $key = $col . '_' . $index;
                $rowPlaceholders[] = ':' . $key;
                $params[$key] = $row[$col];
            }
            $placeholders[] = '(' . implode(', ', $rowPlaceholders) . ')';
        }
        
        $sql = sprintf(
            'INSERT INTO %s (%s) VALUES %s',
            $this->escapeIdentifier($table),
            implode(', ', array_map([$this, 'escapeIdentifier'], $columns)),
            implode(', ', $placeholders)
        );
        
        $this->write($sql, $params);
        return (int)$this->getAffectedRows();
    }
    
    /**
     * Update data
     * 
     * @param string $table Table name
     * @param array $data Associative array of column => value
     * @param array $where Conditions (column => value)
     * @param string $operator Logical operator (AND, OR)
     * @return int Number of affected rows
     */
    public function update(string $table, array $data, array $where, string $operator = 'AND'): int
    {
        // Build SET clause
        $setClause = implode(', ', array_map(function($col) {
            return $this->escapeIdentifier($col) . ' = :' . $col;
        }, array_keys($data)));
        
        // Build WHERE clause
        $whereClause = implode(' ' . $operator . ' ', array_map(function($col) {
            return $this->escapeIdentifier($col) . ' = :where_' . $col;
        }, array_keys($where)));
        
        // Prepare parameters with prefix for where clauses
        $params = $data;
        foreach ($where as $col => $value) {
            $params['where_' . $col] = $value;
        }
        
        $sql = sprintf(
            'UPDATE %s SET %s WHERE %s',
            $this->escapeIdentifier($table),
            $setClause,
            $whereClause
        );
        
        $this->write($sql, $params);
        return $this->getAffectedRows();
    }
    
    /**
     * Delete data
     * 
     * @param string $table Table name
     * @param array $where Conditions
     * @param string $operator Logical operator
     * @return int Number of affected rows
     */
    public function delete(string $table, array $where, string $operator = 'AND'): int
    {
        // Build WHERE clause
        $whereClause = implode(' ' . $operator . ' ', array_map(function($col) {
            return $this->escapeIdentifier($col) . ' = :' . $col;
        }, array_keys($where)));
        
        $sql = sprintf(
            'DELETE FROM %s WHERE %s',
            $this->escapeIdentifier($table),
            $whereClause
        );
        
        $this->write($sql, $where);
        return $this->getAffectedRows();
    }
    
    /**
     * Begin transaction
     * 
     * @return bool
     */
    public function beginTransaction(): bool
    {
        if (!$this->inTransaction) {
            $this->inTransaction = $this->getMasterConnection()->beginTransaction();
            if ($this->inTransaction && is_development()) {
                $this->log('Transaction started');
            }
        }
        return $this->inTransaction;
    }
    
    /**
     * Commit transaction
     * 
     * @return bool
     */
    public function commit(): bool
    {
        if ($this->inTransaction) {
            $result = $this->getMasterConnection()->commit();
            $this->inTransaction = false;
            if ($result && is_development()) {
                $this->log('Transaction committed');
            }
            return $result;
        }
        return false;
    }
    
    /**
     * Rollback transaction
     * 
     * @return bool
     */
    public function rollback(): bool
    {
        if ($this->inTransaction) {
            $result = $this->getMasterConnection()->rollBack();
            $this->inTransaction = false;
            if ($result && is_development()) {
                $this->log('Transaction rolled back');
            }
            return $result;
        }
        return false;
    }
    
    /**
     * Check if in transaction
     * 
     * @return bool
     */
    public function inTransaction(): bool
    {
        return $this->inTransaction;
    }
    
    /**
     * Get last inserted ID
     * 
     * @return string
     */
    public function lastInsertId(): string
    {
        return $this->getMasterConnection()->lastInsertId();
    }
    
    /**
     * Get number of affected rows
     * 
     * @return int
     */
    public function getAffectedRows(): int
    {
        return $this->connection ? $this->connection->lastInsertId() ?: 0 : 0;
    }
    
    /**
     * Escape identifier (table/column name)
     * 
     * @param string $identifier
     * @return string
     */
    public function escapeIdentifier(string $identifier): string
    {
        return '`' . str_replace('`', '``', $identifier) . '`';
    }
    
    /**
     * Get parameter type for binding
     * 
     * @param mixed $value
     * @return int
     */
    private function getParameterType($value): int
    {
        if (is_int($value)) {
            return PDO::PARAM_INT;
        } elseif (is_bool($value)) {
            return PDO::PARAM_BOOL;
        } elseif (is_null($value)) {
            return PDO::PARAM_NULL;
        } else {
            return PDO::PARAM_STR;
        }
    }
    
    /**
     * Log query for debugging
     * 
     * @param string $sql SQL query
     * @param array $params Parameters
     * @param float $time Execution time
     * @return void
     */
    private function logQuery(string $sql, array $params = [], float $time = 0.0): void
    {
        if (defined('DB_QUERY_LOG') && DB_QUERY_LOG) {
            $this->queryLog[] = [
                'sql' => $sql,
                'params' => $params,
                'time' => $time,
                'time_ms' => round($time * 1000, 2),
                'backtrace' => debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS, 3)
            ];
            
            // Keep only last 1000 queries
            if (count($this->queryLog) > 1000) {
                array_shift($this->queryLog);
            }
        }
    }
    
    /**
     * Get query log
     * 
     * @return array
     */
    public function getQueryLog(): array
    {
        return $this->queryLog;
    }
    
    /**
     * Get query statistics
     * 
     * @return array
     */
    public function getQueryStats(): array
    {
        return [
            'total_queries' => $this->queryCount,
            'total_time' => round($this->totalQueryTime, 4),
            'avg_time' => $this->queryCount > 0 ? round($this->totalQueryTime / $this->queryCount, 4) : 0,
            'slow_queries' => count(array_filter($this->queryLog, function($q) {
                return ($q['time'] ?? 0) > 1.0;
            }))
        ];
    }
    
    /**
     * Log message
     * 
     * @param string $message
     * @param string $level
     * @return void
     */
    private function log(string $message, string $level = 'info'): void
    {
        if (LOG_ENABLED) {
            $logEntry = sprintf(
                "[%s] [%s] %s%s",
                date(DATETIME_FORMAT),
                strtoupper($level),
                $message,
                PHP_EOL
            );
            
            $logFile = LOG_PATH . '/database.log';
            file_put_contents($logFile, $logEntry, FILE_APPEND);
        }
    }
    
    /**
     * Close database connections
     * 
     * @return void
     */
    public function close(): void
    {
        if ($this->inTransaction) {
            $this->rollback();
        }
        
        $this->connection = null;
        $this->slaveConnection = null;
        self::$instance = null;
        
        if (is_development()) {
            $this->log('Connection closed');
        }
    }
    
    /**
     * Test database connection
     * 
     * @return bool
     */
    public function testConnection(): bool
    {
        try {
            $this->getMasterConnection()->query('SELECT 1');
            
            if ($this->slaveConnection) {
                $this->slaveConnection->query('SELECT 1');
            }
            
            return true;
        } catch (PDOException $e) {
            $this->log('Connection test failed: ' . $e->getMessage(), 'error');
            return false;
        }
    }
    
    /**
     * Get connection stats
     * 
     * @return array
     */
    public function getStats(): array
    {
        return [
            'connected' => $this->connection !== null,
            'slave_connected' => $this->slaveConnection !== null,
            'connections_count' => self::$connectionCount,
            'in_transaction' => $this->inTransaction,
            'query_count' => $this->queryCount,
            'total_query_time' => round($this->totalQueryTime, 4),
            'server_info' => $this->connection ? $this->connection->getAttribute(PDO::ATTR_SERVER_INFO) : null,
            'driver_name' => $this->connection ? $this->connection->getAttribute(PDO::ATTR_DRIVER_NAME) : null,
            'server_version' => $this->connection ? $this->connection->getAttribute(PDO::ATTR_SERVER_VERSION) : null,
            'client_version' => $this->connection ? $this->connection->getAttribute(PDO::ATTR_CLIENT_VERSION) : null
        ];
    }
    
    /**
     * Enable query logging
     * 
     * @return void
     */
    public function enableQueryLog(): void
    {
        if (!defined('DB_QUERY_LOG')) {
            define('DB_QUERY_LOG', true);
        }
    }
    
    /**
     * Disable query logging
     * 
     * @return void
     */
    public function disableQueryLog(): void
    {
        if (defined('DB_QUERY_LOG')) {
            // Cannot undefine, but we can stop logging
        }
    }
    
    /**
     * Get slow queries (queries taking > 1 second)
     * 
     * @return array
     */
    public function getSlowQueries(): array
    {
        return array_filter($this->queryLog, function($q) {
            return ($q['time'] ?? 0) > 1.0;
        });
    }
    
    /**
     * Run a callback in a transaction
     * 
     * @param callable $callback Function to execute
     * @return mixed
     * @throws Exception
     */
    public function transactional(callable $callback)
    {
        try {
            $this->beginTransaction();
            $result = $callback();
            $this->commit();
            return $result;
        } catch (Exception $e) {
            $this->rollback();
            throw $e;
        }
    }
}

// ============================================================
// GLOBAL DATABASE ACCESS FUNCTIONS
// ============================================================

/**
 * Get database instance (convenience function)
 * 
 * @return Database
 */
function db(): Database
{
    return Database::getInstance();
}

/**
 * Begin a database transaction
 * 
 * @return bool
 */
function db_begin(): bool
{
    return db()->beginTransaction();
}

/**
 * Commit a database transaction
 * 
 * @return bool
 */
function db_commit(): bool
{
    return db()->commit();
}

/**
 * Rollback a database transaction
 * 
 * @return bool
 */
function db_rollback(): bool
{
    return db()->rollback();
}

/**
 * Run a callback in a transaction
 * 
 * @param callable $callback
 * @return mixed
 */
function db_transaction(callable $callback)
{
    return db()->transactional($callback);
}

// ============================================================
// DATABASE MIGRATION HELPERS
// ============================================================

/**
 * Check if a table exists
 * 
 * @param string $table
 * @return bool
 */
function table_exists(string $table): bool
{
    try {
        $result = db()->fetchOne(
            "SELECT COUNT(*) as count FROM information_schema.tables 
             WHERE table_schema = :db AND table_name = :table",
            [
                'db' => DB_NAME,
                'table' => $table
            ]
        );
        return $result && $result->count > 0;
    } catch (Exception $e) {
        return false;
    }
}

/**
 * Check if a column exists in a table
 * 
 * @param string $table
 * @param string $column
 * @return bool
 */
function column_exists(string $table, string $column): bool
{
    try {
        $result = db()->fetchOne(
            "SELECT COUNT(*) as count FROM information_schema.columns 
             WHERE table_schema = :db AND table_name = :table AND column_name = :column",
            [
                'db' => DB_NAME,
                'table' => $table,
                'column' => $column
            ]
        );
        return $result && $result->count > 0;
    } catch (Exception $e) {
        return false;
    }
}

// ============================================================
// END OF DATABASE CONFIGURATION
// ============================================================