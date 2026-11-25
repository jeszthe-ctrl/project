# Golden Little Wonders website (goldenlittlewonders.com)

The website for Golden Little Wonders, a golden retriever breeder in Cincinnati, Ohio. Plain PHP, no database, runs on any PHP 7.4+ host (8.2 or 8.3 recommended). It is an independent site: it shares no code or data with any other site in this repository.

## What's in it

- **Its own design:** espresso and gold on warm ivory, Bricolage Grotesque and Figtree, a dark animated hero with glass cards, a bento grid, a "how it works" timeline that fills as you scroll, a magazine guide library and a gold closing band.
- **Home page:** available puppies with filters (girls, boys, American, English cream, field), "Proceed now" on every puppy, why families choose us, how it works, the one-minute inquiry, the guide library, the breed at a glance and buyer questions (with FAQ structured data).
- **Golden Retriever Puppies for Sale** (`/golden-retriever-puppies-for-sale`) and a page for every puppy, with photos or video stills, price, type, delivery, ready date, documents and both parents. Puppies without a photo show “Photos coming soon” until one is added.
- **State delivery pages** (`/golden-retriever-puppies-for-sale/{state}`) for "near me" searches.
- **Trust and E-E-A-T pages:** about, how we raise our puppies, health & testing, how we write our guides, health guarantee, how it works, what's included, puppy delivery, how to verify us, FAQ, terms, privacy. Every guide ends with an “About this guide” box.
- **39 guides and blog posts** (6 full-length) with a quick answer under each title and FAQ markup. See `docs/keyword-map.md`.
- **Inquiries only:** choose a dog, phone and email (both required), optional note. No payment methods or deposits on the site. The owner gets every inquiry at the site email and the customer gets a copy.
- **Admin** (`/admin.php`): puppies (with spreadsheet import), parents, litters, reviews (written by the owner), guides, pages, locations, delivery, email templates, inquiries.

## Install or update

Run `php tools/build-installer.php` and upload `dist/index.php` to the web space, then open the site. It unpacks itself and keeps existing data (puppies, inquiries, photos, admin sign-in).
