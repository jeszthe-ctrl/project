<?php
/* Guides and articles (edited in Admin → Guides). Split over several files only to keep them readable. */
if(!defined('GB_ROOT')){ http_response_code(404); exit; }

return array_merge(
  require GB_ROOT.'/inc/content-guides-1.php',
  require GB_ROOT.'/inc/content-guides-2.php',
  require GB_ROOT.'/inc/content-guides-3.php'
);
