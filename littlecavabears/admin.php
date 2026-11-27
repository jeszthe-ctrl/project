<?php
/* =============================================================
   Little Cava Bears — admin. Open /admin.php in your browser.
   The first visit asks you to create the admin sign-in, so do
   that straight after uploading. Everything is saved to data/
   (last 30 versions kept in data/backups/).
   ============================================================= */

define('BB_ROOT', __DIR__);
@ini_set('display_errors', '0');
error_reporting(E_ALL & ~E_NOTICE & ~E_WARNING & ~E_DEPRECATED);
require BB_ROOT.'/inc/core.php';
require BB_ROOT.'/inc/deals.php';
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
const ARTICLE_GROUPS = ['buying'=>'Buying a cavapoo puppy', 'breed'=>'The cavapoo', 'sizes'=>'Cavapoo sizes', 'colors'=>'Colors and markings', 'grooming'=>'Haircuts and grooming', 'care'=>'Care, health and training', 'culture'=>'Rescue and more'];
const FORMAT_HELP = 'Formatting: blank line = new paragraph · "## " heading, "### " subheading · "- " bullet, "1. " numbered · **bold**, *italic* · [link text](/health-guarantee) or [text](https://…) · "> " highlighted note · tables as | a | b | lines · a photo on its own line: ![description](assets/uploads/…) (use “Insert” under Photos inside the text). {deposit} {balance} {vet_check_hours} ({vet_check_hour}) {genetic_years} ({genetic_year}) {go_home_weeks} {email} {city} {state} {payment_methods} {registry} {reply_time} {delivery_options} fill in your settings. A line with only {guarantees}, {steps}, {included}, {payments}, {delivery_rates}, {puppies}, {licenses}, {payee}, {breeder} or {contact} shows that block. Questions under "## Questions" written as "### Question" become FAQs for Google.';
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

function states(){ static $s = null; return $s ?? ($s = require BB_ROOT.'/inc/states.php'); }
function state_slugs(){ return array_map(fn($st)=>slugify($st[0]), array_values(states())); }

/* old address → new address (both without the leading /), so links and rankings follow a moved page */
function add_redirect($from, $to){
  global $SITE;
  $from = trim($from, '/'); $to = trim($to, '/');
  if($from === '' || $from === $to) return;
  foreach($SITE['redirects'] as $f=>$t) if($t === $from) $SITE['redirects'][$f] = $to;   /* no chains: a→b, b→c becomes a→c, b→c */
  unset($SITE['redirects'][$to]);                                                        /* the new address is a real page now */
  $SITE['redirects'][$from] = $to;
  foreach($SITE['redirects'] as $f=>$t) if($f === $t) unset($SITE['redirects'][$f]);
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
  return ["New cavapoo puppy: {$p['name']}",
    "Good news — {$p['name']} is now available at {$CFG['brand']}.\n\n"
    ."{$p['name']}: ".trim($p['color'].' '.strtolower($p['sex'] === 'female' ? 'female' : 'male')).' cavapoo'.($age ? ", $age old" : '').(($p['price'] ?? '') !== '' ? ', '.money($p['price']) : '').".\n"
    ."Vet checked, shots, microchip, starter food and all documents, delivered from {$CFG['city']}, {$CFG['state']}.\n\n"
    ."See photos and send an inquiry: ".live_url('cavapoo-puppies-for-sale/'.$p['slug'])."\n\nWaitlist families hear first, so if {$p['name']} is the one, let us know soon."];
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
    if(data_write('auth', $AUTH)){ log_in(); note('Your sign-in is set up. Welcome to your Little Cava Bears admin!'); }
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
    'price'=>in_price('price'), 'size'=>in_array(in_line('size'), ['toy', 'mini', 'standard'], true) ? in_line('size') : '', 'weight'=>in_line('weight', 40),
    'registry'=>in_line('registry', 40), 'reg_no'=>in_line('reg_no', 80), 'sire'=>in_line('sire', 20), 'dam'=>in_line('dam', 20),
    'microchip'=>in_bool('microchip'), 'chip_no'=>in_line('chip_no', 40), 'vet_date'=>in_date('vet_date'), 'vaccines'=>in_str('vaccines', 1000), 'tagline'=>in_line('tagline', 200),
    'description'=>in_str('description', 20000), 'video'=>in_line('video', 300), 'hidden'=>in_bool('hidden'),
    'markings'=>in_line('markings', 60), 'adult_weight'=>in_line('adult_weight', 40), 'champion'=>in_bool('champion'), 'sire_text'=>in_line('sire_text', 80), 'dam_text'=>in_line('dam_text', 80),
    'seo_title'=>in_line('seo_title', 120), 'seo_desc'=>in_line('seo_desc', 300), 'updated'=>date('c'),
  ]);
  $want = in_line('slug', 80) !== '' ? in_line('slug', 80) : $name.' '.$p['color'].' '.$p['sex'];
  $old_slug = $p['slug'] ?? '';
  if($i === null || in_line('slug', 80) !== $old_slug) $p['slug'] = unique_slug($want, $SITE['puppies'], $p['id']);
  if(in_array($p['slug'], state_slugs(), true)) $p['slug'] = unique_slug($p['slug'].'-puppy', $SITE['puppies'], $p['id']);
  if($i !== null && $old_slug !== '' && $old_slug !== $p['slug']) add_redirect('cavapoo-puppies-for-sale/'.$old_slug, 'cavapoo-puppies-for-sale/'.$p['slug']);
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
  unset($r['pending']);   /* saving a review that a family sent in also approves it */
  if($r['name'] === '' || $r['text'] === ''){ note('A review needs the family’s name and their words.', 'err'); go('review', $id ? ['id'=>$id] : []); }
  $r['photo'] = handle_one_photo('photo', $r['photo'] ?? '', 'review-'.$r['name']);
  if($i === null) array_unshift($SITE['reviews'], $r); else $SITE['reviews'][$i] = $r;
  save('Review saved.');
  go('reviews');
}
if($act === 'approve_review'){
  $i = $find_index($SITE['reviews'], in_line('id', 20));
  if($i !== null){ unset($SITE['reviews'][$i]['pending']); $SITE['reviews'][$i]['hidden'] = false; save('Review approved: it’s on the Reviews page now.'); }
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
    'seo_desc'=>in_line('seo_desc', 300), 'answer'=>in_line('answer', 600), 'excerpt'=>in_line('excerpt', 300), 'body'=>in_str('body', 200000), 'draft'=>in_bool('draft'),
    'date'=>in_date('date') ?: ($a['date'] ?? date('Y-m-d'))]);
  if($changed && $idx !== null) $a['updated'] = date('Y-m-d');
  if($idx !== null && $slug !== $orig){
    $SITE['removed']['articles'][] = $orig;   /* a renamed starter guide mustn't come back under its old address */
    add_redirect(($a['type'] === 'blog' ? 'blog/' : 'guides/').$orig, ($type === 'blog' ? 'blog/' : 'guides/').$slug);
  }
  /* photos placed inside the text with ![description](assets/uploads/…) */
  $inline = [];
  foreach($a['photos'] ?? [] as $f){ if(in_array($f, (array)($_POST['remove_inline'] ?? []), true)) delete_upload($f); else $inline[] = $f; }
  foreach(upload_list('inline') as $f){ $r = save_upload($f, 'guide-'.$slug); if($r !== '' && $r[0] === '!') note(substr($r, 1), 'err'); elseif($r !== '') $inline[] = $r; }
  $a['photos'] = array_slice($inline, 0, 30);
  $a['image'] = handle_one_photo('image', $a['image'] ?? '', 'article-'.$slug);
  if($idx === null) $SITE['articles'][] = $a; else $SITE['articles'][$idx] = $a;
  save('“'.$title.'” is saved.'.($a['draft'] ? ' It’s a draft, so it isn’t on the site yet.' : ''));
  go('article', ['slug'=>$slug]);
}
if($act === 'delete_article'){
  foreach($SITE['articles'] as $k=>$a){ if($a['slug'] === in_line('slug', 120)){ delete_upload($a['image'] ?? ''); foreach($a['photos'] ?? [] as $f) delete_upload($f); array_splice($SITE['articles'], $k, 1); $SITE['removed']['articles'][] = $a['slug'];
      add_redirect(($a['type'] === 'blog' ? 'blog/' : 'guides/').$a['slug'], $a['type'] === 'blog' ? 'blog' : 'guides'); save('Deleted. Its old address now leads to the '.($a['type'] === 'blog' ? 'blog' : 'guides').' page.'); break; } }
  go('articles');
}

