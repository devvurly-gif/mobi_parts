# Laravel + Vue.js Deployment Guide for Shared Hosting

## Prerequisites Checklist

Before deployment, ensure your shared hosting supports:
- ✅ PHP 8.1 or higher
- ✅ Composer (or ability to upload vendor folder)
- ✅ Node.js & NPM (for building assets, or build locally)
- ✅ MySQL/MariaDB database
- ✅ SSH access (recommended) or FTP/SFTP
- ✅ mod_rewrite enabled (for `.htaccess`)

---

## Step 1: Prepare Your Project Locally

### 1.1 Build Production Assets
```bash
# Install dependencies
npm install

# Build for production
npm run build
```

This creates optimized files in `public/build/` directory.

### 1.2 Optimize Laravel
```bash
# Clear and cache config
php artisan config:cache
php artisan route:cache
php artisan view:cache

# Generate application key (if not set)
php artisan key:generate
```

### 1.3 Prepare .env for Production
Create a production `.env` file with:
```env
APP_NAME="MobiPart Apps"
APP_ENV=production
APP_KEY=base64:YOUR_GENERATED_KEY
APP_DEBUG=false
APP_URL=https://yourdomain.com

DB_CONNECTION=mysql
DB_HOST=localhost
DB_PORT=3306
DB_DATABASE=your_database_name
DB_USERNAME=your_database_user
DB_PASSWORD=your_database_password

# Storage configuration
FILESYSTEM_DISK=public

# Session driver (use database on shared hosting)
SESSION_DRIVER=database
```

---

## Step 2: Upload Files to Shared Hosting

### 2.1 Files to Upload

**Upload these files/folders:**
```
├── app/
├── bootstrap/
├── config/
├── database/
├── public/          # ⚠️ Important: contents go to public_html or www
├── resources/
├── routes/
├── storage/         # ⚠️ Create with proper permissions
├── vendor/           # Upload or install via Composer
├── artisan
├── composer.json
├── composer.lock
└── .env              # ⚠️ Create on server (don't upload from local)
```

**DO NOT upload:**
- `node_modules/`
- `.git/`
- `.env` (create new on server)
- `tests/`
- `storage/logs/*` (create empty directory)

### 2.2 Shared Hosting Structure Options

**Option A: Standard Laravel Structure (Recommended if you have access to parent directory)**
```
/home/username/
├── mobi_part_apps/
│   ├── app/
│   ├── public/          ← Symlink from public_html
│   ├── storage/
│   └── ...
└── public_html/         ← Symlink to mobi_part_apps/public
```

**Option B: Shared Hosting Constraint (public_html must be root)**
```
/home/username/public_html/
├── index.php           ← From Laravel's public/index.php
├── .htaccess           ← From Laravel's public/.htaccess
├── build/              ← From Laravel's public/build/
├── storage/            ← Symlink to ../storage/app/public
└── ... (other public files)

/home/username/
├── app/
├── bootstrap/
├── config/
├── storage/
├── vendor/
└── ... (other Laravel files)
```

---

## Step 3: Configure Directory Structure

### 3.1 Move Public Files (If Required by Host)

If your host requires `public_html` as root:

1. **Create parent directory structure:**
```bash
/home/username/
├── laravel_app/        # All Laravel files except public/
└── public_html/        # Only contents of public/ folder
```

2. **Move public contents:**
```bash
# Move everything from public/ to public_html/
mv public/* public_html/
mv public/.htaccess public_html/
```

3. **Update `public_html/index.php`:**
Change these lines:
```php
require __DIR__.'/../vendor/autoload.php';
$app = require_once __DIR__.'/../bootstrap/app.php';
```
To:
```php
require __DIR__.'/../laravel_app/vendor/autoload.php';
$app = require_once __DIR__.'/../laravel_app/bootstrap/app.php';
```

---

## Step 4: Set File Permissions

Via SSH (if available):
```bash
# Navigate to project root
cd /home/username/laravel_app

# Set storage permissions
chmod -R 775 storage
chmod -R 775 bootstrap/cache

# Set ownership (adjust user/group as needed)
chown -R username:username storage
chown -R username:username bootstrap/cache
```

Via FTP:
- Set `storage/` folder permissions to `775`
- Set `bootstrap/cache/` folder permissions to `775`
- Set files in `storage/` to `664`

---

## Step 5: Database Setup

### 5.1 Create Database
1. Log into your hosting control panel (cPanel, Plesk, etc.)
2. Create a MySQL database
3. Create a database user and assign privileges
4. Note the credentials

### 5.2 Run Migrations
Via SSH:
```bash
php artisan migrate --force
```

Or via database import:
- Export your local database
- Import via phpMyAdmin or similar

### 5.3 Create Storage Symlink
```bash
php artisan storage:link
```

If symlink doesn't work, copy `storage/app/public/` contents to `public/storage/`

---

## Step 6: Environment Configuration

### 6.1 Create .env File
On the server, create `.env` in project root with production values:

```env
APP_NAME="MobiPart Apps"
APP_ENV=production
APP_KEY=base64:XXXXXXXXXXXXX
APP_DEBUG=false
APP_URL=https://yourdomain.com

DB_CONNECTION=mysql
DB_HOST=localhost
DB_PORT=3306
DB_DATABASE=your_db_name
DB_USERNAME=your_db_user
DB_PASSWORD=your_db_password

SESSION_DRIVER=database
QUEUE_CONNECTION=sync
LOG_CHANNEL=daily

FILESYSTEM_DISK=public
```

