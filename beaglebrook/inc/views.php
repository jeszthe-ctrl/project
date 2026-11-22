<?php
/* The website's page templates: layout, page bodies and the reusable blocks. */
if(!defined('BB_ROOT')){ http_response_code(404); exit; }

/* ---------------- puppy wording ---------------- */
function sex_label($s){ return $s === 'female' ? 'Female' : ($s === 'male' ? 'Male' : ''); }
/* "Lemon & White Female Beagle Puppy" */
function puppy_kind($p){ return trim(ucwords(strtolower($p['color'] ?? '')).' '.sex_label($p['sex'] ?? '')).' Beagle Puppy'; }
function puppy_heading($p){ return $p['name'].' — '.puppy_kind($p); }
function puppy_meta_desc($p){
  global $CFG;
  $age = age_text($p['dob'] ?? '');
  $bits = [$p['name'].' is '.($age ? 'a '.$age.' old ' : 'a ').strtolower(trim(($p['color'] ?? '').' '.sex_label($p['sex'] ?? ''))).' beagle puppy for sale'.(($p['price'] ?? '') !== '' ? ' for '.money($p['price']) : '').'.'];
  $bits[] = 'Vet checked, shots, microchip and full papers, delivered from '.$CFG['city'].', '.$CFG['state'].'.';
  return plain(implode(' ', $bits), 158);
}
function status_badge($p){
  $s = $p['status'] ?? 'available';
  return '<span class="badge badge-'.h($s).'">'.h(PUPPY_STATUSES[$s] ?? $s).'</span>';
}
function deposit_amount($price){ global $CFG; return round((float)$price * (int)$CFG['deposit_pct'] / 100, 2); }
function size_text($s){ return ['13'=>'13-inch (under 20 lb as an adult)', '15'=>'15-inch (about 20–30 lb as an adult)'][$s] ?? ''; }

/* ---------------- small icons (inline SVG, drawn with currentColor) ---------------- */
function icon($name, $class='ic'){
  $p = [
    'check'  => '<path d="M5 12.5l4.2 4.2L19 7"/>',
    'shield' => '<path d="M12 3l7.5 3v5.5c0 4.6-3.2 8.2-7.5 9.5-4.3-1.3-7.5-4.9-7.5-9.5V6z"/><path d="M8.5 12l2.5 2.5 4.5-5"/>',
    'heart'  => '<path d="M12 20s-7.5-4.6-7.5-10.1A4.3 4.3 0 0112 7.3a4.3 4.3 0 017.5 2.6C19.5 15.4 12 20 12 20z"/>',
    'truck'  => '<path d="M3 6.5h11v9H3zM14 10h3.8l3.2 3.2v2.3h-7z"/><circle cx="7" cy="17.5" r="1.8"/><circle cx="17" cy="17.5" r="1.8"/>',
    'paw'    => '<ellipse cx="7" cy="9" rx="1.8" ry="2.4"/><ellipse cx="12" cy="6.8" rx="1.8" ry="2.4"/><ellipse cx="17" cy="9" rx="1.8" ry="2.4"/><path d="M12 12c-3 0-5.5 3.2-5.5 5.2 0 1.6 1.4 2.3 2.9 1.9 1.1-.3 1.8-.6 2.6-.6s1.5.3 2.6.6c1.5.4 2.9-.3 2.9-1.9 0-2-2.5-5.2-5.5-5.2z"/>',
    'chip'   => '<rect x="6" y="6" width="12" height="12" rx="2"/><path d="M9 3v3M15 3v3M9 18v3M15 18v3M3 9h3M3 15h3M18 9h3M18 15h3"/>',
    'syringe'=> '<path d="M14.5 4.5l5 5M17 7l-9.5 9.5-3 .5.5-3L14.5 4.5M11 8l1.5 1.5M8.5 10.5L10 12"/>',
    'doc'    => '<path d="M7 3h7l4 4v14H7z"/><path d="M14 3v4h4M9.5 12h6M9.5 15.5h6"/>',
    'bowl'   => '<path d="M3.5 11h17a8.5 7 0 01-17 0z"/><path d="M8 7.5c0-1.5 1.5-1.5 1.5-3M12.5 7.5c0-1.5 1.5-1.5 1.5-3"/>',
    'video'  => '<rect x="3" y="6" width="13" height="12" rx="2"/><path d="M16 10.5l5-3v9l-5-3"/>',
    'star'   => '<path d="M12 3.5l2.6 5.4 5.9.8-4.3 4.1 1 5.8-5.2-2.8-5.2 2.8 1-5.8-4.3-4.1 5.9-.8z"/>',
    'phone'  => '<path d="M6.5 3.5h3l1.5 4-2 1.5a11 11 0 006 6l1.5-2 4 1.5v3a2 2 0 01-2 2A16 16 0 014.5 5.5a2 2 0 012-2z"/>',
    'chat'   => '<path d="M4 5h16v10H9l-5 4z"/>',
    'mail'   => '<rect x="3" y="5.5" width="18" height="13" rx="2"/><path d="M3.5 7l8.5 6 8.5-6"/>',
    'home'   => '<path d="M3.5 11L12 4l8.5 7M6 9.5V20h12V9.5"/>',
    'clock'  => '<circle cx="12" cy="12" r="8.5"/><path d="M12 7.5V12l3 2"/>',
    'lock'   => '<rect x="5" y="10.5" width="14" height="10" rx="2"/><path d="M8 10.5V8a4 4 0 018 0v2.5"/>',
    'menu'   => '<path d="M4 7h16M4 12h16M4 17h16"/>',
    'arrow'  => '<path d="M5 12h14M13 6l6 6-6 6"/>',
    'calendar'=>'<rect x="4" y="5.5" width="16" height="15" rx="2"/><path d="M4 10h16M8.5 3.5v4M15.5 3.5v4"/>',
    'book'   => '<path d="M4 5.5A2.5 2.5 0 016.5 3H20v15H6.5A2.5 2.5 0 004 20.5zM4 20.5A2.5 2.5 0 006.5 23H20"/>',
  ][$name] ?? '';
  return '<svg class="'.h($class).'" viewBox="0 0 24 24" aria-hidden="true" focusable="false">'.$p.'</svg>';
}
/* the icon that suits a guarantee, step or included item, from its wording */
function icon_for($title){
  $t = strtolower($title);
  foreach(['vet'=>'shield', 'genetic'=>'heart', 'right puppy'=>'chip', 'delivery'=>'truck', 'deliver'=>'truck', 'paper'=>'doc', 'deposit'=>'lock',
           'fee'=>'check', 'take-back'=>'home', 'shot'=>'syringe', 'microchip'=>'chip', 'food'=>'bowl', 'guarantee'=>'shield', 'inquiry'=>'chat',
           'support'=>'phone', 'plan'=>'calendar'] as $k=>$ic) if(strpos($t, $k) !== false) return $ic;
  return 'check';
}

/* the home page photo until one is uploaded in Settings (and no puppy has photos yet) */
const SITE_HERO = 'assets/site/tricolor-beagle-puppy-4x3.jpg';

/* the logo: a paw over a brook, and the name */
function logo_svg($class='logo-mark'){
  return '<svg class="'.h($class).'" viewBox="0 0 48 48" aria-hidden="true" focusable="false"><circle cx="24" cy="24" r="23" fill="var(--brand)"/>'
    .'<g fill="#fff8ec"><ellipse cx="15.5" cy="17" rx="3.2" ry="4.2"/><ellipse cx="24" cy="13.2" rx="3.2" ry="4.2"/><ellipse cx="32.5" cy="17" rx="3.2" ry="4.2"/>'
    .'<path d="M24 21.5c-5.3 0-9.6 5.4-9.6 8.8 0 2.7 2.4 3.9 5 3.2 1.9-.5 3.1-1 4.6-1s2.7.5 4.6 1c2.6.7 5-.5 5-3.2 0-3.4-4.3-8.8-9.6-8.8z"/></g>'
    .'<path d="M9 38.5c3-2 6-2 9 0s6 2 9 0 6-2 9 0 2.5 1.5 3 1.2" fill="none" stroke="#9fd3c7" stroke-width="2.2" stroke-linecap="round"/></svg>';
}

/* a photo, or a soft placeholder until photos are added */
function photo_or_placeholder($rel, $alt, $sizes='(max-width:600px) 100vw, (max-width:1100px) 50vw, 360px', $lcp=false){
  $img = $rel ? img_tag($rel, $alt, $sizes, $lcp) : '';
  return $img !== '' ? $img : '<div class="ph" role="img" aria-label="'.h($alt).' (photo coming soon)">'.icon('paw', 'ph-ic').'<span>Photos coming soon</span></div>';
}

/* ---------------- blocks used inside page text ({guarantees}, {steps}…) ---------------- */
function guarantees_block($limit=0){
  global $SITE;
  $list = $limit ? array_slice($SITE['guarantees'], 0, $limit) : $SITE['guarantees'];
  $o = '<div class="cards cards-g">';
  foreach($list as $g) $o .= '<div class="gcard">'.icon(icon_for($g['title']), 'ic ic-lg').'<h3>'.h(fill($g['title'])).'</h3><p>'.h(fill($g['text'])).'</p></div>';
  return $o.'</div>';
}
function steps_block(){
  global $SITE;
  $o = '<ol class="steps">';
  foreach($SITE['steps'] as $i=>$s) $o .= '<li><span class="step-n">'.($i + 1).'</span><div><h3>'.h(fill($s['title'])).'</h3><p>'.h(fill($s['text'])).'</p></div></li>';
  return $o.'</ol>';
}
function included_block(){
  global $SITE;
  $o = '<ul class="included">';
  foreach($SITE['included'] as $s) $o .= '<li>'.icon(icon_for($s['title']), 'ic ic-lg').'<div><strong>'.h(fill($s['title'])).'</strong><span>'.h(fill($s['text'])).'</span></div></li>';
  return $o.'</ul>';
}
function payments_block(){
  $o = '<ul class="paylist">';
  foreach(payments_on() as $p) $o .= '<li>'.icon('check').'<strong>'.h($p['name']).'</strong>'.($p['note'] !== '' ? ' <span>— '.h(fill($p['note'])).'</span>' : '').'</li>';
  return $o.'</ul>';
}
function puppies_block($list=null, $limit=0){
  $list = $list ?? array_merge(puppies_with('available'), puppies_with('reserved'));
  if($limit) $list = array_slice($list, 0, $limit);
  if(!$list) return '<div class="empty"><p><strong>All our puppies have found homes for now.</strong> New litters are announced to our waitlist first.</p><p><a class="btn" href="'.h(url('upcoming-litters')).'#waitlist">Join the waitlist</a></p></div>';
  $o = '<div class="pgrid">';
  foreach($list as $p) $o .= puppy_card($p);
  return $o.'</div>';
}
function delivery_rates_block(){
  global $SITE;
  $opts = delivery_options();
  if(!$opts) return '';
  $o = '<div class="table-wrap"><table><thead><tr><th scope="col">Delivery to</th>';
  foreach($opts as $m) $o .= '<th scope="col">'.h($m['label']).'<br><span class="muted">'.h($m['days']).'</span></th>';
  $o .= '</tr></thead><tbody>';
  foreach($SITE['delivery']['zones'] as $z){
    $states = trim((string)$z['states']);
    $o .= '<tr><th scope="row">'.h($z['name']).($states !== '*' && $states !== '' ? '<br><span class="muted">'.h(str_replace(',', ', ', preg_replace('/\s+/', '', $states))).'</span>' : '').'</th>';
    foreach($opts as $m) $o .= '<td><strong>'.(is_numeric($z[$m['key']] ?? '') ? h(money($z[$m['key']])) : 'Quoted on request').'</strong></td>';
    $o .= '</tr>';
  }
  return $o.'</tbody></table></div>';
}
function licenses_block(){
  global $CFG;
  $lines = array_filter(array_map('trim', explode("\n", (string)$CFG['license_text'])));
  if(!$lines && $CFG['registry'] === '') return '';
  $o = '<div class="callout"><p><strong>Our registration and licensing</strong></p><ul>';
  if($CFG['registry'] !== '') $o .= '<li>Our puppies are registered with '.h($CFG['registry']).'.</li>';
  foreach($lines as $l) $o .= '<li>'.h($l).'</li>';
  return $o.'</ul></div>';
}
function payee_block(){
  global $CFG;
  return $CFG['payee_name'] !== '' ? '<div class="callout"><p>The name on our payment accounts is <strong>'.h($CFG['payee_name']).'</strong>. If you are asked to pay any other name, stop and email us.</p></div>' : '';
}
function breeder_block(){
  global $CFG;
  if($CFG['breeder_name'] === '' && $CFG['breeder_story'] === '') return '';
  $o = '<div class="breeder">';
  if($CFG['breeder_photo'] !== '') $o .= '<div class="breeder-ph">'.img_tag($CFG['breeder_photo'], $CFG['breeder_name'] ?: 'The breeder', '(max-width:700px) 100vw, 320px').'</div>';
  $o .= '<div>'.($CFG['breeder_name'] !== '' ? '<h2 id="meet-the-breeder">Meet '.h($CFG['breeder_name']).'</h2>' : '').rich($CFG['breeder_story']).'</div></div>';
  return $o;
}
function contact_block(){
  global $CFG;
  $o = '<ul class="contact-list">';
  $o .= '<li>'.icon('mail').'<a href="mailto:'.h($CFG['email']).'">'.h($CFG['email']).'</a></li>';
  if($CFG['phone'] !== '') $o .= '<li>'.icon('phone').'<a href="tel:'.h(tel($CFG['phone'])).'">'.h($CFG['phone']).'</a> · <a href="sms:'.h(tel($CFG['phone'])).'">Text us</a></li>';
  $o .= '<li>'.icon('home').h($CFG['city'].', '.$CFG['state_name']).' · delivery only</li>';
  $o .= '<li>'.icon('clock').'We reply '.h(fill('{reply_time}')).'</li>';
  return $o.'</ul>';
}
function tel($p){ $d = preg_replace('/[^\d+]/', '', (string)$p); return strlen($d) === 10 ? '+1'.$d : $d; }

