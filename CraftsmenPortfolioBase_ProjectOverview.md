# Craftsmen Czech Portfolio Website Base

## 1. Overall site structure

A single long-scrolling homepage.

Homepage navigation scrolls smoothly to sections on the same page rather than opening separate pages.

Main content sections should be modular and individually enabled/disabled from the CMS.

For MVP, section order is fixed.

Main sections:

- Hero
- O nás
- Naše služby
- Poradenství
- Realizace
- Kontakt

Sections such as Poradenství can be disabled completely if a particular client does not need them.

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
- upload image(s)
- edit title
- edit location
- edit short description

### Future variants

Architecture should not unnecessarily prevent adding alternative Realizace layouts later, such as:

- carousel gallery
- richer project cards
- popup containing structured project information and mini-gallery
- dedicated project pages

Only the simple grid/lightbox version is required for MVP.

---

## 8. “Kontakt” section

Two-column desktop layout.

### Left

Contact information:

- phone
- email
- company address
- billing/company information

Below contact details:

Interactive map / Mapy.cz or suitable alternative, with clickable link to open the location externally.

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

Typical fields:

- Jméno
- Telefon
- E-mail
- Předmět
- Zpráva

### B. Quote / enquiry form

Typical fields:

- Jméno
- Telefon
- E-mail
- Místo realizace / adresa
- Typ práce / kategorie
- Předmět
- Popis zakázky
- Upload photographs/files

The MVP does **not** contain an interactive address-map picker.

Relevant predefined fields can be enabled/disabled and configured as required/optional.

Do not build a generic visual form-builder in MVP.

---

## 10. Form submission behaviour

Every successful form submission must first be stored in ProcessWire.

Notification systems operate after the submission has been stored, so failure of an email or SMS notification must not cause the enquiry itself to be lost.

Submission flow:

**Customer submits form → validation → save enquiry → send notifications → show confirmation**

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

Example statuses:

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

---

## 13. Optional SMS notifications

SMS notifications are optional and can be enabled/disabled.

When enabled, a new enquiry triggers a short SMS through an external SMS gateway/API.

Example:

**Nová poptávka z webu: Jan Novák, pokrývačské práce. Detail najdete ve správě webu.**

Avoid putting unnecessary personal information or the entire enquiry inside the SMS.

SMS provider credentials/settings are configurable by the administrator.

Ideally the SMS-gateway account belongs to the client and the client pays the provider directly.

---

## 14. Client CMS

The client should receive a deliberately simplified Czech-language editing interface.

Normal content editing should not require interacting with development settings.

The CMS should work comfortably on desktop and mobile.

Suggested top-level client navigation:

- Přehled
- Obsah webu
- Služby
- Reference
- Poptávky
- Kontakt
- Nastavení

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

### Section controls

Relevant homepage sections have a simple:

**Zobrazit tuto sekci: ✓ / ✗**

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

Establish a reasonable retention/deletion policy rather than keeping every enquiry indefinitely.

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
- load map/embed functionality efficiently rather than allowing it to dominate initial page load

The site should remain fast on ordinary mobile connections, not merely high-speed desktop broadband.

---

## 21. Security / robustness baseline

- HTTPS only
- ProcessWire and modules kept current
- minimal number of third-party modules
- secure client/admin passwords
- appropriate ProcessWire roles/permissions
- client cannot access developer/system settings
- server-side form validation
- file-upload restrictions by type and size
- spam/rate-limit protection
- regular automated backups
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
