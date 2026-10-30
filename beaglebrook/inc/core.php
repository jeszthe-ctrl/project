<?php
/* Shared by index.php (the website) and admin.php (the backend):
   data storage, sessions, email, text messages, photos and the text formatter. */
if(!defined('BB_ROOT')){ http_response_code(404); exit; }

/* Every data file starts with this line, so opening one in a browser shows nothing
   even on hosts that ignore data/.htaccess. The JSON follows on the next line. */
const BB_GUARD = "<?php http_response_code(404); exit; ?>\n";

const PUPPY_STATUSES   = ['available'=>'Available', 'reserved'=>'Reserved', 'sold'=>'Sold'];
const INQUIRY_STATUSES = ['new'=>'New', 'contacted'=>'Contacted', 'deposit'=>'Deposit received', 'delivered'=>'Delivered', 'closed'=>'Closed'];
const LITTER_STATUSES  = ['planned'=>'Planned', 'expected'=>'Expected', 'born'=>'Born'];
const IMG_EXTS = ['jpg', 'jpeg', 'png', 'webp'];
const US_STATES = ['AL'=>'Alabama','AK'=>'Alaska','AZ'=>'Arizona','AR'=>'Arkansas','CA'=>'California','CO'=>'Colorado','CT'=>'Connecticut',
  'DE'=>'Delaware','DC'=>'District of Columbia','FL'=>'Florida','GA'=>'Georgia','HI'=>'Hawaii','ID'=>'Idaho','IL'=>'Illinois','IN'=>'Indiana',
  'IA'=>'Iowa','KS'=>'Kansas','KY'=>'Kentucky','LA'=>'Louisiana','ME'=>'Maine','MD'=>'Maryland','MA'=>'Massachusetts','MI'=>'Michigan',
  'MN'=>'Minnesota','MS'=>'Mississippi','MO'=>'Missouri','MT'=>'Montana','NE'=>'Nebraska','NV'=>'Nevada','NH'=>'New Hampshire',
  'NJ'=>'New Jersey','NM'=>'New Mexico','NY'=>'New York','NC'=>'North Carolina','ND'=>'North Dakota','OH'=>'Ohio','OK'=>'Oklahoma',
  'OR'=>'Oregon','PA'=>'Pennsylvania','RI'=>'Rhode Island','SC'=>'South Carolina','SD'=>'South Dakota','TN'=>'Tennessee','TX'=>'Texas',
  'UT'=>'Utah','VT'=>'Vermont','VA'=>'Virginia','WA'=>'Washington','WV'=>'West Virginia','WI'=>'Wisconsin','WY'=>'Wyoming'];

function h($s){ return htmlspecialchars((string)$s, ENT_QUOTES|ENT_SUBSTITUTE, 'UTF-8'); }

function start_session(){
  if(session_status() === PHP_SESSION_ACTIVE) return;
  $https = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') || ($_SERVER['HTTP_X_FORWARDED_PROTO'] ?? '') === 'https';
  session_set_cookie_params(['lifetime'=>0, 'path'=>'/', 'secure'=>$https, 'httponly'=>true, 'samesite'=>'Lax']);
  /* some hosts' session folder isn't writable: keep sessions in data/ instead */
  $path = preg_replace('/^.*;/', '', (string)session_save_path()) ?: sys_get_temp_dir();
  if(!@is_dir($path) || !@is_writable($path)){
    $own = BB_ROOT.'/data/sessions';
    if(!is_dir($own)) @mkdir($own, 0700, true);
    if(is_writable($own)){ session_save_path($own); @ini_set('session.gc_probability', '1'); @ini_set('session.gc_divisor', '100'); }
  }
  @session_start();
}

/* ---------------- data files ---------------- */
function data_dir(){
  $d = BB_ROOT.'/data';
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
  if(@file_put_contents($tmp, BB_GUARD.$json, LOCK_EX) === false) return false;
  if(!@rename($tmp, $file)){ @unlink($tmp); return false; }
  return true;
}