/* our live prices, from the puppies listed right now */
function our_prices_block(){
  $list = array_values(array_filter(puppies_with('available'), fn($p)=>($p['price'] ?? '') !== ''));
  if(!$list) return '<div class="callout"><p>All our puppies have found homes for now. Each puppy’s price is shown on their page; <a href="'.h(url('upcoming-litters')).'#waitlist">join the waitlist</a> to hear about the next litter first.</p></div>';
  $prices = array_map(fn($p)=>(float)$p['price'], $list);
  $o = '<div class="stats"><div><b>'.count($list).'</b><span>'.(count($list) === 1 ? 'puppy' : 'puppies').' available now</span></div><div><b>'.h(money(min($prices))).'</b><span>lowest price</span></div>'
     .'<div><b>'.h(money(max($prices))).'</b><span>highest price</span></div><div><b>'.h(money(round(array_sum($prices) / count($prices)))).'</b><span>average</span></div></div>';
  $o .= '<div class="table-wrap"><table><thead><tr><th scope="col">Puppy</th><th scope="col">Color</th><th scope="col">Age</th><th scope="col">Price</th></tr></thead><tbody>';
  foreach(array_slice($list, 0, 8) as $p) $o .= '<tr><th scope="row"><a href="'.h(puppy_url($p)).'">'.h($p['name']).'</a></th><td>'.h(trim($p['color'].' '.strtolower(sex_label($p['sex'])))).'</td><td>'.h(age_text($p['dob'] ?? '') ?: '—').'</td><td><strong>'.h(money($p['price'])).'</strong></td></tr>';
  return $o.'</tbody></table></div><p class="muted">Updated automatically from the puppies listed on our site.</p>';
}
/* the call to action placed right under the price tables and at the end of every guide */
function price_cta_block(){
  return '<div class="cta-card"><div><p class="eyebrow">'.icon('paw').'Ready to choose?</p><p class="cta-t">See our beagle puppies for sale, with real photos, prices and both parents.</p></div>'
    .'<div class="cta-row"><a class="btn btn-lg" href="'.h(url(PUPPIES_PATH)).'">See available puppies '.icon('arrow').'</a><a class="btn btn-lg btn-ghost" href="'.h(url(PUPPIES_PATH)).'#inquire">Ask about a puppy</a></div></div>';
}
/* "We deliver to …" links to the state pages */
function states_links($title='We deliver beagle puppies to'){
  $on = locations_on();
  if(!$on) return '';
  $o = '<div class="states"><p class="eyebrow">'.icon('truck').h($title).'</p><ul>';
  foreach($on as $c=>$l) $o .= '<li><a href="'.h(location_url($c)).'">'.h(states()[$c][0]).'</a></li>';
  return $o.'</ul><p class="muted">…and every other state: '.h(fill('{delivery_options}')).'.</p></div>';
}

function page_blocks(){
  return ['{guarantees}'=>guarantees_block(), '{steps}'=>steps_block(), '{included}'=>included_block(), '{payments}'=>payments_block(), '{our_prices}'=>our_prices_block(), '{price_cta}'=>price_cta_block(), '{states}'=>states_links(), '{delivery_rates}'=>delivery_rates_block(),
          '{puppies}'=>puppies_block(), '{licenses}'=>licenses_block(), '{payee}'=>payee_block(), '{breeder}'=>breeder_block(), '{contact}'=>contact_block()];
}

/* ---------------- cards ---------------- */
function puppy_card($p){
  $age = age_text($p['dob'] ?? '');
  $o = '<article class="pcard"><a class="pcard-ph" href="'.h(puppy_url($p)).'" tabindex="-1" aria-hidden="true">'.photo_or_placeholder($p['photos'][0] ?? '', $p['name'].', '.strtolower(puppy_kind($p))).status_badge($p).'</a>';
  $o .= '<div class="pcard-b"><h3><a href="'.h(puppy_url($p)).'">'.h($p['name']).'</a></h3>';
  $o .= '<p class="pcard-kind">'.h(trim(($p['color'] ?? '').' · '.sex_label($p['sex'] ?? ''), ' ·')).(!empty($p['champion']) ? ' <span class="champ">'.icon('star').'Champion bloodline</span>' : '').'</p>';
  $o .= '<ul class="pcard-facts">'.($age ? '<li>'.icon('calendar').h($age).' old</li>' : '').(($p['ready'] ?? '') !== '' && strtotime($p['ready']) > time() ? '<li>'.icon('home').'Ready '.h(date('M j', strtotime($p['ready']))).'</li>' : (($p['status'] ?? '') === 'available' ? '<li>'.icon('home').'Ready now</li>' : '')).'</ul>';
  if($par = parents_line($p)) $o .= '<p class="pcard-par">'.h($par).'</p>';
  $o .= '<div class="pcard-f"><span class="price">'.(($p['price'] ?? '') !== '' ? h(money($p['price'])) : '').'</span>';
  if(($p['status'] ?? '') === 'sold') $o .= '<a class="btn btn-sm btn-ghost" href="'.h(puppy_url($p)).'">See '.h($p['name']).'</a>';
  else $o .= '<span class="pcard-btns"><a class="btn btn-sm btn-ghost" href="'.h(puppy_url($p)).'" aria-label="Details about '.h($p['name']).'">Details</a>'
            .'<a class="btn btn-sm" href="'.h(puppy_url($p)).'#inquire" data-pick="'.h($p['id']).'" aria-label="Proceed now with '.h($p['name']).'">Proceed now</a></span>';
  return $o.'</div></div></article>';
}
/* "Mom Felicity · Dad Topper", from the linked parents or the parent text from a spreadsheet */
function parents_line($p){
  $name = function($id, $text){
    if($par = parent_by_id($id ?? '')) return $par['name'];
    $t = trim(preg_replace('/\s*\([^)]*\)/', '', (string)$text));
    return strcasecmp($t, 'beagle') === 0 ? '' : $t;
  };
  $mom = $name($p['dam'] ?? '', $p['dam_text'] ?? ''); $dad = $name($p['sire'] ?? '', $p['sire_text'] ?? '');
  return implode(' · ', array_filter([$mom !== '' ? 'Mom '.$mom : '', $dad !== '' ? 'Dad '.$dad : '']));
}
/* the facts about a puppy shown when it's chosen in the inquiry form */
function pick_info($p){
  $ready = ($p['ready'] ?? '') !== '' && strtotime($p['ready']) > time() ? 'Ready '.date('M j', strtotime($p['ready'])) : (($p['status'] ?? '') === 'available' ? 'Ready now' : 'Reserved');
  return implode(' · ', array_filter([trim(($p['color'] ?? '').' '.strtolower(sex_label($p['sex'] ?? ''))), $ready, ($p['price'] ?? '') !== '' ? money($p['price']) : '', parents_line($p)]));
}
function article_card($a){
  $o = '<article class="acard"><a href="'.h(url(article_path($a))).'">';
  if(($a['image'] ?? '') !== '') $o .= img_tag($a['image'], $a['image_alt'] ?? $a['title'], '(max-width:600px) 100vw, 360px');
  return $o.'<h3>'.h($a['title']).'</h3><p>'.h(fill($a['excerpt'] ?? plain($a['body'], 140))).'</p><span class="more">Read '.($a['type'] === 'blog' ? 'post' : 'guide').' '.icon('arrow').'</span></a></article>';
}
function parent_mini($p){
  return '<a class="parent-mini" href="'.h(parent_url($p)).'">'.photo_or_placeholder($p['photos'][0] ?? '', $p['name'], '120px')
    .'<span><small>'.($p['role'] === 'sire' ? 'Sire (dad)' : 'Dam (mom)').'</small><strong>'.h($p['name']).'</strong>'.(($p['registered_name'] ?? '') !== '' ? '<em>'.h($p['registered_name']).'</em>' : '').'</span></a>';
}
function review_card($r){
  $stars = max(0, min(5, (int)($r['rating'] ?? 5)));
  $o = '<figure class="review">'.(($r['photo'] ?? '') !== '' ? img_tag($r['photo'], ($r['puppy'] ?? '') !== '' ? $r['puppy'].' at home' : 'Beagle at home', '(max-width:600px) 100vw, 360px') : '');
  $o .= '<div class="stars" aria-label="'.$stars.' out of 5 stars">'.str_repeat(icon('star', 'ic star'), $stars).'</div>';
  $o .= '<blockquote>'.rich($r['text'] ?? '').'</blockquote><figcaption><strong>'.h($r['name'] ?? '').'</strong>'
    .(($r['location'] ?? '') !== '' ? ' · '.h($r['location']) : '').(($r['puppy'] ?? '') !== '' ? '<br><span>Puppy: '.h($r['puppy']).'</span>' : '').(($r['date'] ?? '') !== '' ? '<br><span>'.h(nice_date($r['date'])).'</span>' : '').'</figcaption></figure>';
  return $o;
}

/* related guides: same group first, then the buying guides */
function related_articles($a, $n=4){
  global $SITE;
  $pool = array_filter($SITE['articles'], fn($x)=>$x['slug'] !== $a['slug'] && empty($x['draft']) && ($x['lang'] ?? 'en') === 'en');
  usort($pool, fn($x, $y)=>(($y['group'] === $a['group']) <=> ($x['group'] === $a['group'])) ?: (($y['group'] === 'buying') <=> ($x['group'] === 'buying')));
  return array_slice(array_values($pool), 0, $n);
}
function guide_links($slugs, $title='Helpful guides'){
  $list = array_filter(array_map(fn($s)=>article($s, 'guide') ?? article($s, 'blog') ?? (($pg = info_page($s)) ? ['type'=>'page', 'slug'=>$s, 'title'=>fill($pg['title']), 'excerpt'=>$pg['answer'] ?? plain($pg['body'], 140), 'body'=>$pg['body']] : null), $slugs));
  if(!$list) return '';
  $o = '<section class="sec"><h2>'.h($title).'</h2><div class="agrid">';
  foreach($list as $a) $o .= article_card($a);
  return $o.'</div></section>';
}

/* ---------------- forms ---------------- */
function err($k){ global $errors; return isset($errors[$k]) ? '<span class="ferr" id="e-'.h($k).'">'.h($errors[$k]).'</span>' : ''; }
function val($k){ global $old; return h($old[$k] ?? ''); }
function field($name, $label, $type='text', $req=true, $attrs=''){
  global $errors;
  $bad = isset($errors[$name]);
  return '<label class="f'.($bad ? ' f-bad' : '').'"><span>'.h($label).($req ? ' <b aria-hidden="true">*</b>' : ' <i>(optional)</i>').'</span>'
    .'<input type="'.$type.'" name="'.h($name).'" value="'.val($name).'"'.($req ? ' required' : '').($bad ? ' aria-invalid="true" aria-describedby="e-'.h($name).'"' : '').' '.$attrs.'>'.err($name).'</label>';
}
function state_select($name='state', $req=true){
  global $old, $errors;
  $o = '<label class="f'.(isset($errors[$name]) ? ' f-bad' : '').'"><span>State'.($req ? ' <b aria-hidden="true">*</b>' : ' <i>(optional)</i>').'</span><select name="'.h($name).'"'.($req ? ' required' : '').' autocomplete="address-level1"><option value="">Choose…</option>';
  foreach(US_STATES as $k=>$v) $o .= '<option value="'.$k.'"'.(($old[$name] ?? '') === $k ? ' selected' : '').'>'.h($v).'</option>';
  return $o.'</select>'.err($name).'</label>';
}
function spam_fields(){
  return '<input type="hidden" name="token" value="'.h(form_token()).'"><div class="hp" aria-hidden="true"><label>Leave this empty<input type="text" name="website" tabindex="-1" autocomplete="off"></label></div>';
}
function form_error(){ global $errors; return $errors ? '<div class="alert alert-err" role="alert"><strong>Please check the form.</strong> '.h($errors['form'] ?? $errors['puppy'] ?? 'Some details are missing or need correcting below.').'</div>' : ''; }

/* The inquiry form, the same on every page: choose a puppy (or "not sure yet"), leave a phone number
   and email (both required), and a note. $sel = the puppy to preselect. */
