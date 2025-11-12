<?php

declare(strict_types=1);

use JsonException;
use Throwable;

/**
 * Health Check API - System Monitoring
 */

header('Content-Type: application/json; charset=utf-8');
header('Access-Control-Allow-Origin: *');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(204);

    return;
}

/**
 * @param array<string, mixed> $payload
 */
function emitJsonResponse(array $payload, int $statusCode = 200): void
{
    http_response_code($statusCode);

    try {
        echo json_encode($payload, JSON_THROW_ON_ERROR | JSON_PRETTY_PRINT);
    } catch (JsonException $exception) {
        http_response_code(500);
        echo '{"error":"Failed to encode response"}';
    }
}

$startTime = microtime(true);

try {
    $health = [
        'status' => 'healthy',
        'timestamp' => date('c'),
        'version' => '1.0',
        'environment' => $_ENV['APP_ENV'] ?? 'production',
        'php_version' => phpversion(),
        'mobile_optimized' => true,
        'pwa_ready' => true,
        'checks' => [
            'database' => 'unknown',
            'filesystem' => is_writable('.') ? 'healthy' : 'warning',
            'memory' => [
                'usage' => memory_get_usage(true),
                'peak' => memory_get_peak_usage(true),
                'limit' => ini_get('memory_limit'),
            ],
            'extensions' => [
                'curl' => extension_loaded('curl'),
                'json' => extension_loaded('json'),
                'pdo' => extension_loaded('pdo'),
                'pdo_mysql' => extension_loaded('pdo_mysql'),
            ],
        ],
        'performance' => [
            'response_time_ms' => round((microtime(true) - $startTime) * 1000),
            'memory_usage_mb' => round(memory_get_usage(true) / 1024 / 1024, 2),
            'load_average' => sys_getloadavg() ?: [0, 0, 0],
        ],
    ];

    if (file_exists(__DIR__ . '/../config/database.php')) {
        try {
            require_once __DIR__ . '/../config/database.php';
            $db = DatabaseConfig::getInstance();
            $health['checks']['database'] = $db->isConnected() ? 'healthy' : 'error';
        } catch (Throwable $exception) {
            $health['checks']['database'] = 'error';
        }
    }

    $hasErrors = in_array('error', $health['checks'], true)
        || $health['checks']['extensions']['pdo'] !== true
        || $health['checks']['extensions']['json'] !== true;

    if ($hasErrors) {
        $health['status'] = 'degraded';
        emitJsonResponse($health, 503);

        return;
    }

    emitJsonResponse($health);
} catch (Throwable $exception) {
    emitJsonResponse(
        [
            'status' => 'error',
            'message' => 'Health check failed',
            'timestamp' => date('c'),
        ],
        500
    );
}
