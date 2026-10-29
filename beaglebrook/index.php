<?php
/* =============================================================
   Beagle Brook — the website. Edit content in admin.php;
   everything is saved to data/ and never in this file.
   ============================================================= */

define('BB_ROOT', __DIR__);
@ini_set('display_errors', '0');
error_reporting(E_ALL & ~E_NOTICE & ~E_WARNING & ~E_DEPRECATED);
require BB_ROOT.'/inc/core.php';
require BB_ROOT.'/inc/views.php';
start_session();
header('X-Content-Type-Options: nosniff');
header('Referrer-Policy: strict-origin-when-cross-origin');
header('X-Frame-Options: SAMEORIGIN');

$SITE = site_load();
$CFG  = $SITE['settings'];
$BASE = rtrim(str_replace('\\', '/', dirname($_SERVER['SCRIPT_NAME'] ?? '/index.php')), '/').'/';

/* ---------------- URLS ----------------
   Clean addresses (/beagle-puppies-for-sale/daisy…, /guides/beagle-price) need the .htaccess rules
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
function article_path($a){ return ($a['type'] === 'blog' ? 'blog/' : 'guides/').$a['slug']; }

function public_puppies(){ global $SITE; return array_values(array_filter($SITE['puppies'], fn($p)=>empty($p['hidden']))); }
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

if($action === 'inquiry' || $action === 'contact'){
  $old = $_POST;
  $puppy = $action === 'inquiry' ? find_by(public_puppies(), 'id', (string)($_POST['puppy'] ?? '')) : null;
  $f = ['name'=>one_line(post('name', 120)), 'email'=>one_line(post('email', 160)), 'phone'=>one_line(post('phone', 40)), 'notes'=>post('notes', 3000)];
  if($f['name'] === '') $errors['name'] = 'Please enter your full name.';
  if(!filter_var($f['email'], FILTER_VALIDATE_EMAIL)) $errors['email'] = 'Please enter a valid email address.';
  if($action === 'inquiry'){
    $f += ['address1'=>one_line(post('address1', 160)), 'address2'=>one_line(post('address2', 120)), 'city'=>one_line(post('city', 80)),
           'state'=>post('state', 2), 'zip'=>one_line(post('zip', 10)), 'payment'=>post('payment', 40), 'timing'=>one_line(post('timing', 60)),
           'contact_pref'=>one_line(post('contact_pref', 20))];
    if(strlen(preg_replace('/\D/', '', $f['phone'])) < 10) $errors['phone'] = 'Please enter a phone number we can call or text, with area code.';
    if($f['address1'] === '') $errors['address1'] = 'Please enter the delivery street address.';
    if($f['city'] === '') $errors['city'] = 'Please enter the city.';
    if(!isset(US_STATES[$f['state']])) $errors['state'] = 'Please choose the state.';
    if(!preg_match('/^\d{5}(-\d{4})?$/', $f['zip'])) $errors['zip'] = 'Please enter a 5-digit ZIP code.';
    $pay = find_by(payments_on(), 'key', $f['payment']);
    if(!$pay) $errors['payment'] = 'Please choose how you would like to pay.';
    if(empty($_POST['agree'])) $errors['agree'] = 'Please confirm you’ve read how the deposit works.';
    if(!$puppy) $errors['puppy'] = 'This puppy is no longer listed. Please choose another puppy.';
  } else {
    if($f['notes'] === '') $errors['notes'] = 'Please write your message.';
  }
  if(!$errors && !form_ok()) $errors['form'] = 'Something went wrong sending the form. Please wait a moment and send it again.';
  if(!$errors && !throttle_ok()) $errors['form'] = 'You’ve sent several forms in a short time. Please email us at '.$CFG['email'].' instead.';
  if(!$errors){
    $ref = new_ref();
    $inq = ['ref'=>$ref, 'kind'=>$action, 'time'=>date('c'), 'status'=>'new'] + $f + [
      'puppy_id'=>$puppy['id'] ?? '', 'puppy_name'=>$puppy['name'] ?? '', 'puppy_price'=>$puppy['price'] ?? '',
      'payment_name'=>isset($pay) && $pay ? $pay['name'] : '', 'page'=>one_line(post('page', 300)), 'admin_notes'=>''];
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
  $lines = ["Name: {$q['name']}", "Email: {$q['email']}", 'Phone: '.($q['phone'] ?: '—')];
  if($q['kind'] === 'inquiry'){
    $lines[] = 'Delivery address: '.$q['address1'].($q['address2'] ? ', '.$q['address2'] : '').", {$q['city']}, {$q['state']} {$q['zip']}";
    $lines[] = "Preferred payment: {$q['payment_name']}";
    if($q['timing'] !== '') $lines[] = "Delivery timing: {$q['timing']}";
    if($q['contact_pref'] !== '') $lines[] = "Best way to reach them: {$q['contact_pref']}";
  }
  $notes = $q['notes'] !== '' ? "\n".($q['kind'] === 'inquiry' ? 'Their notes' : 'Message').":\n{$q['notes']}\n" : '';
  if($puppy){
    $head = "New inquiry for {$puppy['name']} ({$puppy['color']} ".strtolower(sex_label($puppy['sex'])).', '.money($puppy['price']).")\nReference: {$q['ref']}";
    $subject = "New inquiry: {$puppy['name']} — {$q['name']}, {$q['city']} {$q['state']}";
  } else {
    $head = "New message from the contact form\nReference: {$q['ref']}";
    $subject = "Website message from {$q['name']}";
  }
  site_mail($CFG['email'], $subject, $head."\n\n".implode("\n", $lines)."\n".$notes."\nOpen in the admin: ".admin_link($q['ref'])."\n\nReply to this email to answer them directly.", $q['email']);
  send_alert_text($puppy
    ? "{$brand}: new inquiry for {$puppy['name']} from {$q['name']}, {$q['phone']}, {$q['city']} {$q['state']}. Ref {$q['ref']}"
    : "{$brand}: new website message from {$q['name']} ({$q['email']}). Ref {$q['ref']}");

  /* the customer's copy */
  $what = $puppy ? "your inquiry for {$puppy['name']}" : 'your message';
  $body = "Hi ".strtok($q['name'], ' ').",\n\nThank you for $what. We’ve received it and will reply ".fill('{reply_time}')." by ".($q['contact_pref'] !== '' && ($q['contact_pref'] ?? '') !== 'Email' ? strtolower($q['contact_pref']).' or email' : 'phone, text or email').".\n\nYour reference: {$q['ref']}\n";
  if($puppy){
    $body .= "\nWhat happens next\n"
      ."1. We get in touch to answer your questions and arrange a live video call with {$puppy['name']}.\n"
      ."2. If you’d like to go ahead, a ".fill('{deposit}')." deposit reserves {$puppy['name']}. You receive a written sales agreement and receipt.\n"
      ."3. We agree your delivery date. The remaining ".fill('{balance}')." is due at delivery.\n"
      ."\nWhat you sent us\n".implode("\n", $lines)."\n".$notes;
  }
  $body .= "\nStaying safe: we only send payment details from {$CFG['email']}, after we’ve spoken with you. We never ask for gift cards or cryptocurrency. If anyone else contacts you claiming to be {$brand}, please let us know.\n"
    ."\nOur health guarantee: ".abs_url('health-guarantee')."\nHow it works: ".abs_url('how-it-works')."\n\n— {$brand}\n{$CFG['city']}, {$CFG['state']} · {$CFG['email']}".($CFG['phone'] ? ' · '.$CFG['phone'] : '')."\n".$CFG['domain'];
  site_mail($q['email'], $puppy ? "We received your inquiry for {$puppy['name']} — {$brand}" : "We received your message — {$brand}", $body, $CFG['email']);
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
if(in_array($PATH, ['admin', 'login', 'wp-admin'], true)){ header('Location: '.$BASE.'admin.php', true, 302); exit; }

