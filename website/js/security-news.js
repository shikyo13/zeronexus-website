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

      // Parse and log response for debugging
      allArticles = await response.json();

      console.log('Fetched articles:', allArticles.length);

      // Log source distribution
      const sourceCounts = {};
      allArticles.forEach(article => {
        const source = article.source || 'unknown';
        sourceCounts[source] = (sourceCounts[source] || 0) + 1;
      });
      console.log('Articles by source:', sourceCounts);

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

    // Debug current filter
    console.log('Applying filter:', currentFilter);
    console.log('Total articles:', allArticles.length);

    // Filter articles if needed
    let articles = [];
    if (currentFilter === 'all') {
      articles = [...allArticles]; // Create a copy of all articles
    } else {
      articles = allArticles.filter(article => {
        console.log(`Article source: ${article.source}, comparing with: ${currentFilter}`);
        return article.source === currentFilter;
      });
    }

    console.log('Filtered articles:', articles.length);

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

      // Create image container
      const imageContainer = document.createElement('div');
      imageContainer.className = 'image-container';

      // For articles with a thumbnail
      if (article.thumbnail) {
        const img = document.createElement('img');
        img.src = article.thumbnail;
        img.alt = article.title;
        img.setAttribute('loading', 'lazy');
        img.setAttribute('decoding', 'async');
        img.onerror = function() { this.style.display = 'none'; };

        imageContainer.appendChild(img);
        articleElement.appendChild(imageContainer);
      }
      // For BleepingComputer articles without thumbnails, try to fetch one
      else if (article.source === 'bleepingcomputer') {
        // Add a placeholder initially
        const img = document.createElement('img');
        img.src = 'https://via.placeholder.com/300x200?text=Loading...';
        img.alt = article.title;
        img.setAttribute('loading', 'lazy');
        img.setAttribute('decoding', 'async');

        // Add to DOM immediately with placeholder
        imageContainer.appendChild(img);
        articleElement.appendChild(imageContainer);

        // Try to fetch the actual image
        fetch(`/api/article-image.php?url=${encodeURIComponent(article.link)}&source=bleepingcomputer`)
          .then(response => response.json())
          .then(data => {
            if (data.image) {
              // Update the image src with the fetched image
              img.src = data.image;
            } else {
              // If no image was found, use a branded placeholder
              img.src = 'https://via.placeholder.com/300x200?text=BleepingComputer';
            }
          })
          .catch(error => {
            console.error('Failed to fetch article image:', error);
            // Set a fallback on error
            img.src = 'https://via.placeholder.com/300x200?text=No+Image';
          });
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

    // Add direct click test for debugging
    document.querySelectorAll('[data-source]').forEach(el => {
      console.log('Found filter element:', el.id, el.getAttribute('data-source'));
      // Force explicit click handler
      el.onclick = function(e) {
        e.preventDefault();
        console.log('DIRECT CLICK:', this.getAttribute('data-source'));
        currentFilter = this.getAttribute('data-source');

        // Update active classes
        document.querySelectorAll('[data-source]').forEach(link => {
          link.classList.remove('active');
        });
        this.classList.add('active');

        // Apply filter and update URL
        displayArticles();
        window.location.hash = this.getAttribute('data-source');
        return false;
      };
    });

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

    // Apply filter from URL hash immediately if present
    if (window.location.hash) {
      const source = window.location.hash.substring(1);
      console.log('Initial hash filter:', source);
      currentFilter = source;

      // Update active state of filter links
      const filterLink = document.querySelector(`[data-source="${source}"]`);
      if (filterLink) {
        document.querySelectorAll('[data-source]').forEach(link => {
          link.classList.remove('active');
        });
        filterLink.classList.add('active');
      }
    }
  }

  // Initialize the feed
  initFeed();
});