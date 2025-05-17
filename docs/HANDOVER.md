# ZeroNexus Website Handover Document

This document provides essential information for continuing development on the ZeroNexus website project in future sessions.

## Project State Summary

The ZeroNexus website is a personal website for Adam Hunt, featuring:

1. A main landing page with social media integration (complete)
2. A security news aggregator (complete)
3. A creative showcase portfolio (complete)
4. A CVE dashboard for security information (complete)
5. A new Network Admin Tools page (partially complete)

### Recent Developments

- Added a new Network Admin Tools page with tab-based navigation
- Implemented the IP Subnet Calculator tool with comprehensive functionality
- Created a git branch structure for feature development
- Added documentation for the site and specific components

## Git Repository Status

- **Main Branch**: Contains the stable, production-ready code
- **Network Admin Tools Branch**: Contains the new tools page implementation
- **Backup Archives**: ZIP archives of code snapshots in the `backups/` directory

## Current Tasks Status

### Completed Tasks

- [x] Create Network Admin Tools page base structure
- [x] Implement tab-based navigation
- [x] Create card-based tool layout
- [x] Implement the IP Subnet Calculator tool
- [x] Add site to header navigation
- [x] Create documentation

### In-Progress Tasks

- [ ] Security Headers Checker implementation (high priority)
- [ ] Firewall Rule Generator implementation (high priority)
- [ ] Command Cheat Sheets (interactive reference)

### Planned Tasks

- [ ] Implement DNS Lookup Tool
- [ ] Create OSI Model Reference
- [ ] Develop Common Ports Reference
- [ ] Implement HTTP Status Codes Guide
- [ ] Build Binary/Hex/Decimal Converter
- [ ] Create documentation templates

## Special Considerations

### Deployment Model

The website is deployed on a Ubuntu VM with code updates done manually via SSH. The VM runs Docker containers for Nginx and PHP. A Cloudflare Tunnel running on the Windows host machine provides public access with SSL.

### Local Development

A Docker-based development environment is now available:
- Run on `http://localhost:8082` using `docker-compose -f docker-compose.dev.yml up -d`
- **Important**: PHP changes require container restart (`docker-compose -f docker-compose.dev.yml restart php`)
- The feeds API (`feeds.zeronexus.net`) is external - local feeds.php proxies to it in development
- See `docs/LOCAL_DEVELOPMENT.md` and `docs/DEV_QUICK_REFERENCE.md` for details

### Code Organization

- **File Naming**: Follows standard conventions with descriptive names
- **CSS Structure**: Each page has its own CSS file, with common styles in `base.css`
- **JavaScript**: Modular approach with separate files for each page
- **PHP Includes**: Common elements in the `includes/` directory

## High-Priority Next Steps

1. **Complete Security Headers Checker Tool**:
   - Create UI in existing modal
   - Implement header fetching functionality
   - Add header analysis and recommendations

2. **Implement Firewall Rule Generator**:
   - Design rule builder interface
   - Implement rule generation for different firewall types
   - Add validation and syntax highlighting

3. **Create Interactive Command Cheat Sheets**:
   - Design interactive UI for command references
   - Implement filtering and search
   - Add copyable command examples

## Technical Debt and Known Issues

### Testing

- No formal testing framework is in place
- Testing is done manually after deployment
- Consider implementing simple test cases for critical functions

### Documentation

- Core functionality is documented in the newly added `docs/` directory
- More comprehensive inline code documentation would be beneficial
- Consider adding JSDoc comments for JavaScript functions

### Security Considerations

- API endpoints have rate limiting, but some could benefit from additional validation
- File-based caching system is functional but could be more robust
- CSP is properly configured but should be reviewed when adding new features

## Resources and References

### Source Code Locations

- **GitHub**: Private repository (not public)
- **Production**: Ubuntu VM via SSH access

### External Dependencies

- **Bootstrap 5.3**: CSS framework used throughout the site
- **Font Awesome**: Icon library
- **Bluesky Embed**: Used for social media integration

### Documentation

- **Site Documentation**: `docs/SITE_DOCUMENTATION.md`
- **Network Admin Tools**: `docs/NETWORK_ADMIN_TOOLS.md`
- **CLAUDE.md**: Guidelines for Claude Code AI interaction

## Contact Information

- **Developer**: Adam Hunt
- **Bluesky**: adamahunt.bsky.social
- **Website**: theitguykc.com

## Session Notes

### Last Session (May 13, 2025)

- Implemented Network Admin Tools page
- Created IP Subnet Calculator tool
- Added documentation and set up git branching
- Created comprehensive documentation for the site

### Goals for Next Session

- Complete Security Headers Checker tool
- Implement Firewall Rule Generator
- If time permits, begin Command Cheat Sheets implementation