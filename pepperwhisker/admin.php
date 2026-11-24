<?php
/* =============================================================
   Pepperwhisker — admin. Open /admin.php in your browser.
   The first visit asks you to create the admin sign-in, so do
   that straight after uploading. Everything is saved to data/
   (last 30 versions kept in data/backups/).
   ============================================================= */

define('PW_ROOT', __DIR__);
@ini_set('display_errors', '0');
error_reporting(E_ALL & ~E_NOTICE & ~E_WARNING & ~E_DEPRECATED);
require PW_ROOT.'/inc/core.php';
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
function article_path($a){ return ($a['type'] === 'blog' ? 'blog/' : 'guides/').$a['slug']; }

/* ---------------- helpers ---------------- */
const FORMAT_HELP = 'Formatting: blank line = new paragraph · "## " heading, "### " subheading · "- " bullet, "1. " numbered · **bold**, *italic* · [link text](/guides/giant-schnauzer) or [text](https://…) · "> " highlighted note · tables as | a | b | lines · a photo on its own line: ![description](assets/uploads/…) (use “Insert” under Photos). {brand} {email} {author} {year} fill in your settings. A line with only {size_chart} shows the schnauzer size drawing; {author_box} shows your author box. Questions under "## Questions" written as "### Question" become FAQs for Google.';
function self_url($v='dashboard', $extra=[]){ return 'admin.php?'.http_build_query(['v'=>$v] + $extra); }
function go($v='dashboard', $extra=[]){ header('Location: '.self_url($v, $extra), true, 303); exit; }
function note($msg, $type='ok'){ $_SESSION['admin_flash'][] = [$type, $msg]; }
function in_str($k, $max=20000){ $v = $_POST[$k] ?? ''; return is_string($v) ? mb_substr(trim(str_replace("\r", '', $v)), 0, $max) : ''; }
function in_line($k, $max=300){ return trim(preg_replace('/[\r\n\t]+/', ' ', in_str($k, $max))); }
function in_bool($k){ return !empty($_POST[$k]); }
function in_date($k){ $v = in_line($k, 20); return preg_match('/^\d{4}-\d{2}-\d{2}$/', $v) ? $v : ''; }

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
function unique_slug($base, $list, $self){
  $base = slugify($base) ?: 'item';
  $slug = $base; $n = 2;
  while(array_filter($list, fn($x)=>($x['slug'] ?? '') === $slug && ($x['slug'] ?? '') !== $self)) $slug = $base.'-'.$n++;
  return $slug;
}
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
/* one photo field (article image, author photo, home photo, share image) */
function handle_one_photo($field, $current, $prefix){
  if(!empty($_POST['remove_'.$field])){ delete_upload($current); $current = ''; }
  $r = save_upload($_FILES[$field] ?? null, $prefix);
  if($r !== '' && $r[0] === '!'){ note(substr($r, 1), 'err'); return $current; }
  if($r !== ''){ if($current !== '') delete_upload($current); return $r; }
  return $current;
}
function messages_all(){
  $out = [];
  foreach(glob(data_dir().'/messages/*.php') ?: [] as $f){ $m = data_read('messages/'.basename($f, '.php')); if($m) $out[] = $m; }
  usort($out, fn($a, $b)=>strcmp($b['time'] ?? '', $a['time'] ?? ''));
  return $out;
}
function valid_msg_id($id){ return is_string($id) && preg_match('/^\d{8}-\d{6}-[a-f0-9]{6}$/', $id); }
/* addresses the site itself uses, which a page can't take */
function reserved_slugs(){ return array_merge(['guides', 'blog', 'contact', 'sitemap.xml', 'robots.txt', 'ads.txt', 'admin', 'login', 'rewrite-check'], array_column(GUIDE_GROUPS, 1)); }

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
    if(data_write('auth', $AUTH)){ log_in(); note('Your sign-in is set up. Welcome to your Pepperwhisker admin!'); }
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
$find_slug = function($list, $slug){ foreach($list as $i=>$x){ if(($x['slug'] ?? '') === $slug) return $i; } return null; };

