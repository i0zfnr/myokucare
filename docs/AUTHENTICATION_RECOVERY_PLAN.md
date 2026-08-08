# Accessible Authentication and Account Recovery Plan

## Status

**Partially implemented — email reset, email verification, session revocation and restricted remembered login are available. JKM-assisted recovery, SMS and passkeys remain planned.**

This plan addresses OKU users who may have difficulty remembering passwords while preserving protection for identity, welfare and employment records.

## Planned recovery options

1. **Password reset by email (implemented)** — a single-use, expiring reset link sent to the registered address.
2. **One-time code by SMS** — a phone-first alternative, subject to an approved provider, consent, delivery controls and cost review.
3. **Passkey or device biometrics** — the preferred long-term passwordless option using the device fingerprint, face recognition or device PIN. The application must never receive or store biometric data.
4. **JKM-assisted recovery** — an authorised officer verifies the person and starts a controlled recovery process. Officers must never see, request or set a permanent password on the user's behalf.

## PWA remembered-session policy

- The PWA uses the same server-side Laravel session as the website.
- **Implemented:** offer **Keep me signed in on this personal device for 30 days** only to OKU users on a trusted personal device.
- Never store a password, session identifier or identity information in `localStorage`, IndexedDB or the service-worker cache.
- Normal inactive sessions should expire after 30–60 minutes.
- **Implemented for password reset and account deactivation:** revoke sessions and remembered-login tokens. A reported-lost-device action remains planned.
- Require fresh authentication before changing identity information, authentication methods or particularly sensitive document downloads.
- JKM officers, employers and administrators should not use long remembered sessions on shared devices.

## Security and accessibility requirements

- Recovery responses must not reveal whether an email address or phone number is registered.
- Reset links and one-time codes must be single-use, short-lived and rate-limited.
- Provide clear Bahasa Melayu instructions, persistent labels, readable errors and keyboard/screen-reader support.
- Recovery must not rely on audio, colour, memory questions or biometrics alone.
- Provide an alternative when a user cannot access the registered email, phone or biometric method.
- Record recovery initiation, completion, failure and officer assistance without storing secrets or codes.
- Notify the account owner after password, passkey, email or phone changes.

## Implemented automated coverage

- Neutral reset responses do not disclose whether an account exists.
- Active users receive a reset notification; inactive users cannot recover an account.
- Reset tokens are single-use and invalid or modified links are rejected.
- Password reset revokes database sessions, personal access tokens and remembered-login tokens.
- Unverified users cannot access protected application routes.
- Signed email verification and resend notifications are covered.

## Remaining required tests

- Expired reset tokens are rejected using the configured broker lifetime.
- Repeated reset/code requests are throttled.
- A user cannot recover another person's account.
- Password reset revokes existing sessions and remembered tokens.
- A reported-lost-device recovery action revokes access.
- Assisted recovery requires an authorised officer and an audit trail.
- The PWA never caches authenticated pages or recovery secrets.
- Recovery works with keyboard navigation, screen readers and mobile text enlargement.

## Release decision

Start with email reset and JKM-assisted recovery for the controlled pilot. Add SMS only after an approved provider and privacy review. Evaluate passkeys as the preferred production passwordless method after device/browser UAT.
