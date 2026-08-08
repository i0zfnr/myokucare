# Firebase Cloud Messaging for the MyOKUcare PWA

## Status

Implemented and configuration-gated. Push subscription is offered only when MyOKUcare is running in installed-PWA standalone mode. It is available to OKU users, employers, JKM officers and administrators after authentication and email verification.

The in-app database notification remains the authoritative record. Firebase receives only a generic alert and an authenticated notification link; sensitive case content is never included in the push payload.

## Firebase Console Setup

1. Create or select the approved Firebase project.
2. Add a Web App and record its public configuration values.
3. Open **Project settings → Cloud Messaging → Web configuration**.
4. Generate or import a Web Push VAPID key pair and copy the public key.
5. Confirm the Firebase Cloud Messaging API is enabled.
6. Create a least-privilege service account that can send FCM messages.
7. Download its JSON key once and store it outside the application repository with server-only filesystem permissions.

Official references:

- <https://firebase.google.com/docs/cloud-messaging/js/client>
- <https://firebase.google.com/docs/cloud-messaging/js/receive>
- <https://firebase.google.com/docs/cloud-messaging/send/v1-api>

## Production Environment

```dotenv
FIREBASE_PUSH_ENABLED=true
FIREBASE_PROJECT_ID=your-project-id
FIREBASE_WEB_API_KEY=your-public-web-api-key
FIREBASE_WEB_AUTH_DOMAIN=your-project-id.firebaseapp.com
FIREBASE_WEB_STORAGE_BUCKET=your-project-id.appspot.com
FIREBASE_MESSAGING_SENDER_ID=1234567890
FIREBASE_WEB_APP_ID=1:1234567890:web:example
FIREBASE_VAPID_PUBLIC_KEY=your-public-vapid-key
FIREBASE_SERVICE_ACCOUNT_PATH=C:\secure\myokucare-firebase-service-account.json
```

The web configuration and VAPID public key are browser-visible identifiers. The service-account JSON is secret and must never be copied into JavaScript, committed to Git, placed in `public/`, or pasted into support logs.

After configuration:

```powershell
php artisan config:clear
php artisan migrate --force
php artisan deployment:check
php artisan queue:work --tries=3 --timeout=60
```

## User Flow

1. User installs the MyOKUcare PWA.
2. User signs in and verifies their email.
3. The installed PWA displays an explicit notification opt-in panel.
4. Notification permission is requested only after the user selects **Aktifkan**.
5. The FCM token is encrypted in `push_subscriptions`; only its SHA-256 hash is indexed.
6. Every in-app system notification queues a generic FCM alert for all active devices belonging to that user.
7. Selecting the push opens the authenticated in-app notification route.
8. Disabling notifications or logging out revokes the stored token. Invalid FCM tokens are removed automatically.

Browser or device settings remain authoritative. Uninstall behavior differs by platform, so users should also be able to revoke notification permission through device settings.

## Privacy Rules

Push payloads must not contain IC/MyKad numbers, Kad OKU data, disability details, addresses, welfare types or decisions, salary, employer-private data, authentication tokens, or recovery links.

The permitted payload is limited to:

- generic localized MyOKUcare title;
- generic “new account update” message;
- random notification identifier and category tag;
- same-origin authenticated notification link.

## UAT

Test each role on at least one supported Android installed PWA and an installed iOS/iPadOS Home Screen web app where available:

- permission is not requested in ordinary browser mode;
- permission is requested only after user action;
- foreground and background alerts work;
- selecting an alert opens the correct authenticated notification;
- logged-out and deactivated users receive no sensitive information;
- duplicate job interest does not produce duplicate pushes;
- multiple devices work independently;
- disabling, logout and invalid-token cleanup work;
- failed Firebase delivery does not roll back the main application transaction.
