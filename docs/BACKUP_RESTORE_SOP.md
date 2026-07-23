# Backup and Restore SOP

## Backup Scope

Back up all of the following together:

- MySQL/MariaDB database
- `.env` stored securely outside Git
- `storage/app` uploaded documents
- Application Git commit/tag
- Web-server and scheduler configuration

## Database Backup

```powershell
mysqldump --single-transaction --routines --triggers -u USER -p DATABASE_NAME > myokucare-backup.sql
```

Verify that the resulting file is not empty and store it encrypted.

## Uploaded Files Backup

Back up `storage/app` using the organisation's approved encrypted backup system. Do not place real Kad OKU images or personal records in Git.

## Restore Procedure

1. Put the application into maintenance mode:

```powershell
php artisan down
```

2. Restore the matching application release.
3. Restore `.env` from the secure configuration store.
4. Restore the database:

```powershell
mysql -u USER -p DATABASE_NAME < myokucare-backup.sql
```

5. Restore `storage/app`.
6. Run:

```powershell
composer install --no-dev --optimize-autoloader
npm ci
npm run build
php artisan migrate --force
php artisan optimize
php artisan storage:link
```

7. Validate login, uploads, dashboards and reports.
8. Bring the system online:

```powershell
php artisan up
```

## Recovery Checks

- Confirm record counts.
- Confirm user roles and login.
- Confirm Kad OKU documents can be accessed only by authorised users.
- Confirm scheduled reviews and welfare cases.
- Confirm PWA assets return successfully.
- Record recovery time and any missing data.
