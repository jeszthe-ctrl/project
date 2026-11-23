# Market-based pricing for every product (November 2026).
#
# MARKET[id] = (market reference in USD, 'v' verified / 'e' estimated).
#   verified  = from a current market tracker or retailer listing found in November 2026
#               (TCGplayer market price via PokemonWizard / Out of Games / The Hobby Bin, SNKRDUNK or
#               Japanese retail for Japanese boxes, retailer asks for One Piece)
#   estimated = no current figure found; set from the verified prices of comparable products
# Products not listed here (singles, accessories, a few Japanese specials) keep the owner's price.
#
# Base price  = products still in print: a share of their US retail price (under Pokémon Center's price);
#               everything else: 12% under the market reference (UNDER).
# Bulk tiers  = gentle steps (TIER_STEPS), and buying more never costs less in total.
# Sealed case = always cheaper per unit than the same quantity bought loose (CASE_SAVING).
import math

UNDER = 0.88          # 12% under market
CASE_SAVING = 0.97    # a case costs 3% less per unit than the same quantity at the loose tier price
TIER_STEPS = [1, 0.98, 0.96, 0.94, 0.92]   # price factor at each tier

M = {}
def m(ids, usd, src='e'):
    for i in ids.split(): M[i] = (usd, src)

# ---- English Pokémon, Mega Evolution era (in print; market from TCGplayer trackers) ----
m('delta-reign-elite-trainer-box', 120, 'v'); m('delta-reign-pokemon-center-elite-trainer-box', 450, 'v'); m('delta-reign-booster-display-box', 250, 'v')
m('delta-reign-booster-bundle', 50); m('delta-reign-build-and-battle-box', 40); m('delta-reign-sleeved-booster-pack', 8); m('delta-reign-loose-booster-pack', 7)
m('delta-reign-3-pack-blister', 25); m('delta-reign-checklane-blister', 9)
m('delta-reign-premium-checklane-blister-ampharos delta-reign-premium-checklane-blister-delphox delta-reign-premium-checklane-blister-keldeo delta-reign-premium-checklane-blister-xerneas', 17)
m('phantasmal-flames-elite-trainer-box', 120, 'v'); m('phantasmal-flames-pokemon-center-elite-trainer-box', 250, 'v'); m('pokemon-phantasmal-flames-booster-display-box', 390, 'v')
m('pokemon-mega-evolution-elite-trainer-box-mega-lucario pokemon-mega-evolution-elite-trainer-box-mega-gardevoir', 90)
m('pokemon-mega-evolution-pokemon-center-elite-trainer-box-mega-lucario pokemon-mega-evolution-pokemon-center-elite-trainer-box-mega-gardevoir', 200)
m('pokemon-mega-evolution-booster-display-box', 280)
m('perfect-order-elite-trainer-box', 95, 'v'); m('perfect-order-pokemon-center-elite-trainer-box', 220); m('perfect-order-booster-display-box', 200)
m('perfect-order-booster-bundle', 45); m('perfect-order-build-and-battle-box', 35)
m('chaos-rising-elite-trainer-box', 64, 'v'); m('chaos-rising-pokemon-center-elite-trainer-box', 220, 'v'); m('chaos-rising-booster-display-box', 173, 'v')
m('chaos-rising-booster-bundle', 40); m('chaos-rising-build-and-battle-box', 32)
m('pitch-black-elite-trainer-box', 65, 'v'); m('pitch-black-pokemon-center-elite-trainer-box', 200); m('pitch-black-booster-display-box', 175)
m('pitch-black-booster-bundle', 40); m('pitch-black-build-and-battle-box', 32)
m('mega-evolution-ascended-heroes-elite-trainer-box', 175, 'v'); m('ascended-heroes-pokemon-center-elite-trainer-box', 400)
m('pokemon-mega-forces-tin-mega-dragonite-ex pokemon-mega-forces-tin-mega-darkrai-ex pokemon-mega-forces-tin-mega-zeraora-ex', 32)
m('pokemon-mega-greninja-ex-league-battle-deck', 35); m('pokemon-unova-heavy-hitters-premium-collection', 65); m('pokemon-mega-rayquaza-ex-premium-tournament-collection', 75)
m('pokemon-mega-charizard-x-ex-ultra-premium-collection', 231, 'v'); m('pokemon-greninja-ex-ultra-premium-collection', 180)