function inquiry_form($sel=null){
  global $CFG, $old, $PATH;
  $list = array_merge(puppies_with('available'), puppies_with('reserved'));
  $chosen = (string)($old['puppy'] ?? ($sel['id'] ?? ''));
  ob_start(); ?>
  <form class="form qform" method="post" action="<?= h(url($PATH)) ?>#inquire" novalidate>
    <input type="hidden" name="action" value="inquiry"><input type="hidden" name="page" value="<?= h($PATH) ?>"><?= spam_fields() ?><?= form_error() ?>
    <label class="f f-dog"><span>Your dog</span><select name="puppy" data-pickbox>
      <option value="">Not sure yet — help me choose</option>
      <?php foreach($list as $p): ?><option value="<?= h($p['id']) ?>"<?= $chosen === $p['id'] ? ' selected' : '' ?> data-info="<?= h(pick_info($p)) ?>" data-url="<?= h(puppy_url($p)) ?>" data-img="<?= h(!empty($p['photos'][0]) ? thumb($p['photos'][0], 320) : '') ?>"><?= h($p['name'].' · '.trim($p['color'].' '.strtolower(sex_label($p['sex']))).(($p['price'] ?? '') !== '' ? ' · '.money($p['price']) : '').($p['status'] === 'reserved' ? ' · reserved' : '')) ?></option><?php endforeach; ?>
    </select></label>
    <?php $cp = $chosen !== '' ? find_by($list, 'id', $chosen) : null; ?>
    <div class="pickbox"<?= $cp ? '' : ' hidden' ?> aria-live="polite"><span class="pickbox-ph"><?php if($cp && !empty($cp['photos'][0])): ?><img src="<?= h(thumb($cp['photos'][0], 320)) ?>" alt="" width="64" height="64"><?php endif; ?></span><span><strong><?= $cp ? h($cp['name']) : '' ?></strong><small><?= $cp ? h(pick_info($cp)) : '' ?></small><a href="<?= $cp ? h(puppy_url($cp)) : '#' ?>">See photos &amp; details</a></span></div>
    <div class="frow frow-2"><?= field('phone', 'Phone', 'tel', true, 'autocomplete="tel" inputmode="tel" placeholder="(555) 555-0123"') ?><?= field('email', 'Email', 'email', true, 'autocomplete="email" placeholder="you@example.com"') ?></div>
    <label class="f"><span>Your note <i>(optional)</i></span><textarea name="notes" rows="3" maxlength="3000" placeholder="Your name, questions, when you’d like your puppy…"><?= val('notes') ?></textarea></label>
    <button class="btn btn-lg btn-block" type="submit">Proceed now <?= icon('arrow') ?></button>
    <p class="fnote"><?= icon('lock') ?> No payment. We reply <?= h(fill('{reply_time}')) ?> by call, text or email. <a href="<?= h(url('privacy-policy')) ?>">Privacy</a></p>
  </form>
  <?php return ob_get_clean();
}

function waitlist_form(){
  global $old;
  ob_start(); ?>
  <form class="form" method="post" action="<?= h(url('upcoming-litters')) ?>#waitlist" novalidate>
    <input type="hidden" name="action" value="waitlist"><?= spam_fields() ?><?= form_error() ?>
    <div class="frow frow-2"><?= field('name', 'Name', 'text', true, 'autocomplete="name"') ?><?= field('email', 'Email', 'email', true, 'autocomplete="email"') ?></div>
    <div class="frow frow-2"><?= field('phone', 'Phone', 'tel', false, 'autocomplete="tel"') ?><?= state_select('state', false) ?></div>
    <div class="frow frow-3">
      <label class="f"><span>Boy or girl?</span><select name="sex"><?php foreach(['Either', 'Male', 'Female'] as $o): ?><option<?= ($old['sex'] ?? '') === $o ? ' selected' : '' ?>><?= h($o) ?></option><?php endforeach; ?></select></label>
      <?= field('color', 'Color preference', 'text', false, 'placeholder="e.g. tricolor, lemon"') ?>
      <label class="f"><span>When?</span><select name="timing"><?php foreach(['', 'As soon as possible', 'In 1–3 months', 'In 3–6 months', 'Later this year'] as $o): ?><option<?= ($old['timing'] ?? '') === $o ? ' selected' : '' ?> value="<?= h($o) ?>"><?= $o === '' ? 'Choose…' : h($o) ?></option><?php endforeach; ?></select></label>
    </div>
    <label class="f"><span>Anything else? <i>(optional)</i></span><textarea name="notes" rows="3" maxlength="1000"><?= val('notes') ?></textarea></label>
    <button class="btn btn-lg" type="submit">Join the waitlist</button>
    <p class="fnote">We only email about new litters and puppies. Unsubscribe with one click, any time.</p>
  </form>
  <?php return ob_get_clean();
}

/* ---------------- structured data (JSON-LD) ---------------- */
function org_ld(){
  global $CFG;
  $same = array_values(array_filter([$CFG['facebook'], $CFG['instagram'], $CFG['tiktok'], $CFG['youtube']]));
  return array_filter(['@type'=>'LocalBusiness', 'image'=>rtrim($CFG['domain'], '/').'/'.share_image(), '@id'=>rtrim($CFG['domain'], '/').'/#org', 'name'=>$CFG['brand'], 'url'=>rtrim($CFG['domain'], '/').'/',
    'logo'=>rtrim($CFG['domain'], '/').'/assets/logo-512.png', 'email'=>$CFG['email'], 'telephone'=>$CFG['phone'] ?: null,
    'description'=>fill($CFG['footer_blurb']),
    'address'=>['@type'=>'PostalAddress', 'addressLocality'=>$CFG['city'], 'addressRegion'=>$CFG['state'], 'addressCountry'=>'US'],
    'areaServed'=>['@type'=>'Country', 'name'=>'United States'], 'sameAs'=>$same ?: null]);
}
function structured_data(){
  global $page, $crumbs, $art, $info, $CFG, $h1, $desc, $canonical, $og_image;
  $g = [org_ld()];
  if($page === 'home') $g[] = ['@type'=>'WebSite', '@id'=>rtrim($CFG['domain'], '/').'/#site', 'name'=>$CFG['brand'], 'url'=>rtrim($CFG['domain'], '/').'/', 'publisher'=>['@id'=>rtrim($CFG['domain'], '/').'/#org']];
  if(count($crumbs) > 1){
    $items = [];
    foreach($crumbs as $i=>[$name, $path]) $items[] = ['@type'=>'ListItem', 'position'=>$i + 1, 'name'=>$name, 'item'=>abs_url($path)];
    $g[] = ['@type'=>'BreadcrumbList', 'itemListElement'=>$items];
  }
  if($page === 'article'){
    $img = $og_image !== '' ? rtrim($CFG['domain'], '/').'/'.$og_image : rtrim($CFG['domain'], '/').'/'.share_image();
    $g[] = ['@type'=>$art['type'] === 'blog' ? 'BlogPosting' : 'Article', 'headline'=>mb_substr($h1, 0, 110), 'description'=>$desc, 'image'=>$img,
      'datePublished'=>$art['date'], 'dateModified'=>$art['updated'] ?? $art['date'], 'inLanguage'=>$art['lang'] ?? 'en',
      'author'=>['@type'=>'Organization', 'name'=>$CFG['brand'], 'url'=>rtrim($CFG['domain'], '/').'/'], 'publisher'=>['@id'=>rtrim($CFG['domain'], '/').'/#org'],
      'mainEntityOfPage'=>$canonical];
  }
  global $puppy, $loc;
  $extra_faqs = $page === 'location' ? location_faqs($loc) : ($page === 'home' ? home_faqs() : []);
  if($page === 'puppy' && ($puppy['price'] ?? '') !== '') $g[] = puppy_product_ld($puppy);
  if(in_array($page, ['puppies', 'location'], true)) if($agg = puppies_offer_ld($page === 'location' ? $loc : '')) $g[] = $agg;
  if(!empty($extra_faqs)) $g[] = ['@type'=>'FAQPage', 'mainEntity'=>array_map(fn($q)=>['@type'=>'Question', 'name'=>$q[0], 'acceptedAnswer'=>['@type'=>'Answer', 'text'=>$q[1]]], $extra_faqs)];
  $faq_text = $page === 'article' ? $art['body'] : ($page === 'info' ? $info['body'] : '');
  if($faq_text !== '' && ($faqs = text_faqs($faq_text))){
    $g[] = ['@type'=>'FAQPage', 'mainEntity'=>array_map(fn($q)=>['@type'=>'Question', 'name'=>$q[0], 'acceptedAnswer'=>['@type'=>'Answer', 'text'=>$q[1]]], $faqs)];
  }
  return '<script type="application/ld+json">'.json_encode(['@context'=>'https://schema.org', '@graph'=>$g], JSON_UNESCAPED_SLASHES|JSON_UNESCAPED_UNICODE|JSON_HEX_TAG).'</script>';
}
/* a puppy as a product with its offer: price, availability, delivery from Cincinnati */
function puppy_product_ld($p){
  global $CFG;
  $base = rtrim($CFG['domain'], '/').'/';
  $avail = ['available'=>'InStock', 'reserved'=>'OutOfStock', 'sold'=>'SoldOut'][$p['status']] ?? 'InStock';
  $offer = ['@type'=>'Offer', 'price'=>number_format((float)$p['price'], 2, '.', ''), 'priceCurrency'=>'USD', 'availability'=>'https://schema.org/'.$avail,
    'itemCondition'=>'https://schema.org/NewCondition', 'url'=>abs_url(PUPPIES_PATH.'/'.$p['slug']), 'seller'=>['@id'=>$base.'#org']];
  if(($fee = delivery_base_fee('standard')) !== null) $offer['shippingDetails'] = ['@type'=>'OfferShippingDetails',
    'shippingRate'=>['@type'=>'MonetaryAmount', 'value'=>number_format($fee, 2, '.', ''), 'currency'=>'USD'],
    'shippingDestination'=>['@type'=>'DefinedRegion', 'addressCountry'=>'US'],
    'deliveryTime'=>['@type'=>'ShippingDeliveryTime', 'handlingTime'=>['@type'=>'QuantitativeValue', 'minValue'=>0, 'maxValue'=>2, 'unitCode'=>'DAY'],
      'transitTime'=>['@type'=>'QuantitativeValue', 'minValue'=>1, 'maxValue'=>5, 'unitCode'=>'DAY']]];
  return array_filter(['@type'=>'Product', 'name'=>puppy_heading($p), 'description'=>puppy_meta_desc($p), 'sku'=>$p['id'], 'category'=>'Beagle puppy',
    'brand'=>['@type'=>'Brand', 'name'=>$CFG['brand']], 'color'=>$p['color'] ?: null,
    'image'=>array_map(fn($f)=>$base.$f, array_slice($p['photos'] ?? [], 0, 4)) ?: null, 'offers'=>$offer]);
}
/* all available puppies as one offer range, on the puppies, state and price pages */
function puppies_offer_ld($state=''){
  global $CFG;
  $list = array_values(array_filter(puppies_with('available'), fn($p)=>($p['price'] ?? '') !== ''));
  if(!$list) return null;
  $prices = array_map(fn($p)=>(float)$p['price'], $list);
  $offer = ['@type'=>'AggregateOffer', 'lowPrice'=>number_format(min($prices), 2, '.', ''), 'highPrice'=>number_format(max($prices), 2, '.', ''), 'priceCurrency'=>'USD',
    'offerCount'=>count($list), 'availability'=>'https://schema.org/InStock', 'seller'=>['@id'=>rtrim($CFG['domain'], '/').'/#org']];
  if($state !== '') $offer['eligibleRegion'] = ['@type'=>'State', 'name'=>states()[$state][0]];
  return ['@type'=>'Product', 'name'=>'Beagle puppies'.($state !== '' ? ' for sale in '.states()[$state][0] : ''), 'category'=>'Beagle puppy',
    'brand'=>['@type'=>'Brand', 'name'=>$CFG['brand']], 'description'=>'Purebred beagle puppies, vet checked, microchipped and sold with full papers, delivered from '.$CFG['city'].', Ohio.',
    'image'=>rtrim($CFG['domain'], '/').'/'.share_image(), 'offers'=>$offer];
}

function share_image(){ global $CFG; return $CFG['share_photo'] !== '' && is_file(BB_ROOT.'/'.$CFG['share_photo']) ? $CFG['share_photo'] : 'assets/share.jpg'; }

/* ---------------- layout ---------------- */
function nav_items(){
  return [[PUPPIES_PATH, 'Puppies for sale'], ['our-beagles', 'Our beagles'], ['how-it-works', 'How it works'],
          ['guides', 'Guides'], ['reviews', 'Reviews'], ['contact', 'Contact']];
}

