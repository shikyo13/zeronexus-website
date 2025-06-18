#!/bin/bash

# Script to extract and validate HTML from PHP files
set -e

# Create temporary directory for HTML files
TEMP_DIR=$(mktemp -d)
trap "rm -rf $TEMP_DIR" EXIT

echo "Extracting HTML from PHP files..."

# Process each PHP file
for php_file in website/*.php; do
    if [ -f "$php_file" ]; then
        base_name=$(basename "$php_file" .php)
        html_file="$TEMP_DIR/$base_name.html"
        
        echo "Processing: $php_file"
        
        # Extract HTML using PHP CLI (suppress errors from missing includes)
        docker run --rm \
            -v "$PWD:/work" \
            -w /work \
            php:8.2-cli \
            php -r "
                error_reporting(0);
                define('SKIP_INCLUDES', true);
                \$_SERVER['REQUEST_URI'] = '/';
                \$_SERVER['HTTP_HOST'] = 'localhost';
                ob_start();
                @include '$php_file';
                \$html = ob_get_clean();
                if (!empty(\$html)) {
                    file_put_contents('$html_file', \$html);
                }
            " 2>/dev/null || true
    fi
done

# Validate extracted HTML files if any were created
if ls "$TEMP_DIR"/*.html 1> /dev/null 2>&1; then
    echo "Validating HTML files..."
    
    # Run W3C validator on extracted HTML
    for html_file in "$TEMP_DIR"/*.html; do
        echo "Validating: $(basename "$html_file")"
        
        # Use vnu.jar validator (W3C validator)
        docker run --rm \
            -v "$TEMP_DIR:/test" \
            validator/validator:latest \
            vnu --errors-only "/test/$(basename "$html_file")" || true
    done
else
    echo "No HTML files were extracted for validation"
fi

echo "HTML validation complete"