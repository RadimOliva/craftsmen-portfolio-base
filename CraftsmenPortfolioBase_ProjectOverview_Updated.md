# Craftsmen Czech Portfolio Website Base

Updated specification: 2026-09-20. Consolidates the original overview and the decisions agreed during project clarification. These are requirements, not a claim that the application has been implemented.

Current delivery scope: a **local, reusable base MVP**. Do not purchase hosting, create production accounts, or deploy to Webglobe as part of this phase.

This document is the current specification. The original overview is retained unchanged for reference. Where earlier planning documents differ, the decisions here take precedence, particularly for backup retention, HEIC deferral, and the selected email transport.

## 1. Overall site structure

A single long-scrolling homepage.

Homepage navigation scrolls smoothly to sections on the same page rather than opening separate pages.

Main content sections should be modular. O nás, Naše služby, Poradenství, and Realizace can be individually enabled/disabled from the CMS. **Hero and Kontakt are permanently enabled** and have no client-facing disable control.

For MVP, section order is fixed.

Main sections:

- Hero
- O nás
- Naše služby
- Poradenství
- Realizace
- Kontakt

Sections such as Poradenství can be disabled completely if a particular client does not need them.

Include one small supporting privacy-information page using the same visual identity, with links from the form and footer. This does not change the single-page navigation model of the main website.

---

## 2. Main header / sticky navigation

Use one responsive header component with two visual states.

### At top of page

Simple horizontal header.

Centered container with fixed maximum width.

Left:

- Simple wordmark/logo
- Switchable between text and uploaded image
- Default placeholder: **Řemesla Václav Čech**

Right:

- O nás
- Naše služby
- Poradenství
- Realizace
- Kontakt
- Phone icon + phone number

Phone number is clickable and initiates a call on supported devices.

Navigation items for disabled sections should disappear automatically.

### After scrolling

The same header becomes sticky and slightly more compact with a quick, subtle transition.

Left:

- Logo / wordmark

Right:

- Same active section navigation
- Nezávazná poptávka CTA
- Phone icon

No separate second navigation system.

---

## 3. Hero section

Create a prominent full-width hero near the top of the homepage.

Hero height should respond naturally to viewport size rather than use a rigid fixed height.

Contents:

- Hero image
- Main heading:
  **Řemeslná výroba, renovace a stavitelství.**
- Supporting tagline:
  **30 let praxe v oboru pro vaše potřeby.**
- Primary CTA:
  **Nezávazná poptávka**

CTA scrolls directly to the contact / quote-request form.

Hero content and image editable through CMS.

The section itself cannot be disabled. Enquiry CTAs always target the permanently available contact form.

---

## 4. “O nás” section

### Desktop

Left:

- Image

Right:

Section heading:

**Řemesla Václav Čech**

Text:

**Provádíme řemeslné, stavební a rekonstrukční práce pro domácnosti i firmy. Zakládáme si na poctivém provedení, spolehlivé domluvě a praktických řešeních, která dávají smysl technicky i finančně.**

### Mobile / tablet

Top:

- Section heading
- Smaller image

Bottom:

- Text

Entire section can be enabled/disabled.

Image, heading and text editable through CMS.

---

## 5. “Naše služby” section

Responsive grid of service cards.

Do not use a carousel for MVP.

Each card contains:

- Image
- Heading
- Short description

Example cards:

### Rekonstrukce

Kompletní i částečné rekonstrukce domů, bytů a dalších prostor.

### Stavební práce

Zednické, bourací a další práce od drobných oprav po větší realizace.

### Tesařské práce

Výroba, opravy a rekonstrukce dřevěných konstrukcí.

### Pokrývačské práce

Realizace a opravy střech včetně souvisejících klempířských prací.

### Instalace

Instalatérské a další technické práce při stavbě nebo rekonstrukci.

### Zakázková výroba

Individuální řemeslná řešení podle konkrétního zadání.

### CMS

Client can:

- add service
- edit service
- delete service
- enable/disable service
- change image
- change title
- change description
- reorder services

Entire section can also be enabled/disabled.

---

## 6. “Poradenství” section

This is an optional general-purpose section and can be disabled completely.

### Desktop

Left:

- Image

Right:

Section heading:

**Poradíme s řešením**

Text:

**Nejste si jistí, jaký postup, materiál nebo rozsah prací zvolit? Projdeme s vámi možnosti, doporučíme vhodné řešení a předem vysvětlíme, co bude realizace obnášet. Ozvěte se nám ještě před zahájením projektu.**

