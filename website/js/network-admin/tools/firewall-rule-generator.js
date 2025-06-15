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
        
        // Direction change handler
        document.getElementById('ruleDirection').addEventListener('change', (e) => {
            this.updateDirectionIndicators(e.target.value);
            this.updateRuleData();
            // Re-validate with new direction context
            if (document.getElementById('generatedOutput').style.display !== 'none') {
                this.checkDangerousRule();
            }
        });

        // Source/Destination type changes
        document.getElementById('sourceType').addEventListener('change', (e) => {
            this.handleTypeChange('source', e.target.value);
        });

        document.getElementById('destType').addEventListener('change', (e) => {
            this.handleTypeChange('dest', e.target.value);
        });

        // Form inputs with real-time validation
        const inputs = ['ruleName', 'ruleAction', 'ruleProtocol', 'sourceValue', 'sourcePort', 
                       'destValue', 'destPort', 'ruleInterface', 'ruleDirection', 'ruleComment'];
        inputs.forEach(id => {
            const element = document.getElementById(id);
            if (element) {
                element.addEventListener('input', () => {
                    this.updateRuleData();
                    this.validateField(id);
                });
                
                // Add blur event for final validation
                element.addEventListener('blur', () => {
                    this.validateField(id);
                });
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
        
        // Show validation warning if present but continue
        if (validation.warning) {
            this.showWarning(validation.warning);
        }

        // Generate rule based on platform
        let rule = '';
        let notes = '';
        
        switch (this.currentPlatform) {
            case 'iptables':
                rule = this.generateIptablesRule();
                notes = 'Add this rule to your iptables configuration. Use iptables-save to persist.';
                if (this.ruleData.direction === 'both') {
                    notes += ' Note: Two rules generated for bidirectional traffic.';
                }
                break;
            case 'pfsense':
                rule = this.generatePfSenseRule();
                notes = 'Add this rule through the pfSense web interface under Firewall > Rules. ';
                notes += 'Direction is determined by the interface where the rule is applied.';
                break;
            case 'cisco-asa':
                rule = this.generateCiscoASARule();
                notes = 'Apply this rule in configuration mode. Remember to save with "write memory". ';
                notes += 'Direction is controlled by ACL assignment to interface (in/out).';
                break;
            case 'fortigate':
                rule = this.generateFortiGateRule();
                notes = 'Apply this in FortiGate CLI. Commit changes with "end" command. ';
                notes += 'FortiGate uses srcintf/dstintf to control traffic direction.';
                break;
            case 'paloalto':
                rule = this.generatePaloAltoRule();
                notes = 'Add through Panorama or device CLI. Commit to activate. ';
                notes += 'Direction is implicit based on zone configuration.';
                break;
            case 'windows':
                rule = this.generateWindowsRule();
                notes = 'Run this command in an elevated PowerShell or Command Prompt.';
                if (this.ruleData.direction === 'both') {
                    notes += ' Note: Two rules generated for bidirectional traffic.';
                }
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
        
        // Direction-specific validation
        const direction = this.ruleData.direction;
        
        // Inbound validation
        if (direction === 'in' || direction === 'both') {
            // Warn about inbound rules to all interfaces
            if (this.ruleData.destination.type === 'any' && 
                this.ruleData.action === 'allow' &&
                (!this.ruleData.interface || this.ruleData.interface === 'any')) {
                return { 
                    valid: false, 
                    message: 'Inbound rules should specify a destination or interface to avoid exposing all services' 
                };
            }
            
            // Validate source ports for inbound rules (usually should be any/empty)
            if (this.ruleData.source.port && this.ruleData.source.port !== 'any') {
                // This is usually a mistake for inbound rules
                console.warn('Source port specified for inbound rule - this is unusual and may not work as expected');
            }
        }
        
        // Outbound validation
        if (direction === 'out' || direction === 'both') {
            // Check for overly broad outbound rules
            if (this.ruleData.protocol === 'any' && 
                this.ruleData.destination.type === 'any' &&
                (!this.ruleData.destination.port || this.ruleData.destination.port === 'any')) {
                return { 
                    valid: true, 
                    warning: 'This outbound rule is very permissive. Consider restricting protocol or ports.' 
                };
            }
        }

        return { valid: true };
    }

    // Platform-specific rule generators
    generateIptablesRule() {
        let rules = [];
        
        // Handle "both" direction by generating two rules
        if (this.ruleData.direction === 'both') {
            // Generate inbound rule
            let inboundRule = this.generateIptablesRuleForDirection('in');
            rules.push(...inboundRule.split('\n'));
            
            // Generate outbound rule
            let outboundRule = this.generateIptablesRuleForDirection('out');
            rules.push(...outboundRule.split('\n'));
            
            return rules.join('\n');
        }
        
        // Single direction rule
        return this.generateIptablesRuleForDirection(this.ruleData.direction);
    }
    
    generateIptablesRuleForDirection(direction) {
        let rules = [];
        let baseRule = 'iptables -A ';
        
        // Chain based on direction
        if (direction === 'in') {
            baseRule += 'INPUT ';
        } else if (direction === 'out') {
            baseRule += 'OUTPUT ';
        } else {
            baseRule += 'FORWARD ';
        }

        // Interface
        if (this.ruleData.interface) {
            if (direction === 'out') {
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
        let rules = [];
        
        // Handle "both" direction
        if (this.ruleData.direction === 'both') {
            rules.push('# Note: pfSense handles direction implicitly based on interface assignment');
            rules.push('# You may need to create two separate rules for true bidirectional traffic');
            rules.push('');
        }
        
        // Determine interface based on direction
        let suggestedInterface = this.ruleData.interface;
        if (!suggestedInterface) {
            if (this.ruleData.direction === 'in') {
                suggestedInterface = 'WAN';  // Inbound typically on WAN
            } else if (this.ruleData.direction === 'out') {
                suggestedInterface = 'LAN';  // Outbound typically on LAN
            } else {
                suggestedInterface = 'WAN';  // Default
            }
        }
        
        let rule = '';
        rule += `# Direction: ${this.ruleData.direction === 'in' ? 'Inbound' : 
                               this.ruleData.direction === 'out' ? 'Outbound' : 'Bidirectional'}\n`;
        rule += `Action: ${this.ruleData.action === 'allow' ? 'Pass' : 'Block'}\n`;
        rule += `Interface: ${suggestedInterface}\n`;
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
        
        rules.push(rule);
        
        // Add interface assignment hint
        if (this.ruleData.direction === 'both') {
            rules.push('\n# For bidirectional traffic, consider creating matching rules on both WAN and LAN interfaces');
        }
        
        return rules.join('\n');
    }

    generateCiscoASARule() {
        let rules = [];
        
        // Handle "both" direction
        if (this.ruleData.direction === 'both') {
            // Generate inbound rule
            rules.push(this.generateCiscoASARuleForDirection('in'));
            rules.push('');
            // Generate outbound rule
            rules.push(this.generateCiscoASARuleForDirection('out'));
            return rules.join('\n');
        }
        
        return this.generateCiscoASARuleForDirection(this.ruleData.direction);
    }
    
    generateCiscoASARuleForDirection(direction) {
        let rule = 'access-list ';
        
        // Determine interface based on direction if not specified
        let interfaceName = this.ruleData.interface;
        if (!interfaceName) {
            interfaceName = direction === 'in' ? 'outside' : 'inside';
        }
        
        // ACL name based on interface and direction
        const aclName = interfaceName + '_access_' + (direction === 'out' ? 'out' : 'in');
        rule += aclName + ' extended ';
        
        // Add comment about direction
        rule = `! Direction: ${direction === 'in' ? 'Inbound' : 'Outbound'} rule\n` + rule;

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
        // Handle "both" direction by generating two rules
        if (this.ruleData.direction === 'both') {
            let rules = [];
            
            // Generate inbound rule
            rules.push(this.generateWindowsRuleForDirection('in', `${this.ruleData.name || 'Generated Rule'} - Inbound`));
            
            // Generate outbound rule
            rules.push(this.generateWindowsRuleForDirection('out', `${this.ruleData.name || 'Generated Rule'} - Outbound`));
            
            return rules.join('\n');
        }
        
        // Single direction rule
        return this.generateWindowsRuleForDirection(this.ruleData.direction, this.ruleData.name || 'Generated Rule');
    }
    
    generateWindowsRuleForDirection(direction, ruleName) {
        let rule = 'netsh advfirewall firewall add rule ';
        
        rule += `name="${ruleName}" `;
        rule += `dir=${direction === 'out' ? 'out' : 'in'} `;
        rule += `action=${this.ruleData.action === 'allow' ? 'allow' : 'block'} `;
        
        if (this.ruleData.protocol !== 'any') {
            rule += `protocol=${this.ruleData.protocol} `;
        }

        // Source (remote for inbound, local for outbound)
        if (this.ruleData.source.type !== 'any' && this.ruleData.source.value) {
            const addr = direction === 'out' ? 'localip' : 'remoteip';
            rule += `${addr}=${this.ruleData.source.value} `;
        }

        // Destination
        if (this.ruleData.destination.type !== 'any' && this.ruleData.destination.value) {
            const addr = direction === 'out' ? 'remoteip' : 'localip';
            rule += `${addr}=${this.ruleData.destination.value} `;
        }

        // Ports
        if (this.ruleData.destination.port && this.ruleData.destination.port !== 'any' && this.ruleData.protocol !== 'any') {
            const port = direction === 'out' ? 'remoteport' : 'localport';
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
        let warnings = [];
        const direction = this.ruleData.direction;
        
        // Direction-specific validation
        if (direction === 'in' || direction === 'both') {
            // Inbound-specific checks
            if (this.ruleData.source.type === 'any' && 
                this.ruleData.destination.type === 'any' && 
                this.ruleData.action === 'allow') {
                warnings.push({
                    level: 'danger',
                    message: 'CRITICAL: This inbound rule allows ALL traffic from ANY external source to ANY internal destination. This is extremely dangerous and could expose your entire network!'
                });
            } else if (this.ruleData.source.type === 'any' && this.ruleData.action === 'allow') {
                // Check for sensitive ports
                const sensitiveInboundPorts = ['22', '3389', '3306', '5432', '1433', '27017'];
                const destPorts = this.ruleData.destination.port ? this.ruleData.destination.port.split(',') : [];
                const hasSensitivePort = destPorts.some(port => sensitiveInboundPorts.includes(port.trim()));
                
                if (hasSensitivePort) {
                    warnings.push({
                        level: 'danger',
                        message: 'HIGH RISK: Allowing inbound access to sensitive services (SSH, RDP, databases) from ANY source. This could lead to unauthorized access!'
                    });
                } else {
                    warnings.push({
                        level: 'warning',
                        message: 'This inbound rule allows traffic from ANY external source. Consider restricting to specific IPs or networks for better security.'
                    });
                }
            }
            
            // Check for private IP exposure
            if (this.ruleData.source.type === 'any' && 
                this.ruleData.destination.type === 'network' &&
                this.isPrivateNetwork(this.ruleData.destination.value)) {
                warnings.push({
                    level: 'warning',
                    message: 'This rule exposes internal/private networks to external access. Ensure this is intentional.'
                });
            }
        }
        
        if (direction === 'out' || direction === 'both') {
            // Outbound-specific checks
            if (this.ruleData.destination.type === 'any' && 
                this.ruleData.action === 'allow') {
                // Check for data exfiltration risks
                const riskyOutboundPorts = ['20', '21', '22', '23', '3389', '445', '139'];
                const destPorts = this.ruleData.destination.port ? this.ruleData.destination.port.split(',') : [];
                const hasRiskyPort = destPorts.some(port => riskyOutboundPorts.includes(port.trim()));
                
                if (hasRiskyPort) {
                    warnings.push({
                        level: 'warning',
                        message: 'CAUTION: Allowing outbound access to file transfer or remote access protocols. Monitor for potential data exfiltration.'
                    });
                } else if (!this.ruleData.destination.port || this.ruleData.destination.port === 'any') {
                    warnings.push({
                        level: 'warning',
                        message: 'This outbound rule allows traffic to ANY destination on ANY port. Consider restricting to specific services.'
                    });
                }
            }
            
            // Check for DNS hijacking risks
            if (this.ruleData.destination.port && this.ruleData.destination.port.includes('53') &&
                this.ruleData.destination.type === 'any') {
                warnings.push({
                    level: 'info',
                    message: 'DNS queries allowed to any server. Consider restricting to trusted DNS servers to prevent DNS hijacking.'
                });
            }
        }
        
        // General checks for all directions
        if (this.ruleData.destination.type === 'network' && 
            this.ruleData.destination.value === '0.0.0.0/0' && 
            this.ruleData.action === 'allow') {
            warnings.push({
                level: 'info',
                message: 'This rule allows traffic to the entire internet (0.0.0.0/0). Ensure this is intentional.'
            });
        }
        
        // Check for missing logging on deny rules
        if (this.ruleData.action === 'deny' && !this.ruleData.logging) {
            warnings.push({
                level: 'info',
                message: 'Consider enabling logging for deny rules to track blocked connection attempts.'
            });
        }
        
        // Display warnings
        if (warnings.length > 0) {
            this.displayWarnings(warnings);
        } else {
            document.getElementById('warningSection').style.display = 'none';
        }
    }
    
    displayWarnings(warnings) {
        const warningSection = document.getElementById('warningSection');
        const warningContent = document.getElementById('warningContent');
        
        // Sort warnings by severity
        const severityOrder = { danger: 0, warning: 1, info: 2 };
        warnings.sort((a, b) => severityOrder[a.level] - severityOrder[b.level]);
        
        // Build warning HTML
        let html = '';
        warnings.forEach(warning => {
            const icon = warning.level === 'danger' ? 'fa-exclamation-triangle' :
                        warning.level === 'warning' ? 'fa-exclamation-circle' : 'fa-info-circle';
            const alertClass = warning.level === 'danger' ? 'alert-danger' :
                              warning.level === 'warning' ? 'alert-warning' : 'alert-info';
            
            // Update main warning section class based on highest severity
            if (warnings[0].level === warning.level) {
                warningSection.className = `alert ${alertClass}`;
            }
            
            html += `<div class="mb-2"><i class="fas ${icon} me-2"></i>${warning.message}</div>`;
        });
        
        warningContent.innerHTML = html;
        warningSection.style.display = 'block';
    }
    
    isPrivateNetwork(cidr) {
        if (!cidr || !cidr.includes('/')) return false;
        const ip = cidr.split('/')[0];
        const parts = ip.split('.');
        if (parts.length !== 4) return false;
        
        const first = parseInt(parts[0]);
        const second = parseInt(parts[1]);
        
        // Check for private IP ranges
        return (first === 10) ||
               (first === 172 && second >= 16 && second <= 31) ||
               (first === 192 && second === 168);
    }

    loadTemplates(platform) {
        const templateGrid = document.getElementById('templateGrid');
        templateGrid.innerHTML = '';
        
        // Get current direction
        const currentDirection = document.getElementById('ruleDirection').value;
        
        // Filter templates by platform and direction
        const platformTemplates = this.templates.filter(t => {
            const platformMatch = t.platforms.includes('all') || t.platforms.includes(platform);
            const directionMatch = !t.direction || t.direction === currentDirection || 
                                 t.direction === 'both' || currentDirection === 'both';
            return platformMatch && directionMatch;
        });
        
        // Group templates by category
        const categories = {
            inbound: [],
            outbound: [],
            bidirectional: [],
            security: []
        };
        
        platformTemplates.forEach(template => {
            const category = template.category || 'security';
            if (categories[category]) {
                categories[category].push(template);
            }
        });
        
        // Render templates by category
        Object.entries(categories).forEach(([category, templates]) => {
            if (templates.length === 0) return;
            
            // Add category header
            const headerDiv = document.createElement('div');
            headerDiv.className = 'col-12 mb-2';
            headerDiv.innerHTML = `
                <h6 class="text-muted text-uppercase">
                    ${category.charAt(0).toUpperCase() + category.slice(1)} Templates
                </h6>
            `;
            templateGrid.appendChild(headerDiv);
            
            // Add templates
            templates.forEach(template => {
                const col = document.createElement('div');
                col.className = 'col-md-4 col-sm-6 mb-3';
                
                const card = document.createElement('div');
                card.className = 'card bg-secondary h-100 cursor-pointer template-card';
                
                // Add direction badge
                let directionBadge = '';
                if (template.direction) {
                    const badgeClass = template.direction === 'in' ? 'primary' : 
                                     template.direction === 'out' ? 'success' : 'info';
                    directionBadge = `<span class="badge bg-${badgeClass} position-absolute top-0 end-0 m-2">
                        ${template.direction === 'in' ? 'Inbound' : 
                          template.direction === 'out' ? 'Outbound' : 'Bidirectional'}
                    </span>`;
                }
                
                card.innerHTML = `
                    <div class="card-body">
                        ${directionBadge}
                        <h6 class="card-title">${template.name}</h6>
                        <p class="card-text small">${template.description}</p>
                    </div>
                `;
                
                card.addEventListener('click', () => this.applyTemplate(template));
                
                col.appendChild(card);
                templateGrid.appendChild(col);
            });
        });
        
        // Update templates when direction changes
        if (!this.directionListener) {
            this.directionListener = true;
            document.getElementById('ruleDirection').addEventListener('change', () => {
                if (this.currentPlatform) {
                    this.loadTemplates(this.currentPlatform);
                }
            });
        }
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
            // Inbound Templates
            {
                name: 'Web Server (Inbound)',
                description: 'Allow incoming HTTP/HTTPS traffic to web server',
                platforms: ['all'],
                direction: 'in',
                category: 'inbound',
                rule: {
                    name: 'Allow Inbound Web Traffic',
                    action: 'allow',
                    protocol: 'tcp',
                    direction: 'in',
                    source: { type: 'any' },
                    destination: { type: 'any', port: '80,443' },
                    comment: 'Allow incoming web traffic from internet'
                }
            },
            {
                name: 'SSH Management (Inbound)',
                description: 'Allow SSH from admin network',
                platforms: ['all'],
                direction: 'in',
                category: 'inbound',
                rule: {
                    name: 'Allow Inbound SSH Management',
                    action: 'allow',
                    protocol: 'tcp',
                    direction: 'in',
                    source: { type: 'network', value: '10.0.0.0/24' },
                    destination: { type: 'any', port: '22' },
                    comment: 'Allow SSH from management network only'
                }
            },
            {
                name: 'Database Access (Inbound)',
                description: 'Allow app servers to connect to database',
                platforms: ['all'],
                direction: 'in',
                category: 'inbound',
                rule: {
                    name: 'Allow Inbound Database Access',
                    action: 'allow',
                    protocol: 'tcp',
                    direction: 'in',
                    source: { type: 'network', value: '10.0.1.0/24' },
                    destination: { type: 'any', port: '3306' },
                    comment: 'Allow MySQL connections from app servers'
                }
            },
            {
                name: 'Mail Server (Inbound)',
                description: 'Accept incoming email connections',
                platforms: ['all'],
                direction: 'in',
                category: 'inbound',
                rule: {
                    name: 'Allow Inbound Email',
                    action: 'allow',
                    protocol: 'tcp',
                    direction: 'in',
                    source: { type: 'any' },
                    destination: { type: 'any', port: '25,587,993,995' },
                    comment: 'Accept SMTP, SMTP-TLS, IMAPS, POP3S'
                }
            },
            {
                name: 'VPN Server (Inbound)',
                description: 'Accept VPN client connections',
                platforms: ['all'],
                direction: 'in',
                category: 'inbound',
                rule: {
                    name: 'Allow Inbound VPN',
                    action: 'allow',
                    protocol: 'udp',
                    direction: 'in',
                    source: { type: 'any' },
                    destination: { type: 'any', port: '500,4500' },
                    comment: 'Accept IPSec VPN connections'
                }
            },
            {
                name: 'RDP Access (Inbound)',
                description: 'Allow Remote Desktop from local network',
                platforms: ['all'],
                direction: 'in',
                category: 'inbound',
                rule: {
                    name: 'Allow Inbound RDP',
                    action: 'allow',
                    protocol: 'tcp',
                    direction: 'in',
                    source: { type: 'network', value: '192.168.1.0/24' },
                    destination: { type: 'any', port: '3389' },
                    comment: 'Allow RDP from internal network only'
                }
            },
            
            // Outbound Templates
            {
                name: 'Web Browsing (Outbound)',
                description: 'Allow users to browse the web',
                platforms: ['all'],
                direction: 'out',
                category: 'outbound',
                rule: {
                    name: 'Allow Outbound Web',
                    action: 'allow',
                    protocol: 'tcp',
                    direction: 'out',
                    source: { type: 'any' },
                    destination: { type: 'any', port: '80,443' },
                    comment: 'Allow outbound HTTP/HTTPS traffic'
                }
            },
            {
                name: 'DNS Queries (Outbound)',
                description: 'Allow DNS lookups to DNS servers',
                platforms: ['all'],
                direction: 'out',
                category: 'outbound',
                rule: {
                    name: 'Allow Outbound DNS',
                    action: 'allow',
                    protocol: 'udp',
                    direction: 'out',
                    source: { type: 'any' },
                    destination: { type: 'network', value: '8.8.8.8', port: '53' },
                    comment: 'Allow DNS queries to Google DNS'
                }
            },
            {
                name: 'Email Client (Outbound)',
                description: 'Allow email clients to send mail',
                platforms: ['all'],
                direction: 'out',
                category: 'outbound',
                rule: {
                    name: 'Allow Outbound Email',
                    action: 'allow',
                    protocol: 'tcp',
                    direction: 'out',
                    source: { type: 'any' },
                    destination: { type: 'any', port: '25,587,465' },
                    comment: 'Allow SMTP/SMTPS for sending email'
                }
            },
            {
                name: 'Software Updates (Outbound)',
                description: 'Allow system to download updates',
                platforms: ['all'],
                direction: 'out',
                category: 'outbound',
                rule: {
                    name: 'Allow Outbound Updates',
                    action: 'allow',
                    protocol: 'tcp',
                    direction: 'out',
                    source: { type: 'any' },
                    destination: { type: 'any', port: '80,443' },
                    comment: 'Allow package managers and update services'
                }
            },
            {
                name: 'NTP Time Sync (Outbound)',
                description: 'Allow time synchronization',
                platforms: ['all'],
                direction: 'out',
                category: 'outbound',
                rule: {
                    name: 'Allow Outbound NTP',
                    action: 'allow',
                    protocol: 'udp',
                    direction: 'out',
                    source: { type: 'any' },
                    destination: { type: 'any', port: '123' },
                    comment: 'Allow NTP time synchronization'
                }
            },
            {
                name: 'Backup to Cloud (Outbound)',
                description: 'Allow backup traffic to cloud storage',
                platforms: ['all'],
                direction: 'out',
                category: 'outbound',
                rule: {
                    name: 'Allow Outbound Backup',
                    action: 'allow',
                    protocol: 'tcp',
                    direction: 'out',
                    source: { type: 'any' },
                    destination: { type: 'any', port: '443' },
                    comment: 'Allow HTTPS backup to cloud providers'
                }
            },
            
            // Bidirectional Templates
            {
                name: 'Site-to-Site VPN',
                description: 'Allow VPN tunnel between sites',
                platforms: ['all'],
                direction: 'both',
                category: 'bidirectional',
                rule: {
                    name: 'Site-to-Site VPN Tunnel',
                    action: 'allow',
                    protocol: 'udp',
                    direction: 'both',
                    source: { type: 'ip', value: '203.0.113.10' },
                    destination: { type: 'ip', value: '198.51.100.20', port: '500,4500' },
                    comment: 'IPSec VPN between branch offices'
                }
            },
            {
                name: 'Database Replication',
                description: 'Allow database sync between servers',
                platforms: ['all'],
                direction: 'both',
                category: 'bidirectional',
                rule: {
                    name: 'Database Replication',
                    action: 'allow',
                    protocol: 'tcp',
                    direction: 'both',
                    source: { type: 'ip', value: '10.0.1.10' },
                    destination: { type: 'ip', value: '10.0.2.10', port: '3306' },
                    comment: 'MySQL replication between primary and secondary'
                }
            },
            
            // Security Templates
            {
                name: 'Block All (Default Deny)',
                description: 'Deny all traffic not explicitly allowed',
                platforms: ['all'],
                direction: 'both',
                category: 'security',
                rule: {
                    name: 'Default Deny All',
                    action: 'deny',
                    protocol: 'any',
                    direction: 'both',
                    source: { type: 'any' },
                    destination: { type: 'any' },
                    logging: true,
                    comment: 'Log and drop all unmatched traffic'
                }
            },
            {
                name: 'Block Malicious IPs',
                description: 'Block known malicious IP addresses',
                platforms: ['all'],
                direction: 'in',
                category: 'security',
                rule: {
                    name: 'Block Malicious Sources',
                    action: 'deny',
                    protocol: 'any',
                    direction: 'in',
                    source: { type: 'network', value: '192.0.2.0/24' },
                    destination: { type: 'any' },
                    logging: true,
                    comment: 'Block traffic from blacklisted IPs'
                }
            }
        ];
    }

    updatePlatformUI(platform) {
        // Platform-specific UI updates
        // Direction is now always visible in the main interface
        // Could add platform-specific hints or validations here in the future
        
        // Update direction indicators on platform change
        this.updateDirectionIndicators(document.getElementById('ruleDirection').value);
    }
    
    updateDirectionIndicators(direction) {
        const directionText = document.getElementById('directionText');
        const sourceIcon = document.getElementById('sourceIcon');
        const destIcon = document.getElementById('destIcon');
        const sourceLabel = document.getElementById('sourceLabel');
        const destLabel = document.getElementById('destLabel');
        const sourceHint = document.getElementById('sourceHint');
        const destHint = document.getElementById('destHint');
        const sourceCard = document.getElementById('sourceCard');
        const destCard = document.getElementById('destinationCard');
        
        // Remove existing border classes
        sourceCard.classList.remove('border-primary', 'border-success', 'border-info');
        destCard.classList.remove('border-primary', 'border-success', 'border-info');
        
        switch (direction) {
            case 'in':
                directionText.innerHTML = 'Configuring an <strong>Inbound</strong> rule: Traffic flows from external Source to local Destination';
                sourceIcon.className = 'fas fa-globe me-2';
                destIcon.className = 'fas fa-server me-2';
                sourceLabel.textContent = 'Source';
                destLabel.textContent = 'Destination';
                sourceHint.textContent = '(External/Remote)';
                destHint.textContent = '(Local/Protected)';
                sourceCard.classList.add('border-primary');
                destCard.classList.add('border-success');
                break;
                
            case 'out':
                directionText.innerHTML = 'Configuring an <strong>Outbound</strong> rule: Traffic flows from local Source to external Destination';
                sourceIcon.className = 'fas fa-server me-2';
                destIcon.className = 'fas fa-globe me-2';
                sourceLabel.textContent = 'Source';
                destLabel.textContent = 'Destination';
                sourceHint.textContent = '(Local/Internal)';
                destHint.textContent = '(External/Remote)';
                sourceCard.classList.add('border-success');
                destCard.classList.add('border-primary');
                break;
                
            case 'both':
                directionText.innerHTML = 'Configuring a <strong>Bidirectional</strong> rule: Traffic flows in both directions between Source and Destination';
                sourceIcon.className = 'fas fa-exchange-alt me-2';
                destIcon.className = 'fas fa-exchange-alt me-2';
                sourceLabel.textContent = 'Endpoint A';
                destLabel.textContent = 'Endpoint B';
                sourceHint.textContent = '(First endpoint)';
                destHint.textContent = '(Second endpoint)';
                sourceCard.classList.add('border-info');
                destCard.classList.add('border-info');
                break;
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
    
    // Real-time field validation
    validateField(fieldId) {
        const element = document.getElementById(fieldId);
        if (!element) return;
        
        let isValid = true;
        let errorMsg = '';
        
        switch (fieldId) {
            case 'sourceValue':
                if (this.ruleData.source.type === 'ip' && element.value) {
                    isValid = this.isValidIP(element.value);
                    errorMsg = 'Invalid IP address format';
                } else if (this.ruleData.source.type === 'network' && element.value) {
                    isValid = this.isValidCIDR(element.value);
                    errorMsg = 'Invalid CIDR format (e.g., 192.168.1.0/24)';
                } else if (this.ruleData.source.type === 'zone' && element.value) {
                    isValid = this.isValidZone(element.value);
                    errorMsg = 'Invalid zone name';
                }
                break;
                
            case 'destValue':
                if (this.ruleData.destination.type === 'ip' && element.value) {
                    isValid = this.isValidIP(element.value);
                    errorMsg = 'Invalid IP address format';
                } else if (this.ruleData.destination.type === 'network' && element.value) {
                    isValid = this.isValidCIDR(element.value);
                    errorMsg = 'Invalid CIDR format (e.g., 10.0.0.0/8)';
                } else if (this.ruleData.destination.type === 'zone' && element.value) {
                    isValid = this.isValidZone(element.value);
                    errorMsg = 'Invalid zone name';
                }
                break;
                
            case 'sourcePort':
            case 'destPort':
                if (element.value && element.value !== 'any') {
                    isValid = this.isValidPort(element.value);
                    errorMsg = 'Invalid port format (e.g., 80, 80-443, or 80,443)';
                }
                break;
        }
        
        // Update UI based on validation
        if (!isValid && element.value) {
            element.classList.add('is-invalid');
            // Create or update error message
            let feedback = element.nextElementSibling;
            if (!feedback || !feedback.classList.contains('invalid-feedback')) {
                feedback = document.createElement('div');
                feedback.className = 'invalid-feedback';
                element.parentNode.insertBefore(feedback, element.nextSibling);
            }
            feedback.textContent = errorMsg;
        } else {
            element.classList.remove('is-invalid');
            // Remove error message if exists
            const feedback = element.nextElementSibling;
            if (feedback && feedback.classList.contains('invalid-feedback')) {
                feedback.remove();
            }
        }
        
        return isValid;
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
        document.getElementById('ruleDirection').value = 'in';  // Default to inbound
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
            direction: 'in',  // Default to inbound
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