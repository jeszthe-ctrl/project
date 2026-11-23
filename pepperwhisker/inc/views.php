<?php
/* The website's page templates: layout, page bodies and the reusable blocks. */
if(!defined('PW_ROOT')){ http_response_code(404); exit; }

/* ---------------- small icons (inline SVG, drawn with currentColor) ---------------- */
function icon($name, $class='ic'){
  $p = [
    'check'  => '<path d="M5 12.5l4.2 4.2L19 7"/>',
    'book'   => '<path d="M4 5.5A2.5 2.5 0 016.5 3H20v15H6.5A2.5 2.5 0 004 20.5zM4 20.5A2.5 2.5 0 006.5 23H20"/>',
    'scissors'=>'<circle cx="6" cy="6.5" r="2.5"/><circle cx="6" cy="17.5" r="2.5"/><path d="M8 8l12 9M8 16l12-9"/>',
    'heart'  => '<path d="M12 20s-7.5-4.6-7.5-10.1A4.3 4.3 0 0112 7.3a4.3 4.3 0 017.5 2.6C19.5 15.4 12 20 12 20z"/>',
    'palette'=> '<path d="M12 3.5a8.5 8.5 0 100 17c1.2 0 1.8-.8 1.8-1.7 0-1.2-1-1.5-1-2.6 0-1 .8-1.7 1.8-1.7h2.2a3.7 3.7 0 003.7-3.7c0-4.2-3.8-7.3-8.5-7.3z"/><circle cx="8" cy="11" r="1"/><circle cx="11" cy="7.5" r="1"/><circle cx="15.5" cy="8.5" r="1"/>',
    'ruler'  => '<rect x="2.5" y="8" width="19" height="8" rx="1.5"/><path d="M6 8v3M9.5 8v4M13 8v3M16.5 8v4"/>',
    'mix'    => '<circle cx="9" cy="12" r="5.5"/><circle cx="15" cy="12" r="5.5"/>',
    'home'   => '<path d="M3.5 11L12 4l8.5 7M6 9.5V20h12V9.5"/>',
    'mail'   => '<rect x="3" y="5.5" width="18" height="13" rx="2"/><path d="M3.5 7l8.5 6 8.5-6"/>',
    'menu'   => '<path d="M4 7h16M4 12h16M4 17h16"/>',
    'arrow'  => '<path d="M5 12h14M13 6l6 6-6 6"/>',
    'info'   => '<circle cx="12" cy="12" r="8.5"/><path d="M12 11v5.5M12 7.6v.2"/>',
    'shield' => '<path d="M12 3l7.5 3v5.5c0 4.6-3.2 8.2-7.5 9.5-4.3-1.3-7.5-4.9-7.5-9.5V6z"/><path d="M8.5 12l2.5 2.5 4.5-5"/>',
  ][$name] ?? '';
  return '<svg class="'.h($class).'" viewBox="0 0 24 24" aria-hidden="true" focusable="false">'.$p.'</svg>';
}
function group_icon($g){ return ['breeds'=>'ruler', 'colors'=>'palette', 'grooming'=>'scissors', 'health'=>'heart', 'mixes'=>'mix', 'puppies'=>'home'][$g] ?? 'book'; }

/* the logo: a schnauzer head in profile, with eyebrow and beard */
function logo_svg($class='logo-mark'){
  return '<svg class="'.h($class).'" viewBox="0 0 48 48" aria-hidden="true" focusable="false"><circle cx="24" cy="24" r="23" fill="var(--brand)"/>'
    .'<path fill="#f4f1ec" d="M15 37c-1.5-5-1.2-10.5 1-15l1.6-8.5 4 6.2c2.6-.8 5.6-.9 8.2-.3l7.4 1.6c1.6.3 2.4 1.5 2.2 2.9l-.3 1.7c-.2 1.1-1.2 1.8-2.3 1.7l-3.2-.2c.3 2.3-.5 5-2.6 6.4-2.3 1.6-5.5 1.6-7.6-.2-.9 2-1.5 3.6-1.6 3.7z"/>'
    .'<path fill="#8a9399" d="M30.4 26.1l6.5.4c-.6 3.4-3.9 5.6-7.3 4.7 1-1.4 1.1-3.5.8-5.1z"/>'
    .'<path d="M24.5 21.2l7-1.2" stroke="#2c3337" stroke-width="1.6" stroke-linecap="round"/><circle cx="27" cy="23.1" r="1.15" fill="#2c3337"/><circle cx="39.3" cy="22" r="1.3" fill="#2c3337"/></svg>';
}

