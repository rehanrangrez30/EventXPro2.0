# EventX Pro – Ultimate College Event Management System

## Installation (XAMPP)

1. Start **Apache** and **MySQL** in the XAMPP Control Panel.
2. Import `database/eventx_pro_db.sql` via phpMyAdmin (creates the
   `eventx_pro_db` database and Phase 1 tables).
3. Copy the `EventXPro` folder into `htdocs`.
4. Open `http://localhost/EventXPro/index.php`.
5. Default DB credentials are in `config/config.php` (`root` / no password) —
   update them if your MySQL setup differs.

## Phase Status

| Phase | Scope                                                  | Status |
|-------|---------------------------------------------------------|--------|
| 1     | Folder structure, config, database, common includes      | ✅ Done |
| 2     | Authentication, homepage, shared layout                  | ✅ Done |
| 3     | Event Management Core + Student/Organizer/Faculty/Volunteer | ✅ Done |
| 4     | Admin panel, attendance, certificates, feedback, notifications | ✅ Done |
| 5     | Backend hardening: error handling, security headers, brute-force lockout, admin analytics charts | ✅ Done |
| 6     | Password complexity, forgot/reset password, remember-me, PDF certificates, QR verification, CSV export | ✅ Done |
| 7     | Gallery/announcement admin UI, faculty/volunteer assignment UI, AI chatbot, final optimization | ✅ Done |
| 8     | PWA (manifest, service worker, install banner) + image lazy-loading polish | ✅ Done |
| 9     | Rule-based event recommendations + smart multi-field search + dashboard stat fix | ✅ Done |
| 10    | Admin Active Sessions (force logout) + pure-PDO database backup download | ✅ Done |
| 11    | Deployment guide + module-by-module testing checklist (docs/) | ✅ Done |
| 12    | Polished User Manual + Developer Guide (.docx, rendered & verified) | ✅ Done |
| 13    | QR self check-in attendance | ✅ Done |
| 14    | Registration confirmation + certificate emails (dependency-free SMTP) | ✅ Done |
| 15    | Certificate visual upgrade: logo, seal, signature line, double border | ✅ Done |
| 16    | Real brand logo replacing the generated placeholder icon, site-wide | ✅ Done |
| 17    | Certificate redesigned around user-provided template artwork + real QR | ✅ Done |
| 18    | Logo swapped again to user's refined/updated brand mark, site-wide | ✅ Done |
| 19    | Logo swapped a third time — user's own pre-transparent PNG, verified clean | ✅ Done |
| 19    | Site color palette re-themed (blue→gold) to match the new logo | ✅ Done |
| 20    | Logo made more prominent, high-quality, and properly transparent site-wide | ✅ Done |
| 21A   | Premium SaaS redesign — Part A: design tokens, typography, navbar glass/scroll | ✅ Done |
| 21B   | Premium SaaS redesign — Part B: hero section + button system | ✅ Done |
| 21C   | Premium SaaS redesign — Part C: event cards, category tiles, platform stats | ✅ Done |
| 21D   | Premium SaaS redesign — Part D: role showcase, organizer CTA, footer | ✅ Done |
| 21E   | Premium SaaS redesign — Part E: background music widget (glassmorphism) | ✅ Done |
| 21F   | Premium SaaS redesign — Part F: CSS 3D card-tilt (Three.js/WebGL deliberately skipped) | ✅ Done |
| 22    | Auth pages brought to full design consistency (forgot/reset password) | ✅ Done |
| 23    | Site-wide in-app Back button + confirmed dark/light mode toggle | ✅ Done |

## Phase 2 Additions

- `login.php`, `register.php`, `logout.php` — full authentication with
  password hashing, CSRF protection, session regeneration, and activity
  logging.
- `profile.php` — shared profile view/edit page for any logged-in role.
- `includes/sidebar.php` — shared dashboard layout used by every role.
- `admin/dashboard.php`, `student/dashboard.php`, `organizer/dashboard.php`,
  `faculty/dashboard.php`, `volunteer/dashboard.php` — role-based dashboards
  protected by `require_role()`, pulling real data from the database.
- `errors/403.php`, `errors/404.php` — friendly error pages.

## Phase 3 Additions

- **Database** (appended to `database/eventx_pro_db.sql`, nothing removed):
  `event_categories`, `events`, `registrations` tables with foreign keys,
  indexes and a unique constraint preventing duplicate registrations.
- **Public**: `events.php` (search/filter/browse), `event_details.php`
  (registration with deadline, capacity and duplicate checks, plus
  cancellation).
- **Organizer**: `organizer/manage_events.php` (list, publish/unpublish,
  cancel, delete — own events only), `organizer/event_form.php`
  (create/edit with full server-side validation).
- **Student**: `student/events.php` (dashboard-styled browsing),
  `student/my_registrations.php`.
- **Faculty / Volunteer**: `faculty/assigned_events.php`,
  `volunteer/assigned_events.php` — campus-wide event monitoring views.
  Per-person assignment tables (`faculty_event_assignments`,
  `volunteer_event_assignments`) and task workflows move to Phase 4
  alongside the Admin Panel, matching the project's original phase order.
