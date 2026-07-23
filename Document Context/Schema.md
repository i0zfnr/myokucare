# MyOKUcare Data Schema

## Entity overview

```text
users
 |-- optional employer_id -> employers
 `-- optional oku_id      -> okus

employers 1 --- * jobs
okus      1 --- * oku_employments * --- 1 jobs
okus      1 --- * job_interests   * --- 1 jobs
okus      1 --- * welfare_applications
welfare_applications 1 --- * review_schedules
```

## Core tables

### `users`

Authentication and role identity.

| Column | Type | Notes |
|---|---|---|
| id | bigint | Primary key |
| name | varchar | Display name |
| email | varchar | Unique login |
| password | varchar | Password hash |
| role | enum | `super_admin`, `jkm_officer`, `employer`, `oku_user`, `family_member`, `viewer` |
| employer_id | foreign key, nullable | Employer account link |
| oku_id | foreign key, nullable | OKU/family link |
| is_active | boolean | Authentication gate |
| last_login_at | timestamp, nullable | Latest successful login |
| email_verified_at | timestamp, nullable | Verification state |
| remember_token | varchar, nullable | Remember-me token |
| created_at, updated_at | timestamp | Audit timestamps |

### `okus`

Master OKU profile.

| Column group | Columns |
|---|---|
| Identity | `name`, `ic_number`, `gender`, `age`, `marital_status` |
| Address/contact | `address`, `phone_number`, `email` |
| OKU | `oku_card_number`, `oku_category` |
| Education/work | `education_level`, `employment_status` |
| Connectivity | `has_smartphone`, `has_internet` |
| Emergency | `emergency_contact_name`, `emergency_contact_phone` |
| System | `profile_photo_path`, `is_active`, timestamps, `deleted_at` |

Unique: `ic_number`, `oku_card_number`.

### `employers`

| Column | Notes |
|---|---|
| company_name | Registered display name |
| registration_number | Unique |
| address, industry_sector | Organisation details |
| contact_person, phone_number, email | Primary contact; email unique |
| website, company_description | Optional public details |
| number_of_employees | Optional organisation size |
| has_oku_quota | Inclusion indicator |
| is_active | Operational state |
| logo_path | Optional asset |
| timestamps, deleted_at | Lifecycle fields |

### `jobs`

| Column | Notes |
|---|---|
| employer_id | Owning employer |
| title, description | Job content |
| requirements, responsibilities | Candidate information |
| oku_category_suitable | Category or `Semua` |
| salary_min, salary_max | Decimal amounts |
| location, working_hours | Work arrangement |
| employment_type | Full-time, part-time, contract, temporary |
| application_deadline | Nullable |
| is_active | Publishing state |
| views_count, applications_count | Counters |
| timestamps, deleted_at | Lifecycle fields |

### `job_interests`

One record per OKU/job pair.

| Column | Notes |
|---|---|
| oku_id, job_id | Unique composite pair |
| status | Interest/application progression |
| notes | Optional operational note |
| application_date | Set when applied |
| interview_date | Set when interviewed |
| timestamps | Record history |

### `oku_employments`

| Column | Notes |
|---|---|
| oku_id, job_id | Related person and job |
| start_date, end_date | Employment period |
| status | `Active`, `Resigned`, `Terminated`, `Completed` |
| salary | Nullable decimal |
| notes | Authorised operational notes |
| timestamps | Record history |

### `welfare_applications`

| Column | Notes |
|---|---|
| oku_id | Applicant |
| application_type | Assistance category |
| status | Pending/review/decision |
| application_date, review_date | Processing dates |
| notes, rejection_reason | Decision context |
| reviewed_by | Nullable user foreign key |
| next_review_date | Planned follow-up |
| timestamps | Record history |

### `review_schedules`

| Column | Notes |
|---|---|
| welfare_application_id | Parent application |
| scheduled_date | Planned review |
| status | Pending/completed/cancelled/rescheduled |
| notes | Scheduling context |
| completed_date | Completion date |
| review_findings | Outcome |
| timestamps | Record history |

### `oku_category_matches`

Prototype configurable mapping between an OKU category and a job category, with `match_score` and notes.

## Framework tables

- `sessions`: database-backed web sessions
- `password_reset_tokens`: password reset tokens
- `personal_access_tokens`: Sanctum tokens
- `cache`, `cache_locks`: database cache
- `queue_jobs`, `job_batches`, `failed_jobs`: asynchronous jobs
- `migrations`: Laravel migration history

`queue_jobs` is intentionally named to avoid collision with the domain `jobs` table.

## Integrity requirements

- Cascade dependent job, employment, interest, welfare, and review records where currently defined.
- Set nullable reviewer/user links to null when the referenced account is removed.
- Use soft deletion for master OKU, employer, and job records.
- Validate end dates against start dates at application level; database check constraints may be added where supported.
- Preserve application, employment, and review history.

## Recommended schema additions

### Ownership and consent

`family_links`

```text
id
family_user_id -> users
oku_id -> okus
relationship
consent_status
consent_granted_at
consent_expires_at
approved_by -> users
timestamps
```

This is preferable to using only `users.oku_id` because it records authority and supports expiry.

### Audit history

`audit_logs`

```text
id
actor_user_id
action
subject_type
subject_id
old_values JSON
new_values JSON
ip_address
user_agent
created_at
```

### Notifications

Use Laravel notifications plus delivery logging:

```text
notifications
notification_deliveries
  - channel
  - destination
  - status
  - attempted_at
  - delivered_at
  - failure_reason
```

### Skills-based matching

```text
skills
oku_skill
job_skill
oku_preferences
job_locations
```

This supports the KIK target of matching by skills, interests, qualifications, and location.

### Trusted content

```text
information_resources
  - title
  - content
  - source_url
  - owner_user_id
  - verified_at
  - expires_at
  - status
```

## Data classification

| Classification | Examples | Handling |
|---|---|---|
| Highly sensitive | IC number, OKU card, disability category | Strict role/ownership checks, audit access |
| Sensitive | Welfare status, review findings, employment salary | Need-to-know access, audit mutations |
| Internal | Operational notes, counters | Authenticated access |
| Public-approved | Published jobs, verified public guidance | May be exposed through controlled public endpoints |

## Source of truth

- Laravel migrations are authoritative for application evolution.
- `database/myokucare.sql` supports a clean MySQL installation.
- Any schema change must update migrations, models, validation, tests, and the SQL import consistently.
