<?php
/**
 * Security Feeds API
 * 
 * Provides paginated security news from multiple sources.
 * 
 * Parameters:
 * - page: The page number to retrieve (default: 1)
 * - limit: Number of items per page (default: 30)
 * - source: Filter by source (optional)
 */

// Set security headers
header('Content-Type: application/json');
header('X-Content-Type-Options: nosniff');
header('X-Frame-Options: DENY');
header('Referrer-Policy: strict-origin-when-cross-origin');
header('X-XSS-Protection: 1; mode=block');

// Enable CORS for specific origins, with Cloudflare support
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

// Rate limiting based on IP with Cloudflare support
function checkRateLimit() {
    // Get the real client IP using Cloudflare's headers if available
    $ip = isset($_SERVER['HTTP_CF_CONNECTING_IP']) ? $_SERVER['HTTP_CF_CONNECTING_IP'] : $_SERVER['REMOTE_ADDR'];
    $rateLimitFile = sys_get_temp_dir() . '/zeronexus_rate_' . md5($ip);
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
            
            // If more than 60 requests in a minute, rate limit
            if ($data['count'] > 60) {
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

// Apply rate limiting
checkRateLimit();

// Get and validate request parameters
$page = isset($_GET['page']) ? max(1, intval($_GET['page'])) : 1;
$limit = isset($_GET['limit']) ? min(50, max(1, intval($_GET['limit']))) : 30;

// Validate source parameter against allowed values
$allowedSources = ['all', 'bleepingcomputer', 'krebsonsecurity', 'thehackernews'];
$source = isset($_GET['source']) ? $_GET['source'] : null;

if ($source !== null && !in_array($source, $allowedSources)) {
    // Invalid source parameter, return error
    http_response_code(400);
    echo json_encode(['error' => true, 'message' => 'Invalid source parameter']);
    exit;
}

// Calculate offset for pagination
$offset = ($page - 1) * $limit;

// Sample data (in a real implementation, this would come from a database or RSS feed parsing)
$articles = getMockArticles();

// Filter by source if provided
if ($source && $source !== 'all') {
    $articles = array_filter($articles, function($article) use ($source) {
        return $article['source'] === $source;
    });
}

// Paginate results
$paginatedArticles = array_slice($articles, $offset, $limit);

// Return the results
echo json_encode($paginatedArticles);

/**
 * Returns mock article data for testing
 */
function getMockArticles() {
    $sources = ['bleepingcomputer', 'krebsonsecurity', 'thehackernews'];
    $articles = [];
    
    // Generate 100 mock articles across different sources
    for ($i = 1; $i <= 100; $i++) {
        $sourceIndex = ($i % 3);
        $source = $sources[$sourceIndex];
        $date = date('Y-m-d\TH:i:s\Z', time() - rand(0, 30 * 24 * 60 * 60));
        
        $articles[] = [
            'id' => 'article' . $i,
            'source' => $source,
            'date' => $date,
            'title' => getArticleTitle($source, $i),
            'description' => getArticleDescription($source, $i),
            'link' => getArticleLink($source, $i),
            'thumbnail' => ($i % 4 === 0) ? '' : 'https://via.placeholder.com/150x100',
        ];
    }
    
    // Sort by date (newest first)
    usort($articles, function($a, $b) {
        return strcmp($b['date'], $a['date']);
    });
    
    return $articles;
}

/**
 * Generate realistic article titles based on source
 */
function getArticleTitle($source, $index) {
    $titles = [
        'bleepingcomputer' => [
            'New Ransomware Variant Targets Enterprise Networks',
            'Critical Windows Vulnerability Patched in Latest Update',
            'Hackers Exploit Zero-Day Vulnerability in Popular CMS',
            'Data Breach Exposes Millions of Customer Records',
            'New Phishing Campaign Impersonates Major Tech Companies'
        ],
        'krebsonsecurity' => [
            'Following the Money: Tracing Ransomware Payments',
            'Inside the Criminal Ecosystem of Credential Theft',
            'Major Financial Institution Suffers Sophisticated Attack',
            'The Growing Threat of Supply Chain Attacks',
            'Interview with a Reformed Cybercriminal'
        ],
        'thehackernews' => [
            'APT Group Targets Critical Infrastructure in New Campaign',
            'Researchers Discover Backdoor in Open-Source Library',
            'New Android Malware Steals Banking Credentials',
            'Cloud Misconfiguration Leads to Massive Data Leak',
            'Government Agencies Issue Joint Security Advisory'
        ]
    ];
    
    $titleIndex = $index % 5;
    return $titles[$source][$titleIndex] . ' #' . $index;
}

/**
 * Generate article descriptions
 */
function getArticleDescription($source, $index) {
    $descriptions = [
        'bleepingcomputer' => 'Security researchers have discovered a new threat targeting enterprise networks. The attack vector involves sophisticated techniques to evade detection and compromise systems across organizations.',
        'krebsonsecurity' => 'An investigation into recent cyberattacks reveals concerning trends in how criminal groups are operating and monetizing their activities. This analysis provides insights into the evolving threat landscape.',
        'thehackernews' => 'A comprehensive analysis of a newly discovered vulnerability reveals significant risks to organizations worldwide. Security teams are advised to implement mitigations immediately.'
    ];
    
    return $descriptions[$source] . ' This is article #' . $index . ' from the feed.';
}

/**
 * Generate article links
 */
function getArticleLink($source, $index) {
    $domains = [
        'bleepingcomputer' => 'https://www.bleepingcomputer.com/news/security/',
        'krebsonsecurity' => 'https://krebsonsecurity.com/posts/',
        'thehackernews' => 'https://thehackernews.com/articles/'
    ];
    
    return $domains[$source] . 'article-' . $index;
}