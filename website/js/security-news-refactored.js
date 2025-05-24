/**
 * Security News Feed JavaScript - Refactored
 * Uses modular pattern and utility functions
 */

const SecurityNews = (() => {
    // Configuration
    const config = {
        apiEndpoint: '/api/feeds.php',
        refreshInterval: 15 * 60 * 1000, // 15 minutes
        cache: Utils.createCache('security_news', 5 * 60 * 1000), // 5 minute cache
        sources: {
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
        }
    };

    // DOM elements
    let elements = {};
    
    // State
    let state = {
        articles: [],
        currentFilter: 'all',
        refreshTimer: null
    };

    /**
     * Initialize DOM element references
     */
    const initElements = () => {
        elements = {
            feed: document.getElementById('security-feed'),
            loading: document.getElementById('loading'),
            error: document.getElementById('error-message'),
            filterLinks: document.querySelectorAll('[data-source]')
        };
    };

    /**
     * Fetch articles from API
     */
    const fetchArticles = async () => {
        try {
            // Check cache first
            const cached = config.cache.get('articles');
            if (cached) {
                state.articles = cached;
                renderArticles();
                return;
            }

            // Show loading state
            Utils.showLoading(elements.feed, 'Loading security feeds...');
            elements.feed.style.display = 'none';
            elements.error.style.display = 'none';

            const response = await Utils.fetchWithTimeout(config.apiEndpoint);
            if (!response.ok) throw new Error('Failed to fetch articles');

            const articles = await response.json();
            
            // Update state and cache
            state.articles = articles;
            config.cache.set('articles', articles);

            console.log(`Fetched ${articles.length} articles`);
            logSourceDistribution(articles);

            renderArticles();

        } catch (error) {
            console.error('Failed to fetch security feeds:', error);
            showError();
        }
    };

    /**
     * Log article source distribution for debugging
     */
    const logSourceDistribution = (articles) => {
        const sourceCounts = articles.reduce((acc, article) => {
            const source = article.source || 'unknown';
            acc[source] = (acc[source] || 0) + 1;
            return acc;
        }, {});
        console.log('Articles by source:', sourceCounts);
    };

    /**
     * Render articles based on current filter
     */
    const renderArticles = () => {
        elements.feed.innerHTML = '';

        // Filter articles
        const filteredArticles = state.currentFilter === 'all' 
            ? [...state.articles]
            : state.articles.filter(article => article.source === state.currentFilter);

        console.log(`Rendering ${filteredArticles.length} articles (filter: ${state.currentFilter})`);

        if (filteredArticles.length === 0) {
            elements.feed.appendChild(createNoResultsElement());
        } else {
            filteredArticles.forEach(article => {
                elements.feed.appendChild(createArticleElement(article));
            });
        }

        // Show feed
        elements.loading.style.display = 'none';
        elements.feed.style.display = 'flex';
    };

    /**
     * Create article DOM element
     */
    const createArticleElement = (article) => {
        const sourceInfo = config.sources[article.source] || {
            name: 'Security News',
            icon: 'fa-solid fa-newspaper',
            color: '#6c757d'
        };

        const articleEl = document.createElement('div');
        articleEl.className = 'feed-item';
        articleEl.setAttribute('data-source', article.source);

        // Add image if available
        if (article.thumbnail || article.source === 'bleepingcomputer') {
            articleEl.appendChild(createImageElement(article));
        }

        // Add header
        articleEl.appendChild(createHeaderElement(article, sourceInfo));

        // Add content
        articleEl.appendChild(createContentElement(article));

        return articleEl;
    };

    /**
     * Create image element with lazy loading
     */
    const createImageElement = (article) => {
        const container = document.createElement('div');
        container.className = 'image-container';

        const img = document.createElement('img');
        img.alt = article.title;
        img.setAttribute('loading', 'lazy');
        img.setAttribute('decoding', 'async');

        if (article.thumbnail) {
            img.src = article.thumbnail;
            img.onerror = () => img.style.display = 'none';
        } else if (article.source === 'bleepingcomputer') {
            // Fetch image dynamically
            img.src = 'https://via.placeholder.com/300x200?text=Loading...';
            fetchArticleImage(article.link, article.source).then(imageUrl => {
                img.src = imageUrl || 'https://via.placeholder.com/300x200?text=BleepingComputer';
            });
        }

        container.appendChild(img);
        return container;
    };

    /**
     * Create header element
     */
    const createHeaderElement = (article, sourceInfo) => {
        const header = document.createElement('div');
        header.className = 'feed-header';

        // Icon
        const iconContainer = document.createElement('div');
        iconContainer.className = 'source-icon';
        const icon = document.createElement('i');
        icon.className = `${sourceInfo.icon} fa-fw`;
        icon.style.color = sourceInfo.color;
        iconContainer.appendChild(icon);

        // Source badge
        const badge = document.createElement('div');
        badge.className = 'source-badge';
        badge.style.color = '#ffffff';
        badge.textContent = sourceInfo.name;

        // Date
        const dateSpan = document.createElement('span');
        dateSpan.className = 'ms-auto';
        dateSpan.style.color = '#ffffff';
        dateSpan.textContent = Utils.formatDate(article.date);

        header.appendChild(iconContainer);
        header.appendChild(badge);
        header.appendChild(dateSpan);

        return header;
    };

    /**
     * Create content element
     */
    const createContentElement = (article) => {
        const content = document.createElement('div');
        content.className = 'feed-content';

        // Title link
        const link = document.createElement('a');
        link.href = article.link;
        link.target = '_blank';
        link.rel = 'noreferrer';
        link.className = 'text-decoration-none';

        const title = document.createElement('h3');
        title.style.fontSize = '1.2rem';
        title.style.color = '#fff';
        title.textContent = article.title;
        link.appendChild(title);

        // Description
        const description = document.createElement('p');
        description.className = 'mb-0';
        description.style.color = '#ffffff';
        description.textContent = article.description.length > 200
            ? article.description.substring(0, 200) + '...'
            : article.description;

        content.appendChild(link);
        content.appendChild(description);

        return content;
    };

    /**
     * Create no results element
     */
    const createNoResultsElement = () => {
        const div = document.createElement('div');
        div.className = 'text-center my-5';
        div.innerHTML = '<p>No articles found for this source.</p>';
        return div;
    };

    /**
     * Fetch article image
     */
    const fetchArticleImage = async (url, source) => {
        try {
            const response = await fetch(`/api/article-image.php?url=${encodeURIComponent(url)}&source=${source}`);
            const data = await response.json();
            return data.image || null;
        } catch (error) {
            console.error('Failed to fetch article image:', error);
            return 'https://via.placeholder.com/300x200?text=No+Image';
        }
    };

    /**
     * Show error state
     */
    const showError = () => {
        elements.loading.style.display = 'none';
        elements.error.style.display = 'block';
        elements.feed.style.display = 'none';
    };

    /**
     * Setup filter event handlers
     */
    const setupFilters = () => {
        elements.filterLinks.forEach(link => {
            link.addEventListener('click', handleFilterClick);
        });

        // Check for hash filter in URL
        const hash = window.location.hash.substring(1);
        if (hash) {
            const filterLink = document.querySelector(`[data-source="${hash}"]`);
            if (filterLink) {
                state.currentFilter = hash;
                updateActiveFilter(filterLink);
            }
        }
    };

    /**
     * Handle filter click
     */
    const handleFilterClick = (e) => {
        e.preventDefault();
        const source = e.currentTarget.getAttribute('data-source');
        
        console.log('Filter clicked:', source);
        
        state.currentFilter = source;
        updateActiveFilter(e.currentTarget);
        renderArticles();
        
        // Update URL
        window.location.hash = source;
    };

    /**
     * Update active filter state
     */
    const updateActiveFilter = (activeLink) => {
        elements.filterLinks.forEach(link => {
            link.classList.toggle('active', link === activeLink);
        });
    };

    /**
     * Setup auto-refresh
     */
    const setupAutoRefresh = () => {
        // Clear existing timer
        if (state.refreshTimer) {
            clearInterval(state.refreshTimer);
        }

        // Set up periodic refresh
        state.refreshTimer = setInterval(() => {
            if (document.visibilityState === 'visible') {
                fetchArticles();
            }
        }, config.refreshInterval);

        // Refresh when returning to page
        Utils.handleVisibilityChange(
            () => fetchArticles(), // onVisible
            null // onHidden
        );
    };

    /**
     * Initialize the security news feed
     */
    const init = () => {
        initElements();
        setupFilters();
        fetchArticles();
        setupAutoRefresh();
    };

    // Public API
    return {
        init,
        refresh: fetchArticles
    };
})();

// Initialize when DOM is ready
document.addEventListener('DOMContentLoaded', () => {
    SecurityNews.init();
});