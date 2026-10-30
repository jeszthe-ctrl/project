<?php
/* =============================================================
   Beagle Brook — admin. Open /admin.php in your browser.
   The first visit asks you to create the admin sign-in, so do
   that straight after uploading. Everything is saved to data/
   (last 30 versions kept in data/backups/).
   ============================================================= */

define('BB_ROOT', __DIR__);
@ini_set('display_errors', '0');
error_reporting(E_ALL & ~E_NOTICE & ~E_WARNING & ~E_DEPRECATED);
require BB_ROOT.'/inc/core.php';
start_session();
header('X-Frame-Options: DENY');
header('X-Robots-Tag: noindex, nofollow');
header('Cache-Control: no-store');
header('Referrer-Policy: same-origin');

$SITE = site_load();
$CFG  = $SITE['settings'];
$AUTH = data_read('auth') ?: [];
$IP   = $_SERVER['REMOTE_ADDR'] ?? '';

/* the public site's address for "view on site" links */
function site_url($path=''){
  global $CFG;
  $path = trim($path, '/');
  return !empty($CFG['pretty_urls']) ? ($path === '' ? './' : $path) : ($path === '' ? './' : 'index.php?path='.rawurlencode($path));
}
function path_url($p){ return site_url($p); }

/* ---------------- helpers ---------------- */
const ARTICLE_GROUPS = ['buying'=>'Buying a beagle puppy', 'breed'=>'The beagle breed', 'care'=>'Care, food and training', 'mixes'=>'Beagle mixes', 'culture'=>'History and news'];
const FORMAT_HELP = 'Formatting: blank line = new paragraph · "## " heading, "### " subheading · "- " bullet, "1. " numbered · **bold**, *italic* · [link text](/health-guarantee) or [text](https://…) · "> " highlighted note · tables as | a | b | lines. {deposit} {balance} {vet_check_hours} ({vet_check_hour}) {genetic_years} ({genetic_year}) {go_home_weeks} {email} {city} {state} {payment_methods} {registry} {reply_time} {delivery_options} fill in your settings. A line with only {guarantees}, {steps}, {included}, {payments}, {delivery_rates}, {puppies}, {licenses}, {payee}, {breeder} or {contact} shows that block. Questions under "## Questions" written as "### Question" become FAQs for Google.';
function self_url($v='dashboard', $extra=[]){ return 'admin.php?'.http_build_query(['v'=>$v] + $extra); }
function go($v='dashboard', $extra=[]){ header('Location: '.self_url($v, $extra), true, 303); exit; }
function note($msg, $type='ok'){ $_SESSION['admin_flash'][] = [$type, $msg]; }
function in_str($k, $max=20000){ $v = $_POST[$k] ?? ''; return is_string($v) ? mb_substr(trim(str_replace("\r", '', $v)), 0, $max) : ''; }
function in_line($k, $max=300){ return trim(preg_replace('/[\r\n\t]+/', ' ', in_str($k, $max))); }
function in_bool($k){ return !empty($_POST[$k]); }
function in_date($k){ $v = in_line($k, 20); return preg_match('/^\d{4}-\d{2}-\d{2}$/', $v) ? $v : ''; }
function in_price($k){ $v = str_replace([',', '$', ' '], '', in_line($k, 20)); return is_numeric($v) && (float)$v >= 0 ? (string)round((float)$v, 2) : ''; }

function is_admin(){ global $AUTH; return !empty($AUTH['key']) && hash_equals($AUTH['key'], (string)($_SESSION['admin'] ?? '')); }
function csrf(){ if(empty($_SESSION['admin_csrf'])) $_SESSION['admin_csrf'] = bin2hex(random_bytes(16)); return $_SESSION['admin_csrf']; }
function csrf_field(){ return '<input type="hidden" name="csrf" value="'.h(csrf()).'">'; }

/* 5 failed sign-ins from one connection inside 15 minutes locks it until they age out */
function recent_fails(){ global $AUTH, $IP; return array_values(array_filter($AUTH['fails'][$IP] ?? [], fn($t)=>$t > time() - 900)); }
function record_fail(){
  global $AUTH, $IP;
  $AUTH['fails'][$IP] = array_merge(recent_fails(), [time()]);
  foreach($AUTH['fails'] as $ip=>$ts){ $AUTH['fails'][$ip] = array_values(array_filter($ts, fn($t)=>$t > time() - 900)); if(!$AUTH['fails'][$ip]) unset($AUTH['fails'][$ip]); }
  data_write('auth', $AUTH);
}
function valid_user($u){ return (bool)preg_match('/^[A-Za-z0-9._@-]{3,40}$/', $u); }
function log_in(){
  global $AUTH, $IP;
  session_regenerate_id(true);
  unset($AUTH['fails'][$IP]);
  data_write('auth', $AUTH);
  $_SESSION['admin'] = $AUTH['key'];
}
function save($msg){
  global $SITE;
  if(site_save($SITE)) note($msg);
  else note('Could not save. The data folder is not writable — ask your host to make it writable by PHP.', 'err');
}

/* unique address part for a puppy, parent or article */
function unique_slug($base, $list, $self_id, $key='id'){
  $base = slugify($base) ?: 'item';
  $slug = $base; $n = 2;
  while(array_filter($list, fn($x)=>($x['slug'] ?? '') === $slug && ($x[$key] ?? '') !== $self_id)) $slug = $base.'-'.$n++;
  return $slug;
}

/* photos: keep the ones not ticked for removal, put the chosen main photo first, add new uploads */
function handle_photos($current, $prefix){
  $keep = [];
  $remove = $_POST['remove_photo'] ?? [];
  foreach($current as $f){ if(in_array($f, (array)$remove, true)) delete_upload($f); else $keep[] = $f; }
  $main = (string)($_POST['main_photo'] ?? '');
  if($main !== '' && in_array($main, $keep, true)) $keep = array_values(array_unique(array_merge([$main], $keep)));
  foreach(upload_list('photos') as $f){
    $r = save_upload($f, $prefix);
    if($r === '') continue;
    if($r[0] === '!') note(substr($r, 1), 'err'); else $keep[] = $r;
  }
  return array_slice(array_values($keep), 0, 12);
}
/* one photo field (hero, breeder, review, article image) */
function handle_one_photo($field, $current, $prefix){
  if(!empty($_POST['remove_'.$field])){ delete_upload($current); $current = ''; }
  $r = save_upload($_FILES[$field] ?? null, $prefix);
  if($r !== '' && $r[0] === '!'){ note(substr($r, 1), 'err'); return $current; }
  if($r !== ''){ if($current !== '') delete_upload($current); return $r; }
  return $current;
}

function inquiries_all(){
  $out = [];
  foreach(glob(data_dir().'/inquiries/BB-*.php') ?: [] as $f){ $q = data_read('inquiries/'.basename($f, '.php')); if($q) $out[] = $q; }
  usort($out, fn($a, $b)=>strcmp($b['time'] ?? '', $a['time'] ?? ''));
  return $out;
}
function valid_ref($r){ return is_string($r) && preg_match('/^BB-\d{2}-[A-F0-9]{6}$/', $r); }
function waitlist(){ return (data_read('waitlist') ?: ['list'=>[]])['list']; }
function unsub_link($email){ global $CFG; $u = site_url('unsubscribe'); return rtrim($CFG['domain'], '/').'/'.($u === './' ? '' : $u).(strpos($u, '?') !== false ? '&' : '?').http_build_query(['e'=>strtolower($email), 'k'=>sign('unsub|'.strtolower($email))]); }
function live_url($path){ global $CFG; $u = site_url($path); return rtrim($CFG['domain'], '/').'/'.($u === './' ? '' : $u); }

/* emails the whole waitlist (each with their own unsubscribe link); returns [sent, failed] */
function email_waitlist($subject, $body){
  global $CFG;
  @set_time_limit(0);
  $sent = $failed = 0;
  foreach(waitlist() as $w){
    $text = 'Hi '.strtok($w['name'], ' ').",\n\n".$body."\n\n— {$CFG['brand']}\n{$CFG['city']}, {$CFG['state']} · {$CFG['email']}\n\nYou’re receiving this because you joined our waitlist. Unsubscribe: ".unsub_link($w['email']);
    if(site_mail($w['email'], $subject, $text, $CFG['email'], ['List-Unsubscribe: <'.unsub_link($w['email']).'>'])) $sent++; else $failed++;
  }
  return [$sent, $failed];
}
function puppy_announcement($p){
  global $CFG;
  $age = age_text($p['dob'] ?? '');
  return ["New beagle puppy: {$p['name']}",
    "Good news — {$p['name']} is now available at {$CFG['brand']}.\n\n"
    ."{$p['name']}: ".trim($p['color'].' '.strtolower($p['sex'] === 'female' ? 'female' : 'male')).' beagle'.($age ? ", $age old" : '').(($p['price'] ?? '') !== '' ? ', '.money($p['price']) : '').".\n"
    ."Vet checked, shots, microchip, starter food and full papers, delivered from {$CFG['city']}, {$CFG['state']}.\n\n"
    ."See photos and send an inquiry: ".live_url('beagle-puppies-for-sale/'.$p['slug'])."\n\nWaitlist families hear first, so if {$p['name']} is the one, let us know soon."];
}

/* ---------------- sign in / set up ---------------- */
$v = is_string($_GET['v'] ?? null) ? $_GET['v'] : 'dashboard';
$POST = $_SERVER['REQUEST_METHOD'] === 'POST';
$act = $POST ? (string)($_POST['act'] ?? '') : '';

if($act === 'setup' && empty($AUTH['hash'])){
  $u = in_line('user', 40); $p = (string)($_POST['pass'] ?? '');
  if(!valid_user($u)) note('Use a username of 3–40 letters, numbers, dots or dashes.', 'err');
  elseif(strlen($p) < 10) note('Use a password of at least 10 characters.', 'err');
  elseif($p !== (string)($_POST['pass2'] ?? '')) note('The two passwords don’t match.', 'err');
  else {
    $AUTH = ['user'=>$u, 'hash'=>password_hash($p, PASSWORD_DEFAULT), 'key'=>bin2hex(random_bytes(16)), 'fails'=>[]];
    if(data_write('auth', $AUTH)){ log_in(); note('Your sign-in is set up. Welcome to your Beagle Brook admin!'); }
    else note('Could not save: the data folder isn’t writable. Ask your host to make it writable by PHP.', 'err');
  }
  go();
}
if($act === 'login'){
  if(count(recent_fails()) >= 5) note('Too many attempts. Wait 15 minutes and try again.', 'err');
  elseif(!empty($AUTH['hash']) && hash_equals(strtolower($AUTH['user'] ?? ''), strtolower(in_line('user', 40))) && password_verify((string)($_POST['pass'] ?? ''), $AUTH['hash'])){ log_in(); go(); }
  else { record_fail(); note('Wrong username or password.', 'err'); }
  go();
}
if($v === 'logout'){ unset($_SESSION['admin']); session_regenerate_id(true); go(); }

if(!is_admin()){ layout_start('Sign in'); login_view(); layout_end(); exit; }
if($POST && !hash_equals(csrf(), (string)($_POST['csrf'] ?? ''))){ note('The form expired. Please try again.', 'err'); go($v); }

/* ---------------- actions ---------------- */
$find_index = function($list, $id){ foreach($list as $i=>$x){ if(($x['id'] ?? '') === $id) return $i; } return null; };

