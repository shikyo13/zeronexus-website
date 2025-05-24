<?php
/**
 * MITRE CVE API (Refactored)
 * 
 * Fetches CVE information from MITRE sources and GitHub
 * Using modular utilities for better code organization
 * 
 * Parameters:
 * - id: CVE ID to look up
 * - source: Optional source preference (github, circl)
 */

// Load shared utilities
require_once __DIR__ . '/lib/autoload.php';

// Initialize utilities
$config = Config::getInstance();
$response = new Response();
$cache = new Cache('mitre-cve');
$httpClient = new HttpClient('mitre-cve');

// Handle CORS
CORS::simple(false);

// Apply rate limiting (30 requests per minute)
RateLimit::simple('mitre-cve');

// Validate input
$validator = new Validator();
$validator->required('id', 'CVE ID is required')
          ->cveId('id', 'Invalid CVE ID format')
          ->in('source', ['github', 'circl'], 'Invalid source specified');

if ($validator->fails()) {
    $response->validationError($validator->errors());
}

$cveId = strtoupper(trim($validator->get('id')));
$source = $validator->get('source', 'github');

// Parse CVE ID to get year and sequence
if (!preg_match('/^CVE-(\d{4})-(\d{4,})$/', $cveId, $matches)) {
    $response->error('Invalid CVE ID format', 400);
}

$year = $matches[1];
$sequence = $matches[2];

// Try to get from cache
$cacheKey = 'mitre_' . $cveId . '_' . $source;
$cacheTTL = 3600; // 1 hour cache

