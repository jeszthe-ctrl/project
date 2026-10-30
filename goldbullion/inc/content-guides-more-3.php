<?php
/* Extra sections and FAQs added to guides (appended to their text). Part 3. */
if(!defined('GB_ROOT')){ http_response_code(404); exit; }

return [
'gold-price-history'=>['body'=><<<'HTML'
<h2>Gold’s biggest bull markets</h2>
<ul>
<li><strong>1971–1980:</strong> from about $35 to about $850 as inflation soared after the end of the gold standard.</li>
<li><strong>2001–2011:</strong> from about $255 to about $1,900 through the dot-com bust, the financial crisis and quantitative easing.</li>
<li><strong>2018 onwards:</strong> from about $1,200 to new records above $4,000, driven by central bank buying, pandemic stimulus and geopolitical tension.</li>
</ul>
<h2>Gold’s biggest declines</h2>
<ul>
<li><strong>1980–1999:</strong> a long bear market as interest rates rose sharply and inflation was tamed.</li>
<li><strong>2011–2015:</strong> a fall of roughly 45% as the economy recovered and the dollar strengthened.</li>
</ul>
<h2>Gold and silver price history</h2>
<p>Silver has followed gold’s big trends with larger swings. Silver traded near $50 in 1980 and again in 2011 before falling sharply both times. Today it is {silver_oz}. See <a href="/guides/gold-vs-silver">gold vs silver</a>.</p>
<h2>Using history wisely</h2>
<p>History shows gold’s long-term role as a store of value, but it can’t tell you next year’s price. Buying gradually and holding for the long term avoids the trap of buying everything at a peak.</p>
HTML, 'faqs'=>[
  ['When did gold first reach $1,000?', 'In 2008, during the build-up to the global financial crisis.'],
  ['When did gold pass $4,000?', 'In October 2025.'],
]],

'gold-vs-silver'=>['body'=><<<'HTML'
<h2>Storage: the hidden difference</h2>
<p>$10,000 of gold at today’s price is about {gold_to_10k} ounces — a handful of coins. The same $10,000 in silver is roughly {silver_to_10k} ounces, weighing several kilograms and needing far more space. For larger sums, gold is much easier to store and transport.</p>
<h2>Premiums and spreads</h2>
<p>Silver products usually carry higher premiums as a percentage of their metal value, because shipping and handling cost the same per ounce while silver is worth far less. In some US states, silver and gold bullion are also taxed differently, so check your state’s rules.</p>
<h2>Volatility</h2>
<p>Silver’s smaller market and industrial use make its price swing much more than gold’s — often rising faster in bull markets and falling harder in downturns.</p>
<h2>A simple approach</h2>
<p>Many buyers start with gold as their core holding and add silver for diversification, often keeping silver to a minority of their precious metals. Track both on our <a href="/gold-price">live price page</a>.</p>
HTML, 'faqs'=>[
  ['Is silver more volatile than gold?', 'Yes. Silver typically moves more than gold in both directions.'],
  ['What is a good gold/silver ratio to buy silver?', 'Some investors favour silver when the ratio is high (for example above 80), but it is a rule of thumb, not a guarantee.'],
]],

'is-gold-a-good-investment'=>['body'=><<<'HTML'
<h2>Gold in different economic conditions</h2>
<table><thead><tr><th>Condition</th><th>Gold has tended to…</th></tr></thead><tbody>
<tr><td>High inflation</td><td>Perform well, especially when interest rates lag inflation</td></tr>
<tr><td>Financial crisis</td><td>Hold up better than stocks, sometimes after an initial dip</td></tr>
<tr><td>Rising real interest rates</td><td>Struggle</td></tr>
<tr><td>Strong dollar</td><td>Struggle in dollar terms</td></tr>
<tr><td>Steady growth, low inflation</td><td>Lag stocks</td></tr>
</tbody></table>
<h2>Dollar-cost averaging into gold</h2>
<p>Buying a fixed amount at regular intervals — for example a <a href="/1-gram-gold-bars">1 gram</a> or <a href="/10-gram-gold-bars">10 gram bar</a> each month — smooths out the effect of price swings and removes the stress of timing the market.</p>
<h2>Common mistakes</h2>
<ul>
<li>Buying collectible coins at high markups and calling them an investment.</li>
<li>Responding to pressure sales calls or “gold IRA” cold calls without comparing costs.</li>
<li>Putting too much of your savings in a single asset.</li>
<li>Forgetting storage, insurance and records.</li>
</ul>
<h2>Who gold suits</h2>
<p>Gold suits long-term savers who want insurance against currency and financial risks and who are comfortable that it produces no income. It suits short-term traders less well.</p>
HTML, 'faqs'=>[
  ['Does gold protect against inflation?', 'Over long periods it has, but over shorter periods its relationship with inflation has been uneven.'],
  ['Is it better to buy gold all at once or over time?', 'Buying over time reduces the risk of buying everything at a high price.'],
]],

'best-gold-coins-to-buy'=>['body'=><<<'HTML'
<h2>Security features on modern bullion coins</h2>
<ul>
<li><strong>Canadian Maple Leaf:</strong> radial lines and a micro-engraved laser mark.</li>
<li><strong>British Britannia:</strong> a latent image that changes when tilted, surface animation and micro-text.</li>
<li><strong>American Gold Eagle (2021+):</strong> a new reverse design with anti-counterfeiting features in the reeded edge.</li>
</ul>
<h2>Coins to approach with caution</h2>
<ul>
<li><strong>Proof and “limited edition” coins</strong> — beautiful, but often sold at large markups over their gold value.</li>
<li><strong>“Graded” modern bullion coins</strong> at high premiums — the grade rarely adds resale value.</li>
<li><strong>Commemorative coins sold on TV</strong> — often gold-plated, not solid gold.</li>
</ul>
<h2>Pre-1933 US gold coins</h2>
<p>$20 Double Eagles, $10 Eagles and $5 Half Eagles are 90% gold and sometimes trade only modestly above their melt value in common dates. See their melt values on our <a href="/gold-coin-prices">gold coin prices</a> page.</p>
HTML, 'faqs'=>[
  ['Are proof gold coins a good investment?', 'They usually cost much more than their gold value. For investment, bullion coins are more efficient.'],
  ['Which gold coin has the lowest premium?', 'It varies with supply, but the Krugerrand, Maple Leaf and Philharmonic are often among the lowest.'],
]],

'american-gold-eagle-vs-buffalo'=>['body'=><<<'HTML'
<h2>Type 1 vs Type 2 Gold Eagles</h2>
<p>In 2021 the US Mint redesigned the Gold Eagle’s reverse: Type 1 coins (1986–2021) show a family of eagles at a nest; Type 2 coins (2021 onwards) show a close-up eagle head and include improved anti-counterfeiting features. Both contain the same gold.</p>
<h2>Proof and uncirculated versions</h2>
<p>Both coins are also sold as proof and burnished collector versions directly by the US Mint at higher prices. For investment, the standard bullion versions are the most cost-effective.</p>
<h2>Melt values today</h2>
<ul><li>1 oz Gold Eagle or Buffalo: {value:1oz}</li><li>1/2 oz Gold Eagle: {value:0.5oz}</li><li>1/4 oz Gold Eagle: {value:0.25oz}</li><li>1/10 oz Gold Eagle: {value:0.1oz}</li></ul>
<h2>IRA eligibility</h2>
<p>Both bullion coins are commonly accepted in self-directed precious metals IRAs held by an approved custodian. Check with your custodian.</p>
HTML, 'faqs'=>[
  ['What is the face value of a Gold Eagle?', '$50 for the 1 oz coin; $25, $10 and $5 for the fractional sizes.'],
  ['How much does a 1 oz Gold Eagle weigh?', '33.93 grams, including about 2.8 g of copper and silver alloy.'],
]],

'how-much-gold-is-in-the-world'=>['body'=><<<'HTML'
<h2>Visualising all the gold</h2>
<p>Gold is so dense that 216,000 tonnes would fill only about 11,200 cubic metres — roughly four Olympic-size swimming pools. A single tonne fits in a cube about 37 cm on each side.</p>
<h2>Where gold is mined</h2>
<p>China, Russia, Australia, Canada and the United States are among the largest producers, along with Ghana, Peru, Mexico and others. In the US, Nevada produces the large majority of the country’s gold.</p>
<h2>How much gold per person?</h2>
<p>Divided among roughly eight billion people, all the gold ever mined comes to about 27 grams each — less than one troy ounce, worth about {value:27g} today.</p>
<h2>Will we run out of gold?</h2>
<p>Gold isn’t consumed the way oil is: almost all the gold ever mined still exists. What is limited is new supply, which is growing slowly and becoming more expensive to produce.</p>
HTML, 'faqs'=>[
  ['How much gold is there per person?', 'About 27 grams each if all the gold ever mined were shared among roughly eight billion people.'],
  ['Which country mines the most gold?', 'China has been the largest producer in recent years, followed by Russia and Australia.'],
]],

'how-to-sell-gold-bars'=>['body'=><<<'HTML'
<h2>Selling gold bars by size</h2>
<ul>
<li><strong>1 g–1 oz bars:</strong> easy to sell at almost any coin shop or online dealer.</li>
<li><strong>10 oz and 100 g bars:</strong> widely bought by dealers; sealed or serial-numbered bars sell best.</li>
<li><strong>1 kg and larger:</strong> fewer local buyers; online and larger dealers are better. Expect a test for cast bars bought privately.</li>
</ul>
<h2>Selling gold coins</h2>
<p>Bullion coins (Eagles, Maples, Krugerrands, Buffalos) are bought by nearly every dealer, usually at a small discount to their selling price. Coins in original tubes or capsules may fetch a little more.</p>
<h2>How fast will I be paid?</h2>
<p>Locally, often immediately. Online, after the dealer receives and checks your items — usually within a few business days by bank transfer or check.</p>
<h2>Keep records</h2>
<p>Note the date, items, weight, price per ounce and total for every sale, along with your original purchase records, for your tax return.</p>
HTML, 'faqs'=>[
  ['Will a dealer buy an opened gold bar?', 'Usually yes, after testing, though possibly at a slightly lower price.'],
  ['Do American Gold Eagles have to be reported when sold?', 'Gold Eagles are not on the IRS list of items dealers must report on Form 1099-B, but your gains are still taxable.'],
]],

'gold-assay-cards-and-certificates'=>['body'=><<<'HTML'
<h2>Assay card vs certificate of authenticity</h2>
<p>An <strong>assay card</strong> seals the bar together with its details. A separate <strong>certificate</strong> (common with cast bars) is a document listing the bar’s serial number and details. Keep certificates with their bars; a mismatched or missing certificate can make resale slower.</p>
<h2>Weighing a bar in its card</h2>
<p>Many refiners publish the total weight of the bar in its packaging. Comparing against that figure is a useful check without opening the card. Your <a href="/guides/best-scale-for-gold">scale</a> needs to be accurate to 0.01 g.</p>
<h2>Storing carded bars</h2>
<p>Keep cards flat and dry, away from heat and sharp objects. Plastic sleeves or a purpose-made case prevent scuffs and cracks.</p>
HTML, 'faqs'=>[
  ['Do all gold bars come with an assay card?', 'Most small minted bars do. Larger cast bars are often sold loose, sometimes with a separate certificate.'],
  ['What is assaying?', 'Testing a sample of metal to confirm its exact purity.'],
]],

'buying-gold-online-vs-local-coin-shop'=>['body'=><<<'HTML'
<h2>What to look for in an online gold dealer</h2>
<ul>
<li>A published phone number, email and business details.</li>
<li>Clear pricing linked to the live spot price, and a price lock when you order.</li>
<li>Written shipping, returns, privacy and authenticity policies.</li>
<li>Fully insured shipping with signature on delivery and discreet packaging.</li>
<li>Recognised products from established refiners and mints.</li>
</ul>
<h2>Scams to avoid, online or offline</h2>
<ul>
<li>Sellers on social media or messaging apps offering gold below spot.</li>
<li>Requests to pay by gift card or to a person whose name doesn’t match the business.</li>
<li>Anyone asking you to buy gold and hand it to a courier — a common scam aimed at older adults.</li>
</ul>
<h2>Combining both</h2>
<p>Some buyers order larger quantities online for selection and price, and use a local shop for quick small purchases or sales. Either way, compare the total delivered cost per ounce.</p>
HTML, 'faqs'=>[
  ['How do I find gold and silver shops near me?', 'Search maps for coin or bullion dealers, then check reviews and ask for prices as dollars over spot.'],
  ['How is gold shipped?', 'Reputable dealers ship fully insured in plain packaging, with tracking and signature on delivery.'],
]],

'how-gold-bullion-is-made'=>['body'=><<<'HTML'
<h2>Responsible sourcing</h2>
<p>Major refiners follow responsible sourcing standards, such as the LBMA Responsible Gold Guidance, which require checks on where gold comes from to reduce the risk of conflict gold, money laundering and human rights abuses. Good Delivery refiners are audited against these standards.</p>
<h2>How long does it take?</h2>
<p>Mining and processing are continuous industrial operations. Refining a batch of doré to 99.99% can take days; casting and minting bars is quick once the refined gold is ready.</p>
<h2>Why refinery brands matter</h2>
<p>Because buyers trust established refiners to have done all of this correctly, bars from famous names sell more easily. See <a href="/guides/best-gold-bars-to-buy">best gold bars to buy</a>.</p>
HTML, 'faqs'=>[
  ['How pure can gold be refined?', 'Commercially to 99.99% (four nines); specialist processes go higher for scientific use.'],
  ['Is recycled gold the same as mined gold?', 'Yes. Once refined, recycled gold is chemically identical to newly mined gold.'],
]],
];
