<?php
/* =============================================================
   Beagle Brook — the website. Edit content in admin.php;
   everything is saved to data/ and never in this file.
   ============================================================= */

define('BB_ROOT', __DIR__);
@ini_set('display_errors', '0');
error_reporting(E_ALL & ~E_NOTICE & ~E_WARNING & ~E_DEPRECATED);
require BB_ROOT.'/inc/core.php';
require BB_ROOT.'/inc/deals.php';
require BB_ROOT.'/inc/views.php';
start_session();
header('X-Content-Type-Options: nosniff');
header('Referrer-Policy: strict-origin-when-cross-origin');
header('X-Frame-Options: SAMEORIGIN');

$SITE = site_load();
$CFG  = $SITE['settings'];
$BASE = rtrim(str_replace('\\', '/', dirname($_SERVER['SCRIPT_NAME'] ?? '/index.php')), '/').'/';

/* ---------------- URLS ----------------
   Clean addresses (/beagle-puppies-for-sale/daisy…, /guides/beagle-lifespan) need the .htaccess rules
   (Apache or LiteSpeed); they switch themselves on the first time /rewrite-check answers.
   Otherwise every page is index.php?path=…  Links are relative to <base href>, so a subfolder works too. */
function url($path='', $q=[]){
  global $CFG;
  $path = trim((string)$path, '/');
  if(!empty($CFG['pretty_urls'])) return ($path === '' ? './' : implode('/', array_map('rawurlencode', explode('/', $path)))).($q ? '?'.http_build_query($q) : '');
  $q = ($path !== '' ? ['path'=>$path] : []) + $q;
  return $q ? 'index.php?'.http_build_query($q) : './';
}
function path_url($p){ return url($p); }
function abs_url($path='', $q=[]){ global $CFG; $u = url($path, $q); return rtrim($CFG['domain'], '/').'/'.($u === './' ? '' : $u); }
function go_to($path='', $q=[], $hash=''){ global $BASE; $u = url($path, $q); header('Location: '.$BASE.($u === './' ? '' : $u).$hash, true, 303); exit; }

const PUPPIES_PATH = 'beagle-puppies-for-sale';

function puppy_url($p){ return url(PUPPIES_PATH.'/'.$p['slug']); }
function parent_url($p){ return url('our-beagles').'#'.$p['slug']; }
function article_path($a){ return $a['type'] === 'page' ? $a['slug'] : ($a['type'] === 'blog' ? 'blog/' : 'guides/').$a['slug']; }

/* ---------------- delivery pages for each state (Admin → Locations) ---------------- */
function states(){ static $s = null; return $s ?? ($s = require BB_ROOT.'/inc/states.php'); }
function state_slug($code){ return slugify(states()[$code][0] ?? $code); }
function locations_on(){ global $SITE; $o = []; foreach($SITE['locations'] ?? [] as $c=>$l) if(!empty($l['on']) && isset(states()[$c])) $o[$c] = $l; return $o; }
function location_by_slug($slug){ foreach(locations_on() as $c=>$l) if(state_slug($c) === $slug) return $c; return null; }
function location_url($code){ return url(PUPPIES_PATH.'/'.state_slug($code)); }
/* rough road distance from Cincinnati to the state's first city: straight-line distance × 1.2, to the nearest 10 miles */
function miles_from_home($code){
  [$lat1, $lon1] = [39.10, -84.51]; [, , $lat2, $lon2] = states()[$code];
  $r = 3959 * 2 * asin(sqrt(sin(deg2rad($lat2 - $lat1) / 2) ** 2 + cos(deg2rad($lat1)) * cos(deg2rad($lat2)) * sin(deg2rad($lon2 - $lon1) / 2) ** 2));
  return (int)(round($r * 1.2 / 10) * 10);
}

/* visible puppies; photo paths whose file isn't on the server yet (e.g. imported from a CSV
   before the photo folder was uploaded) are left out so no page shows a broken image */