/* a standing schnauzer in profile, 100 × 80; the ground is at y=74 and the withers 44 units above it */
const DOG_PATH = 'M14.5 30L10 19.5L13.5 18.5L19 28.5L60 28C63.5 27 64.5 22 65 16L66 7.5L70.5 13L80 13L84 16L95 17L97.5 20.5L96.5 24.5L95.5 32L89 36.5L80.5 32.5L74 35L74.5 46L72.5 51L73 63L75.5 71L77 74L63.5 74L65 65L65.5 56L60 53.5L40 54.5L34.5 52L35.5 61L34 68L36.5 74L24 74L24.5 68L20 58L15.5 46L14 38Z';

/* the three schnauzers side by side at their real relative heights */
function size_chart_svg($caption=true){
  $dogs = [['Miniature', 14, '12–14 in · 11–20 lb', 'var(--salt-dog)', 'miniature-schnauzer'], ['Standard', 19.5, '17.5–19.5 in · 30–50 lb', 'var(--pepper-dog)', 'standard-schnauzer'], ['Giant', 27.5, '23.5–27.5 in · 55–85 lb', 'var(--black-dog)', 'giant-schnauzer']];
  $k = 7.2;
  $ground = (int)ceil(67 * 27.5 * $k / 44) + 8;   /* the tallest dog's ear tip (y=7.5) fits inside the frame */
  $x = 10; $o = '';
  foreach($dogs as [$name, $in, $label, $fill, $slug]){
    $sc = $in * $k / 44;
    $w = 100 * $sc;
    $o .= '<a href="'.h(url('guides/'.$slug)).'"><g transform="translate('.round($x, 1).' '.round($ground - 74 * $sc, 1).') scale('.round($sc, 3).')"><path d="'.DOG_PATH.'" fill="'.$fill.'"/><path d="M71 12.5L82 11.5" stroke="var(--salt)" stroke-width="'.round(2.2 / $sc, 2).'" stroke-linecap="round" opacity=".55"/></g>'
        .'<text x="'.round($x + $w / 2, 1).'" y="'.($ground + 34).'" text-anchor="middle" class="sc-n">'.$name.'</text><text x="'.round($x + $w / 2, 1).'" y="'.($ground + 62).'" text-anchor="middle" class="sc-d">'.$label.'</text></a>';
    $x += $w + 22;
  }
  return '<figure class="sizechart"><svg viewBox="0 0 '.(int)ceil($x - 12).' '.($ground + 72).'" role="img" aria-label="Miniature, standard and giant schnauzer drawn to scale: 14, 19.5 and 27.5 inches at the shoulder"><line x1="0" y1="'.$ground.'.5" x2="'.(int)ceil($x).'" y2="'.$ground.'.5" class="sc-g"/>'.$o.'</svg>'
    .($caption ? '<figcaption>Drawn to scale at the top of each breed’s height range.</figcaption>' : '').'</figure>';
}

/* ---------------- blocks used inside page text: a line with only {size_chart} or {author_box} ---------------- */
function author_box(){
  global $CFG;
  if($CFG['author_name'] === '') return '';
  $img = $CFG['author_photo'] !== '' ? img_tag($CFG['author_photo'], $CFG['author_name'], '96px') : '';
  return '<div class="author">'.$img.'<div><p class="eyebrow">Written by</p><p><strong>'.h($CFG['author_name']).'</strong></p>'.($CFG['author_bio'] !== '' ? rich($CFG['author_bio']) : '').'</div></div>';
}
function page_blocks(){ return ['{size_chart}'=>size_chart_svg(), '{author_box}'=>author_box()]; }

/* ---------------- cards and lists ---------------- */
function article_card($a){
  $o = '<article class="acard"><a href="'.h(url(article_path($a))).'">';
  if(($a['image'] ?? '') !== '') $o .= img_tag($a['image'], $a['image_alt'] ?? $a['title'], '(max-width:600px) 100vw, 360px');
  else $o .= '<span class="acard-ic">'.icon(group_icon($a['group'] ?? '')).'</span>';
  return $o.'<h3>'.h($a['title']).'</h3><p>'.h(fill($a['excerpt'] ?? plain($a['body'], 140))).'</p><span class="more">Read guide '.icon('arrow').'</span></a></article>';
}
/* related guides: same topic first, then the breed guides */
function related_articles($a, $n=4){
  global $SITE;
  $pool = array_filter($SITE['articles'], fn($x)=>$x['slug'] !== $a['slug'] && empty($x['draft']) && ($x['lang'] ?? 'en') === 'en');
  usort($pool, fn($x, $y)=>(($y['group'] === $a['group']) <=> ($x['group'] === $a['group'])) ?: (($y['group'] === 'breeds') <=> ($x['group'] === 'breeds')));
  return array_slice(array_values($pool), 0, $n);
}
function topic_link($g){ return isset(GUIDE_GROUPS[$g]) ? '<a href="'.h(url(GUIDE_GROUPS[$g][1])).'">'.h(GUIDE_GROUPS[$g][0]).'</a>' : ''; }

