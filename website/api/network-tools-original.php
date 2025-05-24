<?php
/**
 * Network Tools API
 * Executes network diagnostics tools and returns results
 */

// Set headers for JSON API first thing
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST');
header('Access-Control-Allow-Headers: Content-Type');

// Ensure proper JSON responses even on errors
error_reporting(E_ALL);
ini_set('display_errors', 0); // Disable HTML error output
ini_set('display_startup_errors', 0);

// Register shutdown function to catch fatal errors
register_shutdown_function(function() {
    $error = error_get_last();
    if ($error !== null && in_array($error['type'], [E_ERROR, E_PARSE, E_CORE_ERROR, E_COMPILE_ERROR])) {
        http_response_code(500);
        echo json_encode([
            'error' => 'Fatal server error: ' . $error['message'],
            'file' => basename($error['file']),
            'line' => $error['line'],
            'output' => 'Server error occurred. Please try again later.'
        ]);
        exit;
    }
});

// Debug mode
$debug = true;

// Custom error handler to ensure JSON response
function handleError($errno, $errstr, $errfile, $errline) {
    // Only handle errors that aren't caught by @ error suppression
    if (!(error_reporting() & $errno)) {
        return false;
    }
    
    http_response_code(500);
    echo json_encode([
        'error' => 'Server error: ' . $errstr,
        'file' => basename($errfile),
        'line' => $errline,
        'type' => 'php_error',
        'output' => 'Server error occurred. Please contact administrator.'
    ]);
    exit;
}

// Set custom error handler
set_error_handler('handleError');

