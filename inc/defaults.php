<?php
/* Starting data for a fresh install. On first load it is copied to data/store.php,
   and from then on everything is edited in admin.php — not here. */
if(!defined('FK_ROOT')){ http_response_code(404); exit; }

return [
  'settings' => [
    'brand'         => 'FUDAKURA',
    'kanji'         => '札蔵',
    'tagline'       => 'Wholesale Japanese Pokémon TCG',
    'legal_name'    => 'Fudakura',
    'address'       => 'Japan',
    'email'         => 'orders@fudakura.com',
    'phone'         => '',
    'order_email'   => 'orders@fudakura.com',
    'domain'        => 'https://fudakura.com',
    'reply_hours'   => 12,
    'hold_hours'    => 48,
    'min_order_usd' => 100,
    'strip_text'    => 'Sealed product, sourced in Japan · Quantity breaks published on every listing',
    'strip_link_text' => 'Mega Lucario Z preorder open →',
    'strip_link_url'  => 'index.php?p=product&id=mega-lucario-z-booster-box',
    'hero_title'    => 'Wholesale Japanese Pokémon cards, shipped worldwide from Japan.',
    'hero_lede'     => 'Sealed booster boxes, Elite Trainer Boxes, premium sets and singles, bought through Japanese distribution and priced by the case. Every quantity break is published — price a full order before you talk to anyone.',
    'footer_blurb'  => 'Wholesale Japanese Pokémon TCG, shipped worldwide from Japan to retailers, resellers and card shops. Pricing published on every product — order direct, no account needed.',
    'shipping_reviewed' => false,
  ],

  'currencies' => [
    'USD' => ['rate'=>1,    'sym'=>'$',   'dec'=>2],
    'EUR' => ['rate'=>0.92, 'sym'=>'€',   'dec'=>2],
    'JPY' => ['rate'=>156,  'sym'=>'¥',   'dec'=>0],
    'GBP' => ['rate'=>0.79, 'sym'=>'£',   'dec'=>2],
    'CAD' => ['rate'=>1.37, 'sym'=>'CA$', 'dec'=>2],
    'AUD' => ['rate'=>1.52, 'sym'=>'A$',  'dec'=>2],
  ],

  'categories' => [
    'boxes'       => ['label'=>'Booster Boxes',         'blurb'=>'Sealed Japanese booster boxes, shipped by the case.'],
    'etb'         => ['label'=>'Elite Trainer Boxes',   'blurb'=>'ETBs and starter sets with sleeves, dice and promos.'],
    'premium'     => ['label'=>'Premium & Special Sets','blurb'=>'Premium decks, collection boxes and limited commemoratives.'],
    'singles'     => ['label'=>'Single Cards',          'blurb'=>'Graded and raw singles, including SAR, AR and promos.'],
    'accessories' => ['label'=>'Accessories',           'blurb'=>'Sleeves, deck boxes, playmats and storage.'],
  ],

  /* status: in | new | low | preorder | soldout.  ladder: [min_qty, unit_price_usd] ascending.  weight: kg per unit */
  'products' => [
    ['id'=>'storm-emeralda-elite-trainer-box', 'sku'=>'FK-ETB-SE-01',
     'name'=>'Storm Emeralda Elite Trainer Box', 'set'=>'Storm Emeralda', 'cat'=>'etb',
     'moq'=>6, 'step'=>6, 'status'=>'in', 'release'=>'', 'weight'=>0.9, 'hidden'=>false,
     'ladder'=>[[1,45.00],[6,38.00],[18,36.00],[36,34.00]],
     'desc'=>'Japanese Elite Trainer Box for the Storm Emeralda expansion. Sealed factory case, includes card sleeves, damage counters, dice, energy cards and the set promo. Sold in multiples of six.'],

    ['id'=>'30th-celebration-m6a-booster-box', 'sku'=>'FK-BB-M6A-01',
     'name'=>'30th Celebration (M6A) Booster Box', 'set'=>'30th Celebration', 'cat'=>'boxes',
     'moq'=>6, 'step'=>6, 'status'=>'in', 'release'=>'', 'weight'=>0.4, 'hidden'=>false,
     'ladder'=>[[1,327.00],[6,279.00],[12,267.00],[24,255.00]],
     'desc'=>'Anniversary booster box from the 30th Celebration series. High pull-rate set with reprinted classic illustrations. Sealed Japanese domestic release.'],

    ['id'=>'mega-rayquaza-ex-mur', 'sku'=>'FK-SGL-MRAY-MUR',
     'name'=>'Mega Rayquaza ex — Master Ultra Rare', 'set'=>'Storm Emeralda', 'cat'=>'singles',
     'moq'=>1, 'step'=>1, 'status'=>'low', 'release'=>'', 'weight'=>0.05, 'hidden'=>false,
     'ladder'=>[[1,1207.30],[3,1150.00],[6,1092.90]],
     'desc'=>'Japanese Master Ultra Rare Mega Rayquaza ex. Pack-fresh, sleeved and toploaded on dispatch. Condition graded NM unless otherwise noted on the invoice.'],

    ['id'=>'mega-gengar-ex-sir', 'sku'=>'FK-SGL-MGEN-SIR',
     'name'=>'Mega Gengar ex — Special Illustration Rare', 'set'=>'Mega Dream ex', 'cat'=>'singles',
     'moq'=>1, 'step'=>1, 'status'=>'low', 'release'=>'', 'weight'=>0.05, 'hidden'=>false,
     'ladder'=>[[1,1033.50],[3,985.00],[6,935.60]],
     'desc'=>'Japanese Special Illustration Rare Mega Gengar ex. Pack-fresh, sleeved and toploaded on dispatch. Condition graded NM unless otherwise noted.'],

    ['id'=>'abyss-eye-elite-trainer-box', 'sku'=>'FK-ETB-AE-01',
     'name'=>'Abyss Eye Elite Trainer Box', 'set'=>'Abyss Eye', 'cat'=>'etb',
     'moq'=>6, 'step'=>6, 'status'=>'new', 'release'=>'', 'weight'=>0.9, 'hidden'=>false,
     'ladder'=>[[1,42.00],[6,35.50],[18,33.60],[36,32.00]],
     'desc'=>'Elite Trainer Box for the Abyss Eye expansion. Sealed Japanese release with sleeves, dice, counters and promo card.'],

    ['id'=>'30th-celebration-greninja-ex-box', 'sku'=>'FK-PRM-GRE-01',
     'name'=>'30th Celebration Greninja ex Box', 'set'=>'30th Celebration', 'cat'=>'premium',
     'moq'=>6, 'step'=>6, 'status'=>'new', 'release'=>'', 'weight'=>0.6, 'hidden'=>false,
     'ladder'=>[[1,21.56],[6,19.40],[18,16.80],[36,14.60]],
     'desc'=>'Special collection box built around the promo Greninja ex, with additional booster packs. Strong single-unit retail margin at case pricing.'],

    ['id'=>'heat-wave-arena-booster-box', 'sku'=>'FK-BB-SV9A-01',
     'name'=>'Heat Wave Arena (SV9a) Booster Box', 'set'=>'Heat Wave Arena', 'cat'=>'boxes',
     'moq'=>6, 'step'=>6, 'status'=>'new', 'release'=>'', 'weight'=>0.4, 'hidden'=>false,
     'ladder'=>[[1,207.11],[6,197.70],[18,186.00],[36,177.00]],
     'desc'=>'Sealed SV9a Heat Wave Arena booster box, Japanese domestic release. Consistent reorder line for shops running draft and league events.'],

    ['id'=>'glory-of-team-rocket-booster-box', 'sku'=>'FK-BB-SV10-01',
     'name'=>'Glory of Team Rocket (SV10) Booster Box', 'set'=>'Glory of Team Rocket', 'cat'=>'boxes',
     'moq'=>6, 'step'=>6, 'status'=>'new', 'release'=>'', 'weight'=>0.4, 'hidden'=>false,
     'ladder'=>[[1,261.40],[6,249.50],[18,235.00],[36,223.40]],
     'desc'=>'Sealed SV10 Glory of Team Rocket booster box. One of the strongest secondary-market sets of the year; allocation moves quickly.'],

    ['id'=>'mega-lucario-z-booster-box', 'sku'=>'FK-BB-MLZ-01',
     'name'=>'Mega Lucario Z Booster Box', 'set'=>'Mega Lucario Z', 'cat'=>'boxes',
     'moq'=>6, 'step'=>6, 'status'=>'preorder', 'release'=>'November 2026', 'weight'=>0.4, 'hidden'=>false,
     'ladder'=>[[1,121.43],[6,115.90],[18,109.50],[36,103.80]],
     'desc'=>'Preorder allocation for the Mega Lucario Z booster box, releasing November 2026. Preorders are invoiced at allocation, not at request.'],
  ],

  /* countries: '*' = everywhere, or a list of country codes */
  'payments' => [
    'crypto'   => ['label'=>'Cryptocurrency',   'note'=>'BTC, ETH or USDT (TRC-20 / ERC-20). Network fees are the sender\'s.', 'countries'=>'*', 'enabled'=>true],
    'cashapp'  => ['label'=>'Cash App',         'note'=>'US customers only.', 'countries'=>['US'], 'enabled'=>true],
    'applepay' => ['label'=>'Apple Pay',        'note'=>'US customers only.', 'countries'=>['US'], 'enabled'=>true],
    'ukbank'   => ['label'=>'UK bank transfer', 'note'=>'UK customers only. Faster Payments to a UK account in our business name.', 'countries'=>['GB'], 'enabled'=>true],
    'other'    => ['label'=>'Other / discuss with us', 'note'=>'Tell us what works and we will arrange it.', 'countries'=>'*', 'enabled'=>true],
  ],

  'countries' => ['US'=>'United States','GB'=>'United Kingdom','CA'=>'Canada','AU'=>'Australia','JP'=>'Japan','DE'=>'Germany','FR'=>'France','ES'=>'Spain','IT'=>'Italy','NL'=>'Netherlands','BE'=>'Belgium','SE'=>'Sweden','NO'=>'Norway','DK'=>'Denmark','FI'=>'Finland','IE'=>'Ireland','PL'=>'Poland','PT'=>'Portugal','CH'=>'Switzerland','AT'=>'Austria','CZ'=>'Czechia','GR'=>'Greece','SG'=>'Singapore','MY'=>'Malaysia','TH'=>'Thailand','PH'=>'Philippines','ID'=>'Indonesia','VN'=>'Vietnam','KR'=>'South Korea','TW'=>'Taiwan','HK'=>'Hong Kong','NZ'=>'New Zealand','MX'=>'Mexico','BR'=>'Brazil','AR'=>'Argentina','CL'=>'Chile','ZA'=>'South Africa','AE'=>'United Arab Emirates','SA'=>'Saudi Arabia','IL'=>'Israel','TR'=>'Turkey','IN'=>'India','NG'=>'Nigeria','KE'=>'Kenya','EG'=>'Egypt','CM'=>'Cameroon','GH'=>'Ghana'],

  /* shipping (USD) = zone base per order + per_kg × total order weight.
     PLACEHOLDER RATES — set real ones in Admin → Shipping before going live. */
  'shipping' => [
    'zones' => [
      ['name'=>'Japan',            'countries'=>['JP'], 'base'=>8,  'per_kg'=>2],
      ['name'=>'United States',    'countries'=>['US'], 'base'=>18, 'per_kg'=>14],
      ['name'=>'Canada',           'countries'=>['CA'], 'base'=>18, 'per_kg'=>14],
      ['name'=>'United Kingdom',   'countries'=>['GB'], 'base'=>16, 'per_kg'=>12],
      ['name'=>'Europe',           'countries'=>['DE','FR','ES','IT','NL','BE','SE','NO','DK','FI','IE','PL','PT','CH','AT','CZ','GR'], 'base'=>16, 'per_kg'=>12],
      ['name'=>'Asia-Pacific',     'countries'=>['SG','MY','TH','PH','ID','VN','KR','TW','HK','AU','NZ'], 'base'=>10, 'per_kg'=>8],
    ],
    'rest' => ['base'=>22, 'per_kg'=>16],
  ],

  /* placeholders: {reply_hours} {hold_hours} {countries} {min_order} */
  'faqs' => [
    ['Do I need an account to see wholesale pricing?', 'No. Every product shows its full quantity-break ladder publicly, so you can price a complete order before contacting anyone. There is no application form and no approval wait.'],
    ['What is the minimum order?', 'Every order must total at least {min_order} including shipping. Sealed product starts at six units and sells in multiples of six. Graded and raw singles start at one, with breaks from three and six.'],
    ['How do I pay?', 'Select a method at checkout — cryptocurrency, Cash App or Apple Pay for US customers, UK bank transfer for UK customers, or the "other" option anywhere else. We send the details for your chosen method to your email and phone within {reply_hours} hours, with your invoice.'],
    ['When is my stock allocated?', 'Placing an order reserves your stock for {hold_hours} hours. Once payment clears, the allocation is confirmed and we dispatch within {hold_hours} hours. If payment does not clear inside the window, high-demand stock returns to general availability.'],
    ['Which countries do you ship to?', '{countries} countries from Japan by EMS, DHL and FedEx with tracking on every consignment. Shipping is calculated at checkout from your destination and order weight. Import duty, VAT or GST and clearance fees are excluded from our prices and collected by your carrier on delivery.'],
    ['Can I preorder an upcoming set?', 'Yes. Preorder lines commit an allocation ahead of release at the same published ladder, and are invoiced at allocation rather than at request.'],
    ['Is the product authentic?', 'Yes. Everything is sourced through Japanese distribution and ships sealed in its original factory packaging. We do not deal in resealed, reprinted or counterfeit product.'],
    ['What if something arrives damaged or short?', 'Report transit damage, a short shipment or a wrong item within seven days of delivery and we replace, credit or refund the affected lines and their shipping.'],
  ],
];
