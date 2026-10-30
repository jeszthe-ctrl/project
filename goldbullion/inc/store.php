<?php
/* Shared by index.php (the shop) and admin.php (the backend):
   data storage, sessions, live spot prices, email and orders. */
if(!defined('GB_ROOT')){ http_response_code(404); exit; }

/* Every data file starts with this line, so opening one in a browser shows nothing
   even on hosts that ignore data/.htaccess. The JSON follows on the next line. */
const GB_GUARD = "<?php http_response_code(404); exit; ?>\n";

const STOCK_STATUSES = ['in'=>'In stock', 'low'=>'Low stock', 'preorder'=>'Pre-order', 'out'=>'Sold out'];
const ORDER_STATUSES = ['new'=>'New', 'invoiced'=>'Payment details sent', 'paid'=>'Paid', 'shipped'=>'Shipped', 'completed'=>'Completed', 'cancelled'=>'Cancelled'];
const METALS         = ['gold'=>'Gold', 'silver'=>'Silver', 'platinum'=>'Platinum', 'palladium'=>'Palladium'];

/* weights */
const G_PER_OZT   = 31.1034768;     /* troy ounce */
const G_PER_LB    = 453.59237;      /* ordinary (avoirdupois) pound */
const G_PER_DWT   = 1.55517384;     /* pennyweight */
const G_PER_TOLA  = 11.6638038;
const UNITS       = ['g'=>'gram', 'oz'=>'troy ounce', 'kg'=>'kilo'];

function h($s){ return htmlspecialchars((string)$s, ENT_QUOTES|ENT_SUBSTITUTE, 'UTF-8'); }

function start_session(){
  if(session_status() === PHP_SESSION_ACTIVE) return;
  $https = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') || ($_SERVER['HTTP_X_FORWARDED_PROTO'] ?? '') === 'https';
  session_set_cookie_params(['lifetime'=>0, 'path'=>'/', 'secure'=>$https, 'httponly'=>true, 'samesite'=>'Lax']);
  /* some hosts' session folder isn't writable: keep sessions in data/ instead */
  $path = preg_replace('/^.*;/', '', (string)session_save_path()) ?: sys_get_temp_dir();
  if(!@is_dir($path) || !@is_writable($path)){
    $own = GB_ROOT.'/data/sessions';
    if(!is_dir($own)) @mkdir($own, 0700, true);
    if(is_writable($own)){ session_save_path($own); @ini_set('session.gc_probability', '1'); @ini_set('session.gc_divisor', '100'); }
  }
  @session_start();
}

/* ---------------- data files ---------------- */
function data_dir(){
  $d = GB_ROOT.'/data';
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
  if(@file_put_contents($tmp, GB_GUARD.$json, LOCK_EX) === false) return false;
  if(!@rename($tmp, $file)){ @unlink($tmp); return false; }
  return true;
}

/* ---------------- the store: settings, products, categories, pages, guides, price pages ---------------- */
function store_defaults(){ return require GB_ROOT.'/inc/defaults.php'; }

/* built-in content, keyed by slug, for "restore the original text" in the admin */
function default_item($section, $slug){
  foreach(store_defaults()[$section] ?? [] as $k=>$row){
    if((string)($row['slug'] ?? $k) === (string)$slug) return $row;
  }
  return null;
}

