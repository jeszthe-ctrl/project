<?php
/* Extra sections and FAQs added to the price pages (appended to their text). */
if(!defined('GB_ROOT')){ http_response_code(404); exit; }

return [
'gold-price'=>['body'=><<<'HTML'
<h2>Gold price per ounce, gram and kilo: quick reference</h2>
<table><thead><tr><th>Amount</th><th>24K gold</th><th>Silver</th></tr></thead><tbody>
<tr><td>1 gram</td><td>{value:1g}</td><td>{silver_g}</td></tr>
<tr><td>1 troy ounce</td><td>{gold_oz}</td><td>{silver_oz}</td></tr>
<tr><td>1 kilogram</td><td>{value:1kg}</td><td>—</td></tr>
</tbody></table>
<p>The gold/silver ratio today is {gs_ratio}: one ounce of gold buys about {gs_ratio} ounces of silver. Read <a href="/guides/gold-vs-silver">gold vs silver</a>.</p>
<h2>Bid, ask and spot</h2>
<p>Markets quote two prices: the <strong>bid</strong> (what buyers will pay) and the <strong>ask</strong> (what sellers want). The spot price shown here sits between them. Dealers then sell physical bullion above spot and buy it back below or close to spot.</p>
<h2>How often does the gold price change?</h2>
<p>Constantly while markets are open — almost 24 hours a day on weekdays. Our page refreshes from live market data through the day, and product prices on our site follow it until you place an order, when your price is locked.</p>
HTML, 'faqs'=>[
  ['What is the gold price per gram today?', '{gold_g} per gram of 24K gold.'],
  ['Is the gold price the same everywhere in the US?', 'Yes. The spot price is a global wholesale price; what differs between sellers is the premium they charge.'],
]],

'24k-gold-price'=>['body'=><<<'HTML'
<h2>24K gold price by weight</h2>
<table><thead><tr><th>Weight</th><th>24K value today</th></tr></thead><tbody>
<tr><td>1 gram</td><td>{value:1g}</td></tr><tr><td>5 grams</td><td>{value:5g}</td></tr><tr><td>10 grams</td><td>{value:10g}</td></tr><tr><td>1 tola (11.66 g)</td><td>{value:1tola}</td></tr><tr><td>1 troy ounce</td><td>{value:1oz}</td></tr><tr><td>100 grams</td><td>{value:100g}</td></tr><tr><td>1 kilogram</td><td>{value:1kg}</td></tr>
</tbody></table>
<h2>24 carat gold cost: bullion vs jewelry</h2>
<p>The cost of 24 carat gold depends on its form. Bullion bars sell for a modest premium over the price above; small bars and coins have higher premiums; 24K jewelry adds making charges that can be substantial. For the most gold per dollar, compare bullion prices per gram against the live 24K rate.</p>
<h2>999 vs 9999 gold price</h2>
<p>Both are 24K. The price difference is tiny — .9999 contains 0.09% more gold than .999 — so a 1 oz .999 bar is worth about {value:0.999oz} versus {value:0.9999oz} for .9999.</p>
HTML, 'faqs'=>[
  ['What is the price of 24 carat gold per ounce?', '{gold_oz} per troy ounce today.'],
  ['How much is 24K gold per kilo?', '{value:1kg} per kilogram today.'],
]],

'22k-gold-price'=>['body'=><<<'HTML'
<h2>22K gold rate by weight</h2>
<table><thead><tr><th>Weight</th><th>22K value today</th></tr></thead><tbody>
<tr><td>1 gram</td><td>{value:1g:22k}</td></tr><tr><td>8 grams (1 sovereign weight)</td><td>{value:8g:22k}</td></tr><tr><td>10 grams</td><td>{value:10g:22k}</td></tr><tr><td>1 tola</td><td>{value:1tola:22k}</td></tr><tr><td>1 troy ounce</td><td>{value:1oz:22k}</td></tr><tr><td>50 grams</td><td>{value:50g:22k}</td></tr><tr><td>100 grams</td><td>{value:100g:22k}</td></tr>
</tbody></table>
<h2>Today’s 22 carat gold price vs Indian and Gulf rates</h2>
<p>Gold rates quoted in India, Pakistan or the UAE are in local currency and often include local import duties and taxes, so they won’t exactly match the US dollar rate here. The underlying gold value is the same: converting a local rate to dollars and adjusting for taxes should land close to {value:1g:22k} per gram.</p>
<h2>22K gold cost when buying jewelry</h2>
<p>Expect to pay the 22K rate plus a making charge, which varies with design complexity. Ask the jeweler to show the gold weight, the rate per gram and the making charge separately, so you can compare stores fairly. See <a href="/guides/22k-vs-24k-gold">22K vs 24K gold</a>.</p>
HTML, 'faqs'=>[
  ['What is the 22K gold rate today in the USA?', '{value:1g:22k} per gram and {value:1tola:22k} per tola, in US dollars.'],
  ['Why is 22K gold cheaper in the US than in India?', 'Indian prices include import duty and taxes; the underlying gold value is similar.'],
]],

'21k-gold-price'=>['body'=><<<'HTML'
<h2>21K gold price by weight</h2>
<table><thead><tr><th>Weight</th><th>21K value today</th></tr></thead><tbody>
<tr><td>1 gram</td><td>{value:1g:21k}</td></tr><tr><td>5 grams</td><td>{value:5g:21k}</td></tr><tr><td>10 grams</td><td>{value:10g:21k}</td></tr><tr><td>20 grams</td><td>{value:20g:21k}</td></tr><tr><td>1 tola</td><td>{value:1tola:21k}</td></tr><tr><td>1 troy ounce</td><td>{value:1oz:21k}</td></tr><tr><td>50 grams</td><td>{value:50g:21k}</td></tr><tr><td>100 grams</td><td>{value:100g:21k}</td></tr>
</tbody></table>
<h2>How to calculate the 21K gold price</h2>
<p>21K price per gram = (spot price per ounce ÷ 31.1035) × 0.875. With spot at {gold_oz}, that’s {gold_g} × 0.875 = <strong>{value:1g:21k}</strong>.</p>
<h2>Buying and selling 21K gold in the US</h2>
<p>Many Middle Eastern jewelers in US cities sell 21K by weight. When buying, ask for the rate per gram and the making charge separately. When selling, compare offers against the melt value above. Read <a href="/guides/what-is-21k-gold">what is 21K gold?</a></p>
HTML, 'faqs'=>[
  ['How much is 10 grams of 21K gold?', '{value:10g:21k} today.'],
  ['What is the 21K gold price per tola?', '{value:1tola:21k} today.'],
]],

'18k-gold-price'=>['body'=><<<'HTML'
<h2>18K gold value by weight</h2>
<table><thead><tr><th>Weight</th><th>18K value today</th></tr></thead><tbody>
<tr><td>1 gram</td><td>{value:1g:18k}</td></tr><tr><td>1 pennyweight</td><td>{value:1dwt:18k}</td></tr><tr><td>5 grams</td><td>{value:5g:18k}</td></tr><tr><td>10 grams</td><td>{value:10g:18k}</td></tr><tr><td>20 grams</td><td>{value:20g:18k}</td></tr><tr><td>1 troy ounce</td><td>{value:1oz:18k}</td></tr>
</tbody></table>
<h2>18K gold watches and luxury jewelry</h2>
<p>Most solid-gold luxury watches and much European fine jewelry are 18K. A solid 18K watch case and bracelet can contain 50–150 grams of 18K gold — {value:50g:18k} to {value:150g:18k} in gold today — although well-known watches are usually worth far more as watches than as gold.</p>
<h2>Today’s 18K gold rate: buying vs selling</h2>
<p>The rate above is melt value. When buying 18K jewelry you also pay for design, labour and brand; when selling as scrap expect a percentage of melt value. Compare <a href="/guides/14k-vs-18k-gold">14K vs 18K</a>.</p>
HTML, 'faqs'=>[
  ['How much is 18K gold worth per pennyweight?', '{value:1dwt:18k} per dwt today.'],
  ['What does 750 mean on gold?', 'It means 75% gold — 18 karat.'],
]],

'14k-gold-price'=>['body'=><<<'HTML'
<h2>14K gold price chart by weight</h2>
<table><thead><tr><th>Weight</th><th>14K value today</th><th>Typical item</th></tr></thead><tbody>
<tr><td>1 gram</td><td>{value:1g:14k}</td><td>Small earrings</td></tr>
<tr><td>2 grams</td><td>{value:2g:14k}</td><td>Thin band, small pendant</td></tr>
<tr><td>3 grams</td><td>{value:3g:14k}</td><td>Women’s wedding band</td></tr>
<tr><td>5 grams</td><td>{value:5g:14k}</td><td>Men’s band, thin chain</td></tr>
<tr><td>10 grams</td><td>{value:10g:14k}</td><td>Medium chain, signet ring</td></tr>
<tr><td>20 grams</td><td>{value:20g:14k}</td><td>Heavy bracelet or chain</td></tr>
<tr><td>1 troy ounce</td><td>{value:1oz:14k}</td><td>—</td></tr>
</tbody></table>
<h2>14K spot price vs what buyers pay</h2>
<p>“14K spot price” means the melt value of 14K gold at today’s spot — {value:1g:14k} per gram. Scrap buyers typically pay 70–90% of that; pawn shops often less. Always ask for the offer as a percentage of melt so you can compare.</p>
<h2>Why 14K is stamped 585</h2>
<p>14/24 is exactly 58.33%, but many makers alloy 14K at 58.5% to make sure every piece meets the legal minimum after small production variations. That’s why most 14K is stamped <strong>585</strong>. We value 14K at 58.3%.</p>
<h2>How much is a 14K gold ring or chain worth?</h2>
<p>See our dedicated guides: <a href="/guides/how-much-is-a-14k-gold-ring-worth">how much is a 14K gold ring worth?</a> and <a href="/guides/how-to-calculate-gold-value">how to calculate gold value</a>.</p>
HTML, 'faqs'=>[
  ['How much is 14K gold per gram at a pawn shop?', 'Usually less than melt value ({value:1g:14k} today). Compare offers from bullion dealers and gold buyers.'],
  ['How much is 14 karat gold worth per ounce?', '{value:1oz:14k} per troy ounce today.'],
]],

'10k-gold-price'=>['body'=><<<'HTML'
<h2>10K gold value by weight</h2>
<table><thead><tr><th>Weight</th><th>10K value today</th><th>Typical item</th></tr></thead><tbody>
<tr><td>1 gram</td><td>{value:1g:10k}</td><td>Small charm</td></tr>
<tr><td>1 pennyweight</td><td>{value:1dwt:10k}</td><td>—</td></tr>
<tr><td>5 grams</td><td>{value:5g:10k}</td><td>Thin chain</td></tr>
<tr><td>10 grams</td><td>{value:10g:10k}</td><td>Class ring</td></tr>
<tr><td>30 grams</td><td>{value:30g:10k}</td><td>Rope or Cuban chain</td></tr>
<tr><td>1 troy ounce</td><td>{value:1oz:10k}</td><td>—</td></tr>
</tbody></table>
<h2>10K gold chains</h2>
<p>Cuban, rope and Franco chains are often 10K because the extra hardness holds up to daily wear, and heavy chains stay affordable. A 50 g 10K chain contains {value:50g:10k} of gold today.</p>
<h2>Is 10K gold worth selling?</h2>
<p>Yes — it has real gold value. Weigh it, multiply by {value:1g:10k} per gram, and compare buyer offers against that figure. See <a href="/guides/10k-vs-14k-gold">10K vs 14K gold</a>.</p>
HTML, 'faqs'=>[
  ['How much is a 10K gold chain worth?', 'Weigh it and multiply by {value:1g:10k} per gram. A 20 g chain contains {value:20g:10k} of gold.'],
  ['How much is 10K gold per pennyweight?', '{value:1dwt:10k} per dwt today.'],
]],

'gold-price-per-gram'=>['body'=><<<'HTML'
<h2>Price of gold per gram vs per ounce</h2>
<p>A troy ounce is 31.1035 grams, so the price per gram is always the ounce price divided by 31.1035. Today: {gold_oz} per ounce = {gold_g} per gram. Small changes in the ounce price move the gram price by about 3% as much.</p>
<h2>Why gram prices from stores look higher</h2>
<p>Retail prices per gram for jewelry, small bars and coins include making, packaging and dealer margin. A 1 gram gold bar costs more than {gold_g}; a 1 kilo bar costs only slightly more per gram than spot. See <a href="/guides/buy-gold-at-spot-price">can you buy gold at spot price?</a></p>
<h2>Grams of gold by karat at a glance</h2>
<table><thead><tr><th>Karat</th><th>Per gram today</th><th>Per 10 grams</th></tr></thead><tbody>
<tr><td>24K</td><td>{value:1g:24k}</td><td>{value:10g:24k}</td></tr><tr><td>22K</td><td>{value:1g:22k}</td><td>{value:10g:22k}</td></tr><tr><td>21K</td><td>{value:1g:21k}</td><td>{value:10g:21k}</td></tr><tr><td>18K</td><td>{value:1g:18k}</td><td>{value:10g:18k}</td></tr><tr><td>14K</td><td>{value:1g:14k}</td><td>{value:10g:14k}</td></tr><tr><td>10K</td><td>{value:1g:10k}</td><td>{value:10g:10k}</td></tr>
</tbody></table>
HTML, 'faqs'=>[
  ['How much is 2 grams of gold worth?', '{value:2g:24k} for 24K gold and {value:2g:14k} for 14K gold today.'],
  ['How much is half a gram of gold worth?', '{value:0.5g:24k} for 24K gold today.'],
]],

'10-gram-gold-price'=>['body'=><<<'HTML'
<h2>10 grams of gold in ounces and tolas</h2>
<p>10 grams = 0.3215 troy ounces = 0.857 tola. It’s a standard unit for quoting gold rates in India and the Gulf, and a popular bar size worldwide.</p>
<h2>10 gram gold bar vs 10 grams of jewelry</h2>
<p>A <a href="/10-gram-gold-bars">10 gram gold bar</a> is 24K and sells close to its gold value plus a modest premium. Ten grams of 22K jewelry contains {value:10g:22k} of gold but usually costs more because of making charges, and sells back for less.</p>
<h2>10g gold price history</h2>
<p>Because 10 grams is about a third of an ounce, its price moves with the ounce price: every $100 move in gold changes the price of 10 grams by about $32. See <a href="/guides/gold-price-history">gold price history</a>.</p>
HTML, 'faqs'=>[
  ['How much is 10 grams of 18K gold?', '{value:10g:18k} today.'],
  ['How much is 10 grams of 14K gold?', '{value:10g:14k} today.'],
]],

'gold-price-per-kilo'=>['body'=><<<'HTML'
<h2>1 kg gold price by karat</h2>
<ul><li>24K: {value:1kg:24k}</li><li>22K: {value:1kg:22k}</li><li>18K: {value:1kg:18k}</li><li>14K: {value:1kg:14k}</li></ul>
<h2>How heavy is a kilo of gold?</h2>
<p>A kilogram is 2.2 lb — about the weight of a large bottle of water — but in gold it’s a bar roughly the size of a smartphone. See <a href="/guides/gold-bar-dimensions">gold bar dimensions</a>.</p>
<h2>Buying a kilo of gold</h2>
<p>A <a href="/1-kilo-gold-bars">1 kilo gold bar</a> carries the lowest premium per ounce of any retail product. It must be sold as one piece, so many buyers combine kilo bars with smaller bars or coins. In the US, dealers may have to report sales of kilo bars to the IRS when you sell — see <a href="/guides/how-to-sell-gold-bars">how to sell gold bars</a>.</p>
HTML, 'faqs'=>[
  ['How much is half a kilo of gold worth?', '{value:500g} today.'],
  ['How much is 5 kg of gold worth?', '{value:5kg} today.'],
]],

'gold-price-per-pound'=>['body'=><<<'HTML'
<h2>How much is a pound of 14K or 10K gold worth?</h2>
<ul><li>1 lb of 24K: {value:1lb}</li><li>1 lb of 22K: {value:1lb:22k}</li><li>1 lb of 18K: {value:1lb:18k}</li><li>1 lb of 14K: {value:1lb:14k}</li><li>1 lb of 10K: {value:1lb:10k}</li></ul>
<h2>How much is a pound of gold in ounces and grams?</h2>
<p>1 lb = 453.59 g = 14.5833 troy ounces = 16 regular ounces. Be careful: if someone quotes “16 ounces” of gold, they may be confusing regular and troy ounces. See <a href="/guides/troy-ounce-vs-ounce">troy ounce vs ounce</a>.</p>
<h2>What does a pound of gold look like?</h2>
<p>About 23.5 cm³ — smaller than a deck of cards. A 1 kilo bar is about 2.2 lb, and a 400 oz Good Delivery bar about 27.4 lb.</p>
HTML, 'faqs'=>[
  ['How much is a pound of 14K gold worth?', '{value:1lb:14k} today.'],
  ['How much is 1 lb of gold worth in 2026?', '{value:1lb} at today’s spot price of {gold_oz}.'],
]],

'tola-gold-price'=>['body'=><<<'HTML'
<h2>Tola to grams and ounces</h2>
<table><thead><tr><th>Tola</th><th>Grams</th><th>Troy oz</th><th>24K value</th></tr></thead><tbody>
<tr><td>1</td><td>11.66</td><td>0.375</td><td>{value:1tola}</td></tr><tr><td>2</td><td>23.33</td><td>0.75</td><td>{value:2tola}</td></tr><tr><td>5</td><td>58.32</td><td>1.875</td><td>{value:5tola}</td></tr><tr><td>10</td><td>116.64</td><td>3.75</td><td>{value:10tola}</td></tr>
</tbody></table>
<h2>Tola rates in Pakistan, India and the Gulf</h2>
<p>Local tola rates are quoted in rupees or dirhams and include local premiums and taxes, so they differ from the US dollar rate here. The gold content of a tola is the same everywhere: 11.6638 g. For gold bar shapes and weights, see <a href="/guides/gold-biscuit">what is a gold biscuit?</a></p>
<h2>Masha and ratti</h2>
<p>Traditional South Asian jewelers also use smaller units: 1 tola = 12 masha, and 1 masha = 8 ratti. So 1 masha is about 0.972 g ({value:0.972g:22k} in 22K).</p>
HTML, 'faqs'=>[
  ['How much is 5 tola of gold worth?', '{value:5tola} for 24K and {value:5tola:22k} for 22K today.'],
  ['How many masha are in a tola?', '12 masha.'],
]],

'white-gold-price'=>['body'=><<<'HTML'
<h2>White gold value by weight</h2>
<table><thead><tr><th>Weight</th><th>10K</th><th>14K</th><th>18K</th></tr></thead><tbody>
<tr><td>1 g</td><td>{value:1g:10k}</td><td>{value:1g:14k}</td><td>{value:1g:18k}</td></tr>
<tr><td>3 g</td><td>{value:3g:10k}</td><td>{value:3g:14k}</td><td>{value:3g:18k}</td></tr>
<tr><td>5 g</td><td>{value:5g:10k}</td><td>{value:5g:14k}</td><td>{value:5g:18k}</td></tr>
<tr><td>10 g</td><td>{value:10g:10k}</td><td>{value:10g:14k}</td><td>{value:10g:18k}</td></tr>
</tbody></table>
<h2>White gold vs platinum value</h2>
<p>Platinum looks similar to white gold but is a different metal, usually 95% pure, priced on the platinum spot price. It is stamped “PT950” or “PLAT”. Don’t value platinum as white gold or vice versa.</p>
<h2>Selling white gold</h2>
<p>Buyers test the karat, weigh the gold and pay a percentage of melt value. Rhodium plating doesn’t add value. See <a href="/guides/where-to-sell-gold-and-silver">where to sell gold</a>.</p>
HTML, 'faqs'=>[
  ['How much is 18K white gold worth per gram?', '{value:1g:18k} per gram today.'],
  ['Is white gold the same as platinum?', 'No. White gold is a gold alloy; platinum is a separate, denser metal with its own price.'],
]],

'gold-coin-prices'=>['body'=><<<'HTML'
<h2>Bullion coins vs numismatic coins</h2>
<p><strong>Bullion coins</strong> trade on their gold content and sell close to melt value plus a modest premium. <strong>Numismatic (collectible) coins</strong> are valued on rarity, date, mint mark and condition, and can be worth many times their gold. Pre-1933 US gold coins sit in between: common dates trade near melt, rare ones far above it.</p>
<h2>How dealers price coins</h2>
<p>Dealers quote coin prices as spot plus a premium per coin. Premiums depend on the coin, its size and demand: popular 1 oz coins typically have lower premiums than fractional coins or less common issues.</p>
<h2>Gold coin values by size today</h2>
<ul><li>1 oz: {value:1oz}</li><li>1/2 oz: {value:0.5oz}</li><li>1/4 oz: {value:0.25oz}</li><li>1/10 oz: {value:0.1oz}</li></ul>
<p>Compare coins in <a href="/guides/best-gold-coins-to-buy">best gold coins to buy</a> and <a href="/guides/american-gold-eagle-vs-buffalo">Gold Eagle vs Buffalo</a>.</p>
HTML, 'faqs'=>[
  ['How much is a 1/4 oz gold coin worth?', '{value:0.25oz} in gold at today’s price.'],
  ['How much is a gold sovereign worth?', 'A sovereign contains 0.2354 oz of gold, worth {value:0.2354oz} today.'],
]],

'ar/gold-price'=>['body'=><<<'HTML'
<h2>سعر الذهب حسب الوزن</h2>
<table><thead><tr><th>الوزن</th><th>عيار 24</th><th>عيار 21</th><th>عيار 18</th></tr></thead><tbody>
<tr><td>1 غرام</td><td>{value:1g:24k}</td><td>{value:1g:21k}</td><td>{value:1g:18k}</td></tr>
<tr><td>5 غرامات</td><td>{value:5g:24k}</td><td>{value:5g:21k}</td><td>{value:5g:18k}</td></tr>
<tr><td>10 غرامات</td><td>{value:10g:24k}</td><td>{value:10g:21k}</td><td>{value:10g:18k}</td></tr>
<tr><td>أونصة</td><td>{value:1oz:24k}</td><td>{value:1oz:21k}</td><td>{value:1oz:18k}</td></tr>
</tbody></table>
<h2>لماذا يختلف سعر الذهب في أمريكا عن الدول العربية؟</h2>
<p>سعر الذهب العالمي واحد، لكن الأسعار المحلية في مصر أو السعودية أو الإمارات تُحسب بالعملة المحلية وقد تتضمن ضرائب ورسوماً ومصنعية. لذلك يختلف السعر النهائي من بلد لآخر، بينما تبقى قيمة الذهب الخام مرتبطة بسعر الأونصة العالمي.</p>
<h2>نصائح لشراء الذهب في أمريكا</h2>
<ul>
<li>اطلب معرفة سعر الغرام وأجرة المصنعية بشكل منفصل.</li>
<li>تأكد من ختم العيار: 875 لعيار 21، و750 لعيار 18، و999 لعيار 24.</li>
<li>للادخار، السبائك عيار 24 أقل تكلفة من المشغولات.</li>
</ul>
HTML, 'faqs'=>[
  ['كم سعر 10 غرامات ذهب عيار 21 في أمريكا؟', '{value:10g:21k} دولار اليوم.'],
  ['ما معنى ختم 875 على الذهب؟', 'يعني أن نسبة الذهب 87.5٪، أي عيار 21.'],
]],
];
