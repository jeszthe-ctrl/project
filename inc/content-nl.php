<?php
/* Dutch text for a fresh install (Flanders and Brussels). Copied into data/store.php on first load and
   edited in admin.php after (choose "Nederlands" at the top of the admin).

   Formatting: blank line = new paragraph, "## " heading, "### " subheading, "- " bullet, "1. " step, **bold**,
   [text](link). Links: product:ID, category:KEY, set:SLUG (sets and series), cards:KEY (collections),
   guide:KEY, page:KEY (about, wholesale, terms, privacy) or page:shop|sets|guides|faq|shipping|payment|how|contact.
   {min_order} {reply_hours} {hold_hours} {free_ship} {standard} {express} {standard_days} {express_days}
   {brand} {company} {address} {email} {kanji} are filled in with current values. */
if(!defined('FK_ROOT')){ http_response_code(404); exit; }

return [
  'settings' => [
    'tagline'         => 'Japanse Pokémon kaarten, rechtstreeks uit Japan naar België',
    'strip_text'      => 'Echte Japanse Pokémon kaarten · Verzegeld uit Japan · Geen btw bovenop de prijs',
    'strip_link_text' => 'Aura Seeker nu te reserveren →',
    'strip_link_url'  => 'product:aura-seeker-booster-box',
    'hero_title'      => 'Japanse Pokémon kaarten, rechtstreeks uit Japan naar België.',
    'hero_lede'       => 'Verzegelde booster boxes, Elite Trainer Boxes, Pokémon boxen en zeldzame losse kaarten, ingekocht in Japan en met tracking tot aan je deur. Elke prijs staat online, ook de korting per aantal, en we rekenen geen btw aan.',
    'footer_blurb'    => 'Onafhankelijke verkoper van echte Japanse Pokémon kaarten. Verzegelde booster boxes, Elite Trainer Boxes en zeldzame kaarten, met tracking verzonden vanuit Japan naar heel België.',
    'home_seo_title'  => 'Pokémon kaarten kopen: Japanse booster boxes & losse kaarten',
    'home_seo_desc'   => 'Japanse Pokémon kaarten kopen in België: verzegelde booster boxes, Elite Trainer Boxes, Pokémon boxen en zeldzame kaarten, verzonden vanuit Japan. Prijzen in euro, geen btw.',
    'home_intro'      => <<<'MD'
## Pokémon kaarten kopen in België, rechtstreeks uit Japan

{brand} verkoopt Japanse Pokémon kaarten aan verzamelaars, spelers en winkels in heel België. Je vindt hier verzegelde [Pokémon booster boxes](category:boxes), [Elite Trainer Boxes](category:etb), [Pokémon boxen en starter decks](category:premium), [zeldzame losse kaarten](category:singles) en [sleeves, mappen en playmats](category:accessories). Alles wordt in Japan ingekocht en met tracking naar je verzonden, van Antwerpen tot Luik.

## Waarom Japanse Pokémon kaarten?

Nieuwe Pokémon sets verschijnen eerst in Japan, vaak maanden voor de Engelse versie. Wie de nieuwste kaarten als eerste wil, koopt dus Japans. Japanse kaarten staan bovendien bekend om hun scherpe druk en nette snijranden, en Japanse booster boxes zijn kleiner en goedkoper per box, zodat je met hetzelfde budget meer sets opent. Meer uitleg vind je in onze gids over [Japanse Pokémon kaarten](guide:japanese).

## Wat is een Pokémon kaart waard?

De waarde van een Pokémon kaart hangt af van de zeldzaamheid, de staat van de kaart en de vraag naar die ene Pokémon. Met onze [prijschecker voor Pokémon kaarten](guide:prices) zie je meteen wat elke box, kaart en accessoire bij ons kost, en hoe de prijs daalt als je meer bestelt.

## Het Pokémon kaartspel (TCG)

Pokémon kaarten zijn ook een spel: de Pokémon Trading Card Game, kortweg TCG. Een [starter deck](category:premium) is de makkelijkste manier om te beginnen. Hoe het spel werkt, lees je in [Pokémon TCG: wat is het en hoe speel je?](guide:tcg)

## Voor winkels en grote bestellingen

Heb je een kaartenwinkel, organiseer je toernooien of verkoop je online? Elke productpagina toont de minimumafname en de prijs per aantal, zonder account of aanvraag. Bestellingen beginnen bij {min_order} inclusief verzending. Zie ook onze [voorwaarden voor groothandel](page:wholesale).
MD,
    /* the Shipping & Returns page ({rates} = the delivery options and rate tables) */
    'shipping_policy' => <<<'MD'
Elke bestelling bij {brand} vertrekt vanuit Japan en komt met tracking bij je aan in België. Op deze pagina lees je hoe we verzenden, wat het kost, hoe lang het duurt en wat er gebeurt als er iets misgaat. De prijs bij het afrekenen is altijd de juiste verzendprijs voor jouw bestelling.

## Leveropties en tarieven

{rates}

## Gratis verzending

Bestellingen met een goederentotaal vanaf **{free_ship}** verzenden we gratis met {standard}, overal in België. Dat gebeurt automatisch bij het afrekenen, zonder code. Liever sneller? Kies {express} en je betaalt alleen het verschil tussen {express} en {standard}.

## Van bestelling tot aan je deur

1. **Je plaatst je bestelling** en krijgt meteen een e-mail met je bestelnummer.
2. **Je betaalt.** Bitcoin betaal je op je bestelpagina zodra je bestelt. Voor andere crypto sturen we je binnen {reply_hours} uur ons walletadres.
3. **Je betaling komt binnen** en je producten worden voor je gereserveerd.
4. **We pakken in en verzenden** binnen {hold_hours} uur vanuit Japan.
5. **Je krijgt je trackingnummer** per e-mail zodra het label gemaakt is.
6. **Je pakket wordt geleverd**, in België meestal door bpost of een koerier.

De levertijd telt vanaf het moment dat je pakket vertrekt, niet vanaf je bestelling.

## Je pakket volgen

Elk pakket heeft tracking. Een nieuw trackingnummer toont soms pas na 24 tot 48 uur de eerste scan; dat is normaal. De route is meestal: label gemaakt → aangenomen in Japan → uitvoer → onderweg → douane in de EU → overgedragen aan bpost of een koerier → onderweg voor levering → geleverd.

Staat de tracking 5 werkdagen stil? Mail ons en we contacteren de vervoerder.

## Levertijden en vertragingen

Levertijden zijn schattingen in werkdagen na verzending, geen garantie. Ze kunnen langer duren als:

- de douane een pakket controleert
- het een feestdag is in Japan (Nieuwjaar, Golden Week begin mei, Obon half augustus) of in België
- vervoerders het druk hebben, bijvoorbeeld in de weken voor Sinterklaas en Kerstmis
- het adres onvolledig of moeilijk bereikbaar is

Plan je een release-event of een stream? Kies {express} en hou een paar dagen marge.

## Douane en belastingen

Wij rekenen geen btw of andere belastingen aan: het totaal bij het afrekenen is wat je ons betaalt. Je bestelling komt uit Japan en gaat dus door de douane bij aankomst in de EU. We geven elk pakket eerlijk aan, met de echte inhoud en waarde. Zoals bij elk pakket van buiten de EU kan de vervoerder bij sommige bestellingen invoerkosten vragen voor hij levert; die betaal je aan de vervoerder, niet aan ons.

Worden die kosten geweigerd, dan gaat het pakket terug naar Japan. Zie [teruggestuurd naar afzender](page:shipping#teruggestuurd-naar-afzender).

## Je leveradres

Controleer voor je bestelt de naam, straat en huisnummer, busnummer, postcode, gemeente en je telefoonnummer. De vervoerder gebruikt je telefoonnummer voor vragen van de douane en om de levering te regelen.

Wil je het adres wijzigen? Mail ons meteen met je bestelnummer. Dat kan tot het pakket aan de vervoerder is overgedragen.

## Hoe we inpakken

- **Verzegelde producten** gaan in hun originele folie, in een doos met opvulling zodat niets kan schuiven.
- **Cases** gaan in de originele doos van de distributeur, waar mogelijk in een extra buitendoos.
- **Losse kaarten** gaan in een sleeve en toploader, vochtdicht verpakt in een stevige envelop of doos.
- **Graded kaarten** worden met slab en al ingepakt, zodat de case geen klap kan krijgen.

## Als er iets misgaat

### Controleer je bestelling bij ontvangst

Open je pakket zo snel mogelijk en controleer:

- de buitendoos op deuken, scheuren of waterschade
- het aantal producten tegenover je bevestiging
- de folie en zegels van verzegelde producten
- de staat van losse kaarten en de labels van graded slabs

Is er iets mis, **bewaar dan alles**: de doos met het label, de verpakking en de producten. Maak foto's voor je iets weggooit; de vervoerder vraagt daar naar. Film bij waardevolle bestellingen het uitpakken.

### Beschadigd tijdens transport

Mail [{email}](mailto:{email}) binnen **7 dagen na levering** met je bestelnummer en foto's van het pakket (alle kanten en het label), de verpakking en de schade. We vervangen de beschadigde producten of betalen ze terug, met de verzendkosten.

Lichte sporen op verzegelde boxen, zoals een kleine deuk of een kreukje in de folie, zijn normaal door de behandeling in de fabriek en bij de distributeur. Twijfel je? Stuur ons foto's.

### Ontbrekende of verkeerde producten

Mail ons binnen 7 dagen met je bestelnummer, wat er ontbreekt of fout is, en foto's. Hou een verkeerd product ongeopend. We sturen wat je bestelde of betalen het terug, en als de fout bij ons ligt, betalen wij ook het terugsturen.

### Verloren tijdens transport

Is je pakket 10 werkdagen na de laatste geschatte leverdatum nog niet aangekomen, of beweegt de tracking al 7 werkdagen niet? Mail ons en we starten een onderzoek bij de vervoerder. Bevestigt de vervoerder dat het pakket verloren is, dan sturen we je bestelling opnieuw of betalen we alles terug.

### Teruggestuurd naar afzender

Pakketten komen terug als het adres fout of onvolledig is, levering niet lukt, het pakket niet wordt opgehaald of invoerkosten worden geweigerd. We nemen dan contact met je op: we sturen opnieuw zodra de nieuwe verzending betaald is, of we betalen je bestelling terug min de verzending heen en terug.

## Retour

### Herroepingsrecht: 14 dagen bedenktijd

Koop je als consument, dan mag je tot 14 dagen na levering zonder reden van je aankoop afzien. Laat het ons binnen die 14 dagen weten per e-mail aan [{email}](mailto:{email}), met je bestelnummer. Stuur de producten daarna binnen 14 dagen terug. De kosten van het terugsturen zijn voor jou.

We betalen het bedrag van de producten en de oorspronkelijke standaard verzendkosten terug binnen 14 dagen na je melding. We mogen wachten met terugbetalen tot de producten terug zijn.

Producten moeten compleet en in dezelfde staat terugkomen. Een verzegelde box of pakje openen is meer dan nodig om het product te bekijken: bij een geopende box, geopende booster packs of een beschadigde verzegeling mogen we de waardevermindering inhouden. Losse kaarten en graded slabs komen terug in dezelfde sleeve of slab, met hetzelfde certificaatnummer.

### Zakelijke kopers

Koop je voor je bedrijf, dan geldt het herroepingsrecht niet. Retour kan dan in overleg: onze fouten lossen we altijd kosteloos op.

## Terugbetalingen

Terugbetalingen gebeuren op dezelfde manier als je betaalde:

- **Bitcoin en andere crypto:** naar een walletadres dat je per e-mail bevestigt, voor het bedrag in euro van de terugbetaalde producten, omgerekend aan de koers op de dag van terugbetaling. Netwerkkosten gaan van het verzonden bedrag af.

Je wallet kan er even over doen om het te tonen.

## Annuleren en pre-orders

Je kan gratis annuleren tot je bestelling verzonden is: mail ons met je bestelnummer. Daarna geldt de retourregeling hierboven.

Pre-orders worden gefactureerd wanneer de voorraad wordt toegewezen, niet wanneer je bestelt, en we verzenden zodra de voorraad binnen is, meestal op of net na de Japanse releasedatum. Releasedatums worden bepaald door The Pokémon Company en schuiven soms op. Kunnen we een pre-order niet leveren, dan betalen we alles terug.

## Garantie en echtheid

Alles wat we verkopen is echt, ingekocht via Japanse distributie en verzonden zoals het uit de fabriek kwam. Als consument heb je bovendien de wettelijke garantie van 2 jaar op gebreken die er bij levering al waren. Twijfel je over een product? Bewaar het zoals het aankwam en mail ons met foto's.

## Veelgestelde vragen

### Verzenden jullie Pokémon kaarten naar België?

Ja. Alles vertrekt vanuit Japan en wordt met tracking geleverd in heel België: {standard} duurt {standard_days}, {express} duurt {express_days}.

### Is verzending gratis?

Vanaf {free_ship} aan goederen verzenden we gratis met {standard}. {express} kost dan alleen het verschil.

### Hoeveel kost verzending?

Dat hangt af van het gewicht van je bestelling. De exacte prijs zie je bij het afrekenen, voor je bestelt. De tabel op deze pagina toont hoe we rekenen.

### Betaal ik btw of invoerkosten?

Wij rekenen geen btw of andere belastingen aan. Zoals bij elk pakket van buiten de EU kan de vervoerder bij sommige bestellingen invoerkosten vragen; die betaal je aan de vervoerder.

### Kan ik mijn bestelling terugsturen?

Ja. Als consument heb je 14 dagen bedenktijd na levering. Geopende boxen en packs kunnen terug, maar dan mogen we de waardevermindering inhouden.

### Mijn bestelling is beschadigd aangekomen. Wat nu?

Bewaar het pakket, de verpakking en de producten, maak foto's en mail ons binnen 7 dagen na levering. We vervangen of betalen terug.

### Hoe volg ik mijn bestelling?

We mailen je trackingnummer zodra je bestelling vertrekt. Een nieuw trackingnummer toont soms pas na 24 tot 48 uur de eerste scan.

## Contact

Mail [{email}](mailto:{email}) met je bestelnummer, je trackingnummer als je het hebt, wat er misging en foto's waar dat helpt. We antwoorden binnen {reply_hours} uur, in het Nederlands, Frans of Engels.

{company} · {address}
MD,
  ],

  /* delivery options: the name and the working days (a number range is shown as "… werkdagen") */
  'methods' => [
    'standard' => ['label'=>'Standaard', 'days'=>'5–9'],
    'express'  => ['label'=>'Express', 'days'=>'2–4'],
  ],

  'payments' => [
    'bitcoin' => ['label'=>'Bitcoin (BTC): nu betalen', 'note'=>'Betaal meteen na je bestelling vanuit elke Bitcoin-wallet. Op de volgende pagina zie je het exacte bedrag en een QR-code.'],
    'crypto'  => ['label'=>'Andere crypto (ETH, USDT)', 'note'=>'ETH of USDT (TRC-20 of ERC-20). We mailen je ons walletadres met je factuur. Netwerkkosten zijn voor de verzender.'],
  ],

  /* h1 and seo_title fall back to the label, seo_desc to the blurb */
  'categories' => [
    'boxes' => [
      'label' => 'Booster boxes', 'slug' => 'pokemon-booster-box',
      'blurb' => 'Verzegelde Japanse Pokémon booster boxes en cases, rechtstreeks uit Japan.',
      'h1' => 'Pokémon booster boxes (Japans)',
      'seo_title' => 'Pokémon booster box kopen: Japanse booster boxes',
      'seo_desc' => 'Japanse Pokémon booster boxes kopen: 151, Terastal Festival ex, 30th Celebration en de nieuwste Mega Evolution sets. Verzegeld uit Japan, prijzen in euro.',
      'intro' => <<<'MD'
## Wat is een Pokémon booster box?

Een Pokémon booster box (ook wel box booster of booster display) is een verzegelde doos met booster packs uit één set. Het is de voordeligste manier om Pokémon boosters te kopen: per pakje betaal je minder dan los, en je hebt de beste kans op de zeldzame kaarten van de set.

Japanse booster boxes zijn kleiner dan Engelse. De meeste Japanse boxen van een hoofdset bevatten 30 packs van 5 kaarten. Speciale sets wijken af: een [151 booster box](product:151-booster-box) bevat 20 packs van 7 kaarten, en een [Terastal Festival ex box](product:terastal-festival-booster-box) 10 packs van 10 kaarten.

## Booster boxes op voorraad

We verkopen de Japanse [Mega Evolution sets](set:mega-evolution), van [Mega Brave](set:mega-brave) tot [Storm Emeralda](set:storm-emeralda), naast favorieten uit [Scarlet & Violet](set:scarlet-violet) zoals [151](set:151) en [Glory of Team Rocket](set:glory-of-team-rocket). Voor grote kopers zijn er verzegelde cases van 12 boxen. Hoe meer boxen je bestelt, hoe lager de prijs per box: de volledige staffel staat op elke productpagina.

## Pokémon boosters los of per box?

Losse booster packs zijn leuk om te openen, maar een box geeft je meer packs voor je geld en een eerlijke kans op de chase cards. Alles over packs, boxen en wat erin zit lees je in onze gids [Pokémon boosters uitgelegd](guide:boosters). Nieuw in Japanse producten? Lees [waarom verzamelaars Japanse Pokémon kaarten kopen](guide:japanese).
MD,
    ],
    'etb' => [
      'label' => 'Elite Trainer Boxes', 'slug' => 'elite-trainer-box',
      'blurb' => 'Elite Trainer Boxes en cases, met booster packs, sleeves en accessoires.',
      'h1' => 'Pokémon Elite Trainer Boxes (ETB)',
      'seo_title' => 'Pokémon Elite Trainer Box (ETB) kopen',
      'seo_desc' => 'Pokémon Elite Trainer Boxes kopen: 30th Celebration, Perfect Order, Ascended Heroes, Chaos Rising en meer, met booster packs, sleeves en dobbelstenen.',
      'intro' => <<<'MD'
## Wat zit er in een Elite Trainer Box?

Een Elite Trainer Box (ETB) combineert booster packs met alles om te spelen: kaartsleeves, dobbelstenen, schadefiches, energiekaarten en een stevige opbergdoos. Daarom is een ETB een van de populairste Pokémon cadeaus, en een vaste waarde in elke kaartenwinkel.

We hebben ETB's uit de Mega Evolution-reeks, zoals [Perfect Order](product:perfect-order-elite-trainer-box), [Ascended Heroes](product:mega-evolution-ascended-heroes-elite-trainer-box), [Chaos Rising](product:chaos-rising-elite-trainer-box) en [Pitch Black](product:pitch-black-elite-trainer-box), plus de [30th Celebration ETB](product:30th-celebration-elite-trainer-box) en volledige cases van 10.

ETB's verkopen we per vier, of per case van 10, met lagere prijzen vanaf 24 stuks. Wil je beginnen met spelen? Lees [hoe het Pokémon TCG werkt](guide:tcg).
MD,
    ],
    'premium' => [
      'label' => 'Pokémon boxen & decks', 'slug' => 'pokemon-box',
      'blurb' => 'Collection boxes, starter decks, premium sets en 30th Celebration specials.',
      'h1' => 'Pokémon boxen, starter decks en premium sets',
      'seo_title' => 'Pokémon box kopen: collection boxes & starter decks',
      'seo_desc' => 'Pokémon boxen kopen: collection boxes met promokaart, Starter Set ex decks, Premium Trainer Box MEGA en 30th Celebration specials, verzonden vanuit Japan.',
      'intro' => <<<'MD'
## Welke Pokémon box past bij jou?

Niet elke Pokémon box is een booster box. Collection boxes zoals de [30th Celebration Greninja ex Box](product:30th-celebration-greninja-ex-box) en de [Sylveon ex Box](product:30th-celebration-sylveon-ex-box) combineren booster packs met een promokaart. Een [Starter Set ex](product:starter-set-ex-eevee-ex) of de [MEGA Start Deck 100 Battle Collection](product:mega-start-deck-100-battle-collection) geeft nieuwe spelers meteen een speelklaar deck.

Voor verzamelaars is er de [30th Celebration Premium Deck Set](product:30th-celebration-premium-deck-set-espeon-and-umbreon) rond Espeon en Umbreon. Starter decks zijn de makkelijkste manier om het Pokémon kaartspel te leren, en een goedkope extra voor kaartenwinkels.
MD,
    ],
    'singles' => [
      'label' => 'Losse kaarten', 'slug' => 'losse-pokemon-kaarten',
      'blurb' => 'Zeldzame Japanse losse kaarten: Special Illustration Rares, full art, gold en PSA graded.',
      'h1' => 'Zeldzame Pokémon kaarten: Japanse losse kaarten',
      'seo_title' => 'Zeldzame Pokémon kaarten kopen: losse Japanse kaarten',
      'seo_desc' => 'Zeldzame losse Pokémon kaarten: Special Illustration Rares, full art en gold kaarten van Charizard, Pikachu, Gengar en Mega Rayquaza. Near Mint of PSA graded.',
      'intro' => <<<'MD'
## Zeldzame en dure Pokémon kaarten

Onze losse kaarten zijn de zeldzame top van elke set: Special Illustration Rares met full art, gouden Hyper Rares en de hoogste zeldzaamheid van de Mega Evolution-reeks, zoals de [Mega Rayquaza ex Master Ultra Rare](product:mega-rayquaza-ex-mur). Je vindt er [Charizard kaarten](cards:charizard), [Pikachu kaarten](cards:pikachu), [Gengar](cards:gengar) en een [PSA 10 graded Pikachu](cards:psa).

Ongegradeerde kaarten zijn Near Mint en gaan op in een sleeve en toploader. Wil je weten wat een kaart waard is? Onze gids [Pokémon kaarten waarde](guide:prices) legt uit wat de prijs bepaalt en toont al onze actuele prijzen.
MD,
    ],
    'accessories' => [
      'label' => 'Accessoires', 'slug' => 'pokemon-accessoires',
      'blurb' => 'Verzamelmappen, sleeves, deck boxes, playmats en opbergdozen.',
      'h1' => 'Pokémon verzamelmappen, sleeves en deck boxes',
      'seo_title' => 'Pokémon verzamelmap, sleeves & deck boxes',
      'seo_desc' => 'Pokémon verzamelmappen, kaartsleeves, deck boxes, playmats en opbergdozen, zoals de 9-pocket Mega Evolution map en Ultra PRO Pikachu sleeves.',
      'intro' => <<<'MD'
## Je Pokémon kaarten goed bewaren

Een goede verzamelmap en de juiste sleeves houden je kaarten in Near Mint staat. Onze [9-pocket Pokémon map](product:pokemon-tcg-9-pocket-binder-mega-evolution-series) bewaart negen kaarten per pagina, en onze sleeves gaan van [Ultra PRO Pikachu Deck Protectors](product:ultra-pro-pikachu-deck-protector-sleeves-65-ct) tot designs met [Celebi en Furret](product:celebi-and-furret-deck-sleeves) of [Mega Rayquaza](product:storm-emeralda-mega-rayquaza-deck-sleeves).

Pokémon kaarten meten 63 × 88 mm, Japanse en Engelse kaarten zijn even groot, dus standaard sleeves passen. Accessoires verkopen we per verpakking van meerdere stuks, met lagere prijzen bij grotere aantallen.
MD,
    ],
  ],

  'series' => [
    'mega' => [
      'name' => 'Mega Evolution',
      'h1' => 'Pokémon Mega Evolution sets (Japans)',
      'seo_title' => 'Pokémon Mega Evolution sets & booster boxes (Japans)',
      'seo_desc' => 'Alle Japanse Pokémon Mega Evolution sets: Mega Brave, Mega Symphonia, Inferno X, Mega Dream ex, Nihil Zero, Ninja Spinner, Abyss Eye, Storm Emeralda en meer.',
      'intro' => <<<'MD'
Mega Evolution kwam in 2025 terug in de Pokémon Trading Card Game, in Japan gelanceerd met de tweelingsets [Mega Brave](set:mega-brave) en [Mega Symphonia](set:mega-symphonia). Elke set sindsdien bracht nieuwe Mega Pokémon ex, van Mega Gengar ex in [Mega Dream ex](set:mega-dream-ex) tot Mega Rayquaza ex in [Storm Emeralda](set:storm-emeralda).

Hieronder vind je elke Mega Evolution set die we verkopen, met verzegelde booster boxes, Elite Trainer Boxes en de belangrijkste losse kaarten. Japanse sets verschijnen eerst, dus deze boosters zijn er ruim voor de Engelse versie. Welke set komt eraan? Zie [nieuwe Pokémon sets](guide:releases).
MD,
    ],
    'sv' => [
      'name' => 'Scarlet & Violet',
      'h1' => 'Pokémon Scarlet & Violet sets (Japans)',
      'seo_title' => 'Pokémon Scarlet & Violet sets: Japanse booster boxes',
      'seo_desc' => 'Japanse Pokémon Scarlet & Violet sets: 151, Terastal Festival ex, Heat Wave Arena en Glory of Team Rocket booster boxes en losse kaarten.',
      'intro' => <<<'MD'
De Scarlet & Violet-reeks liep van 2023 tot de start van Mega Evolution in 2025 en leverde enkele van de meest verzamelde Japanse sets ooit, zoals [151](set:151), met de originele 151 Pokémon, en [Terastal Festival ex](set:terastal-festival-ex).

We hebben nog verzegelde Scarlet & Violet booster boxes uit Japan, waaronder [Heat Wave Arena](set:heat-wave-arena) en [Glory of Team Rocket](set:glory-of-team-rocket). Zodra deze sets niet meer gedrukt worden, worden verzegelde boxen schaarser.
MD,
    ],
  ],

  /* keyed by the set name used on products */
  'sets' => [
    'Aura Seeker (Hadou Seeker)' => ['intro'=>'Aura Seeker (Hadou Seeker) is een aankomende Japanse set uit de Mega Evolution-reeks. Reserveer nu je booster boxes: pre-orders worden pas gefactureerd wanneer de voorraad wordt toegewezen.'],
    'Storm Emeralda' => ['intro'=>'Storm Emeralda (M6) is de Japanse Mega Evolution-set rond Mega Rayquaza ex. We verkopen verzegelde Storm Emeralda booster boxes, cases van 12 boxen en Elite Trainer Boxes, de Mega Rayquaza ex Master Ultra Rare en Special Illustration Rare, en bijpassende Mega Rayquaza sleeves.'],
    '30th Celebration' => ['intro'=>"30th Celebration viert in 2026 de 30ste verjaardag van Pokémon. Mewtwo ex en Mew ex zijn de sterren van de set, samen met Umbreon ex, Salamence ex en Greninja ex, en in elk booster pack zit een Pikachu: er zijn 30 verschillende zeldzame Pikachu kaarten te verzamelen.\n\nHet aanbod omvat booster boxes, [Elite Trainer Boxes](product:30th-celebration-elite-trainer-box) en cases, de Greninja ex en Sylveon ex boxen, de Espeon & Umbreon Premium Deck Set en een Tech Sticker Collection."],
    'Abyss Eye' => ['intro'=>'Abyss Eye (M5) is de Japanse Mega Evolution-set met Mega Darkrai ex en Mega Excadrill ex. We hebben verzegelde Abyss Eye booster boxes en Elite Trainer Boxes, de Pitch Black Elite Trainer Box, en Mega Darkrai ex en Mega Excadrill ex als losse kaart.'],
    'Ninja Spinner' => ['intro'=>'Ninja Spinner (M4) brengt Mega Greninja ex en Mega Floette ex naar de Mega Evolution-reeks. Op voorraad: verzegelde Ninja Spinner booster boxes (beperkt), de Chaos Rising Elite Trainer Box, en Mega Greninja ex en Mega Floette ex als losse kaart.'],
    'Nihil Zero' => ['intro'=>'Nihil Zero (M3) hoort bij de Japanse Mega Evolution-reeks. We verkopen verzegelde Nihil Zero booster boxes en de Perfect Order Elite Trainer Box.'],
    'Mega Dream ex' => ['intro'=>'Mega Dream ex (M2a) is de speciale set van de Mega Evolution-reeks en de thuisbasis van de Mega Gengar ex Special Illustration Rare. Op voorraad: verzegelde Mega Dream ex booster boxes, de Ascended Heroes Elite Trainer Box en de Mega Gengar ex SIR zelf.'],
    'Inferno X' => ['intro'=>'Inferno X (M2) is de tweede Japanse hoofdset van de Mega Evolution-reeks, hier te koop als verzegelde booster box.'],
    'Mega Symphonia' => ['intro'=>'Mega Symphonia (M1S) is een van de twee sets waarmee de Mega Evolution-reeks in 2025 in Japan begon, samen met Mega Brave.'],
    'Mega Brave' => ['intro'=>'Mega Brave (M1L) opende in 2025 de Japanse Mega Evolution-reeks, samen met tweelingset Mega Symphonia.'],
    'Glory of Team Rocket' => ['intro'=>'Glory of Team Rocket (SV10) brengt de Pokémon van Team Rocket terug in het Pokémon TCG en is een van de meest gevraagde Japanse Scarlet & Violet sets. Te koop als verzegelde booster box.'],
    'Heat Wave Arena' => ['intro'=>'Heat Wave Arena (SV9a) is een Japanse Scarlet & Violet uitbreiding, te koop als verzegelde booster box.'],
    'Terastal Festival ex' => ['intro'=>'Terastal Festival ex (SV8a) is de speciale Scarlet & Violet set rond Terastal Pokémon en alle evoluties van Eevee, in het Engels uitgebracht als Prismatic Evolutions. Japanse boxen bevatten 10 packs van 10 kaarten.'],
    '151' => [
      'seo_title'=>'Pokémon 151 kaarten: Japanse 151 booster box & Charizard',
      'seo_desc'=>'Japanse Pokémon 151 kaarten (SV2a): verzegelde 151 booster boxes en de Charizard ex Special Illustration Rare, verzonden vanuit Japan naar België.',
      'intro'=>"Pokémon Card 151 (SV2a) is de Japanse speciale set rond de originele 151 Pokémon uit Red en Green, van Bulbasaur tot Mew. Het is een van de meest verzamelde sets uit de Scarlet & Violet-periode, in het Engels verschenen als Scarlet & Violet—151. Een Japanse 151 booster box bevat 20 packs van 7 kaarten, en een van de chase cards is de Charizard ex Special Illustration Rare.\n\n## Pokémon 151 kaartenlijst\n\nDe Japanse 151 (verschenen op 16 juni 2023) telt 210 kaarten: een basisset van 165 kaarten met de originele 151 Pokémon plus Trainer- en Energiekaarten, en 45 secret rares: 18 Art Rares (AR), 16 Super Rares (SR), 8 Special Art Rares (SAR) en 3 gouden Ultra Rares (UR). Alle sets op een rij vind je in onze [lijst van Pokémon sets](guide:sets).\n\nWe verkopen verzegelde 151 booster boxes en de 151 Charizard ex SAR. Meer Charizard? Zie al onze [Charizard Pokémon kaarten](cards:charizard)."],
  ],

  /* names and descriptions per product id; the first paragraph of a description is the summary under the title */
  'products' => [
    'storm-emeralda-elite-trainer-box' => ['name'=>'Storm Emeralda Elite Trainer Box', 'desc'=>"Een verzegelde Storm Emeralda Elite Trainer Box uit de Japanse Mega Evolution-reeks.\n\nIn de box zitten booster packs, kaartsleeves, dobbelstenen, schadefiches, energiekaarten en de promokaart van de set, in een opbergdoos: alles om Storm Emeralda te openen en te spelen. Loopt goed naast de [Storm Emeralda booster boxes](product:storm-emeralda-m6-booster-box).\n\n- Verzegelde Storm Emeralda Elite Trainer Box\n- Booster packs, sleeves, dobbelstenen, schadefiches, energiekaarten en promo\n- Per zes, met lagere prijzen vanaf 18 en 36"],
    '30th-celebration-m6a-booster-box' => ['name'=>'30th Celebration (M6A) booster box (Japans)', 'desc'=>"Een verzegelde Japanse booster box van 30th Celebration (M6A), de set voor de 30ste verjaardag van Pokémon.\n\n30th Celebration brengt klassieke Pokémon illustraties terug naast nieuwe kaarten en heeft een hoge pull rate. Daarom zijn deze jubileumboxen geliefd bij verzamelaars en bij wie graag boxen opent. Open ze voor de kaarten, of bewaar ze verzegeld als verzamelobject.\n\n- Verzegelde Japanse booster box: 30th Celebration (M6A)\n- Klassieke illustraties opnieuw uitgebracht, hoge pull rate\n- Lagere prijzen vanaf 12 en 24 boxen\n- Meer uit de jubileumreeks: de [30th Celebration Elite Trainer Box](product:30th-celebration-elite-trainer-box) en [speciale boxen](set:30th-celebration)"],
    'mega-rayquaza-ex-mur' => ['name'=>'Mega Rayquaza ex Master Ultra Rare (Japans)', 'desc'=>"De Japanse Mega Rayquaza ex Master Ultra Rare uit Storm Emeralda: de zeldzaamste chase card van de set.\n\nRayquaza is sinds zijn eerste kaarten een van de meest verzamelde legendarische Pokémon, en zijn Master Ultra Rare staat helemaal bovenaan de zeldzaamheden van Storm Emeralda. Elk exemplaar is Near Mint en gaat op in een sleeve en toploader.\n\n- Japans, Storm Emeralda (M6)\n- Zeldzaamheid: Master Ultra Rare\n- Near Mint, in sleeve en toploader\n- Lagere prijzen vanaf 3 en 6 exemplaren"],
    'mega-gengar-ex-sir' => ['name'=>'Mega Gengar ex Special Illustration Rare (Japans)', 'desc'=>"De Japanse Mega Gengar ex Special Illustration Rare uit Mega Dream ex.\n\nGengar heeft een van de trouwste fanbases in de hobby, en deze Special Illustration Rare is een van de mooiste Gengar Pokémon kaarten van de Mega Evolution-reeks. Near Mint, in sleeve en toploader.\n\n- Japans, Mega Dream ex (M2A)\n- Special Illustration Rare (full art)\n- Lagere prijzen vanaf 3 en 6 exemplaren\n- Alle [Gengar Pokémon kaarten](cards:gengar)"],
    '30th-celebration-elite-trainer-box' => ['name'=>'30th Celebration Elite Trainer Box', 'desc'=>"De Pokémon TCG: 30th Celebration Elite Trainer Box, met negen booster packs van de jubileumset en alles om ermee te spelen.\n\n30th Celebration viert dertig jaar Pokémon: Mewtwo ex en Mew ex voeren de set aan, samen met Umbreon ex, Salamence ex en Greninja ex, en in elk booster pack zit een Pikachu, met 30 verschillende zeldzame Pikachu kaarten. Koop je in volume? Zie de [verzegelde case van 10](product:30th-celebration-elite-trainer-box-case-10-ct).\n\n- 9 Pokémon TCG: 30th Celebration booster packs\n- 1 full-art foil promokaart met Nidorina\n- 16 foil basis-energiekaarten en 65 kaartsleeves\n- Een spelersgids voor 30th Celebration\n- 6 schadedobbelstenen, 1 toernooidobbelsteen en 1 plastic munt\n- Een verzameldoos met 6 tussenschotten, plus een codekaart voor Pokémon TCG Live\n- Per vier, met de laagste prijs vanaf 24"],
    'storm-emeralda-m6-booster-box' => ['name'=>'Storm Emeralda (M6) booster box (Japans)', 'desc'=>"Een verzegelde Japanse booster box van Storm Emeralda (M6), de Mega Evolution-set met Mega Rayquaza ex als ster.\n\nDe chase cards van Storm Emeralda zijn onder andere de [Mega Rayquaza ex Master Ultra Rare](product:mega-rayquaza-ex-mur) en de [Special Illustration Rare](product:mega-rayquaza-ex-sar-245-191-near-mint), waardoor verzegelde boxen gewild blijven. Bestel per zes, of neem een [verzegelde case van 12](product:storm-emeralda-booster-box-case-12-ct) voor de laagste prijs per box.\n\n- Verzegelde Japanse Mega Evolution booster box (M6)\n- Chase cards: Mega Rayquaza ex Master Ultra Rare en Special Illustration Rare\n- Beste prijs per box vanaf 24 boxen"],
    'abyss-eye-elite-trainer-box' => ['name'=>'Abyss Eye Elite Trainer Box', 'desc'=>"Een verzegelde Abyss Eye Elite Trainer Box uit de Japanse Mega Evolution-reeks.\n\nBooster packs met sleeves, dobbelstenen, schadefiches en de promokaart in een opbergdoos: de makkelijke manier om Abyss Eye te openen naast de [booster boxes](product:abyss-eye-m5-booster-box).\n\n- Verzegelde Abyss Eye Elite Trainer Box\n- Per zes, met lagere prijzen vanaf 18 en 36"],
    '30th-celebration-greninja-ex-box' => ['name'=>'30th Celebration Greninja ex Box', 'desc'=>"Een 30th Celebration collection box rond een Greninja ex promokaart, met booster packs.\n\nCollection boxes als deze verkopen goed per stuk en als cadeau, en de prijzen vanaf 18 en 36 boxen laten winkels marge. Past perfect naast de [Sylveon ex Box](product:30th-celebration-sylveon-ex-box).\n\n- Greninja ex promokaart plus booster packs\n- Deel van de [30th Celebration](set:30th-celebration) reeks\n- Per zes, met lagere prijzen vanaf 18 en 36"],
    'heat-wave-arena-sv9a-booster-box' => ['name'=>'Heat Wave Arena (SV9a) booster box (Japans)', 'desc'=>"Een verzegelde Japanse booster box van Heat Wave Arena (SV9a) uit de Scarlet & Violet-reeks.\n\nHeat Wave Arena is een betrouwbare herbestelling voor kaartenwinkels met drafts en speelavonden, en een vaste waarde voor verzamelaars van Japanse Scarlet & Violet producten.\n\n- Verzegelde Japanse booster box: Heat Wave Arena (SV9a)\n- Deel van de [Scarlet & Violet-reeks](set:scarlet-violet)\n- Lagere prijzen vanaf 18 en 36 boxen"],
    'glory-of-team-rocket-sv10-booster-box' => ['name'=>'Glory of Team Rocket (SV10) booster box (Japans)', 'desc'=>"Een verzegelde Japanse booster box van Glory of Team Rocket (SV10): de Pokémon van Team Rocket zijn terug in het TCG.\n\nGlory of Team Rocket is een van de meest gevraagde Japanse Scarlet & Violet sets, gebouwd rond de Pokémon van Team Rocket. De voorraad gaat snel.\n\n- Verzegelde Japanse booster box: Glory of Team Rocket (SV10)\n- Deel van de [Scarlet & Violet-reeks](set:scarlet-violet)\n- Lagere prijzen vanaf 18 en 36 boxen"],
    'mega-start-deck-100-battle-collection' => ['name'=>'MEGA Start Deck 100 Battle Collection', 'desc'=>"De MEGA Start Deck 100 Battle Collection: speelklare Pokémon TCG decks voor nieuwe spelers.\n\nStart decks zijn de makkelijkste manier om het Pokémon kaartspel te leren: openen en spelen, zonder zelf een deck te bouwen. Voor kaartenwinkels zijn ze een goedkoop instapproduct voor speelavonden en beginnersevents.\n\n- Speelklare decks uit de Mega Evolution-periode\n- Per case van 12, met de laagste prijs vanaf 72"],
    'starter-set-ex-zorua-and-zoroark-ex' => ['name'=>'Starter Set ex: Zorua & Zoroark ex', 'desc'=>"Starter Set ex met een speelklaar deck rond Zorua en Zoroark ex.\n\nEen compleet deck rond Zoroark ex, meteen klaar om te spelen: een goed eerste deck voor nieuwe spelers en een makkelijke extra naast booster boxes.\n\n- Speelklaar Zorua & Zoroark ex deck\n- Per case van 12, met de laagste prijs vanaf 72"],
    'premium-trainer-box-mega' => ['name'=>'Premium Trainer Box MEGA', 'desc'=>"Premium Trainer Box MEGA: een premium trainer box uit de Mega Evolution-periode van het Pokémon TCG.\n\nPremium trainer boxes combineren booster packs met speelaccessoires voor wie zijn eerste Mega Evolution decks bouwt, en zijn een sterk cadeau.\n\n- Premium trainer box uit de Mega Evolution-periode\n- Per vier, met de laagste prijs per stuk vanaf 24"],
    '151-booster-box' => ['name'=>'Pokémon 151 booster box (Japans, SV2a)', 'desc'=>"Een verzegelde Japanse booster box van 151 (SV2a): 20 packs van 7 kaarten uit de set rond de originele 151 Pokémon.\n\nPokémon 151 kaarten horen bij de meest verzamelde van de Scarlet & Violet-periode: de originele Pokémon van Bulbasaur tot Mew, in moderne illustraties met full art rares. De [Charizard ex Special Illustration Rare](product:151-charizard-ex-special-illustration-rare) is de chase card. 151 verscheen later in het Engels als Scarlet & Violet—151, maar de Japanse versie kwam eerst en veel verzamelaars verkiezen ze.\n\n- Verzegelde Japanse 151 booster box (SV2a): 20 packs van 7 kaarten\n- Chase card: Charizard ex Special Illustration Rare\n- Per zes, met de laagste prijs vanaf 36 boxen\n- Nieuw in Japanse boxen? Lees [wat Japanse Pokémon kaarten anders maakt](guide:japanese)"],
    '151-charizard-ex-special-illustration-rare' => ['name'=>'Charizard ex 151 Special Illustration Rare (Japans)', 'desc'=>"De Japanse Charizard ex Special Illustration Rare uit 151 (SV2a).\n\nCharizard is een van de meest verzamelde Pokémon in het TCG, en zijn 151 Special Illustration Rare is een van de bepalende Charizard kaarten van de Scarlet & Violet-periode. Near Mint, in sleeve en toploader.\n\n- Japans, 151 (SV2a)\n- Special Illustration Rare (full art)\n- Lagere prijs vanaf 6 exemplaren\n- Meer [Charizard Pokémon kaarten](cards:charizard)"],
    'storm-emeralda-booster-box-case-12-ct' => ['name'=>'Storm Emeralda booster box case (12 boxen)', 'desc'=>"Een verzegelde case met 12 Japanse Storm Emeralda (M6) booster boxes, geprijsd per case.\n\nVoor winkels en box breakers die Storm Emeralda in volume inslaan, is een verzegelde case de eenvoudigste aankoop: twaalf boxen in één verzegelde doos, goedkoper per box dan los, en nog goedkoper vanaf zes cases.\n\n- 12 verzegelde Japanse Storm Emeralda (M6) booster boxes per case\n- Lagere prijs per box dan los, met extra korting vanaf 6 cases\n- Chase card: [Mega Rayquaza ex Master Ultra Rare](product:mega-rayquaza-ex-mur)"],
    'aura-seeker-booster-box' => ['name'=>'Aura Seeker (Hadou Seeker) booster box (Japans, pre-order)', 'release'=>'november 2026', 'desc'=>"Reserveer de Japanse booster box van Aura Seeker (Hadou Seeker), een aankomende set uit de Mega Evolution-reeks.\n\nMet een pre-order reserveer je je toewijzing voor de release, aan de staffelprijzen hieronder. Je krijgt pas een factuur wanneer de voorraad wordt toegewezen, niet wanneer je bestelt, en de boxen vertrekken vanuit Japan zodra ze uit en betaald zijn.\n\n- Verzegelde Japanse booster box, pre-order\n- Vandaag niets te betalen: factuur bij toewijzing\n- Per zes, met de laagste prijs vanaf 36 boxen"],
    'starter-set-ex-eevee-ex' => ['name'=>'Starter Set ex: Eevee ex', 'desc'=>"Starter Set ex met een speelklaar deck rond Eevee ex.\n\nEen compleet deck rond Eevee ex, meteen klaar om te spelen: een makkelijke eerste aankoop voor nieuwe spelers en een populair cadeau.\n\n- Speelklaar Eevee ex deck\n- Per case van 10, met de laagste prijs vanaf 60"],
    'pokemon-tcg-card-storage-box-booster-box-display' => ['name'=>'Pokémon TCG opbergdoos in booster box-stijl', 'desc'=>"Een opbergdoos voor Pokémon kaarten in de stijl van een booster box display.\n\nDe doos houdt kaarten met of zonder sleeve recht en plat op een plank of toonbank, en is meteen een mooi displaystuk.\n\n- Opbergdoos in booster box-stijl\n- Per zes, met de laagste prijs vanaf 36"],
    'pokemon-tcg-9-pocket-binder-mega-evolution-series' => ['name'=>'Pokémon verzamelmap 9-pocket: Mega Evolution', 'desc'=>"Een Pokémon verzamelmap met 9 vakjes per pagina en Mega Evolution-artwork.\n\nElke pagina bewaart negen standaard Pokémon kaarten: de eenvoudigste manier om een groeiende verzameling te ordenen en je full art pulls te tonen. Kaarten met één sleeve passen er ruim in.\n\n- Verzamelmap met 9 vakjes per pagina\n- Mega Evolution artwork\n- Per zes, met de laagste prijs vanaf 36"],
    'celebi-and-furret-deck-sleeves' => ['name'=>'Celebi & Furret deck sleeves', 'desc'=>"Deck sleeves met Celebi en Furret, op maat van standaard Pokémon kaarten.\n\nSleeves beschermen kaarten tijdens het spelen en bij het bewaren, en designs met personages zijn een populaire extra aan de kassa.\n\n- Past op standaard Pokémon kaarten van 63 × 88 mm\n- Per 24, met de laagste prijs vanaf 144"],
    'pikachu-ditto-ver-deck-sleeves' => ['name'=>'Pikachu (Ditto versie) deck sleeves', 'desc'=>"Deck sleeves met Pikachu in de Ditto-versie, op maat van standaard Pokémon kaarten.\n\nSleeves beschermen kaarten tijdens het spelen en bij het bewaren, en designs met personages zijn een populaire extra aan de kassa.\n\n- Past op standaard Pokémon kaarten van 63 × 88 mm\n- Per 24, met de laagste prijs vanaf 144"],
    'storm-emeralda-mega-rayquaza-deck-sleeves' => ['name'=>'Storm Emeralda Mega Rayquaza deck sleeves', 'desc'=>"Deck sleeves met Mega Rayquaza uit de Storm Emeralda release, op maat van standaard Pokémon kaarten.\n\nSleeves beschermen kaarten tijdens het spelen en bij het bewaren. Ze zijn de logische extra bij [Storm Emeralda booster boxes](product:storm-emeralda-m6-booster-box).\n\n- Past op standaard Pokémon kaarten van 63 × 88 mm\n- Per 24, met de laagste prijs vanaf 144"],
    'ultra-pro-pikachu-alcove-tower-deck-box' => ['name'=>'Ultra PRO Pikachu Alcove Tower deck box', 'desc'=>"Een Ultra PRO Alcove Tower deck box met Pikachu-artwork.\n\nEen stevige deck box om een deck met sleeves mee te nemen naar speelavonden en toernooien, met een Pikachu die zichzelf verkoopt.\n\n- Ultra PRO Alcove Tower deck box\n- Pikachu artwork\n- Per 12, met de laagste prijs vanaf 72"],
    'ultra-pro-pikachu-deck-protector-sleeves-65-ct' => ['name'=>'Ultra PRO Pikachu Deck Protector sleeves (65 stuks)', 'desc'=>"Ultra PRO Deck Protector sleeves met Pikachu-artwork: 65 sleeves voor standaard Pokémon kaarten per verpakking.\n\nSleeves van standaardformaat passen op Pokémon kaarten, zowel Japanse als Engelse, om mee te spelen en te bewaren.\n\n- 65 sleeves per verpakking\n- Past op standaard Pokémon kaarten van 63 × 88 mm\n- Per 24, met de laagste prijs vanaf 144"],
    'pokemon-playmat-assorted-designs' => ['name'=>'Pokémon playmat: diverse designs', 'desc'=>"Pokémon playmats in diverse designs.\n\nEen playmat beschermt je kaarten tijdens het spelen en markeert de speelzone: een populaire extra voor spelers en een vaste verkoper in winkels. De mix van designs verschilt per zending.\n\n- Diverse Pokémon designs\n- Minimum 10, daarna per vijf, met de laagste prijs vanaf 50"],
    'pokemon-deck-box-assorted' => ['name'=>'Pokémon deck box: diverse designs', 'desc'=>"Pokémon deck boxes in diverse designs.\n\nEen deck box houdt een deck met sleeves veilig in je tas of zak, en verkoopt vlot naast sleeves en playmats. De mix van designs verschilt per zending.\n\n- Diverse Pokémon designs\n- Minimum 20, daarna per tien, met de laagste prijs vanaf 100"],
    'pokemon-card-sleeves-64-ct-assorted-designs' => ['name'=>'Pokémon kaartsleeves (64 stuks): diverse designs', 'desc'=>"Pokémon kaartsleeves in diverse designs, 64 per verpakking.\n\nOp maat van standaard Pokémon kaarten van 63 × 88 mm (Japanse en Engelse kaarten zijn even groot). Deze sleeves houden kaarten Near Mint tijdens het spelen en het bewaren. De mix van designs verschilt per zending.\n\n- 64 sleeves per verpakking\n- Past op Japanse en Engelse Pokémon kaarten\n- Minimum 20, daarna per tien, met de laagste prijs vanaf 100"],
    'mega-rayquaza-ex-sar-245-191-near-mint' => ['name'=>'Mega Rayquaza ex SAR #245/191 (Japans, Near Mint)', 'desc'=>"De Japanse Mega Rayquaza ex Special Illustration Rare, kaart 245/191 uit Storm Emeralda, in Near Mint staat.\n\nEen full art Special Illustration Rare van de hoofd-Pokémon van de set, en een betaalbaardere manier om Mega Rayquaza te bezitten dan de [Master Ultra Rare](product:mega-rayquaza-ex-mur).\n\n- Japans, Storm Emeralda (M6), kaart 245/191\n- Special Illustration Rare (full art)\n- Near Mint, in sleeve en toploader\n- Minimum 3 exemplaren, met de laagste prijs vanaf 25"],
    'pikachu-ex-sar-240-191-psa-10-gem-mint' => ['name'=>'Pikachu ex SAR #240/191 PSA 10 Gem Mint', 'desc'=>"Pikachu ex Special Illustration Rare #240/191, gegradeerd PSA 10 Gem Mint.\n\nPSA 10 is de hoogste grade: een zo goed als perfecte kaart, gecontroleerd en verzegeld in de slab van PSA. Pikachu kaarten verkopen aan elke verzamelaar, en exemplaren met de topgrade zijn de kaarten die ze houden.\n\n- Pikachu ex Special Illustration Rare #240/191\n- Gegradeerd PSA 10 Gem Mint\n- Verzonden in de originele PSA slab\n- Meer [PSA graded Pokémon kaarten](cards:psa)"],
    'mega-floette-ex-japanese' => ['name'=>'Mega Floette ex (Japans)', 'desc'=>"De Japanse Mega Floette ex uit Ninja Spinner (M4), in Near Mint staat.\n\nMega Floette ex is een van de nieuwe Mega Pokémon ex uit Ninja Spinner. In sleeve en toploader verzonden.\n\n- Japans, [Ninja Spinner](set:ninja-spinner) (M4)\n- Near Mint, in sleeve en toploader\n- Lagere prijs vanaf 6 exemplaren"],
    'mega-excadrill-ex-japanese' => ['name'=>'Mega Excadrill ex (Japans)', 'desc'=>"De Japanse Mega Excadrill ex uit Abyss Eye (M5), in Near Mint staat.\n\nMega Excadrill ex is een van de Mega Pokémon ex uit Abyss Eye. In sleeve en toploader verzonden.\n\n- Japans, [Abyss Eye](set:abyss-eye) (M5)\n- Near Mint, in sleeve en toploader\n- Lagere prijs vanaf 6 exemplaren"],
    'mega-greninja-ex-japanese' => ['name'=>'Mega Greninja ex (Japans)', 'desc'=>"De Japanse Mega Greninja ex uit Ninja Spinner (M4), in Near Mint staat.\n\nGreninja is al jaren een favoriet, en Mega Greninja ex is een van de sterren van Ninja Spinner. In sleeve en toploader verzonden.\n\n- Japans, [Ninja Spinner](set:ninja-spinner) (M4)\n- Near Mint, in sleeve en toploader\n- Lagere prijs vanaf 6 exemplaren"],
    'mega-darkrai-ex-japanese' => ['name'=>'Mega Darkrai ex (Japans)', 'desc'=>"De Japanse Mega Darkrai ex uit Abyss Eye (M5), in Near Mint staat.\n\nDarkrai is een geliefde mythische Pokémon, en Mega Darkrai ex is een van de topkaarten van Abyss Eye. In sleeve en toploader verzonden.\n\n- Japans, [Abyss Eye](set:abyss-eye) (M5)\n- Near Mint, in sleeve en toploader\n- Lagere prijs vanaf 6 exemplaren"],
    'pikachu-ex-special-illustration-rare-277-217' => ['name'=>'Pikachu ex Special Illustration Rare #277/217', 'desc'=>"Pikachu ex Special Illustration Rare, kaart 277/217, in Near Mint staat.\n\nEen full art Special Illustration Rare van de mascotte van Pokémon: een Pikachu kaart als middelpunt van elke verzameling. In sleeve en toploader verzonden.\n\n- Kaart 277/217\n- Special Illustration Rare (full art)\n- Near Mint\n- Meer [Pikachu Pokémon kaarten](cards:pikachu)"],
    'mega-charizard-y-ex-hyper-rare' => ['name'=>'Mega Charizard Y ex Hyper Rare (gouden kaart)', 'desc'=>"Mega Charizard Y ex Hyper Rare: een gouden Charizard kaart uit de Mega Evolution-reeks.\n\nHyper Rares zijn de gouden kaarten met reliëf bovenaan een set, en die van Charizard zijn altijd bij de meest gezochte. Near Mint, in sleeve en toploader.\n\n- Hyper Rare (goud, met reliëf)\n- Near Mint\n- Lagere prijs vanaf 6 exemplaren\n- Wat is een kaart als deze waard? Zie [Pokémon kaarten waarde](guide:prices)"],
    '30th-celebration-premium-deck-set-espeon-and-umbreon' => ['name'=>'30th Celebration Premium Deck Set: Espeon & Umbreon', 'desc'=>"De 30th Celebration Premium Deck Set met Espeon en Umbreon: een jubileumstuk voor verzamelaars.\n\nEspeon en Umbreon zijn twee van de meest geliefde evoluties van Eevee, en deze premium set zet ze centraal in de viering van 30 jaar Pokémon. Per stuk verkocht, met een lagere prijs vanaf zes sets.\n\n- 30th Celebration Premium Deck Set: Espeon & Umbreon\n- Jubileum-verzamelstuk\n- Per stuk, met een lagere prijs vanaf 6"],
    '30th-celebration-sylveon-ex-box' => ['name'=>'30th Celebration Sylveon ex Box', 'desc'=>"Een 30th Celebration collection box rond Sylveon ex, met booster packs.\n\nDe tegenhanger van de [Greninja ex Box](product:30th-celebration-greninja-ex-box): een makkelijk cadeau, een sterke verkoper per stuk voor winkels, en een lagere prijs vanaf 36 boxen.\n\n- Sylveon ex collection box met booster packs\n- Deel van de [30th Celebration](set:30th-celebration) reeks\n- Per zes, met de laagste prijs vanaf 36"],
    '30th-celebration-tech-sticker-collection' => ['name'=>'30th Celebration Tech Sticker Collection', 'desc'=>"De 30th Celebration Tech Sticker Collection: een betaalbare extra uit de jubileumreeks.\n\nEen goedkoop product dat goed verkoopt aan de kassa en in online winkelmandjes naast 30th Celebration boxen.\n\n- 30th Celebration stickercollectie\n- Per 12, met de laagste prijs vanaf 72"],
    'pitch-black-elite-trainer-box' => ['name'=>'Pitch Black Elite Trainer Box', 'desc'=>"Een verzegelde Pokémon TCG Mega Evolution Pitch Black Elite Trainer Box.\n\nPitch Black hoort bij de Mega Evolution-reeks van de Pokémon Trading Card Game. Een Elite Trainer Box is de complete manier om aan een nieuwe set te beginnen: booster packs plus kaartsleeves, dobbelstenen, schadefiches en een opbergdoos. Verwante Japanse producten vind je bij [Abyss Eye](set:abyss-eye).\n\n- Verzegelde Mega Evolution Pitch Black Elite Trainer Box\n- Booster packs, sleeves, dobbelstenen, schadefiches en opbergdoos\n- Per vier, met de laagste prijs vanaf 24"],
    'chaos-rising-elite-trainer-box' => ['name'=>'Chaos Rising Elite Trainer Box', 'desc'=>"Een verzegelde Pokémon TCG Mega Evolution Chaos Rising Elite Trainer Box.\n\nChaos Rising hoort bij de Mega Evolution-reeks van de Pokémon Trading Card Game. Een Elite Trainer Box is de complete manier om aan een nieuwe set te beginnen: booster packs plus kaartsleeves, dobbelstenen, schadefiches en een opbergdoos. Verwante Japanse producten vind je bij [Ninja Spinner](set:ninja-spinner).\n\n- Verzegelde Mega Evolution Chaos Rising Elite Trainer Box\n- Booster packs, sleeves, dobbelstenen, schadefiches en opbergdoos\n- Per vier, met de laagste prijs vanaf 24"],
    'perfect-order-elite-trainer-box' => ['name'=>'Perfect Order Elite Trainer Box', 'desc'=>"Een verzegelde Pokémon TCG Mega Evolution Perfect Order Elite Trainer Box.\n\nPerfect Order hoort bij de Mega Evolution-reeks van de Pokémon Trading Card Game. Een Elite Trainer Box is de complete manier om aan een nieuwe set te beginnen: booster packs plus kaartsleeves, dobbelstenen, schadefiches en een opbergdoos. Verwante Japanse producten vind je bij [Nihil Zero](set:nihil-zero).\n\n- Verzegelde Mega Evolution Perfect Order Elite Trainer Box\n- Booster packs, sleeves, dobbelstenen, schadefiches en opbergdoos\n- Per vier, met de laagste prijs vanaf 24"],
    'mega-evolution-ascended-heroes-elite-trainer-box' => ['name'=>'Mega Evolution: Ascended Heroes Elite Trainer Box', 'desc'=>"Een verzegelde Pokémon TCG Mega Evolution Ascended Heroes Elite Trainer Box.\n\nAscended Heroes hoort bij de Mega Evolution-reeks van de Pokémon Trading Card Game. Een Elite Trainer Box is de complete manier om aan een nieuwe set te beginnen: booster packs plus kaartsleeves, dobbelstenen, schadefiches en een opbergdoos. Verwante Japanse producten vind je bij [Mega Dream ex](set:mega-dream-ex).\n\n- Verzegelde Mega Evolution Ascended Heroes Elite Trainer Box\n- Booster packs, sleeves, dobbelstenen, schadefiches en opbergdoos\n- Per vier, met de laagste prijs vanaf 24"],
    '30th-celebration-elite-trainer-box-case-10-ct' => ['name'=>'30th Celebration Elite Trainer Box case (10 boxen)', 'desc'=>"Een verzegelde case met 10 Pokémon TCG: 30th Celebration Elite Trainer Boxes, geprijsd per case.\n\nDe voordeligste manier om jubileum-ETB's in te slaan: tien boxen in één verzegelde case, met een sterk dalende prijs per case vanaf zes cases. Elke Elite Trainer Box bevat 9 booster packs, een full-art Nidorina promo, 65 sleeves, dobbelstenen en een spelersgids.\n\n- 10 verzegelde 30th Celebration Elite Trainer Boxes per case\n- Per box: 9 booster packs, Nidorina promo, 16 foil energiekaarten, 65 sleeves, dobbelstenen, munt en verzameldoos\n- Lagere prijs per case vanaf 6 cases\n- Ook [per vier](product:30th-celebration-elite-trainer-box) te koop"],
    'mega-dream-ex-m2a-booster-box' => ['name'=>'Mega Dream ex (M2A) booster box (Japans)', 'desc'=>"Een verzegelde Japanse booster box van Mega Dream ex (M2A), de speciale set van de Mega Evolution-reeks.\n\nMega Dream ex is de thuisbasis van de [Mega Gengar ex Special Illustration Rare](product:mega-gengar-ex-sir), een van de topkaarten van de Mega Evolution-periode. Daarom zijn deze boxen geliefd bij verzamelaars die hem zelf willen trekken.\n\n- Verzegelde Japanse Mega Dream ex booster box (M2A)\n- Chase card: Mega Gengar ex Special Illustration Rare\n- Beste prijs per box vanaf 24 boxen\n- Alle [Gengar Pokémon kaarten](cards:gengar)"],
    'inferno-x-booster-box' => ['name'=>'Inferno X (M2) booster box (Japans)', 'desc'=>"Een verzegelde Japanse booster box van Inferno X (M2), de tweede hoofdset van de Mega Evolution-reeks.\n\nInferno X zet de Mega Evolution-periode voort met nieuwe Mega Pokémon ex en full art rares. Verzegelde boxen zijn goed voor spelers die decks bouwen, verzamelaars die de zeldzame kaarten zoeken en winkels die actuele Japanse producten aanvullen.\n\n- Verzegelde Japanse booster box: Inferno X (M2)\n- Deel van de [Mega Evolution-reeks](set:mega-evolution)\n- Per zes, met de beste prijs vanaf 36 boxen"],
    'mega-symphonia-booster-box' => ['name'=>'Mega Symphonia (M1S) booster box (Japans)', 'desc'=>"Een verzegelde Japanse booster box van Mega Symphonia (M1S), een van de twee sets waarmee de Mega Evolution-reeks in 2025 begon.\n\nMega Symphonia en tweelingset [Mega Brave](product:mega-brave-booster-box) brachten Mega Evolution terug in het Pokémon TCG, en veel verzamelaars kopen ze samen om de start van de reeks compleet te hebben.\n\n- Verzegelde Japanse booster box: Mega Symphonia (M1S)\n- Tweelingset van Mega Brave (M1L)\n- Per zes, met de beste prijs vanaf 36 boxen"],
    'mega-brave-booster-box' => ['name'=>'Mega Brave (M1L) booster box (Japans)', 'desc'=>"Een verzegelde Japanse booster box van Mega Brave (M1L), een van de twee sets waarmee de Mega Evolution-reeks in 2025 begon.\n\nMega Brave en tweelingset [Mega Symphonia](product:mega-symphonia-booster-box) brachten Mega Evolution terug in het Pokémon TCG, en veel verzamelaars kopen ze samen om de start van de reeks compleet te hebben.\n\n- Verzegelde Japanse booster box: Mega Brave (M1L)\n- Tweelingset van Mega Symphonia (M1S)\n- Per zes, met de beste prijs vanaf 36 boxen"],
    'abyss-eye-m5-booster-box' => ['name'=>'Abyss Eye (M5) booster box (Japans)', 'desc'=>"Een verzegelde Japanse booster box van Abyss Eye (M5) uit de Mega Evolution-reeks.\n\nIn Abyss Eye zitten onder meer [Mega Darkrai ex](product:mega-darkrai-ex-japanese) en [Mega Excadrill ex](product:mega-excadrill-ex-japanese). Open boxen om ze te trekken, of koop de losse kaarten meteen.\n\n- Verzegelde Japanse booster box: Abyss Eye (M5)\n- Met Mega Darkrai ex en Mega Excadrill ex\n- Beste prijs per box vanaf 24 boxen\n- Bijpassende [Abyss Eye Elite Trainer Box](product:abyss-eye-elite-trainer-box)"],
    'ninja-spinner-m4-booster-box' => ['name'=>'Ninja Spinner (M4) booster box (Japans)', 'desc'=>"Een verzegelde Japanse booster box van Ninja Spinner (M4): beperkte voorraad.\n\nNinja Spinner brengt [Mega Greninja ex](product:mega-greninja-ex-japanese) en [Mega Floette ex](product:mega-floette-ex-japanese) naar de Mega Evolution-reeks. Greninja is al jaren een favoriet, en er zijn nog maar weinig verzegelde boxen.\n\n- Verzegelde Japanse booster box: Ninja Spinner (M4)\n- Met Mega Greninja ex en Mega Floette ex\n- Beste prijs per box vanaf 24 boxen"],
    'nihil-zero-booster-box' => ['name'=>'Nihil Zero (M3) booster box (Japans)', 'desc'=>"Een verzegelde Japanse booster box van Nihil Zero (M3) uit de Mega Evolution-reeks.\n\nNihil Zero is een betaalbare instap in de Mega Evolution-periode, en de vlakke prijsstaffel maakt er een makkelijke extra van bij een grotere bestelling.\n\n- Verzegelde Japanse booster box: Nihil Zero (M3)\n- Deel van de [Mega Evolution-reeks](set:mega-evolution)\n- Per zes"],
    'terastal-festival-booster-box' => ['name'=>'Terastal Festival ex (SV8a) booster box (Japans)', 'desc'=>"Een verzegelde Japanse booster box van Terastal Festival ex (SV8a), de speciale Scarlet & Violet set rond de evoluties van Eevee.\n\nTerastal Festival ex draait om Terastal Pokémon en alle Eevee-evoluties, en verscheen in het Engels als Prismatic Evolutions. Een Japanse box bevat 10 packs van 10 kaarten, en de Special Art Rares van de set, vooral Umbreon ex, horen bij de meest gezochte kaarten van de laatste jaren.\n\n- Verzegelde Japanse booster box: Terastal Festival ex (SV8a), 10 packs van 10 kaarten\n- In het Engels verschenen als Prismatic Evolutions\n- Per zes, met de laagste prijs vanaf 36 boxen"],
  ],

  /* products are included if their id is listed, or their name contains a match term
     (comma-separated); cond limits to one condition, or lists every product in it when there are no terms.
     key: the same collection in French, for the language switch and Google */
  'collections' => [
    ['key'=>'charizard', 'slug'=>'charizard-pokemon-kaarten', 'title'=>'Charizard Pokémon kaarten', 'h1'=>'Charizard Pokémon kaarten (Japans)',
     'match'=>'charizard', 'ids'=>[], 'cond'=>'',
     'seo_title'=>'Charizard Pokémon kaarten kopen: Japanse SAR & Hyper Rare',
     'seo_desc'=>'Japanse Charizard Pokémon kaarten: de 151 Charizard ex Special Illustration Rare en de gouden Mega Charizard Y ex Hyper Rare, Near Mint.',
     'intro'=><<<'MD'
Charizard is een van de meest verzamelde Pokémon in de Trading Card Game, en zijn kaarten zijn vaak de duurste van een set. Onze Japanse Charizard kaarten zijn onder meer de Charizard ex Special Illustration Rare uit [151](set:151) en de gouden Mega Charizard Y ex Hyper Rare uit de Mega Evolution-reeks.

Elke kaart is Near Mint en gaat op in een sleeve en toploader. Waarom de ene Charizard kaart zoveel meer waard is dan de andere, lees je in [Pokémon kaarten waarde](guide:prices).
MD],
    ['key'=>'pikachu', 'slug'=>'pikachu-pokemon-kaarten', 'title'=>'Pikachu Pokémon kaarten', 'h1'=>'Pikachu Pokémon kaarten',
     'match'=>'pikachu', 'ids'=>[], 'cond'=>'',
     'seo_title'=>'Pikachu Pokémon kaarten: SAR, PSA 10 & Pikachu sleeves',
     'seo_desc'=>'Pikachu Pokémon kaarten uit Japan: Pikachu ex Special Illustration Rares, waaronder een PSA 10 Gem Mint, plus Pikachu sleeves en deck boxes.',
     'intro'=><<<'MD'
Pikachu is het gezicht van Pokémon, en Pikachu kaarten verkopen aan elk soort verzamelaar. We hebben Pikachu ex Special Illustration Rares, waaronder een [PSA 10 Gem Mint](cards:psa) exemplaar, plus Pikachu deck sleeves en een Ultra PRO Pikachu deck box.

Ongegradeerde Pikachu kaarten gaan op in sleeve en toploader; graded kaarten in hun PSA slab.
MD],
    ['key'=>'gengar', 'slug'=>'gengar-pokemon-kaarten', 'title'=>'Gengar Pokémon kaarten', 'h1'=>'Gengar Pokémon kaarten',
     'match'=>'gengar', 'ids'=>['mega-dream-ex-m2a-booster-box'], 'cond'=>'',
     'seo_title'=>'Gengar Pokémon kaart: Mega Gengar ex SIR (Japans)',
     'seo_desc'=>'De Japanse Mega Gengar ex Special Illustration Rare uit Mega Dream ex, plus verzegelde Mega Dream ex booster boxes om hem zelf te trekken.',
     'intro'=><<<'MD'
Gengar heeft een van de trouwste fanbases in het Pokémon TCG, en de Mega Gengar ex Special Illustration Rare uit [Mega Dream ex](set:mega-dream-ex) is een van de topkaarten van de Mega Evolution-reeks. Koop de kaart Near Mint, of open verzegelde Mega Dream ex booster boxes om hem zelf te trekken.
MD],
    ['key'=>'psa', 'slug'=>'psa-graded-pokemon-kaarten', 'title'=>'PSA graded Pokémon kaarten', 'h1'=>'PSA graded Pokémon kaarten',
     'match'=>'', 'ids'=>[], 'cond'=>'Graded',
     'seo_title'=>'PSA graded Pokémon kaarten: PSA 10 Gem Mint',
     'seo_desc'=>'PSA graded Pokémon kaarten, waaronder een PSA 10 Gem Mint Pikachu ex Special Illustration Rare, verzonden in de originele PSA slab.',
     'intro'=><<<'MD'
Graded Pokémon kaarten zijn beoordeeld en verzegeld in een case door een gradingbedrijf. PSA geeft een score van 1 tot 10: PSA 10 (Gem Mint) is een zo goed als perfecte kaart, PSA 9 is Mint en PSA 8 is Near Mint–Mint. Omdat de grade de twijfel over de staat wegneemt, verkopen graded kaarten, zeker PSA 10's, meestal voor duidelijk meer dan ongegradeerde exemplaren.

Onze graded Pokémon kaarten gaan op in hun originele PSA slab. Voor ongegradeerde kaarten, zie onze [losse kaarten](category:singles); hoe grading de prijs beïnvloedt, lees je in [Pokémon kaarten waarde](guide:prices).
MD],
  ],

  /* key: the same guide in French. anchor: the short link text (the keyword the guide targets) */
  'guides' => [
    ['key'=>'prices', 'slug'=>'pokemon-kaarten-waarde', 'anchor'=>'Pokémon kaarten waarde', 'updated'=>'2026-09-27',
     'title'=>'Pokémon kaarten waarde: wat is jouw kaart waard?',
     'seo_title'=>'Pokémon kaarten waarde: prijzen checken (2026)',
     'seo_desc'=>'Wat is een Pokémon kaart waard? Zo bepaal je de waarde van Pokémon kaarten: zeldzaamheid, staat, grading en vraag, plus een prijschecker met onze actuele prijzen.',
     'body'=><<<'MD'
Wat is een Pokémon kaart waard? De meeste kaarten uit een booster pack zijn een paar cent waard, maar sommige kaarten gaan voor honderden of zelfs duizenden euro. Op deze pagina lees je hoe je de waarde van Pokémon kaarten bepaalt, en met de prijschecker hieronder zie je meteen wat elke box, kaart en accessoire bij ons kost.

## Prijschecker: onze actuele prijzen

Typ een set, een Pokémon of een producttype. De eerste prijs geldt vanaf de minimumafname, de tweede is de laagste prijs per stuk als je meer bestelt.

{price_list}

## Wat bepaalt de waarde van een Pokémon kaart?

Vijf dingen bepalen bijna altijd de prijs:

1. **Zeldzaamheid.** Het symbool of de letters onderaan de kaart tonen hoe zeldzaam ze is. Gewone kaarten (C, U) zijn weinig waard; Special Illustration Rares (SAR of SIR), Hyper Rares (goud) en de nieuwe Master Ultra Rares zijn de dure kaarten.
2. **De Pokémon.** Charizard, Pikachu, Umbreon, Gengar, Mew en Rayquaza zijn altijd gewild. Dezelfde zeldzaamheid met een minder populaire Pokémon is vaak een fractie waard.
3. **De staat.** Een kaart zonder krassen, witte randjes of kreukjes (Near Mint) is veel meer waard dan een bespeelde kaart. Kijk bij goed licht naar de randen, de hoeken en het oppervlak.
4. **Grading.** Een kaart die door PSA, BGS of CGC beoordeeld is, zit verzegeld in een case met een cijfer van 1 tot 10. Een PSA 10 is vaak meerdere keren zoveel waard als dezelfde kaart zonder grade. Zie onze [PSA graded Pokémon kaarten](cards:psa).
5. **Taal en set.** Japanse kaarten verschijnen eerst en hebben hun eigen markt. Sommige sets, zoals [151](set:151) en [Terastal Festival ex](set:terastal-festival-ex), blijven lang gewild.

## Zo zoek je de waarde van jouw kaart op

1. **Zoek het kaartnummer**, rechts- of linksonder (bijvoorbeeld 245/191), en de setcode.
2. **Zoek verkochte exemplaren**, niet de vraagprijzen. Wat iemand vraagt is niet wat een kaart opbrengt. Filter op verkochte advertenties bij veilingsites en kijk naar prijsgidsen voor verzamelaars.
3. **Vergelijk dezelfde staat en taal.** Een Japanse kaart vergelijk je met Japanse verkopen, een graded kaart met dezelfde grade.
4. **Kijk naar meerdere verkopen.** Eén uitschieter zegt weinig; het gemiddelde van de laatste weken zegt veel.

## Pokémon cards price value: kort in het Engels

Veel verzamelaars zoeken in het Engels, op "pokemon cards price value" of "pokémon card worth". Het principe is hetzelfde: rarity + Pokémon + condition + grading bepalen de prijs, en je kijkt naar recent verkochte exemplaren. De prijschecker hierboven werkt in elke taal.

## Hoeveel is een booster box waard?

Een verzegelde booster box is meestal meer waard dan de som van de gewone kaarten die erin zitten, omdat je een kans hebt op de chase cards. Boxen van sets die niet meer gedrukt worden, stijgen vaak in waarde zolang ze verzegeld blijven. Onze [Japanse booster boxes](category:boxes) tonen de prijs per box, en hoe die daalt als je er meer neemt.

## Vragen over Pokémon kaarten waarde

### Wat is de duurste Pokémon kaart?

De duurste Pokémon kaarten zijn oude promokaarten en zeldzame vroege kaarten in topgrade, die voor miljoenen verkocht zijn. Van recente sets zijn Special Illustration Rares en gouden kaarten van populaire Pokémon het meest waard.

### Zijn Japanse Pokémon kaarten minder waard dan Engelse?

Niet per se. Japanse kaarten hebben hun eigen markt, en van sommige kaarten is de Japanse versie juist meer waard, door de drukkwaliteit of omdat ze eerst verscheen. Lees meer over [Japanse Pokémon kaarten](guide:japanese).

### Is een PSA 10 altijd meer waard?

Bijna altijd. Een PSA 10 bewijst dat de kaart zo goed als perfect is, en verzamelaars betalen daar een premie voor. Grading kost wel geld en tijd, dus het loont vooral bij kaarten die al veel waard zijn.

### Hoe weet ik of mijn kaart echt is?

Kijk naar de druk, de kleuren, de dikte en de achterkant, en vergelijk met een kaart die je zeker echt weet. Onze gids [echte of nep Pokémon kaarten herkennen](guide:fakes) legt het stap voor stap uit.
MD],

    ['key'=>'buy', 'slug'=>'pokemon-kaarten-kopen', 'anchor'=>'Pokémon kaarten kopen in België', 'updated'=>'2026-09-27',
     'title'=>'Pokémon kaarten kopen in België: waar en hoe?',
     'seo_title'=>'Pokémon kaarten kopen in België: winkels, online & Japans',
     'seo_desc'=>'Waar koop je Pokémon kaarten in België? Speelgoedwinkels, kaartenwinkels, beurzen of online: waar let je op, wat kost het, en waarom Japanse kaarten kopen.',
     'body'=><<<'MD'
Pokémon kaarten kopen in België kan op veel plaatsen: in de speelgoedwinkel, in een kaartenwinkel om de hoek, op een beurs of online. Elke optie heeft voor- en nadelen. Hier lees je waar je terechtkan, waar je op let, en waarom steeds meer verzamelaars Japanse kaarten kopen.

## Waar kan je Pokémon kaarten kopen?

### Speelgoedwinkels en supermarkten

Grote winkels zoals speelgoedketens, multimediazaken en sommige supermarkten verkopen Engelse of Franstalige booster packs, blisters en Elite Trainer Boxes. Handig voor een cadeau, maar het aanbod is beperkt en nieuwe sets zijn vaak snel uitverkocht.

### Kaartenwinkels en spelwinkels

Een lokale kaartenwinkel is de beste plek voor losse kaarten, advies en speelavonden. Zoek op "kaartenwinkel" of "Pokémon kaarten winkel" in je gemeente; in elke grotere stad, van Gent en Antwerpen tot Brussel en Leuven, zijn er zaken met Pokémon kaarten.

### Beurzen en conventies

Op beurzen en conventies voor strips, games en anime vind je vaak kraampjes met Pokémon kaarten, ook zeldzame losse kaarten en graded slabs. Handig om kaarten te bekijken voor je koopt.

### Online

Online vind je het grootste aanbod en kan je prijzen vergelijken. Let wel op: koop bij verkopers met duidelijke contactgegevens, een retourbeleid en echte, verzegelde producten. Deze winkels vermelden we alleen ter vergelijking; we zijn er niet mee verbonden.

## Japanse Pokémon kaarten kopen

Bij {brand} koop je Japanse Pokémon kaarten, rechtstreeks uit Japan. Waarom Japans?

- **Eerder:** nieuwe sets verschijnen eerst in Japan, vaak maanden voor de Engelse versie. Zie [nieuwe Pokémon sets](guide:releases).
- **Voordeliger per box:** Japanse [booster boxes](category:boxes) zijn kleiner en goedkoper per box.
- **Kwaliteit:** Japanse kaarten staan bekend om hun scherpe druk en nette snijranden.

Alles wordt verzegeld verzonden, met tracking, en we rekenen geen btw aan. Meer uitleg: [Japanse Pokémon kaarten](guide:japanese).

## Waar let je op als je Pokémon kaarten koopt?

- **Verzegeling:** een booster box of pack moet in de originele folie zitten, zonder losse naden of dubbele folie.
- **Prijs:** te goed om waar te zijn? Dan is het vaak nep. Vergelijk met onze [prijschecker](guide:prices).
- **Echtheid:** leer [nep Pokémon kaarten herkennen](guide:fakes) voor je losse kaarten koopt.
- **Verkoper:** controleer contactgegevens, voorwaarden en retourbeleid.

## Wat kost een Pokémon kaart?

Een booster pack kost een paar euro, een booster box enkele tientallen tot honderden euro, en een zeldzame losse kaart van een paar euro tot honderden euro. Onze [Pokémon kaarten waarde](guide:prices) pagina toont al onze prijzen, met korting als je meer bestelt.

## Vragen over Pokémon kaarten kopen

### Waar koop je Pokémon kaarten het goedkoopst?

Per kaart is een booster box meestal de goedkoopste manier om packs te kopen. Voor een specifieke kaart is die kaart los kopen bijna altijd goedkoper dan packs openen tot je ze trekt.

### Kan ik Pokémon kaarten online kopen in België?

Ja. Wij verzenden vanuit Japan naar heel België, met tracking. {standard} duurt {standard_days}.

### Zijn Japanse kaarten te gebruiken in Belgische toernooien?

Voor officiële toernooien gelden regels over welke taalversies toegelaten zijn. Japanse kaarten zijn vooral voor verzamelaars en om thuis te spelen; kijk voor een toernooi altijd de regels van de organisator na.
MD],

    ['key'=>'japanese', 'slug'=>'japanse-pokemon-kaarten', 'anchor'=>'Japanse Pokémon kaarten', 'updated'=>'2026-09-27',
     'title'=>'Japanse Pokémon kaarten: wat is het verschil?',
     'seo_title'=>'Japanse Pokémon kaarten: verschillen, sets & kopen',
     'seo_desc'=>'Waarom kopen verzamelaars Japanse Pokémon kaarten? Eerdere releases, drukkwaliteit, kleinere booster boxes en hoe je ze in België koopt.',
     'body'=><<<'MD'
Japanse Pokémon kaarten zijn de originele versie van elke moderne set van de Pokémon Trading Card Game. Dezelfde kaarten verschijnen later in het Engels, maar veel verzamelaars, en ook heel wat spelers, kopen liever Japans. Dit is het verschil, en dit moet je weten voor je koopt.

## Japanse sets verschijnen eerst

Nieuwe Pokémon sets worden in Japan ontworpen en daar eerst uitgebracht. Engelse versies volgen meestal maanden later en combineren soms twee Japanse sets, waardoor de namen niet altijd overeenkomen. De Japanse [151](set:151) werd "Scarlet & Violet—151" in het Engels, en [Terastal Festival ex](set:terastal-festival-ex) werd "Prismatic Evolutions". Wil je de nieuwste kaarten zo snel mogelijk, dan koop je Japans. Alle Japanse sets die we verkopen, met hun setcode, staan in onze [lijst van Pokémon sets](guide:sets).

## Zelfde formaat, andere packs

Japanse en Engelse Pokémon kaarten zijn precies even groot: 63 × 88 mm, dus ze passen in dezelfde [sleeves en mappen](category:accessories). Het verschil zit in de verpakking:

- De meeste Japanse booster boxes van een hoofdset bevatten 30 packs van 5 kaarten; een Engelse box bevat 36 packs van 10 kaarten.
- Japanse speciale sets verschillen: een 151 box bevat 20 packs van 7 kaarten, een Terastal Festival ex box 10 packs van 10.
- Japanse boxen kosten minder per box, dus je opent meer sets met hetzelfde budget.

## Drukkwaliteit

Japanse kaarten staan bekend om hun consistente druk: scherpe kleuren, nette centrering en weinig drukfouten. Voor verzamelaars die kaarten laten graden, maakt dat verschil, omdat centrering en randen de grade mee bepalen.

## Kan je Japanse kaarten lezen?

De tekst staat in het Japans, maar de kaarten zelf zijn herkenbaar: dezelfde Pokémon, aanvallen met dezelfde energiesymbolen en dezelfde schadegetallen. Voor verzamelaars maakt de taal niet uit. Wil je spelen, dan helpt een kaartendatabase om de tekst op te zoeken.

## Waar koop je Japanse Pokémon kaarten in België?

In gewone winkels vind je vooral Engelse en Franstalige producten. Japanse kaarten koop je bij gespecialiseerde winkels of online. Bij {brand} koop je ze rechtstreeks uit Japan: [booster boxes](category:boxes), [Elite Trainer Boxes](category:etb) en [losse kaarten](category:singles), verzegeld en met tracking verzonden, zonder btw. Meer over [Pokémon kaarten kopen in België](guide:buy).

## Vragen over Japanse Pokémon kaarten

### Zijn Japanse Pokémon kaarten echt?

Ja, als je ze bij een betrouwbare verkoper koopt: het zijn de officiële kaarten van The Pokémon Company in Japan. Let wel op namaak; zie [nep Pokémon kaarten herkennen](guide:fakes).

### Zijn Japanse Pokémon kaarten meer waard?

Dat verschilt per kaart. Sommige Japanse kaarten zijn meer waard dan de Engelse versie, andere minder. Zie [Pokémon kaarten waarde](guide:prices).

### Zitten er codes voor Pokémon TCG Live in Japanse producten?

Nee. Japanse producten bevatten geen codekaarten voor Pokémon TCG Live; die zitten in Engelstalige en andere westerse producten.
MD],

    ['key'=>'releases', 'slug'=>'nieuwe-pokemon-sets', 'anchor'=>'Nieuwe Pokémon sets', 'updated'=>'2026-09-27',
     'title'=>'Nieuwe Pokémon sets en releases in 2026',
     'seo_title'=>'Nieuwe Pokémon sets 2026: releases & pre-orders',
     'seo_desc'=>'Welke Pokémon sets komen eraan? De nieuwste Japanse Pokémon releases van 2025 en 2026, hoe het releaseschema werkt en hoe je vooraf reserveert.',
     'body'=><<<'MD'
Elk jaar verschijnen er meerdere nieuwe Pokémon sets. In Japan komen ze eerst uit, gevolgd door de Engelse en andere versies. Hier lees je welke sets recent verschenen, wat eraan komt, en hoe je een nieuwe set vooraf reserveert.

## Hoe werkt het releaseschema?

De Pokémon Company brengt in Japan elk jaar meerdere hoofdsets uit, aangevuld met speciale sets (zoals [151](set:151) of [Mega Dream ex](set:mega-dream-ex)) en losse producten zoals decks en collection boxes. De Engelse versie van een set volgt meestal enkele maanden later, soms als combinatie van twee Japanse sets.

Wie de nieuwste kaarten wil, kijkt dus eerst naar Japan.

## Recente Japanse sets: de Mega Evolution-reeks

De [Mega Evolution-reeks](set:mega-evolution) begon in 2025 en brengt Mega Evolution terug in het TCG. De Japanse sets tot nu toe:

- [Mega Brave](set:mega-brave) (M1L) en [Mega Symphonia](set:mega-symphonia) (M1S): de tweelingsets waarmee de reeks begon in 2025
- [Inferno X](set:inferno-x) (M2)
- [Mega Dream ex](set:mega-dream-ex) (M2a), de speciale set met Mega Gengar ex
- [Nihil Zero](set:nihil-zero) (M3)
- [Ninja Spinner](set:ninja-spinner) (M4) met Mega Greninja ex
- [Abyss Eye](set:abyss-eye) (M5) met Mega Darkrai ex
- [Storm Emeralda](set:storm-emeralda) (M6) met Mega Rayquaza ex
- [30th Celebration](set:30th-celebration) (M6a), de jubileumset voor 30 jaar Pokémon in 2026

## Binnenkort: Aura Seeker

[Aura Seeker (Hadou Seeker)](set:aura-seeker) is de volgende Japanse set uit de Mega Evolution-reeks, verwacht in november 2026. Je kan de [Aura Seeker booster box](product:aura-seeker-booster-box) nu al reserveren: je betaalt pas wanneer de voorraad wordt toegewezen.

Releasedatums worden bepaald door The Pokémon Company en kunnen verschuiven.

## Hoe reserveer je een nieuwe set?

1. **Kies het product** met de status "pre-order".
2. **Bestel zoals altijd.** Je reserveert je toewijzing aan de getoonde prijs.
3. **Je krijgt een factuur** wanneer de voorraad wordt toegewezen, niet wanneer je bestelt.
4. **We verzenden** zodra de voorraad binnen is, meestal op of net na de Japanse releasedatum.

## Oudere sets die nog gewild zijn

Niet alleen nieuwe sets zijn gewild. Scarlet & Violet sets zoals [Glory of Team Rocket](set:glory-of-team-rocket), [Terastal Festival ex](set:terastal-festival-ex) en [151](set:151) blijven populair, en verzegelde boxen worden schaarser zodra een set niet meer gedrukt wordt. Alle sets op een rij: [lijst van Pokémon sets](guide:sets).
MD],

    ['key'=>'sets', 'slug'=>'pokemon-sets-lijst', 'anchor'=>'Lijst van Pokémon sets', 'updated'=>'2026-09-27',
     'title'=>'Lijst van Pokémon sets: alle Japanse sets met setcode',
     'seo_title'=>'Pokémon sets lijst: alle Japanse sets & setcodes',
     'seo_desc'=>'Een overzicht van de Japanse Pokémon sets van Mega Evolution en Scarlet & Violet, met setcode, en hoe je setsymbolen en kaartnummers leest.',
     'body'=><<<'MD'
Elke Pokémon kaart hoort bij een set, en elke set heeft een eigen code en symbool. Hieronder vind je alle Japanse Pokémon sets die we verkopen, per reeks, met hun setcode. Klik op een set voor de producten en meer uitleg.

## Alle Japanse sets bij {brand}

{set_table}

## Wat is een reeks en wat is een set?

Een **reeks** (in het Engels "series") is een grote periode van het Pokémon TCG, zoals [Scarlet & Violet](set:scarlet-violet) of [Mega Evolution](set:mega-evolution). Binnen een reeks verschijnen meerdere **sets** (uitbreidingen), elk met een eigen naam, symbool en kaartenlijst.

Japanse setcodes beginnen met de reeks: SV voor Scarlet & Violet, M voor Mega Evolution. Een kleine letter "a" of een extra letter wijst vaak op een speciale set, zoals SV2a (151) of M2a (Mega Dream ex).

## Zo lees je het kaartnummer

Onderaan elke kaart staat een nummer zoals 245/191. Het tweede getal is de grootte van de basisset; een eerste getal dat hoger is, zoals 245, betekent een secret rare buiten de basisset. Naast het nummer staat de zeldzaamheid (C, U, R, RR, AR, SR, SAR, UR…) en de setcode.

## Pokémon 151 kaartenlijst

De Japanse [151](set:151) (SV2a) telt 210 kaarten: 165 in de basisset en 45 secret rares, waaronder 8 Special Art Rares en 3 gouden Ultra Rares. Het is een van de meest gezochte sets van de laatste jaren.

## Nieuwe sets

Welke set is de nieuwste, en wat komt eraan? Zie [nieuwe Pokémon sets](guide:releases).
MD],

    ['key'=>'boosters', 'slug'=>'pokemon-boosters', 'anchor'=>'Pokémon boosters uitgelegd', 'updated'=>'2026-09-27',
     'title'=>'Pokémon boosters: booster packs en booster boxes uitgelegd',
     'seo_title'=>'Pokémon booster kopen: packs & booster boxes uitgelegd',
     'seo_desc'=>'Wat zit er in een Pokémon booster pack en in een booster box? Aantal kaarten, kansen op zeldzame kaarten, Japanse vs Engelse boosters en wat je het best koopt.',
     'body'=><<<'MD'
Een Pokémon booster is een verzegeld pakje kaarten uit één set. Je weet niet welke kaarten erin zitten: dat is net de spanning. Hier lees je wat er in een booster pack en een booster box zit, en wat je het best koopt.

## Wat zit er in een Pokémon booster pack?

Een Japans booster pack van een hoofdset bevat meestal 5 kaarten; een Engels pack 10. In elk pack zit minstens één holo of zeldzame kaart, en af en toe een kaart van hoge zeldzaamheid, zoals een Special Illustration Rare of een gouden kaart. Speciale sets wijken af: een Japans [151](set:151) pack bevat 7 kaarten.

## Wat is een booster box?

Een booster box is een verzegelde doos met packs van dezelfde set:

- **Japanse booster box, hoofdset:** meestal 30 packs van 5 kaarten
- **Japanse speciale set:** bijvoorbeeld 20 packs van 7 (151) of 10 packs van 10 ([Terastal Festival ex](set:terastal-festival-ex))
- **Engelse booster box:** 36 packs van 10 kaarten

Een box is per pack goedkoper dan losse boosters, en je krijgt een eerlijke verdeling van de zeldzaamheden. Bekijk onze [Pokémon booster boxes](category:boxes).

## Booster box, Elite Trainer Box of collection box?

- **Booster box:** de meeste packs voor je geld. Ideaal om een set te verzamelen of voor een box break.
- **[Elite Trainer Box](category:etb):** minder packs, maar met sleeves, dobbelstenen en een opbergdoos. Perfect om te beginnen of als cadeau.
- **[Collection box](category:premium):** een paar packs plus een promokaart. Een mooi cadeau.

## Welke Pokémon booster box kopen?

- Voor de nieuwste kaarten: de laatste set uit de [Mega Evolution-reeks](set:mega-evolution), zoals [Storm Emeralda](product:storm-emeralda-m6-booster-box).
- Voor verzamelaars: speciale sets zoals [151](product:151-booster-box) en [Terastal Festival ex](product:terastal-festival-booster-box).
- Voor het jubileum: [30th Celebration](product:30th-celebration-m6a-booster-box).

## Vragen over Pokémon boosters

### Hoeveel boosters zitten er in een booster box?

Een Japanse booster box van een hoofdset bevat meestal 30 boosters, een Engelse 36. Speciale Japanse sets hebben er minder, bijvoorbeeld 20 of 10.

### Kan ik een booster box openen en de kaarten verkopen?

Ja, dat doen veel verzamelaars. Of het loont, hangt af van de set en je geluk. Wat losse kaarten waard zijn, lees je in [Pokémon kaarten waarde](guide:prices).

### Is een verzegelde booster box een goede belegging?

Verzegelde boxen van populaire sets stijgen vaak in waarde zodra ze niet meer gedrukt worden, maar dat is nooit gegarandeerd. Koop vooral wat je zelf leuk vindt.
MD],

    ['key'=>'tcg', 'slug'=>'pokemon-tcg', 'anchor'=>'Pokémon TCG uitgelegd', 'updated'=>'2026-09-27',
     'title'=>'Pokémon TCG: wat is het en hoe speel je het kaartspel?',
     'seo_title'=>'Pokémon TCG: wat is het & hoe speel je het?',
     'seo_desc'=>'TCG staat voor Trading Card Game. Zo werkt het Pokémon kaartspel: de soorten kaarten, een beurt, hoe je wint, en hoe je begint met een starter deck.',
     'body'=><<<'MD'
TCG staat voor **Trading Card Game**: een ruilkaartspel. De Pokémon TCG is het officiële kaartspel van Pokémon, gespeeld door miljoenen mensen, van kinderen aan de keukentafel tot spelers op wereldkampioenschappen. Hier lees je wat de Pokémon TCG is en hoe je speelt.

## Wat is een Trading Card Game?

In een trading card game verzamel je kaarten, ruil je ze met anderen en bouw je er een eigen deck mee om tegen iemand te spelen. Nieuwe kaarten komen uit [booster packs](guide:boosters): je weet nooit precies wat je krijgt. Naast Pokémon zijn Magic: The Gathering en Yu-Gi-Oh! bekende TCG's.

## De drie soorten kaarten

- **Pokémon:** je vechters. Basis-Pokémon speel je meteen; evoluties leg je later op een basis-Pokémon.
- **Energie:** nodig om aanvallen te gebruiken. Elke aanval toont welke energie hij kost.
- **Trainer:** voorwerpen, supporters en stadions die je helpen: kaarten trekken, Pokémon genezen of zoeken in je deck.

## Zo speel je: de basis

1. **Deck van 60 kaarten.** Elke speler schudt zijn deck en trekt 7 kaarten.
2. **Actieve Pokémon.** Je legt een basis-Pokémon als actieve Pokémon, en tot 5 op je bank.
3. **Prijskaarten.** Je legt 6 kaarten gedekt opzij: je prijskaarten.
4. **Je beurt.** Trek een kaart, speel basis-Pokémon, evolueer, leg één energie aan, speel Trainerkaarten, en val aan.
5. **Knock-out.** Is de schade op een Pokémon gelijk aan zijn HP, dan is hij knock-out en neem je een prijskaart (bij sommige ex Pokémon meer).
6. **Winnen.** Je wint als je al je prijskaarten hebt, als je tegenstander geen Pokémon meer heeft, of als hij geen kaart meer kan trekken.

## Beginnen met spelen

De makkelijkste start is een speelklaar deck, zoals een [Starter Set ex](product:starter-set-ex-eevee-ex) of de [MEGA Start Deck 100 Battle Collection](product:mega-start-deck-100-battle-collection). Wil je extra packs, sleeves en dobbelstenen? Neem een [Elite Trainer Box](category:etb). Bescherm je kaarten met [sleeves en een deck box](category:accessories).

## Pokémon TCG Live

Naast het fysieke kaartspel bestaat er een digitale versie: Pokémon TCG Live. Codekaarten om kaarten in de app te krijgen, zitten in Engelstalige en andere westerse producten, niet in Japanse.

## Vragen over het Pokémon TCG

### Hoeveel kaarten zitten er in een Pokémon deck?

Precies 60, met maximaal 4 exemplaren van dezelfde kaart (basis-energie uitgezonderd).

### Vanaf welke leeftijd kan je Pokémon TCG spelen?

Het spel is bedoeld vanaf ongeveer 6 jaar. Jonge spelers beginnen het best met een speelklaar starter deck.

### Kan ik Japanse kaarten gebruiken om te spelen?

Thuis en met vrienden zeker. Voor officiële toernooien gelden regels over welke talen toegelaten zijn; kijk die altijd na bij de organisator.
MD],

    ['key'=>'fakes', 'slug'=>'nep-pokemon-kaarten-herkennen', 'anchor'=>'Nep Pokémon kaarten herkennen', 'updated'=>'2026-09-27',
     'title'=>'Echte of nep Pokémon kaarten herkennen: 8 controles',
     'seo_title'=>'Nep Pokémon kaarten herkennen: echt of vals?',
     'seo_desc'=>'Zo herken je nep Pokémon kaarten: de achterkant, de druk, de dikte, het lettertype, de kleuren en de verzegeling van boosters. Acht eenvoudige controles.',
     'body'=><<<'MD'
Namaak Pokémon kaarten zijn overal: op marktplaatsen, op beurzen en in goedkope "mystery boxes". Met deze acht controles herken je de meeste valse kaarten in een paar seconden.

## 8 controles om nep Pokémon kaarten te herkennen

1. **De achterkant.** Vergelijk met een kaart die je zeker echt weet. Bij namaak is het blauw vaak te licht of te paars, en de wervelingen zijn wazig. Japanse kaarten hebben een andere achterkant dan Engelse, dus vergelijk Japans met Japans.
2. **De kleuren en druk.** Echte kaarten zijn scherp gedrukt. Wazige tekst, vlekkerige kleuren of een glanzende laag over de hele kaart wijzen op namaak.
3. **Het lettertype.** Namaak gebruikt vaak een net iets ander lettertype, met rare spaties, spelfouten of verkeerde accenten.
4. **De dikte en stijfheid.** Een echte kaart is stevig en veert terug. Namaak is vaak te dun, te glad of te buigzaam.
5. **De rand.** Kijk naar de zijkant van de kaart: echte Engelse kaarten hebben vaak een dunne donkere laag in het midden. Namaak is door en door wit.
6. **De holo.** Echte holo's hebben een specifiek patroon per zeldzaamheid. Een regenbooglaag over de hele kaart, ook over de tekst, is verdacht.
7. **De getallen.** HP, schade en kaartnummer moeten kloppen met de officiële kaart. Absurde waarden (HP 1000) zijn altijd nep.
8. **De verzegeling.** Booster packs en boxen moeten strak en netjes verzegeld zijn. Dubbele folie, lijmresten of losse naden kunnen betekenen dat een pack opnieuw verzegeld is.

## Opnieuw verzegelde boosters

Niet alleen kaarten worden nagemaakt: soms worden packs geopend, de zeldzame kaarten eruit gehaald en de packs opnieuw dichtgelijmd. Koop verzegelde producten daarom bij een betrouwbare verkoper. Alles bij {brand} komt uit Japanse distributie en wordt verzonden zoals het uit de fabriek kwam.

## Twijfel je over een dure kaart?

Laat de kaart graden door PSA, BGS of CGC: zij controleren de echtheid voor ze een grade geven. Graded kaarten zitten in een verzegelde case met een certificaatnummer dat je online kan opzoeken. Zie onze [PSA graded Pokémon kaarten](cards:psa).

## Vragen over nep Pokémon kaarten

### Zijn goedkope Pokémon kaarten altijd nep?

Niet altijd, maar een prijs die veel te laag is, is een waarschuwing. Vergelijk met onze [Pokémon kaarten waarde](guide:prices) pagina.

### Zijn Japanse Pokémon kaarten vaker nep?

Er bestaat namaak van alle talen. Japanse kaarten koop je het veiligst bij een verkoper die rechtstreeks uit Japan inkoopt. Lees meer over [Japanse Pokémon kaarten](guide:japanese).
MD],

    ['key'=>'template', 'slug'=>'pokemon-kaart-maken', 'anchor'=>'Pokémon kaart maken (gratis template)', 'updated'=>'2026-09-27',
     'title'=>'Zelf een Pokémon kaart maken: gratis template',
     'seo_title'=>'Pokémon kaart maken: gratis template om te printen',
     'seo_desc'=>'Maak je eigen Pokémon kaart met onze gratis template op het echte formaat (63 × 88 mm), met afloop en veilige zone. Tips voor het ontwerp en het printen.',
     'body'=><<<'MD'
Zelf een Pokémon kaart maken is een leuk project, voor een verjaardag, een klas of gewoon voor jezelf. Met onze gratis template heb je meteen het juiste formaat.

## Gratis template

{card_template}

## Het formaat van een Pokémon kaart

Een Pokémon kaart meet **63 × 88 mm** (ongeveer 2,5 × 3,5 inch), met afgeronde hoeken. Japanse en Engelse kaarten zijn even groot, dus je eigen kaart past in dezelfde sleeves en mappen.

- **Afloop (bleed):** 3 mm rondom, zodat er na het snijden geen witte randjes zijn
- **Veilige zone:** hou tekst en belangrijke details een paar millimeter binnen de snijlijn

## Zo maak je je eigen Pokémon kaart

1. **Download de template** en open ze in een teken- of ontwerpprogramma dat SVG leest.
2. **Kies je illustratie** en plaats ze in het bovenste vak.
3. **Voeg de tekst toe:** naam, HP, type, aanvallen met hun energiekosten en schade.
4. **Print op 100 %** ("werkelijke grootte"), niet "aanpassen aan pagina", op stevig papier of karton.
5. **Snijd langs de snijlijn** en rond de hoeken af.
6. **Steek hem in een sleeve** voor een echte kaartfeeling. Zie onze [sleeves](category:accessories).

## Mag dat zomaar?

Een eigen kaart voor persoonlijk gebruik maken is een leuk knutselproject. Verkoop geen kaarten die op echte Pokémon kaarten lijken en gebruik ze niet in officiële toernooien: Pokémon en de kaartvormgeving zijn beschermd.

## Hoe ziet een echte kaart eruit?

Wil je weten waar alles staat op een echte kaart? Lees [hoe het Pokémon TCG werkt](guide:tcg), of vergelijk met echte kaarten via [nep Pokémon kaarten herkennen](guide:fakes).
MD],
  ],

  /* key: the same page in French, and how the shop finds it (terms, privacy, about, wholesale) */
  'pages' => [
    ['key'=>'about', 'slug'=>'over-ons', 'title'=>'Over {brand}',
     'seo_title'=>'Over {brand}: Japanse Pokémon kaarten voor België',
     'seo_desc'=>'{brand} is een onafhankelijke verkoper van echte Japanse Pokémon kaarten, met verzending vanuit Japan naar België.',
     'body'=><<<'MD'
{brand} is een onafhankelijke verkoper van echte Japanse Pokémon Trading Card Game producten. We verkopen verzegelde booster boxes, Elite Trainer Boxes, collection boxes, zeldzame losse kaarten en accessoires, rechtstreeks vanuit Japan verzonden naar heel België.

## Onze naam

{brand} ({kanji}) combineert twee Japanse woorden: **fuda** (札), een kaart, en **kura** (蔵), een opslagplaats. Een opslagplaats van Japanse ruilkaarten.

## Voor wie?

Voor verzamelaars, spelers en ouders die een cadeau zoeken, en voor kaartenwinkels, online winkels en toernooiorganisatoren die in grotere aantallen kopen. Zie onze [voorwaarden voor groothandel](page:wholesale).

## Wat we verkopen

- Verzegelde Japanse [booster boxes](category:boxes), van jubileumsets zoals [30th Celebration](set:30th-celebration) tot favorieten zoals [Pokémon Card 151](set:151)
- [Elite Trainer Boxes](category:etb) en [Pokémon boxen en decks](category:premium)
- [Zeldzame losse kaarten](category:singles), ook [PSA graded kaarten](cards:psa)
- [Accessoires](category:accessories): sleeves, mappen, deck boxes en playmats

## Hoe we werken

- **Alle prijzen online.** Geen account, geen aanvraag: de prijs per aantal staat op elke productpagina.
- **Ingekocht in Japan.** Alles komt uit Japanse distributie en wordt verzegeld verzonden in de originele verpakking. We verkopen nooit opnieuw verzegelde, nagedrukte of valse producten.
- **Verzonden naar België.** Elke bestelling vertrekt met tracking vanuit Japan, geprijsd in euro, zonder btw. Zie [Verzending & retour](page:shipping).
- **Echte antwoorden.** Vragen gaan naar een echt persoon op [{email}](mailto:{email}), in het Nederlands, Frans of Engels.

## Onafhankelijk

{company} is een onafhankelijk bedrijf. We zijn niet verbonden aan, goedgekeurd door of in licentie van The Pokémon Company, Nintendo, Creatures Inc. of GAME FREAK Inc.

## Bedrijfsgegevens

{company} · {address} · [{email}](mailto:{email})
MD],
    ['key'=>'wholesale', 'slug'=>'pokemon-kaarten-groothandel', 'title'=>'Pokémon kaarten groothandel voor winkels en verkopers',
     'seo_title'=>'Pokémon kaarten groothandel België: Japanse TCG',
     'seo_desc'=>'Japanse Pokémon kaarten in groothandel voor Belgische kaartenwinkels, online winkels en toernooiorganisatoren: minimumafname, prijs per aantal, geen aanvraag.',
     'body'=><<<'MD'
{brand} levert echte Japanse Pokémon Trading Card Game producten aan winkels en verkopers in heel België: verzegelde booster boxes, Elite Trainer Boxes, collection boxes, losse kaarten en accessoires, rechtstreeks vanuit Japan verzonden.

## Groothandel zonder aanvraag

Bij de meeste groothandels moet je een account aanvragen, wachten op goedkeuring en daarna een prijslijst vragen. Bij ons niet. Elke productpagina toont de voorwaarden:

- **Minimumafname:** verzegelde producten beginnen bij een aantal dat bij de verpakking past, meestal 4 of 6 boxen; losse kaarten vanaf één.
- **Veelvouden:** je bestelt per verpakkingseenheid, zodat elke bestelling overeenkomt met hoe het product verpakt is.
- **Automatische staffelkorting:** de prijs per stuk daalt bij elk niveau, zoals 6+, 24+ of 36+, en wordt automatisch toegepast.
- **Minimumbestelling:** {min_order} inclusief verzending.

Controleer elke prijs met onze [prijschecker](guide:prices).

## Voor wie?

- **Kaartenwinkels en spelwinkels:** verzegelde Japanse boxen voor de rekken en voor box-opening events, en losse kaarten voor de vitrine.
- **Online verkopers:** cases van nieuwe Japanse sets vanaf de release, met tracking op elke zending.
- **Toernooiorganisatoren:** booster boxes als prijzen, en Elite Trainer Boxes en accessoires voor events.

## Betalen en factuur

Je betaalt in Bitcoin op de bestelpagina, of in ETH of USDT tegen factuur. We rekenen geen btw aan: het totaal is wat je betaalt. Vermeld je ondernemingsnummer in de opmerkingen bij je bestelling, dan zetten we het op je factuur.

## Grote bestellingen en pre-orders

Wil je volledige cases, vaste bestellingen of een toewijzing op een komende release? Mail [{email}](mailto:{email}) met de sets en aantallen, en we komen terug met een prijs.
MD],
    ['key'=>'terms', 'slug'=>'algemene-voorwaarden', 'title'=>'Algemene verkoopvoorwaarden',
     'seo_title'=>'Algemene verkoopvoorwaarden | {brand}',
     'seo_desc'=>'De verkoopvoorwaarden van {brand}: prijzen in euro zonder btw, bestellen, betalen in crypto, levering in België, 14 dagen herroepingsrecht en garantie.',
     'body'=><<<'MD'
Deze voorwaarden gelden voor elke bestelling bij {brand}. Door te bestellen ga je ermee akkoord. Ben je consument, dan gaan je wettelijke rechten altijd voor.

## 1. Wie we zijn

{company}, {address}. E-mail: [{email}](mailto:{email}).

## 2. Producten en prijzen

We verkopen echte Japanse Pokémon Trading Card Game producten. Prijzen staan in euro. We rekenen geen btw of andere belastingen aan: het totaal bij het afrekenen, inclusief verzending, is wat je ons betaalt. Zoals bij elk pakket van buiten de EU kan de vervoerder bij sommige bestellingen invoerkosten vragen; die betaal je aan de vervoerder.

Afbeeldingen dienen ter illustratie. Kennelijke fouten in een prijs of beschrijving binden ons niet; we nemen dan contact met je op voor we iets aanrekenen.

## 3. Bestellen

Je bestelling is een aanbod om te kopen. Je krijgt een bevestiging per e-mail; de koop is gesloten wanneer we je betaling aanvaarden. De minimumbestelling is {min_order} inclusief verzending. Verzegelde producten verkopen we per verpakkingseenheid, zoals op elke productpagina staat.

## 4. Betalen

Je betaalt in Bitcoin, op je bestelpagina, of in ETH of USDT naar het walletadres dat we je per e-mail sturen. Je producten blijven {hold_hours} uur gereserveerd. Komt de betaling niet binnen die termijn binnen, dan mogen we de reservering vrijgeven.

## 5. Levering

We verzenden binnen {hold_hours} uur na betaling vanuit Japan, met tracking. De levertijden op [Verzending & retour](page:shipping) zijn schattingen. Het risico gaat op jou over bij levering aan jou of aan iemand die je aanwijst.

## 6. Herroepingsrecht

Ben je consument, dan mag je tot **14 dagen na levering** zonder opgave van reden van de koop afzien. Laat het ons weten per e-mail aan [{email}](mailto:{email}) met je bestelnummer; je mag daarvoor het Europese modelformulier gebruiken, maar dat hoeft niet. Stuur de producten binnen 14 dagen na je melding terug; de kosten van het terugsturen zijn voor jou.

We betalen alle betaalde bedragen terug, inclusief de oorspronkelijke standaard verzendkosten, binnen 14 dagen na je melding, op de manier waarop je betaalde (voor crypto: naar een walletadres dat je bevestigt, tegen de koers van die dag). We mogen wachten tot de producten terug zijn.

Je bent aansprakelijk voor waardevermindering door meer te doen dan nodig om het product te bekijken. Een verzegelde box of booster pack openen hoort daarbij: bij geopende of niet meer verzegelde producten mogen we de waardevermindering inhouden.

Zakelijke kopers hebben geen herroepingsrecht.

## 7. Garantie

Consumenten hebben de wettelijke garantie van 2 jaar op gebreken die bij levering al bestonden. Meld een gebrek zo snel mogelijk; transportschade binnen 7 dagen na levering, met foto's. Zie [Verzending & retour](page:shipping).

## 8. Pre-orders

Pre-orders worden gefactureerd bij toewijzing van de voorraad. Releasedatums worden bepaald door de uitgever en kunnen verschuiven. Kunnen we een pre-order niet leveren, dan betalen we alles terug.

## 9. Klachten

Mail [{email}](mailto:{email}); we antwoorden binnen {reply_hours} uur. Komen we er samen niet uit, dan kan je als consument terecht bij de Consumentenombudsdienst (consumentenombudsdienst.be).

## 10. Toepasselijk recht

Op deze voorwaarden is het recht van toepassing dat volgt uit de regels van internationaal privaatrecht. Als consument in België behoud je altijd de bescherming van de dwingende regels van het Belgische recht.

## 11. Onafhankelijk

{company} is niet verbonden aan The Pokémon Company, Nintendo, Creatures Inc. of GAME FREAK Inc. Alle productnamen en merken zijn eigendom van hun eigenaars.
MD],
    ['key'=>'privacy', 'slug'=>'privacybeleid', 'title'=>'Privacybeleid',
     'seo_title'=>'Privacybeleid | {brand}',
     'seo_desc'=>'Welke gegevens {brand} verzamelt bij een bestelling, waarom, hoe lang we ze bewaren en welke rechten je hebt onder de GDPR.',
     'body'=><<<'MD'
We gebruiken je gegevens alleen om je bestelling te verwerken en je vragen te beantwoorden. Dit is wat we bijhouden en waarom.

## Wie verantwoordelijk is

{company}, {address}, [{email}](mailto:{email}).

## Welke gegevens

- **Bestelgegevens:** naam, bedrijf, e-mail, telefoon, leveradres, bestelde producten, opmerkingen en betaalmethode.
- **Betaalgegevens bij Bitcoin:** het bedrag en de transactie op de openbare blockchain. We krijgen nooit je wallet-sleutels.
- **Technische gegevens:** je IP-adres bij een bestelling (tegen misbruik) en een sessiecookie voor je winkelwagen en taalkeuze.
- **Chat:** als je de live chat gebruikt, verwerkt de chatdienst (Tawk.to) je berichten.

## Waarom

Om je bestelling uit te voeren (overeenkomst), om fraude te voorkomen (gerechtvaardigd belang) en om te voldoen aan wettelijke bewaarplichten.

## Met wie we delen

Alleen met wie nodig is om je bestelling te leveren: de vervoerder en de douane (naam, adres, telefoon, inhoud en waarde), onze webhost en e-maildienst, en de chatdienst als je chat. Om Bitcoin-betalingen te controleren raadplegen we openbare blockchaindiensten (mempool.space, blockstream.info); daarbij delen we geen persoonsgegevens. We verkopen je gegevens nooit.

Je bestelling wordt vanuit Japan verwerkt en verzonden; Japan heeft een adequaatheidsbesluit van de Europese Commissie.

## Hoe lang

Bestelgegevens bewaren we zolang de wet ons verplicht voor onze boekhouding, en niet langer dan nodig.

## Je rechten

Je mag je gegevens inkijken, verbeteren of laten wissen, bezwaar maken en ze opvragen in een leesbaar formaat. Mail [{email}](mailto:{email}). Ben je niet tevreden, dan kan je klacht indienen bij de Gegevensbeschermingsautoriteit (gegevensbeschermingsautoriteit.be).

## Cookies

We gebruiken alleen een noodzakelijke sessiecookie. Gebruik je de chat, dan plaatst de chatdienst zijn eigen cookies.
MD],
  ],

  'faqs' => [
    ['Verzenden jullie Pokémon kaarten naar België?', 'Ja. Alles vertrekt vanuit Japan en wordt met tracking geleverd in heel België. Kies bij het afrekenen {standard} ({standard_days}) of {express} ({express_days}); de verzending wordt berekend op het gewicht van je bestelling.'],
    ['Zijn jullie Pokémon kaarten echt?', 'Ja. Alles komt uit Japanse distributie en wordt verzegeld verzonden in de originele fabrieksverpakking. We verkopen nooit opnieuw verzegelde, nagedrukte of valse producten.'],
    ['Rekenen jullie btw aan?', 'Nee. We rekenen geen btw of andere belastingen aan: het totaal bij het afrekenen, inclusief verzending, is wat je ons betaalt. Zoals bij elk pakket van buiten de EU kan de vervoerder bij sommige bestellingen invoerkosten vragen; die betaal je aan de vervoerder.'],
    ['Hoe betaal ik?', 'In crypto. Bitcoin betaal je meteen op je bestelpagina: scan de QR-code of kopieer het bedrag en adres in je wallet. Voor ETH of USDT sturen we je binnen {reply_hours} uur ons walletadres met je factuur.'],
    ['Wat is de minimumbestelling?', 'Elke bestelling moet minstens {min_order} bedragen, inclusief verzending. Verzegelde producten verkopen we per verpakkingseenheid (meestal per vier of zes); losse kaarten vanaf één.'],
    ['Is verzending gratis?', 'Ja, vanaf {free_ship} aan goederen verzenden we gratis met {standard}, automatisch bij het afrekenen. Kies je {express}, dan betaal je alleen het verschil.'],
    ['Heb ik een account nodig?', 'Nee. Elke productpagina toont de minimumafname en de prijs per aantal. Je bestelt zonder account of aanvraag.'],
    ['Waarom Japanse Pokémon kaarten?', 'Japanse sets verschijnen maanden voor de Engelse, Japanse booster boxes zijn goedkoper per box, en de drukkwaliteit is uitstekend. Japanse en Engelse kaarten zijn even groot (63 × 88 mm).'],
    ['Kan ik mijn bestelling terugsturen?', 'Ja. Als consument heb je 14 dagen bedenktijd na levering. Laat het ons per e-mail weten en stuur de producten terug; bij geopende boxen of packs mogen we de waardevermindering inhouden. Details staan op onze pagina Verzending & retour.'],
    ['Kan ik een nieuwe set reserveren?', 'Ja. Pre-orders reserveren je toewijzing voor de release aan de getoonde prijzen, en worden pas gefactureerd bij toewijzing.'],
    ['Wanneer wordt mijn bestelling verzonden?', 'Je producten blijven {hold_hours} uur gereserveerd. Zodra je betaling binnen is, verzenden we binnen {hold_hours} uur vanuit Japan en mailen we je trackingnummer.'],
    ['Wat als er iets beschadigd of fout aankomt?', 'Meld transportschade, ontbrekende of verkeerde producten binnen 7 dagen na levering, met foto\'s. We vervangen of betalen terug, met de verzendkosten.'],
    ['Antwoorden jullie in het Nederlands?', 'Ja. Mail {email} in het Nederlands, Frans of Engels; we antwoorden binnen {reply_hours} uur.'],
  ],
];
