<?php
/* Live price pages. The live tables and calculator are added by the shop around this text.
   kind: spot | karat (k) | unit (unit) | coins | ar.  {value:10g:14k} = live value of 10 grams of 14K gold;
   units g oz kg lb dwt tola t. {gold_oz} {gold_g} {silver_oz} {k_gram} {k_oz} {k_dwt} {date} also work. */
if(!defined('GB_ROOT')){ http_response_code(404); exit; }

return [
[
  'slug'=>'gold-price', 'kind'=>'spot', 'nav'=>'Gold & Silver Price Today',
  'h1'=>'Gold & Silver Price Today',
  'seo_title'=>'Gold Price Today – Live Gold & Silver Spot Prices (USD)',
  'seo_desc'=>'Live price of gold and silver today in US dollars per ounce, gram and kilo, plus 24K, 22K, 18K, 14K and 10K gold prices. Updated through the trading day.',
  'intro'=>'The live gold spot price and silver spot price in US dollars, updated through the trading day, with prices per ounce, gram and kilo and for every common karat.',
  'body'=><<<'HTML'
<h2>What is the gold spot price?</h2>
<p>The <strong>spot price</strong> is the price for one troy ounce (31.1035 grams) of pure gold for immediate delivery in the wholesale market. It is set continuously by trading between banks, refiners and funds in London, New York, Zurich, Shanghai and other centres, and it is the reference price for every gold bar, coin and piece of jewelry in the world.</p>
<p>When you buy <a href="/">gold bullion</a>, you pay the spot price for the gold it contains plus a premium for making, packaging and delivering it. When you sell, dealers pay close to spot for recognised bullion and less for jewelry and scrap.</p>
<h2>Gold price per ounce, gram and kilo</h2>
<p>Gold is quoted per troy ounce. To convert: <strong>price per gram = spot ÷ 31.1035</strong>, and <strong>price per kilo = price per gram × 1,000</strong>. At today’s price that’s {gold_g} per gram and {value:1kg} per kilo. See live tables for <a href="/gold-price-per-gram">gold price per gram</a>, <a href="/gold-price-per-kilo">per kilo</a> and <a href="/gold-price-per-pound">per pound</a>.</p>
<h2>Gold price by karat</h2>
<p>Jewelry gold is mixed with other metals, so its value depends on its karat: 24K is pure gold, 22K is 91.7% gold, 18K is 75%, 14K is 58.3% and 10K is 41.7%. Check live prices for <a href="/24k-gold-price">24K</a>, <a href="/22k-gold-price">22K</a>, <a href="/21k-gold-price">21K</a>, <a href="/18k-gold-price">18K</a>, <a href="/14k-gold-price">14K</a> and <a href="/10k-gold-price">10K gold</a>.</p>
<h2>Spot price vs futures price</h2>
<p>Financial news often quotes the price of the most active <strong>gold futures</strong> contract on the COMEX exchange in New York. Futures are agreements to buy gold at a later date, so their price is normally a little higher than spot, reflecting interest and storage costs until delivery. Read more in <a href="/guides/gold-futures-vs-spot-price">gold futures vs spot price</a>.</p>
<h2>What moves the price of gold and silver?</h2>
<ul>
<li><strong>Interest rates and the US dollar:</strong> gold pays no interest, so it tends to do better when real interest rates fall and when the dollar weakens.</li>
<li><strong>Central bank buying:</strong> central banks have been large buyers of gold in recent years.</li>
<li><strong>Uncertainty:</strong> wars, financial stress and inflation fears push investors towards gold as a safe haven.</li>
<li><strong>Industrial demand:</strong> silver is used heavily in electronics and solar panels, so it also follows the economy more closely than gold.</li>
</ul>
<h2>Gold coin prices today</h2>
<p>For live melt values of the American Gold Eagle, Buffalo, Maple Leaf, Krugerrand and more, see <a href="/gold-coin-prices">gold coin prices today</a>.</p>
HTML,
  'faqs'=>[
    ['What is the price of gold today?', 'Gold is trading at {gold_oz} per troy ounce, or {gold_g} per gram, as of {date}.'],
    ['What is the price of silver today?', 'Silver is trading at {silver_oz} per troy ounce as of {date}.'],
    ['Why is the price I pay higher than the spot price?', 'Spot is the wholesale price of raw gold. Bars and coins add a premium for refining, minting, packaging, insurance and delivery, and the dealer’s margin.'],
    ['When does the gold market trade?', 'Gold trades almost 24 hours a day from Sunday evening to Friday evening (US Eastern time), passing between Asian, European and US markets.'],
  ],
],
[
  'slug'=>'24k-gold-price', 'kind'=>'karat', 'k'=>24, 'nav'=>'24K Gold Price',
  'h1'=>'24K Gold Price Today',
  'seo_title'=>'24K Gold Price Today Per Gram & Ounce – 24 Carat Gold (USD)',
  'seo_desc'=>'Today’s 24K gold price per gram, ounce, tola and kilo in US dollars, from the live spot price. 24 carat gold price calculator and 24K vs 22K comparison.',
  'intro'=>'24 karat gold is pure gold — at least 99.9% — so its price is the gold spot price itself. Live 24K gold prices per gram, ounce, tola and kilo in US dollars.',
  'body'=><<<'HTML'
<h2>How is the 24K gold price calculated?</h2>
<p>Because 24K gold is (almost) pure, the 24 carat gold price is simply the spot price: {gold_oz} per troy ounce, which is <strong>{k_gram} per gram</strong>. Investment <a href="/gold-bars">gold bars</a> are .9999 fine (99.99% gold), and most 24K jewelry is .999.</p>
<h2>Is 24K gold real gold?</h2>
<p>Yes — 24K is the purest form of gold sold. It is soft and bright yellow, which makes it perfect for bullion bars and coins but less practical for everyday jewelry, which is why rings are usually made from 14K or 18K. Learn more in <a href="/guides/24-karat-gold">what is 24 karat gold?</a></p>
<h2>24K vs 22K gold price</h2>
<p>22K gold contains 91.67% gold, so it is worth about 8% less than 24K by weight: today {value:1g:22k} per gram against {value:1g:24k} for 24K. See the <a href="/22k-gold-price">22K gold price</a>.</p>
<h2>Buying 24K gold</h2>
<p>The cheapest way to buy 24K gold is as bullion, where you pay a small premium over the spot price. Jewelry is priced well above its gold value because of design and labour. Browse <a href="/gold-bars">24K gold bars</a> from <a href="/1-gram-gold-bars">1 gram</a> to <a href="/1-kilo-gold-bars">1 kilo</a>.</p>
HTML,
  'faqs'=>[
    ['How much is 24K gold per gram today?', '{k_gram} per gram, based on a spot price of {gold_oz} per troy ounce ({date}).'],
    ['How much is 10 grams of 24K gold?', '{value:10g:24k} at today’s spot price.'],
    ['How much is one tola of 24K gold?', 'One tola is 11.66 grams, worth {value:1tola:24k} in 24K gold today.'],
  ],
],
[
  'slug'=>'22k-gold-price', 'kind'=>'karat', 'k'=>22, 'nav'=>'22K Gold Price',
  'h1'=>'22K Gold Price Today',
  'seo_title'=>'22K Gold Price Today Per Gram in USA – 22 Carat Gold Rate',
  'seo_desc'=>'Today’s 22K gold rate per gram, ounce and tola in US dollars, calculated live from the gold spot price. 22 carat gold price calculator and chart of common weights.',
  'intro'=>'22 karat gold is 91.67% pure gold — the standard for traditional South Asian and Middle Eastern jewelry and for coins such as the American Gold Eagle. Here is today’s 22K gold rate in US dollars.',
  'body'=><<<'HTML'
<h2>How is the 22K gold rate calculated?</h2>
<p>22K gold is 22 parts gold out of 24, or 91.67% (often stamped <strong>916</strong>). Its value is the gold spot price multiplied by 0.9167: today <strong>{k_gram} per gram</strong> and {k_oz} per troy ounce.</p>
<p><strong>Formula:</strong> 22K price per gram = (spot price per ounce ÷ 31.1035) × 0.9167.</p>
<h2>22K gold price per tola</h2>
<p>One tola is 11.6638 grams. At today’s rate, a tola of 22K gold is worth <strong>{value:1tola:22k}</strong>, and a ten-tola weight {value:10tola:22k}. See our <a href="/tola-gold-price">gold tola price</a> page.</p>
<h2>Why jewelers charge more than the 22K rate</h2>
<p>The rate above is the <em>melt value</em> of the gold. Jewelers add making charges for design and labour, and when you sell, buyers usually pay somewhat less than melt value. To buy gold at close to its gold value, buy <a href="/gold-bars">gold bullion</a> instead.</p>
<h2>22K gold coins</h2>
<p>The American Gold Eagle and South African Krugerrand are 22K coins that still contain exactly one troy ounce of pure gold, because they are slightly heavier (33.93 g). See live <a href="/gold-coin-prices">gold coin prices</a>.</p>
HTML,
  'faqs'=>[
    ['What is the 22K gold price per gram today?', '{k_gram} per gram in US dollars, from a spot price of {gold_oz} ({date}).'],
    ['How much is 10 grams of 22K gold?', '{value:10g:22k} at today’s gold price.'],
    ['Is 22K gold real gold?', 'Yes. 22K gold is 91.67% pure gold, with the rest made up of metals such as copper and silver to make it harder.'],
    ['What is the difference between 22K and 24K gold?', '24K is pure gold; 22K is 91.67% gold. So 22K is worth about 8% less per gram but is harder and better suited to jewelry.'],
  ],
],
[
  'slug'=>'21k-gold-price', 'kind'=>'karat', 'k'=>21, 'nav'=>'21K Gold Price',
  'h1'=>'21K Gold Price Today',
  'seo_title'=>'21K Gold Price Per Gram Today in US Dollars',
  'seo_desc'=>'Today’s 21K gold price per gram, ounce and tola in USD. 21 karat gold is 87.5% pure — popular in Middle Eastern jewelry. Live price calculator.',
  'intro'=>'21 karat gold is 87.5% pure gold and the most popular karat for jewelry across the Middle East and North Africa. Today’s 21K gold price in US dollars, calculated live from the spot price.',
  'body'=><<<'HTML'
<h2>How the 21K gold price is calculated</h2>
<p>21K gold is 21 parts gold out of 24, or 87.5% (stamped <strong>875</strong>). Multiply the 24K price per gram by 0.875: today that gives <strong>{k_gram} per gram</strong>.</p>
<h2>21K gold vs 22K and 18K</h2>
<p>Per gram today: 22K {value:1g:22k} · 21K {value:1g:21k} · 18K {value:1g:18k}. 21K gold has a rich yellow colour and is harder than 22K, which is why it is widely used for bangles and chains in the Gulf states and Egypt.</p>
<p>For Arabic speakers, see <a href="/ar/gold-price">سعر الذهب اليوم في أمريكا</a>.</p>
HTML,
  'faqs'=>[
    ['What is the 21K gold price per gram today?', '{k_gram} per gram, based on the live gold spot price of {gold_oz} per ounce.'],
    ['How pure is 21K gold?', '87.5% gold. The remaining 12.5% is usually copper and silver.'],
  ],
],
[
  'slug'=>'18k-gold-price', 'kind'=>'karat', 'k'=>18, 'nav'=>'18K Gold Price',
  'h1'=>'18K Gold Price Today',
  'seo_title'=>'18K Gold Price Today Per Gram & Ounce – Current 18K Rate',
  'seo_desc'=>'Current 18K gold price per gram, ounce and pennyweight in US dollars. 18 karat gold is 75% pure. Live calculator for 18K gold rings, chains and scrap.',
  'intro'=>'18 karat gold is 75% pure gold — the standard for fine jewelry and luxury watches. Today’s 18K gold price per gram, ounce and pennyweight, from the live spot price.',
  'body'=><<<'HTML'
<h2>How much is 18K gold worth?</h2>
<p>18K gold is 18/24 = 75% gold (stamped <strong>750</strong>). At today’s spot price that’s <strong>{k_gram} per gram</strong>, {k_dwt} per pennyweight and {k_oz} per troy ounce.</p>
<h2>18K gold price per ounce</h2>
<p>A troy ounce of 18K gold contains 0.75 oz of pure gold, worth {k_oz} today. Note that jewelers and scales often use grams or pennyweights (dwt), not ounces: 1 troy ounce = 20 dwt = 31.1 g.</p>
<h2>Selling 18K gold jewelry</h2>
<p>Weigh your piece in grams, multiply by the 18K price per gram, and you have its melt value. Buyers typically pay a percentage of melt value, so get more than one offer. Stones, clasps and springs don’t count towards the gold weight. Read <a href="/guides/where-to-sell-gold-and-silver">where to sell gold</a>.</p>
<h2>18K white gold, yellow gold and rose gold</h2>
<p>All 18K gold has the same gold content regardless of colour, so 18K white gold is worth the same per gram as 18K yellow gold. See <a href="/white-gold-price">white gold price today</a>.</p>
HTML,
  'faqs'=>[
    ['What is the price of 18K gold per gram today?', '{k_gram} per gram as of {date}.'],
    ['What is the price of 18K gold per ounce?', '{k_oz} per troy ounce today.'],
    ['Is 18K gold worth more than 14K?', 'Yes. 18K is 75% gold and 14K is 58.3%, so 18K is worth about 29% more per gram.'],
  ],
],
[
  'slug'=>'14k-gold-price', 'kind'=>'karat', 'k'=>14, 'nav'=>'14K Gold Price',
  'h1'=>'14K Gold Price Today Per Gram',
  'seo_title'=>'14K Gold Price Today Per Gram – Live 14K Gold Rate (USD)',
  'seo_desc'=>'14K gold price today per gram, ounce and pennyweight, updated live from the gold spot price. Find out what your 14K gold ring, chain or scrap is worth.',
  'intro'=>'14 karat gold is 58.3% pure gold and the most common karat for jewelry in the United States. Here is today’s 14K gold price per gram, pennyweight and ounce, calculated live from the spot price.',
  'body'=><<<'HTML'
<h2>How much is 14K gold worth per gram today?</h2>
<p>14K gold contains 14 parts gold out of 24, or 58.3% (often stamped <strong>585</strong>, because many makers use 58.5%). Today that makes 14K gold worth <strong>{k_gram} per gram</strong> in melt value.</p>
<p><strong>Formula:</strong> 14K price per gram = (gold spot price ÷ 31.1035) × 0.583.</p>
<h2>14K gold price per ounce</h2>
<p>One troy ounce of 14K gold is worth <strong>{k_oz}</strong> today. Remember that gold is weighed in troy ounces (31.1 g), not the 28.35 g ounce on a kitchen scale. Using the wrong ounce under-values your gold by about 9%.</p>
<h2>14K gold price per pennyweight (dwt)</h2>
<p>Many US jewelers and gold buyers quote prices per pennyweight. One pennyweight is 1.555 grams, so 14K gold is worth {k_dwt} per dwt today.</p>
<h2>How to work out what your 14K gold is worth</h2>
<ol>
<li>Check the stamp: <strong>14K</strong>, <strong>14KT</strong> or <strong>585</strong>. “14K GP” or “14K GF” means gold-plated or gold-filled, worth far less.</li>
<li>Weigh it in grams on an accurate scale — see our guide to <a href="/guides/best-scale-for-gold">choosing a scale for gold</a>.</li>
<li>Multiply the weight by today’s 14K price per gram. For example, a 5 gram chain contains {value:5g:14k} of gold.</li>
<li>Expect buyers to offer 70–90% of melt value; compare offers before you sell.</li>
</ol>
<h2>14K vs 10K and 18K</h2>
<p>Per gram today: 10K {value:1g:10k} · 14K {value:1g:14k} · 18K {value:1g:18k}. See the <a href="/10k-gold-price">10K gold price</a> and <a href="/18k-gold-price">18K gold price</a>.</p>
HTML,
  'faqs'=>[
    ['What is 14K gold worth per gram today?', '{k_gram} per gram, based on a gold spot price of {gold_oz} per ounce on {date}.'],
    ['How much is 14K gold per ounce?', '{k_oz} per troy ounce today.'],
    ['How much is a 14K gold ring worth?', 'Weigh it in grams and multiply by {k_gram}. A typical 3 gram 14K band contains {value:3g:14k} of gold; stones are not included.'],
    ['Is 14K gold real gold?', 'Yes. 14K gold is 58.3% pure gold alloyed with other metals for strength. It is the most popular karat for US jewelry.'],
    ['Does white gold have the same value as yellow gold?', 'Yes, at the same karat. 14K white gold and 14K yellow gold both contain 58.3% gold.'],
  ],
],
[
  'slug'=>'10k-gold-price', 'kind'=>'karat', 'k'=>10, 'nav'=>'10K Gold Price',
  'h1'=>'10K Gold Price Today',
  'seo_title'=>'10K Gold Price Today Per Gram & Ounce – Current 10 Karat Value',
  'seo_desc'=>'Today’s 10K gold price per gram, pennyweight and ounce in USD. 10 karat gold is 41.7% pure — see what your 10K gold chain, ring or class ring is worth.',
  'intro'=>'10 karat gold is 41.7% pure gold — the lowest karat that can legally be sold as “gold” in the United States. Today’s 10K gold price per gram, pennyweight and ounce.',
  'body'=><<<'HTML'
<h2>How much is 10K gold worth?</h2>
<p>10K gold is 10/24 = 41.7% gold (stamped <strong>10K</strong> or <strong>417</strong>). At today’s spot price it is worth <strong>{k_gram} per gram</strong>, {k_dwt} per pennyweight and {k_oz} per troy ounce.</p>
<h2>10K gold price per ounce</h2>
<p>A troy ounce of 10K gold contains 0.417 oz of pure gold, worth {k_oz} today. Many men’s chains and class rings are 10K: a 20 gram 10K chain holds {value:20g:10k} of gold.</p>
<h2>Is 10K gold real gold?</h2>
<p>Yes — it contains real gold, just less of it. It is harder and more scratch-resistant than 14K, which makes it popular for everyday chains, class rings and men’s jewelry. Anything below 10K cannot be called gold in the US.</p>
<h2>Compare karats</h2>
<p>Per gram today: 10K {value:1g:10k} · <a href="/14k-gold-price">14K</a> {value:1g:14k} · <a href="/18k-gold-price">18K</a> {value:1g:18k} · <a href="/24k-gold-price">24K</a> {value:1g:24k}.</p>
HTML,
  'faqs'=>[
    ['What is the price of 10K gold today?', '{k_gram} per gram or {k_oz} per troy ounce as of {date}.'],
    ['How much is 10K gold per ounce?', '{k_oz} per troy ounce at today’s spot price.'],
    ['Is 10K gold worth anything?', 'Yes. 10K gold is 41.7% pure gold, so it has real melt value: {k_gram} per gram today.'],
  ],
],
[
  'slug'=>'gold-price-per-gram', 'kind'=>'unit', 'unit'=>'g', 'nav'=>'Gold Price Per Gram',
  'h1'=>'How Much Is a Gram of Gold Worth Today?',
  'seo_title'=>'How Much Is a Gram of Gold? Gold Price Per Gram Today',
  'seo_desc'=>'A gram of gold is worth {gold_g} today. Live gold price per gram in US dollars for 24K, 22K, 18K, 14K and 10K gold, plus 2 gram, half gram and 10 gram values.',
  'intro'=>'One gram of pure gold is worth <strong>{gold_g}</strong> today ({date}). Below is the live gold price per gram for every karat and common weight, in US dollars.',
  'body'=><<<'HTML'
<h2>How to calculate the price of gold per gram</h2>
<p>Gold is priced per troy ounce, and a troy ounce is 31.1035 grams. So:</p>
<p><strong>Gold price per gram = spot price per ounce ÷ 31.1035</strong></p>
<p>At today’s spot price of {gold_oz}, that’s {gold_g} per gram of 24K gold. For lower karats, multiply by the gold content: 22K × 0.917, 18K × 0.75, 14K × 0.583 and 10K × 0.417.</p>
<h2>What is 1 gram of gold worth?</h2>
<ul>
<li>1 gram of 24K gold: <strong>{value:1g:24k}</strong></li>
<li>1 gram of 22K gold: {value:1g:22k}</li>
<li>1 gram of 18K gold: {value:1g:18k}</li>
<li>1 gram of 14K gold: {value:1g:14k}</li>
<li>1 gram of 10K gold: {value:1g:10k}</li>
</ul>
<h2>How much is half a gram of gold worth?</h2>
<p>Half a gram of 24K gold is worth <strong>{value:0.5g:24k}</strong> today, and half a gram of 14K gold {value:0.5g:14k}.</p>
<h2>2 grams of gold price</h2>
<p>2 grams of 24K gold is worth <strong>{value:2g:24k}</strong>; 2 grams of 22K gold {value:2g:22k}.</p>
<h2>10 gram gold price</h2>
<p>10 grams of 24K gold is worth {value:10g:24k} today. See the full <a href="/10-gram-gold-price">10 gram gold price</a> page.</p>
<h2>Buying gold by the gram</h2>
<p>The easiest way to own gold by the gram is a <a href="/1-gram-gold-bars">1 gram gold bar</a>: 24K .9999 gold sealed in an assay card. Small bars carry a higher premium than larger ones, so it costs more than the melt value above. Read <a href="/guides/is-a-1-gram-gold-bar-worth-it">is a 1 gram gold bar worth it?</a></p>
HTML,
  'faqs'=>[
    ['How much is a gram of gold worth today?', 'A gram of pure 24K gold is worth {gold_g} as of {date}. A gram of 14K gold is worth {value:1g:14k}.'],
    ['What’s the price of gold per gram in the USA?', '{gold_g} per gram for 24K gold, based on the US dollar spot price of {gold_oz} per troy ounce.'],
    ['Why do stores charge more than the price per gram?', 'Jewelry and small bars include making charges, packaging and dealer margin on top of the gold value.'],
  ],
],
[
  'slug'=>'10-gram-gold-price', 'kind'=>'unit', 'unit'=>'10g', 'nav'=>'10 Gram Gold Price',
  'h1'=>'10 Gram Gold Price Today',
  'seo_title'=>'10 Gram Gold Price Today in USA – 24K, 22K & 18K',
  'seo_desc'=>'Today’s price of 10 grams of gold in US dollars: 24K {value:10g:24k}. Live 10g gold rate for 24K, 22K, 21K, 18K and 14K gold.',
  'intro'=>'10 grams of 24K gold is worth <strong>{value:10g:24k}</strong> today. Gold is commonly priced per 10 grams in India, the Gulf and South Asia — here is the live 10 gram gold rate in US dollars for every karat.',
  'body'=><<<'HTML'
<h2>How the 10 gram gold price is calculated</h2>
<p>Divide the spot price per troy ounce by 31.1035 to get the price per gram, then multiply by 10. Today: {gold_oz} ÷ 31.1035 × 10 = <strong>{value:10g:24k}</strong> for 24K gold.</p>
<h2>10 grams of gold by karat</h2>
<ul>
<li>24K: {value:10g:24k}</li><li>22K: {value:10g:22k}</li><li>21K: {value:10g:21k}</li><li>18K: {value:10g:18k}</li><li>14K: {value:10g:14k}</li>
</ul>
<h2>Buy a 10 gram gold bar</h2>
<p>A <a href="/10-gram-gold-bars">10 gram gold bar</a> is 24K .9999 gold sealed in an assay card — a popular size because it balances a modest price with a lower premium per gram than 1 gram bars.</p>
HTML,
  'faqs'=>[
    ['What is the price of 10 grams of gold today?', '{value:10g:24k} for 24K gold and {value:10g:22k} for 22K gold, as of {date}.'],
    ['How many ounces is 10 grams of gold?', '0.3215 troy ounces.'],
  ],
],
[
  'slug'=>'gold-price-per-kilo', 'kind'=>'unit', 'unit'=>'kg', 'nav'=>'Gold Price Per Kilo',
  'h1'=>'How Much Is a Kilo of Gold Worth?',
  'seo_title'=>'How Much Is a Kilo of Gold? 1kg Gold Price Today',
  'seo_desc'=>'1 kilo of gold is worth {value:1kg} today. Live 1kg gold price in US dollars, ounces in a kilo, and what a kilo gold bar and a ton of gold are worth.',
  'intro'=>'One kilogram of pure gold is worth <strong>{value:1kg}</strong> today ({date}). A kilo contains 32.15 troy ounces of gold.',
  'body'=><<<'HTML'
<h2>How to calculate the price of 1 kg of gold</h2>
<p>A kilogram is 1,000 grams, or <strong>32.1507 troy ounces</strong>. So the 1kg gold price = spot price × 32.1507. Today: {gold_oz} × 32.1507 = <strong>{value:1kg}</strong>.</p>
<h2>How big is a kilo of gold?</h2>
<p>Gold is extremely dense (19.32 g/cm³), so a kilo is just 52 cubic centimetres — about the size of a smartphone. A <a href="/1-kilo-gold-bars">1 kilo gold bar</a> weighs 2.2 lb.</p>
<h2>1 kilo gold bar price</h2>
<p>The gold in a kilo bar is worth {value:1kg}. Kilo bars carry the lowest premium of any retail gold bar, so you pay only slightly more than this. Kilo bars are .9999 fine, so the bar contains a full kilogram of gold.</p>
<h2>How much is a ton of gold worth?</h2>
<p>A metric ton (tonne) is 1,000 kilograms, or 32,150.7 troy ounces. At today’s price, a tonne of gold is worth about <strong>{value:1t}</strong>. All the gold ever mined — around 216,000 tonnes according to industry estimates — would fit in a cube about 22 metres on each side.</p>
<h2>Other weights</h2>
<ul>
<li>100 grams of gold: {value:100g}</li>
<li>500 grams of gold: {value:500g}</li>
<li>1 pound of gold: {value:1lb} — see <a href="/gold-price-per-pound">how much is a pound of gold</a></li>
<li>A 400 oz Good Delivery bar (12.4 kg): {value:400oz} — see the <a href="/guides/400-oz-gold-bar">400 oz gold bar</a></li>
</ul>
HTML,
  'faqs'=>[
    ['How much is 1 kilo of gold worth today?', '{value:1kg} at the spot price of {gold_oz} per troy ounce ({date}).'],
    ['How many ounces are in a kilo of gold?', '32.1507 troy ounces.'],
    ['How much is a ton of gold worth?', 'About {value:1t} per metric ton at today’s price.'],
  ],
],
[
  'slug'=>'gold-price-per-pound', 'kind'=>'unit', 'unit'=>'lb', 'nav'=>'Gold Price Per Pound',
  'h1'=>'How Much Is a Pound of Gold Worth?',
  'seo_title'=>'How Much Is a Pound of Gold Worth Today? (1 lb Gold Price)',
  'seo_desc'=>'A pound of gold is worth {value:1lb} today. Live 1 lb gold price in US dollars, how many troy ounces are in a pound, and troy pound vs regular pound.',
  'intro'=>'One pound (lb) of pure gold is worth <strong>{value:1lb}</strong> today ({date}). A standard pound contains 14.58 troy ounces of gold.',
  'body'=><<<'HTML'
<h2>How to calculate the value of a pound of gold</h2>
<p>A normal (avoirdupois) pound is 453.59 grams. Gold is priced per troy ounce of 31.1035 grams, so <strong>1 lb = 14.5833 troy ounces</strong>. Today: {gold_oz} × 14.5833 = <strong>{value:1lb}</strong>.</p>
<h2>Troy pound vs regular pound</h2>
<p>There is also an old <strong>troy pound</strong> of 12 troy ounces (373.24 g), which is lighter than a normal pound. Gold is no longer traded in troy pounds, but it explains why a “pound of gold” is sometimes quoted at different values. A troy pound of gold is worth {value:12oz} today.</p>
<h2>How much does a gold bar weigh in pounds?</h2>
<ul>
<li>1 oz gold bar: 0.069 lb</li>
<li><a href="/10-oz-gold-bars">10 oz gold bar</a>: 0.69 lb ({value:10oz})</li>
<li><a href="/1-kilo-gold-bars">1 kilo gold bar</a>: 2.2 lb ({value:1kg})</li>
<li>400 oz Good Delivery bar: 27.4 lb ({value:400oz})</li>
</ul>
<p>More in our <a href="/guides/gold-bar-weight-and-sizes">gold bar weight guide</a>.</p>
HTML,
  'faqs'=>[
    ['How much is 1 lb of gold worth today?', '{value:1lb} at a spot price of {gold_oz} per troy ounce ({date}).'],
    ['How many troy ounces are in a pound?', '14.5833 troy ounces in a standard pound; 12 troy ounces in a troy pound.'],
  ],
],
[
  'slug'=>'tola-gold-price', 'kind'=>'unit', 'unit'=>'tola', 'nav'=>'Gold Tola Price',
  'h1'=>'Gold Price Per Tola Today',
  'seo_title'=>'Gold Tola Price Today in USA – 24K, 22K & 21K (USD)',
  'seo_desc'=>'One tola of 24K gold is worth {value:1tola:24k} today. Live gold tola price in US dollars for 24K, 22K, 21K and 18K, and the 10 tola bar price.',
  'intro'=>'A tola is a traditional South Asian and Gulf unit of gold weight equal to 11.6638 grams. One tola of 24K gold is worth <strong>{value:1tola:24k}</strong> today.',
  'body'=><<<'HTML'
<h2>How much is one tola of gold?</h2>
<ul>
<li>24K (999): {value:1tola:24k}</li>
<li>22K (916): {value:1tola:22k}</li>
<li>21K (875): {value:1tola:21k}</li>
<li>18K (750): {value:1tola:18k}</li>
</ul>
<h2>How many grams in a tola?</h2>
<p>One tola = <strong>11.6638 grams</strong> = 0.375 troy ounces. Ten tolas weigh 116.64 grams, or 3.75 troy ounces.</p>
<h2>The 10 tola gold bar</h2>
<p>The 10 tola bar (TT bar) is one of the most popular gold bars in Pakistan, India, Bangladesh and the Gulf. It contains 3.75 oz of 24K gold, worth <strong>{value:10tola:24k}</strong> today.</p>
HTML,
  'faqs'=>[
    ['What is the gold price per tola today?', '{value:1tola:24k} for 24K gold and {value:1tola:22k} for 22K gold, as of {date}.'],
    ['How many grams is one tola?', '11.6638 grams.'],
  ],
],
[
  'slug'=>'white-gold-price', 'kind'=>'white', 'nav'=>'White Gold Price',
  'h1'=>'White Gold Price Today',
  'seo_title'=>'White Gold Price Today – How Much Is White Gold Worth?',
  'seo_desc'=>'How much is white gold worth? Live white gold prices per gram for 10K, 14K and 18K white gold, and why white gold is worth the same as yellow gold of the same karat.',
  'intro'=>'White gold is worth exactly the same per gram as yellow gold of the same karat, because the value is in the gold content, not the colour. Here are today’s white gold prices.',
  'body'=><<<'HTML'
<h2>How much is white gold worth?</h2>
<p>White gold is real gold mixed with white metals such as palladium, nickel or silver, then usually plated with rhodium for a bright finish. A 14K white gold ring contains 58.3% gold — the same as 14K yellow gold — so it has the same melt value: <strong>{value:1g:14k} per gram</strong> today.</p>
<ul>
<li>10K white gold: {value:1g:10k} per gram</li>
<li>14K white gold: {value:1g:14k} per gram</li>
<li>18K white gold: {value:1g:18k} per gram</li>
</ul>
<h2>Is white gold worth more than yellow gold?</h2>
<p>No — at the same karat, the gold value is identical. A white gold piece made with palladium may cost more to buy new, but buyers pay for the gold content. Read <a href="/guides/white-gold-vs-yellow-gold">white gold vs yellow gold</a> for the full comparison.</p>
<h2>How to tell what karat your white gold is</h2>
<p>Look for a stamp inside the band or near the clasp: 10K/417, 14K/585 or 18K/750. Then weigh it and multiply by the price per gram above.</p>
HTML,
  'faqs'=>[
    ['How much is 14K white gold worth per gram?', '{value:1g:14k} per gram today, the same as 14K yellow gold.'],
    ['Is white gold real gold?', 'Yes. White gold is real gold alloyed with white metals and usually rhodium plated.'],
  ],
],
[
  'slug'=>'gold-coin-prices', 'kind'=>'coins', 'nav'=>'Gold Coin Prices',
  'h1'=>'Gold Coin Prices Today',
  'seo_title'=>'Gold Coin Prices Today – Gold Eagle, Buffalo, Maple & Krugerrand',
  'seo_desc'=>'Live gold coin prices today: melt values of the American Gold Eagle, Buffalo, Canadian Maple Leaf, Krugerrand, Philharmonic, Britannia and fractional gold coins.',
  'intro'=>'The live gold value of the world’s most popular gold bullion coins, calculated from today’s spot price of {gold_oz} per ounce.',
  'body'=><<<'HTML'
<h2>How gold coin prices work</h2>
<p>A bullion coin’s price starts with its <strong>melt value</strong> — the value of the pure gold it contains, shown above. Dealers then add a premium that varies by coin, size and demand. One-ounce coins usually sell for a few percent over spot; fractional coins carry higher premiums. Older or rare coins can be worth more to collectors.</p>
<h2>1 ounce gold coin price</h2>
<p>Every 1 oz gold bullion coin — whether 22K like the Gold Eagle or 24K like the Maple Leaf — contains one troy ounce of pure gold, worth <strong>{value:1oz}</strong> today.</p>
<h2>Which gold coin should you buy?</h2>
<p>For the lowest cost per ounce, buy 1 oz coins from major mints. If you want flexibility, mix in fractional coins. Compare coins with bars in <a href="/guides/gold-coins-vs-gold-bars">gold coins vs gold bars</a>, or shop <a href="/gold-coins">gold coins</a>.</p>
HTML,
  'faqs'=>[
    ['How much is a 1 oz gold coin worth today?', 'Its gold content is worth {value:1oz} at today’s spot price. Dealers sell bullion coins for a small premium above that.'],
    ['How much is a 1/10 oz gold coin worth?', '{value:0.1oz} in gold value today.'],
  ],
],
[
  'slug'=>'ar/gold-price', 'kind'=>'ar', 'lang'=>'ar', 'nav'=>'سعر الذهب اليوم',
  'h1'=>'سعر الذهب اليوم في أمريكا بالدولار',
  'seo_title'=>'سعر الذهب اليوم في أمريكا – سعر غرام الذهب عيار 24 و21 و18',
  'seo_desc'=>'سعر الذهب اليوم في أمريكا بالدولار الأمريكي: سعر الأونصة وسعر غرام الذهب عيار 24 و22 و21 و18 و14، محدّث مباشرة من سعر الذهب الفوري.',
  'intro'=>'سعر الذهب اليوم في الولايات المتحدة الأمريكية بالدولار، محسوب مباشرة من سعر الأونصة الفوري في الأسواق العالمية: {gold_oz} للأونصة.',
  'body'=><<<'HTML'
<h2>كيف يُحسب سعر غرام الذهب؟</h2>
<p>يُسعَّر الذهب عالمياً بالأونصة التروي، وتساوي <strong>31.1035 غراماً</strong>. لحساب سعر الغرام عيار 24 نقسم سعر الأونصة على 31.1035، ثم نضرب الناتج في نسبة نقاء العيار:</p>
<ul>
<li>عيار 24 = ذهب خالص 99.9%</li>
<li>عيار 22 = 91.7% ذهب</li>
<li>عيار 21 = 87.5% ذهب</li>
<li>عيار 18 = 75% ذهب</li>
<li>عيار 14 = 58.3% ذهب</li>
</ul>
<h2>سعر أونصة الذهب في أمريكا اليوم</h2>
<p>سعر أونصة الذهب اليوم هو <strong>{gold_oz}</strong> دولار أمريكي. الأسعار في هذه الصفحة هي قيمة الذهب الخام؛ وتضيف محلات المجوهرات عادةً أجرة المصنعية، بينما تُباع سبائك الذهب بعلاوة صغيرة فوق السعر الفوري.</p>
<h2>سعر الذهب عيار 21 في أمريكا</h2>
<p>سعر غرام الذهب عيار 21 اليوم هو <strong>{value:1g:21k}</strong>، وهو العيار الأكثر انتشاراً في مصر ودول الخليج.</p>
<h2>شراء سبائك الذهب في أمريكا</h2>
<p>إذا كنت تقيم في الولايات المتحدة وترغب في الادخار بالذهب، فإن السبائك عيار 24 هي أقل الطرق تكلفة لامتلاك الذهب، لأنها لا تحمل أجرة مصنعية المجوهرات. تتوفر لدينا <a href="/1-gram-gold-bars">سبائك 1 غرام</a> و<a href="/10-gram-gold-bars">سبائك 10 غرام</a> و<a href="/1-oz-gold-bars">سبائك الأونصة</a> و<a href="/1-kilo-gold-bars">سبائك الكيلو</a>، مع شحن مؤمَّن داخل الولايات المتحدة.</p>
<p><a href="/gold-price" hreflang="en">English: Gold price today</a></p>
HTML,
  'faqs'=>[
    ['كم سعر الذهب اليوم في أمريكا؟', 'سعر أونصة الذهب اليوم {gold_oz} دولار، وسعر غرام الذهب عيار 24 هو {gold_g} دولار ({date}).'],
    ['كم سعر غرام الذهب عيار 21 في أمريكا؟', '{value:1g:21k} دولار للغرام اليوم.'],
    ['كم سعر غرام الذهب عيار 18 في أمريكا؟', '{value:1g:18k} دولار للغرام اليوم.'],
    ['كم غراماً في أونصة الذهب؟', 'الأونصة التروي تساوي 31.1035 غراماً.'],
  ],
],
];