function store_load(){
  $d = store_defaults();
  $s = data_read('store');
  if(!$s){ $s = $d; data_write('store', $s); return $s; }   /* first run: start from the built-in content */
  foreach($d as $k=>$v) if(!isset($s[$k])) $s[$k] = $v;     /* sections added by later versions */
  $s['settings'] += $d['settings'];
  /* built-in pages, guides and price pages added by a later version (never replaces your edits) */
  foreach(['pages', 'guides', 'tools', 'categories'] as $sec){
    $have = array_column($s[$sec], 'slug');
    foreach($d[$sec] as $row) if(!in_array($row['slug'], $have, true) && !in_array($row['slug'], $s['settings']['deleted_'.$sec] ?? [], true)) $s[$sec][] = $row;
  }
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

function slugify($s){
  $s = strtolower(strtr(trim((string)$s), ['é'=>'e','è'=>'e','ê'=>'e','á'=>'a','à'=>'a','ä'=>'a','ó'=>'o','ö'=>'o','ú'=>'u','ü'=>'u','í'=>'i','ñ'=>'n','—'=>'-','–'=>'-','½'=>'1-2','¼'=>'1-4','/'=>'-']));
  return trim(preg_replace('/[^a-z0-9]+/', '-', $s), '-');
}

/* ---------------- fetching from the web (spot prices, product photos on CSV import) ---------------- */
function http_get($url, $timeout=5){
  if(function_exists('curl_init')){
    $ch = curl_init($url);
    curl_setopt_array($ch, [CURLOPT_RETURNTRANSFER=>true, CURLOPT_FOLLOWLOCATION=>true, CURLOPT_MAXREDIRS=>3, CURLOPT_CONNECTTIMEOUT=>$timeout,
      CURLOPT_TIMEOUT=>$timeout + 5, CURLOPT_USERAGENT=>'Mozilla/5.0 (compatible; GoldBullionShop/1.0)', CURLOPT_HTTPHEADER=>['Accept: */*']]);
    $body = curl_exec($ch);
    $code = (int)curl_getinfo($ch, CURLINFO_HTTP_CODE);
    if(PHP_VERSION_ID < 80000) curl_close($ch);
    return ($body !== false && $code >= 200 && $code < 300) ? $body : null;
  }
  if(!ini_get('allow_url_fopen')) return null;
  $ctx = stream_context_create(['http'=>['timeout'=>$timeout + 5, 'header'=>"User-Agent: Mozilla/5.0 (compatible; GoldBullionShop/1.0)\r\n"]]);
  $body = @file_get_contents($url, false, $ctx);
  return $body === false ? null : $body;
}

/* ---------------- live spot prices ----------------
   Gold and silver in US dollars per troy ounce, fetched from public price feeds at most every
   10 minutes and kept in data/spot.php. If every feed fails, the last good price is used; with no
   price at all, the manual prices from Admin → Spot price. You can also switch to manual there. */
function spot_fetch(){
  $out = ['gold'=>0, 'silver'=>0, 'gold_change'=>null, 'gold_change_pct'=>null, 'silver_change'=>null, 'silver_change_pct'=>null];
  /* 1. goldprice.org (also has today's change) */
  $j = json_decode((string)http_get('https://data-asg.goldprice.org/dbXRates/USD'), true);
  $i = $j['items'][0] ?? null;
  if($i && (float)($i['xauPrice'] ?? 0) > 100){
    return ['gold'=>(float)$i['xauPrice'], 'silver'=>(float)($i['xagPrice'] ?? 0),
            'gold_change'=>isset($i['chgXau']) ? (float)$i['chgXau'] : null, 'gold_change_pct'=>isset($i['pcXau']) ? (float)$i['pcXau'] : null,
            'silver_change'=>isset($i['chgXag']) ? (float)$i['chgXag'] : null, 'silver_change_pct'=>isset($i['pcXag']) ? (float)$i['pcXag'] : null,
            'source'=>'goldprice.org'];
  }
  /* 2. gold-api.com */
  foreach(['gold'=>'XAU', 'silver'=>'XAG'] as $m=>$sym){
    $j = json_decode((string)http_get('https://api.gold-api.com/price/'.$sym), true);
    if((float)($j['price'] ?? 0) > 0) $out[$m] = (float)$j['price'];
  }
  if($out['gold'] > 100) return $out + ['source'=>'gold-api.com'];
  return null;
}

/* ['gold'=>…, 'silver'=>…, 'time'=>unix, 'source'=>…, 'live'=>bool] — gold 0 means no price is known */
function spot(){
  static $cache = null;
  if($cache !== null) return $cache;
  $cfg = $GLOBALS['STORE']['settings'] ?? [];
  $manual = ['gold'=>(float)($cfg['manual_gold'] ?? 0), 'silver'=>(float)($cfg['manual_silver'] ?? 0), 'time'=>(int)($cfg['manual_time'] ?? time()),
             'source'=>'manual', 'live'=>false, 'gold_change'=>null, 'gold_change_pct'=>null, 'silver_change'=>null, 'silver_change_pct'=>null];
  if(($cfg['spot_mode'] ?? 'auto') === 'manual') return $cache = $manual;
  $saved = data_read('spot') ?: [];
  $age = time() - (int)($saved['checked'] ?? 0);
  if($age > 600){
    $saved['checked'] = time();                     /* one try per 10 minutes, even when the feeds are down */
    data_write('spot', $saved);
    $fresh = spot_fetch();
    if($fresh){ $saved = $fresh + ['time'=>time(), 'checked'=>time()]; data_write('spot', $saved); }
  }
  if((float)($saved['gold'] ?? 0) > 0){
    $silver = (float)($saved['silver'] ?? 0) ?: $manual['silver'];
    return $cache = ['gold'=>(float)$saved['gold'], 'silver'=>$silver, 'time'=>(int)($saved['time'] ?? time()), 'source'=>$saved['source'] ?? '',
                     'live'=>time() - (int)($saved['time'] ?? 0) < 3600] + $saved;
  }
  return $cache = $manual;
}

function oz_from($weight, $unit){
  $f = ['g'=>1, 'oz'=>G_PER_OZT, 'kg'=>1000][(string)$unit] ?? G_PER_OZT;
  return (float)$weight * $f / G_PER_OZT;
}

/* "1 oz", "10 g", "1 kg", "½ oz" */
function weight_label($weight, $unit){
  $w = (float)$weight;
  if($w <= 0) return '';
  $fr = ['0.5'=>'1/2', '0.25'=>'1/4', '0.1'=>'1/10', '0.05'=>'1/20'];
  $n = $unit === 'oz' && isset($fr[(string)$w]) ? $fr[(string)$w] : rtrim(rtrim(number_format($w, 3, '.', ''), '0'), '.');
  return $n.' '.(['g'=>'g', 'oz'=>'oz', 'kg'=>'kg'][(string)$unit] ?? $unit);
}

/* a product's price in USD: a fixed price, or the live spot value of its metal + your premium */
function product_price($p){
  if(($p['pricing'] ?? 'fixed') === 'spot'){
    $spot = spot();
    $per_oz = (float)($spot[$p['metal'] ?? 'gold'] ?? 0);
    if($per_oz <= 0) return 0;
    $metal = $per_oz * oz_from($p['weight'] ?? 0, $p['unit'] ?? 'oz');
    return round($metal * (1 + (float)($p['premium_pct'] ?? 0) / 100) + (float)($p['premium_usd'] ?? 0), 2);
  }
  return round((float)($p['price'] ?? 0), 2);
}

/* ---------------- shipping ---------------- */
function ship_methods($store){
  $out = [];
  foreach($store['shipping']['methods'] ?? [] as $k=>$m) if(!empty($m['enabled'])) $out[$k] = $m;
  return $out;
}

function shipping_cost($store, $method, $goods){
  $m = ship_methods($store)[$method] ?? null;
  if(!$m) return null;
  $free = (float)($store['shipping']['free_over'] ?? 0);
  if($free > 0 && $goods >= $free && !empty($m['free_eligible'])) return 0.0;
  return round((float)$m['price'], 2);
}

/* "1–3 business days" → [1, 3], for Google's delivery-time data */
function day_range($days){ return preg_match('/(\d+)\D+(\d+)/', (string)$days, $m) ? [(int)$m[1], (int)$m[2]] : (preg_match('/(\d+)/', (string)$days, $m) ? [(int)$m[1], (int)$m[1]] : [3, 5]); }

/* ---------------- email ---------------- */
function mail_header($s){ return preg_match('/[^\x20-\x7E]/', $s) ? '=?UTF-8?B?'.base64_encode($s).'?=' : $s; }

/* UTF-8 plain-text mail from the shop address. With an SMTP server set in Admin → Settings → Email,
   mail goes out through your mailbox and lands in inboxes more reliably; otherwise PHP's mail(). */
function shop_mail($to, $subject, $body, $reply_to=''){
  $cfg = $GLOBALS['STORE']['settings'];
  $from = filter_var($cfg['from_email'] ?? '', FILTER_VALIDATE_EMAIL) ? $cfg['from_email'] : $cfg['email'];
  $headers = array_values(array_filter([
    'From: '.mail_header($cfg['brand']).' <'.$from.'>',
    $reply_to ? 'Reply-To: '.$reply_to : '',
    'MIME-Version: 1.0',
    'Content-Type: text/plain; charset=UTF-8',
    'Content-Transfer-Encoding: quoted-printable',
  ]));
  $subject = mail_header($subject);
  $body = quoted_printable_encode(str_replace("\n", "\r\n", str_replace("\r\n", "\n", $body)));
  $GLOBALS['GB_MAIL_ERROR'] = '';
  if(trim($cfg['smtp_host'] ?? '') !== '') return smtp_send($cfg, $from, $to, $subject, $body, $headers);
  $ok = @mail($to, $subject, $body, implode("\r\n", $headers), '-f'.$from) || @mail($to, $subject, $body, implode("\r\n", $headers));
  if(!$ok) $GLOBALS['GB_MAIL_ERROR'] = 'PHP mail() failed: your host may not allow sending mail from PHP. Enter your mailbox SMTP details in Settings → Email.';
  return $ok;
}

/* a small SMTP client: SSL (port 465) or STARTTLS (587), AUTH PLAIN/LOGIN, certificate checked */
function smtp_send($cfg, $from, $to, $subject, $body, $headers){
  $host = trim($cfg['smtp_host']); $secure = $cfg['smtp_secure'] ?? 'ssl';
  $port = (int)($cfg['smtp_port'] ?? 0) ?: ($secure === 'ssl' ? 465 : 587);
  $ctx = stream_context_create(['ssl'=>['verify_peer'=>true, 'verify_peer_name'=>true, 'peer_name'=>$host, 'SNI_enabled'=>true]]);
  $fp = @stream_socket_client(($secure === 'ssl' ? 'ssl://' : 'tcp://').$host.':'.$port, $errno, $errstr, 15, STREAM_CLIENT_CONNECT, $ctx);
  if(!$fp){ $GLOBALS['GB_MAIL_ERROR'] = "Couldn’t connect to $host:$port ($errstr)."; error_log('shop smtp: '.$GLOBALS['GB_MAIL_ERROR']); return false; }
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
    $GLOBALS['GB_MAIL_ERROR'] = 'The mail server said: '.$e->getMessage();
    error_log('shop smtp: '.$GLOBALS['GB_MAIL_ERROR']);
    @fclose($fp);
    return false;
  }
}

