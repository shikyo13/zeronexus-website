<?php
/**
 * Security Headers Checker API (Refactored)
 * 
 * Fetches and analyzes HTTP security headers for a given website
 * Using modular utilities for better code organization
 * 
 * Parameters:
 * - url: The website URL to check
 */

// Load shared utilities
require_once __DIR__ . '/lib/autoload.php';

// Initialize utilities
$config = Config::getInstance();
$response = new Response();
$cache = new Cache('security-headers');

// Handle CORS with proper origin restrictions
CORS::simple(false); // Use configured origins, not allow all

// Apply rate limiting (20 requests per minute for this endpoint)
RateLimit::simple('security-headers');

// Validate input
$validator = new Validator();
$validator->required('url', 'URL parameter is required')
          ->url('url', 'Please provide a valid URL');

if ($validator->fails()) {
    $response->validationError($validator->errors());
}

$url = $validator->get('url');

// Prepare URL for fetching
if (!preg_match('/^https?:\/\//i', $url)) {
    $url = 'https://' . $url;
}

// Try to get from cache
$cacheKey = 'headers_' . md5($url);
$cacheTTL = $config->getCacheTTL('security-headers') ?? 3600; // 1 hour default

$result = $cache->remember($cacheKey, function() use ($url, $config) {
    // Security headers to check for (normalized to lowercase)
    $securityHeaders = [
        'content-security-policy',
        'strict-transport-security',
        'x-content-type-options',
        'x-frame-options',
        'x-xss-protection',
        'referrer-policy',
        'permissions-policy',
        'cache-control',
        'content-security-policy-report-only',
        'public-key-pins',
        'cross-origin-embedder-policy',
        'cross-origin-opener-policy',
        'cross-origin-resource-policy',
        'access-control-allow-origin'
    ];
    
    // Create context for the request
    $context = stream_context_create([
        'http' => [
            'method' => 'GET',
            'timeout' => 10,
            'header' => [
                'User-Agent: Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36',
                'Accept: text/html,application/xhtml+xml,application/xml;q=0.9,*/*;q=0.8',
                'Accept-Language: en-US,en;q=0.5',
                'Connection: close'
            ]
        ],
        'ssl' => [
            // SSL verification disabled for home server setup with Cloudflare
            // This is intentional - see Config.php for architectural explanation
            'verify_peer' => $config->get('ssl_verify', false),
            'verify_peer_name' => $config->get('ssl_verify', false)
        ]
    ]);
    
    // Fetch headers
    $allHeaders = @get_headers($url, 1, $context);
    
    if ($allHeaders === false) {
        // Try with HTTP if HTTPS failed
        if (stripos($url, 'https://') === 0) {
            $httpUrl = 'http://' . substr($url, 8);
            $allHeaders = @get_headers($httpUrl, 1, $context);
            
            if ($allHeaders === false) {
                throw new Exception('Failed to fetch headers from the website using both HTTPS and HTTP.');
            }
            
            $url = $httpUrl;
        } else {
            throw new Exception('Failed to fetch headers from the website.');
        }
    }
    
    // Normalize headers to lowercase
    $normalizedHeaders = [];
    foreach ($allHeaders as $key => $value) {
        if (is_string($key)) {
            $normalizedHeaders[strtolower($key)] = $value;
        }
    }
    
    // Prepare response
    $result = [
        'url' => $url,
        'headers' => [],
        'security_headers' => [],
        'missing_headers' => [],
        'timestamp' => time()
    ];
    
    // Extract security headers
    foreach ($securityHeaders as $header) {
        if (isset($normalizedHeaders[$header])) {
            $value = $normalizedHeaders[$header];
            if (is_array($value)) {
                $value = implode("; ", $value);
            }
            $result['security_headers'][$header] = $value;
        } else {
            $result['missing_headers'][] = $header;
        }
    }
    
    // Add all headers
    foreach ($normalizedHeaders as $key => $value) {
        if (is_string($key)) {
            if (is_array($value)) {
                $result['headers'][$key] = implode("; ", $value);
            } else {
                $result['headers'][$key] = $value;
            }
        }
    }
    
    // Add security score
    $totalHeaders = count($securityHeaders);
    $presentHeaders = count($result['security_headers']);
    $result['security_score'] = [
        'present' => $presentHeaders,
        'total' => $totalHeaders,
        'percentage' => round(($presentHeaders / $totalHeaders) * 100, 2)
    ];
    
    return $result;
}, $cacheTTL);

// Add cache status header
if ($cache->get($cacheKey) !== null) {
    header('X-Cache: HIT');
} else {
    header('X-Cache: MISS');
}

// Send successful response
$response->success($result);