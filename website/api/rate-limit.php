<?php
/**
 * Rate Limiting Helper
 * 
 * Common rate limiting functionality for the API endpoints.
 */

/**
 * Check rate limit based on IP with Cloudflare support
 * 
 * @param string $endpoint The API endpoint name for separate rate limiting
 * @param int $maxRequests Maximum requests per minute (default: 20)
 * @throws Exception if rate limit is exceeded
 */
function checkRateLimit($endpoint = 'default', $maxRequests = 20) {
    // Get the real client IP using Cloudflare's headers if available
    $ip = isset($_SERVER['HTTP_CF_CONNECTING_IP']) ? $_SERVER['HTTP_CF_CONNECTING_IP'] : $_SERVER['REMOTE_ADDR'];
    $rateLimitFile = sys_get_temp_dir() . '/zeronexus_' . $endpoint . '_rate_' . md5($ip);
    $currentTime = time();
    
    // Check if file exists and read it
    if (file_exists($rateLimitFile)) {
        $data = json_decode(file_get_contents($rateLimitFile), true);
        
        // Reset counter if more than 1 minute has passed
        if ($currentTime - $data['timestamp'] > 60) {
            $data = [
                'count' => 1,
                'timestamp' => $currentTime
            ];
        } else {
            $data['count']++;
            
            // If more than max requests in a minute, rate limit
            if ($data['count'] > $maxRequests) {
                http_response_code(429);
                echo json_encode(['error' => true, 'message' => 'Too many requests. Please try again later.']);
                exit;
            }
        }
    } else {
        $data = [
            'count' => 1,
            'timestamp' => $currentTime
        ];
    }
    
    // Write updated data
    file_put_contents($rateLimitFile, json_encode($data));
}