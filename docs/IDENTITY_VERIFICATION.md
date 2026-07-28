# Identity verification module

## Setup

1. Run `composer install` and `npm install`.
2. Ensure Python 3 and OpenCV (`cv2`) are installed on the application host.
3. Set `IDENTITY_PYTHON_BINARY` if Python is not available as `python`.
4. Configure the thresholds and retention period shown in `.env.example`.
5. Run `php artisan migrate`.
6. Run `npm run build` for production.
7. Run Laravel's scheduler so `identity:purge-expired` executes daily.
8. Serve production only over HTTPS; browser camera access requires HTTPS outside localhost.

The application stores verification images on the private disk using Laravel application encryption. Uploaded images are re-encoded before storage to remove EXIF metadata, then OpenCV generates a cropped, perspective-corrected copy for review. Browser-provided OCR text, confidence scores and QR payloads are treated as untrusted and cannot verify an account.

## JKM/SMOKU provider

No authorised JKM or SMOKU API is configured in this repository. `OkuVerificationProvider` is bound to `UnavailableOkuVerificationProvider`, which always returns `UNVERIFIED_EXTERNAL_DATA`. It does not scrape JKM pages and never claims official verification.

To integrate an authorised API, implement `App\Contracts\OkuVerificationProvider`, keep credentials in environment/secret storage, bind the implementation in `bootstrap/app.php`, and add provider contract tests. Card images must not be sent to a provider unless its contract requires them and the consent wording is updated.

## Current limitations

- Browser OCR is optional presentation assistance only and is not accepted as verification evidence.
- MyKad front/back side classification uses required side slots, OCR/layout signals, and duplicate-image detection; it cannot authenticate physical security features with a standard web camera.
- Card obstruction detection is limited to card-boundary, sharpness, lighting, glare, and OCR-readability signals. Uncertain images must go to manual review.
- Until an authorised server-side provider is configured, submitted documents require manual approval by an authorised JKM reviewer.
- HEIC is intentionally rejected because this server has no configured HEIC decoder.
- `VERIFIED_LOCALLY_ONLY` is diagnostic comparison output only and does not unlock the account. Only `VERIFIED`, set through an authorised review/provider path, grants verified access.

## Privacy operations

- Verification values and QR payloads are encrypted with `APP_KEY`.
- Normal UI/API responses mask NRIC values.
- Image routes require authentication and ownership or staff roles.
- Access and review actions are written to `activity_logs` without raw NRIC.
- `php artisan identity:purge-expired` removes document files and soft-deletes their records after the configured retention period.
- Rotating `APP_KEY` requires a controlled data re-encryption plan.
