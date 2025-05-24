<?php
/**
 * Rate limiting handler for API endpoints
 * 
 * This class provides centralized rate limiting functionality using
 * file-based storage, suitable for low-traffic personal sites.
 */

require_once __DIR__ . '/Config.php';

class RateLimit {
    private $config;
    private $endpoint;
    private $limit;
    private $window = 60; // 1 minute window
    
    public function __construct($endpoint = 'default') {
        $this->config = Config::getInstance();
        $this->endpoint = $endpoint;
        $this->limit = $this->config->getRateLimit($endpoint);
    }
    
    /**
     * Check if the current request should be rate limited
     * 
     * @return bool True if request should proceed, false if rate limited
     */
    public function check() {
        $ip = $this->getClientIP();
        $key = $this->getRateLimitKey($ip);
        $data = $this->getRateLimitData($key);
        
        $now = time();
        $windowStart = $now - $this->window;
        
        // Clean old entries
        $data = array_filter($data, function($timestamp) use ($windowStart) {
            return $timestamp > $windowStart;
        });
        
        // Check limit
        if (count($data) >= $this->limit) {
            $this->sendRateLimitHeaders(count($data));
            return false;
        }
        
        // Add current request
        $data[] = $now;
        $this->saveRateLimitData($key, $data);
        
        return true;
    }
    
    /**
     * Enforce rate limit - sends error response if limited
     */
    public function enforce() {
        if (!$this->check()) {
            http_response_code(429);
            header('Content-Type: application/json');
            echo json_encode([
                'error' => 'Rate limit exceeded',
                'message' => "Maximum {$this->limit} requests per minute allowed",
                'retry_after' => $this->window
            ]);
            exit;
        }
    }
    
    /**
     * Get client IP address (Cloudflare-aware)
     */
    private function getClientIP() {
        // Check for Cloudflare's CF-Connecting-IP header first
        if (!empty($_SERVER['HTTP_CF_CONNECTING_IP'])) {
            return $_SERVER['HTTP_CF_CONNECTING_IP'];
        }
        
        // Fallback to X-Forwarded-For
        if (!empty($_SERVER['HTTP_X_FORWARDED_FOR'])) {
            $ips = explode(',', $_SERVER['HTTP_X_FORWARDED_FOR']);
            return trim($ips[0]);
        }
        
        // Fallback to direct connection
        return $_SERVER['REMOTE_ADDR'] ?? '127.0.0.1';
    }
    
    /**
     * Get rate limit storage key
     */
    private function getRateLimitKey($ip) {
        return md5($this->endpoint . ':' . $ip);
    }
    
    /**
     * Get rate limit data from storage
     */
    private function getRateLimitData($key) {
        $cacheDir = sys_get_temp_dir() . '/zeronexus_ratelimit';
        if (!is_dir($cacheDir)) {
            mkdir($cacheDir, 0777, true);
        }
        
        $file = $cacheDir . '/' . $key;
        if (!file_exists($file)) {
            return [];
        }
        
        $data = @file_get_contents($file);
        if ($data === false) {
            return [];
        }
        
        $decoded = json_decode($data, true);
        return is_array($decoded) ? $decoded : [];
    }
    
    /**
     * Save rate limit data to storage
     */
    private function saveRateLimitData($key, $data) {
        $cacheDir = sys_get_temp_dir() . '/zeronexus_ratelimit';
        if (!is_dir($cacheDir)) {
            mkdir($cacheDir, 0777, true);
        }
        
        $file = $cacheDir . '/' . $key;
        @file_put_contents($file, json_encode($data));
        
        // Clean old files occasionally (1% chance)
        if (mt_rand(1, 100) === 1) {
            $this->cleanOldFiles($cacheDir);
        }
    }
    
    /**
     * Clean old rate limit files
     */
    private function cleanOldFiles($dir) {
        $expiry = time() - ($this->window * 2); // Clean files older than 2 windows
        
        $files = glob($dir . '/*');
        foreach ($files as $file) {
            if (is_file($file) && filemtime($file) < $expiry) {
                @unlink($file);
            }
        }
    }
    
    /**
     * Send rate limit headers
     */
    private function sendRateLimitHeaders($current) {
        header("X-RateLimit-Limit: {$this->limit}");
        header("X-RateLimit-Remaining: " . max(0, $this->limit - $current));
        header("X-RateLimit-Reset: " . (time() + $this->window));
        header("Retry-After: {$this->window}");
    }
    
    /**
     * Quick static method for simple rate limiting
     * 
     * @param string $endpoint Endpoint name for rate limit configuration
     */
    public static function simple($endpoint = 'default') {
        $limiter = new self($endpoint);
        $limiter->enforce();
    }
}