# FUDAKURA storefront

FUDAKURA (札蔵) is an independent distributor and reseller of authentic Japanese Pokémon TCG products, shipping wholesale orders from Japan to Australia (fudakura.com.au). This is its shop, in plain PHP. It needs no database and runs on any PHP 7.4+ host.

## Files

| Path | What it is |
|---|---|
| `index.php` | The shop customers see. |
| `admin.php` | Your backend: products, photos, prices, shipping, payments, settings, FAQ and orders. |
| `inc/` | Shared code and the starting data (`defaults.php`). Blocked from the web. |
| `data/` | Created automatically. Holds your edits (`store.php`), orders (`orders/`), the admin login (`auth.php`) and the last 30 versions of your data (`backups/`). Blocked from the web. |
| `assets/products/` | Product photos uploaded from the admin. Smaller copies for grids are kept in `thumbs/` and rebuilt automatically. |
| `assets/site/` | Site photos (home page hero, 30th Celebration feature, link-preview image). These are not product photos. |
| `assets/qrcode.min.js` | Draws the Bitcoin QR code on the payment page (MIT-licensed qrcode-generator). |

You shouldn't need to edit any code. Day-to-day changes are made in `admin.php` and saved to `data/`, so uploading a newer version of the code never overwrites your products or orders.

## Install

**One file:** upload the single `index.php` built by `php tools/build-installer.php [admin-password]` (it lands in `dist/`) to your web space and open your site. It unpacks everything below next to itself, keeps your host's own `.htaccess` rules, moves a placeholder `index.html` aside, and then becomes the shop's normal `index.php`. Your data in `data/` is never overwritten, so uploading a newer one later updates the shop. If PHP can't create files in the folder, use the zip instead:

