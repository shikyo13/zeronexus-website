/**
 * Firewall Rule Generator Tool
 * Generates firewall rules for multiple platforms with visual rule builder
 */
class FirewallRuleGenerator {
    constructor() {
        this.modal = null;
        this.helpModal = null;
        this.currentPlatform = '';
        this.ruleData = this.getDefaultRuleData();
        this.templates = this.getTemplates();
        this.init();
    }

    init() {
        // Get modal elements
        this.modal = document.getElementById('firewallRuleGeneratorModal');
        this.helpModal = document.getElementById('firewallHelpModal');
        
        if (!this.modal) return;

        // Setup event listeners
        this.setupEventListeners();
        
        // Initialize modal events
        this.modal.addEventListener('shown.bs.modal', () => {
            document.getElementById('platformSelect').focus();
        });

        this.modal.addEventListener('hidden.bs.modal', () => {
            this.resetForm();
        });
    }

    setupEventListeners() {
        // Platform selector
        document.getElementById('platformSelect').addEventListener('change', (e) => {
            this.handlePlatformChange(e.target.value);
        });

        // Source/Destination type changes
        document.getElementById('sourceType').addEventListener('change', (e) => {
            this.handleTypeChange('source', e.target.value);
        });

        document.getElementById('destType').addEventListener('change', (e) => {
            this.handleTypeChange('dest', e.target.value);
        });

        // Form inputs
        const inputs = ['ruleName', 'ruleAction', 'ruleProtocol', 'sourceValue', 'sourcePort', 
                       'destValue', 'destPort', 'ruleInterface', 'ruleDirection', 'ruleComment'];
        inputs.forEach(id => {
            const element = document.getElementById(id);
            if (element) {
                element.addEventListener('input', () => this.updateRuleData());
            }
        });

        // Checkboxes
        document.getElementById('enableLogging').addEventListener('change', () => this.updateRuleData());

        // Buttons
        document.getElementById('generateRuleBtn').addEventListener('click', () => this.generateRule());
        document.getElementById('clearRuleBtn').addEventListener('click', () => this.clearRule());
        document.getElementById('copyRuleBtn').addEventListener('click', () => this.copyRule());
        document.getElementById('newRuleBtn').addEventListener('click', () => this.newRule());
        document.getElementById('helpBtn').addEventListener('click', () => this.showHelp());

        // Port dropdown handlers
        const portDropdownItems = document.querySelectorAll('#firewallRuleGeneratorModal .dropdown-item[data-port]');
        portDropdownItems.forEach(item => {
            item.addEventListener('click', (e) => {
                e.preventDefault();
                const port = e.target.getAttribute('data-port');
                document.getElementById('destPort').value = port;
                this.updateRuleData();
            });
        });

        // Keyboard shortcuts
        document.addEventListener('keydown', (e) => {
            if (this.modal && this.modal.classList.contains('show')) {
                if (e.ctrlKey && e.key === 'g') {
                    e.preventDefault();
                    this.generateRule();
                } else if (e.key === 'Escape') {
                    this.clearRule();
                }
            }
        });
    }

    handlePlatformChange(platform) {
        this.currentPlatform = platform;
        
        if (platform) {
            // Show relevant sections
            document.getElementById('templateSection').style.display = 'block';
            document.getElementById('ruleBuilderSection').style.display = 'block';
            
            // Load templates for this platform
            this.loadTemplates(platform);
            
            // Update UI based on platform capabilities
            this.updatePlatformUI(platform);
        } else {
            // Hide sections
            document.getElementById('templateSection').style.display = 'none';
            document.getElementById('ruleBuilderSection').style.display = 'none';
            document.getElementById('generatedOutput').style.display = 'none';
        }
    }

    handleTypeChange(field, type) {
        const valueInput = document.getElementById(field + 'Value');
        
        if (type === 'any') {
            valueInput.value = '';
            valueInput.disabled = true;
            valueInput.placeholder = 'Not required for "Any"';
        } else {
            valueInput.disabled = false;
            if (type === 'ip') {
                valueInput.placeholder = 'e.g., 192.168.1.100';
            } else if (type === 'network') {
                valueInput.placeholder = 'e.g., 192.168.1.0/24';
            } else if (type === 'zone') {
                valueInput.placeholder = 'e.g., inside, dmz, wan';
            }
        }
        
        this.updateRuleData();
    }

