<?php
/**
 * CVE Progressive Sync Service
 * 
 * This service progressively syncs CVE data from NVD and CISA APIs into the local database.
 * It starts with the most recent CVEs and works backwards, respecting rate limits.
 */

require_once __DIR__ . '/cve-database.php';

class CVESyncService {
    private $db;
    private $nvdApiUrl = 'https://services.nvd.nist.gov/rest/json/cves/2.0';
    private $cisaApiUrl = 'https://www.cisa.gov/sites/default/files/feeds/known_exploited_vulnerabilities.json';
    private $rateLimitDelay = 6; // 6 seconds between requests (10 requests per minute)
    private $batchSize = 2000; // NVD max results per page
    private $maxRequestsPerRun = 50; // Limit requests per execution
    
    public function __construct() {
        $this->db = new CVEDatabase();
    }
    
    /**
     * Main sync function - can be called via command line or web
     */
    public function sync($syncType = 'recent') {
        try {
            echo "Starting CVE sync ($syncType)...\n";
            
            switch ($syncType) {
                case 'recent':
                    return $this->syncRecent();
                case 'full':
                    return $this->syncFull();
                case 'cisa':
                    return $this->syncCISA();
                case 'continue':
                    return $this->continueSync();
                default:
                    throw new Exception("Unknown sync type: $syncType");
            }
            
        } catch (Exception $e) {
            error_log("CVE sync error: " . $e->getMessage());
            echo "Error: " . $e->getMessage() . "\n";
            return false;
        }
    }
    
    /**
     * Sync recent CVEs (last 30 days)
     */
    private function syncRecent() {
        $startDate = date('c', strtotime('-30 days'));
        $endDate = date('c');
        
        echo "Syncing recent CVEs from $startDate to $endDate\n";
        
        $totalSynced = 0;
        $startIndex = 0;
        
        do {
            $url = $this->nvdApiUrl . "?pubStartDate=" . urlencode($startDate) . 
                   "&pubEndDate=" . urlencode($endDate) . 
                   "&resultsPerPage=" . $this->batchSize . 
                   "&startIndex=" . $startIndex;
            
            echo "Fetching from: $url\n";
            
            $data = $this->makeApiRequest($url);
            if (!$data) {
                echo "Failed to fetch data, stopping sync\n";
                break;
            }
            
            if (!isset($data['vulnerabilities']) || empty($data['vulnerabilities'])) {
                echo "No more vulnerabilities to process\n";
                break;
            }
            
            $batchSynced = $this->processBatch($data['vulnerabilities'], 'recent');
            $totalSynced += $batchSynced;
            
            echo "Processed batch: $batchSynced CVEs (Total: $totalSynced)\n";
            
            // Update progress
            $lastCve = end($data['vulnerabilities'])['cve']['id'] ?? '';
            $this->db->updateSyncProgress('recent', $lastCve, $totalSynced);
            
            $startIndex += $this->batchSize;
            
            // Check if we have more results
            $totalResults = $data['totalResults'] ?? 0;
            if ($startIndex >= $totalResults) {
                echo "Reached end of results\n";
                break;
            }
            
            // Rate limiting
            sleep($this->rateLimitDelay);
            
        } while ($totalSynced < $this->maxRequestsPerRun * $this->batchSize);
        
        // Mark as completed
        $this->db->updateSyncProgress('recent', '', $totalSynced, 'completed');
        
        echo "Recent sync completed. Total CVEs synced: $totalSynced\n";
        return $totalSynced;
    }
    
    /**
     * Start full historical sync (from current year backwards)
     */
    private function syncFull() {
        $currentYear = date('Y');
        echo "Starting full sync from year $currentYear\n";
        
        $totalSynced = 0;
        
        for ($year = $currentYear; $year >= 1999; $year--) {
            echo "Syncing year $year\n";
            
            $yearSynced = $this->syncYear($year);
            $totalSynced += $yearSynced;
            
            echo "Year $year completed: $yearSynced CVEs\n";
            
            // Update progress
            $this->db->updateSyncProgress('full', "year_$year", $totalSynced, 'in_progress');
            
            // Check rate limiting - if we've made too many requests, stop for now
            if ($totalSynced >= $this->maxRequestsPerRun * $this->batchSize) {
                echo "Rate limit reached, stopping for now. Resume with 'continue' sync type.\n";
                break;
            }
        }
        
        if ($year < 1999) {
            $this->db->updateSyncProgress('full', 'completed', $totalSynced, 'completed');
            echo "Full sync completed!\n";
        }
        
        return $totalSynced;
    }
    
