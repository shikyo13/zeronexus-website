<!doctype html>
<html lang="en">
  <head>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    
    <!-- Resource hints for faster loading -->
    <link rel="preconnect" href="https://cdn.jsdelivr.net" crossorigin />
    <link rel="preconnect" href="https://kit.fontawesome.com" crossorigin />
    
    <!-- Preload critical resources -->
    <link rel="preload" href="/css/base.css" as="style" />
    <?php if (isset($page_css)): ?>
      <link rel="preload" href="<?php echo $page_css; ?>" as="style" />
    <?php endif; ?>
    <link rel="preload" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" as="style" crossorigin="anonymous" />
    <link rel="preload" href="/js/common.js" as="script" />
    
    <!-- Theme color with system preferences -->
    <meta name="theme-color" content="#181a1b" media="(prefers-color-scheme: light)" />
    <meta name="theme-color" content="#181a1b" media="(prefers-color-scheme: dark)" />

    <!-- SEO meta tags -->
    <title><?php echo isset($page_title) ? htmlspecialchars($page_title) : 'ZeroNexus - Adam Hunt'; ?></title>
    <meta
      name="description"
      content="<?php echo isset($page_description) ? htmlspecialchars($page_description) : 'Welcome to ZeroNexus, the digital home of Adam Hunt - IT Professional specializing in networking, security, and development.'; ?>"
    />
    <meta name="author" content="Adam Hunt" />
    
    <!-- Social sharing meta tags -->
    <meta property="og:title" content="<?php echo isset($page_title) ? htmlspecialchars($page_title) : 'ZeroNexus - Adam Hunt'; ?>" />
    <meta property="og:description" content="<?php echo isset($page_description) ? htmlspecialchars($page_description) : 'Welcome to ZeroNexus, the digital home of Adam Hunt - IT Professional specializing in networking, security, and development.'; ?>" />
    <meta property="og:type" content="website" />
    <meta property="og:url" content="https://zeronexus.net" />
    <meta property="og:image" content="https://zeronexus.net/favicon.png" />
    
    <!-- Favicon setup -->
    <link rel="icon" type="image/svg+xml" href="/favicon.svg" />
    <link rel="icon" type="image/png" href="/favicon.png" sizes="100x100" />
    <link rel="apple-touch-icon" sizes="100x100" href="/favicon.png" />
    <link rel="manifest" href="/site.webmanifest" />
    
    <!-- Bootstrap CSS -->
    <link 
      rel="stylesheet" 
      href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css"
      integrity="sha384-QWTKZyjpPEjISv5WaRU9OFeRpok6YctnYmDr5pNlyT2bRjXh0JMhjY6hW+ALEwIH"
      crossorigin="anonymous"
    />
    
    <!-- Custom CSS -->
    <link rel="stylesheet" href="/css/base.css">
    <?php if (isset($page_css)): ?>
      <link rel="stylesheet" href="<?php echo $page_css; ?>">
    <?php endif; ?>
    
    <?php if (isset($extra_head)): echo $extra_head; endif; ?>
  </head>

  <body>
    <header>
      <div class="mb-3">
        <i class="fa-solid fa-mug-hot fa-2x fa-fw" title="ZeroNexus"></i>
      </div>
      <h1 class="mb-3">
        <?php echo isset($header_title) ? htmlspecialchars($header_title) : 'ZeroNexus'; ?>
        <?php if (isset($header_subtitle)): ?>
          <span class="d-block mt-2 h4 fw-normal"><?php echo htmlspecialchars($header_subtitle); ?></span>
        <?php endif; ?>
      </h1>

      <!-- Main navigation bar - always visible -->
      <nav class="site-nav d-flex flex-wrap justify-content-center gap-3 mb-4" aria-label="Main site navigation">
        <a href="/" <?php echo basename($_SERVER['PHP_SELF']) == 'index.php' ? 'class="active"' : ''; ?> title="Home">
          <i class="fa-solid fa-home fa-2x fa-fw" aria-hidden="true"></i>
          <span class="nav-label">Home</span>
        </a>

        <a href="/showcase.php" <?php echo basename($_SERVER['PHP_SELF']) == 'showcase.php' ? 'class="active"' : ''; ?> title="Creative Showcase">
          <i class="fa-solid fa-palette fa-2x fa-fw" aria-hidden="true"></i>
          <span class="nav-label">Showcase</span>
        </a>

        <a href="/security-news.php" <?php echo basename($_SERVER['PHP_SELF']) == 'security-news.php' ? 'class="active"' : ''; ?> title="Security News">
          <i class="fa-solid fa-shield-halved fa-2x fa-fw" aria-hidden="true"></i>
          <span class="nav-label">Security News</span>
        </a>

        <a href="/cve-dashboard.php" <?php echo basename($_SERVER['PHP_SELF']) == 'cve-dashboard.php' ? 'class="active"' : ''; ?> title="CVE Dashboard">
          <i class="fa-solid fa-bug fa-2x fa-fw" aria-hidden="true"></i>
          <span class="nav-label">CVE Dashboard</span>
        </a>

        <a href="/network-admin.php" <?php echo basename($_SERVER['PHP_SELF']) == 'network-admin.php' ? 'class="active"' : ''; ?> title="Network Admin Tools">
          <i class="fa-solid fa-network-wired fa-2x fa-fw" aria-hidden="true"></i>
          <span class="nav-label">Admin Tools</span>
        </a>
      </nav>

      <?php if (!isset($hide_social_icons) || !$hide_social_icons): ?>
      <!-- Social media navigation -->
      <nav class="social-icons d-flex flex-wrap justify-content-center gap-3" aria-label="Social media links">
        <a
          href="https://bsky.app/profile/adamahunt.bsky.social"
          target="_blank"
          rel="me noopener"
          title="Follow me on Bluesky"
        >
          <i class="fa-brands fa-bluesky fa-2x fa-fw" aria-hidden="true"></i>
          <span class="visually-hidden">Bluesky Profile</span>
        </a>
        
        <a
          href="https://www.reddit.com/user/Shikyo/"
          target="_blank"
          rel="me noopener"
          title="Find me on Reddit"
        >
          <i class="fa-brands fa-reddit fa-2x fa-fw" aria-hidden="true"></i>
          <span class="visually-hidden">Reddit Profile</span>
        </a>
        
        <a
          href="https://github.com/shikyo13"
          target="_blank"
          rel="me noopener"
          title="Check out my code on GitHub"
        >
          <i class="fa-brands fa-github fa-2x fa-fw" aria-hidden="true"></i>
          <span class="visually-hidden">GitHub Profile</span>
        </a>
        
        <a
          href="https://steamcommunity.com/id/ahunt/"
          target="_blank"
          rel="me noopener"
          title="Connect on Steam"
        >
          <i class="fa-brands fa-steam fa-2x fa-fw" aria-hidden="true"></i>
          <span class="visually-hidden">Steam Profile</span>
        </a>
        

        <a
          href="https://theitguykc.com"
          target="_blank"
          rel="noopener"
          title="Visit The IT Guy KC"
        >
          <i class="fa-solid fa-briefcase fa-2x fa-fw" aria-hidden="true"></i>
          <span class="visually-hidden">The IT Guy KC</span>
        </a>
      </nav>
      <?php endif; ?>