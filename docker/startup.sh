#!/bin/sh
# Startup script for PHP container to ensure CVE continuous sync runs

echo "Starting PHP-FPM container initialization..."

# Create necessary directories
mkdir -p /usr/share/nginx/html/api/data
chmod 755 /usr/share/nginx/html/api/data

# Start CVE continuous sync in background
echo "Starting CVE continuous sync service..."
nohup php /usr/share/nginx/html/api/sync-cves-continuous.php > /var/log/cve-sync.log 2>&1 &

# Store the PID for monitoring
echo $! > /var/run/cve-sync.pid

echo "CVE sync started with PID: $(cat /var/run/cve-sync.pid)"

# Start PHP-FPM in foreground
echo "Starting PHP-FPM..."
exec php-fpm