# RBAC, exports and JKM record management

## Setup

1. Run `php artisan migrate`.
2. Install the report dependencies in the Python used by `IDENTITY_PYTHON_BINARY`:
   `python -m pip install reportlab openpyxl`
3. Keep Laravel's scheduler running so `exports:purge-expired` removes expired files hourly.

## Access model

Application roles remain `jkm_officer`, `oku_user`, and `employer` (with `super_admin` as the system administrator). Permissions can be assigned from the system-admin user form. JKM deletion, sensitive-data, identity-document and permanent-deletion permissions are deliberately not granted by default.

OKU and employer access is derived on the server from an authorised employment relationship. Frontend identifiers never expand the authenticated user's scope. NRIC is masked in normal exports and document images are excluded.

## Secure exports

PDF, CSV and XLSX files are written to Laravel's private local disk with random ULID filenames. Each export has an audit record, purpose, filter/field metadata, owner, expiry, signed download route and hourly deletion. PDF reports include report metadata, record totals, page numbers, confidentiality notice and watermark.

## Deletion

Employer and OKU records use soft deletion. The backend requires a deletion permission, reason, exact confirmation and dependency validation. Restore and permanent deletion require separate permissions; permanent deletion additionally requires password confirmation and is only available for an already soft-deleted record with no retained relationships.

## Operational notes

- Salary values use Laravel encrypted casts.
- Disability category exports require both user consent and `sensitive_data.export`.
- Raw card images are never part of normal exports.
- The application does not grant official JKM/SMOKU verification without an authorised provider.
