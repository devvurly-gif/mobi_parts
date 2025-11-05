#!/bin/bash

# Fix Product Image URLs on VPS
# This script ensures APP_URL is set correctly and clears caches

echo "🔧 Fixing Product Image URLs on VPS..."

PROJECT_DIR=$(pwd)

# Check if we're in the right directory
if [ ! -f "artisan" ]; then
    echo "❌ Error: artisan file not found. Please run this script from the Laravel project root."
    exit 1
fi

# Step 1: Check and update APP_URL
echo "Checking APP_URL in .env..."
if [ ! -f ".env" ]; then
    echo "❌ Error: .env file not found!"
    exit 1
fi

# Get current APP_URL
CURRENT_APP_URL=$(grep "^APP_URL=" .env | cut -d '=' -f2 | tr -d '"' | tr -d "'" | tr -d ' ')

if [ -z "$CURRENT_APP_URL" ] || [ "$CURRENT_APP_URL" = "http://localhost" ]; then
    echo "⚠️  APP_URL is not set or is localhost"
    read -p "Enter your VPS URL (e.g., http://167.172.123.88): " NEW_APP_URL
    
    if [ ! -z "$NEW_APP_URL" ]; then
        # Update .env file
        if grep -q "^APP_URL=" .env; then
            sed -i "s|^APP_URL=.*|APP_URL=$NEW_APP_URL|" .env
        else
            echo "APP_URL=$NEW_APP_URL" >> .env
        fi
        echo "✅ Updated APP_URL to: $NEW_APP_URL"
    fi
else
    echo "✅ APP_URL is set to: $CURRENT_APP_URL"
fi

# Step 2: Clear all caches
echo ""
echo "Clearing Laravel caches..."
php artisan config:clear
php artisan cache:clear
php artisan route:clear
php artisan view:clear

# Step 3: Rebuild config cache
echo "Rebuilding config cache..."
php artisan config:cache

# Step 4: Verify storage symlink
echo ""
echo "Checking storage symlink..."
if [ -L "public/storage" ] || [ -d "public/storage" ]; then
    echo "✅ Storage symlink/directory exists"
    ls -la public/ | grep storage
else
    echo "⚠️  Storage symlink not found. Creating..."
    php artisan storage:link
fi

# Step 5: Test URL generation
echo ""
echo "Testing image URL generation..."
php artisan tinker --execute="
\$path = 'products/test.jpg';
echo 'Storage URL: ' . Storage::disk('public')->url(\$path) . PHP_EOL;
echo 'APP_URL: ' . config('app.url') . PHP_EOL;
echo 'Expected full URL: ' . config('app.url') . '/storage/' . \$path . PHP_EOL;
"

echo ""
echo "✅ Image URL fix complete!"
echo ""
echo "📋 Next steps:"
echo "  1. Verify APP_URL in .env: $(grep "^APP_URL=" .env | cut -d '=' -f2)"
echo "  2. Test an image URL in browser"
echo "  3. Check product API response for correct image_url"
echo ""
echo "🧪 Test URL format:"
echo "  http://167.172.123.88/storage/products/[image-name].jpg"

