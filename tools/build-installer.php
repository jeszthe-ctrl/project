<?php
/* Builds dist/index.php: the whole shop in one file.

   Upload that single index.php to the web space and open the site: it unpacks admin.php, inc/,
   assets/, .htaccess, robots.txt and data/ next to itself, then replaces itself with the shop's
   real index.php. Existing data (products, orders, the admin login) is never overwritten, so the
   same file also updates a running shop.

   Run:  php tools/build-installer.php [admin-password]
   With a password, a fresh install also gets that admin login (otherwise admin.php asks for one). */
if(PHP_SAPI !== 'cli'){ http_response_code(404); exit; }

$root = dirname(__DIR__);
$list = [];
foreach(['index.php', 'admin.php', '.htaccess', 'robots.txt', 'README.md', 'data/.htaccess', 'data/index.html', 'assets/products/.gitkeep'] as $f) $list[] = $f;
foreach(['inc', 'assets'] as $dir){
  $it = new RecursiveIteratorIterator(new RecursiveDirectoryIterator("$root/$dir", FilesystemIterator::SKIP_DOTS));
  foreach($it as $file){
    $rel = substr($file->getPathname(), strlen($root) + 1);
    if(strpos($rel, 'assets/products/') === 0) continue;          // uploaded photos and thumbnails stay on the server
    $list[] = $rel;
  }
}
$list = array_values(array_unique($list));
sort($list);

$pack = '';
foreach($list as $rel){
  $data = file_get_contents("$root/$rel");
  if($data === false){ fwrite(STDERR, "missing $rel\n"); exit(1); }
  $pack .= pack('N', strlen($rel)).$rel.pack('N', strlen($data)).$data;
}
if(isset($argv[1]) && $argv[1] !== ''){
  if(strlen($argv[1]) < 10){ fwrite(STDERR, "Use a password of at least 10 characters.\n"); exit(1); }
  $auth = "<?php http_response_code(404); exit; ?>\n".json_encode(['hash'=>password_hash($argv[1], PASSWORD_DEFAULT), 'key'=>bin2hex(random_bytes(16)), 'fails'=>[]]);
  $pack .= pack('N', 13).'data/auth.php'.pack('N', strlen($auth)).$auth;
}

$stub = <<<'PHP'
<?php
/* =============================================================
   POKEKURA — the whole shop in this one file.

   Upload this index.php to your web space (e.g. public_html) and
   open your site. It unpacks the shop next to itself (admin.php,
   inc/, assets/, .htaccess, robots.txt, data/) and then replaces
   itself with the shop's own index.php.

   Your products, orders and admin password in data/ are never
   overwritten, so uploading a newer copy of this file later is
   also how you update the shop.
   ============================================================= */
error_reporting(0);
$dir  = __DIR__;
$raw  = @file_get_contents(__FILE__, false, null, __COMPILER_HALT_OFFSET__);
if(!is_string($raw) || substr($raw, 0, 6) !== 'FKPK1:'){ header('Location: ./'); exit; }   // already installed
$raw  = substr($raw, 6);
$page = function($title, $html){
  echo '<!DOCTYPE html><html lang="en"><head><meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1"><meta name="robots" content="noindex">',
       '<title>', $title, '</title><style>body{margin:0;background:#050507;color:#F5F6FB;font:16px/1.6 system-ui,sans-serif}main{max-width:640px;margin:10vh auto;padding:0 20px}',
       'h1{font-size:28px}a.b{display:inline-block;background:linear-gradient(135deg,#FF3B5C,#FF6B3D);color:#fff;padding:12px 20px;border-radius:999px;text-decoration:none;font-weight:700;margin:6px 8px 0 0}',
       'code{background:#161924;padding:2px 6px;border-radius:5px}.n{color:#C4C9D9}</style></head><body><main>', $html, '</main></body></html>';
  exit;
};
if(!is_writable($dir)) $page('Can’t install', '<h1>The shop can’t unpack itself here</h1><p class="n">PHP isn’t allowed to create files in this folder. Ask your host to make it writable by PHP, or upload the files from <code>pokekura-site.zip</code> instead.</p>');