    /**
     * Continue previous sync where it left off
     */
    private function continueSync() {
        $progress = $this->db->getSyncProgress('full');
        
        if (!$progress || $progress['status'] === 'completed') {
            echo "No incomplete sync found. Starting new full sync.\n";
            return $this->syncFull();
        }
        
        // Parse last processed year
        $lastProcessed = $progress['last_cve_processed'];
        if (preg_match('/year_(\d{4})/', $lastProcessed, $matches)) {
            $lastYear = intval($matches[1]);
            $startYear = $lastYear - 1; // Continue from next year
        } else {
            $startYear = date('Y'); // Start from current year if unclear
        }
        
        echo "Continuing sync from year $startYear\n";
        
        $totalSynced = $progress['total_processed'];
        
        for ($year = $startYear; $year >= 1999; $year--) {
            echo "Syncing year $year\n";
            
            $yearSynced = $this->syncYear($year);
            $totalSynced += $yearSynced;
            
            echo "Year $year completed: $yearSynced CVEs\n";
            
            // Update progress
            $this->db->updateSyncProgress('full', "year_$year", $totalSynced, 'in_progress');
            
            // Check rate limiting
            if ($yearSynced >= $this->maxRequestsPerRun * $this->batchSize) {
                echo "Rate limit reached, stopping for now.\n";
                break;
            }
        }
        
        if ($year < 1999) {
            $this->db->updateSyncProgress('full', 'completed', $totalSynced, 'completed');
            echo "Full sync completed!\n";
        }
        
        return $totalSynced;
    }
    
    /**
     * Sync CVEs for a specific year
     */
    private function syncYear($year) {
        $startDate = "$year-01-01T00:00:00.000Z";
        $endDate = "$year-12-31T23:59:59.999Z";
        
        $totalSynced = 0;
        $startIndex = 0;
        $requestCount = 0;
        
        do {
            $url = $this->nvdApiUrl . "?pubStartDate=" . urlencode($startDate) . 
                   "&pubEndDate=" . urlencode($endDate) . 
                   "&resultsPerPage=" . $this->batchSize . 
                   "&startIndex=" . $startIndex;
            
            $data = $this->makeApiRequest($url);
            if (!$data) {
                echo "Failed to fetch data for year $year\n";
                break;
            }
            
            if (!isset($data['vulnerabilities']) || empty($data['vulnerabilities'])) {
                echo "No more vulnerabilities for year $year\n";
                break;
            }
            
            $batchSynced = $this->processBatch($data['vulnerabilities'], 'full');
            $totalSynced += $batchSynced;
            $requestCount++;
            
            echo "  Batch $requestCount: $batchSynced CVEs (Year total: $totalSynced)\n";
            
            $startIndex += $this->batchSize;
            
            // Check if we have more results
            $totalResults = $data['totalResults'] ?? 0;
            if ($startIndex >= $totalResults) {
                break;
            }
            
            // Rate limiting
            sleep($this->rateLimitDelay);
            
            // Prevent too many requests in one run
            if ($requestCount >= $this->maxRequestsPerRun) {
                echo "  Request limit reached for year $year\n";
                break;
            }
            
        } while (true);
        
        return $totalSynced;
    }
    
