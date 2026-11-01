<?php
/* Turns a product row (from a catalog CSV or the admin) into a tidy product:
   metal, type, weight, fine metal content, brand, year, grading — and files it
   into the right categories. Used by the CSV import and by the bundled catalog. */
if(!defined('GB_ROOT')){ http_response_code(404); exit; }

const BRANDS = ['PAMP Suisse'=>'pamp', 'Valcambi'=>'valcambi', 'Argor-Heraeus'=>'argor[ -]?heraeus', 'Heraeus'=>'heraeus', 'Perth Mint'=>'perth mint',
  'Royal Canadian Mint'=>'royal canadian mint|\brcm\b', 'The Royal Mint'=>'royal mint', 'US Mint'=>'us mint|united states mint', 'Credit Suisse'=>'credit suisse',
  'Engelhard'=>'engelhard', 'Johnson Matthey'=>'johnson matthey', 'Sunshine Minting'=>'sunshine', 'SilverTowne'=>'silvertowne', 'Geiger'=>'geiger',
  'Asahi'=>'asahi', 'Scottsdale Mint'=>'scottsdale', 'Golden State Mint'=>'golden state mint', 'Monarch'=>'monarch', 'Mother Lode Mint'=>'mother lode',
  'Elemetal'=>'elemetal', 'Germania Mint'=>'germania', 'Highland Mint'=>'highland mint', 'Istanbul Gold Refinery'=>'istanbul gold', 'Metalor'=>'metalor',
  'Royal Australian Mint'=>'royal australian mint', 'Austrian Mint'=>'austrian mint', 'South African Mint'=>'south african mint', 'Mexican Mint'=>'mexican mint',
  'China Mint'=>'china mint', 'Niue (New Zealand Mint)'=>'\bniue\b', 'Tuvalu (Perth Mint)'=>'\btuvalu\b', 'Nebü'=>'neb[uü]', 'Goldback'=>'goldback',
  'Air-Tite'=>'air-?tite', 'MK BarZ'=>'mk barz', 'Silver Shield'=>'silver shield', 'Lady Fortuna'=>'fortuna', 'JBR'=>'\bjbr\b', 'Kitco'=>'kitco'];

/* fine gold in pre-1933 US gold coins, by face value */
const PRE33_GOLD_OZ = ['1'=>0.04837, '2.50'=>0.12094, '2.5'=>0.12094, '3'=>0.14512, '5'=>0.24187, '10'=>0.48375, '20'=>0.9675];

function cat_text($s){ return mb_strtolower(trim((string)$s)); }

/* "1/10 oz" → [0.1, 'oz'], "1 Troy Ounce" → [1, 'oz'], "5 Gram" → [5, 'g'], "1 Kilo" → [1, 'kg'], "12.4g" → [12.4, 'g'] */
function weight_parse($s){
  $s = strtolower(str_replace(['½','¼','¾'], ['1/2','1/4','3/4'], (string)$s));
  if(!preg_match('#(\d*\.?\d+(?:\s*/\s*\d+)?)\s*-?\s*(troy\s*ounces?|troy\s*oz|ozt|ounces?|oz|kilograms?|kilos?|kg|grams?|gm|g)(?![a-z])#', $s, $m)) return null;
  $n = $m[1];
  if(strpos($n, '/') !== false){ [$a, $b] = array_map('floatval', explode('/', $n)); $n = $b ? $a / $b : 0; } else $n = (float)$n;
  $u = $m[2];
  $unit = $u[0] === 'k' ? 'kg' : ($u[0] === 'g' ? 'g' : 'oz');
  return $n > 0 ? [round($n, 6), $unit] : null;
}

/* "Tube (20 Coins" / "Monster Box (500 Coins" / "Set of 5 x 1/10 oz" / "Roll of 20" → 20 */
function piece_count($name){
  if(preg_match('/\((?:[^)]*?\b)?(\d+)\s*(?:coins|bars|rounds|pcs|pieces)\b/i', $name, $m)) return max(1, (int)$m[1]);
  if(preg_match('/\b(?:set|roll|tube|lot) of (\d+)/i', $name, $m)) return max(1, (int)$m[1]);
  if(preg_match('/\((\d+)\s*x\s*\d/i', $name, $m)) return max(1, (int)$m[1]);
  return 1;
}

function catalog_metal($r, $name){
  $m = cat_text($r['metal'] ?? '');
  foreach(['gold', 'silver', 'platinum', 'palladium', 'copper'] as $k) if(strpos($m, $k) !== false) return $k;
  $n = cat_text($name);
  if(preg_match('/neb[uü]|goldback|goldnote|\b(10|14|18|22|24)k\b/u', $n)) return 'gold';
  foreach(['platinum', 'palladium', 'silver', 'gold', 'copper'] as $k) if(preg_match('/\b'.$k.'\b/', $n)) return $k;
  return '';
}

