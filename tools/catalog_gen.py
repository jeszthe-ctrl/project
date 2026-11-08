# Generates inc/catalog.php: the product range added in November 2026.
import re, json, math, sys

def slug(s):
    s = s.strip()
    for a, b in {'é':'e','É':'e','—':'-','–':'-','&':'and',"'":''}.items(): s = s.replace(a, b)
    return re.sub(r'[^a-z0-9]+', '-', s.lower()).strip('-')

# ---------------------------------------------------------------- the list
# (name as shown, type, set, price USD single unit, extras)
P = []
def add(name, typ, st, price, **kw): P.append(dict(name=name, typ=typ, set=st, price=price, **kw))

C30 = '30th Celebration'
add('Pokémon 30th Celebration Ultra-Premium Collection — Day', 'upc', C30, 189.99, status='new')
add('Pokémon 30th Celebration Ultra-Premium Collection — Night', 'upc', C30, 189.99, status='new')
add('Pokémon 30th Celebration Pokémon Center Elite Trainer Box', 'pcetb', C30, 139.99, status='new')
add('Pokémon 30th Celebration Ditto Premium Collection', 'premium', C30, 44.99, status='new', poke='Ditto')
add('Pokémon 30th Celebration Mew Figure Collection', 'figure', C30, 39.99, status='new', poke='Mew')
add('Pokémon 30th Celebration Mewtwo Figure Collection', 'figure', C30, 39.99, status='new', poke='Mewtwo')
add('Pokémon 30th Celebration Booster Bundle', 'bundle', C30, 49.99, status='new')
add('Pokémon 30th Celebration Mini Tin Collection', 'minitin', C30, 14.99, status='new')
add('Pokémon 30th Celebration Binder Collection', 'binder', C30, 39.99, status='new')
add('Pokémon 30th Celebration Poster Collection', 'poster', C30, 29.99, status='new')
add('Pokémon 30th Celebration Eevee 2-Pack Blister', 'blister2', C30, 14.99, status='new', poke='Eevee')
add('Pokémon 30th Celebration Knock Out Collection', 'premium', C30, 24.99, status='new')
add('Pokémon 30th Celebration Espeon ex Battle Deck', 'deck', C30, 21.99, status='new', poke='Espeon ex')
add('Pokémon 30th Celebration Umbreon ex Battle Deck', 'deck', C30, 21.99, status='new', poke='Umbreon ex')
add('Pokémon 30th Celebration Sylveon ex Tin', 'tin', C30, 29.99, status='preorder', release='December 2026', poke='Sylveon ex')
add('Pokémon 30th Celebration Greninja ex Tin', 'tin', C30, 29.99, status='preorder', release='December 2026', poke='Greninja ex')
add('Pokémon 30th Celebration English Booster Pack', 'loose', C30, 6.99, status='new')

DR = 'Delta Reign'
add('Pokémon Mega Evolution—Delta Reign Elite Trainer Box', 'etb', DR, 64.99, status='new')
add('Pokémon Mega Evolution—Delta Reign Pokémon Center Elite Trainer Box', 'pcetb', DR, 119.99, status='new')
add('Pokémon Mega Evolution—Delta Reign Booster Display Box', 'display', DR, 219.99, status='new')
add('Pokémon Mega Evolution—Delta Reign Booster Bundle', 'bundle', DR, 39.99, status='new')
add('Pokémon Mega Evolution—Delta Reign Build & Battle Box', 'bb', DR, 26.99, status='new')
add('Pokémon Mega Evolution—Delta Reign Sleeved Booster Pack', 'sleeved', DR, 6.49, status='new')
add('Pokémon Mega Evolution—Delta Reign 3-Pack Blister', 'blister3', DR, 19.99, status='new')
add('Pokémon Mega Evolution—Delta Reign Checklane Blister', 'checklane', DR, 7.49, status='new')
for pk in ['Ampharos', 'Delphox', 'Keldeo', 'Xerneas']:
    add(f'Pokémon Mega Evolution—Delta Reign Premium Checklane Blister — {pk}', 'pchecklane', DR, 13.99, status='new', poke=pk)
add('Pokémon Mega Evolution—Delta Reign Loose Booster Pack', 'loose', DR, 5.99, status='new')
add('Pokémon Mega Evolution—Delta Reign Factory-Sealed Booster Case', 'case_display', DR, 1249.99, status='new')
add('Pokémon Mega Evolution—Delta Reign Factory-Sealed ETB Case', 'case_etb', DR, 619.99, status='new')

for st, pc, disp in [('Pitch Black', 124.99, 219.99), ('Chaos Rising', 124.99, 214.99), ('Perfect Order', 124.99, 214.99)]:
    pass
