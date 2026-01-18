#!/bin/bash

# Laravel ELK Stack Setup Script
# This script sets up the Laravel application for ELK stack logging

set -e

echo "=========================================="
echo "Laravel ELK Stack Setup"
echo "=========================================="
echo ""

# Check if we're in the right directory
if [ ! -f "composer.json" ]; then
    echo "❌ Error: composer.json not found. Please run this script from the Application-Logger directory."
    exit 1
fi

# Install Composer dependencies
echo "📦 Installing Composer dependencies..."
if command -v composer &> /dev/null; then
    composer install --no-interaction --optimize-autoloader
    echo "✅ Composer dependencies installed"
else
    echo "⚠️  Composer not found. Skipping dependency installation."
    echo "   Dependencies will be installed when Docker container builds."
fi

# Setup .env file
if [ ! -f ".env" ]; then
    echo "📝 Creating .env file from .env.example..."
    cp .env.example .env
    echo "✅ .env file created"
else
    echo "✅ .env file already exists"
fi

# Update LOG_CHANNEL in .env
echo "🔧 Configuring logging channel..."
if grep -q "LOG_CHANNEL=" .env; then
    sed -i '' 's/LOG_CHANNEL=.*/LOG_CHANNEL=elk_json/' .env || sed -i 's/LOG_CHANNEL=.*/LOG_CHANNEL=elk_json/' .env
else
    echo "LOG_CHANNEL=elk_json" >> .env
fi
echo "✅ Logging channel set to elk_json"

# Generate application key
if grep -q "APP_KEY=$" .env || grep -q "APP_KEY=\s*$" .env; then
    echo "🔑 Generating application key..."
    if command -v php &> /dev/null; then
        php artisan key:generate --force
        echo "✅ Application key generated"
    else
        echo "⚠️  PHP not found. Key will be generated in Docker container."
    fi
else
    echo "✅ Application key already set"
fi

# Create storage directories
echo "📁 Creating storage directories..."
mkdir -p storage/logs
mkdir -p storage/framework/cache
mkdir -p storage/framework/sessions
mkdir -p storage/framework/views
mkdir -p bootstrap/cache
echo "✅ Storage directories created"

# Set permissions
echo "🔒 Setting permissions..."
chmod -R 775 storage bootstrap/cache 2>/dev/null || true
echo "✅ Permissions set"

# Clear caches
if command -v php &> /dev/null && [ -f "artisan" ]; then
    echo "🧹 Clearing caches..."
    php artisan config:clear 2>/dev/null || true
    php artisan cache:clear 2>/dev/null || true
    echo "✅ Caches cleared"
fi

echo ""
echo "=========================================="
echo "✅ Laravel ELK Stack Setup Complete!"
echo "=========================================="
echo ""
echo "Next steps:"
echo "1. Start the ELK stack: podman-compose up -d"
echo "2. Wait for all services to be healthy (30-60 seconds)"
echo "3. Generate test logs:"
echo "   curl 'http://localhost:8000/api/logs/generate'"
echo "   curl 'http://localhost:8000/api/logs/batch?count=50'"
echo "4. View logs in Kibana: http://localhost:5601"
echo "   - Create data view: kafka-logstash-logs-*"
echo "   - Filter by: log_source: \"laravel-app\""
echo ""
echo "For more information, see Application-Logger/README-LOGGING.md"
echo ""
