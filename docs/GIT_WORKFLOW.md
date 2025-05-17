# Git Workflow and Branch Strategy

## Branch Structure

This project follows a simplified Git Flow branching model:

### Main Branches

1. **`main`** (Production)
   - Always reflects production-ready state
   - Protected branch - no direct pushes
   - All changes come through pull requests
   - Deployed automatically to production

2. **`develop`** (Integration)
   - Active development branch
   - Features are merged here first
   - Tested in staging environment
   - Merged to `main` when stable

### Feature Branches

- Created from: `develop`
- Naming: `feature/feature-name` or `feature/issue-number-description`
- Merged back to: `develop`
- Example: `feature/add-password-generator`

### Hotfix Branches

- Created from: `main`
- Naming: `hotfix/issue-description`
- Merged to: Both `main` and `develop`
- Example: `hotfix/fix-cors-headers`

## Workflow Steps

### Starting New Feature

```bash
# Update develop branch
git checkout develop
git pull origin develop

# Create feature branch
git checkout -b feature/my-feature

# Work on feature
# ... make changes ...
git add .
git commit -m "Add my feature"

# Push to remote
git push -u origin feature/my-feature
```

### Creating Pull Request

1. Push feature branch to remote
2. Create PR from feature branch to `develop`
3. Request review if needed
4. Merge after approval
5. Delete feature branch

### Releasing to Production

```bash
# Update local branches
git checkout develop
git pull origin develop
git checkout main
git pull origin main

# Merge develop to main
git merge develop
git push origin main

# Tag the release (optional)
git tag -a v1.0.0 -m "Version 1.0.0"
git push origin v1.0.0
```

## Commit Message Format

Use clear, descriptive commit messages:

```
<type>: <subject>

<body>

<footer>
```

### Types
- `feat`: New feature
- `fix`: Bug fix
- `docs`: Documentation changes
- `style`: Code style changes (formatting, etc.)
- `refactor`: Code refactoring
- `test`: Test additions/changes
- `chore`: Maintenance tasks

### Examples

```
feat: Add subnet calculator to network tools

- Implemented CIDR notation parsing
- Added subnet mask calculation
- Created visual subnet display

Closes #123
```

```
fix: Resolve CORS issue in development environment

Added localhost:8082 to allowed origins in all API endpoints
```

## Best Practices

1. **Keep commits atomic** - One logical change per commit
2. **Pull before push** - Always sync with remote before pushing
3. **Test before merging** - Ensure all tests pass
4. **Review code** - Use pull requests for code review
5. **Clean up branches** - Delete merged feature branches

## Common Commands

```bash
# View branch history
git log --oneline --graph --all

# Check current branch
git branch

# Switch branches
git checkout branch-name

# Create and switch to new branch
git checkout -b new-branch

# Merge branch
git merge branch-name

# Delete local branch
git branch -d branch-name

# Delete remote branch
git push origin --delete branch-name

# Stash changes
git stash
git stash pop

# Undo last commit (keep changes)
git reset HEAD~1

# Undo last commit (discard changes)
git reset --hard HEAD~1
```

## Setting Up Branch Protection

For repository maintainers:

1. Go to Settings → Branches
2. Add rule for `main` branch:
   - Require pull request reviews
   - Dismiss stale PR approvals
   - Require status checks
   - Include administrators
   - Restrict who can push

## Emergency Procedures

### Reverting a Bad Merge

```bash
# Find the commit before the merge
git log --oneline

# Revert to that commit
git revert -m 1 <merge-commit-hash>
```

### Fixing Conflicts

```bash
# Update your branch
git checkout feature/my-feature
git merge develop

# Fix conflicts manually
# Then:
git add .
git commit -m "Resolve merge conflicts"
```