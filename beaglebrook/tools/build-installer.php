<?php
/* Builds the two ways to put the Beagle Brook website on a web host:

   dist/index.php               the whole site in one file. Upload it to the web space and open
                                https://your-domain/index.php: it checks the host, unpacks admin.php, inc/,
                                assets/, .htaccess and data/ next to itself, then becomes the site's real
                                index.php. Existing data (puppies, inquiries, the admin sign-in, your photos)
                                is never overwritten, so the same file also updates a running site.
   dist/beaglebrook-upload.zip  the same files as a zip, to upload and "Extract" in the host's File Manager.

   The site travels inside index.php as plain base64 text with a SHA-256 fingerprint, so a copy that
   was damaged on the way (text-mode FTP, an editor, a cut-off upload) is detected before anything
   is written, instead of half-installing.

   Run:  php tools/build-installer.php [admin-password] [admin-username]
   With a password, a fresh install also gets that admin sign-in (username "admin" unless given);
   otherwise admin.php asks for one on the first visit. Use a password only for a first install. */
if(PHP_SAPI !== 'cli'){ http_response_code(404); exit; }

$root = dirname(__DIR__);
$list = [];
$it = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($root, FilesystemIterator::SKIP_DOTS));
foreach($it as $file){
  $rel = str_replace('\\', '/', substr($file->getPathname(), strlen($root) + 1));
  if(preg_match('#^(dist|tools|docs)/|(^|/)\.git|\.DS_Store$|^\.gitignore$#', $rel)) continue;
  if(strpos($rel, 'data/') === 0 && !in_array($rel, ['data/.htaccess', 'data/index.html'], true)) continue;              // your data stays on the server
  if(strpos($rel, 'assets/uploads/') === 0 && !in_array($rel, ['assets/uploads/.htaccess', 'assets/uploads/.gitkeep', 'assets/uploads/index.html'], true)) continue;   // and your photos
  $list[] = $rel;
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
  $user = $argv[2] ?? 'admin';
  if(!preg_match('/^[A-Za-z0-9._@-]{3,40}$/', $user)){ fwrite(STDERR, "Use a username of 3-40 letters or numbers.\n"); exit(1); }
  $files['data/auth.php'] = "<?php http_response_code(404); exit; ?>\n".json_encode(['user'=>$user, 'hash'=>password_hash($argv[1], PASSWORD_DEFAULT), 'key'=>bin2hex(random_bytes(16)), 'fails'=>[]]);
}
$pack = '';
foreach($files as $rel=>$data) $pack .= pack('N', strlen($rel)).$rel.pack('N', strlen($data)).$data;

/* The installer runs on whatever PHP the host has, so it is written for PHP 5.3+ and can at least
   say "your PHP is too old" instead of showing a blank page. */
$stub = <<<'PHP'
<?php
/* =============================================================
   Beagle Brook — the whole website in this one file.

   1. Upload this index.php to your web space (e.g. public_html)
      with your host's File Manager "Upload" button.
   2. Open  https://your-domain/index.php  in your browser.

   It checks your hosting, unpacks the website next to itself
   (admin.php, inc/, assets/, .htaccess, data/) and then becomes
   the site's own index.php.

   Your puppies, inquiries, photos and admin sign-in are never
   overwritten, so uploading a newer copy of this file later is
   also how you update the website.
   ============================================================= */
@ini_set('display_errors', '0');
error_reporting(0);
$bb_dir  = dirname(__FILE__);
$bb_self = basename(__FILE__);

