<?php
/**
 * HTTP Client for external API requests
 * 
 * This class provides a standardized way to make HTTP requests to external APIs,
 * with built-in caching, error handling, and SSL configuration.
 */

require_once __DIR__ . '/Config.php';
require_once __DIR__ . '/Cache.php';

class HttpClient {
    private $config;
    private $cache;
    private $defaultOptions = [];
    
    public function __construct($cacheNamespace = null) {
        $this->config = Config::getInstance();
        $this->cache = $cacheNamespace ? new Cache($cacheNamespace) : null;
        
        // Set default cURL options
        $this->defaultOptions = [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_TIMEOUT => 30,
            CURLOPT_CONNECTTIMEOUT => 10,
            CURLOPT_USERAGENT => 'ZeroNexus API Client/1.0',
            // SSL Verification Architecture Decision:
            // SSL verification is disabled because:
            // 1. Production runs on Windows 11 home server with Cloudflare Tunnel
            // 2. SSL/HTTPS is handled at the edge by Cloudflare
            // 3. The Ubuntu VM doesn't need to verify SSL for external requests
            // 4. This simplifies certificate management for a personal site
            // This is an intentional design decision, not a security oversight.
            CURLOPT_SSL_VERIFYPEER => $this->config->get('ssl_verify', false),
            CURLOPT_SSL_VERIFYHOST => $this->config->get('ssl_verify', false) ? 2 : 0
        ];
    }
    
    /**
     * Make a GET request
     * 
     * @param string $url URL to request
     * @param array $headers Optional headers
     * @param int|null $cacheTTL Cache TTL in seconds (null to disable cache)
     * @return array Response with 'body', 'status', 'headers', 'error'
     */
    public function get($url, $headers = [], $cacheTTL = null) {
        return $this->request('GET', $url, null, $headers, $cacheTTL);
    }
    
    /**
     * Make a POST request
     * 
     * @param string $url URL to request
     * @param mixed $data Data to send (array or string)
     * @param array $headers Optional headers
     * @return array Response with 'body', 'status', 'headers', 'error'
     */
    public function post($url, $data = null, $headers = []) {
        return $this->request('POST', $url, $data, $headers, null);
    }
    
    /**
     * Make a request with caching support
     * 
     * @param string $method HTTP method
     * @param string $url URL to request
     * @param mixed $data Data for POST requests
     * @param array $headers Headers to send
     * @param int|null $cacheTTL Cache TTL in seconds
     * @return array Response array
     */
    private function request($method, $url, $data = null, $headers = [], $cacheTTL = null) {
        // Try cache first for GET requests
        if ($method === 'GET' && $this->cache && $cacheTTL !== null) {
            $cacheKey = 'http_' . md5($url . serialize($headers));
            $cached = $this->cache->get($cacheKey);
            
            if ($cached !== null) {
                return $cached;
            }
        }
        
        // Initialize cURL
        $ch = curl_init();
        
        // Set URL and method
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, $method);
        
        // Apply default options
        curl_setopt_array($ch, $this->defaultOptions);
        
        // Set headers
        if (!empty($headers)) {
            $formattedHeaders = [];
            foreach ($headers as $key => $value) {
                if (is_numeric($key)) {
                    $formattedHeaders[] = $value;
                } else {
                    $formattedHeaders[] = "$key: $value";
                }
            }
            curl_setopt($ch, CURLOPT_HTTPHEADER, $formattedHeaders);
        }
        
        // Set POST data
        if ($method === 'POST' && $data !== null) {
            if (is_array($data)) {
                curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($data));
            } else {
                curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
            }
        }
        
        // Capture response headers
        curl_setopt($ch, CURLOPT_HEADER, true);
        
        // Execute request
        $response = curl_exec($ch);
        $error = curl_error($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $headerSize = curl_getinfo($ch, CURLINFO_HEADER_SIZE);
        
        curl_close($ch);
        
        // Prepare result
        $result = [
            'success' => false,
            'status' => $httpCode,
            'headers' => [],
            'body' => '',
            'error' => null
        ];
        
        if ($error) {
            $result['error'] = $error;
        } else {
            // Parse headers and body
            $headerText = substr($response, 0, $headerSize);
            $result['body'] = substr($response, $headerSize);
            
            // Parse headers
            $headerLines = explode("\r\n", trim($headerText));
            foreach ($headerLines as $line) {
                if (strpos($line, ':') !== false) {
                    list($key, $value) = explode(':', $line, 2);
                    $result['headers'][strtolower(trim($key))] = trim($value);
                }
            }
            
            $result['success'] = $httpCode >= 200 && $httpCode < 300;
        }
        
        // Cache successful GET requests
        if ($method === 'GET' && $result['success'] && $this->cache && $cacheTTL !== null) {
            $this->cache->set($cacheKey, $result, $cacheTTL);
        }
        
        return $result;
    }
    
    /**
     * Make a JSON API request
     * 
     * @param string $method HTTP method
     * @param string $url URL to request
     * @param mixed $data Data to send (will be JSON encoded)
     * @param array $headers Additional headers
     * @param int|null $cacheTTL Cache TTL for GET requests
     * @return array Decoded JSON response or error
     */
    public function json($method, $url, $data = null, $headers = [], $cacheTTL = null) {
        // Add JSON headers
        $headers['Accept'] = 'application/json';
        
        if ($method !== 'GET' && $data !== null) {
            $headers['Content-Type'] = 'application/json';
            $data = json_encode($data);
        }
        
        $response = $this->request($method, $url, $data, $headers, $cacheTTL);
        
        if ($response['success'] && !empty($response['body'])) {
            $decoded = json_decode($response['body'], true);
            if (json_last_error() === JSON_ERROR_NONE) {
                $response['data'] = $decoded;
            } else {
                $response['success'] = false;
                $response['error'] = 'Invalid JSON response: ' . json_last_error_msg();
            }
        }
        
        return $response;
    }
    
    /**
     * Quick static method for simple GET requests
     * 
     * @param string $url URL to fetch
     * @param int|null $cacheTTL Cache TTL in seconds
     * @return string|false Response body or false on error
     */
    public static function fetch($url, $cacheTTL = null) {
        $client = new self();
        $response = $client->get($url, [], $cacheTTL);
        return $response['success'] ? $response['body'] : false;
    }
    
    /**
     * Quick static method for JSON API requests
     * 
     * @param string $url URL to fetch
     * @param int|null $cacheTTL Cache TTL in seconds
     * @return array|false Decoded JSON or false on error
     */
    public static function fetchJson($url, $cacheTTL = null) {
        $client = new self();
        $response = $client->json('GET', $url, null, [], $cacheTTL);
        return $response['success'] && isset($response['data']) ? $response['data'] : false;
    }
}