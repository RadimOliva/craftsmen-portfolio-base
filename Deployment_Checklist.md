# Client installation and handover checklist

Status: local base MVP implemented; this remains the per-client deployment and handover checklist. No hosting purchase, production deployment, or account creation is authorised by this checklist. See README.md for local setup and checks.

Each client receives an independent installation, database, and released version. No shared client data or automatic feature upgrades.

## Prepare the reusable release

- [ ] Record the tested ProcessWire, PHP, database, and module versions.
- [ ] Package application files, installation instructions, and clean demo content.
- [ ] Exclude real enquiries, private attachments, client accounts, credentials, logs, and backups.
- [ ] Document local installation, configuration, scheduled jobs, email testing, and restore steps.
- [ ] Test installation into a fresh database without dependencies on the original installation.
- [ ] Keep local email in a capture/test transport and SMS disabled or mocked by default.

## Prepare a future client installation

- [ ] Confirm hosting requirements and outstanding questions in Hosting_Compatibility_Shortlist.md.
- [ ] Assign domain, hosting ownership, and a separate database and database user.
- [ ] Install the released package and create unique administrator/client accounts and secrets.
- [ ] Configure company details, branding, licensed images, services, references, contact details, and SEO.
- [ ] Keep Hero and Kontakt permanently enabled; configure optional sections.
- [ ] Configure multiple reference images, cover images, ordering, and image alternative text.
- [ ] Configure form mode; all fields initially enabled, all required except enquiry attachments.
- [ ] Verify at least one of phone/email is both enabled and required in every valid form configuration.
- [ ] Use enabled services plus Jiné as enquiry categories, independent of section visibility.
- [ ] Configure private enquiry uploads: JPG/PNG/WebP/PDF, 5 files, 10 MB each, 25 MB total.
- [ ] Configure owner image uploads: JPG/PNG/WebP, 15 MB each, responsive derivatives.
- [ ] Keep HEIC disabled; support is deferred beyond the initial MVP.
- [ ] Set map visibility and address-text visibility independently; enabled Mapy map loads immediately.
- [ ] Replace privacy placeholders and confirm enquiry retention: proposed 12 months from receipt with expiry warning and documented extensions where justified.

## Connect delivery and scheduled work

- [ ] Select and test email transport. Hosting SMTP is the current proposal; a separate transactional-email account is not mandatory.
- [ ] Configure a domain sender, recipient addresses, and relevant SPF/DKIM/DMARC settings; use visitor email as Reply-To when provided.
- [ ] Connect the client's SmsManager account if SMS is enabled; configure recipient, credentials, and credit responsibility.
- [ ] Store enquiries and pending notifications durably before reporting success.
- [ ] Configure the queue worker on a real scheduler; target every minute, subject to hosting limits.
- [ ] Verify temporary-failure retries, permanent-failure reporting, duplicate prevention, and manual retry.
- [ ] Distinguish provider acceptance from confirmed delivery; reconcile ambiguous sends where supported.
- [ ] Schedule daily retention cleanup and expiry warnings; include attachments and applicable backup/email policies.

## Verify before a future launch

- [ ] Check mobile/desktop layouts, navigation, galleries, accessibility, and disabled-section behaviour.
- [ ] Check Czech client editing, immediate publishing, and restricted system access.
- [ ] Check Přehled enquiry statistics, recent enquiries, notification failures, and approximate page views labelled Zobrazení webu.
- [ ] Test both form modes, required-field rules, spam controls, duplicate submissions, and file restrictions.
- [ ] Verify private files cannot be fetched by unauthenticated visitors.
- [ ] Confirm failed notifications never discard enquiries and the visitor does not wait for retries.
- [ ] Verify actual email receipt and optional SMS delivery using authorised test recipients.
- [ ] Enable HTTPS, disable debug output, check canonical/SEO metadata, and remove demo/private data.

## Backups and handover

- [ ] Assign responsibility for security maintenance, hosting renewals, SMS credit, and failure alerts.
- [ ] Confirm automated daily backups cover database, code, public images, and private attachments.
- [ ] Use and document the longest backup retention included with the selected Webglobe service (for example 14 or 30 days); confirm file and database coverage separately.
- [ ] Do not require a separate backup provider or fixed 30-day history for this MVP.
- [ ] Restore into an isolated environment before handover; disable outbound email/SMS during the test.
- [ ] Verify restored login, content, enquiries, images, and attachments.
- [ ] Provide client editing instructions and deployment/restore documentation.
- [ ] Explain that backups and jobs can run automatically after setup; security updates and restore drills need an assigned maintainer.
- [ ] Schedule restore drills at handover and every six months; review security updates monthly and urgent vulnerabilities promptly, separately from feature upgrades.

Unchecked items must be verified for each future client installation, even where the local MVP already implements the corresponding capability.
