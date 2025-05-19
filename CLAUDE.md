# CLAUDE.md

This file provides guidance to Claude Code (claude.ai/code) when working with code in this repository.

**Repository**: https://github.com/shikyo13/zeronexus-website
**Live Site**: https://zeronexus.net

## Project Overview

This repository contains a personal website called ZeroNexus, which serves as a digital portfolio for Adam Hunt. The site includes:

- A main landing page with social links and Bluesky feed integration
- A security news aggregator page 
- A creative showcase for artwork and games
- Simple API endpoints for security-related information
- A CVE dashboard for searching and displaying vulnerability information
- Network Admin Tools for IT professionals

## Architecture

The website uses a simple architecture:

1. **Docker Containers**: 
   - An Nginx Alpine container serves static content
   - A PHP-FPM container processes PHP files
   - Docker Compose orchestrates these services

2. **Frontend**:
   - Static HTML/CSS/JS with Bootstrap 5.3
   - Custom JavaScript for special features like the showcase filter
   - Bluesky Embed for displaying social posts

3. **API Backend**:
   - Simple PHP endpoints for security news feeds and CVE lookups
   - No database - uses file-based caching and proxies to external services
   - Rate limiting implementation through temporary files
   - **Important**: The feeds API at `feeds.zeronexus.net` is a separate service not included in this repository
   - In development, the local feeds.php proxies to the production feeds API to get real article data

## Development Commands

### Local Development

**Initial Setup:**
```bash
# First time setup (requires Docker Desktop)
chmod +x scripts/setup-dev.sh
./scripts/setup-dev.sh
```

**Regular Commands:**
```bash
# Start the local development environment
docker-compose -f docker-compose.dev.yml up -d

# Access the site at http://localhost:8082

# View logs for local development
docker-compose -f docker-compose.dev.yml logs -f

# Stop the local environment
docker-compose -f docker-compose.dev.yml down

# View PHP error logs
tail -f logs/php/error.log

# Enter PHP container
docker-compose -f docker-compose.dev.yml exec php sh

# Restart PHP container after making PHP changes (required!)
docker-compose -f docker-compose.dev.yml restart php
```

**Note:** The development environment uses `nginx/conf.d/dev.conf` instead of `default.conf` for relaxed security settings suitable for local testing. See `docs/LOCAL_DEVELOPMENT.md` for complete documentation.

### Production Commands

These commands are executed on the production VM after SSH connection:

```bash
# Check Docker container status
docker-compose ps

# Restart a specific service
docker-compose restart zeronexus

# View logs for a specific service
docker-compose logs -f zeronexus

# Execute commands in the PHP container
docker-compose exec php sh

# Rebuild containers after config changes
docker-compose up -d --build

# Stop all containers
docker-compose down

# Deploy code updates to production
ssh user@vm-ip "cd /path/to/site && docker-compose restart"
```

### Git Workflow

**Current Branch Structure:**
- `main` - Production branch
- `develop` - Active development branch
- `network-admin-tools` - Feature branch for Network Admin Tools page

```bash
# Create a new feature branch from develop
git checkout develop
git pull origin develop
git checkout -b feature-name

# Check status of working files
git status

# Add modified files
git add file1 file2

# Commit changes
git commit -m "Description of changes"

# Push feature branch
git push origin feature-name

# Create pull request to develop branch
# (Use GitHub UI or gh CLI)
```

## Testing and Validation

The project uses manual testing with no formal testing framework:

1. Test API endpoints directly in the browser or using curl/Postman
2. Validate JavaScript and CSS using browser dev tools
3. Check responsive design across different screen sizes
4. Validate PHP by checking error logs: `docker-compose exec php cat /var/log/php-fpm/error.log`
5. Lint and typecheck commands (if provided by user): 
   - Ask user for specific commands if not in documentation
   - Suggest adding them to CLAUDE.md for future reference

## Project Structure

- `docker-compose.yml` - Production container configuration
- `docker-compose.dev.yml` - Development container configuration with extended debugging
- `nginx/` - Nginx configuration files
  - `nginx.conf` - Global Nginx settings
  - `conf.d/default.conf` - Website-specific server configuration with CSP settings
  - `conf.d/dev.conf` - Development configuration with relaxed security