function catalog_type($r, $name){
  $t = cat_text($r['product_type'] ?? $r['type'] ?? '');
  $n = cat_text($name);
  if((preg_match('/goldback|goldnote|\bnote\b|banknote|aurum|certificate note|currency/', $n) || $t === 'note') && !preg_match('/\bbar\b|\bround\b/', $n)) return 'note';
  if($t === 'jewelry' || preg_match('/neb[uü]|pendant|bezel|necklace|earring|bracelet|\bring\b/', $n)) return 'jewelry';
  /* capsules, holders, boxes, cases… — but not coins sold in a tube, box or capsule */
  $is_acc = preg_match('/capsule|holder|air-?tite|display|easel|album|folder|\bcase\b|gloves|magnif|\bscale\b|velvet|\bbag\b|\bflip|slab|\bstand\b|presentation box|storage|\bchest\b|tube(?!\s*\()|monster box(?!\s*\()/', $n);
  $has_metal = preg_match('/\b(gold|silver|platinum|palladium|copper)\b/', $n) && (weight_parse($n) || preg_match('/\$\d|coin|dollar|half|quarter|dime|medal/', $n));
  if($is_acc && !$has_metal) return 'accessory';
  if(preg_match('/\bround\b|medallion|\bmedal\b/', $n)) return 'round';
  if(preg_match('/\bbar\b|\bbars\b|combibar|ingot|bullet|\bshot\b|\bwafer\b|\bbiscuit\b/', $n)) return 'bar';
  if(preg_match('/\bset\b|collection/', $n) && preg_match('/\d-coin|set of|\bset\b/', $n)) return 'set';
  if(preg_match('/coin|eagle|buffalo|maple|krugerrand|britannia|sovereign|philharmonic|kangaroo|nugget|panda|lunar|libertad|pesos|dollar|\bcent\b|dime|quarter|half|denarius|drachm|stater|reales|franc|ducat|corona|vreneli|tube|monster box/', $n)) return 'coin';
  if($t === 'accessory') return 'accessory';
  return in_array($t, ['coin', 'bar', 'round', 'set'], true) ? $t : ($t === 'bullion bundle' ? 'coin' : 'collectible');
}

