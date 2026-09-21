# Initial Development Phase

**Project:** Craftsmen Czech Portfolio Website Base  
**Recorded:** 2026-09-20  
**Milestone:** Initial local MVP implementation  
**Status at handover:** Website and client CMS running locally; production deployment not performed.

This document preserves the development history of the first implementation phase. It records the agreed scope, implementation, problems resolved, verification and remaining work. It is a historical snapshot, not a claim that future production checks have been completed. Record subsequent phases in separate dated documents and link them under Development history below rather than overwriting this baseline.

## 1. Requirements and scope established

The implementation followed `CraftsmenPortfolioBase_ProjectOverview_Updated.md`, which consolidated the original overview and the clarification discussion. The original overview was retained for reference.

The main decisions were:

- Each client receives an independent installation, database, credentials and released version. There is no shared tenant infrastructure or automatic distribution of new features to previous clients.
- Build and verify the base locally first. Webglobe is the selected future hosting provider; no hosting purchase or public deployment belongs to this phase.
- Use ProcessWire stable master and its native administration interface, with simplified Czech client screens.
- Client edits publish immediately. Technical settings remain outside the normal client role.
- Use replaceable demo identity, Czech copy and suitable stock photographs.
- Keep Hero and Kontakt permanently available. Other specified sections can be hidden.
- Support multi-image reference galleries and owner image uploads from the outset.
- Provide configurable simple-contact and detailed-quote forms. At least one of phone or email must remain enabled and required.
- Use Webglobe mailbox SMTP for future email delivery, without a separate Brevo account. Integrate SmsManager for optional SMS notifications.
- Keep HEIC out of the initial MVP. Use a placeholder privacy page until the operator supplies approved wording.
- Use the longest backup retention included with the future Webglobe service, rather than requiring an additional backup provider or a fixed 30-day history.

The deployment checklist and hosting compatibility shortlist were brought into line with these decisions.

## 2. Portable development environment

Installed and configured a project-local PHP/MariaDB environment, without WSL, Docker, a global PHP installation or a Windows service.

| Component | Version recorded for this phase | Purpose |
|---|---|---|
| ProcessWire | 3.0.259 stable master | CMS, pages, users, permissions, sessions and image handling |
| PHP | 8.4.25 NTS x64 | Application runtime and local development server |
| MariaDB | 11.4.10 | Separate application database, using InnoDB tables |
| PHPMailer | 6.12.0 in the dependency lock | Authenticated SMTP transport |
| Node.js | Launcher instructions require 22 or newer | Portable setup, process launch, packaging and HTTP tests |

Official PHP and MariaDB download URLs and SHA-256 checksums were recorded in `resources/runtime.json`. Composer dependencies were installed and locked in `composer.lock`.

Configured PHP extensions include PDO MySQL, mysqli, GD, mbstring, fileinfo, curl, OpenSSL, ZIP and intl. Local PHP settings use a 256 MB memory limit, 15 MB upload limit, 28 MB POST limit and 20 simultaneous PHP upload slots; the application applies stricter visitor attachment limits itself.

The local website listens on `127.0.0.1:8080`, and MariaDB on `127.0.0.1:3307`. A Node launcher starts the database, development web server and a worker loop every 60 seconds. Private configuration and generated account credentials are stored under `storage/` and excluded from clean releases.

### Windows runtime issue resolved

An initial restricted PHP execution produced a Windows “Bad Image” dialog for `php_fileinfo.dll`, with error `0xc0e90002`. Windows code-integrity evidence indicated that the extension was blocked in that execution context. The user chose to retain a portable setup instead of installing WSL.

The same portable PHP runtime successfully loaded its extensions and ran the application under approved execution. No Windows security protection was disabled. PowerShell script execution was also restricted, so the supported launch workflow was moved to Node scripts without changing Windows execution policy. Earlier PowerShell launcher files remain in the project; the README documents the Node workflow.

## 3. CMS foundation and data model

Built a fresh-install CLI bootstrap and seed process to create:

