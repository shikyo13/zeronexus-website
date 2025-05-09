/**
 * Common JavaScript functions for ZeroNexus
 */

// Update copyright year
document.addEventListener('DOMContentLoaded', function() {
  const yearEl = document.getElementById('year');
  if (yearEl) {
    yearEl.textContent = new Date().getFullYear();
  }
});