/* builds the product record from a catalog row */
function catalog_product($r){
  $name = trim(preg_replace('/\s+/', ' ', (string)($r['name'] ?? '')));
  $specs = json_decode((string)($r['catalog_specifications_json'] ?? ''), true) ?: [];
  $metal = catalog_metal($r, $name);
  $type = catalog_type($r, $name);
  $w = weight_parse($r['listed_weight'] ?? '') ?: weight_parse($specs['Metal Weight'] ?? '') ?: weight_parse($name);
  $n = cat_text($name);
  $graded = (bool)preg_match('/\b(pcgs|ngc|anacs|icg|cac)\b|\b(ms|pf|pr|sp)\s?-?\d{2}\b/', $n) || in_array(strtoupper(trim($r['listed_grading_service'] ?? '')), ['PCGS', 'NGC', 'ANACS', 'ICG'], true);
  $proof = (bool)preg_match('/\bproof\b|\bpf\s?\d|\bpr\s?\d|reverse proof/', $n);
  $year = '';
  if(preg_match('/\b(1[5-9]\d\d|20[0-4]\d)(?:-[a-z]{1,2})?\b/', $name, $ym)) $year = $ym[1];
  elseif(preg_match('/^\d{4}$/', trim($r['listed_year'] ?? ''))) $year = trim($r['listed_year']);
  $brand = trim((string)($r['manufacturer'] ?? '')) ?: trim((string)($specs['Manufacturer'] ?? ''));
  if($brand === '' || preg_match('/^(varied|any mint|various)$/i', $brand)){
    $brand = '';
    foreach(BRANDS as $label=>$re) if(preg_match('/'.$re.'/iu', $name)){ $brand = $label; break; }
    if($brand === '' && preg_match('/american (gold|silver|platinum|palladium)|\bus mint\b|pre-?33|morgan|peace silver dollar/i', $name)) $brand = 'US Mint';
    if($brand === '' && preg_match('/canadian|maple leaf/i', $name)) $brand = 'Royal Canadian Mint';
    if($brand === '' && preg_match('/britannia|british|sovereign|queen\'?s beast|tudor beast/i', $name)) $brand = 'The Royal Mint';
    if($brand === '' && preg_match('/australian|kangaroo|kookaburra|lunar|koala/i', $name)) $brand = 'Perth Mint';
    if($brand === '' && preg_match('/krugerrand/i', $name)) $brand = 'South African Mint';
    if($brand === '' && preg_match('/philharmonic/i', $name)) $brand = 'Austrian Mint';
    if($brand === '' && preg_match('/libertad|mexican/i', $name)) $brand = 'Mexican Mint';
    if($brand === '' && preg_match('/panda/i', $name)) $brand = 'China Mint';
  }
  $qty = piece_count($name);
  /* fine metal per item, in troy ounces, where it can be worked out */
  $fine = 0;
  if($w && $metal !== '' && in_array($type, ['bar', 'coin', 'round', 'set', 'note', 'collectible'], true)) $fine = oz_from($w[0], $w[1]) * $qty;
  if(!$fine && $metal === 'gold' && preg_match('/pre-?33 \$(\d+(?:\.\d+)?)/i', $name, $fm) && isset(PRE33_GOLD_OZ[$fm[1]])) $fine = PRE33_GOLD_OZ[$fm[1]];
  if(!$fine && $metal === 'silver' && preg_match('/90% silver.*\$(\d+)\s*(?:fv|tube|face)/i', $name, $fm)) $fine = 0.715 * (float)$fm[1];
  if(!$fine && preg_match('#^(\d+(?:/\d+)?)\s+[a-z ]+goldback#i', $name, $fm)){ $q = explode('/', $fm[1]); $fine = (count($q) === 2 ? $q[0] / $q[1] : (float)$q[0]) / 1000; }
  if(!$fine && $metal === 'silver' && preg_match('/\b(morgan|peace) silver dollar/i', $name)) $fine = 0.7734;
  if(!$fine && $metal === 'gold'){   /* classic gold coins sold without a stated weight */
    foreach(['/100 corona/i'=>0.9802, '/20 corona/i'=>0.1960, '/10 corona/i'=>0.0980, '/4 ducat/i'=>0.4430, '/\b1 ducat|\bducat/i'=>0.1107, '/50 pesos/i'=>1.2057,
             '/20 pesos/i'=>0.4823, '/10 pesos/i'=>0.2411, '/20 francs|8 florin/i'=>0.1867, '/10 francs|4 florin/i'=>0.0933, '/half sovereign/i'=>0.1177, '/sovereign/i'=>0.2354,
             '/20 lire/i'=>0.1867, '/20 mark/i'=>0.2305, '/10 guilder/i'=>0.1947] as $re=>$oz) if(preg_match($re, $name)){ $fine = $oz; break; }
  }
  if(!$fine && $type === 'jewelry' && preg_match('/(\d+(?:\.\d+)?)\s*g\b.*?\b(24|22|18|14|10)k\b/i', $name, $jm)) $fine = (float)$jm[1] / G_PER_OZT * (int)$jm[2] / 24;
  $p = [
    'id'=>preg_replace('/[^A-Za-z0-9_-]/', '', (string)($r['sku'] ?? '')) ?: bin2hex(random_bytes(5)),
    'sku'=>trim((string)($r['sku'] ?? '')), 'name'=>$name,
    'metal'=>$metal, 'type'=>$type,
    'weight'=>$w ? $w[0] : 0, 'unit'=>$w ? $w[1] : 'oz', 'qty'=>$qty, 'fine_oz'=>round($fine, 6),
    'purity'=>trim((string)($specs['Purity'] ?? '')), 'brand'=>$brand, 'year'=>$year,
    'grade'=>preg_match('/^(none)?$/i', trim($r['listed_grade'] ?? '')) ? '' : trim($r['listed_grade']),
    'grader'=>preg_match('/^(none)?$/i', trim($r['listed_grading_service'] ?? '')) ? (preg_match('/\b(PCGS|NGC|ANACS)\b/', $name, $gm) ? $gm[1] : '') : strtoupper(trim($r['listed_grading_service'])),
    'graded'=>$graded, 'proof'=>$proof,
    'packaging'=>trim((string)($specs['Packaging Type'] ?? '')),
    'specs'=>array_diff_key($specs, array_flip(['Product ID', 'Product Title', 'Shipping'])),
    'short'=>'', 'description'=>trim((string)($r['description'] ?? '')),
    'pricing'=>'fixed', 'price'=>(float)preg_replace('/[^\d.]/', '', (string)($r['price'] ?? '')), 'premium_pct'=>0, 'premium_usd'=>0, 'compare_at'=>0,
    'stock'=>preg_match('/out|sold/i', $r['availability'] ?? '') ? 'out' : (preg_match('/pre/i', $r['availability'] ?? '') ? 'preorder' : 'in'),
    'hidden'=>!in_array(strtolower(trim((string)($r['published'] ?? 'true'))), ['true', '1', 'yes'], true),
    'featured'=>false, 'images'=>array_values(array_filter(preg_split('/\s*[|\n]\s*/', (string)($r['image_urls'] ?? '')), fn($u)=>preg_match('#^https?://#', $u))),
    'seo_title'=>'', 'seo_desc'=>'', 'sort'=>0,
  ];
  if($p['purity'] === '' && $metal === 'gold' && $type === 'bar') $p['purity'] = '.9999';
  if($p['description'] === '' || stripos($p['description'], 'product collection') !== false) $p['description'] = product_description_auto($p);
  $p['categories'] = categorize($p);
  return $p;
}

