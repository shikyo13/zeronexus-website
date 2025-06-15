# Development Guide

This guide covers development practices and code quality standards for the ZeroNexus website.

## Code Quality Standards

The project enforces code quality through automated linting and static analysis tools. All code must pass these checks before being merged.

### PHP Standards

We follow PSR-12 coding standards for PHP code. Additionally:

- Use type declarations where possible
- Avoid deprecated PHP functions
- Keep functions focused and under 50 lines
- Use meaningful variable and function names

### JavaScript Standards

JavaScript code follows ESLint recommended rules with these customizations:

- Browser environment globals are allowed
- Console statements are permitted (for debugging)
- Bootstrap and jQuery globals are recognized

### CSS Standards

CSS follows Stylelint standard configuration:

- Use consistent formatting
- Avoid overly specific selectors
- Maintain browser compatibility
- Bootstrap overrides should be minimal

## Running Code Quality Checks Locally

While CI/CD runs these automatically, you can run them locally using Docker:

### PHP Checks

```bash
# PHPStan static analysis
docker run --rm -v $PWD:/app phpstan/phpstan analyse -c phpstan.neon

# PHP CodeSniffer
docker run --rm -v $PWD:/app cytopia/phpcs
```

### JavaScript Checks

```bash
# ESLint
docker run --rm -v $PWD:/work -w /work cytopia/eslint website/js/
```

### CSS Checks

```bash
# Stylelint
docker run --rm -v $PWD:/work -w /work cytopia/stylelint 'website/css/*.css'
```

## Fixing Common Issues

### PHP Issues

**Undefined variable**: Ensure all variables are initialized before use:
```php
$result = null; // Initialize
if ($condition) {
    $result = getValue();
}
```

**Line too long**: Break long lines at logical points:
```php
$longMessage = "This is a very long message that " .
    "continues on the next line for readability";
```

### JavaScript Issues

**Undefined variable**: Declare all variables:
```javascript
let myVariable; // Declare at top of scope
// ... use myVariable later
```

**Missing semicolon**: Always end statements with semicolons:
```javascript
const result = calculateValue();
```

### CSS Issues

**Invalid property**: Check spelling and browser support:
```css
/* Wrong */
.element {
    colr: red;
}

/* Correct */
.element {
    color: red;
}
```

## Contributing Code

1. Create a feature branch from `main`
2. Make your changes
3. Run local linting checks
4. Commit with clear messages
5. Push and create a pull request
6. Ensure all CI checks pass

## Ignoring False Positives

If you encounter a false positive from the linting tools:

1. **PHP**: Add to `ignoreErrors` in `phpstan.neon`
2. **JavaScript**: Use inline comments: `// eslint-disable-next-line rule-name`
3. **CSS**: Use inline comments: `/* stylelint-disable-next-line rule-name */`

Use these sparingly and document why the rule is being ignored.

## Progressive Enhancement

The linting rules start at a baseline level and will be progressively tightened:

- PHPStan starts at level 0 (most lenient)
- Rules will be added incrementally
- Existing code will be refactored as needed

This ensures the codebase improves over time without blocking immediate development.