function bb_page($title, $html, $refresh = 0){
  if(!headers_sent()){ header('Content-Type: text/html; charset=utf-8'); header('Cache-Control: no-store'); header('X-Robots-Tag: noindex'); }
  echo '<!DOCTYPE html><html lang="en"><head><meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1"><meta name="robots" content="noindex">',
       $refresh ? '<meta http-equiv="refresh" content="'.(int)$refresh.';url=./">' : '',
       '<title>', $title, '</title><style>body{margin:0;background:#fbf7f0;color:#2a1f17;font:16px/1.6 system-ui,-apple-system,"Segoe UI",Roboto,sans-serif}',
       'main{max-width:680px;margin:8vh auto;padding:0 20px}h1{font-size:28px;letter-spacing:-.02em;line-height:1.2}h2{font-size:18px;margin:26px 0 6px}',
       'a.b{display:inline-block;background:#7a4a1e;color:#fff;padding:12px 20px;border-radius:10px;text-decoration:none;font-weight:700;margin:6px 8px 0 0}a.b+a.b{background:#fff;color:#5e3814;box-shadow:inset 0 0 0 1px #e7dccb}',
       'code{background:#f4ecdf;padding:2px 6px;border-radius:5px}.n{color:#6b5a4b}.w{border-left:3px solid #E8870F;background:#FFF7EC;padding:10px 14px;border-radius:0 8px 8px 0;margin:10px 0}',
       '.e{border-left:3px solid #B5241D;background:#FEF1F0;padding:12px 16px;border-radius:0 8px 8px 0;margin:14px 0}ol,ul{padding-left:22px}li{margin:4px 0}.lg{width:72px;margin-bottom:22px}.lg svg{display:block;width:100%;height:auto}</style></head><body><main>',
       '<div class="lg">%%LOGO%%</div>', $html, '</main></body></html>';
  exit;
}
function bb_damaged(){
  bb_page('Upload again', '<h1>This file was damaged on its way to your server</h1>'.
    '<div class="e">Nothing was installed and nothing on your site was changed.</div>'.
    '<p class="n">This happens when the file is uploaded in “text” mode, opened and saved in an editor, copied and pasted, or cut off during upload.</p>'.
    '<h2>To fix it</h2><ol><li>Download <code>index.php</code> again (don’t open it).</li>'.
    '<li>In your hosting control panel open <b>File Manager</b> → <code>public_html</code> and use the <b>Upload</b> button to upload it (if you use FTP, set the transfer mode to <b>Binary</b>).</li>'.
    '<li>Open this page again.</li></ol>'.
    '<p class="n">Or use <code>beaglebrook-upload.zip</code>: upload it to <code>public_html</code>, right-click it and choose <b>Extract</b>.</p>');
}

/* 1. PHP version */
if(version_compare(PHP_VERSION, '7.4.0', '<'))
  bb_page('PHP too old', '<h1>Your hosting runs PHP '.htmlspecialchars(PHP_VERSION).'</h1>'.
    '<div class="e">The website needs PHP 7.4 or newer (8.2 or 8.3 is best). Nothing was installed.</div>'.
    '<h2>To fix it</h2><ol><li>In your hosting control panel find <b>Select PHP Version</b>, <b>MultiPHP Manager</b> or <b>PHP Configuration</b>.</li>'.
    '<li>Choose <b>8.2</b> or <b>8.3</b> for your domain and save.</li><li>Reload this page.</li></ol>');

/* 2. the website inside this file, checked against its fingerprint before anything is written */
$bb_raw = @file_get_contents(__FILE__, false, null, __COMPILER_HALT_OFFSET__);
$bb_at  = is_string($bb_raw) ? strpos($bb_raw, 'BBB64:') : false;
if($bb_at === false){
  if(is_file($bb_dir.'/inc/core.php') && is_file($bb_dir.'/admin.php')){
    /* already installed: the host's PHP cache is still serving the installer for a moment */
    if(function_exists('opcache_invalidate')) @opcache_invalidate(__FILE__, true);
    bb_page('Finishing', '<h1>Your website is installed ✓</h1><p class="n">Your host is still showing the installer for a moment. This page opens the website by itself in a few seconds.</p><p><a class="b" href="./">Open the website</a></p>', 4);
  }
  bb_damaged();
}
$bb_pack = base64_decode(substr($bb_raw, $bb_at + 6));   /* line breaks in the text are skipped */
unset($bb_raw);
if(!is_string($bb_pack) || !function_exists('hash') || hash('sha256', $bb_pack) !== '%%SHA256%%') bb_damaged();

/* 3. what the website needs from PHP */
$bb_need = array('json_encode'=>'json', 'session_start'=>'session', 'filter_var'=>'filter', 'hash_hmac'=>'hash', 'preg_match'=>'pcre', 'random_bytes'=>'random', 'password_hash'=>'password');
$bb_missing = array();
foreach($bb_need as $fn=>$ext) if(!function_exists($fn)) $bb_missing[] = $ext;
if($bb_missing)
  bb_page('PHP settings', '<h1>Your hosting’s PHP is missing something the website needs</h1>'.
    '<div class="e">Missing: <b>'.htmlspecialchars(implode(', ', array_unique($bb_missing))).'</b>. Nothing was installed.</div>'.
    '<p class="n">In your hosting control panel open <b>Select PHP Version</b> → <b>Extensions</b> (or <b>PHP Configuration</b>), tick the missing ones, save, and reload this page.</p>');