/* a factual description built from the product's own details */
function product_description_auto($p){
  $metal = $p['metal'] ?? ''; $t = $p['type'] ?? ''; $s = [];
  $oz = (float)($p['fine_oz'] ?? 0);
  if($oz > 0 && $metal !== ''){
    $g = rtrim(rtrim(number_format($oz * G_PER_OZT, 3), '0'), '.');
    $amt = $oz >= 0.999 ? rtrim(rtrim(number_format($oz, 3), '0'), '.').' troy oz' : $g.($g === '1' ? ' gram' : ' grams');
    $s[] = ($p['qty'] ?? 1) > 1 ? "Together these {$p['qty']} pieces contain $amt of ".(($p['purity'] ?? '') !== '' ? $p['purity'].' fine ' : '').$metal.'.'
                               : "The {$p['name']} contains $amt of ".(($p['purity'] ?? '') !== '' ? $p['purity'].' fine ' : '').$metal.'.';
  } else $s[] = "The {$p['name']}.";
  if(($p['brand'] ?? '') !== '' && !preg_match('/^(varied|any mint)/i', $p['brand'])) $s[] = 'Made by '.$p['brand'].'.';
  if(!empty($p['graded'])) $s[] = 'Graded'.(($p['grade'] ?? '') !== '' ? ' '.$p['grade'] : '').(($p['grader'] ?? '') !== '' ? ' by '.$p['grader'] : '').' and sealed in a tamper-evident holder that guarantees its authenticity and grade.';
  elseif(!empty($p['proof'])) $s[] = 'Struck as a proof, with mirror-like fields and frosted design details.';
  $s[] = ['bar'=>'Bars offer one of the lowest premiums per ounce of any form of bullion.',
          'coin'=>!empty($p['graded']) || !empty($p['proof']) ? 'A collectible coin valued for its finish and condition as well as its metal.' : 'Government and sovereign bullion coins are recognised by dealers worldwide, which makes them easy to buy and sell.',
          'round'=>'As a privately minted round it has no face value and is valued on its metal content and design.',
          'note'=>stripos($p['name'], 'gold') !== false || stripos($p['name'], 'goldback') !== false ? 'Each note contains a thin layer of 24K gold sealed in protective polymer.' : 'A collectible note valued for its history and condition.',
          'jewelry'=>'Bullion jewelry is valued largely on its precious-metal weight.',
          'set'=>'A collector set sold together.', 'accessory'=>'Designed to protect, store or display your coins and bars.', 'collectible'=>'A collectible valued for its history, design and condition.'][$t] ?? '';
  if(($p['packaging'] ?? '') !== '' && !preg_match('/^(none|varied)$/i', $p['packaging'])) $s[] = 'Packaging: '.strtolower($p['packaging']).'.';
  return trim(implode(' ', array_filter($s)));
}

