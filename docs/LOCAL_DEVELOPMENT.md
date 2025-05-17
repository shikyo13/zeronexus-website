# Local Development Environment Setup

This guide explains how to set up and use the ZeroNexus local development environment.

## Prerequisites

1. **Docker Desktop**: Install from https://www.docker.com/products/docker-desktop
2. **Git**: Should already be installed
3. **Code Editor**: VS Code, PHPStorm, or your preferred editor

## Initial Setup

1. **Clone the repository** (if not already done):
   ```bash
   git clone https://github.com/shikyo13/zeronexus-website.git
   cd zeronexus-website
   ```

2. **Make the setup script executable**:
   ```bash
   chmod +x scripts/setup-dev.sh
   ```

3. **Run the setup script**:
   ```bash
   ./scripts/setup-dev.sh
   ```

   This script will:
   - Check for Docker installation
   - Create necessary directories
   - Start the development containers
   - Display access information

## Accessing the Development Site

- **Website**: http://localhost:8082
- **API Endpoints**: http://localhost:8082/api/

## Development Workflow

### Starting the Environment

```bash
docker-compose -f docker-compose.dev.yml up -d
```

### Stopping the Environment

```bash
docker-compose -f docker-compose.dev.yml down
```

### Viewing Logs

**All logs:**
```bash
docker-compose -f docker-compose.dev.yml logs -f
```

**PHP logs only:**
```bash
docker-compose -f docker-compose.dev.yml logs -f php
```

**Nginx logs only:**
```bash
docker-compose -f docker-compose.dev.yml logs -f zeronexus
```

**Error logs (files):**
- PHP errors: `./logs/php/error.log`
- Nginx errors: `./logs/nginx/error.log`
- Access logs: `./logs/nginx/access.log`

### Restarting Services

**IMPORTANT**: PHP changes require container restart to take effect.

```bash
# Restart all services
docker-compose -f docker-compose.dev.yml restart

# Restart just PHP (faster)
docker-compose -f docker-compose.dev.yml restart php
```

### Entering Containers

**PHP container (for running PHP commands):**
```bash
docker-compose -f docker-compose.dev.yml exec php sh
```

**Nginx container:**
```bash
docker-compose -f docker-compose.dev.yml exec zeronexus sh
```

## File Structure

```
zeronexus-website/
├── docker-compose.yml        # Production configuration
├── docker-compose.dev.yml    # Development configuration
├── nginx/
│   ├── conf.d/
│   │   ├── default.conf      # Production nginx config
│   │   └── dev.conf          # Development nginx config
│   └── nginx.conf            # Global nginx settings
├── website/                  # Website source files
├── logs/                     # Development logs (git-ignored)
│   ├── nginx/
│   └── php/
└── scripts/
    └── setup-dev.sh          # Development setup script
```

## Key Differences: Development vs Production

### Development Environment:
- Runs on port 8082 (vs 8081 in production)
- Relaxed CSP (Content Security Policy) for easier testing
- No rate limiting on API endpoints
- Full error display and logging
- No HTTPS enforcement
- Simplified CORS configuration
- Writable volumes (not read-only)

### Production Environment:
- Stricter security headers
- Rate limiting enabled
- Error display disabled
- HTTPS enforcement
- Cloudflare integration
- Read-only volumes

## Common Development Tasks

### Testing API Endpoints

The development environment has CORS configured for `http://localhost:8082`. You can test API endpoints directly:

```bash
# Test feeds API
curl http://localhost:8082/api/feeds.php

# Test CVE search
curl "http://localhost:8082/api/cve-search.php?year=2023"
```

### Modifying PHP Code

1. Edit files in `./website/`
2. **RESTART PHP CONTAINER** after changes:
   ```bash
   docker-compose -f docker-compose.dev.yml restart php
   ```
3. Check `./logs/php/error.log` for any errors

**Note**: Unlike JavaScript/CSS changes which take effect immediately, PHP changes require container restart.

### Modifying Nginx Configuration

1. Edit `./nginx/conf.d/dev.conf`
2. Restart nginx:
   ```bash
   docker-compose -f docker-compose.dev.yml restart zeronexus
   ```

### Adding PHP Extensions

To add PHP extensions, modify the `command` section in `docker-compose.dev.yml`:

```yaml
command: sh -c "
  docker-php-ext-install opcache mysqli pdo_mysql && 
  # ... rest of the command
"
```

Then rebuild:
```bash
docker-compose -f docker-compose.dev.yml up -d --build
```

## Important Development Notes

### External Services

The website depends on external services:

1. **Feeds API**: The security news feed data comes from `feeds.zeronexus.net`, which is a separate service not included in this repository.
   - In development, the local `feeds.php` automatically proxies to the production API
   - This ensures you see real article data in development
   - If the production API is down, you'll see mock data instead

2. **Article Images**: The `article-image.php` API extracts thumbnail images from actual article URLs.
   - This works with real articles from the feeds API
   - Mock articles will return 404 errors in logs (this is expected)

