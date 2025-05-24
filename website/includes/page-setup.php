<?php
/**
 * Page Setup Utility
 * 
 * Provides a convenient function for setting up common page variables
 * and including header/footer files.
 */

/**
 * Sets up page variables and optionally includes header
 * 
 * @param array $config Page configuration options
 * @return array The processed configuration for use in the page
 */
function setupPage($config = []) {
    // Default configuration
    $defaults = [
        'title' => 'ZeroNexus - Adam Hunt',
        'description' => 'Welcome to ZeroNexus, the digital home of Adam Hunt - IT Professional specializing in networking, security, and development.',
        'css' => null,
        'js' => null,
        'js_type' => null,
        'header_title' => 'ZeroNexus',
        'header_subtitle' => null,
        'extra_head' => null,
        'extra_scripts' => null,
        'hide_social_icons' => false,
        'include_header' => true
    ];
    
    // Merge with provided configuration
    $config = array_merge($defaults, $config);
    
    // Extract variables to current scope
    extract($config, EXTR_PREFIX_SAME, 'config');
    
    // Set global variables for use in header/footer
    foreach ($config as $key => $value) {
        $GLOBALS['page_' . $key] = $value;
        // Also set without prefix for compatibility
        if (in_array($key, ['title', 'description', 'css', 'js', 'js_type'])) {
            $GLOBALS['page_' . $key] = $value;
        } else {
            $GLOBALS[$key] = $value;
        }
    }
    
    // Include header if requested
    if ($config['include_header']) {
        include __DIR__ . '/header.php';
    }
    
    return $config;
}

/**
 * Includes the footer file
 */
function includeFooter() {
    include __DIR__ . '/footer.php';
}

/**
 * Helper function to add cache-busting query string
 * 
 * @param string $path The file path
 * @param bool $useTimestamp Whether to use current timestamp (dev) or file mtime (prod)
 * @return string The path with cache-busting query string
 */
function addCacheBuster($path, $useTimestamp = false) {
    if ($useTimestamp) {
        return $path . '?v=' . time();
    }
    
    // Use file modification time for production
    $fullPath = $_SERVER['DOCUMENT_ROOT'] . $path;
    if (file_exists($fullPath)) {
        return $path . '?v=' . filemtime($fullPath);
    }
    
    return $path;
}

/**
 * Helper to set security headers for pages
 */
function setSecurityHeaders() {
    // Only set if not already sent
    if (!headers_sent()) {
        header("X-Content-Type-Options: nosniff");
        header("X-Frame-Options: SAMEORIGIN");
        header("Referrer-Policy: strict-origin-when-cross-origin");
    }
}