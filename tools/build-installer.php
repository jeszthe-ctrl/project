<?php
/* Builds the two ways to put the shop on a web host:

   dist/index.php           the whole shop in one file. Upload it to the web space and open
                            https://your-domain/index.php: it checks the host, unpacks admin.php, inc/,
                            assets/, .htaccess, robots.txt and data/ next to itself, then becomes the
                            shop's real index.php. Existing data (products, orders, the admin login) is
                            never overwritten, so the same file also updates a running shop.
   dist/fudakura-upload.zip the same files as a zip, to upload and "Extract" in the host's File Manager.

   The site travels inside index.php as plain base64 text with a SHA-256 fingerprint, so a copy that
   was damaged on the way (text-mode FTP, an editor, a cut-off upload) is detected before anything
   is written, instead of half-installing.

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

$files = [];
foreach($list as $rel){
  $data = file_get_contents("$root/$rel");
  if($data === false){ fwrite(STDERR, "missing $rel\n"); exit(1); }
  $files[$rel] = $data;
}
if(isset($argv[1]) && $argv[1] !== ''){
  if(strlen($argv[1]) < 10){ fwrite(STDERR, "Use a password of at least 10 characters.\n"); exit(1); }
  $files['data/auth.php'] = "<?php http_response_code(404); exit; ?>\n".json_encode(['hash'=>password_hash($argv[1], PASSWORD_DEFAULT), 'key'=>bin2hex(random_bytes(16)), 'fails'=>[]]);
}
$pack = '';
foreach($files as $rel=>$data) $pack .= pack('N', strlen($rel)).$rel.pack('N', strlen($data)).$data;

/* The installer runs on whatever PHP the host has, so it is written for PHP 5.3+ and can at least
   say "your PHP is too old" instead of showing a blank page. */
$stub = <<<'PHP'
<?php
/* =============================================================
   FUDAKURA — the whole shop in this one file.

   1. Upload this index.php to your web space (e.g. public_html)
      with your host's File Manager "Upload" button.
   2. Open  https://your-domain/index.php  in your browser.

   It checks your hosting, unpacks the shop next to itself
   (admin.php, inc/, assets/, .htaccess, robots.txt, data/) and
   then becomes the shop's own index.php.

   Your products, orders and admin password in data/ are never
   overwritten, so uploading a newer copy of this file later is
   also how you update the shop.
   ============================================================= */
@ini_set('display_errors', '0');
error_reporting(0);
$pk_dir  = dirname(__FILE__);
$pk_self = basename(__FILE__);

function pk_page($title, $html, $refresh = 0){
  if(!headers_sent()){ header('Content-Type: text/html; charset=utf-8'); header('Cache-Control: no-store'); header('X-Robots-Tag: noindex'); }
  echo '<!DOCTYPE html><html lang="en"><head><meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1"><meta name="robots" content="noindex">',
       $refresh ? '<meta http-equiv="refresh" content="'.(int)$refresh.';url=./">' : '',
       '<title>', $title, '</title><style>body{margin:0;background:#fff;color:#0C1633;font:16px/1.6 system-ui,-apple-system,"Segoe UI",Roboto,sans-serif}',
       'main{max-width:680px;margin:8vh auto;padding:0 20px}h1{font-size:28px;letter-spacing:-.02em;line-height:1.2}h2{font-size:18px;margin:26px 0 6px}',
       'a.b{display:inline-block;background:#2A44D4;color:#fff;padding:12px 20px;border-radius:10px;text-decoration:none;font-weight:700;margin:6px 8px 0 0}a.b+a.b{background:#fff;color:#0C1633;box-shadow:inset 0 0 0 1px #E1E5EE}',
       'code{background:#F2F4F9;padding:2px 6px;border-radius:5px}.n{color:#3A4566}.w{border-left:3px solid #E8870F;background:#FFF7EC;padding:10px 14px;border-radius:0 8px 8px 0;margin:10px 0}',
       '.e{border-left:3px solid #B5241D;background:#FEF1F0;padding:12px 16px;border-radius:0 8px 8px 0;margin:14px 0}ol,ul{padding-left:22px}li{margin:4px 0}</style></head><body><main>',
       $html, '</main></body></html>';
  exit;
}
function pk_damaged(){
  pk_page('Upload again', '<h1>This file was damaged on its way to your server</h1>'.
    '<div class="e">Nothing was installed and nothing on your site was changed.</div>'.
    '<p class="n">This happens when the file is uploaded in “text” mode, opened and saved in an editor, copied and pasted, or cut off during upload.</p>'.
    '<h2>To fix it</h2><ol><li>Download <code>index.php</code> again (don’t open it).</li>'.
    '<li>In your hosting control panel open <b>File Manager</b> → <code>public_html</code> and use the <b>Upload</b> button to upload it (if you use FTP, set the transfer mode to <b>Binary</b>).</li>'.
    '<li>Open this page again.</li></ol>'.
    '<p class="n">Or use <code>fudakura-upload.zip</code>: upload it to <code>public_html</code>, right-click it and choose <b>Extract</b>.</p>');
}

