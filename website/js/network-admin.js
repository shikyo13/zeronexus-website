/**
 * Network Admin Tools JavaScript
 * 
 * Provides functionality for the Network Admin Tools page
 */

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
    const binaryMask = createBinaryRepresentation(subnetMask);

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

  /**
   * Create binary representation of IP address
   */
  function createBinaryRepresentation(ipAddress) {
    return ipAddress.split('.')
      .map(octet => parseInt(octet, 10).toString(2).padStart(8, '0'))
      .join(' ');
  }
}

// Call setup function when document is ready
document.addEventListener('DOMContentLoaded', function() {
  setupSubnetCalculator();
});

/**
 * DNS Lookup functionality
 * To be implemented
 */
function performDnsLookup() {
  // Placeholder for DNS lookup implementation
  console.log('DNS lookup will be implemented in a future update');
}

/**
 * Security Headers Checker functionality
 * To be implemented
 */
function checkSecurityHeaders() {
  // Placeholder for security headers checker implementation
  console.log('Security headers checker will be implemented in a future update');
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