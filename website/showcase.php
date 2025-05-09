<?php
// --- Function Definitions ---

/**
 * Processes metadata for an artwork image.
 * - Sets a default tag of "Art".
 * - Unifies folder names (e.g. "Artwork", "Digital Art", "Art") to "Art".
 * - Merges in additional metadata from an optional JSON file.
 */
function getImageMetadata($filepath) {
    // Default metadata structure with a single "Art" tag.
    $metadata = [
        'title'       => '',
        'description' => '',
        'tags'        => ['Art']
    ];
    
    // Remove "artwork/" from the directory path.
    $relativePath = str_replace('artwork/', '', dirname($filepath));
    
    // If this image is in a subfolder (i.e. not directly in "artwork")
    if ($relativePath !== '' && $relativePath !== '.' && strtolower($relativePath) !== 'artwork') {
        // Split the path into folders and add each as a tag.
        $folders = explode('/', $relativePath);
        foreach ($folders as $folder) {
            // Convert folder names like "forum-signatures" to "Forum Signatures"
            $folderClean = ucwords(str_replace('-', ' ', $folder));
            // Unify "Artwork", "Digital Art", and "Art" to "Art"
            $folderCleanLower = strtolower($folderClean);
            if (in_array($folderCleanLower, ['artwork', 'digital art', 'art'])) {
                $folderClean = 'Art';
            }
            // Only add if not already present
            if (!in_array($folderClean, $metadata['tags'])) {
                $metadata['tags'][] = $folderClean;
            }
        }
    }
    
    // Look for a matching JSON file for additional metadata.
    $jsonFile = pathinfo($filepath, PATHINFO_DIRNAME) . '/' . pathinfo($filepath, PATHINFO_FILENAME) . '.json';
    if (file_exists($jsonFile)) {
        $jsonData = json_decode(file_get_contents($jsonFile), true);
        if ($jsonData) {
            // Merge JSON tags with default tags (if provided)
            if (isset($jsonData['tags'])) {
                $metadata['tags'] = array_merge($metadata['tags'], $jsonData['tags']);
            }
            // Merge in any other metadata fields (e.g., title, description)
            $metadata = array_merge($metadata, $jsonData);
        }
    } else {
        // If no JSON exists, generate a title from the filename.
        $metadata['title'] = ucwords(str_replace('-', ' ', pathinfo($filepath, PATHINFO_FILENAME)));
    }
    
    // Finally, unify any tag synonyms within the tags array.
    $unifiedTags = [];
    foreach ($metadata['tags'] as $tag) {
        $tagLower = strtolower($tag);
        if (in_array($tagLower, ['art', 'artwork', 'digital art'])) {
            $tag = 'Art';
        }
        if (!in_array($tag, $unifiedTags)) {
            $unifiedTags[] = $tag;
        }
    }
    $metadata['tags'] = $unifiedTags;
    
    return $metadata;
}

/**
 * Checks if a file is an allowed image type.
 */
function isImage($filepath) {
    $allowedTypes = ['jpg', 'jpeg', 'png', 'gif', 'webp'];
    $extension = strtolower(pathinfo($filepath, PATHINFO_EXTENSION));
    return in_array($extension, $allowedTypes);
}

/**
 * Recursively scans a directory for image files.
 */
function scanForImages($directory) {
    $images = [];
    $allowedExtensions = ['jpg', 'jpeg', 'png', 'gif', 'webp'];
    $files = scandir($directory);
    foreach ($files as $file) {
        if ($file === '.' || $file === '..') {
            continue;
        }
        $filepath = $directory . $file;
        if (is_dir($filepath)) {
            // Recursively scan subdirectories.
            $subImages = scanForImages($filepath . '/');
            $images = array_merge($images, $subImages);
        } else {
            $ext = strtolower(pathinfo($file, PATHINFO_EXTENSION));
            if (in_array($ext, $allowedExtensions)) {
                $images[] = $filepath;
            }
        }
    }
    return $images;
}

/**
 * Converts text to a slug (lowercase, hyphen-separated).
 */
