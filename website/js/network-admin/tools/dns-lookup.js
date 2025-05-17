/**
 * DNS Lookup tool functionality
 * Provides DNS record lookup features for the Network Admin Tools page
 */

/**
 * Initialize DNS lookup functionality
 */
function setupDnsLookup() {
  // Get DOM elements
  const domainInput = document.getElementById('domainName');
  const recordTypeSelect = document.getElementById('recordType');
  const lookupBtn = document.getElementById('lookupDnsBtn');
  const clearBtn = document.getElementById('clearDnsBtn');
  const resultsDiv = document.getElementById('dnsResults');
  const errorDiv = document.getElementById('dnsError');
  const errorText = document.getElementById('dnsErrorText');
  const loadingDiv = document.getElementById('dnsLoading');
  const copyBtn = document.getElementById('copyDnsResultsBtn');
  const toggleVisBtn = document.getElementById('toggleVisualizationBtn');
  const ptrHelp = document.getElementById('ptrHelp');
  const recordsDiv = document.getElementById('dnsRecords');
  const visualizationDiv = document.getElementById('dnsVisualization');
  const dnsRecordsWrap = document.getElementById('dnsRecordsWrap');

  // Add event listeners
  if (lookupBtn) {
    lookupBtn.addEventListener('click', lookupDns);
  }
  
  if (clearBtn) {
    clearBtn.addEventListener('click', clearDnsForm);
  }
  
  if (copyBtn) {
    copyBtn.addEventListener('click', copyDnsResults);
  }
  
  if (toggleVisBtn) {
    toggleVisBtn.addEventListener('click', toggleVisualization);
  }
  
  if (recordTypeSelect) {
    recordTypeSelect.addEventListener('change', handleRecordTypeChange);
  }
  
  /**
   * Handle record type change to show help for PTR lookups
   */
  function handleRecordTypeChange() {
    if (ptrHelp) {
      if (recordTypeSelect.value === 'PTR') {
        ptrHelp.classList.remove('d-none');
      } else {
        ptrHelp.classList.add('d-none');
      }
    }
  }
  
  /**
   * Perform DNS lookup
   */
  function lookupDns() {
    // Hide any previous results or errors
    if (resultsDiv) resultsDiv.classList.add('d-none');
    if (errorDiv) errorDiv.classList.add('d-none');
    if (loadingDiv) loadingDiv.classList.remove('d-none');
    
    // Get input values
    const domain = domainInput ? domainInput.value.trim() : '';
    const recordType = recordTypeSelect ? recordTypeSelect.value : 'A';
    
    // Validate inputs
    if (!domain) {
      showError('Please enter a domain name or IP address');
      if (loadingDiv) loadingDiv.classList.add('d-none');
      return;
    }
    
    // Simple validation for PTR lookups
    if (recordType === 'PTR' && !domain.match(/^(?:[0-9]{1,3}\.){3}[0-9]{1,3}$/)) {
      showError('For PTR lookups, please enter a valid IP address (not a domain name)');
      if (loadingDiv) loadingDiv.classList.add('d-none');
      return;
    }
    
    // Call the API endpoint
    fetch(`/api/dns-lookup.php?domain=${encodeURIComponent(domain)}&type=${encodeURIComponent(recordType)}`)
      .then(response => {
        if (!response.ok) {
          throw new Error(`HTTP error ${response.status}`);
        }
        return response.json();
      })
      .then(data => {
        // Hide loading indicator
        if (loadingDiv) loadingDiv.classList.add('d-none');
        
        if (data.error) {
          showError(data.error);
          return;
        }
        
        // Display results
        if (resultsDiv) {
          resultsDiv.classList.remove('d-none');
          document.getElementById('resultDomain').textContent = domain;
          
          // Clear previous results
          if (recordsDiv) recordsDiv.innerHTML = '';
          if (visualizationDiv) visualizationDiv.innerHTML = '';
          
          // Hide visualization by default
          if (visualizationDiv) visualizationDiv.classList.add('d-none');
          if (dnsRecordsWrap) dnsRecordsWrap.classList.remove('d-none');
          
          // Update visualization toggle button
          if (toggleVisBtn) {
            toggleVisBtn.querySelector('span').textContent = 'Show Visualization';
          }
          
          // Process and display records - Add console logging for debugging
          console.log('DNS records returned:', data.records);
          
          if (data.records && data.records.length > 0) {
            // For debugging, log each record's type
            data.records.forEach((record, index) => {
              console.log(`Record ${index}:`, record.type, record);
              // Additional logging for A records specifically
              if ((record.type || '').toUpperCase() === 'A') {
                console.log('Found A record:', record);
                console.log('A record fields:', {
                  type: record.type,
                  name: record.name || record.host,
                  address: record.address || record.ip || record.data || record.value
                });
              }
            });
            
            displayRecords(data.records, recordType);
            createVisualization(data.records, domain, recordType);
          } else {
            if (recordsDiv) {
              recordsDiv.innerHTML = '<div class="alert alert-warning">No DNS records found for this domain and record type.</div>';
            }
          }
        }
      })
      .catch(error => {
        console.error('DNS lookup error:', error);
        if (loadingDiv) loadingDiv.classList.add('d-none');
        showError(`Failed to lookup DNS records: ${error.message}`);
      });
  }
  
  /**
   * Display DNS records in the UI using styled cards
   */
  function displayRecords(records, recordType) {
    if (!recordsDiv) return;
    
    // Clear previous content
    recordsDiv.innerHTML = '';
    
    // Group records by type in case of ANY queries
    const recordsByType = {};
    
    // Debug the record types and structure
    console.log('Record type requested:', recordType);
    
    records.forEach(record => {
      // Ensure consistent case for record types and handle PHP dns_get_record() output format
      const type = (record.type || recordType).toUpperCase();
      if (!recordsByType[type]) {
        recordsByType[type] = [];
      }
      recordsByType[type].push(record);
    });
    
    // Create section for each record type
    Object.keys(recordsByType).forEach(type => {
      const typeRecords = recordsByType[type];
      
      // Add a header for this record type if we have multiple types
      if (Object.keys(recordsByType).length > 1 || recordType === 'ANY') {
        const typeHeader = document.createElement('h6');
        typeHeader.className = 'mt-3 mb-2';
        typeHeader.textContent = `${type} Records (${typeRecords.length})`;
        recordsDiv.appendChild(typeHeader);
      }
      
      // Create a container for this record type
      const recordsContainer = document.createElement('div');
      recordsContainer.className = 'dns-records-container';
      
      // Create styled cards for each record
      typeRecords.forEach(record => {
        // Create card container
        const card = document.createElement('div');
        card.className = 'dns-record-card';
        
        // Create card header with type and TTL
        const cardHeader = document.createElement('div');
        cardHeader.className = 'dns-record-card-header';
        
        const recordType = document.createElement('div');
        recordType.className = 'dns-record-card-type';
        recordType.textContent = record.type || type;
        
        const recordTTL = document.createElement('div');
        recordTTL.className = 'dns-record-card-ttl';
        recordTTL.textContent = `TTL: ${record.ttl || 'N/A'}`;
        
        cardHeader.appendChild(recordType);
        cardHeader.appendChild(recordTTL);
        card.appendChild(cardHeader);
        
        // Create card content area with record details
        const cardContent = document.createElement('div');
        cardContent.className = 'dns-record-card-content';
        
        // Add hostname/name row
        const nameRow = createCardRow('Name', record.name || record.host || '');
        cardContent.appendChild(nameRow);
        
        // Add value/address/target row based on record type
        let recordValue = '';
        const valueLabel = getValueLabelForType(type);
        
        if (type === 'TXT') {
          recordValue = record.txt || record.entries?.join(' ') || record.text || record.value || '';
        } else if (type === 'SOA') {
          // Formatted SOA data
          const soaData = [];
          if (record.mname) soaData.push(`Primary NS: ${record.mname}`);
          if (record.rname) soaData.push(`Admin: ${record.rname}`);
          if (record.serial) soaData.push(`Serial: ${record.serial}`);
          if (record.refresh) soaData.push(`Refresh: ${record.refresh}`);
          if (record.retry) soaData.push(`Retry: ${record.retry}`);
          if (record.expire) soaData.push(`Expire: ${record.expire}`);
          if (record.minimum) soaData.push(`Minimum TTL: ${record.minimum}`);
          recordValue = soaData.join(', ');
        } else if (type === 'A' || type === 'AAAA') {
          recordValue = record.address || record.ip || record.value || record.data || '';
        } else {
          recordValue = record.address || record.target || record.value || record.data || record.txt || '';
        }
        
        const valueRow = createCardRow(valueLabel, recordValue);
        cardContent.appendChild(valueRow);
        
        // Add priority for MX records
        if ((type === 'MX' || type === 'SRV') && (record.priority || record.preference)) {
          const priorityRow = createCardRow('Priority', record.priority || record.preference);
          cardContent.appendChild(priorityRow);
        }
        
        // Add weight for SRV records
        if (type === 'SRV' && record.weight) {
          const weightRow = createCardRow('Weight', record.weight);
          cardContent.appendChild(weightRow);
        }
        
        // Add port for SRV records
        if (type === 'SRV' && record.port) {
          const portRow = createCardRow('Port', record.port);
          cardContent.appendChild(portRow);
        }
        
        // Add class
        const classRow = createCardRow('Class', record.class || 'IN');
        cardContent.appendChild(classRow);
        
        card.appendChild(cardContent);
        recordsContainer.appendChild(card);
      });
      
      recordsDiv.appendChild(recordsContainer);
    });
    
    // If no records were found, display a message
    if (Object.keys(recordsByType).length === 0) {
      const noRecords = document.createElement('div');
      noRecords.className = 'alert alert-info';
      noRecords.innerHTML = `<i class="fas fa-info-circle me-2"></i> No ${recordType} records found for domain.`;
      recordsDiv.appendChild(noRecords);
    }
  }
  
  /**
   * Helper function to create a row for the record card
   */
  function createCardRow(label, value) {
    const row = document.createElement('div');
    row.className = 'dns-record-card-row';
    
    const labelElement = document.createElement('div');
    labelElement.className = 'dns-record-card-label';
    labelElement.textContent = label;
    
    const valueElement = document.createElement('div');
    valueElement.className = 'dns-record-card-value';
    valueElement.textContent = value;
    
    row.appendChild(labelElement);
    row.appendChild(valueElement);
    
    return row;
  }
  
  /**
   * Get the appropriate value label based on record type
   */
  function getValueLabelForType(type) {
    switch (type) {
      case 'A':
      case 'AAAA':
        return 'IP Address';
      case 'MX':
        return 'Mail Server';
      case 'CNAME':
        return 'Canonical Name';
      case 'NS':
        return 'Name Server';
      case 'TXT':
        return 'Text Value';
      case 'SOA':
        return 'Data';
      case 'PTR':
        return 'Hostname';
      case 'SRV':
        return 'Target';
      default:
        return 'Value';
    }
  }
  
  /**
   * Get appropriate columns based on DNS record type
   */
  function getColumnsForRecordType(recordType) {
    // Normalize record type to uppercase for consistent comparison
    const normalizedType = recordType.toUpperCase();
    switch (normalizedType) {
      case 'A':
      case 'AAAA':
        return ['Hostname', 'TTL', 'Class', 'Type', 'Address'];
      case 'MX':
        return ['Hostname', 'TTL', 'Class', 'Type', 'Priority', 'Target'];
      case 'NS':
      case 'CNAME':
        return ['Hostname', 'TTL', 'Class', 'Type', 'Target'];
      case 'TXT':
        return ['Hostname', 'TTL', 'Class', 'Type', 'Value'];
      case 'SOA':
        return ['Hostname', 'TTL', 'Class', 'Type', 'Data'];
      case 'PTR':
        return ['IP', 'TTL', 'Class', 'Type', 'Hostname'];
      case 'CAA':
        return ['Hostname', 'TTL', 'Class', 'Type', 'Flags', 'Tag', 'Value'];
      case 'SRV':
        return ['Hostname', 'TTL', 'Class', 'Type', 'Priority', 'Weight', 'Port', 'Target'];
      default:
        return ['Hostname', 'TTL', 'Class', 'Type', 'Value'];
    }
  }
  
  /**
   * Create DNS records visualization
   */
  function createVisualization(records, domain, recordType) {
    if (!visualizationDiv) return;
    
    // Clear previous content
    visualizationDiv.innerHTML = '';
    
    // Simplify domain name for display (remove trailing dot)
    const simpleDomain = domain.replace(/\.$/, '');
    
    // Create visualization container
    const vis = document.createElement('div');
    vis.className = 'dns-vis-container';
    
    // Create domain node
    const domainNode = document.createElement('div');
    domainNode.className = 'dns-node domain-node';
    domainNode.innerHTML = `
      <div class="node-icon"><i class="fas fa-globe"></i></div>
      <div class="node-label">${simpleDomain}</div>
    `;
    vis.appendChild(domainNode);
    
    // Create records container
    const recordsContainer = document.createElement('div');
    recordsContainer.className = 'dns-records-container';
    
    // Group records by type
    const recordsByType = {};
    records.forEach(record => {
      // Ensure consistent case for record types
      const type = (record.type || recordType).toUpperCase();
      if (!recordsByType[type]) {
        recordsByType[type] = [];
      }
      recordsByType[type].push(record);
    });
    
    // Create nodes for each record type
    Object.keys(recordsByType).forEach(type => {
      const typeNode = document.createElement('div');
      typeNode.className = 'dns-node-group';
      
      const typeLabel = document.createElement('div');
      typeLabel.className = 'node-type-label';
      typeLabel.textContent = `${type} Records (${recordsByType[type].length})`;
      typeNode.appendChild(typeLabel);
      
      // Create nodes for each record
      recordsByType[type].forEach(record => {
        const recordNode = document.createElement('div');
        recordNode.className = 'dns-node record-node';
        
        // Set icon based on record type
        let icon = 'fa-server';
        // Handle different fields for different record types, especially A/AAAA records
        let valueField = '';
        if (type === 'A' || type === 'AAAA') {
          valueField = record.address || record.ip || record.value || record.data || '';
        } else {
          valueField = record.address || record.target || record.value || record.data || '';
        }
        
        switch (type) {
          case 'A':
            icon = 'fa-server';
            break;
          case 'AAAA':
            icon = 'fa-server';
            break;
          case 'MX':
            icon = 'fa-envelope';
            break;
          case 'NS':
            icon = 'fa-database';
            break;
          case 'CNAME':
            icon = 'fa-exchange-alt';
            break;
          case 'TXT':
            icon = 'fa-file-alt';
            valueField = valueField.substr(0, 30) + (valueField.length > 30 ? '...' : '');
            break;
          case 'PTR':
            icon = 'fa-exchange-alt';
            break;
          case 'SRV':
            icon = 'fa-network-wired';
            break;
          default:
            icon = 'fa-server';
        }
        
        // Add priority for MX records
        const priorityStr = record.priority || record.preference ? 
          `<div class="record-priority">Priority: ${record.priority || record.preference}</div>` : '';
        
        // Get hostname/name field
        const nameField = record.name || record.host || '';
        
        recordNode.innerHTML = `
          <div class="node-icon"><i class="fas ${icon}"></i></div>
          <div class="node-content">
            <div class="node-name">${nameField}</div>
            ${priorityStr}
            <div class="node-value">${valueField}</div>
            <div class="node-ttl">TTL: ${record.ttl || 'N/A'}</div>
          </div>
        `;
        
        typeNode.appendChild(recordNode);
      });
      
      recordsContainer.appendChild(typeNode);
    });
    
    vis.appendChild(recordsContainer);
    visualizationDiv.appendChild(vis);
  }
  
  /**
   * Toggle visibility of the DNS records visualization
   */
  function toggleVisualization() {
    if (!visualizationDiv || !dnsRecordsWrap) return;
    
    const isVisVisible = !visualizationDiv.classList.contains('d-none');
    
    if (isVisVisible) {
      // Switch to records view
      visualizationDiv.classList.add('d-none');
      dnsRecordsWrap.classList.remove('d-none');
      toggleVisBtn.querySelector('span').textContent = 'Show Visualization';
    } else {
      // Switch to visualization view
      visualizationDiv.classList.remove('d-none');
      dnsRecordsWrap.classList.add('d-none');
      toggleVisBtn.querySelector('span').textContent = 'Show Records';
    }
  }
  
  /**
   * Clear the DNS lookup form
   */
  function clearDnsForm() {
    if (domainInput) domainInput.value = '';
    if (recordTypeSelect) recordTypeSelect.value = 'A';
    if (resultsDiv) resultsDiv.classList.add('d-none');
    if (errorDiv) errorDiv.classList.add('d-none');
    if (ptrHelp) ptrHelp.classList.add('d-none');
    
    // Focus on the domain input
    if (domainInput) domainInput.focus();
  }
  
  /**
   * Copy DNS results to clipboard
   */
  function copyDnsResults() {
    if (!recordsDiv) return;
    
    // Get domain and record type
    const domain = document.getElementById('resultDomain').textContent;
    const recordType = recordTypeSelect ? recordTypeSelect.value : 'A';
    
    // Extract card data
    const cards = recordsDiv.querySelectorAll('.dns-record-card');
    if (cards.length === 0) {
      alert('No results to copy');
      return;
    }
    
    let resultText = `DNS Lookup Results for ${domain} (${recordType} Records):\n\n`;
    
    // Process each card to extract data
    cards.forEach((card, index) => {
      const type = card.querySelector('.dns-record-card-type').textContent;
      const ttl = card.querySelector('.dns-record-card-ttl').textContent;
      
      resultText += `Record ${index + 1} (${type}):\n`;
      resultText += `${ttl}\n`;
      
      // Get all rows
      card.querySelectorAll('.dns-record-card-row').forEach(row => {
        const label = row.querySelector('.dns-record-card-label').textContent;
        const value = row.querySelector('.dns-record-card-value').textContent;
        resultText += `${label}: ${value}\n`;
      });
      
      resultText += '\n';
    });
    
    // Copy to clipboard
    navigator.clipboard.writeText(resultText)
      .then(() => {
        // Show copied notification
        const originalText = copyBtn.innerHTML;
        copyBtn.innerHTML = '<i class="fas fa-check me-2"></i>Copied!';
        
        setTimeout(() => {
          copyBtn.innerHTML = originalText;
        }, 2000);
      })
      .catch(err => {
        console.error('Could not copy text: ', err);
        alert('Failed to copy results to clipboard.');
      });
  }
  
  /**
   * Display an error message
   */
  function showError(message) {
    if (errorDiv && errorText) {
      errorText.textContent = message;
      errorDiv.classList.remove('d-none');
    }
  }
  
  // Check URL parameters for direct linking
  function checkUrlParams() {
    const hash = window.location.hash;
    if (hash && hash.startsWith('#dns-lookup')) {
      // Extract parameters from the hash
      const paramsStr = hash.split('?')[1] || '';
      const searchParams = new URLSearchParams(paramsStr);
      
      // Set input values if provided
      const domain = searchParams.get('domain');
      const type = searchParams.get('type');
      
      if (domain && domainInput) {
        domainInput.value = domain;
      }
      
      if (type && recordTypeSelect) {
        recordTypeSelect.value = type.toUpperCase();
        handleRecordTypeChange();
      }
      
      // Auto-lookup if autorun parameter is true
      if (searchParams.get('autorun') === 'true' && lookupBtn) {
        setTimeout(() => {
          lookupBtn.click();
        }, 300);
      }
    }
  }
  
  // Check URL parameters when the page loads
  checkUrlParams();
}

// Export the setup function
export default setupDnsLookup;