if($PATH === '') $page = 'home';
elseif($PATH === PUPPIES_PATH || $PATH === 'puppies') $page = 'puppies';
elseif(count($seg) === 2 && $seg[0] === PUPPIES_PATH){ $puppy = find_by(public_puppies(), 'slug', $seg[1]); if($puppy) $page = 'puppy'; }
elseif(in_array($PATH, ['our-beagles', 'upcoming-litters', 'reviews', 'contact', 'guides', 'blog', 'thank-you', 'unsubscribe', 'sitemap.xml', 'robots.txt'], true)) $page = $PATH;
elseif(count($seg) === 2 && $seg[0] === 'guides'){ $art = article($seg[1], 'guide'); if($art) $page = 'article'; }
elseif(count($seg) === 2 && $seg[0] === 'blog'){ $art = article($seg[1], 'blog'); if($art) $page = 'article'; }
elseif(count($seg) === 1 && ($info = info_page($PATH))) $page = 'info';
/* a puppy whose page was removed: send visitors to the list rather than a dead end */
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
  echo "User-agent: *\nDisallow: /admin.php\nDisallow: /thank-you\nDisallow: /unsubscribe\n\nSitemap: ".abs_url('sitemap.xml')."\n";
  exit;
}
if($page === 'sitemap.xml'){
  $abs_img = fn($f)=>rtrim($CFG['domain'], '/').'/'.$f;
  $mod = gmdate('Y-m-d', @filemtime(data_file('site')) ?: time());
  $urls = [[abs_url(), $mod, []], [abs_url(PUPPIES_PATH), $mod, []]];
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
