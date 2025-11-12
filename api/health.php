<?php
/**
 * Health Check API - System Monitoring
 */

header('Content-Type: application/json; charset=utf-8');
header('Access-Control-Allow-Origin: *');

$startTime = microtime(true);

try {
    // Basic health checks
    $health = [
        'status' => 'healthy',
        'timestamp' => date('c'),
        'version' => '1.0',
        'environment' => $_ENV['APP_ENV'] ?? 'production',
        'php_version' => phpversion(),
        'mobile_optimized' => true,
        'pwa_ready' => true,
        'checks' => [
            'database' => 'unknown', // Will be checked if database class exists
            'filesystem' => is_writable('.') ? 'healthy' : 'warning',
            'memory' => [
                'usage' => memory_get_usage(true),
                'peak' => memory_get_peak_usage(true),
                'limit' => ini_get('memory_limit')
            ],
            'extensions' => [
                'curl' => extension_loaded('curl'),
                'json' => extension_loaded('json'),
                'pdo' => extension_loaded('pdo'),
                'pdo_mysql' => extension_loaded('pdo_mysql')
            ]
        ],
        'performance' => [
            'response_time_ms' => round((microtime(true) - $startTime) * 1000),
            'memory_usage_mb' => round(memory_get_usage(true) / 1024 / 1024, 2),
            'load_average' => sys_getloadavg() ?: [0, 0, 0]
        ]
    ];
    
    // Check database connection if possible
    if (file_exists(__DIR__ . '/../config/database.php')) {
        try {
            require_once __DIR__ . '/../config/database.php';
            $db = DatabaseConfig::getInstance();
            $health['checks']['database'] = $db->isConnected() ? 'healthy' : 'error';
        } catch (Exception $e) {
            $health['checks']['database'] = 'error';
        }
    }
    
    // Overall status
    $hasErrors = in_array('error', $health['checks']) || 
                 !$health['checks']['extensions']['pdo'] ||
                 !$health['checks']['extensions']['json'];
    
    if ($hasErrors) {
        $health['status'] = 'degraded';
        http_response_code(503);
    }
    
    echo json_encode($health, JSON_PRETTY_PRINT);
    
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'status' => 'error',
        'message' => 'Health check failed',
        'timestamp' => date('c'),
        'error' => $e->getMessage()
    ]);
}
?>