/* ---------------- forms ---------------- */
function err($k){ global $errors; return isset($errors[$k]) ? '<span class="ferr" id="e-'.h($k).'">'.h($errors[$k]).'</span>' : ''; }
function val($k){ global $old; return h($old[$k] ?? ''); }
function field($name, $label, $type='text', $req=true, $attrs=''){
  global $errors;
  $bad = isset($errors[$name]);
  return '<label class="f'.($bad ? ' f-bad' : '').'"><span>'.h($label).($req ? ' <b aria-hidden="true">*</b>' : ' <i>(optional)</i>').'</span>'
    .'<input type="'.$type.'" name="'.h($name).'" value="'.val($name).'"'.($req ? ' required' : '').($bad ? ' aria-invalid="true" aria-describedby="e-'.h($name).'"' : '').' '.$attrs.'>'.err($name).'</label>';
}
function spam_fields(){
  return '<input type="hidden" name="token" value="'.h(form_token()).'"><div class="hp" aria-hidden="true"><label>Leave this empty<input type="text" name="website" tabindex="-1" autocomplete="off"></label></div>';
}
function contact_form(){
  global $errors;
  $o = $errors ? '<div class="alert alert-err" role="alert"><strong>Please check the form.</strong> '.h($errors['form'] ?? 'Some details are missing or need correcting below.').'</div>' : '';
  $o .= '<form class="form" method="post" action="'.h(url('contact')).'#form" id="form" novalidate><input type="hidden" name="action" value="contact">'.spam_fields();
  $o .= '<div class="frow">'.field('name', 'Your name', 'text', false, 'autocomplete="name" maxlength="120"').field('email', 'Your email', 'email', true, 'autocomplete="email" maxlength="160"').'</div>';
  $bad = isset($errors['message']);
  $o .= '<label class="f'.($bad ? ' f-bad' : '').'"><span>Your message <b aria-hidden="true">*</b></span><textarea name="message" rows="6" required maxlength="5000"'.($bad ? ' aria-invalid="true" aria-describedby="e-message"' : '').'>'.val('message').'</textarea>'.err('message').'</label>';
  return $o.'<button class="btn" type="submit">Send message</button><p class="fnote">We only use your email to reply. <a href="'.h(url('privacy-policy')).'">Privacy</a></p></form>';
}

/* ---------------- structured data (JSON-LD) ---------------- */
function org_ld(){
  global $CFG;
  $base = rtrim($CFG['domain'], '/').'/';
  $same = array_values(array_filter([$CFG['facebook'], $CFG['instagram'], $CFG['pinterest'], $CFG['youtube']]));
  return array_filter(['@type'=>'Organization', '@id'=>$base.'#org', 'name'=>$CFG['brand'], 'url'=>$base, 'logo'=>$base.'assets/logo-512.png',
    'email'=>$CFG['email'], 'description'=>fill($CFG['footer_blurb']), 'sameAs'=>$same ?: null]);
}
function author_ld(){
  global $CFG;
  $base = rtrim($CFG['domain'], '/').'/';
  return $CFG['author_name'] !== '' ? ['@type'=>'Person', 'name'=>$CFG['author_name'], 'url'=>abs_url('about')] : ['@type'=>'Organization', 'name'=>$CFG['brand'], 'url'=>$base];
}
function faq_ld($faqs){ return ['@type'=>'FAQPage', 'mainEntity'=>array_map(fn($q)=>['@type'=>'Question', 'name'=>$q[0], 'acceptedAnswer'=>['@type'=>'Answer', 'text'=>$q[1]]], $faqs)]; }
function structured_data(){
  global $page, $crumbs, $art, $info, $CFG, $h1, $desc, $canonical, $og_image, $topic;
  $base = rtrim($CFG['domain'], '/').'/';
  $g = [org_ld()];
  if($page === 'home'){ $g[] = ['@type'=>'WebSite', '@id'=>$base.'#site', 'name'=>$CFG['brand'], 'url'=>$base, 'publisher'=>['@id'=>$base.'#org'], 'inLanguage'=>'en']; $g[] = faq_ld(home_faqs()); }
  if(count($crumbs) > 1){
    $items = [];
    foreach($crumbs as $i=>[$name, $path]) $items[] = ['@type'=>'ListItem', 'position'=>$i + 1, 'name'=>$name, 'item'=>abs_url($path)];
    $g[] = ['@type'=>'BreadcrumbList', 'itemListElement'=>$items];
  }
  if($page === 'article'){
    $g[] = array_filter(['@type'=>$art['type'] === 'blog' ? 'BlogPosting' : 'Article', 'headline'=>mb_substr($h1, 0, 110), 'description'=>$desc,
      'image'=>abs_img($og_image !== '' ? $og_image : share_image()), 'datePublished'=>$art['date'], 'dateModified'=>$art['updated'] ?? $art['date'],
      'inLanguage'=>$art['lang'] ?? 'en', 'author'=>author_ld(), 'publisher'=>['@id'=>$base.'#org'], 'mainEntityOfPage'=>$canonical]);
  }
  if($page === 'topic'){
    $list = topic_articles($topic);
    $g[] = ['@type'=>'CollectionPage', 'name'=>$h1, 'description'=>$desc, 'url'=>$canonical, 'mainEntity'=>['@type'=>'ItemList',
      'itemListElement'=>array_map(fn($a, $i)=>['@type'=>'ListItem', 'position'=>$i + 1, 'url'=>abs_url(article_path($a)), 'name'=>$a['title']], $list, array_keys($list))]];
  }
  $faq_text = $page === 'article' ? $art['body'] : ($page === 'info' ? $info['body'] : '');
  if($faq_text !== '' && ($faqs = text_faqs($faq_text))) $g[] = faq_ld($faqs);
  return '<script type="application/ld+json">'.json_encode(['@context'=>'https://schema.org', '@graph'=>$g], JSON_UNESCAPED_SLASHES|JSON_UNESCAPED_UNICODE|JSON_HEX_TAG).'</script>';
}
function share_image(){ global $CFG; return $CFG['share_photo'] !== '' && is_file(PW_ROOT.'/'.$CFG['share_photo']) ? $CFG['share_photo'] : 'assets/share.jpg'; }