    updateRuleData() {
        this.ruleData = {
            name: document.getElementById('ruleName').value,
            action: document.getElementById('ruleAction').value,
            protocol: document.getElementById('ruleProtocol').value,
            source: {
                type: document.getElementById('sourceType').value,
                value: document.getElementById('sourceValue').value,
                port: document.getElementById('sourcePort').value
            },
            destination: {
                type: document.getElementById('destType').value,
                value: document.getElementById('destValue').value,
                port: document.getElementById('destPort').value
            },
            interface: document.getElementById('ruleInterface').value,
            direction: document.getElementById('ruleDirection').value,
            logging: document.getElementById('enableLogging').checked,
            comment: document.getElementById('ruleComment').value
        };
    }

    generateRule() {
        this.updateRuleData();
        
        // Validate rule data
        const validation = this.validateRule();
        if (!validation.valid) {
            this.showWarning(validation.message);
            return;
        }

        // Generate rule based on platform
        let rule = '';
        let notes = '';
        
        switch (this.currentPlatform) {
            case 'iptables':
                rule = this.generateIptablesRule();
                notes = 'Add this rule to your iptables configuration. Use iptables-save to persist.';
                break;
            case 'pfsense':
                rule = this.generatePfSenseRule();
                notes = 'Add this rule through the pfSense web interface under Firewall > Rules.';
                break;
            case 'cisco-asa':
                rule = this.generateCiscoASARule();
                notes = 'Apply this rule in configuration mode. Remember to save with "write memory".';
                break;
            case 'fortigate':
                rule = this.generateFortiGateRule();
                notes = 'Apply this in FortiGate CLI. Commit changes with "end" command.';
                break;
            case 'paloalto':
                rule = this.generatePaloAltoRule();
                notes = 'Add through Panorama or device CLI. Commit to activate.';
                break;
            case 'windows':
                rule = this.generateWindowsRule();
                notes = 'Run this command in an elevated PowerShell or Command Prompt.';
                break;
        }

        // Display generated rule
        this.displayGeneratedRule(rule, notes);
        
        // Check for dangerous rules
        this.checkDangerousRule();
    }

    validateRule() {
        // Basic validation
        if (this.ruleData.source.type !== 'any' && !this.ruleData.source.value) {
            return { valid: false, message: 'Source value is required when type is not "Any"' };
        }
        
        if (this.ruleData.destination.type !== 'any' && !this.ruleData.destination.value) {
            return { valid: false, message: 'Destination value is required when type is not "Any"' };
        }

        // Validate IP addresses
        if (this.ruleData.source.type === 'ip' && !this.isValidIP(this.ruleData.source.value)) {
            return { valid: false, message: 'Invalid source IP address format' };
        }
        
        if (this.ruleData.destination.type === 'ip' && !this.isValidIP(this.ruleData.destination.value)) {
            return { valid: false, message: 'Invalid destination IP address format' };
        }

        // Validate CIDR notation
        if (this.ruleData.source.type === 'network' && !this.isValidCIDR(this.ruleData.source.value)) {
            return { valid: false, message: 'Invalid source network CIDR format' };
        }
        
        if (this.ruleData.destination.type === 'network' && !this.isValidCIDR(this.ruleData.destination.value)) {
            return { valid: false, message: 'Invalid destination network CIDR format' };
        }
        
        // Validate zone names
        if (this.ruleData.source.type === 'zone' && !this.isValidZone(this.ruleData.source.value)) {
            return { valid: false, message: 'Invalid source zone name' };
        }
        
        if (this.ruleData.destination.type === 'zone' && !this.isValidZone(this.ruleData.destination.value)) {
            return { valid: false, message: 'Invalid destination zone name' };
        }

        // Validate ports
        if (this.ruleData.source.port && !this.isValidPort(this.ruleData.source.port)) {
            return { valid: false, message: 'Invalid source port format' };
        }
        
        if (this.ruleData.destination.port && !this.isValidPort(this.ruleData.destination.port)) {
            return { valid: false, message: 'Invalid destination port format' };
        }

        return { valid: true };
    }