if($act === 'save_page'){
  $orig = in_line('orig', 120);
  $idx = null; foreach($SITE['pages'] as $k=>$pg){ if($pg['slug'] === $orig && $orig !== ''){ $idx = $k; break; } }
  $pg = ['slug'=>$idx !== null ? $orig : unique_slug(in_line('slug', 120) ?: in_line('title', 120), array_map(fn($x)=>$x + ['sid'=>$x['slug']], $SITE['pages']), '', 'sid'),
         'menu'=>in_line('menu', 60), 'answer'=>in_line('answer', 600), 'title'=>in_line('title', 200), 'seo_title'=>in_line('seo_title', 120), 'seo_desc'=>in_line('seo_desc', 300), 'body'=>in_str('body', 100000)];
  if(in_array($pg['slug'], ['cavapoo-puppies-for-sale', 'our-dogs', 'upcoming-litters', 'reviews', 'contact', 'guides', 'blog', 'thank-you', 'unsubscribe', 'admin', 'assets', 'data', 'inc'], true)){ note('That address is used by the site already. Choose another.', 'err'); go('page'); }
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

/* ---- inquiry: one-click emails, agreement, payments ---- */
const STATUS_ORDER = ['new'=>0, 'contacted'=>1, 'deposit'=>2, 'delivered'=>3, 'closed'=>4];
/* moves an inquiry forward (never back) and reserves its puppy when asked */
function advance(&$q, $status, $reserve=false){
  global $SITE;
  if($status !== '' && (STATUS_ORDER[$status] ?? 0) > (STATUS_ORDER[$q['status']] ?? 0)) $q['status'] = $status;
  if($reserve && ($q['puppy_id'] ?? '') !== ''){
    foreach($SITE['puppies'] as $i=>$p) if($p['id'] === $q['puppy_id'] && $p['status'] === 'available'){
      $SITE['puppies'][$i]['status'] = 'reserved'; $SITE['puppies'][$i]['updated'] = date('c');
      site_save($SITE); note($p['name'].' is now marked Reserved on the website.');
    }
  }
}
function mail_result($ok, $what){ note($ok ? $what : 'The email could not be sent. '.($GLOBALS['BB_MAIL_ERROR'] ?? '').' Check Settings → Email & alerts.', $ok ? 'ok' : 'err'); }

if($act === 'send_template'){
  $q = inquiry_load(in_line('ref', 20)); $key = in_line('key', 20); $tpl = $SITE['templates'][$key] ?? null;
  if(!$q || !$tpl) go('inquiries');
  $extra = ['date'=>in_date('date') !== '' ? date('l, F j', strtotime(in_date('date'))) : '', 'note'=>in_str('note', 3000)];
  if(!empty($tpl['needs_date']) && $extra['date'] === '' && in_str('body', 20000) === ''){ note('Choose the departure date first.', 'err'); go('inquiry', ['ref'=>$q['ref']]); }
  $custom = in_str('body', 20000) !== '';
  $subject = $custom ? in_line('subject', 200) : template_render($tpl['subject'], $q, $extra);
  $body = $custom ? in_str('body', 20000) : template_render($tpl['body'], $q, $extra);
  if($key === 'payment' && !$custom && template_vars($q)['{payment_details}'] === ''){
    note('Add your '.($q['payment_name'] ?: 'payment').' details under Admin → Payments first (they’re private: only sent by email), or edit the email before sending.', 'err');
    go('inquiry', ['ref'=>$q['ref']]);
  }
  if($key === 'payment' && empty($q['deal'])){ $q['deal'] = deal_of($q); $q['agreement']['sent'] = date('c'); }   /* the payment email links the agreement */
  $ok = email_buyer($q, $key, $subject, $body);
  if($ok) advance($q, $tpl['status'] ?? '', !empty($tpl['reserve']));
  inquiry_save($q);
  mail_result($ok, '“'.$tpl['name'].'” sent to '.$q['email'].'.');
  go('inquiry', ['ref'=>$q['ref']]);
}
if($act === 'send_agreement'){
  $q = inquiry_load(in_line('ref', 20));
  if(!$q) go('inquiries');
  if(empty($q['agreement']['accepted'])){ unset($q['deal']); $q['deal'] = deal_of($q); }   /* not accepted yet: send today's details */
  $q['agreement']['sent'] = date('c');
  $d = deal_of($q); $t = deal_totals($q);
  $body = "Hi ".(strtok(trim($q['name']), ' ') ?: 'there').",\n\nHere is your sales agreement for {$d['puppy']}. Please read it and accept it online — it takes a minute:\n".agreement_link($q['ref'])
    ."\n\nSummary\n- Puppy: {$d['puppy']}, ".money($d['price'])."\n".($d['delivery'] !== '' ? "- Delivery: {$d['delivery']}, ".($d['delivery_fee'] !== null ? money($d['delivery_fee']) : 'to be confirmed')."\n" : '')
    ."- Total: ".money($t['total'])."\n- Deposit to reserve: ".money($d['deposit'])."\n- Due at delivery: ".money($t['at_delivery'])
    ."\n\nThe agreement includes our health guarantee, deposit and refund terms and delivery details. You can print it from the same page.";
  $ok = email_buyer($q, 'agreement', 'Your sales agreement for '.$d['puppy'].' — '.$CFG['brand'], $body);
  if($ok) advance($q, 'contacted');
  inquiry_save($q);
  mail_result($ok, 'Sales agreement sent to '.$q['email'].'.');
  go('inquiry', ['ref'=>$q['ref']]);
}
if($act === 'record_payment'){
  $q = inquiry_load(in_line('ref', 20));
  if(!$q) go('inquiries');
  $amount = (float)in_price('amount');
  if($amount <= 0){ note('Enter the amount received.', 'err'); go('inquiry', ['ref'=>$q['ref']]); }
  if(empty($q['deal'])) $q['deal'] = deal_of($q);
  $n = 1 + max(array_merge([0], array_map(fn($x)=>(int)$x['n'], $q['payments'] ?? [])));
  $pay = ['n'=>$n, 'amount'=>$amount, 'method'=>in_line('method', 40) ?: ($q['payment_name'] ?? ''), 'date'=>in_date('date') ?: date('Y-m-d'), 'note'=>in_line('note', 200), 'time'=>date('c')];
  $q['payments'][] = $pay;
  advance($q, 'deposit', true);
  note('Payment of '.money($amount).' recorded.');
  if(in_bool('send')){
    $d = deal_of($q); $t = deal_totals($q);
    $body = "Hi ".(strtok(trim($q['name']), ' ') ?: 'there').",\n\nThank you — we’ve received your payment of ".money($amount)." for {$d['puppy']}.\n\nReceipt {$q['ref']}-{$n}\n- Amount: ".money($amount)."\n- Method: {$pay['method']}\n- Date: ".nice_date($pay['date'])
      ."\n\nTotal: ".money($t['total'])."\nPaid so far: ".money($t['paid'])."\nStill to pay: ".money($t['due'])."\n\nYour receipt online (you can print it): ".receipt_link($q['ref']);
    mail_result(email_buyer($q, 'receipt', 'Receipt for your payment of '.money($amount).' — '.$d['puppy'], $body), 'Receipt emailed to '.$q['email'].'.');
  }
  inquiry_save($q);
  go('inquiry', ['ref'=>$q['ref']]);
}
if($act === 'delete_payment'){
  $q = inquiry_load(in_line('ref', 20));
  if($q){ $n = (int)in_line('n', 5); $q['payments'] = array_values(array_filter($q['payments'] ?? [], fn($x)=>(int)$x['n'] !== $n)); inquiry_save($q); note('Payment removed.'); }
  go('inquiry', ['ref'=>$q['ref'] ?? '']);
}
if($act === 'save_templates'){
  foreach($SITE['templates'] as $k=>$t){
    if(!isset($_POST['tpl'][$k])) continue;
    $row = (array)$_POST['tpl'][$k];
    $subject = trim(preg_replace('/\s+/', ' ', (string)($row['subject'] ?? ''))); $body = trim(str_replace("\r", '', (string)($row['body'] ?? '')));
    if($subject !== '' && $body !== ''){ $SITE['templates'][$k]['subject'] = mb_substr($subject, 0, 200); $SITE['templates'][$k]['body'] = mb_substr($body, 0, 10000); }
  }
  save('Email templates saved.');
  go('templates');
}
if($act === 'reset_template'){
  $k = in_line('key', 20); $d = site_defaults()['templates'][$k] ?? null;
  if($d){ $SITE['templates'][$k] = $d; save('“'.$d['name'].'” is back to its original wording.'); }
  go('templates');
}
if($act === 'add_redirect'){
  $from = trim(preg_replace('#^https?://[^/]+#i', '', in_line('from', 300)), '/'); $to = trim(preg_replace('#^https?://[^/]+#i', '', in_line('to', 300)), '/');
  if($from === '' || !preg_match('#^[\w./%-]+$#', $from)) note('Enter the old address, e.g. /guides/old-guide-name', 'err');
  else { add_redirect($from, $to); save("/$from now leads to /$to."); }
  go('redirects');
}
if($act === 'delete_redirect'){
  unset($SITE['redirects'][in_line('from', 300)]); save('Redirect removed.');
  go('redirects');
}

if($act === 'save_locations'){
  $on = array_map('strtoupper', (array)($_POST['on'] ?? []));
  foreach(states() as $c=>$st){
    $cur = $SITE['locations'][$c] ?? ['on'=>false, 'notes'=>''];
    $was = !empty($cur['on']); $cur['on'] = in_array($c, $on, true);
    if($was && !$cur['on']) add_redirect('cavapoo-puppies-for-sale/'.slugify($st[0]), 'cavapoo-puppies-for-sale');   /* a page that existed keeps its visitors */
    if($cur['on'] && isset($SITE['redirects']['cavapoo-puppies-for-sale/'.slugify($st[0])])) unset($SITE['redirects']['cavapoo-puppies-for-sale/'.slugify($st[0])]);
    if($cur['on'] || $cur['notes'] !== '') $SITE['locations'][$c] = $cur; else unset($SITE['locations'][$c]);
  }
  save('Delivery pages saved: '.count(array_filter($SITE['locations'], fn($l)=>!empty($l['on']))).' states.');
  go('locations');
}
if($act === 'save_location_notes'){
  $c = strtoupper(in_line('state', 2));
  if(isset(states()[$c])){ $SITE['locations'][$c] = ['on'=>!empty($SITE['locations'][$c]['on']), 'notes'=>in_str('notes', 20000)]; save(states()[$c][0].' notes saved.'); }
  go('locations', ['s'=>$c]);
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
    $out[] = ['key'=>slugify($row['key'] ?? '' ?: $name) ?: new_id(), 'name'=>mb_substr($name, 0, 40), 'note'=>mb_substr(trim((string)($row['note'] ?? '')), 0, 200),
              'details'=>mb_substr(trim(str_replace("\r", '', (string)($row['details'] ?? ''))), 0, 1000), 'on'=>!empty($row['on'])];
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
  if(isset($_POST['domain'])){ $d = rtrim(in_line('domain', 200), '/'); if(preg_match('#^https?://[a-z0-9.-]+(:\d+)?(/[\w./-]*)?$#i', $d)) $s['domain'] = $d; else note('The website address should look like https://littlecavabears.com', 'err'); }
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
if($act === 'save_inquiry_details'){
  $q = inquiry_load(in_line('ref', 20));
  if(!$q) go('inquiries');
  $p = find_by($SITE['puppies'], 'id', in_line('puppy', 20));
  $q = array_merge($q, ['name'=>in_line('name', 120), 'email'=>in_line('email', 160) ?: $q['email'], 'phone'=>in_line('phone', 40),
    'address1'=>in_line('address1', 160), 'address2'=>in_line('address2', 120), 'city'=>in_line('city', 80),
    'state'=>isset(US_STATES[strtoupper(in_line('state', 2))]) ? strtoupper(in_line('state', 2)) : '', 'zip'=>in_line('zip', 10),
    'puppy_id'=>$p['id'] ?? '', 'puppy_name'=>$p['name'] ?? '', 'puppy_price'=>$p['price'] ?? '']);
  $pay = find_by($SITE['payments'], 'key', in_line('payment', 40));
  $q['payment'] = $pay['key'] ?? ''; $q['payment_name'] = $pay['name'] ?? '';
  $opt = find_by($SITE['delivery']['methods'], 'key', in_line('delivery', 20));
  if($opt){
    $fee = $q['state'] !== '' ? delivery_fee($q['state'], $opt['key']) : delivery_base_fee($opt['key']);
    $q['delivery'] = $opt['key']; $q['delivery_label'] = $opt['label']; $q['delivery_days'] = $opt['days'];
    $q['delivery_name'] = $opt['label'].' ('.$opt['days'].')'; $q['delivery_fee'] = $fee === null ? '' : (string)$fee;
  } else foreach(['delivery', 'delivery_label', 'delivery_days', 'delivery_name', 'delivery_fee'] as $k) unset($q[$k]);
  if(empty($q['agreement']['accepted'])) unset($q['deal']);   /* the next agreement sent uses these details */
  inquiry_save($q);
  note('Details saved.'.(!empty($q['agreement']['accepted']) ? ' The accepted agreement keeps the details it was accepted with.' : ''));
  go('inquiry', ['ref'=>$q['ref']]);
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

/* ---- puppies from a spreadsheet (CSV): one row per puppy, matched to existing puppies by name ---- */
const PUPPY_CSV = ['name', 'sex', 'color', 'markings', 'dob', 'ready', 'price', 'status', 'size', 'adult_weight', 'weight', 'registry', 'reg_no', 'chip_no', 'champion', 'dam', 'sire', 'vet_date', 'vaccines', 'tagline', 'description', 'video', 'photos', 'old_slug'];
/* other column names people use, so most spreadsheets import without renaming anything */
const PUPPY_CSV_ALIASES = ['puppy'=>'name', 'puppy name'=>'name', 'dog'=>'name', 'dog name'=>'name', 'call name'=>'name', 'gender'=>'sex',
  'colour'=>'color', 'color/markings'=>'color', 'markings'=>'color', 'date of birth'=>'dob', 'birthday'=>'dob', 'born'=>'dob', 'birth date'=>'dob',
  'ready date'=>'ready', 'go home'=>'ready', 'go home date'=>'ready', 'ready to go'=>'ready', 'price ($)'=>'price', 'price usd'=>'price', 'cost'=>'price',
  'availability'=>'status', 'adult size'=>'size', 'expected size'=>'size', 'breed'=>'size', 'variety'=>'size', 'registration'=>'reg_no', 'registration number'=>'reg_no', 'reg #'=>'reg_no',
  'akc'=>'reg_no', 'litter number'=>'reg_no', 'microchip'=>'chip_no', 'microchip number'=>'chip_no', 'chip'=>'chip_no', 'mother'=>'dam', 'mom'=>'dam',
  'father'=>'sire', 'dad'=>'sire', 'vet check'=>'vet_date', 'vet check date'=>'vet_date', 'shots'=>'vaccines', 'vaccinations'=>'vaccines',
  'summary'=>'tagline', 'about'=>'description', 'notes'=>'description', 'video link'=>'video', 'youtube'=>'video',
  'ready_date'=>'ready', 'price_usd'=>'price', 'est_adult_weight_lbs'=>'adult_weight', 'adult weight'=>'adult_weight', 'expected adult weight'=>'adult_weight',
  'champion_bloodline'=>'champion', 'champion bloodline'=>'champion', 'video_embed_url'=>'video', 'dad_info'=>'sire', 'mom_info'=>'dam',
  'gallery_images'=>'photos', 'gallery'=>'photos', 'images'=>'photos', 'slug'=>'old_slug',
  'champion_status'=>'champion', 'image_gallery'=>'photos', 'video_url'=>'video'];
function csv_date($v){
  $v = trim((string)$v);
  if($v === '') return '';
  if(preg_match('#^(\d{1,2})[/.-](\d{1,2})[/.-](\d{2,4})$#', $v, $m)){ $y = strlen($m[3]) === 2 ? 2000 + (int)$m[3] : (int)$m[3]; return checkdate((int)$m[1], (int)$m[2], $y) ? sprintf('%04d-%02d-%02d', $y, $m[1], $m[2]) : ''; }
  if(preg_match('/\bnow\b|go home|^ready$/i', $v)) return '';   /* "Ready now", "Ready to go home" */
  $v = preg_replace('/^ready\s*(by|on|from|after)?\s*/i', '', $v);   /* "Ready by Dec. 15" */
  $t = strtotime(str_replace('.', '', $v));
  return $t ? date('Y-m-d', $t) : '';
}
function csv_rows($file){
  $raw = (string)@file_get_contents($file);
  $raw = preg_replace('/^\xEF\xBB\xBF/', '', $raw);
  if(!mb_check_encoding($raw, 'UTF-8')) $raw = mb_convert_encoding($raw, 'UTF-8', 'Windows-1252');   /* Excel's own format */
  $first = strtok($raw, "\n");
  $sep = substr_count($first, ';') > substr_count($first, ',') ? ';' : (substr_count($first, "\t") > substr_count($first, ',') ? "\t" : ',');
  $fp = fopen('php://temp', 'r+'); fwrite($fp, $raw); rewind($fp);
  $rows = [];
  while(($r = fgetcsv($fp, 0, $sep, '"', '')) !== false) if(array_filter($r, fn($c)=>trim((string)$c) !== '')) $rows[] = $r;
  fclose($fp);
  return $rows;
}

if($act === 'import_puppies'){
  $f = $_FILES['csv'] ?? null;
  if(!$f || ($f['error'] ?? 1) !== UPLOAD_ERR_OK){ note('Choose a CSV file to import.', 'err'); go('puppy_import'); }
  $rows = csv_rows($f['tmp_name']);
  if(count($rows) < 2){ note('The file has no puppies in it: it needs a header row and at least one puppy row.', 'err'); go('puppy_import'); }
  $cols = [];
  foreach(array_shift($rows) as $i=>$hd){
    $k = strtolower(trim(preg_replace('/\s+/', ' ', (string)$hd)));
    $k = PUPPY_CSV_ALIASES[$k] ?? str_replace(' ', '_', $k);
    if(in_array($k, PUPPY_CSV, true) && !isset($cols[$k])) $cols[$k] = $i;
  }
  if(!isset($cols['name'])){ note('The file needs a “name” column (the puppy’s name). Download the template to see the columns.', 'err'); go('puppy_import'); }
  $new_parents = [];
  $parent_id = function($name) use(&$SITE){ $name = strtolower(trim($name)); if($name === '') return ''; foreach($SITE['parents'] as $p) if(strtolower($p['name']) === $name || strtolower($p['registered_name'] ?? '') === $name) return $p['id']; return null; };
  $added = $updated = 0; $issues = [];
  foreach($rows as $n=>$r){
    $get = fn($k)=>isset($cols[$k]) ? trim((string)($r[$cols[$k]] ?? '')) : null;
    $name = $get('name');
    if($name === ''){ $issues[] = 'Row '.($n + 2).': no name, skipped.'; continue; }
    $i = null; foreach($SITE['puppies'] as $k=>$p) if(strcasecmp($p['name'], $name) === 0){ $i = $k; break; }
    $p = $i !== null ? $SITE['puppies'][$i] : ['id'=>new_id(), 'created'=>date('c'), 'photos'=>[], 'status'=>'available', 'sex'=>'male', 'color'=>'', 'price'=>'', 'dob'=>'', 'microchip'=>true, 'hidden'=>false];
    $p['name'] = mb_substr($name, 0, 60);
    $set = function($k, $v) use(&$p){ if($v !== null) $p[$k] = $v; };
    if(($v = $get('sex')) !== null && $v !== '') $p['sex'] = preg_match('/^(f|female|girl|she|bitch)/i', $v) ? 'female' : 'male';
    $set('color', $get('color') !== null ? mb_substr($get('color'), 0, 60) : null);
    foreach(['dob', 'ready', 'vet_date'] as $k) if(($v = $get($k)) !== null){ $p[$k] = csv_date($v); if($k === 'dob' && $p[$k] !== '' && $p[$k] > date('Y-m-d')) $p[$k] = date('Y-m-d', strtotime($p[$k].' -1 year'));   /* "May 27" without a year */
      if($v !== '' && $p[$k] === '' && !preg_match('/\bnow\b|go home|^ready$/i', $v)) $issues[] = "$name: couldn’t read the $k “{$v}” (use e.g. 2026-08-28 or 8/28/2026)."; }
    if(($v = $get('price')) !== null){ $num = str_replace(['$', ',', ' ', 'USD', 'usd'], '', $v); if($v === '' || is_numeric($num)) $p['price'] = $v === '' ? '' : (string)round((float)$num, 2); else $issues[] = "$name: couldn’t read the price “{$v}”."; }
    if(($v = $get('status')) !== null && $v !== '') $p['status'] = preg_match('/sold|home|adopted/i', $v) ? 'sold' : (preg_match('/reserv|hold|pending|deposit/i', $v) ? 'reserved' : 'available');
    if(($v = $get('size')) !== null) $p['size'] = preg_match('/toy|teacup|tiny|petite|micro/i', $v) ? 'toy' : (preg_match('/mini/i', $v) ? 'mini' : (preg_match('/standard|medium|large/i', $v) ? 'standard' : ''));
    foreach(['weight'=>40, 'registry'=>40, 'reg_no'=>80, 'chip_no'=>40, 'tagline'=>200, 'video'=>300] as $k=>$max) $set($k, $get($k) !== null ? mb_substr($get($k), 0, $max) : null);
    foreach(['vaccines'=>1000, 'description'=>20000] as $k=>$max) $set($k, $get($k) !== null ? mb_substr(str_replace(['\n', ' | '], "\n", $get($k)), 0, $max) : null);
    /* parents: "Topper (22-25 lbs)" links to (or creates) the parent Topper; a generic name such as "Cavapoo" is kept as text */
    foreach(['dam', 'sire'] as $k) if(($v = $get($k)) !== null){
      $pname = trim(preg_replace('/\s*\(.*\)\s*$/', '', $v)); $pweight = preg_match('/\(([^)]*)\)/', $v, $m) ? trim($m[1]) : '';
      $p[$k.'_text'] = '';
      if($pname === ''){ $p[$k] = ''; continue; }
      if(in_array(strtolower($pname), ['cavapoo', 'unknown', 'n/a', '-'], true)){ $p[$k] = ''; $p[$k.'_text'] = $v; continue; }
      $id = $parent_id($pname);
      if($id === null){
        $np = ['id'=>new_id(), 'name'=>mb_substr($pname, 0, 60), 'role'=>$k, 'registered_name'=>'', 'registry'=>'', 'reg_no'=>'', 'dob'=>'', 'color'=>'', 'height'=>'',
               'weight'=>$pweight, 'health'=>'', 'description'=>'', 'retired'=>false, 'hidden'=>false, 'photos'=>[]];
        $np['slug'] = unique_slug($np['name'], $SITE['parents'], $np['id']);
        $SITE['parents'][] = $np; $id = $np['id']; $new_parents[] = $np['name'];
      }
      $p[$k] = $id;
    }
    if(($v = $get('champion')) !== null) $p['champion'] = (bool)preg_match('/^(y|yes|true|1|champion)/i', $v);
    foreach(['markings'=>60, 'adult_weight'=>40, 'old_slug'=>80] as $k=>$max) if(($v = $get($k)) !== null) $p[$k] = mb_substr(preg_match('/^no markings?$|cavapoo coat$/i', $v) ? '' : $v, 0, $max);
    if(($v = $get('photos')) !== null && $v !== ''){
      /* photo paths on this website (e.g. assets/puppies/babe/babe-01.jpg): they show once those files are uploaded */
      $paths = array_values(array_filter(array_map(fn($x)=>ltrim(trim($x), '/'), preg_split('/\s*[|,;]\s*/', $v)), fn($x)=>preg_match('#^assets/[\w./-]+\.(jpe?g|png|webp)$#i', $x) && strpos($x, '..') === false));
      $p['photos'] = array_values(array_unique(array_merge(array_values(array_filter($p['photos'] ?? [], fn($f)=>strpos($f, 'assets/uploads/') === 0)), $paths)));
    }
    if(($p['chip_no'] ?? '') !== '') $p['microchip'] = true;
    /* the spreadsheet's own page name (e.g. lilac-female-black-miniature-cavapoo) is kept as the page address */
    if(empty($p['slug'])) $p['slug'] = unique_slug(($p['old_slug'] ?? '') !== '' ? $p['old_slug'] : $p['name'].' '.($p['color'] ?? '').' '.$p['sex'], $SITE['puppies'], $p['id']);
    if(in_array($p['slug'], state_slugs(), true)) $p['slug'] = unique_slug($p['slug'].'-puppy', $SITE['puppies'], $p['id']);
    $p['updated'] = date('c');
    if($i === null){ $SITE['puppies'][] = $p; $added++; } else { $SITE['puppies'][$i] = $p; $updated++; }
  }
  save("Imported: $added new ".($added === 1 ? 'puppy' : 'puppies').", $updated updated.".($new_parents ? ' New parents added: '.implode(', ', array_unique($new_parents)).'.' : ''));
  foreach(array_slice($issues, 0, 15) as $m) note($m, 'err');
  go('puppies');
}

/* CSV downloads */
if($v === 'export'){
  $what = $_GET['what'] ?? '';
  header('Content-Type: text/csv; charset=utf-8');
  header('Content-Disposition: attachment; filename="littlecavabears-'.(['waitlist'=>'waitlist', 'puppies'=>'puppies', 'puppy_template'=>'puppies-template'][$what] ?? 'inquiries').($what === 'puppy_template' ? '' : '-'.date('Y-m-d')).'.csv"');
  $out = fopen('php://output', 'w');
  fwrite($out, "\xEF\xBB\xBF");
  $safe = fn($row)=>array_map(fn($c)=>preg_match('/^[=+\-@\t\r]/', (string)$c) ? "'".$c : $c, $row);   /* no spreadsheet formulas from form text */
  if($what === 'puppy_template' || $what === 'puppies'){
    fputcsv($out, PUPPY_CSV);
    if($what === 'puppy_template'){ $ex = ['name'=>'Honey', 'sex'=>'Female', 'color'=>'Apricot', 'markings'=>'', 'dob'=>'2026-08-28', 'ready'=>'2026-10-23', 'price'=>'1500', 'status'=>'Available', 'size'=>'Mini', 'adult_weight'=>'12–16 lbs', 'weight'=>'3.2 lb', 'registry'=>'AKC', 'reg_no'=>'Litter HL5551234', 'chip_no'=>'', 'champion'=>'No', 'dam'=>'Maple', 'sire'=>'Duke', 'vet_date'=>'2026-10-20', 'vaccines'=>'DHPP #1 — 6 weeks | DHPP #2 — 9 weeks', 'tagline'=>'Sweet apricot girl with a wavy teddy-bear coat', 'description'=>'Honey is the calm one of the litter.', 'video'=>'', 'photos'=>'', 'old_slug'=>'']; fputcsv($out, array_map(fn($k)=>$ex[$k] ?? '', PUPPY_CSV)); }
    else { $pn = fn($id)=>($x = find_by($GLOBALS['SITE']['parents'], 'id', (string)$id)) ? $x['name'] : '';
      foreach($GLOBALS['SITE']['puppies'] as $p) fputcsv($out, $safe([$p['name'], ucfirst($p['sex']), $p['color'], $p['dob'] ?? '', $p['ready'] ?? '', $p['price'] ?? '', PUPPY_STATUSES[$p['status']] ?? $p['status'], $p['size'] ?? '', $p['weight'] ?? '',
        $p['registry'] ?? '', $p['reg_no'] ?? '', $p['chip_no'] ?? '', $pn($p['dam'] ?? ''), $pn($p['sire'] ?? ''), $p['vet_date'] ?? '', str_replace("\n", ' | ', $p['vaccines'] ?? ''), $p['tagline'] ?? '', $p['description'] ?? '', $p['video'] ?? ''])); }
    exit;
  }
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
$VIEWS = ['dashboard'=>'Dashboard', 'inquiries'=>'Inquiries', 'puppies'=>'Puppies', 'parents'=>'Parents', 'reviews'=>'Reviews', 'articles'=>'Guides & blog', 'settings'=>'Settings',
          'litters'=>'Litters', 'waitlist'=>'Waitlist', 'pages'=>'Pages', 'locations'=>'Locations', 'promises'=>'Guarantees', 'delivery'=>'Delivery', 'payments'=>'Payments',
          'templates'=>'Email templates', 'redirects'=>'Redirects', 'account'=>'Sign-in'];
const MAIN_MENU = ['dashboard', 'inquiries', 'puppies', 'parents', 'reviews', 'articles', 'settings'];
$SUB = ['puppy_import'=>'puppies', 'puppy'=>'puppies', 'parent'=>'parents', 'inquiry'=>'inquiries', 'review'=>'reviews', 'article'=>'articles', 'page'=>'pages'];
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
.top{background:#2e221d;color:#eef1f4;padding:10px 18px;display:flex;align-items:center;gap:14px;flex-wrap:wrap}.top a{color:#eef1f4;text-decoration:none}.top strong{margin-right:auto;font-family:Georgia,serif;font-size:1.1rem}
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
.more{position:relative}.more summary{list-style:none;cursor:pointer;padding:7px 11px;border-radius:8px;font-weight:550}.more summary::-webkit-details-marker{display:none}.more summary::after{content:' ▾'}.more.on summary{background:var(--br);color:#fff}.more>div{position:absolute;z-index:5;top:110%;left:0;background:#fff;border:1px solid var(--line);border-radius:10px;box-shadow:0 10px 30px rgba(0,0,0,.12);padding:6px;display:grid;min-width:200px}.more>div a{padding:8px 10px}
.tabs{display:flex;gap:6px;flex-wrap:wrap;margin-bottom:14px}.tabs a{padding:6px 12px;border-radius:999px;border:1px solid var(--line);text-decoration:none;color:var(--ink);background:#fff}.tabs a.on{background:var(--br);color:#fff;border-color:var(--br)}
details summary{cursor:pointer;font-weight:600;margin-bottom:8px}
.acts{display:grid;gap:12px}@media(min-width:900px){.acts{grid-template-columns:1fr 1fr}}.act{border:1px solid var(--line);border-radius:10px;padding:12px 14px;background:#fffdf9}.act p{margin:6px 0 10px}.act form{margin:0}.act details{margin-top:8px}.act table{margin:6px 0}
.tot-row{display:flex;flex-wrap:wrap;gap:6px 18px;margin:-4px 0 14px;font-size:.92rem;color:var(--mut)}.tot-row b{color:var(--ink)}
.btn[disabled]{opacity:.5;cursor:not-allowed}
.help{font-size:.85rem;color:var(--mut);background:#fbf6ee;border:1px solid var(--line);border-radius:8px;padding:8px 10px;margin:-4px 0 12px}
</style></head><body>
<?php if(is_admin()): ?>
<div class="top"><strong><?= h($CFG['brand']) ?> admin</strong><a href="<?= h(site_url()) ?>" target="_blank">View website ↗</a><a href="<?= h(self_url('logout')) ?>">Sign out</a></div>
<?php $waiting = count(array_filter($GLOBALS['SITE']['reviews'], fn($r)=>!empty($r['pending']))); $cur = $SUB[$v] ?? $v; ?>
<nav class="side"><?php foreach(MAIN_MENU as $k): ?><a class="<?= $cur === $k ? 'on' : '' ?>" href="<?= h(self_url($k)) ?>"><?= h($VIEWS[$k]) ?><?= $k === 'inquiries' && $new ? '<span class="pill">'.$new.'</span>' : '' ?><?= $k === 'reviews' && $waiting ? '<span class="pill">'.$waiting.'</span>' : '' ?></a><?php endforeach; ?>
  <details class="more<?= in_array($cur, MAIN_MENU, true) ? '' : ' on' ?>"><summary>More<?= in_array($cur, MAIN_MENU, true) ? '' : ': '.h($VIEWS[$cur] ?? '') ?></summary><div><?php foreach($VIEWS as $k=>$label) if(!in_array($k, MAIN_MENU, true)): ?><a class="<?= $cur === $k ? 'on' : '' ?>" href="<?= h(self_url($k)) ?>"><?= h($label) ?></a><?php endif; ?></div></details></nav>
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
    [count($SITE['parents']) > 0, 'Add your adult cavapoos (the parents) with their health testing', 'parents', ''],
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
    <div class="card"><div class="stat"><?= count(array_filter($SITE['reviews'], fn($r)=>empty($r['pending']))) ?></div>reviews · <a href="<?= h(self_url('review')) ?>">add a review</a></div>
  </div>
  <div class="grid g2">
    <div class="card"><h2 style="margin-top:0">Latest inquiries</h2>
      <?php if(!$inq): ?><p class="mut">None yet. They appear here and are emailed to <?= h($CFG['email']) ?>.</p><?php endif; ?>
      <table><?php foreach(array_slice($inq, 0, 8) as $q): ?><tr><td><a href="<?= h(self_url('inquiry', ['ref'=>$q['ref']])) ?>"><?= h($q['name'] !== '' ? $q['name'] : $q['email']) ?></a><br><span class="mut"><?= h($q['puppy_name'] ?: 'Message') ?> · <?= h(date('M j, g:ia', strtotime($q['time']))) ?></span></td><td><?= badge($q['status'], INQUIRY_STATUSES) ?></td></tr><?php endforeach; ?></table></div>
    <div class="card"><h2 style="margin-top:0">Launch checklist</h2><ul class="chk">
      <?php foreach($checks as [$ok, $label, $view, $tab]): ?><li class="<?= $ok ? 'done' : 'todo' ?>"><?= $ok ? '✓' : '○' ?> <?= $ok ? h($label) : '<a href="'.h(self_url($view, $tab ? ['tab'=>$tab] : [])).'">'.h($label).'</a>' ?></li><?php endforeach; ?>
    </ul><p class="mut">Sitemap for Google: <a href="<?= h(site_url('sitemap.xml')) ?>" target="_blank"><?= h(rtrim($CFG['domain'], '/')) ?>/sitemap.xml</a></p></div>
  </div>
  <?php if(empty($CFG['pretty_urls']) && empty($CFG['pretty_auto'])): ?><script>fetch('rewrite-check',{cache:'no-store'}).then(r=>r.ok&&r.json()).then(j=>{if(j&&j.clean_urls)location.reload()}).catch(()=>{});</script><?php endif;
}

function view_puppies(){
  global $SITE;
  ?><div class="row"><h1>Puppies</h1><a class="btn btn-g right" href="<?= h(self_url('puppy_import')) ?>">Import from a spreadsheet</a><a class="btn" href="<?= h(self_url('puppy')) ?>">+ Add a puppy</a></div>
  <p class="mut">Restock by adding new puppies here. Every puppy that isn’t hidden is shown on the site; add photos any time. Mark them Reserved when a buyer confirms and Sold after delivery — sold puppies stay on the site as “gone to loving homes”, which builds trust.</p>
  <div class="tw"><table><tr><th></th><th>Puppy</th><th>Price</th><th>Born</th><th>Status</th><th>Quick change</th></tr>
  <?php foreach($SITE['puppies'] as $p): ?><tr>
    <?php $ph = ''; foreach($p['photos'] ?? [] as $f) if(is_file(BB_ROOT.'/'.$f)){ $ph = thumb($f, 160); break; } if($ph === '') $ph = stream_thumb($p['video'] ?? ''); ?>
    <td style="width:76px"><?php if($ph !== ''): ?><img src="<?= h($ph) ?>" alt="" style="width:64px;height:50px;object-fit:cover;border-radius:6px"><?php endif; ?></td>
    <td><a href="<?= h(self_url('puppy', ['id'=>$p['id']])) ?>"><strong><?= h($p['name']) ?></strong></a> <?= !empty($p['hidden']) ? '<span class="badge">Hidden</span>' : ($ph === '' ? '<span class="badge" title="Shown on the site with “Photos coming soon” until you add a photo">No photo yet</span>' : '') ?><br><span class="mut"><?= h($p['color'].' · '.ucfirst($p['sex'])) ?></span></td>
    <td><?= ($p['price'] ?? '') !== '' ? h(money($p['price'])) : '—' ?></td><td><?= h($p['dob'] ? date('M j, Y', strtotime($p['dob'])) : '—') ?></td><td><?= badge($p['status'], PUPPY_STATUSES) ?></td>
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
  ?><div class="row"><h1><?= $new ? 'Add a puppy' : h($p['name']) ?></h1><?php if(!$new): ?><a class="btn btn-g right" target="_blank" href="<?= h(site_url('cavapoo-puppies-for-sale/'.$p['slug'])) ?>">View on site ↗</a><?php endif; ?></div>
  <?= form_start('save_puppy', true) ?><input type="hidden" name="id" value="<?= h($p['id']) ?>">
  <div class="card"><h2 style="margin-top:0">The basics</h2><div class="grid g3">
    <label>Name *<input type="text" name="name" value="<?= h($p['name']) ?>" required></label>
    <label>Sex<select name="sex"><option value="male"<?= $p['sex'] === 'male' ? ' selected' : '' ?>>Male</option><option value="female"<?= $p['sex'] === 'female' ? ' selected' : '' ?>>Female</option></select></label>
    <label>Color and markings<small>e.g. Tricolor, Lemon &amp; White, Red &amp; White</small><input type="text" name="color" value="<?= h($p['color']) ?>"></label>
    <label>Price (USD)<input type="text" name="price" value="<?= h($p['price']) ?>" inputmode="decimal"></label>
    <label>Status<select name="status"><?php foreach(PUPPY_STATUSES as $k=>$l): ?><option value="<?= $k ?>"<?= $p['status'] === $k ? ' selected' : '' ?>><?= h($l) ?></option><?php endforeach; ?></select></label>
    <label>Expected adult size<select name="size"><option value="">Not sure yet</option><option value="toy"<?= $p['size'] === 'toy' ? ' selected' : '' ?>>Toy (6–12 lb)</option><option value="mini"<?= $p['size'] === 'mini' ? ' selected' : '' ?>>Mini (12–18 lb)</option><option value="standard"<?= $p['size'] === 'standard' ? ' selected' : '' ?>>Standard (18–25 lb)</option></select></label>
    <label>Date of birth<input type="date" name="dob" value="<?= h($p['dob']) ?>"></label>
    <label>Ready for delivery<small>Leave empty if ready now</small><input type="date" name="ready" value="<?= h($p['ready']) ?>"></label>
    <label>Current weight<small>optional, e.g. 4.2 lb at 8 weeks</small><input type="text" name="weight" value="<?= h($p['weight']) ?>"></label>
    <label>Expected adult weight<small>e.g. 15–35 lbs</small><input type="text" name="adult_weight" value="<?= h($p['adult_weight'] ?? '') ?>"></label>
    <label>Markings<small>optional</small><input type="text" name="markings" value="<?= h($p['markings'] ?? '') ?>"></label>
    <label class="cb" style="margin-top:28px"><input type="checkbox" name="champion" value="1"<?= !empty($p['champion']) ? ' checked' : '' ?>> Champion bloodline</label>
  </div>
  <label>One-line summary<small>Shown under the name, e.g. “Sweet apricot girl with a wavy teddy-bear coat”</small><input type="text" name="tagline" value="<?= h($p['tagline']) ?>"></label></div>

  <div class="card"><h2 style="margin-top:0">Registration and health</h2><div class="grid g3">
    <label>Registry<small>Leave empty to use <?= h($CFG['registry'] ?: 'your registry from Settings') ?></small><input type="text" name="registry" value="<?= h($p['registry']) ?>"></label>
    <label>Registration / litter number<input type="text" name="reg_no" value="<?= h($p['reg_no']) ?>"></label>
    <label>Vet check date<input type="date" name="vet_date" value="<?= h($p['vet_date']) ?>"></label>
    <label>Dam (mom)<select name="dam"><?= parent_options('dam', $p['dam']) ?></select><small>or describe: </small><input type="text" name="dam_text" value="<?= h($p['dam_text'] ?? '') ?>" placeholder="e.g. Cavapoo (25-30 lbs)"></label>
    <label>Sire (dad)<select name="sire"><?= parent_options('sire', $p['sire']) ?></select><small>or describe: </small><input type="text" name="sire_text" value="<?= h($p['sire_text'] ?? '') ?>" placeholder="e.g. Cavapoo (25-30 lbs)"></label>
    <label class="cb" style="margin-top:28px"><input type="checkbox" name="microchip" value="1"<?= $p['microchip'] ? ' checked' : '' ?>> Microchipped</label>
    <label>Microchip number<small>Private: only on the buyer’s sales agreement</small><input type="text" name="chip_no" value="<?= h($p['chip_no'] ?? '') ?>"></label>
  </div>
  <label>Shots and deworming given<small>One per line, e.g. “DHPP #1 — 6 weeks (Sept 3)”</small><textarea name="vaccines"><?= h($p['vaccines']) ?></textarea></label>
  <?php if(!$SITE['parents']): ?><p class="help">Add the parents under <a href="<?= h(self_url('parent')) ?>">Parents</a> first and they’ll appear here, with their own photos and health testing on the puppy’s page.</p><?php endif; ?></div>

  <div class="card"><h2 style="margin-top:0">Description</h2>
    <label>About this puppy<small>Personality, how they play, what kind of home suits them. Original, specific detail helps both buyers and Google. <?= h('**bold**, "- " bullets, blank line = new paragraph.') ?></small><textarea name="description" rows="8"><?= h($p['description']) ?></textarea></label>
    <label>Video link<small>A YouTube link shows the video on the page; another https:// link shows a “Watch video” button</small><input type="url" name="video" value="<?= h($p['video']) ?>"></label>
    <?= photos_editor($p['photos']) ?></div>

  <div class="card"><details><summary>Search engine &amp; address (optional)</summary>
    <label>Page address<small>cavapoo-puppies-for-sale/<b>…</b> — made from the name, color and sex if left empty</small><input type="text" name="slug" value="<?= h($p['slug']) ?>"></label>
    <label>Title for Google<small>Leave empty for “<?= h(($p['name'] ?: 'Name').': Color Sex Cavapoo Puppy for Sale | '.$CFG['brand']) ?>”</small><input type="text" name="seo_title" value="<?= h($p['seo_title']) ?>"></label>
    <label>Description for Google<small>Leave empty to write one from the puppy’s details</small><textarea name="seo_desc" rows="2"><?= h($p['seo_desc']) ?></textarea></label>
  </details>
  <label class="cb"><input type="checkbox" name="hidden" value="1"<?= $p['hidden'] ? ' checked' : '' ?>> Hide from the website</label>
  <?php if(waitlist()): ?><label class="cb"><input type="checkbox" name="notify" value="1"> Email this puppy to the <?= count(waitlist()) ?> people on the waitlist when I save</label><?php endif; ?>
  <button class="btn"><?= $new ? 'Add puppy' : 'Save puppy' ?></button></div></form>
  <?php if(!$new) echo '<p>'.del_button('delete_puppy', ['id'=>$p['id']], 'Delete puppy', 'Delete '.$p['name'].' and their photos? To keep them as “gone home”, mark them Sold instead.').'</p>';
}

function view_puppy_import(){
  ?><h1>Import puppies from a spreadsheet</h1>
  <div class="card"><ol>
    <li><a href="<?= h(self_url('export', ['what'=>'puppy_template'])) ?>">Download the template</a> and open it in Excel, Numbers or Google Sheets (or use your own spreadsheet with similar column names).</li>
    <li>One row per puppy. Only <b>name</b> is required; fill in what you know.</li>
    <li>Save it as <b>CSV</b> (File → Save As / Download → CSV) and upload it below.</li>
    <li>Then open each puppy under Puppies and add their photos.</li></ol>
  <?= form_start('import_puppies', true) ?><label>CSV file<input type="file" name="csv" accept=".csv,text/csv,text/plain" required></label><button class="btn">Import puppies</button></form></div>
  <div class="card"><h2 style="margin-top:0">Columns</h2><table>
    <tr><td><b>name</b></td><td>The puppy’s name. A puppy with the same name is <b>updated</b>; a new name adds a puppy.</td></tr>
    <tr><td>sex</td><td>Male or Female</td></tr><tr><td>color</td><td>e.g. Tricolor, Lemon &amp; White</td></tr>
    <tr><td>dob, ready, vet_date</td><td>Dates: 2026-08-28 or 8/28/2026. “ready” is when they can go home.</td></tr>
    <tr><td>price</td><td>e.g. 1500</td></tr><tr><td>status</td><td>Available, Reserved or Sold</td></tr><tr><td>size</td><td>Toy, Mini or Standard (expected adult size)</td></tr>
    <tr><td>weight, registry, reg_no</td><td>e.g. 4.2 lb · AKC · Litter HL5551234</td></tr><tr><td>chip_no</td><td>Microchip number (private: only on the sales agreement)</td></tr>
    <tr><td>dam, sire</td><td>The parents’ names, exactly as under Parents (add the parents first)</td></tr>
    <tr><td>vaccines</td><td>Separate several with “ | ”</td></tr><tr><td>tagline, description, video</td><td>One-line summary, the full description, a YouTube link</td></tr>
  </table><p class="mut">Common column names like “Puppy name”, “Gender”, “Date of birth”, “Mother”, “Father” or “Microchip” are recognized too. <a href="<?= h(self_url('export', ['what'=>'puppies'])) ?>">Download all puppies as CSV</a></p></div><?php
}

function view_parents(){
  global $SITE;
  ?><div class="row"><h1>Parents</h1><a class="btn right" href="<?= h(self_url('parent')) ?>">+ Add a parent</a></div>
  <p class="mut">Your adult cavapoos appear on the “Our dogs” page and on each of their puppies’ pages. Registration and health testing here are the strongest trust signals on the site.</p>
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
    <label>Title<small>optional — e.g. “Spring 2027 mini litter”</small><input type="text" name="title" value="<?= h($edit['title']) ?>"></label>
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
    <td><a href="<?= h(self_url('inquiry', ['ref'=>$q['ref']])) ?>"><strong><?= h($q['name'] !== '' ? $q['name'] : $q['email']) ?></strong></a><br><span class="mut"><?= h($q['phone'] ?: $q['email']) ?></span></td>
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
  ?><div class="row"><h1><?= h($q['name'] !== '' ? $q['name'] : $q['email']) ?></h1><span class="right"><?= badge($q['status'], INQUIRY_STATUSES) ?></span></div>
  <p class="mut"><?= h($q['kind'] === 'inquiry' ? 'Puppy inquiry' : 'Contact message') ?> · <?= h($q['ref']) ?> · <?= h(date('l, F j, Y \a\t g:ia', strtotime($q['time']))) ?></p>
  <?php if(($q['puppy_id'] ?? '') !== '') inquiry_actions($q); else echo '<div class="card"><p class="mut" style="margin:0">They haven’t chosen a puppy yet. Call or text them, then choose their puppy under <b>Edit details</b> to unlock the one-click emails, the sales agreement and payments.</p></div>'; ?>
  <div class="grid g2"><div class="card">
    <h2 style="margin-top:0">Contact</h2>
    <p><a href="mailto:<?= h($q['email']) ?>?subject=<?= rawurlencode(($q['puppy_name'] ? 'Your inquiry for '.$q['puppy_name'] : 'Your message').' — '.$CFG['brand']) ?>"><?= h($q['email']) ?></a><br>
      <?php if($q['phone'] !== ''): ?><a href="tel:<?= h($tel) ?>"><?= h($q['phone']) ?></a> · <a href="sms:<?= h($tel) ?>">Text</a><?php endif; ?></p>
    <?php if($q['notes'] !== ''): ?><h2>Their note</h2><p style="white-space:pre-wrap"><?= h($q['notes']) ?></p><?php endif; ?>
    <?php if(($q['address1'] ?? '') !== ''): ?><p><strong>Delivery address</strong><br><?= h($q['address1']) ?><?= ($q['address2'] ?? '') !== '' ? '<br>'.h($q['address2']) : '' ?><br><?= h(trim($q['city'].', '.$q['state'].' '.$q['zip'], ', ')) ?></p><?php endif; ?>
    <?php if(($q['delivery_name'] ?? '') !== ''): ?><p><strong>Delivery:</strong> <?= h($q['delivery_name']) ?> — <?= $q['delivery_fee'] !== '' ? h(money($q['delivery_fee'])) : 'to be quoted' ?></p><?php endif; ?>
    <?php if(($q['payment_name'] ?? '') !== ''): ?><p><strong>Payment:</strong> <?= h($q['payment_name']) ?></p><?php endif; ?>
    <details<?= ($q['address1'] ?? '') === '' && ($q['puppy_id'] ?? '') !== '' ? ' open' : '' ?>><summary>Edit details: puppy, delivery address, delivery and payment</summary>
      <p class="mut">Fill these in once you’ve talked. They go into the sales agreement and the emails.</p>
      <?= form_start('save_inquiry_details') ?><input type="hidden" name="ref" value="<?= h($q['ref']) ?>">
      <label>Puppy<select name="puppy"><option value="">Not chosen yet</option><?php foreach($SITE['puppies'] as $pp): ?><option value="<?= h($pp['id']) ?>"<?= $pp['id'] === ($q['puppy_id'] ?? '') ? ' selected' : '' ?>><?= h($pp['name'].' — '.$pp['color'].' '.$pp['sex'].' ('.(PUPPY_STATUSES[$pp['status']] ?? $pp['status']).')') ?></option><?php endforeach; ?></select></label>
      <div class="grid g3"><label>Name<input type="text" name="name" value="<?= h($q['name']) ?>"></label><label>Phone<input type="tel" name="phone" value="<?= h($q['phone']) ?>"></label><label>Email<input type="email" name="email" value="<?= h($q['email']) ?>"></label></div>
      <label>Street address<input type="text" name="address1" value="<?= h($q['address1'] ?? '') ?>"></label>
      <div class="grid g4"><label>Apt / suite<input type="text" name="address2" value="<?= h($q['address2'] ?? '') ?>"></label><label>City<input type="text" name="city" value="<?= h($q['city'] ?? '') ?>"></label>
        <label>State<select name="state"><option value="">—</option><?php foreach(US_STATES as $k=>$v): ?><option value="<?= $k ?>"<?= ($q['state'] ?? '') === $k ? ' selected' : '' ?>><?= h($v) ?></option><?php endforeach; ?></select></label><label>ZIP<input type="text" name="zip" value="<?= h($q['zip'] ?? '') ?>"></label></div>
      <div class="grid g2"><label>Delivery<select name="delivery"><option value="">Not chosen yet</option><?php foreach($SITE['delivery']['methods'] as $m): ?><option value="<?= h($m['key']) ?>"<?= ($q['delivery'] ?? '') === $m['key'] ? ' selected' : '' ?>><?= h($m['label'].' ('.$m['days'].')') ?></option><?php endforeach; ?></select></label>
        <label>Payment method<select name="payment"><option value="">Not chosen yet</option><?php foreach($SITE['payments'] as $pm): ?><option value="<?= h($pm['key']) ?>"<?= ($q['payment'] ?? '') === $pm['key'] ? ' selected' : '' ?>><?= h($pm['name']) ?></option><?php endforeach; ?></select></label></div>
      <button class="btn btn-s">Save details</button></form></details>
  </div><div>
    <?php if($p): ?><div class="card"><h2 style="margin-top:0">Puppy: <a href="<?= h(self_url('puppy', ['id'=>$p['id']])) ?>"><?= h($p['name']) ?></a></h2>
      <p><?= badge($p['status'], PUPPY_STATUSES) ?> · <?= $p['price'] !== '' ? h(money($p['price'])).' · deposit '.h(money(round((float)$p['price'] * (int)$CFG['deposit_pct'] / 100, 2))) : '' ?></p>
      <?= form_start('puppy_status') ?><input type="hidden" name="id" value="<?= h($p['id']) ?>"><input type="hidden" name="back" value="inquiry"><input type="hidden" name="ref" value="<?= h($q['ref']) ?>">
        <div class="row"><select name="status" style="width:auto"><?php foreach(PUPPY_STATUSES as $k=>$l): ?><option value="<?= $k ?>"<?= $p['status'] === $k ? ' selected' : '' ?>><?= h($l) ?></option><?php endforeach; ?></select><button class="btn btn-g btn-s">Update puppy</button></div></form></div>
    <?php elseif($q['puppy_name']): ?><div class="card"><p>Puppy: <?= h($q['puppy_name']) ?> (no longer listed)</p></div><?php endif; ?>
    <div class="card"><?= form_start('save_inquiry') ?><input type="hidden" name="ref" value="<?= h($q['ref']) ?>">
      <label>Status<select name="status"><?php foreach(INQUIRY_STATUSES as $k=>$l): ?><option value="<?= $k ?>"<?= $q['status'] === $k ? ' selected' : '' ?>><?= h($l) ?></option><?php endforeach; ?></select></label>
      <label>Your private notes<small>Deposit received, delivery plans…</small><textarea name="admin_notes" rows="6"><?= h($q['admin_notes'] ?? '') ?></textarea></label>
      <button class="btn">Save</button></form></div>
    <?php if(!empty($q['emails'])): ?><div class="card"><h2 style="margin-top:0">Emails sent</h2><table><?php foreach(array_reverse($q['emails']) as $e): ?><tr><td class="mut" style="white-space:nowrap"><?= h(date('M j, g:ia', strtotime($e['time']))) ?></td><td><?= h($e['subject']) ?><?= empty($e['ok']) ? ' <span class="badge b-reserved">Failed</span>' : '' ?></td></tr><?php endforeach; ?></table></div><?php endif; ?>
    <p><?= del_button('delete_inquiry', ['ref'=>$q['ref']], 'Delete inquiry') ?></p>
  </div></div><?php
}

/* a page of the public site from the admin (works on any host and with or without clean addresses) */
function site_page($path, $query){ $u = site_url($path); return $u.(strpos($u, '?') !== false ? '&' : '?').http_build_query($query); }

/* the one-click emails, the sales agreement and payments for one inquiry */
function inquiry_actions($q){
  global $SITE, $CFG;
  $d = deal_of($q); $t = deal_totals($q);
  $sent = function($key) use($q){ $last = null; foreach($q['emails'] ?? [] as $e) if($e['key'] === $key && !empty($e['ok'])) $last = $e; return $last ? 'Sent '.date('M j, g:ia', strtotime($last['time'])) : ''; };
  $tpl_box = function($key, $hint) use($q, $SITE, $sent){
    $t = $SITE['templates'][$key];
    $preview_extra = ['date'=>!empty($t['needs_date']) ? '[departure date]' : '', 'note'=>''];
    $missing = $key === 'payment' && template_vars($q)['{payment_details}'] === '';
    ?><div class="act"><div class="row"><strong><?= h($t['name']) ?></strong><span class="mut right"><?= h($sent($key)) ?></span></div>
      <p class="mut"><?= h($hint) ?></p>
      <?php if($missing): ?><p class="help">Add your <?= h($q['payment_name'] ?: 'payment') ?> details under <a href="<?= h(self_url('payments')) ?>">Payments</a> to send this in one click.</p><?php endif; ?>
      <?= form_start('send_template') ?><input type="hidden" name="ref" value="<?= h($q['ref']) ?>"><input type="hidden" name="key" value="<?= h($key) ?>">
        <div class="row"><?php if(!empty($t['needs_date'])): ?><input type="date" name="date" required style="width:auto;margin:0"><?php endif; ?>
        <button class="btn btn-s"<?= $missing ? ' disabled' : '' ?> onclick="return confirm('Send “<?= h($t['name']) ?>” to <?= h($q['email']) ?>?')">Send now</button></div></form>
      <details><summary>Preview &amp; edit</summary><?= form_start('send_template') ?><input type="hidden" name="ref" value="<?= h($q['ref']) ?>"><input type="hidden" name="key" value="<?= h($key) ?>">
        <label>Subject<input type="text" name="subject" value="<?= h(template_render($t['subject'], $q, $preview_extra)) ?>"></label>
        <label>Message<textarea name="body" rows="12"><?= h(template_render($t['body'], $q, $preview_extra)) ?></textarea></label>
        <button class="btn btn-s">Send this version</button></form></details></div><?php
  };
  $ag = $q['agreement'] ?? [];
  ?><div class="card"><h2 style="margin-top:0">Next steps</h2>
  <div class="tot-row"><span>Puppy <b><?= h(money($d['price'])) ?></b></span><span>Delivery <b><?= $d['delivery_fee'] !== null ? h(money($d['delivery_fee'])) : '—' ?></b></span><span>Total <b><?= h(money($t['total'])) ?></b></span>
    <span>Deposit <b><?= h(money($d['deposit'])) ?></b></span><span>Paid <b><?= h(money($t['paid'])) ?></b></span><span>Still to pay <b><?= h(money($t['due'])) ?></b></span></div>
  <div class="acts">
    <?php $tpl_box('welcome', 'Thanks them for their inquiry about '.$d['puppy'].', with the price, delivery options and a link to the puppy’s page.'); ?>
    <div class="act"><div class="row"><strong>Sales agreement</strong><span class="mut right"><?= !empty($ag['accepted']) ? '✓ Accepted' : (!empty($ag['sent']) ? 'Sent '.h(date('M j, g:ia', strtotime($ag['sent']))) : '') ?></span></div>
      <p class="mut"><?= !empty($ag['accepted']) ? 'Accepted online by “'.h($ag['accepted_name']).'” on '.h(date('M j, Y \a\t g:ia', strtotime($ag['accepted']))).'.' : 'Emails a link to their agreement: puppy, price, deposit, delivery, guarantees. They read it, accept it by typing their name, and can print it.' ?></p>
      <div class="row"><?= form_start('send_agreement') ?><input type="hidden" name="ref" value="<?= h($q['ref']) ?>"><button class="btn btn-s" onclick="return confirm('Email the sales agreement to <?= h($q['email']) ?>?')"><?= empty($ag['sent']) ? 'Send agreement' : (empty($ag['accepted']) ? 'Send updated agreement' : 'Resend link') ?></button></form>
        <a class="btn btn-g btn-s" target="_blank" href="<?= h(site_page('agreement', ['ref'=>$q['ref'], 'k'=>sign('agreement|'.$q['ref'])])) ?>">Open / print ↗</a></div></div>
    <?php $tpl_box('payment', 'Their '.$d['deposit_pct'].'% deposit ('.money($d['deposit']).'), your '.($q['payment_name'] ?: 'payment').' details and the agreement link.'); ?>
    <div class="act"><div class="row"><strong>Record a payment</strong><span class="mut right"><?= count($q['payments'] ?? []) ?> recorded</span></div>
      <?php if(!empty($q['payments'])): ?><table><?php foreach($q['payments'] as $pm): ?><tr><td><?= h(nice_date($pm['date'])) ?></td><td><?= h($pm['method']) ?></td><td><b><?= h(money($pm['amount'])) ?></b></td><td><?= del_button('delete_payment', ['ref'=>$q['ref'], 'n'=>$pm['n']], '×', 'Remove this payment?') ?></td></tr><?php endforeach; ?></table>
        <p><a target="_blank" href="<?= h(site_page('receipt', ['ref'=>$q['ref'], 'k'=>sign('receipt|'.$q['ref'])])) ?>">Open / print receipt ↗</a></p><?php endif; ?>
      <?= form_start('record_payment') ?><input type="hidden" name="ref" value="<?= h($q['ref']) ?>"><div class="grid g3">
        <label>Amount ($)<input type="text" name="amount" inputmode="decimal" value="<?= h($t['deposit_left'] > 0 ? $t['deposit_left'] : ($t['due'] > 0 ? $t['due'] : '')) ?>"></label>
        <label>Method<select name="method"><?php foreach($SITE['payments'] as $pm): ?><option<?= $pm['name'] === ($q['payment_name'] ?? '') ? ' selected' : '' ?>><?= h($pm['name']) ?></option><?php endforeach; ?></select></label>
        <label>Date<input type="date" name="date" value="<?= date('Y-m-d') ?>"></label></div>
        <label class="cb"><input type="checkbox" name="send" value="1" checked> Email them a receipt</label>
        <button class="btn btn-s">Record payment</button></form></div>
    <?php $tpl_box('reserved', 'Confirms the reservation with their receipt and agreement links, and marks '.$d['puppy'].' Reserved.'); ?>
    <?php $tpl_box('delivery', 'Departure date, '.($d['delivery'] ?: 'delivery').', the balance due at delivery and what to have ready.'); ?>
    <?php $tpl_box('followup', 'A friendly nudge if they’ve gone quiet, with the waitlist as a fallback.'); ?>
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
  $pending = array_values(array_filter($SITE['reviews'], fn($r)=>!empty($r['pending'])));
  ?><div class="row"><h1>Reviews</h1><a class="btn right" href="<?= h(self_url('review')) ?>">+ Add a review</a></div>
  <p class="mut">Add the reviews your customers give you — word for word, with their permission, and only from real buyers. Made-up reviews break FTC rules and Google’s policies.</p>
  <?php if($pending): ?><div class="card"><h2 style="margin-top:0">Waiting for your approval (<?= count($pending) ?>)</h2>
    <?php foreach($pending as $r): ?><div class="act" style="margin-bottom:10px"><div class="row"><strong><?= h($r['name']) ?></strong><span class="mut"><?= h(trim(($r['location'] ?? '').' · '.($r['puppy'] ?? ''), ' ·')) ?></span><span class="right"><?= str_repeat('★', (int)$r['rating']) ?></span></div>
      <p style="white-space:pre-wrap"><?= h($r['text']) ?></p><?php if(($r['photo'] ?? '') !== ''): ?><p><img src="<?= h(thumb($r['photo'], 320)) ?>" alt="" style="max-width:200px;border-radius:8px"></p><?php endif; ?>
      <div class="row"><?= form_start('approve_review') ?><input type="hidden" name="id" value="<?= h($r['id']) ?>"><button class="btn btn-s">Approve</button></form><a class="btn btn-g btn-s" href="<?= h(self_url('review', ['id'=>$r['id']])) ?>">Edit</a><?= del_button('delete_review', ['id'=>$r['id']], 'Delete', 'Delete this review?') ?></div></div><?php endforeach; ?></div><?php endif; ?>
  <div class="tw"><table><tr><th>Family</th><th>Puppy</th><th>Date</th><th>Stars</th><th></th></tr>
  <?php foreach($SITE['reviews'] as $r): ?><tr><td><a href="<?= h(self_url('review', ['id'=>$r['id']])) ?>"><strong><?= h($r['name']) ?></strong></a><br><span class="mut"><?= h($r['location']) ?></span></td><td><?= h($r['puppy']) ?></td><td><?= h($r['date']) ?></td><td><?= str_repeat('★', (int)$r['rating']) ?></td><td><?= !empty($r['pending']) ? '<span class="badge b-new">Waiting</span>' : (!empty($r['hidden']) ? '<span class="badge">Hidden</span>' : '') ?></td></tr><?php endforeach; ?>
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
  <label>Quick answer<small>40–60 words that answer the title’s question directly. Shown in a box under the heading, and the text Google is most likely to quote.</small><textarea name="answer" rows="3"><?= h($a['answer'] ?? '') ?></textarea></label>
  <label>Text *<small><?= h(FORMAT_HELP) ?></small><textarea class="big" name="body" required><?= h($a['body']) ?></textarea></label>
  <?= one_photo_editor('image', $a['image'] ?? '', 'Main photo', 'Shown at the top and when shared. Your own cavapoo photos are best.') ?>
  <h2>Photos inside the text</h2>
  <?php if(!empty($a['photos'])): ?><p class="mut">Click “Insert” to place a photo where your cursor is in the text, then change “Describe the photo” to what it shows (e.g. “Tricolor cavapoo puppy at 8 weeks”). Save to see it on the guide.</p>
  <div class="photos"><?php foreach($a['photos'] as $f): ?><figure><img src="<?= h(thumb($f, 320)) ?>" alt=""><button type="button" class="btn btn-g btn-s" data-insert="<?= h('![Describe the photo]('.$f.')') ?>">Insert</button><label class="cb"><input type="checkbox" name="remove_inline[]" value="<?= h($f) ?>"> Remove</label></figure><?php endforeach; ?></div><?php endif; ?>
  <label>Add photos for the text<small>Upload, save, then insert them where you want them</small><input type="file" name="inline[]" accept="image/jpeg,image/png,image/webp" multiple></label>
  <script>document.querySelectorAll('[data-insert]').forEach(function(b){b.addEventListener('click',function(){var t=document.querySelector('textarea[name=body]'),v=b.getAttribute('data-insert'),s=t.selectionStart||t.value.length;t.value=t.value.slice(0,s)+'\n\n'+v+'\n\n'+t.value.slice(t.selectionEnd||s);t.focus();t.selectionStart=t.selectionEnd=s+v.length+4;})});</script>
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
  <?php if($new): ?><label>Address<small>e.g. cavapoo-puppies-for-sale-kentucky</small><input type="text" name="slug" value=""></label><?php endif; ?>
  <label>Quick answer <small>Optional: 40–60 words shown in a box under the heading</small><textarea name="answer" rows="3"><?= h($pg['answer'] ?? '') ?></textarea></label>
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

function view_locations(){
  global $SITE, $CFG;
  $edit = strtoupper((string)($_GET['s'] ?? ''));
  if(isset(states()[$edit])){
    $st = states()[$edit]; $l = $SITE['locations'][$edit] ?? ['on'=>false, 'notes'=>''];
    ?><div class="row"><h1><?= h($st[0]) ?> delivery page</h1><?php if(!empty($l['on'])): ?><a class="btn btn-g right" target="_blank" href="<?= h(site_url('cavapoo-puppies-for-sale/'.slugify($st[0]))) ?>">View on site ↗</a><?php endif; ?></div>
    <p class="mut">The page already shows the puppies available, delivery prices to <?= h($st[0]) ?>, the distance from <?= h($CFG['city']) ?>, the main cities, a weather tip and local questions. Your notes make it truly local, which is what Google rewards: stories of families you’ve delivered to there, their reviews, local dog rules you know, delivery days you usually drive.</p>
    <div class="card"><?= form_start('save_location_notes') ?><input type="hidden" name="state" value="<?= h($edit) ?>">
      <label>Local notes for <?= h($st[0]) ?><small><?= h(FORMAT_HELP) ?></small><textarea class="big" name="notes"><?= h($l['notes']) ?></textarea></label>
      <button class="btn">Save notes</button> <a href="<?= h(self_url('locations')) ?>">Back to all states</a></form></div><?php
    return;
  }
  ?><h1>Locations</h1>
  <p class="mut">Each state you tick gets its own page at /cavapoo-puppies-for-sale/<i>state</i>, for people who search “cavapoo puppies for sale in Texas” and similar. Every page says clearly that puppies are raised in <?= h($CFG['city']) ?> and delivered.</p>
  <p class="help">Tick the states you really deliver to and add your own notes to each (stories, reviews, local knowledge). A few genuinely useful state pages do better on Google than dozens of near-identical ones, which Google can treat as spam.</p>
  <?= form_start('save_locations') ?><div class="tw"><table><tr><th>Page</th><th>State</th><th>Main cities</th><th>Notes</th></tr>
  <?php foreach(states() as $c=>$st): $l = $SITE['locations'][$c] ?? ['on'=>false, 'notes'=>'']; ?><tr><td><input type="checkbox" name="on[]" value="<?= h($c) ?>"<?= !empty($l['on']) ? ' checked' : '' ?>></td><td><strong><?= h($st[0]) ?></strong></td><td class="mut"><?= h(implode(', ', array_slice($st[1], 0, 3))) ?></td>
    <td><a href="<?= h(self_url('locations', ['s'=>$c])) ?>"><?= trim($l['notes']) !== '' ? 'Edit notes ✓' : 'Add notes' ?></a></td></tr><?php endforeach; ?></table></div>
  <button class="btn">Save states</button></form><?php
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

function view_templates(){
  global $SITE;
  ?><h1>Email templates</h1>
  <p class="mut">The one-click emails on each inquiry. Each starts with the buyer’s details filled in; you can also edit any email before sending it.</p>
  <p class="help">You can use: {first_name} {puppy} {puppy_link} {price} {deposit} {deposit_amount} {delivery_option} {delivery_time} {delivery_fee} {total} {due_at_delivery} {paid} {balance_due} {last_payment} {payment_method} {payment_details} {agreement_link} {receipt_link} {delivery_date} {note} {ref} {email} {phone_or_email} {city} {vet_check_hours} {care_guide_link} {waitlist_link}. Every email ends with your name and contact details automatically.</p>
  <?= form_start('save_templates') ?><?php foreach($SITE['templates'] as $k=>$t): ?><div class="card"><h2 style="margin-top:0"><?= h($t['name']) ?></h2>
    <label>Subject<input type="text" name="tpl[<?= h($k) ?>][subject]" value="<?= h($t['subject']) ?>"></label>
    <label>Message<textarea name="tpl[<?= h($k) ?>][body]" rows="12"><?= h($t['body']) ?></textarea></label></div><?php endforeach; ?>
  <button class="btn">Save templates</button></form>
  <h2>Back to the original wording</h2><div class="row"><?php foreach($SITE['templates'] as $k=>$t) echo del_button('reset_template', ['key'=>$k], 'Reset “'.$t['name'].'”', 'Replace your wording of “'.$t['name'].'” with the original?'); ?></div><?php
}

function view_redirects(){
  global $SITE;
  ?><h1>Redirects</h1>
  <p class="mut">When an address changes, the old one sends visitors (and Google) to the new one, so links people saved and the ranking the page earned aren’t lost. They’re added automatically when you rename or delete a guide, or change a puppy’s address. You can also add your own, e.g. for pages of an old website.</p>
  <div class="tw"><table><tr><th>Old address</th><th>Goes to</th><th></th></tr>
  <?php foreach($SITE['redirects'] as $from=>$to): ?><tr><td>/<?= h($from) ?></td><td><a target="_blank" href="<?= h(site_url($to)) ?>">/<?= h($to) ?></a></td><td><?= del_button('delete_redirect', ['from'=>$from], 'Remove') ?></td></tr><?php endforeach; ?>
  <?php if(!$SITE['redirects']): ?><tr><td colspan="3" class="mut">None yet.</td></tr><?php endif; ?></table></div>
  <div class="card" style="margin-top:16px"><?= form_start('add_redirect') ?><div class="grid g2"><label>Old address<input type="text" name="from" placeholder="/old-page"></label><label>New address<input type="text" name="to" placeholder="/cavapoo-puppies-for-sale"></label></div><button class="btn">Add redirect</button></form></div><?php
}

function view_payments(){
  global $SITE;
  $rows = $SITE['payments']; $rows[] = ['key'=>'', 'name'=>'', 'note'=>'', 'on'=>true];
  ?><h1>Payment methods</h1>
  <p class="mut">Private: nothing here is shown on the website. Payment methods and details are only used in the emails you send from an inquiry, after you’ve talked with the customer.</p>
  <p class="help"><strong>Payment details</strong> (your PayPal email, Zelle phone, bank account…) are private: they’re never on the website, only in the “Payment instructions” email you send from an inquiry.</p>
  <?= form_start('save_payments') ?><div class="tw"><table><tr><th>On</th><th>Name</th><th>Note on the website (optional)</th><th>Payment details (private)</th></tr>
  <?php foreach($rows as $i=>$p): ?><tr><td><input type="hidden" name="pay[<?= $i ?>][key]" value="<?= h($p['key']) ?>"><input type="checkbox" name="pay[<?= $i ?>][on]" value="1"<?= $p['on'] ? ' checked' : '' ?>></td>
    <td><input type="text" name="pay[<?= $i ?>][name]" value="<?= h($p['name']) ?>" placeholder="<?= $p['name'] === '' ? 'Add another method…' : '' ?>"></td><td><input type="text" name="pay[<?= $i ?>][note]" value="<?= h($p['note']) ?>"></td><td><textarea name="pay[<?= $i ?>][details]" rows="2" placeholder="e.g. Send to payments@… (Goods &amp; Services)"><?= h($p['details'] ?? '') ?></textarea></td></tr><?php endforeach; ?>
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
    <div class="grid g2"><?= $t('brand', 'Business name') ?><?= $t('domain', 'Website address', 'e.g. https://littlecavabears.com — used in Google links and emails') ?>
      <?= $t('email', 'Email', 'Inquiries go here, and emails are sent from it', 'email') ?><?= $t('phone', 'Phone (for Call and Text buttons)', 'e.g. (513) 555-0123. Leave empty to hide the buttons.', 'tel') ?>
      <?= $t('city', 'City') ?><div class="grid g2"><?= $t('state', 'State code', 'e.g. OH') ?><?= $t('state_name', 'State name') ?></div>
      <?= $t('registry', 'Registry', 'e.g. a doodle registry, if you register litters — shown on puppy pages') ?><?= $t('payee_name', 'Name on your payment accounts', 'Shown on the “Verify us” page so buyers can check who they’re paying') ?>
      <?= $t('reply_time', 'How quickly you reply', 'Used in sentences like “we reply the same day”') ?></div>
    <?= $ta('license_text', 'Licenses and registrations', 'One per line, e.g. “Ohio Department of Agriculture dog breeder license #12345”. Shown on Verify us. Leave empty if none apply.', 3) ?>
  <?php elseif($tab === 'deposit'): ?>
    <p class="help">These numbers fill in every page, guarantee and email automatically.</p>
    <div class="grid g4"><?= $t('deposit_pct', 'Deposit (%)', 'At least 50', 'number') ?><?= $t('vet_check_hours', 'Vet check window (hours)', 'e.g. 72', 'number') ?><?= $t('genetic_years', 'Genetic guarantee (years)', '', 'number') ?><?= $t('go_home_weeks', 'Earliest go-home age (weeks)', '8 or more', 'number') ?></div>
    <?= $ta('delivery_note', 'Delivery note', 'Shown on the home page') ?>
    <p class="mut">Payment methods are under <a href="<?= h(self_url('payments')) ?>">Payments</a>; guarantee wording under <a href="<?= h(self_url('promises')) ?>">Guarantees</a>.</p>
  <?php elseif($tab === 'about'): ?>
    <p class="help">Real people sell puppies. Your name, your photo and your story on the About page are among the strongest trust signals you can add.</p>
    <?= $t('breeder_name', 'Your name(s)', 'e.g. “Jessica and Tom”') ?><?= $ta('breeder_story', 'Your story', 'How you started with cavapoos, how puppies are raised in your home, what you love about the breed. Formatting as in guides.', 8) ?>
    <?= one_photo_editor('breeder_photo', $CFG['breeder_photo'], 'Your photo', 'You with your cavapoos') ?>
  <?php elseif($tab === 'look'): ?>
    <?= one_photo_editor('hero_photo', $CFG['hero_photo'], 'Home page photo', 'A bright, real photo of your puppies (landscape). Without one, the first available puppy’s photo is used.') ?>
    <?= $t('hero_title', 'Home page heading') ?><?= $ta('hero_lede', 'Home page introduction', '', 3) ?>
    <?= $t('puppies_title', 'Puppies page heading') ?><?= $ta('puppies_intro', 'Puppies page introduction') ?><?= $ta('parents_intro', 'Our dogs page introduction', '', 3) ?>
    <?= $ta('litters_intro', 'Upcoming litters introduction', '', 3) ?><?= $ta('reviews_intro', 'Reviews page introduction', '', 3) ?><?= $ta('contact_intro', 'Contact page introduction', '', 3) ?><?= $ta('footer_blurb', 'Footer description', '', 2) ?>
  <?php elseif($tab === 'seo'): ?>
    <div class="grid g2"><?= $t('home_seo_title', 'Home page title for Google') ?><?= $t('home_seo_desc', 'Home page description for Google') ?><?= $t('puppies_seo_title', 'Puppies page title for Google') ?><?= $t('puppies_seo_desc', 'Puppies page description for Google') ?></div>
    <p class="help">Google Search Console: add your site as a “URL prefix” property, choose the “HTML tag” method and paste just the content="…" code here. Then submit <?= h(rtrim($CFG['domain'], '/')) ?>/sitemap.xml under Sitemaps.</p>
    <div class="grid g3"><?= $t('gsc_verification', 'Google Search Console code') ?><?= $t('bing_verification', 'Bing Webmaster code') ?><?= $t('ga4_id', 'Google Analytics ID', 'e.g. G-ABC123XYZ') ?></div>
    <div class="grid g2"><?= $t('facebook', 'Facebook page', '', 'url') ?><?= $t('instagram', 'Instagram', '', 'url') ?><?= $t('tiktok', 'TikTok', '', 'url') ?><?= $t('youtube', 'YouTube', '', 'url') ?><?= $t('google_reviews_url', 'Google review link', 'From your Google Business Profile → “Ask for reviews”', 'url') ?></div>
    <?= one_photo_editor('share_photo', $CFG['share_photo'], 'Link preview image', 'Shown when your site is shared on Facebook, in texts etc. 1200 × 630 px is ideal.') ?>
    <input type="hidden" name="pretty_urls_form" value="1"><label class="cb"><input type="checkbox" name="pretty_urls" value="1"<?= !empty($CFG['pretty_urls']) ? ' checked' : '' ?>> Clean page addresses (e.g. /guides/cavapoo-lifespan). Switch off only if pages show “Not Found”.</label>
  <?php else: ?>
    <h2 style="margin-top:0">Sending email</h2>
    <p class="help">For inquiry emails to reliably reach your inbox (and your customers’), enter your mailbox’s SMTP details — from your email provider or host (e.g. cPanel → Email Accounts → Connect Devices). Leave empty to use the host’s PHP mail.</p>
    <div class="grid g3"><?= $t('smtp_host', 'SMTP server', 'e.g. mail.littlecavabears.com') ?><?= $t('smtp_port', 'Port', '587 for TLS, 465 for SSL') ?>
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
