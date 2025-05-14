<?php
/**
 * IT Admin Tools
 *
 * A collection of utilities and references for IT professionals.
 */

// Page variables
$page_title = "IT Admin Tools - ZeroNexus";
$page_description = "Essential utilities and references for IT professionals.";
$page_css = "/css/network-admin.css";
$page_js = "/js/network-admin.js";
$header_title = "IT Admin Tools";
$header_subtitle = "Essential utilities for IT professionals";

// Include header
include 'includes/header.php';
?>

<main>
    <div class="container">
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

        <!-- Tab content -->
        <div class="tab-content" id="toolTabsContent">
            <!-- All Tools -->
            <div class="tab-pane fade show active" id="all" role="tabpanel" aria-labelledby="all-tab">
                <h2 class="mb-4">All Tools</h2>
                <div class="row row-cols-1 row-cols-md-2 row-cols-lg-3 g-4">
                    <!-- DIAGNOSTICS CATEGORY -->
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
                                <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#pingTracerouteModal">
                                    <i class="fas fa-route me-2"></i>Use Tool
                                </button>
                            </div>
                        </div>
                    </div>

                    <!-- SECURITY CATEGORY -->
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
                                <div class="btn btn-outline-primary disabled">
                                    <i class="fas fa-shield-alt me-2"></i>Coming Soon
                                </div>
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

                    <!-- COMMANDS CATEGORY -->                    
                    <!-- Linux Commands -->
                    <div class="col">
                        <div class="tool-card">
                            <div class="card-header">
                                <i class="fab fa-linux fa-2x"></i>
                                <h3>Linux Networking Commands</h3>
                            </div>
                            <div class="card-body">
                                <p>Essential Linux commands for network diagnostics and configuration.</p>
                                <div class="btn btn-outline-primary disabled">
                                    <i class="fas fa-terminal me-2"></i>Coming Soon
                                </div>
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
                                <div class="btn btn-outline-primary disabled">
                                    <i class="fas fa-terminal me-2"></i>Coming Soon
                                </div>
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
                                <div class="btn btn-outline-primary disabled">
                                    <i class="fas fa-terminal me-2"></i>Coming Soon
                                </div>
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
                                <div class="btn btn-outline-primary disabled">
                                    <i class="fas fa-code me-2"></i>Coming Soon
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- REFERENCES CATEGORY -->
                    <!-- OSI Model -->
                    <div class="col">
                        <div class="tool-card">
                            <div class="card-header">
                                <i class="fas fa-layer-group fa-2x"></i>
                                <h3>OSI Model Reference</h3>
                            </div>
                            <div class="card-body">
                                <p>Interactive guide to the OSI Model layers and protocols.</p>
                                <div class="btn btn-outline-primary disabled">
                                    <i class="fas fa-layer-group me-2"></i>Coming Soon
                                </div>
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
                                <div class="btn btn-outline-primary disabled">
                                    <i class="fas fa-sitemap me-2"></i>Coming Soon
                                </div>
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
                                <div class="btn btn-outline-primary disabled">
                                    <i class="fas fa-plug me-2"></i>Coming Soon
                                </div>
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
                                <div class="btn btn-outline-primary disabled">
                                    <i class="fas fa-exchange-alt me-2"></i>Coming Soon
                                </div>
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
                                <div class="btn btn-outline-primary disabled">
                                    <i class="fas fa-stream me-2"></i>Coming Soon
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- CALCULATORS CATEGORY -->
                    <!-- Binary/Hex/Decimal Converter -->
                    <div class="col">
                        <div class="tool-card">
                            <div class="card-header">
                                <i class="fas fa-exchange-alt fa-2x"></i>
                                <h3>Binary/Hex/Decimal Converter</h3>
                            </div>
                            <div class="card-body">
                                <p>Convert between binary, hexadecimal, and decimal number systems.</p>
                                <div class="btn btn-outline-primary disabled">
                                    <i class="fas fa-exchange-alt me-2"></i>Coming Soon
                                </div>
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
                                <div class="btn btn-outline-primary disabled">
                                    <i class="fas fa-network-wired me-2"></i>Coming Soon
                                </div>
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
                                <div class="btn btn-outline-primary disabled">
                                    <i class="fas fa-random me-2"></i>Coming Soon
                                </div>
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
                                <div class="btn btn-outline-primary disabled">
                                    <i class="fas fa-tachometer-alt me-2"></i>Coming Soon
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- TEMPLATES CATEGORY -->
                    <!-- Network Diagram Templates -->
                    <div class="col">
                        <div class="tool-card">
                            <div class="card-header">
                                <i class="fas fa-project-diagram fa-2x"></i>
                                <h3>Network Diagram Templates</h3>
                            </div>
                            <div class="card-body">
                                <p>Templates for creating professional network topology diagrams.</p>
                                <div class="btn btn-outline-primary disabled">
                                    <i class="fas fa-download me-2"></i>Coming Soon
                                </div>
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
                                <div class="btn btn-outline-primary disabled">
                                    <i class="fas fa-download me-2"></i>Coming Soon
                                </div>
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
                                <div class="btn btn-outline-primary disabled">
                                    <i class="fas fa-download me-2"></i>Coming Soon
                                </div>
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
                                <div class="btn btn-outline-primary disabled">
                                    <i class="fas fa-download me-2"></i>Coming Soon
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Diagnostic Tools -->
            <div class="tab-pane fade" id="diagnostics" role="tabpanel" aria-labelledby="diagnostics-tab">
                <h2 class="mb-4">Diagnostic Tools</h2>
                <div class="row row-cols-1 row-cols-md-2 row-cols-lg-3 g-4">
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
                                <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#pingTracerouteModal">
                                    <i class="fas fa-route me-2"></i>Use Tool
                                </button>
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
                                <div class="btn btn-outline-primary disabled">
                                    <i class="fas fa-shield-alt me-2"></i>Coming Soon
                                </div>
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
                                <div class="btn btn-outline-primary disabled">
                                    <i class="fas fa-terminal me-2"></i>Coming Soon
                                </div>
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
                                <div class="btn btn-outline-primary disabled">
                                    <i class="fas fa-terminal me-2"></i>Coming Soon
                                </div>
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
                                <div class="btn btn-outline-primary disabled">
                                    <i class="fas fa-terminal me-2"></i>Coming Soon
                                </div>
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
                                <div class="btn btn-outline-primary disabled">
                                    <i class="fas fa-code me-2"></i>Coming Soon
                                </div>
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
                                <div class="btn btn-outline-primary disabled">
                                    <i class="fas fa-layer-group me-2"></i>Coming Soon
                                </div>
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
                                <div class="btn btn-outline-primary disabled">
                                    <i class="fas fa-sitemap me-2"></i>Coming Soon
                                </div>
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
                                <div class="btn btn-outline-primary disabled">
                                    <i class="fas fa-plug me-2"></i>Coming Soon
                                </div>
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
                                <div class="btn btn-outline-primary disabled">
                                    <i class="fas fa-exchange-alt me-2"></i>Coming Soon
                                </div>
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
                                <div class="btn btn-outline-primary disabled">
                                    <i class="fas fa-stream me-2"></i>Coming Soon
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Calculators and Converters -->
            <div class="tab-pane fade" id="calculators" role="tabpanel" aria-labelledby="calculators-tab">
                <h2 class="mb-4">Calculators & Converters</h2>
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

                    <!-- Binary/Hex/Decimal Converter -->
                    <div class="col">
                        <div class="tool-card">
                            <div class="card-header">
                                <i class="fas fa-exchange-alt fa-2x"></i>
                                <h3>Binary/Hex/Decimal Converter</h3>
                            </div>
                            <div class="card-body">
                                <p>Convert between binary, hexadecimal, and decimal number systems.</p>
                                <div class="btn btn-outline-primary disabled">
                                    <i class="fas fa-exchange-alt me-2"></i>Coming Soon
                                </div>
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
                                <div class="btn btn-outline-primary disabled">
                                    <i class="fas fa-network-wired me-2"></i>Coming Soon
                                </div>
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
                                <div class="btn btn-outline-primary disabled">
                                    <i class="fas fa-random me-2"></i>Coming Soon
                                </div>
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
                                <div class="btn btn-outline-primary disabled">
                                    <i class="fas fa-tachometer-alt me-2"></i>Coming Soon
                                </div>
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
                                <div class="btn btn-outline-primary disabled">
                                    <i class="fas fa-download me-2"></i>Coming Soon
                                </div>
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
                                <div class="btn btn-outline-primary disabled">
                                    <i class="fas fa-download me-2"></i>Coming Soon
                                </div>
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
                                <div class="btn btn-outline-primary disabled">
                                    <i class="fas fa-download me-2"></i>Coming Soon
                                </div>
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
                                <div class="btn btn-outline-primary disabled">
                                    <i class="fas fa-download me-2"></i>Coming Soon
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</main>

