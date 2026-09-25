<?php
/* Starting data for a fresh install. On first load it is copied to data/store.php,
   and from then on everything is edited in admin.php — not here.
   Category, series, set, collection and guide text lives in content.php. */
if(!defined('FK_ROOT')){ http_response_code(404); exit; }

$d = [
  'settings' => [
    'brand'         => 'FUDAKURA',
    'kanji'         => '札蔵',
    'tagline'       => 'Japanese Pokémon cards, shipped from Japan',
    'legal_name'    => 'Fudakura',
    'address'       => 'Japan',
    'email'         => 'support@fudakura.store',
    'phone'         => '',
    'order_email'   => 'support@fudakura.store',
    'domain'        => 'https://fudakura.store',
    'reply_hours'   => 12,
    'hold_hours'    => 48,
    'min_order_usd' => 100,
    'strip_text'    => 'Sealed product, sourced in Japan · Quantity breaks published on every listing',
    'strip_link_text' => 'Aura Seeker preorders open →',
    'strip_link_url'  => 'index.php?p=product&id=aura-seeker-booster-box',
    'hero_title'    => 'Wholesale Japanese Pokémon cards, shipped worldwide from Japan.',
    'hero_lede'     => 'Sealed booster boxes, Elite Trainer Boxes, premium sets and singles, bought through Japanese distribution and priced by the case. Every quantity break is published — price a full order before you talk to anyone.',
    'footer_blurb'  => 'Wholesale Japanese Pokémon TCG, shipped worldwide from Japan to retailers, resellers and card shops. Pricing published on every product — order direct, no account needed.',
    'shipping_reviewed' => false,
    'pretty_urls'   => false,            /* clean addresses like /products/151-booster-box; turn on in Admin → Settings */
    'free_ship_usd' => 2000,             /* free Standard shipping from this goods total (USD); 0 = off */
    'content_version' => 2,              /* set by the shop: which built-in content updates are applied */
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

  'currencies' => [
    'USD' => ['rate'=>1,    'sym'=>'$',   'dec'=>2],
    'EUR' => ['rate'=>0.87635, 'sym'=>'€', 'dec'=>2],
    'JPY' => ['rate'=>156,  'sym'=>'¥',   'dec'=>0],
    'GBP' => ['rate'=>0.79, 'sym'=>'£',   'dec'=>2],
    'CAD' => ['rate'=>1.37, 'sym'=>'CA$', 'dec'=>2],
    'AUD' => ['rate'=>1.52, 'sym'=>'A$',  'dec'=>2],
  ],


  /* status: in | new | low | preorder | soldout.  cond: Sealed | Graded | Near Mint | Lightly Played.
     ladder: [min_qty, unit_price_usd] ascending.  weight: kg per unit (used for shipping) */
  'products' => [
    ['id'=>'storm-emeralda-elite-trainer-box', 'sku'=>'FK-ETB-SE-01',
     'name'=>'Storm Emeralda Elite Trainer Box', 'set'=>'Storm Emeralda', 'cat'=>'etb', 'cond'=>'Sealed',
     'moq'=>6, 'step'=>6, 'status'=>'in', 'release'=>'', 'weight'=>0.9, 'hidden'=>false,
     'ladder'=>[[1,45.00],[6,38.00],[18,36.00],[36,34.00]],
     'desc'=>'A factory-sealed Storm Emeralda Elite Trainer Box from the Japanese Mega Evolution series.

Inside are booster packs plus card sleeves, dice, damage counters, energy cards and the set promo, packed in a storage box — everything needed to open and play Storm Emeralda. A steady seller next to [Storm Emeralda booster boxes](product:storm-emeralda-m6-booster-box).

- Sealed Storm Emeralda Elite Trainer Box
- Booster packs, sleeves, dice, damage counters, energy cards and promo
- Sold in sixes, with lower prices from 18 and 36'],
    ['id'=>'30th-celebration-m6a-booster-box', 'sku'=>'FK-BB-M6A-01',
     'name'=>'30th Celebration (M6A) Booster Box', 'set'=>'30th Celebration', 'cat'=>'boxes', 'cond'=>'Sealed',
     'moq'=>6, 'step'=>6, 'status'=>'in', 'release'=>'', 'weight'=>0.4, 'hidden'=>false,
     'ladder'=>[[1,327.00],[6,279.00],[12,267.00],[24,255.00]],
     'desc'=>'A factory-sealed Japanese booster box from 30th Celebration (M6A), the set marking Pokémon\'s 30th anniversary.

30th Celebration brings back classic Pokémon illustrations alongside new cards and has a high pull rate, which makes anniversary boxes a favourite with collectors and box openers alike. Open them for the pulls, or keep boxes sealed as an anniversary collectible.

- Sealed Japanese booster box — 30th Celebration (M6A)
- Reprinted classic illustrations and a high pull rate
- Lower prices from 12 and 24 boxes
- More from the anniversary range: [30th Celebration Elite Trainer Box](product:30th-celebration-elite-trainer-box) and [special boxes](set:30th-celebration)'],
    ['id'=>'mega-rayquaza-ex-mur', 'sku'=>'FK-SGL-MRAY-MUR',
     'name'=>'Mega Rayquaza ex — Master Ultra Rare (Japanese)', 'set'=>'Storm Emeralda', 'cat'=>'singles', 'cond'=>'Near Mint',
     'moq'=>1, 'step'=>1, 'status'=>'in', 'release'=>'', 'weight'=>0.05, 'hidden'=>false,
     'ladder'=>[[1,1207.30],[3,1150.00],[6,1092.90]],
     'desc'=>'The Japanese Mega Rayquaza ex Master Ultra Rare from Storm Emeralda — the top-rarity chase card of the set.

Rayquaza has been one of the Pokémon TCG\'s most collected legends since its first cards, and its Master Ultra Rare sits at the very top of Storm Emeralda\'s rarity chart. Every copy is Near Mint, sleeved and toploaded on dispatch, with any condition notes on your invoice.

- Japanese — Storm Emeralda (M6)
- Rarity: Master Ultra Rare
- Near Mint, sleeved and toploaded
- Lower prices from 3 and 6 copies'],
    ['id'=>'mega-gengar-ex-sir', 'sku'=>'FK-SGL-MGEN-SIR',
     'name'=>'Mega Gengar ex — Special Illustration Rare', 'set'=>'Mega Dream ex', 'cat'=>'singles', 'cond'=>'Near Mint',
     'moq'=>1, 'step'=>1, 'status'=>'in', 'release'=>'', 'weight'=>0.05, 'hidden'=>false,
     'ladder'=>[[1,1033.50],[3,985.00],[6,935.60]],
     'desc'=>'The Japanese Mega Gengar ex Special Illustration Rare from Mega Dream ex.

Gengar has one of the most loyal followings in the hobby, and this Special Illustration Rare is one of the standout Gengar Pokémon cards of the Mega Evolution series. Near Mint, sleeved and toploaded on dispatch.

- Japanese — Mega Dream ex (M2A)
- Special Illustration Rare (full art)
- Lower prices from 3 and 6 copies
- See all [Gengar Pokémon cards](cards:gengar-pokemon-cards)'],
    ['id'=>'30th-celebration-elite-trainer-box', 'sku'=>'FK-ETB-30C-01',
     'name'=>'30th Celebration Elite Trainer Box', 'set'=>'30th Celebration', 'cat'=>'etb', 'cond'=>'Sealed',
     'moq'=>4, 'step'=>4, 'status'=>'in', 'release'=>'', 'weight'=>0.9, 'hidden'=>false,
     'ladder'=>[[1,48.99],[4,44.00],[24,33.20]],
     'desc'=>'The Pokémon TCG: 30th Celebration Elite Trainer Box — nine booster packs of the 30th-anniversary expansion plus everything needed to play them.

30th Celebration marks thirty years of Pokémon: Mewtwo ex and Mew ex lead the set, joined by Umbreon ex, Salamence ex and Greninja ex, and every booster pack contains a Pikachu — with 30 different Pikachu rare cards to collect. Buying in volume? See the [sealed 10-box case](product:30th-celebration-elite-trainer-box-case-10-ct).

- 9 Pokémon TCG: 30th Celebration booster packs
- 1 full-art foil promo card featuring Nidorina
- 16 foil Basic Energy cards and 65 card sleeves
- A player\'s guide to the 30th Celebration expansion
- 6 damage-counter dice, 1 competition-legal coin-flip die and 1 plastic coin
- A collector\'s box with 6 dividers, plus a code card for Pokémon TCG Live
- Sold in fours, with the lowest price from 24'],
    ['id'=>'storm-emeralda-m6-booster-box', 'sku'=>'FK-BB-M6-01',
     'name'=>'Storm Emeralda (M6) Booster Box', 'set'=>'Storm Emeralda', 'cat'=>'boxes', 'cond'=>'Sealed',
     'moq'=>6, 'step'=>6, 'status'=>'in', 'release'=>'', 'weight'=>0.4, 'hidden'=>false,
     'ladder'=>[[1,218.00],[6,185.00],[24,170.00]],
     'desc'=>'A factory-sealed Japanese booster box of Storm Emeralda (M6), the Mega Evolution set headlined by Mega Rayquaza ex.

Storm Emeralda\'s chase cards include the [Mega Rayquaza ex Master Ultra Rare](product:mega-rayquaza-ex-mur) and [Special Illustration Rare](product:mega-rayquaza-ex-sar-245-191-near-mint), which keeps sealed boxes in demand with collectors and box breakers. Order in multiples of six, or take a [sealed 12-box case](product:storm-emeralda-booster-box-case-12-ct) for the lowest price per box.

- Sealed Japanese Mega Evolution-series booster box (M6)
- Chase cards: Mega Rayquaza ex Master Ultra Rare and Special Illustration Rare
- Best price per box from 24 boxes'],
    ['id'=>'abyss-eye-elite-trainer-box', 'sku'=>'FK-ETB-AE-01',
     'name'=>'Abyss Eye Elite Trainer Box', 'set'=>'Abyss Eye', 'cat'=>'etb', 'cond'=>'Sealed',
     'moq'=>6, 'step'=>6, 'status'=>'in', 'release'=>'', 'weight'=>0.9, 'hidden'=>false,
     'ladder'=>[[1,42.00],[6,35.50],[18,33.60],[36,32.00]],
     'desc'=>'A factory-sealed Abyss Eye Elite Trainer Box from the Japanese Mega Evolution series.

Booster packs with sleeves, dice, damage counters and the promo card in a storage box — the easy way to open Abyss Eye alongside [booster boxes](product:abyss-eye-m5-booster-box).

- Sealed Abyss Eye Elite Trainer Box
- Sold in sixes, with lower prices from 18 and 36'],
    ['id'=>'30th-celebration-greninja-ex-box', 'sku'=>'FK-PRM-GRE-01',
     'name'=>'30th Celebration Greninja ex Box', 'set'=>'30th Celebration', 'cat'=>'premium', 'cond'=>'Sealed',
     'moq'=>6, 'step'=>6, 'status'=>'in', 'release'=>'', 'weight'=>0.6, 'hidden'=>false,
     'ladder'=>[[1,21.56],[6,19.40],[18,16.80],[36,14.60]],
     'desc'=>'A 30th Celebration collection box built around a Greninja ex promo card, with booster packs inside.

Collection boxes like this sell well as single units and gifts, and case pricing from 18 and 36 boxes leaves room for margin. It pairs naturally with the [Sylveon ex Box](product:30th-celebration-sylveon-ex-box).

- Greninja ex promo card plus booster packs
- Part of the [30th Celebration](set:30th-celebration) range
- Sold in sixes, with lower prices from 18 and 36'],
    ['id'=>'heat-wave-arena-sv9a-booster-box', 'sku'=>'FK-BB-SV9A-01',
     'name'=>'Heat Wave Arena (SV9a) Booster Box', 'set'=>'Heat Wave Arena', 'cat'=>'boxes', 'cond'=>'Sealed',
     'moq'=>6, 'step'=>6, 'status'=>'in', 'release'=>'', 'weight'=>0.4, 'hidden'=>false,
     'ladder'=>[[1,207.11],[6,197.70],[18,186.00],[36,177.00]],
     'desc'=>'A factory-sealed Japanese Heat Wave Arena (SV9a) booster box from the Scarlet & Violet series.

Heat Wave Arena is a dependable reorder line for card shops running drafts and league nights, and a steady opener for collectors of Scarlet & Violet-era Japanese product.

- Sealed Japanese booster box — Heat Wave Arena (SV9a)
- Part of the [Scarlet & Violet series](set:scarlet-violet)
- Lower prices from 18 and 36 boxes'],
    ['id'=>'glory-of-team-rocket-sv10-booster-box', 'sku'=>'FK-BB-SV10-01',
     'name'=>'Glory of Team Rocket (SV10) Booster Box', 'set'=>'Glory of Team Rocket', 'cat'=>'boxes', 'cond'=>'Sealed',
     'moq'=>6, 'step'=>6, 'status'=>'in', 'release'=>'', 'weight'=>0.4, 'hidden'=>false,
     'ladder'=>[[1,261.40],[6,249.50],[18,235.00],[36,223.40]],
     'desc'=>'A factory-sealed Japanese Glory of Team Rocket (SV10) booster box — Team Rocket\'s Pokémon return to the TCG.

Glory of Team Rocket is one of the most in-demand Japanese Scarlet & Violet sets, built around the Pokémon of Team Rocket. Allocation sells quickly.

- Sealed Japanese booster box — Glory of Team Rocket (SV10)
- Part of the [Scarlet & Violet series](set:scarlet-violet)
- Lower prices from 18 and 36 boxes'],
    ['id'=>'mega-start-deck-100-battle-collection', 'sku'=>'FK-PRM-MSD100-01',
     'name'=>'MEGA Start Deck 100 Battle Collection', 'set'=>'', 'cat'=>'premium', 'cond'=>'Sealed',
     'moq'=>12, 'step'=>12, 'status'=>'in', 'release'=>'', 'weight'=>1.2, 'hidden'=>false,
     'ladder'=>[[1,37.11],[12,35.40],[72,31.70]],
     'desc'=>'The MEGA Start Deck 100 Battle Collection: ready-to-play Pokémon TCG decks for new players.

Start decks are the easiest way to learn how to play the Pokémon card game — open the box and play, with no deck-building needed. For card shops they\'re a low-cost entry product for league nights and new-player events.

- Ready-to-play decks from the Mega Evolution era
- Sold by the case of 12, with the lowest price from 72'],
    ['id'=>'starter-set-ex-zorua-and-zoroark-ex', 'sku'=>'FK-PRM-SSZOR-01',
     'name'=>'Starter Set ex — Zorua & Zoroark ex', 'set'=>'', 'cat'=>'premium', 'cond'=>'Sealed',
     'moq'=>12, 'step'=>12, 'status'=>'in', 'release'=>'', 'weight'=>0.35, 'hidden'=>false,
     'ladder'=>[[1,37.11],[12,35.40],[72,31.70]],
     'desc'=>'Starter Set ex with a ready-to-play Zorua and Zoroark ex deck.

A complete deck built around Zoroark ex, ready to play straight from the box — a good first deck for new players and an easy add-on next to booster boxes.

- Ready-to-play Zorua & Zoroark ex deck
- Sold by the case of 12, with the lowest price from 72'],
    ['id'=>'premium-trainer-box-mega', 'sku'=>'FK-PRM-PTBM-01',
     'name'=>'Premium Trainer Box MEGA', 'set'=>'', 'cat'=>'premium', 'cond'=>'Sealed',
     'moq'=>4, 'step'=>4, 'status'=>'in', 'release'=>'', 'weight'=>0.9, 'hidden'=>false,
     'ladder'=>[[1,91.40],[4,87.20],[24,78.10]],
     'desc'=>'Premium Trainer Box MEGA — a premium trainer box from the Pokémon TCG Mega Evolution era.

Premium trainer boxes combine booster packs with play accessories for players building their first Mega Evolution decks, and make a strong gift.

- Mega Evolution-era premium trainer box
- Sold in fours, with the lowest per-unit price from 24'],
    ['id'=>'151-booster-box', 'sku'=>'FK-BB-SV2A-01',
     'name'=>'151 Booster Box', 'set'=>'151', 'cat'=>'boxes', 'cond'=>'Sealed',
     'moq'=>6, 'step'=>6, 'status'=>'in', 'release'=>'', 'weight'=>0.4, 'hidden'=>false,
     'ladder'=>[[1,445.71],[6,425.40],[36,380.90]],
     'desc'=>'A factory-sealed Japanese 151 (SV2a) booster box — 20 packs of 7 cards from the set that revisits the original 151 Pokémon.

151 Pokémon cards are some of the most collected of the Scarlet & Violet era: the original Pokémon from Bulbasaur to Mew, in modern artwork with full art rares. The [Charizard ex Special Illustration Rare](product:151-charizard-ex-special-illustration-rare) is the chase card. 151 was later released in English as Scarlet & Violet—151, but the Japanese version came first and many collectors prefer it.

- Sealed Japanese 151 booster box (SV2a): 20 packs of 7 cards
- Chase card: Charizard ex Special Illustration Rare
- Sold in sixes, with the lowest price from 36 boxes
- New to Japanese boxes? Read [what\'s different about Japanese Pokémon cards](guide:japanese-pokemon-cards)'],
    ['id'=>'151-charizard-ex-special-illustration-rare', 'sku'=>'FK-SGL-CHAR-151',
     'name'=>'151 Charizard ex — Special Illustration Rare', 'set'=>'151', 'cat'=>'singles', 'cond'=>'Near Mint',
     'moq'=>1, 'step'=>1, 'status'=>'in', 'release'=>'', 'weight'=>0.05, 'hidden'=>false,
     'ladder'=>[[1,328.80],[6,297.70]],
     'desc'=>'The Japanese Charizard ex Special Illustration Rare from 151 (SV2a).

Charizard is one of the most collected Pokémon in the TCG, and its 151 Special Illustration Rare is one of the defining Charizard Pokémon cards of the Scarlet & Violet era. Near Mint, sleeved and toploaded on dispatch.

- Japanese — 151 (SV2a)
- Special Illustration Rare (full art)
- Lower price from 6 copies
- More [Charizard Pokémon cards](cards:charizard-pokemon-cards)'],
    ['id'=>'storm-emeralda-booster-box-case-12-ct', 'sku'=>'FK-BB-M6-C12',
     'name'=>'Storm Emeralda Booster Box Case (12-ct)', 'set'=>'Storm Emeralda', 'cat'=>'boxes', 'cond'=>'Sealed',
     'moq'=>1, 'step'=>1, 'status'=>'in', 'release'=>'', 'weight'=>5.6, 'hidden'=>false,
     'ladder'=>[[1,1635.00],[6,1463.70]],
     'desc'=>'A factory-sealed case of 12 Japanese Storm Emeralda (M6) booster boxes, priced per case.

For shops and box breakers stocking Storm Emeralda in volume, a sealed case is the simplest buy: twelve booster boxes in one sealed case at a lower price per box than buying boxes individually, falling further from six cases.

- 12 sealed Japanese Storm Emeralda (M6) booster boxes per case
- Lower price per box than single boxes, with a further break from 6 cases
- Chase card: [Mega Rayquaza ex Master Ultra Rare](product:mega-rayquaza-ex-mur)'],
    ['id'=>'aura-seeker-booster-box', 'sku'=>'FK-BB-MLZ-01',
     'name'=>'Aura Seeker (Hadou Seeker) Booster Box', 'set'=>'Aura Seeker (Hadou Seeker)', 'cat'=>'boxes', 'cond'=>'Sealed',
     'moq'=>6, 'step'=>6, 'status'=>'preorder', 'release'=>'November 2026', 'weight'=>0.4, 'hidden'=>false,
     'ladder'=>[[1,121.43],[6,115.90],[18,109.50],[36,103.80]],
     'desc'=>'Preorder the Japanese Aura Seeker (Hadou Seeker) booster box, an upcoming Mega Evolution-series set.

A preorder reserves your allocation ahead of release at the quantity-break prices shown. You are invoiced when stock is allocated, not when you order, and boxes ship from Japan once released and paid.

- Sealed Japanese booster box — preorder
- Nothing to pay today: invoiced at allocation
- Sold in sixes, with the lowest price from 36 boxes'],
    ['id'=>'starter-set-ex-eevee-ex', 'sku'=>'FK-PRM-SSEEV-01',
     'name'=>'Starter Set ex — Eevee ex', 'set'=>'', 'cat'=>'premium', 'cond'=>'Sealed',
     'moq'=>10, 'step'=>10, 'status'=>'in', 'release'=>'', 'weight'=>0.35, 'hidden'=>false,
     'ladder'=>[[1,75.00],[10,59.00],[60,51.00]],
     'desc'=>'Starter Set ex with a ready-to-play Eevee ex deck.

A complete deck built around Eevee ex, ready to play straight from the box — an easy first purchase for new players and a popular gift.

- Ready-to-play Eevee ex deck
- Sold by the case of 10, with the lowest price from 60'],
    ['id'=>'pokemon-tcg-card-storage-box-booster-box-display', 'sku'=>'FK-ACC-STOR-01',
     'name'=>'Pokémon TCG Card Storage Box — Booster Box Display', 'set'=>'', 'cat'=>'accessories', 'cond'=>'Sealed',
     'moq'=>6, 'step'=>6, 'status'=>'in', 'release'=>'', 'weight'=>0.25, 'hidden'=>false,
     'ladder'=>[[1,8.82],[6,7.90],[36,6.00]],
     'desc'=>'A Pokémon TCG card storage box styled like a booster box display.

It keeps sleeved or loose cards upright and flat on a shelf or counter, and doubles as a display piece. Pokémon cards are 63 × 88 mm — see our [Pokémon card size guide](guide:pokemon-card-size).

- Card storage box in booster box display style
- Sold in sixes, with the lowest price from 36'],
    ['id'=>'pokemon-tcg-9-pocket-binder-mega-evolution-series', 'sku'=>'FK-ACC-BIND-ME',
     'name'=>'Pokémon TCG 9-Pocket Binder — Mega Evolution Series', 'set'=>'', 'cat'=>'accessories', 'cond'=>'Sealed',
     'moq'=>6, 'step'=>6, 'status'=>'in', 'release'=>'', 'weight'=>0.45, 'hidden'=>false,
     'ladder'=>[[1,31.36],[6,27.90],[36,20.30]],
     'desc'=>'A 9-pocket Pokémon card binder with Mega Evolution series artwork.

Each page holds nine standard-size Pokémon cards, so a binder is the simplest way to organise a growing collection and show off full art pulls. Single-sleeved cards fit comfortably — see our [card size guide](guide:pokemon-card-size) for sleeve sizes.

- 9-pocket Pokémon card binder
- Mega Evolution series artwork
- Sold in sixes, with the lowest price from 36'],
    ['id'=>'celebi-and-furret-deck-sleeves', 'sku'=>'FK-ACC-SLV-CELF',
     'name'=>'Celebi & Furret Deck Sleeves', 'set'=>'', 'cat'=>'accessories', 'cond'=>'Sealed',
     'moq'=>24, 'step'=>24, 'status'=>'in', 'release'=>'', 'weight'=>0.06, 'hidden'=>false,
     'ladder'=>[[1,12.73],[24,11.50],[144,8.60]],
     'desc'=>'Deck sleeves with Celebi and Furret artwork, sized for standard Pokémon cards.

Sleeves protect cards in play and in storage, and character designs like these are popular add-ons at the counter.

- Fits standard 63 × 88 mm Pokémon cards
- Sold in 24s, with the lowest price from 144'],
    ['id'=>'pikachu-ditto-ver-deck-sleeves', 'sku'=>'FK-ACC-SLV-PIKD',
     'name'=>'Pikachu (Ditto Ver.) Deck Sleeves', 'set'=>'', 'cat'=>'accessories', 'cond'=>'Sealed',
     'moq'=>24, 'step'=>24, 'status'=>'in', 'release'=>'', 'weight'=>0.06, 'hidden'=>false,
     'ladder'=>[[1,12.73],[24,11.50],[144,8.60]],
     'desc'=>'Deck sleeves with Pikachu in its Ditto version artwork, sized for standard Pokémon cards.

Sleeves protect cards in play and in storage, and character designs like these are popular add-ons at the counter.

- Fits standard 63 × 88 mm Pokémon cards
- Sold in 24s, with the lowest price from 144'],
    ['id'=>'storm-emeralda-mega-rayquaza-deck-sleeves', 'sku'=>'FK-ACC-SLV-MRAY',
     'name'=>'Storm Emeralda Mega Rayquaza Deck Sleeves', 'set'=>'Storm Emeralda', 'cat'=>'accessories', 'cond'=>'Sealed',
     'moq'=>24, 'step'=>24, 'status'=>'in', 'release'=>'', 'weight'=>0.06, 'hidden'=>false,
     'ladder'=>[[1,12.73],[24,11.50],[144,8.60]],
     'desc'=>'Deck sleeves with Mega Rayquaza artwork from the Storm Emeralda release, sized for standard Pokémon cards.

Sleeves protect cards in play and in storage, and character designs like these are popular add-ons at the counter. They\'re the natural add-on to [Storm Emeralda booster boxes](product:storm-emeralda-m6-booster-box).

- Fits standard 63 × 88 mm Pokémon cards
- Sold in 24s, with the lowest price from 144'],
    ['id'=>'ultra-pro-pikachu-alcove-tower-deck-box', 'sku'=>'FK-ACC-UP-ALCT',
     'name'=>'Ultra PRO Pikachu Alcove Tower Deck Box', 'set'=>'', 'cat'=>'accessories', 'cond'=>'Sealed',
     'moq'=>12, 'step'=>12, 'status'=>'in', 'release'=>'', 'weight'=>0.2, 'hidden'=>false,
     'ladder'=>[[1,20.59],[12,18.50],[72,13.90]],
     'desc'=>'An Ultra PRO Alcove Tower deck box with Pikachu artwork.

A sturdy deck box to carry a sleeved Pokémon deck to league nights and tournaments, with Pikachu artwork that sells itself.

- Ultra PRO Alcove Tower deck box
- Pikachu artwork
- Sold in 12s, with the lowest price from 72'],
    ['id'=>'ultra-pro-pikachu-deck-protector-sleeves-65-ct', 'sku'=>'FK-ACC-UP-SLV65',
     'name'=>'Ultra PRO Pikachu Deck Protector Sleeves (65 ct)', 'set'=>'', 'cat'=>'accessories', 'cond'=>'Sealed',
     'moq'=>24, 'step'=>24, 'status'=>'in', 'release'=>'', 'weight'=>0.06, 'hidden'=>false,
     'ladder'=>[[1,10.78],[24,9.10],[144,5.40]],
     'desc'=>'Ultra PRO Deck Protector sleeves with Pikachu artwork — 65 standard-size Pokémon card sleeves per pack.

Standard-size sleeves fit Pokémon cards for play and storage. See our [Pokémon card size guide](guide:pokemon-card-size) for which sleeves go with which cards.

- 65 sleeves per pack
- Fits standard 63 × 88 mm Pokémon cards
- Sold in 24s, with the lowest price from 144'],
    ['id'=>'pokemon-playmat-assorted-designs', 'sku'=>'FK-ACC-MAT-AST',
     'name'=>'Pokémon Playmat — Assorted Designs', 'set'=>'', 'cat'=>'accessories', 'cond'=>'Sealed',
     'moq'=>10, 'step'=>5, 'status'=>'in', 'release'=>'', 'weight'=>0.45, 'hidden'=>false,
     'ladder'=>[[1,28.00],[10,22.00],[50,19.00]],
     'desc'=>'Pokémon playmats in assorted designs.

A playmat protects cards during play and marks out the play area — a popular add-on for players and a steady seller for shops. The mix of designs varies by shipment.

- Assorted Pokémon designs
- Minimum 10, then in fives, with the lowest price from 50'],
    ['id'=>'pokemon-deck-box-assorted', 'sku'=>'FK-ACC-DBX-AST',
     'name'=>'Pokémon Deck Box — Assorted', 'set'=>'', 'cat'=>'accessories', 'cond'=>'Sealed',
     'moq'=>20, 'step'=>10, 'status'=>'in', 'release'=>'', 'weight'=>0.1, 'hidden'=>false,
     'ladder'=>[[1,12.00],[20,9.40],[100,8.00]],
     'desc'=>'Pokémon deck boxes in assorted designs.

Deck boxes keep a sleeved deck safe in a bag or pocket, and sell steadily alongside sleeves and playmats. The mix of designs varies by shipment.

- Assorted Pokémon designs
- Minimum 20, then in tens, with the lowest price from 100'],
    ['id'=>'pokemon-card-sleeves-64-ct-assorted-designs', 'sku'=>'FK-ACC-SLV64-AST',
     'name'=>'Pokémon Card Sleeves (64 ct) — Assorted Designs', 'set'=>'', 'cat'=>'accessories', 'cond'=>'Sealed',
     'moq'=>20, 'step'=>10, 'status'=>'in', 'release'=>'', 'weight'=>0.06, 'hidden'=>false,
     'ladder'=>[[1,8.00],[20,6.20],[100,5.20]],
     'desc'=>'Pokémon card sleeves in assorted designs, 64 per pack.

Sized for standard 63 × 88 mm Pokémon cards (Japanese and English cards are the same size), these sleeves keep cards in Near Mint condition in play and in storage. The mix of designs varies by shipment.

- 64 sleeves per pack
- Fits Japanese and English Pokémon cards
- Minimum 20, then in tens, with the lowest price from 100'],
    ['id'=>'mega-rayquaza-ex-sar-245-191-near-mint', 'sku'=>'FK-SGL-MRAY-SAR245',
     'name'=>'Mega Rayquaza ex SAR #245/191 — Near Mint', 'set'=>'Storm Emeralda', 'cat'=>'singles', 'cond'=>'Near Mint',
     'moq'=>3, 'step'=>1, 'status'=>'in', 'release'=>'', 'weight'=>0.05, 'hidden'=>false,
     'ladder'=>[[1,120.00],[3,102.00],[25,90.00]],
     'desc'=>'The Japanese Mega Rayquaza ex Special Illustration Rare, card 245/191 from Storm Emeralda, in Near Mint condition.

A full art Special Illustration Rare of the set\'s headline Pokémon, and a more affordable way into Mega Rayquaza than the [Master Ultra Rare](product:mega-rayquaza-ex-mur).

- Japanese — Storm Emeralda (M6), card 245/191
- Special Illustration Rare (full art)
- Near Mint, sleeved and toploaded
- Minimum 3 copies, with the lowest price from 25'],
    ['id'=>'pikachu-ex-sar-240-191-psa-10-gem-mint', 'sku'=>'FK-SGL-PIKA-PSA10',
     'name'=>'Pikachu ex SAR #240/191 — PSA 10 Gem Mint', 'set'=>'', 'cat'=>'singles', 'cond'=>'Graded',
     'moq'=>1, 'step'=>1, 'status'=>'in', 'release'=>'', 'weight'=>0.15, 'hidden'=>false,
     'ladder'=>[[1,495.00],[3,470.00]],
     'desc'=>'Pikachu ex Special Illustration Rare #240/191, graded PSA 10 Gem Mint.

PSA 10 is the top grade: a virtually perfect card, authenticated and sealed in PSA\'s tamper-evident slab. Pikachu cards sell to every kind of collector, and top-grade copies are the ones they keep.

- Pikachu ex Special Illustration Rare #240/191
- Graded PSA 10 Gem Mint
- Ships in its original PSA slab
- More [PSA graded Pokémon cards](cards:psa-graded-pokemon-cards)'],
    ['id'=>'mega-floette-ex-japanese', 'sku'=>'FK-SGL-MFLO',
     'name'=>'Mega Floette ex (Japanese)', 'set'=>'Ninja Spinner', 'cat'=>'singles', 'cond'=>'Near Mint',
     'moq'=>1, 'step'=>1, 'status'=>'in', 'release'=>'', 'weight'=>0.05, 'hidden'=>false,
     'ladder'=>[[1,56.40],[6,51.00]],
     'desc'=>'The Japanese Mega Floette ex from Ninja Spinner (M4), in Near Mint condition.

Mega Floette ex is one of the new Mega Pokémon ex introduced in Ninja Spinner. Sleeved and toploaded on dispatch.

- Japanese — [Ninja Spinner](set:ninja-spinner) (M4)
- Near Mint, sleeved and toploaded
- Lower price from 6 copies'],
    ['id'=>'mega-excadrill-ex-japanese', 'sku'=>'FK-SGL-MEXC',
     'name'=>'Mega Excadrill ex (Japanese)', 'set'=>'Abyss Eye', 'cat'=>'singles', 'cond'=>'Near Mint',
     'moq'=>1, 'step'=>1, 'status'=>'in', 'release'=>'', 'weight'=>0.05, 'hidden'=>false,
     'ladder'=>[[1,65.80],[6,59.50]],
     'desc'=>'The Japanese Mega Excadrill ex from Abyss Eye (M5), in Near Mint condition.

Mega Excadrill ex is one of the Mega Pokémon ex introduced in Abyss Eye. Sleeved and toploaded on dispatch.

- Japanese — [Abyss Eye](set:abyss-eye) (M5)
- Near Mint, sleeved and toploaded
- Lower price from 6 copies'],
    ['id'=>'mega-greninja-ex-japanese', 'sku'=>'FK-SGL-MGRE',
     'name'=>'Mega Greninja ex (Japanese)', 'set'=>'Ninja Spinner', 'cat'=>'singles', 'cond'=>'Near Mint',
     'moq'=>1, 'step'=>1, 'status'=>'in', 'release'=>'', 'weight'=>0.05, 'hidden'=>false,
     'ladder'=>[[1,84.60],[6,76.60]],
     'desc'=>'The Japanese Mega Greninja ex from Ninja Spinner (M4), in Near Mint condition.

Greninja is a long-time fan favourite, and Mega Greninja ex is one of the headline Pokémon of Ninja Spinner. Sleeved and toploaded on dispatch.

- Japanese — [Ninja Spinner](set:ninja-spinner) (M4)
- Near Mint, sleeved and toploaded
- Lower price from 6 copies'],
    ['id'=>'mega-darkrai-ex-japanese', 'sku'=>'FK-SGL-MDRK',
     'name'=>'Mega Darkrai ex (Japanese)', 'set'=>'Abyss Eye', 'cat'=>'singles', 'cond'=>'Near Mint',
     'moq'=>1, 'step'=>1, 'status'=>'in', 'release'=>'', 'weight'=>0.05, 'hidden'=>false,
     'ladder'=>[[1,182.30],[6,165.00]],
     'desc'=>'The Japanese Mega Darkrai ex from Abyss Eye (M5), in Near Mint condition.

Darkrai is a fan-favourite Mythical Pokémon, and Mega Darkrai ex is one of the headline cards of Abyss Eye. Sleeved and toploaded on dispatch.

- Japanese — [Abyss Eye](set:abyss-eye) (M5)
- Near Mint, sleeved and toploaded
- Lower price from 6 copies'],
    ['id'=>'pikachu-ex-special-illustration-rare-277-217', 'sku'=>'FK-SGL-PIKA-SAR277',
     'name'=>'Pikachu ex — Special Illustration Rare #277/217', 'set'=>'', 'cat'=>'singles', 'cond'=>'Near Mint',
     'moq'=>1, 'step'=>1, 'status'=>'in', 'release'=>'', 'weight'=>0.05, 'hidden'=>false,
     'ladder'=>[[1,362.70],[6,328.30]],
     'desc'=>'Pikachu ex Special Illustration Rare, card 277/217, in Near Mint condition.

A full art Special Illustration Rare of Pokémon\'s mascot — a centrepiece Pikachu Pokémon card for any collection. Sleeved and toploaded on dispatch.

- Card 277/217
- Special Illustration Rare (full art)
- Near Mint
- More [Pikachu Pokémon cards](cards:pikachu-pokemon-cards)'],
    ['id'=>'mega-charizard-y-ex-hyper-rare', 'sku'=>'FK-SGL-MCHY-HR',
     'name'=>'Mega Charizard Y ex — Hyper Rare', 'set'=>'', 'cat'=>'singles', 'cond'=>'Near Mint',
     'moq'=>1, 'step'=>1, 'status'=>'in', 'release'=>'', 'weight'=>0.05, 'hidden'=>false,
     'ladder'=>[[1,410.60],[6,371.70]],
     'desc'=>'Mega Charizard Y ex Hyper Rare — a gold Charizard Pokémon card from the Mega Evolution series.

Hyper Rares are the textured gold cards at the top of a set, and Charizard\'s are always among the most wanted. Near Mint, sleeved and toploaded on dispatch.

- Hyper Rare (gold, textured)
- Near Mint
- Lower price from 6 copies
- What makes [gold Pokémon cards](guide:pokemon-card-rarities) rare'],
    ['id'=>'30th-celebration-premium-deck-set-espeon-and-umbreon', 'sku'=>'FK-PRM-30C-ESUM',
     'name'=>'30th Celebration Premium Deck Set — Espeon & Umbreon', 'set'=>'30th Celebration', 'cat'=>'premium', 'cond'=>'Sealed',
     'moq'=>1, 'step'=>1, 'status'=>'in', 'release'=>'', 'weight'=>1.2, 'hidden'=>false,
     'ladder'=>[[1,432.80],[6,371.70]],
     'desc'=>'The 30th Celebration Premium Deck Set featuring Espeon and Umbreon — a collector-focused anniversary piece.

Espeon and Umbreon are two of the most loved Eevee evolutions, and this premium set puts them at the centre of Pokémon\'s 30th-anniversary celebrations. Sold individually, with a lower price from six sets.

- 30th Celebration Premium Deck Set: Espeon & Umbreon
- Anniversary collectible
- Sold individually, with a lower price from 6'],
    ['id'=>'30th-celebration-sylveon-ex-box', 'sku'=>'FK-PRM-SYL-01',
     'name'=>'30th Celebration Sylveon ex Box', 'set'=>'30th Celebration', 'cat'=>'premium', 'cond'=>'Sealed',
     'moq'=>6, 'step'=>6, 'status'=>'in', 'release'=>'', 'weight'=>0.6, 'hidden'=>false,
     'ladder'=>[[1,21.56],[6,19.40],[36,14.60]],
     'desc'=>'A 30th Celebration collection box built around Sylveon ex, with booster packs inside.

The companion to the [Greninja ex Box](product:30th-celebration-greninja-ex-box): an easy gift, a strong single-unit seller for shops, and a lower price from 36 boxes.

- Sylveon ex collection box with booster packs
- Part of the [30th Celebration](set:30th-celebration) range
- Sold in sixes, with the lowest price from 36'],
    ['id'=>'30th-celebration-tech-sticker-collection', 'sku'=>'FK-PRM-30C-STK',
     'name'=>'30th Celebration Tech Sticker Collection', 'set'=>'30th Celebration', 'cat'=>'premium', 'cond'=>'Sealed',
     'moq'=>12, 'step'=>12, 'status'=>'in', 'release'=>'', 'weight'=>0.25, 'hidden'=>false,
     'ladder'=>[[1,14.70],[12,13.20],[72,10.00]],
     'desc'=>'The 30th Celebration Tech Sticker Collection — an affordable anniversary add-on.

A low-price item that sells well at the counter and in online baskets next to 30th Celebration boxes.

- 30th Celebration sticker collection
- Sold in 12s, with the lowest price from 72'],
    ['id'=>'pitch-black-elite-trainer-box', 'sku'=>'FK-ETB-PB-01',
     'name'=>'Pitch Black Elite Trainer Box', 'set'=>'Abyss Eye', 'cat'=>'etb', 'cond'=>'Sealed',
     'moq'=>4, 'step'=>4, 'status'=>'in', 'release'=>'', 'weight'=>0.9, 'hidden'=>false,
     'ladder'=>[[1,44.09],[4,39.60],[24,29.90]],
     'desc'=>'A factory-sealed Pokémon TCG Mega Evolution Pitch Black Elite Trainer Box.

Pitch Black is part of the Pokémon Trading Card Game\'s Mega Evolution series. An Elite Trainer Box is the complete way to start a new set: booster packs plus card sleeves, dice, damage counters and a storage box. Related Japanese product is in our [Abyss Eye](set:abyss-eye) range.

- Sealed Mega Evolution Pitch Black Elite Trainer Box
- Booster packs, sleeves, dice, damage counters and storage box
- Sold in fours, with the lowest price from 24'],
    ['id'=>'chaos-rising-elite-trainer-box', 'sku'=>'FK-ETB-CR-01',
     'name'=>'Chaos Rising Elite Trainer Box', 'set'=>'Ninja Spinner', 'cat'=>'etb', 'cond'=>'Sealed',
     'moq'=>4, 'step'=>4, 'status'=>'in', 'release'=>'', 'weight'=>0.9, 'hidden'=>false,
     'ladder'=>[[1,44.09],[4,39.60],[24,29.90]],
     'desc'=>'A factory-sealed Pokémon TCG Mega Evolution Chaos Rising Elite Trainer Box.

Chaos Rising is part of the Pokémon Trading Card Game\'s Mega Evolution series. An Elite Trainer Box is the complete way to start a new set: booster packs plus card sleeves, dice, damage counters and a storage box. Related Japanese product is in our [Ninja Spinner](set:ninja-spinner) range.

- Sealed Mega Evolution Chaos Rising Elite Trainer Box
- Booster packs, sleeves, dice, damage counters and storage box
- Sold in fours, with the lowest price from 24'],
    ['id'=>'perfect-order-elite-trainer-box', 'sku'=>'FK-ETB-PO-01',
     'name'=>'Perfect Order Elite Trainer Box', 'set'=>'Nihil Zero', 'cat'=>'etb', 'cond'=>'Sealed',
     'moq'=>4, 'step'=>4, 'status'=>'in', 'release'=>'', 'weight'=>0.9, 'hidden'=>false,
     'ladder'=>[[1,44.09],[4,39.60],[24,29.90]],
     'desc'=>'A factory-sealed Pokémon TCG Mega Evolution Perfect Order Elite Trainer Box.

Perfect Order is part of the Pokémon Trading Card Game\'s Mega Evolution series. An Elite Trainer Box is the complete way to start a new set: booster packs plus card sleeves, dice, damage counters and a storage box. Related Japanese product is in our [Nihil Zero](set:nihil-zero) range.

- Sealed Mega Evolution Perfect Order Elite Trainer Box
- Booster packs, sleeves, dice, damage counters and storage box
- Sold in fours, with the lowest price from 24'],
    ['id'=>'mega-evolution-ascended-heroes-elite-trainer-box', 'sku'=>'FK-ETB-AH-01',
     'name'=>'Mega Evolution: Ascended Heroes Elite Trainer Box', 'set'=>'Mega Dream ex', 'cat'=>'etb', 'cond'=>'Sealed',
     'moq'=>4, 'step'=>4, 'status'=>'in', 'release'=>'', 'weight'=>0.9, 'hidden'=>false,
     'ladder'=>[[1,44.09],[4,39.60],[24,29.90]],
     'desc'=>'A factory-sealed Pokémon TCG Mega Evolution Ascended Heroes Elite Trainer Box.

Ascended Heroes is part of the Pokémon Trading Card Game\'s Mega Evolution series. An Elite Trainer Box is the complete way to start a new set: booster packs plus card sleeves, dice, damage counters and a storage box. Related Japanese product is in our [Mega Dream ex](set:mega-dream-ex) range.

- Sealed Mega Evolution Ascended Heroes Elite Trainer Box
- Booster packs, sleeves, dice, damage counters and storage box
- Sold in fours, with the lowest price from 24'],
    ['id'=>'30th-celebration-elite-trainer-box-case-10-ct', 'sku'=>'FK-ETB-30C-C10',
     'name'=>'30th Celebration Elite Trainer Box Case (10-ct)', 'set'=>'30th Celebration', 'cat'=>'etb', 'cond'=>'Sealed',
     'moq'=>1, 'step'=>1, 'status'=>'in', 'release'=>'', 'weight'=>10, 'hidden'=>false,
     'ladder'=>[[1,440.50],[6,331.70]],
     'desc'=>'A sealed case of 10 Pokémon TCG: 30th Celebration Elite Trainer Boxes, priced per case.

The most economical way to stock anniversary ETBs: ten boxes in one sealed case, with the price per case falling sharply from six cases. Each Elite Trainer Box holds 9 booster packs, a full-art Nidorina promo, 65 sleeves, dice and a player\'s guide.

- 10 sealed 30th Celebration Elite Trainer Boxes per case
- Each box: 9 booster packs, Nidorina promo, 16 foil Energy, 65 sleeves, dice, coin and collector\'s box
- Price per case drops from 6 cases
- Also sold [in fours](product:30th-celebration-elite-trainer-box)'],
    ['id'=>'mega-dream-ex-m2a-booster-box', 'sku'=>'FK-BB-M2A-01',
     'name'=>'Mega Dream ex (M2A) Booster Box', 'set'=>'Mega Dream ex', 'cat'=>'boxes', 'cond'=>'Sealed',
     'moq'=>6, 'step'=>6, 'status'=>'in', 'release'=>'', 'weight'=>0.4, 'hidden'=>false,
     'ladder'=>[[1,175.00],[6,149.00],[24,136.00]],
     'desc'=>'A factory-sealed Japanese Mega Dream ex (M2A) booster box — the special set of the Mega Evolution series.

Mega Dream ex is home to the [Mega Gengar ex Special Illustration Rare](product:mega-gengar-ex-sir), one of the standout cards of the Mega Evolution era, which makes these boxes a favourite with collectors chasing it.

- Sealed Japanese Mega Dream ex booster box (M2A)
- Chase card: Mega Gengar ex Special Illustration Rare
- Best price per box from 24 boxes
- See all [Gengar Pokémon cards](cards:gengar-pokemon-cards)'],
    ['id'=>'inferno-x-booster-box', 'sku'=>'FK-BB-INFX-01',
     'name'=>'Inferno X Booster Box', 'set'=>'Inferno X', 'cat'=>'boxes', 'cond'=>'Sealed',
     'moq'=>6, 'step'=>6, 'status'=>'in', 'release'=>'', 'weight'=>0.4, 'hidden'=>false,
     'ladder'=>[[1,217.10],[6,207.20],[36,185.50]],
     'desc'=>'A factory-sealed Japanese Inferno X (M2) booster box, the second main set of the Mega Evolution series.

Inferno X continues the Mega Evolution era with new Mega Pokémon ex and full art rares. Sealed boxes suit players building decks, collectors chasing the set\'s rare cards, and shops restocking current Japanese product.

- Sealed Japanese booster box — Inferno X (M2)
- Part of the [Mega Evolution series](set:mega-evolution)
- Sold in sixes, with the best price from 36 boxes'],
    ['id'=>'mega-symphonia-booster-box', 'sku'=>'FK-BB-M1S-01',
     'name'=>'Mega Symphonia Booster Box', 'set'=>'Mega Symphonia', 'cat'=>'boxes', 'cond'=>'Sealed',
     'moq'=>6, 'step'=>6, 'status'=>'in', 'release'=>'', 'weight'=>0.4, 'hidden'=>false,
     'ladder'=>[[1,109.97],[6,105.00],[36,94.00]],
     'desc'=>'A factory-sealed Japanese Mega Symphonia (M1S) booster box — one of the twin sets that launched the Mega Evolution series in 2025.

Mega Symphonia and its partner set [Mega Brave](product:mega-brave-booster-box) brought Mega Evolution back to the Pokémon TCG, and many collectors buy the two together to complete the launch of the era.

- Sealed Japanese booster box — Mega Symphonia (M1S)
- Twin set of Mega Brave (M1L)
- Sold in sixes, with the best price from 36 boxes'],
    ['id'=>'mega-brave-booster-box', 'sku'=>'FK-BB-M1L-01',
     'name'=>'Mega Brave Booster Box', 'set'=>'Mega Brave', 'cat'=>'boxes', 'cond'=>'Sealed',
     'moq'=>6, 'step'=>6, 'status'=>'in', 'release'=>'', 'weight'=>0.4, 'hidden'=>false,
     'ladder'=>[[1,119.97],[6,114.50],[36,102.50]],
     'desc'=>'A factory-sealed Japanese Mega Brave (M1L) booster box — one of the twin sets that launched the Mega Evolution series in 2025.

Mega Brave and its partner set [Mega Symphonia](product:mega-symphonia-booster-box) brought Mega Evolution back to the Pokémon TCG, and many collectors buy the two together to complete the launch of the era.

- Sealed Japanese booster box — Mega Brave (M1L)
- Twin set of Mega Symphonia (M1S)
- Sold in sixes, with the best price from 36 boxes'],
    ['id'=>'abyss-eye-m5-booster-box', 'sku'=>'FK-BB-M5-01',
     'name'=>'Abyss Eye (M5) Booster Box', 'set'=>'Abyss Eye', 'cat'=>'boxes', 'cond'=>'Sealed',
     'moq'=>6, 'step'=>6, 'status'=>'in', 'release'=>'', 'weight'=>0.4, 'hidden'=>false,
     'ladder'=>[[1,198.00],[6,168.00],[24,152.00]],
     'desc'=>'A factory-sealed Japanese Abyss Eye (M5) booster box from the Mega Evolution series.

Abyss Eye features [Mega Darkrai ex](product:mega-darkrai-ex-japanese) and [Mega Excadrill ex](product:mega-excadrill-ex-japanese) among its Mega Pokémon ex. Open boxes to chase them, or pick up the singles directly.

- Sealed Japanese booster box — Abyss Eye (M5)
- Features Mega Darkrai ex and Mega Excadrill ex
- Best price per box from 24 boxes
- Matching [Abyss Eye Elite Trainer Box](product:abyss-eye-elite-trainer-box)'],
    ['id'=>'ninja-spinner-m4-booster-box', 'sku'=>'FK-BB-M4-01',
     'name'=>'Ninja Spinner (M4) Booster Box', 'set'=>'Ninja Spinner', 'cat'=>'boxes', 'cond'=>'Sealed',
     'moq'=>6, 'step'=>6, 'status'=>'low', 'release'=>'', 'weight'=>0.4, 'hidden'=>false,
     'ladder'=>[[1,165.00],[6,141.00],[24,129.00]],
     'desc'=>'A factory-sealed Japanese Ninja Spinner (M4) booster box — limited stock remaining.

Ninja Spinner brings [Mega Greninja ex](product:mega-greninja-ex-japanese) and [Mega Floette ex](product:mega-floette-ex-japanese) to the Mega Evolution series. Greninja is a long-time fan favourite, and remaining sealed boxes are limited.

- Sealed Japanese booster box — Ninja Spinner (M4)
- Features Mega Greninja ex and Mega Floette ex
- Best price per box from 24 boxes'],
    ['id'=>'nihil-zero-booster-box', 'sku'=>'FK-BB-NZ-01',
     'name'=>'Nihil Zero Booster Box', 'set'=>'Nihil Zero', 'cat'=>'boxes', 'cond'=>'Sealed',
     'moq'=>6, 'step'=>6, 'status'=>'in', 'release'=>'', 'weight'=>0.4, 'hidden'=>false,
     'ladder'=>[[1,83.30],[6,82.60],[36,81.20]],
     'desc'=>'A factory-sealed Japanese Nihil Zero (M3) booster box from the Mega Evolution series.

Nihil Zero is an affordable way into the Mega Evolution era, and its flat price ladder makes it an easy add-on to a larger order.

- Sealed Japanese booster box — Nihil Zero (M3)
- Part of the [Mega Evolution series](set:mega-evolution)
- Sold in sixes'],
    ['id'=>'terastal-festival-booster-box', 'sku'=>'FK-BB-SV8A-01',
     'name'=>'Terastal Festival Booster Box', 'set'=>'Terastal Festival ex', 'cat'=>'boxes', 'cond'=>'Sealed',
     'moq'=>6, 'step'=>6, 'status'=>'in', 'release'=>'', 'weight'=>0.4, 'hidden'=>false,
     'ladder'=>[[1,88.20],[6,85.10],[36,78.40]],
     'desc'=>'A factory-sealed Japanese Terastal Festival ex (SV8a) booster box — the Eevee-evolutions special set of the Scarlet & Violet era.

Terastal Festival ex is built around Terastal Pokémon and all of the Eevee evolutions, and was released in English as Prismatic Evolutions. Japanese boxes hold 10 packs of 10 cards, and the set\'s Special Art Rares — Umbreon ex above all — are some of the most sought-after cards of recent years.

- Sealed Japanese booster box — Terastal Festival ex (SV8a): 10 packs of 10 cards
- Released in English as Prismatic Evolutions
- Sold in sixes, with the lowest price from 36 boxes'],
  ],

  /* countries: '*' = everywhere, or a list of country codes */
  /* type 'bitcoin' = paid on the site to btc_address above; any other method = you send the details */
  'payments' => [
    'bitcoin'  => ['label'=>'Bitcoin (BTC) — pay now', 'note'=>'Pay from any Bitcoin wallet as soon as you order. The exact amount and a QR code appear on the next page.', 'countries'=>'*', 'enabled'=>true, 'type'=>'bitcoin'],
    'crypto'   => ['label'=>'Other crypto (ETH, USDT)', 'note'=>'ETH or USDT (TRC-20 / ERC-20). We send the wallet address with your invoice. Network fees are the sender\'s.', 'countries'=>'*', 'enabled'=>true],
    'cashapp'  => ['label'=>'Cash App',         'note'=>'US customers only.', 'countries'=>['US'], 'enabled'=>true],
    'applepay' => ['label'=>'Apple Pay',        'note'=>'US customers only.', 'countries'=>['US'], 'enabled'=>true],
    'ukbank'   => ['label'=>'UK bank transfer', 'note'=>'UK customers only. Faster Payments to a UK account in our business name.', 'countries'=>['GB'], 'enabled'=>true],
    'other'    => ['label'=>'Other / discuss with us', 'note'=>'Tell us what works and we will arrange it.', 'countries'=>'*', 'enabled'=>true],
  ],

  'countries' => ['US'=>'United States','GB'=>'United Kingdom','CA'=>'Canada','AU'=>'Australia','JP'=>'Japan','DE'=>'Germany','FR'=>'France','ES'=>'Spain','IT'=>'Italy','NL'=>'Netherlands','BE'=>'Belgium','SE'=>'Sweden','NO'=>'Norway','DK'=>'Denmark','FI'=>'Finland','IE'=>'Ireland','PL'=>'Poland','PT'=>'Portugal','CH'=>'Switzerland','AT'=>'Austria','CZ'=>'Czechia','GR'=>'Greece','SG'=>'Singapore','MY'=>'Malaysia','TH'=>'Thailand','PH'=>'Philippines','ID'=>'Indonesia','VN'=>'Vietnam','KR'=>'South Korea','TW'=>'Taiwan','HK'=>'Hong Kong','NZ'=>'New Zealand','MX'=>'Mexico','BR'=>'Brazil','AR'=>'Argentina','CL'=>'Chile','ZA'=>'South Africa','AE'=>'United Arab Emirates','SA'=>'Saudi Arabia','IL'=>'Israel','TR'=>'Turkey','IN'=>'India','NG'=>'Nigeria','KE'=>'Kenya','EG'=>'Egypt','CM'=>'Cameroon','GH'=>'Ghana'],

  /* shipping (USD) = zone per-order price + per-kg price × order weight, for each delivery option; totals round up
     to whole dollars. A single card to the US comes to $12 Standard — TCGplayer's $11.99 international flat rate,
     rounded up — and heavier orders add per kg. Check these against your carrier's real costs from Japan. */
  'shipping' => [
    'methods'  => ['standard'=>['label'=>'Standard', 'days'=>'3–6 working days'],
                   'express' =>['label'=>'Express',  'days'=>'1–2 working days']],
    'round_up' => true,
    'zones' => [
      ['name'=>'United States',  'countries'=>['US'], 'standard'=>['base'=>11, 'per_kg'=>8], 'express'=>['base'=>22, 'per_kg'=>16]],
      ['name'=>'Canada',         'countries'=>['CA'], 'standard'=>['base'=>11, 'per_kg'=>8], 'express'=>['base'=>22, 'per_kg'=>16]],
      ['name'=>'United Kingdom', 'countries'=>['GB'], 'standard'=>['base'=>11, 'per_kg'=>8], 'express'=>['base'=>22, 'per_kg'=>16]],
      ['name'=>'Europe',         'countries'=>['DE','FR','ES','IT','NL','BE','SE','NO','DK','FI','IE','PL','PT','CH','AT','CZ','GR'],
                                 'standard'=>['base'=>12, 'per_kg'=>9], 'express'=>['base'=>24, 'per_kg'=>18]],
      ['name'=>'Asia-Pacific',   'countries'=>['SG','MY','TH','PH','ID','VN','KR','TW','HK','AU','NZ'],
                                 'standard'=>['base'=>8, 'per_kg'=>6], 'express'=>['base'=>16, 'per_kg'=>12]],
      ['name'=>'Japan',          'countries'=>['JP'], 'standard'=>['base'=>5, 'per_kg'=>2], 'express'=>['base'=>10, 'per_kg'=>4]],
    ],
    'rest' => ['standard'=>['base'=>15, 'per_kg'=>11], 'express'=>['base'=>30, 'per_kg'=>22]],
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
