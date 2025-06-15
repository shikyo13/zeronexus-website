/**
 * Linux Commands Reference functionality
 * Provides searchable Linux command reference for the Network Admin Tools page
 */

let commandsData = [];
let filteredCommands = [];
let favoriteCommands = new Set();

/**
 * Initialize Linux commands functionality
 */
function setupLinuxCommands() {
  loadFavorites();
  loadCommandsData();
  setupEventListeners();
  checkUrlParams();
}

/**
 * Load commands data from JSON file
 */
async function loadCommandsData() {
  try {
    const response = await fetch('/js/network-admin/data/linux-commands.json');
    const data = await response.json();
    commandsData = data.commands;
    filteredCommands = [...commandsData];
    renderCommands();
  } catch (error) {
    console.error('Error loading commands data:', error);
    showError('Failed to load commands data. Please refresh the page.');
  }
}

/**
 * Set up event listeners
 */
function setupEventListeners() {
  const searchInput = document.getElementById('commandSearch');
  const clearSearchBtn = document.getElementById('clearSearchBtn');
  const categoryFilter = document.getElementById('categoryFilter');
  const difficultyButtons = document.querySelectorAll('[data-difficulty]');
  const showFavoritesBtn = document.getElementById('showFavoritesBtn');

  // Search functionality with debouncing
  if (searchInput) {
    let searchTimeout;
    searchInput.addEventListener('input', (e) => {
      clearTimeout(searchTimeout);
      searchTimeout = setTimeout(() => {
        filterCommands();
      }, 200);
    });
  }

  // Clear search
  if (clearSearchBtn) {
    clearSearchBtn.addEventListener('click', () => {
      if (searchInput) searchInput.value = '';
      filterCommands();
    });
  }

  // Category filter
  if (categoryFilter) {
    categoryFilter.addEventListener('change', filterCommands);
  }

  // Difficulty filter buttons
  difficultyButtons.forEach(btn => {
    btn.addEventListener('click', (e) => {
      // Update active state
      difficultyButtons.forEach(b => b.classList.remove('active'));
      e.target.classList.add('active');
      filterCommands();
    });
  });

  // Show favorites toggle
  if (showFavoritesBtn) {
    showFavoritesBtn.addEventListener('click', toggleFavoritesView);
  }
}

/**
 * Filter commands based on search and filters
 */
function filterCommands() {
  const searchTerm = document.getElementById('commandSearch')?.value.toLowerCase() || '';
  const category = document.getElementById('categoryFilter')?.value || 'all';
  const difficulty = document.querySelector('[data-difficulty].active')?.dataset.difficulty || 'all';
  const showingFavorites = document.getElementById('showFavoritesBtn')?.classList.contains('active') || false;

  filteredCommands = commandsData.filter(cmd => {
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
    const matchesFavorites = !showingFavorites || favoriteCommands.has(cmd.name);

    return matchesSearch && matchesCategory && matchesDifficulty && matchesFavorites;
  });

  renderCommands();
}

/**
 * Render commands grid
 */
function renderCommands() {
  const commandsGrid = document.getElementById('commandsGrid');
  const loadingDiv = document.getElementById('commandsLoading');
  const noResultsDiv = document.getElementById('noResults');

  if (!commandsGrid) return;

  // Hide loading
  if (loadingDiv) loadingDiv.classList.add('d-none');

  // Clear existing content
  commandsGrid.innerHTML = '';

  if (filteredCommands.length === 0) {
    if (noResultsDiv) noResultsDiv.classList.remove('d-none');
    return;
  }

  if (noResultsDiv) noResultsDiv.classList.add('d-none');

  // Render command cards
  filteredCommands.forEach(command => {
    const commandCard = createCommandCard(command);
    commandsGrid.appendChild(commandCard);
  });
}

/**
 * Create a command card element
 */
