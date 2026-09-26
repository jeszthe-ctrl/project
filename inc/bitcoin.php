<?php
/* Bitcoin payments, shared by index.php and admin.php.

   The customer pays the order total straight to your wallet address from their order page.
   The BTC price comes from public price feeds (mempool.space, Coinbase, Kraken) and payments
   are found with the public Esplora API (mempool.space, blockstream.info as a fallback), so
   nothing needs installing, no account or API key, and your wallet's keys never touch the site.
   The server only ever reads public blockchain data for your address. */
if(!defined('FK_ROOT')){ http_response_code(404); exit; }

function btc_apis(){ return ($e = getenv('FK_BTC_API')) ? [rtrim($e, '/')] : ['https://mempool.space/api', 'https://blockstream.info/api']; }
function btc_tx_url($txid){ return 'https://mempool.space/tx/'.rawurlencode($txid); }
function btc_addr_url($a){ return 'https://mempool.space/address/'.rawurlencode($a); }

/* ---------------- settings ---------------- */
function btc_settings(){
  $s = $GLOBALS['STORE']['settings'];
  return ['address'=>btc_norm(trim((string)($s['btc_address'] ?? ''))),
          'minutes'=>max(10, (int)($s['btc_quote_minutes'] ?? 60)),
          'confs'  =>max(1, (int)($s['btc_confirmations'] ?? 1))];
}
function btc_norm($a){ return stripos($a, 'bc1') === 0 ? strtolower($a) : $a; }

/* a payment method set up as "Bitcoin — pay on the site" with a valid wallet address */
function btc_method($m){ return ($m['type'] ?? '') === 'bitcoin'; }
function btc_ready(){ return btc_valid_address(btc_settings()['address']); }

/* ---------------- addresses ---------------- */
/* bc1… (SegWit / Taproot, checksum checked) or legacy 1… / 3… (Base58Check) on the main network */
function btc_valid_address($a){
  $a = trim((string)$a);
  if(preg_match('/^bc1[02-9ac-hj-np-z]{8,87}$/i', $a)) return btc_bech32_ok($a);
  if(preg_match('/^[13][1-9A-HJ-NP-Za-km-z]{25,34}$/', $a)) return btc_base58_ok($a);
  return false;
}
function btc_bech32_ok($a){
  if(strtolower($a) !== $a && strtoupper($a) !== $a) return false;
  $a = strtolower($a); $cs = 'qpzry9x8gf2tvdw0s3jn54khce6mua7l';
  $pos = strrpos($a, '1'); $hrp = substr($a, 0, $pos); $data = [];
  foreach(str_split(substr($a, $pos + 1)) as $ch) $data[] = strpos($cs, $ch);
  if($hrp !== 'bc' || count($data) < 7 || in_array(false, $data, true)) return false;
  $vals = [];
  foreach(str_split($hrp) as $ch) $vals[] = ord($ch) >> 5;
  $vals[] = 0;
  foreach(str_split($hrp) as $ch) $vals[] = ord($ch) & 31;
  $gen = [0x3b6a57b2, 0x26508e6d, 0x1ea119fa, 0x3d4233dd, 0x2a1462b3]; $chk = 1;
  foreach(array_merge($vals, $data) as $v){
    $b = $chk >> 25; $chk = (($chk & 0x1ffffff) << 5) ^ $v;
    for($i = 0; $i < 5; $i++) if(($b >> $i) & 1) $chk ^= $gen[$i];
  }
  $ver = $data[0];
  if($ver > 16) return false;
  if($chk !== ($ver === 0 ? 1 : 0x2bc830a3)) return false;
  $len = (int)floor((count($data) - 7) * 5 / 8);   // witness program bytes
  return $ver === 0 ? in_array($len, [20, 32], true) : $len >= 2 && $len <= 40;
}
function btc_base58_ok($a){
  $alpha = '123456789ABCDEFGHJKLMNPQRSTUVWXYZabcdefghijkmnopqrstuvwxyz';
  $num = [];   // base-256 digits, least significant first
  foreach(str_split($a) as $ch){
    $carry = strpos($alpha, $ch);
    foreach($num as $i=>$byte){ $carry += $byte * 58; $num[$i] = $carry & 0xff; $carry >>= 8; }
    while($carry){ $num[] = $carry & 0xff; $carry >>= 8; }
  }
  $raw = str_repeat("\0", strspn($a, '1')).implode('', array_map('chr', array_reverse($num)));
  if(strlen($raw) !== 25 || !in_array(ord($raw[0]), [0, 5], true)) return false;
  return substr(hash('sha256', hash('sha256', substr($raw, 0, 21), true), true), 0, 4) === substr($raw, 21);
}

