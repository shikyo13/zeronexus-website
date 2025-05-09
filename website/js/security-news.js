/**
 * Security News Feed JavaScript
 */

document.addEventListener('DOMContentLoaded', function() {
  const securityFeed = document.getElementById('security-feed');
  const loadingIndicator = document.getElementById('loading');
  const errorMessage = document.getElementById('error-message');

  /**
   * Fetches and displays security feed articles
   */
  async function fetchAndDisplayFeeds() {
    try {
      // Show loading indicator
      loadingIndicator.style.display = 'block';
      securityFeed.style.display = 'none';
      errorMessage.style.display = 'none';

      const response = await fetch('https://feeds.zeronexus.net/api/feeds');
      const articles = await response.json();
      
      // Clear existing content
      securityFeed.innerHTML = '';
      
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
        
        // Create header div with source info
        const headerDiv = document.createElement('div');
        headerDiv.className = 'feed-header';
        
        // Create icon
        const icon = document.createElement('i');
        icon.className = `${sourceInfo.icon} fa-fw`;
        icon.style.color = sourceInfo.color;
        
        // Create source name span
        const sourceName = document.createElement('span');
        sourceName.className = 'ms-1 fw-bold'; // Changed from text-body-secondary to make it more visible
        sourceName.textContent = sourceInfo.name;

        // Create date span
        const dateSpan = document.createElement('span');
        dateSpan.className = 'ms-auto text-white-50'; // Changed from text-body-secondary for better contrast
        dateSpan.textContent = formattedDate;
        
        // Assemble header
        headerDiv.appendChild(icon);
        headerDiv.appendChild(sourceName);
        headerDiv.appendChild(dateSpan);
        articleElement.appendChild(headerDiv);
        
        // Create content div
        const contentDiv = document.createElement('div');
        contentDiv.className = 'd-flex gap-3';
        
        // Add thumbnail if available
        if (article.thumbnail) {
          const thumbnailDiv = document.createElement('div');
          thumbnailDiv.className = 'flex-shrink-0';
          thumbnailDiv.style.width = '150px';
          
          const img = document.createElement('img');
          img.src = article.thumbnail;
          // Add responsive image attributes
          if (article.thumbnail) {
            // In a real application, you would generate these URLs based on the thumbnail
            img.srcset = `${article.thumbnail} 300w, ${article.thumbnail} 200w, ${article.thumbnail} 100w`;
            img.sizes = '(max-width: 576px) 0px, 150px'; // Hide on very small screens
          }
          img.alt = article.title;
          img.className = 'img-fluid rounded';
          img.style.objectFit = 'cover';
          img.style.height = '100px';
          img.style.width = '150px';
          img.setAttribute('loading', 'lazy');
          img.setAttribute('decoding', 'async');
          img.onerror = function() { this.style.display = 'none'; };
          
          thumbnailDiv.appendChild(img);
          contentDiv.appendChild(thumbnailDiv);
        }
        
        // Create content
        const textDiv = document.createElement('div');
        textDiv.className = 'flex-grow-1';
        
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
        textDiv.appendChild(titleLink);
        
        // Create description
        const description = document.createElement('p');
        description.className = 'text-white-50 mb-0'; // Changed from text-body-secondary for better contrast
        description.style.fontSize = '0.95rem';
        
        const descText = article.description.length > 200 
          ? article.description.substring(0, 200) + '...'
          : article.description;
        
        description.textContent = descText;
        textDiv.appendChild(description);
        
        // Assemble content
        contentDiv.appendChild(textDiv);
        articleElement.appendChild(contentDiv);
        
        // Add to container
        securityFeed.appendChild(articleElement);
      });

      // Hide loading indicator and show content
      loadingIndicator.style.display = 'none';
      securityFeed.style.display = 'flex';
    } catch (error) {
      console.error('Failed to fetch security feeds:', error);
      loadingIndicator.style.display = 'none';
      errorMessage.style.display = 'block';
    }
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

  // Initial load
  fetchAndDisplayFeeds();

  // Check if page is visible and refresh feeds every 15 minutes
  let interval = setInterval(function() {
    if (document.visibilityState === 'visible') {
      fetchAndDisplayFeeds();
    }
  }, 15 * 60 * 1000);

  // Handle visibility changes
  document.addEventListener('visibilitychange', function() {
    if (document.visibilityState === 'visible') {
      fetchAndDisplayFeeds();
    }
  });
});