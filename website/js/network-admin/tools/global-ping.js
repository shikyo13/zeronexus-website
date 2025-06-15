/**
 * Global Network Tools functionality
 * Provides network diagnostics (ping, traceroute, mtr) from multiple global locations
 */

// Location presets for quick selection
const LOCATION_PRESETS = {
    'world': {
        name: 'Worldwide',
        locations: [
            { country: 'US' },
            { country: 'GB' },
            { country: 'DE' },
            { country: 'JP' },
            { country: 'AU' }
        ]
    },
    'north-america': {
        name: 'North America',
        locations: [
            { country: 'US', city: 'New York' },
            { country: 'US', city: 'Los Angeles' },
            { country: 'CA' },
            { country: 'MX' }
        ]
    },
    'europe': {
        name: 'Europe',
        locations: [
            { country: 'GB' },
            { country: 'DE' },
            { country: 'FR' },
            { country: 'NL' },
            { country: 'ES' }
        ]
    },
    'asia': {
        name: 'Asia',
        locations: [
            { country: 'JP' },
            { country: 'SG' },
            { country: 'IN' },
            { country: 'KR' },
            { country: 'CN' }
        ]
    },
    'oceania': {
        name: 'Oceania',
        locations: [
            { country: 'AU', city: 'Sydney' },
            { country: 'AU', city: 'Melbourne' },
            { country: 'NZ' }
        ]
    },
    'south-america': {
        name: 'South America',
        locations: [
            { country: 'BR' },
            { country: 'AR' },
            { country: 'CL' }
        ]
    }
};

/**
 * Initialize global network test functionality
 */
export function initializeGlobalTest(modal) {
    console.log('Initializing global test functionality...');
    
    // Create location selector UI
    const locationSelector = createLocationSelector();
    
    // Find the row that contains the host input (first row)
    const hostRow = modal.querySelector('#hostTarget')?.closest('.row');
    if (hostRow) {
        // Add location selector as a new column in the existing second row
        const secondRow = hostRow.parentNode.querySelector('.row.g-3')?.parentNode.querySelector('.row') || 
                           hostRow.nextElementSibling;
        
        if (secondRow) {
            // Create a new column for location selector
            const locationCol = document.createElement('div');
            locationCol.className = 'col-md-4';
            locationCol.innerHTML = `
                <div class="tool-input-group">
                    <label for="testLocations" class="form-label">Test Locations:</label>
                    ${locationSelector}
                    <div class="form-text">Select regions to test from</div>
                </div>
            `;
            
            // Insert as the first child of the second row
            secondRow.insertBefore(locationCol, secondRow.firstElementChild);
            
            // Adjust existing columns to be smaller
            const existingCols = secondRow.querySelectorAll('.col-md-4:not(.col-md-4:first-child)');
            existingCols.forEach(col => {
                col.className = 'col-md-4';
            });
        }
    }
    
    // Add global test button
    const runButton = modal.querySelector('#runNetworkToolBtn');
    if (runButton) {
        const globalButton = document.createElement('button');
        globalButton.type = 'button';
        globalButton.className = 'btn btn-success ms-2';
        globalButton.id = 'runGlobalNetworkToolBtn';
        globalButton.innerHTML = '<i class="fas fa-globe me-2"></i>Global Test';
        
        runButton.parentNode.insertBefore(globalButton, runButton.nextSibling);
        
        // Add event listener
        globalButton.addEventListener('click', runGlobalTest);
        
        console.log('Global test button added successfully');
    } else {
        console.error('Could not find run button for global test initialization');
    }
    
    console.log('Global test functionality initialized');
}

/**
 * Create location selector dropdown
 */
function createLocationSelector() {
    let options = '<option value="world" selected>Worldwide (5 locations)</option>';
    
    for (const [key, preset] of Object.entries(LOCATION_PRESETS)) {
        if (key !== 'world') {
            options += `<option value="${key}">${preset.name}</option>`;
        }
    }
    
    return `<select class="form-select" id="testLocations" style="background-color: #FFFFFF !important;">${options}</select>`;
}

/**
 * Run global network test
 */
