<!-- Firewall Rule Generator Modal -->
<div class="modal fade" id="firewallRuleGeneratorModal" tabindex="-1" aria-labelledby="firewallRuleGeneratorModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-xl">
        <div class="modal-content bg-dark text-light">
            <div class="modal-header border-secondary">
                <h5 class="modal-title" id="firewallRuleGeneratorModalLabel">
                    <i class="fas fa-shield-alt me-2"></i>Firewall Rule Generator
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            
            <div class="modal-body">
                <!-- Platform Selector -->
                <div class="mb-4">
                    <label for="platformSelect" class="form-label">Select Platform</label>
                    <select class="form-select bg-secondary text-light border-secondary" id="platformSelect">
                        <option value="">Choose a firewall platform...</option>
                        <option value="iptables">iptables (Linux)</option>
                        <option value="pfsense">pfSense</option>
                        <option value="cisco-asa">Cisco ASA</option>
                        <option value="fortigate">FortiGate</option>
                        <option value="paloalto">Palo Alto</option>
                        <option value="windows">Windows Firewall</option>
                    </select>
                </div>

                <!-- Template Section -->
                <div class="mb-4" id="templateSection" style="display: none;">
                    <h6 class="text-muted mb-3">Quick Start Templates</h6>
                    <div class="row g-2" id="templateGrid">
                        <!-- Templates will be dynamically added here -->
                    </div>
                </div>

                <!-- Rule Builder Form -->
                <div id="ruleBuilderSection" style="display: none;">
                    <h6 class="text-muted mb-3">Rule Configuration</h6>
                    
                    <!-- Basic Rule Settings -->
                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label for="ruleName" class="form-label">Rule Name/Description</label>
                            <input type="text" class="form-control bg-secondary text-light border-secondary" 
                                   id="ruleName" placeholder="e.g., Allow HTTPS traffic">
                        </div>
                        <div class="col-md-3">
                            <label for="ruleAction" class="form-label">Action</label>
                            <select class="form-select bg-secondary text-light border-secondary" id="ruleAction">
                                <option value="allow">Allow</option>
                                <option value="deny">Deny/Drop</option>
                                <option value="log">Log Only</option>
                            </select>
                        </div>
                        <div class="col-md-3">
                            <label for="ruleProtocol" class="form-label">Protocol</label>
                            <select class="form-select bg-secondary text-light border-secondary" id="ruleProtocol">
                                <option value="any">Any</option>
                                <option value="tcp">TCP</option>
                                <option value="udp">UDP</option>
                                <option value="icmp">ICMP</option>
                            </select>
                        </div>
                    </div>

                    <!-- Source Configuration -->
                    <div class="card bg-secondary mb-3">
                        <div class="card-header">
                            <h6 class="mb-0">Source</h6>
                        </div>
                        <div class="card-body">
                            <div class="row">
                                <div class="col-md-4">
                                    <label class="form-label">Source Type</label>
                                    <select class="form-select bg-dark text-light border-secondary" id="sourceType">
                                        <option value="any">Any</option>
                                        <option value="ip">Single IP</option>
                                        <option value="network">Network/CIDR</option>
                                        <option value="zone">Zone/Interface</option>
                                    </select>
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label">Source Value</label>
                                    <input type="text" class="form-control bg-dark text-light border-secondary" 
                                           id="sourceValue" placeholder="e.g., 192.168.1.0/24" disabled>
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label">Source Port</label>
                                    <input type="text" class="form-control bg-dark text-light border-secondary" 
                                           id="sourcePort" placeholder="any, 80, or 1000-2000">
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Destination Configuration -->
                    <div class="card bg-secondary mb-3">
                        <div class="card-header">
                            <h6 class="mb-0">Destination</h6>
                        </div>
                        <div class="card-body">
                            <div class="row">
                                <div class="col-md-4">
                                    <label class="form-label">Destination Type</label>
                                    <select class="form-select bg-dark text-light border-secondary" id="destType">
                                        <option value="any">Any</option>
                                        <option value="ip">Single IP</option>
                                        <option value="network">Network/CIDR</option>
                                        <option value="zone">Zone/Interface</option>
                                    </select>
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label">Destination Value</label>
                                    <input type="text" class="form-control bg-dark text-light border-secondary" 
                                           id="destValue" placeholder="e.g., 10.0.0.5" disabled>
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label">Destination Port</label>
                                    <div class="input-group">
                                        <input type="text" class="form-control bg-dark text-light border-secondary" 
                                               id="destPort" placeholder="443, 80,443 or 1-1024">
                                        <button class="btn btn-outline-secondary dropdown-toggle" type="button" 
                                                data-bs-toggle="dropdown" aria-expanded="false">
                                            <i class="fas fa-list"></i>
                                        </button>
                                        <ul class="dropdown-menu dropdown-menu-end bg-dark">
                                            <li><a class="dropdown-item text-light" href="#" data-port="22">SSH (22)</a></li>
                                            <li><a class="dropdown-item text-light" href="#" data-port="80">HTTP (80)</a></li>
                                            <li><a class="dropdown-item text-light" href="#" data-port="443">HTTPS (443)</a></li>
                                            <li><a class="dropdown-item text-light" href="#" data-port="3389">RDP (3389)</a></li>
                                            <li><a class="dropdown-item text-light" href="#" data-port="3306">MySQL (3306)</a></li>
                                            <li><a class="dropdown-item text-light" href="#" data-port="5432">PostgreSQL (5432)</a></li>
                                            <li><a class="dropdown-item text-light" href="#" data-port="25">SMTP (25)</a></li>
                                            <li><a class="dropdown-item text-light" href="#" data-port="53">DNS (53)</a></li>
                                            <li><hr class="dropdown-divider"></li>
                                            <li><a class="dropdown-item text-light" href="#" data-port="80,443">Web (80,443)</a></li>
                                            <li><a class="dropdown-item text-light" href="#" data-port="25,587,993,995">Mail (25,587,993,995)</a></li>
                                        </ul>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Advanced Options (Collapsible) -->
                    <div class="mb-3">
                        <button class="btn btn-sm btn-outline-secondary" type="button" 
                                data-bs-toggle="collapse" data-bs-target="#advancedOptions">
                            <i class="fas fa-cog me-2"></i>Advanced Options
                        </button>
                        <div class="collapse mt-3" id="advancedOptions">
                            <div class="card bg-secondary">
                                <div class="card-body">
                                    <div class="row">
                                        <div class="col-md-4">
                                            <label class="form-label">Interface</label>
                                            <input type="text" class="form-control bg-dark text-light border-secondary" 
                                                   id="ruleInterface" placeholder="e.g., eth0, wan, inside">
                                        </div>
                                        <div class="col-md-4">
                                            <label class="form-label">Direction</label>
                                            <select class="form-select bg-dark text-light border-secondary" id="ruleDirection">
                                                <option value="">Auto</option>
                                                <option value="in">Inbound</option>
                                                <option value="out">Outbound</option>
                                            </select>
                                        </div>
                                        <div class="col-md-4">
                                            <label class="form-label">Logging</label>
                                            <div class="form-check form-switch mt-2">
                                                <input class="form-check-input" type="checkbox" id="enableLogging">
                                                <label class="form-check-label" for="enableLogging">
                                                    Enable logging for this rule
                                                </label>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="row mt-3">
                                        <div class="col-12">
                                            <label class="form-label">Comment</label>
                                            <input type="text" class="form-control bg-dark text-light border-secondary" 
                                                   id="ruleComment" placeholder="Optional comment for the rule">
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Generate Button -->
                    <div class="d-flex justify-content-between align-items-center mb-3">
                        <button type="button" class="btn btn-outline-warning" id="clearRuleBtn">
                            <i class="fas fa-eraser me-2"></i>Clear Rule
                        </button>
                        <button type="button" class="btn btn-primary" id="generateRuleBtn">
                            <i class="fas fa-magic me-2"></i>Generate Rule
                        </button>
                    </div>
                </div>

                <!-- Generated Output -->
                <div id="generatedOutput" style="display: none;">
                    <div class="d-flex justify-content-between align-items-center mb-2">
                        <h6 class="text-muted mb-0">Generated Rule</h6>
                        <div>
                            <button type="button" class="btn btn-sm btn-outline-info" id="copyRuleBtn">
                                <i class="fas fa-copy me-1"></i>Copy
                            </button>
                            <button type="button" class="btn btn-sm btn-outline-secondary" id="newRuleBtn">
                                <i class="fas fa-plus me-1"></i>New Rule
                            </button>
                        </div>
                    </div>
                    <pre class="bg-black p-3 rounded border border-secondary"><code id="generatedRuleCode" class="language-bash"></code></pre>
                    
                    <!-- Platform-specific notes -->
                    <div id="platformNotes" class="alert alert-info mt-3" style="display: none;">
                        <i class="fas fa-info-circle me-2"></i>
                        <span id="platformNotesContent"></span>
                    </div>
                </div>

                <!-- Warnings -->
                <div id="warningSection" class="alert alert-warning" style="display: none;">
                    <i class="fas fa-exclamation-triangle me-2"></i>
                    <span id="warningContent"></span>
                </div>
            </div>

            <div class="modal-footer border-secondary">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                <button type="button" class="btn btn-outline-info" id="helpBtn">
                    <i class="fas fa-question-circle me-2"></i>Help
                </button>
            </div>
        </div>
    </div>