/* ---------------- layout ---------------- */
function nav_items(){
  return [['guides/miniature-schnauzer', 'Miniature'], ['guides/standard-schnauzer', 'Standard'], ['guides/giant-schnauzer', 'Giant'],
          ['schnauzer-grooming', 'Grooming'], ['schnauzer-health', 'Health'], ['schnauzer-mixes', 'Mixes'], ['guides', 'All guides']];
}

function render_page(){
  global $CFG, $page, $title, $desc, $canonical, $noindex, $og_type, $og_image, $crumbs, $lang, $BASE, $PATH;
  $css_v = @filemtime(PW_ROOT.'/assets/style.css');
  $img = abs_img($og_image !== '' ? $og_image : share_image());
  header('Content-Type: text/html; charset=utf-8');
  ?><!doctype html>
<html lang="<?= h($lang) ?>">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<base href="<?= h($BASE) ?>">
<title><?= h($title) ?></title>
<meta name="description" content="<?= h($desc) ?>">
<?php if($noindex): ?><meta name="robots" content="noindex, follow">
<?php else: ?><link rel="canonical" href="<?= h($canonical) ?>">
<meta name="robots" content="max-image-preview:large">
<?php endif; ?>
<meta property="og:site_name" content="<?= h($CFG['brand']) ?>">
<meta property="og:type" content="<?= h($og_type) ?>">
<meta property="og:title" content="<?= h($title) ?>">
<meta property="og:description" content="<?= h($desc) ?>">
<meta property="og:url" content="<?= h($canonical) ?>">
<meta property="og:image" content="<?= h($img) ?>">
<meta name="twitter:card" content="summary_large_image">
<meta name="theme-color" content="#2f3a40">
<link rel="icon" href="assets/favicon.svg" type="image/svg+xml">
<link rel="icon" href="favicon.ico" sizes="32x32">
<link rel="apple-touch-icon" href="assets/apple-touch-icon.png">
<?php if($CFG['gsc_verification'] !== ''): ?><meta name="google-site-verification" content="<?= h($CFG['gsc_verification']) ?>">
<?php endif; if($CFG['bing_verification'] !== ''): ?><meta name="msvalidate.01" content="<?= h($CFG['bing_verification']) ?>">
<?php endif; ?>
<link rel="stylesheet" href="assets/style.css?v=<?= (int)$css_v ?>">
<?= structured_data() ?>
<?php if(preg_match('/^G-[A-Z0-9]{4,}$/', $CFG['ga4_id'])): ?>
<script async src="https://www.googletagmanager.com/gtag/js?id=<?= h($CFG['ga4_id']) ?>"></script>
<script>window.dataLayer=window.dataLayer||[];function gtag(){dataLayer.push(arguments)}gtag('js',new Date());gtag('config','<?= h($CFG['ga4_id']) ?>');</script>
<?php endif; if(adsense_id() !== '' && !in_array($page, ['contact', 'notfound'], true)): ?>
<script async src="https://pagead2.googlesyndication.com/pagead/js/adsbygoogle.js?client=<?= h(adsense_id()) ?>" crossorigin="anonymous"></script>
<?php endif; ?>
</head>
<body class="pg-<?= h($page) ?>">
<a class="skip" href="#main">Skip to content</a>
<header class="site-h">
  <div class="wrap hrow">
    <a class="brand" href="./" aria-label="<?= h($CFG['brand']) ?> home"><?= logo_svg() ?><span class="brand-t"><?= h($CFG['brand']) ?><small>Schnauzer guides</small></span></a>
    <button class="navbtn" type="button" aria-expanded="false" aria-controls="nav"><?= icon('menu') ?><span>Menu</span></button>
    <nav id="nav" class="nav" aria-label="Main">
      <?php foreach(nav_items() as [$path, $label]): ?><a href="<?= h(url($path)) ?>"<?= $PATH === $path ? ' aria-current="page"' : '' ?>><?= h($label) ?></a><?php endforeach; ?>
    </nav>
  </div>
</header>
<?php if($crumbs && count($crumbs) > 1): ?>
<nav class="crumbs wrap" aria-label="Breadcrumb"><ol><?php foreach($crumbs as $i=>[$name, $path]): ?><li><?php if($i < count($crumbs) - 1): ?><a href="<?= h(url($path)) ?>"><?= h($name) ?></a><?php else: ?><span aria-current="page"><?= h($name) ?></span><?php endif; ?></li><?php endforeach; ?></ol></nav>
<?php endif; ?>
<main id="main">
<?php
  $fn = 'view_'.str_replace(['-', '.'], '_', $page);
  if(function_exists($fn)) $fn(); else view_notfound();
?>
</main>
<?php footer(); ?>
<script>
(function(){var b=document.querySelector('.navbtn'),n=document.getElementById('nav');
if(b)b.addEventListener('click',function(){var o=n.classList.toggle('open');b.setAttribute('aria-expanded',o)});
document.querySelectorAll('form.form').forEach(function(f){f.addEventListener('submit',function(){var s=f.querySelector('[type=submit]');setTimeout(function(){s.disabled=true;s.textContent='Sending…'},10)})});
var bad=document.querySelector('.f-bad input,.f-bad textarea');if(bad)bad.focus();})();
</script>
</body>
</html>
<?php
}

