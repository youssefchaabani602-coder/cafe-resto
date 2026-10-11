<?php
/**
 * MAISON MOKA — About Our Roastery & Patisserie Atelier (about.php)
 * Details the brand story, direct-trade harvest transparency ledger,
 * and culinary leadership.
 */

declare(strict_types=1);

require_once __DIR__ . '/includes/config.php';
require_once __DIR__ . '/includes/data.php';

$page_title = 'Our Story & Direct-Trade Roastery';
$page_description = 'Discover Maison Moka’s direct-trade specialty coffee roastery and artisanal French patisserie atelier at 38 Rue de Turenne in Le Marais, Paris.';

$harvest_ledger = [
    [
        'lot'       => 'HB-204 · Halo Beriti',
        'origin'    => 'Gedeb, Yirgacheffe, Ethiopia',
        'producer'  => 'Halo Beriti Smallholder Cooperative (310 Growers)',
        'altitude'  => '2,200m MASL',
        'sca_score' => '88.75',
        'fob_price' => '$4.85 / lb (+42% vs. Specialty Benchmark)',
    ],
    [
        'lot'       => 'CO-88 · El Diviso Pink Bourbon',
        'origin'    => 'Pitalito, Huila, Colombia',
        'producer'  => 'Finca El Diviso — Nestór & Adrian Lasso',
        'altitude'  => '1,900m MASL',
        'sca_score' => '89.25',
        'fob_price' => '$5.40 / lb (+48% vs. Specialty Benchmark)',
    ],
    [
        'lot'       => 'GT-19 · La Folie Volcanic Bourbon',
        'origin'    => 'Antigua Valley, Guatemala',
        'producer'  => 'Beneficio Bella Vista Estate Lots',
        'altitude'  => '1,750m MASL',
        'sca_score' => '87.50',
        'fob_price' => '$4.10 / lb (+34% vs. Specialty Benchmark)',
    ],
];

require_once __DIR__ . '/includes/header.php';
?>

<section class="page-hero-compact">
    <div class="container">
        <p class="eyebrow-meta">
            <span>Maison Fondée en 2018</span>
            <span aria-hidden="true">·</span>
            <span>38 Rue de Turenne, Paris IIIe</span>
        </p>
        <h1 class="page-title">Where High-Altitude Terroir Meets Haute Pâtisserie</h1>
        <p class="page-subtitle">
            Founded by Head Roaster Antoine Mercier and Chef Pâtissier Claire Vasseur, Maison Moka unites transparent single-origin coffee roasting with the discipline of classical French pastry.
        </p>
    </div>
</section>

<section class="section-block">
    <div class="container">
        <div class="hero-grid">
            <div class="hero-copy">
                <p class="eyebrow-meta">
                    <span>The Atelier</span>
                    <span aria-hidden="true">·</span>
                    <span>Le Marais Architecture</span>
                </p>
                <h2 class="section-title">Restoring a 1924 Limestone Boulangerie for Modern Coffee Craft</h2>
                <p class="body-prose">
                    Housed in a historic 1924 limestone storefront on Rue de Turenne, our atelier was restored using Lutetian stone, oiled French walnut, and unlacquered brass. At the back of the salon stands our 15-kilogram Loring S15 Falcon convection roaster, reducing roasting emissions by 80% compared to conventional cast-iron drum roasters while preserving delicate floral aromatics.
                </p>
                <p class="body-prose">
                    Every morning at 06:30, the scent of caramelizing Charentes-Poitou butter and roasted Bronte pistachios meets the first espresso calibration shots of the day.
                </p>
                <div class="hero-cta-group">
                    <a href="menu.php" class="btn btn-primary">Explore Current Harvest</a>
                    <a href="reservation.php" class="btn btn-secondary">Book a Guided Cupping</a>
                </div>
            </div>

            <div class="hero-media">
                <div class="media-frame media-frame-16x9">
                    <img
                        src="assets/images/hero-maison-moka.jpg"
                        alt="Maison Moka roastery bar and patisserie counter in Paris"
                        referrerPolicy="no-referrer"
                        class="media-img"
                    >
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Transparent Harvest & Direct-Trade Ledger -->
<section class="section-block section-surface">
    <div class="container">
        <div class="section-header-row">
            <div>
                <p class="eyebrow-meta">
                    <span>Sourcing Integrity</span>
                    <span aria-hidden="true">·</span>
                    <span>2026 Harvest Ledger</span>
                </p>
                <h2 class="section-title">Published Farm-Gate &amp; Cupping Transparency</h2>
            </div>
        </div>

        <div class="cart-table-wrap">
            <table class="cart-table">
                <thead>
                    <tr>
                        <th scope="col">Lot Identifier</th>
                        <th scope="col">Origin &amp; Region</th>
                        <th scope="col">Producer / Washing Station</th>
                        <th scope="col" class="num-col">Elevation</th>
                        <th scope="col" class="num-col">SCA Score</th>
                        <th scope="col" class="num-col">Verified FOB Price</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($harvest_ledger as $row): ?>
                        <tr>
                            <td class="mono-inline font-semibold"><?= e($row['lot']) ?></td>
                            <td><?= e($row['origin']) ?></td>
                            <td><?= e($row['producer']) ?></td>
                            <td class="num-col tabular-nums"><?= e($row['altitude']) ?></td>
                            <td class="num-col tabular-nums font-semibold"><?= e($row['sca_score']) ?></td>
                            <td class="num-col tabular-nums"><?= e($row['fob_price']) ?></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</section>

<!-- Atelier Leadership -->
<section class="section-block">
    <div class="container">
        <div class="section-header-row">
            <div>
                <p class="eyebrow-meta">
                    <span>Artisans of the House</span>
                    <span aria-hidden="true">·</span>
                    <span>Leadership</span>
                </p>
                <h2 class="section-title">The Founders Behind Maison Moka</h2>
            </div>
        </div>

        <div class="founders-grid">
            <article class="founder-card">
                <p class="eyebrow-meta">Head of Coffee &amp; Co-Founder</p>
                <h3 class="founder-name">Antoine Mercier</h3>
                <p class="body-prose">
                    A certified Q-Arabica Grader and two-time French Brewers Cup finalist, Antoine spent six harvest seasons working alongside agronomists in East Africa and Colombia before establishing Maison Moka’s roasting program in Le Marais.
                </p>
                <p class="product-meta-line">
                    <span>Q-Arabica Grader</span>
                    <span aria-hidden="true">·</span>
                    <span>Water Chemistry &amp; Convection Profiling</span>
                </p>
            </article>

            <article class="founder-card">
                <p class="eyebrow-meta">Chef Pâtissier &amp; Co-Founder</p>
                <h3 class="founder-name">Claire Vasseur</h3>
                <p class="body-prose">
                    Trained at Place Vendôme and in Lyon’s finest chocolateries, Claire designs desserts with reduced refined sugar and heightened nut, grain, and botanical aromatics so each pastry harmonizes naturally with single-origin coffee.
                </p>
                <p class="product-meta-line">
                    <span>Haute Pâtisserie</span>
                    <span aria-hidden="true">·</span>
                    <span>48-Hour Cold Lamination</span>
                </p>
            </article>
        </div>
    </div>
</section>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