<!-- Tool Modals (placeholder - will implement actual tool functionality in separate files) -->
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
                            <i class="fas fa-info-circle me-2"></i> Analyze security headers for websites and get recommendations to improve security.
                        </div>
                    </div>

                    <div class="col-md-12">
                        <div class="tool-input-group">
                            <label for="websiteUrl" class="form-label">Website URL:</label>
                            <div class="input-group">
                                <input type="text" class="form-control" id="websiteUrl" placeholder="example.com">
                            </div>
                            <div class="form-text">Enter a domain name (e.g., example.com, sub.example.org)</div>
                        </div>
                    </div>

                    <div class="col-md-12 text-center mt-3 mb-4">
                        <button type="button" class="btn btn-primary" id="checkHeadersBtn">
                            <i class="fas fa-search me-2"></i>Check Headers
                        </button>
                        <button type="button" class="btn btn-outline-secondary ms-2" id="clearHeadersBtn">
                            <i class="fas fa-eraser me-2"></i>Clear
                        </button>
                    </div>

                    <div class="col-md-12">
                        <div class="tool-output d-none" id="headerResults">
                            <div class="d-flex justify-content-between align-items-center mb-3 pb-2 border-bottom">
                                <h6 class="mb-0">Security Headers for <span id="resultWebsite"></span></h6>
                                <div>
                                    <span id="headerScore" class="badge bg-primary me-2">0/100</span>
                                    <button type="button" class="btn btn-sm btn-outline-primary" id="copyHeadersBtn">
                                        <i class="fas fa-copy me-1"></i>Copy Results
                                    </button>
                                </div>
                            </div>

                            <div id="headersList" class="mb-4"></div>
                            
                            <div id="headersRecommendations" class="mt-4">
                                <h6 class="border-bottom pb-2 mb-3">Recommendations</h6>
                                <div id="recommendationsList"></div>
                            </div>
                        </div>

                        <div id="headerError" class="alert alert-danger d-none mt-3" role="alert">
                            <i class="fas fa-exclamation-triangle me-2"></i>
                            <span id="headerErrorText"></span>
                        </div>
                        
                        <div id="headerLoading" class="text-center d-none mt-3">
                            <div class="spinner-border text-primary" role="status">
                                <span class="visually-hidden">Loading...</span>
                            </div>
                            <p class="mt-2">Checking security headers...</p>
                        </div>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <div class="d-flex justify-content-between w-100">
                    <div>
                        <button type="button" class="btn btn-outline-info btn-sm" data-bs-toggle="collapse" data-bs-target="#headersHelp" aria-expanded="false" aria-controls="headersHelp">
                            <i class="fas fa-question-circle me-1"></i> Help
                        </button>
                    </div>
                    <div>
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                    </div>
                </div>

                <div class="collapse w-100 mt-3" id="headersHelp">
                    <div class="card card-body bg-dark">
                        <h6>About Security Headers</h6>
                        <p class="small">Security headers are HTTP response headers that help improve the security of your website by enabling browser security features and preventing common web vulnerabilities.</p>
                        
                        <h6 class="mt-3">Common Security Headers</h6>
                        <div class="row small">
                            <div class="col-sm-6">
                                <ul>
                                    <li><strong>Content-Security-Policy (CSP)</strong>: Controls which resources can be loaded</li>
                                    <li><strong>Strict-Transport-Security (HSTS)</strong>: Forces HTTPS connections</li>
                                    <li><strong>X-Content-Type-Options</strong>: Prevents MIME type sniffing</li>
                                </ul>
                            </div>
                            <div class="col-sm-6">
                                <ul>
                                    <li><strong>X-Frame-Options</strong>: Prevents clickjacking attacks</li>
                                    <li><strong>X-XSS-Protection</strong>: Blocks reflected XSS attacks</li>
                                    <li><strong>Referrer-Policy</strong>: Controls how much referrer info is shared</li>
                                </ul>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

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