function render_page(){
  global $SITE, $CFG, $page, $title, $desc, $canonical, $noindex, $og_type, $og_image, $crumbs, $h1, $lang, $BASE, $PATH;
  $css_v = @filemtime(BB_ROOT.'/assets/style.css');
  $img = rtrim($CFG['domain'], '/').'/'.($og_image !== '' ? $og_image : share_image());
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
<?php endif; ?>
<meta property="og:site_name" content="<?= h($CFG['brand']) ?>">
<meta property="og:type" content="<?= h($og_type === 'product' ? 'website' : $og_type) ?>">
<meta property="og:title" content="<?= h($title) ?>">
<meta property="og:description" content="<?= h($desc) ?>">
<meta property="og:url" content="<?= h($canonical) ?>">
<meta property="og:image" content="<?= h($img) ?>">
<meta name="twitter:card" content="summary_large_image">
<meta name="theme-color" content="#7a4a1e">
<link rel="icon" href="assets/favicon.svg" type="image/svg+xml">
<link rel="icon" href="favicon.ico" sizes="32x32">
<link rel="apple-touch-icon" href="assets/apple-touch-icon.png">
<?php if($CFG['gsc_verification'] !== ''): ?><meta name="google-site-verification" content="<?= h($CFG['gsc_verification']) ?>">
<?php endif; if($CFG['bing_verification'] !== ''): ?><meta name="msvalidate.01" content="<?= h($CFG['bing_verification']) ?>">
<?php endif; ?>
<script>document.documentElement.className+=" js"</script>
<link rel="stylesheet" href="assets/style.css?v=<?= (int)$css_v ?>">
<?= structured_data() ?>
<?php if(preg_match('/^G-[A-Z0-9]{4,}$/', $CFG['ga4_id'])): ?>
<script async src="https://www.googletagmanager.com/gtag/js?id=<?= h($CFG['ga4_id']) ?>"></script>
<script>window.dataLayer=window.dataLayer||[];function gtag(){dataLayer.push(arguments)}gtag('js',new Date());gtag('config','<?= h($CFG['ga4_id']) ?>');</script>
<?php endif; ?>
</head>
<body class="pg-<?= h($page) ?>">
<a class="skip" href="#main">Skip to content</a>
<div class="topbar"><div class="wrap"><span><?= icon('truck') ?>Delivered to your door</span><span class="hide-sm"><?= icon('shield') ?><?= h(fill('{genetic_year}')) ?> health guarantee</span><span class="hide-sm"><?= icon('doc') ?>All puppy documents available</span></div></div>
<header class="site-h">
  <div class="wrap hrow">
    <a class="brand" href="./" aria-label="<?= h($CFG['brand']) ?> home"><?= logo_svg() ?><span class="brand-t"><?= h($CFG['brand']) ?><small>Purebred beagle puppies</small></span></a>
    <button class="navbtn" type="button" aria-expanded="false" aria-controls="nav"><?= icon('menu') ?><span>Menu</span></button>
    <nav id="nav" class="nav" aria-label="Main">
      <?php foreach(nav_items() as [$path, $label]): ?><a href="<?= h(url($path)) ?>"<?= $PATH === $path || ($path === PUPPIES_PATH && $page === 'puppy') || ($path === 'guides' && $page === 'article') ? ' aria-current="page"' : '' ?>><?= h($label) ?></a><?php endforeach; ?>
    </nav>
    <a class="btn btn-sm hcta" href="<?= h(url(PUPPIES_PATH)) ?>">Find your puppy</a>
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
<?php /* the phone bar: on a puppy's page its main button goes straight to that puppy's inquiry form */
  global $puppy;
  $main = $page === 'puppy' && ($puppy['status'] ?? '') !== 'sold' ? ['#inquire', 'arrow', 'Proceed now'] : ($page === 'home' ? ['#puppies', 'paw', 'Choose a puppy'] : [url(PUPPIES_PATH), 'paw', 'See puppies']); ?>
<?php if($CFG['phone'] !== ''): ?>
<div class="mbar"><a href="sms:<?= h(tel($CFG['phone'])) ?>"><?= icon('chat') ?>Text us</a><a href="tel:<?= h(tel($CFG['phone'])) ?>"><?= icon('phone') ?>Call</a><a class="mbar-main" href="<?= h($main[0]) ?>"><?= icon($main[1]) ?><?= h($main[2]) ?></a></div>
<?php else: ?>
<div class="mbar"><a href="mailto:<?= h($CFG['email']) ?>"><?= icon('mail') ?>Email us</a><a class="mbar-main" href="<?= h($main[0]) ?>"><?= icon($main[1]) ?><?= h($main[2]) ?></a></div>
<?php endif; ?>
<script>
(function(){
var d=document,b=d.querySelector('.navbtn'),n=d.getElementById('nav');
if(b)b.addEventListener('click',function(){var o=n.classList.toggle('open');b.setAttribute('aria-expanded',o)});
d.querySelectorAll('[data-gal]').forEach(function(t){t.addEventListener('click',function(){var m=d.getElementById('gal-main');if(!m)return;m.innerHTML=t.querySelector('template').innerHTML;d.querySelectorAll('[data-gal]').forEach(function(x){x.removeAttribute('aria-current')});t.setAttribute('aria-current','true')})});
d.querySelectorAll('form.form').forEach(function(f){f.addEventListener('submit',function(){var s=f.querySelector('[type=submit]');setTimeout(function(){s.disabled=true;s.textContent='Sending…'},10)})});
var bad=d.querySelector('.f-bad input,.f-bad select,.f-bad textarea');if(bad)bad.focus();
/* choosing a dog: the form shows the chosen puppy; "Inquire" on a card picks it in this page's form */
var sel=d.querySelector('select[data-pickbox]');
function showPick(){var box=sel.form.querySelector('.pickbox'),o=sel.options[sel.selectedIndex];if(!box)return;if(!o||!o.value){box.hidden=true;return}
var ph=box.querySelector('.pickbox-ph'),im=o.getAttribute('data-img');ph.innerHTML='';if(im){var i=new Image(64,64);i.src=im;i.alt='';ph.appendChild(i)}
box.querySelector('strong').textContent=o.text.split(' · ')[0];box.querySelector('small').textContent=o.getAttribute('data-info');box.querySelector('a').href=o.getAttribute('data-url');box.hidden=false}
if(sel){sel.addEventListener('change',showPick);
d.querySelectorAll('[data-pick]').forEach(function(a){a.addEventListener('click',function(e){var v=a.getAttribute('data-pick');if(!sel.querySelector('option[value="'+v+'"]'))return;e.preventDefault();sel.value=v;showPick();
var t=d.getElementById('inquire');t.scrollIntoView({behavior:window.matchMedia('(prefers-reduced-motion: reduce)').matches?'auto':'smooth'});setTimeout(function(){var p=sel.form.querySelector('[name=phone]');if(p&&!p.value)p.focus({preventScroll:true})},450)})})}
})();
</script>
</body>
</html>
<?php
}

function footer(){
  global $SITE, $CFG;
  $guides = array_slice(articles_of('guide'), 0, 7);
  ?>
<section class="pre-f"><div class="wrap pre-f-in">
  <div><h2>Questions? Talk to a real beagle breeder.</h2><p>We reply <?= h(fill('{reply_time}')) ?>. No pressure, and no payment until you’re sure.</p></div>
  <div class="pre-f-b"><?php if($CFG['phone'] !== ''): ?><a class="btn btn-light" href="sms:<?= h(tel($CFG['phone'])) ?>"><?= icon('chat') ?>Text us</a><a class="btn btn-light" href="tel:<?= h(tel($CFG['phone'])) ?>"><?= icon('phone') ?><?= h($CFG['phone']) ?></a><?php endif; ?><a class="btn btn-light" href="mailto:<?= h($CFG['email']) ?>"><?= icon('mail') ?><?= h($CFG['email']) ?></a></div>
</div></section>
<footer class="site-f">
  <div class="wrap fgrid">
    <div class="fcol fcol-brand">
      <a class="brand brand-f" href="./"><?= logo_svg() ?><span class="brand-t"><?= h($CFG['brand']) ?></span></a>
      <p><?= h(fill($CFG['footer_blurb'])) ?></p>
      <?= contact_block() ?>
      <?php $soc = array_filter(['Facebook'=>$CFG['facebook'], 'Instagram'=>$CFG['instagram'], 'TikTok'=>$CFG['tiktok'], 'YouTube'=>$CFG['youtube']]); if($soc): ?>
      <p class="social"><?php foreach($soc as $n=>$u): ?><a href="<?= h($u) ?>" rel="noopener me" target="_blank"><?= h($n) ?></a><?php endforeach; ?></p>
      <?php endif; ?>
    </div>
    <div class="fcol"><h2>Puppies</h2><ul>
      <li><a href="<?= h(url(PUPPIES_PATH)) ?>">Beagle puppies for sale</a></li>
      <li><a href="<?= h(url('upcoming-litters')) ?>">Upcoming litters &amp; waitlist</a></li>
      <li><a href="<?= h(url('our-beagles')) ?>">Meet the parents</a></li>
      <?php foreach(array_slice(locations_on(), 0, 8, true) as $c=>$l): ?><li><a href="<?= h(location_url($c)) ?>">Beagle puppies in <?= h(states()[$c][0]) ?></a></li><?php endforeach; ?>
      <li><a href="<?= h(url('reviews')) ?>">Reviews</a></li>
    </ul></div>
    <div class="fcol"><h2>Buying with confidence</h2><ul>
      <?php foreach(['how-it-works', 'health-guarantee', 'whats-included', 'puppy-delivery', 'is-beagle-brook-legit', 'faq', 'about'] as $s): $pg = info_page($s); if($pg): ?>
      <li><a href="<?= h(url($s)) ?>"><?= h($pg['menu'] ?: $pg['title']) ?></a></li>
      <?php endif; endforeach; ?>
    </ul></div>
    <div class="fcol"><h2>Beagle guides</h2><ul>
      <?php foreach($guides as $a): ?><li><a href="<?= h(url(article_path($a))) ?>"><?= h(short_title($a['title'])) ?></a></li><?php endforeach; ?>
      <li><a href="<?= h(url('guides')) ?>"><strong>All guides</strong></a> · <a href="<?= h(url('blog')) ?>"><strong>Blog</strong></a></li>
    </ul></div>
  </div>
  <div class="wrap fbottom"><p>© <?= date('Y') ?> <?= h($CFG['brand']) ?> · <?= h($CFG['city'].', '.$CFG['state_name']) ?></p>
    <p><a href="<?= h(url('terms-of-sale')) ?>">Terms of sale</a> · <a href="<?= h(url('privacy-policy')) ?>">Privacy</a> · <a href="<?= h(url('contact')) ?>">Contact</a></p></div>
</footer>
<?php
}
/* "How Long Do Beagles Live? Beagle Lifespan and…" → "How Long Do Beagles Live?" for compact lists */
function short_title($t){ return preg_match('/^([^:]+?\?)\s/u', $t, $m) ? $m[1] : preg_split('/:\s|\s—\s|\s\(/u', $t, 2)[0]; }

/* ---------------- page bodies ---------------- */
function page_head($lede='', $class=''){
  global $h1;
  echo '<header class="phead wrap '.$class.'"><h1>'.h($h1).'</h1>'.($lede !== '' ? '<div class="lede">'.rich($lede).'</div>' : '').'</header>';
}

/* "Ask about a puppy": the inquiry form in a raised card, with what happens next beside it */
function inquiry_section($sel=null, $title='', $band=true){
  global $CFG;
  $title = $title !== '' ? $title : 'Ask about a puppy';
  ob_start(); ?>
  <section class="sec<?= $band ? ' band' : '' ?> inq-sec" id="inquire"><div class="wrap inq">
    <div class="inq-side">
      <p class="eyebrow"><?= icon('chat') ?>One-minute inquiry</p>
      <h2><?= h($title) ?></h2>
      <p>Choose your dog, leave your phone number and email, and add a note if you like. That’s it — <strong>no payment</strong>.</p>
      <ol class="mini-steps"><li><span>1</span>Choose your dog, or “not sure yet”</li><li><span>2</span>We call, text or email you <?= h(fill('{reply_time}')) ?></li><li><span>3</span>We answer every question and help you choose</li></ol>
      <?php if($CFG['phone'] !== ''): ?><p class="inq-call"><a class="btn btn-ghost" href="sms:<?= h(tel($CFG['phone'])) ?>"><?= icon('chat') ?>Text us</a><a class="btn btn-ghost" href="tel:<?= h(tel($CFG['phone'])) ?>"><?= icon('phone') ?><?= h($CFG['phone']) ?></a></p><?php endif; ?>
    </div>
    <div class="inq-form"><?= inquiry_form($sel) ?></div>
  </div></section>
  <?php return ob_get_clean();
}

