<?php
/* Shared by index.php (storefront) and admin.php (backend):
   data storage, sessions and the few helpers both need. */
if(!defined('FK_ROOT')){ http_response_code(404); exit; }
require_once FK_ROOT.'/inc/i18n.php';

/* Every data file starts with this line, so opening one in a browser shows nothing
   even on hosts that ignore data/.htaccess. The JSON follows on the next line. */
const FK_GUARD = "<?php http_response_code(404); exit; ?>\n";

const PRODUCT_STATUSES = ['in'=>'In stock', 'new'=>'New', 'low'=>'Low stock', 'preorder'=>'Preorder', 'soldout'=>'Sold out'];
const CONDITIONS       = ['Sealed', 'Graded', 'Near Mint', 'Lightly Played'];
const ORDER_STATUSES   = ['new'=>'New', 'invoiced'=>'Payment details sent', 'paid'=>'Paid', 'shipped'=>'Shipped', 'cancelled'=>'Cancelled'];

function status_label($list, $key){ return $list[$key] ?? (string)$key; }

function h($s){ return htmlspecialchars((string)$s, ENT_QUOTES|ENT_SUBSTITUTE, 'UTF-8'); }

function start_session(){
  if(session_status() === PHP_SESSION_ACTIVE) return;
  $https = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') || ($_SERVER['HTTP_X_FORWARDED_PROTO'] ?? '') === 'https';
  session_set_cookie_params(['lifetime'=>0, 'path'=>'/', 'secure'=>$https, 'httponly'=>true, 'samesite'=>'Lax']);
  /* some hosts' session folder isn't writable (or is outside open_basedir): keep sessions in data/ instead */
  $path = preg_replace('/^.*;/', '', (string)session_save_path()) ?: sys_get_temp_dir();
  if(!@is_dir($path) || !@is_writable($path)){
    $own = FK_ROOT.'/data/sessions';
    if(!is_dir($own)) @mkdir($own, 0700, true);
    if(is_writable($own)){ session_save_path($own); @ini_set('session.gc_probability', '1'); @ini_set('session.gc_divisor', '100'); }
  }
  @session_start();
}

/* ---------------- data files ---------------- */
function data_dir(){
  $d = FK_ROOT.'/data';
  if(!is_dir($d)) @mkdir($d, 0755, true);
  return $d;
}

function data_file($name){ return data_dir().'/'.$name.'.php'; }

function data_read($name){
  $file = data_file($name);
  if(!is_file($file)) return null;
  $raw = (string)file_get_contents($file);
  $v = json_decode(substr($raw, strpos($raw, "\n") + 1), true);
  return is_array($v) ? $v : null;
}

/* write to a temp file then rename, so a crash never leaves half a file */
function data_write($name, $value){
  $file = data_file($name);
  if(!is_dir(dirname($file))) @mkdir(dirname($file), 0755, true);
  $json = json_encode($value, JSON_PRETTY_PRINT|JSON_UNESCAPED_SLASHES|JSON_UNESCAPED_UNICODE);
  if($json === false) return false;
  $tmp = $file.'.'.bin2hex(random_bytes(4)).'.tmp';
  if(@file_put_contents($tmp, FK_GUARD.$json, LOCK_EX) === false) return false;
  if(!@rename($tmp, $file)){ @unlink($tmp); return false; }
  return true;
}

/* ---------------- store (products, settings, …) ---------------- */
function store_defaults(){ return require FK_ROOT.'/inc/defaults.php'; }

function store_load(){
  $d = store_defaults();
  $s = data_read('store');
  /* first run, or data from a version of the shop without French and Dutch text: seed from the defaults
     (store_save keeps the old file in data/backups/) */
  if(!$s || !isset($s['i18n'])){ $s = $d; store_save($s); }
  $s += $d;                                       // sections added by later versions
  $s['settings'] += $d['settings'];
  foreach(array_keys(LANGS) as $L){
    $s['i18n'][$L] = ($s['i18n'][$L] ?? []) + ($d['i18n'][$L] ?? []);
    $s['i18n'][$L]['settings'] = ($s['i18n'][$L]['settings'] ?? []) + ($d['i18n'][$L]['settings'] ?? []);
  }
  if((int)($s['settings']['content_version'] ?? 1) < CONTENT_VERSION){ $s['settings']['content_version'] = CONTENT_VERSION; store_save($s); }
  return $s;
}

