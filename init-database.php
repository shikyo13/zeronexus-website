<?php
/**
 * Database Initialization Script
 * 
 * This script initializes the CVE database with some sample data for testing.
 * In production, this would be replaced by the full sync service.
 */

require_once __DIR__ . '/website/api/sync-cves.php';

echo "Initializing CVE Database...\n";

try {
    $service = new CVESyncService();
    
    echo "Starting recent CVE sync (last 30 days)...\n";
    $result = $service->sync('recent');
    
    echo "Recent sync completed. Synced $result CVEs.\n";
    
    echo "Starting CISA sync...\n";
    $cisaResult = $service->sync('cisa');
    
    echo "CISA sync completed. Synced $cisaResult CVEs.\n";
    
    // Get status
    $status = $service->getStatus();
    echo "\nDatabase Status:\n";
    echo "Total CVEs: " . ($status['database_stats']['total_cves'] ?? 0) . "\n";
    echo "CISA Exploited: " . ($status['database_stats']['cisa_exploited'] ?? 0) . "\n";
    echo "Date range: " . ($status['database_stats']['oldest_cve'] ?? 'N/A') . " to " . ($status['database_stats']['newest_cve'] ?? 'N/A') . "\n";
    
    echo "\nDatabase initialization completed successfully!\n";
    
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
    exit(1);
}