/* 1. PHP version */
if(version_compare(PHP_VERSION, '7.4.0', '<'))
  pk_page('PHP too old', '<h1>Your hosting runs PHP '.htmlspecialchars(PHP_VERSION).'</h1>'.
    '<div class="e">The shop needs PHP 7.4 or newer (8.2 or 8.3 is best). Nothing was installed.</div>'.
    '<h2>To fix it</h2><ol><li>In your hosting control panel find <b>Select PHP Version</b>, <b>MultiPHP Manager</b> or <b>PHP Configuration</b>.</li>'.
    '<li>Choose <b>8.2</b> or <b>8.3</b> for your domain and save.</li><li>Reload this page.</li></ol>');

/* 2. the shop inside this file, checked against its fingerprint before anything is written */
$pk_raw = @file_get_contents(__FILE__, false, null, __COMPILER_HALT_OFFSET__);
$pk_at  = is_string($pk_raw) ? strpos($pk_raw, 'PKB64:') : false;
if($pk_at === false){
  if(is_file($pk_dir.'/inc/store.php') && is_file($pk_dir.'/admin.php')){
    /* already installed: the host's PHP cache is still serving the installer for a moment */
    if(function_exists('opcache_invalidate')) @opcache_invalidate(__FILE__, true);
    pk_page('Finishing', '<h1>Your shop is installed ✓</h1><p class="n">Your host is still showing the installer for a moment. This page opens the shop by itself in a few seconds.</p><p><a class="b" href="./">Open the shop</a></p>', 4);
  }
  pk_damaged();
}
$pk_pack = base64_decode(substr($pk_raw, $pk_at + 6));   /* line breaks in the text are skipped */
unset($pk_raw);
if(!is_string($pk_pack) || !function_exists('hash') || hash('sha256', $pk_pack) !== '%%SHA256%%') pk_damaged();

/* 3. what the shop needs from PHP */
$pk_need = array('json_encode'=>'json', 'session_start'=>'session', 'filter_var'=>'filter', 'hash_hmac'=>'hash', 'preg_match'=>'pcre', 'random_bytes'=>'random', 'password_hash'=>'password');
$pk_missing = array();
foreach($pk_need as $fn=>$ext) if(!function_exists($fn)) $pk_missing[] = $ext;
if($pk_missing)
  pk_page('PHP settings', '<h1>Your hosting’s PHP is missing something the shop needs</h1>'.
    '<div class="e">Missing: <b>'.htmlspecialchars(implode(', ', array_unique($pk_missing))).'</b>. Nothing was installed.</div>'.
    '<p class="n">In your hosting control panel open <b>Select PHP Version</b> → <b>Extensions</b> (or <b>PHP Configuration</b>), tick the missing ones, save, and reload this page.</p>');
$pk_warn = array();
if(!extension_loaded('openssl')) $pk_warn[] = 'The PHP <b>openssl</b> extension is off: Bitcoin prices and sending email through your mailbox (SMTP) need it. Turn it on in <b>Select PHP Version → Extensions</b>.';
if(!function_exists('curl_init') && !ini_get('allow_url_fopen')) $pk_warn[] = 'PHP can’t fetch web pages (<b>curl</b> is off and <b>allow_url_fopen</b> is off), so Bitcoin prices can’t be looked up. Turn on the <b>curl</b> extension.';
if(!function_exists('imagecreatetruecolor')) $pk_warn[] = 'The PHP <b>gd</b> extension is off, so product photos are shown at full size (pages load a little slower). Turn it on in <b>Select PHP Version → Extensions</b>.';

/* 4. can PHP write here? */
if(!is_writable($pk_dir))
  pk_page('Can’t install', '<h1>The shop can’t unpack itself in this folder</h1><div class="e">PHP isn’t allowed to create files here. Nothing was installed.</div>'.
    '<p class="n">Use <code>fudakura-upload.zip</code> instead: upload it to <code>public_html</code> in File Manager, right-click it and choose <b>Extract</b>. Or ask your host to make the folder writable by PHP.</p>');

