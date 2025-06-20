/**
 * Network Admin Tools JavaScript
 * 
 * This file has been refactored to use a modular structure.
 * It now imports functionality from individual modules in the network-admin/ directory.
 */

// Import utility modules
import { checkUrlHash } from './network-admin/utils/hash-handler.js';
import setupNavigation from './network-admin/ui/navigation.js';
import setupToolSearch from './network-admin/ui/search.js';

// Import tool modules
import setupSubnetCalculator from './network-admin/tools/subnet-calculator.js';
import setupDnsLookup from './network-admin/tools/dns-lookup.js';
import setupPingTraceroute from './network-admin/tools/ping-traceroute.js';
import setupSecurityHeadersChecker from './network-admin/tools/security-headers.js';
import setupSecurityHeadersGenerator from './network-admin/tools/security-headers-generator.js';
import setupPasswordStrength from './network-admin/tools/password-strength.js';
import setupFirewallRuleGenerator from './network-admin/tools/firewall-rule-generator.js';
import setupLinuxCommands from './network-admin/tools/linux-commands.js';
import setupWindowsCommands from './network-admin/tools/windows-commands.js';

// Initialize tools when DOM is loaded
window.addEventListener('DOMContentLoaded', function() {
  console.log('Initializing network admin tools...');
  
  // Check for hash in URL and open corresponding tool
  checkUrlHash();
  
  // Listen for hash changes to open tools dynamically
  window.addEventListener('hashchange', checkUrlHash);
  
  // Initialize UI components
  setupNavigation();
  setupToolSearch();
  
  // Initialize tool components
  setupSubnetCalculator();
  setupDnsLookup();
  setupPingTraceroute();
  setupSecurityHeadersChecker();
  try {
    console.log('Initializing Security Headers Generator...');
    setupSecurityHeadersGenerator();
    console.log('Successfully initialized Security Headers Generator');
  } catch (e) {
    console.error('Failed to initialize Security Headers Generator:', e);
  }
  setupPasswordStrength();
  try {
    console.log('Initializing Firewall Rule Generator...');
    setupFirewallRuleGenerator();
    console.log('Successfully initialized Firewall Rule Generator');
  } catch (e) {
    console.error('Failed to initialize Firewall Rule Generator:', e);
  }
  try {
    console.log('Initializing Linux Commands Reference...');
    setupLinuxCommands();
    console.log('Successfully initialized Linux Commands Reference');
  } catch (e) {
    console.error('Failed to initialize Linux Commands Reference:', e);
  }
  try {
    console.log('Initializing Windows Commands Reference...');
    setupWindowsCommands();
    console.log('Successfully initialized Windows Commands Reference');
  } catch (e) {
    console.error('Failed to initialize Windows Commands Reference:', e);
  }
});