add('Pokémon Mega Evolution—Pitch Black Pokémon Center Elite Trainer Box', 'pcetb', 'Pitch Black', 124.99)
add('Pokémon Mega Evolution—Chaos Rising Pokémon Center Elite Trainer Box', 'pcetb', 'Chaos Rising', 124.99)
add('Pokémon Mega Evolution—Perfect Order Pokémon Center Elite Trainer Box', 'pcetb', 'Perfect Order', 124.99)
add('Pokémon Mega Evolution—Ascended Heroes Pokémon Center Elite Trainer Box', 'pcetb', 'Ascended Heroes', 159.99)
add('Pokémon Mega Evolution—Pitch Black Booster Display Box', 'display', 'Pitch Black', 219.99)
add('Pokémon Mega Evolution—Chaos Rising Booster Display Box', 'display', 'Chaos Rising', 214.99)
add('Pokémon Mega Evolution—Perfect Order Booster Display Box', 'display', 'Perfect Order', 214.99)
add('Pokémon Mega Evolution—Pitch Black Booster Bundle', 'bundle', 'Pitch Black', 39.99)
add('Pokémon Mega Evolution—Chaos Rising Booster Bundle', 'bundle', 'Chaos Rising', 39.99)
add('Pokémon Mega Evolution—Perfect Order Booster Bundle', 'bundle', 'Perfect Order', 39.99)
add('Pokémon Mega Evolution—Pitch Black Build & Battle Box', 'bb', 'Pitch Black', 26.99)
add('Pokémon Mega Evolution—Chaos Rising Build & Battle Box', 'bb', 'Chaos Rising', 26.99)
add('Pokémon Mega Evolution—Perfect Order Build & Battle Box', 'bb', 'Perfect Order', 26.99)
for pk, pr in [('Mega Dragonite ex', 29.99), ('Mega Darkrai ex', 29.99), ('Mega Zeraora ex', 29.99)]:
    add(f'Pokémon Mega Forces Tin — {pk}', 'tin', '', pr, poke=pk)
add('Pokémon Mega Greninja ex League Battle Deck', 'leaguedeck', '', 32.99, status='preorder', release='November 13, 2026', poke='Mega Greninja ex')
add('Pokémon Unova Heavy Hitters Premium Collection', 'premium', '', 64.99, status='preorder', release='December 4, 2026')
add('Pokémon Mega Rayquaza ex Premium Tournament Collection', 'premium', '', 74.99, status='preorder', release='February 12, 2027', poke='Mega Rayquaza ex')
add('Pokémon Japanese Special Deck Set MEGA — Feraligatr, Dragonite & Gengar', 'jpdeckset', '', 69.99, status='preorder', release='November 13, 2026')
PF = 'Phantasmal Flames'
add('Pokémon Mega Evolution—Phantasmal Flames Elite Trainer Box', 'etb', PF, 74.99)
add('Pokémon Mega Evolution—Phantasmal Flames Pokémon Center Elite Trainer Box', 'pcetb', PF, 149.99)
ME = 'Mega Evolution'
add('Pokémon Mega Evolution Elite Trainer Box — Mega Lucario', 'etb', ME, 69.99, poke='Mega Lucario')
add('Pokémon Mega Evolution Elite Trainer Box — Mega Gardevoir', 'etb', ME, 69.99, poke='Mega Gardevoir')
add('Pokémon Mega Evolution Pokémon Center Elite Trainer Box — Mega Lucario', 'pcetb', ME, 139.99, poke='Mega Lucario')
add('Pokémon Mega Evolution Pokémon Center Elite Trainer Box — Mega Gardevoir', 'pcetb', ME, 139.99, poke='Mega Gardevoir')

SVETB = [  # set, ETB price, PC ETB price
    ('Black Bolt', 79.99, 159.99), ('White Flare', 79.99, 159.99), ('Destined Rivals', 109.99, 219.99),
    ('Journey Together', 79.99, 149.99), ('Prismatic Evolutions', 119.99, 329.99), ('Surging Sparks', 84.99, 169.99),
    ('Stellar Crown', 69.99, 139.99), ('Shrouded Fable', 74.99, 149.99), ('Twilight Masquerade', 79.99, 149.99),
    ('Temporal Forces', 74.99, 139.99), ('Paldean Fates', 124.99, 259.99), ('151', 139.99, 299.99),
    ('Obsidian Flames', 89.99, 169.99), ('Paldea Evolved', 94.99, 179.99)]
for st, e, pc in SVETB:
    setname = 'Scarlet & Violet—151' if st == '151' else st
    add(f'Pokémon Scarlet & Violet—{st} Elite Trainer Box', 'etb', setname, e)
    add(f'Pokémon Scarlet & Violet—{st} Pokémon Center Elite Trainer Box', 'pcetb', setname, pc)