- ProcessWire configuration, unique authentication secrets and developer/client credentials.
- The `/sprava/` administration URL and Czech language configuration with AdminThemeUikit.
- A `klient` role with website-management access but without superuser, module, template, field or user-management permissions.
- Custom templates for sections, settings, services, references, enquiries and content containers.
- A JSON content field (`craft_data`) for structured application settings/content and a ProcessWire image field (`craft_images`) for owner media.
- Demo hero, about and advice sections, six services, three references, contact/settings content and a supporting privacy page.

Added database tables for submission receipts, notification jobs, temporary rate-limit counters and aggregate daily page views. Enquiry creation, submission-token recording and notification enqueueing use the same database transaction, so success is not reported before the enquiry is durably stored.

The installer refuses to overwrite an existing local configuration or populate a nonempty target database. It supports either the portable local database or a separately prepared database supplied through `CRAFT_INSTALL_CONFIG`.

## 4. Public website implemented

Created the responsive Czech homepage with a cream, forest-green and orange visual treatment, locally stored placeholder photographs and lightweight CSS/JavaScript.

Implemented:

- Responsive header, sticky navigation, mobile menu, company wordmark/uploaded logo and contact shortcuts.
- Full-width editable hero and enquiry calls to action.
- Editable O nás and Poradenství sections with images and visibility controls.
- Service cards with editable content, images, order and visibility.
- Reference cards with multiple photographs, cover-image ordering and a lightbox supporting navigation, keyboard controls and touch interaction.
- Permanent contact section with editable company details, form introduction and mobile layout placing the form before contact details.
- Independent map and address visibility settings. An enabled Mapy iframe loads immediately; the demo map remains disabled.
- Privacy supporting page, footer, editable SEO title/description, canonical URL, Open Graph metadata, favicon, sitemap and environment-aware robots output.
- Responsive image derivatives, lazy loading for non-hero images, alternative-text editing, skip link, labelled form controls and visible validation feedback.

The company mark follows the configured company name, and the displayed years-of-experience value can be edited. Photos are deliberately replaceable demo assets; their source references are in `resources/PHOTO_SOURCES.md`.

## 5. Simplified Czech client administration

Added the custom `ProcessCraft` module within the native ProcessWire admin shell.

| Client screen | Implemented responsibilities |
|---|---|
| Přehled | New/monthly enquiry counts, previous-month comparison, latest enquiries, notification failures, approaching expiry and approximate page views |
| Obsah webu | Hero, about and advice text, images and permitted section switches |
| Služby | Create/edit/delete services; change order, visibility and images |
| Reference | Create/edit/delete projects; manage descriptions, location, images, alternative text and gallery order |
| Poptávky | Enquiry details, private downloads, workflow state, retention dates/reasons, delivery status and manual retry |
| Kontakt | Company identity, phone/email, logo, years of experience, address, map and contact-form introduction |
| Nastavení | Optional sections, form mode/rules, recipients, SMS controls, SEO and privacy copy |

Saving content publishes it immediately. Hero and Kontakt cannot be disabled. Client navigation and direct admin-route checks restrict access to the intended screens. SMTP passwords and SMS API credentials remain private technical configuration rather than client-editable fields.

## 6. Forms, private files and enquiry lifecycle

Implemented simple-contact and detailed-quote modes. All predefined fields start enabled and required, except quote attachments. The client can change requirements and visibility, subject to the mandatory reply-method safeguard. Quote categories use enabled services plus “Jiné”, independently of whether the services section is visible.

Validation and submission protection include server-side field checks, CSRF tokens, a honeypot, a minimum completion time, per-hour rate limiting and submission tokens that prevent duplicate enquiries. Failed validation preserves valid submitted text. Successful submissions use a POST/redirect/GET flow with HTTP 303.

| Upload type | Accepted formats | Limits and handling |
|---|---|---|
| Visitor enquiry attachments | JPG, PNG, WebP, PDF | Maximum five files, 10 MB each and 25 MB combined; stored privately with random internal names |
| Owner website images | JPG, PNG, WebP | Maximum 15 MB per image; managed through ProcessWire image fields |

