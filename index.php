<?php
/* =============================================================
   FUDAKURA — wholesale Japanese Pokémon TCG storefront. PHP 7.4+.

   Day-to-day editing — products, prices, photos, shipping rates,
   payment methods, business details, FAQ, orders — is done in
   admin.php. You should not need to edit this file. See README.md.
   ============================================================= */

define('FK_ROOT', __DIR__);
require FK_ROOT.'/inc/store.php';
start_session();
error_reporting(E_ALL & ~E_NOTICE & ~E_WARNING);

/* ---------------- DATA (edited in admin.php) ---------------- */
$STORE      = store_load();
$CONFIG     = $STORE['settings'];
$CURRENCIES = $STORE['currencies'];
$CATEGORIES = $STORE['categories'];
$PRODUCTS   = array_values(array_filter($STORE['products'], fn($p)=>empty($p['hidden']) && isset($CATEGORIES[$p['cat']])));
$PAYMENTS   = array_filter($STORE['payments'], fn($m)=>!empty($m['enabled']));
$COUNTRIES  = $STORE['countries'];
$MIN_ORDER  = (float)$CONFIG['min_order_usd'];
$SERIES     = $STORE['series'] ?? [];
$COLLECTIONS= $STORE['collections'] ?? [];
$GUIDES     = $STORE['guides'] ?? [];

/* ---------------- HELPERS ---------------- */
function product($id){ global $PRODUCTS; foreach($PRODUCTS as $p){ if($p['id']===$id) return $p; } return null; }

function unit_price($p, $qty){
  $price = $p['ladder'][0][1];
  foreach($p['ladder'] as $t){ if($qty >= $t[0]) $price = $t[1]; }
  return $price;
}

function can_order($p){ return $p['status'] !== 'soldout'; }

/* snap a requested quantity to the MOQ and selling step; 0 = cannot be ordered */
function fit_qty($p, $qty){
  if(!can_order($p)) return 0;
  return min(99999, max($p['moq'], (int)round($qty / $p['step']) * $p['step']));
}

function payment_ok($key, $country){
  global $PAYMENTS;
  if(!isset($PAYMENTS[$key])) return false;
  $c = $PAYMENTS[$key]['countries'];
  return $c==='*' || in_array($country, $c, true);
}

function flash($msg){ $_SESSION['flash'][] = $msg; }

function cur_code(){
  global $CURRENCIES;
  $c = $_GET['cur'] ?? $_SESSION['cur'] ?? 'USD';
  if(!is_string($c) || !isset($CURRENCIES[$c])) $c = 'USD';
  $_SESSION['cur'] = $c;
  return $c;
}

function money($usd){
  global $CURRENCIES;
  $c = cur_code(); $m = $CURRENCIES[$c];
  return $m['sym'] . number_format($usd * $m['rate'], $m['dec']);
}

function cart(){ return $_SESSION['cart'] ?? []; }
function cart_units(){ $n=0; foreach(cart() as $q) $n += $q; return $n; }
function cart_lines(){
  $lines = [];
  foreach(cart() as $id=>$qty){
    $p = product($id); if(!$p) continue;
    $u = unit_price($p,$qty);
    $lines[] = ['p'=>$p,'qty'=>$qty,'unit'=>$u,'total'=>round($u*$qty, 2),'saved'=>round(($p['ladder'][0][1]-$u)*$qty, 2)];
  }
  return $lines;
}
function cart_total(){ $t=0; foreach(cart_lines() as $l) $t += $l['total']; return round($t, 2); }
function cart_saved(){ $t=0; foreach(cart_lines() as $l) $t += $l['saved']; return round($t, 2); }
function cart_weight(){ $kg=0; foreach(cart_lines() as $l) $kg += (float)($l['p']['weight'] ?? 0) * $l['qty']; return $kg; }

/* ---------------- URLS ----------------
   Clean addresses (Admin → Settings; needs .htaccess support, i.e. Apache or LiteSpeed):
     /shop  /booster-boxes  /products/{id}  /sets  /sets/{set or series}  /cards/{collection}  /guides/{guide}  /cart …
   Otherwise index.php?p=…  Links are written relative to <base href>, so the shop also works in a subfolder. */
$BASE = rtrim(str_replace('\\', '/', dirname($_SERVER['SCRIPT_NAME'] ?? '/index.php')), '/').'/';
const PAGE_PATHS = ['cart'=>'cart', 'checkout'=>'checkout', 'received'=>'order-received', 'how'=>'how-it-works',
                    'shipping'=>'shipping', 'payment'=>'payment-methods', 'faq'=>'faq', 'contact'=>'contact',
                    'sets'=>'sets', 'guides'=>'guides', 'sitemap'=>'sitemap.xml'];

function cat_slug($key){ global $CATEGORIES; return ($CATEGORIES[$key]['slug'] ?? '') ?: slugify($CATEGORIES[$key]['label'] ?? $key); }

function url($p, $extra=[]){
  global $CONFIG;
  if(empty($CONFIG['pretty_urls'])) return $p === 'home' && !$extra ? './' : 'index.php?'.http_build_query(['p'=>$p] + $extra);
  $pull = function($k) use(&$extra){ $v = (string)($extra[$k] ?? ''); unset($extra[$k]); return $v; };
  switch($p){
    case 'home':       $path = ''; break;
    case 'catalog':    $c = $pull('cat'); $path = $c !== '' ? cat_slug($c) : 'shop'; break;
    case 'product':    $path = 'products/'.rawurlencode($pull('id')); break;
    case 'set':
    case 'series':     $path = 'sets/'.rawurlencode($pull('s')); break;
    case 'collection': $path = 'cards/'.rawurlencode($pull('c')); break;
    case 'guide':      $path = 'guides/'.rawurlencode($pull('g')); break;
    default:           $paths = PAGE_PATHS; $path = $paths[$p] ?? '';
  }
  return ($path === '' ? './' : $path).($extra ? '?'.http_build_query($extra) : '');
}

/* absolute link for canonical tags, sitemaps and structured data */
function abs_url($p, $extra=[]){ global $CONFIG; $u = url($p, $extra); return rtrim($CONFIG['domain'], '/').'/'.($u === './' ? '' : $u); }

function go_to($p, $extra=[]){ global $BASE; $u = url($p, $extra); header('Location: '.$BASE.($u === './' ? '' : $u)); exit; }

/* request path → page, for clean addresses */
function route_from_path(){
  global $BASE, $CATEGORIES;
  $path = rawurldecode((string)parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH));
  if(strpos($path, $BASE) === 0) $path = substr($path, strlen($BASE));
  $path = trim($path, '/');
  if($path === '' || $path === 'index.php') return null;
  $seg = explode('/', $path);
  if(count($seg) === 1){
    if($path === 'shop') return ['p'=>'catalog'];
    foreach($CATEGORIES as $k=>$c){ if(cat_slug($k) === $path) return ['p'=>'catalog', 'cat'=>$k]; }
    $pages = array_flip(PAGE_PATHS);
    if(isset($pages[$path])) return ['p'=>$pages[$path]];
  } elseif(count($seg) === 2){
    [$a, $b] = $seg;
    if($a === 'products') return ['p'=>'product', 'id'=>$b];
    if($a === 'sets')     return ['p'=>series_by_slug($b) ? 'series' : 'set', 's'=>$b];
    if($a === 'cards')    return ['p'=>'collection', 'c'=>$b];
    if($a === 'guides')   return ['p'=>'guide', 'g'=>$b];
  }
  return ['p'=>'notfound'];
}

/* ---------------- sets, series, collections, guides ---------------- */
/* every set that has products in the shop, in admin order, with its page settings */
function sets_all(){
  global $PRODUCTS, $STORE;
  $names = [];
  foreach($PRODUCTS as $p){ if($p['set'] !== '') $names[$p['set']] = true; }
  $order = array_merge(array_keys($STORE['sets'] ?? []), array_keys($names));
  $out = [];
  foreach($order as $name){
    if(!isset($names[$name]) || isset($out[$name])) continue;
    $out[$name] = ($STORE['sets'][$name] ?? []) + ['name'=>$name, 'slug'=>'', 'series'=>'', 'code'=>'', 'intro'=>'', 'seo_title'=>'', 'seo_desc'=>''];
    $out[$name]['name'] = $name;
    if($out[$name]['slug'] === '') $out[$name]['slug'] = slugify($name);
  }
  return $out;
}
function set_by_slug($slug){ foreach(sets_all() as $s){ if($s['slug'] === $slug) return $s; } return null; }
function set_of($name){ $all = sets_all(); return $all[$name] ?? null; }
function series_by_slug($slug){ global $SERIES; foreach($SERIES as $k=>$s){ if(($s['slug'] ?? '') === $slug) return $s + ['key'=>$k]; } return null; }
function series_sets($key){ return array_filter(sets_all(), fn($s)=>$s['series'] === $key); }
function set_products($name){ global $PRODUCTS; return array_values(array_filter($PRODUCTS, fn($p)=>$p['set'] === $name)); }

function collection_by_slug($slug){ global $COLLECTIONS; foreach($COLLECTIONS as $c){ if(($c['slug'] ?? '') === $slug) return $c; } return null; }
function collection_products($c){
  global $PRODUCTS;
  $terms = array_filter(array_map('trim', explode(',', strtolower($c['match'] ?? ''))));
  $ids = $c['ids'] ?? []; $cond = $c['cond'] ?? '';
  return array_values(array_filter($PRODUCTS, function($p) use($terms, $ids, $cond){
    if(in_array($p['id'], $ids, true)) return true;
    if($cond !== '' && ($p['cond'] ?? 'Sealed') !== $cond) return false;
    if(!$terms) return $cond !== '';
    foreach($terms as $t){ if(strpos(strtolower($p['name']), $t) !== false) return true; }
    return false;
  }));
}
function guide_by_slug($slug){ global $GUIDES; foreach($GUIDES as $g){ if(($g['slug'] ?? '') === $slug) return $g; } return null; }

/* ---------------- admin-written text ----------------
   blank line = paragraph, "## " / "### " heading, "- " bullet, **bold**, [text](link) — see inc/content.php */