/* Content that a newer version of the shop adds or improves is applied once, by version number. */
const CONTENT_VERSION = 1;

/* ---------------- two languages ----------------
   data/store.php keeps the text of each language next to the shared data:
   - products, categories, series, sets and payment methods carry their text per language in 'tr' => ['nl'=>[…], 'fr'=>[…]]
   - the delivery options carry theirs the same way (shipping → methods)
   - settings text, collections, guides, pages and the FAQ live in 'i18n' => ['nl'=>[…], 'fr'=>[…]]
   store_view() turns that into one language's plain store (what index.php and admin.php work with);
   store_unview() puts an edited plain store back. Text missing in one language falls back to the other. */
const I18N_KEYED    = ['products'=>['name','desc','seo_title','seo_desc','release'],
                       'categories'=>['label','blurb','slug','h1','seo_title','seo_desc','intro'],
                       'series'=>['name','h1','seo_title','seo_desc','intro'],
                       'sets'=>['intro','seo_title','seo_desc'],
                       'payments'=>['label','note']];
const I18N_METHODS  = ['label','days'];
const I18N_SETTINGS = ['tagline','strip_text','strip_link_text','strip_link_url','hero_title','hero_lede','footer_blurb',
                       'home_seo_title','home_seo_desc','home_intro','shipping_policy'];
const I18N_LISTS    = ['collections','guides','pages','faqs'];

function lang_other($L){ foreach(array_keys(LANGS) as $x) if($x !== $L) return $x; return $L; }

function store_view($raw, $L){
  $o = lang_other($L);
  $s = $raw; unset($s['i18n']);
  $pick = function($e, $fields) use($L, $o){
    $tr = ($e['tr'][$L] ?? []) + ($e['tr'][$o] ?? []);
    foreach($fields as $f) $e[$f] = (string)($tr[$f] ?? ($e[$f] ?? ''));
    return $e;
  };
  foreach(I18N_KEYED as $sec=>$fields) foreach($s[$sec] ?? [] as $k=>$e) $s[$sec][$k] = $pick($e, $fields);
  foreach($s['shipping']['methods'] ?? [] as $k=>$e) $s['shipping']['methods'][$k] = $pick($e, I18N_METHODS);
  $s['settings'] = array_filter(($raw['i18n'][$L]['settings'] ?? []) + ($raw['i18n'][$o]['settings'] ?? []), fn($v)=>$v !== null) + $raw['settings'];
  foreach(I18N_LISTS as $sec) $s[$sec] = $raw['i18n'][$L][$sec] ?? [];
  $s['lang'] = $L;
  return $s;
}

function store_unview($raw, $s, $L){
  $o = lang_other($L);
  /* a field this language didn't have, still showing the other language's text (or nothing), stays unset,
     so it keeps following the other language until someone writes it here */
  $put = function($e, $fields, $old) use($L, $o){
    $tr = $e['tr'] ?? $old;
    $mine = $tr[$L] ?? []; $tr[$L] = [];
    foreach($fields as $f){
      $v = (string)($e[$f] ?? ''); unset($e[$f]);
      if(!isset($mine[$f]) && ($v === '' || $v === (string)($tr[$o][$f] ?? ''))) continue;
      $tr[$L][$f] = $v;
    }
    $e['tr'] = $tr;
    return $e;
  };
  foreach(I18N_KEYED as $sec=>$fields){
    $old = [];
    foreach($raw[$sec] ?? [] as $k=>$e) $old[(string)($sec === 'products' ? ($e['id'] ?? $k) : $k)] = $e['tr'] ?? [];
    foreach($s[$sec] ?? [] as $k=>$e) $s[$sec][$k] = $put($e, $fields, $old[(string)($sec === 'products' ? ($e['id'] ?? $k) : $k)] ?? []);
  }
  foreach($s['shipping']['methods'] ?? [] as $k=>$e) $s['shipping']['methods'][$k] = $put($e, I18N_METHODS, $raw['shipping']['methods'][$k]['tr'] ?? []);
  $s['i18n'] = $raw['i18n'] ?? [];
  foreach(I18N_SETTINGS as $f){
    $v = (string)($s['settings'][$f] ?? ''); unset($s['settings'][$f]);
    if(!isset($raw['i18n'][$L]['settings'][$f]) && ($v === '' || $v === (string)($raw['i18n'][$o]['settings'][$f] ?? ''))) continue;
    $s['i18n'][$L]['settings'][$f] = $v;
  }
  foreach(I18N_LISTS as $sec){ $s['i18n'][$L][$sec] = array_values($s[$sec] ?? []); unset($s[$sec]); }
  unset($s['lang']);
  return $s;
}