/* ---------------- the site's content (puppies, parents, pages, guides, settings) ---------------- */
function site_defaults(){ return require BB_ROOT.'/inc/defaults.php'; }

function site_load(){
  $d = site_defaults();
  $s = data_read('site');
  if(!$s){ $s = $d; data_write('site', $s); }      // first run: start from the defaults
  $s += $d;                                         // sections added by later versions
  $s['settings'] += $d['settings'];
  /* guides, blog posts and pages added by a later version appear without touching edited ones */
  foreach(['articles', 'pages'] as $k){
    $have = array_column($s[$k], 'slug');
    foreach($d[$k] as $item) if(!in_array($item['slug'], $have, true) && !in_array($item['slug'], $s['removed'][$k] ?? [], true)) $s[$k][] = $item;
  }
  return $s;
}

/* keeps the last 30 versions in data/backups/ */
function site_save($s){
  $cur = data_file('site');
  if(is_file($cur)){
    $dir = data_dir().'/backups';
    if(!is_dir($dir)) @mkdir($dir, 0755, true);
    @copy($cur, $dir.'/site-'.date('Ymd-His').'-'.bin2hex(random_bytes(2)).'.php');
    $old = glob($dir.'/site-*.php') ?: [];
    sort($old);
    foreach(array_slice($old, 0, max(0, count($old) - 30)) as $f) @unlink($f);
  }
  return data_write('site', $s);
}

function find_by($list, $key, $value){ foreach($list as $item){ if((string)($item[$key] ?? '') === (string)$value) return $item; } return null; }

function slugify($s){
  $s = strtolower(strtr(trim((string)$s), ['é'=>'e','è'=>'e','ê'=>'e','á'=>'a','à'=>'a','ä'=>'a','ó'=>'o','ö'=>'o','ú'=>'u','ü'=>'u','í'=>'i','ñ'=>'n','—'=>'-','–'=>'-','&'=>' and ']));
  return trim(preg_replace('/[^a-z0-9]+/', '-', $s), '-');
}

function new_id(){ return bin2hex(random_bytes(5)); }

/* "2026-09-14" → "September 14, 2026" */
function nice_date($ymd){ $t = strtotime((string)$ymd); return $t ? date('F j, Y', $t) : ''; }

/* a puppy's age from its date of birth: "9 weeks", "4 months" */
function age_text($dob){
  $t = strtotime((string)$dob);
  if(!$t || $t > time()) return '';
  $days = (int)floor((time() - $t) / 86400);
  if($days < 14) return $days === 1 ? '1 day' : $days.' days';
  if($days < 120) return floor($days / 7).' weeks';
  return floor($days / 30.44).' months';
}

function money($v){ $v = (float)$v; return '$'.number_format($v, fmod($v, 1) == 0 ? 0 : 2); }

/* ---------------- delivery ----------------
   Each option (Standard, Express) has a label and a delivery time. Prices are set per zone of states;
   a zone whose states are "*" covers every state not listed in an earlier zone. */
function delivery_options(){ return array_values(array_filter($GLOBALS['SITE']['delivery']['methods'], fn($m)=>!empty($m['on']))); }
function delivery_zone($state){
  foreach($GLOBALS['SITE']['delivery']['zones'] as $z){
    $states = array_map('trim', explode(',', strtoupper((string)$z['states'])));
    if(in_array('*', $states, true) || in_array(strtoupper((string)$state), $states, true)) return $z;
  }
  return null;
}
/* the price of an option to a state, or null when no price is set for it */
function delivery_fee($state, $key){
  $z = delivery_zone($state);
  return $z !== null && is_numeric($z[$key] ?? '') ? (float)$z[$key] : null;
}
/* the price most buyers pay (the "*" zone, else the first zone), for sentences like "Standard delivery, $55" */
function delivery_base_fee($key){
  $zones = $GLOBALS['SITE']['delivery']['zones'];
  foreach($zones as $z) if(trim((string)$z['states']) === '*') return is_numeric($z[$key] ?? '') ? (float)$z[$key] : null;
  return isset($zones[0]) && is_numeric($zones[0][$key] ?? '') ? (float)$zones[0][$key] : null;
}
/* "Standard delivery (2–5 days from departure, $55) or Express delivery (within 24 hours of departure, $100)" */
function delivery_summary(){
  $varies = count($GLOBALS['SITE']['delivery']['zones']) > 1;
  $parts = array_map(function($m) use($varies){
    $fee = delivery_base_fee($m['key']);
    return $m['label'].' ('.$m['days'].($fee !== null ? ', '.($varies ? 'from ' : '').money($fee) : '').')';
  }, delivery_options());
  return $parts ? implode(' or ', $parts) : 'delivery to your door';
}