/* ---------------- web requests ---------------- */
/* [HTTP status (0 = no answer), decoded JSON or null] */
function btc_get($url, $timeout=6){
  $code = 0; $body = null;
  if(function_exists('curl_init')){
    $c = curl_init($url);
    curl_setopt_array($c, [CURLOPT_RETURNTRANSFER=>true, CURLOPT_TIMEOUT=>$timeout, CURLOPT_CONNECTTIMEOUT=>4,
      CURLOPT_FOLLOWLOCATION=>true, CURLOPT_MAXREDIRS=>3, CURLOPT_USERAGENT=>'Mozilla/5.0 (shop payment check)',
      CURLOPT_HTTPHEADER=>['Accept: application/json']]);
    $body = curl_exec($c); $code = (int)curl_getinfo($c, CURLINFO_HTTP_CODE);
    curl_close($c);
  } elseif(ini_get('allow_url_fopen')){
    $ctx = stream_context_create(['http'=>['timeout'=>$timeout, 'ignore_errors'=>true,
      'header'=>"Accept: application/json\r\nUser-Agent: Mozilla/5.0 (shop payment check)\r\n"]]);
    $body = @file_get_contents($url, false, $ctx);
    if(isset($http_response_header[0]) && preg_match('#\s(\d{3})\s#', $http_response_header[0].' ', $m)) $code = (int)$m[1];
  }
  if(!is_string($body)) return [0, null];
  return [$code, json_decode($body, true)];
}

/* ---------------- price ---------------- */
/* ['price'=>price of 1 BTC in $cur, 'cur'=>…, 'source'=>…, 'time'=>…] — refreshed every 5 minutes, null if no feed answers.
   $cur is the shop currency (GBP, USD, EUR, JPY, CAD, AUD and CHF are quoted by every feed). */
function btc_rate($cur){
  $cur = strtoupper(preg_replace('/[^A-Za-z]/', '', (string)$cur));
  $c = data_read('btc-rate') ?: [];
  $fresh = fn($max)=>!empty($c['price']) && ($c['cur'] ?? '') === $cur && time() - ($c['time'] ?? 0) < $max;
  if($fresh(300)) return $c;
  $feeds = ($e = getenv('FK_PRICE_API')) ? [[$e, fn($j)=>$j[$cur] ?? 0, 'test feed']] : [
    ['https://mempool.space/api/v1/prices', fn($j)=>$j[$cur] ?? 0, 'mempool.space'],
    ["https://api.coinbase.com/v2/prices/BTC-$cur/spot", fn($j)=>$j['data']['amount'] ?? 0, 'Coinbase'],
    ["https://api.kraken.com/0/public/Ticker?pair=XBT$cur", fn($j)=>is_array($j['result'] ?? null) && $j['result'] ? (reset($j['result'])['c'][0] ?? 0) : 0, 'Kraken'],
  ];
  foreach($feeds as [$url, $pick, $name]){
    [$code, $j] = btc_get($url, 5);
    $v = $code === 200 && is_array($j) ? (float)$pick($j) : 0;
    if($v > 1000){ $c = ['price'=>round($v, 2), 'cur'=>$cur, 'source'=>$name, 'time'=>time()]; data_write('btc-rate', $c); return $c; }
  }
  return $fresh(3600) ? $c : null;   // a price from the last hour beats none
}

function btc_amount($sats){ return number_format($sats / 1e8, 8, '.', ''); }

