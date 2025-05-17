/**
 * Ping tool functionality
 * Provides network diagnostic tool features for the Network Admin Tools page
 */

/**
 * Initialize Ping functionality
 */
function setupPingTraceroute() {
  console.log('Setting up Ping tool...');
  
  // Get DOM elements
  const hostTargetInput = document.getElementById('hostTarget');
  const toolTypeSelect = document.getElementById('toolType');
  const packetCountInput = document.getElementById('packetCount');
  const packetSizeInput = document.getElementById('packetSize');
  const timeoutInput = document.getElementById('timeout');
  const runButton = document.getElementById('runNetworkToolBtn');
  const clearButton = document.getElementById('clearNetworkToolBtn');
  const resultOutput = document.getElementById('resultOutput');
  const loadingDiv = document.getElementById('networkToolLoading');
  const resultsDiv = document.getElementById('networkToolResults');
  const errorDiv = document.getElementById('networkToolError');
  const errorText = document.getElementById('networkToolErrorText');
  const loadingToolType = document.getElementById('loadingToolType');
  const resultHost = document.getElementById('resultHost');
  const resultToolType = document.getElementById('resultToolType');
  
  /**
   * Reset all display elements for network tool
   */
  function resetNetworkToolDisplay() {
    // Hide results and error containers
    if (resultsDiv) resultsDiv.classList.add('d-none');
    if (errorDiv) errorDiv.classList.add('d-none');
    if (loadingDiv) loadingDiv.classList.add('d-none');
    
    // Clear content areas
    if (resultOutput) resultOutput.textContent = '';
    
    // Clear all stats fields
    const statsFields = [
      'statsHost', 'statsPackets', 'statsSuccess', 'statsMin', 
      'statsAvg', 'statsMax', 'statsStdDev', 'statsIp'
    ];
    
    statsFields.forEach(fieldId => {
      const field = document.getElementById(fieldId);
      if (field) field.textContent = '';
    });
  }
  
  /**
   * Run a network test through the API
   */
  function runNetworkTest(host, tool) {
    // Always force tool to be 'ping'
    tool = 'ping';
    
    // Show loading indicator
    if (loadingDiv) loadingDiv.classList.remove('d-none');
    if (loadingToolType) loadingToolType.textContent = tool;
    
    // Get parameters
    const packetCount = packetCountInput?.value || 4;
    const packetSize = packetSizeInput?.value || 56;
    const timeout = timeoutInput?.value || 2;
    
    // Construct API URL
    const apiUrl = `/api/network-tools.php?host=${encodeURIComponent(host)}&tool=${encodeURIComponent(tool)}&packetCount=${encodeURIComponent(packetCount)}&packetSize=${encodeURIComponent(packetSize)}&timeout=${encodeURIComponent(timeout)}`;
    
    console.log(`Calling network API: ${apiUrl}`);
    
    // Make API call
    fetch(apiUrl)
      .then(response => {
        return response.text().then(text => {
          try {
            // Check if response contains HTML (server error page)
            if (text.includes('<!DOCTYPE html>') || text.includes('<html')) {
              console.error('HTML error response received');
              throw new Error('Server Error: The server returned an HTML error page instead of JSON.');
            }
            
            // Try to parse as JSON
            const jsonData = JSON.parse(text);
            
            // Check for errors in JSON response
            if (!response.ok) {
              if (!jsonData.output && jsonData.error) {
                jsonData.output = `ERROR: ${jsonData.error}`;
              }
              return jsonData;
            }
            
            return jsonData;
          } catch (e) {
            console.error('Response parsing error:', e);
            throw new Error('Server error: Invalid response format.');
          }
        });
      })
      .then(data => {
        // Hide loading indicator
        if (loadingDiv) loadingDiv.classList.add('d-none');
        
        // Show results container
        if (resultsDiv) resultsDiv.classList.remove('d-none');
        
        // Update result headers
        if (resultHost) resultHost.textContent = host;
        if (resultToolType) resultToolType.textContent = tool.toUpperCase();
        
        // Add output
        if (resultOutput) {
          resultOutput.textContent = data.output || 'No results returned';
          
          // Check for errors in output
          if (data.output && data.output.includes('ERROR:')) {
            if (errorDiv && errorText) {
              errorDiv.classList.remove('d-none');
              
              // Extract error message
              const errorMatch = data.output.match(/ERROR:([^\n]+)/);
              if (errorMatch) {
                errorText.textContent = errorMatch[1].trim();
              } else {
                errorText.textContent = 'An error occurred executing the command.';
              }
            }
          }
        }
        
        // Update stats tab
        if (data.output) {
          updateNetworkStats(data.output, tool, host);
        }
      })
      .catch(error => {
        console.error('Network tool error:', error);
        
        // Hide loading indicator
        if (loadingDiv) loadingDiv.classList.add('d-none');
        
        // Show error message
        if (errorDiv) {
          errorDiv.classList.remove('d-none');
          if (errorText) {
            errorText.innerHTML = `
              <div class="mb-2">
                ${error.message || 'An error occurred while processing your request.'}
              </div>
              <div class="small text-muted">
                <strong>Troubleshooting:</strong>
                <ul class="mb-0 ps-3">
                  <li>Check that the domain or IP address is valid</li>
                  <li>Try using a different domain (e.g. google.com)</li>
                  <li>Refresh the page and try again</li>
                </ul>
              </div>
            `;
          }
        }
      });
  }
  
  /**
   * Update network stats based on ping output
   */
  function updateNetworkStats(output, tool, host) {
    // Get stats elements
    const statsHost = document.getElementById('statsHost');
    const statsPackets = document.getElementById('statsPackets');
    const statsSuccess = document.getElementById('statsSuccess');
    const statsMin = document.getElementById('statsMin');
    const statsAvg = document.getElementById('statsAvg');
    const statsMax = document.getElementById('statsMax');
    const statsStdDev = document.getElementById('statsStdDev');
    const statsIp = document.getElementById('statsIp');
    
    // Clear previous stats
    if (statsHost) statsHost.textContent = '';
    if (statsPackets) statsPackets.textContent = '';
    if (statsSuccess) statsSuccess.textContent = '';
    if (statsMin) statsMin.textContent = '';
    if (statsAvg) statsAvg.textContent = '';
    if (statsMax) statsMax.textContent = '';
    if (statsStdDev) statsStdDev.textContent = '';
    if (statsIp) statsIp.textContent = '';
    
    // Update host
    if (statsHost) statsHost.textContent = host;
    
    // Parse stats from output
    let hostIP = '';
    let packetCount = 0;
    let packetLoss = 0;
    let minLatency = 0;
    let avgLatency = 0;
    let maxLatency = 0;
    let stdDev = 0;
    
    // Look for target IP in ping output
    const ipMatch = output.match(/PING\s+[^\s]+\s+\(([^)]+)\)/);
    if (ipMatch) {
      hostIP = ipMatch[1];
    }
    
    // Look for statistics line
    const statsMatch = output.match(/(\d+) packets transmitted, (\d+) (?:packets )?received, ([\d.]+)% packet loss/);
    if (statsMatch) {
      packetCount = parseInt(statsMatch[1]);
      const packetsReceived = parseInt(statsMatch[2]);
      packetLoss = parseFloat(statsMatch[3]);
      
      // Calculate success rate
      const successRate = 100 - packetLoss;
      
      // Look for round-trip stats
      const rtMatch = output.match(/min\/avg\/max(?:\/(?:mdev|stddev))? = ([\d.]+)\/([\d.]+)\/([\d.]+)(?:\/([\d.]+))? ms/);
      if (rtMatch) {
        minLatency = parseFloat(rtMatch[1]);
        avgLatency = parseFloat(rtMatch[2]);
        maxLatency = parseFloat(rtMatch[3]);
        stdDev = rtMatch[4] ? parseFloat(rtMatch[4]) : 0;
      }
      
      // Update stats display
      if (statsPackets) statsPackets.textContent = packetCount.toString();
      if (statsSuccess) statsSuccess.textContent = successRate.toFixed(1) + '%';
      if (statsMin) statsMin.textContent = minLatency + ' ms';
      if (statsAvg) statsAvg.textContent = avgLatency + ' ms';
      if (statsMax) statsMax.textContent = maxLatency + ' ms';
      if (statsStdDev) statsStdDev.textContent = stdDev + ' ms';
    }
    
    // Update IP if available
    if (statsIp) {
      statsIp.textContent = hostIP || '(unknown)';
    }
  }
  
  // Copy results to clipboard
  document.getElementById('copyNetworkToolBtn')?.addEventListener('click', function() {
    if (!resultOutput) return;
    
    const text = resultOutput.textContent;
    
    navigator.clipboard.writeText(text)
      .then(() => {
        // Show copied notification
        const originalText = this.innerHTML;
        this.innerHTML = '<i class="fas fa-check me-1"></i>Copied!';
        
        setTimeout(() => {
          this.innerHTML = originalText;
        }, 2000);
      })
      .catch(err => {
        console.error('Could not copy text: ', err);
        alert('Failed to copy results to clipboard.');
      });
  });
  
  // Clear form
  if (clearButton) {
    clearButton.addEventListener('click', function() {
      if (hostTargetInput) hostTargetInput.value = '';
      if (packetCountInput) packetCountInput.value = '4';
      if (packetSizeInput) packetSizeInput.value = '56';
      if (timeoutInput) timeoutInput.value = '2';
      
      // Hide results
      if (resultsDiv) resultsDiv.classList.add('d-none');
      
      // Hide error
      if (errorDiv) errorDiv.classList.add('d-none');
      
      // Focus on host input
      if (hostTargetInput) hostTargetInput.focus();
    });
  }
  
  // Always force tool to be ping
  if (toolTypeSelect) {
    toolTypeSelect.value = 'ping';
  }
  
  // Run button event listener
  if (runButton) {
    runButton.addEventListener('click', function() {
      // Get host value
      const host = hostTargetInput ? hostTargetInput.value.trim() : '';
      
      // Validate host
      if (!host) {
        if (errorDiv && errorText) {
          errorDiv.classList.remove('d-none');
          errorText.textContent = 'Please enter a domain name or IP address';
        }
        return;
      }
      
      // Prevent double-clicks
      runButton.disabled = true;
      setTimeout(() => {
        runButton.disabled = false;
      }, 3000);
      
      // Reset display
      resetNetworkToolDisplay();
      
      // Run the test
      setTimeout(() => {
        runNetworkTest(host, 'ping');
      }, 50);
    });
  }
  
  // Check URL parameters for direct linking
  function checkUrlParams() {
    const hash = window.location.hash;
    if (hash && hash.startsWith('#ping-traceroute')) {
      // Extract parameters
      const paramsStr = hash.split('?')[1] || '';
      const searchParams = new URLSearchParams(paramsStr);
      
      // Get host parameter
      const host = searchParams.get('host');
      
      // Set host if provided
      if (host && hostTargetInput) {
        hostTargetInput.value = host;
      }
      
      // Get other parameters
      const packetCount = searchParams.get('packetCount');
      const packetSize = searchParams.get('packetSize');
      const timeout = searchParams.get('timeout');
      
      // Set other parameters if provided
      if (packetCount && packetCountInput) {
        packetCountInput.value = packetCount;
      }
      
      if (packetSize && packetSizeInput) {
        packetSizeInput.value = packetSize;
      }
      
      if (timeout && timeoutInput) {
        timeoutInput.value = timeout;
      }
      
      // Auto-run if specified
      if (searchParams.get('autorun') === 'true' && runButton) {
        setTimeout(() => {
          runButton.click();
        }, 300);
      }
    }
  }
  
  // Check URL parameters on load
  checkUrlParams();
}

// Export the setup function
export default setupPingTraceroute;