/* {name}, {order}, {total}, {method}… in the payment-details and status emails */
function fill_template($text, $order){
  $cfg = $GLOBALS['STORE']['settings'];
  return strtr((string)$text, [
    '{name}'=>$order['first_name'] ?? '', '{order}'=>$order['ref'] ?? '', '{total}'=>money($order['total'] ?? 0),
    '{method}'=>$order['payment_label'] ?? '', '{shop}'=>$cfg['brand'], '{email}'=>$cfg['email'], '{phone}'=>$cfg['phone'],
    '{tracking}'=>$order['tracking'] ?? '', '{pay_window}'=>$cfg['pay_window'] ?? '24', '{carrier}'=>$order['carrier'] ?? '', '{details}'=>$order['payment_details'] ?? '',
  ]);
}

function money($v){ return '$'.number_format((float)$v, 2); }

/* ---------------- orders (data/orders/{ref}.php) ---------------- */
function site_secret(){
  $s = data_read('secret');
  if(empty($s['key'])){ $s = ['key'=>bin2hex(random_bytes(32))]; data_write('secret', $s); }
  return $s['key'];
}
/* the key in a customer's order link, so order pages can't be opened by guessing references */
function order_key($ref){ return substr(hash_hmac('sha256', 'order|'.$ref, site_secret()), 0, 24); }
function order_key_ok($ref, $k){ return valid_ref($ref) && is_string($k) && hash_equals(order_key($ref), $k); }

