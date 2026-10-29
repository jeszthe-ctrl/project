<?php
/* What a fresh install starts with. Everything here is edited in admin.php afterwards and
   saved to data/site.php, so changing this file never overwrites the owner's edits.
   In page text: {deposit} {balance} {vet_check_hours} ({vet_check_hour}) {genetic_years} ({genetic_year}) {go_home_weeks} {email}
   {city} {state} {payment_methods} {registry} {reply_time} fill in the current settings, and a line
   with just {guarantees}, {steps}, {included}, {payments}, {puppies}, {licenses}, {breeder},
   {payee} or {contact} shows that block. */
if(!defined('BB_ROOT')){ http_response_code(404); exit; }

return [
  'settings' => [
    'brand'           => 'Beagle Brook',
    'domain'          => 'https://beaglebrook.com',
    'email'           => 'support@beaglebrook.com',
    'phone'           => '',               /* shown with Call and Text buttons, e.g. (513) 555-0123 */
    'city'            => 'Cincinnati',
    'state'           => 'OH',
    'state_name'      => 'Ohio',
    'registry'        => '',               /* e.g. AKC */
    'payee_name'      => '',               /* the name on your payment accounts, shown on the verification page */
    'license_text'    => '',               /* one per line, e.g. "Ohio Department of Agriculture dog breeder license #…" */
    'deposit_pct'     => 50,
    'vet_check_hours' => 72,
    'genetic_years'   => 1,
    'go_home_weeks'   => 8,
    'reply_time'      => 'the same day',
    'breeder_name'    => '',
    'breeder_story'   => '',
    'breeder_photo'   => '',
    'hero_photo'      => '',
    'share_photo'     => '',
    'tagline'         => 'Purebred beagle puppies, raised in Cincinnati and delivered to your door',
    'hero_title'      => 'Beagle puppies for sale, delivered to your door',
    'hero_lede'       => 'Healthy, family-raised purebred beagles from Cincinnati, Ohio. Every puppy comes vet checked, with age-appropriate shots, a microchip, starter food and full registration papers, backed by a written health guarantee.',
    'home_seo_title'  => 'Beagle Puppies for Sale | Beagle Brook, Cincinnati, Ohio',
    'home_seo_desc'   => 'Purebred beagle puppies for sale from Beagle Brook in Cincinnati, Ohio. Vet checked, shots, microchip, full papers and a written health guarantee. Delivered to your door.',
    'puppies_title'   => 'Beagle Puppies for Sale',
    'puppies_seo_title'=> 'Beagle Puppies for Sale Near You | Available Now | Beagle Brook',
    'puppies_seo_desc'=> 'See every beagle puppy available now at Beagle Brook: photos, price, age, color and parents. Vet checked, microchipped, full papers, delivered from Cincinnati, Ohio.',
    'puppies_intro'   => "Every beagle puppy we have available right now is listed here, with real photos, price, date of birth and both parents. Each one has been raised in our home in {city}, {state}, examined by our veterinarian and is ready for a family that will love them.\n\nFound the one? Open their page and send an inquiry: it takes two minutes, there is no payment at this stage, and we reply {reply_time}. A {deposit} deposit reserves your puppy once we've talked and you've seen them on a live video call.",
    'parents_intro'   => "Good puppies start with good parents. These are the adult beagles behind our litters: their registration, health testing, size and temperament. Ask us about any of them on your video call.",
    'litters_intro'   => "Our beagles have litters a few times a year, and the best puppies often go to families who are already on our waitlist. Join it below and we'll email you the moment a new litter is born, before the puppies are posted publicly.",
    'reviews_intro'   => "What families say after their Beagle Brook puppy comes home. Every review here is from a real buyer and is published with their permission.",
    'contact_intro'   => "Questions about a puppy, delivery or the breed? Send us a message and we'll reply {reply_time}. To ask about a specific puppy, use the inquiry form on that puppy's page.",
    'footer_blurb'    => 'Purebred beagle puppies raised in Cincinnati, Ohio. Vet checked, microchipped, full papers and a written health guarantee — delivered to your door.',
    'delivery_note'   => 'We deliver from Cincinnati, Ohio. Your delivery fee depends on distance and is confirmed in writing before you pay anything.',
    'facebook'        => '', 'instagram' => '', 'tiktok' => '', 'youtube' => '',
    'google_reviews_url' => '',
    'gsc_verification'=> '', 'bing_verification' => '', 'ga4_id' => '',
    'smtp_host'       => '', 'smtp_port' => '', 'smtp_secure' => 'tls', 'smtp_user' => '', 'smtp_pass' => '',
    'alert_phone'     => '', 'alert_sms_email' => '', 'twilio_sid' => '', 'twilio_token' => '', 'twilio_from' => '',
    'pretty_urls'     => false, 'pretty_auto' => false,
  ],

  /* shown on the site and offered in the inquiry form; payment details are sent by email, never shown publicly */
  'payments' => [
    ['key'=>'paypal',   'name'=>'PayPal',        'note'=>'', 'on'=>true],
    ['key'=>'zelle',    'name'=>'Zelle',         'note'=>'', 'on'=>true],
    ['key'=>'bank',     'name'=>'Bank transfer', 'note'=>'', 'on'=>true],
    ['key'=>'chime',    'name'=>'Chime',         'note'=>'', 'on'=>true],
    ['key'=>'applepay', 'name'=>'Apple Pay',     'note'=>'', 'on'=>true],
  ],

  'guarantees' => [
    ['title'=>'{vet_check_hour} vet check guarantee', 'text'=>'Take your puppy to your own vet within {vet_check_hours} of delivery. If they find a serious illness that was present when we delivered, you get a replacement puppy or your money back.'],
    ['title'=>'{genetic_year} genetic health guarantee', 'text'=>'Covers life-threatening hereditary and congenital conditions diagnosed by a licensed vet within {genetic_years} of delivery.'],
    ['title'=>'Real puppy guarantee', 'text'=>'You see your puppy live on a video call before you pay a deposit. The puppy on that call is the puppy we deliver.'],
    ['title'=>'Safe delivery guarantee', 'text'=>'If your puppy can’t travel safely on the agreed day, we reschedule at no extra cost, or refund every dollar you’ve paid.'],
    ['title'=>'Full papers guarantee', 'text'=>'Every puppy comes with full registration papers — never limited registration, and never an extra charge.'],
    ['title'=>'Deposit protection', 'text'=>'If we cancel, or can’t deliver your puppy healthy, your {deposit} deposit is refunded in full.'],
    ['title'=>'No hidden fees', 'text'=>'The price on each puppy’s page includes the vet check, shots, microchip, starter food and papers. Delivery is quoted in writing up front.'],
    ['title'=>'Lifetime take-back promise', 'text'=>'If you ever can’t keep your beagle, at any age, we will take them back or help rehome them. They will never go to a shelter.'],
  ],

  /* "What comes with your puppy" */
  'included' => [
    ['title'=>'Veterinarian health check', 'text'=>'A full exam by a licensed veterinarian before delivery, with a written health record.'],
    ['title'=>'Age-appropriate shots', 'text'=>'Puppy vaccinations given on schedule for their age, recorded in their health record so your vet can continue the series.'],
    ['title'=>'Microchip', 'text'=>'Microchipped before they leave us. We help you register the chip in your name.'],
    ['title'=>'Starter food', 'text'=>'A supply of the food your puppy is already eating, so their tummy has an easy first week.'],
    ['title'=>'Full registration papers', 'text'=>'Full registration papers in your name, with the registered names and numbers of both parents.'],
    ['title'=>'Written health guarantee', 'text'=>'A {vet_check_hour} vet check guarantee and a {genetic_year} genetic health guarantee, in writing.'],
  ],

  'steps' => [
    ['title'=>'Choose your puppy and send an inquiry', 'text'=>'Every puppy’s page has a short inquiry form. Tell us where you live and a little about your home. No payment at this stage.'],
    ['title'=>'Talk to us and meet your puppy on video', 'text'=>'We reply {reply_time} by phone, text or email, answer your questions and set up a live video call so you can see your puppy and ask anything.'],
    ['title'=>'Reserve with a {deposit} deposit', 'text'=>'Pay the deposit by {payment_methods}. You get a written receipt and sales agreement, and your puppy is marked Reserved on our site.'],
    ['title'=>'We plan your delivery', 'text'=>'We agree the date, time and delivery method with you, and confirm the delivery fee in writing.'],
    ['title'=>'Delivery day', 'text'=>'Your puppy arrives with their health record, microchip details, starter food and papers. The remaining {balance} is due at delivery.'],
    ['title'=>'Lifetime support', 'text'=>'Questions about feeding, training or health? Call, text or email us any time, for your beagle’s whole life.'],
  ],

  'puppies' => [],
  'parents' => [],
  'litters' => [],
  'reviews' => [],
  'removed' => ['articles'=>[], 'pages'=>[]],

  'pages' => [
    [
      'slug'=>'how-it-works', 'menu'=>'How it works', 'title'=>'How to buy a beagle puppy from Beagle Brook',
      'seo_title'=>'How Buying a Beagle Puppy Works | Beagle Brook',
      'seo_desc'=>'How buying a beagle puppy from Beagle Brook works: inquiry, live video call, {deposit} deposit, delivery to your door and lifetime support.',
      'body'=><<<'TXT'
Bringing a puppy home should feel exciting, not risky. Here is exactly what happens from the moment you find a puppy you love to the day they arrive at your door — no surprises, no pressure and no payment until you've spoken to us and seen your puppy live.

## Step by step

{steps}

## What your puppy comes with

{included}

## Deposit and balance

A **{deposit} deposit** reserves your puppy. It is part of the puppy's price, not an extra fee. The remaining **{balance}** is due at delivery. Before you pay anything you'll receive a written sales agreement that lists your puppy, the full price, the deposit, the delivery fee and our guarantees.

We accept {payment_methods}. Payment details are only ever sent from **{email}**, after we've spoken with you. Read our [deposit and refund policy](/deposit-refund-policy) for the details.

## Delivery only

We deliver every puppy to your door from {city}, {state}. Your delivery date, method and fee are agreed with you and confirmed in writing. See [puppy delivery](/puppy-delivery) for how it works.

## Our guarantees

{guarantees}

Read the full [health guarantee](/health-guarantee).

## Ready?

Browse our [beagle puppies for sale](/beagle-puppies-for-sale), or [join the waitlist](/upcoming-litters) to hear about the next litter first.
TXT
    ],
    [
      'slug'=>'health-guarantee', 'menu'=>'Health guarantee', 'title'=>'Our beagle puppy health guarantee',
      'seo_title'=>'Beagle Puppy Health Guarantee | Beagle Brook',
      'seo_desc'=>'Every Beagle Brook puppy comes with a {vet_check_hour} vet check guarantee, a {genetic_year} genetic health guarantee, safe delivery and a lifetime take-back promise.',
      'body'=><<<'TXT'
We stand behind every beagle we raise. Every Beagle Brook puppy is examined by a licensed veterinarian before delivery and comes with the written guarantees below. They are part of the sales agreement you receive before paying a deposit.

{guarantees}

## 1. The {vet_check_hour} vet check guarantee

Within **{vet_check_hours} of delivery**, take your puppy to a licensed veterinarian of your choice for a wellness exam. We strongly recommend it for every new puppy.

If your vet finds a serious illness or condition that was present when we delivered your puppy and makes them unfit to keep, email us your vet's written findings within that time. You may choose:

- a **replacement puppy** of equal value from a current or upcoming litter, or
- a **full refund of the purchase price** once the puppy is returned to us.

## 2. The {genetic_year} genetic health guarantee

For **{genetic_years} from the date of delivery**, we guarantee your puppy against hereditary and congenital conditions that are life-threatening or that seriously affect their quality of life, diagnosed by a licensed veterinarian.

If that happens, we will provide a **replacement puppy** of equal value from a current or upcoming litter. You do not have to return your beagle — we know they are family by then.

## What isn't covered

Some things are common in young puppies, are easily treated and are not hereditary defects. We will always help and advise, but they are not grounds for a refund or replacement:

- intestinal parasites such as worms, coccidia or giardia, which can appear after the stress of moving home
- mild kennel cough, ear mites, minor skin conditions or allergies
- low blood sugar (hypoglycemia) after delivery, which is usually caused by missed meals
- small umbilical hernias that don't need surgery, cherry eye, and minor bite alignment
- illness or injury caused by accidents, environment, diet, obesity or care after delivery

## What we ask of you

- Have your puppy examined by a licensed vet within {vet_check_hours} of delivery.
- Keep up the vaccination schedule your vet recommends.
- Feed a complete, balanced puppy food and keep your beagle at a healthy weight.
- Tell us within 7 days of a diagnosis and send us the vet records.
- Let us ask a second opinion from a licensed vet if needed (at our cost).

## How to make a claim

Email **{email}** with your name, your puppy's name, a description of the problem and your vet's written records. We reply {reply_time} and tell you, in writing, what we will do and when.

## Safe delivery guarantee

Your puppy is examined by our vet before they travel. If they are unwell, or it isn't safe for them to travel on the agreed day — because of weather or anything else — we reschedule at no extra cost. If we can't deliver your puppy healthy, everything you've paid is refunded in full.

## Lifetime take-back promise

If you can ever no longer keep your beagle, at any age and for any reason, contact us first. We will take them back or help find them a loving new home. A Beagle Brook beagle should never end up in a shelter.

## Lifetime breeder support

Buying a puppy from us is the start of a relationship. Call, text or email us any time with questions about feeding, training, behavior or health — for your beagle's whole life.

## Questions

### Do I have to take my puppy to the vet within {vet_check_hours}?
Yes, to keep the vet check guarantee. It is also the best thing you can do for your puppy: your vet sees them healthy, records a baseline, and plans the rest of their vaccinations.

### Do I have to return my beagle to use the genetic guarantee?
No. Under the {genetic_year} genetic health guarantee we provide a replacement puppy and you keep your beagle.

### Is the health guarantee in writing?
Yes. It is part of the sales agreement you receive and sign before paying your deposit.
TXT
    ],
    [
      'slug'=>'deposit-refund-policy', 'menu'=>'Deposit & refunds', 'title'=>'Deposit and refund policy',
      'seo_title'=>'Deposit & Refund Policy for Beagle Puppies | Beagle Brook',
      'seo_desc'=>'How the {deposit} deposit works at Beagle Brook: what it reserves, when the balance is due, which payment methods we accept, and when deposits are refunded.',
      'body'=><<<'TXT'
A deposit is a big step, so we keep the rules short, clear and in writing.

## How the deposit works

- A **{deposit} deposit** reserves the puppy you choose. It is part of the puppy's price.
- The remaining **{balance}** of the price, plus the delivery fee, is due **at delivery**.
- You only pay a deposit after we've spoken with you and you've seen your puppy on a live video call.
- Before you pay, you receive a written sales agreement with the puppy's name, date of birth, price, deposit, delivery fee and our guarantees.
- Once your deposit arrives, we send a written receipt and mark your puppy **Reserved** on our website, usually within a few hours.

## How to pay

We accept {payment_methods}.

{payments}

Payment details are only ever sent from **{email}**, after we've spoken with you. If you ever receive payment instructions from any other address, phone number or social media account, don't pay — contact us. See [how to verify us](/is-beagle-brook-legit).

## When your deposit is refunded in full

- We cancel the sale for any reason.
- Your puppy is found to be unwell by our veterinarian before delivery and you don't want a replacement.
- We can't deliver your puppy healthy within two weeks of the agreed date.

Refunds are sent within 7 days, by the same method you paid with where possible.

## If you change your mind

We hold your puppy for you and turn other families away, so a deposit is not refundable if you change your mind. It is never simply lost, though: you can **transfer it once to another puppy** from a current or upcoming litter within 12 months.

## Questions

### Can I pay the full price up front?
Yes, if you prefer. The same refund rules apply to everything you've paid.

### Is there anything else to pay?
Only the delivery fee, which depends on distance and is confirmed in writing before you pay. The puppy's price already includes the vet check, shots, microchip, starter food and full registration papers.

### Do you accept gift cards or cryptocurrency?
No, never. Anyone asking you to pay for a Beagle Brook puppy that way is not us.
TXT
    ],
    [
      'slug'=>'puppy-delivery', 'menu'=>'Puppy delivery', 'title'=>'Beagle puppy delivery from Cincinnati, Ohio',
      'seo_title'=>'Beagle Puppy Delivery From Cincinnati, OH | Beagle Brook',
      'seo_desc'=>'How Beagle Brook delivers beagle puppies from Cincinnati, Ohio to your door: delivery methods, cost, health certificate, what to expect and how to prepare.',
      'body'=><<<'TXT'
Every Beagle Brook puppy is delivered to your door from our home in {city}, {state}. Delivery is the only way our puppies go home, so we've made it as safe, calm and simple as we can.

## How delivery works

1. **Agree the details.** Once your puppy is reserved, we agree the delivery date, the time window and the delivery method with you. Puppies travel once they are at least {go_home_weeks} old.
2. **Vet check before travel.** Our veterinarian examines your puppy shortly before delivery. Puppies traveling to another state travel with a health certificate from our vet.
3. **Delivery day.** We keep you updated on the journey. When your puppy arrives, you check them over, pay the remaining {balance}, and receive their health record, microchip details, starter food and registration papers.

## Delivery methods

- **Ground delivery** — your puppy travels by car in a secure, comfortable crate, with regular breaks for water, food and potty.
- **Flight nanny** — for longer distances, a person flies with your puppy in the cabin and hands them to you at the airport.

We'll recommend the best option for your location and your puppy's age.

## How much does delivery cost?

Delivery depends on distance from {city}, {state}. We quote it in writing before you pay a deposit — the price we quote is the price you pay.

## Getting ready for your puppy

- A crate sized for an adult beagle, with a divider for now
- Food and water bowls, and the food your puppy is used to (we send a starter supply)
- A collar or harness and a 6-foot leash
- Chew toys, a soft bed and enzyme cleaner for accidents
- A vet appointment booked within {vet_check_hours} of delivery

Our [beagle puppy care guide](/guides/beagle-puppy-care) covers the first weeks at home in detail.

## Questions

### Can I pick up my puppy instead?
We are a delivery-only breeder: every puppy is delivered to your door.

### How old are puppies when they are delivered?
At least {go_home_weeks} old, and only once our vet is happy that they are healthy and ready.

### What if the weather is bad on delivery day?
Your puppy's safety comes first. If it isn't safe to travel, we reschedule at no extra cost.
TXT
    ],
    [
      'slug'=>'whats-included', 'menu'=>'What’s included', 'title'=>'What comes with your Beagle Brook puppy',
      'seo_title'=>'What Comes With Your Beagle Puppy | Beagle Brook',
      'seo_desc'=>'Every Beagle Brook puppy comes with a vet health check, age-appropriate shots, a microchip, starter food, full registration papers and a written health guarantee.',
      'body'=><<<'TXT'
The price on each puppy's page is the price of the puppy and everything below. No add-ons, no "registration fee", no surprises.

{included}

## Your puppy's paperwork

On delivery day you receive:

- your puppy's **health record**: the vet check, the vaccinations given with dates, and when the next ones are due
- the **microchip number**, so you can register it in your name
- **full registration papers**, with both parents' registered names and numbers
- your copy of the **sales agreement and health guarantee**

## Our guarantees

{guarantees}

See the full [health guarantee](/health-guarantee), or browse [beagle puppies for sale](/beagle-puppies-for-sale).
TXT
    ],
    [
      'slug'=>'is-beagle-brook-legit', 'menu'=>'Verify us', 'title'=>'Is Beagle Brook legit? How to verify us before you pay',
      'seo_title'=>'Is Beagle Brook Legit? How to Verify Us Before You Pay',
      'seo_desc'=>'How to check that Beagle Brook is a real beagle breeder before you pay: video calls, registration lookups, written agreements and the warning signs of puppy scams.',
      'body'=><<<'TXT'
Puppy scams are real, and beagles are one of the breeds scammers copy most. You should check any breeder before you send money — including us. Here is exactly how to verify Beagle Brook, and the warning signs to watch for anywhere else.

## How to verify Beagle Brook

1. **See your puppy live on video before paying.** We'll set up a live video call with the exact puppy you want. Ask us to hold up a note with your name and today's date, or to pick up the puppy and show you their markings. Recorded videos and photos can be stolen; a live call can't.
2. **Ask for the parents' registration numbers.** Every puppy page lists both parents. Ask for their registered names and numbers and look them up with the registry yourself.
3. **Check our email address.** We only email from **{email}**. Payment details only ever come from that address, after you've spoken to us.
4. **Get it in writing.** You receive a written sales agreement with your puppy's details, price, deposit, delivery fee and our guarantees before paying anything, and a written receipt for every payment.
5. **Check the name on the account.** Our payment accounts are in our business name.

{payee}

{licenses}

## Warning signs of a puppy scam

Walk away from any seller — anywhere — who:

- won't do a live video call, or always has an excuse
- asks for payment by gift card, cryptocurrency, Western Union or MoneyGram
- asks for surprise extra payments for "shipping insurance", a "climate-controlled crate", "vaccination certificates" or customs
- prices puppies far below everyone else, or pressures you to pay today
- uses photos you can find elsewhere online (a reverse image search takes seconds)
- won't give you a written agreement or the parents' registration details

## If someone claims to be us

If anyone contacts you claiming to be Beagle Brook from another email address, phone number or social media account, don't send money. Email us at **{email}** and we'll tell you straight away whether it's really us.

## Questions

### Can I visit before buying?
We are a delivery-only breeder, which is why we do live video calls with every family before any payment. On the call you can see your puppy, the parents and where they're raised, and ask anything.

### Are you licensed?
Our license and registration details, where they apply, are listed on this page.

### How do I know the puppy delivered is the one I chose?
The puppy you meet on your live video call is the puppy we deliver — it's our real puppy guarantee. Your health record and microchip number also match the puppy named on your sales agreement.
TXT
    ],
    [
      'slug'=>'about', 'menu'=>'About us', 'title'=>'About Beagle Brook',
      'seo_title'=>'About Beagle Brook | Beagle Breeder in Cincinnati, Ohio',
      'seo_desc'=>'Beagle Brook raises purebred beagle puppies in Cincinnati, Ohio and delivers them to families across the country. Meet the people and dogs behind our puppies.',
      'body'=><<<'TXT'
Beagle Brook raises one breed: the beagle. Our puppies are born and raised in our home in {city}, {state}, and delivered to families who are ready for a merry, curious, loyal little hound.

{breeder}

## How we raise our puppies

- **Raised in a home, not a kennel.** Puppies grow up around everyday sounds, people and gentle handling, so they're ready for family life.
- **Vet care from day one.** Every puppy is examined by a licensed veterinarian, gets age-appropriate shots and is microchipped before they leave us.
- **Healthy parents.** Our adult beagles are registered, health checked and chosen for good temperament. You can [meet them here](/our-beagles).
- **Going home at the right age.** Puppies are delivered no earlier than {go_home_weeks} old.

## What we promise every family

{guarantees}

## Why only beagles?

Beagles are cheerful, gentle with children, endlessly curious and happiest as part of a family. They also have a powerful nose, a big voice and a big appetite. Focusing on one breed means we can tell you honestly whether a beagle suits your home, and help you with everything that comes after. Our [beagle breed guide](/guides/beagle-dog-breed) is a good place to start.

## Talk to us

Email **{email}** or use our [contact page](/contact). We reply {reply_time}.
TXT
    ],
    [
      'slug'=>'faq', 'menu'=>'FAQ', 'title'=>'Frequently asked questions',
      'seo_title'=>'Beagle Puppy FAQ: Deposit, Delivery & Health | Beagle Brook',
      'seo_desc'=>'Answers to common questions about buying a beagle puppy from Beagle Brook: deposits, payment, delivery, papers, health guarantee and the beagle breed.',
      'body'=><<<'TXT'
Everything families usually ask before reserving a Beagle Brook puppy. Can't find your answer? [Contact us](/contact) — we reply {reply_time}.

## Questions

### How do I reserve a puppy?
Open the puppy's page and send the inquiry form at the bottom. We'll reply {reply_time}, answer your questions and set up a live video call with your puppy. When you're ready, a {deposit} deposit reserves them. See [how it works](/how-it-works).

### How much is the deposit?
{deposit} of the puppy's price. It is part of the price, not an extra fee.

### When is the rest due?
The remaining {balance}, plus the delivery fee, is due at delivery.

### Which payment methods do you accept?
{payment_methods}. Payment details are only sent from {email} after we've spoken with you. We never accept gift cards or cryptocurrency.

### Is the deposit refundable?
Yes, in full, if we cancel or can't deliver your puppy healthy. If you change your mind, it can be moved once to another puppy within 12 months. Read the [deposit and refund policy](/deposit-refund-policy).

### Can I pick up my puppy?
We are delivery-only: every puppy is delivered to your door from {city}, {state}.

### How much does delivery cost?
It depends on distance. We quote it in writing before you pay a deposit. See [puppy delivery](/puppy-delivery).

### What comes with my puppy?
A veterinarian health check, age-appropriate shots, a microchip, starter food, full registration papers and our written health guarantee. See [what's included](/whats-included).

### Do your puppies come with full papers?
Yes. Every puppy comes with full registration papers, never limited registration, at no extra cost.

### What health guarantee do you offer?
A {vet_check_hour} vet check guarantee and a {genetic_year} genetic health guarantee against life-threatening hereditary conditions, plus a lifetime take-back promise. Read the full [health guarantee](/health-guarantee).

### How old are puppies when they go home?
At least {go_home_weeks} old, and only when our veterinarian is happy they're ready.

### Can I see my puppy before I pay?
Yes. Every family sees their puppy on a live video call before paying a deposit.

### Can I meet the parents?
Both parents are listed on every puppy's page with their registration and health details, and you can see them on your video call. Meet [our beagles](/our-beagles).

### Are beagles good with children?
Beagles are famously gentle, patient and playful, which makes them a popular family dog. Like any puppy, they need supervision with small children and gentle, consistent training.

### What's the difference between a 13-inch and a 15-inch beagle?
Beagles come in two size varieties: up to 13 inches at the shoulder (usually under 20 pounds) and 13 to 15 inches (usually 20 to 30 pounds). Their temperament is the same. Each puppy's page shows the expected size.

### Do you have a waitlist?
Yes. [Join the waitlist](/upcoming-litters) and we'll email you as soon as a new litter is born, before puppies are posted publicly.

### How do I know you're a real breeder?
See [how to verify us](/is-beagle-brook-legit): live video calls, registration numbers you can look up, written agreements and a single email address for payments.

### What if I can't keep my beagle one day?
Contact us first. We will take them back or help rehome them, at any age.
TXT
    ],
    [
      'slug'=>'beagle-puppies-for-sale-ohio', 'menu'=>'Beagle puppies in Ohio', 'title'=>'Beagle puppies for sale in Ohio',
      'seo_title'=>'Beagle Puppies for Sale in Ohio | Beagle Brook, Cincinnati',
      'seo_desc'=>'Looking for beagle puppies for sale near you in Ohio? Beagle Brook raises purebred beagles in Cincinnati and delivers across Ohio and beyond. See available puppies.',
      'body'=><<<'TXT'
Beagle Brook is a beagle breeder based in **{city}, {state}**. Our puppies are raised in our home and delivered to families across Ohio — Cincinnati, Dayton, Columbus, Cleveland, Toledo, Akron and everywhere in between — as well as to neighboring states and beyond.

## Available beagle puppies

{puppies}

## Why choose an Ohio beagle breeder?

- **Shorter, easier journeys.** For Ohio families, delivery is a short trip, which is easier on a young puppy.
- **Same time zone, same seasons.** Easy to talk to us, and your puppy is already used to Ohio weather.
- **Lifetime local support.** We're a phone call or text away for advice, for your beagle's whole life.

## What every puppy comes with

{included}

## Owning a beagle in Ohio

- **Dog license.** Ohio requires dogs older than three months to be registered with the county auditor each year. Your puppy's microchip doesn't replace it.
- **Vaccinations.** Ohio requires rabies vaccination for dogs; your vet will give it once your puppy is old enough. Their health record shows what they've had so far.
- **Weather.** Beagles cope well with Ohio winters with a dry, warm place to sleep, and they need shade and water in summer.
- **Fenced yards.** A beagle's nose can lead them a long way. A secure fence and a leash in unfenced places keep them safe.

## How buying from us works

{steps}

## Questions

### Are your beagles bred in Ohio?
Yes. Every Beagle Brook puppy is born and raised in our home in {city}, {state}.

### Do you deliver outside Ohio?
Yes. We deliver to Kentucky, Indiana and other states. Delivery is quoted in writing before you pay anything.

### Can I find beagle puppies for sale near me in Ohio?
Our available puppies are listed above and on our [beagle puppies for sale](/beagle-puppies-for-sale) page. If none suit you right now, [join our waitlist](/upcoming-litters).
TXT
    ],
    [
      'slug'=>'terms-of-sale', 'menu'=>'Terms of sale', 'title'=>'Terms of sale',
      'seo_title'=>'Terms of Sale | Beagle Brook',
      'seo_desc'=>'The terms that apply when you reserve and buy a beagle puppy from Beagle Brook: inquiries, deposits, delivery, papers and guarantees.',
      'body'=><<<'TXT'
These terms apply when you reserve and buy a puppy from {brand}, {city}, {state}. Each sale also has its own written sales agreement, which you receive before paying a deposit; if anything in it differs from this page, the sales agreement applies.

## Inquiries

Sending an inquiry is free and doesn't commit you to anything. A puppy is only reserved once we've confirmed the sale with you in writing and received your deposit.

## Prices

The price shown on each puppy's page is in US dollars and includes the veterinarian health check, age-appropriate shots, microchip, starter food and full registration papers. Delivery is charged separately and quoted in writing before you pay.

## Deposit and payment

A {deposit} deposit reserves your puppy. The remaining {balance} and the delivery fee are due at delivery. See our [deposit and refund policy](/deposit-refund-policy).

## Delivery

We deliver every puppy, no earlier than {go_home_weeks} old, once our veterinarian has examined them. See [puppy delivery](/puppy-delivery).

## Registration papers

Every puppy is sold with full registration papers. Papers are transferred to you once the full price is paid.

## Health guarantee

Every puppy is covered by our written [health guarantee](/health-guarantee).

## Contact

{brand} · {city}, {state} · {email}
TXT
    ],
    [
      'slug'=>'privacy-policy', 'menu'=>'Privacy policy', 'title'=>'Privacy policy',
      'seo_title'=>'Privacy Policy | Beagle Brook',
      'seo_desc'=>'How Beagle Brook uses the information you send us in puppy inquiries, waitlist sign-ups and messages.',
      'body'=><<<'TXT'
This policy explains what information {brand} collects on this website and how we use it.

## What we collect

- **Puppy inquiries:** your name, email, phone number, delivery address, preferred payment method and any notes you write.
- **Waitlist:** your name, email, phone number (optional) and puppy preferences.
- **Messages:** what you send through our contact form.
- **Basic site statistics:** pages visited and the type of device, if analytics are enabled. We don't sell or share this.

## How we use it

Only to answer you, arrange the sale and delivery of your puppy, keep our records, and — if you joined our waitlist — email you about new litters. We never sell or rent your information.

## Who we share it with

Only people who help us deliver your puppy (such as a transport partner, who receives your name, phone and delivery address), and where the law requires.

## Waitlist emails

Every waitlist email has a link to unsubscribe instantly.

## Your choices

Email {email} to see, correct or delete the information we hold about you.

## Contact

{brand} · {city}, {state} · {email}
TXT
    ],
  ],

  'articles' => require BB_ROOT.'/inc/articles.php',
];