# ---- English 30th Celebration ----
m('30th-celebration-elite-trainer-box', 185, 'v'); m('pokemon-30th-celebration-pokemon-center-elite-trainer-box', 400, 'v')
m('pokemon-30th-celebration-ultra-premium-collection-day', 600, 'v'); m('pokemon-30th-celebration-ultra-premium-collection-night', 660, 'v')
m('pokemon-30th-celebration-booster-bundle', 90); m('pokemon-30th-celebration-mini-tin-collection', 25); m('pokemon-30th-celebration-binder-collection', 60)
m('pokemon-30th-celebration-poster-collection', 45); m('pokemon-30th-celebration-ditto-premium-collection', 70)
m('pokemon-30th-celebration-mew-figure-collection pokemon-30th-celebration-mewtwo-figure-collection', 60)
m('pokemon-30th-celebration-eevee-2-pack-blister', 25); m('pokemon-30th-celebration-knock-out-collection', 35)
m('pokemon-30th-celebration-espeon-ex-battle-deck pokemon-30th-celebration-umbreon-ex-battle-deck', 30)
m('pokemon-30th-celebration-sylveon-ex-tin pokemon-30th-celebration-greninja-ex-tin', 40); m('pokemon-30th-celebration-english-booster-pack', 12)

# ---- English Scarlet & Violet (out of print) ----
m('black-bolt-elite-trainer-box white-flare-elite-trainer-box', 140, 'v'); m('black-bolt-pokemon-center-elite-trainer-box white-flare-pokemon-center-elite-trainer-box', 300)
m('pokemon-black-bolt-booster-bundle pokemon-white-flare-booster-bundle', 45, 'v')
m('destined-rivals-elite-trainer-box', 133, 'v'); m('destined-rivals-pokemon-center-elite-trainer-box', 350); m('pokemon-destined-rivals-booster-display-box', 378, 'v'); m('pokemon-destined-rivals-booster-bundle', 75)
m('journey-together-elite-trainer-box', 138, 'v'); m('journey-together-pokemon-center-elite-trainer-box', 300); m('pokemon-journey-together-booster-display-box', 255, 'v')
m('prismatic-evolutions-elite-trainer-box', 160, 'v'); m('prismatic-evolutions-pokemon-center-elite-trainer-box', 440, 'v')
m('pokemon-prismatic-evolutions-booster-bundle', 100); m('pokemon-prismatic-evolutions-super-premium-collection', 300)
m('surging-sparks-elite-trainer-box', 129, 'v'); m('surging-sparks-pokemon-center-elite-trainer-box', 280); m('pokemon-surging-sparks-booster-display-box', 295, 'v')
m('stellar-crown-elite-trainer-box', 145, 'v'); m('stellar-crown-pokemon-center-elite-trainer-box', 300); m('pokemon-stellar-crown-booster-display-box', 330, 'v')
m('shrouded-fable-elite-trainer-box', 112, 'v'); m('shrouded-fable-pokemon-center-elite-trainer-box', 250)
m('twilight-masquerade-elite-trainer-box', 140); m('twilight-masquerade-pokemon-center-elite-trainer-box', 300); m('pokemon-twilight-masquerade-booster-display-box', 320)
m('temporal-forces-elite-trainer-box', 125, 'v'); m('temporal-forces-pokemon-center-elite-trainer-box', 280); m('pokemon-temporal-forces-booster-display-box', 330)
m('paldean-fates-elite-trainer-box', 475, 'v'); m('paldean-fates-pokemon-center-elite-trainer-box', 750)
m('pokemon-scarlet-violet-151-elite-trainer-box', 490, 'v'); m('pokemon-scarlet-violet-151-pokemon-center-elite-trainer-box', 900)
m('pokemon-scarlet-violet-151-ultra-premium-collection', 450); m('pokemon-scarlet-violet-151-booster-bundle', 180); m('pokemon-scarlet-violet-151-binder-collection', 150)
m('pokemon-scarlet-violet-151-poster-collection', 120); m('pokemon-scarlet-violet-151-alakazam-ex-collection pokemon-scarlet-violet-151-zapdos-ex-collection', 80)
m('pokemon-scarlet-violet-151-mini-tin-collection', 50)
m('obsidian-flames-elite-trainer-box', 260, 'v'); m('obsidian-flames-pokemon-center-elite-trainer-box', 450); m('pokemon-obsidian-flames-booster-display-box', 450)
m('paldea-evolved-elite-trainer-box', 219, 'v'); m('paldea-evolved-pokemon-center-elite-trainer-box', 400); m('pokemon-paldea-evolved-booster-display-box', 460, 'v')
m('pokemon-scarlet-and-violet-base-set-elite-trainer-box-koraidon pokemon-scarlet-and-violet-base-set-elite-trainer-box-miraidon', 200)
m('pokemon-terapagos-ex-ultra-premium-collection', 251, 'v')

