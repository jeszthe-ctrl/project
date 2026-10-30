<?php
/* =============================================================
   Gold Bullion Shop — admin. Open /admin.php (or /admin).

   The first visit asks you to create your admin username and
   password, so do that straight after uploading. Everything you
   save goes to data/ (the last 30 versions are kept in
   data/backups/) — the website's code is never touched.
   ============================================================= */

define('GB_ROOT', __DIR__);
@ini_set('display_errors', '0');
error_reporting(E_ALL & ~E_NOTICE & ~E_WARNING & ~E_DEPRECATED);
require GB_ROOT.'/inc/store.php';
start_session();
header('X-Frame-Options: DENY');
header('X-Robots-Tag: noindex, nofollow');
header('Cache-Control: no-store');
header('Referrer-Policy: same-origin');

$STORE = store_load();
$AUTH  = data_read('auth') ?: [];
$IP    = $_SERVER['REMOTE_ADDR'] ?? '';
$V     = preg_replace('/[^a-z]/', '', (string)($_GET['v'] ?? 'dashboard')) ?: 'dashboard';

/* ---------------- helpers ---------------- */
function aurl($v='dashboard', $extra=[]){ return 'admin.php?'.http_build_query(['v'=>$v] + $extra); }
function go($v='dashboard', $extra=[]){ header('Location: '.aurl($v, $extra)); exit; }
function note($msg, $type='ok'){ $_SESSION['admin_flash'][] = [$type, $msg]; }
function in_str($k){ $v = $_POST[$k] ?? ''; return is_string($v) ? trim(str_replace("\r\n", "\n", $v)) : ''; }
function in_num($k, $def=0){ $v = $_POST[$k] ?? ''; return is_numeric($v) ? (float)$v : $def; }
function is_admin(){ global $AUTH; return !empty($AUTH['key']) && hash_equals($AUTH['key'], (string)($_SESSION['admin'] ?? '')); }
function csrf(){ if(empty($_SESSION['admin_csrf'])) $_SESSION['admin_csrf'] = bin2hex(random_bytes(16)); return $_SESSION['admin_csrf']; }
function csrf_field(){ return '<input type="hidden" name="csrf" value="'.h(csrf()).'">'; }
function csrf_ok(){ return hash_equals(csrf(), (string)($_POST['csrf'] ?? '')); }
function save($msg){ global $STORE; if(store_save($STORE)) note($msg); else note('Could not save: the data folder is not writable. Ask your host to make it writable by PHP.', 'err'); }
function usd($v){ return '$'.number_format((float)$v, 2); }

/* FAQs are edited as  Q: question / A: answer  blocks */
function faqs_to_text($faqs){ return implode("\n\n", array_map(fn($f)=>"Q: {$f[0]}\nA: {$f[1]}", $faqs ?? [])); }
function text_to_faqs($t){
  $out = [];
  foreach(preg_split('/\n\s*\n(?=\s*Q:)/', trim($t)) as $block){
    if(preg_match('/^\s*Q:\s*(.+?)\n\s*A:\s*(.+)$/s', $block, $m)) $out[] = [trim($m[1]), trim($m[2])];
  }
  return $out;
}

function recent_fails(){ global $AUTH, $IP; return array_values(array_filter($AUTH['fails'][$IP] ?? [], fn($t)=>$t > time() - 900)); }
function record_fail(){ global $AUTH, $IP; $AUTH['fails'][$IP] = array_merge(recent_fails(), [time()]); data_write('auth', $AUTH); }
function log_in(){ global $AUTH, $IP; session_regenerate_id(true); unset($AUTH['fails'][$IP]); data_write('auth', $AUTH); $_SESSION['admin'] = $AUTH['key']; }

function idx($section, $key, $field='slug'){ global $STORE; foreach($STORE[$section] as $i=>$row) if((string)($row[$field] ?? '') === (string)$key) return $i; return null; }
function taken_paths(){
  global $STORE;
  return array_merge(['shop','guides','cart','checkout','order-received','search','products','admin','sitemap.xml','robots.txt','assets','data','inc','tools'],
    array_column($STORE['categories'], 'slug'), array_column($STORE['pages'], 'slug'), array_column($STORE['tools'], 'slug'));
}
function unique_slug($slug, $taken){ $base = $slug ?: 'item'; $s = $base; $n = 2; while(in_array($s, $taken, true)) $s = $base.'-'.$n++; return $s; }

/* ---------------- sign in / first run ---------------- */
$act = $_POST['act'] ?? '';
if(empty($AUTH['hash'])){
  if($act === 'setup' && csrf_ok()){
    $u = in_str('user'); $p = (string)($_POST['pass'] ?? '');
    if(!preg_match('/^[A-Za-z0-9._@-]{3,40}$/', $u)) note('Use a username of 3–40 letters, numbers, dots or dashes.', 'err');
    elseif(strlen($p) < 10) note('Use a password of at least 10 characters.', 'err');
    elseif($p !== ($_POST['pass2'] ?? '')) note('The two passwords don’t match.', 'err');
    else { $AUTH = ['user'=>$u, 'hash'=>password_hash($p, PASSWORD_DEFAULT), 'key'=>bin2hex(random_bytes(16)), 'fails'=>[]]; data_write('auth', $AUTH); log_in(); note('Welcome! Your admin login is set. Start with Settings.'); go('settings'); }
  }
  shell_start('Create your admin login');
  echo '<div class="login"><h1>Create your admin login</h1><p class="muted">Choose the username and password you’ll use to manage your shop.</p><form method="post">'.csrf_field().'<input type="hidden" name="act" value="setup">
    <label>Username<input name="user" required autocomplete="username"></label><label>Password (10+ characters)<input type="password" name="pass" required autocomplete="new-password"></label>
    <label>Repeat password<input type="password" name="pass2" required autocomplete="new-password"></label><button class="btn">Create login</button></form></div>';
  shell_end(); exit;
}
if($act === 'login'){
  if(count(recent_fails()) >= 5) note('Too many attempts. Try again in 15 minutes.', 'err');
  elseif(csrf_ok() && hash_equals(strtolower($AUTH['user'] ?? ''), strtolower(in_str('user'))) && password_verify((string)($_POST['pass'] ?? ''), $AUTH['hash'])){ log_in(); go(); }
  else { record_fail(); note('Wrong username or password.', 'err'); }
}
if($V === 'logout'){ unset($_SESSION['admin']); go(); }
if(!is_admin()){
  shell_start('Sign in');
  echo '<div class="login"><h1>Sign in</h1><form method="post">'.csrf_field().'<input type="hidden" name="act" value="login">
    <label>Username<input name="user" required autocomplete="username" autofocus></label><label>Password<input type="password" name="pass" required autocomplete="current-password"></label><button class="btn">Sign in</button></form></div>';
  shell_end(); exit;
}

