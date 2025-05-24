<?php
/**
 * CORS (Cross-Origin Resource Sharing) handler
 * 
 * This class provides centralized CORS handling for all API endpoints,
 * eliminating code duplication and ensuring consistent security policies.
 */

require_once __DIR__ . '/Config.php';

class CORS {
    private $config;
    private $allowedOrigins;
    
    public function __construct() {
        $this->config = Config::getInstance();
        $this->allowedOrigins = $this->config->get('cors_origins', []);
    }
    
    /**
     * Handle CORS headers for the current request
     * 
     * @param array $additionalOrigins Optional additional allowed origins
     * @param bool $allowAll Allow all origins (use with caution, only for public utilities)
     */
    public function handle($additionalOrigins = [], $allowAll = false) {
        // Handle preflight OPTIONS request
        if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
            $this->handlePreflight($allowAll);
            exit;
        }
        
        // Set CORS headers
        $this->setHeaders($additionalOrigins, $allowAll);
    }
    
    /**
     * Handle preflight OPTIONS request
     */
    private function handlePreflight($allowAll = false) {
        if ($allowAll) {
            header('Access-Control-Allow-Origin: *');
        } else {
            $origin = $this->getValidOrigin();
            if ($origin) {
                header("Access-Control-Allow-Origin: $origin");
            }
        }
        
        header('Access-Control-Allow-Methods: GET, POST, OPTIONS');
        header('Access-Control-Allow-Headers: Content-Type, X-Requested-With');
        header('Access-Control-Max-Age: 86400'); // 24 hours
        header('Content-Type: text/plain; charset=utf-8');
        header('Content-Length: 0');
        http_response_code(204);
    }
    
    /**
     * Set CORS headers for the response
     */
    private function setHeaders($additionalOrigins = [], $allowAll = false) {
        if ($allowAll) {
            header('Access-Control-Allow-Origin: *');
        } else {
            $origin = $this->getValidOrigin($additionalOrigins);
            if ($origin) {
                header("Access-Control-Allow-Origin: $origin");
                header('Vary: Origin');
            }
        }
        
        header('Access-Control-Allow-Methods: GET, POST, OPTIONS');
        header('Access-Control-Allow-Headers: Content-Type, X-Requested-With');
    }
    
    /**
     * Get valid origin from request
     */
    private function getValidOrigin($additionalOrigins = []) {
        if (!isset($_SERVER['HTTP_ORIGIN'])) {
            return null;
        }
        
        $requestOrigin = $_SERVER['HTTP_ORIGIN'];
        $allOrigins = array_merge($this->allowedOrigins, $additionalOrigins);
        
        // Check exact match
        if (in_array($requestOrigin, $allOrigins)) {
            return $requestOrigin;
        }
        
        // Check wildcard patterns
        foreach ($allOrigins as $pattern) {
            if (strpos($pattern, '*') !== false) {
                $regex = '/^' . str_replace(['*', '.'], ['.*', '\.'], $pattern) . '$/';
                if (preg_match($regex, $requestOrigin)) {
                    return $requestOrigin;
                }
            }
        }
        
        return null;
    }
    
    /**
     * Quick static method for simple CORS handling
     * 
     * @param bool $allowAll Allow all origins
     */
    public static function simple($allowAll = false) {
        $cors = new self();
        $cors->handle([], $allowAll);
    }
}