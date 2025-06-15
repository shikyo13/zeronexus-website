<?php
/**
 * DNS Lookup API
 * 
 * Performs DNS lookups for different record types
 * 
 * Parameters:
 * - domain: The domain name to lookup
 * - type: DNS record type (A, AAAA, MX, TXT, NS, SOA, CNAME, PTR)
 */

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

// Check if origin is allowed or is a subdomain of zeronexus.net
$isAllowed = in_array($origin, $allowedOrigins);
if (!$isAllowed && preg_match('/^https?:\/\/.*\.zeronexus\.net(:[0-9]+)?$/', $origin)) {
    $isAllowed = true;
}

if ($isAllowed) {
    header("Access-Control-Allow-Origin: $origin");
    header('Access-Control-Allow-Methods: GET, OPTIONS');
    header('Access-Control-Allow-Headers: Content-Type, CF-Connecting-IP, CF-IPCountry, CF-Ray, CF-Visitor, X-Forwarded-For, X-Forwarded-Proto');
    header('Vary: Origin');
}

// Exit on OPTIONS request (preflight)
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    exit(0);
}

// Rate limiting based on IP with Cloudflare support
function checkRateLimit() {
    // Get the real client IP using Cloudflare's headers if available
    $ip = isset($_SERVER['HTTP_CF_CONNECTING_IP']) ? $_SERVER['HTTP_CF_CONNECTING_IP'] : $_SERVER['REMOTE_ADDR'];
    $rateLimitFile = sys_get_temp_dir() . '/zeronexus_dns_lookup_rate_' . md5($ip);
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
            
            // If more than 20 requests in a minute, rate limit
            if ($data['count'] > 20) {
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

// Apply rate limiting
checkRateLimit();

// Get parameters
$domain = isset($_GET['domain']) ? trim($_GET['domain']) : '';
$type = isset($_GET['type']) ? strtoupper(trim($_GET['type'])) : 'A';

// Validate domain
if (empty($domain)) {
    http_response_code(400);
    echo json_encode(['error' => true, 'message' => 'Domain parameter is required']);
    exit;
}

// Validate domain or IP format
$isDomain = preg_match('/^[a-zA-Z0-9\-\.]+\.[a-zA-Z]{2,}$/', $domain);
$isIPv4 = filter_var($domain, FILTER_VALIDATE_IP, FILTER_FLAG_IPV4);
$isIPv6 = filter_var($domain, FILTER_VALIDATE_IP, FILTER_FLAG_IPV6);

// For PTR records, the domain should be an IP address
if ($type === 'PTR' && !$isIPv4 && !$isIPv6) {
    http_response_code(400);
    echo json_encode(['error' => true, 'message' => 'For PTR lookups, please provide a valid IP address']);
    exit;
}

// For records other than PTR, we need a domain
if ($type !== 'PTR' && !$isDomain) {
    http_response_code(400);
    echo json_encode(['error' => true, 'message' => 'Please enter a valid domain name (e.g., example.com)']);
    exit;
}

// Allowed record types
$allowedTypes = ['A', 'AAAA', 'MX', 'TXT', 'NS', 'SOA', 'CNAME', 'PTR', 'CAA', 'SRV', 'DNSKEY', 'DS', 'RRSIG', 'NAPTR', 'ANY'];

if (!in_array($type, $allowedTypes)) {
    http_response_code(400);
    echo json_encode(['error' => true, 'message' => 'Invalid DNS record type']);
    exit;
}

// Check cache
$cacheDir = sys_get_temp_dir() . '/zeronexus_dns_cache/';
if (!is_dir($cacheDir)) {
    mkdir($cacheDir, 0755, true);
}

$cacheKey = md5("dns_{$domain}_{$type}");
$cacheFile = $cacheDir . $cacheKey . '.json';
$cacheLifetime = 3600; // 1 hour cache

// Check if we have a fresh cache
if (file_exists($cacheFile) && (time() - filemtime($cacheFile) < $cacheLifetime)) {
    header('X-Cache: HIT');
    echo file_get_contents($cacheFile);
    exit;
}

// Prepare response
$response = [
    'domain' => $domain,
    'type' => $type,
    'records' => [],
    'timestamp' => time()
];

// If this is a PTR lookup, prepare the reverse lookup domain
if ($type === 'PTR') {
    if ($isIPv4) {
        // IPv4 - Reverse the octets and append in-addr.arpa
        $reversed = implode('.', array_reverse(explode('.', $domain))) . '.in-addr.arpa';
    } else {
        // IPv6 - More complex transformation
        $ipv6 = inet_pton($domain);
        $reversed = '';
        for ($i = strlen($ipv6) - 1; $i >= 0; $i--) {
            $reversed .= sprintf('%02x.%02x.', ord($ipv6[$i]) & 0xf, ord($ipv6[$i]) >> 4);
        }
        $reversed .= 'ip6.arpa';
    }
    
    $lookupDomain = $reversed;
} else {
    $lookupDomain = $domain;
}

// Map the UI record types to DNS_* constants
// Check that constants exist before using them (PHP may not have all DNS constants defined)
$dnsTypeConstants = [];

if (defined('DNS_A')) $dnsTypeConstants['A'] = DNS_A;
if (defined('DNS_AAAA')) $dnsTypeConstants['AAAA'] = DNS_AAAA;
if (defined('DNS_MX')) $dnsTypeConstants['MX'] = DNS_MX;
if (defined('DNS_TXT')) $dnsTypeConstants['TXT'] = DNS_TXT;
if (defined('DNS_NS')) $dnsTypeConstants['NS'] = DNS_NS;
if (defined('DNS_SOA')) $dnsTypeConstants['SOA'] = DNS_SOA;
if (defined('DNS_CNAME')) $dnsTypeConstants['CNAME'] = DNS_CNAME;
if (defined('DNS_PTR')) $dnsTypeConstants['PTR'] = DNS_PTR;
if (defined('DNS_CAA')) $dnsTypeConstants['CAA'] = DNS_CAA;
if (defined('DNS_SRV')) $dnsTypeConstants['SRV'] = DNS_SRV;
if (defined('DNS_DNSKEY')) $dnsTypeConstants['DNSKEY'] = DNS_DNSKEY;
if (defined('DNS_DS')) $dnsTypeConstants['DS'] = DNS_DS;
if (defined('DNS_RRSIG')) $dnsTypeConstants['RRSIG'] = DNS_RRSIG;
if (defined('DNS_NAPTR')) $dnsTypeConstants['NAPTR'] = DNS_NAPTR;
if (defined('DNS_ALL')) $dnsTypeConstants['ANY'] = DNS_ALL;
else if (defined('DNS_ANY')) $dnsTypeConstants['ANY'] = DNS_ANY;

// Special handling for ANY type to fetch multiple record types
if ($type === 'ANY') {
    // We'll manually query multiple record types
    $useManualLookup = true;
    $dnsTypeConstant = 0; // Placeholder, not used for ANY in our implementation
} else {
    $useManualLookup = false;
    // Use the specific type constant or fall back to 0 (DNS_A) + fallback error handling
    if (isset($dnsTypeConstants[$type])) {
        $dnsTypeConstant = $dnsTypeConstants[$type];
    } else {
        // If the requested type isn't available, use DNS_A as fallback
        $dnsTypeConstant = isset($dnsTypeConstants['A']) ? $dnsTypeConstants['A'] : 0;
    }
}

// Disable error output to ensure we return only JSON
ini_set('display_errors', 0);
error_reporting(0);

// Wrap in try/catch for extra safety
try {
    $records = [];
    
    // For 'ANY', manually query the most common record types
    if ($useManualLookup && $type === 'ANY') {
        $commonTypes = ['A', 'AAAA', 'MX', 'NS', 'TXT', 'CNAME', 'SOA'];
        foreach ($commonTypes as $recordType) {
            if (isset($dnsTypeConstants[$recordType])) {
                $typeRecords = @dns_get_record($lookupDomain, $dnsTypeConstants[$recordType]);
                if ($typeRecords && is_array($typeRecords)) {
                    $records = array_merge($records, $typeRecords);
                }
            }
        }
    } else {
        // Perform standard DNS lookup - suppress warnings with @
        $records = @dns_get_record($lookupDomain, $dnsTypeConstant);
    }
    
    if ($records === false || count($records) === 0) {
        // No records found, but not an error
        $response['records'] = [];
    } else {
        $response['records'] = $records;
    }
    
    // Cache the successful result
    @file_put_contents($cacheFile, json_encode($response));
    
    // Return the response
    header('X-Cache: MISS');
    echo json_encode($response);
} catch (Exception $e) {
    // Provide a clean JSON error response
    http_response_code(500);
    echo json_encode(['error' => true, 'message' => 'DNS lookup failed: ' . $e->getMessage()]);
} catch (Error $e) {
    // Catch PHP 7+ errors as well
    http_response_code(500);
    echo json_encode(['error' => true, 'message' => 'Server error during DNS lookup: ' . $e->getMessage()]);
}