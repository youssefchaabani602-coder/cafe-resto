<?php
/**
 * MAISON MOKA — Homepage (index.php)
 * Sections:
 * 1. Storefront Hero & Roastery Provenance Strip
 * 2. Featured Roastery & Patisserie Collection
 * 3. Direct-Trade Craftsmanship & Quantified Sourcing Proof
 * 4. Salon de Thé Reservation Showcase
 */

declare(strict_types=1);

require_once __DIR__ . '/includes/config.php';
require_once __DIR__ . '/includes/data.php';

$page_title = '';
$page_description = 'Parisian specialty coffee roastery, artisanal patisserie, online boutique, and table reservation system built with HTML5, CSS3, Vanilla JavaScript, and PHP.';

// Filter featured items (3 primary showcase items)
$featured_products = array_filter($CATALOG, static function (array $item): bool {
    return !empty($item['featured']);
});

require_once __DIR__ . '/includes/header.php';
?>

<!-- Section 1: Storefront Hero -->
<section class="hero-section">
    <div class="container">
        <div class="hero-grid">
            <div class="hero-copy">
                <p class="eyebrow-meta">
                    <span>38 Rue de Turenne · Paris IIIe</span>
                    <span aria-hidden="true">·</span>
                    <span>Est. 2018</span>
                </p>
                <h1 class="hero-headline">
                    High-Altitude Micro-Lots &amp; Haute Pâtisserie in the Heart of Le Marais.
                </h1>
                <p class="hero-lead">
                    Every Tuesday and Friday morning, our Loring convection roaster brings single-estate coffees from Gedeb, Huila, and Antigua to peak clarity—paired in our limestone salon with warm choux craquelin and Charentes-Poitou viennoiserie.
                </p>
                <div class="hero-cta-group">
                    <a href="menu.php" class="btn btn-primary btn-lg">Explore Roastery &amp; Menu</a>
                    <a href="reservation.php" class="btn btn-secondary btn-lg">Reserve a Salon Table</a>
                </div>
                <p class="hero-trust-note">
                    <span>87.5+ SCA Cupping Standard</span>
                    <span aria-hidden="true">·</span>
                    <span>Same-Day Paris Bike Courier</span>
                    <span aria-hidden="true">·</span>
                    <span>Click &amp; Collect in 30 Mins</span>
                </p>
            </div>

            <div class="hero-media">
                <div class="media-frame media-frame-16x9">
                    <img
                        src="assets/images/hero-maison-moka.jpg"
                        alt="Sunlit limestone interior of Maison Moka Paris with brass espresso machine and freshly baked viennoiserie on marble counter"
                        referrerPolicy="no-referrer"
                        class="media-img"
                    >
                </div>
            </div>
        </div>

        <!-- Full-Width 4-Column Provenance & Service Strip -->
        <div class="provenance-strip" aria-label="Roastery Standards and Metrics">
            <div class="provenance-item">
                <span class="provenance-metric tabular-nums">1,650m – 2,300m</span>
                <span class="provenance-label">Strictly High-Grown Arabica Lots</span>
            </div>
            <div class="provenance-item">
                <span class="provenance-metric tabular-nums">100% Farm-Direct</span>
                <span class="provenance-label">Verified Station &amp; Harvest Transparency</span>
            </div>
            <div class="provenance-item">
                <span class="provenance-metric tabular-nums">06:30 Morning Bake</span>
                <span class="provenance-label">AOP Butter Lamination &amp; Fresh Choux</span>
            </div>
            <div class="provenance-item">
                <span class="provenance-metric tabular-nums">Free from €45.00</span>
                <span class="provenance-label">Zero-Emission Paris Courier Dispatch</span>
            </div>
        </div>
    </div>
</section>