1. Upload everything to your web space. Keep the folder structure.
2. Make sure PHP can write to `data/` and `assets/products/` (most hosts allow this by default; otherwise set them to 755 or 775).
3. Open `https://your-domain/admin.php`. If the installer was built with a password, sign in with it and change it under **Password**. Otherwise, **straight away**, create your admin password there: the first person to open that page sets it.
4. In the admin:
   - **Settings**: your registered company name, full business address, emails and website address. Then **Send a test email** (Settings, bottom) to your business address and to a personal one to check order emails arrive. If they don't, or land in spam, enter your mailbox's SMTP details under **Email**.
   - **Shipping**: your real shipping rates, the free-shipping amount and the Shipping & Returns page text. The site ships with starting rates, and the dashboard warns you until you save this page.
   - **Payments**: check the Bitcoin address is yours (it's checked for typos before saving).
   - **Products**: add photos, check prices and add the rest of your range.

## How the shop works

- **Prices** are entered in USD per unit in the admin, with quantity breaks (e.g. 1+, 6+, 18+, 36+), and shoppers see them in **Australian dollars only**, converted with the AUD rate in **Settings → Currencies**. Keep that rate up to date. There's no currency switch while AUD is the only currency.
- **Australia only by default.** The country list in **Settings** holds just Australia, so checkout skips the country choice. Add countries (and shipping zones) there to sell further afield.
- **Minimum order**: the order total including shipping must reach the minimum set in **Settings** ($100 by default). Checkout shows the shipping, the total and how much more is needed as soon as the customer picks a country, and the server checks it again when the order is placed.
- **Shipping** is by weight, with two options at checkout: **Standard** (4–8 working days) and **Express** (2–4 working days). For each zone and option, the price is the per-order amount + the per-kg rate × the order weight (each product's weight × quantity). It is rounded up to a whole dollar unless you untick that in **Shipping**. Set per-kg to 0 for flat-rate shipping. Countries not in any zone use the "Rest of world" rates. The Shipping tab previews what customers in your first country (Australia) pay for typical orders.
- **GST**: prices exclude GST. The Shipping & Returns page, Terms, Wholesale page and FAQ explain that orders over A$1,000 are charged 10% GST (plus any duty and charges) by Australian customs, and that GST-registered buyers can claim it back. Overseas sellers whose sales to Australia reach A$75,000 a year must register with the ATO and charge GST on low-value orders (A$1,000 or less) to buyers who aren't GST-registered businesses: check your position with an accountant and adjust that text if you register.
- **No stock limits.** Customers can order any quantity (in the product's multiples, from its minimum). To stop orders for a product, set its status to **Sold out**, or tick **Hide from the shop**.
- **Orders** are saved in `data/orders/` and listed under **Orders** in the admin. Each one is emailed to your order address (support@fudakura.com.au by default) and to the customer, and a copy of the customer's confirmation also comes to your order address, so you see exactly what they were sent. Order statuses are: New → Payment details sent → Paid → Shipped (or Cancelled).
- **Free shipping**: orders whose goods total reaches the amount in **Shipping** ($2,000 by default) get free Standard shipping, and Express costs only the difference. It's shown in the bar at the top of every page, in the cart and at checkout. Set it to 0 to turn it off.
- **Payments**: crypto and PayID only. **Bitcoin** is paid on the site (see below); **Other crypto (ETH, USDT)** and **PayID / bank transfer (AUD)** get their payment details from you by email, with the invoice.
- **Shipping & Returns page**: one page with the delivery options, live rate tables and your policies. Edit the text in **Shipping**; the line `{rates}` is where the rate tables go, and questions under `## Questions` written as `### Question` are given to Google as FAQs. Old `/shipping` and `/returns` addresses redirect to it.

## Bitcoin payments

Customers who choose Bitcoin are taken straight to their order's payment page. It shows the exact BTC amount (worked out from the US-dollar total at the live price, held for 60 minutes and then renewed if unpaid), a QR code for their wallet, your address and copy buttons. The same page link is in their order email.

The page watches the blockchain for the payment. When it appears, you and the customer each get a **receipt email** with a link to follow the transaction on mempool.space, and a second email when it **confirms**; the order is then marked **Paid**. If a customer pays a different amount (for example an exchange took its fee from it), they can paste their transaction ID on the page, and you can attach one yourself on the order in the admin. Payments that come up short are flagged, never marked Paid.

- Set the address, how long an amount is held and how many confirmations to wait for in **Payments**. Only your address is stored: never your wallet's keys or recovery words.
- The server needs to reach the public price and blockchain services (mempool.space, blockstream.info, Coinbase, Kraken). Every normal host allows this (PHP's cURL, or `allow_url_fopen`).
- Every order uses the same address, so anyone who looks it up on a block explorer can see the payments it has received.

## Live chat

The Tawk.to chat code is in **Settings → Live chat**. It loads after each page has finished loading, so it doesn't slow the shop down. To see visitors live and answer chats on your phone, install the Tawk.to app and sign in. To use a different chat service, paste its code instead; leave the box empty to turn chat off.

## Search engines (SEO)

- **Moving from an old domain:** if an older copy of the shop has been live on another domain (e.g. pokekura.com or fudakura.store), upload this version there too and set **Settings → This site has moved to** = `https://fudakura.com.au`. Every old page then 301-redirects to the same page here, and in Search Console you can use *Change of address*.
- **Clean addresses:** most hosts (Apache or LiteSpeed) support addresses like `/products/151-booster-box`. Open `https://your-domain/shop`. If the shop appears, tick **Clean page addresses** in **Settings**. Old `index.php?p=…` links then redirect permanently to the clean ones.
- **What Google sees:** every product, category, set, collection, guide and page has its own title, description and main heading. You can edit all of them in the admin; blank fields fall back to sensible defaults.
- **Google Search Console:** turn on clean addresses first (below), then add your site as a *URL prefix* property, choose *HTML tag*, paste the tag into **Settings → Google Search Console verification**, save and press Verify. Bing has a matching box, or can import from Search Console.
- **Sitemap:** `index.php?p=sitemap` (or `/sitemap.xml` with clean addresses). Submit it in Google Search Console. `robots.txt` already points to it; if your domain changes, update the Sitemap line in `robots.txt`.
- **Keyword guides:** 22 guides (Admin → Guides) cover the Australian target keywords, including retailer, store and "near me" searches: where to buy in Australia (Kmart, Big W, Target, EB Games, JB Hi-Fi, Costco and others, as comparisons only), **Pokémon Center Australia**, card shops near me (Good Games stores, PokéBox, Purplish TCG, Gamers Village) and card shows, **booster packs** vs boxes, the most expensive, most valuable and rarest cards, card values and a live **price checker**, a Japanese **card database** (with Perfect Order and Ascended Heroes), how to play and read cards, grading costs, Chinese cards, a **free printable card template** (A4 and US Letter), card size and dimensions, scanner apps and where to sell. The home page, booster box, ETB and binder/folder category pages carry the head terms (Pokémon cards Australia, booster box, Pokémon ETB, Pokémon card binder). Store and retailer names are used only to describe where cards are sold; keep the "not affiliated" lines if you edit those guides, and check store details now and then, since they change.
- **Internal links:** every product, category, set, series, collection and FAQ page links to the guides that fit it, each guide links to four related guides and to the right products, and link text uses the keyword each guide targets. The map lives in `index.php` (GUIDE_RELATED and PAGE_GUIDES); guides you add in the admin appear on the Guides page and in "Related guides" automatically.
- **Updates keep your edits:** when a newer version adds or improves built-in content, it's applied once on the first page load, and only to text you haven't changed yourself.
- **Content tabs in the admin:** **Sets** (a page per set and series), **Collections** (e.g. Charizard cards), **Guides** (articles), and **Pages** (About, Returns, Privacy, Terms, all linked in the footer).
- **Formatting in intros, guides and descriptions:** a blank line starts a new paragraph, `## ` makes a heading, `- ` makes a bullet, `**bold**`. Links look like `[text](product:ID)`; you can also link to `category:KEY`, `set:SLUG`, `cards:SLUG`, `guide:SLUG`, `page:about` or a full `https://` address. In product descriptions, the first paragraph is the summary shown under the title.

## Backups

Every save keeps the previous version in `data/backups/`. To restore one, copy it over `data/store.php`. To back up everything, download the `data/` and `assets/products/` folders.
