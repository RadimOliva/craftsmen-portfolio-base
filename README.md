# Craftsmen portfolio base — local MVP

A separate ProcessWire installation for each client. Czech public website and simplified native CMS; no shared client database or automatic feature upgrades. Canonical requirements: `CraftsmenPortfolioBase_ProjectOverview_Updated.md`.

## Open this installation

From the project directory, run `node tools/local.mjs` using Node.js 22 or newer. Open http://127.0.0.1:8080/ and http://127.0.0.1:8080/sprava/. Unique passwords are in `storage/LOCAL_ACCESS.md`; use `klient` for the client experience and `developer` only for technical administration. Do not publish or copy that credentials file into a release.

The launcher starts portable MariaDB, PHP's development web server, and a notification worker every 60 seconds. They run only while this computer is awake. PHP's development server is for local use only. Database port 3307 and website port 8080 bind to loopback. This local database uses a blank root password and a separate random application password: never expose it to a network or use that root setup in production.

On this computer, the restricted Codex execution context caused a Windows Bad Image error for PHP extensions. The same runtime passed its extension checks under approved execution. Run through the approved launcher or a normal terminal; do not disable Smart App Control or change Windows execution policy. No WSL, Docker, Windows service, or global PHP installation is required. Windows may need the Microsoft Visual C++ 2022 x64 runtime if it is not already available.

To stop the local processes, use `node tools/stop.mjs`. Starting the launcher again resumes work. No data is removed.

## What is included

- Responsive homepage, sticky navigation, mobile menu, editable sections, services and reference galleries with keyboard/touch navigation.
- Czech client CMS for text, images, order, visibility, contact data, form rules, privacy text and SEO. Hero and Kontakt remain visible.
- Simple contact and detailed quote forms; required-field validation, CSRF, honeypot, minimum completion time, rate limiting and duplicate protection.
- Private validated attachments, enquiry states, expiry warnings, documented retention extensions and daily deletion after the expiry date.
- Transactional enquiry and notification queue, independent email/SMS states, delayed retries and manual handling of ambiguous outcomes.
- Webglobe-compatible authenticated SMTP adapter and SmsManager API adapter. **Local email is captured in `storage/mail`; SMS is mocked.** No paid or external messages are sent by default.
- Approximate aggregate page views and enquiry statistics. Views are not unique visitors.

HEIC is deferred. Visitor uploads: JPG/PNG/WebP/PDF, five files, 10 MB each, 25 MB total. Owner images: JPG/PNG/WebP, 15 MB each. Both image paths reject images above 40 megapixels. Mapy embed and address visibility are independent; an enabled map loads immediately. Demo map is off.

## Create a clean client release

Run `node tools/package.mjs`. The resulting directory under `.runtime/releases` includes the pinned core, dependencies, application, clean seed data, stock images and instructions. It excludes runtime databases, uploaded client media, enquiries, sessions, logs, passwords and local configuration. Keep each delivered release independently versioned. Composer dependencies are included; a Composer account is unnecessary.

For a fresh portable Windows installation, copy that clean release to a new directory and run `node tools/setup.mjs`, then `node tools/local.mjs`. Setup downloads official pinned PHP/MariaDB archives and verifies SHA-256 checksums from `resources/runtime.json`. It refuses an existing installation. Internet access is needed for these downloads. Avoid running two default portable installations on the same ports at once.

For an existing PHP/MySQL environment, prepare an **empty, separate** database and a JSON file outside the web root containing `dbHost`, `dbPort`, `dbName`, `dbUser`, `dbPass`, `hosts` (array), and `baseUrl`. Set environment variable `CRAFT_INSTALL_CONFIG` to that file's absolute path, then run `php tools/bootstrap.php` followed by `php tools/configure-admin.php`. The installer refuses nonempty databases, seeds demo content, and generates unique credentials. Remove the temporary installer configuration securely after use. Do not run the seed script again on a populated installation.

Pinned local versions: ProcessWire 3.0.259 stable master, PHP 8.4.25 NTS x64, MariaDB 11.4.10, PHPMailer 6.12.0. Runtime requirements: PDO MySQL, mbstring, fileinfo, GD with JPEG/PNG/WebP, curl, OpenSSL, ZIP, writable `site/assets` and `storage`, Apache rewrite/.htaccess in production. Local tooling also enables mysqli and intl. PHP memory limit 256 MB; upload limit 15 MB; POST limit 28 MB; `max_file_uploads` 20 so the application can reject excess visitor files explicitly.