</div>

<!-- Help Modal -->
<div class="modal fade" id="firewallHelpModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content bg-dark text-light">
            <div class="modal-header border-secondary">
                <h5 class="modal-title">Firewall Rule Generator Help</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <h6>Overview</h6>
                <p>The Firewall Rule Generator helps you create syntactically correct firewall rules for multiple platforms. It provides both templates for common scenarios and a visual rule builder for custom configurations.</p>
                
                <h6>Supported Platforms</h6>
                <ul>
                    <li><strong>iptables:</strong> Linux netfilter firewall</li>
                    <li><strong>pfSense:</strong> Open-source firewall/router</li>
                    <li><strong>Cisco ASA:</strong> Adaptive Security Appliance</li>
                    <li><strong>FortiGate:</strong> Fortinet firewall appliances</li>
                    <li><strong>Palo Alto:</strong> Next-generation firewalls</li>
                    <li><strong>Windows Firewall:</strong> Windows Advanced Firewall</li>
                </ul>

                <h6>Quick Start</h6>
                <ol>
                    <li>Select your firewall platform</li>
                    <li>Choose a template or build a custom rule</li>
                    <li>Configure source and destination settings</li>
                    <li>Click "Generate Rule" to create the command</li>
                    <li>Copy the generated rule to your clipboard</li>
                </ol>

                <h6>Rule Components</h6>
                <ul>
                    <li><strong>Action:</strong> Allow permits traffic, Deny blocks it</li>
                    <li><strong>Protocol:</strong> TCP for web/SSH, UDP for DNS/VPN, ICMP for ping</li>
                    <li><strong>Source/Destination:</strong> Can be any, single IP, network range, or zone</li>
                    <li><strong>Ports:</strong> Single (443), multiple (80,443), or ranges (1000-2000)</li>
                </ul>

                <h6>Safety Tips</h6>
                <ul>
                    <li>Avoid overly permissive rules (any/any)</li>
                    <li>Test rules in a safe environment first</li>
                    <li>Always have a backup access method</li>
                    <li>Review generated rules before applying</li>
                </ul>

                <h6>Keyboard Shortcuts</h6>
                <ul>
                    <li><kbd>Ctrl</kbd> + <kbd>G</kbd> - Generate rule</li>
                    <li><kbd>Ctrl</kbd> + <kbd>C</kbd> - Copy generated rule</li>
                    <li><kbd>Esc</kbd> - Clear form</li>
                </ul>
            </div>
            <div class="modal-footer border-secondary">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>