### Mobile / tablet

Top:

- Heading
- Smaller image

Bottom:

- Text

Heading, text and image editable through CMS.

---

## 7. “Realizace” section

Eyebrow:

**Realizovali jsme**

Main heading:

**Reference**

### MVP presentation

Responsive grid of project/reference thumbnails.

Each item contains:

- main image
- project title

On click, open a clean lightbox displaying:

- enlarged image
- previous/next navigation through all images belonging to this reference
- title
- optional location
- short description

Example:

**Rekonstrukce střechy rodinného domu**
Praha 10

Výměna krytiny, oprava krovu a nové klempířské prvky.

### CMS

Client can:

- add reference
- edit reference
- delete reference
- enable/disable reference
- reorder references
- upload multiple images from day one
- choose the cover image shown in the grid
- reorder images within a reference
- edit title
- edit location
- edit short description

The entire Realizace section can be enabled/disabled. The lightbox must have keyboard and touch-friendly controls. A project with a single image remains valid.

### Future variants

Architecture should not unnecessarily prevent adding alternative Realizace layouts later, such as:

- carousel gallery
- richer project cards
- richer popup containing structured project information beyond the MVP lightbox
- dedicated project pages

Only the simple grid/lightbox version is required for MVP.

Multiple photos and lightbox image navigation are included in that MVP, not deferred to a future gallery variant.

---

## 8. “Kontakt” section

Always shown; clients cannot disable this section.

Two-column desktop layout.

### Left

Contact information:

- phone
- email
- company address
- billing/company information

Below contact details:

Use a standard Mapy.cz / Mapy.com interactive embed, with a clickable link to open the location externally.

The client can disable the map completely. When enabled, it loads immediately; do not require a click to reveal or load it. A separate setting controls whether the company address text is publicly shown, so hiding the map does not inadvertently leave a private address visible. Billing/company details remain separately editable.

### Right

Contact / quote-request form.

Heading:

**Máte projekt nebo potřebujete opravu?**

Supporting text:

**Popište nám stručně, co potřebujete. Ozveme se a domluvíme další postup.**

### Mobile / tablet order

1. Contact / quote-request form
2. Contact information
3. Map

---

## 9. Contact / quote-request form

The website supports two basic form modes selectable in CMS.

### A. Simple contact form

All of these predefined fields are enabled and required by default:

- Jméno
- Telefon
- E-mail
- Předmět
- Zpráva

### B. Quote / enquiry form

All of these predefined fields are enabled by default. Every field except uploaded photographs/files is required by default:

- Jméno
- Telefon
- E-mail
- Místo realizace / adresa
- Typ práce / kategorie
- Předmět
- Popis zakázky
- Upload photographs/files

The MVP does **not** contain an interactive address-map picker.

The client can enable/disable predefined fields and change required/optional settings for either mode.

Enforce a configuration safeguard: **at least one of Telefon or E-mail must be both enabled and required**. Reject CMS settings that would leave both optional or disabled; enforce the resulting field rules on the server as well.

Typ práce / kategorie uses enabled service records plus **Jiné**. Hiding the Naše služby section does not remove its enabled records from the form choices. No separate category editor is required for MVP.

Do not build a generic visual form-builder in MVP.

### Upload rules

Visitor attachments belong to private enquiries and are distinct from public images uploaded by the website owner.

| Rule | Visitor enquiry attachments | Owner's website images |
|---|---|---|
| Accepted formats | JPG, PNG, WebP, PDF | JPG, PNG, WebP |
| Maximum size per file | 10 MB | 15 MB per source image |
| Count/combined limit | Up to 5 files, 25 MB total per enquiry | Multiple images per reference |
| Access | Authenticated, authorised CMS users only | Public when used in published content |
| Processing | Validate actual file contents and prevent execution | Validate, resize, and optimise for responsive display |

Private attachments must not be retrievable through unauthenticated public download URLs. Notification emails link to the enquiry rather than attaching its files.

**HEIC support is deferred and is not an MVP acceptance requirement.** Do not advertise or accept HEIC initially. Future support requires verified decoding/conversion on the actual environment, including tests with real iPhone images for orientation, colour, resource limits, and output. ImageMagick/Imagick availability alone does not prove a working HEIC decoder is present.

---

## 10. Form submission behaviour

Every successful form submission must first be stored in ProcessWire.

Notification systems operate after the submission has been stored, so failure of an email or SMS notification must not cause the enquiry itself to be lost.

Submission flow:

**Customer submits form → validation → durably save enquiry and pending notification jobs → show confirmation.**

Email/SMS delivery happens independently after persistence. The visitor does not wait for provider responses or notification retries.

### Successful submission

Display a clear Czech confirmation message such as:

**Děkujeme. Vaši poptávku jsme přijali a ozveme se vám co nejdříve.**

Prevent accidental duplicate submissions.

### Failed submission

Display a clear error without deleting valid information the user has already entered.

### Spam protection

Use lightweight spam protection such as:

- honeypot
- rate limiting
- server-side validation

Avoid unnecessarily intrusive CAPTCHA unless it becomes necessary.

### Notification reliability and retries

- Track email and SMS independently so one failing channel does not block the other.
- Attempt delivery promptly through a scheduled worker. Target a worker run every minute, subject to the eventual hosting scheduler's supported interval.
- For temporary failures, retry after approximately 1, 5, 15, and 60 minutes following successive failed attempts. Actual timing depends on worker frequency; these are approximate delays, not an exact delivery guarantee.
- Stop automatic retries for permanent/configuration errors such as invalid credentials; require corrective action.
- After retries are exhausted, retain the failure state and expose a manual retry action.
- Prevent duplicate jobs/sends. For ambiguous timeouts, check provider status or use provider duplicate-prevention support where available before resending, particularly for paid SMS.
- Provider acceptance is not proof of delivery to an inbox or handset. Do not display it as confirmed delivery.
- Keep notification jobs independent of visitor traffic; do not rely solely on page visits to trigger retries.

---

## 11. Client enquiry dashboard

The client should have a very simple **Poptávky** section inside the website CMS.

No full CRM is required.

### Enquiry list

Show basic information such as:

- submission date/time
- customer name
- phone
- enquiry type
- status

Statuses:

- Nová
- Vyřízená
- Archivovaná

### Enquiry detail

Opening an enquiry shows:

- all submitted fields
- customer contact information
- message / project description
- uploaded files/photos
- submission date
- status

Client can:

- view enquiry
- change status
- archive enquiry
- delete enquiry

Also show separate email and SMS notification states in enquiry details:

- **Čeká na odeslání**
- **Předáno poskytovateli**
- **Chyba odeslání**

Failures need an understandable Czech explanation and a manual retry control. Surface failures requiring attention in Přehled as well. Keep technical credentials and sensitive debug information out of client-facing error messages.

No messaging/chat system is required for MVP.

---

## 12. Email notifications

New enquiries should generate an email notification to one or more configurable recipient addresses.

Example subject:

**Nová poptávka z webu – Jan Novák**

Email should contain:

- basic enquiry information
- customer contact details
- direct link to the enquiry in the CMS

Email recipient address must be editable by the client/admin.

### Selected email transport

Use **authenticated SMTP through a Webglobe hosting mailbox** for future production installations. No Brevo account or separate transactional-email provider is required.

ProcessWire's built-in WireMail abstraction should be used with a suitable SMTP transport. Its default PHP-mail implementation is a different transport; do not assume it provides authenticated SMTP without configuration. Keep transport configuration replaceable.

An administrator configures the mailbox credentials and domain sender. Configure the relevant SPF/DKIM/DMARC records during deployment. Use the visitor's email as Reply-To when supplied, rather than impersonating the visitor in the From address.

Do not include enquiry attachments in notification emails. Provide the authenticated CMS link instead.

For the local MVP, capture outgoing mail locally through a test transport so notifications can be inspected without a hosting account or sending real messages.

---

## 13. Optional SMS notifications

SMS notifications are optional and can be enabled/disabled.

When enabled, a new enquiry triggers a short SMS through an external SMS gateway/API.

Use **SmsManager.cz**. The working integration belongs to the initial product delivery even though SMS is optional for each client installation. A normal web installation does not itself connect to mobile networks.

Example:

**Nová poptávka z webu: Jan Novák, pokrývačské práce. Detail najdete ve správě webu.**

Avoid putting unnecessary personal information or the entire enquiry inside the SMS.

The client can enable/disable SMS and change notification recipients. Provider credentials and technical connection settings are administrator-only.

The SMS account belongs to the client, who pays the provider directly and is responsible for available credit. Local development uses a disabled or mocked transport by default and does not send paid SMS messages. Real delivery verification takes place with configured credentials and authorised test recipients before a future launch.

---

## 14. Client CMS

The client should receive a deliberately simplified Czech-language editing interface.

