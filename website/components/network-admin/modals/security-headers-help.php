<!-- Security Headers Help Modal -->
<div class="modal fade" id="headerHelpModal" tabindex="-1" aria-labelledby="headerHelpModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="headerHelpModalLabel">Security Headers Help</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <h5>About Security Headers</h5>
                <p>Security headers are HTTP response headers that your web server can use to increase the security of your website. They help protect against common web vulnerabilities by instructing browsers to behave in certain ways.</p>
                
                <div class="accordion" id="securityHeadersAccordion">
                    <div class="accordion-item">
                        <h2 class="accordion-header" id="headingCSP">
                            <button class="accordion-button" type="button" data-bs-toggle="collapse" data-bs-target="#collapseCSP" aria-expanded="true" aria-controls="collapseCSP">
                                Content Security Policy (CSP)
                            </button>
                        </h2>
                        <div id="collapseCSP" class="accordion-collapse collapse show" aria-labelledby="headingCSP" data-bs-parent="#securityHeadersAccordion">
                            <div class="accordion-body">
                                <p>CSP helps prevent cross-site scripting (XSS) and data injection attacks by specifying which domains the browser should consider as valid sources for executable scripts, stylesheets, images, etc.</p>
                                <p><strong>default-src:</strong> The fallback directive for all content types. Example: <code>'self'</code> (only allow resources from same origin)</p>
                                <p><strong>script-src:</strong> Controls from where scripts can be loaded. Example: <code>'self' https://trusted-cdn.com</code></p>
                                <p><strong>img-src:</strong> Controls which sites can serve images. Example: <code>'self' data: https:</code> (allows images from same origin, data URLs, and any HTTPS site)</p>
                                <p><strong>Report-Only Mode:</strong> Monitors policy violations without blocking resources.</p>
                            </div>
                        </div>
                    </div>
                    
                    <div class="accordion-item">
                        <h2 class="accordion-header" id="headingHSTS">
                            <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#collapseHSTS" aria-expanded="false" aria-controls="collapseHSTS">
                                HTTP Strict Transport Security (HSTS)
                            </button>
                        </h2>
                        <div id="collapseHSTS" class="accordion-collapse collapse" aria-labelledby="headingHSTS" data-bs-parent="#securityHeadersAccordion">
                            <div class="accordion-body">
                                <p>HSTS forces browsers to use HTTPS instead of HTTP for your site, helping prevent protocol downgrade attacks and cookie hijacking.</p>
                                <p><strong>max-age:</strong> How long the browser should remember to use HTTPS (in seconds). 31536000 = 1 year.</p>
                                <p><strong>includeSubDomains:</strong> Applies the policy to all subdomains.</p>
                                <p><strong>preload:</strong> Indicates intention to submit site to browser HSTS preload list (applied even before first visit).</p>
                            </div>
                        </div>
                    </div>
                    
                    <div class="accordion-item">
                        <h2 class="accordion-header" id="headingOther">
                            <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#collapseOther" aria-expanded="false" aria-controls="collapseOther">
                                Other Security Headers
                            </button>
                        </h2>
                        <div id="collapseOther" class="accordion-collapse collapse" aria-labelledby="headingOther" data-bs-parent="#securityHeadersAccordion">
                            <div class="accordion-body">
                                <p><strong>X-Content-Type-Options:</strong> Prevents browsers from MIME-sniffing a response away from the declared content-type. Setting <code>nosniff</code> helps reduce the danger of drive-by downloads.</p>
                                <p><strong>X-Frame-Options:</strong> Controls whether a browser should be allowed to render a page in a frame or iframe. Options:</p>
                                <ul>
                                    <li><code>DENY</code>: Page cannot be displayed in a frame</li>
                                    <li><code>SAMEORIGIN</code>: Page can only be displayed in a frame on the same origin as the page itself</li>
                                </ul>
                                <p><strong>Referrer-Policy:</strong> Controls how much referrer information should be included with requests. Common options:</p>
                                <ul>
                                    <li><code>no-referrer</code>: No referrer information is sent</li>
                                    <li><code>strict-origin</code>: Only send origin when the protocol security level stays the same (HTTPS→HTTPS)</li>
                                </ul>
                            </div>
                        </div>
                    </div>
                </div>
                
                <h5 class="mt-4">How to Use This Tool</h5>
                <ol>
                    <li>Select your web server type (Nginx, Apache, IIS, or PHP)</li>
                    <li>Configure the security headers you want to include</li>
                    <li>Toggle the switches to enable/disable specific headers</li>
                    <li>Click "Generate Headers" to create configuration code</li>
                    <li>Copy the generated code and add it to your server configuration</li>
                </ol>
                
                <div class="alert alert-warning">
                    <i class="fas fa-exclamation-triangle me-2"></i>
                    <strong>Important:</strong> Test your security headers thoroughly before deploying to production. Overly strict settings may break website functionality.
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                <a href="https://developer.mozilla.org/en-US/docs/Web/HTTP/Headers#security" target="_blank" rel="noopener noreferrer" class="btn btn-primary">
                    <i class="fas fa-external-link-alt me-1"></i>Learn More
                </a>
            </div>
        </div>
    </div>
</div>