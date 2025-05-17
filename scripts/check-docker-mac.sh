#!/bin/bash

# Docker check specifically for macOS

echo "🔍 Checking Docker installation on macOS..."

# Check if Docker Desktop is running
if ! docker info > /dev/null 2>&1; then
    echo "❌ Docker Desktop is not running or not properly installed."
    echo ""
    echo "To fix this:"
    echo "1. Make sure Docker Desktop is installed from https://www.docker.com/products/docker-desktop"
    echo "2. Launch Docker Desktop from Applications"
    echo "3. Wait for it to fully start (icon in menu bar shows 'Docker Desktop is running')"
    echo "4. Try this script again"
    echo ""
    echo "If Docker Desktop is installed but this still fails, try:"
    echo "  - Restart Docker Desktop"
    echo "  - Check System Preferences > Security & Privacy for any blocked software"
    echo "  - Reinstall Docker Desktop"
    exit 1
fi

# Check Docker version
DOCKER_VERSION=$(docker --version)
echo "✅ Docker is installed: $DOCKER_VERSION"

# Check Docker Compose (both variants)
if docker compose version > /dev/null 2>&1; then
    COMPOSE_VERSION=$(docker compose version)
    echo "✅ Docker Compose is available: $COMPOSE_VERSION"
    echo "   Using: 'docker compose' (new syntax)"
elif command -v docker-compose > /dev/null 2>&1; then
    COMPOSE_VERSION=$(docker-compose --version)
    echo "✅ Docker Compose is available: $COMPOSE_VERSION"
    echo "   Using: 'docker-compose' (old syntax)"
else
    echo "❌ Docker Compose not found."
    echo "   This should be included with Docker Desktop."
    echo "   Try reinstalling Docker Desktop."
    exit 1
fi

echo ""
echo "✅ All Docker components are properly installed!"
echo "   You can now run: ./scripts/setup-dev.sh"