// Handle unexpected exceptions
try {
    // Import rate limiting functionality
    try {
        require_once 'rate-limit.php';
    } catch (Exception $e) {
        http_response_code(500);
        echo json_encode(['error' => 'Server configuration error']);
        exit;
    }

    // Temporarily disabled rate limiting for debugging
    // try {
    //     // We're using checkRateLimit from rate-limit.php, which throws an exception if rate limit is exceeded
    //     checkRateLimit('network_tools', 10);
    // } catch (Exception $e) {
    //     http_response_code(429);
    //     echo json_encode(['error' => 'Rate limit exceeded. Please try again later.']);
    //     exit;
    // }

    // Log request for debugging
    error_log("Network tools API request: " . json_encode($_GET));

    // Function to sanitize and validate input
    function sanitizeInput($input) {
        return escapeshellarg(trim($input));
    }

    // Validate parameters
    if (!isset($_GET['host']) || empty($_GET['host'])) {
        http_response_code(400);
        echo json_encode(['error' => 'Missing host parameter']);
        exit;
    }

    // Set a time limit for script execution
    set_time_limit(60); // 60 seconds max execution time

    // Get and sanitize parameters
    $host = sanitizeInput($_GET['host']);
    $tool = isset($_GET['tool']) ? $_GET['tool'] : 'ping';
    $packetCount = isset($_GET['packetCount']) ? intval($_GET['packetCount']) : 4;
    $packetSize = isset($_GET['packetSize']) ? intval($_GET['packetSize']) : 56;
    $timeout = isset($_GET['timeout']) ? intval($_GET['timeout']) : 2;

    // Force tool to be ping for now
    $tool = 'ping';

    // Validate parameters
    if (!in_array($tool, ['ping', 'traceroute', 'mtr'])) {
        http_response_code(400);
        echo json_encode(['error' => 'Invalid tool specified. Allowed: ping, traceroute, mtr']);
        exit;
    }

    // Check if we can run the tools with multiple methods
    function findExecutable($command) {
        // Try using which command
        $path = trim(@shell_exec("which $command 2>/dev/null"));
        if (!empty($path) && file_exists($path) && is_executable($path)) {
            return $path;
        }
        
        // Try using command -v (more portable)
        $path = trim(@shell_exec("command -v $command 2>/dev/null"));
        if (!empty($path) && file_exists($path) && is_executable($path)) {
            return $path;
        }
        
        // Define common locations to check for each command
        $commonPaths = [
            "ping" => ["/bin/ping", "/usr/bin/ping", "/sbin/ping", "/usr/sbin/ping"],
            "traceroute" => ["/usr/sbin/traceroute", "/usr/bin/traceroute", "/sbin/traceroute", "/bin/traceroute"],
            "mtr" => ["/usr/sbin/mtr", "/usr/bin/mtr", "/sbin/mtr", "/bin/mtr", "/usr/local/bin/mtr"]
        ];
        
        // Check common paths
        if (isset($commonPaths[$command])) {
            foreach ($commonPaths[$command] as $commonPath) {
                if (file_exists($commonPath) && is_executable($commonPath)) {
                    return $commonPath;
                }
            }
        }
        
        // Not found
        return '';
    }

    // Find tools using our helper function
    $pingPath = findExecutable('ping');
    $traceroutePath = findExecutable('traceroute');
    $mtrPath = findExecutable('mtr');

    // Debug: Add to response
    $toolsAvailable = [
        'ping' => !empty($pingPath),
        'traceroute' => !empty($traceroutePath),
        'mtr' => !empty($mtrPath),
        'ping_path' => $pingPath,
        'traceroute_path' => $traceroutePath,
        'mtr_path' => $mtrPath,
        'php_user' => function_exists('posix_getpwuid') && function_exists('posix_geteuid') ? 
                      (posix_getpwuid(posix_geteuid())['name'] ?? 'unknown') : 'unknown',
        'server_os' => PHP_OS
    ];

    // Log tool detection results
    error_log("Tool detection: " . json_encode($toolsAvailable));

    // Limit parameters to reasonable values
    $packetCount = max(1, min(20, $packetCount));
    $packetSize = max(32, min(1472, $packetSize));
    $timeout = max(1, min(10, $timeout));

    // Special handling for macOS
    $isMacOS = PHP_OS === 'Darwin';

    // Build commands based on tool and platform
    $command = '';
    $skipExecution = false;
    $output = '';

    if ($tool === 'ping') {
        if (empty($pingPath)) {
            $output = "ERROR: Ping command not found. This tool requires the ping command to be installed on the server.";
            $skipExecution = true;
        } else {
            if ($isMacOS) {
                // macOS ping commands
                $command = "{$pingPath} -c {$packetCount} -s {$packetSize} -W {$timeout} {$host} 2>&1";
            } else {
                // Linux ping command
                $command = "{$pingPath} -c {$packetCount} -s {$packetSize} -W {$timeout} {$host} 2>&1";
            }
        }
    } else if ($tool === 'traceroute') {
        // Only ping is supported for now
        $output = "ERROR: Traceroute is not currently supported. Please use ping.";
        $skipExecution = true;
    } else if ($tool === 'mtr') {
        // Only ping is supported for now
        $output = "ERROR: MTR is not currently supported. Please use ping.";
        $skipExecution = true;
    }

    // Log the command we're about to execute
    error_log("About to execute: $command");

    // Add debug information
    if ($debug) {
        $debugInfo = [
            'command' => $command,
            'host' => $host,
            'tool' => $tool
        ];
    }

    // Skip command execution if we're using sample data or don't have tools
    if (!isset($skipExecution) || $skipExecution !== true) {
        // Try different methods to execute the command
        $output = '';
        $return_var = 0;
        
        // Add execution method to debug info
        $executionMethod = 'none';
        
        try {
            // Method 1: Use exec (basic but reliable)
            $execOutput = [];
            @exec($command, $execOutput, $return_var);
            
            if ($return_var !== 127) { // Not a "command not found" error
                $output = implode("\n", $execOutput);
                $executionMethod = 'exec';
                error_log("exec method returned code: $return_var");
            } else {
                error_log("exec failed with 'command not found' error");
                
                // Method 2: Try shell_exec
                $shellOutput = @shell_exec($command);
                
                if ($shellOutput !== null) {
                    $output = $shellOutput;
                    $executionMethod = 'shell_exec';
                    error_log("shell_exec method succeeded");
                } else {
                    error_log("shell_exec failed");
                    
                    // Method 3: Try proc_open
                    $descriptorspec = [
                        1 => ['pipe', 'w'], // stdout
                        2 => ['pipe', 'w']  // stderr
                    ];
                    
                    $process = @proc_open($command, $descriptorspec, $pipes);
                    
                    if (is_resource($process)) {
                        $executionMethod = 'proc_open';
                        error_log("proc_open succeeded");
                        
                        // Read from pipes
                        $stdout = stream_get_contents($pipes[1]);
                        $stderr = stream_get_contents($pipes[2]);
                        
                        // Close pipes and process
                        fclose($pipes[1]);
                        fclose($pipes[2]);
                        $return_var = proc_close($process);
                        
                        error_log("proc_open returned: $return_var");
                        
                        // Use stdout or stderr based on return code
                        if (!empty($stdout)) {
                            $output = $stdout;
                        } else if (!empty($stderr)) {
                            $output = $stderr;
                        } else {
                            $output = "ERROR: Command execution failed.";
                        }
                    } else {
                        // All methods failed
                        error_log("All command execution methods failed");
                        $output = "ERROR: Could not execute command. The server might not have permissions to run this command.";
                        
                        // Provide more diagnostic information
                        $output .= "\nPlease ensure the required network tools are installed on the server.";
                        if ($tool === 'traceroute') {
                            $output .= "\nInstall traceroute with: sudo apt-get install traceroute (Debian/Ubuntu) or sudo yum install traceroute (CentOS/RHEL)";
                        } else if ($tool === 'mtr') {
                            $output .= "\nInstall MTR with: sudo apt-get install mtr-tiny (Debian/Ubuntu) or sudo yum install mtr (CentOS/RHEL)";
                        }
                    }
                }
            }
        } catch (Exception $innerException) {
            error_log("Exception during command execution: " . $innerException->getMessage());
            $output = "ERROR: Exception during command execution: " . $innerException->getMessage();
            $executionMethod = 'exception';
        }
    }

    // Check if the output contains any data
    if (empty($output) && !isset($skipExecution)) {
        $output = "ERROR: Command execution returned no output. The tool may not be installed or might not have the required permissions.";
        error_log("No output from command execution: $command");
    }

    // Return the results
    $response = [
        'tool' => $tool,
        'host' => $_GET['host'], // Return the original host for display
        'output' => $output
    ];

    // Add debug info if enabled
    if ($debug) {
        $response['debug'] = $debugInfo ?? [];
        $response['raw_command'] = $command ?? '';
        $response['return_code'] = $return_var ?? 0;
        $response['tools_available'] = $toolsAvailable;
        $response['execution_method'] = $executionMethod ?? 'none';
    }

    // Send response
    echo json_encode($response);

} catch (Exception $e) {
    // Catch any unexpected exceptions and return a proper JSON error
    http_response_code(500);
    echo json_encode([
        'error' => 'Unexpected error: ' . $e->getMessage(),
        'type' => 'exception',
        'file' => basename($e->getFile()),
        'line' => $e->getLine(),
        'output' => 'An unexpected error occurred. Please try again later.'
    ]);
    exit;
}