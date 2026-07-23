# Deployment Checklist

## Before Deployment

- [ ] UAT signed off
- [ ] Production database created
- [ ] HTTPS certificate configured
- [ ] `.env` prepared securely
- [ ] `APP_ENV=production`
- [ ] `APP_DEBUG=false`
- [ ] Correct `APP_URL`
- [ ] Strong `APP_KEY`
- [ ] Production mail settings
- [ ] Database backup completed
- [ ] Upload storage backup completed
- [ ] Queue and scheduler strategy confirmed

## Build and Release

```powershell
composer install --no-dev --optimize-autoloader
npm ci
npm run build
php artisan migrate --force
php artisan storage:link
php artisan optimize
php artisan test
```

- [ ] Web root points to `public`
- [ ] `storage` and `bootstrap/cache` are writable
- [ ] Queue worker configured if queues are enabled
- [ ] Scheduler runs `php artisan schedule:run` every minute
- [ ] Log rotation and monitoring configured

## Smoke Test

- [ ] Welcome, login and registration load
- [ ] Every role reaches the correct dashboard
- [ ] Inactive users cannot log in
- [ ] OKU record CRUD works
- [ ] CSV/XLSX import works
- [ ] Kad OKU upload and review works
- [ ] Employer and job workflows work
- [ ] Welfare submission and processing work
- [ ] Reports and exports work
- [ ] PWA manifest, service worker and offline page load
- [ ] Mobile browser navigation works
- [ ] Installed-PWA bottom navigation works

## After Deployment

- [ ] Remove maintenance mode
- [ ] Record deployed Git commit
- [ ] Monitor errors and slow requests
- [ ] Verify automated backup
- [ ] Confirm rollback procedure
