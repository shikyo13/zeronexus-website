# ZeroNexus Website

[![Code Quality](https://github.com/shikyo13/zeronexus-website/actions/workflows/code-quality.yml/badge.svg)](https://github.com/shikyo13/zeronexus-website/actions/workflows/code-quality.yml)

Personal website for Adam Hunt, featuring a portfolio showcase, security news feed, and social media integration.

## Features

- **Landing Page**: Personal bio and Bluesky feed integration
- **Security News Feed**: Aggregated security news from popular sources
- **Creative Showcase**: Portfolio displaying artwork and games
- **API**: Simple endpoints for security-related information

## Tech Stack

- **Nginx**: Web server and reverse proxy
- **PHP-FPM**: Processing PHP files
- **Docker**: Containerization for easy deployment
- **Bootstrap 5**: Frontend framework
- **Cloudflare**: CDN and tunnel for secure access

## Development Setup

### Prerequisites

- Docker and Docker Compose
- Git
- Code editor of your choice

### Local Development

1. Clone the repository:
   ```bash
   git clone https://github.com/shikyo13/zeronexus-website.git
   cd zeronexus-website
   ```

2. Start the development environment:
   ```bash
   docker-compose up -d
   ```

3. Access the site at http://localhost:8081

### Stopping the Environment

```bash
docker-compose down
```

## Project Structure

- `docker-compose.yml` - Container configuration
- `nginx/` - Nginx configuration files
- `website/` - Main website content
  - `api/` - Backend PHP API endpoints
  - `css/` - Stylesheet files
  - `js/` - JavaScript files
  - `includes/` - Reusable PHP components
  - `artwork/` - Media files for showcase

## Deployment

The site is automatically deployed to production when changes are pushed to the main branch using GitHub Actions.

## License

All rights reserved. This source code is private and not for distribution.

## Contact

Adam Hunt - [Bluesky](https://bsky.app/profile/adamahunt.bsky.social) | [GitHub](https://github.com/shikyo13) | [The IT Guy KC](https://theitguykc.com)