/* 5. unpack */
$pk_files = array(); $pos = 0; $len = strlen($pk_pack);
while($pos + 8 <= $len){
  $n = unpack('N', substr($pk_pack, $pos, 4)); $n = $n[1]; $rel = substr($pk_pack, $pos + 4, $n); $pos += 4 + $n;
  $m = unpack('N', substr($pk_pack, $pos, 4)); $m = $m[1]; $pk_files[$rel] = substr($pk_pack, $pos + 4, $m); $pos += 4 + $m;
}
unset($pk_pack);
if(!isset($pk_files['index.php'])) pk_damaged();
$done = array(); $kept = array();
foreach($pk_files as $rel=>$data){
  if($rel === 'index.php' || !preg_match('#^[A-Za-z0-9._/-]+$#', $rel) || strpos($rel, '..') !== false) continue;
  $path = $pk_dir.'/'.$rel;
  if(!is_dir(dirname($path)) && !@mkdir(dirname($path), 0755, true)) pk_page('Can’t install', '<h1>Couldn’t create the folder '.htmlspecialchars(dirname($rel)).'</h1><p class="n">Use <code>fudakura-upload.zip</code> instead, or ask your host to make this folder writable by PHP.</p>');
  if(strpos($rel, 'data/') === 0 && !in_array($rel, array('data/.htaccess', 'data/index.html'), true) && file_exists($path)){ $kept[] = $rel; continue; }   /* your data stays */
  if($rel === '.htaccess' && is_file($path)){
    $old = (string)file_get_contents($path);
    if(strpos($old, 'Clean page addresses') !== false){ $kept[] = $rel; continue; }   /* ours already */
    $data = rtrim($old)."\n\n".$data;                                                  /* keep the host's own rules */
  }
  $tmp = $path.'.'.mt_rand(100000, 999999).'.tmp';
  if(@file_put_contents($tmp, $data) === false || !@rename($tmp, $path)){ @unlink($tmp); pk_page('Can’t install', '<h1>Couldn’t write '.htmlspecialchars($rel).'</h1><p class="n">Use <code>fudakura-upload.zip</code> instead, or ask your host to make this folder writable by PHP.</p>'); }
  @chmod($path, 0644);
  $done[] = $rel;
}

/* 6. the host's placeholder pages would be shown instead of the shop */
$moved = array();
foreach(array('index.html', 'index.htm', 'default.php', 'default.html', 'default.htm') as $ph){
  if($ph !== $pk_self && is_file($pk_dir.'/'.$ph) && @rename($pk_dir.'/'.$ph, $pk_dir.'/'.$ph.'.old')) $moved[] = $ph;
}

/* 7. become the shop's index.php (also when this file was uploaded under another name) */
$target = $pk_dir.'/index.php';
if($pk_self !== 'index.php' && is_file($target) && strpos((string)@file_get_contents($target, false, null, 0, 4000), 'FK_ROOT') === false) @rename($target, $target.'.old');
$tmp = $target.'.'.mt_rand(100000, 999999).'.tmp';
if(@file_put_contents($tmp, $pk_files['index.php']) === false || !@rename($tmp, $target)){ @unlink($tmp); pk_page('Couldn’t finish', '<h1>Couldn’t finish</h1><p class="n">The shop files are unpacked, but index.php couldn’t be replaced. Use <code>fudakura-upload.zip</code> instead.</p>'); }
@chmod($target, 0644);
if($pk_self !== 'index.php') @unlink(__FILE__);
if(function_exists('opcache_invalidate')){ @opcache_invalidate($target, true); @opcache_invalidate(__FILE__, true); }
clearstatcache();

$html = '<h1>Your shop is installed ✓</h1><p class="n">'.count($done).' files unpacked'.($kept ? ', '.count($kept).' of your existing files kept as they were' : '').'.</p>';
if($moved) $html .= '<p class="n">Your host’s placeholder page ('.htmlspecialchars(implode(', ', $moved)).') was renamed with <code>.old</code> on the end, so the shop shows instead.</p>';
foreach($pk_warn as $w) $html .= '<div class="w">'.$w.'</div>';
$html .= '<p><a class="b" href="./">Open the shop</a><a class="b" href="admin.php">Open the admin</a></p>'.
  '<p class="n" id="cu">Checking clean page addresses…</p>'.
  '<p class="n">Next, in the admin: check <b>Settings</b> (business details, test email) and <b>Shipping</b>, then add your product photos.</p>'.
  '<script>var cu=document.getElementById("cu");fetch("rewrite-check",{cache:"no-store"}).then(function(r){return r.ok?r.json():{}}).then(function(j){cu.innerHTML=j.clean_urls?"Clean page addresses (like <code>/products/…</code>) are <b>on</b> ✓":"Your host doesn’t support clean page addresses, so the shop uses <code>index.php?p=…</code> addresses. Everything works either way."}).catch(function(){cu.textContent="Your host doesn’t support clean page addresses. Everything works either way."});</script>';
pk_page('Shop installed', $html);
__halt_compiler();
PHP;

$b64 = chunk_split(base64_encode($pack), 76, "\n");
@mkdir("$root/dist", 0755, true);
file_put_contents("$root/dist/index.php", str_replace('%%SHA256%%', hash('sha256', $pack), $stub).'PKB64:'."\n".$b64);
printf("dist/index.php: %d files, %.0f KB%s\n", count($files), filesize("$root/dist/index.php") / 1024, isset($files['data/auth.php']) ? ', with admin login' : '');

/* the same files as a zip, for hosts where uploading a zip and choosing "Extract" is easier */
if(class_exists('ZipArchive')){
  $zf = "$root/dist/fudakura-upload.zip"; @unlink($zf);
  $z = new ZipArchive();
  if($z->open($zf, ZipArchive::CREATE) === true){
    foreach($files as $rel=>$data) $z->addFromString($rel, $data);
    $z->close();
    printf("dist/fudakura-upload.zip: %d files, %.0f KB\n", count($files), filesize($zf) / 1024);
  }
}
