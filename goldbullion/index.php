<?php
/* =============================================================
   Gold Bullion Shop — the storefront.

   You never need to edit this file. Products, prices, pages,
   guides, shipping, payments and settings are all edited in
   admin.php and saved to data/.
   ============================================================= */

define('GB_ROOT', __DIR__);
@ini_set('display_errors', '0');
error_reporting(E_ALL & ~E_NOTICE & ~E_WARNING & ~E_DEPRECATED);
require GB_ROOT.'/inc/store.php';

$STORE = store_load();
$CFG   = $STORE['settings'];
$BASE  = rtrim(str_replace('\\', '/', dirname($_SERVER['SCRIPT_NAME'] ?? '/index.php')), '/').'/';
$DOMAIN = rtrim($CFG['domain'] ?: ('https://'.($_SERVER['HTTP_HOST'] ?? 'localhost')), '/');

header('X-Content-Type-Options: nosniff');
header('Referrer-Policy: strict-origin-when-cross-origin');
header('X-Frame-Options: SAMEORIGIN');

/* the cart needs a session; plain page views don't, so they stay fast and cacheable */
if(!empty($_COOKIE[session_name()]) || $_SERVER['REQUEST_METHOD'] === 'POST') start_session();

/* ---------------- lookups ---------------- */
function visible_products(){
  global $STORE;
  $out = array_values(array_filter($STORE['products'], fn($p)=>empty($p['hidden'])));
  usort($out, fn($a, $b)=>[(int)!empty($b['featured']), -(float)($a['sort'] ?? 0)] <=> [(int)!empty($a['featured']), -(float)($b['sort'] ?? 0)]);
  return $out;
}
function product_by_slug($slug){ global $STORE; foreach($STORE['products'] as $p) if(($p['slug'] ?? '') === $slug && empty($p['hidden'])) return $p; return null; }
function product_by_id($id){ global $STORE; foreach($STORE['products'] as $p) if(($p['id'] ?? '') === $id) return $p; return null; }
function category($slug){ global $STORE; foreach($STORE['categories'] as $c) if($c['slug'] === $slug) return $c; return null; }
function child_cats($slug){ global $STORE; return array_values(array_filter($STORE['categories'], fn($c)=>($c['parent'] ?? '') === $slug)); }
function cat_products($slug){
  $slugs = array_merge([$slug], array_column(child_cats($slug), 'slug'));
  return array_values(array_filter(visible_products(), function($p) use($slugs){
    foreach((array)($p['categories'] ?? []) as $c) if(in_array($c, $slugs, true)) return true;
    return false;
  }));
}
function page_by_slug($slug){ global $STORE; foreach($STORE['pages'] as $p) if($p['slug'] === $slug) return $p; return null; }
function guide_by_slug($slug){ global $STORE; foreach($STORE['guides'] as $g) if($g['slug'] === $slug && empty($g['hidden'])) return $g; return null; }
function tool_by_slug($slug){ global $STORE; foreach($STORE['tools'] as $t) if($t['slug'] === $slug) return $t; return null; }
function guides_all(){ global $STORE; return array_values(array_filter($STORE['guides'], fn($g)=>empty($g['hidden']))); }
function in_stock($p){ return ($p['stock'] ?? 'in') !== 'out' && product_price($p) > 0; }

/* ---------------- money, units, tokens ---------------- */
function usd($v, $dec=2){ return '$'.number_format((float)$v, $v >= 100000 ? 0 : $dec); }

const UNIT_G = ['g'=>1, 'oz'=>G_PER_OZT, 'kg'=>1000, 'lb'=>G_PER_LB, 'dwt'=>G_PER_DWT, 'tola'=>G_PER_TOLA, 't'=>1000000];

/* live value of an amount of gold: value(10, 'g', 14) */
function gold_value($amount, $unit, $k=24, $metal='gold'){
  $spot = spot()[$metal] ?? 0;
  if($spot <= 0) return null;
  return $spot / G_PER_OZT * (float)$amount * (UNIT_G[$unit] ?? 1) * min(24, $k) / 24;
}
function gold_value_txt($amount, $unit, $k=24){ $v = gold_value($amount, $unit, $k); return $v === null ? '—' : usd($v); }

function fill($text, $extra=[]){
  global $CFG, $STORE;
  $text = (string)$text;
  if(strpos($text, '{') === false) return $text;
  $spot = spot(); $ship = $STORE['shipping']['methods'];
  $map = [
    '{brand}'=>$CFG['brand'], '{email}'=>$CFG['email'], '{phone}'=>$CFG['phone'] ?: $CFG['email'], '{hours}'=>$CFG['hours'],
    '{reply_time}'=>$CFG['reply_time'], '{pay_window}'=>$CFG['pay_window'], '{dispatch_time}'=>$CFG['dispatch_time'],
    '{legal_name}'=>$CFG['legal_name'] ?: $CFG['brand'], '{address}'=>$CFG['address'], '{domain}'=>preg_replace('#^https?://#', '', $GLOBALS['DOMAIN']),
    '{standard_price}'=>usd($ship['standard']['price'] ?? 0), '{express_price}'=>usd($ship['express']['price'] ?? 0),
    '{standard_days}'=>$ship['standard']['days'] ?? '', '{express_days}'=>$ship['express']['days'] ?? '',
    '{gold_oz}'=>$spot['gold'] > 0 ? usd($spot['gold']) : '—', '{gold_g}'=>$spot['gold'] > 0 ? usd($spot['gold'] / G_PER_OZT) : '—',
    '{gold_kg}'=>gold_value_txt(1, 'kg'), '{silver_oz}'=>$spot['silver'] > 0 ? usd($spot['silver']) : '—',
    '{date}'=>date('F j, Y'), '{year}'=>date('Y'), '{updated}'=>date('F j, Y', (int)($CFG['policies_updated'] ?? 0) ?: strtotime('2026-10-30')),
  ] + $extra;
  $text = strtr($text, $map);
  return preg_replace_callback('/\{value:([\d.]+)(g|oz|kg|lb|dwt|tola|t)(?::(\d+)k)?\}/', fn($m)=>gold_value_txt($m[1], $m[2], isset($m[3]) && $m[3] !== '' ? (int)$m[3] : 24), $text);
}

/* rich content: tokens, blocks and root-relative links that also work in a subfolder */
function content($html, $extra=[]){
  global $BASE;
  $html = fill($html, $extra);
  $blocks = ['calculator'=>fn()=>calculator_html(), 'payment_list'=>fn()=>payment_list_html(), 'shipping_table'=>fn()=>shipping_table_html(), 'business_details'=>fn()=>business_details_html()];
  foreach($blocks as $k=>$fn){
    if(strpos($html, '{'.$k.'}') === false) continue;
    $out = $fn();
    $html = str_replace(['<p>{'.$k.'}</p>', '{'.$k.'}'], [$out, $out], $html);
  }
  if($BASE !== '/') $html = preg_replace('#(href|src)="/(?!/)#', '$1="'.$BASE, $html);
  return $html;
}

/* adds ids to h2 headings and returns [html, table of contents] */
function with_toc($html){
  $toc = []; $used = [];
  $html = preg_replace_callback('#<h2([^>]*)>(.*?)</h2>#s', function($m) use(&$toc, &$used){
    if(preg_match('/id="([^"]+)"/', $m[1], $id)){ $toc[] = [$id[1], strip_tags($m[2])]; return $m[0]; }
    $slug = slugify(strip_tags($m[2])) ?: 'section'; $s = $slug; $n = 2;
    while(isset($used[$s])) $s = $slug.'-'.$n++;
    $used[$s] = 1; $toc[] = [$s, strip_tags($m[2])];
    return '<h2 id="'.$s.'"'.$m[1].'>'.$m[2].'</h2>';
  }, $html);
  return [$html, $toc];
}

function plain($html, $len=160){
  $t = trim(preg_replace('/\s+/', ' ', html_entity_decode(strip_tags((string)$html), ENT_QUOTES, 'UTF-8')));
  return mb_strlen($t) > $len ? rtrim(mb_substr($t, 0, $len - 1)).'…' : $t;
}

function link_to($path){ global $BASE; return $BASE.ltrim($path, '/'); }
function abs_url($path){ global $DOMAIN; return $DOMAIN.'/'.ltrim($path, '/'); }
function img_src($src){ global $BASE; return preg_match('#^https?://#', $src) ? $src : $BASE.ltrim($src, '/'); }
function product_url($p){ return link_to('products/'.$p['slug']); }
function redirect($path, $code=302){ header('Location: '.link_to($path), true, $code); exit; }

function csrf(){ start_session(); if(empty($_SESSION['csrf'])) $_SESSION['csrf'] = bin2hex(random_bytes(16)); return $_SESSION['csrf']; }
function csrf_ok(){ return isset($_SESSION['csrf'], $_POST['csrf']) && hash_equals($_SESSION['csrf'], (string)$_POST['csrf']); }
function flash($msg, $type='ok'){ start_session(); $_SESSION['flash'][] = [$type, $msg]; }

/* ---------------- cart ---------------- */
function cart(){ return $_SESSION['cart'] ?? []; }
function cart_count(){ return array_sum(cart()); }
function cart_lines(){
  $out = [];
  foreach(cart() as $id=>$qty){
    $p = product_by_id($id);
    if(!$p || !empty($p['hidden']) || !in_stock($p)) continue;
    $unit = product_price($p);
    $out[] = ['p'=>$p, 'qty'=>$qty, 'unit'=>$unit, 'total'=>round($unit * $qty, 2)];
  }
  return $out;
}
function cart_total(){ return round(array_sum(array_column(cart_lines(), 'total')), 2); }

/* ---------------- request ---------------- */
$path = rawurldecode((string)parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH));
if(strpos($path, $BASE) === 0) $path = substr($path, strlen($BASE));
if(isset($_GET['p']) && is_string($_GET['p'])) $path = $_GET['p'];
if($path === 'index.php') $path = '';
if($path !== '' && substr($path, -1) === '/' && $_SERVER['REQUEST_METHOD'] === 'GET') redirect(rtrim($path, '/'), 301);
$path = trim($path, '/');

const US_STATES = ['AL'=>'Alabama','AK'=>'Alaska','AZ'=>'Arizona','AR'=>'Arkansas','CA'=>'California','CO'=>'Colorado','CT'=>'Connecticut','DE'=>'Delaware','DC'=>'District of Columbia','FL'=>'Florida','GA'=>'Georgia','HI'=>'Hawaii','ID'=>'Idaho','IL'=>'Illinois','IN'=>'Indiana','IA'=>'Iowa','KS'=>'Kansas','KY'=>'Kentucky','LA'=>'Louisiana','ME'=>'Maine','MD'=>'Maryland','MA'=>'Massachusetts','MI'=>'Michigan','MN'=>'Minnesota','MS'=>'Mississippi','MO'=>'Missouri','MT'=>'Montana','NE'=>'Nebraska','NV'=>'Nevada','NH'=>'New Hampshire','NJ'=>'New Jersey','NM'=>'New Mexico','NY'=>'New York','NC'=>'North Carolina','ND'=>'North Dakota','OH'=>'Ohio','OK'=>'Oklahoma','OR'=>'Oregon','PA'=>'Pennsylvania','RI'=>'Rhode Island','SC'=>'South Carolina','SD'=>'South Dakota','TN'=>'Tennessee','TX'=>'Texas','UT'=>'Utah','VT'=>'Vermont','VA'=>'Virginia','WA'=>'Washington','WV'=>'West Virginia','WI'=>'Wisconsin','WY'=>'Wyoming','PR'=>'Puerto Rico'];

/* ---------------- actions ---------------- */
if($_SERVER['REQUEST_METHOD'] === 'POST'){
  $act = $_POST['action'] ?? '';
  if($act === 'add'){
    $p = product_by_id((string)($_POST['id'] ?? ''));
    $qty = max(1, min(100, (int)($_POST['qty'] ?? 1)));
    if($p && in_stock($p) && empty($p['hidden'])){
      $_SESSION['cart'][$p['id']] = min(100, ($_SESSION['cart'][$p['id']] ?? 0) + $qty);
      flash('Added to your cart: '.$p['name']);
    }
    redirect('cart', 303);
  }
  if($act === 'cart'){
    foreach((array)($_POST['qty'] ?? []) as $id=>$q){
      $q = (int)$q;
      if($q <= 0) unset($_SESSION['cart'][$id]); elseif(isset($_SESSION['cart'][$id])) $_SESSION['cart'][$id] = min(100, $q);
    }
    if(isset($_POST['remove'])) unset($_SESSION['cart'][(string)$_POST['remove']]);
    redirect(isset($_POST['checkout']) ? 'checkout' : 'cart', 303);
  }
  if($act === 'order'){ place_order(); }
  if($act === 'contact'){ send_contact(); }
}

/* ---------------- checkout ---------------- */

function countries(){ global $CFG; return array_values(array_filter(array_map('trim', explode(',', $CFG['countries'] ?: 'United States')))); }

