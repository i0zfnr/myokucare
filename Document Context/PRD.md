# MyOKUcare Product Requirements

## Product summary

MyOKUcare is a digital service proposed by i-SysBest, Pejabat Kebajikan Masyarakat Daerah Besut, to improve OKU employment data, job discovery, trusted information, and welfare review management.

The KIK source describes the project as a prototype requiring testing, validation, and formal approval before official use.

## Problem statement

The source documentation identifies four core problems:

1. No central, current source for determining how many OKU are working and their employment status.
2. Some OKU users lack suitable devices or an effective browsing experience for existing job portals.
3. Users lack clear and reliable welfare information.
4. Review-form reminders are not provided early enough.

## Product outcomes

| Outcome | Product response |
|---|---|
| Employment data can be obtained and stored | Central OKU and employment records |
| Job search is easier and not limited by device constraints | Responsive job listings and recommendations |
| Information is valid and useful | Officer-managed content and traceable sources |
| Reviews are managed more effectively | Review schedules, deadlines, reminders, and reporting |

## Users

### Super Admin

- Provision administrative accounts
- Configure the system
- Access all operational and aggregate records
- Review audit activity

### JKM Officer

- Register and update OKU records
- Verify employment and welfare information
- Manage applications and reviews
- Generate operational reports

### Employer

- Maintain company profile
- Create and manage jobs
- View authorised applicants
- Progress applications through hiring stages

### OKU User

- Maintain permitted profile information
- View suitable jobs
- Express interest or apply
- View application status
- View welfare application and review status

### Family Member

Planned role; not implemented in the current four-role release.

- Assist a specifically linked OKU user
- Access only information for which consent or authority exists

### Viewer

Planned role; not implemented in the current four-role release.

- View aggregate reports only
- Cannot view unnecessary personal records or mutate data

## Functional requirements

### FR-1 Authentication and accounts

- Users log in using a unique email and their own password.
- The system rejects inactive accounts.
- Sessions regenerate after authentication.
- Users can log out from every authenticated page.
- Current public registration permits Employer and OKU User. Family Member remains planned.
- Admin and JKM roles require authorised provisioning.
- Password reset and email verification are required before production.
- Accessible recovery should follow `docs/AUTHENTICATION_RECOVERY_PLAN.md`, including email reset, assisted recovery and later approved SMS/passkey options.

### FR-2 OKU profiles

- JKM can create, view, update, and deactivate an OKU profile.
- IC number and OKU card number must be unique.
- Profile records include identity, category, education, employment status, contact details, connectivity, and emergency contact.
- Changes to sensitive information must be auditable.

### FR-3 Employer profiles and jobs

- Employers have a unique registration number and email.
- Authorised employers can create jobs with description, requirements, suitability, salary, location, type, and deadline.
- Expired and inactive jobs must not be recommended.

### FR-4 Job matching

- Minimum matching uses the OKU suitability category.
- Target matching expands to skills, education, interests, qualifications, and location.
- The score explanation must be visible and reproducible.
- A recommendation is not a hiring decision.

### FR-5 Applications and employment

- OKU users can express interest or apply once per job.
- Applications progress through defined statuses.
- Hiring creates an employment record.
- Employment status and history remain consistent.
- Resignation, termination, and completion require dates and authorised updates.

### FR-6 Welfare

- An authorised user can submit a welfare application.
- Officers can move it through Pending, Under Review, Approved, or Rejected.
- Rejection requires a reason.
- Approved or reviewable cases can receive a six-month review date.
- The system creates reminders before the review deadline.
- Completed, cancelled, and rescheduled reviews remain in history.

### FR-7 Dashboard and reporting

- Counts and charts use current persisted records.
- Reports support employment, categories, welfare status, and review completion.
- CSV export is available to authorised roles.
- Empty data is represented honestly.
- Personally identifiable information is excluded from aggregate viewer reports.

### FR-8 Notifications

Planned:

- job recommendation;
- application status change;
- welfare status change;
- approaching review;
- overdue review.

Delivery channel must respect user preference and consent.

### FR-9 Accessibility

- Responsive interface
- Keyboard navigation
- Screen-reader-friendly markup
- Larger-text compatibility
- High-contrast text
- Malay-first wording
- Planned OKU Accessibility Assistant with user-controlled read aloud and print support
- Future Easy Language and multilingual support
- Spoken information must also remain available as visible text
- Detailed planned requirements: `docs/PLANNED_ACCESSIBILITY_ASSISTANT.md`

## Non-functional requirements

| Area | Requirement |
|---|---|
| Security | Least privilege, hashed passwords, CSRF, validation, audit history |
| Performance | Common list and dashboard requests should complete within 2 seconds under expected district load |
| Availability | Defined maintenance window and monitored production health |
| Reliability | Transactions for multi-record status changes |
| Privacy | Purpose limitation, minimum data collection, retention controls |
| Accessibility | WCAG 2.2 AA target |
| Portability | MySQL 8+ or compatible MariaDB; modern evergreen browsers |
| Backup | Automated encrypted backups with tested restoration |

## Success measures

The KIK documentation proposes:

- job matching accuracy target: at least 90%;
- review reminder effectiveness target: 95%;
- application processing target: under seven days;
- welfare review completion target: 100%;
- user satisfaction target: 90%;
- increased economic participation and improved data-driven policy.

These are targets, not current measured results. Measurement definitions and baseline periods must be agreed before reporting achievement.

## Current implementation status

Implemented:

- email/password login and role accounts;
- role-aware navigation and route middleware;
- OKU records;
- employer and job persistence;
- category-based recommendations;
- job-interest API;
- welfare status and review schedule persistence;
- database-backed dashboard;
- CSV reports;
- responsive public and authenticated interfaces.
- server-enforced ownership, active-session blocking, safe exports and regression tests for confirmed attack paths.

Partial:

- role-specific workflows;
- employer job management UI;
- OKU/family ownership and consent;
- employment status transitions;
- welfare form UI;
- explainable weighted matching.

Not yet implemented:

- automatic six-month scheduling;
- reminders and notifications;
- password reset and email verification;
- SMS recovery and passkeys;
- admin user-management UI;
- audit log;
- mobile ID card;
- external integration;
- production accessibility audit;
- formal pilot and authorised acceptance.

## Release gates

The product must not be described as officially validated until:

1. functional and security testing pass;
2. role ownership policies are implemented;
3. privacy and retention controls are approved;
4. pilot users complete acceptance testing;
5. accessibility testing is completed;
6. backup and recovery are demonstrated;
7. JKM authority approves production use.
