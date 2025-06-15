<?php
/**
 * Request deduplication helper
 * Prevents multiple simultaneous requests for the same resource
 */

class RequestDeduplicator {
    private $lockDir;
    private $lockTimeout = 30; // 30 seconds max lock time
    
    public function __construct() {
        $this->lockDir = sys_get_temp_dir() . '/global_network_locks';
        if (!is_dir($this->lockDir)) {
            mkdir($this->lockDir, 0777, true);
        }
    }
    
    /**
     * Try to acquire a lock for a request
     * Returns true if lock acquired, false if request is already in progress
     */
    public function acquireLock($key) {
        $lockFile = $this->lockDir . '/' . md5($key) . '.lock';
        
        // Check if lock exists and is still valid
        if (file_exists($lockFile)) {
            $lockTime = filemtime($lockFile);
            if (time() - $lockTime < $this->lockTimeout) {
                return false; // Lock is still valid
            }
            // Lock expired, remove it
            @unlink($lockFile);
        }
        
        // Try to create lock file atomically
        $handle = @fopen($lockFile, 'x');
        if ($handle === false) {
            return false; // Another process created the lock
        }
        
        fwrite($handle, time());
        fclose($handle);
        return true;
    }
    
    /**
     * Release a lock
     */
    public function releaseLock($key) {
        $lockFile = $this->lockDir . '/' . md5($key) . '.lock';
        @unlink($lockFile);
    }
    
    /**
     * Wait for a lock to be released and get the result
     */
    public function waitForResult($key, $cache, $maxWait = 25) {
        $start = time();
        
        while (time() - $start < $maxWait) {
            // Check if lock is released
            if (!$this->isLocked($key)) {
                // Try to get result from cache
                $result = $cache->get($key);
                if ($result !== null) {
                    return $result;
                }
                break;
            }
            
            // Wait a bit before checking again
            usleep(500000); // 0.5 seconds
        }
        
        return null;
    }
    
    /**
     * Check if a request is locked
     */
    private function isLocked($key) {
        $lockFile = $this->lockDir . '/' . md5($key) . '.lock';
        
        if (!file_exists($lockFile)) {
            return false;
        }
        
        $lockTime = filemtime($lockFile);
        if (time() - $lockTime >= $this->lockTimeout) {
            @unlink($lockFile);
            return false;
        }
        
        return true;
    }
    
    /**
     * Clean up old lock files
     */
    public function cleanup() {
        $files = glob($this->lockDir . '/*.lock');
        if (!$files) return;
        
        foreach ($files as $file) {
            if (file_exists($file)) {
                $lockTime = filemtime($file);
                if (time() - $lockTime >= $this->lockTimeout) {
                    @unlink($file);
                }
            }
        }
    }
}