function footer(){
  global $CFG;
  ?>
<footer class="site-f">
  <div class="wrap fgrid">
    <div class="fcol fcol-brand">
      <a class="brand brand-f" href="./"><?= logo_svg() ?><span class="brand-t"><?= h($CFG['brand']) ?></span></a>
      <p><?= h(fill($CFG['footer_blurb'])) ?></p>
      <p><a href="mailto:<?= h($CFG['email']) ?>"><?= h($CFG['email']) ?></a></p>
      <?php $soc = array_filter(['Facebook'=>$CFG['facebook'], 'Instagram'=>$CFG['instagram'], 'Pinterest'=>$CFG['pinterest'], 'YouTube'=>$CFG['youtube']]); if($soc): ?>
      <p class="social"><?php foreach($soc as $n=>$u): ?><a href="<?= h($u) ?>" rel="noopener me" target="_blank"><?= h($n) ?></a><?php endforeach; ?></p>
      <?php endif; ?>
    </div>
    <div class="fcol"><h2>The breeds</h2><ul>
      <?php foreach(['miniature-schnauzer'=>'Miniature schnauzer', 'standard-schnauzer'=>'Standard schnauzer', 'giant-schnauzer'=>'Giant schnauzer', 'schnauzer-sizes'=>'Schnauzer size chart', 'schnauzer-colors'=>'Schnauzer colors', 'schnauzer-miniatura'=>'En español'] as $s=>$l): if(article($s, 'guide')): ?>
      <li><a href="<?= h(url('guides/'.$s)) ?>"><?= h($l) ?></a></li>
      <?php endif; endforeach; ?>
    </ul></div>
    <div class="fcol"><h2>Topics</h2><ul>
      <?php foreach(GUIDE_GROUPS as $g=>[$label, $slug]): ?><li><a href="<?= h(url($slug)) ?>"><?= h($label) ?></a></li><?php endforeach; ?>
      <li><a href="<?= h(url('guides')) ?>"><strong>All guides</strong></a></li>
    </ul></div>
    <div class="fcol"><h2><?= h($CFG['brand']) ?></h2><ul>
      <?php foreach(['about', 'editorial-policy', 'advertising-disclosure', 'privacy-policy', 'terms-of-use'] as $s): $pg = info_page($s); if($pg): ?>
      <li><a href="<?= h(url($s)) ?>"><?= h($pg['menu'] ?: $pg['title']) ?></a></li>
      <?php endif; endforeach; ?>
      <li><a href="<?= h(url('contact')) ?>">Contact</a></li>
    </ul></div>
  </div>
  <div class="wrap fbottom"><p>© <?= date('Y') ?> <?= h($CFG['brand']) ?>. Guides are general information, not veterinary advice.</p>
    <p>We don’t breed or sell dogs.</p></div>
</footer>
<?php
}
/* "Giant Schnauzer: Size, Temperament…" → "Giant Schnauzer" for compact lists */
function short_title($t){ return preg_match('/^([^:]+?\?)\s/u', $t, $m) ? $m[1] : preg_split('/:\s|\s—\s|\s\(/u', $t, 2)[0]; }