function rate_limited($key, $max, $window){
  $f = 'rate'; $now = time(); $ip = $_SERVER['REMOTE_ADDR'] ?? '';
  $r = data_read($f) ?: [];
  foreach($r as $k=>$ts){ $r[$k] = array_values(array_filter($ts, fn($t)=>$t > $now - 86400)); if(!$r[$k]) unset($r[$k]); }
  $id = $key.'|'.$ip;
  $hits = array_filter($r[$id] ?? [], fn($t)=>$t > $now - $window);
  if(count($hits) >= $max){ data_write($f, $r); return true; }
  $r[$id][] = $now; data_write($f, $r);
  return false;
}

function place_order(){
  global $STORE, $CFG;
  $in = fn($k, $max=200)=>mb_substr(trim(str_replace(["\r", "\n"], ' ', (string)($_POST[$k] ?? ''))), 0, $max);
  $f = [];
  foreach(['first_name','last_name','email','phone','address1','address2','city','state','zip','country','ship','pay'] as $k) $f[$k] = $in($k);
  $f['notes'] = mb_substr(trim((string)($_POST['notes'] ?? '')), 0, 1000);
  $_SESSION['checkout'] = $f;
  $err = [];
  if(!csrf_ok()) $err[] = 'Your session expired. Please submit again.';
  if(($_POST['website'] ?? '') !== '') $err[] = 'Please try again.';   /* bots fill the hidden field */
  $lines = cart_lines();
  if(!$lines) $err[] = 'Your cart is empty.';
  if($f['first_name'] === '' || $f['last_name'] === '') $err[] = 'Please enter your first and last name.';
  if(!filter_var($f['email'], FILTER_VALIDATE_EMAIL)) $err[] = 'Please enter a valid email address.';
  $digits = preg_replace('/\D/', '', $f['phone']);
  if(strlen($digits) < 10 || strlen($digits) > 15) $err[] = 'Please enter a valid phone number, including area code. We text your payment details to it.';
  if($f['address1'] === '' || $f['city'] === '' || $f['state'] === '' || $f['zip'] === '') $err[] = 'Please complete your delivery address.';
  if(preg_match('/\b(p\.?\s*o\.?\s*box|post office box)\b/i', $f['address1'].' '.$f['address2'])) $err[] = 'We can’t ship insured bullion to a PO Box. Please use a street address.';
  $countries = countries();
  if(!in_array($f['country'], $countries, true)) $f['country'] = $countries[0];
  if($f['country'] === 'United States' && !preg_match('/^\d{5}(-?\d{4})?$/', $f['zip'])) $err[] = 'Please enter a valid ZIP code.';
  $methods = ship_methods($STORE);
  if(!isset($methods[$f['ship']])) $err[] = 'Please choose a shipping option.';
  $pays = array_filter($STORE['payments'], fn($m)=>!empty($m['enabled']));
  if(!isset($pays[$f['pay']])) $err[] = 'Please choose a payment method.';
  if(empty($_POST['agree'])) $err[] = 'Please accept the terms and conditions.';
  if(!$err && rate_limited('order', 6, 3600)) $err[] = 'Too many orders from your connection in the last hour. Please contact us.';
  if($err){ foreach($err as $e) flash($e, 'err'); redirect('checkout', 303); }

  $goods = round(array_sum(array_column($lines, 'total')), 2);
  $ship_cost = shipping_cost($STORE, $f['ship'], $goods);
  $ref = new_order_ref();
  $order = $f + [
    'ref'=>$ref, 'status'=>'new', 'time'=>date('Y-m-d H:i'), 'time_iso'=>date('c'),
    'name'=>$f['first_name'].' '.$f['last_name'],
    'state_name'=>US_STATES[$f['state']] ?? $f['state'],
    'lines'=>array_map(fn($l)=>['id'=>$l['p']['id'], 'name'=>$l['p']['name'], 'sku'=>$l['p']['sku'] ?? '', 'qty'=>$l['qty'], 'unit'=>$l['unit'], 'total'=>$l['total']], $lines),
    'goods'=>$goods, 'shipping'=>$ship_cost, 'ship_label'=>$methods[$f['ship']]['label'], 'total'=>round($goods + $ship_cost, 2),
    'payment'=>$f['pay'], 'payment_label'=>$pays[$f['pay']]['label'], 'spot'=>spot()['gold'],
    'ip'=>$_SERVER['REMOTE_ADDR'] ?? '', 'agent'=>mb_substr($_SERVER['HTTP_USER_AGENT'] ?? '', 0, 200),
    'history'=>[[date('Y-m-d H:i'), 'Order placed']],
  ];
  unset($order['pay'], $order['ship']);
  $order['ship_method'] = $f['ship'];
  if(!order_save($order)){ flash('Sorry, we couldn’t save your order. Please try again or email '.$CFG['email'].'.', 'err'); redirect('checkout', 303); }
  $sent = send_order_mail($order);
  $order['mail'] = $sent; order_save($order);
  unset($_SESSION['cart'], $_SESSION['checkout']);
  header('Location: '.link_to('order-received').'?'.http_build_query(['ref'=>$ref, 'k'=>order_key($ref)]), true, 303);
  exit;
}

function order_text($o){
  $t = '';
  foreach($o['lines'] as $l) $t .= sprintf("  %d x %s%s — %s\n", $l['qty'], $l['name'], $l['sku'] ? " ({$l['sku']})" : '', money($l['total']));
  $t .= "\n  Subtotal: ".money($o['goods'])."\n  Shipping: ".($o['shipping'] > 0 ? money($o['shipping']) : 'Free')." ({$o['ship_label']})\n  TOTAL:    ".money($o['total'])."\n";
  return $t;
}

function send_order_mail($o){
  global $CFG;
  $addr = "{$o['address1']}".($o['address2'] ? ", {$o['address2']}" : '')."\n  {$o['city']}, {$o['state']} {$o['zip']}\n  {$o['country']}";
  $b  = "NEW ORDER {$o['ref']} — ".money($o['total'])."\n".str_repeat('=', 48)."\n\n";
  $b .= "PAYMENT METHOD: {$o['payment_label']}\n-> Send this customer the payment details (Admin → Orders → Send payment details).\n\n";
  $b .= "CUSTOMER\n  {$o['name']}\n  Email: {$o['email']}\n  Phone: {$o['phone']}\n\nSHIP TO\n  $addr\n\nITEMS\n".order_text($o)."\n";
  if($o['notes']) $b .= "NOTES\n  {$o['notes']}\n\n";
  $b .= "Gold spot at order: ".money($o['spot'])."/oz\nPlaced: {$o['time']} · IP {$o['ip']}\n";
  $shop = shop_mail($CFG['order_email'] ?: $CFG['email'], "New order {$o['ref']} — {$o['payment_label']} — ".money($o['total']), $b, $o['email']);

  $c  = "Hi {$o['first_name']},\n\nThank you — we’ve received your order {$o['ref']}.\n\n".order_text($o)."\n";
  $c .= "Payment method: {$o['payment_label']}\n\nWHAT HAPPENS NEXT\n";
  $c .= "1. We’ll text or email you the payment details and exact amount {$CFG['reply_time']}.\n";
  $c .= "2. Your price is locked for {$CFG['pay_window']} hours from now. Please include {$o['ref']} in your payment note.\n";
  $c .= "3. Once your payment clears we ship fully insured in plain packaging and email you the tracking number.\n\n";
  $c .= "IMPORTANT: we only ever send payment details from {$CFG['email']}".($CFG['phone'] ? " or {$CFG['phone']}" : '').". If anyone else sends you payment instructions for this order, don’t pay and contact us.\n\n";
  $c .= "Shipping to:\n  {$o['name']}\n  $addr\n\nQuestions? Just reply to this email.\n\n{$CFG['brand']}\n".$GLOBALS['DOMAIN']."\n";
  $cust = shop_mail($o['email'], "Order {$o['ref']} received — {$CFG['brand']}", $c, $CFG['email']);
  return ['shop'=>$shop, 'customer'=>$cust, 'error'=>$GLOBALS['GB_MAIL_ERROR'] ?? ''];
}

function send_contact(){
  global $CFG;
  $in = fn($k, $max=200)=>mb_substr(trim((string)($_POST[$k] ?? '')), 0, $max);
  $m = ['name'=>$in('name'), 'email'=>$in('email'), 'phone'=>$in('phone'), 'order'=>$in('order', 30), 'message'=>$in('message', 5000)];
  $err = [];
  if(!csrf_ok()) $err[] = 'Your session expired. Please send again.';
  if(($_POST['website'] ?? '') !== '') $err[] = 'Please try again.';
  if($m['name'] === '') $err[] = 'Please enter your name.';
  if(!filter_var($m['email'], FILTER_VALIDATE_EMAIL)) $err[] = 'Please enter a valid email address.';
  if(mb_strlen($m['message']) < 5) $err[] = 'Please write a message.';
  if(!$err && rate_limited('contact', 5, 3600)) $err[] = 'Too many messages. Please email us directly.';
  $_SESSION['contact'] = $m;
  if($err){ foreach($err as $e) flash($e, 'err'); redirect('contact', 303); }
  $m['time'] = date('Y-m-d H:i'); $m['ip'] = $_SERVER['REMOTE_ADDR'] ?? '';
  $all = data_read('messages') ?: [];
  array_unshift($all, $m);
  data_write('messages', array_slice($all, 0, 500));
  $body = "Contact form message\n\nName: {$m['name']}\nEmail: {$m['email']}\nPhone: {$m['phone']}\nOrder: {$m['order']}\n\n{$m['message']}\n";
  shop_mail($CFG['order_email'] ?: $CFG['email'], 'Website message from '.$m['name'], $body, $m['email']);
  unset($_SESSION['contact']);
  flash('Thank you — your message has been sent. We’ll reply '.$CFG['reply_time'].'.');
  redirect('contact', 303);
}

/* ---------------- routing ---------------- */
$META = ['title'=>'', 'desc'=>'', 'canonical'=>'', 'robots'=>'index,follow', 'schema'=>[], 'crumbs'=>[], 'type'=>'website', 'image'=>'', 'lang'=>'en', 'alt'=>[]];
$seg = $path === '' ? [] : explode('/', $path);

if($path === 'robots.txt'){
  header('Content-Type: text/plain; charset=utf-8');
  echo "User-agent: *\nDisallow: /admin.php\nDisallow: /cart\nDisallow: /checkout\nDisallow: /order-received\nDisallow: /search\nDisallow: /*?p=\n\nSitemap: ".abs_url('sitemap.xml')."\n";
  exit;
}
if($path === 'sitemap.xml'){ sitemap(); exit; }
if($path === 'admin' || $path === 'login') { header('Location: '.$BASE.'admin.php'); exit; }

ob_start();
if($path === '')                          $view = 'home';
elseif($path === 'shop')                  $view = 'shop';
elseif($path === 'guides')                $view = 'guides';
elseif($path === 'cart')                  $view = 'cart';
elseif($path === 'checkout')              $view = 'checkout';
elseif($path === 'order-received')        $view = 'received';
elseif($path === 'search')                $view = 'search';
elseif(($seg[0] ?? '') === 'products' && count($seg) === 2 && product_by_slug($seg[1])) $view = 'product';
elseif(($seg[0] ?? '') === 'guides' && count($seg) === 2 && guide_by_slug($seg[1]))      $view = 'guide';
elseif(tool_by_slug($path))               $view = 'tool';
elseif(count($seg) === 1 && category($path)) $view = 'category';
elseif(count($seg) === 1 && page_by_slug($path)) $view = 'page';
else                                      $view = 'notfound';

$view_fn = 'view_'.$view;
$view_fn($seg, $path);
$BODY = ob_get_clean();
render($BODY);

/* =============================================================
   VIEWS
   ============================================================= */
function crumbs($items){ global $META; $META['crumbs'] = $items; }

function view_home(){
  global $CFG, $STORE, $META;
  $home = $STORE['home'];
  $META['title'] = fill($CFG['home_seo_title']);
  $META['desc'] = fill($CFG['home_seo_desc']);
  $META['canonical'] = abs_url('');
  $META['full_title'] = true;
  $products = visible_products();
  $featured = array_slice(array_values(array_filter($products, fn($p)=>!empty($p['featured']))) ?: $products, 0, 8);
  $spot = spot(); ?>
<section class="hero">
  <div class="wrap hero-in">
    <div class="hero-copy">
      <p class="kicker"><?= h($CFG['hero_kicker']) ?></p>
      <h1><?= h(fill($CFG['hero_title'])) ?></h1>
      <p class="hero-text"><?= h(fill($CFG['hero_text'])) ?></p>
      <div class="hero-cta"><a class="btn btn-gold" href="<?= link_to('gold-bars') ?>">Shop gold bars</a><a class="btn btn-ghost" href="<?= link_to('gold-coins') ?>">Shop gold coins</a></div>
      <ul class="hero-points"><li>Price locked when you order</li><li>Fully insured delivery</li><li>Discreet packaging</li></ul>
    </div>
    <div class="hero-art"><?= spot_card() ?></div>
  </div>
</section>
<section class="trust"><div class="wrap trust-in">
  <div><b>Genuine bullion</b><span>From recognised refiners and mints</span></div>
  <div><b>Live spot pricing</b><span>Transparent premiums, locked at order</span></div>
  <div><b>Insured shipping</b><span>Standard <?= usd($STORE['shipping']['methods']['standard']['price'] ?? 0, 0) ?> · Express <?= usd($STORE['shipping']['methods']['express']['price'] ?? 0, 0) ?></span></div>
  <div><b>Easy payment</b><span>Cash App, Apple Pay, Zelle, Chime, crypto</span></div>
</div></section>
<section class="wrap sec">
  <div class="sec-head"><h2>Shop gold bullion by size</h2><a href="<?= link_to('shop') ?>">View all gold bullion →</a></div>
  <?= size_tiles() ?>
</section>
<?php if($featured): ?>
<section class="wrap sec">
  <div class="sec-head"><h2>Popular gold bars &amp; coins</h2><a href="<?= link_to('shop') ?>">Shop all →</a></div>
  <?= product_grid($featured) ?>
</section>
<?php endif; ?>
<section class="wrap sec">
  <div class="sec-head"><h2>Gold price today by karat</h2><a href="<?= link_to('gold-price') ?>">Live gold &amp; silver prices →</a></div>
  <?= karat_table([24, 22, 21, 18, 14, 10], true) ?>
</section>
<section class="wrap sec content-grid">
  <article class="prose"><?= content($home['body']) ?></article>
  <aside class="side"><?= side_box() ?></aside>
</section>
<section class="wrap sec">
  <div class="sec-head"><h2>Gold bullion guides</h2><a href="<?= link_to('guides') ?>">All guides →</a></div>
  <?= guide_cards(array_slice(guides_all(), 0, 6)) ?>
</section>
<section class="wrap sec narrow"><?= faq_block($home['faqs'], 'Gold bullion FAQ') ?></section>
<?php }

