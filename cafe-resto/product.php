<?php
/**
 * MAISON MOKA — Individual Product Detail Page (product.php)
 * Features a Contiguous Purchase Module, sensory profile breakdown,
 * provenance specifications, brewing guide, and related pairings.
 */

declare(strict_types=1);

require_once __DIR__ . '/includes/config.php';
require_once __DIR__ . '/includes/data.php';

$product_id = clean_input($_GET['id'] ?? 'ethiopia-yirgacheffe');
if (!isset($CATALOG[$product_id])) {
    http_response_code(404);
    $product = reset($CATALOG);
    $not_found_notice = true;
} else {
    $product = $CATALOG[$product_id];
    $not_found_notice = false;
}

// Select 3 complementary products
$related_products = [];
foreach ($CATALOG as $item_id => $item) {
    if ($item_id !== $product['id']) {
        $related_products[] = $item;
    }
    if (count($related_products) === 3) {
        break;
    }
}

$page_title = $product['name'];
$page_description = $product['name'] . ' — ' . $product['tagline'] . '. ' . $product['description'];

require_once __DIR__ . '/includes/header.php';
?>

<section class="section-block pdp-section">
    <div class="container">
        <!-- Breadcrumb Navigation -->
        <nav class="breadcrumb-nav" aria-label="Breadcrumb">
            <a href="index.php">Home</a>
            <span aria-hidden="true">/</span>
            <a href="menu.php?category=<?= e($product['category']) ?>"><?= e($product['category_label']) ?></a>
            <span aria-hidden="true">/</span>
            <span aria-current="page"><?= e($product['name']) ?></span>
        </nav>

        <?php if ($not_found_notice): ?>
            <div class="alert-banner alert-warning" role="alert">
                The requested product identifier was not found. Showing our flagship <strong><?= e($product['name']) ?></strong> instead.
            </div>
        <?php endif; ?>

        <!-- Contiguous Purchase Module (Sticky Gallery Left, Purchase & Provenance Right) -->
        <div class="pdp-grid">
            <div class="pdp-gallery-col">
                <div class="media-frame media-frame-4x3 pdp-main-media">
                    <img
                        src="<?= e($product['image']) ?>"
                        alt="<?= e($product['name']) ?> — <?= e($product['tagline']) ?>"
                        referrerPolicy="no-referrer"
                        class="media-img"
                    >
                </div>

                <!-- Sensory Profile Breakdown -->
                <div class="sensory-panel">
                    <h2 class="card-subheading">Sensory &amp; Structure Profile</h2>
                    <div class="sensory-bars">
                        <?php foreach ($product['metrics'] as $metric_label => $metric_score): ?>
                            <div class="sensory-row">
                                <div class="sensory-label-row">
                                    <span><?= e($metric_label) ?></span>
                                    <span class="tabular-nums"><?= (int) $metric_score ?> / 100</span>
                                </div>
                                <div class="sensory-track" role="progressbar" aria-valuenow="<?= (int) $metric_score ?>" aria-valuemin="0" aria-valuemax="100">
                                    <div class="sensory-fill" style="width: <?= (int) $metric_score ?>%;"></div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </div>
            </div>

            <div class="pdp-purchase-col">
                <div class="pdp-purchase-module">
                    <p class="eyebrow-meta">
                        <span><?= e($product['category_label']) ?></span>
                        <span aria-hidden="true">·</span>
                        <span><?= e($product['status_text']) ?></span>
                    </p>

                    <h1 class="pdp-title"><?= e($product['name']) ?></h1>
                    <p class="pdp-tagline"><?= e($product['tagline']) ?></p>

                    <div class="pdp-price-row">
                        <span class="pdp-price tabular-nums" id="pdp-unit-price" data-base-price="<?= e((string) $product['price']) ?>">
                            <?= format_price((float) $product['price']) ?>
                        </span>
                        <span class="pdp-weight-meta"><?= e($product['weight']) ?> · VAT Included</span>
                    </div>

                    <p class="body-prose pdp-description"><?= e($product['description']) ?></p>

                    <!-- Tasting Notes (Unboxed Metadata with Separators) -->
                    <div class="pdp-tasting-block">
                        <span class="meta-heading">Primary Tasting Notes:</span>
                        <p class="tasting-notes-line">
                            <?= e(implode(' · ', $product['tasting_notes'])) ?>
                        </p>
                    </div>

                    <!-- Purchase Form Submitting to cart.php -->
                    <form action="cart.php" method="post" class="pdp-buy-form quick-add-form" data-product-id="<?= e($product['id']) ?>" data-product-name="<?= e($product['name']) ?>" data-product-price="<?= e((string) $product['price']) ?>">
                        <?= csrf_field() ?>
                        <input type="hidden" name="action" value="add">
                        <input type="hidden" name="product_id" value="<?= e($product['id']) ?>">

                        <div class="form-group">
                            <label for="pdp-option" class="form-label"><?= e($product['options_label']) ?></label>
                            <select id="pdp-option" name="option" class="form-select">
                                <?php foreach ($product['options'] as $option): ?>
                                    <option value="<?= e($option) ?>"><?= e($option) ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>

                        <div class="pdp-action-row">
                            <div class="qty-stepper" aria-label="Select Quantity">
                                <button type="button" class="qty-btn" data-qty-change="-1" aria-label="Decrease quantity">&minus;</button>
                                <input
                                    type="number"
                                    id="pdp-quantity"
                                    name="quantity"
                                    value="1"
                                    min="1"
                                    max="20"
                                    class="qty-input tabular-nums"
                                    aria-label="Quantity"
                                >
                                <button type="button" class="qty-btn" data-qty-change="1" aria-label="Increase quantity">&plus;</button>
                            </div>

                            <button type="submit" class="btn btn-primary btn-lg pdp-submit-btn">
                                Add to Shopping Bag — <span id="pdp-live-total" class="tabular-nums"><?= format_price((float) $product['price']) ?></span>
                            </button>
                        </div>
                    </form>

                    <!-- Provenance & Technical Specifications Table -->
                    <dl class="pdp-specs-list">
                        <div class="spec-row">
                            <dt>Origin / Atelier</dt>
                            <dd><?= e($product['origin']) ?></dd>
                        </div>
                        <div class="spec-row">
                            <dt>Elevation / Ingredients</dt>
                            <dd class="tabular-nums"><?= e($product['altitude']) ?></dd>
                        </div>
                        <div class="spec-row">
                            <dt>Processing Method</dt>
                            <dd><?= e($product['process']) ?></dd>
                        </div>
                        <div class="spec-row">
                            <dt>Batch &amp; Harvest</dt>
                            <dd><?= e($product['harvest']) ?></dd>
                        </div>
                        <div class="spec-row">
                            <dt>Preparation &amp; Brewing</dt>
                            <dd><?= e($product['brew_guide']) ?></dd>
                        </div>
                        <div class="spec-row">
                            <dt>Allergen Information</dt>
                            <dd><?= e($product['allergens']) ?></dd>
                        </div>
                    </dl>
                </div>
            </div>
        </div>

        <!-- Complementary Pairings -->
        <div class="related-section">
            <div class="section-header-row">
                <div>
                    <p class="eyebrow-meta">
                        <span>Sommelier &amp; Barista Pairings</span>
                        <span aria-hidden="true">·</span>
                        <span>Curated Accompaniments</span>
                    </p>
                    <h2 class="section-title">Recommended Alongside This Selection</h2>
                </div>
                <a href="menu.php" class="text-action-link">Return to Full Menu &rarr;</a>
            </div>

            <div class="product-grid">
                <?php foreach ($related_products as $rel): ?>
                    <article class="product-card">
                        <a href="product.php?id=<?= e($rel['id']) ?>" class="product-card-media">
                            <img
                                src="<?= e($rel['image']) ?>"
                                alt="<?= e($rel['name']) ?>"
                                referrerPolicy="no-referrer"
                                class="product-card-img"
                                loading="lazy"
                            >
                        </a>
                        <div class="product-card-body">
                            <p class="product-meta-line">
                                <span><?= e($rel['category_label']) ?></span>
                                <span aria-hidden="true">·</span>
                                <span><?= e($rel['weight']) ?></span>
                            </p>
                            <h3 class="product-card-title">
                                <a href="product.php?id=<?= e($rel['id']) ?>"><?= e($rel['name']) ?></a>
                            </h3>
                            <p class="product-card-tagline"><?= e($rel['tagline']) ?></p>
                            <div class="product-card-footer">
                                <span class="product-price tabular-nums"><?= format_price((float) $rel['price']) ?></span>
                                <a href="product.php?id=<?= e($rel['id']) ?>" class="btn btn-secondary btn-sm">View Offering</a>
                            </div>
                        </div>
                    </article>
                <?php endforeach; ?>
            </div>
        </div>
    </div>
</section>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