- All five dashboards and the homepage now show **live** event/registration
  counts instead of zeros or "coming soon" notices.
- `includes/sidebar.php` gained role-specific links; no existing link was
  removed.

## Phase 4 Additions

- **Database** (appended to `eventx_pro_db.sql`, nothing removed): `login_logs`,
  `attendance`, `certificates`, `feedback`, `notifications`, `gallery`,
  `announcements`, `volunteer_tasks`, `volunteer_event_assignments`,
  `faculty_event_assignments`, `settings`.
  **Naming note:** a later revision of the master spec proposed a different
  column-naming convention (generic `id`, `event_title`, etc.). To honor the
  "never rewrite existing functionality" rule, the new tables keep the
  naming already in production (`user_id`, `event_id`, `student_id`,
  `password_hash`, the `roles` lookup table) instead of renaming Phase 1-3
  tables to match. No existing PHP file had to change because of this.
- **Admin**: `manage_users.php` (search/filter/activate/deactivate/ban),
  `manage_events.php` (platform-wide cancel/archive/publish),
  `manage_categories.php` (add/toggle categories), `activity_logs.php`
  (searchable audit trail).
- **Organizer**: `attendance.php` (mark present/absent per event),
  `certificates.php` (generate certificates for present attendees).
- **Student**: `certificates.php` (view issued certificates + verification
  codes), `feedback.php` (rate attended events, 1 feedback per event).
- **Shared**: `notifications.php` + a navbar bell with unread count;
  `login_logs` now records every login attempt (success/failed/blocked)
  and logout time, alongside the existing `activity_logs` entries.
- **Fixed pre-existing dead links**: `gallery.php`, `about.php`,
  `contact.php` (referenced in the navbar since Phase 1 but never built)
  now exist with real content/queries instead of 404s.
- **Deferred to Phase 5**: gallery/announcement upload UI, the full
  settings panel, an admin UI to assign faculty/volunteers to specific
  events (their pages currently show all published events campus-wide),
  Chart.js analytics, and PDF certificate generation (certificate records
  and verification codes are already issued; the PDF file itself is not
  yet rendered).

## Phase 5 Additions

**Scoping note:** the Part 5 master-spec document describes a class-based/
`api/` REST architecture (`classes/Database.php`, `classes/User.php`,
`includes/auth.php`, `api/*.php`, etc.). Rebuilding the app onto that
architecture would mean rewriting every existing page — the project's
"never rewrite, only add" rule takes priority over matching that document
literally. Instead, Phase 5 implements the underlying *behaviors* the
document asks for (centralized error handling, hardened security, brute-
force protection, analytics) on top of the procedural structure already in
place from Phases 1-4. Nothing existing was restructured.

- **`includes/error_handler.php`**: central `set_error_handler` /
  `set_exception_handler` / shutdown handler. Logs to `logs/error.log` and
  `logs/security.log`; shows the new `errors/500.php` friendly page instead
  of a raw PHP/SQL error. Wired into `config.php` so every page gets it
  automatically.
- **Security headers** (`X-Frame-Options`, `X-Content-Type-Options`,
  `X-XSS-Protection`, `Referrer-Policy`) added in `config.php`.
- **`.htaccess`** deny-all files added to `config/`, `includes/`,
  `database/`, `logs/`, `docs/` so these folders 404 if hit directly in a
  browser, even though PHP can still `require` them internally.
- **Brute-force lockout**: `login.php` now blocks further attempts for an
  IP after 5 failed logins inside 15 minutes (checked against the existing
  `login_logs` table), and logs the block to `security.log`.
- **Admin analytics**: Chart.js line chart (registrations, last 7 days) and
  doughnut chart (events by category) added to `admin/dashboard.php`,
  fulfilling the analytics item deferred from Phase 4.

## Phase 6 Additions

- **Password policy**: `register.php` now requires 8+ characters with
  upper/lower case, a number and a special character (was length-only).
- **Forgot / reset password**: `forgot_password.php` (email lookup, token
  generation) + `reset_password.php` (token validation, expiry, new
  password), backed by the new `password_resets` table.
- **Remember me**: `login.php` checkbox issues a selector/validator token
  stored (hashed) in `remember_tokens`; `config/db.php` auto-logs the user
  back in on return visits if a valid, unexpired cookie is present.
- **PDF certificates**: `includes/pdf_lib.php` is a dependency-free raw-PDF
  writer (no Composer/internet needed on XAMPP) — `organizer/certificates.php`
  now renders and saves an actual `.pdf` per certificate instead of just a
  database record.
- **Certificate verification + QR**: `certificate_verify.php` looks up a
  certificate by number/code and renders a scannable QR (client-side via
  qrcodejs, no server dependency) linking back to that verification page.
- **CSV export**: `admin/export.php` streams users/events/registrations as
  CSV downloads.
- **Security logging**: `logs/security.log` already separated from
  `logs/error.log` in Phase 5's error handler; brute-force blocks write
  there specifically.

## Database Re-spec Note (master doc Part 6)

