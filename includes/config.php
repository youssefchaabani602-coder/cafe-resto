<?php
/**
 * MAISON MOKA — Core Configuration, Session Management & Security Helpers
 * Compatible with PHP 7.4+ / PHP 8.x (XAMPP, Apache, Nginx, PHP Built-in Server)
 */

declare(strict_types=1);

if (session_status() === PHP_SESSION_NONE) {
    session_set_cookie_params([
        'lifetime' => 0,
        'path'     => '/',
        'httponly' => true,
        'samesite' => 'Lax',
    ]);
    session_start();
}

// Initialize session containers if not already set
if (!isset($_SESSION['cart']) || !is_array($_SESSION['cart'])) {
    $_SESSION['cart'] = [];
}

if (!isset($_SESSION['reservations']) || !is_array($_SESSION['reservations'])) {
    $_SESSION['reservations'] = [];
}

if (!isset($_SESSION['inquiries']) || !is_array($_SESSION['inquiries'])) {
    $_SESSION['inquiries'] = [];
}

if (!isset($_SESSION['promo_code'])) {
    $_SESSION['promo_code'] = null;
}

if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

// Site Constants
define('SITE_NAME', 'MAISON MOKA');
define('SITE_TAGLINE', 'Torréfaction Artisanale & Salon de Thé · Paris IIIe');
define('FREE_SHIPPING_THRESHOLD', 45.00);
define('STANDARD_SHIPPING_FEE', 5.50);

/**
 * Escape string for safe HTML output (XSS Prevention)
 */
function e(?string $value): string
{
    return htmlspecialchars((string) $value, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
}

/**
 * Format numeric Euro amount with consistent tabular precision
 */
function format_price(float $amount): string
{
    return '€' . number_format($amount, 2, '.', ',');
}

/**
 * Verify CSRF token submitted with POST requests
 */
function verify_csrf_token(?string $token): bool
{
    if (empty($token) || empty($_SESSION['csrf_token'])) {
        return false;
    }
    return hash_equals($_SESSION['csrf_token'], $token);
}

/**
 * Generate hidden CSRF input field for HTML forms
 */
function csrf_field(): string
{
    return '<input type="hidden" name="csrf_token" value="' . e($_SESSION['csrf_token']) . '">';
}

/**
 * Sanitize single-line text input
 */
function clean_input(?string $value): string
{
    return trim(strip_tags((string) $value));
}

/**
 * Calculate comprehensive shopping cart totals
 */
function get_cart_summary(array $catalog): array
{
    $items = [];
    $subtotal = 0.0;
    $total_quantity = 0;

    foreach ($_SESSION['cart'] as $cart_key => $entry) {
        $product_id = (string) ($entry['product_id'] ?? '');
        if (!isset($catalog[$product_id])) {
            continue;
        }

        $product = $catalog[$product_id];
        $qty = max(1, min(20, (int) ($entry['quantity'] ?? 1)));
        $option = clean_input($entry['option'] ?? ($product['options'][0] ?? 'Standard'));
        $unit_price = (float) $product['price'];
        $line_total = $unit_price * $qty;

        $subtotal += $line_total;
        $total_quantity += $qty;

        $items[] = [
            'cart_key'   => $cart_key,
            'product_id' => $product_id,
            'name'       => $product['name'],
            'category'   => $product['category_label'],
            'image'      => $product['image'],
            'option'     => $option,
            'unit_price' => $unit_price,
            'quantity'   => $qty,
            'line_total' => $line_total,
            'Origin'     => $product['origin'],
            'weight'     => $product['weight'],
        ];
    }

    $discount = 0.0;
    $promo_code = $_SESSION['promo_code'] ?? null;
    if ($promo_code === 'MOKA10' && $subtotal > 0) {
        $discount = round($subtotal * 0.10, 2);
    } elseif ($promo_code === 'BIENVENUE15' && $subtotal >= 35.00) {
        $discount = round($subtotal * 0.15, 2);
    }

    $discounted_subtotal = max(0.0, $subtotal - $discount);
    $shipping = ($discounted_subtotal === 0.0 || $discounted_subtotal >= FREE_SHIPPING_THRESHOLD)
        ? 0.0
        : STANDARD_SHIPPING_FEE;

    $total = $discounted_subtotal + $shipping;
    $remaining_for_free_shipping = max(0.0, FREE_SHIPPING_THRESHOLD - $discounted_subtotal);

    return [
        'items'                       => $items,
        'total_quantity'              => $total_quantity,
        'subtotal'                    => $subtotal,
        'discount'                    => $discount,
        'promo_code'                  => $promo_code,
        'shipping'                    => $shipping,
        'total'                       => $total,
        'remaining_for_free_shipping' => $remaining_for_free_shipping,
    ];
}

/**
 * Add or increment an item in $_SESSION['cart']
 */
function cart_add_item(array $catalog, string $product_id, int $quantity = 1, string $option = ''): bool
{
    if (!isset($catalog[$product_id])) {
        return false;
    }

    $product = $catalog[$product_id];
    $valid_options = $product['options'] ?? ['Standard'];
    if ($option === '' || !in_array($option, $valid_options, true)) {
        $option = $valid_options[0];
    }

    $quantity = max(1, min(20, $quantity));
    $cart_key = $product_id . '__' . md5($option);

    if (isset($_SESSION['cart'][$cart_key])) {
        $_SESSION['cart'][$cart_key]['quantity'] = min(
            20,
            (int) $_SESSION['cart'][$cart_key]['quantity'] + $quantity
        );
    } else {
        $_SESSION['cart'][$cart_key] = [
            'product_id' => $product_id,
            'option'     => $option,
            'quantity'   => $quantity,
            'added_at'   => date('c'),
        ];
    }

    return true;
}