/* ---------------- actions (POST) ---------------- */
if($_SERVER['REQUEST_METHOD'] === 'POST'){
  if(!csrf_ok()){ note('Your session expired — please try again.', 'err'); go($V); }
  $S = &$STORE['settings'];
  switch($act){
    case 'settings':
      foreach(['brand','legal_name','domain','email','order_email','from_email','phone','sms','address','hours','reply_time','pay_window','dispatch_time','announcement','author','countries',
               'gsc_verification','bing_verification','ga4_id','chat_code','smtp_host','smtp_secure','smtp_user'] as $k) if(isset($_POST[$k])) $S[$k] = in_str($k);
      $S['domain'] = rtrim($S['domain'], '/');
      $S['smtp_port'] = (int)in_num('smtp_port', 465);
      if(in_str('smtp_pass') !== '') $S['smtp_pass'] = in_str('smtp_pass');
      if(!empty($_POST['smtp_clear'])) $S['smtp_pass'] = '';
      if(!filter_var($S['email'], FILTER_VALIDATE_EMAIL)) note('The shop email address doesn’t look right.', 'err');
      save('Settings saved.'); go('settings');
    case 'spot':
      $S['spot_mode'] = in_str('spot_mode') === 'manual' ? 'manual' : 'auto';
      $S['manual_gold'] = in_num('manual_gold'); $S['manual_silver'] = in_num('manual_silver'); $S['manual_time'] = time();
      if(!empty($_POST['refresh'])){ $d = data_read('spot') ?: []; $d['checked'] = 0; data_write('spot', $d); }
      save('Spot price settings saved.'); go('spot');
    case 'testmail':
      $to = in_str('to');
      $ok = filter_var($to, FILTER_VALIDATE_EMAIL) && shop_mail($to, 'Test email from '.$S['brand'], "This is a test email from your shop's admin.\n\nIf you can read this, order emails will reach you too.");
      note($ok ? "Test email sent to $to. Check the inbox and the spam folder." : 'The test email could not be sent. '.($GLOBALS['GB_MAIL_ERROR'] ?? ''), $ok ? 'ok' : 'err');
      go('settings');
    case 'account':
      if(!password_verify((string)($_POST['current'] ?? ''), $AUTH['hash'])){ note('Your current password is wrong.', 'err'); go('account'); }
      $u = in_str('user'); $p = (string)($_POST['pass'] ?? '');
      if(preg_match('/^[A-Za-z0-9._@-]{3,40}$/', $u)) $AUTH['user'] = $u;
      if($p !== ''){ if(strlen($p) < 10 || $p !== ($_POST['pass2'] ?? '')){ note('New password must be 10+ characters and match.', 'err'); go('account'); } $AUTH['hash'] = password_hash($p, PASSWORD_DEFAULT); }
      $AUTH['key'] = bin2hex(random_bytes(16)); data_write('auth', $AUTH); $_SESSION['admin'] = $AUTH['key'];
      note('Login details updated.'); go('account');
    case 'shipping':
      foreach($STORE['shipping']['methods'] as $k=>&$m){
        $in = $_POST['m'][$k] ?? [];
        $m = ['enabled'=>!empty($in['enabled']), 'label'=>trim($in['label'] ?? $m['label']), 'price'=>max(0, (float)($in['price'] ?? 0)), 'days'=>trim($in['days'] ?? ''), 'note'=>trim($in['note'] ?? ''), 'free_eligible'=>!empty($in['free_eligible'])];
      } unset($m);
      $STORE['shipping']['free_over'] = max(0, in_num('free_over'));
      save('Shipping saved.'); go('shipping');
    case 'payments':
      foreach($STORE['payments'] as $k=>&$m){
        $in = $_POST['m'][$k] ?? [];
        $m = ['enabled'=>!empty($in['enabled']), 'label'=>trim($in['label'] ?? $m['label']), 'note'=>trim(str_replace("\r\n", "\n", $in['note'] ?? '')), 'details'=>trim(str_replace("\r\n", "\n", $in['details'] ?? ''))];
      } unset($m);
      foreach(['pay_email_subject','pay_email_body','ship_email_subject','ship_email_body'] as $k) $S[$k] = in_str($k);
      save('Payment methods saved.'); go('payments');
    case 'home':
      foreach(['home_seo_title','home_seo_desc','hero_kicker','hero_title','hero_text','shop_seo_title','shop_seo_desc','shop_intro','guides_seo_title','guides_seo_desc'] as $k) $S[$k] = in_str($k);
      $STORE['home']['body'] = in_str('body'); $STORE['home']['faqs'] = text_to_faqs(in_str('faqs'));
      save('Home page saved.'); go('home');
    case 'item':   /* pages, guides, price pages, categories */
      $sec = in_str('sec'); $slug = in_str('slug');
      if(!in_array($sec, ['pages','guides','tools','categories'], true)) go();
      $i = idx($sec, $slug);
      $row = $i === null ? [] : $STORE[$sec][$i];
      foreach(['title','name','h1','seo_title','seo_desc','summary','intro','body','group','topic','nav','parent','date','updated','lang'] as $k) if(isset($_POST[$k])) $row[$k] = in_str($k);
      if(isset($_POST['faqs'])) $row['faqs'] = text_to_faqs(in_str('faqs'));
      if($sec === 'guides') $row['hidden'] = !empty($_POST['hidden']);
      if($sec === 'categories') $row['nav'] = !empty($_POST['nav']);
      if($sec !== 'tools'){
        $new = slugify(in_str('newslug') ?: ($row['title'] ?? $row['name'] ?? ''));
        if($i === null || ($new !== '' && $new !== $slug && in_str('newslug') !== '')){
          $taken = $sec === 'guides' ? array_column($STORE['guides'], 'slug') : taken_paths();
          $row['slug'] = unique_slug($new, array_diff($taken, [$slug]));
        }
      }
      if($sec === 'guides' && $i !== null) $row['updated'] = in_str('updated') ?: date('Y-m-d');
      if($sec === 'pages' && ($row['group'] ?? '') === 'legal') $S['policies_updated'] = time();
      if($i === null){ $row += ['faqs'=>[], 'date'=>date('Y-m-d')]; $STORE[$sec][] = $row; } else $STORE[$sec][$i] = $row;
      save('Saved.'); go('edit', ['sec'=>$sec, 'slug'=>$row['slug']]);
    case 'restore':
      $sec = in_str('sec'); $slug = in_str('slug'); $i = idx($sec, $slug); $d = default_item($sec, $slug);
      if($i !== null && $d){ $STORE[$sec][$i] = $d; save('Original text restored.'); }
      go('edit', ['sec'=>$sec, 'slug'=>$slug]);
    case 'delete_item':
      $sec = in_str('sec'); $slug = in_str('slug'); $i = idx($sec, $slug);
      if($i !== null && in_array($sec, ['pages','guides','categories'], true)){
        array_splice($STORE[$sec], $i, 1);
        if(default_item($sec, $slug)) $S['deleted_'.$sec][] = $slug;
        save('Deleted.');
      }
      go($sec);
    case 'product':
      product_save(); break;
    case 'delete_products':
      $ids = (array)($_POST['ids'] ?? []);
      if(!empty($_POST['all'])) $ids = array_column($STORE['products'], 'id');
      $STORE['products'] = array_values(array_filter($STORE['products'], fn($p)=>!in_array($p['id'], $ids, true)));
      save(count($ids).' product(s) deleted.'); go('products');
    case 'import':
      csv_import(); break;
    case 'order':
      order_action(); break;
    case 'delete_messages':
      data_write('messages', []); note('Messages cleared.'); go('messages');
  }
}

/* ---------------- products ---------------- */
function product_save(){
  global $STORE;
  $id = in_str('id'); $i = idx('products', $id, 'id');
  $p = $i === null ? ['id'=>bin2hex(random_bytes(5)), 'images'=>[]] : $STORE['products'][$i];
  foreach(['name','sku','brand','purity','packaging','short','description','seo_title','seo_desc','gtin','metal','type','unit','pricing','stock'] as $k) $p[$k] = in_str($k);
  foreach(['weight','price','premium_pct','premium_usd','compare_at','sort'] as $k) $p[$k] = in_num($k);
  $p['categories'] = array_values(array_intersect((array)($_POST['categories'] ?? []), array_column($STORE['categories'], 'slug')));
  if(!$p['categories']) $p['categories'] = [auto_category($p)];
  $p['featured'] = !empty($_POST['featured']); $p['hidden'] = !empty($_POST['hidden']);
  $slug = slugify(in_str('slug') ?: $p['name']);
  $others = array_column(array_filter($STORE['products'], fn($x)=>$x['id'] !== $p['id']), 'slug');
  $p['slug'] = unique_slug($slug, $others);
  /* photos: keep, remove, add by upload or address */
  $keep = [];
  foreach((array)($p['images'] ?? []) as $k=>$src) if(empty($_POST['rm_img'][$k])) $keep[] = $src;
  foreach(preg_split('/\s+/', in_str('img_urls')) as $u) if(preg_match('#^https?://\S+$#', $u)) $keep[] = $u;
  if(!empty($_FILES['photos']['name'][0])){
    foreach($_FILES['photos']['tmp_name'] as $n=>$tmp){
      if(!is_uploaded_file($tmp)) continue;
      $rel = store_image(file_get_contents($tmp), $p['slug']);
      if($rel) $keep[] = $rel; else note('“'.h($_FILES['photos']['name'][$n]).'” isn’t a JPG, PNG, WebP or GIF image (or is over 15 MB).', 'err');
    }
  }
  if(!empty($_POST['img_first'])){ $f = (int)$_POST['img_first']; if(isset($keep[$f])){ $x = $keep[$f]; unset($keep[$f]); array_unshift($keep, $x); } }
  $p['images'] = array_values(array_unique($keep));
  if($i === null) $STORE['products'][] = $p; else $STORE['products'][$i] = $p;
  save('Product saved.');
  go('product', ['id'=>$p['id']]);
}

/* which category a product belongs in, from its metal, type and weight */
function auto_category($p){
  global $STORE;
  $best = ($p['metal'] ?? 'gold') === 'gold' ? 'gold-bars' : ''; $score = 0;
  foreach($STORE['categories'] as $c){
    $m = $c['match'] ?? [];
    if(!$m) continue;
    if(isset($m['metal']) && $m['metal'] !== ($p['metal'] ?? 'gold')) continue;
    if(isset($m['type']) && $m['type'] !== ($p['type'] ?? 'bar')) continue;
    if(isset($m['weight']) && (abs((float)$m['weight'] - (float)($p['weight'] ?? 0)) > 0.0001 || $m['unit'] !== ($p['unit'] ?? ''))) continue;
    if(count($m) > $score){ $best = $c['slug']; $score = count($m); }
  }
  return $best ?: 'gold-bars';
}

/* weight and unit from text such as "1 oz", "1/10 oz", "10 Gram", "1 Kilo", "100g" */
function parse_weight($s){
  $s = strtolower(str_replace(['½','¼'], ['1/2','1/4'], (string)$s));
  if(!preg_match('#(\d+(?:\.\d+)?(?:\s*/\s*\d+)?)\s*-?\s*(troy\s*ounces?|troy\s*oz|ozt|ounces?|oz|kilograms?|kilos?|kg|grams?|gram|gm|gr|g)\b#', $s, $m)) return null;
  $n = $m[1];
  if(strpos($n, '/') !== false){ [$a, $b] = array_map('floatval', explode('/', $n)); $n = $b ? $a / $b : 0; } else $n = (float)$n;
  $u = $m[2];
  $unit = preg_match('/^(k)/', $u) ? 'kg' : (preg_match('/^(g)/', $u) ? 'g' : 'oz');
  return $n > 0 ? [$n, $unit] : null;
}

function csv_rows($file){
  $raw = file_get_contents($file);
  if(substr($raw, 0, 3) === "\xEF\xBB\xBF") $raw = substr($raw, 3);
  $first = strtok($raw, "\n");
  $delim = substr_count($first, ';') > substr_count($first, ',') ? ';' : (substr_count($first, "\t") > substr_count($first, ',') ? "\t" : ',');
  $fh = fopen('php://temp', 'r+'); fwrite($fh, $raw); rewind($fh);
  $head = null; $rows = [];
  while(($r = fgetcsv($fh, 0, $delim, '"', '\\')) !== false){
    if($head === null){ $head = array_map(fn($h)=>preg_replace('/[^a-z0-9]/', '', strtolower($h)), $r); continue; }
    if(count(array_filter($r, fn($x)=>trim((string)$x) !== '')) === 0) continue;
    $rows[] = array_combine($head, array_pad(array_slice($r, 0, count($head)), count($head), ''));
  }
  return $rows;
}

