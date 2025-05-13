<?php
/**
 * Network Admin Tools
 * 
 * A collection of utilities and references for network administrators and IT professionals.
 */

// Page variables
$page_title = "Network Admin Tools - ZeroNexus";
$page_description = "Essential utilities and references for network administrators and IT professionals.";
$page_css = "/css/network-admin.css";
$page_js = "/js/network-admin.js";
$header_title = "Network Admin Tools";
$header_subtitle = "Essential utilities for network professionals";

// Include header
include 'includes/header.php';
?>

<main>
    <div class="container">
        <!-- Tab navigation for tool categories -->
        <ul class="nav nav-tabs mb-4" id="toolTabs" role="tablist">
            <li class="nav-item" role="presentation">
                <button class="nav-link active" id="diagnostics-tab" data-bs-toggle="tab" data-bs-target="#diagnostics" 
                    type="button" role="tab" aria-controls="diagnostics" aria-selected="true">
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
                <button class="nav-link" id="references-tab" data-bs-toggle="tab" data-bs-target="#references" 
                    type="button" role="tab" aria-controls="references" aria-selected="false">
                    <i class="fas fa-book me-2"></i>References
                </button>
            </li>
            <li class="nav-item" role="presentation">
                <button class="nav-link" id="calculators-tab" data-bs-toggle="tab" data-bs-target="#calculators" 
                    type="button" role="tab" aria-controls="calculators" aria-selected="false">
                    <i class="fas fa-calculator me-2"></i>Calculators
                </button>
            </li>
            <li class="nav-item" role="presentation">
                <button class="nav-link" id="templates-tab" data-bs-toggle="tab" data-bs-target="#templates" 
                    type="button" role="tab" aria-controls="templates" aria-selected="false">
                    <i class="fas fa-file-alt me-2"></i>Templates
                </button>
            </li>
        </ul>

        <!-- Tab content -->
        <div class="tab-content" id="toolTabsContent">
            <!-- Diagnostic Tools -->
            <div class="tab-pane fade show active" id="diagnostics" role="tabpanel" aria-labelledby="diagnostics-tab">
                <h2 class="mb-4">Diagnostic Tools</h2>
                <div class="row row-cols-1 row-cols-md-2 row-cols-lg-3 g-4">
                    <!-- IP Subnet Calculator -->
                    <div class="col">
                        <div class="tool-card">
                            <div class="card-header">
                                <i class="fas fa-network-wired fa-2x"></i>
                                <h3>IP Subnet Calculator</h3>
                            </div>
                            <div class="card-body">
                                <p>Calculate subnet masks, CIDR notation, IP ranges, and more.</p>
                                <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#subnetCalculatorModal">
                                    <i class="fas fa-calculator me-2"></i>Use Tool
                                </button>
                            </div>
                        </div>
                    </div>
                    
                    <!-- DNS Lookup Tool -->
                    <div class="col">
                        <div class="tool-card">
                            <div class="card-header">
                                <i class="fas fa-search fa-2x"></i>
                                <h3>DNS Lookup</h3>
                            </div>
                            <div class="card-body">
                                <p>Perform DNS lookups including forward/reverse, MX, and more.</p>
                                <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#dnsLookupModal">
                                    <i class="fas fa-search me-2"></i>Use Tool
                                </button>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Ping/Traceroute -->
                    <div class="col">
                        <div class="tool-card">
                            <div class="card-header">
                                <i class="fas fa-route fa-2x"></i>
                                <h3>Ping/Traceroute</h3>
                            </div>
                            <div class="card-body">
                                <p>Visualize network paths and measure latency between hosts.</p>
                                <div class="btn btn-outline-primary disabled">
                                    <i class="fas fa-route me-2"></i>Coming Soon
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Security Tools -->
            <div class="tab-pane fade" id="security" role="tabpanel" aria-labelledby="security-tab">
                <h2 class="mb-4">Security Tools</h2>
                <div class="row row-cols-1 row-cols-md-2 row-cols-lg-3 g-4">
                    <!-- Security Headers Checker -->
                    <div class="col">
                        <div class="tool-card">
                            <div class="card-header">
                                <i class="fas fa-lock fa-2x"></i>
                                <h3>Security Headers Checker</h3>
                            </div>
                            <div class="card-body">
                                <p>Analyze security headers for websites and get recommendations.</p>
                                <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#securityHeadersModal">
                                    <i class="fas fa-lock me-2"></i>Use Tool
                                </button>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Firewall Rule Generator -->
                    <div class="col">
                        <div class="tool-card">
                            <div class="card-header">
                                <i class="fas fa-shield-alt fa-2x"></i>
                                <h3>Firewall Rule Generator</h3>
                            </div>
                            <div class="card-body">
                                <p>Generate firewall rules for different platforms like iptables, UFW, and more.</p>
                                <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#firewallRuleModal">
                                    <i class="fas fa-shield-alt me-2"></i>Use Tool
                                </button>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Password Strength Tester -->
                    <div class="col">
                        <div class="tool-card">
                            <div class="card-header">
                                <i class="fas fa-key fa-2x"></i>
                                <h3>Password Strength Tester</h3>
                            </div>
                            <div class="card-body">
                                <p>Test password strength and generate secure passwords.</p>
                                <div class="btn btn-outline-primary disabled">
                                    <i class="fas fa-key me-2"></i>Coming Soon
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Command References -->
            <div class="tab-pane fade" id="commands" role="tabpanel" aria-labelledby="commands-tab">
                <h2 class="mb-4">Command References</h2>
                <div class="row row-cols-1 row-cols-md-2 g-4">
                    <!-- Linux Commands -->
                    <div class="col">
                        <div class="tool-card">
                            <div class="card-header">
                                <i class="fab fa-linux fa-2x"></i>
                                <h3>Linux Networking Commands</h3>
                            </div>
                            <div class="card-body">
                                <p>Essential Linux commands for network diagnostics and configuration.</p>
                                <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#linuxCommandsModal">
                                    <i class="fas fa-terminal me-2"></i>View Commands
                                </button>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Windows Commands -->
                    <div class="col">
                        <div class="tool-card">
                            <div class="card-header">
                                <i class="fab fa-windows fa-2x"></i>
                                <h3>Windows Networking Commands</h3>
                            </div>
                            <div class="card-body">
                                <p>Windows CMD and PowerShell commands for network management.</p>
                                <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#windowsCommandsModal">
                                    <i class="fas fa-terminal me-2"></i>View Commands
                                </button>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Cisco IOS Commands -->
                    <div class="col">
                        <div class="tool-card">
                            <div class="card-header">
                                <i class="fas fa-network-wired fa-2x"></i>
                                <h3>Cisco IOS Commands</h3>
                            </div>
                            <div class="card-body">
                                <p>Common Cisco IOS commands for network device configuration.</p>
                                <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#ciscoCommandsModal">
                                    <i class="fas fa-terminal me-2"></i>View Commands
                                </button>
                            </div>
                        </div>
                    </div>
                    
                    <!-- PowerShell Scripts -->
                    <div class="col">
                        <div class="tool-card">
                            <div class="card-header">
                                <i class="fas fa-code fa-2x"></i>
                                <h3>PowerShell Networking Scripts</h3>
                            </div>
                            <div class="card-body">
                                <p>Useful PowerShell scripts for network administration tasks.</p>
                                <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#powershellScriptsModal">
                                    <i class="fas fa-code me-2"></i>View Scripts
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Reference Guides -->
            <div class="tab-pane fade" id="references" role="tabpanel" aria-labelledby="references-tab">
                <h2 class="mb-4">Reference Guides</h2>
                <div class="row row-cols-1 row-cols-md-2 row-cols-lg-3 g-4">
                    <!-- OSI Model -->
                    <div class="col">
                        <div class="tool-card">
                            <div class="card-header">
                                <i class="fas fa-layer-group fa-2x"></i>
                                <h3>OSI Model Reference</h3>
                            </div>
                            <div class="card-body">
                                <p>Interactive guide to the OSI Model layers and protocols.</p>
                                <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#osiModelModal">
                                    <i class="fas fa-layer-group me-2"></i>View Guide
                                </button>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Troubleshooting Decision Trees -->
                    <div class="col">
                        <div class="tool-card">
                            <div class="card-header">
                                <i class="fas fa-sitemap fa-2x"></i>
                                <h3>Troubleshooting Decision Trees</h3>
                            </div>
                            <div class="card-body">
                                <p>Flowcharts for common network troubleshooting scenarios.</p>
                                <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#troubleshootingModal">
                                    <i class="fas fa-sitemap me-2"></i>View Guide
                                </button>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Common Port Reference -->
                    <div class="col">
                        <div class="tool-card">
                            <div class="card-header">
                                <i class="fas fa-plug fa-2x"></i>
                                <h3>Common Port Reference</h3>
                            </div>
                            <div class="card-body">
                                <p>Searchable list of common network ports and their services.</p>
                                <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#portsReferenceModal">
                                    <i class="fas fa-plug me-2"></i>View Guide
                                </button>
                            </div>
                        </div>
                    </div>
                    
                    <!-- HTTP Status Codes -->
                    <div class="col">
                        <div class="tool-card">
                            <div class="card-header">
                                <i class="fas fa-exchange-alt fa-2x"></i>
                                <h3>HTTP Status Codes</h3>
                            </div>
                            <div class="card-body">
                                <p>Complete reference of HTTP status codes and their meanings.</p>
                                <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#httpStatusModal">
                                    <i class="fas fa-exchange-alt me-2"></i>View Guide
                                </button>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Regex for Log Parsing -->
                    <div class="col">
                        <div class="tool-card">
                            <div class="card-header">
                                <i class="fas fa-stream fa-2x"></i>
                                <h3>Regex for Log Parsing</h3>
                            </div>
                            <div class="card-body">
                                <p>Useful regular expressions for parsing common log formats.</p>
                                <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#regexReferenceModal">
                                    <i class="fas fa-stream me-2"></i>View Guide
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Calculators and Converters -->
            <div class="tab-pane fade" id="calculators" role="tabpanel" aria-labelledby="calculators-tab">
                <h2 class="mb-4">Calculators & Converters</h2>
                <div class="row row-cols-1 row-cols-md-2 row-cols-lg-3 g-4">
                    <!-- Binary/Hex/Decimal Converter -->
                    <div class="col">
                        <div class="tool-card">
                            <div class="card-header">
                                <i class="fas fa-exchange-alt fa-2x"></i>
                                <h3>Binary/Hex/Decimal Converter</h3>
                            </div>
                            <div class="card-body">
                                <p>Convert between binary, hexadecimal, and decimal number systems.</p>
                                <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#numberConverterModal">
                                    <i class="fas fa-exchange-alt me-2"></i>Use Tool
                                </button>
                            </div>
                        </div>
                    </div>
                    
                    <!-- CIDR to Subnet Mask -->
                    <div class="col">
                        <div class="tool-card">
                            <div class="card-header">
                                <i class="fas fa-network-wired fa-2x"></i>
                                <h3>CIDR to Subnet Mask</h3>
                            </div>
                            <div class="card-body">
                                <p>Convert between CIDR notation and subnet masks.</p>
                                <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#cidrConverterModal">
                                    <i class="fas fa-network-wired me-2"></i>Use Tool
                                </button>
                            </div>
                        </div>
                    </div>
                    
                    <!-- IPv4 to IPv6 Converter -->
                    <div class="col">
                        <div class="tool-card">
                            <div class="card-header">
                                <i class="fas fa-random fa-2x"></i>
                                <h3>IPv4 to IPv6 Converter</h3>
                            </div>
                            <div class="card-body">
                                <p>Convert IPv4 addresses to IPv6 format and calculate IPv6 subnets.</p>
                                <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#ipv6ConverterModal">
                                    <i class="fas fa-random me-2"></i>Use Tool
                                </button>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Bandwidth Calculator -->
                    <div class="col">
                        <div class="tool-card">
                            <div class="card-header">
                                <i class="fas fa-tachometer-alt fa-2x"></i>
                                <h3>Bandwidth Calculator</h3>
                            </div>
                            <div class="card-body">
                                <p>Calculate download/upload times, throughput, and more.</p>
                                <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#bandwidthCalculatorModal">
                                    <i class="fas fa-tachometer-alt me-2"></i>Use Tool
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Templates -->
            <div class="tab-pane fade" id="templates" role="tabpanel" aria-labelledby="templates-tab">
                <h2 class="mb-4">Documentation Templates</h2>
                <div class="row row-cols-1 row-cols-md-2 g-4">
                    <!-- Network Diagram Templates -->
                    <div class="col">
                        <div class="tool-card">
                            <div class="card-header">
                                <i class="fas fa-project-diagram fa-2x"></i>
                                <h3>Network Diagram Templates</h3>
                            </div>
                            <div class="card-body">
                                <p>Templates for creating professional network topology diagrams.</p>
                                <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#networkDiagramModal">
                                    <i class="fas fa-download me-2"></i>Download Templates
                                </button>
                            </div>
                        </div>
                    </div>
                    
                    <!-- IT Runbook Templates -->
                    <div class="col">
                        <div class="tool-card">
                            <div class="card-header">
                                <i class="fas fa-book fa-2x"></i>
                                <h3>IT Runbook Templates</h3>
                            </div>
                            <div class="card-body">
                                <p>Templates for creating comprehensive IT operations runbooks.</p>
                                <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#runbookTemplateModal">
                                    <i class="fas fa-download me-2"></i>Download Templates
                                </button>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Incident Response Forms -->
                    <div class="col">
                        <div class="tool-card">
                            <div class="card-header">
                                <i class="fas fa-exclamation-triangle fa-2x"></i>
                                <h3>Incident Response Forms</h3>
                            </div>
                            <div class="card-body">
                                <p>Templates for documenting and responding to security incidents.</p>
                                <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#incidentResponseModal">
                                    <i class="fas fa-download me-2"></i>Download Templates
                                </button>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Change Management Templates -->
                    <div class="col">
                        <div class="tool-card">
                            <div class="card-header">
                                <i class="fas fa-clipboard-check fa-2x"></i>
                                <h3>Change Management Templates</h3>
                            </div>
                            <div class="card-body">
                                <p>Templates for planning and documenting network changes.</p>
                                <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#changeManagementModal">
                                    <i class="fas fa-download me-2"></i>Download Templates
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</main>