/* ---------------- page bodies ---------------- */
function page_head($lede='', $class=''){
  global $h1;
  echo '<header class="phead wrap '.$class.'"><h1>'.h($h1).'</h1>'.($lede !== '' ? '<div class="lede">'.rich($lede).'</div>' : '').'</header>';
}

function home_faqs(){
  return [
    ['What are the three types of schnauzer?', 'The miniature schnauzer (12–14 inches, 11–20 lb), the standard schnauzer (17.5–19.5 inches, 30–50 lb) and the giant schnauzer (23.5–27.5 inches, 55–85 lb). They are three separate breeds that share a German origin, a beard and a wiry, low-shedding coat.'],
    ['Which schnauzer is best for families?', 'The miniature schnauzer suits most families: it is friendly, sturdy and adaptable. The standard suits active families, and the giant is best with experienced owners who have time for daily exercise and training.'],
    ['Do schnauzers shed?', 'Very little. Their harsh, wiry coat holds loose hair until it is brushed or clipped, which is why schnauzers are popular with people with mild allergies. They need brushing a few times a week and a haircut every 5 to 8 weeks.'],
    ['How long do schnauzers live?', 'Miniature schnauzers usually live 12 to 15 years, standards 13 to 16 and giants about 10 to 15.'],
    ['What is a standard schnauzer?', 'The standard schnauzer is the original, medium-size schnauzer that the miniature and giant were bred from: 17.5 to 19.5 inches tall and about 30 to 50 pounds. It is often called the regular or medium schnauzer.'],
  ];
}

function view_home(){
  global $CFG;
  $breeds = [
    ['miniature-schnauzer', 'Miniature schnauzer', '12–14 in · 11–20 lb', 'Friendly, adaptable and the most popular schnauzer. Great for families, seniors and apartments.'],
    ['standard-schnauzer', 'Standard schnauzer', '17.5–19.5 in · 30–50 lb', 'The medium-size original: clever, sporty and protective. The "regular" schnauzer.'],
    ['giant-schnauzer', 'Giant schnauzer', '23.5–27.5 in · 55–85 lb', 'A powerful working dog and devoted guardian for experienced, active owners.'],
  ];
  ?>
  <section class="hero"><div class="wrap hero-in">
    <div class="hero-t">
      <p class="eyebrow"><?= icon('book') ?><?= h($CFG['tagline']) ?></p>
      <h1><?= h($CFG['hero_title']) ?></h1>
      <p class="lede"><?= h($CFG['hero_lede']) ?></p>
      <p class="hero-b"><a class="btn" href="<?= h(url('guides')) ?>">Browse all guides</a><a class="btn btn-ghost" href="<?= h(url('guides/schnauzer-sizes')) ?>"><?= icon('ruler') ?>Size chart</a></p>
    </div>
    <div class="hero-art"><?= $CFG['hero_photo'] !== '' && is_file(PW_ROOT.'/'.$CFG['hero_photo']) ? img_tag($CFG['hero_photo'], 'A schnauzer', '(max-width:900px) 100vw, 520px', true, 'hero-img') : size_chart_svg(false) ?></div>
  </div></section>

  <section class="sec"><div class="wrap narrow home-intro">
    <h2 class="sec-h">What is a schnauzer?</h2>
    <p>The schnauzer is a bearded, wire-coated dog from Germany, named after the German word <em>Schnauze</em>, “snout”. There isn’t one schnauzer breed but three: the <a href="<?= h(url('guides/miniature-schnauzer')) ?>">miniature schnauzer</a>, the <a href="<?= h(url('guides/standard-schnauzer')) ?>">standard schnauzer</a> (the original, often called the regular or medium schnauzer) and the <a href="<?= h(url('guides/giant-schnauzer')) ?>">giant schnauzer</a>. All three share the bushy eyebrows, beard and low-shedding coat, and all are smart, loyal and alert watchdogs, but they differ a lot in size, energy and how much training they need.</p>
    <p>Schnauzers come in salt and pepper or black (miniatures also in black and silver), live about 12 to 16 years depending on size, and need brushing a few times a week and a haircut every 5 to 8 weeks. Read the full <a href="<?= h(url('guides/schnauzer-dog-breed')) ?>">schnauzer dog breed guide</a> to compare them.</p>
  </div></section>

  <section class="sec"><div class="wrap">
    <h2 class="sec-h">The three schnauzers</h2>
    <div class="bgrid"><?php foreach($breeds as [$slug, $name, $size, $text]): ?>
      <a class="bcard" href="<?= h(url('guides/'.$slug)) ?>"><h3><?= h($name) ?></h3><p class="bsize"><?= icon('ruler') ?><?= h($size) ?></p><p><?= h($text) ?></p><span class="more">Read the guide <?= icon('arrow') ?></span></a>
    <?php endforeach; ?></div>
    <p class="center"><a href="<?= h(url('guides/schnauzer-dog-breed')) ?>">How the three breeds compare</a> · <a href="<?= h(url('guides/schnauzer-sizes')) ?>">Schnauzer size chart</a></p>
  </div></section>

  <?php foreach(GUIDE_GROUPS as $g=>[$label, $slug, $intro]): if($g === 'breeds') continue; $list = array_slice(topic_articles($g), 0, 3); if(!$list) continue; ?>
  <section class="sec<?= in_array($g, ['grooming', 'mixes'], true) ? ' band' : '' ?>"><div class="wrap">
    <div class="sec-row"><div><h2 class="sec-h"><?= icon(group_icon($g)) ?><?= h($label) ?></h2><p class="mut"><?= h($intro) ?></p></div><a class="btn btn-ghost btn-sm" href="<?= h(url($slug)) ?>">All <?= h(strtolower($label)) ?> guides</a></div>
    <div class="agrid"><?php foreach($list as $a) echo article_card($a); ?></div>
  </div></section>
  <?php endforeach; ?>

  <section class="sec"><div class="wrap narrow">
    <h2 class="sec-h">Schnauzer questions</h2>
    <div class="faq"><?php foreach(home_faqs() as [$q, $a]): ?><details><summary><?= h($q) ?></summary><p><?= h($a) ?></p></details><?php endforeach; ?></div>
  </div></section>

  <section class="sec band"><div class="wrap note-strip"><?= icon('shield') ?><p><strong>Independent and honest.</strong> We don’t breed or sell dogs, and breeders can’t pay to appear here. Looking for a puppy? Read <a href="<?= h(url('guides/how-to-buy-a-schnauzer-puppy')) ?>">how to find a good breeder and avoid scams</a> first.</p></div></section>
  <?php
}

