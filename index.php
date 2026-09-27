<?php
/* =============================================================
   FUDAKURA — Japanese Pokémon TCG for Belgium, in Dutch and French. PHP 7.4+.

   Day-to-day editing — products, prices, photos, shipping rates,
   payment methods, business details, FAQ, orders — is done in
   admin.php. You should not need to edit this file. See README.md.
   ============================================================= */

define('FK_ROOT', __DIR__);
@ini_set('display_errors', '0');                    /* never print PHP messages into pages (they break redirects) */
error_reporting(E_ALL & ~E_NOTICE & ~E_WARNING & ~E_DEPRECATED);
require FK_ROOT.'/inc/store.php';
require FK_ROOT.'/inc/bitcoin.php';
if(!ini_get('zlib.output_compression') && extension_loaded('zlib')) ob_start('ob_gzhandler');
start_session();

/* ---------------- DATA (edited in admin.php) ---------------- */
$RAW    = store_load();
$CONFIG = $RAW['settings'];   /* shared settings; the language's own text is added below */
$BASE   = rtrim(str_replace('\\', '/', dirname($_SERVER['SCRIPT_NAME'] ?? '/index.php')), '/').'/';

/* Settings → "This site has moved to": every visitor (and Google) goes to the same page on the new domain */
if(($moved = rtrim(trim((string)($CONFIG['moved_to'] ?? '')), '/')) !== '' && preg_match('#^https?://[^/]+$#i', $moved)
   && strcasecmp((string)parse_url($moved, PHP_URL_HOST), preg_replace('/:\d+$/', '', (string)($_SERVER['HTTP_HOST'] ?? ''))) !== 0){
  header('Location: '.$moved.($_SERVER['REQUEST_URI'] ?? '/'), true, 301); exit;
}

/* ---------------- LANGUAGE ----------------
   /nl/… and /fr/… with clean addresses, index.php?l=nl|fr without. Forms post to index.php?l=…
   The bare domain sends visitors to their language's home page (their last choice, else their browser's). */
$REQ_PATH = rawurldecode((string)parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH));
if(strpos($REQ_PATH, $BASE) === 0) $REQ_PATH = substr($REQ_PATH, strlen($BASE));
$REQ_PATH = trim($REQ_PATH, '/');
$seg0 = explode('/', $REQ_PATH)[0];
$gl = is_string($_GET['l'] ?? null) ? $_GET['l'] : '';
if(isset(LANGS[$gl]))        $LANG = $gl;
elseif(isset(LANGS[$seg0]))  $LANG = $seg0;
else {
  $LANG = isset(LANGS[$_SESSION['lang'] ?? '']) ? $_SESSION['lang'] : lang_from_browser();
  if($_SERVER['REQUEST_METHOD'] === 'GET' && !isset($_GET['p']) && ($REQ_PATH === '' || $REQ_PATH === 'index.php')){
    header('Vary: Accept-Language, Cookie');
    header('Location: '.$BASE.url('home'), true, 302); exit;
  }
}
$_SESSION['lang'] = $LANG;
$LOCALE = LANG_LOCALE[$LANG];

$STORE      = store_view($RAW, $LANG);
$CONFIG     = $STORE['settings'];
$CURRENCIES = $STORE['currencies'];
$CATEGORIES = $STORE['categories'];
$PRODUCTS   = array_values(array_filter($STORE['products'], fn($p)=>empty($p['hidden']) && isset($CATEGORIES[$p['cat']])));
$PAYMENTS   = array_filter($STORE['payments'], fn($m)=>!empty($m['enabled']) && (!btc_method($m) || btc_ready()));   /* Bitcoin only with a valid wallet address */
$COUNTRIES  = [];
foreach($STORE['countries'] as $cc=>$nm) $COUNTRIES[$cc] = country_name($cc, $nm);
$HOME_CC    = (string)array_key_first($COUNTRIES);   /* the first country in Settings: shipping examples and Google's shipping data */
$MIN_ORDER  = (float)$CONFIG['min_order_usd'];
$SERIES     = $STORE['series'] ?? [];
$COLLECTIONS= $STORE['collections'] ?? [];
$GUIDES     = $STORE['guides'] ?? [];
$INFO_PAGES = $STORE['pages'] ?? [];
$POST_URL   = 'index.php?l='.$LANG;   /* where forms post */

/* the store in another language, for links to the same page there */
function view_of($L){
  global $RAW, $STORE, $LANG;
  static $views = [];
  if($L === $LANG) return $STORE;
  return $views[$L] ?? ($views[$L] = store_view($RAW, $L));
}

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
  $c = $_GET['cur'] ?? $_SESSION['cur'] ?? array_key_first($CURRENCIES);
  if(!is_string($c) || !isset($CURRENCIES[$c])) $c = array_key_first($CURRENCIES);   /* the first currency in Settings */
  $_SESSION['cur'] = $c;
  return $c;
}

function money($usd){
  global $CURRENCIES;
  $m = $CURRENCIES[cur_code()];
  return fmt_amount($usd * $m['rate'], $m['dec'], $m['sym']);
}

/* whole units, for round numbers like the free-shipping threshold */
function money_whole($usd){
  global $CURRENCIES;
  $m = $CURRENCIES[cur_code()];
  return fmt_amount(round($usd * $m['rate']), 0, $m['sym']);
}

/* status words shoppers see */
function status_word($p){
  return ['in'=>t('In stock'), 'new'=>t('New'), 'low'=>t('Low stock'), 'preorder'=>t('Preorder'), 'soldout'=>t('Sold out')][$p['status']] ?? $p['status'];
}
function cond_word($c){ return ['Sealed'=>t('Sealed'), 'Graded'=>t('Graded'), 'Near Mint'=>'Near Mint', 'Lightly Played'=>'Lightly Played'][$c] ?? $c; }

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
   Clean addresses (Admin → Settings; needs .htaccess support, i.e. Apache or LiteSpeed), per language:
     /nl/  /nl/winkel  /nl/{category}  /nl/producten/{id}  /nl/sets/{set}  /nl/kaarten/{collection}  /nl/gidsen/{guide}  /nl/{page}
     /fr/  /fr/boutique  /fr/{category}  /fr/produits/{id}  /fr/series/{set}  /fr/cartes/{collection}  /fr/guides/{guide}  /fr/{page}
   Otherwise index.php?p=…&l=…  Links are written relative to <base href>, so the shop also works in a subfolder. */
/* site photos (assets/site): the home page image until you upload your own in Settings, and the link-preview image */
const SITE_HERO  = 'assets/site/pokemon-30th-celebration-elite-trainer-box.webp';
const SITE_SHARE = 'assets/site/share-30th-celebration.jpg';

function cat_slug($key, $L=null){ $c = view_of($L ?? lang())['categories'][$key] ?? []; return ($c['slug'] ?? '') ?: slugify(($c['label'] ?? '') ?: $key); }

function url($p, $extra=[], $L=null){
  global $CONFIG;
  $L = $L ?? lang();
  if(empty($CONFIG['pretty_urls'])){
    if($p === 'sitemap' || $p === 'paystatus') return 'index.php?'.http_build_query(['p'=>$p] + $extra);
    return 'index.php?'.http_build_query(($p === 'home' ? [] : ['p'=>$p]) + ['l'=>$L] + $extra);
  }
  $pull = function($k) use(&$extra){ $v = (string)($extra[$k] ?? ''); unset($extra[$k]); return $v; };
  if($p === 'sitemap' || $p === 'paystatus') return ($p === 'sitemap' ? 'sitemap.xml' : 'pay-status').($extra ? '?'.http_build_query($extra) : '');
  $P = LANG_PATHS[$L];
  switch($p){
    case 'home':       $path = ''; break;
    case 'catalog':    $c = $pull('cat'); $path = $c !== '' ? cat_slug($c, $L) : $P['catalog']; break;
    case 'product':    $path = $P['product'].'/'.rawurlencode($pull('id')); break;
    case 'set':
    case 'series':     $path = $P['sets'].'/'.rawurlencode($pull('s')); break;
    case 'collection': $path = $P['collection'].'/'.rawurlencode($pull('c')); break;
    case 'guide':      $path = $P['guides'].'/'.rawurlencode($pull('g')); break;
    case 'page':       $path = rawurlencode($pull('pg')); break;
    default:           $path = $P[$p] ?? '';
  }
  return $L.'/'.$path.($extra ? '?'.http_build_query($extra) : '');
}

/* absolute link for canonical tags, sitemaps and structured data */
function abs_url($p, $extra=[], $L=null){ global $CONFIG; $u = url($p, $extra, $L); return rtrim($CONFIG['domain'], '/').'/'.($u === './' ? '' : $u); }

function go_to($p, $extra=[]){ global $BASE; header('Location: '.$BASE.url($p, $extra)); exit; }

/* request path → page, for clean addresses */
function route_from_path(){
  global $REQ_PATH, $CATEGORIES, $LANG;
  $path = $REQ_PATH;
  if($path === '' || $path === 'index.php') return null;
  if($path === 'sitemap.xml') return ['p'=>'sitemap'];
  if($path === 'pay-status')  return ['p'=>'paystatus'];
  $seg = explode('/', $path);
  if(!isset(LANGS[$seg[0]])) return ['p'=>'notfound'];
  array_shift($seg);
  if(!$seg) return null;
  $P = LANG_PATHS[$LANG];
  if(count($seg) === 1){
    $one = $seg[0];
    foreach($CATEGORIES as $k=>$c){ if(cat_slug($k) === $one) return ['p'=>'catalog', 'cat'=>$k]; }
    $pages = array_flip($P);
    if(isset($pages[$one]) && !in_array($pages[$one], ['product','collection'], true)) return ['p'=>$pages[$one]];
    if(info_page($one)) return ['p'=>'page', 'pg'=>$one];
  } elseif(count($seg) === 2){
    [$a, $b] = $seg;
    if($a === $P['product'])    return ['p'=>'product', 'id'=>$b];
    if($a === $P['sets'])       return ['p'=>series_by_slug($b) ? 'series' : 'set', 's'=>$b];
    if($a === $P['collection']) return ['p'=>'collection', 'c'=>$b];
    if($a === $P['guides'])     return ['p'=>'guide', 'g'=>$b];
  }
  return ['p'=>'notfound'];
}

