<?php
/* Category pages. "match" lets the CSV import file products into the right category by metal,
   type and weight. Tokens like {value:1oz} show the live spot value when the page is viewed. */
if(!defined('GB_ROOT')){ http_response_code(404); exit; }

return [
[
  'slug'=>'gold-bars', 'name'=>'Gold Bars', 'parent'=>'', 'nav'=>true, 'match'=>['metal'=>'gold', 'type'=>'bar'],
  'h1'=>'24K Gold Bars for Sale',
  'seo_title'=>'Gold Bars for Sale – 24K Gold Bullion Bars, 1 Gram to 1 Kilo',
  'seo_desc'=>'Buy 24K gold bars online: .9999 fine gold bullion bars from 1 gram to 1 kilo, priced from the live spot price. Sealed assay cards, insured shipping.',
  'intro'=>'Every gold bar we sell is 24 karat, investment-grade bullion — at least .999 fine and usually .9999 — made by an established refinery and stamped with its weight, purity and maker’s mark. Choose a size below or read on for help picking one.',
  'body'=><<<'HTML'
<h2>Which size of gold bar should you buy?</h2>
<p>The right size depends on your budget and how you plan to sell later. As a rule, <strong>the bigger the bar, the lower the premium you pay per ounce</strong> — but smaller bars are easier to sell a little at a time.</p>
<table>
<thead><tr><th>Bar size</th><th>Fine gold</th><th>Gold value at today’s spot</th><th>Best for</th></tr></thead>
<tbody>
<tr><td><a href="/1-gram-gold-bars">1 gram</a></td><td>0.032 oz</td><td>{value:1g}</td><td>Gifts, first purchases, small budgets</td></tr>
<tr><td><a href="/10-gram-gold-bars">10 gram</a></td><td>0.322 oz</td><td>{value:10g}</td><td>Regular saving, flexible resale</td></tr>
<tr><td><a href="/1-oz-gold-bars">1 oz</a></td><td>1.000 oz</td><td>{value:1oz}</td><td>The most traded size worldwide</td></tr>
<tr><td><a href="/100-gram-gold-bars">100 gram</a></td><td>3.215 oz</td><td>{value:100g}</td><td>Lower premiums in a compact bar</td></tr>
<tr><td><a href="/5-oz-gold-bars">5 oz</a></td><td>5.000 oz</td><td>{value:5oz}</td><td>A middle step between 1 oz and 10 oz</td></tr>
<tr><td><a href="/10-oz-gold-bars">10 oz</a></td><td>10.000 oz</td><td>{value:10oz}</td><td>Serious investors who want low cost per ounce</td></tr>
<tr><td><a href="/1-kilo-gold-bars">1 kilo</a></td><td>32.151 oz</td><td>{value:1kg}</td><td>The lowest premium of any retail bar</td></tr>
</tbody></table>
<p>Not sure how heavy each bar is in your hand? Our <a href="/guides/gold-bar-weight-and-sizes">gold bar weight and size guide</a> compares every size, including the 400 oz bars held by central banks.</p>
<h2>What makes a gold bar “investment grade”?</h2>
<ul>
<li><strong>Purity of .999 or higher.</strong> Almost all modern bars are .9999 (“four nines”) fine — 24 karat gold.</li>
<li><strong>A recognised maker.</strong> Bars from refiners such as PAMP Suisse, Valcambi, Argor-Heraeus, the Perth Mint and the Royal Canadian Mint are accepted by dealers everywhere, which keeps resale simple.</li>
<li><strong>Clear markings.</strong> Weight, purity, refiner and — on most bars — a unique serial number.</li>
<li><strong>Protective packaging.</strong> Small minted bars usually come sealed in a tamper-evident assay card with a certificate. Keep it sealed: it helps you sell at the best price.</li>
</ul>
<h2>Minted or cast?</h2>
<p><strong>Minted bars</strong> are struck from a blank, so they have sharp edges, a polished finish and often a design on the back. <strong>Cast bars</strong> are poured into a mould, giving the rugged, classic “gold brick” look and a slightly lower premium. Both contain exactly the stated weight of gold. Read more in <a href="/guides/minted-vs-cast-gold-bars">minted vs cast gold bars</a>.</p>
<h2>How our gold bar prices work</h2>
<p>Each bar’s price is the live <a href="/gold-price">gold spot price</a> (today {gold_oz} per troy ounce) multiplied by its gold content, plus a premium that covers refining, minting, packaging and shipping. Your price is locked the moment you place your order. See <a href="/guides/buy-gold-at-spot-price">why nobody sells gold bars at exactly spot</a>.</p>
HTML,
  'faqs'=>[
    ['Are your gold bars 24 karat?', 'Yes. Every gold bar we sell is 24 karat investment bullion, at least .999 fine and in most cases .9999 fine.'],
    ['Which gold bar size has the lowest premium?', 'Larger bars carry a lower premium per ounce. A 1 kilo or 10 oz bar costs much less per ounce over spot than a 1 gram or 1 oz bar.'],
    ['Do gold bars come with a certificate?', 'Most minted bars come sealed in an assay card that shows the weight, purity, serial number and refiner’s signature. Many cast bars are sold with a separate certificate or without one; the serial number stamped on the bar identifies it.'],
    ['How is my order shipped?', 'In plain, unbranded packaging, fully insured, with an adult signature required on delivery. Standard and express options are available at checkout.'],
  ],
],
[
  'slug'=>'1-gram-gold-bars', 'name'=>'1 Gram Gold Bars', 'parent'=>'gold-bars', 'nav'=>true, 'match'=>['metal'=>'gold', 'type'=>'bar', 'weight'=>1, 'unit'=>'g'],
  'h1'=>'1 Gram Gold Bars',
  'seo_title'=>'1 Gram Gold Bars for Sale – 24K 1g Gold Bullion Bars',
  'seo_desc'=>'Buy 1 gram gold bars: 24K .9999 fine gold bullion sealed in assay cards. The most affordable way to own real gold. Live pricing, insured US shipping.',
  'intro'=>'A 1 gram gold bar is the smallest standard piece of investment gold you can buy: real 24K bullion, sealed in a tamper-evident assay card, for the price of a nice dinner out.',
  'body'=><<<'HTML'
<h2>How much is a 1 gram gold bar worth?</h2>
<p>At today’s spot price, the gold inside a 1g gold bar is worth <strong>{value:1g}</strong>. Because a gram is so small, the cost of minting, assaying and packaging makes up a bigger share of the price, so a 1 gram bar sells for more than its melt value. For live figures by weight and karat, see <a href="/gold-price-per-gram">how much a gram of gold is worth today</a>.</p>
<h2>How big is a 1 gram gold bar?</h2>
<p>Tiny — roughly the size of a fingernail, about 15 × 9 mm and well under a millimetre thick. That is why 1g bars almost always come sealed in a credit-card-sized assay card: the card protects the bar, shows its serial number and makes it easy to store and resell.</p>
<h2>Why buy 1g gold bars?</h2>
<ul>
<li><strong>Low entry price.</strong> Start owning physical gold without committing hundreds or thousands of dollars.</li>
<li><strong>Divisibility.</strong> Ten 1 gram bars can be sold one at a time; a 10 gram bar cannot be split.</li>
<li><strong>Gifts.</strong> Birthdays, graduations, weddings and christenings — a 1 gram bar is a gift that keeps its value.</li>
<li><strong>Genuine bullion.</strong> Same 24K .9999 gold as a kilo bar, from the same refiners.</li>
</ul>
<h2>The trade-off: premium</h2>
<p>You pay more per gram for a 1 gram bar than for a <a href="/10-gram-gold-bars">10 gram bar</a> or a <a href="/1-oz-gold-bars">1 oz bar</a>. If your goal is the most gold for your money, step up a size when your budget allows. If your goal is flexibility or gifting, 1 gram bars are hard to beat.</p>
<h2>Other small sizes</h2>
<table><thead><tr><th>Weight</th><th>Gold value today</th></tr></thead><tbody>
<tr><td>1 gram</td><td>{value:1g}</td></tr><tr><td>2.5 gram</td><td>{value:2.5g}</td></tr><tr><td>5 gram</td><td>{value:5g}</td></tr><tr><td>10 gram</td><td>{value:10g}</td></tr><tr><td>20 gram</td><td>{value:20g}</td></tr>
</tbody></table>
HTML,
  'faqs'=>[
    ['Is a 1 gram gold bar real gold?', 'Yes. Our 1 gram gold bars are solid 24 karat gold, .9999 fine, made by established refiners and sealed in an assay card with a serial number.'],
    ['Is a 1 gram gold bar a good investment?', 'It is a genuine, easy-to-sell way to own gold, but its premium over the gold value is higher than larger bars. It suits small budgets, gifts and building up a position one gram at a time.'],
    ['Should I open the assay card?', 'No. Keeping the card sealed protects the bar and proves it has not been tampered with, which makes it easier to sell at a good price later.'],
  ],
],
[
  'slug'=>'10-gram-gold-bars', 'name'=>'10 Gram Gold Bars', 'parent'=>'gold-bars', 'nav'=>true, 'match'=>['metal'=>'gold', 'type'=>'bar', 'weight'=>10, 'unit'=>'g'],
  'h1'=>'10 Gram Gold Bars',
  'seo_title'=>'10 Gram Gold Bars for Sale – 24K 10g Gold Bullion',
  'seo_desc'=>'Buy 10 gram gold bars in sealed assay cards. 24K .9999 fine gold from top refiners, lower premiums than 1g bars. Live spot pricing and insured shipping.',
  'intro'=>'The 10 gram gold bar is the sweet spot between affordability and value: a third of a troy ounce of 24K gold, with a noticeably lower premium per gram than a 1 gram bar.',
  'body'=><<<'HTML'
<h2>10 gram gold bar value today</h2>
<p>A 10 gram bar holds 0.3215 troy ounces of fine gold, worth <strong>{value:10g}</strong> at the current spot price of {gold_oz} per ounce. Prices on this page update with the market and are locked when you order.</p>
<h2>Size and weight</h2>
<p>A 10g gold bar is about the size of a postage stamp — roughly 28 × 16 mm and around 1.3 mm thick, depending on the refiner. Most are minted bars sealed in an assay card with a matching serial number.</p>
<h2>Why choose 10g?</h2>
<ul>
<li>Lower cost per gram than <a href="/1-gram-gold-bars">1 gram bars</a>, while still at a manageable price.</li>
<li>Popular around the world, especially in Europe and Asia, so easy to resell.</li>
<li>Three 10g bars weigh almost the same as one <a href="/1-oz-gold-bars">1 oz bar</a> (31.1 g), giving you more flexibility when you sell.</li>
</ul>
<h2>10 gram gold price per karat</h2>
<p>Jewellery is priced on its gold content. For comparison, 10 grams of gold is worth: 24K {value:10g:24k} · 22K {value:10g:22k} · 18K {value:10g:18k} · 14K {value:10g:14k}. See the full <a href="/gold-price-per-gram">gold price per gram</a> table.</p>
HTML,
  'faqs'=>[
    ['How much is a 10 gram gold bar worth?', 'Its gold content is worth {value:10g} at today’s spot price of {gold_oz} per troy ounce. Retail prices include a small premium for minting and packaging.'],
    ['How many 10 gram bars make an ounce?', 'A troy ounce is 31.1035 grams, so about 3.1 bars of 10 grams equal one troy ounce.'],
  ],
],
[
  'slug'=>'100-gram-gold-bars', 'name'=>'100 Gram Gold Bars', 'parent'=>'gold-bars', 'nav'=>true, 'match'=>['metal'=>'gold', 'type'=>'bar', 'weight'=>100, 'unit'=>'g'],
  'h1'=>'100 Gram Gold Bars',
  'seo_title'=>'100 Gram Gold Bars for Sale – 100g 24K Gold Bullion Bars',
  'seo_desc'=>'Buy 100 gram gold bullion bars: 3.215 oz of 24K .9999 gold at a lower premium per ounce. Minted and cast 100g bars, live pricing, insured shipping.',
  'intro'=>'A 100 gram gold bar packs 3.215 troy ounces of 24K gold into a bar smaller than a credit card — a favourite with investors who want low premiums without stepping up to a kilo.',
  'body'=><<<'HTML'
<h2>100g gold bar price today</h2>
<p>The gold in a 100 gram bar is worth <strong>{value:100g}</strong> at today’s spot price. Premiums are much lower than on 1 oz bars, so more of your money goes into gold.</p>
<h2>Size</h2>
<p>Minted 100g bars measure about 55 × 31 mm and roughly 3 mm thick; cast versions are shorter and chunkier. Either way it fits easily in a safe or a safe-deposit box.</p>
<h2>100 gram vs 1 oz vs 10 oz</h2>
<table><thead><tr><th>Bar</th><th>Troy ounces</th><th>Gold value today</th></tr></thead><tbody>
<tr><td><a href="/1-oz-gold-bars">1 oz</a></td><td>1.000</td><td>{value:1oz}</td></tr>
<tr><td>100 gram</td><td>3.215</td><td>{value:100g}</td></tr>
<tr><td><a href="/10-oz-gold-bars">10 oz</a></td><td>10.000</td><td>{value:10oz}</td></tr>
</tbody></table>
HTML,
  'faqs'=>[
    ['How many ounces are in a 100 gram gold bar?', '3.215 troy ounces. Precious metals are always weighed in troy ounces of 31.1035 grams.'],
    ['How much is a 100 gram gold bar worth?', 'Its gold is worth {value:100g} at today’s spot price.'],
  ],
],
[
  'slug'=>'1-oz-gold-bars', 'name'=>'1 oz Gold Bars', 'parent'=>'gold-bars', 'nav'=>true, 'match'=>['metal'=>'gold', 'type'=>'bar', 'weight'=>1, 'unit'=>'oz'],
  'h1'=>'1 oz Gold Bars',
  'seo_title'=>'1 oz Gold Bars for Sale – 1 Ounce 24K Gold Bullion Bars',
  'seo_desc'=>'Buy 1 oz gold bars: one troy ounce of 24K .9999 fine gold from PAMP, Valcambi and other top refiners. Live spot pricing, sealed assay cards, insured shipping.',
  'intro'=>'The 1 ounce gold bar is the world’s most popular gold bar — one troy ounce of 24K gold, recognised and easy to sell anywhere, priced directly off the global gold spot price.',
  'body'=><<<'HTML'
<h2>1 oz gold bar price today</h2>
<p>The gold spot price is quoted per troy ounce, so the value of a 1 oz gold bar is simply the spot price: <strong>{gold_oz}</strong> right now. The price you pay adds a modest premium for refining, minting and packaging. Watch the live <a href="/gold-price">gold price today</a>.</p>
<h2>A troy ounce is not an ordinary ounce</h2>
<p>Gold is weighed in <strong>troy ounces</strong> of 31.1035 grams — about 10% heavier than the 28.35 gram ounce on your kitchen scale. A 1 oz gold bar therefore contains 31.1 grams of fine gold.</p>
<h2>How big is a one ounce gold bar?</h2>
<p>Smaller than most people expect: a minted 1 oz bar is about 41 × 24 mm (1.6 × 0.95 inches) and under 2 mm thick — smaller than a business card. Cast 1 oz bars are shorter and thicker. Gold is so dense that an ounce takes up very little space.</p>
<h2>1 oz gold bar or 1 oz gold coin?</h2>
<p>Both hold one ounce of gold. Bars usually cost less over spot; <a href="/gold-coins">gold coins</a> are government-issued legal tender and can be easier to sell in some places. Compare them in <a href="/guides/gold-coins-vs-gold-bars">gold coins vs gold bars</a>.</p>
<h2>Popular 1 oz gold bars</h2>
<ul>
<li><strong>PAMP Suisse Lady Fortuna</strong> — the iconic Swiss bar with Veriscan®-registered surface.</li>
<li><strong>Valcambi</strong> — Swiss minted bars in CertiCard packaging.</li>
<li><strong>Perth Mint, Royal Canadian Mint, Argor-Heraeus</strong> — trusted government and Swiss refiners.</li>
</ul>
<p>See our pick of the <a href="/guides/best-gold-bars-to-buy">best gold bars to buy</a>.</p>
HTML,
  'faqs'=>[
    ['How much is a 1 oz gold bar worth today?', 'Its gold content is worth the spot price: {gold_oz} at the moment. Dealers sell slightly above spot and buy back slightly below it.'],
    ['How many grams are in a 1 oz gold bar?', '31.1035 grams. Gold uses the troy ounce, which is heavier than the standard 28.35 gram ounce.'],
    ['Are 1 oz gold bars a good investment?', 'They are the most widely traded gold bar in the world, combining a reasonable premium with easy resale, which makes them a popular core holding.'],
  ],
],
[
  'slug'=>'5-oz-gold-bars', 'name'=>'5 oz Gold Bars', 'parent'=>'gold-bars', 'nav'=>false, 'match'=>['metal'=>'gold', 'type'=>'bar', 'weight'=>5, 'unit'=>'oz'],
  'h1'=>'5 oz Gold Bars',
  'seo_title'=>'5 oz Gold Bullion Bars for Sale – 24K .9999 Gold',
  'seo_desc'=>'Buy 5 oz gold bullion bars: 155.5 grams of 24K .9999 fine gold at a lower premium than 1 oz bars. Live pricing and fully insured US shipping.',
  'intro'=>'A 5 oz gold bullion bar holds 155.5 grams of 24K gold — a less common size that bridges the gap between 1 oz bars and 10 oz bars.',
  'body'=><<<'HTML'
<h2>5 oz gold bar value</h2>
<p>At today’s spot price the gold in a 5 oz bar is worth <strong>{value:5oz}</strong>. You pay a lower premium per ounce than on five separate 1 oz bars, while keeping the bar at a size that is still straightforward to resell.</p>
<h2>Weight and size</h2>
<p>5 troy ounces = 155.5 grams, or about 0.34 lb. Minted 5 oz bars are about the size of a credit card cut in half; cast bars are thicker.</p>
<p>Compare with <a href="/1-oz-gold-bars">1 oz gold bars</a> and <a href="/10-oz-gold-bars">10 oz gold bars</a>.</p>
HTML,
  'faqs'=>[['How many grams is a 5 oz gold bar?', '155.52 grams of fine gold (5 × 31.1035 g).']],
],
[
  'slug'=>'10-oz-gold-bars', 'name'=>'10 oz Gold Bars', 'parent'=>'gold-bars', 'nav'=>true, 'match'=>['metal'=>'gold', 'type'=>'bar', 'weight'=>10, 'unit'=>'oz'],
  'h1'=>'10 oz Gold Bars',
  'seo_title'=>'10 oz Gold Bars for Sale – 10 Ounce Gold Ingots, 24K',
  'seo_desc'=>'Buy 10 oz gold bars and ingots: 311 grams of 24K .9999 gold at one of the lowest premiums per ounce. Cast and minted bars, live pricing, insured shipping.',
  'intro'=>'The 10 ounce gold bar is the choice of serious investors: 311 grams of 24K gold in a single ingot, at one of the lowest premiums per ounce you can buy.',
  'body'=><<<'HTML'
<h2>10 oz gold bar price today</h2>
<p>A 10 oz gold ingot contains <strong>{value:10oz}</strong> worth of gold at today’s spot price of {gold_oz} per ounce. Because the cost of making the bar is spread over ten ounces, the premium per ounce is far lower than on 1 oz bars.</p>
<h2>How big is a 10 oz gold bar?</h2>
<p>A cast 10 oz bar is roughly 2.4 × 1.1 inches (about 62 × 28 mm) and around 9 mm thick — about the size of a domino, but it weighs 311 grams (0.69 lb). Read more in our <a href="/guides/gold-bar-weight-and-sizes">gold bar weight guide</a>.</p>
<h2>Cast 10 oz ingots vs minted bars</h2>
<p>Most 10 oz bars are cast: poured into a mould, then stamped with weight, purity, refiner and serial number. Cast bars cost a little less than minted bars of the same weight. Both are equally pure — see <a href="/guides/minted-vs-cast-gold-bars">minted vs cast gold bars</a>.</p>
<h2>Is a 10 oz gold bar right for you?</h2>
<ul>
<li><strong>Yes</strong> if you want the most gold for your money and plan to hold long-term.</li>
<li><strong>Consider smaller bars</strong> if you may want to sell part of your holding — a 10 oz bar has to be sold whole. Many investors mix 10 oz bars with <a href="/1-oz-gold-bars">1 oz bars</a>.</li>
</ul>
HTML,
  'faqs'=>[
    ['How much is a 10 oz gold bar worth?', 'The gold in it is worth {value:10oz} at today’s spot price. Retail prices add a small premium.'],
    ['How much does a 10 oz gold bar weigh?', '311.03 grams, or about 0.69 lb (11 ordinary ounces).'],
    ['What is the difference between a gold bar and a gold ingot?', 'Nothing important — “ingot” traditionally means a cast bar, but the words are used interchangeably.'],
  ],
],
[
  'slug'=>'100-oz-gold-bars', 'name'=>'100 oz Gold Bars', 'parent'=>'gold-bars', 'nav'=>false, 'match'=>['metal'=>'gold', 'type'=>'bar', 'weight'=>100, 'unit'=>'oz'],
  'h1'=>'100 oz Gold Bars',
  'seo_title'=>'100 oz Gold Bars – 100 Ounce Gold Bullion Bar Price',
  'seo_desc'=>'100 oz gold bars hold 3.11 kg of gold. See what a 100 ounce gold bar is worth today, how big it is and how to buy one.',
  'intro'=>'The 100 ounce gold bar weighs 3.11 kilograms (6.86 lb) and is the same size used for COMEX gold futures delivery. Contact us for availability and a locked price.',
  'body'=><<<'HTML'
<h2>What is a 100 oz gold bar worth?</h2>
<p>At today’s spot price of {gold_oz}, the gold in a 100 oz bar is worth <strong>{value:100oz}</strong>. That makes it one of the lowest-premium ways to buy gold — and a bar that you must sell as a single unit.</p>
<h2>Size and weight</h2>
<p>100 troy ounces = 3,110 grams = 6.86 lb. A typical cast 100 oz bar is around 5.5 × 2.3 × 1.1 inches (140 × 58 × 27 mm). COMEX gold futures contracts are for 100 ounces of gold, delivered as one 100 oz bar or three kilo bars.</p>
<p>For larger sizes see our guide to the <a href="/guides/400-oz-gold-bar">400 oz Good Delivery gold bar</a>. For a more flexible holding, consider <a href="/1-kilo-gold-bars">1 kilo gold bars</a> or <a href="/10-oz-gold-bars">10 oz bars</a>.</p>
HTML,
  'faqs'=>[['How much is 100 oz of gold worth?', '{value:100oz} at today’s spot price of {gold_oz} per troy ounce.']],
],
[
  'slug'=>'1-kilo-gold-bars', 'name'=>'1 Kilo Gold Bars', 'parent'=>'gold-bars', 'nav'=>true, 'match'=>['metal'=>'gold', 'type'=>'bar', 'weight'=>1, 'unit'=>'kg'],
  'h1'=>'1 Kilo Gold Bars',
  'seo_title'=>'1 Kilo Gold Bar Price – Buy 1kg 24K Gold Bullion Bars',
  'seo_desc'=>'1kg gold bars: 32.15 troy ounces of 24K .9999 gold at the lowest premium of any retail bar. See today’s 1 kilo gold bar price and buy with insured shipping.',
  'intro'=>'A 1 kilo gold bar holds 32.15 troy ounces of 24K gold. It is the standard bar for banks, vaults and serious investors worldwide — and carries the lowest premium per ounce of any retail gold bar.',
  'body'=><<<'HTML'
<h2>1 kilo gold bar price today</h2>
<p>The gold in a 1kg bar is worth <strong>{value:1kg}</strong> at today’s spot price of {gold_oz} per ounce ({gold_g} per gram). For a live breakdown see <a href="/gold-price-per-kilo">how much a kilo of gold is worth</a>.</p>
<h2>How big is a kilo of gold?</h2>
<p>Surprisingly small: a cast kilo bar is about 4.5 × 2 × 0.6 inches (roughly 115 × 52 × 15 mm) — about the size of a smartphone, but it weighs 2.2 lb. Gold is nearly twice as dense as lead, so a kilo takes up just 52 cubic centimetres.</p>
<h2>Why buy a kilo bar?</h2>
<ul>
<li><strong>Lowest premium:</strong> the cost of producing one bar is spread over 32 ounces.</li>
<li><strong>Global standard:</strong> kilo bars are traded on exchanges and held by banks across Asia, Europe and the Middle East.</li>
<li><strong>Compact storage:</strong> a large sum of money in a very small space.</li>
</ul>
<h2>Things to know</h2>
<p>A kilo bar is a large single purchase and must be sold as one unit. In the US, dealers who buy back gold bars totalling one kilo or more in a single transaction may be required to report the sale to the IRS on Form 1099-B; ask a tax professional how this applies to you.</p>
HTML,
  'faqs'=>[
    ['How much is a 1 kilo gold bar worth?', '{value:1kg} at today’s gold spot price of {gold_oz} per troy ounce.'],
    ['How many ounces are in a kilo of gold?', '32.1507 troy ounces, or 35.27 ordinary (avoirdupois) ounces.'],
    ['How much does a kilo gold bar weigh in pounds?', '2.2046 lb — exactly one kilogram.'],
  ],
],
[
  'slug'=>'gold-coins', 'name'=>'Gold Coins', 'parent'=>'', 'nav'=>true, 'match'=>['metal'=>'gold', 'type'=>'coin'],
  'h1'=>'Gold Coins for Sale',
  'seo_title'=>'Gold Coins for Sale – Buy Gold Bullion Coins Online',
  'seo_desc'=>'Buy gold bullion coins: American Gold Eagles, Buffalos, Maple Leafs, Krugerrands and mini gold coins. Live gold coin prices today and insured US shipping.',
  'intro'=>'Gold bullion coins are minted by national governments, carry a face value as legal tender and are recognised by dealers everywhere. Choose from full-ounce coins to tiny fractional and mini gold coins.',
  'body'=><<<'HTML'
<h2>Gold coin prices today</h2>
<p>Bullion coins are priced from the live gold spot price ({gold_oz} per ounce). A 1 oz gold coin contains {value:1oz} of gold; a ½ oz coin {value:0.5oz}; a ¼ oz coin {value:0.25oz}; and a 1/10 oz coin {value:0.1oz}. Coins carry a slightly higher premium than bars because of their detailed minting and sovereign guarantee.</p>
<h2>The world’s most popular gold coins</h2>
<table><thead><tr><th>Coin</th><th>Issuer</th><th>Purity</th><th>Gold content</th></tr></thead><tbody>
<tr><td>American Gold Eagle</td><td>US Mint</td><td>22K (.9167)</td><td>1 troy oz (gross weight 33.93 g)</td></tr>
<tr><td>American Gold Buffalo</td><td>US Mint</td><td>24K (.9999)</td><td>1 troy oz</td></tr>
<tr><td>Canadian Gold Maple Leaf</td><td>Royal Canadian Mint</td><td>24K (.9999)</td><td>1 troy oz</td></tr>
<tr><td>South African Krugerrand</td><td>South African Mint</td><td>22K (.9167)</td><td>1 troy oz (gross 33.93 g)</td></tr>
<tr><td>Austrian Philharmonic</td><td>Austrian Mint</td><td>24K (.9999)</td><td>1 troy oz</td></tr>
<tr><td>British Gold Britannia</td><td>The Royal Mint</td><td>24K (.9999)</td><td>1 troy oz</td></tr>
</tbody></table>
<p>22 karat coins like the Gold Eagle and Krugerrand still contain a full ounce of pure gold — the small amount of copper alloy just makes them harder-wearing.</p>
<h2>Mini and fractional gold coins</h2>
<p>Mini gold coins — 1/10 oz, 1/20 oz and 1 gram coins — let you own sovereign gold coins on a small budget and make memorable gifts. Like <a href="/1-gram-gold-bars">1 gram gold bars</a>, they carry a higher premium per gram than full-ounce coins.</p>
<h2>Coins or bars?</h2>
<p>Bars give you more gold per dollar; coins offer government backing, beautiful designs and very easy resale. Many buyers own both. Read <a href="/guides/gold-coins-vs-gold-bars">gold coins vs gold bars</a> to decide.</p>
HTML,
  'faqs'=>[
    ['How much is a 1 ounce gold coin worth?', 'A 1 oz gold bullion coin contains {value:1oz} of gold at today’s spot price. Coins sell for a premium above that and some rare dates are worth more to collectors.'],
    ['Are American Gold Eagles pure gold?', 'They are 22 karat (91.67% gold) but contain exactly one troy ounce of pure gold; the rest is copper and silver for durability.'],
    ['What are mini gold coins?', 'Small bullion coins, usually 1/10 oz, 1/20 oz or 1 gram, issued by national mints. They are real gold and an affordable way to start.'],
  ],
],
[
  'slug'=>'silver-bullion', 'name'=>'Silver Bullion', 'parent'=>'', 'nav'=>true, 'match'=>['metal'=>'silver'],
  'h1'=>'Silver Bullion Bars & Coins',
  'seo_title'=>'Silver Bullion for Sale – Silver Bars & Coins at Live Prices',
  'seo_desc'=>'Buy silver bullion bars and coins, priced from the live silver spot price. .999 fine silver from trusted mints with insured US shipping.',
  'intro'=>'Investment-grade .999 fine silver bars and coins, priced from the live silver spot price.',
  'body'=><<<'HTML'
<h2>Silver price today</h2>
<p>Silver is trading at <strong>{silver_oz}</strong> per troy ounce. See live <a href="/gold-price">gold and silver prices</a>.</p>
HTML,
  'faqs'=>[],
],
];
