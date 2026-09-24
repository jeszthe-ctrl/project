<?php
/* Shared by index.php (storefront) and admin.php (backend):
   data storage, sessions and the few helpers both need. */
if(!defined('FK_ROOT')){ http_response_code(404); exit; }

/* Every data file starts with this line, so opening one in a browser shows nothing
   even on hosts that ignore data/.htaccess. The JSON follows on the next line. */
const FK_GUARD = "<?php http_response_code(404); exit; ?>\n";

const PRODUCT_STATUSES = ['in'=>'In stock', 'new'=>'New', 'low'=>'Low stock', 'preorder'=>'Preorder', 'soldout'=>'Sold out'];
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

/* USD: zone base per order + per-kg rate × order weight */
function shipping_usd($store, $country, $kg){
  $z = ship_zone($store, $country);
  return round((float)$z['base'] + (float)$z['per_kg'] * $kg, 2);
}

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
