<?php
/**
 * MAISON MOKA — Demo Checkout & Order Review (checkout.php)
 * Performs server-side validation on customer contact, delivery method,
 * and payment selection, then stores a demo receipt in $_SESSION['last_order'].
 */

declare(strict_types=1);

require_once __DIR__ . '/includes/config.php';
require_once __DIR__ . '/includes/data.php';

$cart = get_cart_summary($CATALOG);
$errors = [];
$order_confirmed = false;
$confirmed_order = null;

$form_data = [
    'full_name'       => clean_input($_POST['full_name'] ?? ''),
    'email'           => clean_input($_POST['email'] ?? ''),
    'phone'           => clean_input($_POST['phone'] ?? ''),
    'fulfillment'     => clean_input($_POST['fulfillment'] ?? 'courier'),
    'address'         => clean_input($_POST['address'] ?? ''),
    'postal_code'     => clean_input($_POST['postal_code'] ?? ''),
    'city'            => clean_input($_POST['city'] ?? 'Paris'),
    'payment_method'  => clean_input($_POST['payment_method'] ?? 'salon_pay'),
    'roast_notes'     => clean_input($_POST['roast_notes'] ?? ''),
];

if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['action'] ?? '') === 'place_order') {
    if (!verify_csrf_token($_POST['csrf_token'] ?? null)) {
        $errors['csrf'] = 'Security token mismatch. Please submit the form again.';
    }

    if (empty($cart['items'])) {
        $errors['cart'] = 'Your shopping bag is empty. Please add at least one item before placing a demo order.';
    }

    if (mb_strlen($form_data['full_name']) < 2) {
        $errors['full_name'] = 'Please enter your full name (at least 2 characters).';
    }

    if (!filter_var($form_data['email'], FILTER_VALIDATE_EMAIL)) {
        $errors['email'] = 'Please enter a valid email address for your order summary.';
    }

    if (!preg_match('/^[0-9+\s().\-]{8,20}$/', $form_data['phone'])) {
        $errors['phone'] = 'Please enter a valid telephone number (8–20 digits).';
    }

    if (!in_array($form_data['fulfillment'], ['courier', 'pickup'], true)) {
        $errors['fulfillment'] = 'Please choose a valid delivery or collection method.';
    }

    if ($form_data['fulfillment'] === 'courier') {
        if (mb_strlen($form_data['address']) < 5) {
            $errors['address'] = 'Street address is required for courier delivery.';
        }
        if (!preg_match('/^[0-9]{5}$/', $form_data['postal_code'])) {
            $errors['postal_code'] = 'Please enter a valid 5-digit French postal code (e.g. 75003).';
        }
    }

    if (!in_array($form_data['payment_method'], ['salon_pay', 'cod_courier', 'demo_card'], true)) {
        $errors['payment_method'] = 'Please select a valid demo payment option.';
    }

    if (empty($errors)) {
        $shipping_fee = ($form_data['fulfillment'] === 'pickup') ? 0.0 : $cart['shipping'];
        $final_total = max(0.0, $cart['subtotal'] - $cart['discount']) + $shipping_fee;

        $confirmed_order = [
            'order_number'   => 'MM-' . date('Ymd') . '-' . random_int(1000, 9999),
            'created_at'     => date('d M Y, H:i'),
            'customer'       => $form_data,
            'items'          => $cart['items'],
            'subtotal'       => $cart['subtotal'],
            'discount'       => $cart['discount'],
            'promo_code'     => $cart['promo_code'],
            'shipping'       => $shipping_fee,
            'total'          => $final_total,
        ];

        $_SESSION['last_order'] = $confirmed_order;
        $_SESSION['cart'] = [];
        $_SESSION['promo_code'] = null;
        $order_confirmed = true;
        $cart = get_cart_summary($CATALOG);
    }
}

$page_title = 'Demo Checkout & Order Review';
$page_description = 'Complete your Maison Moka demo order for Paris courier delivery or Click & Collect at 38 Rue de Turenne.';

