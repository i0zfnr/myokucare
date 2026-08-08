# MyOKUcare Next Steps

Current estimated progress: **82%**
Target: production-ready release

## Current Verified Status

Measured on 24 July 2026:

- **88% functionally complete**
- **68% production-ready**
- 33 OKU records in the current local database
- 6 user accounts
- 1 employer record
- 1 job record
- 0 welfare applications
- 9 of 9 migrations applied
- 107 application routes registered
- Production frontend build passes
- 71 tests and 453 assertions pass
- 0 npm production dependency vulnerabilities
- 0 known Composer security advisories

Main production risks:

- Limited automated coverage for the application size
- Password reset and real email verification are incomplete
- Firebase push delivery is implemented but remains disabled until production project credentials and PWA device UAT are completed
- Physical-device and client UAT are incomplete
- Privacy, retention and sensitive-document security need formal review
- Production hosting, SSL, monitoring, backups and rollback are not verified

The functional percentage measures implemented workflows. The production-ready percentage is lower because security remediation, UAT and deployment operations are mandatory before handling real sensitive OKU data.

## Objective

Complete testing, authentication, notifications, security review and production deployment without adding unnecessary design changes.

## Phase 1 — User Acceptance Testing

Priority: Critical

1. Create test accounts for every role:
   - Pentadbir
   - JKM Officer
   - Employer
   - OKU User
2. Test every item in `docs/UAT_CHECKLIST.md`.
3. Test at minimum on:
   - Android phone, approximately 360×800
   - iPhone, approximately 390×844
   - Tablet, approximately 768×1024
   - Laptop, approximately 1366×768
   - Desktop, 1920×1080
4. Record each issue with:
   - Page and role
   - Reproduction steps
   - Expected result
   - Actual result
   - Screenshot
   - Severity: Critical, High, Medium or Low

Acceptance criteria:

- No Critical or High issues remain.
- Every role can complete its main workflow.
- Mobile browser and installed PWA behavior are verified.

## Phase 2 — Complete Authentication

Priority: High

Implement:

- Forgot-password request page
- Password-reset email and token flow
- Real email verification
- Resend-verification action
- Optional 30-day remembered session for an OKU user on a trusted personal device
- JKM-assisted recovery with identity checks and an audit trail
- Evaluate one-time SMS codes after provider, privacy and cost approval
- Evaluate passkeys/device biometrics as the preferred long-term passwordless option
- Clear inactive-account recovery procedure
- Tests for throttling, reset, verification, session revocation and accessibility

Detailed requirements: `docs/AUTHENTICATION_RECOVERY_PLAN.md`.

Acceptance criteria:

- A user can securely recover a forgotten password.
- Unverified email behavior follows the client's approved rule.
- Reset tokens expire and cannot be reused.
- Password reset, logout and deactivation revoke existing sessions and remembered-login tokens.
- Passwords and session identifiers are never stored in PWA browser storage or caches.
- Authentication feature tests pass.

## Phase 3 — Validate Real Client Workflows

Priority: High

Review the system with JKM representatives and confirm:

- Required OKU registration fields
- Kad OKU verification process
- Approved OKU categories and labels
- Employment status options
- Employer approval process
- Job matching requirements
- Welfare application statuses
- Review scheduling and reminder rules
- Required report formats
- CSV/XLSX import columns

Acceptance criteria:

- Client signs off the final fields, statuses and workflows.
- Database, forms and reports use the approved terminology.
- Sample records complete the full process without manual database changes.

## Phase 4 — Notifications

Priority: High

Implement notifications for:

- Kad OKU verification result
- Welfare application status change
- Upcoming welfare review
- Job interest/application update
- New matching job

Recommended order:

1. In-app database notifications
2. Email notifications
3. Scheduled reminders
4. Web push notifications — implemented with Firebase Cloud Messaging; production configuration and device UAT remain

Acceptance criteria:

- Notifications are sent only to authorised recipients.
- Users can mark in-app notifications as read.
- Failed email or push delivery does not break the main transaction.
- Scheduled reminders are not duplicated.

## Phase 5 — Security and Privacy

Priority: Critical before production

Complete:

- Privacy policy
- Terms of service
- Data-retention policy
- Access review for IC numbers and Kad OKU images
- Private storage and authorised document delivery
- Upload MIME, extension and size validation
- Production log review to prevent personal-data leakage
- Backup encryption
- Session, cookie and HTTPS configuration
- Administrator audit coverage

Acceptance criteria:

- Sensitive files are not directly public.
- Unauthorised roles cannot access protected records or documents.
- Production runs with `APP_DEBUG=false` and HTTPS.
- Backup and recovery have been tested.

## Phase 6 — Expand Automated Testing

Priority: High

Add tests for:

- Every role and route permission
- Password reset and email verification
- Invalid and malicious uploads
- CSV/XLSX import edge cases
- Job matching and interest workflow
- Welfare status transitions
- Report filters and exports
- Admin user management
- Audit logs
- PWA manifest, offline page and service worker

Target:

- At least 70 focused feature tests
- All critical workflows covered
- No failing tests before deployment

Verification commands:

```powershell
php artisan test
npm run build
php artisan view:clear
php artisan view:cache
git diff --check
```

## Phase 7 — Production Deployment

Priority: Final release

Follow `docs/DEPLOYMENT_CHECKLIST.md`.

Required infrastructure:

- Production domain
- HTTPS certificate
- PHP 8.3+ server
- MySQL/MariaDB database
- Email provider
- Queue worker
- Laravel scheduler
- Automated encrypted backups
- Error and uptime monitoring

Acceptance criteria:

- Deployment checklist is complete.
- Database and uploaded files are backed up.
- Production smoke tests pass.
- Rollback procedure is tested.
- Deployed Git commit is recorded.

## Phase 8 — Final Client Sign-Off

1. Demonstrate every role.
2. Let real JKM staff, employers and OKU users perform UAT.
3. Resolve all Critical and High findings.
4. Obtain written approval.
5. Tag the approved Git release.
6. Deliver system, documentation and recovery credentials securely.

## Recommended Work Order

| Order | Work | Progress impact |
|---:|---|---:|
| 1 | UAT and workflow validation | 82% → 86% |
| 2 | Authentication completion | 86% → 89% |
| 3 | Notifications | 89% → 92% |
| 4 | Security and privacy | 92% → 95% |
| 5 | Expanded automated tests | 95% → 97% |
| 6 | Deployment and monitoring | 97% → 99% |
| 7 | Client sign-off | 99% → 100% |

## Immediate Next Action

Start Phase 1 using `docs/UAT_CHECKLIST.md`. Do not add another major feature until the first UAT round identifies the client's actual remaining needs.

## Planned Accessibility Function

The proposed OKU Accessibility Assistant is documented in `docs/PLANNED_ACCESSIBILITY_ASSISTANT.md`.

It includes read-aloud controls, printable information, text sizing, high contrast, keyboard support, and a later Easy Language function. This is a planned function and must be validated with JKM representatives and users with different disabilities before implementation. It should be scheduled after the first UAT round unless accessibility testing identifies a release-blocking issue.

## Definition of Complete

MyOKUcare is 100% complete when:

- Every approved workflow works for every role.
- Critical and High UAT issues are closed.
- Authentication recovery and verification are operational.
- Notifications and scheduled reminders work.
- Sensitive information passes security and privacy review.
- Automated tests cover critical workflows.
- Production backup, monitoring and rollback are active.
- The client provides written acceptance.
