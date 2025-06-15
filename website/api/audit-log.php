<?php
/**
 * Audit logging functionality for network tools
 */

class AuditLogger {
    private $logFile;
    private $maxLogSize = 10485760; // 10MB
    private $maxLogFiles = 5;
    
    public function __construct($logName = 'global_network_tools') {
        $logDir = sys_get_temp_dir() . '/network_tools_logs';
        if (!is_dir($logDir)) {
            mkdir($logDir, 0777, true);
        }
        
        $this->logFile = $logDir . '/' . $logName . '.log';
        $this->rotateLogsIfNeeded();
    }
    
    /**
     * Log a request
     */
    public function logRequest($data) {
        $logEntry = [
            'timestamp' => date('Y-m-d H:i:s'),
            'ip' => $this->getClientIP(),
            'user_agent' => $_SERVER['HTTP_USER_AGENT'] ?? 'Unknown',
            'tool' => $data['tool'] ?? 'unknown',
            'host' => $data['host'] ?? 'unknown',
            'locations' => count($data['locations'] ?? []),
            'authenticated' => $data['authenticated'] ?? false,
            'cache_hit' => $data['cache_hit'] ?? false,
            'measurement_id' => $data['measurement_id'] ?? null,
            'error' => $data['error'] ?? null
        ];
        
        $logLine = json_encode($logEntry) . PHP_EOL;
        
        // Atomic write
        file_put_contents($this->logFile, $logLine, FILE_APPEND | LOCK_EX);
    }
    
    /**
     * Get client IP address
     */
    private function getClientIP() {
        // Check for Cloudflare IP
        if (!empty($_SERVER['HTTP_CF_CONNECTING_IP'])) {
            return $_SERVER['HTTP_CF_CONNECTING_IP'];
        }
        
        // Check for proxy headers
        $headers = [
            'HTTP_CLIENT_IP',
            'HTTP_X_FORWARDED_FOR',
            'HTTP_X_FORWARDED',
            'HTTP_X_CLUSTER_CLIENT_IP',
            'HTTP_FORWARDED_FOR',
            'HTTP_FORWARDED',
            'REMOTE_ADDR'
        ];
        
        foreach ($headers as $header) {
            if (!empty($_SERVER[$header])) {
                $ips = explode(',', $_SERVER[$header]);
                $ip = trim($ips[0]);
                
                // Validate IP
                if (filter_var($ip, FILTER_VALIDATE_IP, FILTER_FLAG_NO_PRIV_RANGE | FILTER_FLAG_NO_RES_RANGE)) {
                    return $ip;
                }
            }
        }
        
        return $_SERVER['REMOTE_ADDR'] ?? 'Unknown';
    }
    
    /**
     * Rotate logs if needed
     */
    private function rotateLogsIfNeeded() {
        if (!file_exists($this->logFile)) {
            return;
        }
        
        $size = filesize($this->logFile);
        if ($size < $this->maxLogSize) {
            return;
        }
        
        // Rotate logs
        for ($i = $this->maxLogFiles - 1; $i >= 1; $i--) {
            $oldFile = $this->logFile . '.' . $i;
            $newFile = $this->logFile . '.' . ($i + 1);
            
            if (file_exists($oldFile)) {
                if ($i == $this->maxLogFiles - 1) {
                    unlink($oldFile); // Delete oldest
                } else {
                    rename($oldFile, $newFile);
                }
            }
        }
        
        // Rename current log
        rename($this->logFile, $this->logFile . '.1');
    }
    
    /**
     * Get recent suspicious activity
     */
    public function getSuspiciousActivity($hours = 1) {
        if (!file_exists($this->logFile)) {
            return [];
        }
        
        $suspicious = [];
        $ipCounts = [];
        $since = time() - ($hours * 3600);
        
        // Read recent log entries
        $lines = file($this->logFile, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
        $lines = array_slice($lines, -1000); // Last 1000 entries
        
        foreach ($lines as $line) {
            $entry = json_decode($line, true);
            if (!$entry) continue;
            
            $timestamp = strtotime($entry['timestamp']);
            if ($timestamp < $since) continue;
            
            $ip = $entry['ip'];
            if (!isset($ipCounts[$ip])) {
                $ipCounts[$ip] = 0;
            }
            $ipCounts[$ip]++;
            
            // Flag high request rates
            if ($ipCounts[$ip] > 50) {
                $suspicious[] = [
                    'type' => 'high_request_rate',
                    'ip' => $ip,
                    'count' => $ipCounts[$ip],
                    'timestamp' => $entry['timestamp']
                ];
            }
            
            // Flag errors
            if (!empty($entry['error'])) {
                $suspicious[] = [
                    'type' => 'error',
                    'ip' => $ip,
                    'error' => $entry['error'],
                    'timestamp' => $entry['timestamp']
                ];
            }
        }
        
        return $suspicious;
    }
}