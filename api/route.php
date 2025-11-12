<?php
/**
 * Main Routing Decision API
 * High-Performance Mobile-Optimized Endpoint
 */

// Performance monitoring
$startTime = microtime(true);

// Headers for API responses
header('Content-Type: application/json; charset=utf-8');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, Authorization, X-Requested-With');
header('Cache-Control: no-cache, no-store, must-revalidate');
header('Pragma: no-cache');
header('Expires: 0');

// Handle preflight OPTIONS request
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(204);
    exit;
}

// Only allow POST requests
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode([
        'error' => 'Method not allowed',
        'message' => 'Only POST requests are accepted',
        'allowed_methods' => ['POST', 'OPTIONS']
    ]);
    exit;
}

try {
    // Get and validate input
    $input = file_get_contents('php://input');
    $data = json_decode($input, true);
    
    if (json_last_error() !== JSON_ERROR_NONE) {
        http_response_code(400);
        echo json_encode([
            'error' => 'Invalid JSON',
            'message' => 'Request body must be valid JSON',
            'json_error' => json_last_error_msg()
        ]);
        exit;
    }
    
    // Validate required fields
    if (!isset($data['ip_address']) || empty($data['ip_address'])) {
        http_response_code(400);
        echo json_encode([
            'error' => 'Missing required field',
            'message' => 'ip_address field is required',
            'required_fields' => ['ip_address']
        ]);
        exit;
    }
    
    // Mock configuration (replace with database queries in production)
    $systemConfig = [
        'system_on' => false, // Change to true for testing
        'is_active' => false, // Change to true for testing
        'rule_type' => 'static_route',
        'mute_duration' => 120,
        'unmute_duration' => 120,
        'current_state' => 'normal',
        'last_toggle_time' => null
    ];
    
    $targetUrls = [
        ['url' => 'https://example.com', 'weight' => 5, 'priority' => 1, 'active' => true],
        ['url' => 'https://backup.com', 'weight' => 3, 'priority' => 2, 'active' => true],
        ['url' => 'https://fallback.com', 'weight' => 2, 'priority' => 3, 'active' => true]
    ];
    
    $targetCountries = ['US', 'UK', 'DE', 'FR', 'ID', 'JP', 'CN', 'IN', 'CA', 'AU'];
    
    // Step 1: Check if system is enabled
    if (!$systemConfig['system_on']) {
        $response = [
            'decision' => 'normal',
            'system_state' => 'normal',
            'processing_time_ms' => round((microtime(true) - $startTime) * 1000),
            'timestamp' => date('c'),
            'debug' => [
                'system_on' => false,
                'is_active' => false,
                'conditions_met' => false,
                'rule_type' => $systemConfig['rule_type'],
                'reason' => 'System is disabled'
            ]
        ];
        
        echo json_encode($response, JSON_PRETTY_PRINT);
        exit;
    }
    
    // Step 2: Check if system is active
    if (!$systemConfig['is_active']) {
        $response = [
            'decision' => 'normal',
            'system_state' => 'normal',
            'processing_time_ms' => round((microtime(true) - $startTime) * 1000),
            'timestamp' => date('c'),
            'debug' => [
                'system_on' => true,
                'is_active' => false,
                'conditions_met' => false,
                'rule_type' => $systemConfig['rule_type'],
                'reason' => 'System is inactive'
            ]
        ];
        
        echo json_encode($response, JSON_PRETTY_PRINT);
        exit;
    }
    
    // Step 3: Check targeting conditions
    $conditionsMet = true;
    $failureReasons = [];
    
    // Country condition check
    if (!empty($data['country'])) {
        $userCountry = strtoupper(trim($data['country']));
        if (!in_array($userCountry, $targetCountries)) {
            $conditionsMet = false;
            $failureReasons[] = "Country '{$userCountry}' not in target list";
        }
    } else {
        $data['country'] = 'US'; // Default
    }
    
    // WAP condition check (must be non-WAP)
    if (isset($data['wap']) && $data['wap'] === true) {
        $conditionsMet = false;
        $failureReasons[] = 'WAP users are not targeted';
    }
    
    // VPN condition check (must be non-VPN) 
    if (isset($data['vpn']) && $data['vpn'] === true) {
        $conditionsMet = false;
        $failureReasons[] = 'VPN users are not targeted';
    }
    
    if (!$conditionsMet) {
        $response = [
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
                'target_countries' => $targetCountries
            ]
        ];
        
        echo json_encode($response, JSON_PRETTY_PRINT);
        exit;
    }
    
    // Step 4: Apply routing rule
    $targetUrl = null;
    $ruleApplied = $systemConfig['rule_type'];
    
    // Filter active URLs
    $activeUrls = array_filter($targetUrls, function($url) {
        return $url['active'] === true;
    });
    
    if (empty($activeUrls)) {
        $response = [
            'decision' => 'normal',
            'system_state' => $systemConfig['current_state'],
            'processing_time_ms' => round((microtime(true) - $startTime) * 1000),
            'timestamp' => date('c'),
            'debug' => [
                'system_on' => true,
                'is_active' => true,
                'conditions_met' => true,
                'rule_type' => $systemConfig['rule_type'],
                'reason' => 'No active target URLs available'
            ]
        ];
        
        echo json_encode($response, JSON_PRETTY_PRINT);
        exit;
    }
    
    switch ($systemConfig['rule_type']) {
        case 'static_route':
            // Select URL with highest priority (lowest number)
            usort($activeUrls, function($a, $b) {
                if ($a['priority'] !== $b['priority']) {
                    return $a['priority'] - $b['priority'];
                }
                return $b['weight'] - $a['weight'];
            });
            $targetUrl = $activeUrls[0]['url'];
            break;
            
        case 'random_route':
            // Random selection based on weight
            $totalWeight = array_sum(array_column($activeUrls, 'weight'));
            $random = mt_rand(1, $totalWeight);
            $cumulativeWeight = 0;
            
            foreach ($activeUrls as $url) {
                $cumulativeWeight += $url['weight'];
                if ($random <= $cumulativeWeight) {
                    $targetUrl = $url['url'];
                    break;
                }
            }
            
            // Fallback if algorithm fails
            if (!$targetUrl) {
                $targetUrl = $activeUrls[array_rand($activeUrls)]['url'];
            }
            break;
            
        case 'mute_unmute':
            // Check mute/unmute cycle state
            $now = time();
            $lastToggle = $systemConfig['last_toggle_time'] ? strtotime($systemConfig['last_toggle_time']) : null;
            $currentState = $systemConfig['current_state'] ?: 'normal';
            
            // Initialize cycle if first time
            if (!$lastToggle) {
                $currentState = 'unmute';
                $lastToggle = $now;
                // In production, update database with new state and timestamp
            }
            
            // Check if toggle is needed
            $timeSinceToggle = $now - $lastToggle;
            $duration = ($currentState === 'mute') ? $systemConfig['mute_duration'] : $systemConfig['unmute_duration'];
            
            if ($timeSinceToggle >= $duration) {
                $currentState = ($currentState === 'mute') ? 'unmute' : 'mute';
                $lastToggle = $now;
                // In production, update database
            }
            
            // Apply decision based on current state
            if ($currentState === 'unmute') {
                usort($activeUrls, function($a, $b) {
                    if ($a['priority'] !== $b['priority']) {
                        return $a['priority'] - $b['priority'];
                    }
                    return $b['weight'] - $a['weight'];
                });
                $targetUrl = $activeUrls[0]['url'];
            }
            // If muted, targetUrl remains null (normal routing)
            
            break;
            
        default:
            // Fallback to static route
            $targetUrl = $activeUrls[0]['url'];
            break;
    }
    
    $decision = $targetUrl ? 'target' : 'normal';
    $processingTime = round((microtime(true) - $startTime) * 1000);
    
    // Build comprehensive response
    $response = [
        'decision' => $decision,
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
            'performance_class' => $processingTime < 100 ? 'excellent' : ($processingTime < 500 ? 'good' : 'acceptable')
        ]
    ];
    
    if ($targetUrl) {
        $response['target_url'] = $targetUrl;
        $response['target_info'] = [
            'rule_used' => $ruleApplied,
            'selection_method' => $systemConfig['rule_type']
        ];
    }
    
    echo json_encode($response, JSON_PRETTY_PRINT);
    
} catch (Exception $e) {
    error_log("Routing API Error: " . $e->getMessage());
    
    http_response_code(500);
    echo json_encode([
        'error' => 'Internal server error',
        'message' => 'An unexpected error occurred',
        'decision' => 'normal',
        'system_state' => 'normal',
        'processing_time_ms' => round((microtime(true) - $startTime) * 1000),
        'timestamp' => date('c'),
        'debug' => [
            'error_occurred' => true,
            'fallback_applied' => true
        ]
    ], JSON_PRETTY_PRINT);
}
?>