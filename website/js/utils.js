/**
 * JavaScript Utility Library
 * 
 * Common functions and patterns for use across the site
 */

const Utils = (() => {
    /**
     * Debounce function to limit how often a function can be called
     * @param {Function} func - Function to debounce
     * @param {number} wait - Wait time in milliseconds
     * @returns {Function} Debounced function
     */
    const debounce = (func, wait) => {
        let timeout;
        return function executedFunction(...args) {
            const later = () => {
                clearTimeout(timeout);
                func(...args);
            };
            clearTimeout(timeout);
            timeout = setTimeout(later, wait);
        };
    };

    /**
     * Throttle function to ensure function is called at most once per interval
     * @param {Function} func - Function to throttle
     * @param {number} limit - Time limit in milliseconds
     * @returns {Function} Throttled function
     */
    const throttle = (func, limit) => {
        let inThrottle;
        return function() {
            const args = arguments;
            const context = this;
            if (!inThrottle) {
                func.apply(context, args);
                inThrottle = true;
                setTimeout(() => inThrottle = false, limit);
            }
        };
    };

    /**
     * Safely parse JSON with error handling
     * @param {string} jsonString - JSON string to parse
     * @param {*} defaultValue - Default value if parsing fails
     * @returns {*} Parsed object or default value
     */
    const safeJsonParse = (jsonString, defaultValue = null) => {
        try {
            return JSON.parse(jsonString);
        } catch (e) {
            console.error('JSON parse error:', e);
            return defaultValue;
        }
    };

    /**
     * Format date to a readable string
     * @param {Date|string} date - Date to format
     * @param {Object} options - Intl.DateTimeFormat options
     * @returns {string} Formatted date string
     */
    const formatDate = (date, options = {}) => {
        const defaultOptions = {
            month: 'short',
            day: 'numeric',
            year: 'numeric'
        };
        const dateObj = date instanceof Date ? date : new Date(date);
        return dateObj.toLocaleDateString('en-US', {...defaultOptions, ...options});
    };

    /**
     * Create and show a loading spinner
     * @param {HTMLElement} container - Container element
     * @param {string} message - Loading message
     * @returns {HTMLElement} Loading element
     */
    const showLoading = (container, message = 'Loading...') => {
        const loadingDiv = document.createElement('div');
        loadingDiv.className = 'text-center loading-container';
        loadingDiv.innerHTML = `
            <div class="loading-spinner"></div>
            <p class="mt-3">${escapeHtml(message)}</p>
        `;
        container.appendChild(loadingDiv);
        return loadingDiv;
    };

    /**
     * Hide and remove loading spinner
     * @param {HTMLElement} loadingElement - Loading element to remove
     */
    const hideLoading = (loadingElement) => {
        if (loadingElement && loadingElement.parentNode) {
            loadingElement.parentNode.removeChild(loadingElement);
        }
    };

    /**
     * Show error message in container
     * @param {HTMLElement} container - Container element
     * @param {string} message - Error message
     * @param {string} icon - Font Awesome icon class
     * @returns {HTMLElement} Error element
     */
    const showError = (container, message, icon = 'fa-triangle-exclamation') => {
        const errorDiv = document.createElement('div');
        errorDiv.className = 'error-message text-center my-5';
        errorDiv.innerHTML = `
            <i class="fa-solid ${escapeHtml(icon)} fa-2x mb-3"></i>
            <p class="mb-0">${escapeHtml(message)}</p>
        `;
        container.appendChild(errorDiv);
        return errorDiv;
    };

    /**
     * Escape HTML to prevent XSS
     * @param {string} unsafe - Unsafe string
     * @returns {string} Escaped string
     */
    const escapeHtml = (unsafe) => {
        return unsafe
            .replace(/&/g, "&amp;")
            .replace(/</g, "&lt;")
            .replace(/>/g, "&gt;")
            .replace(/"/g, "&quot;")
            .replace(/'/g, "&#039;");
    };

    /**
     * Update URL parameters without page reload
     * @param {Object} params - Parameters to set
     * @param {boolean} replace - Whether to replace history state
     */
    const updateUrlParams = (params, replace = false) => {
        const url = new URL(window.location);
        
        Object.entries(params).forEach(([key, value]) => {
            if (value === null || value === undefined || value === '') {
                url.searchParams.delete(key);
            } else {
                url.searchParams.set(key, value);
            }
        });

        const method = replace ? 'replaceState' : 'pushState';
        window.history[method]({}, '', url);
    };

    /**
     * Get URL parameter value
     * @param {string} param - Parameter name
     * @returns {string|null} Parameter value or null
     */
    const getUrlParam = (param) => {
        const urlParams = new URLSearchParams(window.location.search);
        return urlParams.get(param);
    };

    /**
     * Fetch with timeout
     * @param {string} url - URL to fetch
     * @param {Object} options - Fetch options
     * @param {number} timeout - Timeout in milliseconds
     * @returns {Promise} Fetch promise
     */
    const fetchWithTimeout = (url, options = {}, timeout = 30000) => {
        return Promise.race([
            fetch(url, options),
            new Promise((_, reject) =>
                setTimeout(() => reject(new Error('Request timeout')), timeout)
            )
        ]);
    };

    /**
     * Create a cache with TTL
     * @param {string} prefix - Cache key prefix
     * @param {number} ttl - Time to live in milliseconds
     * @returns {Object} Cache object with get/set methods
     */
    const createCache = (prefix, ttl = 3600000) => {
        const buildKey = (key) => `${prefix}_${key}`;
        
        return {
            get(key) {
                const storageKey = buildKey(key);
                const cached = localStorage.getItem(storageKey);
                if (!cached) return null;
                
                const data = safeJsonParse(cached);
                if (!data || Date.now() > data.expires) {
                    localStorage.removeItem(storageKey);
                    return null;
                }
                
                return data.value;
            },
            
            set(key, value) {
                const storageKey = buildKey(key);
                const data = {
                    value,
                    expires: Date.now() + ttl
                };
                localStorage.setItem(storageKey, JSON.stringify(data));
            },
            
            clear(key) {
                if (key) {
                    localStorage.removeItem(buildKey(key));
                } else {
                    // Clear all cache entries with this prefix
                    Object.keys(localStorage)
                        .filter(k => k.startsWith(prefix))
                        .forEach(k => localStorage.removeItem(k));
                }
            }
        };
    };

    /**
     * Set active navigation state
     * @param {string} selector - Selector for navigation links
     * @param {Function} getActiveKey - Function to determine active key
     */
    const setupActiveNav = (selector, getActiveKey) => {
        const links = document.querySelectorAll(selector);
        const activeKey = getActiveKey();
        
        links.forEach(link => {
            const linkKey = link.getAttribute('data-key') || link.getAttribute('href');
            if (linkKey === activeKey) {
                link.classList.add('active');
            } else {
                link.classList.remove('active');
            }
        });
    };

    /**
     * Copy text to clipboard
     * @param {string} text - Text to copy
     * @returns {Promise<void>}
     */
    const copyToClipboard = async (text) => {
        if (navigator.clipboard) {
            return navigator.clipboard.writeText(text);
        }
        
        // Fallback for older browsers
        const textArea = document.createElement('textarea');
        textArea.value = text;
        textArea.style.position = 'fixed';
        textArea.style.left = '-999999px';
        textArea.style.top = '-999999px';
        document.body.appendChild(textArea);
        textArea.focus();
        textArea.select();
        
        try {
            document.execCommand('copy');
        } finally {
            textArea.remove();
        }
    };

    /**
     * Handle visibility change for auto-refresh
     * @param {Function} onVisible - Callback when page becomes visible
     * @param {Function} onHidden - Callback when page becomes hidden
     */
    const handleVisibilityChange = (onVisible, onHidden) => {
        document.addEventListener('visibilitychange', () => {
            if (document.visibilityState === 'visible') {
                onVisible && onVisible();
            } else {
                onHidden && onHidden();
            }
        });
    };

    // Public API
    return {
        debounce,
        throttle,
        safeJsonParse,
        formatDate,
        showLoading,
        hideLoading,
        showError,
        escapeHtml,
        updateUrlParams,
        getUrlParam,
        fetchWithTimeout,
        createCache,
        setupActiveNav,
        copyToClipboard,
        handleVisibilityChange
    };
})();

// Export for use in other scripts
window.Utils = Utils;