async function runGlobalTest() {
    const hostInput = document.getElementById('hostTarget');
    const toolTypeInput = document.getElementById('toolType');
    const locationSelect = document.getElementById('testLocations');
    const resultsDiv = document.getElementById('networkToolResults');
    const loadingDiv = document.getElementById('networkToolLoading');
    const errorDiv = document.getElementById('networkToolError');
    const errorText = document.getElementById('networkToolErrorText');
    
    // Get values
    const host = hostInput?.value.trim();
    const tool = toolTypeInput?.value || 'ping';
    const locationPreset = locationSelect?.value || 'world';
    
    // Validate
    if (!host) {
        if (errorDiv && errorText) {
            errorDiv.classList.remove('d-none');
            errorText.textContent = 'Please enter a domain name or IP address';
        }
        return;
    }
    
    // Reset display
    if (resultsDiv) resultsDiv.classList.add('d-none');
    if (errorDiv) errorDiv.classList.add('d-none');
    
    // Show loading
    if (loadingDiv) {
        loadingDiv.classList.remove('d-none');
        const loadingText = loadingDiv.querySelector('p');
        if (loadingText) {
            loadingText.innerHTML = `Running global ${tool} test from ${LOCATION_PRESETS[locationPreset].name}...`;
        }
    }
    
    try {
        // Get locations
        const locations = LOCATION_PRESETS[locationPreset].locations;
        
        // Make API request
        const response = await fetch('/api/global-network-tools.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json'
            },
            body: JSON.stringify({
                host: host,
                tool: tool,
                locations: locations,
                packetCount: document.getElementById('packetCount')?.value || 4
            })
        });
        
        const data = await response.json();
        
        if (!response.ok) {
            throw new Error(data.error || 'Request failed');
        }
        
        // Hide loading
        if (loadingDiv) loadingDiv.classList.add('d-none');
        
        // Display results
        displayGlobalResults(data, host);
        
    } catch (error) {
        // Hide loading
        if (loadingDiv) loadingDiv.classList.add('d-none');
        
        // Show error
        if (errorDiv && errorText) {
            errorDiv.classList.remove('d-none');
            errorText.innerHTML = `
                <div class="mb-2">${error.message}</div>
                <div class="small text-muted">
                    <strong>Troubleshooting:</strong>
                    <ul class="mb-0 ps-3">
                        <li>Check your internet connection</li>
                        <li>Try a different domain</li>
                        <li>Wait a moment and try again (rate limits may apply)</li>
                    </ul>
                </div>
            `;
        }
    }
}

/**
 * Display global test results
 */
function displayGlobalResults(data, host) {
    const resultsDiv = document.getElementById('networkToolResults');
    const resultOutput = document.getElementById('resultOutput');
    
    if (!resultsDiv || !resultOutput) return;
    
    // Show results container
    resultsDiv.classList.remove('d-none');
    
    // Update header
    const resultHost = document.getElementById('resultHost');
    const resultToolType = document.getElementById('resultToolType');
    const tool = data.tool || 'ping';
    
    if (resultHost) resultHost.textContent = host;
    if (resultToolType) resultToolType.textContent = `Global ${tool.charAt(0).toUpperCase() + tool.slice(1)}`;
    
    // Create formatted output
    let output = `GLOBAL ${tool.toUpperCase()} TEST RESULTS\n`;
    output += `Target: ${host}\n`;
    output += `Tool: ${tool}\n`;
    output += `Measurement ID: ${data.measurementId || 'N/A'}\n`;
    output += data.cached ? '(Cached results)\n' : '';
    output += `${'='.repeat(60)}\n\n`;
    
    // Process each location result
    data.data.forEach((result, index) => {
        const location = result.location;
        output += `LOCATION ${index + 1}: ${location.city || 'Unknown'}, ${location.country}\n`;
        output += `Network: ${location.network}\n`;
        if (location.asn) {
            output += `ASN: ${location.asn}\n`;
        }
        output += '\n';
        
        if (result.status === 'finished' && result.output) {
            output += result.output;
        } else if (result.error) {
            output += `ERROR: ${result.error}\n`;
        } else {
            output += 'No output available\n';
        }
        
        output += `\n${'='.repeat(60)}\n\n`;
    });
    
    // Add summary statistics
    output += generateGlobalSummary(data.data);
    
    // Display output
    resultOutput.textContent = output;
    
    // Update statistics tab
    updateGlobalStats(data.data, host);
    
    // Show visual tab for global results
    const visualTab = document.getElementById('visualResults-tab');
    if (visualTab) {
        visualTab.classList.remove('d-none');
    }
    
    // Create location cards visualization
    createLocationCards(data.data);
    
    // Add export and share functionality
    addExportShareButtons(data, host);
}