function csv_import(){
  global $STORE;
  @set_time_limit(600);
  if(empty($_FILES['csv']['tmp_name']) || !is_uploaded_file($_FILES['csv']['tmp_name'])){ note('Choose a CSV file to import.', 'err'); go('import'); }
  $rows = csv_rows($_FILES['csv']['tmp_name']);
  $pick = function($r, $names){ foreach($names as $n){ if(isset($r[$n]) && trim((string)$r[$n]) !== '') return trim((string)$r[$n]); } return ''; };
  $A = [
    'name'=>['name','title','productname','product','posttitle','itemname'], 'slug'=>['slug','handle','urlkey','permalink'],
    'sku'=>['sku','variantsku','itemnumber','productcode','code'], 'price'=>['saleprice','price','regularprice','variantprice','yourprice','retailprice','sellprice'],
    'compare'=>['compareatprice','variantcompareatprice','regularprice','msrp'], 'premium_pct'=>['premiumpercent','premium','premiumpct','markuppercent','markup'],
    'premium_usd'=>['premiumusd','premiumdollar','premiumamount','markupusd'], 'cat'=>['category','categories','type','producttype','collection','collections'],
    'weight'=>['weight','fineweight','metalweight','weightoz','weightg','size'], 'unit'=>['unit','weightunit','variantweightunit'],
    'desc'=>['description','bodyhtml','body','longdescription','postcontent','content'], 'short'=>['shortdescription','excerpt','summary','postexcerpt'],
    'img'=>['images','image','imagesrc','imageurl','featuredimage','photo','photos','picture','imageurls'], 'brand'=>['brand','vendor','manufacturer','mint','refiner','maker'],
    'purity'=>['purity','fineness'], 'metal'=>['metal','metaltype'], 'stock'=>['stock','stockstatus','instock','inventory','variantinventoryqty','quantity','qty','availability'],
    'seo_title'=>['seotitle','metatitle'], 'seo_desc'=>['seodescription','metadescription'], 'gtin'=>['gtin','upc','ean','barcode','variantbarcode'], 'packaging'=>['packaging','package'],
    'featured'=>['featured','isfeatured'],
  ];
  $replace = in_str('mode') === 'replace';
  $download = !empty($_POST['download']);
  $products = $replace ? [] : $STORE['products'];
  $by = []; foreach($products as $k=>$p){ if(!empty($p['sku'])) $by['sku:'.strtolower($p['sku'])] = $k; $by['slug:'.$p['slug']] = $k; }
  $cats = []; foreach($STORE['categories'] as $c){ $cats[strtolower($c['slug'])] = $c['slug']; $cats[strtolower($c['name'])] = $c['slug']; }
  $added = 0; $updated = 0; $skipped = 0; $imgfail = 0; $handles = [];
  foreach($rows as $r){
    $name = $pick($r, $A['name']);
    $handle = $pick($r, $A['slug']);
    /* Shopify exports put extra photos on rows with only the handle */
    if($name === '' && $handle !== '' && isset($handles[$handle])){
      $k = $handles[$handle]; $img = $pick($r, $A['img']);
      if($img) $products[$k]['images'][] = $download ? (fetch_image($img, $products[$k]['slug']) ?: $img) : $img;
      continue;
    }
    if($name === ''){ $skipped++; continue; }
    $text = $name.' '.$pick($r, $A['weight']).' '.$pick($r, $A['unit']);
    $metal = strtolower($pick($r, $A['metal']));
    $metal = isset(METALS[$metal]) ? $metal : (preg_match('/silver/i', $name) ? 'silver' : (preg_match('/platinum/i', $name) ? 'platinum' : (preg_match('/palladium/i', $name) ? 'palladium' : 'gold')));
    $type = preg_match('/\b(coin|eagle|buffalo|maple|krugerrand|philharmonic|britannia|panda|kangaroo|sovereign|pesos|libertad|lunar|napoleon|ducat)\b/i', $name) ? 'coin' : (preg_match('/\bround\b/i', $name) ? 'round' : 'bar');
    $wraw = $pick($r, $A['weight']); $uraw = strtolower($pick($r, $A['unit']));
    $w = parse_weight($name) ?: (is_numeric($wraw) && (float)$wraw > 0 ? [(float)$wraw, preg_match('/^k/', $uraw) ? 'kg' : (preg_match('/^g/', $uraw) ? 'g' : 'oz')] : parse_weight($text));
    $p = [
      'name'=>$name, 'sku'=>$pick($r, $A['sku']), 'brand'=>$pick($r, $A['brand']), 'metal'=>$metal, 'type'=>$type,
      'weight'=>$w[0] ?? 0, 'unit'=>$w[1] ?? 'oz', 'purity'=>$pick($r, $A['purity']) ?: ($metal === 'gold' && $type === 'bar' ? '.9999' : ''),
      'packaging'=>$pick($r, $A['packaging']), 'short'=>strip_tags($pick($r, $A['short'])), 'description'=>clean_html($pick($r, $A['desc'])),
      'seo_title'=>$pick($r, $A['seo_title']), 'seo_desc'=>$pick($r, $A['seo_desc']), 'gtin'=>$pick($r, $A['gtin']),
      'featured'=>in_array(strtolower($pick($r, $A['featured'])), ['1','yes','true','y'], true), 'hidden'=>false,
    ];
    $price = (float)preg_replace('/[^\d.]/', '', $pick($r, $A['price']));
    $prem_pct = $pick($r, $A['premium_pct']); $prem_usd = $pick($r, $A['premium_usd']);
    if($price <= 0 && ($prem_pct !== '' || $prem_usd !== '')){ $p['pricing'] = 'spot'; $p['premium_pct'] = (float)$prem_pct; $p['premium_usd'] = (float)preg_replace('/[^\d.-]/', '', $prem_usd); $p['price'] = 0; }
    else { $p['pricing'] = 'fixed'; $p['price'] = $price; $p['premium_pct'] = 0; $p['premium_usd'] = 0; }
    $cmp = (float)preg_replace('/[^\d.]/', '', $pick($r, $A['compare']));
    $p['compare_at'] = $cmp > $price ? $cmp : 0;
    $st = strtolower($pick($r, $A['stock']));
    $p['stock'] = $st === '' ? 'in' : ((is_numeric($st) && (float)$st <= 0) || preg_match('/out|sold|^no$|^0$|false/', $st) ? 'out' : (preg_match('/pre/', $st) ? 'preorder' : 'in'));
    $pc = [];
    foreach(preg_split('/\s*[,|;>]\s*/', strtolower($pick($r, $A['cat']))) as $c) if(isset($cats[$c])) $pc[] = $cats[$c];
    $p['categories'] = array_values(array_unique($pc ?: [auto_category($p)]));
    $slug = slugify($handle ?: $name);
    $key = $p['sku'] !== '' && isset($by['sku:'.strtolower($p['sku'])]) ? $by['sku:'.strtolower($p['sku'])] : ($by['slug:'.$slug] ?? null);
    $imgs = array_values(array_filter(preg_split('/\s*[|;\n]\s*|\s*,\s*(?=https?:)/', $pick($r, $A['img']))));
    if($key !== null){
      $old = $products[$key];
      $p = array_merge($old, array_filter($p, fn($v)=>$v !== '' && $v !== []));
      $p['pricing'] = $p['pricing'] ?? 'fixed';
      $updated++;
    } else {
      $p['id'] = bin2hex(random_bytes(5));
      $p['slug'] = unique_slug($slug, array_column($products, 'slug'));
      $p['images'] = [];
      $added++;
    }
    if($imgs){
      $p['images'] = [];
      foreach($imgs as $src){
        if(!preg_match('#^https?://#', $src)) continue;
        $local = $download ? fetch_image($src, $p['slug']) : '';
        if($download && !$local) $imgfail++;
        $p['images'][] = $local ?: $src;
      }
    }
    if($key !== null) $products[$key] = $p; else { $products[] = $p; $key = count($products) - 1; }
    $by['slug:'.$p['slug']] = $key; if($p['sku'] !== '') $by['sku:'.strtolower($p['sku'])] = $key;
    if($handle !== '') $handles[$handle] = $key;
  }
  $STORE['products'] = array_values($products);
  save("Import finished: $added added, $updated updated".($skipped ? ", $skipped rows skipped (no product name)" : '').($imgfail ? ", $imgfail photos couldn’t be downloaded and are linked from their original address instead" : '').'.');
  go('products');
}

function fetch_image($url, $name){ return store_image(http_get($url, 8), $name); }

/* product descriptions from a CSV: keep formatting, drop scripts, frames, styles and event handlers */
function clean_html($html){
  $html = preg_replace('#<(script|style|iframe|object|embed|form)\b.*?</\1>#is', '', (string)$html);
  $html = preg_replace('#<(script|style|iframe|object|embed|form|input|button)\b[^>]*>#i', '', $html);
  $html = preg_replace('#\s(on\w+|style)\s*=\s*("[^"]*"|\'[^\']*\'|[^\s>]+)#i', '', $html);
  return trim(preg_replace('#javascript:#i', '', $html));
}

/* ---------------- orders ---------------- */
function order_action(){
  global $STORE;
  $o = order_load(in_str('ref'));
  if(!$o){ note('Order not found.', 'err'); go('orders'); }
  $do = in_str('do');
  $log = function($msg) use(&$o){ $o['history'][] = [date('Y-m-d H:i'), $msg]; };
  if($do === 'status'){
    $st = in_str('status');
    if(isset(ORDER_STATUSES[$st]) && $st !== $o['status']){ $o['status'] = $st; $log('Status → '.ORDER_STATUSES[$st]); }
    $o['carrier'] = in_str('carrier'); $o['tracking'] = in_str('tracking'); $o['admin_note'] = in_str('admin_note');
    order_save($o); note('Order updated.');
  }
  if($do === 'paymail' || $do === 'shipmail'){
    $ok = shop_mail($o['email'], in_str('subject'), in_str('body'), $STORE['settings']['email']);
    if($ok){
      if($do === 'paymail' && $o['status'] === 'new') $o['status'] = 'invoiced';
      if($do === 'shipmail' && in_array($o['status'], ['new','invoiced','paid'], true)) $o['status'] = 'shipped';
      $log($do === 'paymail' ? 'Payment details emailed' : 'Shipping email sent');
      order_save($o); note('Email sent to '.$o['email'].'.');
    } else note('The email could not be sent. '.($GLOBALS['GB_MAIL_ERROR'] ?? ''), 'err');
  }
  if($do === 'delete'){ @unlink(data_file('orders/'.$o['ref'])); note('Order '.$o['ref'].' deleted.'); go('orders'); }
  go('order', ['ref'=>$o['ref']]);
}

/* ---------------- pages ---------------- */
$NAV = ['dashboard'=>'Dashboard', 'orders'=>'Orders', 'messages'=>'Messages', 'products'=>'Products', 'import'=>'Import CSV', 'categories'=>'Categories',
        'home'=>'Home page', 'pages'=>'Pages', 'guides'=>'Guides', 'tools'=>'Price pages', 'shipping'=>'Shipping', 'payments'=>'Payments',
        'spot'=>'Spot price', 'settings'=>'Settings', 'account'=>'Login'];
if($V === 'export' || $V === 'template'){ ('page_'.$V)(); exit; }
shell_start($NAV[$V] ?? 'Admin', true);
$fn = 'page_'.$V;
if(function_exists($fn)) $fn(); else page_dashboard();
shell_end();