/* ---------------- secrets (links only the right person can open) ---------------- */
function site_secret(){
  $s = data_read('secret');
  if(empty($s['key'])){ $s = ['key'=>bin2hex(random_bytes(32))]; data_write('secret', $s); }
  return $s['key'];
}
function sign($what){ return substr(hash_hmac('sha256', $what, site_secret()), 0, 24); }
function sign_ok($what, $k){ return is_string($k) && hash_equals(sign($what), $k); }

/* ---------------- photos (assets/uploads/) ---------------- */
function upload_dir(){
  $d = BB_ROOT.'/assets/uploads';
  if(!is_dir($d)) @mkdir($d, 0755, true);
  return $d;
}

/* Saves one uploaded photo ($_FILES entry) as assets/uploads/{prefix}-{random}.{ext}.
   Returns the file's path from the site root, or an error message starting with "!". */
function save_upload($f, $prefix){
  if(!is_array($f) || ($f['error'] ?? UPLOAD_ERR_NO_FILE) === UPLOAD_ERR_NO_FILE) return '';
  if($f['error'] !== UPLOAD_ERR_OK) return '!'.($f['error'] === UPLOAD_ERR_INI_SIZE || $f['error'] === UPLOAD_ERR_FORM_SIZE
    ? 'The photo is larger than the host allows ('.ini_get('upload_max_filesize').').' : 'The photo didn’t upload completely. Try again.');
  $info = @getimagesize($f['tmp_name']);
  $ext = [IMAGETYPE_JPEG=>'jpg', IMAGETYPE_PNG=>'png', IMAGETYPE_WEBP=>'webp'][$info[2] ?? 0] ?? '';
  if(!$ext) return '!Photos must be JPG, PNG or WebP.';
  $rel = 'assets/uploads/'.slugify($prefix).'-'.bin2hex(random_bytes(3)).'.'.$ext;
  upload_dir();
  if(!@move_uploaded_file($f['tmp_name'], BB_ROOT.'/'.$rel)) return '!Couldn’t save the photo: the assets/uploads folder isn’t writable.';
  shrink_image(BB_ROOT.'/'.$rel, 1600, true);
  return $rel;
}

/* every file of a multi-file input as single $_FILES entries */
function upload_list($field){
  $f = $_FILES[$field] ?? null;
  if(!$f || !is_array($f['name'])) return $f ? [$f] : [];
  $out = [];
  foreach(array_keys($f['name']) as $i) $out[] = ['name'=>$f['name'][$i], 'tmp_name'=>$f['tmp_name'][$i], 'error'=>$f['error'][$i], 'size'=>$f['size'][$i]];
  return $out;
}

function delete_upload($rel){
  if(!is_string($rel) || !preg_match('#^assets/uploads/[a-z0-9-]+\.(jpg|png|webp)$#', $rel)) return;
  @unlink(BB_ROOT.'/'.$rel);
  foreach(glob(BB_ROOT.'/assets/uploads/thumbs/'.pathinfo($rel, PATHINFO_FILENAME).'-*') ?: [] as $t) @unlink($t);
}

/* Shrinks photos over $max px (phone photos are often 4000px and several MB) and turns
   sideways phone JPEGs upright. With $clean, every photo is saved afresh, which drops its
   metadata: phone photos carry the GPS position they were taken at, i.e. the breeder's home.
   Needs GD; skipped quietly without it. */
