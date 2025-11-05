#!/bin/bash

# Fix Storage Symlink and Image URLs on VPS
# Run this script from the Laravel project root

echo "🔧 Fixing Storage Symlink and Image URLs on VPS..."

# Get the current directory
PROJECT_DIR=$(pwd)

# Check if we're in the right directory
if [ ! -f "artisan" ]; then
    echo "❌ Error: artisan file not found. Please run this script from the Laravel project root."
    exit 1
fi

# Step 1: Check if .env exists
if [ ! -f ".env" ]; then
    echo "❌ Error: .env file not found!"
    echo "Please create .env file first."
    exit 1
fi

# Step 2: Get APP_URL from .env
APP_URL=$(grep "^APP_URL=" .env | cut -d '=' -f2 | tr -d '"' | tr -d "'")
if [ -z "$APP_URL" ]; then
    echo "⚠️  Warning: APP_URL not found in .env"
    echo "Please set APP_URL in .env file (e.g., APP_URL=http://167.172.123.88)"
    read -p "Enter your APP_URL (e.g., http://167.172.123.88): " APP_URL
    if [ ! -z "$APP_URL" ]; then
        # Update .env file
        if grep -q "^APP_URL=" .env; then
            sed -i "s|^APP_URL=.*|APP_URL=$APP_URL|" .env
        else
            echo "APP_URL=$APP_URL" >> .env
        fi
        echo "✅ Updated APP_URL in .env"
    fi
else
    echo "✅ Found APP_URL: $APP_URL"
fi

# Step 3: Remove existing symlink if it exists
if [ -L "public/storage" ]; then
    echo "Removing existing storage symlink..."
    rm public/storage
fi

# Step 4: Create storage symlink
echo "Creating storage symlink..."
php artisan storage:link

if [ $? -eq 0 ]; then
    echo "✅ Storage symlink created successfully!"
else
    echo "⚠️  Warning: php artisan storage:link failed"
    echo "Trying manual symlink creation..."
    
    # Manual symlink creation
    if [ -d "storage/app/public" ]; then
        ln -s ../storage/app/public public/storage
        if [ $? -eq 0 ]; then
            echo "✅ Manual symlink created successfully!"
        else
            echo "❌ Failed to create symlink manually"
            echo "Trying alternative: copying files..."
            
            # Alternative: Copy files if symlink doesn't work
            if [ ! -d "public/storage" ]; then
                mkdir -p public/storage
                cp -r storage/app/public/* public/storage/
                echo "✅ Copied storage files to public/storage"
            fi
        fi
    else
        echo "❌ Error: storage/app/public directory not found!"
        exit 1
    fi
fi

# Step 5: Set permissions
echo "Setting storage permissions..."
chmod -R 775 storage
chmod -R 775 public/storage 2>/dev/null || true
chmod -R 775 bootstrap/cache

# Step 6: Clear config cache
echo "Clearing config cache..."
php artisan config:clear
php artisan cache:clear

# Step 7: Verify symlink
if [ -L "public/storage" ] || [ -d "public/storage" ]; then
    echo ""
    echo "✅ Verification:"
    ls -la public/ | grep storage
    echo ""
    echo "✅ Storage setup complete!"
    echo ""
    echo "📋 Summary:"
    echo "  - APP_URL: $APP_URL"
    echo "  - Storage symlink: $(ls -la public/storage 2>/dev/null | head -1 || echo 'Not found')"
    echo "  - Image URLs should work at: $APP_URL/storage/products/..."
    echo ""
    echo "🧪 Test by visiting: $APP_URL/storage/products/[any-image-name].jpg"
else
    echo "❌ Error: Storage symlink verification failed!"
    exit 1
fi

