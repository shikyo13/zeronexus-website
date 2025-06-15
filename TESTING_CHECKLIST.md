# CVE Dashboard Sorting Fix - Testing Checklist

## Overview
This document outlines the testing scenarios for the CVE dashboard sorting bug fixes implemented in issue #5.

## Changes Made
1. **Server-side sorting** - Added `sort` parameter to `/api/cve-combined.php`
2. **Cache key updates** - Sort order now included in cache key generation
3. **Date normalization** - Robust date parsing handles multiple formats
4. **Client-side updates** - Sort changes trigger new API requests instead of re-rendering
5. **Debug cleanup** - Removed console.log statements

## Test Scenarios

### 1. Basic Sorting Tests
- [ ] **Default sort (newest first)**
  - Load CVE dashboard without sort parameter
  - Verify CVEs are sorted by date descending (newest first)
  - URL: `/cve-dashboard.php`

- [ ] **Ascending sort (oldest first)**
  - Change sort dropdown to "Oldest First"
  - Verify page fetches new data (loading indicator shows)
  - Verify CVEs are sorted by date ascending (oldest first)
  - Check browser network tab shows `sort=date_asc` parameter

- [ ] **Descending sort (newest first)**
  - Change sort dropdown back to "Newest First"
  - Verify page fetches new data (loading indicator shows)
  - Verify CVEs are sorted by date descending (newest first)
  - Check browser network tab shows `sort=date_desc` parameter

### 2. Search + Sort Combinations
- [ ] **Year search with sorting**
  - Select year 2023
  - Change sort to "Oldest First"
  - Verify results are from 2023 AND sorted oldest first
  - API URL should contain: `year=2023&sort=date_asc`

- [ ] **Keyword search with sorting**
  - Search for "microsoft"
  - Change sort order
  - Verify results contain "microsoft" AND are properly sorted
  - API URL should contain: `keyword=microsoft&sort=date_desc`

- [ ] **Vendor search with sorting**
  - Search for vendor "apache"
  - Change sort order
  - Verify results are from Apache AND properly sorted
  - API URL should contain: `vendor=apache&sort=date_asc`

### 3. Edge Cases
- [ ] **CVEs with same date**
  - Find CVEs published on same date
  - Verify they're sorted by CVE ID as secondary sort

- [ ] **Missing dates**
  - If any CVEs lack published date
  - Verify they appear at the end (treated as epoch 0)

- [ ] **Invalid sort parameter**
  - Manually add `?sort=invalid` to URL
  - Verify it defaults to `date_desc`

### 4. Cache Behavior
- [ ] **Cache includes sort order**
  - Load page with default sort
  - Change sort order
  - Change back to original sort
  - Verify each change fetches fresh data (cache key different)

- [ ] **Different searches cached separately**
  - Search for "buffer" with newest first
  - Search for "buffer" with oldest first
  - Go back to newest first
  - Each should maintain its own cache

### 5. Performance Tests
- [ ] **Response time**
  - Sorting should not significantly impact response time
  - Target: <2 seconds for typical result sets

- [ ] **Large result sets**
  - Search for common term (e.g., year 2023)
  - Verify sorting works correctly even with 100+ results

### 6. UI/UX Tests
- [ ] **Loading indicator**
  - Shows when sort order changes
  - Hides when results loaded

- [ ] **No console errors**
  - Open browser console
  - Change sort orders
  - Verify no JavaScript errors

- [ ] **Sort dropdown state**
  - Selected sort order persists after results load
  - Dropdown shows correct selected value

## API Testing Commands

### Using curl (when environment is available):
```bash
# Test default sort (should be date_desc)
curl "http://localhost:8082/api/cve-combined.php?recent=true"

# Test ascending sort
curl "http://localhost:8082/api/cve-combined.php?recent=true&sort=date_asc"

# Test with search
curl "http://localhost:8082/api/cve-combined.php?keyword=apache&sort=date_desc"

# Test invalid sort (should default to date_desc)
curl "http://localhost:8082/api/cve-combined.php?recent=true&sort=invalid"
```

## Expected Behavior Summary
1. **Sort parameter values**: `date_desc` (default) or `date_asc`
2. **Cache key includes sort**: Different cache entries for different sort orders
3. **Client triggers fetch**: Changing sort fetches new data, not just re-render
4. **Consistent ordering**: Same sort produces same order every time
5. **Secondary sort**: CVEs with identical dates sorted by ID

## Regression Tests
- [ ] All existing search functionality still works
- [ ] Filters (year, severity, sources) work with sorting
- [ ] Direct CVE ID lookup still works
- [ ] Recent CVEs view works with sorting

## Notes
- The sort happens server-side before pagination/limiting
- This ensures correct chronological order across all pages
- Date formats handled: ISO 8601, YYYY-MM-DD, and others via strtotime()