function shrink_image($file, $max=1600, $clean=false){
  $i = @getimagesize($file);
  if(!$i || !function_exists('imagecreatetruecolor')) return;
  $type = $i[2];
  $exif = ($type === IMAGETYPE_JPEG && function_exists('exif_read_data')) ? @exif_read_data($file) : [];
  $orient = (int)(is_array($exif) ? ($exif['Orientation'] ?? 1) : 1);
  if(!$clean && max($i[0], $i[1]) <= $max && !in_array($orient, [3,6,8], true)) return;
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
  if($type === IMAGETYPE_JPEG) imagejpeg($dst, $file, 84);
  elseif($type === IMAGETYPE_PNG) imagepng($dst, $file, 6);
  elseif(function_exists('imagewebp')) imagewebp($dst, $file, 84);
}

/* a $w-px copy of a photo for grids, made once and kept in assets/uploads/thumbs */
function thumb($rel, $w=640){
  $src = BB_ROOT.'/'.$rel;
  if(!function_exists('imagecreatetruecolor') || !is_file($src)) return $rel;
  $tag = substr(md5(filesize($src).'-'.filemtime($src)), 0, 8);
  $t = 'assets/uploads/thumbs/'.pathinfo($rel, PATHINFO_FILENAME)."-$w-$tag.".pathinfo($rel, PATHINFO_EXTENSION);
  if(is_file(BB_ROOT.'/'.$t)) return $t;
  $dir = BB_ROOT.'/assets/uploads/thumbs';
  if(!is_dir($dir) && !@mkdir($dir, 0755, true)) return $rel;
  if(!@copy($src, BB_ROOT.'/'.$t)) return $rel;
  shrink_image(BB_ROOT.'/'.$t, $w);
  return $t;
}

/* <img> with a small copy for phones and grids and fixed dimensions so nothing jumps while loading.
   $lcp = the page's main image (load it first). */
function img_tag($rel, $alt, $sizes='(max-width:600px) 100vw, (max-width:1100px) 50vw, 360px', $lcp=false, $class=''){
  if(!is_file(BB_ROOT.'/'.$rel)) return '';
  $small = thumb($rel);
  $full = @getimagesize(BB_ROOT.'/'.$rel) ?: [1200, 900];
  $dim = @getimagesize(BB_ROOT.'/'.$small) ?: $full;
  $v = '?v='.@filemtime(BB_ROOT.'/'.$rel);
  $srcset = $small !== $rel ? ' srcset="'.h($small).' '.$dim[0].'w, '.h($rel.$v).' '.$full[0].'w" sizes="'.h($sizes).'"' : '';
  return '<img'.($class ? ' class="'.h($class).'"' : '').' src="'.h($small === $rel ? $rel.$v : $small).'"'.$srcset.' width="'.(int)$dim[0].'" height="'.(int)$dim[1].'" alt="'.h($alt).'"'
       .($lcp ? ' fetchpriority="high">' : ' loading="lazy" decoding="async">');
}

/* ---------------- email ---------------- */
function mail_header($s){ return preg_match('/[^\x20-\x7E]/', $s) ? '=?UTF-8?B?'.base64_encode($s).'?=' : $s; }

/* UTF-8 plain-text mail from the site address. With an SMTP server set in Admin → Settings → Email
   (your mailbox provider), mail goes out through it and lands in inboxes more reliably;
   otherwise PHP's mail() is used, with the envelope sender set to the site address so SPF lines up. */
