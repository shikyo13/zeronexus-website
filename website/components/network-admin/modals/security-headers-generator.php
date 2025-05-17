<!-- Security Headers Generator Modal -->
<div class="modal fade" id="securityHeadersGeneratorModal" tabindex="-1" aria-labelledby="securityHeadersGeneratorModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-scrollable">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="securityHeadersGeneratorModalLabel">Security Headers Generator</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div class="row">
                    <div class="col-md-12 mb-3">
                        <div class="alert alert-info" role="alert">
                            <i class="fas fa-info-circle me-2"></i> Generate customized security headers to include in your website configuration.
                        </div>
                    </div>

                    <div class="col-md-12">
                        <form id="securityHeadersGeneratorForm" onsubmit="return false;" autocomplete="off">
                            <div class="mb-4">
                                <label class="form-label fw-bold">Web Server Type</label>
                                <div class="btn-group w-100" role="group" aria-label="Web server selection">
                                    <input type="radio" class="btn-check" name="serverType" id="serverNginx" value="nginx" checked autocomplete="off">
                                    <label class="btn btn-outline-primary" for="serverNginx">Nginx</label>

                                    <input type="radio" class="btn-check" name="serverType" id="serverApache" value="apache" autocomplete="off">
                                    <label class="btn btn-outline-primary" for="serverApache">Apache</label>
                                    
                                    <input type="radio" class="btn-check" name="serverType" id="serverIIS" value="iis" autocomplete="off">
                                    <label class="btn btn-outline-primary" for="serverIIS">IIS</label>
                                    
                                    <input type="radio" class="btn-check" name="serverType" id="serverPHP" value="php" autocomplete="off">
                                    <label class="btn btn-outline-primary" for="serverPHP">PHP</label>
                                </div>
                            </div>

                            <div class="row">
                                <!-- Content Security Policy (CSP) -->
                                <div class="col-md-12 mb-3">
                                    <div class="card card-primary">
                                        <div class="card-header d-flex justify-content-between align-items-center">
                                            <span>Content Security Policy</span>
                                            <div class="form-check form-switch">
                                                <input class="form-check-input" type="checkbox" id="enableCSP" checked>
                                                <label class="form-check-label" for="enableCSP">Enable</label>
                                            </div>
                                        </div>
                                        <div class="card-body">
                                            <div class="mb-3">
                                                <label for="cspDefaultSrc" class="form-label">default-src</label>
                                                <input type="text" class="form-control csp-field" id="cspDefaultSrc" value="'self'" placeholder="e.g. 'self' https:">
                                                <small class="text-muted">Use single quotes for 'self', 'none', etc.</small>
                                            </div>
                                            <div class="mb-3">
                                                <label for="cspScriptSrc" class="form-label">script-src</label>
                                                <input type="text" class="form-control csp-field" id="cspScriptSrc" value="'self'" placeholder="e.g. 'self' https:">
                                                <small class="text-muted">Use domain names or keywords: 'self', https://example.com</small>
                                            </div>
                                            <div class="mb-3">
                                                <label for="cspImgSrc" class="form-label">img-src</label>
                                                <input type="text" class="form-control csp-field" id="cspImgSrc" value="'self' data:" placeholder="e.g. 'self' data: https:">
                                                <small class="text-muted">Add data: for inline images, https: for all HTTPS sources</small>
                                            </div>
                                            <div class="mb-3">
                                                <div class="form-check form-switch">
                                                    <input class="form-check-input csp-field" type="checkbox" id="cspReportOnly">
                                                    <label class="form-check-label" for="cspReportOnly">Report-Only Mode</label>
                                                </div>
                                                <small class="text-muted">Policy violations will be reported but not enforced</small>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <!-- HSTS -->
                                <div class="col-md-6 mb-3">
                                    <div class="card card-info">
                                        <div class="card-header d-flex justify-content-between align-items-center">
                                            <span>HTTP Strict Transport Security</span>
                                            <div class="form-check form-switch">
                                                <input class="form-check-input" type="checkbox" id="enableHSTS" checked>
                                                <label class="form-check-label" for="enableHSTS">Enable</label>
                                            </div>
                                        </div>
                                        <div class="card-body">
                                            <div class="mb-3">
                                                <label for="hstsMaxAge" class="form-label">max-age (seconds)</label>
                                                <input type="number" class="form-control hsts-field" id="hstsMaxAge" value="31536000">
                                                <small class="text-muted">31536000 = 1 year</small>
                                            </div>
                                            <div class="mb-3">
                                                <div class="form-check form-switch">
                                                    <input class="form-check-input hsts-field" type="checkbox" id="hstsIncludeSubDomains" checked>
                                                    <label class="form-check-label" for="hstsIncludeSubDomains">Include Sub Domains</label>
                                                </div>
                                            </div>
                                            <div class="mb-3">
                                                <div class="form-check form-switch">
                                                    <input class="form-check-input hsts-field" type="checkbox" id="hstsPreload">
                                                    <label class="form-check-label" for="hstsPreload">Preload</label>
                                                </div>
                                                <small class="text-muted">For inclusion in browser preload lists</small>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <!-- X-Content-Type-Options -->
                                <div class="col-md-6 mb-3">
                                    <div class="card card-secondary">
                                        <div class="card-header d-flex justify-content-between align-items-center">
                                            <span>X-Content-Type-Options</span>
                                            <div class="form-check form-switch">
                                                <input class="form-check-input" type="checkbox" id="enableNoSniff" checked>
                                                <label class="form-check-label" for="enableNoSniff">Enable</label>
                                            </div>
                                        </div>
                                        <div class="card-body">
                                            <p>Prevents browsers from MIME-sniffing a response away from the declared content-type</p>
                                            <div class="form-check form-switch">
                                                <input class="form-check-input" type="checkbox" id="noSniffValue" checked disabled>
                                                <label class="form-check-label" for="noSniffValue">nosniff</label>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <!-- X-Frame-Options -->
                                <div class="col-md-6 mb-3">
                                    <div class="card card-info">
                                        <div class="card-header d-flex justify-content-between align-items-center">
                                            <span>X-Frame-Options</span>
                                            <div class="form-check form-switch">
                                                <input class="form-check-input" type="checkbox" id="enableXFrame" checked>
                                                <label class="form-check-label" for="enableXFrame">Enable</label>
                                            </div>
                                        </div>
                                        <div class="card-body">
                                            <div class="mb-3">
                                                <label for="xFrameOption" class="form-label">Option</label>
                                                <select class="form-select xframe-field" id="xFrameOption">
                                                    <option value="DENY">DENY</option>
                                                    <option value="SAMEORIGIN">SAMEORIGIN</option>
                                                </select>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <!-- Referrer-Policy -->
                                <div class="col-md-6 mb-3">
                                    <div class="card card-secondary">
                                        <div class="card-header d-flex justify-content-between align-items-center">
                                            <span>Referrer-Policy</span>
                                            <div class="form-check form-switch">
                                                <input class="form-check-input" type="checkbox" id="enableReferrerPolicy" checked>
                                                <label class="form-check-label" for="enableReferrerPolicy">Enable</label>
                                            </div>
                                        </div>
                                        <div class="card-body">
                                            <div class="mb-3">
                                                <label for="referrerPolicy" class="form-label">Policy</label>
                                                <select class="form-select referrer-field" id="referrerPolicy">
                                                    <option value="no-referrer">no-referrer</option>
                                                    <option value="no-referrer-when-downgrade">no-referrer-when-downgrade</option>
                                                    <option value="origin">origin</option>
                                                    <option value="origin-when-cross-origin">origin-when-cross-origin</option>
                                                    <option value="same-origin">same-origin</option>
                                                    <option value="strict-origin" selected>strict-origin</option>
                                                    <option value="strict-origin-when-cross-origin">strict-origin-when-cross-origin</option>
                                                    <option value="unsafe-url">unsafe-url</option>
                                                </select>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div class="d-grid gap-2 mt-4">
                                <button type="submit" class="btn btn-primary" id="generateHeadersBtn">
                                    <i class="fas fa-cog me-2"></i>Generate Headers
                                </button>
                            </div>
                        </form>

                        <!-- Results Area -->
                        <div id="generatedHeadersContainer" class="mt-4 d-none">
                            <div class="card">
                                <div class="card-header d-flex justify-content-between align-items-center">
                                    <h5 class="mb-0">Generated Headers</h5>
                                    <button type="button" class="btn btn-sm btn-outline-primary" id="copyGeneratedHeadersBtn">
                                        <i class="fas fa-copy me-1"></i>Copy
                                    </button>
                                </div>
                                <div class="card-body">
                                    <pre class="mb-0 p-3 bg-dark text-light rounded" id="generatedHeadersOutput" style="white-space: pre-wrap; max-height: 400px; overflow-y: auto;"></pre>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-sm btn-outline-info" data-bs-toggle="modal" data-bs-target="#headerHelpModal">
                    <i class="fas fa-book-open me-1"></i>Help Guide
                </button>
                <div class="flex-grow-1"></div>
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>