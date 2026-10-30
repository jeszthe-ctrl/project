<?php
/* Guides and articles (edited in Admin → Guides). Split over several files only to keep them readable;
   content-guides-more-*.php add extra sections and FAQs to the guides they name. */
if(!defined('GB_ROOT')){ http_response_code(404); exit; }

$guides = array_merge(
  require GB_ROOT.'/inc/content-guides-1.php',
  require GB_ROOT.'/inc/content-guides-2.php',
  require GB_ROOT.'/inc/content-guides-3.php',
  require GB_ROOT.'/inc/content-guides-4.php',
  require GB_ROOT.'/inc/content-guides-5.php'
);
$more = array_merge(
  require GB_ROOT.'/inc/content-guides-more-1.php',
  require GB_ROOT.'/inc/content-guides-more-2.php',
  require GB_ROOT.'/inc/content-guides-more-3.php'
);
foreach($guides as &$g){
  if(!isset($more[$g['slug']])) continue;
  /* the extra sections go before a closing {calculator}, if the guide ends with one */
  $body = rtrim($g['body']);
  $calc = preg_match('/\n(\{calculator(?::\d+)?\})$/', $body, $m) ? $m[1] : '';
  if($calc) $body = substr($body, 0, -strlen($calc));
  $g['body'] = rtrim($body)."\n".$more[$g['slug']]['body'].($calc ? "\n".$calc."\n" : '');
  $g['faqs'] = array_merge($g['faqs'] ?? [], $more[$g['slug']]['faqs'] ?? []);
}
unset($g);
return $guides;
