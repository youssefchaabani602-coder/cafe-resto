<?php
/**
 * MAISON MOKA — Menu & Roastery Catalog (menu.php)
 * Supports server-side filtering via GET parameters (?category=..., ?q=..., ?sort=...)
 * and real-time client-side Vanilla JS filtering, search, and sorting.
 */

declare(strict_types=1);

require_once __DIR__ . '/includes/config.php';
require_once __DIR__ . '/includes/data.php';

$selected_category = clean_input($_GET['category'] ?? 'all');
if (!isset($CATEGORIES[$selected_category])) {
    $selected_category = 'all';
}

$search_query = clean_input($_GET['q'] ?? '');
$sort_order = clean_input($_GET['sort'] ?? 'featured');
if (!in_array($sort_order, ['featured', 'price-asc', 'price-desc', 'name-asc'], true)) {
    $sort_order = 'featured';
}

// Server-side filtering
$filtered_catalog = array_filter($CATALOG, static function (array $item) use ($selected_category, $search_query): bool {
    if ($selected_category !== 'all' && $item['category'] !== $selected_category) {
        return false;
    }

    if ($search_query !== '') {
        $haystack = strtolower(
            $item['name'] . ' ' .
            $item['tagline'] . ' ' .
            $item['origin'] . ' ' .
            implode(' ', $item['tasting_notes'])
        );
        if (strpos($haystack, strtolower($search_query)) === false) {
            return false;
        }
    }

    return true;
});

// Server-side sorting
uasort($filtered_catalog, static function (array $a, array $b) use ($sort_order): int {
    if ($sort_order === 'price-asc') {
        return $a['price'] <=> $b['price'];
    }
    if ($sort_order === 'price-desc') {
        return $b['price'] <=> $a['price'];
    }
    if ($sort_order === 'name-asc') {
        return strcmp($a['name'], $b['name']);
    }
    // Default: featured first
    return ((int) $b['featured']) <=> ((int) $a['featured']);
});

$page_title = 'Menu & Roastery Boutique';
$page_description = 'Browse Maison Moka single-origin coffee beans, slow-bar espresso creations, artisanal Parisian patisserie, and hand-blown brewing equipment.';

require_once __DIR__ . '/includes/header.php';
?>

<section class="page-hero-compact">
    <div class="container">
        <p class="eyebrow-meta">
            <span>Torréfaction &amp; Pâtisserie</span>
            <span aria-hidden="true">·</span>
            <span>Freshly Roasted &amp; Baked in Paris IIIe</span>
        </p>
        <h1 class="page-title">Roastery Boutique &amp; Salon Menu</h1>
        <p class="page-subtitle">
            Order whole or freshly ground micro-lot beans for delivery across France, or pre-order patisserie and espresso beverages for same-day Paris courier or Rue de Turenne collection.
        </p>
    </div>
</section>

