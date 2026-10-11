<?php
/**
 * MAISON MOKA — Shopping Cart Controller & View (cart.php)
 * Processes POST actions (add, update, remove, clear, promo) in $_SESSION['cart']
 * Supports both standard form redirects and JSON responses for Vanilla JS fetch().
 */

declare(strict_types=1);

require_once __DIR__ . '/includes/config.php';
require_once __DIR__ . '/includes/data.php';

$flash_message = '';
$flash_type = 'success';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $is_ajax = (
        (!empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest') ||
        (isset($_SERVER['HTTP_ACCEPT']) && strpos($_SERVER['HTTP_ACCEPT'], 'application/json') !== false)
    );

    if (!verify_csrf_token($_POST['csrf_token'] ?? null)) {
        if ($is_ajax) {
            header('Content-Type: application/json; charset=UTF-8');
            http_response_code(403);
            echo json_encode(['ok' => false, 'message' => 'Invalid security token. Please refresh the page.']);
            exit;
        }
        $flash_message = 'Security token expired. Please submit the form again.';
        $flash_type = 'error';
    } else {
        $action = clean_input($_POST['action'] ?? '');

        if ($action === 'add') {
            $product_id = clean_input($_POST['product_id'] ?? '');
            $quantity = max(1, min(20, (int) ($_POST['quantity'] ?? 1)));
            $option = clean_input($_POST['option'] ?? '');

            if (cart_add_item($CATALOG, $product_id, $quantity, $option)) {
                $flash_message = 'Added ' . $CATALOG[$product_id]['name'] . ' to your shopping bag.';
                $flash_type = 'success';
            } else {
                $flash_message = 'Unable to locate the selected product in our catalog.';
                $flash_type = 'error';
            }
        } elseif ($action === 'update') {
            $cart_key = clean_input($_POST['cart_key'] ?? '');
            $quantity = (int) ($_POST['quantity'] ?? 1);

            if (isset($_SESSION['cart'][$cart_key])) {
                if ($quantity <= 0) {
                    unset($_SESSION['cart'][$cart_key]);
                    $flash_message = 'Item removed from your shopping bag.';
                } else {
                    $_SESSION['cart'][$cart_key]['quantity'] = min(20, $quantity);
                    $flash_message = 'Updated item quantity in your shopping bag.';
                }
            }
        } elseif ($action === 'remove') {
            $cart_key = clean_input($_POST['cart_key'] ?? '');
            if (isset($_SESSION['cart'][$cart_key])) {
                unset($_SESSION['cart'][$cart_key]);
                $flash_message = 'Selection removed from your shopping bag.';
            }
        } elseif ($action === 'clear') {
            $_SESSION['cart'] = [];
            $_SESSION['promo_code'] = null;
            $flash_message = 'Your shopping bag has been cleared.';
        } elseif ($action === 'apply_promo') {
            $code = strtoupper(clean_input($_POST['promo_code'] ?? ''));
            if ($code === 'MOKA10') {
                $_SESSION['promo_code'] = 'MOKA10';
                $flash_message = 'Code MOKA10 applied: 10% roastery privilege discount active.';
            } elseif ($code === 'BIENVENUE15') {
                $_SESSION['promo_code'] = 'BIENVENUE15';
                $flash_message = 'Code BIENVENUE15 applied: 15% discount on orders of €35.00 or more.';
            } else {
                $flash_message = 'Promo code not recognized. Try MOKA10 for 10% off your order.';
                $flash_type = 'error';
            }
        } elseif ($action === 'remove_promo') {
            $_SESSION['promo_code'] = null;
            $flash_message = 'Promotional code removed.';
        }

        if ($is_ajax) {
            $summary = get_cart_summary($CATALOG);
            header('Content-Type: application/json; charset=UTF-8');
            echo json_encode([
                'ok'             => ($flash_type !== 'error'),
                'message'        => $flash_message,
                'total_quantity' => $summary['total_quantity'],
                'total_formatted'=> format_price($summary['total']),
                'subtotal_formatted' => format_price($summary['subtotal']),
            ]);
            exit;
        }
    }
}

$cart = get_cart_summary($CATALOG);
$page_title = 'Your Shopping Bag';
$page_description = 'Review your selected Maison Moka single-origin coffees, patisserie, and brewing equipment before checkout.';

