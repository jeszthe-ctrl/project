<?php
/* Extra sections and FAQs added to guides (appended to their text). Part 2. */
if(!defined('GB_ROOT')){ http_response_code(404); exit; }

return [
'physical-gold-vs-gold-etfs'=>['body'=><<<'HTML'
<h2>Costs over time</h2>
<p>Physical gold has an upfront cost (the premium) and possibly storage and insurance; an ETF has a small upfront trading cost but an annual fee that compounds year after year. Over very long holding periods, physical bullion bought at a low premium can be cheaper; for short holding periods, ETFs usually are.</p>
<h2>Taxes</h2>
<p>In the US, both physical gold and most physically backed gold ETFs are generally taxed as collectibles on long-term gains (up to 28%). Gold mining stocks and miners ETFs are generally taxed like other stocks. Rules change and individual situations vary — ask a tax professional.</p>
<h2>Risks compared</h2>
<table><thead><tr><th>Risk</th><th>Physical gold</th><th>Gold ETF</th><th>Miners ETF</th></tr></thead><tbody>
<tr><td>Gold price falls</td><td>Yes</td><td>Yes</td><td>Yes, often more</td></tr>
<tr><td>Theft or loss</td><td>Yes — store and insure it</td><td>No</td><td>No</td></tr>
<tr><td>Counterparty or custody</td><td>None</td><td>Fund and custodian</td><td>Companies and fund</td></tr>
<tr><td>Company-specific risk</td><td>None</td><td>None</td><td>Costs, accidents, management</td></tr>
</tbody></table>
<h2>Liquidity</h2>
<p>ETFs can be sold in seconds during market hours. Physical gold can be sold to dealers the same day, but you need to deliver it. Both are highly liquid compared with most real assets.</p>
HTML, 'faqs'=>[
  ['Can I convert a gold ETF into physical gold?', 'Generally not for individual investors in major US gold ETFs; some specialist products offer delivery.'],
  ['Are gold miners riskier than gold?', 'Usually. Mining shares tend to rise and fall more than the gold price and carry company risks.'],
]],

'gold-futures-vs-spot-price'=>['body'=><<<'HTML'
<h2>A worked example</h2>
<p>Suppose spot gold is {gold_oz}. If interest rates are around 4–5% a year, a futures contract for delivery in twelve months would typically trade a few percent higher, because the seller is effectively financing and storing the gold for a year. The gap shrinks as delivery approaches, until futures and spot converge.</p>
<h2>Who uses gold futures?</h2>
<ul>
<li><strong>Miners and refiners</strong> hedging future production.</li>
<li><strong>Jewelers and manufacturers</strong> locking in input costs.</li>
<li><strong>Banks and funds</strong> managing positions.</li>
<li><strong>Speculators</strong> using leverage — with the risk of losing more than their initial margin.</li>
</ul>
<h2>Physical bullion vs futures for individuals</h2>
<p>Futures require a brokerage account, margin and active management, and most contracts are 100 oz ({value:100oz} today). Physical <a href="/gold-bars">gold bars</a> and <a href="/gold-coins">coins</a> have no leverage, no expiry and no margin calls — which is why most individual savers prefer them.</p>
HTML, 'faqs'=>[
  ['What is contango in gold?', 'When futures prices are higher than spot, reflecting the cost of financing and storing gold until delivery. It is the normal state of the gold market.'],
  ['Is the gold price on TV the spot price?', 'Often it is the most active COMEX futures price, which can differ slightly from spot.'],
]],

'where-to-sell-gold-and-silver'=>['body'=><<<'HTML'
<h2>Questions to ask a gold buyer</h2>
<ul>
<li>What percentage of spot (or melt value) do you pay for this item?</li>
<li>How do you test it, and is testing free?</li>
<li>Do you charge any fees?</li>
<li>How and when will I be paid?</li>
<li>Will you put the offer in writing?</li>
</ul>
<h2>Red flags when selling</h2>
<ul>
<li>A buyer who won’t say what percentage of spot they’re paying.</li>
<li>Weighing out of your sight, or using a scale you can’t read.</li>
<li>Pressure to sell immediately or “today only” prices.</li>
<li>Mail-in services that send a low offer once they have your gold.</li>
</ul>
<h2>Selling silver</h2>
<p>Silver bullion sells close to spot at dealers, but because silver’s value per ounce is far lower, dealer spreads take a larger percentage. Sterling silver (925) jewelry and flatware sell for their silver content; silver-plated items are worth very little. Today silver is {silver_oz} per ounce.</p>
<h2>Selling gold jewelry</h2>
<p>Sort by karat, weigh each group, and work out melt value with our <a href="/guides/how-to-calculate-gold-value">gold value calculator guide</a>. Designer and antique pieces may be worth more as jewelry than as scrap.</p>
HTML, 'faqs'=>[
  ['How much do gold buyers pay?', 'Typically 70–90% of melt value for jewelry and close to spot for recognised bullion bars and coins.'],
  ['Is it better to sell gold to a jeweler or a coin shop?', 'For bullion, a coin or bullion shop usually pays more. For designer jewelry, a jeweler or specialist reseller may.'],
]],

'how-to-store-gold-bullion'=>['body'=><<<'HTML'
<h2>Choosing a home safe</h2>
<ul>
<li><strong>Burglary rating:</strong> look for a UL TL-15 or TL-30 rating or an equivalent independent rating for higher values.</li>
<li><strong>Fire rating:</strong> gold won’t burn, but packaging and paperwork can.</li>
<li><strong>Anchoring:</strong> bolt it to the floor or a wall so it can’t be carried away.</li>
<li><strong>Location:</strong> out of sight, not in the master bedroom closet (the first place burglars look).</li>
</ul>
<h2>Insurance</h2>
<p>Standard homeowners policies often cap coverage for precious metals at a low figure. Ask your insurer about a scheduled rider for bullion, keep receipts and photos, and update coverage as your holdings and the gold price change.</p>
<h2>Keeping records</h2>
<p>Keep an inventory with product, weight, serial number, purchase date and price. Store a copy away from the gold. You’ll need purchase prices for tax when you sell, and your heirs will need to know what you own and where.</p>
<h2>Splitting your storage</h2>
<p>Many owners spread larger holdings: some at home for quick access, some in a bank box or vault. That limits the damage from any single theft, fire or access problem.</p>
HTML, 'faqs'=>[
  ['Where do people hide gold at home?', 'In a bolted, rated safe or a well-concealed location away from obvious places. Discretion is the best protection.'],
  ['Should I tell anyone I own gold?', 'Only people who need to know, such as a spouse or your executor.'],
]],

'gold-quarters'=>['body'=><<<'HTML'
<h2>Valuable gold-coloured coins worth checking</h2>
<ul>
<li><strong>2000-P Sacagawea “Cheerios” dollar</strong> — a rare early variety distributed in cereal boxes, worth far more than face value.</li>
<li><strong>Presidential dollars with missing edge lettering</strong> — error coins that collectors seek.</li>
<li><strong>Proof and uncirculated sets</strong> — sometimes more than face value.</li>
</ul>
<p>Ordinary circulated golden dollars are worth $1.</p>
<h2>Gold-plated quarter sets</h2>
<p>Gold-plated quarter collections are often sold in attractive boxes at prices far above their value. They make nice keepsakes, but dealers generally treat them as face value. They are not an investment.</p>
<h2>Real gold coins at affordable sizes</h2>
<p>If you want an affordable real gold coin, look at 1/10 oz and 1/4 oz bullion coins, or 1 gram coins and bars. A 1/4 oz coin contains {value:0.25oz} of gold today and a 1/10 oz coin {value:0.1oz}. See <a href="/guides/mini-gold-coins">mini gold coins</a>.</p>
HTML, 'faqs'=>[
  ['Is a gold quarter worth more than 25 cents?', 'A gold-plated quarter is usually worth around face value. A pre-1929 quarter eagle ($2.50 gold coin) is worth much more.'],
  ['What is a quarter eagle?', 'A US $2.50 gold coin minted until 1929, containing about 0.121 oz of gold.'],
]],

'donde-comprar-oro'=>['body'=><<<'HTML'
<h2>¿Cuánto cuesta el oro hoy?</h2>
<ul>
<li>1 gramo de oro de 24 quilates: {value:1g}</li>
<li>1 onza troy (31,1 g): {value:1oz}</li>
<li>Lingote de 10 gramos: {value:10g}</li>
<li>Lingote de 1 kilo: {value:1kg}</li>
</ul>
<p>Estos son valores del oro según el precio spot. Los productos se venden con una pequeña prima sobre ese valor.</p>
<h2>¿Lingotes o monedas?</h2>
<p>Los lingotes ofrecen más oro por cada dólar. Las monedas de oro de inversión, como el American Gold Eagle o la Maple Leaf canadiense, cuestan un poco más pero son muy fáciles de vender. Muchos compradores tienen ambos. Lea <a href="/guides/gold-coins-vs-gold-bars">monedas de oro vs lingotes</a> (en inglés).</p>
<h2>Impuestos al comprar y vender oro</h2>
<p>Muchos estados de EE. UU. no cobran impuesto sobre las ventas a los lingotes y monedas de inversión, pero otros sí. Las ganancias al vender oro físico pagan impuestos; consulte con un profesional.</p>
<h2>Cómo guardar el oro</h2>
<p>Mantenga los lingotes en su empaque original, en una caja fuerte anclada o en una caja de seguridad bancaria, y no comente sus compras en redes sociales.</p>
HTML, 'faqs'=>[
  ['¿Cuánto vale una onza de oro hoy?', '{gold_oz} según el precio spot de hoy.'],
  ['¿Qué oro es mejor para invertir?', 'Los lingotes y monedas de oro de 24 quilates de refinerías y casas de moneda reconocidas.'],
]],

'14k-vs-18k-gold'=>['body'=><<<'HTML'
<h2>Engagement rings: 14K or 18K?</h2>
<p>In the US, 14K is the most popular choice for engagement rings and wedding bands because it balances colour, durability and cost. 18K is popular for luxury pieces and in Europe. For prong settings that hold a valuable diamond, some jewelers prefer platinum or 14K prongs for strength.</p>
<h2>Price difference on real pieces</h2>
<p>Because 18K contains more gold, the same design costs more in 18K. For a ring with 4 g of metal, the gold content difference today is {value:4g:18k} vs {value:4g:14k}. The retail price gap is usually larger, because jewelers price on design and labour as well.</p>
<h2>How to tell 14K from 18K</h2>
<p>Check the stamp (585 or 14K vs 750 or 18K). Without a stamp, a jeweler can test it with acid or XRF. See <a href="/guides/gold-hallmarks">gold hallmarks explained</a>.</p>
<h2>Resale</h2>
<p>When selling as scrap, both are valued on gold content. 18K returns more per gram; neither returns anything close to its original retail price. Use our <a href="/guides/how-to-calculate-gold-value">gold value guide</a> to work it out.</p>
HTML, 'faqs'=>[
  ['Does 18K gold scratch more easily than 14K?', 'Yes, because it contains more soft pure gold.'],
  ['Is 14K gold good for everyday wear?', 'Yes, it is one of the best karats for daily-wear rings and chains.'],
]],

'10k-vs-14k-gold'=>['body'=><<<'HTML'
<h2>Which is better for chains?</h2>
<p>Many men’s chains are 10K because it resists stretching and scratching and keeps prices manageable on heavy pieces. A heavy 50 g chain contains {value:50g:10k} of gold in 10K vs {value:50g:14k} in 14K — a big difference on large pieces.</p>
<h2>Can you tell 10K from 14K by looking?</h2>
<p>Side by side, 14K looks slightly warmer and more yellow. On its own, it’s hard to tell. Check the stamp (417 or 10K, 585 or 14K) or have it tested.</p>
<h2>Allergies</h2>
<p>10K contains more alloy metals, so people with metal sensitivities are more likely to react to 10K than 14K. Nickel-containing white gold is the most common culprit.</p>
<h2>Resale value</h2>
<p>Scrap buyers pay on gold content: 14K returns about 40% more per gram than 10K. See the live <a href="/10k-gold-price">10K</a> and <a href="/14k-gold-price">14K</a> prices.</p>
HTML, 'faqs'=>[
  ['Is 10K gold good quality?', 'It is real gold and very durable, though it contains less gold than 14K.'],
  ['Why is 10K gold cheaper?', 'It contains 41.7% gold compared with 58.3% in 14K.'],
]],

'22k-vs-24k-gold'=>['body'=><<<'HTML'
<h2>22K gold in South Asian jewelry</h2>
<p>22K is the traditional standard for jewelry in India, Pakistan, Bangladesh and Nepal, where gold is bought for weddings and festivals and as family savings. It keeps a rich colour while holding intricate designs better than 24K. Jewelers there often price by the gram or tola plus a making charge.</p>
<h2>Price per tola and per 10 grams</h2>
<p>Today: 22K {value:1tola:22k} per tola and {value:10g:22k} per 10 g; 24K {value:1tola:24k} per tola and {value:10g:24k} per 10 g. See the <a href="/tola-gold-price">tola</a> and <a href="/10-gram-gold-price">10 gram</a> price pages.</p>
<h2>Is 916 the same as 22K?</h2>
<p>Yes. 916 means 91.6% gold — 22 karat. In India, BIS hallmarking requires purity grades such as 22K916 to be stamped along with a unique HUID code.</p>
<h2>How to tell them apart</h2>
<p>24K is a deeper, more orange-yellow and noticeably softer. Check the stamp, and for bullion, buy from known refiners.</p>
HTML, 'faqs'=>[
  ['What does 22K916 mean?', 'It is a hallmark meaning 22 karat gold of 91.6% purity.'],
  ['Is 22K gold good for investment?', 'It contains real gold, but jewelry carries making charges. For investment, bullion bars and coins are more efficient.'],
]],

'gold-hallmarks'=>['body'=><<<'HTML'
<h2>Hallmarks around the world</h2>
<ul>
<li><strong>United Kingdom:</strong> independent assay offices stamp a sponsor’s mark, a fineness number and an assay office mark (for example a leopard’s head for London).</li>
<li><strong>India:</strong> BIS hallmarking with the purity grade (e.g. 22K916) and a six-character HUID code.</li>
<li><strong>Switzerland and Europe:</strong> fineness numbers with official control marks in some countries.</li>
<li><strong>United States:</strong> no government assay; makers stamp karat or fineness and must add a registered trademark when they do.</li>
</ul>
<h2>Where to find the stamp</h2>
<p>Rings: inside the band. Chains and bracelets: on or near the clasp, or on a small tag. Earrings: on the post or back. Pendants: on the bail. Use a 10× jeweler’s loupe to read small marks.</p>
<h2>No stamp?</h2>
<p>Older, handmade or resized pieces may have lost their stamp. A jeweler can test the metal with acid or XRF in minutes.</p>
HTML, 'faqs'=>[
  ['What does 999 mean on gold?', '99.9% pure gold — 24 karat.'],
  ['What does 417 mean on gold?', '41.7% gold — 10 karat.'],
]],

'what-is-21k-gold'=>['body'=><<<'HTML'
<h2>21K gold colour</h2>
<p>21K has a deep, rich yellow — warmer than 18K and only slightly paler than 22K. It is often polished to a high shine or given detailed hand-finishing in Gulf-style jewelry.</p>
<h2>Is 21K gold durable?</h2>
<p>More durable than 22K and 24K, less than 18K and 14K. It suits bangles, chains and necklaces well; for rings worn daily it can show scratches over time.</p>
<h2>Buying 21K jewelry</h2>
<p>Jewelers usually price 21K by the gram at a rate close to the gold value plus a making charge. Compare the per-gram rate against today’s {value:1g:21k} and ask how much of the price is the making charge — that part is lost when you sell.</p>
<h2>Selling 21K gold</h2>
<p>Multiply the weight by {value:1g:21k} for melt value, and expect offers somewhat below that. See <a href="/guides/how-to-calculate-gold-value">how to calculate gold value</a>.</p>
HTML, 'faqs'=>[
  ['What does 875 mean on gold?', '87.5% gold — 21 karat.'],
  ['Is 21K better than 18K?', '21K contains more gold and is worth more per gram; 18K is harder.'],
]],

'gold-plated-vs-gold-filled-vs-solid-gold'=>['body'=><<<'HTML'
<h2>Caring for each type</h2>
<ul>
<li><strong>Gold plated:</strong> avoid water, perfume and lotions; store dry; expect wear.</li>
<li><strong>Gold filled:</strong> can be worn daily and cleaned with mild soap and water.</li>
<li><strong>Solid gold:</strong> durable; clean with warm soapy water and a soft brush.</li>
</ul>
<h2>Which should you buy?</h2>
<p>Fashion pieces you’ll wear briefly: plated is fine. Everyday jewelry on a budget: gold filled lasts much longer. Pieces to keep for life or pass on: solid gold. Investment: none of these — buy <a href="/gold-bars">24K bullion</a>, which carries no jewelry markup.</p>
<h2>Selling gold filled scrap</h2>
<p>Some refiners and scrap buyers accept gold-filled items in bulk and pay for their small gold content. Individual pieces are usually worth little.</p>
HTML, 'faqs'=>[
  ['Does gold filled tarnish?', 'Good-quality gold filled jewelry resists tarnish for years with normal care.'],
  ['Is vermeil better than gold plated?', 'Vermeil has a thicker gold layer over sterling silver, so it usually lasts longer than basic gold plating.'],
]],

'gold-bar-dimensions'=>['body'=><<<'HTML'
<h2>Minted vs cast: same weight, different shape</h2>
<p>A minted 1 oz bar is long, thin and flat; a cast 1 oz bar is shorter and thicker. A minted 100 g bar is about the size of a credit card cut in half; a cast 100 g bar is a chunky rectangle. Always compare against the specification for the exact product you have.</p>
<h2>Gold volume calculator</h2>
<p>Volume = weight ÷ 19.32. So 1 kg of gold occupies about 51.8 cm³; a 1 oz bar about 1.6 cm³. A fake made from a lighter metal must take up more space to reach the same weight — which is why measurement works.</p>
<h2>Will a gold bar fit in my safe?</h2>
<p>Easily. Even a 1 kg bar is smaller than a smartphone. Most home safes can hold many kilos of gold; check the safe’s weight rating and floor anchoring rather than its interior space.</p>
<h2>Weight vs size comparison</h2>
<p>For weights in grams, ounces and pounds, see <a href="/guides/gold-bar-weight-and-sizes">gold bar weight and sizes</a>.</p>
HTML, 'faqs'=>[
  ['How thick is a 1 oz gold bar?', 'A minted 1 oz bar is typically under 2 mm thick; cast versions are thicker.'],
  ['How much space does a kilo of gold take up?', 'About 52 cubic centimetres.'],
]],
];
