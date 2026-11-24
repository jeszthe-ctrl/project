<?php
/* The guides and blog posts a fresh install starts with (edited in Admin → Guides & blog).
   Each one targets a group of searches from the keyword research (see docs/keyword-map.md);
   "group" decides which guides are linked together as related reading. */
if(!defined('BB_ROOT')){ http_response_code(404); exit; }

$all = array_merge(
  require BB_ROOT.'/inc/articles/breed.php',
  require BB_ROOT.'/inc/articles/colors.php',
  require BB_ROOT.'/inc/articles/grooming.php',
  require BB_ROOT.'/inc/articles/mixes.php',
  require BB_ROOT.'/inc/articles/buying.php',
  require BB_ROOT.'/inc/articles/care.php',
  require BB_ROOT.'/inc/articles/blog.php'
);

/* full-length versions of the most-searched guides replace the short ones with the same slug */
$full = array_column(array_merge(require BB_ROOT.'/inc/articles/pillars_a.php', require BB_ROOT.'/inc/articles/pillars_b.php'), null, 'slug');
foreach($all as $i=>$a) if(isset($full[$a['slug']])) $all[$i] = $full[$a['slug']];
return $all;