/* the categories a product belongs to (most specific first) */
function categorize($p){
  $n = cat_text($p['name']); $m = $p['metal'] ?? ''; $t = $p['type'] ?? '';
  $oz = ($p['weight'] ?? 0) > 0 ? oz_from($p['weight'], $p['unit'] ?? 'oz') : 0;
  $is = fn($re)=>(bool)preg_match($re, $n);
  $near = fn($target)=>$oz > 0 && abs($oz - $target) / $target < 0.002;
  $c = [];
  if($t === 'accessory') return ['accessories'];
  if($t === 'jewelry') return ['gold-jewelry'];
  if($t === 'note') return [$is('/goldback|gold|aurum/') ? 'goldbacks' : 'rare-coins'];
  if($is('/ancient|denarius|drachm|stater|tetradrachm|byzantine|roman empire|greek/')) return ['rare-coins'];
  if($m === 'gold'){
    if($t === 'bar'){
      $map = [[1 / G_PER_OZT, '1-gram-gold-bars'], [10 / G_PER_OZT, '10-gram-gold-bars'], [1, '1-oz-gold-bars'], [100 / G_PER_OZT, '100-gram-gold-bars'],
              [5, '5-oz-gold-bars'], [10, '10-oz-gold-bars'], [1000 / G_PER_OZT, '1-kilo-gold-bars'], [100, '100-oz-gold-bars']];
      foreach($map as [$target, $slug]) if(($p['qty'] ?? 1) == 1 && $near($target)){ $c[] = $slug; break; }
      if(!$c && $oz > 0 && $oz < 1) $c[] = 'fractional-gold-bars';
      $c[] = 'gold-bars';
      return $c;
    }
    if($t === 'round') return ['gold-rounds'];
    if($t === 'collectible' && !$is('/coin|eagle|pesos|sovereign|ducat|franc/')) return ['gold-coins'];
    if($is('/pre-?33|pre-?1933|\$\d+(\.\d+)? (liberty|indian|saint)|double eagle|half eagle|quarter eagle|three dollar/') && !$is('/american gold eagle/')) $c[] = 'pre-1933-gold-coins';
    elseif($is('/american gold eagle|gold eagle coin|\bgold eagles?\b/') && !$is('/samoa|liberty gold eagle/')) $c[] = 'american-gold-eagles';
    elseif($is('/buffalo/')) $c[] = 'american-gold-buffalos';
    elseif($is('/maple/')) $c[] = 'canadian-gold-maple-leafs';
    elseif($is('/krugerrand/')) $c[] = 'gold-krugerrands';
    elseif($is('/britannia|sovereign|british|queen.?s beast|tudor|royal mint|\buk\b/')) $c[] = 'british-gold-coins';
    elseif($is('/philharmonic|austrian|corona|ducat/')) $c[] = 'austrian-gold-coins';
    elseif($is('/australian|kangaroo|nugget|lunar|perth|kookaburra|koala/')) $c[] = 'australian-gold-coins';
    elseif($is('/panda|chinese/')) $c[] = 'chinese-gold-pandas';
    else $c[] = 'world-gold-coins';
    if(!empty($p['graded'])) $c[] = 'certified-gold-coins';
    if(!empty($p['proof']) && empty($p['graded'])) $c[] = 'proof-gold-coins';
    if($oz > 0 && $oz < 0.99 && ($p['qty'] ?? 1) == 1) $c[] = 'fractional-gold-coins';
    $c[] = 'gold-coins';
    return array_values(array_unique($c));
  }
  if($m === 'silver'){
    if($is('/90%|40%|35%|junk|\bfv\b|face value|war nickel/')) return ['junk-silver', 'silver-coins', 'silver-bullion'];
    if($t === 'bar'){
      $map = [[1, '1-oz-silver-bars'], [10, '10-oz-silver-bars'], [100, '100-oz-silver-bars'], [1000 / G_PER_OZT, '1-kilo-silver-bars']];
      foreach($map as [$target, $slug]) if(($p['qty'] ?? 1) == 1 && $near($target)){ $c[] = $slug; break; }
      return array_merge($c, ['silver-bars', 'silver-bullion']);
    }
    if($t === 'round') return ['silver-rounds', 'silver-bullion'];
    if($is('/american silver eagle|silver eagle coin|\bsilver eagles?\b/') && !$is('/samoa|wedge|australian/')) $c[] = 'american-silver-eagles';
    elseif($is('/maple/')) $c[] = 'canadian-silver-maple-leafs';
    elseif($is('/morgan|peace dollar|peace silver|silver dollar|trade dollar|seated liberty|barber|walking liberty|franklin|kennedy|mercury|roosevelt|washington/')) $c[] = 'us-silver-coins';
    elseif($is('/britannia|british|queen.?s beast|tudor|royal mint/')) $c[] = 'british-silver-coins';
    elseif($is('/australian|kangaroo|kookaburra|koala|lunar|perth|wedge.?tail/')) $c[] = 'australian-silver-coins';
    else $c[] = 'world-silver-coins';
    if(!empty($p['graded'])) $c[] = 'certified-silver-coins';
    return array_values(array_unique(array_merge($c, ['silver-coins', 'silver-bullion'])));
  }
  if($m === 'platinum') return ['platinum'];
  if($m === 'palladium') return ['palladium'];
  if($m === 'copper') return ['copper'];
  if($t === 'set' || $t === 'collectible' || $t === 'coin') return ['rare-coins'];
  return ['accessories'];
}
