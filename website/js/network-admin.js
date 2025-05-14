/**
 * Network Admin Tools JavaScript
 * 
 * Provides functionality for the Network Admin Tools page
 */

// Global debugging
console.log('Network Admin Tools JavaScript loaded');
window.addEventListener('load', function() {
  console.log('Window fully loaded');
  // Check modal elements after window load
  console.log('Ping modal elements after window load:');
  console.log('- hostTarget:', document.getElementById('hostTarget'));
  console.log('- runNetworkToolBtn:', document.getElementById('runNetworkToolBtn'));
  
  // Direct event binding for run button to ensure it works
  document.body.addEventListener('click', function(e) {
    if (e.target && (e.target.id === 'runNetworkToolBtn' || (e.target.parentElement && e.target.parentElement.id === 'runNetworkToolBtn'))) {
      console.log('Run button clicked through event delegation!');
      
      // Get values from form
      const hostInput = document.getElementById('hostTarget');
      const toolType = document.getElementById('toolType');
      
      const host = hostInput ? hostInput.value.trim() : 'example.com';
      const tool = toolType ? toolType.value : 'ping';
      
      // Simple simulation
      simulateNetworkTest(host, tool);
    }
  });
  
  function simulateNetworkTest(host, tool) {
    // Show the results container
    const resultsDiv = document.getElementById('networkToolResults');
    if (resultsDiv) resultsDiv.classList.remove('d-none');
    
    // Update the result headers
    const resultHost = document.getElementById('resultHost');
    const resultToolType = document.getElementById('resultToolType');
    if (resultHost) resultHost.textContent = host;
    if (resultToolType) resultToolType.textContent = tool.toUpperCase();
    
    // Add some sample output
    const resultOutput = document.getElementById('resultOutput');
    if (resultOutput) {
      if (tool === 'ping') {
        resultOutput.textContent = `PING ${host} (192.168.1.1): 56 data bytes\n64 bytes from 192.168.1.1: icmp_seq=1 ttl=64 time=40.123 ms\n64 bytes from 192.168.1.1: icmp_seq=2 ttl=64 time=35.342 ms\n64 bytes from 192.168.1.1: icmp_seq=3 ttl=64 time=38.758 ms\n64 bytes from 192.168.1.1: icmp_seq=4 ttl=64 time=44.552 ms\n\n--- ${host} ping statistics ---\n4 packets transmitted, 4 packets received, 0% packet loss\nround-trip min/avg/max/stddev = 35.342/39.694/44.552/3.392 ms`;
      } else if (tool === 'traceroute') {
        resultOutput.textContent = `traceroute to ${host} (192.168.1.1), 30 hops max, 60 byte packets\n 1  local-gateway.net (192.168.0.1)  2.456 ms  2.142 ms  1.865 ms\n 2  isp-edge-router.net (10.0.0.1)  12.387 ms  10.234 ms  11.765 ms\n 3  isp-core-76.net (10.10.10.76)  24.789 ms  22.348 ms  23.124 ms\n 4  level3-transit-45.net (4.4.8.45)  35.678 ms  36.245 ms  34.987 ms\n 5  ${host} (192.168.1.1)  45.732 ms  44.891 ms  46.123 ms`;
      } else {
        resultOutput.textContent = `Start: 2025-05-14 12:34:56, Host: ${host}\nHOST: Mozilla/5.0             Loss%   Snt   Last   Avg  Best  Wrst StDev\n 1. local-gateway.net (192.168.0.1)  0%     4    2.1   2.3   1.9   3.2   0.5\n 2. isp-edge-router.net (10.0.0.1)   0%     4   12.4  11.5  10.2  12.4   0.9\n 3. isp-core-76.net (10.10.10.76)    0%     4   23.7  23.4  22.3  24.8   0.9\n 4. level3-transit-45.net (4.4.8.45)  0%     4   36.1  35.6  34.9  36.2   0.5\n 5. ${host} (192.168.1.1)             0%     4   45.7  45.6  44.9  46.1   0.5`;
      }
    }
    
    // Show the visualization tab only for traceroute and mtr
    const visualTab = document.getElementById('visualTab');
    if (visualTab) {
      if (tool === 'traceroute' || tool === 'mtr') {
        visualTab.classList.remove('d-none');
        
        // Create a basic visualization
        const tracerouteVis = document.getElementById('tracerouteVisualization');
        if (tracerouteVis) {
          tracerouteVis.innerHTML = '';
          
          // Create simple hop visualization
          for (let i = 1; i <= 5; i++) {
            const hopContainer = document.createElement('div');
            hopContainer.className = 'hop-container';
            
            const hopNumber = document.createElement('div');
            hopNumber.className = 'hop-number';
            hopNumber.textContent = i;
            hopContainer.appendChild(hopNumber);
            
            const hopNode = document.createElement('div');
            hopNode.className = 'hop-node';
            
            let icon = 'fa-network-wired';
            let hostname = 'unknown';
            if (i === 1) {
              icon = 'fa-home';
              hostname = 'local-gateway.net';
            } else if (i === 5) {
              icon = 'fa-server';
              hostname = host;
            } else if (i === 2) {
              hostname = 'isp-edge-router.net';
            } else if (i === 3) {
              hostname = 'isp-core-76.net';
            } else if (i === 4) {
              hostname = 'level3-transit-45.net';
            }
            
            hopNode.innerHTML = `
              <div class="hop-node-icon"><i class="fas ${icon}"></i></div>
              <div class="hop-details">
                <div class="hop-hostname">${hostname}</div>
                <div class="hop-ip">192.168.${i}.1</div>
                <div class="hop-stats">
                  <div class="hop-stat">
                    <div class="hop-stat-label">RTT:</div>
                    <div class="hop-stat-value latency-good">${(i * 10).toFixed(1)} ms</div>
                  </div>
                </div>
              </div>
            `;
            
            hopContainer.appendChild(hopNode);
            
            // Add path indicator unless it's the last hop
            if (i < 5) {
              const hopPath = document.createElement('div');
              hopPath.className = 'hop-path';
              hopContainer.appendChild(hopPath);
            }
            
            tracerouteVis.appendChild(hopContainer);
          }
        }
      } else {
        visualTab.classList.add('d-none');
      }
    }
    
    // Update stats tab with sample data
    const statsHost = document.getElementById('statsHost');
    const statsPackets = document.getElementById('statsPackets');
    const statsSuccess = document.getElementById('statsSuccess');
    const statsIp = document.getElementById('statsIp');
    const statsMin = document.getElementById('statsMin');
    const statsAvg = document.getElementById('statsAvg');
    const statsMax = document.getElementById('statsMax');
    const statsStdDev = document.getElementById('statsStdDev');
    
    if (statsHost) statsHost.textContent = host;
    if (statsPackets) statsPackets.textContent = '4';
    if (statsSuccess) statsSuccess.textContent = '100.0%';
    if (statsIp) statsIp.textContent = '192.168.1.1';
    if (statsMin) statsMin.textContent = '35.3 ms';
    if (statsAvg) statsAvg.textContent = '39.7 ms';
    if (statsMax) statsMax.textContent = '44.6 ms';
    if (statsStdDev) statsStdDev.textContent = '3.4 ms';
    
    // Make sure results are visible
    const statsTab = document.getElementById('rawResults-tab');
    if (statsTab) {
      setTimeout(() => {
        const bootstrapTab = window.bootstrap && window.bootstrap.Tab ? new bootstrap.Tab(statsTab) : null;
        if (bootstrapTab) bootstrapTab.show();
      }, 100);
    }
  }
});

document.addEventListener('DOMContentLoaded', function() {
  // Save active tab in localStorage so it persists between page refreshes
  const toolTabs = document.getElementById('toolTabs');
  const tabLinks = toolTabs.querySelectorAll('.nav-link');
  
  // Get saved tab from localStorage or use default
  const savedTab = localStorage.getItem('activeNetworkToolTab');
  
  if (savedTab) {
    // Activate the saved tab
    const tabToActivate = document.getElementById(savedTab);
    if (tabToActivate) {
      const tab = new bootstrap.Tab(tabToActivate);
      tab.show();
    }
  }
  
  // Listen for tab changes and save to localStorage
  tabLinks.forEach(tabLink => {
    tabLink.addEventListener('shown.bs.tab', function(event) {
      localStorage.setItem('activeNetworkToolTab', event.target.id);
    });
  });
  
  // Tool-specific functionality will be added in future updates
  // This will include implementations for each category of tools
});

/**
 * IP Subnet Calculator functionality
 * Implements complete subnet calculation logic
 */
