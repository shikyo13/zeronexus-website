# Git Best Practices for ZeroNexus Website

## Branch Strategy

Follow the Git Flow model:
- `main` - Production-ready code
- `develop` - Integration branch for features
- `feature/*` - New features
- `hotfix/*` - Emergency fixes

## Commit Messages

Follow Conventional Commits format:
```
<type>(<scope>): <subject>

<body>

<footer>
```

Types:
- `feat`: New feature
- `fix`: Bug fix
- `docs`: Documentation changes
- `style`: Code style changes
- `refactor`: Code refactoring
- `test`: Test changes
- `chore`: Maintenance tasks

## GitHub CLI Commands

### Daily Workflow
```bash
# Check repo status
gh repo view

# Create a new feature branch
git checkout develop
git pull origin develop
git checkout -b feature/my-feature

# Create a PR
gh pr create --base develop --title "feat: Add new feature" --body "Description"

# List PRs
gh pr list

# Check PR status
gh pr status

# View PR in browser
gh pr view --web
```

### Syncing with GitHub
```bash
# Fetch latest changes
git fetch --all

# Update develop branch
git checkout develop
git pull origin develop

# Sync fork (if applicable)
gh repo sync

# View repo issues
gh issue list
```

### Creating Releases
```bash
# Create a release
gh release create v1.0.0 --title "Version 1.0.0" --notes "Release notes"

# List releases
gh release list
```

## Pre-Push Checklist

Before pushing code:
1. Run local development environment
2. Test your changes thoroughly
3. Check PHP error logs
4. Verify no console errors
5. Update documentation if needed
6. Write clear commit messages

## Pull Request Process

1. Create feature branch from `develop`
2. Make changes and commit
3. Push to GitHub
4. Create PR using `gh pr create`
5. Fill out PR template
6. Request review if needed
7. Merge after approval
8. Delete feature branch

## Keeping Repository Clean

```bash
# Prune remote tracking branches
git remote prune origin

# Clean up local branches
git branch --merged | grep -v "\*\|main\|develop" | xargs -n 1 git branch -d

# View large files in repo
git ls-files | xargs ls -la | sort -nrk5 | head -20
```

## Security Reminders

- Never commit sensitive data (passwords, API keys)
- Use environment variables for configuration
- Review changes before committing
- Keep dependencies updated