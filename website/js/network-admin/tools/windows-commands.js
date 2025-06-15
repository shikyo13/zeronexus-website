/**
 * Windows Commands Reference functionality
 * Provides searchable Windows command reference for the Network Admin Tools page
 */

let windowsCommandsData = [];
let windowsFilteredCommands = [];
let windowsFavoriteCommands = new Set();

/**
 * Initialize Windows commands functionality
 */
function setupWindowsCommands() {
  loadWindowsFavorites();
  loadWindowsCommandsData();
  setupWindowsEventListeners();
  checkWindowsUrlParams();
}

/**
 * Load commands data from JSON file
 */
async function loadWindowsCommandsData() {
  try {
    const response = await fetch('/js/network-admin/data/windows-commands.json');
    const data = await response.json();
    windowsCommandsData = data.commands;
    windowsFilteredCommands = [...windowsCommandsData];
    renderWindowsCommands();
  } catch (error) {
    console.error('Error loading Windows commands data:', error);
    showWindowsError('Failed to load commands data. Please refresh the page.');
  }
}

/**
 * Set up event listeners
 */
function setupWindowsEventListeners() {
  const searchInput = document.getElementById('windowsCommandSearch');
  const clearSearchBtn = document.getElementById('windowsClearSearchBtn');
  const categoryFilter = document.getElementById('windowsCategoryFilter');
  const difficultyButtons = document.querySelectorAll('#windowsCommandsModal [data-difficulty]');
  const showFavoritesBtn = document.getElementById('windowsShowFavoritesBtn');

  // Search functionality with debouncing
  if (searchInput) {
    let searchTimeout;
    searchInput.addEventListener('input', (e) => {
      clearTimeout(searchTimeout);
      searchTimeout = setTimeout(() => {
        filterWindowsCommands();
      }, 200);
    });
  }

  // Clear search
  if (clearSearchBtn) {
    clearSearchBtn.addEventListener('click', () => {
      if (searchInput) searchInput.value = '';
      filterWindowsCommands();
    });
  }

  // Category filter
  if (categoryFilter) {
    categoryFilter.addEventListener('change', filterWindowsCommands);
  }

  // Difficulty filter buttons
  difficultyButtons.forEach(btn => {
    btn.addEventListener('click', (e) => {
      // Update active state
      difficultyButtons.forEach(b => b.classList.remove('active'));
      e.target.classList.add('active');
      filterWindowsCommands();
    });
  });

  // Show favorites toggle
  if (showFavoritesBtn) {
    showFavoritesBtn.addEventListener('click', toggleWindowsFavoritesView);
  }
}

/**
 * Filter commands based on search and filters
 */
function filterWindowsCommands() {
  const searchTerm = document.getElementById('windowsCommandSearch')?.value.toLowerCase() || '';
  const category = document.getElementById('windowsCategoryFilter')?.value || 'all';
  const difficulty = document.querySelector('#windowsCommandsModal [data-difficulty].active')?.dataset.difficulty || 'all';
  const showingFavorites = document.getElementById('windowsShowFavoritesBtn')?.classList.contains('active') || false;

  windowsFilteredCommands = windowsCommandsData.filter(cmd => {
    // Search filter
    const matchesSearch = !searchTerm || 
      cmd.name.toLowerCase().includes(searchTerm) ||
      cmd.description.toLowerCase().includes(searchTerm) ||
      cmd.tags.some(tag => tag.toLowerCase().includes(searchTerm)) ||
      cmd.examples.some(ex => ex.command.toLowerCase().includes(searchTerm));

    // Category filter
    const matchesCategory = category === 'all' || cmd.category === category;

    // Difficulty filter
    const matchesDifficulty = difficulty === 'all' || cmd.difficulty === difficulty;

    // Favorites filter
    const matchesFavorites = !showingFavorites || windowsFavoriteCommands.has(cmd.name);

    return matchesSearch && matchesCategory && matchesDifficulty && matchesFavorites;
  });

  renderWindowsCommands();
}

/**
 * Render commands grid
 */
function renderWindowsCommands() {
  const commandsGrid = document.getElementById('windowsCommandsGrid');
  const loadingDiv = document.getElementById('windowsCommandsLoading');
  const noResultsDiv = document.getElementById('windowsNoResults');

  if (!commandsGrid) return;

  // Hide loading
  if (loadingDiv) loadingDiv.classList.add('d-none');

  // Clear existing content
  commandsGrid.innerHTML = '';

  if (windowsFilteredCommands.length === 0) {
    if (noResultsDiv) noResultsDiv.classList.remove('d-none');
    return;
  }

  if (noResultsDiv) noResultsDiv.classList.add('d-none');

  // Render command cards
  windowsFilteredCommands.forEach(command => {
    const commandCard = createWindowsCommandCard(command);
    commandsGrid.appendChild(commandCard);
  });
}

/**
 * Create a command card element
 */
