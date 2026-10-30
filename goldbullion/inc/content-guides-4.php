<?php
/* Guides, part 4: weights, units, karats and jewelry gold. */
if(!defined('GB_ROOT')){ http_response_code(404); exit; }

return [
[
  'slug'=>'troy-ounce-vs-ounce', 'topic'=>'basics', 'date'=>'2026-10-31',
  'title'=>'Troy Ounce vs Ounce: How Many Grams Are in an Ounce of Gold?',
  'seo_title'=>'Troy Ounce vs Ounce: How Many Grams in an Ounce of Gold?',
  'seo_desc'=>'An ounce of gold is a troy ounce: 31.1035 grams, about 10% heavier than a regular 28.35 g ounce. Troy ounce vs ounce explained, with conversion tables.',
  'summary'=>'Why gold is weighed in troy ounces, how they differ from regular ounces, and how to convert grams, ounces, kilos and pounds.',
  'body'=><<<'HTML'
<p class="lead"><strong>There are 31.1035 grams in a troy ounce of gold.</strong> Gold, silver and other precious metals are always weighed in troy ounces, which are about 9.7% heavier than the regular (avoirdupois) ounce of 28.35 grams used for food and postage. One troy ounce of gold is worth {gold_oz} today.</p>
<h2>Troy ounce vs regular ounce</h2>
<table><thead><tr><th></th><th>Troy ounce (ozt)</th><th>Regular ounce (oz)</th></tr></thead><tbody>
<tr><th>Grams</th><td>31.1035 g</td><td>28.3495 g</td></tr>
<tr><th>Used for</th><td>Gold, silver, platinum, palladium</td><td>Food, packages, everyday goods</td></tr>
<tr><th>Ounces per pound</th><td>12 (troy pound = 373.24 g)</td><td>16 (pound = 453.59 g)</td></tr>
<tr><th>Gold value today</th><td>{value:1oz}</td><td>{value:28.3495g}</td></tr>
</tbody></table>
<p>So a troy ounce is heavier than a regular ounce, but a troy <em>pound</em> is lighter than a regular pound — because it contains only 12 ounces instead of 16. That’s the answer to the old riddle: a pound of feathers weighs more than a pound of gold.</p>
<h2>Where does the troy ounce come from?</h2>
<p>The name is usually traced to Troyes, a market town in medieval France whose fairs set standard weights for traders. England adopted troy weights for precious metals in the Middle Ages, and the troy ounce remains the global standard for gold pricing today. Every <a href="/gold-price">gold spot price</a> you see is per troy ounce.</p>
<h2>Gold weight conversion table</h2>
<table><thead><tr><th>From</th><th>Troy ounces</th><th>Grams</th><th>Gold value today</th></tr></thead><tbody>
<tr><td>1 gram</td><td>0.03215</td><td>1</td><td>{value:1g}</td></tr>
<tr><td>1 pennyweight (dwt)</td><td>0.05</td><td>1.5552</td><td>{value:1dwt}</td></tr>
<tr><td>1 tola</td><td>0.375</td><td>11.6638</td><td>{value:1tola}</td></tr>
<tr><td>1 regular ounce</td><td>0.9115</td><td>28.3495</td><td>{value:28.3495g}</td></tr>
<tr><td>1 troy ounce</td><td>1</td><td>31.1035</td><td>{value:1oz}</td></tr>
<tr><td>1 troy pound</td><td>12</td><td>373.24</td><td>{value:12oz}</td></tr>
<tr><td>1 regular pound</td><td>14.5833</td><td>453.59</td><td>{value:1lb}</td></tr>
<tr><td>1 kilogram</td><td>32.1507</td><td>1,000</td><td>{value:1kg}</td></tr>
</tbody></table>
<h2>Why using the wrong ounce costs you money</h2>
<p>If you weigh gold on a kitchen scale set to ounces and multiply by the spot price, you’ll under-value it by almost 9%: the scale reports regular ounces, which are lighter. When selling jewelry, weigh in grams and use the <a href="/gold-price-per-gram">gold price per gram</a>, or set your scale to “ozt”. See our guide to the <a href="/guides/best-scale-for-gold">best scale for gold</a>.</p>
<h2>How to convert</h2>
<ul>
<li><strong>Grams to troy ounces:</strong> divide by 31.1035.</li>
<li><strong>Troy ounces to grams:</strong> multiply by 31.1035.</li>
<li><strong>Regular ounces to troy ounces:</strong> multiply by 0.9115.</li>
<li><strong>Kilos to troy ounces:</strong> multiply by 32.1507.</li>
</ul>
{calculator}
HTML,
  'faqs'=>[
    ['How many grams are in an ounce of gold?', '31.1035 grams. Gold is weighed in troy ounces, not regular 28.35 gram ounces.'],
    ['Is a troy ounce heavier than an ounce?', 'Yes. A troy ounce is 31.1 g and a regular ounce is 28.35 g, so a troy ounce is about 9.7% heavier.'],
    ['How many troy ounces are in a pound?', '14.5833 troy ounces are in a regular pound; a troy pound contains 12 troy ounces.'],
  ],
],
[
  'slug'=>'gold-weight-units', 'topic'=>'basics', 'date'=>'2026-10-31',
  'title'=>'Gold Weight Units Explained: Pennyweight, Grain, Tola, Tael and More',
  'seo_title'=>'Gold Weight Units: Pennyweight, Grain, Tola & Tael Explained',
  'seo_desc'=>'Gold weight units explained: pennyweight (dwt), grain, gram, troy ounce, tola, tael, baht and kilo — with conversions and today’s value for each.',
  'summary'=>'Every unit used to weigh gold around the world — dwt, grain, tola, tael, baht — and how they convert.',
  'body'=><<<'HTML'
<p class="lead"><strong>Gold is weighed in troy ounces worldwide, but jewelers, refiners and traditional markets use other units too: grams, pennyweights (dwt), grains, tolas, taels and baht.</strong> Here’s what each one is and what it’s worth in gold today.</p>
<h2>Gold weight units at a glance</h2>
<table><thead><tr><th>Unit</th><th>Grams</th><th>Troy ounces</th><th>Where it’s used</th><th>24K value today</th></tr></thead><tbody>
<tr><td>Grain</td><td>0.0648</td><td>0.00208</td><td>Historic coinage, ammunition, fine assaying</td><td>{value:0.06479891g}</td></tr>
<tr><td>Gram</td><td>1</td><td>0.03215</td><td>Worldwide, jewelry</td><td>{value:1g}</td></tr>
<tr><td>Pennyweight (dwt)</td><td>1.5552</td><td>0.05</td><td>US jewelers and scrap buyers</td><td>{value:1dwt}</td></tr>
<tr><td>Chỉ</td><td>3.75</td><td>0.1206</td><td>Vietnam</td><td>{value:3.75g}</td></tr>
<tr><td>Tola</td><td>11.6638</td><td>0.375</td><td>India, Pakistan, Nepal, Gulf</td><td>{value:1tola}</td></tr>
<tr><td>Baht (Thai gold)</td><td>15.244</td><td>0.4901</td><td>Thailand (bullion bars)</td><td>{value:15.244g}</td></tr>
<tr><td>Troy ounce</td><td>31.1035</td><td>1</td><td>Global bullion standard</td><td>{value:1oz}</td></tr>
<tr><td>Tael (Hong Kong)</td><td>37.429</td><td>1.2034</td><td>Hong Kong, Chinese markets</td><td>{value:37.429g}</td></tr>
<tr><td>Kilogram</td><td>1,000</td><td>32.1507</td><td>Wholesale bars</td><td>{value:1kg}</td></tr>
</tbody></table>
<h2>Pennyweight (dwt)</h2>
<p>A pennyweight is 1/20 of a troy ounce — 1.555 grams. The “d” comes from <em>denarius</em>, the Roman penny. Many US gold buyers still quote prices per dwt, so it’s worth knowing: 14K gold is worth {value:1dwt:14k} per dwt today, and 10K gold {value:1dwt:10k}. See the <a href="/14k-gold-price">14K</a> and <a href="/10k-gold-price">10K</a> price pages.</p>
<h2>Grain</h2>
<p>The grain is the smallest traditional unit, originally the weight of a single grain of barley. There are 480 grains in a troy ounce and 24 grains in a pennyweight.</p>
<h2>Tola</h2>
<p>The tola (11.6638 g, exactly 180 grains) is the traditional gold unit across South Asia and the Gulf, and the 10 tola bar is one of the world’s most popular bars. See today’s <a href="/tola-gold-price">gold tola price</a>.</p>
<h2>Tael</h2>
<p>The tael varies by place: 37.429 g in Hong Kong, 50 g in mainland China and about 37.5 g in Taiwan. Hong Kong 5-tael and 1-tael bars still trade.</p>
<h2>Baht</h2>
<p>Thailand’s gold baht is 15.244 g for bullion (slightly less for jewelry). Thai gold is typically 96.5% pure.</p>
<h2>Which unit should you use?</h2>
<p>For bullion, use troy ounces; for jewelry and small bars, grams. When you compare prices between dealers, convert everything to the same unit first — the <a href="/guides/troy-ounce-vs-ounce">troy ounce guide</a> has the conversions.</p>
{calculator}
HTML,
  'faqs'=>[
    ['How many grams is a pennyweight?', '1.5552 grams. There are 20 pennyweights in a troy ounce.'],
    ['How many grains are in a troy ounce?', '480 grains.'],
    ['How many grams is a tola?', '11.6638 grams.'],
  ],
],
[
  'slug'=>'how-to-calculate-gold-value', 'topic'=>'karats', 'date'=>'2026-10-31',
  'title'=>'How to Calculate Gold Value: The Melt Value Formula (With Examples)',
  'seo_title'=>'How to Calculate Gold Value: Melt Value Formula & Examples',
  'seo_desc'=>'How to calculate the value of gold: weight in grams × purity × spot price per gram. Step-by-step examples for 10K, 14K, 18K and 24K gold, with a calculator.',
  'summary'=>'The simple formula for working out what any piece of gold is worth, with worked examples for each karat.',
  'body'=><<<'HTML'
<p class="lead"><strong>Gold value = weight in grams × purity × spot price per gram.</strong> Today the spot price is {gold_g} per gram, so 10 grams of 14K gold (58.3% pure) contains {value:10g:14k} of gold. This is called the <em>melt value</em>.</p>
<h2>Step 1: Find the purity</h2>
<p>Look for the karat stamp or fineness number:</p>
<table><thead><tr><th>Stamp</th><th>Karat</th><th>Purity</th></tr></thead><tbody>
<tr><td>417</td><td>10K</td><td>0.417</td></tr><tr><td>585</td><td>14K</td><td>0.583–0.585</td></tr><tr><td>750</td><td>18K</td><td>0.750</td></tr><tr><td>875</td><td>21K</td><td>0.875</td></tr><tr><td>916</td><td>22K</td><td>0.917</td></tr><tr><td>999 / 9999</td><td>24K</td><td>0.999+</td></tr>
</tbody></table>
<p>Not sure what a stamp means? See <a href="/guides/gold-hallmarks">gold hallmarks explained</a>.</p>
<h2>Step 2: Weigh it in grams</h2>
<p>Use a digital scale that reads to 0.01 g. Remove stones, pearls and non-gold parts where you can, or estimate their weight. Don’t use ounces from a kitchen scale — gold uses <a href="/guides/troy-ounce-vs-ounce">troy ounces</a>.</p>
<h2>Step 3: Get the spot price per gram</h2>
<p>Divide the spot price per troy ounce by 31.1035. Today: {gold_oz} ÷ 31.1035 = <strong>{gold_g}</strong> per gram. Or read it from our <a href="/gold-price-per-gram">gold price per gram</a> page.</p>
<h2>Step 4: Multiply</h2>
<p><strong>Weight (g) × purity × price per gram = melt value.</strong></p>
<h2>Worked examples at today’s price</h2>
<table><thead><tr><th>Item</th><th>Calculation</th><th>Melt value</th></tr></thead><tbody>
<tr><td>3 g 14K wedding band</td><td>3 × 0.583 × {gold_g}</td><td>{value:3g:14k}</td></tr>
<tr><td>8 g 10K class ring</td><td>8 × 0.417 × {gold_g}</td><td>{value:8g:10k}</td></tr>
<tr><td>20 g 18K bracelet</td><td>20 × 0.75 × {gold_g}</td><td>{value:20g:18k}</td></tr>
<tr><td>50 g 22K bangles</td><td>50 × 0.917 × {gold_g}</td><td>{value:50g:22k}</td></tr>
<tr><td>1 oz 24K gold bar</td><td>31.1035 × 1 × {gold_g}</td><td>{value:1oz}</td></tr>
</tbody></table>
<h2>Why buyers pay less than melt value</h2>
<p>Melt value is what the pure gold is worth before refining. A buyer must test it, refine it and cover their costs, so scrap gold usually sells for roughly 70–90% of melt value. Recognised bullion bars and coins sell much closer to 100%, because they don’t need refining. That’s one reason bullion is the most efficient way to own gold — see <a href="/guides/what-is-gold-bullion">what is gold bullion?</a></p>
{calculator:14}
HTML,
  'faqs'=>[
    ['How do you calculate the value of gold?', 'Multiply the weight in grams by the purity (e.g. 0.583 for 14K) and by the spot price per gram ({gold_g} today).'],
    ['What is melt value?', 'The value of the pure gold contained in an item at the current spot price, before any dealer margin or refining cost.'],
    ['How much is 1 gram of 14K gold worth?', '{value:1g:14k} today.'],
  ],
],
[
  'slug'=>'how-much-is-a-14k-gold-ring-worth', 'topic'=>'karats', 'date'=>'2026-10-31',
  'title'=>'How Much Is a 14K Gold Ring Worth? (Melt Value by Weight)',
  'seo_title'=>'How Much Is a 14K Gold Ring Worth? Value by Weight Today',
  'seo_desc'=>'A 14K gold ring is worth {value:1g:14k} per gram in gold today. See typical ring weights, a value chart for 2–10 gram rings, and how to get the best price.',
  'summary'=>'What a 14K gold ring is worth in gold, with typical ring weights and a value chart.',
  'body'=><<<'HTML'
<p class="lead"><strong>A 14K gold ring is worth about {value:1g:14k} per gram in gold value today.</strong> Most 14K rings weigh 2 to 10 grams, so their gold is typically worth between {value:2g:14k} and {value:10g:14k}. Diamonds and gemstones are valued separately.</p>
<h2>14K gold ring value chart</h2>
<table><thead><tr><th>Ring weight</th><th>Typical ring</th><th>14K gold value today</th><th>10K for comparison</th></tr></thead><tbody>
<tr><td>2 g</td><td>Thin band, stacking ring</td><td>{value:2g:14k}</td><td>{value:2g:10k}</td></tr>
<tr><td>3 g</td><td>Women’s wedding band, engagement setting</td><td>{value:3g:14k}</td><td>{value:3g:10k}</td></tr>
<tr><td>5 g</td><td>Men’s band (narrow), cocktail ring</td><td>{value:5g:14k}</td><td>{value:5g:10k}</td></tr>
<tr><td>8 g</td><td>Men’s wide band, class ring</td><td>{value:8g:14k}</td><td>{value:8g:10k}</td></tr>
<tr><td>10 g</td><td>Heavy signet ring</td><td>{value:10g:14k}</td><td>{value:10g:10k}</td></tr>
<tr><td>15 g</td><td>Large class or championship ring</td><td>{value:15g:14k}</td><td>{value:15g:10k}</td></tr>
</tbody></table>
<h2>How to find your ring’s value</h2>
<ol>
<li><strong>Check the stamp</strong> inside the band: 14K, 14KT or 585. “14K GP” or “14K GF” means plated or filled — see <a href="/guides/gold-plated-vs-gold-filled-vs-solid-gold">plated vs filled vs solid gold</a>.</li>
<li><strong>Weigh it</strong> on a 0.01 g scale. If it has stones, subtract their estimated weight (a 1 carat diamond weighs 0.2 g).</li>
<li><strong>Multiply</strong> the gold weight by today’s <a href="/14k-gold-price">14K gold price per gram</a> ({value:1g:14k}).</li>
</ol>
<h2>What a buyer will pay</h2>
<p>Gold buyers and pawn shops typically pay 70–90% of melt value for 14K jewelry. Designer rings (Tiffany, Cartier and similar) and antique pieces can be worth more than their gold, so have them appraised before selling as scrap.</p>
<h2>What about the diamond?</h2>
<p>Diamonds are valued on cut, colour, clarity and carat weight and resell for well below retail. A certified diamond (GIA, IGI) is easier to sell. Gold buyers usually pay little or nothing for small stones.</p>
<h2>Is it better to sell or keep?</h2>
<p>If the ring has sentimental value, consider keeping it — jewelry rarely sells for anything close to what it cost new. If you want to own gold as an investment, <a href="/gold-bars">gold bullion</a> avoids the jewelry markup entirely. Read <a href="/guides/where-to-sell-gold-and-silver">where to sell gold</a>.</p>
{calculator:14}
HTML,
  'faqs'=>[
    ['How much is a 14K gold ring worth?', 'Its gold is worth about {value:1g:14k} per gram today. A typical 3 g band contains {value:3g:14k} of gold.'],
    ['How much does a 14K gold ring weigh?', 'Most weigh 2–10 grams: thin bands 2–3 g, men’s bands 5–10 g, class rings 8–15 g.'],
    ['Is a 14K gold ring worth selling?', 'It has real melt value, but buyers pay 70–90% of it. Compare several offers and check whether the ring has designer or antique value first.'],
  ],
],
[
  'slug'=>'14k-vs-18k-gold', 'topic'=>'karats', 'date'=>'2026-10-31',
  'title'=>'14K vs 18K Gold: Value, Durability and Which to Choose',
  'seo_title'=>'14K vs 18K Gold: Price, Durability & Which Is Better',
  'seo_desc'=>'14K vs 18K gold compared: 58.3% vs 75% gold, price per gram today, colour, durability and allergies. Find out which is worth more and which lasts longer.',
  'summary'=>'The differences between 14K and 18K gold in value, colour, hardness and everyday wear.',
  'body'=><<<'HTML'
<p class="lead"><strong>18K gold contains 75% gold and 14K contains 58.3%, so 18K is worth about 29% more per gram</strong> — today {value:1g:18k} vs {value:1g:14k}. 14K is harder and more scratch-resistant; 18K is richer in colour and less likely to cause allergies.</p>
<h2>14K vs 18K at a glance</h2>
<table><thead><tr><th></th><th>14K gold</th><th>18K gold</th></tr></thead><tbody>
<tr><th>Gold content</th><td>58.3% (stamp 585)</td><td>75% (stamp 750)</td></tr>
<tr><th>Value per gram today</th><td>{value:1g:14k}</td><td>{value:1g:18k}</td></tr>
<tr><th>Value per ounce today</th><td>{value:1oz:14k}</td><td>{value:1oz:18k}</td></tr>
<tr><th>Colour</th><td>Paler yellow</td><td>Deeper, warmer yellow</td></tr>
<tr><th>Hardness</th><td>Harder, more scratch-resistant</td><td>Softer, shows wear sooner</td></tr>
<tr><th>Allergy risk</th><td>Higher (more alloy metals)</td><td>Lower</td></tr>
<tr><th>Common in</th><td>US everyday jewelry</td><td>Fine jewelry, Europe, watches</td></tr>
</tbody></table>
<h2>Which is worth more?</h2>
<p>18K, by gold content. A 5 gram ring in 18K contains {value:5g:18k} of gold; the same ring in 14K, {value:5g:14k}. Retail prices also reflect brand, design and labour, which often matter more than the karat.</p>
<h2>Which lasts longer?</h2>
<p>14K. The extra alloy metals make it harder, so it holds up better on rings worn every day, especially for people who work with their hands. 18K is ideal for pieces that see less wear — earrings, pendants, special-occasion rings — or for anyone with sensitive skin.</p>
<h2>What about white gold?</h2>
<p>14K white gold often contains nickel, a common allergen; 18K white gold is more often alloyed with palladium. Colour does not change the value — see <a href="/guides/white-gold-vs-yellow-gold">white gold vs yellow gold</a>.</p>
<h2>For investment, neither</h2>
<p>Jewelry carries big markups over its gold value. If your goal is owning gold, buy 24K <a href="/gold-bars">gold bars</a> or <a href="/gold-coins">coins</a> at a small premium over spot. Compare all karats in <a href="/guides/gold-karats-explained">gold karats explained</a>.</p>
HTML,
  'faqs'=>[
    ['Is 18K gold better than 14K?', '18K contains more gold and is worth more, but 14K is harder and more durable for everyday wear.'],
    ['How much more is 18K than 14K gold?', 'About 29% more per gram: {value:1g:18k} vs {value:1g:14k} today.'],
  ],
],
[
  'slug'=>'10k-vs-14k-gold', 'topic'=>'karats', 'date'=>'2026-10-31',
  'title'=>'10K vs 14K Gold: Is 10K Gold Real and Which Is Worth More?',
  'seo_title'=>'10K vs 14K Gold: Value, Durability & Is 10K Real Gold?',
  'seo_desc'=>'10K vs 14K gold: 41.7% vs 58.3% gold. Compare price per gram today, colour, durability and value, and find out if 10K gold is real gold.',
  'summary'=>'How 10K and 14K gold differ, which is worth more, and whether 10K counts as real gold.',
  'body'=><<<'HTML'
<p class="lead"><strong>14K gold contains 58.3% gold and 10K contains 41.7%, so 14K is worth about 40% more per gram</strong> — today {value:1g:14k} vs {value:1g:10k}. Both are real gold; 10K is the most durable and affordable, 14K has a richer colour and higher value.</p>
<h2>Comparison</h2>
<table><thead><tr><th></th><th>10K gold</th><th>14K gold</th></tr></thead><tbody>
<tr><th>Gold content</th><td>41.7% (stamp 417)</td><td>58.3% (stamp 585)</td></tr>
<tr><th>Value per gram today</th><td>{value:1g:10k}</td><td>{value:1g:14k}</td></tr>
<tr><th>Value per pennyweight</th><td>{value:1dwt:10k}</td><td>{value:1dwt:14k}</td></tr>
<tr><th>Durability</th><td>Hardest common karat</td><td>Very good</td></tr>
<tr><th>Colour</th><td>Paler yellow</td><td>Warmer yellow</td></tr>
<tr><th>Typical use</th><td>Chains, class rings, men’s jewelry</td><td>Engagement rings, everyday jewelry</td></tr>
</tbody></table>
<h2>Is 10K gold real gold?</h2>
<p>Yes. 10K is the lowest karat that can legally be called “gold” in the United States. It contains real gold with real melt value — {value:1g:10k} per gram today — just less of it than higher karats.</p>
<h2>Which is better?</h2>
<ul>
<li><strong>Choose 10K</strong> for chains and rings that take hard daily wear, or to keep costs down.</li>
<li><strong>Choose 14K</strong> for a better colour, more gold and a good balance of value and strength.</li>
</ul>
<h2>Value examples</h2>
<p>A 20 gram chain: 10K {value:20g:10k}, 14K {value:20g:14k}. A 10 gram class ring: 10K {value:10g:10k}, 14K {value:10g:14k}. Check live prices on the <a href="/10k-gold-price">10K</a> and <a href="/14k-gold-price">14K</a> pages.</p>
HTML,
  'faqs'=>[
    ['Is 10K or 14K gold worth more?', '14K, by about 40% per gram: {value:1g:14k} vs {value:1g:10k} today.'],
    ['Does 10K gold tarnish?', 'It can darken slightly over time because of its higher alloy content, but polishing restores it.'],
  ],
],
[
  'slug'=>'22k-vs-24k-gold', 'topic'=>'karats', 'date'=>'2026-10-31',
  'title'=>'22K vs 24K Gold: Purity, Price and Which to Buy',
  'seo_title'=>'22K vs 24K Gold: Difference in Purity, Price & Use',
  'seo_desc'=>'22K gold is 91.67% gold and 24K is 99.9%+. Compare the 22K and 24K gold price per gram today, durability, coins and jewelry, and which is better to buy.',
  'summary'=>'The real differences between 22K and 24K gold in purity, price, durability and use.',
  'body'=><<<'HTML'
<p class="lead"><strong>24K gold is pure gold (99.9% or more); 22K gold is 91.67% gold mixed with copper or silver for strength.</strong> Per gram today, 24K is worth {value:1g:24k} and 22K {value:1g:22k} — about 8% less.</p>
<h2>22K vs 24K comparison</h2>
<table><thead><tr><th></th><th>22K</th><th>24K</th></tr></thead><tbody>
<tr><th>Gold content</th><td>91.67% (stamp 916)</td><td>99.9–99.99% (999, 9999)</td></tr>
<tr><th>Price per gram today</th><td>{value:1g:22k}</td><td>{value:1g:24k}</td></tr>
<tr><th>Price per 10 grams</th><td>{value:10g:22k}</td><td>{value:10g:24k}</td></tr>
<tr><th>Price per tola</th><td>{value:1tola:22k}</td><td>{value:1tola:24k}</td></tr>
<tr><th>Hardness</th><td>Harder, holds shape</td><td>Very soft, scratches easily</td></tr>
<tr><th>Used for</th><td>Traditional jewelry, Gold Eagles, Krugerrands, Sovereigns</td><td>Bullion bars, most modern bullion coins</td></tr>
</tbody></table>
<h2>Why are some gold coins 22K?</h2>
<p>Coins like the American Gold Eagle and South African Krugerrand are 22K so they resist scratches and dents. They’re made slightly heavier (33.93 g) so they still contain exactly one troy ounce of pure gold — so a 22K 1 oz coin has the same gold value as a 24K 1 oz coin. See <a href="/guides/american-gold-eagle-vs-buffalo">Gold Eagle vs Buffalo</a>.</p>
<h2>Which should you buy?</h2>
<ul>
<li><strong>For investment:</strong> either — just compare the price per ounce of pure gold. 24K <a href="/gold-bars">gold bars</a> usually offer the lowest premium.</li>
<li><strong>For jewelry you’ll wear:</strong> 22K holds up better than 24K.</li>
<li><strong>For gifting tradition</strong> (weddings, Diwali, Eid): 22K jewelry or 24K coins and bars are both popular.</li>
</ul>
<p>Live prices: <a href="/22k-gold-price">22K gold price</a> · <a href="/24k-gold-price">24K gold price</a>.</p>
HTML,
  'faqs'=>[
    ['Is 22K or 24K gold better?', '24K is purer and worth more per gram; 22K is more durable. For investment, compare the price per ounce of pure gold.'],
    ['What is the price difference between 22K and 24K?', '22K is about 8.3% cheaper per gram: {value:1g:22k} vs {value:1g:24k} today.'],
  ],
],
[
  'slug'=>'gold-hallmarks', 'topic'=>'karats', 'date'=>'2026-10-31',
  'title'=>'Gold Hallmarks Explained: What 375, 417, 585, 750, 916 and 999 Mean',
  'seo_title'=>'Gold Stamps Explained: 375, 417, 585, 750, 916, 999 Meaning',
  'seo_desc'=>'What gold stamps mean: 417 is 10K, 585 is 14K, 750 is 18K, 916 is 22K and 999 is 24K. Plus GP, GF, HGE and vermeil marks, and what each is worth today.',
  'summary'=>'A complete guide to gold purity stamps and plating marks, and what each is worth per gram.',
  'body'=><<<'HTML'
<p class="lead"><strong>A gold hallmark is a number or code stamped on gold that tells you its purity.</strong> The three-digit numbers are parts per thousand: 585 means 58.5% gold (14K), 750 means 75% (18K), and 999 means 99.9% (24K).</p>
<h2>Gold purity stamps chart</h2>
<table><thead><tr><th>Stamp</th><th>Karat</th><th>Gold content</th><th>Value per gram today</th></tr></thead><tbody>
<tr><td>333</td><td>8K</td><td>33.3%</td><td>{value:1g:8k}</td></tr>
<tr><td>375</td><td>9K</td><td>37.5%</td><td>{value:1g:9k}</td></tr>
<tr><td>417</td><td>10K</td><td>41.7%</td><td>{value:1g:10k}</td></tr>
<tr><td>585</td><td>14K</td><td>58.5%</td><td>{value:1g:14k}</td></tr>
<tr><td>750</td><td>18K</td><td>75%</td><td>{value:1g:18k}</td></tr>
<tr><td>875</td><td>21K</td><td>87.5%</td><td>{value:1g:21k}</td></tr>
<tr><td>916</td><td>22K</td><td>91.6%</td><td>{value:1g:22k}</td></tr>
<tr><td>990</td><td>23.8K</td><td>99%</td><td>{value:0.99g}</td></tr>
<tr><td>999 / 9999</td><td>24K</td><td>99.9% / 99.99%</td><td>{value:1g:24k}</td></tr>
</tbody></table>
<h2>Marks that mean it is NOT solid gold</h2>
<ul>
<li><strong>GP / GEP / HGE / HGP</strong> — gold plated, gold electroplated, heavy gold electroplate: a thin gold layer over base metal.</li>
<li><strong>GF</strong> — gold filled: a thicker layer of gold bonded to a base metal, at least 1/20 of the total weight (often marked “1/20 14K GF”).</li>
<li><strong>RGP</strong> — rolled gold plate: like gold filled but a thinner layer.</li>
<li><strong>Vermeil</strong> — gold plating over sterling silver (look for “925”).</li>
</ul>
<p>More detail in <a href="/guides/gold-plated-vs-gold-filled-vs-solid-gold">gold plated vs gold filled vs solid gold</a>.</p>
<h2>Other marks you’ll see</h2>
<ul>
<li><strong>Maker’s mark</strong> — in the US, jewelry stamped with a quality mark must also carry the trademark of the company responsible for it.</li>
<li><strong>Assay office marks</strong> — in the UK and some other countries, independent assay offices test and hallmark gold.</li>
<li><strong>Bullion marks</strong> — bars and coins show weight, fineness and refiner or mint, usually with a serial number on bars.</li>
</ul>
<h2>Can stamps be faked?</h2>
<p>Yes — a stamp is a claim, not proof. Fakes sometimes carry convincing marks. If something seems off (too light, discoloured, magnetic), have it tested. See <a href="/guides/how-to-tell-if-gold-is-real">how to tell if gold is real</a>.</p>
HTML,
  'faqs'=>[
    ['What does 585 mean on gold?', '58.5% gold, which is 14 karat.'],
    ['What does 916 mean on gold?', '91.6% gold, which is 22 karat.'],
    ['What does 750 mean on gold?', '75% gold — 18 karat.'],
    ['What does GF mean on gold?', 'Gold filled: a layer of gold bonded over a base metal, not solid gold.'],
  ],
],
[
  'slug'=>'what-is-21k-gold', 'topic'=>'karats', 'date'=>'2026-10-31',
  'title'=>'What Is 21K Gold? Purity, Value and Why It’s Popular',
  'seo_title'=>'What Is 21K Gold? Purity (875), Price & Value Explained',
  'seo_desc'=>'21K gold is 87.5% pure gold (stamp 875), the favourite karat in the Middle East. Its value per gram today, how it compares to 18K and 22K, and how to buy it.',
  'summary'=>'What 21 karat gold is, what it’s worth, and why it’s the standard across the Middle East.',
  'body'=><<<'HTML'
<p class="lead"><strong>21K gold is 87.5% pure gold — 21 parts gold to 3 parts other metals — and is stamped 875.</strong> It’s worth {value:1g:21k} per gram today and is the most popular karat for jewelry in Egypt, Saudi Arabia, the UAE and across the Middle East.</p>
<h2>21K gold value</h2>
<table><thead><tr><th>Weight</th><th>21K value today</th></tr></thead><tbody>
<tr><td>1 gram</td><td>{value:1g:21k}</td></tr><tr><td>5 grams</td><td>{value:5g:21k}</td></tr><tr><td>10 grams</td><td>{value:10g:21k}</td></tr><tr><td>1 tola</td><td>{value:1tola:21k}</td></tr><tr><td>1 troy ounce</td><td>{value:1oz:21k}</td></tr><tr><td>50 grams</td><td>{value:50g:21k}</td></tr>
</tbody></table>
<p>Live: <a href="/21k-gold-price">21K gold price today</a> · Arabic: <a href="/ar/gold-price" hreflang="ar">سعر الذهب اليوم في أمريكا</a>.</p>
<h2>Why is 21K so popular in the Middle East?</h2>
<p>21K strikes a balance: high enough purity to hold real value and the rich yellow colour buyers prize, yet hard enough for bangles, chains and sets worn every day. Gold jewelry is traditionally bought as savings in the region, so a high-karat standard makes sense.</p>
<h2>21K vs 18K vs 22K</h2>
<ul>
<li><strong>18K</strong> (75%): {value:1g:18k}/g — harder, common in Europe and fine jewelry.</li>
<li><strong>21K</strong> (87.5%): {value:1g:21k}/g — Middle Eastern standard.</li>
<li><strong>22K</strong> (91.7%): {value:1g:22k}/g — South Asian standard, Gold Eagles and Krugerrands.</li>
</ul>
<h2>Buying 21K gold in the US</h2>
<p>21K jewelry is sold by specialist jewelers in many US cities. If your goal is saving in gold, 24K <a href="/gold-bars">gold bars</a> give you more gold for your money because they carry no making charges.</p>
HTML,
  'faqs'=>[
    ['Is 21K gold real gold?', 'Yes. 21K gold is 87.5% pure gold.'],
    ['How much is 21K gold per gram?', '{value:1g:21k} per gram today.'],
  ],
],
[
  'slug'=>'gold-plated-vs-gold-filled-vs-solid-gold', 'topic'=>'karats', 'date'=>'2026-10-31',
  'title'=>'Gold Plated vs Gold Filled vs Solid Gold: What’s the Difference?',
  'seo_title'=>'Gold Plated vs Gold Filled vs Solid Gold – Differences & Value',
  'seo_desc'=>'Gold plated, gold filled, vermeil and solid gold explained: how much gold each contains, how long it lasts, how to tell them apart, and what each is worth.',
  'summary'=>'How plated, filled, vermeil and solid gold differ in gold content, durability and value.',
  'body'=><<<'HTML'
<p class="lead"><strong>Solid gold is gold all the way through. Gold filled has a thick layer of real gold bonded to a base metal. Gold plated has a very thin layer of gold over base metal.</strong> Only solid gold has meaningful resale value.</p>
<h2>Comparison</h2>
<table><thead><tr><th></th><th>Gold plated</th><th>Vermeil</th><th>Gold filled</th><th>Solid gold</th></tr></thead><tbody>
<tr><th>Gold content</th><td>Tiny (microns thick)</td><td>Plating over sterling silver</td><td>At least 5% of total weight</td><td>All of it, at its karat</td></tr>
<tr><th>Marks</th><td>GP, GEP, HGE</td><td>Vermeil, 925</td><td>GF, 1/20 14K GF</td><td>10K, 14K, 585, 750…</td></tr>
<tr><th>Lifespan</th><td>Months to a few years</td><td>A few years</td><td>Many years, even decades</td><td>Lifetime</td></tr>
<tr><th>Resale value</th><td>Almost none</td><td>Mainly the silver</td><td>Low</td><td>Full melt value</td></tr>
</tbody></table>
<h2>Gold plated</h2>
<p>A microscopically thin layer of gold is applied by electroplating. It wears through with use, revealing the base metal. Scrap value is negligible.</p>
<h2>Gold vermeil</h2>
<p>Gold plating over sterling silver. In the US, vermeil must have a gold layer of at least 10 karat and a minimum thickness. Its resale value is mostly the silver.</p>
<h2>Gold filled</h2>
<p>A thick layer of karat gold is heat- and pressure-bonded to a brass core. In the US the gold must be at least 1/20 of the item’s total weight. A 20 g “1/20 14K GF” chain contains about 1 g of 14K gold — {value:1g:14k} of gold.</p>
<h2>Solid gold</h2>
<p>Gold alloyed at a specific karat throughout — <a href="/guides/gold-karats-explained">10K to 24K</a>. Its value is its weight × purity × spot price: see <a href="/guides/how-to-calculate-gold-value">how to calculate gold value</a>.</p>
<h2>How to tell them apart</h2>
<ul>
<li>Read the stamp — see <a href="/guides/gold-hallmarks">gold hallmarks</a>.</li>
<li>Look for wear at edges and clasps showing a different colour underneath.</li>
<li>Solid gold is heavier for its size.</li>
<li>An acid test or XRF test at a jeweler gives a definite answer.</li>
</ul>
HTML,
  'faqs'=>[
    ['Is gold filled real gold?', 'It contains a real layer of karat gold, at least 5% of its weight, bonded to a base metal. It is not solid gold.'],
    ['Is gold plated jewelry worth anything?', 'Very little as scrap; the gold layer is extremely thin.'],
  ],
],
[
  'slug'=>'gold-bar-dimensions', 'topic'=>'bars', 'date'=>'2026-10-31',
  'title'=>'Gold Bar Dimensions: How Big Is a Gold Bar?',
  'seo_title'=>'Gold Bar Dimensions: How Big Is a Gold Bar? (Every Size)',
  'seo_desc'=>'How big is a gold bar? Typical dimensions of 1 g, 1 oz, 10 oz, 1 kg and 400 oz gold bars in mm and inches, and why gold bars are so small for their value.',
  'summary'=>'Typical sizes of every gold bar from 1 gram to 400 oz, in millimetres and inches.',
  'body'=><<<'HTML'
<p class="lead"><strong>Gold bars are much smaller than most people expect.</strong> A 1 oz gold bar is smaller than a business card, a 1 kilo bar is about the size of a smartphone, and even the 400 oz bars in central bank vaults are only about 10 inches long.</p>
<h2>Typical gold bar dimensions</h2>
<p>Exact sizes vary by refiner; minted bars are thinner and wider than cast bars of the same weight.</p>
<table><thead><tr><th>Bar</th><th>Typical size (mm)</th><th>Approx. inches</th><th>Gold value today</th></tr></thead><tbody>
<tr><td><a href="/1-gram-gold-bars">1 gram</a> (minted)</td><td>15 × 9 × 0.4</td><td>0.6 × 0.35</td><td>{value:1g}</td></tr>
<tr><td>5 gram (minted)</td><td>23 × 14 × 0.9</td><td>0.9 × 0.55</td><td>{value:5g}</td></tr>
<tr><td><a href="/10-gram-gold-bars">10 gram</a> (minted)</td><td>28 × 16 × 1.3</td><td>1.1 × 0.6</td><td>{value:10g}</td></tr>
<tr><td><a href="/1-oz-gold-bars">1 oz</a> (minted)</td><td>41 × 24 × 1.8</td><td>1.6 × 0.95</td><td>{value:1oz}</td></tr>
<tr><td><a href="/100-gram-gold-bars">100 gram</a> (minted)</td><td>55 × 31 × 3</td><td>2.2 × 1.2</td><td>{value:100g}</td></tr>
<tr><td><a href="/10-oz-gold-bars">10 oz</a> (cast)</td><td>62 × 28 × 9</td><td>2.4 × 1.1 × 0.35</td><td>{value:10oz}</td></tr>
<tr><td><a href="/1-kilo-gold-bars">1 kilo</a> (cast)</td><td>115 × 52 × 15</td><td>4.5 × 2 × 0.6</td><td>{value:1kg}</td></tr>
<tr><td><a href="/100-oz-gold-bars">100 oz</a> (cast)</td><td>140 × 58 × 27</td><td>5.5 × 2.3 × 1.1</td><td>{value:100oz}</td></tr>
<tr><td><a href="/guides/400-oz-gold-bar">400 oz</a> (cast)</td><td>~255 × 80 × 37 (top)</td><td>~10 × 3 × 1.5</td><td>{value:400oz}</td></tr>
</tbody></table>
<h2>Why gold bars are so small</h2>
<p>Gold is one of the densest metals: 19.32 grams per cubic centimetre, about 1.7 times lead. A cube of gold with 10 cm sides would weigh 19.3 kg. That density is why a fortune fits in your hand — and why fake bars use tungsten, which is almost as dense. See <a href="/guides/hollow-and-tungsten-filled-gold-bars">tungsten-filled gold bars</a>.</p>
<h2>Measuring your bar</h2>
<p>Checking dimensions with digital calipers against the refiner’s published specification is one of the best simple authenticity checks: a fake made of a lighter metal must be bigger to reach the right weight. Combine it with <a href="/guides/gold-bar-weight-and-sizes">weight checks</a>.</p>
HTML,
  'faqs'=>[
    ['How big is a 1 oz gold bar?', 'About 41 × 24 mm (1.6 × 0.95 inches) and under 2 mm thick for a minted bar.'],
    ['How big is a kilo gold bar?', 'Roughly 115 × 52 × 15 mm — about the size of a smartphone.'],
    ['How big is a standard gold bar?', 'A 400 oz Good Delivery bar is about 10 inches long, 3 inches wide and 1.5 inches tall.'],
  ],
],
];
