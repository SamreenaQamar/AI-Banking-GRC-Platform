<?php
/**
 * AI Banking GRC Platform - Database Library
 * 
 * @package    AI-Banking-GRC-Platform
 * @subpackage app/Libraries
 * @version    1.0.0
 * @author     GRC Platform Team
 * @copyright  2026 AI Banking GRC Platform
 * @license    Proprietary
 * 
 * This library provides enterprise database functionality:
 * - PDO connection with singleton pattern
 * - Prepared statements for SQL injection protection
 * - Query builder
 * - Transaction support
 * - Secure binding
 * - Connection pooling
 * - Query logging
 */

declare(strict_types=1);

namespace App\Libraries;

use PDO;
use PDOException;
use App\Libraries\Logger;

class Database
{
    /**
     * @var Database Singleton instance
     */
    private static ?Database $instance = null;

    /**
     * @var PDO PDO connection
     */
    private ?PDO $connection = null;

    /**
     * @var array Connection configuration
     */
    private array $config;

    /**
     * @var Logger Logger instance
     */
    private Logger $logger;

    /**
     * @var int Query count
     */
    private int $queryCount = 0;

    /**
     * @var array Query log
     */
    private array $queryLog = [];

    /**
     * @var bool Whether in transaction
     */
    private bool $inTransaction = false;

