<?php
/**
 * CISA Known Exploited Vulnerabilities API (Refactored)
 * 
 * Retrieves the CISA KEV (Known Exploited Vulnerabilities) catalog
 * Using modular utilities for better code organization
 * 
 * Parameters:
 * - id: Optional CVE ID to search for specific vulnerability
 */

// Load shared utilities
require_once __DIR__ . '/lib/autoload.php';

// Initialize utilities
$config = Config::getInstance();
$response = new Response();
$cache = new Cache('cisa-kev');
$httpClient = new HttpClient('cisa-kev');

// Handle CORS
CORS::simple(false);

// Apply rate limiting (30 requests per minute)
RateLimit::simple('cisa-kev');

// Validate input
$validator = new Validator();
if (isset($_GET['id'])) {
    $validator->cveId('id', 'Invalid CVE ID format');
    
    if ($validator->fails()) {
        $response->validationError($validator->errors());
    }
}

$cveId = isset($_GET['id']) ? strtoupper(trim($validator->get('id'))) : null;

// Cache key depends on whether we're searching for specific CVE
$cacheKey = $cveId ? 'kev_cve_' . $cveId : 'kev_full';
$cacheTTL = 3600; // 1 hour cache

$result = $cache->remember($cacheKey, function() use ($cveId, $httpClient) {
    // CISA KEV catalog URL
    $url = 'https://www.cisa.gov/sites/default/files/feeds/known_exploited_vulnerabilities.json';
    
    // Fetch the KEV catalog
    $apiResponse = $httpClient->json('GET', $url, null, [], null);
    
    if (!$apiResponse['success']) {
        throw new Exception('Failed to fetch CISA KEV catalog');
    }
    
    if (!isset($apiResponse['data']) || empty($apiResponse['data'])) {
        throw new Exception('Invalid response from CISA KEV API');
    }
    
    $data = $apiResponse['data'];
    
    // Validate response structure
    if (!isset($data['vulnerabilities']) || !is_array($data['vulnerabilities'])) {
        throw new Exception('Invalid KEV catalog format');
    }
    
    // If searching for specific CVE
    if ($cveId) {
        $found = null;
        foreach ($data['vulnerabilities'] as $vuln) {
            if (isset($vuln['cveID']) && strtoupper($vuln['cveID']) === $cveId) {
                $found = $vuln;
                break;
            }
        }
        
        if (!$found) {
            throw new Exception("CVE $cveId not found in CISA KEV catalog");
        }
        
        return [
            'found' => true,
            'vulnerability' => $found,
            'catalog_info' => [
                'title' => $data['title'] ?? 'CISA Known Exploited Vulnerabilities Catalog',
                'catalog_version' => $data['catalogVersion'] ?? null,
                'date_released' => $data['dateReleased'] ?? null,
                'count' => $data['count'] ?? count($data['vulnerabilities'])
            ]
        ];
    }
    
    // Return full catalog with some stats
    $stats = [
        'total' => count($data['vulnerabilities']),
        'by_vendor' => [],
        'by_year' => [],
        'recent' => []
    ];
    
    // Calculate statistics
    foreach ($data['vulnerabilities'] as $vuln) {
        // By vendor
        $vendor = $vuln['vendorProject'] ?? 'Unknown';
        if (!isset($stats['by_vendor'][$vendor])) {
            $stats['by_vendor'][$vendor] = 0;
        }
        $stats['by_vendor'][$vendor]++;
        
        // By year
        if (isset($vuln['cveID']) && preg_match('/CVE-(\d{4})-/', $vuln['cveID'], $matches)) {
            $year = $matches[1];
            if (!isset($stats['by_year'][$year])) {
                $stats['by_year'][$year] = 0;
            }
            $stats['by_year'][$year]++;
        }
    }
    
    // Sort vendors by count
    arsort($stats['by_vendor']);
    $stats['by_vendor'] = array_slice($stats['by_vendor'], 0, 10); // Top 10 vendors
    
    // Sort years
    krsort($stats['by_year']);
    
    // Get 10 most recent vulnerabilities (by dateAdded)
    $recent = $data['vulnerabilities'];
    usort($recent, function($a, $b) {
        $dateA = strtotime($a['dateAdded'] ?? '1970-01-01');
        $dateB = strtotime($b['dateAdded'] ?? '1970-01-01');
        return $dateB - $dateA;
    });
    $stats['recent'] = array_slice($recent, 0, 10);
    
    return [
        'catalog_info' => [
            'title' => $data['title'] ?? 'CISA Known Exploited Vulnerabilities Catalog',
            'catalog_version' => $data['catalogVersion'] ?? null,
            'date_released' => $data['dateReleased'] ?? null,
            'count' => $data['count'] ?? count($data['vulnerabilities'])
        ],
        'statistics' => $stats,
        'vulnerabilities' => $data['vulnerabilities']
    ];
}, $cacheTTL);

// Send response
$response->success($result);