    // Platform-specific rule generators
    generateIptablesRule() {
        let rules = [];
        let baseRule = 'iptables -A ';
        
        // Chain based on direction
        if (this.ruleData.direction === 'in' || !this.ruleData.direction) {
            baseRule += 'INPUT ';
        } else if (this.ruleData.direction === 'out') {
            baseRule += 'OUTPUT ';
        } else {
            baseRule += 'FORWARD ';
        }

        // Interface
        if (this.ruleData.interface) {
            if (this.ruleData.direction === 'out') {
                baseRule += `-o ${this.ruleData.interface} `;
            } else {
                baseRule += `-i ${this.ruleData.interface} `;
            }
        }

        // Protocol
        if (this.ruleData.protocol !== 'any') {
            baseRule += `-p ${this.ruleData.protocol} `;
        }

        // Source
        if (this.ruleData.source.type !== 'any' && this.ruleData.source.value) {
            baseRule += `-s ${this.ruleData.source.value} `;
        }

        // Source port (only for TCP/UDP)
        if (this.ruleData.source.port && this.ruleData.source.port !== 'any' && 
            (this.ruleData.protocol === 'tcp' || this.ruleData.protocol === 'udp')) {
            baseRule += `--sport ${this.ruleData.source.port} `;
        }

        // Destination
        if (this.ruleData.destination.type !== 'any' && this.ruleData.destination.value) {
            baseRule += `-d ${this.ruleData.destination.value} `;
        }

        // Destination port (only for TCP/UDP)
        if (this.ruleData.destination.port && this.ruleData.destination.port !== 'any' && 
            (this.ruleData.protocol === 'tcp' || this.ruleData.protocol === 'udp')) {
            // Handle multiple ports
            if (this.ruleData.destination.port.includes(',')) {
                baseRule += `-m multiport --dports ${this.ruleData.destination.port} `;
            } else {
                baseRule += `--dport ${this.ruleData.destination.port} `;
            }
        }

        // State tracking for established connections
        if (this.ruleData.action === 'allow' && this.ruleData.protocol === 'tcp') {
            baseRule += '-m state --state NEW,ESTABLISHED ';
        }

        // Comment
        if (this.ruleData.comment) {
            baseRule += `-m comment --comment "${this.ruleData.comment}" `;
        }

        // Action
        const action = this.ruleData.action === 'allow' ? 'ACCEPT' : 'DROP';

        // Logging
        if (this.ruleData.logging) {
            rules.push(baseRule + `-j LOG --log-prefix "${this.ruleData.name || 'FW'}: " --log-level 4`);
        }

        rules.push(baseRule + '-j ' + action);

        return rules.join('\n');
    }

    generatePfSenseRule() {
        let rule = '';
        
        rule += `Action: ${this.ruleData.action === 'allow' ? 'Pass' : 'Block'}\n`;
        rule += `Interface: ${this.ruleData.interface || 'WAN'}\n`;
        rule += `Protocol: ${this.ruleData.protocol.toUpperCase()}\n`;
        
        // Source
        rule += 'Source: ';
        if (this.ruleData.source.type === 'any') {
            rule += 'any';
        } else {
            rule += this.ruleData.source.value;
        }
        if (this.ruleData.source.port && this.ruleData.source.port !== 'any') {
            rule += ` port ${this.ruleData.source.port}`;
        }
        rule += '\n';

        // Destination
        rule += 'Destination: ';
        if (this.ruleData.destination.type === 'any') {
            rule += 'any';
        } else {
            rule += this.ruleData.destination.value;
        }
        if (this.ruleData.destination.port && this.ruleData.destination.port !== 'any') {
            rule += ` port ${this.ruleData.destination.port}`;
        }
        rule += '\n';

        if (this.ruleData.logging) {
            rule += 'Log: enabled\n';
        }

        if (this.ruleData.comment) {
            rule += `Description: ${this.ruleData.comment}\n`;
        }

        return rule;
    }