Use **ProcessWire's existing admin interface**, adapted with Czech labels, simplified navigation, and appropriate roles. Do not build a separate custom CMS application.

Normal content editing should not require interacting with development settings.

The CMS should work comfortably on desktop and mobile.

Top-level client navigation:

- Přehled
- Obsah webu
- Služby
- Reference
- Poptávky
- Kontakt
- Nastavení

### Přehled

A simple overview showing:

- New enquiries awaiting attention.
- Enquiries received this month and last month.
- The five most recent enquiries.
- Notification failures requiring attention.
- Approximate daily website page views labelled **Zobrazení webu**.

Enquiry statistics come directly from the enquiry database. The traffic counter should exclude known bots and logged-in administrators where practical and avoid storing visitor identities. Count page views, not claimed unique visitors or sessions. Repeated views may count multiple times; the number is approximate. No dedicated visitor analytics service or tracking suite is required for MVP.

### Client-editable content

Client can edit:

- company name
- logo
- phone
- email
- company address
- billing/company details
- hero heading
- hero tagline
- hero image
- O nás heading/text/image
- services
- Poradenství heading/text/image
- references
- contact information
- form notification email
- basic SEO title
- meta description

Client can also edit privacy-page content and the agreed operational content settings: form mode, field visibility/required status, optional-section visibility, notification recipients, SMS on/off, and independent map/address visibility.

**Saved content changes go live immediately.** No separate draft, preview-approval, or publishing workflow is required.

### Section controls

Relevant homepage sections have a simple:

**Zobrazit tuto sekci: ✓ / ✗**

This control is only available for O nás, Naše služby, Poradenství, and Realizace. Hero and Kontakt are permanent.

Disabled sections:

- disappear from the website
- disappear from homepage navigation automatically

### Restricted client access

Normal client account should not expose technical/development settings such as:

- ProcessWire modules
- template definitions
- fields
- PHP/templates
- system configuration
- server configuration
- email/SMS provider credentials and technical connection settings

Developer/admin account retains full access.

---

## 15. Footer

Compact footer.

Contents:

- company/site owner
- copyright
- optional company identification information
- privacy-policy link

Avoid oversized multi-column corporate footer.

---

## 16. General design direction

General qualities:

- clean
- sturdy
- practical
- trustworthy
- modern but not trendy
- good contrast
- uncomplicated navigation
- relatively compact layout
- fast and subtle interactions

Avoid:

- excessive animation
- glassmorphism
- tech/SaaS aesthetics
- excessive rounded cards
- neon colours
- complex interactive effects
- unnecessary decorative UI
- excessive empty space

### Typography

Highly readable, functional sans-serif typeface.

Prioritise readability over novelty.

### Initial visual assets and demo content

The supplied Czech text throughout this overview is the default demo content. Choose a restrained palette and suitable high-resolution, licensed placeholder photos related to craftsmanship, construction, woodwork, and renovation. These images are intended to be replaced for individual clients. Preserve any required licensing/attribution information.

Routine choices such as exact spacing, breakpoints, transitions, and mobile-menu styling can be made during implementation within this direction.

---

## 17. Responsive behaviour

Explicit desktop, tablet and mobile layouts.

On smaller screens:

- stack multi-column sections vertically
- reduce hero heading size appropriately
- collapse navigation into mobile navigation
- maintain comfortable touch targets
- make form controls full-width where appropriate
- maintain readable font sizes
- maintain sensible horizontal page padding
- prevent text lines becoming excessively wide
- ensure sticky navigation does not obscure anchored sections

No horizontal page scrolling.

---

## 18. Accessibility baseline

Target sensible WCAG 2.2 AA fundamentals rather than trying to implement every theoretical accessibility enhancement.

Include:

- semantic HTML
- logical heading hierarchy
- keyboard-operable navigation
- clearly visible keyboard focus
- adequate colour contrast
- descriptive form labels
- understandable validation/error messages
- alternative text for meaningful images
- decorative images ignored by assistive technology where appropriate
- sufficiently large/tolerant touch targets
- no functionality dependent exclusively on hover
- respect reduced-motion preferences for non-essential animation
- lightbox/modal usable with keyboard and closable using Escape
- correct page language metadata

---

## 19. Privacy / personal data

Forms should collect only information reasonably required to process the enquiry.

Provide a clearly accessible privacy-information page/link near the form.

Create a small supporting page using the site's shared visual design and link it from the footer too. Use clearly marked placeholder privacy text for the local demo; replace it with client-specific approved information before a public launch. The client can edit the page through the CMS.