function page_dashboard(){
  global $STORE;
  $S = $STORE['settings']; $orders = orders_all(); $spot = spot();
  $new = count(array_filter($orders, fn($o)=>$o['status'] === 'new'));
  $todo = [];
  if(!$STORE['products']) $todo[] = ['Add your products', 'Import your product CSV.', 'import'];
  if(!$S['phone']) $todo[] = ['Add your phone number', 'A visible phone number builds trust with customers and with Google.', 'settings'];
  if(!$S['address']) $todo[] = ['Add your business address', 'Real business details are one of the strongest trust signals for a site that sells gold.', 'settings'];
  if(!$S['legal_name']) $todo[] = ['Add your registered business name', 'Shown in your terms, privacy policy and footer.', 'settings'];
  if(strpos(json_encode($STORE['payments']), 'YOUR') !== false) $todo[] = ['Enter your payment details', 'Your $Cashtag, Zelle email and wallet addresses for the payment-details email.', 'payments'];
  if(!$S['smtp_host']) $todo[] = ['Set up email sending (SMTP)', 'Order emails are most reliable when sent through your mailbox. Then send a test email.', 'settings'];
  if(!$S['gsc_verification']) $todo[] = ['Connect Google Search Console', 'Verify your site and submit your sitemap.', 'settings'];
  if($spot['gold'] <= 0) $todo[] = ['Spot price unavailable', 'The live price feed can’t be reached from your server. Enter a manual price for now.', 'spot']; ?>
<h1>Dashboard</h1>
<div class="stats">
  <a class="stat" href="<?= aurl('orders') ?>"><b><?= $new ?></b><span>new orders</span></a>
  <a class="stat" href="<?= aurl('orders') ?>"><b><?= count($orders) ?></b><span>orders in total</span></a>
  <a class="stat" href="<?= aurl('products') ?>"><b><?= count($STORE['products']) ?></b><span>products</span></a>
  <a class="stat" href="<?= aurl('spot') ?>"><b><?= $spot['gold'] > 0 ? usd($spot['gold']) : '—' ?></b><span>gold spot (<?= h($spot['source'] ?: 'none') ?>)</span></a>
</div>
<?php if($todo): ?><div class="card"><h2>To do</h2><ul class="todo"><?php foreach($todo as [$t, $d, $v]): ?><li><a href="<?= aurl($v) ?>"><b><?= h($t) ?></b></a><br><span class="muted"><?= h($d) ?></span></li><?php endforeach; ?></ul></div><?php endif; ?>
<div class="card"><h2>Latest orders</h2><?php orders_table(array_slice($orders, 0, 10)); ?></div>
<div class="card"><h2>Your site</h2><p><a href="./" target="_blank">Open the shop</a> · <a href="sitemap.xml" target="_blank">Sitemap</a> (submit <code><?= h(rtrim($S['domain'], '/')) ?>/sitemap.xml</code> in Google Search Console)</p></div>
<?php }

function orders_table($orders){
  if(!$orders){ echo '<p class="muted">No orders yet.</p>'; return; }
  echo '<table class="tbl"><thead><tr><th>Order</th><th>Date</th><th>Customer</th><th>Payment</th><th>Total</th><th>Status</th></tr></thead><tbody>';
  foreach($orders as $o) echo '<tr><td><a href="'.aurl('order', ['ref'=>$o['ref']]).'"><b>'.h($o['ref']).'</b></a></td><td>'.h($o['time']).'</td><td>'.h($o['name']).'<br><span class="muted">'.h($o['phone']).'</span></td><td>'.h($o['payment_label']).'</td><td>'.usd($o['total']).'</td><td><span class="st st-'.h($o['status']).'">'.h(ORDER_STATUSES[$o['status']] ?? $o['status']).'</span></td></tr>';
  echo '</tbody></table>';
}

function page_orders(){
  $all = orders_all(); $f = $_GET['status'] ?? '';
  $list = $f ? array_values(array_filter($all, fn($o)=>$o['status'] === $f)) : $all; ?>
<h1>Orders</h1>
<p class="tabs"><a href="<?= aurl('orders') ?>"<?= $f === '' ? ' class="on"' : '' ?>>All (<?= count($all) ?>)</a><?php foreach(ORDER_STATUSES as $k=>$l): $n = count(array_filter($all, fn($o)=>$o['status'] === $k)); ?> <a href="<?= aurl('orders', ['status'=>$k]) ?>"<?= $f === $k ? ' class="on"' : '' ?>><?= h($l) ?> (<?= $n ?>)</a><?php endforeach; ?></p>
<div class="card"><?php orders_table($list); ?></div>
<?php }

function page_order(){
  global $STORE;
  $o = order_load($_GET['ref'] ?? '');
  if(!$o){ echo '<p>Order not found.</p>'; return; }
  $S = $STORE['settings']; $pm = $STORE['payments'][$o['payment']] ?? ['details'=>''];
  $o['payment_details'] = fill_template($pm['details'] ?? '', $o);
  $pay_body = fill_template($S['pay_email_body'], $o);
  $ship_body = fill_template($S['ship_email_body'], $o);
  $sms = "Hi {$o['first_name']}, this is {$S['brand']} about order {$o['ref']} (".money($o['total'])."). ".str_replace("\n", ' ', $o['payment_details'])." Please put {$o['ref']} in the payment note. Thank you!"; ?>
<p><a href="<?= aurl('orders') ?>">← Orders</a></p>
<h1>Order <?= h($o['ref']) ?> <span class="st st-<?= h($o['status']) ?>"><?= h(ORDER_STATUSES[$o['status']] ?? '') ?></span></h1>
<?php if(isset($o['mail']) && (!$o['mail']['shop'] || !$o['mail']['customer'])): ?><p class="flash err">Order emails didn’t all send when this order was placed. <?= h($o['mail']['error'] ?: 'Check Settings → Email.') ?></p><?php endif; ?>
<div class="cols">
  <div class="card"><h2>Customer</h2>
    <p><b><?= h($o['name']) ?></b><br><a href="mailto:<?= h($o['email']) ?>"><?= h($o['email']) ?></a><br><a href="tel:<?= h(preg_replace('/[^\d+]/', '', $o['phone'])) ?>"><?= h($o['phone']) ?></a> · <a href="sms:<?= h(preg_replace('/[^\d+]/', '', $o['phone'])) ?>">text</a></p>
    <p><?= h($o['address1']) ?><?= $o['address2'] ? '<br>'.h($o['address2']) : '' ?><br><?= h($o['city']) ?>, <?= h($o['state']) ?> <?= h($o['zip']) ?><br><?= h($o['country']) ?></p>
    <?php if($o['notes']): ?><p><b>Notes:</b> <?= nl2br(h($o['notes'])) ?></p><?php endif; ?>
    <p class="muted small">Placed <?= h($o['time']) ?> · IP <?= h($o['ip']) ?> · gold spot <?= usd($o['spot'] ?? 0) ?></p>
  </div>
  <div class="card"><h2>Items</h2><table class="tbl"><tbody>
    <?php foreach($o['lines'] as $l): ?><tr><td><?= (int)$l['qty'] ?> × <?= h($l['name']) ?> <span class="muted"><?= h($l['sku']) ?></span></td><td class="r"><?= usd($l['total']) ?></td></tr><?php endforeach; ?>
    <tr><td>Shipping — <?= h($o['ship_label']) ?></td><td class="r"><?= usd($o['shipping']) ?></td></tr>
    <tr><td><b>Total</b></td><td class="r"><b><?= usd($o['total']) ?></b></td></tr></tbody></table>
    <p>Payment method: <b><?= h($o['payment_label']) ?></b></p>
  </div>
</div>
<div class="card"><h2>1. Send payment details</h2>
  <form method="post"><?= csrf_field() ?><input type="hidden" name="act" value="order"><input type="hidden" name="ref" value="<?= h($o['ref']) ?>"><input type="hidden" name="do" value="paymail">
    <label>Subject<input name="subject" value="<?= h(fill_template($S['pay_email_subject'], $o)) ?>"></label>
    <label>Email<textarea name="body" rows="14"><?= h($pay_body) ?></textarea></label>
    <button class="btn">Email payment details to <?= h($o['email']) ?></button></form>
  <p class="small">Prefer to text? Copy this message: <textarea rows="3" readonly onclick="this.select()"><?= h($sms) ?></textarea>
  <a class="btn btn-ghost" href="sms:<?= h(preg_replace('/[^\d+]/', '', $o['phone'])) ?>?&body=<?= rawurlencode($sms) ?>">Open in Messages (phone)</a></p>
</div>
<div class="card"><h2>2. Status &amp; tracking</h2>
  <form method="post"><?= csrf_field() ?><input type="hidden" name="act" value="order"><input type="hidden" name="ref" value="<?= h($o['ref']) ?>"><input type="hidden" name="do" value="status">
    <div class="grid3"><label>Status<select name="status"><?php foreach(ORDER_STATUSES as $k=>$l): ?><option value="<?= $k ?>"<?= $o['status'] === $k ? ' selected' : '' ?>><?= h($l) ?></option><?php endforeach; ?></select></label>
    <label>Carrier<input name="carrier" value="<?= h($o['carrier'] ?? '') ?>" placeholder="USPS, UPS, FedEx"></label><label>Tracking number<input name="tracking" value="<?= h($o['tracking'] ?? '') ?>"></label></div>
    <label>Private note (only you see this)<textarea name="admin_note" rows="2"><?= h($o['admin_note'] ?? '') ?></textarea></label>
    <button class="btn">Save</button></form>
</div>
<div class="card"><h2>3. Send shipping email</h2>
  <form method="post"><?= csrf_field() ?><input type="hidden" name="act" value="order"><input type="hidden" name="ref" value="<?= h($o['ref']) ?>"><input type="hidden" name="do" value="shipmail">
    <label>Subject<input name="subject" value="<?= h(fill_template($S['ship_email_subject'], $o)) ?>"></label>
    <label>Email<textarea name="body" rows="9"><?= h($ship_body) ?></textarea></label>
    <p class="small muted">Save the carrier and tracking number above first, then reload this page so they appear in the email.</p>
    <button class="btn">Email shipping confirmation</button></form>
</div>
<div class="card"><h2>History</h2><ul><?php foreach($o['history'] ?? [] as [$t, $m]): ?><li><?= h($t) ?> — <?= h($m) ?></li><?php endforeach; ?></ul>
  <form method="post" onsubmit="return confirm('Delete this order permanently?')"><?= csrf_field() ?><input type="hidden" name="act" value="order"><input type="hidden" name="ref" value="<?= h($o['ref']) ?>"><input type="hidden" name="do" value="delete"><button class="btn btn-danger">Delete order</button></form></div>
<?php }

