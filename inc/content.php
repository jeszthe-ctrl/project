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
    'strip_text'    => 'Authentic Japanese Pokémon TCG · Wholesale MOQs & case pricing · Shipped worldwide from Japan',
    'hero_title'    => 'Wholesale Japanese Pokémon cards, shipped worldwide from Japan.',
    'hero_lede'     => 'Authentic sealed booster boxes, Elite Trainer Boxes, premium sets, singles and TCG accessories for card shops, online retailers, tournament organizers and distributors. No approval process: minimum order quantities, case multiples and quantity-break prices are built into every listing.',
    'footer_blurb'  => 'Independent distributor and reseller of authentic Japanese Pokémon TCG products, shipping wholesale orders worldwide from Japan to card shops, online retailers, tournament organizers and distributors.',
    'home_seo_title'=> 'Japanese Pokémon Cards Wholesale: Booster Boxes & Singles',
    'home_seo_desc' => 'Wholesale Japanese Pokémon cards from Japan: sealed booster boxes, ETBs, premium sets and singles for card shops and retailers, with MOQ and case pricing.',
    'home_intro'    => <<<'MD'
## Why buy Japanese Pokémon cards?

Japanese sets come out first, often months before their English versions, so Japanese booster boxes are how collectors get the newest cards early. Many collectors also prefer Japanese printing and card quality, and Japanese special sets like [151](set:151) and [Terastal Festival ex](set:terastal-festival-ex) are some of the most sought-after boxes in the hobby.

Everything we sell is sourced in Japan and shipped worldwide, including to the USA, sealed exactly as it left the factory. Browse [booster boxes](category:boxes), [rare single cards](category:singles), [PSA graded cards](cards:psa-graded-pokemon-cards) or [card binders and sleeves](category:accessories), or start with our guide to [Japanese Pokémon cards](guide:japanese-pokemon-cards).

## Wholesale Japanese Pokémon cards

{brand} is an independent distributor and reseller of Japanese Pokémon TCG products. There's no approval-gated registration: every listing shows its minimum order quantity, case multiple and full quantity-break ladder, so card shops, online retailers, tournament organizers and distributors can price a complete order up front. Orders start at {min_order} including shipping. See our [wholesale terms](page:wholesale).
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
      'h1' => 'Pokémon Trading Card Game Mega Evolution sets & booster packs',
      'seo_title' => 'Pokémon TCG Mega Evolution Sets & Booster Packs',
      'seo_desc' => 'Japanese Pokémon TCG Mega Evolution sets — Mega Brave, Mega Symphonia, Inferno X, Mega Dream ex, Nihil Zero, Ninja Spinner, Abyss Eye, Storm Emeralda and more.',
      'intro' => <<<'MD'
Mega Evolution returned to the Pokémon Trading Card Game in 2025, launching in Japan with the twin sets [Mega Brave](set:mega-brave) and [Mega Symphonia](set:mega-symphonia). Each set since has brought new Mega Pokémon ex — from Mega Gengar ex in [Mega Dream ex](set:mega-dream-ex) to Mega Rayquaza ex in [Storm Emeralda](set:storm-emeralda).

Below is every Mega Evolution set we stock, with sealed booster boxes, Elite Trainer Boxes and the key single cards from each. Japanese sets release first, so Mega Evolution booster packs from Japan arrive well before their English versions.
MD,
    ],
    'sv' => [
      'name' => 'Scarlet & Violet', 'slug' => 'scarlet-violet',
      'h1' => 'Pokémon Trading Card Game: Scarlet & Violet sets',
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
      'intro'=>"Pokémon Card 151 (SV2a) is the Japanese special set that revisits the original 151 Pokémon from Red and Green, from Bulbasaur to Mew. It is one of the most collected sets of the Scarlet & Violet era, released in English as Scarlet & Violet—151. Japanese 151 booster boxes hold 20 packs of 7 cards, and the chase cards include the Charizard ex Special Illustration Rare.\n\n## Pokémon 151 card list\n\nJapanese 151 (released on 16 June 2023) has 210 cards: a 165-card main set covering the original 151 Pokémon plus Trainer and Energy cards, and 45 secret rares: 18 Art Rares (AR), 16 Super Rares (SR), 8 Special Art Rares (SAR) and 3 gold Ultra Rares (UR). More in our [Pokémon card database](guide:pokemon-card-database).\n\nWe stock sealed 151 booster boxes and the 151 Charizard ex SAR. Looking for more 151 Pokémon cards? See all our [Charizard Pokémon cards](cards:charizard-pokemon-cards)."],
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

New Pokémon card sets are designed and released in Japan first. English versions usually follow months later, and are often built by combining or reworking Japanese sets, so set names don't always match. The Japanese [151](set:151) set became "Scarlet & Violet—151" in English, and [Terastal Festival ex](set:terastal-festival-ex) became "Prismatic Evolutions". If you want the newest cards as early as possible, Japanese is the way to get them. Every Japanese set we stock, with its set code, is listed in our [Pokémon card database](guide:pokemon-card-database).

## Same size, different packs

Japanese and English Pokémon cards are exactly the same size — 63 × 88 mm — so they fit the same [sleeves and binders](category:accessories). What differs is the packaging:

- Most Japanese main-set booster boxes hold 30 packs of 5 cards, compared with 36 packs of 10 cards in an English booster box.
- Japanese special sets vary: a 151 box holds 20 packs of 7 cards, and a Terastal Festival ex box holds 10 packs of 10.
- Japanese boxes cost less per box, so they are a popular way to open more sets on the same budget.

## Why collectors choose Japanese cards

Many collectors say Japanese cards have more consistent printing, cutting and centering, which matters when a card is sent for grading. Some cards and promos only ever appear in Japanese. And because Japanese sets release first, the newest Mega Evolution cards are often only available in Japanese for months.

## Can you play with Japanese cards?

Japanese cards work exactly like English ones in casual play — the attacks, HP and rules are the same, just written in Japanese. Official tournaments have their own rules on card language, so check with your organiser before bringing Japanese cards to an event. Our guides to [how to read a Pokémon card](guide:how-to-read-a-pokemon-card) and [how to play Pokémon cards](guide:how-to-play-pokemon-cards) work for both languages.

## Buying Japanese Pokémon cards in the USA

US stores such as Walmart and Target sell English cards ([where to buy Pokémon cards](guide:where-to-buy-pokemon-cards)), so Japanese cards mostly come from importers. Official Chinese-language cards are a different product again: see [Chinese Pokémon cards](guide:chinese-pokemon-cards).