function createWindowsCommandCard(command) {
  const cardDiv = document.createElement('div');
  cardDiv.className = 'col-md-6 col-lg-4';

  const isFavorite = windowsFavoriteCommands.has(command.name);
  const difficultyColor = getWindowsDifficultyColor(command.difficulty);
  const cardId = `windows-command-${command.name}`;

  cardDiv.innerHTML = `
    <div class="card bg-dark border-secondary command-card" data-command="${command.name}">
      <div class="card-header d-flex justify-content-between align-items-center py-2">
        <div class="d-flex align-items-center">
          <code class="text-primary fw-bold me-2">${command.name}</code>
          <span class="badge ${difficultyColor} badge-sm">${command.difficulty}</span>
        </div>
        <div>
          <button class="btn btn-sm btn-outline-warning windows-favorite-btn ${isFavorite ? 'active' : ''}" 
                  data-command="${command.name}" title="Add to favorites">
            <i class="fas fa-star"></i>
          </button>
          <button class="btn btn-sm btn-outline-primary windows-copy-btn" 
                  data-command="${command.name}" title="Copy command">
            <i class="fas fa-copy"></i>
          </button>
        </div>
      </div>
      <div class="card-body">
        <div class="syntax-section mb-1">
          <strong class="small">Syntax:</strong>
          <code class="d-block mt-1 text-success">${command.syntax}</code>
        </div>
        <div class="description-section mb-4">
          <p class="card-text mb-2">${command.description}</p>
          ${command.detailedDescription ? `<p class="text-muted small mb-0">${command.detailedDescription}</p>` : ''}
        </div>
        
        ${command.commonOptions.length > 0 ? `
        <div class="options-section mb-3">
          <button class="btn btn-sm btn-outline-info windows-toggle-options" data-target="${cardId}-options">
            <i class="fas fa-chevron-down me-1"></i> Options (${command.commonOptions.length})
          </button>
          <div class="collapse mt-2" id="${cardId}-options">
            ${command.commonOptions.map(opt => `
              <div class="small border-start border-info ps-2 mb-2 d-flex align-items-baseline">
                <code class="text-warning flex-shrink-0">${opt.flag}</code>
                <span class="ms-2">${opt.description}</span>
              </div>
            `).join('')}
          </div>
        </div>` : ''}
        
        <div class="examples-section">
          <button class="btn btn-sm btn-outline-success windows-toggle-examples" data-target="${cardId}-examples">
            <i class="fas fa-chevron-down me-1"></i> Examples (${command.examples.length})
          </button>
          <div class="collapse mt-2" id="${cardId}-examples">
            ${command.examples.map((ex, idx) => `
              <div class="example-item border-start border-success ps-2 mb-2">
                <div class="d-flex justify-content-between align-items-start">
                  <code class="text-info small">${ex.command}</code>
                  <button class="btn btn-xs btn-outline-secondary windows-copy-example-btn" 
                          data-command="${ex.command}" title="Copy example">
                    <i class="fas fa-copy"></i>
                  </button>
                </div>
                <div class="small text-muted mt-1">${ex.description}</div>
              </div>
            `).join('')}
          </div>
        </div>
        
        ${command.related.length > 0 ? `
        <div class="related-section mt-3 pt-2 border-top border-secondary">
          <small class="text-muted">Related: 
            ${command.related.map(rel => `<code class="text-muted">${rel}</code>`).join(', ')}
          </small>
        </div>` : ''}
        
        <div class="tags-section mt-2">
          ${command.tags.map(tag => `<span class="badge bg-secondary me-1">${tag}</span>`).join('')}
        </div>
      </div>
    </div>
  `;

  // Add event listeners to the card
  setupWindowsCardEventListeners(cardDiv, command);

  return cardDiv;
}

/**
 * Set up event listeners for a command card
 */
function setupWindowsCardEventListeners(cardDiv, command) {
  // Toggle options
  const optionsToggle = cardDiv.querySelector('.windows-toggle-options');
  if (optionsToggle) {
    optionsToggle.addEventListener('click', (e) => {
      const target = e.target.closest('.windows-toggle-options').dataset.target;
      const collapse = document.getElementById(target);
      const icon = e.target.closest('.windows-toggle-options').querySelector('i');
      
      collapse.classList.toggle('show');
      icon.classList.toggle('fa-chevron-down');
      icon.classList.toggle('fa-chevron-up');
    });
  }

  // Toggle examples
  const examplesToggle = cardDiv.querySelector('.windows-toggle-examples');
  if (examplesToggle) {
    examplesToggle.addEventListener('click', (e) => {
      const target = e.target.closest('.windows-toggle-examples').dataset.target;
      const collapse = document.getElementById(target);
      const icon = e.target.closest('.windows-toggle-examples').querySelector('i');
      
      collapse.classList.toggle('show');
      icon.classList.toggle('fa-chevron-down');
      icon.classList.toggle('fa-chevron-up');
    });
  }

  // Copy command
  const copyBtn = cardDiv.querySelector('.windows-copy-btn');
  if (copyBtn) {
    copyBtn.addEventListener('click', () => {
      windowsCopyToClipboard(command.name, copyBtn);
    });
  }

  // Copy example commands
  const copyExampleBtns = cardDiv.querySelectorAll('.windows-copy-example-btn');
  copyExampleBtns.forEach(btn => {
    btn.addEventListener('click', (e) => {
      const cmd = e.target.closest('.windows-copy-example-btn').dataset.command;
      windowsCopyToClipboard(cmd, btn);
    });
  });

  // Favorite toggle
  const favoriteBtn = cardDiv.querySelector('.windows-favorite-btn');
  if (favoriteBtn) {
    favoriteBtn.addEventListener('click', () => {
      toggleWindowsFavorite(command.name, favoriteBtn);
    });
  }
}

