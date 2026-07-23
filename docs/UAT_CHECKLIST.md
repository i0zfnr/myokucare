# User Acceptance Testing Checklist

Test on Android, iPhone, tablet, laptop and desktop where available.

## Public and Authentication

- [ ] Welcome page is readable and responsive
- [ ] Registration works for OKU, employer and family member
- [ ] Required OKU fields are enforced
- [ ] Login works using e-mail and password
- [ ] Incorrect login shows a clear error
- [ ] Password visibility control works
- [ ] Inactive account is rejected
- [ ] Logout invalidates the session

## Role Access

- [ ] Super Admin sees only authorised modules
- [ ] JKM officer sees operational modules
- [ ] Employer sees employer/job modules
- [ ] OKU user sees personal profile/job/welfare modules
- [ ] Family member sees allowed support modules
- [ ] Viewer cannot modify data
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
- [ ] Install prompt disappears after installation

## Accessibility

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
