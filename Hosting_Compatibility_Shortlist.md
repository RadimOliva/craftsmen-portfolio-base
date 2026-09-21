# ProcessWire and Webglobe compatibility shortlist

Checked: 2026-09-20. Scope: local base MVP only. Webglobe is the agreed future provider; Plus is the proposed plan. Public documentation was reviewed; no provider contact, account inspection, purchase, deployment, or live compatibility test took place.

## Pinned local runtime

- The MVP now includes **ProcessWire stable master 3.0.259**, tested locally with **PHP 8.4.25** and **MariaDB 11.4.10**. Keep releases pinned per client; review current security releases at deployment. [Official downloads](https://processwire.com/download/core/).
- Official installation requirements: Apache or compatible server, rewrite/.htaccess support, PHP 8.x recommended, PDO database support, MySQL 5.6+ or equivalent MariaDB, and GD 2 or ImageMagick. Use a maintained PHP 8.x/database release rather than treating these minimums as recommended versions. [Installation requirements](https://processwire.com/docs/start/install/new/).
- Proposed PHP target: **8.4**, subject to testing the selected core and modules. Webglobe advertises PHP 8.2–8.5, MySQL/MariaDB, SSL, and SSH/SFTP. Confirm the exact database version, extensions, rewrite behaviour, and upload/resource limits on the assigned server. [Hosting specifications](https://www.webglobe.cz/webhosting).

## Scheduler and backups

| Item | Published finding | Remaining confirmation |
|---|---|---|
| Scheduler | Webglobe supports scheduled calls to scripts on the hosted domain. | Minimum interval, job count, timeout, authentication options, and whether CLI cron is available on Plus. Target a one-minute notification worker and daily cleanup; revise notification timing if the minimum interval is longer. |
| File backups | Main hosting page advertises daily backups with up to 14 days of restoration. Help article says 14 days, sometimes 30. | Exact retention and recovery procedure for the selected Plus service. |
| Database backups | Help article states five days for quick restoration, with older copies potentially recoverable to the file-backup date. Separate database documentation lists five daily copies and a 300 MB limit for that mechanism. | Whether that limit applies to the chosen service, coverage of larger databases, older-copy availability, restore fees and recovery time. |
| Selected backup policy | Use the longest retention included with the chosen service, whether 14 or 30 days. No additional backup provider or fixed 30-day requirement. | Confirm file/database coverage, limits, recovery process and responsible maintainer. |

Sources: [Cron](https://www.webglobe.cz/poradna/cron), [backup and restore overview](https://www.webglobe.cz/poradna/jak-zalohovat-data), [database backups](https://www.webglobe.cz/poradna/zalohovani-databazi). Do not apply VPS backup terms to shared webhosting.

## Email: no separate transactional provider required

ProcessWire's default WireMail uses PHP mail. Webglobe documents PHP mail and authenticated SMTP. Proposed production default: use the hosting mailbox's authenticated SMTP, without a Brevo account; retain a replaceable transport. Confirm mailbox credentials, sender restrictions, sending quotas, and domain authentication during deployment. PHP mail still depends on the host's mail infrastructure; application acceptance is not proof of inbox delivery.

For the local MVP, capture email locally rather than depending on production delivery. SMS uses SmsManager when configured; local testing should not send paid messages by default.

Sources: [ProcessWire WireMail](https://processwire.com/api/ref/functions/wire-mail/), [Webglobe PHP/SMTP sending](https://www.webglobe.cz/poradna/odesilani-emailu-z-webu).

## HEIC: deferred

ImageMagick supports HEIC with libheif; a working HEIC decoder must be included in that build. PHP Imagick availability alone does not establish HEIC decoding. [ImageMagick formats](https://imagemagick.org/formats/), [libheif codecs](https://github.com/ImageMagick/heif).

No public Webglobe confirmation of HEIC decoding was found. The local portable runtime now supports JPG/PNG/WebP with GD. HEIC is intentionally rejected and deferred; no HEIC conversion test is claimed.

Before enabling HEIC, confirm PHP Imagick + ImageMagick + libheif with a HEIC decoder, then test real iPhone samples for decoding, orientation, colour, resource limits, metadata removal, and JPEG/WebP output. Local support can be provisioned later, but does not guarantee hosting support. Keep HEIC acceptance conditional until the actual environment passes these checks.

## Questions for Webglobe before deployment

1. Does Plus on the assigned server support the selected ProcessWire/PHP version, PDO MySQL, GD/WebP, rewrite rules, and .htaccess access restrictions?
2. What is the shortest scheduler interval and execution timeout? Are authenticated HTTPS calls or CLI jobs available?
3. What are the exact file/database backup retention, size limits, restore fees, and recovery times for this service?
4. For a later HEIC feature only: is PHP Imagick enabled with working HEIC decoding through libheif?
5. What are PHP-mail and authenticated-SMTP quotas and sender/domain-authentication requirements?

These are unresolved provider-specific checks, not reasons to purchase hosting before building the local MVP.
