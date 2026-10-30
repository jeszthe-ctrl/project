<?php
/* The shop's starting data. On first run it's copied to data/store.php and from then on
   everything is edited in admin.php — you never need to change this file.
   Content: inc/content-*.php (categories, pages, price pages, guides). */
if(!defined('GB_ROOT')){ http_response_code(404); exit; }

return [
  'settings' => [
    'brand'        => 'Gold Bullion Shop',
    'legal_name'   => '',
    'domain'       => 'https://goldbullion-shop.com',
    'email'        => 'sales@goldbullion.online',
    'order_email'  => 'sales@goldbullion.online',
    'from_email'   => '',
    'phone'        => '',
    'sms'          => '',
    'address'      => '',
    'hours'        => 'Monday–Friday 9am–6pm ET · Saturday 10am–2pm ET',
    'reply_time'   => 'within a few business hours',
    'pay_window'   => '24',
    'dispatch_time'=> '1–2 business days after payment clears',
    'announcement' => 'Insured shipping on every order · Live spot pricing · Payment details sent by text or email after you order',
    'author'       => 'Gold Bullion Shop Research Desk',
    'countries'    => 'United States',

    /* home page */
    'home_seo_title' => 'Gold Bullion: Buy Gold Bars & Coins Online at Low Premiums',
    'home_seo_desc'  => 'Buy gold bullion online: 24K gold bars from 1 gram to 1 kilo and gold coins, priced from the live spot price. Fully insured US shipping, discreet packaging.',
    'hero_kicker'    => 'Investment-grade 24K gold',
    'hero_title'     => 'Buy Gold Bullion Online',
    'hero_text'      => 'Gold bars from 1 gram to 1 kilo and gold coins from the world’s leading refiners and mints — priced from the live spot price, packed discreetly and shipped fully insured to your door.',

    /* shop + listing pages */
    'shop_seo_title'   => 'Shop Gold Bullion – All Gold Bars & Gold Coins for Sale',
    'shop_seo_desc'    => 'Browse every gold bar and gold coin we stock, from 1 gram to 1 kilo. 24K .9999 fine gold bullion from leading refiners and mints, priced live from spot.',
    'shop_intro'       => 'Every product below is genuine, investment-grade gold bullion from an established refinery or sovereign mint. Prices follow the live gold spot price and are locked the moment you place your order.',
    'guides_seo_title' => 'Gold Bullion Guides – Buying, Weights, Prices & Karats',
    'guides_seo_desc'  => 'Plain-English guides to buying gold bullion: bar sizes and weights, karats, spot price vs premiums, how to spot fake gold, storage and more.',

    /* search engines */
    'gsc_verification'  => '',
    'bing_verification' => '',
    'ga4_id'            => '',
    'chat_code'         => '',

    /* live spot price */
    'spot_mode'     => 'auto',
    'manual_gold'   => 0,
    'manual_silver' => 0,
    'manual_time'   => 0,

    /* email: leave smtp_host empty to use the host's PHP mail() */
    'smtp_host'   => '',
    'smtp_port'   => 465,
    'smtp_secure' => 'ssl',
    'smtp_user'   => '',
    'smtp_pass'   => '',

    /* the email you send from Admin → Orders → "Send payment details" */
    'pay_email_subject' => 'Payment details for your order {order}',
    'pay_email_body'    => "Hi {name},\n\nThank you for your order {order} with {shop}.\n\nOrder total: {total}\nPayment method: {method}\n\nHOW TO PAY\n{details}\n\nPlease write your order number {order} in the payment note so we can match it to your order. Your price is held for {pay_window} hours from when you placed your order.\n\nAs soon as your payment arrives we pack your order and email you the tracking number.\n\nQuestions? Just reply to this email or call/text {phone}.\n\n{shop}",
    'ship_email_subject' => 'Your order {order} has shipped',
    'ship_email_body'    => "Hi {name},\n\nGood news — your order {order} is on its way.\n\nCarrier: {carrier}\nTracking number: {tracking}\n\nYour package is fully insured and needs an adult signature on delivery. It is packed in plain, unbranded packaging.\n\nThank you for buying from {shop}.",

    'deleted_pages' => [], 'deleted_guides' => [], 'deleted_tools' => [], 'deleted_categories' => [],
  ],

  'shipping' => [
    'methods' => [
      'standard' => ['enabled'=>true, 'label'=>'Standard Shipping', 'price'=>28, 'days'=>'3–5 business days', 'note'=>'Fully insured, adult signature required', 'free_eligible'=>true],
      'express'  => ['enabled'=>true, 'label'=>'Express Shipping',  'price'=>50, 'days'=>'1–2 business days', 'note'=>'Priority handling, fully insured, adult signature required', 'free_eligible'=>false],
    ],
    'free_over' => 0,
  ],

  /* "details" is what you send customers (your $Cashtag, Zelle email…). It is never shown on the website. */
  'payments' => [
    'cashapp'  => ['enabled'=>true, 'label'=>'Cash App',  'note'=>'After you order, we text or email you our $Cashtag and the exact amount.', 'details'=>'Send {total} to our Cash App $Cashtag: $YOURCASHTAG'],
    'applepay' => ['enabled'=>true, 'label'=>'Apple Pay', 'note'=>'After you order, we send you the number or email to pay with Apple Cash.', 'details'=>'Send {total} with Apple Pay (Apple Cash) to: YOUR NUMBER OR EMAIL'],
    'zelle'    => ['enabled'=>true, 'label'=>'Zelle',     'note'=>'After you order, we send you our Zelle email or phone number.', 'details'=>'Send {total} with Zelle to: YOUR ZELLE EMAIL OR PHONE (name: YOUR BUSINESS NAME)'],
    'chime'    => ['enabled'=>true, 'label'=>'Chime',     'note'=>'After you order, we send you our $ChimeSign to pay with Chime Pay Anyone.', 'details'=>'Send {total} with Chime Pay Anyone to: $YOURCHIMESIGN'],
    'crypto'   => ['enabled'=>true, 'label'=>'Crypto (BTC, ETH, USDT, USDC)', 'note'=>'After you order, we send you a wallet address and the exact amount in your chosen coin.', 'details'=>"Bitcoin (BTC): YOUR BTC ADDRESS\nUSDT (TRC-20 or ERC-20): YOUR ADDRESS\nReply to this email if you'd prefer another coin."],
  ],

  'products'   => [],
  'categories' => require GB_ROOT.'/inc/content-categories.php',
  'pages'      => require GB_ROOT.'/inc/content-pages.php',
  'tools'      => require GB_ROOT.'/inc/content-tools.php',
  'guides'     => require GB_ROOT.'/inc/content-guides.php',
  'home'       => require GB_ROOT.'/inc/content-home.php',
];