function setupSubnetCalculator() {
  // Get DOM elements
  const ipAddressInput = document.getElementById('ipAddress');
  const subnetInput = document.getElementById('subnetInput');
  const calculateBtn = document.getElementById('calculateSubnetBtn');
  const clearBtn = document.getElementById('clearSubnetBtn');
  const copyResultsBtn = document.getElementById('copyResultsBtn');
  const resultsDiv = document.getElementById('subnetResults');
  const errorDiv = document.getElementById('subnetError');
  const errorText = document.getElementById('subnetErrorText');

  // Set up event listeners
  if (calculateBtn) {
    calculateBtn.addEventListener('click', calculateSubnet);
  }

  if (clearBtn) {
    clearBtn.addEventListener('click', clearSubnetForm);
  }

  if (copyResultsBtn) {
    copyResultsBtn.addEventListener('click', copySubnetResults);
  }

  // Input validation with Enter key
  if (ipAddressInput) {
    ipAddressInput.addEventListener('keyup', function(e) {
      if (e.key === 'Enter') {
        calculateSubnet();
      }
    });
  }

  if (subnetInput) {
    subnetInput.addEventListener('keyup', function(e) {
      if (e.key === 'Enter') {
        calculateSubnet();
      }
    });
  }

  /**
   * Main subnet calculation function
   */
  function calculateSubnet() {
    // Clear previous error
    hideError();

    // Get input values
    const ipAddress = ipAddressInput.value.trim();
    const subnetStr = subnetInput.value.trim();

    // Validate inputs
    if (!ipAddress) {
      showError('IP Address is required.');
      return;
    }

    if (!subnetStr) {
      showError('Subnet Mask or CIDR notation is required.');
      return;
    }

    // Validate IP address format
    if (!isValidIpAddress(ipAddress)) {
      showError('Invalid IP address format. Please enter a valid IPv4 address (e.g., 192.168.1.1).');
      return;
    }

    // Parse the subnet input (could be CIDR notation or subnet mask)
    let cidrBits;
    let subnetMask;

    if (subnetStr.startsWith('/')) {
      // CIDR notation
      cidrBits = parseInt(subnetStr.substring(1), 10);
      if (isNaN(cidrBits) || cidrBits < 0 || cidrBits > 32) {
        showError('Invalid CIDR notation. Must be between /0 and /32.');
        return;
      }
      subnetMask = cidrToSubnetMask(cidrBits);
    } else {
      // Subnet mask (e.g., 255.255.255.0)
      if (!isValidIpAddress(subnetStr)) {
        showError('Invalid subnet mask format. Please enter a valid subnet mask (e.g., 255.255.255.0) or CIDR notation (e.g., /24).');
        return;
      }

      // Validate that it's a valid subnet mask
      if (!isValidSubnetMask(subnetStr)) {
        showError('Invalid subnet mask. Subnet masks must have continuous 1s followed by continuous 0s in binary.');
        return;
      }

      subnetMask = subnetStr;
      cidrBits = subnetMaskToCidr(subnetMask);
    }

    // Parse IP and subnet to integer values
    const ipOctets = ipAddress.split('.').map(octet => parseInt(octet, 10));
    const subnetOctets = subnetMask.split('.').map(octet => parseInt(octet, 10));

    // Convert IP and subnet to 32-bit integers
    const ipInt = (ipOctets[0] << 24) | (ipOctets[1] << 16) | (ipOctets[2] << 8) | ipOctets[3];
    const subnetInt = (subnetOctets[0] << 24) | (subnetOctets[1] << 16) | (subnetOctets[2] << 8) | subnetOctets[3];

    // Calculate network and broadcast addresses
    const networkInt = ipInt & subnetInt;
    const wildcardInt = ~subnetInt & 0xFFFFFFFF; // Bitwise NOT plus mask to ensure 32-bit
    const broadcastInt = networkInt | wildcardInt;

    // Calculate network class
    const networkClass = determineNetworkClass(ipOctets[0]);

    // Calculate first and last host addresses
    let firstHostInt, lastHostInt;
    if (cidrBits < 31) {
      firstHostInt = networkInt + 1;
      lastHostInt = broadcastInt - 1;
    } else if (cidrBits === 31) {
      // Special case: /31 networks are used for point-to-point links (RFC 3021)
      firstHostInt = networkInt;
      lastHostInt = broadcastInt;
    } else if (cidrBits === 32) {
      // Special case: /32 is a host route
      firstHostInt = networkInt;
      lastHostInt = networkInt;
    }

    // Calculate total and usable hosts
    let totalHosts, usableHosts;
    if (cidrBits < 31) {
      totalHosts = Math.pow(2, 32 - cidrBits);
      usableHosts = totalHosts - 2; // Subtract network and broadcast addresses
    } else if (cidrBits === 31) {
      // Special case: /31 networks can use both addresses (RFC 3021)
      totalHosts = 2;
      usableHosts = 2;
    } else if (cidrBits === 32) {
      // Special case: /32 is a single host
      totalHosts = 1;
      usableHosts = 1;
    }

    // Convert results back to dotted decimal format
    const networkAddress = intToIpAddress(networkInt);
    const broadcastAddress = intToIpAddress(broadcastInt);
    const firstHost = intToIpAddress(firstHostInt);
    const lastHost = intToIpAddress(lastHostInt);

    // Create binary representation of subnet mask
    // Convert each octet to 8-bit binary with spaces between octets
    const binaryMask = subnetMask
      .split('.')
      .map(octet => parseInt(octet, 10).toString(2).padStart(8, '0'))
      .join(' ');

    // Display results
    displayResults({
      ipAddress: ipAddress,
      subnetMask: subnetMask,
      cidr: '/' + cidrBits,
      networkClass: networkClass,
      networkAddress: networkAddress,
      broadcastAddress: broadcastAddress,
      firstHost: firstHost,
      lastHost: lastHost,
      totalHosts: totalHosts.toLocaleString(),
      usableHosts: usableHosts.toLocaleString(),
      binaryMask: binaryMask
    });
  }

  /**
   * Display calculated subnet results
   */
  function displayResults(results) {
    // Update result fields
    document.getElementById('resultIpAddress').innerText = results.ipAddress;
    document.getElementById('resultSubnetMask').innerText = results.subnetMask;
    document.getElementById('resultCidr').innerText = results.cidr;
    document.getElementById('resultNetworkClass').innerText = results.networkClass;
    document.getElementById('resultNetworkAddress').innerText = results.networkAddress;
    document.getElementById('resultBroadcastAddress').innerText = results.broadcastAddress;
    document.getElementById('resultFirstHost').innerText = results.firstHost;
    document.getElementById('resultLastHost').innerText = results.lastHost;
    document.getElementById('resultTotalHosts').innerText = results.totalHosts;
    document.getElementById('resultUsableHosts').innerText = results.usableHosts;
    document.getElementById('resultBinaryMask').innerText = results.binaryMask;

    // Show results
    resultsDiv.classList.remove('d-none');
  }

  /**
   * Clear the subnet calculator form
   */
  function clearSubnetForm() {
    ipAddressInput.value = '';
    subnetInput.value = '';
    resultsDiv.classList.add('d-none');
    hideError();
    ipAddressInput.focus();
  }

  /**
   * Show error message
   */
  function showError(message) {
    errorText.innerText = message;
    errorDiv.classList.remove('d-none');
    resultsDiv.classList.add('d-none');
  }

  /**
   * Hide error message
   */
  function hideError() {
    errorDiv.classList.add('d-none');
  }

  /**
   * Copy subnet calculation results to clipboard
   */
  function copySubnetResults() {
    let resultsText = '';
    const labels = document.querySelectorAll('.result-group label');
    const values = document.querySelectorAll('.result-value');

    for (let i = 0; i < labels.length; i++) {
      resultsText += labels[i].innerText.replace(':', '') + ': ' + values[i].innerText + '\\n';
    }

    navigator.clipboard.writeText(resultsText.trim())
      .then(() => {
        // Show copied notification
        const originalText = copyResultsBtn.innerHTML;
        copyResultsBtn.innerHTML = '<i class="fas fa-check me-2"></i>Copied!';

        setTimeout(() => {
          copyResultsBtn.innerHTML = originalText;
        }, 2000);
      })
      .catch(err => {
        console.error('Could not copy text: ', err);
      });
  }

  /**
   * Utility function to convert CIDR prefix to subnet mask
   */
  function cidrToSubnetMask(cidr) {
    const mask = ~0 << (32 - cidr);
    const octets = [
      (mask >>> 24) & 255,
      (mask >>> 16) & 255,
      (mask >>> 8) & 255,
      mask & 255
    ];
    return octets.join('.');
  }

  /**
   * Utility function to convert subnet mask to CIDR prefix
   */
  function subnetMaskToCidr(subnetMask) {
    const octets = subnetMask.split('.').map(octet => parseInt(octet, 10));
    let cidr = 0;
    octets.forEach(octet => {
      const binaryOctet = octet.toString(2);
      cidr += binaryOctet.split('1').length - 1;
    });
    return cidr;
  }

  /**
   * Utility function to convert 32-bit integer to IP address
   */
  function intToIpAddress(int) {
    return [
      (int >>> 24) & 255,
      (int >>> 16) & 255,
      (int >>> 8) & 255,
      int & 255
    ].join('.');
  }

  /**
   * Validate IP address format
   */
  function isValidIpAddress(ipAddress) {
    // IPv4 Regex: four octets (0-255) separated by dots
    const ipv4Regex = /^(25[0-5]|2[0-4][0-9]|[01]?[0-9][0-9]?)\.(25[0-5]|2[0-4][0-9]|[01]?[0-9][0-9]?)\.(25[0-5]|2[0-4][0-9]|[01]?[0-9][0-9]?)\.(25[0-5]|2[0-4][0-9]|[01]?[0-9][0-9]?)$/;
    return ipv4Regex.test(ipAddress);
  }

  /**
   * Validate subnet mask format
   */
  function isValidSubnetMask(mask) {
    // Convert to binary string
    const octets = mask.split('.').map(octet => parseInt(octet, 10));
    const binaryMask = ((octets[0] << 24) | (octets[1] << 16) | (octets[2] << 8) | octets[3]).toString(2);

    // Check for continuous 1s followed by continuous 0s
    return /^1*0*$/.test(binaryMask);
  }

  /**
   * Determine network class based on first octet
   */
  function determineNetworkClass(firstOctet) {
    if (firstOctet >= 0 && firstOctet <= 127) return 'Class A';
    if (firstOctet >= 128 && firstOctet <= 191) return 'Class B';
    if (firstOctet >= 192 && firstOctet <= 223) return 'Class C';
    if (firstOctet >= 224 && firstOctet <= 239) return 'Class D (Multicast)';
    if (firstOctet >= 240 && firstOctet <= 255) return 'Class E (Reserved)';
    return 'Unknown';
  }

  // No longer needed - integrated directly into the calculation function
}