function topic_articles($g){ return array_values(array_filter(articles_of('guide'), fn($a)=>$a['group'] === $g && ($a['lang'] ?? 'en') === 'en')); }

function view_guides(){
  global $CFG;
  page_head($CFG['guides_intro']);
  echo '<div class="wrap"><nav class="chips" aria-label="Topics">';
  foreach(GUIDE_GROUPS as $g=>[$label, $slug]) echo '<a href="'.h(url($slug)).'">'.icon(group_icon($g)).h($label).'</a>';
  echo '</nav>';
  foreach(GUIDE_GROUPS as $g=>[$label, $slug]){
    $list = topic_articles($g);
    if(!$list) continue;
    echo '<section class="sec" id="'.h($g).'"><h2><a href="'.h(url($slug)).'">'.h($label).'</a></h2><div class="agrid">';
    foreach($list as $a) echo article_card($a);
    echo '</div></section>';
  }
  $rest = array_values(array_filter(articles_of('guide'), fn($a)=>!isset(GUIDE_GROUPS[$a['group']]) || ($a['lang'] ?? 'en') !== 'en'));
  if($rest){ echo '<section class="sec"><h2>More guides</h2><div class="agrid">'; foreach($rest as $a) echo article_card($a); echo '</div></section>'; }
  $blog = articles_of('blog');
  if($blog) echo '<p><a class="btn btn-ghost" href="'.h(url('blog')).'">Read the blog</a></p>';
  echo '</div>';
}

function view_topic(){
  global $topic;
  [$label, , $intro] = GUIDE_GROUPS[$topic];
  page_head($intro);
  echo '<div class="wrap"><div class="agrid">';
  foreach(topic_articles($topic) as $a) echo article_card($a);
  echo '</div><nav class="chips chips-more" aria-label="Other topics"><span>More topics:</span>';
  foreach(GUIDE_GROUPS as $g=>[$l, $slug]) if($g !== $topic) echo '<a href="'.h(url($slug)).'">'.icon(group_icon($g)).h($l).'</a>';
  echo '</nav></div>';
}

function view_blog(){
  page_head('News, stories and seasonal tips for schnauzer owners.');
  $list = articles_of('blog');
  usort($list, fn($a, $b)=>strcmp($b['date'], $a['date']));
  echo '<div class="wrap"><div class="agrid">';
  foreach($list as $a) echo article_card($a);
  if(!$list) echo '<p class="mut">No posts yet.</p>';
  echo '</div><p><a class="btn btn-ghost" href="'.h(url('guides')).'">See all schnauzer guides</a></p></div>';
}

