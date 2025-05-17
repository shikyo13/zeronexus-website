/**
 * IP Subnet Calculator functionality
 * Provides IP subnet calculation features for the Network Admin Tools page
 */

/**
 * Initialize subnet calculator functionality
 */
function setupSubnetCalculator() {
  // Get DOM elements
  const ipAddressInput = document.getElementById('ipAddress');
  const subnetInput = document.getElementById('subnetInput');
  const calculateBtn = document.getElementById('calculateSubnetBtn');
  const clearBtn = document.getElementById('clearSubnetBtn');
  const resultsDiv = document.getElementById('subnetResults');
  const errorDiv = document.getElementById('subnetError');
  const errorText = document.getElementById('subnetErrorText');
  const copyBtn = document.getElementById('copyResultsBtn');

  // Add event listeners
  if (calculateBtn) {
    calculateBtn.addEventListener('click', calculateSubnet);
  }
  
  if (clearBtn) {
    clearBtn.addEventListener('click', clearSubnetForm);
  }
  
  if (copyBtn) {
    copyBtn.addEventListener('click', copySubnetResults);
  }
  
  /**
   * Validate and calculate subnet information
   */
  function calculateSubnet() {
    // Hide any previous results or errors
    if (resultsDiv) resultsDiv.classList.add('d-none');
    if (errorDiv) errorDiv.classList.add('d-none');
    
    // Get input values
    const ipAddress = ipAddressInput ? ipAddressInput.value.trim() : '';
    const subnetValue = subnetInput ? subnetInput.value.trim() : '';
    
    // Validate inputs
    if (!ipAddress) {
      showError('Please enter an IP address');
      return;
    }
    
    if (!subnetValue) {
      showError('Please enter a subnet mask or CIDR notation');
      return;
    }
    
    // Validate IP address format
    if (!isValidIpAddress(ipAddress)) {
      showError('Invalid IP address format. Please enter a valid IPv4 address (e.g., 192.168.1.1)');
      return;
    }
    
    // Process subnet mask or CIDR
    let cidrPrefix = null;
    let subnetMask = null;
    
    if (subnetValue.startsWith('/')) {
      // CIDR notation
      cidrPrefix = parseInt(subnetValue.substring(1));
      if (isNaN(cidrPrefix) || cidrPrefix < 0 || cidrPrefix > 32) {
        showError('Invalid CIDR notation. Please enter a value between /0 and /32');
        return;
      }
      subnetMask = cidrToSubnetMask(cidrPrefix);
    } else {
      // Subnet mask
      if (!isValidSubnetMask(subnetValue)) {
        showError('Invalid subnet mask format. Please enter a valid IPv4 subnet mask (e.g., 255.255.255.0)');
        return;
      }
      subnetMask = subnetValue;
      cidrPrefix = subnetMaskToCidr(subnetMask);
    }
    
    // Calculate subnet information
    try {
      const ipOctets = ipAddress.split('.').map(Number);
      const maskOctets = subnetMask.split('.').map(Number);
      
      // Calculate network address
      const networkOctets = ipOctets.map((octet, index) => octet & maskOctets[index]);
      const networkAddress = networkOctets.join('.');
      
      // Calculate wildcard mask
      const wildcardOctets = maskOctets.map(octet => 255 - octet);
      
      // Calculate broadcast address
      const broadcastOctets = networkOctets.map((octet, index) => octet | wildcardOctets[index]);
      const broadcastAddress = broadcastOctets.join('.');
      
      // Calculate total hosts and usable hosts
      const totalHosts = Math.pow(2, 32 - cidrPrefix);
      const usableHosts = Math.max(totalHosts - 2, 1); // Subnets like /31 and /32 have special cases
      
      // Calculate first and last usable host addresses
      let firstHost, lastHost;
      
      if (cidrPrefix >= 31) {
        // Special cases for /31 and /32
        if (cidrPrefix === 31) {
          firstHost = networkAddress;
          lastHost = broadcastAddress;
        } else { // /32
          firstHost = networkAddress;
          lastHost = networkAddress;
        }
      } else {
        // Normal case
        const firstHostOctets = [...networkOctets];
        firstHostOctets[3] += 1;
        firstHost = firstHostOctets.join('.');
        
        const lastHostOctets = [...broadcastOctets];
        lastHostOctets[3] -= 1;
        lastHost = lastHostOctets.join('.');
      }
      
      // Determine network class
      let networkClass = '';
      const firstOctet = ipOctets[0];
      
      if (firstOctet >= 1 && firstOctet <= 126) {
        networkClass = 'Class A';
      } else if (firstOctet >= 128 && firstOctet <= 191) {
        networkClass = 'Class B';
      } else if (firstOctet >= 192 && firstOctet <= 223) {
        networkClass = 'Class C';
      } else if (firstOctet >= 224 && firstOctet <= 239) {
        networkClass = 'Class D (Multicast)';
      } else if (firstOctet >= 240 && firstOctet <= 255) {
        networkClass = 'Class E (Reserved)';
      } else if (firstOctet === 127) {
        networkClass = 'Loopback';
      }
      
      // Generate binary subnet mask
      const binaryMask = maskOctets.map(octet => octet.toString(2).padStart(8, '0')).join('.');
      
      // Display results
      document.getElementById('resultIpAddress').textContent = ipAddress;
      document.getElementById('resultSubnetMask').textContent = subnetMask;
      document.getElementById('resultCidr').textContent = `/${cidrPrefix}`;
      document.getElementById('resultNetworkClass').textContent = networkClass;
      document.getElementById('resultNetworkAddress').textContent = networkAddress;
      document.getElementById('resultBroadcastAddress').textContent = broadcastAddress;
      document.getElementById('resultFirstHost').textContent = firstHost;
      document.getElementById('resultLastHost').textContent = lastHost;
      document.getElementById('resultTotalHosts').textContent = totalHosts.toLocaleString();
      document.getElementById('resultUsableHosts').textContent = usableHosts.toLocaleString();
      document.getElementById('resultBinaryMask').textContent = binaryMask;
      
      // Show results
      if (resultsDiv) resultsDiv.classList.remove('d-none');
      
    } catch (error) {
      showError('Error calculating subnet information: ' + error.message);
    }
  }
  
  /**
   * Clear the subnet calculator form
   */
  function clearSubnetForm() {
    if (ipAddressInput) ipAddressInput.value = '';
    if (subnetInput) subnetInput.value = '';
    if (resultsDiv) resultsDiv.classList.add('d-none');
    if (errorDiv) errorDiv.classList.add('d-none');
    
    // Focus on the IP address input
    if (ipAddressInput) ipAddressInput.focus();
  }
  
  /**
   * Copy subnet results to clipboard
   */
  function copySubnetResults() {
    // Create a formatted string with all the results
    const ipAddress = document.getElementById('resultIpAddress').textContent;
    const subnetMask = document.getElementById('resultSubnetMask').textContent;
    const cidr = document.getElementById('resultCidr').textContent;
    const networkClass = document.getElementById('resultNetworkClass').textContent;
    const networkAddress = document.getElementById('resultNetworkAddress').textContent;
    const broadcastAddress = document.getElementById('resultBroadcastAddress').textContent;
    const firstHost = document.getElementById('resultFirstHost').textContent;
    const lastHost = document.getElementById('resultLastHost').textContent;
    const totalHosts = document.getElementById('resultTotalHosts').textContent;
    const usableHosts = document.getElementById('resultUsableHosts').textContent;
    const binaryMask = document.getElementById('resultBinaryMask').textContent;
    
    const resultText = `Subnet Calculator Results:
IP Address: ${ipAddress}
Subnet Mask: ${subnetMask}
CIDR Notation: ${cidr}
Network Class: ${networkClass}
Network Address: ${networkAddress}
Broadcast Address: ${broadcastAddress}
First Usable Host: ${firstHost}
Last Usable Host: ${lastHost}
Total Hosts: ${totalHosts}
Usable Hosts: ${usableHosts}
Binary Subnet Mask: ${binaryMask}
`;
    
    // Copy to clipboard
    navigator.clipboard.writeText(resultText)
      .then(() => {
        // Show copied notification
        const originalText = copyBtn.innerHTML;
        copyBtn.innerHTML = '<i class="fas fa-check me-2"></i>Copied!';
        
        setTimeout(() => {
          copyBtn.innerHTML = originalText;
        }, 2000);
      })
      .catch(err => {
        console.error('Could not copy text: ', err);
        alert('Failed to copy results to clipboard.');
      });
  }
  
  /**
   * Display an error message
   */
  function showError(message) {
    if (errorDiv && errorText) {
      errorText.textContent = message;
      errorDiv.classList.remove('d-none');
    }
  }
  
  /**
   * Check if the string is a valid IPv4 address
   */
  function isValidIpAddress(ip) {
    // IPv4 regex pattern
    const ipv4Pattern = /^(25[0-5]|2[0-4][0-9]|[01]?[0-9][0-9]?)\.(25[0-5]|2[0-4][0-9]|[01]?[0-9][0-9]?)\.(25[0-5]|2[0-4][0-9]|[01]?[0-9][0-9]?)\.(25[0-5]|2[0-4][0-9]|[01]?[0-9][0-9]?)$/;
    return ipv4Pattern.test(ip);
  }
  
  /**
   * Check if the string is a valid subnet mask
   */
  function isValidSubnetMask(mask) {
    // First check the format using IPv4 pattern
    if (!isValidIpAddress(mask)) {
      return false;
    }
    
    // Convert to binary and ensure it has continuous 1s followed by continuous 0s
    const maskOctets = mask.split('.').map(Number);
    const binaryStr = maskOctets.map(octet => octet.toString(2).padStart(8, '0')).join('');
    
    // Check for continuous 1s followed by continuous 0s (or all 1s or all 0s)
    return /^1*0*$/.test(binaryStr);
  }
  
  /**
   * Convert CIDR prefix to subnet mask
   */
  function cidrToSubnetMask(cidr) {
    const fullMask = 0xffffffff; // 32 bits all set to 1
    const maskBits = fullMask << (32 - cidr); // Shift right bits to 0 based on CIDR
    
    // Convert to dotted decimal format
    return [
      (maskBits >>> 24) & 0xff,
      (maskBits >>> 16) & 0xff,
      (maskBits >>> 8) & 0xff,
      maskBits & 0xff
    ].join('.');
  }
  
  /**
   * Convert subnet mask to CIDR prefix
   */
  function subnetMaskToCidr(mask) {
    // Count the number of 1 bits in the subnet mask
    return mask.split('.')
      .map(octet => parseInt(octet))
      .map(octet => octet.toString(2).split('1').length - 1)
      .reduce((acc, curr) => acc + curr, 0);
  }
  
  // Check URL parameters for direct linking
  function checkUrlParams() {
    const hash = window.location.hash;
    if (hash && hash.startsWith('#subnet-calculator')) {
      // Extract parameters from the hash
      const paramsStr = hash.split('?')[1] || '';
      const searchParams = new URLSearchParams(paramsStr);
      
      // Set input values if provided
      const ip = searchParams.get('ip');
      const subnet = searchParams.get('subnet');
      
      if (ip && ipAddressInput) {
        ipAddressInput.value = ip;
      }
      
      if (subnet && subnetInput) {
        subnetInput.value = subnet;
      }
      
      // Auto-calculate if autorun parameter is true
      if (searchParams.get('autorun') === 'true' && calculateBtn) {
        setTimeout(() => {
          calculateBtn.click();
        }, 300);
      }
    }
  }
  
  // Check URL parameters when the page loads
  checkUrlParams();
}

// Export the setup function
export default setupSubnetCalculator;