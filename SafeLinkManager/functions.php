<?php
// functions.php - Safe Link Manager Core Functions

if (!defined('SLM_INIT')) {
    http_response_code(403);
    die('Direct access not permitted');
}

/**
 * HMAC-SHA256 Signing Functions
 */

// Convert hex string to binary
function slm_hex_to_bin($hex) {
    $hex = strtolower(trim($hex));
    if (!preg_match('/^([0-9a-f]{2}){32}$/', $hex)) {
        throw new Exception('Secret must be 64 hex characters');
    }
    return hex2bin($hex);
}

// Base64 URL-safe encoding
function slm_base64_url_encode($data) {
    return rtrim(strtr(base64_encode($data), '+/', '-_'), '=');
}

// Base64 URL-safe decoding
function slm_base64_url_decode($data) {
    return base64_decode(strtr($data, '-_', '+/'));
}

// Generate HMAC-SHA256 signature
function slm_sign_url($url, $expiration, $secret = null) {
    $secret = $secret ?? SLM_SECRET_KEY;
    $key = slm_hex_to_bin($secret);
    $message = $url . '|' . $expiration;
    $signature = hash_hmac('sha256', $message, $key, true);
    return slm_base64_url_encode($signature);
}

// Verify HMAC-SHA256 signature
function slm_verify_signature($url, $expiration, $signature, $secret = null) {
    try {
        $expected = slm_sign_url($url, $expiration, $secret);
        return hash_equals($expected, $signature);
    } catch (Exception $e) {
        return false;
    }
}

// Generate signed redirect URL
function slm_generate_signed_url($baseUrl, $targetUrl, $expirationMinutes = 10, $secret = null) {
    $baseUrl = rtrim($baseUrl, '/');
    $expiration = time() + ($expirationMinutes * 60);
    $signature = slm_sign_url($targetUrl, $expiration, $secret);
    
    return sprintf(
        '%s/go?to=%s&exp=%d&sig=%s',
        $baseUrl,
        urlencode($targetUrl),
        $expiration,
        urlencode($signature)
    );
}

/**
 * Route Management Functions
 */

// Load routes from JSON file
function slm_load_routes() {
    if (!file_exists(SLM_ROUTES_FILE)) {
        return [];
    }
    
    $content = file_get_contents(SLM_ROUTES_FILE);
    $routes = json_decode($content, true);
    
    return is_array($routes) ? $routes : [];
}

// Save routes to JSON file
function slm_save_routes($routes) {
    if (count($routes) > SLM_MAX_ROUTES) {
        throw new Exception('Maximum routes limit exceeded');
    }
    
    $json = json_encode($routes, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);
    if ($json === false) {
        throw new Exception('Failed to encode routes');
    }
    
    if (file_put_contents(SLM_ROUTES_FILE, $json) === false) {
        throw new Exception('Failed to save routes');
    }
    
    return true;
}

// Get single route
function slm_get_route($slug) {
    $routes = slm_load_routes();
    return $routes[$slug] ?? null;
}

// Add or update route
function slm_set_route($slug, $url) {
    $slug = trim($slug);
    $url = trim($url);
    
    if (empty($slug)) {
        throw new Exception('Slug cannot be empty');
    }
    
    if (!slm_is_valid_url($url)) {
        throw new Exception('Invalid URL');
    }
    
    $routes = slm_load_routes();
    $routes[$slug] = $url;
    slm_save_routes($routes);
    
    return true;
}

// Delete route
function slm_delete_route($slug) {
    $routes = slm_load_routes();
    if (!isset($routes[$slug])) {
        throw new Exception('Route not found');
    }
    
    unset($routes[$slug]);
    slm_save_routes($routes);
    
    return true;
}

/**
 * Validation Functions
 */

// Validate HTTP/HTTPS URL
function slm_is_valid_url($url) {
    if (empty($url)) {
        return false;
    }
    
    $parsed = parse_url($url);
    if (!$parsed || !isset($parsed['scheme']) || !isset($parsed['host'])) {
        return false;
    }
    
    return in_array(strtolower($parsed['scheme']), ['http', 'https']);
}