function link_target($t){
  if(preg_match('#^(https?://|mailto:)#i', $t)) return $t;
  if(!preg_match('/^([a-z]+):(.+)$/', $t, $m)) return null;
  $pages = ['home'=>'home', 'shop'=>'catalog', 'sets'=>'sets', 'guides'=>'guides', 'faq'=>'faq', 'shipping'=>'shipping',
            'payment'=>'payment', 'how'=>'how', 'contact'=>'contact'];
  switch($m[1]){
    case 'product':  return url('product', ['id'=>$m[2]]);
    case 'category': return url('catalog', ['cat'=>$m[2]]);
    case 'set':      return url(series_by_slug($m[2]) ? 'series' : 'set', ['s'=>$m[2]]);
    case 'cards':    return url('collection', ['c'=>$m[2]]);
    case 'guide':    return url('guide', ['g'=>$m[2]]);
    case 'page':     return isset($pages[$m[2]]) ? url($pages[$m[2]]) : null;
  }
  return null;
}
function rich_inline($s){
  $bold = fn($x)=>preg_replace('/\*\*(.+?)\*\*/u', '<strong>$1</strong>', $x);
  $out = ''; $pos = 0;
  preg_match_all('/\[([^\]]+)\]\(([^)\s]+)\)/u', $s, $links, PREG_OFFSET_CAPTURE | PREG_SET_ORDER);
  foreach($links as $l){
    $out .= $bold(h(substr($s, $pos, $l[0][1] - $pos)));
    $href = link_target($l[2][0]);
    $out .= $href !== null ? '<a href="'.h($href).'">'.$bold(h($l[1][0])).'</a>' : $bold(h($l[1][0]));
    $pos = $l[0][1] + strlen($l[0][0]);
  }
  return $out.$bold(h(substr($s, $pos)));
}
function rich($text){
  $html = ''; $para = []; $list = false;
  $flush = function() use(&$html, &$para, &$list){
    if($para){ $html .= '<p>'.rich_inline(implode(' ', $para)).'</p>'; $para = []; }
    if($list){ $html .= '</ul>'; $list = false; }
  };
  foreach(explode("\n", str_replace("\r", '', fill((string)$text))) as $line){
    $line = trim($line);
    if($line === ''){ $flush(); continue; }
    if(preg_match('/^(#{2,3})\s+(.+)$/', $line, $m)){ $flush(); $t = strlen($m[1]); $html .= "<h$t>".rich_inline($m[2])."</h$t>"; continue; }
    if(preg_match('/^[-*]\s+(.+)$/', $line, $m)){
      if($para){ $html .= '<p>'.rich_inline(implode(' ', $para)).'</p>'; $para = []; }
      if(!$list){ $html .= '<ul>'; $list = true; }
      $html .= '<li>'.rich_inline($m[1]).'</li>'; continue;
    }
    if($list){ $html .= '</ul>'; $list = false; }
    $para[] = $line;
  }
  $flush();
  return $html;
}
/* plain text of admin-written text, for meta descriptions */
function plain($text, $len=155){
  $t = trim(preg_replace('/\s+/u', ' ', preg_replace(['/\[([^\]]+)\]\([^)]*\)/', '/\*\*|^#+\s*/m'], ['$1', ''], fill((string)$text))));
  if(!preg_match('/^.{'.($len + 1).'}/us', $t)) return $t;
  $cut = preg_match('/^(.{1,'.$len.'})\s/us', $t, $m) ? $m[1] : preg_replace('/^(.{'.$len.'}).*$/us', '$1', $t);
  return rtrim($cut, ' ,;:-').'…';
}

/* admin-editable text: fills {reply_hours} {hold_hours} {countries} {min_order} */
function fill($text){
  global $CONFIG, $COUNTRIES, $MIN_ORDER;
  return strtr($text, ['{reply_hours}'=>(int)$CONFIG['reply_hours'], '{hold_hours}'=>(int)$CONFIG['hold_hours'],
                       '{countries}'=>count($COUNTRIES), '{min_order}'=>money($MIN_ORDER)]);
}

/* =?UTF-8?B?…?= so names like “Pokémon” and dashes survive in subjects and sender names */
function mail_header($s){ return preg_match('/[^\x20-\x7E]/', $s) ? '=?UTF-8?B?'.base64_encode($s).'?=' : $s; }

/* UTF-8 plain-text mail from the shop address. The envelope sender (-f) is set to the same
   address so SPF checks line up; hosts that refuse -f get a second try without it. */
function shop_mail($to, $subject, $body, $reply_to=''){
  global $CONFIG;
  $from = filter_var($CONFIG['email'], FILTER_VALIDATE_EMAIL) ? $CONFIG['email'] : $CONFIG['order_email'];
  $headers = implode("\r\n", array_filter([
    'From: '.mail_header($CONFIG['brand']).' <'.$from.'>',
    $reply_to ? 'Reply-To: '.$reply_to : '',
    'MIME-Version: 1.0',
    'Content-Type: text/plain; charset=UTF-8',
    'Content-Transfer-Encoding: quoted-printable',
  ]));
  $subject = mail_header($subject);
  $body = quoted_printable_encode($body);
  return @mail($to, $subject, $body, $headers, '-f'.$from) || @mail($to, $subject, $body, $headers);
}

/* returns which emails went out, so a failure shows up in the admin instead of vanishing */
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
      $l['name'].' ('.$l['sku'].')', $l['qty'], $l['unit'], $l['total']);
  }
  $body .= "\n  GOODS:    {$order['goods']}\n";
  $body .= "  SHIPPING: {$order['shipping']} ({$order['ship_zone']})\n";
  $body .= "  TOTAL:    {$order['total']} ({$order['currency']})\n";
  $body .= "  Import duty and taxes are not included.\n\n";
  if($order['notes']) $body .= "NOTES\n  {$order['notes']}\n\n";
  $body .= "Submitted: {$order['time']}\n";

  $to_shop = shop_mail($CONFIG['order_email'], "New order {$order['ref']} — {$order['payment_label']}", $body, $order['email']);

  /* customer confirmation */
  $c  = "Thank you — we have your order.\n\n";
  $c .= "Order reference: {$order['ref']}\n";
  $c .= "Goods: {$order['goods']}\n";
  $c .= "Shipping: {$order['shipping']}\n";
  $c .= "Order total: {$order['total']} ({$order['currency']})\n";
  $c .= "Payment method selected: {$order['payment_label']}\n\n";
  $c .= "WHAT HAPPENS NEXT\n";
  $c .= "Your stock is reserved for {$CONFIG['hold_hours']} hours. We will contact you\n";
  $c .= "by email or text within {$CONFIG['reply_hours']} hours with the payment details\n";
  $c .= "for the method you selected, together with your invoice.\n\n";
  $c .= "Quote {$order['ref']} on your payment so we can match it to your order.\n";
  $c .= "Once payment clears we dispatch within {$CONFIG['hold_hours']} hours from Japan\n";
  $c .= "with tracking.\n\n";
  $c .= "Questions: {$CONFIG['email']}\n";
  $c .= "{$CONFIG['legal_name']} — {$CONFIG['address']}\n";
  $to_customer = shop_mail($order['email'], "Order {$order['ref']} received — {$CONFIG['brand']}", $c, $CONFIG['email']);
  return ['shop'=>$to_shop, 'customer'=>$to_customer];
}

/* ---------------- ACTIONS (POST / redirect) ---------------- */
/* drop cart lines for products since removed, hidden or sold out; re-snap quantities to MOQ/step */
foreach(cart() as $id=>$qty){
  $p = product($id);
  $fit = $p ? fit_qty($p, $qty) : 0;
  if($fit === $qty) continue;
  if($fit) $_SESSION['cart'][$id] = $fit;
  else { unset($_SESSION['cart'][$id]); flash(($p ? $p['name'] : 'A product').' is no longer available and was removed from your order.'); }
}

$errors = [];
if($_SERVER['REQUEST_METHOD'] === 'POST'){
  $action = $_POST['action'] ?? '';

  if($action === 'add'){
    $p = product($_POST['id'] ?? '');
    if($p){
      $qty = fit_qty($p, (int)($_POST['qty'] ?? 0) ?: $p['moq']);
      if($qty) $_SESSION['cart'][$p['id']] = $qty;
      else     flash("{$p['name']} is sold out.");
    }
    go_to('cart');
  }

  if($action === 'update'){
    foreach(($_POST['qty'] ?? []) as $id=>$q){
      $p = product($id); if(!$p) continue;
      $q = (int)$q;
      if($q <= 0 || !can_order($p)){ unset($_SESSION['cart'][$id]); continue; }
      $_SESSION['cart'][$id] = fit_qty($p, $q);
    }
    go_to('cart');
  }

  if($action === 'remove'){
    unset($_SESSION['cart'][$_POST['id'] ?? '']);
    go_to('cart');
  }

  if($action === 'order'){
    $f = [];
    foreach(['name','company','email','phone','country','address1','address2','city','region','postcode','notes','payment'] as $k){
      $f[$k] = trim(is_string($_POST[$k] ?? null) ? $_POST[$k] : '');
    }
    /* bot filter: honeypot left empty, token from this session's checkout page, not submitted instantly */
    $bot = ($_POST['website'] ?? '') !== ''
        || empty($_SESSION['co_token'])
        || !hash_equals($_SESSION['co_token'], is_string($_POST['token'] ?? null) ? $_POST['token'] : '')
        || time() - ($_SESSION['co_time'] ?? time()) < 3;
    if($bot)                                             $errors[] = 'We could not submit your order. Please check the form and place it again.';
    if(!cart_lines())                                    $errors[] = 'Your order is empty.';
    if($f['name'] === '')                                $errors[] = 'Enter the name the order ships to.';
    if(!filter_var($f['email'], FILTER_VALIDATE_EMAIL))  $errors[] = 'Enter a valid email address.';
    if($f['phone'] === '')                               $errors[] = 'Enter a phone number — we send payment details by text.';
    if(!isset($COUNTRIES[$f['country']]))                $errors[] = 'Select your country.';
    if($f['address1'] === '')                            $errors[] = 'Enter a street address.';
    if($f['city'] === '')                                $errors[] = 'Enter a city.';
    if(!isset($PAYMENTS[$f['payment']]))                 $errors[] = 'Choose how you want to pay.';
    elseif(isset($COUNTRIES[$f['country']]) && !payment_ok($f['payment'], $f['country']))
      $errors[] = $PAYMENTS[$f['payment']]['label'].' is not available for '.$COUNTRIES[$f['country']].'. Choose another payment method.';
    if(empty($_POST['agree']))                           $errors[] = 'Confirm you understand payment details follow by email or text.';

    /* minimum order value counts goods plus shipping */
    if(isset($COUNTRIES[$f['country']]) && cart_lines()){
      $ship  = shipping_usd($STORE, $f['country'], cart_weight());
      $grand = round(cart_total() + $ship, 2);
      if($grand < $MIN_ORDER)
        $errors[] = 'The minimum order is '.money($MIN_ORDER).' including shipping. Your total is '.money($grand).', so add '.money($MIN_ORDER - $grand).' more to place this order.';
    }

    if(!$errors){
      $lines = [];
      foreach(cart_lines() as $l){
        $lines[] = ['id'=>$l['p']['id'], 'name'=>$l['p']['name'], 'sku'=>$l['p']['sku'], 'qty'=>$l['qty'],
                    'unit_usd'=>$l['unit'], 'total_usd'=>$l['total'],
                    'unit'=>money($l['unit']), 'total'=>money($l['total'])];
      }
      $goods = cart_total();
      $order = $f + [
        'ref'           => new_order_ref(),
        'status'        => 'new',
        'country_name'  => $COUNTRIES[$f['country']],
        'payment_label' => $PAYMENTS[$f['payment']]['label'],
        'ship_zone'     => ship_zone($STORE, $f['country'])['name'],
        'lines'         => $lines,
        'goods_usd'     => $goods,
        'shipping_usd'  => $ship,
        'total_usd'     => round($goods + $ship, 2),
        'goods'         => money($goods),
        'shipping'      => money($ship),
        'total'         => money($goods + $ship),
        'currency'      => cur_code(),
        'time'          => gmdate('Y-m-d H:i').' UTC',
        'time_iso'      => gmdate('c'),
        'ip'            => $_SERVER['REMOTE_ADDR'] ?? '',
      ];
      if(!order_save($order)) error_log("fudakura: could not save order {$order['ref']} to data/orders");
      $sent = send_order_mail($order);
      $order['mail_shop'] = $sent['shop']; $order['mail_customer'] = $sent['customer'];
      order_save($order);
      if(!$sent['shop']) error_log("fudakura: order email for {$order['ref']} to {$CONFIG['order_email']} failed");
      $_SESSION['last_order'] = $order;
      $_SESSION['cart'] = [];
      go_to('received');
    }
    $form = $f;
  }
}

