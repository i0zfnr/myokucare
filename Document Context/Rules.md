# MyOKUcare Business and Engineering Rules

## Account rules

1. Every account has one unique email and one password known only to its owner.
2. Passwords are stored only as secure hashes.
3. Public registration may create only `employer` or `oku_user`.
4. `super_admin` and `jkm_officer` accounts require authorised creation.
5. Inactive accounts cannot authenticate or continue protected requests.
6. A successful login regenerates the session and records `last_login_at`.
7. Demonstration accounts and shared passwords are forbidden in production.
8. Passwords, session identifiers and recovery secrets must never be stored in PWA browser storage or service-worker caches.
9. Password reset, logout, deactivation and reported-device-loss workflows revoke applicable sessions and remembered-login tokens.

## Authorisation rules

1. Deny access by default.
2. Menu visibility is not security; every sensitive route requires middleware or a policy.
3. Super Admin can access all approved functions.
4. JKM Officer can manage OKU, employment, welfare, and operational reports.
5. Employer access is limited to its own employer record, jobs, and applicants.
6. OKU User access is limited to its own profile, interests, employment, and welfare information.
7. Planned Family Member access requires an explicit link and recorded authority or consent before that role is implemented.
8. The planned Viewer role sees aggregate, read-only information and no unnecessary personal data.

## OKU data rules

1. `ic_number` is unique.
2. `oku_card_number` is unique.
3. Age is between 1 and 120 in the current schema.
4. Gender values: `Lelaki`, `Perempuan`.
5. Marital status values: `Berkahwin`, `Bujang`, `Duda`, `Janda`.
6. OKU categories: `Fizikal`, `Pendengaran`, `Mental`, `Pembelajaran`, `Penglihatan`.
7. Employment status: `Bekerja`, `Tidak Bekerja`, `Sendiri`.
8. Deactivation is preferred to irreversible deletion.
9. Sensitive changes must record actor, time, and before/after values when audit logging is implemented.

## Employer and job rules

1. Employer registration number and email are unique.
2. A job belongs to exactly one employer.
3. Salary minimum cannot be negative.
4. Salary maximum, when present, must be at least salary minimum.
5. Suitable category values include the five OKU categories and `Semua`.
6. Employment types: `Sepenuh Masa`, `Separuh Masa`, `Kontrak`, `Sementara`.
7. Inactive, deleted, or expired jobs are excluded from recommendations.
8. Employers may alter only their own jobs.

## Matching rules

1. Current minimum eligibility is category suitability or `Semua`.
2. The current score is a prototype heuristic.
3. The target model in the workflow document is 40% category, 30% education, and 30% location.
4. A score must be explainable to users and testable using fixed inputs.
5. Matching must not infer disability severity or discriminate using unrelated sensitive information.
6. A recommendation never guarantees interview or employment.

## Application rules

1. One OKU can have only one interest record per job.
2. Status values: `Interested`, `Applied`, `Shortlisted`, `Interviewed`, `Hired`, `Rejected`.
3. Allowed forward flow:

```text
Interested -> Applied -> Shortlisted -> Interviewed -> Hired
        |          |             |             |
        `----------+-------------+-----------> Rejected
```

4. `application_date` is required when entering Applied.
5. `interview_date` is required when entering Interviewed.
6. Hired status creates or links an employment record in one transaction.
7. Application counts are derived or updated exactly once.

## Employment rules

1. Employment belongs to one OKU and one job.
2. Status values: `Active`, `Resigned`, `Terminated`, `Completed`.
3. Start date is required.
4. End date is required when employment is no longer active.
5. End date cannot precede start date.
6. An OKU marked `Bekerja` should have an active employment or an authorised explanation.
7. Historical employment records are retained.

## Welfare rules

1. Welfare applications belong to one OKU.
2. Status values: `Pending`, `Under Review`, `Approved`, `Rejected`.
3. Rejected applications require a rejection reason.
4. Officer identity and review date are recorded when a decision is made.
5. Review status values: `Pending`, `Completed`, `Cancelled`, `Rescheduled`.
6. Completion requires a completed date and findings.
7. Rescheduling preserves the previous event and creates or records the new schedule.
8. The target review cycle is every six months when applicable.
9. Reminder delivery and failure must be logged.

## Dashboard and reporting rules

1. Dashboard statistics use database records at request time.
2. Page-load data must not be called instant real-time data.
3. No-data charts show an empty state, not simulated values.
4. Report filters and calculation dates must be displayed.
5. Aggregate reports, including any future Viewer reports, must suppress identifying information.
6. CSV exports require authentication and an authorised reporting role.

## Content and accessibility rules

1. Use clear Bahasa Melayu by default.
2. Information sources must be identified and maintained by an accountable owner.
3. Never present unverified welfare or job information as official.
4. All functionality must be keyboard accessible.
5. Colour cannot be the sole indicator of status.
6. Forms require persistent labels and readable errors.
7. Text remains usable at 200% zoom.

## Engineering rules

1. Validate all external input on the server.
2. Use Eloquent or parameterised queries.
3. Protect state-changing web requests with CSRF.
4. Wrap multi-record transitions in database transactions.
5. Queue external notifications and integrations.
6. Add indexes for common filters and foreign keys.
7. Do not expose stack traces, debug toolbars, or secrets in production.
8. Automated tests cover authentication, role access, validation, matching, and status transitions.
9. Migrations and `database/myokucare.sql` must remain logically aligned.
10. Production changes require backups and a rollback plan.
