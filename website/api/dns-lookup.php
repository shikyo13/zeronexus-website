<?php
/**
 * DNS Lookup API (Refactored)
 * 
 * Performs DNS queries and returns structured results
 * Using modular utilities for better code organization
 * 
 * Parameters:
 * - host: Domain name to look up
 * - type: Record type (A, AAAA, MX, TXT, NS, CNAME, SOA, PTR, CAA, ANY)
 */

// Load shared utilities
require_once __DIR__ . '/lib/autoload.php';

// Initialize utilities
$config = Config::getInstance();
$response = new Response();
$cache = new Cache('dns-lookup');

// Handle CORS
CORS::simple(false);

// Apply rate limiting (60 requests per minute for DNS lookups)
RateLimit::simple('dns-lookup');

// Validate input
$validator = new Validator();
$validator->required('host', 'Host parameter is required');

// Validate host is either domain or IP
$host = $validator->get('host');
if ($host) {
    // Remove protocol if present
    $host = preg_replace('#^https?://#', '', $host);
    $host = trim($host, '/');
    
    // Check if it's an IP or domain
    if (!filter_var($host, FILTER_VALIDATE_IP)) {
        $validator->domain('host', 'Host must be a valid domain name or IP address');
    }
}

// Validate record type
$validTypes = ['A', 'AAAA', 'MX', 'TXT', 'NS', 'CNAME', 'SOA', 'PTR', 'CAA', 'ANY'];
$validator->in('type', $validTypes, 'Invalid DNS record type');

if ($validator->fails()) {
    $response->validationError($validator->errors());
}

$host = $host ?: $validator->get('host');
$type = strtoupper($validator->get('type', 'A'));

// Try to get from cache
$cacheKey = 'dns_' . md5($host . '_' . $type);
$cacheTTL = 300; // 5 minutes cache for DNS

$result = $cache->remember($cacheKey, function() use ($host, $type, $config) {
    $records = [];
    $error = null;
    
    try {
        // Map record types to PHP constants
        $typeMap = [
            'A' => DNS_A,
            'AAAA' => DNS_AAAA,
            'MX' => DNS_MX,
            'TXT' => DNS_TXT,
            'NS' => DNS_NS,
            'CNAME' => DNS_CNAME,
            'SOA' => DNS_SOA,
            'PTR' => DNS_PTR,
            'CAA' => DNS_CAA,
            'ANY' => DNS_ANY
        ];
        
        $dnsType = $typeMap[$type] ?? DNS_A;
        
        // Perform DNS lookup
        if ($type === 'ANY') {
            // For ANY, we'll query multiple types
            $allRecords = [];
            foreach (['A', 'AAAA', 'MX', 'TXT', 'NS', 'CNAME', 'SOA'] as $singleType) {
                $singleDnsType = $typeMap[$singleType];
                $result = @dns_get_record($host, $singleDnsType);
                if ($result) {
                    $allRecords = array_merge($allRecords, $result);
                }
            }
            $rawRecords = $allRecords;
        } else {
            $rawRecords = @dns_get_record($host, $dnsType);
        }
        
        if ($rawRecords === false) {
            throw new Exception("DNS lookup failed for $host");
        }
        
        // Format records based on type
        foreach ($rawRecords as $record) {
            $formattedRecord = [
                'type' => $record['type'],
                'ttl' => $record['ttl'] ?? null
            ];
            
            switch ($record['type']) {
                case 'A':
                    $formattedRecord['ip'] = $record['ip'];
                    break;
                    
                case 'AAAA':
                    $formattedRecord['ipv6'] = $record['ipv6'];
                    break;
                    
                case 'MX':
                    $formattedRecord['priority'] = $record['pri'];
                    $formattedRecord['target'] = $record['target'];
                    break;
                    
                case 'TXT':
                    $formattedRecord['txt'] = $record['txt'];
                    break;
                    
                case 'NS':
                    $formattedRecord['target'] = $record['target'];
                    break;
                    
                case 'CNAME':
                    $formattedRecord['target'] = $record['target'];
                    break;
                    
                case 'SOA':
                    $formattedRecord['mname'] = $record['mname'];
                    $formattedRecord['rname'] = $record['rname'];
                    $formattedRecord['serial'] = $record['serial'];
                    $formattedRecord['refresh'] = $record['refresh'];
                    $formattedRecord['retry'] = $record['retry'];
                    $formattedRecord['expire'] = $record['expire'];
                    $formattedRecord['minimum'] = $record['minimum-ttl'];
                    break;
                    
                case 'PTR':
                    $formattedRecord['target'] = $record['target'];
                    break;
                    
                case 'CAA':
                    $formattedRecord['flags'] = $record['flags'];
                    $formattedRecord['tag'] = $record['tag'];
                    $formattedRecord['value'] = $record['value'];
                    break;
            }
            
            $records[] = $formattedRecord;
        }
        
        // Sort records by type and then by specific fields
        usort($records, function($a, $b) {
            if ($a['type'] !== $b['type']) {
                return strcmp($a['type'], $b['type']);
            }
            
            // Sort MX by priority
            if ($a['type'] === 'MX') {
                return $a['priority'] - $b['priority'];
            }
            
            return 0;
        });
        
    } catch (Exception $e) {
        $error = $e->getMessage();
    }
    
    // Additional info
    $additionalInfo = [];
    
    // Try to get authoritative nameservers
    try {
        $nsRecords = @dns_get_record($host, DNS_NS);
        if ($nsRecords) {
            $additionalInfo['nameservers'] = array_map(function($r) {
                return $r['target'];
            }, $nsRecords);
        }
    } catch (Exception $e) {
        // Ignore errors for additional info
    }
    
    // Get reverse DNS for A records
    if ($type === 'A' || $type === 'ANY') {
        foreach ($records as &$record) {
            if ($record['type'] === 'A' && isset($record['ip'])) {
                $reverse = @gethostbyaddr($record['ip']);
                if ($reverse && $reverse !== $record['ip']) {
                    $record['reverse'] = $reverse;
                }
            }
        }
    }
    
    return [
        'host' => $host,
        'type' => $type,
        'records' => $records,
        'record_count' => count($records),
        'query_time' => date('Y-m-d H:i:s'),
        'additional_info' => $additionalInfo,
        'error' => $error
    ];
}, $cacheTTL);

// Send response
if ($result['error']) {
    $response->error($result['error'], 400);
} else {
    $response->success($result);
}