function public_puppies(){
  global $SITE; static $memo = null;
  if($memo === null){
    $memo = [];
    foreach($SITE['puppies'] as $p){
      if(!empty($p['hidden'])) continue;
      $p['photos'] = array_values(array_filter($p['photos'] ?? [], fn($f)=>is_string($f) && $f !== '' && is_file(BB_ROOT.'/'.$f)));
      $memo[] = $p;
    }
  }
  return $memo;
}
function puppies_with($status){ return array_values(array_filter(public_puppies(), fn($p)=>$p['status'] === $status)); }
function parent_by_id($id){ global $SITE; return $id === '' ? null : find_by($SITE['parents'], 'id', $id); }
function article($slug, $type){ global $SITE; foreach($SITE['articles'] as $a){ if($a['slug'] === $slug && $a['type'] === $type && empty($a['draft'])) return $a; } return null; }
function articles_of($type){ global $SITE; return array_values(array_filter($SITE['articles'], fn($a)=>$a['type'] === $type && empty($a['draft']))); }
function info_page($slug){ global $SITE; return find_by($SITE['pages'], 'slug', $slug); }
function payments_on(){ global $SITE; return array_values(array_filter($SITE['payments'], fn($p)=>!empty($p['on']))); }

/* ---------------- forms: spam checks ---------------- */
function form_token(){ $t = (string)time(); return $t.'.'.sign('form|'.$t); }
/* a real person takes a few seconds to fill in a form; bots fill the hidden "website" field or post instantly */
function form_ok(){
  if(trim((string)($_POST['website'] ?? '')) !== '') return false;
  $tok = explode('.', (string)($_POST['token'] ?? ''), 2);
  if(count($tok) !== 2 || !sign_ok('form|'.$tok[0], $tok[1])) return false;
  $age = time() - (int)$tok[0];
  return $age >= 3 && $age <= 86400;
}
/* at most 8 submissions an hour from one connection */
function throttle_ok(){
  $ip = substr(hash('sha256', ($_SERVER['REMOTE_ADDR'] ?? '').site_secret()), 0, 16);
  $t = data_read('throttle') ?: [];
  foreach($t as $k=>$times){ $t[$k] = array_values(array_filter($times, fn($x)=>$x > time() - 3600)); if(!$t[$k]) unset($t[$k]); }
  if(count($t[$ip] ?? []) >= 8){ data_write('throttle', $t); return false; }
  $t[$ip][] = time();
  data_write('throttle', $t);
  return true;
}
function post($k, $max=200){ return mb_substr(trim(str_replace("\r", '', (string)($_POST[$k] ?? ''))), 0, $max); }
function one_line($s){ return trim(preg_replace('/[\r\n\t]+/', ' ', (string)$s)); }

function new_ref(){
  do { $ref = 'BB-'.date('y').'-'.strtoupper(bin2hex(random_bytes(3))); } while(is_file(data_file('inquiries/'.$ref)));
  return $ref;
}

/* ---------------- ACTIONS ---------------- */
$errors = []; $old = [];
$action = $_SERVER['REQUEST_METHOD'] === 'POST' ? (string)($_POST['action'] ?? '') : '';

/* the inquiry form, the same on every page: a puppy (or "not sure yet"), a note, and the
   phone number and email we reply to (both required); the name is optional */
