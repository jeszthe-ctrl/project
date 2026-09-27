<?php
/* French text for a fresh install (Wallonia and Brussels). Copied into data/store.php on first load and
   edited in admin.php after (choose "Français" at the top of the admin).
   Formatting and link syntax: see content-nl.php. */
if(!defined('FK_ROOT')){ http_response_code(404); exit; }

return [
  'settings' => [
    'tagline'         => 'Cartes Pokémon japonaises, du Japon à la Belgique',
    'strip_text'      => 'Cartes Pokémon japonaises authentiques · Scellées, envoyées du Japon · Sans TVA ajoutée',
    'strip_link_text' => 'Précommandes Aura Seeker ouvertes →',
    'strip_link_url'  => 'product:aura-seeker-booster-box',
    'hero_title'      => 'Cartes Pokémon japonaises, livrées du Japon en Belgique.',
    'hero_lede'       => 'Displays scellés, coffrets Dresseur d’Élite, coffrets Pokémon et cartes rares à l’unité, achetés au Japon et livrés avec suivi jusqu’à votre porte. Chaque prix est affiché, remises par quantité comprises, et nous n’ajoutons pas de TVA.',
    'footer_blurb'    => 'Revendeur indépendant de cartes Pokémon japonaises authentiques. Displays scellés, coffrets et cartes rares, envoyés du Japon avec suivi partout en Belgique.',
    'home_seo_title'  => 'Cartes Pokémon japonaises : displays, coffrets et cartes rares',
    'home_seo_desc'   => 'Acheter des cartes Pokémon japonaises en Belgique : displays scellés, coffrets Dresseur d’Élite, coffrets et cartes rares, envoyés du Japon. Prix en euros, sans TVA.',
    'home_intro'      => <<<'MD'
## Cartes Pokémon japonaises, livrées en Belgique

{brand} vend des cartes Pokémon japonaises aux collectionneurs, joueurs et boutiques de toute la Belgique. Vous trouverez ici des [displays Pokémon japonais](category:boxes) scellés, des [coffrets Dresseur d’Élite](category:etb), des [coffrets Pokémon et decks](category:premium), des [cartes Pokémon rares à l’unité](category:singles) et des [albums et protège-cartes](category:accessories). Tout est acheté au Japon et envoyé avec suivi, de Bruxelles à Liège, Namur ou Charleroi.

## Pourquoi des cartes Pokémon japonaises ?

Les nouvelles séries Pokémon sortent d’abord au Japon, souvent plusieurs mois avant les versions anglaise et française. Pour avoir les cartes les plus récentes en premier, on achète japonais. Les cartes japonaises sont aussi réputées pour la qualité de leur impression, et les displays japonais sont plus petits et moins chers par boîte : on ouvre plus de séries pour le même budget. Tout est expliqué dans notre guide des [cartes Pokémon japonaises](guide:japanese).

## Prix et valeur d’une carte Pokémon

Le prix d’une carte Pokémon dépend de sa rareté, de son état et de la popularité du Pokémon. Notre [vérificateur de prix des cartes Pokémon](guide:prices) affiche le prix de chaque display, carte et accessoire, et comment il baisse quand vous en prenez plus. Pour estimer une carte que vous possédez, lisez [valeur d’une carte Pokémon : cote et estimation](guide:value).

## Le jeu de cartes à collectionner Pokémon (JCC / TCG)

Les cartes Pokémon sont aussi un jeu : le Jeu de Cartes à Collectionner Pokémon, ou TCG en anglais. Un [deck de démarrage](category:premium) est la façon la plus simple de commencer. Les règles sont expliquées dans [TCG : c’est quoi et comment jouer ?](guide:tcg)

## Pour les boutiques et les grosses commandes

Vous tenez une boutique de cartes, organisez des tournois ou vendez en ligne ? Chaque fiche produit indique la quantité minimum et le prix par quantité, sans compte ni demande. Les commandes commencent à {min_order} livraison comprise. Voir nos [conditions pour grossistes](page:wholesale).
MD,
    'shipping_policy' => <<<'MD'
Chaque commande {brand} part du Japon et arrive avec suivi chez vous en Belgique. Cette page explique comment nous livrons, combien ça coûte, les délais et ce qui se passe en cas de problème. Le prix affiché à la commande est toujours le bon prix de livraison pour votre commande.

## Options et tarifs de livraison

{rates}

## Livraison gratuite

Les commandes dont le total des articles atteint **{free_ship}** sont livrées gratuitement en {standard}, partout en Belgique. C’est automatique à la commande, sans code. Plus pressé ? Choisissez {express} et vous ne payez que la différence entre {express} et {standard}.

## De la commande à votre porte

1. **Vous passez commande** et recevez tout de suite un e-mail avec votre numéro de commande.
2. **Vous payez.** Le Bitcoin se paie sur votre page de commande dès que vous commandez. Pour les autres cryptos, nous vous envoyons notre adresse de portefeuille dans les {reply_hours} heures.
3. **Votre paiement arrive** et vos articles vous sont réservés.
4. **Nous emballons et expédions** dans les {hold_hours} heures depuis le Japon.
5. **Vous recevez votre numéro de suivi** par e-mail dès que l’étiquette est créée.
6. **Votre colis est livré**, en Belgique généralement par bpost ou un transporteur express.

Les délais comptent à partir du départ du colis, pas de la commande.

## Suivre votre colis

Chaque colis est suivi. Un nouveau numéro de suivi peut mettre 24 à 48 heures à afficher le premier scan : c’est normal. Le parcours est généralement : étiquette créée → pris en charge au Japon → export → en transit → douane dans l’UE → remis à bpost ou au transporteur → en cours de livraison → livré.

Le suivi n’a pas bougé depuis 5 jours ouvrables ? Écrivez-nous et nous relançons le transporteur.

## Délais et retards

Les délais sont des estimations en jours ouvrables après l’expédition, pas des garanties. Ils peuvent s’allonger quand :

- la douane contrôle un colis
- c’est un jour férié au Japon (Nouvel An, Golden Week début mai, Obon mi-août) ou en Belgique
- les transporteurs sont surchargés, par exemple avant la Saint-Nicolas et Noël
- l’adresse est incomplète ou difficile d’accès

Vous préparez un événement de sortie ou un stream ? Choisissez {express} et gardez quelques jours de marge.

## Douane et taxes

Nous n’ajoutons ni TVA ni autre taxe : le total à la commande est ce que vous nous payez. Votre commande vient du Japon et passe donc la douane à son arrivée dans l’UE. Nous déclarons chaque colis honnêtement, avec son vrai contenu et sa vraie valeur. Comme pour tout colis venant de l’extérieur de l’UE, le transporteur peut demander des frais d’importation pour certaines commandes avant de livrer ; ils sont payés au transporteur, pas à nous.

Si ces frais sont refusés, le colis repart au Japon. Voir [retour à l’expéditeur](page:shipping#retour-a-lexpediteur).

## Votre adresse de livraison

Avant de commander, vérifiez le nom, la rue et le numéro, la boîte, le code postal, la commune et votre téléphone. Le transporteur utilise votre numéro pour les questions de douane et pour organiser la livraison.

Besoin de changer d’adresse ? Écrivez-nous tout de suite avec votre numéro de commande. C’est possible tant que le colis n’a pas été remis au transporteur.

## Notre emballage

- **Produits scellés :** dans leur film d’origine, dans un carton rembourré pour que rien ne bouge.
- **Cartons de distributeur :** dans le carton d’origine, si possible dans un second carton.
- **Cartes à l’unité :** en sleeve et toploader, protégées de l’humidité, dans une enveloppe rigide ou une boîte.
- **Cartes gradées :** le slab emballé et mis en boîte pour qu’il ne prenne aucun choc.

## En cas de problème

### Vérifiez votre commande à la réception

Ouvrez votre colis dès que possible et vérifiez :

- le carton : écrasé, déchiré ou mouillé ?
- le nombre d’articles par rapport à votre confirmation
- le film et les scellés des produits scellés
- l’état des cartes et les étiquettes des slabs gradés

En cas de problème, **gardez tout** : le carton avec son étiquette, l’emballage et les articles. Prenez des photos avant de jeter quoi que ce soit ; le transporteur les demande. Pour les commandes de valeur, filmez le déballage.

### Endommagé pendant le transport

Écrivez à [{email}](mailto:{email}) dans les **7 jours suivant la livraison** avec votre numéro de commande et des photos du colis (tous les côtés et l’étiquette), de l’emballage et des dégâts. Nous remplaçons ou remboursons les articles endommagés, avec la livraison.

De légères marques sur une boîte scellée, comme un petit enfoncement ou un pli du film, sont normales après la manipulation en usine et chez le distributeur. En cas de doute, envoyez-nous des photos.

### Articles manquants ou erronés

Écrivez-nous dans les 7 jours avec votre numéro de commande, ce qui manque ou ne va pas, et des photos. Gardez un article erroné non ouvert. Nous envoyons ce que vous avez commandé ou le remboursons, et si l’erreur vient de nous, le retour est à nos frais.

### Perdu en transit

Votre colis n’est pas arrivé 10 jours ouvrables après la dernière date estimée, ou le suivi n’a pas bougé depuis 7 jours ouvrables ? Écrivez-nous et nous ouvrons une enquête chez le transporteur. S’il confirme la perte, nous renvoyons votre commande ou la remboursons entièrement.

### Retour à l’expéditeur

Un colis nous revient si l’adresse est fausse ou incomplète, si la livraison échoue, s’il n’est pas retiré ou si les frais d’importation sont refusés. Nous vous contactons : nous le renvoyons une fois la nouvelle livraison payée, ou nous remboursons votre commande moins la livraison aller et retour.

## Retours

### Droit de rétractation : 14 jours

Si vous achetez en tant que consommateur, vous pouvez renoncer à votre achat sans motif jusqu’à 14 jours après la livraison. Prévenez-nous dans ce délai par e-mail à [{email}](mailto:{email}), avec votre numéro de commande. Renvoyez ensuite les articles dans les 14 jours. Les frais de retour sont à votre charge.

Nous remboursons le prix des articles et les frais de livraison standard d’origine dans les 14 jours suivant votre demande. Nous pouvons attendre le retour des articles avant de rembourser.

Les articles doivent revenir complets et dans le même état. Ouvrir une boîte ou un booster scellé va au-delà de ce qui est nécessaire pour examiner le produit : pour une boîte ouverte, des boosters ouverts ou un scellé abîmé, nous pouvons retenir la dépréciation. Les cartes à l’unité et les slabs gradés reviennent dans le même sleeve ou slab, avec le même numéro de certificat.

### Acheteurs professionnels

Si vous achetez pour votre entreprise, le droit de rétractation ne s’applique pas. Un retour reste possible d’un commun accord ; nos erreurs sont toujours réparées gratuitement.

## Remboursements

Les remboursements se font de la même manière que le paiement :

- **Bitcoin et autres cryptos :** vers une adresse de portefeuille que vous confirmez par e-mail, pour le montant en euros des articles remboursés, converti au cours du jour du remboursement. Les frais de réseau sont déduits du montant envoyé.

Votre portefeuille peut mettre un peu de temps à l’afficher.

## Annulations et précommandes

Vous pouvez annuler gratuitement tant que votre commande n’est pas expédiée : écrivez-nous avec votre numéro de commande. Ensuite, les règles de retour ci-dessus s’appliquent.

Les précommandes sont facturées quand le stock est attribué, pas au moment de la commande, et expédiées dès l’arrivée du stock, généralement à la date de sortie japonaise ou juste après. Les dates de sortie sont fixées par The Pokémon Company et peuvent bouger. Si nous ne pouvons pas honorer une précommande, nous la remboursons entièrement.

## Garantie et authenticité

Tout ce que nous vendons est authentique, acheté par la distribution japonaise et expédié tel qu’il est sorti de l’usine. En tant que consommateur, vous bénéficiez en plus de la garantie légale de 2 ans pour les défauts présents à la livraison. Un doute sur un article ? Gardez-le tel qu’il est arrivé et écrivez-nous avec des photos.

## Questions fréquentes

### Livrez-vous des cartes Pokémon en Belgique ?

Oui. Tout part du Japon et est livré avec suivi dans toute la Belgique : {standard} en {standard_days}, {express} en {express_days}.

### La livraison est-elle gratuite ?

À partir de {free_ship} d’articles, la livraison {standard} est gratuite. L’{express} ne coûte alors que la différence.

### Combien coûte la livraison ?

Cela dépend du poids de votre commande. Le prix exact s’affiche à la commande, avant de payer. Le tableau sur cette page montre le calcul.

### Vais-je payer la TVA ou des frais d’importation ?

Nous n’ajoutons ni TVA ni autre taxe. Comme pour tout colis venant de l’extérieur de l’UE, le transporteur peut demander des frais d’importation pour certaines commandes ; ils sont payés au transporteur.

### Puis-je renvoyer ma commande ?

Oui. En tant que consommateur, vous avez 14 jours de rétractation après la livraison. Les boîtes et boosters ouverts peuvent revenir, mais nous pouvons alors retenir la dépréciation.

### Ma commande est arrivée abîmée. Que faire ?

Gardez le colis, l’emballage et les articles, prenez des photos et écrivez-nous dans les 7 jours suivant la livraison. Nous remplaçons ou remboursons.

### Comment suivre ma commande ?

Nous vous envoyons le numéro de suivi dès que la commande part. Un nouveau numéro peut mettre 24 à 48 heures à afficher le premier scan.

## Nous contacter

Écrivez à [{email}](mailto:{email}) avec votre numéro de commande, votre numéro de suivi si vous l’avez, le problème et des photos si utile. Nous répondons dans les {reply_hours} heures, en français, néerlandais ou anglais.

{company} · {address}
MD,
  ],

  'methods' => [
    'standard' => ['label'=>'Standard', 'days'=>'5–9'],
    'express'  => ['label'=>'Express', 'days'=>'2–4'],
  ],

  'payments' => [
    'bitcoin' => ['label'=>'Bitcoin (BTC) : payer maintenant', 'note'=>'Payez dès la commande depuis n’importe quel portefeuille Bitcoin. Le montant exact et un QR code s’affichent sur la page suivante.'],
    'crypto'  => ['label'=>'Autres cryptos (ETH, USDT)', 'note'=>'ETH ou USDT (TRC-20 ou ERC-20). Nous vous envoyons notre adresse de portefeuille avec votre facture. Les frais de réseau sont à la charge de l’expéditeur.'],
  ],

  'categories' => [
    'boxes' => [
      'label' => 'Displays & boosters', 'slug' => 'display-pokemon',
      'blurb' => 'Displays Pokémon japonais scellés et cartons de displays, directement du Japon.',
      'h1' => 'Displays Pokémon japonais (boîtes de boosters)',
      'seo_title' => 'Display Pokémon japonais : boîtes de boosters scellées',
      'seo_desc' => 'Displays Pokémon japonais scellés : 151, Terastal Festival ex, 30th Celebration et les dernières séries Méga-Évolution. Boosters Pokémon envoyés du Japon, prix en euros.',
      'intro' => <<<'MD'
## Qu’est-ce qu’un display Pokémon ?

Un display Pokémon (ou boîte de boosters) est une boîte scellée qui contient les boosters d’une seule série. C’est la façon la plus avantageuse d’acheter des boosters Pokémon : chaque booster revient moins cher qu’à l’unité, et vous avez la meilleure chance d’obtenir les cartes rares de la série.

Les displays japonais sont plus petits que les displays anglais ou français. La plupart des displays japonais d’une série principale contiennent 30 boosters de 5 cartes. Les séries spéciales sont différentes : un [display 151](product:151-booster-box) contient 20 boosters de 7 cartes, et un [display Terastal Festival ex](product:terastal-festival-booster-box) 10 boosters de 10 cartes.

## Nos displays en stock

Nous proposons les séries japonaises [Méga-Évolution](set:mega-evolution), de [Mega Brave](set:mega-brave) à [Storm Emeralda](set:storm-emeralda), ainsi que les incontournables [Écarlate et Violet](set:scarlet-violet) comme [151](set:151) et [Glory of Team Rocket](set:glory-of-team-rocket). Pour les gros acheteurs, il existe des cartons scellés de 12 displays. Plus vous prenez de displays, plus le prix par boîte baisse : toute la grille est sur chaque fiche produit.

## Boosters à l’unité ou display ?

Les boosters à l’unité sont amusants à ouvrir, mais un display vous donne plus de boosters pour votre argent et une vraie chance d’obtenir les cartes phares. Tout sur les boosters, les displays et leur contenu : [booster Pokémon, tout comprendre](guide:boosters). Nouveau dans les produits japonais ? Lisez [pourquoi les collectionneurs achètent des cartes japonaises](guide:japanese).
MD,
    ],
    'etb' => [
      'label' => 'Coffrets Dresseur d’Élite', 'slug' => 'coffret-dresseur-elite',
      'blurb' => 'Coffrets Dresseur d’Élite (ETB) et cartons, avec boosters, protège-cartes et accessoires.',
      'h1' => 'Coffrets Dresseur d’Élite Pokémon (ETB)',
      'seo_title' => 'Coffret Dresseur d’Élite Pokémon (ETB)',
      'seo_desc' => 'Coffrets Dresseur d’Élite Pokémon (Elite Trainer Box) : 30th Celebration, Perfect Order, Ascended Heroes, Chaos Rising et plus, avec boosters, protège-cartes et dés.',
      'intro' => <<<'MD'
## Que contient un coffret Dresseur d’Élite ?

Un coffret Dresseur d’Élite (Elite Trainer Box, ou ETB) réunit des boosters et tout le nécessaire pour jouer : protège-cartes, dés, marqueurs de dégâts, cartes Énergie et une solide boîte de rangement. C’est l’un des cadeaux Pokémon les plus populaires, et une valeur sûre en boutique.

Nous proposons les coffrets de la série Méga-Évolution, comme [Perfect Order](product:perfect-order-elite-trainer-box), [Ascended Heroes](product:mega-evolution-ascended-heroes-elite-trainer-box), [Chaos Rising](product:chaos-rising-elite-trainer-box) et [Pitch Black](product:pitch-black-elite-trainer-box), ainsi que le [coffret 30th Celebration](product:30th-celebration-elite-trainer-box) et des cartons complets de 10.

Les coffrets se vendent par quatre, ou par carton de 10, avec des prix plus bas à partir de 24. Envie de jouer ? Lisez [comment fonctionne le JCC Pokémon](guide:tcg).
MD,
    ],
    'premium' => [
      'label' => 'Coffrets Pokémon', 'slug' => 'coffret-pokemon',
      'blurb' => 'Coffrets collection, decks de démarrage, coffrets premium et éditions 30th Celebration.',
      'h1' => 'Coffrets Pokémon : boîtes collection, decks et coffrets premium',
      'seo_title' => 'Coffret carte Pokémon : boîtes collection et decks',
      'seo_desc' => 'Coffrets et boîtes Pokémon : coffrets collection avec carte promo, decks Starter Set ex, Premium Trainer Box MEGA et éditions 30th Celebration, envoyés du Japon.',
      'intro' => <<<'MD'
## Quel coffret de cartes Pokémon choisir ?

Toutes les boîtes Pokémon ne sont pas des displays. Les coffrets collection comme la [boîte 30th Celebration Amphinobi-ex](product:30th-celebration-greninja-ex-box) et la [boîte Nymphali-ex](product:30th-celebration-sylveon-ex-box) associent des boosters à une carte promo. Un [Starter Set ex](product:starter-set-ex-eevee-ex) ou la [MEGA Start Deck 100 Battle Collection](product:mega-start-deck-100-battle-collection) donne aux nouveaux joueurs un deck prêt à jouer.

Pour les collectionneurs, il y a le [coffret premium 30th Celebration](product:30th-celebration-premium-deck-set-espeon-and-umbreon) avec Mentali et Noctali. Un coffret de cartes Pokémon est un cadeau facile, et les decks de démarrage sont la façon la plus simple d’apprendre à jouer.
MD,
    ],
    'singles' => [
      'label' => 'Cartes rares', 'slug' => 'cartes-pokemon-rares',
      'blurb' => 'Cartes japonaises rares à l’unité : illustrations spéciales, full art, cartes dorées et gradées PSA.',
      'h1' => 'Cartes Pokémon rares à l’unité (japonaises)',
      'seo_title' => 'Cartes Pokémon rares à l’unité : cartes japonaises',
      'seo_desc' => 'Cartes Pokémon rares à l’unité : illustrations spéciales rares, full art et cartes dorées de Dracaufeu, Pikachu, Ectoplasma et Méga-Rayquaza. Near Mint ou gradées PSA.',
      'intro' => <<<'MD'
## Cartes Pokémon rares et chères

Nos cartes à l’unité sont le haut de chaque série : illustrations spéciales rares en full art, Hyper Rares dorées et la rareté la plus élevée de la série Méga-Évolution, comme la [Méga-Rayquaza-ex Master Ultra Rare](product:mega-rayquaza-ex-mur). Vous y trouverez des [cartes Dracaufeu](cards:charizard), des [cartes Pikachu](cards:pikachu), [Ectoplasma](cards:gengar) et un [Pikachu gradé PSA 10](cards:psa).

Les cartes non gradées sont Near Mint et partent en sleeve et toploader. Combien vaut une carte ? Notre guide [valeur d’une carte Pokémon](guide:value) explique la cote et l’estimation, et le [vérificateur de prix](guide:prices) montre tous nos prix actuels.
MD,
    ],
    'accessories' => [
      'label' => 'Albums & accessoires', 'slug' => 'album-carte-pokemon',
      'blurb' => 'Albums pour cartes, protège-cartes, deck box, tapis de jeu et rangement.',
      'h1' => 'Album carte Pokémon, protège-cartes et deck box',
      'seo_title' => 'Album carte Pokémon, protège-cartes et deck box',
      'seo_desc' => 'Albums pour cartes Pokémon, protège-cartes, deck box, tapis de jeu et boîtes de rangement, dont l’album 9 cases Méga-Évolution et les sleeves Ultra PRO Pikachu.',
      'intro' => <<<'MD'
## Bien ranger sa collection de cartes Pokémon

Un bon album pour cartes Pokémon et les bons protège-cartes gardent votre collection en état Near Mint. Notre [album 9 cases Pokémon](product:pokemon-tcg-9-pocket-binder-mega-evolution-series) range neuf cartes par page, et nos protège-cartes vont des [Ultra PRO Pikachu Deck Protector](product:ultra-pro-pikachu-deck-protector-sleeves-65-ct) aux designs [Celebi et Fouinar](product:celebi-and-furret-deck-sleeves) ou [Méga-Rayquaza](product:storm-emeralda-mega-rayquaza-deck-sleeves).

Les cartes Pokémon mesurent 63 × 88 mm ; les cartes japonaises et anglaises ont la même taille, donc les protège-cartes standard conviennent. Tout pour commencer une collection : [album et collection de cartes Pokémon](guide:collect).
MD,
    ],
  ],

  'series' => [
    'mega' => [
      'name' => 'Méga-Évolution',
      'h1' => 'Séries Pokémon Méga-Évolution (japonaises)',
      'seo_title' => 'Séries Pokémon Méga-Évolution : displays japonais',
      'seo_desc' => 'Toutes les séries japonaises Pokémon Méga-Évolution : Mega Brave, Mega Symphonia, Inferno X, Mega Dream ex, Nihil Zero, Ninja Spinner, Abyss Eye, Storm Emeralda et plus.',
      'intro' => <<<'MD'
La Méga-Évolution est revenue dans le Jeu de Cartes à Collectionner Pokémon en 2025, lancée au Japon avec les séries jumelles [Mega Brave](set:mega-brave) et [Mega Symphonia](set:mega-symphonia). Chaque série depuis apporte de nouveaux Pokémon Méga-ex, de Méga-Ectoplasma-ex dans [Mega Dream ex](set:mega-dream-ex) à Méga-Rayquaza-ex dans [Storm Emeralda](set:storm-emeralda).

Voici chaque série Méga-Évolution que nous proposons, avec displays scellés, coffrets Dresseur d’Élite et les cartes clés à l’unité. Les séries japonaises sortent en premier, bien avant les versions française et anglaise. Quelle est la prochaine ? Voir [sorties Pokémon](guide:releases).
MD,
    ],
    'sv' => [
      'name' => 'Écarlate et Violet',
      'h1' => 'Séries Pokémon Écarlate et Violet (japonaises)',
      'seo_title' => 'Séries Pokémon Écarlate et Violet : displays japonais',
      'seo_desc' => 'Séries japonaises Pokémon Écarlate et Violet : displays et cartes de 151, Terastal Festival ex, Heat Wave Arena et Glory of Team Rocket.',
      'intro' => <<<'MD'
L’ère Écarlate et Violet a duré de 2023 jusqu’au début de la Méga-Évolution en 2025, et a produit certaines des séries japonaises les plus collectionnées, comme [151](set:151), avec les 151 premiers Pokémon, et [Terastal Festival ex](set:terastal-festival-ex).

Nous avons encore des displays Écarlate et Violet scellés du Japon, dont [Heat Wave Arena](set:heat-wave-arena) et [Glory of Team Rocket](set:glory-of-team-rocket). Une fois ces séries épuisées, les displays scellés deviennent plus rares.
MD,
    ],
  ],

  'sets' => [
    'Aura Seeker (Hadou Seeker)' => ['intro'=>'Aura Seeker (Hadou Seeker) est une prochaine série japonaise de la gamme Méga-Évolution. Précommandez vos displays dès maintenant : les précommandes ne sont facturées qu’à l’attribution du stock.'],
    'Storm Emeralda' => ['intro'=>'Storm Emeralda (M6) est la série japonaise Méga-Évolution centrée sur Méga-Rayquaza-ex. Nous proposons des displays Storm Emeralda scellés, des cartons de 12 displays et des coffrets Dresseur d’Élite, la Méga-Rayquaza-ex Master Ultra Rare et l’illustration spéciale rare, et des protège-cartes Méga-Rayquaza assortis.'],
    '30th Celebration' => ['intro'=>"30th Celebration fête les 30 ans de Pokémon en 2026. Mewtwo-ex et Mew-ex mènent la série, avec Noctali-ex, Drattak-ex et Amphinobi-ex, et chaque booster contient un Pikachu : 30 cartes rares Pikachu différentes à collectionner.\n\nLa gamme comprend des displays, des [coffrets Dresseur d’Élite](product:30th-celebration-elite-trainer-box) et des cartons, les boîtes Amphinobi-ex et Nymphali-ex, le coffret premium Mentali et Noctali et une collection d’autocollants."],
    'Abyss Eye' => ['intro'=>'Abyss Eye (M5) est la série japonaise Méga-Évolution avec Méga-Darkrai-ex et Méga-Minotaupe-ex. Nous proposons des displays et coffrets Dresseur d’Élite Abyss Eye scellés, le coffret Pitch Black, et Méga-Darkrai-ex et Méga-Minotaupe-ex à l’unité.'],
    'Ninja Spinner' => ['intro'=>'Ninja Spinner (M4) apporte Méga-Amphinobi-ex et Méga-Floette-ex à la série Méga-Évolution. En stock : displays Ninja Spinner scellés (quantité limitée), le coffret Chaos Rising, et Méga-Amphinobi-ex et Méga-Floette-ex à l’unité.'],
    'Nihil Zero' => ['intro'=>'Nihil Zero (M3) fait partie de la série japonaise Méga-Évolution. Nous proposons des displays Nihil Zero scellés et le coffret Dresseur d’Élite Perfect Order.'],
    'Mega Dream ex' => ['intro'=>'Mega Dream ex (M2a) est la série spéciale de la gamme Méga-Évolution et celle de la Méga-Ectoplasma-ex illustration spéciale rare. En stock : displays Mega Dream ex scellés, le coffret Ascended Heroes et la Méga-Ectoplasma-ex elle-même.'],
    'Inferno X' => ['intro'=>'Inferno X (M2) est la deuxième série principale japonaise de la gamme Méga-Évolution, disponible ici en display scellé.'],
    'Mega Symphonia' => ['intro'=>'Mega Symphonia (M1S) est l’une des deux séries qui ont lancé la gamme Méga-Évolution au Japon en 2025, avec Mega Brave.'],
    'Mega Brave' => ['intro'=>'Mega Brave (M1L) a ouvert la série japonaise Méga-Évolution en 2025, avec sa série jumelle Mega Symphonia.'],
    'Glory of Team Rocket' => ['intro'=>'Glory of Team Rocket (SV10) ramène les Pokémon de la Team Rocket dans le JCC Pokémon et fait partie des séries japonaises Écarlate et Violet les plus demandées. Disponible en display scellé.'],
    'Heat Wave Arena' => ['intro'=>'Heat Wave Arena (SV9a) est une extension japonaise Écarlate et Violet, disponible en display scellé.'],
    'Terastal Festival ex' => ['intro'=>'Terastal Festival ex (SV8a) est la série spéciale Écarlate et Violet consacrée aux Pokémon Téracristal et à toutes les évolutions d’Évoli, sortie en anglais sous le nom Prismatic Evolutions. Les displays japonais contiennent 10 boosters de 10 cartes.'],
    '151' => [
      'seo_title'=>'Cartes Pokémon 151 : display 151 japonais et Dracaufeu',
      'seo_desc'=>'Cartes Pokémon 151 japonaises (SV2a) : displays 151 scellés et Dracaufeu-ex illustration spéciale rare, envoyés du Japon en Belgique.',
      'intro'=>"Pokémon Card 151 (SV2a) est la série spéciale japonaise consacrée aux 151 premiers Pokémon de Rouge et Vert, de Bulbizarre à Mew. C’est l’une des séries les plus collectionnées de l’ère Écarlate et Violet, sortie en français sous le nom Écarlate et Violet – 151. Un display 151 japonais contient 20 boosters de 7 cartes, et parmi les cartes phares figure Dracaufeu-ex illustration spéciale rare.\n\n## Liste des cartes Pokémon 151\n\nLa série japonaise 151 (sortie le 16 juin 2023) compte 210 cartes : une base de 165 cartes avec les 151 premiers Pokémon, des cartes Dresseur et Énergie, et 45 cartes secrètes : 18 Art Rares (AR), 16 Super Rares (SR), 8 Special Art Rares (SAR) et 3 Ultra Rares dorées (UR). Toutes les séries sont dans notre [liste des séries de cartes Pokémon](guide:sets).\n\nNous proposons des displays 151 scellés et Dracaufeu-ex SAR de 151. Plus de Dracaufeu ? Voir toutes nos [cartes Pokémon Dracaufeu](cards:charizard)."],
  ],

  'products' => [
    'storm-emeralda-elite-trainer-box' => ['name'=>'Coffret Dresseur d’Élite Storm Emeralda', 'desc'=>"Un coffret Dresseur d’Élite Storm Emeralda scellé, de la série japonaise Méga-Évolution.\n\nDans le coffret : des boosters, des protège-cartes, des dés, des marqueurs de dégâts, des cartes Énergie et la carte promo de la série, dans une boîte de rangement. Tout pour ouvrir et jouer Storm Emeralda. Se vend très bien avec les [displays Storm Emeralda](product:storm-emeralda-m6-booster-box).\n\n- Coffret Dresseur d’Élite Storm Emeralda scellé\n- Boosters, protège-cartes, dés, marqueurs, Énergies et promo\n- Par six, avec des prix plus bas à partir de 18 et 36"],
    '30th-celebration-m6a-booster-box' => ['name'=>'Display 30th Celebration (M6A) japonais', 'desc'=>"Un display japonais scellé de 30th Celebration (M6A), la série des 30 ans de Pokémon.\n\n30th Celebration fait revenir des illustrations classiques à côté de nouvelles cartes, avec un excellent taux de cartes rares. Ces displays anniversaire plaisent autant aux collectionneurs qu’à ceux qui aiment ouvrir des boîtes. Ouvrez-les pour les cartes, ou gardez-les scellés comme objet de collection.\n\n- Display japonais scellé : 30th Celebration (M6A)\n- Illustrations classiques rééditées, excellent taux de rares\n- Prix plus bas à partir de 12 et 24 displays\n- Dans la même gamme : le [coffret Dresseur d’Élite 30th Celebration](product:30th-celebration-elite-trainer-box) et les [coffrets spéciaux](set:30th-celebration)"],
    'mega-rayquaza-ex-mur' => ['name'=>'Méga-Rayquaza-ex Master Ultra Rare (japonaise)', 'desc'=>"La Méga-Rayquaza-ex Master Ultra Rare japonaise de Storm Emeralda : la carte la plus rare de la série.\n\nRayquaza est l’un des légendaires les plus collectionnés depuis ses premières cartes, et sa Master Ultra Rare est tout en haut des raretés de Storm Emeralda. Chaque exemplaire est Near Mint et part en sleeve et toploader.\n\n- Japonaise, Storm Emeralda (M6)\n- Rareté : Master Ultra Rare\n- Near Mint, en sleeve et toploader\n- Prix plus bas à partir de 3 et 6 exemplaires"],
    'mega-gengar-ex-sir' => ['name'=>'Méga-Ectoplasma-ex (Gengar) illustration spéciale rare (japonaise)', 'desc'=>"La Méga-Ectoplasma-ex illustration spéciale rare japonaise de Mega Dream ex.\n\nEctoplasma a l’un des publics les plus fidèles du hobby, et cette illustration spéciale rare est l’une des plus belles cartes Ectoplasma de la série Méga-Évolution. Near Mint, en sleeve et toploader.\n\n- Japonaise, Mega Dream ex (M2A)\n- Illustration spéciale rare (full art)\n- Prix plus bas à partir de 3 et 6 exemplaires\n- Toutes les [cartes Pokémon Ectoplasma](cards:gengar)"],
    '30th-celebration-elite-trainer-box' => ['name'=>'Coffret Dresseur d’Élite 30th Celebration', 'desc'=>"Le coffret Dresseur d’Élite Pokémon 30th Celebration : neuf boosters de la série anniversaire et tout pour jouer.\n\n30th Celebration fête trente ans de Pokémon : Mewtwo-ex et Mew-ex mènent la série, avec Noctali-ex, Drattak-ex et Amphinobi-ex, et chaque booster contient un Pikachu, avec 30 cartes rares Pikachu différentes. Vous achetez en volume ? Voir le [carton scellé de 10](product:30th-celebration-elite-trainer-box-case-10-ct).\n\n- 9 boosters Pokémon 30th Celebration\n- 1 carte promo full art holographique Nidorina\n- 16 cartes Énergie de base holographiques et 65 protège-cartes\n- Un guide du joueur 30th Celebration\n- 6 dés de dégâts, 1 dé de tournoi et 1 pièce en plastique\n- Une boîte de collection avec 6 séparateurs, plus une carte code pour Pokémon TCG Live\n- Par quatre, au prix le plus bas à partir de 24"],
    'storm-emeralda-m6-booster-box' => ['name'=>'Display Storm Emeralda (M6) japonais', 'desc'=>"Un display japonais scellé de Storm Emeralda (M6), la série Méga-Évolution menée par Méga-Rayquaza-ex.\n\nParmi les cartes phares de Storm Emeralda : la [Méga-Rayquaza-ex Master Ultra Rare](product:mega-rayquaza-ex-mur) et l’[illustration spéciale rare](product:mega-rayquaza-ex-sar-245-191-near-mint), ce qui rend les displays scellés très recherchés. Commandez par six, ou prenez un [carton scellé de 12](product:storm-emeralda-booster-box-case-12-ct) pour le meilleur prix par display.\n\n- Display japonais Méga-Évolution scellé (M6)\n- Cartes phares : Méga-Rayquaza-ex Master Ultra Rare et illustration spéciale rare\n- Meilleur prix par display à partir de 24"],
    'abyss-eye-elite-trainer-box' => ['name'=>'Coffret Dresseur d’Élite Abyss Eye', 'desc'=>"Un coffret Dresseur d’Élite Abyss Eye scellé, de la série japonaise Méga-Évolution.\n\nDes boosters avec protège-cartes, dés, marqueurs et la carte promo, dans une boîte de rangement : la façon facile d’ouvrir Abyss Eye à côté des [displays](product:abyss-eye-m5-booster-box).\n\n- Coffret Dresseur d’Élite Abyss Eye scellé\n- Par six, avec des prix plus bas à partir de 18 et 36"],
    '30th-celebration-greninja-ex-box' => ['name'=>'Coffret 30th Celebration Amphinobi-ex (Greninja)', 'desc'=>"Un coffret collection 30th Celebration autour d’une carte promo Amphinobi-ex, avec des boosters.\n\nCes coffrets se vendent bien à l’unité et en cadeau, et les prix à partir de 18 et 36 boîtes laissent de la marge aux boutiques. Il va parfaitement avec le [coffret Nymphali-ex](product:30th-celebration-sylveon-ex-box).\n\n- Carte promo Amphinobi-ex et boosters\n- De la gamme [30th Celebration](set:30th-celebration)\n- Par six, avec des prix plus bas à partir de 18 et 36"],
    'heat-wave-arena-sv9a-booster-box' => ['name'=>'Display Heat Wave Arena (SV9a) japonais', 'desc'=>"Un display japonais scellé de Heat Wave Arena (SV9a), de la série Écarlate et Violet.\n\nHeat Wave Arena est un réassort fiable pour les boutiques qui organisent des drafts et des soirées de jeu, et une valeur sûre pour les collectionneurs de produits japonais Écarlate et Violet.\n\n- Display japonais scellé : Heat Wave Arena (SV9a)\n- De la série [Écarlate et Violet](set:scarlet-violet)\n- Prix plus bas à partir de 18 et 36 displays"],
    'glory-of-team-rocket-sv10-booster-box' => ['name'=>'Display Glory of Team Rocket (SV10) japonais', 'desc'=>"Un display japonais scellé de Glory of Team Rocket (SV10) : les Pokémon de la Team Rocket reviennent dans le JCC.\n\nGlory of Team Rocket est l’une des séries japonaises Écarlate et Violet les plus demandées, construite autour des Pokémon de la Team Rocket. Le stock part vite.\n\n- Display japonais scellé : Glory of Team Rocket (SV10)\n- De la série [Écarlate et Violet](set:scarlet-violet)\n- Prix plus bas à partir de 18 et 36 displays"],
    'mega-start-deck-100-battle-collection' => ['name'=>'MEGA Start Deck 100 Battle Collection', 'desc'=>"La MEGA Start Deck 100 Battle Collection : des decks Pokémon prêts à jouer pour les nouveaux joueurs.\n\nLes decks de démarrage sont la façon la plus simple d’apprendre le jeu de cartes Pokémon : on ouvre et on joue, sans construire de deck. Pour les boutiques, c’est un produit d’entrée peu cher pour les soirées de jeu et les événements débutants.\n\n- Decks prêts à jouer de l’ère Méga-Évolution\n- Par carton de 12, au prix le plus bas à partir de 72"],
    'starter-set-ex-zorua-and-zoroark-ex' => ['name'=>'Starter Set ex : Zorua et Zoroark-ex', 'desc'=>"Starter Set ex avec un deck prêt à jouer autour de Zorua et Zoroark-ex.\n\nUn deck complet autour de Zoroark-ex, prêt à jouer dès l’ouverture : un bon premier deck pour les nouveaux joueurs et un ajout facile à côté des displays.\n\n- Deck prêt à jouer Zorua et Zoroark-ex\n- Par carton de 12, au prix le plus bas à partir de 72"],
    'premium-trainer-box-mega' => ['name'=>'Premium Trainer Box MEGA', 'desc'=>"Premium Trainer Box MEGA : un coffret premium de l’ère Méga-Évolution du JCC Pokémon.\n\nLes coffrets premium associent des boosters à des accessoires de jeu pour construire ses premiers decks Méga-Évolution, et font un excellent cadeau.\n\n- Coffret premium de l’ère Méga-Évolution\n- Par quatre, au prix le plus bas à partir de 24"],
    '151-booster-box' => ['name'=>'Display Pokémon 151 japonais (SV2a)', 'desc'=>"Un display japonais scellé de 151 (SV2a) : 20 boosters de 7 cartes de la série consacrée aux 151 premiers Pokémon.\n\nLes cartes Pokémon 151 sont parmi les plus collectionnées de l’ère Écarlate et Violet : les Pokémon d’origine, de Bulbizarre à Mew, en illustrations modernes avec des rares full art. La [Dracaufeu-ex illustration spéciale rare](product:151-charizard-ex-special-illustration-rare) est la carte phare. 151 est sortie plus tard en français, mais la version japonaise est arrivée la première et beaucoup de collectionneurs la préfèrent.\n\n- Display 151 japonais scellé (SV2a) : 20 boosters de 7 cartes\n- Carte phare : Dracaufeu-ex illustration spéciale rare\n- Par six, au prix le plus bas à partir de 36 displays\n- Nouveau dans les displays japonais ? Lisez [ce qui change avec les cartes japonaises](guide:japanese)"],
    '151-charizard-ex-special-illustration-rare' => ['name'=>'Dracaufeu-ex (Charizard) 151 illustration spéciale rare (japonaise)', 'desc'=>"La Dracaufeu-ex illustration spéciale rare japonaise de 151 (SV2a).\n\nDracaufeu est l’un des Pokémon les plus collectionnés du JCC, et son illustration spéciale rare de 151 est l’une des cartes Dracaufeu phares de l’ère Écarlate et Violet. Near Mint, en sleeve et toploader.\n\n- Japonaise, 151 (SV2a)\n- Illustration spéciale rare (full art)\n- Prix plus bas à partir de 6 exemplaires\n- Plus de [cartes Pokémon Dracaufeu](cards:charizard)"],
    'storm-emeralda-booster-box-case-12-ct' => ['name'=>'Carton de 12 displays Storm Emeralda', 'desc'=>"Un carton scellé de 12 displays japonais Storm Emeralda (M6), vendu au carton.\n\nPour les boutiques et les box breakers qui prennent Storm Emeralda en volume, le carton scellé est l’achat le plus simple : douze displays dans un seul carton, moins chers par boîte qu’à l’unité, et encore moins à partir de six cartons.\n\n- 12 displays japonais Storm Emeralda (M6) scellés par carton\n- Prix par display plus bas qu’à l’unité, remise supplémentaire à partir de 6 cartons\n- Carte phare : [Méga-Rayquaza-ex Master Ultra Rare](product:mega-rayquaza-ex-mur)"],
    'aura-seeker-booster-box' => ['name'=>'Display Aura Seeker (Hadou Seeker) japonais – précommande', 'release'=>'novembre 2026', 'desc'=>"Précommandez le display japonais Aura Seeker (Hadou Seeker), une prochaine série de la gamme Méga-Évolution.\n\nUne précommande réserve votre attribution avant la sortie, aux prix par quantité ci-dessous. Vous n’êtes facturé qu’à l’attribution du stock, pas à la commande, et les displays partent du Japon dès leur sortie et leur paiement.\n\n- Display japonais scellé, en précommande\n- Rien à payer aujourd’hui : facture à l’attribution\n- Par six, au prix le plus bas à partir de 36 displays"],
    'starter-set-ex-eevee-ex' => ['name'=>'Starter Set ex : Évoli-ex (Eevee)', 'desc'=>"Starter Set ex avec un deck prêt à jouer autour d’Évoli-ex.\n\nUn deck complet autour d’Évoli-ex, prêt à jouer dès l’ouverture : un premier achat facile pour les nouveaux joueurs et un cadeau populaire.\n\n- Deck prêt à jouer Évoli-ex\n- Par carton de 10, au prix le plus bas à partir de 60"],
    'pokemon-tcg-card-storage-box-booster-box-display' => ['name'=>'Boîte de rangement Pokémon style display', 'desc'=>"Une boîte de rangement pour cartes Pokémon au style d’un display de boosters.\n\nElle garde les cartes, avec ou sans protège-cartes, droites et à plat sur une étagère ou un comptoir, et sert aussi de pièce d’exposition.\n\n- Boîte de rangement style display\n- Par six, au prix le plus bas à partir de 36"],
    'pokemon-tcg-9-pocket-binder-mega-evolution-series' => ['name'=>'Album carte Pokémon 9 cases Méga-Évolution', 'desc'=>"Un album pour cartes Pokémon à 9 cases par page, illustré Méga-Évolution.\n\nChaque page range neuf cartes Pokémon standard : la façon la plus simple d’organiser une collection qui grandit et de montrer vos plus belles cartes. Les cartes avec un protège-carte y entrent sans problème.\n\n- Album 9 cases par page\n- Illustrations Méga-Évolution\n- Par six, au prix le plus bas à partir de 36"],
    'celebi-and-furret-deck-sleeves' => ['name'=>'Protège-cartes Celebi et Fouinar (Furret)', 'desc'=>"Des protège-cartes Celebi et Fouinar, au format des cartes Pokémon standard.\n\nLes protège-cartes protègent les cartes en jeu comme en rangement, et les designs de personnages sont un ajout populaire en caisse.\n\n- Pour cartes Pokémon standard de 63 × 88 mm\n- Par 24, au prix le plus bas à partir de 144"],
    'pikachu-ditto-ver-deck-sleeves' => ['name'=>'Protège-cartes Pikachu (version Métamorph)', 'desc'=>"Des protège-cartes avec Pikachu en version Métamorph, au format des cartes Pokémon standard.\n\nLes protège-cartes protègent les cartes en jeu comme en rangement, et les designs de personnages sont un ajout populaire en caisse.\n\n- Pour cartes Pokémon standard de 63 × 88 mm\n- Par 24, au prix le plus bas à partir de 144"],
    'storm-emeralda-mega-rayquaza-deck-sleeves' => ['name'=>'Protège-cartes Méga-Rayquaza Storm Emeralda', 'desc'=>"Des protège-cartes Méga-Rayquaza de la sortie Storm Emeralda, au format des cartes Pokémon standard.\n\nLes protège-cartes protègent les cartes en jeu comme en rangement. Ils accompagnent naturellement les [displays Storm Emeralda](product:storm-emeralda-m6-booster-box).\n\n- Pour cartes Pokémon standard de 63 × 88 mm\n- Par 24, au prix le plus bas à partir de 144"],
    'ultra-pro-pikachu-alcove-tower-deck-box' => ['name'=>'Deck box Ultra PRO Pikachu Alcove Tower', 'desc'=>"Une deck box Ultra PRO Alcove Tower illustrée Pikachu.\n\nUne deck box solide pour emporter un deck protégé aux soirées de jeu et aux tournois, avec un Pikachu qui se vend tout seul.\n\n- Deck box Ultra PRO Alcove Tower\n- Illustration Pikachu\n- Par 12, au prix le plus bas à partir de 72"],
    'ultra-pro-pikachu-deck-protector-sleeves-65-ct' => ['name'=>'Protège-cartes Ultra PRO Pikachu (65 pièces)', 'desc'=>"Des protège-cartes Ultra PRO Deck Protector illustrés Pikachu : 65 protège-cartes au format standard par paquet.\n\nLes protège-cartes standard conviennent aux cartes Pokémon japonaises comme anglaises, pour jouer et ranger.\n\n- 65 protège-cartes par paquet\n- Pour cartes Pokémon standard de 63 × 88 mm\n- Par 24, au prix le plus bas à partir de 144"],
    'pokemon-playmat-assorted-designs' => ['name'=>'Tapis de jeu Pokémon : modèles assortis', 'desc'=>"Des tapis de jeu Pokémon en modèles assortis.\n\nUn tapis de jeu protège les cartes pendant la partie et délimite la zone de jeu : un accessoire apprécié des joueurs et un bon vendeur en boutique. Les modèles varient selon les arrivages.\n\n- Modèles Pokémon assortis\n- Minimum 10, puis par cinq, au prix le plus bas à partir de 50"],
    'pokemon-deck-box-assorted' => ['name'=>'Deck box Pokémon : modèles assortis', 'desc'=>"Des deck box Pokémon en modèles assortis.\n\nUne deck box garde un deck protégé en sécurité dans un sac ou une poche, et se vend bien avec les protège-cartes et les tapis. Les modèles varient selon les arrivages.\n\n- Modèles Pokémon assortis\n- Minimum 20, puis par dix, au prix le plus bas à partir de 100"],
    'pokemon-card-sleeves-64-ct-assorted-designs' => ['name'=>'Protège-cartes Pokémon (64 pièces) : modèles assortis', 'desc'=>"Des protège-cartes Pokémon en modèles assortis, 64 par paquet.\n\nAu format des cartes Pokémon standard de 63 × 88 mm (les cartes japonaises et anglaises ont la même taille), ils gardent vos cartes Near Mint en jeu comme en rangement. Les modèles varient selon les arrivages.\n\n- 64 protège-cartes par paquet\n- Pour cartes Pokémon japonaises et anglaises\n- Minimum 20, puis par dix, au prix le plus bas à partir de 100"],
    'mega-rayquaza-ex-sar-245-191-near-mint' => ['name'=>'Méga-Rayquaza-ex SAR 245/191 (japonaise, Near Mint)', 'desc'=>"La Méga-Rayquaza-ex illustration spéciale rare japonaise, carte 245/191 de Storm Emeralda, en état Near Mint.\n\nUne illustration spéciale rare full art du Pokémon vedette de la série, et une façon plus abordable d’avoir Méga-Rayquaza que la [Master Ultra Rare](product:mega-rayquaza-ex-mur).\n\n- Japonaise, Storm Emeralda (M6), carte 245/191\n- Illustration spéciale rare (full art)\n- Near Mint, en sleeve et toploader\n- Minimum 3 exemplaires, au prix le plus bas à partir de 25"],
    'pikachu-ex-sar-240-191-psa-10-gem-mint' => ['name'=>'Pikachu-ex SAR 240/191 gradée PSA 10 Gem Mint', 'desc'=>"Pikachu-ex illustration spéciale rare 240/191, gradée PSA 10 Gem Mint.\n\nPSA 10 est la note la plus haute : une carte quasi parfaite, authentifiée et scellée dans le slab de PSA. Les cartes Pikachu plaisent à tous les collectionneurs, et les exemplaires les mieux notés sont ceux qu’ils gardent.\n\n- Pikachu-ex illustration spéciale rare 240/191\n- Gradée PSA 10 Gem Mint\n- Envoyée dans son slab PSA d’origine\n- Plus de [cartes Pokémon gradées PSA](cards:psa)"],
    'mega-floette-ex-japanese' => ['name'=>'Méga-Floette-ex (japonaise)', 'desc'=>"La Méga-Floette-ex japonaise de Ninja Spinner (M4), en état Near Mint.\n\nMéga-Floette-ex est l’un des nouveaux Pokémon Méga-ex de Ninja Spinner. Envoyée en sleeve et toploader.\n\n- Japonaise, [Ninja Spinner](set:ninja-spinner) (M4)\n- Near Mint, en sleeve et toploader\n- Prix plus bas à partir de 6 exemplaires"],
    'mega-excadrill-ex-japanese' => ['name'=>'Méga-Minotaupe-ex (Excadrill, japonaise)', 'desc'=>"La Méga-Minotaupe-ex japonaise d’Abyss Eye (M5), en état Near Mint.\n\nMéga-Minotaupe-ex est l’un des Pokémon Méga-ex d’Abyss Eye. Envoyée en sleeve et toploader.\n\n- Japonaise, [Abyss Eye](set:abyss-eye) (M5)\n- Near Mint, en sleeve et toploader\n- Prix plus bas à partir de 6 exemplaires"],
    'mega-greninja-ex-japanese' => ['name'=>'Méga-Amphinobi-ex (Greninja, japonaise)', 'desc'=>"La Méga-Amphinobi-ex japonaise de Ninja Spinner (M4), en état Near Mint.\n\nAmphinobi est un favori de longue date, et Méga-Amphinobi-ex est l’une des vedettes de Ninja Spinner. Envoyée en sleeve et toploader.\n\n- Japonaise, [Ninja Spinner](set:ninja-spinner) (M4)\n- Near Mint, en sleeve et toploader\n- Prix plus bas à partir de 6 exemplaires"],
    'mega-darkrai-ex-japanese' => ['name'=>'Méga-Darkrai-ex (japonaise)', 'desc'=>"La Méga-Darkrai-ex japonaise d’Abyss Eye (M5), en état Near Mint.\n\nDarkrai est un Pokémon fabuleux très apprécié, et Méga-Darkrai-ex est l’une des cartes phares d’Abyss Eye. Envoyée en sleeve et toploader.\n\n- Japonaise, [Abyss Eye](set:abyss-eye) (M5)\n- Near Mint, en sleeve et toploader\n- Prix plus bas à partir de 6 exemplaires"],
    'pikachu-ex-special-illustration-rare-277-217' => ['name'=>'Pikachu-ex illustration spéciale rare 277/217', 'desc'=>"Pikachu-ex illustration spéciale rare, carte 277/217, en état Near Mint.\n\nUne illustration spéciale rare full art de la mascotte de Pokémon : une carte Pikachu au centre de toute collection. Envoyée en sleeve et toploader.\n\n- Carte 277/217\n- Illustration spéciale rare (full art)\n- Near Mint\n- Plus de [cartes Pokémon Pikachu](cards:pikachu)"],
    'mega-charizard-y-ex-hyper-rare' => ['name'=>'Méga-Dracaufeu Y-ex Hyper Rare (carte dorée)', 'desc'=>"Méga-Dracaufeu Y-ex Hyper Rare : une carte Dracaufeu dorée de la série Méga-Évolution.\n\nLes Hyper Rares sont les cartes dorées texturées en haut d’une série, et celles de Dracaufeu sont toujours parmi les plus recherchées. Near Mint, en sleeve et toploader.\n\n- Hyper Rare (dorée, texturée)\n- Near Mint\n- Prix plus bas à partir de 6 exemplaires\n- Combien vaut une telle carte ? Voir [valeur d’une carte Pokémon](guide:value)"],
    '30th-celebration-premium-deck-set-espeon-and-umbreon' => ['name'=>'Coffret premium 30th Celebration Mentali et Noctali', 'desc'=>"Le coffret premium 30th Celebration avec Mentali et Noctali (Espeon et Umbreon) : une pièce anniversaire pour collectionneurs.\n\nMentali et Noctali sont deux des évolutions d’Évoli les plus aimées, et ce coffret premium les met au centre des 30 ans de Pokémon. Vendu à l’unité, avec un prix plus bas à partir de six coffrets.\n\n- Coffret premium 30th Celebration : Mentali et Noctali\n- Objet de collection anniversaire\n- À l’unité, avec un prix plus bas à partir de 6"],
    '30th-celebration-sylveon-ex-box' => ['name'=>'Coffret 30th Celebration Nymphali-ex (Sylveon)', 'desc'=>"Un coffret collection 30th Celebration autour de Nymphali-ex, avec des boosters.\n\nLe compagnon du [coffret Amphinobi-ex](product:30th-celebration-greninja-ex-box) : un cadeau facile, un bon vendeur à l’unité pour les boutiques, et un prix plus bas à partir de 36.\n\n- Coffret collection Nymphali-ex avec boosters\n- De la gamme [30th Celebration](set:30th-celebration)\n- Par six, au prix le plus bas à partir de 36"],
    '30th-celebration-tech-sticker-collection' => ['name'=>'Collection d’autocollants 30th Celebration', 'desc'=>"La 30th Celebration Tech Sticker Collection : un petit plus abordable de la gamme anniversaire.\n\nUn article bon marché qui se vend bien en caisse et dans les paniers en ligne, à côté des boîtes 30th Celebration.\n\n- Collection d’autocollants 30th Celebration\n- Par 12, au prix le plus bas à partir de 72"],
    'pitch-black-elite-trainer-box' => ['name'=>'Coffret Dresseur d’Élite Pitch Black', 'desc'=>"Un coffret Dresseur d’Élite Pokémon Méga-Évolution Pitch Black scellé.\n\nPitch Black fait partie de la série Méga-Évolution du JCC Pokémon. Un coffret Dresseur d’Élite est la façon complète de commencer une nouvelle série : des boosters plus protège-cartes, dés, marqueurs de dégâts et une boîte de rangement. Les produits japonais liés sont dans la gamme [Abyss Eye](set:abyss-eye).\n\n- Coffret Dresseur d’Élite Méga-Évolution Pitch Black scellé\n- Boosters, protège-cartes, dés, marqueurs et boîte de rangement\n- Par quatre, au prix le plus bas à partir de 24"],
    'chaos-rising-elite-trainer-box' => ['name'=>'Coffret Dresseur d’Élite Chaos Rising', 'desc'=>"Un coffret Dresseur d’Élite Pokémon Méga-Évolution Chaos Rising scellé.\n\nChaos Rising fait partie de la série Méga-Évolution du JCC Pokémon. Un coffret Dresseur d’Élite est la façon complète de commencer une nouvelle série : des boosters plus protège-cartes, dés, marqueurs de dégâts et une boîte de rangement. Les produits japonais liés sont dans la gamme [Ninja Spinner](set:ninja-spinner).\n\n- Coffret Dresseur d’Élite Méga-Évolution Chaos Rising scellé\n- Boosters, protège-cartes, dés, marqueurs et boîte de rangement\n- Par quatre, au prix le plus bas à partir de 24"],
    'perfect-order-elite-trainer-box' => ['name'=>'Coffret Dresseur d’Élite Perfect Order', 'desc'=>"Un coffret Dresseur d’Élite Pokémon Méga-Évolution Perfect Order scellé.\n\nPerfect Order fait partie de la série Méga-Évolution du JCC Pokémon. Un coffret Dresseur d’Élite est la façon complète de commencer une nouvelle série : des boosters plus protège-cartes, dés, marqueurs de dégâts et une boîte de rangement. Les produits japonais liés sont dans la gamme [Nihil Zero](set:nihil-zero).\n\n- Coffret Dresseur d’Élite Méga-Évolution Perfect Order scellé\n- Boosters, protège-cartes, dés, marqueurs et boîte de rangement\n- Par quatre, au prix le plus bas à partir de 24"],
    'mega-evolution-ascended-heroes-elite-trainer-box' => ['name'=>'Coffret Dresseur d’Élite Méga-Évolution Ascended Heroes', 'desc'=>"Un coffret Dresseur d’Élite Pokémon Méga-Évolution Ascended Heroes scellé.\n\nAscended Heroes fait partie de la série Méga-Évolution du JCC Pokémon. Un coffret Dresseur d’Élite est la façon complète de commencer une nouvelle série : des boosters plus protège-cartes, dés, marqueurs de dégâts et une boîte de rangement. Les produits japonais liés sont dans la gamme [Mega Dream ex](set:mega-dream-ex).\n\n- Coffret Dresseur d’Élite Méga-Évolution Ascended Heroes scellé\n- Boosters, protège-cartes, dés, marqueurs et boîte de rangement\n- Par quatre, au prix le plus bas à partir de 24"],
    '30th-celebration-elite-trainer-box-case-10-ct' => ['name'=>'Carton de 10 coffrets Dresseur d’Élite 30th Celebration', 'desc'=>"Un carton scellé de 10 coffrets Dresseur d’Élite Pokémon 30th Celebration, vendu au carton.\n\nLa façon la plus économique de prendre des coffrets anniversaire : dix coffrets dans un carton scellé, avec un prix par carton qui baisse nettement à partir de six cartons. Chaque coffret contient 9 boosters, une promo full art Nidorina, 65 protège-cartes, des dés et un guide du joueur.\n\n- 10 coffrets Dresseur d’Élite 30th Celebration scellés par carton\n- Par coffret : 9 boosters, promo Nidorina, 16 Énergies holo, 65 protège-cartes, dés, pièce et boîte de collection\n- Prix par carton plus bas à partir de 6 cartons\n- Aussi vendu [par quatre](product:30th-celebration-elite-trainer-box)"],
    'mega-dream-ex-m2a-booster-box' => ['name'=>'Display Mega Dream ex (M2A) japonais', 'desc'=>"Un display japonais scellé de Mega Dream ex (M2A), la série spéciale de la gamme Méga-Évolution.\n\nMega Dream ex est la série de la [Méga-Ectoplasma-ex illustration spéciale rare](product:mega-gengar-ex-sir), l’une des cartes phares de l’ère Méga-Évolution. Ces displays sont donc très prisés des collectionneurs qui veulent la tirer eux-mêmes.\n\n- Display japonais Mega Dream ex scellé (M2A)\n- Carte phare : Méga-Ectoplasma-ex illustration spéciale rare\n- Meilleur prix par display à partir de 24\n- Toutes les [cartes Pokémon Ectoplasma](cards:gengar)"],
    'inferno-x-booster-box' => ['name'=>'Display Inferno X (M2) japonais', 'desc'=>"Un display japonais scellé d’Inferno X (M2), la deuxième série principale de la gamme Méga-Évolution.\n\nInferno X poursuit l’ère Méga-Évolution avec de nouveaux Pokémon Méga-ex et des rares full art. Les displays scellés conviennent aux joueurs qui construisent leurs decks, aux collectionneurs qui cherchent les cartes rares et aux boutiques qui se réassortent en produits japonais récents.\n\n- Display japonais scellé : Inferno X (M2)\n- De la série [Méga-Évolution](set:mega-evolution)\n- Par six, au meilleur prix à partir de 36 displays"],
    'mega-symphonia-booster-box' => ['name'=>'Display Mega Symphonia (M1S) japonais', 'desc'=>"Un display japonais scellé de Mega Symphonia (M1S), l’une des deux séries qui ont lancé la gamme Méga-Évolution en 2025.\n\nMega Symphonia et sa série jumelle [Mega Brave](product:mega-brave-booster-box) ont ramené la Méga-Évolution dans le JCC Pokémon, et beaucoup de collectionneurs achètent les deux pour compléter le lancement.\n\n- Display japonais scellé : Mega Symphonia (M1S)\n- Série jumelle de Mega Brave (M1L)\n- Par six, au meilleur prix à partir de 36 displays"],
    'mega-brave-booster-box' => ['name'=>'Display Mega Brave (M1L) japonais', 'desc'=>"Un display japonais scellé de Mega Brave (M1L), l’une des deux séries qui ont lancé la gamme Méga-Évolution en 2025.\n\nMega Brave et sa série jumelle [Mega Symphonia](product:mega-symphonia-booster-box) ont ramené la Méga-Évolution dans le JCC Pokémon, et beaucoup de collectionneurs achètent les deux pour compléter le lancement.\n\n- Display japonais scellé : Mega Brave (M1L)\n- Série jumelle de Mega Symphonia (M1S)\n- Par six, au meilleur prix à partir de 36 displays"],
    'abyss-eye-m5-booster-box' => ['name'=>'Display Abyss Eye (M5) japonais', 'desc'=>"Un display japonais scellé d’Abyss Eye (M5), de la série Méga-Évolution.\n\nAbyss Eye contient notamment [Méga-Darkrai-ex](product:mega-darkrai-ex-japanese) et [Méga-Minotaupe-ex](product:mega-excadrill-ex-japanese). Ouvrez des displays pour les obtenir, ou achetez directement les cartes à l’unité.\n\n- Display japonais scellé : Abyss Eye (M5)\n- Avec Méga-Darkrai-ex et Méga-Minotaupe-ex\n- Meilleur prix par display à partir de 24\n- [Coffret Dresseur d’Élite Abyss Eye](product:abyss-eye-elite-trainer-box) assorti"],
    'ninja-spinner-m4-booster-box' => ['name'=>'Display Ninja Spinner (M4) japonais', 'desc'=>"Un display japonais scellé de Ninja Spinner (M4) : stock limité.\n\nNinja Spinner apporte [Méga-Amphinobi-ex](product:mega-greninja-ex-japanese) et [Méga-Floette-ex](product:mega-floette-ex-japanese) à la série Méga-Évolution. Amphinobi est un favori de longue date, et il reste peu de displays scellés.\n\n- Display japonais scellé : Ninja Spinner (M4)\n- Avec Méga-Amphinobi-ex et Méga-Floette-ex\n- Meilleur prix par display à partir de 24"],
    'nihil-zero-booster-box' => ['name'=>'Display Nihil Zero (M3) japonais', 'desc'=>"Un display japonais scellé de Nihil Zero (M3), de la série Méga-Évolution.\n\nNihil Zero est une entrée abordable dans l’ère Méga-Évolution, et sa grille de prix plate en fait un ajout facile à une commande plus importante.\n\n- Display japonais scellé : Nihil Zero (M3)\n- De la série [Méga-Évolution](set:mega-evolution)\n- Par six"],
    'terastal-festival-booster-box' => ['name'=>'Display Terastal Festival ex (SV8a) japonais', 'desc'=>"Un display japonais scellé de Terastal Festival ex (SV8a), la série spéciale Écarlate et Violet autour des évolutions d’Évoli.\n\nTerastal Festival ex est consacrée aux Pokémon Téracristal et à toutes les évolutions d’Évoli, et est sortie en anglais sous le nom Prismatic Evolutions. Un display japonais contient 10 boosters de 10 cartes, et les Special Art Rares de la série, Noctali-ex en tête, sont parmi les cartes les plus recherchées de ces dernières années.\n\n- Display japonais scellé : Terastal Festival ex (SV8a), 10 boosters de 10 cartes\n- Sortie en anglais sous le nom Prismatic Evolutions\n- Par six, au prix le plus bas à partir de 36 displays"],
  ],

  'collections' => [
    ['key'=>'charizard', 'slug'=>'cartes-pokemon-dracaufeu', 'title'=>'Cartes Pokémon Dracaufeu', 'h1'=>'Cartes Pokémon Dracaufeu (Charizard) japonaises',
     'match'=>'dracaufeu, charizard', 'ids'=>[], 'cond'=>'',
     'seo_title'=>'Carte Pokémon Dracaufeu : cartes japonaises rares',
     'seo_desc'=>'Cartes Pokémon Dracaufeu japonaises : Dracaufeu-ex 151 illustration spéciale rare et Méga-Dracaufeu Y-ex Hyper Rare dorée, Near Mint.',
     'intro'=><<<'MD'
Dracaufeu (Charizard) est l’un des Pokémon les plus collectionnés du Jeu de Cartes à Collectionner, et ses cartes sont souvent les plus chères d’une série. Nos cartes Dracaufeu japonaises comprennent Dracaufeu-ex illustration spéciale rare de [151](set:151) et la Méga-Dracaufeu Y-ex Hyper Rare dorée de la série Méga-Évolution.

Chaque carte est Near Mint et part en sleeve et toploader. Pourquoi une carte Dracaufeu vaut beaucoup plus qu’une autre ? Réponse dans [valeur d’une carte Pokémon](guide:value).
MD],
    ['key'=>'pikachu', 'slug'=>'cartes-pokemon-pikachu', 'title'=>'Cartes Pokémon Pikachu', 'h1'=>'Cartes Pokémon Pikachu',
     'match'=>'pikachu', 'ids'=>[], 'cond'=>'',
     'seo_title'=>'Cartes Pokémon Pikachu : SAR, PSA 10 et protège-cartes',
     'seo_desc'=>'Cartes Pokémon Pikachu du Japon : Pikachu-ex illustrations spéciales rares, dont une gradée PSA 10 Gem Mint, plus protège-cartes et deck box Pikachu.',
     'intro'=><<<'MD'
Pikachu est le visage de Pokémon, et ses cartes plaisent à tous les collectionneurs. Nous proposons des Pikachu-ex illustrations spéciales rares, dont un exemplaire [gradé PSA 10 Gem Mint](cards:psa), plus des protège-cartes Pikachu et une deck box Ultra PRO Pikachu.

Les cartes Pikachu non gradées partent en sleeve et toploader ; les cartes gradées dans leur slab PSA.
MD],
    ['key'=>'gengar', 'slug'=>'cartes-pokemon-ectoplasma', 'title'=>'Cartes Pokémon Ectoplasma', 'h1'=>'Cartes Pokémon Ectoplasma (Gengar)',
     'match'=>'ectoplasma, gengar', 'ids'=>['mega-dream-ex-m2a-booster-box'], 'cond'=>'',
     'seo_title'=>'Carte Pokémon Ectoplasma : Méga-Ectoplasma-ex (japonaise)',
     'seo_desc'=>'La Méga-Ectoplasma-ex illustration spéciale rare japonaise de Mega Dream ex, et des displays Mega Dream ex scellés pour la tirer vous-même.',
     'intro'=><<<'MD'
Ectoplasma (Gengar) a l’un des publics les plus fidèles du JCC Pokémon, et la Méga-Ectoplasma-ex illustration spéciale rare de [Mega Dream ex](set:mega-dream-ex) est l’une des cartes phares de la série Méga-Évolution. Achetez la carte Near Mint, ou ouvrez des displays Mega Dream ex scellés pour la tirer vous-même.
MD],
    ['key'=>'psa', 'slug'=>'cartes-pokemon-gradees-psa', 'title'=>'Cartes Pokémon gradées PSA', 'h1'=>'Cartes Pokémon gradées PSA',
     'match'=>'', 'ids'=>[], 'cond'=>'Graded',
     'seo_title'=>'Cartes Pokémon gradées PSA : PSA 10 Gem Mint',
     'seo_desc'=>'Cartes Pokémon gradées PSA, dont une Pikachu-ex illustration spéciale rare PSA 10 Gem Mint, envoyées dans leur slab PSA d’origine.',
     'intro'=><<<'MD'
Une carte Pokémon gradée a été évaluée puis scellée dans un boîtier par une société de gradation. PSA note de 1 à 10 : PSA 10 (Gem Mint) est une carte quasi parfaite, PSA 9 est Mint et PSA 8 est Near Mint–Mint. Comme la note enlève le doute sur l’état, les cartes gradées, surtout en PSA 10, se vendent généralement nettement plus cher que les exemplaires non gradés.

Nos cartes gradées partent dans leur slab PSA d’origine. Pour les cartes non gradées, voir nos [cartes rares](category:singles) ; l’effet de la gradation sur la cote est expliqué dans [valeur d’une carte Pokémon](guide:value).
MD],
  ],

  'guides' => [
    ['key'=>'prices', 'slug'=>'prix-carte-pokemon', 'anchor'=>'Prix des cartes Pokémon', 'updated'=>'2026-09-27',
     'title'=>'Prix des cartes Pokémon : combien coûte une carte ?',
     'seo_title'=>'Prix carte Pokémon : vérificateur de prix (2026)',
     'seo_desc'=>'Quel est le prix d’une carte Pokémon ? Prix des boosters, displays, coffrets et cartes rares, et vérificateur avec tous nos prix actuels en euros.',
     'body'=><<<'MD'
Quel est le prix d’une carte Pokémon ? Une carte commune vaut quelques centimes, un booster quelques euros, un display des dizaines ou des centaines d’euros, et une carte rare peut monter à des centaines d’euros. Sur cette page, vous trouvez les prix de chaque type de produit et un vérificateur avec tous nos prix actuels.

## Vérificateur de prix des cartes Pokémon

Tapez une série, un Pokémon ou un type de produit. Le premier prix s’applique à partir de la quantité minimum, le second est le prix le plus bas par pièce quand vous en prenez plus.

{price_list}

## Prix des cartes Pokémon par type de produit

- **Booster :** quelques euros le paquet. Le prix d’un booster japonais est plus bas que celui d’un booster anglais ou français, mais il contient moins de cartes (5 au lieu de 10).
- **Display (boîte de boosters) :** la façon la moins chère d’acheter des boosters. Un display japonais de série principale contient 30 boosters. Voir nos [displays Pokémon japonais](category:boxes).
- **Coffret Dresseur d’Élite :** des boosters plus des accessoires. Voir nos [coffrets Dresseur d’Élite](category:etb).
- **Coffret ou boîte collection :** quelques boosters et une carte promo, idéal en cadeau. Voir nos [coffrets Pokémon](category:premium).
- **Carte rare à l’unité :** de quelques euros à plusieurs centaines d’euros selon la rareté, le Pokémon et l’état. Voir nos [cartes rares](category:singles).

## Pourquoi le prix d’une carte Pokémon varie autant

Le prix d’une carte dépend de cinq choses : sa rareté, le Pokémon (Dracaufeu, Pikachu, Noctali, Ectoplasma et Mew sont toujours demandés), son état, sa gradation éventuelle (PSA, BGS, CGC), et la série. Tout est détaillé dans notre guide [valeur d’une carte Pokémon : cote et estimation](guide:value).

## Acheter au meilleur prix

- **Prenez un display** plutôt que des boosters à l’unité si vous voulez ouvrir une série.
- **Achetez la carte à l’unité** si vous visez une carte précise : c’est presque toujours moins cher que d’ouvrir des boosters jusqu’à la tirer.
- **Profitez des remises par quantité :** chez nous, le prix par pièce baisse automatiquement quand vous en prenez plus, et la livraison est gratuite à partir de {free_ship}.

## Questions sur le prix des cartes Pokémon

### Quel est le prix d’un booster Pokémon ?

Un booster coûte quelques euros. Acheté dans un display, il revient moins cher à l’unité.

### Quelle est la carte Pokémon la plus chère ?

Les cartes les plus chères sont d’anciennes cartes promotionnelles et des cartes rares des débuts en parfait état, vendues pour des sommes énormes. Parmi les séries récentes, les illustrations spéciales rares et les cartes dorées de Pokémon populaires valent le plus.

### Les prix affichés incluent-ils la TVA ?

Nous n’ajoutons ni TVA ni autre taxe : le prix affiché, plus la livraison, est ce que vous payez.
MD],

    ['key'=>'value', 'slug'=>'valeur-carte-pokemon', 'anchor'=>'Valeur d’une carte Pokémon', 'updated'=>'2026-09-27',
     'title'=>'Valeur d’une carte Pokémon : cote et estimation',
     'seo_title'=>'Valeur carte Pokémon : cote et estimation de vos cartes',
     'seo_desc'=>'Comment connaître la valeur de vos cartes Pokémon ? Rareté, état, gradation PSA et cote : la méthode pour estimer une carte Pokémon, pas à pas.',
     'body'=><<<'MD'
Vous avez retrouvé de vieilles cartes, ou vous venez d’ouvrir un booster, et vous vous demandez ce qu’elles valent ? Voici comment estimer la valeur d’une carte Pokémon et trouver sa cote, étape par étape.

## Ce qui fait la valeur d’une carte Pokémon

1. **La rareté.** Le symbole ou les lettres en bas de la carte indiquent sa rareté. Les communes (C) et peu communes (U) valent peu ; les illustrations spéciales rares (SAR ou SIR), les Hyper Rares dorées et les Master Ultra Rares sont les cartes chères.
2. **Le Pokémon.** Dracaufeu, Pikachu, Noctali, Ectoplasma, Mew et Rayquaza sont toujours recherchés. La même rareté avec un Pokémon moins populaire vaut souvent beaucoup moins.
3. **L’état.** Une carte sans rayure, bord blanchi ni pli (Near Mint) vaut bien plus qu’une carte jouée. Regardez les bords, les coins et la surface sous une bonne lumière.
4. **La gradation.** Une carte gradée par PSA, BGS ou CGC est scellée dans un boîtier avec une note de 1 à 10. Une PSA 10 vaut souvent plusieurs fois la même carte non gradée. Voir nos [cartes Pokémon gradées PSA](cards:psa).
5. **La langue et la série.** Les cartes japonaises sortent en premier et ont leur propre marché. Certaines séries, comme [151](set:151) et [Terastal Festival ex](set:terastal-festival-ex), restent longtemps recherchées.

## Estimer une carte Pokémon : la méthode

1. **Identifiez la carte :** le numéro (par exemple 245/191) et le code de série, en bas de la carte.
2. **Cherchez les ventes réalisées**, pas les prix demandés. Un prix affiché n’est pas un prix payé. Filtrez les ventes terminées sur les sites d’enchères et consultez les sites de cote pour collectionneurs.
3. **Comparez à état et langue égaux.** Une carte japonaise se compare à des ventes japonaises, une carte gradée à la même note.
4. **Regardez plusieurs ventes.** Une vente isolée ne veut rien dire ; la moyenne des dernières semaines, si.

## Qu’est-ce que la cote d’une carte Pokémon ?

La cote est le prix auquel une carte se vend réellement en ce moment. Elle bouge avec la demande : une carte peut monter quand un Pokémon devient populaire ou quand une série n’est plus imprimée, et baisser après une réédition. Pour une estimation fiable, suivez la cote sur plusieurs semaines.

## Vos cartes valent-elles quelque chose ?

- **Cartes communes et peu communes :** en général quelques centimes.
- **Holos et rares récentes :** souvent quelques euros.
- **Illustrations spéciales rares, cartes dorées, anciennes cartes en bon état :** peuvent valoir des dizaines à des centaines d’euros.
- **Cartes rares gradées PSA 10 :** peuvent valoir beaucoup plus.

Pour comparer, notre [vérificateur de prix des cartes Pokémon](guide:prices) affiche tous nos prix actuels.

## Questions sur la valeur des cartes Pokémon

### Comment savoir si ma carte Pokémon a de la valeur ?

Regardez sa rareté, le Pokémon et son état, puis cherchez des ventes récentes de la même carte. Une carte rare d’un Pokémon populaire en état Near Mint a presque toujours de la valeur.

### Les cartes japonaises valent-elles moins que les françaises ?

Pas forcément. Les cartes japonaises ont leur propre marché, et pour certaines cartes la version japonaise vaut même plus. Voir [cartes Pokémon japonaises](guide:japanese).

### Faut-il faire grader ses cartes ?

La gradation coûte de l’argent et prend du temps. Elle vaut surtout la peine pour des cartes déjà précieuses et en excellent état.

### Comment savoir si ma carte est authentique ?

Vérifiez l’impression, les couleurs, l’épaisseur et le dos de la carte. Notre guide [vraie ou fausse carte Pokémon](guide:fakes) explique tout.
MD],

    ['key'=>'buy', 'slug'=>'acheter-cartes-pokemon', 'anchor'=>'Où acheter des cartes Pokémon', 'updated'=>'2026-09-27',
     'title'=>'Où acheter des cartes Pokémon en Belgique ?',
     'seo_title'=>'Acheter des cartes Pokémon en Belgique : magasins et en ligne',
     'seo_desc'=>'Où acheter des cartes Pokémon en Belgique ? Magasins de jouets, boutiques de cartes à proximité, salons ou en ligne : les options, les prix et les pièges à éviter.',
     'body'=><<<'MD'
Acheter des cartes Pokémon en Belgique, c’est possible à beaucoup d’endroits : en magasin de jouets, dans une boutique de cartes près de chez vous, sur un salon ou en ligne. Voici où aller, à quoi faire attention, et pourquoi de plus en plus de collectionneurs achètent des cartes japonaises.

## Où acheter des cartes Pokémon ?

### Magasins de jouets et grandes surfaces

Les grandes enseignes de jouets, les magasins multimédia et certaines grandes surfaces vendent des boosters, blisters et coffrets en français ou en anglais. Pratique pour un cadeau, mais le choix est limité et les nouvelles séries partent vite.

### Magasin de cartes Pokémon à proximité

Une boutique de cartes ou de jeux près de chez vous est le meilleur endroit pour les cartes à l’unité, les conseils et les soirées de jeu. Cherchez « magasin carte Pokémon » ou « boutique de jeux » dans votre commune : à Bruxelles, Liège, Namur, Charleroi ou Mons, il y a des boutiques qui vendent des cartes Pokémon.

### Salons et conventions

Les salons de BD, de jeux et d’anime ont souvent des stands de cartes Pokémon, y compris des cartes rares et des slabs gradés. Pratique pour voir les cartes avant d’acheter.

### En ligne

En ligne, le choix est le plus large et on peut comparer les prix. Attention : achetez chez des vendeurs avec des coordonnées claires, une politique de retour et des produits authentiques scellés. Les enseignes citées ici le sont à titre de comparaison ; nous n’y sommes pas liés.

## Acheter des cartes Pokémon japonaises

Chez {brand}, vous achetez des cartes Pokémon japonaises, directement du Japon. Pourquoi japonais ?

- **Plus tôt :** les nouvelles séries sortent d’abord au Japon, souvent des mois avant la version française. Voir [sorties Pokémon](guide:releases).
- **Moins cher par boîte :** les [displays japonais](category:boxes) sont plus petits et moins chers.
- **Qualité :** les cartes japonaises sont réputées pour leur impression.

Tout est envoyé scellé, avec suivi, et nous n’ajoutons pas de TVA. En savoir plus : [cartes Pokémon japonaises](guide:japanese).

## Vente de cartes Pokémon : à quoi faire attention ?

- **Le scellé :** un display ou un booster doit être dans son film d’origine, sans double film ni soudure décollée.
- **Le prix :** trop beau pour être vrai ? C’est souvent un faux. Comparez avec notre [vérificateur de prix](guide:prices).
- **L’authenticité :** apprenez à [reconnaître une fausse carte Pokémon](guide:fakes) avant d’acheter à l’unité.
- **Le vendeur :** vérifiez ses coordonnées, ses conditions et sa politique de retour.

## Questions sur l’achat de cartes Pokémon

### Où acheter des cartes Pokémon pas cher ?

Pour ouvrir des boosters, un display revient le moins cher par booster. Pour une carte précise, l’acheter à l’unité est presque toujours moins cher qu’ouvrir des boosters.

### Peut-on acheter des cartes Pokémon en ligne en Belgique ?

Oui. Nous livrons du Japon dans toute la Belgique, avec suivi. La livraison {standard} prend {standard_days}.

### Peut-on jouer en tournoi avec des cartes japonaises ?

Les tournois officiels ont leurs règles sur les langues autorisées. Les cartes japonaises sont surtout pour la collection et le jeu entre amis ; vérifiez toujours le règlement de l’organisateur.
MD],

    ['key'=>'japanese', 'slug'=>'cartes-pokemon-japonaises', 'anchor'=>'Cartes Pokémon japonaises', 'updated'=>'2026-09-27',
     'title'=>'Cartes Pokémon japonaises : quelles différences ?',
     'seo_title'=>'Carte Pokémon japonaise : différences, displays et achat',
     'seo_desc'=>'Pourquoi acheter des cartes Pokémon japonaises ? Sorties plus tôt, qualité d’impression, displays japonais plus petits et comment les acheter en Belgique.',
     'body'=><<<'MD'
Les cartes Pokémon japonaises sont la version originale de chaque série moderne du Jeu de Cartes à Collectionner Pokémon. Les mêmes cartes sortent ensuite en anglais et en français, mais beaucoup de collectionneurs, et pas mal de joueurs, préfèrent le japonais. Voici les différences, et ce qu’il faut savoir avant d’acheter.

## Les séries japonaises sortent en premier

Les nouvelles séries Pokémon sont conçues et sorties d’abord au Japon. Les versions anglaise et française suivent généralement des mois plus tard, et regroupent parfois deux séries japonaises, si bien que les noms ne correspondent pas toujours. La série japonaise [151](set:151) est devenue « Écarlate et Violet – 151 », et [Terastal Festival ex](set:terastal-festival-ex) est sortie en anglais sous le nom « Prismatic Evolutions ». Pour avoir les cartes les plus récentes, on achète japonais. Toutes nos séries japonaises, avec leur code, sont dans notre [liste des séries de cartes Pokémon](guide:sets).

## Même taille, boosters différents

Les cartes japonaises et françaises ont exactement la même taille : 63 × 88 mm, donc elles vont dans les mêmes [protège-cartes et albums](category:accessories). La différence est dans l’emballage :

- La plupart des displays japonais de série principale contiennent 30 boosters de 5 cartes ; un display anglais ou français, 36 boosters de 10 cartes.
- Les séries spéciales japonaises varient : un display 151 contient 20 boosters de 7 cartes, un display Terastal Festival ex 10 boosters de 10.
- Les displays japonais coûtent moins cher par boîte : on ouvre plus de séries pour le même budget.

## Display Pokémon japonais : le bon plan

Le display Pokémon japonais est le produit préféré des collectionneurs qui aiment ouvrir : plus petit, moins cher, avec les cartes rares de la série. Voir tous nos [displays Pokémon japonais](category:boxes).

## La qualité d’impression

Les cartes japonaises sont réputées pour leur impression régulière : couleurs nettes, bon centrage et peu de défauts. Pour ceux qui font grader leurs cartes, ça compte, car le centrage et les bords influencent la note.

## Peut-on lire les cartes japonaises ?

Le texte est en japonais, mais les cartes restent faciles à reconnaître : mêmes Pokémon, mêmes symboles d’Énergie et mêmes dégâts. Pour la collection, la langue ne change rien. Pour jouer, une base de données de cartes aide à traduire le texte.

## Où acheter des cartes Pokémon japonaises en Belgique ?

En magasin, on trouve surtout des produits en français et en anglais. Les cartes japonaises s’achètent en boutique spécialisée ou en ligne. Chez {brand}, vous les achetez directement du Japon : [displays](category:boxes), [coffrets Dresseur d’Élite](category:etb) et [cartes rares](category:singles), scellés et envoyés avec suivi, sans TVA. Voir aussi [où acheter des cartes Pokémon en Belgique](guide:buy).

## Questions sur les cartes Pokémon japonaises

### Les cartes Pokémon japonaises sont-elles authentiques ?

Oui, si vous les achetez chez un vendeur fiable : ce sont les cartes officielles de The Pokémon Company au Japon. Attention aux contrefaçons ; voir [vraie ou fausse carte Pokémon](guide:fakes).

### Les cartes japonaises valent-elles plus cher ?

Ça dépend de la carte. Certaines valent plus que la version française, d’autres moins. Voir [valeur d’une carte Pokémon](guide:value).

### Y a-t-il des codes JCC Pokémon Live dans les produits japonais ?

Non. Les produits japonais ne contiennent pas de cartes code pour JCC Pokémon Live ; on les trouve dans les produits en français, anglais et autres langues occidentales. Voir [code JCC Pokémon Live](guide:codes).
MD],

    ['key'=>'releases', 'slug'=>'sortie-pokemon', 'anchor'=>'Sorties Pokémon', 'updated'=>'2026-09-27',
     'title'=>'Sorties Pokémon 2026 : les nouvelles séries de cartes',
     'seo_title'=>'Sortie Pokémon 2026 : nouvelles séries et précommandes',
     'seo_desc'=>'Quelles sont les prochaines sorties de cartes Pokémon ? Les nouvelles séries japonaises de 2025 et 2026, les dates de sortie et comment précommander.',
     'body'=><<<'MD'
Chaque année, plusieurs nouvelles séries de cartes Pokémon sortent. Au Japon d’abord, puis en anglais et en français. Voici les sorties récentes, ce qui arrive, et comment précommander une nouvelle série.

## Comment fonctionnent les dates de sortie Pokémon ?

The Pokémon Company sort chaque année au Japon plusieurs séries principales, complétées par des séries spéciales (comme [151](set:151) ou [Mega Dream ex](set:mega-dream-ex)) et des produits comme des decks et des coffrets. La version française d’une série suit en général quelques mois plus tard, parfois en regroupant deux séries japonaises.

Pour les nouveautés Pokémon, il faut donc regarder d’abord le Japon.

## Nouveautés Pokémon : la série Méga-Évolution

La [série Méga-Évolution](set:mega-evolution) a commencé en 2025 et ramène la Méga-Évolution dans le JCC. Les séries japonaises à ce jour :

- [Mega Brave](set:mega-brave) (M1L) et [Mega Symphonia](set:mega-symphonia) (M1S) : les séries jumelles du lancement en 2025
- [Inferno X](set:inferno-x) (M2)
- [Mega Dream ex](set:mega-dream-ex) (M2a), la série spéciale avec Méga-Ectoplasma-ex
- [Nihil Zero](set:nihil-zero) (M3)
- [Ninja Spinner](set:ninja-spinner) (M4) avec Méga-Amphinobi-ex
- [Abyss Eye](set:abyss-eye) (M5) avec Méga-Darkrai-ex
- [Storm Emeralda](set:storm-emeralda) (M6) avec Méga-Rayquaza-ex
- [30th Celebration](set:30th-celebration) (M6a), la série anniversaire des 30 ans de Pokémon en 2026

## Prochaine sortie : Aura Seeker

[Aura Seeker (Hadou Seeker)](set:aura-seeker) est la prochaine série japonaise de la gamme Méga-Évolution, attendue en novembre 2026. Vous pouvez déjà précommander le [display Aura Seeker](product:aura-seeker-booster-box) : vous ne payez qu’à l’attribution du stock.

Les dates de sortie sont fixées par The Pokémon Company et peuvent changer.

## Comment précommander une nouvelle série ?

1. **Choisissez le produit** marqué « précommande ».
2. **Commandez comme d’habitude.** Vous réservez votre attribution au prix affiché.
3. **Vous recevez une facture** quand le stock est attribué, pas à la commande.
4. **Nous expédions** dès l’arrivée du stock, généralement à la date de sortie japonaise ou juste après.

## Les séries plus anciennes toujours recherchées

Les nouveautés ne sont pas les seules à être demandées. Les séries Écarlate et Violet comme [Glory of Team Rocket](set:glory-of-team-rocket), [Terastal Festival ex](set:terastal-festival-ex) et [151](set:151) restent populaires, et les displays scellés deviennent rares une fois la série épuisée. Toutes les séries : [liste des séries de cartes Pokémon](guide:sets).
MD],

    ['key'=>'sets', 'slug'=>'series-cartes-pokemon', 'anchor'=>'Liste des séries de cartes Pokémon', 'updated'=>'2026-09-27',
     'title'=>'Séries de cartes Pokémon : la liste des séries japonaises',
     'seo_title'=>'Série carte Pokémon : liste des séries et codes',
     'seo_desc'=>'La liste des séries de cartes Pokémon japonaises Méga-Évolution et Écarlate et Violet, avec leur code, et comment lire le numéro et la rareté d’une carte.',
     'body'=><<<'MD'
Chaque carte Pokémon appartient à une série, et chaque série a son code et son symbole. Voici toutes les séries de cartes Pokémon japonaises que nous proposons, par ère, avec leur code. Cliquez sur une série pour voir les produits et en savoir plus.

## Liste des séries chez {brand}

{set_table}

## Série, extension, bloc : quelle différence ?

Une **ère** ou un **bloc** est une grande période du JCC Pokémon, comme [Écarlate et Violet](set:scarlet-violet) ou [Méga-Évolution](set:mega-evolution). Dans chaque bloc sortent plusieurs **séries** (ou extensions), chacune avec son nom, son symbole et sa liste de cartes.

Les codes japonais commencent par le bloc : SV pour Écarlate et Violet, M pour Méga-Évolution. Une lettre en plus, comme le « a » de SV2a (151) ou M2a (Mega Dream ex), désigne souvent une série spéciale.

## Lire le numéro et la rareté d’une carte

En bas de chaque carte figure un numéro comme 245/191. Le second chiffre est la taille de la série de base ; un premier chiffre plus grand, comme 245, indique une carte secrète hors série de base. À côté du numéro : la rareté (C, U, R, RR, AR, SR, SAR, UR…) et le code de la série.

## Liste des cartes Pokémon 151

La série japonaise [151](set:151) (SV2a) compte 210 cartes : 165 dans la série de base et 45 cartes secrètes, dont 8 Special Art Rares et 3 Ultra Rares dorées. C’est l’une des séries les plus recherchées de ces dernières années.

## Pokémon Generations et anciennes séries

Les anciennes séries anglaises ou françaises, comme Generations, restent recherchées sur le marché de l’occasion. Nous nous concentrons sur les séries japonaises récentes et leurs cartes phares.

## Nouvelles séries

Quelle est la plus récente, et quelle est la prochaine ? Voir [sorties Pokémon](guide:releases).
MD],

    ['key'=>'boosters', 'slug'=>'booster-pokemon', 'anchor'=>'Booster Pokémon', 'updated'=>'2026-09-27',
     'title'=>'Booster Pokémon : packs, displays et coffrets expliqués',
     'seo_title'=>'Booster Pokémon : que contient un pack et un display ?',
     'seo_desc'=>'Que contient un booster Pokémon ? Nombre de cartes, chances de rares, boosters japonais ou français, displays et coffrets : ce qu’il faut savoir avant d’acheter.',
     'body'=><<<'MD'
Un booster Pokémon est un paquet scellé de cartes d’une même série. On ne sait pas quelles cartes il contient : c’est tout le plaisir. Voici ce qu’il y a dans un booster, dans un display, et ce qu’il vaut mieux acheter.

## Que contient un booster Pokémon ?

Un booster japonais de série principale contient généralement 5 cartes ; un booster français ou anglais, 10. Chaque booster contient au moins une carte holo ou rare, et de temps en temps une carte de rareté supérieure, comme une illustration spéciale rare ou une carte dorée. Les séries spéciales sont différentes : un booster japonais [151](set:151) contient 7 cartes.

## Pack Pokémon, display, coffret : que choisir ?

- **Booster à l’unité (pack Pokémon) :** pour le plaisir d’ouvrir, ou pour compléter.
- **[Display](category:boxes) (boîte de boosters) :** le plus de boosters pour votre argent. Idéal pour collectionner une série.
- **[Coffret Dresseur d’Élite](category:etb) :** moins de boosters, mais avec protège-cartes, dés et boîte de rangement. Parfait pour débuter ou offrir.
- **[Coffret collection](category:premium) :** quelques boosters et une carte promo. Un beau cadeau.

## Boosters collection : combien dans un display ?

- **Display japonais, série principale :** généralement 30 boosters de 5 cartes
- **Série spéciale japonaise :** par exemple 20 boosters de 7 (151) ou 10 boosters de 10 ([Terastal Festival ex](set:terastal-festival-ex))
- **Display anglais ou français :** 36 boosters de 10 cartes

## Quel display Pokémon acheter ?

- Pour les cartes les plus récentes : la dernière série [Méga-Évolution](set:mega-evolution), comme [Storm Emeralda](product:storm-emeralda-m6-booster-box).
- Pour les collectionneurs : les séries spéciales comme [151](product:151-booster-box) et [Terastal Festival ex](product:terastal-festival-booster-box).
- Pour l’anniversaire : [30th Celebration](product:30th-celebration-m6a-booster-box).

## Packs Pokémon TCG en ligne

Vous cherchez des packs Pokémon TCG en ligne ? Nos [displays japonais](category:boxes) partent scellés du Japon avec suivi, et le prix par display baisse quand vous en prenez plus.

## Questions sur les boosters Pokémon

### Combien de boosters dans un display Pokémon ?

Un display japonais de série principale contient généralement 30 boosters, un display français 36. Les séries spéciales japonaises en contiennent moins, par exemple 20 ou 10.

### Peut-on ouvrir un display et revendre les cartes ?

Oui, beaucoup de collectionneurs le font. Que ce soit rentable dépend de la série et de la chance. La valeur des cartes est expliquée dans [valeur d’une carte Pokémon](guide:value).

### Un display scellé est-il un bon investissement ?

Les displays scellés de séries populaires prennent souvent de la valeur une fois la série épuisée, mais rien n’est garanti. Achetez d’abord ce qui vous plaît.
MD],

    ['key'=>'tcg', 'slug'=>'tcg-jeu-de-cartes-a-collectionner', 'anchor'=>'TCG : le jeu de cartes à collectionner Pokémon', 'updated'=>'2026-09-27',
     'title'=>'TCG : c’est quoi ? Le jeu de cartes à collectionner Pokémon expliqué',
     'seo_title'=>'TCG : c’est quoi ? Jeu de cartes à collectionner Pokémon',
     'seo_desc'=>'TCG signifie Trading Card Game, le jeu de cartes à collectionner (JCC). Comment fonctionne le jeu Pokémon : types de cartes, tour de jeu, victoire et comment débuter.',
     'body'=><<<'MD'
TCG signifie **Trading Card Game**, en français **jeu de cartes à collectionner** (JCC). Le JCC Pokémon (Pokémon Trading Card Game) est le jeu de cartes officiel de Pokémon, joué par des millions de personnes, des enfants à la table de la cuisine jusqu’aux championnats du monde. Voici ce qu’est le jeu de cartes à collectionner Pokémon et comment y jouer.

## C’est quoi un TCG ?

Dans un TCG, on collectionne des cartes, on les échange et on construit son propre deck pour affronter un adversaire. Les nouvelles cartes viennent des [boosters](guide:boosters) : on ne sait jamais ce qu’on va obtenir. Magic: The Gathering et Yu-Gi-Oh! sont d’autres TCG connus. On écrit parfois « T C G » ou « JCC » : c’est la même chose.

## Les trois types de cartes

- **Pokémon :** vos combattants. Les Pokémon de base se jouent directement ; les évolutions se posent ensuite sur un Pokémon de base.
- **Énergie :** nécessaire pour attaquer. Chaque attaque indique l’Énergie qu’elle coûte.
- **Dresseur :** objets, supporters et stades qui vous aident : piocher, soigner, chercher dans votre deck.

## Comment jouer : les bases

1. **Un deck de 60 cartes.** Chaque joueur mélange son deck et pioche 7 cartes.
2. **Le Pokémon Actif.** Posez un Pokémon de base comme Pokémon Actif, et jusqu’à 5 sur votre Banc.
3. **Les cartes Récompense.** Posez 6 cartes face cachée : vos Récompenses.
4. **Votre tour.** Piochez, jouez des Pokémon de base, faites évoluer, attachez une Énergie, jouez des cartes Dresseur, puis attaquez.
5. **K.O.** Quand les dégâts sur un Pokémon atteignent ses PV, il est K.O. et vous prenez une Récompense (plus pour certains Pokémon ex).
6. **Gagner.** Vous gagnez quand vous avez pris toutes vos Récompenses, quand votre adversaire n’a plus de Pokémon en jeu, ou quand il ne peut plus piocher.

## Débuter le jeu Pokémon

Le plus simple est un deck prêt à jouer, comme un [Starter Set ex](product:starter-set-ex-eevee-ex) ou la [MEGA Start Deck 100 Battle Collection](product:mega-start-deck-100-battle-collection). Vous voulez aussi des boosters, des protège-cartes et des dés ? Prenez un [coffret Dresseur d’Élite](category:etb). Choisir son deck : [meilleurs decks Pokémon](guide:decks).

## JCC Pokémon Live

À côté du jeu physique existe une version numérique : JCC Pokémon Live. Tout sur les codes à échanger : [code JCC Pokémon Live](guide:codes).

## Questions sur le JCC Pokémon

### Combien de cartes dans un deck Pokémon ?

Exactement 60, avec au maximum 4 exemplaires d’une même carte (hors Énergies de base).

### À partir de quel âge ?

Le jeu est prévu à partir d’environ 6 ans. Les jeunes joueurs commencent idéalement avec un deck de démarrage prêt à jouer.

### Peut-on jouer avec des cartes japonaises ?

À la maison et entre amis, sans problème. Pour les tournois officiels, des règles fixent les langues autorisées : vérifiez-les toujours auprès de l’organisateur.
MD],

    ['key'=>'decks', 'slug'=>'meilleur-deck-pokemon', 'anchor'=>'Meilleurs decks Pokémon', 'updated'=>'2026-09-27',
     'title'=>'Meilleur deck Pokémon : choisir et construire son deck',
     'seo_title'=>'Meilleur deck Pokémon Trading Card Game : bien choisir',
     'seo_desc'=>'Quel est le meilleur deck Pokémon Trading Card Game pour vous ? Decks de démarrage, types de decks, construire un deck de 60 cartes et conseils pour progresser.',
     'body'=><<<'MD'
Quel est le meilleur deck Pokémon ? La réponse change avec chaque nouvelle série, car les decks les plus forts en tournoi évoluent sans cesse. Mais le meilleur deck pour vous dépend surtout de votre niveau et de votre façon de jouer. Voici comment choisir et construire un bon deck du Pokémon Trading Card Game.

## Pour débuter : un deck prêt à jouer

Le meilleur premier deck est un deck de démarrage : il est équilibré, légal et prêt à jouer, et il vous apprend les bases. Nos options :

- [Starter Set ex : Évoli-ex](product:starter-set-ex-eevee-ex) : simple et polyvalent
- [Starter Set ex : Zorua et Zoroark-ex](product:starter-set-ex-zorua-and-zoroark-ex) : plus tactique
- [MEGA Start Deck 100 Battle Collection](product:mega-start-deck-100-battle-collection) : des decks de l’ère Méga-Évolution

## Les grands types de decks

- **Agressif :** des attaquants rapides qui mettent la pression dès les premiers tours.
- **Contrôle :** ralentir l’adversaire, le priver de ressources et gagner sur la durée.
- **Combo / évolution :** installer un Pokémon évolué puissant, souvent un Pokémon-ex ou Méga-ex, puis enchaîner les K.O.

Commencez par un type qui vous amuse ; c’est comme ça qu’on progresse.

## Construire un deck de 60 cartes

Une base classique pour débuter :

1. **12 à 18 Pokémon**, avec une ligne d’évolution principale et un ou deux attaquants de soutien.
2. **25 à 35 cartes Dresseur**, pour piocher, chercher des Pokémon et récupérer de l’Énergie.
3. **8 à 14 Énergies**, selon le coût de vos attaques.
4. **4 exemplaires maximum** d’une même carte, hors Énergies de base.

Testez, notez ce qui manque, ajustez : un deck se construit partie après partie.

## Où trouver les cartes ?

Ouvrez des [displays](category:boxes) ou des [coffrets Dresseur d’Élite](category:etb) pour les cartes de la dernière série, et protégez votre deck avec des [protège-cartes et une deck box](category:accessories).

## Best Pokémon trading card deck : les decks du moment

Les decks qui dominent en tournoi changent à chaque série et à chaque mise à jour des règles. Pour les listes du moment, suivez les résultats des tournois officiels et des boutiques près de chez vous. Les bases de ce guide restent valables quelle que soit la série.

## Questions sur les decks Pokémon

### Quel est le meilleur deck Pokémon pour débuter ?

Un deck de démarrage prêt à jouer, comme un [Starter Set ex](product:starter-set-ex-eevee-ex). Il est équilibré et vous apprend les règles.

### Combien coûte un bon deck ?

Un deck de démarrage coûte peu. Un deck de tournoi compétitif coûte plus, car il utilise des cartes rares et plusieurs exemplaires des meilleures cartes.

### Les règles du jeu

Les bases sont expliquées dans [TCG : le jeu de cartes à collectionner Pokémon](guide:tcg).
MD],

    ['key'=>'collect', 'slug'=>'album-collection-cartes-pokemon', 'anchor'=>'Album et collection de cartes Pokémon', 'updated'=>'2026-09-27',
     'title'=>'Album carte Pokémon : bien commencer sa collection',
     'seo_title'=>'Album carte Pokémon et collection : le guide',
     'seo_desc'=>'Comment commencer une collection de cartes Pokémon et la ranger : quel album choisir, protège-cartes, toploaders, et comment organiser sa collection.',
     'body'=><<<'MD'
Une collection de cartes Pokémon commence souvent avec quelques boosters, et grandit vite. Voici comment bien la commencer, choisir un album pour cartes Pokémon et garder vos cartes en parfait état.

## Commencer une collection Pokémon

- **Choisissez un objectif :** une série complète, un Pokémon préféré (Pikachu, Dracaufeu, Évoli…), ou seulement les plus belles illustrations.
- **Ouvrez malin :** un [display](category:boxes) est la façon la moins chère d’ouvrir une série ; pour une carte précise, achetez-la [à l’unité](category:singles).
- **Gardez une liste :** notez les numéros que vous avez (par exemple 245/191) pour savoir ce qui manque. Voir notre [liste des séries](guide:sets).

## Quel album pour cartes Pokémon choisir ?

- **Album à pochettes (9 cases par page) :** le plus courant. Chaque page présente neuf cartes. Notre [album 9 cases Méga-Évolution](product:pokemon-tcg-9-pocket-binder-mega-evolution-series) en est un.
- **Album à anneaux :** pratique pour réorganiser, mais les anneaux peuvent marquer les cartes près du bord. Préférez les pochettes à chargement latéral.
- **Boîte de rangement :** idéale pour les doubles et les cartes communes. Voir notre [boîte style display](product:pokemon-tcg-card-storage-box-booster-box-display).

## Protège-cartes, toploaders et slabs

- **Protège-cartes (sleeves) :** la première protection, pour jouer comme pour ranger. Voir nos [protège-cartes](category:accessories).
- **Toploader :** un étui rigide pour les cartes de valeur.
- **Slab gradé :** une carte gradée par PSA est scellée dans un boîtier. Voir nos [cartes gradées PSA](cards:psa).

## Carte et album Pokémon : les erreurs à éviter

- Ne pas mettre deux cartes dans la même pochette.
- Éviter le soleil direct et l’humidité : ils décolorent et gondolent les cartes.
- Ne jamais utiliser d’élastiques ou de trombones.
- Ranger l’album debout, comme un livre, pas à plat sous une pile.

## Que vaut votre collection ?

Pour estimer vos cartes, lisez [valeur d’une carte Pokémon : cote et estimation](guide:value), et comparez avec notre [vérificateur de prix](guide:prices).
MD],

    ['key'=>'fakes', 'slug'=>'fausse-carte-pokemon', 'anchor'=>'Vraie ou fausse carte Pokémon', 'updated'=>'2026-09-27',
     'title'=>'Vraie ou fausse carte Pokémon : 8 vérifications, du dos à l’impression',
     'seo_title'=>'Fausse carte Pokémon : reconnaître une vraie (dos, impression)',
     'seo_desc'=>'Comment reconnaître une fausse carte Pokémon ? Le dos de la carte, l’impression, l’épaisseur, la police, les couleurs et le scellé des boosters : 8 vérifications simples.',
     'body'=><<<'MD'
Les fausses cartes Pokémon sont partout : sur les sites de revente, sur les marchés et dans les « mystery box » bon marché. Avec ces huit vérifications, vous repérez la plupart des contrefaçons en quelques secondes.

## Le dos de la carte Pokémon

Commencez toujours par le dos. Comparez avec une carte dont vous êtes sûr. Sur une fausse carte, le bleu est souvent trop clair ou trop violet, et les tourbillons sont flous. Attention : le dos des cartes japonaises est différent de celui des cartes françaises et anglaises, donc comparez japonais avec japonais.

## 8 vérifications pour reconnaître une fausse carte

1. **Le dos.** Couleurs, netteté et motif, comme ci-dessus.
2. **Les couleurs et l’impression.** Une vraie carte est imprimée net. Un texte flou, des couleurs baveuses ou une brillance sur toute la carte trahissent une contrefaçon.
3. **La police.** Les fausses utilisent souvent une police légèrement différente, avec des espaces bizarres, des fautes ou des accents manquants.
4. **L’épaisseur et la rigidité.** Une vraie carte est ferme et reprend sa forme. Une fausse est souvent trop fine, trop lisse ou trop souple.
5. **La tranche.** Regardez le bord de la carte : les vraies cartes françaises et anglaises ont souvent une fine couche sombre au milieu. Une fausse est blanche de part en part.
6. **L’holographie.** Les vraies holos ont un motif propre à chaque rareté. Un effet arc-en-ciel sur toute la carte, texte compris, est suspect.
7. **Les chiffres.** PV, dégâts et numéro doivent correspondre à la carte officielle. Des valeurs absurdes (PV 1000) sont toujours fausses.
8. **Le scellé.** Boosters et displays doivent être scellés proprement. Un double film, des traces de colle ou une soudure décollée peuvent signaler un booster rescellé.

## Les boosters rescellés

Ce ne sont pas que les cartes qui sont contrefaites : certains boosters sont ouverts, vidés de leurs cartes rares puis recollés. Achetez les produits scellés chez un vendeur fiable. Chez {brand}, tout vient de la distribution japonaise et part tel qu’il est sorti de l’usine.

## Un doute sur une carte chère ?

Faites-la grader par PSA, BGS ou CGC : ils vérifient l’authenticité avant de donner une note. Une carte gradée est scellée dans un boîtier avec un numéro de certificat vérifiable en ligne. Voir nos [cartes Pokémon gradées PSA](cards:psa).

## Questions sur les fausses cartes Pokémon

### Une carte pas chère est-elle forcément fausse ?

Non, mais un prix beaucoup trop bas est un signal d’alerte. Comparez avec notre [vérificateur de prix](guide:prices).

### Les cartes japonaises sont-elles plus souvent contrefaites ?

Il existe des contrefaçons dans toutes les langues. Le plus sûr est d’acheter les cartes japonaises chez un vendeur qui s’approvisionne directement au Japon. Voir [cartes Pokémon japonaises](guide:japanese).
MD],

    ['key'=>'template', 'slug'=>'creer-sa-carte-pokemon', 'anchor'=>'Créer sa carte Pokémon (modèle gratuit)', 'updated'=>'2026-09-27',
     'title'=>'Créer sa carte Pokémon : modèle gratuit à imprimer',
     'seo_title'=>'Créer sa carte Pokémon : modèle gratuit (card creator)',
     'seo_desc'=>'Créez votre propre carte Pokémon avec notre modèle gratuit au vrai format (63 × 88 mm), avec fond perdu et zone de sécurité. Conseils de design et d’impression.',
     'body'=><<<'MD'
Créer sa propre carte Pokémon est un chouette projet, pour un anniversaire, une classe ou juste pour soi. Avec notre modèle gratuit, vous avez tout de suite le bon format : c’est votre « card creator » à imprimer.

## Modèle gratuit

{card_template}

## Le format d’une carte Pokémon

Une carte Pokémon mesure **63 × 88 mm**, avec des coins arrondis. Les cartes japonaises et françaises ont la même taille, donc votre carte ira dans les mêmes protège-cartes et albums.

- **Fond perdu :** 3 mm tout autour, pour ne pas avoir de bord blanc après la découpe
- **Zone de sécurité :** gardez le texte et les détails importants quelques millimètres à l’intérieur du trait de coupe

## Le design d’une carte Pokémon

Une carte Pokémon suit toujours la même mise en page (le « UI design » de la carte) : le nom et les PV en haut, l’illustration dans un cadre, puis les attaques avec leur coût en Énergie et leurs dégâts, et en bas la faiblesse, la résistance et le coût de retraite. Reprenez cette structure et votre carte sera tout de suite reconnaissable.

## Créer sa carte pas à pas

1. **Téléchargez le modèle** et ouvrez-le dans un logiciel de dessin ou de mise en page qui lit le SVG.
2. **Choisissez votre illustration** et placez-la dans le cadre du haut.
3. **Ajoutez le texte :** nom, PV, type, attaques avec leur coût en Énergie et leurs dégâts.
4. **Imprimez à 100 %** (« taille réelle »), pas « ajuster à la page », sur du papier épais ou du carton.
5. **Découpez le long du trait de coupe** et arrondissez les coins.
6. **Glissez-la dans un protège-carte** pour la sensation d’une vraie carte. Voir nos [protège-cartes](category:accessories).

## Est-ce autorisé ?

Créer une carte pour son usage personnel est un projet créatif amusant. Ne vendez pas de cartes qui imitent de vraies cartes Pokémon et ne les utilisez pas en tournoi officiel : Pokémon et le design des cartes sont protégés.

## Voir de vraies cartes

Pour savoir à quoi sert chaque partie d’une vraie carte, lisez [le jeu de cartes à collectionner Pokémon](guide:tcg), ou comparez avec de vraies cartes grâce à [vraie ou fausse carte Pokémon](guide:fakes).
MD],

    ['key'=>'codes', 'slug'=>'code-jcc-pokemon-live', 'anchor'=>'Code JCC Pokémon Live', 'updated'=>'2026-09-27',
     'title'=>'Code JCC Pokémon Live : où le trouver et comment l’échanger',
     'seo_title'=>'Code JCC Pokémon Live (online) : trouver et échanger',
     'seo_desc'=>'Où trouver un code JCC Pokémon Live et comment l’échanger dans l’application. Quels produits contiennent des codes, et pourquoi les produits japonais n’en ont pas.',
     'body'=><<<'MD'
Le JCC Pokémon Live est la version numérique du jeu de cartes à collectionner Pokémon. Beaucoup de produits physiques contiennent une carte code qui débloque du contenu dans le jeu. Voici où trouver ces codes et comment les échanger.

## Qu’est-ce qu’un code JCC Pokémon Live ?

C’est une petite carte avec un code (et souvent un QR code) glissée dans certains produits : boosters, coffrets Dresseur d’Élite, coffrets collection. En l’échangeant, vous recevez dans le jeu le contenu du produit : boosters virtuels, cartes promo ou objets.

## Quels produits contiennent des codes ?

- **Produits en français, anglais et autres langues occidentales :** la plupart des boosters et coffrets contiennent une carte code.
- **Produits japonais :** ils ne contiennent pas de code pour JCC Pokémon Live. Le jeu numérique ne fait pas partie de la gamme japonaise.

Si vous achetez des [displays japonais](category:boxes) pour les cartes, vous n’aurez donc pas de codes : les cartes, elles, sortent plus tôt et sont très recherchées. Voir [cartes Pokémon japonaises](guide:japanese).

## Comment échanger un code (pokemon.fr/echanger)

Beaucoup de joueurs cherchent « pokemon.fr/echanger » pour échanger leurs codes. Aujourd’hui, l’échange se fait directement dans l’application JCC Pokémon Live :

1. **Ouvrez l’application** JCC Pokémon Live et connectez-vous à votre compte Dresseur.
2. **Allez dans la boutique** et choisissez l’option d’échange de code.
3. **Scannez le QR code** de la carte avec l’appareil photo, ou tapez le code à la main.
4. **Récupérez votre contenu** dans le jeu.

Un code ne s’utilise qu’une fois. Gardez les cartes code à l’abri des regards tant qu’elles ne sont pas échangées.

## Codes en ligne : attention aux arnaques

Méfiez-vous des sites qui promettent des codes gratuits ou qui demandent vos identifiants : ne donnez jamais votre mot de passe. Les codes ne s’obtiennent qu’avec des produits officiels.

## Jouer au JCC Pokémon

Envie de jouer avec de vraies cartes ? Lisez [TCG : le jeu de cartes à collectionner Pokémon](guide:tcg) et choisissez un [meilleur deck Pokémon](guide:decks) pour débuter.
MD],
  ],

  'pages' => [
    ['key'=>'about', 'slug'=>'a-propos', 'title'=>'À propos de {brand}',
     'seo_title'=>'À propos de {brand} : cartes Pokémon japonaises pour la Belgique',
     'seo_desc'=>'{brand} est un revendeur indépendant de cartes Pokémon japonaises authentiques, avec livraison du Japon vers la Belgique.',
     'body'=><<<'MD'
{brand} est un revendeur indépendant de produits authentiques du Jeu de Cartes à Collectionner Pokémon japonais. Nous vendons des displays scellés, des coffrets Dresseur d’Élite, des coffrets collection, des cartes rares à l’unité et des accessoires, envoyés directement du Japon dans toute la Belgique.

## Notre nom

{brand} ({kanji}) associe deux mots japonais : **fuda** (札), une carte, et **kura** (蔵), un entrepôt. Un entrepôt de cartes à collectionner japonaises.

## Pour qui ?

Pour les collectionneurs, les joueurs et les parents qui cherchent un cadeau, ainsi que pour les boutiques de cartes, les vendeurs en ligne et les organisateurs de tournois qui achètent en quantité. Voir nos [conditions pour grossistes](page:wholesale).

## Ce que nous vendons

- Des [displays japonais](category:boxes) scellés, des séries anniversaire comme [30th Celebration](set:30th-celebration) aux incontournables comme [Pokémon Card 151](set:151)
- Des [coffrets Dresseur d’Élite](category:etb) et des [coffrets Pokémon](category:premium)
- Des [cartes rares à l’unité](category:singles), dont des [cartes gradées PSA](cards:psa)
- Des [accessoires](category:accessories) : protège-cartes, albums, deck box et tapis de jeu

## Notre façon de travailler

- **Tous les prix affichés.** Pas de compte, pas de demande : le prix par quantité est sur chaque fiche produit.
- **Acheté au Japon.** Tout vient de la distribution japonaise et part scellé dans son emballage d’origine. Nous ne vendons jamais de produits rescellés, réimprimés ou contrefaits.
- **Livré en Belgique.** Chaque commande part du Japon avec suivi, en euros, sans TVA. Voir [Livraison et retours](page:shipping).
- **De vraies réponses.** Vos questions arrivent à une vraie personne à [{email}](mailto:{email}), en français, néerlandais ou anglais.

## Indépendant

{company} est une entreprise indépendante. Nous ne sommes ni affiliés, ni approuvés, ni sous licence de The Pokémon Company, Nintendo, Creatures Inc. ou GAME FREAK Inc.

## Coordonnées

{company} · {address} · [{email}](mailto:{email})
MD],
    ['key'=>'wholesale', 'slug'=>'grossiste-cartes-pokemon', 'title'=>'Grossiste cartes Pokémon pour boutiques et revendeurs',
     'seo_title'=>'Grossiste cartes Pokémon Belgique : JCC japonais',
     'seo_desc'=>'Cartes Pokémon japonaises en gros pour boutiques de cartes, vendeurs en ligne et organisateurs de tournois en Belgique : quantités minimum, prix dégressifs, sans demande.',
     'body'=><<<'MD'
{brand} fournit des produits authentiques du JCC Pokémon japonais aux boutiques et revendeurs de toute la Belgique : displays scellés, coffrets Dresseur d’Élite, coffrets collection, cartes à l’unité et accessoires, envoyés directement du Japon.

## Grossiste sans demande de compte

Chez la plupart des grossistes, il faut demander un compte, attendre la validation puis réclamer une liste de prix. Pas chez nous. Chaque fiche produit affiche les conditions :

- **Quantité minimum :** les produits scellés commencent à une quantité adaptée à l’emballage, généralement 4 ou 6 boîtes ; les cartes à l’unité dès une.
- **Multiples :** vous commandez par unité d’emballage, pour que chaque commande corresponde au conditionnement.
- **Remises automatiques :** le prix par pièce baisse à chaque palier, comme 6+, 24+ ou 36+, et s’applique tout seul.
- **Commande minimum :** {min_order} livraison comprise.

Vérifiez chaque prix avec notre [vérificateur de prix](guide:prices).

## Pour qui ?

- **Boutiques de cartes et de jeux :** displays japonais scellés pour les rayons et les soirées d’ouverture, cartes à l’unité pour la vitrine.
- **Vendeurs en ligne :** cartons des nouvelles séries japonaises dès la sortie, avec suivi sur chaque envoi.
- **Organisateurs de tournois :** displays pour les lots, coffrets et accessoires pour les événements.

## Paiement et facture

Vous payez en Bitcoin sur la page de commande, ou en ETH ou USDT sur facture. Nous n’ajoutons pas de TVA : le total est ce que vous payez. Indiquez votre numéro d’entreprise dans les remarques de la commande et nous le mettons sur votre facture.

## Grosses commandes et précommandes

Cartons complets, commandes régulières ou attribution sur une prochaine sortie ? Écrivez à [{email}](mailto:{email}) avec les séries et quantités, et nous revenons vers vous avec un prix.
MD],
    ['key'=>'terms', 'slug'=>'conditions-generales-de-vente', 'title'=>'Conditions générales de vente',
     'seo_title'=>'Conditions générales de vente | {brand}',
     'seo_desc'=>'Les conditions de vente de {brand} : prix en euros sans TVA, commande, paiement en crypto, livraison en Belgique, droit de rétractation de 14 jours et garantie.',
     'body'=><<<'MD'
Ces conditions s’appliquent à toute commande chez {brand}. En commandant, vous les acceptez. Si vous êtes consommateur, vos droits légaux priment toujours.

## 1. Qui sommes-nous

{company}, {address}. E-mail : [{email}](mailto:{email}).

## 2. Produits et prix

Nous vendons des produits authentiques du Jeu de Cartes à Collectionner Pokémon japonais. Les prix sont en euros. Nous n’ajoutons ni TVA ni autre taxe : le total à la commande, livraison comprise, est ce que vous nous payez. Comme pour tout colis venant de l’extérieur de l’UE, le transporteur peut demander des frais d’importation pour certaines commandes ; ils sont payés au transporteur.

Les images sont illustratives. Une erreur manifeste de prix ou de description ne nous engage pas ; nous vous contactons avant de facturer quoi que ce soit.

## 3. Commande

Votre commande est une offre d’achat. Vous recevez une confirmation par e-mail ; la vente est conclue quand nous acceptons votre paiement. La commande minimum est de {min_order} livraison comprise. Les produits scellés se vendent par unité d’emballage, comme indiqué sur chaque fiche produit.

## 4. Paiement

Vous payez en Bitcoin, sur votre page de commande, ou en ETH ou USDT à l’adresse de portefeuille que nous vous envoyons par e-mail. Vos articles restent réservés {hold_hours} heures. Si le paiement n’arrive pas dans ce délai, nous pouvons libérer la réservation.

## 5. Livraison

Nous expédions dans les {hold_hours} heures après paiement depuis le Japon, avec suivi. Les délais indiqués sur [Livraison et retours](page:shipping) sont des estimations. Le risque vous est transféré à la livraison, à vous ou à la personne que vous désignez.

## 6. Droit de rétractation

Si vous êtes consommateur, vous pouvez renoncer à l’achat sans motif jusqu’à **14 jours après la livraison**. Prévenez-nous par e-mail à [{email}](mailto:{email}) avec votre numéro de commande ; vous pouvez utiliser le formulaire type européen, mais ce n’est pas obligatoire. Renvoyez les articles dans les 14 jours suivant votre demande ; les frais de retour sont à votre charge.

Nous remboursons toutes les sommes payées, y compris les frais de livraison standard d’origine, dans les 14 jours suivant votre demande, par le même moyen de paiement (pour la crypto : vers une adresse de portefeuille que vous confirmez, au cours du jour). Nous pouvons attendre le retour des articles.

Vous êtes responsable de la dépréciation résultant de manipulations autres que celles nécessaires pour examiner le produit. Ouvrir une boîte ou un booster scellé en fait partie : pour les produits ouverts ou dont le scellé est abîmé, nous pouvons retenir la dépréciation.

Les acheteurs professionnels n’ont pas de droit de rétractation.

## 7. Garantie

Les consommateurs bénéficient de la garantie légale de 2 ans pour les défauts présents à la livraison. Signalez un défaut dès que possible ; les dommages de transport dans les 7 jours suivant la livraison, avec photos. Voir [Livraison et retours](page:shipping).

## 8. Précommandes

Les précommandes sont facturées à l’attribution du stock. Les dates de sortie sont fixées par l’éditeur et peuvent changer. Si nous ne pouvons pas honorer une précommande, nous la remboursons entièrement.

## 9. Réclamations

Écrivez à [{email}](mailto:{email}) ; nous répondons dans les {reply_hours} heures. Si nous ne trouvons pas de solution ensemble, vous pouvez, en tant que consommateur, vous adresser au Service de Médiation pour le Consommateur (mediationconsommateur.be).

## 10. Droit applicable

Ces conditions sont soumises au droit désigné par les règles de droit international privé. En tant que consommateur en Belgique, vous gardez toujours la protection des règles impératives du droit belge.

## 11. Indépendant

{company} n’est pas affilié à The Pokémon Company, Nintendo, Creatures Inc. ou GAME FREAK Inc. Tous les noms de produits et marques appartiennent à leurs propriétaires.
MD],
    ['key'=>'privacy', 'slug'=>'politique-de-confidentialite', 'title'=>'Politique de confidentialité',
     'seo_title'=>'Politique de confidentialité | {brand}',
     'seo_desc'=>'Quelles données {brand} collecte lors d’une commande, pourquoi, combien de temps nous les gardons et quels sont vos droits selon le RGPD.',
     'body'=><<<'MD'
Nous utilisons vos données uniquement pour traiter votre commande et répondre à vos questions. Voici ce que nous gardons et pourquoi.

## Responsable

{company}, {address}, [{email}](mailto:{email}).

## Quelles données

- **Données de commande :** nom, entreprise, e-mail, téléphone, adresse de livraison, articles commandés, remarques et moyen de paiement.
- **Paiement en Bitcoin :** le montant et la transaction sur la blockchain publique. Nous n’avons jamais accès aux clés de votre portefeuille.
- **Données techniques :** votre adresse IP lors d’une commande (contre les abus) et un cookie de session pour votre panier et votre langue.
- **Chat :** si vous utilisez le chat en direct, le service de chat (Tawk.to) traite vos messages.

## Pourquoi

Pour exécuter votre commande (contrat), prévenir la fraude (intérêt légitime) et respecter nos obligations légales de conservation.

## Avec qui nous partageons

Uniquement avec ceux qui en ont besoin pour livrer votre commande : le transporteur et la douane (nom, adresse, téléphone, contenu et valeur), notre hébergeur et notre service d’e-mail, et le service de chat si vous l’utilisez. Pour vérifier les paiements Bitcoin, nous consultons des services publics de blockchain (mempool.space, blockstream.info) sans partager de données personnelles. Nous ne vendons jamais vos données.

Votre commande est traitée et expédiée depuis le Japon ; le Japon bénéficie d’une décision d’adéquation de la Commission européenne.

## Combien de temps

Nous gardons les données de commande aussi longtemps que la loi l’exige pour notre comptabilité, et pas plus que nécessaire.

## Vos droits

Vous pouvez accéder à vos données, les rectifier ou les faire effacer, vous opposer à leur traitement et les recevoir dans un format lisible. Écrivez à [{email}](mailto:{email}). En cas d’insatisfaction, vous pouvez porter plainte auprès de l’Autorité de protection des données (autoriteprotectiondonnees.be).

## Cookies

Nous n’utilisons qu’un cookie de session nécessaire. Si vous utilisez le chat, le service de chat place ses propres cookies.
MD],
  ],

  'faqs' => [
    ['Livrez-vous des cartes Pokémon en Belgique ?', 'Oui. Tout part du Japon et est livré avec suivi dans toute la Belgique. Choisissez à la commande {standard} ({standard_days}) ou {express} ({express_days}) ; la livraison est calculée selon le poids de votre commande.'],
    ['Vos cartes Pokémon sont-elles authentiques ?', 'Oui. Tout vient de la distribution japonaise et part scellé dans son emballage d’origine. Nous ne vendons jamais de produits rescellés, réimprimés ou contrefaits.'],
    ['Ajoutez-vous la TVA ?', 'Non. Nous n’ajoutons ni TVA ni autre taxe : le total à la commande, livraison comprise, est ce que vous nous payez. Comme pour tout colis venant de l’extérieur de l’UE, le transporteur peut demander des frais d’importation pour certaines commandes ; ils sont payés au transporteur.'],
    ['Comment payer ?', 'En crypto. Le Bitcoin se paie tout de suite sur votre page de commande : scannez le QR code ou copiez le montant et l’adresse dans votre portefeuille. Pour l’ETH ou l’USDT, nous vous envoyons notre adresse de portefeuille avec votre facture dans les {reply_hours} heures.'],
    ['Quelle est la commande minimum ?', 'Chaque commande doit atteindre {min_order} livraison comprise. Les produits scellés se vendent par unité d’emballage (généralement par quatre ou six) ; les cartes à l’unité dès une.'],
    ['La livraison est-elle gratuite ?', 'Oui, à partir de {free_ship} d’articles, la livraison {standard} est gratuite, automatiquement. En {express}, vous ne payez que la différence.'],
    ['Faut-il créer un compte ?', 'Non. Chaque fiche produit affiche la quantité minimum et le prix par quantité. Vous commandez sans compte ni demande.'],
    ['Pourquoi des cartes Pokémon japonaises ?', 'Les séries japonaises sortent des mois avant les françaises, les displays japonais sont moins chers par boîte, et la qualité d’impression est excellente. Les cartes japonaises et françaises ont la même taille (63 × 88 mm).'],
    ['Puis-je renvoyer ma commande ?', 'Oui. En tant que consommateur, vous avez 14 jours de rétractation après la livraison. Prévenez-nous par e-mail et renvoyez les articles ; pour les boîtes ou boosters ouverts, nous pouvons retenir la dépréciation. Tous les détails sont sur notre page Livraison et retours.'],
    ['Puis-je précommander une nouvelle série ?', 'Oui. Les précommandes réservent votre attribution avant la sortie aux prix affichés, et ne sont facturées qu’à l’attribution du stock.'],
    ['Quand ma commande part-elle ?', 'Vos articles restent réservés {hold_hours} heures. Dès réception du paiement, nous expédions dans les {hold_hours} heures depuis le Japon et vous envoyons le numéro de suivi.'],
    ['Et si un article arrive abîmé ou erroné ?', 'Signalez les dommages de transport, articles manquants ou erronés dans les 7 jours suivant la livraison, avec photos. Nous remplaçons ou remboursons, livraison comprise.'],
    ['Répondez-vous en français ?', 'Oui. Écrivez à {email} en français, néerlandais ou anglais ; nous répondons dans les {reply_hours} heures.'],
  ],
];