    generateCiscoASARule() {
        let rule = 'access-list ';
        
        // ACL name (use interface or default)
        const aclName = (this.ruleData.interface || 'outside') + '_access_in';
        rule += aclName + ' extended ';

        // Action
        rule += this.ruleData.action === 'allow' ? 'permit ' : 'deny ';

        // Protocol
        rule += this.ruleData.protocol === 'any' ? 'ip ' : this.ruleData.protocol + ' ';

        // Source
        if (this.ruleData.source.type === 'any') {
            rule += 'any ';
        } else if (this.ruleData.source.type === 'ip') {
            rule += 'host ' + this.ruleData.source.value + ' ';
        } else if (this.ruleData.source.type === 'network') {
            // Convert CIDR to Cisco format
            const [network, cidr] = this.ruleData.source.value.split('/');
            const mask = this.cidrToWildcard(parseInt(cidr));
            rule += network + ' ' + mask + ' ';
        } else {
            rule += this.ruleData.source.value + ' ';
        }

        // Destination
        if (this.ruleData.destination.type === 'any') {
            rule += 'any';
        } else if (this.ruleData.destination.type === 'ip') {
            rule += 'host ' + this.ruleData.destination.value;
        } else if (this.ruleData.destination.type === 'network') {
            // Convert CIDR to Cisco format
            const [network, cidr] = this.ruleData.destination.value.split('/');
            const mask = this.cidrToWildcard(parseInt(cidr));
            rule += network + ' ' + mask;
        } else {
            rule += this.ruleData.destination.value;
        }

        // Ports
        if (this.ruleData.destination.port && this.ruleData.destination.port !== 'any' && this.ruleData.protocol !== 'any' && this.ruleData.protocol !== 'ip') {
            // Handle port ranges
            if (this.ruleData.destination.port.includes('-')) {
                const [start, end] = this.ruleData.destination.port.split('-');
                rule += ' range ' + start + ' ' + end;
            } else if (this.ruleData.destination.port.includes(',')) {
                // Multiple ports need separate rules in Cisco ASA
                rule += ' eq ' + this.ruleData.destination.port.split(',')[0];
                rule += '\n# Note: Multiple ports require separate ACL entries in Cisco ASA';
            } else {
                rule += ' eq ' + this.ruleData.destination.port;
            }
        }

        // Logging
        if (this.ruleData.logging) {
            rule += ' log';
        }

        return rule;
    }
    
    // Helper function to convert CIDR to wildcard mask
    cidrToWildcard(cidr) {
        const bits = 32 - cidr;
        const wildcard = (Math.pow(2, bits) - 1);
        const octets = [];
        for (let i = 3; i >= 0; i--) {
            octets.push((wildcard >> (i * 8)) & 255);
        }
        return octets.join('.');
    }

    generateFortiGateRule() {
        let rule = 'config firewall policy\n';
        rule += 'edit 0\n';  // 0 creates new policy
        
        rule += `set name "${this.ruleData.name || 'Generated_Rule'}"\n`;
        rule += `set srcintf "${this.ruleData.interface || 'any'}"\n`;
        rule += `set dstintf "${this.ruleData.interface || 'any'}"\n`;
        
        // Source
        if (this.ruleData.source.type === 'any') {
            rule += 'set srcaddr "all"\n';
        } else {
            rule += `set srcaddr "${this.ruleData.source.value}"\n`;
        }

        // Destination
        if (this.ruleData.destination.type === 'any') {
            rule += 'set dstaddr "all"\n';
        } else {
            rule += `set dstaddr "${this.ruleData.destination.value}"\n`;
        }

        // Action
        rule += `set action ${this.ruleData.action === 'allow' ? 'accept' : 'deny'}\n`;

        // Service/Port
        if (this.ruleData.destination.port && this.ruleData.destination.port !== 'any') {
            rule += `set service "${this.ruleData.protocol.toUpperCase()}-${this.ruleData.destination.port}"\n`;
        } else {
            rule += 'set service "ALL"\n';
        }

        rule += 'set schedule "always"\n';
        
        if (this.ruleData.logging) {
            rule += 'set logtraffic all\n';
        }

        if (this.ruleData.comment) {
            rule += `set comments "${this.ruleData.comment}"\n`;
        }

        rule += 'next\n';
        rule += 'end';

        return rule;
    }

    generatePaloAltoRule() {
        let rule = 'set rulebase security rules ';
        const ruleName = (this.ruleData.name || 'Generated_Rule').replace(/\s+/g, '_');
        
        rule += ruleName + ' ';
        
        // Source zone
        rule += 'from ' + (this.ruleData.interface || 'any') + ' ';
        
        // Destination zone  
        rule += 'to ' + (this.ruleData.interface || 'any') + ' ';
        
        // Source
        if (this.ruleData.source.type === 'any') {
            rule += 'source any ';
        } else {
            rule += 'source ' + this.ruleData.source.value + ' ';
        }

        // Destination
        if (this.ruleData.destination.type === 'any') {
            rule += 'destination any ';
        } else {
            rule += 'destination ' + this.ruleData.destination.value + ' ';
        }

        // Service
        if (this.ruleData.destination.port && this.ruleData.destination.port !== 'any') {
            rule += 'service ' + this.ruleData.protocol + '-' + this.ruleData.destination.port + ' ';
        } else {
            rule += 'service any ';
        }

        // Application
        rule += 'application any ';

        // Action
        rule += 'action ' + (this.ruleData.action === 'allow' ? 'allow' : 'deny');

        if (this.ruleData.logging) {
            rule += '\nset rulebase security rules ' + ruleName + ' log-setting default';
        }

        return rule;
    }

