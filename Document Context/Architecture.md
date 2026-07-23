# MyOKUcare Architecture

## Purpose

MyOKUcare is a role-based digital service for managing OKU profiles, employment opportunities, job matching, employment history, welfare applications, review schedules, and management reporting.

This architecture is based on:

- the KIK 2026 MyOKUcare documentation from Pejabat Kebajikan Masyarakat Daerah Besut;
- the current Laravel 13 implementation in this repository;
- the principle that sensitive OKU data must remain centrally controlled, traceable, and accessible only to authorised roles.

## Current system boundary

```text
Browser
  |
  | HTTPS, session cookie, CSRF token
  v
Laravel web application
  |-- Authentication and role middleware
  |-- Controllers and server-side validation
  |-- Job matching and statistics services
  |-- Blade views and Vite assets
  |-- Queue jobs (queue_jobs)
  |
  v
MySQL / MariaDB
```

The current application is a modular Laravel monolith. This is appropriate for the prototype and initial district deployment because it keeps transactions, validation, reporting, and access control within one deployable application.

## Layers

### Presentation

- Blade templates under `resources/views`
- Responsive web interface using `#FF9064` and `#FF6565`
- Public welcome, login, and registration pages
- Role-aware administration sidebar
- Server-rendered dashboard and reports

### Application

- `AuthController`: email/password login, logout, and public registration
- `OkuController`: OKU record management and job recommendations
- `EmployerController`: employer management
- `JobController`: job listing management
- `WelfareController`: welfare application status and review scheduling
- `ReportController`: employment and welfare summaries and CSV exports
- `OkuApiController`: JSON endpoints for matching and job interest

### Domain services

- `JobMatchingService`: finds active, non-expired jobs suitable for an OKU category and assigns a score
- `OkuDataService`: calculates dashboard, category, employment, and welfare totals

### Persistence

- Eloquent models define domain relationships
- Migrations are the authoritative application schema
- `database/myokucare.sql` is the complete MySQL import version
- Soft deletion is used for OKU, employer, and job master records

## Roles

| Role | Intended responsibility |
|---|---|
| Super Admin | Full configuration, user, operational, and reporting access |
| JKM Officer | Manage OKU, jobs, welfare applications, reviews, and reports |
| Employer | Manage company information, post jobs, and review applicants |
| OKU User | View jobs, express interest, and monitor welfare information |
| Family Member | Assist a linked OKU user and monitor permitted information |
| Viewer | Read-only statistics and reports |

Public registration is restricted to Employer, OKU User, and Family Member. Administrative roles must be provisioned by an authorised administrator.

## Main request flows

### Authentication

```text
Welcome -> Login -> validate email/password -> check active account
        -> regenerate session -> record last login -> role dashboard
```

### OKU registration

```text
JKM Officer -> validated registration form -> unique IC/card checks
            -> store OKU record -> profile -> job recommendations
```

### Employment

```text
Employer -> publish job -> matching service filters active jobs
OKU -> express interest -> application status progression
Employer/JKM -> shortlist/interview/hire -> employment record
```

Target progression:

```text
Interested -> Applied -> Shortlisted -> Interviewed -> Hired
                                                |
                                                v
                                      Active Employment
```

### Welfare

```text
Application -> Pending -> Under Review -> Approved or Rejected
                                  |
                                  v
                         Review Schedule
```

The KIK target requires an automatic six-month review and advance reminders. The database supports review dates and schedules, but automated scheduling and notification delivery remain planned work.

## Dashboard data behaviour

Dashboard values are queried from the database on each page request:

- total OKU;
- active OKU;
- employed and unemployed OKU;
- OKU count by category;
- active employment records;
- pending or under-review welfare applications.

This is current database data at page-load time, not push-based real-time data. Automatic polling or Laravel broadcasting may be added later.

## Security architecture

- Passwords use Laravel's hashed password cast.
- Sessions are regenerated after login.
- CSRF protection applies to web forms.
- Output is escaped by Blade by default.
- Eloquent parameter binding protects ordinary queries from SQL injection.
- Route middleware enforces authentication and roles.
- Inactive accounts are rejected.
- Administrative roles cannot be selected through public registration.

Required before production:

- ownership policies for linked OKU and employer records;
- email verification and password reset;
- audit log for sensitive reads and mutations;
- rate limiting on authentication and API endpoints;
- encrypted transport and secure production cookies;
- backup, retention, incident response, and recovery procedures;
- privacy review for Malaysian personal-data and public-sector requirements.

## Planned integrations

The KIK document identifies future potential for:

- government, private-sector, and NGO data sharing;
- job matching by skills, interests, qualifications, and location;
- online training and skills courses;
- voice support, screen-reader optimisation, larger text, and multiple languages;
- automated analytics and reports;
- mobile OKU identification;
- email, SMS, or application reminders.

Integrations should be introduced through service interfaces and queued jobs so external provider failures do not block core transactions.

## Deployment model

Recommended initial production topology:

```text
Reverse proxy / TLS
        |
Laravel application
   |           |
MySQL       Queue worker
   |
Encrypted backups
```

Separate development, testing, staging, and production environments. Never use the demonstration accounts or shared temporary passwords in production.