<!-- DNS Lookup Modal -->
<div class="modal fade" id="dnsLookupModal" tabindex="-1" aria-labelledby="dnsLookupModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="dnsLookupModalLabel">DNS Lookup Tool</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div class="row">
                    <div class="col-md-12 mb-4">
                        <div class="alert alert-info" role="alert">
                            <i class="fas fa-info-circle me-2"></i> Look up DNS records for a domain name. Select a record type to query specific DNS information.
                        </div>
                    </div>

                    <div class="col-md-8">
                        <div class="tool-input-group">
                            <label for="domainName" class="form-label">Domain Name or IP Address:</label>
                            <input type="text" class="form-control" id="domainName" placeholder="e.g. example.com or 192.168.1.1">
                        </div>
                    </div>

                    <div class="col-md-4">
                        <div class="tool-input-group">
                            <label for="recordType" class="form-label">Record Type:</label>
                            <select class="form-select" id="recordType">
                                <option value="A">A (IPv4 Address)</option>
                                <option value="AAAA">AAAA (IPv6 Address)</option>
                                <option value="MX">MX (Mail Exchange)</option>
                                <option value="NS">NS (Name Server)</option>
                                <option value="TXT">TXT (Text)</option>
                                <option value="CNAME">CNAME (Canonical Name)</option>
                                <option value="SOA">SOA (Start of Authority)</option>
                                <option value="PTR">PTR (Pointer/Reverse) - Requires IP Address</option>
                                <option value="CAA">CAA (Certification Authority Authorization)</option>
                                <option value="SRV">SRV (Service)</option>
                                <option value="ANY">ANY (All Common Records)</option>
                            </select>
                        </div>
                        <div id="ptrHelp" class="alert alert-info d-none mt-2 mb-2">
                            <i class="fas fa-info-circle me-2"></i> 
                            <strong>PTR Lookup:</strong> For reverse DNS lookups, please enter an IP address (not a domain name).
                        </div>
                    </div>

                    <div class="col-md-12 text-center mt-3 mb-4">
                        <button type="button" class="btn btn-primary" id="lookupDnsBtn">
                            <i class="fas fa-search me-2"></i>Lookup
                        </button>
                        <button type="button" class="btn btn-outline-secondary ms-2" id="clearDnsBtn">
                            <i class="fas fa-eraser me-2"></i>Clear
                        </button>
                    </div>

                    <div class="col-md-12">
                        <div class="tool-output d-none" id="dnsResults">
                            <h6 class="border-bottom pb-2 mb-3">DNS Records for <span id="resultDomain"></span></h6>

                            <div class="dns-result-container">
                                <div id="dnsRecordsWrap" class="mb-3">
                                    <div id="dnsRecords" class="dns-records"></div>
                                </div>

                                <div id="dnsVisualization" class="dns-visualization mb-3 d-none"></div>
                            </div>

                            <div class="col-md-12 text-center mt-3">
                                <button type="button" class="btn btn-sm btn-outline-primary" id="copyDnsResultsBtn">
                                    <i class="fas fa-copy me-2"></i>Copy Results
                                </button>
                                <button type="button" class="btn btn-sm btn-outline-primary ms-2" id="toggleVisualizationBtn">
                                    <i class="fas fa-chart-network me-2"></i><span>Show Visualization</span>
                                </button>
                            </div>
                        </div>

                        <div id="dnsError" class="alert alert-danger d-none mt-3" role="alert">
                            <i class="fas fa-exclamation-triangle me-2"></i>
                            <span id="dnsErrorText"></span>
                        </div>

                        <div id="dnsLoading" class="text-center d-none mt-3">
                            <div class="spinner-border text-primary" role="status">
                                <span class="visually-hidden">Loading...</span>
                            </div>
                            <p class="mt-2">Querying DNS servers...</p>
                        </div>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <div class="d-flex justify-content-between w-100">
                    <div>
                        <button type="button" class="btn btn-outline-info btn-sm" data-bs-toggle="collapse" data-bs-target="#dnsHelp" aria-expanded="false" aria-controls="dnsHelp">
                            <i class="fas fa-question-circle me-1"></i> Help
                        </button>
                    </div>
                    <div>
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                    </div>
                </div>

                <div class="collapse w-100 mt-3" id="dnsHelp">
                    <div class="card card-body bg-dark">
                        <h6>How to Use the DNS Lookup Tool</h6>
                        <ul class="small">
                            <li><strong>Domain Name:</strong> Enter a valid domain name (e.g., example.com)</li>
                            <li><strong>IP Address:</strong> For reverse lookups (PTR), enter an IP address</li>
                            <li><strong>Record Type:</strong> Select the DNS record type you want to look up</li>
                            <li>Click <strong>Lookup</strong> to query DNS servers</li>
                        </ul>

                        <h6 class="mt-3">DNS Record Types</h6>
                        <div class="row text-start small">
                            <div class="col-sm-6">
                                <ul>
                                    <li><strong>A:</strong> IPv4 Address</li>
                                    <li><strong>AAAA:</strong> IPv6 Address</li>
                                    <li><strong>MX:</strong> Mail Exchange</li>
                                    <li><strong>NS:</strong> Name Server</li>
                                    <li><strong>TXT:</strong> Text Records</li>
                                </ul>
                            </div>
                            <div class="col-sm-6">
                                <ul>
                                    <li><strong>CNAME:</strong> Canonical Name</li>
                                    <li><strong>SOA:</strong> Start of Authority</li>
                                    <li><strong>PTR:</strong> Pointer/Reverse Lookup</li>
                                    <li><strong>CAA:</strong> CA Authorization</li>
                                    <li><strong>SRV:</strong> Service Records</li>
                                </ul>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Ping/Traceroute Modal -->
