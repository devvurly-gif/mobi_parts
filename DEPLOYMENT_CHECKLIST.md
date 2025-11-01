# Quick Deployment Checklist

## Pre-Deployment (Local)
- [ ] Run `npm run build` to compile assets
- [ ] Run `composer install --no-dev --optimize-autoloader`
- [ ] Set `APP_DEBUG=false` in `.env`
- [ ] Generate `APP_KEY` if not set: `php artisan key:generate`
- [ ] Test application locally with production settings

## File Upload
- [ ] Upload all Laravel files (except `node_modules`, `.git`, `tests`)
- [ ] Upload `vendor/` folder OR install via Composer on server
- [ ] Upload `public/build/` folder (compiled assets)
- [ ] Move `public/` contents to `public_html/` if required by host
- [ ] Update `public_html/index.php` paths if files were moved

## Server Configuration
- [ ] Create `.env` file on server with production values
- [ ] Set file permissions: `storage/` (775), `bootstrap/cache/` (775)
- [ ] Create database and user
- [ ] Update database credentials in `.env`
- [ ] Run `php artisan key:generate` on server
- [ ] Run `php artisan storage:link`
- [ ] Run `php artisan migrate --force`

## Cache & Optimization
- [ ] Run `php artisan config:cache`
- [ ] Run `php artisan route:cache`
- [ ] Run `php artisan view:cache`

## Testing
- [ ] Visit homepage - should load
- [ ] Check Vue.js components render
- [ ] Test API endpoints
- [ ] Verify images load
- [ ] Test file uploads (if applicable)
- [ ] Check browser console for errors

## Security
- [ ] Verify `APP_DEBUG=false`
- [ ] Protect `.env` file with `.htaccess`
- [ ] Remove unnecessary files from public directory

## Post-Deployment
- [ ] Set up cron jobs (if needed)
- [ ] Monitor error logs
- [ ] Test all critical features

---

**Quick Commands (run on server via SSH):**
```bash
# Install dependencies
composer install --no-dev --optimize-autoloader

# Generate key
php artisan key:generate

# Create storage link
php artisan storage:link

# Run migrations
php artisan migrate --force

# Clear and cache
php artisan config:clear && php artisan config:cache
php artisan route:clear && php artisan route:cache
php artisan view:clear && php artisan view:cache
```

