# Pepperwhisker website (pepperwhisker.com)

An independent guide site about schnauzers: the miniature, standard and giant schnauzer. It's written in plain PHP, needs no database and runs on any PHP 7.4+ host (8.2 or 8.3 recommended). It's a standalone site and shares no code or data with anything else in this repository.

Pepperwhisker doesn't breed or sell dogs and has no puppy listings. It earns from display ads (Google AdSense) and, optionally, affiliate links.

## What's in it

**For readers**
- **Home page** with a to-scale size drawing of the three schnauzers, the three breeds at a glance, the latest guides in each topic and common questions.
- **36 guides** built from the keyword research, in six topics. Each topic has its own page:
  - Schnauzer breeds (`/schnauzer-breeds`)
  - Colors and coats (`/schnauzer-colors-and-coats`)
  - Grooming and haircuts (`/schnauzer-grooming`)
  - Health, lifespan and food (`/schnauzer-health`)
  - Schnauzer mixes (`/schnauzer-mixes`)
  - Puppies, price and rescue (`/schnauzer-puppies`)

  There's also one Spanish guide for the Spanish searches. See [docs/keyword-map.md](docs/keyword-map.md) for which page targets which searches.
- **Every guide has:**
  - a quick-answer box under the heading
  - a table of contents
  - comparison tables
  - a Questions section, marked up as FAQs for Google
  - related guides
  - a "general information, not veterinary advice" note on health guides
- **Schnauzer pictures** (`/schnauzer-pictures`): your own photos, grouped by breed, for Google Images searches like "miniature schnauzer pictures". The same photos appear on the matching breed and color guides. The page appears in the menu and sitemap once you've uploaded photos.
- **Pages:** About, Editorial policy, Advertising & affiliate disclosure, Privacy policy, Terms of use, Contact.
- **Contact form:** messages are saved under Admin → Messages and emailed to you. Spam protection uses a hidden field, a timing check and a limit per connection.

**Admin (`/admin.php`)**
- **Guides & blog:**
  - edit any guide or write new ones and blog posts
  - add a main photo and photos inside the text
  - set the quick answer, address, Google title and description
  - save as a draft
- **Photos:** upload your own schnauzer photos (several at once), tag each with breed and color, and write the description Google Images reads. Photos are resized, turned upright and cleaned of location data.
- **Pages:** edit the About and policy pages or add your own.
- **Messages** from the contact form.
- **Redirects:** when you rename or delete a guide, the old address leads to the new one automatically.
- **Settings:**
  - site name, email, home page text and photo
  - the writer's name, bio and photo, shown on every guide
  - Google Search Console, Bing, Google Analytics
  - Google AdSense (turns on Auto ads and `ads.txt`), the affiliate note
  - SMTP email with a test button
- **Dashboard** with a launch checklist.

## SEO built in

- **Addresses and titles:** clean, keyword-led addresses (`/guides/giant-schnauzer`), one `<h1>` per page, and a unique title and description on every page (all editable).
- **Canonical tags and social previews:** a canonical tag on every page, Open Graph and Twitter tags, and a link-preview image.
- **Structured data (JSON-LD):**
  - Organization and WebSite
  - BreadcrumbList
  - Article, with the writer as author
  - CollectionPage with an ItemList on topic pages
  - ImageGallery with ImageObject (credit and copyright) on the pictures page
  - FAQPage from each guide's Questions section and the home page questions
- **Sitemap and robots:**
  - `sitemap.xml`, built automatically, including new guides and every gallery photo
  - `robots.txt`
  - `ads.txt` once AdSense is set
- **Internal links:** every guide links to related guides, its topic page and the breed guides.
- **Speed:** no frameworks, no web fonts, one small CSS file, the drawings are inline SVG, and photos are resized on upload with smaller copies for phones.

## Put it online

1. Build the site (on any computer with PHP):

   ```bash
   php tools/build-installer.php
   ```

   This creates `dist/index.php` (the whole website in one file) and `dist/pepperwhisker-upload.zip` (the same files as a zip).
2. In your host's File Manager, open `public_html` and **Upload** `index.php` (with FTP, use **binary** mode). Then open `https://pepperwhisker.com/index.php`. It checks your hosting, unpacks the site, moves the host's placeholder page aside and becomes the site's own `index.php`.
   **Or:** upload the zip, right-click it, choose **Extract**, then delete the zip and any placeholder `index.html`.
3. Open `https://pepperwhisker.com/admin.php` **straight away** and create your admin sign-in. The first person to open that page sets it.
4. Work through the **launch checklist** on the dashboard:
   - your email (SMTP) with a test email
   - the writer's name, bio and photo
   - your own schnauzer photos on the main guides
   - Google Search Console: submit `https://pepperwhisker.com/sitemap.xml`
5. When the site has some traffic and your own photos, apply for **Google AdSense**. Once approved, paste your publisher ID (`ca-pub-…`) in Settings → Google & ads.

Your data (edited guides, messages, sign-in, settings) lives in `data/`, and photos in `assets/uploads/`. Neither is in the installer or the zip. **To update the site later, upload a newer `index.php` the same way**: your data and photos are kept. `data/backups/` keeps the last 30 versions of your content. Forgot the admin password? Delete `data/auth.php` and open `admin.php` to create a new sign-in.

## Files

| Path | What it is |
|---|---|
| `index.php` | The website: addresses, contact form, sitemap, robots.txt, ads.txt. |
| `inc/views.php` | Page templates, the logo and the size drawing. |
| `inc/core.php` | Data storage, email (SMTP), photos, the text formatter and the guide topics. |
| `inc/defaults.php` | Starting settings and pages. |
| `inc/articles/` | The starting guides. |
| `admin.php` | The admin. |
| `assets/` | Styles, logo, icons and the link-preview image; `uploads/` holds your photos. |
| `data/` | Your content, messages and sign-in (blocked from the web). |
| `docs/keyword-map.md` | Which page targets which keywords. Not uploaded. |
| `tools/build-installer.php` | Builds the one-file installer and the zip in `dist/`. |

## Run it on your computer

```bash
php -S localhost:8000
```

Run it in this folder, then open http://localhost:8000/index.php (clean addresses need Apache or LiteSpeed, which is what most hosts use).
