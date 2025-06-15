# Network Admin Tools Documentation

This document provides detailed information about the Network Admin Tools page implementation in the ZeroNexus website.

## Overview

The Network Admin Tools page provides a collection of utilities and references for network administrators and IT professionals. It includes:

- Diagnostic Tools
- Security Tools
- Command References
- Reference Guides
- Calculators & Converters
- Documentation Templates

## Implementation Status

| Tool | Status | Notes |
|------|--------|-------|
| IP Subnet Calculator | Complete | Fully functional with validation and error handling |
| DNS Lookup Tool | Complete | DNS records lookup with visualization |
| Ping/Traceroute/MTR | Complete | Network diagnostic tools with rate limiting |
| Security Headers Checker | Complete | Analyzes website security headers |
| Security Headers Generator | Complete | Generates custom security headers for web servers |
| Password Strength Tester | Complete | Real-time password analysis and secure generation |
| Firewall Rule Generator | Complete | Multi-platform firewall rule generation |
| Command Cheat Sheets | Planned | |
| OSI Model Reference | Planned | |
| Common Ports Reference | Planned | |
| HTTP Status Codes | Planned | |
| Binary/Hex/Decimal Converter | Planned | |
| Documentation Templates | Planned | |

## File Structure

### Main Files
- `website/network-admin.php` - Main page entry point
- `website/components/network-admin/main.php` - Main component with tab structure
- `website/css/network-admin.css` - Styles for the Network Admin Tools page
- `website/js/network-admin.js` - Main JavaScript module loader

### Component Structure
- `website/components/network-admin/tabs/` - Tab content files
  - `security.php` - Security tools tab
  - `diagnostics.php` - Diagnostic tools tab
  - Other tab files...
- `website/components/network-admin/modals/` - Tool modal dialogs
  - `subnet-calculator.php` - IP Subnet Calculator modal
  - `dns-lookup.php` - DNS Lookup modal
  - `ping-traceroute.php` - Ping/Traceroute/MTR modal
  - `security-headers.php` - Security Headers Checker modal
  - `security-headers-generator.php` - Security Headers Generator modal
  - `password-strength.php` - Password Strength Tester & Generator modal

### JavaScript Modules
- `website/js/network-admin/tools/` - Individual tool implementations
  - `subnet-calculator.js` - Subnet calculation logic
  - `dns-lookup.js` - DNS lookup functionality
  - `ping-traceroute.js` - Network diagnostic tools
  - `security-headers.js` - Security headers analysis
  - `security-headers-generator.js` - Security headers generation
  - `password-strength.js` - Password analysis and generation

## IP Subnet Calculator Implementation

The IP Subnet Calculator tool provides comprehensive subnet calculation functionality:

### Features

- Accepts both CIDR notation (/24) and traditional subnet masks (255.255.255.0)
- Calculates network address, broadcast address, usable hosts, etc.
- Handles special cases like /31 and /32 networks
- Provides error validation with user-friendly error messages
- Includes copy-to-clipboard functionality

### Technical Implementation

The subnet calculator is implemented using client-side JavaScript with the following key functions:

- `setupSubnetCalculator()` - Main setup function that attaches event handlers
- `calculateSubnet()` - Core calculation function
- `cidrToSubnetMask()` - Converts CIDR prefix to subnet mask
- `subnetMaskToCidr()` - Converts subnet mask to CIDR prefix
- `isValidIpAddress()` - Validates IP address format
- `isValidSubnetMask()` - Validates subnet mask format
- `determineNetworkClass()` - Returns network class based on first octet

## Password Strength Tester & Generator Implementation

The Password Strength Tester & Generator provides comprehensive password analysis and secure password generation functionality:

### Features

#### Password Strength Testing
- Real-time password strength analysis as you type
- Entropy-based strength calculation
- Visual strength meter with color-coded feedback
- Detailed analysis including:
  - Password length
  - Entropy in bits
  - Character set size
  - Estimated time to crack
- Pattern detection for common weak passwords
- Optional advanced pattern detection (can be enabled with checkbox)
- All analysis done client-side for security

#### Password Generator
- Cryptographically secure password generation using Web Crypto API
- Customizable password options:
  - Length (8-64 characters)
  - Character types (uppercase, lowercase, numbers, symbols)
  - Character exclusion
- Generate multiple passwords at once (1, 5, or 10)
- Session-based password history
- Copy to clipboard with automatic clearing after 60 seconds

### Technical Implementation

The password tool is implemented as an ES6 module with the following key components:

- `setupPasswordStrength()` - Main initialization function
- `analyzePassword()` - Real-time password analysis
- `calculateEntropy()` - Entropy calculation based on character set
- `calculateStrengthScore()` - Overall strength scoring (0-4 scale)
- `generateSecurePassword()` - Secure password generation using crypto.getRandomValues()
- `copyToClipboard()` - Secure clipboard handling with timeout

### Security Considerations

- Uses Web Crypto API for cryptographically secure random number generation
- All processing done client-side - passwords never sent to server
- Clipboard automatically cleared after 60 seconds
- Session history stored in memory only, cleared on page reload
- No use of Math.random() for any security-critical operations

## Future Implementations

### Security Headers Checker

The Security Headers Checker will analyze website security headers and provide recommendations. Planned features:

- Input field for website URL
- Analysis of security headers (CSP, HSTS, X-Frame-Options, etc.)
- Security score and recommendations
- Comparison with best practices

### Firewall Rule Generator

The Firewall Rule Generator helps create syntactically correct firewall rules for multiple platforms. 

#### Features

- **Multi-Platform Support**: 
  - iptables (Linux)
  - pfSense
  - Cisco ASA
  - FortiGate
  - Palo Alto
  - Windows Firewall

- **Visual Rule Builder**:
  - Form-based interface with source/destination configuration
  - Protocol selection (TCP/UDP/ICMP/Any)
  - Port configuration with common service presets
  - Advanced options (interfaces, zones, logging)

- **Template System**:
  - Quick-start templates for common scenarios
  - Pre-configured rules for web servers, SSH, databases, mail servers, etc.
  - One-click template application

- **Validation & Safety**:
  - Real-time IP address and CIDR validation
  - Port range validation
  - Warnings for overly permissive rules
  - Platform-specific syntax checking

- **Advanced Features**:
  - Syntax highlighting with Prism.js
  - Copy-to-clipboard functionality
  - Platform-specific notes and tips
  - Keyboard shortcuts (Ctrl+G to generate)

#### Technical Implementation

The Firewall Rule Generator is implemented as a JavaScript class with the following key components:

- `FirewallRuleGenerator` - Main class handling all functionality
- Platform-specific generators for each firewall type
- Real-time validation system with visual feedback
- Template management system
- URL hash navigation support (#firewall-rule-generator)

## Development Guidelines

### Adding New Tools

1. Update the modal definition in `network-admin.php`
2. Add tool-specific styles to `network-admin.css` if needed
3. Implement tool functionality in `network-admin.js`
4. Update this documentation

### UI Guidelines

- All tools should follow the established card-based design
- Tools should include clear instructions and help sections
- Error validation should be user-friendly with specific error messages
- Consider both light and dark mode compatibility