/**
 * Showcase Page JavaScript - Refactored
 * Uses modular pattern and utility functions
 */

const Showcase = (() => {
    // DOM elements
    let elements = {};
    
    // State
    let state = {
        activeCategory: 'all'
    };

    /**
     * Initialize DOM element references
     */
    const initElements = () => {
        elements = {
            navLinks: document.querySelectorAll('.nav-link'),
            projectItems: document.querySelectorAll('.project-item')
        };
    };

    /**
     * Filter projects based on selected category
     * @param {string} category - Category to filter by
     */
    const filterProjects = (category) => {
        category = category.toLowerCase();
        state.activeCategory = category;
        
        elements.projectItems.forEach(item => {
            const categories = item.dataset.category.toLowerCase().split(' ');
            const shouldShow = category === 'all' || categories.includes(category);
            
            item.classList.toggle('hidden', !shouldShow);
        });
    };

    /**
     * Handle navigation click
     * @param {Event} e - Click event
     */
    const handleNavClick = (e) => {
        e.preventDefault();
        const link = e.currentTarget;
        const category = link.dataset.category;
        
        // Update active state
        elements.navLinks.forEach(l => l.classList.remove('active'));
        link.classList.add('active');
        
        // Filter projects
        filterProjects(category);
        
        // Update URL
        Utils.updateUrlParams({ category: category === 'all' ? null : category });
    };

    /**
     * Setup event handlers
     */
    const setupEventHandlers = () => {
        elements.navLinks.forEach(link => {
            link.addEventListener('click', handleNavClick);
        });
    };

    /**
     * Initialize from URL parameters
     */
    const initFromUrl = () => {
        const category = Utils.getUrlParam('category') || 'all';
        const targetLink = Array.from(elements.navLinks).find(
            link => link.dataset.category === category
        );
        
        if (targetLink) {
            targetLink.click();
        }
    };

    /**
     * Initialize the showcase
     */
    const init = () => {
        initElements();
        setupEventHandlers();
        initFromUrl();
    };

    // Public API
    return {
        init,
        showFullImage
    };
})();

/**
 * Shows a full-size image in a modal
 * @param {string} src - Image source URL
 * @param {string} title - Image title
 */
function showFullImage(src, title) {
    const modal = createImageModal(src, title);
    document.body.appendChild(modal);
    
    const modalInstance = new bootstrap.Modal(modal);
    modalInstance.show();
    
    modal.addEventListener('hidden.bs.modal', () => {
        modal.remove();
    });
}

/**
 * Create image modal element
 * @param {string} src - Image source URL
 * @param {string} title - Image title
 * @returns {HTMLElement} Modal element
 */
function createImageModal(src, title) {
    const modal = document.createElement('div');
    modal.className = 'modal fade';
    
    const modalDialog = document.createElement('div');
    modalDialog.className = 'modal-dialog modal-xl modal-dialog-centered';
    
    const modalContent = document.createElement('div');
    modalContent.className = 'modal-content bg-dark';
    
    // Header
    const modalHeader = createModalHeader(title);
    
    // Body with responsive image
    const modalBody = createModalBody(src, title);
    
    // Assemble modal
    modalContent.appendChild(modalHeader);
    modalContent.appendChild(modalBody);
    modalDialog.appendChild(modalContent);
    modal.appendChild(modalDialog);
    
    return modal;
}

/**
 * Create modal header
 * @param {string} title - Modal title
 * @returns {HTMLElement} Header element
 */
function createModalHeader(title) {
    const header = document.createElement('div');
    header.className = 'modal-header border-secondary';
    
    const titleEl = document.createElement('h5');
    titleEl.className = 'modal-title text-white';
    titleEl.textContent = title;
    
    const closeBtn = document.createElement('button');
    closeBtn.type = 'button';
    closeBtn.className = 'btn-close btn-close-white';
    closeBtn.setAttribute('data-bs-dismiss', 'modal');
    closeBtn.setAttribute('aria-label', 'Close');
    
    header.appendChild(titleEl);
    header.appendChild(closeBtn);
    
    return header;
}

/**
 * Create modal body with responsive image
 * @param {string} src - Image source URL
 * @param {string} title - Image title
 * @returns {HTMLElement} Body element
 */
function createModalBody(src, title) {
    const body = document.createElement('div');
    body.className = 'modal-body text-center p-0';
    
    const picture = document.createElement('picture');
    
    // Add responsive sources
    const sizes = [
        { media: '(min-width: 1200px)', width: 'xl' },
        { media: '(min-width: 992px)', width: 'lg' },
        { media: '(min-width: 768px)', width: 'md' },
        { media: '(min-width: 576px)', width: 'sm' }
    ];
    
    sizes.forEach(({ media, width }) => {
        const source = document.createElement('source');
        source.media = media;
        source.srcset = src; // In production, use different resolutions
        picture.appendChild(source);
    });
    
    // Fallback image
    const img = document.createElement('img');
    img.src = src;
    img.alt = title;
    img.className = 'img-fluid';
    img.setAttribute('loading', 'lazy');
    img.setAttribute('decoding', 'async');
    
    picture.appendChild(img);
    body.appendChild(picture);
    
    return body;
}

// Initialize when DOM is ready
document.addEventListener('DOMContentLoaded', () => {
    Showcase.init();
});