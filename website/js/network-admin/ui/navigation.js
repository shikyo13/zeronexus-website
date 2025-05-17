/**
 * Navigation functionality for Network Admin Tools
 * Handles tab switching and navbar functionality
 */

/**
 * Initialize navigation functionality
 */
function setupNavigation() {
  // Bootstrap takes care of most tab navigation
  // This is just a place for any custom tab behavior
  
  // Get tab elements
  const tabs = document.querySelectorAll('#toolTabs .nav-link');
  
  // Add event listeners for any custom behavior
  tabs.forEach(tab => {
    tab.addEventListener('shown.bs.tab', event => {
      // Update URL hash when tab changes (optional)
      // const tabId = event.target.id.replace('-tab', '');
      // history.replaceState(null, null, `#tab-${tabId}`);
    });
  });
  
  // Check URL for tab selection
  function checkTabFromUrl() {
    const hash = window.location.hash;
    if (hash && hash.startsWith('#tab-')) {
      const tabId = hash.replace('#tab-', '');
      const tabToActivate = document.getElementById(`${tabId}-tab`);
      
      if (tabToActivate) {
        const tab = new bootstrap.Tab(tabToActivate);
        tab.show();
      }
    }
  }
  
  // Initial check for tab in URL
  checkTabFromUrl();
}

// Export the setup function
export default setupNavigation;