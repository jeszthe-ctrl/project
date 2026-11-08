# POKEKURA storefront

POKEKURA (ポケ蔵) is an independent distributor and reseller of authentic Japanese Pokémon TCG products, shipping wholesale orders worldwide from Japan. This is its shop, in plain PHP. It needs no database and runs on any PHP 7.4+ host.

## Files

| Path | What it is |
|---|---|
| `index.php` | The shop customers see. |
| `admin.php` | Your backend: products, photos, prices, shipping, payments, settings, FAQ and orders. |
| `inc/` | Shared code and the starting data (`defaults.php`). Blocked from the web. |
| `data/` | Created automatically. Holds your edits (`store.php`), orders (`orders/`), the admin login (`auth.php`) and the last 30 versions of your data (`backups/`). Blocked from the web. |
| `assets/products/` | Product photos uploaded from the admin. Smaller copies for grids are kept in `thumbs/` and rebuilt automatically. |
| `assets/site/` | Site photos (home page hero, 30th Celebration feature, link-preview image). A fresh install also copies the two 30th Celebration Elite Trainer Box photos to that product; the other products get photos from the admin. |
| `assets/qrcode.min.js` | Draws the Bitcoin QR code on the payment page (MIT-licensed qrcode-generator). |

You shouldn't need to edit any code. Day-to-day changes are made in `admin.php` and saved to `data/`, so uploading a newer version of the code never overwrites your products or orders.

## Logo

`assets/logo.svg` is the full logo (the kura mark, POKEKURA and ポケ蔵 · JAPAN TCG WHOLESALE), `assets/logo-white.svg` the version for dark backgrounds, `assets/logo-mark.svg` the square mark, and `assets/wordmark.svg` the name alone. The lettering is converted to shapes, so it looks the same everywhere. The browser icons are `favicon.ico`, `assets/favicon.svg` and `assets/apple-touch-icon.png`; `assets/logo-512.png` is the logo Google shows for the business, and `assets/site/share-pokekura.jpg` is the picture shown when a page is shared on social media or in messages.

## Install

`php tools/build-installer.php [admin-password] [admin-username]` builds two things in `dist/` (with a password, both include that admin sign-in; the username is `admin` unless you give one). Needs PHP 7.4 or newer (8.2 or 8.3 recommended).

**Easiest: the zip.** In your host's File Manager, open `public_html`, **Upload** `pokekura-upload.zip`, right-click it and choose **Extract**, then delete the zip. If the host put a placeholder page there (`index.html`, `default.php`), delete it too. Then open `https://your-domain/admin.php`.

**Or one file:** upload `index.php` with File Manager's **Upload** button (FTP: *binary* mode) and open `https://your-domain/index.php` (with `/index.php`, in case the host's placeholder page would show first). It checks your hosting (PHP version and extensions, folder permissions), unpacks everything next to itself, keeps your host's own `.htaccess` rules, moves placeholder pages (`index.html`, `default.php`, …) aside, and becomes the shop's normal `index.php`. The site travels inside it as text with a fingerprint: if the file was damaged on the way (text-mode transfer, opened in an editor, cut off), it says so and changes nothing. Your data in `data/` is never overwritten, so uploading a newer one later updates the shop.

Uploading the files by hand instead:

1. Upload everything to your web space. Keep the folder structure.
2. Make sure PHP can write to `data/` and `assets/products/` (most hosts allow this by default; otherwise set them to 755 or 775).
3. Open `https://your-domain/admin.php` (or just `/admin`). If the installer was built with a sign-in, use that username and password, and change them under **Sign-in**. Otherwise, **straight away**, create your admin username and password there: the first person to open that page sets them.
4. In the admin:
   - **Settings**: your registered company name, full business address, emails and website address. Then **Send a test email** (Settings, bottom) to your business address and to a personal one to check order emails arrive. If they don't, or land in spam, enter your mailbox's SMTP details under **Email**.
   - **Shipping**: your real shipping rates, the free-shipping amount and the Shipping & customs / Returns page text. The site ships with starting rates, and the dashboard warns you until you save this page.
   - **Payments**: check the Bitcoin address is yours (it's checked for typos before saving).
   - **Products**: add photos, check prices and add the rest of your range.

## How the shop works

- **Card language** is shown on every product (a badge on cards, the product details and a quick-answers box). It's automatic — English for Elite Trainer Boxes, Japanese for everything else — or set it per product in **Products → Card language**.
- **Prices** are set in USD per unit, with bulk tiers (e.g. 1+, 6+, 12+, 24+) in **Products**. Each tier is shown as a one-tap button on product cards and pages ("12+ $267 each −18%"): tapping it adds that quantity. The price updates live as the quantity changes, and the cart offers "Add 6 more → $X each" to reach the next tier. Other currencies are converted using the rates in **Settings**.
- **Minimum order value** (**Settings**, $100 by default) is not advertised anywhere on the shop. Checkout only mentions it if an order's total falls below it. Set it to 0 to turn it off.
- **Checkout** asks for name, email, phone (required, for the courier) and shipping address. Company, apartment and notes are optional and folded away; the country is pre-selected from the visitor's browser, and the first available payment method is picked. There is no terms tick-box: the button text says that placing the order accepts the terms and returns policy.
- **Wholesale pages**: Sealed cases (`/sealed-cases`), Minimum order quantities (`/minimum-order-quantities`), Request a quote (`/request-a-quote`), Join the B2B list (`/b2b-list`) and Track an order (`/track-order`). Quote requests and B2B sign-ups are emailed to your order address and listed under **Leads** in the admin (the B2B list downloads as CSV). Track an order finds an order by its reference and email; put the courier's tracking number on the order in the admin and it shows there.
- **Shipping** is by weight, with two options at checkout: **Standard** (3–6 working days) and **Express** (1–2 working days). For each zone and option, the price is the per-order amount + the per-kg rate × the order weight (each product's weight × quantity). It is rounded up to a whole dollar unless you untick that in **Shipping**. Set per-kg to 0 for flat-rate shipping. Countries not in any zone use the "Rest of world" rates. The Shipping tab previews what US customers pay for typical orders.
- **No stock limits.** Customers can order any quantity (in the product's multiples, from its minimum). To stop orders for a product, set its status to **Sold out**, or tick **Hide from the shop**.
- **Orders** are saved in `data/orders/` and listed under **Orders** in the admin. Each one is also emailed to your order address and to the customer. Order statuses are: New → Payment details sent → Paid → Shipped (or Cancelled).
- **Free shipping**: orders whose goods total reaches the amount in **Shipping** ($2,000 by default) get free Standard shipping, and Express costs only the difference. It's shown in the bar at the top of every page, in the cart and at checkout. Set it to 0 to turn it off.
- **Payments**: the customer picks a method (each can be limited to certain countries). For ordinary methods you send them the details. **Bitcoin** is paid on the site — see below.
- **Shipping & customs and Returns pages** (`/shipping`, `/returns`): both come from the one policy text in **Shipping**. Sections headed *If something goes wrong*, *Returns*, *Refunds*, *Cancellations…* and *Authenticity…* show on Returns; everything else on Shipping & customs. The line `{rates}` is where the rate tables go, and questions under `## Questions` written as `### Question` are given to Google as FAQs. The old `/shipping-returns` address redirects.

## Product range (November 2026)

`inc/catalog.php` holds the 180 products added in November 2026: English Pokémon sealed product (30th Celebration, Delta Reign and the other Mega Evolution sets, Scarlet & Violet ETBs and Pokémon Center ETBs, booster boxes, bundles, UPCs, tins, blisters), Japanese booster boxes and English One Piece Card Game. Each has its own description, SEO title and description, bulk tiers (4+, 6+, 10+, 24+; 10+, 36+, 100+ for packs and blisters; 2+, 4+, 6+ for items over $300), weight and language. **Prices are set 8% under market estimates** (`DISCOUNT` in the generator): check them in **Products**. Shops installed earlier receive any of these products they don't already have once, on the first page load (nothing you added or edited is changed). New set pages, the One Piece Card Game category and collection pages (Pokémon Center ETBs, UPCs, English and Japanese booster boxes, bundles, tins, Build & Battle Boxes, packs and blisters) come with them, and the home page features the new ranges. Add your own photos in **Products**. `tools/catalog_gen.py` regenerates the file.

## Customer reviews

Add the reviews customers send you in **Reviews**: their name as they agreed to have it shown, country, rating, an optional title, their words, the product (or the shop in general), the date and, optionally, their order reference (shown as "Verified order" when it matches an order). Reviews show on the home page ("What our customers say", the latest three), on the product they're about (with a star summary under the title), and on `/reviews`, linked from the footer. Untick **Show on the shop** to hide one. Product pages with reviews give Google the star rating for that product; shop-wide reviews are not marked up, because Google doesn't show stars for a business's reviews of itself. Only add real reviews.

## Design

White first, blue for buttons and links, black for emphasis (top bar, active tabs, badges). Product lists (shop, categories, sets, series, collections, sealed cases) have a **Grid / Compact / List** switch: Grid shows 4 products a row on desktop and 2 on phones, Compact 6 and 3, and List is a one-row-per-product price list with bulk tiers and quantity. Each visitor's choice is remembered in their browser. The logo files and the link-preview image (`assets/site/share-pokekura.jpg`) are white, blue and black.

## Shopping and content (content update 12)

- **Shop pages** have quick picks (Elite Trainer Boxes, booster boxes, collections, One Piece, Pokémon Center ETBs, UPCs, Japanese boxes, preorders, sealed cases), a **Newest first** sort, and filters that fold away on phones.
- **Product pages on phones** keep an "Add to order" bar at the bottom while the buy box is off screen. The header button shows **Cart**, the item count and the order total.
- **Content:** real intros on the English and Japanese set pages (with the matching English or Japanese set), questions and answers on the main category pages (sent to Google as FAQ data), and four buyer guides — Pokémon Center ETB vs regular ETB, what a UPC is, booster box vs ETB vs booster bundle, and One Piece booster boxes — linked from the categories, collections and home page. Existing shops get them once; text you've edited is kept.

## Bitcoin payments

Customers who choose Bitcoin are taken straight to their order's payment page. It shows the exact BTC amount (worked out from the US-dollar total at the live price, held for 60 minutes and then renewed if unpaid), a QR code for their wallet, your address and copy buttons. The same page link is in their order email.

The page watches the blockchain for the payment. When it appears, you and the customer each get a **receipt email** with a link to follow the transaction on mempool.space, and a second email when it **confirms**; the order is then marked **Paid**. If a customer pays a different amount (for example an exchange took its fee from it), they can paste their transaction ID on the page, and you can attach one yourself on the order in the admin. Payments that come up short are flagged, never marked Paid.

- Set the address, how long an amount is held and how many confirmations to wait for in **Payments**. Only your address is stored: never your wallet's keys or recovery words.
- The server needs to reach the public price and blockchain services (mempool.space, blockstream.info, Coinbase, Kraken). Every normal host allows this (PHP's cURL, or `allow_url_fopen`).
- Every order uses the same address, so anyone who looks it up on a block explorer can see the payments it has received.

## Live chat

The Tawk.to chat code is in **Settings → Live chat**. It loads after each page has finished loading, so it doesn't slow the shop down. To see visitors live and answer chats on your phone, install the Tawk.to app and sign in. To use a different chat service, paste its code instead; leave the box empty to turn chat off.

## Search engines (SEO)

- **Changing domain:** if you ever move this shop to a new domain of your own, keep a copy on the old one and set **Settings → This site has moved to** to the new address. Every old page then 301-redirects to the same page there, and in Search Console you can use *Change of address*.
- **Clean addresses:** most hosts (Apache or LiteSpeed) support addresses like `/products/151-booster-box`. The shop checks this when it's installed (and again from the admin dashboard) and switches them on by itself when they work. To check by hand, open `https://your-domain/shop`: if the shop appears, **Clean page addresses** can be ticked in **Settings**. Old `index.php?p=…` links then redirect permanently to the clean ones. If you untick it, it stays off.
- **What Google sees:** every product, category, set, collection, guide and page has its own title, description and main heading. You can edit all of them in the admin; blank fields fall back to sensible defaults.
- **Google Search Console:** check clean addresses are on first (above), then add your site as a *URL prefix* property, choose *HTML tag*, paste the tag into **Settings → Google Search Console verification**, save and press Verify. Bing has a matching box, or can import from Search Console.
- **Sitemap:** `index.php?p=sitemap` (or `/sitemap.xml` with clean addresses). Submit it in Google Search Console. `robots.txt` already points to it; if your domain changes, update the Sitemap line in `robots.txt`.
- **Keyword guides:** 20 guides (Admin → Guides) cover every target keyword, including retailer, "near me" and tool searches: the most expensive and rarest cards, card values and a live **price checker**, a Japanese **card database**, where to buy (Walmart, Target, Costco, GameStop and others, as comparisons only), card shops and shows, how to play and read cards, grading costs, Chinese cards, a **free printable card template**, scanner apps and where to sell. Retailer names are used only to describe where cards are sold; keep the "not affiliated" line if you edit that guide.
- **Internal links:** every product, category, set, series, collection and FAQ page links to the guides that fit it, each guide links to four related guides and to the right products, and link text uses the keyword each guide targets. The map lives in `index.php` (GUIDE_RELATED and PAGE_GUIDES); guides you add in the admin appear on the Guides page and in "Related guides" automatically.
- **Updates keep your edits:** when a newer version adds or improves built-in content, it's applied once on the first page load, and only to text you haven't changed yourself.
- **Content tabs in the admin:** **Sets** (a page per set and series), **Collections** (e.g. Charizard cards), **Guides** (articles), and **Pages** (About, Wholesale, Privacy, Terms of service).
- **Footer:** four columns — Shop (the categories), Wholesale (How it works, Sealed cases, Browse by set, Join the B2B list, Request a quote, Track an order, Minimum order quantities), Support (Shipping & customs, Payment methods, Returns, Contact, FAQ, Buyer guides) and Company (About, Terms of service, Privacy policy) — plus a line of popular sets, collections and guides for search engines.
- **Formatting in intros, guides and descriptions:** a blank line starts a new paragraph, `## ` makes a heading, `- ` makes a bullet, `**bold**`. Links look like `[text](product:ID)`; you can also link to `category:KEY`, `set:SLUG`, `cards:SLUG`, `guide:SLUG`, `page:about` or a full `https://` address. In product descriptions, the first paragraph is the summary shown under the title.

## Backups

Every save keeps the previous version in `data/backups/`. To restore one, copy it over `data/store.php`. To back up everything, download the `data/` and `assets/products/` folders.