function view_shop(){
  global $CFG, $META;
  $META['title'] = fill($CFG['shop_seo_title']); $META['desc'] = fill($CFG['shop_seo_desc']); $META['canonical'] = abs_url('shop');
  crumbs([['Shop', 'shop']]);
  $products = visible_products(); ?>
<div class="wrap page-head"><h1>Shop Gold Bullion</h1><p class="lead"><?= h(fill($CFG['shop_intro'])) ?></p><?= cat_chips('') ?></div>
<section class="wrap sec"><?= $products ? product_grid($products) : empty_shop() ?></section>
<section class="wrap sec"><h2 class="h2">Shop by size</h2><?= size_tiles() ?></section>
<?php $META['schema'][] = item_list_schema($products);
}

function empty_shop(){ global $CFG; return '<div class="empty"><p>New stock is being added. For current availability and prices, email <a href="mailto:'.h($CFG['email']).'">'.h($CFG['email']).'</a>.</p></div>'; }

function view_category($seg, $path){
  global $META;
  $c = category($path);
  $products = cat_products($c['slug']);
  $META['title'] = fill($c['seo_title'] ?: $c['name']); $META['desc'] = fill($c['seo_desc']); $META['canonical'] = abs_url($c['slug']);
  if(!$products) $META['robots'] = 'noindex,follow';
  $trail = [['Shop', 'shop']];
  if(!empty($c['parent']) && ($par = category($c['parent']))) $trail[] = [$par['name'], $par['slug']];
  $trail[] = [$c['name'], $c['slug']];
  crumbs($trail); ?>
<div class="wrap page-head"><h1><?= h(fill($c['h1'] ?: $c['name'])) ?></h1><p class="lead"><?= fill(h($c['intro'])) ?></p><?= cat_chips($c['slug']) ?></div>
<section class="wrap sec"><?= $products ? product_grid($products) : empty_shop() ?></section>
<section class="wrap sec content-grid">
  <article class="prose"><?= content($c['body']) ?><?= faq_block($c['faqs'] ?? []) ?></article>
  <aside class="side"><?= side_box() ?><?= related_guides_box(cat_guides($c['slug'])) ?></aside>
</section>
<?php $META['schema'][] = item_list_schema($products);
}

function view_product($seg){
  global $META, $CFG, $STORE;
  $p = product_by_slug($seg[1]);
  $price = product_price($p);
  $META['title'] = fill($p['seo_title'] ?: (mb_strlen($p['name']) <= 38 ? $p['name'].' for Sale' : $p['name']));
  $META['desc'] = fill($p['seo_desc'] ?: plain(($p['short'] ?? '') ?: ($p['description'] ?? ''), 155));
  if(mb_strlen($META['desc']) < 60) $META['desc'] = 'Buy '.$p['name'].' — '.trim(product_spec_line($p).'. Live spot pricing, insured US shipping and discreet packaging.', ' .').'.';
  $META['canonical'] = abs_url('products/'.$p['slug']); $META['type'] = 'product';
  $imgs = array_values(array_filter((array)($p['images'] ?? [])));
  if($imgs) $META['image'] = preg_match('#^https?://#', $imgs[0]) ? $imgs[0] : abs_url($imgs[0]);
  $cat = category(($p['categories'] ?? [''])[0] ?? '');
  $trail = [['Shop', 'shop']];
  if($cat){ if(!empty($cat['parent']) && ($par = category($cat['parent']))) $trail[] = [$par['name'], $par['slug']]; $trail[] = [$cat['name'], $cat['slug']]; }
  $trail[] = [$p['name'], 'products/'.$p['slug']];
  crumbs($trail);
  $oz = oz_from($p['weight'] ?? 0, $p['unit'] ?? 'oz');
  $melt = gold_value($oz, 'oz', 24, $p['metal'] ?? 'gold');
  $stock = $p['stock'] ?? 'in'; ?>
<div class="wrap product">
  <div class="gallery">
    <div class="gal-main"><?= product_img($p, 0, true) ?></div>
    <?php if(count($imgs) > 1): ?><div class="gal-thumbs"><?php foreach($imgs as $i=>$src): ?><button type="button" class="thumb" data-src="<?= h(img_src($src)) ?>" aria-label="Show photo <?= $i + 1 ?>"><img src="<?= h(img_src($src)) ?>" alt="" width="72" height="72" loading="lazy"></button><?php endforeach; ?></div><?php endif; ?>
  </div>
  <div class="buy">
    <?php if(!empty($p['brand'])): ?><p class="kicker"><?= h($p['brand']) ?></p><?php endif; ?>
    <h1><?= h($p['name']) ?></h1>
    <p class="spec"><?= h(product_spec_line($p)) ?></p>
    <div class="price-row"><span class="price"><?= $price > 0 ? usd($price) : 'Call for price' ?></span>
      <?php if(!empty($p['compare_at']) && $p['compare_at'] > $price): ?><s class="was"><?= usd($p['compare_at']) ?></s><?php endif; ?>
      <span class="stock stock-<?= h($stock) ?>"><?= h(STOCK_STATUSES[$stock] ?? '') ?></span></div>
    <?php if(($p['pricing'] ?? 'fixed') === 'spot'): ?><p class="small muted">Price moves with the live gold price and is locked when you place your order.</p><?php endif; ?>
    <?php if(!empty($p['short'])): ?><p class="short"><?= h(fill($p['short'])) ?></p><?php endif; ?>
    <?php if(in_stock($p)): ?>
    <form method="post" action="<?= link_to('cart') ?>" class="add">
      <input type="hidden" name="action" value="add"><input type="hidden" name="id" value="<?= h($p['id']) ?>">
      <label class="qty"><span>Qty</span><input type="number" name="qty" value="1" min="1" max="100" inputmode="numeric"></label>
      <button class="btn btn-gold btn-lg" type="submit">Add to cart</button>
    </form>
    <?php else: ?><p class="note">This item is currently sold out. <a href="<?= link_to('contact') ?>">Contact us</a> to be told when it’s back.</p><?php endif; ?>
    <ul class="assure">
      <li><b>Price locked</b> when you order — pay within <?= h($CFG['pay_window']) ?> hours</li>
      <li><b>Insured shipping</b> — <?= h(implode(' · ', array_map(fn($m)=>$m['label'].' '.usd($m['price'], 0), ship_methods($STORE)))) ?></li>
      <li><b>Pay with</b> <?= h(implode(', ', array_column(array_filter($STORE['payments'], fn($m)=>!empty($m['enabled'])), 'label'))) ?></li>
      <li><b>Authenticity guaranteed</b> — <a href="<?= link_to('authenticity-guarantee') ?>">our promise</a></li>
    </ul>
  </div>
</div>
<section class="wrap sec content-grid">
  <article class="prose">
    <h2>Specifications</h2>
    <table class="specs"><tbody>
      <?php foreach(array_filter([
        'Metal'=>METALS[$p['metal'] ?? 'gold'] ?? '',
        'Weight'=>weight_label($p['weight'] ?? 0, $p['unit'] ?? 'oz'),
        'Fine content'=>$oz > 0 ? rtrim(rtrim(number_format($oz, 4), '0'), '.').' troy oz ('.number_format($oz * G_PER_OZT, 2).' g)' : '',
        'Purity'=>$p['purity'] ?? '',
        'Type'=>['bar'=>'Bar', 'coin'=>'Coin', 'round'=>'Round'][$p['type'] ?? ''] ?? '',
        'Brand / mint'=>$p['brand'] ?? '',
        'Packaging'=>$p['packaging'] ?? '',
        'SKU'=>$p['sku'] ?? '',
        'Metal value today'=>$melt ? usd($melt).' (at spot)' : '',
      ]) as $k=>$v): ?><tr><th><?= h($k) ?></th><td><?= h($v) ?></td></tr><?php endforeach; ?>
    </tbody></table>
    <?php if(!empty($p['description'])): ?><h2>Product description</h2><?= content($p['description']) ?><?php endif; ?>
    <?php if($cat && !empty($cat['body'])): ?><p>Learn more: <a href="<?= link_to($cat['slug']) ?>"><?= h($cat['name']) ?></a> · <a href="<?= link_to('gold-price') ?>">gold price today</a> · <a href="<?= link_to('guides/gold-bar-weight-and-sizes') ?>">gold bar weights &amp; sizes</a></p><?php endif; ?>
  </article>
  <aside class="side"><?= side_box() ?></aside>
</section>
<?php
  $related = array_slice(array_values(array_filter($cat ? cat_products($cat['parent'] ?: $cat['slug']) : visible_products(), fn($x)=>$x['id'] !== $p['id'])), 0, 4);
  if($related) echo '<section class="wrap sec"><h2 class="h2">You may also like</h2>'.product_grid($related).'</section>';
  $META['schema'][] = product_schema($p, $price);
}

function view_tool($seg, $path){
  global $META;
  $t = tool_by_slug($path);
  $k = (int)($t['k'] ?? 24);
  $extra = ['{k_gram}'=>gold_value_txt(1, 'g', $k), '{k_oz}'=>gold_value_txt(1, 'oz', $k), '{k_dwt}'=>gold_value_txt(1, 'dwt', $k)];
  $META['title'] = fill($t['seo_title'], $extra); $META['desc'] = fill($t['seo_desc'], $extra); $META['canonical'] = abs_url($t['slug']);
  $META['full_title'] = true;
  $ar = ($t['kind'] ?? '') === 'ar';
  if($ar){ $META['lang'] = 'ar'; $META['alt'] = ['en'=>'gold-price', 'ar'=>'ar/gold-price']; }
  if($t['slug'] === 'gold-price') $META['alt'] = ['en'=>'gold-price', 'ar'=>'ar/gold-price'];
  crumbs($t['slug'] === 'gold-price' || $ar ? [[$ar ? 'سعر الذهب' : 'Gold Price Today', $t['slug']]] : [['Gold Price Today', 'gold-price'], [$t['nav'] ?? $t['h1'], $t['slug']]]);
  $spot = spot(); ?>
<div class="wrap page-head<?= $ar ? ' rtl' : '' ?>"<?= $ar ? ' dir="rtl" lang="ar"' : '' ?>>
  <h1><?= h(fill($t['h1'], $extra)) ?></h1>
  <p class="lead"><?= fill($t['intro'], $extra) ?></p>
  <p class="updated"><?= $ar ? 'آخر تحديث' : 'Updated' ?> <time datetime="<?= date('c', $spot['time'] ?: time()) ?>"><?= date('M j, Y g:i A T', $spot['time'] ?: time()) ?></time><?= $spot['gold'] > 0 ? '' : ' — live price temporarily unavailable' ?></p>
</div>
<section class="wrap sec tool<?= $ar ? ' rtl' : '' ?>"<?= $ar ? ' dir="rtl" lang="ar"' : '' ?>><?= tool_panel($t) ?></section>
<section class="wrap sec content-grid<?= $ar ? ' rtl' : '' ?>"<?= $ar ? ' dir="rtl" lang="ar"' : '' ?>>
  <article class="prose"><?= content($t['body'], $extra) ?><?= faq_block($t['faqs'] ?? [], $ar ? 'أسئلة شائعة' : 'Frequently asked questions', $extra) ?></article>
  <aside class="side" dir="ltr" lang="en"><?= price_nav($t['slug']) ?><?= side_box() ?></aside>
</section>
<?php
  $META['schema'][] = ['@context'=>'https://schema.org', '@type'=>'WebPage', 'name'=>$META['title'], 'url'=>$META['canonical'], 'dateModified'=>date('c', $spot['time'] ?: time()), 'inLanguage'=>$ar ? 'ar' : 'en-US'];
}

