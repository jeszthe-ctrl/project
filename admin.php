<?php
/* =============================================================
   FUDAKURA — admin backend. Open /admin.php in your browser.

   The first visit asks you to create the admin password, so do
   that straight after uploading. Everything saved here goes to
   data/store.php (last 30 versions kept in data/backups/) and
   orders to data/orders/ — the storefront code is never touched.
   ============================================================= */

define('FK_ROOT', __DIR__);
require FK_ROOT.'/inc/store.php';
require FK_ROOT.'/inc/bitcoin.php';
start_session();
error_reporting(E_ALL & ~E_NOTICE & ~E_WARNING);
header('X-Frame-Options: DENY');
header('X-Robots-Tag: noindex, nofollow');
header('Cache-Control: no-store');
header('Referrer-Policy: same-origin');

$STORE = store_load();
$AUTH  = data_read('auth') ?: [];
$IP    = $_SERVER['REMOTE_ADDR'] ?? '';
const IMG_EXTS = ['jpg','jpeg','png','webp'];

/* ---------------- helpers ---------------- */
function self_url($v='dashboard', $extra=[]){ return 'admin.php?'.http_build_query(['v'=>$v] + $extra); }
function go($v='dashboard', $extra=[]){ header('Location: '.self_url($v, $extra)); exit; }
function note($msg, $type='ok'){ $_SESSION['admin_flash'][] = [$type, $msg]; }
function str($v){ return is_string($v) ? trim($v) : ''; }
function in_str($k){ return str($_POST[$k] ?? ''); }
function in_arr($k){ $v = $_POST[$k] ?? []; return is_array($v) ? $v : []; }
function num($v, $min=0){ return is_numeric($v) && (float)$v >= $min ? (float)$v : null; }
function codes($s){
  $out = [];
  foreach(preg_split('/[\s,]+/', strtoupper(str($s))) as $c){ if(preg_match('/^[A-Z]{2}$/', $c)) $out[] = $c; }
  return array_values(array_unique($out));
}
/* an amount in the shop currency (or, for an order, in the currency it was placed in) */
function usd($v, $code=null){ return money_in($v, $code ?? base_cur()); }

function is_admin(){ global $AUTH; return !empty($AUTH['key']) && hash_equals($AUTH['key'], (string)($_SESSION['admin'] ?? '')); }
function csrf(){ if(empty($_SESSION['admin_csrf'])) $_SESSION['admin_csrf'] = bin2hex(random_bytes(16)); return $_SESSION['admin_csrf']; }
function csrf_field(){ return '<input type="hidden" name="csrf" value="'.h(csrf()).'">'; }

/* login throttle: 5 failed attempts from one IP inside 15 minutes locks that IP until they age out */
function recent_fails(){ global $AUTH, $IP; return array_values(array_filter($AUTH['fails'][$IP] ?? [], fn($t)=>$t > time()-900)); }
function record_fail(){
  global $AUTH, $IP;
  $AUTH['fails'][$IP] = array_merge(recent_fails(), [time()]);
  foreach($AUTH['fails'] as $ip=>$ts){
    $AUTH['fails'][$ip] = array_values(array_filter($ts, fn($t)=>$t > time()-900));
    if(!$AUTH['fails'][$ip]) unset($AUTH['fails'][$ip]);
  }
  data_write('auth', $AUTH);
}
function log_in(){
  global $AUTH, $IP;
  session_regenerate_id(true);
  unset($AUTH['fails'][$IP]);
  data_write('auth', $AUTH);
  $_SESSION['admin'] = $AUTH['key'];
}

function save($msg){
  global $STORE;
  if(store_save($STORE)) note($msg);
  else note('Could not save. The data folder is not writable — ask your host to make it writable by PHP.', 'err');
}

/* formatting accepted in intros and guides (rendered by the shop) */
const FORMAT_HELP = 'Blank line = new paragraph · "## " heading, "### " subheading · "- " bullet, "1. " numbered step · **bold** · links: [text](product:ID), category:KEY, set:SLUG, cards:SLUG, guide:SLUG, page:shipping, or a full https:// address · {min_order} {free_ship} fill in your current values · in guides, a line with just {price_list}, {set_table} or {card_template} shows that tool · questions under "## Questions" written as "### Question" become FAQs for Google.';
/* first path segments the shop uses itself, so categories can't take them */
const RESERVED_SLUGS = ['shop','products','sets','cards','guides','cart','checkout','order-received','how-it-works','shipping',
                        'shipping-returns','returns','pay','pay-status',
                        'payment-methods','faq','contact','sitemap-xml','admin-php','index-php','assets','data','inc'];

function unique_slug($slug, $taken){
  $base = $slug; $n = 2;
  while(in_array($slug, $taken, true)) $slug = $base.'-'.$n++;
  return $slug;
}

function seo_inputs($name, $row, $ph_title='', $ph_desc=''){ ?>
  <div class="fld"><label class="f">Google title</label>
    <input type="text" name="<?= h($name) ?>[seo_title]" value="<?= h($row['seo_title'] ?? '') ?>" placeholder="<?= h($ph_title) ?>" maxlength="90">
    <div class="hint">Shown as the headline in Google. Under 60 characters works best; your shop name is added after it.</div></div>
  <div class="fld"><label class="f">Google description</label>
    <textarea name="<?= h($name) ?>[seo_desc]" rows="2" maxlength="300" placeholder="<?= h($ph_desc) ?>"><?= h($row['seo_desc'] ?? '') ?></textarea>
    <div class="hint">The snippet under the headline. Aim for 120–160 characters and include the words people search for.</div></div>
<?php }

function coll_products($c){
  global $STORE;
  $terms = array_filter(array_map('trim', explode(',', strtolower($c['match'] ?? ''))));
  $ids = $c['ids'] ?? []; $cond = $c['cond'] ?? '';
  return array_values(array_filter($STORE['products'], function($p) use($terms, $ids, $cond){
    if(!empty($p['hidden'])) return false;
    if(in_array($p['id'], $ids, true)) return true;
    if($cond !== '' && ($p['cond'] ?? 'Sealed') !== $cond) return false;
    if(!$terms) return $cond !== '';
    foreach($terms as $t){ if(strpos(strtolower($p['name']), $t) !== false) return true; }
    return false;
  }));
}

/* set names in admin order: saved set pages first, then any new set typed on a product */
function set_names(){
  global $STORE;
  $names = array_map('strval', array_keys($STORE['sets'] ?? []));   /* "151" comes back from array keys as a number */
  foreach($STORE['products'] as $p){ if($p['set'] !== '' && !in_array($p['set'], $names, true)) $names[] = $p['set']; }
  return $names;
}

function product_index($id){
  global $STORE;
  foreach($STORE['products'] as $i=>$p){ if($p['id'] === $id) return $i; }
  return null;
}

/* ---------------- photos: assets/products/{id}.ext, {id}-2.ext … {id}-4.ext ---------------- */
function photo_paths($id){
  $out = [];
  foreach(photo_slots() as $suf){
    foreach(IMG_EXTS as $e){
      $f = FK_ROOT."/assets/products/{$id}{$suf}.{$e}";
      if(is_file($f)){ $out[] = $f; break; }
    }
  }
  return $out;
}

/* put $files into the photo slots of $id in the given order (renames via temp names so slots can swap) */
function photos_arrange($id, $files){
  $dir = FK_ROOT.'/assets/products'; $tmp = [];
  foreach(glob("$dir/thumbs/{$id}-*") ?: [] as $old) @unlink($old);   /* small copies are remade on demand */
  foreach($files as $f){
    $t = "$dir/.tmp-".bin2hex(random_bytes(5)).'.'.pathinfo($f, PATHINFO_EXTENSION);
    if(@rename($f, $t)) $tmp[] = $t;
  }
  foreach(array_slice($tmp, 0, count(photo_slots())) as $k=>$t){
    @rename($t, "$dir/{$id}".photo_slots()[$k].'.'.pathinfo($t, PATHINFO_EXTENSION));
  }
}

function image_ext($file){
  $i = @getimagesize($file);
  $map = [IMAGETYPE_JPEG=>'jpg', IMAGETYPE_PNG=>'png', IMAGETYPE_WEBP=>'webp'];
  return $i ? ($map[$i[2]] ?? null) : null;
}

/* accepted uploads from a multiple file input, moved into assets/products under temp names */
function take_uploads($field, $limit){
  $f = $_FILES[$field] ?? null; $out = [];
  if(!$f || !is_array($f['name'])) return $out;
  $dir = FK_ROOT.'/assets/products';
  if(!is_dir($dir)) @mkdir($dir, 0755, true);
  foreach($f['name'] as $i=>$name){
    $err = $f['error'][$i] ?? UPLOAD_ERR_NO_FILE;
    if($err === UPLOAD_ERR_NO_FILE) continue;
    if(count($out) >= $limit){ note("Skipped “{$name}”: a product can have 4 photos.", 'err'); continue; }
    if($err !== UPLOAD_ERR_OK){ note("“{$name}” did not upload (it may be larger than your host allows).", 'err'); continue; }
    if($f['size'][$i] > 8*1024*1024){ note("Skipped “{$name}”: photos must be under 8 MB.", 'err'); continue; }
    $ext = image_ext($f['tmp_name'][$i]);
    if(!$ext){ note("Skipped “{$name}”: use a JPG, PNG or WebP image.", 'err'); continue; }
    $dest = "$dir/.up-".bin2hex(random_bytes(5)).".$ext";
    if(move_uploaded_file($f['tmp_name'][$i], $dest)){ shrink_image($dest); $out[] = $dest; }
    else note("Could not store “{$name}”. Make sure assets/products is writable.", 'err');
  }
  return $out;
}

/* ---------------- setup / login / logout ---------------- */
$do = in_str('do');
$v  = str($_GET['v'] ?? '') ?: 'dashboard';
$form_errors = []; $form = null;

/* an upload bigger than the host's post_max_size arrives as an empty POST */
if($_SERVER['REQUEST_METHOD'] === 'POST' && !$_POST && (int)($_SERVER['CONTENT_LENGTH'] ?? 0) > 0){
  note('That upload was too large for your hosting plan (limit '.ini_get('post_max_size').'). Try smaller photos, or fewer at once.', 'err');
  go(is_admin() ? $v : 'login', array_filter(['id'=>str($_GET['id'] ?? '')]));
}

if($_SERVER['REQUEST_METHOD'] === 'POST' && $do !== '' && !hash_equals(csrf(), in_str('csrf'))){
  note('Your session expired. Please try again.', 'err');
  go(is_admin() ? $v : 'login');
}

if(empty($AUTH['hash'])){
  if($do === 'setup'){
    $pw = (string)($_POST['password'] ?? '');
    if(strlen($pw) < 10)                         $form_errors[] = 'Use at least 10 characters.';
    elseif($pw !== (string)($_POST['confirm'] ?? '')) $form_errors[] = 'The two passwords do not match.';
    else {
      $AUTH = ['hash'=>password_hash($pw, PASSWORD_DEFAULT), 'key'=>bin2hex(random_bytes(16)), 'fails'=>[]];
      if(data_write('auth', $AUTH)){ log_in(); note('Password set. Welcome to your shop admin.'); go(); }
      $form_errors[] = 'Could not save. The data folder is not writable — ask your host to make it writable by PHP.';
    }
  }
  $v = 'setup';
} elseif(!is_admin()){
  if($do === 'login'){
    if(count(recent_fails()) >= 5) $form_errors[] = 'Too many attempts. Wait 15 minutes and try again.';
    elseif(password_verify((string)($_POST['password'] ?? ''), $AUTH['hash'])){ log_in(); go(); }
    else { record_fail(); usleep(400000); $form_errors[] = 'Wrong password.'; }
  }
  $v = 'login';
}

if(is_admin() && $v === 'logout'){
  unset($_SESSION['admin']); session_regenerate_id(true);
  go('login');
}