add('Pokémon Scarlet & Violet Base Set Elite Trainer Box — Koraidon', 'etb', 'Scarlet & Violet Base Set', 84.99, poke='Koraidon')
add('Pokémon Scarlet & Violet Base Set Elite Trainer Box — Miraidon', 'etb', 'Scarlet & Violet Base Set', 84.99, poke='Miraidon')
add('Pokémon Crown Zenith Elite Trainer Box', 'etb', 'Crown Zenith', 109.99)
add('Pokémon Crown Zenith Pokémon Center Elite Trainer Box Plus', 'pcetb', 'Crown Zenith', 299.99)
add('Pokémon 30th Celebration UPC — Factory-Sealed Case Configuration', 'case_upc', C30, 749.99, status='new')
add('Pokémon Mega Charizard X ex Ultra-Premium Collection', 'upc', '', 169.99, poke='Mega Charizard X ex')
add('Pokémon Greninja ex Ultra-Premium Collection', 'upc', '', 139.99, poke='Greninja ex')
add('Pokémon Terapagos ex Ultra-Premium Collection', 'upc', '', 139.99, poke='Terapagos ex')
add('Pokémon Scarlet & Violet—151 Ultra-Premium Collection', 'upc', 'Scarlet & Violet—151', 159.99)
add('Pokémon Sword & Shield Charizard Ultra-Premium Collection', 'upc', '', 299.99, poke='Charizard')
add('Pokémon Arceus VSTAR Ultra-Premium Collection', 'upc', '', 189.99, poke='Arceus VSTAR')
add('Pokémon Celebrations Ultra-Premium Collection', 'upc', 'Celebrations', 319.99)
add('Pokémon Prismatic Evolutions Super-Premium Collection', 'upc', 'Prismatic Evolutions', 189.99)
add('Pokémon Crown Zenith Shiny Zacian Premium Figure Collection', 'figure', 'Crown Zenith', 94.99, poke='Shiny Zacian')
add('Pokémon Crown Zenith Shiny Zamazenta Premium Figure Collection', 'figure', 'Crown Zenith', 94.99, poke='Shiny Zamazenta')
for st, pr in [('Phantasmal Flames', 249.99), ('Mega Evolution', 219.99), ('Destined Rivals', 339.99), ('Journey Together', 249.99),
               ('Surging Sparks', 269.99), ('Stellar Crown', 199.99), ('Twilight Masquerade', 239.99), ('Temporal Forces', 219.99),
               ('Obsidian Flames', 229.99), ('Paldea Evolved', 239.99)]:
    add(f'Pokémon {st} Booster Display Box', 'display', st, pr)
for st, pr in [('Black Bolt', 59.99), ('White Flare', 59.99), ('Prismatic Evolutions', 74.99), ('Destined Rivals', 79.99)]:
    add(f'Pokémon {st} Booster Bundle', 'bundle', st, pr)
add('Pokémon Scarlet & Violet—151 Booster Bundle', 'bundle', 'Scarlet & Violet—151', 74.99)
add('Pokémon Scarlet & Violet—151 Binder Collection', 'binder', 'Scarlet & Violet—151', 64.99)
add('Pokémon Scarlet & Violet—151 Poster Collection', 'poster', 'Scarlet & Violet—151', 49.99)
add('Pokémon Scarlet & Violet—151 Alakazam ex Collection', 'premium', 'Scarlet & Violet—151', 39.99, poke='Alakazam ex')
add('Pokémon Scarlet & Violet—151 Zapdos ex Collection', 'premium', 'Scarlet & Violet—151', 39.99, poke='Zapdos ex')
add('Pokémon Scarlet & Violet—151 Mini Tin Collection', 'minitin', 'Scarlet & Violet—151', 21.99)

JP = [  # name, code, price, packs x cards (None = unknown)
    ('Black Bolt', 'SV11B', 119.99), ('White Flare', 'SV11W', 119.99), ('Battle Partners', 'SV9', 84.99),
    ('Super Electric Breaker', 'SV8', 94.99), ('Paradise Dragona', 'SV7a', 84.99), ('Stellar Miracle', 'SV7', 74.99),
    ('Night Wanderer', 'SV6a', 99.99), ('Mask of Change', 'SV6', 74.99), ('Crimson Haze', 'SV5a', 94.99),
    ('Wild Force', 'SV5K', 84.99), ('Cyber Judge', 'SV5M', 84.99), ('Shiny Treasure ex', 'SV4a', 144.99),
    ('Ancient Roar', 'SV4K', 99.99), ('Future Flash', 'SV4M', 99.99), ('Raging Surf', 'SV3a', 104.99),
    ('Ruler of the Black Flame', 'SV3', 134.99), ('Clay Burst', 'SV2D', 124.99), ('Snow Hazard', 'SV2P', 124.99),
    ('Triplet Beat', 'SV1a', 134.99), ('Violet ex', 'SV1V', 144.99), ('Scarlet ex', 'SV1S', 144.99),
    ('VSTAR Universe', 'S12a', 229.99), ('Lost Abyss', 'S11', 154.99), ('Pokémon GO', 'S10b', 144.99), ('Eevee Heroes', 'S6a', 649.99)]
for st, code, pr in JP:
    add(f'Pokémon Japanese {st} ({code}) Booster Box', 'jpbox', f'{st} (Japanese)' if st in ('Black Bolt', 'White Flare') else st, pr, code=code)

