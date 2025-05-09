<?php
// Page variables
$page_title = "Security News - ZeroNexus";
$page_description = "Latest cybersecurity news aggregated from trusted sources including BleepingComputer, Krebs on Security, and The Hacker News.";
$page_css = "/css/security-news.css";
$page_js = "/js/security-news.js";
$header_title = "Security News";

// Hide standard header with social links
$hide_social_icons = true;

// Extra resources for this page
$extra_head = '<link rel="preconnect" href="https://feeds.zeronexus.net" crossorigin />';

// Include header
include 'includes/header.php';
?>

<main class="feed-container">
  <!-- Page header and navigation -->
  <div class="d-flex justify-content-between align-items-center mb-4">
    <a href="/" class="back-link">
      <i class="fa-solid fa-arrow-left"></i>
      Back to ZeroNexus
    </a>
  </div>

  <!-- Page title -->
  <div class="page-header">
    <h2>Security News</h2>
    <p class="text-muted">Latest cybersecurity updates from trusted sources</p>
  </div>

  <!-- Source filter tabs -->
  <div class="source-tabs mb-4">
    <ul class="nav nav-pills justify-content-center">
      <li class="nav-item">
        <a class="nav-link active mx-1" href="#" data-source="all">All Sources</a>
      </li>
      <li class="nav-item">
        <a class="nav-link mx-1" href="#" data-source="bleepingcomputer">BleepingComputer</a>
      </li>
      <li class="nav-item">
        <a class="nav-link mx-1" href="#" data-source="krebsonsecurity">Krebs on Security</a>
      </li>
      <li class="nav-item">
        <a class="nav-link mx-1" href="#" data-source="thehackernews">The Hacker News</a>
      </li>
    </ul>
  </div>

  <!-- Loading indicator -->
  <div id="loading" class="text-center" style="display: none;">
    <div class="loading-spinner"></div>
    <p class="mt-3">Loading security feeds...</p>
  </div>

  <!-- Feed content -->
  <div id="security-feed" class="d-flex flex-column">
    <!-- Feed items will be inserted here -->
  </div>

  <!-- Error message -->
  <div id="error-message" class="error-message" style="display: none;">
    <i class="fa-solid fa-triangle-exclamation fa-2x mb-3"></i>
    <p class="mb-0">Unable to load security feeds. Please try again later.</p>
  </div>
</main>

<?php 
// Include footer
include 'includes/footer.php';
?>