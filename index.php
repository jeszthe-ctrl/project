<?php
/* =============================================================
   FUDAKURA — wholesale Japanese Pokémon TCG
   Single-file storefront. Drop on any PHP 7.4+ host.

   SETUP
   1. Edit $CONFIG below (email, phone, business details).
   2. Put product photos in  assets/products/{product-id}.jpg
      Extra angles:          assets/products/{product-id}-2.jpg, -3.jpg
      The page auto-detects them. Missing photo = tidy placeholder.
   3. Edit $PRODUCTS to match live inventory.
   4. Make sure mail() works, or swap send_order_mail() for SMTP.
   ============================================================= */

session_start();
error_reporting(E_ALL & ~E_NOTICE & ~E_WARNING);

/* ---------------- CONFIG ---------------- */
$CONFIG = [
  'brand'        => 'FUDAKURA',
  'kanji'        => '札蔵',
  'tagline'      => 'Wholesale Japanese Pokémon TCG',
  'legal_name'   => 'Fudakura',                    // registered company name
  'address'      => 'Japan',                        // business address
  'email'        => 'orders@fudakura.com',
  'phone'        => '',                             // shown on confirmation if set
  'order_email'  => 'orders@fudakura.com',          // where new orders are sent
  'domain'       => 'https://fudakura.com',
  'reply_hours'  => 12,                             // hours to send payment details
  'hold_hours'   => 48,
];

$CURRENCIES = [
  'USD' => ['rate'=>1,     'sym'=>'$',    'dec'=>2],
  'EUR' => ['rate'=>0.92,  'sym'=>'€',    'dec'=>2],
  'JPY' => ['rate'=>156,   'sym'=>'¥',    'dec'=>0],
  'GBP' => ['rate'=>0.79,  'sym'=>'£',    'dec'=>2],
  'CAD' => ['rate'=>1.37,  'sym'=>'CA$',  'dec'=>2],
  'AUD' => ['rate'=>1.52,  'sym'=>'A$',   'dec'=>2],
];

$CATEGORIES = [
  'boxes'       => ['label'=>'Booster Boxes',        'blurb'=>'Sealed Japanese booster boxes, shipped by the case.'],
  'etb'         => ['label'=>'Elite Trainer Boxes',  'blurb'=>'ETBs and starter sets with sleeves, dice and promos.'],
  'premium'     => ['label'=>'Premium & Special Sets','blurb'=>'Premium decks, collection boxes and limited commemoratives.'],
  'singles'     => ['label'=>'Single Cards',         'blurb'=>'Graded and raw singles, including SAR, AR and promos.'],
  'accessories' => ['label'=>'Accessories',          'blurb'=>'Sleeves, deck boxes, playmats and storage.'],
];

/* ladder: [min_qty, unit_price_usd] ascending */
$PRODUCTS = [
  [ 'id'=>'storm-emeralda-elite-trainer-box', 'sku'=>'FK-ETB-SE-01',
    'name'=>'Storm Emeralda Elite Trainer Box', 'set'=>'Storm Emeralda', 'cat'=>'etb',
    'moq'=>6, 'step'=>6, 'status'=>'in', 'stock'=>180,
    'ladder'=>[[1,45.00],[6,38.00],[18,36.00],[36,34.00]],
    'desc'=>'Japanese Elite Trainer Box for the Storm Emeralda expansion. Sealed factory case, includes card sleeves, damage counters, dice, energy cards and the set promo. Sold in multiples of six.' ],

  [ 'id'=>'30th-celebration-m6a-booster-box', 'sku'=>'FK-BB-M6A-01',
    'name'=>'30th Celebration (M6A) Booster Box', 'set'=>'30th Celebration', 'cat'=>'boxes',
    'moq'=>6, 'step'=>6, 'status'=>'in', 'stock'=>96,
    'ladder'=>[[1,327.00],[6,279.00],[12,267.00],[24,255.00]],
    'desc'=>'Anniversary booster box from the 30th Celebration series. High pull-rate set with reprinted classic illustrations. Sealed Japanese domestic release.' ],

  [ 'id'=>'mega-rayquaza-ex-mur', 'sku'=>'FK-SGL-MRAY-MUR',
    'name'=>'Mega Rayquaza ex — Master Ultra Rare', 'set'=>'Storm Emeralda', 'cat'=>'singles',
    'moq'=>1, 'step'=>1, 'status'=>'in', 'stock'=>8,
    'ladder'=>[[1,1207.30],[3,1150.00],[6,1092.90]],
    'desc'=>'Japanese Master Ultra Rare Mega Rayquaza ex. Pack-fresh, sleeved and toploaded on dispatch. Condition graded NM unless otherwise noted on the invoice.' ],

  [ 'id'=>'mega-gengar-ex-sir', 'sku'=>'FK-SGL-MGEN-SIR',
    'name'=>'Mega Gengar ex — Special Illustration Rare', 'set'=>'Mega Dream ex', 'cat'=>'singles',
    'moq'=>1, 'step'=>1, 'status'=>'in', 'stock'=>11,
    'ladder'=>[[1,1033.50],[3,985.00],[6,935.60]],
    'desc'=>'Japanese Special Illustration Rare Mega Gengar ex. Pack-fresh, sleeved and toploaded on dispatch. Condition graded NM unless otherwise noted.' ],

  [ 'id'=>'abyss-eye-elite-trainer-box', 'sku'=>'FK-ETB-AE-01',
    'name'=>'Abyss Eye Elite Trainer Box', 'set'=>'Abyss Eye', 'cat'=>'etb',
    'moq'=>6, 'step'=>6, 'status'=>'new', 'stock'=>240,
    'ladder'=>[[1,42.00],[6,35.50],[18,33.60],[36,32.00]],
    'desc'=>'Elite Trainer Box for the Abyss Eye expansion. Sealed Japanese release with sleeves, dice, counters and promo card.' ],

  [ 'id'=>'30th-celebration-greninja-ex-box', 'sku'=>'FK-PRM-GRE-01',
    'name'=>'30th Celebration Greninja ex Box', 'set'=>'30th Celebration', 'cat'=>'premium',
    'moq'=>6, 'step'=>6, 'status'=>'new', 'stock'=>310,
    'ladder'=>[[1,21.56],[6,19.40],[18,16.80],[36,14.60]],
    'desc'=>'Special collection box built around the promo Greninja ex, with additional booster packs. Strong single-unit retail margin at case pricing.' ],

  [ 'id'=>'heat-wave-arena-booster-box', 'sku'=>'FK-BB-SV9A-01',
    'name'=>'Heat Wave Arena (SV9a) Booster Box', 'set'=>'Heat Wave Arena', 'cat'=>'boxes',
    'moq'=>6, 'step'=>6, 'status'=>'new', 'stock'=>64,
    'ladder'=>[[1,207.11],[6,197.70],[18,186.00],[36,177.00]],
    'desc'=>'Sealed SV9a Heat Wave Arena booster box, Japanese domestic release. Consistent reorder line for shops running draft and league events.' ],

  [ 'id'=>'glory-of-team-rocket-booster-box', 'sku'=>'FK-BB-SV10-01',
    'name'=>'Glory of Team Rocket (SV10) Booster Box', 'set'=>'Glory of Team Rocket', 'cat'=>'boxes',
    'moq'=>6, 'step'=>6, 'status'=>'new', 'stock'=>52,
    'ladder'=>[[1,261.40],[6,249.50],[18,235.00],[36,223.40]],
    'desc'=>'Sealed SV10 Glory of Team Rocket booster box. One of the strongest secondary-market sets of the year; allocation moves quickly.' ],

  [ 'id'=>'mega-lucario-z-booster-box', 'sku'=>'FK-BB-MLZ-01',
    'name'=>'Mega Lucario Z Booster Box', 'set'=>'Mega Lucario Z', 'cat'=>'boxes',
    'moq'=>6, 'step'=>6, 'status'=>'preorder', 'stock'=>0, 'release'=>'November 2026',
    'ladder'=>[[1,121.43],[6,115.90],[18,109.50],[36,103.80]],
    'desc'=>'Preorder allocation for the Mega Lucario Z booster box, releasing November 2026. Preorders are invoiced at allocation, not at request.' ],
];

/* payment methods: which countries each is offered to */
$PAYMENTS = [
  'crypto'   => ['label'=>'Cryptocurrency',    'note'=>'BTC, ETH or USDT (TRC-20 / ERC-20).', 'countries'=>'*'],
  'cashapp'  => ['label'=>'Cash App',          'note'=>'US customers only.',                  'countries'=>['US']],
  'applepay' => ['label'=>'Apple Pay',         'note'=>'US customers only.',                  'countries'=>['US']],
  'ukbank'   => ['label'=>'UK bank transfer',  'note'=>'UK customers only. Faster Payments.', 'countries'=>['GB']],
  'other'    => ['label'=>'Other / discuss with us', 'note'=>'Tell us what works and we will arrange it.', 'countries'=>'*'],
];

$COUNTRIES = ['US'=>'United States','GB'=>'United Kingdom','CA'=>'Canada','AU'=>'Australia','JP'=>'Japan','DE'=>'Germany','FR'=>'France','ES'=>'Spain','IT'=>'Italy','NL'=>'Netherlands','BE'=>'Belgium','SE'=>'Sweden','NO'=>'Norway','DK'=>'Denmark','FI'=>'Finland','IE'=>'Ireland','PL'=>'Poland','PT'=>'Portugal','CH'=>'Switzerland','AT'=>'Austria','CZ'=>'Czechia','GR'=>'Greece','SG'=>'Singapore','MY'=>'Malaysia','TH'=>'Thailand','PH'=>'Philippines','ID'=>'Indonesia','VN'=>'Vietnam','KR'=>'South Korea','TW'=>'Taiwan','HK'=>'Hong Kong','NZ'=>'New Zealand','MX'=>'Mexico','BR'=>'Brazil','AR'=>'Argentina','CL'=>'Chile','ZA'=>'South Africa','AE'=>'United Arab Emirates','SA'=>'Saudi Arabia','IL'=>'Israel','TR'=>'Turkey','IN'=>'India','NG'=>'Nigeria','KE'=>'Kenya','EG'=>'Egypt','CM'=>'Cameroon','GH'=>'Ghana'];