# ---- English Sword & Shield (out of print) ----
m('pokemon-crown-zenith-elite-trainer-box', 300, 'v'); m('pokemon-crown-zenith-pokemon-center-elite-trainer-box-plus', 420, 'v')
m('pokemon-crown-zenith-shiny-zacian-premium-figure-collection', 336, 'v'); m('pokemon-crown-zenith-shiny-zamazenta-premium-figure-collection', 320)
m('pokemon-sword-and-shield-charizard-ultra-premium-collection', 580, 'v'); m('pokemon-arceus-vstar-ultra-premium-collection', 377, 'v')
m('pokemon-celebrations-ultra-premium-collection', 1235, 'v')

# ---- Japanese boxes (USD at about ¥153; Japanese retail or SNKRDUNK) ----
m('storm-emeralda-m6-booster-box', 80, 'v'); m('abyss-eye-m5-booster-box', 52, 'v'); m('nihil-zero-booster-box', 55, 'v'); m('mega-symphonia-booster-box', 55, 'v')
m('mega-brave-booster-box', 65, 'v'); m('ninja-spinner-m4-booster-box', 60, 'v'); m('mega-dream-ex-m2a-booster-box', 110); m('inferno-x-booster-box', 90)
m('aura-seeker-booster-box', 70); m('30th-celebration-m6a-booster-box', 180); m('heat-wave-arena-sv9a-booster-box', 75); m('glory-of-team-rocket-sv10-booster-box', 150)
m('terastal-festival-booster-box', 130); m('151-booster-box', 280, 'v')
m('pokemon-japanese-black-bolt-sv11b-booster-box', 127, 'v'); m('pokemon-japanese-white-flare-sv11w-booster-box', 136, 'v')
m('pokemon-japanese-battle-partners-sv9-booster-box', 85); m('pokemon-japanese-super-electric-breaker-sv8-booster-box', 110); m('pokemon-japanese-paradise-dragona-sv7a-booster-box', 100)
m('pokemon-japanese-stellar-miracle-sv7-booster-box', 85); m('pokemon-japanese-night-wanderer-sv6a-booster-box', 110); m('pokemon-japanese-mask-of-change-sv6-booster-box', 90)
m('pokemon-japanese-crimson-haze-sv5a-booster-box', 120); m('pokemon-japanese-wild-force-sv5k-booster-box', 100); m('pokemon-japanese-cyber-judge-sv5m-booster-box', 95)
m('pokemon-japanese-shiny-treasure-ex-sv4a-booster-box', 115, 'v'); m('pokemon-japanese-ancient-roar-sv4k-booster-box pokemon-japanese-future-flash-sv4m-booster-box', 120)
m('pokemon-japanese-raging-surf-sv3a-booster-box', 140); m('pokemon-japanese-ruler-of-the-black-flame-sv3-booster-box', 180)
m('pokemon-japanese-clay-burst-sv2d-booster-box pokemon-japanese-snow-hazard-sv2p-booster-box', 170); m('pokemon-japanese-triplet-beat-sv1a-booster-box', 170)
m('pokemon-japanese-violet-ex-sv1v-booster-box pokemon-japanese-scarlet-ex-sv1s-booster-box', 210)
m('pokemon-japanese-vstar-universe-s12a-booster-box', 190, 'v'); m('pokemon-japanese-lost-abyss-s11-booster-box', 250); m('pokemon-japanese-pokemon-go-s10b-booster-box', 200)
m('pokemon-japanese-eevee-heroes-s6a-booster-box', 750); m('pokemon-japanese-special-deck-set-mega-feraligatr-dragonite-and-gengar', 75)

