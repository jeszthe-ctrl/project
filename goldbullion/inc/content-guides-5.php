<?php
/* Guides, part 5: prices and investing, coins, buying and selling. */
if(!defined('GB_ROOT')){ http_response_code(404); exit; }

return [
[
  'slug'=>'gold-price-history', 'topic'=>'prices', 'date'=>'2026-10-31',
  'title'=>'Gold Price History: The Price of Gold Over the Years',
  'seo_title'=>'Gold Price History: Price of Gold by Year Since 1970',
  'seo_desc'=>'Gold price history from $35 an ounce in 1970 to {gold_oz} today: the major highs, lows and turning points, and what drove each one.',
  'summary'=>'The key moments in the price of gold since the end of the gold standard, and what drove them.',
  'body'=><<<'HTML'
<p class="lead"><strong>Gold has risen from $35 an ounce in 1970 to {gold_oz} today.</strong> The path has not been smooth: long rallies have been followed by long declines, and investors who bought at the 1980 or 2011 peaks waited many years to break even.</p>
<h2>Gold price milestones (USD per troy ounce, approximate)</h2>
<table><thead><tr><th>Year</th><th>Price</th><th>What happened</th></tr></thead><tbody>
<tr><td>1934–1971</td><td>$35</td><td>Price fixed by the US government under the gold standard</td></tr>
<tr><td>1971</td><td>~$40</td><td>President Nixon ends dollar convertibility into gold; the price starts to float</td></tr>
<tr><td>January 1980</td><td>~$850</td><td>Peak during high inflation, the oil crisis and the Soviet invasion of Afghanistan</td></tr>
<tr><td>1999</td><td>~$255</td><td>Two-decade low after central banks sold gold and the stock market boomed</td></tr>
<tr><td>2008</td><td>~$1,000</td><td>Crosses $1,000 for the first time as the financial crisis builds</td></tr>
<tr><td>2011</td><td>~$1,900</td><td>Record high after the crisis, money printing and the euro-zone debt crisis</td></tr>
<tr><td>2015</td><td>~$1,050</td><td>Multi-year low as the dollar strengthens and interest rates are set to rise</td></tr>
<tr><td>2020</td><td>~$2,070</td><td>New record during the COVID-19 pandemic</td></tr>
<tr><td>2024</td><td>~$2,700+</td><td>Repeated records driven by central bank buying and expected rate cuts</td></tr>
<tr><td>2025</td><td>$3,000 → $4,000</td><td>Passes $3,000 in March and $4,000 in October</td></tr>
<tr><td>Today</td><td>{gold_oz}</td><td>Live spot price</td></tr>
</tbody></table>
<h2>Lessons from gold’s history</h2>
<ul>
<li><strong>Gold protects purchasing power over long periods</strong>, but can lose value for years at a time.</li>
<li><strong>Big rallies tend to come in crises</strong> — inflation, banking stress, war, currency weakness.</li>
<li><strong>Timing is hard.</strong> Many buyers average in over time instead of trying to pick the bottom.</li>
<li><strong>Central banks matter.</strong> They were big sellers in the 1990s and big buyers in recent years.</li>
</ul>
<h2>Gold vs inflation</h2>
<p>$35 in 1970 is worth several hundred dollars in today’s money after inflation, while gold itself is now {gold_oz}. Over five decades gold has comfortably beaten inflation — though over shorter windows, such as 1980–2000, it fell well behind it.</p>
<p>What moves the price today? Read <a href="/guides/what-drives-the-gold-price">what drives the gold price</a>, and follow the live <a href="/gold-price">gold price today</a>.</p>
<p class="small muted">Historical figures are rounded approximations from widely reported market prices. Past performance is not a guide to future returns.</p>
HTML,
  'faqs'=>[
    ['What was the price of gold in 1970?', 'About $35 per ounce, the official price set under the gold standard.'],
    ['What is the highest gold price ever?', 'Gold has set repeated records in recent years. Today’s spot price is {gold_oz}.'],
  ],
],
[
  'slug'=>'what-drives-the-gold-price', 'topic'=>'prices', 'date'=>'2026-10-31',
  'title'=>'What Drives the Gold Price? 8 Factors That Move Gold',
  'seo_title'=>'What Drives the Price of Gold? 8 Key Factors Explained',
  'seo_desc'=>'Why does the gold price go up and down? Interest rates, the US dollar, inflation, central bank buying, ETF flows, crises, jewelry demand and mine supply explained.',
  'summary'=>'The eight forces that move the price of gold, from interest rates to central bank buying.',
  'body'=><<<'HTML'
<p class="lead"><strong>The gold price is driven mainly by real interest rates, the US dollar, investment and central bank demand, and fear.</strong> Jewelry demand and mine supply matter too, but they move slowly. Today gold trades at {gold_oz}.</p>
<h2>1. Real interest rates</h2>
<p>Gold pays no interest. When interest rates after inflation (“real rates”) are high, holding gold costs you the income you could earn elsewhere, so gold tends to struggle. When real rates fall, gold usually rises.</p>
<h2>2. The US dollar</h2>
<p>Gold is priced in dollars. A weaker dollar makes gold cheaper for buyers using other currencies, raising demand and the dollar price.</p>
<h2>3. Inflation and currency debasement</h2>
<p>Gold can’t be printed. When people worry about inflation or government debt eroding paper money, they buy gold to preserve purchasing power.</p>
<h2>4. Central bank buying</h2>
<p>Central banks hold gold as a reserve asset. In recent years they have been buying at the fastest pace in decades, partly to reduce reliance on the US dollar.</p>
<h2>5. Investment flows</h2>
<p>Buying and selling by gold ETFs, futures traders and investors buying bars and coins can move the price quickly. See <a href="/guides/physical-gold-vs-gold-etfs">physical gold vs ETFs</a> and <a href="/guides/gold-futures-vs-spot-price">futures vs spot</a>.</p>
<h2>6. Crises and uncertainty</h2>
<p>Wars, banking failures, pandemics and political shocks drive “safe haven” buying.</p>
<h2>7. Jewelry demand</h2>
<p>Jewelry is the largest single use of gold, led by India and China. High prices tend to reduce jewelry buying, which helps balance the market.</p>
<h2>8. Mine supply and recycling</h2>
<p>Mine output grows slowly, and new mines take a decade or more to develop. When prices rise, more old jewelry is recycled, adding supply.</p>
<h2>What it means for buyers</h2>
<p>No one can reliably predict short-term moves. Many buyers spread purchases over time and hold gold as long-term insurance rather than a trade. See <a href="/guides/is-gold-a-good-investment">is gold a good investment?</a> and <a href="/guides/gold-price-history">gold price history</a>.</p>
HTML,
  'faqs'=>[
    ['Why does gold go up when interest rates fall?', 'Gold pays no interest, so lower rates reduce the cost of holding it compared with interest-paying assets.'],
    ['Does a strong dollar lower the gold price?', 'Usually. A stronger dollar makes gold more expensive in other currencies, which tends to reduce demand.'],
  ],
],
[
  'slug'=>'gold-vs-silver', 'topic'=>'prices', 'date'=>'2026-10-31',
  'title'=>'Gold vs Silver: Which Is the Better Buy? (Gold/Silver Ratio Explained)',
  'seo_title'=>'Gold vs Silver: Which to Buy? Gold/Silver Ratio Explained',
  'seo_desc'=>'Gold vs silver as an investment: volatility, storage, premiums, industrial demand and the gold/silver ratio (currently {gs_ratio}). Which suits you better?',
  'summary'=>'How gold and silver compare as investments, and what the gold/silver ratio tells you.',
  'body'=><<<'HTML'
<p class="lead"><strong>Gold is the steadier store of value; silver is cheaper, more volatile and more tied to industry.</strong> Today gold is {gold_oz} and silver {silver_oz} per ounce, so one ounce of gold buys about <strong>{gs_ratio} ounces of silver</strong> — the gold/silver ratio.</p>
<h2>Gold vs silver compared</h2>
<table><thead><tr><th></th><th>Gold</th><th>Silver</th></tr></thead><tbody>
<tr><th>Price per ounce today</th><td>{gold_oz}</td><td>{silver_oz}</td></tr>
<tr><th>Volatility</th><td>Lower</td><td>Higher — bigger swings both ways</td></tr>
<tr><th>Main demand</th><td>Jewelry, investment, central banks</td><td>Industry (electronics, solar), jewelry, investment</td></tr>
<tr><th>Storage</th><td>Very compact</td><td>Bulky — {gs_ratio}× the space for the same value</td></tr>
<tr><th>Premiums</th><td>Lower as a percentage</td><td>Higher as a percentage</td></tr>
<tr><th>Central bank reserves</th><td>Yes</td><td>No</td></tr>
</tbody></table>
<h2>The gold/silver ratio</h2>
<p>The ratio is the gold price divided by the silver price. In the 20th century it often ranged from about 40 to 80; it spiked above 100 in 2020. Some investors buy silver when the ratio is high (silver relatively cheap) and gold when it’s low. It’s a rule of thumb, not a guarantee.</p>
<h2>Who should buy gold</h2>
<p>Investors who want wealth preservation, low storage needs and a highly liquid asset. Start with <a href="/1-oz-gold-bars">1 oz gold bars</a> or <a href="/gold-coins">gold coins</a>.</p>
<h2>Who should buy silver</h2>
<p>Buyers on a smaller budget, or those willing to accept more volatility for potentially bigger moves. Remember silver takes up far more space for the same value.</p>
<h2>Why not both?</h2>
<p>Many investors hold mostly gold with a smaller silver position. Track both on our <a href="/gold-price">gold and silver price today</a> page.</p>
HTML,
  'faqs'=>[
    ['What is the gold/silver ratio today?', 'About {gs_ratio}: one ounce of gold ({gold_oz}) buys about {gs_ratio} ounces of silver ({silver_oz}).'],
    ['Is silver a better investment than gold?', 'Silver is more volatile and more industrial; gold is the steadier store of value. Which is better depends on your goals and risk tolerance.'],
  ],
],
[
  'slug'=>'is-gold-a-good-investment', 'topic'=>'prices', 'date'=>'2026-10-31',
  'title'=>'Is Gold a Good Investment? Pros, Cons and How Much to Own',
  'seo_title'=>'Is Gold a Good Investment in 2026? Pros, Cons & Risks',
  'seo_desc'=>'Is gold a good investment? The honest pros and cons of owning gold, how it behaves in a portfolio, the risks, and how investors decide how much to hold.',
  'summary'=>'An honest look at gold’s strengths and weaknesses as an investment.',
  'body'=><<<'HTML'
<p class="lead"><strong>Gold can be a good investment for protecting wealth and diversifying a portfolio, but it pays no income and can fall in value for years.</strong> Most investors treat it as insurance — a part of their savings, not all of it.</p>
<h2>The case for gold</h2>
<ul>
<li><strong>Long-term store of value:</strong> gold has preserved purchasing power over centuries. From $35 in 1970 it trades at {gold_oz} today.</li>
<li><strong>Diversification:</strong> gold often moves differently from stocks and bonds, and has tended to hold up in crises.</li>
<li><strong>No counterparty:</strong> physical gold isn’t anyone’s promise to pay.</li>
<li><strong>Liquidity:</strong> recognised bars and coins can be sold worldwide.</li>
<li><strong>Central bank demand</strong> has been strong in recent years.</li>
</ul>
<h2>The case against</h2>
<ul>
<li><strong>No income:</strong> no dividends or interest; returns come only from price changes.</li>
<li><strong>Long flat or falling periods:</strong> gold fell for most of 1980–2000 and roughly 2011–2015. See <a href="/guides/gold-price-history">gold price history</a>.</li>
<li><strong>Costs:</strong> dealer premiums, storage and insurance for physical gold.</li>
<li><strong>Taxes:</strong> in the US, physical gold is generally taxed as a collectible, at up to 28% on long-term gains.</li>
</ul>
<h2>How much gold do people own?</h2>
<p>There’s no single right answer. Many financial commentators suggest a modest allocation, often in the region of 5–15% of a portfolio, but your situation is unique — a qualified adviser can help.</p>
<h2>Physical gold or paper gold?</h2>
<p>Physical bars and coins give you direct ownership; ETFs and miners offer convenience and different risks. Compare them in <a href="/guides/physical-gold-vs-gold-etfs">physical gold vs gold ETFs</a>.</p>
<h2>If you decide to buy</h2>
<ol>
<li>Buy recognised products at a fair premium: <a href="/guides/best-gold-bars-to-buy">best gold bars</a>, <a href="/guides/best-gold-coins-to-buy">best gold coins</a>.</li>
<li>Consider buying in stages rather than all at once.</li>
<li>Store it safely: <a href="/guides/how-to-store-gold-bullion">how to store gold</a>.</li>
</ol>
<p class="small muted">General information only, not financial advice. Gold prices can fall as well as rise.</p>
HTML,
  'faqs'=>[
    ['Is gold a good long-term investment?', 'Over long periods gold has preserved purchasing power, but it can underperform for years and pays no income.'],
    ['What percentage of a portfolio should be in gold?', 'Commonly cited ranges are around 5–15%, but it depends on your circumstances; consider professional advice.'],
  ],
],
[
  'slug'=>'best-gold-coins-to-buy', 'topic'=>'coins', 'date'=>'2026-10-31',
  'title'=>'Best Gold Coins to Buy for Investment',
  'seo_title'=>'Best Gold Coins to Buy for Investment (1 oz & Fractional)',
  'seo_desc'=>'The best gold coins to buy: American Gold Eagle, Buffalo, Canadian Maple Leaf, Krugerrand, Philharmonic and Britannia compared on purity, premium and resale.',
  'summary'=>'The world’s best gold bullion coins compared on purity, cost and how easy they are to sell.',
  'body'=><<<'HTML'
<p class="lead"><strong>The best gold coins for investment are widely recognised government bullion coins: the American Gold Eagle, American Gold Buffalo, Canadian Gold Maple Leaf, South African Krugerrand, Austrian Philharmonic and British Britannia.</strong> Each 1 oz coin contains {value:1oz} of gold at today’s price.</p>
<h2>Top gold bullion coins compared</h2>
<table><thead><tr><th>Coin</th><th>Purity</th><th>First issued</th><th>Why buy it</th></tr></thead><tbody>
<tr><td>American Gold Eagle</td><td>22K (.9167)</td><td>1986</td><td>The best-known coin in the US; durable; IRA-eligible</td></tr>
<tr><td>American Gold Buffalo</td><td>24K (.9999)</td><td>2006</td><td>The US Mint’s pure gold coin; classic design</td></tr>
<tr><td>Canadian Gold Maple Leaf</td><td>24K (.9999)</td><td>1979</td><td>Advanced security features; often lower premium</td></tr>
<tr><td>South African Krugerrand</td><td>22K (.9167)</td><td>1967</td><td>The original modern bullion coin; recognised worldwide</td></tr>
<tr><td>Austrian Philharmonic</td><td>24K (.9999)</td><td>1989</td><td>Europe’s favourite; competitive premiums</td></tr>
<tr><td>British Gold Britannia</td><td>24K (.9999)</td><td>1987</td><td>Latent security image; strong UK and global demand</td></tr>
</tbody></table>
<h2>Which is best?</h2>
<ul>
<li><strong>For US buyers who want maximum recognition:</strong> American Gold Eagle.</li>
<li><strong>For pure 24K gold:</strong> Buffalo, Maple Leaf, Philharmonic or Britannia.</li>
<li><strong>For the lowest premium:</strong> usually the Krugerrand, Maple Leaf or Philharmonic, depending on supply.</li>
</ul>
<h2>Fractional coins</h2>
<p>Most of these coins come in 1/2, 1/4 and 1/10 oz sizes. They cost more per ounce but are easier to sell in pieces. See <a href="/guides/mini-gold-coins">mini gold coins</a>.</p>
<h2>Coins or bars?</h2>
<p>Coins cost a little more than <a href="/gold-bars">gold bars</a> but are easier to sell. Read <a href="/guides/gold-coins-vs-gold-bars">gold coins vs gold bars</a> and check live <a href="/gold-coin-prices">gold coin prices</a>.</p>
HTML,
  'faqs'=>[
    ['What is the best gold coin to buy?', 'For US buyers the American Gold Eagle is the most recognised; for pure gold, the Buffalo, Maple Leaf or Philharmonic.'],
    ['How much is a 1 oz gold coin worth?', '{value:1oz} in gold at today’s spot price, plus a dealer premium.'],
  ],
],
[
  'slug'=>'american-gold-eagle-vs-buffalo', 'topic'=>'coins', 'date'=>'2026-10-31',
  'title'=>'American Gold Eagle vs Gold Buffalo: Which Should You Buy?',
  'seo_title'=>'American Gold Eagle vs Gold Buffalo – Differences Explained',
  'seo_desc'=>'Gold Eagle vs Gold Buffalo: 22K vs 24K, weight, sizes, designs, premiums and resale. Both contain 1 oz of gold worth {value:1oz} today.',
  'summary'=>'The US Mint’s two gold bullion coins compared side by side.',
  'body'=><<<'HTML'
<p class="lead"><strong>Both coins contain exactly one troy ounce of gold (worth {value:1oz} today) and carry a $50 face value. The Gold Eagle is 22K and comes in four sizes; the Gold Buffalo is 24K pure gold and comes mainly in 1 oz.</strong></p>
<h2>Side-by-side comparison</h2>
<table><thead><tr><th></th><th>American Gold Eagle</th><th>American Gold Buffalo</th></tr></thead><tbody>
<tr><th>First issued</th><td>1986</td><td>2006</td></tr>
<tr><th>Purity</th><td>22K (91.67% gold, with silver and copper)</td><td>24K (.9999)</td></tr>
<tr><th>Gross weight (1 oz)</th><td>33.93 g</td><td>31.11 g</td></tr>
<tr><th>Gold content</th><td>1 troy oz</td><td>1 troy oz</td></tr>
<tr><th>Sizes</th><td>1, 1/2, 1/4, 1/10 oz</td><td>1 oz (fractional sizes in some years)</td></tr>
<tr><th>Design</th><td>Lady Liberty (Saint-Gaudens); eagle reverse redesigned in 2021</td><td>Native American head and American bison (from the 1913 nickel)</td></tr>
<tr><th>Durability</th><td>Harder, resists scratches</td><td>Softer, marks more easily</td></tr>
<tr><th>Premium</th><td>Usually a little higher</td><td>Similar or slightly lower</td></tr>
</tbody></table>
<h2>Is the Buffalo worth more because it’s 24K?</h2>
<p>No. Both contain the same one ounce of gold. The Eagle simply has extra copper and silver to make it tougher, which is why it weighs more.</p>
<h2>Which is easier to sell?</h2>
<p>Both are among the easiest coins to sell in the US. The Eagle is the most recognised and comes in fractional sizes, which helps if you want to sell in small pieces.</p>
<h2>Our take</h2>
<p>Choose the <strong>Eagle</strong> for flexibility and durability, or the <strong>Buffalo</strong> if you prefer pure 24K gold and the classic design. See <a href="/guides/best-gold-coins-to-buy">best gold coins to buy</a> and <a href="/gold-coins">shop gold coins</a>.</p>
HTML,
  'faqs'=>[
    ['Is a Gold Eagle pure gold?', 'It is 22K (91.67% gold), but contains a full troy ounce of pure gold.'],
    ['Are Gold Buffalos better than Gold Eagles?', 'Neither is better in gold value; the Buffalo is 24K and the Eagle is more durable and comes in more sizes.'],
  ],
],
[
  'slug'=>'how-much-gold-is-in-the-world', 'topic'=>'basics', 'date'=>'2026-10-31',
  'title'=>'How Much Gold Is There in the World — and What Is It Worth?',
  'seo_title'=>'How Much Gold Is in the World? Total Gold & Its Value Today',
  'seo_desc'=>'All the gold ever mined is around 216,000 tonnes — a cube about 22 m wide, worth roughly {value:216000t} today. Who owns it, how much is left, and Fort Knox.',
  'summary'=>'How much gold has ever been mined, who owns it, and what it would all be worth at today’s price.',
  'body'=><<<'HTML'
<p class="lead"><strong>Around 216,000 tonnes of gold had been mined in all of history by the end of 2023, according to the World Gold Council.</strong> Gold is so dense that all of it would fit in a cube roughly 22 metres on each side — and at today’s price it would be worth about <strong>{value:216000t}</strong>.</p>
<h2>What a tonne of gold is worth</h2>
<p>A metric tonne is 32,150.7 troy ounces, worth <strong>{value:1t}</strong> at today’s spot price of {gold_oz}. See <a href="/gold-price-per-kilo">gold price per kilo</a>.</p>
<h2>Who owns the world’s gold?</h2>
<p>Approximate shares of all above-ground gold:</p>
<ul>
<li><strong>Jewelry:</strong> just under half.</li>
<li><strong>Private investment</strong> (bars, coins and gold-backed ETFs): around a fifth.</li>
<li><strong>Central banks:</strong> around a sixth.</li>
<li><strong>Industry and other uses</strong> (electronics, dentistry): the rest.</li>
</ul>
<h2>Largest official gold holdings (approximate)</h2>
<table><thead><tr><th>Holder</th><th>Tonnes</th><th>Value today</th></tr></thead><tbody>
<tr><td>United States</td><td>~8,133</td><td>{value:8133t}</td></tr>
<tr><td>Germany</td><td>~3,350</td><td>{value:3350t}</td></tr>
<tr><td>International Monetary Fund</td><td>~2,814</td><td>{value:2814t}</td></tr>
<tr><td>Italy</td><td>~2,452</td><td>{value:2452t}</td></tr>
<tr><td>France</td><td>~2,437</td><td>{value:2437t}</td></tr>
</tbody></table>
<h2>How much gold is in Fort Knox?</h2>
<p>The US Bullion Depository at Fort Knox holds about 147 million troy ounces — roughly 4,580 tonnes — or about {value:147000000oz} at today’s price, mostly in 400 oz <a href="/guides/400-oz-gold-bar">Good Delivery-style bars</a>.</p>
<h2>How much gold is left to mine?</h2>
<p>Estimated known underground reserves are around 50,000–60,000 tonnes. Global mine output is roughly 3,500 tonnes a year, so annual supply grows the above-ground stock by only about 1.5–2% — one reason gold holds its value.</p>
HTML,
  'faqs'=>[
    ['How much gold has ever been mined?', 'Around 216,000 tonnes by the end of 2023, according to the World Gold Council.'],
    ['How much is a ton of gold worth?', '{value:1t} per metric tonne at today’s price.'],
  ],
],
[
  'slug'=>'how-to-sell-gold-bars', 'topic'=>'selling', 'date'=>'2026-10-31',
  'title'=>'How to Sell Gold Bars and Coins for the Best Price',
  'seo_title'=>'How to Sell Gold Bars & Coins for the Best Price',
  'seo_desc'=>'How to sell gold bars and coins: check the spot price, compare buy-back offers, keep assay cards sealed, avoid low-ball buyers and understand IRS reporting.',
  'summary'=>'A step-by-step guide to selling bullion bars and coins at a fair price.',
  'body'=><<<'HTML'
<p class="lead"><strong>To sell gold bars or coins for the best price, know the spot price, get several buy-back quotes expressed against spot, and sell recognised products in their original packaging.</strong> Today gold is {gold_oz} per ounce.</p>
<h2>Step 1: Know what you have</h2>
<p>List each item’s weight, purity, maker and condition. A <a href="/1-oz-gold-bars">1 oz bar</a> contains {value:1oz} of gold; a <a href="/1-kilo-gold-bars">kilo bar</a> {value:1kg}. See <a href="/guides/how-much-is-a-gold-bar-worth">how much is a gold bar worth</a>.</p>
<h2>Step 2: Check the spot price</h2>
<p>Buy-back offers are based on live spot. Watch our <a href="/gold-price">gold price today</a> page when you request quotes.</p>
<h2>Step 3: Get quotes as a percentage of spot</h2>
<p>Ask each buyer: “What do you pay as a percentage of spot (or dollars over/under spot) for this exact product?” This lets you compare like for like. Recognised bullion usually sells close to spot; jewelry and damaged items sell for less.</p>
<h2>Step 4: Choose your buyer</h2>
<p>Local coin shops pay on the spot; online dealers can be competitive on larger amounts but involve insured shipping and waiting for payment. Compare options in <a href="/guides/where-to-sell-gold-and-silver">where to sell gold and silver</a>.</p>
<h2>Step 5: Protect yourself</h2>
<ul>
<li>Keep sealed assay cards sealed — they prove the bar hasn’t been tampered with. See <a href="/guides/gold-assay-cards-and-certificates">assay cards explained</a>.</li>
<li>Get the price locked in writing before shipping.</li>
<li>Use insured, tracked shipping with signature.</li>
<li>Be wary of unsolicited buyers and anyone pressuring you to sell quickly.</li>
</ul>
<h2>Taxes and reporting</h2>
<p>Gains on physical gold are generally taxable in the US, often at collectibles rates of up to 28% for items held over a year. Dealers must file Form 1099-B for some sales — for example gold bars totalling a kilo or more, or 25 or more 1 oz Krugerrands, Maple Leafs or Mexican Onzas in one transaction. Keep your purchase receipts and consult a tax professional.</p>
HTML,
  'faqs'=>[
    ['Where can I sell gold bars?', 'Local coin and bullion dealers and established online bullion dealers. Compare offers as a percentage of the spot price.'],
    ['Do I have to report selling gold?', 'Gains are generally taxable, and dealers must report some sales on Form 1099-B. Ask a tax professional about your situation.'],
  ],
],
[
  'slug'=>'gold-assay-cards-and-certificates', 'topic'=>'safety', 'date'=>'2026-10-31',
  'title'=>'Gold Assay Cards and Certificates: What They Are and Why They Matter',
  'seo_title'=>'Gold Bar Assay Card & Certificate Explained – Keep It Sealed?',
  'seo_desc'=>'What an assay card is, what the certificate shows, why you should keep gold bars sealed, and what to do if a bar’s card is damaged or missing.',
  'summary'=>'What gold bar assay cards and certificates prove, and how they affect resale.',
  'body'=><<<'HTML'
<p class="lead"><strong>An assay card is the sealed, tamper-evident package many gold bars come in.</strong> It certifies the bar’s weight, purity and serial number, signed by the refiner’s assayer, and proves the bar hasn’t been swapped or tampered with since it left the refinery.</p>
<h2>What an assay certificate shows</h2>
<ul>
<li>Refiner or mint name and logo</li>
<li>Weight (e.g. 1 oz, 10 g, 100 g)</li>
<li>Fineness (usually 999.9)</li>
<li>Unique serial number, matching the number stamped on the bar</li>
<li>Assayer’s signature and, on many cards, security features such as holograms</li>
</ul>
<h2>Should you open the assay card?</h2>
<p><strong>No.</strong> Once the seal is broken, a buyer can no longer be sure the bar inside is the original. Opened bars are still gold, but may need testing and may sell for less.</p>
<h2>Modern security features</h2>
<ul>
<li><strong>PAMP CertiPAMP™ and Veriscan®</strong> — the bar’s surface acts like a fingerprint you can scan. See <a href="/guides/pamp-suisse-gold-bars">PAMP Suisse gold bars</a>.</li>
<li><strong>Valcambi CertiCard</strong> and <strong>Argor-Heraeus KineBar™</strong> — holographic and kinegram features.</li>
<li><strong>Perth Mint and Royal Canadian Mint</strong> — tamper-evident cards with security printing.</li>
</ul>
<h2>Cast bars without cards</h2>
<p>Larger cast bars (10 oz, kilo) are often sold loose, sometimes with a separate certificate. The serial number on the bar is its identity — keep any paperwork with it.</p>
<h2>Damaged card?</h2>
<p>A scuffed card isn’t a problem. A cracked or opened seal is. If you buy secondary-market bars, inspect the card closely and buy from reputable dealers. See <a href="/guides/how-to-tell-if-gold-is-real">how to tell if gold is real</a>.</p>
HTML,
  'faqs'=>[
    ['What is an assay card on a gold bar?', 'A sealed, tamper-evident package that certifies the bar’s weight, purity and serial number.'],
    ['Does opening an assay card reduce value?', 'It can. An opened bar may need testing before a dealer buys it and may fetch a lower price.'],
  ],
],
[
  'slug'=>'buying-gold-online-vs-local-coin-shop', 'topic'=>'basics', 'date'=>'2026-10-31',
  'title'=>'Buying Gold Online vs at a Local Coin Shop: Pros and Cons',
  'seo_title'=>'Buying Gold Online vs Local Coin Shop – Which Is Better?',
  'seo_desc'=>'Should you buy gold online or at a gold and silver shop near you? Prices, selection, safety, privacy and convenience compared, plus tips for both.',
  'summary'=>'The trade-offs between buying gold from an online dealer and a local gold and silver shop.',
  'body'=><<<'HTML'
<p class="lead"><strong>Online dealers usually offer a wider selection and competitive prices delivered to your door; local coin shops let you see the gold and leave with it the same day.</strong> Both can be good choices if you buy from a reputable dealer.</p>
<h2>Comparison</h2>
<table><thead><tr><th></th><th>Online dealer</th><th>Local coin shop</th></tr></thead><tbody>
<tr><th>Selection</th><td>Wide range of brands and sizes</td><td>Limited to what’s in stock</td></tr>
<tr><th>Price</th><td>Often competitive; add shipping</td><td>Varies; no shipping</td></tr>
<tr><th>Convenience</th><td>Order anytime from home</td><td>Visit during opening hours</td></tr>
<tr><th>Inspection</th><td>After delivery</td><td>Before you pay</td></tr>
<tr><th>Delivery risk</th><td>Covered by insured shipping</td><td>You carry it home</td></tr>
</tbody></table>
<h2>Tips for buying online</h2>
<ul>
<li>Check for real contact details, clear policies and insured shipping.</li>
<li>Compare the total delivered price per ounce.</li>
<li>Only pay using details from the dealer’s own email or phone.</li>
<li>Buy recognised products — see <a href="/guides/best-gold-bars-to-buy">best gold bars to buy</a>.</li>
</ul>
<h2>Tips for local shops</h2>
<ul>
<li>Ask prices as dollars over spot.</li>
<li>Check reviews and how long they’ve been in business.</li>
<li>Be discreet when leaving with your purchase.</li>
</ul>
<p>Read our full guide on <a href="/guides/how-to-buy-gold-bullion">how to buy gold bullion safely</a>, or see <a href="/how-to-order">how ordering works with us</a>.</p>
HTML,
  'faqs'=>[
    ['Is it safe to buy gold online?', 'Yes, from established dealers with clear policies, insured shipping and recognised products.'],
    ['Is gold cheaper online or in a store?', 'Online prices are often competitive, but compare the total delivered cost per ounce.'],
  ],
],
[
  'slug'=>'how-gold-bullion-is-made', 'topic'=>'basics', 'date'=>'2026-10-31',
  'title'=>'How Gold Bullion Is Made: From Mine to Gold Bar',
  'seo_title'=>'How Are Gold Bars Made? From Mine to Gold Bullion Bar',
  'seo_desc'=>'How gold bullion is made: mining, processing ore, doré bars, refining to 99.99% purity, casting and minting gold bars, and assaying. Step by step.',
  'summary'=>'The journey from gold ore in the ground to a sealed .9999 gold bar.',
  'body'=><<<'HTML'
<p class="lead"><strong>Gold bullion is made in five stages: mining, processing the ore, smelting it into rough doré bars, refining it to 99.5–99.99% purity, and casting or minting it into bars and coins.</strong></p>
<h2>1. Mining</h2>
<p>Most gold is mined from rock in open-pit or underground mines. Ore is often low-grade — commonly just a few grams of gold per tonne of rock — which is why so much rock is moved for each ounce.</p>
<h2>2. Processing the ore</h2>
<p>The ore is crushed and ground into a fine powder. Gold is then separated, typically by dissolving it in a dilute cyanide solution and capturing it on activated carbon, or by gravity methods.</p>
<h2>3. Doré bars</h2>
<p>The recovered gold is smelted at the mine into doré bars — rough bars that are often roughly 60–90% gold, with silver and other metals.</p>
<h2>4. Refining</h2>
<p>Refineries purify doré and recycled gold. The <strong>Miller process</strong> bubbles chlorine through molten gold to reach about 99.5%. The <strong>Wohlwill process</strong> (electrolysis) reaches 99.99%. Chemical refining with aqua regia is also used.</p>
<h2>5. Casting and minting</h2>
<ul>
<li><strong>Cast bars:</strong> molten gold is poured into moulds, cooled and stamped — kilo bars, 10 oz bars and 400 oz Good Delivery bars.</li>
<li><strong>Minted bars:</strong> gold is rolled into strips, blanks are cut and struck between dies, then sealed in assay cards.</li>
<li><strong>Coins:</strong> blanks are struck by government mints.</li>
</ul>
<p>Read <a href="/guides/minted-vs-cast-gold-bars">minted vs cast gold bars</a>.</p>
<h2>6. Assaying</h2>
<p>Samples are tested to confirm purity, and each bar is weighed, stamped and serial-numbered. See <a href="/guides/gold-assay-cards-and-certificates">assay cards and certificates</a>.</p>
<h2>Recycled gold</h2>
<p>A large share of gold refined each year comes from recycled jewelry and electronics. Once refined, recycled gold is identical to newly mined gold.</p>
HTML,
  'faqs'=>[
    ['How are gold bars made?', 'Refined gold is either poured into moulds (cast bars) or rolled, cut and struck (minted bars), then assayed and stamped.'],
    ['What is a doré bar?', 'A rough bar of impure gold, often 60–90% gold, produced at the mine before refining.'],
  ],
],
];