$bb_warn = array();
if(!extension_loaded('openssl')) $bb_warn[] = 'The PHP <b>openssl</b> extension is off: sending inquiry emails through your mailbox (SMTP) needs it. Turn it on in <b>Select PHP Version → Extensions</b>.';
if(!function_exists('curl_init')) $bb_warn[] = 'The PHP <b>curl</b> extension is off: text alerts through Twilio need it (email-to-text alerts still work). Turn it on in <b>Select PHP Version → Extensions</b> if you use Twilio.';
if(!function_exists('imagecreatetruecolor')) $bb_warn[] = 'The PHP <b>gd</b> extension is off, so photos can’t be resized or cleaned of their location data, and pages load slower. Turn it on in <b>Select PHP Version → Extensions</b>.';

/* 4. can PHP write here? */
if(!is_writable($bb_dir))
  bb_page('Can’t install', '<h1>The website can’t unpack itself in this folder</h1><div class="e">PHP isn’t allowed to create files here. Nothing was installed.</div>'.
    '<p class="n">Use <code>beaglebrook-upload.zip</code> instead: upload it to <code>public_html</code> in File Manager, right-click it and choose <b>Extract</b>. Or ask your host to make the folder writable by PHP.</p>');

/* 5. unpack */
$bb_files = array(); $pos = 0; $len = strlen($bb_pack);
while($pos + 8 <= $len){
  $n = unpack('N', substr($bb_pack, $pos, 4)); $n = $n[1]; $rel = substr($bb_pack, $pos + 4, $n); $pos += 4 + $n;
  $m = unpack('N', substr($bb_pack, $pos, 4)); $m = $m[1]; $bb_files[$rel] = substr($bb_pack, $pos + 4, $m); $pos += 4 + $m;
}
unset($bb_pack);
if(!isset($bb_files['index.php'])) bb_damaged();
$done = array(); $kept = array();
foreach($bb_files as $rel=>$data){
  if($rel === 'index.php' || !preg_match('#^[A-Za-z0-9._/-]+$#', $rel) || strpos($rel, '..') !== false) continue;
  $path = $bb_dir.'/'.$rel;
  if(!is_dir(dirname($path)) && !@mkdir(dirname($path), 0755, true)) bb_page('Can’t install', '<h1>Couldn’t create the folder '.htmlspecialchars(dirname($rel)).'</h1><p class="n">Use <code>beaglebrook-upload.zip</code> instead, or ask your host to make this folder writable by PHP.</p>');
  if(strpos($rel, 'data/') === 0 && !in_array($rel, array('data/.htaccess', 'data/index.html'), true) && file_exists($path)){ $kept[] = $rel; continue; }   /* your data stays */
  if($rel === '.htaccess' && is_file($path)){
    $old = (string)file_get_contents($path);
    $start = strpos($old, '# Clean page addresses');
    if($start === false) $data = rtrim($old)."\n\n".$data;                              /* keep the host's own rules, ours after them */
    else {
      /* ours already: replace just our block with this version's, keeping any host rules before or after it */
      $end = strpos($old, '# End of Beagle Brook rules', $start);
      if($end !== false) $end += strlen('# End of Beagle Brook rules');
      elseif(($e = strpos($old, 'ExpiresByType text/css "access plus 30 days"', $start)) !== false && ($c = strpos($old, '</IfModule>', $e)) !== false) $end = $c + strlen('</IfModule>');
      if($end === false){ $kept[] = $rel; continue; }                                    /* edited by hand: leave it */
      $data = substr($old, 0, $start).rtrim($data).substr($old, $end);
      if($data === $old){ $kept[] = $rel; continue; }
    }
  }
  $tmp = $path.'.'.mt_rand(100000, 999999).'.tmp';
  if(@file_put_contents($tmp, $data) === false || !@rename($tmp, $path)){ @unlink($tmp); bb_page('Can’t install', '<h1>Couldn’t write '.htmlspecialchars($rel).'</h1><p class="n">Use <code>beaglebrook-upload.zip</code> instead, or ask your host to make this folder writable by PHP.</p>'); }
  @chmod($path, 0644);
  $done[] = $rel;
}