<!-- Section 2: Featured Roastery & Patisserie Collection -->
<section class="section-block" id="featured-collection">
    <div class="container">
        <div class="section-header-row">
            <div>
                <p class="eyebrow-meta">
                    <span>Curated Selection</span>
                    <span aria-hidden="true">·</span>
                    <span>Autumn–Winter Harvest</span>
                </p>
                <h2 class="section-title">Signature Roasts &amp; Morning Patisserie</h2>
            </div>
            <a href="menu.php" class="text-action-link">View Full Catalog (<?= count($CATALOG) ?> Offerings) &rarr;</a>
        </div>

        <div class="product-grid">
            <?php foreach ($featured_products as $product): ?>
                <article class="product-card">
                    <a href="product.php?id=<?= e($product['id']) ?>" class="product-card-media" aria-label="View details for <?= e($product['name']) ?>">
                        <img
                            src="<?= e($product['image']) ?>"
                            alt="<?= e($product['name']) ?> — <?= e($product['tagline']) ?>"
                            referrerPolicy="no-referrer"
                            class="product-card-img"
                            loading="lazy"
                        >
                    </a>
                    <div class="product-card-body">
                        <p class="product-meta-line">
                            <span><?= e($product['category_label']) ?></span>
                            <span aria-hidden="true">·</span>
                            <span><?= e($product['weight']) ?></span>
                        </p>
                        <h3 class="product-card-title">
                            <a href="product.php?id=<?= e($product['id']) ?>"><?= e($product['name']) ?></a>
                        </h3>
                        <p class="product-card-tagline"><?= e($product['tagline']) ?></p>

                        <div class="product-card-footer">
                            <span class="product-price tabular-nums"><?= format_price((float) $product['price']) ?></span>
                            <form action="cart.php" method="post" class="quick-add-form" data-product-id="<?= e($product['id']) ?>" data-product-name="<?= e($product['name']) ?>" data-product-price="<?= e((string) $product['price']) ?>">
                                <?= csrf_field() ?>
                                <input type="hidden" name="action" value="add">
                                <input type="hidden" name="product_id" value="<?= e($product['id']) ?>">
                                <input type="hidden" name="option" value="<?= e($product['options'][0]) ?>">
                                <input type="hidden" name="quantity" value="1">
                                <button type="submit" class="btn btn-secondary btn-sm">Add to Bag</button>
                            </form>
                        </div>
                    </div>
                </article>
            <?php endforeach; ?>
        </div>
    </div>
</section>