function view_article(){
  global $art, $h1, $CFG;
  $toc = headings($art['body']);
  $words = word_count($art['body']);
  $es = ($art['lang'] ?? 'en') === 'es';
  $by = $CFG['author_name'] !== '' ? $CFG['author_name'] : $CFG['brand'];
  $upd = ($art['updated'] ?? '') !== '' ? $art['updated'] : $art['date'];
  ?>
  <article class="wrap article">
    <header class="phead">
      <p class="eyebrow"><?= icon(group_icon($art['group'])) ?><?= $art['type'] === 'blog' ? 'Blog' : (isset(GUIDE_GROUPS[$art['group']]) && !$es ? topic_link($art['group']) : 'Guía') ?></p>
      <h1><?= h($h1) ?></h1>
      <p class="meta"><?= $es ? 'Por' : 'By' ?> <a href="<?= h(url('about')) ?>"><?= h($by) ?></a> · <?= $es ? 'Actualizado' : 'Updated' ?> <time datetime="<?= h($upd) ?>"><?= h(nice_date($upd)) ?></time><?= !$es && $words > 300 ? ' · '.max(1, (int)round($words / 230)).' min read' : '' ?></p>
    </header>
    <?php if(($art['answer'] ?? '') !== ''): ?><div class="answer"><p><?= h(fill($art['answer'])) ?></p></div><?php endif; ?>
    <?php if($art['group'] === 'health' && !$es): ?><p class="notice"><?= icon('info') ?><span>This guide is general information, not veterinary advice. Ask your veterinarian about your own dog.</span></p><?php endif; ?>
    <?php if(!empty($CFG['affiliate_note']) && !$es): ?><p class="notice notice-light"><?= icon('info') ?><span>Some links may be affiliate links; we may earn a small commission at no cost to you. <a href="<?= h(url('advertising-disclosure')) ?>">Learn more</a>.</span></p><?php endif; ?>
    <?php if(($art['image'] ?? '') !== ''): ?><div class="art-img"><?= img_tag($art['image'], $art['image_alt'] ?? $art['title'], '(max-width:900px) 100vw, 860px', true) ?></div><?php endif; ?>
    <div class="art-grid">
      <div class="prose"><?= rich($art['body'], page_blocks()) ?></div>
      <aside class="art-side">
        <?php if(count($toc) > 3): ?><nav class="toc" aria-label="<?= $es ? 'En esta página' : 'On this page' ?>"><p><strong><?= $es ? 'En esta página' : 'On this page' ?></strong></p><ol><?php foreach($toc as [$id, $t]): ?><li><a href="<?= h(url(article_path($art))) ?>#<?= h($id) ?>"><?= h($t) ?></a></li><?php endforeach; ?></ol></nav><?php endif; ?>
        <?php if(!$es): ?><div class="side-box"><p><strong>The three schnauzers</strong></p><ul>
          <li><a href="<?= h(url('guides/miniature-schnauzer')) ?>">Miniature</a> <span>11–20 lb</span></li>
          <li><a href="<?= h(url('guides/standard-schnauzer')) ?>">Standard</a> <span>30–50 lb</span></li>
          <li><a href="<?= h(url('guides/giant-schnauzer')) ?>">Giant</a> <span>55–85 lb</span></li></ul>
          <a class="btn btn-block btn-ghost" href="<?= h(url('guides/schnauzer-sizes')) ?>">Size chart</a></div><?php endif; ?>
      </aside>
    </div>
  </article>
  <?php $rel = related_articles($art); if($rel): ?><section class="sec band"><div class="wrap"><h2><?= $es ? 'Más guías (en inglés)' : 'Keep reading' ?></h2><div class="agrid"><?php foreach($rel as $a) echo article_card($a); ?></div></div></section><?php endif; ?>
  <?php
}

function view_info(){
  global $info;
  page_head('');
  if(($info['answer'] ?? '') !== '') echo '<div class="wrap"><div class="answer answer-page"><p>'.h(fill($info['answer'])).'</p></div></div>';
  echo '<div class="wrap"><div class="prose">'.rich($info['body'], page_blocks()).'</div></div>';
}

function view_contact(){
  global $CFG;
  page_head($CFG['contact_intro']);
  $sent = !empty($_SESSION['flash']); $msg = $_SESSION['flash'] ?? ''; unset($_SESSION['flash']);
  echo '<div class="wrap contact">';
  if($sent) echo '<div class="alert alert-ok" role="status" id="sent">'.h($msg).'</div>';
  echo '<div class="contact-grid"><div class="card">'.contact_form().'</div><div class="contact-side"><h2>Email us</h2><p><a href="mailto:'.h($CFG['email']).'">'.h($CFG['email']).'</a></p>'
    .'<h2>Please note</h2><p>We don’t breed or sell dogs and can’t recommend a specific breeder, but we’re happy to help with questions about schnauzers. For a sick or injured dog, contact your veterinarian.</p></div></div></div>';
}

function view_notfound(){
  page_head('');
  echo '<section class="wrap prose"><p>Sorry, we couldn’t find that page. It may have moved.</p><p><a class="btn" href="'.h(url('guides')).'">All schnauzer guides</a> <a class="btn btn-ghost" href="./">Home</a></p></section>';
}
