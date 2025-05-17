    <footer>
      <p class="text-body-secondary">
        &copy; <span id="year"></span> ZeroNexus
      </p>
      <p class="text-body-secondary mb-0">
        Created by Adam Hunt. All rights reserved.
      </p>
    </footer>

    <!-- Scripts -->
    <script defer src="https://kit.fontawesome.com/4215701992.js" crossorigin="anonymous"></script>
    <script defer
      src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"
      integrity="sha384-YvpcrYf0tY3lHB60NNkmXc5s9fDVZLESaAA55NDzOxhy9GkcIdslK1eN7N6jIeHz"
      crossorigin="anonymous"
    ></script>
    
    <!-- Custom scripts -->
    <script src="/js/common.js" defer></script>
    
    <?php if (isset($page_js)): ?>
      <script src="<?php echo $page_js; ?>" <?php echo isset($page_js_type) ? 'type="' . $page_js_type . '"' : ''; ?> defer></script>
    <?php endif; ?>
    
    <?php if (isset($extra_scripts)): echo $extra_scripts; endif; ?>
  </body>
</html>