function view_guides(){
  global $CFG, $META;
  $META['title'] = fill($CFG['guides_seo_title']); $META['desc'] = fill($CFG['guides_seo_desc']); $META['canonical'] = abs_url('guides');
  crumbs([['Guides', 'guides']]);
  $topics = ['basics'=>'Gold bullion basics', 'bars'=>'Gold bars', 'coins'=>'Gold coins', 'prices'=>'Prices & investing', 'safety'=>'Authenticity & safety', 'selling'=>'Selling gold']; ?>
<div class="wrap page-head"><h1>Gold Bullion Guides</h1><p class="lead">Plain-English guides to buying, valuing and owning gold — written to help you make better decisions, whoever you buy from.</p></div>
<?php foreach($topics as $k=>$label){ $gs = array_values(array_filter(guides_all(), fn($g)=>($g['topic'] ?? 'basics') === $k)); if(!$gs) continue; ?>
<section class="wrap sec"><h2 class="h2"><?= h($label) ?></h2><?= guide_cards($gs) ?></section>
<?php } ?>
<section class="wrap sec"><h2 class="h2">Live gold price tools</h2><?= price_links() ?></section>
<?php }

function view_guide($seg){
  global $META, $CFG;
  $g = guide_by_slug($seg[1]);
  $META['title'] = fill($g['seo_title'] ?: $g['title']); $META['desc'] = fill($g['seo_desc'] ?: ($g['summary'] ?? ''));
  $META['canonical'] = abs_url('guides/'.$g['slug']); $META['type'] = 'article'; $META['full_title'] = true;
  if(!empty($g['lang'])) $META['lang'] = $g['lang'];
  crumbs([['Guides', 'guides'], [$g['title'], 'guides/'.$g['slug']]]);
  [$body, $toc] = with_toc(content($g['body']));
  $words = str_word_count(strip_tags($body));
  $updated = $g['updated'] ?? $g['date'] ?? date('Y-m-d'); ?>
<div class="wrap page-head article-head">
  <h1><?= h(fill($g['title'])) ?></h1>
  <p class="byline">By <?= h($CFG['author']) ?> · Updated <time datetime="<?= h($updated) ?>"><?= date('F j, Y', strtotime($updated)) ?></time> · <?= max(2, (int)round($words / 220)) ?> min read</p>
</div>
<section class="wrap sec content-grid">
  <article class="prose">
    <?php if(count($toc) > 3): ?><nav class="toc" aria-label="Contents"><b>In this guide</b><ol><?php foreach($toc as [$id, $label]): ?><li><a href="#<?= h($id) ?>"><?= h($label) ?></a></li><?php endforeach; ?></ol></nav><?php endif; ?>
    <?= $body ?>
    <?= faq_block($g['faqs'] ?? []) ?>
    <p class="disclaimer small muted">This guide is general information, not financial advice. Prices quoted are live spot values and change through the day. See our <a href="<?= link_to('disclaimer') ?>">disclaimer</a>.</p>
  </article>
  <aside class="side"><?= side_box() ?><?= related_guides_box(related_guides($g)) ?></aside>
</section>
<?php
  $META['schema'][] = ['@context'=>'https://schema.org', '@type'=>'Article', 'headline'=>mb_substr(fill($g['title']), 0, 110), 'description'=>$META['desc'],
    'datePublished'=>$g['date'] ?? $updated, 'dateModified'=>$updated, 'inLanguage'=>$g['lang'] ?? 'en-US',
    'author'=>['@type'=>'Organization', 'name'=>$CFG['author'], 'url'=>abs_url('about-us')],
    'publisher'=>['@type'=>'Organization', 'name'=>$CFG['brand'], 'logo'=>['@type'=>'ImageObject', 'url'=>abs_url('assets/img/logo-512.png')]],
    'mainEntityOfPage'=>$META['canonical'], 'image'=>abs_url('assets/img/share.jpg')];
}

function view_page($seg, $path){
  global $META, $CFG;
  $pg = page_by_slug($path);
  $META['title'] = fill($pg['seo_title'] ?: $pg['title']); $META['desc'] = fill($pg['seo_desc']); $META['canonical'] = abs_url($pg['slug']);
  if($pg['slug'] === 'track-order') $META['robots'] = 'noindex,follow';
  crumbs([[$pg['title'], $pg['slug']]]); ?>
<div class="wrap page-head"><h1><?= h(fill($pg['h1'] ?: $pg['title'])) ?></h1></div>
<section class="wrap sec content-grid">
  <article class="prose">
    <?= content($pg['body']) ?>
    <?php if($pg['slug'] === 'contact') contact_form(); ?>
    <?php if($pg['slug'] === 'track-order') track_form(); ?>
    <?= faq_block($pg['faqs'] ?? [], $pg['slug'] === 'faq' ? '' : 'Frequently asked questions') ?>
  </article>
  <aside class="side"><?= $pg['slug'] === 'contact' ? contact_box() : side_box() ?></aside>
</section>
<?php
  if($pg['slug'] === 'about-us') $META['schema'][] = ['@context'=>'https://schema.org', '@type'=>'AboutPage', 'name'=>$META['title'], 'url'=>$META['canonical']];
  if($pg['slug'] === 'contact') $META['schema'][] = ['@context'=>'https://schema.org', '@type'=>'ContactPage', 'name'=>$META['title'], 'url'=>$META['canonical']];
}

function view_cart(){
  global $META, $STORE;
  $META['title'] = 'Your Cart'; $META['robots'] = 'noindex,nofollow'; $META['canonical'] = abs_url('cart');
  $lines = cart_lines(); ?>
<div class="wrap page-head"><h1>Your Cart</h1></div>
<section class="wrap sec narrow">
<?php if(!$lines): ?>
  <div class="empty"><p>Your cart is empty.</p><a class="btn btn-gold" href="<?= link_to('shop') ?>">Shop gold bullion</a></div>
<?php else: ?>
  <form method="post" action="<?= link_to('cart') ?>" class="cart">
    <input type="hidden" name="action" value="cart">
    <table class="cart-table"><thead><tr><th>Product</th><th>Price</th><th>Qty</th><th class="r">Total</th><th></th></tr></thead><tbody>
    <?php foreach($lines as $l): $p = $l['p']; ?>
      <tr><td><a href="<?= product_url($p) ?>"><?= h($p['name']) ?></a><div class="small muted"><?= h(product_spec_line($p)) ?></div></td>
        <td><?= usd($l['unit']) ?></td>
        <td><input type="number" name="qty[<?= h($p['id']) ?>]" value="<?= (int)$l['qty'] ?>" min="0" max="100" class="qty-in" aria-label="Quantity"></td>
        <td class="r"><?= usd($l['total']) ?></td>
        <td><button class="link" name="remove" value="<?= h($p['id']) ?>" aria-label="Remove">Remove</button></td></tr>
    <?php endforeach; ?>
    </tbody></table>
    <div class="cart-foot">
      <button class="btn btn-ghost" type="submit">Update cart</button>
      <div class="cart-sum"><p>Subtotal <b><?= usd(cart_total()) ?></b></p><p class="small muted">Shipping from <?= usd(min(array_column(ship_methods($STORE), 'price') ?: [0])) ?>, chosen at checkout. Prices follow the live gold price until you place your order.</p>
        <button class="btn btn-gold btn-lg" type="submit" name="checkout" value="1">Checkout</button></div>
    </div>
  </form>
<?php endif; ?>
</section>
<?php }

function view_checkout(){
  global $META, $STORE, $CFG;
  $META['title'] = 'Checkout'; $META['robots'] = 'noindex,nofollow'; $META['canonical'] = abs_url('checkout');
  start_session();
  $lines = cart_lines();
  if(!$lines) redirect('cart');
  $f = $_SESSION['checkout'] ?? [];
  $v = fn($k)=>h($f[$k] ?? '');
  $goods = cart_total();
  $methods = ship_methods($STORE);
  $pays = array_filter($STORE['payments'], fn($m)=>!empty($m['enabled']));
  $sel_ship = $f['ship'] ?? array_key_first($methods);
  $countries = countries(); ?>
<div class="wrap page-head"><h1>Checkout</h1><p class="lead">No payment is taken on this website. After you order, we text or email you the payment details {reply}.</p></div>
<section class="wrap sec">
<form method="post" action="<?= link_to('checkout') ?>" class="checkout" id="checkout" novalidate>
  <input type="hidden" name="action" value="order"><input type="hidden" name="csrf" value="<?= h(csrf()) ?>">
  <div class="hp" aria-hidden="true"><label>Website <input type="text" name="website" tabindex="-1" autocomplete="off"></label></div>
  <div class="co-main">
    <fieldset><legend>1. Contact details</legend>
      <div class="row2"><label>First name<input name="first_name" value="<?= $v('first_name') ?>" required autocomplete="given-name"></label><label>Last name<input name="last_name" value="<?= $v('last_name') ?>" required autocomplete="family-name"></label></div>
      <div class="row2"><label>Email<input type="email" name="email" value="<?= $v('email') ?>" required autocomplete="email"></label>
      <label>Mobile phone <span class="req">required</span><input type="tel" name="phone" value="<?= $v('phone') ?>" required autocomplete="tel" placeholder="(555) 123-4567" pattern="[\d\s()+.\-]{10,20}"></label></div>
      <p class="small muted">We text or email your payment details and may call to confirm your order.</p>
    </fieldset>
    <fieldset><legend>2. Delivery address</legend>
      <label>Street address<input name="address1" value="<?= $v('address1') ?>" required autocomplete="address-line1"></label>
      <label>Apartment, suite, etc. (optional)<input name="address2" value="<?= $v('address2') ?>" autocomplete="address-line2"></label>
      <div class="row3"><label>City<input name="city" value="<?= $v('city') ?>" required autocomplete="address-level2"></label>
        <label>State<?php if(($f['country'] ?? $countries[0]) === 'United States' && count($countries) === 1): ?><select name="state" required autocomplete="address-level1"><option value="">Select…</option><?php foreach(US_STATES as $code=>$name): ?><option value="<?= $code ?>"<?= ($f['state'] ?? '') === $code ? ' selected' : '' ?>><?= h($name) ?></option><?php endforeach; ?></select><?php else: ?><input name="state" value="<?= $v('state') ?>" required autocomplete="address-level1"><?php endif; ?></label>
        <label>ZIP code<input name="zip" value="<?= $v('zip') ?>" required autocomplete="postal-code" inputmode="numeric"></label></div>
      <?php if(count($countries) > 1): ?><label>Country<select name="country"><?php foreach($countries as $c): ?><option<?= ($f['country'] ?? '') === $c ? ' selected' : '' ?>><?= h($c) ?></option><?php endforeach; ?></select></label>
      <?php else: ?><input type="hidden" name="country" value="<?= h($countries[0]) ?>"><p class="small muted">We ship to street addresses in the <?= h($countries[0]) ?>. No PO Boxes. An adult must sign for delivery.</p><?php endif; ?>
    </fieldset>
    <fieldset><legend>3. Shipping</legend>
      <?php foreach($methods as $k=>$m): $cost = shipping_cost($STORE, $k, $goods); ?>
      <label class="choice"><input type="radio" name="ship" value="<?= h($k) ?>" data-cost="<?= $cost ?>"<?= $sel_ship === $k ? ' checked' : '' ?> required>
        <span><b><?= h($m['label']) ?> — <?= $cost > 0 ? usd($cost) : 'Free' ?></b><small><?= h($m['days']) ?><?= $m['note'] ? ' · '.h($m['note']) : '' ?></small></span></label>
      <?php endforeach; ?>
    </fieldset>
    <fieldset><legend>4. Payment method</legend>
      <?php foreach($pays as $k=>$m): ?>
      <label class="choice"><input type="radio" name="pay" value="<?= h($k) ?>"<?= ($f['pay'] ?? '') === $k ? ' checked' : '' ?> required>
        <span><b><?= h($m['label']) ?></b><small><?= h(fill($m['note'])) ?></small></span></label>
      <?php endforeach; ?>
      <p class="small muted">Payments must come from an account in your name. We only send payment details from <?= h($CFG['email']) ?><?= $CFG['phone'] ? ' or '.h($CFG['phone']) : '' ?>.</p>
    </fieldset>
    <fieldset><legend>5. Order notes (optional)</legend><textarea name="notes" rows="3" maxlength="1000"><?= $v('notes') ?></textarea></fieldset>
  </div>
  <aside class="co-side">
    <div class="summary"><h2>Order summary</h2>
      <ul class="sum-lines"><?php foreach($lines as $l): ?><li><span><?= (int)$l['qty'] ?> × <?= h($l['p']['name']) ?></span><b><?= usd($l['total']) ?></b></li><?php endforeach; ?></ul>
      <p class="sum-row"><span>Subtotal</span><b data-goods="<?= $goods ?>"><?= usd($goods) ?></b></p>
      <p class="sum-row"><span>Shipping</span><b id="ship-cost"><?= usd(shipping_cost($STORE, $sel_ship, $goods) ?? 0) ?></b></p>
      <p class="sum-row total"><span>Total</span><b id="grand"><?= usd($goods + (shipping_cost($STORE, $sel_ship, $goods) ?? 0)) ?></b></p>
      <label class="agree"><input type="checkbox" name="agree" value="1" required> I agree to the <a href="<?= link_to('terms-and-conditions') ?>" target="_blank">terms</a> and <a href="<?= link_to('shipping-returns') ?>" target="_blank">shipping &amp; returns policy</a>.</label>
      <button class="btn btn-gold btn-lg btn-block" type="submit">Place order</button>
      <p class="small muted center">Your price is locked when you place your order and held for <?= h($CFG['pay_window']) ?> hours.</p>
    </div>
  </aside>
</form>
</section>
<?php
  $GLOBALS['BODY_REPLACE'] = ['{reply}'=>h($CFG['reply_time'])];
}