The privacy information should explain at minimum:

- who operates the website
- what information is collected
- why it is collected
- where it is sent/stored
- how long it is retained
- relevant contact information

Do not automatically use enquiry data for unrelated marketing.

If marketing functionality is later added, keep it separate from the ordinary enquiry process.

Enquiries must be deletable from the CMS.

### Enquiry retention

- Default to deleting ordinary enquiries and their attachments **12 months after receipt**.
- Warn the client before expiry.
- Allow a documented extension where an active project or another justified need requires it.
- Run retention cleanup automatically through a daily scheduled task.
- Archiving is a status change, not an exemption from deletion.
- Records needed for contracts, warranties, accounting, or other justified purposes require the applicable separate retention policy.
- Twelve months is an agreed product default, not a universal statutory deadline. Confirm the policy against each client's actual processing needs before launch.
- Do not provide indefinite retention of all identifiable enquiries as the default.

Deletion from the CMS must cover the associated private attachments. Email copies and backups have their own lifecycles; CMS deletion does not instantly remove those copies. Document this in the deployment/privacy arrangements and respect deletion decisions when restoring backups.

Third-party embeds, analytics and external services should be reviewed for their cookie/privacy behaviour before deployment.

Prefer avoiding unnecessary tracking and third-party scripts.

---

## 20. Performance baseline

Website should remain lightweight.

Requirements:

- optimized/resized responsive images
- lazy-load below-the-fold images where appropriate
- avoid unnecessarily large JavaScript libraries
- avoid excessive plugins/modules
- minify/bundle production assets where appropriate
- use browser caching
- enable server compression
- limit externally hosted fonts/scripts
- no autoplay video or heavyweight decorative media by default
- keep the map integration lightweight, while honouring the agreed immediate loading of an enabled map (no click-to-load gate)

The site should remain fast on ordinary mobile connections, not merely high-speed desktop broadband.

---

## 21. Security / robustness baseline

- HTTPS only
- ProcessWire and modules maintained with security/compatibility updates according to the maintenance policy below
- minimal number of third-party modules
- secure client/admin passwords
- appropriate ProcessWire roles/permissions
- client cannot access developer/system settings
- server-side form validation
- file-upload restrictions by type and size
- spam/rate-limit protection
- regular automated backups on the future hosting installation, using the selected provider's included retention
- ability to restore from backup
- errors should not expose server/debug information publicly
- production debugging disabled
- notifications failing must never destroy submitted enquiries

---

## 22. SEO basics

Homepage:

- editable SEO title
- editable meta description
- appropriate Open Graph title
- Open Graph description
- Open Graph image
- canonical URL
- sensible semantic heading structure

Generate/include as appropriate:

- robots.txt
- sitemap
- favicon/site icons

Business name, contact details and primary service/location information should be represented consistently in page content.

---

## 23. MVP philosophy

The MVP should prove the reusable tradesman-site system rather than implement every possible website feature.

Priorities:

1. Good-looking public website
2. Excellent mobile experience
3. Extremely simple Czech client CMS
4. Reliable enquiry/quote intake
5. Enquiries visible inside CMS
6. Reliable email notifications
7. Optional SMS notifications
8. Easy enable/disable of unnecessary sections
9. Easy redeployment for another client
10. Low maintenance and minimal technical dependency

More advanced gallery layouts, complicated forms, CRM functionality, scheduling, customer accounts and similar functionality can be added later without being part of the first working version.

Here, deferred scheduling means customer appointment/booking functionality. The background scheduler required for notification retries and retention cleanup is part of the MVP.

---

## 24. Reusable installation and release model

Each client purchases an independent installation with a separate database and a particular released version. No client data, runtime, or central content system is shared between installations.

A later client may receive a newer release; earlier clients do not automatically receive those feature changes. Security and compatibility maintenance is a separate responsibility from feature upgrades.

Make copying, reinstalling, and redeploying the base as simple as possible:

- Provide a clean release package and installation instructions.
- Include only demo content, never another client's enquiries, attachments, accounts, credentials, logs, or backups.
- Keep client-specific configuration separate from reusable application code.
- Create unique credentials and secrets for each installation.
- Record the tested core, PHP, database, and module versions with each release.
- Verify installation into a fresh database without relying on the original installation.
- Supply a client setup and handover checklist covering content, mail, SMS, scheduled tasks, privacy, backups, and verification.

