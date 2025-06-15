<!-- Windows Commands Reference Modal -->
<div class="modal fade" id="windowsCommandsModal" tabindex="-1" aria-labelledby="windowsCommandsModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-xl">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="windowsCommandsModalLabel">Essential Windows Commands Reference</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div class="row">
                    <div class="col-md-12 mb-4">
                        <div class="alert alert-info" role="alert">
                            <i class="fas fa-terminal me-2"></i> Comprehensive reference for essential Windows commands with examples and usage patterns.
                        </div>
                    </div>

                    <!-- Search and Filter Controls -->
                    <div class="col-md-12 mb-4">
                        <div class="row">
                            <div class="col-md-8">
                                <div class="tool-input-group">
                                    <label for="windowsCommandSearch" class="form-label">Search Commands:</label>
                                    <div class="input-group">
                                        <input type="text" class="form-control" id="windowsCommandSearch" placeholder="Search by command name, description, or tag...">
                                        <button class="btn btn-outline-secondary" type="button" id="windowsClearSearchBtn">
                                            <i class="fas fa-times"></i>
                                        </button>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="tool-input-group">
                                    <label for="windowsCategoryFilter" class="form-label">Category:</label>
                                    <select class="form-select" id="windowsCategoryFilter">
                                        <option value="all">All Categories</option>
                                        <option value="file-management">File Management</option>
                                        <option value="system-info">System Information</option>
                                        <option value="networking">Networking</option>
                                        <option value="process-management">Process Management</option>
                                        <option value="disk-management">Disk Management</option>
                                        <option value="user-management">User Management</option>
                                        <option value="registry">Registry</option>
                                        <option value="power-management">Power Management</option>
                                        <option value="services">Services</option>
                                        <option value="security">Security</option>
                                    </select>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Difficulty Filter Pills -->
                    <div class="col-md-12 mb-4">
                        <div class="d-flex flex-wrap gap-2">
                            <span class="text-muted small me-2">Difficulty:</span>
                            <button type="button" class="btn btn-sm btn-outline-success active" data-difficulty="all">All</button>
                            <button type="button" class="btn btn-sm btn-outline-success" data-difficulty="beginner">Beginner</button>
                            <button type="button" class="btn btn-sm btn-outline-warning" data-difficulty="intermediate">Intermediate</button>
                            <button type="button" class="btn btn-sm btn-outline-danger" data-difficulty="advanced">Advanced</button>
                        </div>
                    </div>

                    <!-- Commands Grid -->
                    <div class="col-md-12">
                        <div id="windowsCommandsGrid" class="row g-3">
                            <!-- Commands will be populated by JavaScript -->
                        </div>
                        
                        <!-- Loading Indicator -->
                        <div id="windowsCommandsLoading" class="text-center py-4">
                            <div class="spinner-border text-primary" role="status">
                                <span class="visually-hidden">Loading commands...</span>
                            </div>
                        </div>

                        <!-- No Results Message -->
                        <div id="windowsNoResults" class="text-center py-4 d-none">
                            <div class="text-muted">
                                <i class="fas fa-search fa-2x mb-3"></i>
                                <p>No commands match your search criteria.</p>
                                <small>Try adjusting your search terms or filters.</small>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <div class="d-flex justify-content-between w-100">
                    <div>
                        <button type="button" class="btn btn-outline-info btn-sm" data-bs-toggle="collapse" data-bs-target="#windowsCommandsHelp" aria-expanded="false" aria-controls="windowsCommandsHelp">
                            <i class="fas fa-question-circle me-1"></i> Help
                        </button>
                        <button type="button" class="btn btn-outline-primary btn-sm ms-2" id="windowsShowFavoritesBtn">
                            <i class="fas fa-star me-1"></i> Favorites (<span id="windowsFavoritesCount">0</span>)
                        </button>
                    </div>
                    <div>
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                    </div>
                </div>

                <!-- Help Section -->
                <div class="collapse w-100 mt-3" id="windowsCommandsHelp">
                    <div class="card card-body bg-dark">
                        <h6>How to Use the Windows Commands Reference</h6>
                        <div class="row">
                            <div class="col-md-6">
                                <ul class="small">
                                    <li><strong>Search:</strong> Type command names, descriptions, or tags</li>
                                    <li><strong>Filter:</strong> Use category and difficulty filters</li>
                                    <li><strong>Copy:</strong> Click command cards to copy to clipboard</li>
                                    <li><strong>Examples:</strong> Expand cards to see usage examples</li>
                                </ul>
                            </div>
                            <div class="col-md-6">
                                <ul class="small">
                                    <li><strong>Favorites:</strong> Star commands for quick access</li>
                                    <li><strong>Keyboard:</strong> Use arrow keys to navigate</li>
                                    <li><strong>Direct Link:</strong> Use <code>#windows-commands</code></li>
                                    <li><strong>Syntax:</strong> <code>[/option]</code> = optional, <code>file...</code> = multiple</li>
                                </ul>
                            </div>
                        </div>

                        <h6 class="mt-3">Quick Reference</h6>
                        <div class="row text-start small">
                            <div class="col-sm-4">
                                <strong>File Management:</strong>
                                <ul class="mt-1">
                                    <li><code>dir</code> - List files</li>
                                    <li><code>copy</code> - Copy files</li>
                                    <li><code>move</code> - Move/rename</li>
                                    <li><code>del</code> - Delete files</li>
                                </ul>
                            </div>
                            <div class="col-sm-4">
                                <strong>Networking:</strong>
                                <ul class="mt-1">
                                    <li><code>ipconfig</code> - IP configuration</li>
                                    <li><code>ping</code> - Test connectivity</li>
                                    <li><code>netstat</code> - Network stats</li>
                                    <li><code>nslookup</code> - DNS lookup</li>
                                </ul>
                            </div>
                            <div class="col-sm-4">
                                <strong>System Info:</strong>
                                <ul class="mt-1">
                                    <li><code>systeminfo</code> - System details</li>
                                    <li><code>tasklist</code> - List processes</li>
                                    <li><code>wmic</code> - WMI queries</li>
                                    <li><code>powercfg</code> - Power settings</li>
                                </ul>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>