function slugify($text) {
    $text = preg_replace('~[^\pL\d]+~u', '-', $text);
    $text = trim($text, '-');
    return strtolower($text);
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Creative Showcase - Adam Hunt</title>
    
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <script defer src="https://kit.fontawesome.com/4215701992.js" crossorigin="anonymous"></script>

    <style>
        :root {
            --bg-color: #181a1b;
            --card-bg: #242627;
            --text-color: #ffffff;
            --link-color: #0d6efd;
            --border-color: rgb(30, 41, 59);
            --hover-brightness: 1.2;
            --transition-speed: 0.3s;
        }
        body {
            background-color: var(--bg-color);
            color: var(--text-color);
            font-family: system-ui, -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, sans-serif;
            min-height: 100vh;
            display: flex;
            flex-direction: column;
        }
        header {
            background-color: var(--card-bg);
            border-bottom: 1px solid var(--border-color);
            padding: 1.5rem 0;
            text-align: center;
        }
        .project-card {
            background: var(--card-bg);
            border: 1px solid var(--border-color);
            border-radius: 8px;
            transition: all var(--transition-speed) ease;
            height: 100%;
            cursor: pointer;
            padding: 0;
            overflow: hidden;
            display: flex;
            flex-direction: column;
        }
        .project-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.3);
        }
        .project-card:hover .preview-image {
            transform: scale(1.05);
        }
        .image-container {
            position: relative;
            width: 100%;
            padding-top: 66.67%; /* Fixed aspect ratio for the container */
            overflow: hidden;
            background-color: var(--card-bg);
            border-radius: 8px 8px 0 0;
        }
        .preview-image {
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            /* Use 'contain' so the whole image is visible (scaled down if needed) */
            object-fit: contain;
            object-position: center;
            transition: transform var(--transition-speed) ease;
        }
        .card-content {
            padding: 1rem;
            flex-grow: 1;
            display: flex;
            flex-direction: column;
        }
        .category-badge {
            background: var(--bg-color);
            border: 1px solid var(--border-color);
            padding: 0.25rem 0.75rem;
            border-radius: 20px;
            font-size: 0.875rem;
            display: inline-block;
            margin: 0.25rem;
        }
        .category-nav {
            background-color: var(--card-bg);
            padding: 1rem;
            border-radius: 8px;
            margin-bottom: 2rem;
        }
        .nav-link {
            color: var(--text-color);
            border-bottom: 2px solid transparent;
            transition: all var(--transition-speed) ease;
            padding: 0.5rem 1rem;
            margin: 0.25rem;
        }
        .nav-link:hover,
        .nav-link.active {
            color: var(--text-color);
            border-bottom-color: var(--text-color);
            background-color: rgba(255, 255, 255, 0.1);
        }
        .project-item {
            transition: opacity var(--transition-speed) ease,
                        transform var(--transition-speed) ease;
        }
        .project-item.hidden {
            opacity: 0;
            transform: scale(0.95);
            pointer-events: none;
            position: absolute;
        }
        .modal-xl {
            max-width: 90vw;
        }
        .modal img {
            max-height: 85vh;
            object-fit: contain;
        }
        /* Mobile adjustments for the modal */
        @media (max-width: 768px) {
            .modal-dialog {
                max-width: 100%;
                margin: 0;
            }
            .modal-content {
                border: none;
                border-radius: 0;
            }
            .modal img {
                max-height: 80vh;
                width: 100%;
                height: auto;
                object-fit: contain;
            }
        }
        .card-button {
            width: 100%;
            height: 100%;
            padding: 0;
            margin: 0;
            border: none;
            background: none;
            color: inherit;
            font: inherit;
            text-align: left;
        }
        .card-button:focus {
            outline: none;
        }
        .card-button:focus-visible {
            box-shadow: 0 0 0 3px var(--link-color);
        }
    </style>
