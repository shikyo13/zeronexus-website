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
                        
                        <h6 class="mt-3">Direct Linking</h6>
                        <p class="small">You can directly link to this tool using: <code>#subnet-calculator</code> or with parameters: <code>#subnet-calculator?ip=192.168.1.1&subnet=/24</code></p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>