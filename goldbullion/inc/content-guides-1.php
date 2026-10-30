<?php
/* Guides, part 1: gold bullion basics, bar sizes and weights, coins. */
if(!defined('GB_ROOT')){ http_response_code(404); exit; }

return [
[
  'slug'=>'what-is-gold-bullion', 'topic'=>'basics', 'date'=>'2026-10-30',
  'title'=>'What Is Gold Bullion? Definition, Types and How It’s Priced',
  'seo_title'=>'What Is Gold Bullion? Definition, Types & How It’s Priced',
  'seo_desc'=>'Gold bullion definition: refined gold of at least 99.5% purity in bars, ingots or coins, valued by weight and purity. Types, purity, pricing and how to buy it.',
  'summary'=>'The plain-English definition of gold bullion, the forms it comes in, how pure it must be and how its price is set.',
  'body'=><<<'HTML'
<p class="lead"><strong>Gold bullion is refined gold — at least 99.5% pure — formed into bars, ingots or coins and valued by its weight and purity rather than its design.</strong> It is the form of gold that central banks hold in their vaults and that investors buy to own gold directly.</p>
<h2>Gold bullion definition</h2>
<p>The word “bullion” means precious metal in bulk form, valued as a commodity. To count as investment-grade gold bullion, gold must be:</p>
<ul>
<li><strong>Highly pure.</strong> The wholesale market’s minimum is .995 (99.5%). Almost all bullion sold to investors today is .999 or .9999 fine — 24 karat.</li>
<li><strong>Of a known, stamped weight</strong> — from 1 gram up to the 400 troy ounce bars traded between banks.</li>
<li><strong>Made by a recognised refiner or mint</strong>, whose mark guarantees its weight and purity.</li>
</ul>
<p>Jewelry, scrap gold and gold-plated items are not bullion: their purity is lower and their value includes design and labour.</p>
<h2>Types of gold bullion</h2>
<h3>Gold bars and ingots</h3>
<p><a href="/gold-bars">Gold bars</a> are the purest and cheapest way to buy gold per ounce. Retail sizes run from <a href="/1-gram-gold-bars">1 gram</a> to <a href="/1-kilo-gold-bars">1 kilo</a>. “Ingot” traditionally means a cast bar, but the words are used interchangeably.</p>
<h3>Gold bullion coins</h3>
<p><a href="/gold-coins">Bullion coins</a> such as the American Gold Eagle, Canadian Maple Leaf and South African Krugerrand are struck by government mints, carry a face value as legal tender, and trade close to their gold value. They cost a little more than bars but are very easy to sell.</p>
<h3>Gold rounds</h3>
<p>Rounds look like coins but are made by private mints and have no face value. They are less common in gold than in silver.</p>
<h2>How pure is gold bullion?</h2>
<table><thead><tr><th>Fineness</th><th>Percent gold</th><th>Typical use</th></tr></thead><tbody>
<tr><td>.9999</td><td>99.99%</td><td>Most modern bars, Maple Leaf, Buffalo, Philharmonic, Britannia</td></tr>
<tr><td>.999</td><td>99.9%</td><td>Some bars and older coins</td></tr>
<tr><td>.995</td><td>99.5%</td><td>Minimum for 400 oz London Good Delivery bars</td></tr>
<tr><td>.9167</td><td>91.67% (22K)</td><td>American Gold Eagle, Krugerrand, Sovereign — still contain their full stated weight of gold</td></tr>
</tbody></table>
<p>For more on karats see <a href="/guides/gold-karats-explained">gold karats explained</a>.</p>
<h2>How is gold bullion priced?</h2>
<p>Bullion is priced from the <a href="/gold-price">gold spot price</a> — the live wholesale price of one troy ounce of pure gold, currently {gold_oz}. You pay spot for the gold content plus a <strong>premium</strong> that covers refining, minting, assay packaging, distribution, insurance and the dealer’s margin.</p>
<ul>
<li>Large bars (1 kilo, 10 oz) have the lowest premiums.</li>
<li>1 oz bars and coins have moderate premiums.</li>
<li>Small bars (1 gram) and fractional coins have the highest premiums per gram.</li>
</ul>
<p>Read <a href="/guides/buy-gold-at-spot-price">can you buy gold at spot price?</a> for how premiums work.</p>
<h2>Gold bullion weights</h2>
<p>Bullion is weighed in <strong>troy ounces</strong> (31.1035 g), not ordinary ounces (28.35 g). A kilo is 32.15 troy ounces. See our <a href="/guides/gold-bar-weight-and-sizes">gold bar weight and size guide</a>.</p>
<h2>Why people buy gold bullion</h2>
<ul>
<li>To own a tangible asset outside the banking system.</li>
<li>To diversify savings away from stocks, bonds and cash.</li>
<li>As a long-term store of value and hedge against currency debasement.</li>
<li>To pass wealth on in a simple, private form.</li>
</ul>
<p>Gold can fall in price as well as rise and pays no interest. It is best seen as one part of a balanced plan, not a get-rich-quick trade.</p>
<h2>How to buy gold bullion</h2>
<p>Buy recognised products from an established dealer, compare the total price including shipping, and keep your bullion in its original packaging. Our step-by-step guide on <a href="/guides/how-to-buy-gold-bullion">how to buy gold bullion safely</a> covers everything, or <a href="/shop">browse our gold bullion</a>.</p>
HTML,
  'faqs'=>[
    ['What is the simple definition of gold bullion?', 'Gold bullion is high-purity gold — at least 99.5% — in the form of bars, ingots or coins, valued by its weight and purity.'],
    ['Is gold bullion 24 karat?', 'Most gold bullion is 24 karat (.999 or .9999 fine). Some bullion coins are 22 karat but contain their full stated weight of pure gold.'],
    ['What is the difference between gold and gold bullion?', 'All bullion is gold, but not all gold is bullion. Jewelry and scrap are lower purity and valued partly for design; bullion is pure gold valued only by its metal.'],
    ['Are gold coins bullion?', 'Coins such as the Gold Eagle, Maple Leaf and Krugerrand are bullion coins, priced close to their gold value. Rare or collectible coins are valued for their scarcity and are called numismatic coins.'],
  ],
],
[
  'slug'=>'how-to-buy-gold-bullion', 'topic'=>'basics', 'date'=>'2026-10-30',
  'title'=>'How to Buy Gold Bullion Safely: A First-Time Buyer’s Guide',
  'seo_title'=>'How to Buy Gold Bullion Safely – First-Time Buyer’s Guide',
  'seo_desc'=>'How to buy gold bullion step by step: choose bars or coins, understand spot price and premiums, pick a dealer, pay safely, and store your gold.',
  'summary'=>'Everything a first-time buyer needs: what to buy, what to pay, how to choose a dealer and how to avoid scams.',
  'body'=><<<'HTML'
<p class="lead">Buying gold bullion is simple once you know what to look for. Follow these seven steps to buy the right product at a fair price — and avoid the scams that target new buyers.</p>
<h2>1. Decide why you’re buying</h2>
<p>Long-term savings, a hedge, a gift? If you’re saving long-term, buy the most gold per dollar (larger bars). If you want flexibility or plan to sell in pieces, choose smaller bars or coins. If it’s a gift, a <a href="/1-gram-gold-bars">1 gram gold bar</a> in a sealed assay card is ideal.</p>
<h2>2. Choose bars or coins</h2>
<p><a href="/gold-bars">Gold bars</a> carry lower premiums. <a href="/gold-coins">Gold coins</a> are government-guaranteed and easy to sell. See <a href="/guides/gold-coins-vs-gold-bars">gold coins vs gold bars</a>.</p>
<h2>3. Choose a size</h2>
<table><thead><tr><th>Budget</th><th>Good starting choice</th></tr></thead><tbody>
<tr><td>Under {value:5g}</td><td><a href="/1-gram-gold-bars">1 g</a> or 2.5 g bars, <a href="/guides/mini-gold-coins">mini gold coins</a></td></tr>
<tr><td>Around {value:10g}</td><td><a href="/10-gram-gold-bars">10 g bar</a> or 1/4 oz coin</td></tr>
<tr><td>Around {value:1oz}</td><td><a href="/1-oz-gold-bars">1 oz bar</a> or 1 oz coin</td></tr>
<tr><td>{value:10oz}+</td><td><a href="/10-oz-gold-bars">10 oz bar</a> or <a href="/1-kilo-gold-bars">1 kilo bar</a></td></tr>
</tbody></table>
<h2>4. Understand the price</h2>
<p>Every price is the <a href="/gold-price">spot price</a> ({gold_oz}/oz today) times the gold content, plus a premium. Compare dealers on the <strong>total delivered price per ounce</strong>, including shipping and any payment fees — not on the headline price alone. Be very wary of anyone selling at or below spot: see <a href="/guides/buy-gold-at-spot-price">can you buy gold at spot price?</a></p>
<h2>5. Choose a dealer you can trust</h2>
<ul>
<li>Clear contact details, published policies and a real person who answers questions.</li>
<li>Recognised brands only: PAMP Suisse, Valcambi, Argor-Heraeus, Perth Mint, Royal Canadian Mint, US Mint and similar.</li>
<li>Insured shipping with signature on delivery.</li>
<li>A written authenticity guarantee — here’s <a href="/authenticity-guarantee">ours</a>.</li>
<li>No pressure, no “prices will double next month” promises.</li>
</ul>
<h2>6. Pay safely</h2>
<p>Only pay using details sent from the dealer’s own email address or phone number, include your order number in the note, and pay from an account in your own name. Never pay someone who contacted you out of the blue. See our <a href="/payment-methods">payment methods</a>.</p>
<h2>7. Store it properly</h2>
<p>Keep bullion in its original packaging, out of sight, in a safe or safe-deposit box, and tell a trusted person where it is. Read <a href="/guides/how-to-store-gold-bullion">how to store gold bullion</a>.</p>
<h2>Red flags</h2>
<ul>
<li>Prices well below spot, or “wholesale gold” offered through social media or messaging apps.</li>
<li>Someone you met online asking you to buy gold for them, or to hand gold to a courier.</li>
<li>Unbranded bars, bars without markings, or assay cards that have been opened and resealed.</li>
<li>Requests for gift cards or payment to a different name than the business.</li>
</ul>
<p>Learn the checks in <a href="/guides/how-to-tell-if-gold-is-real">how to tell if gold is real</a>.</p>
HTML,
  'faqs'=>[
    ['What is the safest way to buy gold bullion?', 'Buy recognised bars and coins from an established dealer with published policies, insured shipping and an authenticity guarantee, and pay only using details sent from the dealer’s own channels.'],
    ['What is the best gold to buy for a beginner?', 'A 1 oz bar or coin is the most widely recognised. On a smaller budget, 1 gram or 10 gram bars in sealed assay cards are a good start.'],
    ['Do I need ID to buy gold?', 'Not usually for small orders, but dealers may verify your identity for larger orders under anti-money-laundering rules.'],
  ],
],
[
  'slug'=>'gold-bar-weight-and-sizes', 'topic'=>'bars', 'date'=>'2026-10-30',
  'title'=>'Gold Bar Weight and Sizes: How Much Does a Gold Bar Weigh?',
  'seo_title'=>'Gold Bar Weight: How Much Does a Gold Bar Weigh? (All Sizes)',
  'seo_desc'=>'How much does a gold bar weigh? A standard 400 oz gold bar weighs 27.4 lb (12.4 kg). Weights, sizes and values of every bar from 1 gram to 400 oz.',
  'summary'=>'Weights, dimensions and values of every gold bar size — from 1 gram to the 27 lb Good Delivery bar — in grams, troy ounces and pounds.',
  'body'=><<<'HTML'
<p class="lead"><strong>A standard gold bar — the 400 troy ounce “Good Delivery” bar held by central banks — weighs about 27.4 pounds (12.4 kg).</strong> But the gold bars most people buy are far smaller: from 1 gram (lighter than a paperclip) to 1 kilo (2.2 lb).</p>
<h2>Gold bar weights chart</h2>
<table><thead><tr><th>Bar</th><th>Grams</th><th>Troy oz</th><th>Pounds</th><th>Gold value today</th></tr></thead><tbody>
<tr><td><a href="/1-gram-gold-bars">1 gram</a></td><td>1</td><td>0.032</td><td>0.002</td><td>{value:1g}</td></tr>
<tr><td>5 gram</td><td>5</td><td>0.161</td><td>0.011</td><td>{value:5g}</td></tr>
<tr><td><a href="/10-gram-gold-bars">10 gram</a></td><td>10</td><td>0.322</td><td>0.022</td><td>{value:10g}</td></tr>
<tr><td><a href="/1-oz-gold-bars">1 oz</a></td><td>31.1</td><td>1</td><td>0.069</td><td>{value:1oz}</td></tr>
<tr><td>50 gram</td><td>50</td><td>1.608</td><td>0.110</td><td>{value:50g}</td></tr>
<tr><td><a href="/100-gram-gold-bars">100 gram</a></td><td>100</td><td>3.215</td><td>0.220</td><td>{value:100g}</td></tr>
<tr><td>10 tola</td><td>116.6</td><td>3.75</td><td>0.257</td><td>{value:10tola}</td></tr>
<tr><td><a href="/5-oz-gold-bars">5 oz</a></td><td>155.5</td><td>5</td><td>0.343</td><td>{value:5oz}</td></tr>
<tr><td>250 gram</td><td>250</td><td>8.038</td><td>0.551</td><td>{value:250g}</td></tr>
<tr><td><a href="/10-oz-gold-bars">10 oz</a></td><td>311</td><td>10</td><td>0.686</td><td>{value:10oz}</td></tr>
<tr><td>500 gram</td><td>500</td><td>16.075</td><td>1.102</td><td>{value:500g}</td></tr>
<tr><td><a href="/1-kilo-gold-bars">1 kilo</a></td><td>1,000</td><td>32.151</td><td>2.205</td><td>{value:1kg}</td></tr>
<tr><td><a href="/100-oz-gold-bars">100 oz</a></td><td>3,110</td><td>100</td><td>6.857</td><td>{value:100oz}</td></tr>
<tr><td><a href="/guides/400-oz-gold-bar">400 oz Good Delivery</a></td><td>12,441</td><td>400</td><td>27.43</td><td>{value:400oz}</td></tr>
</tbody></table>
<h2>How many ounces are in a gold bar?</h2>
<p>It depends on the bar. The most popular investment bar contains <strong>1 troy ounce</strong>; a kilo bar contains 32.15 oz; and the large bars in central bank vaults contain roughly <strong>400 oz</strong> (anywhere from 350 to 430 oz, because each is weighed and stamped individually).</p>
<h2>Troy ounces vs regular ounces</h2>
<p>Gold is always weighed in troy ounces of 31.1035 grams. A regular (avoirdupois) ounce is 28.35 grams. So a “1 oz” gold bar weighs about 1.1 regular ounces on a kitchen scale. Twelve troy ounces make a troy pound; 14.58 troy ounces make a regular pound — see <a href="/gold-price-per-pound">how much a pound of gold is worth</a>.</p>
<h2>How much does a gold brick weigh?</h2>
<p>When people say “gold brick” they usually mean the large bars from films and bank vaults. A 400 oz Good Delivery bar is about 10 inches long, 3 inches wide and 1.5 inches tall and weighs around <strong>27 pounds</strong> — heavy enough that most people need two hands to lift it, despite being smaller than a house brick. A real house brick made of solid gold would weigh around 85 lb.</p>
<h2>Gold biscuit and gold ingot weight</h2>
<p>“Gold biscuit” is a common term in South Asia for a small flat bar, often 10 tola (116.64 g) or 100 g; in the US it can mean a 1 oz bar. “Ingot” is another word for a cast bar of any size. Read <a href="/guides/gold-biscuit">what is a gold biscuit?</a></p>
<h2>Why is gold so heavy?</h2>
<p>Gold has a density of 19.32 g/cm³ — about 1.7 times as dense as lead and 2.5 times as dense as steel. That’s why a kilo bar is only the size of a smartphone, and why the only common metal with similar density, tungsten (19.25 g/cm³), is used to make fake bars. See <a href="/guides/hollow-and-tungsten-filled-gold-bars">hollow and tungsten-filled gold bars</a>.</p>
<h2>Gold bar dimensions</h2>
<p>Exact sizes differ between refiners, and minted bars are thinner and wider than cast bars of the same weight. Typical sizes:</p>
<ul>
<li>1 g: about 15 × 9 × 0.4 mm</li>
<li>1 oz minted: about 41 × 24 × 1.8 mm</li>
<li>10 oz cast: about 62 × 28 × 9 mm</li>
<li>1 kg cast: about 115 × 52 × 15 mm</li>
<li>400 oz Good Delivery: about 255 × 75 × 40 mm on top, tapering towards the base</li>
</ul>
HTML,
  'faqs'=>[
    ['How much does a standard gold bar weigh?', 'A standard 400 troy ounce Good Delivery bar weighs about 12.4 kg or 27.4 lb.'],
    ['How much does a 1 oz gold bar weigh?', '31.1 grams, or about 1.1 ordinary ounces.'],
    ['How many ounces are in a gold bar?', 'Retail bars range from 0.032 oz (1 gram) to 32.15 oz (1 kilo). The large bars held by central banks contain about 400 troy ounces.'],
    ['How much is a gold bar worth?', 'A 1 oz gold bar contains {value:1oz} of gold today; a 1 kilo bar {value:1kg}; a 400 oz bar {value:400oz}. See how much a gold bar is worth.'],
  ],
],
[
  'slug'=>'how-much-is-a-gold-bar-worth', 'topic'=>'bars', 'date'=>'2026-10-30',
  'title'=>'How Much Is a Gold Bar Worth Today? Every Size, Live Prices',
  'seo_title'=>'How Much Is a Gold Bar Worth Today? (1 oz to 400 oz Brick)',
  'seo_desc'=>'How much is a gold bar worth? A 1 oz bar is worth {value:1oz} and a 400 oz gold brick {value:400oz} at today’s spot price. Live values for every size.',
  'summary'=>'Live values of every size of gold bar and ingot — including the 400 oz gold brick — and how to work out what any bar is worth.',
  'body'=><<<'HTML'
<p class="lead">At today’s spot price of {gold_oz} per ounce, <strong>a 1 oz gold bar is worth about {value:1oz}, a 1 kilo bar about {value:1kg}, and a full 400 oz gold brick about {value:400oz}</strong>. Here’s what every size is worth and how to calculate it yourself.</p>
<h2>Gold bar values today</h2>
<table><thead><tr><th>Bar</th><th>Gold content</th><th>Value at spot</th></tr></thead><tbody>
<tr><td>1 gram</td><td>0.032 oz</td><td>{value:1g}</td></tr>
<tr><td>10 gram</td><td>0.322 oz</td><td>{value:10g}</td></tr>
<tr><td>1 oz</td><td>1 oz</td><td>{value:1oz}</td></tr>
<tr><td>100 gram</td><td>3.215 oz</td><td>{value:100g}</td></tr>
<tr><td>10 oz</td><td>10 oz</td><td>{value:10oz}</td></tr>
<tr><td>1 kilo</td><td>32.15 oz</td><td>{value:1kg}</td></tr>
<tr><td>100 oz</td><td>100 oz</td><td>{value:100oz}</td></tr>
<tr><td>400 oz (gold brick)</td><td>~400 oz</td><td>{value:400oz}</td></tr>
</tbody></table>
<h2>How to work out a gold bar’s value</h2>
<ol>
<li>Find the weight stamped on the bar and convert it to troy ounces (grams ÷ 31.1035).</li>
<li>Multiply by the purity — .9999 bars count as pure.</li>
<li>Multiply by the live <a href="/gold-price">gold spot price</a>.</li>
</ol>
<p>Example: a 250 g bar = 8.038 oz × {gold_oz} = {value:250g}.</p>
<h2>How much is one brick of gold worth?</h2>
<p>The classic gold brick is the 400 oz Good Delivery bar. Each one is weighed individually and contains between 350 and 430 troy ounces of gold at a purity of at least 99.5%, so its exact value varies. At about 400 oz, a brick is worth roughly <strong>{value:400oz}</strong> today. Read all about the <a href="/guides/400-oz-gold-bar">400 oz gold bar</a>.</p>
<h2>How much is a gold ingot worth?</h2>
<p>“Ingot” simply means a cast bar. A 10 oz ingot is worth {value:10oz}; a kilo ingot {value:1kg}. Value depends only on weight and purity.</p>
<h2>What you pay vs what you get back</h2>
<p>The figures above are the gold’s value at spot. When you <strong>buy</strong>, you pay spot plus a premium; when you <strong>sell</strong>, a dealer pays close to spot for recognised, undamaged bars. The difference — the spread — is lowest on large bars from well-known refiners, which is why bars like <a href="/guides/pamp-suisse-gold-bars">PAMP Suisse</a> and Valcambi are so popular.</p>
<h2>What makes one bar worth more than another of the same weight?</h2>
<ul>
<li><strong>Brand recognition</strong> — well-known refiners sell back more easily.</li>
<li><strong>Condition and packaging</strong> — a sealed assay card is worth more than a loose, scratched bar.</li>
<li><strong>Minted vs cast</strong> — minted bars carry a slightly higher premium. See <a href="/guides/minted-vs-cast-gold-bars">minted vs cast</a>.</li>
</ul>
HTML,
  'faqs'=>[
    ['How much is a gold bar worth today?', 'It depends on its weight: a 1 oz bar is worth {value:1oz}, a 1 kilo bar {value:1kg} and a 400 oz bar {value:400oz} at today’s spot price.'],
    ['How much is one brick of gold worth?', 'A standard 400 oz gold brick is worth about {value:400oz} at today’s price.'],
    ['How much is 1 oz of gold worth?', '{gold_oz} — the spot price is quoted per troy ounce.'],
  ],
],
[
  'slug'=>'400-oz-gold-bar', 'topic'=>'bars', 'date'=>'2026-10-30',
  'title'=>'The 400 oz Gold Bar: Good Delivery Bars Explained',
  'seo_title'=>'400 oz Gold Bar: Weight, Size & Price of a Good Delivery Bar',
  'seo_desc'=>'The 400 troy ounce gold bar is the London Good Delivery standard held by central banks. Its weight (27.4 lb), size, purity rules and price today: {value:400oz}.',
  'summary'=>'Everything about the 400 troy ounce Good Delivery gold bar: weight, dimensions, rules, value and whether you can buy one.',
  'body'=><<<'HTML'
<p class="lead"><strong>The 400 oz gold bar is the standard bar of the global wholesale gold market.</strong> Known as the London Good Delivery bar, it is the bar you see in central bank vaults and films — weighing about 27.4 lb and worth around {value:400oz} at today’s price.</p>
<h2>What is a Good Delivery gold bar?</h2>
<p>The London Bullion Market Association (LBMA) sets the “Good Delivery” standard for gold traded in the London market, the world’s largest. Only bars made by refiners on the LBMA Good Delivery List, and meeting its specifications, can be traded and stored in that market. Central banks, the IMF and gold-backed ETFs hold their gold almost entirely in these bars.</p>
<h2>400 oz gold bar specifications</h2>
<table><tbody>
<tr><th>Gold content</th><td>350 to 430 troy ounces (about 10.9–13.4 kg)</td></tr>
<tr><th>Minimum purity</th><td>995.0 parts per thousand (99.5%)</td></tr>
<tr><th>Typical weight</th><td>About 400 oz = 12.44 kg = 27.4 lb</td></tr>
<tr><th>Approximate size</th><td>Top about 255 × 80 mm, height about 37 mm, with sloping sides</td></tr>
<tr><th>Markings</th><td>Refiner’s mark, serial number, fineness and year of manufacture</td></tr>
</tbody></table>
<p>Because every bar weighs slightly differently, each is weighed and its exact fine gold content recorded. Trades are settled on that exact weight.</p>
<h2>How much is a 400 oz gold bar worth?</h2>
<p>At today’s spot price of {gold_oz}, a 400 troy ounce bar holds <strong>{value:400oz}</strong> of gold. A 430 oz bar would be worth {value:430oz}, and a 350 oz bar {value:350oz}.</p>
<h2>Can you buy a 400 oz gold bar?</h2>
<p>Technically yes, but rarely in practice. 400 oz bars trade between banks and are normally kept inside the LBMA vault system, because a bar that leaves it must be re-assayed before it can re-enter. For private investors, <a href="/1-kilo-gold-bars">1 kilo bars</a>, <a href="/100-oz-gold-bars">100 oz bars</a> and <a href="/10-oz-gold-bars">10 oz bars</a> offer nearly the same low premium per ounce with far easier storage and resale.</p>
<h2>Why are gold bars shaped like that?</h2>
<p>Large bars are cast by pouring molten gold into open moulds. The sloping sides make it easier to release the bar from the mould, and the trapezoid shape lets bars stack securely in vaults.</p>
<h2>How heavy is a gold bar compared with a house brick?</h2>
<p>A house brick weighs about 2–2.5 kg. A Good Delivery bar is smaller, but weighs five times as much — 12.4 kg — because gold is so dense. See our <a href="/guides/gold-bar-weight-and-sizes">gold bar weight chart</a>.</p>
HTML,
  'faqs'=>[
    ['How much does a 400 oz gold bar weigh?', 'About 12.44 kg or 27.4 lb. Each bar contains between 350 and 430 troy ounces of gold.'],
    ['How much is a 400 troy ounce gold bar worth?', '{value:400oz} at today’s spot price of {gold_oz} per ounce.'],
    ['What is a good delivery gold bar?', 'A large gold bar of 350–430 troy ounces and at least 99.5% purity, made by an LBMA-accredited refiner, that meets the London Bullion Market Association’s standards for wholesale trading.'],
  ],
],
[
  'slug'=>'gold-biscuit', 'topic'=>'bars', 'date'=>'2026-10-30',
  'title'=>'What Is a Gold Biscuit? Weight, Sizes and Price',
  'seo_title'=>'Gold Biscuit Weight & Price – What Is a Gold Biscuit?',
  'seo_desc'=>'A gold biscuit is a small flat gold bar. Common gold biscuit weights are 10 tola (116.64 g), 100 g and 1 oz. Sizes, weights and today’s gold biscuit prices.',
  'summary'=>'What people mean by a gold biscuit, the common sizes and weights, and what each is worth today.',
  'body'=><<<'HTML'
<p class="lead"><strong>A gold biscuit is simply a small, flat gold bar.</strong> The term is widely used in India, Pakistan, Bangladesh, Nepal and the Gulf, where the classic gold biscuit is a <strong>10 tola bar weighing 116.64 grams</strong>. In the US, people sometimes call a 1 oz gold bar a biscuit.</p>
<h2>Gold biscuit weights</h2>
<table><thead><tr><th>Gold biscuit</th><th>Weight</th><th>Troy oz</th><th>Gold value today</th></tr></thead><tbody>
<tr><td>1 oz biscuit</td><td>31.1 g</td><td>1.000</td><td>{value:1oz}</td></tr>
<tr><td>50 g biscuit</td><td>50 g</td><td>1.608</td><td>{value:50g}</td></tr>
<tr><td>100 g biscuit</td><td>100 g</td><td>3.215</td><td>{value:100g}</td></tr>
<tr><td>10 tola biscuit (TT bar)</td><td>116.64 g</td><td>3.750</td><td>{value:10tola}</td></tr>
<tr><td>1 kg biscuit</td><td>1,000 g</td><td>32.151</td><td>{value:1kg}</td></tr>
</tbody></table>
<h2>The 10 tola gold biscuit</h2>
<p>The 10 tola bar, also called a TT bar (“ten tola”), contains 3.75 troy ounces of 24K gold — {value:10tola} today. It became the standard savings bar across South Asia and the Gulf and is still made by Swiss and Gulf refiners. See the <a href="/tola-gold-price">gold tola price</a>.</p>
<h2>1 ounce gold biscuit</h2>
<p>A 1 oz gold biscuit is a <a href="/1-oz-gold-bars">1 oz gold bar</a>: 31.1 g of 24K gold, worth {gold_oz} at spot. It’s the most traded gold bar in the world.</p>
<h2>Is a gold biscuit pure gold?</h2>
<p>Bars sold as investment gold biscuits are 24K, usually .999 or .9999 fine, and stamped with weight, purity and refiner. Always check the markings and buy recognised brands.</p>
HTML,
  'faqs'=>[
    ['How much does a gold biscuit weigh?', 'The most common gold biscuit is a 10 tola bar of 116.64 grams. Others weigh 1 oz (31.1 g), 100 g or 1 kg.'],
    ['How much is a gold biscuit worth?', 'A 10 tola gold biscuit holds {value:10tola} of gold at today’s price; a 100 g biscuit {value:100g}.'],
  ],
],
[
  'slug'=>'gold-coins-vs-gold-bars', 'topic'=>'coins', 'date'=>'2026-10-30',
  'title'=>'Gold Coins vs Gold Bars: Which Is the Better Investment?',
  'seo_title'=>'Gold Coins vs Gold Bars: Investment Comparison (2026)',
  'seo_desc'=>'Gold coin investment vs bullion bars comparison: premiums, resale, security, divisibility and tax. Find out whether gold coins or gold bars suit you better.',
  'summary'=>'A side-by-side comparison of gold coins and gold bars on cost, resale, security and flexibility.',
  'body'=><<<'HTML'
<p class="lead"><strong>Gold bars give you more gold for your money; gold coins give you easier resale, government backing and more flexibility.</strong> Both contain real, investment-grade gold, and many investors own both.</p>
<h2>Gold coins vs bars at a glance</h2>
<table><thead><tr><th></th><th>Gold bars</th><th>Gold coins</th></tr></thead><tbody>
<tr><th>Premium over spot</th><td>Lower, especially 10 oz and 1 kg</td><td>Higher</td></tr>
<tr><th>Who makes them</th><td>Private refiners and some mints</td><td>Government mints</td></tr>
<tr><th>Legal tender</th><td>No</td><td>Yes, with a face value</td></tr>
<tr><th>Resale</th><td>Easy for well-known brands</td><td>Very easy everywhere</td></tr>
<tr><th>Counterfeit protection</th><td>Assay cards, serial numbers, surface scans</td><td>Detailed designs and modern security features</td></tr>
<tr><th>Sizes</th><td>1 g to 400 oz</td><td>1/20 oz to 1 oz (some larger)</td></tr>
<tr><th>Best for</th><td>Maximising gold per dollar</td><td>Flexibility, gifting, recognisability</td></tr>
</tbody></table>
<h2>Cost: bars win</h2>
<p>A bar’s minting cost is spread over more gold, so premiums fall as size increases. A <a href="/1-kilo-gold-bars">1 kilo bar</a> carries one of the lowest premiums of any retail product. Coins require detailed dies and sovereign guarantees, so even 1 oz coins usually cost a few percent more than a 1 oz bar.</p>
<h2>Resale: coins have the edge</h2>
<p>Every dealer in the world knows a Gold Eagle, Maple Leaf or Krugerrand. Bars from leading refiners such as <a href="/guides/pamp-suisse-gold-bars">PAMP Suisse</a> are also accepted everywhere; bars from obscure makers can take longer to sell or need testing.</p>
<h2>Flexibility</h2>
<p>If you may need to sell gold a little at a time, ten 1 oz coins or bars are far more flexible than one 10 oz bar. Fractional coins and small bars give even more flexibility at a higher premium.</p>
<h2>Tax</h2>
<p>In the US, gold bars and bullion coins held for more than a year are generally taxed as collectibles at up to 28% on gains. Some dealers must report certain sales to the IRS, depending on product and quantity. Rules change, so ask a tax professional.</p>
<h2>Which should you buy?</h2>
<ul>
<li><strong>Buy bars</strong> if you want the most gold for your money and plan to hold long-term.</li>
<li><strong>Buy coins</strong> if you value recognisability, plan to sell in small pieces, or want gifts.</li>
<li><strong>Buy both</strong> to balance cost and flexibility — for example a <a href="/10-oz-gold-bars">10 oz bar</a> plus several <a href="/gold-coins">1 oz coins</a>.</li>
</ul>
HTML,
  'faqs'=>[
    ['Is it better to buy gold coins or bars?', 'Bars cost less per ounce; coins are easier to sell and more flexible. For pure investment value bars usually win; for liquidity and gifting coins are better.'],
    ['Do gold coins hold value better than bars?', 'Both track the gold price. Bullion coins can resell at slightly higher premiums, which partly offsets their higher purchase cost.'],
  ],
],
[
  'slug'=>'is-a-1-gram-gold-bar-worth-it', 'topic'=>'bars', 'date'=>'2026-10-30',
  'title'=>'Is a 1 Gram Gold Bar Worth It? Value, Premiums and Who Should Buy',
  'seo_title'=>'1 Gram Gold Bar Value: Is a 1g Gold Bar a Good Investment?',
  'seo_desc'=>'A 1 gram gold bar holds {value:1g} of gold today. Is it worth buying? Premiums, resale value, size and the pros and cons of 1g gold bars, honestly explained.',
  'summary'=>'What a 1 gram gold bar is worth, why it costs more than its gold value, and when it makes sense to buy one.',
  'body'=><<<'HTML'
<p class="lead"><strong>A 1 gram gold bar contains {value:1g} of 24K gold at today’s price.</strong> It sells for more than that because of the cost of making and packaging such a small bar. Whether it’s “worth it” depends on why you’re buying.</p>
<h2>1 gram gold bar value</h2>
<p>The value of the gold in a 1g bar is the spot price per gram: {gold_g}. A dealer selling it adds a premium; when you sell it back, you’ll typically receive a little below the gold value, and more for a sealed bar from a famous refiner. See the live <a href="/gold-price-per-gram">gold price per gram</a>.</p>
<h2>Why 1 gram bars have higher premiums</h2>
<p>Producing a 1 gram bar costs almost as much as producing a 10 gram bar — the same stamping, assay card, certificate and handling — but it contains a tenth of the gold. So the fixed cost is a much bigger share of the price.</p>
<h2>When a 1 gram gold bar makes sense</h2>
<ul>
<li><strong>Gifts</strong> — a real gold gift that keeps its value, for birthdays, graduations and weddings.</li>
<li><strong>Starting small</strong> — owning physical gold for the first time.</li>
<li><strong>Divisibility</strong> — small units you can sell or trade one by one.</li>
<li><strong>Regular saving</strong> — buying a little every month.</li>
</ul>
<h2>When it doesn’t</h2>
<p>If your goal is the most gold for your money, buy larger: a <a href="/10-gram-gold-bars">10 gram bar</a> or <a href="/1-oz-gold-bars">1 oz bar</a> cuts the premium per gram substantially.</p>
<h2>Is a 1 gram gold bar real gold?</h2>
<p>Yes, if it comes from a recognised refiner: solid 24K .9999 gold. Beware of novelty “gold bars” and gold-foil cards sold online, which contain a tiny fraction of a gram. Check the weight and fineness printed on the card. See <a href="/guides/how-to-tell-if-gold-is-real">how to tell if gold is real</a>.</p>
HTML,
  'faqs'=>[
    ['How much is a 1 gram gold bar worth?', 'The gold in it is worth {value:1g} at today’s spot price.'],
    ['Can I sell a 1 gram gold bar?', 'Yes. Dealers buy recognised 1 gram bars, paying the most for bars still sealed in their assay card.'],
  ],
],
[
  'slug'=>'mini-gold-coins', 'topic'=>'coins', 'date'=>'2026-10-30',
  'title'=>'Mini Gold Coins: The Smallest Gold Coins You Can Buy',
  'seo_title'=>'Mini Gold Coins & Miniature Gold Coins – Sizes and Values',
  'seo_desc'=>'Mini gold coins from 1 gram to 1/10 oz: real gold bullion coins from national mints. Sizes, gold content, values and how they compare with small gold bars.',
  'summary'=>'The smallest real gold coins — 1 gram, 1/20 oz and 1/10 oz — what they contain and whether they’re a good buy.',
  'body'=><<<'HTML'
<p class="lead"><strong>Mini gold coins are genuine gold bullion coins weighing between half a gram and 1/10 of an ounce.</strong> They let you own government-minted gold for a small outlay, and they make memorable gifts.</p>
<h2>Popular mini and miniature gold coins</h2>
<table><thead><tr><th>Size</th><th>Gold content</th><th>Gold value today</th><th>Examples</th></tr></thead><tbody>
<tr><td>0.5 gram</td><td>0.016 oz</td><td>{value:0.5g}</td><td>Canadian 0.5 g Maple Leaf</td></tr>
<tr><td>1 gram</td><td>0.032 oz</td><td>{value:1g}</td><td>1 g gold coins from various mints; Maplegram</td></tr>
<tr><td>1/20 oz</td><td>1.56 g</td><td>{value:0.05oz}</td><td>Maple Leaf, Panda (1 g), Kangaroo</td></tr>
<tr><td>1/10 oz</td><td>3.11 g</td><td>{value:0.1oz}</td><td>Gold Eagle, Maple Leaf, Krugerrand, Britannia, Philharmonic</td></tr>
</tbody></table>
<h2>Are mini gold coins a good investment?</h2>
<p>They’re real gold, recognised by dealers, and easy to sell — but like <a href="/1-gram-gold-bars">1 gram gold bars</a>, they carry higher premiums than full-ounce coins because the minting cost is spread over very little gold. They are best for gifting, starting small, or adding divisible units to a larger holding.</p>
<h2>Beware of “gold” novelty coins</h2>
<p>Souvenir coins described as “gold clad”, “gold layered” or “24K gold plated” contain only a microscopic layer of gold and are worth very little. Genuine bullion coins state their fineness (.999, .9999 or .9167) and weight, and come from national mints.</p>
<p>Browse <a href="/gold-coins">gold coins</a> or see <a href="/gold-coin-prices">gold coin prices today</a>.</p>
HTML,
  'faqs'=>[
    ['What is the smallest gold coin?', 'Some mints issue 0.5 gram gold coins. The most common small bullion coins are 1/20 oz and 1/10 oz.'],
    ['How much is a 1/10 oz gold coin worth?', 'It contains {value:0.1oz} of gold at today’s spot price.'],
  ],
],
];