/* ---------------- HELPERS ---------------- */
function h($s){ return htmlspecialchars((string)$s, ENT_QUOTES, 'UTF-8'); }

function product($id){ global $PRODUCTS; foreach($PRODUCTS as $p){ if($p['id']===$id) return $p; } return null; }

function unit_price($p, $qty){
  $price = $p['ladder'][0][1];
  foreach($p['ladder'] as $t){ if($qty >= $t[0]) $price = $t[1]; }
  return $price;
}

function cur_code(){
  global $CURRENCIES;
  $c = $_GET['cur'] ?? $_SESSION['cur'] ?? 'USD';
  if(!isset($CURRENCIES[$c])) $c = 'USD';
  $_SESSION['cur'] = $c;
  return $c;
}

function money($usd){
  global $CURRENCIES;
  $c = cur_code(); $m = $CURRENCIES[$c];
  return $m['sym'] . number_format($usd * $m['rate'], $m['dec']);
}

function photos($id){
  $out = [];
  foreach(['','-2','-3','-4'] as $suffix){
    foreach(['jpg','jpeg','png','webp'] as $ext){
      $rel = "assets/products/{$id}{$suffix}.{$ext}";
      if(file_exists(__DIR__.'/'.$rel)){ $out[] = $rel; break; }
    }
  }
  return $out;
}

function cart(){ return $_SESSION['cart'] ?? []; }
function cart_units(){ $n=0; foreach(cart() as $q) $n += $q; return $n; }
function cart_lines(){
  $lines = [];
  foreach(cart() as $id=>$qty){
    $p = product($id); if(!$p) continue;
    $u = unit_price($p,$qty);
    $lines[] = ['p'=>$p,'qty'=>$qty,'unit'=>$u,'total'=>$u*$qty,'saved'=>($p['ladder'][0][1]-$u)*$qty];
  }
  return $lines;
}
function cart_total(){ $t=0; foreach(cart_lines() as $l) $t += $l['total']; return $t; }
function cart_saved(){ $t=0; foreach(cart_lines() as $l) $t += $l['saved']; return $t; }

function url($p, $extra=[]){
  $q = array_merge(['p'=>$p], $extra);
  return 'index.php?'.http_build_query($q);
}

function send_order_mail($order){
  global $CONFIG;
  $body  = "NEW ORDER  {$order['ref']}\n";
  $body .= str_repeat('=',50)."\n\n";
  $body .= "PAYMENT METHOD CHOSEN: {$order['payment_label']}\n";
  $body .= "-> Send payment details to this customer manually.\n\n";
  $body .= "CONTACT\n";
  $body .= "  Name:    {$order['name']}\n";
  $body .= "  Company: {$order['company']}\n";
  $body .= "  Email:   {$order['email']}\n";
  $body .= "  Phone:   {$order['phone']}\n\n";
  $body .= "SHIP TO\n";
  $body .= "  {$order['address1']}\n";
  if($order['address2']) $body .= "  {$order['address2']}\n";
  $body .= "  {$order['city']}, {$order['region']} {$order['postcode']}\n";
  $body .= "  {$order['country_name']}\n\n";
  $body .= "ITEMS\n";
  foreach($order['lines'] as $l){
    $body .= sprintf("  %-46s %4d x %10s = %10s\n",
      $l['name'], $l['qty'], $l['unit'], $l['total']);
  }
  $body .= "\n  GOODS TOTAL: {$order['total']} ({$order['currency']})\n";
  $body .= "  Freight and duty quoted separately.\n\n";
  if($order['notes']) $body .= "NOTES\n  {$order['notes']}\n\n";
  $body .= "Submitted: {$order['time']}\n";

  $headers = "From: {$CONFIG['brand']} <{$CONFIG['order_email']}>\r\n";
  $headers .= "Reply-To: {$order['email']}\r\n";
  @mail($CONFIG['order_email'], "New order {$order['ref']} — {$order['payment_label']}", $body, $headers);

  /* customer confirmation */
  $c  = "Thank you — we have your order.\n\n";
  $c .= "Order reference: {$order['ref']}\n";
  $c .= "Goods total: {$order['total']} ({$order['currency']})\n";
  $c .= "Payment method selected: {$order['payment_label']}\n\n";
  $c .= "WHAT HAPPENS NEXT\n";
  $c .= "Your stock is reserved for {$CONFIG['hold_hours']} hours. We will contact you\n";
  $c .= "by email or text within {$CONFIG['reply_hours']} hours with the payment details\n";
  $c .= "for the method you selected, together with your final invoice\n";
  $c .= "including shipping.\n\n";
  $c .= "Quote {$order['ref']} on your payment so we can match it to your order.\n";
  $c .= "Once payment clears we dispatch within {$CONFIG['hold_hours']} hours from Japan\n";
  $c .= "with tracking.\n\n";
  $c .= "Questions: {$CONFIG['email']}\n";
  $c .= "{$CONFIG['legal_name']} — {$CONFIG['address']}\n";
  @mail($order['email'], "Order {$order['ref']} received — {$CONFIG['brand']}", $c,
        "From: {$CONFIG['brand']} <{$CONFIG['order_email']}>\r\n");
}

/* ---------------- ACTIONS (POST / redirect) ---------------- */
$errors = [];
if($_SERVER['REQUEST_METHOD'] === 'POST'){
  $action = $_POST['action'] ?? '';

  if($action === 'add'){
    $p = product($_POST['id'] ?? '');
    if($p){
      $qty = max($p['moq'], (int)round(((int)$_POST['qty'] ?: $p['moq']) / $p['step']) * $p['step']);
      $_SESSION['cart'][$p['id']] = $qty;
    }
    header('Location: '.url('cart')); exit;
  }

  if($action === 'update'){
    foreach(($_POST['qty'] ?? []) as $id=>$q){
      $p = product($id); if(!$p) continue;
      $q = (int)$q;
      if($q <= 0){ unset($_SESSION['cart'][$id]); continue; }
      $_SESSION['cart'][$id] = max($p['moq'], (int)round($q / $p['step']) * $p['step']);
    }
    header('Location: '.url('cart')); exit;
  }

  if($action === 'remove'){
    unset($_SESSION['cart'][$_POST['id'] ?? '']);
    header('Location: '.url('cart')); exit;
  }

  if($action === 'order'){
    $f = [];
    foreach(['name','company','email','phone','country','address1','address2','city','region','postcode','notes','payment'] as $k){
      $f[$k] = trim($_POST[$k] ?? '');
    }
    if(!cart_lines())                                    $errors[] = 'Your order is empty.';
    if($f['name'] === '')                                $errors[] = 'Enter the name the order ships to.';
    if(!filter_var($f['email'], FILTER_VALIDATE_EMAIL))  $errors[] = 'Enter a valid email address.';
    if($f['phone'] === '')                               $errors[] = 'Enter a phone number — we send payment details by text.';
    if(!isset($COUNTRIES[$f['country']]))                $errors[] = 'Select your country.';
    if($f['address1'] === '')                            $errors[] = 'Enter a street address.';
    if($f['city'] === '')                                $errors[] = 'Enter a city.';
    if(!isset($PAYMENTS[$f['payment']]))                 $errors[] = 'Choose how you want to pay.';
    if(empty($_POST['agree']))                           $errors[] = 'Confirm you understand payment details follow by email or text.';

    if(!$errors){
      $lines = [];
      foreach(cart_lines() as $l){
        $lines[] = ['name'=>$l['p']['name'].' ('.$l['p']['sku'].')','qty'=>$l['qty'],
                    'unit'=>money($l['unit']),'total'=>money($l['total'])];
      }
      $order = $f + [
        'ref'           => 'FK-'.date('y').'-'.strtoupper(substr(bin2hex(random_bytes(3)),0,5)),
        'country_name'  => $COUNTRIES[$f['country']],
        'payment_label' => $PAYMENTS[$f['payment']]['label'],
        'lines'         => $lines,
        'total'         => money(cart_total()),
        'currency'      => cur_code(),
        'time'          => gmdate('Y-m-d H:i').' UTC',
      ];
      send_order_mail($order);
      $_SESSION['last_order'] = $order;
      $_SESSION['cart'] = [];
      header('Location: '.url('received')); exit;
    }
    $form = $f;
  }
}

/* ---------------- ROUTE ---------------- */
$page = $_GET['p'] ?? 'home';
if($_SERVER['REQUEST_METHOD']==='POST' && ($_POST['action']??'')==='order' && $errors) $page = 'checkout';

$cat   = $_GET['cat'] ?? '';
$q     = trim($_GET['q'] ?? '');
$prod  = $page === 'product' ? product($_GET['id'] ?? '') : null;
if($page === 'product' && !$prod) $page = 'catalog';

/* SEO per page */
$titles = [
  'home'     => 'Wholesale Japanese Pokémon Cards | Booster Boxes & ETBs from Japan',
  'catalog'  => ($cat && isset($CATEGORIES[$cat]) ? $CATEGORIES[$cat]['label'].' Wholesale' : 'Wholesale Catalog').' | Japanese Pokémon TCG',
  'cart'     => 'Your order',
  'checkout' => 'Checkout',
  'received' => 'Order received',
  'how'      => 'How wholesale ordering works',
  'shipping' => 'Shipping, customs and delivery',
  'payment'  => 'Payment methods',
  'faq'      => 'Wholesale FAQ',
  'contact'  => 'Contact',
];
$page_title = $prod ? $prod['name'].' — Wholesale' : ($titles[$page] ?? 'Wholesale Japanese Pokémon TCG');
$page_desc  = $prod
  ? substr($prod['desc'],0,155)
  : 'Buy Japanese Pokémon TCG wholesale direct from Japan. Sealed booster boxes, Elite Trainer Boxes, premium sets and singles at published quantity-break pricing. Ships worldwide.';
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1, viewport-fit=cover">
<title><?= h($page_title) ?> | <?= h($CONFIG['brand']) ?></title>
<meta name="description" content="<?= h($page_desc) ?>">
<link rel="canonical" href="<?= h($CONFIG['domain']) ?>/<?= $page==='home'?'':h('index.php?p='.$page) ?>">
<meta name="robots" content="<?= in_array($page,['cart','checkout','received']) ? 'noindex, follow' : 'index, follow, max-image-preview:large' ?>">
<meta property="og:type" content="<?= $prod ? 'product' : 'website' ?>">
<meta property="og:site_name" content="<?= h($CONFIG['brand']) ?>">
<meta property="og:title" content="<?= h($page_title) ?>">
<meta property="og:description" content="<?= h($page_desc) ?>">
<?php $og = $prod ? photos($prod['id']) : []; if($og): ?>
<meta property="og:image" content="<?= h($CONFIG['domain'].'/'.$og[0]) ?>">
<?php endif; ?>
<meta name="twitter:card" content="summary_large_image">
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Shippori+Mincho+B1:wght@600;700;800&family=Zen+Kaku+Gothic+New:wght@400;500;700;900&display=swap" rel="stylesheet">

