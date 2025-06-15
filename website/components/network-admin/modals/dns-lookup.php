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
                            <select class="form-select" id="recordType" style="background-color: #FFFFFF !important; background: #FFFFFF !important;">
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
                
                <!-- Force white backgrounds on all form controls -->
                <script>
                    // Apply white background to all form controls when the modal opens
                    document.addEventListener('DOMContentLoaded', function() {
                        const modal = document.getElementById('dnsLookupModal');
                        if (modal) {
                            modal.addEventListener('shown.bs.modal', function() {
                                const inputs = modal.querySelectorAll('input, select, textarea');
                                inputs.forEach(input => {
                                    input.style.backgroundColor = '#FFFFFF';
                                    input.style.color = '#212529';
                                    
                                    // Also apply on focus and change
                                    input.addEventListener('focus', () => {
                                        input.style.backgroundColor = '#FFFFFF';
                                        input.style.color = '#212529';
                                    });
                                    input.addEventListener('input', () => {
                                        input.style.backgroundColor = '#FFFFFF';
                                        input.style.color = '#212529';
                                    });
                                });
                            });
                        }
                    });
                </script>

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
                        
                        <h6 class="mt-3">Direct Linking</h6>
                        <p class="small">You can directly link to this tool using: <code>#dns-lookup</code> or with parameters: <code>#dns-lookup?domain=example.com&type=MX</code></p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>