// Call setup functions when document is ready
document.addEventListener('DOMContentLoaded', function() {
  console.log('DOM Content Loaded - setting up tools');
  setupSubnetCalculator();
  setupDnsLookup();
  setupSecurityHeadersChecker();
  setupPingTraceroute();
  
  // Direct event binding as a fallback
  const runNetworkToolBtn = document.getElementById('runNetworkToolBtn');
  if (runNetworkToolBtn) {
    console.log('Adding direct event listener to run button');
    runNetworkToolBtn.addEventListener('click', function() {
      console.log('Run button clicked!');
      const hostInput = document.getElementById('hostTarget');
      const host = hostInput ? hostInput.value.trim() : '';
      alert('Testing network tool functionality for host: ' + host);
    });
  } else {
    console.log('Run button not found in DOM');
  }
});

/**
 * DNS Lookup functionality
 * Implements DNS record lookup and visualization
 */
function setupDnsLookup() {
  // Get DOM elements
  const domainInput = document.getElementById('domainName');
  const recordTypeSelect = document.getElementById('recordType');
  const lookupBtn = document.getElementById('lookupDnsBtn');
  const clearBtn = document.getElementById('clearDnsBtn');
  const copyResultsBtn = document.getElementById('copyDnsResultsBtn');
  const toggleVisualizationBtn = document.getElementById('toggleVisualizationBtn');
  const resultsDiv = document.getElementById('dnsResults');
  const dnsRecordsDiv = document.getElementById('dnsRecords');
  const dnsVisualizationDiv = document.getElementById('dnsVisualization');
  const resultDomainSpan = document.getElementById('resultDomain');
  const errorDiv = document.getElementById('dnsError');
  const errorText = document.getElementById('dnsErrorText');
  const loadingDiv = document.getElementById('dnsLoading');

  // Set up event listeners
  if (lookupBtn) {
    lookupBtn.addEventListener('click', performDnsLookup);
  }

  if (clearBtn) {
    clearBtn.addEventListener('click', clearDnsForm);
  }

  if (copyResultsBtn) {
    copyResultsBtn.addEventListener('click', copyDnsResults);
  }

  if (toggleVisualizationBtn) {
    toggleVisualizationBtn.addEventListener('click', toggleVisualization);
  }

  // Input validation with Enter key
  if (domainInput) {
    domainInput.addEventListener('keyup', function(e) {
      if (e.key === 'Enter') {
        performDnsLookup();
      }
    });
  }
  
  // Show or hide the PTR help text when record type changes
  if (recordTypeSelect) {
    recordTypeSelect.addEventListener('change', function() {
      const ptrHelp = document.getElementById('ptrHelp');
      if (ptrHelp) {
        ptrHelp.classList.toggle('d-none', this.value !== 'PTR');
      }
    });
  }

  /**
   * Perform DNS lookup based on form inputs
   */
  function performDnsLookup() {
    // Clear previous results and errors
    hideError();
    hideResults();
    showLoading();

    // Get input values
    const domain = domainInput.value.trim();
    const recordType = recordTypeSelect.value;

    // Validate domain
    if (!domain) {
      hideLoading();
      showError('Please enter a domain name or IP address.');
      return;
    }

    // Basic domain or IP validation
    const isDomain = /^[a-zA-Z0-9\-\.]+\.[a-zA-Z]{2,}$/.test(domain);
    const isIPv4 = /^(\d{1,3}\.){3}\d{1,3}$/.test(domain);
    const isIPv6 = /^([0-9a-fA-F]{1,4}:){7}[0-9a-fA-F]{1,4}$/.test(domain) ||
                  /^([0-9a-fA-F]{1,4}:){1,7}:$/.test(domain) ||
                  /^:((:[0-9a-fA-F]{1,4}){1,7}|:)$/.test(domain) ||
                  /^([0-9a-fA-F]{1,4}:){1,7}(:[0-9a-fA-F]{1,4}){1,7}$/.test(domain);

    if (recordType === 'PTR' && !isIPv4 && !isIPv6) {
      hideLoading();
      showError('For PTR (reverse lookup), please enter a valid IP address.');
      return;
    }

    if (recordType !== 'PTR' && !isDomain) {
      hideLoading();
      showError('Please enter a valid domain name (e.g., example.com).');
      return;
    }

    // Create API URL
    const apiUrl = `/api/dns-lookup.php?domain=${encodeURIComponent(domain)}&type=${encodeURIComponent(recordType)}`;

    // Make API request
    fetch(apiUrl)
      .then(response => {
        if (!response.ok) {
          throw new Error(`HTTP error! Status: ${response.status}`);
        }
        return response.json().catch(e => {
          // Handle JSON parsing errors
          throw new Error('Invalid response format from server. Please try again.');
        });
      })
      .then(data => {
        hideLoading();

        if (data.error) {
          showError(data.message || 'An error occurred during DNS lookup.');
          return;
        }

        displayDnsResults(data);
      })
      .catch(error => {
        hideLoading();
        showError('Failed to perform DNS lookup: ' + error.message);
        console.error('DNS lookup error:', error);
      });
  }

  /**
   * Display DNS lookup results
   */
  function displayDnsResults(data) {
    // Set domain name in results
    resultDomainSpan.textContent = data.domain;

    // Clear previous results
    dnsRecordsDiv.innerHTML = '';

    // Check if we have records
    if (!data.records || data.records.length === 0) {
      dnsRecordsDiv.innerHTML = '<div class="p-3 text-center">No DNS records found for the specified type.</div>';
      showResults();
      return;
    }

    // Process each record
    data.records.forEach(record => {
      const recordItem = document.createElement('div');
      recordItem.className = 'dns-record-item';

      // Create record type badge
      const recordType = document.createElement('span');
      recordType.className = `dns-record-type ${record.type.toLowerCase()}`;
      recordType.textContent = record.type;
      recordItem.appendChild(recordType);

      // Host name (if available)
      if (record.host) {
        const hostSpan = document.createElement('span');
        hostSpan.textContent = record.host;
        recordItem.appendChild(hostSpan);
      }

      // Create record content container
      const recordContent = document.createElement('div');
      recordContent.className = 'dns-record-content';

      // Add record-specific content based on type
      switch (record.type) {
        case 'A':
        case 'AAAA':
          addProperty(recordContent, 'IP', record.ip || record.ipv6 || 'N/A');
          addProperty(recordContent, 'TTL', record.ttl || 'N/A');
          break;

        case 'MX':
          addProperty(recordContent, 'Target', record.target || 'N/A');
          addProperty(recordContent, 'Priority', record.pri || record.priority || 'N/A');
          addProperty(recordContent, 'TTL', record.ttl || 'N/A');
          break;

        case 'NS':
          addProperty(recordContent, 'Target', record.target || 'N/A');
          addProperty(recordContent, 'TTL', record.ttl || 'N/A');
          break;

        case 'TXT':
          addProperty(recordContent, 'Text', record.txt || record.entries?.join('<br>') || 'N/A');
          addProperty(recordContent, 'TTL', record.ttl || 'N/A');
          break;

        case 'CNAME':
          addProperty(recordContent, 'Target', record.target || 'N/A');
          addProperty(recordContent, 'TTL', record.ttl || 'N/A');
          break;

        case 'SOA':
          addProperty(recordContent, 'MName', record.mname || 'N/A');
          addProperty(recordContent, 'RName', record.rname || 'N/A');
          addProperty(recordContent, 'Serial', record.serial || 'N/A');
          addProperty(recordContent, 'Refresh', record.refresh || 'N/A');
          addProperty(recordContent, 'Retry', record.retry || 'N/A');
          addProperty(recordContent, 'Expire', record.expire || 'N/A');
          addProperty(recordContent, 'Minimum', record.minimum || 'N/A');
          addProperty(recordContent, 'TTL', record.ttl || 'N/A');
          break;

        case 'PTR':
          addProperty(recordContent, 'Target', record.target || 'N/A');
          addProperty(recordContent, 'TTL', record.ttl || 'N/A');
          break;

        case 'SRV':
          addProperty(recordContent, 'Target', record.target || 'N/A');
          addProperty(recordContent, 'Priority', record.pri || record.priority || 'N/A');
          addProperty(recordContent, 'Weight', record.weight || 'N/A');
          addProperty(recordContent, 'Port', record.port || 'N/A');
          addProperty(recordContent, 'TTL', record.ttl || 'N/A');
          break;

        case 'CAA':
          addProperty(recordContent, 'Flags', record.flags || 'N/A');
          addProperty(recordContent, 'Tag', record.tag || 'N/A');
          addProperty(recordContent, 'Value', record.value || 'N/A');
          addProperty(recordContent, 'TTL', record.ttl || 'N/A');
          break;

        default:
          // For all other record types, add all properties
          Object.keys(record).forEach(key => {
            if (key !== 'type' && key !== 'host') {
              addProperty(recordContent, key, record[key]);
            }
          });
          break;
      }

      recordItem.appendChild(recordContent);
      dnsRecordsDiv.appendChild(recordItem);
    });

    // Show results
    showResults();

    // Create visualization if enabled
    createVisualization(data);
  }

  /**
   * Helper to add a property to the record content
   */
  function addProperty(container, name, value) {
    const propName = document.createElement('div');
    propName.className = 'dns-property';
    propName.textContent = name;

    const propValue = document.createElement('div');
    propValue.className = 'dns-value';
    propValue.innerHTML = value; // Using innerHTML to support line breaks

    container.appendChild(propName);
    container.appendChild(propValue);
  }

  /**
   * Create a visualization for DNS record relationships
   */
  function createVisualization(data) {
    dnsVisualizationDiv.innerHTML = '';
    
    if (!data.records || data.records.length === 0) {
      const noRecordsMsg = document.createElement('div');
      noRecordsMsg.className = 'text-center p-5';
      noRecordsMsg.innerHTML = `
        <i class="fas fa-search fa-3x mb-3" style="opacity: 0.5;"></i>
        <p>No DNS records found for ${data.domain} with type ${data.type}.</p>
      `;
      dnsVisualizationDiv.appendChild(noRecordsMsg);
      return;
    }
    
    // Create container for the visualization
    const visualContainer = document.createElement('div');
    visualContainer.className = 'dns-visualization-container';
    
    // Create domain node (center)
    const domainNode = document.createElement('div');
    domainNode.className = 'dns-node dns-domain-node';
    domainNode.innerHTML = `
      <i class="fas fa-globe mb-2"></i>
      <div class="dns-node-label">${data.domain}</div>
    `;
    
    // Create record groups by type
    const recordsByType = {};
    data.records.forEach(record => {
      if (!recordsByType[record.type]) {
        recordsByType[record.type] = [];
      }
      recordsByType[record.type].push(record);
    });
    
    // Create the record nodes grouped by type
    const recordGroupsContainer = document.createElement('div');
    recordGroupsContainer.className = 'dns-record-groups';
    
    // Add domain node to the container
    const domainContainer = document.createElement('div');
    domainContainer.className = 'dns-domain-container';
    domainContainer.appendChild(domainNode);
    visualContainer.appendChild(domainContainer);
    
    // Add record groups
    Object.keys(recordsByType).forEach((type, index) => {
      const records = recordsByType[type];
      const typeGroup = document.createElement('div');
      typeGroup.className = 'dns-type-group';
      
      // Type header
      const typeHeader = document.createElement('div');
      typeHeader.className = 'dns-type-header';
      typeHeader.innerHTML = `
        <span class="dns-record-type ${type.toLowerCase()}">${type}</span>
        <span class="dns-type-count">${records.length} record(s)</span>
      `;
      typeGroup.appendChild(typeHeader);
      
      // Record nodes
      const recordNodes = document.createElement('div');
      recordNodes.className = 'dns-record-nodes';
      
      records.forEach(record => {
        const recordNode = document.createElement('div');
        recordNode.className = 'dns-record-node';
        
        // Determine what to display based on record type
        let displayValue = '';
        switch (record.type) {
          case 'A':
          case 'AAAA':
            displayValue = record.ip || record.ipv6 || 'N/A';
            recordNode.innerHTML = `
              <i class="fas fa-server"></i>
              <div class="dns-node-value">${displayValue}</div>
            `;
            break;
            
          case 'MX':
            displayValue = `${record.target || 'N/A'} (${record.pri || record.priority || 'N/A'})`;
            recordNode.innerHTML = `
              <i class="fas fa-mail-bulk"></i>
              <div class="dns-node-value">${displayValue}</div>
            `;
            break;
            
          case 'NS':
            displayValue = record.target || 'N/A';
            recordNode.innerHTML = `
              <i class="fas fa-database"></i>
              <div class="dns-node-value">${displayValue}</div>
            `;
            break;
            
          case 'TXT':
            displayValue = record.txt || (record.entries ? record.entries[0] : 'N/A');
            // Truncate if too long
            if (displayValue && displayValue.length > 30) {
              displayValue = displayValue.substring(0, 27) + '...';
            }
            recordNode.innerHTML = `
              <i class="fas fa-file-alt"></i>
              <div class="dns-node-value">${displayValue}</div>
            `;
            break;
            
          case 'CNAME':
            displayValue = record.target || 'N/A';
            recordNode.innerHTML = `
              <i class="fas fa-link"></i>
              <div class="dns-node-value">${displayValue}</div>
            `;
            break;
            
          case 'PTR':
            displayValue = record.target || 'N/A';
            recordNode.innerHTML = `
              <i class="fas fa-undo"></i>
              <div class="dns-node-value">${displayValue}</div>
            `;
            break;
            
          default:
            displayValue = record.host || record.target || 'N/A';
            recordNode.innerHTML = `
              <i class="fas fa-cog"></i>
              <div class="dns-node-value">${displayValue}</div>
            `;
        }
        
        recordNodes.appendChild(recordNode);
      });
      
      typeGroup.appendChild(recordNodes);
      recordGroupsContainer.appendChild(typeGroup);
    });
    
    visualContainer.appendChild(recordGroupsContainer);
    dnsVisualizationDiv.appendChild(visualContainer);
  }

  /**
   * Toggle between records view and visualization
   */
  function toggleVisualization() {
    const isVisualizationVisible = !dnsVisualizationDiv.classList.contains('d-none');
    const toggleText = toggleVisualizationBtn.querySelector('span');

    if (isVisualizationVisible) {
      // Switch to records view
      dnsVisualizationDiv.classList.add('d-none');
      dnsRecordsDiv.parentElement.classList.remove('d-none');
      toggleText.textContent = 'Show Visualization';
    } else {
      // Switch to visualization view
      dnsVisualizationDiv.classList.remove('d-none');
      dnsRecordsDiv.parentElement.classList.add('d-none');
      toggleText.textContent = 'Show Records';
    }
  }

  /**
   * Clear the DNS lookup form
   */
  function clearDnsForm() {
    domainInput.value = '';
    recordTypeSelect.value = 'A';
    hideResults();
    hideError();
    domainInput.focus();
  }

  /**
   * Copy DNS results to clipboard
   */
  function copyDnsResults() {
    let resultsText = `DNS Lookup Results for ${resultDomainSpan.textContent}\n`;
    resultsText += `Record Type: ${recordTypeSelect.options[recordTypeSelect.selectedIndex].text}\n\n`;

    const recordItems = dnsRecordsDiv.querySelectorAll('.dns-record-item');

    recordItems.forEach((item, index) => {
      const type = item.querySelector('.dns-record-type').textContent;
      resultsText += `Record ${index + 1} [${type}]:\n`;

      const properties = item.querySelectorAll('.dns-property');
      const values = item.querySelectorAll('.dns-value');

      for (let i = 0; i < properties.length; i++) {
        resultsText += `${properties[i].textContent}: ${values[i].textContent}\n`;
      }

      resultsText += '\n';
    });

    navigator.clipboard.writeText(resultsText.trim())
      .then(() => {
        // Show copied notification
        const originalText = copyResultsBtn.innerHTML;
        copyResultsBtn.innerHTML = '<i class="fas fa-check me-2"></i>Copied!';

        setTimeout(() => {
          copyResultsBtn.innerHTML = originalText;
        }, 2000);
      })
      .catch(err => {
        console.error('Could not copy text: ', err);
        showError('Failed to copy results to clipboard.');
      });
  }

  /**
   * Show error message
   */
  function showError(message) {
    errorText.textContent = message;
    errorDiv.classList.remove('d-none');
  }

  /**
   * Hide error message
   */
  function hideError() {
    errorDiv.classList.add('d-none');
  }

  /**
   * Show results container
   */
  function showResults() {
    resultsDiv.classList.remove('d-none');
  }

  /**
   * Hide results container
   */
  function hideResults() {
    resultsDiv.classList.add('d-none');
  }

  /**
   * Show loading indicator
   */
  function showLoading() {
    loadingDiv.classList.remove('d-none');
  }

  /**
   * Hide loading indicator
   */
  function hideLoading() {
    loadingDiv.classList.add('d-none');
  }
}

