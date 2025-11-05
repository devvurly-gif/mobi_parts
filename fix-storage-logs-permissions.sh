#!/bin/bash

# Fix Storage Logs Permissions on VPS
# This script fixes permission issues with Laravel log files

echo "🔧 Fixing Storage Logs Permissions..."

PROJECT_DIR=$(pwd)

# Check if we're in the right directory
if [ ! -f "artisan" ]; then
    echo "❌ Error: artisan file not found. Please run this script from the Laravel project root."
    exit 1
fi

# Step 1: Set permissions for storage/logs directory
echo "Setting permissions for storage/logs..."
chmod -R 775 "$PROJECT_DIR/storage"
chmod -R 775 "$PROJECT_DIR/storage/logs"

# Step 2: Create log file if it doesn't exist
if [ ! -f "$PROJECT_DIR/storage/logs/laravel.log" ]; then
    echo "Creating laravel.log file..."
    touch "$PROJECT_DIR/storage/logs/laravel.log"
    chmod 664 "$PROJECT_DIR/storage/logs/laravel.log"
fi

# Step 3: Set ownership (if running as root)
if [ "$EUID" -eq 0 ]; then
    echo "Setting ownership to www-data:www-data..."
    chown -R www-data:www-data "$PROJECT_DIR/storage"
    chown -R www-data:www-data "$PROJECT_DIR/storage/logs"
    
    # Ensure log file is owned by www-data
    if [ -f "$PROJECT_DIR/storage/logs/laravel.log" ]; then
        chown www-data:www-data "$PROJECT_DIR/storage/logs/laravel.log"
    fi
else
    echo "⚠️  Not running as root. Setting ownership manually..."
    echo "Run as root or with sudo:"
    echo "  sudo chown -R www-data:www-data $PROJECT_DIR/storage"
    echo "  sudo chown www-data:www-data $PROJECT_DIR/storage/logs/laravel.log"
fi

# Step 4: Verify permissions
echo ""
echo "Verifying permissions..."
ls -la "$PROJECT_DIR/storage/logs/" | head -5

echo ""
echo "✅ Storage logs permissions fixed!"
echo ""
echo "📋 Summary:"
echo "  - Storage directory: 775"
echo "  - Logs directory: 775"
echo "  - Laravel log file: 664"
echo "  - Owner: www-data:www-data (if run as root)"

