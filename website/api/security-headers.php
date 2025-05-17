<?php
/**
 * Security Headers Checker API
 * 
 * Fetches and analyzes HTTP security headers for a given website
 * 
 * Parameters:
 * - url: The website URL to check
 */

// Add timestamp for debugging
error_log("Security Headers API called at: " . date('Y-m-d H:i:s'));

// Set security headers
header('Content-Type: application/json');
header('X-Content-Type-Options: nosniff');
header('X-Frame-Options: DENY');
header('Referrer-Policy: strict-origin-when-cross-origin');
header('X-XSS-Protection: 1; mode=block');

// Enable CORS for specific origins, with Cloudflare support
$allowedOrigins = [
    'https://zeronexus.net',
    'https://www.zeronexus.net',
    'http://localhost:8081', // For local development
    'http://localhost:8082'  // For alternative local development
];

$origin = isset($_SERVER['HTTP_ORIGIN']) ? $_SERVER['HTTP_ORIGIN'] : '';

// Temporarily allow all origins for testing
header("Access-Control-Allow-Origin: *");
header('Access-Control-Allow-Methods: GET, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, CF-Connecting-IP, CF-IPCountry, CF-Ray, CF-Visitor, X-Forwarded-For, X-Forwarded-Proto');
header('Vary: Origin');

// Original CORS logic - temporarily disabled for testing
// Check if origin is allowed or is a subdomain of zeronexus.net
// $isAllowed = in_array($origin, $allowedOrigins);
// if (!$isAllowed && preg_match('/^https?:\/\/.*\.zeronexus\.net(:[0-9]+)?$/', $origin)) {
//     $isAllowed = true;
// }
// 
// if ($isAllowed) {
//     header("Access-Control-Allow-Origin: $origin");
//     header('Access-Control-Allow-Methods: GET, OPTIONS');
//     header('Access-Control-Allow-Headers: Content-Type, CF-Connecting-IP, CF-IPCountry, CF-Ray, CF-Visitor, X-Forwarded-For, X-Forwarded-Proto');
//     header('Vary: Origin');
// }

// Exit on OPTIONS request (preflight)
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    exit(0);
}

// Rate limiting based on IP with Cloudflare support
function checkRateLimit() {
    // Get the real client IP using Cloudflare's headers if available
    $ip = isset($_SERVER['HTTP_CF_CONNECTING_IP']) ? $_SERVER['HTTP_CF_CONNECTING_IP'] : $_SERVER['REMOTE_ADDR'];
    $rateLimitFile = sys_get_temp_dir() . '/zeronexus_sec_headers_rate_' . md5($ip);
    $currentTime = time();
    
    // Check if file exists and read it
    if (file_exists($rateLimitFile)) {
        $data = json_decode(file_get_contents($rateLimitFile), true);
        
        // Reset counter if more than 1 minute has passed
        if ($currentTime - $data['timestamp'] > 60) {
            $data = [
                'count' => 1,
                'timestamp' => $currentTime
            ];
        } else {
            $data['count']++;
            
            // If more than 15 requests in a minute, rate limit
            if ($data['count'] > 15) {
                http_response_code(429);
                echo json_encode(['error' => true, 'message' => 'Too many requests. Please try again later.']);
                exit;
            }
        }
    } else {
        $data = [
            'count' => 1,
            'timestamp' => $currentTime
        ];
    }
    
    // Write updated data
    file_put_contents($rateLimitFile, json_encode($data));
}

// Temporarily disable rate limiting for troubleshooting
// checkRateLimit();

// Get parameters
$url = isset($_GET['url']) ? trim($_GET['url']) : '';

// Validate URL
if (empty($url)) {
    http_response_code(400);
    echo json_encode(['error' => true, 'message' => 'URL parameter is required']);
    exit;
}

// Check cache
$cacheDir = sys_get_temp_dir() . '/zeronexus_headers_cache/';
if (!is_dir($cacheDir)) {
    mkdir($cacheDir, 0755, true);
}

$cacheKey = md5("headers_{$url}");
$cacheFile = $cacheDir . $cacheKey . '.json';
$cacheLifetime = 3600; // 1 hour cache

