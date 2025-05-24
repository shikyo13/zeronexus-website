<?php
/**
 * Standardized API response handler
 * 
 * This class provides consistent JSON response formatting and error handling
 * across all API endpoints.
 */

require_once __DIR__ . '/Config.php';

class Response {
    private $config;
    
    public function __construct() {
        $this->config = Config::getInstance();
        
        // Set default JSON header
        header('Content-Type: application/json');
        
        // Set up error handling
        $this->setupErrorHandling();
    }
    
    /**
     * Send a successful response
     * 
     * @param mixed $data Response data
     * @param int $statusCode HTTP status code
     */
    public function success($data, $statusCode = 200) {
        http_response_code($statusCode);
        
        $response = [
            'success' => true,
            'data' => $data
        ];
        
        if ($this->config->isDebug()) {
            $response['debug'] = [
                'environment' => $this->config->get('environment'),
                'timestamp' => date('Y-m-d H:i:s')
            ];
        }
        
        echo json_encode($response, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);
        exit;
    }
    
    /**
     * Send an error response
     * 
     * @param string $message Error message
     * @param int $statusCode HTTP status code
     * @param array $details Additional error details
     */
    public function error($message, $statusCode = 400, $details = []) {
        http_response_code($statusCode);
        
        $response = [
            'success' => false,
            'error' => $message
        ];
        
        if (!empty($details)) {
            $response['details'] = $details;
        }
        
        if ($this->config->isDebug()) {
            $response['debug'] = [
                'environment' => $this->config->get('environment'),
                'timestamp' => date('Y-m-d H:i:s'),
                'request_method' => $_SERVER['REQUEST_METHOD'] ?? 'unknown',
                'request_uri' => $_SERVER['REQUEST_URI'] ?? 'unknown'
            ];
        }
        
        echo json_encode($response, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);
        exit;
    }
    
    /**
     * Send a validation error response
     * 
     * @param array $errors Validation errors
     */
    public function validationError($errors) {
        $this->error('Validation failed', 422, ['validation_errors' => $errors]);
    }
    
    /**
     * Send a not found response
     * 
     * @param string $message Custom message
     */
    public function notFound($message = 'Resource not found') {
        $this->error($message, 404);
    }
    
    /**
     * Send a server error response
     * 
     * @param string $message Error message
     * @param Exception $exception Optional exception for debugging
     */
    public function serverError($message = 'Internal server error', $exception = null) {
        $details = [];
        
        if ($this->config->isDebug() && $exception) {
            $details['exception'] = [
                'message' => $exception->getMessage(),
                'file' => basename($exception->getFile()),
                'line' => $exception->getLine()
            ];
        }
        
        $this->error($message, 500, $details);
    }
    
    /**
     * Set up error handling to ensure JSON responses
     */
    private function setupErrorHandling() {
        // Disable HTML error output
        ini_set('display_errors', 0);
        ini_set('display_startup_errors', 0);
        
        // Set error reporting based on environment
        if ($this->config->isDebug()) {
            error_reporting(E_ALL);
        } else {
            error_reporting(E_ERROR | E_PARSE);
        }
        
        // Register error handler
        set_error_handler([$this, 'handleError']);
        
        // Register shutdown function for fatal errors
        register_shutdown_function([$this, 'handleShutdown']);
    }
    
    /**
     * Handle PHP errors
     */
    public function handleError($errno, $errstr, $errfile, $errline) {
        // Don't handle suppressed errors
        if (!(error_reporting() & $errno)) {
            return false;
        }
        
        $this->serverError('PHP Error occurred', new \ErrorException($errstr, 0, $errno, $errfile, $errline));
    }
    
    /**
     * Handle fatal errors on shutdown
     */
    public function handleShutdown() {
        $error = error_get_last();
        if ($error !== null && in_array($error['type'], [E_ERROR, E_PARSE, E_CORE_ERROR, E_COMPILE_ERROR])) {
            $this->serverError('Fatal error occurred');
        }
    }
    
    /**
     * Quick static method for sending success response
     */
    public static function sendSuccess($data, $statusCode = 200) {
        $response = new self();
        $response->success($data, $statusCode);
    }
    
    /**
     * Quick static method for sending error response
     */
    public static function sendError($message, $statusCode = 400, $details = []) {
        $response = new self();
        $response->error($message, $statusCode, $details);
    }
}