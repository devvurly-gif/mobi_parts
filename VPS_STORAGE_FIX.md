# Fix Storage Symlink and Image URLs on VPS

This guide helps you fix the storage symlink issue on your VPS so images load correctly.

## Problem

Images are not loading because:
1. Storage symlink (`public/storage`) doesn't exist
2. `APP_URL` in `.env` is incorrect or not set
3. Permissions are incorrect

## Quick Fix

### Option 1: Automated Script (Recommended)

1. **SSH into your VPS:**
   ```bash
   ssh root@167.172.123.88
   # or your username
   ```

2. **Navigate to your project:**
   ```bash
   cd /var/www/html/mobi_parts
   # or wherever your project is located
   ```

3. **Run the fix script:**
   ```bash
   chmod +x fix-storage-vps.sh
   ./fix-storage-vps.sh
   ```

### Option 2: Manual Fix

#### Step 1: Update APP_URL in .env

```bash
cd /var/www/html/mobi_parts
nano .env
```

Update or add:
```env
APP_URL=http://167.172.123.88
```

Or if using HTTPS:
```env
APP_URL=https://167.172.123.88
```

Save and exit (Ctrl+X, then Y, then Enter).

#### Step 2: Create Storage Symlink

```bash
php artisan storage:link
```

If this fails, try manual symlink:
```bash
cd public
ln -s ../storage/app/public storage
cd ..
```

**Alternative:** If symlinks don't work (some shared hosting), copy files:
```bash
mkdir -p public/storage
cp -r storage/app/public/* public/storage/
```

#### Step 3: Set Permissions

```bash
chmod -R 775 storage
chmod -R 775 public/storage
chmod -R 775 bootstrap/cache
```

If running as root, set ownership:
```bash
chown -R www-data:www-data storage
chown -R www-data:www-data public/storage
chown -R www-data:www-data bootstrap/cache
```

#### Step 4: Clear Cache

```bash
php artisan config:clear
php artisan cache:clear
php artisan route:clear
php artisan view:clear
```

#### Step 5: Verify

Check if symlink exists:
```bash
ls -la public/ | grep storage
```

You should see:
```
lrwxrwxrwx 1 www-data www-data ... storage -> ../storage/app/public
```

Or check if directory exists:
```bash
ls -la public/storage/products/
```

## Verify Image URLs

After fixing, test image URLs:

1. **Check a product image exists:**
   ```bash
   ls -la storage/app/public/products/
   ```

2. **Test URL in browser:**
   ```
   http://167.172.123.88/storage/products/FGmh6jShWYDtdaIfLvEDCgh844BXfSMrqPttmieM.jpg
   ```

3. **Check API response:**
   ```bash
   curl http://167.172.123.88/api/products | jq '.[0].primary_image.image_url'
   ```

## Troubleshooting

### Issue: Symlink Permission Denied

**Solution:**
```bash
# Remove existing symlink
rm public/storage

# Create with proper permissions
sudo php artisan storage:link

# Or manually
cd public
sudo ln -s ../storage/app/public storage
sudo chown www-data:www-data storage
```

### Issue: Images Still Not Loading

**Check:**
1. **APP_URL is correct:**
   ```bash
   grep APP_URL .env
   ```

2. **Symlink exists:**
   ```bash
   ls -la public/storage
   ```

3. **Files exist:**
   ```bash
   ls -la storage/app/public/products/
   ```

4. **Web server can read:**
   ```bash
   sudo -u www-data ls -la public/storage/products/
   ```

### Issue: 404 Not Found

If images return 404:

1. **Check web server configuration** (Nginx/Apache) allows following symlinks
2. **Verify file exists:**
   ```bash
   test -f storage/app/public/products/FGmh6jShWYDtdaIfLvEDCgh844BXfSMrqPttmieM.jpg && echo "File exists" || echo "File missing"
   ```

3. **Check web server error logs:**
   ```bash
   # Nginx
   tail -f /var/log/nginx/error.log
   
   # Apache
   tail -f /var/log/apache2/error.log
   ```

### Issue: Symlink Not Allowed (Shared Hosting)

Some shared hosting doesn't allow symlinks. Use copy method:

```bash
# Create directory
mkdir -p public/storage

# Copy files
cp -r storage/app/public/* public/storage/

# Set permissions
chmod -R 755 public/storage

# Create a script to sync periodically (optional)
cat > sync-storage.sh << 'EOF'
#!/bin/bash
rsync -av storage/app/public/ public/storage/
EOF
chmod +x sync-storage.sh
```

## Nginx Configuration

If using Nginx, ensure your config allows following symlinks:

```nginx
server {
    # ... other config ...
    
    location / {
        try_files $uri $uri/ /index.php?$query_string;
    }
    
    location /storage {
        alias /var/www/html/mobi_parts/public/storage;
        expires 30d;
        add_header Cache-Control "public, immutable";
    }
}
```

## Apache Configuration

If using Apache with `.htaccess`, ensure it allows following symlinks:

```apache
<IfModule mod_rewrite.c>
    Options +FollowSymLinks
    RewriteEngine On
    # ... other rules ...
</IfModule>
```

## Verify Everything Works

Run these commands to verify:

```bash
# 1. Check APP_URL
echo "APP_URL: $(grep APP_URL .env)"

# 2. Check symlink
echo "Symlink: $(ls -la public/ | grep storage)"

# 3. Check files
echo "Files: $(ls storage/app/public/products/ | wc -l) product images"

# 4. Test URL generation
php artisan tinker --execute="echo config('filesystems.disks.public.url');"
```

## Quick Reference

| Command | Purpose |
|---------|---------|
| `php artisan storage:link` | Create storage symlink |
| `ls -la public/storage` | Verify symlink exists |
| `chmod -R 775 storage` | Set storage permissions |
| `php artisan config:clear` | Clear config cache |
| `grep APP_URL .env` | Check APP_URL setting |

## After Fix

Once fixed, your image URLs should work:
- ✅ `http://167.172.123.88/storage/products/image.jpg`
- ✅ Images load in the Vue app
- ✅ API returns correct `image_url` in responses