function page_messages(){
  $all = data_read('messages') ?: []; ?>
<h1>Messages</h1><p class="muted">Contact form messages (also emailed to you).</p>
<?php if(!$all) echo '<p class="muted">No messages.</p>'; foreach($all as $m): ?>
<div class="card"><p><b><?= h($m['name']) ?></b> · <a href="mailto:<?= h($m['email']) ?>"><?= h($m['email']) ?></a> <?= h($m['phone']) ?> <?= $m['order'] ? '· Order '.h($m['order']) : '' ?> <span class="muted">· <?= h($m['time']) ?></span></p><p><?= nl2br(h($m['message'])) ?></p></div>
<?php endforeach; if($all): ?><form method="post" onsubmit="return confirm('Delete all messages?')"><?= csrf_field() ?><input type="hidden" name="act" value="delete_messages"><button class="btn btn-danger">Delete all messages</button></form><?php endif;
}

function page_products(){
  global $STORE;
  $q = mb_strtolower(trim($_GET['q'] ?? ''));
  $list = array_values(array_filter($STORE['products'], fn($p)=>$q === '' || mb_strpos(mb_strtolower($p['name'].' '.($p['sku'] ?? '')), $q) !== false)); ?>
<h1>Products <a class="btn" href="<?= aurl('product') ?>">+ Add product</a> <a class="btn btn-ghost" href="<?= aurl('import') ?>">Import CSV</a> <a class="btn btn-ghost" href="<?= aurl('export') ?>">Export CSV</a></h1>
<form class="inline"><input type="hidden" name="v" value="products"><input name="q" value="<?= h($q) ?>" placeholder="Search products"><button class="btn btn-ghost">Search</button></form>
<form method="post" onsubmit="return confirm('Delete the selected products?')"><?= csrf_field() ?><input type="hidden" name="act" value="delete_products">
<div class="card"><table class="tbl"><thead><tr><th><input type="checkbox" onclick="document.querySelectorAll('.pick').forEach(c=>c.checked=this.checked)"></th><th></th><th>Product</th><th>Category</th><th>Price</th><th>Stock</th></tr></thead><tbody>
<?php foreach($list as $p): $img = $p['images'][0] ?? ''; ?>
<tr><td><input type="checkbox" class="pick" name="ids[]" value="<?= h($p['id']) ?>"></td><td><?= $img ? '<img src="'.h($img).'" width="44" height="44" alt="" loading="lazy">' : '' ?></td>
<td><a href="<?= aurl('product', ['id'=>$p['id']]) ?>"><b><?= h($p['name']) ?></b></a><br><span class="muted small"><?= h($p['sku'] ?? '') ?> <?= !empty($p['hidden']) ? '· hidden' : '' ?> <?= !empty($p['featured']) ? '· ★ featured' : '' ?></span></td>
<td class="small"><?= h(implode(', ', $p['categories'] ?? [])) ?></td><td><?= usd(product_price($p)) ?><?= ($p['pricing'] ?? '') === 'spot' ? '<br><span class="muted small">spot + premium</span>' : '' ?></td><td><?= h(STOCK_STATUSES[$p['stock'] ?? 'in'] ?? '') ?></td></tr>
<?php endforeach; ?></tbody></table><?php if(!$list) echo '<p class="muted">No products yet — <a href="'.aurl('import').'">import your CSV</a> or add one.</p>'; ?></div>
<?php if($list): ?><button class="btn btn-danger">Delete selected</button> <label class="check"><input type="checkbox" name="all" value="1"> delete ALL products</label><?php endif; ?></form>
<?php }

function page_export(){
  global $STORE;
  header('Content-Type: text/csv; charset=utf-8'); header('Content-Disposition: attachment; filename="products-'.date('Y-m-d').'.csv"');
  $out = fopen('php://output', 'w');
  $cols = ['name','slug','sku','category','metal','type','weight','unit','purity','brand','packaging','price','premium_percent','premium_usd','compare_at_price','stock','featured','images','short_description','description','seo_title','seo_description','gtin'];
  fputcsv($out, $cols, ',', '"', '\\');
  foreach($STORE['products'] as $p) fputcsv($out, [$p['name'], $p['slug'], $p['sku'] ?? '', implode('|', $p['categories'] ?? []), $p['metal'] ?? '', $p['type'] ?? '', $p['weight'] ?? '', $p['unit'] ?? '', $p['purity'] ?? '', $p['brand'] ?? '', $p['packaging'] ?? '',
    ($p['pricing'] ?? '') === 'spot' ? '' : ($p['price'] ?? ''), ($p['pricing'] ?? '') === 'spot' ? ($p['premium_pct'] ?? 0) : '', ($p['pricing'] ?? '') === 'spot' ? ($p['premium_usd'] ?? 0) : '', $p['compare_at'] ?? '',
    $p['stock'] ?? 'in', !empty($p['featured']) ? 'yes' : '', implode('|', $p['images'] ?? []), $p['short'] ?? '', $p['description'] ?? '', $p['seo_title'] ?? '', $p['seo_desc'] ?? '', $p['gtin'] ?? ''], ',', '"', '\\');
  exit;
}

function page_import(){ ?>
<h1>Import products from CSV</h1>
<div class="card"><form method="post" enctype="multipart/form-data"><?= csrf_field() ?><input type="hidden" name="act" value="import">
  <label>CSV file<input type="file" name="csv" accept=".csv,text/csv,text/plain" required></label>
  <label class="check"><input type="radio" name="mode" value="merge" checked> Add new products and update existing ones (matched by SKU, or by name)</label>
  <label class="check"><input type="radio" name="mode" value="replace"> Replace all products with this file</label>
  <label class="check"><input type="checkbox" name="download" value="1" checked> Download product photos to my server (recommended — faster and they can’t disappear)</label>
  <button class="btn">Import</button></form></div>
<div class="card"><h2>What the file can contain</h2>
<p>The first row must be column names. Exports from WooCommerce, Shopify and most spreadsheets work as they are. Recognised columns (any one of the names in each group):</p>
<ul class="small">
<li><b>Name</b> (required): name, title, product name</li>
<li><b>Price</b>: price, sale price, regular price, variant price — a fixed price in USD</li>
<li><b>Or live pricing</b>: leave price empty and fill <b>premium_percent</b> and/or <b>premium_usd</b>: the price then follows the live spot price (spot × weight + premium)</li>
<li><b>Weight</b>: weight + unit (g, oz, kg). If missing, it’s read from the name (“1 oz”, “10 gram”, “1 kilo”, “1/10 oz”)</li>
<li><b>Category</b>: category, categories, type, collection — category names or addresses (e.g. 1-oz-gold-bars). If missing, products are filed automatically by metal, bar/coin and weight</li>
<li><b>Images</b>: images, image src, image url — several separated by | or ,</li>
<li>Also: sku, brand / vendor, purity, metal, description, short description, stock / quantity, compare at price, seo title, seo description, gtin / upc, featured, handle / slug</li>
</ul>
<p><a href="<?= aurl('template') ?>">Download a sample CSV</a></p></div>
<?php }

function page_template(){
  header('Content-Type: text/csv; charset=utf-8'); header('Content-Disposition: attachment; filename="products-sample.csv"');
  echo "name,sku,category,weight,unit,purity,brand,price,premium_percent,premium_usd,stock,images,short_description,description\n";
  echo "\"1 oz Gold Bar - PAMP Suisse Lady Fortuna (In Assay)\",PAMP-1OZ,1-oz-gold-bars,1,oz,.9999,PAMP Suisse,,3.5,0,in,https://example.com/pamp-1oz.jpg,\"One troy ounce of .9999 fine gold in sealed CertiPAMP packaging.\",\"<p>The PAMP Suisse Lady Fortuna 1 oz gold bar...</p>\"\n";
  echo "\"1 gram Gold Bar - Valcambi (In Assay)\",VAL-1G,1-gram-gold-bars,1,g,.9999,Valcambi,149.99,,,in,https://example.com/val-1g.jpg,\"1 gram of .9999 fine gold in a sealed assay card.\",\n";
  exit;
}

