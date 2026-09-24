<?php
/* Starting SEO content for a fresh install: category, series, set and collection pages, guides,
   home page text and FAQ. Copied into data/store.php on first load and edited in admin.php after.

   Formatting (intros and guides): blank line = new paragraph, "## " heading, "- " bullet, **bold**,
   [text](link). Links can be full URLs or shop pages: product:ID, category:KEY, set:SLUG (sets and
   series), cards:SLUG (collections), guide:SLUG, page:shop|sets|guides|faq|shipping|payment|how|contact.
   {min_order} {reply_hours} {hold_hours} {countries} are filled in with current values. */
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
      'seo_title' => 'Pokémon Elite Trainer Boxes: Perfect Order & Ascended Heroes',
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
      'intro'=>'30th Celebration marks the Pokémon franchise\'s 30th anniversary in 2026, with reprinted classic illustrations and a range of anniversary products: booster boxes, Elite Trainer Boxes and cases, the Greninja ex and Sylveon ex boxes, the Espeon & Umbreon Premium Deck Set and a Tech Sticker Collection.'],
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
     'seo_desc'=>'What makes a Pokémon card worth money — rarity, the Pokémon, condition, PSA grading and language — how to check Pokémon card values, and the most expensive card sold.',
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

  'faqs' => [
    ['Do you ship Japanese Pokémon cards to the USA?', 'Yes. Everything ships from Japan to the USA with tracking by EMS, DHL or FedEx, typically in 3–6 working days. Shipping is calculated at checkout, and orders start at {min_order} including shipping.'],
    ['Do I need an account to see pricing?', 'No. Every product shows its full quantity-break ladder publicly, so you can price a complete order before contacting anyone. There is no application form and no approval wait.'],
    ['What is the minimum order?', 'Every order must total at least {min_order} including shipping. Sealed product is sold in cases (usually multiples of four or six); single cards start at one.'],
    ['Are Japanese Pokémon cards the same size as English cards?', 'Yes. Both are 63 × 88 mm (about 2.5 × 3.5 inches), so they fit standard sleeves, toploaders and binders.'],
    ['How do I pay?', 'Select a method at checkout. We send the details for your chosen method to your email and phone within {reply_hours} hours, together with your invoice.'],
    ['When is my stock allocated?', 'Placing an order reserves your stock for {hold_hours} hours. Once payment clears, the allocation is confirmed and we dispatch within {hold_hours} hours. If payment does not clear inside the window, high-demand stock returns to general availability.'],
    ['Will I pay import duty in the USA?', 'Possibly. US imports of any value can be charged import duty and carrier fees, which your carrier collects on delivery. These are not included in our prices.'],
    ['Can I preorder an upcoming set?', 'Yes. Preorder lines commit an allocation ahead of release at the same published prices, and are invoiced at allocation rather than at request.'],
    ['Are your Pokémon cards authentic?', 'Yes. Everything is sourced through Japanese distribution and ships sealed in its original factory packaging. We do not deal in resealed, reprinted or fake product.'],
    ['What if something arrives damaged or short?', 'Report transit damage, a short shipment or a wrong item within seven days of delivery and we replace, credit or refund the affected lines and their shipping.'],
  ],
];
