# Pepper Whisker website (pepperwhisker.com)

The website for Pepper Whisker, a schnauzer breeder in Cincinnati, Ohio. Plain PHP, no database, runs on any PHP 7.4+ host (8.2 or 8.3 recommended). It is a separate site from Beagle Brook and shares no data with it.

## What's in it

- **Home page:** available puppies right under the hero, "Proceed now" on every puppy, the one-minute inquiry, guarantees, what's included, the breed at a glance and buyer questions (with FAQ structured data).
- **Schnauzer Puppies for Sale** (`/schnauzer-puppies-for-sale`) and a page for every puppy, with photos or video stills, price, delivery, ready date, documents and both parents. Only puppies with a photo or video are shown.
- **State delivery pages** (`/schnauzer-puppies-for-sale/{state}`) for "near me" searches.
- **Trust pages:** how it works, health guarantee, what's included, puppy delivery, how to verify us, about, FAQ, terms, privacy.
- **53 guides and blog posts** with a quick answer under each title and FAQ markup. See `docs/keyword-map.md`.
- **Inquiries only:** choose a dog, phone and email (both required), optional note. No payment methods or deposits on the site. The owner gets every inquiry at the site email and the customer gets a copy.
- **Admin** (`/admin.php`): puppies (with spreadsheet import), parents, litters, reviews (written by the owner), guides, pages, locations, delivery, email templates, inquiries.

## Install or update

Run `php tools/build-installer.php` and upload `dist/index.php` to the web space, then open the site. It unpacks itself and keeps existing data (puppies, inquiries, photos, admin sign-in).
