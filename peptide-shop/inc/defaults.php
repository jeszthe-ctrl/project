<?php
/* Starting data for a fresh install. On first load it is copied to data/store.php,
   and from then on everything is edited in admin.php — not here.
   Category, article and page text lives in content.php.

   The products, prices, batch numbers and shipping rates below are SAMPLES to show how the shop works:
   replace them with your own in the admin before you open. Sequences, formulas, molecular weights and
   CAS numbers are reference values for each compound; check them against your supplier's COA. */
if(!defined('FK_ROOT')){ http_response_code(404); exit; }

$d = [
  'settings' => [
    'brand'         => 'Aminova Research',   /* placeholder name: change it in Admin → Settings */
    'mark'          => 'A',                  /* the letter in the square logo mark */
    'tagline'       => 'Research peptides with a COA for every batch',
    'legal_name'    => 'Aminova Research LLC',
    'address'       => 'United States',
    'email'         => 'support@example.com',
    'phone'         => '',
    'order_email'   => 'orders@example.com',
    'domain'        => 'https://example.com',
    'ship_from'     => 'the United States',  /* "Ships from …" wording */
    'ship_country'  => 'US',                 /* country code of where you ship from (for Google's return-policy data) */
    'reply_hours'   => 12,
    'hold_hours'    => 24,
    'min_order_usd' => 0,                    /* 0 = no minimum */
    'ruo_gate'      => true,                 /* first visit: "I'm 21+ and buying for research" confirmation */
    'strip_link_text' => 'Peptide calculator →',
    'strip_link_url'  => '',                 /* blank = the calculator */
    'hero_image'    => '',
    'shipping_reviewed' => false,        /* the dashboard reminds you until you save your own rates (Admin → Shipping) */
    'pretty_urls'   => false,            /* clean addresses like /products/bpc-157-5mg: switched on by themselves when the host supports them */
    'pretty_auto'   => false,            /* set once that automatic switch has happened, so turning them off in the admin sticks */
    'free_ship_usd' => 200,              /* free Standard shipping from this goods total (USD); 0 = off */
    'content_version' => 1,              /* set by the shop: which built-in content updates are applied */
    'moved_to'      => '',               /* an old domain running this code can send every visitor here (301) */
    'google_verify' => '', 'bing_verify' => '',   /* Search Console / Bing verification codes (Admin → Settings) */
    /* SMTP for order emails (Admin → Settings → Email); blank host = the web host's mail() */
    'smtp_host' => '', 'smtp_port' => 465, 'smtp_secure' => 'ssl', 'smtp_user' => '', 'smtp_pass' => '',

    /* Bitcoin: orders paid with a "Bitcoin" payment method are paid to this address from the order page.
       Bitcoin stays hidden at checkout until you enter your own address in Admin → Payments. */
    'btc_address'       => '',
    'btc_quote_minutes' => 60,           /* how long a BTC amount is held before it is renewed at the current price */
    'btc_confirmations' => 1,            /* blockchain confirmations before an order is marked Paid */

    /* live chat: paste your chat service's code in Admin → Settings → Live chat (it loads after the page) */
    'chat_code' => '',
  ],

  'currencies' => [
    'USD' => ['rate'=>1,    'sym'=>'$',   'dec'=>2],
    'EUR' => ['rate'=>0.87635, 'sym'=>'€', 'dec'=>2],
    'GBP' => ['rate'=>0.79, 'sym'=>'£',   'dec'=>2],
    'CAD' => ['rate'=>1.37, 'sym'=>'CA$', 'dec'=>2],
    'AUD' => ['rate'=>1.52, 'sym'=>'A$',  'dec'=>2],
  ],

  /* status: in | new | low | preorder | soldout.
     compound: groups sizes of the same peptide in the library.  mg: peptide per vial (fills in the calculator).
     mw: molecular weight in g/mol.  ladder: [min_qty, unit_price_usd] ascending.  weight: kg per unit (for shipping).
     batch: the lot number of the batch you are shipping; its COA is uploaded on the product in the admin. */
  'products' => [
    ['id'=>'bpc-157-5mg', 'sku'=>'AR-BPC-05', 'name'=>'BPC-157 5 mg', 'compound'=>'BPC-157', 'cat'=>'repair',
     'size'=>'5 mg', 'mg'=>5, 'form'=>'Lyophilized powder', 'purity'=>'≥99%', 'batch'=>'BPC-2611-A',
     'seq'=>'Gly-Glu-Pro-Pro-Pro-Gly-Lys-Pro-Ala-Asp-Asp-Ala-Gly-Leu-Val', 'formula'=>'C62H98N16O22', 'mw'=>1419.55, 'cas'=>'137525-51-0',
     'storage'=>'−20 °C, dry and dark', 'moq'=>1, 'step'=>1, 'status'=>'in', 'release'=>'', 'weight'=>0.02, 'hidden'=>false,
     'ladder'=>[[1,39.00],[5,35.00],[10,31.00]],
     'desc'=>'BPC-157 is a synthetic 15-amino-acid peptide (pentadecapeptide), supplied as 5 mg of lyophilized powder for laboratory research.

Its sequence corresponds to part of a protein found in gastric juice, which gave it the name "Body Protection Compound". BPC-157 has been studied in preclinical cell and animal models of tissue repair and angiogenesis. It is stable in solution compared with many peptides, which makes it a common reference compound.

- 5 mg per vial, lyophilized, HPLC-purified
- Batch-specific certificate of analysis (HPLC and MS)
- Also available as [BPC-157 10 mg](product:bpc-157-10mg)
- For research use only. Not for human or veterinary use.'],
    ['id'=>'bpc-157-10mg', 'sku'=>'AR-BPC-10', 'name'=>'BPC-157 10 mg', 'compound'=>'BPC-157', 'cat'=>'repair',
     'size'=>'10 mg', 'mg'=>10, 'form'=>'Lyophilized powder', 'purity'=>'≥99%', 'batch'=>'BPC-2611-B',
     'seq'=>'Gly-Glu-Pro-Pro-Pro-Gly-Lys-Pro-Ala-Asp-Asp-Ala-Gly-Leu-Val', 'formula'=>'C62H98N16O22', 'mw'=>1419.55, 'cas'=>'137525-51-0',
     'storage'=>'−20 °C, dry and dark', 'moq'=>1, 'step'=>1, 'status'=>'in', 'release'=>'', 'weight'=>0.02, 'hidden'=>false,
     'ladder'=>[[1,69.00],[5,62.00],[10,55.00]],
     'desc'=>'BPC-157 in a 10 mg vial: the same synthetic 15-amino-acid peptide as our [5 mg vial](product:bpc-157-5mg), for larger experiments.

BPC-157 has been studied in preclinical cell and animal models of tissue repair and angiogenesis. The larger vial lowers the cost per milligram for longer studies.

- 10 mg per vial, lyophilized, HPLC-purified
- Batch-specific certificate of analysis (HPLC and MS)
- For research use only. Not for human or veterinary use.'],
    ['id'=>'tb-500-5mg', 'sku'=>'AR-TB5-05', 'name'=>'TB-500 5 mg', 'compound'=>'TB-500', 'cat'=>'repair',
     'size'=>'5 mg', 'mg'=>5, 'form'=>'Lyophilized powder', 'purity'=>'≥98%', 'batch'=>'TB5-2610-A',
     'seq'=>'Ac-Leu-Lys-Lys-Thr-Glu-Thr-Gln', 'formula'=>'C38H68N10O14', 'mw'=>889.02, 'cas'=>'885340-08-9',
     'storage'=>'−20 °C, dry and dark', 'moq'=>1, 'step'=>1, 'status'=>'in', 'release'=>'', 'weight'=>0.02, 'hidden'=>false,
     'ladder'=>[[1,45.00],[5,40.00],[10,36.00]],
     'desc'=>'TB-500 is a synthetic N-acetylated heptapeptide (Ac-LKKTETQ) corresponding to the actin-binding region of thymosin β4, supplied as 5 mg of lyophilized powder.

Thymosin β4 is a 43-residue protein that binds G-actin. The LKKTETQ fragment carries its actin-binding motif and is used in preclinical research on cell migration and tissue repair.

- 5 mg per vial, lyophilized, HPLC-purified
- N-terminal acetylation included in the formula and molecular weight
- Batch-specific certificate of analysis (HPLC and MS)
- For research use only. Not for human or veterinary use.'],
    ['id'=>'ghk-cu-50mg', 'sku'=>'AR-GHK-50', 'name'=>'GHK-Cu 50 mg', 'compound'=>'GHK-Cu', 'cat'=>'repair',
     'size'=>'50 mg', 'mg'=>50, 'form'=>'Lyophilized powder (blue)', 'purity'=>'≥98%', 'batch'=>'GHK-2609-A',
     'seq'=>'Gly-His-Lys · Cu(II)', 'formula'=>'C14H24N6O4Cu', 'mw'=>403.92, 'cas'=>'89030-95-5',
     'storage'=>'−20 °C, dry and dark', 'moq'=>1, 'step'=>1, 'status'=>'in', 'release'=>'', 'weight'=>0.02, 'hidden'=>false,
     'ladder'=>[[1,35.00],[5,31.00],[10,28.00]],
     'desc'=>'GHK-Cu is the copper(II) complex of the tripeptide glycyl-L-histidyl-L-lysine, supplied as 50 mg of lyophilized blue powder.

GHK occurs naturally in plasma and binds copper ions with high affinity. GHK-Cu is used in research on copper transport, extracellular matrix and gene expression, and in cosmetic science research. The copper complex gives the powder its blue colour.

- 50 mg per vial, lyophilized
- Formula and molecular weight given for the copper complex
- Batch-specific certificate of analysis
- For research use only. Not for human or veterinary use.'],
    ['id'=>'ipamorelin-5mg', 'sku'=>'AR-IPA-05', 'name'=>'Ipamorelin 5 mg', 'compound'=>'Ipamorelin', 'cat'=>'gh',
     'size'=>'5 mg', 'mg'=>5, 'form'=>'Lyophilized powder', 'purity'=>'≥99%', 'batch'=>'IPA-2611-A',
     'seq'=>'Aib-His-D-2Nal-D-Phe-Lys-NH2', 'formula'=>'C38H49N9O5', 'mw'=>711.85, 'cas'=>'170851-70-4',
     'storage'=>'−20 °C, dry and dark', 'moq'=>1, 'step'=>1, 'status'=>'in', 'release'=>'', 'weight'=>0.02, 'hidden'=>false,
     'ladder'=>[[1,36.00],[5,32.00],[10,29.00]],
     'desc'=>'Ipamorelin is a synthetic pentapeptide growth hormone secretagogue, supplied as 5 mg of lyophilized powder.

It contains non-natural residues (Aib, D-2-naphthylalanine and D-phenylalanine) and an amidated C-terminus. Ipamorelin is a selective agonist of the ghrelin/growth hormone secretagogue receptor (GHS-R1a) and is used in receptor and endocrine research.

- 5 mg per vial, lyophilized, HPLC-purified
- Batch-specific certificate of analysis (HPLC and MS)
- For research use only. Not for human or veterinary use.'],
    ['id'=>'sermorelin-5mg', 'sku'=>'AR-SER-05', 'name'=>'Sermorelin 5 mg', 'compound'=>'Sermorelin', 'cat'=>'gh',
     'size'=>'5 mg', 'mg'=>5, 'form'=>'Lyophilized powder', 'purity'=>'≥98%', 'batch'=>'SER-2610-A',
     'seq'=>'Tyr-Ala-Asp-Ala-Ile-Phe-Thr-Asn-Ser-Tyr-Arg-Lys-Val-Leu-Gly-Gln-Leu-Ser-Ala-Arg-Lys-Leu-Leu-Gln-Asp-Ile-Met-Ser-Arg-NH2',
     'formula'=>'C149H246N44O42S', 'mw'=>3357.92, 'cas'=>'86168-78-7',
     'storage'=>'−20 °C, dry and dark', 'moq'=>1, 'step'=>1, 'status'=>'in', 'release'=>'', 'weight'=>0.02, 'hidden'=>false,
     'ladder'=>[[1,48.00],[5,43.00],[10,39.00]],
     'desc'=>'Sermorelin is GHRH(1–29)-NH2, the first 29 amino acids of human growth-hormone-releasing hormone, supplied as 5 mg of lyophilized powder.

GHRH(1–29) is the shortest fragment of the 44-residue hormone with full activity at the GHRH receptor, which makes it a standard tool in GHRH receptor research. It contains a methionine, so protect solutions from oxidation.

- 5 mg per vial, lyophilized, HPLC-purified
- C-terminal amide included in the formula and molecular weight
- Batch-specific certificate of analysis (HPLC and MS)
- For research use only. Not for human or veterinary use.'],
    ['id'=>'selank-5mg', 'sku'=>'AR-SLK-05', 'name'=>'Selank 5 mg', 'compound'=>'Selank', 'cat'=>'neuro',
     'size'=>'5 mg', 'mg'=>5, 'form'=>'Lyophilized powder', 'purity'=>'≥99%', 'batch'=>'SLK-2611-A',
     'seq'=>'Thr-Lys-Pro-Arg-Pro-Gly-Pro', 'formula'=>'C33H57N11O9', 'mw'=>751.88, 'cas'=>'129954-34-3',
     'storage'=>'−20 °C, dry and dark', 'moq'=>1, 'step'=>1, 'status'=>'in', 'release'=>'', 'weight'=>0.02, 'hidden'=>false,
     'ladder'=>[[1,34.00],[5,30.00],[10,27.00]],
     'desc'=>'Selank is a synthetic heptapeptide (TKPRPGP), supplied as 5 mg of lyophilized powder.

It extends tuftsin (Thr-Lys-Pro-Arg), a natural immunoglobulin fragment, with a Pro-Gly-Pro tail that slows its breakdown by peptidases. Selank is used in neuroscience research.

- 5 mg per vial, lyophilized, HPLC-purified
- Batch-specific certificate of analysis (HPLC and MS)
- For research use only. Not for human or veterinary use.'],
    ['id'=>'semax-5mg', 'sku'=>'AR-SMX-05', 'name'=>'Semax 5 mg', 'compound'=>'Semax', 'cat'=>'neuro',
     'size'=>'5 mg', 'mg'=>5, 'form'=>'Lyophilized powder', 'purity'=>'≥99%', 'batch'=>'SMX-2611-A',
     'seq'=>'Met-Glu-His-Phe-Pro-Gly-Pro', 'formula'=>'C37H51N9O10S', 'mw'=>813.93, 'cas'=>'80714-61-0',
     'storage'=>'−20 °C, dry and dark', 'moq'=>1, 'step'=>1, 'status'=>'in', 'release'=>'', 'weight'=>0.02, 'hidden'=>false,
     'ladder'=>[[1,34.00],[5,30.00],[10,27.00]],
     'desc'=>'Semax is a synthetic heptapeptide (MEHFPGP), supplied as 5 mg of lyophilized powder.

It combines the ACTH(4–7) fragment Met-Glu-His-Phe with a stabilising Pro-Gly-Pro tail, and has no hormonal (corticotropic) activity. Semax is used in neuroscience research. It contains a methionine, so protect solutions from oxidation.

- 5 mg per vial, lyophilized, HPLC-purified
- Batch-specific certificate of analysis (HPLC and MS)
- For research use only. Not for human or veterinary use.'],
    ['id'=>'dsip-5mg', 'sku'=>'AR-DSP-05', 'name'=>'DSIP 5 mg', 'compound'=>'DSIP', 'cat'=>'neuro',
     'size'=>'5 mg', 'mg'=>5, 'form'=>'Lyophilized powder', 'purity'=>'≥98%', 'batch'=>'DSP-2610-A',
     'seq'=>'Trp-Ala-Gly-Gly-Asp-Ala-Ser-Gly-Glu', 'formula'=>'C35H48N10O15', 'mw'=>848.82, 'cas'=>'62568-57-4',
     'storage'=>'−20 °C, dry and dark', 'moq'=>1, 'step'=>1, 'status'=>'low', 'release'=>'', 'weight'=>0.02, 'hidden'=>false,
     'ladder'=>[[1,32.00],[5,28.00],[10,25.00]],
     'desc'=>'DSIP (delta sleep-inducing peptide) is a nonapeptide (WAGGDASGE), supplied as 5 mg of lyophilized powder.

DSIP was first isolated in 1977 during research on sleep regulation. It is used in neuroscience research. It contains a tryptophan, so protect it from light and oxidation.

- 5 mg per vial, lyophilized, HPLC-purified
- Batch-specific certificate of analysis (HPLC and MS)
- For research use only. Not for human or veterinary use.'],
    ['id'=>'mots-c-10mg', 'sku'=>'AR-MOT-10', 'name'=>'MOTS-c 10 mg', 'compound'=>'MOTS-c', 'cat'=>'longevity',
     'size'=>'10 mg', 'mg'=>10, 'form'=>'Lyophilized powder', 'purity'=>'≥98%', 'batch'=>'MOT-2611-A',
     'seq'=>'Met-Arg-Trp-Gln-Glu-Met-Gly-Tyr-Ile-Phe-Tyr-Pro-Arg-Lys-Leu-Arg', 'formula'=>'C101H152N28O22S2', 'mw'=>2174.61, 'cas'=>'1627580-64-6',
     'storage'=>'−20 °C, dry and dark', 'moq'=>1, 'step'=>1, 'status'=>'new', 'release'=>'', 'weight'=>0.02, 'hidden'=>false,
     'ladder'=>[[1,58.00],[5,52.00],[10,47.00]],
     'desc'=>'MOTS-c is a 16-amino-acid peptide encoded in the mitochondrial 12S rRNA gene, supplied as 10 mg of lyophilized powder.

It was described in 2015 as a mitochondrial-derived peptide and is used in research on metabolism, AMPK signalling and mitochondrial–nuclear communication. It contains two methionines and a tryptophan, so protect it from oxidation and light.

- 10 mg per vial, lyophilized, HPLC-purified
- Batch-specific certificate of analysis (HPLC and MS)
- For research use only. Not for human or veterinary use.'],
    ['id'=>'epitalon-10mg', 'sku'=>'AR-EPI-10', 'name'=>'Epitalon 10 mg', 'compound'=>'Epitalon', 'cat'=>'longevity',
     'size'=>'10 mg', 'mg'=>10, 'form'=>'Lyophilized powder', 'purity'=>'≥99%', 'batch'=>'EPI-2611-A',
     'seq'=>'Ala-Glu-Asp-Gly', 'formula'=>'C14H22N4O9', 'mw'=>390.35, 'cas'=>'307297-39-8',
     'storage'=>'−20 °C, dry and dark', 'moq'=>1, 'step'=>1, 'status'=>'in', 'release'=>'', 'weight'=>0.02, 'hidden'=>false,
     'ladder'=>[[1,38.00],[5,34.00],[10,30.00]],
     'desc'=>'Epitalon (also spelled Epithalon) is a synthetic tetrapeptide, Ala-Glu-Asp-Gly, supplied as 10 mg of lyophilized powder.

It was developed from research on a peptide extract of the pineal gland and is used in research on telomerase activity and cellular ageing. Its short, polar sequence dissolves readily in water.

- 10 mg per vial, lyophilized, HPLC-purified
- Batch-specific certificate of analysis (HPLC and MS)
- For research use only. Not for human or veterinary use.'],
    ['id'=>'thymosin-alpha-1-5mg', 'sku'=>'AR-TA1-05', 'name'=>'Thymosin Alpha-1 5 mg', 'compound'=>'Thymosin α1', 'cat'=>'longevity',
     'size'=>'5 mg', 'mg'=>5, 'form'=>'Lyophilized powder', 'purity'=>'≥98%', 'batch'=>'TA1-2610-A',
     'seq'=>'Ac-Ser-Asp-Ala-Ala-Val-Asp-Thr-Ser-Ser-Glu-Ile-Thr-Thr-Lys-Asp-Leu-Lys-Glu-Lys-Lys-Glu-Val-Val-Glu-Glu-Ala-Glu-Asn',
     'formula'=>'C129H215N33O55', 'mw'=>3108.31, 'cas'=>'62304-98-7',
     'storage'=>'−20 °C, dry and dark', 'moq'=>1, 'step'=>1, 'status'=>'in', 'release'=>'', 'weight'=>0.02, 'hidden'=>false,
     'ladder'=>[[1,52.00],[5,47.00],[10,42.00]],
     'desc'=>'Thymosin α1 is a 28-amino-acid peptide with an acetylated N-terminus, supplied as 5 mg of lyophilized powder.

It was first isolated from thymus tissue (thymosin fraction 5) and is used in immunology research, including studies of T-cell maturation and innate immune signalling. Its many acidic residues make it very water-soluble.

- 5 mg per vial, lyophilized, HPLC-purified
- N-terminal acetylation included in the formula and molecular weight
- Batch-specific certificate of analysis (HPLC and MS)
- For research use only. Not for human or veterinary use.'],
  ],

  /* countries: '*' = everywhere, or a list of country codes */
  /* type 'bitcoin' = paid on the site to btc_address above; any other method = you send the details */
  'payments' => [
    'bitcoin'  => ['label'=>'Bitcoin (BTC) — pay now', 'note'=>'Pay from any Bitcoin wallet as soon as you order. The exact amount and a QR code appear on the next page.', 'countries'=>'*', 'enabled'=>true, 'type'=>'bitcoin'],
    'crypto'   => ['label'=>'Other crypto (USDT, ETH)', 'note'=>'USDT (TRC-20 / ERC-20) or ETH. We send the wallet address with your invoice. Network fees are the sender\'s.', 'countries'=>'*', 'enabled'=>true],
    'zelle'    => ['label'=>'Zelle',            'note'=>'US customers only. We send our Zelle details with your invoice.', 'countries'=>['US'], 'enabled'=>true],
    'bank'     => ['label'=>'Bank transfer',    'note'=>'We send our bank details with your invoice. Quote your order reference.', 'countries'=>'*', 'enabled'=>true],
  ],

  'countries' => ['US'=>'United States','CA'=>'Canada','GB'=>'United Kingdom','IE'=>'Ireland','AU'=>'Australia','NZ'=>'New Zealand',
    'DE'=>'Germany','FR'=>'France','ES'=>'Spain','IT'=>'Italy','NL'=>'Netherlands','BE'=>'Belgium','SE'=>'Sweden','NO'=>'Norway',
    'DK'=>'Denmark','FI'=>'Finland','PL'=>'Poland','PT'=>'Portugal','CH'=>'Switzerland','AT'=>'Austria','CZ'=>'Czechia',
    'SG'=>'Singapore','JP'=>'Japan','KR'=>'South Korea','HK'=>'Hong Kong','IL'=>'Israel','AE'=>'United Arab Emirates','MX'=>'Mexico','BR'=>'Brazil'],

  /* shipping (USD) = zone per-order price + per-kg price × order weight, for each delivery option; totals round up
     to whole dollars. SAMPLE rates for a small padded parcel from the US: set your own in Admin → Shipping. */
  'shipping' => [
    'methods'  => ['standard'=>['label'=>'Standard', 'days'=>'2–5 working days'],
                   'express' =>['label'=>'Express',  'days'=>'1–2 working days']],
    'round_up' => true,
    'zones' => [
      ['name'=>'United States', 'countries'=>['US'], 'standard'=>['base'=>8, 'per_kg'=>4], 'express'=>['base'=>28, 'per_kg'=>10]],
      ['name'=>'Canada',        'countries'=>['CA'], 'standard'=>['base'=>22, 'per_kg'=>12], 'express'=>['base'=>45, 'per_kg'=>20]],
      ['name'=>'United Kingdom & Europe', 'countries'=>['GB','IE','DE','FR','ES','IT','NL','BE','SE','NO','DK','FI','PL','PT','CH','AT','CZ'],
                                'standard'=>['base'=>28, 'per_kg'=>14], 'express'=>['base'=>55, 'per_kg'=>24]],
      ['name'=>'Australia & New Zealand', 'countries'=>['AU','NZ'], 'standard'=>['base'=>32, 'per_kg'=>16], 'express'=>['base'=>60, 'per_kg'=>26]],
    ],
    'rest' => ['standard'=>['base'=>35, 'per_kg'=>18], 'express'=>['base'=>65, 'per_kg'=>28]],
  ],
];

$c = require __DIR__.'/content.php';
$d['settings']    = $c['settings'] + $d['settings'];
$d['categories']  = $c['categories'];
$d['faqs']        = $c['faqs'];
$d['guides']      = $c['guides'];
$d['pages']       = $c['pages'];
return $d;
