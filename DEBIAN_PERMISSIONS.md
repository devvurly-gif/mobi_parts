# Debian Server Permissions Setup Guide

This guide explains how to set proper permissions for the Laravel application on a Debian server.

## Quick Setup (Recommended)

1. **Navigate to your project directory:**
   ```bash
   cd /var/www/html/mobi_parts
   # or wherever your project is located
   ```

2. **Run the setup script:**
   ```bash
   chmod +x set-permissions.sh
   sudo ./set-permissions.sh
   ```

## Manual Setup

### Step 1: Set Ownership

Set the web server user as the owner (usually `www-data` for Apache/Nginx):

```bash
sudo chown -R www-data:www-data /var/www/html/mobi_parts
```

**Alternative:** If you're using a different user (e.g., your username):
```bash
sudo chown -R $USER:$USER /var/www/html/mobi_parts
```

### Step 2: Set Directory Permissions

Set all directories to 755:
```bash
sudo find /var/www/html/mobi_parts -type d -exec chmod 755 {} \;
```

### Step 3: Set File Permissions

Set all files to 644:
```bash
sudo find /var/www/html/mobi_parts -type f -exec chmod 644 {} \;
```

### Step 4: Set Writable Directories

Laravel needs write access to `storage` and `bootstrap/cache`:

```bash
sudo chmod -R 775 /var/www/html/mobi_parts/storage
sudo chmod -R 775 /var/www/html/mobi_parts/bootstrap/cache
```

Ensure ownership is correct:
```bash
sudo chown -R www-data:www-data /var/www/html/mobi_parts/storage
sudo chown -R www-data:www-data /var/www/html/mobi_parts/bootstrap/cache
```

### Step 5: Make Artisan Executable

```bash
sudo chmod +x /var/www/html/mobi_parts/artisan
```

## One-Line Command (Alternative)

If you prefer a single command:

```bash
cd /var/www/html/mobi_parts && \
sudo chown -R www-data:www-data . && \
sudo find . -type d -exec chmod 755 {} \; && \
sudo find . -type f -exec chmod 644 {} \; && \
sudo chmod -R 775 storage bootstrap/cache && \
sudo chmod +x artisan
```

## Verify Permissions

Check if permissions are set correctly:

```bash
ls -la /var/www/html/mobi_parts/storage
ls -la /var/www/html/mobi_parts/bootstrap/cache
```

You should see:
- `storage/` and `bootstrap/cache/` directories with `drwxrwxr-x` (775)
- Files inside with `-rw-rw-r--` (664) or `-rw-r--r--` (644)

## Troubleshooting

### Permission Denied Errors

If you see permission errors:

1. **Check current permissions:**
   ```bash
   ls -la /var/www/html/mobi_parts/storage
   ```

2. **Check web server user:**
   ```bash
   ps aux | grep -E 'apache|nginx|httpd' | head -1
   ```
   This shows which user the web server runs as (usually `www-data`).

3. **Add your user to www-data group (optional):**
   ```bash
   sudo usermod -a -G www-data $USER
   ```
   Then log out and back in for changes to take effect.

### Storage Link Issues

If `storage:link` command fails:

```bash
sudo php artisan storage:link
```

### SELinux (if enabled)

If SELinux is enabled, you may need to set context:

```bash
sudo chcon -R -t httpd_sys_rw_content_t /var/www/html/mobi_parts/storage
sudo chcon -R -t httpd_sys_rw_content_t /var/www/html/mobi_parts/bootstrap/cache
```

## Security Notes

- **Never use 777 permissions** - this is a security risk
- **Recommended:** Use 775 for directories that need write access
- **Files should be 644** - readable by all, writable by owner only
- **Keep ownership consistent** - all files should be owned by the same user/group

## Common Web Server Users

- **Apache:** `www-data` (Debian/Ubuntu) or `apache` (CentOS/RHEL)
- **Nginx:** `www-data` (Debian/Ubuntu) or `nginx` (CentOS/RHEL)

## Quick Reference

| Location | Permission | Owner |
|----------|-----------|-------|
| All directories | 755 | www-data:www-data |
| All files | 644 | www-data:www-data |
| storage/ | 775 | www-data:www-data |
| bootstrap/cache/ | 775 | www-data:www-data |
| artisan | 755 | www-data:www-data |