function createCommandCard(command) {
  const cardDiv = document.createElement('div');
  cardDiv.className = 'col-md-6 col-lg-4';

  const isFavorite = favoriteCommands.has(command.name);
  const difficultyColor = getDifficultyColor(command.difficulty);
  const cardId = `command-${command.name}`;

  cardDiv.innerHTML = `
    <div class="card bg-dark border-secondary command-card" data-command="${command.name}">
      <div class="card-header d-flex justify-content-between align-items-center py-2">
        <div class="d-flex align-items-center">
          <code class="text-primary fw-bold me-2">${command.name}</code>
          <span class="badge ${difficultyColor} badge-sm">${command.difficulty}</span>
        </div>
        <div>
          <button class="btn btn-sm btn-outline-warning favorite-btn ${isFavorite ? 'active' : ''}" 
                  data-command="${command.name}" title="Add to favorites">
            <i class="fas fa-star"></i>
          </button>
          <button class="btn btn-sm btn-outline-primary copy-btn" 
                  data-command="${command.name}" title="Copy command">
            <i class="fas fa-copy"></i>
          </button>
        </div>
      </div>
      <div class="card-body">
        <div class="syntax-section mb-2">
          <strong class="small">Syntax:</strong>
          <code class="d-block mt-1 text-success">${command.syntax}</code>
        </div>
        <div class="description-section mb-3">
          <p class="card-text mb-2">${command.description}</p>
          ${command.detailedDescription ? `<p class="text-muted small">${command.detailedDescription}</p>` : ''}
        </div>
        
        ${command.commonOptions.length > 0 ? `
        <div class="options-section mb-3">
          <button class="btn btn-sm btn-outline-info toggle-options" data-target="${cardId}-options">
            <i class="fas fa-chevron-down me-1"></i> Options (${command.commonOptions.length})
          </button>
          <div class="collapse mt-2" id="${cardId}-options">
            ${command.commonOptions.map(opt => `
              <div class="small border-start border-info ps-2 mb-1">
                <code class="text-warning">${opt.flag}</code>
                <span class="ms-2">${opt.description}</span>
              </div>
            `).join('')}
          </div>
        </div>` : ''}
        
        <div class="examples-section">
          <button class="btn btn-sm btn-outline-success toggle-examples" data-target="${cardId}-examples">
            <i class="fas fa-chevron-down me-1"></i> Examples (${command.examples.length})
          </button>
          <div class="collapse mt-2" id="${cardId}-examples">
            ${command.examples.map((ex, idx) => `
              <div class="example-item border-start border-success ps-2 mb-2">
                <div class="d-flex justify-content-between align-items-start">
                  <code class="text-info small">${ex.command}</code>
                  <button class="btn btn-xs btn-outline-secondary copy-example-btn" 
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
  setupCardEventListeners(cardDiv, command);

  return cardDiv;
}

/**
 * Set up event listeners for a command card
 */
function setupCardEventListeners(cardDiv, command) {
  // Toggle options
  const optionsToggle = cardDiv.querySelector('.toggle-options');
  if (optionsToggle) {
    optionsToggle.addEventListener('click', (e) => {
      const target = e.target.closest('.toggle-options').dataset.target;
      const collapse = document.getElementById(target);
      const icon = e.target.closest('.toggle-options').querySelector('i');
      
      collapse.classList.toggle('show');
      icon.classList.toggle('fa-chevron-down');
      icon.classList.toggle('fa-chevron-up');
    });
  }

  // Toggle examples
  const examplesToggle = cardDiv.querySelector('.toggle-examples');
  if (examplesToggle) {
    examplesToggle.addEventListener('click', (e) => {
      const target = e.target.closest('.toggle-examples').dataset.target;
      const collapse = document.getElementById(target);
      const icon = e.target.closest('.toggle-examples').querySelector('i');
      
      collapse.classList.toggle('show');
      icon.classList.toggle('fa-chevron-down');
      icon.classList.toggle('fa-chevron-up');
    });
  }

  // Copy command
  const copyBtn = cardDiv.querySelector('.copy-btn');
  if (copyBtn) {
    copyBtn.addEventListener('click', () => {
      copyToClipboard(command.name, copyBtn);
    });
  }

  // Copy example commands
  const copyExampleBtns = cardDiv.querySelectorAll('.copy-example-btn');
  copyExampleBtns.forEach(btn => {
    btn.addEventListener('click', (e) => {
      const cmd = e.target.closest('.copy-example-btn').dataset.command;
      copyToClipboard(cmd, btn);
    });
  });

  // Favorite toggle
  const favoriteBtn = cardDiv.querySelector('.favorite-btn');
  if (favoriteBtn) {
    favoriteBtn.addEventListener('click', () => {
      toggleFavorite(command.name, favoriteBtn);
    });
  }
}