if($action === 'inquiry' || $action === 'contact'){
  $old = $_POST;
  $puppy = find_by(public_puppies(), 'id', (string)($_POST['puppy'] ?? ''));
  $f = ['name'=>one_line(post('name', 120)), 'email'=>one_line(post('email', 160)), 'phone'=>one_line(post('phone', 40)), 'notes'=>post('notes', 3000),
        'address1'=>'', 'address2'=>'', 'city'=>'', 'state'=>'', 'zip'=>'', 'payment'=>'', 'timing'=>'', 'contact_pref'=>''];
  if(strlen(preg_replace('/\D/', '', $f['phone'])) < 10) $errors['phone'] = 'Please enter a phone number we can call or text, with area code.';
  if(!filter_var($f['email'], FILTER_VALIDATE_EMAIL)) $errors['email'] = 'Please enter a valid email address.';
  if(!$errors && !form_ok()) $errors['form'] = 'Something went wrong sending the form. Please wait a moment and send it again.';
  if(!$errors && !throttle_ok()) $errors['form'] = 'You’ve sent several forms in a short time. Please email us at '.$CFG['email'].' instead.';
  if(!$errors){
    $ref = new_ref();
    $inq = ['ref'=>$ref, 'kind'=>'inquiry', 'time'=>date('c'), 'status'=>'new'] + $f + [
      'puppy_id'=>$puppy['id'] ?? '', 'puppy_name'=>$puppy['name'] ?? '', 'puppy_price'=>$puppy['price'] ?? '',
      'payment_name'=>'', 'page'=>one_line(post('page', 300)), 'admin_notes'=>''];
    data_write('inquiries/'.$ref, $inq);
    send_inquiry_mail($inq, $puppy);
    $_SESSION['last_inquiry'] = $ref;
    go_to('thank-you', ['ref'=>$ref]);
  }
}

if($action === 'waitlist'){
  $old = $_POST;
  $f = ['name'=>one_line(post('name', 120)), 'email'=>strtolower(one_line(post('email', 160))), 'phone'=>one_line(post('phone', 40)),
        'state'=>post('state', 2), 'sex'=>one_line(post('sex', 20)), 'color'=>one_line(post('color', 80)), 'timing'=>one_line(post('timing', 60)), 'notes'=>post('notes', 1000)];
  if($f['name'] === '') $errors['name'] = 'Please enter your name.';
  if(!filter_var($f['email'], FILTER_VALIDATE_EMAIL)) $errors['email'] = 'Please enter a valid email address.';
  if(!$errors && !form_ok()) $errors['form'] = 'Something went wrong sending the form. Please wait a moment and send it again.';
  if(!$errors && !throttle_ok()) $errors['form'] = 'You’ve sent several forms in a short time. Please email us at '.$CFG['email'].' instead.';
  if(!$errors){
    $w = data_read('waitlist') ?: ['list'=>[]];
    $w['list'] = array_values(array_filter($w['list'], fn($x)=>$x['email'] !== $f['email']));
    $w['list'][] = $f + ['time'=>date('c')];
    data_write('waitlist', $w);
    send_waitlist_mail($f);
    $_SESSION['flash'] = 'You’re on the waitlist! We’ve emailed you a confirmation. We’ll email you as soon as a new litter is born.';
    go_to('upcoming-litters', [], '#waitlist');
  }
}

/* ---------------- emails ---------------- */
function admin_link($ref){ global $CFG; return rtrim($CFG['domain'], '/').'/admin.php?v=inquiry&ref='.$ref; }

