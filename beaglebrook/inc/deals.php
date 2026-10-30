<?php
/* Sales agreements, payment receipts and the reply emails sent from an inquiry.
   Shared by admin.php (sending) and index.php (the buyer's agreement and receipt pages). */
if(!defined('BB_ROOT')){ http_response_code(404); exit; }

function inquiry_load($ref){ return is_string($ref) && preg_match('/^BB-\d{2}-[A-F0-9]{6}$/', $ref) ? data_read('inquiries/'.$ref) : null; }
function inquiry_save($q){ return data_write('inquiries/'.$q['ref'], $q); }

/* absolute link to a page of the public site, with or without clean addresses */
function site_link($path, $query=[]){
  $c = $GLOBALS['SITE']['settings'];
  $base = rtrim($c['domain'], '/').'/';
  if(!empty($c['pretty_urls'])) return $base.$path.($query ? '?'.http_build_query($query) : '');
  return $base.'index.php?'.http_build_query(['path'=>$path] + $query);
}
/* the buyer's own agreement and receipt pages: the key in the link is what lets them open it */
function agreement_link($ref){ return site_link('agreement', ['ref'=>$ref, 'k'=>sign('agreement|'.$ref)]); }
function receipt_link($ref){ return site_link('receipt', ['ref'=>$ref, 'k'=>sign('receipt|'.$ref)]); }

/* The deal as it stands, or as it was when the agreement was sent: a sent agreement is frozen
   in the inquiry, so editing the puppy afterwards never changes what the buyer read. */
function deal_of($q){
  if(!empty($q['deal'])) return $q['deal'];
  $S = $GLOBALS['SITE']; $c = $S['settings'];
  $p = ($q['puppy_id'] ?? '') !== '' ? find_by($S['puppies'], 'id', $q['puppy_id']) : null;
  $par = function($id) use($S){
    $x = $id ? find_by($S['parents'], 'id', $id) : null;
    return $x ? trim($x['name'].(($x['registered_name'] ?? '') !== '' ? ' ('.$x['registered_name'].')' : '').(($x['reg_no'] ?? '') !== '' ? ', '.trim(($x['registry'] ?? '').' '.$x['reg_no']) : '')) : '';
  };
  $price = (float)($p['price'] ?? $q['puppy_price'] ?? 0);
  return [
    'date'=>date('Y-m-d'),
    'puppy'=>$p['name'] ?? $q['puppy_name'] ?? '', 'sex'=>($p['sex'] ?? '') === 'female' ? 'Female' : (($p['sex'] ?? '') === 'male' ? 'Male' : ''),
    'color'=>$p['color'] ?? '', 'dob'=>$p['dob'] ?? '', 'registry'=>($p['registry'] ?? '') !== '' ? $p['registry'] : $c['registry'],
    'reg_no'=>$p['reg_no'] ?? '', 'chip_no'=>$p['chip_no'] ?? '', 'dam'=>$par($p['dam'] ?? ''), 'sire'=>$par($p['sire'] ?? ''),
    'price'=>$price, 'deposit_pct'=>(int)$c['deposit_pct'], 'deposit'=>round($price * (int)$c['deposit_pct'] / 100, 2),
    'delivery'=>$q['delivery_name'] ?? '', 'delivery_label'=>$q['delivery_label'] ?? preg_replace('/\s*\(.*\)$/', '', $q['delivery_name'] ?? ''),
    'delivery_days'=>$q['delivery_days'] ?? (preg_match('/\((.*)\)$/', $q['delivery_name'] ?? '', $m) ? $m[1] : ''), 'delivery_fee'=>($q['delivery_fee'] ?? '') !== '' ? (float)$q['delivery_fee'] : null,
    'payment'=>$q['payment_name'] ?? '',
    'vet_hours'=>(int)$c['vet_check_hours'], 'genetic_years'=>(int)$c['genetic_years'], 'go_home_weeks'=>(int)$c['go_home_weeks'],
    'guarantees'=>array_map(fn($g)=>[fill($g['title']), fill($g['text'])], $S['guarantees']),
    'seller'=>$c['brand'], 'seller_name'=>$c['payee_name'], 'seller_place'=>$c['city'].', '.$c['state_name'], 'seller_email'=>$c['email'], 'seller_phone'=>$c['phone'],
    'licenses'=>array_values(array_filter(array_map('trim', explode("\n", (string)$c['license_text'])))),
  ];
}

/* money owed and paid; the deposit is part of the price, delivery is paid with the balance */
function deal_totals($q){
  $d = deal_of($q);
  $paid = round(array_sum(array_map(fn($x)=>(float)$x['amount'], $q['payments'] ?? [])), 2);
  $total = round($d['price'] + (float)($d['delivery_fee'] ?? 0), 2);
  return ['total'=>$total, 'paid'=>$paid, 'due'=>max(0, round($total - $paid, 2)), 'deposit_left'=>max(0, round($d['deposit'] - $paid, 2)),
          'at_delivery'=>round($total - $d['deposit'], 2)];
}