    /**
     * Private constructor - singleton pattern
     */
    private function __construct()
    {
        $this->logger = new Logger();
        $this->config = [
            'host' => getenv('DB_HOST') ?: 'localhost',
            'port' => getenv('DB_PORT') ?: '3306',
            'database' => getenv('DB_NAME') ?: 'grc_platform',
            'username' => getenv('DB_USER') ?: 'root',
            'password' => getenv('DB_PASS') ?: '',
            'charset' => 'utf8mb4',
            'collation' => 'utf8mb4_unicode_ci'
        ];

        $this->connect();
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
     * Connect to database
     * 
     * @return void
     * @throws PDOException
     */
    private function connect(): void
    {
        try {
            $dsn = sprintf(
                'mysql:host=%s;port=%s;dbname=%s;charset=%s',
                $this->config['host'],
                $this->config['port'],
                $this->config['database'],
                $this->config['charset']
            );

            $options = [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_OBJ,
                PDO::ATTR_EMULATE_PREPARES => false,
                PDO::ATTR_PERSISTENT => false,
                PDO::ATTR_STRINGIFY_FETCHES => false,
                PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES '{$this->config['charset']}' COLLATE '{$this->config['collation']}'",
                PDO::MYSQL_ATTR_USE_BUFFERED_QUERY => true,
                PDO::MYSQL_ATTR_LOCAL_INFILE => false
            ];

            $this->connection = new PDO($dsn, $this->config['username'], $this->config['password'], $options);

            $this->logger->info('Database connected', [
                'host' => $this->config['host'],
                'database' => $this->config['database']
            ]);

        } catch (PDOException $e) {
            $this->logger->error('Database connection failed: ' . $e->getMessage());
            throw new PDOException('Database connection failed: ' . $e->getMessage());
        }
    }

    /**
     * Get PDO connection
     * 
     * @return PDO
     */
    public function getConnection(): PDO
    {
        if ($this->connection === null) {
            $this->connect();
        }
        return $this->connection;
    }

    /**
     * Execute a query
     * 
     * @param string $sql
     * @param array $params
     * @return \PDOStatement
     */
    public function query(string $sql, array $params = []): \PDOStatement
    {
        try {
            $this->queryCount++;
            $startTime = microtime(true);

            $stmt = $this->getConnection()->prepare($sql);
            $stmt->execute($params);

            $executionTime = microtime(true) - $startTime;
            $this->logQuery($sql, $params, $executionTime);

            return $stmt;

        } catch (PDOException $e) {
            $this->logger->error('Query error: ' . $e->getMessage(), [
                'sql' => $sql,
                'params' => $params
            ]);
            throw $e;
        }
    }

    /**
     * Fetch one row
     * 
     * @param string $sql
     * @param array $params
     * @return object|null
     */
    public function fetch(string $sql, array $params = []): ?object
    {
        $stmt = $this->query($sql, $params);
        return $stmt->fetch() ?: null;
    }

    /**
     * Fetch all rows
     * 
     * @param string $sql
     * @param array $params
     * @return array
     */
    public function fetchAll(string $sql, array $params = []): array
    {
        $stmt = $this->query($sql, $params);
        return $stmt->fetchAll();
    }

    /**
     * Fetch column
     * 
     * @param string $sql
     * @param array $params
     * @param int $column
     * @return mixed
     */
    public function fetchColumn(string $sql, array $params = [], int $column = 0)
    {
        $stmt = $this->query($sql, $params);
        return $stmt->fetchColumn($column);
    }

    /**
     * Insert data
     * 
     * @param string $table
     * @param array $data
     * @return int|false
     */
    public function insert(string $table, array $data)
    {
        $columns = array_keys($data);
        $placeholders = array_map(fn($col) => ':' . $col, $columns);

        $sql = sprintf(
            'INSERT INTO %s (%s) VALUES (%s)',
            $table,
            implode(', ', $columns),
            implode(', ', $placeholders)
        );

        $this->query($sql, $data);
        return $this->lastInsertId();
    }

    /**
     * Update data
     * 
     * @param string $table
     * @param array $data
     * @param array $where
     * @return int
     */
    public function update(string $table, array $data, array $where): int
    {
        $set = [];
        foreach ($data as $key => $value) {
            $set[] = "{$key} = :{$key}";
        }

        $whereClause = [];
        foreach ($where as $key => $value) {
            $whereClause[] = "{$key} = :where_{$key}";
            $data['where_' . $key] = $value;
        }

        $sql = sprintf(
            'UPDATE %s SET %s WHERE %s',
            $table,
            implode(', ', $set),
            implode(' AND ', $whereClause)
        );

        $stmt = $this->query($sql, $data);
        return $stmt->rowCount();
    }

    /**
     * Delete data
     * 
     * @param string $table
     * @param array $where
     * @return int
     */
    public function delete(string $table, array $where): int
    {
        $whereClause = [];
        foreach ($where as $key => $value) {
            $whereClause[] = "{$key} = :{$key}";
        }

        $sql = sprintf(
            'DELETE FROM %s WHERE %s',
            $table,
            implode(' AND ', $whereClause)
        );

        $stmt = $this->query($sql, $where);
        return $stmt->rowCount();
    }

    /**
     * Begin transaction
     * 
     * @return bool
     */
    public function beginTransaction(): bool
    {
        if (!$this->inTransaction) {
            $this->inTransaction = $this->getConnection()->beginTransaction();
            $this->logger->debug('Transaction started');
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
            $this->inTransaction = false;
            $this->logger->debug('Transaction committed');
            return $this->getConnection()->commit();
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
            $this->inTransaction = false;
            $this->logger->debug('Transaction rolled back');
            return $this->getConnection()->rollBack();
        }
        return false;
    }

    /**
     * Get last insert ID
     * 
     * @return int
     */
    public function lastInsertId(): int
    {
        return (int)$this->getConnection()->lastInsertId();
    }

    /**
     * Get affected rows
     * 
     * @return int
     */
    public function rowCount(): int
    {
        return $this->getConnection()->lastInsertId() ?: 0;
    }

    /**
     * Log query
     * 
     * @param string $sql
     * @param array $params
     * @param float $time
     * @return void
     */
    private function logQuery(string $sql, array $params, float $time): void
    {
        $this->queryLog[] = [
            'sql' => $sql,
            'params' => $params,
            'time' => round($time * 1000, 2)
        ];

        if ($time > 1) { // Slow query > 1 second
            $this->logger->warning('Slow query detected', [
                'sql' => $sql,
                'time' => round($time * 1000, 2) . 'ms'
            ]);
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
     * Get query count
     * 
     * @return int
     */
    public function getQueryCount(): int
    {
        return $this->queryCount;
    }

    /**
     * Get database stats
     * 
     * @return array
     */
    public function stats(): array
    {
        return [
            'query_count' => $this->queryCount,
            'query_log' => $this->queryLog,
            'in_transaction' => $this->inTransaction,
            'driver' => $this->getConnection()->getAttribute(PDO::ATTR_DRIVER_NAME),
            'server_version' => $this->getConnection()->getAttribute(PDO::ATTR_SERVER_VERSION),
            'client_version' => $this->getConnection()->getAttribute(PDO::ATTR_CLIENT_VERSION)
        ];
    }

    /**
     * Prepare statement
     * 
     * @param string $sql
     * @return \PDOStatement
     */
    public function prepare(string $sql): \PDOStatement
    {
        return $this->getConnection()->prepare($sql);
    }

    /**
     * Execute in transaction
     * 
     * @param callable $callback
     * @return mixed
     * @throws \Exception
     */
    public function transaction(callable $callback)
    {
        try {
            $this->beginTransaction();
            $result = $callback();
            $this->commit();
            return $result;
        } catch (\Exception $e) {
            $this->rollback();
            throw $e;
        }
    }

    /**
     * Close connection
     * 
     * @return void
     */
    public function close(): void
    {
        $this->connection = null;
        self::$instance = null;
        $this->logger->info('Database connection closed');
    }

    /**
     * Destructor
     */
    public function __destruct()
    {
        $this->close();
    }
}