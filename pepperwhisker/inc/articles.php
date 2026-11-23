<?php
/* The guides a fresh install starts with (edited in Admin → Guides).
   Each one targets a group of searches from the keyword research (docs/keyword-map.md);
   "group" decides which topic a guide is listed under and which guides link to each other. */
if(!defined('PW_ROOT')){ http_response_code(404); exit; }

return array_merge(
  require PW_ROOT.'/inc/articles/breeds.php',
  require PW_ROOT.'/inc/articles/colors.php',
  require PW_ROOT.'/inc/articles/grooming.php',
  require PW_ROOT.'/inc/articles/health.php',
  require PW_ROOT.'/inc/articles/mixes.php',
  require PW_ROOT.'/inc/articles/puppies.php',
  require PW_ROOT.'/inc/articles/spanish.php'
);
