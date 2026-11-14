<?php
/* Starting content for a fresh install: home page text, research-area (category) pages, the Research Hub
   articles, policy pages and FAQ. Copied into data/store.php on first load and edited in admin.php after.

   Formatting (intros, articles, pages): blank line = new paragraph, "## " heading, "- " bullet, "1. " numbered
   step, **bold**, [text](link). Links can be full URLs or shop pages: product:ID, category:KEY, guide:SLUG,
   page:shop|research|library|calculator|faq|shipping|payment|how|contact|about|terms|…
   {min_order} {reply_hours} {hold_hours} {countries} {free_ship} {standard} {express} {standard_days} {express_days}
   {brand} {company} {address} {email} {ship_from} are filled in with current values.
   In articles, a line that is just {calculator} or {library} shows that tool.

   Wording: everything here describes products as research materials. Keep it that way when editing — no
   dosing, no health claims, no "for use in humans". See the Research Use Only page. */
if(!defined('FK_ROOT')){ http_response_code(404); exit; }

return [
  'settings' => [
    'strip_text'    => 'For laboratory research use only · Not for human or veterinary use',
    'hero_title'    => 'Research peptides, tested batch by batch.',
    'hero_lede'     => 'Lyophilized research peptides with the HPLC and mass-spec report for every batch on the product page. Free calculators for reconstitution, molarity and dilution, plus a reference library of sequences, molecular weights and handling notes.',
    'footer_blurb'  => 'Lyophilized research peptides for laboratory and analytical use, with a certificate of analysis for every batch. Not for human or veterinary use.',
    'home_seo_title'=> 'Research Peptides with Batch COAs · Peptide Calculator',
    'home_seo_desc' => 'Lyophilized research peptides with HPLC and mass-spec reports for every batch, a free reconstitution and molarity calculator, and a peptide reference library.',
    'home_intro'    => <<<'MD'
## What we supply

{brand} supplies synthetic peptides for in-vitro and laboratory research. Every product is a lyophilized (freeze-dried) powder in a sealed glass vial, made by solid-phase synthesis and purified by HPLC. Each batch is tested for purity and identity, and its certificate of analysis is posted on the product page.

Products are sold strictly for research use only. They are not drugs, foods, supplements or cosmetics, and must not be used in humans or animals. See our [Research Use Only policy](page:research-use-only).

## Tools for the bench

- The [peptide calculator](page:calculator) works out reconstitution concentrations, molarity and dilutions.
- The [peptide library](page:library) lists sequences, formulas, molecular weights and CAS numbers.
- The [Research Hub](page:research) explains handling, storage and how to read a certificate of analysis.
MD,

    /* the Shipping & Returns page ({rates} = the delivery options and rate tables) */
    'shipping_policy' => <<<'MD'
Every {brand} order ships from {ship_from} with tracking. This page explains how we ship, what it costs, how long it takes and what happens if something goes wrong. The price at checkout is always the final word on shipping for your order.

## Delivery options and rates

{rates}

## Free shipping

Orders with a goods total over **{free_ship}** ship free with {standard} delivery. It's applied automatically at checkout, with no code to enter. Choose {express} and you pay only the difference between {express} and {standard}.

## How we pack

Lyophilized peptides are stable at room temperature for the length of a normal shipment, so they travel without ice packs. Every order is packed to protect the vials:

- vials stay sealed, with crimped caps, exactly as they left quality control
- each vial is cushioned so it can't knock against another
- vials travel inside a sealed bag with a desiccant pack, away from moisture
- the parcel is plain, with no product names on the outside

Put the vials in the freezer or fridge when they arrive. See [peptide storage and stability](guide:peptide-storage-and-stability).

## From order to doorstep

1. **You place your order** and get an email with your order reference straight away.
2. **You pay.** Bitcoin is paid on your order page the moment you order. For other methods, we send payment details within {reply_hours} hours.
3. **Your payment clears** and your order is allocated.
4. **We pack and dispatch** your order within {hold_hours} hours.
5. **We email your tracking number** as soon as the label is created.

Delivery times start when your parcel leaves us, not when you order.

## Customs, duty and import rules

Orders shipped outside {ship_from} clear customs in the destination country. Import duty, taxes and carrier fees are **not included** in our prices and are paid by the buyer.

Rules on importing research chemicals differ from country to country. **It's your responsibility to check that you may import and hold these products where you are** before you order. We declare every parcel honestly, with its true contents and value. We can't mark orders as gifts or declare a lower value.

## If something goes wrong

### Damaged, missing or wrong items

Check your order as soon as it arrives. If a vial is broken or its seal is damaged, an item is missing, or you received the wrong product, email [{email}](mailto:{email}) within **7 days of delivery** with your order reference and photos. We'll replace or refund the affected items and their shipping.

### Lost in transit

If your parcel hasn't arrived 10 working days after the latest delivery estimate, or tracking hasn't moved for 7 working days, email us and we'll open a trace with the carrier. If the carrier confirms it's lost, we'll reship your order or refund it in full.

### Seized by customs

Parcels held or destroyed by customs because the products can't legally be imported to your country are not covered. Check your local rules before ordering.

## Returns

Because we can't verify how a product was stored once it has left us, **we don't accept returns of products that have been delivered**, opened or not. Damaged, wrong and missing items are always put right, as above.

## Refunds

Once we approve a refund, we send it within 5 working days, the same way you paid:

- **Bitcoin and other crypto:** to a wallet address you confirm by email, for the US-dollar value of the refunded items at that day's rate. Network fees come out of the amount sent.
- **Other methods:** back through the same method where it allows refunds, or by bank transfer.

## Cancellations

You can cancel free of charge any time before your order is dispatched: email us with your order reference.

## Questions

### How fast do you ship?

Orders are dispatched within {hold_hours} hours of payment. {standard} delivery takes {standard_days} and {express} takes {express_days} after dispatch.

### Do peptides need to be shipped cold?

No. Lyophilized peptides are stable at room temperature for the duration of shipping. Store them cold as soon as they arrive.

### Is the packaging discreet?

Yes. Parcels are plain, with no product names on the outside. The customs declaration on international parcels lists the true contents.

### Do you offer free shipping?

Yes. Orders over {free_ship} ship free with {standard} delivery. {express} costs only the difference.

### Can I return a product?

Delivered products can't be returned, because their storage can't be verified. Damaged, wrong or missing items are replaced or refunded.

## Contact us

Email [{email}](mailto:{email}) with your order reference and photos where they help. We reply within {reply_hours} hours.

{company} · {address}
MD,
  ],

  /* research areas: h1 and seo_title fall back to the label, seo_desc to the blurb */
  'categories' => [
    'repair' => [
      'label' => 'Repair & Recovery', 'slug' => 'repair-recovery-peptides',
      'blurb' => 'Peptides studied in preclinical models of tissue repair: BPC-157, TB-500 and GHK-Cu.',
      'h1' => 'Repair & recovery research peptides',
      'seo_title' => 'BPC-157, TB-500 & GHK-Cu Research Peptides',
      'seo_desc' => 'BPC-157, TB-500 and GHK-Cu research peptides: lyophilized, HPLC-purified, with a certificate of analysis for every batch. For research use only.',
      'intro' => <<<'MD'
## Peptides in tissue repair research

This group covers peptides that appear in preclinical (cell and animal) research on wound healing, angiogenesis and tissue remodelling. [BPC-157](product:bpc-157-5mg) is a 15-residue fragment of a gastric protein, [TB-500](product:tb-500-5mg) is a synthetic fragment of thymosin β4, and [GHK-Cu](product:ghk-cu-50mg) is the copper complex of a naturally occurring tripeptide.

Sequences, formulas and molecular weights are in the [peptide library](page:library). For research use only.
MD,
    ],
    'gh' => [
      'label' => 'GH Secretagogues', 'slug' => 'growth-hormone-secretagogue-peptides',
      'blurb' => 'Ipamorelin and sermorelin, studied for their action on growth hormone release.',
      'h1' => 'Growth hormone secretagogue research peptides',
      'seo_title' => 'Ipamorelin & Sermorelin Research Peptides',
      'seo_desc' => 'Ipamorelin and sermorelin research peptides, lyophilized and HPLC-tested, with batch certificates of analysis. For laboratory research use only.',
      'intro' => <<<'MD'
## Growth hormone secretagogue research

Growth hormone secretagogues are compounds studied for how they stimulate growth hormone release. [Ipamorelin](product:ipamorelin-5mg) is a synthetic pentapeptide that acts on the ghrelin receptor (GHS-R1a). [Sermorelin](product:sermorelin-5mg) is the first 29 amino acids of growth-hormone-releasing hormone (GHRH 1–29), the shortest fragment with full activity at the GHRH receptor.

Both are used in receptor-binding and endocrine research models. For research use only.
MD,
    ],
    'neuro' => [
      'label' => 'Neuropeptides', 'slug' => 'neuropeptides',
      'blurb' => 'Selank, Semax and DSIP: short synthetic neuropeptides used in neuroscience research.',
      'h1' => 'Neuropeptide research compounds',
      'seo_title' => 'Selank, Semax & DSIP Research Peptides',
      'seo_desc' => 'Selank, Semax and DSIP research neuropeptides with sequences, molecular weights and batch certificates of analysis. For research use only.',
      'intro' => <<<'MD'
## Neuropeptides for research

[Selank](product:selank-5mg) and [Semax](product:semax-5mg) are synthetic heptapeptides developed from natural regulatory peptides: Selank from tuftsin, and Semax from a fragment of adrenocorticotropic hormone (ACTH 4–10) with a Pro-Gly-Pro tail. [DSIP](product:dsip-5mg) (delta sleep-inducing peptide) is a nonapeptide first isolated in sleep research.

They're used in neuroscience and behavioural research models. For research use only.
MD,
    ],
    'longevity' => [
      'label' => 'Longevity & Cellular', 'slug' => 'longevity-cellular-peptides',
      'blurb' => 'MOTS-c, Epitalon and Thymosin α1, used in ageing, mitochondrial and immunology research.',
      'h1' => 'Longevity & cellular research peptides',
      'seo_title' => 'MOTS-c, Epitalon & Thymosin Alpha-1 Research Peptides',
      'seo_desc' => 'MOTS-c, Epitalon and Thymosin Alpha-1 research peptides: lyophilized, HPLC-tested, with a COA for every batch. For laboratory research use only.',
      'intro' => <<<'MD'
## Peptides in ageing and cellular research

[MOTS-c](product:mots-c-10mg) is a 16-residue peptide encoded in mitochondrial DNA, studied in metabolic and mitochondrial research. [Epitalon](product:epitalon-10mg) is a synthetic tetrapeptide (Ala-Glu-Asp-Gly) used in research on telomerase and ageing. [Thymosin α1](product:thymosin-alpha-1-5mg) is a 28-residue N-acetylated peptide from the thymus, studied in immunology.

For research use only.
MD,
    ],
  ],

  /* the Research Hub: articles */
  'guides' => [
    ['slug'=>'what-are-research-peptides', 'updated'=>'2026-11-14',
     'title'=>'What are research peptides?',
     'seo_title'=>'What Are Research Peptides? Synthesis, Purity & Forms',
     'seo_desc'=>'What research peptides are, how they are made by solid-phase synthesis and purified by HPLC, why they come freeze-dried, and what research-use-only means.',
     'body'=><<<'MD'
A peptide is a short chain of amino acids joined by peptide bonds. Chains of roughly 2 to 50 amino acids are usually called peptides; longer chains that fold into a stable structure are proteins. "Research peptides" are synthetic peptides made for laboratory work: receptor studies, assay development, cell culture and analytical reference.

## How peptides are made

Almost all research peptides are made by **solid-phase peptide synthesis (SPPS)**. The first amino acid is anchored to a resin bead and the chain is built one residue at a time, with protecting groups stopping unwanted side reactions. When the chain is complete, it is cleaved from the resin and the protecting groups are removed.

The crude product contains the target peptide plus shorter "deletion" sequences and other by-products, so it is purified by **reversed-phase HPLC**. The purified fractions are then freeze-dried.

## Why peptides come as a powder

Peptides are much more stable dry than in solution. **Lyophilization** (freeze-drying) removes water under vacuum and leaves a light, fluffy "cake" or powder at the bottom of the vial. In that form, most peptides keep for months to years when stored cold and dry. They're reconstituted in a suitable solvent just before use: see [how to reconstitute lyophilized peptides](guide:how-to-reconstitute-lyophilized-peptides).

## Sequence notation

Sequences are written from the N-terminus to the C-terminus, using three-letter (Gly-Glu-Pro…) or one-letter (GEP…) amino acid codes. Modifications are added at the ends: **Ac-** means the N-terminus is acetylated, and **-NH2** means the C-terminus is amidated. These changes alter the molecular weight, so they matter for calculations. The [peptide library](page:library) lists each sequence with its formula and weight.

## Purity and identity

Two tests matter most:

- **HPLC purity**: the share of the sample that is the target peptide.
- **Mass spectrometry**: confirms the molecule has the expected mass, so it's the right peptide.

Both are on every certificate of analysis. See [how to read a peptide certificate of analysis](guide:how-to-read-a-certificate-of-analysis).

## Research use only

Research peptides are not medicines. They haven't been approved as drugs, and they're sold only for in-vitro and laboratory research, not for use in humans or animals. Read our [Research Use Only policy](page:research-use-only).
MD],

    ['slug'=>'how-to-reconstitute-lyophilized-peptides', 'updated'=>'2026-11-14',
     'title'=>'How to reconstitute lyophilized peptides',
     'seo_title'=>'How to Reconstitute Lyophilized Peptides (Lab Guide)',
     'seo_desc'=>'Step-by-step lab guide to reconstituting freeze-dried peptides: choosing a solvent, technique, working out concentration and aliquoting to avoid freeze-thaw.',
     'body'=><<<'MD'
Reconstitution means dissolving a lyophilized peptide in a solvent to make a stock solution of known concentration. Done carefully, it gives accurate, repeatable concentrations and keeps the peptide intact.

## Before you start

- **Let the vial reach room temperature** (about 20–30 minutes) before opening it. Opening a cold vial draws in moisture, which condenses on the powder.
- **Tap the vial gently** so all the powder sits at the bottom.
- **Work cleanly**: wipe the stopper with 70% isopropyl alcohol and use sterile consumables.
- **Read the certificate of analysis** for the peptide's molecular weight and the net peptide content, if stated.

## Choosing a solvent

Start with the mildest solvent that will dissolve the peptide, and test a small amount first if you're unsure.

- **Sterile water** suits most short, charged (hydrophilic) peptides.
- **Bacteriostatic water** (water with 0.9% benzyl alcohol) limits microbial growth in stock solutions that are opened more than once.
- **Dilute acetic acid (0.1–1%)** helps basic peptides, rich in Lys, Arg or His, that resist water.
- **Dilute ammonium hydroxide or bicarbonate buffer** helps acidic peptides, rich in Asp or Glu.
- **A little DMSO**, then water or buffer, for very hydrophobic peptides. Keep the final DMSO level compatible with your assay.

Peptides with Cys, Met or Trp oxidize easily: use degassed solvent and avoid basic pH where you can.

## Step by step

1. **Work out the volume.** Decide the concentration you want and calculate the solvent volume (see below).
2. **Add the solvent slowly down the inside wall** of the vial, not straight onto the powder.
3. **Swirl or roll gently** until the powder dissolves. Don't shake hard or vortex: foaming can denature some peptides.
4. **Check the solution** is clear and free of particles. If it stays cloudy, sonicate briefly in a water bath, or adjust the pH.
5. **Label the vial** with the peptide, concentration, solvent, date and your initials.
6. **Aliquot** the stock into single-use volumes and freeze any you won't use soon.

## Working out the concentration

Concentration is the mass of peptide divided by the volume of solvent. 5 mg in 2 mL gives 2.5 mg/mL, which is 2,500 µg/mL or 2.5 µg/µL. To take 250 µg from that stock, you'd draw 100 µL.

The [peptide calculator](page:calculator) does this for you, along with molarity (µM, mM) from the molecular weight, and C1V1 dilutions:

{calculator}

## After reconstitution

Peptides are far less stable in solution than dry. As a general rule, keep stock solutions at 2–8 °C for short-term use (days to a few weeks, depending on the peptide), and freeze aliquots at −20 °C or below for longer. Avoid repeated freeze-thaw cycles. See [peptide storage and stability](guide:peptide-storage-and-stability).
MD],

    ['slug'=>'peptide-storage-and-stability', 'updated'=>'2026-11-14',
     'title'=>'Peptide storage and stability',
     'seo_title'=>'Peptide Storage & Stability: Temperatures, Freeze-Thaw, Shelf Life',
     'seo_desc'=>'How to store lyophilized and reconstituted peptides: temperatures, moisture, light, freeze-thaw cycles, and which amino acids make a peptide less stable.',
     'body'=><<<'MD'
How you store a peptide decides how long it stays intact. Heat, moisture, light, oxygen and repeated freezing all break peptides down, so the aim is to keep them cold, dry and dark.

## Lyophilized (powder) peptides

- **Long term:** −20 °C, or −80 °C for the longest shelf life. Most peptides keep for years like this.
- **Short term:** 2–8 °C for weeks to months.
- **At room temperature:** stable for days to weeks, which is why they can ship without ice.
- **Keep them dry:** store vials sealed, with a desiccant, and let them warm to room temperature before opening so no condensation forms inside.
- **Keep them dark:** store in the box or an opaque container.

## Reconstituted (solution) peptides

Peptides in solution degrade much faster than dry ones.

- **In the fridge (2–8 °C):** usually days to a few weeks, depending on the peptide and solvent.
- **Frozen (−20 °C or below):** weeks to months.
- **Bacteriostatic water** slows microbial growth in a vial you open repeatedly. It doesn't stop chemical degradation.

## Avoid freeze-thaw cycles

Each freeze and thaw stresses the peptide and can cause aggregation. Split stock solutions into **single-use aliquots** before freezing, and thaw only what you need.

## Sequences that need extra care

Some amino acids make a peptide more fragile:

- **Cys, Met and Trp** oxidize easily. Store under inert gas if you can, and use degassed solvents.
- **Asn and Gln** can deamidate, especially at neutral to basic pH.
- **Asp-Gly and Asp-Pro bonds** are prone to cleavage in acidic conditions.
- **N-terminal Gln** can cyclize to pyroglutamate.

## Signs of degradation

Discolouration, a powder that has become sticky or shrunk (it has absorbed moisture), cloudiness that won't clear, or a change in HPLC profile all suggest the peptide has degraded. If results suddenly drift, a fresh vial is the first thing to check.
MD],

    ['slug'=>'how-to-read-a-certificate-of-analysis', 'updated'=>'2026-11-14',
     'title'=>'How to read a peptide certificate of analysis (COA)',
     'seo_title'=>'How to Read a Peptide Certificate of Analysis (COA)',
     'seo_desc'=>'What each line on a peptide COA means: HPLC purity, mass spec identity, net peptide content, counter-ions, appearance, batch number, and the red flags to look for.',
     'body'=><<<'MD'
A certificate of analysis (COA) is the test report for one specific batch. It tells you what's in the vial and how pure it is. We post the COA for every batch on its product page.

## The header

- **Product name and sequence:** check it matches what you ordered, including any Ac- or -NH2 modifications.
- **Batch or lot number:** must match the label on your vial. A COA for a different batch tells you nothing about yours.
- **Test date and laboratory:** who ran the tests, and when.

## HPLC purity

High-performance liquid chromatography separates the peptide from impurities. The report shows a chromatogram, a trace with peaks, and a purity figure: the area of the main peak as a percentage of all peaks. **≥98%** is a common specification for research peptides.

Look at the chromatogram itself: one dominant, sharp peak is what you want. Several sizeable side peaks mean significant impurities, whatever the headline number says. See [HPLC and mass spectrometry explained](guide:hplc-and-mass-spectrometry-explained).

## Mass spectrometry (identity)

Mass spec measures the mass of the molecule. The observed mass should match the theoretical mass of the sequence, within the instrument's tolerance. Depending on the method, you may see the mass as [M+H]+, or several peaks for different charge states (for example [M+2H]2+ at roughly half the mass, plus one). The [peptide library](page:library) lists theoretical molecular weights.

## Net peptide content

A lyophilized peptide is never 100% peptide by weight. It also holds water and counter-ions (usually trifluoroacetate, TFA, or acetate) from purification. **Net peptide content** is the share of the powder's weight that is actually peptide, often 70–90%.

This is different from purity. A peptide can be 99% pure (almost no other peptides present) with 80% net content (20% of the weight is water and salts). For exact molar work, multiply the weighed mass by the net peptide content.

## Other lines you may see

- **Appearance:** usually "white lyophilized powder".
- **Counter-ion:** TFA or acetate salt.
- **Water content (Karl Fischer)** and **endotoxin** levels, on some reports.

## Red flags

- No batch number, or one that doesn't match your vial
- No chromatogram or mass spectrum, only a number
- A mass that doesn't match the sequence
- The same COA used for several products or years of batches
- No named laboratory or test date
MD],

    ['slug'=>'hplc-and-mass-spectrometry-explained', 'updated'=>'2026-11-14',
     'title'=>'HPLC and mass spectrometry explained',
     'seo_title'=>'HPLC and Mass Spectrometry for Peptides, Explained',
     'seo_desc'=>'How HPLC measures peptide purity and mass spectrometry confirms identity, in plain English, with what to look for on a chromatogram and a mass spectrum.',
     'body'=><<<'MD'
Two analytical methods back up almost every peptide certificate of analysis: HPLC for **purity** and mass spectrometry for **identity**. Together they answer "how much of this is my peptide?" and "is it the right peptide?".

## HPLC: how pure is it?

In reversed-phase high-performance liquid chromatography (RP-HPLC), the sample is pumped through a column packed with a water-repelling material. A solvent gradient, usually water to acetonitrile with a little TFA, is run through it. Each component in the sample sticks to the column to a different degree, so they come off (elute) at different times.

A UV detector, typically at 214–220 nm where peptide bonds absorb, records what comes off. The result is a **chromatogram**: time on the x-axis, signal on the y-axis.

- The **main peak** is the target peptide.
- **Smaller peaks** are impurities, such as deletion sequences missing a residue, or oxidized forms.
- **Purity** = main peak area ÷ total peak area × 100.

## Mass spectrometry: is it the right molecule?

A mass spectrometer turns molecules into ions and measures their mass-to-charge ratio (m/z). Peptides are usually ionized by **electrospray (ESI)** or **MALDI**.

- **MALDI** mostly gives singly charged ions, so you see one main peak at [M+H]+: the molecular weight plus one proton (about 1 Da).
- **ESI** often gives several charge states. A peptide of mass M shows peaks at (M + n) ÷ n for n = 1, 2, 3…, and software deconvolutes them back to M.

The measured mass should match the theoretical mass of the sequence. A mismatch of a whole residue's mass (57 Da for Gly, 71 for Ala and so on) points to a deletion or addition. +16 Da usually means oxidation (often of Met).

## Why you need both

HPLC can't tell you what the main peak *is*, only that it's the biggest. Mass spec can't tell you *how much* of the sample is that molecule. A trustworthy COA has both, for the same batch. See [how to read a certificate of analysis](guide:how-to-read-a-certificate-of-analysis).
MD],

    ['slug'=>'peptide-calculations', 'updated'=>'2026-11-14',
     'title'=>'Peptide calculations: concentration, molarity and dilution',
     'seo_title'=>'Peptide Calculations: Concentration, Molarity & Dilution',
     'seo_desc'=>'Worked examples of peptide lab maths: concentration after reconstitution, mass to molarity with molecular weight, net peptide content, and C1V1 = C2V2 dilutions.',
     'body'=><<<'MD'
Most peptide maths comes down to three formulas. Here they are with worked examples, plus a calculator that does them for you.

{calculator}

## 1. Concentration after reconstitution

**Concentration = mass ÷ volume**

10 mg of peptide dissolved in 2 mL gives 10 ÷ 2 = **5 mg/mL**. Converting units:

- 5 mg/mL = 5,000 µg/mL
- 5 mg/mL = 5 µg/µL

To take a set amount from the stock: **volume = amount ÷ concentration**. For 500 µg from a 5 mg/mL stock: 500 µg ÷ 5,000 µg/mL = 0.1 mL = **100 µL**.

## 2. Mass to molarity

Molar concentration needs the molecular weight (MW, in g/mol). It's on the certificate of analysis and in the [peptide library](page:library).

**Molarity (mol/L) = concentration (g/L) ÷ MW (g/mol)**

5 mg of BPC-157 (MW 1,419.55 g/mol) in 1 mL is 5 g/L. 5 ÷ 1,419.55 = 0.00352 mol/L = **3.52 mM**.

Working the other way, to make 1 mL of a 1 mM solution of Epitalon (MW 390.35): mass = 0.001 mol/L × 0.001 L × 390.35 g/mol = 0.00039 g = **0.39 mg**.

## 3. Correcting for net peptide content

Weighed powder includes water and counter-ions. If the COA gives a net peptide content of 85%, then 5 mg of powder holds 5 × 0.85 = **4.25 mg of peptide**. Use the corrected mass for exact molar work. (When you dissolve the whole vial, the label amount usually refers to the peptide itself: check the COA.)

## 4. Dilutions: C1V1 = C2V2

To dilute a stock (C1) to a working concentration (C2) in a final volume (V2), you need V1 of stock:

**V1 = C2 × V2 ÷ C1**

To make 10 mL of 10 µM from a 3.52 mM stock: V1 = 0.01 mM × 10 mL ÷ 3.52 mM = 0.0284 mL = **28.4 µL** of stock, made up to 10 mL.

## Units at a glance

- 1 mg = 1,000 µg; 1 µg = 1,000 ng
- 1 mL = 1,000 µL
- 1 M = 1,000 mM = 1,000,000 µM
- mg/mL is the same as µg/µL, and the same as g/L
MD],

    ['slug'=>'peptide-glossary', 'updated'=>'2026-11-14',
     'title'=>'Peptide glossary: terms from A to Z',
     'seo_title'=>'Peptide Glossary: Lab and COA Terms Explained',
     'seo_desc'=>'Plain definitions of peptide terms: acetate and TFA salts, amidation, bacteriostatic water, HPLC, lyophilization, net peptide content, SPPS and more.',
     'body'=><<<'MD'
Short definitions of terms you'll meet on product pages, certificates of analysis and in the [Research Hub](page:research).

## A–F

- **Acetylation (Ac-):** an acetyl group on the N-terminus. Adds 42.04 Da.
- **Aliquot:** a measured portion of a solution, usually single-use, frozen separately to avoid freeze-thaw.
- **Amidation (-NH2):** the C-terminal acid is replaced by an amide. Changes the mass by −0.98 Da.
- **Amino acid residue:** one amino acid unit within the chain.
- **Bacteriostatic water:** sterile water with 0.9% benzyl alcohol to limit microbial growth.
- **CAS number:** a unique registry number for a chemical substance.
- **COA (certificate of analysis):** the test report for one batch. See [how to read one](guide:how-to-read-a-certificate-of-analysis).
- **Counter-ion:** the ion paired with the peptide in its salt form, usually TFA or acetate.
- **Deletion sequence:** an impurity missing one or more residues.
- **Freeze-thaw cycle:** one freezing and thawing of a solution. Repeated cycles degrade peptides.

## H–M

- **HPLC:** high-performance liquid chromatography, used to measure purity.
- **Hydrophobic / hydrophilic:** water-repelling / water-attracting. Affects which solvent dissolves a peptide.
- **Lyophilization:** freeze-drying. Leaves the peptide as a stable powder.
- **Mass spectrometry (MS):** measures molecular mass to confirm identity.
- **Molarity (M):** moles per litre. See [peptide calculations](guide:peptide-calculations).
- **Molecular weight (MW):** the mass of one mole of the molecule, in g/mol (or daltons for one molecule).

## N–Z

- **Net peptide content:** the share of the powder's weight that is peptide, after water and counter-ions.
- **N-terminus / C-terminus:** the start (amine end) and end (carboxyl end) of the chain. Sequences are written N to C.
- **Purity:** the share of the sample that is the target peptide, by HPLC peak area.
- **Reconstitution:** dissolving a lyophilized peptide. See [the guide](guide:how-to-reconstitute-lyophilized-peptides).
- **RUO (research use only):** not for diagnostic, therapeutic, human or veterinary use. See our [policy](page:research-use-only).
- **SPPS:** solid-phase peptide synthesis, the standard way to make peptides.
- **TFA (trifluoroacetic acid):** used in purification; often left as the counter-ion.
MD],
  ],

  'pages' => [
    ['slug'=>'about', 'title'=>'About {brand}',
     'seo_title'=>'About {brand}',
     'seo_desc'=>'{brand} supplies lyophilized research peptides with a certificate of analysis for every batch, for laboratory research use only.',
     'body'=><<<'MD'
{brand} supplies synthetic peptides for laboratory and analytical research. We keep a focused range, test every batch, and publish the results.

## What we stand for

- **Every batch tested.** HPLC purity and mass-spec identity, with the certificate of analysis on the product page.
- **Clear information.** Sequences, formulas, molecular weights and CAS numbers in our [peptide library](page:library), and practical handling notes in the [Research Hub](page:research).
- **Research use only.** We sell to researchers, for research. See our [Research Use Only policy](page:research-use-only).

## Contact

{company} · {address} · [{email}](mailto:{email})
MD],

    ['slug'=>'research-use-only', 'title'=>'Research Use Only policy',
     'seo_title'=>'Research Use Only (RUO) Policy',
     'seo_desc'=>'All {brand} products are for in-vitro and laboratory research use only. Not for human or veterinary use, not drugs, foods, supplements or cosmetics.',
     'body'=><<<'MD'
**All products sold by {brand} are for laboratory research use only.**

## What that means

- Products are intended for **in-vitro and laboratory research** by qualified people.
- They are **not for human or veterinary use**: not for injection, ingestion, inhalation or application to the body, and not for administration to animals.
- They are **not drugs, foods, dietary supplements or cosmetics**, and haven't been approved by the FDA or any other regulator for any use.
- Nothing on this website is medical advice, or a claim that any product treats, cures or prevents any condition.

## Who can buy

By ordering, you confirm that you are **21 or older**, that you are buying for legitimate research purposes, and that you will handle, store and dispose of the products safely and lawfully.

## Your responsibilities

- You are responsible for knowing and following the laws that apply where you live and work, including import rules.
- Products must be handled by people trained in laboratory practice, using appropriate protective equipment.
- Products must be kept away from children and anyone not qualified to handle them.

## Our right to refuse

We may cancel any order, and refund it, if we believe products will be used outside research. That includes orders or messages that ask about personal use or dosing. We don't answer questions about using our products in humans or animals.
MD],

    ['slug'=>'terms', 'title'=>'Terms of sale',
     'seo_title'=>'Terms of Sale',
     'seo_desc'=>'Terms of sale for {brand}: research use only, ordering, payment, shipping, returns and liability.',
     'body'=><<<'MD'
These terms apply to every order placed with {company}. By placing an order you accept them.

## Research use only

All products are sold for laboratory research use only, under our [Research Use Only policy](page:research-use-only), which forms part of these terms. You confirm that you are 21 or older and buying for research purposes. Products must not be used in humans or animals.

## Orders and prices

Prices are shown in US dollars, with conversions to other currencies for reference. A contract is formed when we confirm your order by email. We may cancel and refund any order, including if a price was shown in error, a product is unavailable, or we believe the order is not for research.

## Payment

Pay by the method you choose at checkout. Bitcoin is paid on your order page. For other methods, we send payment details within {reply_hours} hours. Orders not paid within {hold_hours} hours may be cancelled.

## Shipping, returns and refunds

Shipping, delivery times, import duties, returns and refunds are covered by our [Shipping & Returns policy](page:shipping). You are responsible for checking that you may import the products into your country.

## Product information

Product information, including sequences, molecular weights and research summaries, is given for reference only. The certificate of analysis for each batch is the definitive record of its tested properties.

## Liability

Products are supplied for research by qualified people. To the extent the law allows, we are not liable for any loss or injury arising from misuse, from use outside research, or from failure to follow safe handling practice. Our total liability for any order is limited to the price paid for it.

## Contact

{company} · {address} · [{email}](mailto:{email})
MD],

    ['slug'=>'privacy-policy', 'title'=>'Privacy policy',
     'seo_title'=>'Privacy Policy',
     'seo_desc'=>'How {brand} collects, uses and protects the personal information you give us when you order.',
     'body'=><<<'MD'
This policy explains what personal information {company} collects and how we use it.

## What we collect

When you order, we collect your name, company (if given), email address, phone number, shipping address and the details of your order. We also record the IP address the order was placed from, to help prevent fraud.

## How we use it

We use your information only to process and ship your order, contact you about it, prevent fraud, and keep the records the law requires. We don't sell your information or use it for advertising.

## Who we share it with

We share only what's needed with the carrier delivering your order and, for card or bank payments, the payment provider. Bitcoin payments are recorded on the public blockchain.

## How long we keep it

We keep order records for as long as tax and accounting law requires, then delete them.

## Your rights

You can ask for a copy of the information we hold about you, or ask us to correct or delete it, by emailing [{email}](mailto:{email}).
MD],
  ],

  'faqs' => [
    ['Are your products for human use?', 'No. Every product is sold for laboratory research use only. They are not for human or veterinary use, and are not drugs, foods, supplements or cosmetics. See our Research Use Only policy.'],
    ['Do you test every batch?', 'Yes. Each batch is tested for purity by HPLC and identity by mass spectrometry, and its certificate of analysis is posted on the product page. The batch number on the COA matches the label on the vial.'],
    ['What form do the peptides come in?', 'As a lyophilized (freeze-dried) powder in a sealed glass vial. Our Research Hub explains how to reconstitute and store them.'],
    ['How should I store peptides when they arrive?', 'Lyophilized peptides keep best in a freezer at −20 °C, sealed and dry, away from light. For short-term use, 2–8 °C is fine. Once reconstituted, keep them cold and avoid repeated freeze-thaw cycles.'],
    ['Can you tell me how much to use?', 'No. We sell for research only and don\'t give dosing or usage advice for humans or animals. Our calculator works out concentrations, molarity and dilutions for laboratory work.'],
    ['How do I pay?', 'Choose a method at checkout. Bitcoin is paid on your order page straight after you order. For other methods, we send the details by email within {reply_hours} hours.'],
    ['How fast do you ship?', 'Orders are dispatched within {hold_hours} hours of payment, with tracking. {standard} takes {standard_days} and {express} takes {express_days}.'],
    ['Do you offer free shipping?', 'Yes. Orders with a goods total over {free_ship} ship free with {standard} delivery, applied automatically at checkout.'],
    ['Do you ship internationally?', 'Yes, to {countries} countries. Import rules for research chemicals vary, so check that you may import the products where you are before ordering.'],
    ['Do you offer quantity discounts?', 'Yes. Every product shows its quantity-break prices, and the lower price is applied automatically in your cart.'],
    ['What if a vial arrives damaged?', 'Email us within 7 days of delivery with your order reference and photos. We replace or refund damaged, wrong and missing items.'],
  ],
];