/* BIP21 link: wallets that open it fill in the address, the amount and a label */
function btc_uri($o){
  $b = $o['btc'];
  $q = array_filter(['amount'=>!empty($b['sats']) ? btc_amount($b['sats']) : null,
                     'label'=>$GLOBALS['STORE']['settings']['brand'], 'message'=>'Order '.$o['ref']]);
  return 'bitcoin:'.$b['address'].'?'.http_build_query($q, '', '&', PHP_QUERY_RFC3986);
}

/* ---------------- quote ---------------- */
/* The BTC amount for the order total, held for btc_quote_minutes, then renewed at the current price
   while the order is unpaid. Every amount quoted is kept, so a payment of any of them is recognised.
   Returns true when the order changed (save it). */
function btc_quote(&$o){
  $b = $o['btc'] ?? null;
  if(!$b || !empty($b['txid']) || !empty($b['reported']) || in_array($o['status'] ?? 'new', ['paid','shipped','cancelled'], true)) return false;
  if(!empty($b['sats']) && time() < ($b['expires'] ?? 0)) return false;
  $r = btc_rate(order_base($o));
  if(!$r) return false;
  $sats = (int)ceil($o['total_usd'] / $r['price'] * 1e8);   /* total_usd holds the total in the order's currency */
  $o['btc'] = array_merge($b, ['sats'=>$sats, 'rate'=>$r['price'], 'rate_cur'=>$r['cur'], 'rate_source'=>$r['source'], 'quoted'=>time(),
    'expires'=>time() + btc_settings()['minutes'] * 60, 'quotes'=>array_slice(array_merge($b['quotes'] ?? [], [$sats]), -24)]);
  return true;
}

/* ---------------- finding the payment ---------------- */
/* payments into $addr seen by the block explorer, newest first — shared by all orders for 20 seconds */
function btc_incoming($addr){
  $cache = data_read('btc-seen') ?: [];
  if(isset($cache[$addr]) && time() - $cache[$addr]['time'] < 20) return $cache[$addr];
  foreach(btc_apis() as $api){
    [$code, $txs] = btc_get($api.'/address/'.rawurlencode($addr).'/txs');
    if($code !== 200 || !is_array($txs)) continue;
    [, $tip] = btc_get($api.'/blocks/tip/height');
    $out = [];
    foreach($txs as $tx) if($p = btc_tx_to($tx, $addr)) $out[] = $p;
    $cache = array_filter($cache, fn($c)=>time() - $c['time'] < 3600);
    $cache[$addr] = ['time'=>time(), 'tip'=>is_numeric($tip) ? (int)$tip : 0, 'txs'=>$out];
    data_write('btc-seen', $cache);
    return $cache[$addr];
  }
  return null;
}
/* what one explorer transaction pays to $addr, or null */
function btc_tx_to($tx, $addr){
  foreach($tx['vin'] ?? [] as $in) if(($in['prevout']['scriptpubkey_address'] ?? '') === $addr) return null;   // money leaving the wallet
  $sats = 0;
  foreach($tx['vout'] ?? [] as $v) if(($v['scriptpubkey_address'] ?? '') === $addr) $sats += (int)$v['value'];
  if(!$sats || !preg_match('/^[0-9a-f]{64}$/', $tx['txid'] ?? '')) return null;
  return ['txid'=>$tx['txid'], 'sats'=>$sats, 'confirmed'=>!empty($tx['status']['confirmed']),
          'height'=>(int)($tx['status']['block_height'] ?? 0), 'time'=>(int)($tx['status']['block_time'] ?? 0)];
}
/* one transaction by its ID: ['found'=>payment or null] — or null if no explorer answered */
function btc_lookup_tx($txid, $addr){
  foreach(btc_apis() as $api){
    [$code, $tx] = btc_get($api.'/tx/'.$txid);
    if($code === 200 && is_array($tx)){
      [, $tip] = btc_get($api.'/blocks/tip/height');
      return ['found'=>btc_tx_to($tx, $addr), 'exists'=>true, 'tip'=>is_numeric($tip) ? (int)$tip : 0];
    }
    if($code === 400 || $code === 404) return ['found'=>null, 'exists'=>false, 'tip'=>0];
  }
  return null;
}

