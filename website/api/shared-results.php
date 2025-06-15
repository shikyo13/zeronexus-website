<?php
/**
 * Shared Results API
 * Allows users to share network test results via unique links
 */

// Set headers for JSON API
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST');
header('Access-Control-Allow-Headers: Content-Type');

// Error handling
error_reporting(E_ALL);
ini_set('display_errors', 0);

// Register shutdown function to catch fatal errors
register_shutdown_function(function() {
    $error = error_get_last();
    if ($error !== null && in_array($error['type'], [E_ERROR, E_PARSE, E_CORE_ERROR, E_COMPILE_ERROR])) {
        http_response_code(500);
        echo json_encode(['error' => 'Fatal server error']);
        exit;
    }
});

try {
    require_once 'rate-limit.php';
    
    // Apply rate limiting
    try {
        checkRateLimit('shared_results', 50); // 50 requests per hour
    } catch (Exception $e) {
        http_response_code(429);
        echo json_encode(['error' => 'Rate limit exceeded']);
        exit;
    }
    
    /**
     * Shared Results Manager
     */
    class SharedResultsManager {
        private $storageDir;
        private $maxAge = 2592000; // 30 days
        private $maxResults = 1000;
        
        public function __construct() {
            $this->storageDir = sys_get_temp_dir() . '/shared_network_results';
            if (!is_dir($this->storageDir)) {
                mkdir($this->storageDir, 0777, true);
            }
            
            // Periodic cleanup
            if (rand(1, 20) === 1) {
                $this->cleanup();
            }
        }
        
        /**
         * Store results and return share ID
         */
        public function storeResults($data) {
            // Validate data
            if (!isset($data['tool'], $data['host'], $data['data'])) {
                throw new Exception('Invalid result data');
            }
            
            // Generate unique ID
            $shareId = $this->generateShareId();
            
            // Prepare storage data
            $storeData = [
                'id' => $shareId,
                'created' => time(),
                'tool' => $data['tool'],
                'host' => $data['host'],
                'measurementId' => $data['measurementId'] ?? null,
                'data' => $data['data'],
                'metadata' => [
                    'locations' => count($data['data']),
                    'cached' => $data['cached'] ?? false,
                    'user_agent' => $_SERVER['HTTP_USER_AGENT'] ?? 'Unknown'
                ]
            ];
            
            // Store to file
            $filename = $this->storageDir . '/' . $shareId . '.json';
            file_put_contents($filename, json_encode($storeData, JSON_PRETTY_PRINT));
            
            return $shareId;
        }
        
        /**
         * Retrieve shared results
         */
        public function getResults($shareId) {
            // Validate share ID format
            if (!preg_match('/^[a-zA-Z0-9]{12}$/', $shareId)) {
                throw new Exception('Invalid share ID format');
            }
            
            $filename = $this->storageDir . '/' . $shareId . '.json';
            
            if (!file_exists($filename)) {
                throw new Exception('Shared result not found');
            }
            
            // Check age
            $age = time() - filemtime($filename);
            if ($age > $this->maxAge) {
                unlink($filename);
                throw new Exception('Shared result has expired');
            }
            
            $data = json_decode(file_get_contents($filename), true);
            if (!$data) {
                throw new Exception('Invalid shared result data');
            }
            
            return $data;
        }
        
        /**
         * Generate unique share ID
         */
        private function generateShareId() {
            $chars = 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789';
            $id = '';
            
            do {
                $id = '';
                for ($i = 0; $i < 12; $i++) {
                    $id .= $chars[random_int(0, strlen($chars) - 1)];
                }
            } while (file_exists($this->storageDir . '/' . $id . '.json'));
            
            return $id;
        }
        
        /**
         * Clean up old results
         */
        private function cleanup() {
            $files = glob($this->storageDir . '/*.json');
            if (!$files) return;
            
            // Sort by modification time
            usort($files, function($a, $b) {
                return filemtime($a) - filemtime($b);
            });
            
            $removed = 0;
            
            // Remove expired files
            foreach ($files as $file) {
                $age = time() - filemtime($file);
                if ($age > $this->maxAge) {
                    unlink($file);
                    $removed++;
                }
            }
            
            // If still too many files, remove oldest
            $remaining = count($files) - $removed;
            if ($remaining > $this->maxResults) {
                $toRemove = $remaining - $this->maxResults;
                for ($i = 0; $i < $toRemove && $i < count($files); $i++) {
                    if (file_exists($files[$i])) {
                        unlink($files[$i]);
                    }
                }
            }
        }
        
        /**
         * Get storage statistics
         */
        public function getStats() {
            $files = glob($this->storageDir . '/*.json');
            $totalSize = 0;
            $oldestAge = 0;
            
            foreach ($files as $file) {
                $totalSize += filesize($file);
                $age = time() - filemtime($file);
                $oldestAge = max($oldestAge, $age);
            }
            
            return [
                'count' => count($files),
                'total_size' => $totalSize,
                'oldest_age_days' => round($oldestAge / 86400, 1)
            ];
        }
    }
    
    $manager = new SharedResultsManager();
    
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        // Store new results
        $input = json_decode(file_get_contents('php://input'), true);
        
        if (!$input) {
            http_response_code(400);
            echo json_encode(['error' => 'Invalid JSON input']);
            exit;
        }
        
        try {
            $shareId = $manager->storeResults($input);
            
            echo json_encode([
                'success' => true,
                'share_id' => $shareId,
                'share_url' => 'https://' . $_SERVER['HTTP_HOST'] . '/network-admin.php#ping-traceroute?share=' . $shareId,
                'expires_in' => 2592000 // 30 days
            ]);
            
        } catch (Exception $e) {
            http_response_code(400);
            echo json_encode(['error' => $e->getMessage()]);
        }
        
    } elseif ($_SERVER['REQUEST_METHOD'] === 'GET') {
        // Retrieve shared results
        $shareId = $_GET['id'] ?? '';
        
        if (empty($shareId)) {
            http_response_code(400);
            echo json_encode(['error' => 'Missing share ID']);
            exit;
        }
        
        try {
            $results = $manager->getResults($shareId);
            echo json_encode($results);
            
        } catch (Exception $e) {
            http_response_code(404);
            echo json_encode(['error' => $e->getMessage()]);
        }
        
    } else {
        http_response_code(405);
        echo json_encode(['error' => 'Method not allowed']);
    }
    
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['error' => 'Server error: ' . $e->getMessage()]);
}