function view_home(){
  global $SITE, $CFG;
  $avail = array_merge(puppies_with('available'), puppies_with('reserved'));
  $reviews = array_values(array_filter($SITE['reviews'], fn($r)=>empty($r['hidden'])));
  $parents = array_values(array_filter($SITE['parents'], fn($p)=>empty($p['hidden']) && empty($p['retired'])));
  /* the hero: an uploaded photo, else the newest puppies' photos, else the starter photo */
  $shots = [];
  if($CFG['hero_photo'] !== '') $shots[] = [$CFG['hero_photo'], 'Beagle puppies at '.$CFG['brand']];
  foreach($avail as $p) if(!empty($p['photos'][0]) && count($shots) < 3) $shots[] = [$p['photos'][0], $p['name'].', '.strtolower(puppy_kind($p))];
  if(!$shots) $shots[] = [SITE_HERO, 'Tricolor beagle puppy at '.$CFG['brand']];
  ?>
  <section class="hero"><div class="wrap hero-in">
    <div class="hero-t">
      <p class="eyebrow"><?= h($CFG['brand']) ?> · Purebred beagle puppies</p>
      <h1><?= h(fill($CFG['hero_title'])) ?></h1>
      <p class="lede"><?= h(fill($CFG['hero_lede'])) ?></p>
      <div class="cta-row"><a class="btn btn-lg" href="#puppies">Choose your puppy <?= icon('arrow') ?></a><a class="btn btn-lg btn-ghost" href="#inquire">Proceed now</a></div>
      <ul class="ticks"><li><?= icon('check') ?>Vet checked</li><li><?= icon('check') ?>Shots &amp; microchip</li><li><?= icon('check') ?>All documents, with names</li><li><?= icon('check') ?>Written health guarantee</li><li><?= icon('check') ?>Delivered to your door</li></ul>
    </div>
    <div class="hero-ph"><?= img_tag($shots[0][0], $shots[0][1], '(max-width:900px) 92vw, 480px', true) ?></div>
  </div></section>

  <section class="sec wrap" id="puppies">
    <div class="sec-h"><h2>Beagle puppies available now</h2><a href="<?= h(url(PUPPIES_PATH)) ?>">See all <?= count($avail) ?: '' ?> puppies <?= icon('arrow') ?></a></div>
    <?php if($avail): ?><p class="muted sec-sub">Tap <strong>Proceed now</strong> on any puppy to choose them, or <strong>Details</strong> for all their photos, papers and parents.</p><?php endif; ?>
    <?= puppies_block($avail, 9) ?>
    <?php if(count($avail) > 9): ?><p class="center"><a class="btn btn-lg btn-ghost" href="<?= h(url(PUPPIES_PATH)) ?>">See all <?= count($avail) ?> beagle puppies <?= icon('arrow') ?></a></p><?php endif; ?>
  </section>

  <section class="wrap easy3 easy3-after" aria-label="How to get your puppy">
    <a class="e3" href="#puppies"><span class="e3-n">1</span><span><strong>Choose your puppy</strong><small>Real photos, price, age and both parents</small></span></a>
    <a class="e3" href="#inquire"><span class="e3-n">2</span><span><strong>Proceed now, in one minute</strong><small>Phone and email, plus a note. No payment</small></span></a>
    <a class="e3" href="<?= h(url('puppy-delivery')) ?>"><span class="e3-n">3</span><span><strong>We call you, then deliver</strong><small>We reply <?= h(fill('{reply_time}')) ?>. Delivered to your door</small></span></a>
  </section>

  <?= inquiry_section() ?>

  <section class="sec"><div class="wrap">
    <div class="sec-h"><h2>Our promises to you</h2><a href="<?= h(url('health-guarantee')) ?>">Read the full health guarantee <?= icon('arrow') ?></a></div>
    <?= guarantees_block() ?>
  </div></section>

  <section class="sec band"><div class="wrap split">
    <div ><h2>How it works</h2><p>From inquiry to delivery day, in a few simple steps. Sending an inquiry is free and you’re never committed until you’ve talked to us and you’re sure.</p><?= steps_block() ?><p><a class="btn" href="<?= h(url('how-it-works')) ?>">How buying from us works</a></p></div>
    <div ><h2>Every puppy comes with</h2><?= included_block() ?><p class="muted">The price on each puppy’s page includes all of this. <a href="<?= h(url('whats-included')) ?>">What’s included</a></p></div>
  </div></section>

  <?php if($parents): ?>
  <section class="sec"><div class="wrap">
    <div class="sec-h"><h2>Meet the parents</h2><a href="<?= h(url('our-beagles')) ?>">All our beagles <?= icon('arrow') ?></a></div>
    <div class="parent-row"><?php foreach(array_slice($parents, 0, 6) as $p) echo parent_mini($p); ?></div>
  </div></section>
  <?php endif; ?>

  <?php if($reviews): ?>
  <section class="sec wrap">
    <div class="sec-h"><h2>What our families say</h2><a href="<?= h(url('reviews')) ?>">All reviews <?= icon('arrow') ?></a></div>
    <div class="rgrid"><?php foreach(array_slice($reviews, 0, 3) as $r) echo review_card($r); ?></div>
  </section>
  <?php endif; ?>

  <section class="sec wrap split split-c">
    <div ><h2>Delivered from <?= h($CFG['city']) ?>, <?= h($CFG['state_name']) ?> to your door</h2>
      <p><?= h(fill($CFG['delivery_note'])) ?> Puppies travel no earlier than <?= h(fill('{go_home_weeks}')) ?> old, after a check by our veterinarian.</p>
      <p class="cta-row"><a class="btn btn-ghost" href="<?= h(url('puppy-delivery')) ?>">How delivery works</a> <a class="btn btn-ghost" href="<?= h(url(PUPPIES_PATH.'/ohio')) ?>">Beagle puppies in Ohio</a></p></div>
    <div class="verify"><h2><?= icon('shield', 'ic ic-lg') ?>Buy with confidence</h2>
      <ul><li>Recent photos of your puppy and both parents, whenever you ask</li><li>A <strong>written agreement</strong> for your puppy, with our guarantees</li><li>We only contact you from <strong><?= h($CFG['email']) ?></strong></li><li>Registration numbers you can look up yourself</li></ul>
      <p><a href="<?= h(url('is-beagle-brook-legit')) ?>">How to verify us <?= icon('arrow') ?></a></p></div>
  </section>

  <?= home_knowledge() ?>

  <?= guide_links(['beagle-dog-breed', 'how-to-find-a-beagle-breeder', 'beagle-temperament', 'beagle-puppy-care', 'lemon-beagle', 'beagle-lifespan'], 'Thinking about a beagle? Start here') ?>

  <section class="sec band" id="waitlist-cta"><div class="wrap waitcta">
    <div><h2>Don’t see your puppy yet?</h2><p>New litters go to our waitlist first. Join free and we’ll email you the moment puppies are born.</p></div>
    <a class="btn btn-lg" href="<?= h(url('upcoming-litters')) ?>#waitlist">Join the waitlist</a>
  </div></section>
  <?php
}

/* The home page's answers for Google: a short answer, the breed at a glance and the questions
   people ask before buying a beagle puppy (also given to Google as FAQ structured data). */
function home_faqs(){
  global $CFG;
  return [
    ['How much is a beagle puppy from '.$CFG['brand'].'?', 'Each puppy’s price is listed on their card and their page. The price includes a vet check, age-appropriate shots, a microchip, starter food and all their documents. Delivery to your door is extra: '.delivery_summary().'.'],
    ['How do I get a beagle puppy from you?', 'Choose a puppy on our site and send the one-minute inquiry form with your phone number and email. Inquiring is free. We call, text or email you '.fill('{reply_time}').', answer every question, send more photos if you like and arrange delivery to your door.'],
    ['Do you deliver beagle puppies near me?', 'Yes. We deliver beagle puppies from '.$CFG['city'].', Ohio to your door: '.delivery_summary().'. Delivery times count from when your puppy leaves us, after a final check by our veterinarian.'],
    ['What comes with a '.$CFG['brand'].' beagle puppy?', 'Every puppy comes with a veterinarian check, age-appropriate shots and deworming, a microchip, a bag of the food they already eat, all their documents (registration papers, vet and vaccination records and microchip details, with names) and a written '.fill('{genetic_year}').' genetic health guarantee.'],
    ['How big do beagles get?', 'Beagles come in two sizes: 13-inch beagles stand up to 13 inches at the shoulder and usually weigh under 20 pounds, and 15-inch beagles stand 13 to 15 inches and weigh about 20 to 30 pounds. Most are fully grown by 12 to 18 months.'],
    ['How long do beagles live?', 'Most beagles live 12 to 15 years, and the AKC gives a life expectancy of 10 to 15 years. Keeping your beagle lean, active and up to date with vet checkups is the best way to help them live a long life.'],
    ['Are beagles good family dogs?', 'Yes. Beagles are friendly, gentle and playful, get along well with children and other dogs, and love being part of family life. They need daily walks, a securely fenced yard and patient training, because they follow their nose and can be vocal.'],
    ['When can a beagle puppy go home?', 'Our puppies go home when they are at least '.fill('{go_home_weeks}').' old, after a veterinarian has examined them. Each puppy’s page shows the date they are ready for delivery.'],
  ];
}
function home_knowledge(){
  global $CFG;
  $facts = [
    ['Size', 'Up to 13 in or 13–15 in at the shoulder', '13-inch-vs-15-inch-beagle'],
    ['Adult weight', 'Under 20 lb (13-inch) · 20–30 lb (15-inch)', 'beagle-growth-chart'],
    ['Lifespan', '12–15 years', 'beagle-lifespan'],
    ['Temperament', 'Friendly, curious, gentle with kids', 'beagle-temperament'],
    ['Colors', 'Tricolor, red & white, lemon & white and more', 'beagle-colors'],
    ['Coat & shedding', 'Short, easy to groom, sheds moderately', 'do-beagles-shed'],
    ['Exercise', 'About an hour a day', 'how-much-exercise-does-a-beagle-need'],
  ];
  ob_start(); ?>
  <section class="sec wrap know" id="beagle-puppies">
    <div class="know-in">
      <div >
        <h2>Beagle puppies: what to know before you buy</h2>
        <p class="answer"><?= h('A beagle puppy is a small, friendly scent hound that grows to about 20–30 pounds and lives 12–15 years. At '.$CFG['brand'].', every puppy is raised in our home in '.$CFG['city'].', Ohio, vet checked, vaccinated, microchipped and comes with all their documents, then is delivered to your door. Each puppy’s price is listed with them.') ?></p>
        <p>Beagles are one of America’s favorite breeds for good reason: they are cheerful, loving and great with children, small enough for most homes and easy to groom. They are also clever, food-driven hounds that need daily walks, a secure yard and patient, reward-based training. Our <a href="<?= h(url('guides/beagle-dog-breed')) ?>">beagle breed guide</a> covers everything in detail.</p>
        <h3 class="facts-h">The beagle at a glance</h3><div class="table-wrap"><table class="facts-t"><tbody>
          <?php foreach($facts as [$k, $v, $slug]): $href = url('guides/'.$slug); ?><tr><th scope="row"><?= h($k) ?></th><td><?= h($v) ?> <a href="<?= h($href) ?>" class="more-l" aria-label="More about beagle <?= h(strtolower($k)) ?>">More</a></td></tr><?php endforeach; ?>
        </tbody></table></div>
      </div>
      <div >
        <h2>Beagle puppy questions</h2>
        <div class="faqs"><?php foreach(home_faqs() as $i=>[$q, $a]): ?><details<?= $i === 0 ? ' open' : '' ?>><summary><h3><?= h($q) ?></h3></summary><p><?= h($a) ?></p></details><?php endforeach; ?></div>
        <p><a href="<?= h(url('faq')) ?>">More questions and answers <?= icon('arrow') ?></a></p>
      </div>
    </div>
  </section>
  <?php return ob_get_clean();
}

function view_puppies(){
  global $CFG, $filters;
  $all = public_puppies();
  $list = array_values(array_filter($all, fn($p)=>$p['status'] !== 'sold'
    && ($filters['sex'] === '' || $p['sex'] === $filters['sex'])
    && ($filters['color'] === '' || stripos($p['color'], $filters['color']) !== false)));
  usort($list, fn($a, $b)=>(($a['status'] === 'available') <=> ($b['status'] === 'available')) * -1 ?: strcmp($b['created'] ?? '', $a['created'] ?? ''));
  $sold = array_slice(array_values(array_filter($all, fn($p)=>$p['status'] === 'sold')), 0, 8);
  $colors = array_values(array_unique(array_filter(array_map(fn($p)=>ucwords(strtolower(trim($p['color']))), array_filter($all, fn($p)=>$p['status'] !== 'sold')))));
  page_head($CFG['puppies_intro']);
  ?>
  <section class="wrap">
    <?php if(count($all) > 3): ?>
    <form class="filters" method="get" action="<?= h(url(PUPPIES_PATH)) ?>" role="search" aria-label="Filter puppies">
      <?php if(empty($CFG['pretty_urls'])): ?><input type="hidden" name="path" value="<?= h(PUPPIES_PATH) ?>"><?php endif; ?>
      <label>Sex <select name="sex" onchange="this.form.submit()"><option value="">Any</option><option value="male"<?= $filters['sex'] === 'male' ? ' selected' : '' ?>>Male</option><option value="female"<?= $filters['sex'] === 'female' ? ' selected' : '' ?>>Female</option></select></label>
      <?php if(count($colors) > 1): ?><label>Color <select name="color" onchange="this.form.submit()"><option value="">Any</option><?php foreach($colors as $c): ?><option<?= strcasecmp($filters['color'], $c) === 0 ? ' selected' : '' ?>><?= h($c) ?></option><?php endforeach; ?></select></label><?php endif; ?>
      <noscript><button class="btn btn-sm">Filter</button></noscript>
      <?php if($filters['sex'] !== '' || $filters['color'] !== ''): ?><a href="<?= h(url(PUPPIES_PATH)) ?>">Clear filters</a><?php endif; ?>
    </form>
    <?php endif; ?>
    <?= puppies_block($list) ?>
  </section>
  <section class="sec wrap"><div class="trustrow">
    <div><?= icon('shield', 'ic ic-lg') ?><strong><?= h(fill('{vet_check_hour}')) ?> vet check &amp; <?= h(fill('{genetic_year}')) ?> genetic guarantee</strong><a href="<?= h(url('health-guarantee')) ?>">Health guarantee</a></div>
    <div><?= icon('chip', 'ic ic-lg') ?><strong>The puppy you choose is the puppy we deliver</strong><a href="<?= h(url('whats-included')) ?>">What’s included</a></div>
    <div><?= icon('chat', 'ic ic-lg') ?><strong>We reply <?= h(fill('{reply_time}')) ?></strong><a href="#inquire">Ask about a puppy</a></div>
    <div><?= icon('truck', 'ic ic-lg') ?><strong>Express 24-hour or Standard 2–5 day delivery</strong><a href="<?= h(url('puppy-delivery')) ?>">Delivery options</a></div>
  </div></section>
  <section class="wrap"><?= states_links() ?></section>
  <?= inquiry_section() ?>
  <?php if($sold): ?>
  <section class="sec wrap"><h2>Recently gone to loving homes</h2><div class="pgrid pgrid-sm"><?php foreach($sold as $p) echo puppy_card($p); ?></div></section>
  <?php endif; ?>
  <section class="sec wrap prose-sec"><div class="prose">
    <h2>Buying a beagle puppy from Beagle Brook</h2>
    <p>Every puppy listed here was born and raised in our home in <?= h($CFG['city']) ?>, <?= h($CFG['state_name']) ?>. Before they go home, each one is examined by a licensed veterinarian, given age-appropriate vaccinations and microchipped, and they travel with starter food and full registration papers. Every puppy page shows both parents, so you can see the size, color and temperament your puppy comes from.</p>
    <p>When you find the right puppy, choose them in the inquiry form and leave your phone number and email. We’ll call, text or email you <?= h(fill('{reply_time}')) ?> and answer every question.</p>
    <p>New to beagles? Read our <a href="<?= h(url('guides/beagle-dog-breed')) ?>">beagle breed guide</a> and <a href="<?= h(url('guides/how-to-find-a-beagle-breeder')) ?>">how to choose a beagle breeder</a>.</p>
  </div></section>
  <?php
}