/* ---------------- ROUTE ---------------- */
$from_path = isset($_GET['p']) ? null : route_from_path();
if($from_path) $_GET = $from_path + $_GET;
$page = $_GET['p'] ?? 'home';
if(!is_string($page)) $page = 'home';
if($_SERVER['REQUEST_METHOD']==='POST' && ($_POST['action']??'')==='order' && $errors) $page = 'checkout';
$PAGES = ['home','catalog','product','sets','set','series','collection','guides','guide','cart','checkout','received',
          'how','shipping','payment','faq','contact','sitemap','notfound'];
if(!in_array($page, $PAGES, true)) $page = 'notfound';

$gs    = fn($k)=>is_string($_GET[$k] ?? null) ? trim($_GET[$k]) : '';
$cat   = $gs('cat');
if(!isset($CATEGORIES[$cat])) $cat = '';
$q     = $gs('q');
/* catalogue filters (prices in the shopper's currency) */
$f_set = $gs('set'); $f_cond = $gs('cond'); $f_avail = $gs('avail'); $f_sort = $gs('sort');
$f_min = is_numeric($gs('min')) ? (float)$gs('min') : null;
$f_max = is_numeric($gs('max')) ? (float)$gs('max') : null;
$filtered = $f_set !== '' || $f_cond !== '' || $f_avail !== '' || $f_sort !== '' || $f_min !== null || $f_max !== null || $q !== '';

$prod = $set = $series = $coll = $guide = null;
if($page === 'product')    $prod   = product($gs('id'));
if($page === 'set')        $set    = set_by_slug($gs('s'));
if($page === 'series')     $series = series_by_slug($gs('s'));
if($page === 'collection') $coll   = collection_by_slug($gs('c'));
if($page === 'guide')      $guide  = guide_by_slug($gs('g'));
if(($page === 'product' && !$prod) || ($page === 'set' && !$set) || ($page === 'series' && !$series)
   || ($page === 'collection' && !$coll) || ($page === 'guide' && !$guide)) $page = 'notfound';
if($page === 'notfound') http_response_code(404);

/* with clean addresses on, old index.php?p=… links (and /shop?cat=…) move permanently to the clean address */
if(!empty($CONFIG['pretty_urls']) && $_SERVER['REQUEST_METHOD'] === 'GET' && !in_array($page, ['notfound','sitemap'], true)
   && ((!$from_path && isset($_GET['p'])) || ($page === 'catalog' && $from_path && !isset($from_path['cat']) && $cat !== ''))){
  $extra = $_GET; unset($extra['p']);
  $u = url($page, $extra);
  header('Location: '.$BASE.($u === './' ? '' : $u), true, 301); exit;
}

/* ---------------- sitemap.xml (index.php?p=sitemap) ---------------- */
if($page === 'sitemap'){
  $abs_img = fn($f)=>rtrim($CONFIG['domain'], '/').'/'.$f;
  $urls = [[abs_url('home'), []], [abs_url('catalog'), []]];
  foreach($CATEGORIES as $k=>$c) $urls[] = [abs_url('catalog', ['cat'=>$k]), []];
  foreach($PRODUCTS as $p) $urls[] = [abs_url('product', ['id'=>$p['id']]), array_map($abs_img, photos($p['id']))];
  $urls[] = [abs_url('sets'), []];
  foreach($SERIES as $k=>$sr){ if(series_sets($k)) $urls[] = [abs_url('series', ['s'=>$sr['slug']]), []]; }
  foreach(sets_all() as $st) $urls[] = [abs_url('set', ['s'=>$st['slug']]), []];
  foreach($COLLECTIONS as $c){ if(collection_products($c)) $urls[] = [abs_url('collection', ['c'=>$c['slug']]), []]; }
  if($GUIDES) $urls[] = [abs_url('guides'), []];
  foreach($GUIDES as $g) $urls[] = [abs_url('guide', ['g'=>$g['slug']]), []];
  foreach(['how','shipping','payment','faq','contact'] as $pg) $urls[] = [abs_url($pg), []];
  $x = fn($v)=>htmlspecialchars($v, ENT_XML1|ENT_QUOTES, 'UTF-8');
  $mod = gmdate('Y-m-d', @filemtime(data_file('store')) ?: time());
  header('Content-Type: application/xml; charset=utf-8');
  echo '<?xml version="1.0" encoding="UTF-8"?>'."\n".'<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9" xmlns:image="http://www.google.com/schemas/sitemap-image/1.1">'."\n";
  foreach($urls as [$loc, $imgs]){
    echo '  <url><loc>'.$x($loc).'</loc><lastmod>'.$mod.'</lastmod>';
    foreach($imgs as $img) echo '<image:image><image:loc>'.$x($img).'</image:loc></image:image>';
    echo "</url>\n";
  }
  echo "</urlset>\n";
  exit;
}

/* ---------------- titles, descriptions, headings, breadcrumbs (SEO) ---------------- */
$cinfo = $cat ? $CATEGORIES[$cat] : null;
$h1 = ''; $crumbs = [['Home', 'home', []]];
$fixed = [
  'cart'     => ['Your order', 'Your order'],
  'checkout' => ['Checkout', 'Checkout'],
  'received' => ['Order received', ''],
  'how'      => ['How ordering works — Japanese Pokémon cards to the USA', 'How ordering works'],
  'shipping' => ['Shipping Japanese Pokémon cards to the USA', 'Shipping, customs and delivery'],
  'payment'  => ['Payment methods', 'Payment methods'],
  'faq'      => ['FAQ — Buying Japanese Pokémon cards', 'Frequently asked questions'],
  'contact'  => ['Contact', 'Contact'],
  'notfound' => ['Page not found', 'Page not found'],
];
$page_desc = '';
switch($page){
  case 'home':
    $page_title = ($CONFIG['home_seo_title'] ?? '') ?: 'Japanese Pokémon Cards — Booster Boxes & Singles';
    $page_desc  = ($CONFIG['home_seo_desc'] ?? '') ?: 'Authentic Japanese Pokémon cards shipped from Japan to the USA: sealed booster boxes, ETBs, rare singles and PSA graded cards, with bulk pricing published.';
    $crumbs = [];
    break;
  case 'catalog':
    $crumbs[] = ['Shop', $cinfo ? 'catalog' : '', []];
    if($cinfo){
      $crumbs[] = [$cinfo['label'], '', []];
      $page_title = ($cinfo['seo_title'] ?? '') ?: $cinfo['label'].' — Japanese Pokémon TCG';
      $page_desc  = ($cinfo['seo_desc'] ?? '') ?: plain($cinfo['blurb']);
      $h1 = ($cinfo['h1'] ?? '') ?: $cinfo['label'];
    } else {
      $page_title = 'Shop Japanese Pokémon Cards — All Products';
      $page_desc  = 'Every Japanese Pokémon product we stock: sealed booster boxes, Elite Trainer Boxes, collection boxes, rare singles, PSA graded cards and accessories, shipped to the USA.';
      $h1 = 'Shop Japanese Pokémon cards';
    }
    if($q !== '') $h1 = 'Results for “'.$q.'”';
    elseif(!$cinfo && $f_set !== '') $h1 = $f_set;
    break;
  case 'product':
    $pset = $prod['set'] !== '' ? set_of($prod['set']) : null;
    $crumbs[] = [$CATEGORIES[$prod['cat']]['label'], 'catalog', ['cat'=>$prod['cat']]];
    if($pset) $crumbs[] = [$pset['name'], 'set', ['s'=>$pset['slug']]];
    $crumbs[] = [$prod['name'], '', []];
    $auto = $prod['name'];
    if(stripos($auto, 'japanese') === false && strlen($auto) < 34) $auto .= ' — Japanese Pokémon TCG';
    $page_title = ($prod['seo_title'] ?? '') ?: $auto;
    $page_desc  = ($prod['seo_desc'] ?? '') ?: plain($prod['desc']);
    break;
  case 'sets':
    $crumbs[] = ['Sets', '', []];
    $page_title = 'Pokémon Card Sets — Japanese Mega Evolution & Scarlet & Violet';
    $page_desc  = 'Every Japanese Pokémon card set we stock, from the Mega Evolution series back to Scarlet & Violet favourites like 151 and Terastal Festival ex.';
    $h1 = 'Pokémon card sets';
    break;
  case 'series':
    $crumbs[] = ['Sets', 'sets', []]; $crumbs[] = [$series['name'], '', []];
    $page_title = ($series['seo_title'] ?? '') ?: 'Pokémon TCG '.$series['name'].' Sets (Japanese)';
    $page_desc  = ($series['seo_desc'] ?? '') ?: plain($series['intro'] ?? '');
    $h1 = ($series['h1'] ?? '') ?: 'Pokémon TCG '.$series['name'].' sets';
    break;
  case 'set':
    $sser = $SERIES[$set['series']] ?? null;
    $crumbs[] = ['Sets', 'sets', []];
    if($sser) $crumbs[] = [$sser['name'], 'series', ['s'=>$sser['slug']]];
    $crumbs[] = [$set['name'], '', []];
    $label = $set['name'].($set['code'] !== '' ? ' ('.$set['code'].')' : '');
    $page_title = $set['seo_title'] ?: $label.' Japanese Booster Boxes & Cards';
    $page_desc  = $set['seo_desc'] ?: (plain($set['intro']) ?: 'Japanese '.$set['name'].' booster boxes and cards, shipped from Japan to the USA.');
    $h1 = $label.' — Japanese Pokémon cards';
    break;
  case 'collection':
    $crumbs[] = ['Shop', 'catalog', []]; $crumbs[] = [$coll['title'], '', []];
    $page_title = ($coll['seo_title'] ?? '') ?: $coll['title'];
    $page_desc  = ($coll['seo_desc'] ?? '') ?: plain($coll['intro'] ?? '');
    $h1 = ($coll['h1'] ?? '') ?: $coll['title'];
    break;
  case 'guides':
    $crumbs[] = ['Guides', '', []];
    $page_title = 'Pokémon Card Guides — Values, Rarities, Size & Fakes';
    $page_desc  = 'Plain-English guides to Pokémon cards: what they are worth, rarities, card size, spotting fakes and buying Japanese Pokémon cards.';
    $h1 = 'Pokémon card guides';
    break;
  case 'guide':
    $crumbs[] = ['Guides', 'guides', []]; $crumbs[] = [$guide['title'], '', []];
    $page_title = ($guide['seo_title'] ?? '') ?: $guide['title'];
    $page_desc  = ($guide['seo_desc'] ?? '') ?: plain($guide['body'] ?? '');
    $h1 = $guide['title'];
    break;
  default:
    [$page_title, $h1] = $fixed[$page];
    if($page === 'checkout') $crumbs[] = ['Order', 'cart', []];
    if($h1 !== '') $crumbs[] = [$h1, '', []]; else $crumbs = [];
    if($page === 'notfound') $crumbs = [];
}
if($page_desc === '') $page_desc = 'Japanese Pokémon cards shipped from Japan to the USA: sealed booster boxes, Elite Trainer Boxes, rare singles and PSA graded cards.';