</head>
<body>
    <?php
    // Scan for images in the artwork directory.
    $artworkDir = 'artwork/';
    $images = scanForImages($artworkDir);

    // Define the fixed main navigation.
    $mainNav = [
        'all'   => 'All',
        'games' => 'Games',
        'art'   => 'Art'
    ];
    ?>

    <header>
        <div class="container">
            <div class="text-center">
                <i class="fa-solid fa-mug-hot fa-2x fa-fw mb-3"></i>
                <h1 class="h4">ZeroNexus</h1>
            </div>
        </div>
    </header>

    <main>
        <div class="container">
            <h2 class="text-center mb-4">Creative Showcase</h2>

            <div class="category-nav">
                <ul class="nav justify-content-center flex-wrap">
                    <?php foreach ($mainNav as $slug => $label): ?>
                        <?php $isActive = ($slug === 'all') ? ' active' : ''; ?>
                        <li class="nav-item">
                            <a class="nav-link<?php echo $isActive; ?>" href="#" data-category="<?php echo htmlspecialchars($slug); ?>">
                                <?php echo htmlspecialchars($label); ?>
                            </a>
                        </li>
                    <?php endforeach; ?>
                </ul>
            </div>

            <div class="row g-4">
                <!-- Hard-coded "Game" Items -->
                <div class="col-md-6 col-lg-4 project-item" data-category="games">
                    <a href="https://metalsnake.zeronexus.net" class="card-button text-decoration-none">
                        <div class="project-card">
                            <div class="image-container">
                                <img src="/metalsnakepreview.png" alt="Metal Snake Game Preview" class="preview-image img-fluid">
                            </div>
                            <div class="card-content">
                                <h3 class="h5 text-white">Metal Snake</h3>
                                <p class="text-muted">A modern take on the classic Snake game with unique mechanics and metallic aesthetics.</p>
                                <div class="d-flex flex-wrap mt-auto">
                                    <span class="category-badge">Game</span>
                                    <span class="category-badge">JavaScript</span>
                                </div>
                            </div>
                        </div>
                    </a>
                </div>

                <div class="col-md-6 col-lg-4 project-item" data-category="games">
                    <a href="https://flippyblock.zeronexus.net" class="card-button text-decoration-none">
                        <div class="project-card">
                            <div class="image-container">
                                <img src="/flippyblockpreview.png" alt="Flippy Block Game Preview" class="preview-image img-fluid">
                            </div>
                            <div class="card-content">
                                <h3 class="h5 text-white">Flippy Block</h3>
                                <p class="text-muted">An addictive puzzle game where you flip blocks to create paths and solve challenges.</p>
                                <div class="d-flex flex-wrap mt-auto">
                                    <span class="category-badge">Game</span>
                                    <span class="category-badge">JavaScript</span>
                                </div>
                            </div>
                        </div>
                    </a>
                </div>
                
                <!-- Dynamically Display Artwork Items -->
                <?php foreach ($images as $image): 
                    if (!isImage($image)) continue;
                    $metadata = getImageMetadata($image);
                    // Convert tags to slugs for the data-category attribute.
                    $sluggedTags = array_map('slugify', $metadata['tags']);
                    $tagClasses  = implode(' ', $sluggedTags);
                ?>
                    <div class="col-md-6 col-lg-4 project-item" data-category="<?php echo htmlspecialchars($tagClasses); ?>">
                        <button type="button" class="card-button" onclick="showFullImage('<?php echo htmlspecialchars(addslashes($image)); ?>', '<?php echo htmlspecialchars(addslashes($metadata['title'])); ?>')">
                            <div class="project-card">
                                <div class="image-container">
                                    <img 
                                        src="<?php echo htmlspecialchars($image); ?>" 
                                        alt="<?php echo htmlspecialchars($metadata['title']); ?>" 
                                        class="preview-image img-fluid"
                                        loading="lazy">
                                </div>
                                <div class="card-content">
                                    <h3 class="h5 text-white"><?php echo htmlspecialchars($metadata['title']); ?></h3>
                                    <?php if (!empty($metadata['description'])): ?>
                                        <p class="text-muted"><?php echo htmlspecialchars($metadata['description']); ?></p>
                                    <?php endif; ?>
                                    <div class="d-flex flex-wrap mt-auto">
                                        <?php foreach ($metadata['tags'] as $tag): ?>
                                            <span class="category-badge"><?php echo htmlspecialchars($tag); ?></span>
                                        <?php endforeach; ?>
                                    </div>
                                </div>
                            </div>
                        </button>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>
    </main>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', () => {
            const navLinks = document.querySelectorAll('.nav-link');
            const projectItems = document.querySelectorAll('.project-item');

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

            navLinks.forEach(link => {
                link.addEventListener('click', (e) => {
                    e.preventDefault();
                    navLinks.forEach(l => l.classList.remove('active'));
                    link.classList.add('active');
                    filterProjects(link.dataset.category);
                });
            });
        });

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
            
            const image = document.createElement('img');
            image.src = src;
            image.alt = title;
            image.className = 'img-fluid';
            
            // Assemble the modal
            modalHeader.appendChild(modalTitle);
            modalHeader.appendChild(closeButton);
            
            modalBody.appendChild(image);
            
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
    </script>
</body>
</html>
