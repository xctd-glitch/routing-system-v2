<?php
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
    exit;
}

try {
    $startTime = microtime(true);
    
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        // Handle configuration updates
        $input = file_get_contents('php://input');
        $data = json_decode($input, true);
        
        if (json_last_error() !== JSON_ERROR_NONE) {
            http_response_code(400);
            echo json_encode(['error' => 'Invalid JSON']);
            exit;
        }
        
        // Mock configuration update (implement database update in production)
        $updatedConfig = [
            'id' => 1,
            'name' => 'Default Configuration',
            'system_on' => isset($data['system_on']) ? (bool)$data['system_on'] : false,
            'is_active' => isset($data['is_active']) ? (bool)$data['is_active'] : false,
            'rule_type' => $data['rule_type'] ?? 'static_route',
            'mute_duration' => isset($data['mute_duration']) ? (int)$data['mute_duration'] : 120,
            'unmute_duration' => isset($data['unmute_duration']) ? (int)$data['unmute_duration'] : 120,
            'current_state' => 'normal',
            'last_toggle_time' => null,
            'updated_at' => date('c'),
            'is_default' => true,
            'processing_time_ms' => round((microtime(true) - $startTime) * 1000)
        ];
        
        echo json_encode($updatedConfig, JSON_PRETTY_PRINT);
        
    } else {
        // Return current configuration
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
            'api_version' => '1.0'
        ];
        
        echo json_encode($currentConfig, JSON_PRETTY_PRINT);
    }
    
} catch (Exception $e) {
    error_log("Config API Error: " . $e->getMessage());
    
    http_response_code(500);
    echo json_encode([
        'error' => 'Configuration error',
        'message' => 'Failed to process configuration request',
        'timestamp' => date('c')
    ]);
}
?>