/* keeps the last 30 versions in data/backups/ */
function store_save($s){
  $cur = data_file('store');
  if(is_file($cur)){
    $dir = data_dir().'/backups';
    if(!is_dir($dir)) @mkdir($dir, 0755, true);
    @copy($cur, $dir.'/store-'.date('Ymd-His').'-'.bin2hex(random_bytes(2)).'.php');
    $old = glob($dir.'/store-*.php') ?: [];
    sort($old);
    foreach(array_slice($old, 0, max(0, count($old) - 30)) as $f) @unlink($f);
  }
  return data_write('store', $s);
}

/* ---------------- products ---------------- */
function photo_slots(){ return ['', '-2', '-3', '-4']; }

function photos($id){
  $out = [];
  foreach(photo_slots() as $suffix){
    foreach(['jpg','jpeg','png','webp'] as $ext){
      $rel = "assets/products/{$id}{$suffix}.{$ext}";
      if(is_file(FK_ROOT.'/'.$rel)){ $out[] = $rel; break; }
    }
  }
  return $out;
}

/* Shrinks photos over $max px (phone photos are often 4000px and several MB) and turns
   sideways phone JPEGs upright. Needs GD; skipped quietly without it, or when the host's
   memory limit is too small to open the image. */
function shrink_image($file, $max=1600){
  $i = @getimagesize($file);
  if(!$i || !function_exists('imagecreatetruecolor')) return;
  $type = $i[2];
  $orient = ($type === IMAGETYPE_JPEG && function_exists('exif_read_data')) ? (int)(@exif_read_data($file)['Orientation'] ?? 1) : 1;
  if(max($i[0], $i[1]) <= $max && !in_array($orient, [3,6,8], true)) return;
  $load = [IMAGETYPE_JPEG=>'imagecreatefromjpeg', IMAGETYPE_PNG=>'imagecreatefrompng', IMAGETYPE_WEBP=>'imagecreatefromwebp'][$type] ?? '';
  if(!$load || !function_exists($load)) return;
  $limit = ini_get('memory_limit');
  $bytes = (int)$limit * (['g'=>1073741824, 'm'=>1048576, 'k'=>1024][strtolower(substr($limit, -1))] ?? 1);
  if($bytes > 0 && memory_get_usage() + $i[0] * $i[1] * 5 * 2 > $bytes) return;
  $src = @$load($file);
  if(!$src) return;
  if($orient === 3) $src = imagerotate($src, 180, 0);
  if($orient === 6) $src = imagerotate($src, -90, 0);
  if($orient === 8) $src = imagerotate($src, 90, 0);
  $w0 = imagesx($src); $h0 = imagesy($src);
  $r = min(1, $max / max($w0, $h0)); $w = (int)round($w0 * $r); $h = (int)round($h0 * $r);
  $dst = imagecreatetruecolor($w, $h);
  if($type !== IMAGETYPE_JPEG){ imagealphablending($dst, false); imagesavealpha($dst, true); }
  imagecopyresampled($dst, $src, 0, 0, 0, 0, $w, $h, $w0, $h0);
  if($type === IMAGETYPE_JPEG) imagejpeg($dst, $file, 85);
  elseif($type === IMAGETYPE_PNG) imagepng($dst, $file, 6);
  elseif(function_exists('imagewebp')) imagewebp($dst, $file, 85);
  if(PHP_VERSION_ID < 80000){ imagedestroy($src); imagedestroy($dst); }   /* freed automatically since PHP 8 */
}

/* A $w-px copy of a product photo for grids and thumbnails, made once and kept in assets/products/thumbs.
   The name carries the source's size+date, so a replaced photo never shows a stale copy. Without GD, the full photo. */
