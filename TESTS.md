# IPnz.live Test Plan

This document outlines automated and manual tests to validate key site features across devices and environments.

## Automated Tests (proposed)

- UI: Playwright E2E
  - Home loads without JS errors and security headers present
  - JOIN button navigates to join form; form validation messages shown
  - Join flow: submit valid data → success alert visible; DB insert mocked
  - External links: open in new tab with `rel="noopener"`
  - X share: click share icon → intent window opens with prefilled text and URL
  - Members section: when DB seeded, `view_active_members` renders cards

- API/Server: PHPUnit (or simple PHP integration tests)
  - `datacenter/database.php` loads config/env correctly
  - `clientregistration.php` validates input and uses prepared statements
  - Inserts set `has_custom_avatar` correctly when avatar omitted

- Performance/Accessibility
  - Lighthouse CI run: performance, accessibility, best practices, SEO thresholds

## Manual QA Checklist

- Browsers: Chrome, Firefox, Edge, Safari
- Devices: Windows desktop app (X), Android Chrome, iOS Safari
- Local Dev: https://localhost:8443
  - SSL trusted, no redirect loops with `.htaccess.dev`
  - Join form preserves inputs on validation errors
  - Avatar optional: default avatar is stored; `has_custom_avatar=0`
  - X share: prefill text and referral URL; works if popup blocked
  - IPA Constitution link in About section points to GitHub Pages
  - All external links have `rel="noopener"`; internal navigation maintains context

## Data Seeding (local dev)

- MariaDB: create minimal member fixture for UI
  ```sql
  INSERT INTO ipnz_members (name,email,phone,join_type,additional_request,avatar_url,has_custom_avatar,status)
  VALUES ('Test Member','test@example.com','028-25578835','early_access','Fixture','https://models.readyplayer.me/64bfa15f0e72c63d7c3934a6.png',0,'active');
  ```

## Next Steps

- Add Playwright + PHPUnit scaffolding
- Wire CI to run Lighthouse
- Seed data script for local Docker
