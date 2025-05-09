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

/**
 * Generates srcset attribute for responsive images.
 * In a real application, you would create resized versions of the images.
 * For this example, we'll use the original image for all sizes.
 */
function generateSrcset($imagePath) {
    $ext = strtolower(pathinfo($imagePath, PATHINFO_EXTENSION));
    $basePath = substr($imagePath, 0, -(strlen($ext) + 1));
    
    // In a real implementation, you would check if these files exist
    // and only include them in the srcset if they do
    return "$imagePath 1200w, $imagePath 800w, $imagePath 400w";
    
    // In a production environment, you would generate different sizes:
    // return "$basePath-large.$ext 1200w, $basePath-medium.$ext 800w, $basePath-small.$ext 400w";
}
?>

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

// Page variables
$page_title = "Creative Showcase - Adam Hunt";
$page_description = "Explore Adam Hunt's creative projects including digital art, web games, and more.";
$page_css = "/css/showcase.css";
$page_js = "/js/showcase.js";
$header_title = "ZeroNexus";
$hide_social_icons = true;

// Include header
include 'includes/header.php';
?>

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
                                        srcset="<?php echo htmlspecialchars(generateSrcset($image)); ?>"
                                        sizes="(max-width: 576px) 100vw, (max-width: 992px) 50vw, 33vw"
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

<?php
// Include footer
include 'includes/footer.php';
?>
