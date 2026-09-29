<?php
/* =============================================================
   FUDAKURA — cartes Pokémon japonaises pour la France (fudakura-france.com). PHP 7.4+.

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
/* shopper-facing names for product statuses and conditions (the admin keeps the English keys) */
const FR_STATUS = ['in'=>'En stock', 'new'=>'Nouveauté', 'low'=>'Stock limité', 'preorder'=>'Précommande', 'soldout'=>'Épuisé'];
const FR_COND   = ['Sealed'=>'Scellé', 'Graded'=>'Gradée', 'Near Mint'=>'Near Mint', 'Lightly Played'=>'Légèrement jouée'];
function cond_fr($c){ return FR_COND[$c] ?? (string)$c; }
function date_fr($ymd){
  $t = strtotime((string)$ymd); if(!$t) return (string)$ymd;
  $m = ['janvier','février','mars','avril','mai','juin','juillet','août','septembre','octobre','novembre','décembre'][(int)date('n', $t) - 1];
  return (date('j', $t) === '1' ? '1er' : date('j', $t)).' '.$m.' '.date('Y', $t);
}
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

/* French format: 1 234,50 € (narrow no-break space for thousands, no-break space before the symbol) */
function money($usd){
  global $CURRENCIES;
  $c = cur_code(); $m = $CURRENCIES[$c];
  return number_format($usd * $m['rate'], $m['dec'], ',', "\u{202F}")."\u{00A0}".$m['sym'];
}

