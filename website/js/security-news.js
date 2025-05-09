/**
 * Security News Feed JavaScript
 */

document.addEventListener('DOMContentLoaded', function() {
  // DOM element references
  const securityFeed = document.getElementById('security-feed');
  const loadingIndicator = document.getElementById('loading');
  const errorMessage = document.getElementById('error-message');

  // Source filter elements with specific IDs
  const filterAll = document.getElementById('filter-all');
  const filterBleepingComputer = document.getElementById('filter-bleepingcomputer');
  const filterKrebsOnSecurity = document.getElementById('filter-krebsonsecurity');
  const filterHackerNews = document.getElementById('filter-thehackernews');
  const sourceLinks = [filterAll, filterBleepingComputer, filterKrebsOnSecurity, filterHackerNews];

  // Keep track of all articles and current filter
  let allArticles = [];
  let currentFilter = 'all';

  /**
   * Fetches all feed articles and stores them
   */
  async function fetchFeeds() {
    try {
      // Show loading indicator
      loadingIndicator.style.display = 'block';
      securityFeed.style.display = 'none';
      errorMessage.style.display = 'none';

      const response = await fetch('https://feeds.zeronexus.net/api/feeds');
      allArticles = await response.json();

      // Display articles with current filter
      displayArticles();

    } catch (error) {
      console.error('Failed to fetch security feeds:', error);
      loadingIndicator.style.display = 'none';
      errorMessage.style.display = 'block';
    }
  }

  /**
   * Filters and displays articles based on current source filter
   */
  function displayArticles() {
    // Clear existing content
    securityFeed.innerHTML = '';

    // Filter articles if needed
    const articles = currentFilter === 'all'
      ? allArticles
      : allArticles.filter(article => article.source === currentFilter);

    if (articles.length === 0) {
      const noResults = document.createElement('div');
      noResults.className = 'text-center my-5';
      noResults.innerHTML = '<p>No articles found for this source.</p>';
      securityFeed.appendChild(noResults);

      // Hide loading and show content
      loadingIndicator.style.display = 'none';
      securityFeed.style.display = 'flex';
      return;
    }

    // Create article elements
    articles.forEach(article => {
      const sourceInfo = getSourceInfo(article.source);
      const articleDate = new Date(article.date);
      const formattedDate = articleDate.toLocaleDateString('en-US', {
        month: 'short',
        day: 'numeric',
        year: 'numeric'
      });

      const articleElement = document.createElement('div');
      articleElement.className = 'feed-item';
      articleElement.setAttribute('data-source', article.source);

      // Add thumbnail if available
      if (article.thumbnail) {
        const imageContainer = document.createElement('div');
        imageContainer.className = 'image-container';

        const img = document.createElement('img');
        img.src = article.thumbnail;
        img.alt = article.title;
        img.setAttribute('loading', 'lazy');
        img.setAttribute('decoding', 'async');
        img.onerror = function() { this.style.display = 'none'; };

        imageContainer.appendChild(img);
        articleElement.appendChild(imageContainer);
      }

      // Create header div with source info
      const headerDiv = document.createElement('div');
      headerDiv.className = 'feed-header';

      // Create source icon in container
      const iconContainer = document.createElement('div');
      iconContainer.className = 'source-icon';

      const icon = document.createElement('i');
      icon.className = `${sourceInfo.icon} fa-fw`;
      icon.style.color = sourceInfo.color;

      iconContainer.appendChild(icon);

      // Create source badge
      const sourceBadge = document.createElement('div');
      sourceBadge.className = 'source-badge';
      sourceBadge.style.color = '#ffffff';
      sourceBadge.textContent = sourceInfo.name;

      // Create date span
      const dateSpan = document.createElement('span');
      dateSpan.className = 'ms-auto';
      dateSpan.style.color = '#ffffff';
      dateSpan.textContent = formattedDate;

      // Assemble header
      headerDiv.appendChild(iconContainer);
      headerDiv.appendChild(sourceBadge);
      headerDiv.appendChild(dateSpan);
      articleElement.appendChild(headerDiv);

      // Create content div
      const contentDiv = document.createElement('div');
      contentDiv.className = 'feed-content';

      // Create title link
      const titleLink = document.createElement('a');
      titleLink.href = article.link;
      titleLink.target = '_blank';
      titleLink.rel = 'noreferrer';
      titleLink.className = 'text-decoration-none';

      const titleHeading = document.createElement('h3');
      titleHeading.style.fontSize = '1.2rem';
      titleHeading.style.color = '#fff';
      titleHeading.textContent = article.title;

      titleLink.appendChild(titleHeading);
      contentDiv.appendChild(titleLink);

      // Create description
      const description = document.createElement('p');
      description.className = 'mb-0';
      description.style.color = '#ffffff';

      const descText = article.description.length > 200
        ? article.description.substring(0, 200) + '...'
        : article.description;

      description.textContent = descText;
      contentDiv.appendChild(description);

      // Assemble article
      articleElement.appendChild(contentDiv);

      // Add to container
      securityFeed.appendChild(articleElement);
    });

    // Hide loading indicator and show content
    loadingIndicator.style.display = 'none';
    securityFeed.style.display = 'flex';
  }

  /**
   * Gets source information based on the source name
   */
  function getSourceInfo(source) {
    const sourceMap = {
      bleepingcomputer: {
        name: 'BleepingComputer',
        icon: 'fa-solid fa-newspaper',
        color: '#0d6efd'
      },
      krebsonsecurity: {
        name: 'Krebs on Security',
        icon: 'fa-solid fa-shield',
        color: '#dc3545'
      },
      thehackernews: {
        name: 'The Hacker News',
        icon: 'fa-solid fa-terminal',
        color: '#198754'
      }
    };

    return sourceMap[source] || {
      name: 'Security News',
      icon: 'fa-solid fa-newspaper',
      color: '#6c757d'
    };
  }

  /**
   * Sets up filtering by source
   */
  function setupSourceFilters() {
    // Make sure we have source links before attaching event listeners
    if (sourceLinks && sourceLinks.length > 0) {
      sourceLinks.forEach(link => {
        if (link) { // Check if the element exists
          link.addEventListener('click', function(e) {
            e.preventDefault();

            // Debug information
            console.log('Filter clicked:', this.id, this.getAttribute('data-source'));

            // Update active state
            sourceLinks.forEach(l => {
              if (l) l.classList.remove('active');
            });
            this.classList.add('active');

            // Set filter and redisplay
            currentFilter = this.getAttribute('data-source');
            displayArticles();

            // Update URL hash for bookmarking
            window.location.hash = this.getAttribute('data-source');
          });
        }
      });

      // Check for hash in URL and activate corresponding filter
      if (window.location.hash) {
        const source = window.location.hash.substring(1);
        const filterLink = document.querySelector(`[data-source="${source}"]`);
        if (filterLink) {
          // Simulate a click on the filter link
          filterLink.click();
        }
      }
    } else {
      console.error('Source filter links not found');
    }
  }

  /**
   * Initialize the feed
   */
  function initFeed() {
    // Set up filtering
    setupSourceFilters();

    // Load initial data
    fetchFeeds();

    // Set up periodic refresh
    setInterval(function() {
      if (document.visibilityState === 'visible') {
        fetchFeeds();
      }
    }, 15 * 60 * 1000);

    // Refresh when returning to page
    document.addEventListener('visibilitychange', function() {
      if (document.visibilityState === 'visible') {
        fetchFeeds();
      }
    });
  }

  // Initialize the feed
  initFeed();
});