if($act === 'save_puppy'){
  $id = in_line('id', 20); $i = $id !== '' ? $find_index($SITE['puppies'], $id) : null;
  $p = $i !== null ? $SITE['puppies'][$i] : ['id'=>new_id(), 'created'=>date('c'), 'photos'=>[]];
  $name = in_line('name', 60);
  if($name === ''){ note('Give the puppy a name.', 'err'); go('puppy', $id ? ['id'=>$id] : []); }
  $p = array_merge($p, [
    'name'=>$name, 'status'=>array_key_exists(in_line('status'), PUPPY_STATUSES) ? in_line('status') : 'available',
    'sex'=>in_line('sex') === 'female' ? 'female' : 'male', 'color'=>in_line('color', 60), 'dob'=>in_date('dob'), 'ready'=>in_date('ready'),
    'price'=>in_price('price'), 'size'=>in_array(in_line('size'), ['13', '15'], true) ? in_line('size') : '', 'weight'=>in_line('weight', 40),
    'registry'=>in_line('registry', 40), 'reg_no'=>in_line('reg_no', 80), 'sire'=>in_line('sire', 20), 'dam'=>in_line('dam', 20),
    'microchip'=>in_bool('microchip'), 'vet_date'=>in_date('vet_date'), 'vaccines'=>in_str('vaccines', 1000), 'tagline'=>in_line('tagline', 200),
    'description'=>in_str('description', 20000), 'video'=>in_line('video', 300), 'hidden'=>in_bool('hidden'),
    'seo_title'=>in_line('seo_title', 120), 'seo_desc'=>in_line('seo_desc', 300), 'updated'=>date('c'),
  ]);
  $want = in_line('slug', 80) !== '' ? in_line('slug', 80) : $name.' '.$p['color'].' '.$p['sex'];
  if($i === null || in_line('slug', 80) !== ($p['slug'] ?? '')) $p['slug'] = unique_slug($want, $SITE['puppies'], $p['id']);
  $p['photos'] = handle_photos($p['photos'] ?? [], 'puppy-'.$p['slug']);
  if($i === null) array_unshift($SITE['puppies'], $p); else $SITE['puppies'][$i] = $p;
  save($i === null ? "{$p['name']} is added." : "{$p['name']} is saved.");
  if(in_bool('notify') && $p['status'] === 'available' && !$p['hidden']){
    [$sub, $body] = puppy_announcement($p);
    [$sent, $failed] = email_waitlist($sub, $body);
    note("Emailed {$p['name']} to $sent waitlist ".($sent === 1 ? 'family' : 'families').($failed ? " ($failed failed — check Settings → Email)" : '').'.', $failed ? 'err' : 'ok');
  }
  go('puppy', ['id'=>$p['id']]);
}
if($act === 'puppy_status'){
  $i = $find_index($SITE['puppies'], in_line('id', 20));
  if($i !== null && array_key_exists(in_line('status'), PUPPY_STATUSES)){ $SITE['puppies'][$i]['status'] = in_line('status'); $SITE['puppies'][$i]['updated'] = date('c'); save($SITE['puppies'][$i]['name'].' is now '.PUPPY_STATUSES[in_line('status')].'.'); }
  go(in_line('back', 20) ?: 'puppies', in_line('ref', 20) ? ['ref'=>in_line('ref', 20)] : []);
}
if($act === 'delete_puppy'){
  $i = $find_index($SITE['puppies'], in_line('id', 20));
  if($i !== null){ foreach($SITE['puppies'][$i]['photos'] ?? [] as $f) delete_upload($f); $n = $SITE['puppies'][$i]['name']; array_splice($SITE['puppies'], $i, 1); save("$n is deleted."); }
  go('puppies');
}

if($act === 'save_parent'){
  $id = in_line('id', 20); $i = $id !== '' ? $find_index($SITE['parents'], $id) : null;
  $p = $i !== null ? $SITE['parents'][$i] : ['id'=>new_id(), 'photos'=>[]];
  $name = in_line('name', 60);
  if($name === ''){ note('Give the dog a name.', 'err'); go('parent', $id ? ['id'=>$id] : []); }
  $p = array_merge($p, ['name'=>$name, 'role'=>in_line('role') === 'sire' ? 'sire' : 'dam', 'registered_name'=>in_line('registered_name', 120),
    'registry'=>in_line('registry', 40), 'reg_no'=>in_line('reg_no', 80), 'dob'=>in_date('dob'), 'color'=>in_line('color', 60),
    'height'=>in_line('height', 40), 'weight'=>in_line('weight', 40), 'health'=>in_str('health', 3000), 'description'=>in_str('description', 10000),
    'retired'=>in_bool('retired'), 'hidden'=>in_bool('hidden')]);
  if(empty($p['slug']) || slugify($p['name']) !== preg_replace('/-\d+$/', '', $p['slug'])) $p['slug'] = unique_slug($p['name'], $SITE['parents'], $p['id']);
  $p['photos'] = handle_photos($p['photos'] ?? [], 'parent-'.$p['slug']);
  if($i === null) $SITE['parents'][] = $p; else $SITE['parents'][$i] = $p;
  save("{$p['name']} is saved.");
  go('parent', ['id'=>$p['id']]);
}
if($act === 'delete_parent'){
  $i = $find_index($SITE['parents'], in_line('id', 20));
  if($i !== null){ foreach($SITE['parents'][$i]['photos'] ?? [] as $f) delete_upload($f); $n = $SITE['parents'][$i]['name']; array_splice($SITE['parents'], $i, 1); save("$n is deleted."); }
  go('parents');
}

if($act === 'save_litter'){
  $id = in_line('id', 20); $i = $id !== '' ? $find_index($SITE['litters'], $id) : null;
  $l = ['id'=>$id !== '' ? $id : new_id(), 'title'=>in_line('title', 120), 'sire'=>in_line('sire', 20), 'dam'=>in_line('dam', 20),
        'status'=>array_key_exists(in_line('status'), LITTER_STATUSES) ? in_line('status') : 'planned', 'date'=>in_date('date'),
        'price'=>in_line('price', 60), 'notes'=>in_str('notes', 5000)];
  if($i === null) $SITE['litters'][] = $l; else $SITE['litters'][$i] = $l;
  save('Litter saved.');
  go('litters');
}
if($act === 'delete_litter'){
  $i = $find_index($SITE['litters'], in_line('id', 20));
  if($i !== null){ array_splice($SITE['litters'], $i, 1); save('Litter deleted.'); }
  go('litters');
}

if($act === 'save_review'){
  $id = in_line('id', 20); $i = $id !== '' ? $find_index($SITE['reviews'], $id) : null;
  $r = $i !== null ? $SITE['reviews'][$i] : ['id'=>new_id(), 'photo'=>''];
  $r = array_merge($r, ['name'=>in_line('name', 80), 'location'=>in_line('location', 80), 'date'=>in_date('date'), 'puppy'=>in_line('puppy', 60),
    'rating'=>max(1, min(5, (int)in_line('rating', 1) ?: 5)), 'text'=>in_str('text', 5000), 'hidden'=>in_bool('hidden')]);
  if($r['name'] === '' || $r['text'] === ''){ note('A review needs the family’s name and their words.', 'err'); go('review', $id ? ['id'=>$id] : []); }
  $r['photo'] = handle_one_photo('photo', $r['photo'] ?? '', 'review-'.$r['name']);
  if($i === null) array_unshift($SITE['reviews'], $r); else $SITE['reviews'][$i] = $r;
  save('Review saved.');
  go('reviews');
}
if($act === 'delete_review'){
  $i = $find_index($SITE['reviews'], in_line('id', 20));
  if($i !== null){ delete_upload($SITE['reviews'][$i]['photo'] ?? ''); array_splice($SITE['reviews'], $i, 1); save('Review deleted.'); }
  go('reviews');
}

if($act === 'save_article'){
  $orig = in_line('orig', 120); $type = in_line('type') === 'blog' ? 'blog' : 'guide';
  $idx = null; foreach($SITE['articles'] as $k=>$a){ if($a['slug'] === $orig && $orig !== ''){ $idx = $k; break; } }
  $a = $idx !== null ? $SITE['articles'][$idx] : ['date'=>date('Y-m-d'), 'image'=>''];
  $title = in_line('title', 200);
  if($title === '' || in_str('body') === ''){ note('A guide or post needs a title and some text.', 'err'); go('article', $orig ? ['slug'=>$orig] : ['type'=>$type]); }
  $changed = ($a['body'] ?? '') !== in_str('body') || ($a['title'] ?? '') !== $title;
  $slug = in_line('slug', 120) !== '' ? in_line('slug', 120) : $title;
  $slug = unique_slug($slug, array_map(fn($x)=>$x + ['sid'=>$x['slug']], $SITE['articles']), $orig, 'sid');
  $a = array_merge($a, ['slug'=>$slug, 'type'=>$type, 'group'=>in_line('group', 20) ?: 'buying', 'title'=>$title, 'seo_title'=>in_line('seo_title', 120),
    'seo_desc'=>in_line('seo_desc', 300), 'excerpt'=>in_line('excerpt', 300), 'body'=>in_str('body', 200000), 'draft'=>in_bool('draft'),
    'date'=>in_date('date') ?: ($a['date'] ?? date('Y-m-d'))]);
  if($changed && $idx !== null) $a['updated'] = date('Y-m-d');
  if($idx !== null && $slug !== $orig) $SITE['removed']['articles'][] = $orig;   /* a renamed starter guide mustn't come back under its old address */
  $a['image'] = handle_one_photo('image', $a['image'] ?? '', 'article-'.$slug);
  if($idx === null) $SITE['articles'][] = $a; else $SITE['articles'][$idx] = $a;
  save('“'.$title.'” is saved.'.($a['draft'] ? ' It’s a draft, so it isn’t on the site yet.' : ''));
  go('article', ['slug'=>$slug]);
}
if($act === 'delete_article'){
  foreach($SITE['articles'] as $k=>$a){ if($a['slug'] === in_line('slug', 120)){ delete_upload($a['image'] ?? ''); array_splice($SITE['articles'], $k, 1); $SITE['removed']['articles'][] = $a['slug']; save('Deleted.'); break; } }
  go('articles');
}

if($act === 'save_page'){
  $orig = in_line('orig', 120);
  $idx = null; foreach($SITE['pages'] as $k=>$pg){ if($pg['slug'] === $orig && $orig !== ''){ $idx = $k; break; } }
  $pg = ['slug'=>$idx !== null ? $orig : unique_slug(in_line('slug', 120) ?: in_line('title', 120), array_map(fn($x)=>$x + ['sid'=>$x['slug']], $SITE['pages']), '', 'sid'),
         'menu'=>in_line('menu', 60), 'title'=>in_line('title', 200), 'seo_title'=>in_line('seo_title', 120), 'seo_desc'=>in_line('seo_desc', 300), 'body'=>in_str('body', 100000)];
  if(in_array($pg['slug'], ['beagle-puppies-for-sale', 'our-beagles', 'upcoming-litters', 'reviews', 'contact', 'guides', 'blog', 'thank-you', 'unsubscribe', 'admin', 'assets', 'data', 'inc'], true)){ note('That address is used by the site already. Choose another.', 'err'); go('page'); }
  if($pg['title'] === ''){ note('A page needs a title.', 'err'); go('page', $orig ? ['slug'=>$orig] : []); }
  if($idx === null) $SITE['pages'][] = $pg; else $SITE['pages'][$idx] = $pg;
  save('“'.$pg['title'].'” is saved.');
  go('page', ['slug'=>$pg['slug']]);
}
if($act === 'delete_page'){
  foreach($SITE['pages'] as $k=>$pg){ if($pg['slug'] === in_line('slug', 120)){ array_splice($SITE['pages'], $k, 1); $SITE['removed']['pages'][] = $pg['slug']; save('Page deleted.'); break; } }
  go('pages');
}

if($act === 'save_promises'){
  foreach(['guarantees', 'steps', 'included'] as $k){
    $items = [];
    foreach(preg_split('/\n\s*\n/', in_str($k, 20000)) as $block){
      $lines = array_values(array_filter(array_map('trim', explode("\n", trim($block)))));
      if(count($lines) >= 2) $items[] = ['title'=>$lines[0], 'text'=>implode(' ', array_slice($lines, 1))];
    }
    if($items) $SITE[$k] = $items;
  }
  save('Guarantees, steps and “what’s included” saved.');
  go('promises');
}

if($act === 'save_delivery'){
  $methods = [];
  foreach(['standard', 'express'] as $k){
    $row = (array)($_POST['m'][$k] ?? []);
    $methods[] = ['key'=>$k, 'label'=>mb_substr(trim((string)($row['label'] ?? '')), 0, 60) ?: ucfirst($k).' delivery', 'days'=>mb_substr(trim((string)($row['days'] ?? '')), 0, 80), 'on'=>!empty($row['on'])];
  }
  $zones = [];
  foreach((array)($_POST['z'] ?? []) as $row){
    $name = trim((string)($row['name'] ?? '')); $states = strtoupper(preg_replace('/[^A-Za-z*,]+/', '', str_replace(' ', ',', (string)($row['states'] ?? ''))));
    if($name === '' && $states === '') continue;
    $price = fn($v)=>is_numeric(str_replace(['$', ','], '', (string)$v)) ? (string)round((float)str_replace(['$', ','], '', (string)$v), 2) : '';
    $zones[] = ['name'=>mb_substr($name ?: 'Zone', 0, 60), 'states'=>trim(preg_replace('/,+/', ',', $states), ','), 'standard'=>$price($row['standard'] ?? ''), 'express'=>$price($row['express'] ?? '')];
  }
  usort($zones, fn($a, $b)=>($a['states'] === '*') <=> ($b['states'] === '*'));   /* specific states first, "*" last */
  if(!$zones) note('Add at least one zone (use * for all states).', 'err');
  else { $SITE['delivery'] = ['methods'=>$methods, 'zones'=>$zones]; save('Delivery options saved.'); }
  go('delivery');
}
if($act === 'save_payments'){
  $out = [];
  foreach((array)($_POST['pay'] ?? []) as $row){
    $name = trim(preg_replace('/\s+/', ' ', (string)($row['name'] ?? '')));
    if($name === '') continue;
    $out[] = ['key'=>slugify($row['key'] ?? '' ?: $name) ?: new_id(), 'name'=>mb_substr($name, 0, 40), 'note'=>mb_substr(trim((string)($row['note'] ?? '')), 0, 200), 'on'=>!empty($row['on'])];
  }
  if(!array_filter($out, fn($p)=>$p['on'])) note('Keep at least one payment method switched on, so customers can choose one.', 'err');
  else { $SITE['payments'] = $out; save('Payment methods saved.'); }
  go('payments');
}