function thumb($rel, $w=600){
  $src = FK_ROOT.'/'.$rel;
  if(!function_exists('imagecreatetruecolor') || !is_file($src)) return $rel;
  $tag = substr(md5(filesize($src).'-'.filemtime($src)), 0, 8);
  $t = 'assets/products/thumbs/'.pathinfo($rel, PATHINFO_FILENAME)."-$w-$tag.".pathinfo($rel, PATHINFO_EXTENSION);
  if(is_file(FK_ROOT.'/'.$t)) return $t;
  $dir = FK_ROOT.'/assets/products/thumbs';
  if(!is_dir($dir) && !@mkdir($dir, 0755, true)) return $rel;
  if(!@copy($src, FK_ROOT.'/'.$t)) return $rel;
  shrink_image(FK_ROOT.'/'.$t, $w);
  return $t;
}

/* <img> with a small copy for phones and grids, the full photo for large screens, and fixed
   dimensions so nothing jumps while loading. $lcp = the page's main image (load it first). */
function img_tag($rel, $alt, $sizes='(max-width:520px) 100vw, (max-width:1040px) 50vw, 300px', $lcp=false){
  $small = thumb($rel);
  $v = '?v='.@filemtime(FK_ROOT.'/'.$rel);
  $dim = @getimagesize(FK_ROOT.'/'.$small) ?: [600, 600];
  $srcset = $small !== $rel ? ' srcset="'.h($small).' '.$dim[0].'w, '.h($rel.$v).' '.(@getimagesize(FK_ROOT.'/'.$rel)[0] ?: 1600).'w" sizes="'.h($sizes).'"' : '';
  return '<img src="'.h($small === $rel ? $rel.$v : $small).'"'.$srcset.' width="'.(int)$dim[0].'" height="'.(int)$dim[1].'" alt="'.h($alt).'"'
       .($lcp ? ' fetchpriority="high">' : ' loading="lazy" decoding="async">');   /* the main image paints as soon as it arrives */
}

function slugify($s){
  $s = strtolower(strtr(trim((string)$s), ['é'=>'e','è'=>'e','ê'=>'e','ë'=>'e','É'=>'e','È'=>'e','á'=>'a','à'=>'a','â'=>'a','ä'=>'a','ó'=>'o','ô'=>'o','ö'=>'o','ú'=>'u','ù'=>'u','û'=>'u','ü'=>'u','í'=>'i','î'=>'i','ï'=>'i','ç'=>'c','ñ'=>'n','œ'=>'oe','’'=>'','\''=>'','—'=>'-','–'=>'-']));
  return trim(preg_replace('/[^a-z0-9]+/', '-', $s), '-');
}

/* ---------------- shipping ---------------- */
function ship_zone($store, $country){
  foreach($store['shipping']['zones'] as $z){
    if(in_array($country, $z['countries'], true)) return $z;
  }
  return ['name'=>t('Rest of world'), 'countries'=>[]] + $store['shipping']['rest'];
}

const SHIP_METHODS = ['standard', 'express'];

/* delivery options shown at checkout, with their names and delivery times */
function ship_methods($store){
  $m = $store['shipping']['methods'] ?? [];
  return ['standard'=>array_filter($m['standard'] ?? [], fn($v)=>$v !== '') + ['label'=>'Standard', 'days'=>'5–9'],
          'express' =>array_filter($m['express'] ?? [], fn($v)=>$v !== '')  + ['label'=>'Express',  'days'=>'2–4']];
}

/* a zone's rates per method (older data had one flat rate: that becomes Standard, Express is double) */
function zone_rates($z){
  $std = $z['standard'] ?? ['base'=>(float)($z['base'] ?? 0), 'per_kg'=>(float)($z['per_kg'] ?? 0)];
  $exp = $z['express'] ?? ['base'=>$std['base'] * 2, 'per_kg'=>$std['per_kg'] * 2];
  return ['standard'=>$std, 'express'=>$exp];
}

/* USD: the zone's per-order price + per-kg price × order weight, rounded up to a whole dollar.
   Pass the goods total to apply free shipping: Standard becomes free and Express costs only the difference. */