/* ---------------- actions (signed-in only) ---------------- */
if(is_admin() && $_SERVER['REQUEST_METHOD'] === 'POST'){

  /* ----- products ----- */
  if($do === 'product_save'){
    $old_id = in_str('old_id');
    $idx = $old_id === '' ? null : product_index($old_id);
    $name = in_str('name');
    $id = slugify(in_str('id') ?: $name);
    $ladder = [];
    foreach(in_arr('lq') as $i=>$q){
      $q = num($q, 1); $pr = num(in_arr('lp')[$i] ?? '', 0);
      if($q !== null && $pr !== null) $ladder[(int)$q] = [(int)$q, round($pr, 2)];
    }
    ksort($ladder);
    $p = [
      'id'=>$id, 'sku'=>in_str('sku'), 'name'=>$name, 'set'=>in_str('set'), 'cat'=>in_str('cat'),
      'moq'=>(int)(num(in_str('moq'), 1) ?? 0), 'step'=>(int)(num(in_str('step'), 1) ?? 0),
      'status'=>in_str('status'), 'cond'=>in_str('cond'), 'release'=>in_str('release'), 'weight'=>num(in_str('weight'), 0),
      'hidden'=>!empty($_POST['hidden']), 'ladder'=>array_values($ladder), 'desc'=>in_str('desc'),
      'seo_title'=>str(in_arr('seo')['seo_title'] ?? ''), 'seo_desc'=>str(in_arr('seo')['seo_desc'] ?? ''),
    ];
    if($name === '')                              $form_errors[] = 'Enter a product name.';
    if($id === '')                                $form_errors[] = 'Enter a web address (letters and numbers).';
    elseif(($clash = product_index($id)) !== null && $clash !== $idx) $form_errors[] = "Another product already uses the web address “{$id}”.";
    if(!isset($STORE['categories'][$p['cat']]))   $form_errors[] = 'Choose a category.';
    if(!array_key_exists($p['status'], PRODUCT_STATUSES)) $form_errors[] = 'Choose a status.';
    if(!in_array($p['cond'], CONDITIONS, true))  $form_errors[] = 'Choose a condition.';
    if($p['moq'] < 1)                             $form_errors[] = 'Minimum order quantity must be 1 or more.';
    if($p['step'] < 1)                            $form_errors[] = 'Sold-in-multiples-of must be 1 or more.';
    if($p['weight'] === null)                     $form_errors[] = 'Enter the weight in kg (0 if unknown).';
    if(!$p['ladder'])                             $form_errors[] = 'Enter at least one price (quantity and unit price).';
    if($old_id !== '' && $idx === null)           $form_errors[] = 'That product no longer exists.';

    if($form_errors){ $form = $p + ['old_id'=>$old_id]; $v = 'product'; }
    else {
      /* photos: keep the ones not ticked for removal, chosen main first, then new uploads */
      $cur  = photo_paths($old_id !== '' ? $old_id : $id);
      $rm   = array_map('intval', in_arr('rm'));
      $main = (int)in_str('main');
      $keep = [];
      foreach($cur as $i=>$f){ if(in_array($i, $rm, true)) @unlink($f); else $keep[$i] = $f; }
      if(isset($keep[$main])) $keep = [$main=>$keep[$main]] + $keep;
      $new = take_uploads('photos', count(photo_slots()) - count($keep));
      photos_arrange($id, array_merge(array_values($keep), $new));
      foreach(photos($id) as $rel){ thumb($rel); thumb($rel, 160); }   /* make the small copies now, not on a shopper's first visit */

      if($idx === null) $STORE['products'][] = $p; else $STORE['products'][$idx] = $p;
      save($idx === null ? "Added “{$name}”." : "Saved “{$name}”.");
      go('product', ['id'=>$id]);
    }
  }

  if($do === 'product_delete'){
    $idx = product_index(in_str('id'));
    if($idx !== null){
      $p = $STORE['products'][$idx];
      foreach(photo_paths($p['id']) as $f) @unlink($f);
      array_splice($STORE['products'], $idx, 1);
      save("Deleted “{$p['name']}”.");
    }
    go('products');
  }

  if($do === 'product_move'){
    $idx = product_index(in_str('id')); $to = $idx === null ? null : $idx + (in_str('dir') === 'up' ? -1 : 1);
    if($idx !== null && isset($STORE['products'][$to])){
      [$STORE['products'][$idx], $STORE['products'][$to]] = [$STORE['products'][$to], $STORE['products'][$idx]];
      save('Order updated.');
    }
    go('products');
  }

  if($do === 'product_copy'){
    $idx = product_index(in_str('id'));
    if($idx !== null){
      $p = $STORE['products'][$idx];
      $base = $p['id'].'-copy'; $id = $base; $n = 2;
      while(product_index($id) !== null) $id = $base.'-'.$n++;
      $p['id'] = $id; $p['name'] .= ' (copy)'; $p['hidden'] = true;
      array_splice($STORE['products'], $idx + 1, 0, [$p]);
      save('Copied. The copy is hidden until you edit and un-hide it.');
      go('product', ['id'=>$id]);
    }
    go('products');
  }

  /* ----- categories ----- */
  if($do === 'categories_save'){
    $used = array_count_values(array_column($STORE['products'], 'cat'));
    $cats = []; $slugs = array_merge(RESERVED_SLUGS, array_column($STORE['pages'] ?? [], 'slug'));
    foreach(in_arr('cats') as $row){
      if(!is_array($row)) continue;
      $given = str($row['key'] ?? '');
      $key = slugify($given ?: str($row['label'] ?? ''));
      $label = str($row['label'] ?? '');
      if($key === '' || $label === '') continue;
      if($given === ''){ $base = $key; $n = 2; while(isset($cats[$key]) || isset($STORE['categories'][$key])) $key = $base.'-'.$n++; }
      if(!empty($row['delete'])){
        if(!empty($used[$key])){ note("“{$label}” still has {$used[$key]} product(s), so it was kept. Move them first.", 'err'); }
        else continue;
      }
      $slug = slugify(str($row['slug'] ?? '') ?: $label);
      $fixed = unique_slug($slug, $slugs);
      if($fixed !== $slug) note("The web address “{$slug}” is already used, so “{$label}” is at “{$fixed}”.", 'err');
      $slugs[] = $fixed;
      $cats[$key] = ['label'=>$label, 'blurb'=>str($row['blurb'] ?? ''), 'slug'=>$fixed, 'h1'=>str($row['h1'] ?? ''),
                     'seo_title'=>str($row['seo_title'] ?? ''), 'seo_desc'=>str($row['seo_desc'] ?? ''), 'intro'=>str($row['intro'] ?? '')];
    }
    foreach($used as $key=>$n){ if(!isset($cats[$key]) && isset($STORE['categories'][$key])) $cats[$key] = $STORE['categories'][$key]; }
    $STORE['categories'] = $cats;
    save('Categories saved.');
    go('categories');
  }

  /* ----- sets and series pages ----- */
  if($do === 'sets_save'){
    foreach(in_arr('series') as $k=>$row){
      if(!is_array($row) || !isset($STORE['series'][$k])) continue;
      $STORE['series'][$k] = ['name'=>str($row['name'] ?? '') ?: $STORE['series'][$k]['name'],
        'slug'=>slugify(str($row['slug'] ?? '')) ?: $STORE['series'][$k]['slug'], 'h1'=>str($row['h1'] ?? ''),
        'seo_title'=>str($row['seo_title'] ?? ''), 'seo_desc'=>str($row['seo_desc'] ?? ''), 'intro'=>str($row['intro'] ?? '')];
    }
    $taken = array_column($STORE['series'], 'slug'); $sets = [];
    foreach(in_arr('sets') as $row){
      $name = is_array($row) ? str($row['name'] ?? '') : '';
      if($name === '') continue;
      $slug = slugify(str($row['slug'] ?? '') ?: $name);
      $fixed = unique_slug($slug, $taken); $taken[] = $fixed;
      if($fixed !== $slug) note("The web address “{$slug}” is already used, so {$name} is at “{$fixed}”.", 'err');
      $sets[$name] = ['slug'=>$fixed, 'series'=>isset($STORE['series'][$row['series'] ?? '']) ? $row['series'] : '',
        'code'=>str($row['code'] ?? ''), 'intro'=>str($row['intro'] ?? ''),
        'seo_title'=>str($row['seo_title'] ?? ''), 'seo_desc'=>str($row['seo_desc'] ?? '')];
    }
    $STORE['sets'] = $sets;
    save('Sets saved.');
    go('sets');
  }

  /* ----- collections (e.g. Charizard cards) ----- */
  if($do === 'collection_save' || $do === 'collection_delete'){
    $i = (int)in_str('i'); $exists = isset($STORE['collections'][$i]);
    if($do === 'collection_delete'){
      if($exists){ $t = $STORE['collections'][$i]['title']; array_splice($STORE['collections'], $i, 1); save("Deleted “{$t}”."); }
      go('collections');
    }
    $c = ['title'=>in_str('title'), 'h1'=>in_str('h1'), 'match'=>in_str('match'), 'cond'=>in_array(in_str('cond'), CONDITIONS, true) ? in_str('cond') : '',
          'ids'=>array_values(array_intersect(array_map('strval', in_arr('ids')), array_column($STORE['products'], 'id'))),
          'intro'=>in_str('intro'), 'seo_title'=>str(in_arr('seo')['seo_title'] ?? ''), 'seo_desc'=>str(in_arr('seo')['seo_desc'] ?? '')];
    $others = array_column(array_filter($STORE['collections'], fn($x, $k)=>$k !== $i || !$exists, ARRAY_FILTER_USE_BOTH), 'slug');
    $c['slug'] = unique_slug(slugify(in_str('slug') ?: $c['title']), $others);
    if($c['title'] === '') $form_errors[] = 'Enter a title.';
    if($c['match'] === '' && !$c['ids'] && $c['cond'] === '') $form_errors[] = 'Choose which products belong: match words, pick products, or pick a condition.';
    if($form_errors){ $form = $c + ['i'=>$exists ? $i : -1]; $v = 'collection'; }
    else {
      if($exists) $STORE['collections'][$i] = $c; else { $STORE['collections'][] = $c; $i = count($STORE['collections']) - 1; }
      save("Saved “{$c['title']}”.");
      go('collection', ['i'=>$i]);
    }
  }

  /* ----- guides ----- */
  if($do === 'guide_save' || $do === 'guide_delete'){
    $i = (int)in_str('i'); $exists = isset($STORE['guides'][$i]);
    if($do === 'guide_delete'){
      if($exists){ $t = $STORE['guides'][$i]['title']; array_splice($STORE['guides'], $i, 1); save("Deleted “{$t}”."); }
      go('guides');
    }
    $g = ['title'=>in_str('title'), 'body'=>in_str('body'), 'updated'=>gmdate('Y-m-d'),
          'seo_title'=>str(in_arr('seo')['seo_title'] ?? ''), 'seo_desc'=>str(in_arr('seo')['seo_desc'] ?? '')];
    $others = array_column(array_filter($STORE['guides'], fn($x, $k)=>$k !== $i || !$exists, ARRAY_FILTER_USE_BOTH), 'slug');
    $g['slug'] = unique_slug(slugify(in_str('slug') ?: $g['title']), $others);
    if($g['title'] === '') $form_errors[] = 'Enter a title.';
    if($g['body'] === '')  $form_errors[] = 'Write the guide text.';
    if($form_errors){ $form = $g + ['i'=>$exists ? $i : -1]; $v = 'guide'; }
    else {
      if($exists) $STORE['guides'][$i] = $g; else { $STORE['guides'][] = $g; $i = count($STORE['guides']) - 1; }
      save("Saved “{$g['title']}”.");
      go('guide', ['i'=>$i]);
    }
  }

  /* ----- info pages (About, Returns, Privacy, Terms) ----- */
  if($do === 'page_save' || $do === 'page_delete'){
    $i = (int)in_str('i'); $exists = isset($STORE['pages'][$i]);
    if($do === 'page_delete'){
      if($exists){ $t = $STORE['pages'][$i]['title']; array_splice($STORE['pages'], $i, 1); save("Deleted “{$t}”."); }
      go('pages');
    }
    $g = ['title'=>in_str('title'), 'body'=>in_str('body'),
          'seo_title'=>str(in_arr('seo')['seo_title'] ?? ''), 'seo_desc'=>str(in_arr('seo')['seo_desc'] ?? '')];
    $others = array_column(array_filter($STORE['pages'], fn($x, $k)=>$k !== $i || !$exists, ARRAY_FILTER_USE_BOTH), 'slug');
    $g['slug'] = unique_slug(slugify(in_str('slug') ?: $g['title']), array_merge($others, RESERVED_SLUGS, array_map(fn($k)=>($STORE['categories'][$k]['slug'] ?? '') ?: $k, array_keys($STORE['categories']))));
    if($g['title'] === '') $form_errors[] = 'Enter a title.';
    if($g['body'] === '')  $form_errors[] = 'Write the page text.';
    if($form_errors){ $form = $g + ['i'=>$exists ? $i : -1]; $v = 'page'; }
    else {
      if($exists) $STORE['pages'][$i] = $g; else { $STORE['pages'][] = $g; $i = count($STORE['pages']) - 1; }
      save("Saved “{$g['title']}”.");
      go('page', ['i'=>$i]);
    }
  }

  /* ----- shipping ----- */
  if($do === 'shipping_save'){
    $zones = []; $seen = [];
    $rates = function($row, $what){   /* ['standard'=>['base','per_kg'], 'express'=>…] or null if a number is missing */
      $out = [];
      foreach(SHIP_METHODS as $m){
        $base = num($row[$m]['base'] ?? '', 0); $kg = num($row[$m]['per_kg'] ?? '', 0);
        if($base === null || $kg === null){ note("$what was skipped: fill in all four prices.", 'err'); return null; }
        $out[$m] = ['base'=>round($base, 2), 'per_kg'=>round($kg, 2)];
      }
      if($out['express']['base'] < $out['standard']['base'] || $out['express']['per_kg'] < $out['standard']['per_kg'])
        note("$what: Express is cheaper than Standard somewhere — check that’s intended.", 'err');
      return $out;
    };
    foreach(in_arr('zones') as $row){
      if(!is_array($row) || !empty($row['delete'])) continue;
      $name = str($row['name'] ?? ''); $cc = codes($row['countries'] ?? '');
      if($name === '' && !$cc) continue;
      if($name === '' || !$cc){ note('A zone was skipped: it needs a name and country codes.', 'err'); continue; }
      $r = $rates($row, "“{$name}”"); if(!$r) continue;
      foreach($cc as $i=>$c){
        if(!isset($STORE['countries'][$c])){ note("$c in “{$name}” isn’t in your country list (Settings), so it was left out.", 'err'); unset($cc[$i]); continue; }
        if(isset($seen[$c])) note("$c is in both “{$seen[$c]}” and “{$name}” — the first zone is used.", 'err');
        $seen[$c] = $seen[$c] ?? $name;
      }
      $zones[] = ['name'=>$name, 'countries'=>array_values($cc)] + $r;
    }
    $rest = $rates(in_arr('rest'), 'Rest of world') ?? zone_rates($STORE['shipping']['rest']);
    $methods = [];
    foreach(ship_methods($STORE) as $m=>$old){
      $row = in_arr('methods')[$m] ?? [];
      $methods[$m] = ['label'=>str($row['label'] ?? '') ?: $old['label'], 'days'=>str($row['days'] ?? '') ?: $old['days']];
    }
    $STORE['shipping'] = ['methods'=>$methods, 'round_up'=>!empty($_POST['round_up']), 'zones'=>$zones, 'rest'=>$rest];
    $STORE['settings']['shipping_reviewed'] = true;
    $free = num(in_str('free_ship_usd'), 0);
    if($free !== null) $STORE['settings']['free_ship_usd'] = round($free, 2); else note('Free shipping: enter an amount in '.base_cur().', or 0 to turn it off. The old amount was kept.', 'err');
    $policy = in_str('shipping_policy');
    if($policy !== '') $STORE['settings']['shipping_policy'] = str_replace("\r", '', $policy);
    else note('The Shipping & Returns page text was empty, so the old text was kept.', 'err');
    if($policy !== '' && strpos($policy, '{rates}') === false) note('The Shipping & Returns text has no {rates} line, so the rate tables won’t show on that page.', 'err');
    save('Shipping rates saved.');
    go('shipping');
  }

  /* ----- payments ----- */
  if($do === 'payments_save'){
    $pays = [];
    foreach(in_arr('pays') as $row){
      if(!is_array($row) || !empty($row['delete'])) continue;
      $label = str($row['label'] ?? '');
      if($label === '') continue;
      $key = slugify(str($row['key'] ?? '') ?: $label); $base = $key; $n = 2;
      while(isset($pays[$key])) $key = $base.'-'.$n++;
      $where = str($row['countries'] ?? '');
      $pays[$key] = ['label'=>$label, 'note'=>str($row['note'] ?? ''),
                     'countries'=>($where === '' || $where === '*') ? '*' : codes($where),
                     'enabled'=>!empty($row['enabled'])];
      if(($row['type'] ?? '') === 'bitcoin') $pays[$key]['type'] = 'bitcoin';
    }
    $STORE['payments'] = $pays;
    $addr = btc_norm(in_str('btc_address'));
    if($addr === '' || btc_valid_address($addr)) $STORE['settings']['btc_address'] = $addr;
    else note("“{$addr}” isn’t a valid Bitcoin address (check for a missing or mistyped character), so the old address was kept.", 'err');
    foreach(['btc_quote_minutes'=>[10, 1440], 'btc_confirmations'=>[1, 6]] as $k=>[$lo, $hi]){ $n = num(in_str($k), $lo); if($n !== null) $STORE['settings'][$k] = (int)min($hi, $n); }
    if(array_filter($pays, fn($m)=>btc_method($m) && !empty($m['enabled'])) && !btc_ready()) note('A Bitcoin payment method is on, but there’s no valid wallet address, so it’s hidden from checkout.', 'err');
    save('Payment methods saved.');
    go('payments');
  }

  /* ----- settings, currencies, countries, hero photo ----- */
  if($do === 'settings_save'){
    $s = &$STORE['settings'];
    foreach(['email','order_email'] as $k){
      if(in_str($k) !== '' && !filter_var(in_str($k), FILTER_VALIDATE_EMAIL)){ note("“".in_str($k)."” isn’t a valid email address, so the old one was kept.", 'err'); $_POST[$k] = $s[$k]; }
    }
    foreach(['brand','kanji','tagline','legal_name','address','email','phone','order_email','domain','company_number','company_registered',
             'strip_text','strip_link_text','strip_link_url','hero_title','hero_lede','footer_blurb'] as $k) $s[$k] = in_str($k);
    $s['domain'] = rtrim($s['domain'], '/');
    foreach(['reply_hours','hold_hours'] as $k){ $n = num(in_str($k), 1); if($n !== null) $s[$k] = (int)$n; }
    $min = num(in_str('min_order_usd'), 0); if($min !== null) $s['min_order_usd'] = round($min, 2);
    $s['home_seo_title'] = str(in_arr('home')['seo_title'] ?? '');
    $s['home_seo_desc']  = str(in_arr('home')['seo_desc'] ?? '');
    $s['home_intro']     = in_str('home_intro');
    $s['pretty_urls']    = ['on'=>true, 'off'=>false][in_str('pretty_urls')] ?? 'auto';
    $s['chat_code']      = str_replace("\r", '', (string)($_POST['chat_code'] ?? ''));
    foreach(['google_verify', 'bing_verify'] as $k){   /* the whole <meta> tag or just its code */
      $vv = in_str($k); if(preg_match('/content=["\']([^"\']+)["\']/i', $vv, $m)) $vv = $m[1];
      $s[$k] = preg_replace('/[^A-Za-z0-9_\-]/', '', $vv);
    }
    $s['smtp_host']   = preg_replace('/[^A-Za-z0-9.\-]/', '', in_str('smtp_host'));
    $s['smtp_secure'] = in_array(in_str('smtp_secure'), ['ssl', 'tls', 'none'], true) ? in_str('smtp_secure') : 'tls';
    $s['smtp_port']   = (int)(num(in_str('smtp_port'), 1) ?? 0);
    $s['smtp_user']   = in_str('smtp_user');
    if((string)($_POST['smtp_pass'] ?? '') !== '') $s['smtp_pass'] = (string)$_POST['smtp_pass'];   /* blank keeps the saved one */
    if(!empty($_POST['smtp_clear'])) $s['smtp_pass'] = '';
    if(trim($s['chat_code']) !== '' && stripos($s['chat_code'], '<script') === false) note('The live chat code has no <script> tag, so it probably won’t work. Paste the whole snippet from your chat provider.', 'err');
    unset($s);

    $base = base_cur(); $curs = [];
    foreach(in_arr('curs') as $row){
      if(!is_array($row) || !empty($row['delete'])) continue;
      $code = strtoupper(str($row['code'] ?? ''));
      if(!preg_match('/^[A-Z]{3}$/', $code)) continue;
      $rate = num($row['rate'] ?? '', 0.000001);
      if($rate === null){ note("$code was skipped: enter a rate above 0.", 'err'); continue; }
      $curs[$code] = ['rate'=>$code === $base ? 1 : $rate, 'sym'=>str($row['sym'] ?? '') ?: $code.' ', 'dec'=>max(0, min(3, (int)($row['dec'] ?? 2)))];
    }
    if(!isset($curs[$base])) $curs = [$base=>$STORE['currencies'][$base] ?? ['rate'=>1,'sym'=>$base.' ','dec'=>2]] + $curs;
    $STORE['currencies'] = $curs;

    /* switching the shop currency converts every price at the rate in the table */
    $to = strtoupper(in_str('base_currency'));
    if($to !== '' && $to !== $base){
      $rebased = isset($curs[$to]) ? store_rebase($STORE, $to) : null;
      if($rebased){
        $STORE = $rebased;
        note("The shop now prices in $to. Every product price, shipping rate, the minimum order and the free-shipping amount were converted at 1 $base = {$curs[$to]['rate']} $to. Check your prices, and round any you want to look tidier.");
      } else note("$to isn’t in the currency table, so the shop currency wasn’t changed.", 'err');
    }

    $countries = [];
    foreach(preg_split('/\R/', (string)($_POST['countries'] ?? '')) as $line){
      if(preg_match('/^\s*([A-Za-z]{2})\s*[=:,]\s*(.+?)\s*$/', $line, $m)) $countries[strtoupper($m[1])] = $m[2];
    }
    if($countries) $STORE['countries'] = $countries; else note('The country list was empty, so the old list was kept.', 'err');

    if(!empty($_POST['hero_remove'])) foreach(photo_paths('hero') as $f) @unlink($f);
    if($up = take_uploads('hero', 1)){ foreach(photo_paths('hero') as $f) @unlink($f); photos_arrange('hero', $up); foreach(photos('hero') as $rel) thumb($rel); }

    save('Settings saved.');
    go('settings');
  }

  /* ----- test email ----- */
  if($do === 'mail_test'){
    $to = in_str('to') ?: $STORE['settings']['order_email'];
    if(!filter_var($to, FILTER_VALIDATE_EMAIL)) note('Enter a valid email address to send the test to.', 'err');
    else {
      $how = trim($STORE['settings']['smtp_host'] ?? '') !== '' ? 'your SMTP server ('.$STORE['settings']['smtp_host'].')' : 'your web host’s mail (PHP mail)';
      $ok = shop_mail($to, 'Test email from your '.$STORE['settings']['brand'].' shop',
        "This is a test from your shop admin.\n\nIf you're reading this, order emails reach this address: new orders are sent to "
        .$STORE['settings']['order_email']." and confirmations to each customer, the moment an order is placed.\n\nSent through $how at ".gmdate('Y-m-d H:i')." UTC.\n");
      data_write('mail-test', ['time'=>time(), 'to'=>$to, 'ok'=>$ok, 'error'=>$GLOBALS['FK_MAIL_ERROR'] ?? '']);
      if($ok) note("Test email handed to $how for $to. Check that inbox (and the spam folder) in the next minute or two.");
      else note('The test email couldn’t be sent. '.($GLOBALS['FK_MAIL_ERROR'] ?? ''), 'err');
    }
    go('settings');
  }

  /* ----- FAQ ----- */
  if($do === 'faq_save'){
    $faqs = [];
    foreach(in_arr('faqs') as $row){
      if(!is_array($row) || !empty($row['delete'])) continue;
      $qq = str($row['q'] ?? ''); $aa = str($row['a'] ?? '');
      if($qq !== '' && $aa !== '') $faqs[] = [$qq, $aa];
    }
    $STORE['faqs'] = $faqs;
    save('FAQ saved.');
    go('faq');
  }

  /* ----- orders ----- */
  if($do === 'order_update'){
    $o = order_load(in_str('ref'));
    if($o){
      if(array_key_exists(in_str('status'), ORDER_STATUSES)) $o['status'] = in_str('status');
      $o['admin_note'] = in_str('admin_note');
      if(order_save($o)) note("Order {$o['ref']} updated."); else note('Could not save the order.', 'err');
      go('order', ['ref'=>$o['ref']]);
    }
    go('orders');
  }

  if($do === 'btc_check' || $do === 'btc_attach'){
    $o = order_load(in_str('ref'));
    if($o && !empty($o['btc'])){
      if($do === 'btc_attach'){
        $txid = strtolower(in_str('txid'));
        if(preg_match('#/tx/([0-9a-f]{64})#i', $txid, $m)) $txid = strtolower($m[1]);
        $claims = btc_claims();
        if(!preg_match('/^[0-9a-f]{64}$/', $txid)) note('That isn’t a transaction ID (64 letters and numbers).', 'err');
        elseif(isset($claims[$txid]) && $claims[$txid] !== $o['ref']) note("That transaction is already matched to order {$claims[$txid]}.", 'err');
        elseif(!($r = btc_lookup_tx($txid, $o['btc']['address']))) note('The block explorers didn’t answer. Try again in a minute.', 'err');
        elseif(!$r['exists']) note('That transaction isn’t on the Bitcoin network (yet).', 'err');
        elseif(!$r['found']) note('That transaction doesn’t pay '.$o['btc']['address'].'.', 'err');
        else { $before = btc_state($o); btc_attach($o, $r['found'], $r['tip']); $after = btc_state($o);
               if($after === 'seen') btc_mail($o, 'received');
               elseif($after !== $before){ if($after === 'confirmed' && in_array($o['status'] ?? 'new', ['new','invoiced'], true)) $o['status'] = 'paid'; btc_mail($o, $after); }
               note('Transaction attached and receipts emailed.'); }
      } else { btc_quote($o); btc_sync($o, true); note('Checked the blockchain: '.btc_state_label(btc_state($o)).'.'); }
      order_save($o);
      go('order', ['ref'=>$o['ref']]);
    }
    go('orders');
  }

  if($do === 'order_delete'){
    $ref = in_str('ref');
    if(valid_ref($ref) && @unlink(data_file('orders/'.$ref))) note("Order $ref deleted.");
    go('orders');
  }

  /* ----- password ----- */
  if($do === 'password'){
    $new = (string)($_POST['new'] ?? '');
    if(!password_verify((string)($_POST['current'] ?? ''), $AUTH['hash'])) $form_errors[] = 'Your current password is wrong.';
    elseif(strlen($new) < 10)                                    $form_errors[] = 'Use at least 10 characters.';
    elseif($new !== (string)($_POST['confirm'] ?? ''))           $form_errors[] = 'The two new passwords do not match.';
    else {
      $AUTH['hash'] = password_hash($new, PASSWORD_DEFAULT);
      $AUTH['key']  = bin2hex(random_bytes(16));      // signs out every other session
      if(data_write('auth', $AUTH)){ $_SESSION['admin'] = $AUTH['key']; note('Password changed. Other devices have been signed out.'); go(); }
      $form_errors[] = 'Could not save the new password.';
    }
    $v = 'password';
  }
}