function valid_ref($ref){ return is_string($ref) && preg_match('/^GB-\d{4}-[A-F0-9]{5}$/', $ref); }

function new_order_ref(){
  do { $ref = 'GB-'.date('ym').'-'.strtoupper(substr(bin2hex(random_bytes(3)), 0, 5)); }
  while(is_file(data_file('orders/'.$ref)));
  return $ref;
}

function order_save($order){ return valid_ref($order['ref'] ?? '') && data_write('orders/'.$order['ref'], $order); }
function order_load($ref){ return valid_ref($ref) ? data_read('orders/'.$ref) : null; }

function orders_all(){
  $out = [];
  foreach(glob(data_dir().'/orders/GB-*.php') ?: [] as $f){
    $o = order_load(basename($f, '.php'));
    if($o) $out[] = $o;
  }
  usort($out, fn($a, $b) => strcmp($b['time_iso'] ?? '', $a['time_iso'] ?? ''));
  return $out;
}

/* ---------------- product photos ----------------
   A product's photos are local files (assets/products/…) or full https:// addresses. */
function shrink_image($file, $max=1400){
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
}

/* save an uploaded or downloaded image as assets/products/{name}.{ext}; returns the path or '' */
function store_image($bytes, $name){
  if(!is_string($bytes) || strlen($bytes) < 100 || strlen($bytes) > 15 * 1048576) return '';
  $info = @getimagesizefromstring($bytes);
  $ext = [IMAGETYPE_JPEG=>'jpg', IMAGETYPE_PNG=>'png', IMAGETYPE_WEBP=>'webp', IMAGETYPE_GIF=>'gif'][$info[2] ?? 0] ?? '';
  if(!$ext) return '';
  $dir = GB_ROOT.'/assets/products';
  if(!is_dir($dir)) @mkdir($dir, 0755, true);
  $base = slugify($name) ?: 'photo';
  $rel = "assets/products/$base.$ext"; $n = 2;
  while(is_file(GB_ROOT.'/'.$rel)) $rel = "assets/products/$base-".$n++.".$ext";
  if(@file_put_contents(GB_ROOT.'/'.$rel, $bytes) === false) return '';
  if($ext !== 'gif') shrink_image(GB_ROOT.'/'.$rel);
  return $rel;
}
