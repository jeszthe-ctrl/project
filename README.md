# FUDAKURA storefront

Wholesale Japanese Pokémon TCG shop in plain PHP. It needs no database and runs on any PHP 7.4+ host.

## Files

| Path | What it is |
|---|---|
| `index.php` | The shop customers see. |
| `admin.php` | Your backend: products, photos, prices, shipping, payments, settings, FAQ and orders. |
| `inc/` | Shared code and the starting data (`defaults.php`). Blocked from the web. |
| `data/` | Created automatically. Holds your edits (`store.php`), orders (`orders/`), the admin login (`auth.php`) and the last 30 versions of your data (`backups/`). Blocked from the web. |
| `assets/products/` | Product photos uploaded from the admin. Smaller copies for grids are kept in `thumbs/` and rebuilt automatically. |
| `assets/site/` | Site photos (home page hero, 30th Celebration feature, link-preview image). These are not product photos. |

You shouldn't need to edit any code. Day-to-day changes are made in `admin.php` and saved to `data/`, so uploading a newer version of the code never overwrites your products or orders.

## Install

1. Upload everything to your web space. Keep the folder structure.
2. Make sure PHP can write to `data/` and `assets/products/` (most hosts allow this by default; otherwise set them to 755 or 775).
3. **Straight away**, open `https://your-domain/admin.php` and create your admin password. The first person to open that page sets the password, so don't leave this step for later.
4. In the admin:
   - **Settings**: your registered company name, full business address, emails and website address.
   - **Shipping**: your real shipping rates. The site ships with placeholder rates, and the dashboard warns you until you save this page.
   - **Products**: add photos, check prices and add the rest of your range.

## How the shop works

- **Prices** are set in USD per unit, with quantity breaks (e.g. 1+, 6+, 18+, 36+). Other currencies are converted using the rates in **Settings**.
- **Minimum order**: the order total including shipping must reach the minimum set in **Settings** ($100 by default). Checkout shows the shipping, the total and how much more is needed as soon as the customer picks a country, and the server checks it again when the order is placed.
- **Shipping** is by weight, with two options at checkout: **Standard** (3–6 working days) and **Express** (1–2 working days). For each zone and option, the price is the per-order amount + the per-kg rate × the order weight (each product's weight × quantity). It is rounded up to a whole dollar unless you untick that in **Shipping**. Set per-kg to 0 for flat-rate shipping. Countries not in any zone use the "Rest of world" rates. The Shipping tab previews what US customers pay for typical orders.
- **No stock limits.** Customers can order any quantity (in the product's multiples, from its minimum). To stop orders for a product, set its status to **Sold out**, or tick **Hide from the shop**.
- **Orders** are saved in `data/orders/` and listed under **Orders** in the admin. Each one is also emailed to your order address and to the customer. Order statuses are: New → Payment details sent → Paid → Shipped (or Cancelled).
- **Payments** aren't taken on the site. The customer picks a method (each can be limited to certain countries) and you send them the details.

## Search engines (SEO)

- **Clean addresses:** most hosts (Apache or LiteSpeed) support addresses like `/products/151-booster-box`. Open `https://your-domain/shop`. If the shop appears, tick **Clean page addresses** in **Settings**. Old `index.php?p=…` links then redirect permanently to the clean ones.
- **What Google sees:** every product, category, set, collection, guide and page has its own title, description and main heading. You can edit all of them in the admin; blank fields fall back to sensible defaults.
- **Sitemap:** `index.php?p=sitemap` (or `/sitemap.xml` with clean addresses). Submit it in Google Search Console. `robots.txt` already points to it; if your domain changes, update the Sitemap line in `robots.txt`.
- **Content tabs in the admin:** **Sets** (a page per set and series), **Collections** (e.g. Charizard cards), **Guides** (articles), and **Pages** (About, Returns, Privacy, Terms, all linked in the footer).
- **Formatting in intros, guides and descriptions:** a blank line starts a new paragraph, `## ` makes a heading, `- ` makes a bullet, `**bold**`. Links look like `[text](product:ID)`; you can also link to `category:KEY`, `set:SLUG`, `cards:SLUG`, `guide:SLUG`, `page:about` or a full `https://` address. In product descriptions, the first paragraph is the summary shown under the title.

## Backups

Every save keeps the previous version in `data/backups/`. To restore one, copy it over `data/store.php`. To back up everything, download the `data/` and `assets/products/` folders.