OP = [  # code, title, type, price, status, release
    ('PEB-01', 'The Drawing of Your Dreams Premium Extra Booster', 'opextra', 159.99, 'preorder', 'April 23, 2027'),
    ('OP-19', 'Come In, World Booster Box', 'opbox', 109.99, 'preorder', 'March 5, 2027'),
    ('EB-06', 'East Blue Extra Booster Box', 'opextra', 119.99, 'preorder', 'January 29, 2027'),
    ('OP-18', 'The Dominance of God Booster Box', 'opbox', 109.99, 'preorder', 'November 20, 2026'),
    ('EB-05', 'One Piece Heroines Edition Vol. 2 Extra Booster Box', 'opextra', 139.99, 'in', ''),
    ('OP-17', "The World's Strongest Warriors Booster Box", 'opbox', 114.99, 'in', ''),
    ('OP-16', 'The Time of Battle Booster Box', 'opbox', 114.99, 'in', ''),
    ('OP15-EB04', "Adventure on Kami's Island Booster Box", 'opbox', 119.99, 'in', ''),
    ('OP14-EB04', "The Azure Sea's Seven Booster Box", 'opbox', 119.99, 'in', ''),
    ('OP-13', 'Carrying On His Will Booster Box', 'opbox', 149.99, 'in', ''),
    ('OP-12', 'Legacy of the Master Booster Box', 'opbox', 124.99, 'in', ''),
    ('OP-11', 'A Fist of Divine Speed Booster Box', 'opbox', 134.99, 'in', ''),
    ('OP-10', 'Royal Blood Booster Box', 'opbox', 124.99, 'in', ''),
    ('OP-09', 'Emperors in the New World Booster Box', 'opbox', 249.99, 'in', ''),
    ('OP-08', 'Two Legends Booster Box', 'opbox', 139.99, 'in', ''),
    ('OP-07', '500 Years in the Future Booster Box', 'opbox', 164.99, 'in', ''),
    ('OP-06', 'Wings of the Captain Booster Box', 'opbox', 189.99, 'in', ''),
    ('OP-05', 'Awakening of the New Era Booster Box', 'opbox', 299.99, 'in', ''),
    ('OP-04', 'Kingdoms of Intrigue Booster Box', 'opbox', 169.99, 'in', ''),
    ('OP-03', 'Pillars of Strength Booster Box', 'opbox', 209.99, 'in', ''),
    ('OP-02', 'Paramount War Booster Box', 'opbox', 249.99, 'in', ''),
    ('OP-01', 'Romance Dawn Booster Box', 'opbox', 399.99, 'in', ''),
    ('EB-03', 'One Piece Heroines Edition Extra Booster Box', 'opextra', 169.99, 'in', ''),
    ('EB-02', 'Anime 25th Collection Extra Booster Box', 'opextra', 189.99, 'in', ''),
    ('EB-01', 'Memorial Collection Extra Booster Box', 'opextra', 209.99, 'in', ''),
    ('PRB-02', 'The Best Vol. 2 Premium Booster Box', 'opextra', 169.99, 'in', ''),
    ('PRB-01', 'The Best Premium Booster Box', 'opextra', 279.99, 'in', ''),
    ('ST-38', 'Baroque Works Starter Deck', 'opstarter', 15.99, 'preorder', 'December 19, 2026'),
    ('ST-37', 'The Desert Princess and the Pirates Starter Deck', 'opstarter', 15.99, 'preorder', 'December 18, 2026'),
    ('ST-36', 'Yellow Eustass Captain Kid Starter Deck', 'opstarter', 15.99, 'in', ''),
    ('ST-35', 'Red/Black Sabo Starter Deck', 'opstarter', 15.99, 'in', ''),
    ('ST-34', 'Purple Charlotte Katakuri Starter Deck', 'opstarter', 15.99, 'in', ''),
    ('ST-33', 'Blue Kuzan Starter Deck', 'opstarter', 15.99, 'in', ''),
    ('ST-32', 'Green Roronoa Zoro Starter Deck', 'opstarter', 15.99, 'in', ''),
]
for code, title, typ, pr, status, rel in OP:
    add(f'One Piece {code} — {title}', typ, '', pr, status=status, release=rel, code=code, title=title)

# ---------------------------------------------------------------- facts per set
ERA = {}
for s in ['Delta Reign', 'Pitch Black', 'Chaos Rising', 'Perfect Order', 'Ascended Heroes', 'Phantasmal Flames', 'Mega Evolution']: ERA[s] = 'Mega Evolution'
for s in ['Black Bolt', 'White Flare', 'Destined Rivals', 'Journey Together', 'Prismatic Evolutions', 'Surging Sparks', 'Stellar Crown',
          'Shrouded Fable', 'Twilight Masquerade', 'Temporal Forces', 'Paldean Fates', 'Scarlet & Violet—151', 'Obsidian Flames',
          'Paldea Evolved', 'Scarlet & Violet Base Set']: ERA[s] = 'Scarlet & Violet'
for s in ['Crown Zenith', 'Celebrations']: ERA[s] = 'Sword & Shield'
ERA['30th Celebration'] = 'Mega Evolution'
ETB_PACKS = {s: 9 for s in ['Black Bolt', 'White Flare', 'Destined Rivals', 'Journey Together', 'Prismatic Evolutions', 'Surging Sparks',
             'Stellar Crown', 'Shrouded Fable', 'Twilight Masquerade', 'Temporal Forces', 'Paldean Fates', 'Scarlet & Violet—151',
             'Obsidian Flames', 'Paldea Evolved', 'Scarlet & Violet Base Set', 'Phantasmal Flames', 'Mega Evolution']}