We source every [booster box](category:boxes) and [single card](category:singles) in Japan and ship it to the USA with tracking. Shipping is calculated at checkout and free on orders over {free_ship}, orders start at {min_order} including shipping, and US import duty may be charged on delivery — see [Shipping & Returns](page:shipping) for details.
MD],
    ['slug'=>'most-expensive-pokemon-cards', 'updated'=>'2026-09-25',
     'title'=>'The most expensive Pokémon cards ever sold',
     'seo_title'=>'Most Expensive Pokémon Card Ever Sold: Top Cards (2026)',
     'seo_desc'=>'What is the most expensive Pokémon card? A PSA 10 Pikachu Illustrator sold for $16.49M in 2026. The record sales, first edition cards and why they cost so much.',
     'body'=><<<'MD'
The most expensive Pokémon card ever sold is a **PSA 10 Pikachu Illustrator**. It sold at Goldin Auctions in February 2026 for **$16,492,000**, one of the highest prices ever paid for any trading card. Here's what makes it, and the other most expensive Pokémon cards, worth so much.

## What is the most expensive Pokémon card?

The Pikachu Illustrator. It was never sold in packs: it was a prize for winners of illustration contests run by the Japanese magazine CoroCoro Comic in 1997–98, and only 39 copies were awarded. Only one has ever been graded PSA 10 (Gem Mint), and that single card holds the record.

## The Pokémon Illustrator card (Pikachu Illustrator)

The card's artwork is by Atsuko Nishida, the artist who drew the original Pikachu, and it's the only Pokémon card that says "Illustrator" where other cards say "Trainer" — a nod to the contest winners it was made for. It has never been reprinted.

## Logan Paul's Pokémon card

The record copy belonged to YouTuber and wrestler Logan Paul. He bought it privately for **$5,275,000**, which Guinness World Records recognised as the most expensive Pokémon card sold in a private sale, and wore it to WrestleMania 38 in 2022. He sold it through Goldin in February 2026 for $16.49 million, roughly three times what he paid.

## The most expensive Pokémon cards sold

- **Pikachu Illustrator, PSA 10:** $16,492,000 at Goldin, February 2026.
- **Pikachu Illustrator, PSA 10:** $5,275,000 in a private sale to Logan Paul.
- **1st Edition Base Set Charizard, PSA 10:** $420,000 at PWCC in March 2022; another copy was reported sold for $550,000 at Heritage Auctions in late 2025.
- **Trophy cards** from the first official tournaments, such as the Pikachu No. 3 Trainer card from Japan's first official Pokémon card tournament in June 1997. Only a handful exist, and they rarely come up for sale.

Prices for cards this rare only move when a copy is sold, so records change every few years rather than every season.

## First edition Pokémon cards (1st Edition)

First edition cards come from the first print run of the early English sets, marked with a small black **Edition 1** stamp just below the left corner of the artwork. The 1999 Base Set comes in three main prints:

- **1st Edition:** stamped, with no shadow along the right side of the art box. Worth the most.
- **Shadowless:** no stamp and no shadow. Worth the most after 1st Edition.
- **Unlimited:** the later print, with a shadow on the art box.

The holo rares, above all Charizard, Blastoise and Venusaur, carry most of the value. Condition decides the rest: a PSA 10 can be worth many times a PSA 8 of the same card. First edition stamps are also faked, so buy graded copies from reputable sellers ([how to tell if a Pokémon card is fake](guide:how-to-tell-if-a-pokemon-card-is-fake)).

## Why are some Pokémon cards so expensive?

- **Rarity:** contest and tournament prizes were made in tiny numbers ([the rarest Pokémon cards](guide:rarest-pokemon-cards)).
- **Condition:** top grades are scarce for older cards.
- **The Pokémon:** Pikachu and Charizard lead almost every price list.
- **History:** the first print runs of the original sets carry decades of nostalgia.

## Expensive modern Pokémon cards

You don't need a 1990s card for a valuable collection. Modern chase cards — Special Illustration Rares, gold Hyper Rares and the Mega Evolution series' top rarities — can sell for hundreds or thousands of dollars in top condition. Examples we stock include the [151 Charizard ex Special Illustration Rare](product:151-charizard-ex-special-illustration-rare), the [Mega Rayquaza ex Master Ultra Rare](product:mega-rayquaza-ex-mur) and a [PSA 10 Pikachu ex Special Illustration Rare](product:pikachu-ex-sar-240-191-psa-10-gem-mint). See all [rare Pokémon cards](category:singles), check prices with our [Pokémon card price checker](guide:pokemon-card-price-checker), or read [how much Pokémon cards are worth](guide:pokemon-card-values).

## Questions

### What is the most expensive Pokémon card?

A PSA 10 Pikachu Illustrator, which sold for $16,492,000 at Goldin Auctions in February 2026.

### How much did Logan Paul pay for his Pokémon card?

$5,275,000, for the PSA 10 Pikachu Illustrator. He sold it in February 2026 for $16.49 million.

### Are first edition Pokémon cards worth money?

Yes, especially the holo rares from the 1999 Base Set in high grades. Condition and authenticity decide the price, so graded copies are the safest to buy.

### What is the most expensive Charizard card?

A 1st Edition Base Set holo Charizard graded PSA 10, which sold for $420,000 in 2022; a copy was reported sold for $550,000 in late 2025.
MD],
    ['slug'=>'rarest-pokemon-cards', 'updated'=>'2026-09-25',
     'title'=>'The rarest Pokémon cards, and the gold cards collectors chase',
     'seo_title'=>'Rarest Pokémon Cards & Gold Pokémon Cards Explained',
     'seo_desc'=>'What is the rarest Pokémon card? From the 39-copy Pikachu Illustrator to trophy cards and Gold Stars, plus modern gold Pokémon cards and how rare they are.',
     'body'=><<<'MD'
Rare Pokémon cards come in two kinds: cards that were made in tiny numbers, and cards that are hard to pull from a pack. The first kind are the rarest Pokémon cards in the world. The second kind, including today's gold cards, are the chase cards of every new set.

## What is the rarest Pokémon card?

The **Pikachu Illustrator**. Only 39 were awarded, as prizes in CoroCoro Comic illustration contests in Japan in 1997–98, and it has never been reprinted. It's also the most expensive: a PSA 10 copy sold for $16.49 million in 2026 ([the most expensive Pokémon cards](guide:most-expensive-pokemon-cards)).

## The rarest Pokémon cards ever made

- **Trophy cards.** Prize cards from early official tournaments, such as the Pikachu "No. 1", "No. 2" and "No. 3 Trainer" cards, were given to a handful of winners each.
- **Contest and event promos.** Cards handed out at one-off events in the late 1990s and 2000s, mostly in Japan, in very small numbers.
- **Gold Star cards.** Printed from 2004 to 2007, marked with a gold star after the Pokémon's name. Most sets had just one to three, and they were very hard to pull.
- **First edition holos.** 1999 Base Set holo rares with the 1st Edition stamp, especially in top grades.
- **Crystal and Shining cards.** Rare holo variants from the e-Card and Neo eras.

Because so few of these exist, their prices only appear when a copy is sold.

## Gold Pokémon cards

"Gold Pokémon cards" can mean three different things:

- **Gold rare cards in modern sets.** In today's sets the hardest pull is often a gold card, with gold borders and a textured foil finish. It's called a **Hyper Rare** in English sets and **UR** in Japanese sets. The [Mega Charizard Y ex Hyper Rare](product:mega-charizard-y-ex-hyper-rare) is one example.
- **Gold Star cards** from 2004–2007 (above).
- **Gold-plated cards** (below).

Modern gold cards are hard to pull, but they're printed for every copy of a set, so they're far easier to find than a Gold Star or a trophy card.

### Golden Pokémon cards (gold-plated)

In 1999 Burger King gave away 23-karat gold-plated metal Pokémon cards, each in a Poké Ball, with kids' meals. They're collectible souvenirs rather than playable cards. Gold metal "cards" sold online today are novelties, not official Pokémon TCG cards.

## How rare are modern chase cards?

In Japanese sets, the rarest cards are marked **SAR** (Special Art Rare) and **UR** (gold), and the Mega Evolution series adds its own top rarities, like the [Mega Rayquaza ex Master Ultra Rare](product:mega-rayquaza-ex-mur). These can turn up less than once per booster box, and a sealed box never guarantees a particular card. Read [Pokémon card rarities explained](guide:pokemon-card-rarities) for every symbol, or shop [rare Pokémon cards](category:singles) if you'd rather buy the exact card you want.

## Questions

### What is the rarest Pokémon card?

The Pikachu Illustrator: only 39 were ever awarded, in Japan in 1997–98.

### Are gold Pokémon cards rare?

Modern gold cards (Hyper Rares, or UR in Japanese sets) are among the hardest pulls in a set. Vintage Gold Star cards from 2004–2007 are much rarer.

### What are gold Pokémon cards worth?

It depends on the card, the set and the condition. Modern gold cards range from a few dollars to hundreds, popular Pokémon in top grades sell for more, and Gold Star cards can reach thousands.
MD],
    ['slug'=>'pokemon-card-price-checker', 'updated'=>'2026-09-25',
     'title'=>'Pokémon card price checker: live Japanese Pokémon card prices',
     'seo_title'=>'Pokémon Card Price Checker: Japanese Card & Box Prices',
     'seo_desc'=>'Check Pokémon card prices: live prices for Japanese booster boxes, Elite Trainer Boxes and rare singles, and how to price check any Pokémon card in 2026.',
     'body'=><<<'MD'
Type a set, product or card below to check our live prices for Japanese Pokémon booster boxes, Elite Trainer Boxes, rare singles and accessories. Each price is what you pay per unit at checkout, with quantity breaks.

## Check Pokémon card prices

{price_list}

## How to price check any Pokémon card

For a card we don't list, check what it has actually **sold** for, not what sellers are asking:

1. **Identify the card exactly:** its name, set, card number (in the bottom corner) and language. Japanese and English prints of the same card are priced separately.
2. **Check recent sold prices** on a marketplace: sold listings on eBay, or TCGplayer's market price for English cards.
3. **Match the condition.** Near Mint, played and graded copies sell for very different amounts, and a PSA 10 can be worth several times an ungraded card ([what grading costs](guide:how-much-does-it-cost-to-grade-a-pokemon-card)).
4. **Scan it** with a [Pokémon card scanner](guide:pokemon-card-scanner) app for a quick first estimate.

## Pokémon card prices: what moves them

Pokémon card prices follow demand for the Pokémon (Charizard, Pikachu and Umbreon lead), the rarity, the condition and supply. Prices often dip just after a set releases, while boxes are easy to find, and rise once a set stops being printed. Read [Pokémon card values](guide:pokemon-card-values) for the full picture.

## A Pokémon price checker for sealed product

Sealed booster boxes are priced per box, and we publish every quantity break: the **Best price** column shows the unit price at our largest break. Switch currency at the top of any page, and see the [shipping rates](page:shipping) for delivery to your country.

## Questions

### How do I check how much my Pokémon card is worth?

Find the exact card (set, number and language), then look at what it has recently sold for in the same condition. A scanner app gives a quick estimate.

### Are these prices for Japanese or English cards?

Our prices are for the Japanese products we sell, shipped from Japan.

### Why is the same card cheaper in quantity?

Our sealed product is sold by the case, and every listing shows its quantity breaks, so the unit price drops as you buy more.
MD],
    ['slug'=>'pokemon-card-database', 'updated'=>'2026-09-25',
     'title'=>'Pokémon card database: Japanese sets, set codes and card lists',
     'seo_title'=>'Pokémon Card Database: Japanese Sets & Card Lists (2026)',
     'seo_desc'=>'A Pokémon card database of Japanese sets: Mega Evolution and Scarlet & Violet set codes, the 151 card list, and the Perfect Order and Ascended Heroes sets.',
     'body'=><<<'MD'
This is our database of the Japanese Pokémon Trading Card Game sets we carry, with their set codes, what's in them and the English sets they match.

## Japanese Pokémon card sets

{set_table}

Each set page lists its booster boxes, Elite Trainer Boxes and singles with live prices.

## Pokémon database or Pokémon DB?

If you're looking up a Pokémon itself — its Pokédex entry, types, moves and stats — you want a Pokémon games database, such as the official Pokédex on pokemon.com or the fan site Pokémon DB. For the cards (which set a card is from, how rare it is and what it's worth) you're in the right place: a card database is organised by set, card number and rarity.

## Pokémon Mega Evolution card list

