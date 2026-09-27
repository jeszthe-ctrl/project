<?php
/* Starting data for a fresh install. On first load it is copied to data/store.php,
   and from then on everything is edited in admin.php — not here.
   This file holds what both languages share (prices, quantities, shipping rates…).
   The Dutch and French text lives in content-nl.php and content-fr.php. */
if(!defined('FK_ROOT')){ http_response_code(404); exit; }

$d = [
  'settings' => [
    'brand'         => 'FUDAKURA',
    'kanji'         => '札蔵',
    'legal_name'    => 'Fudakura',
    'address'       => 'Japan',
    'email'         => 'support@fudakura.eu',
    'phone'         => '',
    'order_email'   => 'support@fudakura.eu',
    'domain'        => 'https://fudakura.eu',
    'reply_hours'   => 12,
    'hold_hours'    => 48,
    'min_order_usd' => 58.14,            /* €50 at the €0.86 rate; set in Admin → Settings */
    'shipping_reviewed' => false,
    'pretty_urls'   => false,            /* clean addresses like /nl/producten/151-booster-box; turn on in Admin → Settings */
    'free_ship_usd' => 1744.19,          /* free Standard shipping from this goods total (USD): €1,500; 0 = off */
    'content_version' => 1,              /* set by the shop: which built-in content updates are applied */
    'moved_to'      => '',               /* an old domain running this code can send every visitor here (301) */
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

  /* prices are entered in USD in the admin and shown in these currencies; the first is the one shoppers see.
     With only EUR here, the shop is euro-only and shows no currency switch. */
  'currencies' => [
    'EUR' => ['rate'=>0.86, 'sym'=>'€', 'dec'=>2],
  ],

  /* status: in | new | low | preorder | soldout.  cond: Sealed | Graded | Near Mint | Lightly Played.
     ladder: [min_qty, unit_price_usd] ascending.  weight: kg per unit (used for shipping).
     Names and descriptions are in content-nl.php and content-fr.php. */
  'products' => [
    ['id'=>'storm-emeralda-elite-trainer-box', 'sku'=>'FK-ETB-SE-01', 'set'=>'Storm Emeralda', 'cat'=>'etb', 'cond'=>'Sealed', 'moq'=>6, 'step'=>6, 'status'=>'in', 'weight'=>0.9, 'hidden'=>false,
     'ladder'=>[[1,45.00],[6,38.00],[18,36.00],[36,34.00]]],
    ['id'=>'30th-celebration-m6a-booster-box', 'sku'=>'FK-BB-M6A-01', 'set'=>'30th Celebration', 'cat'=>'boxes', 'cond'=>'Sealed', 'moq'=>6, 'step'=>6, 'status'=>'in', 'weight'=>0.4, 'hidden'=>false,
     'ladder'=>[[1,327.00],[6,279.00],[12,267.00],[24,255.00]]],
    ['id'=>'mega-rayquaza-ex-mur', 'sku'=>'FK-SGL-MRAY-MUR', 'set'=>'Storm Emeralda', 'cat'=>'singles', 'cond'=>'Near Mint', 'moq'=>1, 'step'=>1, 'status'=>'in', 'weight'=>0.05, 'hidden'=>false,
     'ladder'=>[[1,1207.30],[3,1150.00],[6,1092.90]]],
    ['id'=>'mega-gengar-ex-sir', 'sku'=>'FK-SGL-MGEN-SIR', 'set'=>'Mega Dream ex', 'cat'=>'singles', 'cond'=>'Near Mint', 'moq'=>1, 'step'=>1, 'status'=>'in', 'weight'=>0.05, 'hidden'=>false,
     'ladder'=>[[1,1033.50],[3,985.00],[6,935.60]]],
    ['id'=>'30th-celebration-elite-trainer-box', 'sku'=>'FK-ETB-30C-01', 'set'=>'30th Celebration', 'cat'=>'etb', 'cond'=>'Sealed', 'moq'=>4, 'step'=>4, 'status'=>'in', 'weight'=>0.9, 'hidden'=>false,
     'ladder'=>[[1,48.99],[4,44.00],[24,33.20]]],
    ['id'=>'storm-emeralda-m6-booster-box', 'sku'=>'FK-BB-M6-01', 'set'=>'Storm Emeralda', 'cat'=>'boxes', 'cond'=>'Sealed', 'moq'=>6, 'step'=>6, 'status'=>'in', 'weight'=>0.4, 'hidden'=>false,
     'ladder'=>[[1,218.00],[6,185.00],[24,170.00]]],
    ['id'=>'abyss-eye-elite-trainer-box', 'sku'=>'FK-ETB-AE-01', 'set'=>'Abyss Eye', 'cat'=>'etb', 'cond'=>'Sealed', 'moq'=>6, 'step'=>6, 'status'=>'in', 'weight'=>0.9, 'hidden'=>false,
     'ladder'=>[[1,42.00],[6,35.50],[18,33.60],[36,32.00]]],
    ['id'=>'30th-celebration-greninja-ex-box', 'sku'=>'FK-PRM-GRE-01', 'set'=>'30th Celebration', 'cat'=>'premium', 'cond'=>'Sealed', 'moq'=>6, 'step'=>6, 'status'=>'in', 'weight'=>0.6, 'hidden'=>false,
     'ladder'=>[[1,21.56],[6,19.40],[18,16.80],[36,14.60]]],
    ['id'=>'heat-wave-arena-sv9a-booster-box', 'sku'=>'FK-BB-SV9A-01', 'set'=>'Heat Wave Arena', 'cat'=>'boxes', 'cond'=>'Sealed', 'moq'=>6, 'step'=>6, 'status'=>'in', 'weight'=>0.4, 'hidden'=>false,
     'ladder'=>[[1,207.11],[6,197.70],[18,186.00],[36,177.00]]],
    ['id'=>'glory-of-team-rocket-sv10-booster-box', 'sku'=>'FK-BB-SV10-01', 'set'=>'Glory of Team Rocket', 'cat'=>'boxes', 'cond'=>'Sealed', 'moq'=>6, 'step'=>6, 'status'=>'in', 'weight'=>0.4, 'hidden'=>false,
     'ladder'=>[[1,261.40],[6,249.50],[18,235.00],[36,223.40]]],
    ['id'=>'mega-start-deck-100-battle-collection', 'sku'=>'FK-PRM-MSD100-01', 'set'=>'', 'cat'=>'premium', 'cond'=>'Sealed', 'moq'=>12, 'step'=>12, 'status'=>'in', 'weight'=>1.2, 'hidden'=>false,
     'ladder'=>[[1,37.11],[12,35.40],[72,31.70]]],
    ['id'=>'starter-set-ex-zorua-and-zoroark-ex', 'sku'=>'FK-PRM-SSZOR-01', 'set'=>'', 'cat'=>'premium', 'cond'=>'Sealed', 'moq'=>12, 'step'=>12, 'status'=>'in', 'weight'=>0.35, 'hidden'=>false,
     'ladder'=>[[1,37.11],[12,35.40],[72,31.70]]],
    ['id'=>'premium-trainer-box-mega', 'sku'=>'FK-PRM-PTBM-01', 'set'=>'', 'cat'=>'premium', 'cond'=>'Sealed', 'moq'=>4, 'step'=>4, 'status'=>'in', 'weight'=>0.9, 'hidden'=>false,
     'ladder'=>[[1,91.40],[4,87.20],[24,78.10]]],
    ['id'=>'151-booster-box', 'sku'=>'FK-BB-SV2A-01', 'set'=>'151', 'cat'=>'boxes', 'cond'=>'Sealed', 'moq'=>6, 'step'=>6, 'status'=>'in', 'weight'=>0.4, 'hidden'=>false,
     'ladder'=>[[1,445.71],[6,425.40],[36,380.90]]],
    ['id'=>'151-charizard-ex-special-illustration-rare', 'sku'=>'FK-SGL-CHAR-151', 'set'=>'151', 'cat'=>'singles', 'cond'=>'Near Mint', 'moq'=>1, 'step'=>1, 'status'=>'in', 'weight'=>0.05, 'hidden'=>false,
     'ladder'=>[[1,328.80],[6,297.70]]],
    ['id'=>'storm-emeralda-booster-box-case-12-ct', 'sku'=>'FK-BB-M6-C12', 'set'=>'Storm Emeralda', 'cat'=>'boxes', 'cond'=>'Sealed', 'moq'=>1, 'step'=>1, 'status'=>'in', 'weight'=>5.6, 'hidden'=>false,
     'ladder'=>[[1,1635.00],[6,1463.70]]],
    ['id'=>'aura-seeker-booster-box', 'sku'=>'FK-BB-MLZ-01', 'set'=>'Aura Seeker (Hadou Seeker)', 'cat'=>'boxes', 'cond'=>'Sealed', 'moq'=>6, 'step'=>6, 'status'=>'preorder', 'weight'=>0.4, 'hidden'=>false,
     'ladder'=>[[1,121.43],[6,115.90],[18,109.50],[36,103.80]]],
    ['id'=>'starter-set-ex-eevee-ex', 'sku'=>'FK-PRM-SSEEV-01', 'set'=>'', 'cat'=>'premium', 'cond'=>'Sealed', 'moq'=>10, 'step'=>10, 'status'=>'in', 'weight'=>0.35, 'hidden'=>false,
     'ladder'=>[[1,75.00],[10,59.00],[60,51.00]]],
    ['id'=>'pokemon-tcg-card-storage-box-booster-box-display', 'sku'=>'FK-ACC-STOR-01', 'set'=>'', 'cat'=>'accessories', 'cond'=>'Sealed', 'moq'=>6, 'step'=>6, 'status'=>'in', 'weight'=>0.25, 'hidden'=>false,
     'ladder'=>[[1,8.82],[6,7.90],[36,6.00]]],
    ['id'=>'pokemon-tcg-9-pocket-binder-mega-evolution-series', 'sku'=>'FK-ACC-BIND-ME', 'set'=>'', 'cat'=>'accessories', 'cond'=>'Sealed', 'moq'=>6, 'step'=>6, 'status'=>'in', 'weight'=>0.45, 'hidden'=>false,
     'ladder'=>[[1,31.36],[6,27.90],[36,20.30]]],
    ['id'=>'celebi-and-furret-deck-sleeves', 'sku'=>'FK-ACC-SLV-CELF', 'set'=>'', 'cat'=>'accessories', 'cond'=>'Sealed', 'moq'=>24, 'step'=>24, 'status'=>'in', 'weight'=>0.06, 'hidden'=>false,
     'ladder'=>[[1,12.73],[24,11.50],[144,8.60]]],
    ['id'=>'pikachu-ditto-ver-deck-sleeves', 'sku'=>'FK-ACC-SLV-PIKD', 'set'=>'', 'cat'=>'accessories', 'cond'=>'Sealed', 'moq'=>24, 'step'=>24, 'status'=>'in', 'weight'=>0.06, 'hidden'=>false,
     'ladder'=>[[1,12.73],[24,11.50],[144,8.60]]],
    ['id'=>'storm-emeralda-mega-rayquaza-deck-sleeves', 'sku'=>'FK-ACC-SLV-MRAY', 'set'=>'Storm Emeralda', 'cat'=>'accessories', 'cond'=>'Sealed', 'moq'=>24, 'step'=>24, 'status'=>'in', 'weight'=>0.06, 'hidden'=>false,
     'ladder'=>[[1,12.73],[24,11.50],[144,8.60]]],
    ['id'=>'ultra-pro-pikachu-alcove-tower-deck-box', 'sku'=>'FK-ACC-UP-ALCT', 'set'=>'', 'cat'=>'accessories', 'cond'=>'Sealed', 'moq'=>12, 'step'=>12, 'status'=>'in', 'weight'=>0.2, 'hidden'=>false,
     'ladder'=>[[1,20.59],[12,18.50],[72,13.90]]],
    ['id'=>'ultra-pro-pikachu-deck-protector-sleeves-65-ct', 'sku'=>'FK-ACC-UP-SLV65', 'set'=>'', 'cat'=>'accessories', 'cond'=>'Sealed', 'moq'=>24, 'step'=>24, 'status'=>'in', 'weight'=>0.06, 'hidden'=>false,
     'ladder'=>[[1,10.78],[24,9.10],[144,5.40]]],
    ['id'=>'pokemon-playmat-assorted-designs', 'sku'=>'FK-ACC-MAT-AST', 'set'=>'', 'cat'=>'accessories', 'cond'=>'Sealed', 'moq'=>10, 'step'=>5, 'status'=>'in', 'weight'=>0.45, 'hidden'=>false,
     'ladder'=>[[1,28.00],[10,22.00],[50,19.00]]],
    ['id'=>'pokemon-deck-box-assorted', 'sku'=>'FK-ACC-DBX-AST', 'set'=>'', 'cat'=>'accessories', 'cond'=>'Sealed', 'moq'=>20, 'step'=>10, 'status'=>'in', 'weight'=>0.1, 'hidden'=>false,
     'ladder'=>[[1,12.00],[20,9.40],[100,8.00]]],
    ['id'=>'pokemon-card-sleeves-64-ct-assorted-designs', 'sku'=>'FK-ACC-SLV64-AST', 'set'=>'', 'cat'=>'accessories', 'cond'=>'Sealed', 'moq'=>20, 'step'=>10, 'status'=>'in', 'weight'=>0.06, 'hidden'=>false,
     'ladder'=>[[1,8.00],[20,6.20],[100,5.20]]],
    ['id'=>'mega-rayquaza-ex-sar-245-191-near-mint', 'sku'=>'FK-SGL-MRAY-SAR245', 'set'=>'Storm Emeralda', 'cat'=>'singles', 'cond'=>'Near Mint', 'moq'=>3, 'step'=>1, 'status'=>'in', 'weight'=>0.05, 'hidden'=>false,
     'ladder'=>[[1,120.00],[3,102.00],[25,90.00]]],
    ['id'=>'pikachu-ex-sar-240-191-psa-10-gem-mint', 'sku'=>'FK-SGL-PIKA-PSA10', 'set'=>'', 'cat'=>'singles', 'cond'=>'Graded', 'moq'=>1, 'step'=>1, 'status'=>'in', 'weight'=>0.15, 'hidden'=>false,
     'ladder'=>[[1,495.00],[3,470.00]]],
    ['id'=>'mega-floette-ex-japanese', 'sku'=>'FK-SGL-MFLO', 'set'=>'Ninja Spinner', 'cat'=>'singles', 'cond'=>'Near Mint', 'moq'=>1, 'step'=>1, 'status'=>'in', 'weight'=>0.05, 'hidden'=>false,
     'ladder'=>[[1,56.40],[6,51.00]]],
    ['id'=>'mega-excadrill-ex-japanese', 'sku'=>'FK-SGL-MEXC', 'set'=>'Abyss Eye', 'cat'=>'singles', 'cond'=>'Near Mint', 'moq'=>1, 'step'=>1, 'status'=>'in', 'weight'=>0.05, 'hidden'=>false,
     'ladder'=>[[1,65.80],[6,59.50]]],
    ['id'=>'mega-greninja-ex-japanese', 'sku'=>'FK-SGL-MGRE', 'set'=>'Ninja Spinner', 'cat'=>'singles', 'cond'=>'Near Mint', 'moq'=>1, 'step'=>1, 'status'=>'in', 'weight'=>0.05, 'hidden'=>false,
     'ladder'=>[[1,84.60],[6,76.60]]],
    ['id'=>'mega-darkrai-ex-japanese', 'sku'=>'FK-SGL-MDRK', 'set'=>'Abyss Eye', 'cat'=>'singles', 'cond'=>'Near Mint', 'moq'=>1, 'step'=>1, 'status'=>'in', 'weight'=>0.05, 'hidden'=>false,
     'ladder'=>[[1,182.30],[6,165.00]]],
    ['id'=>'pikachu-ex-special-illustration-rare-277-217', 'sku'=>'FK-SGL-PIKA-SAR277', 'set'=>'', 'cat'=>'singles', 'cond'=>'Near Mint', 'moq'=>1, 'step'=>1, 'status'=>'in', 'weight'=>0.05, 'hidden'=>false,
     'ladder'=>[[1,362.70],[6,328.30]]],
    ['id'=>'mega-charizard-y-ex-hyper-rare', 'sku'=>'FK-SGL-MCHY-HR', 'set'=>'', 'cat'=>'singles', 'cond'=>'Near Mint', 'moq'=>1, 'step'=>1, 'status'=>'in', 'weight'=>0.05, 'hidden'=>false,
     'ladder'=>[[1,410.60],[6,371.70]]],
    ['id'=>'30th-celebration-premium-deck-set-espeon-and-umbreon', 'sku'=>'FK-PRM-30C-ESUM', 'set'=>'30th Celebration', 'cat'=>'premium', 'cond'=>'Sealed', 'moq'=>1, 'step'=>1, 'status'=>'in', 'weight'=>1.2, 'hidden'=>false,
     'ladder'=>[[1,432.80],[6,371.70]]],
    ['id'=>'30th-celebration-sylveon-ex-box', 'sku'=>'FK-PRM-SYL-01', 'set'=>'30th Celebration', 'cat'=>'premium', 'cond'=>'Sealed', 'moq'=>6, 'step'=>6, 'status'=>'in', 'weight'=>0.6, 'hidden'=>false,
     'ladder'=>[[1,21.56],[6,19.40],[36,14.60]]],
    ['id'=>'30th-celebration-tech-sticker-collection', 'sku'=>'FK-PRM-30C-STK', 'set'=>'30th Celebration', 'cat'=>'premium', 'cond'=>'Sealed', 'moq'=>12, 'step'=>12, 'status'=>'in', 'weight'=>0.25, 'hidden'=>false,
     'ladder'=>[[1,14.70],[12,13.20],[72,10.00]]],
    ['id'=>'pitch-black-elite-trainer-box', 'sku'=>'FK-ETB-PB-01', 'set'=>'Abyss Eye', 'cat'=>'etb', 'cond'=>'Sealed', 'moq'=>4, 'step'=>4, 'status'=>'in', 'weight'=>0.9, 'hidden'=>false,
     'ladder'=>[[1,44.09],[4,39.60],[24,29.90]]],
    ['id'=>'chaos-rising-elite-trainer-box', 'sku'=>'FK-ETB-CR-01', 'set'=>'Ninja Spinner', 'cat'=>'etb', 'cond'=>'Sealed', 'moq'=>4, 'step'=>4, 'status'=>'in', 'weight'=>0.9, 'hidden'=>false,
     'ladder'=>[[1,44.09],[4,39.60],[24,29.90]]],
    ['id'=>'perfect-order-elite-trainer-box', 'sku'=>'FK-ETB-PO-01', 'set'=>'Nihil Zero', 'cat'=>'etb', 'cond'=>'Sealed', 'moq'=>4, 'step'=>4, 'status'=>'in', 'weight'=>0.9, 'hidden'=>false,
     'ladder'=>[[1,44.09],[4,39.60],[24,29.90]]],
    ['id'=>'mega-evolution-ascended-heroes-elite-trainer-box', 'sku'=>'FK-ETB-AH-01', 'set'=>'Mega Dream ex', 'cat'=>'etb', 'cond'=>'Sealed', 'moq'=>4, 'step'=>4, 'status'=>'in', 'weight'=>0.9, 'hidden'=>false,
     'ladder'=>[[1,44.09],[4,39.60],[24,29.90]]],
    ['id'=>'30th-celebration-elite-trainer-box-case-10-ct', 'sku'=>'FK-ETB-30C-C10', 'set'=>'30th Celebration', 'cat'=>'etb', 'cond'=>'Sealed', 'moq'=>1, 'step'=>1, 'status'=>'in', 'weight'=>10, 'hidden'=>false,
     'ladder'=>[[1,440.50],[6,331.70]]],
    ['id'=>'mega-dream-ex-m2a-booster-box', 'sku'=>'FK-BB-M2A-01', 'set'=>'Mega Dream ex', 'cat'=>'boxes', 'cond'=>'Sealed', 'moq'=>6, 'step'=>6, 'status'=>'in', 'weight'=>0.4, 'hidden'=>false,
     'ladder'=>[[1,175.00],[6,149.00],[24,136.00]]],
    ['id'=>'inferno-x-booster-box', 'sku'=>'FK-BB-INFX-01', 'set'=>'Inferno X', 'cat'=>'boxes', 'cond'=>'Sealed', 'moq'=>6, 'step'=>6, 'status'=>'in', 'weight'=>0.4, 'hidden'=>false,
     'ladder'=>[[1,217.10],[6,207.20],[36,185.50]]],
    ['id'=>'mega-symphonia-booster-box', 'sku'=>'FK-BB-M1S-01', 'set'=>'Mega Symphonia', 'cat'=>'boxes', 'cond'=>'Sealed', 'moq'=>6, 'step'=>6, 'status'=>'in', 'weight'=>0.4, 'hidden'=>false,
     'ladder'=>[[1,109.97],[6,105.00],[36,94.00]]],
    ['id'=>'mega-brave-booster-box', 'sku'=>'FK-BB-M1L-01', 'set'=>'Mega Brave', 'cat'=>'boxes', 'cond'=>'Sealed', 'moq'=>6, 'step'=>6, 'status'=>'in', 'weight'=>0.4, 'hidden'=>false,
     'ladder'=>[[1,119.97],[6,114.50],[36,102.50]]],
    ['id'=>'abyss-eye-m5-booster-box', 'sku'=>'FK-BB-M5-01', 'set'=>'Abyss Eye', 'cat'=>'boxes', 'cond'=>'Sealed', 'moq'=>6, 'step'=>6, 'status'=>'in', 'weight'=>0.4, 'hidden'=>false,
     'ladder'=>[[1,198.00],[6,168.00],[24,152.00]]],
    ['id'=>'ninja-spinner-m4-booster-box', 'sku'=>'FK-BB-M4-01', 'set'=>'Ninja Spinner', 'cat'=>'boxes', 'cond'=>'Sealed', 'moq'=>6, 'step'=>6, 'status'=>'low', 'weight'=>0.4, 'hidden'=>false,
     'ladder'=>[[1,165.00],[6,141.00],[24,129.00]]],
    ['id'=>'nihil-zero-booster-box', 'sku'=>'FK-BB-NZ-01', 'set'=>'Nihil Zero', 'cat'=>'boxes', 'cond'=>'Sealed', 'moq'=>6, 'step'=>6, 'status'=>'in', 'weight'=>0.4, 'hidden'=>false,
     'ladder'=>[[1,83.30],[6,82.60],[36,81.20]]],
    ['id'=>'terastal-festival-booster-box', 'sku'=>'FK-BB-SV8A-01', 'set'=>'Terastal Festival ex', 'cat'=>'boxes', 'cond'=>'Sealed', 'moq'=>6, 'step'=>6, 'status'=>'in', 'weight'=>0.4, 'hidden'=>false,
     'ladder'=>[[1,88.20],[6,85.10],[36,78.40]]],
  ],

  /* countries: '*' = everywhere, or a list of country codes.
     type 'bitcoin' = paid on the site to btc_address above; any other method = you send the details */
  'payments' => [
    'bitcoin'  => ['countries'=>'*', 'enabled'=>true, 'type'=>'bitcoin'],
    'crypto'   => ['countries'=>'*', 'enabled'=>true],
  ],

  /* codes; shoppers see the names in their own language */
  'countries' => ['BE'=>'Belgium'],

  /* shipping (USD) = zone per-order price + per-kg price × order weight, for each delivery option; totals round up
     to whole dollars. Starting rates for Japan → Belgium: check them against your carrier's real costs. */
  'shipping' => [
    'methods'  => ['standard'=>[], 'express'=>[]],
    'round_up' => true,
    'zones' => [
      ['name'=>'Belgium', 'countries'=>['BE'], 'standard'=>['base'=>11, 'per_kg'=>8], 'express'=>['base'=>22, 'per_kg'=>15]],
    ],
    'rest' => ['standard'=>['base'=>15, 'per_kg'=>11], 'express'=>['base'=>30, 'per_kg'=>22]],
  ],
];

/* shared set, series and category data; the text of each is per language */
$d['categories'] = ['boxes'=>[], 'etb'=>[], 'premium'=>[], 'singles'=>[], 'accessories'=>[]];
$d['series'] = ['mega'=>['slug'=>'mega-evolution'], 'sv'=>['slug'=>'scarlet-violet']];
$d['sets'] = [
  'Aura Seeker (Hadou Seeker)' => ['slug'=>'aura-seeker', 'series'=>'mega', 'code'=>''],
  'Storm Emeralda'       => ['slug'=>'storm-emeralda', 'series'=>'mega', 'code'=>'M6'],
  '30th Celebration'     => ['slug'=>'30th-celebration', 'series'=>'mega', 'code'=>'M6a'],
  'Abyss Eye'            => ['slug'=>'abyss-eye', 'series'=>'mega', 'code'=>'M5'],
  'Ninja Spinner'        => ['slug'=>'ninja-spinner', 'series'=>'mega', 'code'=>'M4'],
  'Nihil Zero'           => ['slug'=>'nihil-zero', 'series'=>'mega', 'code'=>'M3'],
  'Mega Dream ex'        => ['slug'=>'mega-dream-ex', 'series'=>'mega', 'code'=>'M2a'],
  'Inferno X'            => ['slug'=>'inferno-x', 'series'=>'mega', 'code'=>'M2'],
  'Mega Symphonia'       => ['slug'=>'mega-symphonia', 'series'=>'mega', 'code'=>'M1S'],
  'Mega Brave'           => ['slug'=>'mega-brave', 'series'=>'mega', 'code'=>'M1L'],
  'Glory of Team Rocket' => ['slug'=>'glory-of-team-rocket', 'series'=>'sv', 'code'=>'SV10'],
  'Heat Wave Arena'      => ['slug'=>'heat-wave-arena', 'series'=>'sv', 'code'=>'SV9a'],
  'Terastal Festival ex' => ['slug'=>'terastal-festival-ex', 'series'=>'sv', 'code'=>'SV8a'],
  '151'                  => ['slug'=>'151', 'series'=>'sv', 'code'=>'SV2a'],
];

/* each language's text goes into 'tr' (per product, category, series, set, payment method and delivery option)
   and 'i18n' (settings text, collections, guides, pages and FAQ): see store_view() in store.php */
foreach(array_keys(LANGS) as $L){
  $c = require __DIR__."/content-$L.php";
  foreach($d['products'] as $i=>$p) $d['products'][$i]['tr'][$L] = $c['products'][$p['id']] ?? [];
  foreach(['categories', 'series', 'sets', 'payments'] as $sec)
    foreach($d[$sec] as $k=>$e) $d[$sec][$k]['tr'][$L] = $c[$sec][$k] ?? [];
  foreach($d['shipping']['methods'] as $k=>$e) $d['shipping']['methods'][$k]['tr'][$L] = $c['methods'][$k] ?? [];
  $d['i18n'][$L] = ['settings'=>$c['settings'], 'collections'=>$c['collections'], 'guides'=>$c['guides'], 'pages'=>$c['pages'], 'faqs'=>$c['faqs']];
}
return $d;
