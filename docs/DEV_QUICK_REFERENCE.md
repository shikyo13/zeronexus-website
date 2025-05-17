# Development Quick Reference

## Starting Development

```bash
# Start containers
docker-compose -f docker-compose.dev.yml up -d

# Access site
open http://localhost:8082
```

## Key Commands

### After Making Changes

| What Changed | Command Required |
|-------------|-----------------|
| PHP files | `docker-compose -f docker-compose.dev.yml restart php` |
| JavaScript/CSS | None - just refresh browser |
| Nginx config | `docker-compose -f docker-compose.dev.yml restart zeronexus` |
| docker-compose.yml | `docker-compose -f docker-compose.dev.yml down && docker-compose -f docker-compose.dev.yml up -d` |

### Debugging

```bash
# View PHP errors
tail -f logs/php/error.log

# View all container logs
docker-compose -f docker-compose.dev.yml logs -f

# Test API endpoints
curl http://localhost:8082/api/feeds.php
curl "http://localhost:8082/api/article-image.php?url=ENCODED_URL"
```

## Important Notes

1. **PHP CHANGES ALWAYS REQUIRE RESTART**
2. The feeds API (`feeds.zeronexus.net`) is external - not part of this repo
3. Development uses mock data if the production API is unavailable
4. Article thumbnails only work with real article URLs from production feeds

## Common Issues

### Thumbnails Not Loading
1. Check if feeds.php is returning real URLs (not `article-XX`)
2. Restart PHP container: `docker-compose -f docker-compose.dev.yml restart php`
3. Check logs: `grep -i article logs/php/error.log`

### CORS Errors
- All API files must include `http://localhost:8082` in allowed origins
- Frontend JS should use relative URLs (`/api/...`)

### Nothing Works
```bash
# Full reset
docker-compose -f docker-compose.dev.yml down
docker-compose -f docker-compose.dev.yml up -d --build
```