function view_puppy(){
  global $puppy, $CFG, $errors, $SITE;
  $p = $puppy;
  $photos = array_values(array_filter($p['photos'] ?? [], fn($f)=>is_file(BB_ROOT.'/'.$f)));
  $sire = parent_by_id($p['sire'] ?? ''); $dam = parent_by_id($p['dam'] ?? '');
  $age = age_text($p['dob'] ?? '');
  $open = $p['status'] === 'available';
  $yt = youtube_id($p['video'] ?? '');
  ?>
  <article class="wrap puppy">
    <div class="pmedia">
      <div class="gal-main" id="gal-main"><?= photo_or_placeholder($photos[0] ?? '', $p['name'].', '.strtolower(puppy_kind($p)), '(max-width:900px) 100vw, 640px', true) ?></div>
      <?php if(count($photos) > 1): ?><div class="gal-thumbs"><?php foreach($photos as $i=>$f): ?>
        <button type="button" data-gal<?= $i === 0 ? ' aria-current="true"' : '' ?> aria-label="Photo <?= $i + 1 ?> of <?= h($p['name']) ?>"><?= str_replace(' loading="lazy"', '', img_tag($f, '', '96px')) ?><template><?= img_tag($f, $p['name'].', '.strtolower(puppy_kind($p)).', photo '.($i + 1), '(max-width:900px) 100vw, 640px') ?></template></button>
      <?php endforeach; ?></div><?php endif; ?>
      <?php $cfs = preg_match('#^https://customer-[a-z0-9]+\.cloudflarestream\.com/[a-f0-9]{32}/iframe$#', (string)($p['video'] ?? '')) ? $p['video'] : ''; ?>
      <?php if($cfs): ?><div class="video"><iframe src="<?= h($cfs) ?>" title="Video of <?= h($p['name']) ?>" loading="lazy" allow="accelerometer; gyroscope; encrypted-media; picture-in-picture" allowfullscreen></iframe></div>
      <?php elseif($yt): ?><div class="video"><iframe src="https://www.youtube-nocookie.com/embed/<?= h($yt) ?>" title="Video of <?= h($p['name']) ?>" loading="lazy" allow="accelerometer; encrypted-media; gyroscope; picture-in-picture" allowfullscreen></iframe></div>
      <?php elseif(($p['video'] ?? '') !== '' && preg_match('#^https://#', $p['video'])): ?><p><a class="btn btn-ghost" href="<?= h($p['video']) ?>" rel="noopener" target="_blank"><?= icon('video') ?>Watch <?= h($p['name']) ?>’s video</a></p><?php endif; ?>
    </div>
    <div class="pinfo">
      <p class="eyebrow"><?= status_badge($p) ?> <?= h(puppy_kind($p)) ?></p>
      <h1><?= h($p['name']) ?> <span>— <?= h(puppy_kind($p)) ?></span></h1>
      <?php if(($p['tagline'] ?? '') !== ''): ?><p class="lede"><?= h($p['tagline']) ?></p><?php endif; ?>
      <div class="pricebox">
        <?php if(($p['price'] ?? '') !== ''): ?><p class="price-lg"><?= h(money($p['price'])) ?></p>
        <p class="muted">Includes vet check, shots, microchip, starter food and all puppy documents.</p><?php endif; ?>
        <?php if(delivery_options()): ?><ul class="dlist"><?php foreach(delivery_options() as $m): $fee = delivery_base_fee($m['key']); ?><li><?= icon('truck') ?><span><strong><?= h($m['label']) ?></strong> <?= h($m['days']) ?></span><b><?= $fee !== null ? (count($SITE['delivery']['zones']) > 1 ? 'from ' : '').h(money($fee)) : 'Quoted' ?></b></li><?php endforeach; ?></ul><?php endif; ?>
        <?php if($open): ?><a class="btn btn-lg btn-block" href="#inquire">Proceed now with <?= h($p['name']) ?> <?= icon('arrow') ?></a>
        <?php elseif($p['status'] === 'reserved'): ?><p class="alert"><strong><?= h($p['name']) ?> is reserved.</strong> You can still send an inquiry to be first in line if the reservation falls through, or <a href="<?= h(url('upcoming-litters')) ?>#waitlist">join the waitlist</a>.</p><a class="btn btn-block btn-ghost" href="#inquire">Inquire as a backup</a>
        <?php else: ?><p class="alert"><strong><?= h($p['name']) ?> has found a loving home.</strong> See <a href="<?= h(url(PUPPIES_PATH)) ?>">available puppies</a> or <a href="<?= h(url('upcoming-litters')) ?>#waitlist">join the waitlist</a>.</p><?php endif; ?>
        <?php if($CFG['phone'] !== ''): ?><p class="pb-contact"><a href="sms:<?= h(tel($CFG['phone'])) ?>"><?= icon('chat') ?>Text us about <?= h($p['name']) ?></a><a href="tel:<?= h(tel($CFG['phone'])) ?>"><?= icon('phone') ?>Call</a></p><?php endif; ?>
      </div>
      <dl class="facts">
        <div><dt>Sex</dt><dd><?= h(sex_label($p['sex'])) ?></dd></div>
        <div><dt>Color</dt><dd><?= h($p['color']) ?><?= ($p['markings'] ?? '') !== '' ? ', '.h(strtolower($p['markings'])) : '' ?></dd></div>
        <?php if(($p['dob'] ?? '') !== ''): ?><div><dt>Born</dt><dd><?= h(nice_date($p['dob'])) ?><?= $age ? ' ('.h($age).' old)' : '' ?></dd></div><?php endif; ?>
        <?php if(($p['ready'] ?? '') !== ''): ?><div><dt>Ready for delivery</dt><dd><?= strtotime($p['ready']) > time() ? h(nice_date($p['ready'])) : 'Now' ?></dd></div><?php endif; ?>
        <?php if(size_text($p['size'] ?? '')): ?><div><dt>Expected size</dt><dd><?= h(size_text($p['size'])) ?></dd></div><?php endif; ?>
        <?php if(($p['weight'] ?? '') !== ''): ?><div><dt>Current weight</dt><dd><?= h($p['weight']) ?></dd></div><?php endif; ?>
        <?php if(($p['adult_weight'] ?? '') !== ''): ?><div><dt>Expected adult weight</dt><dd><?= h($p['adult_weight']) ?></dd></div><?php endif; ?>
        <?php if(!empty($p['champion'])): ?><div><dt>Bloodline</dt><dd>Champion bloodline</dd></div><?php endif; ?>
        <div><dt>Registration</dt><dd><?= h(trim(($p['registry'] ?? '' ?: $CFG['registry']).' full registration') === 'full registration' ? 'Full registration' : trim(($p['registry'] ?? '' ?: $CFG['registry']).' full registration')) ?><?= ($p['reg_no'] ?? '') !== '' ? ' · '.h($p['reg_no']) : '' ?></dd></div>
        <div><dt>Documents</dt><dd>All available, including names: registration papers, vet and vaccination records, microchip details</dd></div>
        <div><dt>Microchip</dt><dd><?= !isset($p['microchip']) || $p['microchip'] ? 'Yes' : 'Before delivery' ?></dd></div>
        <div><dt>Vet check</dt><dd><?= ($p['vet_date'] ?? '') !== '' ? 'Examined '.h(nice_date($p['vet_date'])) : 'Before delivery' ?></dd></div>
        <?php if(($p['vaccines'] ?? '') !== ''): ?><div><dt>Shots</dt><dd><?= nl2br(h($p['vaccines'])) ?></dd></div><?php endif; ?>
        <div><dt>Delivery</dt><dd>To your door from <?= h($CFG['city'].', '.$CFG['state']) ?> — <a href="<?= h(url('puppy-delivery')) ?>">options and prices</a></dd></div>
      </dl>
    </div>
  </article>

  <div class="wrap puppy-more">
    <div class="prose">
      <?php if(trim($p['description'] ?? '') !== ''): ?><h2>About <?= h($p['name']) ?></h2><?= rich($p['description']) ?><?php endif; ?>
      <?php if($sire || $dam || ($p['dam_text'] ?? '') !== '' || ($p['sire_text'] ?? '') !== ''): ?><h2><?= h($p['name']) ?>’s parents</h2><div class="parent-row"><?= $dam ? parent_mini($dam) : (($p['dam_text'] ?? '') !== '' ? '<span class="parent-mini"><span><small>Dam (mom)</small><strong>'.h($p['dam_text']).'</strong></span></span>' : '') ?><?= $sire ? parent_mini($sire) : (($p['sire_text'] ?? '') !== '' ? '<span class="parent-mini"><span><small>Sire (dad)</small><strong>'.h($p['sire_text']).'</strong></span></span>' : '') ?></div>
        <p class="muted">Both parents’ registration and health details are on <a href="<?= h(url('our-beagles')) ?>">our beagles</a> page. Ask us for more photos of them any time.</p><?php endif; ?>
      <h2>What comes with <?= h($p['name']) ?></h2><?= included_block() ?>
      <h2>Our guarantees</h2><?= guarantees_block() ?>
    </div>
  </div>

  <?php if($p['status'] !== 'sold') echo inquiry_section($p, $open ? 'Ask about '.$p['name'] : $p['name'].' is reserved: ask to be next in line'); ?>

  <?php $others = array_values(array_filter(puppies_with('available'), fn($x)=>$x['id'] !== $p['id'])); if($others): ?>
  <section class="sec wrap"><div class="sec-h"><h2>More beagle puppies available</h2><a href="<?= h(url(PUPPIES_PATH)) ?>">See all <?= icon('arrow') ?></a></div><?= puppies_block($others, 4) ?></section>
  <?php endif; ?>
  <div class="wrap"><?= guide_links(['beagle-puppy-care', 'beagle-puppy-checklist', 'beagle-training', 'best-dog-food-for-beagles'], 'Getting ready for your puppy') ?></div>
  <?php
}
function youtube_id($u){ return preg_match('#(?:youtube(?:-nocookie)?\.com/(?:watch\?v=|embed/|shorts/)|youtu\.be/)([\w-]{11})#', (string)$u, $m) ? $m[1] : ''; }

/* A state's delivery page: its own delivery price, distance, cities, climate tip and questions,
   the puppies available now, and the owner's local notes from Admin → Locations. */
/* a state page's own questions and answers (shown on the page and given to Google as FAQs) */
function location_faqs($loc){
  global $CFG;
  [$name, $cities] = states()[$loc];
  $home = $loc === 'OH'; $miles = miles_from_home($loc); $first = $cities[0];
  $listed = implode(', ', array_slice($cities, 0, -1)).' and '.end($cities);
  $fee_text = implode(' or ', array_map(fn($m)=>$m['label'].' ('.$m['days'].(($f = delivery_fee($loc, $m['key'])) !== null ? ', '.money($f) : '').')', delivery_options()));
  return [
    ['Do you deliver beagle puppies to '.$first.', '.$name.'?', 'Yes. We deliver across '.$name.', including '.$listed.': '.$fee_text.'. Delivery times count from when your puppy leaves us in '.$CFG['city'].'.'],
    ['How much is beagle puppy delivery to '.$name.'?', 'Delivery to '.$name.' is '.$fee_text.'. Puppy prices are shown on each puppy’s page and include the vet check, shots, microchip, starter food and full papers.'],
    ['Where are your beagle puppies raised?', $home ? 'In our home in '.$CFG['city'].', Ohio. Ohio families get a short, easy journey for their puppy.' : 'In our home in '.$CFG['city'].', Ohio, about '.number_format($miles).' miles from '.$first.' by road. We are not based in '.$name.'; we deliver to your door.'],
    ['Do puppies traveling to '.$name.' need a health certificate?', $home ? 'Puppies staying in Ohio are examined by our veterinarian before delivery and come with their health record.' : 'Puppies crossing state lines travel with a health certificate from our veterinarian, issued after a check shortly before departure.'],
  ];
}