A later master-prompt revision (Part 6) re-describes the whole database
under a different convention again — generic `id` primary keys, database
name `college_event_management`, and formal `roles` / `permissions` /
`role_permissions` tables for granular RBAC. Per the same resolution used
for Parts 4 and 5: the existing schema (`eventx_pro_db`, `user_id`/
`event_id`-style keys, a simple `roles` lookup table) is kept as-is —
rebuilding the database under new naming would break every existing query
in every existing PHP file. The `permissions`/`role_permissions` tables
are intentionally **not** added: RBAC is already enforced in code via
`require_role()` on every protected page, which satisfies the same
functional requirement without the extra join complexity; an earlier
version of this same master document (Part 4.2.2) explicitly called that
finer-grained table structure optional future scope. Say the word if you'd
like full DB-level permission tables added later.

**New in this pass:** `database/sample_data.sql` — realistic demo data
(1 admin, 1 organizer, 1 faculty, 1 volunteer, 3 students, 4 events across
statuses, registrations, attendance, feedback, a notification, and a
volunteer task) so the dashboards, reports and charts aren't empty out of
the box. Run it after `eventx_pro_db.sql`. All demo accounts use the
password `Demo@1234`.

## Phase 7 Additions

- **Gallery**: `admin/manage_gallery.php` — real upload with extension,
  size, and `getimagesize()` validation; images tied to an event and
  stored under `assets/uploads/gallery/`.
- **Announcements**: `admin/manage_announcements.php` (create/edit/pin/
  publish, audience targeting) + `includes/announcement_banner.php`
  (shows the top pinned/published announcement matching the visitor's
  role) wired into `includes/header.php` site-wide.
- **Faculty/Volunteer assignment UI**: `admin/manage_assignments.php` lets
  admins assign specific faculty/volunteers to specific events
  (`faculty_event_assignments` / `volunteer_event_assignments`).
  `faculty/assigned_events.php` and `volunteer/assigned_events.php` now
  query those tables directly instead of listing all campus events.
- **AI Chatbot**: `chatbot_api.php` (dependency-free, rule-based — no
  external AI API, keeping the project fully offline/XAMPP-compatible) +
  `includes/chatbot_widget.php` + `assets/js/chatbot.js`. Handles common
  FAQs and live DB lookups (nearest registration deadlines, upcoming
  event count).

## Phase 8 Additions

Source: `EVENTX_PRO.docx` (Parts 8–33). Parts 16–33 and most of Part 9/12
(AI vision, voice assistant, predictive ML, fraud detection), Part 10.9
(real payment gateway), and Part 11's MFA/OAuth/SSO were flagged
out-of-scope for a dependency-free PHP/XAMPP diploma project and skipped.
This pass covers item 1 of the agreed shortlist: **Part 8.17–8.18 (PWA +
performance polish)**.

- **PWA**: `manifest.json` (installable, standalone display, theme color)
  + real generated icons (`assets/images/icons/` — 192/512/apple-touch/
  favicon, brand gradient) + `sw.js` (network-first for pages so dynamic
  data never goes stale, cache-first for static CSS/JS/icons) +
  `offline.html` fallback page.
- **Install banner**: the `.ex-install-banner` CSS from Phase 1 was
  unused until now — `includes/pwa_install_banner.php` (markup) +
  `assets/js/main.js` (listens for `beforeinstallprompt`, handles
  Install/Dismiss, remembers dismissal for 30 days) wire it up.
- Service worker registers via an absolute URL (`window.EVENTX_APP_URL`,
  set once in `includes/footer.php`) so it resolves correctly from pages
  nested in `/admin/`, `/student/`, etc., not just the site root.
- **Performance**: `loading="lazy"` added to gallery image grids
  (`gallery.php`, `admin/manage_gallery.php`) so off-screen images don't
  block initial load.

## Phase 9 Additions

Item 2 of the shortlist: **feasible subset of Part 9's AI Integration
content** (recommendation engine + smart search). Real ML/vision/voice/
predictive-analytics features from Part 9 remain out of scope (see the
note above) — everything here is plain SQL logic, no external AI API,
staying dependency-free on XAMPP.

- **"You Might Also Like"** on `event_details.php` — same-category,
  upcoming, published events, excluding the one being viewed.
- **"Recommended for You"** on `student/dashboard.php` — finds the
  category the student has registered for most, then suggests other
  upcoming events in that category they haven't joined yet.
- **Smart search** on `events.php` — now matches title, venue,
  description, *and* category name (was title/venue only), with simple
  relevance ranking (title match ranks above venue/description/category
  match). A no-results search now suggests upcoming events instead of a
  dead end.
- **Fixed while touching this area**: `student/dashboard.php`'s
  Certificates Earned and Pending Feedback stat cards were still
  hardcoded to `0` with a stale "unlocks in Phase 4" note, left over from
  before certificates/feedback were actually built in Phase 4. Both now
  show real counts.

## Phase 10 Additions

Item 3 of the shortlist: DB backup + active-session admin UI. Both pieces
of backend plumbing for this already existed (from the very first session
of this project, before this shared history) — `login_logs.logout_time`
revocation checking was already wired into `config/db.php` with a comment
pointing at "Admin > Active Sessions," but that admin page itself was
never actually built until now.