require_once __DIR__ . '/includes/header.php';
?>

<section class="page-hero-compact">
    <div class="container">
        <p class="eyebrow-meta">
            <span>Maison Moka Boutique</span>
            <span aria-hidden="true">·</span>
            <span>Session-Backed Order Bag</span>
        </p>
        <h1 class="page-title">Your Shopping Bag</h1>
        <p class="page-subtitle">
            <?php if ($cart['remaining_for_free_shipping'] > 0 && $cart['total_quantity'] > 0): ?>
                Add <strong class="tabular-nums"><?= format_price($cart['remaining_for_free_shipping']) ?></strong> more to unlock complimentary zero-emission Paris bike courier delivery.
            <?php elseif ($cart['total_quantity'] > 0): ?>
                Your order qualifies for <strong>Complimentary Paris Courier Delivery</strong>.
            <?php else: ?>
                Your shopping bag is currently empty. Explore our freshly roasted single-origin beans and daily patisserie.
            <?php endif; ?>
        </p>
    </div>
</section>

<section class="section-block">
    <div class="container">
        <?php if ($flash_message !== ''): ?>
            <div class="alert-banner <?= $flash_type === 'error' ? 'alert-error' : 'alert-success' ?>" role="status">
                <?= e($flash_message) ?>
            </div>
        <?php endif; ?>

        <?php if (empty($cart['items'])): ?>
            <div class="empty-state-box">
                <h2 class="section-title">No Offerings in Your Bag Yet</h2>
                <p class="body-prose">
                    Start with our signature Ethiopia Yirgacheffe Halo Beriti or a freshly piped Pistachio Paris-Brest for salon pickup or courier delivery.
                </p>
                <div class="hero-cta-group">
                    <a href="menu.php" class="btn btn-primary">Browse Roastery &amp; Patisserie</a>
                    <a href="reservation.php" class="btn btn-secondary">Reserve a Salon Table</a>
                </div>
            </div>
        <?php else: ?>
            <div class="cart-layout-grid">
                <!-- Left Column: Itemized Cart Table -->
                <div class="cart-items-col">
                    <div class="cart-table-wrap">
                        <table class="cart-table">
                            <thead>
                                <tr>
                                    <th scope="col">Offering &amp; Preparation</th>
                                    <th scope="col" class="num-col">Unit Price</th>
                                    <th scope="col">Quantity</th>
                                    <th scope="col" class="num-col">Subtotal</th>
                                    <th scope="col"><span class="sr-only">Remove</span></th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($cart['items'] as $item): ?>
                                    <tr>
                                        <td>
                                            <div class="cart-item-cell">
                                                <a href="product.php?id=<?= e($item['product_id']) ?>" class="cart-item-thumb">
                                                    <img
                                                        src="<?= e($item['image']) ?>"
                                                        alt="<?= e($item['name']) ?>"
                                                        referrerPolicy="no-referrer"
                                                    >
                                                </a>
                                                <div>
                                                    <a href="product.php?id=<?= e($item['product_id']) ?>" class="cart-item-name">
                                                        <?= e($item['name']) ?>
                                                    </a>
                                                    <p class="cart-item-meta">
                                                        <span><?= e($item['category']) ?></span>
                                                        <span aria-hidden="true">·</span>
                                                        <span><?= e($item['option']) ?></span>
                                                    </p>
                                                </div>
                                            </div>
                                        </td>
                                        <td class="num-col tabular-nums"><?= format_price((float) $item['unit_price']) ?></td>
                                        <td>
                                            <form action="cart.php" method="post" class="cart-qty-form">
                                                <?= csrf_field() ?>
                                                <input type="hidden" name="action" value="update">
                                                <input type="hidden" name="cart_key" value="<?= e($item['cart_key']) ?>">
                                                <div class="qty-stepper qty-stepper-sm">
                                                    <button type="button" class="qty-btn" data-qty-change="-1" data-auto-submit="true" aria-label="Decrease quantity">&minus;</button>
                                                    <input
                                                        type="number"
                                                        name="quantity"
                                                        value="<?= (int) $item['quantity'] ?>"
                                                        min="1"
                                                        max="20"
                                                        class="qty-input tabular-nums"
                                                        aria-label="Quantity for <?= e($item['name']) ?>"
                                                    >
                                                    <button type="button" class="qty-btn" data-qty-change="1" data-auto-submit="true" aria-label="Increase quantity">&plus;</button>
                                                </div>
                                                <noscript>
                                                    <button type="submit" class="btn btn-ghost btn-sm">Update</button>
                                                </noscript>
                                            </form>
                                        </td>
                                        <td class="num-col tabular-nums font-semibold"><?= format_price((float) $item['line_total']) ?></td>
                                        <td class="action-col">
                                            <form action="cart.php" method="post">
                                                <?= csrf_field() ?>
                                                <input type="hidden" name="action" value="remove">
                                                <input type="hidden" name="cart_key" value="<?= e($item['cart_key']) ?>">
                                                <button type="submit" class="text-remove-btn" aria-label="Remove <?= e($item['name']) ?>">Remove</button>
                                            </form>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>

                    <div class="cart-footer-actions">
                        <a href="menu.php" class="btn btn-secondary">&larr; Continue Shopping</a>
                        <form action="cart.php" method="post">
                            <?= csrf_field() ?>
                            <input type="hidden" name="action" value="clear">
                            <button type="submit" class="btn btn-ghost">Clear Entire Bag</button>
                        </form>
                    </div>
                </div>

                <!-- Right Column: Order Summary & Promo Code -->
                <aside class="cart-summary-col">
                    <div class="summary-card">
                        <h2 class="card-subheading">Order Summary</h2>

                        <dl class="summary-lines">
                            <div class="summary-row">
                                <dt>Items Subtotal (<span class="tabular-nums"><?= (int) $cart['total_quantity'] ?></span>)</dt>
                                <dd class="tabular-nums"><?= format_price($cart['subtotal']) ?></dd>
                            </div>

                            <?php if ($cart['discount'] > 0): ?>
                                <div class="summary-row summary-discount-row">
                                    <dt>Privilege Code (<?= e((string) $cart['promo_code']) ?>)</dt>
                                    <dd class="tabular-nums">&minus;<?= format_price($cart['discount']) ?></dd>
                                </div>
                            <?php endif; ?>

                            <div class="summary-row">
                                <dt>Paris Courier / Collection</dt>
                                <dd class="tabular-nums">
                                    <?= $cart['shipping'] === 0.0 ? 'Complimentary (€0.00)' : format_price($cart['shipping']) ?>
                                </dd>
                            </div>

                            <div class="summary-row summary-total-row">
                                <dt>Estimated Total (Incl. VAT)</dt>
                                <dd class="tabular-nums"><?= format_price($cart['total']) ?></dd>
                            </div>
                        </dl>

                        <!-- Promo Code Form -->
                        <div class="promo-box">
                            <?php if (!empty($cart['promo_code'])): ?>
                                <form action="cart.php" method="post" class="promo-active-row">
                                    <?= csrf_field() ?>
                                    <input type="hidden" name="action" value="remove_promo">
                                    <span class="promo-active-label">Active Code: <strong class="mono-inline"><?= e((string) $cart['promo_code']) ?></strong></span>
                                    <button type="submit" class="text-remove-btn">Remove</button>
                                </form>
                            <?php else: ?>
                                <form action="cart.php" method="post" class="promo-form">
                                    <?= csrf_field() ?>
                                    <input type="hidden" name="action" value="apply_promo">
                                    <label for="promo_code" class="form-label">Roastery Privilege Code</label>
                                    <div class="promo-input-group">
                                        <input
                                            type="text"
                                            id="promo_code"
                                            name="promo_code"
                                            placeholder="Enter MOKA10"
                                            class="form-input"
                                        >
                                        <button type="submit" class="btn btn-secondary">Apply</button>
                                    </div>
                                </form>
                            <?php endif; ?>
                        </div>

                        <a href="checkout.php" class="btn btn-primary btn-lg btn-block">Proceed to Order Review</a>

                        <p class="summary-footnote">
                            Click &amp; Collect at 38 Rue de Turenne is always complimentary when selected during checkout.
                        </p>
                    </div>
                </aside>
            </div>
        <?php endif; ?>
    </div>
</section>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
