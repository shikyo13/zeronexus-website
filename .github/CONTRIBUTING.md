# Contributing to ZeroNexus Website

Thank you for your interest in contributing to the ZeroNexus website! This document provides guidelines for contributing code to the project.

## Code Quality Requirements

All contributions must pass our automated code quality checks:

- **PHP**: PHPStan level 0 and PSR-12 compliance
- **JavaScript**: ESLint recommended rules
- **CSS**: Stylelint standard configuration
- **HTML**: W3C validation (where applicable)

## Pull Request Process

1. Fork the repository
2. Create a feature branch from `main`
3. Make your changes following our coding standards
4. Test your changes locally
5. Ensure all automated checks pass
6. Submit a pull request with a clear description

## Code Standards

### PHP
- Follow PSR-12 coding standards
- Use type hints where possible
- Keep methods under 50 lines
- Document complex logic with comments

### JavaScript
- Use ES6+ syntax where appropriate
- Avoid global variables
- Handle errors appropriately
- Test in multiple browsers

### CSS
- Use consistent naming conventions
- Avoid !important unless necessary
- Maintain mobile-first approach
- Keep specificity low

## Running Tests Locally

Before submitting a PR, run the linting tools locally:

```bash
# PHP
docker run --rm -v $PWD:/app phpstan/phpstan analyse -c phpstan.neon
docker run --rm -v $PWD:/app cytopia/phpcs

# JavaScript
docker run --rm -v $PWD:/work -w /work cytopia/eslint website/js/

# CSS
docker run --rm -v $PWD:/work -w /work cytopia/stylelint 'website/css/*.css'
```

## Commit Messages

- Use clear, descriptive commit messages
- Start with a verb in present tense
- Keep the first line under 50 characters
- Reference issue numbers where applicable

Example:
```
Add user authentication to API endpoints

- Implement JWT token validation
- Add rate limiting per user
- Update documentation

Fixes #123
```

## Questions?

If you have questions about contributing, please open an issue for discussion.