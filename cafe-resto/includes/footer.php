<?php
/**
 * MAISON MOKA — Reusable Footer Component
 * Includes Le Marais salon hours, navigation links, and transparent demo disclaimer.
 */
?>
    </main>

    <footer class="site-footer">
        <div class="container">
            <div class="footer-grid">
                <div class="footer-col footer-brand-col">
                    <a href="index.php" class="footer-wordmark">MAISON MOKA</a>
                    <p class="footer-address">
                        38 Rue de Turenne, 75003 Paris · Le Marais<br>
                        Torréfaction Artisanale, Pâtisserie &amp; Salon de Thé
                    </p>
                    <p class="footer-meta-line">
                        Direct-Trade Micro-Lots · Loring S15 Falcon Roaster · Daily Laminated Viennoiserie
                    </p>
                </div>

                <div class="footer-col">
                    <h2 class="footer-heading">Boutique &amp; Salon</h2>
                    <ul class="footer-links">
                        <li><a href="menu.php">Full Menu &amp; Roastery Shop</a></li>
                        <li><a href="menu.php?category=beans">Single-Origin Coffee Beans</a></li>
                        <li><a href="menu.php?category=patisserie">Artisanal Patisserie</a></li>
                        <li><a href="menu.php?category=espresso">Espresso &amp; Slow Bar</a></li>
                        <li><a href="reservation.php">Reserve a Salon Table</a></li>
                    </ul>
                </div>

                <div class="footer-col">
                    <h2 class="footer-heading">Maison &amp; Service</h2>
                    <ul class="footer-links">
                        <li><a href="about.php">Our Heritage &amp; Roastery</a></li>
                        <li><a href="contact.php">Contact &amp; Private Cuppings</a></li>
                        <li><a href="cart.php">Shopping Bag</a></li>
                        <li><a href="checkout.php">Demo Order Review</a></li>
                    </ul>
                </div>

                <div class="footer-col">
                    <h2 class="footer-heading">Opening Hours</h2>
                    <dl class="footer-hours">
                        <div class="hours-row">
                            <dt>Tue – Fri</dt>
                            <dd class="tabular-nums">08:00 – 19:00</dd>
                        </div>
                        <div class="hours-row">
                            <dt>Saturday</dt>
                            <dd class="tabular-nums">08:30 – 19:30</dd>
                        </div>
                        <div class="hours-row">
                            <dt>Sunday</dt>
                            <dd class="tabular-nums">09:00 – 18:00</dd>
                        </div>
                        <div class="hours-row">
                            <dt>Monday</dt>
                            <dd>Roastery Production Only</dd>
                        </div>
                    </dl>
                </div>
            </div>

            <div class="footer-bottom">
                <p class="footer-copyright">
                    &copy; <?= date('Y') ?> <?= e(SITE_NAME) ?> Paris. Built with semantic HTML5, CSS3, Vanilla JavaScript &amp; PHP Sessions.
                </p>
                <p class="footer-disclaimer">
                    Demo Environment Notice: Orders, table reservations, and contact inquiries are validated and stored in your active PHP session for demonstration purposes. No real payment cards are charged and no external emails are dispatched.
                </p>
            </div>
        </div>
    </footer>

    <!-- Toast Feedback Region for Vanilla JS Cart & Form Interactions -->
    <div id="toast-container" class="toast-container" aria-live="polite" aria-atomic="true"></div>

    <script src="assets/js/script.js"></script>
</body>
</html>
