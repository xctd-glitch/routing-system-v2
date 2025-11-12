<?php

declare(strict_types=1);

use JsonException;

/**
 * Configuration Management API
 * Mobile-Optimized Configuration Updates
 */

header('Content-Type: application/json; charset=utf-8');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, Authorization');

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

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $input = file_get_contents('php://input');

    if ($input === false) {
        emitJsonResponse(
            [
                'error' => 'Unable to read request body',
            ],
            500
        );

        return;
    }

    try {
        $data = json_decode($input, true, 512, JSON_THROW_ON_ERROR);
    } catch (JsonException $exception) {
        emitJsonResponse(
            [
                'error' => 'Invalid JSON payload',
                'message' => 'Request body must be valid JSON',
            ],
            400
        );

        return;
    }

    if (!is_array($data)) {
        emitJsonResponse(
            [
                'error' => 'Invalid request shape',
                'message' => 'JSON payload must decode to an object',
            ],
            400
        );

        return;
    }

    $systemOn = array_key_exists('system_on', $data) ? (bool) $data['system_on'] : false;
    $isActive = array_key_exists('is_active', $data) ? (bool) $data['is_active'] : false;
    $ruleType = isset($data['rule_type']) && is_string($data['rule_type']) ? $data['rule_type'] : 'static_route';
    $muteDuration = isset($data['mute_duration']) ? (int) $data['mute_duration'] : 120;
    $unmuteDuration = isset($data['unmute_duration']) ? (int) $data['unmute_duration'] : 120;

    $updatedConfig = [
        'id' => 1,
        'name' => 'Default Configuration',
        'system_on' => $systemOn,
        'is_active' => $isActive,
        'rule_type' => $ruleType,
        'mute_duration' => $muteDuration,
        'unmute_duration' => $unmuteDuration,
        'current_state' => 'normal',
        'last_toggle_time' => null,
        'updated_at' => date('c'),
        'is_default' => true,
        'processing_time_ms' => round((microtime(true) - $startTime) * 1000),
    ];

    emitJsonResponse($updatedConfig);

    return;
}

$currentConfig = [
    'id' => 1,
    'name' => 'Default Configuration',
    'system_on' => false,
    'is_active' => false,
    'rule_type' => 'static_route',
    'mute_duration' => 120,
    'unmute_duration' => 120,
    'last_toggle_time' => null,
    'current_state' => 'normal',
    'created_at' => '2024-01-01T00:00:00Z',
    'updated_at' => date('c'),
    'is_default' => true,
    'processing_time_ms' => round((microtime(true) - $startTime) * 1000),
    'api_version' => '1.0',
];

emitJsonResponse($currentConfig);
