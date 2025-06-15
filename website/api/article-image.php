<?php
/**
 * Article Image Extraction API
 * 
 * This endpoint extracts featured images from article URLs to provide thumbnails
 * for sources that don't include them in their RSS feeds (like BleepingComputer).
 * 
 * Parameters:
 * - url: The article URL to extract images from
 * - source: The source name (optional, for specialized extraction logic)
 */

// Set security headers
header('Content-Type: application/json');
header('X-Content-Type-Options: nosniff');
header('X-Frame-Options: DENY');
header('Referrer-Policy: strict-origin-when-cross-origin');
header('X-XSS-Protection: 1; mode=block');

// Enable CORS for specific origins
$allowedOrigins = [
    'https://zeronexus.net',
    'https://www.zeronexus.net',
    'http://localhost:8081' // For local development
];

$origin = isset($_SERVER['HTTP_ORIGIN']) ? $_SERVER['HTTP_ORIGIN'] : '';

// Check if origin is allowed or is a subdomain of zeronexus.net
$isAllowed = in_array($origin, $allowedOrigins);
if (!$isAllowed && preg_match('/^https?:\/\/.*\.zeronexus\.net(:[0-9]+)?$/', $origin)) {
    $isAllowed = true;
}

if ($isAllowed) {
    header("Access-Control-Allow-Origin: $origin");
    header('Access-Control-Allow-Methods: GET, OPTIONS');
    header('Access-Control-Allow-Headers: Content-Type, CF-Connecting-IP, CF-IPCountry, CF-Ray, CF-Visitor, X-Forwarded-For, X-Forwarded-Proto');
    header('Vary: Origin');
}

// Exit on OPTIONS request (preflight)
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    exit(0);
}

// Basic input validation
if (!isset($_GET['url']) || empty($_GET['url'])) {
    http_response_code(400);
    echo json_encode(['error' => true, 'message' => 'Missing required parameter: url']);
    exit;
}

// Get parameters
$url = $_GET['url'];
$source = isset($_GET['source']) ? $_GET['source'] : null;

// Validate URL format
if (!filter_var($url, FILTER_VALIDATE_URL)) {
    http_response_code(400);
    echo json_encode(['error' => true, 'message' => 'Invalid URL format']);
    exit;
}

// Domain whitelist for security
$allowedDomains = [
    'bleepingcomputer.com',
    'www.bleepingcomputer.com',
    'krebsonsecurity.com',
    'thehackernews.com',
    'www.thehackernews.com'
];

// Check if URL is from an allowed domain
$urlDomain = parse_url($url, PHP_URL_HOST);
$isAllowedDomain = false;

foreach ($allowedDomains as $domain) {
    if ($urlDomain === $domain || preg_match('/\.' . preg_quote($domain, '/') . '$/', $urlDomain)) {
        $isAllowedDomain = true;
        break;
    }
}

if (!$isAllowedDomain) {
    http_response_code(403);
    echo json_encode(['error' => true, 'message' => 'Domain not allowed']);
    exit;
}

// Caching logic
$cacheDir = sys_get_temp_dir() . '/zeronexus_image_cache/';
try {
    if (!is_dir($cacheDir)) {
        mkdir($cacheDir, 0755, true);
    }

    $cacheKey = md5($url);
    $cacheFile = $cacheDir . $cacheKey . '.json';
    $cacheLifetime = 3600 * 24; // 24 hours

    // Check cache
    if (file_exists($cacheFile) && (time() - filemtime($cacheFile) < $cacheLifetime)) {
        $cachedData = json_decode(file_get_contents($cacheFile), true);

        // Return cached result
        header('X-Cache: HIT');
        echo json_encode($cachedData);
        exit;
    }
} catch (Exception $e) {
    // If caching fails, continue without it
    error_log('Caching error in article-image.php: ' . $e->getMessage());
}

// Function to fetch and extract image URL
function extractImageUrl($url, $source) {
    try {
        // Initialize cURL
        $ch = curl_init();

        if (!$ch) {
            return null;
        }

        // Set cURL options
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
        curl_setopt($ch, CURLOPT_TIMEOUT, 10);
        curl_setopt($ch, CURLOPT_USERAGENT, 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/91.0.4472.124 Safari/537.36');
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false); // For development only

        // Execute cURL session
        $html = curl_exec($ch);

        // Check for errors
        if (curl_errno($ch)) {
            curl_close($ch);
            return null;
        }

        $statusCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        if ($statusCode !== 200) {
            return null;
        }
    } catch (Exception $e) {
        return null;
    }
    
    // Source-specific extraction logic
    if ($source === 'bleepingcomputer' || stripos($url, 'bleepingcomputer.com') !== false) {
        // Extract meta og:image for BleepingComputer
        if (preg_match('/<meta\s+property="og:image"\s+content="([^"]+)"/i', $html, $matches)) {
            return $matches[1];
        }
        
        // Fallback to article featured image
        if (preg_match('/<img[^>]+class="[^"]*article_featured[^"]*"[^>]+src="([^"]+)"/i', $html, $matches)) {
            return $matches[1];
        }
    } elseif ($source === 'krebsonsecurity' || stripos($url, 'krebsonsecurity.com') !== false) {
        // Extract first image from Krebs
        if (preg_match('/<img[^>]+src="([^"]+)"/i', $html, $matches)) {
            return $matches[1];
        }
    } elseif ($source === 'thehackernews' || stripos($url, 'thehackernews.com') !== false) {
        // Extract home-img for THN
        if (preg_match('/<img[^>]+class="[^"]*home-img[^"]*"[^>]+src="([^"]+)"/i', $html, $matches)) {
            return $matches[1];
        }
        
        // Fallback to og:image
        if (preg_match('/<meta\s+property="og:image"\s+content="([^"]+)"/i', $html, $matches)) {
            return $matches[1];
        }
    }
    
    // Generic fallback - try to get any og:image
    if (preg_match('/<meta\s+property="og:image"\s+content="([^"]+)"/i', $html, $matches)) {
        return $matches[1];
    }
    
    // Final fallback - try to find the first image with reasonable dimensions
    if (preg_match_all('/<img[^>]+src="([^"]+)"[^>]*>/i', $html, $matches)) {
        foreach ($matches[0] as $i => $imgTag) {
            // Check for width/height attributes
            if (preg_match('/width="(\d+)"/i', $imgTag, $width) && 
                preg_match('/height="(\d+)"/i', $imgTag, $height)) {
                
                // Only return images with reasonable dimensions (avoid icons)
                if (intval($width[1]) >= 200 && intval($height[1]) >= 100) {
                    return $matches[1][$i];
                }
            }
        }
        
        // If we couldn't find an image with dimensions, return the first one
        return $matches[1][0];
    }
    
    return null;
}

// Extract image URL
$imageUrl = extractImageUrl($url, $source);

// Prepare response
$response = [
    'url' => $url,
    'image' => $imageUrl,
    'source' => $source,
    'timestamp' => time()
];

// Try to cache the result
try {
    if (isset($cacheFile)) {
        file_put_contents($cacheFile, json_encode($response));
    }
} catch (Exception $e) {
    // If caching fails, log the error but continue
    error_log('Cache writing error in article-image.php: ' . $e->getMessage());
}

// Send response
header('X-Cache: MISS');
echo json_encode($response);