/**
 * Security Headers Generator functionality
 * Provides security header generation for web servers
 */

/**
 * Initialize Security Headers Generator
 */
function setupSecurityHeadersGenerator() {
  console.log('Setting up Security Headers Generator...');
  
  try {
    // Get DOM elements
    const form = document.getElementById('securityHeadersGeneratorForm');
    const serverTypeInputs = document.querySelectorAll('input[name="serverType"]');
    const generateButton = document.getElementById('generateHeadersBtn');
    const resultContainer = document.getElementById('generatedHeadersContainer');
    const resultOutput = document.getElementById('generatedHeadersOutput');
    const copyButton = document.getElementById('copyGeneratedHeadersBtn');

    console.log('Got form elements:', {
      form: !!form,
      serverTypeInputs: serverTypeInputs?.length,
      generateButton: !!generateButton,
      resultContainer: !!resultContainer,
      resultOutput: !!resultOutput,
      copyButton: !!copyButton
    });

    // Check if form elements exist
    if (!form) {
      console.error('Form not found: #securityHeadersGeneratorForm');
    }
    if (!generateButton) {
      console.error('Generate button not found: #generateHeadersBtn');
    }

    // Add event listener to button 
    if (generateButton) {
      generateButton.addEventListener('click', function(e) {
        e.preventDefault();
        console.log("Generate button clicked");
        generateHeaders();
      });
    }
    
    // Add event listener to form for Enter key
    if (form) {
      form.addEventListener('submit', function(e) {
        e.preventDefault();
        console.log("Generator form submitted");
        generateHeaders();
        return false;
      });
    }
    
    // Also add key handlers for input fields
    const inputFields = form?.querySelectorAll('input[type="text"], input[type="number"]');
    if (inputFields) {
      inputFields.forEach(input => {
        input.addEventListener('keydown', function(e) {
          if (e.key === 'Enter') {
            e.preventDefault();
            generateHeaders();
            return false;
          }
        });
      });
    }
    
    // Add event listeners for server type radio buttons
    if (serverTypeInputs && serverTypeInputs.length > 0) {
      serverTypeInputs.forEach(input => {
        input.addEventListener('change', function() {
          const serverType = this.value;
          console.log('Server type changed to:', serverType);
          
          // Update UI elements if needed based on server type
          if (serverType === 'php') {
            // PHP-specific UI updates if needed
            document.querySelectorAll('.php-specific').forEach(el => el.classList.remove('d-none'));
          } else {
            document.querySelectorAll('.php-specific').forEach(el => el.classList.add('d-none'));
          }
          
          // If there's already generated headers, re-generate them for the new server type
          if (resultContainer && !resultContainer.classList.contains('d-none')) {
            generateHeaders();
          }
        });
      });
    } else {
      console.error('No server type inputs found');
    }

    // Initialize toggle switches
    const toggles = form?.querySelectorAll('.form-check-input[type="checkbox"]');
    if (toggles) {
      console.log(`Found ${toggles.length} toggle switches`);
      toggles.forEach(toggle => {
        // Add event listener for toggles
        toggle.addEventListener('change', function() {
          const id = this.id;
          const isEnabled = this.checked;
          console.log(`Toggle ${id} changed to ${isEnabled}`);
          
          // Handle specific toggles that affect other fields
          if (id === 'enableCSP') {
            const cspFields = document.querySelectorAll('.csp-field');
            cspFields.forEach(field => {
              field.disabled = !isEnabled;
            });
          } else if (id === 'enableHSTS') {
            const hstsFields = document.querySelectorAll('.hsts-field');
            hstsFields.forEach(field => {
              field.disabled = !isEnabled;
            });
          } else if (id === 'enableXFrame') {
            const xFrameFields = document.querySelectorAll('.xframe-field');
            xFrameFields.forEach(field => {
              field.disabled = !isEnabled;
            });
          } else if (id === 'enableReferrerPolicy') {
            const referrerFields = document.querySelectorAll('.referrer-field');
            referrerFields.forEach(field => {
              field.disabled = !isEnabled;
            });
          }
        });
      });
    } else {
      console.error('No toggle switches found');
    }

    // Copy button functionality
    if (copyButton) {
      copyButton.addEventListener('click', function() {
        if (!resultOutput || !resultOutput.textContent.trim()) {
          console.error('No content to copy');
          return;
        }
        
        try {
          navigator.clipboard.writeText(resultOutput.textContent)
            .then(() => {
              // Show success message
              const originalText = copyButton.innerHTML;
              copyButton.innerHTML = '<i class="fas fa-check me-1"></i>Copied';
              copyButton.classList.remove('btn-outline-primary');
              copyButton.classList.add('btn-success');
              
              setTimeout(() => {
                copyButton.innerHTML = originalText;
                copyButton.classList.remove('btn-success');
                copyButton.classList.add('btn-outline-primary');
              }, 2000);
            })
            .catch(err => {
              console.error('Could not copy text: ', err);
              copyButton.innerHTML = '<i class="fas fa-times me-1"></i>Failed';
              copyButton.classList.remove('btn-outline-primary');
              copyButton.classList.add('btn-danger');
              
              setTimeout(() => {
                copyButton.innerHTML = '<i class="fas fa-copy me-1"></i>Copy';
                copyButton.classList.remove('btn-danger');
                copyButton.classList.add('btn-outline-primary');
              }, 2000);
            });
        } catch (err) {
          console.error('Clipboard API not available:', err);
          alert('Cannot access clipboard. Please copy manually.');
        }
      });
    }

    /**
     * Generate security headers based on form inputs
     */
    function generateHeaders() {
      console.log('Generating headers...');
      
      // Create a feedback element if it doesn't exist
      let feedbackElement = document.getElementById('securityHeadersGeneratorFeedback');
      if (!feedbackElement) {
        feedbackElement = document.createElement('div');
        feedbackElement.id = 'securityHeadersGeneratorFeedback';
        feedbackElement.className = 'alert alert-info mt-3 d-none';
        form.appendChild(feedbackElement);
      }
      
      // Hide feedback initially
      feedbackElement.className = 'alert alert-info mt-3 d-none';
      
      // Get selected server type
      let serverType = 'nginx';
      serverTypeInputs.forEach(input => {
        if (input.checked) {
          serverType = input.value;
        }
      });
      
      console.log('Selected server type:', serverType);

      // Get content security policy options
      const enableCSP = document.getElementById('enableCSP')?.checked ?? true;
      const cspDefaultSrc = document.getElementById('cspDefaultSrc')?.value?.trim() || "'self'";
      const cspScriptSrc = document.getElementById('cspScriptSrc')?.value?.trim() || "'self'";
      const cspImgSrc = document.getElementById('cspImgSrc')?.value?.trim() || "'self' data:";
      const cspReportOnly = document.getElementById('cspReportOnly')?.checked ?? false;
      
      // Add debug log to check values
      console.log('CSP Values:', { enableCSP, cspDefaultSrc, cspScriptSrc, cspImgSrc, cspReportOnly });
      
      // Get HSTS options
      const enableHSTS = document.getElementById('enableHSTS')?.checked ?? true;
      const hstsMaxAge = document.getElementById('hstsMaxAge')?.value || '31536000';
      const hstsIncludeSubDomains = document.getElementById('hstsIncludeSubDomains')?.checked ?? true;
      const hstsPreload = document.getElementById('hstsPreload')?.checked ?? false;
      
      // Get X-Content-Type-Options option
      const enableNoSniff = document.getElementById('enableNoSniff')?.checked ?? true;
      
      // Get X-Frame-Options
      const enableXFrame = document.getElementById('enableXFrame')?.checked ?? true;
      const xFrameOption = document.getElementById('xFrameOption')?.value || 'DENY';
      
      // Get Referrer-Policy
      const enableReferrerPolicy = document.getElementById('enableReferrerPolicy')?.checked ?? true;
      const referrerPolicy = document.getElementById('referrerPolicy')?.value || 'strict-origin';
      
      // Generate headers based on server type
      let headerCode = '';
      
      switch (serverType) {
        case 'nginx':
          headerCode = generateNginxHeaders(
            enableCSP, cspDefaultSrc, cspScriptSrc, cspImgSrc, cspReportOnly,
            enableHSTS, hstsMaxAge, hstsIncludeSubDomains, hstsPreload,
            enableNoSniff, enableXFrame, xFrameOption, enableReferrerPolicy, referrerPolicy
          );
          break;
          
        case 'apache':
          headerCode = generateApacheHeaders(
            enableCSP, cspDefaultSrc, cspScriptSrc, cspImgSrc, cspReportOnly,
            enableHSTS, hstsMaxAge, hstsIncludeSubDomains, hstsPreload,
            enableNoSniff, enableXFrame, xFrameOption, enableReferrerPolicy, referrerPolicy
          );
          break;
          
        case 'iis':
          headerCode = generateIISHeaders(
            enableCSP, cspDefaultSrc, cspScriptSrc, cspImgSrc, cspReportOnly,
            enableHSTS, hstsMaxAge, hstsIncludeSubDomains, hstsPreload,
            enableNoSniff, enableXFrame, xFrameOption, enableReferrerPolicy, referrerPolicy
          );
          break;
          
        case 'php':
          headerCode = generatePHPHeaders(
            enableCSP, cspDefaultSrc, cspScriptSrc, cspImgSrc, cspReportOnly,
            enableHSTS, hstsMaxAge, hstsIncludeSubDomains, hstsPreload,
            enableNoSniff, enableXFrame, xFrameOption, enableReferrerPolicy, referrerPolicy
          );
          break;
      }
      
      // Show results
      if (resultContainer) {
        resultContainer.classList.remove('d-none');
        // Scroll to results
        resultContainer.scrollIntoView({ behavior: 'smooth', block: 'start' });
      }
      
      if (resultOutput) {
        resultOutput.textContent = headerCode;
        console.log('Header code generated:', headerCode);
      }
    }

    /**
     * Generate Nginx server headers configuration
     */
    function generateNginxHeaders(
      enableCSP, cspDefaultSrc, cspScriptSrc, cspImgSrc, cspReportOnly,
      enableHSTS, hstsMaxAge, hstsIncludeSubDomains, hstsPreload,
      enableNoSniff, enableXFrame, xFrameOption, enableReferrerPolicy, referrerPolicy
    ) {
      let config = `# Security Headers for Nginx\n# Add these to your server block in nginx.conf or site config\n\n`;
      
      if (enableCSP) {
        const cspHeaderName = cspReportOnly ? 'Content-Security-Policy-Report-Only' : 'Content-Security-Policy';
        
        // Format CSP directives, ensuring non-empty values
        let cspValue = "";
        if (cspDefaultSrc && cspDefaultSrc.trim()) {
          cspValue += `default-src ${cspDefaultSrc.trim()};`;
        } else {
          cspValue += `default-src 'self';`;
        }
        
        if (cspScriptSrc && cspScriptSrc.trim()) {
          cspValue += ` script-src ${cspScriptSrc.trim()};`;
        }
        
        if (cspImgSrc && cspImgSrc.trim()) {
          cspValue += ` img-src ${cspImgSrc.trim()};`;
        }
        
        config += `# Content Security Policy\n`;
        config += `add_header ${cspHeaderName} "${cspValue}" always;\n\n`;
      }
      
      if (enableHSTS) {
        config += `# HTTP Strict Transport Security\n`;
        let hstsValue = `max-age=${hstsMaxAge}`;
        if (hstsIncludeSubDomains) hstsValue += '; includeSubDomains';
        if (hstsPreload) hstsValue += '; preload';
        config += `add_header Strict-Transport-Security "${hstsValue}" always;\n\n`;
      }
      
      if (enableNoSniff) {
        config += `# X-Content-Type-Options\n`;
        config += `add_header X-Content-Type-Options "nosniff" always;\n\n`;
      }
      
      if (enableXFrame) {
        config += `# X-Frame-Options\n`;
        config += `add_header X-Frame-Options "${xFrameOption}" always;\n\n`;
      }
      
      if (enableReferrerPolicy) {
        config += `# Referrer-Policy\n`;
        config += `add_header Referrer-Policy "${referrerPolicy}" always;\n\n`;
      }
      
      return config;
    }

    /**
     * Generate Apache server headers configuration
     */
    function generateApacheHeaders(
      enableCSP, cspDefaultSrc, cspScriptSrc, cspImgSrc, cspReportOnly,
      enableHSTS, hstsMaxAge, hstsIncludeSubDomains, hstsPreload,
      enableNoSniff, enableXFrame, xFrameOption, enableReferrerPolicy, referrerPolicy
    ) {
      let config = `# Security Headers for Apache\n# Add these to your .htaccess file or server configuration\n\n`;
      
      config += `<IfModule mod_headers.c>\n`;
      
      if (enableCSP) {
        const cspHeaderName = cspReportOnly ? 'Content-Security-Policy-Report-Only' : 'Content-Security-Policy';
        
        // Format CSP directives, ensuring non-empty values
        let cspValue = "";
        if (cspDefaultSrc && cspDefaultSrc.trim()) {
          cspValue += `default-src ${cspDefaultSrc.trim()};`;
        } else {
          cspValue += `default-src 'self';`;
        }
        
        if (cspScriptSrc && cspScriptSrc.trim()) {
          cspValue += ` script-src ${cspScriptSrc.trim()};`;
        }
        
        if (cspImgSrc && cspImgSrc.trim()) {
          cspValue += ` img-src ${cspImgSrc.trim()};`;
        }
        
        config += `    # Content Security Policy\n`;
        config += `    Header set ${cspHeaderName} "${cspValue}"\n\n`;
      }
      
      if (enableHSTS) {
        config += `    # HTTP Strict Transport Security\n`;
        let hstsValue = `max-age=${hstsMaxAge}`;
        if (hstsIncludeSubDomains) hstsValue += '; includeSubDomains';
        if (hstsPreload) hstsValue += '; preload';
        config += `    Header set Strict-Transport-Security "${hstsValue}"\n\n`;
      }
      
      if (enableNoSniff) {
        config += `    # X-Content-Type-Options\n`;
        config += `    Header set X-Content-Type-Options "nosniff"\n\n`;
      }
      
      if (enableXFrame) {
        config += `    # X-Frame-Options\n`;
        config += `    Header set X-Frame-Options "${xFrameOption}"\n\n`;
      }
      
      if (enableReferrerPolicy) {
        config += `    # Referrer-Policy\n`;
        config += `    Header set Referrer-Policy "${referrerPolicy}"\n\n`;
      }
      
      config += `</IfModule>`;
      
      return config;
    }

    /**
     * Generate IIS server headers configuration
     */
    function generateIISHeaders(
      enableCSP, cspDefaultSrc, cspScriptSrc, cspImgSrc, cspReportOnly,
      enableHSTS, hstsMaxAge, hstsIncludeSubDomains, hstsPreload,
      enableNoSniff, enableXFrame, xFrameOption, enableReferrerPolicy, referrerPolicy
    ) {
      let config = `<!-- Security Headers for IIS -->\n<!-- Add these to your web.config file -->\n\n`;
      
      config += `<configuration>\n`;
      config += `  <system.webServer>\n`;
      config += `    <httpProtocol>\n`;
      config += `      <customHeaders>\n`;
      
      if (enableCSP) {
        const cspHeaderName = cspReportOnly ? 'Content-Security-Policy-Report-Only' : 'Content-Security-Policy';
        
        // Format CSP directives, ensuring non-empty values
        let cspValue = "";
        if (cspDefaultSrc && cspDefaultSrc.trim()) {
          cspValue += `default-src ${cspDefaultSrc.trim()};`;
        } else {
          cspValue += `default-src 'self';`;
        }
        
        if (cspScriptSrc && cspScriptSrc.trim()) {
          cspValue += ` script-src ${cspScriptSrc.trim()};`;
        }
        
        if (cspImgSrc && cspImgSrc.trim()) {
          cspValue += ` img-src ${cspImgSrc.trim()};`;
        }
        
        config += `        <!-- Content Security Policy -->\n`;
        config += `        <add name="${cspHeaderName}" value="${cspValue}" />\n\n`;
      }
      
      if (enableHSTS) {
        config += `        <!-- HTTP Strict Transport Security -->\n`;
        let hstsValue = `max-age=${hstsMaxAge}`;
        if (hstsIncludeSubDomains) hstsValue += '; includeSubDomains';
        if (hstsPreload) hstsValue += '; preload';
        config += `        <add name="Strict-Transport-Security" value="${hstsValue}" />\n\n`;
      }
      
      if (enableNoSniff) {
        config += `        <!-- X-Content-Type-Options -->\n`;
        config += `        <add name="X-Content-Type-Options" value="nosniff" />\n\n`;
      }
      
      if (enableXFrame) {
        config += `        <!-- X-Frame-Options -->\n`;
        config += `        <add name="X-Frame-Options" value="${xFrameOption}" />\n\n`;
      }
      
      if (enableReferrerPolicy) {
        config += `        <!-- Referrer-Policy -->\n`;
        config += `        <add name="Referrer-Policy" value="${referrerPolicy}" />\n\n`;
      }
      
      config += `      </customHeaders>\n`;
      config += `    </httpProtocol>\n`;
      config += `  </system.webServer>\n`;
      config += `</configuration>`;
      
      return config;
    }

    /**
     * Generate PHP headers
     */
    function generatePHPHeaders(
      enableCSP, cspDefaultSrc, cspScriptSrc, cspImgSrc, cspReportOnly,
      enableHSTS, hstsMaxAge, hstsIncludeSubDomains, hstsPreload,
      enableNoSniff, enableXFrame, xFrameOption, enableReferrerPolicy, referrerPolicy
    ) {
      let config = `<?php\n// Security Headers for PHP\n// Add these to the beginning of your PHP files\n\n`;
      
      if (enableCSP) {
        const cspHeaderName = cspReportOnly ? 'Content-Security-Policy-Report-Only' : 'Content-Security-Policy';
        
        // Format CSP directives, ensuring non-empty values
        let cspValue = "";
        if (cspDefaultSrc && cspDefaultSrc.trim()) {
          cspValue += `default-src ${cspDefaultSrc.trim()};`;
        } else {
          cspValue += `default-src 'self';`;
        }
        
        if (cspScriptSrc && cspScriptSrc.trim()) {
          cspValue += ` script-src ${cspScriptSrc.trim()};`;
        }
        
        if (cspImgSrc && cspImgSrc.trim()) {
          cspValue += ` img-src ${cspImgSrc.trim()};`;
        }
        
        config += `// Content Security Policy\n`;
        config += `header("${cspHeaderName}: ${cspValue}");\n\n`;
      }
      
      if (enableHSTS) {
        config += `// HTTP Strict Transport Security\n`;
        let hstsValue = `max-age=${hstsMaxAge}`;
        if (hstsIncludeSubDomains) hstsValue += '; includeSubDomains';
        if (hstsPreload) hstsValue += '; preload';
        config += `header("Strict-Transport-Security: ${hstsValue}");\n\n`;
      }
      
      if (enableNoSniff) {
        config += `// X-Content-Type-Options\n`;
        config += `header("X-Content-Type-Options: nosniff");\n\n`;
      }
      
      if (enableXFrame) {
        config += `// X-Frame-Options\n`;
        config += `header("X-Frame-Options: ${xFrameOption}");\n\n`;
      }
      
      if (enableReferrerPolicy) {
        config += `// Referrer-Policy\n`;
        config += `header("Referrer-Policy: ${referrerPolicy}");\n\n`;
      }
      
      return config;
    }

  } catch (e) {
    console.error('Error initializing Security Headers Generator:', e);
    
    // Add an error message to the modal
    const modal = document.getElementById('securityHeadersGeneratorModal');
    if (modal) {
      const errorDiv = document.createElement('div');
      errorDiv.className = 'alert alert-danger mt-3';
      errorDiv.innerHTML = '<i class="fas fa-exclamation-triangle me-2"></i>There was an error initializing the Security Headers Generator. Please try refreshing the page.';
      
      // Find the first child of the modal body and insert before it
      const modalBody = modal.querySelector('.modal-body');
      if (modalBody && modalBody.firstChild) {
        modalBody.insertBefore(errorDiv, modalBody.firstChild);
      }
    }
  }
}

// Export the setup function
export default setupSecurityHeadersGenerator;