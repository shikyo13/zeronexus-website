<!-- Ping/Traceroute Modal -->
<div class="modal fade" id="pingTracerouteModal" tabindex="-1" aria-labelledby="pingTracerouteModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="pingTracerouteModalLabel">Ping Tool</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div class="row">
                    <div class="col-md-12 mb-4">
                        <div class="alert alert-info" role="alert">
                            <i class="fas fa-info-circle me-2"></i> Test network connectivity to remote hosts using ping.
                        </div>
                    </div>

                    <div class="col-md-6">
                        <div class="tool-input-group">
                            <label for="hostTarget" class="form-label">Domain or IP Address:</label>
                            <input type="text" class="form-control" id="hostTarget" placeholder="e.g. example.com or 192.168.1.1">
                        </div>
                    </div>

                    <div class="col-md-3">
                        <input type="hidden" id="toolType" value="ping">
                        <div class="tool-input-group">
                            <label for="packetCount" class="form-label"><span id="packetCountLabel">Packet Count:</span></label>
                            <input type="number" class="form-control" id="packetCount" min="1" max="20" value="4" style="background-color: #FFFFFF !important; background: #FFFFFF !important;">
                            <div class="form-text" id="packetCountHelp">Number of packets (1-20)</div>
                        </div>
                    </div>
                    
                    <div class="col-md-12 mt-3">
                        <div class="row g-3">
                            <div class="col-md-6">
                                <div class="tool-input-group">
                                    <label for="packetSize" class="form-label">Packet Size:</label>
                                    <input type="number" class="form-control" id="packetSize" min="32" max="1472" value="56" style="background-color: #FFFFFF !important; background: #FFFFFF !important;">
                                    <div class="form-text">Size in bytes (32-1472)</div>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="tool-input-group">
                                    <label for="timeout" class="form-label">Timeout:</label>
                                    <input type="number" class="form-control" id="timeout" min="1" max="10" value="2" style="background-color: #FFFFFF !important; background: #FFFFFF !important;">
                                    <div class="form-text">Seconds to wait (1-10)</div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="col-md-12 text-center mt-3 mb-4">
                        <button type="button" class="btn btn-primary" id="runNetworkToolBtn">
                            <i class="fas fa-play me-2"></i>Ping Host
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

                            <!-- Tab navigation for results -->
                            <ul class="nav nav-tabs" id="networkResultTabs" role="tablist">
                                <li class="nav-item" role="presentation">
                                    <button class="nav-link active" id="rawResults-tab" data-bs-toggle="tab" data-bs-target="#rawResults" type="button" role="tab" aria-controls="rawResults" aria-selected="true">Raw Output</button>
                                </li>
                                <li class="nav-item" role="presentation">
                                    <button class="nav-link" id="statsResults-tab" data-bs-toggle="tab" data-bs-target="#statsResults" type="button" role="tab" aria-controls="statsResults" aria-selected="false">Statistics</button>
                                </li>
                            </ul>

                            <div class="tab-content mt-3" id="networkResultsTabContent">
                                <!-- Raw Results Tab -->
                                <div class="tab-pane fade show active" id="rawResults" role="tabpanel" aria-labelledby="rawResults-tab">
                                    <div id="resultOutput" class="network-output code-block"></div>
                                </div>
                                
                                <!-- Visual Results Tab removed as it only applies to traceroute -->
                                
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
                
                <!-- Force white backgrounds on all form controls -->
                <script>
                    // Apply white background to all form controls when the modal opens
                    document.addEventListener('DOMContentLoaded', function() {
                        const modal = document.getElementById('pingTracerouteModal');
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

                <div class="collapse w-100 mt-3" id="networkToolHelp">
                    <div class="card card-body bg-dark">
                        <h6>About the Network Tools</h6>
                        <p class="small">These tools help diagnose network connectivity and trace the route that packets take to reach a destination.</p>
                        
                        <h6 class="mt-3">About Ping</h6>
                        <div class="row small">
                            <div class="col-12">
                                <p>The ping tool measures network connectivity by sending ICMP echo request packets to a host and waiting for responses. It provides information about:</p>
                                <ul>
                                    <li><strong>Round Trip Time (RTT)</strong> - How long it takes for packets to travel to the destination and back</li>
                                    <li><strong>Packet Loss</strong> - Percentage of packets that didn't receive a response</li>
                                    <li><strong>Statistics</strong> - Min, average, max, and standard deviation of latency</li>
                                </ul>
                            </div>
                        </div>
                        
                        <h6 class="mt-3">Usage Tips</h6>
                        <ul class="small">
                            <li>Enter a domain name (e.g., example.com) or IP address (e.g., 8.8.8.8)</li>
                            <li>Adjust packet count if needed (more packets = more reliable stats, but takes longer)</li>
                            <li>The statistics tab provides summarized metrics about the connection</li>
                        </ul>
                        
                        <h6 class="mt-3">Direct Linking</h6>
                        <p class="small">You can directly link to this tool using: <code>#ping-traceroute</code> or with parameters: <code>#ping-traceroute?host=example.com&autorun=true</code></p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>