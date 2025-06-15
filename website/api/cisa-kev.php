<?php
/**
 * CISA Known Exploited Vulnerabilities (KEV) API Endpoint
 * 
 * This endpoint fetches and caches vulnerability data from CISA's KEV catalog.
 * This provides valuable information about actively exploited vulnerabilities.
 */

// Set security headers
header('Content-Type: application/json');
header('X-Content-Type-Options: nosniff');
header('X-Frame-Options: DENY');
header('Referrer-Policy: strict-origin-when-cross-origin');
header('X-XSS-Protection: 1; mode=block');

// Enable CORS (same as other API endpoints)
$allowedOrigins = [
    'https://zeronexus.net',
    'https://www.zeronexus.net',
    'http://localhost:8081' // For local development
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

// Apply rate limiting (reuse from existing endpoints)
require_once __DIR__ . '/rate-limit.php';
checkRateLimit('cisa_kev');

// Get request parameters
$cveId = isset($_GET['id']) ? trim($_GET['id']) : null;
$year = isset($_GET['year']) ? intval($_GET['year']) : null;
$vendor = isset($_GET['vendor']) ? trim($_GET['vendor']) : null;
$product = isset($_GET['product']) ? trim($_GET['product']) : null;
$recent = isset($_GET['recent']) ? filter_var($_GET['recent'], FILTER_VALIDATE_BOOLEAN) : false;

// Initialize error handling
$error = null;

/**
 * Function to make HTTP requests with cURL
 */
function makeRequest($url) {
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_TIMEOUT, 15);
    curl_setopt($ch, CURLOPT_HTTPHEADER, [
        'User-Agent: ZeroNexus-CVE-Tool/1.0'
    ]);
    
    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $error = curl_error($ch);
    curl_close($ch);
    
    if ($error) {
        throw new Exception("cURL Error: " . $error);
    }
    
    if ($httpCode !== 200) {
        throw new Exception("API returned non-200 status code: " . $httpCode);
    }
    
    return $response;
}

/**
 * Fetch the CISA KEV catalog and cache it
 */
function fetchKevCatalog() {
    // First check cache
    $cacheDir = sys_get_temp_dir() . '/zeronexus_cisa_cache';
    if (!is_dir($cacheDir)) {
        mkdir($cacheDir, 0755, true);
    }
    
    $cacheFile = $cacheDir . '/kev_catalog.json';
    $cacheExpiry = 6 * 60 * 60; // 6 hours cache - KEV can update several times a day
    
    // Check if cache exists and is not expired
    if (file_exists($cacheFile) && (time() - filemtime($cacheFile)) < $cacheExpiry) {
        // Serve from cache
        return json_decode(file_get_contents($cacheFile), true);
    }
    
    // Cache miss, fetch from CISA
    try {
        // CISA's KEV catalog is available as a JSON file
        $cisaKevUrl = "https://www.cisa.gov/sites/default/files/feeds/known_exploited_vulnerabilities.json";
        $response = makeRequest($cisaKevUrl);
        
        // Parse response
        $data = json_decode($response, true);
        
        if ($data && isset($data['vulnerabilities'])) {
            // Add our source metadata
            $data['source'] = 'CISA KEV';
            $data['fetched_at'] = date('c');
            
            // Cache the result
            file_put_contents($cacheFile, json_encode($data));
            return $data;
        } else {
            throw new Exception("Invalid data format from CISA KEV catalog");
        }
    } catch (Exception $e) {
        error_log("Error fetching CISA KEV catalog: " . $e->getMessage());
        // If we have an outdated cache, better to use it than nothing
        if (file_exists($cacheFile)) {
            return json_decode(file_get_contents($cacheFile), true);
        }
        throw $e; // Re-throw if no cache fallback
    }
}

/**
 * Filter the KEV catalog by various criteria
 */
