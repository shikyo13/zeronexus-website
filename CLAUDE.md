# CLAUDE.md

This file provides guidance to Claude Code (claude.ai/code) when working with code in this repository.

## Project Overview

This repository contains a personal website called ZeroNexus, which serves as a digital portfolio for Adam Hunt. The site includes:

- A main landing page with social links and Bluesky feed integration
- A security news aggregator page
- A creative showcase for artwork and games
- Simple API endpoints for security-related information
- A CVE dashboard for searching and displaying vulnerability information

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

## Development Setup

### Development Workflow

The development workflow is:

1. Develop and test code on the MacBook
2. Push completed changes to the production Ubuntu VM via SSH
3. Restart containers on the VM to apply changes

There is no local development environment with containers - code is edited locally and then deployed directly to production.

### Production Environment Commands

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
```

## Project Structure

- `docker-compose.yml` - Production container configuration
- `docker-compose.dev.yml` - Development container configuration with extended debugging
- `nginx/` - Nginx configuration files
  - `nginx.conf` - Global Nginx settings
  - `conf.d/default.conf` - Website-specific server configuration with CSP settings
- `website/` - Main website content
  - `api/` - Backend PHP API endpoints
  - `artwork/` - Images and artwork for the showcase
  - `css/` - Stylesheet files
  - `js/` - JavaScript files
  - `includes/` - Reusable PHP components (header.php, footer.php)
  - `img/` - Website images organized by category

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
- `docs/SITE_DOCUMENTATION.md` - Comprehensive site documentation
- `docs/NETWORK_ADMIN_TOOLS.md` - Network Admin Tools documentation
- `docs/HANDOVER.md` - Handover documentation for development sessions

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

5. **Article Image Extraction** (`/api/article-image.php`)
   - Extracts featured images from article URLs
   - Required parameters: `url`
   - Optional parameters: `source` for site-specific extraction
   - Example: `/api/article-image.php?url=https://example.com/article&source=bleepingcomputer`

## Content Security Policy (CSP)

The website implements a strict Content Security Policy, particularly for Bluesky video content. When modifying JavaScript or embedding external content, ensure that:

1. The domain is whitelisted in the CSP in `/nginx/conf.d/default.conf`
2. Media sources are properly defined for video content
3. Inline scripts use the appropriate CSP policies

## Common Tasks

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

## Deployment

The site is deployed manually to production:

1. Code updates are pushed via SSH to the production Ubuntu 22.04 LTS VM running in Oracle VirtualBox
2. The VM runs Docker containers with the production code
3. After updates, containers need to be restarted: `ssh user@vm-ip "cd /path/to/site && docker-compose restart"`
4. The VM is configured with a bridged network adapter to the host Windows 11 machine
5. Cloudflare Tunnel runs on the Windows host to provide secure access:
   - The local environment listens for HTTP requests
   - Cloudflare handles SSL redirects and certificates