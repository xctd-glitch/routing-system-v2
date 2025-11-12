<?php
// config.php - Safe Link Manager Configuration

// Prevent direct access
if (!defined('SLM_INIT')) {
    http_response_code(403);
    die('Direct access not permitted');
}

// Application Settings
define('SLM_VERSION', '1.0.0');
define('SLM_APP_NAME', 'Safe Link Manager');

// Security Settings
define('SLM_SECRET_KEY', getenv('SRP_REDIRECT_SECRET') ?: '0000000000000000000000000000000000000000000000000000000000000000');
define('SLM_ALLOWED_ORIGINS', ['http://localhost', 'http://localhost:8080', 'http://127.0.0.1']);

// File Paths
define('SLM_ROUTES_FILE', __DIR__ . '/routes.json');

// System Settings
define('SLM_SYSTEM_ON', true);
define('SLM_ALLOWED_COUNTRIES', ['us', 'id', 'sg']); // Default allowed countries
define('SLM_FLAG_WAP', true);
define('SLM_FLAG_VPN', true);
define('SLM_FLAG_PROXY', true);
define('SLM_FLAG_BOT', true);

// API Settings
define('SLM_API_TIMEOUT', 30);
define('SLM_MAX_ROUTES', 1000);

// CORS Settings
function slm_set_cors_headers() {
    $origin = $_SERVER['HTTP_ORIGIN'] ?? '';
    if (in_array($origin, SLM_ALLOWED_ORIGINS)) {
        header("Access-Control-Allow-Origin: $origin");
    }
    header("Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS");
    header("Access-Control-Allow-Headers: Content-Type, Authorization");
    header("Access-Control-Max-Age: 3600");
    
    if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
        http_response_code(204);
        exit;
    }
}

// Error Handler
function slm_json_error($message, $code = 400) {
    http_response_code($code);
    echo json_encode([
        'success' => false,
        'error' => $message,
        'timestamp' => time()
    ]);
    exit;
}

// Success Response
function slm_json_success($data = null, $message = 'Success') {
    echo json_encode([
        'success' => true,
        'message' => $message,
        'data' => $data,
        'timestamp' => time()
    ]);
    exit;
}