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
| DNS Lookup Tool | Planned | |
| Security Headers Checker | Planned | High priority for next implementation |
| Firewall Rule Generator | Planned | High priority for next implementation |
| Command Cheat Sheets | Planned | |
| OSI Model Reference | Planned | |
| Common Ports Reference | Planned | |
| HTTP Status Codes | Planned | |
| Binary/Hex/Decimal Converter | Planned | |
| Documentation Templates | Planned | |

## File Structure

- `website/network-admin.php` - Main page with tab structure and tool cards
- `website/css/network-admin.css` - Styles for the Network Admin Tools page
- `website/js/network-admin.js` - JavaScript functionality for tools

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

## Future Implementations

### Security Headers Checker

The Security Headers Checker will analyze website security headers and provide recommendations. Planned features:

- Input field for website URL
- Analysis of security headers (CSP, HSTS, X-Frame-Options, etc.)
- Security score and recommendations
- Comparison with best practices

### Firewall Rule Generator

The Firewall Rule Generator will help create rules for various firewall implementations. Planned features:

- Support for multiple firewall types (iptables, UFW, Windows Firewall)
- Common rule templates for various services
- Custom rule generation based on user inputs
- Rule validation and syntax highlighting

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