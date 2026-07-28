# MyOKUcare System Documentation

## Purpose

MyOKUcare centralises OKU registration, career information, employment opportunities, welfare cases and JKM monitoring in one role-based application.

## Application Flow

1. A public user opens the welcome page.
2. The user registers with e-mail, password and a supported public role.
3. An OKU user enters required personal details during registration.
4. After first login, an OKU user uploads a Kad OKU image.
5. JKM staff review and update verification status.
6. Employers and authorised staff manage inclusive job opportunities.
7. OKU users review matching jobs and register interest.
8. OKU users submit welfare applications for their linked profile.
9. JKM staff process cases and schedule reviews.
10. Authorised roles view reports and current dashboard statistics.

## Authentication

- GET login route: `login`
- POST login route: `login.store`
- Registration routes: `register`, `register.store`
- All roles authenticate using e-mail and password.
- Sessions regenerate after successful login.
- Inactive accounts are rejected.
- Failed login attempts are rate-limited by e-mail and IP.
- Deactivated accounts are blocked on subsequent requests, including an existing session.
- Password recovery, SMS codes and passkeys are planned but not yet implemented; see `docs/AUTHENTICATION_RECOVERY_PLAN.md`.

## Data and Services

- `OkuDataService`: common OKU queries/statistics.
- `OkuImportService`: CSV/XLSX validation and importing.
- `EmploymentReportService`: employment reporting.
- `WelfareReportService`: welfare reporting.
- `AuditService`: administrative activity logging.

## Frontend Structure

- `resources/views/layout.blade.php` is the shared authenticated shell.
- Role dashboards live in `resources/views/dashboard/`.
- Reusable icons use `resources/views/components/dashboard-icon.blade.php`.
- `resources/css/app.css` contains authenticated application styles.
- `resources/css/auth.css` contains login styles.
- `resources/css/landing.css` contains public landing styles.
- `resources/js/app.js` contains PWA, accessibility, form and shared interactions.

## PWA Behavior

- Manifest: `public/manifest.webmanifest`
- Service worker: `public/sw.js`
- Offline fallback: `public/offline.html`
- Mobile browser: bottom navigation hidden.
- Installed standalone PWA: role-specific bottom navigation visible.
- Install prompt disappears after installation. Manual dismissal is currently session-only unless dismissal persistence is added.
- Authenticated pages and API responses are network-only and are not stored by the service worker.
- The service worker caches only the public offline page and static assets.
- The PWA shares Laravel's server-side session cookie with the website; it never stores passwords in browser storage.

## Security Notes

- Keep `.env` out of Git.
- Use HTTPS in production.
- Run authorization checks for every sensitive route.
- Treat IC numbers, Kad OKU images and welfare data as sensitive personal data.
- Restrict uploaded file types and storage access.
- Configure production logs without exposing personal data.

## Useful Commands

```powershell
composer install
npm install
php artisan key:generate
php artisan migrate
npm run build
php artisan test
php artisan optimize
```

For local development:

```powershell
composer run dev
```
