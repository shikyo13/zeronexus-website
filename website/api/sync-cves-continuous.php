<?php
/**
 * Continuous CVE Sync Service
 * 
 * This script runs continuously, syncing CVEs while respecting rate limits.
 * It can be run as a background process or via supervisor/systemd.
 */

require_once __DIR__ . '/sync-cves.php';

// Set time limit to indefinite for continuous running
set_time_limit(0);

// Ensure script continues even if connection is closed
ignore_user_abort(true);

$service = new CVESyncService();

echo "Starting continuous CVE sync service...\n";
echo "Press Ctrl+C to stop\n\n";

// Track sync state
$lastRecentSync = 0;
$lastCisaSync = 0;
$lastFullSync = 0;
$recentSyncInterval = 3600; // Sync recent CVEs every hour
$cisaSyncInterval = 86400; // Sync CISA KEV every 24 hours
$fullSyncInterval = 300; // Try full sync every 5 minutes if needed

// Track if we've completed a full sync
$fullSyncCompleted = false;

while (true) {
    try {
        $currentTime = time();
        
        // Get current database stats
        $status = $service->getStatus();
        $dbStats = $status['database_stats'];
        
        // Sync recent CVEs every hour
        if ($currentTime - $lastRecentSync > $recentSyncInterval) {
            echo "[" . date('Y-m-d H:i:s') . "] Syncing recent CVEs...\n";
            $recentCount = $service->sync('recent');
            echo "[" . date('Y-m-d H:i:s') . "] Synced $recentCount recent CVEs\n";
            $lastRecentSync = $currentTime;
        }
        
        // If we have very few CVEs, attempt a full historical sync
        // The full sync will handle its own pagination and rate limiting
        if (!$fullSyncCompleted && $dbStats['total_cves'] < 300000 && ($currentTime - $lastFullSync > $fullSyncInterval)) {
            echo "[" . date('Y-m-d H:i:s') . "] Starting/continuing full historical sync (current: {$dbStats['total_cves']} CVEs)...\n";
            
            try {
                $fullCount = $service->sync('full');
                echo "[" . date('Y-m-d H:i:s') . "] Full sync batch completed. Synced $fullCount CVEs in this batch\n";
                
                // Check if we've reached a reasonable number of CVEs
                $newStatus = $service->getStatus();
                if ($newStatus['database_stats']['total_cves'] > 300000) {
                    $fullSyncCompleted = true;
                    echo "[" . date('Y-m-d H:i:s') . "] Full sync appears to be complete with {$newStatus['database_stats']['total_cves']} total CVEs\n";
                }
            } catch (Exception $e) {
                echo "[" . date('Y-m-d H:i:s') . "] Full sync error: " . $e->getMessage() . "\n";
                // Don't update lastFullSync on error, so we retry sooner
            }
            
            $lastFullSync = $currentTime;
        }
        
        // Sync CISA KEV data periodically
        if ($currentTime - $lastCisaSync > $cisaSyncInterval) {
            echo "[" . date('Y-m-d H:i:s') . "] Syncing CISA Known Exploited Vulnerabilities...\n";
            try {
                $cisaCount = $service->sync('cisa');
                echo "[" . date('Y-m-d H:i:s') . "] Synced $cisaCount CISA CVEs\n";
                $lastCisaSync = $currentTime;
            } catch (Exception $e) {
                echo "[" . date('Y-m-d H:i:s') . "] CISA sync error: " . $e->getMessage() . "\n";
            }
        }
        
        // Display current database stats
        echo "[" . date('Y-m-d H:i:s') . "] Database stats: " . 
             $dbStats['total_cves'] . " total CVEs, " .
             "Date range: " . $dbStats['oldest_cve'] . " to " . $dbStats['newest_cve'] . "\n";
        
        // Sleep for a bit before next check
        echo "[" . date('Y-m-d H:i:s') . "] Sleeping for 5 minutes...\n\n";
        sleep(300); // 5 minutes
        
    } catch (Exception $e) {
        echo "[" . date('Y-m-d H:i:s') . "] Error: " . $e->getMessage() . "\n";
        echo "[" . date('Y-m-d H:i:s') . "] Retrying in 1 minute...\n\n";
        sleep(60);
    }
}