function page_product(){
  global $STORE;
  $i = idx('products', $_GET['id'] ?? '', 'id');
  $p = $i === null ? ['id'=>'', 'metal'=>'gold', 'type'=>'bar', 'unit'=>'oz', 'pricing'=>'fixed', 'stock'=>'in', 'purity'=>'.9999', 'categories'=>[], 'images'=>[]] : $STORE['products'][$i];
  $v = fn($k)=>h($p[$k] ?? ''); ?>
<p><a href="<?= aurl('products') ?>">← Products</a><?php if($i !== null): ?> · <a href="products/<?= h($p['slug']) ?>" target="_blank">View on site</a><?php endif; ?></p>
<h1><?= $i === null ? 'Add product' : h($p['name']) ?></h1>
<form method="post" enctype="multipart/form-data"><?= csrf_field() ?><input type="hidden" name="act" value="product"><input type="hidden" name="id" value="<?= $v('id') ?>">
<div class="card"><h2>Product</h2>
  <label>Name<input name="name" value="<?= $v('name') ?>" required placeholder="1 oz Gold Bar - PAMP Suisse Lady Fortuna"></label>
  <div class="grid3"><label>Brand / mint<input name="brand" value="<?= $v('brand') ?>"></label><label>SKU<input name="sku" value="<?= $v('sku') ?>"></label><label>Address (URL)<input name="slug" value="<?= $v('slug') ?>" placeholder="made from the name"></label></div>
  <div class="grid4"><label>Metal<select name="metal"><?php foreach(METALS as $k=>$l): ?><option value="<?= $k ?>"<?= ($p['metal'] ?? '') === $k ? ' selected' : '' ?>><?= $l ?></option><?php endforeach; ?></select></label>
    <label>Type<select name="type"><?php foreach(['bar'=>'Bar','coin'=>'Coin','round'=>'Round'] as $k=>$l): ?><option value="<?= $k ?>"<?= ($p['type'] ?? '') === $k ? ' selected' : '' ?>><?= $l ?></option><?php endforeach; ?></select></label>
    <label>Weight<input name="weight" value="<?= $v('weight') ?>" inputmode="decimal"></label>
    <label>Unit<select name="unit"><?php foreach(['g'=>'grams','oz'=>'troy oz','kg'=>'kilo'] as $k=>$l): ?><option value="<?= $k ?>"<?= ($p['unit'] ?? '') === $k ? ' selected' : '' ?>><?= $l ?></option><?php endforeach; ?></select></label></div>
  <div class="grid3"><label>Purity<input name="purity" value="<?= $v('purity') ?>" placeholder=".9999"></label><label>Packaging<input name="packaging" value="<?= $v('packaging') ?>" placeholder="Sealed assay card"></label><label>GTIN / UPC<input name="gtin" value="<?= $v('gtin') ?>"></label></div>
  <p class="label">Categories</p><div class="checks"><?php foreach($STORE['categories'] as $c): ?><label class="check"><input type="checkbox" name="categories[]" value="<?= h($c['slug']) ?>"<?= in_array($c['slug'], $p['categories'] ?? [], true) ? ' checked' : '' ?>> <?= h($c['name']) ?></label><?php endforeach; ?></div>
  <p class="hint">Leave all unticked to file it automatically by metal, type and weight.</p>
</div>
<div class="card"><h2>Price &amp; stock</h2>
  <label class="check"><input type="radio" name="pricing" value="fixed"<?= ($p['pricing'] ?? 'fixed') === 'fixed' ? ' checked' : '' ?>> Fixed price</label>
  <label class="check"><input type="radio" name="pricing" value="spot"<?= ($p['pricing'] ?? '') === 'spot' ? ' checked' : '' ?>> Live price: spot value of the metal + premium (follows the market automatically)</label>
  <div class="grid4"><label>Fixed price (USD)<input name="price" value="<?= $v('price') ?>" inputmode="decimal"></label><label>Premium %<input name="premium_pct" value="<?= $v('premium_pct') ?>" inputmode="decimal"></label><label>Premium $<input name="premium_usd" value="<?= $v('premium_usd') ?>" inputmode="decimal"></label><label>Compare-at price<input name="compare_at" value="<?= $v('compare_at') ?>" inputmode="decimal"></label></div>
  <?php if($i !== null): ?><p>Price on the site right now: <b><?= usd(product_price($p)) ?></b></p><?php endif; ?>
  <div class="grid3"><label>Stock<select name="stock"><?php foreach(STOCK_STATUSES as $k=>$l): ?><option value="<?= $k ?>"<?= ($p['stock'] ?? 'in') === $k ? ' selected' : '' ?>><?= $l ?></option><?php endforeach; ?></select></label><label>Sort (higher = first)<input name="sort" value="<?= $v('sort') ?>"></label><div><label class="check"><input type="checkbox" name="featured" value="1"<?= !empty($p['featured']) ? ' checked' : '' ?>> Featured on home page</label><label class="check"><input type="checkbox" name="hidden" value="1"<?= !empty($p['hidden']) ? ' checked' : '' ?>> Hide from the shop</label></div></div>
</div>
<div class="card"><h2>Photos</h2>
  <div class="imgs"><?php foreach($p['images'] ?? [] as $k=>$src): ?><div><img src="<?= h($src) ?>" alt="" width="110" height="110"><label class="check"><input type="checkbox" name="rm_img[<?= $k ?>]" value="1"> remove</label><?php if($k): ?><label class="check"><input type="radio" name="img_first" value="<?= $k ?>"> make main</label><?php endif; ?></div><?php endforeach; ?></div>
  <label>Upload photos<input type="file" name="photos[]" accept="image/*" multiple></label>
  <label>Or add photo addresses (one per line)<textarea name="img_urls" rows="2" placeholder="https://…"></textarea></label>
</div>
<div class="card"><h2>Description</h2>
  <label>Short description (shown next to the price)<textarea name="short" rows="2"><?= $v('short') ?></textarea></label>
  <label>Full description<textarea name="description" class="rich" rows="10"><?= $v('description') ?></textarea></label>
</div>
<div class="card"><h2>Google</h2><?php seo_fields($p, $p['name'] ?? ''); ?></div>
<button class="btn btn-lg">Save product</button>
</form>
<?php }

function seo_fields($row, $ph=''){ ?>
  <label>Google title <span class="count" data-max="60"></span><input name="seo_title" value="<?= h($row['seo_title'] ?? '') ?>" maxlength="90" placeholder="<?= h($ph) ?>" class="counted"></label>
  <p class="hint">The blue headline in Google. Aim for 50–60 characters with the main keyword first.</p>
  <label>Google description <span class="count" data-max="160"></span><textarea name="seo_desc" rows="2" maxlength="300" class="counted"><?= h($row['seo_desc'] ?? '') ?></textarea></label>
  <p class="hint">The text under the headline. Aim for 140–160 characters. Live prices like {gold_oz} work here.</p>
<?php }

function page_categories(){ item_list('categories', 'Categories', 'name'); }
function page_pages(){ item_list('pages', 'Pages', 'title'); }
function page_guides(){ item_list('guides', 'Guides', 'title'); }
function page_tools(){ item_list('tools', 'Price pages', 'h1'); }

function item_list($sec, $title, $field){
  global $STORE; ?>
<h1><?= h($title) ?><?php if($sec !== 'tools'): ?> <a class="btn" href="<?= aurl('edit', ['sec'=>$sec, 'new'=>1]) ?>">+ Add</a><?php endif; ?></h1>
<?php if($sec === 'tools'): ?><p class="muted">The live price tables and calculator are added automatically; here you edit the words around them.</p><?php endif; ?>
<div class="card"><table class="tbl"><thead><tr><th>Title</th><th>Address</th><th>Google title</th></tr></thead><tbody>
<?php foreach($STORE[$sec] as $r): $path = ($sec === 'guides' ? 'guides/' : '').$r['slug']; ?>
<tr><td><a href="<?= aurl('edit', ['sec'=>$sec, 'slug'=>$r['slug']]) ?>"><b><?= h($r[$field] ?? $r['slug']) ?></b></a><?= !empty($r['hidden']) ? ' <span class="muted">(hidden)</span>' : '' ?></td><td class="small"><a href="<?= h($path) ?>" target="_blank">/<?= h($path) ?></a></td><td class="small"><?= h($r['seo_title'] ?? '') ?></td></tr>
<?php endforeach; ?></tbody></table></div>
<?php }

function page_edit(){
  global $STORE;
  $sec = $_GET['sec'] ?? ''; if(!in_array($sec, ['pages','guides','tools','categories'], true)) return;
  $i = empty($_GET['new']) ? idx($sec, $_GET['slug'] ?? '') : null;
  $r = $i === null ? ['slug'=>'', 'faqs'=>[]] : $STORE[$sec][$i];
  $v = fn($k)=>h($r[$k] ?? '');
  $path = ($sec === 'guides' ? 'guides/' : '').($r['slug'] ?? '');
  $is_default = $i !== null && default_item($sec, $r['slug']); ?>
<p><a href="<?= aurl($sec) ?>">← Back</a><?php if($i !== null): ?> · <a href="<?= h($path) ?>" target="_blank">View on site</a><?php endif; ?></p>
<h1><?= $i === null ? 'Add' : h($r['title'] ?? $r['name'] ?? $r['h1']) ?></h1>
<form method="post"><?= csrf_field() ?><input type="hidden" name="act" value="item"><input type="hidden" name="sec" value="<?= h($sec) ?>"><input type="hidden" name="slug" value="<?= $v('slug') ?>">
<div class="card">
  <?php if($sec === 'categories'): ?><label>Name (menus and buttons)<input name="name" value="<?= $v('name') ?>" required></label>
  <?php elseif($sec !== 'tools'): ?><label>Title<?= $sec === 'pages' ? ' (menus and footer)' : '' ?><input name="title" value="<?= $v('title') ?>" required></label>
  <?php else: ?><label>Menu name<input name="nav" value="<?= $v('nav') ?>"></label><?php endif; ?>
  <?php if($sec !== 'guides'): ?><label>Main heading (H1)<input name="h1" value="<?= $v('h1') ?>"></label><?php endif; ?>
  <?php if($sec !== 'tools'): ?><label>Address<input name="newslug" value="<?= $v('slug') ?>" placeholder="made from the title"></label><p class="hint">Changing the address of a page Google already knows loses its ranking — avoid it.</p><?php endif; ?>
  <?php if($sec === 'pages'): ?><label>Footer group<select name="group"><?php foreach(['help'=>'Help','company'=>'Company','legal'=>'Legal'] as $k=>$l): ?><option value="<?= $k ?>"<?= ($r['group'] ?? '') === $k ? ' selected' : '' ?>><?= $l ?></option><?php endforeach; ?></select></label><?php endif; ?>
  <?php if($sec === 'guides'): ?>
    <div class="grid3"><label>Topic<select name="topic"><?php foreach(['basics'=>'Basics','bars'=>'Gold bars','coins'=>'Gold coins','karats'=>'Karats & jewelry gold','prices'=>'Prices & investing','safety'=>'Authenticity & safety','selling'=>'Selling'] as $k=>$l): ?><option value="<?= $k ?>"<?= ($r['topic'] ?? '') === $k ? ' selected' : '' ?>><?= $l ?></option><?php endforeach; ?></select></label>
    <label>Published<input type="date" name="date" value="<?= $v('date') ?: date('Y-m-d') ?>"></label><label>Updated<input type="date" name="updated" value="<?= date('Y-m-d') ?>"></label></div>
    <label>Summary (guide lists)<textarea name="summary" rows="2"><?= $v('summary') ?></textarea></label>
    <label class="check"><input type="checkbox" name="hidden" value="1"<?= !empty($r['hidden']) ? ' checked' : '' ?>> Hide this guide</label>
  <?php endif; ?>
  <?php if($sec === 'categories'): ?>
    <label>Parent category<select name="parent"><option value="">— none —</option><?php foreach($STORE['categories'] as $c) if($c['slug'] !== ($r['slug'] ?? '')): ?><option value="<?= h($c['slug']) ?>"<?= ($r['parent'] ?? '') === $c['slug'] ? ' selected' : '' ?>><?= h($c['name']) ?></option><?php endif; ?></select></label>
    <label class="check"><input type="checkbox" name="nav" value="1"<?= !empty($r['nav']) ? ' checked' : '' ?>> Show in menus (only once it has products)</label>
  <?php endif; ?>
  <?php if($sec === 'categories' || $sec === 'tools'): ?><label>Intro (top of the page)<textarea name="intro" rows="3"><?= $v('intro') ?></textarea></label><?php endif; ?>
</div>
<div class="card"><h2>Content</h2>
  <textarea name="body" class="rich" rows="24"><?= $v('body') ?></textarea>
  <p class="hint">Live values: {gold_oz} {gold_g} {silver_oz} {date} · {value:1oz} {value:10g:14k} (weight in g, oz, kg, lb, dwt, tola; optional karat) · on a line of its own: {calculator} · shop details: {email} {phone} {brand} {standard_price} {express_price} {pay_window}</p>
</div>
<div class="card"><h2>FAQs</h2><textarea name="faqs" rows="10" placeholder="Q: Question?&#10;A: Answer."><?= h(faqs_to_text($r['faqs'] ?? [])) ?></textarea><p class="hint">Write each as “Q: …” then “A: …” on the next line, with a blank line between them. They show as a FAQ section and are given to Google.</p></div>
<div class="card"><h2>Google</h2><?php seo_fields($r); ?></div>
<button class="btn btn-lg">Save</button>
</form>
<?php if($is_default): ?><form method="post" class="inline" onsubmit="return confirm('Replace your text with the original?')"><?= csrf_field() ?><input type="hidden" name="act" value="restore"><input type="hidden" name="sec" value="<?= h($sec) ?>"><input type="hidden" name="slug" value="<?= $v('slug') ?>"><button class="btn btn-ghost">Restore original text</button></form><?php endif; ?>
<?php if($i !== null && $sec !== 'tools'): ?><form method="post" class="inline" onsubmit="return confirm('Delete this permanently?')"><?= csrf_field() ?><input type="hidden" name="act" value="delete_item"><input type="hidden" name="sec" value="<?= h($sec) ?>"><input type="hidden" name="slug" value="<?= $v('slug') ?>"><button class="btn btn-danger">Delete</button></form><?php endif; ?>
<?php }