function site_mail($to, $subject, $body, $reply_to='', $extra_headers=[]){
  $cfg = $GLOBALS['SITE']['settings'];
  $from = $cfg['email'];
  $headers = array_values(array_filter(array_merge([
    'From: '.mail_header($cfg['brand']).' <'.$from.'>',
    $reply_to && filter_var($reply_to, FILTER_VALIDATE_EMAIL) ? 'Reply-To: '.$reply_to : '',
    'MIME-Version: 1.0',
    'Content-Type: text/plain; charset=UTF-8',
    'Content-Transfer-Encoding: quoted-printable',
  ], $extra_headers)));
  $subject = mail_header($subject);
  $body = quoted_printable_encode(str_replace("\n", "\r\n", str_replace("\r\n", "\n", $body)));
  $GLOBALS['BB_MAIL_ERROR'] = '';
  if(trim($cfg['smtp_host'] ?? '') !== '') return smtp_send($cfg, $from, $to, $subject, $body, $headers);
  $ok = @mail($to, $subject, $body, implode("\r\n", $headers), '-f'.$from) || @mail($to, $subject, $body, implode("\r\n", $headers));
  if(!$ok) $GLOBALS['BB_MAIL_ERROR'] = 'PHP mail() failed: the host may not allow sending mail from PHP. Set an SMTP server in Settings → Email.';
  return $ok;
}

/* a small SMTP client: SSL (port 465) or STARTTLS (587), AUTH PLAIN/LOGIN, certificate checked */
function smtp_send($cfg, $from, $to, $subject, $body, $headers){
  $host = trim($cfg['smtp_host']); $secure = $cfg['smtp_secure'] ?? 'tls';
  $port = (int)($cfg['smtp_port'] ?? 0) ?: ($secure === 'ssl' ? 465 : 587);
  $ctx = stream_context_create(['ssl'=>['verify_peer'=>true, 'verify_peer_name'=>true, 'peer_name'=>$host, 'SNI_enabled'=>true]]);
  $fp = @stream_socket_client(($secure === 'ssl' ? 'ssl://' : 'tcp://').$host.':'.$port, $errno, $errstr, 15, STREAM_CLIENT_CONNECT, $ctx);
  if(!$fp){ $GLOBALS['BB_MAIL_ERROR'] = "Couldn’t connect to $host:$port ($errstr)."; error_log('site smtp: '.$GLOBALS['BB_MAIL_ERROR']); return false; }
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
    $GLOBALS['BB_MAIL_ERROR'] = 'The mail server said: '.$e->getMessage();
    error_log('site smtp: '.$GLOBALS['BB_MAIL_ERROR']);
    @fclose($fp);
    return false;
  }
}

/* ---------------- text messages to the breeder ----------------
   Twilio (Admin → Settings → Text alerts) if set up; otherwise an email-to-text address
   from the phone carrier (e.g. 5135550123@vtext.com), which costs nothing. */
function send_alert_text($msg){
  $cfg = $GLOBALS['SITE']['settings'];
  $msg = mb_substr($msg, 0, 300);
  if(($cfg['twilio_sid'] ?? '') !== '' && ($cfg['twilio_token'] ?? '') !== '' && ($cfg['twilio_from'] ?? '') !== '' && ($cfg['alert_phone'] ?? '') !== ''){
    if(!function_exists('curl_init')) return false;
    $ch = curl_init('https://api.twilio.com/2010-04-01/Accounts/'.rawurlencode($cfg['twilio_sid']).'/Messages.json');
    curl_setopt_array($ch, [CURLOPT_POST=>true, CURLOPT_RETURNTRANSFER=>true, CURLOPT_TIMEOUT=>10,
      CURLOPT_USERPWD=>$cfg['twilio_sid'].':'.$cfg['twilio_token'],
      CURLOPT_POSTFIELDS=>http_build_query(['From'=>$cfg['twilio_from'], 'To'=>$cfg['alert_phone'], 'Body'=>$msg])]);
    $res = curl_exec($ch);
    $code = (int)curl_getinfo($ch, CURLINFO_HTTP_CODE);
    if($code < 200 || $code >= 300) error_log('site twilio: '.$code.' '.substr((string)$res, 0, 300));
    return $code >= 200 && $code < 300;
  }
  if(filter_var($cfg['alert_sms_email'] ?? '', FILTER_VALIDATE_EMAIL)) return site_mail($cfg['alert_sms_email'], 'New inquiry', $msg);
  return false;
}

