# Beagle Brook website (beaglebrook.com)

The website for Beagle Brook, a beagle breeder in Cincinnati, Ohio. It's written in plain PHP, needs no database and runs on any PHP 7.4+ host (8.2 or 8.3 recommended). It's a separate site and shares no code or data with anything else in this repository.

## What's in it

**For buyers**
- **Home page** with available puppies, guarantees, how it works, what's included, the parents, reviews and the delivery promise.
- **Beagle Puppies for Sale** (`/beagle-puppies-for-sale`): the category page. Puppies can be filtered by sex and color, and sold puppies are shown as "gone to loving homes".
- **A page for every puppy** (`/beagle-puppies-for-sale/daisy-lemon-and-white-female`). It shows the photo gallery, video, price and the 50% deposit amount, age, ready date, expected size, registration, microchip, vet check, shots, both parents, what's included and the guarantees. At the bottom is the **inquiry form**: full name, email, phone (required), delivery address (required), preferred payment method, delivery timing, best way to reach them, personal notes, and a checkbox confirming they understand the deposit. No payment is taken on the site.
- **Our beagles** (the parents, with registration and health testing), **Upcoming litters and waitlist**, **Reviews** (empty until you add some), **Contact**.
- **Trust pages:**
  - How it works
  - Health guarantee
  - What's included
  - Puppy delivery
  - Deposit & refund policy
  - Is Beagle Brook legit? (how to verify you)
  - About
  - FAQ
  - Beagle puppies for sale in Ohio
  - Terms of sale
  - Privacy policy
- **18 guides, 4 blog posts and a Chinese-language guide**, covering the keyword research. See [docs/keyword-map.md](docs/keyword-map.md) for which page targets which searches.
- On phones, a bar stays at the bottom of the screen with **Text us**, **Call** and **Puppies** buttons.

**When someone sends an inquiry**
- It's saved under **Admin → Inquiries** and emailed to support@beaglebrook.com. Hit Reply to answer the customer directly.
- The customer gets a confirmation email: their reference, what happens next, and a reminder that payment details only ever come from your address.
- You get a **text message** too, if you turn on text alerts (Settings → Email & alerts).

**From an inquiry to delivery day, in one click each** (Admin → Inquiries → open an inquiry → Next steps)
- **Video call invite.** Thanks the buyer and asks for a time for a live video call.
- **Sales agreement.** Emails a link to their agreement: both parties, the puppy (with registration, parents and the private microchip number), price, delivery, deposit and balance, refunds, delivery terms and the full guarantees. The buyer accepts it online by typing their name and ticking a box. You're emailed and texted, and the date, name and IP address are recorded. It prints cleanly or saves as a PDF, with signature lines.
- **Payment instructions.** Their exact deposit and your private details for the method they chose (add these under Admin → Payments; they never appear on the website).
- **Record a payment.** The amount, method and date. It emails a numbered receipt, marks the puppy Reserved and moves the inquiry to "Deposit received". The buyer's receipt page lists every payment and what's still owed.
- **Reservation confirmed**, **Delivery scheduled** (pick the departure date) and a **friendly follow-up**.
- Every email can be previewed and edited before sending, and the wording lives in **Admin → Email templates**. Each inquiry keeps a history of the emails sent.

**Admin (`/admin.php`)**
- **Puppies:** add a puppy with all its details (name, sex, color, DOB, ready date, price, size, registry and number, parents, microchip, vet check date, shots, description, video and up to 12 photos). Change its status to Available, Reserved or Sold with one click. Tick "Email this puppy to the waitlist" to announce a new puppy.
- **Parents, Litters, Reviews** (add reviews your customers send you).
- **Waitlist:** email everyone, with an unsubscribe link added to each email automatically, and download a CSV.
- **Guides & blog and Pages:** edit any text or write new posts.
  Upload photos for a guide and click **Insert** to place them anywhere in the text, with an optional caption.
- **Redirects:** when you rename or delete a guide, or change a puppy's address, the old address leads to the new page automatically, so saved links and rankings aren't lost. You can add your own too.
- **Guarantees:** edit the guarantee cards, steps and "what's included".
- **Delivery:** Standard delivery $55 (2–5 days) and Express delivery $100 (within 24 hours), counted from departure. You can set different prices for particular states.
- **Payments:** PayPal, Zelle, Bank transfer, Chime and Apple Pay to start with. Add, rename, remove or switch any of them off.
- **Settings:**
  - phone number and the deposit percentage (50% minimum)
  - guarantee periods and the earliest go-home age
  - your story and photo
  - home page photo
  - Google Search Console and Analytics
  - SMTP email and text alerts
