# CLAUDE.md

This file provides guidance to Claude Code (claude.ai/code) when working with code in this repository.

## Project Overview

This repository contains a personal website called ZeroNexus, which serves as a digital portfolio for Adam Hunt. The site includes:

- A main landing page with social links and Bluesky feed integration
- A security news aggregator page
- A creative showcase for artwork and games
- Simple API endpoints for security-related information

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
   - No database - uses mockups or proxies to external services

## Development Setup

### Starting the Development Environment

To start the development environment:

```bash
# Start the containers
docker-compose up -d

# View logs
docker-compose logs -f
```

The site will be available at http://localhost:8081

### Stopping the Development Environment

```bash
docker-compose down
```

## Project Structure

- `docker-compose.yml` - Container configuration
- `logs/` - Log files directory
- `website/` - Main website content
  - `api/` - Backend PHP API endpoints
  - `artwork/` - Images and artwork for the showcase
  - Various HTML and PHP files for the different pages

## Key Files

- `website/index.html` - Main landing page
- `website/security-news.html` - Security news aggregator
- `website/showcase.php` - Creative portfolio page
- `website/api/feeds.php` - Security news feed API
- `website/api/cve-proxy.php` - CVE information proxy API
- `website/api/cve-search.php` - CVE search API

## Common Tasks

### Adding New Artwork

1. Add the image file to the `website/artwork/` directory
2. Optionally create a matching JSON file with the same name to add metadata

### Updating the API

The API endpoints in `website/api/` are simple PHP files that can be modified directly.

### Frontend Changes

The website uses Bootstrap 5.3 with custom CSS. Most style rules are embedded directly in the HTML files.