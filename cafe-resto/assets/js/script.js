/**
 * MAISON MOKA — Vanilla JavaScript Interactivity (assets/js/script.js)
 * Zero external frameworks. Handles:
 * 1. Mobile navigation drawer & dismissible top notice bar
 * 2. Instant client-side catalog filtering, search, and sorting on menu.php
 * 3. Quantity steppers & live total price calculation on product.php and cart.php
 * 4. Asynchronous "Add to Bag" form enhancements with graceful fallback
 * 5. Client-side validation & dynamic delivery fee calculation on checkout.php, reservation.php, contact.php
 */

(function () {
    'use strict';

    /**
     * Display a non-intrusive toast notification
     */
    function showToast(message) {
        var container = document.getElementById('toast-container');
        if (!container) return;

        var toast = document.createElement('div');
        toast.className = 'toast-message';
        toast.textContent = message;
        container.appendChild(toast);

        window.requestAnimationFrame(function () {
            toast.classList.add('is-visible');
        });

        window.setTimeout(function () {
            toast.classList.remove('is-visible');
            window.setTimeout(function () {
                if (toast.parentNode) {
                    toast.parentNode.removeChild(toast);
                }
            }, 200);
        }, 3200);
    }

    /**
     * Format Euro currency in tabular format
     */
    function formatEuro(amount) {
        return '€' + Number(amount).toFixed(2);
    }

    /**
     * 1. Top Notice Dismissal & Mobile Menu Toggle
     */
    function initHeaderControls() {
        var noticeBar = document.getElementById('top-notice-bar');
        var dismissBtn = document.getElementById('dismiss-notice-btn');

        if (noticeBar && dismissBtn) {
            try {
                if (window.sessionStorage.getItem('moka_notice_dismissed') === '1') {
                    noticeBar.classList.add('is-hidden');
                }
            } catch (e) {
                // Ignore storage restrictions
            }

            dismissBtn.addEventListener('click', function () {
                noticeBar.classList.add('is-hidden');
                try {
                    window.sessionStorage.setItem('moka_notice_dismissed', '1');
                } catch (e) {
                    // Ignore
                }
            });
        }

        var menuToggle = document.getElementById('mobile-menu-toggle');
        var primaryNav = document.getElementById('primary-nav');

        if (menuToggle && primaryNav) {
            menuToggle.addEventListener('click', function () {
                var isOpen = primaryNav.classList.toggle('is-open');
                menuToggle.setAttribute('aria-expanded', isOpen ? 'true' : 'false');
                menuToggle.textContent = isOpen ? 'Close' : 'Menu';
            });
        }
    }

    /**
     * 2. Interactive Catalog Filtering, Search & Sorting (menu.php)
     */
    function initCatalogFilters() {
        var grid = document.getElementById('catalog-product-grid');
        var filterForm = document.getElementById('catalog-filter-form');
        if (!grid || !filterForm) return;

        var cards = Array.prototype.slice.call(grid.querySelectorAll('.product-card'));
        var tabButtons = Array.prototype.slice.call(filterForm.querySelectorAll('[data-filter-category]'));
        var searchInput = document.getElementById('catalog-search');
        var sortSelect = document.getElementById('catalog-sort');
        var countNode = document.getElementById('visible-product-count');
        var emptyState = document.getElementById('catalog-empty-state');
        var resetBtn = document.getElementById('reset-filters-btn');
        var emptyResetBtn = document.getElementById('empty-reset-btn');

        var activeCategory = 'all';
        tabButtons.forEach(function (btn) {
            if (btn.classList.contains('is-active')) {
                activeCategory = btn.getAttribute('data-filter-category') || 'all';
            }
        });

        function applyClientFilters() {
            var query = searchInput ? searchInput.value.trim().toLowerCase() : '';
            var sortVal = sortSelect ? sortSelect.value : 'featured';
            var visibleCount = 0;

            cards.forEach(function (card) {
                var cat = card.getAttribute('data-category') || '';
                var hay = card.getAttribute('data-search') || '';
                var matchCat = (activeCategory === 'all' || cat === activeCategory);
                var matchQuery = (query === '' || hay.indexOf(query) !== -1);

                if (matchCat && matchQuery) {
                    card.classList.remove('is-hidden');
                    visibleCount++;
                } else {
                    card.classList.add('is-hidden');
                }
            });

            // Sort DOM elements cleanly
            var sortedCards = cards.slice().sort(function (a, b) {
                var priceA = parseFloat(a.getAttribute('data-price') || '0');
                var priceB = parseFloat(b.getAttribute('data-price') || '0');
                var nameA = a.getAttribute('data-name') || '';
                var nameB = b.getAttribute('data-name') || '';
                var featA = parseInt(a.getAttribute('data-featured') || '0', 10);
                var featB = parseInt(b.getAttribute('data-featured') || '0', 10);

                if (sortVal === 'price-asc') return priceA - priceB;
                if (sortVal === 'price-desc') return priceB - priceA;
                if (sortVal === 'name-asc') return nameA.localeCompare(nameB);
                return featB - featA;
            });

            sortedCards.forEach(function (card) {
                grid.appendChild(card);
            });

            if (countNode) {
                countNode.textContent = String(visibleCount);
            }

            if (emptyState) {
                if (visibleCount === 0) {
                    emptyState.classList.remove('is-hidden');
                } else {
                    emptyState.classList.add('is-hidden');
                }
            }

            if (resetBtn) {
                if (activeCategory !== 'all' || query !== '') {
                    resetBtn.classList.remove('is-hidden');
                } else {
                    resetBtn.classList.add('is-hidden');
                }
            }
        }

        tabButtons.forEach(function (btn) {
            btn.addEventListener('click', function (e) {
                e.preventDefault();
                activeCategory = btn.getAttribute('data-filter-category') || 'all';
                tabButtons.forEach(function (b) {
                    b.classList.toggle('is-active', b === btn);
                });
                applyClientFilters();
            });
        });

        if (searchInput) {
            searchInput.addEventListener('input', applyClientFilters);
        }

        if (sortSelect) {
            sortSelect.addEventListener('change', applyClientFilters);
        }

        filterForm.addEventListener('submit', function (e) {
            e.preventDefault();
            applyClientFilters();
        });

        function resetAllFilters(e) {
            if (e) e.preventDefault();
            activeCategory = 'all';
            if (searchInput) searchInput.value = '';
            if (sortSelect) sortSelect.value = 'featured';
            tabButtons.forEach(function (b) {
                b.classList.toggle('is-active', b.getAttribute('data-filter-category') === 'all');
            });
            applyClientFilters();
        }

        if (resetBtn) resetBtn.addEventListener('click', resetAllFilters);
        if (emptyResetBtn) emptyResetBtn.addEventListener('click', resetAllFilters);
    }

    /**
     * 3. Quantity Steppers & PDP Live Total Calculation
     */
    function initQuantitySteppers() {
        var steppers = Array.prototype.slice.call(document.querySelectorAll('.qty-stepper'));
        var pdpUnitPriceNode = document.getElementById('pdp-unit-price');
        var pdpLiveTotalNode = document.getElementById('pdp-live-total');

        function updatePdpTotal(qty) {
            if (!pdpUnitPriceNode || !pdpLiveTotalNode) return;
            var basePrice = parseFloat(pdpUnitPriceNode.getAttribute('data-base-price') || '0');
            pdpLiveTotalNode.textContent = formatEuro(basePrice * qty);
        }

        steppers.forEach(function (stepper) {
            var input = stepper.querySelector('.qty-input');
            var buttons = Array.prototype.slice.call(stepper.querySelectorAll('.qty-btn'));
            if (!input) return;

            buttons.forEach(function (btn) {
                btn.addEventListener('click', function () {
                    var delta = parseInt(btn.getAttribute('data-qty-change') || '0', 10);
                    var current = parseInt(input.value || '1', 10);
                    var next = Math.max(1, Math.min(20, current + delta));
                    input.value = String(next);
                    updatePdpTotal(next);

                    if (btn.getAttribute('data-auto-submit') === 'true' && input.form) {
                        input.form.submit();
                    }
                });
            });

            input.addEventListener('change', function () {
                var val = parseInt(input.value || '1', 10);
                if (isNaN(val) || val < 1) val = 1;
                if (val > 20) val = 20;
                input.value = String(val);
                updatePdpTotal(val);
            });
        });
    }

    /**
     * 4. Asynchronous Add-to-Bag Enhancement with Header Badge Update
     */
    function initQuickAddForms() {
        var forms = Array.prototype.slice.call(document.querySelectorAll('.quick-add-form'));
        var countBadge = document.getElementById('header-cart-count');
        var totalBadge = document.getElementById('header-cart-total');

        forms.forEach(function (form) {
            form.addEventListener('submit', function (e) {
                if (!window.fetch || !window.FormData) return;
                e.preventDefault();

                var formData = new FormData(form);
                var submitBtn = form.querySelector('button[type="submit"]');
                var originalText = submitBtn ? submitBtn.innerHTML : '';

                if (submitBtn) {
                    submitBtn.disabled = true;
                }

                fetch('cart.php', {
                    method: 'POST',
                    headers: {
                        'X-Requested-With': 'XMLHttpRequest',
                        'Accept': 'application/json'
                    },
                    body: formData
                })
                    .then(function (res) {
                        return res.json();
                    })
                    .then(function (data) {
                        if (data && data.ok) {
                            if (countBadge) countBadge.textContent = String(data.total_quantity);
                            if (totalBadge) totalBadge.textContent = data.total_formatted;
                            showToast(data.message || 'Added to your shopping bag.');
                        } else {
                            showToast((data && data.message) || 'Could not update shopping bag.');
                        }
                    })
                    .catch(function () {
                        // Fallback to standard form submission if AJAX fails
                        form.submit();
                    })
                    .finally(function () {
                        if (submitBtn) {
                            submitBtn.disabled = false;
                            submitBtn.innerHTML = originalText;
                        }
                    });
            });
        });
    }

    /**
     * 5. Checkout Fulfillment Toggle & Client-Side Form Validation
     */
    function initFormsValidation() {
        // Checkout Form
        var checkoutForm = document.getElementById('checkout-form');
        if (checkoutForm) {
            var fulfillmentRadios = Array.prototype.slice.call(
                checkoutForm.querySelectorAll('input[name="fulfillment"]')
            );
            var addressFields = document.getElementById('courier-address-fields');
            var subtotalEl = document.getElementById('checkout-subtotal');
            var shippingEl = document.getElementById('checkout-shipping');
            var totalEl = document.getElementById('checkout-total');
            var btnTotalEl = document.getElementById('checkout-btn-total');

            function updateFulfillmentUI() {
                var selected = 'courier';
                fulfillmentRadios.forEach(function (r) {
                    if (r.checked) selected = r.value;
                });

                if (addressFields) {
                    addressFields.classList.toggle('is-hidden', selected === 'pickup');
                }

                if (subtotalEl && shippingEl && totalEl) {
                    var discountedSub = parseFloat(subtotalEl.getAttribute('data-val') || '0');
                    var courierFee = parseFloat(shippingEl.getAttribute('data-courier-fee') || '0');
                    var activeShipping = selected === 'pickup' ? 0 : courierFee;
                    var finalTotal = discountedSub + activeShipping;

                    shippingEl.textContent = activeShipping === 0
                        ? 'Complimentary (€0.00)'
                        : formatEuro(activeShipping);
                    totalEl.textContent = formatEuro(finalTotal);
                    if (btnTotalEl) btnTotalEl.textContent = formatEuro(finalTotal);
                }
            }

            fulfillmentRadios.forEach(function (r) {
                r.addEventListener('change', updateFulfillmentUI);
            });

            checkoutForm.addEventListener('submit', function (e) {
                var valid = true;
                var nameInput = document.getElementById('full_name');
                var emailInput = document.getElementById('email');
                var phoneInput = document.getElementById('phone');
                var addressInput = document.getElementById('address');
                var postalInput = document.getElementById('postal_code');

                var selectedFulfillment = 'courier';
                fulfillmentRadios.forEach(function (r) {
                    if (r.checked) selectedFulfillment = r.value;
                });

                function setFieldError(id, msg) {
                    var errNode = document.getElementById('err-' + id);
                    var inputNode = document.getElementById(id);
                    if (errNode) errNode.textContent = msg;
                    if (inputNode) inputNode.classList.toggle('is-invalid', msg !== '');
                    if (msg !== '') valid = false;
                }

                setFieldError('full_name', (!nameInput || nameInput.value.trim().length < 2) ? 'Please enter your full name.' : '');
                setFieldError('email', (!emailInput || !/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(emailInput.value.trim())) ? 'Please enter a valid email address.' : '');
                setFieldError('phone', (!phoneInput || !/^[0-9+\s().\-]{8,20}$/.test(phoneInput.value.trim())) ? 'Please enter a valid phone number.' : '');

                if (selectedFulfillment === 'courier') {
                    setFieldError('address', (!addressInput || addressInput.value.trim().length < 5) ? 'Street address is required for courier delivery.' : '');
                    setFieldError('postal_code', (!postalInput || !/^[0-9]{5}$/.test(postalInput.value.trim())) ? 'Enter a 5-digit French postal code (e.g. 75003).' : '');
                } else {
                    setFieldError('address', '');
                    setFieldError('postal_code', '');
                }

                if (!valid) {
                    e.preventDefault();
                    showToast('Please check the highlighted checkout fields.');
                }
            });
        }

        // Reservation Form
        var resForm = document.getElementById('reservation-form');
        if (resForm) {
            resForm.addEventListener('submit', function (e) {
                var valid = true;
                var nameInput = document.getElementById('guest_name');
                var emailInput = document.getElementById('guest_email');
                var phoneInput = document.getElementById('guest_phone');
                var dateInput = document.getElementById('res_date');

                function setResError(id, msg) {
                    var errNode = document.getElementById('err-' + id);
                    var inputNode = document.getElementById(id);
                    if (errNode) errNode.textContent = msg;
                    if (inputNode) inputNode.classList.toggle('is-invalid', msg !== '');
                    if (msg !== '') valid = false;
                }

                setResError('guest_name', (!nameInput || nameInput.value.trim().length < 2) ? 'Please enter the primary guest name.' : '');
                setResError('guest_email', (!emailInput || !/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(emailInput.value.trim())) ? 'Please enter a valid email address.' : '');
                setResError('guest_phone', (!phoneInput || !/^[0-9+\s().\-]{8,20}$/.test(phoneInput.value.trim())) ? 'Please enter a valid phone number.' : '');

                if (dateInput && dateInput.value) {
                    var parts = dateInput.value.split('-');
                    var chosen = new Date(parseInt(parts[0], 10), parseInt(parts[1], 10) - 1, parseInt(parts[2], 10));
                    if (chosen.getDay() === 1) {
                        setResError('res_date', 'Our salon is closed on Mondays for roastery production. Please select Tue–Sun.');
                    } else {
                        setResError('res_date', '');
                    }
                }

                if (!valid) {
                    e.preventDefault();
                    showToast('Please review the highlighted reservation details.');
                }
            });
        }

        // Contact Form
        var contactForm = document.getElementById('contact-form');
        if (contactForm) {
            contactForm.addEventListener('submit', function (e) {
                var valid = true;
                var nameInput = document.getElementById('name');
                var emailInput = document.getElementById('contact_email');
                var msgInput = document.getElementById('message');

                function setContactError(id, msg) {
                    var errNode = document.getElementById('err-' + id);
                    var inputNode = document.getElementById(id);
                    if (errNode) errNode.textContent = msg;
                    if (inputNode) inputNode.classList.toggle('is-invalid', msg !== '');
                    if (msg !== '') valid = false;
                }

                setContactError('name', (!nameInput || nameInput.value.trim().length < 2) ? 'Please enter your name.' : '');
                setContactError('contact_email', (!emailInput || !/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(emailInput.value.trim())) ? 'Please enter a valid email address.' : '');
                setContactError('message', (!msgInput || msgInput.value.trim().length < 10) ? 'Please enter a message of at least 10 characters.' : '');

                if (!valid) {
                    e.preventDefault();
                    showToast('Please complete all required inquiry fields.');
                }
            });
        }
    }

    document.addEventListener('DOMContentLoaded', function () {
        initHeaderControls();
        initCatalogFilters();
        initQuantitySteppers();
        initQuickAddForms();
        initFormsValidation();
    });
})();