/* The sales agreement as headed sections, so the same words appear on the web page, in print and by email. */
function agreement_sections($q){
  $d = deal_of($q); $t = deal_totals($q);
  $fee = $d['delivery_fee'] !== null ? money($d['delivery_fee']) : 'to be confirmed in writing';
  $years = $d['genetic_years'] === 1 ? '1 year' : $d['genetic_years'].' years';
  $s = [];
  $s[] = ['The parties', [
    'Seller: '.$d['seller'].($d['seller_name'] !== '' && $d['seller_name'] !== $d['seller'] ? ' ('.$d['seller_name'].')' : '').', '.$d['seller_place'].' · '.$d['seller_email'].($d['seller_phone'] !== '' ? ' · '.$d['seller_phone'] : ''),
    'Buyer: '.$q['name'].', '.trim($q['address1'].($q['address2'] !== '' ? ', '.$q['address2'] : '').', '.$q['city'].', '.$q['state'].' '.$q['zip']).' · '.$q['email'].' · '.$q['phone'],
    'Reference: '.$q['ref'].' · Date: '.nice_date($d['date']),
  ], []];
  $s[] = ['The puppy', [], array_values(array_filter([
    'Name: '.$d['puppy'], 'Breed: purebred beagle', $d['sex'] !== '' ? 'Sex: '.$d['sex'] : '', $d['color'] !== '' ? 'Color: '.$d['color'] : '',
    $d['dob'] !== '' ? 'Date of birth: '.nice_date($d['dob']) : '',
    'Registration: '.trim(($d['registry'] !== '' ? $d['registry'].' ' : '').'full registration'.($d['reg_no'] !== '' ? ', '.$d['reg_no'] : '')),
    'Microchip: '.($d['chip_no'] !== '' ? $d['chip_no'] : 'number given at delivery'),
    $d['dam'] !== '' ? 'Dam (mother): '.$d['dam'] : '', $d['sire'] !== '' ? 'Sire (father): '.$d['sire'] : '',
  ]))];
  $s[] = ['Price and payment', [
    'The puppy’s price includes a veterinarian health check, age-appropriate vaccinations, a microchip, starter food and full registration papers.',
  ], array_values(array_filter([
    'Puppy price: '.money($d['price']),
    $d['delivery'] !== '' ? 'Delivery: '.$d['delivery'].', '.$fee : '',
    'Total: '.money($t['total']).($d['delivery_fee'] === null && $d['delivery'] !== '' ? ' plus delivery' : ''),
    'Deposit to reserve the puppy ('.$d['deposit_pct'].'% of the price): '.money($d['deposit']),
    'Due at delivery, before the puppy is handed over: '.money($t['at_delivery']).' (the rest of the price plus delivery)',
    $d['payment'] !== '' ? 'Payment method: '.$d['payment'] : '',
  ])), ];
  $s[] = ['Deposit and refunds', [
    'The deposit is part of the puppy’s price. Once it is received, the seller reserves the puppy for the buyer and sends a written receipt for every payment.',
    'The deposit and anything else paid are refunded in full, within 7 days, if: the seller cancels the sale; the puppy is found unwell by the seller’s veterinarian before delivery and the buyer does not want a replacement; or the seller cannot deliver the puppy healthy within two weeks of the agreed date.',
    'If the buyer changes their mind, the deposit is not refunded, but it can be transferred once to another puppy from a current or upcoming litter within 12 months.',
  ], []];
  $s[] = ['Delivery', [
    'The puppy is delivered to the buyer’s address above, no earlier than '.$d['go_home_weeks'].' weeks old, after an examination by the seller’s veterinarian. Puppies traveling to another state travel with a veterinary health certificate. Delivery times count from when the puppy leaves the seller.',
    'If it is not safe for the puppy to travel on the agreed day, because of illness, weather or anything else, the delivery is rescheduled at no extra cost.',
  ], []];
  $s[] = ['Health guarantee', [
    'The buyer agrees to have the puppy examined by a licensed veterinarian within '.$d['vet_hours'].' hours of delivery. If that veterinarian finds a serious illness that was present at delivery and makes the puppy unfit to keep, the buyer may choose a replacement puppy of equal value or a full refund of the price once the puppy is returned.',
    'For '.$years.' from delivery, the puppy is guaranteed against hereditary and congenital conditions that are life-threatening or seriously affect quality of life, diagnosed by a licensed veterinarian. The remedy is a replacement puppy of equal value; the buyer does not have to return their dog.',
    'Common, treatable conditions of young puppies (such as intestinal parasites, mild kennel cough or minor skin problems), and illness or injury caused after delivery, are not covered. The buyer agrees to keep up vaccinations, feed a complete diet and tell the seller within 7 days of any diagnosis, with the veterinarian’s records. The full health guarantee on the seller’s website is part of this agreement.',
  ], array_map(fn($g)=>$g[0].': '.$g[1], $d['guarantees'])];
  $s[] = ['Registration papers and take-back', [
    'Full registration papers are transferred to the buyer once the price has been paid in full.',
    'If the buyer can ever no longer keep the dog, at any age, they agree to contact the seller first. The seller will take the dog back or help rehome it.',
  ], []];
  if($d['licenses']) $s[] = ['Seller’s licenses and registrations', [], $d['licenses']];
  $s[] = ['Agreement', [
    'By accepting this agreement online (typing their full name) or by signing it, the buyer and seller agree to these terms. The seller accepts them by sending this agreement.',
  ], []];
  return $s;
}