/**
 * Generate summary statistics for global results
 */
function generateGlobalSummary(results) {
    let summary = 'SUMMARY STATISTICS\n';
    summary += `${'='.repeat(60)}\n`;
    
    const stats = [];
    let totalLoss = 0;
    let lossCount = 0;
    
    results.forEach(result => {
        if (result.stats) {
            stats.push({
                location: `${result.location.city || result.location.country}`,
                avg: result.stats.avg,
                loss: result.stats.loss
            });
            
            if (result.stats.loss !== null) {
                totalLoss += result.stats.loss;
                lossCount++;
            }
        }
    });
    
    if (stats.length > 0) {
        // Sort by average latency
        stats.sort((a, b) => (a.avg || 999) - (b.avg || 999));
        
        summary += '\nLatency Rankings:\n';
        stats.forEach((stat, index) => {
            const avg = stat.avg ? `${stat.avg.toFixed(2)} ms` : 'N/A';
            const loss = stat.loss !== null ? `${stat.loss}% loss` : '';
            summary += `${index + 1}. ${stat.location}: ${avg} ${loss}\n`;
        });
        
        // Calculate global average
        const avgLatencies = stats.filter(s => s.avg).map(s => s.avg);
        if (avgLatencies.length > 0) {
            const globalAvg = avgLatencies.reduce((a, b) => a + b, 0) / avgLatencies.length;
            summary += `\nGlobal Average Latency: ${globalAvg.toFixed(2)} ms\n`;
        }
        
        if (lossCount > 0) {
            const avgLoss = totalLoss / lossCount;
            summary += `Global Average Packet Loss: ${avgLoss.toFixed(1)}%\n`;
        }
    }
    
    return summary;
}

/**
 * Update statistics tab with global results
 */
function updateGlobalStats(results, host) {
    // Update host field
    const statsHost = document.getElementById('statsHost');
    if (statsHost) statsHost.textContent = host;
    
    // Calculate aggregate statistics
    const stats = results.filter(r => r.stats).map(r => r.stats);
    
    if (stats.length > 0) {
        // Find min/max/avg across all locations
        const allAvg = stats.filter(s => s.avg).map(s => s.avg);
        const allMin = stats.filter(s => s.min).map(s => s.min);
        const allMax = stats.filter(s => s.max).map(s => s.max);
        
        if (allAvg.length > 0) {
            const globalMin = Math.min(...allMin);
            const globalMax = Math.max(...allMax);
            const globalAvg = allAvg.reduce((a, b) => a + b, 0) / allAvg.length;
            
            // Update display
            const statsMin = document.getElementById('statsMin');
            const statsAvg = document.getElementById('statsAvg');
            const statsMax = document.getElementById('statsMax');
            
            if (statsMin) statsMin.textContent = `${globalMin.toFixed(2)} ms`;
            if (statsAvg) statsAvg.textContent = `${globalAvg.toFixed(2)} ms`;
            if (statsMax) statsMax.textContent = `${globalMax.toFixed(2)} ms`;
        }
        
        // Update packet count (show location count instead)
        const statsPackets = document.getElementById('statsPackets');
        if (statsPackets) statsPackets.textContent = `${results.length} locations`;
        
        // Calculate overall success rate
        const lossStats = stats.filter(s => s.loss !== null);
        if (lossStats.length > 0) {
            const avgLoss = lossStats.reduce((a, b) => a + b.loss, 0) / lossStats.length;
            const successRate = 100 - avgLoss;
            
            const statsSuccess = document.getElementById('statsSuccess');
            if (statsSuccess) statsSuccess.textContent = `${successRate.toFixed(1)}%`;
        }
    }
}

/**
 * Add export and share buttons to the results
 */