/* 6. the host's placeholder pages would be shown instead of the website */
$moved = array();
foreach(array('index.html', 'index.htm', 'default.php', 'default.html', 'default.htm') as $ph){
  if($ph !== $bb_self && is_file($bb_dir.'/'.$ph) && @rename($bb_dir.'/'.$ph, $bb_dir.'/'.$ph.'.old')) $moved[] = $ph;
}

/* 7. become the website's index.php (also when this file was uploaded under another name) */
$target = $bb_dir.'/index.php';
if($bb_self !== 'index.php' && is_file($target) && strpos((string)@file_get_contents($target, false, null, 0, 4000), 'BB_ROOT') === false) @rename($target, $target.'.old');
$tmp = $target.'.'.mt_rand(100000, 999999).'.tmp';
if(@file_put_contents($tmp, $bb_files['index.php']) === false || !@rename($tmp, $target)){ @unlink($tmp); bb_page('Couldn’t finish', '<h1>Couldn’t finish</h1><p class="n">The website files are unpacked, but index.php couldn’t be replaced. Use <code>beaglebrook-upload.zip</code> instead.</p>'); }
@chmod($target, 0644);
if($bb_self !== 'index.php') @unlink(__FILE__);
if(function_exists('opcache_invalidate')){ @opcache_invalidate($target, true); @opcache_invalidate(__FILE__, true); }
clearstatcache();

$html = '<h1>Your website is installed ✓</h1><p class="n">'.count($done).' files unpacked'.($kept ? ', '.count($kept).' of your existing files kept as they were' : '').'.</p>';
if($moved) $html .= '<p class="n">Your host’s placeholder page ('.htmlspecialchars(implode(', ', $moved)).') was renamed with <code>.old</code> on the end, so the website shows instead.</p>';
foreach($bb_warn as $w) $html .= '<div class="w">'.$w.'</div>';
$html .= (is_file($bb_dir.'/data/auth.php') ? '' : '<div class="w"><b>Do this now:</b> open the admin and create your sign-in. The first person to open it sets the username and password.</div>').
  '<p><a class="b" href="admin.php">Open the admin</a><a class="b" href="./">Open the website</a></p>'.
  '<p class="n" id="cu">Checking clean page addresses…</p>'.
  '<p class="n">Next, follow the <b>launch checklist</b> on the admin dashboard: your phone number and details, email (SMTP) with a test email, your payment details, the parents and your puppies.</p>'.
  '<script>var cu=document.getElementById("cu");fetch("rewrite-check",{cache:"no-store"}).then(function(r){return r.ok?r.json():{}}).then(function(j){cu.innerHTML=j.clean_urls?"Clean page addresses (like <code>/beagle-puppies-for-sale</code>) are <b>on</b> ✓":"Your host doesn’t support clean page addresses, so the site uses <code>index.php?path=…</code> addresses. Everything works, but clean addresses are better for Google: ask your host to enable <b>mod_rewrite</b>."}).catch(function(){cu.textContent="Your host doesn’t support clean page addresses. Everything works either way."});</script>';
bb_page('Website installed', $html);
__halt_compiler();
PHP;

$b64 = chunk_split(base64_encode($pack), 76, "\n");
@mkdir("$root/dist", 0755, true);
$logo = preg_replace('#<title>.*?</title>#s', '', trim((string)@file_get_contents("$root/assets/logo.svg")));
$logo = str_replace(["'", '\\'], ['&#39;', ''], $logo);          /* it sits inside a '…' PHP string in the installer */
file_put_contents("$root/dist/index.php", str_replace(['%%SHA256%%', '%%LOGO%%'], [hash('sha256', $pack), $logo], $stub).'BBB64:'."\n".$b64);
printf("dist/index.php: %d files, %.0f KB%s\n", count($files), filesize("$root/dist/index.php") / 1024, isset($files['data/auth.php']) ? ', with admin login' : '');

/* the same files as a zip, for hosts where uploading a zip and choosing "Extract" is easier */
if(class_exists('ZipArchive')){
  $zf = "$root/dist/beaglebrook-upload.zip"; @unlink($zf);
  $z = new ZipArchive();
  if($z->open($zf, ZipArchive::CREATE) === true){
    foreach($files as $rel=>$data) $z->addFromString($rel, $data);
    $z->close();
    printf("dist/beaglebrook-upload.zip: %d files, %.0f KB\n", count($files), filesize($zf) / 1024);
  }
}
