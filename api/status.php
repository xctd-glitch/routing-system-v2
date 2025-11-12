<?php

declare(strict_types=1);

use JsonException;

/**
 * System Status API - Mobile Performance Optimized
 */

header('Content-Type: application/json; charset=utf-8');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');

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

$systemStatus = [
    'system_on' => false,
    'is_active' => false,
    'current_state' => 'normal',
    'rule_type' => 'static_route',
    'mute_duration' => 120,
    'unmute_duration' => 120,
    'last_toggle_time' => null,
];

$stats = [
    'total_requests' => 1250,
    'target_decisions' => 875,
    'normal_decisions' => 375,
    'success_rate' => 95.2,
    'avg_processing_time' => 45,
];

$counts = [
    'active_urls' => 3,
    'active_countries' => 5,
    'total_configurations' => 1,
];

$response = [
    'system_on' => $systemStatus['system_on'],
    'is_active' => $systemStatus['is_active'],
    'current_state' => $systemStatus['current_state'],
    'rule_type' => $systemStatus['rule_type'],
    'stats' => $stats,
    'active_urls' => $counts['active_urls'],
    'active_countries' => $counts['active_countries'],
    'last_updated' => date('c'),
    'processing_time_ms' => round((microtime(true) - $startTime) * 1000),
    'api_version' => '1.0',
    'mobile_optimized' => true,
    'pwa_ready' => true,
];

emitJsonResponse($response);