## Checks

Run `node tools/local.mjs check`, then `node tests/http.mjs` with the website running. These checks use the local database; the HTTP suite creates and removes a labelled test enquiry. Never run them on a client production database. Browser checks should cover desktop/mobile layouts, menu, gallery keyboard controls, form errors and CMS editing.

Run `node tools/local.mjs worker` for a one-off queue/retention pass. Local captures are JSON files, not a public mailbox. Notification acceptance is not proof of inbox or handset delivery. Explicit temporary email/SMS refusals retry after 1, 5, 15 and 60 minutes. Permanent failures appear in CMS; uncertain outcomes require checking receipt before a manual retry.

## Future Webglobe deployment — not performed

Follow `Deployment_Checklist.md` and confirm the provider details in `Hosting_Compatibility_Shortlist.md`. Install at a domain root, use HTTPS, verify Apache access restrictions, and deny direct access to `storage`, `tools`, `tests`, resources and vendor files. PHP's local router is not a substitute for testing production Apache rules.

In private `storage/local.php`, set the actual host allowlist/base URL, `mode` to `production`, `mailTransport` to `smtp`, Webglobe's actual `smtpHost`/`smtpPort`, mailbox `smtpUser`/`smtpPassword`, and `mailFrom`. Use port 587 with STARTTLS or 465 with TLS. Set `smsTransport` to `live` and `smsApiKey` only when authorised; the client controls SMS enablement/recipient in CMS. SMTP and SMS credentials are technical configuration, never exposed to client screens. Verify sender domain authentication, quotas and real receipt using authorised recipients. These external integrations have not been tested against paid/live accounts.

Configure a real CLI scheduler to run `php /absolute/project/tools/worker.php` every minute; daily retention runs inside the worker. If the selected hosting service supports only URL-based jobs or longer intervals, arrange a supported protected scheduler before launch: this MVP deliberately exposes no public worker endpoint. Configure scheduler failure alerts with the host/maintainer. Local worker logs alone are not production monitoring.

Replace all demo identity/photos and the marked privacy placeholder before launch. Default enquiry retention is 12 months; archival does not exempt an enquiry from expiry. Mailbox messages and provider backups have their own retention and must be covered by the operator's policy. HEIC and production legal wording remain deferred.

## Maintenance and restoration

Use Webglobe automated daily backups with the longest included retention (confirm actual file/database coverage and 14/30-day offering); no extra backup provider is required. Check backup/job failures, review security updates monthly, handle urgent vulnerabilities promptly, and test updates before applying them to an individual client. Updates are not automatically applied by this project.

Rehearse restoration before handover and every six months, plus after major infrastructure changes. Restore files **including private storage and uploaded media** and the matching database into an isolated environment. Before serving it or running workers, force `mode=local`, `mailTransport=capture`, `smsTransport=mock` and a local base URL. Verify login, content, images, enquiry attachments and queue state. Keep production outbound traffic disabled during the drill. Record date, backup used, result and recovery time. Restoration testing is preventive, not only a response to a failure.

## Client editing guide

**Přehled:** latest enquiries, counts, delivery failures and upcoming deletion. **Obsah webu:** hero/about/advice text and photos; Pruh výhod manages up to four icon/title/subtitle snippets and strip visibility. **Služby / Reference:** add items, change descriptions, order and visibility; use lower order numbers first. The first image is the cover; edit image order and alternative descriptions. **Kontakt:** identity, phone/email, address and map. **Nastavení:** navigation branding (independent image/text visibility toggles, name, subtitle and font), form type/requirements, recipients, SMS, SEO and privacy. At least one of phone/email must stay enabled and required in each form. Saving publishes immediately.

**Poptávky:** open details/private attachments, mark handled or archived, extend retention with a reason, inspect notification states and retry only after checking for an existing delivery. Deletion removes the enquiry and its attachments permanently from the active installation. Provider backups and previously delivered emails are separate copies.

Stock photo references: `resources/PHOTO_SOURCES.md`. ProcessWire licence: `LICENSE-ProcessWire.txt`; bundled dependencies retain their own licences.