/**
 * Copy text to clipboard with visual feedback
 */
function windowsCopyToClipboard(text, button) {
  navigator.clipboard.writeText(text)
    .then(() => {
      const originalIcon = button.querySelector('i').className;
      button.querySelector('i').className = 'fas fa-check';
      button.classList.add('text-success');
      
      setTimeout(() => {
        button.querySelector('i').className = originalIcon;
        button.classList.remove('text-success');
      }, 1500);
    })
    .catch(err => {
      console.error('Failed to copy:', err);
      alert('Failed to copy to clipboard');
    });
}

/**
 * Toggle favorite status for a command
 */
function toggleWindowsFavorite(commandName, button) {
  if (windowsFavoriteCommands.has(commandName)) {
    windowsFavoriteCommands.delete(commandName);
    button.classList.remove('active');
  } else {
    windowsFavoriteCommands.add(commandName);
    button.classList.add('active');
  }
  
  saveWindowsFavorites();
  updateWindowsFavoritesCount();
}

/**
 * Toggle favorites view
 */
function toggleWindowsFavoritesView() {
  const btn = document.getElementById('windowsShowFavoritesBtn');
  if (!btn) return;

  btn.classList.toggle('active');
  
  if (btn.classList.contains('active')) {
    btn.innerHTML = '<i class="fas fa-star me-1"></i> Show All';
  } else {
    btn.innerHTML = `<i class="fas fa-star me-1"></i> Favorites (<span id="windowsFavoritesCount">${windowsFavoriteCommands.size}</span>)`;
  }
  
  filterWindowsCommands();
}

/**
 * Get difficulty badge color
 */
function getWindowsDifficultyColor(difficulty) {
  switch (difficulty) {
    case 'beginner': return 'bg-success';
    case 'intermediate': return 'bg-warning';
    case 'advanced': return 'bg-danger';
    default: return 'bg-secondary';
  }
}

/**
 * Load favorites from localStorage
 */
function loadWindowsFavorites() {
  try {
    const saved = localStorage.getItem('windowsCommandsFavorites');
    if (saved) {
      windowsFavoriteCommands = new Set(JSON.parse(saved));
    }
  } catch (error) {
    console.error('Error loading Windows favorites:', error);
    windowsFavoriteCommands = new Set();
  }
  updateWindowsFavoritesCount();
}

/**
 * Save favorites to localStorage
 */
function saveWindowsFavorites() {
  try {
    localStorage.setItem('windowsCommandsFavorites', JSON.stringify([...windowsFavoriteCommands]));
  } catch (error) {
    console.error('Error saving Windows favorites:', error);
  }
}

/**
 * Update favorites count display
 */
function updateWindowsFavoritesCount() {
  const countSpan = document.getElementById('windowsFavoritesCount');
  if (countSpan) {
    countSpan.textContent = windowsFavoriteCommands.size;
  }
}

/**
 * Show error message
 */
function showWindowsError(message) {
  const commandsGrid = document.getElementById('windowsCommandsGrid');
  const loadingDiv = document.getElementById('windowsCommandsLoading');
  
  if (loadingDiv) loadingDiv.classList.add('d-none');
  if (commandsGrid) {
    commandsGrid.innerHTML = `
      <div class="col-12">
        <div class="alert alert-danger" role="alert">
          <i class="fas fa-exclamation-triangle me-2"></i>${message}
        </div>
      </div>
    `;
  }
}

/**
 * Check URL parameters for direct linking
 */
function checkWindowsUrlParams() {
  const hash = window.location.hash;
  if (hash && hash.startsWith('#windows-commands')) {
    const paramsStr = hash.split('?')[1] || '';
    const searchParams = new URLSearchParams(paramsStr);
    
    const search = searchParams.get('search');
    const category = searchParams.get('category');
    const difficulty = searchParams.get('difficulty');
    
    if (search) {
      const searchInput = document.getElementById('windowsCommandSearch');
      if (searchInput) searchInput.value = search;
    }
    
    if (category) {
      const categoryFilter = document.getElementById('windowsCategoryFilter');
      if (categoryFilter) categoryFilter.value = category;
    }
    
    if (difficulty) {
      const difficultyButtons = document.querySelectorAll('#windowsCommandsModal [data-difficulty]');
      difficultyButtons.forEach(btn => {
        btn.classList.remove('active');
        if (btn.dataset.difficulty === difficulty) {
          btn.classList.add('active');
        }
      });
    }
    
    // Apply filters after a short delay to ensure data is loaded
    setTimeout(() => {
      filterWindowsCommands();
    }, 500);
  }
}

// Export the setup function
export default setupWindowsCommands;