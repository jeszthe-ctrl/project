<?php
/* Starting data for a fresh install. On first load it is copied to data/store.php,
   and from then on everything is edited in admin.php — not here.
   Category, series, set, collection and guide text lives in content.php. */
if(!defined('FK_ROOT')){ http_response_code(404); exit; }

$d = [
  'settings' => [
    'brand'         => 'FUDAKURA',
    'kanji'         => '札蔵',
    'tagline'       => 'Japanese Pokémon cards, shipped from Japan to the UK',
    'legal_name'    => 'Fudakura',
    'address'       => 'Japan',
    'email'         => 'support@fudakura.co.uk',
    'phone'         => '',
    'order_email'   => 'support@fudakura.co.uk',
    'domain'        => 'https://fudakura.co.uk',
    'company_number' => '', 'company_registered' => '',   /* Companies House number and where registered (Admin → Settings) */
    'vat_number'    => '',               /* UK VAT number; empty = not VAT-registered, no VAT shown */
    'vat_rate'      => 20,               /* % included in prices once a VAT number is set */
    'base_currency' => 'GBP',            /* the shop currency: every price and rate below is in it */
    'reply_hours'   => 12,
    'hold_hours'    => 48,
    'min_order_usd' => 75,               /* minimum order incl. shipping, in the shop currency (the name is historical) */
    'strip_text'    => 'Sealed product, sourced in Japan · Quantity breaks published on every listing',
    'strip_link_text' => 'Aura Seeker preorders open →',
    'strip_link_url'  => 'product:aura-seeker-booster-box',
    'hero_title'    => 'Wholesale Japanese Pokémon cards, shipped from Japan to the UK.',
    'hero_lede'     => 'Sealed booster boxes, Elite Trainer Boxes, premium sets and singles, bought through Japanese distribution and priced in pounds by the case. Every quantity break is published — price a full order before you talk to anyone.',
    'footer_blurb'  => 'Wholesale Japanese Pokémon TCG, shipped from Japan to UK collectors, resellers and card shops. Prices in pounds on every product — order direct, no account needed.',
    'shipping_reviewed' => false,
    'pretty_urls'   => 'auto',           /* clean addresses like /products/151-booster-box: 'auto' = on when the host's rewrite rules work */
    'free_ship_usd' => 1500,             /* free Standard shipping from this goods total (shop currency); 0 = off */
    'content_version' => 3,              /* set by the shop: which built-in content updates are applied */
    'google_verify' => '', 'bing_verify' => '',   /* Search Console / Bing verification codes (Admin → Settings) */
    /* SMTP for order emails (Admin → Settings → Email); blank host = the web host's mail() */
    'smtp_host' => '', 'smtp_port' => 465, 'smtp_secure' => 'ssl', 'smtp_user' => '', 'smtp_pass' => '',

    /* Bitcoin: orders paid with a "Bitcoin" payment method are paid to this address from the order page */
    'btc_address'       => 'bc1qhvgghjapwsugh4nnckh0skqar7jcg5xmu36jpl',
    'btc_quote_minutes' => 60,           /* how long a BTC amount is held before it is renewed at the current price */
    'btc_confirmations' => 1,            /* blockchain confirmations before an order is marked Paid */

    /* live chat (Tawk.to): loaded after the page has finished loading, so it never slows the shop down */
    'chat_code' => <<<'HTML'
<!--Start of Tawk.to Script-->
<script type="text/javascript">
var Tawk_API=Tawk_API||{}, Tawk_LoadStart=new Date();
(function(){
var s1=document.createElement("script"),s0=document.getElementsByTagName("script")[0];
s1.async=true;
s1.src='https://embed.tawk.to/6ab599650aebd43443ef66b2/default';
s1.charset='UTF-8';
s1.setAttribute('crossorigin','*');
s0.parentNode.insertBefore(s1,s0);
})();
</script>
<!--End of Tawk.to Script-->
HTML,
  ],

  /* rate = units of each currency per 1 of the shop currency (GBP) */
  'currencies' => [
    'GBP' => ['rate'=>1,      'sym'=>'£',   'dec'=>2],
    'EUR' => ['rate'=>1.1093, 'sym'=>'€',   'dec'=>2],
    'USD' => ['rate'=>1.2658, 'sym'=>'$',   'dec'=>2],
    'JPY' => ['rate'=>197.47, 'sym'=>'¥',   'dec'=>0],
    'CAD' => ['rate'=>1.7342, 'sym'=>'CA$', 'dec'=>2],
    'AUD' => ['rate'=>1.9241, 'sym'=>'A$',  'dec'=>2],
  ],


  /* status: in | new | low | preorder | soldout.  cond: Sealed | Graded | Near Mint | Lightly Played.
     ladder: [min_qty, unit_price] ascending, in the shop currency (GBP).  weight: kg per unit (used for shipping) */
  'products' => [
    ['id'=>'storm-emeralda-elite-trainer-box', 'seo_desc'=>'Sealed Storm Emeralda Elite Trainer Box from the Japanese Mega Evolution series: booster packs, sleeves, dice and a storage box, shipped to the UK.',
     'sku'=>'FK-ETB-SE-01',
     'name'=>'Storm Emeralda Elite Trainer Box', 'set'=>'Storm Emeralda', 'cat'=>'etb', 'cond'=>'Sealed',
     'moq'=>6, 'step'=>6, 'status'=>'in', 'release'=>'', 'weight'=>0.9, 'hidden'=>false,
     'ladder'=>[[1,35.50],[6,30.00],[18,28.50],[36,27.00]],
     'desc'=>'A factory-sealed Storm Emeralda Elite Trainer Box from the Japanese Mega Evolution series.

Inside are booster packs plus card sleeves, dice, damage counters, energy cards and the set promo, packed in a storage box — everything needed to open and play Storm Emeralda. A steady seller next to [Storm Emeralda booster boxes](product:storm-emeralda-m6-booster-box).

- Sealed Storm Emeralda Elite Trainer Box
- Booster packs, sleeves, dice, damage counters, energy cards and promo
- Sold in sixes, with lower prices from 18 and 36'],
    ['id'=>'30th-celebration-m6a-booster-box', 'seo_desc'=>'Sealed Japanese 30th Celebration (M6A) booster box for Pokémon\'s 30th anniversary, with classic illustrations and a high pull rate. Shipped to the UK.',
     'sku'=>'FK-BB-M6A-01',
     'name'=>'30th Celebration (M6A) Booster Box', 'set'=>'30th Celebration', 'cat'=>'boxes', 'cond'=>'Sealed',
     'moq'=>6, 'step'=>6, 'status'=>'in', 'release'=>'', 'weight'=>0.4, 'hidden'=>false,
     'ladder'=>[[1,258.00],[6,220.00],[12,211.00],[24,201.00]],
     'desc'=>'A factory-sealed Japanese booster box from 30th Celebration (M6A), the set marking Pokémon\'s 30th anniversary.

30th Celebration brings back classic Pokémon illustrations alongside new cards and has a high pull rate, which makes anniversary boxes a favourite with collectors and box openers alike. Open them for the pulls, or keep boxes sealed as an anniversary collectible.

- Sealed Japanese booster box — 30th Celebration (M6A)
- Reprinted classic illustrations and a high pull rate
- Lower prices from 12 and 24 boxes
- More from the anniversary range: [30th Celebration Elite Trainer Box](product:30th-celebration-elite-trainer-box) and [special boxes](set:30th-celebration)'],
    ['id'=>'mega-rayquaza-ex-mur', 'seo_desc'=>'Japanese Mega Rayquaza ex Master Ultra Rare from Storm Emeralda (M6), the top chase card of the set. Near Mint, sleeved and toploaded, shipped to the UK.',
     'sku'=>'FK-SGL-MRAY-MUR',
     'name'=>'Mega Rayquaza ex — Master Ultra Rare (Japanese)', 'set'=>'Storm Emeralda', 'cat'=>'singles', 'cond'=>'Near Mint',
     'moq'=>1, 'step'=>1, 'status'=>'in', 'release'=>'', 'weight'=>0.05, 'hidden'=>false,
     'ladder'=>[[1,954.00],[3,908.00],[6,863.00]],
     'desc'=>'The Japanese Mega Rayquaza ex Master Ultra Rare from Storm Emeralda — the top-rarity chase card of the set.

Rayquaza has been one of the Pokémon TCG\'s most collected legends since its first cards, and its Master Ultra Rare sits at the very top of Storm Emeralda\'s rarity chart. Every copy is Near Mint, sleeved and toploaded on dispatch, with any condition notes on your invoice.

- Japanese — Storm Emeralda (M6)
- Rarity: Master Ultra Rare
- Near Mint, sleeved and toploaded
- Lower prices from 3 and 6 copies'],
    ['id'=>'mega-gengar-ex-sir', 'seo_desc'=>'Japanese Mega Gengar ex Special Illustration Rare from Mega Dream ex (M2A), a standout full art Gengar card. Near Mint and shipped to the UK.',
     'sku'=>'FK-SGL-MGEN-SIR',
     'name'=>'Mega Gengar ex — Special Illustration Rare', 'set'=>'Mega Dream ex', 'cat'=>'singles', 'cond'=>'Near Mint',
     'moq'=>1, 'step'=>1, 'status'=>'in', 'release'=>'', 'weight'=>0.05, 'hidden'=>false,
     'ladder'=>[[1,816.00],[3,778.00],[6,739.00]],
     'desc'=>'The Japanese Mega Gengar ex Special Illustration Rare from Mega Dream ex.

Gengar has one of the most loyal followings in the hobby, and this Special Illustration Rare is one of the standout Gengar Pokémon cards of the Mega Evolution series. Near Mint, sleeved and toploaded on dispatch.

- Japanese — Mega Dream ex (M2A)
- Special Illustration Rare (full art)
- Lower prices from 3 and 6 copies
- See all [Gengar Pokémon cards](cards:gengar-pokemon-cards)'],
    ['id'=>'30th-celebration-elite-trainer-box', 'seo_desc'=>'Pokémon TCG 30th Celebration Elite Trainer Box: 9 booster packs, a full-art Nidorina promo, 65 sleeves, dice and a collector\'s box. Shipped to the UK.',
     'sku'=>'FK-ETB-30C-01',
     'name'=>'30th Celebration Elite Trainer Box', 'set'=>'30th Celebration', 'cat'=>'etb', 'cond'=>'Sealed',
     'moq'=>4, 'step'=>4, 'status'=>'in', 'release'=>'', 'weight'=>0.9, 'hidden'=>false,
     'ladder'=>[[1,38.50],[4,35.00],[24,26.00]],
     'desc'=>'The Pokémon TCG: 30th Celebration Elite Trainer Box — nine booster packs of the 30th-anniversary expansion plus everything needed to play them.

30th Celebration marks thirty years of Pokémon: Mewtwo ex and Mew ex lead the set, joined by Umbreon ex, Salamence ex and Greninja ex, and every booster pack contains a Pikachu — with 30 different Pikachu rare cards to collect. Buying in volume? See the [sealed 10-box case](product:30th-celebration-elite-trainer-box-case-10-ct).

- 9 Pokémon TCG: 30th Celebration booster packs
- 1 full-art foil promo card featuring Nidorina
- 16 foil Basic Energy cards and 65 card sleeves
- A player\'s guide to the 30th Celebration expansion
- 6 damage-counter dice, 1 competition-legal coin-flip die and 1 plastic coin
- A collector\'s box with 6 dividers, plus a code card for Pokémon TCG Live
- Sold in fours, with the lowest price from 24'],
    ['id'=>'storm-emeralda-m6-booster-box', 'seo_desc'=>'Sealed Japanese Storm Emeralda (M6) booster box, headlined by Mega Rayquaza ex and its Master Ultra Rare. Priced in pounds and shipped from Japan to the UK.',
     'sku'=>'FK-BB-M6-01',
     'name'=>'Storm Emeralda (M6) Booster Box', 'set'=>'Storm Emeralda', 'cat'=>'boxes', 'cond'=>'Sealed',
     'moq'=>6, 'step'=>6, 'status'=>'in', 'release'=>'', 'weight'=>0.4, 'hidden'=>false,
     'ladder'=>[[1,172.00],[6,146.00],[24,134.00]],
     'desc'=>'A factory-sealed Japanese booster box of Storm Emeralda (M6), the Mega Evolution set headlined by Mega Rayquaza ex.

Storm Emeralda\'s chase cards include the [Mega Rayquaza ex Master Ultra Rare](product:mega-rayquaza-ex-mur) and [Special Illustration Rare](product:mega-rayquaza-ex-sar-245-191-near-mint), which keeps sealed boxes in demand with collectors and box breakers. Order in multiples of six, or take a [sealed 12-box case](product:storm-emeralda-booster-box-case-12-ct) for the lowest price per box.

- Sealed Japanese Mega Evolution-series booster box (M6)
- Chase cards: Mega Rayquaza ex Master Ultra Rare and Special Illustration Rare
- Best price per box from 24 boxes'],
    ['id'=>'abyss-eye-elite-trainer-box', 'seo_desc'=>'Sealed Abyss Eye Elite Trainer Box from the Japanese Mega Evolution series, with booster packs, sleeves, dice and a storage box. Shipped from Japan to the UK.',
     'sku'=>'FK-ETB-AE-01',
     'name'=>'Abyss Eye Elite Trainer Box', 'set'=>'Abyss Eye', 'cat'=>'etb', 'cond'=>'Sealed',
     'moq'=>6, 'step'=>6, 'status'=>'in', 'release'=>'', 'weight'=>0.9, 'hidden'=>false,
     'ladder'=>[[1,33.00],[6,28.00],[18,26.50],[36,25.50]],
     'desc'=>'A factory-sealed Abyss Eye Elite Trainer Box from the Japanese Mega Evolution series.

Booster packs with sleeves, dice, damage counters and the promo card in a storage box — the easy way to open Abyss Eye alongside [booster boxes](product:abyss-eye-m5-booster-box).

- Sealed Abyss Eye Elite Trainer Box
- Sold in sixes, with lower prices from 18 and 36'],
    ['id'=>'30th-celebration-greninja-ex-box', 'seo_desc'=>'30th Celebration Greninja ex Box: a Greninja ex promo card with booster packs, from Pokémon\'s 30th-anniversary range. Sealed and shipped to the UK.',
     'sku'=>'FK-PRM-GRE-01',
     'name'=>'30th Celebration Greninja ex Box', 'set'=>'30th Celebration', 'cat'=>'premium', 'cond'=>'Sealed',
     'moq'=>6, 'step'=>6, 'status'=>'in', 'release'=>'', 'weight'=>0.6, 'hidden'=>false,
     'ladder'=>[[1,17.00],[6,15.30],[18,13.30],[36,11.50]],
     'desc'=>'A 30th Celebration collection box built around a Greninja ex promo card, with booster packs inside.

Collection boxes like this sell well as single units and gifts, and case pricing from 18 and 36 boxes leaves room for margin. It pairs naturally with the [Sylveon ex Box](product:30th-celebration-sylveon-ex-box).

- Greninja ex promo card plus booster packs
- Part of the [30th Celebration](set:30th-celebration) range
- Sold in sixes, with lower prices from 18 and 36'],
    ['id'=>'heat-wave-arena-sv9a-booster-box', 'seo_desc'=>'Sealed Japanese Heat Wave Arena (SV9a) booster box from the Scarlet & Violet series, with case pricing. Priced in pounds and shipped from Japan to the UK.',
     'sku'=>'FK-BB-SV9A-01',
     'name'=>'Heat Wave Arena (SV9a) Booster Box', 'set'=>'Heat Wave Arena', 'cat'=>'boxes', 'cond'=>'Sealed',
     'moq'=>6, 'step'=>6, 'status'=>'in', 'release'=>'', 'weight'=>0.4, 'hidden'=>false,
     'ladder'=>[[1,164.00],[6,156.00],[18,147.00],[36,140.00]],
     'desc'=>'A factory-sealed Japanese Heat Wave Arena (SV9a) booster box from the Scarlet & Violet series.

Heat Wave Arena is a dependable reorder line for card shops running drafts and league nights, and a steady opener for collectors of Scarlet & Violet-era Japanese product.

- Sealed Japanese booster box — Heat Wave Arena (SV9a)
- Part of the [Scarlet & Violet series](set:scarlet-violet)
- Lower prices from 18 and 36 boxes'],
    ['id'=>'glory-of-team-rocket-sv10-booster-box', 'seo_desc'=>'Sealed Japanese Glory of Team Rocket (SV10) booster box: Team Rocket\'s Pokémon return in one of the most wanted Scarlet & Violet sets. Shipped to the UK.',
     'sku'=>'FK-BB-SV10-01',
     'name'=>'Glory of Team Rocket (SV10) Booster Box', 'set'=>'Glory of Team Rocket', 'cat'=>'boxes', 'cond'=>'Sealed',
     'moq'=>6, 'step'=>6, 'status'=>'in', 'release'=>'', 'weight'=>0.4, 'hidden'=>false,
     'ladder'=>[[1,207.00],[6,197.00],[18,186.00],[36,176.00]],
     'desc'=>'A factory-sealed Japanese Glory of Team Rocket (SV10) booster box — Team Rocket\'s Pokémon return to the TCG.

Glory of Team Rocket is one of the most in-demand Japanese Scarlet & Violet sets, built around the Pokémon of Team Rocket. Allocation sells quickly.

- Sealed Japanese booster box — Glory of Team Rocket (SV10)
- Part of the [Scarlet & Violet series](set:scarlet-violet)
- Lower prices from 18 and 36 boxes'],
    ['id'=>'mega-start-deck-100-battle-collection', 'seo_desc'=>'MEGA Start Deck 100 Battle Collection: ready-to-play Pokémon TCG decks from the Mega Evolution era, ideal for new players. Shipped to the UK.',
     'sku'=>'FK-PRM-MSD100-01',
     'name'=>'MEGA Start Deck 100 Battle Collection', 'set'=>'', 'cat'=>'premium', 'cond'=>'Sealed',
     'moq'=>12, 'step'=>12, 'status'=>'in', 'release'=>'', 'weight'=>1.2, 'hidden'=>false,
     'ladder'=>[[1,29.50],[12,28.00],[72,25.00]],
     'desc'=>'The MEGA Start Deck 100 Battle Collection: ready-to-play Pokémon TCG decks for new players.

Start decks are the easiest way to learn how to play the Pokémon card game — open the box and play, with no deck-building needed. For card shops they\'re a low-cost entry product for league nights and new-player events.

- Ready-to-play decks from the Mega Evolution era
- Sold by the case of 12, with the lowest price from 72'],
    ['id'=>'starter-set-ex-zorua-and-zoroark-ex', 'seo_desc'=>'Starter Set ex Zorua & Zoroark ex: a ready-to-play Pokémon TCG deck built around Zoroark ex, a good first deck for new players. Shipped to the UK.',
     'sku'=>'FK-PRM-SSZOR-01',
     'name'=>'Starter Set ex — Zorua & Zoroark ex', 'set'=>'', 'cat'=>'premium', 'cond'=>'Sealed',
     'moq'=>12, 'step'=>12, 'status'=>'in', 'release'=>'', 'weight'=>0.35, 'hidden'=>false,
     'ladder'=>[[1,29.50],[12,28.00],[72,25.00]],
     'desc'=>'Starter Set ex with a ready-to-play Zorua and Zoroark ex deck.

A complete deck built around Zoroark ex, ready to play straight from the box — a good first deck for new players and an easy add-on next to booster boxes.

- Ready-to-play Zorua & Zoroark ex deck
- Sold by the case of 12, with the lowest price from 72'],
    ['id'=>'premium-trainer-box-mega', 'seo_desc'=>'Premium Trainer Box MEGA: booster packs and play accessories from the Pokémon TCG Mega Evolution era, and a strong gift. Sealed and shipped to the UK.',
     'sku'=>'FK-PRM-PTBM-01',
     'name'=>'Premium Trainer Box MEGA', 'set'=>'', 'cat'=>'premium', 'cond'=>'Sealed',
     'moq'=>4, 'step'=>4, 'status'=>'in', 'release'=>'', 'weight'=>0.9, 'hidden'=>false,
     'ladder'=>[[1,72.00],[4,69.00],[24,61.50]],
     'desc'=>'Premium Trainer Box MEGA — a premium trainer box from the Pokémon TCG Mega Evolution era.

Premium trainer boxes combine booster packs with play accessories for players building their first Mega Evolution decks, and make a strong gift.

- Mega Evolution-era premium trainer box
- Sold in fours, with the lowest per-unit price from 24'],
    ['id'=>'151-booster-box', 'seo_title'=>'Pokémon 151 Booster Box (Japanese SV2a) — 20 Packs', 'seo_desc'=>'Sealed Japanese Pokémon 151 booster box (SV2a): 20 packs of 7 cards, with the Charizard ex SAR as chase card. Priced in pounds, shipped from Japan to the UK.',
     'sku'=>'FK-BB-SV2A-01',
     'name'=>'151 Booster Box', 'set'=>'151', 'cat'=>'boxes', 'cond'=>'Sealed',
     'moq'=>6, 'step'=>6, 'status'=>'in', 'release'=>'', 'weight'=>0.4, 'hidden'=>false,
     'ladder'=>[[1,352.00],[6,336.00],[36,301.00]],
     'desc'=>'A factory-sealed Japanese 151 (SV2a) booster box — 20 packs of 7 cards from the set that revisits the original 151 Pokémon.

151 Pokémon cards are some of the most collected of the Scarlet & Violet era: the original Pokémon from Bulbasaur to Mew, in modern artwork with full art rares. The [Charizard ex Special Illustration Rare](product:151-charizard-ex-special-illustration-rare) is the chase card. 151 was later released in English as Scarlet & Violet—151, but the Japanese version came first and many collectors prefer it.

- Sealed Japanese 151 booster box (SV2a): 20 packs of 7 cards
- Chase card: Charizard ex Special Illustration Rare
- Sold in sixes, with the lowest price from 36 boxes
- New to Japanese boxes? Read [what\'s different about Japanese Pokémon cards](guide:japanese-pokemon-cards)'],
    ['id'=>'151-charizard-ex-special-illustration-rare', 'seo_desc'=>'Japanese 151 Charizard ex Special Illustration Rare (SV2a), one of the defining Charizard cards of the Scarlet & Violet era. Near Mint, shipped to the UK.',
     'sku'=>'FK-SGL-CHAR-151',
     'name'=>'151 Charizard ex — Special Illustration Rare', 'set'=>'151', 'cat'=>'singles', 'cond'=>'Near Mint',
     'moq'=>1, 'step'=>1, 'status'=>'in', 'release'=>'', 'weight'=>0.05, 'hidden'=>false,
     'ladder'=>[[1,260.00],[6,235.00]],
     'desc'=>'The Japanese Charizard ex Special Illustration Rare from 151 (SV2a).

Charizard is one of the most collected Pokémon in the TCG, and its 151 Special Illustration Rare is one of the defining Charizard Pokémon cards of the Scarlet & Violet era. Near Mint, sleeved and toploaded on dispatch.

- Japanese — 151 (SV2a)
- Special Illustration Rare (full art)
- Lower price from 6 copies
- More [Charizard Pokémon cards](cards:charizard-pokemon-cards)'],
    ['id'=>'storm-emeralda-booster-box-case-12-ct', 'seo_desc'=>'Sealed case of 12 Japanese Storm Emeralda (M6) booster boxes, priced per case with a lower price per box. For shops and box breakers in the UK.',
     'sku'=>'FK-BB-M6-C12',
     'name'=>'Storm Emeralda Booster Box Case (12-ct)', 'set'=>'Storm Emeralda', 'cat'=>'boxes', 'cond'=>'Sealed',
     'moq'=>1, 'step'=>1, 'status'=>'in', 'release'=>'', 'weight'=>5.6, 'hidden'=>false,
     'ladder'=>[[1,1292.00],[6,1156.00]],
     'desc'=>'A factory-sealed case of 12 Japanese Storm Emeralda (M6) booster boxes, priced per case.

For shops and box breakers stocking Storm Emeralda in volume, a sealed case is the simplest buy: twelve booster boxes in one sealed case at a lower price per box than buying boxes individually, falling further from six cases.

- 12 sealed Japanese Storm Emeralda (M6) booster boxes per case
- Lower price per box than single boxes, with a further break from 6 cases
- Chase card: [Mega Rayquaza ex Master Ultra Rare](product:mega-rayquaza-ex-mur)'],
    ['id'=>'aura-seeker-booster-box', 'seo_desc'=>'Preorder the Japanese Aura Seeker (Hadou Seeker) booster box, an upcoming Mega Evolution set due November 2026. Invoiced at allocation, shipped to the UK.',
     'sku'=>'FK-BB-MLZ-01',
     'name'=>'Aura Seeker (Hadou Seeker) Booster Box', 'set'=>'Aura Seeker (Hadou Seeker)', 'cat'=>'boxes', 'cond'=>'Sealed',
     'moq'=>6, 'step'=>6, 'status'=>'preorder', 'release'=>'November 2026', 'weight'=>0.4, 'hidden'=>false,
     'ladder'=>[[1,96.00],[6,91.50],[18,86.50],[36,82.00]],
     'desc'=>'Preorder the Japanese Aura Seeker (Hadou Seeker) booster box, an upcoming Mega Evolution-series set.

A preorder reserves your allocation ahead of release at the quantity-break prices shown. You are invoiced when stock is allocated, not when you order, and boxes ship from Japan once released and paid.

- Sealed Japanese booster box — preorder
- Nothing to pay today: invoiced at allocation
- Sold in sixes, with the lowest price from 36 boxes'],
    ['id'=>'starter-set-ex-eevee-ex', 'seo_desc'=>'Starter Set ex Eevee ex: a ready-to-play Pokémon TCG deck built around Eevee ex, an easy first deck and a popular gift. Sealed and shipped to the UK.',
     'sku'=>'FK-PRM-SSEEV-01',
     'name'=>'Starter Set ex — Eevee ex', 'set'=>'', 'cat'=>'premium', 'cond'=>'Sealed',
     'moq'=>10, 'step'=>10, 'status'=>'in', 'release'=>'', 'weight'=>0.35, 'hidden'=>false,
     'ladder'=>[[1,59.00],[10,46.50],[60,40.50]],
     'desc'=>'Starter Set ex with a ready-to-play Eevee ex deck.

A complete deck built around Eevee ex, ready to play straight from the box — an easy first purchase for new players and a popular gift.

- Ready-to-play Eevee ex deck
- Sold by the case of 10, with the lowest price from 60'],
    ['id'=>'pokemon-tcg-card-storage-box-booster-box-display', 'seo_desc'=>'Pokémon TCG card storage box in booster box display style, keeping sleeved or loose cards upright and flat. Sold in sixes and shipped to the UK.',
     'sku'=>'FK-ACC-STOR-01',
     'name'=>'Pokémon TCG Card Storage Box — Booster Box Display', 'set'=>'', 'cat'=>'accessories', 'cond'=>'Sealed',
     'moq'=>6, 'step'=>6, 'status'=>'in', 'release'=>'', 'weight'=>0.25, 'hidden'=>false,
     'ladder'=>[[1,7.00],[6,6.20],[36,4.70]],
     'desc'=>'A Pokémon TCG card storage box styled like a booster box display.

It keeps sleeved or loose cards upright and flat on a shelf or counter, and doubles as a display piece. Pokémon cards are 63 × 88 mm — see our [Pokémon card size guide](guide:pokemon-card-size).

- Card storage box in booster box display style
- Sold in sixes, with the lowest price from 36'],
    ['id'=>'pokemon-tcg-9-pocket-binder-mega-evolution-series', 'seo_title'=>'Pokémon Card Binder — 9-Pocket, Mega Evolution Series', 'seo_desc'=>'A 9-pocket Pokémon card binder in Mega Evolution series artwork, holding nine standard-size Pokémon cards per page. Priced in pounds with bulk breaks.',
     'sku'=>'FK-ACC-BIND-ME',
     'name'=>'Pokémon TCG 9-Pocket Binder — Mega Evolution Series', 'set'=>'', 'cat'=>'accessories', 'cond'=>'Sealed',
     'moq'=>6, 'step'=>6, 'status'=>'in', 'release'=>'', 'weight'=>0.45, 'hidden'=>false,
     'ladder'=>[[1,25.00],[6,22.00],[36,16.00]],
     'desc'=>'A 9-pocket Pokémon card binder with Mega Evolution series artwork.

Each page holds nine standard-size Pokémon cards, so a binder is the simplest way to organise a growing collection and show off full art pulls. Single-sleeved cards fit comfortably — see our [card size guide](guide:pokemon-card-size) for sleeve sizes.

- 9-pocket Pokémon card binder
- Mega Evolution series artwork
- Sold in sixes, with the lowest price from 36'],
    ['id'=>'celebi-and-furret-deck-sleeves', 'seo_desc'=>'Celebi & Furret deck sleeves for standard 63 × 88 mm Pokémon cards, protecting cards in play and storage. Sold in packs of 24, shipped to the UK.',
     'sku'=>'FK-ACC-SLV-CELF',
     'name'=>'Celebi & Furret Deck Sleeves', 'set'=>'', 'cat'=>'accessories', 'cond'=>'Sealed',
     'moq'=>24, 'step'=>24, 'status'=>'in', 'release'=>'', 'weight'=>0.06, 'hidden'=>false,
     'ladder'=>[[1,10.10],[24,9.10],[144,6.80]],
     'desc'=>'Deck sleeves with Celebi and Furret artwork, sized for standard Pokémon cards.

Sleeves protect cards in play and in storage, and character designs like these are popular add-ons at the counter.

- Fits standard 63 × 88 mm Pokémon cards
- Sold in 24s, with the lowest price from 144'],
    ['id'=>'pikachu-ditto-ver-deck-sleeves', 'seo_desc'=>'Pikachu (Ditto Ver.) deck sleeves for standard 63 × 88 mm Pokémon cards, protecting cards in play and storage. Sold in packs of 24, shipped to the UK.',
     'sku'=>'FK-ACC-SLV-PIKD',
     'name'=>'Pikachu (Ditto Ver.) Deck Sleeves', 'set'=>'', 'cat'=>'accessories', 'cond'=>'Sealed',
     'moq'=>24, 'step'=>24, 'status'=>'in', 'release'=>'', 'weight'=>0.06, 'hidden'=>false,
     'ladder'=>[[1,10.10],[24,9.10],[144,6.80]],
     'desc'=>'Deck sleeves with Pikachu in its Ditto version artwork, sized for standard Pokémon cards.

Sleeves protect cards in play and in storage, and character designs like these are popular add-ons at the counter.

- Fits standard 63 × 88 mm Pokémon cards
- Sold in 24s, with the lowest price from 144'],
    ['id'=>'storm-emeralda-mega-rayquaza-deck-sleeves', 'seo_desc'=>'Mega Rayquaza deck sleeves from the Storm Emeralda release, sized for standard 63 × 88 mm Pokémon cards. Sold in packs of 24, shipped to the UK.',
     'sku'=>'FK-ACC-SLV-MRAY',
     'name'=>'Storm Emeralda Mega Rayquaza Deck Sleeves', 'set'=>'Storm Emeralda', 'cat'=>'accessories', 'cond'=>'Sealed',
     'moq'=>24, 'step'=>24, 'status'=>'in', 'release'=>'', 'weight'=>0.06, 'hidden'=>false,
     'ladder'=>[[1,10.10],[24,9.10],[144,6.80]],
     'desc'=>'Deck sleeves with Mega Rayquaza artwork from the Storm Emeralda release, sized for standard Pokémon cards.

Sleeves protect cards in play and in storage, and character designs like these are popular add-ons at the counter. They\'re the natural add-on to [Storm Emeralda booster boxes](product:storm-emeralda-m6-booster-box).

- Fits standard 63 × 88 mm Pokémon cards
- Sold in 24s, with the lowest price from 144'],
    ['id'=>'ultra-pro-pikachu-alcove-tower-deck-box', 'seo_desc'=>'Ultra PRO Alcove Tower deck box with Pikachu artwork, holding a sleeved Pokémon deck for league nights and tournaments. Sold in 12s, shipped to the UK.',
     'sku'=>'FK-ACC-UP-ALCT',
     'name'=>'Ultra PRO Pikachu Alcove Tower Deck Box', 'set'=>'', 'cat'=>'accessories', 'cond'=>'Sealed',
     'moq'=>12, 'step'=>12, 'status'=>'in', 'release'=>'', 'weight'=>0.2, 'hidden'=>false,
     'ladder'=>[[1,16.30],[12,14.60],[72,11.00]],
     'desc'=>'An Ultra PRO Alcove Tower deck box with Pikachu artwork.

A sturdy deck box to carry a sleeved Pokémon deck to league nights and tournaments, with Pikachu artwork that sells itself.

- Ultra PRO Alcove Tower deck box
- Pikachu artwork
- Sold in 12s, with the lowest price from 72'],
    ['id'=>'ultra-pro-pikachu-deck-protector-sleeves-65-ct', 'seo_desc'=>'Ultra PRO Pikachu Deck Protector sleeves: 65 standard-size sleeves per pack for 63 × 88 mm Pokémon cards. Sold in 24s and shipped to the UK.',
     'sku'=>'FK-ACC-UP-SLV65',
     'name'=>'Ultra PRO Pikachu Deck Protector Sleeves (65 ct)', 'set'=>'', 'cat'=>'accessories', 'cond'=>'Sealed',
     'moq'=>24, 'step'=>24, 'status'=>'in', 'release'=>'', 'weight'=>0.06, 'hidden'=>false,
     'ladder'=>[[1,8.50],[24,7.20],[144,4.30]],
     'desc'=>'Ultra PRO Deck Protector sleeves with Pikachu artwork — 65 standard-size Pokémon card sleeves per pack.

Standard-size sleeves fit Pokémon cards for play and storage. See our [Pokémon card size guide](guide:pokemon-card-size) for which sleeves go with which cards.

- 65 sleeves per pack
- Fits standard 63 × 88 mm Pokémon cards
- Sold in 24s, with the lowest price from 144'],
    ['id'=>'pokemon-playmat-assorted-designs', 'seo_desc'=>'Pokémon playmats in assorted designs, protecting cards during play and marking out the play area. Minimum 10, then in fives, shipped to the UK.',
     'sku'=>'FK-ACC-MAT-AST',
     'name'=>'Pokémon Playmat — Assorted Designs', 'set'=>'', 'cat'=>'accessories', 'cond'=>'Sealed',
     'moq'=>10, 'step'=>5, 'status'=>'in', 'release'=>'', 'weight'=>0.45, 'hidden'=>false,
     'ladder'=>[[1,22.00],[10,17.40],[50,15.00]],
     'desc'=>'Pokémon playmats in assorted designs.

A playmat protects cards during play and marks out the play area — a popular add-on for players and a steady seller for shops. The mix of designs varies by shipment.

- Assorted Pokémon designs
- Minimum 10, then in fives, with the lowest price from 50'],
    ['id'=>'pokemon-deck-box-assorted', 'seo_desc'=>'Pokémon deck boxes in assorted designs, keeping a sleeved deck safe in a bag or pocket. Minimum 20, then in tens, priced in pounds and shipped to the UK.',
     'sku'=>'FK-ACC-DBX-AST',
     'name'=>'Pokémon Deck Box — Assorted', 'set'=>'', 'cat'=>'accessories', 'cond'=>'Sealed',
     'moq'=>20, 'step'=>10, 'status'=>'in', 'release'=>'', 'weight'=>0.1, 'hidden'=>false,
     'ladder'=>[[1,9.50],[20,7.40],[100,6.30]],
     'desc'=>'Pokémon deck boxes in assorted designs.

Deck boxes keep a sleeved deck safe in a bag or pocket, and sell steadily alongside sleeves and playmats. The mix of designs varies by shipment.

- Assorted Pokémon designs
- Minimum 20, then in tens, with the lowest price from 100'],
    ['id'=>'pokemon-card-sleeves-64-ct-assorted-designs', 'seo_desc'=>'Pokémon card sleeves in assorted designs, 64 per pack, fitting Japanese and English Pokémon cards. Minimum 20, then in tens, shipped to the UK.',
     'sku'=>'FK-ACC-SLV64-AST',
     'name'=>'Pokémon Card Sleeves (64 ct) — Assorted Designs', 'set'=>'', 'cat'=>'accessories', 'cond'=>'Sealed',
     'moq'=>20, 'step'=>10, 'status'=>'in', 'release'=>'', 'weight'=>0.06, 'hidden'=>false,
     'ladder'=>[[1,6.30],[20,4.90],[100,4.10]],
     'desc'=>'Pokémon card sleeves in assorted designs, 64 per pack.

Sized for standard 63 × 88 mm Pokémon cards (Japanese and English cards are the same size), these sleeves keep cards in Near Mint condition in play and in storage. The mix of designs varies by shipment.

- 64 sleeves per pack
- Fits Japanese and English Pokémon cards
- Minimum 20, then in tens, with the lowest price from 100'],
    ['id'=>'mega-rayquaza-ex-sar-245-191-near-mint', 'seo_desc'=>'Japanese Mega Rayquaza ex Special Illustration Rare 245/191 from Storm Emeralda (M6), full art of the set\'s headline Pokémon. Near Mint, shipped to the UK.',
     'sku'=>'FK-SGL-MRAY-SAR245',
     'name'=>'Mega Rayquaza ex SAR #245/191 — Near Mint', 'set'=>'Storm Emeralda', 'cat'=>'singles', 'cond'=>'Near Mint',
     'moq'=>3, 'step'=>1, 'status'=>'in', 'release'=>'', 'weight'=>0.05, 'hidden'=>false,
     'ladder'=>[[1,95.00],[3,80.50],[25,71.00]],
     'desc'=>'The Japanese Mega Rayquaza ex Special Illustration Rare, card 245/191 from Storm Emeralda, in Near Mint condition.

A full art Special Illustration Rare of the set\'s headline Pokémon, and a more affordable way into Mega Rayquaza than the [Master Ultra Rare](product:mega-rayquaza-ex-mur).

- Japanese — Storm Emeralda (M6), card 245/191
- Special Illustration Rare (full art)
- Near Mint, sleeved and toploaded
- Minimum 3 copies, with the lowest price from 25'],
    ['id'=>'pikachu-ex-sar-240-191-psa-10-gem-mint', 'seo_desc'=>'Pikachu ex Special Illustration Rare 240/191 graded PSA 10 Gem Mint, the top grade, in its original PSA slab. A graded Pikachu card shipped to the UK.',
     'sku'=>'FK-SGL-PIKA-PSA10',
     'name'=>'Pikachu ex SAR #240/191 — PSA 10 Gem Mint', 'set'=>'', 'cat'=>'singles', 'cond'=>'Graded',
     'moq'=>1, 'step'=>1, 'status'=>'in', 'release'=>'', 'weight'=>0.15, 'hidden'=>false,
     'ladder'=>[[1,391.00],[3,371.00]],
     'desc'=>'Pikachu ex Special Illustration Rare #240/191, graded PSA 10 Gem Mint.

PSA 10 is the top grade: a virtually perfect card, authenticated and sealed in PSA\'s tamper-evident slab. Pikachu cards sell to every kind of collector, and top-grade copies are the ones they keep.

- Pikachu ex Special Illustration Rare #240/191
- Graded PSA 10 Gem Mint
- Ships in its original PSA slab
- More [PSA graded Pokémon cards](cards:psa-graded-pokemon-cards)'],
    ['id'=>'mega-floette-ex-japanese', 'seo_desc'=>'Japanese Mega Floette ex from Ninja Spinner (M4), one of the new Mega Pokémon ex of the Mega Evolution series. Near Mint, shipped to the UK.',
     'sku'=>'FK-SGL-MFLO',
     'name'=>'Mega Floette ex (Japanese)', 'set'=>'Ninja Spinner', 'cat'=>'singles', 'cond'=>'Near Mint',
     'moq'=>1, 'step'=>1, 'status'=>'in', 'release'=>'', 'weight'=>0.05, 'hidden'=>false,
     'ladder'=>[[1,44.50],[6,40.50]],
     'desc'=>'The Japanese Mega Floette ex from Ninja Spinner (M4), in Near Mint condition.

Mega Floette ex is one of the new Mega Pokémon ex introduced in Ninja Spinner. Sleeved and toploaded on dispatch.

- Japanese — [Ninja Spinner](set:ninja-spinner) (M4)
- Near Mint, sleeved and toploaded
- Lower price from 6 copies'],
    ['id'=>'mega-excadrill-ex-japanese', 'seo_desc'=>'Japanese Mega Excadrill ex from Abyss Eye (M5), one of the Mega Pokémon ex of the Mega Evolution series. Near Mint, sleeved and toploaded, shipped to the UK.',
     'sku'=>'FK-SGL-MEXC',
     'name'=>'Mega Excadrill ex (Japanese)', 'set'=>'Abyss Eye', 'cat'=>'singles', 'cond'=>'Near Mint',
     'moq'=>1, 'step'=>1, 'status'=>'in', 'release'=>'', 'weight'=>0.05, 'hidden'=>false,
     'ladder'=>[[1,52.00],[6,47.00]],
     'desc'=>'The Japanese Mega Excadrill ex from Abyss Eye (M5), in Near Mint condition.

Mega Excadrill ex is one of the Mega Pokémon ex introduced in Abyss Eye. Sleeved and toploaded on dispatch.

- Japanese — [Abyss Eye](set:abyss-eye) (M5)
- Near Mint, sleeved and toploaded
- Lower price from 6 copies'],
    ['id'=>'mega-greninja-ex-japanese', 'seo_desc'=>'Japanese Mega Greninja ex from Ninja Spinner (M4), a headline card of the set and a fan favourite. Near Mint, sleeved and toploaded, shipped to the UK.',
     'sku'=>'FK-SGL-MGRE',
     'name'=>'Mega Greninja ex (Japanese)', 'set'=>'Ninja Spinner', 'cat'=>'singles', 'cond'=>'Near Mint',
     'moq'=>1, 'step'=>1, 'status'=>'in', 'release'=>'', 'weight'=>0.05, 'hidden'=>false,
     'ladder'=>[[1,67.00],[6,60.50]],
     'desc'=>'The Japanese Mega Greninja ex from Ninja Spinner (M4), in Near Mint condition.

Greninja is a long-time fan favourite, and Mega Greninja ex is one of the headline Pokémon of Ninja Spinner. Sleeved and toploaded on dispatch.

- Japanese — [Ninja Spinner](set:ninja-spinner) (M4)
- Near Mint, sleeved and toploaded
- Lower price from 6 copies'],
    ['id'=>'mega-darkrai-ex-japanese', 'seo_desc'=>'Japanese Mega Darkrai ex from Abyss Eye (M5), a headline Mega Pokémon ex of the set. Near Mint, sleeved and toploaded, shipped from Japan to the UK.',
     'sku'=>'FK-SGL-MDRK',
     'name'=>'Mega Darkrai ex (Japanese)', 'set'=>'Abyss Eye', 'cat'=>'singles', 'cond'=>'Near Mint',
     'moq'=>1, 'step'=>1, 'status'=>'in', 'release'=>'', 'weight'=>0.05, 'hidden'=>false,
     'ladder'=>[[1,144.00],[6,130.00]],
     'desc'=>'The Japanese Mega Darkrai ex from Abyss Eye (M5), in Near Mint condition.

Darkrai is a fan-favourite Mythical Pokémon, and Mega Darkrai ex is one of the headline cards of Abyss Eye. Sleeved and toploaded on dispatch.

- Japanese — [Abyss Eye](set:abyss-eye) (M5)
- Near Mint, sleeved and toploaded
- Lower price from 6 copies'],
    ['id'=>'pikachu-ex-special-illustration-rare-277-217', 'seo_desc'=>'Pikachu ex Special Illustration Rare 277/217, a full art Pikachu card and a centrepiece for any collection. Near Mint and shipped to the UK.',
     'sku'=>'FK-SGL-PIKA-SAR277',
     'name'=>'Pikachu ex — Special Illustration Rare #277/217', 'set'=>'', 'cat'=>'singles', 'cond'=>'Near Mint',
     'moq'=>1, 'step'=>1, 'status'=>'in', 'release'=>'', 'weight'=>0.05, 'hidden'=>false,
     'ladder'=>[[1,287.00],[6,259.00]],
     'desc'=>'Pikachu ex Special Illustration Rare, card 277/217, in Near Mint condition.

A full art Special Illustration Rare of Pokémon\'s mascot — a centrepiece Pikachu Pokémon card for any collection. Sleeved and toploaded on dispatch.

- Card 277/217
- Special Illustration Rare (full art)
- Near Mint
- More [Pikachu Pokémon cards](cards:pikachu-pokemon-cards)'],
    ['id'=>'mega-charizard-y-ex-hyper-rare', 'seo_desc'=>'Mega Charizard Y ex Hyper Rare: a gold, textured Charizard card from the Mega Evolution series. Near Mint, sleeved and toploaded, shipped to the UK.',
     'sku'=>'FK-SGL-MCHY-HR',
     'name'=>'Mega Charizard Y ex — Hyper Rare', 'set'=>'', 'cat'=>'singles', 'cond'=>'Near Mint',
     'moq'=>1, 'step'=>1, 'status'=>'in', 'release'=>'', 'weight'=>0.05, 'hidden'=>false,
     'ladder'=>[[1,324.00],[6,294.00]],
     'desc'=>'Mega Charizard Y ex Hyper Rare — a gold Charizard Pokémon card from the Mega Evolution series.

Hyper Rares are the textured gold cards at the top of a set, and Charizard\'s are always among the most wanted. Near Mint, sleeved and toploaded on dispatch.

- Hyper Rare (gold, textured)
- Near Mint
- Lower price from 6 copies
- What makes [gold Pokémon cards](guide:pokemon-card-rarities) rare'],
    ['id'=>'30th-celebration-premium-deck-set-espeon-and-umbreon', 'seo_desc'=>'30th Celebration Premium Deck Set with Espeon and Umbreon, a collector\'s piece for Pokémon\'s 30th anniversary. Sealed and shipped from Japan to the UK.',
     'sku'=>'FK-PRM-30C-ESUM',
     'name'=>'30th Celebration Premium Deck Set — Espeon & Umbreon', 'set'=>'30th Celebration', 'cat'=>'premium', 'cond'=>'Sealed',
     'moq'=>1, 'step'=>1, 'status'=>'in', 'release'=>'', 'weight'=>1.2, 'hidden'=>false,
     'ladder'=>[[1,342.00],[6,294.00]],
     'desc'=>'The 30th Celebration Premium Deck Set featuring Espeon and Umbreon — a collector-focused anniversary piece.

Espeon and Umbreon are two of the most loved Eevee evolutions, and this premium set puts them at the centre of Pokémon\'s 30th-anniversary celebrations. Sold individually, with a lower price from six sets.

- 30th Celebration Premium Deck Set: Espeon & Umbreon
- Anniversary collectible
- Sold individually, with a lower price from 6'],
    ['id'=>'30th-celebration-sylveon-ex-box', 'seo_desc'=>'30th Celebration Sylveon ex Box: a Sylveon ex collection box with booster packs, from Pokémon\'s 30th-anniversary range. Sealed and shipped to the UK.',
     'sku'=>'FK-PRM-SYL-01',
     'name'=>'30th Celebration Sylveon ex Box', 'set'=>'30th Celebration', 'cat'=>'premium', 'cond'=>'Sealed',
     'moq'=>6, 'step'=>6, 'status'=>'in', 'release'=>'', 'weight'=>0.6, 'hidden'=>false,
     'ladder'=>[[1,17.00],[6,15.30],[36,11.50]],
     'desc'=>'A 30th Celebration collection box built around Sylveon ex, with booster packs inside.

The companion to the [Greninja ex Box](product:30th-celebration-greninja-ex-box): an easy gift, a strong single-unit seller for shops, and a lower price from 36 boxes.

- Sylveon ex collection box with booster packs
- Part of the [30th Celebration](set:30th-celebration) range
- Sold in sixes, with the lowest price from 36'],
    ['id'=>'30th-celebration-tech-sticker-collection', 'seo_desc'=>'30th Celebration Tech Sticker Collection, an affordable add-on from Pokémon\'s 30th-anniversary range. Sold in 12s, priced in pounds, shipped to the UK.',
     'sku'=>'FK-PRM-30C-STK',
     'name'=>'30th Celebration Tech Sticker Collection', 'set'=>'30th Celebration', 'cat'=>'premium', 'cond'=>'Sealed',
     'moq'=>12, 'step'=>12, 'status'=>'in', 'release'=>'', 'weight'=>0.25, 'hidden'=>false,
     'ladder'=>[[1,11.60],[12,10.40],[72,7.90]],
     'desc'=>'The 30th Celebration Tech Sticker Collection — an affordable anniversary add-on.

A low-price item that sells well at the counter and in online baskets next to 30th Celebration boxes.

- 30th Celebration sticker collection
- Sold in 12s, with the lowest price from 72'],
    ['id'=>'pitch-black-elite-trainer-box', 'seo_desc'=>'Pokémon TCG Mega Evolution Pitch Black Elite Trainer Box: booster packs, sleeves, dice, damage counters and a storage box. Sealed and shipped to the UK.',
     'sku'=>'FK-ETB-PB-01',
     'name'=>'Pitch Black Elite Trainer Box', 'set'=>'Abyss Eye', 'cat'=>'etb', 'cond'=>'Sealed',
     'moq'=>4, 'step'=>4, 'status'=>'in', 'release'=>'', 'weight'=>0.9, 'hidden'=>false,
     'ladder'=>[[1,35.00],[4,31.50],[24,23.50]],
     'desc'=>'A factory-sealed Pokémon TCG Mega Evolution Pitch Black Elite Trainer Box.

Pitch Black is part of the Pokémon Trading Card Game\'s Mega Evolution series. An Elite Trainer Box is the complete way to start a new set: booster packs plus card sleeves, dice, damage counters and a storage box. Related Japanese product is in our [Abyss Eye](set:abyss-eye) range.

- Sealed Mega Evolution Pitch Black Elite Trainer Box
- Booster packs, sleeves, dice, damage counters and storage box
- Sold in fours, with the lowest price from 24'],
    ['id'=>'chaos-rising-elite-trainer-box', 'seo_title'=>'Pokémon Chaos Rising Elite Trainer Box (Mega Evolution)', 'seo_desc'=>'Pokémon Chaos Rising Elite Trainer Box from the Mega Evolution series: booster packs, sleeves, dice and a storage box. Sealed and shipped to the UK.',
     'sku'=>'FK-ETB-CR-01',
     'name'=>'Chaos Rising Elite Trainer Box', 'set'=>'Ninja Spinner', 'cat'=>'etb', 'cond'=>'Sealed',
     'moq'=>4, 'step'=>4, 'status'=>'in', 'release'=>'', 'weight'=>0.9, 'hidden'=>false,
     'ladder'=>[[1,35.00],[4,31.50],[24,23.50]],
     'desc'=>'A factory-sealed Pokémon TCG Mega Evolution Chaos Rising Elite Trainer Box.

Chaos Rising is part of the Pokémon Trading Card Game\'s Mega Evolution series. An Elite Trainer Box is the complete way to start a new set: booster packs plus card sleeves, dice, damage counters and a storage box. Related Japanese product is in our [Ninja Spinner](set:ninja-spinner) range.

- Sealed Mega Evolution Chaos Rising Elite Trainer Box
- Booster packs, sleeves, dice, damage counters and storage box
- Sold in fours, with the lowest price from 24'],
    ['id'=>'perfect-order-elite-trainer-box', 'seo_desc'=>'Pokémon TCG Mega Evolution Perfect Order Elite Trainer Box, headlined by Mega Zygarde ex: booster packs, sleeves, dice and storage box. Shipped to the UK.',
     'sku'=>'FK-ETB-PO-01',
     'name'=>'Perfect Order Elite Trainer Box', 'set'=>'Nihil Zero', 'cat'=>'etb', 'cond'=>'Sealed',
     'moq'=>4, 'step'=>4, 'status'=>'in', 'release'=>'', 'weight'=>0.9, 'hidden'=>false,
     'ladder'=>[[1,35.00],[4,31.50],[24,23.50]],
     'desc'=>'A factory-sealed Pokémon TCG Mega Evolution Perfect Order Elite Trainer Box.

Perfect Order is part of the Pokémon Trading Card Game\'s Mega Evolution series. An Elite Trainer Box is the complete way to start a new set: booster packs plus card sleeves, dice, damage counters and a storage box. Related Japanese product is in our [Nihil Zero](set:nihil-zero) range.

- Sealed Mega Evolution Perfect Order Elite Trainer Box
- Booster packs, sleeves, dice, damage counters and storage box
- Sold in fours, with the lowest price from 24'],
    ['id'=>'mega-evolution-ascended-heroes-elite-trainer-box', 'seo_desc'=>'Pokémon TCG Mega Evolution Ascended Heroes Elite Trainer Box: booster packs, sleeves, dice, damage counters and a storage box. Sealed and shipped to the UK.',
     'sku'=>'FK-ETB-AH-01',
     'name'=>'Mega Evolution: Ascended Heroes Elite Trainer Box', 'set'=>'Mega Dream ex', 'cat'=>'etb', 'cond'=>'Sealed',
     'moq'=>4, 'step'=>4, 'status'=>'in', 'release'=>'', 'weight'=>0.9, 'hidden'=>false,
     'ladder'=>[[1,35.00],[4,31.50],[24,23.50]],
     'desc'=>'A factory-sealed Pokémon TCG Mega Evolution Ascended Heroes Elite Trainer Box.

Ascended Heroes is part of the Pokémon Trading Card Game\'s Mega Evolution series. An Elite Trainer Box is the complete way to start a new set: booster packs plus card sleeves, dice, damage counters and a storage box. Related Japanese product is in our [Mega Dream ex](set:mega-dream-ex) range.

- Sealed Mega Evolution Ascended Heroes Elite Trainer Box
- Booster packs, sleeves, dice, damage counters and storage box
- Sold in fours, with the lowest price from 24'],
    ['id'=>'30th-celebration-elite-trainer-box-case-10-ct', 'seo_desc'=>'Sealed case of 10 Pokémon TCG 30th Celebration Elite Trainer Boxes, priced per case, with a lower price from six cases. Shipped to the UK.',
     'sku'=>'FK-ETB-30C-C10',
     'name'=>'30th Celebration Elite Trainer Box Case (10-ct)', 'set'=>'30th Celebration', 'cat'=>'etb', 'cond'=>'Sealed',
     'moq'=>1, 'step'=>1, 'status'=>'in', 'release'=>'', 'weight'=>10, 'hidden'=>false,
     'ladder'=>[[1,348.00],[6,262.00]],
     'desc'=>'A sealed case of 10 Pokémon TCG: 30th Celebration Elite Trainer Boxes, priced per case.

The most economical way to stock anniversary ETBs: ten boxes in one sealed case, with the price per case falling sharply from six cases. Each Elite Trainer Box holds 9 booster packs, a full-art Nidorina promo, 65 sleeves, dice and a player\'s guide.

- 10 sealed 30th Celebration Elite Trainer Boxes per case
- Each box: 9 booster packs, Nidorina promo, 16 foil Energy, 65 sleeves, dice, coin and collector\'s box
- Price per case drops from 6 cases
- Also sold [in fours](product:30th-celebration-elite-trainer-box)'],
    ['id'=>'mega-dream-ex-m2a-booster-box', 'seo_desc'=>'Sealed Japanese Mega Dream ex (M2A) booster box, the special set of the Mega Evolution series and home of the Mega Gengar ex SIR. Shipped to the UK.',
     'sku'=>'FK-BB-M2A-01',
     'name'=>'Mega Dream ex (M2A) Booster Box', 'set'=>'Mega Dream ex', 'cat'=>'boxes', 'cond'=>'Sealed',
     'moq'=>6, 'step'=>6, 'status'=>'in', 'release'=>'', 'weight'=>0.4, 'hidden'=>false,
     'ladder'=>[[1,138.00],[6,118.00],[24,107.00]],
     'desc'=>'A factory-sealed Japanese Mega Dream ex (M2A) booster box — the special set of the Mega Evolution series.

Mega Dream ex is home to the [Mega Gengar ex Special Illustration Rare](product:mega-gengar-ex-sir), one of the standout cards of the Mega Evolution era, which makes these boxes a favourite with collectors chasing it.

- Sealed Japanese Mega Dream ex booster box (M2A)
- Chase card: Mega Gengar ex Special Illustration Rare
- Best price per box from 24 boxes
- See all [Gengar Pokémon cards](cards:gengar-pokemon-cards)'],
    ['id'=>'inferno-x-booster-box', 'seo_desc'=>'Sealed Japanese Inferno X (M2) booster box, the second main set of the Mega Evolution series, with new Mega Pokémon ex. Shipped from Japan to the UK.',
     'sku'=>'FK-BB-INFX-01',
     'name'=>'Inferno X Booster Box', 'set'=>'Inferno X', 'cat'=>'boxes', 'cond'=>'Sealed',
     'moq'=>6, 'step'=>6, 'status'=>'in', 'release'=>'', 'weight'=>0.4, 'hidden'=>false,
     'ladder'=>[[1,172.00],[6,164.00],[36,147.00]],
     'desc'=>'A factory-sealed Japanese Inferno X (M2) booster box, the second main set of the Mega Evolution series.

Inferno X continues the Mega Evolution era with new Mega Pokémon ex and full art rares. Sealed boxes suit players building decks, collectors chasing the set\'s rare cards, and shops restocking current Japanese product.

- Sealed Japanese booster box — Inferno X (M2)
- Part of the [Mega Evolution series](set:mega-evolution)
- Sold in sixes, with the best price from 36 boxes'],
    ['id'=>'mega-symphonia-booster-box', 'seo_desc'=>'Sealed Japanese Mega Symphonia (M1S) booster box, one of the twin sets that launched the Mega Evolution series in 2025. Shipped from Japan to the UK.',
     'sku'=>'FK-BB-M1S-01',
     'name'=>'Mega Symphonia Booster Box', 'set'=>'Mega Symphonia', 'cat'=>'boxes', 'cond'=>'Sealed',
     'moq'=>6, 'step'=>6, 'status'=>'in', 'release'=>'', 'weight'=>0.4, 'hidden'=>false,
     'ladder'=>[[1,87.00],[6,83.00],[36,74.50]],
     'desc'=>'A factory-sealed Japanese Mega Symphonia (M1S) booster box — one of the twin sets that launched the Mega Evolution series in 2025.

Mega Symphonia and its partner set [Mega Brave](product:mega-brave-booster-box) brought Mega Evolution back to the Pokémon TCG, and many collectors buy the two together to complete the launch of the era.

- Sealed Japanese booster box — Mega Symphonia (M1S)
- Twin set of Mega Brave (M1L)
- Sold in sixes, with the best price from 36 boxes'],
    ['id'=>'mega-brave-booster-box', 'seo_desc'=>'Sealed Japanese Mega Brave (M1L) booster box, one of the twin sets that launched the Mega Evolution series in 2025. Shipped from Japan to the UK.',
     'sku'=>'FK-BB-M1L-01',
     'name'=>'Mega Brave Booster Box', 'set'=>'Mega Brave', 'cat'=>'boxes', 'cond'=>'Sealed',
     'moq'=>6, 'step'=>6, 'status'=>'in', 'release'=>'', 'weight'=>0.4, 'hidden'=>false,
     'ladder'=>[[1,95.00],[6,90.50],[36,81.00]],
     'desc'=>'A factory-sealed Japanese Mega Brave (M1L) booster box — one of the twin sets that launched the Mega Evolution series in 2025.

Mega Brave and its partner set [Mega Symphonia](product:mega-symphonia-booster-box) brought Mega Evolution back to the Pokémon TCG, and many collectors buy the two together to complete the launch of the era.

- Sealed Japanese booster box — Mega Brave (M1L)
- Twin set of Mega Symphonia (M1S)
- Sold in sixes, with the best price from 36 boxes'],
    ['id'=>'abyss-eye-m5-booster-box', 'seo_desc'=>'Sealed Japanese Abyss Eye (M5) booster box from the Mega Evolution series, featuring Mega Darkrai ex and Mega Excadrill ex. Shipped to the UK.',
     'sku'=>'FK-BB-M5-01',
     'name'=>'Abyss Eye (M5) Booster Box', 'set'=>'Abyss Eye', 'cat'=>'boxes', 'cond'=>'Sealed',
     'moq'=>6, 'step'=>6, 'status'=>'in', 'release'=>'', 'weight'=>0.4, 'hidden'=>false,
     'ladder'=>[[1,156.00],[6,133.00],[24,120.00]],
     'desc'=>'A factory-sealed Japanese Abyss Eye (M5) booster box from the Mega Evolution series.

Abyss Eye features [Mega Darkrai ex](product:mega-darkrai-ex-japanese) and [Mega Excadrill ex](product:mega-excadrill-ex-japanese) among its Mega Pokémon ex. Open boxes to chase them, or pick up the singles directly.

- Sealed Japanese booster box — Abyss Eye (M5)
- Features Mega Darkrai ex and Mega Excadrill ex
- Best price per box from 24 boxes
- Matching [Abyss Eye Elite Trainer Box](product:abyss-eye-elite-trainer-box)'],
    ['id'=>'ninja-spinner-m4-booster-box', 'seo_desc'=>'Sealed Japanese Ninja Spinner (M4) booster box, with Mega Greninja ex and Mega Floette ex. Limited stock, priced in pounds and shipped to the UK.',
     'sku'=>'FK-BB-M4-01',
     'name'=>'Ninja Spinner (M4) Booster Box', 'set'=>'Ninja Spinner', 'cat'=>'boxes', 'cond'=>'Sealed',
     'moq'=>6, 'step'=>6, 'status'=>'low', 'release'=>'', 'weight'=>0.4, 'hidden'=>false,
     'ladder'=>[[1,130.00],[6,111.00],[24,102.00]],
     'desc'=>'A factory-sealed Japanese Ninja Spinner (M4) booster box — limited stock remaining.

Ninja Spinner brings [Mega Greninja ex](product:mega-greninja-ex-japanese) and [Mega Floette ex](product:mega-floette-ex-japanese) to the Mega Evolution series. Greninja is a long-time fan favourite, and remaining sealed boxes are limited.

- Sealed Japanese booster box — Ninja Spinner (M4)
- Features Mega Greninja ex and Mega Floette ex
- Best price per box from 24 boxes'],
    ['id'=>'nihil-zero-booster-box', 'seo_desc'=>'Sealed Japanese Nihil Zero (M3) booster box from the Mega Evolution series, an affordable way into the era. Priced in pounds, shipped from Japan to the UK.',
     'sku'=>'FK-BB-NZ-01',
     'name'=>'Nihil Zero Booster Box', 'set'=>'Nihil Zero', 'cat'=>'boxes', 'cond'=>'Sealed',
     'moq'=>6, 'step'=>6, 'status'=>'in', 'release'=>'', 'weight'=>0.4, 'hidden'=>false,
     'ladder'=>[[1,66.00],[6,65.50],[36,64.00]],
     'desc'=>'A factory-sealed Japanese Nihil Zero (M3) booster box from the Mega Evolution series.

Nihil Zero is an affordable way into the Mega Evolution era, and its flat price ladder makes it an easy add-on to a larger order.

- Sealed Japanese booster box — Nihil Zero (M3)
- Part of the [Mega Evolution series](set:mega-evolution)
- Sold in sixes'],
    ['id'=>'terastal-festival-booster-box', 'seo_desc'=>'Sealed Japanese Terastal Festival ex (SV8a) booster box, the Eevee-evolutions set released in English as Prismatic Evolutions. Shipped to the UK.',
     'sku'=>'FK-BB-SV8A-01',
     'name'=>'Terastal Festival Booster Box', 'set'=>'Terastal Festival ex', 'cat'=>'boxes', 'cond'=>'Sealed',
     'moq'=>6, 'step'=>6, 'status'=>'in', 'release'=>'', 'weight'=>0.4, 'hidden'=>false,
     'ladder'=>[[1,69.50],[6,67.00],[36,62.00]],
     'desc'=>'A factory-sealed Japanese Terastal Festival ex (SV8a) booster box — the Eevee-evolutions special set of the Scarlet & Violet era.

Terastal Festival ex is built around Terastal Pokémon and all of the Eevee evolutions, and was released in English as Prismatic Evolutions. Japanese boxes hold 10 packs of 10 cards, and the set\'s Special Art Rares — Umbreon ex above all — are some of the most sought-after cards of recent years.

- Sealed Japanese booster box — Terastal Festival ex (SV8a): 10 packs of 10 cards
- Released in English as Prismatic Evolutions
- Sold in sixes, with the lowest price from 36 boxes'],
  ],

  /* countries: '*' = everywhere, or a list of country codes */
  /* type 'bitcoin' = paid on the site to btc_address above; any other method = you send the details */
  'payments' => [
    'ukbank'   => ['label'=>'UK bank transfer', 'note'=>'Pay in pounds by Faster Payments. We text or email our UK bank details with your invoice.', 'countries'=>'*', 'enabled'=>true],
    'crypto'   => ['label'=>'Crypto (Bitcoin, ETH, USDT)', 'note'=>'We text or email our wallet address and the exact amount with your invoice. Network fees are the sender\'s.', 'countries'=>'*', 'enabled'=>true],
    /* Bitcoin paid on the site (QR code and automatic payment check) is off: add a method with type 'bitcoin' to turn it back on. */
  ],

  'countries' => ['GB'=>'United Kingdom','IE'=>'Ireland','US'=>'United States','CA'=>'Canada','AU'=>'Australia','JP'=>'Japan','DE'=>'Germany','FR'=>'France','ES'=>'Spain','IT'=>'Italy','NL'=>'Netherlands','BE'=>'Belgium','SE'=>'Sweden','NO'=>'Norway','DK'=>'Denmark','FI'=>'Finland','PL'=>'Poland','PT'=>'Portugal','CH'=>'Switzerland','AT'=>'Austria','CZ'=>'Czechia','GR'=>'Greece','SG'=>'Singapore','MY'=>'Malaysia','TH'=>'Thailand','PH'=>'Philippines','ID'=>'Indonesia','VN'=>'Vietnam','KR'=>'South Korea','TW'=>'Taiwan','HK'=>'Hong Kong','NZ'=>'New Zealand','MX'=>'Mexico','BR'=>'Brazil','AR'=>'Argentina','CL'=>'Chile','ZA'=>'South Africa','AE'=>'United Arab Emirates','SA'=>'Saudi Arabia','IL'=>'Israel','TR'=>'Turkey','IN'=>'India','NG'=>'Nigeria','KE'=>'Kenya','EG'=>'Egypt','CM'=>'Cameroon','GH'=>'Ghana'],

  /* shipping (GBP) = zone per-order price + per-kg price × order weight, for each delivery option; totals round up
     to whole pounds. A single card from Japan to the UK comes to £9 Standard and £18 Express, and heavier orders
     add per kg. Check these against your carrier's real costs from Japan. */
  'shipping' => [
    'methods'  => ['standard'=>['label'=>'Standard', 'days'=>'4–7 working days'],
                   'express' =>['label'=>'Express',  'days'=>'2–4 working days']],
    'round_up' => true,
    'zones' => [
      ['name'=>'United Kingdom', 'countries'=>['GB'], 'standard'=>['base'=>8.5, 'per_kg'=>6.5], 'express'=>['base'=>17, 'per_kg'=>12.5]],
      ['name'=>'Ireland & Europe', 'countries'=>['IE','DE','FR','ES','IT','NL','BE','SE','NO','DK','FI','PL','PT','CH','AT','CZ','GR'],
                                 'standard'=>['base'=>9.5, 'per_kg'=>7], 'express'=>['base'=>19, 'per_kg'=>14]],
      ['name'=>'United States & Canada', 'countries'=>['US','CA'], 'standard'=>['base'=>8.5, 'per_kg'=>6.5], 'express'=>['base'=>17, 'per_kg'=>12.5]],
      ['name'=>'Asia-Pacific',   'countries'=>['SG','MY','TH','PH','ID','VN','KR','TW','HK','AU','NZ'],
                                 'standard'=>['base'=>6.5, 'per_kg'=>5], 'express'=>['base'=>12.5, 'per_kg'=>9.5]],
      ['name'=>'Japan',          'countries'=>['JP'], 'standard'=>['base'=>4, 'per_kg'=>1.5], 'express'=>['base'=>8, 'per_kg'=>3]],
    ],
    'rest' => ['standard'=>['base'=>12, 'per_kg'=>8.5], 'express'=>['base'=>23.5, 'per_kg'=>17.5]],
  ],


];

$c = require __DIR__.'/content.php';
$d['settings']    = $c['settings'] + $d['settings'];
$d['categories']  = $c['categories'];
$d['faqs']        = $c['faqs'];
$d['series']      = $c['series'];
$d['sets']        = $c['sets'];
$d['collections'] = $c['collections'];
$d['guides']      = $c['guides'];
$d['pages']       = $c['pages'];
return $d;
