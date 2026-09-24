# FUDAKURA storefront

Wholesale Japanese Pokémon TCG shop in plain PHP. It needs no database and runs on any PHP 7.4+ host.

## Files

| Path | What it is |
|---|---|
| `index.php` | The shop customers see. |
| `admin.php` | Your backend: products, photos, prices, shipping, payments, settings, FAQ and orders. |
| `inc/` | Shared code and the starting data (`defaults.php`). Blocked from the web. |
| `data/` | Created automatically. Holds your edits (`store.php`), orders (`orders/`), the admin login (`auth.php`) and the last 30 versions of your data (`backups/`). Blocked from the web. |
| `assets/products/` | Product photos uploaded from the admin. |

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
- **Shipping** = the zone's per-order amount + its per-kg rate × the order weight (each product's weight × quantity). Set per-kg to 0 for flat-rate shipping. Countries not in any zone use the "Rest of world" rate.
- **No stock limits.** Customers can order any quantity (in the product's multiples, from its minimum). To stop orders for a product, set its status to **Sold out**, or tick **Hide from the shop**.
- **Orders** are saved in `data/orders/` and listed under **Orders** in the admin. Each one is also emailed to your order address and to the customer. Order statuses are: New → Payment details sent → Paid → Shipped (or Cancelled).
- **Payments** aren't taken on the site. The customer picks a method (each can be limited to certain countries) and you send them the details.

## Backups

Every save keeps the previous version in `data/backups/`. To restore one, copy it over `data/store.php`. To back up everything, download the `data/` and `assets/products/` folders.
