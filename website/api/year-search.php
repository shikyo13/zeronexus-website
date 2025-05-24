<?php
/**
 * CVE Year-based Search Utility (Refactored)
 * 
 * Provides a function to search CVEs by year using GitHub CVE data
 * This is a utility function used by cve-search.php
 */

// Only load utilities if not already loaded (when called from cve-search.php)
if (!class_exists('HttpClient')) {
    require_once __DIR__ . '/lib/autoload.php';
}

/**
 * Search CVEs by year using GitHub CVE API approach
 * 
 * @param int $year The year to search for
 * @return array Array of CVE data
 */
function searchCvesByYear($year) {
    $config = Config::getInstance();
    $cache = new Cache('year-search');
    $httpClient = new HttpClient('year-search');
    
    // Validate year
    $currentYear = intval(date('Y'));
    if ($year < 1999 || $year > $currentYear) {
        return [];
    }
    
    // Cache key for year data
    $cacheKey = 'year_' . $year;
    $cacheTTL = 21600; // 6 hours cache for year data
    
    return $cache->remember($cacheKey, function() use ($year, $httpClient) {
        $cves = [];
        
        // GitHub approach - fetch the year index
        $indexUrl = "https://raw.githubusercontent.com/CVEProject/cvelist/master/{$year}/index.json";
        
        $response = $httpClient->get($indexUrl, [], null);
        
        if (!$response['success'] || empty($response['body'])) {
            // Fallback to NVD API
            return fetchFromNVD($year, $httpClient);
        }
        
        $indexData = json_decode($response['body'], true);
        if (json_last_error() !== JSON_ERROR_NONE || !is_array($indexData)) {
            return fetchFromNVD($year, $httpClient);
        }
        
        // Process index data
        foreach ($indexData as $cveId => $metadata) {
            if (!preg_match('/^CVE-\d{4}-\d{4,}$/', $cveId)) {
                continue;
            }
            
            $cves[] = [
                'id' => $cveId,
                'state' => $metadata['state'] ?? 'PUBLISHED',
                'assigner' => $metadata['assignerOrgId'] ?? null,
                'assignerShortName' => $metadata['assignerShortName'] ?? null,
                'datePublished' => $metadata['datePublished'] ?? null,
                'dateUpdated' => $metadata['dateUpdated'] ?? null,
                'dateReserved' => $metadata['dateReserved'] ?? null
            ];
        }
        
        // Sort by CVE ID (newest first)
        usort($cves, function($a, $b) {
            return strcmp($b['id'], $a['id']);
        });
        
        // For each CVE, try to get additional details
        foreach ($cves as &$cve) {
            $cve['description'] = 'CVE details available';
            $cve['severity'] = null;
            $cve['score'] = null;
            
            // Extract year and sequence from CVE ID
            if (preg_match('/^CVE-(\d{4})-(\d{4,})$/', $cve['id'], $matches)) {
                $sequence = $matches[2];
                $rangeStart = floor(intval($sequence) / 1000) * 1000;
                $rangeFolder = $rangeStart . 'xxx';
                
                // Try to fetch individual CVE data
                $cveUrl = sprintf(
                    'https://raw.githubusercontent.com/CVEProject/cvelist/master/%s/%s/CVE-%s-%s.json',
                    $year,
                    $rangeFolder,
                    $year,
                    $sequence
                );
                
                $cveResponse = $httpClient->get($cveUrl, [], null);
                
                if ($cveResponse['success'] && !empty($cveResponse['body'])) {
                    $cveData = json_decode($cveResponse['body'], true);
                    
                    if (json_last_error() === JSON_ERROR_NONE) {
                        // Extract description
                        if (isset($cveData['containers']['cna']['descriptions'])) {
                            foreach ($cveData['containers']['cna']['descriptions'] as $desc) {
                                if ($desc['lang'] === 'en') {
                                    $cve['description'] = substr($desc['value'], 0, 200) . '...';
                                    break;
                                }
                            }
                        }
                        
                        // Extract CVSS score if available
                        if (isset($cveData['containers']['cna']['metrics'])) {
                            foreach ($cveData['containers']['cna']['metrics'] as $metric) {
                                if (isset($metric['cvssV3_1'])) {
                                    $cve['score'] = $metric['cvssV3_1']['baseScore'] ?? null;
                                    $cve['severity'] = $metric['cvssV3_1']['baseSeverity'] ?? null;
                                    break;
                                } elseif (isset($metric['cvssV3_0'])) {
                                    $cve['score'] = $metric['cvssV3_0']['baseScore'] ?? null;
                                    $cve['severity'] = $metric['cvssV3_0']['baseSeverity'] ?? null;
                                    break;
                                }
                            }
                        }
                    }
                }
                
                // Don't fetch too many details to avoid rate limiting
                if (count($cves) > 100) {
                    break;
                }
            }
        }
        
        return $cves;
    }, $cacheTTL);
}

/**
 * Fallback function to fetch from NVD API
 */
function fetchFromNVD($year, $httpClient) {
    $url = "https://services.nvd.nist.gov/rest/json/cves/v2";
    $params = [
        'keywordSearch' => "CVE-{$year}",
        'resultsPerPage' => 200
    ];
    
    $url .= '?' . http_build_query($params);
    
    $response = $httpClient->json('GET', $url, null, [], null);
    
    if (!$response['success'] || !isset($response['data']['vulnerabilities'])) {
        return [];
    }
    
    $cves = [];
    foreach ($response['data']['vulnerabilities'] as $vuln) {
        $cve = $vuln['cve'];
        
        $formatted = [
            'id' => $cve['id'],
            'state' => $cve['vulnStatus'] ?? 'PUBLISHED',
            'assigner' => $cve['sourceIdentifier'] ?? null,
            'datePublished' => $cve['published'] ?? null,
            'dateUpdated' => $cve['lastModified'] ?? null,
            'description' => '',
            'severity' => null,
            'score' => null
        ];
        
        // Extract description
        if (isset($cve['descriptions'])) {
            foreach ($cve['descriptions'] as $desc) {
                if ($desc['lang'] === 'en') {
                    $formatted['description'] = substr($desc['value'], 0, 200) . '...';
                    break;
                }
            }
        }
        
        // Extract CVSS metrics
        if (isset($cve['metrics'])) {
            if (isset($cve['metrics']['cvssMetricV31'][0])) {
                $metric = $cve['metrics']['cvssMetricV31'][0];
                $formatted['score'] = $metric['cvssData']['baseScore'] ?? null;
                $formatted['severity'] = $metric['cvssData']['baseSeverity'] ?? null;
            } elseif (isset($cve['metrics']['cvssMetricV30'][0])) {
                $metric = $cve['metrics']['cvssMetricV30'][0];
                $formatted['score'] = $metric['cvssData']['baseScore'] ?? null;
                $formatted['severity'] = $metric['cvssData']['baseSeverity'] ?? null;
            }
        }
        
        $cves[] = $formatted;
    }
    
    return $cves;
}