function page_home(){
  global $STORE; $S = $STORE['settings']; $v = fn($k)=>h($S[$k] ?? ''); ?>
<h1>Home page</h1>
<form method="post"><?= csrf_field() ?><input type="hidden" name="act" value="home">
<div class="card"><h2>Google (home page)</h2>
  <label>Google title<input name="home_seo_title" value="<?= $v('home_seo_title') ?>" class="counted"></label>
  <label>Google description<textarea name="home_seo_desc" rows="2" class="counted"><?= $v('home_seo_desc') ?></textarea></label></div>
<div class="card"><h2>Top banner</h2>
  <label>Small line above the heading<input name="hero_kicker" value="<?= $v('hero_kicker') ?>"></label>
  <label>Main heading (H1)<input name="hero_title" value="<?= $v('hero_title') ?>"></label>
  <label>Text<textarea name="hero_text" rows="3"><?= $v('hero_text') ?></textarea></label></div>
<div class="card"><h2>Home page article</h2><textarea name="body" class="rich" rows="24"><?= h($STORE['home']['body']) ?></textarea></div>
<div class="card"><h2>Home page FAQs</h2><textarea name="faqs" rows="12"><?= h(faqs_to_text($STORE['home']['faqs'])) ?></textarea></div>
<div class="card"><h2>Shop and guides pages</h2>
  <label>Shop: Google title<input name="shop_seo_title" value="<?= $v('shop_seo_title') ?>" class="counted"></label>
  <label>Shop: Google description<textarea name="shop_seo_desc" rows="2" class="counted"><?= $v('shop_seo_desc') ?></textarea></label>
  <label>Shop: intro text<textarea name="shop_intro" rows="2"><?= $v('shop_intro') ?></textarea></label>
  <label>Guides: Google title<input name="guides_seo_title" value="<?= $v('guides_seo_title') ?>" class="counted"></label>
  <label>Guides: Google description<textarea name="guides_seo_desc" rows="2" class="counted"><?= $v('guides_seo_desc') ?></textarea></label></div>
<button class="btn btn-lg">Save</button></form>
<?php }

function page_shipping(){
  global $STORE; ?>
<h1>Shipping</h1>
<form method="post"><?= csrf_field() ?><input type="hidden" name="act" value="shipping">
<?php foreach($STORE['shipping']['methods'] as $k=>$m): ?>
<div class="card"><h2><?= h($m['label']) ?></h2>
  <label class="check"><input type="checkbox" name="m[<?= $k ?>][enabled]" value="1"<?= !empty($m['enabled']) ? ' checked' : '' ?>> Offer this option</label>
  <div class="grid3"><label>Name<input name="m[<?= $k ?>][label]" value="<?= h($m['label']) ?>"></label><label>Price (USD)<input name="m[<?= $k ?>][price]" value="<?= h($m['price']) ?>" inputmode="decimal"></label><label>Delivery time<input name="m[<?= $k ?>][days]" value="<?= h($m['days']) ?>"></label></div>
  <label>Note shown at checkout<input name="m[<?= $k ?>][note]" value="<?= h($m['note']) ?>"></label>
  <label class="check"><input type="checkbox" name="m[<?= $k ?>][free_eligible]" value="1"<?= !empty($m['free_eligible']) ? ' checked' : '' ?>> Free when the free-shipping amount is reached</label>
</div>
<?php endforeach; ?>
<div class="card"><label>Free shipping on orders over (USD, 0 = off)<input name="free_over" value="<?= h($STORE['shipping']['free_over']) ?>" inputmode="decimal"></label></div>
<button class="btn btn-lg">Save</button></form>
<p class="muted">The Shipping &amp; Returns page shows these options automatically. Edit its text under <a href="<?= aurl('edit', ['sec'=>'pages', 'slug'=>'shipping-returns']) ?>">Pages</a>.</p>
<?php }

function page_payments(){
  global $STORE; $S = $STORE['settings']; ?>
<h1>Payment methods</h1>
<p class="muted">Customers choose a method at checkout. You then send them the details from the order page with one click. Your details are never shown on the website.</p>
<form method="post"><?= csrf_field() ?><input type="hidden" name="act" value="payments">
<?php foreach($STORE['payments'] as $k=>$m): ?>
<div class="card"><h2><?= h($m['label']) ?></h2>
  <label class="check"><input type="checkbox" name="m[<?= $k ?>][enabled]" value="1"<?= !empty($m['enabled']) ? ' checked' : '' ?>> Accept this method</label>
  <label>Name<input name="m[<?= $k ?>][label]" value="<?= h($m['label']) ?>"></label>
  <label>Shown at checkout<input name="m[<?= $k ?>][note]" value="<?= h($m['note']) ?>"></label>
  <label>Your payment details (sent to the customer — {total} fills in their order total)<textarea name="m[<?= $k ?>][details]" rows="3"><?= h($m['details']) ?></textarea></label>
</div>
<?php endforeach; ?>
<div class="card"><h2>Payment-details email</h2>
  <label>Subject<input name="pay_email_subject" value="<?= h($S['pay_email_subject']) ?>"></label>
  <label>Message<textarea name="pay_email_body" rows="14"><?= h($S['pay_email_body']) ?></textarea></label>
  <p class="hint">{name} {order} {total} {method} {details} (the details of their method, above) {shop} {email} {phone}</p></div>
<div class="card"><h2>Shipping email</h2>
  <label>Subject<input name="ship_email_subject" value="<?= h($S['ship_email_subject']) ?>"></label>
  <label>Message<textarea name="ship_email_body" rows="8"><?= h($S['ship_email_body']) ?></textarea></label>
  <p class="hint">{name} {order} {carrier} {tracking} {shop}</p></div>
<button class="btn btn-lg">Save</button></form>
<?php }

function page_spot(){
  global $STORE; $S = $STORE['settings']; $spot = spot(); $saved = data_read('spot') ?: []; ?>
<h1>Spot price</h1>
<div class="card"><p>Gold: <b><?= $spot['gold'] > 0 ? usd($spot['gold']) : '—' ?></b> · Silver: <b><?= $spot['silver'] > 0 ? usd($spot['silver']) : '—' ?></b> · Source: <?= h($spot['source'] ?: 'none') ?> · Updated <?= $spot['time'] ? date('M j, Y g:i A', $spot['time']) : 'never' ?></p>
<?php if(($S['spot_mode'] ?? 'auto') === 'auto' && empty($saved['gold'])): ?><p class="flash err">The live price feeds haven’t answered from your server yet. If this continues, ask your host to allow outgoing connections (cURL), or use manual prices below.</p><?php endif; ?></div>
<form method="post"><?= csrf_field() ?><input type="hidden" name="act" value="spot">
<div class="card">
  <label class="check"><input type="radio" name="spot_mode" value="auto"<?= ($S['spot_mode'] ?? 'auto') === 'auto' ? ' checked' : '' ?>> Live prices (updated every 10 minutes automatically)</label>
  <label class="check"><input type="radio" name="spot_mode" value="manual"<?= ($S['spot_mode'] ?? '') === 'manual' ? ' checked' : '' ?>> Manual prices</label>
  <div class="grid3"><label>Manual gold price per oz (USD)<input name="manual_gold" value="<?= h($S['manual_gold']) ?>" inputmode="decimal"></label><label>Manual silver price per oz (USD)<input name="manual_silver" value="<?= h($S['manual_silver']) ?>" inputmode="decimal"></label></div>
  <p class="hint">Manual prices are also used as a fallback if the live feeds have never answered.</p>
  <label class="check"><input type="checkbox" name="refresh" value="1"> Fetch a fresh live price now</label>
</div><button class="btn btn-lg">Save</button></form>
<?php }

