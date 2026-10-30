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
  foreach(['vet'=>'shield', 'genetic'=>'heart', 'real puppy'=>'video', 'video'=>'video', 'delivery'=>'truck', 'deliver'=>'truck', 'paper'=>'doc', 'deposit'=>'lock',
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
/* prices for the browser, so the inquiry form can show the total as soon as a state is chosen */
function delivery_js_data(){
  global $SITE;
  return json_encode(['zones'=>array_map(fn($z)=>['states'=>array_map('trim', explode(',', strtoupper((string)$z['states']))), 'fees'=>array_map(fn($m)=>is_numeric($z[$m['key']] ?? '') ? (float)$z[$m['key']] : null, array_column(delivery_options(), null, 'key'))], $SITE['delivery']['zones'])], JSON_HEX_TAG|JSON_HEX_AMP);
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

function page_blocks(){
  return ['{guarantees}'=>guarantees_block(), '{steps}'=>steps_block(), '{included}'=>included_block(), '{payments}'=>payments_block(), '{delivery_rates}'=>delivery_rates_block(),
          '{puppies}'=>puppies_block(), '{licenses}'=>licenses_block(), '{payee}'=>payee_block(), '{breeder}'=>breeder_block(), '{contact}'=>contact_block()];
}

/* ---------------- cards ---------------- */
function puppy_card($p){
  $age = age_text($p['dob'] ?? '');
  $o = '<article class="pcard"><a class="pcard-ph" href="'.h(puppy_url($p)).'" tabindex="-1" aria-hidden="true">'.photo_or_placeholder($p['photos'][0] ?? '', $p['name'].', '.strtolower(puppy_kind($p))).status_badge($p).'</a>';
  $o .= '<div class="pcard-b"><h3><a href="'.h(puppy_url($p)).'">'.h($p['name']).'</a></h3>';
  $o .= '<p class="pcard-kind">'.h(trim(($p['color'] ?? '').' · '.sex_label($p['sex'] ?? ''), ' ·')).'</p>';
  $o .= '<ul class="pcard-facts">'.($age ? '<li>'.icon('calendar').h($age).' old</li>' : '').(($p['ready'] ?? '') !== '' && strtotime($p['ready']) > time() ? '<li>'.icon('home').'Ready '.h(date('M j', strtotime($p['ready']))).'</li>' : (($p['status'] ?? '') === 'available' ? '<li>'.icon('home').'Ready now</li>' : '')).'</ul>';
  $o .= '<div class="pcard-f"><span class="price">'.(($p['price'] ?? '') !== '' ? h(money($p['price'])) : '').'</span><a class="btn btn-sm" href="'.h(puppy_url($p)).'">'.(($p['status'] ?? '') === 'sold' ? 'See '.h($p['name']) : 'Meet '.h($p['name'])).'</a></div>';
  return $o.'</div></article>';
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
  $list = array_filter(array_map(fn($s)=>article($s, 'guide') ?? article($s, 'blog'), $slugs));
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

function inquiry_form($p){
  global $CFG, $old, $errors, $PATH;
  $pays = payments_on();
  ob_start(); ?>
  <form class="form" method="post" action="<?= h(puppy_url($p)) ?>#inquire" novalidate>
    <input type="hidden" name="action" value="inquiry"><input type="hidden" name="puppy" value="<?= h($p['id']) ?>"><input type="hidden" name="page" value="<?= h($PATH) ?>">
    <?= spam_fields() ?><?= form_error() ?>
    <fieldset><legend>Your details</legend>
      <div class="frow"><?= field('name', 'Full name', 'text', true, 'autocomplete="name"') ?></div>
      <div class="frow frow-2"><?= field('email', 'Email', 'email', true, 'autocomplete="email"') ?><?= field('phone', 'Phone', 'tel', true, 'autocomplete="tel" placeholder="(555) 555-0123"') ?></div>
    </fieldset>
    <fieldset><legend>Delivery address</legend>
      <p class="fhelp">We deliver every puppy to your door, so we need the address to quote your delivery.</p>
      <div class="frow"><?= field('address1', 'Street address', 'text', true, 'autocomplete="address-line1"') ?></div>
      <div class="frow"><?= field('address2', 'Apartment, suite, etc.', 'text', false, 'autocomplete="address-line2"') ?></div>
      <div class="frow frow-3"><?= field('city', 'City', 'text', true, 'autocomplete="address-level2"') ?><?= state_select() ?><?= field('zip', 'ZIP code', 'text', true, 'autocomplete="postal-code" inputmode="numeric" maxlength="10"') ?></div>
    </fieldset>
    <?php $dopts = delivery_options(); if($dopts): ?>
    <fieldset class="<?= isset($errors['delivery']) ? 'f-bad' : '' ?>" data-delivery="<?= h(delivery_js_data()) ?>"><legend>Delivery option <b aria-hidden="true">*</b></legend>
      <p class="fhelp">Delivery times count from when <?= h($p['name']) ?> leaves us in <?= h($CFG['city'].', '.$CFG['state']) ?>.</p>
      <div class="dopts">
        <?php foreach($dopts as $i=>$m): $fee = delivery_fee($old['state'] ?? '', $m['key']) ?? delivery_base_fee($m['key']); ?>
        <label class="dopt"><input type="radio" name="delivery" value="<?= h($m['key']) ?>"<?= ($old['delivery'] ?? ($i === 0 ? $m['key'] : '')) === $m['key'] ? ' checked' : '' ?> required>
          <span><strong><?= h($m['label']) ?></strong><small><?= h($m['days']) ?></small><em data-fee="<?= h($m['key']) ?>"><?= $fee !== null ? h(money($fee)) : 'Quoted on request' ?></em></span></label>
        <?php endforeach; ?>
      </div><?= err('delivery') ?>
      <?php if(($p['price'] ?? '') !== ''): ?>
      <dl class="totals" data-price="<?= h((float)$p['price']) ?>" data-deposit="<?= h(deposit_amount($p['price'])) ?>">
        <div><dt><?= h($p['name']) ?></dt><dd><?= h(money($p['price'])) ?></dd></div>
        <div><dt>Delivery</dt><dd data-t="fee">—</dd></div>
        <div class="tot"><dt>Total</dt><dd data-t="total">—</dd></div>
        <div class="sub"><dt><?= h(fill('{deposit}')) ?> deposit, after we’ve talked</dt><dd><?= h(money(deposit_amount($p['price']))) ?></dd></div>
        <div class="sub"><dt>Due at delivery</dt><dd data-t="due">—</dd></div>
      </dl>
      <?php endif; ?>
    </fieldset>
    <?php endif; ?>
    <fieldset class="<?= isset($errors['payment']) ? 'f-bad' : '' ?>"><legend>How would you like to pay? <b aria-hidden="true">*</b></legend>
      <p class="fhelp">Nothing is paid now. We send payment details from <?= h($CFG['email']) ?> only after we’ve talked and you’ve seen <?= h($p['name']) ?> on video.</p>
      <div class="chips">
        <?php foreach($pays as $pm): ?><label class="chip"><input type="radio" name="payment" value="<?= h($pm['key']) ?>"<?= ($old['payment'] ?? '') === $pm['key'] ? ' checked' : '' ?> required><span><?= h($pm['name']) ?></span></label><?php endforeach; ?>
      </div><?= err('payment') ?>
    </fieldset>
    <fieldset><legend>A little more <i>(optional)</i></legend>
      <div class="frow frow-2">
        <label class="f"><span>When would you like delivery?</span><select name="timing"><?php foreach(['', 'As soon as possible', 'Within a month', 'In 1–3 months', 'Just exploring for now'] as $o): ?><option<?= ($old['timing'] ?? '') === $o ? ' selected' : '' ?> value="<?= h($o) ?>"><?= $o === '' ? 'Choose…' : h($o) ?></option><?php endforeach; ?></select></label>
        <label class="f"><span>Best way to reach you</span><select name="contact_pref"><?php foreach(['', 'Call', 'Text', 'Email'] as $o): ?><option<?= ($old['contact_pref'] ?? '') === $o ? ' selected' : '' ?> value="<?= h($o) ?>"><?= $o === '' ? 'Any' : h($o) ?></option><?php endforeach; ?></select></label>
      </div>
      <label class="f"><span>Personal notes</span><textarea name="notes" rows="5" maxlength="3000" placeholder="Tell us about your family and home: children or other pets, a fenced yard, your experience with dogs, any questions for us…"><?= val('notes') ?></textarea></label>
    </fieldset>
    <label class="check<?= isset($errors['agree']) ? ' f-bad' : '' ?>"><input type="checkbox" name="agree" value="1"<?= !empty($old['agree']) ? ' checked' : '' ?> required>
      <span>I understand that once we’ve talked, a <?= h(fill('{deposit}')) ?> deposit<?= ($p['price'] ?? '') !== '' ? ' ('.h(money(deposit_amount($p['price']))).')' : '' ?> reserves <?= h($p['name']) ?>, and the remaining balance and the delivery fee are due at delivery. <a href="<?= h(url('deposit-refund-policy')) ?>">Deposit policy</a></span></label><?= err('agree') ?>
    <button class="btn btn-lg btn-block" type="submit">Send my inquiry for <?= h($p['name']) ?></button>
    <p class="fnote"><?= icon('lock') ?> No payment now. We reply <?= h(fill('{reply_time}')) ?>. Your details are only used to arrange your puppy — <a href="<?= h(url('privacy-policy')) ?>">privacy policy</a>.</p>
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

function contact_form(){
  global $PATH;
  ob_start(); ?>
  <form class="form" method="post" action="<?= h(url('contact')) ?>#message" novalidate>
    <input type="hidden" name="action" value="contact"><input type="hidden" name="page" value="<?= h($PATH) ?>"><?= spam_fields() ?><?= form_error() ?>
    <div class="frow frow-2"><?= field('name', 'Name', 'text', true, 'autocomplete="name"') ?><?= field('email', 'Email', 'email', true, 'autocomplete="email"') ?></div>
    <div class="frow"><?= field('phone', 'Phone', 'tel', false, 'autocomplete="tel"') ?></div>
    <label class="f<?= isset($GLOBALS['errors']['notes']) ? ' f-bad' : '' ?>"><span>Message <b aria-hidden="true">*</b></span><textarea name="notes" rows="6" maxlength="3000" required><?= val('notes') ?></textarea><?= err('notes') ?></label>
    <button class="btn btn-lg" type="submit">Send message</button>
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
  $faq_text = $page === 'article' ? $art['body'] : ($page === 'info' ? $info['body'] : '');
  if($faq_text !== '' && ($faqs = text_faqs($faq_text))){
    $g[] = ['@type'=>'FAQPage', 'mainEntity'=>array_map(fn($q)=>['@type'=>'Question', 'name'=>$q[0], 'acceptedAnswer'=>['@type'=>'Answer', 'text'=>$q[1]]], $faqs)];
  }
  return '<script type="application/ld+json">'.json_encode(['@context'=>'https://schema.org', '@graph'=>$g], JSON_UNESCAPED_SLASHES|JSON_UNESCAPED_UNICODE|JSON_HEX_TAG).'</script>';
}
function share_image(){ global $CFG; return $CFG['share_photo'] !== '' && is_file(BB_ROOT.'/'.$CFG['share_photo']) ? $CFG['share_photo'] : 'assets/share.jpg'; }

/* ---------------- layout ---------------- */
function nav_items(){
  return [[PUPPIES_PATH, 'Puppies for sale'], ['upcoming-litters', 'Upcoming litters'], ['our-beagles', 'Our beagles'], ['how-it-works', 'How it works'],
          ['health-guarantee', 'Guarantee'], ['guides', 'Guides'], ['reviews', 'Reviews'], ['contact', 'Contact']];
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
<link rel="stylesheet" href="assets/style.css?v=<?= (int)$css_v ?>">
<?= structured_data() ?>
<?php if(preg_match('/^G-[A-Z0-9]{4,}$/', $CFG['ga4_id'])): ?>
<script async src="https://www.googletagmanager.com/gtag/js?id=<?= h($CFG['ga4_id']) ?>"></script>
<script>window.dataLayer=window.dataLayer||[];function gtag(){dataLayer.push(arguments)}gtag('js',new Date());gtag('config','<?= h($CFG['ga4_id']) ?>');</script>
<?php endif; ?>
</head>
<body class="pg-<?= h($page) ?>">
<a class="skip" href="#main">Skip to content</a>
<div class="topbar"><div class="wrap"><span><?= icon('truck') ?>Delivered to your door from <?= h($CFG['city'].', '.$CFG['state']) ?></span><span class="hide-sm"><?= icon('shield') ?><?= h(fill('{genetic_year}')) ?> health guarantee</span><span class="hide-sm"><?= icon('doc') ?>Full papers included</span></div></div>
<header class="site-h">
  <div class="wrap hrow">
    <a class="brand" href="./" aria-label="<?= h($CFG['brand']) ?> home"><?= logo_svg() ?><span class="brand-t"><?= h($CFG['brand']) ?><small>Beagle puppies · <?= h($CFG['city']) ?>, <?= h($CFG['state']) ?></small></span></a>
    <button class="navbtn" type="button" aria-expanded="false" aria-controls="nav"><?= icon('menu') ?><span>Menu</span></button>
    <nav id="nav" class="nav" aria-label="Main">
      <?php foreach(nav_items() as [$path, $label]): ?><a href="<?= h(url($path)) ?>"<?= $PATH === $path || ($path === PUPPIES_PATH && $page === 'puppy') || ($path === 'guides' && $page === 'article') ? ' aria-current="page"' : '' ?>><?= h($label) ?></a><?php endforeach; ?>
    </nav>
    <a class="btn btn-sm hcta" href="<?= h(url(PUPPIES_PATH)) ?>">See puppies</a>
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
<?php if($CFG['phone'] !== ''): ?>
<div class="mbar"><a href="sms:<?= h(tel($CFG['phone'])) ?>"><?= icon('chat') ?>Text us</a><a href="tel:<?= h(tel($CFG['phone'])) ?>"><?= icon('phone') ?>Call</a><a class="mbar-main" href="<?= h(url(PUPPIES_PATH)) ?>"><?= icon('paw') ?>Puppies</a></div>
<?php else: ?>
<div class="mbar"><a href="mailto:<?= h($CFG['email']) ?>"><?= icon('mail') ?>Email us</a><a class="mbar-main" href="<?= h(url(PUPPIES_PATH)) ?>"><?= icon('paw') ?>See puppies</a></div>
<?php endif; ?>
<script>
(function(){var b=document.querySelector('.navbtn'),n=document.getElementById('nav');if(b)b.addEventListener('click',function(){var o=n.classList.toggle('open');b.setAttribute('aria-expanded',o)});
document.querySelectorAll('[data-gal]').forEach(function(t){t.addEventListener('click',function(){var m=document.getElementById('gal-main');if(!m)return;m.innerHTML=t.querySelector('template').innerHTML;document.querySelectorAll('[data-gal]').forEach(function(x){x.removeAttribute('aria-current')});t.setAttribute('aria-current','true')})});
document.querySelectorAll('form.form').forEach(function(f){f.addEventListener('submit',function(){var s=f.querySelector('[type=submit]');setTimeout(function(){s.disabled=true;s.textContent='Sending…'},10)})});
var bad=document.querySelector('.f-bad input,.f-bad select,.f-bad textarea');if(bad)bad.focus();
var fs=document.querySelector('[data-delivery]');if(fs){var D=JSON.parse(fs.getAttribute('data-delivery')),f=fs.closest('form'),st=f.querySelector('[name=state]'),tl=fs.querySelector('.totals'),
money=function(v){return '$'+v.toLocaleString('en-US',{minimumFractionDigits:v%1?2:0,maximumFractionDigits:2})},
zone=function(s){for(var i=0;i<D.zones.length;i++){var z=D.zones[i];if(z.states.indexOf('*')>-1||z.states.indexOf(s)>-1)return z}return null},
upd=function(){var z=zone(st?st.value:''),k=(f.querySelector('[name=delivery]:checked')||{}).value;
fs.querySelectorAll('[data-fee]').forEach(function(e){var v=z?z.fees[e.getAttribute('data-fee')]:null;if(v!=null)e.textContent=money(v);else if(z)e.textContent='Quoted on request'});
if(!tl)return;var fee=z&&k?z.fees[k]:null,p=+tl.getAttribute('data-price'),d=+tl.getAttribute('data-deposit'),q=function(n){return tl.querySelector('[data-t='+n+']')};
q('fee').textContent=fee==null?(z?'Quoted on request':'Choose your state'):money(fee);q('total').textContent=fee==null?money(p)+' + delivery':money(p+fee);q('due').textContent=fee==null?money(p-d)+' + delivery':money(p-d+fee)};
f.addEventListener('change',upd);upd()}})();
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
  <div><h2>Questions? Talk to a real beagle breeder.</h2><p>We reply <?= h(fill('{reply_time}')) ?>. No pressure, and no payment until you’ve seen your puppy live.</p></div>
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
      <li><a href="<?= h(url('beagle-puppies-for-sale-ohio')) ?>">Beagle puppies in Ohio</a></li>
      <li><a href="<?= h(url('reviews')) ?>">Reviews</a></li>
    </ul></div>
    <div class="fcol"><h2>Buying with confidence</h2><ul>
      <?php foreach(['how-it-works', 'health-guarantee', 'whats-included', 'puppy-delivery', 'deposit-refund-policy', 'is-beagle-brook-legit', 'faq', 'about'] as $s): $pg = info_page($s); if($pg): ?>
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

function view_home(){
  global $SITE, $CFG;
  $avail = array_merge(puppies_with('available'), puppies_with('reserved'));
  $reviews = array_values(array_filter($SITE['reviews'], fn($r)=>empty($r['hidden'])));
  $parents = array_values(array_filter($SITE['parents'], fn($p)=>empty($p['hidden']) && empty($p['retired'])));
  $hero = $CFG['hero_photo'] !== '' ? img_tag($CFG['hero_photo'], 'Beagle puppies at '.$CFG['brand'], '(max-width:900px) 100vw, 560px', true) : '';
  if($hero === '' && $avail && !empty($avail[0]['photos'][0])) $hero = img_tag($avail[0]['photos'][0], $avail[0]['name'].', '.strtolower(puppy_kind($avail[0])), '(max-width:900px) 100vw, 560px', true);
  if($hero === '') $hero = img_tag(SITE_HERO, 'Tricolor beagle puppy at '.$CFG['brand'], '(max-width:900px) 100vw, 560px', true);
  ?>
  <section class="hero"><div class="wrap hero-in">
    <div class="hero-t">
      <p class="eyebrow"><?= icon('paw') ?><?= h($CFG['brand']) ?> · <?= h($CFG['city'].', '.$CFG['state_name']) ?></p>
      <h1><?= h(fill($CFG['hero_title'])) ?></h1>
      <p class="lede"><?= h(fill($CFG['hero_lede'])) ?></p>
      <div class="cta-row"><a class="btn btn-lg" href="<?= h(url(PUPPIES_PATH)) ?>">See available puppies <?= icon('arrow') ?></a><a class="btn btn-lg btn-ghost" href="<?= h(url('upcoming-litters')) ?>#waitlist">Join the waitlist</a></div>
      <ul class="ticks"><li><?= icon('check') ?>Vet checked</li><li><?= icon('check') ?>Shots &amp; microchip</li><li><?= icon('check') ?>Full papers</li><li><?= icon('check') ?>Written health guarantee</li><li><?= icon('check') ?>Delivered to your door</li></ul>
    </div>
    <div class="hero-m"><?= $hero !== '' ? '<div class="hero-ph">'.$hero.'</div>' : '<div class="hero-card">'.logo_svg('hero-logo').'<p><strong>Purebred beagles only.</strong><br>Raised in our home, delivered to yours.</p></div>' ?></div>
  </div></section>

  <section class="sec wrap" id="puppies">
    <div class="sec-h"><h2>Beagle puppies available now</h2><a href="<?= h(url(PUPPIES_PATH)) ?>">See all puppies <?= icon('arrow') ?></a></div>
    <?= puppies_block($avail, 6) ?>
  </section>

  <section class="sec band"><div class="wrap">
    <div class="sec-h"><h2>Our promises to you</h2><a href="<?= h(url('health-guarantee')) ?>">Read the full health guarantee <?= icon('arrow') ?></a></div>
    <?= guarantees_block() ?>
  </div></section>

  <section class="sec wrap split">
    <div><h2>How it works</h2><p>From inquiry to delivery day, in six simple steps. No payment until you’ve spoken to us and met your puppy on a live video call.</p><?= steps_block() ?><p><a class="btn" href="<?= h(url('how-it-works')) ?>">How buying from us works</a></p></div>
    <div><h2>Every puppy comes with</h2><?= included_block() ?><p class="muted">The price on each puppy’s page includes all of this. <a href="<?= h(url('whats-included')) ?>">What’s included</a></p></div>
  </section>

  <?php if($parents): ?>
  <section class="sec band"><div class="wrap">
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
    <div><h2>Delivered from <?= h($CFG['city']) ?>, <?= h($CFG['state_name']) ?> to your door</h2>
      <p><?= h(fill($CFG['delivery_note'])) ?> Puppies travel no earlier than <?= h(fill('{go_home_weeks}')) ?> old, after a check by our veterinarian.</p>
      <p><a class="btn btn-ghost" href="<?= h(url('puppy-delivery')) ?>">How delivery works</a> <a class="btn btn-ghost" href="<?= h(url('beagle-puppies-for-sale-ohio')) ?>">Beagle puppies in Ohio</a></p></div>
    <div class="verify"><h2><?= icon('shield', 'ic ic-lg') ?>Buy with confidence</h2>
      <ul><li>See your puppy on a <strong>live video call</strong> before any payment</li><li>A <strong>written sales agreement</strong> and receipt for every payment</li><li>Payment details only from <strong><?= h($CFG['email']) ?></strong></li><li>We <strong>never</strong> ask for gift cards or crypto</li></ul>
      <p><a href="<?= h(url('is-beagle-brook-legit')) ?>">How to verify us before you pay <?= icon('arrow') ?></a></p></div>
  </section>

  <?= guide_links(['beagle-price', 'beagle-dog-breed', 'how-to-find-a-beagle-breeder', 'beagle-puppy-care', 'lemon-beagle', 'beagle-lifespan'], 'Thinking about a beagle? Start here') ?>

  <section class="sec band" id="waitlist-cta"><div class="wrap waitcta">
    <div><h2>Don’t see your puppy yet?</h2><p>New litters go to our waitlist first. Join free and we’ll email you the moment puppies are born.</p></div>
    <a class="btn btn-lg" href="<?= h(url('upcoming-litters')) ?>#waitlist">Join the waitlist</a>
  </div></section>
  <?php
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
    <div><?= icon('video', 'ic ic-lg') ?><strong>Live video call before you pay</strong><a href="<?= h(url('how-it-works')) ?>">How it works</a></div>
    <div><?= icon('lock', 'ic ic-lg') ?><strong><?= h(fill('{deposit}')) ?> deposit, balance at delivery</strong><a href="<?= h(url('deposit-refund-policy')) ?>">Deposit policy</a></div>
    <div><?= icon('truck', 'ic ic-lg') ?><strong>Express 24-hour or Standard 2–5 day delivery</strong><a href="<?= h(url('puppy-delivery')) ?>">Delivery options</a></div>
  </div></section>
  <?php if($sold): ?>
  <section class="sec wrap"><h2>Recently gone to loving homes</h2><div class="pgrid pgrid-sm"><?php foreach($sold as $p) echo puppy_card($p); ?></div></section>
  <?php endif; ?>
  <section class="sec wrap prose-sec"><div class="prose">
    <h2>Buying a beagle puppy from Beagle Brook</h2>
    <p>Every puppy listed here was born and raised in our home in <?= h($CFG['city']) ?>, <?= h($CFG['state_name']) ?>. Before they go home, each one is examined by a licensed veterinarian, given age-appropriate vaccinations and microchipped, and they travel with starter food and full registration papers. Every puppy page shows both parents, so you can see the size, color and temperament your puppy comes from.</p>
    <p>When you find the right puppy, send an inquiry from their page. We’ll call or text you <?= h(fill('{reply_time}')) ?>, answer every question and set up a live video call. A <?= h(fill('{deposit}')) ?> deposit reserves your puppy, and the balance is due at delivery. Payment by <?= h(fill('{payment_methods}')) ?>.</p>
    <p>New to beagles? Read <a href="<?= h(url('guides/beagle-price')) ?>">how much a beagle costs</a>, our <a href="<?= h(url('guides/beagle-dog-breed')) ?>">beagle breed guide</a> and <a href="<?= h(url('guides/how-to-find-a-beagle-breeder')) ?>">how to choose a beagle breeder</a>.</p>
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
      <?php if($yt): ?><div class="video"><iframe src="https://www.youtube-nocookie.com/embed/<?= h($yt) ?>" title="Video of <?= h($p['name']) ?>" loading="lazy" allow="accelerometer; encrypted-media; gyroscope; picture-in-picture" allowfullscreen></iframe></div>
      <?php elseif(($p['video'] ?? '') !== '' && preg_match('#^https://#', $p['video'])): ?><p><a class="btn btn-ghost" href="<?= h($p['video']) ?>" rel="noopener" target="_blank"><?= icon('video') ?>Watch <?= h($p['name']) ?>’s video</a></p><?php endif; ?>
    </div>
    <div class="pinfo">
      <p class="eyebrow"><?= status_badge($p) ?> <?= h(puppy_kind($p)) ?></p>
      <h1><?= h($p['name']) ?> <span>— <?= h(puppy_kind($p)) ?></span></h1>
      <?php if(($p['tagline'] ?? '') !== ''): ?><p class="lede"><?= h($p['tagline']) ?></p><?php endif; ?>
      <div class="pricebox">
        <?php if(($p['price'] ?? '') !== ''): ?><p class="price-lg"><?= h(money($p['price'])) ?></p>
        <p class="muted">Includes vet check, shots, microchip, starter food and full papers. <?= h(fill('{deposit}')) ?> deposit (<?= h(money(deposit_amount($p['price']))) ?>) reserves <?= $p['sex'] === 'female' ? 'her' : 'him' ?>; balance at delivery.</p><?php endif; ?>
        <?php if(delivery_options()): ?><ul class="dlist"><?php foreach(delivery_options() as $m): $fee = delivery_base_fee($m['key']); ?><li><?= icon('truck') ?><span><strong><?= h($m['label']) ?></strong> <?= h($m['days']) ?></span><b><?= $fee !== null ? (count($SITE['delivery']['zones']) > 1 ? 'from ' : '').h(money($fee)) : 'Quoted' ?></b></li><?php endforeach; ?></ul><?php endif; ?>
        <?php if($open): ?><a class="btn btn-lg btn-block" href="#inquire">Send an inquiry for <?= h($p['name']) ?></a>
        <?php elseif($p['status'] === 'reserved'): ?><p class="alert"><strong><?= h($p['name']) ?> is reserved.</strong> You can still send an inquiry to be first in line if the reservation falls through, or <a href="<?= h(url('upcoming-litters')) ?>#waitlist">join the waitlist</a>.</p><a class="btn btn-block btn-ghost" href="#inquire">Inquire as a backup</a>
        <?php else: ?><p class="alert"><strong><?= h($p['name']) ?> has found a loving home.</strong> See <a href="<?= h(url(PUPPIES_PATH)) ?>">available puppies</a> or <a href="<?= h(url('upcoming-litters')) ?>#waitlist">join the waitlist</a>.</p><?php endif; ?>
        <?php if($CFG['phone'] !== ''): ?><p class="pb-contact"><a href="sms:<?= h(tel($CFG['phone'])) ?>"><?= icon('chat') ?>Text us about <?= h($p['name']) ?></a><a href="tel:<?= h(tel($CFG['phone'])) ?>"><?= icon('phone') ?>Call</a></p><?php endif; ?>
      </div>
      <dl class="facts">
        <div><dt>Sex</dt><dd><?= h(sex_label($p['sex'])) ?></dd></div>
        <div><dt>Color</dt><dd><?= h($p['color']) ?></dd></div>
        <?php if(($p['dob'] ?? '') !== ''): ?><div><dt>Born</dt><dd><?= h(nice_date($p['dob'])) ?><?= $age ? ' ('.h($age).' old)' : '' ?></dd></div><?php endif; ?>
        <?php if(($p['ready'] ?? '') !== ''): ?><div><dt>Ready for delivery</dt><dd><?= strtotime($p['ready']) > time() ? h(nice_date($p['ready'])) : 'Now' ?></dd></div><?php endif; ?>
        <?php if(size_text($p['size'] ?? '')): ?><div><dt>Expected size</dt><dd><?= h(size_text($p['size'])) ?></dd></div><?php endif; ?>
        <?php if(($p['weight'] ?? '') !== ''): ?><div><dt>Current weight</dt><dd><?= h($p['weight']) ?></dd></div><?php endif; ?>
        <div><dt>Registration</dt><dd><?= h(trim(($p['registry'] ?? '' ?: $CFG['registry']).' full registration') === 'full registration' ? 'Full registration' : trim(($p['registry'] ?? '' ?: $CFG['registry']).' full registration')) ?><?= ($p['reg_no'] ?? '') !== '' ? ' · '.h($p['reg_no']) : '' ?></dd></div>
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
      <?php if($sire || $dam): ?><h2><?= h($p['name']) ?>’s parents</h2><div class="parent-row"><?= $dam ? parent_mini($dam) : '' ?><?= $sire ? parent_mini($sire) : '' ?></div>
        <p class="muted">Both parents’ registration and health details are on <a href="<?= h(url('our-beagles')) ?>">our beagles</a> page. Ask to see them on your video call.</p><?php endif; ?>
      <h2>What comes with <?= h($p['name']) ?></h2><?= included_block() ?>
      <h2>Our guarantees</h2><?= guarantees_block() ?>
    </div>
  </div>

  <?php if($p['status'] !== 'sold'): ?>
  <section class="sec band" id="inquire"><div class="wrap inq">
    <div class="inq-side">
      <h2><?= $open ? 'Inquire about '.h($p['name']) : 'Inquire as a backup for '.h($p['name']) ?></h2>
      <p>Tell us where you live and a little about your home. It takes two minutes, and there’s <strong>no payment now</strong>.</p>
      <ol class="mini-steps"><li>We reply <?= h(fill('{reply_time}')) ?> and set up a live video call with <?= h($p['name']) ?>.</li><li>A <?= h(fill('{deposit}')) ?> deposit reserves <?= $p['sex'] === 'female' ? 'her' : 'him' ?>, with a written agreement and receipt.</li><li>We deliver to your door; the balance is due at delivery.</li></ol>
      <p class="muted">We accept <?= h(fill('{payment_methods}')) ?>. <a href="<?= h(url('is-beagle-brook-legit')) ?>">How to verify us</a></p>
    </div>
    <div class="inq-form"><?= inquiry_form($p) ?></div>
  </div></section>
  <?php endif; ?>

  <?php $others = array_values(array_filter(puppies_with('available'), fn($x)=>$x['id'] !== $p['id'])); if($others): ?>
  <section class="sec wrap"><div class="sec-h"><h2>More beagle puppies available</h2><a href="<?= h(url(PUPPIES_PATH)) ?>">See all <?= icon('arrow') ?></a></div><?= puppies_block($others, 4) ?></section>
  <?php endif; ?>
  <div class="wrap"><?= guide_links(['beagle-puppy-care', 'beagle-price', 'beagle-training', 'best-dog-food-for-beagles'], 'Getting ready for your puppy') ?></div>
  <?php
}
function youtube_id($u){ return preg_match('#(?:youtube(?:-nocookie)?\.com/(?:watch\?v=|embed/|shorts/)|youtu\.be/)([\w-]{11})#', (string)$u, $m) ? $m[1] : ''; }

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
      <p>We’d love to hear how they’re doing. Email your review and a photo to <a href="mailto:<?= h($CFG['email']) ?>?subject=<?= rawurlencode('My review of '.$CFG['brand']) ?>"><?= h($CFG['email']) ?></a><?= $CFG['google_reviews_url'] !== '' ? ', or <a href="'.h($CFG['google_reviews_url']).'" rel="noopener" target="_blank">review us on Google</a>' : '' ?>.</p>
    </div>
  </section>
  <?php
}

function view_contact(){
  global $CFG;
  page_head($CFG['contact_intro']);
  ?>
  <section class="wrap split" id="message">
    <div><?= contact_form() ?></div>
    <div><h2>Get in touch</h2><?= contact_block() ?>
      <p class="muted">Asking about a specific puppy? Use the inquiry form on <a href="<?= h(url(PUPPIES_PATH)) ?>">their page</a> so we have your delivery details.</p>
      <h2>Before you buy</h2><ul class="linklist"><li><a href="<?= h(url('how-it-works')) ?>">How it works</a></li><li><a href="<?= h(url('health-guarantee')) ?>">Health guarantee</a></li><li><a href="<?= h(url('faq')) ?>">FAQ</a></li><li><a href="<?= h(url('is-beagle-brook-legit')) ?>">How to verify us</a></li></ul></div>
  </section>
  <?php
}

const GUIDE_GROUPS = ['buying'=>'Buying a beagle puppy', 'breed'=>'The beagle breed', 'care'=>'Care, food and training', 'mixes'=>'Beagle mixes', 'culture'=>'Beagles in history and the news'];
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
    <?php if(($art['image'] ?? '') !== ''): ?><div class="art-img"><?= img_tag($art['image'], $art['image_alt'] ?? $art['title'], '(max-width:900px) 100vw, 860px', true) ?></div><?php endif; ?>
    <div class="art-grid">
      <div class="prose"><?= rich($art['body'], page_blocks()) ?></div>
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
  $wide = in_array($info['slug'], ['beagle-puppies-for-sale-ohio'], true);
  page_head('');
  echo '<div class="wrap"><div class="prose'.($wide ? ' prose-wide' : '').'">'.rich($info['body'], page_blocks()).'</div></div>';
  if($info['slug'] === 'faq' || $info['slug'] === 'health-guarantee') echo '<div class="wrap">'.guide_links(['beagle-price', 'how-to-find-a-beagle-breeder', 'beagle-puppy-care'], 'Helpful guides').'</div>';
}

function view_thank_you(){
  global $CFG;
  $ref = (string)($_GET['ref'] ?? '');
  $mine = ($_SESSION['last_inquiry'] ?? '') === $ref;
  page_head('');
  ?>
  <section class="wrap prose">
    <?php if($mine){ $lead = inquiry_load($ref); echo ga_event('generate_lead', array_filter(['lead_type'=>($lead['kind'] ?? '') === 'contact' ? 'contact' : 'puppy_inquiry', 'item_name'=>$lead['puppy_name'] ?? '', 'value'=>($lead['puppy_price'] ?? '') !== '' ? (float)$lead['puppy_price'] : null, 'currency'=>($lead['puppy_price'] ?? '') !== '' ? 'USD' : null])); } ?>
    <div class="alert alert-ok" role="status"><p><strong>Thank you!</strong> <?= $mine ? 'Your reference is <strong>'.h($ref).'</strong>. ' : '' ?>We’ve emailed you a copy and will reply <?= h(fill('{reply_time}')) ?>.</p></div>
    <h2>What happens next</h2>
    <?= steps_block() ?>
    <div class="callout"><p><strong>Staying safe:</strong> we only send payment details from <strong><?= h($CFG['email']) ?></strong>, after we’ve spoken with you. We never ask for gift cards or cryptocurrency. Can’t see our email? Check your spam folder and add <?= h($CFG['email']) ?> to your contacts.</p></div>
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
    <p class="muted">Full health guarantee: <?= h(site_link('health-guarantee')) ?> · Deposit and refund policy: <?= h(site_link('deposit-refund-policy')) ?></p>
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
          <label class="check"><input type="checkbox" name="agree" value="1" required><span>I have read this agreement, including the health guarantee and the deposit and refund terms, and I accept it.</span></label>
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