function view_received(){
  global $META, $CFG, $STORE;
  $META['title'] = 'Order Received'; $META['robots'] = 'noindex,nofollow';
  $ref = (string)($_GET['ref'] ?? '');
  $o = order_key_ok($ref, $_GET['k'] ?? '') ? order_load($ref) : null;
  if(!$o){ echo '<div class="wrap page-head"><h1>Order not found</h1><p><a href="'.link_to('track-order').'">Track an order</a></p></div>'; return; } ?>
<div class="wrap page-head center"><p class="check" aria-hidden="true">✓</p><h1>Thank you, <?= h($o['first_name']) ?> — your order is in</h1>
  <p class="lead">Order number <b><?= h($o['ref']) ?></b>. A confirmation has been sent to <b><?= h($o['email']) ?></b>.</p></div>
<section class="wrap sec narrow">
  <div class="next"><h2>What happens next</h2><ol>
    <li><b>We send your payment details</b> for <?= h($o['payment_label']) ?> by text to <?= h($o['phone']) ?> or by email, <?= h($CFG['reply_time']) ?>.</li>
    <li><b>You pay within <?= h($CFG['pay_window']) ?> hours</b> — your price of <?= usd($o['total']) ?> is locked until then. Put <?= h($o['ref']) ?> in the payment note.</li>
    <li><b>We ship fully insured</b> <?= h($CFG['dispatch_time']) ?> and email you tracking.</li></ol>
    <p class="note">We only send payment details from <b><?= h($CFG['email']) ?></b><?= $CFG['phone'] ? ' or <b>'.h($CFG['phone']).'</b>' : '' ?>. Never pay details sent from anywhere else.</p></div>
  <table class="cart-table"><tbody>
    <?php foreach($o['lines'] as $l): ?><tr><td><?= (int)$l['qty'] ?> × <?= h($l['name']) ?></td><td class="r"><?= usd($l['total']) ?></td></tr><?php endforeach; ?>
    <tr><td>Shipping (<?= h($o['ship_label']) ?>)</td><td class="r"><?= $o['shipping'] > 0 ? usd($o['shipping']) : 'Free' ?></td></tr>
    <tr class="total"><td>Total</td><td class="r"><?= usd($o['total']) ?></td></tr>
  </tbody></table>
  <p class="center"><a class="btn btn-ghost" href="<?= link_to('') ?>">Back to the shop</a></p>
</section>
<?php }

function view_search(){
  global $META;
  $q = trim((string)($_GET['q'] ?? ''));
  $META['title'] = 'Search'.($q !== '' ? ': '.$q : ''); $META['robots'] = 'noindex,follow';
  $ql = mb_strtolower($q);
  $products = $q === '' ? [] : array_values(array_filter(visible_products(), fn($p)=>mb_strpos(mb_strtolower($p['name'].' '.($p['brand'] ?? '').' '.($p['sku'] ?? '')), $ql) !== false));
  $guides = $q === '' ? [] : array_values(array_filter(guides_all(), fn($g)=>mb_strpos(mb_strtolower($g['title'].' '.($g['summary'] ?? '')), $ql) !== false)); ?>
<div class="wrap page-head"><h1>Search</h1><form class="search-big" action="<?= link_to('search') ?>"><input name="q" value="<?= h($q) ?>" placeholder="Search gold bars, coins, guides…" aria-label="Search"><button class="btn btn-gold">Search</button></form></div>
<section class="wrap sec"><?php if($q !== ''): ?>
  <?= $products ? product_grid($products) : '<p>No products match “'.h($q).'”.</p>' ?>
  <?php if($guides): ?><h2 class="h2">Guides</h2><?= guide_cards($guides) ?><?php endif; ?>
<?php endif; ?></section>
<?php }

function view_notfound(){
  global $META;
  http_response_code(404);
  $META['title'] = 'Page Not Found'; $META['robots'] = 'noindex,follow'; ?>
<div class="wrap page-head center"><h1>Page not found</h1><p class="lead">Sorry, that page doesn’t exist. Try one of these:</p>
<p><a class="btn btn-gold" href="<?= link_to('shop') ?>">Shop gold bullion</a> <a class="btn btn-ghost" href="<?= link_to('gold-price') ?>">Gold price today</a> <a class="btn btn-ghost" href="<?= link_to('guides') ?>">Guides</a></p></div>
<?php }

/* =============================================================
   COMPONENTS
   ============================================================= */
function product_spec_line($p){
  $main = implode(' ', array_filter([weight_label($p['weight'] ?? 0, $p['unit'] ?? 'oz'), METALS[$p['metal'] ?? 'gold'] ?? '', ['bar'=>'bar', 'coin'=>'coin', 'round'=>'round'][$p['type'] ?? ''] ?? '']));
  $pur = trim((string)($p['purity'] ?? ''));
  if($pur !== '' && preg_match('/^[.\d]+$/', $pur)) $pur .= ' fine';
  return $main.($main !== '' && $pur !== '' ? ' · ' : '').$pur;
}

function product_img($p, $i=0, $lcp=false){
  $imgs = array_values(array_filter((array)($p['images'] ?? [])));
  $alt = $p['name'].(!empty($p['brand']) ? ' by '.$p['brand'] : '');
  $art = ($p['type'] ?? '') === 'coin' ? 'coin' : 'bar';
  if(isset($imgs[$i])) return '<img src="'.h(img_src($imgs[$i])).'" alt="'.h($alt).'" width="600" height="600"'.($lcp ? ' fetchpriority="high"' : ' loading="lazy" decoding="async"')
    .' onerror="this.onerror=null;this.src=\''.h(link_to('assets/img/'.$art.'.svg')).'\'">';
  return '<img src="'.h(link_to('assets/img/'.$art.'.svg')).'" alt="'.h($alt).'" width="600" height="600" class="ph"'.($lcp ? '' : ' loading="lazy"').'>';
}

function product_grid($products){
  ob_start(); ?>
<ul class="grid"><?php foreach($products as $p): $price = product_price($p); ?>
  <li class="card">
    <a class="card-img" href="<?= product_url($p) ?>"><?= product_img($p) ?></a>
    <div class="card-body">
      <h3><a href="<?= product_url($p) ?>"><?= h($p['name']) ?></a></h3>
      <p class="small muted"><?= h(product_spec_line($p)) ?></p>
      <p class="card-price"><?= $price > 0 ? usd($price) : 'Call for price' ?><?php if(($p['stock'] ?? 'in') !== 'in'): ?> <span class="stock stock-<?= h($p['stock']) ?>"><?= h(STOCK_STATUSES[$p['stock']] ?? '') ?></span><?php endif; ?></p>
      <?php if(in_stock($p)): ?><form method="post" action="<?= link_to('cart') ?>"><input type="hidden" name="action" value="add"><input type="hidden" name="id" value="<?= h($p['id']) ?>"><button class="btn btn-gold btn-sm btn-block" type="submit">Add to cart</button></form><?php endif; ?>
    </div>
  </li>
<?php endforeach; ?></ul>
<?php return ob_get_clean();
}

function size_tiles(){
  $sizes = [['1-gram-gold-bars', '1 gram', 1, 'g'], ['10-gram-gold-bars', '10 gram', 10, 'g'], ['1-oz-gold-bars', '1 oz', 1, 'oz'], ['100-gram-gold-bars', '100 gram', 100, 'g'],
            ['10-oz-gold-bars', '10 oz', 10, 'oz'], ['1-kilo-gold-bars', '1 kilo', 1, 'kg'], ['gold-coins', 'Gold coins', 1, 'oz']];
  $out = '<ul class="tiles">';
  foreach($sizes as [$slug, $label, $w, $u]){
    $c = category($slug);
    if(!$c) continue;
    $out .= '<li><a href="'.link_to($slug).'"><span class="tile-art tile-'.($slug === 'gold-coins' ? 'coin' : 'bar').'" aria-hidden="true"></span><b>'.h($c['name']).'</b><small>'
          .($slug === 'gold-coins' ? '1 oz coin gold value '.gold_value_txt(1, 'oz') : 'Gold value '.gold_value_txt($w, $u)).'</small></a></li>';
  }
  return $out.'</ul>';
}

function cat_chips($current){
  global $STORE;
  $out = '<nav class="chips" aria-label="Categories"><a href="'.link_to('shop').'"'.($current === '' ? ' aria-current="page"' : '').'>All</a>';
  foreach($STORE['categories'] as $c){
    if(empty($c['nav']) && $c['slug'] !== $current) continue;
    if(!cat_products($c['slug']) && $c['slug'] !== $current && !in_array($c['slug'], ['gold-bars', 'gold-coins'], true)) continue;
    $out .= '<a href="'.link_to($c['slug']).'"'.($c['slug'] === $current ? ' aria-current="page"' : '').'>'.h($c['name']).'</a>';
  }
  return $out.'</nav>';
}

function spot_card(){
  $s = spot();
  $chg = function($v, $pct){ if($v === null) return ''; $up = $v >= 0; return '<span class="chg '.($up ? 'up' : 'down').'">'.($up ? '▲' : '▼').' '.usd(abs($v)).($pct !== null ? ' ('.number_format(abs($pct), 2).'%)' : '').'</span>'; };
  ob_start(); ?>
<div class="spot-card">
  <div class="spot-title"><span class="dot<?= !empty($s['live']) ? ' live' : '' ?>"></span>Live spot price</div>
  <div class="spot-row"><span>Gold</span><b><?= $s['gold'] > 0 ? usd($s['gold']) : '—' ?></b><small>/ oz</small><?= $chg($s['gold_change'] ?? null, $s['gold_change_pct'] ?? null) ?></div>
  <div class="spot-row spot-sub"><span>Per gram</span><b><?= $s['gold'] > 0 ? usd($s['gold'] / G_PER_OZT) : '—' ?></b><span>Per kilo</span><b><?= gold_value_txt(1, 'kg') ?></b></div>
  <div class="spot-row"><span>Silver</span><b><?= $s['silver'] > 0 ? usd($s['silver']) : '—' ?></b><small>/ oz</small><?= $chg($s['silver_change'] ?? null, $s['silver_change_pct'] ?? null) ?></div>
  <p class="spot-time">Updated <?= date('M j, g:i A T', $s['time'] ?: time()) ?> · <a href="<?= link_to('gold-price') ?>">Gold price today</a></p>
</div>
<?php return ob_get_clean();
}

function karat_table($ks, $links=false){
  $tools = ['24'=>'24k-gold-price', '22'=>'22k-gold-price', '21'=>'21k-gold-price', '18'=>'18k-gold-price', '14'=>'14k-gold-price', '10'=>'10k-gold-price'];
  $out = '<div class="tbl-wrap"><table class="price-table"><thead><tr><th>Karat</th><th>Purity</th><th>Per gram</th><th>Per pennyweight</th><th>Per troy oz</th><th>Per tola</th></tr></thead><tbody>';
  foreach($ks as $k){
    $name = $k.'K gold';
    $cell = $links && isset($tools[(string)$k]) && tool_by_slug($tools[(string)$k]) ? '<a href="'.link_to($tools[(string)$k]).'">'.$name.'</a>' : $name;
    $out .= '<tr><th>'.$cell.'</th><td>'.number_format($k / 24 * 100, 1).'%</td><td>'.gold_value_txt(1, 'g', $k).'</td><td>'.gold_value_txt(1, 'dwt', $k).'</td><td>'.gold_value_txt(1, 'oz', $k).'</td><td>'.gold_value_txt(1, 'tola', $k).'</td></tr>';
  }
  return $out.'</tbody></table></div>';
}

