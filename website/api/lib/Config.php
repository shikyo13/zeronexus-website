<?php
/**
 * Configuration management for API endpoints
 * 
 * This class provides centralized configuration management including
 * environment detection, debug settings, and common constants.
 */

class Config {
    private static $instance = null;
    private $config = [];
    
    private function __construct() {
        $this->initializeConfig();
    }
    
    /**
     * Get singleton instance
     */
    public static function getInstance() {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }
    
    /**
     * Initialize configuration based on environment
     */
    private function initializeConfig() {
        // Detect environment
        $this->config['environment'] = $this->detectEnvironment();
        
        // Set debug mode based on environment
        $this->config['debug'] = $this->config['environment'] === 'development';
        
        // API rate limits (requests per minute)
        $this->config['rate_limits'] = [
            'default' => 60,
            'feeds' => 60,
            'cve-proxy' => 20,
            'network-tools' => 10,
            'article-image' => 30,
            'security-headers' => 20
        ];
        
        // Cache TTL in seconds
        $this->config['cache_ttl'] = [
            'feeds' => 300,        // 5 minutes
            'cve' => 3600,         // 1 hour
            'article-image' => 86400, // 24 hours
            'dns-lookup' => 300    // 5 minutes
        ];
        
        // Allowed CORS origins
        $this->config['cors_origins'] = [
            'https://zeronexus.net',
            'https://www.zeronexus.net',
            'https://*.zeronexus.net',
            'http://localhost:8081',
            'http://localhost:8082'
        ];
        
        // SSL verification setting
        // Note: Disabled for home server setup with Cloudflare Tunnel handling SSL
        // This is intentional for proxying external APIs in this architecture
        $this->config['ssl_verify'] = false;
    }
    
    /**
     * Detect current environment
     */
    private function detectEnvironment() {
        // Check multiple sources for environment
        if (isset($_SERVER['ENVIRONMENT'])) {
            return $_SERVER['ENVIRONMENT'];
        }
        
        // Check if running on localhost
        if (isset($_SERVER['HTTP_HOST'])) {
            $host = $_SERVER['HTTP_HOST'];
            if (strpos($host, 'localhost') !== false || strpos($host, '127.0.0.1') !== false) {
                return 'development';
            }
        }
        
        // Default to production
        return 'production';
    }
    
    /**
     * Get configuration value
     */
    public function get($key, $default = null) {
        return $this->config[$key] ?? $default;
    }
    
    /**
     * Check if in debug mode
     */
    public function isDebug() {
        return $this->config['debug'];
    }
    
    /**
     * Check if in development environment
     */
    public function isDevelopment() {
        return $this->config['environment'] === 'development';
    }
    
    /**
     * Get rate limit for specific endpoint
     */
    public function getRateLimit($endpoint) {
        $limits = $this->get('rate_limits', []);
        return $limits[$endpoint] ?? $limits['default'] ?? 60;
    }
    
    /**
     * Get cache TTL for specific type
     */
    public function getCacheTTL($type) {
        $ttls = $this->get('cache_ttl', []);
        return $ttls[$type] ?? 3600; // Default 1 hour
    }
}