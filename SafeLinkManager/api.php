<?php
// api.php - Safe Link Manager API Endpoint

define('SLM_INIT', true);

require_once __DIR__ . '/config.php';
require_once __DIR__ . '/functions.php';

// Set headers
header('Content-Type: application/json');
slm_set_cors_headers();

// Get request method and action
$method = $_SERVER['REQUEST_METHOD'];
$action = $_GET['action'] ?? '';

try {
    switch ($action) {
        
        // Get all routes
        case 'get_routes':
            if ($method !== 'GET') {
                slm_json_error('Method not allowed', 405);
            }
            $routes = slm_load_routes();
            slm_json_success($routes, 'Routes retrieved');
            break;
        
        // Get single route
        case 'get_route':
            if ($method !== 'GET') {
                slm_json_error('Method not allowed', 405);
            }
            $slug = $_GET['slug'] ?? '';
            if (empty($slug)) {
                slm_json_error('Slug required');
            }
            $url = slm_get_route($slug);
            if ($url === null) {
                slm_json_error('Route not found', 404);
            }
            slm_json_success(['slug' => $slug, 'url' => $url], 'Route retrieved');
            break;
        
        // Set route (add or update)
        case 'set_route':
            if ($method !== 'POST') {
                slm_json_error('Method not allowed', 405);
            }
            $data = slm_get_json_input();
            $slug = slm_sanitize($data['slug'] ?? '');
            $url = $data['url'] ?? '';
            
            slm_set_route($slug, $url);
            slm_json_success(['slug' => $slug, 'url' => $url], 'Route saved');
            break;
        
        // Delete route
        case 'delete_route':
            if ($method !== 'DELETE' && $method !== 'POST') {
                slm_json_error('Method not allowed', 405);
            }
            $data = $method === 'DELETE' ? slm_get_json_input() : $_POST;
            $slug = slm_sanitize($data['slug'] ?? '');
            
            slm_delete_route($slug);
            slm_json_success(null, 'Route deleted');
            break;
        
        // Bulk set routes
        case 'set_routes':
            if ($method !== 'POST') {
                slm_json_error('Method not allowed', 405);
            }
            $data = slm_get_json_input();
            $routes = $data['routes'] ?? [];
            
            if (!is_array($routes)) {
                slm_json_error('Routes must be an object');
            }
            
            // Validate all routes
            foreach ($routes as $slug => $url) {
                if (!slm_is_valid_url($url)) {
                    slm_json_error("Invalid URL for slug: $slug");
                }
            }
            
            slm_save_routes($routes);
            slm_json_success($routes, 'Routes imported');
            break;
        
        // Clear all routes
        case 'clear_routes':
            if ($method !== 'POST' && $method !== 'DELETE') {
                slm_json_error('Method not allowed', 405);
            }
            slm_save_routes([]);
            slm_json_success(null, 'All routes cleared');
            break;
        
        // Generate signed URL
        case 'sign_url':
            if ($method !== 'POST') {
                slm_json_error('Method not allowed', 405);
            }
            $data = slm_get_json_input();
            $baseUrl = $data['baseUrl'] ?? '';
            $targetUrl = $data['targetUrl'] ?? '';
            $expirationMinutes = intval($data['expirationMinutes'] ?? 10);
            $secret = $data['secret'] ?? null;
            
            if (!slm_is_valid_url($baseUrl)) {
                slm_json_error('Invalid base URL');
            }
            if (!slm_is_valid_url($targetUrl)) {
                slm_json_error('Invalid target URL');
            }
            
            $signedUrl = slm_generate_signed_url($baseUrl, $targetUrl, $expirationMinutes, $secret);
            slm_json_success(['url' => $signedUrl], 'URL signed');
            break;
        
        // Verify signed URL
        case 'verify_url':
            if ($method !== 'POST') {
                slm_json_error('Method not allowed', 405);
            }
            $data = slm_get_json_input();
            $targetUrl = $data['targetUrl'] ?? '';
            $expiration = $data['expiration'] ?? '';
            $signature = $data['signature'] ?? '';
            $secret = $data['secret'] ?? null;
            
            $isValid = slm_verify_signature($targetUrl, $expiration, $signature, $secret);
            slm_json_success([
                'valid' => $isValid,
                'expired' => time() > intval($expiration)
            ], $isValid ? 'Signature valid' : 'Signature invalid');
            break;
        
        // Generate A/B test URLs
        case 'generate_ab':
            if ($method !== 'POST') {
                slm_json_error('Method not allowed', 405);
            }
            $data = slm_get_json_input();
            $baseUrl = $data['baseUrl'] ?? '';
            $urlA = $data['urlA'] ?? '';
            $urlB = $data['urlB'] ?? '';
            $expirationMinutes = intval($data['expirationMinutes'] ?? 10);
            $secret = $data['secret'] ?? null;
            
            if (!slm_is_valid_url($baseUrl)) {
                slm_json_error('Invalid base URL');
            }
            if (!slm_is_valid_url($urlA)) {
                slm_json_error('Invalid URL A');
            }
            if (!slm_is_valid_url($urlB)) {
                slm_json_error('Invalid URL B');
            }
            
            $urls = slm_generate_ab_urls($baseUrl, $urlA, $urlB, $expirationMinutes, $secret);
            slm_json_success($urls, 'A/B URLs generated');
            break;
        
        // Simulate A/B variant selection
        case 'simulate_ab':
            if ($method !== 'POST') {
                slm_json_error('Method not allowed', 405);
            }
            $data = slm_get_json_input();
            $visitorKey = $data['visitorKey'] ?? '';
            $weightA = intval($data['weightA'] ?? 50);
            
            $variant = slm_pick_ab_variant($visitorKey, $weightA);
            slm_json_success(['variant' => $variant], 'Variant selected');
            break;
        
        // Evaluate rules
        case 'evaluate_rules':
            if ($method !== 'POST') {
                slm_json_error('Method not allowed', 405);
            }
            $data = slm_get_json_input();
            $result = slm_evaluate_rules($data);
            slm_json_success($result, 'Rules evaluated');
            break;
        
        // Detect mobile from user agent
        case 'detect_mobile':
            if ($method !== 'POST') {
                slm_json_error('Method not allowed', 405);
            }
            $data = slm_get_json_input();
            $userAgent = $data['userAgent'] ?? '';
            $isMobile = slm_is_mobile_ua($userAgent);
            slm_json_success(['isMobile' => $isMobile], 'Mobile detected');
            break;
        
        // Health check
        case 'health':
            slm_json_success([
                'version' => SLM_VERSION,
                'timestamp' => time(),
                'routes_count' => count(slm_load_routes())
            ], 'API healthy');
            break;
        
        default:
            slm_json_error('Invalid action', 404);
    }
    
} catch (Exception $e) {
    slm_json_error($e->getMessage(), 500);
}