// The DNS lookup is now initialized in the above DOMContentLoaded event
// along with the subnet calculator

/**
 * Security Headers Checker functionality
 * Analyzes security headers for websites and provides recommendations
 */
function setupSecurityHeadersChecker() {
  // Get DOM elements
  const websiteUrlInput = document.getElementById('websiteUrl');
  const checkHeadersBtn = document.getElementById('checkHeadersBtn');
  const clearHeadersBtn = document.getElementById('clearHeadersBtn');
  const copyHeadersBtn = document.getElementById('copyHeadersBtn');
  const resultsDiv = document.getElementById('headerResults');
  const headersList = document.getElementById('headersList');
  const recommendationsList = document.getElementById('recommendationsList');
  const resultWebsiteSpan = document.getElementById('resultWebsite');
  const headerScoreBadge = document.getElementById('headerScore');
  const errorDiv = document.getElementById('headerError');
  const errorText = document.getElementById('headerErrorText');
  const loadingDiv = document.getElementById('headerLoading');

  // Key security headers we want to check
  const securityHeaders = [
    {
      name: 'Content-Security-Policy',
      description: 'Controls which resources can be loaded and executed on your page',
      importance: 'high',
      recommendation: 'Implement a strong Content-Security-Policy to prevent XSS attacks by restricting which resources can load.'
    },
    {
      name: 'Strict-Transport-Security',
      description: 'Forces browsers to use HTTPS for communication with your site',
      importance: 'high',
      recommendation: 'Add Strict-Transport-Security header with a long max-age value (e.g., 31536000) to ensure HTTPS usage.'
    },
    {
      name: 'X-Content-Type-Options',
      description: 'Prevents browsers from MIME-sniffing content types',
      importance: 'medium',
      recommendation: 'Add X-Content-Type-Options: nosniff to prevent MIME type sniffing.'
    },
    {
      name: 'X-Frame-Options',
      description: 'Controls if a page can be embedded in frames, iframes, or objects',
      importance: 'medium',
      recommendation: 'Add X-Frame-Options: SAMEORIGIN to protect against clickjacking attacks.'
    },
    {
      name: 'X-XSS-Protection',
      description: 'Enables browser\'s built-in XSS protection features',
      importance: 'medium',
      recommendation: 'Add X-XSS-Protection: 1; mode=block to enable browser XSS protection (note: modern browsers rely more on CSP).'
    },
    {
      name: 'Referrer-Policy',
      description: 'Controls how much referrer information is included with requests',
      importance: 'medium',
      recommendation: 'Add Referrer-Policy with a restrictive value like strict-origin-when-cross-origin to control referrer information.'
    },
    {
      name: 'Permissions-Policy',
      description: 'Controls which browser features and APIs can be used on the site',
      importance: 'low',
      recommendation: 'Consider adding a Permissions-Policy to restrict browser features like geolocation, microphone, and camera access.'
    },
    {
      name: 'Cache-Control',
      description: 'Defines browser and proxy caching policies',
      importance: 'low',
      recommendation: 'Add a Cache-Control header with appropriate values like no-store or private for sensitive pages.'
    }
  ];

  // Set up event listeners
  if (checkHeadersBtn) {
    checkHeadersBtn.addEventListener('click', checkHeaders);
  }

  if (clearHeadersBtn) {
    clearHeadersBtn.addEventListener('click', clearHeadersForm);
  }

  if (copyHeadersBtn) {
    copyHeadersBtn.addEventListener('click', copyHeadersResults);
  }

  // Input validation with Enter key
  if (websiteUrlInput) {
    websiteUrlInput.addEventListener('keyup', function(e) {
      if (e.key === 'Enter') {
        checkHeaders();
      }
    });
  }

  /**
   * Check security headers for the specified URL
   */
  function checkHeaders() {
    // Clear previous results and errors
    hideError();
    hideResults();
    showLoading();

    // Get URL from input
    let website = websiteUrlInput.value.trim();

    // Basic validation
    if (!website) {
      hideLoading();
      showError('Please enter a website URL.');
      return;
    }

    // Less strict domain validation (allows subdomains, www, etc.)
    const urlRegex = /^[a-zA-Z0-9][-a-zA-Z0-9.]*\.[a-zA-Z]{2,}$/;
    
    // Remove any protocol if the user included it
    if (website.startsWith('http://') || website.startsWith('https://')) {
      const url = new URL(website);
      website = url.hostname;
    }
    
    if (!urlRegex.test(website)) {
      hideLoading();
      showError('Please enter a valid domain name (e.g., example.com, www.example.org).');
      return;
    }

    // Create API URL
    const apiUrl = `/api/security-headers.php?url=${encodeURIComponent(website)}`;

    // Make API request
    fetch(apiUrl)
      .then(response => {
        if (!response.ok) {
          throw new Error(`HTTP error! Status: ${response.status}`);
        }
        return response.json().catch(e => {
          throw new Error('Invalid response format from server. Please try again.');
        });
      })
      .then(data => {
        hideLoading();

        if (data.error) {
          showError(data.message || 'An error occurred while checking headers.');
          return;
        }

        displayHeaderResults(data, website);
      })
      .catch(error => {
        hideLoading();
        showError('Failed to check security headers: ' + error.message);
        console.error('Security headers check error:', error);
      });
  }

  /**
   * Display security headers results
   */
  function displayHeaderResults(data, website) {
    // Clear previous results
    headersList.innerHTML = '';
    recommendationsList.innerHTML = '';
    
    // Display the website
    resultWebsiteSpan.textContent = website;
    
    // Process headers
    let totalScore = 0;
    let maxScore = 0;
    const missingHeaders = [];
    
    // Create header items
    securityHeaders.forEach(header => {
      // Calculate weight based on importance
      let weight = 1;
      if (header.importance === 'high') weight = 3;
      else if (header.importance === 'medium') weight = 2;
      
      maxScore += weight;
      
      // Create header item
      const headerItem = document.createElement('div');
      const headerValue = data.headers[header.name.toLowerCase()] || null;
      
      if (headerValue) {
        // Header is present
        headerItem.className = 'header-item present';
        totalScore += weight;
        
        // Basic content analysis for common headers
        let warningMessage = null;
        if (header.name === 'Content-Security-Policy' && headerValue.includes('unsafe-inline')) {
          headerItem.className = 'header-item warning';
          warningMessage = 'Warning: unsafe-inline reduces the security of your CSP.';
          totalScore -= weight / 2; // Partial point deduction
        } else if (header.name === 'Strict-Transport-Security') {
          const maxAgeMatch = headerValue.match(/max-age=(\d+)/i);
          if (maxAgeMatch && parseInt(maxAgeMatch[1]) < 31536000) {
            headerItem.className = 'header-item warning';
            warningMessage = 'Warning: max-age is less than the recommended value of 31536000 (1 year).';
            totalScore -= weight / 3; // Partial point deduction
          }
        }
        
        headerItem.innerHTML = `
          <div class="header-name">
            ${header.name}
            <span class="header-status present">${warningMessage ? 'Warning' : 'Present'}</span>
          </div>
          <div class="header-value">${headerValue}</div>
          <div class="header-description">
            ${header.description}
            ${warningMessage ? '<br><strong>' + warningMessage + '</strong>' : ''}
          </div>
        `;
      } else {
        // Header is missing
        headerItem.className = 'header-item missing';
        missingHeaders.push(header);
        
        headerItem.innerHTML = `
          <div class="header-name">
            ${header.name}
            <span class="header-status missing">Missing</span>
          </div>
          <div class="header-description">${header.description}</div>
        `;
      }
      
      headersList.appendChild(headerItem);
    });
    
    // Calculate final score (0-100 scale)
    const finalScore = Math.round((totalScore / maxScore) * 100);
    
    // Update score badge
    headerScoreBadge.textContent = `${finalScore}/100`;
    
    // Update score color based on value
    if (finalScore >= 80) {
      headerScoreBadge.className = 'badge bg-success me-2';
    } else if (finalScore >= 50) {
      headerScoreBadge.className = 'badge bg-warning text-dark me-2';
    } else {
      headerScoreBadge.className = 'badge bg-danger me-2';
    }
    
    // Log the score and headers for debugging
    console.log(`Security Score: ${finalScore}/100`);
    console.log('Security Headers:', data.headers);
    
    // Add recommendations
    if (missingHeaders.length > 0) {
      missingHeaders.forEach(header => {
        const recommendationItem = document.createElement('div');
        recommendationItem.className = 'recommendation-item';
        recommendationItem.innerHTML = `
          <div class="recommendation-title">Add ${header.name}</div>
          <div class="recommendation-text">${header.recommendation}</div>
        `;
        recommendationsList.appendChild(recommendationItem);
      });
    } else {
      recommendationsList.innerHTML = '<div class="p-3 text-center">Great job! All important security headers are implemented.</div>';
    }
    
    // Show results
    showResults();
  }

  /**
   * Clear the form
   */
  function clearHeadersForm() {
    websiteUrlInput.value = '';
    hideResults();
    hideError();
    websiteUrlInput.focus();
  }

  /**
   * Copy headers results to clipboard
   */
  function copyHeadersResults() {
    let resultsText = `Security Headers Report for ${resultWebsiteSpan.textContent}\n`;
    resultsText += `Score: ${headerScoreBadge.textContent}\n\n`;
    
    // Headers
    const headerItems = headersList.querySelectorAll('.header-item');
    headerItems.forEach(item => {
      const headerName = item.querySelector('.header-name').textContent.trim();
      const headerStatus = item.querySelector('.header-status').textContent.trim();
      
      resultsText += `${headerName}: ${headerStatus}\n`;
      
      const headerValue = item.querySelector('.header-value');
      if (headerValue) {
        resultsText += `Value: ${headerValue.textContent.trim()}\n`;
      }
      
      const headerDesc = item.querySelector('.header-description').textContent.trim();
      resultsText += `${headerDesc}\n\n`;
    });
    
    // Recommendations
    resultsText += 'Recommendations:\n';
    const recommendations = recommendationsList.querySelectorAll('.recommendation-item');
    
    if (recommendations.length > 0) {
      recommendations.forEach(rec => {
        const title = rec.querySelector('.recommendation-title').textContent.trim();
        const text = rec.querySelector('.recommendation-text').textContent.trim();
        resultsText += `- ${title}: ${text}\n`;
      });
    } else {
      resultsText += 'Great job! All important security headers are implemented.\n';
    }
    
    navigator.clipboard.writeText(resultsText.trim())
      .then(() => {
        // Show copied notification
        const originalText = copyHeadersBtn.innerHTML;
        copyHeadersBtn.innerHTML = '<i class="fas fa-check me-1"></i>Copied!';
        
        setTimeout(() => {
          copyHeadersBtn.innerHTML = originalText;
        }, 2000);
      })
      .catch(err => {
        console.error('Could not copy text: ', err);
        showError('Failed to copy results to clipboard.');
      });
  }

  /**
   * Show error message
   */
  function showError(message) {
    errorText.textContent = message;
    errorDiv.classList.remove('d-none');
  }

  /**
   * Hide error message
   */
  function hideError() {
    errorDiv.classList.add('d-none');
  }

  /**
   * Show results container
   */
  function showResults() {
    resultsDiv.classList.remove('d-none');
  }

  /**
   * Hide results container
   */
  function hideResults() {
    resultsDiv.classList.add('d-none');
  }

  /**
   * Show loading indicator
   */
  function showLoading() {
    loadingDiv.classList.remove('d-none');
  }

  /**
   * Hide loading indicator
   */
  function hideLoading() {
    loadingDiv.classList.add('d-none');
  }
}

