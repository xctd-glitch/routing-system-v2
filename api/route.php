<?php

declare(strict_types=1);

use JsonException;

/**
 * @return array{current_state: string|null, last_toggle_time: string|null}
 */
function loadMuteState(): array
{
    $defaultState = [
        'current_state' => null,
        'last_toggle_time' => null,
    ];

    $filePath = dirname(__DIR__) . '/storage/mute_state.json';

    if (!is_file($filePath)) {
        return $defaultState;
    }

    $contents = file_get_contents($filePath);
    if ($contents === false) {
        return $defaultState;
    }

    try {
        $data = json_decode($contents, true, 512, JSON_THROW_ON_ERROR);
    } catch (JsonException) {
        return $defaultState;
    }

    if (!is_array($data)) {
        return $defaultState;
    }

    $currentState = isset($data['current_state']) && is_string($data['current_state'])
        ? strtolower($data['current_state'])
        : null;

    if ($currentState !== null && !in_array($currentState, ['mute', 'unmute', 'normal'], true)) {
        $currentState = null;
    }

    $lastToggleTime = isset($data['last_toggle_time']) && is_string($data['last_toggle_time'])
        ? $data['last_toggle_time']
        : null;

    if ($lastToggleTime !== null && strtotime($lastToggleTime) === false) {
        $lastToggleTime = null;
    }

    return [
        'current_state' => $currentState,
        'last_toggle_time' => $lastToggleTime,
    ];
}

/**
 * Persist the mute/unmute state to the shared storage file.
 */
function persistMuteState(string $currentState, int $lastToggleTimestamp): void
{
    $filePath = dirname(__DIR__) . '/storage/mute_state.json';
    $directory = dirname($filePath);

    if (!is_dir($directory)) {
        if (!mkdir($directory, 0755, true) && !is_dir($directory)) {
            return;
        }
    }

    $payload = [
        'current_state' => $currentState,
        'last_toggle_time' => date('c', $lastToggleTimestamp),
    ];

    try {
        $encoded = json_encode($payload, JSON_PRETTY_PRINT | JSON_THROW_ON_ERROR);
    } catch (JsonException) {
        return;
    }

    $handle = fopen($filePath, 'c+');
    if ($handle === false) {
        return;
    }

    try {
        if (!flock($handle, LOCK_EX)) {
            return;
        }

        if (!ftruncate($handle, 0) || fseek($handle, 0) !== 0) {
            return;
        }

        $bytesWritten = fwrite($handle, $encoded);
        if ($bytesWritten === false || $bytesWritten < strlen($encoded)) {
            return;
        }

        fflush($handle);
    } finally {
        flock($handle, LOCK_UN);
        fclose($handle);
    }
}

/**
 * Synchronize the in-memory and persisted mute/unmute state.
 *
 * @param array<string, mixed> $systemConfig
 */
function updateMuteState(array &$systemConfig, string $currentState, int $timestamp): void
{
    $systemConfig['current_state'] = $currentState;
    $systemConfig['last_toggle_time'] = date('c', $timestamp);
    persistMuteState($currentState, $timestamp);
}

/**
 * Main Routing Decision API
 * High-Performance Mobile-Optimized Endpoint
 */

$startTime = microtime(true);

header('Content-Type: application/json; charset=utf-8');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, Authorization, X-Requested-With');
header('Cache-Control: no-cache, no-store, must-revalidate');
header('Pragma: no-cache');
header('Expires: 0');

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

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    emitJsonResponse(
        [
            'error' => 'Method not allowed',
            'message' => 'Only POST requests are accepted',
            'allowed_methods' => ['POST', 'OPTIONS'],
        ],
        405
    );

    return;
}

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
            'error' => 'Invalid JSON',
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

if (!array_key_exists('ip_address', $data) || !is_string($data['ip_address']) || $data['ip_address'] === '') {
    emitJsonResponse(
        [
            'error' => 'Missing required field',
            'message' => 'ip_address field is required',
            'required_fields' => ['ip_address'],
        ],
        400
    );

    return;
}

$systemConfig = [
    'system_on' => false,
    'is_active' => false,
    'rule_type' => 'static_route',
    'mute_duration' => 120,
    'unmute_duration' => 120,
    'current_state' => 'normal',
    'last_toggle_time' => null,
];

$storedMuteState = loadMuteState();

if ($storedMuteState['current_state'] !== null) {
    $systemConfig['current_state'] = $storedMuteState['current_state'];
}

if ($storedMuteState['last_toggle_time'] !== null) {
    $systemConfig['last_toggle_time'] = $storedMuteState['last_toggle_time'];
}

$targetUrls = [
    ['url' => 'https://example.com', 'weight' => 5, 'priority' => 1, 'active' => true],
    ['url' => 'https://backup.com', 'weight' => 3, 'priority' => 2, 'active' => true],
    ['url' => 'https://fallback.com', 'weight' => 2, 'priority' => 3, 'active' => true],
];

$targetCountries = ['US', 'UK', 'DE', 'FR', 'ID', 'JP', 'CN', 'IN', 'CA', 'AU'];

if ($systemConfig['system_on'] !== true) {
    emitJsonResponse(
        [
            'decision' => 'normal',
            'system_state' => 'normal',
            'processing_time_ms' => round((microtime(true) - $startTime) * 1000),
            'timestamp' => date('c'),
            'debug' => [
                'system_on' => false,
                'is_active' => false,
                'conditions_met' => false,
                'rule_type' => $systemConfig['rule_type'],
                'reason' => 'System is disabled',
            ],
        ]
    );

    return;
}

