<!-- Security Headers Checker Modal -->
<div class="modal fade" id="securityHeadersModal" tabindex="-1" aria-labelledby="securityHeadersModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="securityHeadersModalLabel">Security Headers Checker</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div class="row">
                    <div class="col-md-12 mb-4">
                        <div class="alert alert-info" role="alert">
                            <i class="fas fa-info-circle me-2"></i> Check security headers for any website and get recommendations for improvement.
                        </div>
                    </div>

                    <div class="col-md-12">
                        <form id="securityHeadersForm" onsubmit="return false;">
                            <div class="tool-input-group">
                                <label for="securityHeadersUrl" class="form-label">Website URL</label>
                                <div class="input-group">
                                    <input type="text" class="form-control" id="securityHeadersUrl" placeholder="e.g. example.com or https://example.com" required>
                                    <button type="submit" class="btn btn-primary" id="checkHeadersBtn">
                                        <i class="fas fa-search me-2"></i>Check Headers
                                    </button>
                                </div>
                                <div class="form-text">Enter a domain name or full URL to analyze its security headers</div>
                            </div>
                        </form>
                        
                        <!-- Loading indicator -->
                        <div id="securityHeadersLoading" class="text-center my-4 d-none">
                            <div class="spinner-border text-primary" role="status">
                                <span class="visually-hidden">Loading...</span>
                            </div>
                            <p class="mt-2">Analyzing security headers...</p>
                        </div>
                        
                        <!-- Error message -->
                        <div id="securityHeadersError" class="alert alert-danger mt-4 d-none">
                            <i class="fas fa-exclamation-triangle me-2"></i>
                            <span id="securityHeadersErrorText"></span>
                        </div>
                        
                        <!-- Results area -->
                        <div id="securityHeadersResults" class="mt-4 d-none">
                            <div class="d-flex justify-content-between align-items-center mb-3">
                                <h5 class="mb-0">Results for <span id="headerResultDomain"></span></h5>
                                <button type="button" class="btn btn-sm btn-outline-primary" id="copyHeadersBtn">
                                    <i class="fas fa-copy me-1"></i>Copy Results
                                </button>
                            </div>
                            
                            <!-- Header info tabs -->
                            <ul class="nav nav-tabs" id="headerResultTabs" role="tablist">
                                <li class="nav-item" role="presentation">
                                    <button class="nav-link active" id="headersTab" data-bs-toggle="tab" data-bs-target="#headersTabContent" type="button" role="tab" aria-controls="headersTabContent" aria-selected="true">Security Headers</button>
                                </li>
                                <li class="nav-item" role="presentation">
                                    <button class="nav-link" id="recommendationsTab" data-bs-toggle="tab" data-bs-target="#recommendationsTabContent" type="button" role="tab" aria-controls="recommendationsTabContent" aria-selected="false">Recommendations</button>
                                </li>
                                <li class="nav-item" role="presentation">
                                    <button class="nav-link" id="allHeadersTab" data-bs-toggle="tab" data-bs-target="#allHeadersTabContent" type="button" role="tab" aria-controls="allHeadersTabContent" aria-selected="false">All Headers</button>
                                </li>
                            </ul>
                            
                            <div class="tab-content p-3 border border-top-0 rounded-bottom" id="headerResultTabContent">
                                <!-- Security Headers Tab -->
                                <div class="tab-pane fade show active" id="headersTabContent" role="tabpanel" aria-labelledby="headersTab">
                                    <div id="securityHeadersList"></div>
                                </div>
                                
                                <!-- Recommendations Tab -->
                                <div class="tab-pane fade" id="recommendationsTabContent" role="tabpanel" aria-labelledby="recommendationsTab">
                                    <div id="recommendationsList"></div>
                                </div>
                                
                                <!-- All Headers Tab -->
                                <div class="tab-pane fade" id="allHeadersTabContent" role="tabpanel" aria-labelledby="allHeadersTab">
                                    <div id="allHeadersList"></div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>