### CORS Considerations

The development environment handles CORS differently than production:
- `nginx/conf.d/dev.conf` has relaxed CORS settings
- All API files include `localhost:8082` in allowed origins
- Frontend JavaScript uses relative URLs (`/api/...`) instead of absolute URLs

### When to Restart Containers

- **PHP changes**: ALWAYS restart the PHP container
- **JavaScript/CSS changes**: No restart needed
- **Nginx config changes**: Restart the nginx container
- **Docker-compose changes**: Restart all containers

```bash
# Quick PHP restart
docker-compose -f docker-compose.dev.yml restart php

# Quick Nginx restart
docker-compose -f docker-compose.dev.yml restart zeronexus
```

## Troubleshooting

### macOS-specific Issues

#### Docker Desktop not found

1. Run the Docker check script:
   ```bash
   ./scripts/check-docker-mac.sh
   ```

2. Make sure Docker Desktop is:
   - Installed from https://www.docker.com/products/docker-desktop
   - Running (check for the whale icon in your menu bar)
   - Fully started (wait for "Docker Desktop is running" in the menu)

#### "docker command not found"

This means Docker Desktop isn't in your PATH:
1. Restart your terminal
2. Check if Docker Desktop is running
3. Try `/usr/local/bin/docker --version`

#### Permission denied on scripts

```bash
chmod +x scripts/*.sh
```

### Containers won't start

1. Check if ports are in use:
   ```bash
   lsof -i :8082
   ```

2. View detailed logs:
   ```bash
   docker-compose -f docker-compose.dev.yml logs
   # or
   docker compose -f docker-compose.dev.yml logs
   ```

### PHP errors not showing

1. Verify error display is enabled:
   ```bash
   docker-compose -f docker-compose.dev.yml exec php php -i | grep display_errors
   ```

2. Check the PHP error log:
   ```bash
   tail -f ./logs/php/error.log
   ```

### Permission issues

If you encounter permission issues with logs:
```bash
chmod -R 777 logs/
```

### API-specific Issues

#### Article thumbnails not showing

1. Check if you're getting real feeds:
   ```bash
   curl http://localhost:8082/api/feeds.php | jq '.[0]'
   ```

2. Check PHP error logs for article-image.php:
   ```bash
   grep -i article logs/php/error.log | tail -20
   ```

3. Verify the PHP container was restarted after any API changes:
   ```bash
   docker-compose -f docker-compose.dev.yml restart php
   ```

#### CORS errors

If you see CORS errors in browser console:
1. Check that the origin is allowed in the API file
2. Verify the dev nginx config is being used
3. Clear browser cache and hard refresh

#### Feeds showing mock data

The local feeds.php will show mock data if:
- The production API is unreachable
- The ENVIRONMENT variable isn't set to 'development'
- The proxy request times out

Check the PHP logs for details.

### Cannot connect to site

1. Verify containers are running:
   ```bash
   docker-compose -f docker-compose.dev.yml ps
   ```

2. Check nginx logs:
   ```bash
   tail -f ./logs/nginx/error.log
   ```

3. Test direct container access:
   ```bash
   docker-compose -f docker-compose.dev.yml exec zeronexus wget -O- http://localhost
   ```

### CORS Issues

The development environment uses permissive CORS settings (`*`). If you still have issues:

1. Check browser console for specific CORS errors
2. Verify the API endpoint is using the dev config:
   ```bash
   docker-compose -f docker-compose.dev.yml exec zeronexus cat /etc/nginx/conf.d/default.conf | grep CORS
   ```

### Favicon not showing

Favicons are served from `/favicon.ico`, `/favicon.png`, and `/favicon.svg`. If not showing:

1. Clear browser cache
2. Check files exist:
   ```bash
   ls -la website/favicon*
   ```
3. Try accessing directly: http://localhost:8082/favicon.png

## Best Practices

1. **Always test in development first** before pushing to production
2. **Check logs regularly** during development
3. **Use git branches** for feature development
4. **Document any configuration changes** in this file
5. **Keep docker-compose.dev.yml and dev.conf in sync** with any production changes

## Pushing to Production

After testing in the local environment:

1. Commit your changes:
   ```bash
   git add .
   git commit -m "Description of changes"
   ```

2. Push to GitHub:
   ```bash
   git push origin feature/your-feature-name
   ```

3. Create a pull request:
   - Go to https://github.com/shikyo13/zeronexus-website/pulls
   - Click "New pull request"
   - Create PR from your feature branch to `develop`
   - After review, merge to `develop`
   - When ready for production, create PR from `develop` to `main`

3. Deploy to production VM:
   ```bash
   ssh user@production-vm "cd /path/to/site && git pull && docker-compose restart"
   ```

Remember: The production environment uses `docker-compose.yml` (without the `-f docker-compose.dev.yml` flag).