The existing **Deployment_Checklist.md** is supporting planning material. Its earlier conditional HEIC and independent-backup recommendations are superseded by this overview: HEIC is deferred and no separate backup service is required for the agreed scope.

## 25. Local MVP and future hosting

### Current phase

Build and verify the base locally. No Webglobe installation, hosting purchase, production account creation, or public deployment is part of the current scope.

Use the **stable ProcessWire master release**, not the development branch. Verify and pin the stable version when implementation begins. The earlier research snapshot listed master 3.0.259; this is a dated observation, not a promise that it will remain the latest release or a claim it is installed.

PHP 8.4 is the proposed test target, subject to checking compatibility with the chosen core/modules. Provide a compatible local web server, database, image processing, email capture, and a documented way to run scheduled jobs. Exact local tooling is an implementation choice.

### Future hosting choice

Use **Webglobe**. Its Plus shared-webhosting plan is the starting recommendation; the exact plan/server configuration is confirmed before deployment. A dedicated cloud server is not required by the MVP.

The short technical compatibility list is:

- Supported PHP 8.x and the selected stable ProcessWire release.
- Apache or compatible rewrite/.htaccess behaviour.
- PDO MySQL support and a maintained MySQL/MariaDB version compatible with ProcessWire.
- GD or ImageMagick with the required JPG/PNG/WebP processing; HEIC is excluded for now.
- Appropriate upload, memory, and request-size limits for the agreed attachments.
- HTTPS and protection of private enquiry files.
- Authenticated hosting SMTP with confirmed sender rules and sending limits.
- A real scheduler for notification delivery/retries and daily retention cleanup.
- Confirmed backup coverage, retention, and recovery procedure for files and database.

Outstanding provider-specific facts do not block the local build: minimum cron interval, actual server/runtime configuration, SMTP quotas, and exact backup retention/recovery terms must be checked before production deployment. Adjust approximate notification timing to the scheduler actually available.

See **Hosting_Compatibility_Shortlist.md** for dated research and source links. Its earlier HEIC and independent-backup proposals are superseded by the decisions in this overview.

## 26. Backups, maintenance, and handover

### Backups

Use Webglobe's automated daily backups and the **longest retention included with the selected service**. Accept the actual included offering (for example, 14 or 30 days); do not impose a separate fixed 30-day requirement or require an additional backup provider for this MVP.

Confirm file and database retention separately. Earlier public documentation described different quick-restore windows for databases and files; do not assume a single duration applies to everything. Verify coverage of code, database, public images, and private attachments, along with any size limits, restore fees, and recovery procedure.

Take a recoverable backup before significant maintenance. Daily backups can leave up to roughly one day of changes at risk between backup runs. Backup retention is distinct from the 12-month enquiry retention policy.

### Maintenance schedule

- Automate daily backups and failure alerts after setup.
- Review application maintenance monthly.
- Address urgent security fixes sooner when required.
- Check compatibility and test updates before applying them; do not assume unattended ProcessWire/module updates are enabled or safe by default.
- Verify backup coverage and investigate reported failures.
- Perform a restore rehearsal at handover and approximately every six months afterward.

A restore rehearsal means restoring into an isolated test installation before a real incident, with outgoing email/SMS disabled. Verify CMS login, public content, enquiries, images, and private attachments. This is distinct from recovering the live site after a failure.

### Ownership and service boundaries

Low maintenance does not mean no maintenance. Assign responsibility for application updates, backup/failure checks, hosting renewals, SMS credit, and restore rehearsals at handover. Confirm what the hosting provider manages versus what the application maintainer must manage.

Offer maintenance separately from the one-time website purchase. If a client declines that service, hand over the instructions and assign ongoing maintenance to the client or their chosen developer. Older installations do not automatically gain new product features through this arrangement.

## 27. Deferred items and launch-only inputs

Deferred beyond the initial MVP:

- HEIC uploads and conversion.
- Advanced gallery layouts and dedicated project pages.
- Generic form builders, full CRM/chat, booking, and customer accounts.
- Unique-visitor/session analytics and dedicated tracking services.
- Shared infrastructure or automatic feature distribution across client installations.

Inputs needed for a future client launch, but not for the local demo:

- Actual branding, licensed final photographs, and company details.
- Domain, hosting plan, and final runtime/scheduler verification.
- Mailbox credentials, notification recipients, and domain email authentication.
- Client-owned SmsManager credentials and credit if SMS is enabled.
- Final privacy information and confirmed retention/maintenance responsibilities.
- Actual backup retention and a successful restore rehearsal.
