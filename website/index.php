<?php
// Page variables
$page_title = "ZeroNexus - Adam Hunt | IT Professional & Tech Enthusiast";
$page_description = "Welcome to ZeroNexus, the digital home of Adam Hunt - IT Professional specializing in networking, security, and development. Explore my projects, games, and technical insights.";
$page_css = "/css/index.css";
$header_title = "ZeroNexus";
$header_subtitle = "Adam Hunt";

// Extra scripts specifically for home page
$extra_scripts = '<script type="module" src="/js/bsky-embed.es.js"></script>';

// Include header
include 'includes/header.php';
?>

<main>
  <!-- Updates section -->
  <section class="updates-section" id="updates">
    <bsky-embed
      username="adamahunt.bsky.social"
      mode="dark"
      limit="10"
      link-target="_blank"
      link-image="true"
      load-more="true"
    ></bsky-embed>
  </section>
</main>

<?php 
// Include footer
include 'includes/footer.php';
?>