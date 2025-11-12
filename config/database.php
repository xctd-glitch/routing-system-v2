<?php
/**
 * Database Configuration - Performance Optimized
 * Mobile-First Dashboard Routing System
 */

class DatabaseConfig {
    private static $instance = null;
    private $pdo = null;
    
    // Default configuration
    private $config = [
        'host' => 'localhost',
        'port' => 3306,
        'dbname' => 'routing_system',
        'username' => 'root',
        'password' => '',
        'charset' => 'utf8mb4',
        'driver' => 'mysql'
    ];
    
    private function __construct() {
        $this->loadEnvironment();
        $this->connect();
    }
    
    public static function getInstance() {
        if (self::$instance === null) {
            self::$instance = new DatabaseConfig();
        }
        return self::$instance;
    }
    
    private function loadEnvironment() {
        $envFile = __DIR__ . '/../.env';
        if (file_exists($envFile)) {
            $lines = file($envFile, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
            foreach ($lines as $line) {
                if (strpos($line, '=') !== false && substr($line, 0, 1) !== '#') {
                    list($key, $value) = explode('=', $line, 2);
                    $key = trim($key);
                    $value = trim($value, '"\'');
                    
                    switch ($key) {
                        case 'DB_HOST':
                            $this->config['host'] = $value;
                            break;
                        case 'DB_PORT':
                            $this->config['port'] = (int)$value;
                            break;
                        case 'DB_NAME':
                            $this->config['dbname'] = $value;
                            break;
                        case 'DB_USERNAME':
                            $this->config['username'] = $value;
                            break;
                        case 'DB_PASSWORD':
                            $this->config['password'] = $value;
                            break;
                        case 'DB_DRIVER':
                            $this->config['driver'] = $value;
                            break;
                    }
                }
            }
        }
    }
    
    private function connect() {
        try {
            $dsn = "{$this->config['driver']}:host={$this->config['host']};port={$this->config['port']};dbname={$this->config['dbname']};charset={$this->config['charset']}";
            
            $options = [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::ATTR_EMULATE_PREPARES => false,
                PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES {$this->config['charset']} COLLATE {$this->config['charset']}_unicode_ci"
            ];
            
            $this->pdo = new PDO($dsn, $this->config['username'], $this->config['password'], $options);
            
        } catch (PDOException $e) {
            error_log("Database connection failed: " . $e->getMessage());
            // For production, don't expose database errors
            $this->pdo = null;
        }
    }
    
    public function getConnection() {
        return $this->pdo;
    }
    
    public function execute($sql, $params = []) {
        if (!$this->pdo) {
            return false;
        }
        
        try {
            $stmt = $this->pdo->prepare($sql);
            $stmt->execute($params);
            return $stmt;
        } catch (PDOException $e) {
            error_log("SQL execution failed: " . $e->getMessage());
            throw new Exception("Database query failed");
        }
    }
    
    public function fetch($sql, $params = []) {
        $stmt = $this->execute($sql, $params);
        return $stmt ? $stmt->fetch() : false;
    }
    
    public function fetchAll($sql, $params = []) {
        $stmt = $this->execute($sql, $params);
        return $stmt ? $stmt->fetchAll() : [];
    }
    
    public function lastInsertId() {
        return $this->pdo ? $this->pdo->lastInsertId() : false;
    }
    
    public function beginTransaction() {
        return $this->pdo ? $this->pdo->beginTransaction() : false;
    }
    
    public function commit() {
        return $this->pdo ? $this->pdo->commit() : false;
    }
    
    public function rollback() {
        return $this->pdo ? $this->pdo->rollback() : false;
    }
    
    public function isConnected() {
        return $this->pdo !== null;
    }
    
    public function getConnectionInfo() {
        if (!$this->pdo) {
            return [
                'status' => 'disconnected',
                'host' => $this->config['host'],
                'database' => $this->config['dbname']
            ];
        }
        
        return [
            'status' => 'connected',
            'host' => $this->config['host'],
            'database' => $this->config['dbname'],
            'driver' => $this->pdo->getAttribute(PDO::ATTR_DRIVER_NAME),
            'version' => $this->pdo->getAttribute(PDO::ATTR_SERVER_VERSION),
            'charset' => $this->config['charset']
        ];
    }
}
?>