ETB_PACKS['Crown Zenith'] = 10
HIGH_CLASS = {'Shiny Treasure ex', 'VSTAR Universe'}   # Japanese high-class sets: 10 packs of 10 cards

def setlabel(s): return '151' if s == 'Scarlet & Violet—151' else s.replace(' (Japanese)', '')

TYPES = {  # type: (category, weight kg, sku code, label)
 'etb': ('etb', 0.95, 'ETB', 'Elite Trainer Box'), 'pcetb': ('etb', 1.3, 'PCETB', 'Pokémon Center Elite Trainer Box'),
 'case_etb': ('etb', 10.0, 'ETBC', 'Elite Trainer Box case'), 'display': ('boxes', 0.75, 'BD', 'booster display box'),
 'bundle': ('boxes', 0.3, 'BB', 'booster bundle'), 'sleeved': ('boxes', 0.04, 'SBP', 'sleeved booster pack'),
 'loose': ('boxes', 0.03, 'BP', 'booster pack'), 'case_display': ('boxes', 5.0, 'BDC', 'booster box case'),
 'jpbox': ('boxes', 0.45, 'JBB', 'Japanese booster box'), 'bb': ('premium', 0.35, 'BNB', 'Build & Battle Box'),
 'blister3': ('premium', 0.15, 'BL3', '3-pack blister'), 'blister2': ('premium', 0.12, 'BL2', '2-pack blister'),
 'checklane': ('premium', 0.06, 'CLB', 'checklane blister'), 'pchecklane': ('premium', 0.1, 'PCLB', 'premium checklane blister'),
 'upc': ('premium', 2.0, 'UPC', 'Ultra-Premium Collection'), 'case_upc': ('premium', 7.0, 'UPCC', 'Ultra-Premium Collection case'),
 'premium': ('premium', 0.8, 'PC', 'premium collection'), 'figure': ('premium', 0.9, 'FIG', 'figure collection'),
 'binder': ('premium', 0.9, 'BIN', 'binder collection'), 'poster': ('premium', 0.6, 'POS', 'poster collection'),
 'tin': ('premium', 0.35, 'TIN', 'tin'), 'minitin': ('premium', 0.12, 'MT', 'mini tin'),
 'deck': ('premium', 0.3, 'DK', 'battle deck'), 'leaguedeck': ('premium', 0.5, 'LBD', 'League Battle Deck'),
 'jpdeckset': ('premium', 0.8, 'JDS', 'Japanese special deck set'),
 'opbox': ('onepiece', 0.7, 'OPB', 'booster box'), 'opextra': ('onepiece', 0.6, 'OPX', 'booster box'),
 'opstarter': ('onepiece', 0.18, 'OPS', 'starter deck'),
}

def nice(x):
    if x < 10: return round(x, 2)
    return math.floor(x * 2) / 2 - 0.01        # 52.49, 52.99 …

def ladder(p):
    if p < 15:  tiers = [(1, 1), (10, .93), (36, .87), (100, .82)]
    elif p < 300: tiers = [(1, 1), (4, .95), (6, .92), (10, .88), (24, .85)]
    else: tiers = [(1, 1), (2, .96), (4, .93), (6, .90)]
    return [[q, p if f == 1 else nice(p * f)] for q, f in tiers]

def clean_name(n): return n
def short(n):  # name without the brand prefix, for titles
    return re.sub(r'^Pokémon (Mega Evolution—|Scarlet & Violet—)?', '', n)

SHIP = 'Ships worldwide from Japan with tracking.'
BULK = 'Buying for a shop or a break? The unit price drops at each bulk tier on this page, applied automatically.'

