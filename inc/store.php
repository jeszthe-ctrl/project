<?php
/* Shared by index.php (storefront) and admin.php (backend):
   data storage, sessions and the few helpers both need. */
if(!defined('FK_ROOT')){ http_response_code(404); exit; }

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
  session_start();
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
  if(!$s){ $s = $d; data_write('store', $s); }   // first run: seed from defaults
  $s += $d;                                       // sections added by later versions
  $s['settings'] += $d['settings'];
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
  imagedestroy($src); imagedestroy($dst);
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
  $s = strtolower(strtr(trim((string)$s), ['é'=>'e','è'=>'e','ê'=>'e','É'=>'e','á'=>'a','à'=>'a','ä'=>'a','ó'=>'o','ö'=>'o','ú'=>'u','ü'=>'u','í'=>'i','ñ'=>'n','—'=>'-','–'=>'-']));
  return trim(preg_replace('/[^a-z0-9]+/', '-', $s), '-');
}

/* ---------------- shipping ---------------- */
function ship_zone($store, $country){
  foreach($store['shipping']['zones'] as $z){
    if(in_array($country, $z['countries'], true)) return $z;
  }
  return ['name'=>'Rest of world', 'countries'=>[]] + $store['shipping']['rest'];
}

const SHIP_METHODS = ['standard', 'express'];

/* delivery options shown at checkout, with their names and delivery times */
function ship_methods($store){
  $m = $store['shipping']['methods'] ?? [];
  return ['standard'=>($m['standard'] ?? []) + ['label'=>'Standard', 'days'=>'3–6 working days'],
          'express' =>($m['express'] ?? [])  + ['label'=>'Express',  'days'=>'1–2 working days']];
}

/* a zone's rates per method (older data had one flat rate: that becomes Standard, Express is double) */
function zone_rates($z){
  $std = $z['standard'] ?? ['base'=>(float)($z['base'] ?? 0), 'per_kg'=>(float)($z['per_kg'] ?? 0)];
  $exp = $z['express'] ?? ['base'=>$std['base'] * 2, 'per_kg'=>$std['per_kg'] * 2];
  return ['standard'=>$std, 'express'=>$exp];
}

/* USD: the zone's per-order price + per-kg price × order weight, rounded up to a whole dollar */
function shipping_usd($store, $country, $kg, $method='standard'){
  $r = zone_rates(ship_zone($store, $country))[$method === 'express' ? 'express' : 'standard'];
  $v = round((float)$r['base'] + (float)$r['per_kg'] * $kg, 2);
  return empty($store['shipping']['round_up']) ? $v : ceil($v);
}

/* "3–6 working days" → [3, 6], for Google's delivery-time data */
function ship_day_range($days){ return preg_match('/(\d+)\D+(\d+)/', (string)$days, $m) ? [(int)$m[1], (int)$m[2]] : [3, 6]; }

/* ---------------- orders (data/orders/{ref}.php) ---------------- */
function valid_ref($ref){ return is_string($ref) && preg_match('/^FK-\d{2}-[A-F0-9]{5}$/', $ref); }

function new_order_ref(){
  do { $ref = 'FK-'.date('y').'-'.strtoupper(substr(bin2hex(random_bytes(3)), 0, 5)); }
  while(is_file(data_file('orders/'.$ref)));
  return $ref;
}

function order_save($order){ return valid_ref($order['ref'] ?? '') && data_write('orders/'.$order['ref'], $order); }
function order_load($ref){ return valid_ref($ref) ? data_read('orders/'.$ref) : null; }

function orders_all(){
  $out = [];
  foreach(glob(data_dir().'/orders/FK-*.php') ?: [] as $f){
    $o = order_load(basename($f, '.php'));
    if($o) $out[] = $o;
  }
  usort($out, fn($a, $b) => strcmp($b['time_iso'] ?? '', $a['time_iso'] ?? ''));
  return $out;
}
