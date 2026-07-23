# MyOKUcare Agent Handoff

Last updated: 24 July 2026  
Repository: `https://github.com/i0zfnr/myokucare`  
Primary branch: `main`

## Read This First

MyOKUcare is a Laravel 13 role-based system for managing OKU profiles, inclusive employment, welfare applications and JKM reporting. The interface uses the coral brand colours `#FF9064` and `#FF6565`, supports desktop and mobile layouts, and can be installed as a PWA.

Before changing code:

1. Read `Document Context/PRD.md`, `Architecture.md`, `Rules.md`, `Schema.md` and `Design.md`.
2. Read `docs/SYSTEM_DOCUMENTATION.md`.
3. Run `git status --short` and preserve unrelated work.
4. Run `php artisan test` and `npm run build` before and after significant changes.
5. Do not add fake statistics, unsupported government integrations or routes that do not exist.

## Technology

- PHP 8.3+
- Laravel 13
- MySQL/MariaDB for normal local and production use
- Blade, Tailwind CSS 4 and custom CSS
- Vite 8
- Laravel Sanctum
- Spatie Laravel Permission
- Maatwebsite Excel
- PHPUnit 12

## Main Roles

| Role | Purpose |
|---|---|
| `super_admin` | Complete administration, users, audit and system reports |
| `jkm_officer` | OKU records, employers, jobs, welfare and reports |
| `employer` | Employer and job management |
| `oku_user` | Career profile, Kad OKU verification, job and welfare access |
| `family_member` | Job and welfare support access |
| `viewer` | Read-only reporting |

All roles log in using e-mail and their own password. Dashboard routing is automatic based on role.

## Implemented

- Public welcome, login and registration pages
- Role-aware authentication and active-account checks
- Login throttling by e-mail and IP
- Separate dashboard view for every role
- Live dashboard polling for current OKU statistics
- OKU CRUD, search, filtering, CSV/XLSX import and CSV export
- Career profile and Kad OKU image upload/verification
- Employer and job management
- Job matching and interest workflow
- Welfare application, status and review scheduling
- Employment and welfare reports with exports
- Super Admin user management, profile, settings and audit activity
- Responsive mobile interface and compact mobile layouts
- Fixed shared topbar and overlay mobile sidebar
- PWA manifest, service worker, offline page and install prompt
- Installed-PWA-only role-based bottom navigation
- Font scaling, high contrast, focus states and semantic labels
- New MyOKUcare logo across UI, favicon and PWA icons
- Single database SQL file: `database/myokucare.sql`

## Important Files

| Area | Location |
|---|---|
| Routes | `routes/web.php` |
| Shared authenticated layout | `resources/views/layout.blade.php` |
| Main styles | `resources/css/app.css` |
| Public landing styles | `resources/css/landing.css` |
| Authentication styles | `resources/css/auth.css` |
| Frontend behavior | `resources/js/app.js` |
| Dashboard polling | `resources/js/modules/dashboard-live.js` |
| Role dashboards | `resources/views/dashboard/` |
| Controllers | `app/Http/Controllers/` |
| Request validation | `app/Http/Requests/` |
| Services | `app/Services/` |
| Database migrations | `database/migrations/` |
| Full SQL | `database/myokucare.sql` |
| Feature tests | `tests/Feature/MyOkuCareTest.php` |
| PWA | `public/manifest.webmanifest`, `public/sw.js`, `public/offline.html` |

## Current UI Rules

- Keep the existing clean, non-glass design.
- Use coral only for primary actions and important active states.
- Keep text readable for staff aged approximately 25–60.
- Mobile touch targets should remain around 40–44px.
- On mobile browsers, use the pinned topbar and hamburger sidebar.
- In installed standalone PWA mode, show role-specific bottom navigation.
- When the sidebar opens on mobile, it covers the topbar.
- Avoid raw Unicode symbols for important icons; use `<x-dashboard-icon>`.

## Known Gaps

- No password-reset workflow.
- No MyDigital ID integration.
- E-mail verification is not a complete real-world verification flow.
- Push notifications are not connected to a server-side push provider.
- Dashboard “real time” uses polling, not WebSockets.
- No production privacy-policy or terms pages.
- Automated coverage is useful but still limited for the size of the system.
- Full physical-device and cross-browser UAT is still required.
- Production hosting, SSL, queue worker, scheduler, backups and monitoring are not yet confirmed.

## Recommended Next Work

1. Complete UAT using `docs/UAT_CHECKLIST.md`.
2. Add password reset and real e-mail verification.
3. Add production notification delivery and scheduled reminders.
4. Expand feature and authorization tests.
5. Complete privacy, retention and security review for sensitive OKU data.
6. Perform deployment using `docs/DEPLOYMENT_CHECKLIST.md`.
7. Push the current uncommitted changes after final visual verification.

## Current Verification Baseline

At the time of this handoff:

- `php artisan test`: 23 tests passed
- Assertions: 246 passed
- `npm run build`: passed
- Blade view compilation: passed

## Estimated Progress

Overall project progress: **82%**

| Area | Progress |
|---|---:|
| Architecture and database | 90% |
| Authentication and roles | 85% |
| OKU management | 90% |
| Employment module | 82% |
| Welfare module | 82% |
| Admin and reports | 85% |
| Responsive UI/accessibility | 88% |
| PWA | 85% |
| Automated testing | 65% |
| Production deployment/UAT | 45% |

The main workflows are functional. Remaining work is mostly production hardening, broader testing, formal UAT and external-service integration.
