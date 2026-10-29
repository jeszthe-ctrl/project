<?php
/* Builds dist/beaglebrook-upload.zip: the whole site, ready to upload and "Extract" in your
   host's File Manager (public_html). Your data (puppies, inquiries, sign-in) lives in data/ on
   the server and is never in the zip, so uploading a newer zip later never overwrites it.

   Run:  php tools/build-zip.php [admin-password] [admin-username]
   With a password, a fresh install also gets that admin sign-in (username "admin" unless given);
   otherwise admin.php asks you to create one on the first visit. */
if(PHP_SAPI !== 'cli'){ http_response_code(404); exit; }
if(!class_exists('ZipArchive')){ fwrite(STDERR, "PHP's zip extension is needed.\n"); exit(1); }

$root = dirname(__DIR__);
$skip = ['#^data/(?!\.htaccess$|index\.html$)#', '#^assets/uploads/(?!\.htaccess$|\.gitkeep$)#', '#^dist/#', '#^tools/#', '#^docs/#', '#(^|/)\.git#', '#\.DS_Store$#'];
@mkdir("$root/dist", 0755, true);
$zipfile = "$root/dist/beaglebrook-upload.zip";
@unlink($zipfile);
$zip = new ZipArchive();
if($zip->open($zipfile, ZipArchive::CREATE) !== true){ fwrite(STDERR, "Couldn't create $zipfile\n"); exit(1); }
$n = 0;
$it = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($root, FilesystemIterator::SKIP_DOTS));
foreach($it as $file){
  $rel = str_replace('\\', '/', substr($file->getPathname(), strlen($root) + 1));
  if($rel === '.gitignore') continue;
  foreach($skip as $re) if(preg_match($re, $rel)) continue 2;
  $zip->addFile($file->getPathname(), $rel); $n++;
}
if(isset($argv[1]) && $argv[1] !== ''){
  if(strlen($argv[1]) < 10){ fwrite(STDERR, "Use a password of at least 10 characters.\n"); exit(1); }
  $user = $argv[2] ?? 'admin';
  if(!preg_match('/^[A-Za-z0-9._@-]{3,40}$/', $user)){ fwrite(STDERR, "Use a username of 3-40 letters or numbers.\n"); exit(1); }
  $zip->addFromString('data/auth.php', "<?php http_response_code(404); exit; ?>\n".json_encode(['user'=>$user, 'hash'=>password_hash($argv[1], PASSWORD_DEFAULT), 'key'=>bin2hex(random_bytes(16)), 'fails'=>[]]));
  echo "Includes the admin sign-in for \"$user\". Don't share this zip.\n";
}
$zip->close();
echo "Built dist/beaglebrook-upload.zip ($n files, ".round(filesize($zipfile) / 1024)." KB)\n";
