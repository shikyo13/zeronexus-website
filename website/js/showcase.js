/**
 * Showcase Page JavaScript
 */

document.addEventListener('DOMContentLoaded', () => {
  const navLinks = document.querySelectorAll('.nav-link');
  const projectItems = document.querySelectorAll('.project-item');

  /**
   * Filters projects based on selected category
   */
  function filterProjects(category) {
    category = category.toLowerCase();
    projectItems.forEach(item => {
      const categories = item.dataset.category.toLowerCase().split(' ');
      if (category === 'all' || categories.includes(category)) {
        item.classList.remove('hidden');
      } else {
        item.classList.add('hidden');
      }
    });
  }

  // Add click event listeners to navigation links
  navLinks.forEach(link => {
    link.addEventListener('click', (e) => {
      e.preventDefault();
      navLinks.forEach(l => l.classList.remove('active'));
      link.classList.add('active');
      filterProjects(link.dataset.category);
    });
  });
});

/**
 * Shows a full-size image in a modal
 */
function showFullImage(src, title) {
  // Create the modal with escaped content
  const modal = document.createElement('div');
  modal.className = 'modal fade';
  
  // Use DOM methods instead of innerHTML for better security
  const modalDialog = document.createElement('div');
  modalDialog.className = 'modal-dialog modal-xl modal-dialog-centered';
  
  const modalContent = document.createElement('div');
  modalContent.className = 'modal-content bg-dark';
  
  const modalHeader = document.createElement('div');
  modalHeader.className = 'modal-header border-secondary';
  
  const modalTitle = document.createElement('h5');
  modalTitle.className = 'modal-title text-white';
  modalTitle.textContent = title;
  
  const closeButton = document.createElement('button');
  closeButton.type = 'button';
  closeButton.className = 'btn-close btn-close-white';
  closeButton.setAttribute('data-bs-dismiss', 'modal');
  closeButton.setAttribute('aria-label', 'Close');
  
  const modalBody = document.createElement('div');
  modalBody.className = 'modal-body text-center p-0';
  
  // Create responsive image
  const picture = document.createElement('picture');
  
  // Create source elements for different screen sizes
  const sourceXL = document.createElement('source');
  sourceXL.media = '(min-width: 1200px)';
  sourceXL.srcset = src; // In a real app, use higher resolution
  
  const sourceLG = document.createElement('source');
  sourceLG.media = '(min-width: 992px)';
  sourceLG.srcset = src; // In a real app, use appropriate resolution
  
  const sourceMD = document.createElement('source');
  sourceMD.media = '(min-width: 768px)';
  sourceMD.srcset = src; // In a real app, use appropriate resolution
  
  const sourceSM = document.createElement('source');
  sourceSM.media = '(min-width: 576px)';
  sourceSM.srcset = src; // In a real app, use appropriate resolution
  
  // Add sources to picture element
  picture.appendChild(sourceXL);
  picture.appendChild(sourceLG);
  picture.appendChild(sourceMD);
  picture.appendChild(sourceSM);
  
  // Create and add the img element as fallback
  const image = document.createElement('img');
  image.src = src;
  image.alt = title;
  image.className = 'img-fluid';
  image.setAttribute('loading', 'lazy');
  image.setAttribute('decoding', 'async');
  
  picture.appendChild(image);
  
  // Assemble the modal
  modalHeader.appendChild(modalTitle);
  modalHeader.appendChild(closeButton);
  
  modalBody.appendChild(picture);
  
  modalContent.appendChild(modalHeader);
  modalContent.appendChild(modalBody);
  
  modalDialog.appendChild(modalContent);
  
  modal.appendChild(modalDialog);
  
  document.body.appendChild(modal);
  
  const modalInstance = new bootstrap.Modal(modal);
  modalInstance.show();
  
  modal.addEventListener('hidden.bs.modal', () => {
    modal.remove();
  });
}