# ZeroNexus API Documentation

## Overview

The ZeroNexus API provides endpoints for security news feeds and CVE lookups. All API endpoints support CORS and return JSON responses.

## Base URL

All API endpoints are accessed through the base URL:

```
https://zeronexus.net/api/
```

## Endpoints

### Security Feeds

```
GET /api/feeds
```

Retrieves the latest security news from multiple sources.

#### Parameters:

- `page` (optional): Page number (default: 1)
- `limit` (optional): Number of items per page (default: 30)
- `source` (optional): Filter by source (e.g., "bleepingcomputer", "krebsonsecurity", "thehackernews")

#### Example Response:

```json
[
  {
    "id": "article1",
    "source": "bleepingcomputer",
    "date": "2023-05-15T12:30:00Z",
    "title": "New Ransomware Variant Targets Enterprise Networks",
    "description": "Security researchers have discovered a new threat targeting enterprise networks...",
    "link": "https://www.bleepingcomputer.com/news/security/article-1",
    "thumbnail": "https://via.placeholder.com/150x100"
  },
  ...
]
```

### CVE Lookup

```
GET /api/cve-proxy
```

Retrieves details for a specific CVE ID from the National Vulnerability Database (NVD).

#### Parameters:

- `id` (required): The CVE ID to look up (e.g., "CVE-2023-12345")

#### Example Response:

The response format matches the NVD API.

### CVE Search

```
GET /api/cve-search
```

Searches for CVEs by year or specific ID.

#### Parameters:

- `id` (optional): A specific CVE ID to look up
- `year` (optional): A year to retrieve CVEs from (e.g., "2023")

At least one of `id` or `year` must be provided.

#### Example Response:

The response format matches the NVD API.

## Error Handling

All endpoints return an error object with the following format when an error occurs:

```json
{
  "error": true,
  "message": "Error message details"
}
```

## Rate Limiting

Please be considerate with your API usage. Heavy traffic may be rate-limited.

## CORS Support

All endpoints support Cross-Origin Resource Sharing (CORS) and can be accessed from any domain.