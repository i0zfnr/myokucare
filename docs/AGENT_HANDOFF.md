# MyOKUcare Agent Handoff

Last updated: 27 July 2026
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
- PHPUnit 12

## Main Roles

| Role | Purpose |
|---|---|
| `super_admin` | Pentadbir: complete administration, users, audit and system reports |
| `jkm_officer` | OKU records, employers, jobs, welfare and reports |
| `employer` | Employer and job management |
| `oku_user` | Career profile, Kad OKU verification, job and welfare access |

All roles log in using e-mail and their own password. Dashboard routing is automatic based on role.

## Implemented

- Public welcome, login and registration pages
- Role-aware authentication and active-account checks
- Login throttling by e-mail and IP
- Active-session revocation for deactivated accounts
- Separate dashboard view for every role
- Live dashboard polling for current OKU statistics
- OKU CRUD, search, filtering, CSV/XLSX import and CSV export
- Career profile and Kad OKU image upload/verification
- Employer and job management
- Job matching and interest workflow
- Welfare application, status and review scheduling
- Employment and welfare reports with exports
- Pentadbir user management, profile, settings and audit activity
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

- No password-reset, SMS-code or passkey workflow. The approved next-phase design is in `docs/AUTHENTICATION_RECOVERY_PLAN.md`.
- No MyDigital ID integration.
- E-mail verification is not a complete real-world verification flow.
- Firebase Cloud Messaging is implemented for installed PWA users but remains configuration-gated; see `docs/FIREBASE_PUSH_NOTIFICATIONS.md`.
- Dashboard “real time” uses polling, not WebSockets.
- No production privacy-policy or terms pages.
- Automated coverage is useful but still limited for the size of the system.
- Full physical-device and cross-browser UAT is still required.
- Production hosting, SSL, queue worker, scheduler, backups and monitoring are not yet confirmed.

## Client Discussion and Agreed Scope

The following decisions and cautions were agreed during the client discussion:

- The system may be presented as a **functional prototype / pilot system**, but it must not yet be described as fully production-ready.
- The initial government-hosting estimate is for **500 users in Kota Putera only**.
- The current Ryaze footprint of **215.8 MB** is only the application baseline and is not a production-capacity figure.
- Expected storage for 500 users is approximately **8–12 GB** under normal use, **22–30 GB** under high use, and **40–45 GB** in an upload-heavy scenario.
- Government hosting should provide **100 GB encrypted live storage** (50 GB absolute minimum), plus **150–300 GB of separate encrypted backup capacity**. Start with approximately **4 vCPU and 8 GB RAM**, then confirm through load testing.
- Until security remediation and independent retesting are complete, demonstrations and user trials must use **fake or synthetic data only**. Do not collect real IC numbers, disability records, identity documents, addresses, salaries or welfare records.
- Earlier security testing reported broken authorization and identity-verification risks. A passing attack test means the simulated attack succeeded; it does not mean the system passed security. Production approval requires each finding to be fixed and followed by negative authorization tests and a fresh security assessment.
- OKU registration must enforce a normalized, database-level unique identity number. A duplicate identity number must not create a second account; it must enter a controlled identity-verification or account-recovery process.
- A PWA login session must not be used as a substitute for password recovery. Sessions need server-side expiry, secure cookies, logout/revocation, deactivated-account enforcement and reauthentication for sensitive actions.
- The next implementation must include accessible recovery options: email password reset, one-time SMS code where approved, passkey/device biometrics as a longer-term option, and JKM-assisted recovery. Officers must never see or retrieve a user's old password.
- The project is planned to start in **February**; the exact year, implementation milestones and production launch date still require client confirmation.
- A Bahasa Melayu storage-estimate document for this scope is maintained at `docs/Anggaran_Storan_MyOKUcare_500_Pengguna_Kota_Putera_BM.docx`.

These are planning estimates, not guaranteed capacity. Final sizing requires measured database growth, upload limits, retention rules, concurrent-user load testing and the government hosting platform's backup policy.

## Recommended Next Work

1. Complete UAT using `docs/UAT_CHECKLIST.md`.
2. Implement the accessible recovery plan: email reset, JKM-assisted recovery, followed by approved SMS and passkey options.
3. Add production notification delivery and scheduled reminders.
4. Expand feature and authorization tests.
5. Complete privacy, retention and security review for sensitive OKU data.
6. Perform deployment using `docs/DEPLOYMENT_CHECKLIST.md`.
7. Push the current uncommitted changes after final visual verification.

## Current Verification Baseline

At the time of this handoff:

- `php artisan test`: 71 tests passed
- Assertions: 453 passed
- `npm run build`: passed
- Blade view compilation: passed
- Composer and npm production audits: 0 known vulnerabilities

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
