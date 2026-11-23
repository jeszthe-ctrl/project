<?php
/* POKEKURA data check & restore.
   Upload as pokekura-check.php next to the shop's index.php and open it in the browser.
   It searches your hosting account (including the File Manager trash) for the shop's data:
   products and settings (data/store.php), backups, orders and product photos, and can copy
   them back into this shop after you sign in with an admin username and password.
   It never deletes anything, and it stops working 48 hours after upload. Delete it when done. */
error_reporting(0);
@set_time_limit(60);
session_start();
header('Content-Type: text/html; charset=utf-8');
header('X-Robots-Tag: noindex, nofollow');
header('Cache-Control: no-store');

const GUARD = '<?php http_response_code(404); exit; ?>';
$HERE = __DIR__;
function h($s){ return htmlspecialchars((string)$s, ENT_QUOTES, 'UTF-8'); }
function page($body){
  echo '<!doctype html><meta name="viewport" content="width=device-width,initial-scale=1"><title>POKEKURA data check</title><style>',
    'body{font:15px/1.5 system-ui,sans-serif;color:#111;background:#fff;max-width:980px;margin:0 auto;padding:16px}',
    'h1{font-size:22px}h2{font-size:17px;margin-top:28px}table{border-collapse:collapse;width:100%;font-size:14px}',
    'td,th{border:1px solid #ddd;padding:6px 8px;text-align:left;vertical-align:top}th{background:#f3f6fb}',
    '.ok{color:#0a7a2f;font-weight:600}.bad{color:#b00020;font-weight:600}.n{color:#555}',
    'button,.b{background:#1d4ed8;color:#fff;border:0;border-radius:6px;padding:8px 12px;font:inherit;cursor:pointer;text-decoration:none}',
    'input{font:inherit;padding:7px;border:1px solid #bbb;border-radius:6px;margin:0 6px 6px 0}code{background:#f3f3f3;padding:1px 4px;border-radius:4px;word-break:break-all}',
    '.box{border:1px solid #cdd8ee;background:#f6f9ff;border-radius:8px;padding:12px 14px;margin:12px 0}</style>', $body;
  exit;
}
if(time() - (int)@filemtime(__FILE__) > 48 * 3600) page('<h1>This check page has expired</h1><p>Delete <code>pokekura-check.php</code> from your hosting. Upload a fresh copy if you need it again.</p>');

/* ---------- reading shop files ---------- */
function read_data($f){
  $raw = (string)@file_get_contents($f);
  if(strpos($raw, GUARD) !== 0) return null;
  $v = json_decode(substr($raw, strpos($raw, "\n") + 1), true);
  return is_array($v) ? $v : null;
}
function images_in($dir){
  $n = 0; $new = 0;
  foreach((@scandir($dir) ?: []) as $f) if(preg_match('/\.(jpe?g|png|webp)$/i', $f) && is_file("$dir/$f")){ $n++; $new = max($new, (int)@filemtime("$dir/$f")); }
  return [$n, $new];
}
function orders_in($dir){
  $n = 0; $new = 0;
  foreach((@scandir($dir) ?: []) as $f) if(preg_match('/^[FP]K-\d{2}-[A-F0-9]{5}\.php$/', $f)){ $n++; $new = max($new, (int)@filemtime("$dir/$f")); }
  return [$n, $new];
}
function store_info($f){
  $v = read_data($f);
  if(!$v) return null;
  return ['products'=>count($v['products'] ?? []), 'reviews'=>count($v['reviews'] ?? []), 'payments'=>count($v['payments'] ?? []),
          'version'=>(int)($v['settings']['content_version'] ?? 1), 'time'=>(int)@filemtime($f)];
}
function d($t){ return $t ? date('j M Y, H:i', $t) : '—'; }

/* ---------- searching the hosting account ---------- */
$home = $HERE;
for($i = 0; $i < 3; $i++){ $up = dirname($home); if($up === $home || !@is_readable($up)) break; $home = $up; if(preg_match('#^/(home\d*|var/www|usr/home)/[^/]+$#', $home)) break; }
$skip = ['node_modules', '.git', 'mail', 'logs', 'tmp', '.cpanel', '.cagefs', 'cache', '.cache', 'ssl', 'etc', 'access-logs', '.softaculous', 'perl5', '.npm', '.composer', 'thumbs', 'sessions'];
$found = ['stores'=>[], 'backups'=>[], 'orders'=>[], 'photos'=>[], 'wordpress'=>[]];
$seen = 0; $t0 = microtime(true);
$walk = function($dir, $depth) use(&$walk, &$found, &$seen, $skip, $t0){
  if($depth > 7 || $seen > 300000 || microtime(true) - $t0 > 40) return;
  $list = @scandir($dir); if(!$list) return;
  foreach($list as $f){
    if($f === '.' || $f === '..') continue;
    $p = "$dir/$f"; $seen++;
    if(is_link($p)) continue;
    if(is_dir($p)){
      if(in_array($f, $skip, true)) continue;
      if($f === 'orders'){ [$n, $t] = orders_in($p); if($n) $found['orders'][$p] = [$n, $t]; }
      if($f === 'products' || strpos($f, 'products') === 0 || strpos($dir, '/.trash') !== false){ [$n, $t] = images_in($p); if($n || $f === 'products') $found['photos'][$p] = [$n, $t]; }
      if($f === 'backups'){ foreach((@glob("$p/store-*.php") ?: []) as $b) if($i = store_info($b)) $found['backups'][$b] = $i; }
      $walk($p, $depth + 1);
    } elseif($f === 'store.php' || preg_match('/^store-unreadable-.*\.php$/', $f)){
      if($i = store_info($p)) $found['stores'][$p] = $i;
    } elseif($f === 'wp-config.php'){ $found['wordpress'][] = $dir; }
  }
};
$walk($home, 0);
$trash = is_dir("$home/.trash") ? "$home/.trash" : null;

