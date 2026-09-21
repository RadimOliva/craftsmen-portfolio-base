# Local MVP verification

Verified 2026-09-20 on Windows with portable PHP 8.4.25, MariaDB 11.4.10 and ProcessWire stable 3.0.259.

- 23 PHP integration assertions passed: client permissions, both form rule sets, contact-method safeguard, validation, transactional storage, notification failure/retry/capture, duplicate jobs, uncertain delivery and expiry deletion including private attachments.
- 36 HTTP assertions passed: public/privacy pages, private-path restrictions, CSRF, invalid uploads, successful and duplicate submissions, client login, forbidden admin routes, authenticated downloads, retention-extension reason, enquiry deletion and multi-image upload/reordering without losing files.
- Clean release installed in an isolated database: generated credentials, Czech CMS, six services, three references, seed images, client permission and zero enquiries verified. Original release directory remained clean.
- Browser checks covered desktop and 390px mobile layout, navigation, contact form ordering, gallery next/previous/Escape, and native Czech client dashboard. Temporary viewport override reset after review.
- Local worker runs every 60 seconds; test email is captured and SMS is mocked. Old disposable HTTP test records removed. A manually submitted sample enquiry remains for demonstration.

No production deployment, real SMTP/SMS delivery, Webglobe scheduler, provider backups or Apache hosting rules were tested. Those require the future hosting account and authorised recipients. HEIC is deferred; privacy wording and visual identity remain demo content. Accessibility checks are an MVP baseline, not a formal conformance audit.

The restricted execution context originally produced a Windows Bad Image error. The portable runtime successfully loaded required extensions and passed the above checks under approved execution, with no Windows security-policy change.
