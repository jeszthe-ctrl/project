# FUDAKURA storefront (Belgium)

FUDAKURA (札蔵) is an independent reseller of authentic Japanese Pokémon TCG products, shipping from Japan to Belgium (fudakura.eu). This is its shop, in plain PHP, in **Dutch and French**. It needs no database and runs on any PHP 7.4+ host.

## Files

| Path | What it is |
|---|---|
| `index.php` | The shop customers see, in Dutch (`/nl/…`) and French (`/fr/…`). |
| `admin.php` | Your backend: products, photos, prices, shipping, payments, settings, FAQ and orders. The admin itself is in English. |
| `inc/` | Shared code and the starting data. Blocked from the web. `defaults.php` holds what both languages share (prices, quantities, shipping rates); `content-nl.php` and `content-fr.php` hold each language's text; `lang/ui-nl.php` and `lang/ui-fr.php` translate the shop's buttons, labels and emails. |
| `data/` | Created automatically. Holds your edits (`store.php`), orders (`orders/`), the admin login (`auth.php`) and the last 30 versions of your data (`backups/`). Blocked from the web. |
| `assets/products/` | Product photos uploaded from the admin. Smaller copies for grids are kept in `thumbs/` and rebuilt automatically. |
| `assets/site/` | Site photos (home page hero, 30th Celebration feature, link-preview image) and the printable card templates. |
| `assets/qrcode.min.js` | Draws the Bitcoin QR code on the payment page (MIT-licensed qrcode-generator). |

You shouldn't need to edit any code. Day-to-day changes are made in `admin.php` and saved to `data/`, so uploading a newer version of the code never overwrites your products or orders.

## Install

**One file:** upload the single `index.php` built by `php tools/build-installer.php [admin-password]` (it lands in `dist/`) to your web space and open your site. It unpacks everything below next to itself, keeps your host's own `.htaccess` rules, moves a placeholder `index.html` aside, and then becomes the shop's normal `index.php`. Your data in `data/` is never overwritten, so uploading a newer one later updates the shop. If PHP can't create files in the folder, upload the files yourself:

1. Upload everything to your web space. Keep the folder structure.
2. Make sure PHP can write to `data/` and `assets/products/` (most hosts allow this by default; otherwise set them to 755 or 775).
3. Open `https://your-domain/admin.php`. If the installer was built with a password, sign in with it and change it under **Password**. Otherwise, **straight away**, create your admin password there: the first person to open that page sets it.
4. In the admin:
   - **Settings**: your registered company name, full business address, emails and website address. Then **Send a test email** (Settings, bottom) to your business address and to a personal one to check order emails arrive. If they don't, or land in spam, enter your mailbox's SMTP details under **Email**.
   - **Shipping**: your real shipping rates, the free-shipping amount and the Shipping & Returns page text (in both languages). The site ships with starting rates, and the dashboard warns you until you save this page.
   - **Payments**: check the Bitcoin address is yours (it's checked for typos before saving).
   - **Products**: add photos, check prices and add the rest of your range.
   - Turn on **Clean page addresses** (see below): Google ranks `/nl/pokemon-booster-box` far better than `index.php?p=…`.

> **Before you buy the domain:** a `.eu` domain can only be registered by a person living in the EU/EEA or a company established there. If that doesn't fit, choose another domain and enter it in **Settings → Website address** and in the Sitemap line of `robots.txt`.

## Two languages

- **Addresses:** every page exists twice, e.g. `/nl/winkel` and `/fr/boutique`, `/nl/producten/151-booster-box` and `/fr/produits/151-booster-box`. The bare domain sends visitors to their language (their last choice, else their browser's; Dutch by default). A **NL | FR** switch in the header leads to the same page in the other language.
- **Google:** each page tells Google about its twin in the other language (`hreflang` nl-BE / fr-BE), and the sitemap lists both, so Flemish searchers get the Dutch page and Walloon searchers the French one.
- **What's shared, what's per language:** prices, quantities, weights, stock status, photos, set names, shipping rates, payment settings, business details and orders are shared. The **text** of products, categories, series, sets, collections, guides, pages, FAQ, payment methods, delivery options and the home page is per language. In the admin, choose **Nederlands** or **Français** at the top to edit that language's text.
- **Missing text:** text you haven't written in one language shows the other language's until you do. A new product added in Dutch appears in French with the Dutch name until you switch to Français and translate it.
- **Guides and pages are separate per language**, each with its own address. Guides and pages that are the same topic in both languages are paired by a key (e.g. `prices`: *Pokémon kaarten waarde* ↔ *Prix des cartes Pokémon*), which links them for the language switch and Google.
- **Customers** get their order emails in the language they ordered in. Your own order emails are in English and say which language to reply in.

## How the shop works

- **Prices** are entered in USD per unit in the admin, with quantity breaks (e.g. 1+, 6+, 18+, 36+), and shoppers see them in **euros only**, converted with the EUR rate in **Settings → Currencies**. Keep that rate up to date. Amounts are written the Belgian way: `€ 1.234,56` in Dutch, `1 234,56 €` in French.
- **No VAT.** The shop never adds VAT or any other tax: the total at checkout, including shipping, is what the customer pays. The Shipping & Returns page, Terms and FAQ say so, and keep one line that, as with any parcel from outside the EU, the carrier may ask for import charges on some parcels.
- **Belgium only by default.** The country list in **Settings** holds just Belgium, so checkout skips the country choice. Add countries (and shipping zones) there to sell further afield: the Netherlands, Luxembourg, France and Germany show their names in Dutch and French automatically.
- **Minimum order**: the order total including shipping must reach the minimum set in **Settings** (US$58.14 by default, which shoppers see as €50). Checkout shows the shipping, the total and how much more is needed, and the server checks it again when the order is placed.
- **Shipping** is by weight, with two options at checkout: **Standard** (5–9 working days) and **Express** (2–4 working days). For each zone and option, the price is the per-order amount + the per-kg rate × the order weight. It is rounded up to a whole dollar unless you untick that in **Shipping**.
- **Free shipping**: orders whose goods total reaches the amount in **Shipping** (US$1,744.19 by default, shown as €1,500) get free Standard shipping, and Express costs only the difference. Set it to 0 to turn it off.
- **Payments**: crypto only. **Bitcoin** is paid on the site (see below); **Other crypto (ETH, USDT)** gets your wallet address by email, with the invoice.
- **Right of withdrawal**: the Terms and the Shipping & Returns page give consumers the EU's 14-day right of withdrawal and 2-year legal guarantee, with a deduction for opened sealed product. Business buyers don't get the withdrawal right. Google's product data carries the same 14-day return policy.
- **Minimum quantities**: products keep the wholesale minimums from the catalogue (sealed product mostly in fours or sixes). Many of the Belgian keywords are consumer searches, so consider lowering the minimum to 1 on popular boxes (Products → the product → Minimum quantity and Sold in multiples of) to sell single boxes to collectors.
- **No stock limits.** Customers can order any quantity (in the product's multiples, from its minimum). To stop orders for a product, set its status to **Sold out**, or tick **Hide from the shop**.
- **Orders** are saved in `data/orders/` and listed under **Orders** in the admin, with the customer's language. Each one is emailed to your order address (support@fudakura.eu by default) and to the customer, and a copy of the customer's confirmation also comes to your order address. Order statuses are: New → Payment details sent → Paid → Shipped (or Cancelled).

## Bitcoin payments

Customers who choose Bitcoin are taken straight to their order's payment page. It shows the exact BTC amount (worked out from the US-dollar total at the live price, held for 60 minutes and then renewed if unpaid), a QR code for their wallet, your address and copy buttons. The same page link is in their order email.

The page watches the blockchain for the payment. When it appears, you and the customer each get a **receipt email** with a link to follow the transaction on mempool.space, and a second email when it **confirms**; the order is then marked **Paid**. If a customer pays a different amount (for example an exchange took its fee from it), they can paste their transaction ID on the page, and you can attach one yourself on the order in the admin. Payments that come up short are flagged, never marked Paid.

- Set the address, how long an amount is held and how many confirmations to wait for in **Payments**. Only your address is stored: never your wallet's keys or recovery words.
- The server needs to reach the public price and blockchain services (mempool.space, blockstream.info, Coinbase, Kraken). Every normal host allows this (PHP's cURL, or `allow_url_fopen`).
- Every order uses the same address, so anyone who looks it up on a block explorer can see the payments it has received.

## Live chat

The Tawk.to chat code is in **Settings → Live chat**. It loads after each page has finished loading, so it doesn't slow the shop down. To see visitors live and answer chats on your phone, install the Tawk.to app and sign in. To use a different chat service, paste its code instead; leave the box empty to turn chat off.

## Search engines (SEO)

- **Clean addresses:** most hosts (Apache or LiteSpeed) support them. Open `https://your-domain/nl/winkel`. If the shop appears, tick **Clean page addresses** in **Settings**. Old `index.php?p=…` links then redirect permanently to the clean ones.
- **Google Search Console:** turn on clean addresses first, then add your site as a *URL prefix* property, choose *HTML tag*, paste the tag into **Settings → Google Search Console verification**, save and press Verify. Bing has a matching box, or can import from Search Console.
- **Sitemap:** `/sitemap.xml` with clean addresses, or `index.php?p=sitemap` (always works). It lists every page in both languages, each with its twin. Submit it in Google Search Console. `robots.txt` already points to it; if your domain changes, update the Sitemap line in `robots.txt`.
- **Keywords:** the Belgian keyword lists (Dutch and French) are built into the page titles, headings, addresses and text:
  - **Dutch:** home page *Pokémon kaarten kopen*; guide *Pokémon kaarten waarde* (with a live price checker; also targets *pokemon waarde*, *waarde pokemon kaarten* and the English *pokemon cards price value*); *Pokémon kaarten kopen in België*; category *Pokémon booster box* (*box booster pokemon*, *booster pokemon*); *Pokémon box*; plus guides on Japanese cards, new sets, the set list, boosters, the Pokémon TCG, spotting fakes and making your own card.
  - **French:** home page *cartes Pokémon japonaises* (*carte pokemon*, *cartes pokemon*); guide *Prix des cartes Pokémon* with the price checker (*prix carte pokemon*, *carte pokémon prix*, *prix d'une carte pokémon*…); *Valeur d'une carte Pokémon : cote et estimation*; *Où acheter des cartes Pokémon* (*acheter carte pokemon*, *magasin*, *à proximité*, *vente*); category *Display Pokémon japonais*; *Coffret carte Pokémon* / *boîte Pokémon*; *Album carte Pokémon*; guides on Japanese cards (*carte pokemon japonaise*), *sorties Pokémon*, the set list (*série carte pokemon*, *liste des cartes*), boosters (*booster pokemon*, *pack pokemon*), *TCG / jeu de cartes à collectionner*, the best decks, fakes and the card back (*carte pokemon dos*), making your own card (*card creator*), and *code JCC Pokémon Live* (*pokemon.fr/echanger*).
  - Searches for other shops' names (Hikaru, Kairyu, Cardshunter, Bescards…) and unrelated ones (flashcards, pokédex, toys) are deliberately not targeted.
- **Internal links:** every product, category, set, series, collection and FAQ page links to the guides that fit it, each guide links to four related guides and to the right products, and link text uses the keyword each guide targets (a guide's **Link text** in the admin). The map lives in `index.php` (GUIDE_RELATED and PAGE_GUIDES), by guide key.
- **Content tabs in the admin:** **Sets** (a page per set and series), **Collections** (e.g. Charizard / Dracaufeu cards), **Guides** (articles), and **Pages** (About, Wholesale, Terms, Privacy, all linked in the footer).
- **Formatting in intros, guides and descriptions:** a blank line starts a new paragraph, `## ` makes a heading, `- ` makes a bullet, `**bold**`. Links look like `[text](product:ID)`; you can also link to `category:KEY`, `set:SLUG`, `cards:KEY`, `guide:KEY`, `page:terms` or a full `https://` address. Keys are the same in both languages, so a link written in Dutch text finds the French page too. In product descriptions, the first paragraph is the summary shown under the title.

## Legal pages

The Terms of sale, Privacy policy and Shipping & Returns text were written for selling to consumers in Belgium from Japan (14-day withdrawal, 2-year guarantee, GDPR, the Belgian consumer mediation service and data protection authority). They're a starting point, not legal advice: have them checked for your business, and fill in your registered company name and address in **Settings**.

## Backups

Every save keeps the previous version in `data/backups/`. To restore one, copy it over `data/store.php`. To back up everything, download the `data/` and `assets/products/` folders.