function page_settings(){
  global $STORE; $S = $STORE['settings']; $v = fn($k)=>h($S[$k] ?? ''); ?>
<h1>Settings</h1>
<form method="post"><?= csrf_field() ?><input type="hidden" name="act" value="settings">
<div class="card"><h2>Business details</h2>
  <p class="hint">Real, consistent business details (name, phone, address) are one of the most important trust signals for Google on a site that sells gold — and for your customers.</p>
  <div class="grid2"><label>Shop name<input name="brand" value="<?= $v('brand') ?>"></label><label>Registered business name<input name="legal_name" value="<?= $v('legal_name') ?>" placeholder="e.g. Your Company LLC"></label></div>
  <div class="grid2"><label>Website address<input name="domain" value="<?= $v('domain') ?>"></label><label>Shop email (shown on the site)<input name="email" value="<?= $v('email') ?>"></label></div>
  <div class="grid2"><label>Phone (shown on the site)<input name="phone" value="<?= $v('phone') ?>"></label><label>Opening hours<input name="hours" value="<?= $v('hours') ?>"></label></div>
  <label>Business address<textarea name="address" rows="3"><?= $v('address') ?></textarea></label>
  <div class="grid3"><label>We send payment details…<input name="reply_time" value="<?= $v('reply_time') ?>"></label><label>Hours to pay (price lock)<input name="pay_window" value="<?= $v('pay_window') ?>"></label><label>We ship…<input name="dispatch_time" value="<?= $v('dispatch_time') ?>"></label></div>
  <label>Announcement bar<input name="announcement" value="<?= $v('announcement') ?>"></label>
  <div class="grid2"><label>Guides written by<input name="author" value="<?= $v('author') ?>"></label><label>Countries you ship to (comma separated)<input name="countries" value="<?= $v('countries') ?>"></label></div>
</div>
<div class="card"><h2>Email</h2>
  <div class="grid2"><label>New orders are sent to<input name="order_email" value="<?= $v('order_email') ?>"></label><label>Send emails from (leave empty to use the shop email)<input name="from_email" value="<?= $v('from_email') ?>"></label></div>
  <p class="hint">For reliable delivery, enter your mailbox’s SMTP details (from your email provider). Leave the server empty to use your host’s PHP mail.</p>
  <div class="grid4"><label>SMTP server<input name="smtp_host" value="<?= $v('smtp_host') ?>" placeholder="smtp.yourprovider.com"></label><label>Port<input name="smtp_port" value="<?= $v('smtp_port') ?>"></label>
    <label>Security<select name="smtp_secure"><option value="ssl"<?= $S['smtp_secure'] === 'ssl' ? ' selected' : '' ?>>SSL (465)</option><option value="tls"<?= $S['smtp_secure'] === 'tls' ? ' selected' : '' ?>>STARTTLS (587)</option></select></label>
    <label>Username<input name="smtp_user" value="<?= $v('smtp_user') ?>" autocomplete="off"></label></div>
  <label>Password <?= $S['smtp_pass'] ? '(saved — leave empty to keep it)' : '' ?><input type="password" name="smtp_pass" autocomplete="new-password"></label>
  <?php if($S['smtp_pass']): ?><label class="check"><input type="checkbox" name="smtp_clear" value="1"> Remove the saved password</label><?php endif; ?>
</div>
<div class="card"><h2>Google &amp; Bing</h2>
  <label>Google Search Console verification (paste the whole &lt;meta&gt; tag or just the code)<input name="gsc_verification" value="<?= $v('gsc_verification') ?>"></label>
  <label>Bing Webmaster verification<input name="bing_verification" value="<?= $v('bing_verification') ?>"></label>
  <label>Google Analytics 4 measurement ID<input name="ga4_id" value="<?= $v('ga4_id') ?>" placeholder="G-XXXXXXXXXX"></label>
</div>
<div class="card"><h2>Live chat (optional)</h2><label>Chat code (e.g. Tawk.to)<textarea name="chat_code" rows="4"><?= $v('chat_code') ?></textarea></label></div>
<button class="btn btn-lg">Save settings</button></form>
<div class="card"><h2>Send a test email</h2><form method="post" class="inline"><?= csrf_field() ?><input type="hidden" name="act" value="testmail"><input type="email" name="to" value="<?= $v('order_email') ?>" required> <button class="btn">Send test</button></form></div>
<?php }

function page_account(){
  global $AUTH; ?>
<h1>Login</h1>
<form method="post" class="card"><?= csrf_field() ?><input type="hidden" name="act" value="account">
  <label>Username<input name="user" value="<?= h($AUTH['user'] ?? '') ?>"></label>
  <label>New password (leave empty to keep)<input type="password" name="pass" autocomplete="new-password"></label>
  <label>Repeat new password<input type="password" name="pass2" autocomplete="new-password"></label>
  <label>Current password (required)<input type="password" name="current" required autocomplete="current-password"></label>
  <button class="btn">Update</button></form>
<?php }

/* ---------------- layout ---------------- */
function shell_start($title, $nav=false){
  global $NAV, $V;
  $flash = $_SESSION['admin_flash'] ?? []; unset($_SESSION['admin_flash']); ?><!DOCTYPE html>
<html lang="en"><head><meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1"><meta name="robots" content="noindex,nofollow">
<title><?= h($title) ?> · Admin</title><link rel="icon" href="assets/img/favicon.svg">
<style>
:root{--ink:#14202f;--muted:#6b7684;--line:#e4e0d6;--gold:#0a0a0a;--navy:#0a0a0a;--bg:#f5f5f5}
*{box-sizing:border-box}body{margin:0;font:15px/1.55 system-ui,-apple-system,"Segoe UI",Roboto,sans-serif;color:var(--ink);background:var(--bg)}
a{color:#0a0a0a}h1{font-size:26px;margin:0 0 18px;display:flex;gap:10px;align-items:center;flex-wrap:wrap}h2{font-size:18px;margin:0 0 12px}
.layout{display:grid;grid-template-columns:220px 1fr;min-height:100vh}
.side{background:var(--navy);padding:18px 12px;color:#cfd6e0}.side b{display:block;color:#fff;font-size:16px;padding:6px 10px 16px}
.side a{display:block;color:#cfd6e0;text-decoration:none;padding:8px 10px;border-radius:8px}.side a:hover,.side a.on{background:rgba(255,255,255,.1);color:#fff}
.main{padding:28px 32px;max-width:1150px;width:100%}
.card{background:#fff;border:1px solid var(--line);border-radius:12px;padding:20px;margin:0 0 18px}
label{display:block;font-weight:600;font-size:13.5px;margin:0 0 12px}
input,select,textarea{display:block;width:100%;margin-top:5px;font:inherit;font-weight:400;padding:9px 11px;border:1px solid #d3cdbf;border-radius:8px;background:#fff}
textarea{font-family:ui-monospace,Menlo,Consolas,monospace;font-size:13.5px}
.check{display:flex;gap:8px;align-items:center;font-weight:500}.check input{width:auto;margin:0}
.btn{display:inline-block;background:var(--gold);color:#fff;border:0;border-radius:8px;padding:10px 18px;font:600 14px system-ui;cursor:pointer;text-decoration:none}
.btn-ghost{background:#fff;color:var(--ink);box-shadow:inset 0 0 0 1px var(--line)}.btn-danger{background:#b3261e}.btn-lg{padding:13px 26px;font-size:15px}
.grid2,.grid3,.grid4{display:grid;gap:0 14px}.grid2{grid-template-columns:1fr 1fr}.grid3{grid-template-columns:repeat(3,1fr)}.grid4{grid-template-columns:repeat(4,1fr)}
.cols{display:grid;grid-template-columns:1fr 1fr;gap:18px}
.tbl{width:100%;border-collapse:collapse}.tbl th,.tbl td{text-align:left;padding:9px 8px;border-bottom:1px solid var(--line);vertical-align:top}.tbl th{font-size:12.5px;text-transform:uppercase;color:var(--muted)}.tbl img{object-fit:contain;border-radius:6px;background:#f6f4ef}
.r{text-align:right}.muted{color:var(--muted)}.small{font-size:13px}.hint{color:var(--muted);font-size:13px;margin:-6px 0 12px}.label{font-weight:600;font-size:13.5px;margin:0 0 6px}
.flash{padding:11px 14px;border-radius:8px;background:#eaf7ee;border:1px solid #cbe8d5;margin:0 0 14px}.flash.err{background:#fdf0ef;border-color:#f2cfcb;color:#8f1f18}
.stats{display:grid;grid-template-columns:repeat(4,1fr);gap:14px;margin-bottom:18px}.stat{background:#fff;border:1px solid var(--line);border-radius:12px;padding:16px;text-decoration:none;color:var(--ink)}.stat b{display:block;font-size:24px}.stat span{color:var(--muted);font-size:13px}
.todo{margin:0;padding-left:18px}.todo li{margin:0 0 10px}
.st{font-size:12px;font-weight:700;padding:3px 9px;border-radius:999px;background:#eef;color:#334}.st-new{background:#fff1d6;color:#7a4d00}.st-invoiced{background:#e6efff;color:#1f3f8f}.st-paid{background:#e3f6ea;color:#14633a}.st-shipped,.st-completed{background:#e9e9ef;color:#333}.st-cancelled{background:#fdeceb;color:#8f1f18}
.tabs a{margin-right:10px;text-decoration:none}.tabs a.on{font-weight:700;color:var(--ink)}
.checks{display:flex;flex-wrap:wrap;gap:4px 18px}.imgs{display:flex;gap:12px;flex-wrap:wrap;margin-bottom:12px}.imgs img{object-fit:contain;border:1px solid var(--line);border-radius:8px}
.inline{display:inline-flex;gap:8px;align-items:center;margin:0 8px 14px 0}.inline input{width:auto;margin:0}
.login{max-width:380px;margin:12vh auto;background:#fff;padding:28px;border-radius:14px;border:1px solid var(--line)}
.count{font-weight:400;color:var(--muted);float:right}
code{background:#f1eee6;padding:1px 5px;border-radius:4px}
@media(max-width:860px){.layout{grid-template-columns:1fr}.side{display:flex;flex-wrap:wrap;gap:2px}.side b{width:100%;padding-bottom:6px}.main{padding:18px 14px}.grid2,.grid3,.grid4,.cols,.stats{grid-template-columns:1fr 1fr}}
</style></head><body>
<?php if($nav): ?><div class="layout"><nav class="side"><b>Gold Bullion Shop</b><?php foreach($NAV as $k=>$l): ?><a href="<?= aurl($k) ?>"<?= $V === $k ? ' class="on"' : '' ?>><?= h($l) ?></a><?php endforeach; ?><a href="./" target="_blank">View shop ↗</a><a href="<?= aurl('logout') ?>">Sign out</a></nav><div class="main"><?php endif; ?>
<?php foreach($flash as [$t, $m]): ?><p class="flash<?= $t === 'err' ? ' err' : '' ?>"><?= h($m) ?></p><?php endforeach;
}

function shell_end(){
  global $V; ?>
<?php if(is_admin()): ?></div></div><?php endif; ?>
<script>
document.querySelectorAll('.counted').forEach(function(el){
  var lab = el.closest('label'), max = /desc/.test(el.name) ? 160 : 60, c = document.createElement('span'); c.className = 'count'; lab.insertBefore(c, lab.firstChild.nextSibling);
  var up = function(){ c.textContent = el.value.length + ' / ' + max; c.style.color = el.value.length > max ? '#b3261e' : ''; }; el.addEventListener('input', up); up();
});
</script>
<?php if(is_admin() && in_array($V, ['edit','home','product'], true)): ?>
<script src="https://cdnjs.cloudflare.com/ajax/libs/tinymce/6.8.3/tinymce.min.js" referrerpolicy="origin"></script>
<script>if(window.tinymce) tinymce.init({selector:'textarea.rich', plugins:'link lists table code autoresize', menubar:false, branding:false, promotion:false,
  toolbar:'blocks | bold italic | bullist numlist | link table | removeformat code', block_formats:'Paragraph=p; Heading 2=h2; Heading 3=h3',
  relative_urls:false, convert_urls:false, entity_encoding:'raw', min_height:380, content_style:'body{font:16px/1.6 system-ui;max-width:780px;margin:12px auto} table{border-collapse:collapse} td,th{border:1px solid #ddd;padding:6px}'});</script>
<?php endif; ?>
</body></html>
<?php }
