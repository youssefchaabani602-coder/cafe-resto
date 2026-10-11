<?php
/**
 * MAISON MOKA — Reusable Header & Top Navigation Component
 * Enforces semantic HTML5 structure and strict 3-zone Top Bar navigation.
 */

require_once __DIR__ . '/config.php';
require_once __DIR__ . '/data.php';

$cart_summary = get_cart_summary($CATALOG);
$current_page = basename($_SERVER['PHP_SELF'] ?? 'index.php');
$page_title_full = isset($page_title) && $page_title !== ''
    ? $page_title . ' — ' . SITE_NAME
    : 'MAISON MOKA — Torréfaction & Salon de Thé';
$page_desc_full = isset($page_description) && $page_description !== ''
    ? $page_description
    : 'Parisian specialty coffee roastery, artisanal patisserie, online boutique, and table reservation system built with HTML5, CSS3, Vanilla JavaScript, and PHP.';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= e($page_title_full) ?></title>
    <meta name="description" content="<?= e($page_desc_full) ?>">
    <meta property="og:title" content="<?= e($page_title_full) ?>">
    <meta property="og:description" content="<?= e($page_desc_full) ?>">
    <meta property="og:type" content="website">
    <meta name="twitter:card" content="summary_large_image">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Cormorant+Garamond:ital,wght@0,500;0,600;0,700;1,400;1,600&family=JetBrains+Mono:wght@400;500&family=Plus+Jakarta+Sans:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="assets/css/style.css">
</head>
<body data-page="<?= e(str_replace('.php', '', $current_page)) ?>">
    <a href="#main-content" class="skip-link">Skip to main content</a>

    <!-- Slim Dismissible Storefront Notice (<= 40px height) -->
    <div class="top-notice-bar" id="top-notice-bar" role="region" aria-label="Roastery Dispatch Notice">
        <div class="container top-notice-inner">
            <p class="top-notice-text">
                Roasted every Tuesday &amp; Friday at 38 Rue de Turenne · Complimentary Paris courier delivery from <?= format_price(FREE_SHIPPING_THRESHOLD) ?> · Code <span class="mono-inline">MOKA10</span> for 10% off first bean order
            </p>
            <button type="button" class="top-notice-close" id="dismiss-notice-btn" aria-label="Dismiss announcement">Dismiss</button>
        </div>
    </div>

    <!-- Strict 3-Zone Top Navigation Bar -->
    <header class="site-header" id="site-header">
        <div class="container header-inner">
            <!-- Zone 1: Single-line Brand Wordmark -->
            <a href="index.php" class="brand-wordmark">MAISON MOKA</a>

            <!-- Zone 2: 5 Single-Line Navigation Links -->
            <nav class="primary-nav" id="primary-nav" aria-label="Main Navigation">
                <a href="menu.php" class="nav-link <?= in_array($current_page, ['menu.php', 'product.php'], true) ? 'is-active' : '' ?>">Menu &amp; Shop</a>
                <a href="reservation.php" class="nav-link <?= $current_page === 'reservation.php' ? 'is-active' : '' ?>">Reservations</a>
                <a href="about.php" class="nav-link <?= $current_page === 'about.php' ? 'is-active' : '' ?>">Our Story</a>
                <a href="contact.php" class="nav-link <?= $current_page === 'contact.php' ? 'is-active' : '' ?>">Contact</a>
                <a href="checkout.php" class="nav-link <?= $current_page === 'checkout.php' ? 'is-active' : '' ?>">Order Review</a>
            </nav>

            <!-- Zone 3: 1 Primary Action -->
            <div class="header-actions">
                <a href="cart.php" class="btn btn-primary header-bag-btn" aria-label="View Shopping Bag">
                    <span>Shopping Bag (<span id="header-cart-count" class="tabular-nums"><?= (int) $cart_summary['total_quantity'] ?></span>) · <span id="header-cart-total" class="tabular-nums"><?= format_price($cart_summary['total']) ?></span></span>
                </a>
                <button type="button" class="mobile-menu-toggle" id="mobile-menu-toggle" aria-expanded="false" aria-controls="primary-nav" aria-label="Toggle navigation menu">
                    Menu
                </button>
            </div>
        </div>
    </header>

    <main id="main-content">
