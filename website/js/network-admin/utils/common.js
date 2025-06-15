/**
 * Common utility functions for network admin tools
 */

/**
 * Wait for a specified delay using Promise
 * @param {number} ms Milliseconds to wait
 * @returns {Promise} Promise that resolves after the delay
 */
function delay(ms) {
  return new Promise(resolve => setTimeout(resolve, ms));
}

/**
 * Check if a string is a valid IPv4 address
 * @param {string} ip IP address to check
 * @returns {boolean} True if valid IPv4 address
 */
function isValidIpv4(ip) {
  const ipv4Pattern = /^(25[0-5]|2[0-4][0-9]|[01]?[0-9][0-9]?)\.(25[0-5]|2[0-4][0-9]|[01]?[0-9][0-9]?)\.(25[0-5]|2[0-4][0-9]|[01]?[0-9][0-9]?)\.(25[0-5]|2[0-4][0-9]|[01]?[0-9][0-9]?)$/;
  return ipv4Pattern.test(ip);
}

/**
 * Check if a string is a valid IPv6 address
 * @param {string} ip IP address to check
 * @returns {boolean} True if valid IPv6 address
 */
function isValidIpv6(ip) {
  // IPv6 regex pattern (simplified)
  const ipv6Pattern = /^(([0-9a-fA-F]{1,4}:){7,7}[0-9a-fA-F]{1,4}|([0-9a-fA-F]{1,4}:){1,7}:|([0-9a-fA-F]{1,4}:){1,6}:[0-9a-fA-F]{1,4}|([0-9a-fA-F]{1,4}:){1,5}(:[0-9a-fA-F]{1,4}){1,2}|([0-9a-fA-F]{1,4}:){1,4}(:[0-9a-fA-F]{1,4}){1,3}|([0-9a-fA-F]{1,4}:){1,3}(:[0-9a-fA-F]{1,4}){1,4}|([0-9a-fA-F]{1,4}:){1,2}(:[0-9a-fA-F]{1,4}){1,5}|[0-9a-fA-F]{1,4}:((:[0-9a-fA-F]{1,4}){1,6})|:((:[0-9a-fA-F]{1,4}){1,7}|:)|fe80:(:[0-9a-fA-F]{0,4}){0,4}%[0-9a-zA-Z]{1,}|::(ffff(:0{1,4}){0,1}:){0,1}((25[0-5]|(2[0-4]|1{0,1}[0-9]){0,1}[0-9])\.){3,3}(25[0-5]|(2[0-4]|1{0,1}[0-9]){0,1}[0-9])|([0-9a-fA-F]{1,4}:){1,4}:((25[0-5]|(2[0-4]|1{0,1}[0-9]){0,1}[0-9])\.){3,3}(25[0-5]|(2[0-4]|1{0,1}[0-9]){0,1}[0-9]))$/;
  return ipv6Pattern.test(ip);
}

/**
 * Check if a string is a valid domain name
 * @param {string} domain Domain name to check
 * @returns {boolean} True if valid domain name
 */
function isValidDomain(domain) {
  const domainPattern = /^(?:[a-z0-9](?:[a-z0-9-]{0,61}[a-z0-9])?\.)+[a-z0-9][a-z0-9-]{0,61}[a-z0-9]$/i;
  return domainPattern.test(domain);
}

/**
 * Copy text to clipboard with visual feedback
 * @param {string} text Text to copy
 * @param {HTMLElement} button Button that triggered the copy (for feedback)
 * @param {string} originalHTML Original button HTML (restored after feedback)
 * @returns {Promise} Promise that resolves when copy operation is complete
 */
function copyToClipboard(text, button, originalHTML) {
  return navigator.clipboard.writeText(text)
    .then(() => {
      // Show copied notification if button provided
      if (button) {
        button.innerHTML = '<i class="fas fa-check me-2"></i>Copied!';
        
        setTimeout(() => {
          button.innerHTML = originalHTML;
        }, 2000);
      }
      return true;
    })
    .catch(err => {
      console.error('Could not copy text: ', err);
      if (button) button.innerHTML = originalHTML;
      return false;
    });
}

// Export utility functions
export {
  delay,
  isValidIpv4,
  isValidIpv6,
  isValidDomain,
  copyToClipboard
};

export default {
  delay,
  isValidIpv4,
  isValidIpv6,
  isValidDomain,
  copyToClipboard
};