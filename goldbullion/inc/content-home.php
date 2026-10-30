<?php
/* Home page text below the products (edited in Admin → Home page). */
if(!defined('GB_ROOT')){ http_response_code(404); exit; }

return [
  'body' => <<<'HTML'
<h2>What is gold bullion?</h2>
<p><strong>Gold bullion</strong> is gold in its most investable form: refined to at least 99.5% purity and cast or minted into bars, ingots or coins whose value comes from their weight and purity rather than their design. Investment gold bullion is almost always 24 karat — .999 or .9999 fine — and is stamped with its weight, purity and the refiner or mint that made it. Read the full explanation in <a href="/guides/what-is-gold-bullion">what is gold bullion?</a></p>

<h2>Gold bullion prices: spot price plus premium</h2>
<p>Every piece of gold bullion is priced from the <a href="/gold-price">gold spot price</a> — the live wholesale price of one troy ounce, currently <strong>{gold_oz}</strong> ({gold_g} per gram). On top of that you pay a small premium that covers refining, minting, assaying, packaging and insured delivery. Larger bars carry lower premiums; small bars and coins carry higher ones. Our prices move with the market during the day, and <strong>your price is locked the moment you place your order</strong>.</p>

<h2>Gold bullion bars by size</h2>
<table>
<thead><tr><th>Size</th><th>Pure gold</th><th>Gold value today</th></tr></thead>
<tbody>
<tr><td><a href="/1-gram-gold-bars">1 gram gold bar</a></td><td>1 g / 0.032 oz</td><td>{value:1g}</td></tr>
<tr><td><a href="/10-gram-gold-bars">10 gram gold bar</a></td><td>10 g / 0.322 oz</td><td>{value:10g}</td></tr>
<tr><td><a href="/1-oz-gold-bars">1 oz gold bar</a></td><td>31.1 g / 1 oz</td><td>{value:1oz}</td></tr>
<tr><td><a href="/100-gram-gold-bars">100 gram gold bar</a></td><td>100 g / 3.215 oz</td><td>{value:100g}</td></tr>
<tr><td><a href="/10-oz-gold-bars">10 oz gold bar</a></td><td>311 g / 10 oz</td><td>{value:10oz}</td></tr>
<tr><td><a href="/1-kilo-gold-bars">1 kilo gold bar</a></td><td>1,000 g / 32.15 oz</td><td>{value:1kg}</td></tr>
</tbody>
</table>
<p>Gold is weighed in troy ounces of 31.1035 grams, not the 28.35 gram ounce used for food. See our guide to <a href="/guides/gold-bar-weight-and-sizes">gold bar weights and sizes</a>.</p>

<h2>Gold bars or gold coins?</h2>
<p><a href="/gold-bars">Gold bars</a> give you the most gold for your money, especially in larger sizes. <a href="/gold-coins">Gold coins</a> such as the American Gold Eagle, Canadian Maple Leaf and Krugerrand are government-issued legal tender with a slightly higher premium and very easy resale. Most buyers start with whichever fits their budget and add the other over time. Compare them in <a href="/guides/gold-coins-vs-gold-bars">gold coins vs gold bars</a>.</p>

<h2>How to buy gold bullion from us</h2>
<ol>
<li><strong>Choose your gold.</strong> Add bars or coins to your cart. Prices follow the live spot price.</li>
<li><strong>Check out in under two minutes.</strong> Enter your delivery address and phone number, and pick standard or express insured shipping and how you want to pay — Cash App, Apple Pay, Zelle, Chime or crypto.</li>
<li><strong>Get your payment details.</strong> We text or email you the exact amount and where to send it, {reply_time}. Your price is locked when you order.</li>
<li><strong>Receive your bullion.</strong> Once payment clears we ship in plain, unbranded packaging, fully insured, with tracking and adult signature on delivery.</li>
</ol>
<p>More detail is on our <a href="/how-to-order">how to order</a> and <a href="/payment-methods">payment methods</a> pages.</p>

<h2>Why buy physical gold bullion?</h2>
<ul>
<li><strong>Nobody else’s promise.</strong> Physical gold isn’t a claim on a bank, fund or company — you own the metal outright.</li>
<li><strong>A long record as a store of value.</strong> Gold has been used as money for thousands of years and is still held in large amounts by central banks today.</li>
<li><strong>Diversification.</strong> Gold often behaves differently from stocks and bonds, which is why many investors hold a portion of their savings in it. Compare it with paper alternatives in <a href="/guides/physical-gold-vs-gold-etfs">physical gold vs gold ETFs</a>.</li>
<li><strong>Private and portable.</strong> A 1 kilo gold bar is the size of a smartphone.</li>
</ul>
<p>Gold prices go down as well as up, and past performance is no guide to the future. We sell gold; we don’t give investment advice. Please read our <a href="/disclaimer">disclaimer</a>.</p>

<h2>Buying gold bullion safely</h2>
<p>The gold market attracts fakes and fly-by-night sellers, so it pays to buy carefully wherever you shop. Buy recognised products from established refiners, keep assay cards sealed, and know the warning signs. Our guides on <a href="/guides/how-to-buy-gold-bullion">how to buy gold bullion safely</a> and <a href="/guides/how-to-tell-if-gold-is-real">how to tell if gold is real</a> walk you through it, and our <a href="/authenticity-guarantee">authenticity guarantee</a> explains what we promise on every order.</p>
HTML,
  'faqs' => [
    ['What is gold bullion?', 'Gold bullion is refined gold — usually 24 karat, .999 or .9999 fine — in the form of bars, ingots or coins, valued by its weight and purity.'],
    ['How much is gold bullion worth today?', 'Gold is trading at {gold_oz} per troy ounce, or {gold_g} per gram. A 1 oz gold bar contains {value:1oz} of gold and a 1 kilo bar {value:1kg}.'],
    ['How do I pay?', 'Choose Cash App, Apple Pay, Zelle, Chime or crypto at checkout. We then text or email you the payment details and exact amount for your order.'],
    ['How much is shipping?', 'Standard insured shipping is {standard_price} ({standard_days}) and express is {express_price} ({express_days}). Every order is fully insured and needs an adult signature.'],
    ['Is my price locked when I order?', 'Yes. Your price is locked when you place your order, as long as payment reaches us within {pay_window} hours.'],
    ['Do you ship discreetly?', 'Yes. Orders are packed in plain boxes with no mention of gold or our shop name on the outside.'],
  ],
];