function view_location(){
  global $loc, $SITE, $CFG, $h1, $extra_faqs;
  [$name, $cities, , , $climate] = states()[$loc];
  $home = $loc === 'OH';
  $miles = miles_from_home($loc);
  $first = $cities[0];
  $listed = implode(', ', array_slice($cities, 0, -1)).' and '.end($cities);
  $fees = []; foreach(delivery_options() as $m) $fees[] = [$m, delivery_fee($loc, $m['key'])];
  $fee_text = implode(' or ', array_map(fn($x)=>$x[0]['label'].' ('.$x[0]['days'].($x[1] !== null ? ', '.money($x[1]) : '').')', $fees));
  $avail = array_merge(puppies_with('available'), puppies_with('reserved'));
  $note = $SITE['locations'][$loc]['notes'] ?? '';
  $tip = ['hot'=>'Beagles handle '.$name.' summers well with shade, plenty of water and walks in the cooler morning and evening. Never leave a beagle in a parked car.',
          'cold'=>'Beagles cope with '.$name.' winters with a warm, dry place to sleep and shorter walks on the coldest days. Check paws for ice and road salt.',
          'mild'=>''.$name.'’s climate suits beagles well. Keep fresh water out on warm days and a dry bed in wet weather.',
          'seasons'=>'Beagles do well through '.$name.'’s four seasons: a warm, dry bed in winter, and shade and water in summer.'][$climate];
  $extra_faqs = location_faqs($loc);
  ?>
  <header class="phead wrap"><p class="eyebrow"><?= icon('truck') ?>Delivered to <?= h($name) ?> from <?= h($CFG['city']) ?>, Ohio</p><h1><?= h($h1) ?></h1></header>
  <div class="wrap"><div class="answer answer-page"><p><?= h($home
    ? 'Beagle Brook raises purebred beagle puppies in '.$CFG['city'].' and delivers them across Ohio, including '.$listed.'. Every puppy is vet checked, microchipped and sent home with shots, starter food and full papers. Delivery: '.$fee_text.'.'
    : 'Yes — Beagle Brook delivers purebred beagle puppies across '.$name.', including '.$listed.'. Our puppies are raised in '.$CFG['city'].', Ohio, vet checked, microchipped and sent with shots, starter food and full papers. Delivery: '.$fee_text.'.') ?></p></div></div>

  <section class="sec wrap"><div class="sec-h"><h2>Beagle puppies available for <?= h($name) ?> families</h2><a href="<?= h(url(PUPPIES_PATH)) ?>">All puppies <?= icon('arrow') ?></a></div><?= puppies_block($avail, 6) ?></section>

  <section class="sec band"><div class="wrap split">
    <div ><h2>Delivery to <?= h($name) ?></h2>
      <div class="table-wrap"><table><thead><tr><th scope="col">Option</th><th scope="col">Arrives</th><th scope="col">Price to <?= h($name) ?></th></tr></thead><tbody>
      <?php foreach($fees as [$m, $fee]): ?><tr><th scope="row"><?= h($m['label']) ?></th><td><?= h($m['days']) ?></td><td><strong><?= $fee !== null ? h(money($fee)) : 'Quoted on request' ?></strong></td></tr><?php endforeach; ?>
      </tbody></table></div>
      <p><?= $home ? 'We’re right here in '.h($CFG['city']).', so Ohio deliveries are short trips.' : h($first).' is about '.number_format($miles).' miles from '.h($CFG['city']).' by road. Delivery times count from when your puppy leaves us.' ?> Puppies travel no earlier than <?= h(fill('{go_home_weeks}')) ?> old, after a check by our veterinarian. <a href="<?= h(url('puppy-delivery')) ?>">How delivery works</a></p></div>
    <div ><h2>Bringing your beagle home in <?= h($name) ?></h2>
      <ul class="included">
        <li><?= icon('home', 'ic ic-lg') ?><div><strong>Cities we deliver to</strong><span><?= h($listed) ?>, and every town in between.</span></div></li>
        <li><?= icon('shield', 'ic ic-lg') ?><div><strong>Your local vet</strong><span>Book a check-up within <?= h(fill('{vet_check_hours}')) ?> of delivery to keep our health guarantee. Bring the health record we send.</span></div></li>
        <li><?= icon('doc', 'ic ic-lg') ?><div><strong>Local rules</strong><span>Check your city or county’s dog license and rabies vaccination rules; your vet can help.</span></div></li>
        <li><?= icon('heart', 'ic ic-lg') ?><div><strong>Weather</strong><span><?= h($tip) ?></span></div></li>
      </ul></div>
  </div></section>

  <?php if(trim($note) !== ''): ?><section class="sec wrap"><div class="prose"><?= rich($note, page_blocks()) ?></div></section><?php endif; ?>

  <section class="sec wrap"><div class="prose"><h2>Every puppy comes with</h2><?= included_block() ?>
    <h2>Questions from <?= h($name) ?> families</h2>
    <?php foreach($extra_faqs as [$q, $a]): ?><h3><?= h($q) ?></h3><p><?= h($a) ?></p><?php endforeach; ?>
    <p>See our <a href="<?= h(url('health-guarantee')) ?>">health guarantee</a> and <a href="<?= h(url('how-it-works')) ?>">how buying from us works</a>.</p>
  </div></section>

  <?= inquiry_section(null, 'Ask about a puppy for your '.$name.' home') ?>

  <?php
  /* the nearest other states, for people searching across a border */
  $others = array_keys(array_diff_key(locations_on(), [$loc=>1]));
  usort($others, fn($a, $b)=>miles_from_home($a) <=> miles_from_home($b));
  if($others): ?><section class="sec wrap"><div class="states"><p class="eyebrow"><?= icon('truck') ?>We also deliver to</p><ul><?php foreach(array_slice($others, 0, 10) as $c): ?><li><a href="<?= h(location_url($c)) ?>"><?= h(states()[$c][0]) ?></a></li><?php endforeach; ?><li><a href="<?= h(url(PUPPIES_PATH)) ?>">All puppies</a></li></ul></div></section><?php endif;
}

function view_our_beagles(){
  global $SITE, $CFG;
  $list = array_values(array_filter($SITE['parents'], fn($p)=>empty($p['hidden'])));
  usort($list, fn($a, $b)=>(!empty($a['retired']) <=> !empty($b['retired'])) ?: strcmp($a['role'], $b['role']));
  page_head($CFG['parents_intro']);
  ?>
  <section class="wrap">
  <?php if(!$list): ?><div class="empty"><p>Profiles of our adult beagles are being added. Ask us about any puppy’s parents and we’ll share their registration and health details.</p></div><?php endif; ?>
  <?php foreach($list as $p): $ph = array_values(array_filter($p['photos'] ?? [], fn($f)=>is_file(BB_ROOT.'/'.$f))); $kids = array_values(array_filter(public_puppies(), fn($x)=>$x['status'] !== 'sold' && (($x['sire'] ?? '') === $p['id'] || ($x['dam'] ?? '') === $p['id']))); ?>
    <article class="parent" id="<?= h($p['slug']) ?>">
      <div class="parent-ph"><?= photo_or_placeholder($ph[0] ?? '', $p['name'].', '.($p['role'] === 'sire' ? 'beagle sire' : 'beagle dam'), '(max-width:700px) 100vw, 420px') ?>
        <?php if(count($ph) > 1): ?><div class="parent-thumbs"><?php foreach(array_slice($ph, 1, 4) as $f) echo img_tag($f, $p['name'], '120px'); ?></div><?php endif; ?></div>
      <div>
        <p class="eyebrow"><?= $p['role'] === 'sire' ? 'Sire (dad)' : 'Dam (mom)' ?><?= !empty($p['retired']) ? ' · Retired' : '' ?></p>
        <h2><?= h($p['name']) ?></h2>
        <?php if(($p['registered_name'] ?? '') !== ''): ?><p class="regname"><?= h($p['registered_name']) ?></p><?php endif; ?>
        <dl class="facts">
          <?php if(($p['registry'] ?? '') !== '' || ($p['reg_no'] ?? '') !== ''): ?><div><dt>Registration</dt><dd><?= h(trim(($p['registry'] ?? '').' '.($p['reg_no'] ?? ''))) ?></dd></div><?php endif; ?>
          <?php if(($p['dob'] ?? '') !== ''): ?><div><dt>Born</dt><dd><?= h(nice_date($p['dob'])) ?></dd></div><?php endif; ?>
          <?php if(($p['color'] ?? '') !== ''): ?><div><dt>Color</dt><dd><?= h($p['color']) ?></dd></div><?php endif; ?>
          <?php if(($p['height'] ?? '') !== ''): ?><div><dt>Height</dt><dd><?= h($p['height']) ?></dd></div><?php endif; ?>
          <?php if(($p['weight'] ?? '') !== ''): ?><div><dt>Weight</dt><dd><?= h($p['weight']) ?></dd></div><?php endif; ?>
        </dl>
        <?php $tests = array_filter(array_map('trim', explode("\n", (string)($p['health'] ?? '')))); if($tests): ?>
          <h3>Health testing</h3><ul class="tests"><?php foreach($tests as $t): ?><li><?= icon('check') ?><?= h($t) ?></li><?php endforeach; ?></ul>
        <?php endif; ?>
        <?= rich($p['description'] ?? '') ?>
        <?php if($kids): ?><p><strong>Puppies available from <?= h($p['name']) ?>:</strong> <?= implode(', ', array_map(fn($k)=>'<a href="'.h(puppy_url($k)).'">'.h($k['name']).'</a>', $kids)) ?></p><?php endif; ?>
      </div>
    </article>
  <?php endforeach; ?>
  </section>
  <div class="wrap"><?= guide_links(['how-to-find-a-beagle-breeder', 'beagle-colors', 'beagle-lifespan'], 'Learn more') ?></div>
  <?php
}

function view_upcoming_litters(){
  global $SITE, $CFG, $errors;
  $litters = $SITE['litters'];
  usort($litters, fn($a, $b)=>strcmp($a['date'] ?? '', $b['date'] ?? ''));
  page_head($CFG['litters_intro']);
  $flash = $_SESSION['flash'] ?? ''; unset($_SESSION['flash']);
  ?>
  <section class="wrap">
    <?php if($litters): ?><div class="litters"><?php foreach($litters as $l): $s = parent_by_id($l['sire'] ?? ''); $d = parent_by_id($l['dam'] ?? ''); ?>
      <article class="litter"><p class="eyebrow"><span class="badge badge-<?= h($l['status']) ?>"><?= h(LITTER_STATUSES[$l['status']] ?? $l['status']) ?></span>
        <?= ($l['date'] ?? '') !== '' ? h(($l['status'] === 'born' ? 'Born ' : 'Expected ').date('F Y', strtotime($l['date'])).($l['status'] === 'born' ? '' : '')) : '' ?></p>
        <h2><?= h(($l['title'] ?? '') !== '' ? $l['title'] : trim(($d['name'] ?? 'Our dam').' × '.($s['name'] ?? 'our sire'))) ?></h2>
        <?php if($d || $s): ?><div class="parent-row"><?= $d ? parent_mini($d) : '' ?><?= $s ? parent_mini($s) : '' ?></div><?php endif; ?>
        <?= rich($l['notes'] ?? '') ?>
        <?php if(($l['price'] ?? '') !== ''): ?><p><strong>Expected price:</strong> <?= h($l['price']) ?></p><?php endif; ?>
      </article>
    <?php endforeach; ?></div>
    <?php else: ?><div class="empty"><p>Our next litters will be announced here. Join the waitlist below to hear first.</p></div><?php endif; ?>
  </section>
  <section class="sec band" id="waitlist"><div class="wrap inq">
    <div class="inq-side"><h2>Join the waitlist</h2><p>Be the first to know when puppies are born — before they’re posted on our site.</p>
      <ul class="ticks ticks-col"><li><?= icon('check') ?>Free, with no commitment</li><li><?= icon('check') ?>Tell us your preferences</li><li><?= icon('check') ?>Unsubscribe any time</li></ul></div>
    <div class="inq-form"><?php if($flash): ?><div class="alert alert-ok" role="status"><?= h($flash) ?></div><?= ga_event('sign_up', ['method'=>'waitlist']) ?><?php endif; ?><?= waitlist_form() ?></div>
  </div></section>
  <?php $now = puppies_with('available'); if($now): ?><section class="sec wrap"><div class="sec-h"><h2>Available right now</h2><a href="<?= h(url(PUPPIES_PATH)) ?>">See all <?= icon('arrow') ?></a></div><?= puppies_block($now, 4) ?></section><?php endif; ?>
  <?php
}

function view_reviews(){
  global $SITE, $CFG;
  $reviews = array_values(array_filter($SITE['reviews'], fn($r)=>empty($r['hidden'])));
  usort($reviews, fn($a, $b)=>strcmp($b['date'] ?? '', $a['date'] ?? ''));
  page_head($CFG['reviews_intro']);
  ?>
  <section class="wrap">
    <?php if($reviews): ?><div class="rgrid"><?php foreach($reviews as $r) echo review_card($r); ?></div>
    <?php else: ?><div class="empty"><p><strong>Reviews from our families will appear here.</strong> We publish every review with the family’s permission, exactly as they wrote it.</p></div><?php endif; ?>
    <div class="review-cta">
      <h2>Brought home a Beagle Brook puppy?</h2>
      <p>We’d love to hear how they’re doing. Send your review and a photo to <a href="mailto:<?= h($CFG['email']) ?>?subject=<?= rawurlencode('My review of '.$CFG['brand']) ?>"><?= h($CFG['email']) ?></a><?= $CFG['google_reviews_url'] !== '' ? ', or <a href="'.h($CFG['google_reviews_url']).'" rel="noopener" target="_blank">review us on Google</a>' : '' ?>.</p>
    </div>
  </section>
  <?php
}

function view_contact(){
  global $CFG;
  page_head($CFG['contact_intro']);
  ?>
  <section class="wrap split" id="inquire">
    <div ><?= inquiry_form() ?></div>
    <div ><h2>Get in touch</h2><?= contact_block() ?>
      <h2>Before you buy</h2><ul class="linklist"><li><a href="<?= h(url('how-it-works')) ?>">How it works</a></li><li><a href="<?= h(url('health-guarantee')) ?>">Health guarantee</a></li><li><a href="<?= h(url('faq')) ?>">FAQ</a></li><li><a href="<?= h(url('is-beagle-brook-legit')) ?>">How to verify us</a></li></ul></div>
  </section>
  <?php
}

const GUIDE_GROUPS = ['buying'=>'Buying a beagle puppy', 'breed'=>'The beagle breed', 'care'=>'Care, food and training', 'mixes'=>'Beagle mixes', 'culture'=>'Rescue and more'];
function view_guides(){
  page_head('Honest, practical guides from a beagle-only breeder: what beagles are really like, what they cost, how to care for them and how to buy a puppy safely.');
  $all = articles_of('guide');
  echo '<div class="wrap">';
  foreach(GUIDE_GROUPS as $g=>$label){
    $list = array_values(array_filter($all, fn($a)=>$a['group'] === $g));
    if(!$list) continue;
    echo '<section class="sec"><h2>'.h($label).'</h2><div class="agrid">';
    foreach($list as $a) echo article_card($a);
    echo '</div></section>';
  }
  $rest = array_values(array_filter($all, fn($a)=>!isset(GUIDE_GROUPS[$a['group']])));
  if($rest){ echo '<section class="sec"><h2>More guides</h2><div class="agrid">'; foreach($rest as $a) echo article_card($a); echo '</div></section>'; }
  echo '<p><a class="btn btn-ghost" href="'.h(url('blog')).'">Read the blog</a></p></div>';
}

