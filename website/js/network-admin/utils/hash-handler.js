/**
 * URL hash handling for direct tool access
 * This module provides functionality to handle URL hash fragments for direct tool access
 */

/**
 * Tool hash to modal ID mapping
 */
const toolHashMap = {
  'subnet-calculator': '#subnetCalculatorModal',
  'dns-lookup': '#dnsLookupModal',
  'ping-traceroute': '#pingTracerouteModal',
  'security-headers': '#securityHeadersModal',
  'security-headers-generator': '#securityHeadersGeneratorModal',
  'firewall-rule-generator': '#firewallRuleGeneratorModal'
};

/**
 * Check URL hash and open the corresponding tool if needed
 */
function checkUrlHash() {
  const hash = window.location.hash.substring(1);
  if (!hash) return;
  
  // Split hash to get the base tool name and parameters
  const baseHash = hash.split('?')[0];
  
  // Check if it's a direct tool link
  if (toolHashMap[baseHash]) {
    const modalSelector = toolHashMap[baseHash];
    const modal = document.querySelector(modalSelector);
    
    if (modal) {
      console.log(`Opening tool from hash: ${baseHash}`);
      
      // Use Bootstrap's modal method to open the modal
      const bsModal = new bootstrap.Modal(modal);
      bsModal.show();
      
      // The tool's setup function will handle any parameters in the hash
    }
  }
}

// Export functions and constants
export { toolHashMap, checkUrlHash };
export default checkUrlHash;