<script type="application/ld+json">
<?= json_encode([
 '@context'=>'https://schema.org',
 '@graph'=>array_filter([
   ['@type'=>'Organization','@id'=>$CONFIG['domain'].'/#org','name'=>$CONFIG['legal_name'],
    'url'=>$CONFIG['domain'],'email'=>$CONFIG['email'],
    'address'=>['@type'=>'PostalAddress','addressCountry'=>'JP'],
    'description'=>'Wholesale supplier of Japanese Pokémon Trading Card Game product, shipping worldwide from Japan.'],
   ['@type'=>'WebSite','@id'=>$CONFIG['domain'].'/#site','url'=>$CONFIG['domain'],
    'name'=>$CONFIG['brand'],'publisher'=>['@id'=>$CONFIG['domain'].'/#org'],
    'potentialAction'=>['@type'=>'SearchAction',
      'target'=>$CONFIG['domain'].'/index.php?p=catalog&q={search_term_string}',
      'query-input'=>'required name=search_term_string']],
   $prod ? ['@type'=>'Product','name'=>$prod['name'],'sku'=>$prod['sku'],
     'description'=>$prod['desc'],'category'=>$CATEGORIES[$prod['cat']]['label'],
     'brand'=>['@type'=>'Brand','name'=>'Pokémon'],
     'offers'=>['@type'=>'Offer','priceCurrency'=>'USD',
       'price'=>number_format(unit_price($prod,$prod['moq']),2,'.',''),
       'eligibleQuantity'=>['@type'=>'QuantitativeValue','minValue'=>$prod['moq']],
       'availability'=>$prod['status']==='preorder'?'https://schema.org/PreOrder':'https://schema.org/InStock',
       'seller'=>['@id'=>$CONFIG['domain'].'/#org']]] : null,
 ])
], JSON_UNESCAPED_SLASHES|JSON_UNESCAPED_UNICODE) ?>
</script>

<style>
:root{
  color-scheme: light dark;
  box-sizing:border-box;
  padding-top:env(safe-area-inset-top,0px);
  padding-bottom:env(safe-area-inset-bottom,0px);
  --paper:#EEF0F5; --card:#FFF; --ink:#101A31; --soft:#48547199; --ink2:#4A5672;
  --muted:#6B7690; --line:#CDD5E2; --hair:#E3E8F0;
  --brand:#1F3573; --brand2:#2E4B9C; --onbrand:#F5F7FC;
  --deep:#0D1630; --ondeep:#DCE3F3; --seal:#C2392A; --gold:#A8842F;
  --sh:0 1px 2px rgba(16,26,49,.05),0 10px 30px -16px rgba(16,26,49,.22);
}
html{scroll-padding-top:calc(env(safe-area-inset-top,0px) + 120px);}
@media (prefers-color-scheme:dark){:root:not([data-theme="light"]){
  --paper:#090E1C; --card:#101832; --ink:#E8ECF8; --ink2:#AFBAD4; --muted:#8693B0;
  --line:#26314D; --hair:#1A2338; --brand:#93A9EA; --brand2:#AEC0F5; --onbrand:#0A1024;
  --deep:#050914; --ondeep:#D5DEF2; --seal:#EC7361; --gold:#DBB663;
  --sh:0 1px 2px rgba(0,0,0,.45),0 12px 34px -18px rgba(0,0,0,.75);
}}
:root[data-theme="dark"]{
  --paper:#090E1C; --card:#101832; --ink:#E8ECF8; --ink2:#AFBAD4; --muted:#8693B0;
  --line:#26314D; --hair:#1A2338; --brand:#93A9EA; --brand2:#AEC0F5; --onbrand:#0A1024;
  --deep:#050914; --ondeep:#D5DEF2; --seal:#EC7361; --gold:#DBB663;
}
*,*::before,*::after{box-sizing:inherit}
body{margin:0;background:var(--paper);color:var(--ink);
  font-family:'Zen Kaku Gothic New','Hiragino Kaku Gothic ProN',system-ui,-apple-system,'Segoe UI',Roboto,sans-serif;
  font-size:16px;line-height:1.62;-webkit-font-smoothing:antialiased;font-variant-numeric:tabular-nums}
h1,h2,h3{font-family:'Shippori Mincho B1','Hiragino Mincho ProN',Georgia,serif;font-weight:700;line-height:1.18;margin:0}
p{margin:0}a{color:inherit}img{max-width:100%;display:block}
button,input,select,textarea{font:inherit;color:inherit}
:focus-visible{outline:2px solid var(--brand2);outline-offset:3px;border-radius:2px}
.wrap{width:min(1240px,calc(100% - 40px));margin-inline:auto}
@media(max-width:640px){.wrap{width:calc(100% - 26px)}}

/* top strip */
.strip{background:var(--deep);color:var(--ondeep);font-size:12.5px}
.strip .wrap{display:flex;justify-content:space-between;align-items:center;gap:16px;min-height:34px;flex-wrap:wrap}
.strip span{opacity:.82}
.strip a{color:var(--gold);text-decoration:none;font-weight:700}

/* header */
header.site{position:sticky;top:env(safe-area-inset-top,0px);z-index:60;
  background:color-mix(in srgb,var(--paper) 90%,transparent);backdrop-filter:blur(10px) saturate(1.3);
  border-bottom:1px solid var(--line)}
.bar{display:flex;align-items:center;gap:16px;min-height:66px}
.brand{display:flex;align-items:baseline;gap:8px;text-decoration:none;flex:none}
.brand .mk{font-family:'Shippori Mincho B1',serif;font-weight:800;font-size:20px;letter-spacing:.17em}
.brand .kj{font-family:'Shippori Mincho B1',serif;font-size:12px;color:var(--seal);
  border:1px solid var(--seal);border-radius:2px;padding:1px 4px}
form.search{flex:1;max-width:460px;display:flex}
form.search input{flex:1;background:var(--card);border:1px solid var(--line);border-right:none;
  border-radius:3px 0 0 3px;padding:9px 12px;font-size:14px;min-width:0}
form.search button{background:var(--brand);color:var(--onbrand);border:1px solid var(--brand);
  border-radius:0 3px 3px 0;padding:0 15px;font-weight:700;cursor:pointer;font-size:14px}
.tools{display:flex;align-items:center;gap:9px;margin-left:auto;flex:none}
select.pick{appearance:none;background:transparent;border:1px solid var(--line);border-radius:3px;
  padding:7px 25px 7px 9px;font-size:13px;cursor:pointer;
  background-image:linear-gradient(45deg,transparent 50%,var(--muted) 50%),linear-gradient(135deg,var(--muted) 50%,transparent 50%);
  background-position:calc(100% - 13px) 53%,calc(100% - 9px) 53%;background-size:5px 5px;background-repeat:no-repeat}
.cartbtn{display:flex;align-items:center;gap:7px;background:var(--brand);color:var(--onbrand);
  text-decoration:none;border-radius:3px;padding:9px 14px;font-size:13.5px;font-weight:700}
.cartbtn b{background:rgba(255,255,255,.22);border-radius:2px;padding:0 6px;min-width:20px;text-align:center}

/* category bar — every category one click from anywhere */
.catbar{border-bottom:1px solid var(--line);background:var(--card)}
.catbar .wrap{display:flex;gap:2px;overflow-x:auto;scrollbar-width:none}
.catbar .wrap::-webkit-scrollbar{display:none}
.catbar a{padding:12px 15px;text-decoration:none;font-size:14px;font-weight:500;color:var(--ink2);
  white-space:nowrap;border-bottom:2px solid transparent}
.catbar a:hover{color:var(--ink);border-bottom-color:var(--brand)}
.catbar a.on{color:var(--ink);border-bottom-color:var(--seal);font-weight:700}
@media(max-width:820px){form.search{order:3;max-width:none;flex-basis:100%;margin-bottom:12px}
  .bar{flex-wrap:wrap;padding-top:10px}}

/* crumbs */
.crumbs{font-size:13px;color:var(--muted);padding:16px 0 0}
.crumbs a{text-decoration:none}.crumbs a:hover{text-decoration:underline}

/* buttons */
.btn{display:inline-block;text-align:center;text-decoration:none;border:1px solid var(--brand);
  background:var(--brand);color:var(--onbrand);border-radius:3px;padding:12px 20px;
  font-size:15px;font-weight:700;cursor:pointer}
.btn:hover{background:var(--brand2);border-color:var(--brand2)}
.btn.g{background:transparent;color:var(--ink);border-color:var(--line)}
.btn.g:hover{background:transparent;border-color:var(--brand)}
.btn.wide{width:100%;padding:14px}
.btn:disabled{opacity:.45;cursor:not-allowed}

section{padding:52px 0}
.sechead{display:flex;justify-content:space-between;align-items:flex-end;gap:20px;margin-bottom:24px;flex-wrap:wrap}
.sechead h2{font-size:clamp(24px,3vw,33px)}
.sechead p{color:var(--muted);font-size:14.5px;margin-top:7px;max-width:62ch}
.sechead a{font-size:14px;font-weight:700;color:var(--brand);text-decoration:none;white-space:nowrap}

/* hero */
.hero{padding:46px 0 40px}
.hgrid{display:grid;grid-template-columns:1.02fr .98fr;gap:50px;align-items:center}
@media(max-width:960px){.hgrid{grid-template-columns:1fr;gap:32px}}
.eyebrow{font-size:12.5px;color:var(--muted);margin-bottom:18px}
.eyebrow b{color:var(--seal)}
h1{font-size:clamp(33px,5vw,56px);margin-bottom:18px}
.lede{font-size:17px;color:var(--ink2);max-width:56ch;margin-bottom:26px}
.hero-cta{display:flex;gap:11px;flex-wrap:wrap;margin-bottom:30px}
.stats{display:flex;gap:26px;flex-wrap:wrap;font-size:13px;color:var(--muted)}
.stats strong{display:block;font-size:21px;color:var(--ink)}
.heroart{position:relative;border-radius:6px;overflow:hidden;border:1px solid var(--line);
  box-shadow:var(--sh);aspect-ratio:4/3;background:var(--card)}
