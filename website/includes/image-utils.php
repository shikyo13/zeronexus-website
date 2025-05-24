<?php
/**
 * Image Utilities
 * 
 * Functions for processing and managing images in the showcase gallery
 */

/**
 * Processes metadata for an artwork image.
 * - Sets a default tag of "Art".
 * - Unifies folder names (e.g. "Artwork", "Digital Art", "Art") to "Art".
 * - Merges in additional metadata from an optional JSON file.
 * 
 * @param string $filepath Path to the image file
 * @return array Metadata array with title, description, and tags
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
 * 
 * @param string $filepath Path to the file
 * @return bool True if the file is an allowed image type
 */
function isImage($filepath) {
    $allowedTypes = ['jpg', 'jpeg', 'png', 'gif', 'webp'];
    $extension = strtolower(pathinfo($filepath, PATHINFO_EXTENSION));
    return in_array($extension, $allowedTypes);
}

/**
 * Recursively scans a directory for image files.
 * 
 * @param string $directory Directory path to scan
 * @return array Array of image file paths
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
 * 
 * @param string $text Text to convert
 * @return string Slugified text
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
 * 
 * @param string $imagePath Path to the image
 * @return string Srcset attribute value
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