/* this shop */
$here_store = store_info("$HERE/data/store.php");
[$here_orders] = orders_in("$HERE/data/orders");
[$here_photos] = images_in("$HERE/assets/products");

/* ---------- signing in: an admin username and password from this shop or any shop found ---------- */
$auths = [];
foreach(array_unique(array_merge(["$HERE/data/auth.php"], array_map(fn($s)=>dirname($s).'/auth.php', array_keys($found['stores'])), array_map(fn($o)=>dirname($o).'/auth.php', array_keys($found['orders'])))) as $a)
  if(($v = read_data($a)) && !empty($v['hash'])) $auths[] = $v;
if(empty($_SESSION['pkc_token'])) $_SESSION['pkc_token'] = bin2hex(random_bytes(16));
$tok = $_SESSION['pkc_token'];
$msg = '';
if(($_POST['do'] ?? '') === 'login'){
  usleep(400000);
  foreach($auths as $a) if(password_verify((string)($_POST['password'] ?? ''), $a['hash']) && strcasecmp((string)($a['user'] ?? ''), trim((string)($_POST['user'] ?? ''))) === 0){ $_SESSION['pkc_ok'] = 1; break; }
  if(empty($_SESSION['pkc_ok'])) $msg = '<p class="bad">That username and password didn\'t match any admin login found.</p>';
}
$ok = !empty($_SESSION['pkc_ok']);

/* ---------- restoring (copies only; nothing is deleted) ---------- */
function inside($p, $list){ return isset($list[$p]); }
if($ok && isset($_POST['do']) && hash_equals($tok, (string)($_POST['t'] ?? ''))){
  $src = (string)($_POST['src'] ?? '');
  @mkdir("$HERE/data/backups", 0755, true);
  if($_POST['do'] === 'store' && (inside($src, $found['stores']) || inside($src, $found['backups']))){
    if(realpath($src) === realpath("$HERE/data/store.php")) $msg = '<p class="n">That is already this shop\'s data.</p>';
    else {
      if(is_file("$HERE/data/store.php")) @copy("$HERE/data/store.php", "$HERE/data/backups/before-restore-".date('Ymd-His').'.php');
      $msg = @copy($src, "$HERE/data/store.php.tmp") && @rename("$HERE/data/store.php.tmp", "$HERE/data/store.php")
        ? '<p class="ok">Products, settings, payment methods and reviews restored. The shop\'s previous data was saved in data/backups first.</p>'
        : '<p class="bad">Couldn\'t write data/store.php. Check that the data folder is writable.</p>';
    }
  }
  if($_POST['do'] === 'orders' && inside($src, $found['orders'])){
    @mkdir("$HERE/data/orders", 0755, true); $n = 0; $had = 0;
    foreach((@scandir($src) ?: []) as $f) if(preg_match('/^[FP]K-\d{2}-[A-F0-9]{5}\.php$/', $f)){
      if(is_file("$HERE/data/orders/$f")){ $had++; continue; }
      if(@copy("$src/$f", "$HERE/data/orders/$f")) $n++;
    }
    $msg = "<p class=\"ok\">$n orders copied into this shop ($had were already here).</p>";
  }
  if($_POST['do'] === 'photos' && inside($src, $found['photos'])){
    @mkdir("$HERE/assets/products", 0755, true); $n = 0;
    if(realpath($src) !== realpath("$HERE/assets/products"))
      foreach((@scandir($src) ?: []) as $f) if(preg_match('/\.(jpe?g|png|webp)$/i', $f) && is_file("$src/$f")){
        foreach(['jpg', 'jpeg', 'png', 'webp'] as $e){ $old = "$HERE/assets/products/".pathinfo($f, PATHINFO_FILENAME).".$e"; if(basename($old) !== $f && is_file($old)) @rename($old, $old.'.replaced'); }   /* a starter photo under another extension */
        if(@copy("$src/$f", "$HERE/assets/products/$f")) $n++;
      }
    $msg = "<p class=\"ok\">$n photos copied into this shop.</p>";
  }
  /* refresh the counts */
  $here_store = store_info("$HERE/data/store.php"); [$here_orders] = orders_in("$HERE/data/orders"); [$here_photos] = images_in("$HERE/assets/products");
}