if($act === 'save_article'){
  $orig = in_line('orig', 120); $i = $orig !== '' ? $find_slug($SITE['articles'], $orig) : null;
  $a = $i !== null ? $SITE['articles'][$i] : ['date'=>date('Y-m-d'), 'photos'=>[]];
  $type = in_line('type') === 'blog' ? 'blog' : 'guide';
  $title = in_line('title', 200);
  if($title === '' || in_str('body') === ''){ note('A guide or post needs a title and some text.', 'err'); go('article', $orig ? ['slug'=>$orig] : ['type'=>$type]); }
  $a = array_merge($a, ['type'=>$type, 'group'=>isset(GUIDE_GROUPS[in_line('group')]) ? in_line('group') : 'breeds', 'title'=>$title,
    'answer'=>in_str('answer', 1000), 'body'=>in_str('body', 200000), 'seo_title'=>in_line('seo_title', 120), 'seo_desc'=>in_line('seo_desc', 300),
    'excerpt'=>in_line('excerpt', 300), 'image_alt'=>in_line('image_alt', 200), 'date'=>in_date('date') ?: ($a['date'] ?? date('Y-m-d')), 'updated'=>date('Y-m-d'), 'draft'=>in_bool('draft')]);
  $want = in_line('slug', 120) !== '' ? in_line('slug', 120) : $title;
  if($i === null || slugify($want) !== $orig) $a['slug'] = unique_slug($want, $SITE['articles'], $orig);
  if($i !== null && $orig !== $a['slug']){ $SITE['removed']['articles'][] = $orig; }
  if($i !== null && $orig !== $a['slug']) add_redirect(article_path(['type'=>$SITE['articles'][$i]['type'], 'slug'=>$orig]), article_path($a));
  elseif($i !== null && $SITE['articles'][$i]['type'] !== $type) add_redirect(article_path($SITE['articles'][$i]), article_path($a));
  $a['image'] = handle_one_photo('image', $a['image'] ?? '', 'guide-'.$a['slug']);
  $photos = [];
  foreach($a['photos'] ?? [] as $f){ if(in_array($f, (array)($_POST['remove_inline'] ?? []), true)) delete_upload($f); else $photos[] = $f; }
  foreach(upload_list('inline') as $f){ $r = save_upload($f, 'guide-'.$a['slug']); if($r === '') continue; if($r[0] === '!') note(substr($r, 1), 'err'); else $photos[] = $r; }
  $a['photos'] = array_slice($photos, 0, 30);
  if($i === null) $SITE['articles'][] = $a; else $SITE['articles'][$i] = $a;
  save('Saved.');
  go('article', ['slug'=>$a['slug']]);
}
if($act === 'delete_article'){
  $i = $find_slug($SITE['articles'], in_line('slug', 120));
  if($i !== null){
    $a = $SITE['articles'][$i];
    delete_upload($a['image'] ?? ''); foreach($a['photos'] ?? [] as $f) delete_upload($f);
    array_splice($SITE['articles'], $i, 1);
    $SITE['removed']['articles'][] = $a['slug'];
    add_redirect(article_path($a), 'guides');
    save('Deleted. Its address now leads to the guides page.');
  }
  go('articles');
}
if($act === 'save_page'){
  $orig = in_line('orig', 120); $i = $orig !== '' ? $find_slug($SITE['pages'], $orig) : null;
  $pg = $i !== null ? $SITE['pages'][$i] : [];
  $title = in_line('title', 200);
  if($title === ''){ note('A page needs a heading.', 'err'); go('page', $orig ? ['slug'=>$orig] : []); }
  $pg = array_merge($pg, ['title'=>$title, 'menu'=>in_line('menu', 60), 'answer'=>in_str('answer', 1000), 'body'=>in_str('body', 200000),
    'seo_title'=>in_line('seo_title', 120), 'seo_desc'=>in_line('seo_desc', 300)]);
  if($i === null){
    $slug = unique_slug(in_line('slug', 120) ?: $title, $SITE['pages'], '');
    if(in_array($slug, reserved_slugs(), true)) $slug = unique_slug($slug.'-page', $SITE['pages'], '');
    $pg['slug'] = $slug; $SITE['pages'][] = $pg;
  } else $SITE['pages'][$i] = $pg;
  save('Page saved.');
  go('page', ['slug'=>$pg['slug']]);
}
if($act === 'delete_page'){
  $i = $find_slug($SITE['pages'], in_line('slug', 120));
  if($i !== null){ $slug = $SITE['pages'][$i]['slug']; array_splice($SITE['pages'], $i, 1); $SITE['removed']['pages'][] = $slug; add_redirect($slug, ''); save('Page deleted. Its address now leads to the home page.'); }
  go('pages');
}
/* ---- photos: your own schnauzer photos for the gallery and the breed guides ---- */
function photo_alt($breed, $color){ return trim((GALLERY_COLORS[$color] ?? '').' '.strtolower(GALLERY_BREEDS[$breed] ?? 'schnauzer')); }
if($act === 'upload_photos'){
  $breed = isset(GALLERY_BREEDS[in_line('breed')]) ? in_line('breed') : 'miniature';
  $color = isset(GALLERY_COLORS[in_line('color')]) ? in_line('color') : 'other';
  $caption = in_line('caption', 200); $n = 0;
  foreach(upload_list('photos') as $f){
    $r = save_upload($f, ($color !== 'other' ? $color.'-' : '').$breed.'-schnauzer');
    if($r === '') continue;
    if($r[0] === '!'){ note(substr($r, 1), 'err'); continue; }
    array_unshift($SITE['gallery'], ['id'=>new_id(), 'file'=>$r, 'breed'=>$breed, 'color'=>$color, 'caption'=>$caption, 'alt'=>$caption !== '' ? $caption : ucfirst(photo_alt($breed, $color)), 'date'=>date('Y-m-d')]);
    $n++;
  }
  if($n) save($n === 1 ? 'Photo added.' : $n.' photos added.'); else note('Choose one or more photos to upload.', 'err');
  go('photos');
}
if($act === 'save_photos'){
  foreach($SITE['gallery'] as $i=>$p){
    $id = $p['id']; $in = $_POST['p'][$id] ?? null;
    if(!is_array($in)) continue;
    $b = (string)($in['breed'] ?? ''); $c = (string)($in['color'] ?? '');
    $SITE['gallery'][$i]['breed'] = isset(GALLERY_BREEDS[$b]) ? $b : $p['breed'];
    $SITE['gallery'][$i]['color'] = isset(GALLERY_COLORS[$c]) ? $c : $p['color'];
    $SITE['gallery'][$i]['caption'] = mb_substr(trim(preg_replace('/\s+/', ' ', (string)($in['caption'] ?? ''))), 0, 200);
    $alt = mb_substr(trim(preg_replace('/\s+/', ' ', (string)($in['alt'] ?? ''))), 0, 200);
    $SITE['gallery'][$i]['alt'] = $alt !== '' ? $alt : ucfirst(photo_alt($SITE['gallery'][$i]['breed'], $SITE['gallery'][$i]['color']));
  }
  save('Photos saved.');
  go('photos');
}
if($act === 'delete_photo'){
  foreach($SITE['gallery'] as $i=>$p) if($p['id'] === in_line('id', 20)){ delete_upload($p['file']); array_splice($SITE['gallery'], $i, 1); save('Photo deleted.'); break; }
  go('photos');
}

if($act === 'add_redirect'){
  $from = trim(preg_replace('#^https?://[^/]+#i', '', in_line('from', 300)), '/'); $to = trim(preg_replace('#^https?://[^/]+#i', '', in_line('to', 300)), '/');
  if($from === '') note('Enter the old address.', 'err'); else { add_redirect($from, $to); save('Redirect added: /'.$from.' → /'.$to); }
  go('redirects');
}
if($act === 'delete_redirect'){ unset($SITE['redirects'][in_line('from', 300)]); save('Redirect removed.'); go('redirects'); }

if($act === 'read_message' || $act === 'delete_message'){
  $id = in_line('id', 40);
  if(valid_msg_id($id) && ($m = data_read('messages/'.$id))){
    if($act === 'delete_message'){ @unlink(data_file('messages/'.$id)); note('Message deleted.'); go('messages'); }
    $m['read'] = !$m['read']; data_write('messages/'.$id, $m);
  }
  go('messages');
}