    generateWindowsRule() {
        let rule = 'netsh advfirewall firewall add rule ';
        
        rule += `name="${this.ruleData.name || 'Generated Rule'}" `;
        rule += `dir=${this.ruleData.direction === 'out' ? 'out' : 'in'} `;
        rule += `action=${this.ruleData.action === 'allow' ? 'allow' : 'block'} `;
        
        if (this.ruleData.protocol !== 'any') {
            rule += `protocol=${this.ruleData.protocol} `;
        }

        // Source (remote for inbound, local for outbound)
        if (this.ruleData.source.type !== 'any' && this.ruleData.source.value) {
            const addr = this.ruleData.direction === 'out' ? 'localip' : 'remoteip';
            rule += `${addr}=${this.ruleData.source.value} `;
        }

        // Destination
        if (this.ruleData.destination.type !== 'any' && this.ruleData.destination.value) {
            const addr = this.ruleData.direction === 'out' ? 'remoteip' : 'localip';
            rule += `${addr}=${this.ruleData.destination.value} `;
        }

        // Ports
        if (this.ruleData.destination.port && this.ruleData.destination.port !== 'any' && this.ruleData.protocol !== 'any') {
            const port = this.ruleData.direction === 'out' ? 'remoteport' : 'localport';
            rule += `${port}=${this.ruleData.destination.port} `;
        }

        if (this.ruleData.interface) {
            rule += `interfacetype=${this.ruleData.interface} `;
        }

        rule += 'enable=yes';

        return rule;
    }

    displayGeneratedRule(rule, notes) {
        document.getElementById('generatedRuleCode').textContent = rule;
        document.getElementById('generatedOutput').style.display = 'block';
        
        if (notes) {
            document.getElementById('platformNotesContent').textContent = notes;
            document.getElementById('platformNotes').style.display = 'block';
        } else {
            document.getElementById('platformNotes').style.display = 'none';
        }

        // Syntax highlighting if Prism is available
        if (typeof Prism !== 'undefined') {
            Prism.highlightElement(document.getElementById('generatedRuleCode'));
        }
    }

    checkDangerousRule() {
        let warning = '';
        
        // Check for overly permissive rules
        if (this.ruleData.source.type === 'any' && 
            this.ruleData.destination.type === 'any' && 
            this.ruleData.action === 'allow') {
            warning = 'This rule allows ALL traffic from ANY source to ANY destination. This is extremely permissive and could pose a security risk.';
        } else if (this.ruleData.source.type === 'any' && this.ruleData.action === 'allow') {
            warning = 'This rule allows traffic from ANY source. Consider restricting the source for better security.';
        } else if (this.ruleData.destination.type === 'network' && 
                   this.ruleData.destination.value === '0.0.0.0/0' && 
                   this.ruleData.action === 'allow') {
            warning = 'This rule allows traffic to the entire internet. Ensure this is intentional.';
        }

        if (warning) {
            this.showWarning(warning);
        } else {
            document.getElementById('warningSection').style.display = 'none';
        }
    }

    loadTemplates(platform) {
        const templateGrid = document.getElementById('templateGrid');
        templateGrid.innerHTML = '';
        
        const platformTemplates = this.templates.filter(t => 
            t.platforms.includes('all') || t.platforms.includes(platform)
        );

        platformTemplates.forEach(template => {
            const col = document.createElement('div');
            col.className = 'col-md-4 col-sm-6';
            
            const card = document.createElement('div');
            card.className = 'card bg-secondary h-100 cursor-pointer template-card';
            card.innerHTML = `
                <div class="card-body">
                    <h6 class="card-title">${template.name}</h6>
                    <p class="card-text small">${template.description}</p>
                </div>
            `;
            
            card.addEventListener('click', () => this.applyTemplate(template));
            
            col.appendChild(card);
            templateGrid.appendChild(col);
        });
    }