function filterKevCatalog($catalog, $params) {
    if (!isset($catalog['vulnerabilities']) || empty($catalog['vulnerabilities'])) {
        return [];
    }
    
    $vulnerabilities = $catalog['vulnerabilities'];
    
    // Filter by CVE ID if provided
    if (isset($params['cveId']) && !empty($params['cveId'])) {
        $vulnerabilities = array_filter($vulnerabilities, function($vuln) use ($params) {
            return strcasecmp($vuln['cveID'], $params['cveId']) === 0;
        });
    }
    
    // Filter by year if provided
    if (isset($params['year']) && $params['year'] > 0) {
        $yearStr = (string)$params['year'];
        $vulnerabilities = array_filter($vulnerabilities, function($vuln) use ($yearStr) {
            // Check the CVE ID for year (CVE-YYYY-NNNNN)
            return strpos($vuln['cveID'], "CVE-{$yearStr}-") === 0;
        });
    }
    
    // Filter by vendor if provided
    if (isset($params['vendor']) && !empty($params['vendor'])) {
        $vendor = strtolower($params['vendor']);
        $vulnerabilities = array_filter($vulnerabilities, function($vuln) use ($vendor) {
            return stripos(strtolower($vuln['vendorProject']), $vendor) !== false;
        });
    }
    
    // Filter by product if provided
    if (isset($params['product']) && !empty($params['product'])) {
        $product = strtolower($params['product']);
        $vulnerabilities = array_filter($vulnerabilities, function($vuln) use ($product) {
            return stripos(strtolower($vuln['product']), $product) !== false;
        });
    }
    
    // Filter for recent vulnerabilities if requested
    if (isset($params['recent']) && $params['recent']) {
        $thirtyDaysAgo = strtotime('-30 days');
        $vulnerabilities = array_filter($vulnerabilities, function($vuln) use ($thirtyDaysAgo) {
            $addedDate = strtotime($vuln['dateAdded']);
            return $addedDate >= $thirtyDaysAgo;
        });
    }
    
    // Convert to array values to reset numeric keys
    return array_values($vulnerabilities);
}

/**
 * Convert KEV data to NVD format for compatibility
 */
function convertKevToNvdFormat($kevVulnerabilities) {
    $nvdFormatVulnerabilities = [];
    
    foreach ($kevVulnerabilities as $kev) {
        // Basic structure to match NVD format
        $nvdVuln = [
            'cve' => [
                'id' => $kev['cveID'],
                'sourceIdentifier' => 'CISA',
                'published' => $kev['dateAdded'],
                'lastModified' => $kev['dateAdded'],
                'vulnStatus' => 'Known Exploited',
                'descriptions' => [
                    [
                        'lang' => 'en',
                        'value' => $kev['vulnerabilityName']
                    ]
                ],
                'metrics' => [
                    'cvssMetricV31' => [
                        [
                            'cvssData' => [
                                'version' => '3.1',
                                'vectorString' => 'CVSS:3.1/AV:N/AC:L/PR:N/UI:N/S:U/C:H/I:H/A:H',
                                'attackVector' => 'NETWORK',
                                'attackComplexity' => 'LOW',
                                'privilegesRequired' => 'NONE',
                                'userInteraction' => 'NONE',
                                'scope' => 'UNCHANGED',
                                'confidentialityImpact' => 'HIGH',
                                'integrityImpact' => 'HIGH',
                                'availabilityImpact' => 'HIGH',
                                'baseScore' => 9.8,
                                'baseSeverity' => 'CRITICAL'
                            ],
                            'exploitabilityScore' => 3.9,
                            'impactScore' => 5.9
                        ]
                    ]
                ],
                'references' => [
                    [
                        'url' => 'https://www.cisa.gov/known-exploited-vulnerabilities-catalog',
                        'source' => 'CISA',
                        'tags' => ['CISA KEV']
                    ]
                ],
                'cisaData' => [
                    'vendorProject' => $kev['vendorProject'],
                    'product' => $kev['product'],
                    'requiredAction' => $kev['requiredAction'],
                    'dueDate' => $kev['dueDate'],
                    'knownRansomwareCampaignUse' => isset($kev['knownRansomwareCampaignUse']) ? $kev['knownRansomwareCampaignUse'] : 'Unknown'
                ]
            }
        ];
        
        $nvdFormatVulnerabilities[] = $nvdVuln;
    }
    
    return [
        'resultsPerPage' => count($nvdFormatVulnerabilities),
        'startIndex' => 0,
        'totalResults' => count($nvdFormatVulnerabilities),
        'format' => 'NVD_CVE',
        'version' => '2.0',
        'timestamp' => date('c'),
        'vulnerabilities' => $nvdFormatVulnerabilities
    ];
}

try {
    // Fetch the KEV catalog
    $kevCatalog = fetchKevCatalog();
    
    // Filter the catalog based on parameters
    $filteredVulnerabilities = filterKevCatalog($kevCatalog, [
        'cveId' => $cveId,
        'year' => $year,
        'vendor' => $vendor,
        'product' => $product,
        'recent' => $recent
    ]);
    
    // Convert to NVD format for consistency
    $formattedResponse = convertKevToNvdFormat($filteredVulnerabilities);
    
    // Return the formatted response
    echo json_encode($formattedResponse);
    
} catch (Exception $e) {
    // Return error in a standardized format
    $error = [
        'error' => true,
        'message' => $e->getMessage()
    ];
    http_response_code(400);
    echo json_encode($error);
}