def desc(p):
    t, s, n = p['typ'], p['set'], p['name']; sl = setlabel(s) if s else ''
    era = ERA.get(s, ''); poke = p.get('poke', '')
    era_txt = f' from the Pokémon TCG {era} series' if era else ''
    pre = f"\n\nThis is a preorder: it ships as soon as stock arrives (release {p['release']}). Preorders are invoiced when stock is allocated, not when you order." if p.get('status') == 'preorder' else ''
    if t == 'etb':
        k = ETB_PACKS.get(s)
        lead = f"The English {sl} Elite Trainer Box{' featuring ' + poke if poke else ''}{era_txt}: {str(k) + ' ' if k else ''}{sl} booster packs plus everything a player needs, in a collector's storage box. Factory sealed."
        body = (f"An Elite Trainer Box (ETB) is the complete way to open {sl}: booster packs plus sleeves, Energy cards and dice to build and play a deck. "
                "It's one of the best-selling Pokémon products for card shops and online stores, and a favourite gift.\n\n"
                f"- {str(k) + ' ' if k else ''}{sl} booster packs\n- 65 card sleeves and 45 Pokémon TCG Energy cards\n- Player's guide, damage-counter dice, a coin-flip die and condition markers\n"
                "- Collector's box with dividers to store your cards\n- English edition, factory sealed")
    elif t == 'pcetb':
        lead = (f"The Pokémon Center exclusive version of the {sl} Elite Trainer Box{' featuring ' + poke if poke else ''}: more booster packs than the standard ETB, "
                "a Pokémon Center exclusive promo card, and the usual sleeves, Energy, dice and storage box. English, factory sealed.")
        body = (f"Pokémon Center Elite Trainer Boxes are sold only through the official Pokémon Center, which makes them the most collected version of each ETB. "
                f"The {sl} Pokémon Center ETB keeps everything in the regular box and adds extra booster packs and an exclusive promo, so it's a favourite to open and to keep sealed.\n\n"
                f"- {sl} booster packs (11 in most Pokémon Center ETBs, compared with 9 in the standard box)\n- Pokémon Center exclusive promo card\n- Card sleeves, Pokémon TCG Energy cards, player's guide, dice and condition markers\n"
                "- Collector's storage box\n- English edition, factory sealed")
    elif t == 'display':
        lead = f"A factory-sealed English {sl} booster display box{era_txt}: 36 {sl} booster packs, the best-value way to open the set or stock it by the pack."
        body = (f"A booster display box (booster box) is how card shops sell {sl} packs at the counter, and how collectors open a set for the best chance at its chase cards. "
                f"Each pack holds 10 cards.\n\n- 36 {sl} booster packs, 10 cards each\n- Factory-sealed display box\n- English edition")
    elif t == 'bundle':
        lead = f"The English {sl} Booster Bundle: 6 {sl} booster packs in one sealed bundle, a simple way to open the set without buying a full box."
        body = (f"Booster bundles are a steady seller for shops and a popular gift. Each bundle holds 6 sealed {sl} booster packs.\n\n- 6 {sl} booster packs\n- English edition, factory sealed")
    elif t == 'bb':
        lead = f"The {sl} Build & Battle Box: a ready-to-play deck, 4 {sl} booster packs and a foil promo card. English, factory sealed."
        body = (f"Build & Battle Boxes are made for prerelease events and new players: open the packs, add the cards to the 40-card deck inside and play straight away. "
                f"Tournament organizers buy them in bulk for {sl} events.\n\n- 4 {sl} booster packs\n- 40-card deck to build on\n- 1 foil promo card\n- Deck-building guide\n- English edition, factory sealed")
    elif t == 'sleeved':
        lead = f"One English {sl} booster pack in a protective artwork sleeve, factory sealed."
        body = f"Sleeved booster packs are the classic counter display item for card shops. Each holds one sealed {sl} booster pack of 10 cards.\n\n- 1 {sl} booster pack in an artwork sleeve\n- English edition"
    elif t == 'loose':
        lead = f"A single sealed English {sl} booster pack{' from the Pokémon TCG ' + era + ' series' if era else ''}."
        body = f"Loose booster packs are great for pack-opening streams, prize support and filling out orders. Each pack is factory sealed.\n\n- 1 sealed {sl} booster pack\n- English edition"
    elif t == 'blister3':
        lead = f"The {sl} 3-Pack Blister: 3 {sl} booster packs with a foil promo card and a coin, factory sealed. English."
        body = f"3-pack blisters are a popular peg item for shops and a low-cost way to open a few packs.\n\n- 3 {sl} booster packs\n- 1 foil promo card\n- 1 coin\n- English edition"
    elif t == 'checklane':
        lead = f"The {sl} Checklane Blister: a sealed {sl} booster pack with a Pokémon coin. English."
        body = f"Checklane blisters are the classic impulse buy next to the till.\n\n- 1 {sl} booster pack\n- 1 Pokémon coin\n- English edition"
    elif t == 'pchecklane':
        lead = f"The {sl} Premium Checklane Blister with {poke}: {sl} booster packs, a foil {poke} promo card and a coin. English, factory sealed."
        body = f"One of four Premium Checklane Blisters for {sl}, this one featuring {poke}. A popular peg item, and collectors buy all four for the promo cards.\n\n- {sl} booster packs\n- Foil {poke} promo card\n- Coin\n- English edition"
    elif t == 'case_display':
        lead = f"A factory-sealed case of 6 English {sl} booster display boxes: 216 booster packs in the original distributor carton."
        body = (f"The best price per pack for {sl}. A sealed case is how shops stock a new release and how breakers buy for streams.\n\n"
                f"- 6 sealed {sl} booster display boxes\n- 36 packs per box, 216 packs in total\n- Original sealed case\n- English edition")
    elif t == 'case_etb':
        lead = f"A factory-sealed case of 10 English {sl} Elite Trainer Boxes, in the original distributor carton."
        body = f"The lowest price per {sl} ETB for shops and resellers.\n\n- 10 sealed {sl} Elite Trainer Boxes\n- Original sealed case\n- English edition"
    elif t == 'case_upc':
        lead = f"A factory-sealed case of {sl} Ultra-Premium Collections in the manufacturer's case configuration, in the original carton."
        body = ("For shops and investors who want the 30th Celebration Ultra-Premium Collections untouched in their sealed case. "
                "The number of collections per case follows the manufacturer's configuration and is confirmed on your invoice before you pay.\n\n"
                f"- Sealed case of {sl} Ultra-Premium Collections\n- Original carton\n- English edition")
    elif t == 'upc':
        what = poke
        lead = f"The {short(n)}: a premium Pokémon TCG collection{' featuring ' + poke if poke else ''} with booster packs, foil promo cards and collector extras. English, factory sealed."
        body = (f"Ultra-Premium Collections are the top tier of Pokémon TCG sealed product and some of the most collected boxes of all — "
                f"opened for the packs and promos, or kept sealed as a display piece.\n\n- Booster packs\n- Foil promo cards{' of ' + what if what else ''}\n- Premium collector extras (see the box for the full contents)\n- English edition, factory sealed")
    elif t in ('premium', 'figure', 'binder', 'poster'):
        extra = {'figure': f"a {poke} figure" if poke else 'a figure', 'binder': 'a collector binder', 'poster': 'a poster', 'premium': 'collector extras'}[t]
        lead = f"The {short(n)}: booster packs, foil promo cards{' of ' + poke if poke else ''} and {extra}. English, factory sealed."
        body = (f"A collector's set that's easy to sell and easy to gift{', from ' + sl if sl else ''}.\n\n- Booster packs\n- Foil promo card{'s' if not poke else ''}{' of ' + poke if poke else ''}\n- {extra[0].upper() + extra[1:]}\n- English edition, factory sealed")
    elif t in ('tin', 'minitin'):
        lead = f"The {short(n)}: booster packs and a {'foil ' + poke + ' promo card' if poke else 'art card'} in a collectible {'mini ' if t == 'minitin' else ''}tin. English, factory sealed."
        body = f"Tins are a year-round best seller: easy to stack, easy to gift and great value per pack.\n\n- Booster packs\n- {'Foil ' + poke + ' promo card' if poke else 'Art card'}\n- Collectible {'mini ' if t == 'minitin' else ''}tin\n- English edition"
    elif t == 'blister2':
        lead = f"The {short(n)}: 2 booster packs and a foil {poke} promo card, factory sealed. English."
        body = f"A low-cost way to open {sl} and collect the {poke} promo.\n\n- 2 booster packs\n- Foil {poke} promo card\n- English edition"
    elif t == 'deck':
        lead = f"The {short(n)}: a ready-to-play 60-card Pokémon TCG deck built around {poke}. English, factory sealed."
        body = f"Battle Decks are the easiest way into the Pokémon TCG: shuffle and play. A great add-on for shops running league nights.\n\n- Ready-to-play 60-card deck featuring {poke}\n- Deck box and play accessories\n- English edition"
    elif t == 'leaguedeck':
        lead = f"The {short(n)}: a tournament-ready 60-card Pokémon TCG deck built around {poke}, with accessories. English."
        body = f"League Battle Decks are built for competitive play straight out of the box, with a deck list tuned around {poke}.\n\n- Tournament-ready 60-card deck featuring {poke}\n- Deck accessories (see the box for the full contents)\n- English edition, factory sealed"
    elif t == 'jpdeckset':
        lead = "The Japanese Special Deck Set MEGA featuring Mega Feraligatr, Mega Dragonite and Mega Gengar: a premium deck set for the Japanese Pokémon TCG. Japanese, factory sealed."
        body = "A Japanese-exclusive deck set from the Mega Evolution era, popular with collectors of Japanese product.\n\n- Japanese special deck set: Feraligatr, Dragonite and Gengar\n- Japanese edition, factory sealed"
    elif t == 'jpbox':
        hc = s in HIGH_CLASS
        lead = f"A factory-sealed Japanese {sl} ({p['code']}) booster box from Japan: {'10 packs of 10 cards' if hc else '30 packs of 5 cards'}, in its original shrink wrap."
        body = (f"Japanese {sl} is sourced in Japan and ships to you directly, sealed exactly as it left the factory. Japanese boxes are a favourite with collectors and box breakers for their print quality and pull rates.\n\n"
                f"- Japanese {sl} ({p['code']}) booster box\n- {'10 booster packs, 10 cards each (high-class set)' if hc else '30 booster packs, 5 cards each'}\n- Japanese edition, factory sealed")
    elif t == 'opbox':
        lead = f"A factory-sealed English One Piece Card Game {p['code']} {p['title'].replace(' Booster Box', '')} booster box: 24 packs of 12 cards."
        body = (f"{p['code']} — {p['title'].replace(' Booster Box', '')} is an English One Piece Card Game booster set from Bandai. A sealed booster box is the best-value way to open the set and chase its Leaders and alternate arts.\n\n"
                f"- 24 {p['code']} booster packs, 12 cards each\n- English edition, factory sealed")
    elif t == 'opextra':
        lead = f"A factory-sealed English One Piece Card Game {p['code']} {p['title']}: a special Bandai release full of alternate-art and reprint cards."
        body = (f"{p['code']} is one of the One Piece Card Game's special releases, and its alternate-art and reprint cards make it a favourite with collectors and box breakers.\n\n"
                f"- Sealed {p['code']} booster box (see the box for the pack count)\n- English edition, factory sealed")
    elif t == 'opstarter':
        lead = f"The English One Piece Card Game {p['code']} {p['title']}: a ready-to-play 51-card deck with its Leader card."
        body = f"Starter decks are the way into the One Piece Card Game and a steady seller for shops running tournaments.\n\n- Ready-to-play deck: 1 Leader card and 50 cards\n- DON!! cards\n- English edition, factory sealed"
    else:
        raise Exception(t)
    return lead + '\n\n' + body + pre + '\n\n' + BULK + ' ' + SHIP