$files = []; $pos = 0; $len = strlen($raw);
while($pos + 8 <= $len){
  $n = unpack('N', substr($raw, $pos, 4))[1]; $rel = substr($raw, $pos + 4, $n); $pos += 4 + $n;
  $m = unpack('N', substr($raw, $pos, 4))[1]; $files[$rel] = substr($raw, $pos + 4, $m); $pos += 4 + $m;
}
$done = []; $kept = [];
foreach($files as $rel => $data){
  if($rel === 'index.php' || !preg_match('#^[A-Za-z0-9._/-]+$#', $rel) || strpos($rel, '..') !== false) continue;
  $path = $dir.'/'.$rel;
  if(!is_dir(dirname($path)) && !@mkdir(dirname($path), 0755, true)) $page('Can’t install', '<h1>Couldn’t create '.htmlspecialchars(dirname($rel)).'</h1><p class="n">Ask your host to make this folder writable by PHP, or upload the zip instead.</p>');
  if(strpos($rel, 'data/') === 0 && !in_array($rel, ['data/.htaccess', 'data/index.html'], true) && file_exists($path)){ $kept[] = $rel; continue; }   // your data stays
  if($rel === '.htaccess' && is_file($path)){
    $old = (string)file_get_contents($path);
    if(strpos($old, 'Clean page addresses') !== false){ $kept[] = $rel; continue; }   // ours already
    $data = rtrim($old)."\n\n".$data;                                                  // keep the host's own rules
  }
  $tmp = $path.'.'.bin2hex(random_bytes(3)).'.tmp';
  if(@file_put_contents($tmp, $data) === false || !@rename($tmp, $path)){ @unlink($tmp); $page('Can’t install', '<h1>Couldn’t write '.htmlspecialchars($rel).'</h1><p class="n">Ask your host to make this folder writable by PHP, or upload the zip instead.</p>'); }
  @chmod($path, 0644);
  $done[] = $rel;
}
$moved = '';
if(is_file($dir.'/index.html')){ @rename($dir.'/index.html', $dir.'/index.html.old'); $moved = '<p class="n">Your host’s placeholder page <code>index.html</code> was renamed to <code>index.html.old</code> so the shop shows instead.</p>'; }
$tmp = $dir.'/index.php.'.bin2hex(random_bytes(3)).'.tmp';
if(@file_put_contents($tmp, $files['index.php']) === false || !@rename($tmp, __FILE__)){ @unlink($tmp); $page('Can’t install', '<h1>Couldn’t finish</h1><p class="n">The shop files are unpacked, but index.php couldn’t be replaced. Upload <code>index.php</code> from the zip.</p>'); }
if(function_exists('opcache_invalidate')) @opcache_invalidate(__FILE__, true);
$page('Shop installed', '<h1>Your shop is installed ✓</h1><p class="n">'.count($done).' files unpacked'.($kept ? ', '.count($kept).' of your existing files kept as they were' : '').'.</p>'.$moved.
  '<p><a class="b" href="./">Open the shop</a><a class="b" href="admin.php">Open the admin</a></p>'.
  '<p class="n" id="cu">Checking clean page addresses…</p>'.
  '<p class="n">Next, in the admin: check <b>Settings</b> (business details, test email) and <b>Shipping</b>, then add your product photos.</p>'.
  '<script>var cu=document.getElementById("cu");fetch("rewrite-check",{cache:"no-store"}).then(function(r){return r.ok?r.json():{}}).then(function(j){cu.innerHTML=j.clean_urls?"Clean page addresses (like <code>/products/…</code>) are <b>on</b> ✓":"Your host doesn’t support clean page addresses, so the shop uses <code>index.php?p=…</code> addresses. Everything works either way."}).catch(function(){cu.textContent="Your host doesn’t support clean page addresses. Everything works either way."});</script>');
__halt_compiler();
PHP;

@mkdir("$root/dist", 0755, true);
file_put_contents("$root/dist/index.php", $stub.'FKPK1:'.$pack);
printf("dist/index.php: %d files, %.0f KB%s\n", count($list), filesize("$root/dist/index.php") / 1024, isset($auth) ? ', with admin login' : '');
