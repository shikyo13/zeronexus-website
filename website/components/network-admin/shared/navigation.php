<!-- Tab navigation for tool categories -->
<ul class="nav nav-tabs mb-4" id="toolTabs" role="tablist">
    <li class="nav-item" role="presentation">
        <button class="nav-link active" id="all-tab" data-bs-toggle="tab" data-bs-target="#all"
            type="button" role="tab" aria-controls="all" aria-selected="true">
            <i class="fas fa-toolbox me-2"></i>All Tools
        </button>
    </li>
    <li class="nav-item" role="presentation">
        <button class="nav-link" id="diagnostics-tab" data-bs-toggle="tab" data-bs-target="#diagnostics"
            type="button" role="tab" aria-controls="diagnostics" aria-selected="false">
            <i class="fas fa-search-location me-2"></i>Diagnostics
        </button>
    </li>
    <li class="nav-item" role="presentation">
        <button class="nav-link" id="security-tab" data-bs-toggle="tab" data-bs-target="#security"
            type="button" role="tab" aria-controls="security" aria-selected="false">
            <i class="fas fa-shield-alt me-2"></i>Security
        </button>
    </li>
    <li class="nav-item" role="presentation">
        <button class="nav-link" id="commands-tab" data-bs-toggle="tab" data-bs-target="#commands"
            type="button" role="tab" aria-controls="commands" aria-selected="false">
            <i class="fas fa-terminal me-2"></i>Commands
        </button>
    </li>
    <li class="nav-item" role="presentation">
        <button class="nav-link" id="calculators-tab" data-bs-toggle="tab" data-bs-target="#calculators"
            type="button" role="tab" aria-controls="calculators" aria-selected="false">
            <i class="fas fa-calculator me-2"></i>Calculators
        </button>
    </li>
    <li class="nav-item" role="presentation">
        <button class="nav-link" id="references-tab" data-bs-toggle="tab" data-bs-target="#references"
            type="button" role="tab" aria-controls="references" aria-selected="false">
            <i class="fas fa-book me-2"></i>References
        </button>
    </li>
    <li class="nav-item" role="presentation">
        <button class="nav-link" id="templates-tab" data-bs-toggle="tab" data-bs-target="#templates"
            type="button" role="tab" aria-controls="templates" aria-selected="false">
            <i class="fas fa-file-alt me-2"></i>Templates
        </button>
    </li>
</ul>

<!-- No results message for search (hidden) -->
<div class="no-results-message d-none" id="noResultsMessage">
    <i class="fas fa-search"></i>
    <h4>No tools found</h4>
    <p>Try using different keywords or browse tools by category.</p>
    <button class="btn btn-outline-primary mt-3" id="resetSearchBtn">
        <i class="fas fa-undo me-2"></i>Reset Search
    </button>
</div>

<!-- Search results count (hidden) -->
<div class="search-count d-none" id="searchCount"></div>