def seo_title(p):
    t, s = p['typ'], p['set']; sl = setlabel(s) if s else ''
    if t == 'etb': x = f"{sl} Elite Trainer Box{' — ' + p['poke'] if p.get('poke') else ''} (English) — Pokémon ETB"
    elif t == 'pcetb': x = f"{sl} Pokémon Center Elite Trainer Box{' — ' + p['poke'] if p.get('poke') else ''}"
    elif t == 'display': x = f"{sl} Booster Box — 36 Packs, English Pokémon TCG"
    elif t == 'bundle': x = f"{sl} Booster Bundle — 6 Packs, Pokémon TCG"
    elif t == 'bb': x = f"{sl} Build & Battle Box — Pokémon TCG"
    elif t == 'jpbox': x = f"Japanese {sl} {p['code']} Booster Box — Pokémon TCG"
    elif t in ('opbox', 'opextra'): x = f"One Piece {p['code']} {p['title']} (English)"
    elif t == 'opstarter': x = f"One Piece {p['code']} {p['title']} (English)"
    else:
        x = short(p['name'])
        if len(x) < 46: x += ' — Pokémon TCG'
    if p.get('status') == 'preorder': x = x.replace(' (English)', '') + ' — Preorder'
    return x

def seo_desc(p, price):
    lead = desc(p).split('\n\n')[0]
    lead = re.sub(r'\s+', ' ', lead)
    first = ladder(price)[1][0]
    tail = f" From ${price:,.2f}, lower prices from {first}+. Ships worldwide."
    room = 158 - len(tail)
    if len(lead) > room: lead = lead[:room].rsplit(' ', 1)[0].rstrip(',;:—-') + '…'
    return lead + tail

