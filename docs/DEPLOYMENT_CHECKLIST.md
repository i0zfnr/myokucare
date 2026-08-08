# Deployment Checklist

## Before Deployment

Run the automated release gate on the target host:

```powershell
php artisan deployment:check
```

Do not continue while any check reports `FAIL`. This command validates configuration without printing credentials; it does not replace UAT, backup restoration or infrastructure monitoring checks.

- [ ] UAT signed off
- [ ] Production database created
- [ ] HTTPS certificate configured
- [ ] `.env` prepared securely
- [ ] `APP_ENV=production`
- [ ] `APP_DEBUG=false`
- [ ] Correct `APP_URL`
- [ ] Strong `APP_KEY`
- [ ] `SESSION_DRIVER=database`
- [ ] `SESSION_ENCRYPT=true`
- [ ] `SESSION_SECURE_COOKIE=true`
- [ ] `SESSION_HTTP_ONLY=true`
- [ ] `SESSION_SAME_SITE=lax`
- [ ] Session encryption and approved idle/remembered-session lifetimes confirmed
- [ ] Production mail settings
- [ ] Firebase Web App, VAPID key and protected service-account path configured
- [ ] Installed-PWA push tested for every role
- [ ] Database backup completed
- [ ] Upload storage backup completed
- [ ] Queue and scheduler strategy confirmed
- [ ] `php artisan deployment:check` passes on the production host

## Build and Release

```powershell
composer install --no-dev --optimize-autoloader
npm ci
npm run build
php artisan migrate --force
php artisan storage:link
php artisan optimize
php artisan test
php artisan deployment:check
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
- [ ] Deactivated users with existing web/API sessions are immediately blocked
- [ ] PWA does not cache authenticated pages, API responses or recovery secrets
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