/* the live numbers at the top of each price page */
function tool_panel($t){
  $kind = $t['kind'] ?? 'spot'; $s = spot();
  $box = fn($label, $value, $big=false)=>'<div class="pbox'.($big ? ' big' : '').'"><span>'.h($label).'</span><b>'.$value.'</b></div>';
  $wt = function($rows, $ks){
    $out = '<div class="tbl-wrap"><table class="price-table"><thead><tr><th>Weight</th>';
    foreach($ks as $k) $out .= '<th>'.$k.'K</th>';
    $out .= '</tr></thead><tbody>';
    foreach($rows as [$label, $amt, $unit]){
      $out .= '<tr><th>'.h($label).'</th>';
      foreach($ks as $k) $out .= '<td>'.gold_value_txt($amt, $unit, $k).'</td>';
      $out .= '</tr>';
    }
    return $out.'</tbody></table></div>';
  };
  ob_start();
  switch($kind){
    case 'spot':
      echo '<div class="tool-top">'.spot_card().'<div class="pboxes">'.$box('Gold per ounce', $s['gold'] > 0 ? usd($s['gold']) : '—', true).$box('Gold per gram', gold_value_txt(1, 'g')).$box('Gold per kilo', gold_value_txt(1, 'kg'))
         .$box('Silver per ounce', $s['silver'] > 0 ? usd($s['silver']) : '—', true).$box('Silver per gram', $s['silver'] > 0 ? usd($s['silver'] / G_PER_OZT) : '—').$box('Silver per kilo', $s['silver'] > 0 ? usd($s['silver'] / G_PER_OZT * 1000) : '—').'</div></div>';
      echo '<h2 class="h2">Gold price per gram, ounce and tola by karat</h2>'.karat_table([24, 22, 21, 18, 14, 10], true);
      echo calculator_html();
      break;
    case 'karat':
      $k = (int)$t['k'];
      echo '<div class="pboxes">'.$box($k.'K gold per gram', gold_value_txt(1, 'g', $k), true).$box($k.'K per troy ounce', gold_value_txt(1, 'oz', $k)).$box($k.'K per pennyweight', gold_value_txt(1, 'dwt', $k)).$box($k.'K per tola', gold_value_txt(1, 'tola', $k)).$box($k.'K per kilo', gold_value_txt(1, 'kg', $k)).'</div>';
      echo '<h2 class="h2">'.$k.'K gold price by weight</h2>';
      $rows = [['1 gram', 1, 'g'], ['2 grams', 2, 'g'], ['5 grams', 5, 'g'], ['10 grams', 10, 'g'], ['20 grams', 20, 'g'], ['50 grams', 50, 'g'], ['1 pennyweight (dwt)', 1, 'dwt'], ['1 tola', 1, 'tola'], ['1 troy ounce', 1, 'oz'], ['100 grams', 100, 'g']];
      echo '<div class="tbl-wrap"><table class="price-table"><thead><tr><th>Weight</th><th>'.$k.'K gold value</th><th>24K gold value</th></tr></thead><tbody>';
      foreach($rows as [$label, $a, $u]) echo '<tr><th>'.h($label).'</th><td><b>'.gold_value_txt($a, $u, $k).'</b></td><td>'.gold_value_txt($a, $u, 24).'</td></tr>';
      echo '</tbody></table></div>';
      echo calculator_html($k);
      break;
    case 'unit':
      $u = $t['unit'];
      if($u === 'g'){
        echo '<div class="pboxes">'.$box('1 gram of 24K gold', gold_value_txt(1, 'g'), true).$box('1 gram of 22K', gold_value_txt(1, 'g', 22)).$box('1 gram of 18K', gold_value_txt(1, 'g', 18)).$box('1 gram of 14K', gold_value_txt(1, 'g', 14)).$box('1 gram of 10K', gold_value_txt(1, 'g', 10)).'</div>';
        echo '<h2 class="h2">Gold price per gram by karat and weight</h2>'.$wt([['½ gram', 0.5, 'g'], ['1 gram', 1, 'g'], ['2 grams', 2, 'g'], ['3 grams', 3, 'g'], ['5 grams', 5, 'g'], ['10 grams', 10, 'g'], ['20 grams', 20, 'g'], ['50 grams', 50, 'g'], ['100 grams', 100, 'g']], [24, 22, 18, 14, 10]);
      } elseif($u === '10g'){
        echo '<div class="pboxes">'.$box('10 grams 24K', gold_value_txt(10, 'g'), true).$box('10 grams 22K', gold_value_txt(10, 'g', 22)).$box('10 grams 21K', gold_value_txt(10, 'g', 21)).$box('10 grams 18K', gold_value_txt(10, 'g', 18)).$box('10 grams 14K', gold_value_txt(10, 'g', 14)).'</div>';
        echo '<h2 class="h2">Gold price for 1 to 100 grams</h2>'.$wt([['1 gram', 1, 'g'], ['8 grams', 8, 'g'], ['10 grams', 10, 'g'], ['20 grams', 20, 'g'], ['50 grams', 50, 'g'], ['100 grams', 100, 'g']], [24, 22, 21, 18, 14]);
      } elseif($u === 'kg'){
        echo '<div class="pboxes">'.$box('1 kilo of gold (24K)', gold_value_txt(1, 'kg'), true).$box('Troy ounces in a kilo', '32.1507').$box('500 grams', gold_value_txt(500, 'g')).$box('1 metric ton', gold_value_txt(1, 't')).'</div>';
        echo '<h2 class="h2">Gold price per kilo by karat</h2>'.$wt([['100 grams', 100, 'g'], ['250 grams', 250, 'g'], ['500 grams', 500, 'g'], ['1 kilogram', 1, 'kg'], ['5 kilograms', 5, 'kg'], ['12.4 kg (400 oz bar)', 400, 'oz']], [24, 22, 18, 14]);
      } elseif($u === 'lb'){
        echo '<div class="pboxes">'.$box('1 pound (lb) of gold', gold_value_txt(1, 'lb'), true).$box('Troy ounces in a pound', '14.5833').$box('1 troy pound (12 oz)', gold_value_txt(12, 'oz')).$box('1 lb of 14K gold', gold_value_txt(1, 'lb', 14)).'</div>';
        echo '<h2 class="h2">Value of a pound of gold by karat</h2>'.$wt([['¼ lb', 0.25, 'lb'], ['½ lb', 0.5, 'lb'], ['1 lb', 1, 'lb'], ['2 lb', 2, 'lb'], ['5 lb', 5, 'lb'], ['27.4 lb (400 oz bar)', 400, 'oz']], [24, 22, 18, 14, 10]);
      } elseif($u === 'tola'){
        echo '<div class="pboxes">'.$box('1 tola 24K', gold_value_txt(1, 'tola'), true).$box('1 tola 22K', gold_value_txt(1, 'tola', 22)).$box('1 tola 21K', gold_value_txt(1, 'tola', 21)).$box('10 tola bar (24K)', gold_value_txt(10, 'tola')).'</div>';
        echo '<h2 class="h2">Gold tola rates by karat</h2>'.$wt([['1 tola', 1, 'tola'], ['2 tola', 2, 'tola'], ['5 tola', 5, 'tola'], ['10 tola', 10, 'tola']], [24, 22, 21, 18]);
      }
      echo calculator_html();
      break;
    case 'white':
      echo karat_table([18, 14, 10]);
      echo calculator_html(14);
      break;
    case 'coins':
      $coins = [['American Gold Eagle (1 oz)', '22K .9167', 1], ['American Gold Eagle (1/2 oz)', '22K .9167', 0.5], ['American Gold Eagle (1/4 oz)', '22K .9167', 0.25], ['American Gold Eagle (1/10 oz)', '22K .9167', 0.1],
        ['American Gold Buffalo (1 oz)', '24K .9999', 1], ['Canadian Gold Maple Leaf (1 oz)', '24K .9999', 1], ['South African Krugerrand (1 oz)', '22K .9167', 1], ['Austrian Gold Philharmonic (1 oz)', '24K .9999', 1],
        ['British Gold Britannia (1 oz)', '24K .9999', 1], ['Australian Gold Kangaroo (1 oz)', '24K .9999', 1], ['Chinese Gold Panda (30 g)', '24K .999', 30 / G_PER_OZT],
        ['British Gold Sovereign', '22K .9167', 0.2354], ['Mexican 50 Pesos', '21.6K .900', 1.2057], ['US $20 Double Eagle (pre-1933)', '21.6K .900', 0.9675], ['US $10 Eagle (pre-1933)', '21.6K .900', 0.48375],
        ['US $5 Half Eagle (pre-1929)', '21.6K .900', 0.241875], ['US $2.50 Quarter Eagle (pre-1929)', '21.6K .900', 0.1209]];
      echo '<div class="tbl-wrap"><table class="price-table"><thead><tr><th>Coin</th><th>Purity</th><th>Gold content</th><th>Melt value today</th></tr></thead><tbody>';
      foreach($coins as [$n, $pur, $oz]) echo '<tr><th>'.h($n).'</th><td>'.h($pur).'</td><td>'.rtrim(rtrim(number_format($oz, 4), '0'), '.').' oz</td><td><b>'.gold_value_txt($oz, 'oz').'</b></td></tr>';
      echo '</tbody></table></div><p class="small muted">Melt value = gold content × spot price. Pre-1933 US gold coins usually sell above melt value to collectors.</p>';
      break;
    case 'ar':
      echo '<div class="pboxes">'.$box('سعر أونصة الذهب', $s['gold'] > 0 ? usd($s['gold']) : '—', true).$box('غرام عيار 24', gold_value_txt(1, 'g', 24)).$box('غرام عيار 21', gold_value_txt(1, 'g', 21)).$box('غرام عيار 18', gold_value_txt(1, 'g', 18)).'</div>';
      echo '<div class="tbl-wrap"><table class="price-table"><thead><tr><th>العيار</th><th>سعر الغرام</th><th>سعر الأونصة</th><th>سعر التولة</th></tr></thead><tbody>';
      foreach([24, 22, 21, 18, 14] as $k) echo '<tr><th>عيار '.$k.'</th><td><b>'.gold_value_txt(1, 'g', $k).'</b></td><td>'.gold_value_txt(1, 'oz', $k).'</td><td>'.gold_value_txt(1, 'tola', $k).'</td></tr>';
      echo '</tbody></table></div><p class="small muted">الأسعار بالدولار الأمريكي، محسوبة من سعر الذهب الفوري. سعر الفضة اليوم: '.($s['silver'] > 0 ? usd($s['silver']) : '—').' للأونصة.</p>';
      break;
  }
  return ob_get_clean();
}

function calculator_html($k=24){
  $s = spot();
  if($s['gold'] <= 0) return '';
  $opt = fn($v, $label, $sel)=>'<option value="'.$v.'"'.($sel ? ' selected' : '').'>'.$label.'</option>';
  $units = ['g'=>'grams', 'dwt'=>'pennyweight (dwt)', 'oz'=>'troy ounces', 'tola'=>'tola', 'kg'=>'kilograms', 'lb'=>'pounds (lb)'];
  ob_start(); ?>
<div class="calc" data-spot="<?= h($s['gold']) ?>" dir="ltr" lang="en">
  <h2 class="h2">Gold value calculator</h2>
  <div class="calc-row">
    <label>Weight<input type="number" class="c-w" value="1" min="0" step="any" inputmode="decimal"></label>
    <label>Unit<select class="c-u"><?php foreach($units as $u=>$l) echo $opt(UNIT_G[$u], $l, $u === 'g'); ?></select></label>
    <label>Karat<select class="c-k"><?php foreach([24, 22, 21, 18, 14, 10, 9] as $kk) echo $opt($kk, $kk.'K', $kk === $k); ?></select></label>
  </div>
  <p class="calc-out">Gold value: <b class="c-v"><?= gold_value_txt(1, 'g', $k) ?></b></p>
  <p class="small muted">Melt value at today’s spot price of <?= usd($s['gold']) ?>/oz. Dealers and jewelers pay less than melt value for scrap.</p>
</div>
<?php return ob_get_clean();
}

function price_links(){
  global $STORE;
  $out = '<ul class="pill-links">';
  foreach($STORE['tools'] as $t) if(($t['kind'] ?? '') !== 'ar') $out .= '<li><a href="'.link_to($t['slug']).'">'.h($t['nav'] ?? $t['h1']).'</a></li>';
  return $out.'</ul>';
}

function price_nav($current){
  global $STORE;
  $out = '<nav class="box price-nav" aria-label="Gold prices"><h2>Gold prices</h2><ul>';
  foreach($STORE['tools'] as $t) $out .= '<li><a href="'.link_to($t['slug']).'"'.($t['slug'] === $current ? ' aria-current="page"' : '').(($t['lang'] ?? '') === 'ar' ? ' lang="ar" hreflang="ar"' : '').'>'.h($t['nav'] ?? $t['h1']).'</a></li>';
  return $out.'</ul></nav>';
}

function side_box(){
  global $CFG;
  return '<div class="box side-spot">'.spot_card().'</div><div class="box"><h2>Buy with confidence</h2><ul class="ticks"><li>Price locked when you order</li><li>Fully insured, discreet shipping</li><li>Genuine bullion, guaranteed</li><li>Real people: '.h($CFG['email']).'</li></ul><a class="btn btn-gold btn-block" href="'.link_to('shop').'">Shop gold bullion</a></div>';
}

function contact_box(){
  global $CFG;
  $out = '<div class="box"><h2>Contact details</h2><p><b>Email</b><br><a href="mailto:'.h($CFG['email']).'">'.h($CFG['email']).'</a></p>';
  if($CFG['phone']) $out .= '<p><b>Phone / text</b><br><a href="tel:'.h(preg_replace('/[^\d+]/', '', $CFG['phone'])).'">'.h($CFG['phone']).'</a></p>';
  if($CFG['address']) $out .= '<p><b>Address</b><br>'.nl2br(h($CFG['address'])).'</p>';
  return $out.'<p><b>Hours</b><br>'.h($CFG['hours']).'</p></div>';
}

function business_details_html(){
  global $CFG;
  $bits = [h($CFG['legal_name'] ?: $CFG['brand'])];
  if($CFG['address']) $bits[] = nl2br(h($CFG['address']));
  $bits[] = 'Email: <a href="mailto:'.h($CFG['email']).'">'.h($CFG['email']).'</a>';
  if($CFG['phone']) $bits[] = 'Phone: '.h($CFG['phone']);
  return implode('<br>', $bits);
}

