<!-- Password Strength Modal -->
<div class="modal fade" id="passwordStrengthModal" tabindex="-1" aria-labelledby="passwordStrengthModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="passwordStrengthModalLabel">Password Tester & Generator</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <!-- Tabs for Test and Generate -->
                <ul class="nav nav-tabs" id="passwordToolTabs" role="tablist">
                    <li class="nav-item" role="presentation">
                        <button class="nav-link active" id="password-test-tab" data-bs-toggle="tab" data-bs-target="#password-test" type="button" role="tab" aria-controls="password-test" aria-selected="true">
                            <i class="fas fa-check-circle me-2"></i>Test Strength
                        </button>
                    </li>
                    <li class="nav-item" role="presentation">
                        <button class="nav-link" id="password-generate-tab" data-bs-toggle="tab" data-bs-target="#password-generate" type="button" role="tab" aria-controls="password-generate" aria-selected="false">
                            <i class="fas fa-dice me-2"></i>Generate Password
                        </button>
                    </li>
                </ul>
                
                <div class="tab-content mt-3" id="passwordToolTabContent">
                    <!-- Password Test Tab -->
                    <div class="tab-pane fade show active" id="password-test" role="tabpanel" aria-labelledby="password-test-tab">
                        <div class="alert alert-info" role="alert">
                            <i class="fas fa-info-circle me-2"></i> Test your password strength in real-time. All analysis is done locally - your password never leaves your browser.
                        </div>
                        
                        <form id="passwordTestForm" onsubmit="return false;">
                            <div class="mb-3">
                                <label for="passwordInput" class="form-label">Password to Test</label>
                                <div class="input-group">
                                    <input type="password" class="form-control" id="passwordInput" placeholder="Enter password to test" autocomplete="off">
                                    <button class="btn btn-outline-secondary" type="button" id="togglePasswordVisibility">
                                        <i class="fas fa-eye"></i>
                                    </button>
                                </div>
                            </div>
                            
                            <!-- Password Strength Meter -->
                            <div id="passwordStrengthMeter" class="mb-4 d-none">
                                <div class="d-flex justify-content-between align-items-center mb-2">
                                    <span class="fw-bold">Strength:</span>
                                    <span id="strengthLabel" class="badge">-</span>
                                </div>
                                <div class="progress" style="height: 25px;">
                                    <div id="strengthBar" class="progress-bar" role="progressbar" style="width: 0%;" aria-valuenow="0" aria-valuemin="0" aria-valuemax="100"></div>
                                </div>
                            </div>
                            
                            <!-- Password Analysis Results -->
                            <div id="passwordAnalysis" class="d-none">
                                <h6 class="mb-3">Analysis Details</h6>
                                <div class="row">
                                    <div class="col-md-6">
                                        <ul class="list-unstyled" id="passwordStats">
                                            <!-- Stats will be populated here -->
                                        </ul>
                                    </div>
                                    <div class="col-md-6">
                                        <div id="passwordFeedback">
                                            <!-- Feedback will be populated here -->
                                        </div>
                                    </div>
                                </div>
                            </div>
                            
                            <!-- Advanced Analysis Toggle -->
                            <div class="mt-3">
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" id="advancedAnalysis">
                                    <label class="form-check-label" for="advancedAnalysis">
                                        Enable advanced pattern detection (loads additional resources)
                                    </label>
                                </div>
                            </div>
                        </form>
                    </div>
                    
                    <!-- Password Generate Tab -->
                    <div class="tab-pane fade" id="password-generate" role="tabpanel" aria-labelledby="password-generate-tab">
                        <div class="alert alert-info" role="alert">
                            <i class="fas fa-info-circle me-2"></i> Generate cryptographically secure passwords using your browser's Web Crypto API.
                        </div>
                        
                        <form id="passwordGenerateForm" onsubmit="return false;">
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label for="passwordLengthValue" class="form-label">Password Length</label>
                                        <input type="number" class="form-control" id="passwordLengthValue" min="8" max="64" value="16">
                                    </div>
                                    
                                    <div class="mb-3">
                                        <label class="form-label">Character Types</label>
                                        <div class="form-check">
                                            <input class="form-check-input" type="checkbox" id="includeUppercase" checked>
                                            <label class="form-check-label" for="includeUppercase">
                                                Uppercase (A-Z)
                                            </label>
                                        </div>
                                        <div class="form-check">
                                            <input class="form-check-input" type="checkbox" id="includeLowercase" checked>
                                            <label class="form-check-label" for="includeLowercase">
                                                Lowercase (a-z)
                                            </label>
                                        </div>
                                        <div class="form-check">
                                            <input class="form-check-input" type="checkbox" id="includeNumbers" checked>
                                            <label class="form-check-label" for="includeNumbers">
                                                Numbers (0-9)
                                            </label>
                                        </div>
                                        <div class="form-check">
                                            <input class="form-check-input" type="checkbox" id="includeSymbols" checked>
                                            <label class="form-check-label" for="includeSymbols">
                                                Symbols (!@#$%^&amp;*)
                                            </label>
                                        </div>
                                    </div>
                                </div>
                                
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label for="excludeChars" class="form-label">Exclude Characters</label>
                                        <input type="text" class="form-control" id="excludeChars" placeholder="e.g., 0O1lI">
                                        <div class="form-text">Characters to exclude from the password</div>
                                    </div>
                                    
                                    
                                    <div class="mb-3">
                                        <label for="passwordCount" class="form-label">Number of Passwords</label>
                                        <select class="form-select" id="passwordCount">
                                            <option value="1">1</option>
                                            <option value="5">5</option>
                                            <option value="10">10</option>
                                        </select>
                                    </div>
                                </div>
                            </div>
                            
                            <div class="text-center mb-3">
                                <button type="button" class="btn btn-primary" id="generatePasswordBtn">
                                    <i class="fas fa-dice me-2"></i>Generate Password
                                </button>
                            </div>
                            
                            <!-- Generated Passwords Display -->
                            <div id="generatedPasswords" class="d-none">
                                <h6 class="mb-3">Generated Passwords</h6>
                                <div id="passwordList" class="list-group">
                                    <!-- Generated passwords will be displayed here -->
                                </div>
                            </div>
                            
                            <!-- Password History (Session Only) -->
                            <div id="passwordHistory" class="mt-4 d-none">
                                <h6 class="mb-2">Session History</h6>
                                <div class="form-text mb-2">History is stored only for this session and will be cleared when you close this window.</div>
                                <div id="historyList" class="list-group" style="max-height: 200px; overflow-y: auto;">
                                    <!-- History will be displayed here -->
                                </div>
                                <button type="button" class="btn btn-sm btn-outline-danger mt-2" id="clearHistoryBtn">
                                    <i class="fas fa-trash me-1"></i>Clear History
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>