- `website/` - Main website content
  - `api/` - Backend PHP API endpoints
  - `artwork/` - Images and artwork for the showcase
  - `css/` - Stylesheet files
  - `js/` - JavaScript files
  - `includes/` - Reusable PHP components (header.php, footer.php)
  - `img/` - Website images organized by category
  - `components/` - Network Admin Tools components structure
- `docs/` - Project documentation
  - `SITE_DOCUMENTATION.md` - Comprehensive site documentation
  - `NETWORK_ADMIN_TOOLS.md` - Network Admin Tools documentation
  - `HANDOVER.md` - Handover documentation for development sessions
  - `GIT_WORKFLOW.md` - Git branching strategy guide
  - `DEV_QUICK_REFERENCE.md` - Quick development commands
  - `LOCAL_DEVELOPMENT.md` - Detailed local setup guide

## Key Files

- `website/index.php` - Main landing page
- `website/security-news.php` - Security news aggregator
- `website/showcase.php` - Creative portfolio page
- `website/cve-dashboard.php` - CVE search and dashboard interface
- `website/network-admin.php` - Network Admin Tools page
- `website/api/feeds.php` - Security news feed API
- `website/api/cve-proxy.php` - CVE information proxy API
- `website/api/cve-search.php` - CVE search API
- `website/api/cisa-kev.php` - CISA Known Exploited Vulnerabilities API
- `website/api/article-image.php` - Image extraction API for articles
- `website/api/mitre-cve.php` - MITRE CVE information API
- `website/api/year-search.php` - CVE year search implementation
- `website/api/network-tools.php` - Network diagnostic tools API
- `website/api/dns-lookup.php` - DNS query functionality
- `website/api/security-headers.php` - Security headers analysis API

## API Endpoints

The website provides several API endpoints:

1. **Feeds API** (`/api/feeds.php`)
   - Returns security news from multiple sources
   - Optional parameters: `page`, `limit`, `source`
   - Example: `/api/feeds.php?source=bleepingcomputer&page=1`

2. **CVE Proxy** (`/api/cve-proxy.php`)
   - Proxies requests to the NVD API for CVE details
   - Required parameters: `id` (CVE ID)
   - Example: `/api/cve-proxy.php?id=CVE-2023-12345`

3. **CVE Search** (`/api/cve-search.php`)
   - Search for CVEs by ID, year, keyword, vendor or severity
   - Parameters: `id`, `year`, `keyword`, `vendor`, `severity`, or `recent=true`
   - Example: `/api/cve-search.php?year=2023` or `/api/cve-search.php?vendor=microsoft&severity=critical`

4. **CISA KEV** (`/api/cisa-kev.php`)
   - Retrieves Known Exploited Vulnerabilities from CISA
   - Optional parameters: `id` for specific CVE lookup
   - Example: `/api/cisa-kev.php` or `/api/cisa-kev.php?id=CVE-2023-12345`

5. **Network Tools** (`/api/network-tools.php`)
   - Executes network diagnostic tools (ping, traceroute, mtr)
   - Required parameters: `host` (domain name or IP address)
   - Optional parameters: `tool` (ping, traceroute, mtr), `packetCount`, `packetSize`, `timeout`
   - Includes rate limiting and security measures
   - Example: `/api/network-tools.php?host=example.com&tool=ping&packetCount=4`

6. **Article Image Extraction** (`/api/article-image.php`)
   - Extracts featured images from article URLs
   - Required parameters: `url`
   - Optional parameters: `source` for site-specific extraction
   - Example: `/api/article-image.php?url=https://example.com/article&source=bleepingcomputer`

7. **DNS Lookup** (`/api/dns-lookup.php`)
   - Performs DNS queries for domain records
   - Required parameters: `host` (domain name)
   - Optional parameters: `type` (A, AAAA, MX, TXT, NS, CNAME, etc.)
   - Example: `/api/dns-lookup.php?host=example.com&type=A`

8. **Security Headers Check** (`/api/security-headers.php`)
   - Analyzes HTTP security headers for websites
   - Required parameters: `url` (website URL)
   - Returns analysis of security headers and recommendations
   - Example: `/api/security-headers.php?url=https://example.com`

## Content Security Policy (CSP)

The website implements a strict Content Security Policy, particularly for Bluesky video content. When modifying JavaScript or embedding external content, ensure that:

