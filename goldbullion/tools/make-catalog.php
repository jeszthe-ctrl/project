<?php
/* Builds inc/catalog.json.gz — the products a fresh install starts with — from a catalog CSV.
   Run:  php tools/make-catalog.php path/to/catalog.csv
   Products keep the CSV's published/draft status; drafts appear once you price and publish
   them in Admin → Pricing. */
if(PHP_SAPI !== 'cli'){ http_response_code(404); exit; }
define('GB_ROOT', dirname(__DIR__));
require GB_ROOT.'/inc/store.php';
require GB_ROOT.'/inc/catalog.php';

$file = $argv[1] ?? '';
if(!is_file($file)){ fwrite(STDERR, "Usage: php tools/make-catalog.php catalog.csv\n"); exit(1); }
$fh = fopen($file, 'r');
if(fread($fh, 3) !== "\xEF\xBB\xBF") rewind($fh);
$head = fgetcsv($fh, 0, ',', '"', '\\');
$out = []; $slugs = [];
while(($row = fgetcsv($fh, 0, ',', '"', '\\')) !== false){
  if(count($row) !== count($head)) continue;
  $p = catalog_product(array_combine($head, $row));
  if($p['name'] === '') continue;
  $base = slugify($p['name']) ?: strtolower($p['id']); $slug = $base; $n = 2;
  while(isset($slugs[$slug])) $slug = $base.'-'.$n++;
  $slugs[$slug] = 1; $p['slug'] = $slug;
  $out[] = $p;
}
file_put_contents(GB_ROOT.'/inc/catalog.json.gz', gzencode(json_encode($out, JSON_UNESCAPED_SLASHES|JSON_UNESCAPED_UNICODE), 9));
$cats = [];
foreach($out as $p) foreach($p['categories'] as $c) $cats[$c] = ($cats[$c] ?? 0) + 1;
printf("%d products → inc/catalog.json.gz (%.0f KB), %d categories, %d drafts\n", count($out), filesize(GB_ROOT.'/inc/catalog.json.gz') / 1024, count($cats), count(array_filter($out, fn($p)=>$p['hidden'])));
