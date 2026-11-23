<?php
/* =============================================================
   Pepperwhisker — the website. Edit content in admin.php;
   everything is saved to data/ and never in this file.
   ============================================================= */

define('PW_ROOT', __DIR__);
@ini_set('display_errors', '0');
error_reporting(E_ALL & ~E_NOTICE & ~E_WARNING & ~E_DEPRECATED);
require PW_ROOT.'/inc/core.php';
require PW_ROOT.'/inc/views.php';
start_session();
header('X-Content-Type-Options: nosniff');
header('Referrer-Policy: strict-origin-when-cross-origin');
header('X-Frame-Options: SAMEORIGIN');

$SITE = site_load();
$CFG  = $SITE['settings'];
$BASE = rtrim(str_replace('\\', '/', dirname($_SERVER['SCRIPT_NAME'] ?? '/index.php')), '/').'/';

/* ---------------- URLS ----------------
   Clean addresses (/guides/giant-schnauzer, /schnauzer-grooming) need the .htaccess rules (Apache or
   LiteSpeed); they switch themselves on the first time /rewrite-check answers.
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

function article_path($a){ return ($a['type'] === 'blog' ? 'blog/' : 'guides/').$a['slug']; }
function article($slug, $type){ global $SITE; foreach($SITE['articles'] as $a){ if($a['slug'] === $slug && $a['type'] === $type && empty($a['draft'])) return $a; } return null; }
function articles_of($type){ global $SITE; return array_values(array_filter($SITE['articles'], fn($a)=>$a['type'] === $type && empty($a['draft']))); }
function info_page($slug){ global $SITE; return find_by($SITE['pages'], 'slug', $slug); }
function topic_by_slug($slug){ foreach(GUIDE_GROUPS as $g=>$t) if($t[1] === $slug) return $g; return null; }
/* "ca-pub-1234567890123456" when Google AdSense is set up in Settings */
function adsense_id(){ global $CFG; $id = trim((string)$CFG['adsense_client']); if(preg_match('/^pub-\d{10,20}$/', $id)) $id = 'ca-'.$id; return preg_match('/^ca-pub-\d{10,20}$/', $id) ? $id : ''; }

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
/* at most 6 messages an hour from one connection */
function throttle_ok(){
  $ip = substr(hash('sha256', ($_SERVER['REMOTE_ADDR'] ?? '').site_secret()), 0, 16);
  $t = data_read('throttle') ?: [];
  foreach($t as $k=>$times){ $t[$k] = array_values(array_filter($times, fn($x)=>$x > time() - 3600)); if(!$t[$k]) unset($t[$k]); }
  if(count($t[$ip] ?? []) >= 6){ data_write('throttle', $t); return false; }
  $t[$ip][] = time();
  data_write('throttle', $t);
  return true;
}
function post($k, $max=200){ return mb_substr(trim(str_replace("\r", '', (string)($_POST[$k] ?? ''))), 0, $max); }
function one_line($s){ return trim(preg_replace('/[\r\n\t]+/', ' ', (string)$s)); }