function addExportShareButtons(data, host) {
    const copyButton = document.getElementById('copyNetworkToolBtn');
    if (!copyButton) return;
    
    // Remove existing export buttons
    const existingButtons = copyButton.parentNode.querySelectorAll('.export-btn, .share-btn');
    existingButtons.forEach(btn => btn.remove());
    
    // Add export button
    const exportButton = document.createElement('button');
    exportButton.type = 'button';
    exportButton.className = 'btn btn-sm btn-outline-info ms-2 export-btn';
    exportButton.innerHTML = '<i class="fas fa-download me-1"></i>Export';
    exportButton.addEventListener('click', () => exportResults(data, host));
    
    // Add share button
    const shareButton = document.createElement('button');
    shareButton.type = 'button';
    shareButton.className = 'btn btn-sm btn-outline-success ms-2 share-btn';
    shareButton.innerHTML = '<i class="fas fa-share-alt me-1"></i>Share';
    shareButton.addEventListener('click', () => shareResults(data, host));
    
    copyButton.parentNode.appendChild(exportButton);
    copyButton.parentNode.appendChild(shareButton);
}

/**
 * Export results to JSON or CSV
 */
async function exportResults(data, host) {
    const formats = ['JSON', 'CSV'];
    const format = await showFormatSelector(formats);
    
    if (!format) return;
    
    let content, filename, mimeType;
    
    if (format === 'JSON') {
        content = JSON.stringify(data, null, 2);
        filename = `global-${data.tool}-${host}-${new Date().toISOString().slice(0, 10)}.json`;
        mimeType = 'application/json';
    } else if (format === 'CSV') {
        content = convertToCSV(data, host);
        filename = `global-${data.tool}-${host}-${new Date().toISOString().slice(0, 10)}.csv`;
        mimeType = 'text/csv';
    }
    
    // Download file
    const blob = new Blob([content], { type: mimeType });
    const url = URL.createObjectURL(blob);
    const a = document.createElement('a');
    a.href = url;
    a.download = filename;
    document.body.appendChild(a);
    a.click();
    document.body.removeChild(a);
    URL.revokeObjectURL(url);
}

/**
 * Convert results to CSV format
 */
function convertToCSV(data, host) {
    const headers = [
        'Location', 'Country', 'City', 'Network', 'ASN', 
        'Status', 'Min Latency (ms)', 'Avg Latency (ms)', 
        'Max Latency (ms)', 'Packet Loss (%)'
    ];
    
    let csv = headers.join(',') + '\n';
    
    data.data.forEach(result => {
        const location = result.location;
        const stats = result.stats || {};
        
        const row = [
            `"${location.city || location.country}"`,
            `"${location.country}"`,
            `"${location.city || ''}"`,
            `"${location.network || ''}"`,
            location.asn || '',
            `"${result.status}"`,
            stats.min || '',
            stats.avg || '',
            stats.max || '',
            stats.loss !== undefined ? stats.loss : ''
        ];
        
        csv += row.join(',') + '\n';
    });
    
    return csv;
}

/**
 * Share results via unique link
 */
async function shareResults(data, host) {
    const shareButton = document.querySelector('.share-btn');
    const originalText = shareButton.innerHTML;
    
    try {
        shareButton.innerHTML = '<i class="fas fa-spinner fa-spin me-1"></i>Sharing...';
        shareButton.disabled = true;
        
        const response = await fetch('/api/shared-results.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json'
            },
            body: JSON.stringify(data)
        });
        
        const result = await response.json();
        
        if (!response.ok) {
            throw new Error(result.error || 'Failed to share results');
        }
        
        // Copy share URL to clipboard
        await navigator.clipboard.writeText(result.share_url);
        
        shareButton.innerHTML = '<i class="fas fa-check me-1"></i>Link Copied!';
        
        // Show share modal with details
        showShareModal(result);
        
        setTimeout(() => {
            shareButton.innerHTML = originalText;
            shareButton.disabled = false;
        }, 3000);
        
    } catch (error) {
        console.error('Share error:', error);
        shareButton.innerHTML = '<i class="fas fa-exclamation-triangle me-1"></i>Share Failed';
        
        setTimeout(() => {
            shareButton.innerHTML = originalText;
            shareButton.disabled = false;
        }, 3000);
        
        alert('Failed to share results: ' + error.message);
    }
}

/**
 * Show format selector
 */
