# Multilingual support

## Interface languages

The application supports:

- `BM` - Bahasa Melayu (default)
- `EN` - English
- `ZH_CN` - Simplified Chinese

Laravel JSON catalogues are stored together in `lang/bm.json`, `lang/en.json`, and `lang/zh-CN.json`. The authenticated user's `preferred_language` is applied by web middleware on every request and remains active after future logins.

Users can change their preference at `/settings/language`. The setting is available to every authenticated role and takes effect on the redirect immediately without logging out.

## User-generated content

Interface language and content language are separate. Original user text is never replaced. Captured multilingual fields create a `UserSubmissionTranslation` record containing encrypted original and translated values, detected language, confidence, provider status, and timestamp.

NRIC, registration numbers, dates, numeric values, and other official identifiers are excluded from translation.

## Translation provider

External translation is disabled unless an authorised provider is explicitly configured:

```env
TRANSLATION_API_ENDPOINT=
TRANSLATION_API_KEY=
```

The adapter expects compatible `/detect` and `/translate` JSON endpoints. If the provider is absent or unavailable, the original content remains accessible and the record is marked `PROVIDER_UNAVAILABLE`. The system never invents a production translation.

Only the specific text field is sent to the configured provider. Do not configure a third-party provider until privacy, consent, retention, and data-processing requirements have been approved.

## Reports

Secure exports accept `BM`, `EN`, or `ZH_CN` and `ORIGINAL`, `TRANSLATED`, or `DUAL` content modes. PDF, CSV, and XLSX column headings follow the selected report language. Chinese PDF output uses a Unicode CID font and has been render-tested.

## Deployment

Run:

```bash
php artisan migrate
npm run build
php artisan test
```
