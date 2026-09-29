<?php
/* Starting data for a fresh install. On first load it is copied to data/store.php,
   and from then on everything is edited in admin.php — not here.
   Category, series, set, collection and guide text lives in content.php. */
if(!defined('FK_ROOT')){ http_response_code(404); exit; }

/* one product: prices (ladder) are [min_qty, unit_price_usd], shown in euros at the EUR rate below */
$P = fn($id, $sku, $name, $set, $cat, $cond, $status, $weight, $ladder, $desc, $release='') => [
  'id'=>$id, 'sku'=>$sku, 'name'=>$name, 'set'=>$set, 'cat'=>$cat, 'cond'=>$cond, 'moq'=>1, 'step'=>1,
  'status'=>$status, 'release'=>$release, 'weight'=>$weight, 'hidden'=>false, 'ladder'=>$ladder, 'desc'=>$desc];

$d = [
  'settings' => [
    'brand'         => 'FUDAKURA',
    'kanji'         => '札蔵',
    'tagline'       => 'Cartes Pokémon japonaises expédiées du Japon en France',
    'legal_name'    => 'Fudakura',
    'address'       => 'Japon',
    'email'         => 'support@fudakura-france.com',
    'phone'         => '',
    'order_email'   => 'support@fudakura-france.com',
    'domain'        => 'https://fudakura-france.com',
    'reply_hours'   => 12,
    'hold_hours'    => 48,
    'min_order_usd' => 58.14,          /* 50 € at the 0.86 rate; only shown at checkout; set in Admin → Settings */
    'strip_link_text' => 'Précommandes Aura Seeker ouvertes →',
    'strip_link_url'  => 'index.php?p=product&id=display-aura-seeker-japonais',
    'shipping_reviewed' => false,
    'pretty_urls'   => false,            /* clean addresses like /produits/display-pokemon-151-japonais; turn on in Admin → Settings */
    'free_ship_usd' => 290.70,           /* free Standard shipping from this goods total (USD, 250 € at 0.86); 0 = off */
    'content_version' => 11,             /* set by the shop: which built-in content updates are applied */
    'moved_to'      => '',               /* an old domain running this code can send every visitor here (301) */
    'google_verify' => '', 'bing_verify' => '',   /* Search Console / Bing verification codes (Admin → Settings) */
    /* SMTP for order emails (Admin → Settings → Email); blank host = the web host's mail() */
    'smtp_host' => '', 'smtp_port' => 465, 'smtp_secure' => 'ssl', 'smtp_user' => '', 'smtp_pass' => '',

    /* Bitcoin: orders paid with a "Bitcoin" payment method are paid to this address from the order page */
    'btc_address'       => 'bc1qhvgghjapwsugh4nnckh0skqar7jcg5xmu36jpl',
    'btc_quote_minutes' => 60,           /* how long a BTC amount is held before it is renewed at the current price */
    'btc_confirmations' => 1,            /* blockchain confirmations before an order is marked Paid */

    /* live chat (Tawk.to): loaded after the page has finished loading, so it never slows the shop down */
    'chat_code' => <<<'HTML'
<!--Start of Tawk.to Script-->
<script type="text/javascript">
var Tawk_API=Tawk_API||{}, Tawk_LoadStart=new Date();
(function(){
var s1=document.createElement("script"),s0=document.getElementsByTagName("script")[0];
s1.async=true;
s1.src='https://embed.tawk.to/6abc1ee9ed6c2d3444240d99/default';
s1.charset='UTF-8';
s1.setAttribute('crossorigin','*');
s0.parentNode.insertBefore(s1,s0);
})();
</script>
<!--End of Tawk.to Script-->
HTML,
  ],

  /* prices are entered in USD in the admin and shown in these currencies; the first is the one shoppers see.
     With only EUR here, the shop is euro-only and shows no currency switch. Keep the rate up to date. */
  'currencies' => [
    'EUR' => ['rate'=>0.86, 'sym'=>'€', 'dec'=>2],
  ],

  /* status: in | new | low | preorder | soldout.  cond: Sealed | Graded | Near Mint | Lightly Played.
     ladder: [min_qty, unit_price_usd] ascending.  weight: kg per unit (used for shipping) */
  'products' => [
    $P('display-pokemon-151-japonais', 'FK-BB-SV2A-01', 'Display Pokémon 151 (SV2a) japonais', '151', 'boxes', 'Sealed', 'in', 0.4,
      [[1,445.71],[6,425.4],[36,380.9]], <<<'MD'
Un display japonais 151 (SV2a) scellé d’usine : 20 boosters de 7 cartes de l’extension qui revisite les 151 Pokémon d’origine.

Les cartes Pokémon 151 comptent parmi les plus collectionnées de l’ère Écarlate et Violet : les Pokémon originaux, de Bulbizarre à Mew, dans des illustrations modernes avec des cartes full art. La carte phare est [Dracaufeu-ex illustration spéciale rare](product:carte-dracaufeu-ex-sar-151-japonaise). 151 est sortie ensuite en français, mais la version japonaise est arrivée la première et de nombreux collectionneurs la préfèrent.

- Display japonais 151 (SV2a) scellé : 20 boosters de 7 cartes
- Carte phare : Dracaufeu-ex illustration spéciale rare (SAR)
- Prix dégressif dès 6 displays
- Première fois avec des cartes japonaises ? Lisez notre guide des [cartes Pokémon japonaises](guide:cartes-pokemon-japonaises)
MD),
    $P('display-inferno-x-flammes-fantasmagoriques-japonais', 'FK-BB-INFX-01', 'Display Inferno X (M2) japonais — Flammes Fantasmagoriques', 'Inferno X', 'boxes', 'Sealed', 'in', 0.4,
      [[1,217.1],[6,207.2],[36,185.5]], <<<'MD'
Un display japonais Inferno X (M2) scellé d’usine, l’extension japonaise sortie en français sous le nom de Flammes Fantasmagoriques.

Inferno X est la deuxième grande extension de la série Méga-Évolution, emmenée par Méga-Dracaufeu X-ex (Méga-Lizardon X en japonais). Le display japonais contient 30 boosters de 5 cartes et sort plusieurs semaines avant la version française.

- Display japonais Inferno X (M2) scellé : 30 boosters de 5 cartes
- Version française : Flammes Fantasmagoriques
- Carte phare : Méga-Dracaufeu X-ex
- Toutes nos [cartes Dracaufeu](cards:dracaufeu)
MD),
    $P('display-terastal-festival-ex-evolutions-prismatiques-japonais', 'FK-BB-SV8A-01', 'Display Terastal Festival ex (SV8a) japonais — Évolutions Prismatiques', 'Terastal Festival ex', 'boxes', 'Sealed', 'in', 0.4,
      [[1,88.2],[6,85.1],[36,78.4]], <<<'MD'
Un display japonais Terastal Festival ex (SV8a) scellé d’usine : l’extension spéciale d’Évoli et de ses évolutions, sortie en français sous le nom d’Évolutions Prismatiques.

Terastal Festival ex met à l’honneur les Pokémon Téracristal et toutes les évolutions d’Évoli. Contrairement à la version française, vendue en coffrets, la version japonaise existe en display de 10 boosters de 10 cartes. Noctali-ex en illustration spéciale rare est l’une des cartes les plus recherchées de ces dernières années.

- Display japonais Terastal Festival ex (SV8a) scellé : 10 boosters de 10 cartes
- Version française : Évolutions Prismatiques
- Carte phare : Noctali-ex illustration spéciale rare
MD),
    $P('display-glory-of-team-rocket-sv10-japonais', 'FK-BB-SV10-01', 'Display Glory of Team Rocket (SV10) japonais — Rivalités Destinées', 'Glory of Team Rocket', 'boxes', 'Sealed', 'in', 0.4,
      [[1,261.4],[6,249.5],[18,235],[36,223.4]], <<<'MD'
Un display japonais Glory of Team Rocket (SV10) scellé d’usine : les Pokémon de la Team Rocket font leur retour dans le JCC.

Glory of Team Rocket est l’une des extensions japonaises Écarlate et Violet les plus demandées. Avec Heat Wave Arena, elle a donné l’extension française Rivalités Destinées, dont la carte phare est Mewtwo-ex de la Team Rocket.

- Display japonais Glory of Team Rocket (SV10) scellé : 30 boosters de 5 cartes
- Version française : Rivalités Destinées (avec Heat Wave Arena)
- Série [Écarlate et Violet](set:ecarlate-et-violet)
MD),
    $P('display-heat-wave-arena-sv9a-japonais', 'FK-BB-SV9A-01', 'Display Heat Wave Arena (SV9a) japonais', 'Heat Wave Arena', 'boxes', 'Sealed', 'in', 0.4,
      [[1,207.11],[6,197.7],[18,186],[36,177]], <<<'MD'
Un display japonais Heat Wave Arena (SV9a) scellé d’usine, de la série Écarlate et Violet.

Heat Wave Arena fait partie, avec [Glory of Team Rocket](set:glory-of-team-rocket-rivalites-destinees), des extensions japonaises regroupées en français dans Rivalités Destinées.

- Display japonais Heat Wave Arena (SV9a) scellé : 30 boosters de 5 cartes
- Série [Écarlate et Violet](set:ecarlate-et-violet)
MD),
    $P('display-mega-brave-m1l-japonais', 'FK-BB-M1L-01', 'Display Mega Brave (M1L) japonais — Méga-Lucario-ex', 'Mega Brave', 'boxes', 'Sealed', 'in', 0.4,
      [[1,119.97],[6,114.5],[36,102.5]], <<<'MD'
Un display japonais Mega Brave (M1L) scellé d’usine : l’une des deux extensions qui ont lancé la série Méga-Évolution en 2025, avec Méga-Lucario-ex en tête d’affiche.

Mega Brave et son extension jumelle [Mega Symphonia](product:display-mega-symphonia-m1s-japonais) ont ramené la Méga-Évolution dans le JCC Pokémon. Ensemble, elles forment l’extension française Méga-Évolution.

- Display japonais Mega Brave (M1L) scellé : 30 boosters de 5 cartes
- Carte phare : Méga-Lucario-ex
- Extension jumelle de Mega Symphonia (M1S)
MD),
    $P('display-mega-symphonia-m1s-japonais', 'FK-BB-M1S-01', 'Display Mega Symphonia (M1S) japonais — Méga-Gardevoir-ex', 'Mega Symphonia', 'boxes', 'Sealed', 'in', 0.4,
      [[1,109.97],[6,105],[36,94]], <<<'MD'
Un display japonais Mega Symphonia (M1S) scellé d’usine : l’extension jumelle de Mega Brave, qui a lancé la série Méga-Évolution en 2025 avec Méga-Gardevoir-ex.

Beaucoup de collectionneurs achètent les deux displays ensemble pour réunir tout le lancement de l’ère Méga-Évolution, sortie en français sous le nom Méga-Évolution.

- Display japonais Mega Symphonia (M1S) scellé : 30 boosters de 5 cartes
- Carte phare : Méga-Gardevoir-ex
- Extension jumelle de [Mega Brave](product:display-mega-brave-m1l-japonais)
MD),
    $P('display-30th-celebration-m6a-japonais', 'FK-BB-M6A-01', 'Display 30th Celebration (M6a) japonais', '30th Celebration', 'boxes', 'Sealed', 'in', 0.45,
      [[1,327],[6,279],[12,267],[24,255]], <<<'MD'
Un display japonais 30th Celebration (M6a) scellé d’usine : l’extension qui fête les 30 ans de Pokémon.

30th Celebration reprend des illustrations classiques aux côtés de nouvelles cartes, avec un très bon taux de cartes rares : un display apprécié des collectionneurs comme des ouvreurs. À ouvrir pour les cartes, ou à garder scellé comme pièce de collection.

- Display japonais 30th Celebration (M6a) scellé
- Illustrations classiques réimprimées et taux de rares élevé
- Toute la gamme : [coffrets et ETB 30th Celebration](set:30th-celebration)
MD),
    $P('display-storm-emeralda-m6-japonais', 'FK-BB-M6-01', 'Display Storm Emeralda (M6) japonais — Méga-Rayquaza-ex', 'Storm Emeralda', 'boxes', 'Sealed', 'in', 0.4,
      [[1,218],[6,185],[24,170]], <<<'MD'
Un display japonais Storm Emeralda (M6) scellé d’usine, l’extension Méga-Évolution emmenée par Méga-Rayquaza-ex.

Les cartes phares de Storm Emeralda sont [Méga-Rayquaza-ex Master Ultra Rare](product:mega-rayquaza-ex-mur-japonaise) et son [illustration spéciale rare](product:mega-rayquaza-ex-sar-245-191). Pour le meilleur prix par display, voyez le [carton de 12 displays](product:carton-12-displays-storm-emeralda).

- Display japonais Storm Emeralda (M6) scellé
- Cartes phares : Méga-Rayquaza-ex MUR et SAR
MD),
    $P('display-abyss-eye-m5-japonais', 'FK-BB-M5-01', 'Display Abyss Eye (M5) japonais', 'Abyss Eye', 'boxes', 'Sealed', 'in', 0.4,
      [[1,198],[6,168],[24,152]], <<<'MD'
Un display japonais Abyss Eye (M5) scellé d’usine, de la série Méga-Évolution.

Abyss Eye compte [Méga-Darkrai-ex](product:mega-darkrai-ex-japonaise) et [Méga-Minotaupe-ex](product:mega-minotaupe-ex-japonaise) parmi ses Méga-Pokémon-ex. Ouvrez des displays pour les obtenir, ou achetez directement les cartes.

- Display japonais Abyss Eye (M5) scellé
- Avec Méga-Darkrai-ex et Méga-Minotaupe-ex
- Existe aussi en [coffret Dresseur d’Élite](product:etb-abyss-eye-japonais)
MD),
    $P('display-ninja-spinner-m4-japonais', 'FK-BB-M4-01', 'Display Ninja Spinner (M4) japonais', 'Ninja Spinner', 'boxes', 'Sealed', 'low', 0.4,
      [[1,165],[6,141],[24,129]], <<<'MD'
Un display japonais Ninja Spinner (M4) scellé d’usine. Stock limité.

Ninja Spinner apporte [Méga-Amphinobi-ex](product:mega-amphinobi-ex-japonaise) et [Méga-Floette-ex](product:mega-floette-ex-japonaise) à la série Méga-Évolution. Amphinobi est un favori des fans depuis longtemps, et les displays scellés restants sont peu nombreux.

- Display japonais Ninja Spinner (M4) scellé
- Avec Méga-Amphinobi-ex et Méga-Floette-ex
MD),
    $P('display-nihil-zero-m3-japonais', 'FK-BB-M3-01', 'Display Nihil Zero (M3) japonais', 'Nihil Zero', 'boxes', 'Sealed', 'in', 0.4,
      [[1,83.3],[6,82.6],[36,81.2]], <<<'MD'
Un display japonais Nihil Zero (M3) scellé d’usine, de la série Méga-Évolution.

Nihil Zero est une façon abordable d’entrer dans l’ère Méga-Évolution, et un bon complément à une commande plus importante.

- Display japonais Nihil Zero (M3) scellé
- Série [Méga-Évolution](set:mega-evolution)
MD),
    $P('display-mega-dream-ex-m2a-japonais', 'FK-BB-M2A-01', 'Display Mega Dream ex (M2a) japonais — Méga-Ectoplasma-ex', 'Mega Dream ex', 'boxes', 'Sealed', 'in', 0.4,
      [[1,175],[6,149],[24,136]], <<<'MD'
Un display japonais Mega Dream ex (M2a) scellé d’usine : l’extension spéciale de la série Méga-Évolution.

Mega Dream ex abrite [Méga-Ectoplasma-ex illustration spéciale rare](product:mega-ectoplasma-ex-sar-japonaise), l’une des cartes marquantes de l’ère Méga-Évolution, ce qui en fait un display très prisé des collectionneurs.

- Display japonais Mega Dream ex (M2a) scellé
- Carte phare : Méga-Ectoplasma-ex illustration spéciale rare
MD),
    $P('display-aura-seeker-japonais', 'FK-BB-AS-01', 'Display Aura Seeker (Hadou Seeker) japonais — précommande', 'Aura Seeker (Hadou Seeker)', 'boxes', 'Sealed', 'preorder', 0.4,
      [[1,121.43],[6,115.9],[18,109.5],[36,103.8]], <<<'MD'
Précommandez le display japonais Aura Seeker (Hadou Seeker), une prochaine extension de la série Méga-Évolution.

La précommande réserve votre display avant la sortie au prix affiché. Vous êtes facturé à l’attribution du stock, pas à la commande, et le display part du Japon dès sa sortie et votre paiement reçu.

- Display japonais scellé, en précommande
- Rien à payer aujourd’hui : facturé à l’attribution du stock
MD, 'novembre 2026'),
    $P('carton-12-displays-storm-emeralda', 'FK-BB-M6-12', 'Carton de 12 displays Storm Emeralda (M6) japonais', 'Storm Emeralda', 'boxes', 'Sealed', 'in', 5.6,
      [[1,1635],[6,1463.7]], <<<'MD'
Un carton scellé de 12 displays japonais Storm Emeralda (M6), vendu au carton.

Pour les boutiques et les ouvreurs qui achètent en volume, le carton scellé est le plus simple : douze displays dans un carton d’usine, à un prix par display plus bas qu’à l’unité.

- 12 displays japonais Storm Emeralda (M6) scellés par carton
- Carte phare : [Méga-Rayquaza-ex Master Ultra Rare](product:mega-rayquaza-ex-mur-japonaise)
MD),

    $P('etb-30th-celebration', 'FK-ETB-30C-01', 'Coffret Dresseur d’Élite 30th Celebration (ETB, anglais)', '30th Celebration', 'etb', 'Sealed', 'in', 0.9,
      [[1,48.99],[4,44],[24,33.2]], <<<'MD'
Le Coffret Dresseur d’Élite (ETB) Pokémon 30th Celebration, en version anglaise : neuf boosters de l’extension des 30 ans et tout le nécessaire pour jouer.

30th Celebration fête trente ans de Pokémon : Mewtwo-ex et Mew-ex mènent l’extension, avec Noctali-ex, Drattak-ex et Amphinobi-ex, et chaque booster contient un Pikachu, parmi 30 cartes Pikachu rares à collectionner. Pour acheter en volume, voyez le [carton de 10 coffrets](product:carton-10-etb-30th-celebration).

- 9 boosters Pokémon 30th Celebration
- 1 carte promo full art Nidorina
- 16 cartes Énergie de base brillantes et 65 protège-cartes
- 6 dés de dégâts, 1 dé pile ou face et 1 pièce
- Une boîte de rangement avec 6 intercalaires et une carte code pour JCC Pokémon Live
MD),
    $P('etb-storm-emeralda-japonais', 'FK-ETB-SE-01', 'Coffret Dresseur d’Élite Storm Emeralda (ETB japonais)', 'Storm Emeralda', 'etb', 'Sealed', 'in', 0.9,
      [[1,45],[6,38],[18,36],[36,34]], <<<'MD'
Un Coffret Dresseur d’Élite Storm Emeralda scellé d’usine, de la série japonaise Méga-Évolution.

Il contient des boosters ainsi que des protège-cartes, des dés, des marqueurs de dégâts, des cartes Énergie et la carte promo de l’extension, dans une boîte de rangement : tout pour ouvrir et jouer Storm Emeralda. Idéal avec un [display Storm Emeralda](product:display-storm-emeralda-m6-japonais).

- Coffret Dresseur d’Élite Storm Emeralda scellé
- Boosters, protège-cartes, dés, marqueurs, Énergies et carte promo
MD),
    $P('etb-abyss-eye-japonais', 'FK-ETB-AE-01', 'Coffret Dresseur d’Élite Abyss Eye (ETB japonais)', 'Abyss Eye', 'etb', 'Sealed', 'in', 0.9,
      [[1,42],[6,35.5],[18,33.6],[36,32]], <<<'MD'
Un Coffret Dresseur d’Élite Abyss Eye scellé d’usine, de la série japonaise Méga-Évolution.

Des boosters avec protège-cartes, dés, marqueurs de dégâts et carte promo dans une boîte de rangement : la façon simple d’ouvrir Abyss Eye, en complément des [displays](product:display-abyss-eye-m5-japonais).

- Coffret Dresseur d’Élite Abyss Eye scellé
MD),
    $P('etb-pitch-black', 'FK-ETB-PB-01', 'Coffret Dresseur d’Élite Méga-Évolution Pitch Black (ETB, anglais)', 'Abyss Eye', 'etb', 'Sealed', 'in', 0.9,
      [[1,44.09],[4,39.6],[24,29.9]], <<<'MD'
Un Coffret Dresseur d’Élite Pokémon Méga-Évolution Pitch Black scellé d’usine, en version anglaise.

Pitch Black fait partie de la série Méga-Évolution du JCC Pokémon. Un Coffret Dresseur d’Élite est la façon complète de commencer une extension : des boosters, des protège-cartes, des dés, des marqueurs de dégâts et une boîte de rangement. L’équivalent japonais est dans notre gamme [Abyss Eye](set:abyss-eye).

- Coffret Dresseur d’Élite Pitch Black scellé (anglais)
- Boosters, protège-cartes, dés, marqueurs et boîte de rangement
MD),
    $P('etb-chaos-rising', 'FK-ETB-CR-01', 'Coffret Dresseur d’Élite Méga-Évolution Chaos Rising (ETB, anglais)', 'Ninja Spinner', 'etb', 'Sealed', 'in', 0.9,
      [[1,44.09],[4,39.6],[24,29.9]], <<<'MD'
Un Coffret Dresseur d’Élite Pokémon Méga-Évolution Chaos Rising scellé d’usine, en version anglaise.

Chaos Rising fait partie de la série Méga-Évolution du JCC Pokémon. Des boosters, des protège-cartes, des dés, des marqueurs de dégâts et une boîte de rangement. L’équivalent japonais est dans notre gamme [Ninja Spinner](set:ninja-spinner).

- Coffret Dresseur d’Élite Chaos Rising scellé (anglais)
- Boosters, protège-cartes, dés, marqueurs et boîte de rangement
MD),
    $P('etb-perfect-order', 'FK-ETB-PO-01', 'Coffret Dresseur d’Élite Méga-Évolution Perfect Order (ETB, anglais)', 'Nihil Zero', 'etb', 'Sealed', 'in', 0.9,
      [[1,44.09],[4,39.6],[24,29.9]], <<<'MD'
Un Coffret Dresseur d’Élite Pokémon Méga-Évolution Perfect Order scellé d’usine, en version anglaise.

Perfect Order fait partie de la série Méga-Évolution du JCC Pokémon, avec Méga-Zygarde-ex en tête d’affiche. L’équivalent japonais est dans notre gamme [Nihil Zero](set:nihil-zero).

- Coffret Dresseur d’Élite Perfect Order scellé (anglais)
- Boosters, protège-cartes, dés, marqueurs et boîte de rangement
MD),
    $P('etb-ascended-heroes', 'FK-ETB-AH-01', 'Coffret Dresseur d’Élite Méga-Évolution Ascended Heroes (ETB, anglais)', 'Mega Dream ex', 'etb', 'Sealed', 'in', 0.9,
      [[1,44.09],[4,39.6],[24,29.9]], <<<'MD'
Un Coffret Dresseur d’Élite Pokémon Méga-Évolution Ascended Heroes scellé d’usine, en version anglaise.

Ascended Heroes fait partie de la série Méga-Évolution du JCC Pokémon. L’équivalent japonais est l’extension spéciale [Mega Dream ex](set:mega-dream-ex), où se trouve Méga-Ectoplasma-ex.

- Coffret Dresseur d’Élite Ascended Heroes scellé (anglais)
- Boosters, protège-cartes, dés, marqueurs et boîte de rangement
MD),
    $P('carton-10-etb-30th-celebration', 'FK-ETB-30C-10', 'Carton de 10 Coffrets Dresseur d’Élite 30th Celebration', '30th Celebration', 'etb', 'Sealed', 'in', 10,
      [[1,440.5],[6,331.7]], <<<'MD'
Un carton scellé de 10 Coffrets Dresseur d’Élite Pokémon 30th Celebration, vendu au carton.

La façon la plus économique d’acheter les ETB anniversaire : dix coffrets dans un carton d’usine. Chaque coffret contient 9 boosters, une promo Nidorina full art, 65 protège-cartes, des dés et un guide du joueur.

- 10 Coffrets Dresseur d’Élite 30th Celebration scellés par carton
- Aussi vendu [à l’unité](product:etb-30th-celebration)
MD),

    $P('coffret-amphinobi-ex-30th-celebration', 'FK-COL-30C-GRE', 'Coffret Amphinobi-ex 30th Celebration', '30th Celebration', 'premium', 'Sealed', 'in', 0.35,
      [[1,21.56],[6,19.4],[18,16.8],[36,14.6]], <<<'MD'
Un coffret collection 30th Celebration autour d’une carte promo Amphinobi-ex, avec des boosters.

Une idée cadeau simple pour les 30 ans de Pokémon, qui va de pair avec le [coffret Nymphali-ex](product:coffret-nymphali-ex-30th-celebration).

- Carte promo Amphinobi-ex et boosters
- Gamme [30th Celebration](set:30th-celebration)
MD),
    $P('coffret-nymphali-ex-30th-celebration', 'FK-COL-30C-SYL', 'Coffret Nymphali-ex 30th Celebration', '30th Celebration', 'premium', 'Sealed', 'in', 0.35,
      [[1,21.56],[6,19.4],[36,14.6]], <<<'MD'
Un coffret collection 30th Celebration autour de Nymphali-ex, avec des boosters.

Le compagnon du [coffret Amphinobi-ex](product:coffret-amphinobi-ex-30th-celebration) : un cadeau facile pour les fans d’Évoli et de ses évolutions.

- Coffret collection Nymphali-ex avec boosters
- Gamme [30th Celebration](set:30th-celebration)
MD),
    $P('premium-deck-set-mentali-noctali-30th-celebration', 'FK-PDS-30C-EU', 'Premium Deck Set Mentali et Noctali 30th Celebration', '30th Celebration', 'premium', 'Sealed', 'in', 0.6,
      [[1,432.8],[6,371.7]], <<<'MD'
Le Premium Deck Set 30th Celebration consacré à Mentali et Noctali : une pièce de collection pour l’anniversaire de Pokémon.

Mentali et Noctali comptent parmi les évolutions d’Évoli les plus appréciées, et ce coffret premium les place au cœur des 30 ans de Pokémon.

- Premium Deck Set 30th Celebration : Mentali et Noctali
- Pièce de collection anniversaire
MD),
    $P('collection-stickers-30th-celebration', 'FK-STK-30C-01', 'Collection de stickers 30th Celebration', '30th Celebration', 'premium', 'Sealed', 'in', 0.2,
      [[1,14.7],[12,13.2],[72,10]], <<<'MD'
La Tech Sticker Collection 30th Celebration : un petit prix pour fêter les 30 ans de Pokémon.

Un complément facile à ajouter à un coffret ou un display 30th Celebration.

- Collection de stickers 30th Celebration
MD),
    $P('premium-trainer-box-mega', 'FK-PTB-MEGA-01', 'Premium Trainer Box MEGA (japonais)', '', 'premium', 'Sealed', 'in', 1.2,
      [[1,91.4],[4,87.2],[24,78.1]], <<<'MD'
Premium Trainer Box MEGA : un coffret dresseur premium de l’ère Méga-Évolution.

Il réunit des boosters et des accessoires de jeu pour construire ses premiers decks Méga-Évolution, et fait un très beau cadeau.

- Coffret dresseur premium de l’ère Méga-Évolution
MD),
    $P('mega-start-deck-100-battle-collection', 'FK-SD-MEGA-100', 'MEGA Start Deck 100 Battle Collection (japonais)', '', 'premium', 'Sealed', 'in', 0.35,
      [[1,37.11],[12,35.4],[72,31.7]], <<<'MD'
La MEGA Start Deck 100 Battle Collection : des decks Pokémon prêts à jouer pour débuter.

Les decks de démarrage sont la façon la plus simple d’apprendre à jouer au JCC Pokémon : on ouvre la boîte et on joue, sans construire de deck. Pour les boutiques, c’est aussi un produit d’initiation idéal.

- Decks prêts à jouer de l’ère Méga-Évolution
- Pour apprendre : [comment jouer aux cartes Pokémon](guide:jouer-cartes-pokemon)
MD),
    $P('deck-starter-set-ex-zorua-zoroark-ex', 'FK-SS-ZOR-01', 'Deck Starter Set ex Zorua et Zoroark-ex (japonais)', '', 'premium', 'Sealed', 'in', 0.35,
      [[1,37.11],[12,35.4],[72,31.7]], <<<'MD'
Un Starter Set ex avec un deck Zorua et Zoroark-ex prêt à jouer.

Un deck complet autour de Zoroark-ex, à jouer dès l’ouverture : un bon premier deck pour débuter.

- Deck Zorua et Zoroark-ex prêt à jouer
MD),
    $P('deck-starter-set-ex-evoli-ex', 'FK-SS-EEV-01', 'Deck Starter Set ex Évoli-ex (japonais)', '', 'premium', 'Sealed', 'in', 0.35,
      [[1,75],[10,59],[60,51]], <<<'MD'
Un Starter Set ex avec un deck Évoli-ex prêt à jouer.

Un deck complet autour d’Évoli-ex, à jouer dès l’ouverture : un premier achat idéal et un cadeau apprécié.

- Deck Évoli-ex prêt à jouer
MD),

    $P('carte-dracaufeu-ex-sar-151-japonaise', 'FK-SGL-CHAR-SAR', 'Carte Dracaufeu-ex illustration spéciale rare 151 (japonaise)', '151', 'singles', 'Near Mint', 'in', 0.05,
      [[1,328.8],[6,297.7]], <<<'MD'
La carte japonaise Dracaufeu-ex illustration spéciale rare (SAR) de l’extension 151 (SV2a).

Dracaufeu est l’un des Pokémon les plus collectionnés du JCC, et son illustration spéciale rare de 151 est l’une des cartes Dracaufeu emblématiques de l’ère Écarlate et Violet. Near Mint, expédiée sous protège-carte et toploader.

- Japonaise — 151 (SV2a)
- Illustration spéciale rare (full art)
- Toutes nos [cartes Dracaufeu](cards:dracaufeu)
MD),
    $P('mega-dracaufeu-y-ex-hyper-rare', 'FK-SGL-MCHY-HR', 'Carte Méga-Dracaufeu Y-ex hyper rare (japonaise)', '', 'singles', 'Near Mint', 'in', 0.05,
      [[1,410.6],[6,371.7]], <<<'MD'
Méga-Dracaufeu Y-ex hyper rare : une carte Dracaufeu dorée de la série Méga-Évolution.

Les hyper rares sont les cartes dorées texturées au sommet d’une extension, et celles de Dracaufeu sont toujours parmi les plus recherchées. Near Mint, expédiée sous protège-carte et toploader.

- Hyper rare (dorée, texturée)
- Near Mint
- Pourquoi les [cartes dorées](guide:rarete-carte-pokemon) sont rares
MD),
    $P('mega-rayquaza-ex-mur-japonaise', 'FK-SGL-MRAY-MUR', 'Carte Méga-Rayquaza-ex Master Ultra Rare (japonaise)', 'Storm Emeralda', 'singles', 'Near Mint', 'in', 0.05,
      [[1,1207.3],[3,1150],[6,1092.9]], <<<'MD'
La carte japonaise Méga-Rayquaza-ex Master Ultra Rare de Storm Emeralda, la carte la plus rare de l’extension.

Rayquaza est l’une des légendes les plus collectionnées du JCC Pokémon, et sa Master Ultra Rare est tout en haut de l’échelle de rareté de Storm Emeralda. Chaque exemplaire est Near Mint, expédié sous protège-carte et toploader.

- Japonaise — Storm Emeralda (M6)
- Rareté : Master Ultra Rare (MUR)
- Near Mint, protège-carte et toploader
MD),
    $P('mega-rayquaza-ex-sar-245-191', 'FK-SGL-MRAY-SAR', 'Carte Méga-Rayquaza-ex SAR 245/191 (japonaise)', 'Storm Emeralda', 'singles', 'Near Mint', 'in', 0.05,
      [[1,120],[3,102],[25,90]], <<<'MD'
La carte japonaise Méga-Rayquaza-ex illustration spéciale rare, numéro 245/191 de Storm Emeralda, en état Near Mint.

Une illustration spéciale rare full art du Pokémon phare de l’extension, plus accessible que la [Master Ultra Rare](product:mega-rayquaza-ex-mur-japonaise).

- Japonaise — Storm Emeralda (M6), carte 245/191
- Illustration spéciale rare (full art)
- Near Mint, protège-carte et toploader
MD),
    $P('mega-ectoplasma-ex-sar-japonaise', 'FK-SGL-MGEN-SAR', 'Carte Méga-Ectoplasma-ex illustration spéciale rare (japonaise)', 'Mega Dream ex', 'singles', 'Near Mint', 'in', 0.05,
      [[1,1033.5],[3,985],[6,935.6]], <<<'MD'
La carte japonaise Méga-Ectoplasma-ex illustration spéciale rare de Mega Dream ex.

Ectoplasma a l’une des communautés de fans les plus fidèles, et cette illustration spéciale rare est l’une des cartes marquantes de la série Méga-Évolution. Near Mint, expédiée sous protège-carte et toploader.

- Japonaise — Mega Dream ex (M2a)
- Illustration spéciale rare (full art)
- Toutes nos [cartes Ectoplasma](cards:ectoplasma)
MD),
    $P('mega-darkrai-ex-japonaise', 'FK-SGL-MDRK-01', 'Carte Méga-Darkrai-ex (japonaise)', 'Abyss Eye', 'singles', 'Near Mint', 'in', 0.05,
      [[1,182.3],[6,165]], <<<'MD'
La carte japonaise Méga-Darkrai-ex d’Abyss Eye (M5), en état Near Mint.

Darkrai est un Pokémon fabuleux très apprécié, et Méga-Darkrai-ex est l’une des cartes phares d’Abyss Eye. Expédiée sous protège-carte et toploader.

- Japonaise — [Abyss Eye](set:abyss-eye) (M5)
- Near Mint, protège-carte et toploader
MD),
    $P('mega-amphinobi-ex-japonaise', 'FK-SGL-MGRE-01', 'Carte Méga-Amphinobi-ex (japonaise)', 'Ninja Spinner', 'singles', 'Near Mint', 'in', 0.05,
      [[1,84.6],[6,76.6]], <<<'MD'
La carte japonaise Méga-Amphinobi-ex de Ninja Spinner (M4), en état Near Mint.

Amphinobi est un favori des fans depuis longtemps, et Méga-Amphinobi-ex est l’un des Pokémon phares de Ninja Spinner. Expédiée sous protège-carte et toploader.

- Japonaise — [Ninja Spinner](set:ninja-spinner) (M4)
- Near Mint, protège-carte et toploader
MD),
    $P('mega-minotaupe-ex-japonaise', 'FK-SGL-MEXC-01', 'Carte Méga-Minotaupe-ex (japonaise)', 'Abyss Eye', 'singles', 'Near Mint', 'in', 0.05,
      [[1,65.8],[6,59.5]], <<<'MD'
La carte japonaise Méga-Minotaupe-ex d’Abyss Eye (M5), en état Near Mint.

Méga-Minotaupe-ex est l’un des Méga-Pokémon-ex introduits dans Abyss Eye. Expédiée sous protège-carte et toploader.

- Japonaise — [Abyss Eye](set:abyss-eye) (M5)
- Near Mint, protège-carte et toploader
MD),
    $P('mega-floette-ex-japonaise', 'FK-SGL-MFLO-01', 'Carte Méga-Floette-ex (japonaise)', 'Ninja Spinner', 'singles', 'Near Mint', 'in', 0.05,
      [[1,56.4],[6,51]], <<<'MD'
La carte japonaise Méga-Floette-ex de Ninja Spinner (M4), en état Near Mint.

Méga-Floette-ex est l’un des nouveaux Méga-Pokémon-ex de Ninja Spinner. Expédiée sous protège-carte et toploader.

- Japonaise — [Ninja Spinner](set:ninja-spinner) (M4)
- Near Mint, protège-carte et toploader
MD),
    $P('pikachu-ex-sar-277-217', 'FK-SGL-PIKA-277', 'Carte Pikachu-ex illustration spéciale rare 277/217', '', 'singles', 'Near Mint', 'in', 0.05,
      [[1,362.7],[6,328.3]], <<<'MD'
Pikachu-ex illustration spéciale rare, carte 277/217, en état Near Mint.

Une illustration spéciale rare full art de la mascotte de Pokémon : une carte Pikachu centrale dans toute collection. Expédiée sous protège-carte et toploader.

- Carte 277/217
- Illustration spéciale rare (full art)
- Near Mint
- Toutes nos [cartes Pikachu](cards:pikachu)
MD),
    $P('pikachu-ex-sar-240-191-psa-10', 'FK-SGL-PIKA-PSA10', 'Carte Pikachu-ex SAR 240/191 gradée PSA 10', '', 'singles', 'Graded', 'in', 0.15,
      [[1,495],[3,470]], <<<'MD'
Pikachu-ex illustration spéciale rare 240/191, gradée PSA 10 Gem Mint.

PSA 10 est la note maximale : une carte quasi parfaite, authentifiée et scellée dans le boîtier inviolable de PSA. Les cartes Pikachu plaisent à tous les collectionneurs, et les exemplaires les mieux notés sont ceux qu’on garde.

- Pikachu-ex illustration spéciale rare 240/191
- Gradée PSA 10 Gem Mint
- Expédiée dans son boîtier PSA d’origine
- Toutes nos [cartes gradées PSA](cards:cartes-pokemon-gradees-psa)
MD),

    $P('classeur-carte-pokemon-9-cases-mega-evolution', 'FK-ACC-BND-01', 'Classeur carte Pokémon 9 cases Méga-Évolution', '', 'accessories', 'Sealed', 'in', 0.45,
      [[1,31.36],[6,27.9],[36,20.3]], <<<'MD'
Un classeur pour cartes Pokémon à 9 cases par page, aux couleurs de la série Méga-Évolution.

Chaque page accueille neuf cartes au format standard : le classeur est la façon la plus simple de ranger une collection qui grandit et de mettre en valeur ses cartes full art. Les cartes sous protège-carte simple y rentrent sans problème.

- Classeur 9 cases pour cartes Pokémon
- Illustration Méga-Évolution
MD),
    $P('boite-de-rangement-cartes-pokemon-display', 'FK-ACC-BOX-01', 'Boîte de rangement cartes Pokémon style display', '', 'accessories', 'Sealed', 'in', 0.25,
      [[1,8.82],[6,7.9],[36,6]], <<<'MD'
Une boîte de rangement pour cartes Pokémon au look de display de boosters.

Elle garde les cartes, sous protège-cartes ou non, bien droites sur une étagère, et sert aussi d’objet de décoration. Les cartes Pokémon mesurent 63 × 88 mm.

- Boîte de rangement style display
MD),
    $P('protege-cartes-pokemon-64', 'FK-ACC-SLV-64', 'Protège-cartes Pokémon (64) — motifs assortis', '', 'accessories', 'Sealed', 'in', 0.06,
      [[1,8],[20,6.2],[100,5.2]], <<<'MD'
Des protège-cartes Pokémon aux motifs assortis, 64 par paquet.

Au format standard 63 × 88 mm (les cartes japonaises et françaises ont la même taille), ils gardent vos cartes en parfait état en jeu comme en rangement. Le choix des motifs varie selon les arrivages.

- 64 protège-cartes par paquet
- Pour cartes Pokémon japonaises et françaises
MD),
    $P('protege-cartes-ultra-pro-pikachu-65', 'FK-ACC-UP-PIKA-65', 'Protège-cartes Ultra PRO Pikachu (65)', '', 'accessories', 'Sealed', 'in', 0.06,
      [[1,10.78],[24,9.1],[144,5.4]], <<<'MD'
Des protège-cartes Ultra PRO Deck Protector illustrés Pikachu : 65 protège-cartes au format standard par paquet.

- 65 protège-cartes par paquet
- Format standard 63 × 88 mm
MD),
    $P('protege-cartes-mega-rayquaza-storm-emeralda', 'FK-ACC-SLV-MRAY', 'Protège-cartes Méga-Rayquaza Storm Emeralda', 'Storm Emeralda', 'accessories', 'Sealed', 'in', 0.06,
      [[1,12.73],[24,11.5],[144,8.6]], <<<'MD'
Des protège-cartes illustrés Méga-Rayquaza, issus de la sortie Storm Emeralda, au format des cartes Pokémon.

Le complément naturel d’un [display Storm Emeralda](product:display-storm-emeralda-m6-japonais).

- Format standard 63 × 88 mm
MD),
    $P('protege-cartes-pikachu-metamorph', 'FK-ACC-SLV-PIKA-DIT', 'Protège-cartes Pikachu (version Métamorph)', '', 'accessories', 'Sealed', 'in', 0.06,
      [[1,12.73],[24,11.5],[144,8.6]], <<<'MD'
Des protège-cartes illustrés Pikachu en version Métamorph, au format des cartes Pokémon.

- Format standard 63 × 88 mm
MD),
    $P('protege-cartes-celebi-fouinar', 'FK-ACC-SLV-CEL', 'Protège-cartes Celebi et Fouinar', '', 'accessories', 'Sealed', 'in', 0.06,
      [[1,12.73],[24,11.5],[144,8.6]], <<<'MD'
Des protège-cartes illustrés Celebi et Fouinar, au format des cartes Pokémon.

- Format standard 63 × 88 mm
MD),
    $P('deck-box-ultra-pro-pikachu-alcove-tower', 'FK-ACC-UP-DBX', 'Deck box Ultra PRO Pikachu Alcove Tower', '', 'accessories', 'Sealed', 'in', 0.1,
      [[1,20.59],[12,18.5],[72,13.9]], <<<'MD'
Une deck box Ultra PRO Alcove Tower illustrée Pikachu.

Une boîte solide pour transporter un deck sous protège-cartes en tournoi ou chez des amis.

- Deck box Ultra PRO Alcove Tower
- Illustration Pikachu
MD),
    $P('deck-box-pokemon', 'FK-ACC-DBX-01', 'Deck box Pokémon — motifs assortis', '', 'accessories', 'Sealed', 'in', 0.06,
      [[1,12],[20,9.4],[100,8]], <<<'MD'
Des deck box Pokémon aux motifs assortis.

Pour garder un deck sous protège-cartes à l’abri dans un sac ou une poche. Le choix des motifs varie selon les arrivages.

- Motifs Pokémon assortis
MD),
    $P('tapis-de-jeu-pokemon', 'FK-ACC-MAT-01', 'Tapis de jeu Pokémon — motifs assortis', '', 'accessories', 'Sealed', 'in', 0.45,
      [[1,28],[10,22],[50,19]], <<<'MD'
Des tapis de jeu Pokémon aux motifs assortis.

Un tapis protège les cartes pendant la partie et délimite la zone de jeu. Le choix des motifs varie selon les arrivages.

- Motifs Pokémon assortis
MD),
  ],

  /* countries: '*' = everywhere, or a list of country codes */
  /* type 'bitcoin' = paid on the site to btc_address above; any other method = you send the details */
  'payments' => [
    'bitcoin'  => ['label'=>'Bitcoin (BTC) — payez maintenant', 'note'=>'Payez depuis n’importe quel portefeuille Bitcoin dès la commande. Le montant exact et un QR code s’affichent sur la page suivante.', 'countries'=>'*', 'enabled'=>true, 'type'=>'bitcoin'],
    'crypto'   => ['label'=>'Autres cryptos (ETH, USDT)', 'note'=>'ETH ou USDT (TRC-20 / ERC-20). Nous vous envoyons l’adresse du portefeuille avec votre facture. Les frais de réseau sont à la charge de l’expéditeur.', 'countries'=>'*', 'enabled'=>true],
    'sepa'     => ['label'=>'Virement bancaire SEPA (EUR)', 'note'=>'Virement en euros depuis n’importe quelle banque de la zone SEPA. Nous vous envoyons nos coordonnées bancaires (IBAN) avec votre facture ; indiquez votre référence de commande.', 'countries'=>'*', 'enabled'=>true],
  ],

  'countries' => ['FR'=>'France', 'BE'=>'Belgique', 'LU'=>'Luxembourg', 'MC'=>'Monaco'],

  /* shipping (USD) = zone per-order price + per-kg price × order weight, for each delivery option; totals round up
     to whole units. Starting rates for Japan → France: check them against your carrier's real costs. */
  'shipping' => [
    'methods'  => ['standard'=>['label'=>'Standard', 'days'=>'5 à 10 jours ouvrés'],
                   'express' =>['label'=>'Express',  'days'=>'2 à 5 jours ouvrés']],
    'round_up' => true,
    'zones' => [
      ['name'=>'France, Belgique, Luxembourg et Monaco', 'countries'=>['FR','BE','LU','MC'], 'standard'=>['base'=>12, 'per_kg'=>9], 'express'=>['base'=>24, 'per_kg'=>16]],
    ],
    'rest' => ['standard'=>['base'=>18, 'per_kg'=>12], 'express'=>['base'=>34, 'per_kg'=>22]],
  ],
];

$c = require __DIR__.'/content.php';
$d['settings']    = $c['settings'] + $d['settings'];
$d['categories']  = $c['categories'];
$d['faqs']        = $c['faqs'];
$d['series']      = $c['series'];
$d['sets']        = $c['sets'];
$d['collections'] = $c['collections'];
$d['guides']      = $c['guides'];
$d['pages']       = $c['pages'];
return $d;