<!-- Tool Modals (placeholder - will implement actual tool functionality in separate files) -->
<!-- IP Subnet Calculator Modal -->
<div class="modal fade" id="subnetCalculatorModal" tabindex="-1" aria-labelledby="subnetCalculatorModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="subnetCalculatorModalLabel">IP Subnet Calculator</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div class="row">
                    <div class="col-md-12 mb-4">
                        <div class="alert alert-info" role="alert">
                            <i class="fas fa-info-circle me-2"></i> Calculate subnet information based on IP address and subnet mask or CIDR notation.
                        </div>
                    </div>

                    <div class="col-md-6">
                        <div class="tool-input-group">
                            <label for="ipAddress" class="form-label">IP Address:</label>
                            <input type="text" class="form-control" id="ipAddress" placeholder="e.g. 192.168.1.1">
                        </div>
                    </div>

                    <div class="col-md-6">
                        <div class="tool-input-group">
                            <label for="subnetInput" class="form-label">Subnet Mask or CIDR:</label>
                            <input type="text" class="form-control" id="subnetInput" placeholder="e.g. 255.255.255.0 or /24">
                        </div>
                    </div>

                    <div class="col-md-12 text-center mt-3 mb-4">
                        <button type="button" class="btn btn-primary" id="calculateSubnetBtn">
                            <i class="fas fa-calculator me-2"></i>Calculate
                        </button>
                        <button type="button" class="btn btn-outline-secondary ms-2" id="clearSubnetBtn">
                            <i class="fas fa-eraser me-2"></i>Clear
                        </button>
                    </div>

                    <div class="col-md-12">
                        <div class="tool-output d-none" id="subnetResults">
                            <h6 class="border-bottom pb-2 mb-3">Subnet Information</h6>

                            <div class="row g-3">
                                <div class="col-md-6">
                                    <div class="result-group">
                                        <label class="fw-bold">IP Address:</label>
                                        <div id="resultIpAddress" class="result-value"></div>
                                    </div>
                                </div>

                                <div class="col-md-6">
                                    <div class="result-group">
                                        <label class="fw-bold">Subnet Mask:</label>
                                        <div id="resultSubnetMask" class="result-value"></div>
                                    </div>
                                </div>

                                <div class="col-md-6">
                                    <div class="result-group">
                                        <label class="fw-bold">CIDR Notation:</label>
                                        <div id="resultCidr" class="result-value"></div>
                                    </div>
                                </div>

                                <div class="col-md-6">
                                    <div class="result-group">
                                        <label class="fw-bold">Network Class:</label>
                                        <div id="resultNetworkClass" class="result-value"></div>
                                    </div>
                                </div>

                                <div class="col-md-6">
                                    <div class="result-group">
                                        <label class="fw-bold">Network Address:</label>
                                        <div id="resultNetworkAddress" class="result-value"></div>
                                    </div>
                                </div>

                                <div class="col-md-6">
                                    <div class="result-group">
                                        <label class="fw-bold">Broadcast Address:</label>
                                        <div id="resultBroadcastAddress" class="result-value"></div>
                                    </div>
                                </div>

                                <div class="col-md-6">
                                    <div class="result-group">
                                        <label class="fw-bold">First Usable Host:</label>
                                        <div id="resultFirstHost" class="result-value"></div>
                                    </div>
                                </div>

                                <div class="col-md-6">
                                    <div class="result-group">
                                        <label class="fw-bold">Last Usable Host:</label>
                                        <div id="resultLastHost" class="result-value"></div>
                                    </div>
                                </div>

                                <div class="col-md-6">
                                    <div class="result-group">
                                        <label class="fw-bold">Total Hosts:</label>
                                        <div id="resultTotalHosts" class="result-value"></div>
                                    </div>
                                </div>

                                <div class="col-md-6">
                                    <div class="result-group">
                                        <label class="fw-bold">Usable Hosts:</label>
                                        <div id="resultUsableHosts" class="result-value"></div>
                                    </div>
                                </div>

                                <div class="col-md-12">
                                    <div class="result-group">
                                        <label class="fw-bold">Binary Subnet Mask:</label>
                                        <div id="resultBinaryMask" class="result-value"></div>
                                    </div>
                                </div>

                                <div class="col-md-12 text-center mt-3">
                                    <button type="button" class="btn btn-sm btn-outline-primary" id="copyResultsBtn">
                                        <i class="fas fa-copy me-2"></i>Copy Results
                                    </button>
                                </div>
                            </div>
                        </div>

                        <div id="subnetError" class="alert alert-danger d-none mt-3" role="alert">
                            <i class="fas fa-exclamation-triangle me-2"></i>
                            <span id="subnetErrorText"></span>
                        </div>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <div class="d-flex justify-content-between w-100">
                    <div>
                        <button type="button" class="btn btn-outline-info btn-sm" data-bs-toggle="collapse" data-bs-target="#subnetHelp" aria-expanded="false" aria-controls="subnetHelp">
                            <i class="fas fa-question-circle me-1"></i> Help
                        </button>
                    </div>
                    <div>
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                    </div>
                </div>

                <div class="collapse w-100 mt-3" id="subnetHelp">
                    <div class="card card-body bg-dark">
                        <h6>How to Use the Subnet Calculator</h6>
                        <ul class="small">
                            <li><strong>IP Address:</strong> Enter a valid IPv4 address (e.g., 192.168.1.1)</li>
                            <li><strong>Subnet Mask:</strong> Enter either a subnet mask (e.g., 255.255.255.0) or CIDR notation (e.g., /24)</li>
                            <li>Click <strong>Calculate</strong> to get comprehensive subnet information</li>
                            <li>Use <strong>Copy Results</strong> to copy all results to clipboard</li>
                        </ul>

                        <h6 class="mt-3">Common Subnet Masks</h6>
                        <div class="row text-start small">
                            <div class="col-sm-6">
                                <ul>
                                    <li>/8 - 255.0.0.0 (Class A)</li>
                                    <li>/16 - 255.255.0.0 (Class B)</li>
                                    <li>/24 - 255.255.255.0 (Class C)</li>
                                </ul>
                            </div>
                            <div class="col-sm-6">
                                <ul>
                                    <li>/26 - 255.255.255.192 (64 hosts)</li>
                                    <li>/27 - 255.255.255.224 (32 hosts)</li>
                                    <li>/28 - 255.255.255.240 (16 hosts)</li>
                                </ul>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Add more tool modals as needed -->

<?php
// Include footer
include 'includes/footer.php';
?>