    applyTemplate(template) {
        // Apply template data to form
        if (template.rule.name) document.getElementById('ruleName').value = template.rule.name;
        if (template.rule.action) document.getElementById('ruleAction').value = template.rule.action;
        if (template.rule.protocol) document.getElementById('ruleProtocol').value = template.rule.protocol;
        
        // Source
        if (template.rule.source) {
            document.getElementById('sourceType').value = template.rule.source.type || 'any';
            this.handleTypeChange('source', template.rule.source.type || 'any');
            if (template.rule.source.value) document.getElementById('sourceValue').value = template.rule.source.value;
            if (template.rule.source.port) document.getElementById('sourcePort').value = template.rule.source.port;
        }

        // Destination
        if (template.rule.destination) {
            document.getElementById('destType').value = template.rule.destination.type || 'any';
            this.handleTypeChange('dest', template.rule.destination.type || 'any');
            if (template.rule.destination.value) document.getElementById('destValue').value = template.rule.destination.value;
            if (template.rule.destination.port) document.getElementById('destPort').value = template.rule.destination.port;
        }

        // Advanced options
        if (template.rule.interface) document.getElementById('ruleInterface').value = template.rule.interface;
        if (template.rule.direction) document.getElementById('ruleDirection').value = template.rule.direction;
        if (template.rule.logging !== undefined) document.getElementById('enableLogging').checked = template.rule.logging;
        if (template.rule.comment) document.getElementById('ruleComment').value = template.rule.comment;

        this.updateRuleData();
        
        // Auto-generate if template specifies
        if (template.autoGenerate) {
            this.generateRule();
        }
    }

    getTemplates() {
        return [
            {
                name: 'Web Server',
                description: 'Allow HTTP/HTTPS traffic',
                platforms: ['all'],
                rule: {
                    name: 'Allow Web Traffic',
                    action: 'allow',
                    protocol: 'tcp',
                    source: { type: 'any' },
                    destination: { type: 'any', port: '80,443' },
                    comment: 'Allow incoming web traffic'
                }
            },
            {
                name: 'SSH Access',
                description: 'Allow SSH from specific network',
                platforms: ['all'],
                rule: {
                    name: 'Allow SSH Management',
                    action: 'allow',
                    protocol: 'tcp',
                    source: { type: 'network', value: '10.0.0.0/24' },
                    destination: { type: 'any', port: '22' },
                    comment: 'Allow SSH from management network'
                }
            },
            {
                name: 'Database Server',
                description: 'Allow database connections',
                platforms: ['all'],
                rule: {
                    name: 'Allow Database Access',
                    action: 'allow',
                    protocol: 'tcp',
                    source: { type: 'network', value: '10.0.1.0/24' },
                    destination: { type: 'any', port: '3306' },
                    comment: 'Allow MySQL from app servers'
                }
            },
            {
                name: 'Mail Server',
                description: 'Allow email protocols',
                platforms: ['all'],
                rule: {
                    name: 'Allow Email Services',
                    action: 'allow',
                    protocol: 'tcp',
                    source: { type: 'any' },
                    destination: { type: 'any', port: '25,587,993,995' },
                    comment: 'Allow SMTP, SMTP-TLS, IMAPS, POP3S'
                }
            },
            {
                name: 'VPN Gateway',
                description: 'Allow VPN connections',
                platforms: ['all'],
                rule: {
                    name: 'Allow VPN Traffic',
                    action: 'allow',
                    protocol: 'udp',
                    source: { type: 'any' },
                    destination: { type: 'any', port: '500,4500' },
                    comment: 'Allow IPSec VPN'
                }
            },
            {
                name: 'Block All',
                description: 'Deny all traffic (default deny)',
                platforms: ['all'],
                rule: {
                    name: 'Default Deny Rule',
                    action: 'deny',
                    protocol: 'any',
                    source: { type: 'any' },
                    destination: { type: 'any' },
                    logging: true,
                    comment: 'Log and drop all unmatched traffic'
                }
            }
        ];
    }

    updatePlatformUI(platform) {
        // Show/hide platform-specific options
        const directionField = document.getElementById('ruleDirection').parentElement;
        const interfaceField = document.getElementById('ruleInterface').parentElement;
        
        // Different platforms have different capabilities
        switch (platform) {
            case 'iptables':
            case 'windows':
                directionField.style.display = 'block';
                break;
            default:
                directionField.style.display = 'none';
        }
    }

