#!/bin/bash

# Laravel + Vue.js Deployment Script
# Usage: ./deploy.sh

set -e  # Exit on error

echo "🚀 Starting deployment process..."

# Colors for output
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
RED='\033[0;31m'
NC='\033[0m' # No Color

# Step 1: Build assets
echo -e "${YELLOW}📦 Building production assets...${NC}"
npm run build

if [ $? -ne 0 ]; then
    echo -e "${RED}❌ Asset build failed!${NC}"
    exit 1
fi

echo -e "${GREEN}✅ Assets built successfully${NC}"

# Step 2: Install/Update Composer dependencies
echo -e "${YELLOW}📦 Installing PHP dependencies...${NC}"
composer install --no-dev --optimize-autoloader

if [ $? -ne 0 ]; then
    echo -e "${RED}❌ Composer install failed!${NC}"
    exit 1
fi

echo -e "${GREEN}✅ PHP dependencies installed${NC}"

# Step 3: Optimize Laravel
echo -e "${YELLOW}⚡ Optimizing Laravel...${NC}"

# Clear previous caches
php artisan config:clear
php artisan route:clear
php artisan view:clear

# Cache for production
php artisan config:cache
php artisan route:cache
php artisan view:cache

echo -e "${GREEN}✅ Laravel optimized${NC}"

# Step 4: Create list of files to upload
echo -e "${YELLOW}📝 Creating file list...${NC}"

# Files/folders to include
cat > .deploy-include.txt << EOF
app/
bootstrap/
config/
database/
public/
resources/
routes/
storage/
vendor/
artisan
composer.json
composer.lock
DEPLOYMENT_GUIDE.md
DEPLOYMENT_CHECKLIST.md
EOF

# Files/folders to exclude
cat > .deploy-exclude.txt << EOF
node_modules/
.git/
.env
.env.backup
.env.production
.env.local
tests/
.phpunit.result.cache
storage/logs/*
storage/framework/cache/*
storage/framework/sessions/*
storage/framework/views/*
README.md
deploy.sh
EOF

echo -e "${GREEN}✅ File lists created${NC}"

# Step 5: Display summary
echo ""
echo -e "${GREEN}━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━${NC}"
echo -e "${GREEN}✅ Deployment package ready!${NC}"
echo -e "${GREEN}━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━${NC}"
echo ""
echo "Next steps:"
echo "1. Upload files (excluding files in .deploy-exclude.txt)"
echo "2. Create .env file on server"
echo "3. Set file permissions (storage: 775, bootstrap/cache: 775)"
echo "4. Run: php artisan storage:link"
echo "5. Run: php artisan migrate --force"
echo "6. Clear and cache on server:"
echo "   php artisan config:cache"
echo "   php artisan route:cache"
echo "   php artisan view:cache"
echo ""
echo -e "${YELLOW}📄 See DEPLOYMENT_GUIDE.md for detailed instructions${NC}"
echo ""

# Cleanup
rm -f .deploy-include.txt .deploy-exclude.txt

echo -e "${GREEN}🎉 Deployment preparation complete!${NC}"