/* ---------- the report ---------- */
$rel = fn($p) => (strpos($p, $home) === 0 ? '~'.substr($p, strlen($home)) : $p);
$where = fn($p) => ($trash && strpos($p, $trash) === 0) ? ' <span class="bad">(in the File Manager trash)</span>' : '';
$btn = fn($do, $src, $label) => $ok ? '<form method="post" style="margin:0"><input type="hidden" name="t" value="'.h($tok).'"><input type="hidden" name="do" value="'.$do.'"><input type="hidden" name="src" value="'.h($src).'"><button>'.h($label).'</button></form>' : '';

$o = '<h1>POKEKURA data check</h1>'.$msg;
$o .= '<div class="box"><b>This shop</b> is in <code>'.h($rel($HERE)).'</code><br>';
$o .= $here_store ? "Products: <b>{$here_store['products']}</b> · Reviews: <b>{$here_store['reviews']}</b> · Payment methods: <b>{$here_store['payments']}</b> · last saved ".d($here_store['time']) : '<span class="bad">No readable data/store.php here.</span>';
$o .= "<br>Orders: <b>$here_orders</b> · Product photos: <b>$here_photos</b></div>";
if(!$ok) $o .= '<div class="box"><b>Sign in to restore</b> with an admin username and password (your current one, or the one you used before).'.
  '<form method="post" style="margin-top:8px"><input type="hidden" name="do" value="login"><input name="user" placeholder="Username" autocomplete="username"><input name="password" type="password" placeholder="Password" autocomplete="current-password"><button>Sign in</button></form>'.
  '<p class="n">Until you sign in, this page only shows what it found.</p></div>';

$o .= '<h2>Orders found</h2>';
if($found['orders']){ $o .= '<table><tr><th>Folder</th><th>Orders</th><th>Newest order</th><th></th></tr>';
  arsort($found['orders']);
  foreach($found['orders'] as $p=>[$n, $t]) $o .= '<tr><td><code>'.h($rel($p)).'</code>'.$where($p).'</td><td><b>'.$n.'</b></td><td>'.d($t).'</td><td>'.(realpath($p) === realpath("$HERE/data/orders") ? '<span class="n">this shop</span>' : $btn('orders', $p, 'Copy these orders here')).'</td></tr>';
  $o .= '</table>';
} else $o .= '<p class="bad">No order files found anywhere in this hosting account.</p>';

$o .= '<h2>Product photos found</h2>';
$ph = array_filter($found['photos'], fn($x)=>$x[0] > 0);
if($ph){ $o .= '<table><tr><th>Folder</th><th>Photos</th><th>Newest photo</th><th></th></tr>';
  uasort($ph, fn($a, $b)=>$b[0] <=> $a[0]);
  foreach($ph as $p=>[$n, $t]) $o .= '<tr><td><code>'.h($rel($p)).'</code>'.$where($p).'</td><td><b>'.$n.'</b></td><td>'.d($t).'</td><td>'.(realpath($p) === realpath("$HERE/assets/products") ? '<span class="n">this shop</span>' : $btn('photos', $p, 'Copy these photos here')).'</td></tr>';
  $o .= '</table>';
} else $o .= '<p class="bad">No product photo folders found.</p>';

$o .= '<h2>Shop data found (products, settings, payment methods, reviews)</h2>';
$all = $found['stores'] + $found['backups'];
if($all){ uasort($all, fn($a, $b)=>$b['time'] <=> $a['time']);
  $o .= '<table><tr><th>File</th><th>Products</th><th>Reviews</th><th>Payment methods</th><th>Saved</th><th></th></tr>';
  $i = 0; foreach($all as $p=>$x){ if(++$i > 40) break;
    $o .= '<tr><td><code>'.h($rel($p)).'</code>'.$where($p).'</td><td>'.$x['products'].'</td><td>'.$x['reviews'].'</td><td>'.$x['payments'].'</td><td>'.d($x['time']).'</td><td>'.(realpath($p) === realpath("$HERE/data/store.php") ? '<span class="n">this shop</span>' : $btn('store', $p, 'Use this data here')).'</td></tr>'; }
  $o .= '</table><p class="n">Backups are saved every time the shop changes; pick the newest one saved before your data went missing.</p>';
} else $o .= '<p class="bad">No shop data files found.</p>';

if($found['wordpress']) $o .= '<h2>Other sites</h2><p>WordPress found in: '.implode(', ', array_map(fn($p)=>'<code>'.h($rel($p)).'</code>', $found['wordpress'])).'. If your orders were taken there (for example with WooCommerce), they are in that site\'s database, not in files.</p>';
$o .= '<p class="n">Searched '.number_format($seen).' files and folders under <code>'.h($home).'</code>. Nothing is ever deleted by this page. <b>Delete pokekura-check.php when you\'re done</b>; it stops working 48 hours after upload anyway.</p>';
page($o);