def php(v, ind=0):
    if isinstance(v, bool): return 'true' if v else 'false'
    if isinstance(v, (int, float)) and not isinstance(v, bool):
        return repr(v) if isinstance(v, float) else str(v)
    if isinstance(v, str): return "'" + v.replace('\\', '\\\\').replace("'", "\\'") + "'"
    if isinstance(v, list):
        if all(not isinstance(x, (dict, list)) for x in v) or all(isinstance(x, list) for x in v):
            return '[' + ','.join(php(x) for x in v) + ']'
        return '[\n' + ',\n'.join(' ' * (ind + 2) + php(x, ind + 2) for x in v) + '\n' + ' ' * ind + ']'
    if isinstance(v, dict):
        return '[' + ', '.join(php(k) + '=>' + php(x, ind + 2) for k, x in v.items()) + ']'
    raise TypeError(v)

seen, out, counters = set(), [], {}
existing = set(sys.argv[1].split(',')) if len(sys.argv) > 1 else set()
for p in P:
    key = p['name']
    if key in seen: continue
    seen.add(key)
    pid = slug(re.sub(r'^Pokémon (Mega Evolution—|Scarlet & Violet—(?!151))', '', p['name']).replace('Scarlet & Violet—151', 'Scarlet Violet 151'))
    assert pid not in existing, pid
    cat, kg, code, _ = TYPES[p['typ']]
    counters[code] = counters.get(code, 0) + 1
    lang = 'Japanese' if p['typ'] in ('jpbox', 'jpdeckset') else 'English'
    lad = ladder(p['price'])
    out.append({'id': pid, 'sku': f"PK-{code}-{counters[code]:03d}", 'name': p['name'], 'set': p['set'], 'cat': cat, 'cond': 'Sealed', 'lang': lang,
                'moq': 1, 'step': 1, 'status': p.get('status', 'in'), 'release': p.get('release', ''), 'weight': kg, 'hidden': False,
                'ladder': lad, 'desc': desc(p), 'seo_title': seo_title(p), 'seo_desc': seo_desc(p, p['price']), 'added': '2026-11'})
ids = [o['id'] for o in out]; assert len(ids) == len(set(ids))
for o in out: assert len(o['seo_desc']) <= 160, (o['id'], len(o['seo_desc']))
src = ("<?php\n/* The product range added in November 2026: English Pokémon TCG sealed product (30th Celebration, Mega Evolution,\n"
       "   Scarlet & Violet, Sword & Shield), Japanese booster boxes and English One Piece Card Game. New shops get these\n"
       "   with the starting data; shops installed earlier get any that are missing, once (content update 10).\n"
       "   Prices are starting estimates in USD: check them in Admin → Products. */\n"
       "if(!defined('FK_ROOT')){ http_response_code(404); exit; }\n\nreturn [\n")
src += ',\n'.join('  ' + php(o, 2) for o in out) + '\n];\n'
open('inc/catalog.php', 'w').write(src)
print(len(out), 'products;', sum(1 for o in out if o['cat'] == 'onepiece'), 'One Piece;', sum(1 for o in out if o['status'] == 'preorder'), 'preorders')
mx = max(out, key=lambda o: len(o['seo_title'])); print('longest seo title', len(mx['seo_title']), mx['seo_title'])
