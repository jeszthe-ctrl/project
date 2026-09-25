<?php
/* Starting SEO content for a fresh install: category, series, set and collection pages, guides,
   home page text and FAQ. Copied into data/store.php on first load and edited in admin.php after.

   Formatting (intros and guides): blank line = new paragraph, "## " heading, "- " bullet, **bold**,
   [text](link). Links can be full URLs or shop pages: product:ID, category:KEY, set:SLUG (sets and
   series), cards:SLUG (collections), guide:SLUG, page:shop|sets|guides|faq|shipping|payment|how|contact.
   {min_order} {reply_hours} {hold_hours} {countries} {free_ship} {standard} {express} {standard_days} {express_days}
   {brand} {company} {address} {email} are filled in with current values. */
if(!defined('FK_ROOT')){ http_response_code(404); exit; }

return [
  'settings' => [
    'strip_text'    => 'Sourced in Japan · Shipped to the USA with tracking · Bulk pricing on every listing',
    'hero_title'    => 'Japanese Pokémon cards, shipped from Japan to the USA.',
    'hero_lede'     => 'Sealed Japanese booster boxes, Elite Trainer Boxes, rare singles and PSA graded cards, sourced in Japan and shipped with tracking. Every quantity break is published, so collectors and card shops see the price before they order.',
    'footer_blurb'  => 'Japanese Pokémon cards shipped from Japan to collectors, resellers and card shops across the USA. Prices published on every listing — no account needed.',
    'home_seo_title'=> 'Japanese Pokémon Cards — Booster Boxes & Singles',
    'home_seo_desc' => 'Authentic Japanese Pokémon cards shipped from Japan to the USA: sealed booster boxes, ETBs, rare singles and PSA graded cards, with bulk pricing published.',
    'home_intro'    => <<<'MD'
## Why buy Japanese Pokémon cards?

Japanese sets come out first, often months before their English versions, so Japanese booster boxes are how collectors get the newest cards early. Many collectors also prefer Japanese printing and card quality, and Japanese special sets like [151](set:151) and [Terastal Festival ex](set:terastal-festival-ex) are some of the most sought-after boxes in the hobby.

Everything we sell is sourced in Japan and shipped to the USA sealed, exactly as it left the factory. Browse [booster boxes](category:boxes), [rare single cards](category:singles), [PSA graded cards](cards:psa-graded-pokemon-cards) or [card binders and sleeves](category:accessories), or start with our guide to [Japanese Pokémon cards](guide:japanese-pokemon-cards).

## Buying in bulk?

Every listing shows its full quantity-break ladder, so card shops and resellers can price a complete order up front. Orders start at {min_order} including shipping, and shipping to the USA is calculated at checkout.
MD,
    /* the Shipping & Returns page ({rates} = the delivery options and rate tables) */
    'shipping_policy' => <<<'MD'
Every {brand} order ships from Japan with tracking. This page explains how we ship, what it costs, how long it takes and what happens if something goes wrong, in plain English. The price at checkout is always the final word on shipping for your order.

## Delivery options and rates

{rates}

## Free shipping

Orders with a goods total over **{free_ship}** ship free with {standard} delivery, to every country we ship to. It's applied automatically at checkout, with no code to enter. Want it faster? Choose {express} and you pay only the difference between {express} and {standard}.

The threshold counts the goods in your order (after quantity breaks, before shipping) in US dollars. Import duty and taxes are never included.

## From order to doorstep

1. **You place your order** and get an email with your order reference straight away.
2. **You pay.** Bitcoin is paid on your order page the moment you order. For other methods, we send payment details within {reply_hours} hours.
3. **Your payment clears** and your stock is allocated to you.
4. **We pack and dispatch** your order from Japan within {hold_hours} hours.
5. **We email your tracking number** as soon as the label is created.
6. **Your parcel is delivered.** Check it over as soon as you can (see below).

Delivery times start when your parcel leaves us, not when you order.

## Tracking your parcel

Every parcel is tracked, and we email your tracking number the day the label is created. A new tracking number can take 24–48 hours to show its first scan, which is normal. Tracking usually runs: label created → accepted in Japan → export customs → in transit → import customs → out for delivery → delivered.

If tracking hasn't changed for 5 working days, email us and we'll chase the carrier.

## Delivery times and delays

Delivery times are estimates in working days after dispatch, not guarantees. They can stretch when:

- customs in your country holds a parcel for inspection
- it's a holiday in Japan (New Year, Golden Week in early May, Obon in mid-August) or where you are
- severe weather or natural disasters disrupt flights and roads
- carriers are backlogged, for example in the run-up to Christmas
- the address is remote, incomplete or hard to reach

Planning a store launch, a release event or a stream? Choose {express} and give yourself a few spare days.

## Customs, duty and taxes

Your order ships from Japan, so it clears customs in your country. Import duty, VAT/GST, sales tax and carrier clearance fees are **not included** in our prices or shipping, and are paid by the buyer. Your carrier collects them before or on delivery. US orders of any value can be charged import duty and carrier fees.

We declare every parcel honestly, with its true contents and value. We can't mark orders as gifts or declare a lower value.

If import charges are refused, the parcel comes back to Japan. See [returned to sender](page:shipping#returned-to-sender).

## Your delivery address

Please double-check the recipient's name, street address, apartment or suite number, city, state or region, ZIP or postal code and phone number before you order. Carriers use your phone number for customs questions and to arrange delivery.

Need to change the address? Email us straight away with your order reference. We can change it until your parcel is handed to the carrier. After that, it usually can't be redirected.

## How we pack

- **Sealed product** ships in its factory shrink wrap, boxed with padding so it can't move around.
- **Cases** ship in their original distributor carton, inside an outer box where size allows.
- **Single cards** ship sleeved and toploaded, sealed against moisture, in a rigid mailer or box.
- **Graded cards** ship with the slab wrapped and boxed so the case can't take a knock.

## If something goes wrong

### Check your order when it arrives

Open your parcel as soon as you can and check:

- the outer box for crushing, tears or water damage
- the number of items against your order confirmation
- the shrink wrap and seals on sealed product
- the condition of single cards, and the labels on graded slabs

If anything is wrong, **keep everything**: the outer box and its label, the packing and the items. Take photos before you throw anything away, because carriers ask for them. For valuable orders, film the unboxing.

### Damaged in transit

Email [{email}](mailto:{email}) within **7 days of delivery** with your order reference and photos of the parcel (all sides, and the label), the packing and the damage. We'll replace, credit or refund the damaged items and their shipping.

Light shelf wear on sealed boxes, such as a small dent, scuff or crease in the shrink wrap from factory and distributor handling, is normal and isn't transit damage. If you're not sure, send us photos and ask.

### Missing or wrong items

Compare what arrived with your order confirmation. If something is missing, or you received a different item, keep the wrong item unopened. Email us within 7 days with your order reference, what's missing or wrong, and photos of what arrived. We'll send what you ordered or refund it, and when the mistake is ours we cover the return shipping too.

### Lost in transit

If your parcel hasn't arrived 10 working days after the latest delivery estimate, or tracking hasn't moved for 7 working days, email us and we'll open a trace with the carrier. If the carrier confirms it's lost, we'll reship your order or refund it in full.

### Marked delivered but not received

Check around your property, with neighbours and with your building's reception or mailroom, and look for a delivery photo or signature on the tracking page. Still missing after 48 hours? Email us. We'll open a claim with the carrier and help however we can.

### Returned to sender

Parcels come back to us when the address is wrong or incomplete, delivery attempts fail, the parcel isn't collected or import charges are refused. We'll get in touch: we can reship once the new shipping is paid, or refund your order less the shipping both ways and any charges we're billed.

## Returns

Pokémon cards and sealed product are collectibles. Their value depends on their condition and on knowing exactly what's inside. So:

- **Contact us before sending anything back.** We can't accept returns we haven't agreed.
- **Sealed product** can come back only unopened, in its original shrink wrap and in the condition it arrived.
- **Opened product** (boxes, packs, cases or tins) can't be returned, because its contents can no longer be verified.
- **Single cards and graded slabs** must come back in the same holder or slab, in the same condition. We check slab certification numbers against what we shipped.
- **Change-of-mind returns** are at our discretion. If we agree to one, you pay the return shipping and any import charges, and we refund the goods once they're back and checked.
- **Our mistakes are free to fix.** Damaged, wrong and missing items never cost you return shipping.

## Refunds

Once we approve a refund, we send it within 5 working days, the same way you paid:

- **Bitcoin and other crypto:** sent to a wallet address you confirm by email, for the US-dollar value of the refunded items, converted at the rate on the day we refund. Network fees come out of the amount sent.
- **Other methods:** back through the same method where it allows refunds, or by bank transfer.

Your bank, card or wallet may take a few more days to show it. Shipping is refunded when the problem was ours.

## Cancellations and preorders

You can cancel free of charge any time before your order is dispatched: email us with your order reference. Once it has shipped, the returns rules above apply.

Preorders are invoiced when stock is allocated, not when you order, and ship as soon as stock arrives, usually on or just after the Japanese release date. Release dates are set by The Pokémon Company and sometimes move. If we can't fill a preorder, we refund it in full.

## Authenticity guarantee

Everything we sell is genuine, bought through Japanese distribution and shipped exactly as it left the factory. If you ever doubt an item, keep it as it arrived and email us with photos. We'll look into it straight away and make it right.

## Questions

### Do you ship Pokémon cards to the USA?

Yes. Everything ships from Japan to the USA with tracking: {standard} takes {standard_days} and {express} takes {express_days}.

### Do you offer free shipping?

Yes. Orders over {free_ship} ship free with {standard} delivery, anywhere we ship. {express} costs only the difference.

### How much is shipping?

It depends on your order's weight and where it's going. The exact price shows at checkout before you order, and the rate table on this page shows how it's worked out.

### How do I track my order?

We email your tracking number when your order ships. New tracking numbers can take 24–48 hours to show their first scan.

### Will I pay import duty or taxes?

Possibly. Import duty, taxes and carrier fees aren't included in our prices, and your carrier collects them before or on delivery.

### My order arrived damaged. What do I do?

Keep the parcel, the packing and the items, take photos, and email us within 7 days of delivery. We'll replace, credit or refund the damaged items.

### Can I return a booster box I've opened?

No. Opened boxes, packs and cases can't be returned, because their contents can no longer be verified. Unopened sealed product can be returned by agreement.

### Can I change my delivery address?

Yes, until your parcel is handed to the carrier. Email us with your order reference as soon as possible.

### Can I cancel my order?

Yes, free of charge until it's dispatched. Email us with your order reference.

### How are Bitcoin payments refunded?

To a wallet address you confirm by email, for the US-dollar value of the refunded items at that day's rate, less network fees.

## Contact us

Email [{email}](mailto:{email}) with your order reference, your tracking number if you have one, what went wrong, and photos where they help. We reply within {reply_hours} hours.

{company} · {address}
MD,
  ],

  /* h1 and seo_title fall back to the label, seo_desc to the blurb */
  'categories' => [
    'boxes' => [
      'label' => 'Booster Boxes', 'slug' => 'booster-boxes',
      'blurb' => 'Sealed Japanese Pokémon booster boxes and cases, straight from Japan.',
      'h1' => 'Japanese Pokémon booster boxes & card packs',
      'seo_title' => 'Japanese Pokémon Booster Boxes & Card Packs',
      'seo_desc' => 'Sealed Japanese Pokémon booster boxes and cases — 151, Terastal Festival ex, Mega Evolution sets and more — shipped from Japan to the USA with bulk pricing.',
      'intro' => <<<'MD'
## Pokémon booster boxes from Japan

A Pokémon booster box is a sealed display of card packs from a single set, and the best-value way to buy Pokémon card packs. Japanese boxes are smaller than English ones: most Japanese main-set boxes hold 30 packs of 5 cards, while special sets differ — a [151](set:151) box holds 20 packs of 7 cards.

We stock the current Japanese [Mega Evolution](set:mega-evolution) sets alongside favourites from the [Scarlet & Violet](set:scarlet-violet) era, plus full sealed cases for bulk buyers. Every Pokémon box ships sealed from Japan, and the price per box drops as you order more — the full ladder is on each listing.

New to Japanese product? Read [what makes Japanese Pokémon cards different](guide:japanese-pokemon-cards).
MD,
    ],
    'etb' => [
      'label' => 'Elite Trainer Boxes', 'slug' => 'elite-trainer-boxes',
      'blurb' => 'Elite Trainer Boxes and cases, with packs, sleeves and accessories inside.',
      'h1' => 'Pokémon Elite Trainer Boxes',
      'seo_title' => 'Pokémon Elite Trainer Boxes: Perfect Order, Ascended Heroes',
      'seo_desc' => 'Sealed Pokémon TCG Elite Trainer Boxes, including Mega Evolution Perfect Order, Ascended Heroes, Chaos Rising and 30th Celebration, with bulk pricing by the case.',
      'intro' => <<<'MD'
## Elite Trainer Boxes

An Elite Trainer Box (ETB) bundles booster packs with card sleeves, dice, damage counters and a storage box, which makes it one of the most popular Pokémon gifts and a steady seller for card shops.

We carry ETBs across the Pokémon Trading Card Game Mega Evolution series — including [Perfect Order](product:perfect-order-elite-trainer-box), [Ascended Heroes](product:mega-evolution-ascended-heroes-elite-trainer-box), [Chaos Rising](product:chaos-rising-elite-trainer-box) and [Pitch Black](product:pitch-black-elite-trainer-box) — plus the [30th Celebration](set:30th-celebration) ETB and full cases.

ETBs are sold in multiples of four, or by the 10-box case, with lower prices from 24 boxes.
MD,
    ],
    'premium' => [
      'label' => 'Premium & Special Sets', 'slug' => 'premium-special-sets',
      'blurb' => 'Collection boxes, starter decks, premium sets and 30th Celebration specials.',
      'h1' => 'Pokémon collection boxes, starter sets & premium sets',
      'seo_title' => 'Pokémon Collection Boxes, Starter Sets & Premium Sets',
      'seo_desc' => 'Pokémon collection boxes, Starter Set ex decks, Premium Trainer Box MEGA and 30th Celebration special sets, shipped from Japan to the USA with bulk pricing.',
      'intro' => <<<'MD'
## Pokémon boxes beyond the booster box

Not every Pokémon box is a booster box. Collection boxes such as the [30th Celebration Greninja ex Box](product:30th-celebration-greninja-ex-box) and [Sylveon ex Box](product:30th-celebration-sylveon-ex-box) pair booster packs with a promo card, while [Starter Set ex](product:starter-set-ex-eevee-ex) decks and the [MEGA Start Deck 100 Battle Collection](product:mega-start-deck-100-battle-collection) give new players a ready-to-play deck.

For collectors, the [30th Celebration Premium Deck Set](product:30th-celebration-premium-deck-set-espeon-and-umbreon) marks Pokémon's 30th anniversary. Starter decks are one of the easiest ways to learn to play the Pokémon Trading Card Game — and a low-price add-on for card shops.
MD,
    ],
    'singles' => [
      'label' => 'Single Cards', 'slug' => 'single-cards',
      'blurb' => 'Rare Japanese singles — Special Illustration Rares, full art, gold and PSA graded cards.',
      'h1' => 'Rare Pokémon cards — Japanese singles',
      'seo_title' => 'Rare Pokémon Cards: Japanese Full Art & Gold Singles',
      'seo_desc' => 'Rare Japanese Pokémon cards: Special Illustration Rares, full art and gold Hyper Rares of Charizard, Pikachu, Gengar and Mega Rayquaza — Near Mint or PSA graded.',
      'intro' => <<<'MD'
## Rare and expensive Pokémon cards

Our single cards are the rare end of each set: Special Illustration Rares with full art, gold Hyper Rares and top-rarity Mega Evolution cards such as the [Mega Rayquaza ex Master Ultra Rare](product:mega-rayquaza-ex-mur). You'll find [Charizard cards](cards:charizard-pokemon-cards), [Pikachu cards](cards:pikachu-pokemon-cards), [Gengar](cards:gengar-pokemon-cards) and a [PSA 10 graded Pikachu](cards:psa-graded-pokemon-cards).

Raw cards are Near Mint and ship sleeved and toploaded. Not sure what the letters mean? Our guide to [Pokémon card rarities](guide:pokemon-card-rarities) explains SAR, SR, UR and more, and [how much Pokémon cards are worth](guide:pokemon-card-values) covers what drives prices.
MD,
    ],
    'accessories' => [
      'label' => 'Accessories', 'slug' => 'accessories',
      'blurb' => 'Card binders, sleeves, deck boxes, playmats and storage.',
      'h1' => 'Pokémon card binders, sleeves & deck boxes',
      'seo_title' => 'Pokémon Card Binders, Sleeves & Deck Boxes',
      'seo_desc' => 'Pokémon card binders, card sleeves, deck boxes, playmats and storage — including 9-pocket Mega Evolution binders and Ultra PRO Pikachu sleeves — with bulk pricing.',
      'intro' => <<<'MD'
## Pokémon card binders and sleeves

A good card binder and the right sleeves keep a collection in Near Mint condition. Our [9-pocket Pokémon card binder](product:pokemon-tcg-9-pocket-binder-mega-evolution-series) holds nine cards per page in Mega Evolution series artwork, and our Pokémon card sleeves range from [Ultra PRO Pikachu Deck Protectors](product:ultra-pro-pikachu-deck-protector-sleeves-65-ct) to character sleeves like [Celebi & Furret](product:celebi-and-furret-deck-sleeves) and [Mega Rayquaza](product:storm-emeralda-mega-rayquaza-deck-sleeves).

Pokémon cards are 63 × 88 mm, so they need standard-size sleeves — see our [Pokémon card size guide](guide:pokemon-card-size) before you buy. Accessories are sold in bulk packs for card shops, with lower prices at higher quantities.
MD,
    ],
  ],

  'series' => [
    'mega' => [
      'name' => 'Mega Evolution', 'slug' => 'mega-evolution',
      'h1' => 'Pokémon TCG Mega Evolution sets (Japanese)',
      'seo_title' => 'Pokémon TCG Mega Evolution Sets & Booster Packs',
      'seo_desc' => 'Japanese Pokémon TCG Mega Evolution sets — Mega Brave, Mega Symphonia, Inferno X, Mega Dream ex, Nihil Zero, Ninja Spinner, Abyss Eye, Storm Emeralda and more.',
      'intro' => <<<'MD'
Mega Evolution returned to the Pokémon Trading Card Game in 2025, launching in Japan with the twin sets [Mega Brave](set:mega-brave) and [Mega Symphonia](set:mega-symphonia). Each set since has brought new Mega Pokémon ex — from Mega Gengar ex in [Mega Dream ex](set:mega-dream-ex) to Mega Rayquaza ex in [Storm Emeralda](set:storm-emeralda).

Below is every Mega Evolution set we stock, with sealed booster boxes, Elite Trainer Boxes and the key single cards from each. Japanese sets release first, so Mega Evolution booster packs from Japan arrive well before their English versions.
MD,
    ],
    'sv' => [
      'name' => 'Scarlet & Violet', 'slug' => 'scarlet-violet',
      'h1' => 'Pokémon TCG Scarlet & Violet sets (Japanese)',
      'seo_title' => 'Pokémon TCG Scarlet & Violet Sets — Japanese Boxes',
      'seo_desc' => 'Japanese Pokémon TCG Scarlet & Violet sets: 151, Terastal Festival ex, Heat Wave Arena and Glory of Team Rocket booster boxes and singles, shipped to the USA.',
      'intro' => <<<'MD'
The Pokémon Trading Card Game Scarlet & Violet era ran from 2023 until the Mega Evolution series began in 2025, and produced some of the most collected Japanese sets ever — including [151](set:151), which revisits the original 151 Pokémon, and [Terastal Festival ex](set:terastal-festival-ex).

We still stock sealed Scarlet & Violet booster boxes from Japan, including [Heat Wave Arena](set:heat-wave-arena) and [Glory of Team Rocket](set:glory-of-team-rocket). As these sets go out of print, sealed boxes become harder to find.
MD,
    ],
  ],

  /* keyed by the set name used on products; series is a key of 'series' above */
  'sets' => [
    'Aura Seeker (Hadou Seeker)' => ['slug'=>'aura-seeker', 'series'=>'mega', 'code'=>'',
      'intro'=>'Aura Seeker (Hadou Seeker) is an upcoming Japanese Mega Evolution-series set. Preorder booster boxes now — preorders are invoiced when stock is allocated, not when you order.'],
    'Storm Emeralda' => ['slug'=>'storm-emeralda', 'series'=>'mega', 'code'=>'M6',
      'intro'=>'Storm Emeralda (M6) is the Japanese Mega Evolution expansion headlined by Mega Rayquaza ex. We stock sealed Storm Emeralda booster boxes, 12-box cases and Elite Trainer Boxes, the Mega Rayquaza ex Master Ultra Rare and Special Illustration Rare, and matching Mega Rayquaza sleeves.'],
    '30th Celebration' => ['slug'=>'30th-celebration', 'series'=>'mega', 'code'=>'M6a',
      'intro'=>"30th Celebration marks the Pokémon franchise's 30th anniversary in 2026. Mewtwo ex and Mew ex lead the set, joined by Umbreon ex, Salamence ex and Greninja ex, and every booster pack contains a Pikachu — with 30 different Pikachu rare cards to collect.\n\nThe range includes booster boxes, [Elite Trainer Boxes](product:30th-celebration-elite-trainer-box) and cases, the Greninja ex and Sylveon ex boxes, the Espeon & Umbreon Premium Deck Set and a Tech Sticker Collection."],
    'Abyss Eye' => ['slug'=>'abyss-eye', 'series'=>'mega', 'code'=>'M5',
      'intro'=>'Abyss Eye (M5) is the Japanese Mega Evolution set featuring Mega Darkrai ex and Mega Excadrill ex. We carry sealed Abyss Eye booster boxes and Elite Trainer Boxes, the Pitch Black Elite Trainer Box, and Mega Darkrai ex and Mega Excadrill ex singles.'],
    'Ninja Spinner' => ['slug'=>'ninja-spinner', 'series'=>'mega', 'code'=>'M4',
      'intro'=>'Ninja Spinner (M4) brings Mega Greninja ex and Mega Floette ex to the Mega Evolution series. Stock includes sealed Ninja Spinner booster boxes (limited), the Chaos Rising Elite Trainer Box, and Mega Greninja ex and Mega Floette ex singles.'],
    'Nihil Zero' => ['slug'=>'nihil-zero', 'series'=>'mega', 'code'=>'M3',
      'intro'=>'Nihil Zero (M3) is part of the Japanese Mega Evolution series. We stock sealed Nihil Zero booster boxes and the Perfect Order Elite Trainer Box.'],
    'Mega Dream ex' => ['slug'=>'mega-dream-ex', 'series'=>'mega', 'code'=>'M2a',
      'intro'=>'Mega Dream ex (M2a) is the special set of the Mega Evolution series and home of the Mega Gengar ex Special Illustration Rare. Stock includes sealed Mega Dream ex booster boxes, the Ascended Heroes Elite Trainer Box and the Mega Gengar ex SIR itself.'],
    'Inferno X' => ['slug'=>'inferno-x', 'series'=>'mega', 'code'=>'M2',
      'intro'=>'Inferno X (M2) is the second main Japanese set of the Mega Evolution series, available here as sealed booster boxes.'],
    'Mega Symphonia' => ['slug'=>'mega-symphonia', 'series'=>'mega', 'code'=>'M1S',
      'intro'=>'Mega Symphonia (M1S) is one of the twin sets that launched the Mega Evolution series in Japan in 2025, released alongside Mega Brave.'],
    'Mega Brave' => ['slug'=>'mega-brave', 'series'=>'mega', 'code'=>'M1L',
      'intro'=>'Mega Brave (M1L) launched the Japanese Mega Evolution series in 2025 together with its twin set, Mega Symphonia.'],
    'Glory of Team Rocket' => ['slug'=>'glory-of-team-rocket', 'series'=>'sv', 'code'=>'SV10',
      'intro'=>'Glory of Team Rocket (SV10) brings Team Rocket\'s Pokémon back to the Pokémon TCG and is one of the most in-demand Japanese Scarlet & Violet sets. Stocked as sealed booster boxes.'],
    'Heat Wave Arena' => ['slug'=>'heat-wave-arena', 'series'=>'sv', 'code'=>'SV9a',
      'intro'=>'Heat Wave Arena (SV9a) is a Japanese Scarlet & Violet expansion, stocked as sealed booster boxes.'],
    'Terastal Festival ex' => ['slug'=>'terastal-festival-ex', 'series'=>'sv', 'code'=>'SV8a',
      'intro'=>'Terastal Festival ex (SV8a) is the Scarlet & Violet special set built around Terastal Pokémon and the Eevee evolutions, released in English as Prismatic Evolutions. Japanese boxes hold 10 packs of 10 cards.'],
    '151' => ['slug'=>'151', 'series'=>'sv', 'code'=>'SV2a',
      'seo_title'=>'151 Pokémon Cards — Japanese 151 Booster Box & Charizard',
      'seo_desc'=>'Japanese 151 Pokémon cards (SV2a): sealed 151 booster boxes and the Charizard ex Special Illustration Rare, shipped from Japan to the USA.',
      'intro'=>"Pokémon Card 151 (SV2a) is the Japanese special set that revisits the original 151 Pokémon from Red and Green, from Bulbasaur to Mew. It is one of the most collected sets of the Scarlet & Violet era, released in English as Scarlet & Violet—151. Japanese 151 booster boxes hold 20 packs of 7 cards, and the chase cards include the Charizard ex Special Illustration Rare.\n\nWe stock sealed 151 booster boxes and the 151 Charizard ex SAR. Looking for more? See all our [Charizard Pokémon cards](cards:charizard-pokemon-cards)."],
  ],

  /* products are included if their id is listed, or their name contains a match term
     (comma-separated); cond limits to one condition, or lists every product in it when there are no terms */
  'collections' => [
    ['slug'=>'charizard-pokemon-cards', 'title'=>'Charizard Pokémon cards', 'h1'=>'Charizard Pokémon cards (Japanese)',
     'match'=>'charizard', 'ids'=>[], 'cond'=>'',
     'seo_title'=>'Charizard Pokémon Cards — Japanese SAR & Hyper Rare',
     'seo_desc'=>'Japanese Charizard Pokémon cards: the 151 Charizard ex Special Illustration Rare and Mega Charizard Y ex Hyper Rare, Near Mint and shipped from Japan to the USA.',
     'intro'=><<<'MD'
Charizard is one of the most collected Pokémon in the Trading Card Game, and its cards are often the most valuable in a set. Our Japanese Charizard cards include the Charizard ex Special Illustration Rare from [151](set:151) and the gold Mega Charizard Y ex Hyper Rare from the Mega Evolution series.

Every card is Near Mint and ships sleeved and toploaded. For why some Charizard cards are worth far more than others, see [how much Pokémon cards are worth](guide:pokemon-card-values).
MD],
    ['slug'=>'pikachu-pokemon-cards', 'title'=>'Pikachu Pokémon cards', 'h1'=>'Pikachu Pokémon cards',
     'match'=>'pikachu', 'ids'=>[], 'cond'=>'',
     'seo_title'=>'Pikachu Pokémon Cards — SAR, PSA 10 & Pikachu Sleeves',
     'seo_desc'=>'Pikachu Pokémon cards from Japan: Pikachu ex Special Illustration Rares including a PSA 10 Gem Mint copy, plus Pikachu sleeves and deck boxes.',
     'intro'=><<<'MD'
Pikachu is the face of Pokémon, and Pikachu cards sell to every kind of collector. We stock Pikachu ex Special Illustration Rares — including a [PSA 10 Gem Mint](cards:psa-graded-pokemon-cards) graded copy — plus Pikachu deck sleeves and an Ultra PRO Pikachu deck box.

Raw Pikachu cards ship sleeved and toploaded; graded cards ship in their PSA slab.
MD],
    ['slug'=>'gengar-pokemon-cards', 'title'=>'Gengar Pokémon cards', 'h1'=>'Gengar Pokémon cards',
     'match'=>'gengar', 'ids'=>['mega-dream-ex-m2a-booster-box'], 'cond'=>'',
     'seo_title'=>'Gengar Pokémon Card — Mega Gengar ex SIR (Japanese)',
     'seo_desc'=>'The Japanese Mega Gengar ex Special Illustration Rare from Mega Dream ex, plus sealed Mega Dream ex booster boxes to chase it yourself.',
     'intro'=><<<'MD'
Gengar has one of the most loyal followings in the Pokémon TCG, and the Mega Gengar ex Special Illustration Rare from [Mega Dream ex](set:mega-dream-ex) is one of the standout cards of the Mega Evolution series. Buy the card Near Mint, or open sealed Mega Dream ex booster boxes to chase it yourself.
MD],
    ['slug'=>'psa-graded-pokemon-cards', 'title'=>'PSA graded Pokémon cards', 'h1'=>'PSA graded Pokémon cards',
     'match'=>'', 'ids'=>[], 'cond'=>'Graded',
     'seo_title'=>'PSA Graded Pokémon Cards — PSA 10 Gem Mint',
     'seo_desc'=>'PSA graded Pokémon cards, including a PSA 10 Gem Mint Pikachu ex Special Illustration Rare, shipped in the original PSA slab from Japan to the USA.',
     'intro'=><<<'MD'
Graded Pokémon cards have been assessed and sealed in a tamper-evident case by a grading company. PSA grades on a 1–10 scale: PSA 10 (Gem Mint) is a virtually perfect card, PSA 9 is Mint and PSA 8 is Near Mint–Mint. Because a grade takes the guesswork out of condition, graded cards — especially PSA 10s — usually sell for a significant premium over raw copies.

Our graded Pokémon cards ship in their original PSA slab. For raw cards, see our [rare single cards](category:singles), and learn how grading affects price in [how much Pokémon cards are worth](guide:pokemon-card-values).
MD],
  ],

  'guides' => [
    ['slug'=>'japanese-pokemon-cards', 'updated'=>'2026-09-24',
     'title'=>'Japanese Pokémon cards: what\'s different and why collectors buy them',
     'seo_title'=>'Japanese Pokémon Cards: Differences, Sets & Where to Buy',
     'seo_desc'=>'Why collectors buy Japanese Pokémon cards: earlier releases, print quality, how Japanese booster boxes compare with English ones, and buying them in the USA.',
     'body'=><<<'MD'
Japanese Pokémon cards are the original version of every modern Pokémon Trading Card Game set. The same cards are later released in English, but many collectors — and plenty of players — prefer to buy Japanese. Here's what's different, and what to know before you buy.

## Japanese sets come out first

New Pokémon card sets are designed and released in Japan first. English versions usually follow months later, and are often built by combining or reworking Japanese sets, so set names don't always match. The Japanese [151](set:151) set became "Scarlet & Violet—151" in English, and [Terastal Festival ex](set:terastal-festival-ex) became "Prismatic Evolutions". If you want the newest cards as early as possible, Japanese is the way to get them.

## Same size, different packs

Japanese and English Pokémon cards are exactly the same size — 63 × 88 mm — so they fit the same [sleeves and binders](category:accessories). What differs is the packaging:

- Most Japanese main-set booster boxes hold 30 packs of 5 cards, compared with 36 packs of 10 cards in an English booster box.
- Japanese special sets vary: a 151 box holds 20 packs of 7 cards, and a Terastal Festival ex box holds 10 packs of 10.
- Japanese boxes cost less per box, so they are a popular way to open more sets on the same budget.

## Why collectors choose Japanese cards

Many collectors say Japanese cards have more consistent printing, cutting and centering, which matters when a card is sent for grading. Some cards and promos only ever appear in Japanese. And because Japanese sets release first, the newest Mega Evolution cards are often only available in Japanese for months.

## Can you play with Japanese cards?

Japanese cards work exactly like English ones in casual play — the attacks, HP and rules are the same, just written in Japanese. Official tournaments have their own rules on card language, so check with your organiser before bringing Japanese cards to an event.

## Buying Japanese Pokémon cards in the USA

We source every [booster box](category:boxes) and [single card](category:singles) in Japan and ship it to the USA with tracking. Shipping is calculated at checkout, orders start at {min_order} including shipping, and US import duty may be charged on delivery — see [shipping](page:shipping) for details.
MD],
    ['slug'=>'pokemon-card-size', 'updated'=>'2026-09-24',
     'title'=>'Pokémon card size: dimensions in mm and inches',
     'seo_title'=>'Pokémon Card Size & Dimensions (mm and inches)',
     'seo_desc'=>'Pokémon cards measure 63 × 88 mm (2.48 × 3.46 inches). The standard Pokémon card size, how Japanese cards compare, and which sleeves, toploaders and binders fit.',
     'body'=><<<'MD'
A standard Pokémon card measures **63 × 88 mm**, or about **2.48 × 3.46 inches** — usually rounded to 2.5 × 3.5 inches. That's the same size as most trading cards, including Magic: The Gathering, so Pokémon cards fit standard-size card accessories.

## Pokémon card dimensions at a glance

- Width: 63 mm (2.48 in)
- Height: 88 mm (3.46 in)
- Thickness: roughly 0.3 mm — a stack of about 30 cards is around 1 cm tall
- Corners: rounded

## Are Japanese Pokémon cards the same size?

Yes. Japanese Pokémon cards are the same 63 × 88 mm as English cards. This catches people out because Japanese Yu-Gi-Oh! cards are smaller, so sleeves sold as "Japanese size" (about 62 × 89 mm) are made for Yu-Gi-Oh! and are too narrow for Pokémon cards.

## Which sleeves fit Pokémon cards?

- **Standard sleeves** (about 66 × 91 mm) fit Pokémon cards for play and storage — for example [Ultra PRO Pikachu Deck Protectors](product:ultra-pro-pikachu-deck-protector-sleeves-65-ct) or our [assorted Pokémon card sleeves](product:pokemon-card-sleeves-64-ct-assorted-designs).
- **Perfect-fit or inner sleeves** (about 64 × 89 mm) go on first, under a standard sleeve, to double-sleeve valuable cards.
- **Toploaders** (3 × 4 inches) hold a sleeved card in a rigid case for shipping or storage — every rare single we sell ships toploaded.

## Binders and storage

A [9-pocket Pokémon card binder](product:pokemon-tcg-9-pocket-binder-mega-evolution-series) holds nine standard-size cards per page. Single-sleeved cards fit comfortably; double-sleeved cards can be tight in some binders. For bulk cards, a [card storage box](product:pokemon-tcg-card-storage-box-booster-box-display) keeps them upright and flat.

## Oversized and graded cards

Jumbo promo cards are much larger than standard cards and need their own oversized sleeves or binders. Graded cards sit in a slab from the grading company — see our [PSA graded Pokémon cards](cards:psa-graded-pokemon-cards).
MD],
    ['slug'=>'how-to-tell-if-a-pokemon-card-is-fake', 'updated'=>'2026-09-24',
     'title'=>'How to tell if a Pokémon card is fake',
     'seo_title'=>'How to Tell if a Pokémon Card Is Fake: 9 Checks',
     'seo_desc'=>'Fake Pokémon cards are common. Nine quick checks — text, colour, texture, the light test, edges and more — to spot fake or imitation Pokémon cards before you buy.',
     'body'=><<<'MD'
Fake Pokémon cards are everywhere, from obvious imitation packs to convincing copies of expensive cards sold online. Most fakes fail at least one of these checks — and the more expensive the card, the more of them you should run.

## 1. Compare it with a real card

The fastest check is a side-by-side comparison with a genuine card from the same set, or with high-resolution photos from a trusted listing. Differences in colour, layout and text stand out immediately.

## 2. Check the text and spelling

Fakes often have spelling mistakes, the wrong font, uneven spacing or a missing accent on the "é" in Pokémon. Look closely at attack names, damage numbers and the small print along the bottom of the card.

## 3. Look at the colours

Real cards have crisp, balanced colours. Fake Pokémon cards are often washed out, too dark or oversaturated, and the card back may be the wrong shade.

## 4. Feel the surface

Modern rare cards — full arts, Special Illustration Rares and gold Hyper Rares — have a fine texture you can feel with a fingertip. Many fakes are completely smooth and glossy.

## 5. Check the holo

A genuine holo pattern is sharp and moves cleanly in the light. Fake holo is often blurry, covers the wrong part of the card, or puts a rainbow sheen over the whole surface.

## 6. Do the light test

Shine a phone torch through the card. Genuine Pokémon cards are printed on layered card stock with a dark inner layer, so only a little light gets through, while many fakes let far more through. Results vary between print eras, so compare against a real common card of the same age.

## 7. Look at the edges and corners

Real cards have clean, even cuts. Rough or fuzzy edges, badly rounded corners, or a card that is noticeably thinner, thicker or bendier than normal are warning signs.

## 8. Be wary of the numbers

HP and attack damage that don't make sense — like an attack doing 1,000 damage — are a giveaway. So are cards for Pokémon or rarities that don't exist in that set.

## 9. Buy sealed from a trusted source

The safest way to avoid fake and imitation Pokémon cards is to buy sealed product from a trusted seller. Check that booster boxes and packs are properly sealed, and treat prices far below market as a red flag.

Everything we sell is sourced through Japanese distribution and ships sealed as it left the factory — browse our [booster boxes](category:boxes), or see [graded cards](cards:psa-graded-pokemon-cards), which have been authenticated by the grading company.
MD],
    ['slug'=>'pokemon-card-rarities', 'updated'=>'2026-09-24',
     'title'=>'Pokémon card rarities explained: rare, full art and gold cards',
     'seo_title'=>'Pokémon Card Rarities: Rare, Full Art & Gold Cards',
     'seo_desc'=>'What makes a Pokémon card rare? Japanese rarity codes (AR, SR, SAR, UR) explained, plus full art and gold Pokémon cards and the rarest Pokémon cards.',
     'body'=><<<'MD'
Every Pokémon card has a rarity, printed in the bottom corner. On Japanese cards it's a letter code; on English cards it's a symbol or name. Here's how to read it — and which cards are the rare ones.

## Japanese rarity codes

- **C, U, R** — common, uncommon and rare: most of the cards in every pack.
- **RR** — Double Rare: Pokémon ex cards in regular art.
- **AR** — Art Rare: a Pokémon shown in a full illustration. Called Illustration Rare in English.
- **SR** — Super Rare: full art Pokémon ex and Trainer cards. Called Ultra Rare in English.
- **SAR** — Special Art Rare: full art Pokémon ex and Trainers with special illustrations, usually the chase cards of a set. Called Special Illustration Rare (SIR) in English.
- **UR** — Ultra Rare: gold cards with a textured gold finish. Called Hyper Rare in English.

The Mega Evolution series adds its own top-end rarities, such as the [Mega Rayquaza ex Master Ultra Rare](product:mega-rayquaza-ex-mur).

## What are full art Pokémon cards?

A full art Pokémon card has artwork that covers the whole card instead of sitting in a frame. SR, SAR and gold cards are all full art. Special Illustration Rares are the most sought-after full arts in modern sets because each one is a unique illustration — see our [rare single cards](category:singles).

## What are gold Pokémon cards?

Gold Pokémon cards — UR in Japanese, Hyper Rare in English — are printed with a gold, textured finish and are among the hardest cards to pull from a set. The [Mega Charizard Y ex Hyper Rare](product:mega-charizard-y-ex-hyper-rare) is one example. Gold metal "cards" sold online as novelties are not playable Pokémon TCG cards.

## What are the rarest Pokémon cards?

The rarest Pokémon cards were never sold in packs at all: prize cards from contests and tournaments, awarded to a handful of people. The best known is Pikachu Illustrator, a 1998 contest prize — see [how much Pokémon cards are worth](guide:pokemon-card-values). In modern sets the rarest cards are the SAR, gold and top-rarity cards, which can turn up less than once per booster box.

## Rarity isn't the whole story

Two cards of the same rarity can be worth very different amounts. The Pokémon on the card matters — [Charizard](cards:charizard-pokemon-cards), [Pikachu](cards:pikachu-pokemon-cards) and [Gengar](cards:gengar-pokemon-cards) are always in demand — and so do condition and grading.
MD],
    ['slug'=>'pokemon-card-values', 'updated'=>'2026-09-24',
     'title'=>'How much are Pokémon cards worth?',
     'seo_title'=>'How Much Are Pokémon Cards Worth? Card Values Explained',
     'seo_desc'=>'What makes a Pokémon card worth money: rarity, condition, PSA grading and language. How to check Pokémon card values, and the most expensive card sold.',
     'body'=><<<'MD'
Most Pokémon cards are worth very little — but the right card, in the right condition, can be worth hundreds or thousands of dollars. Here's what decides a Pokémon card's value, and how to check what yours is worth.

## What makes a Pokémon card worth money?

- **Rarity.** Special Illustration Rares, gold cards and top-rarity cards are printed in far smaller numbers than commons. See [Pokémon card rarities](guide:pokemon-card-rarities).
- **The Pokémon.** Fan favourites sell for more. [Charizard](cards:charizard-pokemon-cards) cards are often the most valuable in a set, followed by the likes of [Pikachu](cards:pikachu-pokemon-cards), Umbreon and [Gengar](cards:gengar-pokemon-cards).
- **Condition.** A card with whitened edges, scratches or creases is worth a fraction of a clean copy. Near Mint is the standard for rare cards.
- **Grading.** A card graded PSA 10 (Gem Mint) often sells for several times the price of an ungraded copy. See [PSA graded Pokémon cards](cards:psa-graded-pokemon-cards).
- **Set and age.** Popular and out-of-print sets hold their value, and sealed booster boxes of sought-after sets like [151](set:151) are collected in their own right.
- **Language.** Japanese and English versions of the same card are priced separately, and either can be worth more depending on the card.

## How to check Pokémon card values

The best guide to what a card is worth is what the same card has actually sold for recently. Search for the exact card — name, set number and language — and filter for sold listings on marketplaces, or use a price guide site. Always compare like for like: a raw card and a PSA 10 of the same card are different items, and so are Japanese and English versions.

## What is the most expensive Pokémon card?

The most famous is **Pikachu Illustrator**, a promo card awarded to winners of a 1998 illustration contest in Japan. Only a few dozen copies exist. In 2022 Logan Paul bought a PSA 10 copy for $5,275,000, which Guinness World Records recognised as the most expensive Pokémon card sold at the time. Other high-value cards include first edition Base Set Charizards in top grades and rare tournament prize cards.

## Are modern Pokémon cards worth money?

Modern cards can be valuable too. Chase cards from recent Japanese sets — like the [Mega Rayquaza ex Master Ultra Rare](product:mega-rayquaza-ex-mur) or the [Mega Gengar ex Special Illustration Rare](product:mega-gengar-ex-sir) — can sell for hundreds or even thousands of dollars soon after release. Our [rare single cards](category:singles) show current prices on every listing, with lower prices when you buy more.
MD],
  ],

  /* About, Returns, Privacy and Terms. Shown at /{slug} (or index.php?p=page&pg={slug}) and linked in the footer.
     Extra placeholders here: {brand} {company} {address} {email}. Review the policies for your own business. */
  'pages' => [
    ['slug'=>'about', 'title'=>'About FUDAKURA',
     'seo_title'=>'About FUDAKURA — Japanese Pokémon Cards from Japan',
     'seo_desc'=>'FUDAKURA sources Japanese Pokémon cards in Japan and ships them sealed to collectors, resellers and card shops in the USA. How we work and who we are.',
     'body'=><<<'MD'
FUDAKURA takes its name from two Japanese words: **fuda** (札), a card, and **kura** (蔵), a storehouse — a card storehouse. We source Japanese Pokémon cards in Japan and ship them to collectors, resellers and card shops in the USA and worldwide.

## What we sell

Sealed Japanese [booster boxes](category:boxes), [Elite Trainer Boxes](category:etb), [collection boxes and starter sets](category:premium), [rare single cards](category:singles) and [accessories](category:accessories) — from the current [Mega Evolution sets](set:mega-evolution) back to [Scarlet & Violet](set:scarlet-violet) favourites like [151](set:151).

## How we work

- **Sourced in Japan.** Everything is bought through Japanese distribution and ships sealed in its original factory packaging. We don't sell resealed, reprinted or fake product.
- **Prices in the open.** Every listing shows its full quantity-break ladder, so you can price an order before you contact us.
- **Careful packing.** Singles ship sleeved and toploaded; sealed product ships as it left the factory, with tracking on every parcel.
- **Straight answers.** Questions go to a real person at [{email}](mailto:{email}).

## Company details

{company} · {address} · [{email}](mailto:{email})
MD],
    ['slug'=>'privacy-policy', 'title'=>'Privacy policy',
     'seo_title'=>'Privacy Policy',
     'seo_desc'=>'What information FUDAKURA collects when you order, how it is used, and how to ask us to see, correct or delete it.',
     'body'=><<<'MD'
This policy explains what information {brand} collects and how it is used.

## What we collect

- **Order details** you enter at checkout: name, company, email, phone, shipping address, notes and your chosen payment method.
- **Technical details:** your IP address is stored with each order to help prevent fraud and spam.
- **Cookies:** one session cookie keeps your order basket and currency choice while you browse. We don't use advertising cookies.
- **Live chat:** our chat window is provided by [tawk.to](https://www.tawk.to/privacy-policy/). It sets its own cookies and shows us the page you're on, your approximate location and your browser while you're on the site, so we can help. Messages you send in chat are stored by tawk.to.
- **Bitcoin payments:** we store the transaction ID and amount of your payment with your order. Bitcoin transactions are public on the Bitcoin blockchain, as with any Bitcoin payment.

## How we use it

We use your details only to process and deliver your order, send payment details and invoices, and answer your questions. Orders are emailed to us and stored on our server.

## Sharing

We share your name, address and phone number with the carrier delivering your order. To check Bitcoin payments, our server looks up our own wallet address on public block explorers (mempool.space and blockstream.info); no personal details are sent. We don't sell your information or share it for marketing.

## How long we keep it

We keep order records for as long as we need them for accounting and legal obligations.

## Your choices

You can ask for a copy of the information we hold about you, or ask us to correct or delete it, by emailing [{email}](mailto:{email}).

## Contact

{company}, {address} — [{email}](mailto:{email})
MD],
    ['slug'=>'terms', 'title'=>'Terms of sale',
     'seo_title'=>'Terms of Sale',
     'seo_desc'=>'Terms for ordering Japanese Pokémon cards from FUDAKURA: prices, minimum order, payment, preorders, shipping, duties and authenticity.',
     'body'=><<<'MD'
These terms apply to orders placed on {brand}.

## Prices and currency

Prices are set in US dollars. Prices shown in other currencies are converted at our current rates for guidance, and your invoice is issued in the currency you selected at checkout.

## Orders

Placing an order reserves stock for {hold_hours} hours while we send payment details, and an order is confirmed once payment clears. Orders must total at least {min_order} including shipping.

## Payment

**Bitcoin** is paid from your order page, straight after you order, to the wallet address shown there. The BTC amount is fixed for a limited time and renewed at the current rate if it runs out before you pay; send the exact amount shown. For **other methods**, we send payment details within {reply_hours} hours: always quote your order reference, and check payment details against our email from {email}. We never ask for card details, passwords or wallet keys.

## Preorders

Preorders are invoiced when stock is allocated, not when you order.

## Shipping and duties

Orders ship from Japan with tracking, and shipping is calculated at checkout. Orders over {free_ship} (goods total) ship free with Standard delivery. Import duty, taxes and carrier fees charged on delivery are the buyer's responsibility. See [Shipping & Returns](page:shipping).

## Damage and shortages

Report damage, shortages or wrong items within seven days of delivery. Returns, refunds and cancellations follow our [Shipping & Returns policy](page:shipping#returns).

## Authenticity and trademarks

All product is genuine and sourced through Japanese distribution. {company} is an independent reseller and is not affiliated with, endorsed by or licensed by The Pokémon Company, Nintendo, Creatures Inc. or GAME FREAK Inc.
MD],
  ],

  'faqs' => [
    ['Do you ship Japanese Pokémon cards to the USA?', 'Yes. Everything ships from Japan to the USA with tracking. Choose Standard delivery (3–6 working days) or Express (1–2 working days) at checkout; shipping is priced by the weight of your order, and orders start at {min_order} including shipping.'],
    ['Do I need an account to see pricing?', 'No. Every product shows its full quantity-break ladder publicly, so you can price a complete order before contacting anyone. There is no application form and no approval wait.'],
    ['What is the minimum order?', 'Every order must total at least {min_order} including shipping. Sealed product is sold in cases (usually multiples of four or six); single cards start at one.'],
    ['Are Japanese Pokémon cards the same size as English cards?', 'Yes. Both are 63 × 88 mm (about 2.5 × 3.5 inches), so they fit standard sleeves, toploaders and binders.'],
    ['How do I pay?', 'Choose a method at checkout. Bitcoin is paid on your order page straight after you order: scan the QR code, or copy the amount and address into your wallet. For other methods, we send the details to your email and phone within {reply_hours} hours, together with your invoice.'],
    ['Do you offer free shipping?', 'Yes. Orders with a goods total over {free_ship} ship free with Standard delivery to every country we ship to, applied automatically at checkout. Choose Express and you pay only the difference.'],
    ['When is my stock allocated?', 'Placing an order reserves your stock for {hold_hours} hours. Once payment clears, the allocation is confirmed and we dispatch within {hold_hours} hours. If payment does not clear inside the window, high-demand stock returns to general availability.'],
    ['Will I pay import duty in the USA?', 'Possibly. US imports of any value can be charged import duty and carrier fees, which your carrier collects on delivery. These are not included in our prices.'],
    ['Can I preorder an upcoming set?', 'Yes. Preorder lines commit an allocation ahead of release at the same published prices, and are invoiced at allocation rather than at request.'],
    ['Are your Pokémon cards authentic?', 'Yes. Everything is sourced through Japanese distribution and ships sealed in its original factory packaging. We do not deal in resealed, reprinted or fake product.'],
    ['What if something arrives damaged or short?', 'Report transit damage, a short shipment or a wrong item within seven days of delivery, with photos, and we replace, credit or refund the affected items and their shipping. Our Shipping & Returns page has the details.'],
  ],
];