function shipping_usd($store, $country, $kg, $method='standard', $goods=null){
  $z = zone_rates(ship_zone($store, $country));
  $price = function($m) use($z, $kg, $store){
    $v = round((float)$z[$m]['base'] + (float)$z[$m]['per_kg'] * $kg, 2);
    return empty($store['shipping']['round_up']) ? $v : ceil($v);
  };
  $m = $method === 'express' ? 'express' : 'standard';
  if($goods !== null && free_shipping($store, $goods)) return $m === 'express' ? max(0, round($price('express') - $price('standard'), 2)) : 0;
  return $price($m);
}

/* free Standard shipping once the goods total reaches this (USD; 0 = off) */
function free_ship_usd($store){ return max(0, (float)($store['settings']['free_ship_usd'] ?? 0)); }
function free_shipping($store, $goods){ $t = free_ship_usd($store); return $t > 0 && round($goods, 2) >= $t; }

/* "3–6 working days" → [3, 6], for Google's delivery-time data */
function ship_day_range($days){ return preg_match('/(\d+)\D+(\d+)/', (string)$days, $m) ? [(int)$m[1], (int)$m[2]] : [3, 6]; }

/* ---------------- email ---------------- */
/* =?UTF-8?B?…?= so names like “Pokémon” and dashes survive in subjects and sender names */
function mail_header($s){ return preg_match('/[^\x20-\x7E]/', $s) ? '=?UTF-8?B?'.base64_encode($s).'?=' : $s; }

/* UTF-8 plain-text mail from the shop address. With an SMTP server set in Admin → Settings → Email
   (e.g. your mailbox provider), mail goes out through it and lands in inboxes more reliably;
   otherwise PHP's mail() is used, with the envelope sender (-f) set to the shop address so SPF
   checks line up (hosts that refuse -f get a second try without it). */
function shop_mail($to, $subject, $body, $reply_to=''){
  $cfg = $GLOBALS['STORE']['settings'];
  $from = filter_var($cfg['email'], FILTER_VALIDATE_EMAIL) ? $cfg['email'] : $cfg['order_email'];
  $headers = array_values(array_filter([
    'From: '.mail_header($cfg['brand']).' <'.$from.'>',
    $reply_to ? 'Reply-To: '.$reply_to : '',
    'MIME-Version: 1.0',
    'Content-Type: text/plain; charset=UTF-8',
    'Content-Transfer-Encoding: quoted-printable',
  ]));
  $subject = mail_header($subject);
  $body = quoted_printable_encode(str_replace("\n", "\r\n", str_replace("\r\n", "\n", $body)));   /* real line breaks, not =0A */
  $GLOBALS['FK_MAIL_ERROR'] = '';
  if(trim($cfg['smtp_host'] ?? '') !== '') return smtp_send($cfg, $from, $to, $subject, $body, $headers);
  $ok = @mail($to, $subject, $body, implode("\r\n", $headers), '-f'.$from) || @mail($to, $subject, $body, implode("\r\n", $headers));
  if(!$ok) $GLOBALS['FK_MAIL_ERROR'] = 'PHP mail() failed: the host may not allow sending mail from PHP. Set an SMTP server in Settings → Email.';
  return $ok;
}