    /**
     * Sync CISA Known Exploited Vulnerabilities
     */
    private function syncCISA() {
        echo "Syncing CISA Known Exploited Vulnerabilities\n";
        
        $data = $this->makeApiRequest($this->cisaApiUrl);
        if (!$data || !isset($data['vulnerabilities'])) {
            echo "Failed to fetch CISA data\n";
            return 0;
        }
        
        $totalSynced = 0;
        
        foreach ($data['vulnerabilities'] as $cisaVuln) {
            $cveId = $cisaVuln['cveID'] ?? '';
            if (empty($cveId)) continue;
            
            // First try to get the CVE from NVD
            $nvdUrl = $this->nvdApiUrl . "?cveId=" . urlencode($cveId);
            $nvdData = $this->makeApiRequest($nvdUrl);
            
            if ($nvdData && isset($nvdData['vulnerabilities'][0])) {
                // Merge CISA data with NVD data
                $cveData = $nvdData['vulnerabilities'][0]['cve'];
                $cveData['cisaData'] = $cisaVuln;
                $cveData['source'] = 'CISA';
                
                if ($this->db->upsertCVE($cveData)) {
                    $totalSynced++;
                }
            } else {
                // Create minimal CVE record with just CISA data
                $cveData = [
                    'id' => $cveId,
                    'published' => $cisaVuln['dateAdded'] ?? date('c'),
                    'lastModified' => date('c'),
                    'descriptions' => [
                        [
                            'lang' => 'en',
                            'value' => $cisaVuln['vulnerabilityName'] ?? 'CISA Known Exploited Vulnerability'
                        ]
                    ],
                    'cisaData' => $cisaVuln,
                    'source' => 'CISA'
                ];
                
                if ($this->db->upsertCVE($cveData)) {
                    $totalSynced++;
                }
            }
            
            // Rate limiting for CISA sync too
            if ($totalSynced % 10 === 0) {
                sleep(1);
            }
        }
        
        $this->db->updateSyncProgress('cisa', '', $totalSynced, 'completed');
        echo "CISA sync completed. Total CVEs synced: $totalSynced\n";
        
        return $totalSynced;
    }
    
    /**
     * Process a batch of CVE vulnerabilities
     */
    private function processBatch($vulnerabilities, $syncType) {
        $synced = 0;
        
        foreach ($vulnerabilities as $vuln) {
            if (!isset($vuln['cve'])) continue;
            
            if ($this->db->upsertCVE($vuln['cve'])) {
                $synced++;
            }
        }
        
        return $synced;
    }
    
    /**
     * Make API request with error handling and retries
     */
    private function makeApiRequest($url, $retries = 3) {
        for ($i = 0; $i < $retries; $i++) {
            $ch = curl_init();
            curl_setopt($ch, CURLOPT_URL, $url);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_TIMEOUT, 30);
            curl_setopt($ch, CURLOPT_HTTPHEADER, [
                'User-Agent: ZeroNexus-CVE-Sync/1.0',
                'Accept: application/json'
            ]);
            
            $response = curl_exec($ch);
            $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
            $error = curl_error($ch);
            curl_close($ch);
            
            if ($error) {
                echo "cURL Error (attempt " . ($i + 1) . "): $error\n";
                if ($i < $retries - 1) {
                    sleep(2);
                    continue;
                }
                return false;
            }
            
            if ($httpCode === 200) {
                $data = json_decode($response, true);
                if ($data === null) {
                    echo "JSON decode error (attempt " . ($i + 1) . ")\n";
                    if ($i < $retries - 1) {
                        sleep(2);
                        continue;
                    }
                    return false;
                }
                return $data;
            } elseif ($httpCode === 429) {
                echo "Rate limited (attempt " . ($i + 1) . "), waiting longer...\n";
                sleep(60); // Wait 1 minute for rate limit
            } else {
                echo "HTTP Error $httpCode (attempt " . ($i + 1) . ")\n";
                if ($i < $retries - 1) {
                    sleep(5);
                    continue;
                }
            }
        }
        
        return false;
    }
    
    /**
     * Get sync status information
     */
    public function getStatus() {
        $recentProgress = $this->db->getSyncProgress('recent');
        $fullProgress = $this->db->getSyncProgress('full');
        $cisaProgress = $this->db->getSyncProgress('cisa');
        $dbStats = $this->db->getDatabaseStats();
        
        return [
            'database_stats' => $dbStats,
            'sync_progress' => [
                'recent' => $recentProgress,
                'full' => $fullProgress,
                'cisa' => $cisaProgress
            ]
        ];
    }
}

// CLI usage
if (php_sapi_name() === 'cli') {
    $syncType = $argv[1] ?? 'recent';
    $service = new CVESyncService();
    $result = $service->sync($syncType);
    echo "Sync result: $result\n";
} 
// Web usage (for debugging/manual triggers)
elseif (isset($_GET['action'])) {
    header('Content-Type: application/json');
    
    $service = new CVESyncService();
    
    switch ($_GET['action']) {
        case 'sync':
            $syncType = $_GET['type'] ?? 'recent';
            $result = $service->sync($syncType);
            echo json_encode(['success' => true, 'synced' => $result]);
            break;
            
        case 'status':
            $status = $service->getStatus();
            echo json_encode($status);
            break;
            
        default:
            echo json_encode(['error' => 'Unknown action']);
    }
}