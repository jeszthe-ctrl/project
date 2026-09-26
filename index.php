<?php
/* =============================================================
   FUDAKURA — wholesale Japanese Pokémon TCG for Australia. PHP 7.4+.

   Day-to-day editing — products, prices, photos, shipping rates,
   payment methods, business details, FAQ, orders — is done in
   admin.php. You should not need to edit this file. See README.md.
   ============================================================= */

define('FK_ROOT', __DIR__);
require FK_ROOT.'/inc/store.php';
require FK_ROOT.'/inc/bitcoin.php';
if(!ini_get('zlib.output_compression') && extension_loaded('zlib')) ob_start('ob_gzhandler');
start_session();
error_reporting(E_ALL & ~E_NOTICE & ~E_WARNING);

/* ---------------- DATA (edited in admin.php) ---------------- */
$STORE      = store_load();
$CONFIG     = $STORE['settings'];
$CURRENCIES = $STORE['currencies'];
$CATEGORIES = $STORE['categories'];
$PRODUCTS   = array_values(array_filter($STORE['products'], fn($p)=>empty($p['hidden']) && isset($CATEGORIES[$p['cat']])));
$PAYMENTS   = array_filter($STORE['payments'], fn($m)=>!empty($m['enabled']) && (!btc_method($m) || btc_ready()));   /* Bitcoin only with a valid wallet address */
$COUNTRIES  = $STORE['countries'];
$HOME_CC    = (string)array_key_first($COUNTRIES);   /* the first country in Settings: shipping examples and Google's shipping data */
$MIN_ORDER  = (float)$CONFIG['min_order_usd'];
$SERIES     = $STORE['series'] ?? [];
$COLLECTIONS= $STORE['collections'] ?? [];
$GUIDES     = $STORE['guides'] ?? [];
$INFO_PAGES = $STORE['pages'] ?? [];

/* Settings → "This site has moved to": every visitor (and Google) goes to the same page on the new domain */
if(($moved = rtrim(trim((string)($CONFIG['moved_to'] ?? '')), '/')) !== '' && preg_match('#^https?://[^/]+$#i', $moved)
   && strcasecmp((string)parse_url($moved, PHP_URL_HOST), preg_replace('/:\d+$/', '', (string)($_SERVER['HTTP_HOST'] ?? ''))) !== 0){
  header('Location: '.$moved.($_SERVER['REQUEST_URI'] ?? '/'), true, 301); exit;
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
  $c = cur_code(); $m = $CURRENCIES[$c];
  return $m['sym'] . number_format($usd * $m['rate'], $m['dec']);
}