function send_inquiry_mail($q, $puppy){
  global $CFG;
  $brand = $CFG['brand'];
  $who = $q['name'] !== '' ? $q['name'] : $q['email'];
  $lines = array_values(array_filter([
    $q['name'] !== '' ? "Name: {$q['name']}" : '', "Phone: {$q['phone']}", "Email: {$q['email']}",
    $puppy ? "Puppy: {$puppy['name']}, ".trim($puppy['color'].' '.strtolower(sex_label($puppy['sex']))).(($puppy['price'] ?? '') !== '' ? ', '.money($puppy['price']) : '') : 'Puppy: not sure yet',
    $puppy ? 'Puppy page: '.abs_url(PUPPIES_PATH.'/'.$puppy['slug']) : '',
  ]));
  $notes = $q['notes'] !== '' ? "\nTheir note:\n{$q['notes']}\n" : '';
  $subject = $puppy ? "New inquiry: {$puppy['name']} — $who" : "New inquiry from $who";
  site_mail($CFG['email'], $subject, "New inquiry · reference {$q['ref']}\n\n".implode("\n", $lines)."\n".$notes."\nOpen in the admin: ".admin_link($q['ref'])."\n\nCall or text them, or reply to this email to answer them directly.", $q['email']);
  send_alert_text("{$brand}: new inquiry".($puppy ? " for {$puppy['name']}" : '')." from $who, {$q['phone']}. Ref {$q['ref']}");

  /* the customer's copy */
  $first = strtok(trim($q['name']), ' ') ?: 'there';
  $body = "Hi $first,\n\nThank you for your inquiry".($puppy ? " about {$puppy['name']}" : '').". We’ve received it and will call, text or email you ".fill('{reply_time}').".\n\nYour reference: {$q['ref']}\n"
    ."\nWhat you sent us\n".implode("\n", $lines)."\n".($q['notes'] !== '' ? "\nYour note:\n{$q['notes']}\n" : '')
    ."\nGood to know: we only contact you from {$CFG['email']}".($CFG['phone'] ? " or {$CFG['phone']}" : '').". If anyone else contacts you claiming to be {$brand}, please let us know.\n"
    ."\nOur health guarantee: ".abs_url('health-guarantee')."\nHow it works: ".abs_url('how-it-works')."\n\n— {$brand}\n{$CFG['city']}, {$CFG['state']} · {$CFG['email']}".($CFG['phone'] ? ' · '.$CFG['phone'] : '')."\n".$CFG['domain'];
  site_mail($q['email'], $puppy ? "We received your inquiry about {$puppy['name']} — {$brand}" : "We received your inquiry — {$brand}", $body, $CFG['email']);
}

function unsubscribe_url($email){ return abs_url('unsubscribe', ['e'=>$email, 'k'=>sign('unsub|'.strtolower($email))]); }

function send_waitlist_mail($f){
  global $CFG;
  site_mail($CFG['email'], "Waitlist sign-up: {$f['name']}", "New waitlist sign-up\n\nName: {$f['name']}\nEmail: {$f['email']}\nPhone: ".($f['phone'] ?: '—')."\nState: ".($f['state'] ?: '—')
    ."\nSex: ".($f['sex'] ?: 'Either')."\nColor: ".($f['color'] ?: 'Any')."\nTiming: ".($f['timing'] ?: '—')."\n".($f['notes'] !== '' ? "\nNotes:\n{$f['notes']}\n" : ''), $f['email']);
  site_mail($f['email'], "You’re on the {$CFG['brand']} waitlist", "Hi ".strtok($f['name'], ' ').",\n\nYou’re on our waitlist. We’ll email you as soon as a new litter is born, before the puppies are posted on our website.\n\n"
    ."In the meantime:\n· Puppies available now: ".abs_url(PUPPIES_PATH)."\n· How it works: ".abs_url('how-it-works')."\n· Beagle puppy care guide: ".abs_url('guides/beagle-puppy-care')."\n\n— {$CFG['brand']}\n{$CFG['city']}, {$CFG['state']} · {$CFG['email']}\n\nDon’t want these emails? Unsubscribe: ".unsubscribe_url($f['email']), $CFG['email'],
    ['List-Unsubscribe: <'.unsubscribe_url($f['email']).'>']);
}

/* ---------------- ROUTE ---------------- */
function request_path(){
  global $BASE;
  if(isset($_GET['path']) && is_string($_GET['path'])) return trim($_GET['path'], '/');
  $path = rawurldecode((string)parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH));
  if(strpos($path, $BASE) === 0) $path = substr($path, strlen($BASE));
  $path = trim($path, '/');
  return $path === 'index.php' ? '' : $path;
}
$PATH = request_path();
$via_path = !isset($_GET['path']);
$seg = $PATH === '' ? [] : explode('/', $PATH);
$page = 'notfound'; $puppy = $art = $info = null;