/**
 * Copy text to clipboard with visual feedback
 */
function copyToClipboard(text, button) {
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
function toggleFavorite(commandName, button) {
  if (favoriteCommands.has(commandName)) {
    favoriteCommands.delete(commandName);
    button.classList.remove('active');
  } else {
    favoriteCommands.add(commandName);
    button.classList.add('active');
  }
  
  saveFavorites();
  updateFavoritesCount();
}

/**
 * Toggle favorites view
 */
function toggleFavoritesView() {
  const btn = document.getElementById('showFavoritesBtn');
  if (!btn) return;

  btn.classList.toggle('active');
  
  if (btn.classList.contains('active')) {
    btn.innerHTML = '<i class="fas fa-star me-1"></i> Show All';
  } else {
    btn.innerHTML = `<i class="fas fa-star me-1"></i> Favorites (<span id="favoritesCount">${favoriteCommands.size}</span>)`;
  }
  
  filterCommands();
}

/**
 * Get difficulty badge color
 */
function getDifficultyColor(difficulty) {
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
function loadFavorites() {
  try {
    const saved = localStorage.getItem('linuxCommandsFavorites');
    if (saved) {
      favoriteCommands = new Set(JSON.parse(saved));
    }
  } catch (error) {
    console.error('Error loading favorites:', error);
    favoriteCommands = new Set();
  }
  updateFavoritesCount();
}

/**
 * Save favorites to localStorage
 */
function saveFavorites() {
  try {
    localStorage.setItem('linuxCommandsFavorites', JSON.stringify([...favoriteCommands]));
  } catch (error) {
    console.error('Error saving favorites:', error);
  }
}

/**
 * Update favorites count display
 */
function updateFavoritesCount() {
  const countSpan = document.getElementById('favoritesCount');
  if (countSpan) {
    countSpan.textContent = favoriteCommands.size;
  }
}

/**
 * Show error message
 */
function showError(message) {
  const commandsGrid = document.getElementById('commandsGrid');
  const loadingDiv = document.getElementById('commandsLoading');
  
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
function checkUrlParams() {
  const hash = window.location.hash;
  if (hash && hash.startsWith('#linux-commands')) {
    const paramsStr = hash.split('?')[1] || '';
    const searchParams = new URLSearchParams(paramsStr);
    
    const search = searchParams.get('search');
    const category = searchParams.get('category');
    const difficulty = searchParams.get('difficulty');
    
    if (search) {
      const searchInput = document.getElementById('commandSearch');
      if (searchInput) searchInput.value = search;
    }
    
    if (category) {
      const categoryFilter = document.getElementById('categoryFilter');
      if (categoryFilter) categoryFilter.value = category;
    }
    
    if (difficulty) {
      const difficultyButtons = document.querySelectorAll('[data-difficulty]');
      difficultyButtons.forEach(btn => {
        btn.classList.remove('active');
        if (btn.dataset.difficulty === difficulty) {
          btn.classList.add('active');
        }
      });
    }
    
    // Apply filters after a short delay to ensure data is loaded
    setTimeout(() => {
      filterCommands();
    }, 500);
  }
}

// Export the setup function
export default setupLinuxCommands;