function showFormatSelector(formats) {
    return new Promise((resolve) => {
        const modal = document.createElement('div');
        modal.className = 'modal fade';
        modal.innerHTML = `
            <div class="modal-dialog">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title">Export Format</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body">
                        <p>Select export format:</p>
                        <div class="d-grid gap-2">
                            ${formats.map(format => 
                                `<button type="button" class="btn btn-outline-primary format-btn" data-format="${format}">
                                    ${format}
                                </button>`
                            ).join('')}
                        </div>
                    </div>
                </div>
            </div>
        `;
        
        document.body.appendChild(modal);
        
        const bsModal = new bootstrap.Modal(modal);
        bsModal.show();
        
        modal.addEventListener('click', (e) => {
            if (e.target.classList.contains('format-btn')) {
                const format = e.target.dataset.format;
                bsModal.hide();
                resolve(format);
            }
        });
        
        modal.addEventListener('hidden.bs.modal', () => {
            document.body.removeChild(modal);
            resolve(null);
        });
    });
}

/**
 * Show share modal with link details
 */
function showShareModal(shareData) {
    const modal = document.createElement('div');
    modal.className = 'modal fade';
    modal.innerHTML = `
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Results Shared Successfully</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <p>Your network test results have been shared! The link has been copied to your clipboard.</p>
                    <div class="mb-3">
                        <label class="form-label">Share URL:</label>
                        <div class="input-group">
                            <input type="text" class="form-control" value="${shareData.share_url}" readonly>
                            <button type="button" class="btn btn-outline-secondary copy-url-btn">
                                <i class="fas fa-copy"></i>
                            </button>
                        </div>
                    </div>
                    <div class="alert alert-info">
                        <i class="fas fa-info-circle me-2"></i>
                        This link will expire in 30 days.
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                </div>
            </div>
        </div>
    `;
    
    document.body.appendChild(modal);
    
    const bsModal = new bootstrap.Modal(modal);
    bsModal.show();
    
    // Copy URL button
    modal.querySelector('.copy-url-btn').addEventListener('click', async () => {
        await navigator.clipboard.writeText(shareData.share_url);
        const btn = modal.querySelector('.copy-url-btn');
        btn.innerHTML = '<i class="fas fa-check"></i>';
        setTimeout(() => {
            btn.innerHTML = '<i class="fas fa-copy"></i>';
        }, 2000);
    });
    
    modal.addEventListener('hidden.bs.modal', () => {
        document.body.removeChild(modal);
    });
}

/**
 * Create location cards for visual display
 */
function createLocationCards(results) {
    const container = document.getElementById('globalLocationsList');
    if (!container) return;
    
    // Clear existing cards
    container.innerHTML = '';
    
    // Create a card for each location
    results.forEach((result, index) => {
        const location = result.location;
        const stats = result.stats || {};
        
        // Determine status color
        let statusClass = 'secondary';
        let statusIcon = 'circle';
        let statusText = 'Unknown';
        
        if (result.status === 'finished') {
            if (stats.loss === 0) {
                statusClass = 'success';
                statusIcon = 'check-circle';
                statusText = 'Online';
            } else if (stats.loss < 50) {
                statusClass = 'warning';
                statusIcon = 'exclamation-circle';
                statusText = 'Degraded';
            } else {
                statusClass = 'danger';
                statusIcon = 'times-circle';
                statusText = 'Offline';
            }
        } else if (result.error) {
            statusClass = 'danger';
            statusIcon = 'times-circle';
            statusText = 'Error';
        }
        
        // Create card HTML
        const cardHtml = `
            <div class="col-md-6 col-lg-4 mb-3">
                <div class="card h-100 border-${statusClass}">
                    <div class="card-header bg-${statusClass} text-white">
                        <i class="fas fa-${statusIcon} me-2"></i>
                        ${location.city || location.country}
                    </div>
                    <div class="card-body">
                        <h6 class="card-subtitle mb-2 text-muted">${location.country} - ${location.network}</h6>
                        <ul class="list-unstyled mb-0">
                            <li><strong>Status:</strong> ${statusText}</li>
                            <li><strong>Avg Latency:</strong> ${stats.avg ? stats.avg.toFixed(2) + ' ms' : 'N/A'}</li>
                            <li><strong>Packet Loss:</strong> ${stats.loss !== undefined ? stats.loss + '%' : 'N/A'}</li>
                            ${location.asn ? `<li><strong>ASN:</strong> ${location.asn}</li>` : ''}
                        </ul>
                    </div>
                </div>
            </div>
        `;
        
        container.innerHTML += cardHtml;
    });
}

// Export functions
export { runGlobalTest, LOCATION_PRESETS };