if($PATH === 'rewrite-check' && $via_path){
  header('Content-Type: application/json; charset=utf-8'); header('Cache-Control: no-store'); header('X-Robots-Tag: noindex');
  if(empty($CFG['pretty_urls']) && empty($CFG['pretty_auto'])){
    $SITE['settings']['pretty_urls'] = $SITE['settings']['pretty_auto'] = true;
    if(site_save($SITE)) $CFG['pretty_urls'] = true;
  }
  echo json_encode(['clean_urls'=>!empty($CFG['pretty_urls'])]);
  exit;
}
/* One address per page: "www." goes to the bare domain, and http goes to https, but only once the
   site has been seen working over https, so a host without an SSL certificate yet still works. */
$is_https = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') || strtolower($_SERVER['HTTP_X_FORWARDED_PROTO'] ?? '') === 'https' || (string)($_SERVER['SERVER_PORT'] ?? '') === '443';
$host = strtolower(preg_replace('/:\d+$/', '', (string)($_SERVER['HTTP_HOST'] ?? '')));
$local = $host === '' || $host === 'localhost' || preg_match('/^(127\.|\d+\.\d+\.\d+\.\d+$)/', $host);
if($is_https && !$local && empty($CFG['https_ok'])){ $SITE['settings']['https_ok'] = $CFG['https_ok'] = true; site_save($SITE); }
if(!$local && $_SERVER['REQUEST_METHOD'] === 'GET' && $PATH !== 'rewrite-check' && (strpos($host, 'www.') === 0 || (!$is_https && !empty($CFG['https_ok'])))){
  header('Location: '.(!empty($CFG['https_ok']) || $is_https ? 'https' : 'http').'://'.preg_replace('/^www\./', '', $host).($_SERVER['REQUEST_URI'] ?? '/'), true, 301); exit;
}

if(in_array($PATH, ['admin', 'login', 'wp-admin'], true)){ header('Location: '.$BASE.'admin.php', true, 302); exit; }

if($PATH === '') $page = 'home';
elseif($PATH === PUPPIES_PATH || $PATH === 'puppies') $page = 'puppies';
elseif(count($seg) === 2 && $seg[0] === PUPPIES_PATH && ($loc = location_by_slug($seg[1]))) $page = 'location';
elseif(count($seg) === 2 && $seg[0] === PUPPIES_PATH){ $puppy = find_by(public_puppies(), 'slug', $seg[1]); if($puppy) $page = 'puppy'; }
elseif(in_array($PATH, ['our-beagles', 'upcoming-litters', 'reviews', 'contact', 'guides', 'blog', 'thank-you', 'unsubscribe', 'sitemap.xml', 'robots.txt', 'agreement', 'receipt'], true)) $page = $PATH;
elseif(count($seg) === 2 && $seg[0] === 'guides'){ $art = article($seg[1], 'guide'); if($art) $page = 'article'; }
elseif(count($seg) === 2 && $seg[0] === 'blog'){ $art = article($seg[1], 'blog'); if($art) $page = 'article'; }
elseif(count($seg) === 1 && ($info = info_page($PATH))) $page = 'info';
/* a puppy whose page was removed: send visitors to the list rather than a dead end */
/* addresses that moved (Admin → Redirects) */
if($page === 'notfound' && isset($SITE['redirects'][$PATH])){ $u = url($SITE['redirects'][$PATH]); header('Location: '.$BASE.($u === './' ? '' : $u), true, 301); exit; }
if($page === 'notfound' && count($seg) === 2 && $seg[0] === 'puppies' && ($old = find_by(public_puppies(), 'old_slug', $seg[1]))){ header('Location: '.$BASE.puppy_url($old), true, 301); exit; }
if($page === 'notfound' && count($seg) === 2 && $seg[0] === PUPPIES_PATH){ header('Location: '.$BASE.url(PUPPIES_PATH), true, 301); exit; }

/* with clean addresses on, old index.php?path=… links move permanently to the clean address */
if(!empty($CFG['pretty_urls']) && !$via_path && $_SERVER['REQUEST_METHOD'] === 'GET' && $page !== 'notfound'){
  $q = $_GET; unset($q['path']);
  header('Location: '.$BASE.substr(url($PATH, $q), $PATH === '' ? 2 : 0), true, 301); exit;
}
if($page === 'notfound') http_response_code(404);