function view_blog(){
  page_head('Puppy tips, stories and news from our home in Cincinnati, Ohio.');
  $list = articles_of('blog');
  usort($list, fn($a, $b)=>strcmp($b['date'], $a['date']));
  echo '<div class="wrap"><div class="agrid">';
  foreach($list as $a) echo article_card($a);
  echo '</div><p><a class="btn btn-ghost" href="'.h(url('guides')).'">See all beagle guides</a></p></div>';
}

function view_article(){
  global $art, $h1, $CFG;
  $toc = headings($art['body']);
  $words = word_count($art['body']);
  $zh = ($art['lang'] ?? 'en') !== 'en';
  ?>
  <article class="wrap article">
    <header class="phead">
      <p class="eyebrow"><?= icon('book') ?><?= $art['type'] === 'blog' ? 'Blog' : 'Beagle guide' ?></p>
      <h1><?= h($h1) ?></h1>
      <p class="meta">By <?= h($CFG['brand']) ?>, beagle breeder in <?= h($CFG['city'].', '.$CFG['state']) ?> · <?= ($art['updated'] ?? '') !== '' && $art['updated'] !== $art['date'] ? 'Updated <time datetime="'.h($art['updated']).'">'.h(nice_date($art['updated'])).'</time>' : '<time datetime="'.h($art['date']).'">'.h(nice_date($art['date'])).'</time>' ?><?= !$zh && $words > 300 ? ' · '.max(1, (int)round($words / 230)).' min read' : '' ?></p>
    </header>
    <?php if(($art['answer'] ?? '') !== ''): ?><div class="answer"><p><?= h(fill($art['answer'])) ?></p></div><?php endif; ?>
    <?php if(!$zh): ?><p class="funnel"><?= icon('paw') ?><span>Looking for a puppy? See our <a href="<?= h(url(PUPPIES_PATH)) ?>">beagle puppies for sale</a>, each listed with photos, price and both parents.</span></p><?php endif; ?>
    <?php if(($art['image'] ?? '') !== ''): ?><div class="art-img"><?= img_tag($art['image'], $art['image_alt'] ?? $art['title'], '(max-width:900px) 100vw, 860px', true) ?></div><?php endif; ?>
    <div class="art-grid">
      <div class="prose"><?= rich($art['body'], page_blocks()) ?><?php if(!$zh) echo price_cta_block(); ?></div>
      <aside class="art-side">
        <?php if(count($toc) > 3): ?><nav class="toc" aria-label="On this page"><p><strong><?= $zh ? '目录' : 'On this page' ?></strong></p><ol><?php foreach($toc as [$id, $t]): ?><li><a href="<?= h(url(article_path($art))) ?>#<?= h($id) ?>"><?= h($t) ?></a></li><?php endforeach; ?></ol></nav><?php endif; ?>
        <div class="side-cta"><?= logo_svg('side-logo') ?><p><strong>Looking for a beagle puppy?</strong></p><p>Vet checked, microchipped, full papers and a written health guarantee — delivered from <?= h($CFG['city']) ?>, <?= h($CFG['state']) ?>.</p><a class="btn btn-block" href="<?= h(url(PUPPIES_PATH)) ?>">See available puppies</a><a class="btn btn-block btn-ghost" href="<?= h(url('upcoming-litters')) ?>#waitlist">Join the waitlist</a></div>
      </aside>
    </div>
  </article>
  <?php $rel = related_articles($art); if($rel && !$zh): ?><section class="sec band"><div class="wrap"><h2>Keep reading</h2><div class="agrid"><?php foreach($rel as $a) echo article_card($a); ?></div></div></section><?php endif; ?>
  <?php
}

function view_info(){
  global $info;
  $wide = false;
  page_head('');
  if(($info['answer'] ?? '') !== '') echo '<div class="wrap"><div class="answer answer-page"><p>'.h(fill($info['answer'])).'</p></div></div>';
  echo '<div class="wrap"><div class="prose'.($wide ? ' prose-wide' : '').'">'.rich($info['body'], page_blocks()).'</div></div>';
  if($info['slug'] === 'puppy-delivery') echo '<div class="wrap">'.states_links('Delivery pages by state').'</div>';
  if($info['slug'] === 'faq' || $info['slug'] === 'health-guarantee') echo '<div class="wrap">'.guide_links(['how-to-find-a-beagle-breeder', 'beagle-puppy-care', 'beagle-health-problems'], 'Helpful guides').'</div>';
}

function view_thank_you(){
  global $CFG;
  $ref = (string)($_GET['ref'] ?? '');
  $mine = ($_SESSION['last_inquiry'] ?? '') === $ref;
  page_head('');
  ?>
  <section class="wrap prose">
    <?php if($mine){ $lead = inquiry_load($ref); echo ga_event('generate_lead', array_filter(['lead_type'=>($lead['kind'] ?? '') === 'contact' ? 'contact' : 'puppy_inquiry', 'item_name'=>$lead['puppy_name'] ?? '', 'value'=>($lead['puppy_price'] ?? '') !== '' ? (float)$lead['puppy_price'] : null, 'currency'=>($lead['puppy_price'] ?? '') !== '' ? 'USD' : null])); } ?>
    <div class="alert alert-ok" role="status"><p><strong>Thank you!</strong> <?= $mine ? 'Your reference is <strong>'.h($ref).'</strong>. ' : '' ?>We’ve emailed a copy to <?= $mine && !empty($lead['email']) ? '<strong>'.h($lead['email']).'</strong>' : 'you' ?> and will reply <?= h(fill('{reply_time}')) ?>.</p></div>
    <?php if($mine && $lead): ?><h2>What you sent us</h2><dl class="sent"><?php if(!empty($lead['puppy_name'])): ?><div><dt>Puppy</dt><dd><?= h($lead['puppy_name']) ?><?= ($lead['puppy_price'] ?? '') !== '' ? ' · '.h(money($lead['puppy_price'])) : '' ?></dd></div><?php endif; ?><div><dt>Phone</dt><dd><?= h($lead['phone'] ?? '') ?></dd></div><div><dt>Email</dt><dd><?= h($lead['email'] ?? '') ?></dd></div><?php if(($lead['notes'] ?? '') !== ''): ?><div><dt>Your note</dt><dd><?= nl2br(h($lead['notes'])) ?></dd></div><?php endif; ?></dl><?php endif; ?>
    <h2>What happens next</h2>
    <?= steps_block() ?>
    <div class="callout"><p><strong>Good to know:</strong> we only contact you from <strong><?= h($CFG['email']) ?></strong><?= $CFG['phone'] !== '' ? ' or '.h($CFG['phone']) : '' ?>. Can’t see our email? Check your spam folder and add <?= h($CFG['email']) ?> to your contacts.</p></div>
    <p><a class="btn" href="<?= h(url('guides/beagle-puppy-care')) ?>">Read our puppy care guide</a> <a class="btn btn-ghost" href="<?= h(url(PUPPIES_PATH)) ?>">Back to puppies</a></p>
  </section>
  <?php
}

/* a Google Analytics event, when Analytics is set up (Settings → Google & social) */
function ga_event($name, $params=[]){
  global $CFG;
  if(!preg_match('/^G-[A-Z0-9]{4,}$/', $CFG['ga4_id'])) return '';
  return '<script>if(window.gtag)gtag("event",'.json_encode($name).','.json_encode((object)$params, JSON_HEX_TAG).');</script>';
}

/* the buyer's sales agreement: read, accept online, print */
function view_agreement(){
  global $doc, $CFG, $errors;
  $q = $doc; $d = deal_of($q); $ag = $q['agreement'] ?? [];
  $flash = $_SESSION['flash'] ?? ''; unset($_SESSION['flash']);
  ?>
  <article class="wrap doc">
    <div class="doc-h"><?= logo_svg('doc-logo') ?><div><h1>Sales agreement</h1><p class="meta"><?= h($d['seller']) ?> · <?= h($d['puppy']) ?> · Reference <?= h($q['ref']) ?></p></div>
      <button class="btn btn-ghost btn-sm no-print" type="button" onclick="window.print()">Print / save as PDF</button></div>
    <?php foreach(agreement_sections($q) as [$hd, $paras, $items]): ?>
      <section><h2><?= h($hd) ?></h2><?php foreach($paras as $p): ?><p><?= h($p) ?></p><?php endforeach; ?>
      <?php if($items): ?><ul><?php foreach($items as $i): ?><li><?= h($i) ?></li><?php endforeach; ?></ul><?php endif; ?></section>
    <?php endforeach; ?>
    <p class="muted">Full health guarantee: <?= h(site_link('health-guarantee')) ?></p>
    <section id="accept" class="accept">
      <?php if($flash): ?><div class="alert alert-ok" role="status"><?= h($flash) ?></div><?php endif; ?>
      <?php if(!empty($ag['accepted'])): ?>
        <h2>Accepted</h2><p>Accepted online by <strong><?= h($ag['accepted_name']) ?></strong> on <?= h(date('F j, Y \a\t g:ia', strtotime($ag['accepted']))) ?>.</p>
      <?php else: ?>
        <h2>Accept this agreement</h2>
        <form class="form no-print" method="post" action="<?= h(url('agreement', ['ref'=>$q['ref'], 'k'=>sign('agreement|'.$q['ref'])])) ?>#accept">
          <input type="hidden" name="action" value="accept_agreement"><input type="hidden" name="ref" value="<?= h($q['ref']) ?>"><input type="hidden" name="k" value="<?= h(sign('agreement|'.$q['ref'])) ?>">
          <?php if(isset($errors['accept'])): ?><div class="alert alert-err" role="alert"><?= h($errors['accept']) ?></div><?php endif; ?>
          <label class="f"><span>Type your full name to sign</span><input type="text" name="signed_name" value="<?= val('signed_name') ?>" autocomplete="name" required></label>
          <label class="check"><input type="checkbox" name="agree" value="1" required><span>I have read this agreement, including the health guarantee, and I accept it.</span></label>
          <button class="btn btn-lg" type="submit">Accept agreement</button>
          <p class="fnote">Questions first? Email <?= h($CFG['email']) ?><?= $CFG['phone'] !== '' ? ' or call '.h($CFG['phone']) : '' ?>.</p>
        </form>
      <?php endif; ?>
      <div class="sign-lines print-only"><p>Buyer’s signature ____________________________ Date __________</p><p>Seller’s signature ____________________________ Date __________</p></div>
    </section>
  </article>
  <?php
}

/* the buyer's payment receipt: every payment recorded, with what is still to pay */
function view_receipt(){
  global $doc;
  $q = $doc; $d = deal_of($q); $t = deal_totals($q);
  ?>
  <article class="wrap doc">
    <div class="doc-h"><?= logo_svg('doc-logo') ?><div><h1>Payment receipt</h1><p class="meta"><?= h($d['seller']) ?> · <?= h($d['seller_place']) ?> · <?= h($d['seller_email']) ?></p></div>
      <button class="btn btn-ghost btn-sm no-print" type="button" onclick="window.print()">Print / save as PDF</button></div>
    <p><strong>Received from:</strong> <?= h($q['name']) ?>, <?= h($q['email']) ?><br><strong>For:</strong> <?= h(trim($d['puppy'].', '.trim($d['color'].' '.strtolower($d['sex'])).' beagle puppy', ', ')) ?><br><strong>Reference:</strong> <?= h($q['ref']) ?></p>
    <?php if(empty($q['payments'])): ?><p>No payments have been recorded yet.</p><?php else: ?>
    <div class="table-wrap"><table><thead><tr><th scope="col">Receipt</th><th scope="col">Date</th><th scope="col">Method</th><th scope="col">Amount</th></tr></thead><tbody>
      <?php foreach($q['payments'] as $p): ?><tr><th scope="row"><?= h($q['ref'].'-'.$p['n']) ?></th><td><?= h(nice_date($p['date'])) ?></td><td><?= h($p['method']) ?></td><td><?= h(money($p['amount'])) ?></td></tr><?php endforeach; ?>
    </tbody></table></div><?php endif; ?>
    <dl class="totals doc-totals">
      <div><dt>Puppy price</dt><dd><?= h(money($d['price'])) ?></dd></div>
      <?php if($d['delivery'] !== ''): ?><div><dt><?= h($d['delivery']) ?></dt><dd><?= $d['delivery_fee'] !== null ? h(money($d['delivery_fee'])) : 'to be confirmed' ?></dd></div><?php endif; ?>
      <div class="tot"><dt>Total</dt><dd><?= h(money($t['total'])) ?></dd></div>
      <div><dt>Paid</dt><dd><?= h(money($t['paid'])) ?></dd></div>
      <div class="tot"><dt>Still to pay</dt><dd><?= h(money($t['due'])) ?></dd></div>
    </dl>
    <p class="muted">Thank you. Keep this receipt with your sales agreement. Questions: <?= h($d['seller_email']) ?></p>
  </article>
  <?php
}

function view_unsubscribe(){
  global $unsub_done;
  page_head('');
  echo '<section class="wrap prose"><p>'.($unsub_done ? 'You won’t receive any more waitlist emails from us. Changed your mind? You can <a href="'.h(url('upcoming-litters')).'#waitlist">join again</a> any time.' : 'This unsubscribe link isn’t valid. Please email us and we’ll remove you straight away.').'</p></section>';
}

function view_notfound(){
  page_head('');
  echo '<section class="wrap prose"><p>Sorry, we couldn’t find that page. It may have moved, or the puppy may have found a home.</p><p><a class="btn" href="'.h(url(PUPPIES_PATH)).'">See available puppies</a> <a class="btn btn-ghost" href="'.h(url('guides')).'">Beagle guides</a></p></section>';
}