/* one canonical URL per page, without filters, currency or search */
$canon_args = ['product'=>['id'=>$prod['id'] ?? ''], 'set'=>['s'=>$set['slug'] ?? ''], 'series'=>['s'=>$series['slug'] ?? ''],
               'collection'=>['c'=>$coll['slug'] ?? ''], 'guide'=>['g'=>$guide['slug'] ?? ''], 'catalog'=>$cat ? ['cat'=>$cat] : []];
$canonical = $page === 'notfound' ? '' : abs_url($page, $canon_args[$page] ?? []);
$noindex = in_array($page, ['cart','checkout','received','notfound'], true) || ($page === 'catalog' && $filtered);

$in_stock = array_values(array_filter($PRODUCTS, fn($p)=>!in_array($p['status'], ['preorder','soldout'], true)));
?>
<!DOCTYPE html>
<html lang="en-US">
<head>
<meta charset="utf-8">
<base href="<?= h($BASE) ?>">
<meta name="viewport" content="width=device-width, initial-scale=1, viewport-fit=cover">
<title><?= h($page_title) ?> | <?= h($CONFIG['brand']) ?></title>
<meta name="description" content="<?= h($page_desc) ?>">
<?php if($canonical): ?><link rel="canonical" href="<?= h($canonical) ?>"><?php endif; ?>
<meta name="robots" content="<?= $noindex ? 'noindex, follow' : 'index, follow, max-image-preview:large' ?>">
<?php
  $abs_img = fn($f)=>rtrim($CONFIG['domain'], '/').'/'.$f;
  $og = $prod ? photos($prod['id']) : (photos('hero') ?: ($PRODUCTS ? photos($PRODUCTS[0]['id']) : [])); ?>
<meta property="og:type" content="<?= $prod ? 'product' : ($guide ? 'article' : 'website') ?>">
<meta property="og:site_name" content="<?= h($CONFIG['brand']) ?>">
<meta property="og:locale" content="en_US">
<meta property="og:title" content="<?= h($page_title) ?>">
<meta property="og:description" content="<?= h($page_desc) ?>">
<?php if($canonical): ?><meta property="og:url" content="<?= h($canonical) ?>"><?php endif; ?>
<?php if($og): ?><meta property="og:image" content="<?= h($abs_img($og[0])) ?>"><?php endif; ?>
<meta name="twitter:card" content="summary_large_image">
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Shippori+Mincho+B1:wght@600;700;800&family=Zen+Kaku+Gothic+New:wght@400;500;700;900&display=swap" rel="stylesheet">

<?php
$org_id = rtrim($CONFIG['domain'], '/').'/#org';
$graph = [
  ['@type'=>'Organization','@id'=>$org_id,'name'=>$CONFIG['legal_name'],'url'=>abs_url('home'),'email'=>$CONFIG['email'],
   'address'=>['@type'=>'PostalAddress','streetAddress'=>$CONFIG['address'],'addressCountry'=>'JP'],
   'description'=>'Supplier of Japanese Pokémon Trading Card Game products, shipping from Japan to the USA and worldwide.'],
  ['@type'=>'WebSite','@id'=>rtrim($CONFIG['domain'], '/').'/#site','url'=>abs_url('home'),'name'=>$CONFIG['brand'],'inLanguage'=>'en-US',
   'publisher'=>['@id'=>$org_id],
   'potentialAction'=>['@type'=>'SearchAction','target'=>abs_url('catalog', ['q'=>'QUERY']),'query-input'=>'required name=search_term_string']],
];
$graph[1]['potentialAction']['target'] = str_replace('QUERY', '{search_term_string}', $graph[1]['potentialAction']['target']);
if($prod){
  $offer = ['@type'=>'Offer','url'=>$canonical,'priceCurrency'=>'USD',
    'price'=>number_format(unit_price($prod, $prod['moq']), 2, '.', ''),
    'eligibleQuantity'=>['@type'=>'QuantitativeValue','minValue'=>$prod['moq']],
    'availability'=>$prod['status']==='preorder' ? 'https://schema.org/PreOrder'
                    : (can_order($prod) ? 'https://schema.org/InStock' : 'https://schema.org/OutOfStock'),
    'seller'=>['@id'=>$org_id]];
  if(($prod['cond'] ?? 'Sealed') === 'Sealed') $offer['itemCondition'] = 'https://schema.org/NewCondition';
  $graph[] = array_filter(['@type'=>'Product','name'=>$prod['name'],'sku'=>$prod['sku'],'url'=>$canonical,
    'image'=>array_map($abs_img, photos($prod['id'])) ?: null,
    'description'=>$prod['desc'],'category'=>$CATEGORIES[$prod['cat']]['label'],
    'brand'=>['@type'=>'Brand','name'=>'Pokémon'],'offers'=>$offer]);
}
if($guide){
  $graph[] = ['@type'=>'Article','headline'=>$guide['title'],'description'=>$page_desc,'url'=>$canonical,
    'dateModified'=>$guide['updated'] ?? gmdate('Y-m-d'),'inLanguage'=>'en-US',
    'author'=>['@id'=>$org_id],'publisher'=>['@id'=>$org_id],'image'=>$og ? $abs_img($og[0]) : null];
}
if(count($crumbs) > 1){
  $items = [];
  foreach($crumbs as $i=>[$label, $pg, $args])
    $items[] = array_filter(['@type'=>'ListItem','position'=>$i + 1,'name'=>$label,'item'=>$pg !== '' ? abs_url($pg, $args) : $canonical]);
  $graph[] = ['@type'=>'BreadcrumbList','itemListElement'=>$items];
}
?>
<script type="application/ld+json">
<?= json_encode(['@context'=>'https://schema.org', '@graph'=>$graph], JSON_UNESCAPED_SLASHES|JSON_UNESCAPED_UNICODE|JSON_HEX_TAG) ?>
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
.filters{display:flex;flex-wrap:wrap;gap:10px 12px;align-items:flex-end;background:var(--card);border:1px solid var(--line);
  border-radius:4px;padding:14px;margin-bottom:20px}
.filters label{display:flex;flex-direction:column;gap:4px;font-size:12px;font-weight:700;color:var(--muted);flex:1 1 150px;min-width:0}
.filters select,.filters input{background:var(--paper);border:1px solid var(--line);border-radius:3px;padding:8px 9px;font-size:14px;font-weight:400;color:var(--ink);width:100%}
.filters .price span{display:flex;gap:6px}
.filters .fbtns{display:flex;gap:8px}
.filters .fbtns .btn{padding:9px 14px;font-size:14px}
@media(max-width:560px){.filters label{flex-basis:calc(50% - 6px)}.filters label.price{flex-basis:100%}}
.card .art{aspect-ratio:1/1;position:relative;border-bottom:1px solid var(--hair);overflow:hidden;text-decoration:none}
.card .art img{width:100%;height:100%;object-fit:cover}
.flag{position:absolute;top:10px;left:10px;font-size:10.5px;font-weight:700;letter-spacing:.05em;
  padding:3px 7px;border-radius:2px;background:var(--ink);color:var(--paper);z-index:2}