if($act === 'save_settings'){
  $tab = in_line('tab', 20);
  $c = &$SITE['settings'];
  if($tab === 'site'){
    $t = in_str('tawk_id', 2000); if(preg_match('#embed\.tawk\.to/([a-f0-9]{20,32}/[a-z0-9]+)#i', $t, $m)) $t = $m[1];
    if($t === '' || preg_match('#^[a-f0-9]{20,32}/[a-z0-9]{1,40}$#i', $t)) $c['tawk_id'] = $t; else note('That live chat code wasn’t recognized, so it wasn’t changed. Paste the Tawk.to script or the ID like 6b05ab87…/default.', 'err');
    foreach(['brand'=>60, 'tagline'=>200, 'hero_title'=>200, 'home_seo_title'=>120, 'home_seo_desc'=>300] as $k=>$n) $c[$k] = in_line($k, $n);
    foreach(['hero_lede', 'guides_intro', 'footer_blurb', 'contact_intro'] as $k) $c[$k] = in_str($k, 1000);
    $e = in_line('email', 160); if(filter_var($e, FILTER_VALIDATE_EMAIL)) $c['email'] = $e; else note('That email address isn’t valid, so it wasn’t changed.', 'err');
    $d = rtrim(in_line('domain', 200), '/'); if(preg_match('#^https?://[a-z0-9.-]+(:\d+)?(/[\w./-]*)?$#i', $d)) $c['domain'] = $d; else note('The website address must start with https://', 'err');
    $c['hero_photo'] = handle_one_photo('hero_photo', $c['hero_photo'], 'home');
    $c['share_photo'] = handle_one_photo('share_photo', $c['share_photo'], 'share');
    if($c['brand'] === '') $c['brand'] = 'Pepperwhisker';
  }
  if($tab === 'about'){
    $c['author_name'] = in_line('author_name', 100); $c['author_bio'] = in_str('author_bio', 3000);
    $c['author_photo'] = handle_one_photo('author_photo', $c['author_photo'], 'author');
    foreach(['facebook', 'instagram', 'pinterest', 'youtube'] as $k){ $u = in_line($k, 300); $c[$k] = $u === '' || preg_match('#^https://#i', $u) ? $u : 'https://'.preg_replace('#^http://#i', '', $u); }
  }
  if($tab === 'google'){
    $g = in_line('gsc_verification', 200); if(preg_match('/content="([^"]+)"/', $g, $m)) $g = $m[1]; $c['gsc_verification'] = preg_replace('/[^\w-]/', '', $g);
    $b = in_line('bing_verification', 200); if(preg_match('/content="([^"]+)"/', $b, $m)) $b = $m[1]; $c['bing_verification'] = preg_replace('/[^\w-]/', '', $b);
    $c['ga4_id'] = strtoupper(in_line('ga4_id', 30));
    $ad = in_line('adsense_client', 60); if(preg_match('/(pub-\d{10,20})/', $ad, $m)) $ad = 'ca-'.$m[1]; $c['adsense_client'] = $ad;
    $c['affiliate_note'] = in_bool('affiliate_note');
    $c['pretty_urls'] = in_bool('pretty_urls'); $c['pretty_auto'] = true;
  }
  if($tab === 'email'){
    $c['smtp_host'] = in_line('smtp_host', 200); $c['smtp_port'] = preg_replace('/\D/', '', in_line('smtp_port', 6));
    $c['smtp_secure'] = in_line('smtp_secure') === 'ssl' ? 'ssl' : 'tls'; $c['smtp_user'] = in_line('smtp_user', 200);
    if(($_POST['smtp_pass'] ?? '') !== '') $c['smtp_pass'] = (string)$_POST['smtp_pass'];
  }
  unset($c);
  save('Settings saved.');
  go('settings', ['tab'=>$tab]);
}
if($act === 'test_email'){
  $to = in_line('to', 160);
  if(!filter_var($to, FILTER_VALIDATE_EMAIL)) note('Enter a valid address.', 'err');
  elseif(site_mail($to, 'Test email from '.$CFG['brand'], "This is a test email from your website.\n\nIf you can read this, contact form messages will reach you.\n\n— ".$CFG['brand'])) note('Test email sent to '.$to.'. Check that inbox (and its spam folder).');
  else note('The test email could not be sent. '.($GLOBALS['PW_MAIL_ERROR'] ?? ''), 'err');
  go('settings', ['tab'=>'email']);
}
if($act === 'change_password'){
  if(!password_verify((string)($_POST['current'] ?? ''), $AUTH['hash'])) note('Your current password is wrong.', 'err');
  elseif(strlen((string)($_POST['pass'] ?? '')) < 10) note('Use a new password of at least 10 characters.', 'err');
  elseif($_POST['pass'] !== ($_POST['pass2'] ?? '')) note('The two new passwords don’t match.', 'err');
  else {
    $u = in_line('user', 40); if(valid_user($u)) $AUTH['user'] = $u;
    $AUTH['hash'] = password_hash((string)$_POST['pass'], PASSWORD_DEFAULT); $AUTH['key'] = bin2hex(random_bytes(16));
    data_write('auth', $AUTH); $_SESSION['admin'] = $AUTH['key']; note('Sign-in updated. Other signed-in browsers have been signed out.');
  }
  go('account');
}

/* ---------------- views ---------------- */
$VIEWS = ['dashboard'=>'Dashboard', 'articles'=>'Guides & blog', 'photos'=>'Photos', 'pages'=>'Pages', 'messages'=>'Messages', 'settings'=>'Settings', 'redirects'=>'Redirects', 'account'=>'Sign-in'];
$SUB = ['article'=>'articles', 'page'=>'pages'];
if(!isset($VIEWS[$v]) && !isset($SUB[$v])) $v = 'dashboard';
layout_start($VIEWS[$v] ?? $VIEWS[$SUB[$v]]);
('view_'.$v)();
layout_end();