/* ---------------- text the admin writes ----------------
   Blank line = paragraph · "## " heading, "### " subheading · "- " bullet, "1. " numbered ·
   "> " callout · **bold**, *italic* · [text](link) · "| a | b |" tables · {placeholders} from fill() */
function fill($text){
  $c = $GLOBALS['SITE']['settings'];
  $pay = array_column(array_filter($GLOBALS['SITE']['payments'], fn($p)=>!empty($p['on'])), 'name');
  $map = [
    '{brand}'=>$c['brand'], '{email}'=>$c['email'], '{phone}'=>$c['phone'], '{city}'=>$c['city'], '{state}'=>$c['state_name'],
    '{deposit}'=>(int)$c['deposit_pct'].'%', '{balance}'=>(100 - (int)$c['deposit_pct']).'%',
    '{vet_check_hours}'=>(int)$c['vet_check_hours'].' hours', '{genetic_years}'=>genetic_text(),
    '{vet_check_hour}'=>(int)$c['vet_check_hours'].'-hour', '{genetic_year}'=>(int)$c['genetic_years'].'-year',
    '{go_home_weeks}'=>(int)$c['go_home_weeks'].' weeks', '{reply_time}'=>$c['reply_time'],
    '{payment_methods}'=>$pay ? implode(', ', array_slice($pay, 0, -1)).(count($pay) > 1 ? ' or ' : '').end($pay) : 'the methods listed on our site',
    '{delivery_options}'=>delivery_summary(),
    '{registry}'=>$c['registry'] !== '' ? $c['registry'] : 'the breed registry',
    '{year}'=>date('Y'),
  ];
  return strtr((string)$text, $map);
}
function genetic_text(){ $y = (int)$GLOBALS['SITE']['settings']['genetic_years']; return $y === 1 ? '1 year' : $y.' years'; }

function rich_inline($s){
  $fmt = function($x){
    $x = preg_replace('/\*\*(.+?)\*\*/u', '<strong>$1</strong>', $x);
    return preg_replace('/(?<![\w*])\*(?!\s)(.+?)(?<!\s)\*(?![\w*])/u', '<em>$1</em>', $x);
  };
  $out = ''; $pos = 0;
  preg_match_all('/\[([^\]]+)\]\(([^)\s]+)\)/u', $s, $links, PREG_OFFSET_CAPTURE | PREG_SET_ORDER);
  foreach($links as $l){
    $out .= $fmt(h(substr($s, $pos, $l[0][1] - $pos)));
    $href = link_target($l[2][0]);
    $ext = preg_match('#^https?://#i', $href ?? '') && strpos($href, (string)parse_url($GLOBALS['SITE']['settings']['domain'], PHP_URL_HOST)) === false;
    $out .= $href !== null ? '<a href="'.h($href).'"'.($ext ? ' rel="noopener" target="_blank"' : '').'>'.$fmt(h($l[1][0])).'</a>' : $fmt(h($l[1][0]));
    $pos = $l[0][1] + strlen($l[0][0]);
  }
  return $out.$fmt(h(substr($s, $pos)));
}

/* links in admin text: a full https:// or mailto: address, or a page of the site written as
   /path (e.g. /health-guarantee, /guides/beagle-price, /beagle-puppies-for-sale) */
function link_target($t){
  if(preg_match('#^(https?://|mailto:|tel:|sms:)#i', $t)) return $t;
  if(preg_match('#^/([a-z0-9/_-]*)(\#[\w-]+)?$#', $t, $m)) return function_exists('path_url') ? path_url($m[1]).($m[2] ?? '') : $t;
  if(preg_match('#^\#[\w-]+$#', $t)) return $t;
  return null;
}