Image inputs above 40 megapixels are rejected. Uploaded content is checked against its claimed file type; visitor image validation includes file-content inspection. HEIC is rejected in this phase. Visitor attachments are downloadable only through an authorised CMS request, not through public storage URLs.

Enquiries support new, handled and archived states. Default retention is 12 months from receipt, with dashboard warnings before expiry. Extending an expiry date requires a reason. Archival does not bypass retention. The worker runs expiry cleanup daily, deleting expired enquiries and their private attachments. Captured local email files have separate 30-day cleanup.

## 7. Notification delivery implemented

Created independent durable notification jobs for each email/SMS recipient. The visitor does not wait for external delivery before receiving confirmation that the enquiry was saved.

Email uses the custom `WireMailCraft` transport: JSON capture locally and PHPMailer authenticated SMTP for future production. Visitor email becomes Reply-To when supplied. Notification content includes the enquiry details and a CMS link.

SMS uses the SmsManager API adapter, with optional client enablement and recipient settings. Local SMS delivery is mocked and does not incur charges.

Queue processing distinguishes pending, sending, accepted, failed and uncertain states. Explicit temporary rejections retry after 1, 5, 15 and 60 minutes. Permanent failures remain visible in the CMS. Interrupted or otherwise ambiguous sends are not blindly resent; the client must check for receipt before manually retrying. Provider acceptance is not presented as proof of inbox or handset delivery.

Live Webglobe SMTP and paid SmsManager delivery were not tested because production accounts and authorised live recipients were outside this phase.

## 8. Issues found and corrected during development

| Finding | Resolution |
|---|---|
| Portable PHP extension blocked during restricted execution | Verified the same runtime under approved execution; retained the portable setup and Windows security settings |
| PowerShell script execution restriction | Added Node-based setup/start/stop/worker tools |
| Local router prefix check also matched `/sitemap.xml` | Corrected route boundaries so the sitemap remained reachable |
| Default redirect status unsuitable for form submissions | Changed save/success redirects to HTTP 303 |
| Repeated submission after success could lose its token context | Retained the last submission token and checked the stored receipt |
| Core admin routes could resolve to a login page rather than a clear denial | Added a requested-route allowlist and restricted client navigation |
| Image-array removal could schedule physical image deletion during reordering | Changed to sorting the existing collection in place |
| ProcessWire image collection keys did not match numeric form indices | Added explicit numeric indices for order, description and removal controls |
| PHP upload-slot limit could truncate excess attachments before application validation | Raised the PHP slot limit while retaining the application’s five-file rule |
| Repeated local form tests exhausted the rate limit | Reset disposable local rate counters in the local-only test workflow |

Some test assertions were also corrected to match ProcessWire’s version constants and HTML-encoded Czech notices. These were test corrections, not changes to the product requirements.

## 9. Reusable setup and release tooling

Added scripts for portable runtime downloads/checksums, fresh database installation, demo seeding, Czech admin configuration, local process management, queue execution and clean release packaging.

The package builder copies the application, pinned core/dependencies, clean seed content, stock images and installation instructions into a timestamped release directory. It excludes local configuration, passwords, enquiry data, private attachments, runtime databases, sessions and client-uploaded assets. Separate client versions can therefore be installed without cloning another client’s private state.

A clean package was copied into an isolated test installation and bootstrapped against a new database. The check verified demo content, images, client permissions and zero copied enquiries. This verified the clean installation path, not a Webglobe deployment or a complete replay of every runtime download on a second Windows computer.

## 10. Verification completed

At the end of this phase:

- **23 PHP integration assertions passed**, covering core version, permissions, form defaults, reply-method safeguards, validation, transactional tables, notification failures/capture, duplicate jobs, interrupted delivery and expiry deletion.
- **36 HTTP assertions passed**, covering public/private routes, privacy/sitemap/robots, CSRF, spoofed uploads, successful/duplicate submissions, authentication, forbidden admin routes, authorised downloads, retention-extension justification, deletion and multi-image ordering without file loss.
- A **fresh isolated installation** passed its clean-data, demo-content, image and permission checks.
- Browser checks covered desktop and 390px mobile layouts, navigation, contact ordering, gallery controls and the Czech client dashboard.
- Edited PHP and Node scripts received relevant syntax checks.
- Disposable HTTP test records were removed. A manually submitted sample enquiry was retained for demonstration.