function layout_start($title){
  global $v, $VIEWS, $SUB, $CFG;
  $unread = is_admin() ? count(array_filter(messages_all(), fn($m)=>empty($m['read']))) : 0;
  ?><!doctype html><html lang="en"><head><meta charset="utf-8"><meta name="viewport" content="width=device-width, initial-scale=1"><meta name="robots" content="noindex, nofollow">
<title><?= h($title) ?> · <?= h($CFG['brand']) ?> admin</title><link rel="icon" href="assets/favicon.svg" type="image/svg+xml">
<style>
:root{--bg:#f5f3ef;--s:#fff;--ink:#1f2427;--mut:#636b70;--line:#e2ddd5;--br:#b4532a;--brd:#94421f;--ok:#256b38;--err:#a3311f}
*{box-sizing:border-box}body{margin:0;font:15px/1.5 system-ui,-apple-system,"Segoe UI",Roboto,sans-serif;background:var(--bg);color:var(--ink)}
a{color:var(--brd)}h1{font:700 1.6rem Georgia,serif;margin:0 0 14px}h2{font:700 1.2rem Georgia,serif;margin:22px 0 10px}
.top{background:#1f282d;color:#e9ecee;padding:10px 18px;display:flex;align-items:center;gap:14px;flex-wrap:wrap}.top a{color:#e9ecee;text-decoration:none}.top strong{margin-right:auto;font-family:Georgia,serif;font-size:1.1rem}
.side{display:flex;flex-wrap:wrap;gap:4px;padding:10px 18px;background:#fff;border-bottom:1px solid var(--line)}.side a{padding:7px 11px;border-radius:8px;text-decoration:none;color:var(--ink);font-weight:550}.side a.on{background:var(--br);color:#fff}
.pill{display:inline-block;background:#c0392b;color:#fff;border-radius:999px;padding:0 7px;font-size:.75rem;margin-left:4px}
main{max-width:1150px;margin:0 auto;padding:20px 18px 60px}
.card{background:var(--s);border:1px solid var(--line);border-radius:12px;padding:18px;margin-bottom:16px}
.grid{display:grid;gap:14px}@media(min-width:800px){.g2{grid-template-columns:1fr 1fr}.g3{grid-template-columns:1fr 1fr 1fr}.g4{grid-template-columns:repeat(4,1fr)}}
label{display:block;font-weight:600;font-size:.9rem;margin-bottom:12px}label small{font-weight:400;color:var(--mut);display:block}
input[type=text],input[type=email],input[type=password],input[type=date],input[type=url],select,textarea{width:100%;font:inherit;padding:8px 10px;border:1px solid #cbc4ba;border-radius:8px;background:#fff;margin-top:4px}
textarea{min-height:90px;resize:vertical}textarea.big{min-height:480px;font:13.5px/1.55 ui-monospace,Menlo,Consolas,monospace}
.cb{display:flex;gap:8px;align-items:center;font-weight:550}.cb input{width:18px;height:18px}
.btn{display:inline-block;background:var(--br);color:#fff;border:0;border-radius:999px;padding:9px 18px;font:600 .95rem inherit;cursor:pointer;text-decoration:none}.btn:hover{background:var(--brd);color:#fff}
.btn-g{background:#fff;color:var(--brd);border:1px solid var(--br)}.btn-d{background:#fff;color:var(--err);border:1px solid var(--err)}.btn-s{padding:5px 12px;font-size:.85rem}
table{width:100%;border-collapse:collapse;background:#fff}th,td{text-align:left;padding:9px 10px;border-bottom:1px solid var(--line);vertical-align:top}th{font-size:.82rem;color:var(--mut);text-transform:uppercase;letter-spacing:.04em}
.tw{overflow-x:auto;border:1px solid var(--line);border-radius:12px}
.flash{padding:10px 14px;border-radius:10px;margin-bottom:12px}.flash.ok{background:#e6f3ea;color:#1f4d2d}.flash.err{background:#fdecea;color:#6d2014}
.badge{display:inline-block;font-size:.72rem;font-weight:700;text-transform:uppercase;padding:3px 8px;border-radius:999px;background:#eee}.b-new{background:#fdf0d8;color:#8a5a00}
.photos{display:flex;flex-wrap:wrap;gap:10px}.photos figure{margin:0;width:130px;font-size:.8rem}.photos img{width:130px;height:100px;object-fit:cover;border-radius:8px}
.mut{color:var(--mut)}.row{display:flex;gap:10px;flex-wrap:wrap;align-items:center}.stat{font:700 2rem Georgia,serif}.right{margin-left:auto}
.chk{list-style:none;padding-left:0}.chk li{margin-bottom:6px}.chk .done{color:var(--ok)}.chk .todo{color:var(--err)}
.tabs{display:flex;gap:6px;flex-wrap:wrap;margin-bottom:14px}.tabs a{padding:6px 12px;border-radius:999px;border:1px solid var(--line);text-decoration:none;color:var(--ink);background:#fff}.tabs a.on{background:var(--br);color:#fff;border-color:var(--br)}
details summary{cursor:pointer;font-weight:600;margin-bottom:8px}
.msg{white-space:pre-wrap;background:#faf8f5;border-radius:8px;padding:10px 12px;margin:8px 0}
</style></head><body>
<?php if(is_admin()): $cur = $SUB[$v] ?? $v; ?>
<div class="top"><strong><?= h($CFG['brand']) ?> admin</strong><a href="<?= h(site_url()) ?>" target="_blank">View website ↗</a><a href="<?= h(self_url('logout')) ?>">Sign out</a></div>
<nav class="side"><?php foreach($VIEWS as $k=>$label): ?><a class="<?= $cur === $k ? 'on' : '' ?>" href="<?= h(self_url($k)) ?>"><?= h($label) ?><?= $k === 'messages' && $unread ? '<span class="pill">'.$unread.'</span>' : '' ?></a><?php endforeach; ?></nav>
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

function form_start($act, $multipart=false){ return '<form method="post"'.($multipart ? ' enctype="multipart/form-data"' : '').'>'.csrf_field().'<input type="hidden" name="act" value="'.h($act).'">'; }
function del_button($act, $fields, $label='Delete', $confirm='Delete this for good?'){
  $o = '<form method="post" onsubmit="return confirm(\''.h($confirm).'\')" style="display:inline">'.csrf_field().'<input type="hidden" name="act" value="'.h($act).'">';
  foreach($fields as $k=>$val) $o .= '<input type="hidden" name="'.h($k).'" value="'.h($val).'">';
  return $o.'<button class="btn btn-d btn-s">'.h($label).'</button></form>';
}
function one_photo_editor($field, $current, $label, $help=''){
  $o = '<label>'.h($label).($help !== '' ? '<small>'.h($help).'</small>' : '').'<input type="file" name="'.h($field).'" accept="image/jpeg,image/png,image/webp"></label>';
  if($current !== '' && is_file(PW_ROOT.'/'.$current)) $o .= '<div class="photos" style="margin:-4px 0 12px"><figure><img src="'.h(thumb($current, 320)).'" alt=""><label class="cb"><input type="checkbox" name="remove_'.h($field).'" value="1"> Remove</label></figure></div>';
  return $o;
}

function view_dashboard(){
  global $SITE, $CFG;
  $msgs = messages_all(); $unread = array_filter($msgs, fn($m)=>empty($m['read']));
  $guides = array_filter($SITE['articles'], fn($a)=>empty($a['draft']));
  $words = array_sum(array_map(fn($a)=>($a['lang'] ?? 'en') === 'en' ? word_count($a['body']) : 0, $guides));
  $checks = [
    [!empty($GLOBALS['AUTH']['hash']), 'Create your admin sign-in', 'account', ''],
    [$CFG['email'] !== 'hello@pepperwhisker.com' || $CFG['smtp_host'] !== '', 'Check the site’s email address', 'settings', 'site'],
    [$CFG['smtp_host'] !== '', 'Set up email sending (SMTP) and send a test email', 'settings', 'email'],
    [$CFG['author_name'] !== '' && $CFG['author_bio'] !== '', 'Add the writer’s name, bio and photo (shown on every guide)', 'settings', 'about'],
    [count($SITE['gallery']) >= 8, 'Upload at least 8 of your own schnauzer photos (they show on the pictures page and the breed guides)', 'photos', ''],
    [count(array_filter($SITE['articles'], fn($a)=>($a['image'] ?? '') !== '')) >= 5, 'Add your own schnauzer photos as the main photo of at least 5 guides', 'articles', ''],
    [$CFG['gsc_verification'] !== '', 'Verify the site in Google Search Console and submit the sitemap', 'settings', 'google'],
    [$CFG['ga4_id'] !== '', 'Add Google Analytics (optional)', 'settings', 'google'],
    [$CFG['adsense_client'] !== '', 'Add your Google AdSense publisher ID once approved (optional)', 'settings', 'google'],
    [!empty($CFG['pretty_urls']), 'Clean page addresses (switch on automatically on most hosts)', 'settings', 'google'],
  ];
  ?><h1>Dashboard</h1>
  <div class="grid g4">
    <div class="card"><div class="stat"><?= count($guides) ?></div>guides and posts · <a href="<?= h(self_url('article')) ?>">write one</a></div>
    <div class="card"><div class="stat"><?= number_format($words) ?></div>words of guides</div>
    <div class="card"><div class="stat"><?= count($unread) ?></div>unread messages · <a href="<?= h(self_url('messages')) ?>">open</a></div>
    <div class="card"><div class="stat"><?= count($SITE['pages']) ?></div>pages · <a href="<?= h(self_url('pages')) ?>">edit</a></div>
  </div>
  <div class="grid g2">
    <div class="card"><h2 style="margin-top:0">Launch checklist</h2><ul class="chk">
      <?php foreach($checks as [$ok, $label, $view, $tab]): ?><li class="<?= $ok ? 'done' : 'todo' ?>"><?= $ok ? '✓' : '○' ?> <?= $ok ? h($label) : '<a href="'.h(self_url($view, $tab ? ['tab'=>$tab] : [])).'">'.h($label).'</a>' ?></li><?php endforeach; ?>
    </ul><p class="mut">Sitemap for Google: <a href="<?= h(site_url('sitemap.xml')) ?>" target="_blank"><?= h(rtrim($CFG['domain'], '/')) ?>/sitemap.xml</a></p></div>
    <div class="card"><h2 style="margin-top:0">Latest messages</h2>
      <?php if(!$msgs): ?><p class="mut">None yet. Contact form messages appear here and are emailed to <?= h($CFG['email']) ?>.</p><?php endif; ?>
      <table><?php foreach(array_slice($msgs, 0, 6) as $m): ?><tr><td><strong><?= h($m['name'] !== '' ? $m['name'] : $m['email']) ?></strong><?= empty($m['read']) ? ' <span class="badge b-new">New</span>' : '' ?><br><span class="mut"><?= h(mb_substr($m['message'], 0, 90)) ?>…</span></td><td class="mut"><?= h(date('M j', strtotime($m['time']))) ?></td></tr><?php endforeach; ?></table></div>
  </div>
  <div class="card"><h2 style="margin-top:0">Make the guides yours</h2><p>Google rewards first-hand experience. The best thing you can do for rankings is to add <strong>your own schnauzer photos</strong> and a few sentences from your own experience to the most important guides: giant schnauzer, miniature schnauzer, haircut styles and the schnoodle. Edit any guide under <a href="<?= h(self_url('articles')) ?>">Guides &amp; blog</a>.</p></div>
  <?php if(empty($CFG['pretty_urls']) && empty($CFG['pretty_auto'])): ?><script>fetch('rewrite-check',{cache:'no-store'}).then(r=>r.ok&&r.json()).then(j=>{if(j&&j.clean_urls)location.reload()}).catch(()=>{});</script><?php endif;
}

function view_articles(){
  global $SITE;
  $list = $SITE['articles'];
  usort($list, fn($a, $b)=>strcmp(array_search($a['group'], array_keys(GUIDE_GROUPS)).$a['title'], array_search($b['group'], array_keys(GUIDE_GROUPS)).$b['title']));
  ?><div class="row"><h1>Guides &amp; blog</h1><a class="btn right" href="<?= h(self_url('article', ['type'=>'guide'])) ?>">+ New guide</a><a class="btn btn-g" href="<?= h(self_url('article', ['type'=>'blog'])) ?>">+ New blog post</a></div>
  <p class="mut">Each guide targets a group of searches (see docs/keyword-map.md). Edit freely, and add your own photos and experience: first-hand detail is what makes Google, and readers, trust a page.</p>
  <div class="tw"><table><tr><th>Title</th><th>Topic</th><th>Words</th><th>Photo</th><th>Updated</th></tr>
  <?php foreach($list as $a): ?><tr><td><a href="<?= h(self_url('article', ['slug'=>$a['slug']])) ?>"><strong><?= h($a['title']) ?></strong></a><?= !empty($a['draft']) ? ' <span class="badge">Draft</span>' : '' ?><?= $a['type'] === 'blog' ? ' <span class="badge">Blog</span>' : '' ?><br><span class="mut">/<?= h(article_path($a)) ?></span></td>
    <td><?= h(GUIDE_GROUPS[$a['group']][0] ?? $a['group']) ?></td><td><?= ($a['lang'] ?? 'en') === 'en' ? word_count($a['body']) : 'ES' ?></td><td><?= ($a['image'] ?? '') !== '' ? '✓' : '<span class="mut">—</span>' ?></td><td><?= h($a['updated'] ?? $a['date']) ?></td></tr><?php endforeach; ?></table></div><?php
}

function view_article(){
  global $SITE;
  $a = find_by($SITE['articles'], 'slug', (string)($_GET['slug'] ?? ''));
  $new = !$a;
  $a = ($a ?? []) + ['slug'=>'', 'type'=>($_GET['type'] ?? '') === 'blog' ? 'blog' : 'guide', 'group'=>'breeds', 'title'=>'', 'seo_title'=>'', 'seo_desc'=>'', 'excerpt'=>'', 'answer'=>'', 'body'=>'', 'image'=>'', 'image_alt'=>'', 'date'=>date('Y-m-d'), 'draft'=>false, 'photos'=>[]];
  ?><div class="row"><h1><?= $new ? 'New '.($a['type'] === 'blog' ? 'blog post' : 'guide') : h($a['title']) ?></h1><?php if(!$new && empty($a['draft'])): ?><a class="btn btn-g right" target="_blank" href="<?= h(site_url(article_path($a))) ?>">View on site ↗</a><?php endif; ?></div>
  <?= form_start('save_article', true) ?><input type="hidden" name="orig" value="<?= h($a['slug']) ?>"><div class="card">
  <label>Title (main heading) *<input type="text" name="title" value="<?= h($a['title']) ?>" required></label>
  <div class="grid g3"><label>Type<select name="type"><option value="guide"<?= $a['type'] === 'guide' ? ' selected' : '' ?>>Guide</option><option value="blog"<?= $a['type'] === 'blog' ? ' selected' : '' ?>>Blog post</option></select></label>
    <label>Topic<small>Where it's listed, and which guides link to it</small><select name="group"><?php foreach(GUIDE_GROUPS as $k=>[$l]): ?><option value="<?= $k ?>"<?= $a['group'] === $k ? ' selected' : '' ?>><?= h($l) ?></option><?php endforeach; ?></select></label>
    <label>Published<input type="date" name="date" value="<?= h($a['date']) ?>"></label></div>
  <label>Quick answer<small>40–60 words that answer the title's question directly. Shown in a box under the heading, and the text Google is most likely to quote.</small><textarea name="answer" rows="3"><?= h($a['answer']) ?></textarea></label>
  <label>Text *<small><?= h(FORMAT_HELP) ?></small><textarea class="big" name="body" required><?= h($a['body']) ?></textarea></label>
  <?= one_photo_editor('image', $a['image'], 'Main photo', 'Shown at the top, on guide cards and when shared. Your own schnauzer photos are best.') ?>
  <label>Describe the main photo<small>For people using screen readers and for Google Images, e.g. “Salt and pepper miniature schnauzer with a fresh schnauzer cut”</small><input type="text" name="image_alt" value="<?= h($a['image_alt']) ?>"></label>
  <h2>Photos inside the text</h2>
  <?php if($a['photos']): ?><p class="mut">Click “Insert” to place a photo where your cursor is in the text, then change “Describe the photo”. Save to see it on the guide.</p>
  <div class="photos"><?php foreach($a['photos'] as $f): ?><figure><img src="<?= h(thumb($f, 320)) ?>" alt=""><button type="button" class="btn btn-g btn-s" data-insert="<?= h('![Describe the photo]('.$f.')') ?>">Insert</button><label class="cb"><input type="checkbox" name="remove_inline[]" value="<?= h($f) ?>"> Remove</label></figure><?php endforeach; ?></div><?php endif; ?>
  <label>Add photos for the text<small>Upload, save, then insert them where you want them</small><input type="file" name="inline[]" accept="image/jpeg,image/png,image/webp" multiple></label>
  <script>document.querySelectorAll('[data-insert]').forEach(function(b){b.addEventListener('click',function(){var t=document.querySelector('textarea[name=body]'),v=b.getAttribute('data-insert'),s=t.selectionStart||t.value.length;t.value=t.value.slice(0,s)+'\n\n'+v+'\n\n'+t.value.slice(t.selectionEnd||s);t.focus();t.selectionStart=t.selectionEnd=s+v.length+4;})});</script>
  <details<?= $new ? ' open' : '' ?>><summary>Search engine &amp; listing</summary>
    <label>Address<small>/<?= $a['type'] === 'blog' ? 'blog' : 'guides' ?>/<b>…</b> — if you change it, the old address leads here automatically</small><input type="text" name="slug" value="<?= h($a['slug']) ?>"></label>
    <label>Title for Google<small>About 50–60 characters, with the main search phrase near the start</small><input type="text" name="seo_title" value="<?= h($a['seo_title']) ?>"></label>
    <label>Description for Google<small>About 140–155 characters that make people want to click</small><textarea name="seo_desc" rows="2"><?= h($a['seo_desc']) ?></textarea></label>
    <label>Short summary<small>Shown on guide cards</small><input type="text" name="excerpt" value="<?= h($a['excerpt']) ?>"></label></details>
  <label class="cb"><input type="checkbox" name="draft" value="1"<?= !empty($a['draft']) ? ' checked' : '' ?>> Draft (not on the website yet)</label>
  <button class="btn">Save</button></div></form>
  <?php if(!$new) echo '<p>'.del_button('delete_article', ['slug'=>$a['slug']], 'Delete', 'Delete this guide? Its address will lead to the guides page.').'</p>';
}

function view_photos(){
  global $SITE;
  $list = $SITE['gallery'];
  ?><div class="row"><h1>Photos</h1><?php if($list): ?><a class="btn btn-g right" target="_blank" href="<?= h(site_url('schnauzer-pictures')) ?>">View the pictures page ↗</a><?php endif; ?></div>
  <p class="mut">Your own schnauzer photos. They appear on the <strong>Schnauzer pictures</strong> page (for Google Images searches like “miniature schnauzer pictures”) and on the matching breed and color guides. Photos are resized, turned upright and cleaned of their location data automatically. Only upload photos you took yourself.</p>
  <div class="card"><h2 style="margin-top:0">Upload photos</h2><?= form_start('upload_photos', true) ?>
    <label>Photos<small>JPG, PNG or WebP; choose several at once</small><input type="file" name="photos[]" accept="image/jpeg,image/png,image/webp" multiple required></label>
    <div class="grid g3"><label>Breed<select name="breed"><?php foreach(GALLERY_BREEDS as $k=>$l): ?><option value="<?= $k ?>"><?= h($l) ?></option><?php endforeach; ?></select></label>
      <label>Color<select name="color"><?php foreach(GALLERY_COLORS as $k=>$l): ?><option value="<?= $k ?>"><?= h($l) ?></option><?php endforeach; ?></select></label>
      <label>Caption <small>Optional, e.g. “Salt and pepper miniature schnauzer after a schnauzer cut”</small><input type="text" name="caption"></label></div>
    <button class="btn">Upload</button></form></div>
  <?php if($list): ?>
  <?= form_start('save_photos') ?><div class="card"><h2 style="margin-top:0">Your photos (<?= count($list) ?>)</h2>
    <p class="mut">The description is what Google Images reads: describe what the photo shows, e.g. “Black giant schnauzer standing in snow”.</p>
    <div class="tw"><table><tr><th>Photo</th><th>Breed and color</th><th>Description and caption</th></tr>
    <?php foreach($list as $p): $k = 'p['.$p['id'].']'; ?><tr>
      <td style="width:150px"><img src="<?= h(thumb($p['file'], 320)) ?>" alt="" style="width:140px;height:105px;object-fit:cover;border-radius:8px"></td>
      <td><select name="<?= $k ?>[breed]"><?php foreach(GALLERY_BREEDS as $b=>$l): ?><option value="<?= $b ?>"<?= $p['breed'] === $b ? ' selected' : '' ?>><?= h($l) ?></option><?php endforeach; ?></select>
        <select name="<?= $k ?>[color]"><?php foreach(GALLERY_COLORS as $c=>$l): ?><option value="<?= $c ?>"<?= $p['color'] === $c ? ' selected' : '' ?>><?= h($l) ?></option><?php endforeach; ?></select></td>
      <td><label>Description (alt text)<input type="text" name="<?= $k ?>[alt]" value="<?= h($p['alt']) ?>"></label><label>Caption<input type="text" name="<?= $k ?>[caption]" value="<?= h($p['caption']) ?>"></label></td>
    </tr><?php endforeach; ?></table></div>
    <p><button class="btn">Save photos</button></p></div></form>
  <div class="card"><h2 style="margin-top:0">Delete a photo</h2><div class="photos"><?php foreach($list as $p): ?><figure><img src="<?= h(thumb($p['file'], 320)) ?>" alt=""><?= del_button('delete_photo', ['id'=>$p['id']], 'Delete', 'Delete this photo?') ?></figure><?php endforeach; ?></div></div>
  <?php endif;
}

function view_pages(){
  global $SITE;
  ?><div class="row"><h1>Pages</h1><a class="btn right" href="<?= h(self_url('page')) ?>">+ New page</a></div>
  <p class="mut">About, policies and any other pages. {brand}, {email} and {author} fill in from Settings.</p>
  <div class="tw"><table><tr><th>Page</th><th>Address</th></tr><?php foreach($SITE['pages'] as $pg): ?><tr><td><a href="<?= h(self_url('page', ['slug'=>$pg['slug']])) ?>"><strong><?= h($pg['title']) ?></strong></a></td><td><a target="_blank" href="<?= h(site_url($pg['slug'])) ?>">/<?= h($pg['slug']) ?></a></td></tr><?php endforeach; ?></table></div><?php
}

function view_page(){
  global $SITE;
  $pg = find_by($SITE['pages'], 'slug', (string)($_GET['slug'] ?? '')) ?? [];
  $new = !$pg;
  $pg += ['slug'=>'', 'menu'=>'', 'title'=>'', 'seo_title'=>'', 'seo_desc'=>'', 'body'=>'', 'answer'=>''];
  ?><div class="row"><h1><?= $new ? 'New page' : h($pg['title']) ?></h1><?php if(!$new): ?><a class="btn btn-g right" target="_blank" href="<?= h(site_url($pg['slug'])) ?>">View on site ↗</a><?php endif; ?></div>
  <?= form_start('save_page') ?><input type="hidden" name="orig" value="<?= h($pg['slug']) ?>"><div class="card">
  <div class="grid g2"><label>Main heading *<input type="text" name="title" value="<?= h($pg['title']) ?>" required></label><label>Short name for menus and breadcrumbs<input type="text" name="menu" value="<?= h($pg['menu']) ?>"></label></div>
  <?php if($new): ?><label>Address<small>e.g. schnauzer-resources</small><input type="text" name="slug" value=""></label><?php endif; ?>
  <label>Quick answer <small>Optional: 40–60 words shown in a box under the heading</small><textarea name="answer" rows="3"><?= h($pg['answer']) ?></textarea></label>
  <label>Text<small><?= h(FORMAT_HELP) ?></small><textarea class="big" name="body"><?= h($pg['body']) ?></textarea></label>
  <div class="grid g2"><label>Title for Google<input type="text" name="seo_title" value="<?= h($pg['seo_title']) ?>"></label><label>Description for Google<input type="text" name="seo_desc" value="<?= h($pg['seo_desc']) ?>"></label></div>
  <button class="btn">Save page</button></div></form>
  <?php if(!$new) echo '<p>'.del_button('delete_page', ['slug'=>$pg['slug']], 'Delete page', 'Delete this page? Its address will lead to the home page.').'</p>';
}

function view_messages(){
  global $CFG;
  $msgs = messages_all();
  ?><h1>Messages</h1><p class="mut">From the contact form. Each one is also emailed to <?= h($CFG['email']) ?>; reply to that email to answer.</p>
  <?php if(!$msgs): ?><div class="card mut">No messages yet.</div><?php endif; ?>
  <?php foreach($msgs as $m): ?><div class="card"><div class="row"><strong><?= h($m['name'] !== '' ? $m['name'] : 'No name') ?></strong><a href="mailto:<?= h($m['email']) ?>?subject=<?= rawurlencode('Re: your message to '.$CFG['brand']) ?>"><?= h($m['email']) ?></a><?= empty($m['read']) ? '<span class="badge b-new">New</span>' : '' ?><span class="mut right"><?= h(date('M j, Y g:ia', strtotime($m['time']))) ?></span></div>
    <div class="msg"><?= h($m['message']) ?></div>
    <div class="row"><?= form_start('read_message') ?><input type="hidden" name="id" value="<?= h($m['id']) ?>"><button class="btn btn-g btn-s"><?= empty($m['read']) ? 'Mark as read' : 'Mark as unread' ?></button></form><?= del_button('delete_message', ['id'=>$m['id']], 'Delete', 'Delete this message?') ?></div></div><?php endforeach;
}

function view_redirects(){
  global $SITE;
  ?><h1>Redirects</h1><p class="mut">When a guide or page moves or is deleted, its old address is sent to the new one here automatically, so links and Google rankings follow. You can add your own too.</p>
  <div class="card"><?= form_start('add_redirect') ?><div class="grid g2"><label>Old address<small>e.g. /old-guide</small><input type="text" name="from"></label><label>New address<small>e.g. /guides/giant-schnauzer (empty = home page)</small><input type="text" name="to"></label></div><button class="btn">Add redirect</button></form></div>
  <div class="tw"><table><tr><th>From</th><th>To</th><th></th></tr>
  <?php foreach($SITE['redirects'] as $from=>$to): ?><tr><td>/<?= h($from) ?></td><td><a target="_blank" href="<?= h(site_url($to)) ?>">/<?= h($to) ?></a></td><td><?= del_button('delete_redirect', ['from'=>$from], 'Remove') ?></td></tr><?php endforeach; ?>
  <?php if(!$SITE['redirects']): ?><tr><td colspan="3" class="mut">None yet.</td></tr><?php endif; ?></table></div><?php
}

function view_settings(){
  global $CFG;
  $tabs = ['site'=>'Site & home page', 'about'=>'Writer & social', 'google'=>'Google & ads', 'email'=>'Email'];
  $tab = isset($tabs[$_GET['tab'] ?? '']) ? $_GET['tab'] : 'site';
  ?><h1>Settings</h1><nav class="tabs"><?php foreach($tabs as $k=>$l): ?><a class="<?= $tab === $k ? 'on' : '' ?>" href="<?= h(self_url('settings', ['tab'=>$k])) ?>"><?= h($l) ?></a><?php endforeach; ?></nav>
  <?= form_start('save_settings', true) ?><input type="hidden" name="tab" value="<?= h($tab) ?>"><div class="card">
  <?php if($tab === 'site'): ?>
    <div class="grid g2"><label>Site name<input type="text" name="brand" value="<?= h($CFG['brand']) ?>"></label><label>Website address<small>With https://, no slash at the end</small><input type="url" name="domain" value="<?= h($CFG['domain']) ?>"></label></div>
    <label>Site email<small>Contact form messages go here, and emails are sent from it</small><input type="email" name="email" value="<?= h($CFG['email']) ?>"></label>
    <label>Tagline<small>Above the home page heading</small><input type="text" name="tagline" value="<?= h($CFG['tagline']) ?>"></label>
    <label>Home page heading<input type="text" name="hero_title" value="<?= h($CFG['hero_title']) ?>"></label>
    <label>Home page introduction<textarea name="hero_lede" rows="3"><?= h($CFG['hero_lede']) ?></textarea></label>
    <?= one_photo_editor('hero_photo', $CFG['hero_photo'], 'Home page photo', 'Optional: replaces the size drawing beside the heading. A landscape photo of your schnauzer works best.') ?>
    <div class="grid g2"><label>Home page title for Google<input type="text" name="home_seo_title" value="<?= h($CFG['home_seo_title']) ?>"></label><label>Home page description for Google<input type="text" name="home_seo_desc" value="<?= h($CFG['home_seo_desc']) ?>"></label></div>
    <label>Guides page introduction<textarea name="guides_intro" rows="2"><?= h($CFG['guides_intro']) ?></textarea></label>
    <label>Contact page introduction<textarea name="contact_intro" rows="2"><?= h($CFG['contact_intro']) ?></textarea></label>
    <label>Footer text<textarea name="footer_blurb" rows="2"><?= h($CFG['footer_blurb']) ?></textarea></label>
    <label>Live chat (Tawk.to)<small>Your Tawk.to property and widget ID, e.g. 6b05ab87398c563cb2397234/default — or paste the whole Tawk.to script. Leave empty to turn the chat off.</small><input type="text" name="tawk_id" value="<?= h($CFG['tawk_id']) ?>"></label>
    <?= one_photo_editor('share_photo', $CFG['share_photo'], 'Link preview image', 'Shown when the home page is shared on social media (1200 × 630 is ideal)') ?>
  <?php elseif($tab === 'about'): ?>
    <p class="mut">The writer is shown on every guide (“By …”) and on the About page. A real name, a short bio with your schnauzer experience and a photo help readers and Google trust the guides.</p>
    <label>Writer's name<small>Leave empty to show the site name</small><input type="text" name="author_name" value="<?= h($CFG['author_name']) ?>"></label>
    <label>Short bio<small>2–4 sentences: your schnauzers, your experience, why you write</small><textarea name="author_bio" rows="4"><?= h($CFG['author_bio']) ?></textarea></label>
    <?= one_photo_editor('author_photo', $CFG['author_photo'], 'Writer photo', 'A square photo, ideally with your schnauzer') ?>
    <h2>Social profiles</h2>
    <div class="grid g2"><?php foreach(['facebook'=>'Facebook', 'instagram'=>'Instagram', 'pinterest'=>'Pinterest', 'youtube'=>'YouTube'] as $k=>$l): ?><label><?= $l ?><input type="url" name="<?= $k ?>" value="<?= h($CFG[$k]) ?>" placeholder="https://"></label><?php endforeach; ?></div>
  <?php elseif($tab === 'google'): ?>
    <label>Google Search Console verification<small>Choose the “HTML tag” method and paste the whole tag or just the code</small><input type="text" name="gsc_verification" value="<?= h($CFG['gsc_verification']) ?>"></label>
    <label>Bing Webmaster verification<small>Optional, same as above</small><input type="text" name="bing_verification" value="<?= h($CFG['bing_verification']) ?>"></label>
    <label>Google Analytics 4 measurement ID<small>e.g. G-ABC123XYZ</small><input type="text" name="ga4_id" value="<?= h($CFG['ga4_id']) ?>"></label>
    <label>Google AdSense publisher ID<small>e.g. ca-pub-1234567890123456. Turns on Auto ads on guides and the ads.txt file Google checks. Leave empty until AdSense approves the site.</small><input type="text" name="adsense_client" value="<?= h($CFG['adsense_client']) ?>"></label>
    <label class="cb"><input type="checkbox" name="affiliate_note" value="1"<?= !empty($CFG['affiliate_note']) ? ' checked' : '' ?>> Show the affiliate-link note on guides (tick this once you add affiliate links)</label>
    <label class="cb"><input type="checkbox" name="pretty_urls" value="1"<?= !empty($CFG['pretty_urls']) ? ' checked' : '' ?>> Clean page addresses (/guides/giant-schnauzer). Switched on by itself when your host supports it.</label>
    <p class="mut">Sitemap: <a href="<?= h(site_url('sitemap.xml')) ?>" target="_blank"><?= h(rtrim($CFG['domain'], '/')) ?>/sitemap.xml</a> — submit it in Search Console.</p>
  <?php else: ?>
    <p class="mut">Use your mailbox provider's SMTP details so messages reliably reach your inbox. Without them, the host's PHP mail is used.</p>
    <div class="grid g3"><label>SMTP server<input type="text" name="smtp_host" value="<?= h($CFG['smtp_host']) ?>" placeholder="smtp.example.com"></label><label>Port<input type="text" name="smtp_port" value="<?= h($CFG['smtp_port']) ?>" placeholder="587"></label>
      <label>Security<select name="smtp_secure"><option value="tls"<?= $CFG['smtp_secure'] !== 'ssl' ? ' selected' : '' ?>>STARTTLS (587)</option><option value="ssl"<?= $CFG['smtp_secure'] === 'ssl' ? ' selected' : '' ?>>SSL (465)</option></select></label></div>
    <div class="grid g2"><label>Username<input type="text" name="smtp_user" value="<?= h($CFG['smtp_user']) ?>" autocomplete="off"></label><label>Password<small><?= $CFG['smtp_pass'] !== '' ? 'Saved. Leave empty to keep it.' : '' ?></small><input type="password" name="smtp_pass" autocomplete="new-password"></label></div>
  <?php endif; ?>
  <button class="btn">Save settings</button></div></form>
  <?php if($tab === 'email'): ?><div class="card"><?= form_start('test_email') ?><label>Send a test email to<small>Use an address at a different provider, e.g. Gmail</small><input type="email" name="to" value="<?= h($CFG['email']) ?>"></label><button class="btn btn-g">Send test email</button></form></div><?php endif;
}

function view_account(){
  global $AUTH;
  ?><h1>Sign-in</h1><div class="card" style="max-width:520px"><?= form_start('change_password') ?>
  <label>Username<input type="text" name="user" value="<?= h($AUTH['user'] ?? '') ?>" autocomplete="username"></label>
  <label>Current password<input type="password" name="current" required autocomplete="current-password"></label>
  <label>New password<small>At least 10 characters</small><input type="password" name="pass" required autocomplete="new-password"></label>
  <label>New password again<input type="password" name="pass2" required autocomplete="new-password"></label>
  <button class="btn">Update sign-in</button></form></div>
  <p class="mut">Forgot the password? Delete the file data/auth.php in your host's File Manager, then open admin.php to create a new sign-in.</p><?php
}
