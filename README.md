# ZeroNexus Website

[![GitHub](https://img.shields.io/github/stars/shikyo13/zeronexus-website?style=social)](https://github.com/shikyo13/zeronexus-website)
[![License](https://img.shields.io/badge/license-MIT-blue)](LICENSE)
[![Docker](https://img.shields.io/badge/docker-ready-brightgreen)](https://docs.docker.com/)

Personal portfolio and security tools website for Adam Hunt.

**Repository**: https://github.com/shikyo13/zeronexus-website
**Live Site**: https://zeronexus.net

## Quick Start

### Clone the Repository

```bash
git clone https://github.com/shikyo13/zeronexus-website.git
cd zeronexus-website
```

### Development Environment

```bash
# Setup (first time only)
chmod +x scripts/setup-dev.sh
./scripts/setup-dev.sh

# Start development
docker-compose -f docker-compose.dev.yml up -d

# Access site
open http://localhost:8082

# Stop development
docker-compose -f docker-compose.dev.yml down
```

## Key Information

- **PHP changes require container restart**: `docker-compose -f docker-compose.dev.yml restart php`
- The security feeds API (`feeds.zeronexus.net`) is external to this repository
- See `docs/DEV_QUICK_REFERENCE.md` for common commands
- Full documentation in `docs/LOCAL_DEVELOPMENT.md`

## Project Structure

```
zeronexus-website/
├── website/           # Main website files
│   ├── api/          # PHP API endpoints
│   ├── css/          # Stylesheets
│   ├── js/           # JavaScript files
│   └── includes/     # PHP includes
├── nginx/            # Nginx configuration
├── docker-compose.yml # Production config
├── docker-compose.dev.yml # Development config
└── docs/             # Documentation
```

## Documentation

- [Local Development Guide](docs/LOCAL_DEVELOPMENT.md)
- [Quick Reference](docs/DEV_QUICK_REFERENCE.md)
- [Git Workflow](docs/GIT_WORKFLOW.md)
- [Site Documentation](docs/SITE_DOCUMENTATION.md)
- [Network Admin Tools](docs/NETWORK_ADMIN_TOOLS.md)
- [Development Handover](docs/HANDOVER.md)