# ---- English One Piece Card Game (retailer asks and TCGplayer analysis) ----
m('one-piece-op-01-romance-dawn-booster-box', 500); m('one-piece-op-02-paramount-war-booster-box', 350); m('one-piece-op-03-pillars-of-strength-booster-box', 320)
m('one-piece-op-04-kingdoms-of-intrigue-booster-box', 280); m('one-piece-op-05-awakening-of-the-new-era-booster-box', 550); m('one-piece-op-06-wings-of-the-captain-booster-box', 300)
m('one-piece-op-07-500-years-in-the-future-booster-box', 260); m('one-piece-op-08-two-legends-booster-box', 240); m('one-piece-op-09-emperors-in-the-new-world-booster-box', 420)
m('one-piece-op-10-royal-blood-booster-box', 280); m('one-piece-op-11-a-fist-of-divine-speed-booster-box', 300); m('one-piece-op-12-legacy-of-the-master-booster-box', 299, 'v')
m('one-piece-op-13-carrying-on-his-will-booster-box', 459, 'v'); m('one-piece-op14-eb04-the-azure-seas-seven-booster-box', 464, 'v')
m('one-piece-op15-eb04-adventure-on-kamis-island-booster-box', 350); m('one-piece-op-16-the-time-of-battle-booster-box', 225, 'v')
m('one-piece-op-17-the-worlds-strongest-warriors-booster-box', 318, 'v'); m('one-piece-op-18-the-dominance-of-god-booster-box', 160); m('one-piece-op-19-come-in-world-booster-box', 140)
m('one-piece-eb-01-memorial-collection-extra-booster-box', 350); m('one-piece-eb-02-anime-25th-collection-extra-booster-box', 330)
m('one-piece-eb-03-one-piece-heroines-edition-extra-booster-box', 305, 'v'); m('one-piece-eb-05-one-piece-heroines-edition-vol-2-extra-booster-box', 200)
m('one-piece-eb-06-east-blue-extra-booster-box', 140); m('one-piece-prb-01-the-best-premium-booster-box', 450); m('one-piece-prb-02-the-best-vol-2-premium-booster-box', 400, 'v')
m('one-piece-peb-01-the-drawing-of-your-dreams-premium-extra-booster', 170)
m('one-piece-st-32-green-roronoa-zoro-starter-deck one-piece-st-33-blue-kuzan-starter-deck one-piece-st-34-purple-charlotte-katakuri-starter-deck one-piece-st-35-red-black-sabo-starter-deck one-piece-st-36-yellow-eustass-captain-kid-starter-deck', 20, 'v')
m('one-piece-st-37-the-desert-princess-and-the-pirates-starter-deck one-piece-st-38-baroque-works-starter-deck', 16)

# ---- products still in print: priced under Pokémon Center / US retail (MSRP) at wholesale level ----
IN_PRINT_SETS = {'Delta Reign', 'Pitch Black', 'Chaos Rising', 'Perfect Order', 'Phantasmal Flames', 'Mega Evolution', 'Ascended Heroes', '30th Celebration'}
IN_PRINT_NAMES = ('Mega Forces Tin', 'Mega Greninja ex League Battle Deck', 'Unova Heavy Hitters', 'Mega Rayquaza ex Premium Tournament',
                  'Mega Charizard X ex Ultra-Premium', 'Greninja ex Ultra-Premium')
OP_IN_PRINT = ('One Piece OP-13', 'One Piece OP14', 'One Piece OP15', 'One Piece OP-16', 'One Piece OP-17', 'One Piece OP-18', 'One Piece OP-19',
               'One Piece EB-05', 'One Piece EB-06', 'One Piece ST-')
# (name contains, US retail price, share of it we charge) — first match wins
MSRP_RULES = [('Pokémon Center Elite Trainer Box', 59.99, 0.90), ('Elite Trainer Box', 49.99, 0.75), ('Booster Display Box', 161.64, 0.70),
  ('Booster Bundle', 26.94, 0.75), ('Build & Battle', 21.99, 0.75), ('Sleeved Booster Pack', 4.49, 0.75), ('Booster Pack', 4.49, 0.75),
  ('3-Pack Blister', 14.99, 0.75), ('2-Pack Blister', 9.99, 0.75), ('Premium Checklane', 9.99, 0.75), ('Checklane', 4.99, 0.75),
  ('Ultra-Premium Collection', 119.99, 0.80), ('Figure Collection', 29.99, 0.75), ('Binder Collection', 29.99, 0.75), ('Poster Collection', 19.99, 0.75),
  ('Mini Tin', 9.99, 0.75), ('League Battle Deck', 29.99, 0.75), ('Battle Deck', 14.99, 0.75), ('Tin', 26.99, 0.75), ('Ditto Premium', 39.99, 0.75),
  ('Knock Out', 14.99, 0.75), ('Unova Heavy Hitters', 49.99, 0.75), ('Premium Tournament', 59.99, 0.75),
  ('Booster Box', 24 * 4.49, 0.72), ('Starter Deck', 11.99, 0.75)]

def in_print(p):
    if p.get('lang') == 'Japanese' or 'Case' in p['name']: return False
    if p['cat'] == 'onepiece': return p['name'].startswith(OP_IN_PRINT)
    return p.get('set') in IN_PRINT_SETS or any(k in p['name'] for k in IN_PRINT_NAMES)

