<?php
/**
 * Simple file-based cache handler
 * 
 * This class provides caching functionality suitable for low-traffic sites,
 * using the filesystem for storage with automatic cleanup.
 */

require_once __DIR__ . '/Config.php';

class Cache {
    private $config;
    private $cacheDir;
    private $defaultTTL = 3600; // 1 hour
    
    public function __construct($namespace = 'default') {
        $this->config = Config::getInstance();
        $this->cacheDir = sys_get_temp_dir() . '/zeronexus_cache/' . $namespace;
        $this->ensureCacheDirectory();
    }
    
    /**
     * Get cached value
     * 
     * @param string $key Cache key
     * @return mixed|null Cached value or null if not found/expired
     */
    public function get($key) {
        $filename = $this->getCacheFilename($key);
        
        if (!file_exists($filename)) {
            return null;
        }
        
        $data = @file_get_contents($filename);
        if ($data === false) {
            return null;
        }
        
        $cached = @unserialize($data);
        if ($cached === false || !is_array($cached)) {
            return null;
        }
        
        // Check expiration
        if (isset($cached['expires']) && $cached['expires'] < time()) {
            @unlink($filename);
            return null;
        }
        
        return $cached['data'] ?? null;
    }
    
    /**
     * Set cache value
     * 
     * @param string $key Cache key
     * @param mixed $value Value to cache
     * @param int|null $ttl Time to live in seconds
     */
    public function set($key, $value, $ttl = null) {
        if ($ttl === null) {
            $ttl = $this->defaultTTL;
        }
        
        $filename = $this->getCacheFilename($key);
        $data = [
            'data' => $value,
            'expires' => time() + $ttl,
            'created' => time()
        ];
        
        @file_put_contents($filename, serialize($data));
        
        // Cleanup old files occasionally (1% chance)
        if (mt_rand(1, 100) === 1) {
            $this->cleanup();
        }
    }
    
    /**
     * Delete cached value
     * 
     * @param string $key Cache key
     */
    public function delete($key) {
        $filename = $this->getCacheFilename($key);
        if (file_exists($filename)) {
            @unlink($filename);
        }
    }
    
    /**
     * Clear all cache in namespace
     */
    public function clear() {
        $files = glob($this->cacheDir . '/*');
        foreach ($files as $file) {
            if (is_file($file)) {
                @unlink($file);
            }
        }
    }
    
    /**
     * Get or set cached value (helper method)
     * 
     * @param string $key Cache key
     * @param callable $callback Function to generate value if not cached
     * @param int|null $ttl Time to live in seconds
     * @return mixed Cached or generated value
     */
    public function remember($key, $callback, $ttl = null) {
        $cached = $this->get($key);
        
        if ($cached !== null) {
            return $cached;
        }
        
        $value = $callback();
        $this->set($key, $value, $ttl);
        
        return $value;
    }
    
    /**
     * Clean up expired cache files
     */
    private function cleanup() {
        $files = glob($this->cacheDir . '/*');
        $now = time();
        
        foreach ($files as $file) {
            if (!is_file($file)) {
                continue;
            }
            
            $data = @file_get_contents($file);
            if ($data === false) {
                @unlink($file);
                continue;
            }
            
            $cached = @unserialize($data);
            if ($cached === false || !is_array($cached)) {
                @unlink($file);
                continue;
            }
            
            if (isset($cached['expires']) && $cached['expires'] < $now) {
                @unlink($file);
            }
        }
    }
    
    /**
     * Get cache filename for key
     */
    private function getCacheFilename($key) {
        return $this->cacheDir . '/' . md5($key);
    }
    
    /**
     * Ensure cache directory exists
     */
    private function ensureCacheDirectory() {
        if (!is_dir($this->cacheDir)) {
            mkdir($this->cacheDir, 0777, true);
        }
    }
    
    /**
     * Static method to quickly cache a value
     * 
     * @param string $namespace Cache namespace
     * @param string $key Cache key
     * @param callable $callback Function to generate value
     * @param int|null $ttl Time to live
     * @return mixed Cached or generated value
     */
    public static function quick($namespace, $key, $callback, $ttl = null) {
        $cache = new self($namespace);
        return $cache->remember($key, $callback, $ttl);
    }
}