const SETTING_TEXT = ['brand'=>80, 'phone'=>40, 'city'=>60, 'state'=>2, 'state_name'=>40, 'registry'=>40, 'payee_name'=>120, 'reply_time'=>60, 'breeder_name'=>80,
  'tagline'=>200, 'hero_title'=>200, 'home_seo_title'=>120, 'home_seo_desc'=>300, 'puppies_title'=>120, 'puppies_seo_title'=>120, 'puppies_seo_desc'=>300,
  'facebook'=>300, 'instagram'=>300, 'tiktok'=>300, 'youtube'=>300, 'google_reviews_url'=>300, 'gsc_verification'=>200, 'bing_verification'=>200, 'ga4_id'=>30,
  'smtp_host'=>200, 'smtp_port'=>6, 'smtp_user'=>200, 'alert_phone'=>30, 'alert_sms_email'=>200, 'twilio_sid'=>80, 'twilio_from'=>30];
const SETTING_LONG = ['hero_lede'=>1000, 'puppies_intro'=>5000, 'parents_intro'=>3000, 'litters_intro'=>3000, 'reviews_intro'=>3000, 'contact_intro'=>3000,
  'footer_blurb'=>500, 'delivery_note'=>1000, 'breeder_story'=>10000, 'license_text'=>2000];
const SETTING_INT = ['deposit_pct'=>[50, 100], 'vet_check_hours'=>[24, 720], 'genetic_years'=>[1, 10], 'go_home_weeks'=>[8, 16]];

if($act === 'save_settings'){
  $s = &$SITE['settings'];
  foreach(SETTING_TEXT as $k=>$max) if(isset($_POST[$k])) $s[$k] = in_line($k, $max);
  foreach(SETTING_LONG as $k=>$max) if(isset($_POST[$k])) $s[$k] = in_str($k, $max);
  foreach(SETTING_INT as $k=>[$lo, $hi]) if(isset($_POST[$k])) $s[$k] = max($lo, min($hi, (int)$_POST[$k]));
  if(isset($_POST['email'])){ $e = in_line('email', 160); if(filter_var($e, FILTER_VALIDATE_EMAIL)) $s['email'] = $e; else note('That email address doesn’t look right, so it wasn’t changed.', 'err'); }
  if(isset($_POST['domain'])){ $d = rtrim(in_line('domain', 200), '/'); if(preg_match('#^https?://[a-z0-9.-]+(:\d+)?(/[\w./-]*)?$#i', $d)) $s['domain'] = $d; else note('The website address should look like https://beaglebrook.com', 'err'); }
  if(isset($_POST['state'])) $s['state'] = strtoupper($s['state']);
  if(isset($_POST['state']) && isset(US_STATES[$s['state']]) && in_line('state_name', 40) === '') $s['state_name'] = US_STATES[$s['state']];
  if(isset($_POST['smtp_secure'])) $s['smtp_secure'] = in_array($_POST['smtp_secure'], ['tls', 'ssl', 'none'], true) ? $_POST['smtp_secure'] : 'tls';
  if(in_str('smtp_pass', 200) !== '') $s['smtp_pass'] = in_str('smtp_pass', 200);
  if(in_str('twilio_token', 200) !== '') $s['twilio_token'] = in_str('twilio_token', 200);
  if(isset($_POST['pretty_urls_form'])) $s['pretty_urls'] = in_bool('pretty_urls');
  foreach(['hero_photo'=>'hero', 'breeder_photo'=>'breeder', 'share_photo'=>'share'] as $k=>$prefix) if(isset($_FILES[$k]) || isset($_POST['remove_'.$k])) $s[$k] = handle_one_photo($k, $s[$k], $prefix);
  unset($s);
  save('Settings saved.');
  go('settings', ['tab'=>in_line('tab', 20) ?: 'business']);
}
if($act === 'test_email'){
  $to = in_line('to', 160);
  if(!filter_var($to, FILTER_VALIDATE_EMAIL)) note('Enter an email address to send the test to.', 'err');
  elseif(site_mail($to, 'Test email from your '.$CFG['brand'].' website', "This is a test from your website's admin.\n\nIf it arrived in your inbox (not spam), inquiry emails will reach you and your customers.")) note("Test email sent to $to. Check the inbox and the spam folder.");
  else note('The test email could not be sent. '.($GLOBALS['BB_MAIL_ERROR'] ?? ''), 'err');
  go('settings', ['tab'=>'alerts']);
}
if($act === 'test_text'){
  if(send_alert_text($CFG['brand'].': test text alert from your website.')) note('Test text sent. It should arrive within a minute.');
  else note('The test text could not be sent. Check the text alert settings. '.($GLOBALS['BB_MAIL_ERROR'] ?? ''), 'err');
  go('settings', ['tab'=>'alerts']);
}
if($act === 'save_account'){
  $u = in_line('user', 40); $p = (string)($_POST['pass'] ?? '');
  if(!password_verify((string)($_POST['current'] ?? ''), $AUTH['hash'])) note('Your current password isn’t right.', 'err');
  elseif(!valid_user($u)) note('Use a username of 3–40 letters, numbers, dots or dashes.', 'err');
  elseif($p !== '' && strlen($p) < 10) note('Use a password of at least 10 characters.', 'err');
  elseif($p !== '' && $p !== (string)($_POST['pass2'] ?? '')) note('The two new passwords don’t match.', 'err');
  else {
    $AUTH['user'] = $u;
    if($p !== ''){ $AUTH['hash'] = password_hash($p, PASSWORD_DEFAULT); $AUTH['key'] = bin2hex(random_bytes(16)); }
    data_write('auth', $AUTH); $_SESSION['admin'] = $AUTH['key'];
    note('Sign-in details saved.');
  }
  go('account');
}

if($act === 'save_inquiry'){
  $ref = in_line('ref', 20);
  $q = valid_ref($ref) ? data_read('inquiries/'.$ref) : null;
  if($q){
    if(array_key_exists(in_line('status'), INQUIRY_STATUSES)) $q['status'] = in_line('status');
    $q['admin_notes'] = in_str('admin_notes', 10000);
    data_write('inquiries/'.$ref, $q);
    note('Inquiry saved.');
  }
  go('inquiry', ['ref'=>$ref]);
}
if($act === 'delete_inquiry'){
  $ref = in_line('ref', 20);
  if(valid_ref($ref)) @unlink(data_file('inquiries/'.$ref));
  note('Inquiry deleted.');
  go('inquiries');
}
if($act === 'waitlist_send'){
  $sub = in_line('subject', 200); $body = in_str('body', 20000);
  if($sub === '' || $body === '') note('Write a subject and a message.', 'err');
  elseif(!waitlist()) note('Nobody is on the waitlist yet.', 'err');
  else { [$sent, $failed] = email_waitlist($sub, $body); note("Sent to $sent ".($sent === 1 ? 'family' : 'families').($failed ? ", $failed failed — check Settings → Email & alerts" : '').'.', $failed ? 'err' : 'ok'); }
  go('waitlist');
}
if($act === 'waitlist_remove'){
  $e = strtolower(in_line('email', 160));
  $w = data_read('waitlist') ?: ['list'=>[]];
  $w['list'] = array_values(array_filter($w['list'], fn($x)=>$x['email'] !== $e));
  data_write('waitlist', $w);
  note("$e removed from the waitlist.");
  go('waitlist');
}

/* CSV downloads */
if($v === 'export'){
  $what = $_GET['what'] ?? '';
  header('Content-Type: text/csv; charset=utf-8');
  header('Content-Disposition: attachment; filename="beaglebrook-'.($what === 'waitlist' ? 'waitlist' : 'inquiries').'-'.date('Y-m-d').'.csv"');
  $out = fopen('php://output', 'w');
  fwrite($out, "\xEF\xBB\xBF");
  $safe = fn($row)=>array_map(fn($c)=>preg_match('/^[=+\-@\t\r]/', (string)$c) ? "'".$c : $c, $row);   /* no spreadsheet formulas from form text */
  if($what === 'waitlist'){
    fputcsv($out, ['Joined', 'Name', 'Email', 'Phone', 'State', 'Sex', 'Color', 'Timing', 'Notes']);
    foreach(waitlist() as $w) fputcsv($out, $safe([$w['time'], $w['name'], $w['email'], $w['phone'], $w['state'], $w['sex'], $w['color'], $w['timing'], $w['notes']]));
  } else {
    fputcsv($out, ['Reference', 'Date', 'Type', 'Status', 'Puppy', 'Name', 'Email', 'Phone', 'Address', 'City', 'State', 'ZIP', 'Delivery', 'Delivery fee', 'Payment', 'Timing', 'Notes']);
    foreach(inquiries_all() as $q) fputcsv($out, $safe([$q['ref'], $q['time'], $q['kind'], INQUIRY_STATUSES[$q['status']] ?? $q['status'], $q['puppy_name'], $q['name'], $q['email'], $q['phone'],
      trim(($q['address1'] ?? '').' '.($q['address2'] ?? '')), $q['city'] ?? '', $q['state'] ?? '', $q['zip'] ?? '', $q['delivery_name'] ?? '', $q['delivery_fee'] ?? '', $q['payment_name'] ?? '', $q['timing'] ?? '', $q['notes']]));
  }
  exit;
}

/* ---------------- views ---------------- */
$VIEWS = ['dashboard'=>'Dashboard', 'puppies'=>'Puppies', 'parents'=>'Parents', 'litters'=>'Litters', 'inquiries'=>'Inquiries', 'waitlist'=>'Waitlist',
          'reviews'=>'Reviews', 'articles'=>'Guides & blog', 'pages'=>'Pages', 'promises'=>'Guarantees', 'delivery'=>'Delivery', 'payments'=>'Payments', 'settings'=>'Settings', 'account'=>'Sign-in'];
$SUB = ['puppy'=>'puppies', 'parent'=>'parents', 'inquiry'=>'inquiries', 'review'=>'reviews', 'article'=>'articles', 'page'=>'pages'];
if(!isset($VIEWS[$v]) && !isset($SUB[$v])) $v = 'dashboard';
layout_start($VIEWS[$v] ?? $VIEWS[$SUB[$v]]);
('view_'.$v)();
layout_end();