def retail_rule(p):
    for key, msrp, share in MSRP_RULES:
        if key in p['name']: return msrp, share
    return None

# sealed cases: (unit product, units per case). Priced from the unit, never above buying loose.
CASES = {'delta-reign-factory-sealed-booster-case': ('delta-reign-booster-display-box', 6),
         'delta-reign-factory-sealed-etb-case': ('delta-reign-elite-trainer-box', 10),
         'storm-emeralda-booster-box-case-12-ct': ('storm-emeralda-m6-booster-box', 12),
         '30th-celebration-elite-trainer-box-case-10-ct': ('30th-celebration-elite-trainer-box', 10)}
# off sale until the owner confirms how many collections (and which) are in the case
HIDE = {'pokemon-30th-celebration-upc-factory-sealed-case-configuration'}


def nice(x):
    if x < 100: return round(math.floor((x + 0.01) * 10) / 10 - 0.01, 2)  # 6.39, 52.79 …
    return round(math.floor(x * 2) / 2 - 0.01, 2)                          # 152.49, 152.99 …

def tier_qty(base, moq, step, old, keep=False):
    """the tier quantities: the owner's own tiers for products sold in multiples or kept at the owner's price, a standard set otherwise"""
    if keep or moq > 1 or step > 1:
        qs = sorted({int(t[0]) for t in old if int(t[0]) >= moq} | {moq})
        return qs[:5]
    if base < 15: return [1, 10, 36, 100]
    if base >= 300: return [1, 2, 4, 6]
    return [1, 4, 6, 10, 24]

def ladder(base, moq, step, old, keep=False):
    qs = tier_qty(base, moq, step, old, keep)
    lad = []
    for i, q in enumerate(qs):
        price = base if i == 0 else nice(base * TIER_STEPS[i])
        if lad:
            prev_q, prev = lad[-1]
            floor_ = prev * (q - step) / q          # lowest price at which the tier can't undercut buying one step less
            if price < floor_: price = math.ceil(floor_ * 100) / 100
            if price >= prev: continue               # no real saving left at this quantity: drop the tier
        lad.append([q, price])
    check(lad, step)
    return lad

def check(lad, step):
    """buying more never costs less in total, and every tier is cheaper than the one before"""
    for (q0, p0), (q1, p1) in zip(lad, lad[1:]):
        assert p1 < p0, lad
        assert (q1 - step) * p0 <= q1 * p1 + 0.005, ('cliff', lad)

def at(lad, q):
    p = lad[0][1]
    for t in lad:
        if q >= t[0]: p = t[1]
    return p

def reprice(products, owner_ids=()):
    """products: list of dicts with id, moq, step, ladder. Returns {id: (ladder, market, src, rule)}.
    owner_ids: products priced by the owner — an estimated market figure never raises their price."""
    by = {p['id']: p for p in products}
    out = {}
    for p in products:
        if p['id'] in CASES: continue
        moq, step = int(p['moq']), int(p['step'])
        mk, src = M.get(p['id'], (None, '-'))
        rr = retail_rule(p) if in_print(p) else None
        if rr:
            base = nice(rr[0] * rr[1]); rule = f'{int(round(rr[1] * 100))}% of US retail (in print)'
            if mk and nice(mk * UNDER) < base: base = nice(mk * UNDER); rule = '12% under market'
        elif mk:
            base = nice(mk * UNDER); rule = '12% under market'
        else:
            mk, src = None, '-'
            base = at(p['ladder'], moq); rule = 'owner price kept'
        if p['id'] in owner_ids and src != 'v' and rule != 'owner price kept' and base > at(p['ladder'], moq):
            base = at(p['ladder'], moq); rule = 'owner price kept (already under market)'
        out[p['id']] = (ladder(base, moq, step, p['ladder'], rule.startswith('owner price kept')), mk, src, rule)
    for cid, (uid, n) in CASES.items():
        if cid not in by: continue
        ulad = out[uid][0]
        c = by[cid]
        base = nice(n * at(ulad, n) * CASE_SAVING)
        lad = [[1, base]] + [[q, nice(base * f)] for q, f in [(2, 0.99), (4, 0.98), (6, 0.97)]]
        check(lad, 1)
        for q, price in lad:   # per unit, a case is always cheaper than the same quantity loose
            assert price / n < at(ulad, q * n) + 0.005, (cid, q, price / n, at(ulad, q * n))
        out[cid] = (lad, None, '-', f'case of {n}: {int((1 - CASE_SAVING) * 100)}% under {n} loose')
    return out
