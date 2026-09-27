<?php
/* The shop's two languages: Dutch (Flanders, Brussels) and French (Wallonia, Brussels).
   Interface text is written in English in the code, wrapped in t(), and translated by inc/lang/ui-*.php.
   Shop content (products, guides, pages…) is written per language: see inc/content-*.php. */
if(!defined('FK_ROOT')){ http_response_code(404); exit; }

const LANGS       = ['nl'=>'Nederlands', 'fr'=>'Français'];   /* the first is the default */
const LANG_LOCALE = ['nl'=>'nl-BE', 'fr'=>'fr-BE'];
const LANG_OG     = ['nl'=>'nl_BE', 'fr'=>'fr_BE'];

/* first path segments for clean addresses, per language: /fr/boutique, /nl/winkel … */
const LANG_PATHS = [
  'nl'=>['catalog'=>'winkel', 'product'=>'producten', 'sets'=>'sets', 'collection'=>'kaarten', 'guides'=>'gidsen',
         'cart'=>'winkelwagen', 'checkout'=>'afrekenen', 'received'=>'bestelling-ontvangen', 'how'=>'hoe-bestellen',
         'shipping'=>'verzending-en-retour', 'payment'=>'betaalmethoden', 'faq'=>'veelgestelde-vragen', 'contact'=>'contact', 'pay'=>'betalen'],
  'fr'=>['catalog'=>'boutique', 'product'=>'produits', 'sets'=>'series', 'collection'=>'cartes', 'guides'=>'guides',
         'cart'=>'panier', 'checkout'=>'commander', 'received'=>'commande-recue', 'how'=>'comment-commander',
         'shipping'=>'livraison-et-retours', 'payment'=>'moyens-de-paiement', 'faq'=>'questions-frequentes', 'contact'=>'contact', 'pay'=>'payer'],
];

function lang(){ $L = $GLOBALS['LANG'] ?? ''; return isset(LANGS[$L]) ? $L : (string)array_key_first(LANGS); }

/* interface text: t('Add to order'), t('Free shipping on orders over %s', $amount) */
function t($s, ...$args){
  static $dict = [];
  $L = lang();
  if(!isset($dict[$L])){ $f = FK_ROOT."/inc/lang/ui-$L.php"; $dict[$L] = is_file($f) ? (require $f) : []; }
  $out = $dict[$L][$s] ?? null;
  if($out === null){ $out = $s; if(!empty($GLOBALS['FK_I18N_LOG'])) $GLOBALS['FK_I18N_MISSING'][$L][$s] = true; }
  return $args ? vsprintf($out, $args) : $out;
}
/* singular / plural: tn(3, '%d product', '%d products') */
function tn($n, $one, $many){ return t($n == 1 ? $one : $many, $n); }

/* amounts the Belgian way: "1 234,56 €" in French, "€ 1.234,56" in Dutch */
function fmt_amount($v, $dec, $sym){
  $sym = trim((string)$sym);
  if(lang() === 'fr') return number_format($v, $dec, ',', "\u{202F}")."\u{00A0}".$sym;
  return $sym."\u{00A0}".number_format($v, $dec, ',', '.');
}
function fmt_num($v, $dec=0){ return lang() === 'fr' ? number_format($v, $dec, ',', "\u{202F}") : number_format($v, $dec, ',', '.'); }

const MONTHS = ['nl'=>['januari','februari','maart','april','mei','juni','juli','augustus','september','oktober','november','december'],
                'fr'=>['janvier','février','mars','avril','mai','juin','juillet','août','septembre','octobre','novembre','décembre']];
function fmt_date($ts){ return date('j', $ts).' '.MONTHS[lang()][(int)date('n', $ts) - 1].' '.date('Y', $ts); }

/* country names in the shopper's language (the admin's list holds the codes) */
const COUNTRY_NAMES = [
  'nl'=>['BE'=>'België', 'NL'=>'Nederland', 'LU'=>'Luxemburg', 'FR'=>'Frankrijk', 'DE'=>'Duitsland'],
  'fr'=>['BE'=>'Belgique', 'NL'=>'Pays-Bas', 'LU'=>'Luxembourg', 'FR'=>'France', 'DE'=>'Allemagne'],
];
function country_name($code, $fallback=''){ return COUNTRY_NAMES[lang()][$code] ?? ($fallback !== '' ? $fallback : $code); }

/* the language a visitor prefers, from their browser: French if they rank it above Dutch */
function lang_from_browser(){
  $best = (string)array_key_first(LANGS); $bq = -1;
  foreach(explode(',', strtolower((string)($_SERVER['HTTP_ACCEPT_LANGUAGE'] ?? ''))) as $part){
    $bits = explode(';q=', trim($part)); $code = substr(trim($bits[0]), 0, 2); $q = isset($bits[1]) ? (float)$bits[1] : 1.0;
    if(isset(LANGS[$code]) && $q > $bq){ $best = $code; $bq = $q; }
  }
  return $best;
}

/* lower/upper case that handles accents (É, Ü…), and still works on hosts without the mbstring extension */
function lc($s){ return function_exists('mb_strtolower') ? mb_strtolower((string)$s, 'UTF-8') : strtolower((string)$s); }
function uc($s){ return function_exists('mb_strtoupper') ? mb_strtoupper((string)$s, 'UTF-8') : strtoupper((string)$s); }