/* which order each transaction belongs to, so one payment can never count for two orders */
function btc_claims(){ return data_read('btc-claims') ?: []; }
function btc_claim($txid, $ref){ $c = btc_claims(); $c[$txid] = $ref; data_write('btc-claims', $c); }

/* order state for the customer and the admin */
function btc_state($o){
  $b = $o['btc'] ?? [];
  if(empty($b['txid'])) return !empty($b['reported']) ? 'reported' : 'awaiting';
  if(($b['confirmations'] ?? 0) < btc_settings()['confs']) return 'seen';
  return ($b['paid_sats'] ?? 0) >= ($b['expected_sats'] ?? PHP_INT_MAX) * 0.995 ? 'confirmed' : 'short';
}
function btc_state_label($s){
  return ['awaiting'=>'Awaiting payment', 'reported'=>'Payment reported — checking', 'seen'=>'Payment seen — confirming',
          'confirmed'=>'Paid — confirmed', 'short'=>'Paid less than the amount due'][$s] ?? $s;
}

/* record a payment against the order */
function btc_attach(&$o, $pay, $tip){
  $b = &$o['btc'];
  $quotes = $b['quotes'] ?? [$b['sats'] ?? 0];
  usort($quotes, fn($x, $y)=>abs($pay['sats'] - $x) <=> abs($pay['sats'] - $y));   // the amount they most likely paid
  $b['txid'] = $pay['txid']; $b['paid_sats'] = $pay['sats']; $b['expected_sats'] = $quotes[0] ?: ($b['sats'] ?? 0);
  $b['confirmations'] = $pay['confirmed'] && $tip ? max(1, $tip - $pay['height'] + 1) : 0;
  $b['seen_at'] = time(); unset($b['reported']);
  btc_claim($pay['txid'], $o['ref']);
}

/* Looks for this order's payment and updates confirmations. Sends the receipt when a payment first shows
   up and a second email when it confirms, and marks a fully paid order Paid. Returns true if the order changed. */
function btc_sync(&$o, $force=false){
  if(empty($o['btc']['address']) || ($o['status'] ?? '') === 'cancelled') return false;
  if(!$force && time() - ($o['btc']['checked'] ?? 0) < 15) return false;
  $o['btc']['checked'] = time();
  $before = btc_state($o);
  $b = $o['btc'];
  if(empty($b['txid']) && !empty($b['reported'])){          // a transaction ID the customer gave while the explorers were down
    $r = btc_lookup_tx($b['reported'], $b['address']);
    if($r && $r['found'] && !isset(btc_claims()[$r['found']['txid']])) btc_attach($o, $r['found'], $r['tip']);
    elseif($r && !$r['found']) unset($o['btc']['reported']);
  }
  if(empty($o['btc']['txid']) || ($o['btc']['confirmations'] ?? 0) < btc_settings()['confs']){
    $seen = btc_incoming($b['address']);
    if($seen){
      if(empty($o['btc']['txid'])){
        $claims = btc_claims(); $since = strtotime($o['time_iso'] ?? 'now') - 3600; $best = null; $bestd = 0.1;
        foreach($seen['txs'] as $t){
          if(isset($claims[$t['txid']]) || ($t['time'] && $t['time'] < $since)) continue;
          foreach($b['quotes'] ?? [] as $q){ $d = abs($t['sats'] / max(1, $q) - 1); if($d <= $bestd){ $best = $t; $bestd = $d; } }
        }
        if($best) btc_attach($o, $best, $seen['tip']);
      } else {
        foreach($seen['txs'] as $t) if($t['txid'] === $o['btc']['txid'] && $t['confirmed'] && $seen['tip'])
          $o['btc']['confirmations'] = max(1, $seen['tip'] - $t['height'] + 1);
      }
    }
  }
  $after = btc_state($o);
  if($before !== $after){
    if($after === 'seen') btc_mail($o, 'received');
    elseif($after === 'confirmed' || $after === 'short'){
      if($after === 'confirmed' && in_array($o['status'] ?? 'new', ['new','invoiced'], true)) $o['status'] = 'paid';
      btc_mail($o, $after);
    }
  }
  return true;
}

