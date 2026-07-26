# Planned OKU Accessibility Assistant

## Status

**Planned future function — not yet implemented.**

This document defines a proposed accessibility assistant for OKU users. It should be validated with JKM representatives and real users with different access needs before development and production release.

## Purpose

The assistant will help OKU users independently access job opportunities, welfare guidance, application instructions, and other approved information in MyOKUcare.

Voice output is only one part of the function. The interface must also support users who are blind or have low vision, are deaf or hard of hearing, have a physical disability, or have a cognitive or learning disability.

## Proposed Accessibility Toolbar

The OKU-facing pages should provide a consistent toolbar containing:

- **Read Aloud** for the main page content
- **Pause**, **Continue**, and **Stop** controls
- Speech-speed selection: slow, normal, and fast
- Bahasa Melayu and English voice selection, where supported
- **Print Information** with a clean, large-text print layout
- Increase and decrease text size
- High-contrast display mode
- Keyboard-accessible controls
- **Easy Language** for a simpler version of complex information

The toolbar should initially be available on:

1. Job details
2. JKM and welfare information
3. Application instructions
4. Application status pages
5. Important user notifications

## User Needs

| User need | Required support |
|---|---|
| Blind or low vision | Semantic screen-reader markup, read aloud, large text, and high contrast |
| Deaf or hard of hearing | Visible text, captions for media, and visual notifications; information must not be audio-only |
| Physical or motor disability | Full keyboard operation, visible focus, large controls, and optional voice input |
| Cognitive or learning disability | Simple wording, short steps, consistent icons, and Easy Language |
| Speech disability | Typing and button alternatives; voice input must never be compulsory |

## Core Functional Requirements

### AF-1 Read Aloud

- A user can start reading the main information using one clearly labelled control.
- The system reads the page title and relevant content in a logical order.
- Navigation menus, repeated decoration, and hidden content are excluded.
- The user can pause, continue, stop, and change the reading speed.
- Starting a new reading session stops the previous session.
- The feature does not automatically play audio when a page opens.
- Controls remain usable using a keyboard and screen reader.
- If speech is unsupported or fails, the original text remains available.

### AF-2 Print Information

- A user can print or save a clean version of the current information.
- The print view contains the title, important details, source, and relevant date.
- Navigation, decorative elements, and private controls are removed.
- Text remains readable at a large size and in black and white.
- Sensitive personal information is excluded unless it is required and the user is authorised to access it.

### AF-3 Text and Display Preferences

- A user can enlarge text without losing content or functionality.
- High-contrast mode maintains readable text, controls, focus indicators, and status messages.
- Accessibility preferences should persist on the user's device where practical.
- The page remains responsive on supported mobile and desktop browsers.

### AF-4 Easy Language

- A user can request a shorter, simpler explanation of approved content.
- The simplified version must be clearly labelled as an explanation.
- The original text remains available for comparison.
- Dates, salary, eligibility, deadlines, contact details, and application requirements must not be changed.
- The function must not create new JKM policies, job conditions, benefits, or eligibility decisions.
- When the system cannot produce a reliable explanation, it displays the original information and an appropriate message.

### AF-5 Information in More Than One Form

- Spoken information must also be available as visible text.
- Audio or video content must provide captions or a text alternative.
- Important states must not be communicated using colour or sound alone.
- Form validation and system errors must be displayed in text and announced to assistive technology.

## Example Job Reading

Displayed information:

> Jawatan: Pembantu Pentadbiran  
> Lokasi: Johor Bahru  
> Gaji: RM1,800 hingga RM2,200  
> Kemudahan OKU: Akses kerusi roda tersedia

When the user selects **Dengar Maklumat Kerja**, the system reads the same approved information. Selecting **Cetak Maklumat** produces a clean printable version.

## Suggested Delivery Phases

### Phase A — Accessibility Foundation

- Review semantic headings, landmarks, labels, alternative text, focus order, and form errors.
- Confirm all important actions work with a keyboard.
- Test with common screen readers and browser zoom.

### Phase B — First Release

- Read Aloud
- Pause, continue, stop, and speed controls
- Print Information
- Text-size controls
- High-contrast mode

### Phase C — Assisted Understanding

- Easy Language summaries
- Bahasa Melayu and English options
- Optional voice questions or commands

Voice input must be optional and should only be added after the non-voice interface is fully usable.

## Technical Direction

- Build the page with semantic HTML before adding speech features.
- Use the browser speech capability for an initial prototype, with feature detection and a text-only fallback.
- Do not depend on a specific voice being installed on every device.
- Read from a controlled content region rather than the entire page.
- Use print-specific CSS for printable information.
- Treat accessibility preferences as non-sensitive settings.
- If an external AI or speech service is considered, complete privacy, consent, cost, language-quality, and data-retention reviews first.
- Do not send IC numbers, Kad OKU images, welfare records, or other sensitive personal data to an external AI service without formal approval and appropriate safeguards.

## Acceptance Criteria

The first release is acceptable when:

- An OKU user can operate all assistant controls using only a keyboard.
- Read Aloud correctly reads the main job and welfare information.
- The user can pause, continue, stop, and change speed.
- No page starts speaking without user action.
- All spoken information is also available as text.
- Text enlargement and high contrast do not hide or overlap important content.
- The print view is readable and excludes unrelated navigation.
- Unsupported speech or voice errors do not block access to information.
- Testing includes users with different disabilities and at least one mobile and one desktop environment.
- The accessibility audit and JKM/user acceptance results are documented.

## Out of Scope for the First Release

- Automatic JKM eligibility decisions
- AI-generated job requirements or benefits
- Mandatory voice authentication
- Replacing the user's device screen reader
- Sign-language generation
- Sending sensitive records to an unapproved external AI provider

## Dependencies and Risks

- Browser and device voices vary in availability and Malay pronunciation quality.
- AI simplification can omit or alter important meaning and therefore requires safeguards and testing.
- A speech button cannot compensate for inaccessible headings, forms, focus order, or controls.
- Users may be in a shared environment, so audio must only begin after an explicit action.
- Real-user testing is required; automated accessibility checks alone are insufficient.

## Reference

Implementation and testing should follow the project's WCAG 2.2 AA target and current W3C Web Accessibility Initiative guidance for page structure, forms, keyboard access, text alternatives, and user notifications.
