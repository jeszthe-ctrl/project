<?php
/* Starting SEO content for a fresh install (in French): category, series, set and collection pages,
   guides (in guides.php), home page text, legal pages and FAQ. Copied into data/store.php on first
   load and edited in admin.php after.

   Formatting (intros and guides): blank line = new paragraph, "## " heading, "- " bullet, **bold**,
   [text](link). Links can be full URLs or shop pages: product:ID, category:KEY, set:SLUG (sets and
   series), cards:SLUG (collections), guide:SLUG, page:shop|sets|guides|faq|shipping|payment|how|contact
   or page:SLUG for the pages below. "## Questions fréquentes" followed by "### question" lines is
   given to Google as FAQ data.
   {min_order} {reply_hours} {hold_hours} {countries} {free_ship} {standard} {express} {standard_days} {express_days}
   {brand} {company} {address} {email} are filled in with current values. */
if(!defined('FK_ROOT')){ http_response_code(404); exit; }

return [
  'settings' => [
    'strip_text'    => 'Cartes Pokémon japonaises authentiques · Scellées d’usine · Expédiées du Japon avec suivi',
    'hero_title'    => 'Cartes Pokémon japonaises, expédiées directement du Japon.',
    'hero_lede'     => 'Displays scellés, coffrets Dresseur d’Élite, coffrets collector, cartes rares et gradées PSA : des produits authentiques achetés auprès de la distribution japonaise, livrés chez vous en France, en Belgique et au Luxembourg.',
    'footer_blurb'  => 'Revendeur indépendant de cartes Pokémon japonaises authentiques, expédiées du Japon avec suivi vers la France, la Belgique, le Luxembourg et Monaco. Particuliers et boutiques bienvenus.',
    'home_seo_title'=> 'Carte Pokémon : displays, ETB et cartes rares du Japon',
    'home_seo_desc' => 'Cartes Pokémon japonaises authentiques : displays scellés, coffrets Dresseur d’Élite, cartes rares, Dracaufeu et cartes gradées PSA, expédiés du Japon avec suivi.',
    'home_intro'    => <<<'MD'
## Cartes Pokémon japonaises, livrées en France

{brand} vend des cartes Pokémon japonaises authentiques : des [displays Pokémon](category:boxes) scellés, des [coffrets Dresseur d’Élite (ETB)](category:etb), des [coffrets Pokémon](category:premium), des [cartes Pokémon rares](category:singles) et des [classeurs pour cartes Pokémon](category:accessories). Tout est acheté auprès de la distribution japonaise et expédié du Japon avec suivi, scellé tel qu’il est sorti d’usine.

## Pourquoi acheter des cartes Pokémon japonaises ?

Les extensions sortent d’abord au Japon, souvent plusieurs mois avant leur version française. Les displays japonais sont donc le moyen d’avoir les nouvelles cartes en premier : [Inferno X](set:inferno-x-flammes-fantasmagoriques) est sorti avant Flammes Fantasmagoriques, [Terastal Festival ex](set:terastal-festival-ex-evolutions-prismatiques) avant Évolutions Prismatiques. Beaucoup de collectionneurs préfèrent aussi la qualité d’impression japonaise. Tout savoir dans notre guide des [cartes Pokémon japonaises](guide:cartes-pokemon-japonaises).

## Prix, valeur et cartes rares

Combien vaut une carte Pokémon ? Notre guide du [prix des cartes Pokémon](guide:prix-carte-pokemon) explique la cote, la valeur et l’estimation d’une carte, et notre liste de la [carte Pokémon la plus chère](guide:carte-pokemon-la-plus-chere) retrace les records. Vous cherchez Dracaufeu ? Voyez toutes nos [cartes Dracaufeu](cards:dracaufeu), dont Dracaufeu-ex de [151](set:151) et Méga-Dracaufeu.

## Particuliers et boutiques

Tout le monde peut commander, dès un seul article, avec des prix dégressifs affichés sur chaque produit. Boutiques et revendeurs : voyez notre [offre revendeurs](page:revendeurs).
MD,
    /* the Shipping & Returns page ({rates} = the delivery options and rate tables) */
    'shipping_policy' => <<<'MD'
Toutes les commandes {brand} sont expédiées du Japon avec suivi. Cette page explique comment nous expédions, combien cela coûte, les délais et ce qui se passe en cas de problème. Le prix affiché à la commande fait toujours foi.

## Options et tarifs de livraison

{rates}

## Livraison offerte

Les commandes dont le montant des articles dépasse **{free_ship}** sont livrées gratuitement en {standard}. La remise s’applique automatiquement, sans code. Plus pressé ? Choisissez {express} : vous ne payez que la différence entre {express} et {standard}.

## De la commande à votre porte

1. **Vous passez commande** et recevez aussitôt un e-mail avec votre référence.
2. **Vous payez.** Le Bitcoin se paie sur la page de votre commande, dès sa validation. Pour les autres moyens, nous vous envoyons les coordonnées sous {reply_hours} heures.
3. **Votre paiement est reçu** et le stock vous est attribué.
4. **Nous préparons et expédions** votre commande depuis le Japon sous {hold_hours} heures.
5. **Nous vous envoyons le numéro de suivi** dès la création de l’étiquette.
6. **Votre colis est livré.** Vérifiez-le dès que possible (voir ci-dessous).

Les délais courent à partir de l’expédition, pas de la commande.

## Suivre votre colis

Chaque colis est suivi, et vous recevez le numéro de suivi le jour de l’expédition. Un nouveau numéro peut mettre 24 à 48 heures à afficher son premier scan : c’est normal. Le suivi passe en général par : étiquette créée → pris en charge au Japon → export → arrivée dans l’Union européenne → dédouanement → livraison.

Si le suivi n’a pas bougé depuis 5 jours ouvrés, écrivez-nous et nous relançons le transporteur.

## Délais et retards

Les délais sont des estimations en jours ouvrés après expédition, pas des garanties. Ils peuvent s’allonger quand :

- la douane retient un colis pour contrôle
- c’est un jour férié au Japon (Nouvel An, Golden Week début mai, Obon mi-août) ou en France
- les transporteurs sont saturés, par exemple avant Noël
- l’adresse est incomplète ou difficile d’accès

Pour une date importante (anniversaire, Noël, événement), choisissez {express} et prévoyez quelques jours de marge.

## Douane et frais d’importation

Votre commande part du Japon et passe la douane à son arrivée dans l’Union européenne. Selon la valeur du colis, le transporteur peut facturer des frais d’importation à la livraison ; ils restent à la charge du destinataire.

## À la réception

Vérifiez votre colis dès réception. S’il est visiblement abîmé, prenez-le en photo (colis et contenu) avant de le déballer entièrement, et écrivez-nous sous 48 heures. Les produits scellés sont emballés avec protection et calage ; les cartes à l’unité partent sous protège-carte et toploader.

## Retours

### Droit de rétractation

Vous êtes un particulier ? Vous disposez de **14 jours** à compter de la réception pour changer d’avis, sans avoir à vous justifier. Écrivez-nous à {email} avec votre référence de commande, puis renvoyez les articles dans leur état d’origine sous 14 jours. Nous vous remboursons les articles et les frais de livraison initiaux (tarif {standard}) sous 14 jours après réception du retour, par le moyen de paiement utilisé ou par virement. Les frais de retour sont à votre charge.

Les produits scellés doivent nous revenir **scellés** : un display ou un coffret ouvert, ou une carte dont l’état a changé, peut entraîner une réduction du remboursement à hauteur de la dépréciation.

### Article endommagé, erroné ou manquant

Si un article arrive endommagé, ne correspond pas à votre commande ou manque, écrivez-nous sous 48 heures avec des photos. Nous le remplaçons ou vous remboursons, frais de retour compris.

### Colis perdu

Si le suivi ne bouge plus, nous ouvrons une enquête auprès du transporteur. Si le colis est déclaré perdu, nous vous renvoyons la commande ou vous remboursons intégralement.

## Questions fréquentes

### Combien coûte la livraison depuis le Japon ?

Le tarif dépend du poids de la commande et de l’option choisie ({standard} ou {express}). Il s’affiche à la commande avant le paiement, et la livraison {standard} est offerte dès {free_ship} d’achat.

### Quel est le délai de livraison en France ?

Comptez {standard_days} en {standard} et {express_days} en {express}, après expédition. Nous expédions sous {hold_hours} heures après réception du paiement.

### Puis-je retourner une carte Pokémon ?

Oui. Les particuliers disposent de 14 jours après réception pour se rétracter. Les articles doivent revenir dans leur état d’origine, les produits scellés encore scellés.

### Y a-t-il des frais de douane ?

Votre colis vient du Japon : selon sa valeur, le transporteur peut facturer des frais d’importation à la livraison, à la charge du destinataire.
MD,
  ],

  /* h1 and seo_title fall back to the label, seo_desc to the blurb */
  'categories' => [
    'boxes' => [
      'label' => 'Displays', 'slug' => 'displays-pokemon',
      'blurb' => 'Displays Pokémon japonais scellés : des boîtes de boosters d’une même extension, direct du Japon.',
      'h1' => 'Display Pokémon japonais',
      'seo_title' => 'Display Pokémon japonais : boîtes de boosters scellées',
      'seo_desc' => 'Displays Pokémon japonais scellés d’usine : 151, Inferno X (Flammes Fantasmagoriques), Terastal Festival ex, Méga-Évolution… Expédiés du Japon avec suivi.',
      'intro' => <<<'MD'
## Qu’est-ce qu’un display Pokémon ?

Un display Pokémon est une boîte scellée de boosters d’une même extension : c’est la façon la plus économique d’acheter des boosters, et la plus sûre pour tomber sur les cartes rares d’une série. Un display japonais contient en général **30 boosters de 5 cartes** pour une extension principale, et 10 à 20 boosters pour une extension spéciale comme [151](set:151) ou [Terastal Festival ex](set:terastal-festival-ex-evolutions-prismatiques).

Nous proposons les extensions japonaises actuelles de la série [Méga-Évolution](set:mega-evolution) et les incontournables de l’ère [Écarlate et Violet](set:ecarlate-et-violet), ainsi que des cartons scellés pour les achats en volume.

## Displays japonais et versions françaises

Chaque extension japonaise a son équivalent français, qui sort plus tard : le display [Inferno X](product:display-inferno-x-flammes-fantasmagoriques-japonais) correspond à Flammes Fantasmagoriques, [Terastal Festival ex](product:display-terastal-festival-ex-evolutions-prismatiques-japonais) à Évolutions Prismatiques, [Glory of Team Rocket](product:display-glory-of-team-rocket-sv10-japonais) à Rivalités Destinées. La correspondance complète est dans notre guide des [extensions Pokémon](guide:extensions-pokemon).

Display, ETB ou coffret : lequel choisir ? Réponse dans notre guide [display, ETB ou coffret Pokémon](guide:display-etb-coffret-pokemon).
MD,
    ],
    'etb' => [
      'label' => 'Coffrets Dresseur d’Élite', 'slug' => 'etb-pokemon',
      'blurb' => 'Coffrets Dresseur d’Élite (ETB) : boosters, protège-cartes, dés et boîte de rangement.',
      'h1' => 'ETB Pokémon : Coffrets Dresseur d’Élite',
      'seo_title' => 'ETB Pokémon : Coffret Dresseur d’Élite scellé',
      'seo_desc' => 'Coffrets Dresseur d’Élite Pokémon (ETB) scellés : 30th Celebration, Storm Emeralda, Abyss Eye, Perfect Order, Ascended Heroes… Boosters et accessoires inclus.',
      'intro' => <<<'MD'
## Qu’y a-t-il dans un ETB Pokémon ?

Un ETB (Elite Trainer Box), ou **Coffret Dresseur d’Élite** en français, réunit des boosters avec des protège-cartes, des dés, des marqueurs de dégâts, des cartes Énergie et une boîte de rangement. C’est l’un des cadeaux Pokémon les plus populaires, et une excellente façon de découvrir une nouvelle extension.

Nous proposons des ETB japonais ([Storm Emeralda](product:etb-storm-emeralda-japonais), [Abyss Eye](product:etb-abyss-eye-japonais)) et des ETB en version anglaise de la série Méga-Évolution, dont [30th Celebration](product:etb-30th-celebration), [Perfect Order](product:etb-perfect-order) et [Ascended Heroes](product:etb-ascended-heroes). La langue est indiquée dans le nom de chaque produit.

## ETB ou display ?

L’ETB contient moins de boosters qu’un display mais ajoute tout le matériel de jeu et une belle boîte de rangement. Pour ouvrir un maximum de boosters au meilleur prix, choisissez un [display](category:boxes) ; pour offrir ou débuter, l’ETB est idéal. Comparaison complète dans notre guide [display, ETB ou coffret](guide:display-etb-coffret-pokemon).
MD,
    ],
    'premium' => [
      'label' => 'Coffrets & decks', 'slug' => 'coffrets-pokemon',
      'blurb' => 'Coffrets collection, coffrets premium, decks prêts à jouer et produits 30th Celebration.',
      'h1' => 'Coffret Pokémon : coffrets collection et decks',
      'seo_title' => 'Coffret Pokémon : coffrets collection, premium et decks',
      'seo_desc' => 'Coffrets Pokémon avec boosters et cartes promo, coffrets premium 30th Celebration, decks Pokémon prêts à jouer : Évoli-ex, Zoroark-ex, MEGA Start Deck.',
      'intro' => <<<'MD'
## Coffrets de cartes Pokémon

Un coffret Pokémon réunit des boosters et une ou plusieurs cartes promo, souvent avec une figurine, un badge ou des accessoires. C’est le cadeau Pokémon par excellence : coffret [Amphinobi-ex](product:coffret-amphinobi-ex-30th-celebration) ou [Nymphali-ex](product:coffret-nymphali-ex-30th-celebration) pour les 30 ans de Pokémon, ou le [Premium Deck Set Mentali et Noctali](product:premium-deck-set-mentali-noctali-30th-celebration) pour les collectionneurs.

## Decks Pokémon prêts à jouer

Pour apprendre à jouer, rien de plus simple qu’un deck Pokémon prêt à jouer : le [Starter Set ex Évoli-ex](product:deck-starter-set-ex-evoli-ex), le [Starter Set ex Zoroark-ex](product:deck-starter-set-ex-zorua-zoroark-ex) ou la [MEGA Start Deck 100 Battle Collection](product:mega-start-deck-100-battle-collection). Les règles sont expliquées dans notre guide [comment jouer aux cartes Pokémon](guide:jouer-cartes-pokemon).

Vous cherchez un coffret Dracaufeu ? Voyez toutes nos [cartes Dracaufeu](cards:dracaufeu).
MD,
    ],
    'singles' => [
      'label' => 'Cartes rares', 'slug' => 'cartes-pokemon-rares',
      'blurb' => 'Cartes Pokémon rares à l’unité : illustrations spéciales, hyper rares et cartes gradées PSA.',
      'h1' => 'Cartes Pokémon rares à l’unité',
      'seo_title' => 'Carte Pokémon rare : SAR, hyper rares et cartes gradées',
      'seo_desc' => 'Cartes Pokémon rares à l’unité : Dracaufeu-ex, Méga-Rayquaza-ex, Méga-Ectoplasma-ex, Pikachu-ex illustration spéciale rare, hyper rares dorées et cartes gradées PSA 10.',
      'intro' => <<<'MD'
## Cartes Pokémon rares et chères

Nos cartes à l’unité sont le haut de gamme de chaque extension : illustrations spéciales rares (SAR) full art, hyper rares dorées et Master Ultra Rare de la série Méga-Évolution, comme [Méga-Rayquaza-ex MUR](product:mega-rayquaza-ex-mur-japonaise). Vous trouverez aussi nos [cartes Dracaufeu](cards:dracaufeu), nos [cartes Pikachu](cards:pikachu), [Ectoplasma](cards:ectoplasma) et des [cartes gradées PSA](cards:cartes-pokemon-gradees-psa).

Les cartes sont Near Mint et partent sous protège-carte et toploader. Que signifient SAR, SR ou UR ? Réponse dans notre guide de la [rareté des cartes Pokémon](guide:rarete-carte-pokemon). Pour savoir ce que vaut une carte, lisez notre guide du [prix des cartes Pokémon](guide:prix-carte-pokemon).
MD,
    ],
    'accessories' => [
      'label' => 'Classeurs & accessoires', 'slug' => 'classeur-carte-pokemon',
      'blurb' => 'Classeurs pour cartes Pokémon, protège-cartes, deck box, tapis de jeu et boîtes de rangement.',
      'h1' => 'Classeur carte Pokémon, protège-cartes et accessoires',
      'seo_title' => 'Classeur carte Pokémon, protège-cartes et deck box',
      'seo_desc' => 'Classeur pour cartes Pokémon 9 cases, protège-cartes Pikachu et Rayquaza, deck box Ultra PRO, tapis de jeu et boîtes de rangement pour cartes Pokémon.',
      'intro' => <<<'MD'
## Classeur pour cartes Pokémon

Un bon classeur Pokémon et des protège-cartes adaptés gardent une collection en parfait état. Notre [classeur carte Pokémon 9 cases](product:classeur-carte-pokemon-9-cases-mega-evolution) accueille neuf cartes par page aux couleurs de la série Méga-Évolution. Pour les cartes de valeur, glissez-les d’abord dans un protège-carte : elles rentrent sans problème dans les pochettes.

## Protège-cartes, deck box et tapis de jeu

Les cartes Pokémon mesurent 63 × 88 mm, japonaises comme françaises : elles utilisent des protège-cartes au format standard, comme nos [protège-cartes Pokémon](product:protege-cartes-pokemon-64) ou les [Ultra PRO Pikachu](product:protege-cartes-ultra-pro-pikachu-65). Pour transporter un deck, prenez une [deck box](product:deck-box-ultra-pro-pikachu-alcove-tower), et pour jouer, un [tapis de jeu Pokémon](product:tapis-de-jeu-pokemon).
MD,
    ],
  ],

  'series' => [
    'mega' => [
      'name' => 'Méga-Évolution', 'slug' => 'mega-evolution',
      'h1' => 'Série Méga-Évolution : displays et cartes Pokémon japonaises',
      'seo_title' => 'Méga-Évolution Pokémon : displays et cartes japonaises',
      'seo_desc' => 'Toutes les extensions japonaises de la série Méga-Évolution : Mega Brave, Mega Symphonia, Inferno X (Flammes Fantasmagoriques), Mega Dream ex, Nihil Zero, Ninja Spinner, Abyss Eye, Storm Emeralda…',
      'intro' => <<<'MD'
La Méga-Évolution est revenue dans le JCC Pokémon en 2025, lancée au Japon par les extensions jumelles [Mega Brave](set:mega-brave) (avec Méga-Lucario-ex) et [Mega Symphonia](set:mega-symphonia) (avec Méga-Gardevoir-ex), devenues en français l’extension Méga-Évolution. Chaque extension depuis apporte de nouveaux Méga-Pokémon-ex : Méga-Dracaufeu X-ex dans [Inferno X](set:inferno-x-flammes-fantasmagoriques) (Flammes Fantasmagoriques en français), Méga-Ectoplasma-ex dans [Mega Dream ex](set:mega-dream-ex), Méga-Rayquaza-ex dans [Storm Emeralda](set:storm-emeralda).

Un Méga-Pokémon-ex rapporte trois cartes Récompense à l’adversaire quand il est mis K.O. : puissant, mais risqué. Voici toutes les extensions Méga-Évolution en stock, avec leurs displays, coffrets et cartes phares. Correspondance des noms japonais, anglais et français dans notre guide des [extensions Pokémon](guide:extensions-pokemon).
MD,
    ],
    'sv' => [
      'name' => 'Écarlate et Violet', 'slug' => 'ecarlate-et-violet',
      'h1' => 'Série Écarlate et Violet : displays Pokémon japonais',
      'seo_title' => 'Écarlate et Violet : displays Pokémon japonais (151, SV10…)',
      'seo_desc' => 'Displays japonais de la série Écarlate et Violet : 151, Terastal Festival ex (Évolutions Prismatiques), Heat Wave Arena et Glory of Team Rocket (Rivalités Destinées).',
      'intro' => <<<'MD'
L’ère Écarlate et Violet du JCC Pokémon a duré de 2023 jusqu’au lancement de la série Méga-Évolution en 2025, et a donné quelques-unes des extensions japonaises les plus collectionnées : [151](set:151), qui revisite les 151 premiers Pokémon, et [Terastal Festival ex](set:terastal-festival-ex-evolutions-prismatiques), l’Évolutions Prismatiques japonaise.

Nous avons encore des displays japonais scellés de [Heat Wave Arena](set:heat-wave-arena) et [Glory of Team Rocket](set:glory-of-team-rocket-rivalites-destinees), les deux extensions réunies en français dans Rivalités Destinées. Ces extensions n’étant plus imprimées, les displays scellés se font rares.
MD,
    ],
  ],

  /* keyed by the set name used on products; series is a key of 'series' above */
  'sets' => [
    'Aura Seeker (Hadou Seeker)' => ['slug'=>'aura-seeker', 'series'=>'mega', 'code'=>'',
      'intro'=>'Aura Seeker (Hadou Seeker) est une prochaine extension japonaise de la série Méga-Évolution. Précommandez votre display dès maintenant : la précommande est facturée à l’attribution du stock, pas à la commande.'],
    'Storm Emeralda' => ['slug'=>'storm-emeralda', 'series'=>'mega', 'code'=>'M6',
      'seo_title'=>'Storm Emeralda (M6) : display japonais et Méga-Rayquaza-ex',
      'intro'=>'Storm Emeralda (M6) est l’extension japonaise Méga-Évolution emmenée par Méga-Rayquaza-ex. Nous proposons des displays scellés, des cartons de 12 displays et des Coffrets Dresseur d’Élite, la Master Ultra Rare et l’illustration spéciale rare de Méga-Rayquaza-ex, ainsi que des protège-cartes Méga-Rayquaza.'],
    '30th Celebration' => ['slug'=>'30th-celebration', 'series'=>'mega', 'code'=>'M6a',
      'seo_title'=>'Pokémon 30th Celebration : display, ETB et coffrets',
      'intro'=>"30th Celebration fête les 30 ans de la franchise Pokémon en 2026. Mewtwo-ex et Mew-ex mènent l’extension, avec Noctali-ex, Drattak-ex et Amphinobi-ex, et chaque booster contient un Pikachu, parmi 30 cartes Pikachu rares à collectionner.\n\nLa gamme comprend des displays, des [Coffrets Dresseur d’Élite](product:etb-30th-celebration) et des cartons, les coffrets Amphinobi-ex et Nymphali-ex, le Premium Deck Set Mentali et Noctali et une collection de stickers."],
    'Abyss Eye' => ['slug'=>'abyss-eye', 'series'=>'mega', 'code'=>'M5',
      'intro'=>'Abyss Eye (M5) est l’extension japonaise Méga-Évolution de Méga-Darkrai-ex et Méga-Minotaupe-ex. Nous proposons des displays et des Coffrets Dresseur d’Élite Abyss Eye scellés, l’ETB anglais Pitch Black, et les cartes Méga-Darkrai-ex et Méga-Minotaupe-ex.'],
    'Ninja Spinner' => ['slug'=>'ninja-spinner', 'series'=>'mega', 'code'=>'M4',
      'intro'=>'Ninja Spinner (M4) apporte Méga-Amphinobi-ex et Méga-Floette-ex à la série Méga-Évolution. En stock : des displays japonais scellés (stock limité), l’ETB anglais Chaos Rising, et les cartes Méga-Amphinobi-ex et Méga-Floette-ex.'],
    'Nihil Zero' => ['slug'=>'nihil-zero', 'series'=>'mega', 'code'=>'M3',
      'intro'=>'Nihil Zero (M3) fait partie de la série japonaise Méga-Évolution. Nous proposons des displays Nihil Zero scellés et l’ETB anglais Perfect Order, avec Méga-Zygarde-ex.'],
    'Mega Dream ex' => ['slug'=>'mega-dream-ex', 'series'=>'mega', 'code'=>'M2a',
      'seo_title'=>'Mega Dream ex (M2a) : display japonais et Méga-Ectoplasma-ex',
      'intro'=>'Mega Dream ex (M2a) est l’extension spéciale de la série Méga-Évolution et abrite Méga-Ectoplasma-ex en illustration spéciale rare. En stock : des displays Mega Dream ex scellés, l’ETB anglais Ascended Heroes et la carte Méga-Ectoplasma-ex SAR.'],
    'Inferno X' => ['slug'=>'inferno-x-flammes-fantasmagoriques', 'series'=>'mega', 'code'=>'M2',
      'h1'=>'Inferno X (Flammes Fantasmagoriques) : display japonais et Méga-Dracaufeu X-ex',
      'seo_title'=>'Flammes Fantasmagoriques en japonais : display Inferno X (M2)',
      'seo_desc'=>'Inferno X (M2), la version japonaise de Flammes Fantasmagoriques : displays scellés avec Méga-Dracaufeu X-ex, expédiés du Japon. ETB et liste des cartes phares.',
      'intro'=>"Inferno X (M2) est la deuxième grande extension japonaise de la série Méga-Évolution, sortie le 26 septembre 2025 et emmenée par **Méga-Dracaufeu X-ex** (Méga-Lizardon X-ex en japonais). Elle est sortie en français sous le nom de **Flammes Fantasmagoriques**.\n\nLe display japonais Inferno X contient 30 boosters de 5 cartes : les mêmes cartes que Flammes Fantasmagoriques, des semaines plus tôt. L’ETB Flammes Fantasmagoriques et les cartes phares sont présentés dans notre guide des [extensions Pokémon](guide:extensions-pokemon) ; pour Dracaufeu, voyez toutes nos [cartes Dracaufeu](cards:dracaufeu)."],
    'Mega Symphonia' => ['slug'=>'mega-symphonia', 'series'=>'mega', 'code'=>'M1S',
      'seo_title'=>'Mega Symphonia (M1S) : display japonais Méga-Gardevoir-ex',
      'intro'=>'Mega Symphonia (M1S) est l’une des deux extensions qui ont lancé la série Méga-Évolution au Japon le 1er août 2025, avec [Mega Brave](set:mega-brave), emmenée par Méga-Gardevoir-ex. Ensemble, elles forment l’extension française Méga-Évolution : voir notre guide des [extensions Pokémon](guide:extensions-pokemon).'],
    'Mega Brave' => ['slug'=>'mega-brave', 'series'=>'mega', 'code'=>'M1L',
      'h1'=>'Mega Brave (M1L) : display japonais Méga-Lucario-ex',
      'seo_title'=>'Méga-Lucario-ex : display japonais Mega Brave (M1L)',
      'intro'=>'Mega Brave (M1L) a lancé la série japonaise Méga-Évolution le 1er août 2025 avec son extension jumelle [Mega Symphonia](set:mega-symphonia). Sa carte phare est **Méga-Lucario-ex**. Ensemble, les deux extensions forment l’extension française Méga-Évolution : voir notre guide des [extensions Pokémon](guide:extensions-pokemon).'],
    'Glory of Team Rocket' => ['slug'=>'glory-of-team-rocket-rivalites-destinees', 'series'=>'sv', 'code'=>'SV10',
      'h1'=>'Glory of Team Rocket (Rivalités Destinées) : display japonais',
      'seo_title'=>'Rivalités Destinées en japonais : display Glory of Team Rocket',
      'seo_desc'=>'Glory of Team Rocket (SV10), l’extension japonaise de Rivalités Destinées : displays scellés avec Mewtwo-ex de la Team Rocket, expédiés du Japon.',
      'intro'=>"Glory of Team Rocket (SV10) ramène les Pokémon de la Team Rocket dans le JCC et compte parmi les extensions japonaises Écarlate et Violet les plus demandées, avec Mewtwo-ex de la Team Rocket comme carte phare. Sortie au Japon le 18 avril 2025, elle a formé avec [Heat Wave Arena](set:heat-wave-arena) l’extension française **Rivalités Destinées**.\n\nEn stock en displays japonais scellés de 30 boosters. Voir aussi notre guide des [extensions Pokémon](guide:extensions-pokemon)."],
    'Heat Wave Arena' => ['slug'=>'heat-wave-arena', 'series'=>'sv', 'code'=>'SV9a',
      'intro'=>'Heat Wave Arena (SV9a) est une extension japonaise Écarlate et Violet, sortie le 14 mars 2025 et disponible en displays scellés. Ses cartes ont rejoint l’extension française **Rivalités Destinées**, avec [Glory of Team Rocket](set:glory-of-team-rocket-rivalites-destinees).'],
    'Terastal Festival ex' => ['slug'=>'terastal-festival-ex-evolutions-prismatiques', 'series'=>'sv', 'code'=>'SV8a',
      'h1'=>'Terastal Festival ex (Évolutions Prismatiques) : display japonais',
      'seo_title'=>'Évolutions Prismatiques en japonais : display Terastal Festival ex',
      'seo_desc'=>'Terastal Festival ex (SV8a), l’Évolutions Prismatiques japonaise : displays de 10 boosters avec Noctali-ex et toutes les évolutions d’Évoli, expédiés du Japon.',
      'intro'=>"Terastal Festival ex (SV8a) est l’extension spéciale Écarlate et Violet consacrée aux Pokémon Téracristal et aux évolutions d’Évoli, sortie au Japon le 6 décembre 2024 et en français sous le nom d’**Évolutions Prismatiques** en janvier 2025. Noctali-ex en illustration spéciale rare est la carte phare.\n\nLa version française n’existe pas en display (seulement en coffrets), mais la version japonaise oui : chaque display contient 10 boosters de 10 cartes. Plus d’infos dans notre guide des [extensions Pokémon](guide:extensions-pokemon)."],
    '151' => ['slug'=>'151', 'series'=>'sv', 'code'=>'SV2a',
      'h1'=>'Pokémon 151 : display japonais et Dracaufeu-ex',
      'seo_title'=>'Pokémon 151 : display japonais et carte Dracaufeu-ex',
      'seo_desc'=>'Cartes Pokémon 151 japonaises (SV2a) : displays 151 scellés et la carte Dracaufeu-ex illustration spéciale rare, expédiés du Japon.',
      'intro'=>"Pokémon Card 151 (SV2a) est l’extension spéciale japonaise qui revisite les 151 Pokémon d’origine de Pokémon Rouge et Vert, de Bulbizarre à Mew. C’est l’une des extensions les plus collectionnées de l’ère Écarlate et Violet, sortie ensuite en français sous le nom d’Écarlate et Violet – 151. La version française n’existait pas en display (coffrets et bundles uniquement), mais le display japonais 151 contient 20 boosters de 7 cartes, et la carte phare est Dracaufeu-ex en illustration spéciale rare.\n\n## Liste des cartes Pokémon 151\n\nLa version japonaise de 151 (sortie le 16 juin 2023) compte 210 cartes : un set principal de 165 cartes avec les 151 Pokémon d’origine, des cartes Dresseur et Énergie, et 45 cartes secrètes : 18 Art Rares (AR), 16 Super Rares (SR), 8 Special Art Rares (SAR) et 3 Ultra Rares dorées (UR). Plus d’infos dans notre guide de la [rareté des cartes Pokémon](guide:rarete-carte-pokemon).\n\nNous proposons des displays 151 scellés et la carte Dracaufeu-ex SAR de 151. Voir toutes nos [cartes Dracaufeu](cards:dracaufeu)."],
  ],

  /* products are included if their id is listed, or their name contains a match term
     (comma-separated); cond limits to one condition, or lists every product in it when there are no terms */
  'collections' => [
    ['slug'=>'dracaufeu', 'title'=>'Cartes Dracaufeu', 'h1'=>'Dracaufeu : cartes Pokémon Dracaufeu-ex, Méga-Dracaufeu et shiny',
     'match'=>'dracaufeu', 'ids'=>['display-inferno-x-flammes-fantasmagoriques-japonais', 'display-pokemon-151-japonais'], 'cond'=>'',
     'seo_title'=>'Dracaufeu : carte Pokémon Dracaufeu-ex, Méga-Dracaufeu, shiny',
     'seo_desc'=>'Cartes Dracaufeu japonaises : Dracaufeu-ex SAR de 151, Méga-Dracaufeu Y-ex hyper rare, displays Inferno X avec Méga-Dracaufeu X-ex. Dracaufeu shiny et coffrets expliqués.',
     'intro'=><<<'MD'
Dracaufeu (Charizard en anglais, Lizardon en japonais) est le Pokémon le plus collectionné du Jeu de Cartes à Collectionner, et une carte Dracaufeu est souvent la plus chère de son extension. Nos cartes Dracaufeu japonaises comprennent [Dracaufeu-ex illustration spéciale rare](product:carte-dracaufeu-ex-sar-151-japonaise) de [151](set:151) et [Méga-Dracaufeu Y-ex hyper rare](product:mega-dracaufeu-y-ex-hyper-rare), et nos displays [Inferno X](product:display-inferno-x-flammes-fantasmagoriques-japonais) sont ceux où l’on trouve Méga-Dracaufeu X-ex.

Chaque carte est Near Mint et part sous protège-carte et toploader.

## Dracaufeu-ex

**Dracaufeu-ex** est le Dracaufeu de l’ère Écarlate et Violet, imprimé dans plusieurs extensions. Les plus connus sont Dracaufeu-ex illustration spéciale rare de 151 et le Dracaufeu-ex shiny de Destinées de Paldea. Comme tous les Pokémon-ex, il rapporte deux cartes Récompense à l’adversaire quand il est mis K.O.

## Méga-Dracaufeu X-ex et Méga-Dracaufeu Y-ex

La Méga-Évolution est revenue en 2025, et Dracaufeu a ses deux formes Méga en **Méga-Pokémon-ex** :

- **Méga-Dracaufeu X-ex**, le Méga-Dracaufeu noir aux flammes bleues (Méga-Lizardon X-ex en japonais), est la tête d’affiche de l’extension japonaise [Inferno X](set:inferno-x-flammes-fantasmagoriques), sortie en français sous le nom de Flammes Fantasmagoriques.
- **Méga-Dracaufeu Y-ex** a une hyper rare dorée, que nous proposons en état Near Mint.

Un Méga-Pokémon-ex rapporte trois cartes Récompense quand il est mis K.O. : un coup fort en partie, et une carte très recherchée.

## Dracaufeu shiny

Un Dracaufeu shiny est noir au lieu d’orange. Les cartes Dracaufeu shiny les plus connues sont Dracaufeu Brillant de Neo Destiny (2002), Dracaufeu-GX shiny de Destinées Occultes, et Dracaufeu-ex shiny illustration spéciale rare de Destinées de Paldea (Shiny Treasure ex en japonais).

## Coffret Dracaufeu

Les coffrets Dracaufeu, comme la Collection Ultra-Premium Dracaufeu, sont des produits anglais et français très recherchés, dont le prix grimpe une fois épuisés. Nous proposons des cartes Dracaufeu japonaises à l’unité et les displays scellés où les trouver. Combien vaut votre Dracaufeu ? Voyez notre guide du [prix des cartes Pokémon](guide:prix-carte-pokemon).
MD],
    ['slug'=>'pikachu', 'title'=>'Cartes Pikachu', 'h1'=>'Carte Pokémon Pikachu',
     'match'=>'pikachu', 'ids'=>[], 'cond'=>'',
     'seo_title'=>'Carte Pokémon Pikachu : SAR, PSA 10 et protège-cartes',
     'seo_desc'=>'Cartes Pokémon Pikachu : Pikachu-ex illustration spéciale rare, un exemplaire gradé PSA 10 Gem Mint, protège-cartes et deck box Pikachu.',
     'intro'=><<<'MD'
Pikachu est le visage de Pokémon, et ses cartes plaisent à tous les collectionneurs. Nous proposons Pikachu-ex en illustration spéciale rare, dont un exemplaire [gradé PSA 10 Gem Mint](cards:cartes-pokemon-gradees-psa), ainsi que des protège-cartes et une deck box Ultra PRO Pikachu.

Les cartes à l’unité partent sous protège-carte et toploader ; les cartes gradées dans leur boîtier PSA. Et chaque booster de [30th Celebration](set:30th-celebration) contient un Pikachu !
MD],
    ['slug'=>'ectoplasma', 'title'=>'Cartes Ectoplasma', 'h1'=>'Cartes Pokémon Ectoplasma',
     'match'=>'ectoplasma', 'ids'=>['display-mega-dream-ex-m2a-japonais'], 'cond'=>'',
     'seo_title'=>'Carte Ectoplasma : Méga-Ectoplasma-ex SAR japonaise',
     'seo_desc'=>'La carte japonaise Méga-Ectoplasma-ex illustration spéciale rare de Mega Dream ex, et les displays Mega Dream ex pour la trouver vous-même.',
     'intro'=><<<'MD'
Ectoplasma (Gengar en anglais) a l’une des communautés de fans les plus fidèles du JCC Pokémon, et Méga-Ectoplasma-ex en illustration spéciale rare de [Mega Dream ex](set:mega-dream-ex) est l’une des cartes marquantes de la série Méga-Évolution. Achetez la carte en état Near Mint, ou ouvrez des displays Mega Dream ex pour la chercher vous-même.
MD],
    ['slug'=>'cartes-pokemon-gradees-psa', 'title'=>'Cartes gradées PSA', 'h1'=>'Cartes Pokémon gradées PSA',
     'match'=>'', 'ids'=>[], 'cond'=>'Graded',
     'seo_title'=>'Cartes Pokémon gradées PSA 10 Gem Mint',
     'seo_desc'=>'Cartes Pokémon gradées PSA, dont Pikachu-ex illustration spéciale rare en PSA 10 Gem Mint, expédiées dans leur boîtier PSA d’origine.',
     'intro'=><<<'MD'
Une carte gradée a été évaluée puis scellée dans un boîtier inviolable par une société de gradation. PSA note de 1 à 10 : PSA 10 (Gem Mint) est une carte quasi parfaite, PSA 9 est Mint et PSA 8 Near Mint–Mint. Comme la note supprime tout doute sur l’état, les cartes gradées, surtout en PSA 10, se vendent nettement plus cher que les cartes brutes.

Nos cartes gradées sont expédiées dans leur boîtier PSA d’origine. Pour les cartes non gradées, voyez nos [cartes rares](category:singles), et l’effet de la note sur le prix dans notre guide du [prix des cartes Pokémon](guide:prix-carte-pokemon).
MD],
  ],

  'guides' => require __DIR__.'/guides.php',

  /* info pages, linked in the footer (slugs are their web addresses) */
  'pages' => [
    ['slug'=>'a-propos', 'title'=>'À propos de {brand}',
     'seo_title'=>'À propos de {brand} : cartes Pokémon japonaises',
     'seo_desc'=>'{brand} est un revendeur indépendant de cartes Pokémon japonaises authentiques, expédiées du Japon avec suivi vers la France, la Belgique et le Luxembourg.',
     'body'=><<<'MD'
{brand} (札蔵) est un revendeur indépendant de produits authentiques du Jeu de Cartes à Collectionner Pokémon japonais. Notre nom vient du japonais : **fuda** (札), la carte, et **kura** (蔵), l’entrepôt ou la réserve. Une réserve de cartes, directement au Japon.

## Ce que nous faisons

Nous achetons auprès de la distribution japonaise et expédions directement du Japon, avec suivi, vers la France, la Belgique, le Luxembourg et Monaco : des [displays](category:boxes) scellés, des [Coffrets Dresseur d’Élite](category:etb), des [coffrets et decks](category:premium), des [cartes rares](category:singles) et des [accessoires](category:accessories).

## Nos engagements

- **Authenticité.** Aucun produit reconditionné, réimprimé ou contrefait. Les produits scellés sont expédiés tels qu’ils sont sortis d’usine.
- **Prix clairs.** Le prix affiché, prix dégressifs compris, est le prix facturé.
- **Expédition soignée.** Calage et protection pour les produits scellés, protège-carte et toploader pour les cartes à l’unité.
- **Réponse rapide.** Nous répondons sous {reply_hours} heures, par e-mail à {email} ou par le chat du site.

{brand} n’est ni affilié à The Pokémon Company, Nintendo, Creatures Inc. ou GAME FREAK Inc., ni approuvé par eux.
MD],
    ['slug'=>'revendeurs', 'title'=>'Offre revendeurs : cartes Pokémon japonaises en gros',
     'seo_title'=>'Cartes Pokémon japonaises en gros pour boutiques et revendeurs',
     'seo_desc'=>'Boutiques, revendeurs et organisateurs de tournois : displays et cartons Pokémon japonais scellés, prix dégressifs affichés, expédition du Japon avec suivi.',
     'body'=><<<'MD'
Vous tenez une boutique de jeux, une boutique en ligne ou organisez des tournois ? {brand} fournit des produits Pokémon japonais scellés aux professionnels comme aux particuliers, sans compte à ouvrir ni validation à attendre.

## Prix dégressifs affichés

Chaque produit affiche ses paliers de prix : le prix unitaire baisse automatiquement quand la quantité augmente (par exemple dès 6, 24 ou 36 displays). Pour les volumes plus importants, nous proposons des **cartons scellés**, comme le [carton de 12 displays Storm Emeralda](product:carton-12-displays-storm-emeralda) ou le [carton de 10 ETB 30th Celebration](product:carton-10-etb-30th-celebration).

## Précommandes et allocations

Les prochaines sorties japonaises, comme [Aura Seeker](set:aura-seeker), sont ouvertes en précommande : vous réservez votre allocation au prix affiché et êtes facturé à l’attribution du stock.

## Commandes régulières

Pour des commandes régulières ou une allocation sur une prochaine sortie, écrivez-nous à {email} avec les extensions et quantités souhaitées : nous revenons vers vous avec un prix sous {reply_hours} heures. Indiquez votre numéro de TVA intracommunautaire ou votre SIRET dans les remarques de commande pour qu’il figure sur la facture.

## Questions fréquentes

### Faut-il un compte professionnel pour voir les prix ?

Non. Tous les prix et paliers sont affichés publiquement, sans inscription.

### Livrez-vous les boutiques en Belgique et au Luxembourg ?

Oui, nous livrons la France, la Belgique, le Luxembourg et Monaco depuis le Japon, avec suivi.
MD],
    ['slug'=>'cgv', 'title'=>'Conditions générales de vente',
     'seo_title'=>'Conditions générales de vente — {brand}',
     'seo_desc'=>'Conditions générales de vente de {brand} : commande, prix, paiement, livraison depuis le Japon, droit de rétractation de 14 jours, garanties et authenticité.',
     'body'=><<<'MD'
Les présentes conditions générales de vente s’appliquent à toute commande passée sur le site de {brand}, exploité par {company}, {address} (contact : {email}). En validant une commande, vous les acceptez.

## Produits

Nous vendons des produits authentiques du Jeu de Cartes à Collectionner Pokémon, principalement en version japonaise ; la langue est indiquée dans le nom de chaque produit. Les photos sont non contractuelles. L’état des cartes à l’unité (Near Mint, gradée…) est indiqué sur chaque fiche.

## Commande

La commande est enregistrée lorsque vous la validez ; vous recevez aussitôt un e-mail récapitulatif avec votre référence. Le stock est réservé pendant {hold_hours} heures dans l’attente du paiement. La commande est confirmée à réception du paiement. Une précommande est facturée à l’attribution du stock.

## Prix

Les prix sont indiqués en euros. Les frais de livraison sont calculés selon le poids et la destination et affichés avant la validation de la commande ; la livraison {standard} est offerte au-delà de {free_ship} d’achat. Les prix dégressifs s’appliquent automatiquement selon la quantité.

## Paiement

Le paiement s’effectue en Bitcoin sur la page de votre commande, en autres cryptomonnaies (ETH, USDT) ou par virement bancaire SEPA ; pour ces derniers, nous vous envoyons les coordonnées sous {reply_hours} heures. Nous ne demandons jamais de numéro de carte, de mot de passe ni de clés de portefeuille.

## Livraison

Les commandes sont expédiées du Japon avec suivi, sous {hold_hours} heures après réception du paiement, vers la France, la Belgique, le Luxembourg et Monaco. Les délais indicatifs sont de {standard_days} en {standard} et de {express_days} en {express}. Le colis passant la douane de l’Union européenne, d’éventuels frais d’importation peuvent être facturés par le transporteur au destinataire. Voir [Livraison et retours](page:shipping).

## Droit de rétractation

Conformément aux articles L221-18 et suivants du Code de la consommation, le client consommateur dispose d’un délai de **14 jours** à compter de la réception pour exercer son droit de rétractation, sans avoir à se justifier. Il suffit de nous en informer par e-mail à {email}, puis de renvoyer les articles sous 14 jours, dans leur état d’origine. Nous remboursons les articles et les frais de livraison initiaux (tarif {standard}) sous 14 jours après réception du retour. Les frais de retour sont à la charge du client. Toute dépréciation résultant de manipulations autres que celles nécessaires pour constater la nature du bien (produit scellé ouvert, carte abîmée) peut être déduite du remboursement.

## Garanties

Les produits bénéficient de la garantie légale de conformité (articles L217-3 et suivants du Code de la consommation) et de la garantie des vices cachés (articles 1641 et suivants du Code civil). En cas d’article endommagé, erroné ou manquant, contactez-nous sous 48 heures avec des photos : nous le remplaçons ou le remboursons.

## Authenticité

Tous nos produits sont authentiques, achetés auprès de la distribution japonaise, et expédiés scellés tels qu’ils sont sortis d’usine.

## Données personnelles

Vos données sont utilisées uniquement pour traiter votre commande : voir notre [politique de confidentialité](page:confidentialite).

## Litiges et médiation

En cas de litige, contactez-nous d’abord à {email} pour trouver une solution amiable. Le client consommateur peut également recourir gratuitement à un médiateur de la consommation, ou à la plateforme européenne de règlement en ligne des litiges (https://ec.europa.eu/consumers/odr). Les présentes conditions sont soumises au droit français.
MD],
    ['slug'=>'mentions-legales', 'title'=>'Mentions légales',
     'seo_title'=>'Mentions légales — {brand}',
     'seo_desc'=>'Mentions légales du site {brand} : éditeur, contact, hébergement et propriété intellectuelle.',
     'body'=><<<'MD'
## Éditeur du site

Le site {brand} est édité par {company}, {address}.

- E-mail : {email}
- Directeur de la publication : {company}

## Hébergement

Le site est hébergé par l’hébergeur indiqué par l’éditeur ; ses coordonnées sont disponibles sur simple demande à {email}.

## Propriété intellectuelle

Pokémon, les noms des Pokémon et des produits du Jeu de Cartes à Collectionner Pokémon sont des marques de The Pokémon Company, Nintendo, Creatures Inc. et GAME FREAK Inc. {brand} est un revendeur indépendant, sans lien avec ces sociétés. Les textes et la présentation du site appartiennent à {company}.

## Données personnelles

Voir notre [politique de confidentialité](page:confidentialite).
MD],
    ['slug'=>'confidentialite', 'title'=>'Politique de confidentialité',
     'seo_title'=>'Politique de confidentialité — {brand}',
     'seo_desc'=>'Comment {brand} utilise et protège vos données personnelles : commandes, e-mails, chat en direct et vos droits (RGPD).',
     'body'=><<<'MD'
{company} ({address}) est responsable du traitement des données personnelles collectées sur ce site. Nous collectons le minimum nécessaire et ne vendons jamais vos données.

## Données collectées

- **Commande :** nom, société, adresse e-mail, téléphone, adresse de livraison, articles commandés, moyen de paiement choisi et, pour le Bitcoin, l’identifiant de la transaction.
- **Chat en direct :** les messages que vous nous envoyez par le chat du site (Tawk.to).
- **Technique :** l’adresse IP de la commande, pour la sécurité et la lutte contre la fraude.

Nous ne collectons aucune donnée bancaire ni aucune clé de portefeuille.

## Utilisation

Vos données servent uniquement à traiter et expédier votre commande, vous envoyer les coordonnées de paiement et le suivi, répondre à vos questions et respecter nos obligations comptables. Elles sont transmises au transporteur pour la livraison.

## Durée de conservation

Les données de commande sont conservées le temps nécessaire à la relation commerciale et aux obligations légales (10 ans pour les pièces comptables).

## Vos droits

Conformément au RGPD, vous pouvez accéder à vos données, les rectifier, les effacer, en limiter le traitement ou vous y opposer, en écrivant à {email}. Vous pouvez aussi introduire une réclamation auprès de la CNIL (www.cnil.fr).

## Cookies

Le site utilise un cookie de session pour votre panier. Le chat en direct peut déposer ses propres cookies lorsqu’il se charge.
MD],
  ],

  'faqs' => [
    ['Les cartes Pokémon sont-elles authentiques ?', 'Oui. Tous nos produits sont achetés auprès de la distribution japonaise et expédiés scellés, tels qu’ils sont sortis d’usine. Aucun produit reconditionné, réimprimé ou contrefait.'],
    ['Livrez-vous en France ?', 'Oui. Tout est expédié du Japon avec suivi vers la France, la Belgique, le Luxembourg et Monaco. Choisissez la livraison {standard} ({standard_days}) ou {express} ({express_days}) ; le tarif dépend du poids de la commande, et la livraison {standard} est offerte dès {free_ship} d’achat.'],
    ['Les cartes sont-elles en français ?', 'La plupart de nos produits sont en version japonaise, qui sort avant la version française ; certains coffrets sont en anglais. La langue est toujours indiquée dans le nom du produit.'],
    ['Peut-on jouer avec des cartes japonaises ?', 'Oui, entre amis. En tournoi officiel, seules les cartes des versions internationales (français, anglais…) sont autorisées ; les cartes japonaises sont surtout collectionnées.'],
    ['Quels moyens de paiement acceptez-vous ?', 'Le Bitcoin, payé directement sur la page de votre commande, les autres cryptomonnaies (ETH, USDT) et le virement bancaire SEPA en euros. Pour ces derniers, nous vous envoyons les coordonnées sous {reply_hours} heures.'],
    ['Puis-je commander un seul article ?', 'Oui, tout le monde peut commander dès un article. Les prix baissent automatiquement avec la quantité, comme indiqué sur chaque produit.'],
    ['Combien de temps mon stock est-il réservé ?', 'Votre stock est réservé {hold_hours} heures après la commande, le temps du paiement. Nous expédions sous {hold_hours} heures après réception du paiement.'],
    ['Comment fonctionnent les précommandes ?', 'La précommande réserve votre produit au prix affiché. Vous êtes facturé à l’attribution du stock, pas à la commande, et le produit part dès sa sortie et votre paiement reçu.'],
    ['Puis-je retourner un produit ?', 'Oui. Les particuliers disposent de 14 jours après réception pour se rétracter. Les articles doivent revenir dans leur état d’origine, les produits scellés encore scellés. Voir notre page Livraison et retours.'],
    ['Y a-t-il des frais de douane ?', 'Votre colis vient du Japon : selon sa valeur, le transporteur peut facturer des frais d’importation à la livraison, à la charge du destinataire.'],
    ['Vendez-vous aux boutiques ?', 'Oui : boutiques et revendeurs bénéficient des mêmes prix dégressifs, avec des cartons scellés pour les volumes. Voir notre offre revendeurs, ou écrivez-nous à {email}.'],
  ],
];