<div class="modal fade" id="pingTracerouteModal" tabindex="-1" aria-labelledby="pingTracerouteModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="pingTracerouteModalLabel">Ping & Traceroute Tool</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div class="row">
                    <div class="col-md-12 mb-4">
                        <div class="alert alert-info" role="alert">
                            <i class="fas fa-info-circle me-2"></i> Test network connectivity and visualize network paths between hosts.
                        </div>
                    </div>

                    <div class="col-md-8">
                        <div class="tool-input-group">
                            <label for="hostTarget" class="form-label">Domain or IP Address:</label>
                            <input type="text" class="form-control" id="hostTarget" placeholder="e.g. example.com or 192.168.1.1">
                        </div>
                    </div>

                    <div class="col-md-4">
                        <div class="tool-input-group">
                            <label for="toolType" class="form-label">Tool:</label>
                            <select class="form-select" id="toolType">
                                <option value="ping" selected>Ping</option>
                                <option value="traceroute">Traceroute</option>
                                <option value="mtr">MTR (My Traceroute)</option>
                            </select>
                        </div>
                    </div>
                    
                    <div class="col-md-12 mt-3">
                        <div class="row g-3">
                            <div class="col-md-4">
                                <div class="tool-input-group">
                                    <label for="packetCount" class="form-label">Packet Count:</label>
                                    <input type="number" class="form-control" id="packetCount" min="1" max="20" value="4">
                                    <div class="form-text">Number of packets to send (1-20)</div>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="tool-input-group">
                                    <label for="packetSize" class="form-label">Packet Size:</label>
                                    <input type="number" class="form-control" id="packetSize" min="32" max="1472" value="56">
                                    <div class="form-text">Size in bytes (32-1472)</div>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="tool-input-group">
                                    <label for="timeout" class="form-label">Timeout:</label>
                                    <input type="number" class="form-control" id="timeout" min="1" max="10" value="2">
                                    <div class="form-text">Seconds to wait (1-10)</div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="col-md-12 text-center mt-3 mb-4">
                        <button type="button" class="btn btn-primary" id="runNetworkToolBtn">
                            <i class="fas fa-play me-2"></i>Run Test
                        </button>
                        <button type="button" class="btn btn-outline-secondary ms-2" id="clearNetworkToolBtn">
                            <i class="fas fa-eraser me-2"></i>Clear
                        </button>
                    </div>

                    <div class="col-md-12">
                        <div class="tool-output d-none" id="networkToolResults">
                            <div class="d-flex justify-content-between align-items-center mb-3 pb-2 border-bottom">
                                <h6 class="mb-0"><span id="resultToolType">Ping</span> Results for <span id="resultHost"></span></h6>
                                <div>
                                    <button type="button" class="btn btn-sm btn-outline-primary" id="copyNetworkToolBtn">
                                        <i class="fas fa-copy me-1"></i>Copy Results
                                    </button>
                                </div>
                            </div>

                            <div class="tab-content" id="networkResultsTabContent">
                                <!-- Raw Results Tab -->
                                <div class="tab-pane fade show active" id="rawResults" role="tabpanel" aria-labelledby="rawResults-tab">
                                    <div id="resultOutput" class="network-output code-block"></div>
                                </div>
                                
                                <!-- Visual Results Tab (for traceroute) -->
                                <div class="tab-pane fade" id="visualResults" role="tabpanel" aria-labelledby="visualResults-tab">
                                    <div id="tracerouteVisualization" class="traceroute-vis"></div>
                                </div>
                                
                                <!-- Stats Tab -->
                                <div class="tab-pane fade" id="statsResults" role="tabpanel" aria-labelledby="statsResults-tab">
                                    <div class="row">
                                        <div class="col-md-6">
                                            <div class="card mb-3">
                                                <div class="card-header">Summary</div>
                                                <div class="card-body">
                                                    <table class="table table-sm result-table">
                                                        <tbody>
                                                            <tr>
                                                                <td>Host</td>
                                                                <td id="statsHost"></td>
                                                            </tr>
                                                            <tr>
                                                                <td>Packets</td>
                                                                <td id="statsPackets"></td>
                                                            </tr>
                                                            <tr>
                                                                <td>Success Rate</td>
                                                                <td id="statsSuccess"></td>
                                                            </tr>
                                                            <tr>
                                                                <td>IP Address</td>
                                                                <td id="statsIp"></td>
                                                            </tr>
                                                        </tbody>
                                                    </table>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <div class="card">
                                                <div class="card-header">Latency</div>
                                                <div class="card-body">
                                                    <table class="table table-sm result-table">
                                                        <tbody>
                                                            <tr>
                                                                <td>Min</td>
                                                                <td id="statsMin"></td>
                                                            </tr>
                                                            <tr>
                                                                <td>Average</td>
                                                                <td id="statsAvg"></td>
                                                            </tr>
                                                            <tr>
                                                                <td>Max</td>
                                                                <td id="statsMax"></td>
                                                            </tr>
                                                            <tr>
                                                                <td>Std Dev</td>
                                                                <td id="statsStdDev"></td>
                                                            </tr>
                                                        </tbody>
                                                    </table>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            
                            <!-- Tab navigation for results -->
                            <ul class="nav nav-tabs mt-3" id="networkResultTabs" role="tablist">
                                <li class="nav-item" role="presentation">
                                    <button class="nav-link active" id="rawResults-tab" data-bs-toggle="tab" data-bs-target="#rawResults" type="button" role="tab" aria-controls="rawResults" aria-selected="true">Raw Output</button>
                                </li>
                                <li class="nav-item d-none" id="visualTab" role="presentation">
                                    <button class="nav-link" id="visualResults-tab" data-bs-toggle="tab" data-bs-target="#visualResults" type="button" role="tab" aria-controls="visualResults" aria-selected="false">Visualization</button>
                                </li>
                                <li class="nav-item" role="presentation">
                                    <button class="nav-link" id="statsResults-tab" data-bs-toggle="tab" data-bs-target="#statsResults" type="button" role="tab" aria-controls="statsResults" aria-selected="false">Statistics</button>
                                </li>
                            </ul>
                        </div>

                        <div id="networkToolError" class="alert alert-danger d-none mt-3" role="alert">
                            <i class="fas fa-exclamation-triangle me-2"></i>
                            <span id="networkToolErrorText"></span>
                        </div>
                        
                        <div id="networkToolLoading" class="text-center d-none mt-3">
                            <div class="spinner-border text-primary" role="status">
                                <span class="visually-hidden">Loading...</span>
                            </div>
                            <p class="mt-2">Running <span id="loadingToolType">ping</span>...</p>
                        </div>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <div class="d-flex justify-content-between w-100">
                    <div>
                        <button type="button" class="btn btn-outline-info btn-sm" data-bs-toggle="collapse" data-bs-target="#networkToolHelp" aria-expanded="false" aria-controls="networkToolHelp">
                            <i class="fas fa-question-circle me-1"></i> Help
                        </button>
                    </div>
                    <div>
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                    </div>
                </div>

                <div class="collapse w-100 mt-3" id="networkToolHelp">
                    <div class="card card-body bg-dark">
                        <h6>About the Network Tools</h6>
                        <p class="small">These tools help diagnose network connectivity and trace the route that packets take to reach a destination.</p>
                        
                        <h6 class="mt-3">Available Tools</h6>
                        <div class="row small">
                            <div class="col-sm-4">
                                <strong>Ping</strong>
                                <p>Tests if a host is reachable and measures round-trip time.</p>
                            </div>
                            <div class="col-sm-4">
                                <strong>Traceroute</strong>
                                <p>Shows the path packets take to reach a network host.</p>
                            </div>
                            <div class="col-sm-4">
                                <strong>MTR</strong>
                                <p>Combines ping and traceroute to provide detailed statistics.</p>
                            </div>
                        </div>
                        
                        <h6 class="mt-3">Usage Tips</h6>
                        <ul class="small">
                            <li>Enter a domain name (e.g., example.com) or IP address (e.g., 8.8.8.8)</li>
                            <li>For traceroute, the visualization tab shows a graphical representation of the network path</li>
                            <li>The statistics tab provides summarized metrics about the connection</li>
                        </ul>
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