.heroart img{width:100%;height:100%;object-fit:cover}

/* placeholder art */
.ph{width:100%;height:100%;display:flex;align-items:center;justify-content:center;flex-direction:column;gap:6px;
  background:repeating-linear-gradient(90deg,color-mix(in srgb,var(--brand) 8%,transparent) 0 1px,transparent 1px 12px),
             linear-gradient(145deg,color-mix(in srgb,var(--brand) 13%,var(--card)),var(--card));
  color:var(--muted);font-size:12px;text-align:center;padding:14px}
.ph span:first-child{font-family:'Shippori Mincho B1',serif;font-size:20px;color:var(--brand)}

/* category tiles */
.cats{display:grid;grid-template-columns:repeat(5,1fr);gap:13px}
@media(max-width:1000px){.cats{grid-template-columns:repeat(2,1fr)}}
@media(max-width:520px){.cats{grid-template-columns:1fr}}
.cats a{background:var(--card);border:1px solid var(--line);border-radius:4px;padding:18px;
  text-decoration:none;display:block}
.cats a:hover{border-color:var(--brand)}
.cats .n{font-size:12px;color:var(--muted)}
.cats h3{font-size:16.5px;margin:7px 0}
.cats p{font-size:13px;color:var(--ink2)}

/* product grid */
.grid{display:grid;grid-template-columns:repeat(4,1fr);gap:16px}
@media(max-width:1040px){.grid{grid-template-columns:repeat(2,1fr)}}
@media(max-width:520px){.grid{grid-template-columns:1fr}}
.card{background:var(--card);border:1px solid var(--line);border-radius:4px;overflow:hidden;
  display:flex;flex-direction:column}
.card:hover{border-color:var(--brand)}
.card .art{aspect-ratio:1/1;position:relative;border-bottom:1px solid var(--hair);overflow:hidden}
.card .art img{width:100%;height:100%;object-fit:cover}
.flag{position:absolute;top:10px;left:10px;font-size:10.5px;font-weight:700;letter-spacing:.05em;
  padding:3px 7px;border-radius:2px;background:var(--ink);color:var(--paper);z-index:2}