/* ---------------- sets, series, collections, guides ---------------- */
/* every set that has products in the shop, in admin order, with its page settings */
function sets_all(){
  global $PRODUCTS, $STORE;
  $names = [];
  foreach($PRODUCTS as $p){ if($p['set'] !== '') $names[$p['set']] = true; }
  /* (string): PHP turns a set called "151" into the number 151 when it is an array key */
  $order = array_map('strval', array_merge(array_keys($STORE['sets'] ?? []), array_keys($names)));
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

/* collections, guides and pages are found by their address (slug) or by their key, which is the same in both languages */
function find_in($list, $x){
  foreach($list as $e){ if(($e['slug'] ?? '') === $x) return $e; }
  foreach($list as $e){ if(($e['key'] ?? '') !== '' && $e['key'] === $x) return $e; }
  return null;
}
function collection_by_slug($slug){ global $COLLECTIONS; return find_in($COLLECTIONS, $slug); }
function collection_products($c){
  global $PRODUCTS;
  $terms = array_filter(array_map('trim', explode(',', lc($c['match'] ?? ''))));
  $ids = $c['ids'] ?? []; $cond = $c['cond'] ?? '';
  return array_values(array_filter($PRODUCTS, function($p) use($terms, $ids, $cond){
    if(in_array($p['id'], $ids, true)) return true;
    if($cond !== '' && ($p['cond'] ?? 'Sealed') !== $cond) return false;
    if(!$terms) return $cond !== '';
    foreach($terms as $t){ if(strpos(lc($p['name']), $t) !== false) return true; }
    return false;
  }));
}
function info_page($slug){ global $INFO_PAGES; foreach($INFO_PAGES as $pg){ if(($pg['slug'] ?? '') === $slug) return $pg; } return null; }
function page_by_key($k){ global $INFO_PAGES; return find_in($INFO_PAGES, $k); }
function page_url($k){ $pg = page_by_key($k); return $pg ? url('page', ['pg'=>$pg['slug']]) : null; }
function guide_by_slug($slug){ global $GUIDES; foreach($GUIDES as $g){ if(($g['slug'] ?? '') === $slug) return $g; } return null; }
function guide_by_key($k){ global $GUIDES; return find_in($GUIDES, $k); }
/* the same collection, guide or page in the other language (matched by key) */
function twin($list, $e){ if(($e['key'] ?? '') === '') return null; foreach($list as $x){ if(($x['key'] ?? '') === $e['key']) return $x; } return null; }

/* ---------------- admin-written text ----------------
   blank line = paragraph, "## " / "### " heading, "- " bullet, **bold**, [text](link) — see inc/content-nl.php */
function link_target($t){
  if(preg_match('#^(https?://|mailto:)#i', $t)) return $t;
  if(!preg_match('/^([a-z]+):([^#]+)(#[\w-]+)?$/', $t, $m)) return null;
  $to = link_page($m[1], $m[2]);
  return $to === null ? null : $to.($m[3] ?? '');
}
function link_page($kind, $slug){
  global $COLLECTIONS;
  $pages = ['home'=>'home', 'shop'=>'catalog', 'sets'=>'sets', 'guides'=>'guides', 'faq'=>'faq', 'shipping'=>'shipping',
            'payment'=>'payment', 'how'=>'how', 'contact'=>'contact'];
  switch($kind){
    case 'product':  return url('product', ['id'=>$slug]);
    case 'category': return url('catalog', ['cat'=>$slug]);
    case 'set':      return url(series_by_slug($slug) ? 'series' : 'set', ['s'=>$slug]);
    case 'cards':    $c = find_in($COLLECTIONS, $slug); return $c ? url('collection', ['c'=>$c['slug']]) : null;
    case 'guide':    $g = guide_by_slug($slug) ?: guide_by_key($slug); return $g ? url('guide', ['g'=>$g['slug']]) : null;
    case 'page':     if(isset($pages[$slug])) return url($pages[$slug]);
                     $pg = info_page($slug) ?: page_by_key($slug);
                     return $pg ? url('page', ['pg'=>$pg['slug']]) : ($slug === 'returns' ? url('shipping').'#'.slugify(t('Returns')) : null);
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
  $html = ''; $para = []; $list = '';   /* '', 'ul' or 'ol' */
  $flush = function() use(&$html, &$para, &$list){
    if($para){ $html .= '<p>'.rich_inline(implode(' ', $para)).'</p>'; $para = []; }
    if($list){ $html .= "</$list>"; $list = ''; }
  };
  foreach(explode("\n", str_replace("\r", '', fill((string)$text))) as $line){
    $line = trim($line);
    if($line === ''){ $flush(); continue; }
    if(preg_match('/^(#{2,3})\s+(.+)$/', $line, $m)){ $flush(); $t = strlen($m[1]); $html .= "<h$t id=\"".h(slugify($m[2]))."\">".rich_inline($m[2])."</h$t>"; continue; }
    if(preg_match('/^(?:([-*])|\d{1,2}[.)])\s+(.+)$/', $line, $m)){   /* "- item" bullets, "1. step" numbered */
      $want = $m[1] !== '' ? 'ul' : 'ol';
      if($para){ $html .= '<p>'.rich_inline(implode(' ', $para)).'</p>'; $para = []; }
      if($list !== $want){ if($list) $html .= "</$list>"; $html .= "<$want>"; $list = $want; }
      $html .= '<li>'.rich_inline($m[2]).'</li>'; continue;
    }
    if($list){ $html .= "</$list>"; $list = ''; }
    $para[] = $line;
  }
  $flush();
  return $html;
}
/* product descriptions: the first paragraph is the summary under the title, the rest is "About this product" */
function desc_parts($text){
  $parts = preg_split('/\n\s*\n/', trim(str_replace("\r", '', (string)$text)), 2);
  return [trim($parts[0] ?? ''), trim($parts[1] ?? '')];
}

/* ---------------- internal links ----------------
   Guides are linked by their key, which is the same in Dutch and French (a guide missing in one language is skipped).
   The link text is each guide's "anchor" (the keyword it targets), else its title. */
const GUIDE_RELATED = [
  'prices'=>['value','buy','japanese','boosters'],   'value'=>['prices','fakes','collect','japanese'],
  'buy'=>['prices','boosters','japanese','releases'], 'japanese'=>['buy','sets','boosters','fakes'],
  'releases'=>['sets','boosters','japanese','buy'],   'sets'=>['releases','japanese','prices','boosters'],
  'boosters'=>['prices','buy','japanese','sets'],     'tcg'=>['decks','codes','boosters','sets'],
  'decks'=>['tcg','codes','boosters','buy'],          'collect'=>['value','prices','fakes','sets'],
  'fakes'=>['value','japanese','buy','prices'],       'template'=>['tcg','fakes','collect','decks'],
  'codes'=>['tcg','decks','japanese','buy'],
];
const PAGE_GUIDES = [
  'cat:boxes'=>['boosters','japanese','prices','releases','sets'],
  'cat:etb'=>['boosters','tcg','decks','buy','releases'],
  'cat:premium'=>['decks','tcg','boosters','buy','codes'],
  'cat:singles'=>['prices','value','fakes','collect','japanese'],
  'cat:accessories'=>['collect','template','tcg','decks','value'],
  'shop'=>['buy','prices','japanese','boosters','releases','sets'],
  'set'=>['sets','prices','releases','japanese'],
  'series'=>['sets','releases','tcg','prices'],
  'coll'=>['prices','value','fakes','collect'],
  'faq'=>['buy','prices','japanese','fakes','tcg','releases','boosters','value'],
  'home'=>['prices','buy','japanese','releases','boosters','tcg'],
];
/* where each group of guides sends readers to shop */
const GUIDE_GROUP = ['prices'=>'collect','value'=>'collect','collect'=>'collect','fakes'=>'collect',
  'tcg'=>'play','decks'=>'play','template'=>'play','codes'=>'play'];
function guide_shop($key){
  $g = GUIDE_GROUP[$key] ?? 'buy';
  if($g === 'collect') return t('Shop [rare Japanese Pokémon cards](category:singles), [PSA graded cards](cards:psa) and [Charizard cards](cards:charizard), shipped from Japan to Belgium.');
  if($g === 'play')    return t('Shop [Elite Trainer Boxes](category:etb), [starter decks and boxes](category:premium) and [sleeves, binders and playmats](category:accessories), shipped from Japan to Belgium.');
  return t('Shop [Japanese booster boxes](category:boxes), [Elite Trainer Boxes](category:etb) and [rare single cards](category:singles), shipped from Japan to Belgium.');
}

function guide_anchor($g){ return fill(($g['anchor'] ?? '') ?: $g['title']); }
function guides_list($keys){ return array_values(array_filter(array_map('guide_by_key', $keys))); }
function guides_for($cat){ return guides_list(PAGE_GUIDES['cat:'.$cat] ?? PAGE_GUIDES['shop']); }
/* related guides: the hand-picked ones first, topped up with the next guides in the list */
function guides_related($g, $n=4){
  global $GUIDES;
  $out = guides_list(GUIDE_RELATED[$g['key'] ?? ''] ?? []);
  $at = (int)array_search($g['slug'], array_column($GUIDES, 'slug'), true);
  for($i = 1; $i < count($GUIDES) && count($out) < $n; $i++){
    $x = $GUIDES[($at + $i) % count($GUIDES)];
    if($x['slug'] !== $g['slug'] && !in_array($x['slug'], array_column($out, 'slug'), true)) $out[] = $x;
  }
  return array_slice($out, 0, $n);
}
/* a row of guide links, e.g. at the foot of a category or set page */
function guide_links($keys, $title, $extra=[]){
  $gs = guides_list($keys); if(!$gs && !$extra) return; ?>
  <div class="glinks"><h2 class="sub2"><?= h($title) ?></h2><div class="gchips">
    <?php foreach($gs as $g): ?><a href="<?= h(url('guide', ['g'=>$g['slug']])) ?>"><?= h(guide_anchor($g)) ?> →</a><?php endforeach; ?>
    <?php foreach($extra as [$label, $href]): ?><a href="<?= h($href) ?>"><?= h($label) ?> →</a><?php endforeach; ?>
  </div></div>
<?php }

/* plain text of admin-written text, for meta descriptions */
function plain($text, $len=155){
  $t = trim(preg_replace('/\s+/u', ' ', preg_replace(['/\[([^\]]+)\]\([^)]*\)/', '/\*\*|^#+\s*/m'], ['$1', ''], fill((string)$text))));
  if(!preg_match('/^.{'.($len + 1).'}/us', $t)) return $t;
  $cut = preg_match('/^(.{1,'.$len.'})\s/us', $t, $m) ? $m[1] : preg_replace('/^(.{'.$len.'}).*$/us', '$1', $t);
  return rtrim($cut, ' ,;:-').'…';
}

/* admin-editable text: fills {reply_hours} {hold_hours} {countries} {min_order} {brand} {company} {address} {email}
   {free_ship} {standard} {express} {standard_days} {express_days} {kanji} */
function fill($text){
  global $CONFIG, $COUNTRIES, $MIN_ORDER, $STORE;
  $sm = ship_methods($STORE);
  return strtr($text, ['{reply_hours}'=>(int)$CONFIG['reply_hours'], '{hold_hours}'=>(int)$CONFIG['hold_hours'],
                       '{countries}'=>count($COUNTRIES), '{min_order}'=>money($MIN_ORDER), '{brand}'=>$CONFIG['brand'],
                       '{company}'=>$CONFIG['legal_name'], '{address}'=>$CONFIG['address'], '{email}'=>$CONFIG['email'], '{kanji}'=>$CONFIG['kanji'],
                       '{free_ship}'=>money_whole(free_ship_usd($STORE)),
                       '{standard}'=>$sm['standard']['label'], '{express}'=>$sm['express']['label'],
                       '{standard_days}'=>days_text($sm['standard']['days']), '{express_days}'=>days_text($sm['express']['days'])]);
}
/* "5–9" → "5–9 working days" in the shopper's language (text that already has words stays as it is) */
function days_text($d){ return preg_match('/[a-z]/i', (string)$d) ? (string)$d : t('%s working days', $d); }

/* returns which emails went out, so a failure shows up in the admin instead of vanishing.
   The shop's copy is in English; the customer's is in the language they ordered in. */
function send_order_mail($order){
  global $CONFIG;
  $body  = "NEW ORDER  {$order['ref']}  (customer's language: ".LANGS[$order['lang']].")\n";
  $body .= str_repeat('=',50)."\n\n";
  $body .= "PAYMENT METHOD CHOSEN: {$order['payment_label']}\n";
  if(!empty($order['btc'])){
    $b = $order['btc'];
    $body .= !empty($b['sats'])
      ? "-> Paid by Bitcoin on the site: ".btc_amount($b['sats'])." BTC to {$b['address']}\n   (1 BTC = \${$b['rate']} from {$b['rate_source']}). You'll get a receipt email when the payment\n   is seen on the blockchain, and another when it confirms. No need to send payment details.\n\n"
      : "-> Paid by Bitcoin on the site to {$b['address']}. The BTC price feeds didn't answer, so the customer's\n   order page will show the amount once they do. You'll get a receipt email when the payment is seen.\n\n";
  } else $body .= "-> Send payment details to this customer manually (write to them in ".LANGS[$order['lang']].").\n\n";
  $body .= "CONTACT\n";
  $body .= "  Name:    {$order['name']}\n";
  $body .= "  Company: {$order['company']}\n";
  $body .= "  Email:   {$order['email']}\n";
  $body .= "  Phone:   {$order['phone']}\n\n";
  $body .= "SHIP TO\n";
  $body .= "  {$order['address1']}\n";
  if($order['address2']) $body .= "  {$order['address2']}\n";
  $body .= "  {$order['postcode']} {$order['city']}".($order['region'] !== '' ? " ({$order['region']})" : '')."\n";
  $body .= "  {$order['country_name']}\n\n";
  $body .= "ITEMS\n";
  foreach($order['lines'] as $l){
    $body .= sprintf("  %-46s %4d x %10s = %10s\n",
      $l['name'].' ('.$l['sku'].')', $l['qty'], $l['unit'], $l['total']);
  }
  $body .= "\n  GOODS:    {$order['goods']}\n";
  $body .= "  SHIPPING: ".((float)($order['shipping_usd'] ?? 1) == 0 ? 'Free' : $order['shipping'])." ({$order['ship_zone']}, {$order['ship_label']})\n";
  $body .= "  TOTAL:    {$order['total']} ({$order['currency']})\n";
  $body .= "  No VAT or other tax is added.\n\n";
  if($order['notes']) $body .= "NOTES\n  {$order['notes']}\n\n";
  $body .= "Submitted: {$order['time']}\n";

  $to_shop = shop_mail($CONFIG['order_email'], "New order {$order['ref']} — {$order['payment_label']}", $body, $order['email']);

  /* customer confirmation, with the items and delivery address so they can check them */
  $c  = t('Thank you, we have your order.')."\n\n";
  $c .= t('Order reference: %s', $order['ref'])."\n\n";
  $c .= uc(t('Your order'))."\n";
  foreach($order['lines'] as $l) $c .= "  {$l['qty']} × {$l['name']} — {$l['total']}\n";
  $c .= "\n".t('Goods: %s', $order['goods'])."\n";
  $c .= t('Shipping: %s', ((float)($order['shipping_usd'] ?? 1) == 0 ? t('Free') : $order['shipping']).' — '.$order['ship_label'])."\n";
  $c .= t('Order total: %s', $order['total'])."\n";
  $c .= t('Payment method: %s', $order['payment_label'])."\n\n";
  $c .= uc(t('Delivery address'))."\n  {$order['name']}\n  {$order['address1']}\n".($order['address2'] ? "  {$order['address2']}\n" : '')
      ."  {$order['postcode']} {$order['city']}\n  {$order['country_name']}\n\n";
  if(!empty($order['btc'])){
    $b = $order['btc'];
    $c .= uc(t('Pay with Bitcoin'))."\n";
    if(!empty($b['sats'])){
      $c .= t('Send exactly: %s BTC', btc_amount($b['sats']))."\n";
      $c .= t('To address: %s', $b['address'])."\n";
      $c .= t('This amount is held until %s UTC. After that, your order page shows a new amount at the current rate.', gmdate('H:i', $b['expires']))."\n\n";
    } else $c .= t('Your order page shows the exact BTC amount and a QR code.')."\n\n";
    $c .= t('Your order page (QR code, amount and payment status):')."\n".btc_pay_link($order)."\n\n";
    $c .= t('We email your receipt as soon as your payment reaches the blockchain, and dispatch within %d hours of it confirming, from Japan with tracking.', (int)$CONFIG['hold_hours'])."\n\n";
  } else {
    $c .= uc(t('What happens next'))."\n";
    $c .= t('Your stock is reserved for %1$d hours. Within %2$d hours we email you the payment details for the method you chose, with your invoice.', (int)$CONFIG['hold_hours'], (int)$CONFIG['reply_hours'])."\n";
    $c .= t('Quote %s with your payment so we can match it to your order.', $order['ref'])."\n";
    $c .= t('Once payment has arrived, we dispatch within %d hours from Japan, with tracking.', (int)$CONFIG['hold_hours'])."\n\n";
  }
  $c .= t('We add no VAT or other tax: the total above is what you pay us.')."\n\n";
  $c .= t('Questions? %s', $CONFIG['email'])."\n";
  $c .= "{$CONFIG['legal_name']} — {$CONFIG['address']}\n";
  $to_customer = shop_mail($order['email'], t('Order %1$s received — %2$s', $order['ref'], $CONFIG['brand']), $c, $CONFIG['email']);
  /* a copy of the customer's confirmation, so the order inbox has exactly what they were sent */
  shop_mail($CONFIG['order_email'], "Confirmation sent: order {$order['ref']}", "This order confirmation was sent to {$order['email']}"
    .($to_customer ? '' : ' — but sending FAILED, so contact the customer yourself').":\n\n".str_repeat('-', 50)."\n\n".$c, $order['email']);
  return ['shop'=>$to_shop, 'customer'=>$to_customer];
}

/* ---------------- ACTIONS (POST / redirect) ---------------- */
/* drop cart lines for products since removed, hidden or sold out; re-snap quantities to MOQ/step */
foreach(cart() as $id=>$qty){
  $p = product($id);
  $fit = $p ? fit_qty($p, $qty) : 0;
  if($fit === $qty) continue;
  if($fit) $_SESSION['cart'][$id] = $fit;
  else { unset($_SESSION['cart'][$id]); flash(t('%s is no longer available and was removed from your order.', $p ? $p['name'] : t('A product'))); }
}

$errors = [];
if($_SERVER['REQUEST_METHOD'] === 'POST'){
  $action = $_POST['action'] ?? '';

  if($action === 'add'){
    $p = product($_POST['id'] ?? '');
    if($p){
      $qty = fit_qty($p, (int)($_POST['qty'] ?? 0) ?: $p['moq']);
      if($qty) $_SESSION['cart'][$p['id']] = $qty;
      else     flash(t('%s is sold out.', $p['name']));
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
    if($bot)                                             $errors[] = t('We could not submit your order. Please check the form and place it again.');
    if(!cart_lines())                                    $errors[] = t('Your order is empty.');
    if($f['name'] === '')                                $errors[] = t('Enter the name the order ships to.');
    if(!filter_var($f['email'], FILTER_VALIDATE_EMAIL))  $errors[] = t('Enter a valid email address.');
    if($f['phone'] === '')                               $errors[] = t('Enter a phone number: the carrier needs it for delivery.');
    if(!isset($COUNTRIES[$f['country']]))                $errors[] = t('Select your country.');
    if($f['address1'] === '')                            $errors[] = t('Enter your street and house number.');
    if($f['postcode'] === '')                            $errors[] = t('Enter your postcode.');
    if($f['city'] === '')                                $errors[] = t('Enter your town or city.');
    if(!isset($PAYMENTS[$f['payment']]))                 $errors[] = t('Choose how you want to pay.');
    elseif(isset($COUNTRIES[$f['country']]) && !payment_ok($f['payment'], $f['country']))
      $errors[] = t('%1$s is not available for %2$s. Choose another payment method.', $PAYMENTS[$f['payment']]['label'], $COUNTRIES[$f['country']]);
    if(empty($_POST['agree']))                           $errors[] = t('Please accept the terms of sale and the shipping & returns policy.');

    /* delivery option: Standard or Express */
    $SHIPM = ship_methods($STORE);
    $method = in_array($_POST['ship_method'] ?? '', SHIP_METHODS, true) ? $_POST['ship_method'] : 'standard';
    $f['ship_method'] = $method;

    /* minimum order value counts goods plus shipping */
    if(isset($COUNTRIES[$f['country']]) && cart_lines()){
      $ship  = shipping_usd($STORE, $f['country'], cart_weight(), $method, cart_total());
      $grand = round(cart_total() + $ship, 2);
      if($grand < $MIN_ORDER)
        $errors[] = t('The minimum order is %1$s including shipping. Your total is %2$s, so add %3$s more to place this order.', money($MIN_ORDER), money($grand), money($MIN_ORDER - $grand));
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
        'lang'          => $LANG,
        'country_name'  => $COUNTRIES[$f['country']],
        'payment_label' => $PAYMENTS[$f['payment']]['label'],
        'ship_zone'     => ship_zone($STORE, $f['country'])['name'],
        'ship_label'    => $SHIPM[$method]['label'].' ('.days_text($SHIPM[$method]['days']).')',
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
        'free_shipping' => free_shipping($STORE, $goods),
      ];
      if(btc_method($PAYMENTS[$f['payment']])){ $order['btc'] = ['address'=>btc_settings()['address'], 'quotes'=>[]]; btc_quote($order); }
      if(!order_save($order)) error_log("shop: could not save order {$order['ref']} to data/orders");
      $sent = send_order_mail($order);
      $order['mail_shop'] = $sent['shop']; $order['mail_customer'] = $sent['customer'];
      order_save($order);
      if(!$sent['shop']) error_log("shop: order email for {$order['ref']} to {$CONFIG['order_email']} failed");
      $_SESSION['last_order'] = $order;
      $_SESSION['cart'] = [];
      if(!empty($order['btc'])) go_to('pay', ['ref'=>$order['ref'], 'k'=>order_key($order['ref'])]);
      go_to('received');
    }
    $form = $f;
  }

  /* Bitcoin: the customer pastes the transaction ID of their payment */
  if($action === 'btc_txid'){
    $ref = is_string($_POST['ref'] ?? null) ? $_POST['ref'] : ''; $k = is_string($_POST['k'] ?? null) ? $_POST['k'] : '';
    $o = order_key_ok($ref, $k) ? order_load($ref) : null;
    $txid = strtolower(trim(is_string($_POST['txid'] ?? null) ? $_POST['txid'] : ''));
    if(preg_match('#/tx/([0-9a-f]{64})#i', $txid, $m)) $txid = strtolower($m[1]);   /* a pasted explorer link works too */
    if(!$o || empty($o['btc'])) go_to('home');
    if(!empty($o['btc']['txid'])) flash(t('We already have your payment for this order. Thank you!'));
    elseif(($o['btc']['tries'] ?? 0) >= 8) flash(t('Too many attempts. Please email %s with your order reference and transaction ID.', $CONFIG['email']));
    elseif(!preg_match('/^[0-9a-f]{64}$/', $txid)) flash(t('That doesn’t look like a transaction ID. It’s 64 letters and numbers, shown in your wallet’s payment details.'));
    else {
      $o['btc']['tries'] = ($o['btc']['tries'] ?? 0) + 1;
      $claims = btc_claims();
      $r = isset($claims[$txid]) ? false : btc_lookup_tx($txid, $o['btc']['address']);
      if($r === false) flash(t('That transaction has already been matched to an order. If you think that’s a mistake, email %s.', $CONFIG['email']));
      elseif($r === null){      /* explorers didn't answer: keep it, and check it again on the next status check */
        $o['btc']['reported'] = $txid;
        shop_mail($CONFIG['order_email'], "Bitcoin payment reported — {$o['ref']}",
          "The customer says they've paid order {$o['ref']} and gave this transaction ID:\n$txid\n".btc_tx_url($txid)."\n\nThe block explorers couldn't be reached to check it. The order page will keep trying, or check it yourself in the admin.\n", $o['email']);
        flash(t('Thanks! We’ve noted your transaction and will confirm it as soon as the Bitcoin network check comes back.'));
      }
      elseif(!$r['exists']) flash(t('We can’t find that transaction on the Bitcoin network yet. If you’ve only just sent it, wait a minute and try again.'));
      elseif(!$r['found']) flash(t('That transaction doesn’t send Bitcoin to our address %s. Check you copied the transaction for this payment.', $o['btc']['address']));
      else {
        $before = btc_state($o);
        btc_attach($o, $r['found'], $r['tip']);
        $after = btc_state($o);
        if($after === 'seen') btc_mail($o, 'received');
        elseif($after !== $before){ if($after === 'confirmed' && in_array($o['status'], ['new','invoiced'], true)) $o['status'] = 'paid'; btc_mail($o, $after); }
        flash(t('Payment found. Thank you! Your receipt is on its way by email.'));
      }
      order_save($o);
    }
    go_to('pay', ['ref'=>$ref, 'k'=>$k]);
  }
}

/* ---------------- ROUTE ---------------- */
$from_path = isset($_GET['p']) ? null : route_from_path();
if($from_path) $_GET = $from_path + $_GET;
$page = $_GET['p'] ?? 'home';
if(!is_string($page)) $page = 'home';
if($_SERVER['REQUEST_METHOD']==='POST' && ($_POST['action']??'')==='order' && $errors) $page = 'checkout';
$PAGES = ['home','catalog','product','sets','set','series','collection','guides','guide','page','cart','checkout','received',
          'how','shipping','payment','faq','contact','sitemap','pay','paystatus','notfound'];
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

$prod = $set = $series = $coll = $guide = $info = null;
if($page === 'product')    $prod   = product($gs('id'));
if($page === 'set')        $set    = set_by_slug($gs('s'));
if($page === 'series')     $series = series_by_slug($gs('s'));
if($page === 'collection') $coll   = collection_by_slug($gs('c'));
if($page === 'guide')      $guide  = guide_by_slug($gs('g'));
if($page === 'page')       $info   = info_page($gs('pg'));
if(($page === 'product' && !$prod) || ($page === 'set' && !$set) || ($page === 'series' && !$series)
   || ($page === 'collection' && !$coll) || ($page === 'guide' && !$guide) || ($page === 'page' && !$info)) $page = 'notfound';

/* Bitcoin order page, opened with the key in the customer's link. The page itself never waits on the
   blockchain: it polls pay-status, which checks for the payment (at most every 15 seconds per order). */
$pay = null;
if($page === 'pay' || $page === 'paystatus'){
  $pay = order_key_ok($gs('ref'), $gs('k')) ? order_load($gs('ref')) : null;
  if(!$pay || empty($pay['btc'])){ $pay = null; if($page === 'pay') $page = 'notfound'; }
  elseif($page === 'pay'){ if(btc_quote($pay)) order_save($pay); }
  else {
    $changed = btc_quote($pay);
    if(btc_sync($pay)) $changed = true;
    if($changed) order_save($pay);
  }
}
if($page === 'paystatus'){
  header('Content-Type: application/json; charset=utf-8'); header('Cache-Control: no-store'); header('X-Robots-Tag: noindex');
  if(!$pay){ http_response_code(404); echo '{}'; exit; }
  echo json_encode(['state'=>btc_state($pay), 'quoted'=>(int)($pay['btc']['quoted'] ?? 0), 'status'=>$pay['status'],
                    'conf'=>(int)($pay['btc']['confirmations'] ?? 0), 'need'=>btc_settings()['confs']]);
  exit;
}
if($page === 'received' && !empty($_SESSION['last_order']['btc'])) go_to('pay', ['ref'=>$_SESSION['last_order']['ref'], 'k'=>order_key($_SESSION['last_order']['ref'])]);
if($page === 'notfound') http_response_code(404);

/* with clean addresses on, old index.php?p=… links (and /nl/winkel?cat=…) move permanently to the clean address */
if(!empty($CONFIG['pretty_urls']) && $_SERVER['REQUEST_METHOD'] === 'GET' && !in_array($page, ['notfound','sitemap','paystatus'], true)
   && ((!$from_path && (isset($_GET['p']) || isset($_GET['l']))) || ($page === 'catalog' && $from_path && !isset($from_path['cat']) && $cat !== ''))){
  $extra = $_GET; unset($extra['p'], $extra['l']);
  header('Location: '.$BASE.url($page, $extra), true, 301); exit;
}

/* ---------------- the same page in each language (hreflang, the language switch, the sitemap) ---------------- */
function page_in($L, $pg, $args){
  switch($pg){
    case 'collection': case 'guide': case 'page':
      $sec = ['collection'=>'collections', 'guide'=>'guides', 'page'=>'pages'][$pg];
      $arg = ['collection'=>'c', 'guide'=>'g', 'page'=>'pg'][$pg];
      $here = find_in($GLOBALS['STORE'][$sec] ?? [], $args[$arg] ?? '');
      $there = $here ? ($L === lang() ? $here : twin(view_of($L)[$sec] ?? [], $here)) : null;
      return $there ? url($pg, [$arg=>$there['slug']], $L) : null;
    case 'notfound': case 'pay': case 'received': return null;
    default: return url($pg, $args, $L);
  }
}

/* ---------------- sitemap.xml (index.php?p=sitemap): every page in both languages, each pointing at its twin ---------------- */
if($page === 'sitemap'){
  $abs_img = fn($f)=>rtrim($CONFIG['domain'], '/').'/'.$f;
  $d = rtrim($CONFIG['domain'], '/').'/';
  $groups = [['home', []], ['catalog', []]];
  foreach($CATEGORIES as $k=>$c) $groups[] = ['catalog', ['cat'=>$k]];
  foreach($PRODUCTS as $p) $groups[] = ['product', ['id'=>$p['id']], array_map($abs_img, photos($p['id']))];
  $groups[] = ['sets', []];
  foreach($SERIES as $k=>$sr){ if(series_sets($k)) $groups[] = ['series', ['s'=>$sr['slug']]]; }
  foreach(sets_all() as $st) $groups[] = ['set', ['s'=>$st['slug']]];
  foreach(['how','shipping','payment','faq','contact'] as $pg) $groups[] = [$pg, []];
  $groups[] = ['guides', []];
  $urls = [];
  foreach($groups as $g){
    $alts = [];
    foreach(array_keys(LANGS) as $L) $alts[$L] = $d.page_in($L, $g[0], $g[1]);
    $urls[] = [$alts, $g[2] ?? [], null, $g[0] === 'home'];   /* the home pages' default is the bare domain, which picks the visitor's language */
  }
  /* collections, guides and pages: each language's own, paired with its twin when there is one */
  foreach(['collections'=>['collection','c'], 'guides'=>['guide','g'], 'pages'=>['page','pg']] as $sec=>[$pg, $arg]){
    foreach(array_keys(LANGS) as $L) foreach(view_of($L)[$sec] ?? [] as $e){
      $alts = [$L=>$d.url($pg, [$arg=>$e['slug']], $L)];
      foreach(array_keys(LANGS) as $L2) if($L2 !== $L && ($tw = twin(view_of($L2)[$sec] ?? [], $e))) $alts[$L2] = $d.url($pg, [$arg=>$tw['slug']], $L2);
      $urls[] = [$alts, [], $L];
    }
  }
  $x = fn($v)=>htmlspecialchars($v, ENT_XML1|ENT_QUOTES, 'UTF-8');
  $mod = gmdate('Y-m-d', @filemtime(data_file('store')) ?: time());
  header('Content-Type: application/xml; charset=utf-8');
  echo '<?xml version="1.0" encoding="UTF-8"?>'."\n".'<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9" xmlns:xhtml="http://www.w3.org/1999/xhtml" xmlns:image="http://www.google.com/schemas/sitemap-image/1.1">'."\n";
  foreach($urls as $u){
    [$alts, $imgs] = $u;
    foreach(!empty($u[2]) ? [$u[2]=>$alts[$u[2]]] : $alts as $L=>$loc){
      echo '  <url><loc>'.$x($loc).'</loc><lastmod>'.$mod.'</lastmod>';
      if(count($alts) > 1){
        foreach($alts as $L2=>$href) echo '<xhtml:link rel="alternate" hreflang="'.LANG_LOCALE[$L2].'" href="'.$x($href).'"/>';
        echo '<xhtml:link rel="alternate" hreflang="x-default" href="'.$x(!empty($u[3]) ? $d : ($alts[array_key_first(LANGS)] ?? $loc)).'"/>';
      }
      foreach($imgs as $img) echo '<image:image><image:loc>'.$x($img).'</image:loc></image:image>';
      echo "</url>\n";
    }
  }
  echo "</urlset>\n";
  exit;
}

/* ---------------- titles, descriptions, headings, breadcrumbs (SEO) ---------------- */
$cinfo = $cat ? $CATEGORIES[$cat] : null;
$h1 = ''; $crumbs = [[t('Home'), 'home', []]];
$fixed = [
  'cart'     => [t('Your order'), t('Your order')],
  'checkout' => [t('Checkout'), t('Checkout')],
  'received' => [t('Order received'), ''],
  'how'      => [t('How to order Japanese Pokémon cards'), t('How ordering works')],
  'shipping' => [t('Shipping & Returns — Pokémon cards from Japan to Belgium'), t('Shipping & Returns')],
  'pay'      => [t('Pay for your order'), ''],
  'payment'  => [t('Payment methods: Bitcoin and crypto'), t('Payment methods')],
  'faq'      => [t('FAQ — Buying Japanese Pokémon cards in Belgium'), t('Frequently asked questions')],
  'contact'  => [t('Contact'), t('Contact')],
  'notfound' => [t('Page not found'), t('Page not found')],
];
$page_desc = '';
switch($page){
  case 'home':
    $page_title = ($CONFIG['home_seo_title'] ?? '') ?: t('Japanese Pokémon cards in Belgium');
    $page_desc  = ($CONFIG['home_seo_desc'] ?? '') ?: t('Japanese Pokémon cards shipped from Japan to Belgium: booster boxes, Elite Trainer Boxes, rare cards and accessories.');
    $crumbs = [];
    break;
  case 'catalog':
    $crumbs[] = [t('Shop'), $cinfo ? 'catalog' : '', []];
    if($cinfo){
      $crumbs[] = [$cinfo['label'], '', []];
      $page_title = ($cinfo['seo_title'] ?? '') ?: $cinfo['label'];
      $page_desc  = ($cinfo['seo_desc'] ?? '') ?: plain($cinfo['blurb']);
      $h1 = ($cinfo['h1'] ?? '') ?: $cinfo['label'];
    } else {
      $page_title = t('Buy Pokémon cards: Japanese booster boxes, ETBs & rare cards');
      $page_desc  = t('Shop Japanese Pokémon cards: sealed booster boxes, Elite Trainer Boxes, collection boxes, rare single cards and accessories, shipped from Japan to Belgium.');
      $h1 = t('All Japanese Pokémon cards');
    }
    if($q !== '') $h1 = t('Results for “%s”', $q);
    elseif(!$cinfo && $f_set !== '') $h1 = $f_set;
    break;
  case 'product':
    $pset = $prod['set'] !== '' ? set_of($prod['set']) : null;
    $crumbs[] = [$CATEGORIES[$prod['cat']]['label'], 'catalog', ['cat'=>$prod['cat']]];
    if($pset) $crumbs[] = [$pset['name'], 'set', ['s'=>$pset['slug']]];
    $crumbs[] = [$prod['name'], '', []];
    $page_title = ($prod['seo_title'] ?? '') ?: $prod['name'];
    $from = unit_price($prod, $prod['moq']);
    $page_desc  = ($prod['seo_desc'] ?? '') ?: plain(desc_parts($prod['desc'])[0], 105).' '.t('From %s each, shipped from Japan to Belgium.', money($from));
    break;
  case 'sets':
    $crumbs[] = [t('Sets'), '', []];
    $page_title = t('Pokémon card sets: Mega Evolution & Scarlet & Violet (Japanese)');
    $page_desc  = t('Every Japanese Pokémon card set we stock, from the Mega Evolution series back to Scarlet & Violet favourites like 151 and Terastal Festival ex.');
    $h1 = t('Pokémon card sets');
    break;
  case 'series':
    $crumbs[] = [t('Sets'), 'sets', []]; $crumbs[] = [$series['name'], '', []];
    $page_title = ($series['seo_title'] ?? '') ?: $series['name'];
    $page_desc  = ($series['seo_desc'] ?? '') ?: plain($series['intro'] ?? '');
    $h1 = ($series['h1'] ?? '') ?: $series['name'];
    break;
  case 'set':
    $sser = $SERIES[$set['series']] ?? null;
    $crumbs[] = [t('Sets'), 'sets', []];
    if($sser) $crumbs[] = [$sser['name'], 'series', ['s'=>$sser['slug']]];
    $crumbs[] = [$set['name'], '', []];
    $label = $set['name'].($set['code'] !== '' ? ' ('.$set['code'].')' : '');
    $page_title = $set['seo_title'] ?: t('%s: Japanese booster boxes & cards', $label);
    $page_desc  = $set['seo_desc'] ?: (plain($set['intro']) ?: t('Japanese %s booster boxes and cards, shipped from Japan to Belgium.', $set['name']));
    $h1 = t('%s — Japanese Pokémon cards', $label);
    break;
  case 'collection':
    $crumbs[] = [t('Shop'), 'catalog', []]; $crumbs[] = [$coll['title'], '', []];
    $page_title = ($coll['seo_title'] ?? '') ?: $coll['title'];
    $page_desc  = ($coll['seo_desc'] ?? '') ?: plain($coll['intro'] ?? '');
    $h1 = ($coll['h1'] ?? '') ?: $coll['title'];
    break;
  case 'guides':
    $crumbs[] = [t('Guides'), '', []];
    $page_title = t('Pokémon card guides: prices, sets, releases & how to play');
    $page_desc  = t('Pokémon card guides: prices and values, Japanese cards, new releases, set lists, how to play, and how to spot fakes.');
    $h1 = t('Pokémon card guides');
    break;
  case 'page':
    $crumbs[] = [$info['title'], '', []];
    $page_title = ($info['seo_title'] ?? '') ?: $info['title'];
    $page_desc  = ($info['seo_desc'] ?? '') ?: plain($info['body'] ?? '');
    $h1 = $info['title'];
    break;
  case 'guide':
    $crumbs[] = [t('Guides'), 'guides', []]; $crumbs[] = [$guide['title'], '', []];
    $page_title = ($guide['seo_title'] ?? '') ?: $guide['title'];
    $page_desc  = ($guide['seo_desc'] ?? '') ?: plain($guide['body'] ?? '');
    $h1 = $guide['title'];
    break;
  default:
    [$page_title, $h1] = $fixed[$page];
    if($page === 'checkout') $crumbs[] = [t('Your order'), 'cart', []];
    if($h1 !== '') $crumbs[] = [$h1, '', []]; else $crumbs = [];
    if($page === 'notfound') $crumbs = [];
    $page_desc = ['shipping'=>(free_ship_usd($STORE) ? t('Free shipping over %s.', money_whole(free_ship_usd($STORE))).' ' : '')
                    .t('Japanese Pokémon cards shipped from Japan to Belgium with tracking: delivery times, rates, returns and refunds.'),
                  'faq'=>t('Answers about buying Japanese Pokémon cards in Belgium: shipping from Japan, payment, minimum order, returns and authenticity.'),
                  'how'=>t('How ordering works at {brand}: public prices and quantity breaks, pay by Bitcoin or crypto, and tracked shipping from Japan to Belgium.'),
                  'payment'=>t('How to pay at {brand}: Bitcoin straight from your wallet on the order page, or ETH and USDT with an invoice by email.'),
                  'contact'=>t('Contact {brand} about Japanese Pokémon cards, bulk prices, shipping to Belgium or an existing order. We reply within %d hours.', (int)$CONFIG['reply_hours'])][$page] ?? '';
}
if($page_desc === '') $page_desc = t('Japanese Pokémon cards shipped from Japan to Belgium: sealed booster boxes, Elite Trainer Boxes, collection boxes, rare cards and accessories.');
/* admin-written titles can use {brand} and the other placeholders, like the page text */
$page_title = fill($page_title); $page_desc = fill($page_desc); $h1 = fill($h1);
foreach($crumbs as $ci=>$cr) $crumbs[$ci][0] = fill($cr[0]);

/* one canonical URL per page, without filters, currency or search */
$canon_args = ['product'=>['id'=>$prod['id'] ?? ''], 'set'=>['s'=>$set['slug'] ?? ''], 'series'=>['s'=>$series['slug'] ?? ''],
               'collection'=>['c'=>$coll['slug'] ?? ''], 'guide'=>['g'=>$guide['slug'] ?? ''], 'page'=>['pg'=>$info['slug'] ?? ''],
               'catalog'=>$cat ? ['cat'=>$cat] : []];
$canonical = in_array($page, ['notfound','pay'], true) ? '' : abs_url($page, $canon_args[$page] ?? []);
$noindex = in_array($page, ['cart','checkout','received','pay','notfound'], true) || ($page === 'catalog' && $filtered);
/* this page in each language: hreflang links for Google, and the language switch */
$ALTS = [];
foreach(array_keys(LANGS) as $L){ $u = page_in($L, $page, $canon_args[$page] ?? []); if($u !== null) $ALTS[$L] = $u; }

$in_stock = array_values(array_filter($PRODUCTS, fn($p)=>!in_array($p['status'], ['preorder','soldout'], true)));
$ORG_DESC = t('Independent reseller of authentic Japanese Pokémon Trading Card Game products, shipping from Japan to Belgium.');
?>
<!DOCTYPE html>
<html lang="<?= h($LOCALE) ?>">
<head>
<meta charset="utf-8">
<base href="<?= h($BASE) ?>">
<meta name="viewport" content="width=device-width, initial-scale=1, viewport-fit=cover">
<title><?= h($page_title) ?> | <?= h($CONFIG['brand']) ?></title>
<meta name="description" content="<?= h($page_desc) ?>">
<?php if($canonical): ?><link rel="canonical" href="<?= h($canonical) ?>">
<?php if(!$noindex && count($ALTS) > 1): $d0 = rtrim($CONFIG['domain'], '/').'/';
  foreach($ALTS as $L=>$u): ?><link rel="alternate" hreflang="<?= LANG_LOCALE[$L] ?>" href="<?= h($d0.$u) ?>">
<?php endforeach; ?><link rel="alternate" hreflang="x-default" href="<?= h($page === 'home' ? $d0 : $d0.$ALTS[array_key_first(LANGS)]) ?>">
<?php endif; endif; ?>
<meta name="robots" content="<?= $noindex ? 'noindex, follow' : 'index, follow, max-image-preview:large' ?>">
<?php
  $abs_img = fn($f)=>rtrim($CONFIG['domain'], '/').'/'.$f;
  $og = ($prod ? photos($prod['id']) : []) ?: (photos('hero') ?: (is_file(FK_ROOT.'/'.SITE_SHARE) ? [SITE_SHARE] : [])); ?>
<meta property="og:type" content="<?= $prod ? 'product' : ($guide ? 'article' : 'website') ?>">
<meta property="og:site_name" content="<?= h($CONFIG['brand']) ?>">
<meta property="og:locale" content="<?= LANG_OG[$LANG] ?>">
<?php foreach(LANG_OG as $L=>$og_l) if($L !== $LANG): ?><meta property="og:locale:alternate" content="<?= $og_l ?>">
<?php endif; ?>
<meta property="og:title" content="<?= h($page_title) ?>">
<meta property="og:description" content="<?= h($page_desc) ?>">
<?php if($canonical): ?><meta property="og:url" content="<?= h($canonical) ?>"><?php endif; ?>
<?php if($og): ?><meta property="og:image" content="<?= h($abs_img($og[0])) ?>"><?php endif; ?>
<meta name="twitter:card" content="summary_large_image">
<meta name="theme-color" content="#F6F1E7">
<link rel="icon" href="assets/favicon.svg" type="image/svg+xml">
<?php if($page === 'home'): foreach(['google_verify'=>'google-site-verification', 'bing_verify'=>'msvalidate.01'] as $k=>$nm) if(($CONFIG[$k] ?? '') !== ''): ?><meta name="<?= $nm ?>" content="<?= h($CONFIG[$k]) ?>">
<?php endif; endif; ?>

<?php
$org_id = rtrim($CONFIG['domain'], '/').'/#org';
$graph = [
  ['@type'=>'Organization','@id'=>$org_id,'name'=>$CONFIG['brand'],'legalName'=>$CONFIG['legal_name'],'alternateName'=>$CONFIG['kanji'],'url'=>abs_url('home'),'email'=>$CONFIG['email'],
   'logo'=>rtrim($CONFIG['domain'], '/').'/assets/logo.svg',
   'address'=>['@type'=>'PostalAddress','streetAddress'=>$CONFIG['address'],'addressCountry'=>'JP'],
   'description'=>$ORG_DESC,
   'knowsAbout'=>['Japanese Pokémon Trading Card Game', 'Pokémon TCG', 'Pokémon booster boxes'],
   'areaServed'=>array_map(fn($c)=>['@type'=>'Country','name'=>$c], array_values($COUNTRIES))],
  ['@type'=>'WebSite','@id'=>rtrim($CONFIG['domain'], '/').'/#site-'.$LANG,'url'=>abs_url('home'),'name'=>$CONFIG['brand'],'inLanguage'=>$LOCALE,
   'publisher'=>['@id'=>$org_id],
   'potentialAction'=>['@type'=>'SearchAction','target'=>abs_url('catalog', ['q'=>'QUERY']),'query-input'=>'required name=search_term_string']],
];
$graph[1]['potentialAction']['target'] = str_replace('QUERY', '{search_term_string}', $graph[1]['potentialAction']['target']);
if($prod){
  $cc0 = array_key_first($CURRENCIES); $rate0 = (float)$CURRENCIES[$cc0]['rate'];   /* prices for Google in the shop's first currency */
  $offer = ['@type'=>'Offer','url'=>$canonical,'priceCurrency'=>$cc0,
    'price'=>number_format(unit_price($prod, $prod['moq']) * $rate0, 2, '.', ''),
    'eligibleQuantity'=>['@type'=>'QuantitativeValue','minValue'=>$prod['moq']],
    'availability'=>$prod['status']==='preorder' ? 'https://schema.org/PreOrder'
                    : (can_order($prod) ? 'https://schema.org/InStock' : 'https://schema.org/OutOfStock'),
    'seller'=>['@id'=>$org_id],
    /* 14-day right of withdrawal for consumers in the EU (see the Terms of sale) */
    'hasMerchantReturnPolicy'=>['@type'=>'MerchantReturnPolicy','applicableCountry'=>$HOME_CC,
      'returnPolicyCategory'=>'https://schema.org/MerchantReturnFiniteReturnWindow','merchantReturnDays'=>14,
      'returnMethod'=>'https://schema.org/ReturnByMail','returnFees'=>'https://schema.org/ReturnFeesCustomerResponsibility']];
  if(($prod['cond'] ?? 'Sealed') === 'Sealed') $offer['itemCondition'] = 'https://schema.org/NewCondition';
  if(!empty($CONFIG['shipping_reviewed'])){   /* only once real rates are set */
    $offer['shippingDetails'] = [];
    foreach(ship_methods($STORE) as $mk=>$mm){
      [$dmin, $dmax] = ship_day_range($mm['days']);
      $offer['shippingDetails'][] = ['@type'=>'OfferShippingDetails',
        'shippingDestination'=>['@type'=>'DefinedRegion','addressCountry'=>$HOME_CC],
        'shippingRate'=>['@type'=>'MonetaryAmount','currency'=>$cc0,'value'=>number_format(shipping_usd($STORE, $HOME_CC, (float)($prod['weight'] ?? 0) * $prod['moq'], $mk, unit_price($prod, $prod['moq']) * $prod['moq']) * $rate0, 2, '.', '')],
        'deliveryTime'=>['@type'=>'ShippingDeliveryTime',
          'handlingTime'=>['@type'=>'QuantitativeValue','minValue'=>0,'maxValue'=>max(1, (int)ceil($CONFIG['hold_hours'] / 24)),'unitCode'=>'DAY'],
          'transitTime'=>['@type'=>'QuantitativeValue','minValue'=>$dmin,'maxValue'=>$dmax,'unitCode'=>'DAY']]];
    }
  }
  $graph[] = array_filter(['@type'=>'Product','name'=>$prod['name'],'sku'=>$prod['sku'],'url'=>$canonical,
    'image'=>array_map($abs_img, photos($prod['id'])) ?: null,
    'description'=>plain($prod['desc'], 2000),'category'=>$CATEGORIES[$prod['cat']]['label'],
    'brand'=>['@type'=>'Brand','name'=>'Pokémon'],'offers'=>$offer]);
}
if($guide){
  $graph[] = ['@type'=>'Article','headline'=>fill($guide['title']),'description'=>$page_desc,'url'=>$canonical,
    'dateModified'=>$guide['updated'] ?? gmdate('Y-m-d'),'inLanguage'=>$LOCALE,
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
/* "Washi" theme: warm paper, sumi ink, a vermilion hanko accent and indigo. Flat, ruled, square-cornered.
   System fonts only: nothing to download before first paint. */
:root{
  color-scheme:light;
  --bg:#F6F1E7; --paper:#FFFDF8; --card:#FFFDF8; --card2:#F1EADC; --ink:#1C1A17; --ink2:#48433B; --muted:#7A7367;
  --line:#DDD4C4; --hair:#EAE3D5;
  --red:#C23A22; --red2:#A82E19; --gold:#9C6B12; --teal:#2F6B5E; --blue:#24476E; --violet:#5B4A7A; --green:#3C7A4B;
  --indigo:#1E2F48; --seal:#C23A22;
  --brand:var(--red); --link:#24476E;
  --holo:var(--red); --hot:var(--red);
  --serif:'Hiragino Mincho ProN','Yu Mincho','YuMincho','Noto Serif JP','Noto Serif',Georgia,'Times New Roman',serif;
  --sans:system-ui,-apple-system,'Segoe UI',Roboto,'Helvetica Neue','Hiragino Kaku Gothic ProN','Noto Sans JP',Arial,sans-serif;
  --r:4px;
}
*,*::before,*::after{box-sizing:border-box}
html{scroll-padding-top:130px;background:var(--bg)}
body{margin:0;background:var(--bg);color:var(--ink);font-family:var(--sans);font-size:16px;line-height:1.65;
  -webkit-font-smoothing:antialiased;font-variant-numeric:tabular-nums}
h1,h2,h3{font-family:var(--serif);font-weight:700;line-height:1.18;margin:0;letter-spacing:.005em;color:var(--ink)}
p{margin:0}a{color:inherit}img{max-width:100%;display:block;height:auto}
button,input,select,textarea{font:inherit;color:inherit}
:focus-visible{outline:2px solid var(--blue);outline-offset:3px;border-radius:2px}
.wrap{width:min(1200px,calc(100% - 48px));margin-inline:auto}
@media(max-width:640px){.wrap{width:calc(100% - 32px)}}
.holo-text{color:var(--red)}
::selection{background:#F3D3C8}

/* top strip */
.strip{background:var(--indigo);color:#D9D2C3;font-size:12px}
.strip .wrap{display:flex;justify-content:space-between;align-items:center;gap:18px;min-height:36px}
.strip .wrap>*{white-space:nowrap}
.strip .st{flex:1;min-width:0;overflow:hidden;text-overflow:ellipsis;text-align:center}
@media(max-width:1100px){.strip .st{display:none}}
.strip a{color:#F2C7A8;text-decoration:none;font-weight:700}
.strip a:hover{text-decoration:underline}

/* header */
header.site{position:sticky;top:0;z-index:60;background:rgba(255,253,248,.94);backdrop-filter:blur(10px);
  -webkit-backdrop-filter:blur(10px);border-bottom:1px solid var(--line)}
.bar{display:flex;align-items:center;gap:22px;min-height:72px}
.brand{display:flex;align-items:center;gap:12px;text-decoration:none;flex:none}
.brand .mk{font-family:var(--serif);font-weight:700;font-size:22px;letter-spacing:.28em;color:var(--ink)}
.brand .kj{font-family:var(--serif);font-size:13px;line-height:1.3;color:#fff;background:var(--seal);border-radius:3px;padding:3px 6px;
  letter-spacing:.08em;box-shadow:inset 0 0 0 1.5px rgba(255,255,255,.35)}
form.search{flex:1;max-width:460px;display:flex;border:1px solid var(--line);background:var(--paper);border-radius:var(--r)}
form.search:focus-within{border-color:var(--ink)}
form.search input{flex:1;background:transparent;border:0;color:var(--ink);padding:10px 14px;font-size:14px;min-width:0;outline:none}
form.search input::placeholder{color:var(--muted)}
form.search button{background:transparent;color:var(--ink);border:0;border-left:1px solid var(--line);padding:0 16px;font-weight:700;cursor:pointer;font-size:13px;letter-spacing:.06em;text-transform:uppercase}
form.search button:hover{color:var(--red)}
.tools{display:flex;align-items:center;gap:10px;margin-left:auto;flex:none}
select.pick{appearance:none;background-color:var(--paper);border:1px solid var(--line);border-radius:var(--r);color:var(--ink);
  padding:8px 28px 8px 12px;font-size:13px;cursor:pointer;
  background-image:linear-gradient(45deg,transparent 50%,var(--muted) 50%),linear-gradient(135deg,var(--muted) 50%,transparent 50%);
  background-position:calc(100% - 15px) 53%,calc(100% - 11px) 53%;background-size:4px 4px;background-repeat:no-repeat}
.cartbtn{display:flex;align-items:center;gap:9px;background:var(--ink);color:var(--paper);text-decoration:none;border-radius:var(--r);
  padding:10px 16px;font-size:13px;font-weight:700;letter-spacing:.06em;text-transform:uppercase}
.cartbtn:hover{background:var(--red)}
.cartbtn svg{flex:none}
.cartbtn b{background:var(--paper);color:var(--ink);border-radius:2px;padding:0 6px;min-width:22px;text-align:center;letter-spacing:0}

/* category bar */
.catbar{border-top:1px solid var(--hair)}
.catbar .wrap{display:flex;gap:0;overflow-x:auto;scrollbar-width:none}
.catbar .wrap::-webkit-scrollbar{display:none}
.catbar a{padding:13px 8px;text-decoration:none;font-size:13.5px;font-weight:500;color:var(--ink2);white-space:nowrap;position:relative}
.catbar a::after{content:"";position:absolute;left:8px;right:8px;bottom:-1px;height:2px;background:var(--red);transform:scaleX(0);transition:transform .15s}
.catbar a:hover{color:var(--ink)}.catbar a:hover::after{transform:scaleX(.5)}
.catbar a.on{color:var(--ink);font-weight:700}.catbar a.on::after{transform:scaleX(1)}
@media(max-width:820px){form.search{order:3;max-width:none;flex-basis:100%;margin-bottom:12px}.bar{flex-wrap:wrap;padding-top:12px;gap:14px}}
@media(max-width:520px){
  .bar{gap:10px}.brand{gap:8px}.brand .mk{font-size:17px;letter-spacing:.16em}.brand .kj{font-size:11px;padding:2px 5px}
  select.pick{padding:7px 22px 7px 10px;font-size:12.5px;background-position:calc(100% - 12px) 53%,calc(100% - 8px) 53%}
  .cartbtn{padding:9px 11px;font-size:12px;gap:7px}.cartbtn .lbl{display:none}.tools{gap:6px}
  .strip .st{display:none}.strip .fship~.sl{display:none}.strip .wrap{justify-content:center;min-height:32px}
  .catbar a{padding:12px 10px;font-size:13.5px}
}

/* crumbs */
.crumbs{font-size:12.5px;color:var(--muted);padding:20px 0 0;letter-spacing:.02em}
.crumbs a{text-decoration:none;color:var(--ink2)}.crumbs a:hover{color:var(--red)}

/* buttons */
.btn{display:inline-block;text-align:center;text-decoration:none;border:1px solid var(--red);background:var(--red);color:#fff;border-radius:var(--r);
  padding:12px 24px;font-size:14px;font-weight:700;letter-spacing:.05em;cursor:pointer;transition:background .12s,color .12s,border-color .12s}
.btn:hover{background:var(--red2);border-color:var(--red2)}
.btn.g{background:transparent;color:var(--ink);border-color:var(--ink)}
.btn.g:hover{background:var(--ink);color:var(--paper)}
.btn.gold{background:var(--ink);border-color:var(--ink);color:var(--paper)}
.btn.gold:hover{background:var(--red);border-color:var(--red)}
.btn.wide{width:100%;padding:15px}
.btn:disabled{opacity:.45;cursor:not-allowed}

section{padding:64px 0}
.sechead{display:flex;justify-content:space-between;align-items:flex-end;gap:20px;margin-bottom:26px;flex-wrap:wrap;padding-bottom:14px;border-bottom:1px solid var(--ink)}
.sechead h2{font-size:clamp(24px,3vw,34px)}
.sechead h1{font-size:clamp(28px,3.6vw,42px);margin:0}
.sechead p{color:var(--muted);font-size:14.5px;margin-top:8px;max-width:64ch}
.sechead>a{font-size:13px;font-weight:700;color:var(--red);text-decoration:none;white-space:nowrap;letter-spacing:.06em;text-transform:uppercase}
.sechead>a:hover{text-decoration:underline}
.sechead .count{color:var(--muted);font-size:14px}
section.top{padding-top:28px}
.sub2{font-size:clamp(20px,2.4vw,27px);margin:44px 0 16px}

/* hero */
.hero{padding:64px 0 56px;position:relative;border-bottom:1px solid var(--line);
  background:radial-gradient(circle at 100% 0,rgba(194,58,34,.07),transparent 38%)}
.hgrid{display:grid;grid-template-columns:1.08fr .92fr;gap:64px;align-items:center}
@media(max-width:960px){.hgrid{grid-template-columns:1fr;gap:36px}}
.eyebrow{display:flex;flex-wrap:wrap;gap:6px 18px;margin-bottom:22px}
.eyebrow span{font-size:11.5px;font-weight:700;letter-spacing:.1em;text-transform:uppercase;color:var(--muted);display:flex;align-items:center;gap:8px}
.eyebrow span::before{content:"";width:6px;height:6px;background:var(--line);transform:rotate(45deg)}
.eyebrow span:first-child{color:var(--red)}.eyebrow span:first-child::before{background:var(--red)}
h1{font-size:clamp(34px,5vw,60px);margin-bottom:20px}
.hero h1{font-weight:700;letter-spacing:-.005em}
.lede{font-size:17.5px;color:var(--ink2);max-width:56ch;margin-bottom:30px}
.hero-cta{display:flex;gap:12px;flex-wrap:wrap;margin-bottom:40px}
.stats{display:grid;grid-template-columns:repeat(4,auto);justify-content:start;gap:12px 0;font-size:12px;color:var(--muted);letter-spacing:.04em;border-top:1px solid var(--line);padding-top:18px}
.stats>div{padding:0 28px 0 0;margin-right:28px;border-right:1px solid var(--line)}.stats>div:last-child{border-right:0}
@media(max-width:560px){.stats{grid-template-columns:1fr 1fr;gap:16px 0}.stats>div:nth-child(2n){border-right:0}}
.stats strong{display:block;font-family:var(--serif);font-size:24px;color:var(--ink);letter-spacing:0}
.heroart{position:relative;aspect-ratio:4/3;background:var(--paper);border:1px solid var(--ink);box-shadow:14px 14px 0 var(--card2)}
.heroart img,.heroart .ph{width:100%;height:100%;object-fit:cover}
.heroart.light{background:#fff}
.heroart.light img{object-fit:contain;padding:22px;background:#fff}
.heroimg{display:block;width:100%;height:100%}

/* featured release */
.feature{position:relative;background:var(--card2);border-block:1px solid var(--line)}
.fgrid{display:grid;grid-template-columns:230px 1fr 400px;gap:44px;align-items:center}
@media(max-width:1060px){.fgrid{grid-template-columns:200px 1fr}.fbox{grid-column:1 / -1}}
@media(max-width:640px){.fgrid{grid-template-columns:1fr;gap:24px}.fpack{max-width:200px;margin:0 auto}}
.fpack{display:block;overflow:hidden;border:1px solid var(--ink);box-shadow:10px 10px 0 var(--red);transition:transform .2s}
.fpack:hover{transform:translate(-2px,-2px)}
.kicker{display:inline-block;font-size:11.5px;font-weight:800;letter-spacing:.16em;text-transform:uppercase;color:var(--red);border:1px solid var(--red);padding:5px 10px;margin-bottom:16px}
.ftext h2{font-size:clamp(28px,3.6vw,44px);margin-bottom:14px}
.ftext p{color:var(--ink2);font-size:16.5px;max-width:52ch}
.fbox{margin:0;background:#fff;border:1px solid var(--line)}
.fbox figcaption{background:var(--paper);color:var(--muted);font-size:13px;padding:10px 14px;border-top:1px solid var(--line)}

/* placeholder art (until photos are uploaded): seigaiha waves and a red seal */
.ph{width:100%;height:100%;display:flex;align-items:center;justify-content:center;flex-direction:column;gap:10px;text-align:center;padding:14px;
  color:var(--muted);font-size:11.5px;letter-spacing:.08em;text-transform:uppercase;
  background:radial-gradient(circle at 50% 100%,transparent 44%,rgba(36,71,110,.07) 45% 49%,transparent 50% 58%,rgba(36,71,110,.07) 59% 63%,transparent 64%) 0 0/36px 18px,
             radial-gradient(circle at 50% 100%,transparent 44%,rgba(36,71,110,.07) 45% 49%,transparent 50% 58%,rgba(36,71,110,.07) 59% 63%,transparent 64%) 18px 9px/36px 18px,
             var(--card2)}
.ph span:first-child{font-family:var(--serif);font-size:24px;line-height:1.2;color:#fff;background:var(--seal);padding:8px 12px;letter-spacing:.12em;border-radius:3px;text-transform:none;box-shadow:inset 0 0 0 2px rgba(255,255,255,.3)}

/* category tiles */
.cats{display:grid;grid-template-columns:repeat(5,1fr);gap:0;border-top:1px solid var(--line);border-left:1px solid var(--line)}
@media(max-width:1000px){.cats{grid-template-columns:repeat(2,1fr)}}
@media(max-width:520px){.cats{grid-template-columns:1fr}}
.cats.three{grid-template-columns:repeat(3,1fr)}
@media(max-width:700px){.cats.three{grid-template-columns:1fr}}
.cats a{background:var(--paper);border-right:1px solid var(--line);border-bottom:1px solid var(--line);padding:22px 20px 24px;text-decoration:none;display:block;transition:background .15s}
.cats a:hover{background:#fff}
.cats a:hover h3{color:var(--red)}
.cats .n{font-size:11.5px;color:var(--red);font-weight:700;letter-spacing:.12em;text-transform:uppercase}
.cats h3{font-size:19px;margin:10px 0 8px}
.cats p{font-size:13.5px;color:var(--ink2)}

/* chips (collections) */
.chips{display:flex;flex-wrap:wrap;gap:8px}
.chips a{padding:8px 14px;border:1px solid var(--line);border-radius:var(--r);text-decoration:none;font-size:13.5px;font-weight:600;color:var(--ink);background:var(--paper);transition:border-color .15s,color .15s}
.chips a:hover{border-color:var(--ink);color:var(--red)}

/* product grid */
.grid{display:grid;grid-template-columns:repeat(4,1fr);gap:22px}
@media(max-width:1040px){.grid{grid-template-columns:repeat(2,1fr)}}
@media(max-width:520px){.grid{grid-template-columns:1fr}}
.card{background:var(--paper);border:1px solid var(--line);display:flex;flex-direction:column;transition:border-color .15s,box-shadow .15s}
.card:hover{border-color:var(--ink);box-shadow:6px 6px 0 var(--card2)}
.card .art{aspect-ratio:1/1;position:relative;border-bottom:1px solid var(--line);overflow:hidden;text-decoration:none;display:block;background:var(--card2)}
.card .art img{width:100%;height:100%;object-fit:cover}
.flag{position:absolute;top:10px;left:10px;font-size:10.5px;font-weight:800;letter-spacing:.12em;padding:4px 8px;
  background:var(--paper);color:var(--green);border:1px solid currentColor;z-index:2}
.flag.new{color:var(--blue)}
.flag.pre{color:var(--gold)}
.flag.low{color:var(--red)}
.flag.out{color:var(--muted)}
.card .in{padding:16px;display:flex;flex-direction:column;gap:7px;flex:1}
.card h3{font-family:var(--serif);font-size:16px;font-weight:700;line-height:1.35}
.card h3 a{text-decoration:none}.card h3 a:hover{color:var(--red)}
.card .meta{font-size:11.5px;color:var(--muted);letter-spacing:.06em;text-transform:uppercase}
.card .px{display:flex;align-items:baseline;gap:8px;margin-top:auto;flex-wrap:wrap;padding-top:8px;border-top:1px dashed var(--line)}
.card .px .u{font-size:21px;font-weight:800;color:var(--ink)}
.card .px .w{font-size:12.5px;color:var(--muted);text-decoration:line-through}
.card .px .per{font-size:12px;color:var(--muted);margin-left:-4px}
.card .drop{font-size:12.5px;color:var(--ink2)}
.card .drop b{color:var(--red)}
.card form{padding:0 16px 16px;display:flex;gap:8px}
.card form .btn{flex:1;padding:10px;font-size:13px}

/* catalogue filters */
.filters{display:flex;flex-wrap:wrap;gap:10px 14px;align-items:flex-end;background:var(--paper);border:1px solid var(--line);padding:16px;margin-bottom:24px}
.filters label{display:flex;flex-direction:column;gap:6px;font-size:11px;font-weight:700;letter-spacing:.1em;color:var(--muted);flex:1 1 150px;min-width:0;text-transform:uppercase}
.filters select,.filters input{background:#fff;border:1px solid var(--line);border-radius:var(--r);padding:9px 10px;font-size:14px;font-weight:400;color:var(--ink);width:100%;text-transform:none;letter-spacing:0}
.filters .price span{display:flex;gap:6px}
.filters .fbtns{display:flex;gap:8px}
.filters .fbtns .btn{padding:10px 16px;font-size:13px}
@media(max-width:560px){.filters label{flex-basis:calc(50% - 7px)}.filters label.price{flex-basis:100%}}

/* qty stepper */
.step{display:flex;border:1px solid var(--ink);border-radius:var(--r);overflow:hidden;background:#fff}
.step button{background:transparent;border:none;padding:6px 12px;cursor:pointer;font-weight:700;color:var(--ink)}
.step button:hover{background:var(--ink);color:var(--paper)}
.step input{width:52px;border:none;border-inline:1px solid var(--line);background:transparent;text-align:center;padding:6px 0;font-size:14px;color:var(--ink)}

/* product page */
.pdp{display:grid;grid-template-columns:1.02fr .98fr;gap:56px;padding:28px 0 10px}
@media(max-width:900px){.pdp{grid-template-columns:1fr;gap:30px}}
.gal-main{aspect-ratio:1/1;border:1px solid var(--line);overflow:hidden;background:var(--card2)}
.gal-main img{width:100%;height:100%;object-fit:cover}
.gal-thumbs{display:flex;gap:9px;margin-top:10px}
.gal-thumbs button{width:72px;height:72px;border:1px solid var(--line);overflow:hidden;padding:0;cursor:pointer;background:var(--paper)}
.gal-thumbs button[aria-current="true"]{border:2px solid var(--ink)}
.gal-thumbs img{width:100%;height:100%;object-fit:cover}
.pdp h1{font-size:clamp(28px,3.4vw,40px);margin-bottom:10px}
.pdp .sub{font-size:12px;color:var(--muted);margin-bottom:18px;letter-spacing:.08em;text-transform:uppercase}
.pdp .summary-line{color:var(--ink2);font-size:16px;margin-bottom:22px;max-width:60ch}
.ladder{border:1px solid var(--line);margin-bottom:18px;background:var(--paper)}
.ladder .lh{padding:11px 16px;background:var(--card2);font-size:11px;font-weight:800;letter-spacing:.14em;text-transform:uppercase;color:var(--ink2);border-bottom:1px solid var(--line)}
.ladder .row{display:flex;justify-content:space-between;padding:11px 16px;font-size:14.5px;border-top:1px solid var(--hair);color:var(--ink2)}
.ladder .lh+.row{border-top:0}
.ladder .row.on{color:var(--ink);font-weight:700;background:#FBEDE7;box-shadow:inset 3px 0 0 var(--red)}
.ladder .row:last-child span:last-child{color:var(--red);font-weight:700}
.buybox{border:1px solid var(--ink);padding:22px;background:#fff;position:relative}
.buybox .big{font-family:var(--serif);font-size:42px;font-weight:700;line-height:1.1;color:var(--ink)}
.buybox .sm{font-size:13.5px;color:var(--muted);margin-bottom:14px}
.buybox form{display:flex;gap:10px;align-items:center;margin-top:12px;flex-wrap:wrap}
.buybox form .btn{flex:1;min-width:150px}
.trustline{display:flex;gap:6px 16px;flex-wrap:wrap;font-size:12px;margin-top:16px;letter-spacing:.04em}
.trustline span{color:var(--teal);display:flex;align-items:center;gap:6px}
.trustline span::before{content:"✓";font-weight:800}
.pinfo{display:grid;grid-template-columns:1.2fr .8fr;gap:28px;margin-top:14px}
@media(max-width:900px){.pinfo{grid-template-columns:1fr}}
.panel{background:var(--paper);border:1px solid var(--line);padding:24px}
.panel h2{font-size:22px;margin-bottom:14px;padding-bottom:10px;border-bottom:1px solid var(--hair)}
.specs{display:grid;grid-template-columns:auto 1fr;gap:0;margin:0;font-size:14.5px}
.specs dt,.specs dd{padding:10px 0;border-bottom:1px solid var(--hair);margin:0}
.specs dt{color:var(--muted);padding-right:18px}
.specs dd{color:var(--ink);font-weight:600}
.specs dd a{color:var(--link);text-decoration:none}
.specs dt:last-of-type,.specs dd:last-of-type{border-bottom:none}
.plinks{display:grid;gap:8px;margin-top:4px}
.plinks a{color:var(--link);text-decoration:none;font-weight:600;font-size:14.5px}
.plinks a:hover{text-decoration:underline}

/* tables / cart */
.tbl{width:100%;border-collapse:separate;border-spacing:0;background:var(--paper);border:1px solid var(--line)}
.tbl th{text-align:left;font-size:11px;letter-spacing:.12em;text-transform:uppercase;color:var(--muted);font-weight:700;padding:12px 14px;border-bottom:1px solid var(--ink);background:var(--paper)}
.tbl td{padding:14px;border-bottom:1px solid var(--hair);font-size:14.5px;vertical-align:top;color:var(--ink2)}
.tbl tr:last-child td{border-bottom:none}
.tbl .r{text-align:right;white-space:nowrap}
.tbl .nm{font-weight:700;color:var(--ink)}
.tbl .sk{font-size:12px;color:var(--muted);margin-top:3px}
.tbl a{color:var(--link)}
@media(max-width:700px){.tbl thead{display:none}.tbl td{display:block;border:none;padding:6px 14px}
  .tbl tr{display:block;border-bottom:1px solid var(--line);padding:10px 0}.tbl .r{text-align:left}}

/* checkout */
.cogrid{display:grid;grid-template-columns:1.25fr .75fr;gap:40px;align-items:start}
@media(max-width:900px){.cogrid{grid-template-columns:1fr}}
fieldset{border:1px solid var(--line);padding:22px;margin:0 0 20px;background:var(--paper)}
legend{font-family:var(--serif);font-weight:700;font-size:19px;padding:0 10px;color:var(--ink)}
.fld{margin-bottom:14px}
.fld label{display:block;font-size:12px;font-weight:700;color:var(--ink2);margin-bottom:6px;letter-spacing:.06em;text-transform:uppercase}
.fld input,.fld select,.fld textarea{width:100%;background:#fff;border:1px solid var(--line);border-radius:var(--r);padding:11px 12px;font-size:15px;color:var(--ink)}
.fld input:focus,.fld select:focus,.fld textarea:focus{border-color:var(--ink);outline:none;box-shadow:0 0 0 3px rgba(28,26,23,.06)}
.fld textarea{min-height:82px;resize:vertical}
.two{display:grid;grid-template-columns:1fr 1fr;gap:14px}
@media(max-width:560px){.two{grid-template-columns:1fr}}
.pay{display:grid;gap:9px}
.pay label{display:flex;gap:11px;align-items:flex-start;border:1px solid var(--line);border-radius:var(--r);padding:13px 14px;cursor:pointer;background:#fff}
.pay label:has(input:checked){border-color:var(--red);background:#FBEDE7;box-shadow:inset 3px 0 0 var(--red)}
.pay label[hidden]{display:none}
.pay input{margin-top:4px;accent-color:var(--red)}
.pay .t{font-weight:700;font-size:14.5px;color:var(--ink)}
.pay .n{font-size:12.5px;color:var(--muted)}
.notice{border-left:3px solid var(--blue);background:#EDF1F5;padding:13px 15px;font-size:13.5px;color:var(--ink2);margin:14px 0}
.notice a{color:var(--link)}
.agree{display:flex;gap:10px;align-items:flex-start;font-size:13.5px;color:var(--ink2);margin:14px 0 4px}
.agree input{margin-top:4px;accent-color:var(--red)}
.summary{border:1px solid var(--ink);background:#fff;padding:20px;position:sticky;top:130px}
.summary h3{font-size:20px;margin-bottom:12px;padding-bottom:10px;border-bottom:1px solid var(--hair)}
.sl{display:flex;justify-content:space-between;gap:12px;font-size:13.5px;padding:8px 0;border-bottom:1px solid var(--hair)}
.sl:last-of-type{border-bottom:none}
.sl .q{color:var(--muted);font-size:12px}
.tot{display:flex;justify-content:space-between;font-family:var(--serif);font-size:22px;font-weight:700;padding-top:12px;margin-top:8px;border-top:2px solid var(--ink);color:var(--ink)}
.errs{border:1px solid var(--red);padding:14px 16px;margin-bottom:20px;background:#FBEDE7;font-size:14px;color:var(--ink)}
.errs ul{margin:6px 0 0;padding-left:18px}
.hp{position:absolute;left:-9999px;width:1px;height:1px;overflow:hidden}
.minwarn{border-left:3px solid var(--red);background:#FBEDE7;padding:11px 14px;font-size:13.5px;margin-top:12px}
.minwarn[hidden]{display:none}

/* confirmation */
.done{max-width:720px;margin:0 auto;text-align:center;padding:24px 0}
.done .ref{font-family:var(--serif);font-size:36px;letter-spacing:.1em;margin:14px 0 6px;color:var(--red)}
.done .card2{border:1px solid var(--line);background:var(--paper);padding:26px;text-align:left;margin-top:26px}
.done ol{padding-left:20px;margin:12px 0 0}
.done li{margin-bottom:10px;font-size:14.5px;color:var(--ink2)}

/* feature band */
.deep{position:relative;background:var(--indigo);color:#E9E3D6}
.deep .wrap{position:relative}
.deep h2{color:#FFFDF8}.deep p{color:#CFC8BA;margin-top:14px;font-size:15.5px}
.deep .btn.g{color:#FFFDF8;border-color:#FFFDF8}.deep .btn.g:hover{background:#FFFDF8;color:var(--indigo)}
.deep .two2{display:grid;grid-template-columns:1fr 1fr;gap:48px;align-items:center}
@media(max-width:860px){.deep .two2{grid-template-columns:1fr;gap:26px}}
.spec{border:1px solid rgba(255,253,248,.22)}
.spec div{display:flex;justify-content:space-between;gap:12px;padding:12px 17px;font-size:14px;border-bottom:1px solid rgba(255,253,248,.14)}
.spec div:last-child{border-bottom:none}
.spec span:first-child{color:#A9B3C2}.spec span:last-child{font-weight:700;color:#FFFDF8;text-align:right}

/* steps */
.steps{display:grid;grid-template-columns:repeat(4,1fr);gap:0;border-top:1px solid var(--ink)}
@media(max-width:900px){.steps{grid-template-columns:1fr 1fr}}
@media(max-width:540px){.steps{grid-template-columns:1fr}}
.steps>div{padding:22px 22px 24px 0;margin-right:22px;border-right:1px solid var(--line)}
.steps>div:last-child{border-right:0}
@media(max-width:900px){.steps>div{border-right:0;border-bottom:1px solid var(--line)}}
.steps .n{font-family:var(--serif);font-size:32px;font-weight:700;margin-bottom:6px;color:var(--red);display:inline-block}
.steps h3{font-size:18px;margin-bottom:7px}
.steps p{font-size:14px;color:var(--ink2)}

/* faq */
.faq details{border-bottom:1px solid var(--line);padding:17px 0}
.faq summary{cursor:pointer;font-family:var(--serif);font-weight:700;font-size:17px;list-style:none;display:flex;justify-content:space-between;gap:14px;color:var(--ink)}
.faq summary::-webkit-details-marker{display:none}
.faq summary::after{content:"+";color:var(--red);font-size:22px;line-height:1;font-family:var(--sans)}
.faq details[open] summary::after{content:"–"}
.faq p{margin-top:9px;font-size:14.5px;color:var(--ink2);max-width:80ch}

/* footer */
footer.site{background:var(--indigo);color:#CFC8BA;padding:56px 0 30px;margin-top:40px}
footer .brand .mk{color:#FFFDF8}
.fg{display:grid;grid-template-columns:1.4fr repeat(4,1fr);gap:28px}
@media(max-width:860px){.fg{grid-template-columns:1fr 1fr}}
@media(max-width:520px){.fg{grid-template-columns:1fr}}
footer h4{font-size:11px;letter-spacing:.16em;text-transform:uppercase;color:#F2C7A8;margin:0 0 14px}
footer ul{list-style:none;margin:0;padding:0;display:grid;gap:8px}
footer a{color:#DCD5C7;text-decoration:none;font-size:14px}
footer a:hover{color:#fff;text-decoration:underline}
footer .bl{font-size:14px;color:#A9B3C2;margin-top:14px;max-width:44ch}
.legal{margin-top:36px;padding-top:18px;border-top:1px solid rgba(255,253,248,.14);font-size:12.5px;color:#A9B3C2;display:grid;gap:9px}
.empty{padding:40px 0;color:var(--muted)}
.empty a{color:var(--link)}

/* long-form text */
.prose{max-width:740px;color:var(--ink2);font-size:16.5px;line-height:1.78}
.prose>:first-child{margin-top:0}
.prose h2{font-size:clamp(23px,2.4vw,29px);color:var(--ink);margin:40px 0 12px;padding-top:6px}
.prose h3{font-size:19.5px;color:var(--ink);margin:26px 0 8px}
.prose p{margin:0 0 16px}
.prose ul{margin:0 0 16px;padding-left:0;list-style:none}
.prose li{margin-bottom:8px;padding-left:22px;position:relative}
.prose li::before{content:"";position:absolute;left:4px;top:.68em;width:6px;height:6px;background:var(--red);transform:rotate(45deg)}
.prose a{color:var(--link);font-weight:600;text-decoration:none;border-bottom:1px solid rgba(36,71,110,.3)}
.prose a:hover{color:var(--red);border-bottom-color:var(--red)}
.prose strong{color:var(--ink)}
.prose.lead{margin-bottom:28px}
.prose.after{margin-top:52px;padding-top:30px;border-top:1px solid var(--line)}

/* set tiles and guide cards */
.tiles{display:grid;grid-template-columns:repeat(auto-fill,minmax(160px,1fr));gap:12px}
.tiles a{background:var(--paper);border:1px solid var(--line);padding:15px 16px;text-decoration:none;display:flex;flex-direction:column;gap:3px;transition:border-color .15s}
.tiles a:hover{border-color:var(--ink)}
.tiles a:hover b{color:var(--red)}
.tiles b{font-family:var(--serif);font-size:16px;line-height:1.3;color:var(--ink)}
.tiles .n{font-size:12px;color:var(--muted)}
.tiles .n:first-child{color:var(--red);font-weight:700;letter-spacing:.1em}
.gcards{display:grid;grid-template-columns:repeat(auto-fill,minmax(260px,1fr));gap:0;border-top:1px solid var(--line);border-left:1px solid var(--line)}
.gcards a{background:var(--paper);border-right:1px solid var(--line);border-bottom:1px solid var(--line);padding:22px;text-decoration:none;display:flex;flex-direction:column;gap:9px;transition:background .15s}
.gcards a:hover{background:#fff}
.gcards a:hover h3{color:var(--red)}
.gcards h3{font-size:18.5px;color:var(--ink)}
.gcards p{font-size:14px;color:var(--ink2)}
.gcards span{margin-top:auto;font-size:12px;font-weight:700;color:var(--red);letter-spacing:.1em;text-transform:uppercase}

/* guides */
.article{max-width:760px}
.article h1{font-size:clamp(30px,4.2vw,48px);margin-bottom:12px}
.article .meta{font-size:12px;color:var(--muted);margin-bottom:24px;letter-spacing:.08em;text-transform:uppercase}
.toc{border-top:1px solid var(--ink);border-bottom:1px solid var(--line);padding:16px 0;margin:0 0 30px}
.toc b{display:block;font-size:11px;letter-spacing:.14em;text-transform:uppercase;color:var(--muted);margin-bottom:8px}
.toc ol{margin:0;padding-left:20px;display:grid;gap:5px}
.toc a{color:var(--link);text-decoration:none;font-size:14.5px}
.toc a:hover{color:var(--red);text-decoration:underline}

/* free shipping: top bar, cart and checkout */
.strip .fship{display:inline-flex;align-items:center;gap:7px;color:#FFFDF8;font-weight:700;letter-spacing:.03em}
.strip .fship svg{color:#F2C7A8;flex:none}
.strip .fship:hover span{text-decoration:underline}
.fsm{margin:10px 0 12px;max-width:420px;margin-left:auto;text-align:left}
.fsm .t{font-size:13.5px;color:var(--ink2);margin-bottom:7px}.fsm .t b{color:var(--ink)}
.fsm .meter{height:5px;background:var(--hair);overflow:hidden}
.fsm .meter i{display:block;height:100%;background:var(--red)}
.fsm.c{max-width:none;margin:12px 0 0}

/* Bitcoin payment page */
.payhead{margin-bottom:24px}
.payhead .kick{font-size:12px;color:var(--muted);letter-spacing:.1em;text-transform:uppercase}.payhead .kick b{color:var(--red)}
.payhead h1{font-size:clamp(28px,3.6vw,42px);margin:6px 0 10px}
.payhead .lede{margin-bottom:0}
.paygrid{display:grid;grid-template-columns:minmax(0,1fr) 360px;gap:28px;align-items:start}
@media(max-width:960px){.paygrid{grid-template-columns:1fr}.paygrid .summary{position:static}}
.paybox{border:1px solid var(--ink);background:#fff;padding:24px;position:relative;box-shadow:inset 0 3px 0 #D9822B}
.payrow{display:grid;grid-template-columns:250px minmax(0,1fr);gap:26px;align-items:start}
@media(max-width:640px){.payrow{grid-template-columns:1fr}.qrcol{max-width:300px;margin:0 auto;width:100%}}
.qr{background:#fff;border:1px solid var(--line);padding:10px;aspect-ratio:1;display:grid;place-items:center;margin-bottom:12px}
.qr svg{width:100%;height:100%;display:block}
.qr .qrph{color:#666;font-size:13px}
.pf .l{font-size:11px;letter-spacing:.14em;text-transform:uppercase;color:var(--muted);font-weight:700;margin-bottom:6px}
.pf .v{display:flex;align-items:center;gap:10px;flex-wrap:wrap}
.pf .amt span{font-family:var(--serif);font-size:clamp(26px,3.4vw,34px);font-weight:700;color:var(--ink);letter-spacing:.01em;font-variant-numeric:tabular-nums}
.pf .amt small{font-size:16px;font-weight:800;color:#C36A12}
.pf .n{font-size:13px;color:var(--muted);margin-top:6px}
.pf .addr code{font-family:ui-monospace,SFMono-Regular,Menlo,Consolas,monospace;font-size:14.5px;color:var(--ink);background:var(--card2);border:1px solid var(--line);padding:9px 11px;word-break:break-all;flex:1;min-width:0}
.copy{background:#fff;border:1px solid var(--ink);color:var(--ink);border-radius:var(--r);padding:7px 14px;font-size:12px;font-weight:700;letter-spacing:.06em;text-transform:uppercase;cursor:pointer;white-space:nowrap}
.copy:hover{background:var(--ink);color:var(--paper)}
.hold{font-size:13.5px;color:var(--ink2);margin-top:16px}.hold b{color:var(--red);font-variant-numeric:tabular-nums}
.watch{display:flex;align-items:center;gap:10px;font-size:13.5px;color:var(--teal);margin-top:14px;padding:11px 13px;background:#EAF2EF;border:1px solid #C8DDD6}
.pulse{width:9px;height:9px;border-radius:50%;background:var(--teal);flex:none;box-shadow:0 0 0 0 rgba(47,107,94,.6);animation:pulse 1.8s infinite}
@keyframes pulse{70%{box-shadow:0 0 0 10px rgba(47,107,94,0)}100%{box-shadow:0 0 0 0 rgba(47,107,94,0)}}
.paytips{list-style:none;padding:0;margin:22px 0 0;display:grid;gap:9px;font-size:13.5px;color:var(--ink2)}
.paytips li{padding-left:22px;position:relative}.paytips b{color:var(--ink)}
.paytips li::before{content:"";position:absolute;left:4px;top:.6em;width:6px;height:6px;background:#D9822B;transform:rotate(45deg)}
.txform{margin-top:20px;border-top:1px solid var(--hair);padding-top:14px}
.txform summary{cursor:pointer;font-size:14px;font-weight:700;color:var(--link)}
.txform label{display:block;font-size:13px;color:var(--muted);margin:12px 0 6px}
.txrow{display:flex;gap:10px;flex-wrap:wrap}
.txrow input{flex:1;min-width:220px;background:#fff;border:1px solid var(--line);border-radius:var(--r);padding:11px 13px;font-family:ui-monospace,Menlo,Consolas,monospace;font-size:13px}
.timeline{list-style:none;margin:0;padding:0;display:grid;gap:0}
.timeline li{position:relative;padding:0 0 22px 38px;display:grid;gap:2px}
.timeline li::before{content:"";position:absolute;left:0;top:1px;width:22px;height:22px;border-radius:50%;border:2px solid var(--line);background:#fff}
.timeline li::after{content:"";position:absolute;left:11px;top:26px;bottom:2px;width:2px;background:var(--line)}
.timeline li:last-child::after{display:none}
.timeline li.ok::before{background:var(--green);border-color:var(--green);box-shadow:inset 0 0 0 5px var(--green)}
.timeline li.ok::after{background:var(--green)}
.timeline li.now::before{border-color:var(--red);animation:pulse 1.8s infinite}
.timeline b{color:var(--ink);font-size:15.5px}.timeline span{font-size:13.5px;color:var(--muted)}
.txbox{margin-top:8px;padding:14px;background:var(--card2);border:1px solid var(--line);display:grid;gap:6px}
.txbox .l{font-size:11px;letter-spacing:.14em;text-transform:uppercase;color:var(--muted);font-weight:700}
.txbox code{font-family:ui-monospace,Menlo,Consolas,monospace;font-size:12.5px;color:var(--ink);word-break:break-all}
.txbox a{color:var(--link);font-weight:700;font-size:14px;text-decoration:none}
.summary .n{font-size:12.5px;color:var(--muted);margin-top:10px}.summary .n a{color:var(--link)}

/* guide links on category, set, collection and FAQ pages */
.glinks{margin-top:8px}
.gchips{display:flex;flex-wrap:wrap;gap:8px}
.gchips a{border:1px solid var(--line);background:var(--paper);border-radius:var(--r);padding:8px 14px;font-size:13.5px;font-weight:600;color:var(--ink);text-decoration:none}
.gchips a:hover{border-color:var(--ink);color:var(--red)}

/* guide tools */
.pcheck{display:flex;align-items:center;gap:12px;flex-wrap:wrap;margin:6px 0 12px}
.pcheck label{font-weight:800;color:var(--ink)}
.pcheck input{flex:1;min-width:220px;background:#fff;border:1px solid var(--line);border-radius:var(--r);padding:11px 14px;font-size:15px;color:var(--ink)}
.pcheck input:focus{border-color:var(--ink);outline:none}
.pcheck span{font-size:13px;color:var(--muted)}
.tbl.pc .sk{font-weight:500}
.tplbox{display:grid;grid-template-columns:200px minmax(0,1fr);gap:22px;align-items:center;border:1px solid var(--line);background:var(--paper);padding:18px;margin:6px 0 20px}
.tplbox img{width:100%;height:auto;background:#fff;border:1px solid var(--line)}
.tplbox b{color:var(--ink);font-size:17px;font-family:var(--serif)}.tplbox p{margin:8px 0 0}
.prose .tplbox .btn{color:#fff;border-bottom:1px solid var(--red);margin:4px 6px 0 0}.prose .tplbox .btn.g{color:var(--ink);border-bottom-color:var(--ink)}
@media(max-width:560px){.tplbox{grid-template-columns:1fr}.tplbox img{max-width:220px}}

/* Shipping & Returns */
.srhead h1{font-size:clamp(30px,4vw,46px)}
.srhead .lede{margin:12px 0 28px}
.srcards{display:grid;grid-template-columns:repeat(4,1fr);gap:0;margin-bottom:44px;border-top:1px solid var(--ink);border-bottom:1px solid var(--line)}
@media(max-width:900px){.srcards{grid-template-columns:1fr 1fr}}
@media(max-width:520px){.srcards>div{padding:14px 10px 14px 0}.srcards .ic{width:30px;height:30px}.srcards b{font-size:15px}}
.srcards>div{--c:var(--red);padding:20px 20px 22px 0;margin-right:20px;display:grid;gap:4px;border-right:1px solid var(--line)}
.srcards>div:last-child{border-right:0}
@media(max-width:900px){.srcards>div:nth-child(2n){border-right:0}}
.srcards .k2{--c:var(--teal)}.srcards .k3{--c:var(--gold)}.srcards .k4{--c:var(--blue)}
.srcards .ic{width:36px;height:36px;display:grid;place-items:center;color:var(--c);border:1px solid currentColor;margin-bottom:8px}
.srcards .ic svg{width:19px;height:19px}
.srcards b{color:var(--ink);font-size:16px;font-family:var(--serif)}.srcards span:last-child{font-size:13.5px;color:var(--ink2)}
.srgrid{display:grid;grid-template-columns:230px minmax(0,1fr);gap:52px;align-items:start}
.srtoc{position:sticky;top:140px;border-left:2px solid var(--ink);padding-left:16px}
.srtoc b{font-size:11px;letter-spacing:.14em;text-transform:uppercase;color:var(--muted)}
.srtoc ol{list-style:none;margin:10px 0 0;padding:0;display:grid;gap:2px}
.srtoc a{display:block;padding:5px 0;font-size:14px;color:var(--ink2);text-decoration:none}
.srtoc a:hover{color:var(--red)}
@media(max-width:960px){
  .srgrid{grid-template-columns:minmax(0,1fr);gap:10px}
  .srtoc{position:static;border:none;padding:0;min-width:0}
  .srtoc ol{display:flex;gap:8px;overflow-x:auto;scrollbar-width:none;padding-bottom:6px}
  .srtoc a{white-space:nowrap;border:1px solid var(--line);border-radius:var(--r);padding:6px 12px;font-size:13px;background:var(--paper)}
}
.prose.sr{max-width:780px}
.prose.sr h2{scroll-margin-top:140px;padding-top:8px}
.prose.sr h3{scroll-margin-top:140px}
.prose ol{margin:0 0 16px;padding:0;list-style:none;counter-reset:step}
.prose ol li{counter-increment:step;padding-left:40px;margin-bottom:12px;min-height:28px}
.prose ol li::before{content:counter(step);position:absolute;left:0;top:.1em;width:26px;height:26px;background:transparent;
  border:1px solid var(--red);color:var(--red);font-weight:800;font-size:12.5px;display:grid;place-items:center;transform:none;font-family:var(--serif)}
.prose .tblwrap{overflow-x:auto;margin:0 0 16px}
.prose .tbl small{color:var(--muted);font-weight:500}
.prose .tbl .free{color:var(--green)}
.prose p.small{font-size:13.5px;color:var(--muted)}
.prose code{font-family:ui-monospace,Menlo,Consolas,monospace;font-size:.9em;color:var(--ink);background:var(--card2);padding:2px 6px;word-break:break-all}
.srcontact{margin-top:44px;border:1px solid var(--ink);padding:22px;display:flex;justify-content:space-between;align-items:center;gap:16px;flex-wrap:wrap;background:#fff;box-shadow:8px 8px 0 var(--card2)}
.srcontact b{display:block;color:var(--ink);font-size:18px;font-family:var(--serif)}.srcontact span{font-size:14px;color:var(--ink2)}
.srcontact .row2{display:flex;gap:10px;flex-wrap:wrap}
.prose .srcontact .btn{color:#fff;border-bottom:1px solid var(--red)}.prose .srcontact .btn.g{color:var(--ink);border-bottom-color:var(--ink)}

/* language switch */
.langsw{display:flex;border:1px solid var(--line);border-radius:var(--r);overflow:hidden;font-size:12.5px;font-weight:700;letter-spacing:.06em}
.langsw a,.langsw span{padding:8px 10px;text-decoration:none;color:var(--ink2)}
.langsw span{background:var(--ink);color:var(--paper)}
.langsw a:hover{color:var(--red)}
@media(max-width:520px){.langsw a,.langsw span{padding:7px 8px}}

@media (prefers-reduced-motion:reduce){*{transition:none!important;animation:none!important}}
</style>
</head>
<body>

<div class="strip"><div class="wrap">
  <?php if(free_ship_usd($STORE)): ?><a class="fship" href="<?= url('shipping') ?>"><svg viewBox="0 0 24 24" width="17" height="17" aria-hidden="true"><path fill="currentColor" d="M3 6.5A1.5 1.5 0 0 1 4.5 5h9A1.5 1.5 0 0 1 15 6.5V8h2.6a1.5 1.5 0 0 1 1.2.6l2.4 3.2c.2.26.3.58.3.9V16a1.5 1.5 0 0 1-1.5 1.5h-.6a2.75 2.75 0 0 1-5.3 0H9.9a2.75 2.75 0 0 1-5.3 0h-.1A1.5 1.5 0 0 1 3 16V6.5Zm12 3V13h4.5l-1.9-2.5a1.5 1.5 0 0 0-1.2-.6H15ZM7.25 18.25a1 1 0 1 0 0-2 1 1 0 0 0 0 2Zm9.4 0a1 1 0 1 0 0-2 1 1 0 0 0 0 2Z"/></svg><span><?= h(t('Free shipping on orders over %s', money_whole(free_ship_usd($STORE)))) ?></span></a><?php endif; ?>
  <span class="st"><?= h($CONFIG['strip_text']) ?></span>
  <?php if($CONFIG['strip_link_text']): ?><a class="sl" href="<?= h(($CONFIG['strip_link_url'] ?? '') !== '' ? (link_target($CONFIG['strip_link_url']) ?? $CONFIG['strip_link_url']) : url('catalog')) ?>"><?= h($CONFIG['strip_link_text']) ?></a><?php endif; ?>
</div></div>

<header class="site">
  <div class="wrap bar">
    <a class="brand" href="<?= h(url('home')) ?>">
      <span class="mk"><?= h($CONFIG['brand']) ?></span>
      <span class="kj"><?= h($CONFIG['kanji']) ?></span>
    </a>
    <form class="search" action="<?= empty($CONFIG['pretty_urls']) ? 'index.php' : h(url('catalog')) ?>" method="get" role="search">
      <?php if(empty($CONFIG['pretty_urls'])): ?><input type="hidden" name="p" value="catalog"><input type="hidden" name="l" value="<?= h($LANG) ?>"><?php endif; ?>
      <input type="search" name="q" value="<?= h($q) ?>" placeholder="<?= h(t('Search a set, card or SKU')) ?>" aria-label="<?= h(t('Search products')) ?>">
      <button type="submit"><?= h(t('Search')) ?></button>
    </form>
    <div class="tools">
      <nav class="langsw" aria-label="<?= h(t('Language')) ?>">
        <?php foreach(LANGS as $L=>$lname): ?>
          <?php if($L === $LANG): ?><span lang="<?= LANG_LOCALE[$L] ?>" title="<?= h($lname) ?>"><?= strtoupper($L) ?></span>
          <?php else: ?><a href="<?= h($ALTS[$L] ?? url('home', [], $L)) ?>" hreflang="<?= LANG_LOCALE[$L] ?>" lang="<?= LANG_LOCALE[$L] ?>" title="<?= h($lname) ?>"><?= strtoupper($L) ?></a><?php endif; ?>
        <?php endforeach; ?>
      </nav>
      <a class="cartbtn" href="<?= url('cart') ?>" aria-label="<?= h(t('Your order')) ?>"><svg viewBox="0 0 24 24" width="16" height="16" aria-hidden="true"><path fill="none" stroke="currentColor" stroke-width="2" stroke-linejoin="round" d="M5 8h14l-1.2 12H6.2L5 8Zm3 0a4 4 0 0 1 8 0"/></svg><span class="lbl"><?= h(t('Order')) ?></span> <b><?= cart_units() ?></b></a>
    </div>
  </div>
  <nav class="catbar" aria-label="<?= h(t('Categories')) ?>"><div class="wrap">
    <a href="<?= url('catalog') ?>" class="<?= $page==='catalog'&&!$cat?'on':'' ?>"><?= h(t('All products')) ?></a>
    <?php foreach($CATEGORIES as $k=>$c): ?>
      <a href="<?= url('catalog',['cat'=>$k]) ?>" class="<?= $cat===$k?'on':'' ?>"><?= h($c['label']) ?></a>
    <?php endforeach; ?>
    <a href="<?= url('sets') ?>" class="<?= in_array($page, ['sets','series','set'], true)?'on':'' ?>"><?= h(t('Sets')) ?></a>
    <?php if($GUIDES): ?><a href="<?= url('guides') ?>" class="<?= in_array($page, ['guides','guide'], true)?'on':'' ?>"><?= h(t('Guides')) ?></a><?php endif; ?>
    <a href="<?= url('shipping') ?>" class="<?= $page==='shipping'?'on':'' ?>"><?= h(t('Shipping & Returns')) ?></a>
    <a href="<?= url('faq') ?>" class="<?= $page==='faq'?'on':'' ?>"><?= h(t('FAQ')) ?></a>
  </div></nav>
</header>

<main>
<?php if(!empty($_SESSION['flash'])): ?>
  <div class="wrap"><div class="notice" role="status"><?php foreach($_SESSION['flash'] as $msg) echo '<div>'.h($msg).'</div>'; ?></div></div>
<?php unset($_SESSION['flash']); endif; ?>
<?php if(count($crumbs) > 1): ?>
  <nav class="wrap crumbs" aria-label="<?= h(t('Breadcrumb')) ?>"><?php foreach($crumbs as $i=>[$label, $pg, $args]):
    echo $i ? ' / ' : '';
    echo $pg !== '' ? '<a href="'.h(url($pg, $args)).'">'.h($label).'</a>' : '<span aria-current="page">'.h($label).'</span>';
  endforeach; ?></nav>
<?php endif; ?>
<?php if($page==='home'): ?>

  <section class="hero"><div class="wrap hgrid">
    <div>
      <div class="eyebrow"><span><?= h(t('Straight from Japan')) ?></span><span><?= h(t('Sealed & authentic')) ?></span><span><?= h(t('Delivered in Belgium')) ?></span><span><?= h(t('No VAT added')) ?></span></div>
      <h1><?= h($CONFIG['hero_title']) ?></h1>
      <p class="lede"><?= h($CONFIG['hero_lede']) ?></p>
      <div class="hero-cta">
        <a class="btn" href="<?= url('catalog') ?>"><?= h(t('Shop Japanese Pokémon cards')) ?></a>
        <?php if($pg_ = guide_by_key('prices')): ?><a class="btn g" href="<?= h(url('guide', ['g'=>$pg_['slug']])) ?>"><?= h(guide_anchor($pg_)) ?></a><?php endif; ?>
      </div>
      <div class="stats">
        <div><strong><?= count($in_stock) ?></strong><?= h(t('products in stock')) ?></div>
        <div><strong><?= money_whole($MIN_ORDER) ?></strong><?= h(t('Minimum order, incl. shipping')) ?></div>
        <?php if(free_ship_usd($STORE)): ?><div><strong><?= money_whole(free_ship_usd($STORE)) ?></strong><?= h(t('Free shipping from')) ?></div><?php endif; ?>
        <div><strong><?= (int)$CONFIG['hold_hours'] ?>h</strong><?= h(t('Dispatched after payment')) ?></div>
      </div>
    </div>
    <div class="heroart<?= photos('hero') ? '' : ' light' ?>">
      <?php $hp = photos('hero');
      if($hp): ?><?= img_tag($hp[0], t('Sealed Japanese Pokémon booster boxes ready to ship from Japan'), '(max-width:960px) 100vw, 600px', true) ?>
      <?php elseif(is_file(FK_ROOT.'/'.SITE_HERO)): ?><a href="<?= h(url('product', ['id'=>'30th-celebration-elite-trainer-box'])) ?>" class="heroimg"><?= img_tag(SITE_HERO, t('Pokémon TCG 30th Celebration Elite Trainer Box'), '(max-width:960px) 100vw, 600px', true) ?></a>
      <?php else: ?><div class="ph"><span><?= h($CONFIG['kanji']) ?></span><span><?= h(t('Japanese Pokémon cards · shipped from Japan')) ?></span></div><?php endif; ?>
    </div>
  </div></section>

  <section>
    <div class="wrap">
      <div class="sechead"><div><h2><?= h(t('Shop by category')) ?></h2>
        <p><?= h(t('Booster boxes, Elite Trainer Boxes, collection boxes, rare single cards and the accessories to keep them safe.')) ?></p></div>
        <a href="<?= url('catalog') ?>"><?= h(t('See everything')) ?> →</a></div>
      <div class="cats">
        <?php foreach($CATEGORIES as $k=>$c):
          $n = count(array_filter($PRODUCTS, fn($p)=>$p['cat']===$k)); ?>
          <a href="<?= url('catalog',['cat'=>$k]) ?>">
            <div class="n"><?= h(tn($n, '%d product', '%d products')) ?></div><h3><?= h($c['label']) ?></h3><p><?= h($c['blurb']) ?></p>
          </a>
        <?php endforeach; ?>
      </div>
    </div>
  </section>

  <?php $pop = array_filter($COLLECTIONS, fn($c)=>collection_products($c)); $s151 = set_by_slug('151'); ?>
  <?php if($pop || $s151): ?>
  <section style="padding-top:0"><div class="wrap">
    <div class="chips" aria-label="<?= h(t('Popular')) ?>">
      <?php foreach($pop as $c): ?><a href="<?= h(url('collection', ['c'=>$c['slug']])) ?>"><?= h($c['title']) ?></a><?php endforeach; ?>
      <?php if($s151): ?><a href="<?= h(url('set', ['s'=>'151'])) ?>"><?= h(t('151 Pokémon cards')) ?></a><?php endif; ?>
      <?php foreach($SERIES as $k=>$sr): if(series_sets($k)): ?><a href="<?= h(url('series', ['s'=>$sr['slug']])) ?>"><?= h($sr['name']) ?></a><?php endif; endforeach; ?>
    </div>
  </div></section>
  <?php endif; ?>

  <section>
    <div class="wrap">
      <div class="sechead"><div><h2><?= h(t('In stock now')) ?></h2>
        <p><?= h(t('Ready to ship from Japan. The more you order, the lower the price per unit: every quantity break is on the listing.')) ?></p></div>
        <a href="<?= url('catalog') ?>"><?= h(t('All products')) ?> →</a></div>
      <div class="grid">
        <?php foreach(array_slice($in_stock,0,8) as $p) include_card($p); ?>
      </div>
    </div>
  </section>

  <?php $f30 = set_by_slug('30th-celebration'); $etb30 = product('30th-celebration-elite-trainer-box');
  if($f30 && is_file(FK_ROOT.'/assets/site/pokemon-30th-celebration-booster-pack.webp')): ?>
  <section class="feature"><div class="wrap fgrid">
    <a class="fpack" href="<?= h(url('set', ['s'=>$f30['slug']])) ?>"><?= img_tag('assets/site/pokemon-30th-celebration-booster-pack.webp', t('Pokémon TCG 30th Celebration booster pack with Pikachu, Mew and Mewtwo'), '(max-width:860px) 55vw, 260px') ?></a>
    <div class="ftext">
      <span class="kicker"><?= h(t('30th anniversary')) ?></span>
      <h2>Pokémon TCG: 30th Celebration</h2>
      <p><?= h(t('Thirty years of Pokémon in one set. Mewtwo ex and Mew ex lead the way, joined by Umbreon ex, Salamence ex and Greninja ex, and every booster pack holds a Pikachu, with 30 different Pikachu rare cards to collect.')) ?></p>
      <div class="hero-cta" style="margin:22px 0 0">
        <a class="btn gold" href="<?= h(url('set', ['s'=>$f30['slug']])) ?>"><?= h(t('Shop 30th Celebration')) ?></a>
        <?php if($etb30): ?><a class="btn g" href="<?= h(url('product', ['id'=>$etb30['id']])) ?>">Elite Trainer Box</a><?php endif; ?>
      </div>
    </div>
    <figure class="fbox">
      <?= img_tag('assets/site/pokemon-30th-celebration-elite-trainer-box-contents.webp', t('What’s inside the Pokémon TCG 30th Celebration Elite Trainer Box'), '(max-width:860px) 100vw, 420px') ?>
      <figcaption><?= h(t('Inside the Elite Trainer Box: 9 booster packs, a full-art Nidorina promo, 65 sleeves, dice and more.')) ?></figcaption>
    </figure>
  </div></section>
  <?php endif; ?>

  <?php $home_sets = sets_all(); if($home_sets): ?>
  <section style="padding-top:0"><div class="wrap">
    <div class="sechead"><div><h2><?= h(t('Shop by set')) ?></h2>
      <p><?= h(t('Japanese Mega Evolution sets, plus Scarlet & Violet favourites like 151.')) ?></p></div>
      <a href="<?= url('sets') ?>"><?= h(t('All sets')) ?> →</a></div>
    <?php set_tiles($home_sets); ?>
  </div></section>
  <?php endif; ?>

  <section class="deep"><div class="wrap two2">
    <div>
      <h2><?= h(t('Sealed in its original factory packaging.')) ?></h2>
      <p><?= h(t('Everything is bought through Japanese distribution and ships exactly as it left the factory. We never sell resealed, reprinted or fake product, and the price on the listing is the price you pay: we add no VAT or other tax.')) ?></p>
      <p style="margin-top:22px"><a class="btn gold" href="<?= url('how') ?>"><?= h(t('How ordering works')) ?></a>
        <?php if($au = page_url('about')): ?><a class="btn g" href="<?= h($au) ?>" style="margin-left:8px"><?= h(t('About %s', $CONFIG['brand'])) ?></a><?php endif; ?></p>
    </div>
    <div class="spec">
      <div><span><?= h(t('Sourcing')) ?></span><span><?= h(t('Japanese distribution')) ?></span></div>
      <div><span><?= h(t('Minimum order value')) ?></span><span><?= h(t('%s incl. shipping', money($MIN_ORDER))) ?></span></div>
      <div><span><?= h(t('Taxes')) ?></span><span><?= h(t('No VAT added')) ?></span></div>
      <div><span><?= h(t('Dispatch after payment')) ?></span><span><?= h(t('Within %d hours', (int)$CONFIG['hold_hours'])) ?></span></div>
      <?php if(free_ship_usd($STORE)): ?><div><span><?= h(t('Free shipping')) ?></span><span><?= h(t('Orders over %s', money_whole(free_ship_usd($STORE)))) ?></span></div><?php endif; ?>
      <?php $nbtc = count(array_filter($PAYMENTS, 'btc_method')); if($PAYMENTS): ?><div><span><?= h(t('Pay by')) ?></span><span><?= h($nbtc ? t('Bitcoin on the site, or other crypto') : t('Crypto')) ?></span></div><?php endif; ?>
      <div><span><?= h(t('Carriers')) ?></span><span>EMS · DHL · FedEx</span></div>
      <?php $SM = ship_methods($STORE); ?><div><span><?= h(t('Delivery')) ?></span><span><?= h($SM['standard']['label'].' '.days_text($SM['standard']['days']).' · '.$SM['express']['label'].' '.days_text($SM['express']['days'])) ?></span></div>
    </div>
  </div></section>

  <section><div class="wrap">
    <div class="sechead"><div><h2><?= h(t('Four steps from cart to courier')) ?></h2></div></div>
    <?php steps_block($CONFIG); ?>
  </div></section>

  <?php if($GUIDES): ?>
  <section style="padding-top:0"><div class="wrap">
    <div class="sechead"><div><h2><?= h(t('Pokémon card guides')) ?></h2>
      <p><?= h(t('Card prices and values, where to buy, new releases and how to play.')) ?></p></div>
      <a href="<?= url('guides') ?>"><?= h(t('All %d guides', count($GUIDES))) ?> →</a></div>
    <?php guide_cards(guides_list(PAGE_GUIDES['home']) ?: array_slice($GUIDES, 0, 6)); ?>
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
      $hay = lc($p['name'].' '.$p['set'].' '.$p['sku'].' '.$CATEGORIES[$p['cat']]['label'].' '.($p['cond'] ?? ''));
      if(strpos($hay, lc($q))===false) return false;
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
      <p><?= $cat ? h($CATEGORIES[$cat]['blurb']) : h(t('Sealed booster boxes, Elite Trainer Boxes, collection boxes, rare singles and accessories, with every quantity break on the listing.')) ?></p>
    </div><span style="color:var(--muted);font-size:14px"><?= h(tn(count($list), '%d product', '%d products')) ?></span></div>
    <form class="filters" method="get" action="<?= empty($CONFIG['pretty_urls']) ? 'index.php' : h(url('catalog')) ?>" id="filters" onsubmit="fsub(this); return false">
      <?php if(empty($CONFIG['pretty_urls'])): ?><input type="hidden" name="p" value="catalog"><input type="hidden" name="l" value="<?= h($LANG) ?>"><?php endif; ?>
      <?php if($q !== ''): ?><input type="hidden" name="q" value="<?= h($q) ?>"><?php endif; ?>
      <label><?= h(t('Product type')) ?><select name="cat" onchange="fsub(this.form)">
        <option value=""><?= h(t('All types')) ?></option>
        <?php foreach($CATEGORIES as $k=>$c): ?><option value="<?= h($k) ?>" <?= $cat===$k?'selected':'' ?>><?= h($c['label']) ?> (<?= (int)($cats_n[$k] ?? 0) ?>)</option><?php endforeach; ?>
      </select></label>
      <label><?= h(t('Set')) ?><select name="set" onchange="fsub(this.form)">
        <option value=""><?= h(t('All sets')) ?></option>
        <?php foreach($sets as $k=>$n): ?><option value="<?= h($k) ?>" <?= $f_set===(string)$k?'selected':'' ?>><?= h($k) ?> (<?= (int)$n ?>)</option><?php endforeach; ?>
      </select></label>
      <label><?= h(t('Condition')) ?><select name="cond" onchange="fsub(this.form)">
        <option value=""><?= h(t('Any condition')) ?></option>
        <?php foreach(CONDITIONS as $k): ?><option value="<?= h($k) ?>" <?= $f_cond===$k?'selected':'' ?>><?= h(cond_word($k)) ?> (<?= (int)($conds[$k] ?? 0) ?>)</option><?php endforeach; ?>
      </select></label>
      <label><?= h(t('Availability')) ?><select name="avail" onchange="fsub(this.form)">
        <option value=""><?= h(t('In stock & preorder')) ?></option>
        <option value="in" <?= $f_avail==='in'?'selected':'' ?>><?= h(t('In stock')) ?> (<?= $n_in ?>)</option>
        <option value="preorder" <?= $f_avail==='preorder'?'selected':'' ?>><?= h(t('Preorder')) ?> (<?= $n_pre ?>)</option>
      </select></label>
      <label class="price"><?= h(t('Price per unit (%s)', cur_code())) ?><span>
        <input type="number" name="min" min="0" step="any" placeholder="<?= h(t('Min')) ?>" value="<?= $f_min===null?'':h($f_min) ?>" aria-label="<?= h(t('Minimum price')) ?>">
        <input type="number" name="max" min="0" step="any" placeholder="<?= h(t('Max')) ?>" value="<?= $f_max===null?'':h($f_max) ?>" aria-label="<?= h(t('Maximum price')) ?>"></span></label>
      <label><?= h(t('Sort')) ?><select name="sort" onchange="fsub(this.form)">
        <?php foreach([''=>t('Featured'),'price-asc'=>t('Price: low to high'),'price-desc'=>t('Price: high to low'),'name'=>t('Name A–Z')] as $k=>$lbl): ?><option value="<?= h($k) ?>" <?= $f_sort===$k?'selected':'' ?>><?= h($lbl) ?></option><?php endforeach; ?>
      </select></label>
      <div class="fbtns"><button class="btn" type="submit"><?= h(t('Apply')) ?></button><?php if($filtered || $cat): ?><a class="btn g" href="<?= url('catalog') ?>"><?= h(t('Clear')) ?></a><?php endif; ?></div>
    </form>
    <?php if($list): ?>
      <div class="grid"><?php foreach($list as $p) include_card($p); ?></div>
    <?php else: ?>
      <p class="empty"><?= t('Nothing matches that. Try a set name, a card name or an SKU, or %s.', '<a href="'.url('catalog').'">'.h(t('browse everything')).'</a>') ?></p>
    <?php endif; ?>
    <?php if($cinfo && !$filtered && trim($cinfo['intro'] ?? '') !== ''): ?><div class="prose after"><?= rich($cinfo['intro']) ?></div><?php endif; ?>
    <?php if(!$filtered) guide_links(PAGE_GUIDES[$cinfo ? 'cat:'.$cat : 'shop'] ?? [], $cinfo ? t('Guides: %s', $cinfo['label']) : t('Pokémon card guides')); ?>
  </div></section>

<?php elseif($page==='product'):
  $ph = photos($prod['id']); $base = unit_price($prod,$prod['moq']); $best = end($prod['ladder']);
  [$summary, $more] = desc_parts($prod['desc']);
  $sser = $pset ? ($SERIES[$pset['series']] ?? null) : null;
  $moq_tier = 0; foreach($prod['ladder'] as $i=>$t){ if($t[0] <= $prod['moq']) $moq_tier = $i; } ?>
  <div class="wrap pdp">
    <div>
      <div class="gal-main">
        <?php if($ph): ?><?= str_replace('<img ', '<img id="galMain" ', img_tag($ph[0], $prod['name'], '(max-width:900px) 100vw, 620px', true)) ?>
        <?php else: ?><div class="ph"><span><?= h($CONFIG['kanji']) ?></span><span><?= h($prod['set'] ?: $CATEGORIES[$prod['cat']]['label']) ?></span></div><?php endif; ?>
      </div>
      <?php if(count($ph)>1): ?>
      <div class="gal-thumbs">
        <?php foreach($ph as $i=>$src): ?>
          <button type="button" aria-current="<?= $i===0?'true':'false' ?>" onclick="galPick(this,'<?= h($src.'?v='.@filemtime(FK_ROOT.'/'.$src)) ?>')" aria-label="<?= h(t('View photo %d', $i+1)) ?>">
            <img src="<?= h(thumb($src, 160)) ?>" width="72" height="72" alt="<?= h($prod['name'].' — '.t('photo %d', $i+1)) ?>" loading="lazy"></button>
        <?php endforeach; ?>
      </div>
      <?php endif; ?>
    </div>
    <div>
      <h1><?= h($prod['name']) ?></h1>
      <div class="sub"><?= h(implode(' · ', array_filter([$prod['set'], $CATEGORIES[$prod['cat']]['label'], cond_word($prod['cond'] ?? 'Sealed'),
        $prod['status']==='preorder' ? t('Releases %s', $prod['release']) : status_word($prod)]))) ?></div>
      <?php if($summary !== ''): ?><p class="summary-line"><?= rich_inline($summary) ?></p><?php endif; ?>

      <div class="ladder">
        <div class="lh"><?= h(t('Price per quantity')) ?></div>
        <?php foreach($prod['ladder'] as $i=>$t):
          $next = $prod['ladder'][$i+1] ?? null;
          $hi   = $next ? $next[0]-1 : null;
          /* a tier wholly below MOQ is only a reference price */
          $lbl  = ($hi!==null && $hi < $prod['moq']) ? t('Single unit')
                : ($hi===null ? t('%d+ units', $t[0]) : ($hi===$t[0] ? tn($t[0], '%d unit', '%d units') : t('%1$d – %2$d units', $t[0], $hi))); ?>
          <div class="row <?= $i===$moq_tier?'on':'' ?>">
            <span><?= h($lbl) ?></span><span><?= money($t[1]) ?></span></div>
        <?php endforeach; ?>
      </div>

      <div class="buybox">
        <div class="big"><?= money($base) ?></div>
        <div class="sm"><?= h(t('per unit, from %d', $prod['moq'])) ?><?php if(count($prod['ladder']) > 1 && $best[1] < $base): ?> · <?= h(t('down to %1$s from %2$d', money($best[1]), $best[0])) ?><?php endif; ?></div>
        <form method="post" action="<?= h($POST_URL) ?>">
          <input type="hidden" name="action" value="add">
          <input type="hidden" name="id" value="<?= h($prod['id']) ?>">
          <?php if(can_order($prod)): stepper($prod, 'qty', $prod['moq'], $prod['moq']); ?>
          <button class="btn" type="submit"><?= h($prod['status']==='preorder' ? t('Preorder now') : t('Add to order')) ?></button>
          <?php else: ?>
          <button class="btn" type="submit" disabled><?= h(t('Sold out')) ?></button>
          <?php endif; ?>
        </form>
        <div class="trustline">
          <span><?= h($prod['step']>1 ? t('Sold in %ds', $prod['step']) : t('Sold individually')) ?></span>
          <span><?= h(t('No VAT added')) ?></span>
          <span><?= h(t('Ships from Japan')) ?></span>
        </div>
      </div>
    </div>
  </div>

  <section class="top"><div class="wrap pinfo">
    <div class="panel">
      <h2><?= h(t('About this product')) ?></h2>
      <div class="prose"><?= $more !== '' ? rich($more) : '<p>'.rich_inline($summary).'</p>' ?></div>
    </div>
    <div style="display:grid;gap:16px;align-content:start">
      <div class="panel"><h2><?= h(t('Product details')) ?></h2>
        <dl class="specs">
          <?php if($pset): ?><dt><?= h(t('Set')) ?></dt><dd><a href="<?= h(url('set', ['s'=>$pset['slug']])) ?>"><?= h($pset['name']) ?></a></dd><?php endif; ?>
          <?php if($pset && $pset['code'] !== ''): ?><dt><?= h(t('Set code')) ?></dt><dd><?= h($pset['code']) ?></dd><?php endif; ?>
          <?php if($sser): ?><dt><?= h(t('Series')) ?></dt><dd><a href="<?= h(url('series', ['s'=>$sser['slug']])) ?>"><?= h($sser['name']) ?></a></dd><?php endif; ?>
          <dt><?= h(t('Product type')) ?></dt><dd><a href="<?= h(url('catalog', ['cat'=>$prod['cat']])) ?>"><?= h($CATEGORIES[$prod['cat']]['label']) ?></a></dd>
          <dt><?= h(t('Condition')) ?></dt><dd><?= h(cond_word($prod['cond'] ?? 'Sealed')) ?></dd>
          <dt><?= h(t('Availability')) ?></dt><dd><?= h($prod['status']==='preorder' ? t('Preorder · releases %s', $prod['release']) : status_word($prod)) ?></dd>
          <dt><?= h(t('Minimum order')) ?></dt><dd><?= (int)$prod['moq'] ?><?= $prod['step'] > 1 ? h(t(', then in %ds', $prod['step'])) : '' ?></dd>
          <?php if($prod['sku'] !== ''): ?><dt><?= h(t('SKU')) ?></dt><dd><?= h($prod['sku']) ?></dd><?php endif; ?>
          <dt><?= h(t('Ships from')) ?></dt><dd><?= h(t('Japan, tracked')) ?></dd>
        </dl>
      </div>
      <div class="panel"><h2><?= h(t('Shipping & payment')) ?></h2>
        <div class="prose" style="font-size:14.5px">
          <?php $SM = ship_methods($STORE); $one = (float)($prod['weight'] ?? 0) * $prod['moq']; ?>
          <p><?= h(t('Shipped from Japan with tracking: %1$s %2$s or %3$s %4$s.', $SM['standard']['label'], days_text($SM['standard']['days']), $SM['express']['label'], days_text($SM['express']['days']))) ?>
            <?= h(t('Priced by weight: %1$d of these to %2$s cost %3$s %4$s or %5$s %6$s.', $prod['moq'], $COUNTRIES[$HOME_CC] ?? $HOME_CC, money(shipping_usd($STORE, $HOME_CC, $one, 'standard')), $SM['standard']['label'], money(shipping_usd($STORE, $HOME_CC, $one, 'express')), $SM['express']['label'])) ?>
            <?php if(free_ship_usd($STORE)): ?><b><?= h(t('Free %1$s shipping on orders over %2$s.', $SM['standard']['label'], money_whole(free_ship_usd($STORE)))) ?></b><?php endif; ?>
            <?= h(t('Orders start at %s including shipping, and we add no VAT.', money($MIN_ORDER))) ?></p>
          <p><?= h(array_filter($PAYMENTS, 'btc_method') ? t('Pay with Bitcoin straight after you order, or choose another crypto and we send the details within %d hours.', (int)$CONFIG['reply_hours']) : t('We send payment details for your chosen method within %d hours.', (int)$CONFIG['reply_hours'])) ?>
            <a href="<?= url('shipping') ?>"><?= h(t('Shipping & Returns')) ?></a> · <a href="<?= url('payment') ?>"><?= h(t('Payment methods')) ?></a> · <a href="<?= url('how') ?>"><?= h(t('How ordering works')) ?></a> · <a href="<?= url('faq') ?>"><?= h(t('FAQ')) ?></a> · <a href="<?= url('contact') ?>"><?= h(t('Contact us')) ?></a></p>
        </div>
      </div>
      <?php $pg = guides_for($prod['cat']); if($pg): ?>
      <div class="panel"><h2><?= h(t('Helpful guides')) ?></h2>
        <div class="plinks"><?php foreach($pg as $g): ?><a href="<?= h(url('guide', ['g'=>$g['slug']])) ?>"><?= h(guide_anchor($g)) ?> →</a><?php endforeach; ?></div>
      </div>
      <?php endif; ?>
    </div>
  </div></section>

  <?php $sibs = $pset ? array_values(array_filter(set_products($pset['name']), fn($x)=>$x['id'] !== $prod['id'])) : [];
  if($sibs): ?>
  <section><div class="wrap">
    <div class="sechead"><div><h2><?= h(t('More from %s', $pset['name'])) ?></h2></div>
      <a href="<?= h(url('set', ['s'=>$pset['slug']])) ?>"><?= h(t('All of %s', $pset['name'])) ?> →</a></div>
    <div class="grid"><?php foreach(array_slice($sibs, 0, 4) as $p) include_card($p); ?></div>
  </div></section>
  <?php endif; ?>
  <section<?= $sibs ? ' style="padding-top:0"' : '' ?>><div class="wrap">
    <div class="sechead"><div><h2><?= h(t('More: %s', $CATEGORIES[$prod['cat']]['label'])) ?></h2></div>
      <a href="<?= url('catalog',['cat'=>$prod['cat']]) ?>"><?= h(t('See all')) ?> →</a></div>
    <div class="grid">
      <?php $rel = array_slice(array_values(array_filter($PRODUCTS, fn($x)=>$x['cat']===$prod['cat'] && $x['id']!==$prod['id'] && !in_array($x, $sibs, true))),0,4);
      if(!$rel) $rel = array_slice(array_values(array_filter($PRODUCTS, fn($x)=>$x['id']!==$prod['id'])),0,4);
      foreach($rel as $p) include_card($p); ?>
    </div>
  </div></section>

<?php elseif($page==='cart'): $lines = cart_lines(); ?>
  <section style="padding-top:22px"><div class="wrap">
    <div class="sechead"><div><h1><?= h($h1) ?></h1>
      <p><?= h(t('Quantity breaks are applied automatically. Change a quantity and the unit price recalculates.')) ?></p></div></div>
    <?php if(!$lines): ?>
      <p class="empty"><?= h(t('Nothing here yet.')) ?> <a href="<?= url('catalog') ?>"><?= h(t('Browse the shop')) ?> →</a></p>
    <?php else: ?>
      <form method="post" action="<?= h($POST_URL) ?>">
        <input type="hidden" name="action" value="update">
        <table class="tbl">
          <thead><tr><th><?= h(t('Product')) ?></th><th><?= h(t('Quantity')) ?></th><th class="r"><?= h(t('Unit')) ?></th><th class="r"><?= h(t('Line total')) ?></th><th></th></tr></thead>
          <tbody>
          <?php foreach($lines as $l): ?>
            <tr>
              <td><div class="nm"><a href="<?= url('product',['id'=>$l['p']['id']]) ?>" style="text-decoration:none"><?= h($l['p']['name']) ?></a></div>
                  <div class="sk"><?= h($l['p']['sku']) ?> · <?= h($l['p']['step'] > 1 ? t('sold in %ds', $l['p']['step']) : t('sold individually')) ?></div></td>
              <td><?php stepper($l['p'], 'qty['.$l['p']['id'].']', $l['qty'], 0); ?></td>
              <td class="r"><?= money($l['unit']) ?></td>
              <td class="r"><b><?= money($l['total']) ?></b></td>
              <td class="r"><button class="btn g" style="padding:7px 12px;font-size:13px"
                    formaction="<?= h($POST_URL) ?>" name="action" value="remove" type="submit"
                    onclick="this.form.insertAdjacentHTML('beforeend','<input type=hidden name=id value=\'<?= h($l['p']['id']) ?>\'>')"><?= h(t('Remove')) ?></button></td>
            </tr>
          <?php endforeach; ?>
          </tbody>
        </table>
        <div style="display:flex;justify-content:space-between;gap:18px;flex-wrap:wrap;margin-top:20px;align-items:center">
          <button class="btn g" type="submit"><?= h(t('Update quantities')) ?></button>
          <div style="text-align:right">
            <?php if(cart_saved()>0): ?><div style="font-size:13.5px;color:var(--muted)"><?= h(t('You save %s with quantity pricing', money(cart_saved()))) ?></div><?php endif; ?>
            <div style="font-size:26px;font-weight:900;margin:4px 0 4px"><?= h(t('Goods total %s', money(cart_total()))) ?></div>
            <?php free_ship_meter(cart_total()); ?>
            <div style="font-size:13.5px;color:var(--muted);margin-bottom:10px"><?= h(t('Shipping is calculated at checkout. Minimum order %s including shipping.', money($MIN_ORDER))) ?></div>
            <a class="btn" href="<?= url('checkout') ?>"><?= h(t('Continue to checkout')) ?></a>
          </div>
        </div>
      </form>
    <?php endif; ?>
  </div></section>

<?php elseif($page==='checkout'): $lines = cart_lines(); $f = $form ?? [];
  if(empty($f['country']) && count($COUNTRIES) === 1) $f['country'] = $HOME_CC;   /* one country: nothing to pick */
  $_SESSION['co_token'] = $_SESSION['co_token'] ?? bin2hex(random_bytes(16));
  $_SESSION['co_time']  = time(); ?>
  <section style="padding-top:22px"><div class="wrap">
    <div class="sechead"><div><h1><?= h($h1) ?></h1>
      <p><?= h(array_filter($PAYMENTS, 'btc_method') ? t('Tell us where it ships and how you want to pay. Paying by Bitcoin? You pay on the next page, straight from your wallet. For other crypto we email you the details and your invoice.') : t('Tell us where it ships and how you want to pay. We email you the payment details and your invoice after you place the order.')) ?></p></div></div>

    <?php if(!$lines): ?>
      <p class="empty"><?= h(t('Your order is empty.')) ?> <a href="<?= url('catalog') ?>"><?= h(t('Browse the shop')) ?> →</a></p>
    <?php else: ?>
    <?php if($errors): ?>
      <div class="errs"><b><?= h(t('Please fix the following:')) ?></b><ul><?php foreach($errors as $e) echo '<li>'.h($e).'</li>'; ?></ul></div>
    <?php endif; ?>

    <form method="post" action="<?= h($POST_URL) ?>" class="cogrid">
      <input type="hidden" name="action" value="order">
      <input type="hidden" name="token" value="<?= h($_SESSION['co_token']) ?>">
      <div class="hp" aria-hidden="true"><label for="website">Leave this empty</label>
        <input id="website" name="website" tabindex="-1" autocomplete="off"></div>
      <div>
        <fieldset>
          <legend><?= h(t('Contact')) ?></legend>
          <div class="two">
            <div class="fld"><label for="name"><?= h(t('Full name')) ?></label>
              <input id="name" name="name" required autocomplete="name" value="<?= h($f['name']??'') ?>"></div>
            <div class="fld"><label for="company"><?= h(t('Company (optional)')) ?></label>
              <input id="company" name="company" autocomplete="organization" value="<?= h($f['company']??'') ?>"></div>
          </div>
          <div class="two">
            <div class="fld"><label for="email"><?= h(t('Email')) ?></label>
              <input id="email" name="email" type="email" required autocomplete="email" value="<?= h($f['email']??'') ?>"></div>
            <div class="fld"><label for="phone"><?= h(t('Phone (for the carrier)')) ?></label>
              <input id="phone" name="phone" type="tel" required autocomplete="tel" placeholder="+32 …" value="<?= h($f['phone']??'') ?>"></div>
          </div>
        </fieldset>

        <fieldset>
          <legend><?= h(t('Delivery address')) ?></legend>
          <div class="fld"><label for="country"><?= h(t('Country')) ?></label>
            <select id="country" name="country" required onchange="filterPay(this.value)">
              <option value=""><?= h(t('Select your country…')) ?></option>
              <?php foreach($COUNTRIES as $code=>$nm): ?>
                <option value="<?= h($code) ?>" <?= ($f['country']??'')===$code?'selected':'' ?>><?= h($nm) ?></option>
              <?php endforeach; ?>
            </select></div>
          <div class="fld"><label for="address1"><?= h(t('Street and house number')) ?></label>
            <input id="address1" name="address1" required autocomplete="address-line1" value="<?= h($f['address1']??'') ?>"></div>
          <div class="fld"><label for="address2"><?= h(t('Box, apartment or floor (optional)')) ?></label>
            <input id="address2" name="address2" autocomplete="address-line2" value="<?= h($f['address2']??'') ?>"></div>
          <div class="two">
            <div class="fld"><label for="postcode"><?= h(t('Postcode')) ?></label>
              <input id="postcode" name="postcode" required autocomplete="postal-code" inputmode="numeric" value="<?= h($f['postcode']??'') ?>"></div>
            <div class="fld"><label for="city"><?= h(t('Town or city')) ?></label>
              <input id="city" name="city" required autocomplete="address-level2" value="<?= h($f['city']??'') ?>"></div>
          </div>
          <input type="hidden" name="region" value="<?= h($f['region']??'') ?>">
        </fieldset>

        <fieldset>
          <legend><?= h(t('Delivery')) ?></legend>
          <div class="pay" id="shipList">
            <?php foreach(ship_methods($STORE) as $mk=>$mm): ?>
              <label>
                <input type="radio" name="ship_method" value="<?= h($mk) ?>" <?= ($f['ship_method'] ?? 'standard')===$mk?'checked':'' ?>>
                <span style="flex:1"><span class="t"><?= h($mm['label']) ?></span><br><span class="n"><?= h(t('%s, tracked', days_text($mm['days']))) ?></span></span>
                <span class="t" data-ship-price="<?= h($mk) ?>"><?php if(!empty($f['country']) && isset($COUNTRIES[$f['country']])){ $sv = shipping_usd($STORE, $f['country'], cart_weight(), $mk, cart_total()); echo $sv > 0 ? money($sv) : h(t('Free')); } ?></span>
              </label>
            <?php endforeach; ?>
          </div>
          <p class="n" style="font-size:12.5px;color:var(--muted);margin-top:10px"><?= h(t('Priced by the weight of your order (%s kg).', fmt_num(cart_weight(), 2))) ?><?= count($COUNTRIES) > 1 ? ' '.h(t('Choose your country to see prices.')) : '' ?></p>
          <?php free_ship_meter(cart_total(), true); ?>
        </fieldset>

        <fieldset>
          <legend><?= h(t('Payment method')) ?></legend>
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
            <b><?= h(t('How payment works.')) ?></b>
            <?php if(array_filter($PAYMENTS, 'btc_method')): ?>
            <b>Bitcoin:</b> <?= h(t('place the order and pay on the next page. You get the exact amount and a QR code for your wallet, and a receipt by email as soon as your payment reaches the blockchain.')) ?>
            <b><?= h(t('Other crypto:')) ?></b> <?= h(t('we email you the wallet address and your invoice within %1$d hours, and hold your stock for %2$d hours.', (int)$CONFIG['reply_hours'], (int)$CONFIG['hold_hours'])) ?>
            <?php else: ?>
            <?= h(t('Select your method and place the order. We email you the payment details within %1$d hours, with your invoice, and hold your stock for %2$d hours. Quote your order reference with your payment.', (int)$CONFIG['reply_hours'], (int)$CONFIG['hold_hours'])) ?>
            <?php endif; ?>
            <?= h(t('We never ask for card details, passwords or wallet keys.')) ?>
          </div>
          <div class="fld"><label for="notes"><?= h(t('Order notes (optional)')) ?></label>
            <textarea id="notes" name="notes" placeholder="<?= h(t('Delivery instructions, your company number for the invoice, anything else we should know.')) ?>"><?= h($f['notes']??'') ?></textarea></div>
          <label class="agree">
            <input type="checkbox" name="agree" value="1" <?= !empty($_POST['agree'])?'checked':'' ?>>
            <span><?= t('I agree to the %1$s and the %2$s.', '<a href="'.h(page_url('terms') ?? url('shipping')).'" target="_blank">'.h(t('terms of sale')).'</a>', '<a href="'.h(url('shipping')).'#'.h(slugify(t('Returns'))).'" target="_blank">'.h(t('shipping & returns policy')).'</a>') ?></span>
          </label>
          <div class="minwarn" id="minWarn" hidden></div>
          <button class="btn wide" id="placeBtn" type="submit" style="margin-top:14px" data-btc-label="<?= h(t('Place order and pay with Bitcoin')) ?>" data-label="<?= h(t('Place order')) ?>"><?= h(t('Place order')) ?></button>
          <p style="font-size:12.5px;color:var(--muted);margin-top:10px"><?= h(t('Shipping is calculated from your order’s weight and shown in the order summary. We add no VAT or other tax.')) ?></p>
        </fieldset>
      </div>

      <div>
        <div class="summary">
          <h3><?= h(t('Order summary')) ?></h3>
          <?php foreach($lines as $l): ?>
            <div class="sl"><span><?= h($l['p']['name']) ?><br><span class="q"><?= h($l['qty']) ?> × <?= money($l['unit']) ?></span></span>
              <span><?= money($l['total']) ?></span></div>
          <?php endforeach; ?>
          <?php if(cart_saved()>0): ?>
            <div class="sl"><span style="color:var(--muted)"><?= h(t('Quantity discount')) ?></span>
              <span style="color:var(--seal)">− <?= money(cart_saved()) ?></span></div>
          <?php endif; ?>
          <div class="sl"><span style="color:var(--muted)"><?= h(t('Goods')) ?></span><span><?= money(cart_total()) ?></span></div>
          <div class="sl"><span style="color:var(--muted)" id="shipLabel"><?= h(t('Shipping')) ?></span><span id="shipCost" style="color:var(--muted)"><?= h(t('Select your country')) ?></span></div>
          <div class="tot"><span><?= h(t('Order total')) ?></span><span id="grandTotal"><?= money(cart_total()) ?></span></div>
          <p style="font-size:12.5px;color:var(--muted);margin-top:8px"><?= h(t('Minimum order %s including shipping.', money($MIN_ORDER))) ?></p>
          <?php
          /* per-country shipping for this cart, so the summary updates as the country changes */
          $cm = $CURRENCIES[cur_code()]; $kg = cart_weight(); $ship_by = [];
          foreach($COUNTRIES as $code=>$nm) foreach(SHIP_METHODS as $mk) $ship_by[$code][$mk] = shipping_usd($STORE, $code, $kg, $mk, cart_total());
          $labels = array_map(fn($m)=>$m['label'], ship_methods($STORE));
          $co_data = ['ship'=>$ship_by, 'labels'=>$labels, 'goods'=>cart_total(), 'min'=>$MIN_ORDER,
                      'btc'=>array_keys(array_filter($PAYMENTS, 'btc_method')),
                      'rate'=>$cm['rate'], 'cur'=>cur_code(), 'dec'=>$cm['dec'], 'locale'=>$LOCALE]; ?>
          <script>window.CO = <?= json_encode($co_data, JSON_HEX_TAG|JSON_HEX_AMP) ?>;</script>
          <p style="font-size:12.5px;color:var(--muted);margin-top:12px"><?= h(t('Prices in euros. No VAT or other tax is added.')) ?></p>
          <p style="margin-top:12px"><a href="<?= url('cart') ?>" style="font-size:13.5px;font-weight:700;color:var(--brand);text-decoration:none">← <?= h(t('Edit order')) ?></a></p>
        </div>
      </div>
    </form>
    <?php endif; ?>
  </div></section>

<?php elseif($page==='received'): $o = $_SESSION['last_order'] ?? null; ?>
  <section><div class="wrap done">
    <?php if(!$o): ?>
      <h1><?= h(t('No recent order')) ?></h1>
      <p class="lede" style="margin:14px auto 22px"><?= h(t('Nothing to show here.')) ?> <a href="<?= url('catalog') ?>"><?= h(t('Browse the shop')) ?> →</a></p>
    <?php else: ?>
      <div style="font-size:13px;color:var(--muted);letter-spacing:.08em"><?= h(uc(t('Order received'))) ?></div>
      <div class="ref"><?= h($o['ref']) ?></div>
      <h1 style="font-size:clamp(24px,3.4vw,34px);margin-top:10px"><?= h(t('Thank you, we have your order.')) ?></h1>
      <p class="lede" style="margin:14px auto 0"><?= ($o['mail_customer'] ?? true) ? t('A confirmation is on its way to %s.', '<b>'.h($o['email']).'</b>') : t('We couldn’t send a confirmation email just now, so please note your order reference. We have your order and will contact you at %s.', '<b>'.h($o['email']).'</b>') ?> <?= h(t('Your stock is reserved for %d hours.', (int)$CONFIG['hold_hours'])) ?></p>

      <div class="card2">
        <h3 style="font-size:19px"><?= h(t('What happens next')) ?></h3>
        <ol>
          <li><?= t('Within %1$d hours we email you the payment details for %2$s, with your invoice.', (int)$CONFIG['reply_hours'], '<b>'.h($o['payment_label']).'</b>') ?></li>
          <li><?= t('Quote %s with your payment so we can match it to your order.', '<b>'.h($o['ref']).'</b>') ?></li>
          <li><?= h(t('Once payment has arrived, we dispatch within %d hours from Japan, with tracking.', (int)$CONFIG['hold_hours'])) ?></li>
          <li><?= h(t('We email your tracking number as soon as the label is created.')) ?></li>
        </ol>
        <hr style="border:none;border-top:1px solid var(--hair);margin:20px 0">
        <div class="sl" style="border:none;padding:0"><span><?= h(t('Goods')) ?></span><span><?= h($o['goods']) ?></span></div>
        <div class="sl" style="border:none;padding:4px 0 0"><span><?= h(t('Shipping')) ?><?= !empty($o['ship_label']) ? ' · '.h($o['ship_label']) : '' ?></span><span><?= (float)$o['shipping_usd'] == 0 ? h(t('Free')) : h($o['shipping']) ?></span></div>
        <div class="sl" style="border:none;padding:4px 0 0"><span><?= h(t('Order total')) ?></span><b><?= h($o['total']) ?></b></div>
        <div class="sl" style="border:none;padding:4px 0 0"><span><?= h(t('Delivery to')) ?></span><span><?= h($o['city']) ?>, <?= h($o['country_name']) ?></span></div>
        <p style="font-size:13.5px;color:var(--muted);margin-top:16px">
          <?= t('Nothing heard within %1$d hours? Check your spam folder, then email %2$s quoting %3$s.', (int)$CONFIG['reply_hours'], '<a href="mailto:'.h($CONFIG['email']).'">'.h($CONFIG['email']).'</a>', h($o['ref'])) ?></p>
      </div>
      <p style="margin-top:24px"><a class="btn g" href="<?= url('catalog') ?>"><?= h(t('Continue shopping')) ?></a></p>
    <?php endif; ?>
  </div></section>

<?php elseif($page==='pay'): $o = $pay; $b = $o['btc']; $st = btc_state($o); $key = order_key($o['ref']);
  $cancelled = ($o['status'] ?? '') === 'cancelled'; $need = btc_settings()['confs']; $conf = (int)($b['confirmations'] ?? 0);
  $open = !$cancelled && in_array($st, ['awaiting','reported'], true); $uri = btc_uri($o);
  $heads = ['awaiting'=>t('Pay with Bitcoin'), 'reported'=>t('Checking your payment'), 'seen'=>t('Payment received'), 'confirmed'=>t('Paid. Thank you!'), 'short'=>t('Payment received')]; ?>
  <section style="padding-top:22px"><div class="wrap">
    <div class="payhead">
      <div class="kick"><?= h(t('Order')) ?> <b><?= h($o['ref']) ?></b> · <?= h($o['time']) ?></div>
      <h1><?= $cancelled ? h(t('Order cancelled')) : h($heads[$st]) ?></h1>
      <?php if($open): ?><p class="lede"><?= h(t('Your order is placed and your stock is reserved. Pay from any Bitcoin wallet: scan the code, or copy the amount and address.')) ?></p>
      <?php elseif(!$cancelled): ?><p class="lede"><?= t('We’ve emailed your receipt to %s.', '<b>'.h($o['email']).'</b>') ?> <?= h($st === 'confirmed' ? t('We’re packing your order and will email your tracking number when it ships.') : t('Your payment is on the blockchain; this page updates when it confirms.')) ?></p><?php endif; ?>
    </div>

    <div class="paygrid">
      <div class="paybox" id="payBox" data-state="<?= h($st) ?>" data-quoted="<?= (int)($b['quoted'] ?? 0) ?>"
           data-status="<?= h(url('paystatus', ['ref'=>$o['ref'], 'k'=>$key])) ?>">
      <?php if($cancelled): ?>
        <p><?= t('This order has been cancelled, so please don’t send a payment for it. If that’s a mistake, email %s.', '<a href="mailto:'.h($CONFIG['email']).'">'.h($CONFIG['email']).'</a>') ?></p>
      <?php elseif($open): ?>
        <div class="payrow">
          <div class="qrcol">
            <div class="qr" id="qr" data-uri="<?= h($uri) ?>"><span class="qrph">QR code</span></div>
            <a class="btn wide gold" href="<?= h($uri) ?>"><?= h(t('Open in wallet app')) ?></a>
          </div>
          <div class="pf">
            <?php if(!empty($b['sats'])): ?>
              <div class="l"><?= h(t('Send exactly')) ?></div>
              <div class="v amt"><span><?= btc_amount($b['sats']) ?></span> <small>BTC</small>
                <button type="button" class="copy" data-copy="<?= btc_amount($b['sats']) ?>"><?= h(t('Copy')) ?></button></div>
              <div class="n"><?= h(t('Order total %1$s · 1 BTC = $%2$s (%3$s)', $o['total'], number_format($b['rate'], 2), $b['rate_source'])) ?></div>
            <?php else: ?>
              <div class="l"><?= h(t('Amount')) ?></div>
              <div class="v amt"><?= h($o['total']) ?> <small><?= h(t('in BTC')) ?></small></div>
              <div class="n"><?= h(t('We couldn’t get the Bitcoin price just now. This page tries again every minute, so please wait for the exact BTC amount before paying.')) ?></div>
            <?php endif; ?>
            <div class="l" style="margin-top:18px"><?= h(t('To this Bitcoin address')) ?></div>
            <div class="v addr"><code><?= h($b['address']) ?></code>
              <button type="button" class="copy" data-copy="<?= h($b['address']) ?>"><?= h(t('Copy')) ?></button></div>
            <?php if(!empty($b['sats'])): ?>
              <div class="hold"><?= t('Amount held for %s. After that it updates to the current rate.', '<b id="countdown" data-expires="'.(int)$b['expires'].'">'.gmdate('i:s', max(0, $b['expires'] - time())).'</b>') ?></div>
            <?php endif; ?>
            <div class="watch"><i class="pulse"></i> <?= h($st === 'reported' ? t('Checking transaction %s… This page updates by itself.', substr($b['reported'], 0, 12)) : t('Watching the blockchain for your payment. This page updates by itself.')) ?></div>
          </div>
        </div>
        <ul class="paytips">
          <li><b><?= h(t('Send the exact amount in one payment.')) ?></b> <?= h(t('If your exchange takes its withdrawal fee from the amount, add the fee on top.')) ?></li>
          <li><b><?= h(t('Bitcoin network only.')) ?></b> <?= h(t('Not Lightning, and not wrapped “BTC” on other networks such as BEP-20 or ERC-20.')) ?></li>
          <li><b><?= h(t('Come back any time.')) ?></b> <?= h(t('The link to this page is in your order email, %s.', $o['email'])) ?></li>
        </ul>
        <details class="txform"<?= $st === 'reported' ? ' open' : '' ?>><summary><?= h(t('Paid already? Add your transaction ID')) ?></summary>
          <form method="post" action="<?= h($POST_URL) ?>">
            <input type="hidden" name="action" value="btc_txid"><input type="hidden" name="ref" value="<?= h($o['ref']) ?>"><input type="hidden" name="k" value="<?= h($key) ?>">
            <label for="txid"><?= h(t('Transaction ID (or a link to it on a block explorer)')) ?></label>
            <div class="txrow"><input id="txid" name="txid" autocomplete="off" spellcheck="false" placeholder="4a5e1e4baab89f3a32518a88c31bc87f…" required>
              <button class="btn" type="submit"><?= h(t('Check payment')) ?></button></div>
          </form>
        </details>
      <?php else: $short = $st === 'short'; ?>
        <ol class="timeline">
          <li class="ok"><b><?= h(t('Order placed')) ?></b><span><?= h($o['time']) ?></span></li>
          <li class="ok"><b><?= h(t('Payment sent')) ?></b><span><?= btc_amount($b['paid_sats']) ?> BTC<?= $short ? h(t(', less than the %s BTC due', btc_amount($b['expected_sats']))) : '' ?></span></li>
          <li class="<?= $conf >= $need ? 'ok' : 'now' ?>"><b><?= h(t('Confirmed on the blockchain')) ?></b><span><?= h($conf >= $need ? t('Confirmed') : t('Waiting for confirmation, usually 10–60 minutes')) ?></span></li>
          <li class="<?= ($o['status'] ?? '') === 'shipped' ? 'ok' : ($conf >= $need && !$short ? 'now' : '') ?>"><b><?= h(t('Shipped from Japan')) ?></b><span><?= h(($o['status'] ?? '') === 'shipped' ? t('On its way: tracking is in your email') : t('Within %d hours of confirmation, with tracking', (int)$CONFIG['hold_hours'])) ?></span></li>
        </ol>
        <?php if($short): ?><div class="errs" style="margin-top:16px"><?= t('Your payment was less than the amount due. Please email %s and we’ll sort out the difference.', '<a href="mailto:'.h($CONFIG['email']).'">'.h($CONFIG['email']).'</a>') ?></div><?php endif; ?>
        <div class="txbox"><div class="l"><?= h(t('Transaction')) ?></div><code><?= h($b['txid']) ?></code>
          <a href="<?= h(btc_tx_url($b['txid'])) ?>" target="_blank" rel="noopener"><?= h(t('Track it on mempool.space')) ?> ↗</a></div>
      <?php endif; ?>
      </div>

      <aside class="summary">
        <h3><?= h(t('Your order')) ?></h3>
        <?php foreach($o['lines'] as $l): ?>
          <div class="sl"><span><?= h($l['name']) ?><br><span class="q"><?= (int)$l['qty'] ?> × <?= h($l['unit']) ?></span></span><span><?= h($l['total']) ?></span></div>
        <?php endforeach; ?>
        <div class="sl"><span style="color:var(--muted)"><?= h(t('Goods')) ?></span><span><?= h($o['goods']) ?></span></div>
        <div class="sl"><span style="color:var(--muted)"><?= h(t('Shipping')) ?> · <?= h($o['ship_label']) ?></span><span><?= !empty($o['free_shipping']) && (float)$o['shipping_usd'] == 0 ? h(t('Free')) : h($o['shipping']) ?></span></div>
        <div class="tot"><span><?= h(t('Order total')) ?></span><span><?= h($o['total']) ?></span></div>
        <p class="n"><?= h(t('Delivery to %1$s, %2$s. The BTC amount is worked out from your order total at the live Bitcoin price.', $o['city'], $o['country_name'])) ?></p>
        <p class="n"><?= h(t('Questions?')) ?> <a href="mailto:<?= h($CONFIG['email']) ?>?subject=<?= rawurlencode(t('Order %s', $o['ref'])) ?>"><?= h($CONFIG['email']) ?></a></p>
      </aside>
    </div>
  </div></section>
  <?php if($open): ?><script src="assets/qrcode.min.js?v=<?= @filemtime(FK_ROOT.'/assets/qrcode.min.js') ?>" defer></script><?php endif; ?>

<?php elseif($page==='how'): ?>
  <section style="padding-top:22px"><div class="wrap">
    <div class="sechead"><div><h1><?= h($h1) ?></h1>
      <p><?= h(t('Four steps from cart to courier, with no account to create: minimum quantities and quantity-break prices are on every listing. Pay by Bitcoin straight from your wallet, or with ETH or USDT against an invoice.')) ?><?php if($wu = page_url('wholesale')): ?> <?= t('Buying for a shop? See our %s.', '<a href="'.h($wu).'">'.h(t('wholesale terms')).'</a>') ?><?php endif; ?></p></div></div>
    <?php steps_block($CONFIG); ?>
    <div style="margin-top:40px" class="cats three">
      <a href="<?= url('payment') ?>"><h3><?= h(t('Payment methods')) ?></h3><p><?= h(t('Bitcoin and other crypto, and how each works.')) ?></p></a>
      <a href="<?= url('shipping') ?>"><h3><?= h(t('Shipping & Returns')) ?></h3><p><?= h(t('Rates, delivery times, returns and refunds.')) ?></p></a>
      <a href="<?= url('faq') ?>"><h3><?= h(t('FAQ')) ?></h3><p><?= h(t('Minimum order, preorders, authenticity and more.')) ?></p></a>
    </div>
  </div></section>

<?php elseif($page==='payment'): ?>
  <section style="padding-top:22px"><div class="wrap" style="max-width:820px">
    <div class="sechead"><div><h1><?= h($h1) ?></h1>
      <p><?= h(array_filter($PAYMENTS, 'btc_method') ? t('Choose your method at checkout. Bitcoin is paid on your order page the moment you order. For other crypto, we email you the details within %d hours, with your invoice.', (int)$CONFIG['reply_hours']) : t('Choose your method at checkout. We email you the details within %d hours, with your invoice.', (int)$CONFIG['reply_hours'])) ?></p></div></div>
    <table class="tbl">
      <thead><tr><th><?= h(t('Method')) ?></th><th><?= h(t('Available in')) ?></th><th><?= h(t('How it works')) ?></th></tr></thead>
      <tbody>
        <?php foreach($PAYMENTS as $m):
          $where = $m['countries']==='*' ? t('All countries')
                 : implode(', ', array_map(fn($c)=>$COUNTRIES[$c] ?? country_name($c), $m['countries'])); ?>
          <tr><td class="nm"><?= h($m['label']) ?></td><td><?= h($where) ?></td><td><?= h($m['note']) ?></td></tr>
        <?php endforeach; ?>
      </tbody>
    </table>
    <?php if(array_filter($PAYMENTS, 'btc_method')): ?>
    <h2 class="sub2"><?= h(t('Paying with Bitcoin')) ?></h2>
    <div class="prose">
      <ol>
        <li><b><?= h(t('Place your order')) ?></b> <?= h(t('and choose Bitcoin at checkout.')) ?></li>
        <li><b><?= h(t('Scan the QR code')) ?></b> <?= h(t('on your order page with any Bitcoin wallet, or copy the exact amount and our address. The amount is held for %d minutes at the current rate.', (int)btc_settings()['minutes'])) ?></li>
        <li><b><?= h(t('Get your receipt.')) ?></b> <?= h(t('The page spots your payment on the blockchain and we email a receipt with a link to follow it.')) ?></li>
        <li><b><?= h(t('We ship')) ?></b> <?= h(t('within %d hours of it confirming, usually 10–60 minutes after you pay.', (int)$CONFIG['hold_hours'])) ?></li>
      </ol>
      <p><?= t('Always check that the address on your order page is %s. We never send a different Bitcoin address by email or chat.', '<code>'.h(btc_settings()['address']).'</code>') ?></p>
    </div>
    <?php endif; ?>
    <div class="notice" style="margin-top:22px">
      <b><?= h(t('We never ask for card details, passwords or wallet keys.')) ?></b>
      <?= h(t('For crypto other than Bitcoin, you place the order and we email you our wallet address, holding your stock for %1$d hours in the meantime. Always check payment details against the email we send from %2$s and quote your order reference.', (int)$CONFIG['hold_hours'], $CONFIG['email'])) ?>
    </div>
    <p style="font-size:14px;color:var(--ink2);margin-top:18px"><?= h(t('Invoices are in euros. Shipping is calculated at checkout. We add no VAT or other tax: the total at checkout is what you pay us.')) ?></p>
  </div></section>

<?php elseif($page==='shipping'):
  $SM = ship_methods($STORE); $fs = free_ship_usd($STORE);
  $policy = str_replace("\r", '', (string)($CONFIG['shipping_policy'] ?? ''));
  $parts = preg_split('/^[ \t]*\{rates\}[ \t]*$/m', $policy, 2);
  preg_match_all('/^##\s+(.+)$/m', fill($policy), $heads); ?>
  <section style="padding-top:22px"><div class="wrap">
    <div class="srhead">
      <h1><?= h($h1) ?></h1>
      <p class="lede"><?= h(t('Shipped from Japan to Belgium with tracking, packed with care, and clearly priced before you pay.')) ?></p>
    </div>
    <div class="srcards">
      <?php if($fs): ?><div class="k1"><span class="ic"><svg viewBox="0 0 24 24" aria-hidden="true"><path fill="currentColor" d="M3 6.5A1.5 1.5 0 0 1 4.5 5h9A1.5 1.5 0 0 1 15 6.5V8h2.6a1.5 1.5 0 0 1 1.2.6l2.4 3.2c.2.26.3.58.3.9V16a1.5 1.5 0 0 1-1.5 1.5h-.6a2.75 2.75 0 0 1-5.3 0H9.9a2.75 2.75 0 0 1-5.3 0h-.1A1.5 1.5 0 0 1 3 16V6.5Zm12 3V13h4.5l-1.9-2.5a1.5 1.5 0 0 0-1.2-.6H15ZM7.25 18.25a1 1 0 1 0 0-2 1 1 0 0 0 0 2Zm9.4 0a1 1 0 1 0 0-2 1 1 0 0 0 0 2Z"/></svg></span><b><?= h(t('Free shipping')) ?></b><span><?= h(t('Orders over %s', money_whole($fs))) ?></span></div><?php endif; ?>
      <div class="k2"><span class="ic"><svg viewBox="0 0 24 24" aria-hidden="true"><path fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" d="M12 7v5l3 2M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z"/></svg></span><b><?= h($SM['standard']['label']) ?></b><span><?= h(t('%s, tracked', days_text($SM['standard']['days']))) ?></span></div>
      <div class="k3"><span class="ic"><svg viewBox="0 0 24 24" aria-hidden="true"><path fill="currentColor" d="M13.5 2 4 13.5h6.5L9.5 22 20 9.5h-6.6L13.5 2Z"/></svg></span><b><?= h($SM['express']['label']) ?></b><span><?= h(t('%s, tracked', days_text($SM['express']['days']))) ?></span></div>
      <div class="k4"><span class="ic"><svg viewBox="0 0 24 24" aria-hidden="true"><path fill="none" stroke="currentColor" stroke-width="2" stroke-linejoin="round" d="M3.5 7.5 12 3l8.5 4.5v9L12 21l-8.5-4.5v-9ZM3.5 7.5 12 12m0 0 8.5-4.5M12 12v9"/></svg></span><b><?= h(t('Dispatched fast')) ?></b><span><?= h(t('Within %d hours of payment', (int)$CONFIG['hold_hours'])) ?></span></div>
    </div>
    <div class="srgrid">
      <?php if(count($heads[1]) >= 3): ?>
      <nav class="srtoc" aria-label="<?= h(t('On this page')) ?>"><b><?= h(t('On this page')) ?></b><ol>
        <?php foreach($heads[1] as $hd): ?><li><a href="<?= h(url('shipping')) ?>#<?= h(slugify($hd)) ?>"><?= h(preg_replace('/\[([^\]]+)\]\([^)]*\)|\*\*/', '$1', $hd)) ?></a></li><?php endforeach; ?>
      </ol></nav>
      <?php endif; ?>
      <article class="prose sr">
        <?= rich($parts[0]) ?>
        <?php if(count($parts) === 2): ship_rates_block(); echo rich($parts[1]); endif; ?>
        <div class="srcontact">
          <div><b><?= h(t('Still have a question?')) ?></b><span><?= t('We reply within %1$d hours. See the %2$s or %3$s.', (int)$CONFIG['reply_hours'], '<a href="'.url('faq').'">'.h(t('FAQ')).'</a>', '<a href="'.url('contact').'">'.h(t('contact us')).'</a>') ?></span></div>
          <div class="row2">
            <a class="btn" href="mailto:<?= h($CONFIG['email']) ?>"><?= h(t('Email %s', $CONFIG['email'])) ?></a>
            <?php if(trim($CONFIG['chat_code'] ?? '') !== ''): ?><button type="button" class="btn g" data-open-chat hidden><?= h(t('Chat with us')) ?></button><?php endif; ?>
          </div>
        </div>
      </article>
    </div>
  </div></section>
  <?php $qa = policy_questions($policy);
  if($qa): ?><script type="application/ld+json"><?= json_encode(['@context'=>'https://schema.org','@type'=>'FAQPage','mainEntity'=>array_map(fn($x)=>
    ['@type'=>'Question','name'=>$x[0],'acceptedAnswer'=>['@type'=>'Answer','text'=>$x[1]]], $qa)], JSON_UNESCAPED_SLASHES|JSON_UNESCAPED_UNICODE|JSON_HEX_TAG) ?></script><?php endif; ?>

<?php elseif($page==='faq'): ?>
  <section style="padding-top:22px"><div class="wrap" style="max-width:860px">
    <div class="sechead"><div><h1><?= h($h1) ?></h1></div></div>
    <div class="faq">
      <?php $faqs = array_map(fn($f)=>[fill($f[0]), fill($f[1])], $STORE['faqs']);
      foreach($faqs as $i=>$fq): ?>
        <details <?= $i===0?'open':'' ?>><summary><?= h($fq[0]) ?></summary><p><?= h($fq[1]) ?></p></details>
      <?php endforeach; ?>
    </div>
    <?php $more = [[t('Shipping & Returns'), url('shipping')], [t('How ordering works'), url('how')], [t('Payment methods'), url('payment')]];
    foreach(['wholesale'=>t('Wholesale terms'), 'about'=>t('About %s', $CONFIG['brand']), 'terms'=>t('Terms of sale'), 'privacy'=>t('Privacy policy')] as $pk=>$lb) if($pu = page_url($pk)) $more[] = [$lb, $pu];
    $more[] = [t('Contact us'), url('contact')];
    guide_links(PAGE_GUIDES['faq'], t('More answers'), $more); ?>
  </div></section>
  <script type="application/ld+json">
  <?= json_encode(['@context'=>'https://schema.org','@type'=>'FAQPage','mainEntity'=>array_map(fn($f)=>
    ['@type'=>'Question','name'=>$f[0],'acceptedAnswer'=>['@type'=>'Answer','text'=>$f[1]]], $faqs)],
    JSON_UNESCAPED_SLASHES|JSON_UNESCAPED_UNICODE|JSON_HEX_TAG) ?>
  </script>

<?php elseif($page==='contact'): ?>
  <section style="padding-top:22px"><div class="wrap" style="max-width:700px">
    <div class="sechead"><div><h1><?= h($h1) ?></h1>
      <p><?= h(t('Questions about stock, bulk prices or an existing order? We reply within %d hours, in Dutch, French or English.', (int)$CONFIG['reply_hours'])) ?></p></div></div>
    <table class="tbl">
      <tbody>
        <tr><td class="nm"><?= h(t('Email')) ?></td><td><a href="mailto:<?= h($CONFIG['email']) ?>"><?= h($CONFIG['email']) ?></a></td></tr>
        <?php if($CONFIG['phone']): ?><tr><td class="nm"><?= h(t('Phone')) ?></td><td><?= h($CONFIG['phone']) ?></td></tr><?php endif; ?>
        <tr><td class="nm"><?= h(t('Company')) ?></td><td><?= h($CONFIG['legal_name']) ?>, <?= h($CONFIG['address']) ?></td></tr>
        <tr><td class="nm"><?= h(t('Existing order')) ?></td><td><?= h(t('Quote your order reference (format FK-26-XXXXX) in the subject line.')) ?></td></tr>
      </tbody>
    </table>
    <p style="margin-top:22px;font-size:14.5px;color:var(--ink2)"><?= h(t('For large quantities, full cases or an allocation on an upcoming release, email us the sets and quantities you want and we will come back with a price.')) ?></p>
  </div></section>

<?php elseif($page==='sets'): ?>
  <section class="top"><div class="wrap">
    <div class="sechead"><div><h1><?= h($h1) ?></h1>
      <p><?= h(t('Every Japanese Pokémon card set we stock. Japanese sets come out months before the English and French versions, so these are the newest cards in the hobby.')) ?></p></div></div>
    <?php $shown = [];
    foreach($SERIES as $k=>$sr): $ss = series_sets($k); if(!$ss) continue; $shown += $ss; ?>
      <div class="sechead" style="margin:30px 0 14px"><div><h2><?= h($sr['name']) ?></h2></div>
        <a href="<?= h(url('series', ['s'=>$sr['slug']])) ?>"><?= h(t('About %s', $sr['name'])) ?> →</a></div>
      <?php set_tiles($ss);
    endforeach;
    $other = array_diff_key(sets_all(), $shown);
    if($other): ?><div class="sechead" style="margin:30px 0 14px"><div><h2><?= h(t('Other sets')) ?></h2></div></div><?php set_tiles($other); endif; ?>
  </div></section>

<?php elseif($page==='series'):
  $ss = series_sets($series['key']);
  $sp = array_values(array_filter($PRODUCTS, fn($p)=>isset($ss[$p['set']]))); ?>
  <section class="top"><div class="wrap">
    <div class="sechead"><div><h1><?= h($h1) ?></h1></div><span class="count"><?= h(tn(count($sp), '%d product', '%d products')) ?></span></div>
    <?php if(trim($series['intro'] ?? '') !== ''): ?><div class="prose lead"><?= rich($series['intro']) ?></div><?php endif; ?>
    <?php if($ss): ?><h2 class="sub2"><?= h(t('Sets')) ?></h2><?php set_tiles($ss); endif; ?>
    <?php if($sp): ?><h2 class="sub2"><?= h(t('All %s products', $series['name'])) ?></h2><div class="grid"><?php foreach($sp as $p) include_card($p); ?></div><?php endif; ?>
    <?php guide_links(PAGE_GUIDES['series'], t('Pokémon card guides')); ?>
  </div></section>

<?php elseif($page==='set'):
  $sp = set_products($set['name']);
  $others = isset($SERIES[$set['series']]) ? array_diff_key(series_sets($set['series']), [$set['name']=>true]) : []; ?>
  <section class="top"><div class="wrap">
    <div class="sechead"><div><h1><?= h($h1) ?></h1></div><span class="count"><?= h(tn(count($sp), '%d product', '%d products')) ?></span></div>
    <?php if(trim($set['intro']) !== ''): ?><div class="prose lead"><?= rich($set['intro']) ?></div><?php endif; ?>
    <div class="grid"><?php foreach($sp as $p) include_card($p); ?></div>
    <?php if($others): ?><h2 class="sub2"><?= h(t('More %s sets', $SERIES[$set['series']]['name'])) ?></h2><?php set_tiles($others); endif; ?>
    <?php guide_links(PAGE_GUIDES['set'], t('Pokémon card guides')); ?>
  </div></section>

<?php elseif($page==='collection'):
  $cp = collection_products($coll); ?>
  <section class="top"><div class="wrap">
    <div class="sechead"><div><h1><?= h($h1) ?></h1></div><span class="count"><?= h(tn(count($cp), '%d product', '%d products')) ?></span></div>
    <?php if(trim($coll['intro'] ?? '') !== ''): ?><div class="prose lead"><?= rich($coll['intro']) ?></div><?php endif; ?>
    <?php if($cp): ?><div class="grid"><?php foreach($cp as $p) include_card($p); ?></div>
    <?php else: ?><p class="empty"><?= t('Nothing in stock here right now. See all %s.', '<a href="'.url('catalog', ['cat'=>'singles']).'">'.h($CATEGORIES['singles']['label'] ?? t('single cards')).'</a>') ?></p><?php endif; ?>
    <?php guide_links(PAGE_GUIDES['coll'], t('Helpful guides')); ?>
  </div></section>

<?php elseif($page==='guides'): ?>
  <section class="top"><div class="wrap">
    <div class="sechead"><div><h1><?= h($h1) ?></h1>
      <p><?= h(t('Straight answers about Pokémon cards: what they cost and what they’re worth, Japanese cards, new releases, set lists, how to play, and how to spot fakes.')) ?></p></div></div>
    <?php guide_cards($GUIDES); ?>
  </div></section>

<?php elseif($page==='guide'): ?>
  <section class="top"><div class="wrap">
    <article class="article">
      <h1><?= h($h1) ?></h1>
      <?php if(!empty($guide['updated'])): ?><p class="meta"><?= h(t('Updated %s', fmt_date(strtotime($guide['updated'])))) ?></p><?php endif; ?>
      <?php preg_match_all('/^##\s+(.+)$/m', str_replace("\r", '', fill($guide['body'] ?? '')), $heads);
      if(count($heads[1]) >= 3): ?>
      <nav class="toc" aria-label="<?= h(t('In this guide')) ?>"><b><?= h(t('In this guide')) ?></b><ol>
        <?php foreach($heads[1] as $hd): $plainhd = preg_replace('/\[([^\]]+)\]\([^)]*\)|\*\*/', '$1', $hd); ?>
          <li><a href="<?= h(url('guide', ['g'=>$guide['slug']])) ?>#<?= h(slugify($hd)) ?>"><?= h($plainhd) ?></a></li>
        <?php endforeach; ?>
      </ol></nav>
      <?php endif; ?>
      <div class="prose"><?php guide_body($guide['body'] ?? ''); ?></div>
      <div class="notice" style="margin-top:28px"><?= rich_inline(guide_shop($guide['key'] ?? '')) ?></div>
    </article>
    <?php $more = guides_related($guide);
    if($more): ?><h2 class="sub2"><?= h(t('Related guides')) ?></h2><?php guide_cards($more); endif; ?>
  </div></section>
  <?php $qa = policy_questions($guide['body'] ?? '');
  if($qa): ?><script type="application/ld+json"><?= json_encode(['@context'=>'https://schema.org','@type'=>'FAQPage','mainEntity'=>array_map(fn($x)=>
    ['@type'=>'Question','name'=>$x[0],'acceptedAnswer'=>['@type'=>'Answer','text'=>$x[1]]], $qa)], JSON_UNESCAPED_SLASHES|JSON_UNESCAPED_UNICODE|JSON_HEX_TAG) ?></script><?php endif; ?>

<?php elseif($page==='page'): ?>
  <section class="top"><div class="wrap">
    <article class="article">
      <h1><?= h($h1) ?></h1>
      <div class="prose" style="margin-top:18px"><?= rich($info['body'] ?? '') ?></div>
    </article>
    <?php if(($info['key'] ?? '') === 'wholesale') guide_links(PAGE_GUIDES['shop'], t('Guides for buyers')); ?>
  </div></section>
  <?php $qa = policy_questions($info['body'] ?? '');
  if($qa): ?><script type="application/ld+json"><?= json_encode(['@context'=>'https://schema.org','@type'=>'FAQPage','mainEntity'=>array_map(fn($x)=>
    ['@type'=>'Question','name'=>$x[0],'acceptedAnswer'=>['@type'=>'Answer','text'=>$x[1]]], $qa)], JSON_UNESCAPED_SLASHES|JSON_UNESCAPED_UNICODE|JSON_HEX_TAG) ?></script><?php endif; ?>

<?php elseif($page==='notfound'): ?>
  <section class="top"><div class="wrap" style="max-width:720px">
    <h1><?= h($h1) ?></h1>
    <p class="lede"><?= t('That page doesn’t exist: it may have moved. Try the %1$s, browse %2$s, or search above.', '<a href="'.url('catalog').'">'.h(t('shop')).'</a>', '<a href="'.url('sets').'">'.h(t('Pokémon card sets')).'</a>') ?></p>
  </div></section>

<?php endif; ?>
</main>

<footer class="site"><div class="wrap">
  <div class="fg">
    <div>
      <div class="brand"><span class="mk"><?= h($CONFIG['brand']) ?></span>
        <span class="kj"><?= h($CONFIG['kanji']) ?></span></div>
      <p class="bl"><?= h($CONFIG['footer_blurb']) ?></p>
    </div>
    <div><h4><?= h(t('Shop')) ?></h4><ul>
      <?php foreach($CATEGORIES as $k=>$c): ?>
        <li><a href="<?= url('catalog',['cat'=>$k]) ?>"><?= h($c['label']) ?></a></li>
      <?php endforeach; ?>
      <li><a href="<?= url('catalog') ?>"><?= h(t('All products')) ?></a></li>
    </ul></div>
    <div><h4><?= h(t('Explore')) ?></h4><ul>
      <li><a href="<?= url('sets') ?>"><?= h(t('Pokémon card sets')) ?></a></li>
      <?php foreach($COLLECTIONS as $c): if(collection_products($c)): ?><li><a href="<?= h(url('collection', ['c'=>$c['slug']])) ?>"><?= h($c['title']) ?></a></li><?php endif; endforeach; ?>
      <?php foreach(guides_list(['prices', 'value', 'buy', 'japanese', 'releases']) as $g): ?><li><a href="<?= h(url('guide', ['g'=>$g['slug']])) ?>"><?= h(guide_anchor($g)) ?></a></li><?php endforeach; ?>
      <?php if($GUIDES): ?><li><a href="<?= url('guides') ?>"><?= h(t('All Pokémon card guides')) ?></a></li><?php endif; ?>
    </ul></div>
    <div><h4><?= h(t('Ordering')) ?></h4><ul>
      <?php if($wu = page_url('wholesale')): ?><li><a href="<?= h($wu) ?>"><?= h(t('Wholesale terms')) ?></a></li><?php endif; ?>
      <li><a href="<?= url('how') ?>"><?= h(t('How it works')) ?></a></li>
      <li><a href="<?= url('payment') ?>"><?= h(t('Payment methods')) ?></a></li>
      <li><a href="<?= url('shipping') ?>"><?= h(t('Shipping & Returns')) ?></a></li>
      <li><a href="<?= url('cart') ?>"><?= h(t('Your order')) ?></a></li>
    </ul></div>
    <div><h4><?= h(t('Help')) ?></h4><ul>
      <li><a href="<?= url('faq') ?>"><?= h(t('FAQ')) ?></a></li>
      <li><a href="<?= url('contact') ?>"><?= h(t('Contact')) ?></a></li>
      <?php foreach($INFO_PAGES as $ip): if(($ip['key'] ?? '') === 'wholesale') continue; ?><li><a href="<?= h(url('page', ['pg'=>$ip['slug']])) ?>"><?= h(fill($ip['title'])) ?></a></li><?php endforeach; ?>
      <li><a href="mailto:<?= h($CONFIG['email']) ?>"><?= h($CONFIG['email']) ?></a></li>
    </ul></div>
  </div>
  <div class="legal">
    <div><?= h(t('Shipped from Japan to Belgium with tracking · Prices in euros · We add no VAT or other tax.')) ?></div>
    <div><?= h(t('%s is an independent reseller of genuine product. We are not affiliated with, endorsed by or licensed by The Pokémon Company, Nintendo, Creatures Inc. or GAME FREAK Inc. All product names and trademarks belong to their owners.', $CONFIG['legal_name'])) ?></div>
    <div>© <?= date('Y') ?> <?= h($CONFIG['legal_name']) ?> · <?php foreach(LANGS as $L=>$lname): ?><?php if($L !== $LANG): ?><a href="<?= h($ALTS[$L] ?? url('home', [], $L)) ?>" hreflang="<?= LANG_LOCALE[$L] ?>" lang="<?= LANG_LOCALE[$L] ?>"><?= h($lname) ?></a><?php endif; ?><?php endforeach; ?></div>
  </div>
</div></footer>

<?php if(($chat = trim($CONFIG['chat_code'] ?? '')) !== ''): ?>
<template id="chatCode"><?= $chat ?></template>
<?php if($who = ($pay ?: ($_SESSION['last_order'] ?? null))): /* lets the chat show which customer you're talking to */ ?>
<script>window.Tawk_API = window.Tawk_API || {}; Tawk_API.visitor = <?= json_encode(['name'=>$who['name'], 'email'=>$who['email']], JSON_HEX_TAG|JSON_HEX_AMP) ?>;</script>
<?php endif; endif; ?>
<script>
window.T = <?= json_encode(['free'=>t('Free'), 'select'=>t('Select your country'), 'shipping'=>t('Shipping'),
  'min'=>t('The minimum order is %1$s including shipping. Your total is %2$s, so add %3$s more to place this order.'),
  'one'=>t('%d product'), 'many'=>t('%d products'), 'copy'=>t('Copy'), 'copied'=>t('Copied ✓'), 'updating'=>t('updating…'),
  'qr'=>t('QR code with our Bitcoin address and the amount')], JSON_HEX_TAG|JSON_HEX_AMP|JSON_UNESCAPED_UNICODE) ?>;
const tf = (s, ...a) => { let i = 0; return s.replace(/%(\d)\$[sd]|%[sd]/g, (m, n) => String(n ? a[n - 1] : a[i++])); };
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
  return new Intl.NumberFormat(CO.locale, {style: 'currency', currency: CO.cur, minimumFractionDigits: CO.dec, maximumFractionDigits: CO.dec}).format(usd * CO.rate);
}
function fmtShip(usd){ return usd > 0 ? fmt(usd) : T.free; }
/* shipping, total and the minimum-order check follow the selected country; the server re-checks all of it */
function updateTotals(country){
  if(!window.CO) return;
  const rates = CO.ship[country], warn = document.getElementById('minWarn'), btn = document.getElementById('placeBtn');
  const cost = document.getElementById('shipCost');
  const picked = (document.querySelector('input[name=ship_method]:checked') || {}).value || 'standard';
  document.querySelectorAll('[data-ship-price]').forEach(el => { el.textContent = rates ? fmtShip(rates[el.dataset.shipPrice]) : ''; });
  document.getElementById('shipLabel').textContent = T.shipping + ' · ' + (CO.labels[picked] || '');
  if(rates === undefined){ cost.textContent = T.select; document.getElementById('grandTotal').textContent = fmt(CO.goods); warn.hidden = true; btn.disabled = false; return; }
  const ship = rates[picked];
  const total = Math.round((CO.goods + ship) * 100) / 100;
  cost.textContent = fmtShip(ship); cost.style.color = '';
  document.getElementById('grandTotal').textContent = fmt(total);
  const short = total < CO.min;
  warn.hidden = !short; btn.disabled = short;
  if(short) warn.textContent = tf(T.min, fmt(CO.min), fmt(total), fmt(CO.min - total));
}
/* price checker: show rows that contain every word typed */
function pcFilter(v){
  const words = v.toLowerCase().split(/\s+/).filter(Boolean); let n = 0;
  document.querySelectorAll('#pcTable tbody tr').forEach(r => { const ok = words.every(w => r.dataset.q.includes(w)); r.hidden = !ok; if(ok) n++; });
  document.getElementById('pcCount').textContent = tf(n === 1 ? T.one : T.many, n);
}
function galPick(btn, src){
  const m = document.getElementById('galMain');
  m.removeAttribute('srcset'); m.src = src;     /* drop the responsive list, or the browser keeps showing the first photo */
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
document.querySelectorAll('input[name=ship_method]').forEach(r => r.addEventListener('change', ()=>updateTotals(c ? c.value : '')));
/* the button says what happens next when Bitcoin is chosen */
document.querySelectorAll('input[name=payment]').forEach(r => r.addEventListener('change', () => {
  const b = document.getElementById('placeBtn'); if(!b || !window.CO) return;
  b.textContent = CO.btc.includes(r.value) ? b.dataset.btcLabel : b.dataset.label;
}));

/* Bitcoin order page: QR code, copy buttons, the countdown on the quoted amount, and a quiet check for the payment */
(function(){
  const box = document.getElementById('payBox'); if(!box) return;
  document.querySelectorAll('.copy').forEach(b => b.addEventListener('click', () => {
    const v = b.dataset.copy, done = () => { b.textContent = T.copied; setTimeout(() => b.textContent = T.copy, 1800); };
    if(navigator.clipboard && window.isSecureContext) navigator.clipboard.writeText(v).then(done, () => prompt(T.copy, v)); else prompt(T.copy, v);
  }));
  const qrEl = document.getElementById('qr');
  const draw = () => {
    if(!qrEl || !window.qrcode) return;
    const qr = qrcode(0, 'M'); qr.addData(qrEl.dataset.uri); qr.make();
    const n = qr.getModuleCount(), q = 3, w = n + q * 2; let d = '';
    for(let r = 0; r < n; r++) for(let c = 0; c < n; c++) if(qr.isDark(r, c)) d += 'M' + (c + q) + ' ' + (r + q) + 'h1v1h-1z';
    qrEl.innerHTML = '<svg viewBox="0 0 ' + w + ' ' + w + '" role="img" aria-label="' + T.qr + '" shape-rendering="crispEdges">'
      + '<rect width="' + w + '" height="' + w + '" fill="#fff"/><path d="' + d + '" fill="#000"/></svg>';
  };
  const lib = document.querySelector('script[src*="qrcode.min.js"]');
  if(qrEl && lib){ if(window.qrcode) draw(); else lib.addEventListener('load', draw); }

  const cd = document.getElementById('countdown');
  if(cd){
    const t0 = Date.now(), end = t0 + (+cd.dataset.expires - <?= time() ?>) * 1000;   /* measured from the server's clock */
    const tick = () => {
      const left = Math.max(0, Math.round((end - Date.now()) / 1000));
      cd.textContent = String(Math.floor(left / 60)).padStart(2, '0') + ':' + String(left % 60).padStart(2, '0');
      if(left > 0) setTimeout(tick, 1000);
      else if(end > t0) location.reload();          /* ran out while open: fetch the new amount */
      else cd.textContent = T.updating;
    };
    tick();
  }

  const started = Date.now(); let fails = 0;
  const poll = () => fetch(box.dataset.status, {cache: 'no-store'}).then(r => r.json()).then(d => {
    if(d.state !== box.dataset.state || String(d.quoted) !== box.dataset.quoted){ location.reload(); return; }
    if(['confirmed','short'].includes(d.state) || d.status === 'cancelled') return;
    if(Date.now() - started < 3 * 3600e3) setTimeout(poll, d.state === 'seen' ? 30000 : 15000);
  }).catch(() => { if(++fails < 20) setTimeout(poll, 30000); });
  setTimeout(poll, 1500);
})();

/* live chat: added once the page has finished loading, so it never slows the shop down */
(function(){
  const t = document.getElementById('chatCode'); if(!t) return;
  window.Tawk_API = window.Tawk_API || {};
  Tawk_API.onLoad = function(){ document.querySelectorAll('[data-open-chat]').forEach(b => { b.hidden = false; b.onclick = () => Tawk_API.maximize(); }); };
  const go = () => [...t.content.childNodes].forEach(n => {
    if(n.nodeName !== 'SCRIPT'){ if(n.nodeType === 1) document.body.appendChild(n.cloneNode(true)); return; }
    const s = document.createElement('script');
    [...n.attributes].forEach(a => s.setAttribute(a.name, a.value));
    s.text = n.textContent; document.body.appendChild(s);
  });
  const later = () => 'requestIdleCallback' in window ? requestIdleCallback(go, {timeout: 2500}) : setTimeout(go, 1200);
  if(document.readyState === 'complete') later(); else addEventListener('load', later);
})();
</script>
</body>
</html>

<?php
/* ---------------- VIEW PARTIALS ---------------- */
function include_card($p){
  global $CATEGORIES, $CONFIG, $POST_URL;
  $ph   = photos($p['id']);
  $base = unit_price($p, $p['moq']);
  $best = end($p['ladder']);
  $flag = $p['status']==='preorder' ? ['pre', t('PREORDER')]
        : ($p['status']==='soldout' ? ['out', t('SOLD OUT')]
        : ($p['status']==='new' ? ['new', t('NEW')]
        : ($p['status']==='low' ? ['low', t('LOW STOCK')] : ['', t('IN STOCK')])));
  ?>
  <article class="card">
    <a class="art" href="<?= url('product',['id'=>$p['id']]) ?>" style="display:block">
      <span class="flag <?= $flag[0] ?>"><?= h($flag[1]) ?></span>
      <?php if($ph): ?>
        <?= img_tag($ph[0], $p['name']) ?>
      <?php else: ?>
        <div class="ph"><span><?= h($CONFIG['kanji']) ?></span><span><?= h($p['set']) ?></span></div>
      <?php endif; ?>
    </a>
    <div class="in">
      <h3><a href="<?= url('product',['id'=>$p['id']]) ?>"><?= h($p['name']) ?></a></h3>
      <div class="meta"><?= h(implode(' · ', array_filter([$p['set'], ($p['cond'] ?? 'Sealed') !== 'Sealed' ? cond_word($p['cond']) : '', $p['status']==='preorder' ? $p['release'] : '']))) ?></div>
      <div class="px"><span class="u"><?= money($base) ?></span><span class="per"><?= h(t('/unit from %d', $p['moq'])) ?></span><?php if($p['ladder'][0][1] > $base): ?><span class="w"><?= money($p['ladder'][0][1]) ?></span><?php endif; ?></div>
      <?php if(count($p['ladder']) > 1 && $best[1] < $base): ?><div class="drop"><?= t('down to %1$s from %2$d', '<b>'.money($best[1]).'</b>', (int)$best[0]) ?></div><?php endif; ?>
      <div class="meta"><?= h(t('Min. %d', $p['moq'])) ?><?= $p['step'] > 1 ? ' · '.h(t('in %ds', $p['step'])) : '' ?></div>
    </div>
    <form method="post" action="<?= h($POST_URL) ?>">
      <input type="hidden" name="action" value="add">
      <input type="hidden" name="id" value="<?= h($p['id']) ?>">
      <?php if(can_order($p)): stepper($p, 'qty', $p['moq'], $p['moq']); ?>
      <button class="btn" type="submit"><?= h(t('Add')) ?></button>
      <?php else: ?>
      <button class="btn" type="submit" disabled><?= h(t('Sold out')) ?></button>
      <?php endif; ?>
    </form>
  </article>
  <?php
}

/* Shipping & Returns: delivery options, rates by destination and worked examples (the {rates} line in the page text) */
function ship_rates_block(){
  global $STORE, $CONFIG, $COUNTRIES, $HOME_CC;
  $SM = ship_methods($STORE); $fs = free_ship_usd($STORE);
  $cell = fn($r)=>money($r['base']).($r['per_kg'] > 0 ? ' <small>+ '.money($r['per_kg']).'/kg</small>' : ''); ?>
  <div class="tblwrap"><table class="tbl">
    <thead><tr><th><?= h(t('Option')) ?></th><th><?= h(t('Delivery time')) ?></th><th><?= h(t('Tracking')) ?></th></tr></thead>
    <tbody>
      <?php foreach($SM as $mm): ?><tr><td class="nm"><?= h($mm['label']) ?></td><td><?= h(t('%s after dispatch', days_text($mm['days']))) ?></td><td><?= h(t('Door to door')) ?></td></tr><?php endforeach; ?>
    </tbody>
  </table></div>
  <p><?= h(t('We ship with Japan Post EMS, DHL Express and FedEx, choosing the best carrier for your parcel’s weight and delivery option. Shipping is priced by the weight of your order: a price per order plus a price per kilogram.')) ?><?php if($fs): ?> <?= h(t('Orders over %1$s ship free with %2$s.', money_whole($fs), $SM['standard']['label'])) ?><?php endif; ?></p>
  <div class="tblwrap"><table class="tbl">
    <thead><tr><th><?= h(t('Destination')) ?></th><?php foreach($SM as $mm): ?><th class="r"><?= h($mm['label']) ?></th><?php endforeach; ?></tr></thead>
    <tbody>
      <?php $zoned = array_merge([], ...array_map(fn($z)=>$z['countries'], $STORE['shipping']['zones']));   /* "Rest of world" only while some country has no zone */
      foreach(array_merge($STORE['shipping']['zones'], array_diff(array_keys($COUNTRIES), $zoned) ? [['name'=>t('Rest of world')]+$STORE['shipping']['rest']] : []) as $z): $zr = zone_rates($z);
        $zname = count($z['countries'] ?? []) === 1 ? country_name($z['countries'][0], $z['name']) : $z['name']; ?>
        <tr><td class="nm"><?= h($zname) ?></td><?php foreach(array_keys($SM) as $mk): ?><td class="r"><?= $cell($zr[$mk]) ?></td><?php endforeach; ?></tr>
      <?php endforeach; ?>
    </tbody>
  </table></div>
  <?php $ex = [[t('A single card'), 0.05, 30], [t('6 booster boxes (about 2.4 kg)'), 2.4, 900], [t('6 Elite Trainer Boxes (about 5.4 kg)'), 5.4, 250]];
  if($fs) $ex[] = [t('36 booster boxes (about 14.4 kg), over %s', money_whole($fs)), 14.4, $fs]; ?>
  <div class="tblwrap"><table class="tbl ex">
    <thead><tr><th><?= h(t('Examples to %s', $COUNTRIES[$HOME_CC] ?? $HOME_CC)) ?></th><?php foreach($SM as $mm): ?><th class="r"><?= h($mm['label']) ?></th><?php endforeach; ?></tr></thead>
    <tbody>
      <?php foreach($ex as [$label, $kg, $goods]): ?>
        <tr><td><?= h($label) ?></td><?php foreach(array_keys($SM) as $mk): $v = shipping_usd($STORE, $HOME_CC, $kg, $mk, $goods); ?><td class="r"><?= $v > 0 ? money($v) : '<b class="free">'.h(t('Free')).'</b>' ?></td><?php endforeach; ?></tr>
      <?php endforeach; ?>
    </tbody>
  </table></div>
  <p class="small"><?= h(t('As a guide, a sealed Japanese booster box weighs about 0.4 kg packed and an Elite Trainer Box about 0.9 kg. Checkout shows the exact price for your order before you pay.')) ?></p>
<?php }

/* A guide's text, with tool blocks where a line says {price_list}, {set_table} or {card_template} */
function guide_body($text){
  $parts = preg_split('/^[ \t]*\{(price_list|set_table|card_template)\}[ \t]*$/m', str_replace("\r", '', (string)$text), -1, PREG_SPLIT_DELIM_CAPTURE);
  foreach($parts as $i=>$part){
    if($i % 2 === 0){ echo rich($part); continue; }
    ['price_list'=>'price_list_block', 'set_table'=>'set_table_block', 'card_template'=>'card_template_block'][$part]();
  }
}

/* price checker: every product with its price at the minimum quantity and its best quantity price, searchable */
function price_list_block(){
  global $PRODUCTS, $CATEGORIES; ?>
  <div class="pcheck"><label for="pcq"><?= h(t('Check a price')) ?></label>
    <input id="pcq" type="search" placeholder="<?= h(t('Try 151, Charizard, Elite Trainer Box, sleeves…')) ?>" oninput="pcFilter(this.value)" autocomplete="off">
    <span id="pcCount"><?= h(tn(count($PRODUCTS), '%d product', '%d products')) ?></span></div>
  <div class="tblwrap"><table class="tbl pc" id="pcTable">
    <thead><tr><th><?= h(t('Product')) ?></th><th class="r"><?= h(t('Price')) ?></th><th class="r"><?= h(t('Best price')) ?></th></tr></thead><tbody>
    <?php foreach($CATEGORIES as $ck=>$c): foreach($PRODUCTS as $p): if($p['cat'] !== $ck) continue;
      $top = end($p['ladder']); ?>
      <tr data-q="<?= h(lc($p['name'].' '.$p['set'].' '.$p['sku'].' '.$c['label'])) ?>">
        <td class="nm"><a href="<?= h(url('product', ['id'=>$p['id']])) ?>"><?= h($p['name']) ?></a>
          <div class="sk"><?= h(implode(' · ', array_filter([$c['label'], $p['set'], cond_word($p['cond']), status_word($p)]))) ?></div></td>
        <td class="r"><?= money(unit_price($p, $p['moq'])) ?><div class="sk"><?= h(t('each, from %d', $p['moq'])) ?></div></td>
        <td class="r"><?= money($top[1]) ?><div class="sk"><?= h(t('each from %d', max($p['moq'], $top[0]))) ?></div></td></tr>
    <?php endforeach; endforeach; ?>
    </tbody></table></div>
  <p class="small"><?= h(t('Live prices from our shop, in euros. Shipping is extra, and free on orders over %s.', money_whole(free_ship_usd($GLOBALS['STORE'])))) ?></p>
<?php }

/* card database: every Japanese set we carry, by series */
function set_table_block(){
  global $SERIES;
  foreach($SERIES as $k=>$sr): $sets = series_sets($k); if(!$sets) continue; ?>
  <div class="tblwrap"><table class="tbl">
    <thead><tr><th><?= h($sr['name']) ?></th><th><?= h(t('Set code')) ?></th><th class="r"><?= h(t('Products')) ?></th></tr></thead><tbody>
    <?php foreach($sets as $st): ?>
      <tr><td class="nm"><a href="<?= h(url('set', ['s'=>$st['slug']])) ?>"><?= h($st['name']) ?></a></td><td><?= h(($st['code'] ?? '') ?: '—') ?></td><td class="r"><?= count(set_products($st['name'])) ?></td></tr>
    <?php endforeach; ?>
    </tbody></table></div>
  <?php endforeach;
}

/* free printable template: exact card size, bleed and safe area */
function card_template_block(){ ?>
  <div class="tplbox">
    <img src="assets/site/trading-card-template-63x88mm.svg" width="276" height="376" alt="<?= h(t('Blank trading card template, 63 × 88 mm with 3 mm bleed and a safe area')) ?>" loading="lazy">
    <div>
      <b><?= h(t('Free printable card template')) ?></b>
      <p><?= h(t('63 × 88 mm, the size of a Pokémon card, with 3 mm bleed, the trim line, rounded corners and a safe area for text. Vector files: print at 100% (“actual size”), not “fit to page”.')) ?></p>
      <p><a class="btn" href="assets/site/trading-card-template-63x88mm.svg" download><?= h(t('Download one card (SVG)')) ?></a>
         <a class="btn g" href="assets/site/trading-card-template-sheet-a4.svg" download><?= h(t('Download a sheet of 9 (A4)')) ?></a></p>
    </div>
  </div>
<?php }

/* the "### question" / answer pairs under a "## …" heading that names questions, for Google's FAQ data */
function policy_questions($text){
  $out = []; $in = false; $q = null; $a = [];
  foreach(explode("\n", fill($text)) as $line){
    if(preg_match('/^##\s+(.+)$/', $line, $m)){ if($q && $a) $out[] = [$q, implode(' ', $a)]; $q = null; $a = []; $in = (bool)preg_match('/question|vragen|questions/i', $m[1]); continue; }
    if(!$in) continue;
    if(preg_match('/^###\s+(.+)$/', $line, $m)){ if($q && $a) $out[] = [$q, implode(' ', $a)]; $q = trim($m[1]); $a = []; continue; }
    if($q && trim($line) !== '') $a[] = trim(preg_replace(['/\[([^\]]+)\]\([^)]*\)/', '/\*\*/'], ['$1', ''], $line));
  }
  if($q && $a) $out[] = [$q, implode(' ', $a)];
  return $out;
}

/* "Add € X more for free shipping" / "Your order ships free" */
function free_ship_meter($goods, $compact=false){
  global $STORE;
  $t = free_ship_usd($STORE); if(!$t) return;
  $sm = ship_methods($STORE); $pct = min(100, round($goods / $t * 100)); ?>
  <div class="fsm<?= $compact ? ' c' : '' ?>">
    <?php if($goods >= $t): ?>
      <div class="t"><b><?= h(t('Your order ships free.')) ?></b> <?= h(t('Free %1$s shipping applied; %2$s costs only the difference.', $sm['standard']['label'], $sm['express']['label'])) ?></div>
    <?php else: ?>
      <div class="t"><?= t('Add %1$s more for free %2$s shipping (orders over %3$s).', '<b>'.money($t - $goods).'</b>', h($sm['standard']['label']), money_whole($t)) ?></div>
    <?php endif; ?>
    <div class="meter" role="progressbar" aria-valuemin="0" aria-valuemax="100" aria-valuenow="<?= (int)$pct ?>" aria-label="<?= h(t('Progress to free shipping')) ?>"><i style="width:<?= (int)$pct ?>%"></i></div>
  </div>
<?php }

/* quantity stepper; $min is the input floor (0 in the cart so a line can be cleared) */
function stepper($p, $name, $value, $min){ ?>
  <div class="step">
    <button type="button" onclick="bump(this,-<?= (int)$p['step'] ?>,<?= (int)$p['moq'] ?>)" aria-label="<?= h(t('Fewer')) ?>">−</button>
    <input type="number" name="<?= h($name) ?>" value="<?= (int)$value ?>" min="<?= (int)$min ?>" step="<?= (int)$p['step'] ?>" aria-label="<?= h(t('Quantity')) ?>">
    <button type="button" onclick="bump(this,<?= (int)$p['step'] ?>,<?= (int)$p['moq'] ?>)" aria-label="<?= h(t('More')) ?>">+</button>
  </div>
<?php }

function set_tiles($sets){ ?>
  <div class="tiles"><?php foreach($sets as $st): $n = count(set_products($st['name'])); ?>
    <a href="<?= h(url('set', ['s'=>$st['slug']])) ?>"><?php if($st['code'] !== ''): ?><span class="n"><?= h($st['code']) ?></span><?php endif; ?>
      <b><?= h($st['name']) ?></b><span class="n"><?= h(tn($n, '%d product', '%d products')) ?></span></a>
  <?php endforeach; ?></div>
<?php }

function guide_cards($guides){ ?>
  <div class="gcards"><?php foreach($guides as $g): ?>
    <a href="<?= h(url('guide', ['g'=>$g['slug']])) ?>"><h3><?= h(fill($g['title'])) ?></h3>
      <p><?= h(fill(($g['seo_desc'] ?? '') ?: plain($g['body'] ?? '', 140))) ?></p><span><?= h(t('Read the guide')) ?> →</span></a>
  <?php endforeach; ?></div>
<?php }

function steps_block($CONFIG){ ?>
  <div class="steps">
    <div><div class="n">01</div><h3><?= h(t('See every price')) ?></h3>
      <p><?= h(t('Every listing shows its full quantity-break ladder. No account, no approval, no quote to wait for.')) ?></p></div>
    <div><div class="n">02</div><h3><?= h(t('Place the order')) ?></h3>
      <p><?= h(t('Add to cart, enter your delivery address and pick a payment method. Your stock is reserved for %d hours.', (int)$CONFIG['hold_hours'])) ?></p></div>
    <div><div class="n">03</div><h3><?= h(t('Pay in crypto')) ?></h3>
      <p><?= h(t('Pay by Bitcoin straight away on your order page, or get our wallet address for ETH or USDT by email within %d hours.', (int)$CONFIG['reply_hours'])) ?></p></div>
    <div><div class="n">04</div><h3><?= h(t('Tracked from Japan')) ?></h3>
      <p><?= h(t('Once payment has arrived, we dispatch within %d hours by EMS, DHL or FedEx, with tracking to your door in Belgium.', (int)$CONFIG['hold_hours'])) ?></p></div>
  </div>
<?php }
