<?php
require_once 'includes/page-setup.php';
require_once 'includes/image-utils.php';

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
$page_title = 'Creative Showcase - Adam Hunt';
$page_description = "Explore Adam Hunt's creative projects including digital art, web games, and more.";
$page_css = '/css/showcase.css';
$page_js = '/js/showcase-refactored.js';
$header_title = 'Creative Showcase';
$extra_scripts = '<script src="/js/utils.js"></script>';

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

<?php include 'includes/footer.php'; ?>
