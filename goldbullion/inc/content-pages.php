<?php
/* Information and policy pages (edited in Admin → Pages).
   {brand} {email} {phone} {standard_price} {express_price} {standard_days} {express_days}
   {pay_window} {reply_time} {dispatch_time} {hours} {legal_name} {address} {domain} fill in from Settings. */
if(!defined('GB_ROOT')){ http_response_code(404); exit; }

return [
[
  'slug'=>'about-us', 'title'=>'About Us', 'group'=>'company',
  'h1'=>'About {brand}',
  'seo_title'=>'About Us – Who We Are and How We Sell Gold Bullion',
  'seo_desc'=>'{brand} sells investment-grade gold bullion online with live spot pricing, locked prices, insured shipping and straightforward service. Learn how we work.',
  'body'=><<<'HTML'
<p class="lead">{brand} is an online gold bullion dealer for people who want to own real, physical gold without the jargon, pressure or hidden costs. We sell investment-grade gold bars and coins, price them openly from the live spot price, and deliver them fully insured to your door.</p>
<h2>What we sell</h2>
<p>We focus on the bullion products that are easiest to buy, store and eventually sell: 24 karat <a href="/gold-bars">gold bars</a> from 1 gram to 1 kilo and government-minted <a href="/gold-coins">gold bullion coins</a>. We stock products from established refiners and mints whose bars and coins are recognised by dealers everywhere — so that the gold you buy from us is as easy to sell as it is to buy.</p>
<h2>How we work</h2>
<ul>
<li><strong>Transparent pricing.</strong> Our prices are built from the live <a href="/gold-price">gold spot price</a> plus a clearly priced premium. We show the gold value of every product so you can see exactly what you’re paying for.</li>
<li><strong>Your price is locked.</strong> The price you see when you place your order is the price you pay, as long as payment arrives within {pay_window} hours.</li>
<li><strong>A real person handles your order.</strong> After you order we contact you by text or email {reply_time} with your payment details, and you can reply to that same thread with any question.</li>
<li><strong>Insured, discreet delivery.</strong> Every package is fully insured until it’s in your hands, sent in plain packaging with tracking and adult signature on delivery. See <a href="/shipping-returns">shipping &amp; returns</a>.</li>
<li><strong>Straight answers.</strong> We write plain-English <a href="/guides">guides</a> to everything from <a href="/guides/gold-bar-weight-and-sizes">gold bar sizes</a> to <a href="/guides/how-to-tell-if-gold-is-real">spotting fake gold</a>, because informed buyers make better decisions.</li>
</ul>
<h2>Our commitments to you</h2>
<ul>
<li>Every product we sell is genuine — backed by our <a href="/authenticity-guarantee">authenticity guarantee</a>.</li>
<li>We never pressure you to buy, and we never make promises about where the gold price will go.</li>
<li>We protect your personal information and never sell it. See our <a href="/privacy-policy">privacy policy</a>.</li>
<li>We follow the anti-money-laundering rules that apply to precious metals dealers. See our <a href="/aml-policy">AML policy</a>.</li>
</ul>
<h2>Talk to us</h2>
<p>Questions before you buy? Email <a href="mailto:{email}">{email}</a> or use our <a href="/contact">contact page</a>. We’re available {hours}.</p>
HTML,
  'faqs'=>[],
],
[
  'slug'=>'editorial-policy', 'title'=>'Editorial Policy', 'group'=>'company',
  'h1'=>'Editorial Policy',
  'seo_title'=>'Editorial Policy – How We Research and Write Our Guides',
  'seo_desc'=>'How {brand} researches, writes, fact-checks and updates its gold guides and price pages, where our data comes from, and how to report a correction.',
  'body'=><<<'HTML'
<p class="lead">Our guides and price pages exist to help people understand gold and make better decisions — whoever they buy from. This page explains how we create them and how we keep them accurate.</p>
<h2>Who writes our content</h2>
<p>Guides are written and reviewed by the {author}, the team at {brand} responsible for our product information and customer education. We write in plain English, answer the question first, and show the numbers behind every claim.</p>
<h2>Where our information comes from</h2>
<ul>
<li><strong>Live prices</strong> come from established precious-metals market data feeds and are refreshed through the trading day. Every price page shows when it was last updated.</li>
<li><strong>Product specifications</strong> come from the refiners and mints that make them (for example published weights, purity and dimensions).</li>
<li><strong>Market and industry facts</strong> come from primary sources such as the London Bullion Market Association (LBMA), the World Gold Council, the US Mint and other national mints.</li>
<li><strong>Rules and regulations</strong> are checked against official sources such as the Federal Trade Commission’s Jewelry Guides and the IRS. Tax rules are summarised in general terms only.</li>
</ul>
<h2>How we calculate values</h2>
<p>Values on our price pages and in our guides are melt values: weight × purity × the live spot price. One troy ounce is 31.1034768 grams. Karat purity is the karat divided by 24. Figures are rounded to the cent.</p>
<h2>Accuracy and updates</h2>
<ul>
<li>Each guide shows the date it was last updated.</li>
<li>We review guides regularly and when markets, products or rules change.</li>
<li>Historical prices are rounded approximations and are labelled as such.</li>
</ul>
<h2>Independence</h2>
<p>We sell gold bullion, and some guides link to products we sell. That never changes the facts we present. We don’t accept payment to mention other companies, and we don’t make predictions about where prices will go. Nothing we publish is financial advice — see our <a href="/disclaimer">disclaimer</a>.</p>
<h2>Corrections</h2>
<p>Spotted something wrong? Email <a href="mailto:{email}">{email}</a> with the page address and the correction. We review every report and update the page if needed.</p>
HTML,
  'faqs'=>[],
],
[
  'slug'=>'contact', 'title'=>'Contact Us', 'group'=>'company',
  'h1'=>'Contact Us',
  'seo_title'=>'Contact Us – Questions About Gold Bullion or Your Order',
  'seo_desc'=>'Contact {brand} about gold bars, coins, payment, shipping or an existing order. Email us or use the contact form — we reply {reply_time}.',
  'body'=><<<'HTML'
<p class="lead">Questions about a product, a payment or an order? Send us a message and we’ll reply {reply_time}. For an existing order, please include your order number (it starts with GB-).</p>
HTML,
  'faqs'=>[],
],
[
  'slug'=>'faq', 'title'=>'FAQ', 'group'=>'help',
  'h1'=>'Frequently Asked Questions',
  'seo_title'=>'Gold Bullion FAQ – Ordering, Payment, Shipping & Products',
  'seo_desc'=>'Answers to common questions about buying gold bullion: ordering, paying by Cash App, Zelle, Apple Pay, Chime or crypto, shipping and price locks.',
  'body'=><<<'HTML'
<p class="lead">Everything you need to know about buying gold bullion from us. Can’t find your answer? <a href="/contact">Contact us</a>.</p>
HTML,
  'faqs'=>[
    ['How do I place an order?', 'Add products to your cart, go to checkout, enter your name, email, phone number and delivery address, choose standard or express shipping and your payment method, and submit. You’ll see an order number straight away and receive a confirmation email. Full steps are on our <a href="/how-to-order">how to order</a> page.'],
    ['Why do you need my phone number?', 'We text you your payment details and may call to confirm larger or first-time orders. It also lets the carrier contact you about your insured delivery. We never share it for marketing.'],
    ['Which payment methods do you accept?', 'Cash App, Apple Pay, Zelle, Chime and cryptocurrency (Bitcoin, Ethereum, USDT and USDC). See <a href="/payment-methods">payment methods</a>.'],
    ['Why don’t I pay on the website?', 'Gold is sold on very thin margins, and card processing fees and chargeback risk would force us to charge everyone more. Instead, after you order we text or email you the exact amount and where to send it, so you pay directly with no added fees.'],
    ['How quickly will I receive payment details?', 'We send them by text or email {reply_time}, during our business hours ({hours}).'],
    ['When is my price locked?', 'The moment you place your order. The price stays fixed as long as your payment reaches us within {pay_window} hours.'],
    ['What happens if I don’t pay in time?', 'Unpaid orders are cancelled after {pay_window} hours. If you need more time, reply to your payment email before then and we’ll do our best to help.'],
    ['How much does shipping cost?', 'Standard shipping is {standard_price} ({standard_days}) and express shipping is {express_price} ({express_days}), whatever the size of your order.'],
    ['Is my order insured?', 'Yes. Every package is fully insured for its value until it is delivered and signed for.'],
    ['Do you ship discreetly?', 'Yes. Packages are plain and unbranded, with nothing on the outside to show what’s inside.'],
    ['When will my order ship?', 'We ship {dispatch_time} and email you a tracking number.'],
    ['Do you ship outside the United States?', 'At present we ship to addresses in the United States only.'],
    ['Can I ship to a PO Box?', 'No. Insured bullion shipments require a street address where an adult can sign for the package.'],
    ['Are your products genuine?', 'Yes. We sell only genuine bullion from established refiners and mints, and every order is covered by our <a href="/authenticity-guarantee">authenticity guarantee</a>.'],
    ['What purity is your gold?', 'Our gold bars are 24 karat, .999 or .9999 fine. Some bullion coins such as the American Gold Eagle and Krugerrand are 22 karat but still contain their full stated weight of pure gold.'],
    ['Can I return my gold?', 'Because gold prices move every minute, bullion can’t be returned for a change of mind once your price is locked and paid. If anything arrives damaged, incorrect or missing, tell us within 3 days and we’ll put it right. See <a href="/shipping-returns">shipping &amp; returns</a>.'],
    ['Do you charge sales tax?', 'Many US states exempt investment bullion from sales tax, while others tax some or all purchases. If tax applies to your order we’ll tell you before you pay.'],
    ['Will my purchase be reported to the government?', 'Buying gold is generally not reported. Precious metals dealers must report cash payments over $10,000 on IRS Form 8300 and may need to verify your identity on large orders. See our <a href="/aml-policy">AML policy</a>.'],
    ['How should I store my gold?', 'Keep it in its original packaging, out of sight, in a quality home safe or a bank safe-deposit box, and check your insurance cover. Our guide on <a href="/guides/how-to-store-gold-bullion">how to store gold at home</a> has the details.'],
    ['How do I track my order?', 'Use the tracking number in your shipping email, or enter your order number and email on our <a href="/track-order">track order</a> page.'],
  ],
],
[
  'slug'=>'how-to-order', 'title'=>'How to Order', 'group'=>'help',
  'h1'=>'How to Order Gold Bullion',
  'seo_title'=>'How to Order – Buying Gold Bullion From Us, Step by Step',
  'seo_desc'=>'How ordering works: choose your gold, check out, get payment details by text or email, pay, and receive insured, tracked delivery. Price locked at order.',
  'body'=><<<'HTML'
<p class="lead">Buying gold from us takes a couple of minutes online and a single payment. Here’s exactly what happens.</p>
<h2>1. Choose your gold</h2>
<p>Browse <a href="/gold-bars">gold bars</a> and <a href="/gold-coins">gold coins</a>. Prices follow the live <a href="/gold-price">gold spot price</a> and can change during the day.</p>
<h2>2. Check out</h2>
<p>Enter your name, email, mobile number and a US street address. Choose <strong>Standard</strong> ({standard_price}, {standard_days}) or <strong>Express</strong> ({express_price}, {express_days}) insured shipping, and pick how you’d like to pay: Cash App, Apple Pay, Zelle, Chime or crypto. When you submit, <strong>your price is locked</strong>.</p>
<h2>3. Receive your payment details</h2>
<p>You get an order confirmation email straight away. Then, {reply_time}, we text or email you the exact amount and the details for your chosen payment method. Payment details always come from <strong>{email}</strong> or our phone number — if you ever receive different payment details from anyone else, don’t pay and contact us.</p>
<h2>4. Pay within {pay_window} hours</h2>
<p>Send the payment and include your order number (e.g. GB-2611-A1B2C) in the note. For first orders and larger orders we may give you a quick call to confirm the details — this protects you as much as us.</p>
<h2>5. We ship, fully insured</h2>
<p>Once your payment clears, we pack your order in plain, unbranded packaging and ship it {dispatch_time}. You’ll receive a tracking number by email. An adult must sign for the delivery.</p>
<h2>Need help?</h2>
<p>Read the <a href="/faq">FAQ</a> or <a href="/contact">contact us</a>.</p>
HTML,
  'faqs'=>[],
],
[
  'slug'=>'payment-methods', 'title'=>'Payment Methods', 'group'=>'help',
  'h1'=>'Payment Methods',
  'seo_title'=>'Payment Methods – Cash App, Apple Pay, Zelle, Chime & Crypto',
  'seo_desc'=>'Pay for your gold bullion with Cash App, Apple Pay, Zelle, Chime or cryptocurrency. We send your payment details by text or email after you order.',
  'body'=><<<'HTML'
<p class="lead">We accept the payment methods below. You choose one at checkout, and after you place your order we text or email you the exact amount and where to send it.</p>
{payment_list}
<h2>Why we send payment details after you order</h2>
<p>Gold bullion trades on thin margins. Card processing fees of 3% or more — and the risk of chargebacks — would force us to raise prices for everyone. Paying directly keeps your cost down. It also lets us confirm every order personally before any money changes hands.</p>
<h2>Paying safely</h2>
<ul>
<li>We only send payment details from <strong>{email}</strong> or our own phone number. If someone else sends you payment details for one of our orders, do not pay — contact us.</li>
<li>Payments must come from an account in the name of the person who placed the order. We can’t accept third-party payments.</li>
<li>Always include your order number in the payment note.</li>
<li>We will never ask for your passwords, PINs, bank login or one-time security codes.</li>
<li>Payments by Cash App, Zelle, Chime and crypto can’t be reversed by your bank, so always double-check the recipient before you send.</li>
</ul>
<h2>Crypto payments</h2>
<p>We accept Bitcoin (BTC), Ethereum (ETH), Tether (USDT) and USD Coin (USDC). We send you a wallet address and the exact coin amount, which is valid for a short time because crypto prices move. Network fees are paid by the sender.</p>
HTML,
  'faqs'=>[
    ['Can I pay by credit card?', 'Not at the moment. We accept Cash App, Apple Pay, Zelle, Chime and crypto, which keeps our prices lower.'],
    ['How long do I have to pay?', '{pay_window} hours from when you place your order. Your price is locked for that time.'],
  ],
],
[
  'slug'=>'shipping-returns', 'title'=>'Shipping & Returns', 'group'=>'help',
  'h1'=>'Shipping & Returns',
  'seo_title'=>'Shipping & Returns – Insured Gold Bullion Delivery',
  'seo_desc'=>'Fully insured, discreet gold bullion shipping across the US: standard {standard_price} or express {express_price}. Adult signature required. Read our returns policy.',
  'body'=><<<'HTML'
<h2>Shipping options</h2>
{shipping_table}
<p>Shipping is a flat price per order, whatever its size or value. We ship {dispatch_time}, and you receive a tracking number by email as soon as your package is on its way.</p>
<h2>Every package is fully insured</h2>
<p>Your order is insured for its full value from the moment it leaves us until it is delivered and signed for. If a package is lost or arrives damaged, we deal with the carrier and the insurer — you don’t have to.</p>
<h2>Discreet packaging</h2>
<p>We pack every order in a plain, sturdy box with no mention of gold, bullion or our shop name on the outside.</p>
<h2>Signature on delivery</h2>
<p>An adult (18+) must sign for every package, so choose an address where someone will be home. If you miss the delivery, the carrier will leave a note so you can rearrange delivery or collect the package from a local office with photo ID.</p>
<h2>Where we ship</h2>
<p>We currently ship to street addresses in the United States. We can’t ship to PO Boxes, mail-forwarding services or freight forwarders. For security, we may ask you to confirm your delivery address before shipping, and for some orders the delivery address must match the billing details on your payment account.</p>
<h2 id="returns">Returns policy</h2>
<p>Gold prices change every minute, so once your price is locked and your payment is received, a bullion order can’t be cancelled or returned for a change of mind. This is standard across the precious metals industry and keeps prices fair for everyone.</p>
<p>If something is wrong with your order, we’ll put it right:</p>
<ul>
<li><strong>Damaged, incorrect or missing items:</strong> tell us within <strong>3 days</strong> of delivery at <a href="mailto:{email}">{email}</a> with your order number and photos. We’ll replace the item or refund you in full, including return shipping.</li>
<li><strong>Package arrives visibly damaged or tampered with:</strong> photograph it before opening, ideally refuse delivery, and contact us straight away.</li>
<li><strong>Returned items</strong> must be in the condition we sent them, with any assay cards still sealed.</li>
</ul>
<h2>Cancellations</h2>
<p>You can cancel free of charge at any time <strong>before</strong> you pay — just reply to your payment email. Orders not paid within {pay_window} hours are cancelled automatically.</p>
HTML,
  'faqs'=>[
    ['How much is shipping?', 'Standard shipping is {standard_price} ({standard_days}); express is {express_price} ({express_days}). Both are fully insured.'],
    ['Can I return gold bullion?', 'Not for a change of mind once paid, because the price is locked to the live market. Damaged, wrong or missing items reported within 3 days are replaced or refunded.'],
    ['Do I need to sign for my package?', 'Yes, an adult must sign for every insured bullion delivery.'],
  ],
],
[
  'slug'=>'authenticity-guarantee', 'title'=>'Authenticity Guarantee', 'group'=>'help',
  'h1'=>'Our Authenticity Guarantee',
  'seo_title'=>'Authenticity Guarantee – Genuine Gold Bullion, Every Order',
  'seo_desc'=>'Every gold bar and coin we sell is genuine bullion from established refiners and mints. How we source and check our products, and what our guarantee covers.',
  'body'=><<<'HTML'
<p class="lead">Every gold bar and coin we sell is genuine. Here’s how we make sure of it — and what we promise if you ever have a doubt.</p>
<h2>Where our gold comes from</h2>
<p>We sell products made by established refiners and sovereign mints — names whose bars and coins are recognised by dealers everywhere — and we buy them through established bullion wholesale channels. We do not buy unbranded or unverifiable gold to resell as bullion.</p>
<h2>How we check it</h2>
<ul>
<li>Every item is inspected before shipping: weight, dimensions, markings, serial numbers and packaging.</li>
<li>Sealed assay cards are checked for tampering and their serial numbers matched to the bar.</li>
<li>Items that don’t meet our standards are never shipped.</li>
</ul>
<h2>Our guarantee</h2>
<p>If a recognised assay office or established precious metals dealer independently tests an item you bought from us and finds it is not genuine, contact us with the written result. We will verify the claim, take the item back and refund the full price you paid, including reasonable testing costs.</p>
<p>The guarantee covers items in the condition we supplied them. Please keep sealed assay cards sealed — it protects you and keeps your bullion easy to resell.</p>
<h2>Check any gold yourself</h2>
<p>Our guide on <a href="/guides/how-to-tell-if-gold-is-real">how to tell if gold is real</a> explains the simple checks anyone can do at home, and the professional tests dealers use.</p>
HTML,
  'faqs'=>[],
],
[
  'slug'=>'track-order', 'title'=>'Track Your Order', 'group'=>'help',
  'h1'=>'Track Your Order',
  'seo_title'=>'Track Your Order',
  'seo_desc'=>'Check the status of your gold bullion order with your order number and email address.',
  'body'=><<<'HTML'
<p class="lead">Enter your order number (it starts with GB-) and the email address you used at checkout.</p>
HTML,
  'faqs'=>[],
],
[
  'slug'=>'privacy-policy', 'title'=>'Privacy Policy', 'group'=>'legal',
  'h1'=>'Privacy Policy',
  'seo_title'=>'Privacy Policy',
  'seo_desc'=>'How {brand} collects, uses and protects your personal information when you visit our website or buy gold bullion from us.',
  'body'=><<<'HTML'
<p><em>Last updated: {updated}</em></p>
<p>This policy explains what personal information {brand} (“we”, “us”) collects when you use {domain} or buy from us, how we use it and the choices you have. If you have questions, email <a href="mailto:{email}">{email}</a>.</p>
<h2>Information we collect</h2>
<ul>
<li><strong>Order information:</strong> your name, email address, phone number, delivery address, the products you order, your chosen payment method and any notes you add.</li>
<li><strong>Payment information:</strong> we do not collect card details. When you pay by Cash App, Apple Pay, Zelle, Chime or crypto, we receive the payment details those services show us, such as the sender name, account handle or wallet address, amount and date.</li>
<li><strong>Verification information:</strong> for larger orders or where the law requires, we may ask for a copy of photo ID or other information to confirm your identity.</li>
<li><strong>Messages:</strong> anything you send us by email, text or our contact form.</li>
<li><strong>Technical information:</strong> like most websites, our server records your IP address, browser type and the pages you visit. If we use analytics, it collects similar information through cookies (see our <a href="/cookie-policy">cookie policy</a>).</li>
</ul>
<h2>How we use it</h2>
<ul>
<li>To process, verify, ship and support your orders, including sending payment details and tracking information.</li>
<li>To prevent fraud and to protect you and us from payment and identity scams.</li>
<li>To meet our legal obligations, including anti-money-laundering, tax and record-keeping rules.</li>
<li>To answer your questions and improve our website.</li>
</ul>
<p>We do not sell or rent your personal information, and we don’t send marketing messages unless you ask us to.</p>
<h2>Who we share it with</h2>
<ul>
<li><strong>Shipping carriers and insurers</strong>, to deliver and insure your package.</li>
<li><strong>Service providers</strong> who help us run the business, such as our web host and email provider, under confidentiality obligations.</li>
<li><strong>Authorities</strong>, when the law requires it — for example IRS reporting of certain cash transactions, or a valid legal request.</li>
</ul>
<h2>How long we keep it</h2>
<p>We keep order and transaction records for at least five years, as required for precious metals dealers, and then delete them. Contact form messages are kept only as long as needed to deal with them.</p>
<h2>Security</h2>
<p>Our website uses HTTPS encryption. Order records are stored on a protected server that can’t be accessed from the web, and access is limited to the people who process orders.</p>
<h2>Your rights</h2>
<p>You can ask us to show you the personal information we hold about you, correct it, or delete it where we’re not legally required to keep it. California residents have rights under the CCPA/CPRA, including the right to know, delete and correct information and not to be discriminated against for using these rights; we do not sell or share personal information for cross-context advertising. To make a request, email <a href="mailto:{email}">{email}</a>.</p>
<h2>Children</h2>
<p>Our website is not intended for anyone under 18 and we do not knowingly collect their information.</p>
<h2>Changes</h2>
<p>We may update this policy and will change the date at the top when we do.</p>
<h2>Contact</h2>
<p>{business_details}</p>
HTML,
  'faqs'=>[],
],
[
  'slug'=>'terms-and-conditions', 'title'=>'Terms & Conditions', 'group'=>'legal',
  'h1'=>'Terms & Conditions',
  'seo_title'=>'Terms & Conditions of Sale',
  'seo_desc'=>'The terms that apply when you buy gold bullion from {brand}: pricing, price locks, payment, shipping, risk, returns and more.',
  'body'=><<<'HTML'
<p><em>Last updated: {updated}</em></p>
<p>These terms apply to every order placed on {domain}. By placing an order you agree to them. Please read them together with our <a href="/shipping-returns">shipping &amp; returns policy</a>, <a href="/privacy-policy">privacy policy</a> and <a href="/aml-policy">AML policy</a>.</p>
<h2>1. Who can buy</h2>
<p>You must be at least 18 years old and able to enter into a binding contract. We ship to street addresses in the United States only.</p>
<h2>2. Prices</h2>
<p>Precious metals prices change continuously. Our website prices follow the live spot price and can change at any time until you place your order. All prices are in US dollars. Sales tax is added where it applies, and you will be told before you pay.</p>
<h2>3. Placing an order and price lock</h2>
<p>When you submit an order, its price is locked and you receive an order number and a confirmation email. This confirms we have received your order; a contract is formed when we send you payment details. We may decline or cancel any order — for example if a price or product was shown in error, if we cannot verify your identity or address, or if we suspect fraud — and we will refund anything you have paid.</p>
<h2>4. Payment</h2>
<p>We accept the payment methods listed on our <a href="/payment-methods">payment methods</a> page. Payment must reach us within {pay_window} hours of your order and must come from an account in the name of the person who placed the order. Orders not paid in time are cancelled. If a price-locked order is cancelled after payment at your request and we agree to it, we may deduct any loss caused by a fall in the metal price since your order was placed.</p>
<h2>5. Verification</h2>
<p>To protect our customers and comply with the law, we may ask you to verify your identity, phone number, address or source of funds before shipping. We may delay an order until verification is complete.</p>
<h2>6. Shipping and risk</h2>
<p>We ship fully insured. Risk passes to you when the package is delivered and signed for at your address. Delivery times are estimates and are not guaranteed.</p>
<h2>7. Returns</h2>
<p>Bullion cannot be returned for a change of mind once paid. Damaged, incorrect or missing items must be reported within 3 days of delivery, as set out in our <a href="/shipping-returns#returns">returns policy</a>.</p>
<h2>8. Products</h2>
<p>Product photos are representative. Bars and coins may differ slightly from the photo, for example in year of issue, serial number, packaging design or minor marks from production and handling; weight and purity always match the description.</p>
<h2>9. No investment advice</h2>
<p>We sell precious metals; we do not give financial, investment, legal or tax advice. Precious metals prices can fall as well as rise. See our <a href="/disclaimer">disclaimer</a>.</p>
<h2>10. Liability</h2>
<p>To the extent the law allows, our total liability for any order is limited to the amount you paid for it, and we are not liable for indirect or consequential losses. Nothing in these terms limits rights you have under consumer protection law that cannot be excluded.</p>
<h2>11. Changes and governing law</h2>
<p>We may update these terms; the version in force when you placed your order applies to that order. These terms are governed by the laws of the US state in which our business is registered.</p>
<h2>12. Contact</h2>
<p>{business_details}</p>
HTML,
  'faqs'=>[],
],
[
  'slug'=>'aml-policy', 'title'=>'AML & KYC Policy', 'group'=>'legal',
  'h1'=>'Anti-Money Laundering (AML) & KYC Policy',
  'seo_title'=>'AML & KYC Policy – Anti-Money Laundering Compliance',
  'seo_desc'=>'How {brand} follows anti-money-laundering rules for precious metals dealers: identity checks, payment rules, reporting and record keeping.',
  'body'=><<<'HTML'
<p>Precious metals are a target for money laundering and fraud. Like every responsible bullion dealer, we follow the US rules that apply to dealers in precious metals under the Bank Secrecy Act, and we take extra steps to protect our customers from scams.</p>
<h2>Know your customer</h2>
<ul>
<li>We verify the name, phone number and address on every order, and may call you to confirm an order.</li>
<li>For larger orders, first orders above a certain value, or where anything doesn’t add up, we may ask for a copy of government-issued photo ID and, if needed, proof of address or source of funds.</li>
<li>We check customers against US government sanctions lists (OFAC).</li>
</ul>
<h2>Payment rules</h2>
<ul>
<li>Payment must come from an account in the name of the person who placed the order. We don’t accept third-party payments.</li>
<li>We don’t accept cash in person or by mail.</li>
<li>Where the law requires, we report transactions to the authorities — for example, IRS Form 8300 for cash or cash-equivalent payments over $10,000.</li>
</ul>
<h2>Suspicious activity</h2>
<p>We may decline, delay or cancel any order that appears suspicious, including orders that seem designed to avoid reporting thresholds, and we may report suspicious activity as the law requires. We will not tell you if a report has been made.</p>
<h2>Record keeping</h2>
<p>We keep transaction and verification records for at least five years. How we protect this information is explained in our <a href="/privacy-policy">privacy policy</a>.</p>
<h2>Protecting you from scams</h2>
<p>If someone you have met online, a “romance” contact, an “investment advisor” or a supposed government official asks you to buy gold and send it to them, it is almost certainly a scam. Please stop and talk to someone you trust — and contact us before paying.</p>
HTML,
  'faqs'=>[],
],
[
  'slug'=>'cookie-policy', 'title'=>'Cookie Policy', 'group'=>'legal',
  'h1'=>'Cookie Policy',
  'seo_title'=>'Cookie Policy',
  'seo_desc'=>'Which cookies {brand} uses, why we use them and how you can control them in your browser.',
  'body'=><<<'HTML'
<p><em>Last updated: {updated}</em></p>
<p>Cookies are small text files stored by your browser. We keep their use to a minimum.</p>
<h2>Cookies we use</h2>
<ul>
<li><strong>Essential session cookie:</strong> remembers your cart and keeps checkout secure. It is deleted when you close your browser. The website can’t work properly without it.</li>
<li><strong>Analytics cookies (if enabled):</strong> we may use Google Analytics to understand how visitors use our site, for example which pages are popular. It does not tell us who you are.</li>
</ul>
<p>We do not use advertising or tracking cookies to follow you around other websites.</p>
<h2>Managing cookies</h2>
<p>You can block or delete cookies in your browser settings. Blocking the essential cookie will stop the cart and checkout from working.</p>
HTML,
  'faqs'=>[],
],
[
  'slug'=>'disclaimer', 'title'=>'Disclaimer', 'group'=>'legal',
  'h1'=>'Disclaimer',
  'seo_title'=>'Disclaimer – No Investment Advice',
  'seo_desc'=>'Information on {domain} is general and is not financial, investment, tax or legal advice. Read our full disclaimer.',
  'body'=><<<'HTML'
<h2>Not investment advice</h2>
<p>The information on this website — including our guides, price pages and product descriptions — is general information for educational purposes. It is not financial, investment, tax or legal advice and does not take into account your personal circumstances. Before making investment decisions, consider speaking to a qualified, independent adviser.</p>
<h2>Prices and risk</h2>
<p>The value of gold and other precious metals can go down as well as up, sometimes sharply, and you may get back less than you paid. Past performance is not a guide to future returns. Buying physical bullion involves dealer premiums, so prices you can sell at are normally lower than prices you buy at.</p>
<h2>Spot prices and calculators</h2>
<p>Spot prices, melt values and calculators on this website are for information only. They come from third-party market data feeds, may be delayed, and are not an offer to buy or sell at those prices. Jewelry and scrap gold sell for less than their melt value.</p>
<h2>Accuracy</h2>
<p>We work hard to keep our information accurate and up to date but cannot guarantee it is complete or error-free. Tax rules mentioned on this site are general and may change; check with a tax professional.</p>
HTML,
  'faqs'=>[],
],
[
  'slug'=>'accessibility', 'title'=>'Accessibility', 'group'=>'legal',
  'h1'=>'Accessibility Statement',
  'seo_title'=>'Accessibility Statement',
  'seo_desc'=>'{brand} is committed to making our website usable by everyone.',
  'body'=><<<'HTML'
<p>We want everyone to be able to use our website and buy from us easily. We aim to meet the Web Content Accessibility Guidelines (WCAG) 2.1 level AA.</p>
<ul>
<li>Pages work with keyboard navigation and screen readers.</li>
<li>Text has strong colour contrast and can be enlarged up to 200% without breaking the layout.</li>
<li>Images have text descriptions and forms have clear labels.</li>
</ul>
<p>If you have difficulty using any part of our website, or would like to place an order another way, email <a href="mailto:{email}">{email}</a> and we will help.</p>
HTML,
  'faqs'=>[],
],
];