/* $blocks: "{name}" lines replaced with ready-made HTML (e.g. the guarantee cards) */
function rich($text, $blocks=[]){
  $html = ''; $para = []; $list = ''; $table = [];
  $inline = fn($s)=>rich_inline($s);
  $close = function() use(&$html, &$para, &$list, &$table, $inline){
    if($para){ $html .= '<p>'.$inline(implode(' ', $para)).'</p>'; $para = []; }
    if($list){ $html .= "</$list>"; $list = ''; }
    if($table){
      $html .= '<div class="table-wrap"><table><thead><tr>';
      foreach(array_shift($table) as $c) $html .= '<th scope="col">'.$inline($c).'</th>';
      $html .= '</tr></thead><tbody>';
      foreach($table as $row){ $html .= '<tr>'; foreach($row as $i=>$c) $html .= $i === 0 ? '<th scope="row">'.$inline($c).'</th>' : '<td>'.$inline($c).'</td>'; $html .= '</tr>'; }
      $html .= '</tbody></table></div>';
      $table = [];
    }
  };
  foreach(explode("\n", str_replace("\r", '', fill((string)$text))) as $line){
    $line = trim($line);
    if($line === ''){ $close(); continue; }
    if(isset($blocks[$line])){ $close(); $html .= $blocks[$line]; continue; }
    if(preg_match('/^\|(.+)\|$/', $line, $m)){
      if($para || $list){ $t = $table; $table = []; $close(); $table = $t; }
      if(preg_match('/^[\s|:-]+$/', $m[1])) continue;   /* the |---|---| line under the header */
      $table[] = array_map('trim', explode('|', $m[1])); continue;
    }
    if($table){ $close(); }
    if(preg_match('/^(#{2,3})\s+(.+)$/', $line, $m)){ $close(); $t = strlen($m[1]); $html .= "<h$t id=\"".h(slugify(strip_tags($m[2])))."\">".$inline($m[2])."</h$t>"; continue; }
    if(preg_match('/^>\s?(.+)$/', $line, $m)){ $close(); $html .= '<div class="callout"><p>'.$inline($m[1]).'</p></div>'; continue; }
    if(preg_match('/^(?:([-*])|\d{1,2}[.)])\s+(.+)$/', $line, $m)){
      $want = $m[1] !== '' ? 'ul' : 'ol';
      if($para){ $html .= '<p>'.$inline(implode(' ', $para)).'</p>'; $para = []; }
      if($list !== $want){ if($list) $html .= "</$list>"; $html .= "<$want>"; $list = $want; }
      $html .= '<li>'.$inline($m[2]).'</li>'; continue;
    }
    if($list){ $html .= "</$list>"; $list = ''; }
    $para[] = $line;
  }
  $close();
  return $html;
}

/* the ## headings of a text, for a table of contents */
function headings($text){
  preg_match_all('/^##\s+(.+)$/m', fill((string)$text), $m);
  return array_map(fn($t)=>[slugify(strip_tags($t)), preg_replace('/\*\*?|\[|\]\([^)]*\)/', '', $t)], $m[1]);
}

/* "### Question" + answer under a "## Questions" (or "## Frequently asked questions") heading, for FAQ markup */
function text_faqs($text){
  $text = fill((string)$text);
  if(!preg_match('/^##\s+(?:Questions|Frequently asked questions|FAQ|常见问题)[^\n]*\n(.*?)(?=^##\s|\z)/ims', $text, $m)) return [];
  $out = [];
  foreach(preg_split('/^###\s+/m', $m[1]) as $chunk){
    $parts = explode("\n", trim($chunk), 2);
    if(count($parts) === 2 && trim($parts[1]) !== '') $out[] = [trim($parts[0]), plain($parts[1], 1000)];
  }
  return $out;
}

/* text without formatting, cut to $len characters on a word boundary */
function plain($text, $len=155){
  $t = preg_replace('/\[([^\]]+)\]\([^)]+\)/', '$1', fill((string)$text));
  $t = preg_replace('/^(#{2,3}|>|[-*]|\d{1,2}[.)])\s+/m', '', $t);
  $t = trim(preg_replace('/\s+/', ' ', str_replace(['**', '*', '|'], '', $t)));
  if(mb_strlen($t) <= $len) return $t;
  $cut = mb_substr($t, 0, $len - 1);
  return preg_replace('/\s+\S*$/u', '', $cut).'…';
}

function word_count($text){ return str_word_count(plain($text, 1000000)); }
