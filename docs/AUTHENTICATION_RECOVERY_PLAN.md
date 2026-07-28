# Accessible Authentication and Account Recovery Plan

## Status

**Planned next implementation — not yet available in the application.**

This plan addresses OKU users who may have difficulty remembering passwords while preserving protection for identity, welfare and employment records.

## Planned recovery options

1. **Password reset by email** — a single-use, expiring reset link sent to the registered address.
2. **One-time code by SMS** — a phone-first alternative, subject to an approved provider, consent, delivery controls and cost review.
3. **Passkey or device biometrics** — the preferred long-term passwordless option using the device fingerprint, face recognition or device PIN. The application must never receive or store biometric data.
4. **JKM-assisted recovery** — an authorised officer verifies the person and starts a controlled recovery process. Officers must never see, request or set a permanent password on the user's behalf.

## PWA remembered-session policy

- The PWA uses the same server-side Laravel session as the website.
- Offer **Keep me signed in on this personal device for 30 days** only to OKU users on a trusted personal device.
- Never store a password, session identifier or identity information in `localStorage`, IndexedDB or the service-worker cache.
- Normal inactive sessions should expire after 30–60 minutes.
- Logout, account deactivation, a reported lost device and password reset must revoke sessions and remembered-login tokens.
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

## Required tests

- Expired, reused and modified reset tokens are rejected.
- Repeated reset/code requests are throttled.
- A user cannot recover another person's account.
- Password reset revokes existing sessions and remembered tokens.
- A deactivated account cannot use recovery to reactivate itself.
- Assisted recovery requires an authorised officer and an audit trail.
- The PWA never caches authenticated pages or recovery secrets.
- Recovery works with keyboard navigation, screen readers and mobile text enlargement.

## Release decision

Start with email reset and JKM-assisted recovery for the controlled pilot. Add SMS only after an approved provider and privacy review. Evaluate passkeys as the preferred production passwordless method after device/browser UAT.