### 6.2 Generate Application Key
```bash
php artisan key:generate
```

### 6.3 Clear and Cache
```bash
php artisan config:clear
php artisan config:cache
php artisan route:clear
php artisan route:cache
php artisan view:clear
php artisan view:cache
```

---

## Step 7: Install Dependencies

### 7.1 Install PHP Dependencies

**Option A: Via SSH (if Composer available)**
```bash
composer install --no-dev --optimize-autoloader
```

**Option B: Upload vendor folder**
- Upload entire `vendor/` folder from local (after `composer install --no-dev`)

### 7.2 Install NPM Dependencies (if building on server)
```bash
npm install --production
npm run build
```

**Recommended:** Build assets locally and upload `public/build/` folder.

---

## Step 8: Configure .htaccess

### 8.1 Public Directory .htaccess
Ensure `public/.htaccess` or `public_html/.htaccess` contains:
```apache
<IfModule mod_rewrite.c>
    <IfModule mod_negotiation.c>
        Options -MultiViews -Indexes
    </IfModule>

    RewriteEngine On

    # Handle Authorization Header
    RewriteCond %{HTTP:Authorization} .
    RewriteRule .* - [E=HTTP_AUTHORIZATION:%{HTTP:Authorization}]

    # Redirect Trailing Slashes If Not A Folder...
    RewriteCond %{REQUEST_FILENAME} !-d
    RewriteCond %{REQUEST_URI} (.+)/$
    RewriteRule ^ %1 [L,R=301]

    # Send Requests To Front Controller...
    RewriteCond %{REQUEST_FILENAME} !-d
    RewriteCond %{REQUEST_FILENAME} !-f
    RewriteRule ^ index.php [L]
</IfModule>
```

### 8.2 Root .htaccess (if needed)
If Laravel is not in root, add `.htaccess` in `public_html/`:
```apache
RewriteEngine On
RewriteRule ^(.*)$ /laravel_app/public/$1 [L]
```

---

## Step 9: Verify Deployment

### 9.1 Test Checklist
- [ ] Homepage loads
- [ ] Vue.js components render
- [ ] API routes work
- [ ] Images load correctly
- [ ] Database connections work
- [ ] File uploads work
- [ ] Storage symlink works
- [ ] No errors in browser console

### 9.2 Common Issues

**Issue: 500 Internal Server Error**
- Check `.env` file exists and has correct values
- Check file permissions
- Check error logs: `storage/logs/laravel.log`
- Enable `APP_DEBUG=true` temporarily to see errors

**Issue: Assets Not Loading**
- Verify `public/build/` exists with manifest.json
- Check `APP_URL` in `.env` matches your domain
- Clear browser cache

**Issue: Storage Files Not Accessible**
- Run `php artisan storage:link`
- Check `public/storage/` exists
- Verify symlink permissions

**Issue: Database Connection Failed**
- Verify database credentials in `.env`
- Check if database user has proper privileges
- Try connecting via phpMyAdmin first

---

## Step 10: Post-Deployment Optimization

### 10.1 Enable Maintenance Mode (Optional)
```bash
php artisan down
# Make changes
php artisan up
```

### 10.2 Set Up Cron Jobs
Most shared hosting requires adding cron via control panel:
```bash
* * * * * cd /path-to-your-project && php artisan schedule:run >> /dev/null 2>&1
```

### 10.3 Security Hardening
1. Set `APP_DEBUG=false` in production
2. Hide sensitive files with `.htaccess`:
```apache
<Files .env>
    Order allow,deny
    Deny from all
</Files>
```

### 10.4 Monitoring
- Monitor `storage/logs/laravel.log`
- Set up error tracking if needed
- Monitor disk space usage

---

## Quick Deployment Script

Create a deployment script for future updates:

```bash
#!/bin/bash
# deploy.sh

echo "Building assets..."
npm run build

echo "Uploading files..."
# Add your FTP/SCP upload commands here

echo "Running migrations..."
# php artisan migrate --force

echo "Clearing caches..."
# php artisan config:cache
# php artisan route:cache
# php artisan view:cache

echo "Deployment complete!"
```

---

## Alternative: Deploy Using Git

If your hosting supports Git:

```bash
# On server
cd /home/username/laravel_app
git pull origin main

# Install dependencies
composer install --no-dev --optimize-autoloader
npm run build

# Run migrations
php artisan migrate --force

# Clear caches
php artisan config:cache
php artisan route:cache
php artisan view:cache
```

---

## Summary Checklist

- [ ] Built production assets (`npm run build`)
- [ ] Created production `.env` file
- [ ] Uploaded all necessary files
- [ ] Set correct file permissions (775 for folders, 664 for files)
- [ ] Created database and configured connection
- [ ] Ran migrations
- [ ] Created storage symlink
- [ ] Cleared and cached configs
- [ ] Tested application
- [ ] Set up cron jobs (if needed)
- [ ] Disabled debug mode

---

## Support

For Laravel-specific hosting issues, refer to:
- [Laravel Deployment Documentation](https://laravel.com/docs/10.x/deployment)
- Your hosting provider's documentation
- Laravel forums and community

---

**Note:** Some shared hosting providers have specific requirements. Always check with your hosting provider for Laravel-specific instructions.