function payment_list_html(){
  global $STORE;
  $out = '<div class="pay-list">';
  foreach($STORE['payments'] as $m) if(!empty($m['enabled'])) $out .= '<div class="pay-item"><h3>'.h($m['label']).'</h3><p>'.h(fill($m['note'])).'</p></div>';
  return $out.'</div>';
}

function shipping_table_html(){
  global $STORE;
  $out = '<div class="tbl-wrap"><table><thead><tr><th>Option</th><th>Price</th><th>Delivery time</th><th>Includes</th></tr></thead><tbody>';
  foreach(ship_methods($STORE) as $m) $out .= '<tr><td><b>'.h($m['label']).'</b></td><td>'.usd($m['price']).'</td><td>'.h($m['days']).'</td><td>'.h($m['note']).'</td></tr>';
  $free = (float)($STORE['shipping']['free_over'] ?? 0);
  return $out.'</tbody></table></div>'.($free > 0 ? '<p>Free standard shipping on orders over '.usd($free, 0).'.</p>' : '');
}

function faq_block($faqs, $title='Frequently asked questions', $extra=[]){
  global $META;
  if(!$faqs) return '';
  $out = ($title ? '<h2 id="faq">'.h($title).'</h2>' : '').'<div class="faq">';
  $ents = [];
  foreach($faqs as [$q, $a]){
    $q = fill($q, $extra); $a = content($a, $extra);
    $out .= '<details><summary>'.h($q).'</summary><div>'.(preg_match('/^\s*</', $a) ? $a : '<p>'.$a.'</p>').'</div></details>';
    $ents[] = ['@type'=>'Question', 'name'=>$q, 'acceptedAnswer'=>['@type'=>'Answer', 'text'=>strip_tags($a, '<a><b><strong><br>')]];
  }
  $META['schema'][] = ['@context'=>'https://schema.org', '@type'=>'FAQPage', 'mainEntity'=>$ents];
  return $out.'</div>';
}

function guide_cards($guides){
  $out = '<ul class="guide-cards">';
  foreach($guides as $g) $out .= '<li><a href="'.link_to('guides/'.$g['slug']).'"'.(!empty($g['lang']) ? ' hreflang="'.h($g['lang']).'"' : '').'><b>'.h(fill($g['title'])).'</b><span>'.h(fill($g['summary'] ?? '')).'</span></a></li>';
  return $out.'</ul>';
}

function related_guides($g){
  $all = guides_all(); $same = []; $other = [];
  foreach($all as $x){ if($x['slug'] === $g['slug'] || !empty($x['lang'])) continue; if(($x['topic'] ?? '') === ($g['topic'] ?? '')) $same[] = $x; else $other[] = $x; }
  return array_slice(array_merge($same, $other), 0, 5);
}
function cat_guides($slug){
  $map = ['gold-coins'=>['gold-coins-vs-gold-bars', 'mini-gold-coins', 'gold-quarters'], '1-gram-gold-bars'=>['is-a-1-gram-gold-bar-worth-it', 'gold-bar-weight-and-sizes', 'best-gold-bars-to-buy'],
          '1-kilo-gold-bars'=>['gold-bar-weight-and-sizes', '400-oz-gold-bar', 'how-much-is-a-gold-bar-worth'], '100-oz-gold-bars'=>['400-oz-gold-bar', 'gold-bar-weight-and-sizes']];
  $slugs = $map[$slug] ?? ['best-gold-bars-to-buy', 'gold-bar-weight-and-sizes', 'minted-vs-cast-gold-bars', 'how-to-buy-gold-bullion'];
  return array_values(array_filter(array_map('guide_by_slug', $slugs)));
}
function related_guides_box($guides){
  if(!$guides) return '';
  $out = '<nav class="box" aria-label="Related guides"><h2>Related guides</h2><ul class="links">';
  foreach($guides as $g) $out .= '<li><a href="'.link_to('guides/'.$g['slug']).'">'.h(fill($g['title'])).'</a></li>';
  return $out.'</ul></nav>';
}

function contact_form(){
  $m = $_SESSION['contact'] ?? []; $v = fn($k)=>h($m[$k] ?? ''); ?>
<form method="post" action="<?= link_to('contact') ?>" class="form">
  <input type="hidden" name="action" value="contact"><input type="hidden" name="csrf" value="<?= h(csrf()) ?>">
  <div class="hp" aria-hidden="true"><label>Website <input type="text" name="website" tabindex="-1" autocomplete="off"></label></div>
  <div class="row2"><label>Your name<input name="name" value="<?= $v('name') ?>" required autocomplete="name"></label><label>Email<input type="email" name="email" value="<?= $v('email') ?>" required autocomplete="email"></label></div>
  <div class="row2"><label>Phone (optional)<input type="tel" name="phone" value="<?= $v('phone') ?>" autocomplete="tel"></label><label>Order number (if any)<input name="order" value="<?= $v('order') ?>" placeholder="GB-…"></label></div>
  <label>Message<textarea name="message" rows="6" required><?= $v('message') ?></textarea></label>
  <button class="btn btn-gold" type="submit">Send message</button>
</form>
<?php }

function track_form(){
  global $CFG;
  $ref = strtoupper(trim((string)($_GET['order'] ?? ''))); $email = trim((string)($_GET['email'] ?? ''));
  $o = null; $tried = $ref !== '' || $email !== '';
  if($tried && !rate_limited('track', 20, 3600)){ $o = order_load($ref); if($o && strcasecmp($o['email'], $email) !== 0) $o = null; } ?>
<form method="get" action="<?= link_to('track-order') ?>" class="form">
  <div class="row2"><label>Order number<input name="order" value="<?= h($ref) ?>" placeholder="GB-2611-A1B2C" required></label><label>Email<input type="email" name="email" value="<?= h($email) ?>" required></label></div>
  <button class="btn btn-gold" type="submit">Check status</button>
</form>
<?php if($tried && !$o): ?><p class="note">We couldn’t find an order with those details. Check your confirmation email, or <a href="<?= link_to('contact') ?>">contact us</a>.</p>
<?php elseif($o): ?>
  <div class="next"><h2>Order <?= h($o['ref']) ?></h2><p>Status: <b><?= h(ORDER_STATUSES[$o['status']] ?? $o['status']) ?></b></p>
  <?php if(!empty($o['tracking'])): ?><p>Tracking: <b><?= h(trim(($o['carrier'] ?? '').' '.$o['tracking'])) ?></b></p><?php endif; ?>
  <p class="small muted">Placed <?= h($o['time']) ?> · Total <?= usd($o['total']) ?></p></div>
<?php endif;
}

/* ---------------- structured data ---------------- */
function product_schema($p, $price){
  global $CFG, $STORE;
  $imgs = array_map(fn($s)=>preg_match('#^https?://#', $s) ? $s : abs_url($s), array_values(array_filter((array)($p['images'] ?? []))));
  $ship = ship_methods($STORE)['standard'] ?? reset($STORE['shipping']['methods']);
  [$t1, $t2] = day_range($ship['days'] ?? '3-5');
  $d = ['@context'=>'https://schema.org', '@type'=>'Product', 'name'=>$p['name'], 'url'=>abs_url('products/'.$p['slug']),
    'description'=>plain(($p['description'] ?? '') ?: ($p['short'] ?? '') ?: $p['name'], 500), 'sku'=>$p['sku'] ?? $p['id'],
    'image'=>$imgs ?: [abs_url('assets/img/share.jpg')], 'category'=>'Precious Metals > Gold Bullion'];
  if(!empty($p['brand'])) $d['brand'] = ['@type'=>'Brand', 'name'=>$p['brand']];
  if(!empty($p['gtin'])) $d['gtin'] = $p['gtin'];
  if((float)($p['weight'] ?? 0) > 0) $d['weight'] = ['@type'=>'QuantitativeValue', 'value'=>(float)$p['weight'], 'unitCode'=>['g'=>'GRM', 'oz'=>'APZ', 'kg'=>'KGM'][$p['unit'] ?? 'oz'] ?? 'APZ'];
  if($price > 0) $d['offers'] = ['@type'=>'Offer', 'url'=>$d['url'], 'priceCurrency'=>'USD', 'price'=>number_format($price, 2, '.', ''),
    'priceValidUntil'=>date('Y-m-d', strtotime('+1 day')), 'itemCondition'=>'https://schema.org/NewCondition',
    'availability'=>['in'=>'https://schema.org/InStock', 'low'=>'https://schema.org/LimitedAvailability', 'preorder'=>'https://schema.org/PreOrder', 'out'=>'https://schema.org/OutOfStock'][$p['stock'] ?? 'in'] ?? 'https://schema.org/InStock',
    'seller'=>['@type'=>'Organization', 'name'=>$CFG['brand']],
    'shippingDetails'=>['@type'=>'OfferShippingDetails', 'shippingRate'=>['@type'=>'MonetaryAmount', 'value'=>number_format((float)($ship['price'] ?? 0), 2, '.', ''), 'currency'=>'USD'],
      'shippingDestination'=>['@type'=>'DefinedRegion', 'addressCountry'=>'US'],
      'deliveryTime'=>['@type'=>'ShippingDeliveryTime', 'handlingTime'=>['@type'=>'QuantitativeValue', 'minValue'=>0, 'maxValue'=>2, 'unitCode'=>'DAY'],
        'transitTime'=>['@type'=>'QuantitativeValue', 'minValue'=>$t1, 'maxValue'=>$t2, 'unitCode'=>'DAY']]]];
  return $d;
}

function item_list_schema($products){
  $items = [];
  foreach(array_slice($products, 0, 30) as $i=>$p) $items[] = ['@type'=>'ListItem', 'position'=>$i + 1, 'url'=>abs_url('products/'.$p['slug']), 'name'=>$p['name']];
  return $items ? ['@context'=>'https://schema.org', '@type'=>'ItemList', 'itemListElement'=>$items] : null;
}

function org_schema(){
  global $CFG;
  $o = ['@context'=>'https://schema.org', '@type'=>'OnlineStore', '@id'=>abs_url('#organization'), 'name'=>$CFG['brand'], 'url'=>abs_url(''),
    'logo'=>abs_url('assets/img/logo-512.png'), 'image'=>abs_url('assets/img/share.jpg'), 'email'=>$CFG['email'],
    'description'=>'Online gold bullion dealer selling 24K gold bars and gold coins with live spot pricing and insured US shipping.',
    'contactPoint'=>['@type'=>'ContactPoint', 'contactType'=>'customer service', 'email'=>$CFG['email'], 'areaServed'=>'US', 'availableLanguage'=>['English']]];
  if($CFG['legal_name']) $o['legalName'] = $CFG['legal_name'];
  if($CFG['phone']){ $o['telephone'] = $CFG['phone']; $o['contactPoint']['telephone'] = $CFG['phone']; }
  if($CFG['address']) $o['address'] = ['@type'=>'PostalAddress', 'streetAddress'=>preg_replace('/\s*\n\s*/', ', ', trim($CFG['address'])), 'addressCountry'=>'US'];
  return $o;
}

/* ---------------- sitemap ---------------- */
function sitemap(){
  global $STORE;
  header('Content-Type: application/xml; charset=utf-8');
  $today = date('Y-m-d');
  $urls = [['', $today, 'daily'], ['shop', $today, 'daily'], ['guides', $today, 'weekly']];
  foreach($STORE['categories'] as $c) if(cat_products($c['slug'])) $urls[] = [$c['slug'], $today, 'daily'];
  foreach(visible_products() as $p) $urls[] = ['products/'.$p['slug'], $today, 'daily'];
  foreach($STORE['tools'] as $t) $urls[] = [$t['slug'], $today, 'hourly'];
  foreach(guides_all() as $g) $urls[] = ['guides/'.$g['slug'], $g['updated'] ?? $g['date'] ?? $today, 'monthly'];
  foreach($STORE['pages'] as $pg) if($pg['slug'] !== 'track-order') $urls[] = [$pg['slug'], $STORE['settings']['policies_updated'] ?? false ? date('Y-m-d', $STORE['settings']['policies_updated']) : '2026-10-30', 'monthly'];
  echo '<?xml version="1.0" encoding="UTF-8"?>'."\n".'<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9" xmlns:xhtml="http://www.w3.org/1999/xhtml">'."\n";
  foreach($urls as [$u, $mod, $freq]){
    echo '<url><loc>'.h(abs_url($u)).'</loc><lastmod>'.h($mod).'</lastmod><changefreq>'.$freq.'</changefreq>';
    if($u === 'gold-price' || $u === 'ar/gold-price') echo '<xhtml:link rel="alternate" hreflang="en" href="'.h(abs_url('gold-price')).'"/><xhtml:link rel="alternate" hreflang="ar" href="'.h(abs_url('ar/gold-price')).'"/><xhtml:link rel="alternate" hreflang="x-default" href="'.h(abs_url('gold-price')).'"/>';
    echo "</url>\n";
  }
  echo '</urlset>';
}

/* =============================================================
   LAYOUT
   ============================================================= */