.flag.new{background:var(--brand);color:var(--onbrand)}
.flag.pre{background:var(--gold);color:#1B1405}
.flag.low{background:var(--seal);color:#fff}
.flag.out{background:var(--muted);color:var(--paper)}
.card .in{padding:14px;display:flex;flex-direction:column;gap:7px;flex:1}
.card h3{font-size:15px;line-height:1.35}
.card h3 a{text-decoration:none}
.card .meta{font-size:12px;color:var(--muted)}
.card .px{display:flex;align-items:baseline;gap:8px;margin-top:auto}
.card .px .u{font-size:21px;font-weight:900}
.card .px .w{font-size:12.5px;color:var(--muted);text-decoration:line-through}
.card .px .per{font-size:12px;color:var(--muted);margin-left:-4px}
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
.hp{position:absolute;left:-9999px;width:1px;height:1px;overflow:hidden}
.minwarn{border-left:3px solid var(--seal);background:color-mix(in srgb,var(--seal) 8%,transparent);
  padding:11px 14px;font-size:13.5px;border-radius:0 3px 3px 0;margin-top:12px}
.minwarn[hidden]{display:none}

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
.fg{display:grid;grid-template-columns:1.4fr repeat(4,1fr);gap:28px}
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
section.top{padding-top:22px}
.sechead h1{font-size:clamp(26px,3.4vw,38px);margin:0}
.sechead .count{color:var(--muted);font-size:14px}
.sub2{font-size:clamp(20px,2.4vw,26px);margin:36px 0 14px}
.prose{max-width:760px;color:var(--ink2);font-size:15.5px}
.prose>:first-child{margin-top:0}
.prose h2{font-size:clamp(21px,2.4vw,27px);color:var(--ink);margin:30px 0 10px}
.prose h3{font-size:18px;color:var(--ink);margin:22px 0 8px}
.prose p{margin:0 0 14px}
.prose ul{margin:0 0 14px;padding-left:20px}
.prose li{margin-bottom:6px}
.prose a{color:var(--brand);font-weight:700}
.prose strong{color:var(--ink)}
.prose.lead{margin-bottom:24px}
.prose.after{margin-top:44px;padding-top:26px;border-top:1px solid var(--line)}
.tiles{display:grid;grid-template-columns:repeat(auto-fill,minmax(150px,1fr));gap:12px}
.tiles a{background:var(--card);border:1px solid var(--line);border-radius:4px;padding:14px 16px;text-decoration:none;display:flex;flex-direction:column;gap:3px}
.tiles a:hover{border-color:var(--brand)}
.tiles b{font-family:'Shippori Mincho B1',serif;font-size:16px;line-height:1.3}
.tiles .n{font-size:12px;color:var(--muted)}
.gcards{display:grid;grid-template-columns:repeat(auto-fill,minmax(260px,1fr));gap:14px}
.gcards a{background:var(--card);border:1px solid var(--line);border-radius:4px;padding:18px;text-decoration:none;display:flex;flex-direction:column;gap:8px}
.gcards a:hover{border-color:var(--brand)}
.gcards h3{font-size:17px}
.gcards p{font-size:14px;color:var(--ink2)}
.gcards span{margin-top:auto;font-size:13.5px;font-weight:700;color:var(--brand)}
.article{max-width:780px}
.article h1{font-size:clamp(28px,4vw,42px);margin-bottom:8px}
.article .meta{font-size:13px;color:var(--muted);margin-bottom:24px}
</style>
</head>
<body>

<div class="strip"><div class="wrap">
  <span><?= h($CONFIG['strip_text']) ?></span>
  <?php if($CONFIG['strip_link_text']): ?><a href="<?= h($CONFIG['strip_link_url'] ?: url('catalog')) ?>"><?= h($CONFIG['strip_link_text']) ?></a><?php endif; ?>
</div></div>

<header class="site">
  <div class="wrap bar">
    <a class="brand" href="<?= h(url('home')) ?>">
      <span class="mk"><?= h($CONFIG['brand']) ?></span>
      <span class="kj"><?= h($CONFIG['kanji']) ?></span>
    </a>
    <form class="search" action="<?= empty($CONFIG['pretty_urls']) ? 'index.php' : 'shop' ?>" method="get" role="search">
      <?php if(empty($CONFIG['pretty_urls'])): ?><input type="hidden" name="p" value="catalog"><?php endif; ?>
      <input type="search" name="q" value="<?= h($q) ?>" placeholder="Search a set, card or SKU" aria-label="Search products">
      <button type="submit">Search</button>
    </form>
    <div class="tools">
      <select class="pick" onchange="location.href=this.value" aria-label="Currency">
        <?php $here_args = $_GET; unset($here_args['p'], $here_args['id'], $here_args['s'], $here_args['c'], $here_args['g']);
        if($from_path && isset($from_path['cat'])) unset($here_args['cat']);
        $here = $page === 'notfound' ? 'home' : $page;
        foreach($CURRENCIES as $code=>$m):
          $u = $here_args; $u['cur'] = $code;
          $u += ($canon_args[$here] ?? []); ?>
          <option value="<?= h(url($here, $u)) ?>" <?= cur_code()===$code?'selected':'' ?>><?= h($code) ?></option>
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
    <a href="<?= url('sets') ?>" class="<?= in_array($page, ['sets','series','set'], true)?'on':'' ?>">Sets</a>
    <?php if($GUIDES): ?><a href="<?= url('guides') ?>" class="<?= in_array($page, ['guides','guide'], true)?'on':'' ?>">Guides</a><?php endif; ?>
    <a href="<?= url('how') ?>" class="<?= $page==='how'?'on':'' ?>">How it works</a>
    <a href="<?= url('shipping') ?>" class="<?= $page==='shipping'?'on':'' ?>">Shipping</a>
    <a href="<?= url('payment') ?>" class="<?= $page==='payment'?'on':'' ?>">Payment</a>
    <a href="<?= url('faq') ?>" class="<?= $page==='faq'?'on':'' ?>">FAQ</a>
    <a href="<?= url('contact') ?>" class="<?= $page==='contact'?'on':'' ?>">Contact</a>
  </div></nav>
</header>

<main>
<?php if(!empty($_SESSION['flash'])): ?>
  <div class="wrap"><div class="notice" role="status"><?php foreach($_SESSION['flash'] as $msg) echo '<div>'.h($msg).'</div>'; ?></div></div>
<?php unset($_SESSION['flash']); endif; ?>
<?php if(count($crumbs) > 1): ?>
  <nav class="wrap crumbs" aria-label="Breadcrumb"><?php foreach($crumbs as $i=>[$label, $pg, $args]):
    echo $i ? ' / ' : '';
    echo $pg !== '' ? '<a href="'.h(url($pg, $args)).'">'.h($label).'</a>' : '<span aria-current="page">'.h($label).'</span>';
  endforeach; ?></nav>
<?php endif; ?>
<?php if($page==='home'): ?>

  <section class="hero"><div class="wrap hgrid">
    <div>
      <div class="eyebrow"><b>Japan direct</b> · Sealed · Priced by the case · <?= count($COUNTRIES) ?> countries</div>
      <h1><?= h($CONFIG['hero_title']) ?></h1>
      <p class="lede"><?= h($CONFIG['hero_lede']) ?></p>
      <div class="hero-cta">
        <a class="btn" href="<?= url('catalog') ?>">Shop Japanese Pokémon cards</a>
        <a class="btn g" href="<?= url('how') ?>">How ordering works</a>
      </div>
      <div class="stats">
        <div><strong><?= count($in_stock) ?></strong>SKUs in stock</div>
        <div><strong><?= money($MIN_ORDER) ?></strong>Minimum order, incl. shipping</div>
        <div><strong><?= count($COUNTRIES) ?></strong>Countries served</div>
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
        <?php foreach(array_slice($in_stock,0,8) as $p) include_card($p); ?>
      </div>
    </div>
  </section>

  <?php $home_sets = sets_all(); if($home_sets): ?>
  <section style="padding-top:0"><div class="wrap">
    <div class="sechead"><div><h2>Shop by set</h2>
      <p>Japanese Mega Evolution sets, plus Scarlet &amp; Violet favourites like 151.</p></div>
      <a href="<?= url('sets') ?>">All sets →</a></div>
    <?php set_tiles($home_sets); ?>
  </div></section>
  <?php endif; ?>

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
      <div><span>Minimum order value</span><span><?= money($MIN_ORDER) ?> incl. shipping</span></div>
      <div><span>Stock hold on order</span><span><?= (int)$CONFIG['hold_hours'] ?> hours</span></div>
      <div><span>Dispatch after payment</span><span>Within <?= (int)$CONFIG['hold_hours'] ?> hours</span></div>
      <div><span>Carriers</span><span>EMS · DHL · FedEx</span></div>
      <div><span>Shipping</span><span>Calculated at checkout</span></div>
    </div>
  </div></section>

  <section><div class="wrap">
    <div class="sechead"><div><h2>Four steps from cart to courier</h2></div></div>
    <?php steps_block($CONFIG); ?>
  </div></section>

  <?php if($GUIDES): ?>
  <section style="padding-top:0"><div class="wrap">
    <div class="sechead"><div><h2>Pokémon card guides</h2>
      <p>What cards are worth, how rarities work, card sizes and how to spot fakes.</p></div>
      <a href="<?= url('guides') ?>">All guides →</a></div>
    <?php guide_cards(array_slice($GUIDES, 0, 3)); ?>
  </div></section>
  <?php endif; ?>

  <?php if(trim($CONFIG['home_intro'] ?? '') !== ''): ?>
  <section style="padding-top:0"><div class="wrap"><div class="prose"><?= rich($CONFIG['home_intro']) ?></div></div></section>
  <?php endif; ?>

<?php elseif($page==='catalog'):
  $rate = $CURRENCIES[cur_code()]['rate'];
  $list = array_filter($PRODUCTS, function($p) use($cat,$q,$CATEGORIES,$f_set,$f_cond,$f_avail,$f_min,$f_max,$rate){
    if($cat && $p['cat']!==$cat) return false;
    if($f_set !== '' && $p['set'] !== $f_set) return false;
    if($f_cond !== '' && ($p['cond'] ?? 'Sealed') !== $f_cond) return false;
    if($f_avail === 'preorder' && $p['status'] !== 'preorder') return false;
    if($f_avail === 'in' && in_array($p['status'], ['preorder','soldout'], true)) return false;
    $price = unit_price($p, $p['moq']) * $rate;
    if($f_min !== null && $price < $f_min) return false;
    if($f_max !== null && $price > $f_max) return false;
    if($q){
      $hay = strtolower($p['name'].' '.$p['set'].' '.$p['sku'].' '.$CATEGORIES[$p['cat']]['label'].' '.($p['cond'] ?? ''));
      if(strpos($hay, strtolower($q))===false) return false;
    }
    return true;
  });
  if($f_sort === 'price-asc' || $f_sort === 'price-desc'){
    usort($list, fn($a, $b)=>unit_price($a, $a['moq']) <=> unit_price($b, $b['moq']));
    if($f_sort === 'price-desc') $list = array_reverse($list);
  } elseif($f_sort === 'name') usort($list, fn($a, $b)=>strcasecmp($a['name'], $b['name']));
  $count_by = fn($key)=>array_count_values(array_map(fn($p)=>(string)($p[$key] ?? ($key==='cond' ? 'Sealed' : '')), $PRODUCTS));
  $sets = array_filter($count_by('set'), fn($k)=>$k !== '', ARRAY_FILTER_USE_KEY);
  $conds = $count_by('cond');
  $cats_n = $count_by('cat');
  $n_pre = count(array_filter($PRODUCTS, fn($p)=>$p['status']==='preorder'));
  $n_in = count(array_filter($PRODUCTS, fn($p)=>!in_array($p['status'], ['preorder','soldout'], true))); ?>
  <section style="padding-top:22px"><div class="wrap">
    <div class="sechead"><div>
      <h1><?= h($h1) ?></h1>
      <p><?= $cat ? h($CATEGORIES[$cat]['blurb']) : 'Sealed booster boxes, Elite Trainer Boxes, rare singles and accessories, with the full quantity-break ladder on every listing.' ?></p>
    </div><span style="color:var(--muted);font-size:14px"><?= count($list) ?> product<?= count($list)===1?'':'s' ?></span></div>
    <form class="filters" method="get" action="<?= empty($CONFIG['pretty_urls']) ? 'index.php' : 'shop' ?>" id="filters" onsubmit="fsub(this); return false">
      <?php if(empty($CONFIG['pretty_urls'])): ?><input type="hidden" name="p" value="catalog"><?php endif; ?>
      <?php if($q !== ''): ?><input type="hidden" name="q" value="<?= h($q) ?>"><?php endif; ?>
      <label>Product type<select name="cat" onchange="fsub(this.form)">
        <option value="">All types</option>
        <?php foreach($CATEGORIES as $k=>$c): ?><option value="<?= h($k) ?>" <?= $cat===$k?'selected':'' ?>><?= h($c['label']) ?> (<?= (int)($cats_n[$k] ?? 0) ?>)</option><?php endforeach; ?>
      </select></label>
      <label>Set<select name="set" onchange="fsub(this.form)">
        <option value="">All sets</option>
        <?php foreach($sets as $k=>$n): ?><option value="<?= h($k) ?>" <?= $f_set===(string)$k?'selected':'' ?>><?= h($k) ?> (<?= (int)$n ?>)</option><?php endforeach; ?>
      </select></label>
      <label>Condition<select name="cond" onchange="fsub(this.form)">
        <option value="">Any condition</option>
        <?php foreach(CONDITIONS as $k): ?><option value="<?= h($k) ?>" <?= $f_cond===$k?'selected':'' ?>><?= h($k) ?> (<?= (int)($conds[$k] ?? 0) ?>)</option><?php endforeach; ?>
      </select></label>
      <label>Buying as<select name="avail" onchange="fsub(this.form)">
        <option value="">In stock &amp; pre-order</option>
        <option value="in" <?= $f_avail==='in'?'selected':'' ?>>In stock (<?= $n_in ?>)</option>
        <option value="preorder" <?= $f_avail==='preorder'?'selected':'' ?>>Pre-order (<?= $n_pre ?>)</option>
      </select></label>
      <label class="price">Price per unit (<?= h(cur_code()) ?>)<span>
        <input type="number" name="min" min="0" step="any" placeholder="Min" value="<?= $f_min===null?'':h($f_min) ?>" aria-label="Minimum price">
        <input type="number" name="max" min="0" step="any" placeholder="Max" value="<?= $f_max===null?'':h($f_max) ?>" aria-label="Maximum price"></span></label>
      <label>Sort<select name="sort" onchange="fsub(this.form)">
        <?php foreach([''=>'Featured','price-asc'=>'Price: low to high','price-desc'=>'Price: high to low','name'=>'Name A–Z'] as $k=>$lbl): ?><option value="<?= h($k) ?>" <?= $f_sort===$k?'selected':'' ?>><?= h($lbl) ?></option><?php endforeach; ?>
      </select></label>
      <div class="fbtns"><button class="btn" type="submit">Apply</button><?php if($filtered || $cat): ?><a class="btn g" href="<?= url('catalog') ?>">Clear</a><?php endif; ?></div>
    </form>
    <?php if($list): ?>
      <div class="grid"><?php foreach($list as $p) include_card($p); ?></div>
    <?php else: ?>
      <p class="empty">Nothing matches that. Try a set name, a card name or an SKU — or <a href="<?= url('catalog') ?>">browse everything</a>.</p>
    <?php endif; ?>
    <?php if($cinfo && !$filtered && trim($cinfo['intro'] ?? '') !== ''): ?><div class="prose after"><?= rich($cinfo['intro']) ?></div><?php endif; ?>
  </div></section>

<?php elseif($page==='product'):
  $ph = photos($prod['id']); $base = unit_price($prod,$prod['moq']); $best = end($prod['ladder']);
  $moq_tier = 0; foreach($prod['ladder'] as $i=>$t){ if($t[0] <= $prod['moq']) $moq_tier = $i; } ?>
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
      <div class="sub"><?= h(implode(' · ', array_filter([$prod['sku'], $prod['set'], $CATEGORIES[$prod['cat']]['label'], $prod['cond'] ?? 'Sealed']))) ?>
        <?= $prod['status']==='preorder' ? ' · Releases '.h($prod['release']) : ' · '.h(status_label(PRODUCT_STATUSES, $prod['status'])) ?></div>
      <p class="desc"><?= h($prod['desc']) ?></p>

      <div class="ladder">
        <div class="lh">Quantity-break pricing</div>
        <?php foreach($prod['ladder'] as $i=>$t):
          $next = $prod['ladder'][$i+1] ?? null;
          $hi   = $next ? $next[0]-1 : null;
          /* a tier wholly below MOQ is only a reference price */
          $lbl  = ($hi!==null && $hi < $prod['moq']) ? 'Single unit'
                : ($hi===null ? $t[0].'+ units' : ($hi===$t[0] ? $t[0].($t[0]===1?' unit':' units') : $t[0].' – '.$hi.' units')); ?>
          <div class="row <?= $i===$moq_tier?'on':'' ?>">
            <span><?= h($lbl) ?></span><span><?= money($t[1]) ?></span></div>
        <?php endforeach; ?>
      </div>

      <div class="buybox">
        <div class="big"><?= money($base) ?></div>
        <div class="sm">per unit at MOQ <?= h($prod['moq']) ?> · down to <?= money($best[1]) ?> at <?= h($best[0]) ?>+</div>
        <form method="post" action="index.php">
          <input type="hidden" name="action" value="add">
          <input type="hidden" name="id" value="<?= h($prod['id']) ?>">
          <?php if(can_order($prod)): stepper($prod, 'qty', $prod['moq'], $prod['moq']); ?>
          <button class="btn" type="submit"><?= $prod['status']==='preorder'?'Add preorder':'Add to order' ?></button>
          <?php else: ?>
          <button class="btn" type="submit" disabled>Sold out</button>
          <?php endif; ?>
        </form>
        <div class="trustline">
          <span>Sold in <?= $prod['step']>1 ? (int)$prod['step'].'s' : 'singles' ?></span>
          <span>Held <?= (int)$CONFIG['hold_hours'] ?>h on order</span>
          <span>Ships from Japan</span>
        </div>
      </div>
    </div>
  </div>

  <?php $sibs = $pset ? array_values(array_filter(set_products($pset['name']), fn($x)=>$x['id'] !== $prod['id'])) : [];
  if($sibs): ?>
  <section><div class="wrap">
    <div class="sechead"><div><h2>More from <?= h($pset['name']) ?></h2></div>
      <a href="<?= h(url('set', ['s'=>$pset['slug']])) ?>">All <?= h($pset['name']) ?> →</a></div>
    <div class="grid"><?php foreach(array_slice($sibs, 0, 4) as $p) include_card($p); ?></div>
  </div></section>
  <?php endif; ?>
  <section><div class="wrap">
    <div class="sechead"><div><h2>More <?= h($CATEGORIES[$prod['cat']]['label']) ?></h2></div>
      <a href="<?= url('catalog',['cat'=>$prod['cat']]) ?>">See all →</a></div>
    <div class="grid">
      <?php $rel = array_slice(array_values(array_filter($PRODUCTS, fn($x)=>$x['cat']===$prod['cat'] && $x['id']!==$prod['id'])),0,4);
      if(!$rel) $rel = array_slice(array_values(array_filter($PRODUCTS, fn($x)=>$x['id']!==$prod['id'])),0,4);
      foreach($rel as $p) include_card($p); ?>
    </div>
  </div></section>

<?php elseif($page==='cart'): $lines = cart_lines(); ?>
  <section style="padding-top:22px"><div class="wrap">
    <div class="sechead"><div><h1><?= h($h1) ?></h1>
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
                  <div class="sk"><?= h($l['p']['sku']) ?> · sold in <?= h($l['p']['step']) ?>s</div></td>
              <td><?php stepper($l['p'], 'qty['.$l['p']['id'].']', $l['qty'], 0); ?></td>
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
            <div style="font-size:26px;font-weight:900;margin:4px 0 4px">Goods total <?= money(cart_total()) ?></div>
            <div style="font-size:13.5px;color:var(--muted);margin-bottom:10px">Shipping is calculated at checkout. Minimum order <?= money($MIN_ORDER) ?> including shipping.</div>
            <a class="btn" href="<?= url('checkout') ?>">Continue to checkout</a>
          </div>
        </div>
      </form>
    <?php endif; ?>
  </div></section>

<?php elseif($page==='checkout'): $lines = cart_lines(); $f = $form ?? [];
  $_SESSION['co_token'] = $_SESSION['co_token'] ?? bin2hex(random_bytes(16));
  $_SESSION['co_time']  = time(); ?>
  <section style="padding-top:22px"><div class="wrap">
    <div class="sechead"><div><h1><?= h($h1) ?></h1>
      <p>Tell us where it ships and how you want to pay. Nothing is charged here — we send your payment details and invoice by email or text after you place the order.</p></div></div>

    <?php if(!$lines): ?>
      <p class="empty">Your order is empty. <a href="<?= url('catalog') ?>">Browse the catalog →</a></p>
    <?php else: ?>
    <?php if($errors): ?>
      <div class="errs"><b>Please fix the following:</b><ul><?php foreach($errors as $e) echo '<li>'.h($e).'</li>'; ?></ul></div>
    <?php endif; ?>

    <form method="post" action="index.php" class="cogrid">
      <input type="hidden" name="action" value="order">
      <input type="hidden" name="token" value="<?= h($_SESSION['co_token']) ?>">
      <div class="hp" aria-hidden="true"><label for="website">Leave this empty</label>
        <input id="website" name="website" tabindex="-1" autocomplete="off"></div>
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
                <option value="<?= h($code) ?>" <?= ($f['country']??'')===$code?'selected':'' ?>><?= h($nm) ?></option>
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
              $data = $m['countries']==='*' ? '*' : implode(',', $m['countries']);
              $ok   = payment_ok($key, $f['country'] ?? ''); ?>
              <label data-countries="<?= h($data) ?>" <?= $ok?'':'hidden' ?>>
                <input type="radio" name="payment" value="<?= h($key) ?>" <?= $ok && ($f['payment']??'')===$key?'checked':'' ?>>
                <span><span class="t"><?= h($m['label']) ?></span><br><span class="n"><?= h($m['note']) ?></span></span>
              </label>
            <?php endforeach; ?>
          </div>
          <div class="notice">
            <b>How payment works.</b> Select your method and place the order. We will send the payment
            details for that method to your email and phone within <?= (int)$CONFIG['reply_hours'] ?> hours,
            together with your invoice. Your stock is reserved for
            <?= (int)$CONFIG['hold_hours'] ?> hours in the meantime. Quote your order reference on the
            payment so we can match it to your order.
          </div>
          <div class="fld"><label for="notes">Order notes (optional)</label>
            <textarea id="notes" name="notes" placeholder="Delivery instructions, preferred carrier, VAT/EORI number, anything else we should know."><?= h($f['notes']??'') ?></textarea></div>
          <label class="agree">
            <input type="checkbox" name="agree" value="1" <?= !empty($_POST['agree'])?'checked':'' ?>>
            <span>I understand that no payment is taken on this site, and that <?= h($CONFIG['legal_name']) ?>
            will contact me by email or text with the payment details and invoice.</span>
          </label>
          <div class="minwarn" id="minWarn" hidden></div>
          <button class="btn wide" id="placeBtn" type="submit" style="margin-top:14px">Place order</button>
          <p style="font-size:12.5px;color:var(--muted);margin-top:10px">
            Shipping is calculated from your destination and shown in the order summary. Import duty and taxes are not included.</p>
        </fieldset>
      </div>

      <div>
        <div class="summary">
          <h3>Order summary</h3>
          <?php foreach($lines as $l): ?>
            <div class="sl"><span><?= h($l['p']['name']) ?><br><span class="q"><?= h($l['qty']) ?> × <?= money($l['unit']) ?></span></span>
              <span><?= money($l['total']) ?></span></div>
          <?php endforeach; ?>
          <?php if(cart_saved()>0): ?>
            <div class="sl"><span style="color:var(--muted)">Quantity-break saving</span>
              <span style="color:var(--seal)">− <?= money(cart_saved()) ?></span></div>
          <?php endif; ?>
          <div class="sl"><span style="color:var(--muted)">Goods</span><span><?= money(cart_total()) ?></span></div>
          <div class="sl"><span style="color:var(--muted)">Shipping</span><span id="shipCost" style="color:var(--muted)">Select your country</span></div>
          <div class="tot"><span>Order total</span><span id="grandTotal"><?= money(cart_total()) ?></span></div>
          <p style="font-size:12.5px;color:var(--muted);margin-top:8px">Minimum order <?= money($MIN_ORDER) ?> including shipping.</p>
          <?php
          /* per-country shipping for this cart, so the summary updates as the country changes */
          $cm = $CURRENCIES[cur_code()]; $kg = cart_weight(); $ship_by = [];
          foreach($COUNTRIES as $code=>$nm) $ship_by[$code] = shipping_usd($STORE, $code, $kg);
          $co_data = ['ship'=>$ship_by, 'goods'=>cart_total(), 'min'=>$MIN_ORDER,
                      'rate'=>$cm['rate'], 'sym'=>$cm['sym'], 'dec'=>$cm['dec']]; ?>
          <script>window.CO = <?= json_encode($co_data, JSON_HEX_TAG|JSON_HEX_AMP) ?>;</script>
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
      <p class="lede" style="margin:14px auto 0"><?php if($o['mail_customer'] ?? true): ?>A confirmation is on its way to <b><?= h($o['email']) ?></b>.<?php else: ?>We couldn’t send a confirmation email just now, so please note your order reference. We have your order and will contact you at <b><?= h($o['email']) ?></b>.<?php endif; ?> Your stock is reserved for <?= (int)$CONFIG['hold_hours'] ?> hours.</p>

      <div class="card2">
        <h3 style="font-size:19px">What happens next</h3>
        <ol>
          <li>We send the payment details for <b><?= h($o['payment_label']) ?></b> to your email and phone within <?= (int)$CONFIG['reply_hours'] ?> hours, with your invoice.</li>
          <li>Quote <b><?= h($o['ref']) ?></b> on the payment so we can match it to your order.</li>
          <li>Payment clears, stock is allocated, and we dispatch within <?= (int)$CONFIG['hold_hours'] ?> hours from Japan with tracking.</li>
          <li>Tracking is emailed to you the moment the label is generated.</li>
        </ol>
        <hr style="border:none;border-top:1px solid var(--hair);margin:20px 0">
        <div class="sl" style="border:none;padding:0"><span>Goods</span><span><?= h($o['goods']) ?></span></div>
        <div class="sl" style="border:none;padding:4px 0 0"><span>Shipping</span><span><?= h($o['shipping']) ?></span></div>
        <div class="sl" style="border:none;padding:4px 0 0"><span>Order total</span><b><?= h($o['total']) ?> <?= h($o['currency']) ?></b></div>
        <div class="sl" style="border:none;padding:4px 0 0"><span>Shipping to</span><span><?= h($o['city']) ?>, <?= h($o['country_name']) ?></span></div>
        <p style="font-size:13.5px;color:var(--muted);margin-top:16px">
          Nothing heard within <?= (int)$CONFIG['reply_hours'] ?> hours? Check your spam folder, then email
          <a href="mailto:<?= h($CONFIG['email']) ?>"><?= h($CONFIG['email']) ?></a> quoting <?= h($o['ref']) ?>.</p>
      </div>
      <p style="margin-top:24px"><a class="btn g" href="<?= url('catalog') ?>">Continue shopping</a></p>
    <?php endif; ?>
  </div></section>

<?php elseif($page==='how'): ?>
  <section style="padding-top:22px"><div class="wrap">
    <div class="sechead"><div><h1><?= h($h1) ?></h1>
      <p>Four steps from cart to courier. Nothing is charged on this site — you settle an invoice from your own bank or payment app.</p></div></div>
    <?php steps_block($CONFIG); ?>
    <div style="margin-top:40px;display:grid;grid-template-columns:repeat(3,1fr);gap:16px" class="cats">
      <a href="<?= url('payment') ?>"><h3>Payment methods</h3><p>What we accept, by country.</p></a>
      <a href="<?= url('shipping') ?>"><h3>Shipping &amp; customs</h3><p>Carriers, timings, duty and taxes.</p></a>
      <a href="<?= url('faq') ?>"><h3>Wholesale FAQ</h3><p>MOQs, preorders, returns and more.</p></a>
    </div>
  </div></section>

<?php elseif($page==='payment'): ?>
  <section style="padding-top:22px"><div class="wrap" style="max-width:820px">
    <div class="sechead"><div><h1><?= h($h1) ?></h1>
      <p>Choose your method at checkout. We send the details for it to your email and phone within <?= (int)$CONFIG['reply_hours'] ?> hours, together with your invoice.</p></div></div>
    <table class="tbl">
      <thead><tr><th>Method</th><th>Available to</th><th>Notes</th></tr></thead>
      <tbody>
        <?php foreach($PAYMENTS as $m):
          $where = $m['countries']==='*' ? 'All countries'
                 : implode(', ', array_map(fn($c)=>$COUNTRIES[$c] ?? $c, $m['countries'])); ?>
          <tr><td class="nm"><?= h($m['label']) ?></td><td><?= h($where) ?></td><td><?= h($m['note']) ?></td></tr>
        <?php endforeach; ?>
      </tbody>
    </table>
    <div class="notice" style="margin-top:22px">
      <b>No payment is taken on this site.</b> There is no card form and no wallet credential to enter here.
      You place the order, we send the payment details, and stock is held for <?= (int)$CONFIG['hold_hours'] ?> hours
      while that happens. Always check the payment details against the email we send from
      <?= h($CONFIG['email']) ?> and quote your order reference.
    </div>
    <p style="font-size:14px;color:var(--ink2);margin-top:18px">Invoices are issued in the currency you had selected at checkout. Shipping is calculated at checkout. Prices exclude import duty, VAT or GST and customs clearance fees.</p>
  </div></section>

<?php elseif($page==='shipping'): ?>
  <section style="padding-top:22px"><div class="wrap" style="max-width:820px">
    <div class="sechead"><div><h1><?= h($h1) ?></h1>
      <p>Everything ships from Japan with tracking on every consignment.</p></div></div>
    <table class="tbl">
      <thead><tr><th>Detail</th><th>What to expect</th></tr></thead>
      <tbody>
        <tr><td class="nm">Carriers</td><td>Japan Post EMS, DHL Express and FedEx. We choose on weight, destination and your instructions.</td></tr>
        <tr><td class="nm">Dispatch</td><td>Within <?= (int)$CONFIG['hold_hours'] ?> hours of payment clearing.</td></tr>
        <tr><td class="nm">Transit</td><td>Typically 3–6 working days to North America and Europe, 2–4 within Asia-Pacific.</td></tr>
        <tr><td class="nm">Shipping cost</td><td>Calculated at checkout from your destination and the weight of your order — see the rates below.</td></tr>
        <tr><td class="nm">Minimum order</td><td><?= money($MIN_ORDER) ?> including shipping.</td></tr>
        <tr><td class="nm">Duty and taxes</td><td>Excluded from our prices. US orders of any value can be charged import duty and carrier fees on delivery; elsewhere your carrier collects import duty, VAT or GST and clearance fees.</td></tr>
        <tr><td class="nm">Damage or shortage</td><td>Report within seven days of delivery and we replace, credit or refund the affected lines and their shipping.</td></tr>
      </tbody>
    </table>
    <h3 style="font-size:19px;margin:34px 0 12px">Shipping rates</h3>
    <table class="tbl">
      <thead><tr><th>Destination</th><th class="r">Per order</th><th class="r">Plus per kg</th></tr></thead>
      <tbody>
        <?php foreach(array_merge($STORE['shipping']['zones'], [['name'=>'Rest of world']+$STORE['shipping']['rest']]) as $z): ?>
          <tr><td class="nm"><?= h($z['name']) ?></td><td class="r"><?= money($z['base']) ?></td><td class="r"><?= money($z['per_kg']) ?></td></tr>
        <?php endforeach; ?>
      </tbody>
    </table>
    <p style="font-size:13.5px;color:var(--muted);margin-top:12px">As a guide, a sealed booster box weighs about 0.4 kg and an Elite Trainer Box about 0.9 kg packed.</p>
  </div></section>

<?php elseif($page==='faq'): ?>
  <section style="padding-top:22px"><div class="wrap" style="max-width:860px">
    <div class="sechead"><div><h1><?= h($h1) ?></h1></div></div>
    <div class="faq">
      <?php $faqs = array_map(fn($f)=>[fill($f[0]), fill($f[1])], $STORE['faqs']);
      foreach($faqs as $i=>$fq): ?>
        <details <?= $i===0?'open':'' ?>><summary><?= h($fq[0]) ?></summary><p><?= h($fq[1]) ?></p></details>
      <?php endforeach; ?>
    </div>
  </div></section>
  <script type="application/ld+json">
  <?= json_encode(['@context'=>'https://schema.org','@type'=>'FAQPage','mainEntity'=>array_map(fn($f)=>
    ['@type'=>'Question','name'=>$f[0],'acceptedAnswer'=>['@type'=>'Answer','text'=>$f[1]]], $faqs)],
    JSON_UNESCAPED_SLASHES|JSON_UNESCAPED_UNICODE|JSON_HEX_TAG) ?>
  </script>

<?php elseif($page==='contact'): ?>
  <section style="padding-top:22px"><div class="wrap" style="max-width:700px">
    <div class="sechead"><div><h1><?= h($h1) ?></h1>
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

<?php elseif($page==='sets'): ?>
  <section class="top"><div class="wrap">
    <div class="sechead"><div><h1><?= h($h1) ?></h1>
      <p>Every Japanese Pokémon card set we stock. Japanese sets release before their English versions, so these are the newest cards in the hobby.</p></div></div>
    <?php $shown = [];
    foreach($SERIES as $k=>$sr): $ss = series_sets($k); if(!$ss) continue; $shown += $ss; ?>
      <div class="sechead" style="margin:30px 0 14px"><div><h2><?= h($sr['name']) ?></h2></div>
        <a href="<?= h(url('series', ['s'=>$sr['slug']])) ?>">About <?= h($sr['name']) ?> →</a></div>
      <?php set_tiles($ss);
    endforeach;
    $other = array_diff_key(sets_all(), $shown);
    if($other): ?><div class="sechead" style="margin:30px 0 14px"><div><h2>Other sets</h2></div></div><?php set_tiles($other); endif; ?>
  </div></section>

<?php elseif($page==='series'):
  $ss = series_sets($series['key']);
  $sp = array_values(array_filter($PRODUCTS, fn($p)=>isset($ss[$p['set']]))); ?>
  <section class="top"><div class="wrap">
    <div class="sechead"><div><h1><?= h($h1) ?></h1></div><span class="count"><?= count($sp) ?> product<?= count($sp)===1?'':'s' ?></span></div>
    <?php if(trim($series['intro'] ?? '') !== ''): ?><div class="prose lead"><?= rich($series['intro']) ?></div><?php endif; ?>
    <?php if($ss): ?><h2 class="sub2">Sets</h2><?php set_tiles($ss); endif; ?>
    <?php if($sp): ?><h2 class="sub2">All <?= h($series['name']) ?> products</h2><div class="grid"><?php foreach($sp as $p) include_card($p); ?></div><?php endif; ?>
  </div></section>

<?php elseif($page==='set'):
  $sp = set_products($set['name']);
  $others = isset($SERIES[$set['series']]) ? array_diff_key(series_sets($set['series']), [$set['name']=>true]) : []; ?>
  <section class="top"><div class="wrap">
    <div class="sechead"><div><h1><?= h($h1) ?></h1></div><span class="count"><?= count($sp) ?> product<?= count($sp)===1?'':'s' ?></span></div>
    <?php if(trim($set['intro']) !== ''): ?><div class="prose lead"><?= rich($set['intro']) ?></div><?php endif; ?>
    <div class="grid"><?php foreach($sp as $p) include_card($p); ?></div>
    <?php if($others): ?><h2 class="sub2">More <?= h($SERIES[$set['series']]['name']) ?> sets</h2><?php set_tiles($others); endif; ?>
  </div></section>

<?php elseif($page==='collection'):
  $cp = collection_products($coll); ?>
  <section class="top"><div class="wrap">
    <div class="sechead"><div><h1><?= h($h1) ?></h1></div><span class="count"><?= count($cp) ?> product<?= count($cp)===1?'':'s' ?></span></div>
    <?php if(trim($coll['intro'] ?? '') !== ''): ?><div class="prose lead"><?= rich($coll['intro']) ?></div><?php endif; ?>
    <?php if($cp): ?><div class="grid"><?php foreach($cp as $p) include_card($p); ?></div>
    <?php else: ?><p class="empty">Nothing in stock here right now — see all <a href="<?= url('catalog', ['cat'=>'singles']) ?>">single cards</a>.</p><?php endif; ?>
  </div></section>

<?php elseif($page==='guides'): ?>
  <section class="top"><div class="wrap">
    <div class="sechead"><div><h1><?= h($h1) ?></h1>
      <p>Straight answers about Pokémon cards — what they're worth, what the rarities mean, sizes and sleeves, spotting fakes, and buying Japanese cards.</p></div></div>
    <?php guide_cards($GUIDES); ?>
  </div></section>

<?php elseif($page==='guide'): ?>
  <section class="top"><div class="wrap">
    <article class="article">
      <h1><?= h($h1) ?></h1>
      <?php if(!empty($guide['updated'])): ?><p class="meta">Updated <?= h(date('j F Y', strtotime($guide['updated']))) ?></p><?php endif; ?>
      <div class="prose"><?= rich($guide['body'] ?? '') ?></div>
      <div class="notice" style="margin-top:28px">Shop <a href="<?= url('catalog', ['cat'=>'boxes']) ?>">Japanese booster boxes</a>, <a href="<?= url('catalog', ['cat'=>'singles']) ?>">rare single cards</a> or <a href="<?= url('catalog', ['cat'=>'accessories']) ?>">binders and sleeves</a> — shipped from Japan to the USA.</div>
    </article>
    <?php $more = array_values(array_filter($GUIDES, fn($g)=>$g['slug'] !== $guide['slug']));
    if($more): ?><h2 class="sub2">More guides</h2><?php guide_cards(array_slice($more, 0, 3)); endif; ?>
  </div></section>

<?php elseif($page==='notfound'): ?>
  <section class="top"><div class="wrap" style="max-width:720px">
    <h1><?= h($h1) ?></h1>
    <p class="lede">That page doesn't exist — it may have moved. Try the <a href="<?= url('catalog') ?>">shop</a>, browse <a href="<?= url('sets') ?>">Pokémon card sets</a>, or search above.</p>
  </div></section>

<?php endif; ?>
</main>

<footer class="site"><div class="wrap">
  <div class="fg">
    <div>
      <div class="brand"><span class="mk" style="color:#fff"><?= h($CONFIG['brand']) ?></span>
        <span class="kj" style="color:var(--gold);border-color:var(--gold)"><?= h($CONFIG['kanji']) ?></span></div>
      <p class="bl"><?= h($CONFIG['footer_blurb']) ?></p>
    </div>
    <div><h4>Shop</h4><ul>
      <?php foreach($CATEGORIES as $k=>$c): ?>
        <li><a href="<?= url('catalog',['cat'=>$k]) ?>"><?= h($c['label']) ?></a></li>
      <?php endforeach; ?>
      <li><a href="<?= url('catalog') ?>">All products</a></li>
    </ul></div>
    <div><h4>Explore</h4><ul>
      <li><a href="<?= url('sets') ?>">Pokémon card sets</a></li>
      <?php foreach($SERIES as $k=>$sr): if(series_sets($k)): ?><li><a href="<?= h(url('series', ['s'=>$sr['slug']])) ?>"><?= h($sr['name']) ?> sets</a></li><?php endif; endforeach; ?>
      <?php foreach($COLLECTIONS as $c): if(collection_products($c)): ?><li><a href="<?= h(url('collection', ['c'=>$c['slug']])) ?>"><?= h($c['title']) ?></a></li><?php endif; endforeach; ?>
      <?php if($GUIDES): ?><li><a href="<?= url('guides') ?>">Pokémon card guides</a></li><?php endif; ?>
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
/* catalogue filters: drop empty fields so shared links stay short */
function fsub(f){
  [...f.elements].forEach(el => { if(el.name && el.value === '') el.disabled = true; });
  f.submit();
}
function bump(btn, delta, min){
  const input = btn.parentElement.querySelector('input');
  input.value = Math.max(min, (parseInt(input.value,10) || min) + delta);
}
function fmt(usd){
  return CO.sym + (usd * CO.rate).toLocaleString('en-US', {minimumFractionDigits: CO.dec, maximumFractionDigits: CO.dec});
}
/* shipping, total and the minimum-order check follow the selected country; the server re-checks all of it */
function updateTotals(country){
  if(!window.CO) return;
  const ship = CO.ship[country], warn = document.getElementById('minWarn'), btn = document.getElementById('placeBtn');
  const cost = document.getElementById('shipCost');
  if(ship === undefined){ cost.textContent = 'Select your country'; document.getElementById('grandTotal').textContent = fmt(CO.goods); warn.hidden = true; btn.disabled = false; return; }
  const total = Math.round((CO.goods + ship) * 100) / 100;
  cost.textContent = fmt(ship); cost.style.color = '';
  document.getElementById('grandTotal').textContent = fmt(total);
  const short = total < CO.min;
  warn.hidden = !short; btn.disabled = short;
  if(short) warn.textContent = 'The minimum order is ' + fmt(CO.min) + ' including shipping. Your total is ' + fmt(total) + ', so add ' + fmt(CO.min - total) + ' more to place this order.';
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
if(c){ c.addEventListener('change', ()=>updateTotals(c.value)); if(c.value){ filterPay(c.value); updateTotals(c.value); } }
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
        : ($p['status']==='soldout' ? ['out','SOLD OUT']
        : ($p['status']==='new' ? ['new','NEW']
        : ($p['status']==='low' ? ['low','LOW STOCK'] : ['','IN STOCK'])));
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
      <div class="meta"><?= h(implode(' · ', array_filter([$p['set'], ($p['cond'] ?? 'Sealed') !== 'Sealed' ? $p['cond'] : '', $p['status']==='preorder' ? $p['release'] : '']))) ?></div>
      <div class="px"><span class="u"><?= money($base) ?></span><span class="per">/unit at <?= (int)$p['moq'] ?></span><?php if($p['ladder'][0][1] > $base): ?><span class="w"><?= money($p['ladder'][0][1]) ?></span><?php endif; ?></div>
      <?php if(count($p['ladder']) > 1 && $best[1] < $base): ?><div class="drop">down to <b><?= money($best[1]) ?></b> at <?= (int)$best[0] ?>+</div><?php endif; ?>
      <div class="meta">MOQ <?= (int)$p['moq'] ?><?= $p['step'] > 1 ? ' · in '.(int)$p['step'].'s' : '' ?></div>
    </div>
    <form method="post" action="index.php">
      <input type="hidden" name="action" value="add">
      <input type="hidden" name="id" value="<?= h($p['id']) ?>">
      <?php if(can_order($p)): stepper($p, 'qty', $p['moq'], $p['moq']); ?>
      <button class="btn" type="submit">Add</button>
      <?php else: ?>
      <button class="btn" type="submit" disabled>Sold out</button>
      <?php endif; ?>
    </form>
  </article>
  <?php
}

/* quantity stepper; $min is the input floor (0 in the cart so a line can be cleared) */
function stepper($p, $name, $value, $min){ ?>
  <div class="step">
    <button type="button" onclick="bump(this,-<?= (int)$p['step'] ?>,<?= (int)$p['moq'] ?>)">−</button>
    <input type="number" name="<?= h($name) ?>" value="<?= (int)$value ?>" min="<?= (int)$min ?>" step="<?= (int)$p['step'] ?>" aria-label="Quantity">
    <button type="button" onclick="bump(this,<?= (int)$p['step'] ?>,<?= (int)$p['moq'] ?>)">+</button>
  </div>
<?php }

function set_tiles($sets){ ?>
  <div class="tiles"><?php foreach($sets as $st): $n = count(set_products($st['name'])); ?>
    <a href="<?= h(url('set', ['s'=>$st['slug']])) ?>"><?php if($st['code'] !== ''): ?><span class="n"><?= h($st['code']) ?></span><?php endif; ?>
      <b><?= h($st['name']) ?></b><span class="n"><?= $n ?> product<?= $n===1?'':'s' ?></span></a>
  <?php endforeach; ?></div>
<?php }

function guide_cards($guides){ ?>
  <div class="gcards"><?php foreach($guides as $g): ?>
    <a href="<?= h(url('guide', ['g'=>$g['slug']])) ?>"><h3><?= h($g['title']) ?></h3>
      <p><?= h(($g['seo_desc'] ?? '') ?: plain($g['body'] ?? '', 140)) ?></p><span>Read the guide →</span></a>
  <?php endforeach; ?></div>
<?php }

function steps_block($CONFIG){ ?>
  <div class="steps">
    <div><div class="n">01</div><h3>Price it openly</h3>
      <p>Every listing shows its full break ladder to everyone. No application, no approval wait, no quote round-trip for standard volumes.</p></div>
    <div><div class="n">02</div><h3>Place the order</h3>
      <p>Add to cart, enter your shipping address and pick a payment method. Stock is reserved in your name for <?= (int)$CONFIG['hold_hours'] ?> hours.</p></div>
    <div><div class="n">03</div><h3>We send payment details</h3>
      <p>Within <?= (int)$CONFIG['reply_hours'] ?> hours you get the details for your chosen method by email or text, with your invoice.</p></div>
    <div><div class="n">04</div><h3>Ship tracked from Japan</h3>
      <p>Payment clears, stock is allocated, and we dispatch within <?= (int)$CONFIG['hold_hours'] ?> hours by EMS, DHL or FedEx with tracking.</p></div>
  </div>
<?php }