/* ---------------- robots.txt and sitemap.xml ---------------- */
if($page === 'robots.txt'){
  header('Content-Type: text/plain; charset=utf-8');
  echo "User-agent: *\nDisallow: /admin.php\nDisallow: /thank-you\nDisallow: /unsubscribe\nDisallow: /agreement\nDisallow: /receipt\n\nSitemap: ".abs_url('sitemap.xml')."\n";
  exit;
}
if($page === 'sitemap.xml'){
  $abs_img = fn($f)=>rtrim($CFG['domain'], '/').'/'.$f;
  $mod = gmdate('Y-m-d', @filemtime(data_file('site')) ?: time());
  $urls = [[abs_url(), $mod, []], [abs_url(PUPPIES_PATH), $mod, []]];
  foreach(locations_on() as $c=>$l) $urls[] = [abs_url(PUPPIES_PATH.'/'.state_slug($c)), $mod, []];
  foreach(public_puppies() as $p) if($p['status'] !== 'sold') $urls[] = [abs_url(PUPPIES_PATH.'/'.$p['slug']), substr($p['updated'] ?? $mod, 0, 10), array_map($abs_img, $p['photos'] ?? [])];
  foreach(['our-beagles', 'upcoming-litters', 'reviews', 'contact', 'guides', 'blog'] as $pg) $urls[] = [abs_url($pg), $mod, []];
  foreach($SITE['pages'] as $pg) $urls[] = [abs_url($pg['slug']), $mod, []];
  foreach($SITE['articles'] as $a) if(empty($a['draft'])) $urls[] = [abs_url(article_path($a)), $a['updated'] ?? $a['date'], $a['image'] ?? '' ? [$abs_img($a['image'])] : []];
  $x = fn($v)=>htmlspecialchars($v, ENT_XML1|ENT_QUOTES, 'UTF-8');
  header('Content-Type: application/xml; charset=utf-8');
  echo '<?xml version="1.0" encoding="UTF-8"?>'."\n".'<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9" xmlns:image="http://www.google.com/schemas/sitemap-image/1.1">'."\n";
  foreach($urls as [$loc, $lastmod, $imgs]){
    echo '  <url><loc>'.$x($loc).'</loc><lastmod>'.$x($lastmod).'</lastmod>';
    foreach($imgs as $img) echo '<image:image><image:loc>'.$x($img).'</image:loc></image:image>';
    echo "</url>\n";
  }
  echo "</urlset>\n";
  exit;
}

/* ---------------- the buyer's sales agreement and receipt (opened with the key in their link) ---------------- */
$doc = null;
if($page === 'agreement' || $page === 'receipt'){
  $ref = (string)($_GET['ref'] ?? $_POST['ref'] ?? '');
  $doc = sign_ok($page.'|'.$ref, (string)($_GET['k'] ?? $_POST['k'] ?? '')) ? inquiry_load($ref) : null;
  if(!$doc){ $page = 'notfound'; http_response_code(404); }
}
if($page === 'agreement' && $action === 'accept_agreement' && empty($doc['agreement']['accepted'])){
  $signed = one_line(post('signed_name', 120));
  if($signed === '' || empty($_POST['agree'])) $errors['accept'] = 'Type your full name and tick the box to accept.';
  else {
    if(empty($doc['deal'])) $doc['deal'] = deal_of($doc);
    $doc['agreement'] = ($doc['agreement'] ?? []) + ['sent'=>date('c')];
    $doc['agreement'] += ['accepted'=>date('c'), 'accepted_name'=>$signed, 'accepted_ip'=>$_SERVER['REMOTE_ADDR'] ?? '', 'accepted_agent'=>mb_substr((string)($_SERVER['HTTP_USER_AGENT'] ?? ''), 0, 200)];
    inquiry_save($doc);
    $d = deal_of($doc);
    site_mail($CFG['email'], "Agreement accepted: {$d['puppy']} — {$doc['name']}", "{$doc['name']} accepted the sales agreement for {$d['puppy']}, signing as “{$signed}”, on ".date('F j, Y \a\t g:ia').".\n\nOpen in the admin: ".admin_link($doc['ref']), $doc['email']);
    send_alert_text("{$CFG['brand']}: {$doc['name']} accepted the agreement for {$d['puppy']}. Ref {$doc['ref']}");
    site_mail($doc['email'], "Your accepted sales agreement for {$d['puppy']} — {$CFG['brand']}", "Hi ".(strtok(trim($doc['name']), ' ') ?: 'there').",\n\nThank you — you accepted the sales agreement for {$d['puppy']} on ".date('F j, Y').". Keep this email: your agreement is always here, and you can print it from the same page:\n".agreement_link($doc['ref'])."\n\n— {$CFG['brand']}\n{$CFG['email']}", $CFG['email']);
    $_SESSION['flash'] = 'Thank you! You’ve accepted the agreement. We’ve emailed you a copy of the link.';
    go_to('agreement', ['ref'=>$doc['ref'], 'k'=>sign('agreement|'.$doc['ref'])], '#accept');
  }
}