- **The dashboard** has a **launch checklist** showing what's still missing.

## SEO built in

- **Clean addresses and page titles:** keyword-led page addresses, one `<h1>` per page, and a unique title and meta description on every page (all editable).
- **Canonical tags and social previews:** a canonical tag on every page, plus Open Graph and Twitter tags.
- **Structured data:**
  - JSON-LD Organization data on every page
  - BreadcrumbList on inner pages
  - Article or BlogPosting on guides and blog posts
  - FAQPage for any page with a "## Questions" section
- **Sitemap and robots:**
  - `sitemap.xml` with images, built automatically, including every new puppy.
  - `robots.txt` that blocks the admin and thank-you pages.
  - Sold puppies and filtered lists are set to `noindex` so Google doesn't index thin or duplicate pages.
- **Internal links:** guides link to related guides and to the puppies page. Puppy pages link to the parents, guarantees and buying guides.
- **Local business data and tracking:** Google reads the business as a local business in Cincinnati serving the whole US. With a Google Analytics ID, inquiries and contact messages are tracked as `generate_lead` (with the puppy and price) and waitlist sign-ups as `sign_up`.
- **Speed:**
  - no frameworks, no web fonts and one small CSS file
  - photos resized on upload, with smaller copies for phones (`srcset`)
  - images lazy-loaded below the fold
  - fixed image sizes, so the page doesn't jump while loading

## Put it online

1. Build the upload zip (on any computer with PHP):

   ```bash
   php tools/build-zip.php
   ```

   This creates `dist/beaglebrook-upload.zip`. Optionally add `'a-strong-password' yourname` to pre-set the admin sign-in. Only do that for the **first** install: a zip that includes a sign-in replaces the existing sign-in when you upload it.
2. In your host's File Manager, open `public_html`, **Upload** the zip, then right-click it and choose **Extract**. Delete the zip, and any placeholder `index.html` the host put there.
3. Open `https://beaglebrook.com/admin.php` **straight away** and create your admin sign-in. The first person to open that page sets it.
4. Work through the **launch checklist** on the dashboard:
   - **Settings → Business:** phone number, registry (e.g. AKC), the name on your payment accounts, any license numbers.
   - **Settings → Email & alerts:** your mailbox's SMTP details, then **Send test email** to an address at a different provider (e.g. Gmail). Turn on text alerts.
   - **Settings → About you:** your name, story and photo.
   - **Parents**, then **Puppies**, with your own photos.
   - **Guarantees and Pages:** read every guarantee and policy and change anything you won't do. They're promises to your customers.
5. **Google Search Console:** add the site, paste the HTML-tag code into Settings → Google & social, then submit `https://beaglebrook.com/sitemap.xml`.
6. Create a **Google Business Profile** as a service-area business, and paste its review link into Settings → Google & social.

Your data (puppies, inquiries, waitlist, sign-in, settings) lives in `data/`, and photos in `assets/uploads/`. The zip contains neither, so uploading a newer version of the site never overwrites them. `data/backups/` keeps the last 30 versions of your settings and content.

## Files

| Path | What it is |
|---|---|
| `index.php` | The website: addresses, forms, emails, sitemap. |
| `inc/views.php` | Page templates. |
| `inc/core.php` | Data storage, email (SMTP), text alerts, photos, the text formatter. |
| `inc/deals.php` | Sales agreements, receipts and the one-click emails. |
| `inc/defaults.php` | Starting settings, guarantees, payment methods and trust pages. |
| `inc/articles/` | Starting guides and blog posts. |
| `admin.php` | The admin. |
| `assets/` | Styles, logo, icons, link-preview image; `site/` holds the starter beagle photos (home page until you upload your own, and the puppy care, beagle breed and lemon beagle guides); `uploads/` holds your photos. |
| `data/` | Your content, inquiries, waitlist and sign-in (blocked from the web). |
| `docs/keyword-map.md` | Which page targets which keywords. Not uploaded. |
| `tools/build-zip.php` | Builds the upload zip. |

## Run it on your computer

```bash
php -S localhost:8000
```

Run it in this folder, then open http://localhost:8000/index.php (clean addresses need Apache or LiteSpeed, which is what most hosts use).
