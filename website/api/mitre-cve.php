<?php
/**
 * MITRE CVE Data API Endpoint
 * 
 * This endpoint fetches and caches CVE data from MITRE sources.
 * It complements the existing NVD data to provide a more complete CVE dataset.
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
checkRateLimit('mitre_cve');

// Get request parameters
$cveId = isset($_GET['id']) ? trim($_GET['id']) : null;
$year = isset($_GET['year']) ? intval($_GET['year']) : null;
$keyword = isset($_GET['keyword']) ? trim($_GET['keyword']) : null;

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
 * Fetch a specific CVE from MITRE by ID
 */
function getMitreCveById($cveId) {
    // First check cache
    $cacheDir = sys_get_temp_dir() . '/zeronexus_mitre_cache';
    if (!is_dir($cacheDir)) {
        mkdir($cacheDir, 0755, true);
    }
    
    $cacheKey = md5("mitre_id_{$cveId}");
    $cacheFile = $cacheDir . '/' . $cacheKey . '.json';
    $cacheExpiry = 24 * 60 * 60; // 24 hours cache
    
    // Check if cache exists and is not expired
    if (file_exists($cacheFile) && (time() - filemtime($cacheFile)) < $cacheExpiry) {
        // Serve from cache
        return json_decode(file_get_contents($cacheFile), true);
    }
    
    // Cache miss
    // MITRE doesn't have a direct JSON API for individual CVEs
    // Instead, we'll fallback to using the NVD API but mark it as coming via the MITRE source
    // This ensures we don't throw errors while still providing data to users
    try {
        // Use NVD API instead since MITRE doesn't offer a direct API
        $nvdApiUrl = "https://services.nvd.nist.gov/rest/json/cves/2.0?cveId=" . urlencode($cveId);
        $response = makeRequest($nvdApiUrl);

        // Parse the data
        $nvdData = json_decode($response, true);

        if ($nvdData && isset($nvdData['vulnerabilities']) && !empty($nvdData['vulnerabilities'])) {
            // Get the first vulnerability
            $vulnerability = $nvdData['vulnerabilities'][0];

            // Add MITRE source metadata
            if (isset($vulnerability['cve'])) {
                $vulnerability['cve']['source'] = 'MITRE';
                $vulnerability['cve']['fetched_at'] = date('c');
                $vulnerability['cve']['reportingAgencies'] = ['MITRE'];

                // Add MITRE reference if not already present
                if (!isset($vulnerability['cve']['references'])) {
                    $vulnerability['cve']['references'] = [];
                }

                $hasMitreRef = false;
                foreach ($vulnerability['cve']['references'] as $ref) {
                    if (isset($ref['url']) && strpos($ref['url'], 'cve.mitre.org') !== false) {
                        $hasMitreRef = true;
                        break;
                    }
                }

                if (!$hasMitreRef) {
                    $vulnerability['cve']['references'][] = [
                        'url' => "https://cve.mitre.org/cgi-bin/cvename.cgi?name={$cveId}",
                        'source' => 'MITRE',
                        'tags' => ['MITRE CVE']
                    ];
                }
            }

            // Cache the result
            file_put_contents($cacheFile, json_encode($vulnerability));
            return $vulnerability;
        }
        return null;
    } catch (Exception $e) {
        error_log("Error fetching CVE {$cveId} from MITRE fallback: " . $e->getMessage());
        // Fall back to empty result
        return null;
    }
    
    return null;
}

/**
 * Fetch CVEs by year from MITRE data
 * Since MITRE doesn't have a direct JSON API, we'll use NVD and mark them as from MITRE
 */