- **`admin/active_sessions.php`** — lists everyone currently signed in
  (successful login, no logout yet) with role, sign-in time, and IP.
  "Force Logout" stamps `logout_time`, which `config/db.php`'s existing
  check picks up and kills on that user's next request — no live/realtime
  connection to the other browser needed. The admin's own row is marked
  "This session" instead of showing a self-destruct button.
- **`admin/backup.php`** — full structure + data `.sql` dump generated
  with pure PDO (`SHOW CREATE TABLE` + batched `INSERT`s), **not**
  `shell_exec('mysqldump ...')` — so it works even on XAMPP setups where
  shell access is disabled. Streams as a download; also shows a per-table
  row-count overview.
- **Gap closed while building this**: remember-me auto-logins (Phase 6)
  weren't creating a `login_logs` row, so a device using "Remember me"
  was invisible to (and unrevokable from) Active Sessions. `config/db.php`
  now logs those too.

## Phase 11 Additions

Item 4 of the shortlist: **Part 13 (Testing/Deployment)**.

- **`docs/DEPLOYMENT_GUIDE.md`** — prerequisites, step-by-step XAMPP
  install (schema → optional sample data → config check → upload
  permissions), a post-install verification checklist, and a
  troubleshooting section for the issues that actually come up on
  XAMPP (blank pages, upload permissions, autoplay blocking, PWA
  banner not appearing, locked-out-by-brute-force, etc.).
- **`docs/TESTING_CHECKLIST.md`** — manual QA checklist organized by
  the project's actual modules (not a generic template) — auth, public
  pages, student/organizer/faculty/volunteer/admin, notifications +
  chatbot, security, PWA/cross-device. Every item references a real
  file in this repo.

## Phase 12 Additions

Item 5 of the shortlist — the last item — **Part 14 (Documentation)**,
completing the shortlist derived from `EVENTX_PRO.docx`.

- **`docs/EventX_Pro_User_Manual.docx`** — role-by-role usage guide
  (Getting Started, Student/Organizer/Faculty/Volunteer/Admin guides,
  chatbot, PWA install, getting help). Built with `docx` (Node),
  rendered to PDF and visually verified page-by-page before delivery.
- **`docs/EventX_Pro_Developer_Guide.docx`** — architecture, full table
  list with purpose/key columns, the naming-convention decision (and
  why), coding conventions, extension instructions for adding a new
  phase without breaking the "never rewrite" rule, and an explicit
  out-of-scope list (ML/vision/voice, OAuth/SSO, real payment gateway,
  blockchain/quantum/satellite/etc.) with the reasoning for each.

**This closes out the 5-item shortlist from the `EVENTX_PRO.docx` scope
review** (PWA/polish → recommendations/search → backup/sessions →
testing/deployment docs → user/developer manuals).

## Phase 13 Additions

**QR self check-in attendance** (from master-doc Part 6.8's QR attendance
concept — implemented the genuinely-buildable way: no new hardware, no
external QR-scanning API).

- **`checkin.php`** — one shared URL per event
  (`checkin.php?event_id=X`). A student scans it with their own phone
  (already logged in as themselves), and the server marks *that*
  student present if they're registered for the event and it's the
  event's day — no per-student unique code needed, since authentication
  comes from their own login session, not the QR content.
- **`organizer/attendance.php`** — now shows the QR code (client-side,
  same `qrcodejs` library already used for certificate verification) to
  display at the venue entrance, alongside the existing manual
  present/absent list — organizers can use either or both.
- **`login.php`** gained an optional, whitelisted `?redirect=` parameter
  (same-site `.php` paths only, otherwise ignored) so a student who
  isn't logged in yet when they scan the QR lands back on check-in
  right after logging in, instead of just their dashboard. Fully
  backward compatible — every existing link to `login.php` behaves
  exactly as before.