function agreement_text($q){
  $o = 'SALES AGREEMENT — '.strtoupper(deal_of($q)['puppy'])."\n";
  foreach(agreement_sections($q) as [$h, $paras, $items]){
    $o .= "\n".strtoupper($h)."\n";
    foreach($paras as $p) $o .= $p."\n";
    foreach($items as $i) $o .= '- '.$i."\n";
  }
  return $o;
}

/* ---------------- reply emails ---------------- */
/* everything an email template can say about one inquiry */
function template_vars($q, $extra=[]){
  $S = $GLOBALS['SITE']; $c = $S['settings'];
  $d = deal_of($q); $t = deal_totals($q);
  $pay = find_by($S['payments'], 'key', $q['payment'] ?? '') ?? find_by($S['payments'], 'name', $q['payment_name'] ?? '');
  $pays = $q['payments'] ?? []; $last = $pays ? end($pays) : null;
  $p = ($q['puppy_id'] ?? '') !== '' ? find_by($S['puppies'], 'id', $q['puppy_id']) : null;
  return [
    '{first_name}'=>strtok(trim($q['name']), ' ') ?: $q['name'], '{name}'=>$q['name'], '{ref}'=>$q['ref'],
    '{puppy}'=>$d['puppy'] ?: 'your puppy', '{puppy_link}'=>$p ? site_link('beagle-puppies-for-sale/'.$p['slug']) : site_link('beagle-puppies-for-sale'),
    '{price}'=>money($d['price']), '{deposit_amount}'=>money($d['deposit']), '{total}'=>money($t['total']),
    '{delivery_option}'=>($d['delivery_label'] ?? '') !== '' ? $d['delivery_label'] : 'delivery', '{delivery_time}'=>$d['delivery_days'] ?? '', '{delivery_fee}'=>$d['delivery_fee'] !== null ? money($d['delivery_fee']) : 'to be confirmed',
    '{due_at_delivery}'=>money($t['at_delivery']), '{paid}'=>money($t['paid']), '{balance_due}'=>money($t['due']),
    '{last_payment}'=>$last ? money($last['amount']).' by '.$last['method'] : 'your deposit',
    '{payment_method}'=>$d['payment'] !== '' ? $d['payment'] : 'your chosen method',
    '{payment_details}'=>trim((string)($pay['details'] ?? '')),
    '{agreement_link}'=>agreement_link($q['ref']), '{receipt_link}'=>receipt_link($q['ref']),
    '{delivery_date}'=>$extra['date'] ?? '', '{note}'=>$extra['note'] ?? '',
    '{phone_or_email}'=>$c['phone'] !== '' ? $c['phone'] : $c['email'],
    '{care_guide_link}'=>site_link('guides/beagle-puppy-care'), '{waitlist_link}'=>site_link('upcoming-litters'),
  ];
}
/* a template with this inquiry's details filled in; blank lines left by an empty {note} are tidied away */
function template_render($text, $q, $extra=[]){
  $out = fill(strtr((string)$text, template_vars($q, $extra)));
  return trim(preg_replace("/\n{3,}/", "\n\n", str_replace("\r", '', $out)));
}
function email_footer(){
  $c = $GLOBALS['SITE']['settings'];
  return "\n\n— ".$c['brand']."\n".$c['city'].', '.$c['state'].' · '.$c['email'].($c['phone'] !== '' ? ' · '.$c['phone'] : '')."\n".$c['domain'];
}
/* sends an email to the inquiry's buyer and records it in the inquiry's history */
function email_buyer(&$q, $key, $subject, $body){
  $ok = site_mail($q['email'], $subject, $body.email_footer(), $GLOBALS['SITE']['settings']['email']);
  $q['emails'][] = ['time'=>date('c'), 'key'=>$key, 'subject'=>$subject, 'ok'=>$ok];
  return $ok;
}