The **Mega Evolution** series began in 2025 and brings back Mega Evolution as **Mega Evolution Pokémon ex**: when one is Knocked Out, the opponent takes 3 Prize cards. The Japanese sets, in order: [Mega Brave](set:mega-brave) (M1L) and [Mega Symphonia](set:mega-symphonia) (M1S), [Inferno X](set:inferno-x) (M2), [Mega Dream ex](set:mega-dream-ex) (M2a), [Nihil Zero](set:nihil-zero) (M3), [Ninja Spinner](set:ninja-spinner) (M4), [Abyss Eye](set:abyss-eye) (M5), [Storm Emeralda](set:storm-emeralda) (M6), [30th Celebration](set:30th-celebration) (M6a) and [Aura Seeker](set:aura-seeker). See the whole [Mega Evolution series](set:mega-evolution).

## Pokémon Trading Card Game Mega Evolution: Perfect Order card list

**Mega Evolution—Perfect Order** is the English expansion released on 27 March 2026. It's themed on Pokémon Legends: Z-A, with **Mega Zygarde ex** as its headline card. Its Japanese counterpart in our range is [Nihil Zero](set:nihil-zero), and we stock the [Perfect Order Elite Trainer Box](product:perfect-order-elite-trainer-box).

## Pokémon Trading Card Game Mega Evolution: Ascended Heroes card list

**Mega Evolution—Ascended Heroes** was released in English on 30 January 2026 with more than 290 cards, including over 30 Trainer cards. Its Japanese counterpart is the special set [Mega Dream ex](set:mega-dream-ex) (M2a), home of the [Mega Gengar ex Special Illustration Rare](product:mega-gengar-ex-sir). We stock the [Ascended Heroes Elite Trainer Box](product:mega-evolution-ascended-heroes-elite-trainer-box).

## Pokémon 151 card list

Japanese **Pokémon Card 151 (SV2a)**, released on 16 June 2023, has **210 cards**: a 165-card main set covering the original 151 Pokémon plus Trainer and Energy cards, and 45 secret rares: 18 Art Rares (AR), 16 Super Rares (SR), 8 Special Art Rares (SAR) and 3 gold Ultra Rares (UR). The star card is the [Charizard ex Special Art Rare](product:151-charizard-ex-special-illustration-rare). See the [151 set](set:151) and the [151 booster box](product:151-booster-box).

## Pokémon Trading Card Game: Scarlet & Violet

The **Scarlet & Violet** series ran from 2023 to 2025. It introduced Pokémon ex (with a lowercase "ex"), Tera Pokémon ex, and the Illustration Rare and Special Illustration Rare rarities. Japanese Scarlet & Violet sets we stock include [151](set:151) (SV2a), [Terastal Festival ex](set:terastal-festival-ex) (SV8a), [Heat Wave Arena](set:heat-wave-arena) (SV9a) and [Glory of Team Rocket](set:glory-of-team-rocket) (SV10). See the whole [Scarlet & Violet series](set:scarlet-violet).

## Using a trading card database

A good trading card database answers three questions: what's in a set, how rare each card is, and what it's worth. Use the set pages for the first, our [rarity guide](guide:pokemon-card-rarities) for the second, and the [Pokémon card price checker](guide:pokemon-card-price-checker) for the third.

## Questions

### How many cards are in Pokémon 151?

The Japanese 151 set (SV2a) has 210 cards: 165 in the main set and 45 secret rares.

### When was Perfect Order released?

Mega Evolution—Perfect Order was released in English on 27 March 2026.

### How many cards are in Ascended Heroes?

