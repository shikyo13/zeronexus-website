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
$recentSyncInterval = 3600; // Sync recent CVEs every hour
$fullSyncRunning = false;

while (true) {
    try {
        $currentTime = time();
        
        // Sync recent CVEs every hour
        if ($currentTime - $lastRecentSync > $recentSyncInterval) {
            echo "[" . date('Y-m-d H:i:s') . "] Syncing recent CVEs...\n";
            $recentCount = $service->sync('recent');
            echo "[" . date('Y-m-d H:i:s') . "] Synced $recentCount recent CVEs\n";
            $lastRecentSync = $currentTime;
        }
        
        // Continue full sync if not completed
        $fullProgress = $service->getSyncProgress('full');
        if (!$fullProgress || $fullProgress['status'] !== 'completed') {
            echo "[" . date('Y-m-d H:i:s') . "] Continuing full historical sync...\n";
            $fullCount = $service->sync('continue');
            echo "[" . date('Y-m-d H:i:s') . "] Synced $fullCount historical CVEs\n";
        }
        
        // Also sync CISA KEV data periodically
        $cisaProgress = $service->getSyncProgress('cisa');
        if (!$cisaProgress || (time() - strtotime($cisaProgress['last_sync_date']) > 86400)) {
            echo "[" . date('Y-m-d H:i:s') . "] Syncing CISA Known Exploited Vulnerabilities...\n";
            $cisaCount = $service->sync('cisa');
            echo "[" . date('Y-m-d H:i:s') . "] Synced $cisaCount CISA CVEs\n";
        }
        
        // Get current database stats
        $status = $service->getStatus();
        $dbStats = $status['database_stats'];
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