/* whole units, for round numbers like the free-shipping threshold */
function money_whole($usd){
  global $CURRENCIES;
  $m = $CURRENCIES[cur_code()];
  return $m['sym'] . number_format(round($usd * $m['rate']), 0);
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
/* site photos (assets/site): the home page image until you upload your own in Settings, and the link-preview image */
const SITE_HERO  = 'assets/site/pokemon-30th-celebration-elite-trainer-box.webp';
const SITE_SHARE = 'assets/site/share-30th-celebration.jpg';
$BASE = rtrim(str_replace('\\', '/', dirname($_SERVER['SCRIPT_NAME'] ?? '/index.php')), '/').'/';
const PAGE_PATHS = ['cart'=>'cart', 'checkout'=>'checkout', 'received'=>'order-received', 'how'=>'how-it-works',
                    'shipping'=>'shipping-returns', 'payment'=>'payment-methods', 'faq'=>'faq', 'contact'=>'contact',
                    'sets'=>'sets', 'guides'=>'guides', 'sitemap'=>'sitemap.xml', 'pay'=>'pay', 'paystatus'=>'pay-status'];
/* older addresses that now live on another page: path => [page, #section] */
const MOVED_PATHS = ['shipping'=>['shipping', ''], 'returns'=>['shipping', 'returns']];

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
    case 'page':       $path = rawurlencode($pull('pg')); break;
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
    if(info_page($path)) return ['p'=>'page', 'pg'=>$path];
    if(isset(MOVED_PATHS[$path])) return ['p'=>MOVED_PATHS[$path][0], 'moved'=>MOVED_PATHS[$path][1]];
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
function info_page($slug){ global $INFO_PAGES; foreach($INFO_PAGES as $pg){ if(($pg['slug'] ?? '') === $slug) return $pg; } return null; }
function guide_by_slug($slug){ global $GUIDES; foreach($GUIDES as $g){ if(($g['slug'] ?? '') === $slug) return $g; } return null; }

/* ---------------- admin-written text ----------------
   blank line = paragraph, "## " / "### " heading, "- " bullet, **bold**, [text](link) — see inc/content.php */
function link_target($t){
  if(preg_match('#^(https?://|mailto:)#i', $t)) return $t;
  if(!preg_match('/^([a-z]+):([^#]+)(#[\w-]+)?$/', $t, $m)) return null;
  $to = link_page($m[1], $m[2]);
  return $to === null ? null : $to.($m[3] ?? '');
}
function link_page($kind, $slug){
  $m = [0, $kind, $slug];
  $pages = ['home'=>'home', 'shop'=>'catalog', 'sets'=>'sets', 'guides'=>'guides', 'faq'=>'faq', 'shipping'=>'shipping',
            'payment'=>'payment', 'how'=>'how', 'contact'=>'contact'];
  switch($m[1]){
    case 'product':  return url('product', ['id'=>$m[2]]);
    case 'category': return url('catalog', ['cat'=>$m[2]]);
    case 'set':      return url(series_by_slug($m[2]) ? 'series' : 'set', ['s'=>$m[2]]);
    case 'cards':    return url('collection', ['c'=>$m[2]]);
    case 'guide':    return url('guide', ['g'=>$m[2]]);
    case 'page':     return isset($pages[$m[2]]) ? url($pages[$m[2]]) : (info_page($m[2]) ? url('page', ['pg'=>$m[2]])
                            : ($m[2] === 'returns' ? url('shipping').'#returns' : null));
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
/* guides that suit each product type, if they exist */
/* ---------------- internal links ----------------
   The short link text for each guide (the keyword it targets), the guides that go together, and the
   guides each kind of page links to. Guides added in the admin simply use their title. */
const GUIDE_ANCHORS = [
  'japanese-pokemon-cards'=>'Japanese Pokémon cards explained', 'most-expensive-pokemon-cards'=>'Most expensive Pokémon cards',
  'rarest-pokemon-cards'=>'Rarest Pokémon cards', 'pokemon-card-price-checker'=>'Pokémon card price checker',
  'pokemon-card-database'=>'Pokémon card database & card lists', 'where-to-buy-pokemon-cards'=>'Where to buy Pokémon cards',
  'pokemon-card-shops-near-me'=>'Pokémon card shops near me', 'pokemon-card-scanner'=>'Pokémon card scanner apps',
  'pokemon-card-template'=>'Free Pokémon card template', 'how-to-play-pokemon-cards'=>'How to play Pokémon cards',
  'how-to-read-a-pokemon-card'=>'How to read a Pokémon card', 'how-much-does-it-cost-to-grade-a-pokemon-card'=>'How much it costs to grade a Pokémon card',
  'chinese-pokemon-cards'=>'Chinese Pokémon cards', 'coolest-pokemon-cards'=>'Cool Pokémon cards',
  'mew-mewtwo-arceus-pokemon-cards'=>'Mew, Mewtwo & Arceus cards', 'where-to-sell-pokemon-cards'=>'Where to sell Pokémon cards',
  'pokemon-card-size'=>'Pokémon card size', 'how-to-tell-if-a-pokemon-card-is-fake'=>'How to tell if a Pokémon card is fake',
  'pokemon-card-rarities'=>'Pokémon card rarities', 'pokemon-card-values'=>'Pokémon card values',
];
const GUIDE_RELATED = [
  'japanese-pokemon-cards'=>['where-to-buy-pokemon-cards','pokemon-card-database','chinese-pokemon-cards','how-to-read-a-pokemon-card'],
  'most-expensive-pokemon-cards'=>['rarest-pokemon-cards','pokemon-card-values','pokemon-card-price-checker','how-much-does-it-cost-to-grade-a-pokemon-card'],
  'rarest-pokemon-cards'=>['most-expensive-pokemon-cards','pokemon-card-rarities','coolest-pokemon-cards','mew-mewtwo-arceus-pokemon-cards'],
  'pokemon-card-price-checker'=>['pokemon-card-values','pokemon-card-scanner','where-to-sell-pokemon-cards','how-much-does-it-cost-to-grade-a-pokemon-card'],
  'pokemon-card-database'=>['pokemon-card-rarities','pokemon-card-price-checker','japanese-pokemon-cards','how-to-read-a-pokemon-card'],
  'where-to-buy-pokemon-cards'=>['pokemon-card-shops-near-me','japanese-pokemon-cards','pokemon-card-price-checker','how-to-tell-if-a-pokemon-card-is-fake'],
  'pokemon-card-shops-near-me'=>['where-to-buy-pokemon-cards','where-to-sell-pokemon-cards','pokemon-card-scanner','how-to-play-pokemon-cards'],
  'pokemon-card-scanner'=>['pokemon-card-price-checker','pokemon-card-values','how-to-tell-if-a-pokemon-card-is-fake','where-to-sell-pokemon-cards'],
  'pokemon-card-template'=>['pokemon-card-size','how-to-read-a-pokemon-card','how-to-tell-if-a-pokemon-card-is-fake','how-to-play-pokemon-cards'],
  'how-to-play-pokemon-cards'=>['how-to-read-a-pokemon-card','pokemon-card-shops-near-me','coolest-pokemon-cards','pokemon-card-database'],
  'how-to-read-a-pokemon-card'=>['how-to-play-pokemon-cards','pokemon-card-rarities','pokemon-card-template','japanese-pokemon-cards'],
  'how-much-does-it-cost-to-grade-a-pokemon-card'=>['pokemon-card-values','most-expensive-pokemon-cards','how-to-tell-if-a-pokemon-card-is-fake','where-to-sell-pokemon-cards'],
  'chinese-pokemon-cards'=>['how-to-tell-if-a-pokemon-card-is-fake','japanese-pokemon-cards','where-to-buy-pokemon-cards','pokemon-card-database'],
  'coolest-pokemon-cards'=>['rarest-pokemon-cards','mew-mewtwo-arceus-pokemon-cards','pokemon-card-rarities','most-expensive-pokemon-cards'],
  'mew-mewtwo-arceus-pokemon-cards'=>['coolest-pokemon-cards','rarest-pokemon-cards','most-expensive-pokemon-cards','pokemon-card-values'],
  'where-to-sell-pokemon-cards'=>['pokemon-card-price-checker','pokemon-card-values','how-much-does-it-cost-to-grade-a-pokemon-card','pokemon-card-shops-near-me'],
  'pokemon-card-size'=>['pokemon-card-template','how-to-read-a-pokemon-card','how-to-play-pokemon-cards','japanese-pokemon-cards'],
  'how-to-tell-if-a-pokemon-card-is-fake'=>['chinese-pokemon-cards','how-much-does-it-cost-to-grade-a-pokemon-card','where-to-buy-pokemon-cards','pokemon-card-values'],
  'pokemon-card-rarities'=>['rarest-pokemon-cards','coolest-pokemon-cards','how-to-read-a-pokemon-card','pokemon-card-database'],
  'pokemon-card-values'=>['pokemon-card-price-checker','most-expensive-pokemon-cards','how-much-does-it-cost-to-grade-a-pokemon-card','where-to-sell-pokemon-cards'],
];
const PAGE_GUIDES = [
  'cat:boxes'=>['japanese-pokemon-cards','pokemon-card-database','pokemon-card-price-checker','where-to-buy-pokemon-cards','chinese-pokemon-cards'],
  'cat:etb'=>['how-to-play-pokemon-cards','where-to-buy-pokemon-cards','japanese-pokemon-cards','pokemon-card-database','pokemon-card-shops-near-me'],
  'cat:premium'=>['how-to-play-pokemon-cards','how-to-read-a-pokemon-card','coolest-pokemon-cards','japanese-pokemon-cards','mew-mewtwo-arceus-pokemon-cards'],
  'cat:singles'=>['pokemon-card-values','most-expensive-pokemon-cards','rarest-pokemon-cards','how-much-does-it-cost-to-grade-a-pokemon-card','how-to-tell-if-a-pokemon-card-is-fake'],
  'cat:accessories'=>['pokemon-card-size','pokemon-card-template','how-to-play-pokemon-cards','where-to-sell-pokemon-cards','pokemon-card-scanner'],
  'shop'=>['where-to-buy-pokemon-cards','pokemon-card-price-checker','pokemon-card-database','japanese-pokemon-cards','pokemon-card-shops-near-me','chinese-pokemon-cards'],
  'set'=>['pokemon-card-database','pokemon-card-price-checker','pokemon-card-rarities','japanese-pokemon-cards'],
  'set:151'=>['most-expensive-pokemon-cards','pokemon-card-database','mew-mewtwo-arceus-pokemon-cards','pokemon-card-price-checker'],
  'set:30th-celebration'=>['mew-mewtwo-arceus-pokemon-cards','pokemon-card-database','coolest-pokemon-cards','pokemon-card-price-checker'],
  'series'=>['pokemon-card-database','how-to-play-pokemon-cards','pokemon-card-rarities','pokemon-card-price-checker'],
  'coll'=>['pokemon-card-values','rarest-pokemon-cards','pokemon-card-price-checker'],
  'coll:charizard-pokemon-cards'=>['most-expensive-pokemon-cards','pokemon-card-values','how-much-does-it-cost-to-grade-a-pokemon-card','pokemon-card-price-checker'],
  'coll:pikachu-pokemon-cards'=>['most-expensive-pokemon-cards','coolest-pokemon-cards','rarest-pokemon-cards','pokemon-card-values'],
  'coll:gengar-pokemon-cards'=>['coolest-pokemon-cards','pokemon-card-rarities','pokemon-card-price-checker','pokemon-card-values'],
  'coll:psa-graded-pokemon-cards'=>['how-much-does-it-cost-to-grade-a-pokemon-card','pokemon-card-values','how-to-tell-if-a-pokemon-card-is-fake','where-to-sell-pokemon-cards'],
  'faq'=>['how-to-play-pokemon-cards','where-to-buy-pokemon-cards','how-much-does-it-cost-to-grade-a-pokemon-card','how-to-tell-if-a-pokemon-card-is-fake','pokemon-card-price-checker','japanese-pokemon-cards','pokemon-card-shops-near-me','chinese-pokemon-cards'],
  'home'=>['most-expensive-pokemon-cards','pokemon-card-price-checker','where-to-buy-pokemon-cards','pokemon-card-database','how-to-play-pokemon-cards','rarest-pokemon-cards'],
];
/* where each group of guides sends readers to shop */
const GUIDE_SHOP = [
  'collect'=>'Shop [rare Pokémon cards](category:singles), [PSA graded Pokémon cards](cards:psa-graded-pokemon-cards) and [Charizard Pokémon cards](cards:charizard-pokemon-cards), shipped from Japan to Australia.',
  'play'=>'Shop [Elite Trainer Boxes](category:etb), [starter decks and premium sets](category:premium) and [card sleeves, binders and playmats](category:accessories), shipped from Japan.',
  'buy'=>'Shop [Japanese booster boxes](category:boxes), [Elite Trainer Boxes](category:etb) and [rare single cards](category:singles), shipped from Japan to Australia.',
];
const GUIDE_GROUP = ['most-expensive-pokemon-cards'=>'collect','rarest-pokemon-cards'=>'collect','pokemon-card-values'=>'collect','pokemon-card-price-checker'=>'collect',
  'how-much-does-it-cost-to-grade-a-pokemon-card'=>'collect','where-to-sell-pokemon-cards'=>'collect','pokemon-card-scanner'=>'collect','coolest-pokemon-cards'=>'collect',
  'mew-mewtwo-arceus-pokemon-cards'=>'collect','pokemon-card-rarities'=>'collect','how-to-play-pokemon-cards'=>'play','how-to-read-a-pokemon-card'=>'play',
  'pokemon-card-template'=>'play','pokemon-card-size'=>'play'];

function guide_anchor($g){ return GUIDE_ANCHORS[$g['slug']] ?? fill($g['title']); }
function guides_list($slugs){ return array_values(array_filter(array_map('guide_by_slug', $slugs))); }
function guides_for($cat){ return guides_list(PAGE_GUIDES['cat:'.$cat] ?? PAGE_GUIDES['shop']); }
/* related guides: the hand-picked ones first, topped up with the next guides in the list */
function guides_related($slug, $n=4){
  global $GUIDES;
  $out = guides_list(GUIDE_RELATED[$slug] ?? []);
  $at = (int)array_search($slug, array_column($GUIDES, 'slug'), true);
  for($i = 1; $i < count($GUIDES) && count($out) < $n; $i++){
    $g = $GUIDES[($at + $i) % count($GUIDES)];
    if($g['slug'] !== $slug && !in_array($g['slug'], array_column($out, 'slug'), true)) $out[] = $g;
  }
  return array_slice($out, 0, $n);
}
/* a row of guide links, e.g. at the foot of a category or set page */
function guide_links($slugs, $title='Helpful guides', $extra=[]){
  $gs = guides_list($slugs); if(!$gs && !$extra) return; ?>
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
   {free_ship} {standard} {express} {standard_days} {express_days} */
function fill($text){
  global $CONFIG, $COUNTRIES, $MIN_ORDER, $STORE;
  $sm = ship_methods($STORE);
  return strtr($text, ['{reply_hours}'=>(int)$CONFIG['reply_hours'], '{hold_hours}'=>(int)$CONFIG['hold_hours'],
                       '{countries}'=>count($COUNTRIES), '{min_order}'=>money($MIN_ORDER), '{brand}'=>$CONFIG['brand'],
                       '{company}'=>$CONFIG['legal_name'], '{address}'=>$CONFIG['address'], '{email}'=>$CONFIG['email'], '{kanji}'=>$CONFIG['kanji'],
                       '{free_ship}'=>money_whole(free_ship_usd($STORE)),
                       '{standard}'=>$sm['standard']['label'], '{express}'=>$sm['express']['label'],
                       '{standard_days}'=>$sm['standard']['days'], '{express_days}'=>$sm['express']['days']]);
}

/* returns which emails went out, so a failure shows up in the admin instead of vanishing */
function send_order_mail($order){
  global $CONFIG;
  $body  = "NEW ORDER  {$order['ref']}\n";
  $body .= str_repeat('=',50)."\n\n";
  $body .= "PAYMENT METHOD CHOSEN: {$order['payment_label']}\n";
  if(!empty($order['btc'])){
    $b = $order['btc'];
    $body .= !empty($b['sats'])
      ? "-> Paid by Bitcoin on the site: ".btc_amount($b['sats'])." BTC to {$b['address']}\n   (1 BTC = \${$b['rate']} from {$b['rate_source']}). You'll get a receipt email when the payment\n   is seen on the blockchain, and another when it confirms. No need to send payment details.\n\n"
      : "-> Paid by Bitcoin on the site to {$b['address']}. The BTC price feeds didn't answer, so the customer's\n   order page will show the amount once they do. You'll get a receipt email when the payment is seen.\n\n";
  } else $body .= "-> Send payment details to this customer manually.\n\n";
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
  $body .= "  SHIPPING: {$order['shipping']} ({$order['ship_zone']}, {$order['ship_label']})\n";
  $body .= "  TOTAL:    {$order['total']} ({$order['currency']})\n";
  $body .= "  Import duty and taxes are not included.\n\n";
  if($order['notes']) $body .= "NOTES\n  {$order['notes']}\n\n";
  $body .= "Submitted: {$order['time']}\n";

  $to_shop = shop_mail($CONFIG['order_email'], "New order {$order['ref']} — {$order['payment_label']}", $body, $order['email']);

  /* customer confirmation */
  $c  = "Thank you — we have your order.\n\n";
  $c .= "Order reference: {$order['ref']}\n";
  $c .= "Goods: {$order['goods']}\n";
  $c .= "Shipping: {$order['shipping']} — {$order['ship_label']}\n";
  $c .= "Order total: {$order['total']} ({$order['currency']})\n";
  $c .= "Payment method selected: {$order['payment_label']}\n\n";
  if(!empty($order['btc'])){
    $b = $order['btc'];
    $c .= "PAY WITH BITCOIN\n";
    if(!empty($b['sats'])){
      $c .= "Send exactly:  ".btc_amount($b['sats'])." BTC\n";
      $c .= "To address:    {$b['address']}\n";
      $c .= "This amount is held until ".gmdate('H:i', $b['expires'])." UTC. After that, your order page\n";
      $c .= "shows a new amount at the current rate.\n\n";
    } else $c .= "Your order page shows the exact BTC amount and a QR code.\n\n";
    $c .= "Your order page (QR code, amount and payment status):\n".btc_pay_link($order)."\n\n";
    $c .= "We email your receipt as soon as your payment reaches the blockchain,\n";
    $c .= "and dispatch within {$CONFIG['hold_hours']} hours of it confirming, from Japan with tracking.\n\n";
  } else {
    $c .= "WHAT HAPPENS NEXT\n";
    $c .= "Your stock is reserved for {$CONFIG['hold_hours']} hours. We will contact you\n";
    $c .= "by email or text within {$CONFIG['reply_hours']} hours with the payment details\n";
    $c .= "for the method you selected, together with your invoice.\n\n";
    $c .= "Quote {$order['ref']} on your payment so we can match it to your order.\n";
    $c .= "Once payment clears we dispatch within {$CONFIG['hold_hours']} hours from Japan\n";
    $c .= "with tracking.\n\n";
  }
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
    if(empty($_POST['agree']))                           $errors[] = 'Please accept the terms of sale and the shipping & returns policy.';

    /* delivery option: Standard or Express */
    $SHIPM = ship_methods($STORE);
    $method = in_array($_POST['ship_method'] ?? '', SHIP_METHODS, true) ? $_POST['ship_method'] : 'standard';
    $f['ship_method'] = $method;

    /* minimum order value counts goods plus shipping */
    if(isset($COUNTRIES[$f['country']]) && cart_lines()){
      $ship  = shipping_usd($STORE, $f['country'], cart_weight(), $method, cart_total());
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
        'ship_label'    => $SHIPM[$method]['label'].' ('.$SHIPM[$method]['days'].')',
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
    if(!empty($o['btc']['txid'])) flash('We already have your payment for this order — thank you.');
    elseif(($o['btc']['tries'] ?? 0) >= 8) flash('Too many attempts. Please email '.$CONFIG['email'].' with your order reference and transaction ID.');
    elseif(!preg_match('/^[0-9a-f]{64}$/', $txid)) flash('That doesn’t look like a transaction ID. It’s 64 letters and numbers, shown in your wallet’s payment details.');
    else {
      $o['btc']['tries'] = ($o['btc']['tries'] ?? 0) + 1;
      $claims = btc_claims();
      $r = isset($claims[$txid]) ? false : btc_lookup_tx($txid, $o['btc']['address']);
      if($r === false) flash('That transaction has already been matched to an order. If you think that’s a mistake, email '.$CONFIG['email'].'.');
      elseif($r === null){      /* explorers didn't answer: keep it, and check it again on the next status check */
        $o['btc']['reported'] = $txid;
        shop_mail($CONFIG['order_email'], "Bitcoin payment reported — {$o['ref']}",
          "The customer says they've paid order {$o['ref']} and gave this transaction ID:\n$txid\n".btc_tx_url($txid)."\n\nThe block explorers couldn't be reached to check it. The order page will keep trying, or check it yourself in the admin.\n", $o['email']);
        flash('Thanks — we’ve noted your transaction and will confirm it as soon as the Bitcoin network check comes back.');
      }
      elseif(!$r['exists']) flash('We can’t find that transaction on the Bitcoin network yet. If you’ve only just sent it, wait a minute and try again.');
      elseif(!$r['found']) flash('That transaction doesn’t send Bitcoin to our address '.$o['btc']['address'].'. Check you copied the transaction for this payment.');
      else {
        $before = btc_state($o);
        btc_attach($o, $r['found'], $r['tip']);
        $after = btc_state($o);
        if($after === 'seen') btc_mail($o, 'received');
        elseif($after !== $before){ if($after === 'confirmed' && in_array($o['status'], ['new','invoiced'], true)) $o['status'] = 'paid'; btc_mail($o, $after); }
        flash('Payment found — thank you. Your receipt is on its way by email.');
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
/* addresses that moved: /shipping and /returns → /shipping-returns */
if(isset($from_path['moved']) || (($_GET['p'] ?? '') === 'page' && !$info && $gs('pg') === 'returns')){
  $u = url('shipping');
  header('Location: '.$BASE.$u.(($from_path['moved'] ?? 'returns') !== '' ? '#'.($from_path['moved'] ?? 'returns') : ''), true, 301); exit;
}

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

/* with clean addresses on, old index.php?p=… links (and /shop?cat=…) move permanently to the clean address */
if(!empty($CONFIG['pretty_urls']) && $_SERVER['REQUEST_METHOD'] === 'GET' && !in_array($page, ['notfound','sitemap','paystatus'], true)
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
  foreach($INFO_PAGES as $ip) $urls[] = [abs_url('page', ['pg'=>$ip['slug']]), []];
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
  'how'      => ['How Wholesale Ordering Works — Japanese Pokémon TCG', 'How wholesale ordering works'],
  'shipping' => ['Shipping & Returns — Japanese Pokémon Cards from Japan', 'Shipping & Returns'],
  'pay'      => ['Pay for your order', ''],
  'payment'  => ['Payment methods', 'Payment methods'],
  'faq'      => ['FAQ — Buying Japanese Pokémon cards', 'Frequently asked questions'],
  'contact'  => ['Contact', 'Contact'],
  'notfound' => ['Page not found', 'Page not found'],
];
$page_desc = '';
switch($page){
  case 'home':
    $page_title = ($CONFIG['home_seo_title'] ?? '') ?: 'Japanese Pokémon Cards — Booster Boxes & Singles';
    $page_desc  = ($CONFIG['home_seo_desc'] ?? '') ?: 'Authentic Japanese Pokémon cards shipped from Japan to Australia: sealed booster boxes, ETBs, rare singles and PSA graded cards, with bulk pricing published.';
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
      $page_desc  = 'Shop Japanese Pokémon cards: sealed booster boxes, Elite Trainer Boxes, rare singles, PSA graded cards and accessories, shipped from Japan to Australia.';
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
    $from = unit_price($prod, $prod['moq']);
    $page_desc  = ($prod['seo_desc'] ?? '') ?: plain(desc_parts($prod['desc'])[0], 105).' From $'.number_format($from, 2).' each; ships from Japan to Australia.';
    break;
  case 'sets':
    $crumbs[] = ['Sets', '', []];
    $page_title = 'Pokémon Card Sets — Mega Evolution & Scarlet & Violet';
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
    $page_desc  = $set['seo_desc'] ?: (plain($set['intro']) ?: 'Japanese '.$set['name'].' booster boxes and cards, shipped from Japan to Australia.');
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
    $page_title = 'Pokémon Card Guides: Values, Rare Cards, How to Play';
    $page_desc  = 'Plain-English Pokémon card guides: values and prices, the most expensive and rarest cards, how to play and read cards, grading, fakes and where to buy.';
    $h1 = 'Pokémon card guides';
    break;
  case 'page':
    $crumbs[] = [$info['title'], '', []];
    $page_title = ($info['seo_title'] ?? '') ?: $info['title'];
    $page_desc  = ($info['seo_desc'] ?? '') ?: plain($info['body'] ?? '');
    $h1 = $info['title'];
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
    $page_desc = ['shipping'=>(free_ship_usd($STORE) ? 'Free shipping over '.money_whole(free_ship_usd($STORE)).'. ' : '')
                    .'Japanese Pokémon cards shipped from Japan with tracking: delivery times, rates, duty, returns and refunds.',
                  'faq'=>'Answers to common questions about buying Japanese Pokémon cards wholesale from Japan: shipping to Australia, payment, minimum order, GST and returns.',
                  'how'=>'How wholesale ordering works at {brand}: public MOQs and quantity-break prices, pay by Bitcoin or invoice, and tracked shipping from Japan to Australia.',
                  'payment'=>'How to pay for Japanese Pokémon cards at {brand}: Bitcoin straight from your wallet, or the method that suits you, with an invoice by email.',
                  'contact'=>'Contact {brand} about wholesale Japanese Pokémon card orders, case pricing, shipping from Japan or an existing order. We reply within '.(int)$CONFIG['reply_hours'].' hours.'][$page] ?? '';
}
if($page_desc === '') $page_desc = 'Wholesale Japanese Pokémon cards shipped from Japan to Australia: sealed booster boxes, Elite Trainer Boxes, premium sets, singles and TCG accessories.';
/* admin-written titles can use {brand} and the other placeholders, like the page text */
$page_title = fill($page_title); $page_desc = fill($page_desc); $h1 = fill($h1);
foreach($crumbs as $ci=>$cr) $crumbs[$ci][0] = fill($cr[0]);

/* one canonical URL per page, without filters, currency or search */
$canon_args = ['product'=>['id'=>$prod['id'] ?? ''], 'set'=>['s'=>$set['slug'] ?? ''], 'series'=>['s'=>$series['slug'] ?? ''],
               'collection'=>['c'=>$coll['slug'] ?? ''], 'guide'=>['g'=>$guide['slug'] ?? ''], 'page'=>['pg'=>$info['slug'] ?? ''],
               'catalog'=>$cat ? ['cat'=>$cat] : []];
$canonical = in_array($page, ['notfound','pay'], true) ? '' : abs_url($page, $canon_args[$page] ?? []);
$noindex = in_array($page, ['cart','checkout','received','pay','notfound'], true) || ($page === 'catalog' && $filtered);

$in_stock = array_values(array_filter($PRODUCTS, fn($p)=>!in_array($p['status'], ['preorder','soldout'], true)));
?>
<!DOCTYPE html>
<html lang="en-AU">
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
  $og = ($prod ? photos($prod['id']) : []) ?: (photos('hero') ?: (is_file(FK_ROOT.'/'.SITE_SHARE) ? [SITE_SHARE] : [])); ?>
<meta property="og:type" content="<?= $prod ? 'product' : ($guide ? 'article' : 'website') ?>">
<meta property="og:site_name" content="<?= h($CONFIG['brand']) ?>">
<meta property="og:locale" content="en_US">
<meta property="og:title" content="<?= h($page_title) ?>">
<meta property="og:description" content="<?= h($page_desc) ?>">
<?php if($canonical): ?><meta property="og:url" content="<?= h($canonical) ?>"><?php endif; ?>
<?php if($og): ?><meta property="og:image" content="<?= h($abs_img($og[0])) ?>"><?php endif; ?>
<meta name="twitter:card" content="summary_large_image">
<meta name="theme-color" content="#050507">
<link rel="icon" href="assets/favicon.svg" type="image/svg+xml">
<?php if($page === 'home'): foreach(['google_verify'=>'google-site-verification', 'bing_verify'=>'msvalidate.01'] as $k=>$nm) if(($CONFIG[$k] ?? '') !== ''): ?><meta name="<?= $nm ?>" content="<?= h($CONFIG[$k]) ?>">
<?php endif; endif; ?>

<?php
$org_id = rtrim($CONFIG['domain'], '/').'/#org';
$graph = [
  ['@type'=>'Organization','@id'=>$org_id,'name'=>$CONFIG['brand'],'legalName'=>$CONFIG['legal_name'],'alternateName'=>$CONFIG['kanji'],'url'=>abs_url('home'),'email'=>$CONFIG['email'],
   'logo'=>rtrim($CONFIG['domain'], '/').'/assets/logo.svg',
   'address'=>['@type'=>'PostalAddress','streetAddress'=>$CONFIG['address'],'addressCountry'=>'JP'],
   'description'=>'Independent distributor and reseller of authentic Japanese Pokémon Trading Card Game products, shipping wholesale orders to Australia directly from Japan.',
   'knowsAbout'=>['Japanese Pokémon Trading Card Game', 'Pokémon TCG wholesale', 'Pokémon booster boxes'],
   'areaServed'=>array_values($COUNTRIES)],
  ['@type'=>'WebSite','@id'=>rtrim($CONFIG['domain'], '/').'/#site','url'=>abs_url('home'),'name'=>$CONFIG['brand'],'inLanguage'=>'en-AU',
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
    'seller'=>['@id'=>$org_id]];
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
    'dateModified'=>$guide['updated'] ?? gmdate('Y-m-d'),'inLanguage'=>'en-AU',
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
/* Black theme with holo-foil accents. System fonts only: nothing to download before first paint. */
:root{
  color-scheme:dark;
  --bg:#050507; --paper:#0A0B11; --card:#101219; --card2:#161924; --ink:#F5F6FB; --ink2:#C4C9D9; --muted:#8D94AA;
  --line:#252a3a; --hair:#1b1f2c;
  --red:#FF3B5C; --red2:#FF6B3D; --gold:#FFC94D; --teal:#22E1C3; --blue:#4DA3FF; --violet:#9B8CFF; --green:#3DDC84;
  --brand:var(--red); --link:#7FD9FF;
  --holo:linear-gradient(115deg,#FF3B5C 0%,#FFC94D 26%,#22E1C3 52%,#4DA3FF 76%,#9B8CFF 100%);
  --hot:linear-gradient(135deg,#FF3B5C,#FF6B3D);
  --serif:'Hiragino Mincho ProN','Yu Mincho','YuMincho','Noto Serif JP','Noto Serif',Georgia,'Times New Roman',serif;
  --sans:system-ui,-apple-system,'Segoe UI',Roboto,'Helvetica Neue','Hiragino Kaku Gothic ProN','Noto Sans JP',Arial,sans-serif;
  --r:10px;
}
*,*::before,*::after{box-sizing:border-box}
html{scroll-padding-top:130px;background:var(--bg)}
body{margin:0;background:var(--bg);color:var(--ink);font-family:var(--sans);font-size:16px;line-height:1.62;
  -webkit-font-smoothing:antialiased;font-variant-numeric:tabular-nums;
  background-image:radial-gradient(1100px 520px at 12% -8%,rgba(255,59,92,.16),transparent 60%),
                   radial-gradient(900px 480px at 92% -4%,rgba(155,140,255,.15),transparent 60%);
  background-repeat:no-repeat}
h1,h2,h3{font-family:var(--serif);font-weight:700;line-height:1.16;margin:0;letter-spacing:-.005em}
p{margin:0}a{color:inherit}img{max-width:100%;display:block;height:auto}
button,input,select,textarea{font:inherit;color:inherit}
:focus-visible{outline:2px solid var(--teal);outline-offset:3px;border-radius:4px}
.wrap{width:min(1240px,calc(100% - 40px));margin-inline:auto}
@media(max-width:640px){.wrap{width:calc(100% - 32px)}}
.holo-text{background:var(--holo);-webkit-background-clip:text;background-clip:text;color:transparent}

/* top strip */
.strip{background:#000;color:var(--ink2);font-size:12.5px;border-bottom:1px solid var(--hair);position:relative}
.strip::before{content:"";position:absolute;inset:0 0 auto 0;height:2px;background:var(--holo)}
.strip .wrap{display:flex;justify-content:space-between;align-items:center;gap:16px;min-height:36px;flex-wrap:wrap;padding-top:2px}
.strip a{color:var(--gold);text-decoration:none;font-weight:700}

/* header */
header.site{position:sticky;top:0;z-index:60;background:rgba(5,5,7,.82);backdrop-filter:blur(14px) saturate(1.4);
  -webkit-backdrop-filter:blur(14px) saturate(1.4);border-bottom:1px solid var(--line)}
.bar{display:flex;align-items:center;gap:16px;min-height:66px}
.brand{display:flex;align-items:center;gap:9px;text-decoration:none;flex:none}
.brand .mk{font-family:var(--serif);font-weight:800;font-size:20px;letter-spacing:.2em;color:#fff}
.brand .kj{font-family:var(--serif);font-size:12px;color:#fff;background:var(--hot);border-radius:4px;padding:2px 6px;letter-spacing:.05em}
form.search{flex:1;max-width:480px;display:flex}
form.search input{flex:1;background:var(--card);border:1px solid var(--line);border-right:none;color:var(--ink);
  border-radius:999px 0 0 999px;padding:10px 16px;font-size:14px;min-width:0}
form.search input::placeholder{color:var(--muted)}
form.search button{background:var(--card2);color:var(--ink);border:1px solid var(--line);
  border-radius:0 999px 999px 0;padding:0 18px;font-weight:700;cursor:pointer;font-size:14px}
form.search button:hover{color:#fff;border-color:var(--red)}
.tools{display:flex;align-items:center;gap:10px;margin-left:auto;flex:none}
select.pick{appearance:none;background-color:var(--card);border:1px solid var(--line);border-radius:999px;color:var(--ink);
  padding:8px 28px 8px 12px;font-size:13px;cursor:pointer;
  background-image:linear-gradient(45deg,transparent 50%,var(--muted) 50%),linear-gradient(135deg,var(--muted) 50%,transparent 50%);
  background-position:calc(100% - 15px) 53%,calc(100% - 11px) 53%;background-size:4px 4px;background-repeat:no-repeat}
.cartbtn{display:flex;align-items:center;gap:8px;background:var(--hot);color:#fff;text-decoration:none;border-radius:999px;
  padding:9px 16px;font-size:13.5px;font-weight:700;box-shadow:0 6px 22px -8px rgba(255,59,92,.7)}
.cartbtn b{background:rgba(0,0,0,.28);border-radius:999px;padding:0 7px;min-width:22px;text-align:center}

/* category bar */
.catbar{border-top:1px solid var(--hair)}
.catbar .wrap{display:flex;gap:2px;overflow-x:auto;scrollbar-width:none}
.catbar .wrap::-webkit-scrollbar{display:none}
.catbar a{padding:12px 8px;text-decoration:none;font-size:14px;font-weight:500;color:var(--ink2);white-space:nowrap;position:relative}
.catbar a::after{content:"";position:absolute;left:8px;right:8px;bottom:0;height:2px;border-radius:2px;background:var(--holo);opacity:0;transition:opacity .15s}
.catbar a:hover{color:#fff}.catbar a:hover::after{opacity:.6}
.catbar a.on{color:#fff;font-weight:700}.catbar a.on::after{opacity:1}
@media(max-width:820px){form.search{order:3;max-width:none;flex-basis:100%;margin-bottom:12px}.bar{flex-wrap:wrap;padding-top:10px}}
@media(max-width:520px){
  .bar{gap:10px}.brand{gap:6px}.brand .mk{font-size:16px;letter-spacing:.12em}.brand .kj{font-size:10.5px;padding:2px 5px}
  select.pick{padding:7px 22px 7px 10px;font-size:12.5px;background-position:calc(100% - 12px) 53%,calc(100% - 8px) 53%}
  .cartbtn{padding:8px 12px;font-size:13px}.tools{gap:6px}
  .strip .st{display:none}.strip .fship~.sl{display:none}.strip .wrap{justify-content:center;min-height:32px}
  .catbar a{padding:11px 11px;font-size:13.5px}
}

/* crumbs */
.crumbs{font-size:13px;color:var(--muted);padding:18px 0 0}
.crumbs a{text-decoration:none;color:var(--ink2)}.crumbs a:hover{color:#fff}

/* buttons */
.btn{display:inline-block;text-align:center;text-decoration:none;border:0;background:var(--hot);color:#fff;border-radius:999px;
  padding:12px 22px;font-size:15px;font-weight:700;cursor:pointer;box-shadow:0 8px 26px -10px rgba(255,59,92,.75);transition:transform .12s,box-shadow .12s}
.btn:hover{transform:translateY(-1px);box-shadow:0 12px 30px -10px rgba(255,59,92,.9)}
.btn.g{background:transparent;color:var(--ink);box-shadow:inset 0 0 0 1px var(--line)}
.btn.g:hover{box-shadow:inset 0 0 0 1px var(--teal);color:#fff}
.btn.gold{background:linear-gradient(135deg,#FFC94D,#FF9F43);color:#1b1204;box-shadow:0 8px 26px -10px rgba(255,201,77,.7)}
.btn.wide{width:100%;padding:15px}
.btn:disabled{opacity:.45;cursor:not-allowed;transform:none}

section{padding:56px 0}
.sechead{display:flex;justify-content:space-between;align-items:flex-end;gap:20px;margin-bottom:24px;flex-wrap:wrap}
.sechead h2{font-size:clamp(24px,3vw,34px)}
.sechead h1{font-size:clamp(28px,3.6vw,42px);margin:0}
.sechead p{color:var(--muted);font-size:14.5px;margin-top:8px;max-width:64ch}
.sechead>a{font-size:14px;font-weight:700;color:var(--teal);text-decoration:none;white-space:nowrap}
.sechead .count{color:var(--muted);font-size:14px}
section.top{padding-top:24px}
.sub2{font-size:clamp(20px,2.4vw,27px);margin:40px 0 16px}

/* hero */
.hero{padding:54px 0 46px;position:relative}
.hgrid{display:grid;grid-template-columns:1.05fr .95fr;gap:52px;align-items:center}
@media(max-width:960px){.hgrid{grid-template-columns:1fr;gap:32px}}
.eyebrow{display:flex;flex-wrap:wrap;gap:8px;margin-bottom:20px}
.eyebrow span{font-size:12px;font-weight:700;letter-spacing:.04em;padding:5px 11px;border-radius:999px;border:1px solid var(--line);color:var(--ink2);background:rgba(255,255,255,.02)}
.eyebrow span:first-child{color:#fff;border-color:transparent;background:var(--hot)}
h1{font-size:clamp(34px,5.2vw,62px);margin-bottom:18px}
.hero h1{background:linear-gradient(180deg,#fff 30%,#C9CCE0);-webkit-background-clip:text;background-clip:text;color:transparent}
.lede{font-size:17.5px;color:var(--ink2);max-width:56ch;margin-bottom:28px}
.hero-cta{display:flex;gap:12px;flex-wrap:wrap;margin-bottom:34px}
.stats{display:grid;grid-template-columns:repeat(4,auto);justify-content:start;gap:12px 30px;font-size:13px;color:var(--muted)}
@media(max-width:560px){.stats{grid-template-columns:1fr 1fr}}
.stats strong{display:block;font-size:22px;color:#fff}
.heroart{position:relative;border-radius:18px;overflow:hidden;aspect-ratio:4/3;background:var(--card);padding:1px}
.heroart::before{content:"";position:absolute;inset:0;border-radius:18px;padding:1px;background:var(--holo);
  -webkit-mask:linear-gradient(#000 0 0) content-box,linear-gradient(#000 0 0);-webkit-mask-composite:xor;mask-composite:exclude;pointer-events:none;z-index:2}
.heroart img,.heroart .ph{width:100%;height:100%;object-fit:cover;border-radius:17px}
.heroart::after{content:"";position:absolute;inset:auto -20% -40% -20%;height:70%;background:radial-gradient(closest-side,rgba(34,225,195,.25),transparent);pointer-events:none}

.heroart.light{background:#fff}
.heroart.light::after{display:none}
.heroart.light img{object-fit:contain;padding:22px;background:#fff}
.heroimg{display:block;width:100%;height:100%}

/* featured release */
.feature{position:relative;overflow:hidden;border-block:1px solid var(--hair);
  background:radial-gradient(700px 360px at 18% 40%,rgba(255,201,77,.18),transparent 60%),radial-gradient(640px 320px at 88% 70%,rgba(255,59,92,.14),transparent 60%),#000}
.fgrid{display:grid;grid-template-columns:230px 1fr 400px;gap:40px;align-items:center}
@media(max-width:1060px){.fgrid{grid-template-columns:200px 1fr}.fbox{grid-column:1 / -1}}
@media(max-width:640px){.fgrid{grid-template-columns:1fr;gap:24px}.fpack{max-width:200px;margin:0 auto}}
.fpack{display:block;border-radius:14px;overflow:hidden;transform:rotate(-4deg);box-shadow:0 30px 60px -22px rgba(255,201,77,.55),0 0 0 1px rgba(255,255,255,.08);transition:transform .2s}
.fpack:hover{transform:rotate(-2deg) translateY(-4px)}
.kicker{display:inline-block;font-size:12px;font-weight:800;letter-spacing:.08em;text-transform:uppercase;color:#1b1204;background:linear-gradient(135deg,#FFC94D,#FF9F43);padding:5px 12px;border-radius:999px;margin-bottom:14px}
.ftext h2{font-size:clamp(28px,3.6vw,44px);margin-bottom:14px}
.ftext p{color:var(--ink2);font-size:16.5px;max-width:52ch}
.fbox{margin:0;background:#fff;border-radius:14px;overflow:hidden;box-shadow:0 24px 50px -24px rgba(0,0,0,.9)}
.fbox figcaption{background:var(--card);color:var(--ink2);font-size:13px;padding:10px 14px}

/* placeholder art (until photos are uploaded) */
.ph{width:100%;height:100%;display:flex;align-items:center;justify-content:center;flex-direction:column;gap:8px;text-align:center;padding:14px;
  color:var(--muted);font-size:12px;
  background:radial-gradient(120% 80% at 20% 10%,rgba(255,59,92,.18),transparent 55%),
             radial-gradient(120% 80% at 90% 90%,rgba(34,225,195,.16),transparent 55%),
             repeating-linear-gradient(135deg,rgba(255,255,255,.025) 0 2px,transparent 2px 10px),var(--card2)}
.ph span:first-child{font-family:var(--serif);font-size:30px;background:var(--holo);-webkit-background-clip:text;background-clip:text;color:transparent}

/* category tiles */
.cats{display:grid;grid-template-columns:repeat(5,1fr);gap:14px}
@media(max-width:1000px){.cats{grid-template-columns:repeat(2,1fr)}}
@media(max-width:520px){.cats{grid-template-columns:1fr}}
.cats a{--c:var(--red);background:linear-gradient(180deg,color-mix(in srgb,var(--c) 14%,var(--card)),var(--card) 60%);
  border:1px solid var(--line);border-top:3px solid var(--c);border-radius:var(--r);padding:18px;text-decoration:none;display:block;transition:transform .15s,border-color .15s}
.cats a:nth-child(2){--c:var(--gold)}.cats a:nth-child(3){--c:var(--teal)}.cats a:nth-child(4){--c:var(--blue)}.cats a:nth-child(5){--c:var(--violet)}
.cats a:hover{transform:translateY(-2px);border-color:var(--c)}
.cats .n{font-size:12px;color:var(--c);font-weight:700}
.cats h3{font-size:17px;margin:7px 0;color:#fff}
.cats p{font-size:13px;color:var(--ink2)}

/* chips (collections) */
.chips{display:flex;flex-wrap:wrap;gap:10px}
.chips a{padding:9px 16px;border-radius:999px;border:1px solid var(--line);text-decoration:none;font-size:14px;font-weight:600;color:var(--ink);background:var(--card);transition:border-color .15s}
.chips a:hover{border-color:var(--teal)}

/* product grid */
.grid{display:grid;grid-template-columns:repeat(4,1fr);gap:16px}
@media(max-width:1040px){.grid{grid-template-columns:repeat(2,1fr)}}
@media(max-width:520px){.grid{grid-template-columns:1fr}}
.card{background:var(--card);border:1px solid var(--line);border-radius:var(--r);overflow:hidden;display:flex;flex-direction:column;
  transition:transform .15s,box-shadow .15s,border-color .15s}
.card:hover{transform:translateY(-3px);border-color:rgba(255,59,92,.55);box-shadow:0 18px 44px -18px rgba(155,140,255,.45)}
.card .art{aspect-ratio:1/1;position:relative;border-bottom:1px solid var(--hair);overflow:hidden;text-decoration:none;display:block;background:var(--card2)}
.card .art img{width:100%;height:100%;object-fit:cover}
.flag{position:absolute;top:10px;left:10px;font-size:10.5px;font-weight:800;letter-spacing:.06em;padding:4px 9px;border-radius:999px;
  background:rgba(61,220,132,.16);color:var(--green);border:1px solid rgba(61,220,132,.4);z-index:2;backdrop-filter:blur(6px)}
.flag.new{background:rgba(34,225,195,.16);color:var(--teal);border-color:rgba(34,225,195,.45)}
.flag.pre{background:rgba(255,201,77,.16);color:var(--gold);border-color:rgba(255,201,77,.45)}
.flag.low{background:rgba(255,59,92,.18);color:#FF7A92;border-color:rgba(255,59,92,.5)}
.flag.out{background:rgba(141,148,170,.16);color:var(--muted);border-color:var(--line)}
.card .in{padding:15px;display:flex;flex-direction:column;gap:7px;flex:1}
.card h3{font-family:var(--sans);font-size:15px;font-weight:700;line-height:1.35}
.card h3 a{text-decoration:none}
.card .meta{font-size:12px;color:var(--muted)}
.card .px{display:flex;align-items:baseline;gap:8px;margin-top:auto;flex-wrap:wrap}
.card .px .u{font-size:22px;font-weight:900;color:#fff}
.card .px .w{font-size:12.5px;color:var(--muted);text-decoration:line-through}
.card .px .per{font-size:12px;color:var(--muted);margin-left:-4px}
.card .drop{font-size:12.5px;color:var(--ink2)}
.card .drop b{color:var(--gold)}
.card form{padding:0 15px 15px;display:flex;gap:8px}
.card form .btn{flex:1;padding:10px;font-size:13.5px}

/* catalogue filters */
.filters{display:flex;flex-wrap:wrap;gap:10px 12px;align-items:flex-end;background:var(--card);border:1px solid var(--line);border-radius:var(--r);padding:14px;margin-bottom:22px}
.filters label{display:flex;flex-direction:column;gap:5px;font-size:11.5px;font-weight:700;letter-spacing:.03em;color:var(--muted);flex:1 1 150px;min-width:0;text-transform:uppercase}
.filters select,.filters input{background:var(--paper);border:1px solid var(--line);border-radius:8px;padding:9px 10px;font-size:14px;font-weight:400;color:var(--ink);width:100%;text-transform:none;letter-spacing:0}
.filters .price span{display:flex;gap:6px}
.filters .fbtns{display:flex;gap:8px}
.filters .fbtns .btn{padding:10px 16px;font-size:14px}
@media(max-width:560px){.filters label{flex-basis:calc(50% - 6px)}.filters label.price{flex-basis:100%}}

/* qty stepper */
.step{display:flex;border:1px solid var(--line);border-radius:999px;overflow:hidden;background:var(--paper)}
.step button{background:transparent;border:none;padding:6px 12px;cursor:pointer;font-weight:700;color:var(--ink2)}
.step button:hover{background:var(--card2);color:#fff}
.step input{width:52px;border:none;border-inline:1px solid var(--line);background:transparent;text-align:center;padding:6px 0;font-size:14px;color:#fff}

/* product page */
.pdp{display:grid;grid-template-columns:1.02fr .98fr;gap:46px;padding:24px 0 10px}
@media(max-width:900px){.pdp{grid-template-columns:1fr;gap:28px}}
.gal-main{aspect-ratio:1/1;border:1px solid var(--line);border-radius:16px;overflow:hidden;background:var(--card2)}
.gal-main img{width:100%;height:100%;object-fit:cover}
.gal-thumbs{display:flex;gap:9px;margin-top:10px}
.gal-thumbs button{width:72px;height:72px;border:1px solid var(--line);border-radius:10px;overflow:hidden;padding:0;cursor:pointer;background:var(--card)}
.gal-thumbs button[aria-current="true"]{border:2px solid var(--teal)}
.gal-thumbs img{width:100%;height:100%;object-fit:cover}
.pdp h1{font-size:clamp(26px,3.4vw,38px);margin-bottom:10px}
.pdp .sub{font-size:13.5px;color:var(--muted);margin-bottom:16px}
.pdp .summary-line{color:var(--ink2);font-size:16px;margin-bottom:20px;max-width:60ch}
.ladder{border:1px solid var(--line);border-radius:var(--r);overflow:hidden;margin-bottom:18px;background:var(--card)}
.ladder .lh{padding:11px 16px;background:var(--card2);font-size:12px;font-weight:800;letter-spacing:.05em;text-transform:uppercase;color:var(--ink2)}
.ladder .row{display:flex;justify-content:space-between;padding:11px 16px;font-size:14.5px;border-top:1px solid var(--hair);color:var(--ink2)}
.ladder .row.on{color:#fff;font-weight:700;background:linear-gradient(90deg,rgba(255,59,92,.14),transparent)}
.ladder .row:last-child span:last-child{color:var(--gold)}
.buybox{border:1px solid var(--line);border-radius:var(--r);padding:20px;background:linear-gradient(180deg,var(--card2),var(--card));position:relative;overflow:hidden}
.buybox::before{content:"";position:absolute;inset:0 0 auto 0;height:3px;background:var(--holo)}
.buybox .big{font-size:40px;font-weight:900;line-height:1.1;color:#fff}
.buybox .sm{font-size:13.5px;color:var(--muted);margin-bottom:14px}
.buybox form{display:flex;gap:10px;align-items:center;margin-top:12px;flex-wrap:wrap}
.buybox form .btn{flex:1;min-width:150px}
.trustline{display:flex;gap:10px;flex-wrap:wrap;font-size:12.5px;margin-top:16px}
.trustline span{padding:5px 10px;border-radius:999px;background:rgba(34,225,195,.08);border:1px solid rgba(34,225,195,.25);color:var(--teal)}
.pinfo{display:grid;grid-template-columns:1.2fr .8fr;gap:28px;margin-top:10px}
@media(max-width:900px){.pinfo{grid-template-columns:1fr}}
.panel{background:var(--card);border:1px solid var(--line);border-radius:var(--r);padding:22px}
.panel h2{font-size:21px;margin-bottom:14px}
.specs{display:grid;grid-template-columns:auto 1fr;gap:0;margin:0;font-size:14.5px}
.specs dt,.specs dd{padding:10px 0;border-bottom:1px solid var(--hair);margin:0}
.specs dt{color:var(--muted);padding-right:18px}
.specs dd{color:#fff;font-weight:600}
.specs dd a{color:var(--link);text-decoration:none}
.specs dt:last-of-type,.specs dd:last-of-type{border-bottom:none}
.plinks{display:grid;gap:8px;margin-top:4px}
.plinks a{color:var(--link);text-decoration:none;font-weight:600;font-size:14.5px}
.plinks a:hover{text-decoration:underline}

/* tables / cart */
.tbl{width:100%;border-collapse:separate;border-spacing:0;background:var(--card);border:1px solid var(--line);border-radius:var(--r);overflow:hidden}
.tbl th{text-align:left;font-size:11.5px;letter-spacing:.05em;text-transform:uppercase;color:var(--muted);font-weight:700;padding:12px 14px;border-bottom:1px solid var(--line);background:var(--card2)}
.tbl td{padding:14px;border-bottom:1px solid var(--hair);font-size:14.5px;vertical-align:top;color:var(--ink2)}
.tbl tr:last-child td{border-bottom:none}
.tbl .r{text-align:right;white-space:nowrap}
.tbl .nm{font-weight:700;color:#fff}
.tbl .sk{font-size:12px;color:var(--muted);margin-top:3px}
.tbl a{color:var(--link)}
@media(max-width:700px){.tbl thead{display:none}.tbl td{display:block;border:none;padding:6px 14px}
  .tbl tr{display:block;border-bottom:1px solid var(--line);padding:10px 0}.tbl .r{text-align:left}}

/* checkout */
.cogrid{display:grid;grid-template-columns:1.25fr .75fr;gap:38px;align-items:start}
@media(max-width:900px){.cogrid{grid-template-columns:1fr}}
fieldset{border:1px solid var(--line);border-radius:var(--r);padding:20px;margin:0 0 18px;background:var(--card)}
legend{font-family:var(--serif);font-weight:700;font-size:18px;padding:0 8px;color:#fff}
.fld{margin-bottom:14px}
.fld label{display:block;font-size:13px;font-weight:700;color:var(--ink2);margin-bottom:5px}
.fld input,.fld select,.fld textarea{width:100%;background:var(--paper);border:1px solid var(--line);border-radius:8px;padding:11px 12px;font-size:15px;color:var(--ink)}
.fld input:focus,.fld select:focus,.fld textarea:focus{border-color:var(--teal);outline:none}
.fld textarea{min-height:82px;resize:vertical}
.two{display:grid;grid-template-columns:1fr 1fr;gap:14px}
@media(max-width:560px){.two{grid-template-columns:1fr}}
.pay{display:grid;gap:9px}
.pay label{display:flex;gap:11px;align-items:flex-start;border:1px solid var(--line);border-radius:10px;padding:13px 14px;cursor:pointer;background:var(--paper)}
.pay label:has(input:checked){border-color:var(--red);background:rgba(255,59,92,.08)}
.pay label[hidden]{display:none}
.pay input{margin-top:4px;accent-color:var(--red)}
.pay .t{font-weight:700;font-size:14.5px;color:#fff}
.pay .n{font-size:12.5px;color:var(--muted)}
.notice{border-left:3px solid var(--teal);background:rgba(34,225,195,.07);padding:13px 15px;font-size:13.5px;color:var(--ink2);border-radius:0 8px 8px 0;margin:14px 0}
.notice a{color:var(--link)}
.agree{display:flex;gap:10px;align-items:flex-start;font-size:13.5px;color:var(--ink2);margin:14px 0 4px}
.agree input{margin-top:4px;accent-color:var(--red)}
.summary{border:1px solid var(--line);border-radius:var(--r);background:var(--card);padding:18px;position:sticky;top:130px}
.summary h3{font-size:19px;margin-bottom:12px}
.sl{display:flex;justify-content:space-between;gap:12px;font-size:13.5px;padding:8px 0;border-bottom:1px solid var(--hair)}
.sl:last-of-type{border-bottom:none}
.sl .q{color:var(--muted);font-size:12px}
.tot{display:flex;justify-content:space-between;font-size:20px;font-weight:900;padding-top:12px;margin-top:8px;border-top:1px solid var(--line);color:#fff}
.errs{border:1px solid rgba(255,59,92,.6);border-radius:var(--r);padding:14px 16px;margin-bottom:20px;background:rgba(255,59,92,.1);font-size:14px}
.errs ul{margin:6px 0 0;padding-left:18px}
.hp{position:absolute;left:-9999px;width:1px;height:1px;overflow:hidden}
.minwarn{border-left:3px solid var(--red);background:rgba(255,59,92,.1);padding:11px 14px;font-size:13.5px;border-radius:0 8px 8px 0;margin-top:12px}
.minwarn[hidden]{display:none}

/* confirmation */
.done{max-width:720px;margin:0 auto;text-align:center;padding:20px 0}
.done .ref{font-family:var(--serif);font-size:36px;letter-spacing:.06em;margin:14px 0 6px;background:var(--holo);-webkit-background-clip:text;background-clip:text;color:transparent}
.done .card2{border:1px solid var(--line);border-radius:var(--r);background:var(--card);padding:26px;text-align:left;margin-top:26px}
.done ol{padding-left:20px;margin:12px 0 0}
.done li{margin-bottom:10px;font-size:14.5px;color:var(--ink2)}

/* feature band */
.deep{position:relative;background:#000;border-block:1px solid var(--hair);overflow:hidden}
.deep::before{content:"";position:absolute;inset:0;background:radial-gradient(700px 300px at 15% 20%,rgba(255,59,92,.18),transparent 60%),
  radial-gradient(700px 320px at 90% 80%,rgba(34,225,195,.14),transparent 60%);pointer-events:none}
.deep .wrap{position:relative}
.deep h2{color:#fff}.deep p{color:var(--ink2);margin-top:14px;font-size:15.5px}
.deep .two2{display:grid;grid-template-columns:1fr 1fr;gap:44px;align-items:center}
@media(max-width:860px){.deep .two2{grid-template-columns:1fr;gap:26px}}
.spec{border:1px solid var(--line);border-radius:var(--r);background:rgba(16,18,25,.7);backdrop-filter:blur(6px)}
.spec div{display:flex;justify-content:space-between;gap:12px;padding:12px 17px;font-size:14px;border-bottom:1px solid var(--hair)}
.spec div:last-child{border-bottom:none}
.spec span:first-child{color:var(--muted)}.spec span:last-child{font-weight:700;color:#fff;text-align:right}

/* steps */
.steps{display:grid;grid-template-columns:repeat(4,1fr);gap:14px}
@media(max-width:900px){.steps{grid-template-columns:1fr 1fr}}
@media(max-width:540px){.steps{grid-template-columns:1fr}}
.steps>div{padding:20px 18px 22px;border:1px solid var(--line);border-radius:var(--r);background:var(--card)}
.steps .n{font-family:var(--serif);font-size:26px;font-weight:800;margin-bottom:8px;background:var(--holo);-webkit-background-clip:text;background-clip:text;color:transparent;display:inline-block}
.steps h3{font-size:17px;margin-bottom:7px}
.steps p{font-size:14px;color:var(--ink2)}

/* faq */
.faq details{border-bottom:1px solid var(--line);padding:16px 0}
.faq summary{cursor:pointer;font-weight:700;font-size:16px;list-style:none;display:flex;justify-content:space-between;gap:14px;color:#fff}
.faq summary::-webkit-details-marker{display:none}
.faq summary::after{content:"+";color:var(--red);font-size:20px;line-height:1}
.faq details[open] summary::after{content:"–"}
.faq p{margin-top:9px;font-size:14.5px;color:var(--ink2);max-width:80ch}

/* footer */
footer.site{background:#000;color:var(--ink2);padding:50px 0 30px;margin-top:30px;position:relative}
footer.site::before{content:"";position:absolute;inset:0 0 auto 0;height:2px;background:var(--holo)}
.fg{display:grid;grid-template-columns:1.4fr repeat(4,1fr);gap:28px}
@media(max-width:860px){.fg{grid-template-columns:1fr 1fr}}
@media(max-width:520px){.fg{grid-template-columns:1fr}}
footer h4{font-size:12px;letter-spacing:.08em;text-transform:uppercase;color:#fff;margin:0 0 12px}
footer ul{list-style:none;margin:0;padding:0;display:grid;gap:8px}
footer a{color:var(--ink2);text-decoration:none;font-size:14px}
footer a:hover{color:#fff;text-decoration:underline}
footer .bl{font-size:14px;color:var(--muted);margin-top:12px;max-width:44ch}
.legal{margin-top:32px;padding-top:18px;border-top:1px solid var(--hair);font-size:12.5px;color:var(--muted);display:grid;gap:9px}
.empty{padding:40px 0;color:var(--muted)}
.empty a{color:var(--link)}

/* long-form text */
.prose{max-width:760px;color:var(--ink2);font-size:16px;line-height:1.72}
.prose>:first-child{margin-top:0}
.prose h2{font-size:clamp(22px,2.4vw,28px);color:#fff;margin:34px 0 12px}
.prose h3{font-size:19px;color:#fff;margin:24px 0 8px}
.prose p{margin:0 0 15px}
.prose ul{margin:0 0 15px;padding-left:0;list-style:none}
.prose li{margin-bottom:8px;padding-left:22px;position:relative}
.prose li::before{content:"";position:absolute;left:4px;top:.62em;width:7px;height:7px;border-radius:2px;background:var(--holo);transform:rotate(45deg)}
.prose a{color:var(--link);font-weight:600;text-decoration:none;border-bottom:1px solid rgba(127,217,255,.35)}
.prose a:hover{border-bottom-color:var(--link)}
.prose strong{color:#fff}
.prose.lead{margin-bottom:26px}
.prose.after{margin-top:48px;padding-top:28px;border-top:1px solid var(--line)}

/* set tiles and guide cards */
.tiles{display:grid;grid-template-columns:repeat(auto-fill,minmax(160px,1fr));gap:12px}
.tiles a{background:var(--card);border:1px solid var(--line);border-radius:var(--r);padding:15px 16px;text-decoration:none;display:flex;flex-direction:column;gap:3px;position:relative;overflow:hidden;transition:border-color .15s,transform .15s}
.tiles a::before{content:"";position:absolute;inset:0 0 auto 0;height:2px;background:var(--holo);opacity:.7}
.tiles a:hover{border-color:var(--teal);transform:translateY(-2px)}
.tiles b{font-family:var(--serif);font-size:16px;line-height:1.3;color:#fff}
.tiles .n{font-size:12px;color:var(--muted)}
.tiles .n:first-child{color:var(--gold);font-weight:700;letter-spacing:.04em}
.gcards{display:grid;grid-template-columns:repeat(auto-fill,minmax(260px,1fr));gap:14px}
.gcards a{background:linear-gradient(160deg,rgba(155,140,255,.1),transparent 45%),var(--card);border:1px solid var(--line);border-radius:var(--r);padding:20px;text-decoration:none;display:flex;flex-direction:column;gap:9px;transition:border-color .15s,transform .15s}
.gcards a:hover{border-color:var(--violet);transform:translateY(-2px)}
.gcards h3{font-size:18px;color:#fff}
.gcards p{font-size:14px;color:var(--ink2)}
.gcards span{margin-top:auto;font-size:13.5px;font-weight:700;color:var(--teal)}

/* guides */
.article{max-width:780px}
.article h1{font-size:clamp(30px,4.2vw,46px);margin-bottom:10px}
.article .meta{font-size:13px;color:var(--muted);margin-bottom:22px}
.toc{border:1px solid var(--line);border-radius:var(--r);background:var(--card);padding:16px 20px;margin:0 0 28px}
.toc b{display:block;font-size:12px;letter-spacing:.06em;text-transform:uppercase;color:var(--muted);margin-bottom:8px}
.toc ol{margin:0;padding-left:20px;display:grid;gap:5px}
.toc a{color:var(--link);text-decoration:none;font-size:14.5px}
.toc a:hover{text-decoration:underline}

/* free shipping: top bar, cart and checkout */
.strip .fship{display:inline-flex;align-items:center;gap:7px;color:#fff;font-weight:800;letter-spacing:.01em}
.strip .fship svg{color:var(--gold);flex:none}
.strip .fship span{background:linear-gradient(90deg,#fff,#FFE3A1);-webkit-background-clip:text;background-clip:text;color:transparent}
.strip .fship:hover span{text-decoration:underline;text-decoration-color:var(--gold)}
.fsm{margin:10px 0 12px;max-width:420px;margin-left:auto;text-align:left}
.fsm .t{font-size:13.5px;color:var(--ink2);margin-bottom:7px}.fsm .t b{color:#fff}
.fsm .meter{height:7px;border-radius:9px;background:var(--hair);overflow:hidden}
.fsm .meter i{display:block;height:100%;background:var(--holo);border-radius:9px}
.fsm.c{max-width:none;margin:12px 0 0}

/* Bitcoin payment page */
.payhead{margin-bottom:22px}
.payhead .kick{font-size:13px;color:var(--muted);letter-spacing:.04em}.payhead .kick b{color:var(--gold)}
.payhead h1{font-size:clamp(28px,3.6vw,42px);margin:6px 0 10px}
.payhead .lede{margin-bottom:0}
.paygrid{display:grid;grid-template-columns:minmax(0,1fr) 360px;gap:26px;align-items:start}
@media(max-width:960px){.paygrid{grid-template-columns:1fr}.paygrid .summary{position:static}}
.paybox{border:1px solid var(--line);border-radius:16px;background:linear-gradient(180deg,rgba(255,201,77,.07),var(--card) 38%);padding:24px;position:relative;overflow:hidden}
.paybox::before{content:"";position:absolute;inset:0 0 auto 0;height:3px;background:linear-gradient(90deg,#F7931A,#FFC94D)}
.payrow{display:grid;grid-template-columns:250px minmax(0,1fr);gap:26px;align-items:start}
@media(max-width:640px){.payrow{grid-template-columns:1fr}.qrcol{max-width:300px;margin:0 auto;width:100%}}
.qr{background:#fff;border-radius:14px;padding:10px;aspect-ratio:1;display:grid;place-items:center;margin-bottom:12px;box-shadow:0 18px 50px -24px rgba(247,147,26,.8)}
.qr svg{width:100%;height:100%;display:block}
.qr .qrph{color:#666;font-size:13px}
.pf .l{font-size:11.5px;letter-spacing:.08em;text-transform:uppercase;color:var(--muted);font-weight:700;margin-bottom:6px}
.pf .v{display:flex;align-items:center;gap:10px;flex-wrap:wrap}
.pf .amt span{font-size:clamp(26px,3.4vw,34px);font-weight:900;color:#fff;letter-spacing:.01em;font-variant-numeric:tabular-nums}
.pf .amt small{font-size:16px;font-weight:800;color:#F7931A}
.pf .n{font-size:13px;color:var(--muted);margin-top:6px}
.pf .addr code{font-family:ui-monospace,SFMono-Regular,Menlo,Consolas,monospace;font-size:14.5px;color:#fff;background:var(--paper);border:1px solid var(--line);border-radius:8px;padding:9px 11px;word-break:break-all;flex:1;min-width:0}
.copy{background:var(--card2);border:1px solid var(--line);color:#fff;border-radius:999px;padding:7px 14px;font-size:13px;font-weight:700;cursor:pointer;white-space:nowrap}
.copy:hover{border-color:var(--gold);color:var(--gold)}
.hold{font-size:13.5px;color:var(--ink2);margin-top:16px}.hold b{color:var(--gold);font-variant-numeric:tabular-nums}
.watch{display:flex;align-items:center;gap:10px;font-size:13.5px;color:var(--teal);margin-top:14px;padding:11px 13px;border-radius:10px;background:rgba(34,225,195,.07);border:1px solid rgba(34,225,195,.25)}
.pulse{width:9px;height:9px;border-radius:50%;background:var(--teal);flex:none;box-shadow:0 0 0 0 rgba(34,225,195,.7);animation:pulse 1.8s infinite}
@keyframes pulse{70%{box-shadow:0 0 0 10px rgba(34,225,195,0)}100%{box-shadow:0 0 0 0 rgba(34,225,195,0)}}
.paytips{list-style:none;padding:0;margin:22px 0 0;display:grid;gap:9px;font-size:13.5px;color:var(--ink2)}
.paytips li{padding-left:22px;position:relative}.paytips b{color:#fff}
.paytips li::before{content:"";position:absolute;left:4px;top:.55em;width:7px;height:7px;border-radius:2px;background:#F7931A;transform:rotate(45deg)}
.txform{margin-top:20px;border-top:1px solid var(--hair);padding-top:14px}
.txform summary{cursor:pointer;font-size:14px;font-weight:700;color:var(--link)}
.txform label{display:block;font-size:13px;color:var(--muted);margin:12px 0 6px}
.txrow{display:flex;gap:10px;flex-wrap:wrap}
.txrow input{flex:1;min-width:220px;background:var(--paper);border:1px solid var(--line);border-radius:10px;padding:11px 13px;font-family:ui-monospace,Menlo,Consolas,monospace;font-size:13px}
.timeline{list-style:none;margin:0;padding:0;display:grid;gap:0}
.timeline li{position:relative;padding:0 0 22px 38px;display:grid;gap:2px}
.timeline li::before{content:"";position:absolute;left:0;top:1px;width:22px;height:22px;border-radius:50%;border:2px solid var(--line);background:var(--paper)}
.timeline li::after{content:"";position:absolute;left:11px;top:26px;bottom:2px;width:2px;background:var(--line)}
.timeline li:last-child::after{display:none}
.timeline li.ok::before{background:var(--green);border-color:var(--green);box-shadow:inset 0 0 0 5px var(--green)}
.timeline li.ok::after{background:var(--green)}
.timeline li.now::before{border-color:var(--gold);animation:pulse 1.8s infinite}
.timeline b{color:#fff;font-size:15.5px}.timeline span{font-size:13.5px;color:var(--muted)}
.txbox{margin-top:8px;padding:14px;border-radius:10px;background:var(--paper);border:1px solid var(--line);display:grid;gap:6px}
.txbox .l{font-size:11.5px;letter-spacing:.08em;text-transform:uppercase;color:var(--muted);font-weight:700}
.txbox code{font-family:ui-monospace,Menlo,Consolas,monospace;font-size:12.5px;color:#fff;word-break:break-all}
.txbox a{color:var(--link);font-weight:700;font-size:14px;text-decoration:none}
.summary .n{font-size:12.5px;color:var(--muted);margin-top:10px}.summary .n a{color:var(--link)}

/* guide links on category, set, collection and FAQ pages */
.glinks{margin-top:8px}
.gchips{display:flex;flex-wrap:wrap;gap:10px}
.gchips a{border:1px solid var(--line);background:var(--card);border-radius:999px;padding:9px 16px;font-size:14px;font-weight:600;color:var(--ink);text-decoration:none}
.gchips a:hover{border-color:var(--teal);color:#fff}

/* guide tools */
.pcheck{display:flex;align-items:center;gap:12px;flex-wrap:wrap;margin:6px 0 12px}
.pcheck label{font-weight:800;color:#fff}
.pcheck input{flex:1;min-width:220px;background:var(--card);border:1px solid var(--line);border-radius:999px;padding:11px 16px;font-size:15px;color:var(--ink)}
.pcheck input:focus{border-color:var(--teal);outline:none}
.pcheck span{font-size:13px;color:var(--muted)}
.tbl.pc .sk{font-weight:500}
.tplbox{display:grid;grid-template-columns:200px minmax(0,1fr);gap:22px;align-items:center;border:1px solid var(--line);border-radius:14px;background:var(--card);padding:18px;margin:6px 0 20px}
.tplbox img{width:100%;height:auto;background:#fff;border-radius:10px}
.tplbox b{color:#fff;font-size:17px}.tplbox p{margin:8px 0 0}
.prose .tplbox .btn{color:#fff;border-bottom:0;margin:4px 6px 0 0}.prose .tplbox .btn.g{color:var(--ink)}
@media(max-width:560px){.tplbox{grid-template-columns:1fr}.tplbox img{max-width:220px}}

/* Shipping & Returns */
.srhead h1{font-size:clamp(30px,4vw,46px)}
.srhead .lede{margin:12px 0 26px}
.srcards{display:grid;grid-template-columns:repeat(4,1fr);gap:14px;margin-bottom:40px}
@media(max-width:900px){.srcards{grid-template-columns:1fr 1fr}}
@media(max-width:520px){.srcards{gap:10px}.srcards>div{padding:14px}.srcards .ic{width:32px;height:32px}.srcards b{font-size:15px}}
.srcards>div{--c:var(--gold);border:1px solid var(--line);border-radius:14px;padding:18px;display:grid;gap:4px;
  background:linear-gradient(180deg,color-mix(in srgb,var(--c) 13%,var(--card)),var(--card) 70%)}
.srcards .k2{--c:var(--teal)}.srcards .k3{--c:var(--red)}.srcards .k4{--c:var(--violet)}
.srcards .ic{width:38px;height:38px;border-radius:10px;display:grid;place-items:center;color:var(--c);background:color-mix(in srgb,var(--c) 16%,transparent);margin-bottom:6px}
.srcards .ic svg{width:21px;height:21px}
.srcards b{color:#fff;font-size:16px}.srcards span:last-child{font-size:13.5px;color:var(--ink2)}
.srgrid{display:grid;grid-template-columns:230px minmax(0,1fr);gap:48px;align-items:start}
.srtoc{position:sticky;top:140px;border-left:1px solid var(--line);padding-left:16px}
.srtoc b{font-size:11.5px;letter-spacing:.08em;text-transform:uppercase;color:var(--muted)}
.srtoc ol{list-style:none;margin:10px 0 0;padding:0;display:grid;gap:2px}
.srtoc a{display:block;padding:5px 0;font-size:14px;color:var(--ink2);text-decoration:none}
.srtoc a:hover{color:#fff}
@media(max-width:960px){
  .srgrid{grid-template-columns:1fr;gap:10px}
  .srtoc{position:static;border:none;padding:0}
  .srtoc ol{display:flex;gap:8px;overflow-x:auto;scrollbar-width:none;padding-bottom:6px}
  .srtoc a{white-space:nowrap;border:1px solid var(--line);border-radius:999px;padding:6px 12px;font-size:13px;background:var(--card)}
}
.prose.sr{max-width:780px}
.prose.sr h2{scroll-margin-top:140px;padding-top:8px}
.prose.sr h3{scroll-margin-top:140px}
.prose ol{margin:0 0 15px;padding:0;list-style:none;counter-reset:step}
.prose ol li{counter-increment:step;padding-left:40px;margin-bottom:12px;min-height:28px}
.prose ol li::before{content:counter(step);position:absolute;left:0;top:0;width:28px;height:28px;border-radius:50%;background:var(--card2);
  border:1px solid var(--line);color:var(--gold);font-weight:800;font-size:13px;display:grid;place-items:center;transform:none}
.prose .tblwrap{overflow-x:auto;margin:0 0 16px}
.prose .tbl small{color:var(--muted);font-weight:500}
.prose .tbl .free{color:var(--green)}
.prose p.small{font-size:13.5px;color:var(--muted)}
.prose code{font-family:ui-monospace,Menlo,Consolas,monospace;font-size:.9em;color:#fff;background:var(--card2);padding:2px 6px;border-radius:5px;word-break:break-all}
.srcontact{margin-top:40px;border:1px solid var(--line);border-radius:14px;padding:20px;display:flex;justify-content:space-between;align-items:center;gap:16px;flex-wrap:wrap;
  background:linear-gradient(120deg,rgba(255,59,92,.09),rgba(155,140,255,.09))}
.srcontact b{display:block;color:#fff;font-size:17px}.srcontact span{font-size:14px;color:var(--ink2)}
.srcontact .row2{display:flex;gap:10px;flex-wrap:wrap}
.prose .srcontact .btn{color:#fff;border-bottom:0}

@media (prefers-reduced-motion:reduce){*{transition:none!important;animation:none!important}}
</style>
</head>
<body>

<div class="strip"><div class="wrap">
  <?php if(free_ship_usd($STORE)): ?><a class="fship" href="<?= url('shipping') ?>"><svg viewBox="0 0 24 24" width="17" height="17" aria-hidden="true"><path fill="currentColor" d="M3 6.5A1.5 1.5 0 0 1 4.5 5h9A1.5 1.5 0 0 1 15 6.5V8h2.6a1.5 1.5 0 0 1 1.2.6l2.4 3.2c.2.26.3.58.3.9V16a1.5 1.5 0 0 1-1.5 1.5h-.6a2.75 2.75 0 0 1-5.3 0H9.9a2.75 2.75 0 0 1-5.3 0h-.1A1.5 1.5 0 0 1 3 16V6.5Zm12 3V13h4.5l-1.9-2.5a1.5 1.5 0 0 0-1.2-.6H15ZM7.25 18.25a1 1 0 1 0 0-2 1 1 0 0 0 0 2Zm9.4 0a1 1 0 1 0 0-2 1 1 0 0 0 0 2Z"/></svg><span>Free shipping on orders over <?= money_whole(free_ship_usd($STORE)) ?></span></a><?php endif; ?>
  <span class="st"><?= h($CONFIG['strip_text']) ?></span>
  <?php if($CONFIG['strip_link_text']): ?><a class="sl" href="<?= h($CONFIG['strip_link_url'] ?: url('catalog')) ?>"><?= h($CONFIG['strip_link_text']) ?></a><?php endif; ?>
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
        <?php $here_args = $_GET; unset($here_args['p'], $here_args['id'], $here_args['s'], $here_args['c'], $here_args['g'], $here_args['pg']);
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
    <?php if(info_page('wholesale')): ?><a href="<?= h(url('page', ['pg'=>'wholesale'])) ?>" class="<?= $page==='page'&&($info['slug'] ?? '')==='wholesale'?'on':'' ?>">Wholesale</a><?php endif; ?>
    <a href="<?= url('catalog') ?>" class="<?= $page==='catalog'&&!$cat?'on':'' ?>">All products</a>
    <?php foreach($CATEGORIES as $k=>$c): ?>
      <a href="<?= url('catalog',['cat'=>$k]) ?>" class="<?= $cat===$k?'on':'' ?>"><?= h($c['label']) ?></a>
    <?php endforeach; ?>
    <a href="<?= url('sets') ?>" class="<?= in_array($page, ['sets','series','set'], true)?'on':'' ?>">Sets</a>
    <?php if($GUIDES): ?><a href="<?= url('guides') ?>" class="<?= in_array($page, ['guides','guide'], true)?'on':'' ?>">Guides</a><?php endif; ?>
    <a href="<?= url('shipping') ?>" class="<?= $page==='shipping'?'on':'' ?>">Shipping &amp; Returns</a>
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
      <div class="eyebrow"><span>Japan direct</span><span>Sealed &amp; authentic</span><span>Ships to Australia</span><span>Wholesale MOQs</span></div>
      <h1><?= h($CONFIG['hero_title']) ?></h1>
      <p class="lede"><?= h($CONFIG['hero_lede']) ?></p>
      <div class="hero-cta">
        <a class="btn" href="<?= url('catalog') ?>">Shop Japanese Pokémon cards</a>
        <a class="btn g" href="<?= h(info_page('wholesale') ? url('page', ['pg'=>'wholesale']) : url('how')) ?>"><?= info_page('wholesale') ? 'Wholesale terms' : 'How ordering works' ?></a>
      </div>
      <div class="stats">
        <div><strong><?= count($in_stock) ?></strong>SKUs in stock</div>
        <div><strong><?= money($MIN_ORDER) ?></strong>Minimum order, incl. shipping</div>
        <?php if(count($COUNTRIES) === 1 && free_ship_usd($STORE)): ?><div><strong><?= money_whole(free_ship_usd($STORE)) ?></strong>Free shipping from</div>
        <?php else: ?><div><strong><?= count($COUNTRIES) ?></strong>Countries served</div><?php endif; ?>
        <div><strong><?= (int)$CONFIG['hold_hours'] ?>h</strong>Stock held on order</div>
      </div>
    </div>
    <div class="heroart<?= photos('hero') ? '' : ' light' ?>">
      <?php $hp = photos('hero');
      if($hp): ?><?= img_tag($hp[0], 'Sealed Japanese Pokémon booster boxes ready to ship from Japan', '(max-width:960px) 100vw, 600px', true) ?>
      <?php elseif(is_file(FK_ROOT.'/'.SITE_HERO)): ?><a href="<?= h(url('product', ['id'=>'30th-celebration-elite-trainer-box'])) ?>" class="heroimg"><?= img_tag(SITE_HERO, 'Pokémon TCG 30th Celebration Elite Trainer Box', '(max-width:960px) 100vw, 600px', true) ?></a>
      <?php else: ?><div class="ph"><span><?= h($CONFIG['kanji']) ?></span><span>Japanese Pokémon cards · shipped from Japan</span></div><?php endif; ?>
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

  <?php $pop = array_filter($COLLECTIONS, fn($c)=>collection_products($c)); $s151 = set_by_slug('151'); ?>
  <?php if($pop || $s151): ?>
  <section style="padding-top:0"><div class="wrap">
    <div class="chips" aria-label="Popular">
      <?php foreach($pop as $c): ?><a href="<?= h(url('collection', ['c'=>$c['slug']])) ?>"><?= h($c['title']) ?></a><?php endforeach; ?>
      <?php if($s151): ?><a href="<?= h(url('set', ['s'=>'151'])) ?>">151 Pokémon cards</a><?php endif; ?>
      <?php foreach($SERIES as $k=>$sr): if(series_sets($k)): ?><a href="<?= h(url('series', ['s'=>$sr['slug']])) ?>"><?= h($sr['name']) ?> sets</a><?php endif; endforeach; ?>
    </div>
  </div></section>
  <?php endif; ?>

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

  <?php $f30 = set_by_slug('30th-celebration'); $etb30 = product('30th-celebration-elite-trainer-box');
  if($f30 && is_file(FK_ROOT.'/assets/site/pokemon-30th-celebration-booster-pack.webp')): ?>
  <section class="feature"><div class="wrap fgrid">
    <a class="fpack" href="<?= h(url('set', ['s'=>$f30['slug']])) ?>"><?= img_tag('assets/site/pokemon-30th-celebration-booster-pack.webp', 'Pokémon TCG 30th Celebration booster pack with Pikachu, Mew and Mewtwo', '(max-width:860px) 55vw, 260px') ?></a>
    <div class="ftext">
      <span class="kicker">30th anniversary</span>
      <h2>Pokémon TCG: 30th Celebration</h2>
      <p>Thirty years of Pokémon in one set. Mewtwo ex and Mew ex lead the way, joined by Umbreon ex, Salamence ex and Greninja ex — and every booster pack holds a Pikachu, with 30 different Pikachu rare cards to collect.</p>
      <div class="hero-cta" style="margin:22px 0 0">
        <a class="btn gold" href="<?= h(url('set', ['s'=>$f30['slug']])) ?>">Shop 30th Celebration</a>
        <?php if($etb30): ?><a class="btn g" href="<?= h(url('product', ['id'=>$etb30['id']])) ?>">Elite Trainer Box</a><?php endif; ?>
      </div>
    </div>
    <figure class="fbox">
      <?= img_tag('assets/site/pokemon-30th-celebration-elite-trainer-box-contents.webp', 'What’s inside the Pokémon TCG 30th Celebration Elite Trainer Box', '(max-width:860px) 100vw, 420px') ?>
      <figcaption>Inside the Elite Trainer Box: 9 booster packs, a full-art Nidorina promo, 65 sleeves, dice and more.</figcaption>
    </figure>
  </div></section>
  <?php endif; ?>

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
      <p style="margin-top:22px"><a class="btn gold" href="<?= url('how') ?>">How ordering works</a>
        <?php if(info_page('about')): ?><a class="btn g" href="<?= h(url('page', ['pg'=>'about'])) ?>" style="margin-left:8px">About <?= h($CONFIG['brand']) ?></a><?php endif; ?></p>
    </div>
    <div class="spec">
      <div><span>Sourcing</span><span>Japanese distribution</span></div>
      <div><span>Minimum order, sealed</span><span>6 units</span></div>
      <div><span>Minimum order, singles</span><span>1 unit</span></div>
      <div><span>Minimum order value</span><span><?= money($MIN_ORDER) ?> incl. shipping</span></div>
      <div><span>Stock hold on order</span><span><?= (int)$CONFIG['hold_hours'] ?> hours</span></div>
      <div><span>Dispatch after payment</span><span>Within <?= (int)$CONFIG['hold_hours'] ?> hours</span></div>
      <?php if(free_ship_usd($STORE)): ?><div><span>Free shipping</span><span>Orders over <?= money_whole(free_ship_usd($STORE)) ?></span></div><?php endif; ?>
      <?php $nbtc = count(array_filter($PAYMENTS, 'btc_method')); if($nbtc): ?><div><span>Pay by</span><span>Bitcoin, on the site<?= count($PAYMENTS) > $nbtc ? ' · or '.(count($PAYMENTS) - $nbtc).' other ways' : '' ?></span></div><?php endif; ?>
      <div><span>Carriers</span><span>EMS · DHL · FedEx</span></div>
      <?php $SM = ship_methods($STORE); ?><div><span>Delivery</span><span><?= h($SM['standard']['label'].' '.$SM['standard']['days'].' · '.$SM['express']['label'].' '.$SM['express']['days']) ?></span></div>
    </div>
  </div></section>

  <section><div class="wrap">
    <div class="sechead"><div><h2>Four steps from cart to courier</h2></div></div>
    <?php steps_block($CONFIG); ?>
  </div></section>

  <?php if($GUIDES): ?>
  <section style="padding-top:0"><div class="wrap">
    <div class="sechead"><div><h2>Pokémon card guides</h2>
      <p>The most expensive and rarest cards, live prices, where to buy, and how to play.</p></div>
      <a href="<?= url('guides') ?>">All <?= count($GUIDES) ?> guides →</a></div>
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
    <?php if(!$filtered) guide_links(PAGE_GUIDES[$cinfo ? 'cat:'.$cat : 'shop'] ?? [], $cinfo ? 'Guides for '.strtolower($cinfo['label']) : 'Pokémon card guides'); ?>
  </div></section>

<?php elseif($page==='product'):
  $ph = photos($prod['id']); $base = unit_price($prod,$prod['moq']); $best = end($prod['ladder']);
  [$summary, $more] = desc_parts($prod['desc']);
  $sser = $pset ? ($SERIES[$pset['series']] ?? null) : null;
  $moq_tier = 0; foreach($prod['ladder'] as $i=>$t){ if($t[0] <= $prod['moq']) $moq_tier = $i; } ?>
  <div class="wrap pdp">
    <div>
      <div class="gal-main">
        <?php if($ph): ?><?= str_replace('<img ', '<img id="galMain" ', img_tag($ph[0], $prod['name'].' — Japanese Pokémon TCG', '(max-width:900px) 100vw, 620px', true)) ?>
        <?php else: ?><div class="ph"><span><?= h($CONFIG['kanji']) ?></span><span><?= h($prod['set'] ?: $CATEGORIES[$prod['cat']]['label']) ?></span></div><?php endif; ?>
      </div>
      <?php if(count($ph)>1): ?>
      <div class="gal-thumbs">
        <?php foreach($ph as $i=>$src): ?>
          <button type="button" aria-current="<?= $i===0?'true':'false' ?>" onclick="galPick(this,'<?= h($src.'?v='.@filemtime(FK_ROOT.'/'.$src)) ?>')" aria-label="View photo <?= $i+1 ?>">
            <img src="<?= h(thumb($src, 160)) ?>" width="72" height="72" alt="<?= h($prod['name']) ?> photo <?= $i+1 ?>" loading="lazy"></button>
        <?php endforeach; ?>
      </div>
      <?php endif; ?>
    </div>
    <div>
      <h1><?= h($prod['name']) ?></h1>
      <div class="sub"><?= h(implode(' · ', array_filter([$prod['set'], $CATEGORIES[$prod['cat']]['label'], $prod['cond'] ?? 'Sealed',
        $prod['status']==='preorder' ? 'Releases '.$prod['release'] : status_label(PRODUCT_STATUSES, $prod['status'])]))) ?></div>
      <?php if($summary !== ''): ?><p class="summary-line"><?= rich_inline($summary) ?></p><?php endif; ?>

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
        <div class="sm">per unit at MOQ <?= h($prod['moq']) ?><?php if(count($prod['ladder']) > 1 && $best[1] < $base): ?> · down to <?= money($best[1]) ?> at <?= h($best[0]) ?>+<?php endif; ?></div>
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
          <span><?= $prod['step']>1 ? 'Sold in '.(int)$prod['step'].'s' : 'Sold individually' ?></span>
          <span>Held <?= (int)$CONFIG['hold_hours'] ?>h on order</span>
          <span>Ships from Japan</span>
        </div>
      </div>
    </div>
  </div>

  <section class="top"><div class="wrap pinfo">
    <div class="panel">
      <h2>About this product</h2>
      <div class="prose"><?= $more !== '' ? rich($more) : '<p>'.rich_inline($summary).'</p>' ?></div>
    </div>
    <div style="display:grid;gap:16px;align-content:start">
      <div class="panel"><h2>Product details</h2>
        <dl class="specs">
          <?php if($pset): ?><dt>Set</dt><dd><a href="<?= h(url('set', ['s'=>$pset['slug']])) ?>"><?= h($pset['name']) ?></a></dd><?php endif; ?>
          <?php if($pset && $pset['code'] !== ''): ?><dt>Set code</dt><dd><?= h($pset['code']) ?></dd><?php endif; ?>
          <?php if($sser): ?><dt>Series</dt><dd><a href="<?= h(url('series', ['s'=>$sser['slug']])) ?>"><?= h($sser['name']) ?></a></dd><?php endif; ?>
          <dt>Product type</dt><dd><a href="<?= h(url('catalog', ['cat'=>$prod['cat']])) ?>"><?= h($CATEGORIES[$prod['cat']]['label']) ?></a></dd>
          <dt>Condition</dt><dd><?= h($prod['cond'] ?? 'Sealed') ?></dd>
          <dt>Availability</dt><dd><?= h($prod['status']==='preorder' ? 'Preorder · releases '.$prod['release'] : status_label(PRODUCT_STATUSES, $prod['status'])) ?></dd>
          <dt>Minimum order</dt><dd><?= (int)$prod['moq'] ?><?= $prod['step'] > 1 ? ', then in '.(int)$prod['step'].'s' : '' ?></dd>
          <?php if($prod['sku'] !== ''): ?><dt>SKU</dt><dd><?= h($prod['sku']) ?></dd><?php endif; ?>
          <dt>Ships from</dt><dd>Japan, tracked</dd>
        </dl>
      </div>
      <div class="panel"><h2>Shipping &amp; payment</h2>
        <div class="prose" style="font-size:14.5px">
          <?php $SM = ship_methods($STORE); $one = (float)($prod['weight'] ?? 0) * $prod['moq']; ?>
          <p>Shipped from Japan with tracking: <b><?= h($SM['standard']['label']) ?></b> <?= h($SM['standard']['days']) ?> or <b><?= h($SM['express']['label']) ?></b> <?= h($SM['express']['days']) ?>.
            Priced by weight — <?= (int)$prod['moq'] ?> of these to <?= h($COUNTRIES[$HOME_CC] ?? $HOME_CC) ?> ship for <?= money(shipping_usd($STORE, $HOME_CC, $one, 'standard')) ?> Standard or <?= money(shipping_usd($STORE, $HOME_CC, $one, 'express')) ?> Express.
            <?php if(free_ship_usd($STORE)): ?><b>Free <?= h($SM['standard']['label']) ?> shipping on orders over <?= money_whole(free_ship_usd($STORE)) ?>.</b><?php endif; ?>
            Orders start at <?= money($MIN_ORDER) ?> including shipping.</p>
          <p><?php if(array_filter($PAYMENTS, 'btc_method')): ?>Pay with Bitcoin straight after you order, or choose another method and we send the details within <?= (int)$CONFIG['reply_hours'] ?> hours.<?php else: ?>We send payment details for your chosen method within <?= (int)$CONFIG['reply_hours'] ?> hours.<?php endif; ?>
            <a href="<?= url('shipping') ?>">Shipping &amp; Returns</a> · <a href="<?= url('payment') ?>">Payment methods</a> · <a href="<?= url('how') ?>">How ordering works</a><?php if(info_page('wholesale')): ?> · <a href="<?= h(url('page', ['pg'=>'wholesale'])) ?>">Wholesale terms</a><?php endif; ?> · <a href="<?= url('faq') ?>">FAQ</a> · <a href="<?= url('contact') ?>">Contact us</a></p>
        </div>
      </div>
      <?php $pg = guides_for($prod['cat']); if($pg): ?>
      <div class="panel"><h2>Helpful guides</h2>
        <div class="plinks"><?php foreach($pg as $g): ?><a href="<?= h(url('guide', ['g'=>$g['slug']])) ?>"><?= h(guide_anchor($g)) ?> →</a><?php endforeach; ?></div>
      </div>
      <?php endif; ?>
    </div>
  </div></section>

  <?php $sibs = $pset ? array_values(array_filter(set_products($pset['name']), fn($x)=>$x['id'] !== $prod['id'])) : [];
  if($sibs): ?>
  <section><div class="wrap">
    <div class="sechead"><div><h2>More from <?= h($pset['name']) ?></h2></div>
      <a href="<?= h(url('set', ['s'=>$pset['slug']])) ?>">All <?= h($pset['name']) ?> →</a></div>
    <div class="grid"><?php foreach(array_slice($sibs, 0, 4) as $p) include_card($p); ?></div>
  </div></section>
  <?php endif; ?>
  <section<?= $sibs ? ' style="padding-top:0"' : '' ?>><div class="wrap">
    <div class="sechead"><div><h2>More <?= h($CATEGORIES[$prod['cat']]['label']) ?></h2></div>
      <a href="<?= url('catalog',['cat'=>$prod['cat']]) ?>">See all →</a></div>
    <div class="grid">
      <?php $rel = array_slice(array_values(array_filter($PRODUCTS, fn($x)=>$x['cat']===$prod['cat'] && $x['id']!==$prod['id'] && !in_array($x, $sibs, true))),0,4);
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
            <?php free_ship_meter(cart_total()); ?>
            <div style="font-size:13.5px;color:var(--muted);margin-bottom:10px">Shipping is calculated at checkout. Minimum order <?= money($MIN_ORDER) ?> including shipping.</div>
            <a class="btn" href="<?= url('checkout') ?>">Continue to checkout</a>
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
      <p>Tell us where it ships and how you want to pay. <?= array_filter($PAYMENTS, 'btc_method') ? 'Paying by Bitcoin? You pay on the next page, straight from your wallet. For other methods we' : 'We' ?> send payment details and your invoice by email or text after you place the order.</p></div></div>

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
          <div class="fld"><label for="address2">Unit, suite or level (optional)</label>
            <input id="address2" name="address2" value="<?= h($f['address2']??'') ?>"></div>
          <div class="two">
            <div class="fld"><label for="city">Suburb / city</label>
              <input id="city" name="city" required value="<?= h($f['city']??'') ?>"></div>
            <div class="fld"><label for="region">State / territory</label>
              <input id="region" name="region" value="<?= h($f['region']??'') ?>"></div>
          </div>
          <div class="fld" style="max-width:260px"><label for="postcode">Postcode</label>
            <input id="postcode" name="postcode" value="<?= h($f['postcode']??'') ?>"></div>
        </fieldset>

        <fieldset>
          <legend>Delivery</legend>
          <div class="pay" id="shipList">
            <?php foreach(ship_methods($STORE) as $mk=>$mm): ?>
              <label>
                <input type="radio" name="ship_method" value="<?= h($mk) ?>" <?= ($f['ship_method'] ?? 'standard')===$mk?'checked':'' ?>>
                <span style="flex:1"><span class="t"><?= h($mm['label']) ?></span><br><span class="n"><?= h($mm['days']) ?>, tracked</span></span>
                <span class="t" data-ship-price="<?= h($mk) ?>"><?php if(!empty($f['country']) && isset($COUNTRIES[$f['country']])){ $sv = shipping_usd($STORE, $f['country'], cart_weight(), $mk, cart_total()); echo $sv > 0 ? money($sv) : 'Free'; } ?></span>
              </label>
            <?php endforeach; ?>
          </div>
          <p class="n" style="font-size:12.5px;color:var(--muted);margin-top:10px">Priced by the weight of your order (<?= h(rtrim(rtrim(number_format(cart_weight(), 2), '0'), '.')) ?> kg). Choose your country to see prices.</p>
          <?php free_ship_meter(cart_total(), true); ?>
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
            <b>How payment works.</b>
            <?php if(array_filter($PAYMENTS, 'btc_method')): ?>
            <b>Bitcoin:</b> place the order and pay on the next page. You get the exact amount and a QR code for your wallet, and a receipt by email as soon as your payment reaches the blockchain.
            <b>Other methods:</b> we send the details to your email and phone within <?= (int)$CONFIG['reply_hours'] ?> hours with your invoice, and hold your stock for <?= (int)$CONFIG['hold_hours'] ?> hours.
            <?php else: ?>
            Select your method and place the order. We will send the payment details for that method to your email and phone within <?= (int)$CONFIG['reply_hours'] ?> hours, together with your invoice. Your stock is reserved for <?= (int)$CONFIG['hold_hours'] ?> hours in the meantime. Quote your order reference on the payment so we can match it to your order.
            <?php endif; ?>
            We never ask for card details, passwords or wallet keys.
          </div>
          <div class="fld"><label for="notes">Order notes (optional)</label>
            <textarea id="notes" name="notes" placeholder="Your ABN (for your invoice), delivery instructions, preferred carrier, anything else we should know."><?= h($f['notes']??'') ?></textarea></div>
          <label class="agree">
            <input type="checkbox" name="agree" value="1" <?= !empty($_POST['agree'])?'checked':'' ?>>
            <span>I agree to the <a href="<?= h(url('page', ['pg'=>'terms'])) ?>" target="_blank">terms of sale</a> and the
            <a href="<?= h(url('shipping')) ?>#returns" target="_blank">shipping &amp; returns policy</a>.</span>
          </label>
          <div class="minwarn" id="minWarn" hidden></div>
          <button class="btn wide" id="placeBtn" type="submit" style="margin-top:14px" data-btc-label="Place order and pay with Bitcoin">Place order</button>
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
          <div class="sl"><span style="color:var(--muted)" id="shipLabel">Shipping</span><span id="shipCost" style="color:var(--muted)">Select your country</span></div>
          <div class="tot"><span>Order total</span><span id="grandTotal"><?= money(cart_total()) ?></span></div>
          <p style="font-size:12.5px;color:var(--muted);margin-top:8px">Minimum order <?= money($MIN_ORDER) ?> including shipping.</p>
          <?php
          /* per-country shipping for this cart, so the summary updates as the country changes */
          $cm = $CURRENCIES[cur_code()]; $kg = cart_weight(); $ship_by = [];
          foreach($COUNTRIES as $code=>$nm) foreach(SHIP_METHODS as $mk) $ship_by[$code][$mk] = shipping_usd($STORE, $code, $kg, $mk, cart_total());
          $labels = array_map(fn($m)=>$m['label'], ship_methods($STORE));
          $co_data = ['ship'=>$ship_by, 'labels'=>$labels, 'goods'=>cart_total(), 'min'=>$MIN_ORDER,
                      'btc'=>array_keys(array_filter($PAYMENTS, 'btc_method')),
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
        <div class="sl" style="border:none;padding:4px 0 0"><span>Shipping<?= !empty($o['ship_label']) ? ' · '.h($o['ship_label']) : '' ?></span><span><?= h($o['shipping']) ?></span></div>
        <div class="sl" style="border:none;padding:4px 0 0"><span>Order total</span><b><?= h($o['total']) ?> <?= h($o['currency']) ?></b></div>
        <div class="sl" style="border:none;padding:4px 0 0"><span>Shipping to</span><span><?= h($o['city']) ?>, <?= h($o['country_name']) ?></span></div>
        <p style="font-size:13.5px;color:var(--muted);margin-top:16px">
          Nothing heard within <?= (int)$CONFIG['reply_hours'] ?> hours? Check your spam folder, then email
          <a href="mailto:<?= h($CONFIG['email']) ?>"><?= h($CONFIG['email']) ?></a> quoting <?= h($o['ref']) ?>.</p>
      </div>
      <p style="margin-top:24px"><a class="btn g" href="<?= url('catalog') ?>">Continue shopping</a></p>
    <?php endif; ?>
  </div></section>

<?php elseif($page==='pay'): $o = $pay; $b = $o['btc']; $st = btc_state($o); $key = order_key($o['ref']);
  $cancelled = ($o['status'] ?? '') === 'cancelled'; $need = btc_settings()['confs']; $conf = (int)($b['confirmations'] ?? 0);
  $open = !$cancelled && in_array($st, ['awaiting','reported'], true); $uri = btc_uri($o);
  $heads = ['awaiting'=>'Pay with Bitcoin', 'reported'=>'Checking your payment', 'seen'=>'Payment received', 'confirmed'=>'Paid — thank you', 'short'=>'Payment received']; ?>
  <section style="padding-top:22px"><div class="wrap">
    <div class="payhead">
      <div class="kick">Order <b><?= h($o['ref']) ?></b> · <?= h($o['time']) ?></div>
      <h1><?= $cancelled ? 'Order cancelled' : h($heads[$st]) ?></h1>
      <?php if($open): ?><p class="lede">Your order is placed and your stock is reserved. Pay from any Bitcoin wallet: scan the code, or copy the amount and address.</p>
      <?php elseif(!$cancelled): ?><p class="lede">We’ve emailed your receipt to <b><?= h($o['email']) ?></b>. <?= $st === 'confirmed' ? 'We’re packing your order and will email your tracking number when it ships.' : 'Your payment is on the blockchain; this page updates when it confirms.' ?></p><?php endif; ?>
    </div>

    <div class="paygrid">
      <div class="paybox" id="payBox" data-state="<?= h($st) ?>" data-quoted="<?= (int)($b['quoted'] ?? 0) ?>"
           data-status="<?= h(url('paystatus', ['ref'=>$o['ref'], 'k'=>$key])) ?>">
      <?php if($cancelled): ?>
        <p>This order has been cancelled, so please don’t send a payment for it. If that’s a mistake, email <a href="mailto:<?= h($CONFIG['email']) ?>"><?= h($CONFIG['email']) ?></a>.</p>
      <?php elseif($open): ?>
        <div class="payrow">
          <div class="qrcol">
            <div class="qr" id="qr" data-uri="<?= h($uri) ?>"><span class="qrph">QR code</span></div>
            <a class="btn wide gold" href="<?= h($uri) ?>">Open in wallet app</a>
          </div>
          <div class="pf">
            <?php if(!empty($b['sats'])): ?>
              <div class="l">Send exactly</div>
              <div class="v amt"><span><?= btc_amount($b['sats']) ?></span> <small>BTC</small>
                <button type="button" class="copy" data-copy="<?= btc_amount($b['sats']) ?>">Copy</button></div>
              <div class="n">Order total $<?= number_format($o['total_usd'], 2) ?> USD · 1 BTC = $<?= number_format($b['rate'], 2) ?> (<?= h($b['rate_source']) ?>)</div>
            <?php else: ?>
              <div class="l">Amount</div>
              <div class="v amt">$<?= number_format($o['total_usd'], 2) ?> <small>USD in BTC</small></div>
              <div class="n">We couldn’t get the Bitcoin price just now. This page tries again every minute, so please wait for the exact BTC amount before paying.</div>
            <?php endif; ?>
            <div class="l" style="margin-top:18px">To this Bitcoin address</div>
            <div class="v addr"><code><?= h($b['address']) ?></code>
              <button type="button" class="copy" data-copy="<?= h($b['address']) ?>">Copy</button></div>
            <?php if(!empty($b['sats'])): ?>
              <div class="hold">Amount held for <b id="countdown" data-expires="<?= (int)$b['expires'] ?>"><?= gmdate('i:s', max(0, $b['expires'] - time())) ?></b>. After that it updates to the current rate.</div>
            <?php endif; ?>
            <div class="watch"><i class="pulse"></i> <?= $st === 'reported' ? 'Checking transaction '.h(substr($b['reported'], 0, 12)).'… — this page updates by itself.' : 'Watching the blockchain for your payment. This page updates by itself.' ?></div>
          </div>
        </div>
        <ul class="paytips">
          <li><b>Send the exact amount in one payment.</b> If your exchange takes its withdrawal fee from the amount, add the fee on top.</li>
          <li><b>Bitcoin network only.</b> Not Lightning, and not wrapped “BTC” on other networks such as BEP-20 or ERC-20.</li>
          <li><b>Come back any time.</b> The link to this page is in your order email, <?= h($o['email']) ?>.</li>
        </ul>
        <details class="txform"<?= $st === 'reported' ? ' open' : '' ?>><summary>Paid already? Add your transaction ID</summary>
          <form method="post" action="index.php">
            <input type="hidden" name="action" value="btc_txid"><input type="hidden" name="ref" value="<?= h($o['ref']) ?>"><input type="hidden" name="k" value="<?= h($key) ?>">
            <label for="txid">Transaction ID (or a link to it on a block explorer)</label>
            <div class="txrow"><input id="txid" name="txid" autocomplete="off" spellcheck="false" placeholder="e.g. 4a5e1e4baab89f3a32518a88c31bc87f…" required>
              <button class="btn" type="submit">Check payment</button></div>
          </form>
        </details>
      <?php else: $short = $st === 'short'; ?>
        <ol class="timeline">
          <li class="ok"><b>Order placed</b><span><?= h($o['time']) ?></span></li>
          <li class="ok"><b>Payment sent</b><span><?= btc_amount($b['paid_sats']) ?> BTC<?= $short ? ' — less than the '.btc_amount($b['expected_sats']).' BTC due' : '' ?></span></li>
          <li class="<?= $conf >= $need ? 'ok' : 'now' ?>"><b>Confirmed on the blockchain</b><span><?= $conf >= $need ? 'Confirmed' : 'Waiting for confirmation, usually 10–60 minutes' ?></span></li>
          <li class="<?= ($o['status'] ?? '') === 'shipped' ? 'ok' : ($conf >= $need && !$short ? 'now' : '') ?>"><b>Shipped from Japan</b><span><?= ($o['status'] ?? '') === 'shipped' ? 'On its way — tracking is in your email' : 'Within '.(int)$CONFIG['hold_hours'].' hours of confirmation, with tracking' ?></span></li>
        </ol>
        <?php if($short): ?><div class="errs" style="margin-top:16px">Your payment was less than the amount due. Please email <a href="mailto:<?= h($CONFIG['email']) ?>"><?= h($CONFIG['email']) ?></a> and we’ll sort out the difference.</div><?php endif; ?>
        <div class="txbox"><div class="l">Transaction</div><code><?= h($b['txid']) ?></code>
          <a href="<?= h(btc_tx_url($b['txid'])) ?>" target="_blank" rel="noopener">Track it on mempool.space ↗</a></div>
      <?php endif; ?>
      </div>

      <aside class="summary">
        <h3>Your order</h3>
        <?php foreach($o['lines'] as $l): ?>
          <div class="sl"><span><?= h($l['name']) ?><br><span class="q"><?= (int)$l['qty'] ?> × <?= h($l['unit']) ?></span></span><span><?= h($l['total']) ?></span></div>
        <?php endforeach; ?>
        <div class="sl"><span style="color:var(--muted)">Goods</span><span><?= h($o['goods']) ?></span></div>
        <div class="sl"><span style="color:var(--muted)">Shipping · <?= h($o['ship_label']) ?></span><span><?= !empty($o['free_shipping']) && (float)$o['shipping_usd'] == 0 ? 'Free' : h($o['shipping']) ?></span></div>
        <div class="tot"><span>Order total</span><span><?= h($o['total']) ?></span></div>
        <p class="n">Ships to <?= h($o['city']) ?>, <?= h($o['country_name']) ?>.<?= $o['currency'] !== 'USD' ? ' The BTC amount is worked out from the US-dollar total, US$'.number_format($o['total_usd'], 2).'.' : '' ?></p>
        <p class="n">Questions? <a href="mailto:<?= h($CONFIG['email']) ?>?subject=<?= rawurlencode('Order '.$o['ref']) ?>"><?= h($CONFIG['email']) ?></a></p>
      </aside>
    </div>
  </div></section>
  <?php if($open): ?><script src="assets/qrcode.min.js?v=<?= @filemtime(FK_ROOT.'/assets/qrcode.min.js') ?>" defer></script><?php endif; ?>

<?php elseif($page==='how'): ?>
  <section style="padding-top:22px"><div class="wrap">
    <div class="sechead"><div><h1><?= h($h1) ?></h1>
      <p>Four steps from cart to courier, with no account approval: minimum order quantities, case multiples and quantity breaks are on every listing. Pay by Bitcoin straight from your wallet, or settle an invoice from your own bank or payment app.<?php if(info_page('wholesale')): ?> See our <a href="<?= h(url('page', ['pg'=>'wholesale'])) ?>">wholesale terms</a>.<?php endif; ?></p></div></div>
    <?php steps_block($CONFIG); ?>
    <div style="margin-top:40px;display:grid;grid-template-columns:repeat(3,1fr);gap:16px" class="cats">
      <a href="<?= url('payment') ?>"><h3>Payment methods</h3><p>What we accept, by country.</p></a>
      <a href="<?= url('shipping') ?>"><h3>Shipping &amp; Returns</h3><p>Rates, timings, duty, returns and refunds.</p></a>
      <a href="<?= url('faq') ?>"><h3>Wholesale FAQ</h3><p>MOQs, preorders, returns and more.</p></a>
    </div>
  </div></section>

<?php elseif($page==='payment'): ?>
  <section style="padding-top:22px"><div class="wrap" style="max-width:820px">
    <div class="sechead"><div><h1><?= h($h1) ?></h1>
      <p>Choose your method at checkout. <?= array_filter($PAYMENTS, 'btc_method') ? 'Bitcoin is paid on your order page the moment you order. For other methods, we' : 'We' ?> send the details to your email and phone within <?= (int)$CONFIG['reply_hours'] ?> hours, together with your invoice.</p></div></div>
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
    <?php if(array_filter($PAYMENTS, 'btc_method')): ?>
    <h2 class="sub2">Paying with Bitcoin</h2>
    <div class="prose">
      <ol>
        <li><b>Place your order</b> and choose Bitcoin at checkout.</li>
        <li><b>Scan the QR code</b> on your order page with any Bitcoin wallet, or copy the exact amount and our address. The amount is held for <?= (int)btc_settings()['minutes'] ?> minutes at the current rate.</li>
        <li><b>Get your receipt.</b> The page spots your payment on the blockchain and we email a receipt with a link to follow it.</li>
        <li><b>We ship</b> within <?= (int)$CONFIG['hold_hours'] ?> hours of it confirming, usually 10–60 minutes after you pay.</li>
      </ol>
      <p>Always check that the address on your order page is <code><?= h(btc_settings()['address']) ?></code>. We never send a different Bitcoin address by email or chat.</p>
    </div>
    <?php endif; ?>
    <div class="notice" style="margin-top:22px">
      <b>We never ask for card details, passwords or wallet keys.</b> For methods other than Bitcoin, you place the order and
      we send the payment details, holding your stock for <?= (int)$CONFIG['hold_hours'] ?> hours in the meantime. Always check
      payment details against the email we send from <?= h($CONFIG['email']) ?> and quote your order reference.
    </div>
    <p style="font-size:14px;color:var(--ink2);margin-top:18px">Invoices are issued in the currency you had selected at checkout. Shipping is calculated at checkout. Prices exclude GST, import duty and customs clearance fees.</p>
  </div></section>

<?php elseif($page==='shipping'):
  $SM = ship_methods($STORE); $fs = free_ship_usd($STORE);
  $policy = str_replace("\r", '', (string)($CONFIG['shipping_policy'] ?? ''));
  $parts = preg_split('/^[ \t]*\{rates\}[ \t]*$/m', $policy, 2);
  preg_match_all('/^##\s+(.+)$/m', fill($policy), $heads); ?>
  <section style="padding-top:22px"><div class="wrap">
    <div class="srhead">
      <h1><?= h($h1) ?></h1>
      <p class="lede">Shipped from Japan with tracking, packed with care, and clearly priced before you pay.</p>
    </div>
    <div class="srcards">
      <?php if($fs): ?><div class="k1"><span class="ic"><svg viewBox="0 0 24 24" aria-hidden="true"><path fill="currentColor" d="M3 6.5A1.5 1.5 0 0 1 4.5 5h9A1.5 1.5 0 0 1 15 6.5V8h2.6a1.5 1.5 0 0 1 1.2.6l2.4 3.2c.2.26.3.58.3.9V16a1.5 1.5 0 0 1-1.5 1.5h-.6a2.75 2.75 0 0 1-5.3 0H9.9a2.75 2.75 0 0 1-5.3 0h-.1A1.5 1.5 0 0 1 3 16V6.5Zm12 3V13h4.5l-1.9-2.5a1.5 1.5 0 0 0-1.2-.6H15ZM7.25 18.25a1 1 0 1 0 0-2 1 1 0 0 0 0 2Zm9.4 0a1 1 0 1 0 0-2 1 1 0 0 0 0 2Z"/></svg></span><b>Free shipping</b><span>On orders over <?= money_whole($fs) ?></span></div><?php endif; ?>
      <div class="k2"><span class="ic"><svg viewBox="0 0 24 24" aria-hidden="true"><path fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" d="M12 7v5l3 2M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z"/></svg></span><b><?= h($SM['standard']['label']) ?></b><span><?= h($SM['standard']['days']) ?>, tracked</span></div>
      <div class="k3"><span class="ic"><svg viewBox="0 0 24 24" aria-hidden="true"><path fill="currentColor" d="M13.5 2 4 13.5h6.5L9.5 22 20 9.5h-6.6L13.5 2Z"/></svg></span><b><?= h($SM['express']['label']) ?></b><span><?= h($SM['express']['days']) ?>, tracked</span></div>
      <div class="k4"><span class="ic"><svg viewBox="0 0 24 24" aria-hidden="true"><path fill="none" stroke="currentColor" stroke-width="2" stroke-linejoin="round" d="M3.5 7.5 12 3l8.5 4.5v9L12 21l-8.5-4.5v-9ZM3.5 7.5 12 12m0 0 8.5-4.5M12 12v9"/></svg></span><b>Dispatched fast</b><span>Within <?= (int)$CONFIG['hold_hours'] ?> hours of payment</span></div>
    </div>
    <div class="srgrid">
      <?php if(count($heads[1]) >= 3): ?>
      <nav class="srtoc" aria-label="On this page"><b>On this page</b><ol>
        <?php foreach($heads[1] as $hd): ?><li><a href="<?= h(url('shipping')) ?>#<?= h(slugify($hd)) ?>"><?= h(preg_replace('/\[([^\]]+)\]\([^)]*\)|\*\*/', '$1', $hd)) ?></a></li><?php endforeach; ?>
      </ol></nav>
      <?php endif; ?>
      <article class="prose sr">
        <?= rich($parts[0]) ?>
        <?php if(count($parts) === 2): ship_rates_block(); echo rich($parts[1]); endif; ?>
        <div class="srcontact">
          <div><b>Still have a question?</b><span>We reply within <?= (int)$CONFIG['reply_hours'] ?> hours. See the <a href="<?= url('faq') ?>">FAQ</a> or <a href="<?= url('contact') ?>">contact us</a>.</span></div>
          <div class="row2">
            <a class="btn" href="mailto:<?= h($CONFIG['email']) ?>">Email <?= h($CONFIG['email']) ?></a>
            <?php if(trim($CONFIG['chat_code'] ?? '') !== ''): ?><button type="button" class="btn g" data-open-chat hidden>Chat with us</button><?php endif; ?>
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
    <?php $more = [['Shipping & Returns', url('shipping')], ['How ordering works', url('how')], ['Payment methods', url('payment')]];
    foreach(['wholesale'=>'Wholesale terms', 'about'=>'About '.$CONFIG['brand'], 'terms'=>'Terms of sale', 'privacy-policy'=>'Privacy policy'] as $pg_=>$lb_) if(info_page($pg_)) $more[] = [$lb_, url('page', ['pg'=>$pg_])];
    $more[] = ['Contact us', url('contact')];
    guide_links(PAGE_GUIDES['faq'], 'More answers', $more); ?>
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
    <?php guide_links(PAGE_GUIDES['series'], 'Guides to '.$series['name'].' cards'); ?>
  </div></section>

<?php elseif($page==='set'):
  $sp = set_products($set['name']);
  $others = isset($SERIES[$set['series']]) ? array_diff_key(series_sets($set['series']), [$set['name']=>true]) : []; ?>
  <section class="top"><div class="wrap">
    <div class="sechead"><div><h1><?= h($h1) ?></h1></div><span class="count"><?= count($sp) ?> product<?= count($sp)===1?'':'s' ?></span></div>
    <?php if(trim($set['intro']) !== ''): ?><div class="prose lead"><?= rich($set['intro']) ?></div><?php endif; ?>
    <div class="grid"><?php foreach($sp as $p) include_card($p); ?></div>
    <?php if($others): ?><h2 class="sub2">More <?= h($SERIES[$set['series']]['name']) ?> sets</h2><?php set_tiles($others); endif; ?>
    <?php guide_links(PAGE_GUIDES['set:'.$set['slug']] ?? PAGE_GUIDES['set'], 'Guides to '.$set['name'].' and more'); ?>
  </div></section>

<?php elseif($page==='collection'):
  $cp = collection_products($coll); ?>
  <section class="top"><div class="wrap">
    <div class="sechead"><div><h1><?= h($h1) ?></h1></div><span class="count"><?= count($cp) ?> product<?= count($cp)===1?'':'s' ?></span></div>
    <?php if(trim($coll['intro'] ?? '') !== ''): ?><div class="prose lead"><?= rich($coll['intro']) ?></div><?php endif; ?>
    <?php if($cp): ?><div class="grid"><?php foreach($cp as $p) include_card($p); ?></div>
    <?php else: ?><p class="empty">Nothing in stock here right now — see all <a href="<?= url('catalog', ['cat'=>'singles']) ?>">single cards</a>.</p><?php endif; ?>
    <?php guide_links(PAGE_GUIDES['coll:'.$coll['slug']] ?? PAGE_GUIDES['coll'], 'Helpful guides'); ?>
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
      <?php preg_match_all('/^##\s+(.+)$/m', str_replace("\r", '', $guide['body'] ?? ''), $heads);
      if(count($heads[1]) >= 3): ?>
      <nav class="toc" aria-label="In this guide"><b>In this guide</b><ol>
        <?php foreach($heads[1] as $hd): $plainhd = preg_replace('/\[([^\]]+)\]\([^)]*\)|\*\*/', '$1', $hd); ?>
          <li><a href="<?= h(url('guide', ['g'=>$guide['slug']])) ?>#<?= h(slugify($hd)) ?>"><?= h($plainhd) ?></a></li>
        <?php endforeach; ?>
      </ol></nav>
      <?php endif; ?>
      <div class="prose"><?php guide_body($guide['body'] ?? ''); ?></div>
      <div class="notice" style="margin-top:28px"><?= rich_inline(GUIDE_SHOP[GUIDE_GROUP[$guide['slug']] ?? 'buy']) ?></div>
    </article>
    <?php $more = guides_related($guide['slug']);
    if($more): ?><h2 class="sub2">Related guides</h2><?php guide_cards($more); endif; ?>
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
    <?php if(($info['slug'] ?? '') === 'wholesale') guide_links(PAGE_GUIDES['shop'], 'Guides for buyers'); ?>
  </div></section>
  <?php $qa = policy_questions($info['body'] ?? '');
  if($qa): ?><script type="application/ld+json"><?= json_encode(['@context'=>'https://schema.org','@type'=>'FAQPage','mainEntity'=>array_map(fn($x)=>
    ['@type'=>'Question','name'=>$x[0],'acceptedAnswer'=>['@type'=>'Answer','text'=>$x[1]]], $qa)], JSON_UNESCAPED_SLASHES|JSON_UNESCAPED_UNICODE|JSON_HEX_TAG) ?></script><?php endif; ?>

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
      <?php foreach(['where-to-buy-pokemon-cards'=>'Where to buy Pokémon cards', 'pokemon-card-price-checker'=>'Pokémon card price checker', 'most-expensive-pokemon-cards'=>'Most expensive Pokémon cards', 'pokemon-card-database'=>'Pokémon card database'] as $gs_=>$gl_):
        if(guide_by_slug($gs_)): ?><li><a href="<?= h(url('guide', ['g'=>$gs_])) ?>"><?= h($gl_) ?></a></li><?php endif; endforeach; ?>
      <?php if($GUIDES): ?><li><a href="<?= url('guides') ?>">All Pokémon card guides</a></li><?php endif; ?>
    </ul></div>
    <div><h4>Ordering</h4><ul>
      <?php if(info_page('wholesale')): ?><li><a href="<?= h(url('page', ['pg'=>'wholesale'])) ?>">Wholesale terms</a></li><?php endif; ?>
      <li><a href="<?= url('how') ?>">How it works</a></li>
      <li><a href="<?= url('payment') ?>">Payment methods</a></li>
      <li><a href="<?= url('shipping') ?>">Shipping &amp; Returns</a></li>
      <li><a href="<?= url('cart') ?>">Your order</a></li>
    </ul></div>
    <div><h4>Support</h4><ul>
      <li><a href="<?= url('faq') ?>">FAQ</a></li>
      <li><a href="<?= url('contact') ?>">Contact</a></li>
      <?php foreach($INFO_PAGES as $ip): if(($ip['slug'] ?? '') === 'wholesale') continue; ?><li><a href="<?= h(url('page', ['pg'=>$ip['slug']])) ?>"><?= h(fill($ip['title'])) ?></a></li><?php endforeach; ?>
      <li><a href="mailto:<?= h($CONFIG['email']) ?>"><?= h($CONFIG['email']) ?></a></li>
    </ul></div>
  </div>
  <div class="legal">
    <div>Shipped from Japan — import duties and taxes are the buyer's responsibility.</div>
    <div><?= h($CONFIG['legal_name']) ?> is an independent reseller of genuine product. We are not affiliated with, endorsed by or licensed by The Pokémon Company, Nintendo, Creatures Inc. or GAME FREAK Inc. All product names and trademarks are the property of their respective owners.</div>
    <div>© <?= date('Y') ?> <?= h($CONFIG['legal_name']) ?>.</div>
  </div>
</div></footer>

<?php if(($chat = trim($CONFIG['chat_code'] ?? '')) !== ''): ?>
<template id="chatCode"><?= $chat ?></template>
<?php if($who = ($pay ?: ($_SESSION['last_order'] ?? null))): /* lets the chat show which customer you're talking to */ ?>
<script>window.Tawk_API = window.Tawk_API || {}; Tawk_API.visitor = <?= json_encode(['name'=>$who['name'], 'email'=>$who['email']], JSON_HEX_TAG|JSON_HEX_AMP) ?>;</script>
<?php endif; endif; ?>
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
  return CO.sym + (usd * CO.rate).toLocaleString('en-AU', {minimumFractionDigits: CO.dec, maximumFractionDigits: CO.dec});
}
function fmtShip(usd){ return usd > 0 ? fmt(usd) : 'Free'; }
/* shipping, total and the minimum-order check follow the selected country; the server re-checks all of it */
function updateTotals(country){
  if(!window.CO) return;
  const rates = CO.ship[country], warn = document.getElementById('minWarn'), btn = document.getElementById('placeBtn');
  const cost = document.getElementById('shipCost');
  const picked = (document.querySelector('input[name=ship_method]:checked') || {}).value || 'standard';
  document.querySelectorAll('[data-ship-price]').forEach(el => { el.textContent = rates ? fmtShip(rates[el.dataset.shipPrice]) : ''; });
  document.getElementById('shipLabel').textContent = 'Shipping · ' + (CO.labels[picked] || '');
  if(rates === undefined){ cost.textContent = 'Select your country'; document.getElementById('grandTotal').textContent = fmt(CO.goods); warn.hidden = true; btn.disabled = false; return; }
  const ship = rates[picked];
  const total = Math.round((CO.goods + ship) * 100) / 100;
  cost.textContent = fmtShip(ship); cost.style.color = '';
  document.getElementById('grandTotal').textContent = fmt(total);
  const short = total < CO.min;
  warn.hidden = !short; btn.disabled = short;
  if(short) warn.textContent = 'The minimum order is ' + fmt(CO.min) + ' including shipping. Your total is ' + fmt(total) + ', so add ' + fmt(CO.min - total) + ' more to place this order.';
}
/* price checker: show rows that contain every word typed */
function pcFilter(v){
  const words = v.toLowerCase().split(/\s+/).filter(Boolean); let n = 0;
  document.querySelectorAll('#pcTable tbody tr').forEach(r => { const ok = words.every(w => r.dataset.q.includes(w)); r.hidden = !ok; if(ok) n++; });
  document.getElementById('pcCount').textContent = n + (n === 1 ? ' product' : ' products');
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
  b.textContent = CO.btc.includes(r.value) ? b.dataset.btcLabel : 'Place order';
}));

/* Bitcoin order page: QR code, copy buttons, the countdown on the quoted amount, and a quiet check for the payment */
(function(){
  const box = document.getElementById('payBox'); if(!box) return;
  document.querySelectorAll('.copy').forEach(b => b.addEventListener('click', () => {
    const v = b.dataset.copy, done = () => { b.textContent = 'Copied ✓'; setTimeout(() => b.textContent = 'Copy', 1800); };
    if(navigator.clipboard && window.isSecureContext) navigator.clipboard.writeText(v).then(done, () => prompt('Copy:', v)); else prompt('Copy:', v);
  }));
  const qrEl = document.getElementById('qr');
  const draw = () => {
    if(!qrEl || !window.qrcode) return;
    const qr = qrcode(0, 'M'); qr.addData(qrEl.dataset.uri); qr.make();
    const n = qr.getModuleCount(), q = 3, w = n + q * 2; let d = '';
    for(let r = 0; r < n; r++) for(let c = 0; c < n; c++) if(qr.isDark(r, c)) d += 'M' + (c + q) + ' ' + (r + q) + 'h1v1h-1z';
    qrEl.innerHTML = '<svg viewBox="0 0 ' + w + ' ' + w + '" role="img" aria-label="QR code with our Bitcoin address and the amount" shape-rendering="crispEdges">'
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
      else cd.textContent = 'updating…';
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
        <?= img_tag($ph[0], $p['name'].' — Japanese Pokémon TCG') ?>
      <?php else: ?>
        <div class="ph"><span><?= h($CONFIG['kanji']) ?></span><span><?= h($p['set']) ?></span></div>
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
/* Shipping & Returns: delivery options, rates by destination and worked examples (the {rates} line in the page text) */
function ship_rates_block(){
  global $STORE, $CONFIG;
  $SM = ship_methods($STORE); $fs = free_ship_usd($STORE);
  $cell = fn($r)=>money($r['base']).($r['per_kg'] > 0 ? ' <small>+ '.money($r['per_kg']).'/kg</small>' : ''); ?>
  <div class="tblwrap"><table class="tbl">
    <thead><tr><th>Option</th><th>Delivery time</th><th>Tracking</th></tr></thead>
    <tbody>
      <?php foreach($SM as $mm): ?><tr><td class="nm"><?= h($mm['label']) ?></td><td><?= h($mm['days']) ?> after dispatch</td><td>Door to door</td></tr><?php endforeach; ?>
    </tbody>
  </table></div>
  <p>We ship with Japan Post EMS, DHL Express and FedEx, choosing the best carrier for your parcel's weight, destination and delivery option. Shipping is priced by the weight of your order and where it's going: a price per order plus a price per kilogram, rounded up to the next whole dollar.<?php if($fs): ?> Orders over <?= money_whole($fs) ?> ship free with <?= h($SM['standard']['label']) ?>.<?php endif; ?></p>
  <div class="tblwrap"><table class="tbl">
    <thead><tr><th>Destination</th><?php foreach($SM as $mm): ?><th class="r"><?= h($mm['label']) ?></th><?php endforeach; ?></tr></thead>
    <tbody>
      <?php $zoned = array_merge([], ...array_map(fn($z)=>$z['countries'], $STORE['shipping']['zones']));   /* "Rest of world" only while some country has no zone */
      foreach(array_merge($STORE['shipping']['zones'], array_diff(array_keys($GLOBALS['COUNTRIES']), $zoned) ? [['name'=>'Rest of world']+$STORE['shipping']['rest']] : []) as $z): $zr = zone_rates($z); ?>
        <tr><td class="nm"><?= h($z['name']) ?></td><?php foreach(array_keys($SM) as $mk): ?><td class="r"><?= $cell($zr[$mk]) ?></td><?php endforeach; ?></tr>
      <?php endforeach; ?>
    </tbody>
  </table></div>
  <?php $ex = [['A single card', 0.05, 30], ['6 booster boxes (about 2.4 kg)', 2.4, 900], ['6 Elite Trainer Boxes (about 5.4 kg)', 5.4, 250]];
  if($fs) $ex[] = ['36 booster boxes (about 14.4 kg), over '.money_whole($fs), 14.4, $fs]; ?>
  <div class="tblwrap"><table class="tbl ex">
    <thead><tr><th>Examples to <?= h($GLOBALS['COUNTRIES'][$GLOBALS['HOME_CC']] ?? $GLOBALS['HOME_CC']) ?></th><?php foreach($SM as $mm): ?><th class="r"><?= h($mm['label']) ?></th><?php endforeach; ?></tr></thead>
    <tbody>
      <?php foreach($ex as [$label, $kg, $goods]): ?>
        <tr><td><?= h($label) ?></td><?php foreach(array_keys($SM) as $mk): $v = shipping_usd($STORE, $GLOBALS['HOME_CC'], $kg, $mk, $goods); ?><td class="r"><?= $v > 0 ? money($v) : '<b class="free">Free</b>' ?></td><?php endforeach; ?></tr>
      <?php endforeach; ?>
    </tbody>
  </table></div>
  <p class="small">As a guide, a sealed Japanese booster box weighs about 0.4 kg packed and an Elite Trainer Box about 0.9 kg. Checkout shows the exact price for your order before you pay.</p>
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
  <div class="pcheck"><label for="pcq">Check a price</label>
    <input id="pcq" type="search" placeholder="Try 151, Charizard, Elite Trainer Box, sleeves…" oninput="pcFilter(this.value)" autocomplete="off">
    <span id="pcCount"><?= count($PRODUCTS) ?> products</span></div>
  <div class="tblwrap"><table class="tbl pc" id="pcTable">
    <thead><tr><th>Product</th><th class="r">Price</th><th class="r">Best price</th></tr></thead><tbody>
    <?php foreach($CATEGORIES as $ck=>$c): foreach($PRODUCTS as $p): if($p['cat'] !== $ck) continue;
      $top = end($p['ladder']); ?>
      <tr data-q="<?= h(strtolower($p['name'].' '.$p['set'].' '.$p['sku'].' '.$c['label'])) ?>">
        <td class="nm"><a href="<?= h(url('product', ['id'=>$p['id']])) ?>"><?= h($p['name']) ?></a>
          <div class="sk"><?= h(implode(' · ', array_filter([$c['label'], $p['set'], $p['cond'], status_label(PRODUCT_STATUSES, $p['status'])]))) ?></div></td>
        <td class="r"><?= money(unit_price($p, $p['moq'])) ?><div class="sk">each, from <?= (int)$p['moq'] ?></div></td>
        <td class="r"><?= money($top[1]) ?><div class="sk">each at <?= (int)max($p['moq'], $top[0]) ?>+</div></td></tr>
    <?php endforeach; endforeach; ?>
    </tbody></table></div>
  <p class="small">Live prices from our catalogue in <?= h(cur_code()) ?> (change the currency at the top of the page). Shipping is extra, and free on orders over <?= money_whole(free_ship_usd($GLOBALS['STORE'])) ?>.</p>
<?php }

/* card database: every Japanese set we carry, by series */
function set_table_block(){
  global $SERIES;
  foreach($SERIES as $k=>$sr): $sets = series_sets($k); if(!$sets) continue; ?>
  <div class="tblwrap"><table class="tbl">
    <thead><tr><th><?= h($sr['name']) ?> set</th><th>Set code</th><th class="r">Products</th></tr></thead><tbody>
    <?php foreach($sets as $st): ?>
      <tr><td class="nm"><a href="<?= h(url('set', ['s'=>$st['slug']])) ?>"><?= h($st['name']) ?></a></td><td><?= h(($st['code'] ?? '') ?: '—') ?></td><td class="r"><?= count(set_products($st['name'])) ?></td></tr>
    <?php endforeach; ?>
    </tbody></table></div>
  <?php endforeach;
}

/* free printable template: exact card size, bleed and safe area */
function card_template_block(){ ?>
  <div class="tplbox">
    <img src="assets/site/trading-card-template-63x88mm.svg" width="276" height="376" alt="Blank trading card template, 63 × 88 mm with 3 mm bleed and a safe area" loading="lazy">
    <div>
      <b>Free printable card template</b>
      <p>63 × 88 mm (2.5 × 3.5 in), the size of a Pokémon card, with 3 mm bleed, the trim line, rounded corners and a safe area for text. Vector files: print at 100% (“actual size”), not “fit to page”.</p>
      <p><a class="btn" href="assets/site/trading-card-template-63x88mm.svg" download>Download one card (SVG)</a>
         <a class="btn g" href="assets/site/trading-card-template-sheet-a4.svg" download>Download a sheet of 9 (A4)</a>
         <a class="btn g" href="assets/site/trading-card-template-sheet-letter.svg" download>US Letter</a></p>
    </div>
  </div>
<?php }

/* the "### question" / answer pairs under "## Questions", for Google's FAQ data */
function policy_questions($text){
  $out = []; $in = false; $q = null; $a = [];
  foreach(explode("\n", fill($text)) as $line){
    if(preg_match('/^##\s+(.+)$/', $line, $m)){ if($q && $a) $out[] = [$q, implode(' ', $a)]; $q = null; $a = []; $in = stripos($m[1], 'question') !== false; continue; }
    if(!$in) continue;
    if(preg_match('/^###\s+(.+)$/', $line, $m)){ if($q && $a) $out[] = [$q, implode(' ', $a)]; $q = trim($m[1]); $a = []; continue; }
    if($q && trim($line) !== '') $a[] = trim(preg_replace(['/\[([^\]]+)\]\([^)]*\)/', '/\*\*/'], ['$1', ''], $line));
  }
  if($q && $a) $out[] = [$q, implode(' ', $a)];
  return $out;
}

/* "Add $X more for free shipping" / "Your order ships free" */
function free_ship_meter($goods, $compact=false){
  global $STORE;
  $t = free_ship_usd($STORE); if(!$t) return;
  $sm = ship_methods($STORE); $pct = min(100, round($goods / $t * 100)); ?>
  <div class="fsm<?= $compact ? ' c' : '' ?>">
    <?php if($goods >= $t): ?>
      <div class="t"><b>Your order ships free.</b> Free <?= h($sm['standard']['label']) ?> shipping applied — <?= h($sm['express']['label']) ?> costs only the difference.</div>
    <?php else: ?>
      <div class="t">Add <b><?= money($t - $goods) ?></b> more for free <?= h($sm['standard']['label']) ?> shipping (orders over <?= money_whole($t) ?>).</div>
    <?php endif; ?>
    <div class="meter" role="progressbar" aria-valuemin="0" aria-valuemax="100" aria-valuenow="<?= (int)$pct ?>" aria-label="Progress to free shipping"><i style="width:<?= (int)$pct ?>%"></i></div>
  </div>
<?php }

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
    <a href="<?= h(url('guide', ['g'=>$g['slug']])) ?>"><h3><?= h(fill($g['title'])) ?></h3>
      <p><?= h(fill(($g['seo_desc'] ?? '') ?: plain($g['body'] ?? '', 140))) ?></p><span>Read the guide →</span></a>
  <?php endforeach; ?></div>
<?php }

function steps_block($CONFIG){ ?>
  <div class="steps">
    <div><div class="n">01</div><h3>Price it openly</h3>
      <p>Every listing shows its full break ladder to everyone. No application, no approval wait, no quote round-trip for standard volumes.</p></div>
    <div><div class="n">02</div><h3>Place the order</h3>
      <p>Add to cart, enter your shipping address and pick a payment method. Stock is reserved in your name for <?= (int)$CONFIG['hold_hours'] ?> hours.</p></div>
    <div><div class="n">03</div><h3>Pay your way</h3>
      <p>Pay by Bitcoin straight away on your order page, or get the details for another method by email or text within <?= (int)$CONFIG['reply_hours'] ?> hours.</p></div>
    <div><div class="n">04</div><h3>Ship tracked from Japan</h3>
      <p>Payment clears, stock is allocated, and we dispatch within <?= (int)$CONFIG['hold_hours'] ?> hours by EMS, DHL or FedEx with tracking.</p></div>
  </div>
<?php }
