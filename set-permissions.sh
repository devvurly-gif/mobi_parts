#!/bin/bash

# Laravel Permissions Setup Script for Debian
# Run this script from the project root directory

echo "Setting Laravel permissions for mobi_parts..."

# Get the current directory
PROJECT_DIR=$(pwd)

# Check if we're in the right directory
if [ ! -f "artisan" ]; then
    echo "Error: artisan file not found. Please run this script from the Laravel project root."
    exit 1
fi

# Set ownership (adjust username as needed)
# Option 1: If running as root, set ownership to www-data
if [ "$EUID" -eq 0 ]; then
    echo "Setting ownership to www-data:www-data..."
    chown -R www-data:www-data "$PROJECT_DIR"
else
    echo "Not running as root. Skipping ownership change."
    echo "To set ownership, run: sudo chown -R www-data:www-data $PROJECT_DIR"
fi

# Set directory permissions (755)
echo "Setting directory permissions to 755..."
find "$PROJECT_DIR" -type d -exec chmod 755 {} \;

# Set file permissions (644)
echo "Setting file permissions to 644..."
find "$PROJECT_DIR" -type f -exec chmod 644 {} \;

# Make artisan executable
echo "Making artisan executable..."
chmod +x "$PROJECT_DIR/artisan"

# Set storage and cache directories to be writable (775)
echo "Setting storage and bootstrap/cache permissions to 775..."
chmod -R 775 "$PROJECT_DIR/storage"
chmod -R 775 "$PROJECT_DIR/bootstrap/cache"

# If running as root, set ownership for storage and cache
if [ "$EUID" -eq 0 ]; then
    echo "Setting storage and cache ownership to www-data:www-data..."
    chown -R www-data:www-data "$PROJECT_DIR/storage"
    chown -R www-data:www-data "$PROJECT_DIR/bootstrap/cache"
else
    echo "To set ownership for storage/cache, run:"
    echo "  sudo chown -R www-data:www-data $PROJECT_DIR/storage"
    echo "  sudo chown -R www-data:www-data $PROJECT_DIR/bootstrap/cache"
fi

# Make scripts executable
echo "Making shell scripts executable..."
find "$PROJECT_DIR" -name "*.sh" -exec chmod +x {} \;

echo ""
echo "✅ Permissions set successfully!"
echo ""
echo "Summary:"
echo "  - Directories: 755"
echo "  - Files: 644"
echo "  - Storage: 775"
echo "  - Bootstrap/cache: 775"
echo "  - Artisan: executable"
echo ""
echo "If you need to set ownership, run as root:"
echo "  sudo chown -R www-data:www-data $PROJECT_DIR"

