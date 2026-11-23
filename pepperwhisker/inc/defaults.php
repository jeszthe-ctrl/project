<?php
/* What a fresh install starts with. Everything here is edited in admin.php and saved to data/site.php;
   this file is only read again for guides or pages added by a later version.
   In page text: {brand} {email} {author} {year} fill in the current settings. */
if(!defined('PW_ROOT')){ http_response_code(404); exit; }

return [
  'settings' => [
    'brand'           => 'Pepperwhisker',
    'domain'          => 'https://pepperwhisker.com',
    'email'           => 'hello@pepperwhisker.com',
    'tagline'         => 'The schnauzer guide: giant, standard and miniature',
    'hero_title'      => 'Everything schnauzer, from miniature to giant',
    'hero_lede'       => 'Clear, practical guides to all three schnauzer breeds: size, temperament, colors, grooming and haircuts, health, popular schnauzer mixes and how to find a puppy or rescue safely.',
    'home_seo_title'  => 'Schnauzer Guide: Miniature, Standard & Giant Schnauzers | Pepperwhisker',
    'home_seo_desc'   => 'The schnauzer explained: miniature, standard and giant schnauzer size, temperament, colors, haircuts, health, lifespan, mixes and finding a puppy safely.',
    'guides_intro'    => 'Every Pepperwhisker guide, grouped by topic. Start with the breed you are curious about, or jump straight to grooming, health or mixes.',
    'footer_blurb'    => 'Independent guides to giant, standard and miniature schnauzers: breed facts, grooming, health and buying a puppy safely.',
    'gallery_intro'   => 'Our own photos of miniature, standard and giant schnauzers: salt and pepper, black, black and silver and white coats, puppies and adults, fresh haircuts and scruffy ones. Click any photo to see it full size, or read the guide to each breed.',
    'contact_intro'   => 'Spotted a mistake, have a schnauzer question or want to share your dog’s story? Send us a message and we’ll get back to you by email.',
    'author_name'     => '',               /* the writer shown on guides; the brand is shown until a name is added */
    'author_bio'      => '',
    'author_photo'    => '',
    'hero_photo'      => '',
    'share_photo'     => '',
    'gsc_verification'=> '', 'bing_verification' => '', 'ga4_id' => '',
    'adsense_client'  => '',               /* ca-pub-…: turns on Google Auto ads and ads.txt */
    'affiliate_note'  => false,            /* show the affiliate line on guides */
    'facebook' => '', 'instagram' => '', 'pinterest' => '', 'youtube' => '',
    'smtp_host' => '', 'smtp_port' => '', 'smtp_secure' => 'tls', 'smtp_user' => '', 'smtp_pass' => '',
    'pretty_urls'     => false,            /* switched on by itself once the .htaccess rules are seen working */
  ],

  'redirects' => [],
  'gallery'   => [],          /* your own photos (Admin → Photos) */
  'removed'   => ['articles'=>[], 'pages'=>[]],

  'articles' => require PW_ROOT.'/inc/articles.php',

  'pages' => [
    [
      'slug'=>'about', 'menu'=>'About', 'title'=>'About Pepperwhisker',
      'seo_title'=>'About Pepperwhisker | Independent Schnauzer Guides',
      'seo_desc'=>'Pepperwhisker is an independent guide to giant, standard and miniature schnauzers. Who we are, what we cover and how we write our guides.',
      'body'=><<<'TXT'
Pepperwhisker is an independent website about schnauzers: the miniature, the standard and the giant. We write for people who are thinking about getting a schnauzer, people who just brought one home and owners who want to keep their dog healthy, well groomed and happy.

## What we cover

- **The three breeds:** what really separates a [miniature](/guides/miniature-schnauzer), a [standard](/guides/standard-schnauzer) and a [giant schnauzer](/guides/giant-schnauzer), and which one suits which home.
- **Colors and coats:** [salt and pepper](/guides/salt-and-pepper-schnauzer), [black](/guides/black-schnauzer), [white](/guides/white-schnauzer) and the colors that aren't part of the breed.
- **Grooming:** [haircut styles](/guides/schnauzer-haircut-styles) and how to [groom a schnauzer at home](/guides/how-to-groom-a-schnauzer).
- **Health:** [lifespan](/guides/miniature-schnauzer-lifespan), [common health problems](/guides/schnauzer-health-problems) and [food](/guides/schnauzer-dog-food).
- **Mixes:** from the [schnoodle](/guides/schnauzer-poodle-mix) to the [schnauzer-yorkie mix](/guides/yorkie-schnauzer-mix).
- **Getting a schnauzer:** what a [schnauzer costs](/guides/schnauzer-price), how to [buy a puppy safely](/guides/how-to-buy-a-schnauzer-puppy) and how to [adopt from a rescue](/guides/schnauzer-rescue).

## We don't sell dogs

Pepperwhisker doesn't breed or sell puppies, and no breeder pays to appear in our guides. That lets us tell you plainly what to look for in a breeder and which warning signs mean you should walk away.

## How we write

Our guides are based on the breed standards of the American Kennel Club and the FCI, the national breed clubs, veterinary sources and the experience of schnauzer owners. Read our [editorial policy](/editorial-policy) to see how we research, check and update them. Health guides are general information: always ask your own veterinarian about your dog.

{author_box}

Questions or corrections? [Contact us](/contact) — we read every message.
TXT
    ],
    [
      'slug'=>'editorial-policy', 'menu'=>'Editorial policy', 'title'=>'Editorial policy',
      'seo_title'=>'Editorial Policy | Pepperwhisker',
      'seo_desc'=>'How Pepperwhisker researches, writes, checks and updates its schnauzer guides, and how we handle corrections, advertising and affiliate links.',
      'body'=><<<'TXT'
We want every Pepperwhisker guide to be accurate, useful and honest. This is how we work.

## Sources

Breed facts (size, colors, temperament, history) come from the official breed standards of the American Kennel Club (AKC) and the Fédération Cynologique Internationale (FCI), and from the national breed clubs for the miniature, standard and giant schnauzer. Health information is based on veterinary sources and breed health surveys. Where sources disagree, such as lifespan figures, we say so and give the range.

## Health information

Our health and food guides are general information, not veterinary advice. They help you know what to ask; they never replace an examination by a licensed veterinarian who knows your dog.

## Prices

Prices for puppies, grooming and food change by region and over time. We give typical ranges and the date we last checked them.

## Independence

We don't breed or sell dogs, and breeders can't pay to be recommended. If we earn money from advertising or affiliate links, it never decides what we write. See our [advertising and affiliate disclosure](/advertising-disclosure).

## Corrections and updates

We review guides regularly and show the date a guide was last updated. If you spot a mistake, please [tell us](/contact) and we'll check and fix it.
TXT
    ],
    [
      'slug'=>'advertising-disclosure', 'menu'=>'Advertising & affiliates', 'title'=>'Advertising and affiliate disclosure',
      'seo_title'=>'Advertising & Affiliate Disclosure | Pepperwhisker',
      'seo_desc'=>'How Pepperwhisker is funded: display advertising and affiliate links, and how we keep them separate from our schnauzer guides.',
      'body'=><<<'TXT'
Pepperwhisker is free to read. To pay for writing and running the site, we may show advertising and use affiliate links.

## Advertising

Ads may be shown by Google AdSense or a similar network. Ads are chosen by the ad network, not by us, and an ad on a page is not a recommendation from Pepperwhisker. Ad networks may use cookies; see our [privacy policy](/privacy-policy).

## Affiliate links

Some links to products, such as grooming tools or dog food, may be affiliate links. If you buy through one, the shop may pay us a small commission at no extra cost to you. We only link to products where a link genuinely helps the guide, and commissions never decide what we recommend.

## What we never do

- We never accept payment from breeders, and we don't list puppies for sale.
- We never let an advertiser or affiliate partner change a guide.

Questions? [Contact us](/contact).
TXT
    ],
    [
      'slug'=>'privacy-policy', 'menu'=>'Privacy', 'title'=>'Privacy policy',
      'seo_title'=>'Privacy Policy | Pepperwhisker',
      'seo_desc'=>'What information Pepperwhisker collects when you read the site or contact us, how it is used and your choices.',
      'body'=><<<'TXT'
This policy explains what information {brand} collects and how it is used. Last updated: November 2026.

## When you contact us

If you send a message through our contact form, we receive your email address, your name if you give it, and your message. We use them only to reply to you. We keep messages for as long as needed to answer them and then delete them within 12 months.

## Analytics

If analytics are switched on, we use Google Analytics to understand which pages are read and on what kind of device. It uses cookies and does not tell us who you are.

## Advertising

If ads are shown, Google and its partners may use cookies to show ads based on your visits to this and other websites. You can turn off personalized advertising in Google's [Ad Settings](https://adssettings.google.com). Visitors in some regions are asked for consent before these cookies are used.

## What we never do

We never sell your personal information, and we never send marketing email you didn't ask for.

## Your choices

You can ask us to see or delete any message you sent us by emailing {email}.
TXT
    ],
    [
      'slug'=>'terms-of-use', 'menu'=>'Terms of use', 'title'=>'Terms of use',
      'seo_title'=>'Terms of Use | Pepperwhisker',
      'seo_desc'=>'The terms for using the Pepperwhisker website and its schnauzer guides.',
      'body'=><<<'TXT'
By using {brand} you agree to these terms.

## Information only

Our guides are general information about schnauzers. They are not veterinary, legal or professional advice. Always consult a licensed veterinarian about your dog's health, diet and medical care.

## Accuracy

We work hard to keep our guides accurate and up to date, but breed standards, prices and veterinary advice change. If you find a mistake, please [tell us](/contact).

## Links to other websites

We link to other websites, such as breed clubs and rescues, for your convenience. We aren't responsible for their content.

## Copyright

The text and images on {brand} belong to us or are used with permission. You may share links to our guides, and quote short passages with a link back, but please don't copy whole guides.

## Contact

Questions about these terms: {email}.
TXT
    ],
  ],
];