if ($systemConfig['is_active'] !== true) {
    emitJsonResponse(
        [
            'decision' => 'normal',
            'system_state' => 'normal',
            'processing_time_ms' => round((microtime(true) - $startTime) * 1000),
            'timestamp' => date('c'),
            'debug' => [
                'system_on' => true,
                'is_active' => false,
                'conditions_met' => false,
                'rule_type' => $systemConfig['rule_type'],
                'reason' => 'System is inactive',
            ],
        ]
    );

    return;
}

$conditionsMet = true;
$failureReasons = [];

if (!empty($data['country'])) {
    $userCountry = strtoupper(trim((string) $data['country']));
    if (!in_array($userCountry, $targetCountries, true)) {
        $conditionsMet = false;
        $failureReasons[] = "Country '{$userCountry}' not in target list";
    }
} else {
    $data['country'] = 'US';
}

if (array_key_exists('wap', $data) && $data['wap'] === true) {
    $conditionsMet = false;
    $failureReasons[] = 'WAP users are not targeted';
}

if (array_key_exists('vpn', $data) && $data['vpn'] === true) {
    $conditionsMet = false;
    $failureReasons[] = 'VPN users are not targeted';
}

if ($conditionsMet !== true) {
    emitJsonResponse(
        [
            'decision' => 'normal',
            'system_state' => $systemConfig['current_state'],
            'processing_time_ms' => round((microtime(true) - $startTime) * 1000),
            'timestamp' => date('c'),
            'debug' => [
                'system_on' => true,
                'is_active' => true,
                'conditions_met' => false,
                'rule_type' => $systemConfig['rule_type'],
                'failure_reasons' => $failureReasons,
                'user_country' => $data['country'],
                'target_countries' => $targetCountries,
            ],
        ]
    );

    return;
}

$targetUrl = null;
$ruleApplied = $systemConfig['rule_type'];

$activeUrls = array_values(array_filter(
    $targetUrls,
    static fn (array $url): bool => $url['active'] === true
));

if ($activeUrls === []) {
    emitJsonResponse(
        [
            'decision' => 'normal',
            'system_state' => $systemConfig['current_state'],
            'processing_time_ms' => round((microtime(true) - $startTime) * 1000),
            'timestamp' => date('c'),
            'debug' => [
                'system_on' => true,
                'is_active' => true,
                'conditions_met' => true,
                'rule_type' => $systemConfig['rule_type'],
                'reason' => 'No active target URLs available',
            ],
        ]
    );

    return;
}

switch ($systemConfig['rule_type']) {
    case 'static_route':
        usort(
            $activeUrls,
            static function (array $a, array $b): int {
                if ($a['priority'] !== $b['priority']) {
                    return $a['priority'] <=> $b['priority'];
                }

                return $b['weight'] <=> $a['weight'];
            }
        );
        $targetUrl = $activeUrls[0]['url'];
        break;

    case 'random_route':
        $totalWeight = array_sum(array_column($activeUrls, 'weight'));
        if ($totalWeight <= 0) {
            break;
        }

        $random = random_int(1, $totalWeight);
        $cumulativeWeight = 0;

        foreach ($activeUrls as $url) {
            $cumulativeWeight += $url['weight'];
            if ($random <= $cumulativeWeight) {
                $targetUrl = $url['url'];
                break;
            }
        }

        if ($targetUrl === null) {
            $targetUrl = $activeUrls[array_rand($activeUrls, 1)]['url'];
        }
        break;

    case 'mute_unmute':
        $now = time();
        $lastToggle = $systemConfig['last_toggle_time'] !== null
            ? strtotime((string) $systemConfig['last_toggle_time'])
            : null;
        $currentState = $systemConfig['current_state'] ?: 'normal';

        if ($lastToggle === false || $lastToggle === null) {
            $currentState = 'unmute';
            $lastToggle = $now;
            updateMuteState($systemConfig, $currentState, $lastToggle);
        }

        $duration = $currentState === 'mute'
            ? (int) $systemConfig['mute_duration']
            : (int) $systemConfig['unmute_duration'];

        if (($now - $lastToggle) >= $duration) {
            $currentState = $currentState === 'mute' ? 'unmute' : 'mute';
            $lastToggle = $now;
            updateMuteState($systemConfig, $currentState, $lastToggle);
        }

        if ($currentState === 'unmute') {
            usort(
                $activeUrls,
                static function (array $a, array $b): int {
                    if ($a['priority'] !== $b['priority']) {
                        return $a['priority'] <=> $b['priority'];
                    }

                    return $b['weight'] <=> $a['weight'];
                }
            );
            $targetUrl = $activeUrls[0]['url'];
        }

        break;

    default:
        $targetUrl = $activeUrls[0]['url'];
        break;
}

$processingTime = round((microtime(true) - $startTime) * 1000);

$response = [
    'decision' => $targetUrl !== null ? 'target' : 'normal',
    'system_state' => $systemConfig['current_state'] ?: 'normal',
    'rule_applied' => $ruleApplied,
    'processing_time_ms' => $processingTime,
    'timestamp' => date('c'),
    'api_version' => '1.0',
    'mobile_optimized' => true,
    'debug' => [
        'system_on' => true,
        'is_active' => true,
        'conditions_met' => true,
        'rule_type' => $systemConfig['rule_type'],
        'user_country' => $data['country'] ?? 'US',
        'is_wap' => $data['wap'] ?? false,
        'is_vpn' => $data['vpn'] ?? false,
        'active_urls_count' => count($activeUrls),
        'performance_class' => $processingTime < 100 ? 'excellent' : ($processingTime < 500 ? 'good' : 'acceptable'),
    ],
];

if ($targetUrl !== null) {
    $response['target_url'] = $targetUrl;
    $response['target_info'] = [
        'rule_used' => $ruleApplied,
        'selection_method' => $systemConfig['rule_type'],
    ];
}

emitJsonResponse($response);
