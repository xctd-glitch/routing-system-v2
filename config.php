================================================================================
FILE 4: .env (Environment Configuration)
================================================================================

# Database Configuration for Dashboard Routing System
# Update these values for your hosting environment

DB_HOST=localhost
DB_PORT=3306
DB_NAME=routing_system
DB_USERNAME=root
DB_PASSWORD=

# Application Settings
APP_NAME=Dashboard Routing System
APP_ENV=production
DEBUG=false

# Security Configuration
SESSION_SECRET=change-this-secret-key-in-production-environment

# Performance Settings
CACHE_ENABLED=true
CACHE_TTL=300
API_TIMEOUT=5000

# Mobile Optimization Features
MOBILE_FIRST=true
PWA_ENABLED=true
LAZY_LOADING=true
TOUCH_OPTIMIZATION=true

# Feature Flags
ANALYTICS_ENABLED=true
MONITORING_ENABLED=true
REAL_TIME_UPDATES=true

================================================================================
FILE 5: .htaccess (Apache Configuration)
================================================================================

# Apache Configuration for Dashboard Routing System
# Mobile-First Performance Optimized

RewriteEngine On

# Security Headers
Header always set X-Content-Type-Options nosniff
Header always set X-Frame-Options DENY
Header always set X-XSS-Protection "1; mode=block"
Header always set Referrer-Policy "strict-origin-when-cross-origin"

# PWA Support Headers
<Files "manifest.json">
    Header set Content-Type application/manifest+json
    Header set Cache-Control "public, max-age=604800"
</Files>

<Files "sw.js">
    Header set Content-Type application/javascript
    Header set Cache-Control "public, max-age=0"
</Files>

# CORS Headers for API endpoints
<FilesMatch "^(api)/.*\.php$">
    Header always set Access-Control-Allow-Origin "*"
    Header always set Access-Control-Allow-Methods "GET, POST, OPTIONS"
    Header always set Access-Control-Allow-Headers "Content-Type, Authorization"
</FilesMatch>

# Handle preflight OPTIONS requests
RewriteCond %{REQUEST_METHOD} OPTIONS
RewriteRule ^(.*)$ $1 [R=200,L]

# API routing
RewriteCond %{REQUEST_FILENAME} !-f
RewriteCond %{REQUEST_FILENAME} !-d
RewriteRule ^api/(.+)$ api/$1.php [L,QSA]

# Deny access to sensitive files
<Files ".env">
    Require all denied
</Files>

<Files "*.log">
    Require all denied
</Files>

<Files "config/installed.lock">
    Require all denied
</Files>

# PHP Performance Settings
php_value memory_limit 256M
php_value upload_max_filesize 10M
php_value post_max_size 10M
php_flag display_errors Off
php_flag log_errors On
php_value max_execution_time 30

# Compression for performance
<IfModule mod_deflate.c>
    AddOutputFilterByType DEFLATE text/plain
    AddOutputFilterByType DEFLATE text/html
    AddOutputFilterByType DEFLATE text/css
    AddOutputFilterByType DEFLATE application/javascript
    AddOutputFilterByType DEFLATE application/json
</IfModule>

# Cache static assets
<IfModule mod_expires.c>
    ExpiresActive on
    ExpiresByType text/css "access plus 1 month"
    ExpiresByType application/javascript "access plus 1 month"
    ExpiresByType image/png "access plus 1 year"
    ExpiresByType image/svg+xml "access plus 1 year"
</IfModule>

================================================================================
FILE 6: manifest.json (PWA Configuration)
================================================================================