.flag.new{background:var(--brand);color:var(--onbrand)}
.flag.pre{background:var(--gold);color:#1B1405}
.flag.low{background:var(--seal);color:#fff}
.card .in{padding:14px;display:flex;flex-direction:column;gap:7px;flex:1}
.card h3{font-size:15px;line-height:1.35}
.card h3 a{text-decoration:none}
.card .meta{font-size:12px;color:var(--muted)}
.card .px{display:flex;align-items:baseline;gap:8px;margin-top:auto}
.card .px .u{font-size:21px;font-weight:900}
.card .px .w{font-size:12.5px;color:var(--muted);text-decoration:line-through}
.card .drop{font-size:12.5px;color:var(--ink2)}
.card .drop b{color:var(--ink)}
.card form{padding:0 14px 14px;display:flex;gap:8px}
.card form .btn{flex:1;padding:9px;font-size:13.5px}

/* qty stepper */
.step{display:flex;border:1px solid var(--line);border-radius:3px;overflow:hidden;background:var(--card)}
.step button{background:transparent;border:none;padding:6px 11px;cursor:pointer;font-weight:700;color:var(--ink2)}
.step button:hover{background:var(--hair)}
.step input{width:52px;border:none;border-inline:1px solid var(--line);background:transparent;
  text-align:center;padding:6px 0;font-size:14px}

/* product page */
.pdp{display:grid;grid-template-columns:1.05fr .95fr;gap:46px;padding:26px 0 10px}
@media(max-width:900px){.pdp{grid-template-columns:1fr;gap:28px}}
.gal-main{aspect-ratio:1/1;border:1px solid var(--line);border-radius:5px;overflow:hidden;background:var(--card)}
.gal-main img{width:100%;height:100%;object-fit:cover}
.gal-thumbs{display:flex;gap:9px;margin-top:9px}
.gal-thumbs button{width:70px;height:70px;border:1px solid var(--line);border-radius:3px;overflow:hidden;
  padding:0;cursor:pointer;background:var(--card)}
.gal-thumbs button[aria-current="true"]{border-color:var(--brand);border-width:2px}
.gal-thumbs img{width:100%;height:100%;object-fit:cover}
.pdp h1{font-size:clamp(25px,3.4vw,36px);margin-bottom:10px}
.pdp .sub{font-size:13.5px;color:var(--muted);margin-bottom:18px}
.pdp .desc{color:var(--ink2);font-size:15px;margin-bottom:22px;max-width:60ch}
.ladder{border:1px solid var(--line);border-radius:4px;overflow:hidden;margin-bottom:20px;background:var(--card)}
.ladder .lh{padding:11px 16px;background:var(--hair);font-size:12.5px;font-weight:700;color:var(--ink2)}
.ladder .row{display:flex;justify-content:space-between;padding:11px 16px;font-size:14.5px;
  border-top:1px solid var(--hair);color:var(--ink2)}
.ladder .row.on{color:var(--ink);font-weight:700;background:color-mix(in srgb,var(--brand) 7%,transparent)}
.buybox{border:1px solid var(--line);border-radius:4px;padding:18px;background:var(--card)}
.buybox .big{font-size:38px;font-weight:900;line-height:1.1}
.buybox .sm{font-size:13.5px;color:var(--muted);margin-bottom:14px}
.buybox form{display:flex;gap:10px;align-items:center;margin-top:12px;flex-wrap:wrap}
.buybox form .btn{flex:1;min-width:150px}
.trustline{display:flex;gap:18px;flex-wrap:wrap;font-size:12.5px;color:var(--muted);margin-top:14px}

/* tables / cart */
.tbl{width:100%;border-collapse:collapse;background:var(--card);border:1px solid var(--line);border-radius:4px}
.tbl th{text-align:left;font-size:12px;color:var(--muted);font-weight:700;padding:12px 14px;border-bottom:1px solid var(--line)}
.tbl td{padding:14px;border-bottom:1px solid var(--hair);font-size:14.5px;vertical-align:top}
.tbl tr:last-child td{border-bottom:none}
.tbl .r{text-align:right;white-space:nowrap}
.tbl .nm{font-weight:700}
.tbl .sk{font-size:12px;color:var(--muted);margin-top:3px}
@media(max-width:700px){.tbl thead{display:none}.tbl td{display:block;border:none;padding:6px 14px}
  .tbl tr{display:block;border-bottom:1px solid var(--line);padding:10px 0}
  .tbl .r{text-align:left}}

/* checkout */
.cogrid{display:grid;grid-template-columns:1.25fr .75fr;gap:38px;align-items:start}
@media(max-width:900px){.cogrid{grid-template-columns:1fr}}
fieldset{border:1px solid var(--line);border-radius:4px;padding:20px;margin:0 0 18px;background:var(--card)}
legend{font-family:'Shippori Mincho B1',serif;font-weight:700;font-size:17px;padding:0 8px}
.fld{margin-bottom:14px}
.fld label{display:block;font-size:13px;font-weight:700;color:var(--ink2);margin-bottom:5px}
.fld input,.fld select,.fld textarea{width:100%;background:var(--paper);border:1px solid var(--line);
  border-radius:3px;padding:10px 12px;font-size:15px}
.fld textarea{min-height:82px;resize:vertical}
.two{display:grid;grid-template-columns:1fr 1fr;gap:14px}
@media(max-width:560px){.two{grid-template-columns:1fr}}
.pay{display:grid;gap:9px}
.pay label{display:flex;gap:11px;align-items:flex-start;border:1px solid var(--line);border-radius:3px;
  padding:13px 14px;cursor:pointer;background:var(--paper)}
.pay label:has(input:checked){border-color:var(--brand);border-width:2px;padding:12px 13px;
  background:color-mix(in srgb,var(--brand) 6%,transparent)}
.pay label[hidden]{display:none}
.pay input{margin-top:4px;accent-color:var(--brand)}
.pay .t{font-weight:700;font-size:14.5px}
.pay .n{font-size:12.5px;color:var(--muted)}
.notice{border-left:3px solid var(--brand);background:color-mix(in srgb,var(--brand) 6%,transparent);
  padding:13px 15px;font-size:13.5px;color:var(--ink2);border-radius:0 3px 3px 0;margin:14px 0}
.agree{display:flex;gap:10px;align-items:flex-start;font-size:13.5px;color:var(--ink2);margin:14px 0 4px}
.agree input{margin-top:4px;accent-color:var(--brand)}
.summary{border:1px solid var(--line);border-radius:4px;background:var(--card);padding:18px;position:sticky;top:130px}
.summary h3{font-size:18px;margin-bottom:12px}
.sl{display:flex;justify-content:space-between;gap:12px;font-size:13.5px;padding:8px 0;border-bottom:1px solid var(--hair)}
.sl:last-of-type{border-bottom:none}
.sl .q{color:var(--muted);font-size:12px}
.tot{display:flex;justify-content:space-between;font-size:19px;font-weight:900;padding-top:12px;
  margin-top:8px;border-top:1px solid var(--line)}
.errs{border:1px solid var(--seal);border-radius:4px;padding:14px 16px;margin-bottom:20px;
  background:color-mix(in srgb,var(--seal) 8%,transparent);font-size:14px}
.errs ul{margin:6px 0 0;padding-left:18px}

/* confirmation */
.done{max-width:720px;margin:0 auto;text-align:center;padding:20px 0}
.done .ref{font-family:'Shippori Mincho B1',serif;font-size:34px;letter-spacing:.06em;
  color:var(--brand);margin:14px 0 6px}
.done .card2{border:1px solid var(--line);border-radius:5px;background:var(--card);padding:26px;
  text-align:left;margin-top:26px}
.done ol{padding-left:20px;margin:12px 0 0}
.done li{margin-bottom:10px;font-size:14.5px;color:var(--ink2)}

/* deep band */
.deep{background:var(--deep);color:var(--ondeep);
  background-image:repeating-linear-gradient(90deg,rgba(255,255,255,.04) 0 1px,transparent 1px 14px)}
.deep h2{color:#fff}.deep p{opacity:.85;margin-top:14px;font-size:15.5px}
.deep .two2{display:grid;grid-template-columns:1fr 1fr;gap:44px;align-items:center}
@media(max-width:860px){.deep .two2{grid-template-columns:1fr;gap:26px}}
.spec{border:1px solid rgba(255,255,255,.16);border-radius:4px}
.spec div{display:flex;justify-content:space-between;padding:12px 17px;font-size:14px;
  border-bottom:1px solid rgba(255,255,255,.1)}
.spec div:last-child{border-bottom:none}
.spec span:first-child{opacity:.7}.spec span:last-child{font-weight:700;color:#fff}

/* steps */
.steps{display:grid;grid-template-columns:repeat(4,1fr);border-top:1px solid var(--line)}
@media(max-width:900px){.steps{grid-template-columns:1fr 1fr}}
@media(max-width:540px){.steps{grid-template-columns:1fr}}
.steps>div{padding:20px 18px 22px;border-right:1px solid var(--line);border-bottom:1px solid var(--line)}
.steps>div:last-child{border-right:none}
.steps .n{font-family:'Shippori Mincho B1',serif;font-size:13px;color:var(--seal);margin-bottom:10px}
.steps h3{font-size:16px;margin-bottom:7px}
.steps p{font-size:14px;color:var(--ink2)}

/* faq */
.faq details{border-bottom:1px solid var(--line);padding:15px 0}
.faq summary{cursor:pointer;font-weight:700;font-size:16px;list-style:none;display:flex;justify-content:space-between;gap:14px}
.faq summary::-webkit-details-marker{display:none}
.faq summary::after{content:"+";color:var(--seal)}
.faq details[open] summary::after{content:"–"}
.faq p{margin-top:9px;font-size:14.5px;color:var(--ink2);max-width:80ch}

/* footer */
footer.site{background:var(--deep);color:var(--ondeep);padding:46px 0 28px;margin-top:24px}
.fg{display:grid;grid-template-columns:1.6fr 1fr 1fr 1fr;gap:32px}
@media(max-width:860px){.fg{grid-template-columns:1fr 1fr}}
@media(max-width:520px){.fg{grid-template-columns:1fr}}
footer h4{font-size:12.5px;letter-spacing:.06em;color:#fff;margin:0 0 11px}
footer ul{list-style:none;margin:0;padding:0;display:grid;gap:7px}
footer a{color:var(--ondeep);text-decoration:none;opacity:.82;font-size:14px}
footer a:hover{opacity:1;text-decoration:underline}
footer .bl{font-size:14px;opacity:.8;margin-top:11px;max-width:44ch}
.legal{margin-top:30px;padding-top:18px;border-top:1px solid rgba(255,255,255,.12);
  font-size:12.5px;opacity:.72;display:grid;gap:9px}
.empty{padding:40px 0;color:var(--muted)}
</style>
</head>
<body>

<div class="strip"><div class="wrap">
  <span>Sealed product, sourced in Japan · Quantity breaks published on every listing</span>
  <a href="<?= url('catalog',['cat'=>'boxes']) ?>">Mega Lucario Z preorder open →</a>
</div></div>

<header class="site">
  <div class="wrap bar">
    <a class="brand" href="index.php">
      <span class="mk"><?= h($CONFIG['brand']) ?></span>
      <span class="kj"><?= h($CONFIG['kanji']) ?></span>
    </a>
    <form class="search" action="index.php" method="get" role="search">
      <input type="hidden" name="p" value="catalog">
      <input type="search" name="q" value="<?= h($q) ?>" placeholder="Search a set, card or SKU" aria-label="Search products">
      <button type="submit">Search</button>
    </form>
    <div class="tools">
      <select class="pick" onchange="location.href=this.value" aria-label="Currency">
        <?php foreach($CURRENCIES as $code=>$m):
          $u = $_GET; $u['cur']=$code; ?>
          <option value="index.php?<?= h(http_build_query($u)) ?>" <?= cur_code()===$code?'selected':'' ?>><?= $code ?></option>
        <?php endforeach; ?>
      </select>
      <a class="cartbtn" href="<?= url('cart') ?>">Order <b><?= cart_units() ?></b></a>
    </div>
  </div>
  <nav class="catbar" aria-label="Categories"><div class="wrap">
    <a href="<?= url('catalog') ?>" class="<?= $page==='catalog'&&!$cat?'on':'' ?>">All products</a>
    <?php foreach($CATEGORIES as $k=>$c): ?>
      <a href="<?= url('catalog',['cat'=>$k]) ?>" class="<?= $cat===$k?'on':'' ?>"><?= h($c['label']) ?></a>
    <?php endforeach; ?>
    <a href="<?= url('how') ?>" class="<?= $page==='how'?'on':'' ?>">How it works</a>
    <a href="<?= url('shipping') ?>" class="<?= $page==='shipping'?'on':'' ?>">Shipping</a>
    <a href="<?= url('payment') ?>" class="<?= $page==='payment'?'on':'' ?>">Payment</a>
    <a href="<?= url('faq') ?>" class="<?= $page==='faq'?'on':'' ?>">FAQ</a>
    <a href="<?= url('contact') ?>" class="<?= $page==='contact'?'on':'' ?>">Contact</a>
  </div></nav>
</header>

<main>
<?php if($page==='home'): ?>

  <section class="hero"><div class="wrap hgrid">
    <div>
      <div class="eyebrow"><b>Japan direct</b> · Sealed · Priced by the case · 43 countries</div>
      <h1>Wholesale Japanese Pokémon cards, shipped worldwide from Japan.</h1>
      <p class="lede">Sealed booster boxes, Elite Trainer Boxes, premium sets and singles, bought through Japanese distribution and priced by the case. Every quantity break is published — price a full order before you talk to anyone.</p>
      <div class="hero-cta">
        <a class="btn" href="<?= url('catalog') ?>">Browse the catalog</a>
        <a class="btn g" href="<?= url('how') ?>">How ordering works</a>
      </div>
      <div class="stats">
        <div><strong>50+</strong>SKUs in stock</div>
        <div><strong>6</strong>Minimum order, sealed</div>
        <div><strong>43</strong>Countries served</div>
        <div><strong><?= (int)$CONFIG['hold_hours'] ?>h</strong>Stock held on order</div>
      </div>
    </div>
    <div class="heroart">
      <?php $hp = photos('hero') ?: photos($PRODUCTS[1]['id']);
      if($hp): ?><img src="<?= h($hp[0]) ?>" alt="Sealed Japanese Pokémon booster boxes ready for wholesale dispatch from Japan">
      <?php else: ?><div class="ph"><span>札蔵</span><span>Add assets/products/hero.jpg</span></div><?php endif; ?>
    </div>
  </div></section>

  <section>
    <div class="wrap">
      <div class="sechead"><div><h2>Shop by category</h2>
        <p>Sealed cases, trainer boxes, commemorative sets, graded singles and the supplies that go out with them.</p></div>
        <a href="<?= url('catalog') ?>">See everything →</a></div>
      <div class="cats">
        <?php foreach($CATEGORIES as $k=>$c):
          $n = count(array_filter($PRODUCTS, fn($p)=>$p['cat']===$k)); ?>
          <a href="<?= url('catalog',['cat'=>$k]) ?>">
            <div class="n"><?= $n ?> listed</div><h3><?= h($c['label']) ?></h3><p><?= h($c['blurb']) ?></p>
          </a>
        <?php endforeach; ?>
      </div>
    </div>
  </section>

  <section>
    <div class="wrap">
      <div class="sechead"><div><h2>In stock now</h2>
        <p>Ready to ship from Japan at published quantity breaks. Sealed product sells in multiples of six; singles start at one.</p></div>
        <a href="<?= url('catalog') ?>">All products →</a></div>
      <div class="grid">
        <?php foreach(array_slice($PRODUCTS,0,8) as $p) include_card($p); ?>
      </div>
    </div>
  </section>

  <section class="deep"><div class="wrap two2">
    <div>
      <h2>Sealed in its original factory packaging.</h2>
      <p>Everything is bought through Japanese distribution and ships exactly as it left the factory. We do not deal in resealed, reprinted or counterfeit product, and the price on the listing is the price on your invoice.</p>
      <p style="margin-top:20px"><a class="btn" style="background:var(--gold);border-color:var(--gold);color:#1B1405" href="<?= url('how') ?>">How ordering works</a></p>
    </div>
    <div class="spec">
      <div><span>Sourcing</span><span>Japanese distribution</span></div>
      <div><span>Minimum order, sealed</span><span>6 units</span></div>
      <div><span>Minimum order, singles</span><span>1 unit</span></div>
      <div><span>Stock hold on order</span><span><?= (int)$CONFIG['hold_hours'] ?> hours</span></div>
      <div><span>Dispatch after payment</span><span>Within <?= (int)$CONFIG['hold_hours'] ?> hours</span></div>
      <div><span>Carriers</span><span>EMS · DHL · FedEx</span></div>
      <div><span>Freight quoted on</span><span>Real parcel weight</span></div>
    </div>
  </div></section>

  <section><div class="wrap">
    <div class="sechead"><div><h2>Four steps from cart to courier</h2></div></div>
    <?php steps_block($CONFIG); ?>
  </div></section>

<?php elseif($page==='catalog'):
  $list = array_filter($PRODUCTS, function($p) use($cat,$q,$CATEGORIES){
    if($cat && $p['cat']!==$cat) return false;
    if($q){
      $hay = strtolower($p['name'].' '.$p['set'].' '.$p['sku'].' '.$CATEGORIES[$p['cat']]['label']);
      if(strpos($hay, strtolower($q))===false) return false;
    }
    return true;
  }); ?>
  <div class="wrap crumbs"><a href="index.php">Home</a> / <?= $cat ? h($CATEGORIES[$cat]['label']) : 'All products' ?></div>
  <section style="padding-top:22px"><div class="wrap">
    <div class="sechead"><div>
      <h2><?= $q ? 'Results for “'.h($q).'”' : ($cat ? h($CATEGORIES[$cat]['label']) : 'Wholesale catalog') ?></h2>
      <p><?= $cat ? h($CATEGORIES[$cat]['blurb']) : 'Everything in stock, with the full quantity-break ladder published on each listing.' ?></p>
    </div><span style="color:var(--muted);font-size:14px"><?= count($list) ?> product<?= count($list)===1?'':'s' ?></span></div>
    <?php if($list): ?>
      <div class="grid"><?php foreach($list as $p) include_card($p); ?></div>
    <?php else: ?>
      <p class="empty">Nothing matches that. Try a set name, a card name or an SKU — or <a href="<?= url('catalog') ?>">browse everything</a>.</p>
    <?php endif; ?>
  </div></section>

<?php elseif($page==='product'):
  $ph = photos($prod['id']); $base = unit_price($prod,$prod['moq']); $best = end($prod['ladder']); ?>
  <div class="wrap crumbs"><a href="index.php">Home</a> / <a href="<?= url('catalog',['cat'=>$prod['cat']]) ?>"><?= h($CATEGORIES[$prod['cat']]['label']) ?></a> / <?= h($prod['name']) ?></div>
  <div class="wrap pdp">
    <div>
      <div class="gal-main">
        <?php if($ph): ?><img id="galMain" src="<?= h($ph[0]) ?>" alt="<?= h($prod['name']) ?> — wholesale Japanese Pokémon TCG">
        <?php else: ?><div class="ph"><span>札蔵</span><span>Add assets/products/<?= h($prod['id']) ?>.jpg</span></div><?php endif; ?>
      </div>
      <?php if(count($ph)>1): ?>
      <div class="gal-thumbs">
        <?php foreach($ph as $i=>$src): ?>
          <button type="button" aria-current="<?= $i===0?'true':'false' ?>" onclick="galPick(this,'<?= h($src) ?>')">
            <img src="<?= h($src) ?>" alt="<?= h($prod['name']) ?> view <?= $i+1 ?>"></button>
        <?php endforeach; ?>
      </div>
      <?php endif; ?>
    </div>
    <div>
      <h1><?= h($prod['name']) ?></h1>
      <div class="sub"><?= h($prod['sku']) ?> · <?= h($prod['set']) ?> · <?= h($CATEGORIES[$prod['cat']]['label']) ?>
        <?= $prod['status']==='preorder' ? ' · Releases '.h($prod['release']) : ($prod['stock'] ? ' · '.$prod['stock'].' in stock' : '') ?></div>
      <p class="desc"><?= h($prod['desc']) ?></p>

      <div class="ladder">
        <div class="lh">Quantity-break pricing</div>
        <?php foreach($prod['ladder'] as $i=>$t):
          $next = $prod['ladder'][$i+1] ?? null;
          $lbl = $i===0 ? 'Single unit' : ($next ? $t[0].' – '.($next[0]-1).' units' : $t[0].'+ units'); ?>
          <div class="row <?= $i===1||($i===0&&count($prod['ladder'])===1)?'on':'' ?>">
            <span><?= h($lbl) ?></span><span><?= money($t[1]) ?></span></div>
        <?php endforeach; ?>
      </div>

      <div class="buybox">
        <div class="big"><?= money($base) ?></div>
        <div class="sm">per unit at MOQ <?= $prod['moq'] ?> · down to <?= money($best[1]) ?> at <?= $best[0] ?>+</div>
        <form method="post" action="index.php">
          <input type="hidden" name="action" value="add">
          <input type="hidden" name="id" value="<?= h($prod['id']) ?>">
          <div class="step">
            <button type="button" onclick="bump(this,-<?= $prod['step'] ?>,<?= $prod['moq'] ?>)">−</button>
            <input type="number" name="qty" value="<?= $prod['moq'] ?>" min="<?= $prod['moq'] ?>" step="<?= $prod['step'] ?>" aria-label="Quantity">
            <button type="button" onclick="bump(this,<?= $prod['step'] ?>,<?= $prod['moq'] ?>)">+</button>
          </div>
          <button class="btn" type="submit"><?= $prod['status']==='preorder'?'Add preorder':'Add to order' ?></button>
        </form>
        <div class="trustline">
          <span>Sold in <?= $prod['step']>1 ? $prod['step'].'s' : 'singles' ?></span>
          <span>Held <?= (int)$CONFIG['hold_hours'] ?>h on order</span>
          <span>Ships from Japan</span>
        </div>
      </div>
    </div>
  </div>

  <section><div class="wrap">
    <div class="sechead"><div><h2>More from <?= h($CATEGORIES[$prod['cat']]['label']) ?></h2></div>
      <a href="<?= url('catalog',['cat'=>$prod['cat']]) ?>">See all →</a></div>
    <div class="grid">
      <?php $rel = array_slice(array_values(array_filter($PRODUCTS, fn($x)=>$x['cat']===$prod['cat'] && $x['id']!==$prod['id'])),0,4);
      if(!$rel) $rel = array_slice(array_values(array_filter($PRODUCTS, fn($x)=>$x['id']!==$prod['id'])),0,4);
      foreach($rel as $p) include_card($p); ?>
    </div>
  </div></section>

<?php elseif($page==='cart'): $lines = cart_lines(); ?>
  <div class="wrap crumbs"><a href="index.php">Home</a> / Your order</div>
  <section style="padding-top:22px"><div class="wrap">
    <div class="sechead"><div><h2>Your order</h2>
      <p>Quantity breaks are applied automatically. Change a quantity and the unit price recalculates.</p></div></div>
    <?php if(!$lines): ?>
      <p class="empty">Nothing here yet. <a href="<?= url('catalog') ?>">Browse the catalog →</a></p>
    <?php else: ?>
      <form method="post" action="index.php">
        <input type="hidden" name="action" value="update">
        <table class="tbl">
          <thead><tr><th>Product</th><th>Quantity</th><th class="r">Unit</th><th class="r">Line total</th><th></th></tr></thead>
          <tbody>
          <?php foreach($lines as $l): ?>
            <tr>
              <td><div class="nm"><a href="<?= url('product',['id'=>$l['p']['id']]) ?>" style="text-decoration:none"><?= h($l['p']['name']) ?></a></div>
                  <div class="sk"><?= h($l['p']['sku']) ?> · sold in <?= $l['p']['step'] ?>s</div></td>
              <td><div class="step">
                    <button type="button" onclick="bump(this,-<?= $l['p']['step'] ?>,<?= $l['p']['moq'] ?>)">−</button>
                    <input type="number" name="qty[<?= h($l['p']['id']) ?>]" value="<?= $l['qty'] ?>" min="0" step="<?= $l['p']['step'] ?>" aria-label="Quantity">
                    <button type="button" onclick="bump(this,<?= $l['p']['step'] ?>,<?= $l['p']['moq'] ?>)">+</button>
                  </div></td>
              <td class="r"><?= money($l['unit']) ?></td>
              <td class="r"><b><?= money($l['total']) ?></b></td>
              <td class="r"><button class="btn g" style="padding:7px 12px;font-size:13px"
                    formaction="index.php" name="action" value="remove" type="submit"
                    onclick="this.form.insertAdjacentHTML('beforeend','<input type=hidden name=id value=\'<?= h($l['p']['id']) ?>\'>')">Remove</button></td>
            </tr>
          <?php endforeach; ?>
          </tbody>
        </table>
        <div style="display:flex;justify-content:space-between;gap:18px;flex-wrap:wrap;margin-top:20px;align-items:center">
          <button class="btn g" type="submit">Update quantities</button>
          <div style="text-align:right">
            <?php if(cart_saved()>0): ?><div style="font-size:13.5px;color:var(--muted)">You save <?= money(cart_saved()) ?> against single-unit pricing</div><?php endif; ?>
            <div style="font-size:26px;font-weight:900;margin:4px 0 10px">Goods total <?= money(cart_total()) ?></div>
            <a class="btn" href="<?= url('checkout') ?>">Continue to checkout</a>
          </div>
        </div>
      </form>
    <?php endif; ?>
  </div></section>

<?php elseif($page==='checkout'): $lines = cart_lines(); $f = $form ?? []; ?>
  <div class="wrap crumbs"><a href="index.php">Home</a> / <a href="<?= url('cart') ?>">Order</a> / Checkout</div>
  <section style="padding-top:22px"><div class="wrap">
    <div class="sechead"><div><h2>Checkout</h2>
      <p>Tell us where it ships and how you want to pay. Nothing is charged here — we send your payment details and final invoice by email or text after you place the order.</p></div></div>

    <?php if(!$lines): ?>
      <p class="empty">Your order is empty. <a href="<?= url('catalog') ?>">Browse the catalog →</a></p>
    <?php else: ?>
    <?php if($errors): ?>
      <div class="errs"><b>Please fix the following:</b><ul><?php foreach($errors as $e) echo '<li>'.h($e).'</li>'; ?></ul></div>
    <?php endif; ?>

    <form method="post" action="index.php" class="cogrid">
      <input type="hidden" name="action" value="order">
      <div>
        <fieldset>
          <legend>Contact</legend>
          <div class="two">
            <div class="fld"><label for="name">Full name</label>
              <input id="name" name="name" required value="<?= h($f['name']??'') ?>"></div>
            <div class="fld"><label for="company">Company (optional)</label>
              <input id="company" name="company" value="<?= h($f['company']??'') ?>"></div>
          </div>
          <div class="two">
            <div class="fld"><label for="email">Email</label>
              <input id="email" name="email" type="email" required value="<?= h($f['email']??'') ?>"></div>
            <div class="fld"><label for="phone">Phone (for payment details by text)</label>
              <input id="phone" name="phone" type="tel" required value="<?= h($f['phone']??'') ?>"></div>
          </div>
        </fieldset>

        <fieldset>
          <legend>Shipping address</legend>
          <div class="fld"><label for="country">Country</label>
            <select id="country" name="country" required onchange="filterPay(this.value)">
              <option value="">Select your country…</option>
              <?php foreach($COUNTRIES as $code=>$nm): ?>
                <option value="<?= $code ?>" <?= ($f['country']??'')===$code?'selected':'' ?>><?= h($nm) ?></option>
              <?php endforeach; ?>
            </select></div>
          <div class="fld"><label for="address1">Street address</label>
            <input id="address1" name="address1" required value="<?= h($f['address1']??'') ?>"></div>
          <div class="fld"><label for="address2">Apartment, suite, unit (optional)</label>
            <input id="address2" name="address2" value="<?= h($f['address2']??'') ?>"></div>
          <div class="two">
            <div class="fld"><label for="city">City</label>
              <input id="city" name="city" required value="<?= h($f['city']??'') ?>"></div>
            <div class="fld"><label for="region">State / province / region</label>
              <input id="region" name="region" value="<?= h($f['region']??'') ?>"></div>
          </div>
          <div class="fld" style="max-width:260px"><label for="postcode">Postal code</label>
            <input id="postcode" name="postcode" value="<?= h($f['postcode']??'') ?>"></div>
        </fieldset>

        <fieldset>
          <legend>Payment method</legend>
          <div class="pay" id="payList">
            <?php foreach($PAYMENTS as $key=>$m):
              $all = $m['countries']==='*';
              $data = $all ? '*' : implode(',', $m['countries']); ?>
              <label data-countries="<?= h($data) ?>" <?= $all?'':'hidden' ?>>
                <input type="radio" name="payment" value="<?= $key ?>" <?= ($f['payment']??'')===$key?'checked':'' ?>>
                <span><span class="t"><?= h($m['label']) ?></span><br><span class="n"><?= h($m['note']) ?></span></span>
              </label>
            <?php endforeach; ?>
          </div>
          <div class="notice">
            <b>How payment works.</b> Select your method and place the order. We will send the payment
            details for that method to your email and phone within <?= (int)$CONFIG['reply_hours'] ?> hours,
            together with your final invoice including shipping. Your stock is reserved for
            <?= (int)$CONFIG['hold_hours'] ?> hours in the meantime. Quote your order reference on the
            payment so we can match it to your order.
          </div>
          <div class="fld"><label for="notes">Order notes (optional)</label>
            <textarea id="notes" name="notes" placeholder="Delivery instructions, preferred carrier, VAT/EORI number, anything else we should know."><?= h($f['notes']??'') ?></textarea></div>
          <label class="agree">
            <input type="checkbox" name="agree" value="1" <?= !empty($_POST['agree'])?'checked':'' ?>>
            <span>I understand that no payment is taken on this site, and that <?= h($CONFIG['legal_name']) ?>
            will contact me by email or text with the payment details and final invoice.</span>
          </label>
          <button class="btn wide" type="submit" style="margin-top:14px">Place order</button>
          <p style="font-size:12.5px;color:var(--muted);margin-top:10px">
            Prices exclude shipping, import duty and taxes. Freight is quoted on real parcel weight on your invoice.</p>
        </fieldset>
      </div>

      <div>
        <div class="summary">
          <h3>Order summary</h3>
          <?php foreach($lines as $l): ?>
            <div class="sl"><span><?= h($l['p']['name']) ?><br><span class="q"><?= $l['qty'] ?> × <?= money($l['unit']) ?></span></span>
              <span><?= money($l['total']) ?></span></div>
          <?php endforeach; ?>
          <?php if(cart_saved()>0): ?>
            <div class="sl"><span style="color:var(--muted)">Quantity-break saving</span>
              <span style="color:var(--seal)">− <?= money(cart_saved()) ?></span></div>
          <?php endif; ?>
          <div class="sl"><span style="color:var(--muted)">Shipping</span><span style="color:var(--muted)">Quoted on invoice</span></div>
          <div class="tot"><span>Goods total</span><span><?= money(cart_total()) ?></span></div>
          <p style="font-size:12.5px;color:var(--muted);margin-top:12px">Shown in <?= cur_code() ?>. Your invoice is issued in the same currency.</p>
          <p style="margin-top:12px"><a href="<?= url('cart') ?>" style="font-size:13.5px;font-weight:700;color:var(--brand);text-decoration:none">← Edit order</a></p>
        </div>
      </div>
    </form>
    <?php endif; ?>
  </div></section>

<?php elseif($page==='received'): $o = $_SESSION['last_order'] ?? null; ?>
  <section><div class="wrap done">
    <?php if(!$o): ?>
      <h1>No recent order</h1>
      <p class="lede" style="margin:14px auto 22px">Nothing to show here. <a href="<?= url('catalog') ?>">Browse the catalog →</a></p>
    <?php else: ?>
      <div style="font-size:13px;color:var(--muted);letter-spacing:.08em">ORDER RECEIVED</div>
      <div class="ref"><?= h($o['ref']) ?></div>
      <h1 style="font-size:clamp(24px,3.4vw,34px);margin-top:10px">Thank you — we have your order.</h1>
      <p class="lede" style="margin:14px auto 0">A confirmation is on its way to <b><?= h($o['email']) ?></b>. Your stock is reserved for <?= (int)$CONFIG['hold_hours'] ?> hours.</p>

      <div class="card2">
        <h3 style="font-size:19px">What happens next</h3>
        <ol>
          <li>We send the payment details for <b><?= h($o['payment_label']) ?></b> to your email and phone within <?= (int)$CONFIG['reply_hours'] ?> hours, with your final invoice including shipping.</li>
          <li>Quote <b><?= h($o['ref']) ?></b> on the payment so we can match it to your order.</li>
          <li>Payment clears, stock is allocated, and we dispatch within <?= (int)$CONFIG['hold_hours'] ?> hours from Japan with tracking.</li>
          <li>Tracking is emailed to you the moment the label is generated.</li>
        </ol>
        <hr style="border:none;border-top:1px solid var(--hair);margin:20px 0">
        <div class="sl" style="border:none;padding:0"><span>Goods total</span><b><?= h($o['total']) ?> <?= h($o['currency']) ?></b></div>
        <div class="sl" style="border:none;padding:4px 0 0"><span>Shipping to</span><span><?= h($o['city']) ?>, <?= h($o['country_name']) ?></span></div>
        <p style="font-size:13.5px;color:var(--muted);margin-top:16px">
          Nothing heard within <?= (int)$CONFIG['reply_hours'] ?> hours? Check your spam folder, then email
          <a href="mailto:<?= h($CONFIG['email']) ?>"><?= h($CONFIG['email']) ?></a> quoting <?= h($o['ref']) ?>.</p>
      </div>
      <p style="margin-top:24px"><a class="btn g" href="<?= url('catalog') ?>">Continue shopping</a></p>
    <?php endif; ?>
  </div></section>

<?php elseif($page==='how'): ?>
  <div class="wrap crumbs"><a href="index.php">Home</a> / How it works</div>
  <section style="padding-top:22px"><div class="wrap">
    <div class="sechead"><div><h2>How wholesale ordering works</h2>
      <p>Four steps from cart to courier. Nothing is charged on this site — you settle an invoice from your own bank or payment app.</p></div></div>
    <?php steps_block($CONFIG); ?>
    <div style="margin-top:40px;display:grid;grid-template-columns:repeat(3,1fr);gap:16px" class="cats">
      <a href="<?= url('payment') ?>"><h3>Payment methods</h3><p>What we accept, by country.</p></a>
      <a href="<?= url('shipping') ?>"><h3>Shipping &amp; customs</h3><p>Carriers, timings, duty and taxes.</p></a>
      <a href="<?= url('faq') ?>"><h3>Wholesale FAQ</h3><p>MOQs, preorders, returns and more.</p></a>
    </div>
  </div></section>

<?php elseif($page==='payment'): ?>
  <div class="wrap crumbs"><a href="index.php">Home</a> / Payment</div>
  <section style="padding-top:22px"><div class="wrap" style="max-width:820px">
    <div class="sechead"><div><h2>Payment methods</h2>
      <p>Choose your method at checkout. We send the details for it to your email and phone within <?= (int)$CONFIG['reply_hours'] ?> hours, together with your final invoice including shipping.</p></div></div>
    <table class="tbl">
      <thead><tr><th>Method</th><th>Available to</th><th>Notes</th></tr></thead>
      <tbody>
        <tr><td class="nm">Cryptocurrency</td><td>All countries</td><td>BTC, ETH or USDT (TRC-20 / ERC-20). Network fees are the sender's.</td></tr>
        <tr><td class="nm">Cash App</td><td>United States</td><td>US customers only.</td></tr>
        <tr><td class="nm">Apple Pay</td><td>United States</td><td>US customers only.</td></tr>
        <tr><td class="nm">UK bank transfer</td><td>United Kingdom</td><td>Faster Payments to a UK account in our business name.</td></tr>
        <tr><td class="nm">Other</td><td>Everywhere else</td><td>Tell us what works at checkout and we will arrange it.</td></tr>
      </tbody>
    </table>
    <div class="notice" style="margin-top:22px">
      <b>No payment is taken on this site.</b> There is no card form and no wallet credential to enter here.
      You place the order, we send the payment details, and stock is held for <?= (int)$CONFIG['hold_hours'] ?> hours
      while that happens. Always check the payment details against the email we send from
      <?= h($CONFIG['email']) ?> and quote your order reference.
    </div>
    <p style="font-size:14px;color:var(--ink2);margin-top:18px">Invoices are issued in the currency you had selected at checkout. Prices exclude shipping, import duty, VAT or GST and customs clearance fees.</p>
  </div></section>

<?php elseif($page==='shipping'): ?>
  <div class="wrap crumbs"><a href="index.php">Home</a> / Shipping</div>
  <section style="padding-top:22px"><div class="wrap" style="max-width:820px">
    <div class="sechead"><div><h2>Shipping, customs and delivery</h2>
      <p>Everything ships from Japan with tracking on every consignment.</p></div></div>
    <table class="tbl">
      <thead><tr><th>Detail</th><th>What to expect</th></tr></thead>
      <tbody>
        <tr><td class="nm">Carriers</td><td>Japan Post EMS, DHL Express and FedEx. We choose on weight, destination and your instructions.</td></tr>
        <tr><td class="nm">Dispatch</td><td>Within <?= (int)$CONFIG['hold_hours'] ?> hours of payment clearing.</td></tr>
        <tr><td class="nm">Transit</td><td>Typically 3–6 working days to North America and Europe, 2–4 within Asia-Pacific.</td></tr>
        <tr><td class="nm">Freight cost</td><td>Quoted on real parcel weight on your invoice — not estimated after you order.</td></tr>
        <tr><td class="nm">Duty and taxes</td><td>Excluded from our prices. Your carrier collects import duty, VAT or GST and clearance fees on delivery.</td></tr>
        <tr><td class="nm">Damage or shortage</td><td>Report within seven days of delivery and we replace, credit or refund the affected lines and their shipping.</td></tr>
      </tbody>
    </table>
  </div></section>

<?php elseif($page==='faq'): ?>
  <div class="wrap crumbs"><a href="index.php">Home</a> / FAQ</div>
  <section style="padding-top:22px"><div class="wrap" style="max-width:860px">
    <div class="sechead"><div><h2>Wholesale FAQ</h2></div></div>
    <div class="faq">
      <?php
      $faqs = [
        ['Do I need an account to see wholesale pricing?','No. Every product shows its full quantity-break ladder publicly, so you can price a complete order before contacting anyone. There is no application form and no approval wait.'],
        ['What is the minimum order quantity?','Sealed product starts at six units and sells in multiples of six. Graded and raw singles start at one, with breaks from three and six.'],
        ['How do I pay?','Select a method at checkout — cryptocurrency, Cash App or Apple Pay for US customers, UK bank transfer for UK customers, or the "other" option anywhere else. We send the details for your chosen method to your email and phone within '.(int)$CONFIG['reply_hours'].' hours, with your final invoice including shipping.'],
        ['When is my stock allocated?','Placing an order reserves your stock for '.(int)$CONFIG['hold_hours'].' hours. Once payment clears, the allocation is confirmed and we dispatch within '.(int)$CONFIG['hold_hours'].' hours. If payment does not clear inside the window, high-demand stock returns to general availability.'],
        ['Which countries do you ship to?','43 countries from Japan by EMS, DHL and FedEx with tracking on every consignment. Import duty, VAT or GST and clearance fees are excluded from our prices and collected by your carrier on delivery.'],
        ['Can I preorder an upcoming set?','Yes. Preorder lines commit an allocation ahead of release at the same published ladder, and are invoiced at allocation rather than at request.'],
        ['Is the product authentic?','Yes. Everything is sourced through Japanese distribution and ships sealed in its original factory packaging. We do not deal in resealed, reprinted or counterfeit product.'],
        ['What if something arrives damaged or short?','Report transit damage, a short shipment or a wrong item within seven days of delivery and we replace, credit or refund the affected lines and their shipping.'],
      ];
      foreach($faqs as $i=>$fq): ?>
        <details <?= $i===0?'open':'' ?>><summary><?= h($fq[0]) ?></summary><p><?= h($fq[1]) ?></p></details>
      <?php endforeach; ?>
    </div>
  </div></section>
  <script type="application/ld+json">
  <?= json_encode(['@context'=>'https://schema.org','@type'=>'FAQPage','mainEntity'=>array_map(fn($f)=>
    ['@type'=>'Question','name'=>$f[0],'acceptedAnswer'=>['@type'=>'Answer','text'=>$f[1]]], $faqs)],
    JSON_UNESCAPED_SLASHES|JSON_UNESCAPED_UNICODE) ?>
  </script>

<?php elseif($page==='contact'): ?>
  <div class="wrap crumbs"><a href="index.php">Home</a> / Contact</div>
  <section style="padding-top:22px"><div class="wrap" style="max-width:700px">
    <div class="sechead"><div><h2>Contact</h2>
      <p>Questions on stock, allocation pricing or an existing order.</p></div></div>
    <table class="tbl">
      <tbody>
        <tr><td class="nm">Email</td><td><a href="mailto:<?= h($CONFIG['email']) ?>"><?= h($CONFIG['email']) ?></a></td></tr>
        <?php if($CONFIG['phone']): ?><tr><td class="nm">Phone</td><td><?= h($CONFIG['phone']) ?></td></tr><?php endif; ?>
        <tr><td class="nm">Business</td><td><?= h($CONFIG['legal_name']) ?>, <?= h($CONFIG['address']) ?></td></tr>
        <tr><td class="nm">Existing order</td><td>Quote your order reference (format FK-26-XXXXX) in the subject line.</td></tr>
      </tbody>
    </table>
    <p style="margin-top:22px;font-size:14.5px;color:var(--ink2)">For standing orders, full-case volumes or allocation on an upcoming release, email us with the sets and quantities you want and we will come back with pricing.</p>
  </div></section>

<?php endif; ?>
</main>

<footer class="site"><div class="wrap">
  <div class="fg">
    <div>
      <div class="brand"><span class="mk" style="color:#fff"><?= h($CONFIG['brand']) ?></span>
        <span class="kj" style="color:var(--gold);border-color:var(--gold)"><?= h($CONFIG['kanji']) ?></span></div>
      <p class="bl">Wholesale Japanese Pokémon TCG, shipped worldwide from Japan to retailers, resellers and card shops. Pricing published on every product — order direct, no account needed.</p>
    </div>
    <div><h4>Shop</h4><ul>
      <?php foreach($CATEGORIES as $k=>$c): ?>
        <li><a href="<?= url('catalog',['cat'=>$k]) ?>"><?= h($c['label']) ?></a></li>
      <?php endforeach; ?>
      <li><a href="<?= url('catalog') ?>">All products</a></li>
    </ul></div>
    <div><h4>Ordering</h4><ul>
      <li><a href="<?= url('how') ?>">How it works</a></li>
      <li><a href="<?= url('payment') ?>">Payment methods</a></li>
      <li><a href="<?= url('shipping') ?>">Shipping &amp; customs</a></li>
      <li><a href="<?= url('cart') ?>">Your order</a></li>
    </ul></div>
    <div><h4>Support</h4><ul>
      <li><a href="<?= url('faq') ?>">FAQ</a></li>
      <li><a href="<?= url('contact') ?>">Contact</a></li>
      <li><a href="mailto:<?= h($CONFIG['email']) ?>"><?= h($CONFIG['email']) ?></a></li>
    </ul></div>
  </div>
  <div class="legal">
    <div>Shipped from Japan — import duties and taxes are the buyer's responsibility.</div>
    <div><?= h($CONFIG['legal_name']) ?> is an independent reseller of genuine product. We are not affiliated with, endorsed by or licensed by The Pokémon Company, Nintendo, Creatures Inc. or GAME FREAK Inc. All product names and trademarks are the property of their respective owners.</div>
    <div>© <?= date('Y') ?> <?= h($CONFIG['legal_name']) ?>.</div>
  </div>
</div></footer>

<script>
function bump(btn, delta, min){
  const input = btn.parentElement.querySelector('input');
  const v = (parseInt(input.value,10) || min) + delta;
  input.value = Math.max(min, v);
}
function galPick(btn, src){
  document.getElementById('galMain').src = src;
  btn.parentElement.querySelectorAll('button').forEach(b=>b.setAttribute('aria-current','false'));
  btn.setAttribute('aria-current','true');
}
function filterPay(country){
  document.querySelectorAll('#payList label').forEach(l=>{
    const allow = l.dataset.countries;
    const ok = allow === '*' || allow.split(',').includes(country);
    l.hidden = !ok;
    if(!ok){ const r = l.querySelector('input'); if(r) r.checked = false; }
  });
}
const c = document.getElementById('country');
if(c && c.value) filterPay(c.value);
</script>
</body>
</html>

<?php
/* ---------------- VIEW PARTIALS ---------------- */
function include_card($p){
  global $CATEGORIES, $CONFIG;
  $ph   = photos($p['id']);
  $base = unit_price($p, $p['moq']);
  $best = end($p['ladder']);
  $flag = $p['status']==='preorder' ? ['pre','PREORDER']
        : ($p['status']==='new' ? ['new','NEW']
        : (($p['stock'] && $p['stock']<15) ? ['low','LOW STOCK'] : ['','IN STOCK']));
  ?>
  <article class="card">
    <a class="art" href="<?= url('product',['id'=>$p['id']]) ?>" style="display:block">
      <span class="flag <?= $flag[0] ?>"><?= $flag[1] ?></span>
      <?php if($ph): ?>
        <img src="<?= h($ph[0]) ?>" alt="<?= h($p['name']) ?> — wholesale Japanese Pokémon TCG" loading="lazy">
      <?php else: ?>
        <div class="ph"><span>札蔵</span><span><?= h($p['set']) ?></span></div>
      <?php endif; ?>
    </a>
    <div class="in">
      <h3><a href="<?= url('product',['id'=>$p['id']]) ?>"><?= h($p['name']) ?></a></h3>
      <div class="meta"><?= h($p['sku']) ?> · MOQ <?= $p['moq'] ?> · sold in <?= $p['step'] ?>s<?= !empty($p['release']) ? ' · '.h($p['release']) : '' ?></div>
      <div class="px"><span class="u"><?= money($base) ?></span><span class="w"><?= money($p['ladder'][0][1]) ?></span></div>
      <div class="drop">down to <b><?= money($best[1]) ?></b> at <?= $best[0] ?>+</div>
    </div>
    <form method="post" action="index.php">
      <input type="hidden" name="action" value="add">
      <input type="hidden" name="id" value="<?= h($p['id']) ?>">
      <div class="step">
        <button type="button" onclick="bump(this,-<?= $p['step'] ?>,<?= $p['moq'] ?>)">−</button>
        <input type="number" name="qty" value="<?= $p['moq'] ?>" min="<?= $p['moq'] ?>" step="<?= $p['step'] ?>" aria-label="Quantity">
        <button type="button" onclick="bump(this,<?= $p['step'] ?>,<?= $p['moq'] ?>)">+</button>
      </div>
      <button class="btn" type="submit">Add</button>
    </form>
  </article>
  <?php
}

function steps_block($CONFIG){ ?>
  <div class="steps">
    <div><div class="n">01</div><h3>Price it openly</h3>
      <p>Every listing shows its full break ladder to everyone. No application, no approval wait, no quote round-trip for standard volumes.</p></div>
    <div><div class="n">02</div><h3>Place the order</h3>
      <p>Add to cart, enter your shipping address and pick a payment method. Stock is reserved in your name for <?= (int)$CONFIG['hold_hours'] ?> hours.</p></div>
    <div><div class="n">03</div><h3>We send payment details</h3>
      <p>Within <?= (int)$CONFIG['reply_hours'] ?> hours you get the details for your chosen method by email or text, with your final invoice including shipping.</p></div>
    <div><div class="n">04</div><h3>Ship tracked from Japan</h3>
      <p>Payment clears, stock is allocated, and we dispatch within <?= (int)$CONFIG['hold_hours'] ?> hours by EMS, DHL or FedEx with tracking.</p></div>
  </div>
<?php }