/* ---------------- view data ---------------- */
$NAV = ['dashboard'=>'Dashboard', 'orders'=>'Orders', 'products'=>'Products', 'categories'=>'Categories',
        'sets'=>'Sets', 'collections'=>'Collections', 'guides'=>'Guides', 'pages'=>'Pages',
        'shipping'=>'Shipping', 'payments'=>'Payments', 'settings'=>'Settings', 'faq'=>'FAQ', 'password'=>'Password'];
if(is_admin() && !isset($NAV[$v]) && !in_array($v, ['product','order','collection','guide','page'], true)) $v = 'dashboard';
$nav_on = ['product'=>'products', 'order'=>'orders', 'collection'=>'collections', 'guide'=>'guides', 'page'=>'pages'][$v] ?? $v;
$flash = $_SESSION['admin_flash'] ?? []; unset($_SESSION['admin_flash']);
$writable = is_writable(data_dir()) && (is_dir(FK_ROOT.'/assets/products') ? is_writable(FK_ROOT.'/assets/products') : is_writable(FK_ROOT.'/assets'));
$S = $STORE['settings'];
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<meta name="robots" content="noindex, nofollow">
<title><?= h(($NAV[$nav_on] ?? 'Sign in').' · '.$S['brand'].' admin') ?></title>
<style>
:root{
  color-scheme: light dark;
  --paper:#EEF0F5; --card:#FFF; --ink:#101A31; --ink2:#4A5672; --muted:#6B7690; --line:#CDD5E2; --hair:#E3E8F0;
  --brand:#1F3573; --brand2:#2E4B9C; --onbrand:#F5F7FC; --deep:#0D1630; --ondeep:#DCE3F3; --seal:#C2392A; --ok:#1E7A4C; --gold:#A8842F;
}
@media (prefers-color-scheme:dark){:root{
  --paper:#090E1C; --card:#101832; --ink:#E8ECF8; --ink2:#AFBAD4; --muted:#8693B0; --line:#26314D; --hair:#1A2338;
  --brand:#93A9EA; --brand2:#AEC0F5; --onbrand:#0A1024; --deep:#050914; --ondeep:#D5DEF2; --seal:#EC7361; --ok:#5CC08F; --gold:#DBB663;
}}
*,*::before,*::after{box-sizing:border-box}
body{margin:0;background:var(--paper);color:var(--ink);font:15px/1.55 system-ui,-apple-system,'Segoe UI',Roboto,sans-serif;font-variant-numeric:tabular-nums}
a{color:var(--brand)} h1,h2,h3{line-height:1.2;margin:0}
button,input,select,textarea{font:inherit;color:inherit}
:focus-visible{outline:2px solid var(--brand2);outline-offset:2px}
.top{background:var(--deep);color:var(--ondeep)}
.top .in{max-width:1180px;margin:auto;padding:0 16px;display:flex;align-items:center;gap:14px;min-height:52px}
.top b{letter-spacing:.14em}.top .sp{margin-left:auto;display:flex;gap:14px;font-size:14px}
.top a{color:var(--ondeep)}
nav.tabs{background:var(--card);border-bottom:1px solid var(--line)}
nav.tabs .in{max-width:1180px;margin:auto;padding:0 10px;display:flex;overflow-x:auto;scrollbar-width:none}
nav.tabs a{padding:12px 12px;text-decoration:none;color:var(--ink2);white-space:nowrap;border-bottom:2px solid transparent;font-size:14.5px}
nav.tabs a.on{color:var(--ink);border-bottom-color:var(--seal);font-weight:700}
nav.tabs a .ct{background:var(--seal);color:#fff;border-radius:9px;padding:0 6px;font-size:11.5px;margin-left:4px}
main{max-width:1180px;margin:auto;padding:22px 16px 60px}
h1{font-size:24px;margin-bottom:6px}
.sub{color:var(--muted);font-size:14px;margin-bottom:18px}
.card{background:var(--card);border:1px solid var(--line);border-radius:6px;padding:18px;margin-bottom:16px}
.card h2{font-size:17px;margin-bottom:12px}
.msg{border-radius:5px;padding:11px 14px;margin-bottom:14px;font-size:14px;border-left:4px solid var(--ok);background:color-mix(in srgb,var(--ok) 10%,var(--card))}
.msg.err{border-left-color:var(--seal);background:color-mix(in srgb,var(--seal) 10%,var(--card))}
.msg.warn{border-left-color:var(--gold);background:color-mix(in srgb,var(--gold) 12%,var(--card))}
.btn{display:inline-block;border:1px solid var(--brand);background:var(--brand);color:var(--onbrand);border-radius:4px;
  padding:9px 16px;font-weight:700;cursor:pointer;text-decoration:none;font-size:14px}
.btn:hover{background:var(--brand2)}
.btn.g{background:transparent;color:var(--ink);border-color:var(--line)}
.btn.d{background:transparent;color:var(--seal);border-color:var(--seal)}
.btn.s{padding:5px 10px;font-size:13px;font-weight:600}
.row{display:flex;gap:10px;flex-wrap:wrap;align-items:center}
.grid2{display:grid;grid-template-columns:1fr 1fr;gap:14px}
.grid3{display:grid;grid-template-columns:repeat(3,1fr);gap:14px}
@media(max-width:720px){.grid2,.grid3{grid-template-columns:1fr}}
label.f{display:block;font-size:13px;font-weight:700;color:var(--ink2);margin-bottom:4px}
.fld{margin-bottom:13px}
.fld .hint{font-size:12.5px;color:var(--muted);margin-top:3px}
input[type=text],input[type=email],input[type=password],input[type=number],input[type=url],select,textarea{
  width:100%;background:var(--paper);border:1px solid var(--line);border-radius:4px;padding:8px 10px;font-size:14.5px}
textarea{min-height:90px;resize:vertical}
table.t{width:100%;border-collapse:collapse;font-size:14px}
table.t th{text-align:left;font-size:12px;color:var(--muted);padding:8px;border-bottom:1px solid var(--line);white-space:nowrap}
table.t td{padding:8px;border-bottom:1px solid var(--hair);vertical-align:middle}
table.t td.r,table.t th.r{text-align:right}
table.t input,table.t select,table.t textarea{min-width:70px}
.scroll{overflow-x:auto}
.thumb{width:46px;height:46px;border-radius:4px;object-fit:cover;background:var(--hair);display:block}
.pill{display:inline-block;font-size:11.5px;font-weight:700;padding:1px 8px;border-radius:10px;background:var(--hair);color:var(--ink2);white-space:nowrap}
.pill.new,.pill.s-new{background:var(--brand);color:var(--onbrand)}
.pill.s-soldout,.pill.s-cancelled{background:var(--muted);color:var(--paper)}
.pill.s-preorder{background:var(--gold);color:#1B1405}
.pill.s-low{background:var(--seal);color:#fff}
.pill.s-paid,.pill.s-shipped{background:var(--ok);color:#fff}
.pill.s-invoiced{background:var(--gold);color:#1B1405}
.pill.hid{background:transparent;border:1px dashed var(--muted)}
.stats{display:grid;grid-template-columns:repeat(4,1fr);gap:12px;margin-bottom:16px}
@media(max-width:720px){.stats{grid-template-columns:1fr 1fr}}
.stats div{background:var(--card);border:1px solid var(--line);border-radius:6px;padding:14px}
.stats b{display:block;font-size:24px}
.stats span{font-size:13px;color:var(--muted)}
.photos{display:flex;gap:12px;flex-wrap:wrap;margin-bottom:10px}
.photos figure{margin:0;width:120px;font-size:12.5px}
.photos img{width:120px;height:120px;object-fit:cover;border-radius:5px;border:1px solid var(--line);display:block;margin-bottom:5px}
.photos label{display:flex;gap:5px;align-items:center}
.ladder td{padding:4px 6px 4px 0}
.ladder input{width:120px}
.inline{display:inline}
details.card>summary{cursor:pointer;list-style:none}
details.card>summary::-webkit-details-marker{display:none}
details.card>summary::before{content:"▸ ";color:var(--muted)}
details.card[open]>summary::before{content:"▾ "}
select[multiple]{padding:4px}
.auth{max-width:380px;margin:60px auto}
.muted{color:var(--muted)} .small{font-size:13px}
dl.kv{display:grid;grid-template-columns:130px 1fr;gap:6px 12px;margin:0;font-size:14px}
dl.kv dt{color:var(--muted)} dl.kv dd{margin:0;word-break:break-word}
</style>
</head>
<body>

<div class="top"><div class="in">
  <b><?= h($S['brand']) ?></b><span class="muted small">admin</span>
  <?php if(is_admin()): ?>
  <div class="sp"><a href="index.php" target="_blank" rel="noopener">View shop ↗</a><a href="<?= h(self_url('logout')) ?>">Sign out</a></div>
  <?php endif; ?>
</div></div>

<?php if(is_admin()):
  $new_orders = count(array_filter(orders_all(), fn($o)=>($o['status'] ?? 'new') === 'new')); ?>
<nav class="tabs"><div class="in">
  <?php foreach($NAV as $k=>$label): ?>
    <a href="<?= h(self_url($k)) ?>" class="<?= $nav_on===$k?'on':'' ?>"><?= h($label) ?><?php if($k==='orders' && $new_orders): ?><span class="ct"><?= $new_orders ?></span><?php endif; ?></a>
  <?php endforeach; ?>
</div></nav>
<?php endif; ?>

<main>
<?php foreach($flash as [$type, $msg]): ?><div class="msg <?= $type==='err'?'err':'' ?>"><?= h($msg) ?></div><?php endforeach; ?>
<?php if($form_errors): ?><div class="msg err"><?php foreach($form_errors as $e) echo '<div>'.h($e).'</div>'; ?></div><?php endif; ?>
<?php if(!$writable): ?><div class="msg err">The <b>data</b> or <b>assets/products</b> folder is not writable, so changes can’t be saved. Ask your host to make them writable by PHP.</div><?php endif; ?>

<?php if($v === 'setup'): ?>
  <div class="auth card">
    <h1>Create your admin password</h1>
    <p class="sub">This is the first visit to your shop admin. Choose a password of at least 10 characters — you’ll use it to sign in from now on.</p>
    <form method="post"><?= csrf_field() ?><input type="hidden" name="do" value="setup">
      <div class="fld"><label class="f" for="pw">Password</label><input type="password" id="pw" name="password" autocomplete="new-password" required minlength="10"></div>
      <div class="fld"><label class="f" for="pw2">Repeat password</label><input type="password" id="pw2" name="confirm" autocomplete="new-password" required minlength="10"></div>
      <button class="btn" type="submit">Create password</button>
    </form>
  </div>

<?php elseif($v === 'login'): ?>
  <div class="auth card">
    <h1>Sign in</h1>
    <p class="sub"><?= h($S['brand']) ?> shop admin</p>
    <form method="post"><?= csrf_field() ?><input type="hidden" name="do" value="login">
      <div class="fld"><label class="f" for="pw">Password</label><input type="password" id="pw" name="password" autocomplete="current-password" required autofocus></div>
      <button class="btn" type="submit">Sign in</button>
    </form>
  </div>

<?php elseif($v === 'dashboard'):
  $orders = orders_all();
  $by = array_count_values(array_map(fn($o)=>$o['status'] ?? 'new', $orders));
  $no_photo = array_filter($STORE['products'], fn($p)=>!photo_paths($p['id']));
  $visible = array_filter($STORE['products'], fn($p)=>empty($p['hidden'])); ?>
  <h1>Dashboard</h1>
  <p class="sub">Everything you change here updates the shop straight away.</p>
  <?php if(empty($S['shipping_reviewed'])): ?><div class="msg warn">Shipping rates are still the <b>placeholder values</b> the site shipped with. Set your real rates in <a href="<?= h(self_url('shipping')) ?>">Shipping</a> — they decide the order total and the <?= usd($S['min_order_usd']) ?> minimum.</div><?php endif; ?>
  <?php if(in_array(trim($S['address']), ['', 'Japan'], true)): ?><div class="msg warn">Your business address is just “<?= h($S['address'] ?: 'blank') ?>”. Add the full address in <a href="<?= h(self_url('settings')) ?>">Settings</a> — buyers look for it before ordering.</div><?php endif; ?>
  <?php $failed = array_filter(array_slice($orders, 0, 20), fn($o)=>isset($o['mail_shop']) && !$o['mail_shop']);
  if($failed): ?><div class="msg err"><?= count($failed) ?> recent order email<?= count($failed)===1?'':'s' ?> to <?= h($S['order_email']) ?> failed to send. The orders are safe here, but check with your host that PHP can send mail from <?= h($S['email']) ?>.</div><?php endif; ?>
  <?php if(!data_read('mail-test')): ?><div class="msg warn">Send yourself a test email in <a href="<?= h(self_url('settings')) ?>#email-settings">Settings → Email</a> to check that order emails reach <?= h($S['order_email']) ?> and your customers.</div><?php endif; ?>
  <?php if(!clean_urls($S)): ?><div class="msg warn">Clean page addresses are off. They help Google — see <a href="<?= h(self_url('settings')) ?>">Settings → Google &amp; web addresses</a> to test and turn them on.</div><?php endif; ?>
  <?php if($no_photo): ?><div class="msg warn"><?= count($no_photo) ?> product<?= count($no_photo)===1?' has':'s have' ?> no photo yet. Add photos from <a href="<?= h(self_url('products')) ?>">Products</a>.</div><?php endif; ?>
  <div class="stats">
    <div><b><?= (int)($by['new'] ?? 0) ?></b><span>New orders</span></div>
    <div><b><?= (int)($by['invoiced'] ?? 0) ?></b><span>Awaiting payment</span></div>
    <div><b><?= (int)($by['paid'] ?? 0) ?></b><span>Paid, to ship</span></div>
    <div><b><?= count($visible) ?></b><span>Products live</span></div>
  </div>
  <div class="card">
    <div class="row" style="justify-content:space-between;margin-bottom:8px"><h2 style="margin:0">Latest orders</h2><a href="<?= h(self_url('orders')) ?>">All orders →</a></div>
    <?php if(!$orders): ?><p class="muted">No orders yet. New orders appear here as soon as they’re placed.</p>
    <?php else: orders_table(array_slice($orders, 0, 6)); endif; ?>
  </div>
  <div class="row"><a class="btn" href="<?= h(self_url('product')) ?>">+ Add a product</a><a class="btn g" href="<?= h(self_url('settings')) ?>">Business details</a></div>

<?php elseif($v === 'orders'):
  $orders = orders_all(); $f = str($_GET['status'] ?? '');
  if(array_key_exists($f, ORDER_STATUSES)) $orders = array_filter($orders, fn($o)=>($o['status'] ?? 'new') === $f); ?>
  <h1>Orders</h1>
  <p class="sub">Every order placed on the shop, newest first. Open one to see the customer’s details and update its status.</p>
  <div class="row" style="margin-bottom:14px">
    <a class="btn s <?= $f===''?'':'g' ?>" href="<?= h(self_url('orders')) ?>">All</a>
    <?php foreach(ORDER_STATUSES as $k=>$label): ?><a class="btn s <?= $f===$k?'':'g' ?>" href="<?= h(self_url('orders', ['status'=>$k])) ?>"><?= h($label) ?></a><?php endforeach; ?>
  </div>
  <div class="card"><?php if(!$orders): ?><p class="muted">No orders here.</p><?php else: orders_table($orders); endif; ?></div>

<?php elseif($v === 'order'):
  $o = order_load(str($_GET['ref'] ?? ''));
  if($o && !empty($o['btc']) && in_array(btc_state($o), ['awaiting','reported','seen'], true) && btc_sync($o)) order_save($o);
  if(!$o): ?><p>That order doesn’t exist. <a href="<?= h(self_url('orders')) ?>">Back to orders</a></p>
  <?php else: $st = $o['status'] ?? 'new'; ?>
  <p class="small"><a href="<?= h(self_url('orders')) ?>">← Orders</a></p>
  <h1>Order <?= h($o['ref']) ?> <span class="pill s-<?= h($st) ?>"><?= h(status_label(ORDER_STATUSES, $st)) ?></span></h1>
  <p class="sub">Placed <?= h($o['time']) ?> · pays by <b><?= h($o['payment_label']) ?></b> · shown to customer in <?= h($o['currency']) ?></p>
  <?php if(isset($o['mail_shop']) && (!$o['mail_shop'] || !$o['mail_customer'])): ?><div class="msg err">
    <?= !$o['mail_shop'] ? 'The new-order email to you did not send. ' : '' ?><?= !$o['mail_customer'] ? 'The customer’s confirmation email did not send, so contact them directly.' : '' ?></div><?php endif; ?>
  <div class="grid2">
    <div class="card"><h2>Customer</h2><dl class="kv">
      <dt>Name</dt><dd><?= h($o['name']) ?></dd>
      <?php if($o['company']): ?><dt>Company</dt><dd><?= h($o['company']) ?></dd><?php endif; ?>
      <dt>Email</dt><dd><a href="mailto:<?= h($o['email']) ?>?subject=<?= rawurlencode('Your order '.$o['ref']) ?>"><?= h($o['email']) ?></a></dd>
      <dt>Phone</dt><dd><a href="tel:<?= h(preg_replace('/[^0-9+]/', '', $o['phone'])) ?>"><?= h($o['phone']) ?></a></dd>
      <dt>Ship to</dt><dd><?= nl2br(h(implode("\n", array_filter([$o['address1'], $o['address2'], trim($o['city'].', '.$o['region'].' '.$o['postcode'], ', '), $o['country_name']])))) ?></dd>
      <?php if($o['notes']): ?><dt>Their notes</dt><dd><?= nl2br(h($o['notes'])) ?></dd><?php endif; ?>
    </dl></div>
    <div class="card"><h2>Status</h2>
      <form method="post"><?= csrf_field() ?><input type="hidden" name="do" value="order_update"><input type="hidden" name="ref" value="<?= h($o['ref']) ?>">
        <div class="fld"><select name="status" aria-label="Status"><?php foreach(ORDER_STATUSES as $k=>$label): ?><option value="<?= h($k) ?>" <?= $st===$k?'selected':'' ?>><?= h($label) ?></option><?php endforeach; ?></select></div>
        <div class="fld"><label class="f" for="an">Private note</label><textarea id="an" name="admin_note" placeholder="Tracking number, what you sent, anything to remember"><?= h($o['admin_note'] ?? '') ?></textarea></div>
        <button class="btn" type="submit">Save</button>
      </form>
    </div>
  </div>
  <?php if(!empty($o['btc'])): $b = $o['btc']; $bst = btc_state($o); ?>
  <div class="card"><h2>Bitcoin payment <span class="pill <?= in_array($bst, ['confirmed'], true) ? 's-paid' : ($bst === 'short' ? 's-low' : ($bst === 'seen' ? 's-invoiced' : 's-new')) ?>"><?= h(btc_state_label($bst)) ?></span></h2>
    <dl class="kv">
      <dt>Amount due</dt><dd><?= !empty($b['sats']) ? h(btc_amount($b['expected_sats'] ?? $b['sats'])).' BTC <span class="muted small">at $'.number_format($b['rate'], 2).' ('.h($b['rate_source']).')'.(!empty($b['txid']) ? '' : ', held until '.h(gmdate('H:i', $b['expires'])).' UTC').'</span>' : '<span class="muted">not quoted yet (price feeds unavailable)</span>' ?></dd>
      <dt>To address</dt><dd><a href="<?= h(btc_addr_url($b['address'])) ?>" target="_blank" rel="noopener"><code><?= h($b['address']) ?></code></a></dd>
      <?php if(!empty($b['txid'])): ?>
        <dt>Paid</dt><dd><b><?= h(btc_amount($b['paid_sats'])) ?> BTC</b> · <?= (int)($b['confirmations'] ?? 0) ?> confirmation<?= ($b['confirmations'] ?? 0) == 1 ? '' : 's' ?></dd>
        <dt>Transaction</dt><dd><a href="<?= h(btc_tx_url($b['txid'])) ?>" target="_blank" rel="noopener"><code><?= h($b['txid']) ?></code> ↗</a></dd>
      <?php elseif(!empty($b['reported'])): ?>
        <dt>Customer says</dt><dd>paid with <a href="<?= h(btc_tx_url($b['reported'])) ?>" target="_blank" rel="noopener"><code><?= h($b['reported']) ?></code> ↗</a> (not checked yet)</dd>
      <?php endif; ?>
      <dt>Payment page</dt><dd><a href="<?= h(btc_pay_link($o)) ?>" target="_blank" rel="noopener">Customer’s payment page ↗</a> <span class="muted small">— the link from their email</span></dd>
      <?php if(!empty($b['mails'])): ?><dt>Receipts</dt><dd class="small"><?php foreach($b['mails'] as [$kind, $t, $ok1, $ok2]) echo h(ucfirst($kind)).' · '.h(gmdate('j M H:i', $t)).' UTC'.($ok1 && $ok2 ? '' : ' <span style="color:var(--seal)">(email failed)</span>').'<br>'; ?></dd><?php endif; ?>
    </dl>
    <div class="row" style="margin-top:12px">
      <form method="post" class="inline"><?= csrf_field() ?><input type="hidden" name="do" value="btc_check"><input type="hidden" name="ref" value="<?= h($o['ref']) ?>"><button class="btn s" type="submit">Check the blockchain now</button></form>
      <?php if(empty($b['txid'])): ?>
      <form method="post" class="row"><?= csrf_field() ?><input type="hidden" name="do" value="btc_attach"><input type="hidden" name="ref" value="<?= h($o['ref']) ?>">
        <input type="text" name="txid" placeholder="Transaction ID the customer sent you" style="width:340px;max-width:100%;font-family:ui-monospace,Menlo,monospace;font-size:13px" aria-label="Transaction ID"><button class="btn g s" type="submit">Attach</button></form>
      <?php endif; ?>
    </div>
  </div>
  <?php endif; ?>
  <div class="card"><h2>Items</h2><div class="scroll"><table class="t">
    <?php $ob = order_base($o); ?>
    <thead><tr><th>Product</th><th>SKU</th><th class="r">Qty</th><th class="r">Unit (<?= h($ob) ?>)</th><th class="r">Total (<?= h($ob) ?>)</th><th class="r">Customer saw</th></tr></thead><tbody>
    <?php foreach($o['lines'] as $l): ?>
      <tr><td><?= h($l['name']) ?></td><td class="muted"><?= h($l['sku']) ?></td><td class="r"><?= (int)$l['qty'] ?></td>
          <td class="r"><?= usd($l['unit_usd'], $ob) ?></td><td class="r"><?= usd($l['total_usd'], $ob) ?></td><td class="r muted"><?= h($l['total']) ?></td></tr>
    <?php endforeach; ?>
      <tr><td colspan="4" class="r">Goods</td><td class="r"><?= usd($o['goods_usd'], $ob) ?></td><td class="r muted"><?= h($o['goods']) ?></td></tr>
      <tr><td colspan="4" class="r">Shipping (<?= h($o['ship_zone']) ?><?= !empty($o['ship_label']) ? ' · '.h($o['ship_label']) : '' ?>)</td><td class="r"><?= usd($o['shipping_usd'], $ob) ?></td><td class="r muted"><?= h($o['shipping']) ?></td></tr>
      <tr><td colspan="4" class="r"><b>Order total</b></td><td class="r"><b><?= usd($o['total_usd'], $ob) ?></b></td><td class="r"><b><?= h($o['total']) ?></b></td></tr>
    </tbody></table></div></div>
  <form method="post" onsubmit="return confirm('Delete order <?= h($o['ref']) ?> permanently?')"><?= csrf_field() ?>
    <input type="hidden" name="do" value="order_delete"><input type="hidden" name="ref" value="<?= h($o['ref']) ?>">
    <button class="btn d s" type="submit">Delete order</button> <span class="muted small">Only for test or spam orders.</span></form>
  <?php endif; ?>

<?php elseif($v === 'products'): ?>
  <div class="row" style="justify-content:space-between"><div><h1>Products</h1><p class="sub">The shop lists products in this order. Hidden products stay here but don’t appear in the shop.</p></div>
    <a class="btn" href="<?= h(self_url('product')) ?>">+ Add a product</a></div>
  <div class="card scroll"><table class="t">
    <thead><tr><th></th><th>Product</th><th>Category</th><th>Status</th><th class="r">Price at MOQ</th><th class="r">MOQ</th><th></th></tr></thead><tbody>
    <?php foreach($STORE['products'] as $i=>$p): $ph = photos($p['id']);
      $moq_price = $p['ladder'][0][1]; foreach($p['ladder'] as $t){ if($p['moq'] >= $t[0]) $moq_price = $t[1]; } ?>
      <tr>
        <td><?php if($ph): ?><img class="thumb" src="<?= h($ph[0]) ?>?v=<?= @filemtime(FK_ROOT.'/'.$ph[0]) ?>" alt=""><?php else: ?><div class="thumb"></div><?php endif; ?></td>
        <td><a href="<?= h(self_url('product', ['id'=>$p['id']])) ?>"><b><?= h($p['name']) ?></b></a><br><span class="muted small"><?= h($p['sku']) ?></span>
            <?php if(!empty($p['hidden'])): ?> <span class="pill hid">Hidden</span><?php endif; ?></td>
        <td><?= h($STORE['categories'][$p['cat']]['label'] ?? '—') ?></td>
        <td><span class="pill s-<?= h($p['status']) ?>"><?= h(status_label(PRODUCT_STATUSES, $p['status'])) ?></span></td>
        <td class="r"><?= usd($moq_price) ?></td>
        <td class="r"><?= (int)$p['moq'] ?></td>
        <td class="r" style="white-space:nowrap">
          <?php foreach(['up'=>'↑','down'=>'↓'] as $dir=>$arrow): ?>
            <form method="post" class="inline"><?= csrf_field() ?><input type="hidden" name="do" value="product_move"><input type="hidden" name="id" value="<?= h($p['id']) ?>"><input type="hidden" name="dir" value="<?= $dir ?>">
              <button class="btn g s" type="submit" aria-label="Move <?= $dir ?>" <?= ($dir==='up' && $i===0) || ($dir==='down' && $i===count($STORE['products'])-1) ? 'disabled' : '' ?>><?= $arrow ?></button></form>
          <?php endforeach; ?>
          <a class="btn g s" href="<?= h(self_url('product', ['id'=>$p['id']])) ?>">Edit</a>
        </td>
      </tr>
    <?php endforeach; ?>
    </tbody></table>
    <?php if(!$STORE['products']): ?><p class="muted">No products yet.</p><?php endif; ?>
  </div>

<?php elseif($v === 'product'):
  $id = str($_GET['id'] ?? '');
  $idx = $id === '' ? null : product_index($id);
  $p = $form ?? ($idx !== null ? $STORE['products'][$idx] + ['old_id'=>$id] : null);
  $is_new = !$p || ($p['old_id'] ?? '') === '';
  $p = ($p ?? []) + ['old_id'=>'', 'id'=>'', 'sku'=>'', 'name'=>'', 'set'=>'', 'cat'=>array_key_first($STORE['categories']),
                     'moq'=>6, 'step'=>6, 'status'=>'in', 'cond'=>'Sealed', 'release'=>'', 'weight'=>0.4, 'hidden'=>false, 'ladder'=>[], 'desc'=>''];
  $ph = $p['old_id'] !== '' ? photos($p['old_id']) : []; ?>
  <p class="small"><a href="<?= h(self_url('products')) ?>">← Products</a></p>
  <h1><?= $is_new ? 'Add a product' : h($p['name']) ?></h1>
  <p class="sub"><?php if(!$is_new): ?><a href="index.php?<?= h(http_build_query(['p'=>'product','id'=>$p['old_id']])) ?>" target="_blank" rel="noopener">View in shop ↗</a><?php else: ?>Fill this in and save — the product appears in the shop straight away unless you tick “Hide”.<?php endif; ?></p>
  <form method="post" enctype="multipart/form-data"><?= csrf_field() ?>
    <input type="hidden" name="do" value="product_save"><input type="hidden" name="old_id" value="<?= h($p['old_id']) ?>">
    <div class="card"><h2>Details</h2>
      <div class="fld"><label class="f" for="name">Product name</label><input type="text" id="name" name="name" value="<?= h($p['name']) ?>" required></div>
      <div class="grid3">
        <div class="fld"><label class="f" for="sku">SKU</label><input type="text" id="sku" name="sku" value="<?= h($p['sku']) ?>"></div>
        <div class="fld"><label class="f" for="set">Set / expansion</label><input type="text" id="set" name="set" value="<?= h($p['set']) ?>"></div>
        <div class="fld"><label class="f" for="cat">Category</label><select id="cat" name="cat">
          <?php foreach($STORE['categories'] as $k=>$c): ?><option value="<?= h($k) ?>" <?= $p['cat']===$k?'selected':'' ?>><?= h($c['label']) ?></option><?php endforeach; ?></select></div>
      </div>
      <div class="grid3">
        <div class="fld"><label class="f" for="status">Status</label><select id="status" name="status">
          <?php foreach(PRODUCT_STATUSES as $k=>$label): ?><option value="<?= h($k) ?>" <?= $p['status']===$k?'selected':'' ?>><?= h($label) ?></option><?php endforeach; ?></select>
          <div class="hint">“Sold out” shows the product but stops orders.</div></div>
        <div class="fld"><label class="f" for="cond">Condition</label><select id="cond" name="cond">
          <?php foreach(CONDITIONS as $c): ?><option <?= ($p['cond'] ?? 'Sealed')===$c?'selected':'' ?>><?= h($c) ?></option><?php endforeach; ?></select></div>
      </div>
      <div class="grid3">
        <div class="fld"><label class="f" for="release">Release date (preorders)</label><input type="text" id="release" name="release" value="<?= h($p['release']) ?>" placeholder="e.g. November 2026"></div>
        <div class="fld"><label class="f" for="pid">Web address</label><input type="text" id="pid" name="id" value="<?= h($p['id']) ?>" placeholder="made from the name if blank">
          <div class="hint">Lowercase words and dashes. Changing it breaks old links to this product.</div></div>
      </div>
      <div class="fld"><label class="f" for="desc">Description</label><textarea id="desc" name="desc" rows="5"><?= h($p['desc']) ?></textarea>
        <div class="hint">What’s in the box, packs per box, release date, key cards. Longer descriptions help Google.</div></div>
      <label class="row small"><input type="checkbox" name="hidden" value="1" <?= !empty($p['hidden'])?'checked':'' ?>> Hide from the shop</label>
    </div>

    <div class="card"><h2>Ordering &amp; price</h2>
      <div class="grid3">
        <div class="fld"><label class="f" for="moq">Minimum quantity (MOQ)</label><input type="number" id="moq" name="moq" min="1" step="1" value="<?= (int)$p['moq'] ?>"></div>
        <div class="fld"><label class="f" for="step">Sold in multiples of</label><input type="number" id="step" name="step" min="1" step="1" value="<?= (int)$p['step'] ?>"></div>
        <div class="fld"><label class="f" for="weight">Weight per unit (kg)</label><input type="number" id="weight" name="weight" min="0" step="0.01" value="<?= h($p['weight']) ?>">
          <div class="hint">Used for shipping. Box ≈ 0.4, ETB ≈ 0.9, single ≈ 0.05.</div></div>
      </div>
      <label class="f">Quantity-break prices (<?= h(base_cur()) ?> per unit)</label>
      <p class="hint small muted" style="margin:0 0 6px">From this many units, each unit costs this much. The first row is the single-unit price shown crossed out. Leave spare rows empty.</p>
      <table class="ladder"><tr><th class="small muted" style="text-align:left">From qty</th><th class="small muted" style="text-align:left">Unit price $</th></tr>
        <?php for($i=0; $i<max(6, count($p['ladder'])+2); $i++): $t = $p['ladder'][$i] ?? ['','']; ?>
          <tr><td><input type="number" name="lq[<?= $i ?>]" min="1" step="1" value="<?= h($t[0]) ?>" aria-label="From quantity"></td>
              <td><input type="number" name="lp[<?= $i ?>]" min="0" step="0.01" value="<?= h($t[1]) ?>" aria-label="Unit price"></td></tr>
        <?php endfor; ?>
      </table>
    </div>

    <div class="card"><h2>Google</h2>
      <p class="small muted" style="margin-top:0">Leave blank to use the product name and the start of the description.</p>
      <?php seo_inputs('seo', $p, $p['name'] ?: 'e.g. 151 Booster Box (Japanese)', 'e.g. Sealed Japanese 151 booster box…'); ?>
    </div>

    <div class="card"><h2>Photos</h2>
      <?php if($ph): ?>
        <div class="photos"><?php foreach($ph as $i=>$src): ?>
          <figure><img src="<?= h($src) ?>?v=<?= @filemtime(FK_ROOT.'/'.$src) ?>" alt="">
            <label><input type="radio" name="main" value="<?= $i ?>" <?= $i===0?'checked':'' ?>> Main photo</label>
            <label><input type="checkbox" name="rm[]" value="<?= $i ?>"> Remove</label></figure>
        <?php endforeach; ?></div>
      <?php endif; ?>
      <div class="fld"><label class="f" for="photos">Add photos</label><input type="file" id="photos" name="photos[]" accept="image/jpeg,image/png,image/webp" multiple>
        <div class="hint">Up to 4 per product. JPG, PNG or WebP under 8 MB — square photos around 1200×1200 look best.</div></div>
    </div>

    <div class="row"><button class="btn" type="submit"><?= $is_new ? 'Add product' : 'Save changes' ?></button><a class="btn g" href="<?= h(self_url('products')) ?>">Cancel</a></div>
  </form>
  <?php if(!$is_new): ?>
  <div class="row" style="margin-top:26px">
    <form method="post" class="inline"><?= csrf_field() ?><input type="hidden" name="do" value="product_copy"><input type="hidden" name="id" value="<?= h($p['old_id']) ?>"><button class="btn g s" type="submit">Duplicate</button></form>
    <form method="post" class="inline" onsubmit="return confirm('Delete this product and its photos?')"><?= csrf_field() ?><input type="hidden" name="do" value="product_delete"><input type="hidden" name="id" value="<?= h($p['old_id']) ?>"><button class="btn d s" type="submit">Delete product</button></form>
  </div>
  <?php endif; ?>

<?php elseif($v === 'categories'):
  $used = array_count_values(array_column($STORE['products'], 'cat')); ?>
  <h1>Categories</h1>
  <p class="sub">Shown in the menu bar and on the home page. Each category has its own page — the heading, Google text and intro below help it rank. A category with products in it can’t be deleted.</p>
  <form method="post"><?= csrf_field() ?><input type="hidden" name="do" value="categories_save">
    <?php $i = 0; foreach($STORE['categories'] + ['' => ['label'=>'','blurb'=>'']] as $k=>$c): ?>
    <details class="card" <?= $k === '' ? '' : 'open' ?>>
      <summary><b><?= $k === '' ? '+ Add a category' : h($c['label']) ?></b><?php if($k !== ''): ?> <span class="muted small">· <?= (int)($used[$k] ?? 0) ?> products</span><?php endif; ?></summary>
      <?php if($k !== ''): ?><input type="hidden" name="cats[<?= $i ?>][key]" value="<?= h($k) ?>"><?php endif; ?>
      <div class="grid3" style="margin-top:12px">
        <div class="fld"><label class="f">Name (menu)</label><input type="text" name="cats[<?= $i ?>][label]" value="<?= h($c['label']) ?>"></div>
        <div class="fld"><label class="f">Web address</label><input type="text" name="cats[<?= $i ?>][slug]" value="<?= h($c['slug'] ?? '') ?>" placeholder="made from the name"></div>
        <div class="fld"><label class="f">Page heading (H1)</label><input type="text" name="cats[<?= $i ?>][h1]" value="<?= h($c['h1'] ?? '') ?>" placeholder="<?= h($c['label'] ?: 'the name') ?>"></div>
      </div>
      <div class="fld"><label class="f">Short description (home page and top of the category)</label><input type="text" name="cats[<?= $i ?>][blurb]" value="<?= h($c['blurb']) ?>"></div>
      <?php seo_inputs("cats[$i]", $c); ?>
      <div class="fld"><label class="f">Intro text (below the products)</label><textarea name="cats[<?= $i ?>][intro]" rows="6"><?= h($c['intro'] ?? '') ?></textarea>
        <div class="hint"><?= h(FORMAT_HELP) ?></div></div>
      <?php if($k !== ''): ?><label class="row small"><input type="checkbox" name="cats[<?= $i ?>][delete]" value="1" <?= !empty($used[$k])?'disabled':'' ?>> Delete this category</label><?php endif; ?>
    </details>
    <?php $i++; endforeach; ?>
    <button class="btn" type="submit">Save categories</button>
  </form>

<?php elseif($v === 'sets'):
  $counts = array_count_values(array_column($STORE['products'], 'set')); ?>
  <h1>Sets</h1>
  <p class="sub">Every set on your products gets its own page (e.g. /sets/151), grouped into series pages. New sets appear here automatically when you type them on a product.</p>
  <form method="post"><?= csrf_field() ?><input type="hidden" name="do" value="sets_save">
    <h2 style="font-size:18px;margin:6px 0 10px">Series</h2>
    <?php foreach($STORE['series'] as $k=>$sr): ?>
    <details class="card">
      <summary><b><?= h($sr['name']) ?></b> <span class="muted small">· /sets/<?= h($sr['slug']) ?></span></summary>
      <div class="grid3" style="margin-top:12px">
        <div class="fld"><label class="f">Name</label><input type="text" name="series[<?= h($k) ?>][name]" value="<?= h($sr['name']) ?>"></div>
        <div class="fld"><label class="f">Web address</label><input type="text" name="series[<?= h($k) ?>][slug]" value="<?= h($sr['slug']) ?>"></div>
        <div class="fld"><label class="f">Page heading (H1)</label><input type="text" name="series[<?= h($k) ?>][h1]" value="<?= h($sr['h1'] ?? '') ?>"></div>
      </div>
      <?php seo_inputs("series[$k]", $sr); ?>
      <div class="fld"><label class="f">Intro text</label><textarea name="series[<?= h($k) ?>][intro]" rows="6"><?= h($sr['intro'] ?? '') ?></textarea><div class="hint"><?= h(FORMAT_HELP) ?></div></div>
    </details>
    <?php endforeach; ?>
    <h2 style="font-size:18px;margin:22px 0 10px">Sets</h2>
    <?php foreach(set_names() as $i=>$name): $st = ($STORE['sets'][$name] ?? []) + ['slug'=>slugify($name), 'series'=>'', 'code'=>'', 'intro'=>'']; ?>
    <details class="card">
      <summary><b><?= h($name) ?></b> <span class="muted small">· <?= (int)($counts[$name] ?? 0) ?> products<?= $st['code'] !== '' ? ' · '.h($st['code']) : '' ?></span></summary>
      <input type="hidden" name="sets[<?= $i ?>][name]" value="<?= h($name) ?>">
      <div class="grid3" style="margin-top:12px">
        <div class="fld"><label class="f">Web address</label><input type="text" name="sets[<?= $i ?>][slug]" value="<?= h($st['slug']) ?>"></div>
        <div class="fld"><label class="f">Series</label><select name="sets[<?= $i ?>][series]"><option value="">—</option>
          <?php foreach($STORE['series'] as $k=>$sr): ?><option value="<?= h($k) ?>" <?= $st['series']===$k?'selected':'' ?>><?= h($sr['name']) ?></option><?php endforeach; ?></select></div>
        <div class="fld"><label class="f">Set code</label><input type="text" name="sets[<?= $i ?>][code]" value="<?= h($st['code']) ?>" placeholder="e.g. SV2a"></div>
      </div>
      <?php seo_inputs("sets[$i]", $st, $name.' Japanese Booster Boxes & Cards'); ?>
      <div class="fld"><label class="f">Intro text</label><textarea name="sets[<?= $i ?>][intro]" rows="4"><?= h($st['intro']) ?></textarea><div class="hint"><?= h(FORMAT_HELP) ?></div></div>
    </details>
    <?php endforeach; ?>
    <button class="btn" type="submit">Save sets</button>
  </form>

<?php elseif($v === 'collections'): ?>
  <div class="row" style="justify-content:space-between"><div><h1>Collections</h1>
    <p class="sub">Pages that gather products around a search people make, like “Charizard Pokémon cards”. Each has its own address (e.g. /cards/charizard-pokemon-cards).</p></div>
    <a class="btn" href="<?= h(self_url('collection', ['i'=>'new'])) ?>">+ Add a collection</a></div>
  <div class="card scroll"><table class="t"><thead><tr><th>Title</th><th>Address</th><th class="r">Products</th><th></th></tr></thead><tbody>
    <?php foreach($STORE['collections'] as $i=>$c): ?>
      <tr><td><a href="<?= h(self_url('collection', ['i'=>$i])) ?>"><b><?= h($c['title']) ?></b></a></td><td class="muted small">/cards/<?= h($c['slug']) ?></td>
          <td class="r"><?= count(coll_products($c)) ?></td><td class="r"><a class="btn g s" href="<?= h(self_url('collection', ['i'=>$i])) ?>">Edit</a></td></tr>
    <?php endforeach; ?>
  </tbody></table><?php if(!$STORE['collections']): ?><p class="muted">No collections yet.</p><?php endif; ?></div>

<?php elseif($v === 'collection'):
  $ci = str($_GET['i'] ?? 'new');
  $c = $form ?? ($STORE['collections'][(int)$ci] ?? null);
  $idx = $form ? $form['i'] : (isset($STORE['collections'][(int)$ci]) && $ci !== 'new' ? (int)$ci : -1);
  if($idx === -1 && !$form) $c = null;
  $c = ($c ?? []) + ['title'=>'','slug'=>'','h1'=>'','match'=>'','ids'=>[],'cond'=>'','intro'=>'','seo_title'=>'','seo_desc'=>'']; ?>
  <p class="small"><a href="<?= h(self_url('collections')) ?>">← Collections</a></p>
  <h1><?= $idx === -1 ? 'Add a collection' : h($c['title']) ?></h1>
  <?php if($idx !== -1): ?><p class="sub"><a href="index.php?<?= h(http_build_query(['p'=>'collection','c'=>$c['slug']])) ?>" target="_blank" rel="noopener">View in shop ↗</a> · <?= count(coll_products($c)) ?> products</p><?php endif; ?>
  <form method="post"><?= csrf_field() ?><input type="hidden" name="do" value="collection_save"><input type="hidden" name="i" value="<?= (int)$idx ?>">
    <div class="card">
      <div class="grid3">
        <div class="fld"><label class="f">Title</label><input type="text" name="title" value="<?= h($c['title']) ?>" required placeholder="e.g. Charizard Pokémon cards"></div>
        <div class="fld"><label class="f">Web address</label><input type="text" name="slug" value="<?= h($c['slug']) ?>" placeholder="made from the title"></div>
        <div class="fld"><label class="f">Page heading (H1)</label><input type="text" name="h1" value="<?= h($c['h1']) ?>" placeholder="the title"></div>
      </div>
      <h2>Which products</h2>
      <div class="grid2">
        <div class="fld"><label class="f">Names containing</label><input type="text" name="match" value="<?= h($c['match']) ?>" placeholder="e.g. charizard (separate several with commas)">
          <div class="hint">Any product whose name contains one of these words — new ones join automatically.</div></div>
        <div class="fld"><label class="f">Only this condition</label><select name="cond"><option value="">Any</option>
          <?php foreach(CONDITIONS as $cn): ?><option <?= $c['cond']===$cn?'selected':'' ?>><?= h($cn) ?></option><?php endforeach; ?></select>
          <div class="hint">On its own (no words), lists every product in that condition.</div></div>
      </div>
      <div class="fld"><label class="f">Always include these products</label>
        <select name="ids[]" multiple size="8"><?php foreach($STORE['products'] as $p): ?><option value="<?= h($p['id']) ?>" <?= in_array($p['id'], $c['ids'], true)?'selected':'' ?>><?= h($p['name']) ?></option><?php endforeach; ?></select>
        <div class="hint">Hold Ctrl (Cmd on Mac) to pick several; on a phone, tap to tick.</div></div>
      <div class="fld"><label class="f">Intro text</label><textarea name="intro" rows="6"><?= h($c['intro']) ?></textarea><div class="hint"><?= h(FORMAT_HELP) ?></div></div>
      <?php seo_inputs('seo', $c); ?>
    </div>
    <button class="btn" type="submit"><?= $idx === -1 ? 'Add collection' : 'Save changes' ?></button>
  </form>
  <?php if($idx !== -1): ?>
  <form method="post" style="margin-top:22px" onsubmit="return confirm('Delete this collection page?')"><?= csrf_field() ?><input type="hidden" name="do" value="collection_delete"><input type="hidden" name="i" value="<?= (int)$idx ?>"><button class="btn d s" type="submit">Delete collection</button></form>
  <?php endif; ?>

<?php elseif($v === 'guides'): ?>
  <div class="row" style="justify-content:space-between"><div><h1>Guides</h1>
    <p class="sub">Articles that answer what people search for — they bring visitors in and link them to your products.</p></div>
    <a class="btn" href="<?= h(self_url('guide', ['i'=>'new'])) ?>">+ Write a guide</a></div>
  <div class="card scroll"><table class="t"><thead><tr><th>Title</th><th>Address</th><th>Updated</th><th></th></tr></thead><tbody>
    <?php foreach($STORE['guides'] as $i=>$g): ?>
      <tr><td><a href="<?= h(self_url('guide', ['i'=>$i])) ?>"><b><?= h($g['title']) ?></b></a></td><td class="muted small">/guides/<?= h($g['slug']) ?></td>
          <td class="small"><?= h($g['updated'] ?? '') ?></td><td class="r"><a class="btn g s" href="<?= h(self_url('guide', ['i'=>$i])) ?>">Edit</a></td></tr>
    <?php endforeach; ?>
  </tbody></table><?php if(!$STORE['guides']): ?><p class="muted">No guides yet.</p><?php endif; ?></div>

<?php elseif($v === 'guide'):
  $gi = str($_GET['i'] ?? 'new');
  $g = $form ?? ($gi !== 'new' ? ($STORE['guides'][(int)$gi] ?? null) : null);
  $idx = $form ? $form['i'] : ($g ? (int)$gi : -1);
  $g = ($g ?? []) + ['title'=>'','slug'=>'','body'=>'','seo_title'=>'','seo_desc'=>'']; ?>
  <p class="small"><a href="<?= h(self_url('guides')) ?>">← Guides</a></p>
  <h1><?= $idx === -1 ? 'Write a guide' : h($g['title']) ?></h1>
  <?php if($idx !== -1): ?><p class="sub"><a href="index.php?<?= h(http_build_query(['p'=>'guide','g'=>$g['slug']])) ?>" target="_blank" rel="noopener">View in shop ↗</a></p><?php endif; ?>
  <form method="post"><?= csrf_field() ?><input type="hidden" name="do" value="guide_save"><input type="hidden" name="i" value="<?= (int)$idx ?>">
    <div class="card">
      <div class="grid2">
        <div class="fld"><label class="f">Title (page heading)</label><input type="text" name="title" value="<?= h($g['title']) ?>" required></div>
        <div class="fld"><label class="f">Web address</label><input type="text" name="slug" value="<?= h($g['slug']) ?>" placeholder="made from the title"></div>
      </div>
      <div class="fld"><label class="f">Text</label><textarea name="body" rows="22" style="font-family:ui-monospace,Menlo,Consolas,monospace;font-size:13.5px"><?= h($g['body']) ?></textarea>
        <div class="hint"><?= h(FORMAT_HELP) ?></div></div>
      <?php seo_inputs('seo', $g, $g['title']); ?>
    </div>
    <button class="btn" type="submit"><?= $idx === -1 ? 'Publish guide' : 'Save changes' ?></button>
  </form>
  <?php if($idx !== -1): ?>
  <form method="post" style="margin-top:22px" onsubmit="return confirm('Delete this guide?')"><?= csrf_field() ?><input type="hidden" name="do" value="guide_delete"><input type="hidden" name="i" value="<?= (int)$idx ?>"><button class="btn d s" type="submit">Delete guide</button></form>
  <?php endif; ?>

<?php elseif($v === 'pages'): ?>
  <div class="row" style="justify-content:space-between"><div><h1>Pages</h1>
    <p class="sub">About, returns, privacy and terms. Google looks for these to judge whether a shop is trustworthy; they’re linked in the footer. Review the policies for your own business.</p></div>
    <a class="btn" href="<?= h(self_url('page', ['i'=>'new'])) ?>">+ Add a page</a></div>
  <div class="card scroll"><table class="t"><thead><tr><th>Title</th><th>Address</th><th></th></tr></thead><tbody>
    <?php foreach($STORE['pages'] as $i=>$g): ?>
      <tr><td><a href="<?= h(self_url('page', ['i'=>$i])) ?>"><b><?= h($g['title']) ?></b></a></td><td class="muted small">/<?= h($g['slug']) ?></td>
          <td class="r"><a class="btn g s" href="<?= h(self_url('page', ['i'=>$i])) ?>">Edit</a></td></tr>
    <?php endforeach; ?>
  </tbody></table><?php if(!$STORE['pages']): ?><p class="muted">No pages yet.</p><?php endif; ?></div>

<?php elseif($v === 'page'):
  $gi = str($_GET['i'] ?? 'new');
  $g = $form ?? ($gi !== 'new' ? ($STORE['pages'][(int)$gi] ?? null) : null);
  $idx = $form ? $form['i'] : ($g ? (int)$gi : -1);
  $g = ($g ?? []) + ['title'=>'','slug'=>'','body'=>'','seo_title'=>'','seo_desc'=>'']; ?>
  <p class="small"><a href="<?= h(self_url('pages')) ?>">← Pages</a></p>
  <h1><?= $idx === -1 ? 'Add a page' : h($g['title']) ?></h1>
  <?php if($idx !== -1): ?><p class="sub"><a href="index.php?<?= h(http_build_query(['p'=>'page','pg'=>$g['slug']])) ?>" target="_blank" rel="noopener">View in shop ↗</a></p><?php endif; ?>
  <form method="post"><?= csrf_field() ?><input type="hidden" name="do" value="page_save"><input type="hidden" name="i" value="<?= (int)$idx ?>">
    <div class="card">
      <div class="grid2">
        <div class="fld"><label class="f">Title (page heading)</label><input type="text" name="title" value="<?= h($g['title']) ?>" required></div>
        <div class="fld"><label class="f">Web address</label><input type="text" name="slug" value="<?= h($g['slug']) ?>" placeholder="made from the title"></div>
      </div>
      <div class="fld"><label class="f">Text</label><textarea name="body" rows="22" style="font-family:ui-monospace,Menlo,Consolas,monospace;font-size:13.5px"><?= h($g['body']) ?></textarea>
        <div class="hint"><?= h(FORMAT_HELP) ?></div></div>
      <?php seo_inputs('seo', $g, $g['title']); ?>
    </div>
    <button class="btn" type="submit"><?= $idx === -1 ? 'Add page' : 'Save changes' ?></button>
  </form>
  <?php if($idx !== -1): ?>
  <form method="post" style="margin-top:22px" onsubmit="return confirm('Delete this page?')"><?= csrf_field() ?><input type="hidden" name="do" value="page_delete"><input type="hidden" name="i" value="<?= (int)$idx ?>"><button class="btn d s" type="submit">Delete page</button></form>
  <?php endif; ?>

<?php elseif($v === 'shipping'):
  $sh = $STORE['shipping']; $SM = ship_methods($STORE);
  $ex = fn($c, $kg, $m)=>usd(shipping_cost($STORE, $c, $kg, $m)); ?>
  <h1>Shipping rates</h1>
  <p class="sub">Customers choose <b><?= h($SM['standard']['label']) ?></b> or <b><?= h($SM['express']['label']) ?></b> at checkout. Each costs the zone’s <b>per-order</b> price + its <b>per-kg</b> price × the order’s weight (each product’s weight × quantity), in <?= h(base_cur()) ?>.
    The <?= usd($S['min_order_usd']) ?> minimum order counts goods plus the shipping chosen.</p>
  <?php if(empty($S['shipping_reviewed'])): ?><div class="msg warn">These are starting rates: a single card to the UK comes to £9 Standard and £18 Express, then more per kg. Check them against what your carrier actually charges from Japan, then save.</div><?php endif; ?>
  <form method="post"><?= csrf_field() ?><input type="hidden" name="do" value="shipping_save">
    <div class="card"><h2>Delivery options</h2>
      <div class="grid2">
        <?php foreach($SM as $m=>$mm): ?>
        <div class="grid2" style="gap:10px">
          <div class="fld"><label class="f">Name</label><input type="text" name="methods[<?= h($m) ?>][label]" value="<?= h($mm['label']) ?>"></div>
          <div class="fld"><label class="f">Delivery time</label><input type="text" name="methods[<?= h($m) ?>][days]" value="<?= h($mm['days']) ?>"></div>
        </div>
        <?php endforeach; ?>
      </div>
      <label class="row small"><input type="checkbox" name="round_up" value="1" <?= !empty($sh['round_up'])?'checked':'' ?>> Round shipping totals up to the next whole <?= base_cur() === 'GBP' ? 'pound' : (base_cur() === 'USD' ? 'dollar' : 'unit') ?></label>
    </div>
    <div class="card"><h2>Free shipping</h2>
      <div class="fld" style="max-width:320px"><label class="f" for="free_ship_usd">Free <?= h($SM['standard']['label']) ?> shipping on orders over (<?= h(base_cur()) ?>)</label>
        <input type="number" id="free_ship_usd" name="free_ship_usd" min="0" step="1" value="<?= h($S['free_ship_usd'] ?? 0) ?>">
        <div class="hint">Counts the goods total, before shipping. <?= h($SM['express']['label']) ?> then costs only the difference. 0 turns it off. It shows in the bar at the top of every page.</div></div>
    </div>
    <div class="card scroll"><table class="t">
      <thead><tr><th>Zone name</th><th>Country codes</th>
        <?php foreach($SM as $mm): ?><th><?= h($mm['label']) ?><br>per order $</th><th><?= h($mm['label']) ?><br>per kg $</th><?php endforeach; ?><th>Delete</th></tr></thead><tbody>
      <?php $zones = $sh['zones']; for($i=0; $i<count($zones)+2; $i++): $z = $zones[$i] ?? ['name'=>'','countries'=>[]];
        $zr = isset($zones[$i]) ? zone_rates($z) : ['standard'=>['base'=>'','per_kg'=>''], 'express'=>['base'=>'','per_kg'=>'']]; ?>
        <tr><td><input type="text" name="zones[<?= $i ?>][name]" value="<?= h($z['name']) ?>" placeholder="<?= isset($zones[$i])?'':'New zone' ?>" aria-label="Zone name"></td>
            <td><input type="text" name="zones[<?= $i ?>][countries]" value="<?= h(implode(', ', $z['countries'])) ?>" placeholder="e.g. US, CA" aria-label="Country codes" style="min-width:170px"></td>
            <?php foreach($SM as $m=>$mm): ?>
            <td><input type="number" name="zones[<?= $i ?>][<?= $m ?>][base]" value="<?= h($zr[$m]['base']) ?>" min="0" step="0.01" aria-label="<?= h($mm['label']) ?> per order"></td>
            <td><input type="number" name="zones[<?= $i ?>][<?= $m ?>][per_kg]" value="<?= h($zr[$m]['per_kg']) ?>" min="0" step="0.01" aria-label="<?= h($mm['label']) ?> per kg"></td>
            <?php endforeach; ?>
            <td><?php if(isset($zones[$i])): ?><input type="checkbox" name="zones[<?= $i ?>][delete]" value="1" aria-label="Delete"><?php endif; ?></td></tr>
      <?php endfor; $rr = zone_rates($sh['rest']); ?>
        <tr><td><b>Rest of world</b></td><td class="muted small">every country not in a zone above</td>
            <?php foreach($SM as $m=>$mm): ?>
            <td><input type="number" name="rest[<?= $m ?>][base]" value="<?= h($rr[$m]['base']) ?>" min="0" step="0.01" aria-label="Rest of world <?= h($mm['label']) ?> per order"></td>
            <td><input type="number" name="rest[<?= $m ?>][per_kg]" value="<?= h($rr[$m]['per_kg']) ?>" min="0" step="0.01" aria-label="Rest of world <?= h($mm['label']) ?> per kg"></td>
            <?php endforeach; ?><td></td></tr>
      </tbody></table>
      <p class="small muted">Country codes are the two-letter codes from your country list in <a href="<?= h(self_url('settings')) ?>">Settings</a> (GB, IE, DE…), separated by commas.</p>
    </div>
    <div class="card"><h2>What customers pay to the UK (current rates, before free shipping)</h2>
      <table class="t"><thead><tr><th>Order</th><th class="r">Weight</th><?php foreach($SM as $mm): ?><th class="r"><?= h($mm['label']) ?></th><?php endforeach; ?></tr></thead><tbody>
        <?php foreach([['1 single card', 0.05], ['24 packs of sleeves', 1.44], ['6 booster boxes', 2.4], ['6 Elite Trainer Boxes', 5.4], ['12-box case', 5.6], ['36 booster boxes', 14.4]] as [$lbl, $kg]): ?>
          <tr><td><?= h($lbl) ?></td><td class="r"><?= h($kg) ?> kg</td><?php foreach(array_keys($SM) as $m): ?><td class="r"><?= $ex('GB', $kg, $m) ?></td><?php endforeach; ?></tr>
        <?php endforeach; ?>
      </tbody></table>
    </div>
    <div class="card"><h2>Shipping &amp; Returns page</h2>
      <p class="small muted" style="margin-top:0">The text of your <a href="index.php?p=shipping" target="_blank" rel="noopener">Shipping &amp; Returns page</a>. Keep the line <b>{rates}</b> where the delivery options and rate tables should appear.
        Numbered steps start with “1. ”, and questions under “## Questions” start with “### ” (Google reads those as FAQs).
        {free_ship} {standard} {express} {standard_days} {express_days} {hold_hours} {reply_hours} {min_order} {email} {company} {address} are filled in for you.</p>
      <textarea name="shipping_policy" rows="26" style="font-family:ui-monospace,Menlo,Consolas,monospace;font-size:13px"><?= h($S['shipping_policy'] ?? '') ?></textarea>
      <div class="hint small muted"><?= h(FORMAT_HELP) ?></div>
    </div>
    <button class="btn" type="submit">Save shipping</button>
  </form>

<?php elseif($v === 'payments'): ?>
  <h1>Payment methods</h1>
  <p class="sub">Customers pick one at checkout. <b>Bitcoin</b> methods are paid on the site, straight to your wallet; for the others, you send the customer the details. Leave “Countries” as * for everywhere, or list country codes (e.g. US, GB).</p>
  <form method="post"><?= csrf_field() ?><input type="hidden" name="do" value="payments_save">
    <div class="card"><h2>Bitcoin wallet</h2>
      <?php $ba = $S['btc_address'] ?? ''; ?>
      <div class="fld"><label class="f" for="btc_address">Your Bitcoin address (receives every Bitcoin payment)</label>
        <input type="text" id="btc_address" name="btc_address" value="<?= h($ba) ?>" spellcheck="false" autocomplete="off" style="font-family:ui-monospace,Menlo,Consolas,monospace">
        <div class="hint"><?php if($ba !== '' && btc_valid_address($ba)): ?>✓ Valid address. <a href="<?= h(btc_addr_url(btc_norm($ba))) ?>" target="_blank" rel="noopener">See its payments on mempool.space ↗</a><?php else: ?>Paste a receiving address from your wallet (bc1…, 1… or 3…). It’s checked before saving, so a typo can’t get through.<?php endif; ?>
          Only the address is ever stored here, never your wallet’s keys or recovery words.</div></div>
      <div class="grid2">
        <div class="fld"><label class="f" for="btc_quote_minutes">Hold the BTC amount for (minutes)</label><input type="number" id="btc_quote_minutes" name="btc_quote_minutes" min="10" max="1440" value="<?= (int)($S['btc_quote_minutes'] ?? 60) ?>"><div class="hint">Then it’s renewed at the current price if still unpaid.</div></div>
        <div class="fld"><label class="f" for="btc_confirmations">Confirmations before an order is marked Paid</label><input type="number" id="btc_confirmations" name="btc_confirmations" min="1" max="6" value="<?= (int)($S['btc_confirmations'] ?? 1) ?>"><div class="hint">1 is usually enough for orders like yours; use 2–3 for very large ones.</div></div>
      </div>
      <p class="small muted" style="margin:0">When a payment shows up, you and the customer get a receipt email with a link to follow the transaction, and another when it confirms. Prices come from mempool.space, Coinbase or Kraken.</p>
    </div>
    <div class="card scroll"><table class="t">
      <thead><tr><th>Name</th><th>Type</th><th>Note shown to customers</th><th>Countries</th><th>On</th><th>Delete</th></tr></thead><tbody>
      <?php $i = 0; foreach($STORE['payments'] as $k=>$m): ?>
        <tr><td><input type="hidden" name="pays[<?= $i ?>][key]" value="<?= h($k) ?>"><input type="text" name="pays[<?= $i ?>][label]" value="<?= h($m['label']) ?>" aria-label="Name"></td>
            <td><select name="pays[<?= $i ?>][type]" aria-label="Type"><option value="">You send details</option><option value="bitcoin" <?= btc_method($m)?'selected':'' ?>>Bitcoin, paid on the site</option></select></td>
            <td><input type="text" name="pays[<?= $i ?>][note]" value="<?= h($m['note']) ?>" aria-label="Note" style="min-width:240px"></td>
            <td><input type="text" name="pays[<?= $i ?>][countries]" value="<?= h($m['countries']==='*' ? '*' : implode(', ', $m['countries'])) ?>" aria-label="Countries"></td>
            <td><input type="checkbox" name="pays[<?= $i ?>][enabled]" value="1" <?= !empty($m['enabled'])?'checked':'' ?> aria-label="Enabled"></td>
            <td><input type="checkbox" name="pays[<?= $i ?>][delete]" value="1" aria-label="Delete"></td></tr>
      <?php $i++; endforeach; ?>
        <tr><td><input type="text" name="pays[<?= $i ?>][label]" placeholder="New method" aria-label="Name"></td>
            <td><select name="pays[<?= $i ?>][type]" aria-label="Type"><option value="">You send details</option><option value="bitcoin">Bitcoin, paid on the site</option></select></td>
            <td><input type="text" name="pays[<?= $i ?>][note]" aria-label="Note"></td>
            <td><input type="text" name="pays[<?= $i ?>][countries]" value="*" aria-label="Countries"></td>
            <td><input type="checkbox" name="pays[<?= $i ?>][enabled]" value="1" checked aria-label="Enabled"></td><td></td></tr>
      </tbody></table></div>
    <button class="btn" type="submit">Save payment methods</button>
  </form>

<?php elseif($v === 'settings'):
  $hero = photos('hero'); ?>
  <h1>Settings</h1>
  <p class="sub">Business details, store rules and the text on the home page.</p>
  <form method="post" enctype="multipart/form-data"><?= csrf_field() ?><input type="hidden" name="do" value="settings_save">
    <div class="card"><h2>Business</h2>
      <div class="grid3">
        <div class="fld"><label class="f" for="brand">Shop name</label><input type="text" id="brand" name="brand" value="<?= h($S['brand']) ?>"></div>
        <div class="fld"><label class="f" for="kanji">Kanji mark</label><input type="text" id="kanji" name="kanji" value="<?= h($S['kanji']) ?>"></div>
        <div class="fld"><label class="f" for="tagline">Tagline</label><input type="text" id="tagline" name="tagline" value="<?= h($S['tagline']) ?>"></div>
      </div>
      <div class="grid2">
        <div class="fld"><label class="f" for="legal_name">Registered company name</label><input type="text" id="legal_name" name="legal_name" value="<?= h($S['legal_name']) ?>"></div>
        <div class="fld"><label class="f" for="address">Business address</label><input type="text" id="address" name="address" value="<?= h($S['address']) ?>"></div>
      </div>
      <div class="grid2">
        <div class="fld"><label class="f" for="company_number">Company number (optional)</label><input type="text" id="company_number" name="company_number" value="<?= h($S['company_number'] ?? '') ?>" placeholder="e.g. 12345678">
          <div class="hint">From Companies House. Shown in the footer and on the Contact page.</div></div>
        <div class="fld"><label class="f" for="company_registered">Registered in</label><input type="text" id="company_registered" name="company_registered" value="<?= h($S['company_registered'] ?? '') ?>" placeholder="England and Wales"></div>
      </div>
      <div class="grid3">
        <div class="fld"><label class="f" for="email">Public email</label><input type="email" id="email" name="email" value="<?= h($S['email']) ?>"></div>
        <div class="fld"><label class="f" for="order_email">Send new orders to</label><input type="email" id="order_email" name="order_email" value="<?= h($S['order_email']) ?>"></div>
        <div class="fld"><label class="f" for="phone">Phone (optional)</label><input type="text" id="phone" name="phone" value="<?= h($S['phone']) ?>"></div>
      </div>
      <div class="fld"><label class="f" for="domain">Website address</label><input type="url" id="domain" name="domain" value="<?= h($S['domain']) ?>"><div class="hint">Used for Google and link previews, e.g. https://fudakura.co.uk</div></div>
    </div>

    <div class="card"><h2>Store rules</h2>
      <div class="grid3">
        <div class="fld"><label class="f" for="min_order_usd">Minimum order (<?= h(base_cur()) ?>, incl. shipping)</label><input type="number" id="min_order_usd" name="min_order_usd" min="0" step="0.01" value="<?= h($S['min_order_usd']) ?>"></div>
        <div class="fld"><label class="f" for="reply_hours">Hours to send payment details</label><input type="number" id="reply_hours" name="reply_hours" min="1" value="<?= (int)$S['reply_hours'] ?>"></div>
        <div class="fld"><label class="f" for="hold_hours">Hours stock is held / to dispatch</label><input type="number" id="hold_hours" name="hold_hours" min="1" value="<?= (int)$S['hold_hours'] ?>"></div>
      </div>
    </div>

    <div class="card"><h2>Google &amp; web addresses</h2>
      <?php seo_inputs('home', ['seo_title'=>$S['home_seo_title'] ?? '', 'seo_desc'=>$S['home_seo_desc'] ?? ''], 'Japanese Pokémon Cards — Booster Boxes & Singles'); ?>
      <div class="fld"><label class="f" for="home_intro">Home page text (shown near the bottom of the home page)</label>
        <textarea id="home_intro" name="home_intro" rows="10"><?= h($S['home_intro'] ?? '') ?></textarea>
        <div class="hint"><?= h(FORMAT_HELP) ?></div></div>
      <div class="grid2">
        <div class="fld"><label class="f" for="google_verify">Google Search Console verification</label><input type="text" id="google_verify" name="google_verify" value="<?= h($S['google_verify'] ?? '') ?>" placeholder="Paste the HTML tag or its code">
          <div class="hint">In Search Console: Add property → URL prefix → HTML tag. Paste it here, save, then press Verify there.</div></div>
        <div class="fld"><label class="f" for="bing_verify">Bing Webmaster Tools verification (optional)</label><input type="text" id="bing_verify" name="bing_verify" value="<?= h($S['bing_verify'] ?? '') ?>" placeholder="Paste the meta tag or its code">
          <div class="hint">Or skip this and import your site from Search Console in Bing.</div></div>
      </div>
      <p class="small muted" style="margin-top:0">Your sitemap to submit: <b><?= h(rtrim($S['domain'], '/')) ?>/<?= clean_urls($S) ? 'sitemap.xml' : 'index.php?p=sitemap' ?></b></p>
      <?php $pu = $S['pretty_urls'] ?? 'auto'; $pu = $pu === 'auto' ? 'auto' : (!empty($pu) ? 'on' : 'off'); ?>
      <div class="fld" style="max-width:520px"><label class="f" for="pretty_urls">Clean page addresses, like /products/151-booster-box</label>
        <select id="pretty_urls" name="pretty_urls">
          <option value="auto" <?= $pu === 'auto' ? 'selected' : '' ?>>Automatic — on when your host supports them (recommended)</option>
          <option value="on" <?= $pu === 'on' ? 'selected' : '' ?>>Always on</option>
          <option value="off" <?= $pu === 'off' ? 'selected' : '' ?>>Off (index.php?p=… addresses)</option>
        </select>
        <div class="hint">Right now they are <b><?= clean_urls($S) ? 'on' : 'off' ?></b>. Automatic uses them when the shop’s .htaccess rules run (Apache and LiteSpeed hosts), and old index.php?p=… links then redirect to the clean ones.</div></div>
    </div>

    <div class="card"><h2>Home page &amp; announcement bar</h2>
      <div class="fld"><label class="f" for="strip_text">Announcement bar text</label><input type="text" id="strip_text" name="strip_text" value="<?= h($S['strip_text']) ?>"></div>
      <div class="grid2">
        <div class="fld"><label class="f" for="strip_link_text">Announcement link text (optional)</label><input type="text" id="strip_link_text" name="strip_link_text" value="<?= h($S['strip_link_text']) ?>"></div>
        <div class="fld"><label class="f" for="strip_link_url">Announcement link goes to</label><input type="text" id="strip_link_url" name="strip_link_url" value="<?= h($S['strip_link_url']) ?>"><div class="hint">A shop page like product:aura-seeker-booster-box, set:151 or guide:new-pokemon-sets, or any page address.</div></div>
      </div>
      <div class="fld"><label class="f" for="hero_title">Headline</label><input type="text" id="hero_title" name="hero_title" value="<?= h($S['hero_title']) ?>"></div>
      <div class="fld"><label class="f" for="hero_lede">Intro paragraph</label><textarea id="hero_lede" name="hero_lede"><?= h($S['hero_lede']) ?></textarea></div>
      <div class="fld"><label class="f" for="footer_blurb">Footer text</label><textarea id="footer_blurb" name="footer_blurb"><?= h($S['footer_blurb']) ?></textarea></div>
      <label class="f">Home page photo</label>
      <?php if($hero): ?><div class="photos"><figure><img src="<?= h($hero[0]) ?>?v=<?= @filemtime(FK_ROOT.'/'.$hero[0]) ?>" alt=""><label><input type="checkbox" name="hero_remove" value="1"> Remove</label></figure></div><?php endif; ?>
      <input type="file" name="hero[]" accept="image/jpeg,image/png,image/webp"><div class="hint small muted">Landscape, about 1600×1200. Until you upload one, the home page shows the 30th Celebration Elite Trainer Box.</div>
    </div>

    <div class="card" id="email-settings"><h2>Email</h2>
      <p class="small muted" style="margin-top:0">New orders go to <b><?= h($S['order_email']) ?></b> and a confirmation to the customer the moment an order is placed, from <b><?= h($S['email']) ?></b>.
        By default the shop sends through your web host. For the best delivery, enter your mailbox’s SMTP details (your email provider lists them, often as “IMAP/SMTP settings”).</p>
      <div class="grid3">
        <div class="fld"><label class="f" for="smtp_host">SMTP server</label><input type="text" id="smtp_host" name="smtp_host" value="<?= h($S['smtp_host'] ?? '') ?>" placeholder="e.g. smtp.hostinger.com — blank = web host mail"></div>
        <div class="fld"><label class="f" for="smtp_secure">Security</label><select id="smtp_secure" name="smtp_secure">
          <?php foreach(['ssl'=>'SSL (usually port 465)', 'tls'=>'STARTTLS (usually port 587)', 'none'=>'None (not recommended)'] as $k=>$lbl): ?><option value="<?= $k ?>" <?= ($S['smtp_secure'] ?? 'ssl')===$k?'selected':'' ?>><?= h($lbl) ?></option><?php endforeach; ?></select></div>
        <div class="fld"><label class="f" for="smtp_port">Port</label><input type="number" id="smtp_port" name="smtp_port" min="1" max="65535" value="<?= (int)($S['smtp_port'] ?? 0) ?: '' ?>" placeholder="465"></div>
      </div>
      <div class="grid2">
        <div class="fld"><label class="f" for="smtp_user">Username (usually the full email address)</label><input type="text" id="smtp_user" name="smtp_user" value="<?= h($S['smtp_user'] ?? '') ?>" autocomplete="off" placeholder="<?= h($S['email']) ?>"></div>
        <div class="fld"><label class="f" for="smtp_pass">Password</label><input type="password" id="smtp_pass" name="smtp_pass" value="" autocomplete="new-password" placeholder="<?= !empty($S['smtp_pass']) ? 'saved — leave blank to keep it' : 'your mailbox password' ?>">
          <?php if(!empty($S['smtp_pass'])): ?><label class="row small"><input type="checkbox" name="smtp_clear" value="1"> Remove the saved password</label><?php endif; ?></div>
      </div>
    </div>

    <div class="card"><h2>Live chat</h2>
      <div class="fld"><label class="f" for="chat_code">Chat code from your chat provider</label>
        <textarea id="chat_code" name="chat_code" rows="9" spellcheck="false" style="font-family:ui-monospace,Menlo,Consolas,monospace;font-size:12.5px"><?= h($S['chat_code'] ?? '') ?></textarea>
        <div class="hint">Paste the whole snippet (Tawk.to: Administration → Channels → Chat Widget). It loads after each page has finished loading, so it never slows the shop down. Leave it empty to turn chat off.
          To see visitors live and get new-chat alerts on your phone, install the Tawk.to app (iPhone or Android) and sign in.</div></div>
    </div>

    <div class="card"><h2>Currencies</h2>
      <?php $B = base_cur(); ?>
      <p class="small muted" style="margin-top:0">Prices are set in <?= h($B) ?>, the shop currency; other currencies are converted with these rates. Update the rates now and then.</p>
      <div class="fld" style="max-width:320px"><label class="f" for="base_currency">Shop currency</label>
        <select id="base_currency" name="base_currency"><?php foreach(array_keys($STORE['currencies']) as $code): ?><option value="<?= h($code) ?>" <?= $code === $B ? 'selected' : '' ?>><?= h($code) ?></option><?php endforeach; ?></select>
        <div class="hint">Changing it converts every price, shipping rate, the minimum order and the free-shipping amount at the rate below. Past orders keep their own currency.</div></div>
      <div class="scroll"><table class="t"><thead><tr><th>Code</th><th>Symbol</th><th>1 <?= h($B) ?> =</th><th>Decimals</th><th>Delete</th></tr></thead><tbody>
      <?php $i = 0; foreach($STORE['currencies'] + ['' => ['rate'=>'','sym'=>'','dec'=>2]] as $code=>$m): ?>
        <tr><td><input type="text" name="curs[<?= $i ?>][code]" value="<?= h($code) ?>" maxlength="3" placeholder="New" aria-label="Code" <?= $code===$B?'readonly':'' ?>></td>
            <td><input type="text" name="curs[<?= $i ?>][sym]" value="<?= h($m['sym']) ?>" aria-label="Symbol"></td>
            <td><input type="number" name="curs[<?= $i ?>][rate]" value="<?= h($m['rate']) ?>" min="0" step="any" aria-label="Rate" <?= $code===$B?'readonly':'' ?>></td>
            <td><input type="number" name="curs[<?= $i ?>][dec]" value="<?= (int)$m['dec'] ?>" min="0" max="3" aria-label="Decimals"></td>
            <td><?php if($code !== $B && $code !== ''): ?><input type="checkbox" name="curs[<?= $i ?>][delete]" value="1" aria-label="Delete"><?php endif; ?></td></tr>
      <?php $i++; endforeach; ?>
      </tbody></table></div>
    </div>

    <div class="card"><h2>Countries you ship to</h2>
      <p class="small muted" style="margin-top:0">One per line: two-letter code = name. These fill the country list at checkout.</p>
      <textarea name="countries" rows="12"><?= h(implode("\n", array_map(fn($c, $n)=>"$c = $n", array_keys($STORE['countries']), $STORE['countries']))) ?></textarea>
    </div>
    <button class="btn" type="submit">Save settings</button>
  </form>
  <?php $mt = data_read('mail-test'); ?>
  <div class="card" style="margin-top:16px"><h2>Send a test email</h2>
    <p class="small muted" style="margin-top:0">Order emails use exactly this route. Send one to your business address and to a personal one (e.g. Gmail) to check both arrive, and not in spam.
      <?php if($mt): ?><br>Last test: <?= h(gmdate('j M H:i', $mt['time'])) ?> UTC to <?= h($mt['to']) ?> — <?= $mt['ok'] ? 'sent' : '<b style="color:var(--seal)">failed</b> ('.h($mt['error']).')' ?>.<?php endif; ?></p>
    <form method="post" class="row"><?= csrf_field() ?><input type="hidden" name="do" value="mail_test">
      <input type="email" name="to" value="<?= h($S['order_email']) ?>" style="max-width:320px" aria-label="Send the test to"><button class="btn" type="submit">Send test email</button></form>
  </div>

<?php elseif($v === 'faq'): ?>
  <h1>FAQ</h1>
  <p class="sub">Questions on the FAQ page, in this order. You can write {min_order}, {reply_hours}, {hold_hours} or {countries} and the shop fills in the current value.</p>
  <form method="post"><?= csrf_field() ?><input type="hidden" name="do" value="faq_save">
    <?php $faqs = $STORE['faqs']; for($i=0; $i<count($faqs)+2; $i++): $fq = $faqs[$i] ?? ['','']; ?>
      <div class="card">
        <div class="fld"><label class="f" for="q<?= $i ?>"><?= isset($faqs[$i]) ? 'Question '.($i+1) : 'New question' ?></label><input type="text" id="q<?= $i ?>" name="faqs[<?= $i ?>][q]" value="<?= h($fq[0]) ?>"></div>
        <div class="fld"><label class="f" for="a<?= $i ?>">Answer</label><textarea id="a<?= $i ?>" name="faqs[<?= $i ?>][a]"><?= h($fq[1]) ?></textarea></div>
        <?php if(isset($faqs[$i])): ?><label class="row small"><input type="checkbox" name="faqs[<?= $i ?>][delete]" value="1"> Delete this question</label><?php endif; ?>
      </div>
    <?php endfor; ?>
    <button class="btn" type="submit">Save FAQ</button>
  </form>

<?php elseif($v === 'password'): ?>
  <h1>Change password</h1>
  <p class="sub">Changing it signs out every other device.</p>
  <div class="card" style="max-width:420px">
    <form method="post"><?= csrf_field() ?><input type="hidden" name="do" value="password">
      <div class="fld"><label class="f" for="cur">Current password</label><input type="password" id="cur" name="current" autocomplete="current-password" required></div>
      <div class="fld"><label class="f" for="new">New password</label><input type="password" id="new" name="new" autocomplete="new-password" minlength="10" required></div>
      <div class="fld"><label class="f" for="conf">Repeat new password</label><input type="password" id="conf" name="confirm" autocomplete="new-password" minlength="10" required></div>
      <button class="btn" type="submit">Change password</button>
    </form>
  </div>
<?php endif; ?>
</main>
</body>
</html>
<?php
function orders_table($orders){ ?>
  <div class="scroll"><table class="t">
    <thead><tr><th>Order</th><th>Placed</th><th>Customer</th><th>Country</th><th>Payment</th><th class="r">Total</th><th>Status</th></tr></thead><tbody>
    <?php foreach($orders as $o): $st = $o['status'] ?? 'new'; ?>
      <tr><td><a href="<?= h(self_url('order', ['ref'=>$o['ref']])) ?>"><b><?= h($o['ref']) ?></b></a></td>
          <td class="small"><?= h($o['time']) ?></td>
          <td><?= h($o['name']) ?><?php if($o['company']): ?><br><span class="muted small"><?= h($o['company']) ?></span><?php endif; ?></td>
          <td><?= h($o['country_name']) ?></td>
          <td><?= h($o['payment_label']) ?><?php if(!empty($o['btc'])): ?><br><span class="muted small">₿ <?= h(btc_state_label(btc_state($o))) ?></span><?php endif; ?></td>
          <td class="r"><?= usd($o['total_usd'] ?? 0, order_base($o)) ?></td>
          <td><span class="pill s-<?= h($st) ?>"><?= h(status_label(ORDER_STATUSES, $st)) ?></span></td></tr>
    <?php endforeach; ?>
    </tbody></table></div>
<?php }