$result = $cache->remember($cacheKey, function() use ($cveId, $year, $sequence, $source, $httpClient) {
    $cveData = null;
    $error = null;
    
    try {
        if ($source === 'github') {
            // GitHub CVE API approach
            // Determine the thousand range for the CVE
            $rangeStart = floor(intval($sequence) / 1000) * 1000;
            $rangeFolder = $rangeStart . 'xxx';
            
            // Construct GitHub raw URL
            $url = sprintf(
                'https://raw.githubusercontent.com/CVEProject/cvelist/master/%s/%s/CVE-%s-%s.json',
                $year,
                $rangeFolder,
                $year,
                $sequence
            );
            
            // Fetch from GitHub
            $response = $httpClient->get($url, [], null);
            
            if ($response['success'] && !empty($response['body'])) {
                $data = json_decode($response['body'], true);
                if (json_last_error() === JSON_ERROR_NONE) {
                    $cveData = $data;
                } else {
                    throw new Exception('Invalid JSON response from GitHub');
                }
            } else {
                throw new Exception('CVE not found on GitHub');
            }
        } else {
            // CIRCL CVE API approach
            $url = "https://cve.circl.lu/api/cve/{$cveId}";
            
            $apiResponse = $httpClient->json('GET', $url, null, [], null);
            
            if ($apiResponse['success'] && isset($apiResponse['data'])) {
                $cveData = $apiResponse['data'];
            } else {
                throw new Exception('CVE not found on CIRCL');
            }
        }
        
        // Parse and format the CVE data
        if ($cveData) {
            $formatted = [
                'id' => $cveId,
                'source' => $source,
                'description' => null,
                'published' => null,
                'modified' => null,
                'references' => [],
                'cvss' => [],
                'cwe' => [],
                'affected_products' => []
            ];
            
            // Extract based on source format
            if ($source === 'github') {
                // GitHub CVEProject format
                if (isset($cveData['containers']['cna']['descriptions'])) {
                    foreach ($cveData['containers']['cna']['descriptions'] as $desc) {
                        if ($desc['lang'] === 'en' || !$formatted['description']) {
                            $formatted['description'] = $desc['value'];
                        }
                    }
                }
                
                if (isset($cveData['cveMetadata']['datePublished'])) {
                    $formatted['published'] = $cveData['cveMetadata']['datePublished'];
                }
                
                if (isset($cveData['cveMetadata']['dateUpdated'])) {
                    $formatted['modified'] = $cveData['cveMetadata']['dateUpdated'];
                }
                
                if (isset($cveData['containers']['cna']['references'])) {
                    foreach ($cveData['containers']['cna']['references'] as $ref) {
                        $formatted['references'][] = [
                            'url' => $ref['url'],
                            'name' => $ref['name'] ?? null,
                            'tags' => $ref['tags'] ?? []
                        ];
                    }
                }
                
                if (isset($cveData['containers']['cna']['metrics'])) {
                    foreach ($cveData['containers']['cna']['metrics'] as $metric) {
                        if (isset($metric['cvssV3_1'])) {
                            $formatted['cvss']['v3.1'] = [
                                'vectorString' => $metric['cvssV3_1']['vectorString'] ?? null,
                                'baseScore' => $metric['cvssV3_1']['baseScore'] ?? null,
                                'baseSeverity' => $metric['cvssV3_1']['baseSeverity'] ?? null
                            ];
                        }
                        if (isset($metric['cvssV3_0'])) {
                            $formatted['cvss']['v3.0'] = [
                                'vectorString' => $metric['cvssV3_0']['vectorString'] ?? null,
                                'baseScore' => $metric['cvssV3_0']['baseScore'] ?? null,
                                'baseSeverity' => $metric['cvssV3_0']['baseSeverity'] ?? null
                            ];
                        }
                    }
                }
                
                if (isset($cveData['containers']['cna']['affected'])) {
                    foreach ($cveData['containers']['cna']['affected'] as $affected) {
                        $product = [
                            'vendor' => $affected['vendor'] ?? 'Unknown',
                            'product' => $affected['product'] ?? 'Unknown',
                            'versions' => []
                        ];
                        
                        if (isset($affected['versions'])) {
                            foreach ($affected['versions'] as $version) {
                                $product['versions'][] = [
                                    'version' => $version['version'] ?? null,
                                    'status' => $version['status'] ?? null,
                                    'versionType' => $version['versionType'] ?? null
                                ];
                            }
                        }
                        
                        $formatted['affected_products'][] = $product;
                    }
                }
            } else {
                // CIRCL format
                $formatted['description'] = $cveData['summary'] ?? null;
                $formatted['published'] = $cveData['Published'] ?? null;
                $formatted['modified'] = $cveData['Modified'] ?? null;
                
                if (isset($cveData['references'])) {
                    foreach ($cveData['references'] as $ref) {
                        $formatted['references'][] = [
                            'url' => $ref,
                            'name' => null,
                            'tags' => []
                        ];
                    }
                }
                
                if (isset($cveData['cvss'])) {
                    $formatted['cvss']['v2'] = [
                        'vectorString' => $cveData['cvss'] ?? null,
                        'baseScore' => $cveData['cvss-score'] ?? null
                    ];
                }
                
                if (isset($cveData['vulnerable_product'])) {
                    foreach ($cveData['vulnerable_product'] as $cpe) {
                        // Parse CPE string for vendor/product info
                        $parts = explode(':', $cpe);
                        if (count($parts) >= 5) {
                            $vendor = $parts[3] ?? 'Unknown';
                            $product = $parts[4] ?? 'Unknown';
                            
                            $formatted['affected_products'][] = [
                                'vendor' => $vendor,
                                'product' => $product,
                                'cpe' => $cpe
                            ];
                        }
                    }
                }
                
                if (isset($cveData['cwe'])) {
                    $formatted['cwe'] = is_array($cveData['cwe']) ? $cveData['cwe'] : [$cveData['cwe']];
                }
            }
            
            return $formatted;
        }
        
    } catch (Exception $e) {
        $error = $e->getMessage();
    }
    
    if ($error) {
        throw new Exception($error);
    }
    
    return null;
}, $cacheTTL);

// Send response
$response->success($result);