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
    // Import rate limiting
    require_once 'rate-limit.php';
    
    // Apply rate limiting
    try {
        checkRateLimit('global_network_tools', 20); // 20 requests per hour
    } catch (Exception $e) {
        http_response_code(429);
        echo json_encode(['error' => 'Rate limit exceeded. Please try again later.']);
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
     * Cache implementation
     */
    class ResultCache {
        private $cacheDir;
        private $cacheLifetime = 300; // 5 minutes
        
        public function __construct() {
            $this->cacheDir = sys_get_temp_dir() . '/global_network_cache';
            if (!is_dir($this->cacheDir)) {
                mkdir($this->cacheDir, 0777, true);
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
    
    // Validate host (basic SSRF protection)
    $privateIPs = [
        '10.0.0.0/8',
        '172.16.0.0/12',
        '192.168.0.0/16',
        '127.0.0.0/8',
        '169.254.0.0/16',
        'fc00::/7',
        'fe80::/10'
    ];
    
    // Check if host is an IP
    if (filter_var($host, FILTER_VALIDATE_IP)) {
        foreach ($privateIPs as $range) {
            if (strpos($range, ':') !== false) {
                // IPv6 range check
                continue; // Skip IPv6 for now
            } else {
                // IPv4 range check
                list($subnet, $bits) = explode('/', $range);
                $subnet = ip2long($subnet);
                $mask = -1 << (32 - $bits);
                $subnet &= $mask;
                
                if ((ip2long($host) & $mask) == $subnet) {
                    http_response_code(400);
                    echo json_encode(['error' => 'Invalid host: private IP addresses are not allowed']);
                    exit;
                }
            }
        }
    }
    
    // Initialize cache
    $cache = new ResultCache();
    
    // Generate cache key
    $cacheKey = sprintf('%s:%s:%s', $tool, $host, md5(json_encode($locations)));
    
    // Check cache first
    $cachedResult = $cache->get($cacheKey);
    if ($cachedResult !== null) {
        echo json_encode([
            'cached' => true,
            'data' => $cachedResult
        ]);
        exit;
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
        
        // Return results
        echo json_encode([
            'cached' => false,
            'tool' => $tool,
            'host' => $host,
            'measurementId' => $measurementId,
            'data' => $processedResults
        ]);
        
    } catch (Exception $e) {
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