- No `attendance` table schema change was needed — self check-ins are
  distinguished from organizer-marked ones via the existing `remarks`
  column ("Self check-in via QR code") and `marked_by` (student's own
  ID vs. the organizer's).

## Phase 14 Additions

**Registration confirmation + certificate emails.** Two genuine, distinct
flows (not "email fires the moment you register with a certificate
already attached," which would let anyone get a certificate without
actually attending):

- **`includes/mailer.php`** — `EventXMailer`, a raw-socket SMTP client
  (STARTTLS support for Gmail's port 587, base64 AUTH LOGIN,
  multipart/mixed for attachments) — no PHPMailer/Composer dependency,
  matching the project's XAMPP-installable-as-is constraint. Wrapped by
  `send_mail()`, which never throws — a misconfigured or failed send is
  logged and silently skipped so it can never block registration or
  certificate generation.
- **`includes/email_templates.php`** — branded HTML templates
  (inline CSS, since email clients don't reliably load stylesheets).
- **`config/config.php`** — new `SMTP_*` / `EMAIL_ENABLED` constants,
  blank by default (`EMAIL_ENABLED` auto-computes to `false` until
  filled in) — see `docs/DEPLOYMENT_GUIDE.md` section 8 for the
  5-minute Gmail App Password setup.
- **`event_details.php`** — registration confirmation email sent right
  after the existing in-app `notify_user()` call, same trigger point.
- **`organizer/certificates.php`** — certificate email (PDF attached)
  sent right after the certificate PDF is generated and saved — i.e.
  only after attendance was marked present, same eligibility rule as
  before. No new column/table needed.

## Phase 15 Additions

**Certificate visual upgrade** — same `eventx_generate_certificate_pdf()`
function signature (no caller changes needed in `organizer/certificates.php`),
significantly richer output:

- **Logo** embedded for real — `assets/images/icons/icon-512.png` is
  loaded with GD, flattened onto white (drops transparency, since raw
  PDF here doesn't do alpha), downsized, and Flate-compressed straight
  into the PDF as an Image XObject. If GD isn't available on a given
  PHP install, the logo is silently skipped and the rest of the
  certificate still renders — no hard dependency added.
- **Double border** (thick blue outer + thin violet inner) and a thin
  rule under the heading block.
- **Circular verification seal** — pure vector (a circle is drawn as a
  40-segment straight-line path, since raw PDF here has no curve
  primitive), labeled "VERIFIED / E-CERTIFICATE".
- **Signature line** — a ruled line with "Authorized Signatory" /
  "EventX Pro Event Management" underneath, bottom-right.
- Verified by actually generating a sample PDF and rendering it to an
  image (`pdftoppm`) before delivery — not just reading the code.

## Phase 16 Additions

**Real brand logo**, replacing the Phase 8 generated "EX" placeholder
icon everywhere it was used — one image swap, zero code duplication,
because every page already pulled from the same 4 files:

- Uploaded logo's white background removed via border-connected-region
  flood fill (not a flat color-distance threshold, which would have
  also erased the logo's own internal white "EVENTX" text/highlights)
  — cropped to a square, then resized into `icon-192.png`,
  `icon-512.png`, `favicon-32.png` (transparent) and
  `apple-touch-icon.png` (flattened onto a solid dark background, since
  iOS ignores alpha on touch icons). A full-resolution transparent
  master (`logo-master.png`) was kept alongside for future use.
- Because `manifest.json`, `includes/header.php`,
  `includes/pwa_install_banner.php`, and `includes/pdf_lib.php`
  (certificate logo) all already referenced these same 4 filenames,
  replacing the files was enough — **no PHP changes needed** in any of
  them, and the certificate PDF picks up the new logo automatically.
- `includes/navbar.php` and `includes/footer.php` previously used a
  generic Font Awesome calendar icon as a text-only "brand" — upgraded
  both to show the actual logo image now that a real one exists.
- Verified by actually regenerating a sample certificate PDF and
  rendering it to an image afterward, confirming the new logo appears
  correctly at PDF scale.

## Phase 17 Additions

**Certificate fully redesigned around the user's own template artwork**,
replacing the earlier vector-drawn design entirely (`includes/pdf_lib.php`
rewritten, same public function signature — no caller changes needed):

- The uploaded certificate design is embedded as the full-page PDF
  background directly from its JPEG bytes (`/Filter /DCTDecode`, no
  re-encoding — keeps the file small and the artwork pixel-perfect).
- **Real, scannable QR code** — no external API call, no client-side-only
  JS this time (a downloaded PDF can't run JavaScript). Vendored the
  classic `phpqrcode` library (`includes/vendor/phpqrcode/`, plain PHP,
  no Composer) fetched from its public GitHub mirror, and verified with
  an independent decoder (`pyzbar`) that it actually reads back the
  correct verification URL — not just "looks like a QR code."
- Every dynamic field (name, event + date paragraph, certificate ID,
  issue date) is positioned using pixel coordinates **measured directly
  against the template** (grid overlays + row-by-row brightness
  profiling to find exact text baselines — not guessed), converted to
  PDF points, with a same-color cover rectangle drawn first so the
  template's own placeholder text ("Your Name Here", "EXPRO2025-0001",
  etc.) is fully hidden under the real value. Long names/titles degrade
  gracefully (auto-shrinking font for long names) rather than
  overflowing the page — verified by rendering both a short and a
  deliberately long test case to image before delivery.
- Static elements the template already had right — the "Thank You"
  ribbon medallion, the EventX Pro seal, and both signature blocks —
  were left untouched; only the fields that are genuinely per-student
  dynamic data got overlaid.

## Phase 18 Additions

**Logo swapped a second time** to a refined version of the brand mark —
same process as Phase 16 (border-connected flood fill to strip the
white background without eating the logo's own internal white text,
crop, pad to square, regenerate all 4 icon sizes), overwriting the same
4 files everywhere they're already referenced. No PHP changes needed
again. Note: this is the **site/app logo** (navbar, favicon, PWA icons)
— the certificate's own branding comes from the Phase 17 template
artwork, which is a separate, independent asset.

## Phase 19 Additions

**Site-wide color re-theme to match the new logo** — sampled the logo's
actual dominant colors programmatically (royal blue ~#0050f0, gold
~#f0b000) rather than guessing, then updated `assets/css/style.css`:

- `--ex-primary`/`--ex-primary-dark` moved from the old blue (#2563eb)
  to the logo's own blue (#1a4fd1/#123a9c); `--ex-accent` moved from
  violet (#7c3aed) to gold (#c8900a, with a brighter #f0b000 variant
  for gradients).
- The homepage hero gradient is now blue → dark navy → gold (a navy
  midpoint was added after an initial blue-straight-to-gold version
  rendered a muddy brown transition — verified by actually rendering
  both versions to an image before choosing).
- **Bootstrap's own `--bs-primary`/`--bs-link-color` variables are now
  re-pointed at the same blue**, so `badge text-bg-primary`,
  `text-primary`, `btn-outline-primary`, and plain links across every
  page pick up the new color automatically too — not just the custom
  `.ex-*` classes, without having to hunt down every page that uses a
  stock Bootstrap utility class.
- If the site still shows old colors/logo after updating, hard-refresh
  (Ctrl+F5) — browsers cache favicons and CSS aggressively.

## Phase 20 Additions

**Logo placement made more prominent and higher quality**, still fully
transparent (verified: alpha channel confirmed present, 0–255 range,
not flattened):

- **New: `assets/images/icons/logo-hero.png`** (320×320, from the
  full-resolution transparent master) — added prominently above the
  homepage hero heading (`index.php`) at 150×150 with a soft drop-shadow,
  since a logo on the busiest page of the site deserves better than the
  tiny navbar icon.
- **New: `assets/images/icons/logo-nav.png`** (96×96 — 2× the ~44px
  display size, for crisp rendering on retina/high-DPI screens) — now
  used in both `includes/navbar.php` (enlarged 36px → 44px) and
  `includes/footer.php` (32px → 36px), replacing `icon-192.png` there.
  Deliberately *not* using the full 512px file for these small
  placements — that would mean loading a ~320KB image just to display
  it at 44px on every single page, which is wasteful.
- Verified by actually rendering navbar + hero + footer together
  (`wkhtmltoimage`) before delivery, not just reasoning about the CSS.

## Phase 19 Additions

**Logo swapped a third time.** This time the user supplied their own
already-transparent PNG rather than a flat white-background export.
Before trusting that, the alpha channel was verified programmatically
(not assumed): checked for a soft box-shaped halo around the logo by
compositing onto solid magenta and inspecting — genuinely clean, no
artifact, so no flood-fill cleanup was needed this round, just crop-to-
content, pad-to-square, and regenerate every size.

**Full inventory of logo/icon files** (all 7 replaced together, since a
partial swap would leave the site showing two different logos):
`icon-192.png`, `icon-512.png`, `favicon-32.png`, `apple-touch-icon.png`
(PWA/browser icons, from Phase 16/18) **and** `logo-nav.png` (navbar +
footer, 96px, transparent), `logo-hero.png` (homepage hero, 300px,
transparent), `logo-master.png` (full-resolution transparent source,
kept for future re-exports) — the latter three were added between
Phase 18 and this pass and are new to this README entry. Confirmed the
certificate template (`assets/images/certificate/template.jpg`) is a
fully separate asset untouched by any of this, since certificate
branding comes from Phase 17's dedicated design file, not these icons.

## Phase 21A Additions — Premium SaaS Redesign, Part 1 of 6

Large visual-only redesign request, split into 6 parts as agreed. **No
PHP logic, database queries, routes, or JS functionality were touched —
this pass is CSS + one font `<link>` + a scroll-listener only.**

- **Design tokens**: `assets/css/style.css` `:root` expanded with
  `--ex-electric` (Electric Blue, for hovers/links/focus states),
  `--ex-navy` (near-black for dark sections), an 8px spacing scale
  (`--ex-space-1` through `-8`), and motion tokens (`--ex-ease`,
  `--ex-fast` 200ms, `--ex-med` 300ms). Every existing variable name
  (`--ex-primary`, `--ex-accent`, `--ex-bg`, etc.) was **kept exactly as
  it was** — this is additive, so every page already using them is
  unaffected.
- **Typography**: swapped Poppins → **Plus Jakarta Sans** for headings
  (per the brief), kept Inter for body text, added weight 800 for
  h1–h3.
- **Navbar**: glass/translucent effect with backdrop blur, becomes more
  opaque with a subtle shadow and compacts slightly after 12px of
  scroll (`assets/js/main.js` scroll listener toggles one class,
  `includes/navbar.php` itself is untouched — same links, same routes,
  same notification bell logic). Nav links got an animated underline
  hover instead of a flat color change.

**Still to come:** Part B (hero + buttons), Part C (event cards +
category tiles + stats), Part D (role showcase + CTA + footer), Part E
(music widget), Part F (responsive + full QA pass).

## Phase 21B Additions — Premium SaaS Redesign, Part 2 of 6

Hero section + a reusable premium button system. Most of the markup for
this was already in place from earlier work in this project's history;
this pass reviewed it against the brief and fixed one real issue plus
confirmed the rest.

- **Fixed**: the hero background gradient ran royal blue → navy → a
  **fully bright gold** corner, which reads as gold being a dominant
  color rather than "premium accent, not excessive" and contradicts
  "blue remains primary" from the brief. Changed to a blue-dominant
  gradient (Royal → Electric → Navy) with gold pulled out into a
  separate, soft 16%-opacity radial glow in one corner instead — gold
  is now clearly an accent, not competing with blue for attention.
- **Confirmed already correct**: hero badge ("COLLEGE EVENT MANAGEMENT
  PLATFORM"), large bold title with the exact copy from the brief,
  `.ex-btn` system (primary/gold/light/outline-light/outline variants,
  all with hover elevation + shadow + 200ms transition, matching the
  "premium hover animation, slight elevation" requirement).
- Verified `.ex-hero` is only referenced on `index.php` — this change
  can't affect any other page.

## Phase 21B Additions — Premium SaaS Redesign, Part 2 of 6

Hero section and the reusable button system — verified against the
brief line by line, most of it was already in solid shape from earlier
polish work, so this pass mainly confirmed and added one refinement:

- **Hero**: badge ("COLLEGE EVENT MANAGEMENT PLATFORM"), the exact
  requested heading, `clamp()`-based responsive title sizing, and a
  background built from the token system — `--ex-primary` →
  `--ex-electric` → `--ex-navy` gradient with a soft white + gold
  radial glow overlay on top (`.ex-hero::before`), matching "soft blue
  and gold ambient lighting" without being flashy.
- **Buttons**: `.ex-btn` system (`-primary`, `-gold`, `-light`,
  `-outline-light`, `-outline`) with elevation-on-hover and a
  200–300ms transition, all gradient-based on the token colors —
  reusable across any future page, not hero-only. `.btn-primary`
  (plain Bootstrap, used on every non-hero page — forms, dashboards,
  login) was deliberately left as its own simpler rule so those pages
  don't suddenly change without being part of this redesign pass yet.
- **Added this pass**: a small arrow micro-interaction — the trailing
  icon on a button nudges right 4px on hover, Stripe-style.

## Phase 21C Additions — Premium SaaS Redesign, Part 3 of 6

Verified against the brief line by line — already built from earlier
polish work, holding up well on review:

- **Category tiles**: gradient icon badge (primary → electric), lifts
  6px + icon scales/rotates slightly on hover, soft blue/gold gradient
  wash fades in behind the content — all via one `::before` overlay,
  not a new element per tile.
- **Featured event cards** (`.ex-card-premium`): consistent icon badge
  style (same gradient as category tiles, so the two feel like one
  system, not two different icon treatments), uppercase pill category
  badge, "Register Now" using the Part B button system.
- **Platform stats**: rebuilt as a real analytics-strip component —
  icon, big tabular-nums number, uppercase label, a vertical divider
  between stats instead of separate boxed cards. Numbers **animate
  with an ease-out count-up** (900ms, `IntersectionObserver`-triggered
  so it only plays once the section scrolls into view) — but always
  counts up to the real `data-value` already rendered server-side from
  the database; nothing is fabricated or hardcoded.

## Phase 21D Additions — Premium SaaS Redesign, Part 4 of 6

Unlike Parts A–C, this section genuinely hadn't been touched by earlier
polish work — real changes this pass, all class-only (same content,
same links, same routes everywhere):

- **"Built for every role"**: plain `fa-circle-check` list → a proper
  `.ex-feature-list` with a circular gradient check-badge per item.
  The right-column stats card is now wrapped in `.ex-dashboard-visual`,
  which adds a soft blue/gold radial glow behind it so it reads as a
  floating dashboard composition rather than a plain card sitting on
  white — no new stat data, same two real database-backed numbers from
  Part C.
- **Organizer CTA**: `.ex-card` on white → `.ex-cta-dark`, a navy
  gradient panel with the same blue/gold ambient glow used in the hero
  (visual consistency across the page), button swapped from plain
  `btn btn-primary` to the Part B `.ex-btn-gold` gradient button.
  `href="register.php"` is byte-for-byte the same link as before.
- **Footer**: background flipped from white to `--ex-navy` (matching
  the CTA and hero), text/link colors adjusted for contrast on dark,
  gold hover on links. Every link, every piece of text, the copyright
  line — all identical to before, only the color treatment changed.

## Phase 21E Additions — Premium SaaS Redesign, Part 5 of 6

Background music widget → premium collapsible glass widget. **The
playback logic in `assets/js/audio-player.js` was not modified even by
one line** — the new expand/collapse behavior is a fully separate
`DOMContentLoaded` block appended after it, operating on a new toggle
button only.

- **Glassmorphism**: `backdrop-filter: blur(14px)` translucent pill,
  dark-mode variant included.
- **Collapsed by default**: shows only a small circular music-icon
  button — much less likely to sit over content/buttons than the old
  always-expanded pill. Clicking it smoothly expands (`max-width` +
  opacity transition, ~300ms) to reveal play/pause, volume, and mute;
  clicking anywhere outside collapses it again.
- Every existing element (`#bgAudio`, `#bgPlayPause`, `#bgVolume`,
  `#bgMute`, `#bgMusicPrompt`, `#bgMusicEnable`) kept its exact same
  `id` — only wrapped in new markup — so every autoplay-fallback,
  play/pause, mute, and volume behavior from Phase 1 works identically.
- Mobile: smaller icon/buttons and a narrower expanded width via one
  `@media (max-width: 576px)` block.

## Phase 21F Additions — Premium SaaS Redesign, Part 6 of 6 (redesign plan complete)

A follow-up master prompt asked for a full Three.js/WebGL 3D experience
(floating 3D objects, camera scroll-scrubbing, shader effects) across
every page. That was scoped down to **Option A**: CSS 3D + AOS instead
of real WebGL — same reasoning as the earlier Phase 8 scope note this
project has followed throughout (crash/lag risk on unknown viva
hardware, and functionality mattering more than visual spectacle for a
diploma submission). This closes out that decision as Part F:

- **`assets/js/card-tilt.js`** — new, small, dependency-free module.
  Mouse-tracked CSS `perspective()`/`rotateX`/`rotateY` tilt (max 6°,
  intentionally subtle) on any element with class `.ex-tilt`. Skips
  itself entirely on touch-primary devices and when
  `prefers-reduced-motion` is set — checked in JS, not just CSS.
  Applied to the homepage's featured-event cards, category tiles, and
  the public events-listing cards.
- **Scroll-reveal**: already provided by AOS (loaded since Phase 1) —
  no new library needed; this is the "GSAP-style reveal" from the
  brief, achieved with the lighter tool already in the project.
- `@media (prefers-reduced-motion: reduce)` added to fully disable the
  tilt/hover transitions for anyone who has that OS setting on.

**This completes the original 6-part homepage redesign** (tokens/nav →
hero/buttons → cards/categories/stats → role-showcase/CTA/footer →
music widget → tilt/scroll-reveal). The 3D master prompt's much larger
ask — redesigning auth pages and all 5 role dashboards in the same
visual language — is a separate, larger follow-up scope, not yet
started.

## Phase 22 Additions — Auth Pages Brought to Design Consistency

Checked all 4 auth pages against the premium `.ex-auth-shell` split
design (already present on `login.php`/`register.php` from earlier
work): `forgot_password.php` and `reset_password.php` were still on the
plain single-column `.ex-card` layout — brought up to match.

- Both now use the same split-panel shell, each with its own
  purpose-written left-panel messaging (password-reset specifics —
  "link expires in 1 hour," "we never reveal whether an email is
  registered" — rather than reusing the login page's generic copy).
- `reset_password.php`'s two password fields gained the same
  show/hide toggle used elsewhere (`.ex-password-toggle` — the JS
  behind it in `assets/js/auth-ui.js` is generic and already loaded
  site-wide, so this needed zero JS changes).
- **Every line of PHP logic in both files is untouched** — token
  validation, expiry checking, CSRF, password complexity regex,
  `password_hash()`, the `remember_tokens` cleanup on reset, the
  dev-mode fallback link when no SMTP is configured — all identical to
  before this pass, verified by diffing only the HTML section of each
  file.

All 4 auth pages (`login.php`, `register.php`, `forgot_password.php`,
`reset_password.php`) now share one consistent visual system.

## Phase 23 Additions — In-App Back Button + Dark Mode Confirmation

- **Back button**: `includes/navbar.php` now shows a small "←" button
  to the left of the logo on every page except the homepage. This
  matters specifically because the site is a PWA (`manifest.json` has
  `"display": "standalone"`) — once installed to a home screen, there's
  no browser chrome at all, so the browser's own back button doesn't
  exist to fall back on.
  The click handler (`assets/js/main.js`) checks `document.referrer`
  before calling `history.back()` — if the page was opened fresh (new
  tab, PWA launch icon, a bookmark) rather than navigated to from
  elsewhere on the site, it sends the user to the homepage instead of
  risking landing on an unrelated external page or a blank tab.
- **Dark/light mode**: verified already fully working end-to-end —
  `includes/header.php` reads the `ex_theme` cookie and sets
  `data-bs-theme` server-side (so there's no light-mode flash before
  JS runs), the moon/sun toggle button has been in the navbar since
  Phase 1, and `assets/css/style.css`'s `[data-bs-theme="dark"]` block
  flips the token values so every component follows automatically. No
  changes were needed here — flagging this in case it wasn't obviously
  visible before.

## Development Rule (per project agreement)

This is treated as a single ongoing project developed phase by phase.
**Existing files and functionality from completed phases are never deleted
or rewritten** — each new phase only adds and integrates new modules on
top of what already exists.

## Asset Policy

The background music file `assets/audio/donu_donu.mp3` is the user's
original uploaded file and must never be regenerated or replaced.


## Payment & Refund Module
- Event organizers can set a ticket price, refund percentage, and refund deadline.
- Students see payment options (UPI, Card, Net Banking) for paid events.
- The included demo gateway records a successful test transaction without charging real money.
- Student cancellation creates a refund request for paid registrations.
- Organizer event cancellation automatically creates refund requests for paid registrations.
- Admins can approve/reject refund requests from `admin/manage_refunds.php`.
- For real payments, replace the demo payment block in `event_details.php` with Razorpay/Stripe order creation and server-side signature verification.