Detailed verification boundaries are recorded in `LOCAL_VERIFICATION.md`. These checks do not constitute a formal accessibility audit, penetration test, load test or validation of production hosting behaviour.

## 11. Main implementation files

Paths below are relative to the project root.

| File or directory | Role |
|---|---|
| `site/config.php`, `site/init.php`, `site/ready.php` | Configuration, URL hooks and client access/navigation restrictions |
| `site/templates/home-view.php`, `partials/`, `assets/` | Public pages, reusable markup, styles, scripts and demo images |
| `site/templates/lib/Craft.php` | Shared content, schema, settings and private-file helpers |
| `site/templates/lib/Intake.php` | Form validation, uploads and transactional enquiry creation |
| `site/templates/lib/Worker.php` | Delivery queue, retries, SMS adapter and retention cleanup |
| `site/modules/ProcessCraft/` | Custom Czech client administration screens |
| `site/modules/WireMailCraft/` | Local capture and SMTP email transport |
| `tools/bootstrap.php`, `tools/seed.php`, `tools/configure-admin.php` | Fresh installation and demo/CMS setup |
| `tools/setup.mjs`, `resources/runtime.json`, `resources/php.ini` | Portable runtime setup and pinned settings |
| `tools/local.mjs`, `tools/stop.mjs`, `tools/router.php` | Local process and development-server workflow |
| `tools/worker-loop.mjs`, `tools/worker.php` | Repeated or one-off queue processing |
| `tools/package.mjs` | Clean release assembly |
| `tests/integration.php`, `tests/http.mjs`, `tests/fresh-install.mjs` | Local integration, HTTP and installation checks |
| `storage/` | Private per-installation configuration, credentials, attachments and captured mail; not release content |

## 12. Handover state and next phase

The local review URLs are `http://127.0.0.1:8080/` and `http://127.0.0.1:8080/sprava/`. The supported startup command is `node tools/local.mjs`. Credentials are stored privately in `storage/LOCAL_ACCESS.md`; no passwords or API secrets are reproduced in this historical record.

Outstanding production work:

1. Confirm the Webglobe plan, supported runtime, Apache rules, upload limits, scheduler capabilities and actual file/database backup retention.
2. Install an independent release with its own database, credentials, domain and HTTPS configuration.
3. Replace demo company identity, photos and privacy wording; review client-specific content and form settings.
4. Configure Webglobe SMTP/domain authentication and optional SmsManager credentials; verify actual delivery with authorised recipients.
5. Configure and monitor a real scheduler. The local worker runs only while the computer is awake; the application has no public worker endpoint.
6. Verify production private-file protections, backups and restoration. Use the longest included provider retention; no additional backup service is required by the agreed scope.
7. Assign maintenance ownership: monthly security-update review, urgent fixes when necessary, backup/job failure checks, and restore rehearsals at handover and every six months.

Application updates are not automatic. The backup and maintenance schedule is an agreed operational plan, not a service already running on Webglobe. HEIC support remains deferred.

## 13. Reference documents

- `CraftsmenPortfolioBase_ProjectOverview.md` — original requirements.
- `CraftsmenPortfolioBase_ProjectOverview_Updated.md` — agreed implementation specification.
- `README.md` — current setup, operation and client editing instructions.
- `LOCAL_VERIFICATION.md` — initial local verification results and limits.
- `Deployment_Checklist.md` — checks for each future client installation.
- `Hosting_Compatibility_Shortlist.md` — dated hosting findings and outstanding provider questions.
- `resources/PHOTO_SOURCES.md` — demo photo source references.

## Development history

| Date | Phase | Record |
|---|---|---|
| 2026-09-20 | Initial development: local MVP, native Czech CMS, portable runtime, enquiry workflow and reusable installation | `Initial_Development_Phase.md` |

For subsequent stages, record the date, reason for the change, affected files/components, migrations or compatibility effects, verification performed and unresolved work. Keep this initial entry as the baseline.