// Temporarily disable cache for troubleshooting
// Check if we have a fresh cache
// if (file_exists($cacheFile) && (time() - filemtime($cacheFile) < $cacheLifetime)) {
//     header('X-Cache: HIT');
//     echo file_get_contents($cacheFile);
//     exit;
// }

// Prepare URL for fetching
if (!preg_match('/^https?:\/\//i', $url)) {
    // Always prefix with HTTPS for security
    $url = 'https://' . $url;
}

// Enhanced logging for debugging
error_log("=========================================");
error_log("Security Headers API: Fetching headers for URL: " . $url);
error_log("Client IP: " . $_SERVER['REMOTE_ADDR']);
error_log("User Agent: " . $_SERVER['HTTP_USER_AGENT']);
error_log("Temp directory: " . sys_get_temp_dir());
error_log("Is SSL: " . (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? 'Yes' : 'No'));

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

// Prepare the response
$response = [
    'url' => $url,
    'headers' => [],
    'timestamp' => time()
];

// Create context for the request
$context = stream_context_create([
    'http' => [
        'method' => 'GET',
        'timeout' => 10, // 10 second timeout
        'header' => [
            'User-Agent: Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/91.0.4472.124 Safari/537.36',
            'Accept: text/html,application/xhtml+xml,application/xml;q=0.9,image/webp,*/*;q=0.8',
            'Accept-Language: en-US,en;q=0.5',
            'Connection: close'
        ]
    ],
    'ssl' => [
        'verify_peer' => false,
        'verify_peer_name' => false
    ]
]);

// Enable error logging but disable display
ini_set('display_errors', 0);
ini_set('log_errors', 1);
error_reporting(E_ALL);

// Wrap in try/catch for extra safety
try {
    // Use a more detailed approach to get headers with fallback to HTTP
    try {
        $allHeaders = @get_headers($url, 1, $context);
        
        if ($allHeaders === false) {
            // Try with HTTP if HTTPS failed
            if (stripos($url, 'https://') === 0) {
                $httpUrl = 'http://' . substr($url, 8);
                error_log("HTTPS failed, trying HTTP: " . $httpUrl);
                $allHeaders = @get_headers($httpUrl, 1, $context);
                
                if ($allHeaders === false) {
                    throw new Exception('Failed to fetch headers from the website using both HTTPS and HTTP.');
                }
                
                // Successfully got headers with HTTP, so update the URL
                $url = $httpUrl;
            } else {
                throw new Exception('Failed to fetch headers from the website.');
            }
        }
    } catch (Exception $e) {
        error_log("Error fetching headers: " . $e->getMessage());
        throw new Exception('Failed to connect to the website. Please check if the domain is correct and accessible.');
    }
    
    // Normalize headers to lowercase
    $normalizedHeaders = [];
    foreach ($allHeaders as $key => $value) {
        if (is_string($key)) {
            $normalizedHeaders[strtolower($key)] = $value;
        }
    }
    
    // Extract all security headers
    foreach ($securityHeaders as $header) {
        if (isset($normalizedHeaders[$header])) {
            $response['headers'][$header] = $normalizedHeaders[$header];
            
            // Handle array values (for headers that can be sent multiple times)
            if (is_array($response['headers'][$header])) {
                $response['headers'][$header] = implode("; ", $response['headers'][$header]);
            }
        }
    }
    
    // Add all other headers
    foreach ($normalizedHeaders as $key => $value) {
        if (!isset($response['headers'][$key]) && is_string($key)) {
            // Skip numeric keys and headers already added
            if (is_array($value)) {
                $response['headers'][$key] = implode("; ", $value);
            } else {
                $response['headers'][$key] = $value;
            }
        }
    }
    
    // Temporarily disable caching for troubleshooting
    // Cache the successful result
    // @file_put_contents($cacheFile, json_encode($response));
    
    // Return the response
    header('X-Cache: MISS');
    echo json_encode($response);
} catch (Exception $e) {
    // Provide a clean JSON error response
    http_response_code(500);
    echo json_encode(['error' => true, 'message' => 'Header check failed: ' . $e->getMessage()]);
} catch (Error $e) {
    // Catch PHP 7+ errors as well
    http_response_code(500);
    echo json_encode(['error' => true, 'message' => 'Server error during header check: ' . $e->getMessage()]);
}