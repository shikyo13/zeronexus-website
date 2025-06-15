<?php
/**
 * HaveIBeenPwned API Proxy
 * Handles CORS and provides secure password breach checking
 */

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST');
header('Access-Control-Allow-Headers: Content-Type');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['error' => 'Method not allowed']);
    exit();
}

// Get JSON input
$input = json_decode(file_get_contents('php://input'), true);

if (!isset($input['prefix']) || strlen($input['prefix']) !== 5) {
    http_response_code(400);
    echo json_encode(['error' => 'Invalid hash prefix']);
    exit();
}

$prefix = strtoupper($input['prefix']);

// Validate prefix is hexadecimal
if (!ctype_xdigit($prefix)) {
    http_response_code(400);
    echo json_encode(['error' => 'Hash prefix must be hexadecimal']);
    exit();
}

try {
    // Make request to HaveIBeenPwned API
    $url = "https://api.pwnedpasswords.com/range/" . $prefix;
    
    $context = stream_context_create([
        'http' => [
            'method' => 'GET',
            'header' => [
                'User-Agent: Password-Tool-Proxy/1.0',
                'Accept: text/plain'
            ],
            'timeout' => 10
        ]
    ]);
    
    $response = file_get_contents($url, false, $context);
    
    if ($response === false) {
        throw new Exception('Failed to fetch from HaveIBeenPwned API');
    }
    
    // Return the response
    echo json_encode([
        'success' => true,
        'data' => $response
    ]);
    
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'error' => 'API request failed: ' . $e->getMessage()
    ]);
}
?>