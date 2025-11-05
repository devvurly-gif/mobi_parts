# Fix Storage Logs Permission Denied Error on VPS

This error occurs when the web server cannot write to the `storage/logs/laravel.log` file.

## Quick Fix

### Option 1: Automated Script

```bash
cd /var/www/html/mobi_parts
chmod +x fix-storage-logs-permissions.sh
sudo ./fix-storage-logs-permissions.sh
```

### Option 2: Manual Commands

Run these commands on your VPS:

```bash
cd /var/www/html/mobi_parts

# Set permissions for storage directory
sudo chmod -R 775 storage
sudo chmod -R 775 storage/logs

# Create log file if it doesn't exist
sudo touch storage/logs/laravel.log
sudo chmod 664 storage/logs/laravel.log

# Set ownership to web server user
sudo chown -R www-data:www-data storage
sudo chown www-data:www-data storage/logs/laravel.log
```

## One-Line Command

```bash
cd /var/www/html/mobi_parts && sudo chmod -R 775 storage && sudo chown -R www-data:www-data storage && sudo touch storage/logs/laravel.log && sudo chmod 664 storage/logs/laravel.log && sudo chown www-data:www-data storage/logs/laravel.log
```

## Verify Permissions

After fixing, verify:

```bash
ls -la storage/logs/
```

You should see:
```
-rw-rw-r-- 1 www-data www-data ... laravel.log
```

The permissions should be `664` and owner should be `www-data:www-data`.

## Common Issues

### Issue: Still getting permission denied

**Solution:** Check if SELinux is enabled and set context:

```bash
# Check SELinux status
getenforce

# If enforcing, set context
sudo chcon -R -t httpd_sys_rw_content_t storage/logs
```

### Issue: Log file doesn't exist

**Solution:** Create it manually:

```bash
sudo touch storage/logs/laravel.log
sudo chmod 664 storage/logs/laravel.log
sudo chown www-data:www-data storage/logs/laravel.log
```

### Issue: Web server user is different

**Solution:** Find your web server user:

```bash
# For Apache
ps aux | grep apache | head -1

# For Nginx
ps aux | grep nginx | head -1
```

Then replace `www-data` with your web server user in the chown commands.

## Complete Storage Permissions Fix

To fix all storage-related permissions:

```bash
cd /var/www/html/mobi_parts

# Storage directory
sudo chmod -R 775 storage
sudo chown -R www-data:www-data storage

# Bootstrap cache
sudo chmod -R 775 bootstrap/cache
sudo chown -R www-data:www-data bootstrap/cache

# Ensure log file exists and is writable
sudo touch storage/logs/laravel.log
sudo chmod 664 storage/logs/laravel.log
sudo chown www-data:www-data storage/logs/laravel.log
```

## Test After Fix

Try accessing your application:

```bash
curl http://167.172.123.88
```

Or check if you can write to the log:

```bash
sudo -u www-data php artisan tinker --execute="Log::info('Test log entry');"
```

Then check:
```bash
tail storage/logs/laravel.log
```

You should see the test log entry.