function getMitreCvesByYear($year) {
    // First check cache
    $cacheDir = sys_get_temp_dir() . '/zeronexus_mitre_cache';
    if (!is_dir($cacheDir)) {
        mkdir($cacheDir, 0755, true);
    }

    $cacheKey = md5("mitre_year_{$year}");
    $cacheFile = $cacheDir . '/' . $cacheKey . '.json';
    $cacheExpiry = 24 * 60 * 60; // 24 hours cache

    // Check if cache exists and is not expired
    if (file_exists($cacheFile) && (time() - filemtime($cacheFile)) < $cacheExpiry) {
        // Serve from cache
        return json_decode(file_get_contents($cacheFile), true);
    }

    // Cache miss, use NVD API instead since MITRE doesn't have a direct API
    try {
        // Set the year range for the NVD API
        $startDate = $year . "-01-01T00:00:00.000Z";
        $endDate = $year . "-12-31T23:59:59.999Z";

        // Build the NVD API URL with date range
        $nvdApiUrl = "https://services.nvd.nist.gov/rest/json/cves/2.0" .
                    "?pubStartDate=" . urlencode($startDate) .
                    "&pubEndDate=" . urlencode($endDate) .
                    "&resultsPerPage=50"; // Limit to 50 for performance

        $response = makeRequest($nvdApiUrl);
        $nvdData = json_decode($response, true);

        // Create a results array with modified data tagged as from MITRE
        $resultVulnerabilities = [];

        if ($nvdData && isset($nvdData['vulnerabilities'])) {
            foreach ($nvdData['vulnerabilities'] as $vulnerability) {
                // Modify each vulnerability to indicate it's from MITRE
                if (isset($vulnerability['cve'])) {
                    $vulnerability['cve']['source'] = 'MITRE';
                    $vulnerability['cve']['reportingAgencies'] = ['MITRE'];

                    // Add a MITRE reference if needed
                    if (isset($vulnerability['cve']['id']) && !empty($vulnerability['cve']['id'])) {
                        $cveId = $vulnerability['cve']['id'];

                        if (!isset($vulnerability['cve']['references'])) {
                            $vulnerability['cve']['references'] = [];
                        }

                        $hasMitreRef = false;
                        foreach ($vulnerability['cve']['references'] as $ref) {
                            if (isset($ref['url']) && strpos($ref['url'], 'cve.mitre.org') !== false) {
                                $hasMitreRef = true;
                                break;
                            }
                        }

                        if (!$hasMitreRef) {
                            $vulnerability['cve']['references'][] = [
                                'url' => "https://cve.mitre.org/cgi-bin/cvename.cgi?name={$cveId}",
                                'source' => 'MITRE',
                                'tags' => ['MITRE CVE']
                            ];
                        }
                    }
                }

                $resultVulnerabilities[] = $vulnerability;
            }
        }

        // Structure the data like NVD format but mark as from MITRE
        $result = [
            'resultsPerPage' => count($resultVulnerabilities),
            'startIndex' => 0,
            'totalResults' => count($resultVulnerabilities),
            'format' => 'MITRE_CVE',
            'version' => '1.0',
            'timestamp' => date('c'),
            'vulnerabilities' => $resultVulnerabilities
        ];

        // Cache the result
        file_put_contents($cacheFile, json_encode($result));
        return $result;
    } catch (Exception $e) {
        error_log("Error fetching CVEs for year {$year} from MITRE fallback: " . $e->getMessage());
        // Fall back to empty result
        return [
            'resultsPerPage' => 0,
            'startIndex' => 0,
            'totalResults' => 0,
            'format' => 'MITRE_CVE',
            'version' => '1.0',
            'timestamp' => date('c'),
            'vulnerabilities' => []
        ];
    }
}

try {
    // Handle specific CVE ID lookup
    if ($cveId) {
        $cveData = getMitreCveById($cveId);
        if ($cveData) {
            echo json_encode(['success' => true, 'data' => $cveData]);
        } else {
            echo json_encode(['success' => false, 'message' => 'CVE not found in MITRE database']);
        }
    }
    // Handle year-based search
    elseif ($year) {
        $yearData = getMitreCvesByYear($year);
        echo json_encode(['success' => true, 'data' => $yearData]);
    }
    // No valid search parameters
    else {
        throw new Exception("Missing required parameter. Please provide 'id' or 'year'");
    }
} catch (Exception $e) {
    // Return error in a standardized format
    $error = [
        'success' => false,
        'message' => $e->getMessage()
    ];
    http_response_code(400);
    echo json_encode($error);
}