function render($body){
  global $META, $CFG, $STORE, $BASE;
  $brand = $CFG['brand'];
  $title = trim($META['title']) ?: $brand;
  if(empty($META['full_title']) && stripos($title, $brand) === false && mb_strlen($title) + mb_strlen($brand) < 62) $title .= ' | '.$brand;
  $desc = $META['desc'] ?: fill($CFG['home_seo_desc']);
  $canon = $META['canonical'];
  $img = $META['image'] ?: abs_url('assets/img/share.jpg');
  $crumbs = $META['crumbs'];
  if(!empty($GLOBALS['BODY_REPLACE'])) $body = strtr($body, $GLOBALS['BODY_REPLACE']);
  $schema = array_values(array_filter($META['schema']));
  $schema[] = org_schema();
  if($canon === abs_url('')) $schema[] = ['@context'=>'https://schema.org', '@type'=>'WebSite', 'name'=>$brand, 'url'=>abs_url(''), 'publisher'=>['@id'=>abs_url('#organization')],
    'potentialAction'=>['@type'=>'SearchAction', 'target'=>abs_url('search').'?q={search_term_string}', 'query-input'=>'required name=search_term_string']];
  if($crumbs){
    $items = [['@type'=>'ListItem', 'position'=>1, 'name'=>'Home', 'item'=>abs_url('')]];
    foreach($crumbs as $i=>[$name, $u]) $items[] = ['@type'=>'ListItem', 'position'=>$i + 2, 'name'=>$name, 'item'=>abs_url($u)];
    $schema[] = ['@context'=>'https://schema.org', '@type'=>'BreadcrumbList', 'itemListElement'=>$items];
  }
  $css = 'assets/style.css?v='.@filemtime(GB_ROOT.'/assets/style.css');
  $js = 'assets/app.js?v='.@filemtime(GB_ROOT.'/assets/app.js');
  $spot = spot();
  $nav_cats = array_values(array_filter($STORE['categories'], fn($c)=>!empty($c['nav']) && ($c['parent'] ?? '') === 'gold-bars' && (cat_products($c['slug']) || !$STORE['products'])));
  $help = array_values(array_filter($STORE['pages'], fn($p)=>($p['group'] ?? '') === 'help'));
  $legal = array_values(array_filter($STORE['pages'], fn($p)=>($p['group'] ?? '') === 'legal'));
  $company = array_values(array_filter($STORE['pages'], fn($p)=>($p['group'] ?? '') === 'company'));
  $count = cart_count();
  $flash = $_SESSION['flash'] ?? []; unset($_SESSION['flash']);
  $lang = $META['lang'];
  header('Content-Type: text/html; charset=utf-8');
  if(!empty($_COOKIE[session_name()]) || session_status() === PHP_SESSION_ACTIVE) header('Cache-Control: private, no-cache');
?><!DOCTYPE html>
<html lang="<?= h($lang === 'en' ? 'en-US' : $lang) ?>"<?= $lang === 'ar' ? ' dir="rtl"' : '' ?>>
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title><?= h($title) ?></title>
<meta name="description" content="<?= h($desc) ?>">
<meta name="robots" content="<?= h($META['robots']) ?><?= $META['robots'] === 'index,follow' ? ',max-image-preview:large,max-snippet:-1' : '' ?>">
<?php if($canon): ?><link rel="canonical" href="<?= h($canon) ?>">
<?php endif; foreach($META['alt'] as $hl=>$u): ?><link rel="alternate" hreflang="<?= h($hl) ?>" href="<?= h(abs_url($u)) ?>">
<?php endforeach; if($META['alt']): ?><link rel="alternate" hreflang="x-default" href="<?= h(abs_url($META['alt']['en'])) ?>">
<?php endif; ?>
<meta property="og:type" content="<?= h($META['type']) ?>">
<meta property="og:site_name" content="<?= h($brand) ?>">
<meta property="og:title" content="<?= h($META['title'] ?: $brand) ?>">
<meta property="og:description" content="<?= h($desc) ?>">
<?php if($canon): ?><meta property="og:url" content="<?= h($canon) ?>">
<?php endif; ?><meta property="og:image" content="<?= h($img) ?>">
<meta property="og:locale" content="<?= $lang === 'ar' ? 'ar_AR' : ($lang === 'es' ? 'es_US' : 'en_US') ?>">
<meta name="twitter:card" content="summary_large_image">
<?php if($CFG['gsc_verification']): ?><meta name="google-site-verification" content="<?= h(preg_replace('/^.*content="([^"]+)".*$/s', '$1', $CFG['gsc_verification'])) ?>">
<?php endif; if($CFG['bing_verification']): ?><meta name="msvalidate.01" content="<?= h(preg_replace('/^.*content="([^"]+)".*$/s', '$1', $CFG['bing_verification'])) ?>">
<?php endif; ?>
<meta name="theme-color" content="#0f1b2d">
<link rel="icon" href="<?= $BASE ?>favicon.ico" sizes="32x32">
<link rel="icon" href="<?= $BASE ?>assets/img/favicon.svg" type="image/svg+xml">
<link rel="apple-touch-icon" href="<?= $BASE ?>assets/img/apple-touch-icon.png">
<link rel="preconnect" href="https://fonts.googleapis.com"><link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Fraunces:opsz,wght@9..144,600;9..144,700&display=swap">
<link rel="stylesheet" href="<?= $BASE.$css ?>">
<?php foreach($schema as $s): ?><script type="application/ld+json"><?= json_encode($s, JSON_UNESCAPED_SLASHES|JSON_UNESCAPED_UNICODE|JSON_HEX_TAG) ?></script>
<?php endforeach; if($CFG['ga4_id'] && preg_match('/^G-[A-Z0-9]+$/', $CFG['ga4_id'])): ?>
<script async src="https://www.googletagmanager.com/gtag/js?id=<?= h($CFG['ga4_id']) ?>"></script><script>window.dataLayer=window.dataLayer||[];function gtag(){dataLayer.push(arguments)}gtag('js',new Date());gtag('config','<?= h($CFG['ga4_id']) ?>');</script>
<?php endif; ?>
</head>
<body>
<a class="skip" href="#main">Skip to content</a>
<div class="topbar" lang="en" dir="ltr"><div class="wrap topbar-in">
  <span class="ticker"><span class="dot<?= !empty($spot['live']) ? ' live' : '' ?>"></span>Gold <b><?= $spot['gold'] > 0 ? usd($spot['gold']) : '—' ?></b><?php if(isset($spot['gold_change_pct'])): ?> <span class="chg <?= $spot['gold_change_pct'] >= 0 ? 'up' : 'down' ?>"><?= $spot['gold_change_pct'] >= 0 ? '▲' : '▼' ?><?= number_format(abs($spot['gold_change_pct']), 2) ?>%</span><?php endif; ?> · Silver <b><?= $spot['silver'] > 0 ? usd($spot['silver']) : '—' ?></b></span>
  <span class="announce"><?= h(fill($CFG['announcement'])) ?></span>
</div></div>
<header class="header" lang="en" dir="ltr"><div class="wrap header-in">
  <a class="logo" href="<?= $BASE ?>" aria-label="<?= h($brand) ?> home"><img src="<?= $BASE ?>assets/img/logo-mark.svg" alt="" width="36" height="36"><span><?= h($brand) ?></span></a>
  <nav class="nav" aria-label="Main">
    <details class="menu"><summary>Menu</summary>
      <ul class="nav-list">
        <li class="has-sub"><a href="<?= link_to('gold-bars') ?>">Gold Bars</a><ul class="sub"><?php foreach($nav_cats as $c): ?><li><a href="<?= link_to($c['slug']) ?>"><?= h($c['name']) ?></a></li><?php endforeach; ?><li><a href="<?= link_to('gold-bars') ?>">All gold bars</a></li></ul></li>
        <li><a href="<?= link_to('gold-coins') ?>">Gold Coins</a></li>
        <li class="has-sub"><a href="<?= link_to('gold-price') ?>">Gold Price</a><ul class="sub"><?php foreach($STORE['tools'] as $t) if(($t['kind'] ?? '') !== 'ar'): ?><li><a href="<?= link_to($t['slug']) ?>"><?= h($t['nav'] ?? $t['h1']) ?></a></li><?php endif; ?></ul></li>
        <li><a href="<?= link_to('guides') ?>">Guides</a></li>
        <li><a href="<?= link_to('about-us') ?>">About</a></li>
        <li><a href="<?= link_to('contact') ?>">Contact</a></li>
      </ul>
    </details>
  </nav>
  <form class="search" action="<?= link_to('search') ?>" role="search"><input name="q" placeholder="Search" aria-label="Search products and guides"></form>
  <a class="cart-link" href="<?= link_to('cart') ?>" aria-label="Cart, <?= $count ?> items"><svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><path d="M6 6h15l-1.5 9h-12z"/><path d="M6 6 5 3H2"/><circle cx="9" cy="20" r="1.5"/><circle cx="18" cy="20" r="1.5"/></svg><?php if($count): ?><span class="badge"><?= $count ?></span><?php endif; ?></a>
</div></header>
<?php if($crumbs): ?>
<nav class="crumbs wrap" aria-label="Breadcrumb" lang="en" dir="ltr"><ol><li><a href="<?= $BASE ?>">Home</a></li><?php foreach($crumbs as $i=>[$name, $u]): ?><li><?= $i === count($crumbs) - 1 ? '<span aria-current="page">'.h($name).'</span>' : '<a href="'.link_to($u).'">'.h($name).'</a>' ?></li><?php endforeach; ?></ol></nav>
<?php endif; ?>
<?php if($flash): ?><div class="wrap flashes"><?php foreach($flash as [$t, $m]): ?><p class="flash flash-<?= h($t) ?>"><?= h($m) ?></p><?php endforeach; ?></div><?php endif; ?>
<main id="main"><?= $body ?></main>
<footer class="footer" lang="en" dir="ltr">
  <div class="wrap footer-in">
    <div class="f-brand"><a class="logo logo-light" href="<?= $BASE ?>"><img src="<?= $BASE ?>assets/img/logo-mark.svg" alt="" width="32" height="32"><span><?= h($brand) ?></span></a>
      <p>Investment-grade gold bullion with live spot pricing, locked prices and fully insured US delivery.</p>
      <p><a href="mailto:<?= h($CFG['email']) ?>"><?= h($CFG['email']) ?></a><?php if($CFG['phone']): ?><br><a href="tel:<?= h(preg_replace('/[^\d+]/', '', $CFG['phone'])) ?>"><?= h($CFG['phone']) ?></a><?php endif; ?><?php if($CFG['address']): ?><br><?= nl2br(h($CFG['address'])) ?><?php endif; ?></p>
      <p class="small">We accept: <?= h(implode(' · ', array_column(array_filter($STORE['payments'], fn($m)=>!empty($m['enabled'])), 'label'))) ?></p>
    </div>
    <div><h2>Shop</h2><ul><li><a href="<?= link_to('shop') ?>">All gold bullion</a></li><li><a href="<?= link_to('gold-bars') ?>">Gold bars</a></li><?php foreach($nav_cats as $c): ?><li><a href="<?= link_to($c['slug']) ?>"><?= h($c['name']) ?></a></li><?php endforeach; ?><li><a href="<?= link_to('gold-coins') ?>">Gold coins</a></li></ul></div>
    <div><h2>Gold prices</h2><ul><?php foreach($STORE['tools'] as $t): ?><li><a href="<?= link_to($t['slug']) ?>"<?= ($t['lang'] ?? '') === 'ar' ? ' lang="ar" hreflang="ar"' : '' ?>><?= h($t['nav'] ?? $t['h1']) ?></a></li><?php endforeach; ?></ul></div>
    <div><h2>Help</h2><ul><?php foreach($help as $p): ?><li><a href="<?= link_to($p['slug']) ?>"><?= h($p['title']) ?></a></li><?php endforeach; ?><li><a href="<?= link_to('guides') ?>">Guides</a></li></ul>
      <h2>Company</h2><ul><?php foreach($company as $p): ?><li><a href="<?= link_to($p['slug']) ?>"><?= h($p['title']) ?></a></li><?php endforeach; ?></ul></div>
    <div><h2>Legal</h2><ul><?php foreach($legal as $p): ?><li><a href="<?= link_to($p['slug']) ?>"><?= h($p['title']) ?></a></li><?php endforeach; ?></ul></div>
  </div>
  <div class="wrap footer-base"><p>© <?= date('Y') ?> <?= h($CFG['legal_name'] ?: $brand) ?>. All rights reserved.</p>
    <p class="small">Prices of precious metals can go down as well as up. Spot prices are indicative and may be delayed. Nothing on this site is financial advice.</p></div>
</footer>
<script src="<?= $BASE.$js ?>" defer></script>
<?php if(trim($CFG['chat_code'])): ?><script>window.addEventListener('load',function(){setTimeout(function(){var d=document.createElement('div');d.innerHTML=<?= json_encode($CFG['chat_code'], JSON_HEX_TAG) ?>;d.querySelectorAll('script').forEach(function(o){var s=document.createElement('script');[].forEach.call(o.attributes,function(a){s.setAttribute(a.name,a.value)});s.text=o.text;document.body.appendChild(s)})},1500)});</script><?php endif; ?>
</body>
</html>
<?php }
