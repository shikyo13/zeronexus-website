<?php
/**
 * Global Network Tools API
 * Executes network diagnostics from multiple global locations using Globalping API
 */

// Set headers for JSON API
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST');
header('Access-Control-Allow-Headers: Content-Type');

// Error reporting configuration
error_reporting(E_ALL);
ini_set('display_errors', 0);
ini_set('display_startup_errors', 0);

// Register shutdown function to catch fatal errors
register_shutdown_function(function() {
    $error = error_get_last();
    if ($error !== null && in_array($error['type'], [E_ERROR, E_PARSE, E_CORE_ERROR, E_COMPILE_ERROR])) {
        http_response_code(500);
        echo json_encode([
            'error' => 'Fatal server error: ' . $error['message'],
            'file' => basename($error['file']),
            'line' => $error['line']
        ]);
        exit;
    }
});

// Custom error handler
set_error_handler(function($errno, $errstr, $errfile, $errline) {
    if (!(error_reporting() & $errno)) {
        return false;
    }
    
    http_response_code(500);
    echo json_encode([
        'error' => 'Server error: ' . $errstr,
        'file' => basename($errfile),
        'line' => $errline
    ]);
    exit;
});

try {
    // Import dependencies
    require_once 'rate-limit.php';
    require_once 'request-dedup.php';
    require_once 'audit-log.php';
    
    // Apply rate limiting with tiered limits
    $rateLimitKey = 'global_network_tools';
    $rateLimitPerHour = 20; // Default for anonymous users
    
    // Check if user has authentication token (future enhancement)
    $authToken = isset($_SERVER['HTTP_AUTHORIZATION']) ? $_SERVER['HTTP_AUTHORIZATION'] : null;
    if ($authToken && strpos($authToken, 'Bearer ') === 0) {
        $token = substr($authToken, 7);
        // In future, validate token and increase limits for authenticated users
        // For now, just note that authentication is supported
        $rateLimitPerHour = 50; // Higher limit for authenticated users
        $rateLimitKey .= '_auth_' . substr(md5($token), 0, 8);
    }
    
    try {
        checkRateLimit($rateLimitKey, $rateLimitPerHour);
    } catch (Exception $e) {
        http_response_code(429);
        $retryAfter = 3600; // 1 hour
        header('Retry-After: ' . $retryAfter);
        echo json_encode([
            'error' => 'Rate limit exceeded. Please try again later.',
            'retry_after' => $retryAfter
        ]);
        exit;
    }
    
    /**
     * Globalping API Client
     */
    class GlobalpingClient {
        private $apiUrl = 'https://api.globalping.io/v1/measurements';
        private $apiToken = null; // Optional - can add token for higher limits
        
        /**
         * Create a new measurement
         */
        public function createMeasurement($type, $target, $locations = [], $options = []) {
            // Validate input
            if (!in_array($type, ['ping', 'traceroute', 'mtr'])) {
                throw new Exception('Invalid measurement type');
            }
            
            // Build request body
            $requestBody = [
                'type' => $type,
                'target' => $target,
                'locations' => !empty($locations) ? $locations : [
                    ['country' => 'US'],
                    ['country' => 'GB'],
                    ['country' => 'DE'],
                    ['country' => 'JP'],
                    ['country' => 'AU']
                ],
                'limit' => 5 // Max locations per request
            ];
            
            // Add measurement options
            if ($type === 'ping' && isset($options['packets'])) {
                $requestBody['measurementOptions'] = [
                    'packets' => min(16, max(1, intval($options['packets'])))
                ];
            }
            
            // Make API request
            $ch = curl_init($this->apiUrl);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_POST, true);
            curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($requestBody));
            curl_setopt($ch, CURLOPT_HTTPHEADER, [
                'Content-Type: application/json',
                'Accept: application/json'
            ]);
            
            if ($this->apiToken) {
                curl_setopt($ch, CURLOPT_HTTPHEADER, array_merge(
                    curl_getopt($ch, CURLOPT_HTTPHEADER),
                    ['Authorization: Bearer ' . $this->apiToken]
                ));
            }
            
            curl_setopt($ch, CURLOPT_TIMEOUT, 30);
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, true);
            
            $response = curl_exec($ch);
            $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
            $error = curl_error($ch);
            curl_close($ch);
            
            if ($error) {
                throw new Exception('API request failed: ' . $error);
            }
            
            $data = json_decode($response, true);
            
            if ($httpCode !== 202) {
                $errorMsg = isset($data['error']['message']) ? $data['error']['message'] : 'Unknown error';
                throw new Exception('API error: ' . $errorMsg);
            }
            
            return $data;
        }
        
        /**
         * Get measurement results
         */
        public function getMeasurement($id) {
            $ch = curl_init($this->apiUrl . '/' . $id);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_HTTPHEADER, [
                'Accept: application/json'
            ]);
            
            if ($this->apiToken) {
                curl_setopt($ch, CURLOPT_HTTPHEADER, array_merge(
                    curl_getopt($ch, CURLOPT_HTTPHEADER),
                    ['Authorization: Bearer ' . $this->apiToken]
                ));
            }
            
            curl_setopt($ch, CURLOPT_TIMEOUT, 30);
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, true);
            
            $response = curl_exec($ch);
            $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
            $error = curl_error($ch);
            curl_close($ch);
            
            if ($error) {
                throw new Exception('API request failed: ' . $error);
            }
            
            $data = json_decode($response, true);
            
            if ($httpCode !== 200) {
                $errorMsg = isset($data['error']['message']) ? $data['error']['message'] : 'Unknown error';
                throw new Exception('API error: ' . $errorMsg);
            }
            
            return $data;
        }
    }
    
    /**
     * Cache implementation with automatic cleanup
     */
    class ResultCache {
        private $cacheDir;
        private $cacheLifetime = 300; // 5 minutes
        private $maxCacheFiles = 100; // Maximum cache files before cleanup
        
        public function __construct() {
            $this->cacheDir = sys_get_temp_dir() . '/global_network_cache';
            if (!is_dir($this->cacheDir)) {
                mkdir($this->cacheDir, 0777, true);
            }
            
            // Periodic cleanup
            if (rand(1, 10) === 1) {
                $this->cleanup();
            }
        }
        
        public function get($key) {
            $filename = $this->cacheDir . '/' . md5($key) . '.cache';
            
            if (!file_exists($filename)) {
                return null;
            }
            
            $data = unserialize(file_get_contents($filename));
            
            if ($data['expires'] < time()) {
                unlink($filename);
                return null;
            }
            
            return $data['value'];
        }
        
        public function set($key, $value) {
            $filename = $this->cacheDir . '/' . md5($key) . '.cache';
            
            $data = [
                'expires' => time() + $this->cacheLifetime,
                'value' => $value
            ];
            
            file_put_contents($filename, serialize($data));
        }
        
        /**
         * Clean up expired cache files
         */
        private function cleanup() {
            $files = glob($this->cacheDir . '/*.cache');
            if (!$files) return;
            
            // Sort by modification time
            usort($files, function($a, $b) {
                return filemtime($a) - filemtime($b);
            });
            
            // Remove expired files
            $removed = 0;
            foreach ($files as $file) {
                if (file_exists($file)) {
                    $data = @unserialize(file_get_contents($file));
                    if (!$data || $data['expires'] < time()) {
                        @unlink($file);
                        $removed++;
                    }
                }
            }
            
            // If still too many files, remove oldest ones
            $remaining = count($files) - $removed;
            if ($remaining > $this->maxCacheFiles) {
                $toRemove = $remaining - $this->maxCacheFiles;
                for ($i = 0; $i < $toRemove && $i < count($files); $i++) {
                    if (file_exists($files[$i])) {
                        @unlink($files[$i]);
                    }
                }
            }
        }
    }
    
    // Validate request method
    if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
        http_response_code(200);
        exit;
    }
    
    // Get request data
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $input = json_decode(file_get_contents('php://input'), true);
    } else {
        $input = $_GET;
    }
    
    // Validate parameters
    if (!isset($input['host']) || empty($input['host'])) {
        http_response_code(400);
        echo json_encode(['error' => 'Missing host parameter']);
        exit;
    }
    
    $host = trim($input['host']);
    $tool = isset($input['tool']) ? $input['tool'] : 'ping';
    $locations = isset($input['locations']) ? $input['locations'] : [];
    
    // Validate tool
    if (!in_array($tool, ['ping', 'traceroute', 'mtr'])) {
        http_response_code(400);
        echo json_encode(['error' => 'Invalid tool. Allowed: ping, traceroute, mtr']);
        exit;
    }
    
    // Enhanced input validation and SSRF protection
    
    // Validate host format
    if (!preg_match('/^[a-zA-Z0-9\.\-:]+$/', $host)) {
        http_response_code(400);
        echo json_encode(['error' => 'Invalid host format']);
        exit;
    }
    
    // Check host length
    if (strlen($host) > 255) {
        http_response_code(400);
        echo json_encode(['error' => 'Host name too long']);
        exit;
    }
    
    // SSRF Protection - Block private and reserved IP ranges
    $privateIPs = [
        '0.0.0.0/8',        // Current network
        '10.0.0.0/8',       // Private network
        '100.64.0.0/10',    // Shared address space
        '127.0.0.0/8',      // Loopback
        '169.254.0.0/16',   // Link local
        '172.16.0.0/12',    // Private network
        '192.0.0.0/24',     // IETF protocol assignments
        '192.0.2.0/24',     // Documentation
        '192.168.0.0/16',   // Private network
        '198.18.0.0/15',    // Network benchmark tests
        '198.51.100.0/24',  // Documentation
        '203.0.113.0/24',   // Documentation
        '224.0.0.0/4',      // Multicast
        '240.0.0.0/4',      // Reserved
        '255.255.255.255/32' // Broadcast
    ];
    
    // Check if host is an IP
    if (filter_var($host, FILTER_VALIDATE_IP, FILTER_FLAG_IPV4)) {
        $hostLong = ip2long($host);
        
        foreach ($privateIPs as $range) {
            list($subnet, $bits) = explode('/', $range);
            $subnet = ip2long($subnet);
            $mask = -1 << (32 - $bits);
            $subnet &= $mask;
            
            if (($hostLong & $mask) == $subnet) {
                http_response_code(400);
                echo json_encode(['error' => 'Invalid host: private or reserved IP addresses are not allowed']);
                exit;
            }
        }
    } elseif (filter_var($host, FILTER_VALIDATE_IP, FILTER_FLAG_IPV6)) {
        // Block common IPv6 private ranges
        $privateIPv6Prefixes = ['fc', 'fd', 'fe80', '::1', '::'];
        $hostLower = strtolower($host);
        
        foreach ($privateIPv6Prefixes as $prefix) {
            if (strpos($hostLower, $prefix) === 0) {
                http_response_code(400);
                echo json_encode(['error' => 'Invalid host: private IPv6 addresses are not allowed']);
                exit;
            }
        }
    }
    
    // Additional hostname validation
    if (!filter_var($host, FILTER_VALIDATE_IP)) {
        // Validate as hostname
        if (!filter_var('http://' . $host, FILTER_VALIDATE_URL)) {
            http_response_code(400);
            echo json_encode(['error' => 'Invalid hostname format']);
            exit;
        }
        
        // Block localhost and similar
        $blockedHosts = ['localhost', 'localhost.localdomain', '127.0.0.1', '::1'];
        if (in_array(strtolower($host), $blockedHosts)) {
            http_response_code(400);
            echo json_encode(['error' => 'Invalid host: localhost is not allowed']);
            exit;
        }
    }
    
    // Initialize cache, deduplicator, and audit logger
    $cache = new ResultCache();
    $dedup = new RequestDeduplicator();
    $auditLogger = new AuditLogger();
    
    // Generate cache key including all relevant parameters
    $cacheKeyData = [
        'tool' => $tool,
        'host' => $host,
        'locations' => $locations,
        'packetCount' => isset($input['packetCount']) ? intval($input['packetCount']) : 4
    ];
    $cacheKey = md5(json_encode($cacheKeyData));
    
    // Check cache first
    $cachedResult = $cache->get($cacheKey);
    if ($cachedResult !== null) {
        // Log cache hit
        $auditLogger->logRequest([
            'tool' => $tool,
            'host' => $host,
            'locations' => $locations,
            'authenticated' => !empty($authToken),
            'cache_hit' => true
        ]);
        
        echo json_encode([
            'cached' => true,
            'data' => $cachedResult
        ]);
        exit;
    }
    
    // Try to acquire lock for this request
    if (!$dedup->acquireLock($cacheKey)) {
        // Another request is already in progress, wait for it
        $result = $dedup->waitForResult($cacheKey, $cache);
        if ($result !== null) {
            echo json_encode([
                'cached' => true,
                'deduplicated' => true,
                'data' => $result
            ]);
            exit;
        }
        
        // If we couldn't get the result, continue with new request
        // This shouldn't normally happen
    }
    
    // Initialize Globalping client
    $client = new GlobalpingClient();
    
    // Create measurement
    try {
        $options = [];
        if (isset($input['packetCount'])) {
            $options['packets'] = intval($input['packetCount']);
        }
        
        $measurement = $client->createMeasurement($tool, $host, $locations, $options);
        $measurementId = $measurement['id'];
        
        // Poll for results (max 30 seconds)
        $maxAttempts = 30;
        $attempt = 0;
        $results = null;
        
        while ($attempt < $maxAttempts) {
            sleep(1);
            $attempt++;
            
            try {
                $results = $client->getMeasurement($measurementId);
                
                if ($results['status'] === 'finished') {
                    break;
                }
            } catch (Exception $e) {
                // Continue polling
            }
        }
        
        if (!$results || $results['status'] !== 'finished') {
            throw new Exception('Measurement timed out');
        }
        
        // Process results
        $processedResults = [];
        
        foreach ($results['results'] as $result) {
            $probe = $result['probe'];
            
            $processedResult = [
                'location' => [
                    'country' => $probe['country'],
                    'city' => $probe['city'] ?? 'Unknown',
                    'network' => $probe['network'] ?? 'Unknown',
                    'asn' => $probe['asn'] ?? null,
                    'latitude' => $probe['latitude'] ?? null,
                    'longitude' => $probe['longitude'] ?? null
                ],
                'status' => $result['result']['status'],
                'output' => $result['result']['output'] ?? '',
                'error' => null
            ];
            
            // Parse ping statistics
            if ($tool === 'ping' && $result['result']['status'] === 'finished') {
                $stats = $result['result']['stats'] ?? null;
                if ($stats) {
                    $processedResult['stats'] = [
                        'min' => $stats['min'] ?? null,
                        'avg' => $stats['avg'] ?? null,
                        'max' => $stats['max'] ?? null,
                        'loss' => $stats['loss'] ?? null
                    ];
                }
            }
            
            $processedResults[] = $processedResult;
        }
        
        // Cache results
        $cache->set($cacheKey, $processedResults);
        
        // Release lock
        $dedup->releaseLock($cacheKey);
        
        // Log successful request
        $auditLogger->logRequest([
            'tool' => $tool,
            'host' => $host,
            'locations' => $locations,
            'authenticated' => !empty($authToken),
            'cache_hit' => false,
            'measurement_id' => $measurementId
        ]);
        
        // Return results
        echo json_encode([
            'cached' => false,
            'tool' => $tool,
            'host' => $host,
            'measurementId' => $measurementId,
            'data' => $processedResults
        ]);
        
    } catch (Exception $e) {
        // Release lock on error
        if (isset($dedup) && isset($cacheKey)) {
            $dedup->releaseLock($cacheKey);
        }
        
        // Log error
        if (isset($auditLogger)) {
            $auditLogger->logRequest([
                'tool' => $tool ?? 'unknown',
                'host' => $host ?? 'unknown',
                'locations' => $locations ?? [],
                'authenticated' => !empty($authToken),
                'cache_hit' => false,
                'error' => $e->getMessage()
            ]);
        }
        
        http_response_code(500);
        echo json_encode([
            'error' => $e->getMessage()
        ]);
    }
    
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'error' => 'Unexpected error: ' . $e->getMessage()
    ]);
}