// Parse CSV to lowercase array
function slm_parse_csv_lower($csv) {
    if (empty($csv)) {
        return [];
    }
    
    $items = array_map('trim', explode(',', $csv));
    $items = array_map('strtolower', $items);
    return array_filter($items);
}

// Detect mobile user agent
function slm_is_mobile_ua($userAgent) {
    $pattern = '/android|iphone|ipod|ipad|mobile|iemobile|blackberry|opera mini|opera mobi|webos/i';
    return preg_match($pattern, strtolower($userAgent)) === 1;
}

/**
 * A/B Testing Functions
 */

// Deterministic A/B picker based on visitor key
function slm_pick_ab_variant($visitorKey, $weightA = 50) {
    $hash = hash('sha256', $visitorKey, true);
    $byte = ord($hash[0]);
    $percentage = $byte % 100;
    
    $weightA = max(0, min(100, intval($weightA)));
    return $percentage < $weightA ? 'A' : 'B';
}

// Generate A/B test URL pair
function slm_generate_ab_urls($baseUrl, $urlA, $urlB, $expirationMinutes = 10, $secret = null) {
    return [
        'A' => slm_generate_signed_url($baseUrl, $urlA, $expirationMinutes, $secret),
        'B' => slm_generate_signed_url($baseUrl, $urlB, $expirationMinutes, $secret)
    ];
}

/**
 * Rule Evaluation Functions
 */

// Evaluate access rules
function slm_evaluate_rules($params) {
    $systemOn = $params['systemOn'] ?? SLM_SYSTEM_ON;
    $allowedCountries = $params['allowedCountries'] ?? [];
    $country = strtolower($params['country'] ?? '');
    $isMobile = $params['isMobile'] ?? false;
    $isVpn = $params['isVpn'] ?? false;
    $isProxy = $params['isProxy'] ?? false;
    $isBot = $params['isBot'] ?? false;
    
    $flagWap = $params['flagWap'] ?? SLM_FLAG_WAP;
    $flagVpn = $params['flagVpn'] ?? SLM_FLAG_VPN;
    $flagProxy = $params['flagProxy'] ?? SLM_FLAG_PROXY;
    $flagBot = $params['flagBot'] ?? SLM_FLAG_BOT;
    
    $reasons = [];
    $pass = true;
    
    if (!$systemOn) {
        $pass = false;
        $reasons[] = 'System OFF';
    }
    
    if (!empty($allowedCountries) && !in_array($country, $allowedCountries)) {
        $pass = false;
        $reasons[] = 'Country not in allowlist';
    }
    
    if ($flagWap && !$isMobile) {
        $pass = false;
        $reasons[] = 'Not WAP/mobile';
    }
    
    if ($flagVpn && $isVpn) {
        $pass = false;
        $reasons[] = 'VPN must be false';
    }
    
    if ($flagProxy && $isProxy) {
        $pass = false;
        $reasons[] = 'Proxy must be false';
    }
    
    if ($flagBot && $isBot) {
        $pass = false;
        $reasons[] = 'Bot/Crawler must be false';
    }
    
    return [
        'pass' => $pass,
        'decision' => $pass ? 'REDIRECT' : 'NORMAL FLOW',
        'reasons' => empty($reasons) ? ['All conditions met'] : $reasons
    ];
}

/**
 * Utility Functions
 */

// Sanitize input
function slm_sanitize($input) {
    if (is_array($input)) {
        return array_map('slm_sanitize', $input);
    }
    return htmlspecialchars(strip_tags(trim($input)), ENT_QUOTES, 'UTF-8');
}

// Get request body as JSON
function slm_get_json_input() {
    $input = file_get_contents('php://input');
    if (empty($input)) {
        return [];
    }
    
    $data = json_decode($input, true);
    return is_array($data) ? $data : [];
}