1. The domain is whitelisted in the CSP in `/nginx/conf.d/default.conf`
2. Media sources are properly defined for video content
3. Inline scripts use the appropriate CSP policies

## Common Tasks

### Network Admin Tools

The Network Admin Tools page provides several IT and networking utilities:

1. **Diagnostic Tools**
   - IP Subnet Calculator - Calculate subnet information from CIDR notation
   - DNS Lookup - Query and visualize DNS records
   - Ping/Traceroute/MTR - Network diagnostic tools with visualization

2. **Security Tools**
   - Security Headers Checker - Analyze website security headers
   - Security Headers Generator - Create custom security headers for web servers

3. **Usage**
   - Tools can be directly linked using URL hash fragments
   - Example: `network-admin.php#ping-traceroute?host=example.com&tool=mtr&autorun=true`
   - Tool results can be visualized and exported
   - API endpoints are rate-limited to prevent abuse

### Adding New Artwork

1. Add the image file to the `website/artwork/` directory
2. Optionally create a matching JSON file with the same name to add metadata, for example:
   ```json
   {
     "title": "Artwork Title",
     "description": "Description of the artwork",
     "tags": ["Art", "Digital", "Portrait"]
   }
   ```

### Updating the API

The API endpoints in `website/api/` are simple PHP files that can be modified directly. When making changes:

1. Ensure CORS headers remain intact
2. Maintain rate limiting implementations
3. Keep domain allowlists updated for security
4. Preserve caching mechanisms where implemented

### Frontend Changes

The website uses Bootstrap 5.3 with custom CSS. Most style rules are in:
- `website/css/base.css` - Common styles across the site
- `website/css/index.css` - Landing page styles
- `website/css/security-news.css` - News feed styles
- `website/css/showcase.css` - Portfolio styles
- `website/css/cve-dashboard.css` - CVE dashboard styles
- `website/css/network-admin.css` - Network Admin Tools styles

## Current Development Focus

According to the latest handover document (`docs/HANDOVER.md`), the following tasks are high priority:

1. Complete the Security Headers Checker tool
2. Implement Firewall Rule Generator 
3. Create interactive Command Cheat Sheets

## Cache Implementation

The API endpoints implement file-based caching:
- Cache directories use `sys_get_temp_dir()` for storage
- Cache keys are typically MD5 hashes of request parameters
- Cache lifetimes vary by endpoint (typically 1-24 hours)
- Rate limiting uses similar mechanisms with short timeouts

## Debugging

To debug issues:
- Check the Docker logs for PHP errors: `docker-compose logs -f php`
- PHP error logs are available in the container: `docker-compose exec php cat /var/log/php-fpm/error.log`
- Use the development environment with extended debugging: `docker-compose -f docker-compose.dev.yml up -d`
- The dev environment enables PHP error display and increases memory limits
- For JavaScript debugging, use the browser's developer tools console
- PHP error reporting can be enabled by adding `ini_set('display_errors', 1);` to specific files during development

### Quick Debugging Commands:
```bash
# View PHP error log (development)
tail -f logs/php/error.log

# Test API endpoints
curl http://localhost:8082/api/feeds.php
curl "http://localhost:8082/api/article-image.php?url=ENCODED_URL"

# Container logs
docker-compose -f docker-compose.dev.yml logs -f
```

## Deployment

The site is deployed manually to production:

1. Code updates are pushed via SSH to the production Ubuntu 22.04 LTS VM running in Oracle VirtualBox
2. The VM runs Docker containers with the production code
3. After updates, containers need to be restarted: `ssh user@vm-ip "cd /path/to/site && docker-compose restart"`
4. The VM is configured with a bridged network adapter to the host Windows 11 machine
5. Cloudflare Tunnel runs on the Windows host to provide secure access:
   - The local environment listens for HTTP requests
   - Cloudflare handles SSL redirects and certificates

## Development Best Practices

1. **Always restart PHP container after PHP changes**
2. Use the development environment for testing before production deployment
3. Check error logs frequently during development
4. Test API endpoints with curl or browser before integration
5. Validate JavaScript changes with browser dev tools
6. Keep CORS headers updated when adding new JavaScript functionality
7. Document any new API endpoints in this file
8. Follow the Git workflow for feature development