<section class="section-block catalog-section">
    <div class="container">
        <!-- Interactive Filter & Search Toolbar (Works both with PHP GET & Vanilla JS) -->
        <form action="menu.php" method="get" class="catalog-toolbar" id="catalog-filter-form">
            <!-- Interactive Category Tabs -->
            <div class="category-tabs" role="tablist" aria-label="Filter offerings by category">
                <?php foreach ($CATEGORIES as $cat_key => $cat_label): ?>
                    <button
                        type="submit"
                        name="category"
                        value="<?= e($cat_key) ?>"
                        data-filter-category="<?= e($cat_key) ?>"
                        class="category-tab-btn <?= $selected_category === $cat_key ? 'is-active' : '' ?>"
                    >
                        <?= e($cat_label) ?>
                    </button>
                <?php endforeach; ?>
            </div>

            <!-- Search & Sort Controls -->
            <div class="catalog-search-sort">
                <div class="search-input-wrap">
                    <label for="catalog-search" class="sr-only">Search by coffee origin, pastry, or tasting note</label>
                    <input
                        type="search"
                        id="catalog-search"
                        name="q"
                        value="<?= e($search_query) ?>"
                        placeholder="Search origin, tasting note, pastry..."
                        class="form-input search-input"
                    >
                </div>

                <div class="sort-select-wrap">
                    <label for="catalog-sort" class="sr-only">Sort products</label>
                    <select id="catalog-sort" name="sort" class="form-select">
                        <option value="featured" <?= $sort_order === 'featured' ? 'selected' : '' ?>>Sort: Featured Selection</option>
                        <option value="price-asc" <?= $sort_order === 'price-asc' ? 'selected' : '' ?>>Price: Low to High</option>
                        <option value="price-desc" <?= $sort_order === 'price-desc' ? 'selected' : '' ?>>Price: High to Low</option>
                        <option value="name-asc" <?= $sort_order === 'name-asc' ? 'selected' : '' ?>>Alphabetical (A–Z)</option>
                    </select>
                </div>

                <noscript>
                    <button type="submit" class="btn btn-secondary btn-sm">Apply Filters</button>
                </noscript>
            </div>
        </form>

        <!-- Results Status Line -->
        <div class="catalog-status-bar">
            <p class="catalog-count-text" id="catalog-count-text">
                Showing <strong class="tabular-nums" id="visible-product-count"><?= count($filtered_catalog) ?></strong> of <span class="tabular-nums"><?= count($CATALOG) ?></span> artisanal offerings
            </p>
            <?php if ($selected_category !== 'all' || $search_query !== ''): ?>
                <a href="menu.php" class="text-action-link" id="reset-filters-link">Reset Filters</a>
            <?php else: ?>
                <button type="button" class="text-action-link is-hidden" id="reset-filters-btn">Reset Filters</button>
            <?php endif; ?>
        </div>

        <!-- Product Grid -->
        <div class="product-grid" id="catalog-product-grid">
            <?php foreach ($filtered_catalog as $product): ?>
                <article
                    class="product-card"
                    data-category="<?= e($product['category']) ?>"
                    data-price="<?= e((string) $product['price']) ?>"
                    data-name="<?= e($product['name']) ?>"
                    data-featured="<?= $product['featured'] ? '1' : '0' ?>"
                    data-search="<?= e(strtolower($product['name'] . ' ' . $product['tagline'] . ' ' . $product['origin'] . ' ' . implode(' ', $product['tasting_notes']))) ?>"
                >
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
                        <!-- Unboxed Metadata with Middot Separators -->
                        <p class="product-meta-line">
                            <span><?= e($product['category_label']) ?></span>
                            <span aria-hidden="true">·</span>
                            <span><?= e($product['origin']) ?></span>
                        </p>

                        <h2 class="product-card-title">
                            <a href="product.php?id=<?= e($product['id']) ?>"><?= e($product['name']) ?></a>
                        </h2>

                        <p class="product-card-tagline"><?= e($product['tagline']) ?></p>

                        <p class="product-tasting-inline">
                            Notes: <?= e(implode(' · ', array_slice($product['tasting_notes'], 0, 3))) ?>
                        </p>

                        <form action="cart.php" method="post" class="card-purchase-form quick-add-form" data-product-id="<?= e($product['id']) ?>" data-product-name="<?= e($product['name']) ?>" data-product-price="<?= e((string) $product['price']) ?>">
                            <?= csrf_field() ?>
                            <input type="hidden" name="action" value="add">
                            <input type="hidden" name="product_id" value="<?= e($product['id']) ?>">
                            <input type="hidden" name="quantity" value="1">

                            <div class="card-option-row">
                                <label for="opt-<?= e($product['id']) ?>" class="sr-only"><?= e($product['options_label']) ?></label>
                                <select id="opt-<?= e($product['id']) ?>" name="option" class="form-select form-select-sm">
                                    <?php foreach ($product['options'] as $opt): ?>
                                        <option value="<?= e($opt) ?>"><?= e($opt) ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </div>

                            <div class="product-card-footer">
                                <div>
                                    <span class="product-price tabular-nums"><?= format_price((float) $product['price']) ?></span>
                                    <span class="product-weight-sub"><?= e($product['weight']) ?></span>
                                </div>
                                <div class="card-btn-group">
                                    <a href="product.php?id=<?= e($product['id']) ?>" class="btn btn-ghost btn-sm">Details</a>
                                    <button type="submit" class="btn btn-primary btn-sm">Add to Bag</button>
                                </div>
                            </div>
                        </form>
                    </div>
                </article>
            <?php endforeach; ?>
        </div>

        <!-- Empty Search State -->
        <div class="empty-state-box <?= count($filtered_catalog) === 0 ? '' : 'is-hidden' ?>" id="catalog-empty-state">
            <h2 class="section-title">No Matching Offerings Found</h2>
            <p class="body-prose">
                We couldn’t find any coffee lots or patisserie matching your current filter criteria. Try clearing your search query or viewing all offerings.
            </p>
            <a href="menu.php" class="btn btn-primary" id="empty-reset-btn">Show All Offerings</a>
        </div>
    </div>
</section>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