/* whole units, for round numbers like the free-shipping threshold */
function money_whole($usd){
  global $CURRENCIES;
  $m = $CURRENCIES[cur_code()];
  return number_format(round($usd * $m['rate']), 0, ',', "\u{202F}")."\u{00A0}".$m['sym'];
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
     /boutique  /displays-pokemon  /produits/{id}  /extensions  /extensions/{set or series}  /cartes/{collection}  /guides/{guide}  /panier …
   Otherwise index.php?p=…  Links are written relative to <base href>, so the shop also works in a subfolder. */
/* site photos (assets/site): the home page image until you upload your own in Settings, and the link-preview image */
const SITE_HERO  = 'assets/site/pokemon-30th-celebration-elite-trainer-box.webp';
const SITE_SHARE = 'assets/site/share-30th-celebration.jpg';
$BASE = rtrim(str_replace('\\', '/', dirname($_SERVER['SCRIPT_NAME'] ?? '/index.php')), '/').'/';
const PAGE_PATHS = ['cart'=>'panier', 'checkout'=>'commande', 'received'=>'commande-recue', 'how'=>'comment-commander',
                    'shipping'=>'livraison-retours', 'payment'=>'moyens-de-paiement', 'faq'=>'faq', 'contact'=>'contact',
                    'sets'=>'extensions', 'guides'=>'guides', 'sitemap'=>'sitemap.xml', 'pay'=>'paiement', 'paystatus'=>'paiement-statut'];
/* short addresses people may type: path => [page, #section] */
const MOVED_PATHS = ['livraison'=>['shipping', ''], 'retours'=>['shipping', 'retours'], 'shipping'=>['shipping', ''], 'returns'=>['shipping', 'retours']];

function cat_slug($key){ global $CATEGORIES; return ($CATEGORIES[$key]['slug'] ?? '') ?: slugify($CATEGORIES[$key]['label'] ?? $key); }

function url($p, $extra=[]){
  global $CONFIG;
  if(empty($CONFIG['pretty_urls'])) return $p === 'home' && !$extra ? './' : 'index.php?'.http_build_query(['p'=>$p] + $extra);
  $pull = function($k) use(&$extra){ $v = (string)($extra[$k] ?? ''); unset($extra[$k]); return $v; };
  switch($p){
    case 'home':       $path = ''; break;
    case 'catalog':    $c = $pull('cat'); $path = $c !== '' ? cat_slug($c) : 'boutique'; break;
    case 'product':    $path = 'produits/'.rawurlencode($pull('id')); break;
    case 'set':
    case 'series':     $path = 'extensions/'.rawurlencode($pull('s')); break;
    case 'collection': $path = 'cartes/'.rawurlencode($pull('c')); break;
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
    if($path === 'boutique') return ['p'=>'catalog'];
    foreach($CATEGORIES as $k=>$c){ if(cat_slug($k) === $path) return ['p'=>'catalog', 'cat'=>$k]; }
    $pages = array_flip(PAGE_PATHS);
    if(isset($pages[$path])) return ['p'=>$pages[$path]];
    if(info_page($path)) return ['p'=>'page', 'pg'=>$path];
    if(isset(MOVED_PATHS[$path])) return ['p'=>MOVED_PATHS[$path][0], 'moved'=>MOVED_PATHS[$path][1]];
  } elseif(count($seg) === 2){
    [$a, $b] = $seg;
    if($a === 'produits')   return ['p'=>'product', 'id'=>$b];
    if($a === 'extensions') return ['p'=>series_by_slug($b) ? 'series' : 'set', 's'=>$b];
    if($a === 'cartes')     return ['p'=>'collection', 'c'=>$b];
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
    $out[$name] = ($STORE['sets'][$name] ?? []) + ['name'=>$name, 'slug'=>'', 'series'=>'', 'code'=>'', 'h1'=>'', 'intro'=>'', 'seo_title'=>'', 'seo_desc'=>''];
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
                            : ($m[2] === 'returns' ? url('shipping').'#retours' : null));
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
  'prix-carte-pokemon'=>'Prix et cote des cartes Pokémon', 'carte-pokemon-la-plus-chere'=>'La carte Pokémon la plus chère',
  'cartes-pokemon-les-plus-rares'=>'Cartes Pokémon rares', 'rarete-carte-pokemon'=>'Rareté des cartes Pokémon',
  'cartes-pokemon-japonaises'=>'Cartes Pokémon japonaises', 'display-etb-coffret-pokemon'=>'Display, ETB ou coffret Pokémon ?',
  'extensions-pokemon'=>'Extensions Pokémon : Flammes Fantasmagoriques et plus', 'series-cartes-pokemon'=>'Séries de cartes Pokémon',
  'jouer-cartes-pokemon'=>'Comment jouer aux cartes Pokémon', 'ou-acheter-cartes-pokemon'=>'Où acheter des cartes Pokémon',
];
const GUIDE_RELATED = [
  'prix-carte-pokemon'=>['carte-pokemon-la-plus-chere','rarete-carte-pokemon','cartes-pokemon-les-plus-rares','cartes-pokemon-japonaises'],
  'carte-pokemon-la-plus-chere'=>['prix-carte-pokemon','cartes-pokemon-les-plus-rares','rarete-carte-pokemon','series-cartes-pokemon'],
  'cartes-pokemon-les-plus-rares'=>['rarete-carte-pokemon','carte-pokemon-la-plus-chere','prix-carte-pokemon','cartes-pokemon-japonaises'],
  'rarete-carte-pokemon'=>['cartes-pokemon-les-plus-rares','prix-carte-pokemon','cartes-pokemon-japonaises','extensions-pokemon'],
  'cartes-pokemon-japonaises'=>['extensions-pokemon','display-etb-coffret-pokemon','rarete-carte-pokemon','ou-acheter-cartes-pokemon'],
  'display-etb-coffret-pokemon'=>['extensions-pokemon','cartes-pokemon-japonaises','jouer-cartes-pokemon','ou-acheter-cartes-pokemon'],
  'extensions-pokemon'=>['series-cartes-pokemon','cartes-pokemon-japonaises','display-etb-coffret-pokemon','prix-carte-pokemon'],
  'series-cartes-pokemon'=>['extensions-pokemon','carte-pokemon-la-plus-chere','jouer-cartes-pokemon','cartes-pokemon-japonaises'],
  'jouer-cartes-pokemon'=>['display-etb-coffret-pokemon','series-cartes-pokemon','ou-acheter-cartes-pokemon','rarete-carte-pokemon'],
  'ou-acheter-cartes-pokemon'=>['cartes-pokemon-japonaises','display-etb-coffret-pokemon','prix-carte-pokemon','extensions-pokemon'],
];
const PAGE_GUIDES = [
  'cat:boxes'=>['display-etb-coffret-pokemon','extensions-pokemon','cartes-pokemon-japonaises','prix-carte-pokemon'],
  'cat:etb'=>['display-etb-coffret-pokemon','extensions-pokemon','jouer-cartes-pokemon','ou-acheter-cartes-pokemon'],
  'cat:premium'=>['display-etb-coffret-pokemon','jouer-cartes-pokemon','extensions-pokemon','ou-acheter-cartes-pokemon'],
  'cat:singles'=>['prix-carte-pokemon','cartes-pokemon-les-plus-rares','rarete-carte-pokemon','carte-pokemon-la-plus-chere'],
  'cat:accessories'=>['jouer-cartes-pokemon','prix-carte-pokemon','cartes-pokemon-japonaises','display-etb-coffret-pokemon'],
  'shop'=>['display-etb-coffret-pokemon','cartes-pokemon-japonaises','prix-carte-pokemon','extensions-pokemon','ou-acheter-cartes-pokemon'],
  'set'=>['extensions-pokemon','cartes-pokemon-japonaises','rarete-carte-pokemon','prix-carte-pokemon'],
  'set:151'=>['carte-pokemon-la-plus-chere','extensions-pokemon','rarete-carte-pokemon','prix-carte-pokemon'],
  'set:inferno-x-flammes-fantasmagoriques'=>['extensions-pokemon','prix-carte-pokemon','cartes-pokemon-japonaises','display-etb-coffret-pokemon'],
  'series'=>['extensions-pokemon','series-cartes-pokemon','cartes-pokemon-japonaises','rarete-carte-pokemon'],
  'coll'=>['prix-carte-pokemon','cartes-pokemon-les-plus-rares','rarete-carte-pokemon'],
  'coll:dracaufeu'=>['carte-pokemon-la-plus-chere','prix-carte-pokemon','extensions-pokemon','cartes-pokemon-les-plus-rares'],
  'coll:pikachu'=>['carte-pokemon-la-plus-chere','cartes-pokemon-les-plus-rares','prix-carte-pokemon'],
  'coll:cartes-pokemon-gradees-psa'=>['prix-carte-pokemon','carte-pokemon-la-plus-chere','rarete-carte-pokemon'],
  'faq'=>['cartes-pokemon-japonaises','display-etb-coffret-pokemon','prix-carte-pokemon','jouer-cartes-pokemon','ou-acheter-cartes-pokemon','extensions-pokemon'],
  'home'=>['prix-carte-pokemon','carte-pokemon-la-plus-chere','display-etb-coffret-pokemon','extensions-pokemon','cartes-pokemon-les-plus-rares','cartes-pokemon-japonaises'],
];
/* where each group of guides sends readers to shop */
const GUIDE_SHOP = [
  'collect'=>'Voir nos [cartes Pokémon rares](category:singles), nos [cartes Dracaufeu](cards:dracaufeu) et nos [cartes gradées PSA](cards:cartes-pokemon-gradees-psa), expédiées du Japon.',
  'play'=>'Voir nos [Coffrets Dresseur d’Élite](category:etb), nos [coffrets et decks](category:premium) et nos [classeurs et protège-cartes](category:accessories), expédiés du Japon.',
  'buy'=>'Voir nos [displays Pokémon japonais](category:boxes), nos [Coffrets Dresseur d’Élite](category:etb) et nos [cartes rares](category:singles), expédiés du Japon.',
];
const GUIDE_GROUP = ['prix-carte-pokemon'=>'collect','carte-pokemon-la-plus-chere'=>'collect','cartes-pokemon-les-plus-rares'=>'collect',
  'rarete-carte-pokemon'=>'collect','jouer-cartes-pokemon'=>'play','series-cartes-pokemon'=>'play'];

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
function guide_links($slugs, $title='Guides utiles', $extra=[]){
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
  $body .= "  SHIPPING: ".((float)($order['shipping_usd'] ?? 1) == 0 ? 'Free' : $order['shipping'])." ({$order['ship_zone']}, {$order['ship_label']})\n";
  $body .= "  TOTAL:    {$order['total']} ({$order['currency']})\n";
  $body .= "\n";
  if($order['notes']) $body .= "NOTES\n  {$order['notes']}\n\n";
  $body .= "Submitted: {$order['time']}\n";

  $to_shop = shop_mail($CONFIG['order_email'], "New order {$order['ref']} — {$order['payment_label']}", $body, $order['email']);

  /* customer confirmation (in French) */
  $free = (float)($order['shipping_usd'] ?? 1) == 0;
  $c  = "Merci, nous avons bien reçu votre commande.\n\n";
  $c .= "Référence de commande : {$order['ref']}\n";
  $c .= "Articles : {$order['goods']}\n";
  $c .= "Livraison : ".($free ? 'Offerte' : $order['shipping'])." — {$order['ship_label']}\n";
  $c .= "Total de la commande : {$order['total']} ({$order['currency']})\n";
  $c .= "Moyen de paiement choisi : {$order['payment_label']}\n\n";
  $c .= "VOS ARTICLES\n";
  foreach($order['lines'] as $l) $c .= "  {$l['qty']} x {$l['name']} — {$l['total']}\n";
  $c .= "\nADRESSE DE LIVRAISON\n  {$order['name']}\n  {$order['address1']}\n".($order['address2'] ? "  {$order['address2']}\n" : '')
      ."  {$order['postcode']} {$order['city']}\n  {$order['country_name']}\n\n";
  if(!empty($order['btc'])){
    $b = $order['btc'];
    $c .= "PAYER EN BITCOIN\n";
    if(!empty($b['sats'])){
      $c .= "Envoyez exactement :  ".btc_amount($b['sats'])." BTC\n";
      $c .= "À l'adresse :         {$b['address']}\n";
      $c .= "Ce montant est garanti jusqu'à ".gmdate('H:i', $b['expires'])." UTC. Ensuite, la page de votre commande\n";
      $c .= "affiche un nouveau montant au cours du moment.\n\n";
    } else $c .= "La page de votre commande affiche le montant exact en BTC et un QR code.\n\n";
    $c .= "La page de votre commande (QR code, montant et suivi du paiement) :\n".btc_pay_link($order)."\n\n";
    $c .= "Nous vous envoyons votre reçu dès que votre paiement apparaît sur la blockchain,\n";
    $c .= "et nous expédions depuis le Japon, avec suivi, dans les {$CONFIG['hold_hours']} heures qui suivent sa confirmation.\n\n";
  } else {
    $c .= "ET MAINTENANT ?\n";
    $c .= "Votre stock est réservé pendant {$CONFIG['hold_hours']} heures. Nous vous contactons\n";
    $c .= "par e-mail ou SMS sous {$CONFIG['reply_hours']} heures avec les coordonnées de paiement\n";
    $c .= "du moyen choisi, accompagnées de votre facture.\n\n";
    $c .= "Indiquez la référence {$order['ref']} avec votre paiement pour que nous puissions le rapprocher de votre commande.\n";
    $c .= "Dès réception du paiement, nous expédions depuis le Japon sous {$CONFIG['hold_hours']} heures,\n";
    $c .= "avec suivi.\n\n";
  }
  $c .= "Une question ? {$CONFIG['email']}\n";
  $c .= "{$CONFIG['legal_name']} — {$CONFIG['address']}\n";
  $to_customer = shop_mail($order['email'], "Commande {$order['ref']} reçue — {$CONFIG['brand']}", $c, $CONFIG['email']);
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
  else { unset($_SESSION['cart'][$id]); flash(($p ? $p['name'] : 'Un produit').' n’est plus disponible et a été retiré de votre commande.'); }
}

$errors = [];
if($_SERVER['REQUEST_METHOD'] === 'POST'){
  $action = $_POST['action'] ?? '';

  if($action === 'add'){
    $p = product($_POST['id'] ?? '');
    if($p){
      $qty = fit_qty($p, (int)($_POST['qty'] ?? 0) ?: $p['moq']);
      if($qty) $_SESSION['cart'][$p['id']] = $qty;
      else     flash("{$p['name']} est épuisé.");
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
    if($bot)                                             $errors[] = 'Votre commande n’a pas pu être envoyée. Vérifiez le formulaire et validez-la à nouveau.';
    if(!cart_lines())                                    $errors[] = 'Votre commande est vide.';
    if($f['name'] === '')                                $errors[] = 'Indiquez le nom du destinataire.';
    if(!filter_var($f['email'], FILTER_VALIDATE_EMAIL))  $errors[] = 'Indiquez une adresse e-mail valide.';
    if($f['phone'] === '')                               $errors[] = 'Indiquez un numéro de téléphone : nous envoyons les coordonnées de paiement par SMS.';
    if(!isset($COUNTRIES[$f['country']]))                $errors[] = 'Choisissez votre pays.';
    if($f['address1'] === '')                            $errors[] = 'Indiquez une adresse.';
    if($f['city'] === '')                                $errors[] = 'Indiquez une ville.';
    if(!isset($PAYMENTS[$f['payment']]))                 $errors[] = 'Choisissez un moyen de paiement.';
    elseif(isset($COUNTRIES[$f['country']]) && !payment_ok($f['payment'], $f['country']))
      $errors[] = $PAYMENTS[$f['payment']]['label'].' n’est pas disponible pour ce pays ('.$COUNTRIES[$f['country']].'). Choisissez un autre moyen de paiement.';
    if(empty($_POST['agree']))                           $errors[] = 'Veuillez accepter les conditions générales de vente et la politique de livraison et de retours.';

    /* delivery option: Standard or Express */
    $SHIPM = ship_methods($STORE);
    $method = in_array($_POST['ship_method'] ?? '', SHIP_METHODS, true) ? $_POST['ship_method'] : 'standard';
    $f['ship_method'] = $method;

    /* minimum order value counts goods plus shipping */
    if(isset($COUNTRIES[$f['country']]) && cart_lines()){
      $ship  = shipping_usd($STORE, $f['country'], cart_weight(), $method, cart_total());
      $grand = round(cart_total() + $ship, 2);
      if($grand < $MIN_ORDER)
        $errors[] = 'Le minimum de commande est de '.money($MIN_ORDER).', livraison comprise. Votre total est de '.money($grand).' : ajoutez '.money($MIN_ORDER - $grand).' pour valider cette commande.';
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
    if(!empty($o['btc']['txid'])) flash('Nous avons déjà reçu votre paiement pour cette commande. Merci !');
    elseif(($o['btc']['tries'] ?? 0) >= 8) flash('Trop de tentatives. Écrivez à '.$CONFIG['email'].' avec votre référence de commande et l’identifiant de transaction.');
    elseif(!preg_match('/^[0-9a-f]{64}$/', $txid)) flash('Ce n’est pas un identifiant de transaction valide : il compte 64 lettres et chiffres et figure dans le détail du paiement de votre portefeuille.');
    else {
      $o['btc']['tries'] = ($o['btc']['tries'] ?? 0) + 1;
      $claims = btc_claims();
      $r = isset($claims[$txid]) ? false : btc_lookup_tx($txid, $o['btc']['address']);
      if($r === false) flash('Cette transaction est déjà associée à une commande. S’il s’agit d’une erreur, écrivez à '.$CONFIG['email'].'.');
      elseif($r === null){      /* explorers didn't answer: keep it, and check it again on the next status check */
        $o['btc']['reported'] = $txid;
        shop_mail($CONFIG['order_email'], "Bitcoin payment reported — {$o['ref']}",
          "The customer says they've paid order {$o['ref']} and gave this transaction ID:\n$txid\n".btc_tx_url($txid)."\n\nThe block explorers couldn't be reached to check it. The order page will keep trying, or check it yourself in the admin.\n", $o['email']);
        flash('Merci, nous avons noté votre transaction et la confirmerons dès que la vérification sur le réseau Bitcoin aura abouti.');
      }
      elseif(!$r['exists']) flash('Cette transaction n’apparaît pas encore sur le réseau Bitcoin. Si vous venez de l’envoyer, patientez une minute puis réessayez.');
      elseif(!$r['found']) flash('Cette transaction n’envoie pas de bitcoins à notre adresse '.$o['btc']['address'].'. Vérifiez que vous avez copié la bonne transaction.');
      else {
        $before = btc_state($o);
        btc_attach($o, $r['found'], $r['tip']);
        $after = btc_state($o);
        if($after === 'seen') btc_mail($o, 'received');
        elseif($after !== $before){ if($after === 'confirmed' && in_array($o['status'], ['new','invoiced'], true)) $o['status'] = 'paid'; btc_mail($o, $after); }
        flash('Paiement trouvé, merci ! Votre reçu vous est envoyé par e-mail.');
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
  header('Location: '.$BASE.$u.(($from_path['moved'] ?? 'retours') !== '' ? '#'.($from_path['moved'] ?? 'retours') : ''), true, 301); exit;
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
$h1 = ''; $crumbs = [['Accueil', 'home', []]];
$fixed = [
  'cart'     => ['Votre panier', 'Votre panier'],
  'checkout' => ['Commande', 'Finaliser la commande'],
  'received' => ['Commande reçue', ''],
  'how'      => ['Comment commander — cartes Pokémon japonaises', 'Comment commander'],
  'shipping' => ['Livraison et retours — cartes Pokémon expédiées du Japon', 'Livraison et retours'],
  'pay'      => ['Payer votre commande', ''],
  'payment'  => ['Moyens de paiement', 'Moyens de paiement'],
  'faq'      => ['FAQ — acheter des cartes Pokémon japonaises', 'Questions fréquentes'],
  'contact'  => ['Contact', 'Contact'],
  'notfound' => ['Page introuvable', 'Page introuvable'],
];
$page_desc = '';
switch($page){
  case 'home':
    $page_title = ($CONFIG['home_seo_title'] ?? '') ?: 'Cartes Pokémon japonaises — displays, coffrets et cartes rares';
    $page_desc  = ($CONFIG['home_seo_desc'] ?? '') ?: 'Cartes Pokémon japonaises authentiques expédiées du Japon : displays scellés, coffrets Dresseur d’Élite, cartes rares et gradées PSA.';
    $crumbs = [];
    break;
  case 'catalog':
    $crumbs[] = ['Boutique', $cinfo ? 'catalog' : '', []];
    if($cinfo){
      $crumbs[] = [$cinfo['label'], '', []];
      $page_title = ($cinfo['seo_title'] ?? '') ?: $cinfo['label'].' — cartes Pokémon japonaises';
      $page_desc  = ($cinfo['seo_desc'] ?? '') ?: plain($cinfo['blurb']);
      $h1 = ($cinfo['h1'] ?? '') ?: $cinfo['label'];
    } else {
      $page_title = 'Boutique cartes Pokémon — displays, ETB et cartes rares';
      $page_desc  = 'Acheter des cartes Pokémon japonaises : displays scellés, coffrets Dresseur d’Élite (ETB), cartes rares, cartes gradées PSA et classeurs, expédiés du Japon.';
      $h1 = 'Boutique de cartes Pokémon japonaises';
    }
    if($q !== '') $h1 = 'Résultats pour « '.$q.' »';
    elseif(!$cinfo && $f_set !== '') $h1 = $f_set;
    break;
  case 'product':
    $pset = $prod['set'] !== '' ? set_of($prod['set']) : null;
    $crumbs[] = [$CATEGORIES[$prod['cat']]['label'], 'catalog', ['cat'=>$prod['cat']]];
    if($pset) $crumbs[] = [$pset['name'], 'set', ['s'=>$pset['slug']]];
    $crumbs[] = [$prod['name'], '', []];
    $auto = $prod['name'];
    if(stripos($auto, 'japonais') === false && strlen($auto) < 34) $auto .= ' — carte Pokémon japonaise';
    $page_title = ($prod['seo_title'] ?? '') ?: $auto;
    $from = unit_price($prod, $prod['moq']);
    $page_desc  = ($prod['seo_desc'] ?? '') ?: plain(desc_parts($prod['desc'])[0], 105).' À partir de '.money($from).' l’unité, expédié du Japon.';
    break;
  case 'sets':
    $crumbs[] = ['Extensions', '', []];
    $page_title = 'Extensions Pokémon — Méga-Évolution et Écarlate et Violet';
    $page_desc  = 'Toutes les extensions Pokémon japonaises en stock, de la série Méga-Évolution aux incontournables Écarlate et Violet comme 151 et Terastal Festival ex.';
    $h1 = 'Extensions de cartes Pokémon';
    break;
  case 'series':
    $crumbs[] = ['Extensions', 'sets', []]; $crumbs[] = [$series['name'], '', []];
    $page_title = ($series['seo_title'] ?? '') ?: 'Extensions Pokémon '.$series['name'].' (japonaises)';
    $page_desc  = ($series['seo_desc'] ?? '') ?: plain($series['intro'] ?? '');
    $h1 = ($series['h1'] ?? '') ?: 'Extensions Pokémon '.$series['name'];
    break;
  case 'set':
    $sser = $SERIES[$set['series']] ?? null;
    $crumbs[] = ['Extensions', 'sets', []];
    if($sser) $crumbs[] = [$sser['name'], 'series', ['s'=>$sser['slug']]];
    $crumbs[] = [$set['name'], '', []];
    $label = $set['name'].($set['code'] !== '' ? ' ('.$set['code'].')' : '');
    $page_title = $set['seo_title'] ?: 'Display '.$label.' et cartes Pokémon japonaises';
    $page_desc  = $set['seo_desc'] ?: (plain($set['intro']) ?: 'Displays et cartes Pokémon japonaises '.$set['name'].', expédiés du Japon.');
    $h1 = ($set['h1'] ?? '') !== '' ? $set['h1'] : $label.' — cartes Pokémon japonaises';
    break;
  case 'collection':
    $crumbs[] = ['Boutique', 'catalog', []]; $crumbs[] = [$coll['title'], '', []];
    $page_title = ($coll['seo_title'] ?? '') ?: $coll['title'];
    $page_desc  = ($coll['seo_desc'] ?? '') ?: plain($coll['intro'] ?? '');
    $h1 = ($coll['h1'] ?? '') ?: $coll['title'];
    break;
  case 'guides':
    $crumbs[] = ['Guides', '', []];
    $page_title = 'Guides cartes Pokémon : prix, cote, cartes rares, règles';
    $page_desc  = 'Nos guides des cartes Pokémon : prix et cote, cartes les plus chères et les plus rares, raretés, Dracaufeu, cartes japonaises, displays et coffrets.';
    $h1 = 'Guides des cartes Pokémon';
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
    if($page === 'checkout') $crumbs[] = ['Panier', 'cart', []];
    if($h1 !== '') $crumbs[] = [$h1, '', []]; else $crumbs = [];
    if($page === 'notfound') $crumbs = [];
    $page_desc = ['shipping'=>(free_ship_usd($STORE) ? 'Livraison offerte dès '.money_whole(free_ship_usd($STORE)).'. ' : '')
                    .'Cartes Pokémon japonaises expédiées du Japon avec suivi : délais, tarifs de livraison, retours et remboursements.',
                  'faq'=>'Réponses aux questions fréquentes sur l’achat de cartes Pokémon japonaises : livraison en France, paiement, retours et authenticité.',
                  'how'=>'Commander chez {brand} : prix dégressifs affichés sur chaque produit, paiement en Bitcoin ou par virement, livraison suivie depuis le Japon.',
                  'payment'=>'Payer vos cartes Pokémon chez {brand} : Bitcoin directement depuis votre portefeuille, ETH et USDT, ou virement bancaire SEPA en euros.',
                  'contact'=>'Contactez {brand} pour une commande de cartes Pokémon japonaises, un prix par quantité ou une commande en cours. Réponse sous '.(int)$CONFIG['reply_hours'].' heures.'][$page] ?? '';
}
if($page_desc === '') $page_desc = 'Cartes Pokémon japonaises expédiées du Japon : displays scellés, coffrets Dresseur d’Élite, coffrets premium, cartes rares et accessoires.';
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
<html lang="fr-FR">
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
<meta property="og:locale" content="fr_FR">
<meta property="og:title" content="<?= h($page_title) ?>">
<meta property="og:description" content="<?= h($page_desc) ?>">
<?php if($canonical): ?><meta property="og:url" content="<?= h($canonical) ?>"><?php endif; ?>
<?php if($og): ?><meta property="og:image" content="<?= h($abs_img($og[0])) ?>"><?php endif; ?>
<meta name="twitter:card" content="summary_large_image">
<meta name="theme-color" content="#0E1A3A">
<link rel="icon" href="assets/favicon.svg" type="image/svg+xml">
<?php if($page === 'home'): foreach(['google_verify'=>'google-site-verification', 'bing_verify'=>'msvalidate.01'] as $k=>$nm) if(($CONFIG[$k] ?? '') !== ''): ?><meta name="<?= $nm ?>" content="<?= h($CONFIG[$k]) ?>">
<?php endif; endif; ?>

<?php
$org_id = rtrim($CONFIG['domain'], '/').'/#org';
$graph = [
  ['@type'=>'Organization','@id'=>$org_id,'name'=>$CONFIG['brand'],'legalName'=>$CONFIG['legal_name'],'alternateName'=>$CONFIG['kanji'],'url'=>abs_url('home'),'email'=>$CONFIG['email'],
   'logo'=>rtrim($CONFIG['domain'], '/').'/assets/logo.svg',
   'address'=>['@type'=>'PostalAddress','streetAddress'=>$CONFIG['address'],'addressCountry'=>'JP'],
   'description'=>'Revendeur indépendant de produits authentiques du Jeu de Cartes à Collectionner Pokémon japonais, expédiés directement du Japon en France.',
   'knowsAbout'=>['Cartes Pokémon japonaises', 'Jeu de Cartes à Collectionner Pokémon', 'Displays Pokémon'],
   'areaServed'=>array_values($COUNTRIES)],
  ['@type'=>'WebSite','@id'=>rtrim($CONFIG['domain'], '/').'/#site','url'=>abs_url('home'),'name'=>$CONFIG['brand'],'inLanguage'=>'fr-FR',
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
    'dateModified'=>$guide['updated'] ?? gmdate('Y-m-d'),'inLanguage'=>'fr-FR',
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
/* "Paris" theme: crisp white, bleu roi and coquelicot red with a thin gold line, a tricolour rule, Didone
   serif headings (Didot/Bodoni where installed, Georgia otherwise) and small uppercase labels.
   System fonts only: nothing to download before first paint. */
:root{
  color-scheme:light;
  --bg:#FFFFFF; --paper:#FFFFFF; --card:#FFFFFF; --card2:#F3F5FA; --ink:#101828; --ink2:#3F4756; --muted:#6B7280;
  --line:#E2E5EC; --hair:#EEF0F4;
  --red:#1E3FAE; --red2:#16318A; --gold:#B8912F; --teal:#0F766E; --blue:#1E3FAE; --violet:#5B3FA8; --green:#15803D;
  --poppy:#E0352B; --navy:#0E1A3A; --cream:#FAF7F0;
  --indigo:#0E1A3A; --seal:#E0352B;
  --brand:var(--blue); --link:#1E3FAE;
  --holo:var(--poppy); --hot:var(--poppy);
  --serif:Didot,'Didot LT STD','Bodoni 72','Bodoni MT','Libre Bodoni','Playfair Display',Georgia,'Times New Roman',serif;
  --sans:system-ui,-apple-system,'Segoe UI',Roboto,'Helvetica Neue',Arial,sans-serif;
  --r:3px;
  --tri:linear-gradient(90deg,#1E3FAE 0 33.33%,#FFFFFF 33.33% 66.66%,#E0352B 66.66%);
}
*,*::before,*::after{box-sizing:border-box}
html{scroll-padding-top:150px;background:var(--bg)}
body{margin:0;background:var(--bg);color:var(--ink);font-family:var(--sans);font-size:16px;line-height:1.65;
  -webkit-font-smoothing:antialiased;font-variant-numeric:tabular-nums;border-top:4px solid transparent;border-image:var(--tri) 1}
h1,h2,h3{font-family:var(--serif);font-weight:700;line-height:1.12;margin:0;letter-spacing:-.005em;color:var(--ink)}
p{margin:0}a{color:inherit}img{max-width:100%;display:block;height:auto}
button,input,select,textarea{font:inherit;color:inherit}
:focus-visible{outline:2px solid var(--poppy);outline-offset:3px}
.wrap{width:min(1240px,calc(100% - 48px));margin-inline:auto}
@media(max-width:640px){.wrap{width:calc(100% - 32px)}}
.holo-text{color:var(--poppy)}
::selection{background:#DCE3FA}

/* top strip */
.strip{background:var(--navy);color:#C9D1E6;font-size:12.5px;letter-spacing:.02em}
.strip .wrap{display:flex;justify-content:space-between;align-items:center;gap:18px;min-height:36px}
.strip .wrap>*{white-space:nowrap}
.strip .st{flex:1;min-width:0;overflow:hidden;text-overflow:ellipsis;text-align:center}
@media(max-width:1100px){.strip .st{display:none}}
.strip a{color:#fff;text-decoration:none;font-weight:700}
.strip a:hover{text-decoration:underline}

/* header */
header.site{position:sticky;top:0;z-index:60;background:rgba(255,255,255,.96);backdrop-filter:blur(10px);-webkit-backdrop-filter:blur(10px);border-bottom:1px solid var(--line)}
.bar{display:flex;align-items:center;gap:26px;min-height:80px}
.brand{display:flex;align-items:center;gap:12px;text-decoration:none;flex:none}
.brand .mk{font-family:var(--serif);font-weight:700;font-size:27px;letter-spacing:.14em;color:var(--navy)}
.brand .kj{font-size:12px;font-weight:700;line-height:1;color:var(--poppy);border:1.5px solid var(--poppy);padding:5px 6px;letter-spacing:.06em}
form.search{flex:1;max-width:480px;display:flex;border-bottom:1.5px solid var(--ink);background:transparent}
form.search:focus-within{border-bottom-color:var(--blue)}
form.search input{flex:1;background:transparent;border:0;color:var(--ink);padding:10px 4px;font-size:14.5px;min-width:0;outline:none}
form.search input::placeholder{color:var(--muted)}
form.search button{background:transparent;color:var(--blue);border:0;padding:0 4px 0 14px;font-weight:700;cursor:pointer;font-size:12px;letter-spacing:.14em;text-transform:uppercase}
form.search button:hover{color:var(--poppy)}
.tools{display:flex;align-items:center;gap:12px;margin-left:auto;flex:none}
.tools>span{border-radius:var(--r)!important}
select.pick{appearance:none;background-color:#fff;border:1px solid var(--line);border-radius:var(--r);color:var(--ink);
  padding:8px 28px 8px 12px;font-size:13px;cursor:pointer;
  background-image:linear-gradient(45deg,transparent 50%,var(--muted) 50%),linear-gradient(135deg,var(--muted) 50%,transparent 50%);
  background-position:calc(100% - 15px) 53%,calc(100% - 11px) 53%;background-size:4px 4px;background-repeat:no-repeat}
.cartbtn{display:flex;align-items:center;gap:10px;background:var(--blue);color:#fff;text-decoration:none;border-radius:var(--r);
  padding:11px 16px;font-size:12px;font-weight:700;letter-spacing:.14em;text-transform:uppercase;transition:background .15s}
.cartbtn:hover{background:var(--navy)}
.cartbtn b{background:#fff;color:var(--blue);border-radius:50%;width:22px;height:22px;display:grid;place-items:center;letter-spacing:0;font-size:12px}

/* category bar */
.catbar{border-top:1px solid var(--hair)}
.catbar .wrap{display:flex;gap:0;overflow-x:auto;scrollbar-width:none}
.catbar .wrap>a:first-child{margin-left:auto}.catbar .wrap>a:last-child{margin-right:auto}
@media(min-width:1101px){.catbar .wrap{flex-wrap:wrap;justify-content:center;overflow:visible}.catbar .wrap>a:first-child,.catbar .wrap>a:last-child{margin:0}.catbar a{padding:11px 10px}}
.catbar .wrap::-webkit-scrollbar{display:none}
.catbar a{padding:13px 10px;text-decoration:none;font-size:11.5px;font-weight:700;color:var(--ink2);white-space:nowrap;letter-spacing:.08em;text-transform:uppercase;position:relative}
.catbar a::after{content:"";position:absolute;left:12px;right:12px;bottom:6px;height:2px;background:var(--poppy);transform:scaleX(0);transition:transform .18s}
.catbar a:hover{color:var(--ink)}.catbar a:hover::after{transform:scaleX(.6)}
.catbar a.on{color:var(--blue)}.catbar a.on::after{transform:scaleX(1)}
@media(max-width:820px){form.search{order:3;max-width:none;flex-basis:100%;margin-bottom:12px}.bar{flex-wrap:wrap;padding-top:12px;gap:14px}}
@media(max-width:520px){
  .bar{gap:10px;min-height:64px}.brand{gap:8px}.brand .mk{font-size:21px;letter-spacing:.1em}.brand .kj{font-size:10.5px;padding:4px 5px}
  select.pick{padding:7px 22px 7px 10px;font-size:12.5px;background-position:calc(100% - 12px) 53%,calc(100% - 8px) 53%}
  .cartbtn{padding:9px 11px;font-size:11px;letter-spacing:.08em}.tools{gap:6px}
  .strip .st{display:none}.strip .fship~.sl{display:none}.strip .wrap{justify-content:center;min-height:32px}
  .catbar a{padding:12px 10px;font-size:11.5px}
}

/* crumbs */
.crumbs{font-size:12.5px;color:var(--muted);padding:22px 0 0}
.crumbs a{text-decoration:none;color:var(--ink2)}.crumbs a:hover{color:var(--poppy)}

/* buttons */
.btn{display:inline-block;text-align:center;text-decoration:none;border:1.5px solid var(--blue);background:var(--blue);color:#fff;border-radius:var(--r);
  padding:13px 26px;font-size:12.5px;font-weight:700;letter-spacing:.14em;text-transform:uppercase;cursor:pointer;transition:background .15s,color .15s,border-color .15s}
.btn:hover{background:var(--navy);border-color:var(--navy)}
.btn.g{background:transparent;color:var(--ink);border-color:var(--ink)}
.btn.g:hover{background:var(--ink);color:#fff}
.btn.gold{background:var(--poppy);border-color:var(--poppy);color:#fff}
.btn.gold:hover{background:#B9281F;border-color:#B9281F}
.btn.wide{width:100%;padding:16px}
.btn:disabled{opacity:.45;cursor:not-allowed}

section{padding:72px 0}
.sechead{display:flex;justify-content:space-between;align-items:flex-end;gap:20px;margin-bottom:30px;flex-wrap:wrap}
.sechead h2{font-size:clamp(28px,3.4vw,42px)}
.sechead h2::after{content:"";display:block;width:56px;height:2px;background:var(--gold);margin-top:14px}
.sechead h1{font-size:clamp(32px,4vw,50px);margin:0}
.sechead p{color:var(--muted);font-size:15px;margin-top:12px;max-width:64ch}
.sechead>a{font-size:12px;font-weight:700;color:var(--blue);text-decoration:none;white-space:nowrap;letter-spacing:.14em;text-transform:uppercase;border-bottom:1.5px solid currentColor;padding-bottom:3px}
.sechead>a:hover{color:var(--poppy)}
.sechead .count{color:var(--muted);font-size:14px}
section.top{padding-top:30px}
.sub2{font-size:clamp(22px,2.6vw,30px);margin:48px 0 18px}

/* hero */
.hero{padding:70px 0 80px;position:relative;overflow:hidden;background:linear-gradient(180deg,var(--cream),#fff 70%)}
.hero::before{content:"";position:absolute;inset:0;background:radial-gradient(circle at 85% 30%,rgba(30,63,174,.08),transparent 45%),radial-gradient(circle at 10% 90%,rgba(224,53,43,.06),transparent 40%);pointer-events:none}
.hgrid{display:grid;grid-template-columns:1.05fr .95fr;gap:70px;align-items:center;position:relative;z-index:0}
@media(max-width:960px){.hgrid{grid-template-columns:1fr;gap:44px}}
.eyebrow{display:flex;flex-wrap:wrap;gap:6px 22px;margin-bottom:24px}
.eyebrow span{font-size:11.5px;font-weight:700;letter-spacing:.16em;text-transform:uppercase;color:var(--muted);display:flex;align-items:center;gap:8px}
.eyebrow span::before{content:"";width:14px;height:1.5px;background:var(--line)}
.eyebrow span:first-child{color:var(--poppy)}.eyebrow span:first-child::before{background:var(--poppy)}
h1{font-size:clamp(36px,5.2vw,66px);margin-bottom:22px}
.hero h1{font-weight:700;letter-spacing:-.015em;color:var(--navy)}
.lede{font-size:18px;color:var(--ink2);max-width:56ch;margin-bottom:32px}
.hero-cta{display:flex;gap:12px;flex-wrap:wrap;margin-bottom:42px}
.stats{display:grid;grid-template-columns:repeat(3,auto);justify-content:start;gap:12px 0;font-size:12px;color:var(--muted);letter-spacing:.06em;text-transform:uppercase;border-top:1px solid var(--line);padding-top:20px}
.stats>div{padding:0 30px 0 0;margin-right:30px;border-right:1px solid var(--line)}.stats>div:last-child{border-right:0}
@media(max-width:560px){.stats{grid-template-columns:1fr 1fr;gap:16px 0}.stats>div:nth-child(2n){border-right:0}}
.stats strong{display:block;font-family:var(--serif);font-size:28px;color:var(--navy);letter-spacing:0;text-transform:none}
.heroart{position:relative;aspect-ratio:4/3;background:#fff;border:1px solid var(--line)}
.heroart::before{content:"";position:absolute;inset:14px -14px -14px 14px;border:1.5px solid var(--gold);z-index:-1}
.heroart::after{content:"";position:absolute;left:0;right:0;bottom:0;height:4px;background:var(--tri)}
.heroart img,.heroart .ph{width:100%;height:100%;object-fit:cover}
.heroart.light{background:#fff}
.heroart.light img{object-fit:contain;padding:22px;background:#fff}
.heroimg{display:block;width:100%;height:100%}

/* featured release */
.feature{position:relative;background:var(--navy);color:#C9D1E6}
.feature h2{color:#fff}.feature .ftext p{color:#C9D1E6}
.feature .btn.g{color:#fff;border-color:rgba(255,255,255,.6)}.feature .btn.g:hover{background:#fff;color:var(--navy)}
.fgrid{display:grid;grid-template-columns:230px 1fr 400px;gap:48px;align-items:center}
@media(max-width:1060px){.fgrid{grid-template-columns:200px 1fr}.fbox{grid-column:1 / -1}}
@media(max-width:640px){.fgrid{grid-template-columns:1fr;gap:24px}.fpack{max-width:200px;margin:0 auto}}
.fpack{display:block;overflow:hidden;box-shadow:0 20px 50px rgba(0,0,0,.35);outline:1.5px solid var(--gold);outline-offset:8px;transition:transform .2s}
.fpack:hover{transform:translateY(-3px)}
.kicker{display:inline-block;font-size:11.5px;font-weight:700;letter-spacing:.18em;text-transform:uppercase;color:var(--gold);margin-bottom:14px}
.ftext h2{font-size:clamp(30px,3.8vw,48px);margin-bottom:14px}
.ftext p{font-size:16.5px;max-width:52ch}
.fbox{margin:0;background:#fff}
.fbox figcaption{background:#fff;color:var(--muted);font-size:13px;padding:11px 14px;border-top:1px solid var(--line)}

/* placeholder art (until photos are uploaded): blue fleur pattern with a tricolour band */
.ph{width:100%;height:100%;display:flex;align-items:center;justify-content:center;flex-direction:column;gap:12px;text-align:center;padding:14px;
  color:var(--muted);font-size:11.5px;font-weight:700;letter-spacing:.14em;text-transform:uppercase;position:relative;
  background:radial-gradient(circle,rgba(30,63,174,.08) 1.5px,transparent 1.6px) 0 0/16px 16px,linear-gradient(180deg,#F7F8FC,#EEF1F8)}
.ph::after{content:"";position:absolute;left:0;right:0;bottom:0;height:3px;background:var(--tri)}
.ph span:first-child{font-family:var(--serif);font-size:24px;line-height:1;color:var(--navy);background:#fff;border:1.5px solid var(--gold);padding:12px 14px;letter-spacing:.12em;text-transform:none}

/* category tiles */
.cats{display:grid;grid-template-columns:repeat(5,1fr);gap:0;border-top:1px solid var(--line);border-left:1px solid var(--line)}
@media(max-width:1000px){.cats{grid-template-columns:repeat(2,1fr)}}
@media(max-width:520px){.cats{grid-template-columns:1fr}}
.cats.three{grid-template-columns:repeat(3,1fr)}
@media(max-width:700px){.cats.three{grid-template-columns:1fr}}
.cats a{background:#fff;border-right:1px solid var(--line);border-bottom:1px solid var(--line);padding:26px 22px 28px;text-decoration:none;display:block;transition:background .15s;position:relative}
.cats a::before{content:"";position:absolute;left:0;top:0;right:0;height:3px;background:var(--blue);transform:scaleX(0);transform-origin:left;transition:transform .2s}
.cats a:hover{background:var(--card2)}.cats a:hover::before{transform:scaleX(1)}
.cats a:hover h3{color:var(--blue)}
.cats .n{font-size:11px;color:var(--poppy);font-weight:700;letter-spacing:.16em;text-transform:uppercase}
.cats h3{font-size:22px;margin:12px 0 8px}
.cats p{font-size:13.5px;color:var(--ink2)}

/* chips (collections) */
.chips{display:flex;flex-wrap:wrap;gap:8px}
.chips a{padding:9px 16px;border:1px solid var(--line);border-radius:999px;text-decoration:none;font-size:13.5px;font-weight:600;color:var(--ink);background:#fff;transition:border-color .15s,color .15s}
.chips a:hover{border-color:var(--blue);color:var(--blue)}

/* product grid */
.grid{display:grid;grid-template-columns:repeat(4,1fr);gap:28px 22px}
@media(max-width:1040px){.grid{grid-template-columns:repeat(2,1fr)}}
@media(max-width:520px){.grid{grid-template-columns:1fr}}
.card{background:#fff;display:flex;flex-direction:column;border:1px solid transparent;transition:border-color .15s,box-shadow .15s}
.card:hover{border-color:var(--line);box-shadow:0 12px 30px rgba(16,24,40,.08)}
.card .art{aspect-ratio:1/1;position:relative;overflow:hidden;text-decoration:none;display:block;background:var(--card2)}
.card .art img{width:100%;height:100%;object-fit:cover;transition:transform .4s}
.card:hover .art img{transform:scale(1.03)}
.flag{position:absolute;top:12px;left:12px;font-size:10px;font-weight:700;letter-spacing:.16em;padding:5px 9px;background:#fff;color:var(--green);z-index:2}
.flag.new{color:var(--blue)}
.flag.pre{color:#fff;background:var(--poppy)}
.flag.low{color:var(--poppy)}
.flag.out{color:var(--muted)}
.card .in{padding:16px 16px 10px;display:flex;flex-direction:column;gap:6px;flex:1}
.card h3{font-family:var(--serif);font-size:17px;font-weight:700;line-height:1.3}
.card h3 a{text-decoration:none}.card h3 a:hover{color:var(--blue)}
.card .meta{font-size:11.5px;color:var(--muted);letter-spacing:.08em;text-transform:uppercase}
.card .px{display:flex;align-items:baseline;gap:8px;margin-top:auto;flex-wrap:wrap;padding-top:10px}
.card .px .u{font-size:21px;font-weight:800;color:var(--navy)}
.card .px .w{font-size:12.5px;color:var(--muted);text-decoration:line-through}
.card .px .per{font-size:12px;color:var(--muted)}
.card .drop{font-size:12.5px;color:var(--ink2)}
.card .drop b{color:var(--poppy)}
.card form{padding:0 16px 16px;display:flex;gap:8px}
.card form .btn{flex:1;padding:11px;font-size:11.5px}

/* catalogue filters */
.filters{display:flex;flex-wrap:wrap;gap:10px 16px;align-items:flex-end;background:var(--card2);padding:18px;margin-bottom:28px}
.filters label{display:flex;flex-direction:column;gap:6px;font-size:10.5px;font-weight:700;letter-spacing:.14em;color:var(--muted);flex:1 1 150px;min-width:0;text-transform:uppercase}
.filters select,.filters input{background:#fff;border:1px solid var(--line);border-radius:var(--r);padding:9px 10px;font-size:14px;font-weight:400;color:var(--ink);width:100%;text-transform:none;letter-spacing:0}
.filters .price span{display:flex;gap:6px}
.filters .fbtns{display:flex;gap:8px}
.filters .fbtns .btn{padding:11px 16px;font-size:11.5px}
@media(max-width:560px){.filters label{flex-basis:calc(50% - 8px)}.filters label.price{flex-basis:100%}}

/* qty stepper */
.step{display:flex;border:1px solid var(--ink);border-radius:var(--r);overflow:hidden;background:#fff}
.step button{background:transparent;border:none;padding:6px 12px;cursor:pointer;font-weight:700;color:var(--ink)}
.step button:hover{background:var(--card2)}
.step input{width:50px;border:none;border-inline:1px solid var(--line);background:transparent;text-align:center;padding:6px 0;font-size:14px;color:var(--ink)}

/* product page */
.pdp{display:grid;grid-template-columns:1.02fr .98fr;gap:60px;padding:28px 0 10px}
@media(max-width:900px){.pdp{grid-template-columns:1fr;gap:30px}}
.gal-main{aspect-ratio:1/1;overflow:hidden;background:var(--card2);position:relative}
.gal-main img{width:100%;height:100%;object-fit:cover}
.gal-thumbs{display:flex;gap:10px;margin-top:12px}
.gal-thumbs button{width:74px;height:74px;border:1px solid var(--line);overflow:hidden;padding:0;cursor:pointer;background:#fff}
.gal-thumbs button[aria-current="true"]{border:2px solid var(--blue)}
.gal-thumbs img{width:100%;height:100%;object-fit:cover}
.pdp h1{font-size:clamp(30px,3.6vw,44px);margin-bottom:12px}
.pdp .sub{font-size:11.5px;color:var(--muted);margin-bottom:18px;letter-spacing:.12em;text-transform:uppercase}
.pdp .summary-line{color:var(--ink2);font-size:16.5px;margin-bottom:24px;max-width:60ch}
.ladder{border:1px solid var(--line);margin-bottom:18px}
.ladder .lh{padding:11px 16px;background:var(--card2);font-size:10.5px;font-weight:700;letter-spacing:.16em;text-transform:uppercase;color:var(--ink2)}
.ladder .row{display:flex;justify-content:space-between;padding:11px 16px;font-size:14.5px;border-top:1px solid var(--hair);color:var(--ink2)}
.ladder .lh+.row{border-top:0}
.ladder .row.on{color:var(--ink);font-weight:700;background:#EEF2FD;box-shadow:inset 3px 0 0 var(--blue)}
.ladder .row:last-child span:last-child{color:var(--poppy);font-weight:700}
.buybox{border:1px solid var(--ink);padding:24px;background:#fff;position:relative}
.buybox::after{content:"";position:absolute;left:-1px;right:-1px;top:-1px;height:4px;background:var(--tri)}
.buybox .big{font-family:var(--serif);font-size:46px;font-weight:700;line-height:1.05;color:var(--navy)}
.buybox .sm{font-size:13.5px;color:var(--muted);margin-bottom:14px}
.buybox form{display:flex;gap:10px;align-items:center;margin-top:12px;flex-wrap:wrap}
.buybox form .btn{flex:1;min-width:150px}
.trustline{display:flex;gap:6px 18px;flex-wrap:wrap;font-size:12.5px;margin-top:16px}
.trustline span{color:var(--teal);display:flex;align-items:center;gap:6px}
.trustline span::before{content:"✓";font-weight:800}
.pinfo{display:grid;grid-template-columns:1.2fr .8fr;gap:28px;margin-top:14px}
@media(max-width:900px){.pinfo{grid-template-columns:1fr}}
.panel{background:#fff;border:1px solid var(--line);padding:26px}
.panel h2{font-size:24px;margin-bottom:14px;padding-bottom:12px;border-bottom:1px solid var(--hair)}
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
.tbl{width:100%;border-collapse:separate;border-spacing:0;background:#fff;border:1px solid var(--line)}
.tbl th{text-align:left;font-size:10.5px;letter-spacing:.14em;text-transform:uppercase;color:var(--muted);font-weight:700;padding:12px 14px;border-bottom:1px solid var(--ink);background:#fff}
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
fieldset{border:1px solid var(--line);padding:24px;margin:0 0 20px;background:#fff}
legend{font-family:var(--serif);font-weight:700;font-size:21px;padding:0 10px;color:var(--navy)}
.fld{margin-bottom:14px}
.fld label{display:block;font-size:11px;font-weight:700;color:var(--ink2);margin-bottom:6px;letter-spacing:.12em;text-transform:uppercase}
.fld input,.fld select,.fld textarea{width:100%;background:#fff;border:1px solid var(--line);border-radius:var(--r);padding:12px 13px;font-size:15px;color:var(--ink)}
.fld input:focus,.fld select:focus,.fld textarea:focus{border-color:var(--blue);outline:none;box-shadow:0 0 0 3px rgba(30,63,174,.1)}
.fld textarea{min-height:82px;resize:vertical}
.two{display:grid;grid-template-columns:1fr 1fr;gap:14px}
@media(max-width:560px){.two{grid-template-columns:1fr}}
.pay{display:grid;gap:9px}
.pay label{display:flex;gap:11px;align-items:flex-start;border:1px solid var(--line);border-radius:var(--r);padding:13px 15px;cursor:pointer;background:#fff}
.pay label:has(input:checked){border-color:var(--blue);background:#EEF2FD;box-shadow:inset 3px 0 0 var(--blue)}
.pay label[hidden]{display:none}
.pay input{margin-top:4px;accent-color:var(--blue)}
.pay .t{font-weight:700;font-size:14.5px;color:var(--ink)}
.pay .n{font-size:12.5px;color:var(--muted)}
.notice{border-left:3px solid var(--blue);background:#F1F4FC;padding:13px 16px;font-size:13.5px;color:var(--ink2);margin:14px 0}
.notice a{color:var(--link)}
.agree{display:flex;gap:10px;align-items:flex-start;font-size:13.5px;color:var(--ink2);margin:14px 0 4px}
.agree input{margin-top:4px;accent-color:var(--blue)}
.summary{border:1px solid var(--ink);background:#fff;padding:22px;position:sticky;top:150px}
.summary::before{content:"";display:block;height:4px;margin:-22px -22px 18px;background:var(--tri)}
.summary h3{font-size:22px;margin-bottom:12px;padding-bottom:10px;border-bottom:1px solid var(--hair)}
.sl{display:flex;justify-content:space-between;gap:12px;font-size:13.5px;padding:8px 0;border-bottom:1px solid var(--hair)}
.sl:last-of-type{border-bottom:none}
.sl .q{color:var(--muted);font-size:12px}
.tot{display:flex;justify-content:space-between;font-family:var(--serif);font-size:24px;font-weight:700;padding-top:12px;margin-top:8px;border-top:2px solid var(--ink);color:var(--navy)}
.errs{border:1px solid var(--poppy);padding:14px 16px;margin-bottom:20px;background:#FDF0EF;font-size:14px;color:var(--ink)}
.errs ul{margin:6px 0 0;padding-left:18px}
.hp{position:absolute;left:-9999px;width:1px;height:1px;overflow:hidden}
.minwarn{border-left:3px solid var(--poppy);background:#FDF0EF;padding:11px 14px;font-size:13.5px;margin-top:12px}
.minwarn[hidden]{display:none}

/* confirmation */
.done{max-width:720px;margin:0 auto;text-align:center;padding:24px 0}
.done .ref{font-family:var(--serif);font-size:38px;letter-spacing:.08em;margin:14px 0 6px;color:var(--blue)}
.done .card2{border:1px solid var(--line);background:#fff;padding:28px;text-align:left;margin-top:26px}
.done ol{padding-left:20px;margin:12px 0 0}
.done li{margin-bottom:10px;font-size:14.5px;color:var(--ink2)}

/* feature band */
.deep{position:relative;background:var(--cream);border-block:1px solid var(--line)}
.deep .wrap{position:relative}
.deep h2{color:var(--navy);font-size:clamp(28px,3.4vw,42px)}.deep p{color:var(--ink2);margin-top:14px;font-size:15.5px}
.deep .two2{display:grid;grid-template-columns:1fr 1fr;gap:56px;align-items:center}
@media(max-width:860px){.deep .two2{grid-template-columns:1fr;gap:26px}}
.spec{border:1px solid var(--line);background:#fff}
.spec div{display:flex;justify-content:space-between;gap:12px;padding:12px 18px;font-size:14px;border-bottom:1px solid var(--hair)}
.spec div:last-child{border-bottom:none}
.spec span:first-child{color:var(--muted)}.spec span:last-child{font-weight:700;color:var(--ink);text-align:right}

/* steps */
.steps{display:grid;grid-template-columns:repeat(4,1fr);gap:0;border-top:1px solid var(--ink)}
@media(max-width:900px){.steps{grid-template-columns:1fr 1fr}}
@media(max-width:540px){.steps{grid-template-columns:1fr}}
.steps>div{padding:24px 24px 26px 0;margin-right:24px;border-right:1px solid var(--line)}
.steps>div:last-child{border-right:0}
@media(max-width:900px){.steps>div{border-right:0;border-bottom:1px solid var(--line)}}
.steps .n{font-family:var(--serif);font-size:40px;font-weight:700;margin-bottom:6px;color:var(--poppy);display:inline-block;font-style:italic}
.steps h3{font-size:21px;margin-bottom:7px}
.steps p{font-size:14px;color:var(--ink2)}

/* faq */
.faq details{border-bottom:1px solid var(--line);padding:18px 0}
.faq summary{cursor:pointer;font-family:var(--serif);font-weight:700;font-size:19px;list-style:none;display:flex;justify-content:space-between;gap:14px;color:var(--ink)}
.faq summary::-webkit-details-marker{display:none}
.faq summary::after{content:"+";color:var(--poppy);font-size:24px;line-height:1;font-family:var(--sans);font-weight:300}
.faq details[open] summary::after{content:"–"}
.faq p{margin-top:10px;font-size:15px;color:var(--ink2);max-width:80ch}

/* footer */
footer.site{background:var(--navy);color:#AEB8D0;padding:60px 0 30px;margin-top:48px;position:relative}
footer.site::before{content:"";position:absolute;left:0;right:0;top:0;height:4px;background:var(--tri)}
footer .brand .mk{color:#fff}
footer .brand .kj{color:#fff;border-color:rgba(255,255,255,.6)}
.fg{display:grid;grid-template-columns:1.4fr repeat(4,1fr);gap:28px}
@media(max-width:860px){.fg{grid-template-columns:1fr 1fr}}
@media(max-width:520px){.fg{grid-template-columns:1fr}}
footer h4{font-size:11px;letter-spacing:.18em;text-transform:uppercase;color:var(--gold);margin:0 0 14px}
footer ul{list-style:none;margin:0;padding:0;display:grid;gap:8px}
footer a{color:#D5DCEC;text-decoration:none;font-size:14px}
footer a:hover{color:#fff;text-decoration:underline}
footer .bl{font-size:14px;color:#9FAAC4;margin-top:14px;max-width:44ch}
.legal{margin-top:36px;padding-top:18px;border-top:1px solid rgba(255,255,255,.12);font-size:12.5px;color:#8F9BB8;display:grid;gap:9px}
.empty{padding:40px 0;color:var(--muted)}
.empty a{color:var(--link)}

/* long-form text */
.prose{max-width:740px;color:var(--ink2);font-size:17px;line-height:1.78}
.prose>:first-child{margin-top:0}
.prose h2{font-size:clamp(25px,2.6vw,32px);color:var(--navy);margin:44px 0 12px;padding-top:6px}
.prose h3{font-size:21px;color:var(--ink);margin:28px 0 8px}
.prose p{margin:0 0 16px}
.prose ul{margin:0 0 16px;padding-left:0;list-style:none}
.prose li{margin-bottom:8px;padding-left:22px;position:relative}
.prose li::before{content:"";position:absolute;left:3px;top:.72em;width:8px;height:1.5px;background:var(--poppy)}
.prose a{color:var(--link);font-weight:600;text-decoration:none;border-bottom:1px solid rgba(30,63,174,.3)}
.prose a:hover{color:var(--poppy);border-bottom-color:var(--poppy)}
.prose strong{color:var(--ink)}
.prose.lead{margin-bottom:30px}
.prose.after{margin-top:56px;padding-top:32px;border-top:1px solid var(--line)}

/* set tiles and guide cards */
.tiles{display:grid;grid-template-columns:repeat(auto-fill,minmax(170px,1fr));gap:12px}
.tiles a{background:#fff;border:1px solid var(--line);padding:16px 18px;text-decoration:none;display:flex;flex-direction:column;gap:3px;transition:border-color .15s}
.tiles a:hover{border-color:var(--blue)}
.tiles a:hover b{color:var(--blue)}
.tiles b{font-family:var(--serif);font-size:17px;line-height:1.3;color:var(--ink)}
.tiles .n{font-size:12px;color:var(--muted)}
.tiles .n:first-child{color:var(--poppy);font-weight:700;letter-spacing:.12em}
.gcards{display:grid;grid-template-columns:repeat(auto-fill,minmax(270px,1fr));gap:0;border-top:1px solid var(--line);border-left:1px solid var(--line)}
.gcards a{background:#fff;border-right:1px solid var(--line);border-bottom:1px solid var(--line);padding:26px;text-decoration:none;display:flex;flex-direction:column;gap:10px;transition:background .15s}
.gcards a:hover{background:var(--card2)}
.gcards a:hover h3{color:var(--blue)}
.gcards h3{font-size:21px;color:var(--ink)}
.gcards p{font-size:14px;color:var(--ink2)}
.gcards span{margin-top:auto;font-size:11px;font-weight:700;color:var(--poppy);letter-spacing:.16em;text-transform:uppercase}

/* guides */
.article{max-width:780px}
.article h1{font-size:clamp(32px,4.4vw,52px);margin-bottom:14px;color:var(--navy)}
.article .meta{font-size:11.5px;color:var(--muted);margin-bottom:26px;letter-spacing:.12em;text-transform:uppercase}
.toc{border-top:1px solid var(--ink);border-bottom:1px solid var(--line);padding:18px 0;margin:0 0 32px}
.toc b{display:block;font-size:10.5px;letter-spacing:.18em;text-transform:uppercase;color:var(--poppy);margin-bottom:8px}
.toc ol{margin:0;padding-left:20px;display:grid;gap:5px}
.toc a{color:var(--link);text-decoration:none;font-size:14.5px}
.toc a:hover{color:var(--poppy);text-decoration:underline}

/* free shipping: top bar, cart and checkout */
.strip .fship{display:inline-flex;align-items:center;gap:7px;color:#fff;font-weight:700}
.strip .fship svg{color:var(--gold);flex:none}
.strip .fship:hover span{text-decoration:underline}
.fsm{margin:10px 0 12px;max-width:420px;margin-left:auto;text-align:left}
.fsm .t{font-size:13.5px;color:var(--ink2);margin-bottom:7px}.fsm .t b{color:var(--ink)}
.fsm .meter{height:4px;background:var(--hair);overflow:hidden}
.fsm .meter i{display:block;height:100%;background:var(--blue)}
.fsm.c{max-width:none;margin:12px 0 0}

/* Bitcoin payment page */
.payhead{margin-bottom:24px}
.payhead .kick{font-size:11.5px;color:var(--muted);letter-spacing:.12em;text-transform:uppercase}.payhead .kick b{color:var(--poppy)}
.payhead h1{font-size:clamp(30px,3.8vw,46px);margin:6px 0 10px}
.payhead .lede{margin-bottom:0}
.paygrid{display:grid;grid-template-columns:minmax(0,1fr) 360px;gap:28px;align-items:start}
@media(max-width:960px){.paygrid{grid-template-columns:1fr}.paygrid .summary{position:static}}
.paybox{border:1px solid var(--ink);background:#fff;padding:26px;position:relative;box-shadow:inset 0 3px 0 #F7931A}
.payrow{display:grid;grid-template-columns:250px minmax(0,1fr);gap:26px;align-items:start}
@media(max-width:640px){.payrow{grid-template-columns:1fr}.qrcol{max-width:300px;margin:0 auto;width:100%}}
.qr{background:#fff;border:1px solid var(--line);padding:10px;aspect-ratio:1;display:grid;place-items:center;margin-bottom:12px}
.qr svg{width:100%;height:100%;display:block}
.qr .qrph{color:#666;font-size:13px}
.pf .l{font-size:10.5px;letter-spacing:.16em;text-transform:uppercase;color:var(--muted);font-weight:700;margin-bottom:6px}
.pf .v{display:flex;align-items:center;gap:10px;flex-wrap:wrap}
.pf .amt span{font-family:var(--serif);font-size:clamp(28px,3.6vw,36px);font-weight:700;color:var(--ink);font-variant-numeric:tabular-nums}
.pf .amt small{font-size:16px;font-weight:800;color:#C36A12}
.pf .n{font-size:13px;color:var(--muted);margin-top:6px}
.pf .addr code{font-family:ui-monospace,SFMono-Regular,Menlo,Consolas,monospace;font-size:14.5px;color:var(--ink);background:var(--card2);border:1px solid var(--line);padding:9px 11px;word-break:break-all;flex:1;min-width:0}
.copy{background:#fff;border:1px solid var(--ink);color:var(--ink);border-radius:var(--r);padding:7px 14px;font-size:11px;font-weight:700;letter-spacing:.12em;text-transform:uppercase;cursor:pointer;white-space:nowrap}
.copy:hover{background:var(--ink);color:#fff}
.hold{font-size:13.5px;color:var(--ink2);margin-top:16px}.hold b{color:var(--poppy);font-variant-numeric:tabular-nums}
.watch{display:flex;align-items:center;gap:10px;font-size:13.5px;color:var(--teal);margin-top:14px;padding:11px 13px;background:#ECF6F4;border:1px solid #CBE5E0}
.pulse{width:9px;height:9px;border-radius:50%;background:var(--teal);flex:none;box-shadow:0 0 0 0 rgba(15,118,110,.6);animation:pulse 1.8s infinite}
@keyframes pulse{70%{box-shadow:0 0 0 10px rgba(15,118,110,0)}100%{box-shadow:0 0 0 0 rgba(15,118,110,0)}}
.paytips{list-style:none;padding:0;margin:22px 0 0;display:grid;gap:9px;font-size:13.5px;color:var(--ink2)}
.paytips li{padding-left:22px;position:relative}.paytips b{color:var(--ink)}
.paytips li::before{content:"";position:absolute;left:3px;top:.72em;width:8px;height:1.5px;background:#F7931A}
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
.timeline li.now::before{border-color:var(--blue);animation:pulse 1.8s infinite}
.timeline b{color:var(--ink);font-size:15.5px}.timeline span{font-size:13.5px;color:var(--muted)}
.txbox{margin-top:8px;padding:14px;background:var(--card2);border:1px solid var(--line);display:grid;gap:6px}
.txbox .l{font-size:10.5px;letter-spacing:.16em;text-transform:uppercase;color:var(--muted);font-weight:700}
.txbox code{font-family:ui-monospace,Menlo,Consolas,monospace;font-size:12.5px;color:var(--ink);word-break:break-all}
.txbox a{color:var(--link);font-weight:700;font-size:14px;text-decoration:none}
.summary .n{font-size:12.5px;color:var(--muted);margin-top:10px}.summary .n a{color:var(--link)}

/* guide links on category, set, collection and FAQ pages */
.glinks{margin-top:8px}
.gchips{display:flex;flex-wrap:wrap;gap:8px}
.gchips a{border:1px solid var(--line);background:#fff;border-radius:999px;padding:9px 16px;font-size:13.5px;font-weight:600;color:var(--ink);text-decoration:none}
.gchips a:hover{border-color:var(--blue);color:var(--blue)}

/* guide tools */
.pcheck{display:flex;align-items:center;gap:12px;flex-wrap:wrap;margin:6px 0 12px}
.pcheck label{font-weight:800;color:var(--ink)}
.pcheck input{flex:1;min-width:220px;background:#fff;border:1px solid var(--line);border-radius:var(--r);padding:11px 14px;font-size:15px;color:var(--ink)}
.pcheck input:focus{border-color:var(--blue);outline:none}
.pcheck span{font-size:13px;color:var(--muted)}
.tbl.pc .sk{font-weight:500}
.tplbox{display:grid;grid-template-columns:200px minmax(0,1fr);gap:22px;align-items:center;border:1px solid var(--line);background:#fff;padding:18px;margin:6px 0 20px}
.tplbox img{width:100%;height:auto;background:#fff;border:1px solid var(--line)}
.tplbox b{color:var(--ink);font-size:18px;font-family:var(--serif)}.tplbox p{margin:8px 0 0}
.prose .tplbox .btn{color:#fff;border-bottom:1.5px solid var(--blue);margin:4px 6px 0 0}.prose .tplbox .btn.g{color:var(--ink);border-bottom-color:var(--ink)}
@media(max-width:560px){.tplbox{grid-template-columns:1fr}.tplbox img{max-width:220px}}

/* Shipping & Returns */
.srhead h1{font-size:clamp(32px,4.2vw,50px);color:var(--navy)}
.srhead .lede{margin:12px 0 28px}
.srcards{display:grid;grid-template-columns:repeat(4,1fr);gap:0;margin-bottom:46px;border-top:1px solid var(--ink);border-bottom:1px solid var(--line)}
@media(max-width:900px){.srcards{grid-template-columns:1fr 1fr}}
@media(max-width:520px){.srcards>div{padding:14px 10px 14px 0}.srcards .ic{width:30px;height:30px}.srcards b{font-size:15px}}
.srcards>div{--c:var(--blue);padding:22px 20px 24px 0;margin-right:20px;display:grid;gap:4px;border-right:1px solid var(--line)}
.srcards>div:last-child{border-right:0}
@media(max-width:900px){.srcards>div:nth-child(2n){border-right:0}}
.srcards .k2{--c:var(--teal)}.srcards .k3{--c:var(--poppy)}.srcards .k4{--c:var(--gold)}
.srcards .ic{width:36px;height:36px;display:grid;place-items:center;color:var(--c);border:1.5px solid currentColor;border-radius:50%;margin-bottom:8px}
.srcards .ic svg{width:18px;height:18px}
.srcards b{color:var(--ink);font-size:17px;font-family:var(--serif)}.srcards span:last-child{font-size:13.5px;color:var(--ink2)}
.srgrid{display:grid;grid-template-columns:230px minmax(0,1fr);gap:56px;align-items:start}
.srtoc{position:sticky;top:160px;border-left:2px solid var(--blue);padding-left:16px}
.srtoc b{font-size:10.5px;letter-spacing:.18em;text-transform:uppercase;color:var(--poppy)}
.srtoc ol{list-style:none;margin:10px 0 0;padding:0;display:grid;gap:2px}
.srtoc a{display:block;padding:5px 0;font-size:14px;color:var(--ink2);text-decoration:none}
.srtoc a:hover{color:var(--blue)}
@media(max-width:960px){
  .srgrid{grid-template-columns:minmax(0,1fr);gap:10px}
  .srtoc{position:static;border:none;padding:0;min-width:0}
  .srtoc ol{display:flex;gap:8px;overflow-x:auto;scrollbar-width:none;padding-bottom:6px}
  .srtoc a{white-space:nowrap;border:1px solid var(--line);border-radius:999px;padding:6px 13px;font-size:13px;background:#fff}
}
.prose.sr{max-width:780px}
.prose.sr h2{scroll-margin-top:160px;padding-top:8px}
.prose.sr h3{scroll-margin-top:160px}
.prose ol{margin:0 0 16px;padding:0;list-style:none;counter-reset:step}
.prose ol li{counter-increment:step;padding-left:42px;margin-bottom:12px;min-height:28px}
.prose ol li::before{content:counter(step);position:absolute;left:0;top:.05em;width:28px;height:28px;background:transparent;border:1.5px solid var(--blue);border-radius:50%;
  color:var(--blue);font-weight:700;font-size:13px;display:grid;place-items:center;font-family:var(--serif)}
.prose .tblwrap{overflow-x:auto;margin:0 0 16px}
.prose .tbl small{color:var(--muted);font-weight:500}
.prose .tbl .free{color:var(--green)}
.prose p.small{font-size:13.5px;color:var(--muted)}
.prose code{font-family:ui-monospace,Menlo,Consolas,monospace;font-size:.9em;color:var(--ink);background:var(--card2);padding:2px 6px;word-break:break-all}
.srcontact{margin-top:46px;padding:24px;display:flex;justify-content:space-between;align-items:center;gap:16px;flex-wrap:wrap;background:var(--navy);color:#C9D1E6;position:relative}
.srcontact::before{content:"";position:absolute;left:0;right:0;top:0;height:4px;background:var(--tri)}
.srcontact b{display:block;color:#fff;font-size:20px;font-family:var(--serif)}.srcontact span{font-size:14px;color:#C9D1E6}
.srcontact span a{color:#fff}
.srcontact .row2{display:flex;gap:10px;flex-wrap:wrap}
.prose .srcontact .btn{color:#fff;border-bottom:1.5px solid var(--blue);background:var(--poppy);border-color:var(--poppy)}
.prose .srcontact .btn.g{background:transparent;color:#fff;border-color:rgba(255,255,255,.6)}

@media (prefers-reduced-motion:reduce){*{transition:none!important;animation:none!important}}
</style>
</head>
<body>

<div class="strip"><div class="wrap">
  <?php if(free_ship_usd($STORE)): ?><a class="fship" href="<?= url('shipping') ?>"><svg viewBox="0 0 24 24" width="17" height="17" aria-hidden="true"><path fill="currentColor" d="M3 6.5A1.5 1.5 0 0 1 4.5 5h9A1.5 1.5 0 0 1 15 6.5V8h2.6a1.5 1.5 0 0 1 1.2.6l2.4 3.2c.2.26.3.58.3.9V16a1.5 1.5 0 0 1-1.5 1.5h-.6a2.75 2.75 0 0 1-5.3 0H9.9a2.75 2.75 0 0 1-5.3 0h-.1A1.5 1.5 0 0 1 3 16V6.5Zm12 3V13h4.5l-1.9-2.5a1.5 1.5 0 0 0-1.2-.6H15ZM7.25 18.25a1 1 0 1 0 0-2 1 1 0 0 0 0 2Zm9.4 0a1 1 0 1 0 0-2 1 1 0 0 0 0 2Z"/></svg><span>Livraison offerte dès <?= money_whole(free_ship_usd($STORE)) ?> d’achat</span></a><?php endif; ?>
  <span class="st"><?= h($CONFIG['strip_text']) ?></span>
  <?php if($CONFIG['strip_link_text']): ?><a class="sl" href="<?= h($CONFIG['strip_link_url'] ?: url('catalog')) ?>"><?= h($CONFIG['strip_link_text']) ?></a><?php endif; ?>
</div></div>

<header class="site">
  <div class="wrap bar">
    <a class="brand" href="<?= h(url('home')) ?>">
      <span class="mk"><?= h($CONFIG['brand']) ?></span>
      <span class="kj"><?= h($CONFIG['kanji']) ?></span>
    </a>
    <form class="search" action="<?= empty($CONFIG['pretty_urls']) ? 'index.php' : 'boutique' ?>" method="get" role="search">
      <?php if(empty($CONFIG['pretty_urls'])): ?><input type="hidden" name="p" value="catalog"><?php endif; ?>
      <input type="search" name="q" value="<?= h($q) ?>" placeholder="Rechercher une carte, une extension…" aria-label="Rechercher un produit">
      <button type="submit">Rechercher</button>
    </form>
    <div class="tools">
      <?php if(count($CURRENCIES) > 1): ?><select class="pick" onchange="location.href=this.value" aria-label="Devise">
        <?php $here_args = $_GET; unset($here_args['p'], $here_args['id'], $here_args['s'], $here_args['c'], $here_args['g'], $here_args['pg']);
        if($from_path && isset($from_path['cat'])) unset($here_args['cat']);
        $here = $page === 'notfound' ? 'home' : $page;
        foreach($CURRENCIES as $code=>$m):
          $u = $here_args; $u['cur'] = $code;
          $u += ($canon_args[$here] ?? []); ?>
          <option value="<?= h(url($here, $u)) ?>" <?= cur_code()===$code?'selected':'' ?>><?= h($code) ?></option>
        <?php endforeach; ?>
      </select><?php else: ?><span style="border:1px solid var(--line);border-radius:4px;padding:8px 12px;font-size:12.5px;color:var(--ink2);letter-spacing:.06em" title="Prix en <?= h(cur_code()) ?>"><?= h(cur_code()) ?></span><?php endif; ?>
      <a class="cartbtn" href="<?= url('cart') ?>">Panier <b><?= cart_units() ?></b></a>
    </div>
  </div>
  <nav class="catbar" aria-label="Catégories"><div class="wrap">
    <?php if(info_page('revendeurs')): ?><a href="<?= h(url('page', ['pg'=>'revendeurs'])) ?>" class="<?= $page==='page'&&($info['slug'] ?? '')==='revendeurs'?'on':'' ?>">Revendeurs</a><?php endif; ?>
    <a href="<?= url('catalog') ?>" class="<?= $page==='catalog'&&!$cat?'on':'' ?>">Tous les produits</a>
    <?php foreach($CATEGORIES as $k=>$c): ?>
      <a href="<?= url('catalog',['cat'=>$k]) ?>" class="<?= $cat===$k?'on':'' ?>"><?= h($c['label']) ?></a>
    <?php endforeach; ?>
    <a href="<?= url('sets') ?>" class="<?= in_array($page, ['sets','series','set'], true)?'on':'' ?>">Extensions</a>
    <?php if($GUIDES): ?><a href="<?= url('guides') ?>" class="<?= in_array($page, ['guides','guide'], true)?'on':'' ?>">Guides</a><?php endif; ?>
    <a href="<?= url('shipping') ?>" class="<?= $page==='shipping'?'on':'' ?>">Livraison &amp; retours</a>
    <a href="<?= url('faq') ?>" class="<?= $page==='faq'?'on':'' ?>">FAQ</a>
    <a href="<?= url('contact') ?>" class="<?= $page==='contact'?'on':'' ?>">Contact</a>
  </div></nav>
</header>

<main>
<?php if(!empty($_SESSION['flash'])): ?>
  <div class="wrap"><div class="notice" role="status"><?php foreach($_SESSION['flash'] as $msg) echo '<div>'.h($msg).'</div>'; ?></div></div>
<?php unset($_SESSION['flash']); endif; ?>
<?php if(count($crumbs) > 1): ?>
  <nav class="wrap crumbs" aria-label="Fil d’Ariane"><?php foreach($crumbs as $i=>[$label, $pg, $args]):
    echo $i ? ' / ' : '';
    echo $pg !== '' ? '<a href="'.h(url($pg, $args)).'">'.h($label).'</a>' : '<span aria-current="page">'.h($label).'</span>';
  endforeach; ?></nav>
<?php endif; ?>
<?php if($page==='home'): ?>

  <section class="hero"><div class="wrap hgrid">
    <div>
      <div class="eyebrow"><span>Direct du Japon</span><span>Scellé &amp; authentique</span><span>Livré en France</span><span>Prix dégressifs</span></div>
      <h1><?= h($CONFIG['hero_title']) ?></h1>
      <p class="lede"><?= h($CONFIG['hero_lede']) ?></p>
      <div class="hero-cta">
        <a class="btn" href="<?= url('catalog') ?>">Voir les cartes Pokémon</a>
        <a class="btn g" href="<?= h(info_page('revendeurs') ? url('page', ['pg'=>'revendeurs']) : url('how')) ?>"><?= info_page('revendeurs') ? 'Offre revendeurs' : 'Comment commander' ?></a>
      </div>
      <div class="stats">
        <div><strong><?= count($in_stock) ?></strong>références en stock</div>
        <?php if(count($COUNTRIES) === 1 && free_ship_usd($STORE)): ?><div><strong><?= money_whole(free_ship_usd($STORE)) ?></strong>livraison offerte dès</div>
        <?php else: ?><div><strong><?= count($COUNTRIES) ?></strong>pays livrés</div><?php endif; ?>
        <div><strong><?= (int)$CONFIG['hold_hours'] ?> h</strong>de réservation du stock</div>
      </div>
    </div>
    <div class="heroart<?= photos('hero') ? '' : ' light' ?>">
      <?php $hp = photos('hero');
      if($hp): ?><?= img_tag($hp[0], 'Displays Pokémon japonais scellés prêts à partir du Japon', '(max-width:960px) 100vw, 600px', true) ?>
      <?php elseif(is_file(FK_ROOT.'/'.SITE_HERO)): ?><a href="<?= h(url('product', ['id'=>'etb-30th-celebration'])) ?>" class="heroimg"><?= img_tag(SITE_HERO, 'Coffret Dresseur d’Élite Pokémon 30th Celebration', '(max-width:960px) 100vw, 600px', true) ?></a>
      <?php else: ?><div class="ph"><span><?= h($CONFIG['kanji']) ?></span><span>Cartes Pokémon japonaises · expédiées du Japon</span></div><?php endif; ?>
    </div>
  </div></section>

  <section>
    <div class="wrap">
      <div class="sechead"><div><h2>Nos catégories</h2>
        <p>Displays scellés, coffrets Dresseur d’Élite, coffrets collector, cartes rares et gradées, et tout pour les ranger.</p></div>
        <a href="<?= url('catalog') ?>">Tout voir →</a></div>
      <div class="cats">
        <?php foreach($CATEGORIES as $k=>$c):
          $n = count(array_filter($PRODUCTS, fn($p)=>$p['cat']===$k)); ?>
          <a href="<?= url('catalog',['cat'=>$k]) ?>">
            <div class="n"><?= $n ?> produit<?= $n > 1 ? 's' : '' ?></div><h3><?= h($c['label']) ?></h3><p><?= h($c['blurb']) ?></p>
          </a>
        <?php endforeach; ?>
      </div>
    </div>
  </section>

  <?php $pop = array_filter($COLLECTIONS, fn($c)=>collection_products($c)); $s151 = set_by_slug('151'); ?>
  <?php if($pop || $s151): ?>
  <section style="padding-top:0"><div class="wrap">
    <div class="chips" aria-label="Populaires">
      <?php foreach($pop as $c): ?><a href="<?= h(url('collection', ['c'=>$c['slug']])) ?>"><?= h($c['title']) ?></a><?php endforeach; ?>
      <?php if($s151): ?><a href="<?= h(url('set', ['s'=>'151'])) ?>">Cartes Pokémon 151</a><?php endif; ?>
      <?php foreach($SERIES as $k=>$sr): if(series_sets($k)): ?><a href="<?= h(url('series', ['s'=>$sr['slug']])) ?>">Extensions <?= h($sr['name']) ?></a><?php endif; endforeach; ?>
    </div>
  </div></section>
  <?php endif; ?>

  <section>
    <div class="wrap">
      <div class="sechead"><div><h2>En stock</h2>
        <p>Prêts à partir du Japon, avec des prix dégressifs affichés sur chaque produit.</p></div>
        <a href="<?= url('catalog') ?>">Tous les produits →</a></div>
      <div class="grid">
        <?php foreach(array_slice($in_stock,0,8) as $p) include_card($p); ?>
      </div>
    </div>
  </section>

  <?php $f30 = set_by_slug('30th-celebration'); $etb30 = product('etb-30th-celebration');
  if($f30 && is_file(FK_ROOT.'/assets/site/pokemon-30th-celebration-booster-pack.webp')): ?>
  <section class="feature"><div class="wrap fgrid">
    <a class="fpack" href="<?= h(url('set', ['s'=>$f30['slug']])) ?>"><?= img_tag('assets/site/pokemon-30th-celebration-booster-pack.webp', 'Booster Pokémon 30th Celebration avec Pikachu, Mew et Mewtwo', '(max-width:860px) 55vw, 260px') ?></a>
    <div class="ftext">
      <span class="kicker">30 ans de Pokémon</span>
      <h2>Pokémon JCC : 30th Celebration</h2>
      <p>Trente ans de Pokémon en une extension. Mewtwo-ex et Mew-ex mènent la danse, avec Noctali-ex, Drattak-ex et Amphinobi-ex, et chaque booster contient un Pikachu, parmi 30 cartes Pikachu rares à collectionner.</p>
      <div class="hero-cta" style="margin:22px 0 0">
        <a class="btn gold" href="<?= h(url('set', ['s'=>$f30['slug']])) ?>">Voir 30th Celebration</a>
        <?php if($etb30): ?><a class="btn g" href="<?= h(url('product', ['id'=>$etb30['id']])) ?>">Coffret Dresseur d’Élite</a><?php endif; ?>
      </div>
    </div>
    <figure class="fbox">
      <?= img_tag('assets/site/pokemon-30th-celebration-elite-trainer-box-contents.webp', 'Contenu du Coffret Dresseur d’Élite Pokémon 30th Celebration', '(max-width:860px) 100vw, 420px') ?>
      <figcaption>Dans le Coffret Dresseur d’Élite : 9 boosters, une carte promo Nidorina full art, 65 protège-cartes, des dés et plus encore.</figcaption>
    </figure>
  </div></section>
  <?php endif; ?>

  <?php $home_sets = sets_all(); if($home_sets): ?>
  <section style="padding-top:0"><div class="wrap">
    <div class="sechead"><div><h2>Par extension</h2>
      <p>Les extensions japonaises Méga-Évolution, et les incontournables Écarlate et Violet comme 151.</p></div>
      <a href="<?= url('sets') ?>">Toutes les extensions →</a></div>
    <?php set_tiles($home_sets); ?>
  </div></section>
  <?php endif; ?>

  <section class="deep"><div class="wrap two2">
    <div>
      <h2>Scellé d’usine, comme au Japon.</h2>
      <p>Tout est acheté auprès de la distribution japonaise et expédié tel qu’il est sorti d’usine. Aucun produit reconditionné, réimprimé ou contrefait, et le prix affiché est le prix facturé.</p>
      <p style="margin-top:22px"><a class="btn gold" href="<?= url('how') ?>">Comment commander</a>
        <?php if(info_page('a-propos')): ?><a class="btn g" href="<?= h(url('page', ['pg'=>'a-propos'])) ?>" style="margin-left:8px">À propos de <?= h($CONFIG['brand']) ?></a><?php endif; ?></p>
    </div>
    <div class="spec">
      <div><span>Approvisionnement</span><span>Distribution japonaise</span></div>
      <div><span>Produits scellés</span><span>À l’unité ou par lot</span></div>
      <div><span>Cartes à l’unité</span><span>Dès 1 exemplaire</span></div>
      <div><span>Réservation du stock</span><span><?= (int)$CONFIG['hold_hours'] ?> heures</span></div>
      <div><span>Expédition après paiement</span><span>Sous <?= (int)$CONFIG['hold_hours'] ?> heures</span></div>
      <?php if(free_ship_usd($STORE)): ?><div><span>Livraison offerte</span><span>Dès <?= money_whole(free_ship_usd($STORE)) ?> d’achat</span></div><?php endif; ?>
      <?php $nbtc = count(array_filter($PAYMENTS, 'btc_method')); if($nbtc): ?><div><span>Paiement</span><span>Bitcoin sur le site<?= count($PAYMENTS) > $nbtc ? ' · ou '.(count($PAYMENTS) - $nbtc).' autres moyens' : '' ?></span></div><?php endif; ?>
      <div><span>Transporteurs</span><span>EMS · DHL · FedEx</span></div>
      <?php $SM = ship_methods($STORE); ?><div><span>Livraison</span><span><?= h($SM['standard']['label'].' '.$SM['standard']['days'].' · '.$SM['express']['label'].' '.$SM['express']['days']) ?></span></div>
    </div>
  </div></section>

  <section><div class="wrap">
    <div class="sechead"><div><h2>Quatre étapes, du panier à votre porte</h2></div></div>
    <?php steps_block($CONFIG); ?>
  </div></section>

  <?php if($GUIDES): ?>
  <section style="padding-top:0"><div class="wrap">
    <div class="sechead"><div><h2>Guides des cartes Pokémon</h2>
      <p>Prix et cote, cartes les plus chères et les plus rares, Dracaufeu, raretés et cartes japonaises.</p></div>
      <a href="<?= url('guides') ?>">Les <?= count($GUIDES) ?> guides →</a></div>
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
      <p><?= $cat ? h($CATEGORIES[$cat]['blurb']) : 'Displays scellés, coffrets Dresseur d’Élite, cartes rares et accessoires, avec les prix dégressifs affichés sur chaque produit.' ?></p>
    </div><span style="color:var(--muted);font-size:14px"><?= count($list) ?> produit<?= count($list) > 1 ? 's' : '' ?></span></div>
    <form class="filters" method="get" action="<?= empty($CONFIG['pretty_urls']) ? 'index.php' : 'boutique' ?>" id="filters" onsubmit="fsub(this); return false">
      <?php if(empty($CONFIG['pretty_urls'])): ?><input type="hidden" name="p" value="catalog"><?php endif; ?>
      <?php if($q !== ''): ?><input type="hidden" name="q" value="<?= h($q) ?>"><?php endif; ?>
      <label>Type de produit<select name="cat" onchange="fsub(this.form)">
        <option value="">Tous les types</option>
        <?php foreach($CATEGORIES as $k=>$c): ?><option value="<?= h($k) ?>" <?= $cat===$k?'selected':'' ?>><?= h($c['label']) ?> (<?= (int)($cats_n[$k] ?? 0) ?>)</option><?php endforeach; ?>
      </select></label>
      <label>Extension<select name="set" onchange="fsub(this.form)">
        <option value="">Toutes les extensions</option>
        <?php foreach($sets as $k=>$n): ?><option value="<?= h($k) ?>" <?= $f_set===(string)$k?'selected':'' ?>><?= h($k) ?> (<?= (int)$n ?>)</option><?php endforeach; ?>
      </select></label>
      <label>État<select name="cond" onchange="fsub(this.form)">
        <option value="">Tous les états</option>
        <?php foreach(CONDITIONS as $k): ?><option value="<?= h($k) ?>" <?= $f_cond===$k?'selected':'' ?>><?= h(cond_fr($k)) ?> (<?= (int)($conds[$k] ?? 0) ?>)</option><?php endforeach; ?>
      </select></label>
      <label>Disponibilité<select name="avail" onchange="fsub(this.form)">
        <option value="">En stock et précommande</option>
        <option value="in" <?= $f_avail==='in'?'selected':'' ?>>En stock (<?= $n_in ?>)</option>
        <option value="preorder" <?= $f_avail==='preorder'?'selected':'' ?>>Précommande (<?= $n_pre ?>)</option>
      </select></label>
      <label class="price">Prix unitaire (<?= h(cur_code()) ?>)<span>
        <input type="number" name="min" min="0" step="any" placeholder="Min" value="<?= $f_min===null?'':h($f_min) ?>" aria-label="Prix minimum">
        <input type="number" name="max" min="0" step="any" placeholder="Max" value="<?= $f_max===null?'':h($f_max) ?>" aria-label="Prix maximum"></span></label>
      <label>Trier<select name="sort" onchange="fsub(this.form)">
        <?php foreach([''=>'Sélection','price-asc'=>'Prix croissant','price-desc'=>'Prix décroissant','name'=>'Nom de A à Z'] as $k=>$lbl): ?><option value="<?= h($k) ?>" <?= $f_sort===$k?'selected':'' ?>><?= h($lbl) ?></option><?php endforeach; ?>
      </select></label>
      <div class="fbtns"><button class="btn" type="submit">Filtrer</button><?php if($filtered || $cat): ?><a class="btn g" href="<?= url('catalog') ?>">Effacer</a><?php endif; ?></div>
    </form>
    <?php if($list): ?>
      <div class="grid"><?php foreach($list as $p) include_card($p); ?></div>
    <?php else: ?>
      <p class="empty">Aucun résultat. Essayez un nom d’extension ou de carte, ou <a href="<?= url('catalog') ?>">parcourez toute la boutique</a>.</p>
    <?php endif; ?>
    <?php if($cinfo && !$filtered && trim($cinfo['intro'] ?? '') !== ''): ?><div class="prose after"><?= rich($cinfo['intro']) ?></div><?php endif; ?>
    <?php if(!$filtered) guide_links(PAGE_GUIDES[$cinfo ? 'cat:'.$cat : 'shop'] ?? [], $cinfo ? 'Guides : '.mb_strtolower($cinfo['label']) : 'Guides des cartes Pokémon'); ?>
  </div></section>

<?php elseif($page==='product'):
  $ph = photos($prod['id']); $base = unit_price($prod,$prod['moq']); $best = end($prod['ladder']);
  [$summary, $more] = desc_parts($prod['desc']);
  $sser = $pset ? ($SERIES[$pset['series']] ?? null) : null;
  $moq_tier = 0; foreach($prod['ladder'] as $i=>$t){ if($t[0] <= $prod['moq']) $moq_tier = $i; } ?>
  <div class="wrap pdp">
    <div>
      <div class="gal-main">
        <?php if($ph): ?><?= str_replace('<img ', '<img id="galMain" ', img_tag($ph[0], $prod['name'].' — carte Pokémon japonaise', '(max-width:900px) 100vw, 620px', true)) ?>
        <?php else: ?><div class="ph"><span><?= h($CONFIG['kanji']) ?></span><span><?= h($prod['set'] ?: $CATEGORIES[$prod['cat']]['label']) ?></span></div><?php endif; ?>
      </div>
      <?php if(count($ph)>1): ?>
      <div class="gal-thumbs">
        <?php foreach($ph as $i=>$src): ?>
          <button type="button" aria-current="<?= $i===0?'true':'false' ?>" onclick="galPick(this,'<?= h($src.'?v='.@filemtime(FK_ROOT.'/'.$src)) ?>')" aria-label="Voir la photo <?= $i+1 ?>">
            <img src="<?= h(thumb($src, 160)) ?>" width="72" height="72" alt="<?= h($prod['name']) ?>, photo <?= $i+1 ?>" loading="lazy"></button>
        <?php endforeach; ?>
      </div>
      <?php endif; ?>
    </div>
    <div>
      <h1><?= h($prod['name']) ?></h1>
      <div class="sub"><?= h(implode(' · ', array_filter([$prod['set'], $CATEGORIES[$prod['cat']]['label'], cond_fr($prod['cond'] ?? 'Sealed'),
        $prod['status']==='preorder' ? 'Sortie '.$prod['release'] : status_label(FR_STATUS, $prod['status'])]))) ?></div>
      <?php if($summary !== ''): ?><p class="summary-line"><?= rich_inline($summary) ?></p><?php endif; ?>

      <div class="ladder">
        <div class="lh">Prix dégressifs</div>
        <?php foreach($prod['ladder'] as $i=>$t):
          $next = $prod['ladder'][$i+1] ?? null;
          $hi   = $next ? $next[0]-1 : null;
          /* a tier wholly below MOQ is only a reference price */
          $lbl  = ($hi!==null && $hi < $prod['moq']) ? 'À l’unité'
                : ($hi===null ? $t[0].' unités et plus' : ($hi===$t[0] ? $t[0].($t[0]===1?' unité':' unités') : 'De '.$t[0].' à '.$hi.' unités')); ?>
          <div class="row <?= $i===$moq_tier?'on':'' ?>">
            <span><?= h($lbl) ?></span><span><?= money($t[1]) ?></span></div>
        <?php endforeach; ?>
      </div>

      <div class="buybox">
        <div class="big"><?= money($base) ?></div>
        <div class="sm">l’unité dès <?= h($prod['moq']) ?> exemplaire<?= $prod['moq'] > 1 ? 's' : '' ?><?php if(count($prod['ladder']) > 1 && $best[1] < $base): ?> · jusqu’à <?= money($best[1]) ?> dès <?= h($best[0]) ?><?php endif; ?></div>
        <form method="post" action="index.php">
          <input type="hidden" name="action" value="add">
          <input type="hidden" name="id" value="<?= h($prod['id']) ?>">
          <?php if(can_order($prod)): stepper($prod, 'qty', $prod['moq'], $prod['moq']); ?>
          <button class="btn" type="submit"><?= $prod['status']==='preorder'?'Précommander':'Ajouter au panier' ?></button>
          <?php else: ?>
          <button class="btn" type="submit" disabled>Épuisé</button>
          <?php endif; ?>
        </form>
        <div class="trustline">
          <span><?= $prod['step']>1 ? 'Par lot de '.(int)$prod['step'] : 'Vendu à l’unité' ?></span>
          <span>Réservé <?= (int)$CONFIG['hold_hours'] ?> h</span>
          <span>Expédié du Japon</span>
        </div>
      </div>
    </div>
  </div>

  <section class="top"><div class="wrap pinfo">
    <div class="panel">
      <h2>Description</h2>
      <div class="prose"><?= $more !== '' ? rich($more) : '<p>'.rich_inline($summary).'</p>' ?></div>
    </div>
    <div style="display:grid;gap:16px;align-content:start">
      <div class="panel"><h2>Caractéristiques</h2>
        <dl class="specs">
          <?php if($pset): ?><dt>Extension</dt><dd><a href="<?= h(url('set', ['s'=>$pset['slug']])) ?>"><?= h($pset['name']) ?></a></dd><?php endif; ?>
          <?php if($pset && $pset['code'] !== ''): ?><dt>Code de l’extension</dt><dd><?= h($pset['code']) ?></dd><?php endif; ?>
          <?php if($sser): ?><dt>Série</dt><dd><a href="<?= h(url('series', ['s'=>$sser['slug']])) ?>"><?= h($sser['name']) ?></a></dd><?php endif; ?>
          <dt>Type de produit</dt><dd><a href="<?= h(url('catalog', ['cat'=>$prod['cat']])) ?>"><?= h($CATEGORIES[$prod['cat']]['label']) ?></a></dd>
          <dt>État</dt><dd><?= h(cond_fr($prod['cond'] ?? 'Sealed')) ?></dd>
          <dt>Disponibilité</dt><dd><?= h($prod['status']==='preorder' ? 'Précommande · sortie '.$prod['release'] : status_label(FR_STATUS, $prod['status'])) ?></dd>
          <dt>Quantité</dt><dd>Dès <?= (int)$prod['moq'] ?><?= $prod['step'] > 1 ? ', puis par '.(int)$prod['step'] : '' ?></dd>
          <?php if($prod['sku'] !== ''): ?><dt>Référence</dt><dd><?= h($prod['sku']) ?></dd><?php endif; ?>
          <dt>Expédition</dt><dd>Depuis le Japon, avec suivi</dd>
        </dl>
      </div>
      <div class="panel"><h2>Livraison et paiement</h2>
        <div class="prose" style="font-size:14.5px">
          <?php $SM = ship_methods($STORE); $one = (float)($prod['weight'] ?? 0) * $prod['moq']; ?>
          <p>Expédié du Japon avec suivi : <b><?= h($SM['standard']['label']) ?></b> <?= h($SM['standard']['days']) ?> ou <b><?= h($SM['express']['label']) ?></b> <?= h($SM['express']['days']) ?>.
            Tarif au poids : <?= (int)$prod['moq'] ?> exemplaire<?= $prod['moq'] > 1 ? 's' : '' ?> vers la <?= h($COUNTRIES[$HOME_CC] ?? $HOME_CC) ?> coûte<?= $prod['moq'] > 1 ? 'nt' : '' ?> <?= money(shipping_usd($STORE, $HOME_CC, $one, 'standard')) ?> en <?= h($SM['standard']['label']) ?> ou <?= money(shipping_usd($STORE, $HOME_CC, $one, 'express')) ?> en <?= h($SM['express']['label']) ?>.
            <?php if(free_ship_usd($STORE)): ?><b>Livraison <?= h($SM['standard']['label']) ?> offerte dès <?= money_whole(free_ship_usd($STORE)) ?> d’achat.</b><?php endif; ?></p>
          <p><?php if(array_filter($PAYMENTS, 'btc_method')): ?>Payez en Bitcoin juste après votre commande, ou choisissez un autre moyen : nous vous envoyons les coordonnées sous <?= (int)$CONFIG['reply_hours'] ?> heures.<?php else: ?>Nous vous envoyons les coordonnées de paiement sous <?= (int)$CONFIG['reply_hours'] ?> heures.<?php endif; ?>
            <a href="<?= url('shipping') ?>">Livraison &amp; retours</a> · <a href="<?= url('payment') ?>">Moyens de paiement</a> · <a href="<?= url('how') ?>">Comment commander</a><?php if(info_page('revendeurs')): ?> · <a href="<?= h(url('page', ['pg'=>'revendeurs'])) ?>">Offre revendeurs</a><?php endif; ?> · <a href="<?= url('faq') ?>">FAQ</a> · <a href="<?= url('contact') ?>">Nous contacter</a></p>
        </div>
      </div>
      <?php $pg = guides_for($prod['cat']); if($pg): ?>
      <div class="panel"><h2>Guides utiles</h2>
        <div class="plinks"><?php foreach($pg as $g): ?><a href="<?= h(url('guide', ['g'=>$g['slug']])) ?>"><?= h(guide_anchor($g)) ?> →</a><?php endforeach; ?></div>
      </div>
      <?php endif; ?>
    </div>
  </div></section>

  <?php $sibs = $pset ? array_values(array_filter(set_products($pset['name']), fn($x)=>$x['id'] !== $prod['id'])) : [];
  if($sibs): ?>
  <section><div class="wrap">
    <div class="sechead"><div><h2>Aussi dans <?= h($pset['name']) ?></h2></div>
      <a href="<?= h(url('set', ['s'=>$pset['slug']])) ?>">Tout <?= h($pset['name']) ?> →</a></div>
    <div class="grid"><?php foreach(array_slice($sibs, 0, 4) as $p) include_card($p); ?></div>
  </div></section>
  <?php endif; ?>
  <section<?= $sibs ? ' style="padding-top:0"' : '' ?>><div class="wrap">
    <div class="sechead"><div><h2>Dans la même catégorie : <?= h($CATEGORIES[$prod['cat']]['label']) ?></h2></div>
      <a href="<?= url('catalog',['cat'=>$prod['cat']]) ?>">Tout voir →</a></div>
    <div class="grid">
      <?php $rel = array_slice(array_values(array_filter($PRODUCTS, fn($x)=>$x['cat']===$prod['cat'] && $x['id']!==$prod['id'] && !in_array($x, $sibs, true))),0,4);
      if(!$rel) $rel = array_slice(array_values(array_filter($PRODUCTS, fn($x)=>$x['id']!==$prod['id'])),0,4);
      foreach($rel as $p) include_card($p); ?>
    </div>
  </div></section>

<?php elseif($page==='cart'): $lines = cart_lines(); ?>
  <section style="padding-top:22px"><div class="wrap">
    <div class="sechead"><div><h1><?= h($h1) ?></h1>
      <p>Les prix dégressifs s’appliquent automatiquement : modifiez une quantité et le prix unitaire se met à jour.</p></div></div>
    <?php if(!$lines): ?>
      <p class="empty">Votre panier est vide. <a href="<?= url('catalog') ?>">Voir la boutique →</a></p>
    <?php else: ?>
      <form method="post" action="index.php">
        <input type="hidden" name="action" value="update">
        <table class="tbl">
          <thead><tr><th>Produit</th><th>Quantité</th><th class="r">Prix unitaire</th><th class="r">Total</th><th></th></tr></thead>
          <tbody>
          <?php foreach($lines as $l): ?>
            <tr>
              <td><div class="nm"><a href="<?= url('product',['id'=>$l['p']['id']]) ?>" style="text-decoration:none"><?= h($l['p']['name']) ?></a></div>
                  <div class="sk"><?= h($l['p']['sku']) ?><?= $l['p']['step'] > 1 ? ' · par lot de '.h($l['p']['step']) : '' ?></div></td>
              <td><?php stepper($l['p'], 'qty['.$l['p']['id'].']', $l['qty'], 0); ?></td>
              <td class="r"><?= money($l['unit']) ?></td>
              <td class="r"><b><?= money($l['total']) ?></b></td>
              <td class="r"><button class="btn g" style="padding:7px 12px;font-size:13px"
                    formaction="index.php" name="action" value="remove" type="submit"
                    onclick="this.form.insertAdjacentHTML('beforeend','<input type=hidden name=id value=\'<?= h($l['p']['id']) ?>\'>')">Retirer</button></td>
            </tr>
          <?php endforeach; ?>
          </tbody>
        </table>
        <div style="display:flex;justify-content:space-between;gap:18px;flex-wrap:wrap;margin-top:20px;align-items:center">
          <button class="btn g" type="submit">Mettre à jour</button>
          <div style="text-align:right">
            <?php if(cart_saved()>0): ?><div style="font-size:13.5px;color:var(--muted)">Vous économisez <?= money(cart_saved()) ?> par rapport au prix à l’unité</div><?php endif; ?>
            <div style="font-size:26px;font-weight:900;margin:4px 0 4px">Sous-total <?= money(cart_total()) ?></div>
            <?php free_ship_meter(cart_total()); ?>
            <div style="font-size:13.5px;color:var(--muted);margin-bottom:10px">Frais de livraison calculés à l’étape suivante.</div>
            <a class="btn" href="<?= url('checkout') ?>">Passer la commande</a>
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
      <p>Indiquez l’adresse de livraison et votre moyen de paiement. <?= array_filter($PAYMENTS, 'btc_method') ? 'En Bitcoin, vous payez sur la page suivante, directement depuis votre portefeuille. Pour les autres moyens, nous vous envoyons' : 'Nous vous envoyons' ?> les coordonnées de paiement et votre facture par e-mail ou SMS après la commande.</p></div></div>

    <?php if(!$lines): ?>
      <p class="empty">Votre panier est vide. <a href="<?= url('catalog') ?>">Voir la boutique →</a></p>
    <?php else: ?>
    <?php if($errors): ?>
      <div class="errs"><b>Merci de corriger les points suivants :</b><ul><?php foreach($errors as $e) echo '<li>'.h($e).'</li>'; ?></ul></div>
    <?php endif; ?>

    <form method="post" action="index.php" class="cogrid">
      <input type="hidden" name="action" value="order">
      <input type="hidden" name="token" value="<?= h($_SESSION['co_token']) ?>">
      <div class="hp" aria-hidden="true"><label for="website">Laissez ce champ vide</label>
        <input id="website" name="website" tabindex="-1" autocomplete="off"></div>
      <div>
        <fieldset>
          <legend>Contact</legend>
          <div class="two">
            <div class="fld"><label for="name">Nom et prénom</label>
              <input id="name" name="name" required value="<?= h($f['name']??'') ?>"></div>
            <div class="fld"><label for="company">Société (facultatif)</label>
              <input id="company" name="company" value="<?= h($f['company']??'') ?>"></div>
          </div>
          <div class="two">
            <div class="fld"><label for="email">E-mail</label>
              <input id="email" name="email" type="email" required value="<?= h($f['email']??'') ?>"></div>
            <div class="fld"><label for="phone">Téléphone (pour recevoir le paiement par SMS)</label>
              <input id="phone" name="phone" type="tel" required value="<?= h($f['phone']??'') ?>"></div>
          </div>
        </fieldset>

        <fieldset>
          <legend>Adresse de livraison</legend>
          <div class="fld"><label for="country">Pays</label>
            <select id="country" name="country" required onchange="filterPay(this.value)">
              <option value="">Choisissez votre pays…</option>
              <?php foreach($COUNTRIES as $code=>$nm): ?>
                <option value="<?= h($code) ?>" <?= ($f['country']??'')===$code?'selected':'' ?>><?= h($nm) ?></option>
              <?php endforeach; ?>
            </select></div>
          <div class="fld"><label for="address1">Adresse</label>
            <input id="address1" name="address1" required value="<?= h($f['address1']??'') ?>"></div>
          <div class="fld"><label for="address2">Complément d’adresse (facultatif)</label>
            <input id="address2" name="address2" value="<?= h($f['address2']??'') ?>"></div>
          <div class="two">
            <div class="fld"><label for="postcode">Code postal</label>
              <input id="postcode" name="postcode" autocomplete="postal-code" value="<?= h($f['postcode']??'') ?>"></div>
            <div class="fld"><label for="city">Ville</label>
              <input id="city" name="city" required autocomplete="address-level2" value="<?= h($f['city']??'') ?>"></div>
          </div>
          <div class="fld" style="max-width:360px"><label for="region">Région (facultatif)</label>
            <input id="region" name="region" value="<?= h($f['region']??'') ?>"></div>
        </fieldset>

        <fieldset>
          <legend>Livraison</legend>
          <div class="pay" id="shipList">
            <?php foreach(ship_methods($STORE) as $mk=>$mm): ?>
              <label>
                <input type="radio" name="ship_method" value="<?= h($mk) ?>" <?= ($f['ship_method'] ?? 'standard')===$mk?'checked':'' ?>>
                <span style="flex:1"><span class="t"><?= h($mm['label']) ?></span><br><span class="n"><?= h($mm['days']) ?>, avec suivi</span></span>
                <span class="t" data-ship-price="<?= h($mk) ?>"><?php if(!empty($f['country']) && isset($COUNTRIES[$f['country']])){ $sv = shipping_usd($STORE, $f['country'], cart_weight(), $mk, cart_total()); echo $sv > 0 ? money($sv) : 'Offerte'; } ?></span>
              </label>
            <?php endforeach; ?>
          </div>
          <p class="n" style="font-size:12.5px;color:var(--muted);margin-top:10px">Tarif selon le poids de votre commande (<?= h(rtrim(rtrim(number_format(cart_weight(), 2, ',', ''), '0'), ',')) ?> kg).<?= count($COUNTRIES) > 1 ? ' Choisissez votre pays pour voir les tarifs.' : '' ?></p>
          <?php free_ship_meter(cart_total(), true); ?>
        </fieldset>

        <fieldset>
          <legend>Moyen de paiement</legend>
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
            <b>Comment se passe le paiement ?</b>
            <?php if(array_filter($PAYMENTS, 'btc_method')): ?>
            <b>Bitcoin :</b> validez la commande et payez sur la page suivante. Vous obtenez le montant exact et un QR code pour votre portefeuille, puis un reçu par e-mail dès que votre paiement apparaît sur la blockchain.
            <b>Autres moyens :</b> nous vous envoyons les coordonnées par e-mail et SMS sous <?= (int)$CONFIG['reply_hours'] ?> heures avec votre facture, et votre stock est réservé <?= (int)$CONFIG['hold_hours'] ?> heures.
            <?php else: ?>
            Choisissez votre moyen de paiement et validez la commande. Nous vous envoyons les coordonnées par e-mail et SMS sous <?= (int)$CONFIG['reply_hours'] ?> heures, avec votre facture. Votre stock est réservé <?= (int)$CONFIG['hold_hours'] ?> heures en attendant. Indiquez votre référence de commande avec le paiement.
            <?php endif; ?>
            Nous ne demandons jamais de numéro de carte, de mot de passe ni de clés de portefeuille.
          </div>
          <div class="fld"><label for="notes">Remarques (facultatif)</label>
            <textarea id="notes" name="notes" placeholder="Numéro de TVA ou SIRET pour votre facture, instructions de livraison, transporteur préféré…"><?= h($f['notes']??'') ?></textarea></div>
          <label class="agree">
            <input type="checkbox" name="agree" value="1" <?= !empty($_POST['agree'])?'checked':'' ?>>
            <span>J’accepte les <a href="<?= h(url('page', ['pg'=>'cgv'])) ?>" target="_blank">conditions générales de vente</a> et la
            <a href="<?= h(url('shipping')) ?>#retours" target="_blank">politique de livraison et de retours</a>.</span>
          </label>
          <div class="minwarn" id="minWarn" hidden></div>
          <button class="btn wide" id="placeBtn" type="submit" style="margin-top:14px" data-btc-label="Commander et payer en Bitcoin">Valider la commande</button>
          <p style="font-size:12.5px;color:var(--muted);margin-top:10px">
            Les frais de livraison sont calculés selon le poids de la commande et affichés dans le récapitulatif.</p>
        </fieldset>
      </div>

      <div>
        <div class="summary">
          <h3>Récapitulatif</h3>
          <?php foreach($lines as $l): ?>
            <div class="sl"><span><?= h($l['p']['name']) ?><br><span class="q"><?= h($l['qty']) ?> × <?= money($l['unit']) ?></span></span>
              <span><?= money($l['total']) ?></span></div>
          <?php endforeach; ?>
          <?php if(cart_saved()>0): ?>
            <div class="sl"><span style="color:var(--muted)">Remise quantité</span>
              <span style="color:var(--seal)">− <?= money(cart_saved()) ?></span></div>
          <?php endif; ?>
          <div class="sl"><span style="color:var(--muted)">Articles</span><span><?= money(cart_total()) ?></span></div>
          <div class="sl"><span style="color:var(--muted)" id="shipLabel">Livraison</span><span id="shipCost" style="color:var(--muted)">Choisissez votre pays</span></div>
          <div class="tot"><span>Total</span><span id="grandTotal"><?= money(cart_total()) ?></span></div>
          <?php
          /* per-country shipping for this cart, so the summary updates as the country changes */
          $cm = $CURRENCIES[cur_code()]; $kg = cart_weight(); $ship_by = [];
          foreach($COUNTRIES as $code=>$nm) foreach(SHIP_METHODS as $mk) $ship_by[$code][$mk] = shipping_usd($STORE, $code, $kg, $mk, cart_total());
          $labels = array_map(fn($m)=>$m['label'], ship_methods($STORE));
          $co_data = ['ship'=>$ship_by, 'labels'=>$labels, 'goods'=>cart_total(), 'min'=>$MIN_ORDER,
                      'btc'=>array_keys(array_filter($PAYMENTS, 'btc_method')),
                      'rate'=>$cm['rate'], 'sym'=>$cm['sym'], 'dec'=>$cm['dec']]; ?>
          <script>window.CO = <?= json_encode($co_data, JSON_HEX_TAG|JSON_HEX_AMP) ?>;</script>
          <p style="font-size:12.5px;color:var(--muted);margin-top:12px">Montants en euros (<?= cur_code() ?>). Votre facture est établie dans la même devise.</p>
          <p style="margin-top:12px"><a href="<?= url('cart') ?>" style="font-size:13.5px;font-weight:700;color:var(--brand);text-decoration:none">← Modifier le panier</a></p>
        </div>
      </div>
    </form>
    <?php endif; ?>
  </div></section>

<?php elseif($page==='received'): $o = $_SESSION['last_order'] ?? null; ?>
  <section><div class="wrap done">
    <?php if(!$o): ?>
      <h1>Aucune commande récente</h1>
      <p class="lede" style="margin:14px auto 22px">Rien à afficher ici. <a href="<?= url('catalog') ?>">Voir la boutique →</a></p>
    <?php else: ?>
      <div style="font-size:13px;color:var(--muted);letter-spacing:.08em">COMMANDE REÇUE</div>
      <div class="ref"><?= h($o['ref']) ?></div>
      <h1 style="font-size:clamp(24px,3.4vw,34px);margin-top:10px">Merci, votre commande est bien enregistrée.</h1>
      <p class="lede" style="margin:14px auto 0"><?php if($o['mail_customer'] ?? true): ?>Une confirmation vous a été envoyée à <b><?= h($o['email']) ?></b>.<?php else: ?>L’e-mail de confirmation n’a pas pu partir pour le moment : notez votre référence de commande. Votre commande est bien enregistrée et nous vous contacterons à <b><?= h($o['email']) ?></b>.<?php endif; ?> Votre stock est réservé <?= (int)$CONFIG['hold_hours'] ?> heures.</p>

      <div class="card2">
        <h3 style="font-size:19px">Et maintenant ?</h3>
        <ol>
          <li>Nous vous envoyons les coordonnées de paiement pour <b><?= h($o['payment_label']) ?></b> par e-mail et SMS sous <?= (int)$CONFIG['reply_hours'] ?> heures, avec votre facture.</li>
          <li>Indiquez la référence <b><?= h($o['ref']) ?></b> avec votre paiement.</li>
          <li>Dès réception du paiement, nous expédions depuis le Japon avec suivi sous <?= (int)$CONFIG['hold_hours'] ?> heures.</li>
          <li>Le numéro de suivi vous est envoyé par e-mail dès l’expédition.</li>
        </ol>
        <hr style="border:none;border-top:1px solid var(--hair);margin:20px 0">
        <div class="sl" style="border:none;padding:0"><span>Articles</span><span><?= h($o['goods']) ?></span></div>
        <div class="sl" style="border:none;padding:4px 0 0"><span>Livraison<?= !empty($o['ship_label']) ? ' · '.h($o['ship_label']) : '' ?></span><span><?= h($o['shipping']) ?></span></div>
        <div class="sl" style="border:none;padding:4px 0 0"><span>Total</span><b><?= h($o['total']) ?></b></div>
        <div class="sl" style="border:none;padding:4px 0 0"><span>Livraison à</span><span><?= h($o['city']) ?>, <?= h($o['country_name']) ?></span></div>
        <p style="font-size:13.5px;color:var(--muted);margin-top:16px">
          Pas de nouvelles sous <?= (int)$CONFIG['reply_hours'] ?> heures ? Vérifiez vos courriers indésirables, puis écrivez à
          <a href="mailto:<?= h($CONFIG['email']) ?>"><?= h($CONFIG['email']) ?></a> en indiquant <?= h($o['ref']) ?>.</p>
      </div>
      <p style="margin-top:24px"><a class="btn g" href="<?= url('catalog') ?>">Continuer mes achats</a></p>
    <?php endif; ?>
  </div></section>

<?php elseif($page==='pay'): $o = $pay; $b = $o['btc']; $st = btc_state($o); $key = order_key($o['ref']);
  $cancelled = ($o['status'] ?? '') === 'cancelled'; $need = btc_settings()['confs']; $conf = (int)($b['confirmations'] ?? 0);
  $open = !$cancelled && in_array($st, ['awaiting','reported'], true); $uri = btc_uri($o);
  $heads = ['awaiting'=>'Payer en Bitcoin', 'reported'=>'Vérification de votre paiement', 'seen'=>'Paiement reçu', 'confirmed'=>'Payé, merci !', 'short'=>'Paiement reçu']; ?>
  <section style="padding-top:22px"><div class="wrap">
    <div class="payhead">
      <div class="kick">Commande <b><?= h($o['ref']) ?></b> · <?= h($o['time']) ?></div>
      <h1><?= $cancelled ? 'Commande annulée' : h($heads[$st]) ?></h1>
      <?php if($open): ?><p class="lede">Votre commande est enregistrée et votre stock réservé. Payez depuis n’importe quel portefeuille Bitcoin : scannez le code, ou copiez le montant et l’adresse.</p>
      <?php elseif(!$cancelled): ?><p class="lede">Votre reçu a été envoyé à <b><?= h($o['email']) ?></b>. <?= $st === 'confirmed' ? 'Nous préparons votre commande et vous enverrons le numéro de suivi dès l’expédition.' : 'Votre paiement est sur la blockchain ; cette page se met à jour dès qu’il est confirmé.' ?></p><?php endif; ?>
    </div>

    <div class="paygrid">
      <div class="paybox" id="payBox" data-state="<?= h($st) ?>" data-quoted="<?= (int)($b['quoted'] ?? 0) ?>"
           data-status="<?= h(url('paystatus', ['ref'=>$o['ref'], 'k'=>$key])) ?>">
      <?php if($cancelled): ?>
        <p>Cette commande a été annulée : merci de ne pas envoyer de paiement. S’il s’agit d’une erreur, écrivez à <a href="mailto:<?= h($CONFIG['email']) ?>"><?= h($CONFIG['email']) ?></a>.</p>
      <?php elseif($open): ?>
        <div class="payrow">
          <div class="qrcol">
            <div class="qr" id="qr" data-uri="<?= h($uri) ?>"><span class="qrph">QR code</span></div>
            <a class="btn wide gold" href="<?= h($uri) ?>">Ouvrir dans mon portefeuille</a>
          </div>
          <div class="pf">
            <?php if(!empty($b['sats'])): ?>
              <div class="l">Envoyez exactement</div>
              <div class="v amt"><span><?= btc_amount($b['sats']) ?></span> <small>BTC</small>
                <button type="button" class="copy" data-copy="<?= btc_amount($b['sats']) ?>">Copier</button></div>
              <div class="n">Total de la commande <?= h($o['total']) ?> · 1 BTC = <?= number_format($b['rate'], 2, ',', "\u{202F}") ?> $US (<?= h($b['rate_source']) ?>)</div>
            <?php else: ?>
              <div class="l">Montant</div>
              <div class="v amt"><?= h($o['total']) ?> <small>en BTC</small></div>
              <div class="n">Le cours du Bitcoin est momentanément indisponible. Cette page réessaie chaque minute : attendez le montant exact en BTC avant de payer.</div>
            <?php endif; ?>
            <div class="l" style="margin-top:18px">À cette adresse Bitcoin</div>
            <div class="v addr"><code><?= h($b['address']) ?></code>
              <button type="button" class="copy" data-copy="<?= h($b['address']) ?>">Copier</button></div>
            <?php if(!empty($b['sats'])): ?>
              <div class="hold">Montant garanti pendant <b id="countdown" data-expires="<?= (int)$b['expires'] ?>"><?= gmdate('i:s', max(0, $b['expires'] - time())) ?></b>. Ensuite, il est recalculé au cours du moment.</div>
            <?php endif; ?>
            <div class="watch"><i class="pulse"></i> <?= $st === 'reported' ? 'Vérification de la transaction '.h(substr($b['reported'], 0, 12)).'… Cette page se met à jour toute seule.' : 'Nous surveillons la blockchain pour détecter votre paiement. Cette page se met à jour toute seule.' ?></div>
          </div>
        </div>
        <ul class="paytips">
          <li><b>Envoyez le montant exact en un seul paiement.</b> Si votre plateforme déduit ses frais de retrait du montant, ajoutez-les en plus.</li>
          <li><b>Réseau Bitcoin uniquement.</b> Pas de Lightning, ni de « BTC » encapsulé sur d’autres réseaux comme BEP-20 ou ERC-20.</li>
          <li><b>Revenez quand vous voulez.</b> Le lien de cette page figure dans l’e-mail de commande envoyé à <?= h($o['email']) ?>.</li>
        </ul>
        <details class="txform"<?= $st === 'reported' ? ' open' : '' ?>><summary>Déjà payé ? Indiquez votre identifiant de transaction</summary>
          <form method="post" action="index.php">
            <input type="hidden" name="action" value="btc_txid"><input type="hidden" name="ref" value="<?= h($o['ref']) ?>"><input type="hidden" name="k" value="<?= h($key) ?>">
            <label for="txid">Identifiant de transaction (ou lien vers un explorateur de blocs)</label>
            <div class="txrow"><input id="txid" name="txid" autocomplete="off" spellcheck="false" placeholder="ex. 4a5e1e4baab89f3a32518a88c31bc87f…" required>
              <button class="btn" type="submit">Vérifier le paiement</button></div>
          </form>
        </details>
      <?php else: $short = $st === 'short'; ?>
        <ol class="timeline">
          <li class="ok"><b>Commande passée</b><span><?= h($o['time']) ?></span></li>
          <li class="ok"><b>Paiement envoyé</b><span><?= btc_amount($b['paid_sats']) ?> BTC<?= $short ? ', inférieur aux '.btc_amount($b['expected_sats']).' BTC dus' : '' ?></span></li>
          <li class="<?= $conf >= $need ? 'ok' : 'now' ?>"><b>Confirmé sur la blockchain</b><span><?= $conf >= $need ? 'Confirmé' : 'En attente de confirmation, en général 10 à 60 minutes' ?></span></li>
          <li class="<?= ($o['status'] ?? '') === 'shipped' ? 'ok' : ($conf >= $need && !$short ? 'now' : '') ?>"><b>Expédié du Japon</b><span><?= ($o['status'] ?? '') === 'shipped' ? 'En route : le suivi est dans vos e-mails' : 'Sous '.(int)$CONFIG['hold_hours'].' heures après confirmation, avec suivi' ?></span></li>
        </ol>
        <?php if($short): ?><div class="errs" style="margin-top:16px">Votre paiement est inférieur au montant dû. Écrivez à <a href="mailto:<?= h($CONFIG['email']) ?>"><?= h($CONFIG['email']) ?></a> et nous réglerons la différence.</div><?php endif; ?>
        <div class="txbox"><div class="l">Transaction</div><code><?= h($b['txid']) ?></code>
          <a href="<?= h(btc_tx_url($b['txid'])) ?>" target="_blank" rel="noopener">Suivre sur mempool.space ↗</a></div>
      <?php endif; ?>
      </div>

      <aside class="summary">
        <h3>Votre commande</h3>
        <?php foreach($o['lines'] as $l): ?>
          <div class="sl"><span><?= h($l['name']) ?><br><span class="q"><?= (int)$l['qty'] ?> × <?= h($l['unit']) ?></span></span><span><?= h($l['total']) ?></span></div>
        <?php endforeach; ?>
        <div class="sl"><span style="color:var(--muted)">Articles</span><span><?= h($o['goods']) ?></span></div>
        <div class="sl"><span style="color:var(--muted)">Livraison · <?= h($o['ship_label']) ?></span><span><?= !empty($o['free_shipping']) && (float)$o['shipping_usd'] == 0 ? 'Offerte' : h($o['shipping']) ?></span></div>
        <div class="tot"><span>Total</span><span><?= h($o['total']) ?></span></div>
        <p class="n">Livraison à <?= h($o['city']) ?>, <?= h($o['country_name']) ?>. Le montant en BTC est calculé à partir du total de la commande au cours du Bitcoin en direct.</p>
        <p class="n">Une question ? <a href="mailto:<?= h($CONFIG['email']) ?>?subject=<?= rawurlencode('Commande '.$o['ref']) ?>"><?= h($CONFIG['email']) ?></a></p>
      </aside>
    </div>
  </div></section>
  <?php if($open): ?><script src="assets/qrcode.min.js?v=<?= @filemtime(FK_ROOT.'/assets/qrcode.min.js') ?>" defer></script><?php endif; ?>

<?php elseif($page==='how'): ?>
  <section style="padding-top:22px"><div class="wrap">
    <div class="sechead"><div><h1><?= h($h1) ?></h1>
      <p>Quatre étapes, sans création de compte : les prix dégressifs sont affichés sur chaque produit. Payez en Bitcoin directement depuis votre portefeuille, ou réglez votre facture par virement.<?php if(info_page('revendeurs')): ?> Voir notre <a href="<?= h(url('page', ['pg'=>'revendeurs'])) ?>">offre revendeurs terms</a>.<?php endif; ?></p></div></div>
    <?php steps_block($CONFIG); ?>
    <div style="margin-top:40px" class="cats three">
      <a href="<?= url('payment') ?>"><h3>Moyens de paiement</h3><p>Crypto et virement SEPA : comment ça marche.</p></a>
      <a href="<?= url('shipping') ?>"><h3>Livraison &amp; retours</h3><p>Tarifs, délais, retours et remboursements.</p></a>
      <a href="<?= url('faq') ?>"><h3>Questions fréquentes</h3><p>Précommandes, quantités, retours et plus encore.</p></a>
    </div>
  </div></section>

<?php elseif($page==='payment'): ?>
  <section style="padding-top:22px"><div class="wrap" style="max-width:820px">
    <div class="sechead"><div><h1><?= h($h1) ?></h1>
      <p>Choisissez votre moyen de paiement à la commande. <?= array_filter($PAYMENTS, 'btc_method') ? 'Le Bitcoin se paie sur la page de votre commande, dès sa validation. Pour les autres moyens, nous vous envoyons' : 'Nous vous envoyons' ?> les coordonnées par e-mail et SMS sous <?= (int)$CONFIG['reply_hours'] ?> heures, avec votre facture.</p></div></div>
    <table class="tbl">
      <thead><tr><th>Moyen</th><th>Disponible pour</th><th>Détails</th></tr></thead>
      <tbody>
        <?php foreach($PAYMENTS as $m):
          $where = $m['countries']==='*' ? 'Tous les pays'
                 : implode(', ', array_map(fn($c)=>$COUNTRIES[$c] ?? $c, $m['countries'])); ?>
          <tr><td class="nm"><?= h($m['label']) ?></td><td><?= h($where) ?></td><td><?= h($m['note']) ?></td></tr>
        <?php endforeach; ?>
      </tbody>
    </table>
    <?php if(array_filter($PAYMENTS, 'btc_method')): ?>
    <h2 class="sub2">Payer en Bitcoin</h2>
    <div class="prose">
      <ol>
        <li><b>Passez votre commande</b> et choisissez Bitcoin.</li>
        <li><b>Scannez le QR code</b> de la page de votre commande avec n’importe quel portefeuille Bitcoin, ou copiez le montant exact et notre adresse. Le montant est garanti <?= (int)btc_settings()['minutes'] ?> minutes au cours du moment.</li>
        <li><b>Recevez votre reçu.</b> La page détecte votre paiement sur la blockchain et nous vous envoyons un reçu avec un lien pour le suivre.</li>
        <li><b>Nous expédions</b> sous <?= (int)$CONFIG['hold_hours'] ?> heures après confirmation, en général 10 à 60 minutes après le paiement.</li>
      </ol>
      <p>Vérifiez toujours que l’adresse affichée sur la page de votre commande est <code><?= h(btc_settings()['address']) ?></code>. Nous n’envoyons jamais d’autre adresse Bitcoin par e-mail ou par chat.</p>
    </div>
    <?php endif; ?>
    <div class="notice" style="margin-top:22px">
      <b>Nous ne demandons jamais de numéro de carte, de mot de passe ni de clés de portefeuille.</b> Pour les autres moyens que le Bitcoin, vous passez commande et
      nous vous envoyons les coordonnées de paiement, en réservant votre stock <?= (int)$CONFIG['hold_hours'] ?> heures. Vérifiez toujours
      les coordonnées de paiement dans l’e-mail envoyé depuis <?= h($CONFIG['email']) ?> et indiquez votre référence de commande.
    </div>
    <p style="font-size:14px;color:var(--ink2);margin-top:18px">Les factures sont établies en euros. Les frais de livraison sont calculés à la commande.</p>
  </div></section>

<?php elseif($page==='shipping'):
  $SM = ship_methods($STORE); $fs = free_ship_usd($STORE);
  $policy = str_replace("\r", '', (string)($CONFIG['shipping_policy'] ?? ''));
  $parts = preg_split('/^[ \t]*\{rates\}[ \t]*$/m', $policy, 2);
  preg_match_all('/^##\s+(.+)$/m', fill($policy), $heads); ?>
  <section style="padding-top:22px"><div class="wrap">
    <div class="srhead">
      <h1><?= h($h1) ?></h1>
      <p class="lede">Expédié du Japon avec suivi, emballé avec soin, et un tarif clair avant de payer.</p>
    </div>
    <div class="srcards">
      <?php if($fs): ?><div class="k1"><span class="ic"><svg viewBox="0 0 24 24" aria-hidden="true"><path fill="currentColor" d="M3 6.5A1.5 1.5 0 0 1 4.5 5h9A1.5 1.5 0 0 1 15 6.5V8h2.6a1.5 1.5 0 0 1 1.2.6l2.4 3.2c.2.26.3.58.3.9V16a1.5 1.5 0 0 1-1.5 1.5h-.6a2.75 2.75 0 0 1-5.3 0H9.9a2.75 2.75 0 0 1-5.3 0h-.1A1.5 1.5 0 0 1 3 16V6.5Zm12 3V13h4.5l-1.9-2.5a1.5 1.5 0 0 0-1.2-.6H15ZM7.25 18.25a1 1 0 1 0 0-2 1 1 0 0 0 0 2Zm9.4 0a1 1 0 1 0 0-2 1 1 0 0 0 0 2Z"/></svg></span><b>Livraison offerte</b><span>Dès <?= money_whole($fs) ?></span></div><?php endif; ?>
      <div class="k2"><span class="ic"><svg viewBox="0 0 24 24" aria-hidden="true"><path fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" d="M12 7v5l3 2M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z"/></svg></span><b><?= h($SM['standard']['label']) ?></b><span><?= h($SM['standard']['days']) ?>, avec suivi</span></div>
      <div class="k3"><span class="ic"><svg viewBox="0 0 24 24" aria-hidden="true"><path fill="currentColor" d="M13.5 2 4 13.5h6.5L9.5 22 20 9.5h-6.6L13.5 2Z"/></svg></span><b><?= h($SM['express']['label']) ?></b><span><?= h($SM['express']['days']) ?>, avec suivi</span></div>
      <div class="k4"><span class="ic"><svg viewBox="0 0 24 24" aria-hidden="true"><path fill="none" stroke="currentColor" stroke-width="2" stroke-linejoin="round" d="M3.5 7.5 12 3l8.5 4.5v9L12 21l-8.5-4.5v-9ZM3.5 7.5 12 12m0 0 8.5-4.5M12 12v9"/></svg></span><b>Expédition rapide</b><span>Sous <?= (int)$CONFIG['hold_hours'] ?> heures après paiement</span></div>
    </div>
    <div class="srgrid">
      <?php if(count($heads[1]) >= 3): ?>
      <nav class="srtoc" aria-label="Sur cette page"><b>Sur cette page</b><ol>
        <?php foreach($heads[1] as $hd): ?><li><a href="<?= h(url('shipping')) ?>#<?= h(slugify($hd)) ?>"><?= h(preg_replace('/\[([^\]]+)\]\([^)]*\)|\*\*/', '$1', $hd)) ?></a></li><?php endforeach; ?>
      </ol></nav>
      <?php endif; ?>
      <article class="prose sr">
        <?= rich($parts[0]) ?>
        <?php if(count($parts) === 2): ship_rates_block(); echo rich($parts[1]); endif; ?>
        <div class="srcontact">
          <div><b>Une autre question ?</b><span>Nous répondons sous <?= (int)$CONFIG['reply_hours'] ?> heures. Consultez la <a href="<?= url('faq') ?>">FAQ</a> ou <a href="<?= url('contact') ?>">contactez-nous</a>.</span></div>
          <div class="row2">
            <a class="btn" href="mailto:<?= h($CONFIG['email']) ?>">Écrire à <?= h($CONFIG['email']) ?></a>
            <?php if(trim($CONFIG['chat_code'] ?? '') !== ''): ?><button type="button" class="btn g" data-open-chat hidden>Discuter en direct</button><?php endif; ?>
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
    <?php $more = [['Livraison & retours', url('shipping')], ['Comment commander', url('how')], ['Moyens de paiement', url('payment')]];
    foreach(['revendeurs'=>'Offre revendeurs', 'a-propos'=>'À propos de '.$CONFIG['brand'], 'cgv'=>'Conditions générales de vente', 'mentions-legales'=>'Mentions légales', 'confidentialite'=>'Politique de confidentialité'] as $pg_=>$lb_) if(info_page($pg_)) $more[] = [$lb_, url('page', ['pg'=>$pg_])];
    $more[] = ['Nous contacter', url('contact')];
    guide_links(PAGE_GUIDES['faq'], 'En savoir plus', $more); ?>
  </div></section>
  <script type="application/ld+json">
  <?= json_encode(['@context'=>'https://schema.org','@type'=>'FAQPage','mainEntity'=>array_map(fn($f)=>
    ['@type'=>'Question','name'=>$f[0],'acceptedAnswer'=>['@type'=>'Answer','text'=>$f[1]]], $faqs)],
    JSON_UNESCAPED_SLASHES|JSON_UNESCAPED_UNICODE|JSON_HEX_TAG) ?>
  </script>

<?php elseif($page==='contact'): ?>
  <section style="padding-top:22px"><div class="wrap" style="max-width:700px">
    <div class="sechead"><div><h1><?= h($h1) ?></h1>
      <p>Une question sur un produit, un prix par quantité ou une commande en cours ? Écrivez-nous.</p></div></div>
    <table class="tbl">
      <tbody>
        <tr><td class="nm">E-mail</td><td><a href="mailto:<?= h($CONFIG['email']) ?>"><?= h($CONFIG['email']) ?></a></td></tr>
        <?php if($CONFIG['phone']): ?><tr><td class="nm">Téléphone</td><td><?= h($CONFIG['phone']) ?></td></tr><?php endif; ?>
        <tr><td class="nm">Société</td><td><?= h($CONFIG['legal_name']) ?>, <?= h($CONFIG['address']) ?></td></tr>
        <tr><td class="nm">Commande en cours</td><td>Indiquez votre référence de commande (format FK-26-XXXXX) dans l’objet.</td></tr>
      </tbody>
    </table>
    <p style="margin-top:22px;font-size:14.5px;color:var(--ink2)">Boutiques et revendeurs : pour des commandes régulières, des volumes importants ou une allocation sur une prochaine sortie, envoyez-nous les extensions et quantités souhaitées, nous revenons vers vous avec un prix.</p>
  </div></section>

<?php elseif($page==='sets'): ?>
  <section class="top"><div class="wrap">
    <div class="sechead"><div><h1><?= h($h1) ?></h1>
      <p>Toutes les extensions Pokémon japonaises en stock. Les extensions japonaises sortent avant leurs versions françaises : ce sont les cartes les plus récentes du jeu.</p></div></div>
    <?php $shown = [];
    foreach($SERIES as $k=>$sr): $ss = series_sets($k); if(!$ss) continue; $shown += $ss; ?>
      <div class="sechead" style="margin:30px 0 14px"><div><h2><?= h($sr['name']) ?></h2></div>
        <a href="<?= h(url('series', ['s'=>$sr['slug']])) ?>">La série <?= h($sr['name']) ?> →</a></div>
      <?php set_tiles($ss);
    endforeach;
    $other = array_diff_key(sets_all(), $shown);
    if($other): ?><div class="sechead" style="margin:30px 0 14px"><div><h2>Autres extensions</h2></div></div><?php set_tiles($other); endif; ?>
  </div></section>

<?php elseif($page==='series'):
  $ss = series_sets($series['key']);
  $sp = array_values(array_filter($PRODUCTS, fn($p)=>isset($ss[$p['set']]))); ?>
  <section class="top"><div class="wrap">
    <div class="sechead"><div><h1><?= h($h1) ?></h1></div><span class="count"><?= count($sp) ?> produit<?= count($sp) > 1 ? 's' : '' ?></span></div>
    <?php if(trim($series['intro'] ?? '') !== ''): ?><div class="prose lead"><?= rich($series['intro']) ?></div><?php endif; ?>
    <?php if($ss): ?><h2 class="sub2">Extensions</h2><?php set_tiles($ss); endif; ?>
    <?php if($sp): ?><h2 class="sub2">Tous les produits <?= h($series['name']) ?></h2><div class="grid"><?php foreach($sp as $p) include_card($p); ?></div><?php endif; ?>
    <?php guide_links(PAGE_GUIDES['series'], 'Guides des cartes '.$series['name']); ?>
  </div></section>

<?php elseif($page==='set'):
  $sp = set_products($set['name']);
  $others = isset($SERIES[$set['series']]) ? array_diff_key(series_sets($set['series']), [$set['name']=>true]) : []; ?>
  <section class="top"><div class="wrap">
    <div class="sechead"><div><h1><?= h($h1) ?></h1></div><span class="count"><?= count($sp) ?> produit<?= count($sp) > 1 ? 's' : '' ?></span></div>
    <?php if(trim($set['intro']) !== ''): ?><div class="prose lead"><?= rich($set['intro']) ?></div><?php endif; ?>
    <div class="grid"><?php foreach($sp as $p) include_card($p); ?></div>
    <?php if($others): ?><h2 class="sub2">Autres extensions <?= h($SERIES[$set['series']]['name']) ?></h2><?php set_tiles($others); endif; ?>
    <?php guide_links(PAGE_GUIDES['set:'.$set['slug']] ?? PAGE_GUIDES['set'], 'Guides : '.$set['name'].' et plus'); ?>
  </div></section>

<?php elseif($page==='collection'):
  $cp = collection_products($coll); ?>
  <section class="top"><div class="wrap">
    <div class="sechead"><div><h1><?= h($h1) ?></h1></div><span class="count"><?= count($cp) ?> produit<?= count($cp) > 1 ? 's' : '' ?></span></div>
    <?php if(trim($coll['intro'] ?? '') !== ''): ?><div class="prose lead"><?= rich($coll['intro']) ?></div><?php endif; ?>
    <?php if($cp): ?><div class="grid"><?php foreach($cp as $p) include_card($p); ?></div>
    <?php else: ?><p class="empty">Rien en stock pour le moment : voir toutes les <a href="<?= url('catalog', ['cat'=>'singles']) ?>">cartes à l’unité</a>.</p><?php endif; ?>
    <?php guide_links(PAGE_GUIDES['coll:'.$coll['slug']] ?? PAGE_GUIDES['coll'], 'Guides utiles'); ?>
  </div></section>

<?php elseif($page==='guides'): ?>
  <section class="top"><div class="wrap">
    <div class="sechead"><div><h1><?= h($h1) ?></h1>
      <p>Des réponses claires sur les cartes Pokémon : leur prix et leur cote, les cartes les plus chères et les plus rares, les raretés, les displays et coffrets, et les cartes japonaises.</p></div></div>
    <?php guide_cards($GUIDES); ?>
  </div></section>

<?php elseif($page==='guide'): ?>
  <section class="top"><div class="wrap">
    <article class="article">
      <h1><?= h($h1) ?></h1>
      <?php if(!empty($guide['updated'])): ?><p class="meta">Mis à jour le <?= h(date_fr($guide['updated'])) ?></p><?php endif; ?>
      <?php preg_match_all('/^##\s+(.+)$/m', str_replace("\r", '', $guide['body'] ?? ''), $heads);
      if(count($heads[1]) >= 3): ?>
      <nav class="toc" aria-label="Dans ce guide"><b>Dans ce guide</b><ol>
        <?php foreach($heads[1] as $hd): $plainhd = preg_replace('/\[([^\]]+)\]\([^)]*\)|\*\*/', '$1', $hd); ?>
          <li><a href="<?= h(url('guide', ['g'=>$guide['slug']])) ?>#<?= h(slugify($hd)) ?>"><?= h($plainhd) ?></a></li>
        <?php endforeach; ?>
      </ol></nav>
      <?php endif; ?>
      <div class="prose"><?php guide_body($guide['body'] ?? ''); ?></div>
      <div class="notice" style="margin-top:28px"><?= rich_inline(GUIDE_SHOP[GUIDE_GROUP[$guide['slug']] ?? 'buy']) ?></div>
    </article>
    <?php $more = guides_related($guide['slug']);
    if($more): ?><h2 class="sub2">Guides associés</h2><?php guide_cards($more); endif; ?>
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
    <?php if(($info['slug'] ?? '') === 'revendeurs') guide_links(PAGE_GUIDES['shop'], 'Guides pour bien acheter'); ?>
  </div></section>
  <?php $qa = policy_questions($info['body'] ?? '');
  if($qa): ?><script type="application/ld+json"><?= json_encode(['@context'=>'https://schema.org','@type'=>'FAQPage','mainEntity'=>array_map(fn($x)=>
    ['@type'=>'Question','name'=>$x[0],'acceptedAnswer'=>['@type'=>'Answer','text'=>$x[1]]], $qa)], JSON_UNESCAPED_SLASHES|JSON_UNESCAPED_UNICODE|JSON_HEX_TAG) ?></script><?php endif; ?>

<?php elseif($page==='notfound'): ?>
  <section class="top"><div class="wrap" style="max-width:720px">
    <h1><?= h($h1) ?></h1>
    <p class="lede">Cette page n’existe pas ou a été déplacée. Essayez la <a href="<?= url('catalog') ?>">boutique</a>, les <a href="<?= url('sets') ?>">extensions Pokémon</a>, ou la recherche ci-dessus.</p>
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
    <div><h4>Boutique</h4><ul>
      <?php foreach($CATEGORIES as $k=>$c): ?>
        <li><a href="<?= url('catalog',['cat'=>$k]) ?>"><?= h($c['label']) ?></a></li>
      <?php endforeach; ?>
      <li><a href="<?= url('catalog') ?>">Tous les produits</a></li>
    </ul></div>
    <div><h4>Explorer</h4><ul>
      <li><a href="<?= url('sets') ?>">Extensions Pokémon</a></li>
      <?php foreach($SERIES as $k=>$sr): if(series_sets($k)): ?><li><a href="<?= h(url('series', ['s'=>$sr['slug']])) ?>">Extensions <?= h($sr['name']) ?></a></li><?php endif; endforeach; ?>
      <?php foreach($COLLECTIONS as $c): if(collection_products($c)): ?><li><a href="<?= h(url('collection', ['c'=>$c['slug']])) ?>"><?= h($c['title']) ?></a></li><?php endif; endforeach; ?>
      <?php foreach(['prix-carte-pokemon'=>'Prix et cote des cartes Pokémon', 'carte-pokemon-la-plus-chere'=>'La carte Pokémon la plus chère', 'cartes-pokemon-les-plus-rares'=>'Cartes Pokémon rares', 'display-etb-coffret-pokemon'=>'Display, ETB ou coffret ?', 'extensions-pokemon'=>'Extensions Pokémon : noms FR, EN, JP'] as $gs_=>$gl_):
        if(guide_by_slug($gs_)): ?><li><a href="<?= h(url('guide', ['g'=>$gs_])) ?>"><?= h($gl_) ?></a></li><?php endif; endforeach; ?>
      <?php if($GUIDES): ?><li><a href="<?= url('guides') ?>">Tous les guides</a></li><?php endif; ?>
    </ul></div>
    <div><h4>Commander</h4><ul>
      <?php if(info_page('revendeurs')): ?><li><a href="<?= h(url('page', ['pg'=>'revendeurs'])) ?>">Offre revendeurs</a></li><?php endif; ?>
      <li><a href="<?= url('how') ?>">Comment commander</a></li>
      <li><a href="<?= url('payment') ?>">Moyens de paiement</a></li>
      <li><a href="<?= url('shipping') ?>">Livraison &amp; retours</a></li>
      <li><a href="<?= url('cart') ?>">Mon panier</a></li>
    </ul></div>
    <div><h4>Aide</h4><ul>
      <li><a href="<?= url('faq') ?>">FAQ</a></li>
      <li><a href="<?= url('contact') ?>">Contact</a></li>
      <?php foreach($INFO_PAGES as $ip): if(($ip['slug'] ?? '') === 'revendeurs') continue; ?><li><a href="<?= h(url('page', ['pg'=>$ip['slug']])) ?>"><?= h(fill($ip['title'])) ?></a></li><?php endforeach; ?>
      <li><a href="mailto:<?= h($CONFIG['email']) ?>"><?= h($CONFIG['email']) ?></a></li>
    </ul></div>
  </div>
  <div class="legal">
    <div>Expédié du Japon avec suivi · Prix en euros.</div>
    <div><?= h($CONFIG['legal_name']) ?> est un revendeur indépendant de produits authentiques, sans lien avec The Pokémon Company, Nintendo, Creatures Inc. ou GAME FREAK Inc., qui ne l’approuvent ni ne le parrainent. Pokémon et les noms des produits sont des marques de leurs propriétaires respectifs.</div>
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
  return (usd * CO.rate).toLocaleString('fr-FR', {minimumFractionDigits: CO.dec, maximumFractionDigits: CO.dec}) + '\u00a0' + CO.sym;
}
function fmtShip(usd){ return usd > 0 ? fmt(usd) : 'Offerte'; }
/* shipping, total and the minimum-order check follow the selected country; the server re-checks all of it */
function updateTotals(country){
  if(!window.CO) return;
  const rates = CO.ship[country], warn = document.getElementById('minWarn'), btn = document.getElementById('placeBtn');
  const cost = document.getElementById('shipCost');
  const picked = (document.querySelector('input[name=ship_method]:checked') || {}).value || 'standard';
  document.querySelectorAll('[data-ship-price]').forEach(el => { el.textContent = rates ? fmtShip(rates[el.dataset.shipPrice]) : ''; });
  document.getElementById('shipLabel').textContent = 'Livraison · ' + (CO.labels[picked] || '');
  if(rates === undefined){ cost.textContent = 'Choisissez votre pays'; document.getElementById('grandTotal').textContent = fmt(CO.goods); warn.hidden = true; btn.disabled = false; return; }
  const ship = rates[picked];
  const total = CO.goods + ship;   /* same sum as the server: rounded only when shown */
  cost.textContent = fmtShip(ship); cost.style.color = '';
  document.getElementById('grandTotal').textContent = fmt(total);
  const short = total < CO.min;
  warn.hidden = !short; btn.disabled = short;
  if(short) warn.textContent = 'Le minimum de commande est de ' + fmt(CO.min) + ', livraison comprise. Votre total est de ' + fmt(total) + ' : ajoutez ' + fmt(CO.min - total) + ' pour valider cette commande.';
}
/* price checker: show rows that contain every word typed */
function pcFilter(v){
  const words = v.toLowerCase().split(/\s+/).filter(Boolean); let n = 0;
  document.querySelectorAll('#pcTable tbody tr').forEach(r => { const ok = words.every(w => r.dataset.q.includes(w)); r.hidden = !ok; if(ok) n++; });
  document.getElementById('pcCount').textContent = n + (n > 1 ? ' produits' : ' produit');
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
  b.textContent = CO.btc.includes(r.value) ? b.dataset.btcLabel : 'Valider la commande';
}));

/* Bitcoin order page: QR code, copy buttons, the countdown on the quoted amount, and a quiet check for the payment */
(function(){
  const box = document.getElementById('payBox'); if(!box) return;
  document.querySelectorAll('.copy').forEach(b => b.addEventListener('click', () => {
    const v = b.dataset.copy, done = () => { b.textContent = 'Copié ✓'; setTimeout(() => b.textContent = 'Copier', 1800); };
    if(navigator.clipboard && window.isSecureContext) navigator.clipboard.writeText(v).then(done, () => prompt('Copier :', v)); else prompt('Copier :', v);
  }));
  const qrEl = document.getElementById('qr');
  const draw = () => {
    if(!qrEl || !window.qrcode) return;
    const qr = qrcode(0, 'M'); qr.addData(qrEl.dataset.uri); qr.make();
    const n = qr.getModuleCount(), q = 3, w = n + q * 2; let d = '';
    for(let r = 0; r < n; r++) for(let c = 0; c < n; c++) if(qr.isDark(r, c)) d += 'M' + (c + q) + ' ' + (r + q) + 'h1v1h-1z';
    qrEl.innerHTML = '<svg viewBox="0 0 ' + w + ' ' + w + '" role="img" aria-label="QR code avec notre adresse Bitcoin et le montant" shape-rendering="crispEdges">'
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
      else cd.textContent = 'mise à jour…';
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
  $flag = $p['status']==='preorder' ? ['pre','PRÉCOMMANDE']
        : ($p['status']==='soldout' ? ['out','ÉPUISÉ']
        : ($p['status']==='new' ? ['new','NOUVEAU']
        : ($p['status']==='low' ? ['low','STOCK LIMITÉ'] : ['','EN STOCK'])));
  ?>
  <article class="card">
    <a class="art" href="<?= url('product',['id'=>$p['id']]) ?>" style="display:block">
      <span class="flag <?= $flag[0] ?>"><?= $flag[1] ?></span>
      <?php if($ph): ?>
        <?= img_tag($ph[0], $p['name'].' — carte Pokémon japonaise') ?>
      <?php else: ?>
        <div class="ph"><span><?= h($CONFIG['kanji']) ?></span><span><?= h($p['set']) ?></span></div>
      <?php endif; ?>
    </a>
    <div class="in">
      <h3><a href="<?= url('product',['id'=>$p['id']]) ?>"><?= h($p['name']) ?></a></h3>
      <div class="meta"><?= h(implode(' · ', array_filter([$p['set'], ($p['cond'] ?? 'Sealed') !== 'Sealed' ? cond_fr($p['cond']) : '', $p['status']==='preorder' ? $p['release'] : '']))) ?></div>
      <div class="px"><span class="u"><?= money($base) ?></span><span class="per">l’unité<?= $p['moq'] > 1 ? ' dès '.(int)$p['moq'] : '' ?></span><?php if($p['ladder'][0][1] > $base): ?><span class="w"><?= money($p['ladder'][0][1]) ?></span><?php endif; ?></div>
      <?php if(count($p['ladder']) > 1 && $best[1] < $base): ?><div class="drop">jusqu’à <b><?= money($best[1]) ?></b> dès <?= (int)$best[0] ?></div><?php endif; ?>
      <div class="meta">Dès <?= (int)$p['moq'] ?><?= $p['step'] > 1 ? ' · par '.(int)$p['step'] : '' ?></div>
    </div>
    <form method="post" action="index.php">
      <input type="hidden" name="action" value="add">
      <input type="hidden" name="id" value="<?= h($p['id']) ?>">
      <?php if(can_order($p)): stepper($p, 'qty', $p['moq'], $p['moq']); ?>
      <button class="btn" type="submit">Ajouter</button>
      <?php else: ?>
      <button class="btn" type="submit" disabled>Épuisé</button>
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
    <thead><tr><th>Option</th><th>Délai</th><th>Suivi</th></tr></thead>
    <tbody>
      <?php foreach($SM as $mm): ?><tr><td class="nm"><?= h($mm['label']) ?></td><td><?= h($mm['days']) ?> après expédition</td><td>De porte à porte</td></tr><?php endforeach; ?>
    </tbody>
  </table></div>
  <p>Nous expédions avec Japan Post EMS, DHL Express et FedEx, en choisissant le meilleur transporteur selon le poids du colis, la destination et l’option de livraison. Le tarif dépend du poids de la commande et de la destination : un forfait par commande plus un prix au kilo, arrondi à l’euro supérieur.<?php if($fs): ?> Livraison <?= h($SM['standard']['label']) ?> offerte dès <?= money_whole($fs) ?> d’achat.<?php endif; ?></p>
  <div class="tblwrap"><table class="tbl">
    <thead><tr><th>Destination</th><?php foreach($SM as $mm): ?><th class="r"><?= h($mm['label']) ?></th><?php endforeach; ?></tr></thead>
    <tbody>
      <?php $zoned = array_merge([], ...array_map(fn($z)=>$z['countries'], $STORE['shipping']['zones']));   /* "Rest of world" only while some country has no zone */
      foreach(array_merge($STORE['shipping']['zones'], array_diff(array_keys($GLOBALS['COUNTRIES']), $zoned) ? [['name'=>'Reste du monde']+$STORE['shipping']['rest']] : []) as $z): $zr = zone_rates($z); ?>
        <tr><td class="nm"><?= h($z['name']) ?></td><?php foreach(array_keys($SM) as $mk): ?><td class="r"><?= $cell($zr[$mk]) ?></td><?php endforeach; ?></tr>
      <?php endforeach; ?>
    </tbody>
  </table></div>
  <?php $ex = [['Une carte à l’unité', 0.05, 30], ['1 display (environ 0,4 kg)', 0.4, 150], ['6 displays (environ 2,4 kg)', 2.4, 900], ['6 coffrets Dresseur d’Élite (environ 5,4 kg)', 5.4, 250]];
  if($fs) $ex[] = ['36 displays (environ 14,4 kg), plus de '.money_whole($fs), 14.4, $fs]; ?>
  <div class="tblwrap"><table class="tbl ex">
    <thead><tr><th>Exemples vers la <?= h($GLOBALS['COUNTRIES'][$GLOBALS['HOME_CC']] ?? $GLOBALS['HOME_CC']) ?></th><?php foreach($SM as $mm): ?><th class="r"><?= h($mm['label']) ?></th><?php endforeach; ?></tr></thead>
    <tbody>
      <?php foreach($ex as [$label, $kg, $goods]): ?>
        <tr><td><?= h($label) ?></td><?php foreach(array_keys($SM) as $mk): $v = shipping_usd($STORE, $GLOBALS['HOME_CC'], $kg, $mk, $goods); ?><td class="r"><?= $v > 0 ? money($v) : '<b class="free">Offerte</b>' ?></td><?php endforeach; ?></tr>
      <?php endforeach; ?>
    </tbody>
  </table></div>
  <p class="small">À titre indicatif, un display japonais scellé pèse environ 0,4 kg emballé et un coffret Dresseur d’Élite environ 0,9 kg. Le prix exact s’affiche à la commande, avant le paiement.</p>
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
  <div class="pcheck"><label for="pcq">Vérifier un prix</label>
    <input id="pcq" type="search" placeholder="Essayez 151, Dracaufeu, display, coffret…" oninput="pcFilter(this.value)" autocomplete="off">
    <span id="pcCount"><?= count($PRODUCTS) ?> produits</span></div>
  <div class="tblwrap"><table class="tbl pc" id="pcTable">
    <thead><tr><th>Produit</th><th class="r">Prix</th><th class="r">Meilleur prix</th></tr></thead><tbody>
    <?php foreach($CATEGORIES as $ck=>$c): foreach($PRODUCTS as $p): if($p['cat'] !== $ck) continue;
      $top = end($p['ladder']); ?>
      <tr data-q="<?= h(strtolower($p['name'].' '.$p['set'].' '.$p['sku'].' '.$c['label'])) ?>">
        <td class="nm"><a href="<?= h(url('product', ['id'=>$p['id']])) ?>"><?= h($p['name']) ?></a>
          <div class="sk"><?= h(implode(' · ', array_filter([$c['label'], $p['set'], cond_fr($p['cond']), status_label(FR_STATUS, $p['status'])]))) ?></div></td>
        <td class="r"><?= money(unit_price($p, $p['moq'])) ?><div class="sk">l’unité, dès <?= (int)$p['moq'] ?></div></td>
        <td class="r"><?= money($top[1]) ?><div class="sk">l’unité dès <?= (int)max($p['moq'], $top[0]) ?></div></td></tr>
    <?php endforeach; endforeach; ?>
    </tbody></table></div>
  <p class="small">Prix de notre catalogue en direct, en euros. Livraison en sus, offerte dès <?= money_whole(free_ship_usd($GLOBALS['STORE'])) ?> d’achat.</p>
<?php }

/* card database: every Japanese set we carry, by series */
function set_table_block(){
  global $SERIES;
  foreach($SERIES as $k=>$sr): $sets = series_sets($k); if(!$sets) continue; ?>
  <div class="tblwrap"><table class="tbl">
    <thead><tr><th>Extension <?= h($sr['name']) ?></th><th>Code</th><th class="r">Produits</th></tr></thead><tbody>
    <?php foreach($sets as $st): ?>
      <tr><td class="nm"><a href="<?= h(url('set', ['s'=>$st['slug']])) ?>"><?= h($st['name']) ?></a></td><td><?= h(($st['code'] ?? '') ?: '—') ?></td><td class="r"><?= count(set_products($st['name'])) ?></td></tr>
    <?php endforeach; ?>
    </tbody></table></div>
  <?php endforeach;
}

/* free printable template: exact card size, bleed and safe area */
function card_template_block(){ ?>
  <div class="tplbox">
    <img src="assets/site/trading-card-template-63x88mm.svg" width="276" height="376" alt="Modèle de carte vierge, 63 × 88 mm avec 3 mm de fond perdu et une zone de sécurité" loading="lazy">
    <div>
      <b>Modèle de carte à imprimer gratuit</b>
      <p>63 × 88 mm, le format d’une carte Pokémon, avec 3 mm de fond perdu, le trait de coupe, les coins arrondis et une zone de sécurité pour le texte. Fichiers vectoriels : imprimez à 100 % (« taille réelle »), pas « ajuster à la page ».</p>
      <p><a class="btn" href="assets/site/trading-card-template-63x88mm.svg" download>Télécharger une carte (SVG)</a>
         <a class="btn g" href="assets/site/trading-card-template-sheet-a4.svg" download>Télécharger une planche de 9 (A4)</a></p>
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
      <div class="t"><b>La livraison vous est offerte.</b> Livraison <?= h($sm['standard']['label']) ?> gratuite : l’option <?= h($sm['express']['label']) ?> ne coûte que la différence.</div>
    <?php else: ?>
      <div class="t">Plus que <b><?= money($t - $goods) ?></b> pour la livraison <?= h($sm['standard']['label']) ?> offerte (dès <?= money_whole($t) ?>).</div>
    <?php endif; ?>
    <div class="meter" role="progressbar" aria-valuemin="0" aria-valuemax="100" aria-valuenow="<?= (int)$pct ?>" aria-label="Progression vers la livraison offerte"><i style="width:<?= (int)$pct ?>%"></i></div>
  </div>
<?php }

function stepper($p, $name, $value, $min){ ?>
  <div class="step">
    <button type="button" onclick="bump(this,-<?= (int)$p['step'] ?>,<?= (int)$p['moq'] ?>)">−</button>
    <input type="number" name="<?= h($name) ?>" value="<?= (int)$value ?>" min="<?= (int)$min ?>" step="<?= (int)$p['step'] ?>" aria-label="Quantité">
    <button type="button" onclick="bump(this,<?= (int)$p['step'] ?>,<?= (int)$p['moq'] ?>)">+</button>
  </div>
<?php }

function set_tiles($sets){ ?>
  <div class="tiles"><?php foreach($sets as $st): $n = count(set_products($st['name'])); ?>
    <a href="<?= h(url('set', ['s'=>$st['slug']])) ?>"><?php if($st['code'] !== ''): ?><span class="n"><?= h($st['code']) ?></span><?php endif; ?>
      <b><?= h($st['name']) ?></b><span class="n"><?= $n ?> produit<?= $n > 1 ? 's' : '' ?></span></a>
  <?php endforeach; ?></div>
<?php }

function guide_cards($guides){ ?>
  <div class="gcards"><?php foreach($guides as $g): ?>
    <a href="<?= h(url('guide', ['g'=>$g['slug']])) ?>"><h3><?= h(fill($g['title'])) ?></h3>
      <p><?= h(fill(($g['seo_desc'] ?? '') ?: plain($g['body'] ?? '', 140))) ?></p><span>Lire le guide →</span></a>
  <?php endforeach; ?></div>
<?php }

function steps_block($CONFIG){ ?>
  <div class="steps">
    <div><div class="n">01</div><h3>Des prix clairs</h3>
      <p>Chaque produit affiche ses prix dégressifs, pour tout le monde. Pas de compte à créer, pas de devis à attendre.</p></div>
    <div><div class="n">02</div><h3>Vous commandez</h3>
      <p>Ajoutez au panier, indiquez votre adresse et choisissez un moyen de paiement. Le stock est réservé à votre nom pendant <?= (int)$CONFIG['hold_hours'] ?> heures.</p></div>
    <div><div class="n">03</div><h3>Vous payez</h3>
      <p>En Bitcoin directement sur la page de votre commande, ou recevez les coordonnées d’un autre moyen par e-mail ou SMS sous <?= (int)$CONFIG['reply_hours'] ?> heures.</p></div>
    <div><div class="n">04</div><h3>Livré depuis le Japon</h3>
      <p>Dès réception du paiement, nous expédions sous <?= (int)$CONFIG['hold_hours'] ?> heures par EMS, DHL ou FedEx, avec suivi.</p></div>
  </div>
<?php }
