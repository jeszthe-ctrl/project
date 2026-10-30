# Gold Bullion Shop — goldbullion-shop.com

A complete gold bullion store in plain PHP: storefront, live spot pricing, a clean black-and-white design, 15 live price pages, 48 in-depth guides (about 43,000 words of content), 15 trust and policy pages, cart, checkout and an admin panel. No database is needed. It runs on any PHP 7.4+ host with Apache or LiteSpeed (cPanel, Hostinger, Namecheap, SiteGround and similar).

**You never edit code.** Products, prices, pages, guides, shipping, payment methods, emails and settings are all edited in `admin.php` and saved to `data/`. Uploading a newer version of the site never overwrites your data.

## Install (5 minutes)

1. Build the upload files: `php tools/build.php` (or `php tools/build.php "YourPassword123" yourname` to include your admin login). This creates `dist/goldbullion-upload.zip` and `dist/index.php`.
2. In your host's **File Manager**, open `public_html`, upload `goldbullion-upload.zip`, right-click it and choose **Extract**. Delete the zip, and any placeholder `index.html` your host put there.
   *Or* upload only `dist/index.php` and open `https://goldbullion-shop.com/index.php`. It unpacks the whole site itself.
3. Open `https://goldbullion-shop.com/admin` and create your admin username and password (unless you built them in).
4. In the admin, the **Dashboard** to-do list walks you through the rest:
   - **Settings**: your registered business name, phone and address (important for trust and for Google), and your mailbox's SMTP details. Then **Send a test email**.
   - **Payments**: your $Cashtag, Zelle email/phone, Apple Cash number, $ChimeSign and crypto wallet addresses. These are only ever sent to customers, never shown on the site.
   - **Import CSV**: upload your product CSV.
   - **Spot price**: check that the live gold price shows. If your host blocks outgoing connections, enter a manual price.

## How orders work

1. The customer checks out. Name, email, **mobile phone (required)**, a US street address, Standard ($28) or Express ($50) shipping and a payment method (Cash App, Apple Pay, Zelle, Chime or Crypto) are all collected. No payment is taken on the site.
2. You get an email at **sales@goldbullion.online** with the full order (reply-to is the customer). The customer gets a confirmation email.
3. In **Admin → Orders**, open the order:
   - **Send payment details**: one click emails the customer the amount and *your* details for the method they chose (pre-written, editable). There's also a ready-made text message to copy, or to open in Messages on your phone.
   - **Status & tracking**: New → Payment details sent → Paid → Shipped → Completed.
   - **Send shipping email**: tracking number email.
4. Customers can check their order at `/track-order`.

## Products

- **Import CSV** accepts WooCommerce, Shopify and spreadsheet exports. Column names are recognised automatically (name, price, sku, category, weight, images, description, brand, stock…). Download the sample CSV from the Import page.
- **Fixed price** or **live price**: leave the price empty and fill `premium_percent` / `premium_usd`, and the price follows the live gold spot price (spot × gold weight + premium). The price is locked into the order when the customer checks out.
- Products are filed into the right category automatically (1 gram, 10 gram, 1 oz, 100 g, 10 oz, 1 kilo bars, gold coins, silver) from their name and weight, unless your CSV has a category column.
- Photos from the CSV are downloaded to your server (`assets/products/`).

## Live spot price

Gold and silver prices come from public price feeds (goldprice.org, with gold-api.com as a fallback). They're fetched at most every 10 minutes and cached. They feed the price ticker, every price page, all `{value:…}` figures in the guides, and live-priced products. If no feed can be reached, the last good price is used, then the manual price from **Admin → Spot price**.

## Editing content

Pages, guides, categories and price pages all have a rich-text editor, Google title and description fields with character counters, and a FAQ box. FAQs are given to Google as structured data. In any text you can use live values:

- `{gold_oz}` `{gold_g}` `{silver_oz}` `{date}` — live prices and today's date
- `{value:1oz}` `{value:10g:14k}` `{value:1kg}` `{value:1tola:22k}` — the live value of any weight (g, oz, kg, lb, dwt, tola, t), with an optional karat
- `{calculator}` on its own line — the gold value calculator (`{calculator:14}` opens it on 14K)
- `{gs_ratio}` — the live gold/silver ratio
- `{email}` `{phone}` `{brand}` `{standard_price}` `{express_price}` `{pay_window}` — from Settings

Built-in pages and guides have a **Restore original text** button.

## SEO built in

- A unique title, meta description, H1 and canonical URL on every page, with keyword-first titles.
- Structured data: OnlineStore organization, WebSite, BreadcrumbList, Product + Offer (with shipping details), ItemList, Article, FAQPage, AboutPage and ContactPage.
- `/sitemap.xml` and `/robots.txt` are generated automatically with your domain (hreflang links for the Arabic page).
- Live prices are rendered in the HTML (not loaded by JavaScript), so Google sees the numbers.
- Fast pages: one CSS file, no frameworks, lazy-loaded images, mobile-first layout.
- Category pages with no products yet are set to `noindex` automatically until products are added.
- Cart, checkout and search are `noindex`. HTTPS and non-www redirects are in `.htaccess`.

See **SEO-STRATEGY.md** for the keyword map, the competitor analysis and the off-site plan.

## Files

| Path | What it is |
|---|---|
| `index.php` | The website |
| `admin.php` | The admin panel |
| `inc/` | Shared code and the starting content (`content-*.php`). Blocked from the web. |
| `data/` | Your settings, products, orders and messages, plus the last 30 backups. Blocked from the web. Download it now and then as a backup. |
| `assets/` | CSS, JS, logo, icons and uploaded product photos |
| `tools/build.php` | Builds the upload zip and one-file installer |

**Nginx hosts** need the equivalent of the `.htaccess` rules: `location / { try_files $uri $uri/ /index.php?$query_string; }` and deny `/data`, `/inc` and `/tools`.
