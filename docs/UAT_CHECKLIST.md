# User Acceptance Testing Checklist

Test on Android, iPhone, tablet, laptop and desktop where available.

## Public and Authentication

- [ ] Welcome page is readable and responsive
- [ ] Registration works for OKU and employer
- [ ] Required OKU fields are enforced
- [ ] Login works using e-mail and password
- [ ] Incorrect login shows a clear error
- [ ] Password visibility control works
- [ ] Inactive account is rejected
- [ ] Logout invalidates the session
- [ ] Deactivation blocks an already authenticated web/PWA session

## Role Access

- [ ] Pentadbir sees only authorised modules
- [ ] JKM officer sees operational modules
- [ ] Employer sees employer/job modules
- [ ] OKU user sees personal profile/job/welfare modules
- [ ] Direct URL access is blocked for unauthorised roles

## OKU

- [ ] Create, view, update and search records
- [ ] Filters and pagination work
- [ ] CSV and XLSX imports report successes and failures
- [ ] Invalid ages are rejected clearly
- [ ] Exported CSV opens correctly
- [ ] Kad OKU upload works
- [ ] JKM verification status works

## Employment

- [ ] Employer creation/edit works
- [ ] Job creation/edit works
- [ ] Job filters work
- [ ] Matching results are reasonable
- [ ] OKU user can express interest

## Welfare

- [ ] Application submission works
- [ ] JKM can review and update status
- [ ] Review scheduling works
- [ ] User sees current status

## Reports and Administration

- [ ] Dashboard statistics reflect database records
- [ ] Employment report filters and export work
- [ ] Welfare report filters and export work
- [ ] User management works
- [ ] Audit activity is recorded and exportable
- [ ] Admin profile and settings save correctly

## Mobile and PWA

- [ ] Header stays pinned
- [ ] Hamburger sidebar covers the header when opened
- [ ] Sidebar closes with backdrop and Escape
- [ ] Forms and cards are compact but readable
- [ ] Browser mode hides bottom navigation
- [ ] Installed PWA shows correct role navigation
- [ ] Content is not covered by bottom navigation
- [ ] Offline page appears without a connection
- [ ] Previously viewed authenticated pages are not available offline
- [ ] Install prompt disappears after installation

## Planned Recovery UAT

Run this section after `docs/AUTHENTICATION_RECOVERY_PLAN.md` is implemented:

- [ ] Email reset link is single-use and expires
- [ ] Password reset revokes existing and remembered sessions
- [ ] Recovery responses do not reveal whether an account exists
- [ ] JKM-assisted recovery requires authorisation and creates an audit record
- [ ] SMS recovery works only if an approved provider is configured
- [ ] Passkey recovery has a non-biometric alternative
- [ ] Recovery is usable with keyboard, screen reader and enlarged text

## Accessibility

Automated baseline checks cover skip navigation, page landmarks, core labels, visible focus CSS, reduced-motion CSS and live status/error regions. The following items still require human testing on representative devices and assistive technology:

- [ ] Keyboard navigation is logical
- [ ] Focus indicator is visible
- [ ] Text scaling works without clipping
- [ ] High-contrast mode remains usable
- [ ] Form errors are announced and associated with fields
- [ ] Icons do not expose meaningless symbols to screen readers

## Sign-off

| Item | Value |
|---|---|
| Tester | |
| Role | |
| Device/browser | |
| Build/commit | |
| Date | |
| Result | Pass / Fail |
| Notes | |