{
  "name": "Dashboard Routing System",
  "short_name": "Routing Dashboard",
  "description": "Mobile-first traffic routing system with PWA capabilities and performance optimization",
  "start_url": "/",
  "display": "standalone",
  "theme_color": "#3b82f6",
  "background_color": "#ffffff",
  "orientation": "portrait-primary",
  "categories": ["productivity", "utilities", "business"],
  "lang": "en",
  "scope": "/",
  "icons": [
    {
      "src": "data:image/svg+xml;base64,PHN2ZyB3aWR0aD0iMTkyIiBoZWlnaHQ9IjE5MiIgZmlsbD0ibm9uZSIgeG1sbnM9Imh0dHA6Ly93d3cudzMub3JnLzIwMDAvc3ZnIj48ZGVmcz48bGluZWFyR3JhZGllbnQgaWQ9ImEiIHgxPSIwIiB5MT0iMCIgeDI9IjE5MiIgeTI9IjE5MiIgZ3JhZGllbnRVbml0cz0idXNlclNwYWNlT25Vc2UiPjxzdG9wIHN0b3AtY29sb3I9IiMzYjgyZjYiLz48c3RvcCBvZmZzZXQ9IjEiIHN0b3AtY29sb3I9IiM4YjVjZjYiLz48L2xpbmVhckdyYWRpZW50PjwvZGVmcz48cmVjdCB3aWR0aD0iMTkyIiBoZWlnaHQ9IjE5MiIgcng9IjMyIiBmaWxsPSJ1cmwoI2EpIi8+PHRleHQgeD0iOTYiIHk9IjEyMCIgZm9udC1mYW1pbHk9IkFyaWFsLHNhbnMtc2VyaWYiIGZvbnQtc2l6ZT0iODAiIGZvbnQtd2VpZ2h0PSI3MDAiIGZpbGw9IiNmZmYiIHRleHQtYW5jaG9yPSJtaWRkbGUiPlI8L3RleHQ+PC9zdmc+",
      "sizes": "192x192",
      "type": "image/svg+xml",
      "purpose": "any maskable"
    },
    {
      "src": "data:image/svg+xml;base64,PHN2ZyB3aWR0aD0iNTEyIiBoZWlnaHQ9IjUxMiIgZmlsbD0ibm9uZSIgeG1sbnM9Imh0dHA6Ly93d3cudzMub3JnLzIwMDAvc3ZnIj48ZGVmcz48bGluZWFyR3JhZGllbnQgaWQ9ImEiIHgxPSIwIiB5MT0iMCIgeDI9IjUxMiIgeTI9IjUxMiIgZ3JhZGllbnRVbml0cz0idXNlclNwYWNlT25Vc2UiPjxzdG9wIHN0b3AtY29sb3I9IiMzYjgyZjYiLz48c3RvcCBvZmZzZXQ9IjEiIHN0b3AtY29sb3I9IiM4YjVjZjYiLz48L2xpbmVhckdyYWRpZW50PjwvZGVmcz48cmVjdCB3aWR0aD0iNTEyIiBoZWlnaHQ9IjUxMiIgcng9Ijg2IiBmaWxsPSJ1cmwoI2EpIi8+PHRleHQgeD0iMjU2IiB5PSIzMjAiIGZvbnQtZmFtaWx5PSJBcmlhbCxzYW5zLXNlcmlmIiBmb250LXNpemU9IjIyMCIgZm9udC13ZWlnaHQ9IjcwMCIgZmlsbD0iI2ZmZiIgdGV4dC1hbmNob3I9Im1pZGRsZSI+UjwvdGV4dD48L3N2Zz4=",
      "sizes": "512x512",
      "type": "image/svg+xml",
      "purpose": "any maskable"
    }
  ],
  "shortcuts": [
    {
      "name": "Configuration",
      "short_name": "Config",
      "url": "/index.php#config",
      "icons": [
        {
          "src": "data:image/svg+xml;base64,PHN2ZyB3aWR0aD0iOTYiIGhlaWdodD0iOTYiIGZpbGw9IiMzYjgyZjYiIHZpZXdCb3g9IjAgMCAyNCAyNCI+PHBhdGggZD0iTTEwLjMyNSA0LjMxN2MuNDI2LTEuNzU2IDIuOTI0LTEuNzU2IDMuMzUgMGExLjcyNCAxLjcyNCAwIDAwMi41NzMgMS4wNjZjMS41NDMtLjk0IDMuMzEuODI2IDIuMzcgMi4zN2ExLjcyNCAxLjcyNCAwIDAwMS4wNjUgMi41NzJjMS43NTYuNDI2IDEuNzU2IDIuOTI0IDAgMy4zNWExLjcyNCAxLjcyNCAwIDAwLTEuMDY2IDIuNTczYy45NCAxLjU0My0uODI2IDMuMzEtMi4zNyAyLjM3YTEuNzI0IDEuNzI0IDAgMDAtMi41NzIgMS4wNjVjLS40MjYgMS43NTYtMi45MjQgMS43NTYtMy4zNSAwYTEuNzI0IDEuNzI0IDAgMDAtMi41NzMtMS4wNjZjLTEuNTQzLjk0LTMuMzEtLjgyNi0yLjM3LTIuMzdhMS43MjQgMS43MjQgMCAwMC0xLjA2NS0yLjU3MmMtMS43NTYtLjQyNi0xLjc1Ni0yLjkyNCAwLTMuMzVhMS43MjQgMS43MjQgMCAwMDEuMDY2LTIuNTczYy0uOTQtMS41NDMuODI2LTMuMzEgMi4zNy0yLjM3Ljk5Ni42MDggMi4yOTYuMDcgMi41NzItMS4wNjV6Ii8+PHBhdGggZD0iTTE1IDEyYTMgMyAwIDExLTYgMCAzIDMgMCAwMTYgMHoiLz48L3N2Zz4=",
          "sizes": "96x96",
          "type": "image/svg+xml"
        }
      ]
    }
  ]
}

================================================================================
FILE 7: config/database.php (Database Connection)
================================================================================

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

================================================================================
END OF CONFIG FILES
================================================================================