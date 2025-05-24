<?php
/**
 * Simple autoloader for API library classes
 * 
 * Include this file to automatically load all utility classes
 */

spl_autoload_register(function ($class) {
    $file = __DIR__ . '/' . $class . '.php';
    if (file_exists($file)) {
        require_once $file;
    }
});

// Optional: Load all classes immediately for better performance
$classes = ['Config', 'CORS', 'RateLimit', 'Response', 'Cache', 'Validator', 'HttpClient'];
foreach ($classes as $class) {
    $file = __DIR__ . '/' . $class . '.php';
    if (file_exists($file)) {
        require_once $file;
    }
}