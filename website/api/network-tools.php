<?php
/**
 * Network Tools API (Refactored)
 * 
 * Executes network diagnostics tools and returns results
 * Using modular utilities for better code organization
 * 
 * Parameters:
 * - host: Domain name or IP address to test
 * - tool: Tool to use (ping, traceroute, mtr) - currently only ping supported
 * - packetCount: Number of packets (default: 4)
 * - packetSize: Size of packets in bytes (default: 56)
 * - timeout: Timeout per packet in seconds (default: 2)
 */

// Load shared utilities
require_once __DIR__ . '/lib/autoload.php';

// Initialize utilities
$config = Config::getInstance();
$response = new Response();

// Handle CORS - this is a public utility tool, so allow all origins
CORS::simple(true); // true = allow all origins for public utility

// Apply rate limiting (10 requests per minute for network tools)
RateLimit::simple('network-tools');

// Validate input
$validator = new Validator();
$validator->required('host', 'Host parameter is required')
          ->length('host', 1, 255, 'Host must be between 1 and 255 characters');

// Validate host is either domain or IP
$host = $validator->get('host');
if ($host && !filter_var($host, FILTER_VALIDATE_IP)) {
    $validator->domain('host', 'Host must be a valid domain name or IP address');
}

// Validate other parameters
$validator->in('tool', ['ping', 'traceroute', 'mtr'], 'Invalid tool specified')
          ->integer('packetCount', 1, 10, 'Packet count must be between 1 and 10')
          ->integer('packetSize', 8, 1472, 'Packet size must be between 8 and 1472')
          ->integer('timeout', 1, 10, 'Timeout must be between 1 and 10 seconds');

if ($validator->fails()) {
    $response->validationError($validator->errors());
}

// Get parameters with defaults
$host = $validator->get('host');
$tool = $validator->get('tool', 'ping');
$packetCount = (int)$validator->get('packetCount', 4);
$packetSize = (int)$validator->get('packetSize', 56);
$timeout = (int)$validator->get('timeout', 2);

// Validate tool selection
if ($tool !== 'ping') {
    $response->error("Invalid tool. Only 'ping' is currently supported.", 400);
}

// Set execution time limit
set_time_limit(60);

// Detect OS
$isMacOS = PHP_OS === 'Darwin';

// Find ping command path
$pingPath = '';
$possiblePaths = ['/bin/ping', '/usr/bin/ping', '/sbin/ping'];
foreach ($possiblePaths as $path) {
    if (file_exists($path) && is_executable($path)) {
        $pingPath = $path;
        break;
    }
}

if (empty($pingPath)) {
    // Try using 'which' command
    $pingPath = trim(shell_exec('which ping 2>/dev/null'));
}

if (empty($pingPath)) {
    $response->error('Ping command not found on server', 500);
}

// Prepare command
// Note: Input is sanitized using escapeshellarg for security
$safeHost = escapeshellarg($host);
$command = "{$pingPath} -c {$packetCount} -s {$packetSize} -W {$timeout} {$safeHost} 2>&1";

// Execute command
$output = '';
$return_var = 0;

try {
    // Execute the command
    exec($command, $outputLines, $return_var);
    $output = implode("\n", $outputLines);
    
    if ($return_var !== 0 && empty($output)) {
        throw new Exception('Command execution failed');
    }
} catch (Exception $e) {
    $response->error('Failed to execute network diagnostic command', 500, [
        'message' => $config->isDebug() ? $e->getMessage() : 'Execution failed'
    ]);
}

// Parse the output
$stats = [];
if (preg_match('/(\d+) packets transmitted, (\d+) (packets )?received/', $output, $matches)) {
    $stats['packets_transmitted'] = intval($matches[1]);
    $stats['packets_received'] = intval($matches[2]);
    $stats['packet_loss'] = round((1 - ($matches[2] / $matches[1])) * 100, 2);
}

if (preg_match('/min\/avg\/max\/stddev = ([\d.]+)\/([\d.]+)\/([\d.]+)\/([\d.]+)/', $output, $matches)) {
    $stats['rtt_min'] = floatval($matches[1]);
    $stats['rtt_avg'] = floatval($matches[2]);
    $stats['rtt_max'] = floatval($matches[3]);
    $stats['rtt_stddev'] = floatval($matches[4]);
}

// Prepare response
$result = [
    'tool' => $tool,
    'host' => $host,
    'parameters' => [
        'packet_count' => $packetCount,
        'packet_size' => $packetSize,
        'timeout' => $timeout
    ],
    'output' => $output,
    'stats' => $stats,
    'success' => $return_var === 0,
    'timestamp' => time()
];

// Add debug info if in debug mode
if ($config->isDebug()) {
    $result['debug'] = [
        'command' => $command,
        'return_code' => $return_var,
        'os' => PHP_OS,
        'ping_path' => $pingPath
    ];
}

// Send response
$response->success($result);