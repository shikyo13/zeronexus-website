/**
 * Security Headers Checker functionality
 * Provides security header analysis for the Network Admin Tools page
 */

/**
 * Initialize Security Headers Checker
 */
function setupSecurityHeadersChecker() {
  console.log('Setting up Security Headers Checker...');
  
  // Important security headers to check
  const securityHeaders = {
    'content-security-policy': {
      name: 'Content-Security-Policy',
      description: 'Helps prevent Cross-Site Scripting (XSS) and other code injection attacks by controlling resources the browser is allowed to load.',
      importance: 'high'
    },
    'strict-transport-security': {
      name: 'Strict-Transport-Security',
      description: 'Forces browsers to use HTTPS for the specified domain.',
      importance: 'high'
    },
    'x-content-type-options': {
      name: 'X-Content-Type-Options',
      description: 'Prevents browsers from MIME-sniffing a response away from the declared content-type.',
      importance: 'high'
    },
    'x-frame-options': {
      name: 'X-Frame-Options',
      description: 'Prevents clickjacking attacks by stopping your page from being embedded in a frame.',
      importance: 'high'
    },
    'referrer-policy': {
      name: 'Referrer-Policy',
      description: 'Controls how much referrer information should be included with requests.',
      importance: 'medium'
    },
    'permissions-policy': {
      name: 'Permissions-Policy',
      description: 'Controls which browser features and APIs can be used on a website.',
      importance: 'medium'
    },
    'cross-origin-embedder-policy': {
      name: 'Cross-Origin-Embedder-Policy',
      description: 'Controls which cross-origin resources can be loaded.',
      importance: 'medium'
    },
    'cross-origin-opener-policy': {
      name: 'Cross-Origin-Opener-Policy',
      description: 'Controls how a document interacts with other browsing contexts.',
      importance: 'medium'
    },
    'cross-origin-resource-policy': {
      name: 'Cross-Origin-Resource-Policy',
      description: 'Controls which domains can load the response of the request.',
      importance: 'medium'
    },
    'x-xss-protection': {
      name: 'X-XSS-Protection',
      description: 'Enables the cross-site scripting (XSS) filter in your browser.',
      importance: 'low' // Modern browsers rely more on CSP
    }
  };

  /**
   * Get DOM elements
   */
  const form = document.getElementById('securityHeadersForm');
  const urlInput = document.getElementById('securityHeadersUrl');
  const checkButton = document.getElementById('checkHeadersBtn');
  const loadingDiv = document.getElementById('securityHeadersLoading');
  const resultsDiv = document.getElementById('securityHeadersResults');
  const errorDiv = document.getElementById('securityHeadersError');
  const errorText = document.getElementById('securityHeadersErrorText');
  const headerResultDomain = document.getElementById('headerResultDomain');
  const copyBtn = document.getElementById('copyHeadersBtn');
  const securityHeadersList = document.getElementById('securityHeadersList');
  const recommendationsList = document.getElementById('recommendationsList');
  const allHeadersList = document.getElementById('allHeadersList');

  // Add event listener to the button
  if (checkButton) {
    checkButton.addEventListener('click', function(e) {
      // Make sure to prevent default to avoid any form submission
      e.preventDefault();
      console.log("Check button clicked");
      checkSecurityHeaders();
    });
  }
  
  // Correct handling for the form itself
  if (form) {
    form.addEventListener('submit', function(e) {
      e.preventDefault(); // Prevent form submission
      console.log("Form submitted via Enter key");
      checkSecurityHeaders();
      return false; // Extra prevention for older browsers
    });
  }

  // Add copy button functionality
  if (copyBtn) {
    copyBtn.addEventListener('click', function() {
      // Create formatted text for copying
      let copyText = `Security Headers Analysis\n`;
      copyText += `URL: ${urlInput.value}\n`;
      copyText += `Date: ${new Date().toLocaleString()}\n\n`;
      
      // Get security headers from DOM
      const headerItems = securityHeadersList.querySelectorAll('.header-item');
      copyText += `=== SECURITY HEADERS ===\n`;
      headerItems.forEach(item => {
        const headerName = item.querySelector('.header-name');
        const headerValue = item.querySelector('.header-value');
        if (headerName && headerValue) {
          copyText += `${headerName.textContent.split('Status')[0].trim()}: ${headerValue ? headerValue.textContent.trim() : 'Not set'}\n`;
        }
      });
      
      // Copy to clipboard
      navigator.clipboard.writeText(copyText)
        .then(() => {
          // Show success message
          const originalText = copyBtn.innerHTML;
          copyBtn.innerHTML = '<i class="fas fa-check me-1"></i>Copied';
          setTimeout(() => {
            copyBtn.innerHTML = originalText;
          }, 2000);
        })
        .catch(err => {
          console.error('Could not copy text: ', err);
        });
    });
  }

  /**
   * Check security headers for a website
   */
  function checkSecurityHeaders() {
    // Show loading and hide results/error
    if (loadingDiv) loadingDiv.classList.remove('d-none');
    if (resultsDiv) resultsDiv.classList.add('d-none');
    if (errorDiv) errorDiv.classList.add('d-none');
    
    // Get URL and clean it
    let url = urlInput.value.trim();
    
    // Remove any spaces
    url = url.replace(/\s+/g, '');
    
    // Check if it's empty
    if (!url) {
      // Show error for empty URL
      if (errorDiv && errorText) {
        errorDiv.classList.remove('d-none');
        errorText.textContent = 'Please enter a domain name or URL';
      }
      return;
    }
    
    // Handle different URL formats
    if (!url.match(/^https?:\/\//i)) {
      // If no protocol specified, add https://
      url = 'https://' + url;
    }
    
    // Disable form while checking
    if (checkButton) checkButton.disabled = true;
    
    // Validate URL format
    try {
      // Test if it's a valid URL format
      new URL(url);
    } catch (e) {
      // Show error for invalid URL
      if (errorDiv && errorText) {
        errorDiv.classList.remove('d-none');
        errorText.textContent = 'Please enter a valid domain name or URL (e.g., example.com or https://example.com)';
      }
      if (loadingDiv) loadingDiv.classList.add('d-none');
      if (checkButton) checkButton.disabled = false;
      return;
    }
    
    // Call the API
    const apiUrl = `/api/security-headers.php?url=${encodeURIComponent(url)}`;
    console.log('Checking headers for URL:', url);
    console.log('API URL:', apiUrl);
    
    fetch(apiUrl)
      .then(response => {
        console.log('Response status:', response.status);
        console.log('Response headers:', [...response.headers.entries()]);
        
        // Check for HTTP errors
        if (!response.ok) {
          return response.text().then(text => {
            console.error('Error response text:', text);
            try {
              const data = JSON.parse(text);
              throw new Error(data.message || 'Failed to check headers');
            } catch (e) {
              throw new Error('Server returned an invalid response. Please try again.');
            }
          });
        }
        
        return response.text().then(text => {
          console.log('Response text length:', text.length);
          try {
            return JSON.parse(text);
          } catch (e) {
            console.error('Failed to parse JSON response:', e);
            console.error('Response text:', text);
            throw new Error('Failed to parse server response. Please try again.');
          }
        });
      })
      .then(data => {
        // Hide loading
        if (loadingDiv) loadingDiv.classList.add('d-none');
        
        // Display results
        if (resultsDiv) {
          resultsDiv.classList.remove('d-none');
          
          // Display domain - clean it for display
          if (headerResultDomain) {
            // Extract hostname for cleaner display
            try {
              const urlObj = new URL(url);
              headerResultDomain.textContent = urlObj.hostname;
            } catch (e) {
              // Fallback to full URL if parsing fails
              headerResultDomain.textContent = url;
            }
          }
          
          // Display security headers
          displaySecurityHeaders(data.headers);
          
          // Display recommendations
          generateRecommendations(data.headers);
          
          // Display all headers
          displayAllHeaders(data.headers);
        }
      })
      .catch(error => {
        console.error('Error checking headers:', error);
        
        // Hide loading
        if (loadingDiv) loadingDiv.classList.add('d-none');
        
        // Show error with more details
        if (errorDiv && errorText) {
          errorDiv.classList.remove('d-none');
          
          let errorMsg = error.message || 'Failed to check headers. Please try again.';
          
          // Add suggestions for common errors
          if (error.message && error.message.includes('not found')) {
            errorMsg += '\n\nPlease check the domain name and try again.';
          } else if (error.message && error.message.includes('Failed to fetch')) {
            errorMsg += '\n\nThe server might be unavailable or your internet connection might be having issues.';
          } else if (error.message && error.message.includes('CORS')) {
            errorMsg += '\n\nThere seems to be a cross-origin issue. This typically happens with certain domain configurations.';
          } else if (error.message && error.message.includes('timeout')) {
            errorMsg += '\n\nThe request timed out. The server might be slow or unavailable.';
          } else if (error.message && error.message.includes('parse')) {
            errorMsg += '\n\nThe server returned an invalid response format.';
          }
          
          errorText.innerHTML = errorMsg.replace(/\n/g, '<br>');
        }
      })
      .finally(() => {
        // Re-enable form always
        if (checkButton) checkButton.disabled = false;
      });
  }

  /**
   * Display security headers in the UI
   */
  function displaySecurityHeaders(headers) {
    if (!securityHeadersList) return;
    
    // Clear previous results
    securityHeadersList.innerHTML = '';
    
    // Process security headers
    let foundCount = 0;
    for (const [key, meta] of Object.entries(securityHeaders)) {
      const hasHeader = headers && Object.keys(headers).some(h => h.toLowerCase() === key.toLowerCase());
      const value = hasHeader ? headers[key] || headers[key.toLowerCase()] : null;
      
      // Create header item element
      const headerItem = document.createElement('div');
      headerItem.className = hasHeader ? 'header-item present' : 'header-item missing';
      
      // Header name and status
      const headerNameDiv = document.createElement('div');
      headerNameDiv.className = 'header-name';
      headerNameDiv.innerHTML = `${meta.name} <span class="header-status ${hasHeader ? 'present' : 'missing'}">${hasHeader ? 'Present' : 'Missing'}</span>`;
      headerItem.appendChild(headerNameDiv);
      
      // Header value if present
      if (hasHeader && value) {
        const headerValueDiv = document.createElement('div');
        headerValueDiv.className = 'header-value';
        headerValueDiv.textContent = value;
        headerItem.appendChild(headerValueDiv);
        foundCount++;
      }
      
      // Header description
      const headerDescDiv = document.createElement('div');
      headerDescDiv.className = 'header-description';
      headerDescDiv.textContent = meta.description;
      headerItem.appendChild(headerDescDiv);
      
      securityHeadersList.appendChild(headerItem);
    }
    
    // No security headers found message
    if (foundCount === 0) {
      const noHeadersDiv = document.createElement('div');
      noHeadersDiv.className = 'alert alert-warning';
      noHeadersDiv.innerHTML = '<i class="fas fa-exclamation-triangle me-2"></i>No security headers were found for this website. Consider implementing the recommended headers to improve security.';
      securityHeadersList.appendChild(noHeadersDiv);
    }
  }

  /**
   * Generate recommendations based on headers
   */
  function generateRecommendations(headers) {
    if (!recommendationsList) return;
    
    // Clear previous recommendations
    recommendationsList.innerHTML = '';
    
    // Track recommendations
    const recommendations = [];
    
    // Check for Content-Security-Policy
    if (!headers || !Object.keys(headers).some(h => h.toLowerCase() === 'content-security-policy')) {
      recommendations.push({
        title: 'Implement Content Security Policy (CSP)',
        text: 'A Content Security Policy helps prevent XSS attacks by specifying which sources of content browsers should allow.'
      });
    }
    
    // Check for HSTS
    if (!headers || !Object.keys(headers).some(h => h.toLowerCase() === 'strict-transport-security')) {
      recommendations.push({
        title: 'Implement HTTP Strict Transport Security (HSTS)',
        text: 'HSTS forces browsers to use HTTPS, protecting against protocol downgrade attacks and cookie hijacking.'
      });
    }
    
    // Check for X-Content-Type-Options
    if (!headers || !Object.keys(headers).some(h => h.toLowerCase() === 'x-content-type-options')) {
      recommendations.push({
        title: 'Add X-Content-Type-Options header',
        text: 'This header prevents browsers from interpreting files as a different MIME type, preventing certain attacks.'
      });
    }
    
    // Check for X-Frame-Options
    if (!headers || !Object.keys(headers).some(h => h.toLowerCase() === 'x-frame-options')) {
      recommendations.push({
        title: 'Add X-Frame-Options header',
        text: 'This header prevents your site from being embedded in frames on other sites, protecting against clickjacking attacks.'
      });
    }
    
    // Check for Referrer-Policy
    if (!headers || !Object.keys(headers).some(h => h.toLowerCase() === 'referrer-policy')) {
      recommendations.push({
        title: 'Add Referrer-Policy header',
        text: 'A Referrer Policy controls how much information is included in referrer headers when making requests.'
      });
    }
    
    // Check for Permissions-Policy
    if (!headers || !Object.keys(headers).some(h => h.toLowerCase() === 'permissions-policy')) {
      recommendations.push({
        title: 'Consider adding Permissions-Policy header',
        text: 'This header allows you to control which browser features and APIs can be used on your website.'
      });
    }
    
    // Display recommendations
    if (recommendations.length > 0) {
      recommendations.forEach(rec => {
        const recItem = document.createElement('div');
        recItem.className = 'recommendation-item';
        
        const recTitle = document.createElement('div');
        recTitle.className = 'recommendation-title';
        recTitle.textContent = rec.title;
        recItem.appendChild(recTitle);
        
        const recText = document.createElement('div');
        recText.className = 'recommendation-text';
        recText.textContent = rec.text;
        recItem.appendChild(recText);
        
        recommendationsList.appendChild(recItem);
      });
    } else {
      // No recommendations needed
      const noRecsDiv = document.createElement('div');
      noRecsDiv.className = 'alert alert-success';
      noRecsDiv.innerHTML = '<i class="fas fa-check-circle me-2"></i>Great job! Your website has all the essential security headers.';
      recommendationsList.appendChild(noRecsDiv);
    }
  }

  /**
   * Display all HTTP headers
   */
  function displayAllHeaders(headers) {
    if (!allHeadersList) return;
    
    // Clear previous headers
    allHeadersList.innerHTML = '';
    
    if (headers && Object.keys(headers).length > 0) {
      // Sort headers alphabetically
      const sortedHeaders = Object.keys(headers).sort();
      let foundCount = 0;
      
      sortedHeaders.forEach(header => {
        foundCount++;
        const value = headers[header];
        
        // Create header item element in same style as security headers
        const headerItem = document.createElement('div');
        
        // Check if this is a security-related header
        const isSecurityHeader = Object.keys(securityHeaders).some(
          secHeader => header.toLowerCase() === secHeader.toLowerCase()
        );
        
        // Apply appropriate styling class
        headerItem.className = isSecurityHeader ? 'header-item present security-related' : 'header-item present';
        
        // Header name
        const headerNameDiv = document.createElement('div');
        headerNameDiv.className = 'header-name';
        
        // Format the header name with proper capitalization
        const formattedName = header.split('-')
          .map(word => word.charAt(0).toUpperCase() + word.slice(1))
          .join('-');
          
        // Add name and status badge - show special badge for security headers
        const badgeClass = isSecurityHeader ? 'header-status security' : 'header-status present';
        const badgeText = isSecurityHeader ? 'Security' : 'Present';
        headerNameDiv.innerHTML = `${formattedName} <span class="${badgeClass}">${badgeText}</span>`;
        headerItem.appendChild(headerNameDiv);
        
        // Header value
        const headerValueDiv = document.createElement('div');
        headerValueDiv.className = 'header-value';
        headerValueDiv.textContent = value;
        headerItem.appendChild(headerValueDiv);
        
        // Add each header to the list
        allHeadersList.appendChild(headerItem);
      });
      
      // No headers found message
      if (foundCount === 0) {
        const noHeadersDiv = document.createElement('div');
        noHeadersDiv.className = 'alert alert-warning';
        noHeadersDiv.innerHTML = '<i class="fas fa-exclamation-triangle me-2"></i>No HTTP headers were found for this website.';
        allHeadersList.appendChild(noHeadersDiv);
      }
    } else {
      // No headers found
      const noHeadersDiv = document.createElement('div');
      noHeadersDiv.className = 'alert alert-warning';
      noHeadersDiv.innerHTML = '<i class="fas fa-exclamation-triangle me-2"></i>No HTTP headers were found for this website.';
      allHeadersList.appendChild(noHeadersDiv);
    }
  }
}

// Export the setup function
export default setupSecurityHeadersChecker;