/* ---------------- emails ---------------- */
function btc_pay_link($o){
  $d = rtrim($GLOBALS['STORE']['settings']['domain'], '/');
  return $d.'/index.php?'.http_build_query(['p'=>'pay', 'ref'=>$o['ref'], 'k'=>order_key($o['ref'])]);
}

/* payment receipts to the shop (order email) and the customer, with a link to follow the transaction */
function btc_mail(&$o, $kind){
  $cfg = $GLOBALS['STORE']['settings']; $b = $o['btc'];
  $paid = btc_amount($b['paid_sats'] ?? 0); $due = btc_amount($b['expected_sats'] ?? ($b['sats'] ?? 0));
  $worth = money_in(($b['paid_sats'] ?? 0) / 1e8 * ($b['rate'] ?? 0), $b['rate_cur'] ?? order_base($o));
  $lines = [
    "Order:          {$o['ref']}",
    "Amount paid:    $paid BTC (about $worth)",
    "Amount due:     $due BTC",
    "To address:     {$b['address']}",
    "Transaction ID: {$b['txid']}",
    "Track it:       ".btc_tx_url($b['txid']),
  ];
  $conf = (int)($b['confirmations'] ?? 0);
  $need = btc_settings()['confs'];
  if($kind === 'received'){
    $subj_shop = "Bitcoin payment received — {$o['ref']} — $paid BTC";
    $subj_cus  = "Payment received — order {$o['ref']}";
    $lines[] = "Status:         on the blockchain, $conf of $need confirmation".($need === 1 ? '' : 's');
    $shop_note = "A customer has paid by Bitcoin. It isn't confirmed yet — you'll get another email when it confirms (usually 10–60 minutes). Don't ship until then.";
    $cus_note  = "We've received your Bitcoin payment. It's waiting for confirmation on the Bitcoin network, which usually takes 10–60 minutes — you don't need to do anything else. We'll email you again when it confirms, and your tracking number as soon as your order ships.";
  } elseif($kind === 'confirmed'){
    $subj_shop = "Bitcoin payment CONFIRMED — {$o['ref']} — ready to ship";
    $subj_cus  = "Payment confirmed — order {$o['ref']}";
    $lines[] = "Status:         confirmed ($conf confirmation".($conf === 1 ? '' : 's').")";
    $shop_note = "The payment has confirmed and the order is marked Paid. Pack and ship it.";
    $cus_note  = "Your payment has confirmed — thank you. We're packing your order now and will email your tracking number as soon as it ships from Japan.";
  } else {   // short
    $subj_shop = "Bitcoin payment SHORT — {$o['ref']}";
    $subj_cus  = "Payment received, but less than the amount due — order {$o['ref']}";
    $lines[] = "Status:         confirmed, ".btc_amount(max(0, ($b['expected_sats'] ?? 0) - ($b['paid_sats'] ?? 0)))." BTC short";
    $shop_note = "The customer paid less than the amount quoted. Contact them before shipping.";
    $cus_note  = "Your payment confirmed, but it was less than the amount due. Please reply to this email and we'll sort out the difference — often an exchange or wallet takes its fee from the amount sent.";
  }
  $detail = implode("\n", $lines);
  $shop = shop_mail($cfg['order_email'], $subj_shop,
    "$shop_note\n\n$detail\n\nCustomer: {$o['name']} <{$o['email']}>\nOrder total: {$o['total']} {$o['currency']} (".money_in($o['total_usd'], order_base($o))." ".order_base($o).")\n",
    $o['email']);
  $cus = shop_mail($o['email'], $subj_cus,
    "$cus_note\n\n$detail\n\nYour order page: ".btc_pay_link($o)."\n\nQuestions: {$cfg['email']}\n{$cfg['legal_name']} — {$cfg['address']}\n",
    $cfg['email']);
  $o['btc']['mails'][] = [$kind, time(), $shop, $cus];
  return $shop;
}
