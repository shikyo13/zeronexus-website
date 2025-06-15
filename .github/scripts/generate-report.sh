#!/bin/bash

# Script to generate a summary report of all linting results
# This helps developers quickly understand what needs to be fixed

echo "Code Quality Report"
echo "=================="
echo ""

# Check if any linting errors occurred
ERRORS_FOUND=0

# PHP checks
if [ -f "phpstan-results.txt" ]; then
    echo "## PHP Analysis (PHPStan)"
    cat phpstan-results.txt
    echo ""
    ERRORS_FOUND=1
fi

if [ -f "phpcs-results.txt" ]; then
    echo "## PHP Code Standards (PHPCS)"
    cat phpcs-results.txt
    echo ""
    ERRORS_FOUND=1
fi

# JavaScript checks
if [ -f "eslint-results.txt" ]; then
    echo "## JavaScript Linting (ESLint)"
    cat eslint-results.txt
    echo ""
    ERRORS_FOUND=1
fi

# CSS checks
if [ -f "stylelint-results.txt" ]; then
    echo "## CSS Linting (Stylelint)"
    cat stylelint-results.txt
    echo ""
    ERRORS_FOUND=1
fi

# HTML validation
if [ -f "html-validation-results.txt" ]; then
    echo "## HTML Validation"
    cat html-validation-results.txt
    echo ""
    ERRORS_FOUND=1
fi

if [ $ERRORS_FOUND -eq 0 ]; then
    echo "✅ All code quality checks passed!"
else
    echo "❌ Code quality issues found. Please review the above errors."
fi

exit $ERRORS_FOUND