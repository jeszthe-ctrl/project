<?php
/* The guides and blog posts a fresh install starts with (edited in Admin → Guides & blog).
   Each one targets a group of searches from the keyword research; "group" decides which
   guides are linked together as related reading. */
if(!defined('BB_ROOT')){ http_response_code(404); exit; }

$all = array_merge(
  require BB_ROOT.'/inc/articles/breed.php',
  require BB_ROOT.'/inc/articles/buying.php',
  require BB_ROOT.'/inc/articles/mixes.php',
  require BB_ROOT.'/inc/articles/care.php',
  require BB_ROOT.'/inc/articles/questions.php',
  require BB_ROOT.'/inc/articles/more_breed.php',
  require BB_ROOT.'/inc/articles/more_care.php',
  require BB_ROOT.'/inc/articles/culture.php',
  require BB_ROOT.'/inc/articles/blog.php'
);

/* the quick answer shown under each heading */
$answers = require BB_ROOT.'/inc/articles/answers.php';
foreach($all as $i=>$a) if(!isset($a['answer']) && isset($answers[$a['slug']])) $all[$i]['answer'] = $answers[$a['slug']];
return $all;
