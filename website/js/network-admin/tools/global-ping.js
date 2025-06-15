/**
 * Global Ping Tool functionality
 * Provides network diagnostics from multiple global locations
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
    // Create location selector UI
    const locationSelector = createLocationSelector();
    
    // Insert after tool type selector
    const toolTypeRow = modal.querySelector('#toolType')?.closest('.col-md-4');
    if (toolTypeRow) {
        const newCol = document.createElement('div');
        newCol.className = 'col-md-8';
        newCol.innerHTML = `
            <div class="tool-input-group">
                <label for="testLocations" class="form-label">Test Locations:</label>
                ${locationSelector}
                <div class="form-text">Select regions to test from</div>
            </div>
        `;
        
        // Insert before the packet count column
        toolTypeRow.parentNode.insertBefore(newCol, toolTypeRow);
        
        // Adjust packet count column width
        toolTypeRow.className = 'col-md-4';
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
    }
    
    // Update modal title to reflect new capability
    const modalTitle = modal.querySelector('.modal-title');
    if (modalTitle) {
        modalTitle.textContent = 'Ping Tool (Local & Global)';
    }
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
    const locationSelect = document.getElementById('testLocations');
    const resultsDiv = document.getElementById('networkToolResults');
    const loadingDiv = document.getElementById('networkToolLoading');
    const errorDiv = document.getElementById('networkToolError');
    const errorText = document.getElementById('networkToolErrorText');
    
    // Get values
    const host = hostInput?.value.trim();
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
            loadingText.innerHTML = `Running global ping test from ${LOCATION_PRESETS[locationPreset].name}...`;
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
                tool: 'ping',
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
    if (resultHost) resultHost.textContent = host;
    if (resultToolType) resultToolType.textContent = 'Global Ping';
    
    // Create formatted output
    let output = `GLOBAL PING TEST RESULTS\n`;
    output += `Target: ${host}\n`;
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