<!-- Section 3: Craftsmanship, Sourcing & Proof of Quality -->
<section class="section-block section-surface">
    <div class="container">
        <div class="editorial-bento-grid">
            <div class="bento-col-main">
                <p class="eyebrow-meta">
                    <span>Torréfaction &amp; Savoir-Faire</span>
                    <span aria-hidden="true">·</span>
                    <span>Rue de Turenne Atelier</span>
                </p>
                <h2 class="section-title">Precision Roasting Paired with Classical French Patisserie</h2>
                <p class="body-prose">
                    Unlike conventional dark roasts that mask terroir behind carbonized bitterness, Maison Moka roasts each single-origin lot to highlight its natural organic acids and fruit sugars. Every batch is logged with bean-probe telemetry and cupped within 18 hours of roasting.
                </p>

                <ol class="editorial-numbered-list">
                    <li>
                        <h3 class="numbered-item-title">01. Direct Harvest Contracting (+38% Above Fair-Trade Baseline)</h3>
                        <p class="numbered-item-text">
                            We partner directly with washing stations in Gedeb (Ethiopia), Pitalito (Colombia), and Sacatepéquez (Guatemala), paying an average farm-gate premium 38% above commodity benchmarks for 87.5+ point micro-lots.
                        </p>
                    </li>
                    <li>
                        <h3 class="numbered-item-title">02. Custom Water Mineralization &amp; Slow Extraction</h3>
                        <p class="numbered-item-text">
                            Our brew bar utilizes reverse-osmosis water remineralized with magnesium and bicarbonate to 75 ppm total hardness, ensuring balanced extraction across V60 pour-overs and 9-bar espresso shots.
                        </p>
                    </li>
                    <li>
                        <h3 class="numbered-item-title">03. Twice-Daily Patisserie Production</h3>
                        <p class="numbered-item-text">
                            Led by Chef Pâtissier Claire Vasseur, our pastry kitchen laminates Charentes-Poitou AOP butter doughs over 48 hours and pipes roasted Bronte pistachio mousseline fresh for morning and afternoon salon seatings.
                        </p>
                    </li>
                </ol>
            </div>

            <aside class="bento-col-side">
                <div class="proof-card">
                    <p class="eyebrow-meta">
                        <span>Verified Patron &amp; Culinary Review</span>
                        <span aria-hidden="true">·</span>
                        <span>Le Marais Flagship</span>
                    </p>
                    <blockquote class="testimonial-quote">
                        “Switching our morning espresso ritual and weekend pastry orders to Maison Moka transformed how we experience coffee in Paris. Their Halo Beriti Yirgacheffe has the clarity of fine white tea, and the Pistachio Paris-Brest is unmatched in the 3rd arrondissement.”
                    </blockquote>
                    <div class="testimonial-attribution">
                        <strong class="attribution-name">Henri &amp; Camille Delacroix</strong>
                        <span class="attribution-role">Founding Architects, Atelier Delacroix · Weekly Bean Subscribers Since 2022</span>
                    </div>
                </div>

                <div class="roast-schedule-card">
                    <h3 class="card-subheading">Weekly Roastery &amp; Cupping Calendar</h3>
                    <dl class="schedule-list">
                        <div class="schedule-row">
                            <dt>Tuesday · 08:00</dt>
                            <dd>Single-Origin Filter Roast &amp; Dispatch</dd>
                        </div>
                        <div class="schedule-row">
                            <dt>Thursday · 16:00</dt>
                            <dd>Public Guided Cupping (6 Guests Max)</dd>
                        </div>
                        <div class="schedule-row">
                            <dt>Friday · 08:00</dt>
                            <dd>Espresso Blend Roast &amp; Weekend Prep</dd>
                        </div>
                    </dl>
                    <a href="about.php" class="text-action-link">Read Our Full Roastery Philosophy &rarr;</a>
                </div>
            </aside>
        </div>
    </div>
</section>

<!-- Section 4: Salon de Thé Reservation Showcase -->
<section class="section-block">
    <div class="container">
        <div class="salon-showcase-grid">
            <div class="salon-showcase-media">
                <div class="media-frame media-frame-16x9">
                    <img
                        src="assets/images/salon-dining.jpg"
                        alt="Intimate Parisian dining room at Maison Moka with leather banquettes, marble bistro tables, and warm brass lamps"
                        referrerPolicy="no-referrer"
                        class="media-img"
                        loading="lazy"
                    >
                </div>
            </div>
            <div class="salon-showcase-copy">
                <p class="eyebrow-meta">
                    <span>Salon de Thé &amp; Slow Bar</span>
                    <span aria-hidden="true">·</span>
                    <span>Table Reservations</span>
                </p>
                <h2 class="section-title">Reserve Your Table at 38 Rue de Turenne</h2>
                <p class="body-prose">
                    Join us in our warm limestone salon, at the interactive slow-bar counter overlooking our vintage brass roaster, or in the sheltered courtyard garden. Table reservations include priority patisserie selection and guided filter flights.
                </p>
                <p class="salon-meta-details">
                    <span>1 to 8 Guests</span>
                    <span aria-hidden="true">·</span>
                    <span>90-Minute Seating Windows</span>
                    <span aria-hidden="true">·</span>
                    <span>Complimentary Reservation</span>
                </p>
                <div class="hero-cta-group">
                    <a href="reservation.php" class="btn btn-primary">Book a Table Online</a>
                    <a href="contact.php" class="btn btn-secondary">Private Cuppings &amp; Events</a>
                </div>
            </div>
        </div>
    </div>
</section>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
