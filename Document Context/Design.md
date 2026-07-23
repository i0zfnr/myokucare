# MyOKUcare Design System

## Design intent

The interface must feel trustworthy, inclusive, calm, and easy to navigate. It supports users with different levels of digital literacy and may be accessed by OKU users, family members, employers, JKM officers, and report viewers.

## Brand

| Token | Value | Usage |
|---|---|---|
| Primary coral | `#FF6565` | Active states, main actions, highlights |
| Secondary coral | `#FF9064` | Gradient start, visual warmth |
| Primary dark | `#D9434C` | Accessible text and icons on light coral |
| Ink | `#172033` | Main text |
| Muted | `#6C7484` | Secondary text |
| Canvas | `#F7F8FB` | Application background |
| Surface | `#FFFFFF` | Panels and forms |
| Line | `#E7E9EE` | Borders and dividers |
| Success | `#168B69` | Confirmed and successful states |

Main gradient:

```css
linear-gradient(135deg, #FF9064, #FF6565)
```

Do not use light coral as body text on white. Use `#D9434C` or a darker neutral to maintain contrast.

## Typography

- Primary family: Instrument Sans or system sans-serif fallback
- Page title: 26-44px depending on context
- Section heading: 16-30px
- Body: 13-16px
- Supporting text: 11-13px
- Avoid long all-uppercase content; uppercase is reserved for short eyebrows and navigation group labels.

## Public journey

```text
Welcome page
  |-- Log Masuk
  `-- Daftar Akaun
         |-- Pengguna OKU
         |-- Ahli Keluarga
         `-- Majikan
```

The welcome page explains the service before asking for an account. The login page must always show a prominent return-to-welcome action.

## Application shell

- Fixed white sidebar on desktop
- Sticky top bar
- Soft-grey content canvas
- Responsive drawer navigation on mobile
- Navigation grouped by domain: Main, OKU, Employment, Welfare, Reports
- Only links permitted for the current role are displayed
- Role and account name remain visible in the sidebar

## Components

### Buttons

- Primary: coral gradient, white label
- Secondary: white surface, neutral border
- Destructive: explicit red treatment and confirmation
- Minimum touch height: 42px
- Use action-oriented Malay labels such as `Simpan Rekod`, `Daftar Akaun`, and `Muat Turun CSV`

### Panels and cards

- White background
- 14-20px corner radius
- Subtle border instead of heavy shadows
- Clear heading, explanation, and action hierarchy

### Tables

- Horizontally scrollable on narrow screens
- Visible column headings
- Status values rendered as text badges, never colour alone
- Names link to detail views only when the role is authorised

### Forms

- Persistent labels above fields
- Required status conveyed in text
- Errors shown near the top and, when expanded, beside their field
- Preserve submitted values after validation errors
- Password controls use browser autocomplete attributes
- Never request information not needed for the stated workflow

### Empty states

An empty database must display a truthful empty state. Never present sample chart values as if they are current data.

## Accessibility requirements

The KIK documentation explicitly identifies future voice, screen-reader, large-text, and multilingual support. The web implementation should establish the foundation now:

- semantic headings and landmarks;
- keyboard-operable navigation and dialogs;
- visible focus indicators;
- skip-to-content link;
- minimum 4.5:1 contrast for normal text;
- labels for every input;
- status meaning available without relying on colour;
- responsive zoom without clipped content;
- reduced-motion support;
- Malay-first plain language;
- meaningful page titles;
- no automatic timeouts without warning.

Target WCAG level: WCAG 2.2 AA.

## Content language

- Default interface language: Bahasa Melayu
- Use `OKU` consistently
- Prefer `Log Masuk`, not mixed `Login/Sign In`
- Prefer `Daftar Akaun`, `Peluang Kerja`, `Permohonan Kebajikan`, and `Kaji Semula`
- Explain technical or administrative terms the first time they appear

## Dashboard rules

- Statistics must come from database queries.
- Show the last data refresh time when automatic refresh is introduced.
- A `Dikemas kini` badge must not imply push-based real-time updates.
- Charts need a legend, accessible text alternative, and honest zero-data state.
- Role dashboards should prioritise the next action:
  - JKM: pending reviews and expiring schedules;
  - Employer: open jobs and applicants;
  - OKU: recommendations and application statuses;
  - Family: linked OKU actions;
  - Viewer: aggregate reports only.

## Responsive behaviour

- Desktop: sidebar and multi-column dashboard
- Tablet: two-column metrics and stacked analysis panels
- Mobile: navigation drawer, one-column cards and forms, full-width actions
- Primary actions should remain visible without horizontal scrolling.