/* ---------------- unsubscribe ---------------- */
$unsub_done = false;
if($page === 'unsubscribe'){
  $e = strtolower(trim((string)($_GET['e'] ?? '')));
  if($e !== '' && sign_ok('unsub|'.$e, (string)($_GET['k'] ?? ''))){
    $w = data_read('waitlist') ?: ['list'=>[]];
    $w['list'] = array_values(array_filter($w['list'], fn($x)=>$x['email'] !== $e));
    data_write('waitlist', $w);
    $unsub_done = true;
  }
}

/* ---------------- titles, descriptions, breadcrumbs ---------------- */
$brand = $CFG['brand'];
$crumbs = [['Home', '']];
$title = $desc = $h1 = ''; $canonical = abs_url($PATH); $noindex = false; $og_type = 'website'; $og_image = ''; $lang = 'en';
$filters = ['sex'=>(string)($_GET['sex'] ?? ''), 'color'=>trim((string)($_GET['color'] ?? ''))];
if(!in_array($filters['sex'], ['', 'male', 'female'], true)) $filters['sex'] = '';

switch($page){
  case 'home':
    $title = fill($CFG['home_seo_title']); $desc = fill($CFG['home_seo_desc']); $canonical = abs_url(); $crumbs = [];
    break;
  case 'puppies':
    $title = fill($CFG['puppies_seo_title']); $desc = fill($CFG['puppies_seo_desc']); $h1 = fill($CFG['puppies_title']);
    $crumbs[] = [$h1, PUPPIES_PATH]; $canonical = abs_url(PUPPIES_PATH);
    if($filters['sex'] !== '' || $filters['color'] !== '') $noindex = true;
    break;
  case 'location':
    [$sname, $scities] = states()[$loc];
    $h1 = 'Beagle puppies for sale in '.$sname; $title = 'Beagle Puppies for Sale in '.$sname.' | '.$brand;
    $desc = 'Purebred beagle puppies delivered to '.implode(', ', array_slice($scities, 0, 3)).' and all of '.$sname.' from our home in '.$CFG['city'].', Ohio. Vet checked, microchipped, full papers, health guarantee.';
    $crumbs[] = [fill($CFG['puppies_title']), PUPPIES_PATH]; $crumbs[] = [$sname, PUPPIES_PATH.'/'.state_slug($loc)];
    break;
  case 'puppy':
    $h1 = puppy_heading($puppy);
    $title = $puppy['seo_title'] ?? '' ?: $puppy['name'].': '.puppy_kind($puppy).' for Sale | '.$brand;
    $desc = $puppy['seo_desc'] ?? '' ?: puppy_meta_desc($puppy);
    $crumbs[] = [fill($CFG['puppies_title']), PUPPIES_PATH]; $crumbs[] = [$puppy['name'], PUPPIES_PATH.'/'.$puppy['slug']];
    $og_type = 'product'; $og_image = $puppy['photos'][0] ?? '';
    if($puppy['status'] === 'sold') $noindex = true;
    break;
  case 'our-beagles':
    $h1 = 'Meet our beagles'; $title = 'Our Beagles: Meet the Parents of Our Puppies | '.$brand;
    $desc = 'Meet the registered, health-checked adult beagles behind every Beagle Brook litter: registration, health testing, size and temperament.';
    $crumbs[] = ['Our beagles', 'our-beagles'];
    break;
  case 'upcoming-litters':
    $h1 = 'Upcoming beagle litters and waitlist'; $title = 'Upcoming Beagle Litters & Puppy Waitlist | '.$brand;
    $desc = 'See planned and newborn Beagle Brook litters and join the waitlist to hear about new beagle puppies before they are posted publicly.';
    $crumbs[] = ['Upcoming litters', 'upcoming-litters'];
    break;
  case 'reviews':
    $h1 = 'Reviews from Beagle Brook families'; $title = 'Beagle Brook Reviews from Puppy Families';
    $desc = 'Read reviews from families who bought a beagle puppy from Beagle Brook in Cincinnati, Ohio.';
    $crumbs[] = ['Reviews', 'reviews'];
    break;
  case 'contact':
    $h1 = 'Contact Beagle Brook'; $title = 'Contact Beagle Brook | Beagle Breeder in Cincinnati, Ohio';
    $desc = 'Questions about a beagle puppy, delivery or the breed? Contact Beagle Brook in Cincinnati, Ohio. We reply '.fill('{reply_time}').'.';
    $crumbs[] = ['Contact', 'contact'];
    break;
  case 'guides':
    $h1 = 'Beagle guides'; $title = 'Beagle Guides: Breed, Price, Care, Training & Mixes';
    $desc = 'Expert beagle guides from a beagle breeder: the breed, price, lifespan, colors, training, food, mixes and buying a puppy safely.';
    $crumbs[] = ['Guides', 'guides'];
    break;
  case 'blog':
    $h1 = 'The Beagle Brook blog'; $title = 'Beagle Blog: Puppy Tips, Stories & News | '.$brand;
    $desc = 'Beagle puppy tips, stories and news from Beagle Brook, a beagle breeder in Cincinnati, Ohio.';
    $crumbs[] = ['Blog', 'blog'];
    break;
  case 'article':
    $h1 = $art['title']; $title = fill($art['seo_title'] ?: $art['title']); $desc = fill($art['seo_desc'] ?: plain($art['body']));
    $crumbs[] = $art['type'] === 'blog' ? ['Blog', 'blog'] : ['Guides', 'guides']; $crumbs[] = [$art['title'], article_path($art)];
    $og_type = 'article'; $og_image = $art['image'] ?? ''; $lang = $art['lang'] ?? 'en';
    break;
  case 'info':
    $h1 = fill($info['title']); $title = fill($info['seo_title'] ?: $info['title'].' | '.$brand); $desc = fill($info['seo_desc'] ?: plain($info['body']));
    $crumbs[] = [$info['menu'] ?: $info['title'], $info['slug']];
    break;
  case 'agreement':
  case 'receipt':
    $h1 = $page === 'agreement' ? 'Sales agreement' : 'Payment receipt'; $title = $h1.' | '.$brand; $noindex = true;
    header('X-Robots-Tag: noindex, nofollow'); header('Cache-Control: private, no-store'); header('Referrer-Policy: no-referrer');
    break;
  case 'thank-you':
    $h1 = 'Thank you — we’ve received it'; $title = 'Thank you | '.$brand; $noindex = true;
    break;
  case 'unsubscribe':
    $h1 = $unsub_done ? 'You’re unsubscribed' : 'Unsubscribe'; $title = 'Unsubscribe | '.$brand; $noindex = true;
    break;
  default:
    $h1 = 'Page not found'; $title = 'Page not found | '.$brand; $noindex = true;
}
if(mb_strlen($desc) > 160) $desc = plain($desc, 158);

render_page();