More than 290, including over 30 Trainer cards. It was released in English on 30 January 2026.
MD],
    ['slug'=>'where-to-buy-pokemon-cards', 'updated'=>'2026-09-25',
     'title'=>'Where to buy Pokémon cards: Walmart, Target, Costco, GameStop and more',
     'seo_title'=>'Where to Buy Pokémon Cards: Walmart, Target & GameStop',
     'seo_desc'=>'Pokémon cards at Walmart, Target, Costco, GameStop, Best Buy, CVS, Walgreens, Dollar General, Barnes & Noble and TCGplayer, and buying Japanese cards online.',
     'body'=><<<'MD'
Pokémon cards are sold almost everywhere in the USA: big-box stores, pharmacies, game stores and online marketplaces. Here's what each kind of store usually carries, how restocks work, and when it's better to buy online.

{brand} is an independent distributor and online shop, and isn't affiliated with any retailer on this page. Store names are used only to describe where Pokémon cards are sold, and are trademarks of their owners.

## Walmart Pokémon cards

Walmart sells English Pokémon TCG products, from booster packs, blister packs and tins to collection boxes and Elite Trainer Boxes, in the trading card section of most stores and on its website. Online, check who the seller is: many listings come from third-party Marketplace sellers, often above retail price, so look for items sold and shipped by Walmart itself.

## Target Pokémon cards

Target carries English Pokémon cards in stores, often in the trading card aisle near electronics, and online. New sets and popular products sell out quickly and can come with purchase limits.

## When does Target restock Pokémon cards?

Target doesn't publish a restock schedule. Trading cards in stores are usually stocked by third-party vendors on a weekly route, so every store has its own restock day. The most reliable tip is to ask staff which day the card vendor comes in. Online restocks appear without notice and sell out fast.

## Costco Pokémon cards

Costco sells Pokémon cards mostly as bundles, with several collection boxes, tins or Elite Trainer Boxes packed together, online and in some warehouses from time to time. Stock comes and goes, and you need a membership to buy.

## GameStop Pokémon cards

GameStop sells sealed Pokémon products in stores and online, including preorders for new sets.

## GameStop Pokémon drops

A "drop" is a limited release that goes live online at a set time: a new set, a special collection or an exclusive. Drops often sell out in minutes, so follow the store's announcements and be signed in with your payment details saved before it starts.

## Best Buy Pokémon cards

Best Buy sells Pokémon TCG products online, often as preorders and drops for popular releases.

## Walgreens, CVS and Dollar General Pokémon cards

Pharmacies and discount stores such as **Walgreens**, **CVS** and **Dollar General** usually carry a small range of single booster packs, blister packs and mini tins, near the checkout or in the toy aisle. They're handy for a pack or two, but stock varies a lot from store to store.

## Barnes and Noble Pokémon cards

Barnes & Noble carries Pokémon TCG products, such as booster bundles, collection boxes and Elite Trainer Boxes, in many stores and online, next to its Pokémon books.

## TCGplayer Pokémon cards

TCGplayer is an online marketplace where many sellers list Pokémon singles and sealed product, with market prices based on recent sales. It's a good place to find a specific English single: check each seller's rating and shipping cost.

## Buying Japanese Pokémon cards online

Every store above sells **English** cards. Japanese sets come out first, often months before their English versions, and many collectors prefer their print quality ([why collectors buy Japanese Pokémon cards](guide:japanese-pokemon-cards)). We ship sealed Japanese [booster boxes](category:boxes), [Elite Trainer Boxes](category:etb) and [rare singles](category:singles) from Japan with tracking, publish every quantity break on the listing, and ship free on orders over {free_ship}. Compare prices with our [Pokémon card price checker](guide:pokemon-card-price-checker).

## Questions

### Does Walmart sell Pokémon cards?

Yes, in most stores and online. Online, check whether the seller is Walmart or a third-party Marketplace seller.

### When does Target restock Pokémon cards?

There's no published schedule. Vendors restock each store on their own weekly route, so ask your store which day. Online restocks are unannounced.

### Does Costco sell Pokémon cards?

Sometimes, usually as multi-product bundles online and in some warehouses.

### Where can I buy Japanese Pokémon cards in the USA?

From shops that import them from Japan. We ship Japanese booster boxes, Elite Trainer Boxes and singles from Japan to the USA with tracking.
MD],
    ['slug'=>'pokemon-card-shops-near-me', 'updated'=>'2026-09-25',
     'title'=>'Pokémon card shops near me: local stores, card shows and buying online',
     'seo_title'=>'Pokémon Card Shops Near Me: Stores, Card Shows & Online',
     'seo_desc'=>'How to find Pokémon card shops and trading card stores near you, what to expect at Pokémon card shows, and how to shop Japanese Pokémon cards online instead.',
     'body'=><<<'MD'
Looking for a Pokémon card shop near you? Here's how to find local trading card stores and card shows, what to look for when you get there, and when ordering online is the better option. {brand} is an online distributor, so we don't have a store to visit, but we deliver to every US state and worldwide.

## How to find Pokémon card shops near me

- **Search a map app** for "trading card store", "card shop" or "game store" and read recent reviews.
- **Use the Play! Pokémon event locator** on pokemon.com. Stores that run official Pokémon TCG leagues and prereleases are listed there, and nearly all of them are card shops.
- **Ask local collectors** in community groups on Facebook or Discord; they'll know which shops are fairly priced.
- **Ask at a card show**, where local shop owners often have tables.

## Trading card shops near me: what they sell

Local trading card shops, often called local game stores, usually sell sealed English booster packs and boxes at or near retail price, keep a case of singles, stock sleeves and binders, and run weekly play events. Many also buy cards, which makes them a good place to trade in duplicates.

## Trading card stores near me: what to check

- **Sealed product** should be factory sealed, with no loose or resealed wrapping.
- **Prices for singles** should be close to recent sold prices; check with a [price checker](guide:pokemon-card-price-checker) or a [scanner app](guide:pokemon-card-scanner).
- **Graded cards** should have an intact slab and a certification number you can look up on the grading company's website.

## Trading card store near me or online?

A local trading card store is great for playing, trading and seeing cards in person. Online shops usually have a far bigger range, especially of Japanese product, which few local stores carry, and deliver to your door.

## Card shops: what to expect

Card shops range from small counters in a game store to large stores with hundreds of graded cards. Expect to pay around retail for new English product, and expect more choice (and more negotiating) on singles.

## Pokémon card shows near me

Card shows are events where many sellers set up tables to buy, sell and trade cards, usually for a day or a weekend, in convention centres, hotel ballrooms or community halls. To find Pokémon card shows near you, check local card shops and collector groups, and search for trading card shows in your city on event listing sites and social media.

## Pokémon card shows: tips

- **Know prices before you go:** a scanner app helps you check a card on the spot.
- **Bring cash and a card;** many sellers take both.
- **Inspect before you buy,** especially graded cards and expensive singles.
- **Negotiate politely,** and bundle several cards for a better price.

## Shop Pokémon cards near me, delivered from Japan

If there's no card shop near you, or you want Japanese cards, shop online. We ship sealed Japanese [booster boxes](category:boxes), [Elite Trainer Boxes](category:etb), [rare singles](category:singles) and [accessories](category:accessories) from Japan to the USA with tracking: {standard} delivery in {standard_days}, or {express} in {express_days}, free on orders over {free_ship}. See [Shipping & Returns](page:shipping).

## Questions

### How do I find a Pokémon card shop near me?

Search a map app for trading card or game stores, and check the Play! Pokémon event locator on pokemon.com for stores that run official events.

### Do you have a store I can visit?

No. {brand} is online only. We ship from Japan to every US state, and worldwide, with tracking.

### How do I find Pokémon card shows near me?

Ask local card shops and collector groups, and search event listings and social media for trading card shows in your area.
MD],
    ['slug'=>'pokemon-card-scanner', 'updated'=>'2026-09-25',
     'title'=>'Pokémon card scanner: how to scan and price your cards',
     'seo_title'=>'Pokémon Card Scanner Apps: Scan & Price Your Cards (2026)',
     'seo_desc'=>'How a Pokémon card scanner works, the apps collectors use, like TCGplayer and Collectr, how accurate they are, and how to scan Japanese Pokémon cards.',
     'body'=><<<'MD'
A Pokémon card scanner is a phone app that recognises a card from your camera and shows its name, set and market price in seconds. It's the fastest way to sort a pile of cards and find the valuable ones.

## How a Pokémon card scanner works

You point your phone's camera at a card. The app matches the image against its card database, identifies the exact card and set, then shows a price based on recent sales. Most apps let you add scanned cards to a collection that tracks its total value over time.

## Pokémon card scanner apps

- **TCGplayer app:** scans cards and shows TCGplayer market prices, and lets you buy and sell on its marketplace.
- **Collectr:** built around tracking a collection's value, with card scanning.
- **Newer scanner apps** compete on speed, and some can scan a whole binder page at once.

Accuracy varies by app, set and language, so it's worth trying two and comparing.

## Pokémon card scanner tips for accurate scans

- Scan in bright, even light, without glare on holo or foil cards.
- Lay the card flat on a plain, dark surface.
- Take the card out of a shiny toploader, or tilt it to avoid reflections.
- Scan one card at a time, then check the set number the app found against the card.

## Scanning Japanese Pokémon cards

Not every app recognises Japanese cards, and some match them to the English version, which can be priced very differently. Check that the result shows the Japanese set code and card number. For sealed Japanese product, use our [Pokémon card price checker](guide:pokemon-card-price-checker).

## What a scan can't tell you

A scanner identifies the card, but it can't judge condition or spot a fake. Check the card yourself ([how to tell if a Pokémon card is fake](guide:how-to-tell-if-a-pokemon-card-is-fake)), and remember that a graded card is priced by its grade ([what grading costs](guide:how-much-does-it-cost-to-grade-a-pokemon-card)).

## Questions

### What is the best Pokémon card scanner app?

The TCGplayer app is the most widely used, and Collectr is popular for tracking a collection's value. Try more than one, because accuracy varies.

### Are Pokémon card scanner apps free?

Most are free to download and scan with, and some charge for extra features.

### Can a scanner tell if a Pokémon card is fake?

No. A scanner identifies which card it is, not whether it's genuine or what condition it's in.
MD],
    ['slug'=>'pokemon-card-template', 'updated'=>'2026-09-25',
     'title'=>'Pokémon card template: make your own custom Pokémon cards',
     'seo_title'=>'Pokémon Card Template: Free Printable for Custom Cards',
     'seo_desc'=>'A free printable Pokémon card template at the exact card size (63 × 88 mm) with bleed and safe area, and how to design, print and sleeve custom Pokémon cards.',
     'body'=><<<'MD'
Making a custom Pokémon card, for a birthday, a school project or just for fun, starts with the right size. Our free template below is the exact size of a Pokémon card, so your custom cards fit standard sleeves, toploaders and binders.

## Free Pokémon card template

{card_template}

The template is blank, with no logos or artwork, so it works in any design app: import the SVG into Canva, Photoshop, Illustrator, Affinity or Inkscape and design on top of it.

## Pokémon card size for your template

- **Finished size:** 63 × 88 mm (2.5 × 3.5 inches).
- **Bleed:** add 3 mm on every side (69 × 94 mm) so the background runs past the cut line.
- **Safe area:** keep text at least 4 mm inside the edge.
- **Corners:** rounded, with a radius of about 3 mm.
- **Resolution:** design at 300 dpi, which is 744 × 1039 pixels, or 815 × 1110 pixels with bleed.

More detail in our guide to [Pokémon card size](guide:pokemon-card-size).

## How to make a custom Pokémon card

1. **Pick a Pokémon, or invent one,** and add its name and HP to the top bar.
2. **Add artwork** to the art window: your own drawing, a photo of your pet, or a picture you have the right to use.
3. **Write attacks** with an Energy cost and damage, then Weakness, Resistance and Retreat. See [how to read a Pokémon card](guide:how-to-read-a-pokemon-card) for where everything goes.
4. **Print at 100% (actual size)** on heavy card stock (300 gsm or more) or matte photo paper.
5. **Cut on the trim line,** round the corners with a corner punch, and slide the card into a [sleeve](product:pokemon-card-sleeves-64-ct-assorted-designs) with a real card behind it for stiffness.

## Custom Pokémon cards: the rules

- Custom cards are for fun: they can't be used at official Pokémon tournaments.
- Don't sell custom cards made with Pokémon names or artwork, or pass them off as real ones. That's counterfeiting ([how to spot a fake Pokémon card](guide:how-to-tell-if-a-pokemon-card-is-fake)).
- Only use artwork you made yourself or have permission to use.

## Questions

### What size is a Pokémon card template?

63 × 88 mm (2.5 × 3.5 inches), plus 3 mm of bleed on each side for printing: 69 × 94 mm.

### What paper should I print custom Pokémon cards on?

Heavy card stock (300 gsm or more) or matte photo paper. A real card behind it in the sleeve makes it feel like the real thing.

### Can I use custom Pokémon cards in a tournament?

No. Only official Pokémon cards can be used at Play! Pokémon events.
MD],
    ['slug'=>'how-to-play-pokemon-cards', 'updated'=>'2026-09-25',
     'title'=>'How to play Pokémon cards: the rules for beginners',
     'seo_title'=>'How to Play Pokémon Cards: Pokémon Card Game Rules',
     'seo_desc'=>'How to play the Pokémon card game step by step: decks, setup, taking a turn, attacking, Trainer cards, Prize cards and the three ways to win.',
     'body'=><<<'MD'
The Pokémon Trading Card Game is a game for two players. Each player uses a deck of 60 cards and tries to Knock Out the other player's Pokémon. Here's how to play Pokémon cards, from setting up to your first win.

## What you need to play

- **A 60-card deck for each player.** The easiest start is a ready-made deck, such as the [Eevee ex starter set](product:starter-set-ex-eevee-ex) or the [MEGA Start Deck 100](product:mega-start-deck-100-battle-collection).
- **Damage counters, a coin or dice, and a playmat.** [Elite Trainer Boxes](category:etb) include counters and dice, and our [playmats](product:pokemon-playmat-assorted-designs) mark out where everything goes.
- **Sleeves and a deck box** to keep your deck together: [deck protector sleeves](product:ultra-pro-pikachu-deck-protector-sleeves-65-ct) and a [deck box](product:pokemon-deck-box-assorted), or the [Pikachu Alcove Tower deck box](product:ultra-pro-pikachu-alcove-tower-deck-box).

## The three kinds of Pokémon cards

- **Pokémon:** Basic Pokémon go straight into play; Stage 1 and Stage 2 Pokémon evolve from them.
- **Energy:** attached to your Pokémon to pay for their attacks.
- **Trainer cards:** Items, Supporters, Stadiums and Pokémon Tools that help you (below).

## How to set up the Pokémon card game

1. Shuffle your deck and draw 7 cards.
2. Put one Basic Pokémon face down as your **Active Pokémon**, and up to 5 more Basic Pokémon face down on your **Bench**. No Basic Pokémon in your hand? Show it, shuffle it back and draw 7 again; your opponent may draw an extra card.
3. Put the top 6 cards of your deck aside, face down, as your **Prize cards**.
4. Flip a coin. The winner chooses who goes first. Then both players turn their Pokémon face up.

## Taking a turn

1. **Draw a card.**
2. **As often as you like:** put Basic Pokémon on your Bench, evolve your Pokémon, play Item cards and use Abilities. You can't evolve a Pokémon on the turn it was played, or on either player's first turn.
3. **Once per turn:** attach one Energy card, play one Supporter card, play one Stadium card, and retreat your Active Pokémon by discarding Energy equal to its Retreat Cost.
4. **Attack** to end your turn. The player who goes first can't attack, or play a Supporter, on their very first turn.

## Attacking and Knock Outs

Each attack shows the Energy it needs and the damage it does. Put damage counters on the Defending Pokémon: double the damage if it's Weak to your Pokémon's type, and less if it has Resistance. When a Pokémon's damage reaches its HP, it's **Knocked Out**. Its owner discards it and moves a Benched Pokémon into the Active spot, and you take a Prize card: one for most Pokémon, two for a Pokémon ex and three for a Mega Evolution Pokémon ex.

## Pokémon Trainer cards

Trainer cards are the tools of your deck:

- **Item:** play as many as you like in your turn, for example to search your deck or heal.
- **Supporter:** one per turn, usually with the strongest effects, like drawing several cards.
- **Stadium:** stays in play and affects both players until another Stadium replaces it.
- **Pokémon Tool:** attach one to a Pokémon for an ongoing effect.

## How to win a Pokémon card game

You win when:

- you take all 6 of your Prize cards;
- your opponent has no Pokémon left in play; or
- your opponent has no cards left to draw at the start of their turn.

## Special Conditions

Some attacks leave the Defending Pokémon **Asleep**, **Burned**, **Confused**, **Paralyzed** or **Poisoned**. Asleep and Paralyzed Pokémon can't attack or retreat; Burned and Poisoned Pokémon take damage between turns; a Confused Pokémon's attack may fail. Moving a Pokémon to the Bench removes them all.

## Where to play

Play at home with a friend, join a league at a local card shop ([Pokémon card shops near me](guide:pokemon-card-shops-near-me)), or practise with Pokémon TCG Live, the official app.

## Questions

### How many cards are in a Pokémon deck?

Exactly 60, with no more than 4 copies of any card with the same name, except basic Energy.

### How do you win at Pokémon cards?

Take all 6 of your Prize cards, Knock Out your opponent's last Pokémon in play, or leave them with no cards to draw at the start of their turn.

### Can I play with Japanese Pokémon cards?

At home, yes: Japanese and English cards follow the same rules and layout, and you can [read a Japanese card](guide:how-to-read-a-pokemon-card) by its numbers and symbols. Official tournaments have their own language rules.
MD],
    ['slug'=>'how-to-read-a-pokemon-card', 'updated'=>'2026-09-25',
     'title'=>'How to read a Pokémon card: every part explained',
     'seo_title'=>'How to Read a Pokémon Card: Every Symbol Explained',
     'seo_desc'=>'How to read a Pokémon card: name, HP, type, stage, attacks, Energy cost, Weakness, Resistance, Retreat, set symbol, card number, rarity and regulation mark.',
     'body'=><<<'MD'
Every Pokémon card follows the same layout, whether it's English or Japanese. Once you know where to look, you can read any card at a glance. Here's each part, from top to bottom.

## The top of the card

- **Stage:** Basic, Stage 1 or Stage 2. Evolved Pokémon also show the Pokémon they evolve from.
- **Name:** including any extra label, like "ex" or "Mega".
- **HP:** Hit Points, the damage the Pokémon can take before it's Knocked Out.
- **Type:** the coloured symbol next to the HP: Grass, Fire, Water, Lightning, Psychic, Fighting, Darkness, Metal, Dragon or Colorless.

## The artwork and the illustrator

The picture takes up the middle of the card. The illustrator's name is printed near the bottom, after "Illus." Many collectors follow favourite artists; see [cool Pokémon cards](guide:coolest-pokemon-cards).

## Abilities and attacks

- **Abilities** are marked "Ability" and can usually be used without attacking.
- **Attacks** show the Energy symbols needed on the left, the attack name, and the damage on the right. The text below explains any extra effect. A white star symbol means Colorless: any type of Energy can pay for it.

## Weakness, Resistance and Retreat Cost

Along the bottom of the text box:

- **Weakness:** a type that does double damage to this Pokémon.
- **Resistance:** a type that does less damage to it.
- **Retreat Cost:** how many Energy you discard to move it to the Bench.

## Rule boxes

Special Pokémon have a rule box at the bottom. For example, when a Pokémon ex is Knocked Out, the opponent takes 2 Prize cards, and 3 for a Mega Evolution Pokémon ex.

## The bottom of the card

- **Regulation mark:** a small letter in a box that shows which tournament formats the card can be played in.
- **Set symbol or set code:** which set the card is from. Japanese cards show a set code, such as SV2a for [151](set:151).
- **Card number:** such as 185/165. A number higher than the set total means a secret rare.
- **Rarity:** on English cards a symbol (a circle for common, a diamond for uncommon, stars for rare cards); on Japanese cards a letter code like C, U, R, RR, AR, SR, SAR or UR. See [Pokémon card rarities](guide:pokemon-card-rarities).

## Trainer and Energy cards

Trainer cards say which kind they are at the top: Item, Supporter, Stadium or Pokémon Tool, followed by their effect. Energy cards show their type. See [how to play Pokémon cards](guide:how-to-play-pokemon-cards) for how each is used.

## Reading Japanese Pokémon cards

Japanese cards use the same layout, numbers and symbols, so HP, damage, Energy costs and card numbers read exactly the same. Only the names and effect text are in Japanese. More in our guide to [Japanese Pokémon cards](guide:japanese-pokemon-cards).

## Questions

### What does HP mean on a Pokémon card?

Hit Points: how much damage the Pokémon can take before it's Knocked Out.

### What do the numbers at the bottom of a Pokémon card mean?

The card's number in its set, such as 185/165. A first number higher than the second means a secret rare.

### What is the letter at the bottom of a Pokémon card?

The regulation mark, which shows which tournament formats the card is legal in.
MD],
    ['slug'=>'how-much-does-it-cost-to-grade-a-pokemon-card', 'updated'=>'2026-09-25',
     'title'=>'How much does it cost to grade a Pokémon card?',
     'seo_title'=>'How Much Does It Cost to Grade a Pokémon Card? (2026)',
     'seo_desc'=>'What grading a Pokémon card costs in 2026 at PSA, CGC and Beckett, the hidden costs like shipping and insurance, and when grading a card is worth it.',
     'body'=><<<'MD'
Grading means sending a card to a company that checks it's genuine, grades its condition from 1 to 10 and seals it in a tamper-evident case, called a slab. Here's what it costs in 2026, and when it's worth paying.

## How much does it cost to grade a Pokémon card?

As of September 2026, the cheapest grading at the main companies starts at roughly **$15–25 per card**. Add shipping both ways and insurance, and a single card sent on its own costs more like **$30–60** in total. Faster service and higher-value cards cost more: express tiers run to hundreds of dollars per card.

Grading prices change often, so check the grading company's current price list before you send anything.

## PSA grading cost

PSA is the biggest grading company, and PSA 10s usually sell for the most. Its fees depend on the card's declared value and how fast you want it back. PSA's cheapest **Value** tiers (from about $20–25 a card) were paused on 2 June 2026 because of a backlog of millions of cards. Until they reopen, PSA submissions start at its more expensive **Regular** tier, well over $50 a card. Some tiers also need a paid Collectors Club membership.

## CGC and Beckett grading cost

- **CGC:** from about $15 a card on its slowest tier, with no membership needed.
- **Beckett (BGS):** from about $20 a card on its slowest tier. Its sub-grades for centering, corners, edges and surface appeal to many collectors.

## The hidden costs of grading

- **Insured shipping** to the grader and back, often $20 or more per order.
- **Card savers and packing** materials.
- **Upcharges** if the graded card turns out to be worth more than your tier's declared-value limit.
- **Waiting time:** from days on express tiers to months on bulk tiers.

## Is grading a Pokémon card worth it?

Grade a card when its graded value is clearly more than its ungraded value plus the cost:

- valuable cards in near-perfect condition, such as Special Illustration Rares, gold cards and vintage holos;
- cards you want authenticated and protected for resale.

Skip grading common cards, and cards with visible whitening, scratches or off-centre borders: a low grade can be worth less than the cost of grading.

## Graded Pokémon cards you can buy now

If you'd rather buy a card that's already graded, see our [PSA graded Pokémon cards](cards:psa-graded-pokemon-cards), such as this [PSA 10 Pikachu ex Special Illustration Rare](product:pikachu-ex-sar-240-191-psa-10-gem-mint).

## Questions

### How much does PSA charge to grade a card?

From about $20–25 per card on its cheapest Value tiers when they're open (they were paused in June 2026), and more for faster tiers and higher-value cards, plus shipping.

### What's the cheapest way to grade Pokémon cards?

Send several cards together on the slowest tier at a grader such as CGC or Beckett, so the shipping cost is shared.

### How long does grading take?

From around a week on express tiers to several months on bulk tiers.
MD],
    ['slug'=>'chinese-pokemon-cards', 'updated'=>'2026-09-25',
     'title'=>'Chinese Pokémon cards: official releases vs imitation cards',
     'seo_title'=>'Chinese Pokémon Cards: Official vs Imitation Explained',
     'seo_desc'=>'Are Chinese Pokémon cards real? Official Simplified and Traditional Chinese Pokémon cards explained, how they differ from Japanese cards, and imitation cards.',
     'body'=><<<'MD'
"Chinese Pokémon cards" can mean two very different things: **official** Pokémon cards printed in Chinese, and **imitation** cards made without permission. Here's how to tell them apart.

## Official Chinese Pokémon cards

There are two official Chinese versions of the Pokémon Trading Card Game:

- **Traditional Chinese,** for Taiwan and Hong Kong, printed since 2019. Its sets closely follow the Japanese releases, with the same structure and numbering.
- **Simplified Chinese,** for mainland China, announced in September 2022, with the first three sets released on 28 October 2022. These sets often combine cards from several Japanese expansions, with their own box sizes and pull rates.

Both are genuine, licensed Pokémon cards, collected for their unique products and exclusive cards.

## Imitation Pokémon cards

Imitation Pokémon cards are counterfeits. They're sold cheaply online and in bulk lots, and many come from unlicensed factories, which is why fake cards are often loosely called "Chinese cards". Signs of an imitation card:

- wrong fonts, spelling mistakes or odd wording;
- washed-out or oversaturated colours;
- flimsy or very glossy card stock, with no dark layer visible along the edge;
- HP or damage numbers that make no sense;
- a price far below market.

Read our full guide on [how to tell if a Pokémon card is fake](guide:how-to-tell-if-a-pokemon-card-is-fake).

## Chinese vs Japanese Pokémon cards

- Every set is released in Japan first.
- Traditional Chinese sets mirror the Japanese sets closely; Simplified Chinese sets are rearranged.
- Japanese cards are the most widely collected non-English Pokémon cards.

We sell [Japanese Pokémon cards](guide:japanese-pokemon-cards) bought through Japanese distribution and sealed in their original factory packaging: see our [booster boxes](category:boxes).

## Questions

### Are Chinese Pokémon cards real?

Official Simplified and Traditional Chinese cards are real. Cheap "Chinese" cards sold in bulk lots, with odd text or colours, are usually imitations.

### Are Chinese Pokémon cards worth anything?

Official ones are collectible, and some exclusive cards sell well. Imitation cards have no collector value.

### How can I tell if a Pokémon card is an imitation?

Compare it with a genuine card: check the text, colours, texture and edges, and shine a light through it. Our [fake card guide](guide:how-to-tell-if-a-pokemon-card-is-fake) covers nine checks.
MD],
    ['slug'=>'coolest-pokemon-cards', 'updated'=>'2026-09-25',
     'title'=>'Cool Pokémon cards: the best, coolest and cutest cards to collect',
     'seo_title'=>'Cool Pokémon Cards: Best, Coolest & Cutest Cards to Collect',
     'seo_desc'=>'The coolest Pokémon cards to collect: full art cards, Special Illustration Rares, Tag Team cards, gold cards and cute Pokémon card art, with Japanese picks.',
     'body'=><<<'MD'
What makes a Pokémon card cool is personal, but collectors agree on a few kinds of cards that look better, feel special and hold their value. Here are the best Pokémon cards to collect, and where to find them.

## The coolest Pokémon cards right now

- **Special Illustration Rares (SIR, or SAR in Japanese).** Full-card artwork that tells a small story, often showing the Pokémon in its habitat. They're the most wanted cards in modern sets: see the [Mega Gengar ex Special Illustration Rare](product:mega-gengar-ex-sir) or the [151 Charizard ex](product:151-charizard-ex-special-illustration-rare).
- **Gold cards.** Gold borders and textured foil, and often the hardest pull in a set ([gold Pokémon cards](guide:rarest-pokemon-cards)).
- **Mega Evolution Pokémon ex.** The new Mega Pokémon ex of the [Mega Evolution sets](set:mega-evolution), such as [Mega Greninja ex](product:mega-greninja-ex-japanese) and [Mega Darkrai ex](product:mega-darkrai-ex-japanese).

## Full art Pokémon cards

A full art card's artwork covers the whole card instead of sitting in a frame, usually with a textured surface you can feel. Full arts first appeared in the Black & White era in the early 2010s and have been in nearly every set since, as full art Pokémon, full art Trainers and alternate arts. Japanese full arts are prized for their print quality and colour.

## Tag Team Pokémon cards

Tag Team cards show two, and sometimes three, Pokémon together on one card: Pikachu & Zekrom-GX, Mewtwo & Mew-GX and Charizard & Braixen-GX are famous examples. They were printed in 2019, in the Sun & Moon era. In play they're powerful, and the opponent takes 3 Prize cards when one is Knocked Out. Their alternate-art versions are some of the best-loved cards of the era.

## Cute Pokémon cards

Cute cards are a collecting category of their own. Pikachu, Eevee and its evolutions, Jigglypuff, Snorlax and Psyduck appear on many of the most popular illustration rares. Japanese Art Rares (AR), which show a single Pokémon in a charming everyday scene, are an affordable way to build a cute binder. The [Terastal Festival ex](set:terastal-festival-ex) set, built around all the Eevee evolutions, is full of them, and our [Pikachu Pokémon cards](cards:pikachu-pokemon-cards) page collects Pikachu, including this [Pikachu ex Special Illustration Rare](product:pikachu-ex-special-illustration-rare-277-217).

## Pokémon pictures: the art on the cards

Every Pokémon card credits its illustrator near the bottom, and many collectors follow favourite artists the way others follow favourite Pokémon. If you're after Pokémon pictures to collect rather than just look at, Art Rares and Special Art Rares are the cards made for the artwork. Show them off in a [9-pocket binder](product:pokemon-tcg-9-pocket-binder-mega-evolution-series).

## Best Pokémon cards for new collectors

1. Pick a Pokémon or a set you love, rather than chasing prices.
2. Buy the chase card you want as a single: it's usually cheaper than opening packs for it.
3. Open a [booster box](category:boxes) for the fun of pulling cards.
4. Protect everything in [sleeves](product:pokemon-card-sleeves-64-ct-assorted-designs) and a binder.

## Questions

### What is the best Pokémon card?

By value, the Pikachu Illustrator, the most expensive Pokémon card ever sold. For collecting today, the Special Illustration Rares and gold cards of your favourite Pokémon are the best cards in each set.

### What are full art Pokémon cards?

Cards whose artwork covers the whole card, usually with a textured finish. They've been printed since the early 2010s.

### What are Tag Team Pokémon cards?

Cards showing two or three Pokémon together, printed in 2019. When one is Knocked Out, the opponent takes 3 Prize cards.
MD],
    ['slug'=>'mew-mewtwo-arceus-pokemon-cards', 'updated'=>'2026-09-25',
     'title'=>'Mew, Mewtwo and Arceus Pokémon cards worth collecting',
     'seo_title'=>'Mew, Mewtwo & Arceus Pokémon Cards: Best Cards to Collect',
     'seo_desc'=>'The Mew, Mewtwo and Arceus Pokémon cards collectors want, from Base Set Mewtwo and Ancient Mew to Arceus VSTAR and the new Mewtwo ex and Mew ex.',
     'body'=><<<'MD'
Mew, Mewtwo and Arceus are three of the most collected Legendary and Mythical Pokémon. Here are their best-known cards, and where to find the newest ones.

## Mewtwo Pokémon card

Mewtwo has been a chase card since the very first set:

- **Base Set Mewtwo (1999):** the original holo rare, card 10/102. 1st Edition copies in top grades are the most valuable.
- **Mewtwo & Mew-GX (2019):** a Tag Team card from Unified Minds, with a much-loved alternate art.
- **Mewtwo ex in 30th Celebration:** the Japanese 30th anniversary set (M6a) features Mewtwo ex alongside Mew ex. See the [30th Celebration set](set:30th-celebration) and its [booster box](product:30th-celebration-m6a-booster-box).

## Mew Pokémon card

Mew is the Mythical Pokémon Mewtwo was cloned from, and its cards are just as collected:

- **Ancient Mew:** a promo card given out with Pokémon: The Movie 2000, printed in an invented "ancient" script.
- **Mew ex in 151:** the [151 set](set:151) covers the original 151 Pokémon, ending with Mew, and its Mew ex cards are fan favourites.
- **Mew ex in 30th Celebration:** a new Mew ex for the 30th anniversary.

## Arceus Pokémon card

Arceus is the Mythical Pokémon said to have shaped the Pokémon universe. It was introduced in the Diamond & Pearl games:

- **Arceus LV.X (2009):** from the Platinum-era set Arceus, which printed many different Arceus cards that could be played together.
- **Arceus VSTAR (2022):** from Brilliant Stars (Star Birth in Japan), released around the time of the game Pokémon Legends: Arceus. Its gold version is a favourite.

## Buying Mew, Mewtwo and Arceus cards

Singles are the surest way to get a specific card; sealed boxes are the fun way to chase one. Browse our [rare Pokémon cards](category:singles) and [Pokémon card sets](page:sets), and check condition carefully before you buy: see [what grading costs](guide:how-much-does-it-cost-to-grade-a-pokemon-card).

## Questions

### What is the most valuable Mewtwo card?

Among regular cards, the 1st Edition Base Set holo Mewtwo in top grades. Early prize and promo Mewtwo cards made in small numbers are rarer still.

### What is Ancient Mew?

A promotional Mew card given out with Pokémon: The Movie 2000, printed with an invented ancient-looking script instead of normal text.

### Is Arceus Legendary or Mythical?

Mythical, like Mew.
MD],
    ['slug'=>'where-to-sell-pokemon-cards', 'updated'=>'2026-09-25',
     'title'=>'Where to sell Pokémon cards for the best price',
     'seo_title'=>'Where to Sell Pokémon Cards for the Best Price (2026)',
     'seo_desc'=>'Where to sell Pokémon cards: local card shops, card shows, eBay, TCGplayer, collector groups and auction houses, with the pros, cons and tips for each.',
     'body'=><<<'MD'
Where you sell Pokémon cards decides how much you get and how fast. Here are the main options, from quick cash to top prices.

## Before you sell: know what you have

- **Identify each card:** set, card number and language. A [scanner app](guide:pokemon-card-scanner) speeds this up.
- **Check what it has recently sold for,** in the same condition ([price checker tips](guide:pokemon-card-price-checker)).
- **Be honest about condition:** whitening, scratches and bends lower the price.
- **Consider grading** your best cards first ([what grading costs](guide:how-much-does-it-cost-to-grade-a-pokemon-card)).

## Local card shops

The fastest way to sell. A shop pays on the spot, in cash or store credit, but usually below market price, because it needs room to resell. Get offers from more than one shop, and ask whether store credit is worth more than cash. See [Pokémon card shops near me](guide:pokemon-card-shops-near-me).

## Card shows

Many buyers in one room, so you can compare offers table by table. Bring your cards sorted, know your prices, and expect to negotiate.

## eBay

The biggest audience of buyers. You set the price or run an auction, and eBay takes a fee from each sale. Take clear photos of the front and back, describe the condition honestly, and ship with tracking.

## TCGplayer

A marketplace built for trading cards, where you list singles against TCGplayer's market prices. You'll need a seller account, and fees apply to each sale.

## Collector groups

Facebook groups and Discord servers can mean lower fees and keen buyers, but more risk. Check the buyer's references, and use a payment method with protection.

## Auction houses for valuable cards

For high-value graded cards, auction houses such as Goldin and Heritage Auctions reach serious collectors, and set many of the hobby's record prices. They charge the seller a commission, and the buyer pays a premium on top.

## How to ship Pokémon cards you've sold

Put each card in a sleeve and a toploader, sandwich it between cardboard in a bubble mailer, and use tracked, insured shipping for anything valuable.

## Questions

### Where can I sell Pokémon cards for the most money?

Usually online (eBay or TCGplayer) or, for high-value graded cards, an auction house. Local shops pay less, but they pay at once.

### Do card shops buy Pokémon cards?

Many do, usually at a discount to market price, in cash or store credit.

### Should I grade my Pokémon cards before selling?

Only valuable cards in near-perfect condition, where a high grade will add more than the cost of grading.
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

Making your own card? Our free [Pokémon card template](guide:pokemon-card-template) is set up at exactly this size, with bleed and a safe area for printing.

## Are Japanese Pokémon cards the same size?

Yes. Japanese Pokémon cards are the same 63 × 88 mm as English cards. This catches people out because Japanese Yu-Gi-Oh! cards are smaller, so sleeves sold as "Japanese size" (about 62 × 89 mm) are made for Yu-Gi-Oh! and are too narrow for Pokémon cards.

## Which sleeves fit Pokémon cards?

- **Standard sleeves** (about 66 × 91 mm) fit Pokémon cards for play and storage — for example [Ultra PRO Pikachu Deck Protectors](product:ultra-pro-pikachu-deck-protector-sleeves-65-ct) or our [assorted Pokémon card sleeves](product:pokemon-card-sleeves-64-ct-assorted-designs).
- **Perfect-fit or inner sleeves** (about 64 × 89 mm) go on first, under a standard sleeve, to double-sleeve valuable cards.
- **Toploaders** (3 × 4 inches) hold a sleeved card in a rigid case for shipping or storage — every rare single we sell ships toploaded.

## Binders and storage

A [9-pocket Pokémon card binder](product:pokemon-tcg-9-pocket-binder-mega-evolution-series) holds nine standard-size cards per page. Single-sleeved cards fit comfortably; double-sleeved cards can be tight in some binders. For bulk cards, a [card storage box](product:pokemon-tcg-card-storage-box-booster-box-display) keeps them upright and flat.

## Oversized and graded cards

Jumbo promo cards are much larger than standard cards and need their own oversized sleeves or binders. Graded cards sit in a slab from the grading company — see our [PSA graded Pokémon cards](cards:psa-graded-pokemon-cards) and [how much it costs to grade a Pokémon card](guide:how-much-does-it-cost-to-grade-a-pokemon-card).
MD],
    ['slug'=>'how-to-tell-if-a-pokemon-card-is-fake', 'updated'=>'2026-09-24',
     'title'=>'How to tell if a Pokémon card is fake',
     'seo_title'=>'How to Tell if a Pokémon Card Is Fake: 9 Checks',
     'seo_desc'=>'Fake Pokémon cards are common. Nine quick checks — text, colour, texture, the light test, edges and more — to spot fake or imitation Pokémon cards before you buy.',
     'body'=><<<'MD'
Fake Pokémon cards are everywhere, from obvious imitation packs to convincing copies of expensive cards sold online. Most fakes fail at least one of these checks — and the more expensive the card, the more of them you should run.

## Imitation Pokémon cards

Imitation Pokémon cards are unlicensed copies. Some are sold openly as cheap bulk lots or novelty "gold" cards; others are made to pass as real cards and sold at real prices. Official cards printed in other languages, such as Chinese, are genuine, so don't confuse them with imitations: see [Chinese Pokémon cards](guide:chinese-pokemon-cards).

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

A [Pokémon card scanner](guide:pokemon-card-scanner) app can tell you which card you're holding, but not whether it's real. Graded cards have been checked by the grading company: see [how much it costs to grade a Pokémon card](guide:how-much-does-it-cost-to-grade-a-pokemon-card).

Everything we sell is sourced through Japanese distribution and ships sealed as it left the factory — browse our [booster boxes](category:boxes), see [where to buy Pokémon cards](guide:where-to-buy-pokemon-cards) safely, or see [graded cards](cards:psa-graded-pokemon-cards), which have been authenticated by the grading company.
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

A full art Pokémon card has artwork that covers the whole card instead of sitting in a frame. SR, SAR and gold cards are all full art. Special Illustration Rares are the most sought-after full arts in modern sets because each one is a unique illustration — see our [rare single cards](category:singles) and collectors' favourites in [cool Pokémon cards](guide:coolest-pokemon-cards).

## What are gold Pokémon cards?

Gold Pokémon cards — UR in Japanese, Hyper Rare in English — are printed with a gold, textured finish and are among the hardest cards to pull from a set. The [Mega Charizard Y ex Hyper Rare](product:mega-charizard-y-ex-hyper-rare) is one example. Gold metal "cards" sold online as novelties are not playable Pokémon TCG cards.

## What are the rarest Pokémon cards?

The rarest Pokémon cards were never sold in packs at all: prize cards from contests and tournaments, awarded to a handful of people. The best known is Pikachu Illustrator, a 1997–98 contest prize: see [the rarest Pokémon cards](guide:rarest-pokemon-cards). In modern sets the rarest cards are the SAR, gold and top-rarity cards, which can turn up less than once per booster box.

## Rarity isn't the whole story

Two cards of the same rarity can be worth very different amounts. The Pokémon on the card matters — [Charizard](cards:charizard-pokemon-cards), [Pikachu](cards:pikachu-pokemon-cards) and [Gengar](cards:gengar-pokemon-cards) are always in demand — and so do condition and grading. To see what cards sell for, use our [Pokémon card price checker](guide:pokemon-card-price-checker), or read about [the most expensive Pokémon cards](guide:most-expensive-pokemon-cards).
MD],
    ['slug'=>'pokemon-card-values', 'updated'=>'2026-09-24',
     'title'=>'Pokémon card values: how much are Pokémon cards worth?',
     'seo_title'=>'Pokémon Card Values: How Much Are Pokémon Cards Worth?',
     'seo_desc'=>'What makes a Pokémon card worth money: rarity, condition, PSA grading and language. How to check Pokémon card values, and the most expensive card sold.',
     'body'=><<<'MD'
Most Pokémon cards are worth very little — but the right card, in the right condition, can be worth hundreds or thousands of dollars. Here's what decides a Pokémon card's value, and how to check what yours is worth.

## Pokémon cards worth money: what makes a card valuable?

- **Rarity.** Special Illustration Rares, gold cards and top-rarity cards are printed in far smaller numbers than commons. See [Pokémon card rarities](guide:pokemon-card-rarities).
- **The Pokémon.** Fan favourites sell for more. [Charizard](cards:charizard-pokemon-cards) cards are often the most valuable in a set, followed by the likes of [Pikachu](cards:pikachu-pokemon-cards), Umbreon and [Gengar](cards:gengar-pokemon-cards).
- **Condition.** A card with whitened edges, scratches or creases is worth a fraction of a clean copy. Near Mint is the standard for rare cards.
- **Grading.** A card graded PSA 10 (Gem Mint) often sells for several times the price of an ungraded copy. See [PSA graded Pokémon cards](cards:psa-graded-pokemon-cards).
- **Set and age.** Popular and out-of-print sets hold their value, and sealed booster boxes of sought-after sets like [151](set:151) are collected in their own right.
- **Language.** Japanese and English versions of the same card are priced separately, and either can be worth more depending on the card.

## How much is a Pokémon Trading Card Game card worth?

As a rough guide: common and uncommon cards are usually worth a few cents; holo rares and regular Pokémon ex a few dollars; full arts and Illustration Rares a few dollars to tens of dollars; and Special Illustration Rares, gold cards and chase cards of popular Pokémon tens to hundreds of dollars, or more in a high grade. Vintage holos, first edition cards and graded gems can be worth thousands.

## How to check Pokémon card values

The best guide to what a card is worth is what the same card has actually sold for recently. Search for the exact card — name, set number and language — and filter for sold listings on marketplaces, or use a price guide site. Always compare like for like: a raw card and a PSA 10 of the same card are different items, and so are Japanese and English versions. A [Pokémon card scanner](guide:pokemon-card-scanner) app gives a quick first estimate.

## Pokémon card prices: price vs value

A card's price is what a seller asks; its value is what buyers actually pay. When you look up Pokémon card prices, compare recent sold prices for the same card, set, language and condition, and ignore unsold listings. For the Japanese booster boxes, Elite Trainer Boxes and singles we sell, our [Pokémon card price checker](guide:pokemon-card-price-checker) shows live prices with every quantity break.

## What is the most expensive Pokémon card?

**Pikachu Illustrator**, a prize card from Japanese illustration contests in 1997–98; only 39 were awarded. A PSA 10 copy sold for $16,492,000 at Goldin Auctions in February 2026. Its seller, Logan Paul, had bought it privately for $5,275,000. Other high-value cards include first edition Base Set Charizards in top grades and rare tournament prize cards. See [the most expensive Pokémon cards](guide:most-expensive-pokemon-cards) and [the rarest Pokémon cards](guide:rarest-pokemon-cards).

## Are modern Pokémon cards worth money?

Modern cards can be valuable too. Chase cards from recent Japanese sets — like the [Mega Rayquaza ex Master Ultra Rare](product:mega-rayquaza-ex-mur) or the [Mega Gengar ex Special Illustration Rare](product:mega-gengar-ex-sir) — can sell for hundreds or even thousands of dollars soon after release. Our [rare single cards](category:singles) show current prices on every listing, with lower prices when you buy more. Ready to sell some of yours? See [where to sell Pokémon cards](guide:where-to-sell-pokemon-cards) and [how much it costs to grade a Pokémon card](guide:how-much-does-it-cost-to-grade-a-pokemon-card).
MD],
  ],

  /* About, Returns, Privacy and Terms. Shown at /{slug} (or index.php?p=page&pg={slug}) and linked in the footer.
     Extra placeholders here: {brand} {company} {address} {email}. Review the policies for your own business. */
  'pages' => [
    ['slug'=>'about', 'title'=>'About {brand}',
     'seo_title'=>'About {brand} — Japanese Pokémon TCG Distributor',
     'seo_desc'=>'{brand} is an independent distributor and reseller of authentic Japanese Pokémon TCG products, shipping wholesale worldwide from Japan.',
     'body'=><<<'MD'
{brand} is an independent distributor and reseller of authentic Japanese Pokémon Trading Card Game products. We specialise in wholesale: sealed booster boxes, premium sets, Elite Trainer Boxes, singles and TCG accessories, shipped worldwide directly from Japan.

## Our name

{brand} ({kanji}) ends in **kura** (蔵), the Japanese word for a storehouse: a storehouse of Japanese trading cards.

## Who we work with

We supply local card shops, online retailers, tournament organizers and independent distributors, and collectors who buy by the box. See our [wholesale terms](page:wholesale).

## What we sell

- Sealed Japanese [booster boxes](category:boxes), from anniversary sets like [30th Celebration](set:30th-celebration) to favourites like [Pokémon Card 151](set:151)
- [Elite Trainer Boxes](category:etb) and [premium sets](category:premium)
- [High-value single cards](category:singles), including [PSA graded cards](cards:psa-graded-pokemon-cards)
- [TCG accessories](category:accessories): sleeves, binders, deck boxes and playmats

## How we work

- **Wholesale without the paperwork.** There's no approval-gated registration. Minimum order quantities, case multiples and tiered quantity-break pricing are built into every listing, and applied automatically.
- **Sourced in Japan.** Everything is bought through Japanese distribution and ships sealed in its original factory packaging. We never sell resealed, reprinted or fake product.
- **Shipped worldwide.** Every order ships from Japan with tracking; see [Shipping & Returns](page:shipping).
- **Straight answers.** Questions go to a real person at [{email}](mailto:{email}).

## Independent

{company} is an independent business. We're not affiliated with, endorsed by or licensed by The Pokémon Company, Nintendo, Creatures Inc. or GAME FREAK Inc.

## Company details

{company} · {address} · [{email}](mailto:{email})
MD],
    ['slug'=>'wholesale', 'title'=>'Wholesale Japanese Pokémon cards for shops and distributors',
     'seo_title'=>'Wholesale Japanese Pokémon Cards & TCG Distributor',
     'seo_desc'=>'Wholesale Japanese Pokémon TCG from Japan for card shops, online retailers, tournament organizers and distributors: MOQs, case pricing, no approval.',
     'body'=><<<'MD'
{brand} is an independent distributor and reseller of authentic Japanese Pokémon Trading Card Game products. We supply sealed booster boxes, Elite Trainer Boxes, premium sets, singles and TCG accessories to businesses worldwide, shipped directly from Japan.

## Wholesale without an application

Most distributors make you apply, wait for approval and then ask for a price list. We don't. Every listing shows its wholesale terms up front:

- **Minimum order quantities (MOQs):** sealed product starts at a case-friendly quantity, usually 4 or 6 boxes; single cards start at one.
- **Case multiples:** quantities go up in the product's selling step, so every order matches how the product is packed.
- **Automatic quantity breaks:** the unit price drops at each tier, such as 6+, 24+ or 36+, and your order applies it for you.
- **Minimum order:** {min_order} including shipping.

Check any price with our [Pokémon card price checker](guide:pokemon-card-price-checker).

## Who we supply

- **Local card shops:** sealed Japanese boxes for the shelf and for pack-opening events, plus singles for the display case.
- **Online retailers:** case quantities of new Japanese sets from release, with tracking on every consignment.
- **Tournament organizers:** booster boxes for prize support, plus Elite Trainer Boxes and accessories for events.
- **Independent distributors:** larger case orders at our best quantity-break prices.

## What we supply

- **Booster boxes** from the current [Mega Evolution](set:mega-evolution) series and [Scarlet & Violet](set:scarlet-violet) favourites, including anniversary sets like [30th Celebration](set:30th-celebration) and [Pokémon Card 151](set:151). See all [Japanese booster boxes](category:boxes).
- **[Elite Trainer Boxes](category:etb)** and **[premium sets](category:premium)**.
- **[High-value single cards](category:singles)**, including [PSA graded cards](cards:psa-graded-pokemon-cards).
- **[TCG accessories](category:accessories):** sleeves, binders, deck boxes, playmats and storage.

## Worldwide shipping from Japan

Orders ship directly from Japan with tracking: {standard} ({standard_days}) or {express} ({express_days}), priced by weight and shown at checkout. Orders over {free_ship} ship free with {standard}. Import duty and taxes are paid by the buyer. See [Shipping & Returns](page:shipping).

## Paying for wholesale orders

Pay by Bitcoin straight from your wallet on the order page, or choose another method and we send payment details with your invoice within {reply_hours} hours. See [payment methods](page:payment).

## Preorders and allocations

Upcoming Japanese sets can be preordered at published prices. Preorders are invoiced when stock is allocated, and ship as soon as it arrives.

## Authentic Japanese product

Everything is sourced through Japanese distribution and ships sealed in its original factory packaging. We never sell resealed, reprinted or counterfeit product ([how to tell if a Pokémon card is fake](guide:how-to-tell-if-a-pokemon-card-is-fake)).

## How to place a wholesale order

1. Add products to your order in the quantities you need: the unit price updates at each break.
2. Enter your company name and shipping address at checkout.
3. Pay by Bitcoin on the next page, or get payment details by email.
4. We dispatch within {hold_hours} hours of payment, from Japan, with tracking.

More detail in [how ordering works](page:how).

## Questions

### Do I need a wholesale account or approval?

No. Wholesale pricing is public on every listing, with no application or approval.

### What is the minimum wholesale order?

{min_order} including shipping. Each product also has its own minimum quantity and case multiple, shown on the listing.

### Do you ship wholesale orders worldwide?

Yes. We ship from Japan with tracking to {countries} countries; the rates are on [Shipping & Returns](page:shipping).

### Do you offer distributor pricing for larger volumes?

Quantity breaks apply automatically at every tier. For volumes beyond our largest tier, email [{email}](mailto:{email}).

### Are your Pokémon cards authentic?

Yes. Everything comes through Japanese distribution, factory sealed.
MD],
    ['slug'=>'privacy-policy', 'title'=>'Privacy policy',
     'seo_title'=>'Privacy Policy',
     'seo_desc'=>'What information {brand} collects when you order, how it is used, and how to ask us to see, correct or delete it.',
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
     'seo_desc'=>'Terms for ordering Japanese Pokémon cards from {brand}: prices, minimum order, payment, preorders, shipping, duties and authenticity.',
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

All product is genuine and sourced through Japanese distribution. {company} is an independent distributor and reseller and is not affiliated with, endorsed by or licensed by The Pokémon Company, Nintendo, Creatures Inc. or GAME FREAK Inc.
MD],
  ],

  'faqs' => [
    ['Do you ship Japanese Pokémon cards to the USA?', 'Yes. Everything ships from Japan to the USA with tracking. Choose Standard delivery (3–6 working days) or Express (1–2 working days) at checkout; shipping is priced by the weight of your order, and orders start at {min_order} including shipping.'],
    ['Do I need a wholesale account to see pricing?', 'No. Every product shows its minimum order quantity, case multiple and full quantity-break ladder publicly, so you can price a complete order before contacting anyone. There is no application form and no approval wait.'],
    ['Who do you sell to?', 'Local card shops, online retailers, tournament organizers and independent distributors, as well as collectors who buy by the box. Wholesale terms are on every listing, with no registration needed.'],
    ['Do you ship worldwide?', 'Yes. Every order ships from Japan with tracking to {countries} countries, by Standard or Express delivery, priced by weight and shown at checkout.'],
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
