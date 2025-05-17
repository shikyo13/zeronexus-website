#!/bin/bash

# Setup script for ZeroNexus local development environment

echo "🚀 Setting up ZeroNexus development environment..."

# Check if Docker is installed
if ! command -v docker &> /dev/null; then
    echo "❌ Docker is not installed. Please install Docker Desktop from https://www.docker.com/products/docker-desktop"
    echo "   For macOS, make sure Docker Desktop is running and the docker command is in your PATH."
    exit 1
fi

# Check if Docker Compose is installed (try both docker-compose and docker compose)
if ! command -v docker-compose &> /dev/null && ! docker compose version &> /dev/null; then
    echo "❌ Docker Compose is not installed. It should come with Docker Desktop."
    echo "   Try running: docker compose --version"
    exit 1
fi

# Determine which docker compose command to use
if docker compose version &> /dev/null; then
    DOCKER_COMPOSE="docker compose"
else
    DOCKER_COMPOSE="docker-compose"
fi

echo "✅ Docker and Docker Compose are installed"

# Create necessary directories
echo "📁 Creating required directories..."
mkdir -p logs/nginx
mkdir -p logs/php

# Set permissions for log directories
chmod -R 777 logs/

# Stop any existing containers
echo "🛑 Stopping any existing containers..."
$DOCKER_COMPOSE -f docker-compose.dev.yml down

# Build and start the development environment
echo "🔨 Building and starting development containers..."
$DOCKER_COMPOSE -f docker-compose.dev.yml up -d

# Wait for services to be ready
echo "⏳ Waiting for services to start..."
sleep 5

# Check if services are running
if $DOCKER_COMPOSE -f docker-compose.dev.yml ps | grep -q "Up"; then
    echo "✅ Development environment is running!"
    echo ""
    echo "🌐 Access the site at: http://localhost:8082"
    echo "📝 PHP error logs: ./logs/php/error.log"
    echo "📝 Nginx logs: ./logs/nginx/error.log and ./logs/nginx/access.log"
    echo ""
    echo "Common commands:"
    echo "  - View logs: $DOCKER_COMPOSE -f docker-compose.dev.yml logs -f"
    echo "  - Stop environment: $DOCKER_COMPOSE -f docker-compose.dev.yml down"
    echo "  - Restart services: $DOCKER_COMPOSE -f docker-compose.dev.yml restart"
    echo "  - Enter PHP container: $DOCKER_COMPOSE -f docker-compose.dev.yml exec php sh"
else
    echo "❌ Failed to start development environment. Check the logs:"
    $DOCKER_COMPOSE -f docker-compose.dev.yml logs
    exit 1
fi