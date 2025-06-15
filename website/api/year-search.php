<?php
/**
 * Simplified CVE Year-based Search
 * This file contains a function to handle year-based CVE searches.
 */

/**
 * Handle year-based CVE search
 * @param int $year The year to search for
 * @param string|null $severity Optional severity filter
 * @param int $resultsPerPage Number of results per page
 * @param string|null $keyword Optional keyword to search for within the year
 * @param string|null $vendor Optional vendor to search for within the year
 * @return string JSON response
 */
function searchCVEsByYear($year, $severity = null, $resultsPerPage = 50, $keyword = null, $vendor = null) {
    // Validate year format
    $currentYear = intval(date('Y'));
    if ($year < 1988 || $year > ($currentYear + 1)) {
        throw new Exception("Invalid year. Please enter a year between 1988 and " . ($currentYear + 1) . ".");
    }

    // Log what we're doing
    error_log("Year search: $year, severity: $severity, keyword: $keyword, vendor: $vendor");

    // Check cache
    $cacheDir = sys_get_temp_dir() . '/zeronexus_cve_search_cache';
    if (!is_dir($cacheDir)) {
        mkdir($cacheDir, 0755, true);
    }

    // Create cache key
    $cacheKey = md5("year_{$year}_keyword_{$keyword}_vendor_{$vendor}_severity_{$severity}");
    $cacheFile = $cacheDir . '/' . $cacheKey . '.json';
    $cacheExpiry = 6 * 60 * 60; // 6 hours

    // Cache hit
    if (file_exists($cacheFile) && (time() - filemtime($cacheFile)) < $cacheExpiry) {
        header('X-Cache: HIT');
        return file_get_contents($cacheFile);
    }

    // Start with base URL
    $apiUrl = "https://services.nvd.nist.gov/rest/json/cves/2.0?";
    
    // Always use the year in the search pattern for reliability
    $searchTerm = "CVE-{$year}";
    
    // Build the keyword search parameter
    if ($keyword) {
        // Add keyword to search term for better filtering
        $searchTerm .= " " . $keyword;
    } elseif ($vendor) {
        // Add vendor to search term for better filtering
        $searchTerm .= " " . $vendor;
    }
    
    // Build the URL
    $params = [];
    $params[] = "keywordSearch=" . urlencode($searchTerm);
    
    // Add severity filter if specified
    if ($severity) {
        $validSeverities = ['CRITICAL', 'HIGH', 'MEDIUM', 'LOW'];
        if (in_array(strtoupper($severity), $validSeverities)) {
            $params[] = "cvssV3Severity=" . urlencode(strtoupper($severity));
        }
    }
    
    // Add result limit
    $params[] = "resultsPerPage={$resultsPerPage}";
    
    // Combine everything
    $apiUrl .= implode("&", $params);
    
    error_log("Year search URL: $apiUrl");
    
    // Make the request
    try {
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $apiUrl);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_TIMEOUT, 15);
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            'User-Agent: ZeroNexus-CVE-Tool/1.0'
        ]);
        
        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $error = curl_error($ch);
        curl_close($ch);
        
        // Check for errors
        if ($error) {
            throw new Exception("cURL Error: " . $error);
        }
        
        if ($httpCode !== 200) {
            throw new Exception("API returned non-200 status code: " . $httpCode);
        }
        
        // Validate response structure
        $responseData = json_decode($response, true);
        if (!$responseData || !isset($responseData['vulnerabilities'])) {
            throw new Exception("Invalid response format from API");
        }
        
        // Cache and return the response
        file_put_contents($cacheFile, $response);
        header('X-Cache: MISS');
        return $response;
        
    } catch (Exception $e) {
        error_log("Error in year search: " . $e->getMessage());
        
        // Return empty results structure
        return json_encode([
            'resultsPerPage' => 0,
            'startIndex' => 0,
            'totalResults' => 0,
            'format' => 'NVD_CVE',
            'version' => '2.0',
            'timestamp' => date('c'),
            'vulnerabilities' => []
        ]);
    }
}