/* a small SMTP client: SSL (port 465) or STARTTLS (587), AUTH PLAIN/LOGIN, certificate checked */
function smtp_send($cfg, $from, $to, $subject, $body, $headers){
  $host = trim($cfg['smtp_host']); $secure = $cfg['smtp_secure'] ?? 'tls';
  $port = (int)($cfg['smtp_port'] ?? 0) ?: ($secure === 'ssl' ? 465 : 587);
  $ctx = stream_context_create(['ssl'=>['verify_peer'=>true, 'verify_peer_name'=>true, 'peer_name'=>$host, 'SNI_enabled'=>true]]);
  $fp = @stream_socket_client(($secure === 'ssl' ? 'ssl://' : 'tcp://').$host.':'.$port, $errno, $errstr, 15, STREAM_CLIENT_CONNECT, $ctx);
  if(!$fp){ $GLOBALS['FK_MAIL_ERROR'] = "Couldn’t connect to $host:$port ($errstr)."; error_log('shop smtp: '.$GLOBALS['FK_MAIL_ERROR']); return false; }
  stream_set_timeout($fp, 20);
  $talk = function($line, $want) use($fp){
    if($line !== null) fwrite($fp, $line."\r\n");
    $reply = '';
    while(($l = fgets($fp, 2048)) !== false){ $reply .= $l; if(strlen($l) < 4 || $l[3] !== '-') break; }
    if(!in_array((int)substr($reply, 0, 3), (array)$want, true)) throw new RuntimeException(trim($reply) ?: 'no answer from the server');
    return $reply;
  };
  $me = preg_replace('/[^a-z0-9.-]/i', '', parse_url($cfg['domain'] ?? '', PHP_URL_HOST) ?: 'localhost');
  try {
    $talk(null, 220);
    $caps = $talk("EHLO $me", 250);
    if($secure === 'tls'){
      $talk('STARTTLS', 220);
      $method = STREAM_CRYPTO_METHOD_TLSv1_2_CLIENT | (defined('STREAM_CRYPTO_METHOD_TLSv1_3_CLIENT') ? STREAM_CRYPTO_METHOD_TLSv1_3_CLIENT : 0);
      if(!@stream_socket_enable_crypto($fp, true, $method)) throw new RuntimeException('STARTTLS failed (certificate or TLS version)');
      $caps = $talk("EHLO $me", 250);
    }
    if(($cfg['smtp_user'] ?? '') !== ''){
      if(preg_match('/AUTH[ =][^\r\n]*PLAIN/i', $caps)) $talk('AUTH PLAIN '.base64_encode("\0".$cfg['smtp_user']."\0".($cfg['smtp_pass'] ?? '')), 235);
      else { $talk('AUTH LOGIN', 334); $talk(base64_encode($cfg['smtp_user']), 334); $talk(base64_encode($cfg['smtp_pass'] ?? ''), 235); }
    }
    $talk("MAIL FROM:<$from>", 250);
    $talk("RCPT TO:<$to>", [250, 251]);
    $talk('DATA', 354);
    $msg = implode("\r\n", array_merge(['Date: '.date('r'), 'Message-ID: <'.bin2hex(random_bytes(12)).'@'.$me.'>', "To: <$to>", "Subject: $subject"], $headers))
         ."\r\n\r\n".preg_replace("/\r?\n/", "\r\n", $body);
    fwrite($fp, preg_replace('/^\./m', '..', $msg)."\r\n.\r\n");
    $talk(null, 250);
    try { $talk('QUIT', 221); } catch(RuntimeException $e){}
    fclose($fp);
    return true;
  } catch(RuntimeException $e){
    $GLOBALS['FK_MAIL_ERROR'] = "The mail server said: ".$e->getMessage();
    error_log('shop smtp: '.$GLOBALS['FK_MAIL_ERROR']);
    @fclose($fp);
    return false;
  }
}

/* ---------------- orders (data/orders/{ref}.php) ---------------- */
/* a random key made on first use, for links that only the customer should be able to open */
function site_secret(){
  $s = data_read('secret');
  if(empty($s['key'])){ $s = ['key'=>bin2hex(random_bytes(32))]; data_write('secret', $s); }
  return $s['key'];
}
/* the key in a customer's payment link, so order pages can't be opened by guessing references */
function order_key($ref){ return substr(hash_hmac('sha256', 'order|'.$ref, site_secret()), 0, 24); }
function order_key_ok($ref, $k){ return valid_ref($ref) && is_string($k) && hash_equals(order_key($ref), $k); }

function valid_ref($ref){ return is_string($ref) && preg_match('/^[FP]K-\d{2}-[A-F0-9]{5}$/', $ref); }   /* FK-…; PK-… from the POKEKURA version */

function new_order_ref(){
  do { $ref = 'FK-'.date('y').'-'.strtoupper(substr(bin2hex(random_bytes(3)), 0, 5)); }
  while(is_file(data_file('orders/'.$ref)));
  return $ref;
}

function order_save($order){ return valid_ref($order['ref'] ?? '') && data_write('orders/'.$order['ref'], $order); }
function order_load($ref){ return valid_ref($ref) ? data_read('orders/'.$ref) : null; }

function orders_all(){
  $out = [];
  foreach(array_merge(glob(data_dir().'/orders/FK-*.php') ?: [], glob(data_dir().'/orders/PK-*.php') ?: []) as $f){
    $o = order_load(basename($f, '.php'));
    if($o) $out[] = $o;
  }
  usort($out, fn($a, $b) => strcmp($b['time_iso'] ?? '', $a['time_iso'] ?? ''));
  return $out;
}