function layout_start($title){
  global $v, $VIEWS, $SUB, $CFG;
  $new = count(array_filter(inquiries_all(), fn($q)=>$q['status'] === 'new'));
  ?><!doctype html><html lang="en"><head><meta charset="utf-8"><meta name="viewport" content="width=device-width, initial-scale=1"><meta name="robots" content="noindex, nofollow">
<title><?= h($title) ?> · <?= h($CFG['brand']) ?> admin</title><link rel="icon" href="assets/favicon.svg" type="image/svg+xml">
<style>
:root{--bg:#f7f3ec;--s:#fff;--ink:#2a1f17;--mut:#6b5a4b;--line:#e3d7c5;--br:#7a4a1e;--brd:#5e3814;--ok:#256b38;--err:#a3311f}
*{box-sizing:border-box}body{margin:0;font:15px/1.5 system-ui,-apple-system,"Segoe UI",Roboto,sans-serif;background:var(--bg);color:var(--ink)}
a{color:var(--br)}h1{font:700 1.6rem Georgia,serif;margin:0 0 14px}h2{font:700 1.2rem Georgia,serif;margin:22px 0 10px}
.top{background:#2a1f17;color:#f2e3cf;padding:10px 18px;display:flex;align-items:center;gap:14px;flex-wrap:wrap}.top a{color:#f2e3cf;text-decoration:none}.top strong{margin-right:auto;font-family:Georgia,serif;font-size:1.1rem}
.side{display:flex;flex-wrap:wrap;gap:4px;padding:10px 18px;background:#fff;border-bottom:1px solid var(--line)}.side a{padding:7px 11px;border-radius:8px;text-decoration:none;color:var(--ink);font-weight:550}.side a.on{background:var(--br);color:#fff}
.pill{display:inline-block;background:#c0392b;color:#fff;border-radius:999px;padding:0 7px;font-size:.75rem;margin-left:4px}
main{max-width:1150px;margin:0 auto;padding:20px 18px 60px}
.card{background:var(--s);border:1px solid var(--line);border-radius:12px;padding:18px;margin-bottom:16px}
.grid{display:grid;gap:14px}@media(min-width:800px){.g2{grid-template-columns:1fr 1fr}.g3{grid-template-columns:1fr 1fr 1fr}.g4{grid-template-columns:repeat(4,1fr)}}
label{display:block;font-weight:600;font-size:.9rem;margin-bottom:12px}label small{font-weight:400;color:var(--mut);display:block}
input[type=text],input[type=email],input[type=password],input[type=date],input[type=number],input[type=url],input[type=tel],select,textarea{width:100%;font:inherit;padding:8px 10px;border:1px solid #cfbfa8;border-radius:8px;background:#fffdf9;margin-top:4px}
textarea{min-height:90px;resize:vertical}textarea.big{min-height:420px;font:13.5px/1.55 ui-monospace,Menlo,Consolas,monospace}
.cb{display:flex;gap:8px;align-items:center;font-weight:550}.cb input{width:18px;height:18px}
.btn{display:inline-block;background:var(--br);color:#fff;border:0;border-radius:999px;padding:9px 18px;font:600 .95rem inherit;cursor:pointer;text-decoration:none}.btn:hover{background:var(--brd);color:#fff}
.btn-g{background:#fff;color:var(--br);border:1px solid var(--br)}.btn-d{background:#fff;color:var(--err);border:1px solid var(--err)}.btn-s{padding:5px 12px;font-size:.85rem}
table{width:100%;border-collapse:collapse;background:#fff}th,td{text-align:left;padding:9px 10px;border-bottom:1px solid var(--line);vertical-align:top}th{font-size:.82rem;color:var(--mut);text-transform:uppercase;letter-spacing:.04em}
.tw{overflow-x:auto;border:1px solid var(--line);border-radius:12px}
.flash{padding:10px 14px;border-radius:10px;margin-bottom:12px}.flash.ok{background:#e6f3ea;color:#1f4d2d}.flash.err{background:#fdecea;color:#6d2014}
.badge{display:inline-block;font-size:.72rem;font-weight:700;text-transform:uppercase;padding:3px 8px;border-radius:999px;background:#eee}
.b-available,.b-new,.b-born{background:#e2f2e6;color:#256b38}.b-reserved,.b-contacted,.b-expected,.b-deposit{background:#fdf0d8;color:#8a5a00}.b-sold,.b-closed,.b-delivered,.b-planned{background:#efe7dd;color:#6b5a4b}
.photos{display:flex;flex-wrap:wrap;gap:10px}.photos figure{margin:0;width:130px;font-size:.8rem}.photos img{width:130px;height:100px;object-fit:cover;border-radius:8px}
.mut{color:var(--mut)}.row{display:flex;gap:10px;flex-wrap:wrap;align-items:center}.stat{font:700 2rem Georgia,serif}.right{margin-left:auto}
.chk{list-style:none;padding-left:0}.chk li{margin-bottom:6px}.chk .done{color:var(--ok)}.chk .todo{color:var(--err)}
.tabs{display:flex;gap:6px;flex-wrap:wrap;margin-bottom:14px}.tabs a{padding:6px 12px;border-radius:999px;border:1px solid var(--line);text-decoration:none;color:var(--ink);background:#fff}.tabs a.on{background:var(--br);color:#fff;border-color:var(--br)}
details summary{cursor:pointer;font-weight:600;margin-bottom:8px}
.help{font-size:.85rem;color:var(--mut);background:#fbf6ee;border:1px solid var(--line);border-radius:8px;padding:8px 10px;margin:-4px 0 12px}
</style></head><body>
<?php if(is_admin()): ?>
<div class="top"><strong><?= h($CFG['brand']) ?> admin</strong><a href="<?= h(site_url()) ?>" target="_blank">View website ↗</a><a href="<?= h(self_url('logout')) ?>">Sign out</a></div>
<nav class="side"><?php foreach($VIEWS as $k=>$label): $on = $v === $k || ($SUB[$v] ?? '') === $k; ?><a class="<?= $on ? 'on' : '' ?>" href="<?= h(self_url($k)) ?>"><?= h($label) ?><?= $k === 'inquiries' && $new ? '<span class="pill">'.$new.'</span>' : '' ?></a><?php endforeach; ?></nav>
<?php endif; ?>
<main><?php foreach($_SESSION['admin_flash'] ?? [] as [$t, $m]): ?><div class="flash <?= h($t) ?>"><?= h($m) ?></div><?php endforeach; unset($_SESSION['admin_flash']);
}
function layout_end(){ echo '</main></body></html>'; }

function login_view(){
  global $AUTH;
  $setup = empty($AUTH['hash']);
  ?><div class="card" style="max-width:420px;margin:40px auto">
  <h1><?= $setup ? 'Create your admin sign-in' : 'Sign in' ?></h1>
  <?php if($setup): ?><p class="mut">The first person to open this page sets the sign-in, so do it now. Choose a password of at least 10 characters.</p><?php endif; ?>
  <form method="post"><input type="hidden" name="act" value="<?= $setup ? 'setup' : 'login' ?>">
    <label>Username<input type="text" name="user" autocomplete="username" required></label>
    <label>Password<input type="password" name="pass" autocomplete="<?= $setup ? 'new-password' : 'current-password' ?>" required></label>
    <?php if($setup): ?><label>Password again<input type="password" name="pass2" autocomplete="new-password" required></label><?php endif; ?>
    <button class="btn"><?= $setup ? 'Create sign-in' : 'Sign in' ?></button></form></div><?php
}

function badge($status, $labels){ return '<span class="badge b-'.h($status).'">'.h($labels[$status] ?? $status).'</span>'; }
function form_start($act, $multipart=false){ return '<form method="post"'.($multipart ? ' enctype="multipart/form-data"' : '').'>'.csrf_field().'<input type="hidden" name="act" value="'.h($act).'">'; }
function del_button($act, $fields, $label='Delete', $confirm='Delete this for good?'){
  $o = '<form method="post" onsubmit="return confirm(\''.h($confirm).'\')" style="display:inline">'.csrf_field().'<input type="hidden" name="act" value="'.h($act).'">';
  foreach($fields as $k=>$v) $o .= '<input type="hidden" name="'.h($k).'" value="'.h($v).'">';
  return $o.'<button class="btn btn-d btn-s">'.h($label).'</button></form>';
}
function photos_editor($photos, $label='Photos'){
  $o = '<h2>'.h($label).'</h2>';
  if($photos){
    $o .= '<div class="photos">';
    foreach($photos as $i=>$f) $o .= '<figure><img src="'.h(thumb($f, 320)).'" alt=""><label class="cb"><input type="radio" name="main_photo" value="'.h($f).'"'.($i === 0 ? ' checked' : '').'> Main photo</label><label class="cb"><input type="checkbox" name="remove_photo[]" value="'.h($f).'"> Remove</label></figure>';
    $o .= '</div>';
  }
  return $o.'<label>Add photos <small>JPG, PNG or WebP. Choose several at once. Large phone photos are resized automatically. Your own photos rank and convert far better than stock photos.</small><input type="file" name="photos[]" accept="image/jpeg,image/png,image/webp" multiple></label>';
}
function one_photo_editor($field, $current, $label, $help=''){
  $o = '<label>'.h($label).($help ? '<small>'.h($help).'</small>' : '').'<input type="file" name="'.h($field).'" accept="image/jpeg,image/png,image/webp"></label>';
  if($current !== '') $o .= '<div class="photos"><figure><img src="'.h(thumb($current, 320)).'" alt=""><label class="cb"><input type="checkbox" name="remove_'.h($field).'" value="1"> Remove</label></figure></div>';
  return $o;
}
function parent_options($role, $sel){
  global $SITE;
  $o = '<option value="">— not listed —</option>';
  foreach($SITE['parents'] as $p) if($p['role'] === $role) $o .= '<option value="'.h($p['id']).'"'.($p['id'] === $sel ? ' selected' : '').'>'.h($p['name']).'</option>';
  return $o;
}

function view_dashboard(){
  global $SITE, $CFG;
  $inq = inquiries_all(); $new = array_filter($inq, fn($q)=>$q['status'] === 'new');
  $avail = array_filter($SITE['puppies'], fn($p)=>$p['status'] === 'available' && empty($p['hidden']));
  $nophoto = array_filter($SITE['puppies'], fn($p)=>empty($p['photos']) && empty($p['hidden']) && $p['status'] !== 'sold');
  $checks = [
    [$CFG['phone'] !== '', 'Add your phone number (turns on the Call and Text buttons)', 'settings', 'business'],
    [$CFG['registry'] !== '', 'Add your registry (e.g. AKC) so papers show the registry name', 'settings', 'business'],
    [$CFG['payee_name'] !== '', 'Add the name on your payment accounts (shown on “Verify us”)', 'settings', 'business'],
    [$CFG['breeder_name'] !== '' && $CFG['breeder_story'] !== '', 'Write your story and add your photo for the About page', 'settings', 'about'],
    [$CFG['license_text'] !== '', 'Add any breeder license or registration numbers you hold', 'settings', 'business'],
    [count($SITE['parents']) > 0, 'Add your adult beagles (the parents) with their health testing', 'parents', ''],
    [count($SITE['puppies']) > 0, 'Add your first puppy', 'puppy', ''],
    [!$nophoto, 'Add photos to every listed puppy', 'puppies', ''],
    [$CFG['hero_photo'] !== '', 'Upload a home page photo', 'settings', 'look'],
    [$CFG['smtp_host'] !== '', 'Set up email sending (SMTP) and send a test email', 'settings', 'alerts'],
    [$CFG['alert_phone'] !== '' || $CFG['alert_sms_email'] !== '', 'Turn on text alerts for new inquiries', 'settings', 'alerts'],
    [$CFG['gsc_verification'] !== '', 'Verify the site in Google Search Console and submit the sitemap', 'settings', 'seo'],
    [!empty($CFG['pretty_urls']), 'Clean page addresses (switch on automatically on most hosts)', 'settings', 'seo'],
  ];
  ?><h1>Dashboard</h1>
  <div class="grid g4">
    <div class="card"><div class="stat"><?= count($new) ?></div>new inquiries · <a href="<?= h(self_url('inquiries')) ?>">open</a></div>
    <div class="card"><div class="stat"><?= count($avail) ?></div>puppies available · <a href="<?= h(self_url('puppy')) ?>">add a puppy</a></div>
    <div class="card"><div class="stat"><?= count(waitlist()) ?></div>on the waitlist · <a href="<?= h(self_url('waitlist')) ?>">email them</a></div>
    <div class="card"><div class="stat"><?= count($SITE['reviews']) ?></div>reviews · <a href="<?= h(self_url('review')) ?>">add a review</a></div>
  </div>
  <div class="grid g2">
    <div class="card"><h2 style="margin-top:0">Latest inquiries</h2>
      <?php if(!$inq): ?><p class="mut">None yet. They appear here and are emailed to <?= h($CFG['email']) ?>.</p><?php endif; ?>
      <table><?php foreach(array_slice($inq, 0, 8) as $q): ?><tr><td><a href="<?= h(self_url('inquiry', ['ref'=>$q['ref']])) ?>"><?= h($q['name']) ?></a><br><span class="mut"><?= h($q['puppy_name'] ?: 'Message') ?> · <?= h(date('M j, g:ia', strtotime($q['time']))) ?></span></td><td><?= badge($q['status'], INQUIRY_STATUSES) ?></td></tr><?php endforeach; ?></table></div>
    <div class="card"><h2 style="margin-top:0">Launch checklist</h2><ul class="chk">
      <?php foreach($checks as [$ok, $label, $view, $tab]): ?><li class="<?= $ok ? 'done' : 'todo' ?>"><?= $ok ? '✓' : '○' ?> <?= $ok ? h($label) : '<a href="'.h(self_url($view, $tab ? ['tab'=>$tab] : [])).'">'.h($label).'</a>' ?></li><?php endforeach; ?>
    </ul><p class="mut">Sitemap for Google: <a href="<?= h(site_url('sitemap.xml')) ?>" target="_blank"><?= h(rtrim($CFG['domain'], '/')) ?>/sitemap.xml</a></p></div>
  </div>
  <?php if(empty($CFG['pretty_urls']) && empty($CFG['pretty_auto'])): ?><script>fetch('rewrite-check',{cache:'no-store'}).then(r=>r.ok&&r.json()).then(j=>{if(j&&j.clean_urls)location.reload()}).catch(()=>{});</script><?php endif;
}

function view_puppies(){
  global $SITE;
  ?><div class="row"><h1>Puppies</h1><a class="btn right" href="<?= h(self_url('puppy')) ?>">+ Add a puppy</a></div>
  <p class="mut">Restock by adding new puppies here. Mark them Reserved when a deposit arrives and Sold after delivery — sold puppies stay on the site as “gone to loving homes”, which builds trust.</p>
  <div class="tw"><table><tr><th></th><th>Puppy</th><th>Price</th><th>Born</th><th>Status</th><th>Quick change</th></tr>
  <?php foreach($SITE['puppies'] as $p): ?><tr>
    <td style="width:76px"><?php if(!empty($p['photos'][0])): ?><img src="<?= h(thumb($p['photos'][0], 160)) ?>" alt="" style="width:64px;height:50px;object-fit:cover;border-radius:6px"><?php endif; ?></td>
    <td><a href="<?= h(self_url('puppy', ['id'=>$p['id']])) ?>"><strong><?= h($p['name']) ?></strong></a> <?= !empty($p['hidden']) ? '<span class="badge">Hidden</span>' : '' ?><br><span class="mut"><?= h($p['color'].' · '.ucfirst($p['sex'])) ?></span></td>
    <td><?= $p['price'] !== '' ? h(money($p['price'])) : '—' ?></td><td><?= h($p['dob'] ? date('M j, Y', strtotime($p['dob'])) : '—') ?></td><td><?= badge($p['status'], PUPPY_STATUSES) ?></td>
    <td><?= form_start('puppy_status') ?><input type="hidden" name="id" value="<?= h($p['id']) ?>"><select name="status" onchange="this.form.submit()" style="width:auto"><?php foreach(PUPPY_STATUSES as $k=>$l): ?><option value="<?= $k ?>"<?= $p['status'] === $k ? ' selected' : '' ?>><?= h($l) ?></option><?php endforeach; ?></select></form></td>
  </tr><?php endforeach; ?>
  <?php if(!$SITE['puppies']): ?><tr><td colspan="6" class="mut">No puppies yet. <a href="<?= h(self_url('puppy')) ?>">Add your first puppy</a>.</td></tr><?php endif; ?>
  </table></div><?php
}

function view_puppy(){
  global $SITE, $CFG;
  $p = find_by($SITE['puppies'], 'id', (string)($_GET['id'] ?? '')) ?? [];
  $new = !$p;
  $p += ['id'=>'', 'name'=>'', 'status'=>'available', 'sex'=>'male', 'color'=>'', 'dob'=>'', 'ready'=>'', 'price'=>'', 'size'=>'', 'weight'=>'', 'registry'=>'', 'reg_no'=>'',
         'sire'=>'', 'dam'=>'', 'microchip'=>true, 'vet_date'=>'', 'vaccines'=>'', 'tagline'=>'', 'description'=>'', 'video'=>'', 'hidden'=>false, 'seo_title'=>'', 'seo_desc'=>'', 'photos'=>[], 'slug'=>''];
  ?><div class="row"><h1><?= $new ? 'Add a puppy' : h($p['name']) ?></h1><?php if(!$new): ?><a class="btn btn-g right" target="_blank" href="<?= h(site_url('beagle-puppies-for-sale/'.$p['slug'])) ?>">View on site ↗</a><?php endif; ?></div>
  <?= form_start('save_puppy', true) ?><input type="hidden" name="id" value="<?= h($p['id']) ?>">
  <div class="card"><h2 style="margin-top:0">The basics</h2><div class="grid g3">
    <label>Name *<input type="text" name="name" value="<?= h($p['name']) ?>" required></label>
    <label>Sex<select name="sex"><option value="male"<?= $p['sex'] === 'male' ? ' selected' : '' ?>>Male</option><option value="female"<?= $p['sex'] === 'female' ? ' selected' : '' ?>>Female</option></select></label>
    <label>Color and markings<small>e.g. Tricolor, Lemon &amp; White, Red &amp; White</small><input type="text" name="color" value="<?= h($p['color']) ?>"></label>
    <label>Price (USD)<small>Deposit (<?= (int)$CFG['deposit_pct'] ?>%) is worked out from this</small><input type="text" name="price" value="<?= h($p['price']) ?>" inputmode="decimal"></label>
    <label>Status<select name="status"><?php foreach(PUPPY_STATUSES as $k=>$l): ?><option value="<?= $k ?>"<?= $p['status'] === $k ? ' selected' : '' ?>><?= h($l) ?></option><?php endforeach; ?></select></label>
    <label>Expected adult size<select name="size"><option value="">Not sure yet</option><option value="13"<?= $p['size'] === '13' ? ' selected' : '' ?>>13-inch (under 20 lb)</option><option value="15"<?= $p['size'] === '15' ? ' selected' : '' ?>>15-inch (20–30 lb)</option></select></label>
    <label>Date of birth<input type="date" name="dob" value="<?= h($p['dob']) ?>"></label>
    <label>Ready for delivery<small>Leave empty if ready now</small><input type="date" name="ready" value="<?= h($p['ready']) ?>"></label>
    <label>Current weight<small>optional, e.g. 4.2 lb at 8 weeks</small><input type="text" name="weight" value="<?= h($p['weight']) ?>"></label>
  </div>
  <label>One-line summary<small>Shown under the name, e.g. “Gentle, cuddly lemon girl who loves kids”</small><input type="text" name="tagline" value="<?= h($p['tagline']) ?>"></label></div>

  <div class="card"><h2 style="margin-top:0">Registration and health</h2><div class="grid g3">
    <label>Registry<small>Leave empty to use <?= h($CFG['registry'] ?: 'your registry from Settings') ?></small><input type="text" name="registry" value="<?= h($p['registry']) ?>"></label>
    <label>Registration / litter number<input type="text" name="reg_no" value="<?= h($p['reg_no']) ?>"></label>
    <label>Vet check date<input type="date" name="vet_date" value="<?= h($p['vet_date']) ?>"></label>
    <label>Dam (mom)<select name="dam"><?= parent_options('dam', $p['dam']) ?></select></label>
    <label>Sire (dad)<select name="sire"><?= parent_options('sire', $p['sire']) ?></select></label>
    <label class="cb" style="margin-top:28px"><input type="checkbox" name="microchip" value="1"<?= $p['microchip'] ? ' checked' : '' ?>> Microchipped</label>
  </div>
  <label>Shots and deworming given<small>One per line, e.g. “DHPP #1 — 6 weeks (Sept 3)”</small><textarea name="vaccines"><?= h($p['vaccines']) ?></textarea></label>
  <?php if(!$SITE['parents']): ?><p class="help">Add the parents under <a href="<?= h(self_url('parent')) ?>">Parents</a> first and they’ll appear here, with their own photos and health testing on the puppy’s page.</p><?php endif; ?></div>

  <div class="card"><h2 style="margin-top:0">Description</h2>
    <label>About this puppy<small>Personality, how they play, what kind of home suits them. Original, specific detail helps both buyers and Google. <?= h('**bold**, "- " bullets, blank line = new paragraph.') ?></small><textarea name="description" rows="8"><?= h($p['description']) ?></textarea></label>
    <label>Video link<small>A YouTube link shows the video on the page; another https:// link shows a “Watch video” button</small><input type="url" name="video" value="<?= h($p['video']) ?>"></label>
    <?= photos_editor($p['photos']) ?></div>

  <div class="card"><details><summary>Search engine &amp; address (optional)</summary>
    <label>Page address<small>beagle-puppies-for-sale/<b>…</b> — made from the name, color and sex if left empty</small><input type="text" name="slug" value="<?= h($p['slug']) ?>"></label>
    <label>Title for Google<small>Leave empty for “<?= h(($p['name'] ?: 'Name').': Color Sex Beagle Puppy for Sale | '.$CFG['brand']) ?>”</small><input type="text" name="seo_title" value="<?= h($p['seo_title']) ?>"></label>
    <label>Description for Google<small>Leave empty to write one from the puppy’s details</small><textarea name="seo_desc" rows="2"><?= h($p['seo_desc']) ?></textarea></label>
  </details>
  <label class="cb"><input type="checkbox" name="hidden" value="1"<?= $p['hidden'] ? ' checked' : '' ?>> Hide from the website</label>
  <?php if(waitlist()): ?><label class="cb"><input type="checkbox" name="notify" value="1"> Email this puppy to the <?= count(waitlist()) ?> people on the waitlist when I save</label><?php endif; ?>
  <button class="btn"><?= $new ? 'Add puppy' : 'Save puppy' ?></button></div></form>
  <?php if(!$new) echo '<p>'.del_button('delete_puppy', ['id'=>$p['id']], 'Delete puppy', 'Delete '.$p['name'].' and their photos? To keep them as “gone home”, mark them Sold instead.').'</p>';
}

function view_parents(){
  global $SITE;
  ?><div class="row"><h1>Parents</h1><a class="btn right" href="<?= h(self_url('parent')) ?>">+ Add a parent</a></div>
  <p class="mut">Your adult beagles appear on the “Our beagles” page and on each of their puppies’ pages. Registration and health testing here are the strongest trust signals on the site.</p>
  <div class="tw"><table><tr><th></th><th>Name</th><th>Role</th><th>Registration</th><th></th></tr>
  <?php foreach($SITE['parents'] as $p): ?><tr><td style="width:76px"><?php if(!empty($p['photos'][0])): ?><img src="<?= h(thumb($p['photos'][0], 160)) ?>" alt="" style="width:64px;height:50px;object-fit:cover;border-radius:6px"><?php endif; ?></td>
    <td><a href="<?= h(self_url('parent', ['id'=>$p['id']])) ?>"><strong><?= h($p['name']) ?></strong></a><br><span class="mut"><?= h($p['registered_name']) ?></span></td>
    <td><?= $p['role'] === 'sire' ? 'Sire' : 'Dam' ?><?= !empty($p['retired']) ? ' · retired' : '' ?></td><td><?= h(trim($p['registry'].' '.$p['reg_no'])) ?></td><td><?= !empty($p['hidden']) ? '<span class="badge">Hidden</span>' : '' ?></td></tr><?php endforeach; ?>
  <?php if(!$SITE['parents']): ?><tr><td colspan="5" class="mut">No parents yet.</td></tr><?php endif; ?></table></div><?php
}

function view_parent(){
  global $SITE, $CFG;
  $p = find_by($SITE['parents'], 'id', (string)($_GET['id'] ?? '')) ?? [];
  $new = !$p;
  $p += ['id'=>'', 'name'=>'', 'role'=>'dam', 'registered_name'=>'', 'registry'=>$CFG['registry'], 'reg_no'=>'', 'dob'=>'', 'color'=>'', 'height'=>'', 'weight'=>'', 'health'=>'', 'description'=>'', 'retired'=>false, 'hidden'=>false, 'photos'=>[]];
  ?><h1><?= $new ? 'Add a parent' : h($p['name']) ?></h1>
  <?= form_start('save_parent', true) ?><input type="hidden" name="id" value="<?= h($p['id']) ?>">
  <div class="card"><div class="grid g3">
    <label>Call name *<input type="text" name="name" value="<?= h($p['name']) ?>" required></label>
    <label>Role<select name="role"><option value="dam"<?= $p['role'] === 'dam' ? ' selected' : '' ?>>Dam (mom)</option><option value="sire"<?= $p['role'] === 'sire' ? ' selected' : '' ?>>Sire (dad)</option></select></label>
    <label>Registered name<input type="text" name="registered_name" value="<?= h($p['registered_name']) ?>"></label>
    <label>Registry<input type="text" name="registry" value="<?= h($p['registry']) ?>"></label>
    <label>Registration number<input type="text" name="reg_no" value="<?= h($p['reg_no']) ?>"></label>
    <label>Date of birth<input type="date" name="dob" value="<?= h($p['dob']) ?>"></label>
    <label>Color<input type="text" name="color" value="<?= h($p['color']) ?>"></label>
    <label>Height<small>e.g. 14 in</small><input type="text" name="height" value="<?= h($p['height']) ?>"></label>
    <label>Weight<small>e.g. 24 lb</small><input type="text" name="weight" value="<?= h($p['weight']) ?>"></label>
  </div>
  <label>Health testing<small>One per line, exactly as on the certificates, e.g. “MLS: Clear (Embark)”, “Hips: OFA Good”, “Eyes: CAER normal 2026”</small><textarea name="health" rows="5"><?= h($p['health']) ?></textarea></label>
  <label>About <?= h($p['name'] ?: 'this dog') ?><small>Temperament, what their puppies are like</small><textarea name="description" rows="5"><?= h($p['description']) ?></textarea></label>
  <?= photos_editor($p['photos']) ?>
  <label class="cb"><input type="checkbox" name="retired" value="1"<?= $p['retired'] ? ' checked' : '' ?>> Retired from breeding</label>
  <label class="cb"><input type="checkbox" name="hidden" value="1"<?= $p['hidden'] ? ' checked' : '' ?>> Hide from the website</label>
  <button class="btn">Save</button></div></form>
  <?php if(!$new) echo '<p>'.del_button('delete_parent', ['id'=>$p['id']], 'Delete', 'Delete '.$p['name'].'?').'</p>';
}

function view_litters(){
  global $SITE;
  $edit = find_by($SITE['litters'], 'id', (string)($_GET['id'] ?? '')) ?? ['id'=>'', 'title'=>'', 'sire'=>'', 'dam'=>'', 'status'=>'planned', 'date'=>'', 'price'=>'', 'notes'=>''];
  ?><h1>Litters</h1><p class="mut">Planned and newborn litters show on the Upcoming litters page, next to the waitlist sign-up. When puppies are ready, add each one under Puppies.</p>
  <div class="tw"><table><tr><th>Litter</th><th>Status</th><th>Date</th><th></th></tr>
  <?php foreach($SITE['litters'] as $l): $d = find_by($SITE['parents'], 'id', $l['dam']); $s = find_by($SITE['parents'], 'id', $l['sire']); ?>
    <tr><td><strong><?= h($l['title'] ?: trim(($d['name'] ?? '?').' × '.($s['name'] ?? '?'))) ?></strong></td><td><?= badge($l['status'], LITTER_STATUSES) ?></td><td><?= h($l['date']) ?></td>
    <td class="row"><a class="btn btn-g btn-s" href="<?= h(self_url('litters', ['id'=>$l['id']])) ?>">Edit</a> <a class="btn btn-g btn-s" href="<?= h(self_url('waitlist', ['litter'=>$l['id']])) ?>">Email waitlist</a> <?= del_button('delete_litter', ['id'=>$l['id']]) ?></td></tr><?php endforeach; ?>
  <?php if(!$SITE['litters']): ?><tr><td colspan="4" class="mut">No litters yet.</td></tr><?php endif; ?></table></div>
  <div class="card" style="margin-top:16px"><h2 style="margin-top:0"><?= $edit['id'] ? 'Edit litter' : 'Add a litter' ?></h2>
  <?= form_start('save_litter') ?><input type="hidden" name="id" value="<?= h($edit['id']) ?>"><div class="grid g3">
    <label>Dam (mom)<select name="dam"><?= parent_options('dam', $edit['dam']) ?></select></label>
    <label>Sire (dad)<select name="sire"><?= parent_options('sire', $edit['sire']) ?></select></label>
    <label>Status<select name="status"><?php foreach(LITTER_STATUSES as $k=>$lb): ?><option value="<?= $k ?>"<?= $edit['status'] === $k ? ' selected' : '' ?>><?= h($lb) ?></option><?php endforeach; ?></select></label>
    <label>Title<small>optional — e.g. “Spring 2027 lemon litter”</small><input type="text" name="title" value="<?= h($edit['title']) ?>"></label>
    <label>Born / expected date<input type="date" name="date" value="<?= h($edit['date']) ?>"></label>
    <label>Expected price<small>optional, e.g. “$1,500”</small><input type="text" name="price" value="<?= h($edit['price']) ?>"></label></div>
  <label>Notes<small>Colors expected, number of puppies, when they’ll be ready</small><textarea name="notes"><?= h($edit['notes']) ?></textarea></label>
  <button class="btn">Save litter</button></form></div><?php
}

function view_inquiries(){
  $all = inquiries_all(); $f = (string)($_GET['status'] ?? '');
  $list = $f !== '' ? array_filter($all, fn($q)=>$q['status'] === $f) : $all;
  ?><div class="row"><h1>Inquiries</h1><a class="btn btn-g right" href="<?= h(self_url('export', ['what'=>'inquiries'])) ?>">Download CSV</a></div>
  <p class="mut">Every puppy inquiry and contact message. Each is also emailed to you (and texted, if text alerts are on). Reply fast — the first breeder to answer usually gets the family.</p>
  <div class="tabs"><a class="<?= $f === '' ? 'on' : '' ?>" href="<?= h(self_url('inquiries')) ?>">All (<?= count($all) ?>)</a><?php foreach(INQUIRY_STATUSES as $k=>$l): $n = count(array_filter($all, fn($q)=>$q['status'] === $k)); ?><a class="<?= $f === $k ? 'on' : '' ?>" href="<?= h(self_url('inquiries', ['status'=>$k])) ?>"><?= h($l) ?> (<?= $n ?>)</a><?php endforeach; ?></div>
  <div class="tw"><table><tr><th>Received</th><th>From</th><th>Puppy</th><th>Delivery to</th><th>Status</th></tr>
  <?php foreach($list as $q): ?><tr><td><?= h(date('M j, g:ia', strtotime($q['time']))) ?><br><span class="mut"><?= h($q['ref']) ?></span></td>
    <td><a href="<?= h(self_url('inquiry', ['ref'=>$q['ref']])) ?>"><strong><?= h($q['name']) ?></strong></a><br><span class="mut"><?= h($q['phone'] ?: $q['email']) ?></span></td>
    <td><?= h($q['puppy_name'] ?: '— message —') ?></td><td><?= h(trim(($q['city'] ?? '').', '.($q['state'] ?? ''), ', ')) ?></td><td><?= badge($q['status'], INQUIRY_STATUSES) ?></td></tr><?php endforeach; ?>
  <?php if(!$list): ?><tr><td colspan="5" class="mut">Nothing here yet.</td></tr><?php endif; ?></table></div><?php
}

function view_inquiry(){
  global $SITE, $CFG;
  $ref = (string)($_GET['ref'] ?? '');
  $q = valid_ref($ref) ? data_read('inquiries/'.$ref) : null;
  if(!$q){ echo '<p>Inquiry not found. <a href="'.h(self_url('inquiries')).'">Back</a></p>'; return; }
  $p = $q['puppy_id'] ? find_by($SITE['puppies'], 'id', $q['puppy_id']) : null;
  $tel = preg_replace('/[^\d+]/', '', $q['phone']);
  ?><div class="row"><h1><?= h($q['name']) ?></h1><span class="right"><?= badge($q['status'], INQUIRY_STATUSES) ?></span></div>
  <p class="mut"><?= h($q['kind'] === 'inquiry' ? 'Puppy inquiry' : 'Contact message') ?> · <?= h($q['ref']) ?> · <?= h(date('l, F j, Y \a\t g:ia', strtotime($q['time']))) ?></p>
  <div class="grid g2"><div class="card">
    <h2 style="margin-top:0">Contact</h2>
    <p><a href="mailto:<?= h($q['email']) ?>?subject=<?= rawurlencode(($q['puppy_name'] ? 'Your inquiry for '.$q['puppy_name'] : 'Your message').' — '.$CFG['brand']) ?>"><?= h($q['email']) ?></a><br>
      <?php if($q['phone'] !== ''): ?><a href="tel:<?= h($tel) ?>"><?= h($q['phone']) ?></a> · <a href="sms:<?= h($tel) ?>">Text</a><?php endif; ?></p>
    <?php if($q['kind'] === 'inquiry'): ?>
      <p><strong>Delivery address</strong><br><?= h($q['address1']) ?><?= $q['address2'] !== '' ? '<br>'.h($q['address2']) : '' ?><br><?= h($q['city'].', '.$q['state'].' '.$q['zip']) ?></p>
      <?php if(($q['delivery_name'] ?? '') !== ''): ?><p><strong>Delivery:</strong> <?= h($q['delivery_name']) ?> — <?= $q['delivery_fee'] !== '' ? h(money($q['delivery_fee'])) : 'to be quoted' ?></p><?php endif; ?>
      <p><strong>Preferred payment:</strong> <?= h($q['payment_name']) ?><br><strong>Delivery timing:</strong> <?= h($q['timing'] ?: '—') ?><br><strong>Best way to reach them:</strong> <?= h($q['contact_pref'] ?: 'Any') ?></p>
    <?php endif; ?>
    <?php if($q['notes'] !== ''): ?><h2><?= $q['kind'] === 'inquiry' ? 'Their notes' : 'Message' ?></h2><p style="white-space:pre-wrap"><?= h($q['notes']) ?></p><?php endif; ?>
  </div><div>
    <?php if($p): ?><div class="card"><h2 style="margin-top:0">Puppy: <a href="<?= h(self_url('puppy', ['id'=>$p['id']])) ?>"><?= h($p['name']) ?></a></h2>
      <p><?= badge($p['status'], PUPPY_STATUSES) ?> · <?= $p['price'] !== '' ? h(money($p['price'])).' · deposit '.h(money(round((float)$p['price'] * (int)$CFG['deposit_pct'] / 100, 2))) : '' ?></p>
      <?= form_start('puppy_status') ?><input type="hidden" name="id" value="<?= h($p['id']) ?>"><input type="hidden" name="back" value="inquiry"><input type="hidden" name="ref" value="<?= h($q['ref']) ?>">
        <div class="row"><select name="status" style="width:auto"><?php foreach(PUPPY_STATUSES as $k=>$l): ?><option value="<?= $k ?>"<?= $p['status'] === $k ? ' selected' : '' ?>><?= h($l) ?></option><?php endforeach; ?></select><button class="btn btn-g btn-s">Update puppy</button></div></form></div>
    <?php elseif($q['puppy_name']): ?><div class="card"><p>Puppy: <?= h($q['puppy_name']) ?> (no longer listed)</p></div><?php endif; ?>
    <div class="card"><?= form_start('save_inquiry') ?><input type="hidden" name="ref" value="<?= h($q['ref']) ?>">
      <label>Status<select name="status"><?php foreach(INQUIRY_STATUSES as $k=>$l): ?><option value="<?= $k ?>"<?= $q['status'] === $k ? ' selected' : '' ?>><?= h($l) ?></option><?php endforeach; ?></select></label>
      <label>Your private notes<small>Deposit received, video call date, delivery plans…</small><textarea name="admin_notes" rows="6"><?= h($q['admin_notes'] ?? '') ?></textarea></label>
      <button class="btn">Save</button></form></div>
    <p><?= del_button('delete_inquiry', ['ref'=>$q['ref']], 'Delete inquiry') ?></p>
  </div></div><?php
}

function view_waitlist(){
  global $SITE, $CFG;
  $list = waitlist();
  $sub = ''; $body = '';
  if(($l = find_by($SITE['litters'], 'id', (string)($_GET['litter'] ?? '')))){
    $d = find_by($SITE['parents'], 'id', $l['dam']); $s = find_by($SITE['parents'], 'id', $l['sire']);
    $name = $l['title'] ?: trim(($d['name'] ?? 'Our').' × '.($s['name'] ?? 'litter'));
    $sub = $l['status'] === 'born' ? "Puppies are here: $name" : "Coming soon: $name";
    $body = ($l['status'] === 'born' ? "Our new litter has arrived! $name was born".($l['date'] ? ' on '.nice_date($l['date']) : '').'.' : "We have a litter on the way: $name".($l['date'] ? ', expected '.date('F Y', strtotime($l['date'])) : '').'.')
      ."\n\n".trim(plain($l['notes'], 2000))."\n\nAs a waitlist family you hear first. Reply to this email to tell us you're interested, or see the litter: ".live_url('upcoming-litters');
  }
  ?><div class="row"><h1>Waitlist</h1><a class="btn btn-g right" href="<?= h(self_url('export', ['what'=>'waitlist'])) ?>">Download CSV</a></div>
  <div class="card"><h2 style="margin-top:0">Email everyone on the waitlist (<?= count($list) ?>)</h2>
  <p class="mut">Each email starts “Hi [first name],” and ends with your contact details and an unsubscribe link. Tip: when you add a puppy, tick “Email this puppy to the waitlist”, or use “Email waitlist” next to a litter.</p>
  <?= form_start('waitlist_send') ?><label>Subject<input type="text" name="subject" value="<?= h($sub) ?>"></label>
  <label>Message<textarea name="body" rows="8"><?= h($body) ?></textarea></label><button class="btn" onclick="return confirm('Send this to everyone on the waitlist?')">Send to <?= count($list) ?> <?= count($list) === 1 ? 'person' : 'people' ?></button></form></div>
  <div class="tw"><table><tr><th>Joined</th><th>Name</th><th>Contact</th><th>Wants</th><th></th></tr>
  <?php foreach(array_reverse($list) as $w): ?><tr><td><?= h(date('M j, Y', strtotime($w['time']))) ?></td><td><strong><?= h($w['name']) ?></strong><br><span class="mut"><?= h($w['state'] ? US_STATES[$w['state']] ?? $w['state'] : '') ?></span></td>
    <td><a href="mailto:<?= h($w['email']) ?>"><?= h($w['email']) ?></a><br><?= h($w['phone']) ?></td><td><?= h(trim($w['sex'].' · '.($w['color'] ?: 'any color').' · '.($w['timing'] ?: ''), ' ·')) ?><?= $w['notes'] !== '' ? '<br><span class="mut">'.h($w['notes']).'</span>' : '' ?></td>
    <td><?= del_button('waitlist_remove', ['email'=>$w['email']], 'Remove', 'Remove '.$w['email'].' from the waitlist?') ?></td></tr><?php endforeach; ?>
  <?php if(!$list): ?><tr><td colspan="5" class="mut">Nobody yet. The sign-up form is on the Upcoming litters page.</td></tr><?php endif; ?></table></div><?php
}

function view_reviews(){
  global $SITE;
  ?><div class="row"><h1>Reviews</h1><a class="btn right" href="<?= h(self_url('review')) ?>">+ Add a review</a></div>
  <p class="mut">Add reviews your customers send you, word for word and with their permission. Never write or edit reviews yourself: fake reviews break FTC rules and Google’s policies, and buyers can tell.</p>
  <div class="tw"><table><tr><th>Family</th><th>Puppy</th><th>Date</th><th>Stars</th><th></th></tr>
  <?php foreach($SITE['reviews'] as $r): ?><tr><td><a href="<?= h(self_url('review', ['id'=>$r['id']])) ?>"><strong><?= h($r['name']) ?></strong></a><br><span class="mut"><?= h($r['location']) ?></span></td><td><?= h($r['puppy']) ?></td><td><?= h($r['date']) ?></td><td><?= str_repeat('★', (int)$r['rating']) ?></td><td><?= !empty($r['hidden']) ? '<span class="badge">Hidden</span>' : '' ?></td></tr><?php endforeach; ?>
  <?php if(!$SITE['reviews']): ?><tr><td colspan="5" class="mut">No reviews yet. The Reviews page shows a friendly message until you add the first one.</td></tr><?php endif; ?></table></div><?php
}

function view_review(){
  global $SITE;
  $r = find_by($SITE['reviews'], 'id', (string)($_GET['id'] ?? '')) ?? [];
  $new = !$r;
  $r += ['id'=>'', 'name'=>'', 'location'=>'', 'date'=>date('Y-m-d'), 'puppy'=>'', 'rating'=>5, 'text'=>'', 'photo'=>'', 'hidden'=>false];
  ?><h1><?= $new ? 'Add a review' : 'Edit review' ?></h1>
  <?= form_start('save_review', true) ?><input type="hidden" name="id" value="<?= h($r['id']) ?>"><div class="card"><div class="grid g3">
    <label>Family name *<small>As they want it shown, e.g. “Sarah M.”</small><input type="text" name="name" value="<?= h($r['name']) ?>" required></label>
    <label>Location<small>e.g. Columbus, OH</small><input type="text" name="location" value="<?= h($r['location']) ?>"></label>
    <label>Puppy’s name<input type="text" name="puppy" value="<?= h($r['puppy']) ?>"></label>
    <label>Date<input type="date" name="date" value="<?= h($r['date']) ?>"></label>
    <label>Stars<select name="rating"><?php for($i = 5; $i >= 1; $i--): ?><option<?= (int)$r['rating'] === $i ? ' selected' : '' ?>><?= $i ?></option><?php endfor; ?></select></label></div>
  <label>Their review *<textarea name="text" rows="6" required><?= h($r['text']) ?></textarea></label>
  <?= one_photo_editor('photo', $r['photo'], 'Photo', 'A photo of the puppy in their new home (with permission)') ?>
  <label class="cb"><input type="checkbox" name="hidden" value="1"<?= $r['hidden'] ? ' checked' : '' ?>> Hide from the website</label>
  <button class="btn">Save review</button></div></form>
  <?php if(!$new) echo '<p>'.del_button('delete_review', ['id'=>$r['id']], 'Delete review').'</p>';
}

function view_articles(){
  global $SITE;
  ?><div class="row"><h1>Guides &amp; blog</h1><a class="btn right" href="<?= h(self_url('article', ['type'=>'blog'])) ?>">+ New blog post</a><a class="btn btn-g" href="<?= h(self_url('article', ['type'=>'guide'])) ?>">+ New guide</a></div>
  <p class="mut">Guides answer the questions buyers search for; blog posts are news and stories. Each one targets a group of searches — edit freely, and add your own photos and experience: first-hand detail is what makes Google (and buyers) trust a page.</p>
  <div class="tw"><table><tr><th>Title</th><th>Type</th><th>Group</th><th>Words</th><th>Updated</th></tr>
  <?php foreach($SITE['articles'] as $a): ?><tr><td><a href="<?= h(self_url('article', ['slug'=>$a['slug']])) ?>"><strong><?= h($a['title']) ?></strong></a><?= !empty($a['draft']) ? ' <span class="badge">Draft</span>' : '' ?><br><span class="mut">/<?= h(($a['type'] === 'blog' ? 'blog/' : 'guides/').$a['slug']) ?></span></td>
    <td><?= $a['type'] === 'blog' ? 'Blog' : 'Guide' ?></td><td><?= h(ARTICLE_GROUPS[$a['group']] ?? $a['group']) ?></td><td><?= ($a['lang'] ?? 'en') === 'en' ? word_count($a['body']) : mb_strlen(plain($a['body'], 100000)).' chars' ?></td><td><?= h($a['updated'] ?? $a['date']) ?></td></tr><?php endforeach; ?></table></div><?php
}

function view_article(){
  global $SITE;
  $a = null; foreach($SITE['articles'] as $x){ if($x['slug'] === (string)($_GET['slug'] ?? '')){ $a = $x; break; } }
  $new = !$a;
  $a = ($a ?? []) + ['slug'=>'', 'type'=>($_GET['type'] ?? '') === 'blog' ? 'blog' : 'guide', 'group'=>'buying', 'title'=>'', 'seo_title'=>'', 'seo_desc'=>'', 'excerpt'=>'', 'body'=>'', 'image'=>'', 'date'=>date('Y-m-d'), 'draft'=>false];
  ?><div class="row"><h1><?= $new ? 'New '.($a['type'] === 'blog' ? 'blog post' : 'guide') : h($a['title']) ?></h1><?php if(!$new && empty($a['draft'])): ?><a class="btn btn-g right" target="_blank" href="<?= h(site_url(($a['type'] === 'blog' ? 'blog/' : 'guides/').$a['slug'])) ?>">View on site ↗</a><?php endif; ?></div>
  <?= form_start('save_article', true) ?><input type="hidden" name="orig" value="<?= h($a['slug']) ?>"><div class="card">
  <label>Title (main heading) *<input type="text" name="title" value="<?= h($a['title']) ?>" required></label>
  <div class="grid g3"><label>Type<select name="type"><option value="guide"<?= $a['type'] === 'guide' ? ' selected' : '' ?>>Guide</option><option value="blog"<?= $a['type'] === 'blog' ? ' selected' : '' ?>>Blog post</option></select></label>
    <label>Group<small>Decides which guides link to each other</small><select name="group"><?php foreach(ARTICLE_GROUPS as $k=>$l): ?><option value="<?= $k ?>"<?= $a['group'] === $k ? ' selected' : '' ?>><?= h($l) ?></option><?php endforeach; ?></select></label>
    <label>Published<input type="date" name="date" value="<?= h($a['date']) ?>"></label></div>
  <label>Text *<small><?= h(FORMAT_HELP) ?></small><textarea class="big" name="body" required><?= h($a['body']) ?></textarea></label>
  <?= one_photo_editor('image', $a['image'] ?? '', 'Main photo', 'Shown at the top and when shared. Your own beagle photos are best.') ?>
  <details<?= $new ? ' open' : '' ?>><summary>Search engine &amp; listing</summary>
    <label>Address<small>/<?= $a['type'] === 'blog' ? 'blog' : 'guides' ?>/<b>…</b> — changing it breaks links people already have, so avoid it once published</small><input type="text" name="slug" value="<?= h($a['slug']) ?>"></label>
    <label>Title for Google<small>About 50–60 characters, with the main search phrase near the start</small><input type="text" name="seo_title" value="<?= h($a['seo_title']) ?>"></label>
    <label>Description for Google<small>About 140–155 characters that make people want to click</small><textarea name="seo_desc" rows="2"><?= h($a['seo_desc']) ?></textarea></label>
    <label>Short summary<small>Shown on guide cards</small><input type="text" name="excerpt" value="<?= h($a['excerpt']) ?>"></label></details>
  <label class="cb"><input type="checkbox" name="draft" value="1"<?= !empty($a['draft']) ? ' checked' : '' ?>> Draft (not on the website yet)</label>
  <button class="btn">Save</button></div></form>
  <?php if(!$new) echo '<p>'.del_button('delete_article', ['slug'=>$a['slug']], 'Delete', 'Delete this guide or post? Pages that link to it will need updating.').'</p>';
}

function view_pages(){
  global $SITE;
  ?><div class="row"><h1>Pages</h1><a class="btn right" href="<?= h(self_url('page')) ?>">+ New page</a></div>
  <p class="mut">Your trust and policy pages. They use your settings automatically ({deposit}, {genetic_years}…), so change numbers once in Settings and every page follows.</p>
  <div class="tw"><table><tr><th>Page</th><th>Address</th></tr><?php foreach($SITE['pages'] as $pg): ?><tr><td><a href="<?= h(self_url('page', ['slug'=>$pg['slug']])) ?>"><strong><?= h($pg['title']) ?></strong></a></td><td><a target="_blank" href="<?= h(site_url($pg['slug'])) ?>">/<?= h($pg['slug']) ?></a></td></tr><?php endforeach; ?></table></div><?php
}

function view_page(){
  global $SITE;
  $pg = find_by($SITE['pages'], 'slug', (string)($_GET['slug'] ?? '')) ?? [];
  $new = !$pg;
  $pg += ['slug'=>'', 'menu'=>'', 'title'=>'', 'seo_title'=>'', 'seo_desc'=>'', 'body'=>''];
  ?><div class="row"><h1><?= $new ? 'New page' : h($pg['title']) ?></h1><?php if(!$new): ?><a class="btn btn-g right" target="_blank" href="<?= h(site_url($pg['slug'])) ?>">View on site ↗</a><?php endif; ?></div>
  <?= form_start('save_page') ?><input type="hidden" name="orig" value="<?= h($pg['slug']) ?>"><div class="card">
  <div class="grid g2"><label>Main heading *<input type="text" name="title" value="<?= h($pg['title']) ?>" required></label><label>Short name for menus and breadcrumbs<input type="text" name="menu" value="<?= h($pg['menu']) ?>"></label></div>
  <?php if($new): ?><label>Address<small>e.g. beagle-puppies-for-sale-kentucky</small><input type="text" name="slug" value=""></label><?php endif; ?>
  <label>Text<small><?= h(FORMAT_HELP) ?></small><textarea class="big" name="body"><?= h($pg['body']) ?></textarea></label>
  <div class="grid g2"><label>Title for Google<input type="text" name="seo_title" value="<?= h($pg['seo_title']) ?>"></label><label>Description for Google<input type="text" name="seo_desc" value="<?= h($pg['seo_desc']) ?>"></label></div>
  <button class="btn">Save page</button></div></form>
  <?php if(!$new) echo '<p>'.del_button('delete_page', ['slug'=>$pg['slug']], 'Delete page', 'Delete this page? Links to it from other pages will stop working.').'</p>';
}

function view_promises(){
  global $SITE;
  $txt = fn($k)=>implode("\n\n", array_map(fn($x)=>$x['title']."\n".$x['text'], $SITE[$k]));
  ?><h1>Guarantees, steps and what’s included</h1>
  <p class="mut">These appear as cards across the site: the home page, every puppy page, How it works and the Health guarantee. Write each item as a title line, then its text on the next line, with a blank line between items. The numbers ({vet_check_hours}, {genetic_years}, {deposit}) come from Settings → Deposit &amp; guarantees.</p>
  <p class="help"><strong>Every guarantee is a promise you must keep.</strong> Read them carefully and change anything you won’t do. The full terms are on the Health guarantee and Deposit &amp; refunds pages (Pages).</p>
  <?= form_start('save_promises') ?><div class="grid g3">
    <div class="card"><label>Our guarantees<textarea class="big" name="guarantees"><?= h($txt('guarantees')) ?></textarea></label></div>
    <div class="card"><label>How it works (steps)<textarea class="big" name="steps"><?= h($txt('steps')) ?></textarea></label></div>
    <div class="card"><label>What comes with every puppy<textarea class="big" name="included"><?= h($txt('included')) ?></textarea></label></div>
  </div><button class="btn">Save</button></form><?php
}

function view_delivery(){
  global $SITE, $CFG;
  $m = array_column($SITE['delivery']['methods'], null, 'key');
  $zones = $SITE['delivery']['zones']; $zones[] = ['name'=>'', 'states'=>'', 'standard'=>'', 'express'=>''];
  ?><h1>Delivery options and prices</h1>
  <p class="mut">Buyers choose an option in the inquiry form and see the price and their total as soon as they pick their state. Delivery times count from when the puppy leaves <?= h($CFG['city']) ?>. Prices show on every puppy page, the Delivery page and in inquiry emails.</p>
  <?= form_start('save_delivery') ?><div class="card"><h2 style="margin-top:0">Options</h2><div class="tw"><table><tr><th>On</th><th>Name</th><th>Delivery time</th></tr>
  <?php foreach(['standard', 'express'] as $k): $r = $m[$k] ?? ['label'=>'', 'days'=>'', 'on'=>false]; ?><tr><td><input type="checkbox" name="m[<?= $k ?>][on]" value="1"<?= !empty($r['on']) ? ' checked' : '' ?>></td>
    <td><input type="text" name="m[<?= $k ?>][label]" value="<?= h($r['label']) ?>"></td><td><input type="text" name="m[<?= $k ?>][days]" value="<?= h($r['days']) ?>"></td></tr><?php endforeach; ?></table></div></div>
  <div class="card"><h2 style="margin-top:0">Prices by state</h2>
  <p class="help">One price for everywhere: keep a single zone with <b>*</b> as its states. To charge differently somewhere (e.g. Alaska and Hawaii), add a zone with those state codes, e.g. <b>AK, HI</b>. Specific zones are checked first, and the <b>*</b> zone covers every other state. Leave a price empty to show “Quoted on request”.</p>
  <div class="tw"><table><tr><th>Zone name</th><th>States</th><th>Standard ($)</th><th>Express ($)</th></tr>
  <?php foreach($zones as $i=>$z): ?><tr><td><input type="text" name="z[<?= $i ?>][name]" value="<?= h($z['name']) ?>" placeholder="<?= $z['name'] === '' ? 'Add a zone…' : '' ?>"></td><td><input type="text" name="z[<?= $i ?>][states]" value="<?= h($z['states']) ?>" placeholder="e.g. AK, HI or *"></td>
    <td><input type="text" name="z[<?= $i ?>][standard]" value="<?= h($z['standard']) ?>" inputmode="decimal"></td><td><input type="text" name="z[<?= $i ?>][express]" value="<?= h($z['express']) ?>" inputmode="decimal"></td></tr><?php endforeach; ?>
  </table></div><p class="mut">To remove a zone, clear its name and states and save.</p></div>
  <button class="btn">Save delivery options</button></form><?php
}

function view_payments(){
  global $SITE;
  $rows = $SITE['payments']; $rows[] = ['key'=>'', 'name'=>'', 'note'=>'', 'on'=>true];
  ?><h1>Payment methods</h1>
  <p class="mut">Customers choose their preferred method in the inquiry form. Payment details are never shown on the website — you send them by email after you’ve talked, which protects your customers from copycat scammers. Add a short note if you like (e.g. “PayPal Goods &amp; Services”); notes show on the Deposit &amp; refunds page.</p>
  <?= form_start('save_payments') ?><div class="tw"><table><tr><th>On</th><th>Name</th><th>Note (optional)</th></tr>
  <?php foreach($rows as $i=>$p): ?><tr><td><input type="hidden" name="pay[<?= $i ?>][key]" value="<?= h($p['key']) ?>"><input type="checkbox" name="pay[<?= $i ?>][on]" value="1"<?= $p['on'] ? ' checked' : '' ?>></td>
    <td><input type="text" name="pay[<?= $i ?>][name]" value="<?= h($p['name']) ?>" placeholder="<?= $p['name'] === '' ? 'Add another method…' : '' ?>"></td><td><input type="text" name="pay[<?= $i ?>][note]" value="<?= h($p['note']) ?>"></td></tr><?php endforeach; ?>
  </table></div><p class="mut">To remove a method, clear its name and save.</p><button class="btn">Save payment methods</button></form><?php
}

function view_settings(){
  global $CFG;
  $tab = (string)($_GET['tab'] ?? 'business');
  $tabs = ['business'=>'Business', 'deposit'=>'Deposit & guarantees', 'about'=>'About you', 'look'=>'Home page & texts', 'seo'=>'Google & social', 'alerts'=>'Email & alerts'];
  if(!isset($tabs[$tab])) $tab = 'business';
  $t = fn($k, $label, $help='', $type='text')=>'<label>'.h($label).($help ? '<small>'.h($help).'</small>' : '').'<input type="'.$type.'" name="'.$k.'" value="'.h($CFG[$k]).'"></label>';
  $ta = fn($k, $label, $help='', $rows=4)=>'<label>'.h($label).($help ? '<small>'.h($help).'</small>' : '').'<textarea name="'.$k.'" rows="'.$rows.'">'.h($CFG[$k]).'</textarea></label>';
  ?><h1>Settings</h1><div class="tabs"><?php foreach($tabs as $k=>$l): ?><a class="<?= $tab === $k ? 'on' : '' ?>" href="<?= h(self_url('settings', ['tab'=>$k])) ?>"><?= h($l) ?></a><?php endforeach; ?></div>
  <?= form_start('save_settings', true) ?><input type="hidden" name="tab" value="<?= h($tab) ?>"><div class="card">
  <?php if($tab === 'business'): ?>
    <div class="grid g2"><?= $t('brand', 'Business name') ?><?= $t('domain', 'Website address', 'e.g. https://beaglebrook.com — used in Google links and emails') ?>
      <?= $t('email', 'Email', 'Inquiries go here, and emails are sent from it', 'email') ?><?= $t('phone', 'Phone (for Call and Text buttons)', 'e.g. (513) 555-0123. Leave empty to hide the buttons.', 'tel') ?>
      <?= $t('city', 'City') ?><div class="grid g2"><?= $t('state', 'State code', 'e.g. OH') ?><?= $t('state_name', 'State name') ?></div>
      <?= $t('registry', 'Registry', 'e.g. AKC — shown on puppy pages as “AKC full registration”') ?><?= $t('payee_name', 'Name on your payment accounts', 'Shown on the “Verify us” page so buyers can check who they’re paying') ?>
      <?= $t('reply_time', 'How quickly you reply', 'Used in sentences like “we reply the same day”') ?></div>
    <?= $ta('license_text', 'Licenses and registrations', 'One per line, e.g. “Ohio Department of Agriculture dog breeder license #12345”. Shown on Verify us. Leave empty if none apply.', 3) ?>
  <?php elseif($tab === 'deposit'): ?>
    <p class="help">These numbers fill in every page, guarantee and email automatically.</p>
    <div class="grid g4"><?= $t('deposit_pct', 'Deposit (%)', 'At least 50', 'number') ?><?= $t('vet_check_hours', 'Vet check window (hours)', 'e.g. 72', 'number') ?><?= $t('genetic_years', 'Genetic guarantee (years)', '', 'number') ?><?= $t('go_home_weeks', 'Earliest go-home age (weeks)', '8 or more', 'number') ?></div>
    <?= $ta('delivery_note', 'Delivery note', 'Shown on the home page') ?>
    <p class="mut">Payment methods are under <a href="<?= h(self_url('payments')) ?>">Payments</a>; guarantee wording under <a href="<?= h(self_url('promises')) ?>">Guarantees</a>.</p>
  <?php elseif($tab === 'about'): ?>
    <p class="help">Real people sell puppies. Your name, your photo and your story on the About page are among the strongest trust signals you can add.</p>
    <?= $t('breeder_name', 'Your name(s)', 'e.g. “Jessica and Tom”') ?><?= $ta('breeder_story', 'Your story', 'How you started with beagles, how puppies are raised in your home, what you love about the breed. Formatting as in guides.', 8) ?>
    <?= one_photo_editor('breeder_photo', $CFG['breeder_photo'], 'Your photo', 'You with your beagles') ?>
  <?php elseif($tab === 'look'): ?>
    <?= one_photo_editor('hero_photo', $CFG['hero_photo'], 'Home page photo', 'A bright, real photo of your puppies (landscape). Without one, the first available puppy’s photo is used.') ?>
    <?= $t('hero_title', 'Home page heading') ?><?= $ta('hero_lede', 'Home page introduction', '', 3) ?>
    <?= $t('puppies_title', 'Puppies page heading') ?><?= $ta('puppies_intro', 'Puppies page introduction') ?><?= $ta('parents_intro', 'Our beagles page introduction', '', 3) ?>
    <?= $ta('litters_intro', 'Upcoming litters introduction', '', 3) ?><?= $ta('reviews_intro', 'Reviews page introduction', '', 3) ?><?= $ta('contact_intro', 'Contact page introduction', '', 3) ?><?= $ta('footer_blurb', 'Footer description', '', 2) ?>
  <?php elseif($tab === 'seo'): ?>
    <div class="grid g2"><?= $t('home_seo_title', 'Home page title for Google') ?><?= $t('home_seo_desc', 'Home page description for Google') ?><?= $t('puppies_seo_title', 'Puppies page title for Google') ?><?= $t('puppies_seo_desc', 'Puppies page description for Google') ?></div>
    <p class="help">Google Search Console: add your site as a “URL prefix” property, choose the “HTML tag” method and paste just the content="…" code here. Then submit <?= h(rtrim($CFG['domain'], '/')) ?>/sitemap.xml under Sitemaps.</p>
    <div class="grid g3"><?= $t('gsc_verification', 'Google Search Console code') ?><?= $t('bing_verification', 'Bing Webmaster code') ?><?= $t('ga4_id', 'Google Analytics ID', 'e.g. G-ABC123XYZ') ?></div>
    <div class="grid g2"><?= $t('facebook', 'Facebook page', '', 'url') ?><?= $t('instagram', 'Instagram', '', 'url') ?><?= $t('tiktok', 'TikTok', '', 'url') ?><?= $t('youtube', 'YouTube', '', 'url') ?><?= $t('google_reviews_url', 'Google review link', 'From your Google Business Profile → “Ask for reviews”', 'url') ?></div>
    <?= one_photo_editor('share_photo', $CFG['share_photo'], 'Link preview image', 'Shown when your site is shared on Facebook, in texts etc. 1200 × 630 px is ideal.') ?>
    <input type="hidden" name="pretty_urls_form" value="1"><label class="cb"><input type="checkbox" name="pretty_urls" value="1"<?= !empty($CFG['pretty_urls']) ? ' checked' : '' ?>> Clean page addresses (e.g. /guides/beagle-price). Switch off only if pages show “Not Found”.</label>
  <?php else: ?>
    <h2 style="margin-top:0">Sending email</h2>
    <p class="help">For inquiry emails to reliably reach your inbox (and your customers’), enter your mailbox’s SMTP details — from your email provider or host (e.g. cPanel → Email Accounts → Connect Devices). Leave empty to use the host’s PHP mail.</p>
    <div class="grid g3"><?= $t('smtp_host', 'SMTP server', 'e.g. mail.beaglebrook.com') ?><?= $t('smtp_port', 'Port', '587 for TLS, 465 for SSL') ?>
      <label>Security<select name="smtp_secure"><?php foreach(['tls'=>'TLS (587)', 'ssl'=>'SSL (465)', 'none'=>'None'] as $k=>$l): ?><option value="<?= $k ?>"<?= $CFG['smtp_secure'] === $k ? ' selected' : '' ?>><?= h($l) ?></option><?php endforeach; ?></select></label>
      <?= $t('smtp_user', 'Username', 'Usually the full email address') ?><label>Password<small><?= $CFG['smtp_pass'] !== '' ? 'Saved. Leave empty to keep it.' : '' ?></small><input type="password" name="smtp_pass" autocomplete="new-password"></label></div>
    <h2>Text alerts for new inquiries</h2>
    <p class="help">Free option: your carrier’s email-to-text address, e.g. 5135550123@vtext.com (Verizon), @txt.att.net (AT&amp;T), @tmomail.net (T-Mobile). Or use Twilio for the most reliable texts.</p>
    <div class="grid g2"><?= $t('alert_sms_email', 'Email-to-text address') ?><?= $t('alert_phone', 'Twilio: your mobile number', 'e.g. +15135550123') ?>
      <?= $t('twilio_sid', 'Twilio Account SID') ?><label>Twilio Auth Token<small><?= $CFG['twilio_token'] !== '' ? 'Saved. Leave empty to keep it.' : '' ?></small><input type="password" name="twilio_token" autocomplete="new-password"></label><?= $t('twilio_from', 'Twilio phone number', 'e.g. +15135550000') ?></div>
  <?php endif; ?>
  <button class="btn">Save settings</button></div></form>
  <?php if($tab === 'alerts'): ?><div class="grid g2">
    <div class="card"><?= form_start('test_email') ?><label>Send a test email to<input type="email" name="to" value="<?= h($CFG['email']) ?>"></label><button class="btn btn-g">Send test email</button></form></div>
    <div class="card"><?= form_start('test_text') ?><p>Send a test text alert using the settings above.</p><button class="btn btn-g">Send test text</button></form></div></div>
  <?php endif;
}

function view_account(){
  global $AUTH;
  ?><h1>Sign-in</h1><div class="card" style="max-width:480px"><?= form_start('save_account') ?>
  <label>Username<input type="text" name="user" value="<?= h($AUTH['user'] ?? '') ?>" autocomplete="username"></label>
  <label>New password<small>Leave empty to keep your current password</small><input type="password" name="pass" autocomplete="new-password"></label>
  <label>New password again<input type="password" name="pass2" autocomplete="new-password"></label>
  <label>Current password *<input type="password" name="current" autocomplete="current-password" required></label>
  <button class="btn">Save</button></form></div><?php
}
