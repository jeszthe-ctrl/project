# FUDAKURA France

The French shop for fudakura-france.com: authentic Japanese Pokémon TCG products shipped from Japan to France, Belgium, Luxembourg and Monaco. The whole storefront, checkout, customer emails, guides and legal pages are in French; the admin stays in English. Plain PHP, no database, any PHP 7.4+ host.

This folder is a separate copy of the shop. The Australian shop (fudakura-australia.com) is the repository root and is not affected by anything here.

## Files

| Path | What it is |
|---|---|
| `index.php` | The shop customers see (French). |
| `admin.php` | Your backend: products, photos, prices, shipping, payments, settings, FAQ, pages and orders. |
| `inc/` | Shared code and the starting data: `defaults.php` (settings, products, payments, shipping), `content.php` (page texts, categories, sets, legal pages, FAQ) and `guides.php` (the 10 guides). Blocked from the web. |
| `data/` | Created automatically: your edits, orders, the admin login and backups. Blocked from the web. |
| `assets/` | Logo, icon, site photos and product photos you upload. |

Day-to-day changes are made in `admin.php`; uploading a newer version of the code never overwrites your products or orders.

## Install

**One file:** upload the `index.php` built by `php tools/build-installer.php [admin-password]` (in `dist/`) and open the site. It unpacks everything next to itself and becomes the shop. If PHP can't create files in the folder, upload the files from the zip instead, then open `admin.php` straight away and set your admin password.

Then in the admin:

- **Settings**: your registered company name and full address (they appear on the Terms, Legal notice and Privacy pages), and **Send a test email**. If emails don't arrive, enter your mailbox's SMTP details.
- **Pages**: complete the **Mentions légales** (host name and address, SIRET if you have one) and name your consumer mediator in the **CGV** — both are legal requirements in France.
- **Shipping**: your real rates. The shop starts with estimates, and the dashboard reminds you until you save this page.
- **Payments**: check the Bitcoin address; add your IBAN wording to the SEPA method if you want it on the checkout.
- **Products**: add photos and check prices.

## How the shop works

- **Prices** are entered in USD in the admin and shown in **euros only**, converted with the EUR rate in **Settings → Currencies** (0.86 to start). Keep it up to date. Amounts are shown the French way: 1 234,50 €.
- **Everybody can buy**, from one item. Every product still shows its quantity-break prices.
- **No tax is added** to prices. The Shipping and Terms pages mention that the carrier may charge import fees on delivery, since parcels come from Japan.
- **Minimum order**: 50 € including shipping (Settings). It is only mentioned at checkout, when an order falls short.
- **Countries**: France, Belgium, Luxembourg and Monaco, one shipping zone. Standard (5 to 10 working days) and Express (2 to 5), priced per order plus per kg and rounded up to whole euros. Free Standard shipping from 250 € of goods.
- **Payments**: Bitcoin paid on the site (QR code, live price, automatic receipt), other crypto (ETH, USDT) and SEPA bank transfer in euros; for those two you email the details with the invoice.
- **Consumer rights**: the Terms (CGV) and Shipping & Returns pages include the 14-day right of withdrawal, the legal guarantees and returns process.
- **Live chat**: the Tawk.to code is in **Settings → Live chat**.

## SEO

- French page addresses with clean URLs on: `/boutique`, `/displays-pokemon`, `/etb-pokemon`, `/coffrets-pokemon`, `/cartes-pokemon-rares`, `/classeur-carte-pokemon`, `/produits/…`, `/extensions/…`, `/cartes/dracaufeu`, `/guides/…`, `/livraison-retours`, `/cgv`, `/mentions-legales`. Tick **Clean page addresses** in Settings once `https://fudakura-france.com/boutique` opens the shop.
- Every page has a French title, description and heading, `lang="fr-FR"`, structured data (organisation, products with euro prices, breadcrumbs, articles, FAQs) and a sitemap at `/sitemap.xml` (or `index.php?p=sitemap`). Submit it in Google Search Console.
- **Keywords covered**: carte(s) Pokémon (home), display Pokémon, ETB / Coffret Dresseur d'Élite, coffret Pokémon and decks, carte Pokémon rare, classeur carte Pokémon, Dracaufeu (ex, Méga X/Y, shiny, coffret), Pikachu, Pokémon 151, Méga-Lucario-ex, Flammes Fantasmagoriques (Inferno X), carte Pokémon japonaise, and 10 guides: prix / valeur / cote / estimation, la carte la plus chère, cartes rares, rareté, cartes japonaises, display vs ETB vs coffret, extensions (French, English and Japanese names), séries (Noir & Blanc, HeartGold SoulSilver…), how to play and decks, where to buy.
- Competitor and marketplace names (Cardmarket, MKM), other card games (One Piece, Magic, Dragon Ball, Lorcana, Yu-Gi-Oh) and the video games' Pokédex searches are not targeted: people searching them want another site or product.