    // Validation helpers
    isValidIP(ip) {
        const ipRegex = /^(?:(?:25[0-5]|2[0-4][0-9]|[01]?[0-9][0-9]?)\.){3}(?:25[0-5]|2[0-4][0-9]|[01]?[0-9][0-9]?)$/;
        return ipRegex.test(ip);
    }

    isValidCIDR(cidr) {
        const cidrRegex = /^(?:(?:25[0-5]|2[0-4][0-9]|[01]?[0-9][0-9]?)\.){3}(?:25[0-5]|2[0-4][0-9]|[01]?[0-9][0-9]?)\/(?:3[0-2]|[1-2]?[0-9])$/;
        return cidrRegex.test(cidr);
    }
    
    isValidZone(zone) {
        // Basic validation for zone names (alphanumeric, dash, underscore)
        const zoneRegex = /^[a-zA-Z0-9_-]+$/;
        return zoneRegex.test(zone);
    }

    isValidPort(port) {
        // Single port
        if (/^\d+$/.test(port)) {
            const num = parseInt(port);
            return num >= 1 && num <= 65535;
        }
        
        // Port range
        if (/^\d+-\d+$/.test(port)) {
            const [start, end] = port.split('-').map(p => parseInt(p));
            return start >= 1 && start <= 65535 && end >= start && end <= 65535;
        }
        
        // Multiple ports
        if (/^[\d,]+$/.test(port)) {
            const ports = port.split(',').map(p => parseInt(p));
            return ports.every(p => p >= 1 && p <= 65535);
        }
        
        return port.toLowerCase() === 'any';
    }

    // UI helpers
    showWarning(message) {
        document.getElementById('warningContent').textContent = message;
        document.getElementById('warningSection').style.display = 'block';
    }

    copyRule() {
        const ruleText = document.getElementById('generatedRuleCode').textContent;
        
        if (navigator.clipboard) {
            navigator.clipboard.writeText(ruleText).then(() => {
                const btn = document.getElementById('copyRuleBtn');
                const originalHtml = btn.innerHTML;
                btn.innerHTML = '<i class="fas fa-check me-1"></i>Copied!';
                setTimeout(() => {
                    btn.innerHTML = originalHtml;
                }, 2000);
            });
        } else {
            // Fallback for older browsers
            const textArea = document.createElement('textarea');
            textArea.value = ruleText;
            document.body.appendChild(textArea);
            textArea.select();
            document.execCommand('copy');
            document.body.removeChild(textArea);
        }
    }

    clearRule() {
        document.getElementById('ruleName').value = '';
        document.getElementById('ruleAction').value = 'allow';
        document.getElementById('ruleProtocol').value = 'any';
        document.getElementById('sourceType').value = 'any';
        document.getElementById('sourceValue').value = '';
        document.getElementById('sourcePort').value = '';
        document.getElementById('destType').value = 'any';
        document.getElementById('destValue').value = '';
        document.getElementById('destPort').value = '';
        document.getElementById('ruleInterface').value = '';
        document.getElementById('ruleDirection').value = '';
        document.getElementById('enableLogging').checked = false;
        document.getElementById('ruleComment').value = '';
        
        this.handleTypeChange('source', 'any');
        this.handleTypeChange('dest', 'any');
        
        document.getElementById('generatedOutput').style.display = 'none';
        document.getElementById('warningSection').style.display = 'none';
        
        this.ruleData = this.getDefaultRuleData();
    }

    newRule() {
        this.clearRule();
        document.getElementById('ruleName').focus();
    }

    resetForm() {
        this.clearRule();
        document.getElementById('platformSelect').value = '';
        this.handlePlatformChange('');
    }

    showHelp() {
        const helpModal = new bootstrap.Modal(document.getElementById('firewallHelpModal'));
        helpModal.show();
    }

    getDefaultRuleData() {
        return {
            name: '',
            action: 'allow',
            protocol: 'any',
            source: { type: 'any', value: '', port: '' },
            destination: { type: 'any', value: '', port: '' },
            interface: '',
            direction: '',
            logging: false,
            comment: ''
        };
    }
}

/**
 * Initialize Firewall Rule Generator functionality
 */
function setupFirewallRuleGenerator() {
    console.log('Setting up Firewall Rule Generator...');
    
    // Create instance of the generator
    const generator = new FirewallRuleGenerator();
    
    // Make it available globally if needed
    window.firewallRuleGenerator = generator;
    
    console.log('Firewall Rule Generator setup complete');
}

// Export for use in main network-admin.js
export default setupFirewallRuleGenerator;