/**
 * Ping/Traceroute Tool functionality
 * Implements network diagnostic tool with visualization
 */
function setupPingTraceroute() {
  console.log('Setting up Ping/Traceroute tool...');
  
  // Get DOM elements
  const hostTargetInput = document.getElementById('hostTarget');
  const toolTypeSelect = document.getElementById('toolType');
  const packetCountInput = document.getElementById('packetCount');
  const packetSizeInput = document.getElementById('packetSize');
  const timeoutInput = document.getElementById('timeout');
  const runBtn = document.getElementById('runNetworkToolBtn');
  const clearBtn = document.getElementById('clearNetworkToolBtn');
  const copyResultsBtn = document.getElementById('copyNetworkToolBtn');
  const resultsDiv = document.getElementById('networkToolResults');
  const resultOutput = document.getElementById('resultOutput');
  const resultToolType = document.getElementById('resultToolType');
  const resultHost = document.getElementById('resultHost');
  const visualTab = document.getElementById('visualTab');
  const tracerouteVisualization = document.getElementById('tracerouteVisualization');
  const errorDiv = document.getElementById('networkToolError');
  const errorText = document.getElementById('networkToolErrorText');
  const loadingDiv = document.getElementById('networkToolLoading');
  const loadingToolType = document.getElementById('loadingToolType');
  const statsHost = document.getElementById('statsHost');
  const statsPackets = document.getElementById('statsPackets');
  const statsSuccess = document.getElementById('statsSuccess');
  const statsIp = document.getElementById('statsIp');
  const statsMin = document.getElementById('statsMin');
  const statsAvg = document.getElementById('statsAvg');
  const statsMax = document.getElementById('statsMax');
  const statsStdDev = document.getElementById('statsStdDev');

  // Debug - check if elements exist
  console.log('Run button exists:', !!runBtn);
  console.log('Host input exists:', !!hostTargetInput);

  // Set up event listeners
  if (runBtn) {
    runBtn.addEventListener('click', runNetworkTool);
  }

  if (clearBtn) {
    clearBtn.addEventListener('click', clearNetworkForm);
  }

  if (copyResultsBtn) {
    copyResultsBtn.addEventListener('click', copyNetworkResults);
  }

  // Input validation with Enter key
  if (hostTargetInput) {
    hostTargetInput.addEventListener('keyup', function(e) {
      if (e.key === 'Enter') {
        runNetworkTool();
      }
    });
  }
  
  // Show/hide visualization tab based on tool type
  if (toolTypeSelect) {
    toolTypeSelect.addEventListener('change', function() {
      updateToolOptions(this.value);
    });
  }

  /**
   * Update UI based on selected tool type
   */
  function updateToolOptions(toolType) {
    // For ping, we may want to adjust packet counts
    if (toolType === 'ping') {
      packetCountInput.max = 20;
      if (parseInt(packetCountInput.value) > 20) {
        packetCountInput.value = 20;
      }
    } else if (toolType === 'traceroute') {
      packetCountInput.max = 3;
      if (parseInt(packetCountInput.value) > 3) {
        packetCountInput.value = 3;
      }
    } else if (toolType === 'mtr') {
      packetCountInput.max = 10;
      if (parseInt(packetCountInput.value) > 10) {
        packetCountInput.value = 10;
      }
    }
  }

  /**
   * Run the network diagnostic tool
   */
  function runNetworkTool() {
    // Clear previous results and errors
    hideError();
    hideResults();
    showLoading();

    // Get input values
    const host = hostTargetInput.value.trim();
    const toolType = toolTypeSelect.value;
    const packetCount = parseInt(packetCountInput.value);
    const packetSize = parseInt(packetSizeInput.value);
    const timeout = parseInt(timeoutInput.value);

    // Update loading message
    loadingToolType.textContent = toolType;

    // Basic validation
    if (!host) {
      hideLoading();
      showError('Please enter a domain name or IP address.');
      return;
    }

    // Validate packet count
    if (isNaN(packetCount) || packetCount < 1) {
      hideLoading();
      showError('Packet count must be at least 1.');
      return;
    }

    // Validate packet size
    if (isNaN(packetSize) || packetSize < 32 || packetSize > 1472) {
      hideLoading();
      showError('Packet size must be between 32 and 1472 bytes.');
      return;
    }

    // Validate timeout
    if (isNaN(timeout) || timeout < 1 || timeout > 10) {
      hideLoading();
      showError('Timeout must be between 1 and 10 seconds.');
      return;
    }

    // Simulate a network request
    // In a real implementation, this would call the API endpoint
    simulateNetworkTool(host, toolType, packetCount, packetSize, timeout);
  }

  /**
   * Simulate network tool response (for development purposes)
   * This will be replaced with actual API calls in production
   */
  function simulateNetworkTool(host, toolType, packetCount, packetSize, timeout) {
    // For demonstration purposes, we'll simulate a response
    // This would normally be a fetch() call to the API
    
    setTimeout(() => {
      let simulatedOutput = '';
      let successRate = 0;
      let minLatency = 0;
      let avgLatency = 0;
      let maxLatency = 0;
      let stdDev = 0;
      
      // Generate different outputs based on tool type
      if (toolType === 'ping') {
        const pingResults = generatePingSimulation(host, packetCount, packetSize);
        simulatedOutput = pingResults.output;
        successRate = pingResults.successRate;
        minLatency = pingResults.minLatency;
        avgLatency = pingResults.avgLatency;
        maxLatency = pingResults.maxLatency;
        stdDev = pingResults.stdDev;
        
        // Hide visualization tab for ping
        visualTab.classList.add('d-none');
      } 
      else if (toolType === 'traceroute') {
        const traceResults = generateTracerouteSimulation(host);
        simulatedOutput = traceResults.output;
        successRate = traceResults.successRate;
        minLatency = traceResults.minLatency;
        avgLatency = traceResults.avgLatency;
        maxLatency = traceResults.maxLatency;
        stdDev = traceResults.stdDev;
        
        // Show visualization tab for traceroute
        visualTab.classList.remove('d-none');
        
        // Create visualization
        createTracerouteVisualization(traceResults.hops);
      }
      else if (toolType === 'mtr') {
        const mtrResults = generateMtrSimulation(host, packetCount);
        simulatedOutput = mtrResults.output;
        successRate = mtrResults.successRate;
        minLatency = mtrResults.minLatency;
        avgLatency = mtrResults.avgLatency;
        maxLatency = mtrResults.maxLatency;
        stdDev = mtrResults.stdDev;
        
        // Show visualization tab for mtr
        visualTab.classList.remove('d-none');
        
        // Create visualization
        createTracerouteVisualization(mtrResults.hops);
      }
      
      // Display results
      hideLoading();
      displayNetworkResults(host, toolType.toUpperCase(), simulatedOutput, {
        successRate: successRate,
        ip: generateRandomIp(),
        minLatency: minLatency,
        avgLatency: avgLatency,
        maxLatency: maxLatency,
        stdDev: stdDev,
        packetCount: packetCount
      });
      
    }, 2000); // Simulate network delay
  }

  /**
   * Generate simulated ping results
   */
  function generatePingSimulation(host, packetCount, packetSize) {
    let output = `PING ${host} (${generateRandomIp()}): ${packetSize} data bytes\n`;
    let successCount = 0;
    const latencies = [];
    
    for (let i = 0; i < packetCount; i++) {
      // 10% chance of packet loss
      if (Math.random() > 0.1) {
        const latency = Math.round(20 + Math.random() * 80);
        latencies.push(latency);
        output += `${packetSize} bytes from ${generateRandomIp()}: icmp_seq=${i+1} ttl=64 time=${latency}.${Math.floor(Math.random() * 1000)} ms\n`;
        successCount++;
      } else {
        output += `Request timeout for icmp_seq ${i+1}\n`;
      }
    }
    
    // Add statistics
    const successRate = successCount / packetCount * 100;
    const minLatency = Math.min(...latencies) || 0;
    const maxLatency = Math.max(...latencies) || 0;
    const avgLatency = latencies.length ? latencies.reduce((a, b) => a + b, 0) / latencies.length : 0;
    
    // Calculate standard deviation
    let stdDev = 0;
    if (latencies.length) {
      const squareDiffs = latencies.map(value => {
        const diff = value - avgLatency;
        return diff * diff;
      });
      const avgSquareDiff = squareDiffs.reduce((a, b) => a + b, 0) / squareDiffs.length;
      stdDev = Math.sqrt(avgSquareDiff);
    }
    
    output += `\n--- ${host} ping statistics ---\n`;
    output += `${packetCount} packets transmitted, ${successCount} packets received, ${Math.round((packetCount - successCount) / packetCount * 100)}% packet loss\n`;
    output += `round-trip min/avg/max/stddev = ${minLatency.toFixed(1)}/${avgLatency.toFixed(1)}/${maxLatency.toFixed(1)}/${stdDev.toFixed(1)} ms\n`;
    
    return {
      output: output,
      successRate: successRate,
      minLatency: minLatency,
      avgLatency: avgLatency,
      maxLatency: maxLatency,
      stdDev: stdDev
    };
  }

  /**
   * Generate simulated traceroute results
   */
  function generateTracerouteSimulation(host) {
    let output = `traceroute to ${host} (${generateRandomIp()}), 30 hops max, 60 byte packets\n`;
    const hops = [];
    const hopCount = Math.floor(5 + Math.random() * 10); // 5-15 hops
    const latencies = [];
    
    for (let i = 1; i <= hopCount; i++) {
      const latency1 = Math.round(20 + Math.random() * 80);
      const latency2 = Math.round(20 + Math.random() * 80);
      const latency3 = Math.round(20 + Math.random() * 80);
      const avgLatency = (latency1 + latency2 + latency3) / 3;
      latencies.push(avgLatency);
      
      const ip = generateRandomIp();
      let hostname = '';
      
      // Generate realistic hostnames for different hop positions
      if (i === 1) {
        hostname = 'local-gateway.net';
      } else if (i === hopCount) {
        hostname = host;
      } else if (i === 2) {
        hostname = 'isp-edge-router.net';
      } else if (i < 4) {
        hostname = `isp-core-${Math.floor(Math.random() * 100)}.net`;
      } else {
        const providers = ['level3', 'cogent', 'telia', 'ntt', 'hurricane'];
        const provider = providers[Math.floor(Math.random() * providers.length)];
        hostname = `${provider}-transit-${Math.floor(Math.random() * 100)}.net`;
      }
      
      output += `${i}  ${hostname} (${ip})  ${latency1.toFixed(1)} ms  ${latency2.toFixed(1)} ms  ${latency3.toFixed(1)} ms\n`;
      
      hops.push({
        hop: i,
        hostname: hostname,
        ip: ip,
        latency1: latency1,
        latency2: latency2,
        latency3: latency3,
        avgLatency: avgLatency
      });
    }
    
    // Calculate statistics
    const minLatency = Math.min(...latencies);
    const maxLatency = Math.max(...latencies);
    const avgLatency = latencies.reduce((a, b) => a + b, 0) / latencies.length;
    
    // Calculate standard deviation
    let stdDev = 0;
    if (latencies.length) {
      const squareDiffs = latencies.map(value => {
        const diff = value - avgLatency;
        return diff * diff;
      });
      const avgSquareDiff = squareDiffs.reduce((a, b) => a + b, 0) / squareDiffs.length;
      stdDev = Math.sqrt(avgSquareDiff);
    }
    
    return {
      output: output,
      hops: hops,
      successRate: 100, // Traceroute typically completes if the final hop is reached
      minLatency: minLatency,
      avgLatency: avgLatency,
      maxLatency: maxLatency,
      stdDev: stdDev
    };
  }

  /**
   * Generate simulated MTR results
   */
  function generateMtrSimulation(host, packetCount) {
    let output = `Start: ${new Date().toISOString().replace('T', ' ').substring(0, 19)}, Host: ${host}\n`;
    output += `HOST: ${navigator.userAgent}             Loss%   Snt   Last   Avg  Best  Wrst StDev\n`;
    
    const hops = [];
    const hopCount = Math.floor(5 + Math.random() * 10); // 5-15 hops
    const latencies = [];
    
    for (let i = 1; i <= hopCount; i++) {
      const loss = Math.random() > 0.9 ? Math.floor(Math.random() * 50) : 0;
      const sent = packetCount;
      const last = Math.round(20 + Math.random() * 80);
      const best = Math.round(last * 0.8);
      const worst = Math.round(last * 1.2);
      const avg = Math.round((last + best + worst) / 3);
      const stdDev = Math.round((worst - best) / 4);
      
      latencies.push(avg);
      
      const ip = generateRandomIp();
      let hostname = '';
      
      // Generate realistic hostnames
      if (i === 1) {
        hostname = 'local-gateway.net';
      } else if (i === hopCount) {
        hostname = host;
      } else if (i === 2) {
        hostname = 'isp-edge-router.net';
      } else if (i < 4) {
        hostname = `isp-core-${Math.floor(Math.random() * 100)}.net`;
      } else {
        const providers = ['level3', 'cogent', 'telia', 'ntt', 'hurricane'];
        const provider = providers[Math.floor(Math.random() * providers.length)];
        hostname = `${provider}-transit-${Math.floor(Math.random() * 100)}.net`;
      }
      
      output += `${i}. ${hostname} (${ip})  ${loss}%    ${sent}   ${last}   ${avg}   ${best}   ${worst}  ${stdDev}\n`;
      
      hops.push({
        hop: i,
        hostname: hostname,
        ip: ip,
        loss: loss,
        sent: sent,
        last: last,
        avg: avg,
        best: best,
        worst: worst,
        stdDev: stdDev
      });
    }
    
    // Calculate statistics
    const minLatency = Math.min(...latencies);
    const maxLatency = Math.max(...latencies);
    const avgLatency = latencies.reduce((a, b) => a + b, 0) / latencies.length;
    
    // Calculate standard deviation
    let stdDev = 0;
    if (latencies.length) {
      const squareDiffs = latencies.map(value => {
        const diff = value - avgLatency;
        return diff * diff;
      });
      const avgSquareDiff = squareDiffs.reduce((a, b) => a + b, 0) / squareDiffs.length;
      stdDev = Math.sqrt(avgSquareDiff);
    }
    
    const successRate = 100 - (hops[hops.length - 1].loss || 0);
    
    return {
      output: output,
      hops: hops,
      successRate: successRate,
      minLatency: minLatency,
      avgLatency: avgLatency,
      maxLatency: maxLatency,
      stdDev: stdDev
    };
  }

  /**
   * Create visualization for traceroute results
   */
  function createTracerouteVisualization(hops) {
    tracerouteVisualization.innerHTML = '';
    
    if (!hops || hops.length === 0) {
      tracerouteVisualization.innerHTML = `
        <div class="text-center p-5">
          <i class="fas fa-map-marked-alt fa-3x mb-3" style="opacity: 0.5;"></i>
          <p>No route information available.</p>
        </div>`;
      return;
    }
    
    // Find highest latency for scaling
    let maxLatency = 0;
    hops.forEach(hop => {
      const hopMax = hop.latency1 || hop.latency2 || hop.latency3 || hop.worst || 0;
      maxLatency = Math.max(maxLatency, hopMax);
    });
    
    // Add 20% buffer to max latency for visualization
    maxLatency = Math.ceil(maxLatency * 1.2);
    
    // Create visualization for each hop
    hops.forEach((hop, index) => {
      // Create hop container
      const hopContainer = document.createElement('div');
      hopContainer.className = 'hop-container';
      
      // Hop number
      const hopNumber = document.createElement('div');
      hopNumber.className = 'hop-number';
      hopNumber.textContent = hop.hop;
      hopContainer.appendChild(hopNumber);
      
      // Create hop node
      const hopNode = document.createElement('div');
      hopNode.className = 'hop-node';
      
      // Choose appropriate icon
      let icon = 'fa-network-wired';
      if (hop.hop === 1) {
        icon = 'fa-home';
      } else if (hop.hop === hops.length) {
        icon = 'fa-server';
      }
      
      // Add icon
      const nodeIcon = document.createElement('div');
      nodeIcon.className = 'hop-node-icon';
      nodeIcon.innerHTML = `<i class="fas ${icon}"></i>`;
      hopNode.appendChild(nodeIcon);
      
      // Add details
      const hopDetails = document.createElement('div');
      hopDetails.className = 'hop-details';
      
      // Hostname/IP
      const hostname = document.createElement('div');
      hostname.className = 'hop-hostname';
      hostname.textContent = hop.hostname || 'Unknown';
      hopDetails.appendChild(hostname);
      
      const ip = document.createElement('div');
      ip.className = 'hop-ip';
      ip.textContent = hop.ip;
      hopDetails.appendChild(ip);
      
      // Stats
      const hopStats = document.createElement('div');
      hopStats.className = 'hop-stats';
      
      // Add different stats based on tool type
      if (hop.hasOwnProperty('latency1')) {
        // Traceroute format
        const avgLatency = (hop.latency1 + hop.latency2 + hop.latency3) / 3;
        
        addStat(hopStats, 'RTT', `${avgLatency.toFixed(1)} ms`, getLatencyClass(avgLatency));
        
        // RTT visualization
        const rttContainer = document.createElement('div');
        rttContainer.className = 'rtt-bar-container';
        
        const barLabel = document.createElement('div');
        barLabel.className = 'rtt-bar-label';
        barLabel.textContent = 'RTT';
        rttContainer.appendChild(barLabel);
        
        const barWrapper = document.createElement('div');
        barWrapper.className = 'rtt-bar-wrapper';
        
        const bar = document.createElement('div');
        bar.className = 'rtt-bar';
        bar.style.width = `${(avgLatency / maxLatency) * 100}%`;
        barWrapper.appendChild(bar);
        rttContainer.appendChild(barWrapper);
        
        const barValue = document.createElement('div');
        barValue.className = 'rtt-bar-value';
        barValue.textContent = `${avgLatency.toFixed(1)} ms`;
        rttContainer.appendChild(barValue);
        
        hopDetails.appendChild(rttContainer);
      } else if (hop.hasOwnProperty('loss')) {
        // MTR format
        addStat(hopStats, 'Loss', `${hop.loss}%`, hop.loss > 10 ? 'latency-bad' : hop.loss > 0 ? 'latency-medium' : 'latency-good');
        addStat(hopStats, 'Avg', `${hop.avg} ms`, getLatencyClass(hop.avg));
        addStat(hopStats, 'Best', `${hop.best} ms`, 'latency-good');
        addStat(hopStats, 'Worst', `${hop.worst} ms`, getLatencyClass(hop.worst));
        
        // RTT visualization
        const rttContainer = document.createElement('div');
        rttContainer.className = 'rtt-bar-container';
        
        const barLabel = document.createElement('div');
        barLabel.className = 'rtt-bar-label';
        barLabel.textContent = 'Avg';
        rttContainer.appendChild(barLabel);
        
        const barWrapper = document.createElement('div');
        barWrapper.className = 'rtt-bar-wrapper';
        
        const bar = document.createElement('div');
        bar.className = 'rtt-bar';
        bar.style.width = `${(hop.avg / maxLatency) * 100}%`;
        barWrapper.appendChild(bar);
        rttContainer.appendChild(barWrapper);
        
        const barValue = document.createElement('div');
        barValue.className = 'rtt-bar-value';
        barValue.textContent = `${hop.avg} ms`;
        rttContainer.appendChild(barValue);
        
        hopDetails.appendChild(rttContainer);
      }
      
      hopNode.appendChild(hopDetails);
      hopContainer.appendChild(hopNode);
      
      // Add path indicator unless it's the last hop
      if (index < hops.length - 1) {
        const hopPath = document.createElement('div');
        hopPath.className = 'hop-path';
        hopContainer.appendChild(hopPath);
      }
      
      tracerouteVisualization.appendChild(hopContainer);
    });
  }

  /**
   * Add a statistic to the hop stats container
   */
  function addStat(container, label, value, className = '') {
    const stat = document.createElement('div');
    stat.className = 'hop-stat';
    
    const statLabel = document.createElement('div');
    statLabel.className = 'hop-stat-label';
    statLabel.textContent = label + ':';
    stat.appendChild(statLabel);
    
    const statValue = document.createElement('div');
    statValue.className = 'hop-stat-value';
    if (className) {
      statValue.classList.add(className);
    }
    statValue.textContent = value;
    stat.appendChild(statValue);
    
    container.appendChild(stat);
  }

  /**
   * Get a CSS class based on latency value
   */
  function getLatencyClass(latency) {
    if (latency < 50) return 'latency-good';
    if (latency < 100) return 'latency-medium';
    return 'latency-bad';
  }

  /**
   * Display network tool results
   */
  function displayNetworkResults(host, toolType, output, stats) {
    // Set result host and tool type
    resultHost.textContent = host;
    resultToolType.textContent = toolType;
    
    // Set raw output
    resultOutput.textContent = output;
    
    // Set statistics
    statsHost.textContent = host;
    statsPackets.textContent = stats.packetCount;
    statsSuccess.textContent = `${stats.successRate.toFixed(1)}%`;
    statsIp.textContent = stats.ip;
    statsMin.textContent = `${stats.minLatency.toFixed(1)} ms`;
    statsAvg.textContent = `${stats.avgLatency.toFixed(1)} ms`;
    statsMax.textContent = `${stats.maxLatency.toFixed(1)} ms`;
    statsStdDev.textContent = `${stats.stdDev.toFixed(1)} ms`;
    
    // Show results
    showResults();
    
    // Make sure the Raw Output tab is active when showing results
    const rawTab = document.getElementById('rawResults-tab');
    if (rawTab) {
      const tab = new bootstrap.Tab(rawTab);
      tab.show();
    }
  }

  /**
   * Generate a random IP address
   */
  function generateRandomIp() {
    return `${Math.floor(Math.random() * 256)}.${Math.floor(Math.random() * 256)}.${Math.floor(Math.random() * 256)}.${Math.floor(Math.random() * 256)}`;
  }

  /**
   * Clear the form
   */
  function clearNetworkForm() {
    hostTargetInput.value = '';
    toolTypeSelect.value = 'ping';
    packetCountInput.value = '4';
    packetSizeInput.value = '56';
    timeoutInput.value = '2';
    hideResults();
    hideError();
    hostTargetInput.focus();
    
    // Reset tool options
    updateToolOptions('ping');
  }

  /**
   * Copy network tool results to clipboard
   */
  function copyNetworkResults() {
    let resultsText = `${resultToolType.textContent} Results for ${resultHost.textContent}\n\n`;
    resultsText += resultOutput.textContent;
    
    navigator.clipboard.writeText(resultsText.trim())
      .then(() => {
        // Show copied notification
        const originalText = copyResultsBtn.innerHTML;
        copyResultsBtn.innerHTML = '<i class="fas fa-check me-1"></i>Copied!';
        
        setTimeout(() => {
          copyResultsBtn.innerHTML = originalText;
        }, 2000);
      })
      .catch(err => {
        console.error('Could not copy text: ', err);
        showError('Failed to copy results to clipboard.');
      });
  }

  /**
   * Show error message
   */
  function showError(message) {
    errorText.textContent = message;
    errorDiv.classList.remove('d-none');
  }

  /**
   * Hide error message
   */
  function hideError() {
    errorDiv.classList.add('d-none');
  }

  /**
   * Show results container
   */
  function showResults() {
    resultsDiv.classList.remove('d-none');
  }

  /**
   * Hide results container
   */
  function hideResults() {
    resultsDiv.classList.add('d-none');
  }

  /**
   * Show loading indicator
   */
  function showLoading() {
    loadingDiv.classList.remove('d-none');
  }

  /**
   * Hide loading indicator
   */
  function hideLoading() {
    loadingDiv.classList.add('d-none');
  }
}

/**
 * Firewall Rule Generator functionality
 * To be implemented
 */
function generateFirewallRule() {
  // Placeholder for firewall rule generator implementation
  console.log('Firewall rule generator will be implemented in a future update');
}

/**
 * Binary/Hex/Decimal Converter functionality
 * To be implemented
 */
function convertNumber() {
  // Placeholder for number converter implementation
  console.log('Number converter will be implemented in a future update');
}

/**
 * CIDR to Subnet Mask Converter functionality
 * To be implemented
 */
function convertCidr() {
  // Placeholder for CIDR converter implementation
  console.log('CIDR converter will be implemented in a future update');
}

/**
 * IPv4 to IPv6 Converter functionality
 * To be implemented
 */
function convertIpv4ToIpv6() {
  // Placeholder for IPv4 to IPv6 converter implementation
  console.log('IPv4 to IPv6 converter will be implemented in a future update');
}

/**
 * Bandwidth Calculator functionality
 * To be implemented
 */
function calculateBandwidth() {
  // Placeholder for bandwidth calculator implementation
  console.log('Bandwidth calculator will be implemented in a future update');
}