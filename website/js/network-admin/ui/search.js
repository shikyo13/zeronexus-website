/**
 * Tool search functionality
 * Provides search capabilities for the Network Admin Tools page
 */

/**
 * Initialize tool search functionality
 */
function setupToolSearch() {
  // Search functionality is currently disabled
  return;
  
  const searchInput = document.getElementById("toolSearch");
  const clearButton = document.getElementById("clearSearch");
  const resetButton = document.getElementById("resetSearchBtn");
  const searchCount = document.getElementById("searchCount");
  const noResultsMessage = document.getElementById("noResultsMessage");
  const allTools = document.querySelectorAll(".tool-card");
  const searchContainer = document.querySelector(".tool-search-container");
  
  if (!searchInput || !searchContainer) return;
  
  // Enable search UI
  searchContainer.classList.remove("d-none");
  
  // Add event listeners
  if (searchInput) {
    searchInput.addEventListener("input", filterTools);
  }
  
  if (clearButton) {
    clearButton.addEventListener("click", clearSearch);
  }
  
  if (resetButton) {
    resetButton.addEventListener("click", clearSearch);
  }
  
  /**
   * Filter tools based on search input
   */
  function filterTools() {
    const searchTerm = searchInput.value.toLowerCase().trim();
    let matchCount = 0;
    
    // If search is empty, show all and exit
    if (searchTerm === "") {
      allTools.forEach(tool => {
        tool.parentElement.classList.remove("d-none");
      });
      
      if (noResultsMessage) noResultsMessage.classList.add("d-none");
      if (searchCount) searchCount.classList.add("d-none");
      return;
    }
    
    // Filter tools
    allTools.forEach(tool => {
      const title = tool.querySelector("h3").textContent.toLowerCase();
      const description = tool.querySelector("p").textContent.toLowerCase();
      const matched = title.includes(searchTerm) || description.includes(searchTerm);
      
      if (matched) {
        tool.parentElement.classList.remove("d-none");
        matchCount++;
      } else {
        tool.parentElement.classList.add("d-none");
      }
    });
    
    // Show/hide no results message
    if (matchCount === 0) {
      if (noResultsMessage) noResultsMessage.classList.remove("d-none");
      if (searchCount) searchCount.classList.add("d-none");
    } else {
      if (noResultsMessage) noResultsMessage.classList.add("d-none");
      
      // Update search count
      if (searchCount) {
        searchCount.textContent = `Found ${matchCount} tool${matchCount !== 1 ? 's' : ''}`;
        searchCount.classList.remove("d-none");
      }
    }
  }
  
  /**
   * Clear search input and reset tool display
   */
  function clearSearch() {
    if (searchInput) searchInput.value = "";
    
    // Reset tool visibility
    allTools.forEach(tool => {
      tool.parentElement.classList.remove("d-none");
    });
    
    // Hide status messages
    if (noResultsMessage) noResultsMessage.classList.add("d-none");
    if (searchCount) searchCount.classList.add("d-none");
    
    // Focus on search input
    if (searchInput) searchInput.focus();
  }
}

// Export the setup function
export default setupToolSearch;