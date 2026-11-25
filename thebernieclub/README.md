# The Bernie Club website (thebernieclub.com)

The website for The Bernie Club, a bernedoodle breeder in Cincinnati, Ohio. Plain PHP, no database, runs on any PHP 7.4+ host (8.2 or 8.3 recommended). It is an independent site: it shares no code or data with any other site in this repository.

## What's in it

- **Its own design:** dark and professional (Bernese black, rust and warm white, Manrope): a cursor-lit hero with a rotating trust ring, a size explorer, cards that expand on hover, a timeline that fills as you scroll, spotlight guide cards and reveal-on-scroll, on every page.
- **Home page:** available puppies with filters (girls, boys, micro, mini, medium, standard), "Proceed now" on every puppy, sizes compared, why join the club, how it works, the one-minute inquiry, the guide library, the breed at a glance and buyer questions (with FAQ structured data).
- **Bernedoodle Puppies for Sale** (`/bernedoodle-puppies-for-sale`) and a page for every puppy, with photos or video stills, price, delivery, ready date, documents and both parents. Puppies without a photo show “Photos coming soon” until one is added.
- **State delivery pages** (`/bernedoodle-puppies-for-sale/{state}`) for "near me" searches.
- **Trust and E-E-A-T pages:** about, how we raise our puppies, health & testing, how we write our guides, health guarantee, how it works, what's included, puppy delivery, how to verify us, FAQ, terms, privacy. Every guide ends with an “About this guide” box.
- **37 guides and blog posts** (5 full-length) with a quick answer under each title and FAQ markup. See `docs/keyword-map.md`.
- **Inquiries only:** choose a dog, phone and email (both required), optional note. No payment methods or deposits on the site. The owner gets every inquiry at the site email and the customer gets a copy.
- **Admin** (`/admin.php`): puppies (with spreadsheet import), parents, litters, reviews (written by the owner), guides, pages, locations, delivery, email templates, inquiries.

## Install or update

Run `php tools/build-installer.php` and upload `dist/index.php` to the web space, then open the site. It unpacks itself and keeps existing data (puppies, inquiries, photos, admin sign-in).