require_once __DIR__ . '/includes/header.php';
?>

<section class="page-hero-compact">
    <div class="container">
        <p class="eyebrow-meta">
            <span>Order Verification</span>
            <span aria-hidden="true">·</span>
            <span>Demo Checkout Flow</span>
        </p>
        <h1 class="page-title">Checkout &amp; Order Review</h1>
        <p class="page-subtitle">
            Complete your customer verification details below. This is a demonstration checkout powered by PHP sessions—no real payment card is charged.
        </p>
    </div>
</section>

<section class="section-block">
    <div class="container">
        <?php if ($order_confirmed && $confirmed_order !== null): ?>
            <!-- Confirmed Order Receipt View -->
            <div class="receipt-card" id="order-confirmation-receipt">
                <div class="receipt-header">
                    <div>
                        <p class="eyebrow-meta">
                            <span>Demo Order Confirmed</span>
                            <span aria-hidden="true">·</span>
                            <span>Preparing in Roastery</span>
                        </p>
                        <h2 class="section-title">Order <span class="mono-inline"><?= e($confirmed_order['order_number']) ?></span> Confirmed</h2>
                        <p class="body-prose">
                            Thank you, <strong><?= e($confirmed_order['customer']['full_name']) ?></strong>. Your demo order has been recorded in your active PHP session on <?= e($confirmed_order['created_at']) ?>.
                        </p>
                    </div>
                    <button type="button" class="btn btn-secondary" onclick="window.print()">Print Order Receipt</button>
                </div>

                <div class="receipt-meta-grid">
                    <div class="receipt-meta-block">
                        <span class="meta-heading">Patron Details</span>
                        <p><?= e($confirmed_order['customer']['full_name']) ?></p>
                        <p><?= e($confirmed_order['customer']['email']) ?></p>
                        <p class="tabular-nums"><?= e($confirmed_order['customer']['phone']) ?></p>
                    </div>

                    <div class="receipt-meta-block">
                        <span class="meta-heading">Fulfillment Method</span>
                        <?php if ($confirmed_order['customer']['fulfillment'] === 'pickup'): ?>
                            <p><strong>Click &amp; Collect — 38 Rue de Turenne</strong></p>
                            <p>75003 Paris · Ready at the Slow Bar in 30 mins</p>
                        <?php else: ?>
                            <p><strong>Paris Bike Courier Delivery</strong></p>
                            <p><?= e($confirmed_order['customer']['address']) ?></p>
                            <p><?= e($confirmed_order['customer']['postal_code']) ?> <?= e($confirmed_order['customer']['city']) ?></p>
                        <?php endif; ?>
                    </div>

                    <div class="receipt-meta-block">
                        <span class="meta-heading">Payment Terms (Demo)</span>
                        <p>
                            <?php
                            $pm = $confirmed_order['customer']['payment_method'];
                            if ($pm === 'salon_pay') {
                                echo 'Pay Upon Salon Pickup (Card or Cash)';
                            } elseif ($pm === 'cod_courier') {
                                echo 'Cash / Terminal on Courier Delivery';
                            } else {
                                echo 'Simulated Online Card Authorization (Demo)';
                            }
                            ?>
                        </p>
                        <p class="receipt-demo-note">No real financial transaction was executed.</p>
                    </div>
                </div>

                <table class="cart-table receipt-table">
                    <thead>
                        <tr>
                            <th scope="col">Offering</th>
                            <th scope="col">Preparation</th>
                            <th scope="col" class="num-col">Qty</th>
                            <th scope="col" class="num-col">Line Total</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($confirmed_order['items'] as $item): ?>
                            <tr>
                                <td><strong><?= e($item['name']) ?></strong></td>
                                <td><?= e($item['option']) ?></td>
                                <td class="num-col tabular-nums"><?= (int) $item['quantity'] ?></td>
                                <td class="num-col tabular-nums"><?= format_price((float) $item['line_total']) ?></td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>

                <div class="receipt-totals">
                    <div class="summary-row">
                        <span>Subtotal</span>
                        <span class="tabular-nums"><?= format_price((float) $confirmed_order['subtotal']) ?></span>
                    </div>
                    <?php if ($confirmed_order['discount'] > 0): ?>
                        <div class="summary-row summary-discount-row">
                            <span>Discount (<?= e((string) $confirmed_order['promo_code']) ?>)</span>
                            <span class="tabular-nums">&minus;<?= format_price((float) $confirmed_order['discount']) ?></span>
                        </div>
                    <?php endif; ?>
                    <div class="summary-row">
                        <span>Fulfillment Fee</span>
                        <span class="tabular-nums"><?= format_price((float) $confirmed_order['shipping']) ?></span>
                    </div>
                    <div class="summary-row summary-total-row">
                        <span>Total Verified (Incl. VAT)</span>
                        <span class="tabular-nums"><?= format_price((float) $confirmed_order['total']) ?></span>
                    </div>
                </div>

                <div class="hero-cta-group">
                    <a href="menu.php" class="btn btn-primary">Return to Roastery Boutique</a>
                    <a href="reservation.php" class="btn btn-secondary">Book a Salon Table</a>
                </div>
            </div>

        <?php elseif (empty($cart['items'])): ?>
            <!-- Empty Cart Redirect State + Last Order Recall if Available -->
            <div class="empty-state-box">
                <h2 class="section-title">Your Shopping Bag Is Empty</h2>
                <p class="body-prose">
                    Add single-origin coffee beans, bottled cold brew, or fresh patisserie to your shopping bag before reviewing your order.
                </p>
                <div class="hero-cta-group">
                    <a href="menu.php" class="btn btn-primary">Explore Menu &amp; Shop</a>
                </div>
            </div>

            <?php if (!empty($_SESSION['last_order'])): ?>
                <?php $prev = $_SESSION['last_order']; ?>
                <div class="previous-order-banner">
                    <div>
                        <p class="eyebrow-meta">Most Recent Session Order</p>
                        <h3 class="card-subheading">Order <span class="mono-inline"><?= e($prev['order_number']) ?></span> — <?= format_price((float) $prev['total']) ?></h3>
                        <p class="body-prose">Placed by <?= e($prev['customer']['full_name']) ?> on <?= e($prev['created_at']) ?>.</p>
                    </div>
                </div>
            <?php endif; ?>

        <?php else: ?>
            <!-- Active Checkout Form & Order Review Split -->
            <?php if (!empty($errors)): ?>
                <div class="alert-banner alert-error" role="alert">
                    <strong>Please review the highlighted fields below:</strong>
                    <ul class="error-list">
                        <?php foreach ($errors as $err): ?>
                            <li><?= e($err) ?></li>
                        <?php endforeach; ?>
                    </ul>
                </div>
            <?php endif; ?>

            <div class="checkout-grid">
                <form action="checkout.php" method="post" class="checkout-form" id="checkout-form" novalidate>
                    <?= csrf_field() ?>
                    <input type="hidden" name="action" value="place_order">

                    <fieldset class="form-fieldset">
                        <legend class="fieldset-legend">01. Patron Contact Details</legend>

                        <div class="form-group">
                            <label for="full_name" class="form-label">Full Name <span class="required-mark">*</span></label>
                            <input
                                type="text"
                                id="full_name"
                                name="full_name"
                                value="<?= e($form_data['full_name']) ?>"
                                required
                                minlength="2"
                                placeholder="e.g. Claire Moreau"
                                class="form-input <?= isset($errors['full_name']) ? 'is-invalid' : '' ?>"
                            >
                            <span class="field-error" id="err-full_name"><?= e($errors['full_name'] ?? '') ?></span>
                        </div>

                        <div class="form-row-2col">
                            <div class="form-group">
                                <label for="email" class="form-label">Email Address <span class="required-mark">*</span></label>
                                <input
                                    type="email"
                                    id="email"
                                    name="email"
                                    value="<?= e($form_data['email']) ?>"
                                    required
                                    placeholder="claire.moreau@example.fr"
                                    class="form-input <?= isset($errors['email']) ? 'is-invalid' : '' ?>"
                                >
                                <span class="field-error" id="err-email"><?= e($errors['email'] ?? '') ?></span>
                            </div>

                            <div class="form-group">
                                <label for="phone" class="form-label">Telephone <span class="required-mark">*</span></label>
                                <input
                                    type="tel"
                                    id="phone"
                                    name="phone"
                                    value="<?= e($form_data['phone']) ?>"
                                    required
                                    placeholder="+33 6 12 34 56 78"
                                    class="form-input tabular-nums <?= isset($errors['phone']) ? 'is-invalid' : '' ?>"
                                >
                                <span class="field-error" id="err-phone"><?= e($errors['phone'] ?? '') ?></span>
                            </div>
                        </div>
                    </fieldset>

                    <fieldset class="form-fieldset">
                        <legend class="fieldset-legend">02. Fulfillment &amp; Delivery Address</legend>

                        <div class="radio-card-grid">
                            <label class="radio-card">
                                <input
                                    type="radio"
                                    name="fulfillment"
                                    value="courier"
                                    <?= $form_data['fulfillment'] === 'courier' ? 'checked' : '' ?>
                                >
                                <span class="radio-card-content">
                                    <strong>Paris Bike Courier Delivery</strong>
                                    <span>Same-day dispatch · <?= $cart['shipping'] === 0.0 ? 'Complimentary (€0.00)' : format_price($cart['shipping']) ?></span>
                                </span>
                            </label>

                            <label class="radio-card">
                                <input
                                    type="radio"
                                    name="fulfillment"
                                    value="pickup"
                                    <?= $form_data['fulfillment'] === 'pickup' ? 'checked' : '' ?>
                                >
                                <span class="radio-card-content">
                                    <strong>Click &amp; Collect — Le Marais</strong>
                                    <span>38 Rue de Turenne, 75003 Paris · Complimentary (€0.00)</span>
                                </span>
                            </label>
                        </div>

                        <div id="courier-address-fields" class="<?= $form_data['fulfillment'] === 'pickup' ? 'is-hidden' : '' ?>">
                            <div class="form-group">
                                <label for="address" class="form-label">Street Address &amp; Door Code <span class="required-mark">*</span></label>
                                <input
                                    type="text"
                                    id="address"
                                    name="address"
                                    value="<?= e($form_data['address']) ?>"
                                    placeholder="14 Rue des Francs-Bourgeois, Code 42B"
                                    class="form-input <?= isset($errors['address']) ? 'is-invalid' : '' ?>"
                                >
                                <span class="field-error" id="err-address"><?= e($errors['address'] ?? '') ?></span>
                            </div>

                            <div class="form-row-2col">
                                <div class="form-group">
                                    <label for="postal_code" class="form-label">Postal Code <span class="required-mark">*</span></label>
                                    <input
                                        type="text"
                                        id="postal_code"
                                        name="postal_code"
                                        value="<?= e($form_data['postal_code']) ?>"
                                        placeholder="75003"
                                        maxlength="5"
                                        class="form-input tabular-nums <?= isset($errors['postal_code']) ? 'is-invalid' : '' ?>"
                                    >
                                    <span class="field-error" id="err-postal_code"><?= e($errors['postal_code'] ?? '') ?></span>
                                </div>

                                <div class="form-group">
                                    <label for="city" class="form-label">City</label>
                                    <input
                                        type="text"
                                        id="city"
                                        name="city"
                                        value="<?= e($form_data['city']) ?>"
                                        class="form-input"
                                    >
                                </div>
                            </div>
                        </div>
                    </fieldset>

                    <fieldset class="form-fieldset">
                        <legend class="fieldset-legend">03. Demo Payment Verification</legend>

                        <div class="radio-stack">
                            <label class="radio-line">
                                <input type="radio" name="payment_method" value="salon_pay" <?= $form_data['payment_method'] === 'salon_pay' ? 'checked' : '' ?>>
                                <span><strong>Pay at Salon / Upon Collection</strong> — Settle by card or cash at 38 Rue de Turenne</span>
                            </label>
                            <label class="radio-line">
                                <input type="radio" name="payment_method" value="cod_courier" <?= $form_data['payment_method'] === 'cod_courier' ? 'checked' : '' ?>>
                                <span><strong>Payment on Courier Delivery</strong> — Contactless terminal or cash upon receipt</span>
                            </label>
                            <label class="radio-line">
                                <input type="radio" name="payment_method" value="demo_card" <?= $form_data['payment_method'] === 'demo_card' ? 'checked' : '' ?>>
                                <span><strong>Simulated Carte Bancaire (Demo Only)</strong> — Instant session confirmation without real charge</span>
                            </label>
                        </div>

                        <div class="form-group" style="margin-top: 1.25rem;">
                            <label for="roast_notes" class="form-label">Roastery / Patisserie Preparation Notes (Optional)</label>
                            <textarea
                                id="roast_notes"
                                name="roast_notes"
                                rows="2"
                                placeholder="e.g. Gift message on pastry box, specific water recipe question..."
                                class="form-textarea"
                            ><?= e($form_data['roast_notes']) ?></textarea>
                        </div>
                    </fieldset>

                    <button type="submit" class="btn btn-primary btn-lg btn-block">
                        Confirm Demo Order — <span class="tabular-nums" id="checkout-btn-total"><?= format_price($cart['total']) ?></span>
                    </button>
                </form>

                <!-- Right Column: Itemized Review Sidebar -->
                <aside class="checkout-summary-col">
                    <div class="summary-card">
                        <div class="summary-header-flex">
                            <h2 class="card-subheading">Order Review (<span class="tabular-nums"><?= (int) $cart['total_quantity'] ?></span>)</h2>
                            <a href="cart.php" class="text-action-link">Edit Bag</a>
                        </div>

                        <ul class="checkout-item-list">
                            <?php foreach ($cart['items'] as $item): ?>
                                <li class="checkout-item-row">
                                    <div>
                                        <strong class="checkout-item-title"><?= e($item['name']) ?></strong>
                                        <span class="checkout-item-sub"><?= e($item['option']) ?> &times; <?= (int) $item['quantity'] ?></span>
                                    </div>
                                    <span class="tabular-nums"><?= format_price((float) $item['line_total']) ?></span>
                                </li>
                            <?php endforeach; ?>
                        </ul>

                        <dl class="summary-lines">
                            <div class="summary-row">
                                <dt>Subtotal</dt>
                                <dd class="tabular-nums" id="checkout-subtotal" data-val="<?= e((string) ($cart['subtotal'] - $cart['discount'])) ?>"><?= format_price($cart['subtotal']) ?></dd>
                            </div>
                            <?php if ($cart['discount'] > 0): ?>
                                <div class="summary-row summary-discount-row">
                                    <dt>Privilege Code (<?= e((string) $cart['promo_code']) ?>)</dt>
                                    <dd class="tabular-nums">&minus;<?= format_price($cart['discount']) ?></dd>
                                </div>
                            <?php endif; ?>
                            <div class="summary-row">
                                <dt>Fulfillment</dt>
                                <dd class="tabular-nums" id="checkout-shipping" data-courier-fee="<?= e((string) $cart['shipping']) ?>">
                                    <?= $cart['shipping'] === 0.0 ? 'Complimentary (€0.00)' : format_price($cart['shipping']) ?>
                                </dd>
                            </div>
                            <div class="summary-row summary-total-row">
                                <dt>Total Due</dt>
                                <dd class="tabular-nums" id="checkout-total"><?= format_price($cart['total']) ?></dd>
                            </div>
                        </dl>

                        <p class="summary-footnote">
                            Transparent Demo Notice: Submitting this form validates your inputs in PHP and generates an itemized receipt in your browser session.
                        </p>
                    </div>
                </aside>
            </div>
        <?php endif; ?>
    </div>
</section>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