/* ---------------- the contact form ---------------- */
$errors = []; $old = [];
$action = $_SERVER['REQUEST_METHOD'] === 'POST' ? (string)($_POST['action'] ?? '') : '';
if($action === 'contact'){
  $old = $_POST;
  $f = ['name'=>one_line(post('name', 120)), 'email'=>one_line(post('email', 160)), 'message'=>post('message', 5000)];
  if(!filter_var($f['email'], FILTER_VALIDATE_EMAIL)) $errors['email'] = 'Please enter a valid email address so we can reply.';
  if(mb_strlen($f['message']) < 5) $errors['message'] = 'Please write your message.';
  if(!$errors && !form_ok()) $errors['form'] = 'Something went wrong sending the form. Please wait a moment and send it again.';
  if(!$errors && !throttle_ok()) $errors['form'] = 'You’ve sent several messages in a short time. Please email us at '.$CFG['email'].' instead.';
  if(!$errors){
    $id = date('Ymd-His').'-'.bin2hex(random_bytes(3));
    data_write('messages/'.$id, ['id'=>$id, 'time'=>date('c'), 'read'=>false] + $f + ['page'=>one_line(post('page', 300))]);
    $who = $f['name'] !== '' ? $f['name'] : $f['email'];
    site_mail($CFG['email'], "Message from $who — {$CFG['brand']}", "New message from the contact form\n\nName: ".($f['name'] ?: '—')."\nEmail: {$f['email']}\n\n{$f['message']}\n\nReply to this email to answer them directly.\nAll messages: ".rtrim($CFG['domain'], '/')."/admin.php?v=messages", $f['email']);
    $_SESSION['flash'] = 'Thank you, your message has been sent. We’ll reply by email.';
    go_to('contact', [], '#sent');
  }
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
$page = 'notfound'; $art = $info = $topic = null;

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
elseif(in_array($PATH, ['guides', 'blog', 'contact', 'sitemap.xml', 'robots.txt', 'ads.txt'], true)) $page = $PATH;
elseif(count($seg) === 2 && $seg[0] === 'guides'){ $art = article($seg[1], 'guide'); if($art) $page = 'article'; }
elseif(count($seg) === 2 && $seg[0] === 'blog'){ $art = article($seg[1], 'blog'); if($art) $page = 'article'; }
elseif(count($seg) === 1 && ($topic = topic_by_slug($PATH)) !== null) $page = 'topic';
elseif(count($seg) === 1 && ($info = info_page($PATH))) $page = 'info';
/* addresses that moved (Admin → Redirects) */
if($page === 'notfound' && isset($SITE['redirects'][$PATH])){ $u = url($SITE['redirects'][$PATH]); header('Location: '.$BASE.($u === './' ? '' : $u), true, 301); exit; }
/* a guide asked for without /guides/ in front */
if($page === 'notfound' && count($seg) === 1 && article($PATH, 'guide')){ header('Location: '.$BASE.url('guides/'.$PATH), true, 301); exit; }

/* with clean addresses on, old index.php?path=… links move permanently to the clean address */
if(!empty($CFG['pretty_urls']) && !$via_path && $_SERVER['REQUEST_METHOD'] === 'GET' && $page !== 'notfound'){
  $q = $_GET; unset($q['path']);
  header('Location: '.$BASE.substr(url($PATH, $q), $PATH === '' ? 2 : 0), true, 301); exit;
}
if($page === 'notfound') http_response_code(404);

/* ---------------- robots.txt, ads.txt and sitemap.xml ---------------- */
if($page === 'robots.txt'){
  header('Content-Type: text/plain; charset=utf-8');
  echo "User-agent: *\nDisallow: /admin.php\n\nSitemap: ".abs_url('sitemap.xml')."\n";
  exit;
}
if($page === 'ads.txt'){
  if(adsense_id() === ''){ http_response_code(404); header('Content-Type: text/plain; charset=utf-8'); echo "# No ad network set up yet.\n"; exit; }
  header('Content-Type: text/plain; charset=utf-8');
  echo 'google.com, '.substr(adsense_id(), 3).", DIRECT, f08c47fec0942fa0\n";
  exit;
}
if($page === 'sitemap.xml'){
  $mod = gmdate('Y-m-d', @filemtime(data_file('site')) ?: time());
  $urls = [[abs_url(), $mod, []], [abs_url('guides'), $mod, []]];
  foreach(GUIDE_GROUPS as $t) $urls[] = [abs_url($t[1]), $mod, []];
  if(articles_of('blog')) $urls[] = [abs_url('blog'), $mod, []];
  $urls[] = [abs_url('contact'), $mod, []];
  foreach($SITE['pages'] as $pg) $urls[] = [abs_url($pg['slug']), $mod, []];
  foreach($SITE['articles'] as $a) if(empty($a['draft'])) $urls[] = [abs_url(article_path($a)), $a['updated'] ?? $a['date'], ($a['image'] ?? '') !== '' ? [abs_img($a['image'])] : []];
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

/* ---------------- titles, descriptions, breadcrumbs ---------------- */
$brand = $CFG['brand'];
$crumbs = [['Home', '']];
$title = $desc = $h1 = ''; $canonical = abs_url($PATH); $noindex = false; $og_type = 'website'; $og_image = ''; $lang = 'en';

switch($page){
  case 'home':
    $title = fill($CFG['home_seo_title']); $desc = fill($CFG['home_seo_desc']); $canonical = abs_url(); $crumbs = [];
    break;
  case 'guides':
    $h1 = 'Schnauzer guides'; $title = 'Schnauzer Guides: Breeds, Grooming, Health & Mixes | '.$brand;
    $desc = 'All '.$brand.' schnauzer guides: miniature, standard and giant schnauzers, colors, haircuts, health, lifespan, mixes, prices and adopting.';
    $crumbs[] = ['Guides', 'guides'];
    break;
  case 'topic':
    [$label, $slug, $intro] = GUIDE_GROUPS[$topic];
    $h1 = ['breeds'=>'Schnauzer breeds', 'colors'=>'Schnauzer colors and coats', 'grooming'=>'Schnauzer grooming and haircuts', 'health'=>'Schnauzer health, lifespan and food',
           'mixes'=>'Schnauzer mixes', 'puppies'=>'Schnauzer puppies: price, buying safely and rescue'][$topic] ?? $label;
    $title = $h1.' | '.$brand; $desc = $intro;
    $crumbs[] = ['Guides', 'guides']; $crumbs[] = [$label, $slug];
    break;
  case 'blog':
    $h1 = 'The '.$brand.' blog'; $title = 'Schnauzer Blog: News, Stories & Tips | '.$brand;
    $desc = 'News, stories and seasonal tips for schnauzer owners from '.$brand.'.';
    $crumbs[] = ['Blog', 'blog'];
    if(!articles_of('blog')) $noindex = true;
    break;
  case 'article':
    $h1 = $art['title']; $title = fill($art['seo_title'] ?: $art['title']); $desc = fill($art['seo_desc'] ?: plain($art['body']));
    if($art['type'] === 'blog') $crumbs[] = ['Blog', 'blog'];
    elseif(isset(GUIDE_GROUPS[$art['group']]) && ($art['lang'] ?? 'en') === 'en'){ $crumbs[] = ['Guides', 'guides']; $crumbs[] = [GUIDE_GROUPS[$art['group']][0], GUIDE_GROUPS[$art['group']][1]]; }
    else $crumbs[] = ['Guides', 'guides'];
    $crumbs[] = [short_title($art['title']), article_path($art)];
    $og_type = 'article'; $og_image = $art['image'] ?? ''; $lang = $art['lang'] ?? 'en';
    break;
  case 'info':
    $h1 = fill($info['title']); $title = fill($info['seo_title'] ?: $info['title'].' | '.$brand); $desc = fill($info['seo_desc'] ?: plain($info['body']));
    $crumbs[] = [$info['menu'] ?: $info['title'], $info['slug']];
    break;
  case 'contact':
    $h1 = 'Contact '.$brand; $title = 'Contact '.$brand; $desc = 'Questions, corrections or schnauzer stories? Contact '.$brand.' by email or with our contact form.';
    $crumbs[] = ['Contact', 'contact'];
    break;
  default:
    $h1 = 'Page not found'; $title = 'Page not found | '.$brand; $noindex = true;
}
if(mb_strlen($desc) > 160) $desc = plain($desc, 158);

render_page();
