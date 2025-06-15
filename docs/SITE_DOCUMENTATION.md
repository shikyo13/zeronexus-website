# ZeroNexus Website Documentation

This document provides comprehensive documentation for the ZeroNexus website, including architecture, components, and development guidelines.

## Site Architecture

The ZeroNexus website uses a simple yet effective architecture:

### Infrastructure

- **Docker Containers**: 
  - Nginx Alpine container for serving static content
  - PHP-FPM container for processing PHP files
  - Docker Compose for orchestration

### Deployment

- **Production Environment**:
  - Ubuntu 22.04 LTS VM running in Oracle VirtualBox
  - Docker containers for Nginx and PHP
  - Manual deployment via SSH
  - Cloudflare Tunnel running on Windows host machine for secure access

### Frontend

- **Technologies**:
  - HTML5/CSS3
  - Bootstrap 5.3 framework
  - Custom JavaScript for interactive features
  - Bluesky Embed integration

### Backend

- **Technologies**:
  - PHP for API endpoints and server-side processing
  - File-based caching system
  - Rate limiting implementation

## Main Pages

### Landing Page (`index.php`)

- Social media links and profile information
- Embedded Bluesky social feed
- Responsive design for multiple device sizes

### Security News (`security-news.php`)

- Aggregated security news from multiple sources
- Filtering by source
- Article thumbnails via `article-image.php`

### Creative Showcase (`showcase.php`)

- Portfolio of artwork and games
- Filtering by category
- Modal-based image viewing

### CVE Dashboard (`cve-dashboard.php`)

- Interface for searching and viewing CVE information
- Integration with NVD and CISA APIs
- Visualization of vulnerability data

### Network Admin Tools (`network-admin.php`)

- Comprehensive suite of IT administration tools
- Tab-based interface for different tool categories
- Modal-based tool implementations
- Includes multiple tools:
  - IP Subnet Calculator - CIDR calculations and network analysis
  - DNS Lookup - Forward/reverse DNS queries
  - Ping/Traceroute - Network diagnostics
  - Security Headers Checker - Website security analysis
  - Security Headers Generator - Create security configurations
  - Firewall Rule Generator - Multi-platform firewall rule creation
- See `NETWORK_ADMIN_TOOLS.md` for detailed documentation

## API Endpoints

### Feeds API (`api/feeds.php`)

- **Purpose**: Returns security news from multiple sources
- **Parameters**: 
  - `page` (default: 1) - Page number
  - `limit` (default: 30) - Items per page
  - `source` (optional) - Filter by source
- **Features**:
  - Rate limiting
  - CORS configuration
  - Pagination

### CVE Proxy (`api/cve-proxy.php`)

- **Purpose**: Proxies requests to the NVD API for CVE details
- **Parameters**:
  - `id` (required) - CVE ID to lookup
- **Features**:
  - Caching to reduce external API calls
  - Error handling

### CVE Search (`api/cve-search.php`)

- **Purpose**: Search for CVEs by various criteria
- **Parameters**:
  - `id`, `year`, `keyword`, `vendor`, `severity`, or `recent=true`
- **Features**:
  - Multiple search methods
  - Integration with `year-search.php` for year-based searches
  - Combined search capabilities (e.g., year + keyword)

### CISA KEV (`api/cisa-kev.php`)

- **Purpose**: Retrieves Known Exploited Vulnerabilities from CISA
- **Parameters**:
  - `id` (optional) - Specific CVE lookup
- **Features**:
  - Caching of CISA KEV catalog
  - Regular updates

### Article Image (`api/article-image.php`)

- **Purpose**: Extracts featured images from article URLs
- **Parameters**:
  - `url` (required) - Article URL
  - `source` (optional) - Source site for specialized extraction
- **Features**:
  - Site-specific image extraction logic
  - Caching to prevent redundant processing

## Styling and CSS

### Core Styles

- `css/base.css` - Base styles shared across the site
- `css/index.css` - Landing page styles
- `css/security-news.css` - News feed styles
- `css/showcase.css` - Portfolio page styles
- `css/cve-dashboard.css` - CVE dashboard styles
- `css/network-admin.css` - Network admin tools styles

### Key Design Elements

- Dark theme with blue accents
- Card-based content layout
- Responsive design for mobile and desktop
- Consistent icon usage (Font Awesome)

## JavaScript Components

### Common Components (`js/common.js`)

- Copyright year updating
- Common utility functions

### Security News (`js/security-news.js`)

- Fetching and rendering news articles
- Source filtering functionality
- Lazy loading of article images

### Showcase (`js/showcase.js`)

- Category filtering
- Image modal implementation
- Responsive grid handling

### CVE Dashboard (`js/cve-dashboard.js`)

- CVE search functionality
- Result visualization
- Data formatting

### Network Admin Tools (`js/network-admin.js`)

- Tab navigation logic
- Tool-specific implementations
- Modal functionality

## Reusable Components

### Header (`includes/header.php`)

- Navigation menu
- Social links (conditionally shown)
- Page metadata

### Footer (`includes/footer.php`)

- Copyright information
- Script loading

## Security Features

### Content Security Policy (CSP)

- Strict CSP implemented in Nginx configuration
- Special handling for Bluesky video content
- Whitelisted external domains

### CORS Configuration

- Configured at both the Nginx and PHP levels
- Domain allowlist for security

### Rate Limiting

- Implemented at API level for all endpoints
- IP-based limiting with Cloudflare headers support
- File-based storage mechanism

## Caching System

### Implementation

- File-based caching using `sys_get_temp_dir()`
- MD5 hash keys based on request parameters
- Configurable cache lifetimes per endpoint

### Cache Types

- API response caching
- External service responses (NVD, CISA)
- Image extraction results

## Development Workflow

### Version Control

- Git repository
- Branch-based workflow:
  - `main` branch for stable code
  - Feature branches (e.g., `network-admin-tools`) for development

### Deployment

1. Develop and test code locally
2. Commit changes to appropriate branch
3. SSH to production VM
4. Pull changes to VM
5. Restart containers if needed

### Backups

- Commits provide code history
- Additional backup archives in `backups/` directory

## Performance Considerations

### Optimization Techniques

- Content caching
- Lazy loading of images
- CSS and JavaScript minification (manual)
- Efficient API response handling

## Future Enhancements

### Planned Features

- Additional Network Admin Tools (see `NETWORK_ADMIN_TOOLS.md`)
- Enhanced CVE visualization
- Integrated search across the site
- Additional API endpoints for security information