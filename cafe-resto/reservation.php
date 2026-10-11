<?php
/**
 * MAISON MOKA — Salon de Thé & Slow Bar Table Reservations (reservation.php)
 * Validates booking requests on server and client, stores reservations in
 * $_SESSION['reservations'], and allows viewing or cancelling active bookings.
 */

declare(strict_types=1);

require_once __DIR__ . '/includes/config.php';
require_once __DIR__ . '/includes/data.php';

$errors = [];
$flash_message = '';
$latest_booking = null;

$default_date = date('Y-m-d', strtotime('+1 day'));

$form = [
    'guest_name'   => clean_input($_POST['guest_name'] ?? ''),
    'guest_email'  => clean_input($_POST['guest_email'] ?? ''),
    'guest_phone'  => clean_input($_POST['guest_phone'] ?? ''),
    'res_date'     => clean_input($_POST['res_date'] ?? $default_date),
    'res_time'     => clean_input($_POST['res_time'] ?? '10:30'),
    'guests'       => (int) ($_POST['guests'] ?? 2),
    'seating_area' => clean_input($_POST['seating_area'] ?? 'salon'),
    'tasting_menu' => clean_input($_POST['tasting_menu'] ?? 'a_la_carte'),
    'notes'        => clean_input($_POST['notes'] ?? ''),
];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!verify_csrf_token($_POST['csrf_token'] ?? null)) {
        $errors['csrf'] = 'Security token expired. Please submit the form again.';
    } else {
        $action = clean_input($_POST['action'] ?? 'book_table');

        if ($action === 'cancel_reservation') {
            $cancel_id = clean_input($_POST['reservation_id'] ?? '');
            foreach ($_SESSION['reservations'] as $idx => $res) {
                if (($res['id'] ?? '') === $cancel_id) {
                    unset($_SESSION['reservations'][$idx]);
                    $_SESSION['reservations'] = array_values($_SESSION['reservations']);
                    $flash_message = 'Reservation ' . $cancel_id . ' has been cancelled from your session.';
                    break;
                }
            }
        } elseif ($action === 'book_table') {
            if (mb_strlen($form['guest_name']) < 2) {
                $errors['guest_name'] = 'Please enter the primary guest name.';
            }

            if (!filter_var($form['guest_email'], FILTER_VALIDATE_EMAIL)) {
                $errors['guest_email'] = 'Please provide a valid email address.';
            }

            if (!preg_match('/^[0-9+\s().\-]{8,20}$/', $form['guest_phone'])) {
                $errors['guest_phone'] = 'Please enter a valid contact phone number.';
            }

            $date_obj = DateTime::createFromFormat('Y-m-d', $form['res_date']);
            $today = new DateTime('today');
            if (!$date_obj || $date_obj < $today) {
                $errors['res_date'] = 'Please select today or a future date for your salon reservation.';
            } elseif ((int) $date_obj->format('N') === 1) {
                $errors['res_date'] = 'On Mondays our Rue de Turenne atelier is closed to the public for production roasting. Please choose Tuesday through Sunday.';
            }

            if (!isset($RESERVATION_SLOTS[$form['res_time']])) {
                $errors['res_time'] = 'Please choose an available seating time.';
            }

            if ($form['guests'] < 1 || $form['guests'] > 8) {
                $errors['guests'] = 'Online reservations accommodate 1 to 8 guests per table.';
            }

            if (!isset($SEATING_AREAS[$form['seating_area']])) {
                $errors['seating_area'] = 'Please select a valid salon seating zone.';
            }

            if (empty($errors)) {
                $latest_booking = [
                    'id'                 => 'RES-' . random_int(10000, 99999),
                    'created_at'         => date('d M Y, H:i'),
                    'guest_name'         => $form['guest_name'],
                    'guest_email'        => $form['guest_email'],
                    'guest_phone'        => $form['guest_phone'],
                    'res_date'           => $form['res_date'],
                    'res_date_formatted' => $date_obj ? $date_obj->format('l, d F Y') : $form['res_date'],
                    'res_time'           => $form['res_time'],
                    'res_time_label'     => $RESERVATION_SLOTS[$form['res_time']],
                    'guests'             => $form['guests'],
                    'seating_area'       => $SEATING_AREAS[$form['seating_area']],
                    'tasting_menu'       => $form['tasting_menu'],
                    'notes'              => $form['notes'],
                ];

                array_unshift($_SESSION['reservations'], $latest_booking);
                $flash_message = 'Table reservation ' . $latest_booking['id'] . ' confirmed in your demo session.';
                // Reset text fields
                $form['notes'] = '';
            }
        }
    }
}

$page_title = 'Salon de Thé Reservations';
$page_description = 'Reserve a table at Maison Moka, 38 Rue de Turenne, Paris IIIe for specialty pour-over flights, espresso pairings, and artisanal patisserie.';

require_once __DIR__ . '/includes/header.php';
?>

<section class="page-hero-compact">
    <div class="container">
        <p class="eyebrow-meta">
            <span>38 Rue de Turenne · Paris IIIe</span>
            <span aria-hidden="true">·</span>
            <span>Tuesday – Sunday</span>
        </p>
        <h1 class="page-title">Reserve a Table at Maison Moka</h1>
        <p class="page-subtitle">
            Book your seating in our limestone salon, at the interactive slow-bar tasting counter, or in our sheltered courtyard garden.
        </p>
    </div>
</section>

<section class="section-block">
    <div class="container">
        <?php if ($flash_message !== ''): ?>
            <div class="alert-banner alert-success" role="status">
                <?= e($flash_message) ?>
            </div>
        <?php endif; ?>

        <?php if (!empty($errors)): ?>
            <div class="alert-banner alert-error" role="alert">
                <strong>Please correct the following reservation details:</strong>
                <ul class="error-list">
                    <?php foreach ($errors as $err): ?>
                        <li><?= e($err) ?></li>
                    <?php endforeach; ?>
                </ul>
            </div>
        <?php endif; ?>

        <div class="reservation-grid">
            <!-- Left Column: Reservation Form -->
            <div class="reservation-form-col">
                <form action="reservation.php" method="post" class="checkout-form" id="reservation-form" novalidate>
                    <?= csrf_field() ?>
                    <input type="hidden" name="action" value="book_table">

                    <fieldset class="form-fieldset">
                        <legend class="fieldset-legend">01. Date, Seating Window &amp; Party Size</legend>

                        <div class="form-row-2col">
                            <div class="form-group">
                                <label for="res_date" class="form-label">Preferred Date (Tue–Sun) <span class="required-mark">*</span></label>
                                <input
                                    type="date"
                                    id="res_date"
                                    name="res_date"
                                    min="<?= date('Y-m-d') ?>"
                                    value="<?= e($form['res_date']) ?>"
                                    required
                                    class="form-input tabular-nums <?= isset($errors['res_date']) ? 'is-invalid' : '' ?>"
                                >
                                <span class="field-error" id="err-res_date"><?= e($errors['res_date'] ?? '') ?></span>
                            </div>

                            <div class="form-group">
                                <label for="guests" class="form-label">Number of Guests <span class="required-mark">*</span></label>
                                <select id="guests" name="guests" class="form-select tabular-nums">
                                    <?php for ($g = 1; $g <= 8; $g++): ?>
                                        <option value="<?= $g ?>" <?= $form['guests'] === $g ? 'selected' : '' ?>>
                                            <?= $g ?> <?= $g === 1 ? 'Guest' : 'Guests' ?>
                                        </option>
                                    <?php endfor; ?>
                                </select>
                            </div>
                        </div>

                        <div class="form-group">
                            <label for="res_time" class="form-label">Seating Time &amp; Service <span class="required-mark">*</span></label>
                            <select id="res_time" name="res_time" class="form-select">
                                <?php foreach ($RESERVATION_SLOTS as $time_key => $time_label): ?>
                                    <option value="<?= e($time_key) ?>" <?= $form['res_time'] === $time_key ? 'selected' : '' ?>>
                                        <?= e($time_label) ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>

                        <div class="form-group">
                            <label for="seating_area" class="form-label">Preferred Salon Zone <span class="required-mark">*</span></label>
                            <select id="seating_area" name="seating_area" class="form-select">
                                <?php foreach ($SEATING_AREAS as $area_key => $area_label): ?>
                                    <option value="<?= e($area_key) ?>" <?= $form['seating_area'] === $area_key ? 'selected' : '' ?>>
                                        <?= e($area_label) ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>

                        <div class="form-group">
                            <label for="tasting_menu" class="form-label">Tasting Experience</label>
                            <select id="tasting_menu" name="tasting_menu" class="form-select">
                                <option value="a_la_carte" <?= $form['tasting_menu'] === 'a_la_carte' ? 'selected' : '' ?>>À la Carte Salon Menu</option>
                                <option value="flight_patisserie" <?= $form['tasting_menu'] === 'flight_patisserie' ? 'selected' : '' ?>>Grand Cru Filter Flight + Signature Patisserie (€24 / Guest)</option>
                                <option value="cupping_session" <?= $form['tasting_menu'] === 'cupping_session' ? 'selected' : '' ?>>Roaster’s Guided Table Cupping (€32 / Guest)</option>
                            </select>
                        </div>
                    </fieldset>

                    <fieldset class="form-fieldset">
                        <legend class="fieldset-legend">02. Guest Contact &amp; Dietary Notes</legend>

                        <div class="form-group">
                            <label for="guest_name" class="form-label">Full Name <span class="required-mark">*</span></label>
                            <input
                                type="text"
                                id="guest_name"
                                name="guest_name"
                                value="<?= e($form['guest_name']) ?>"
                                required
                                placeholder="e.g. Julien Laurent"
                                class="form-input <?= isset($errors['guest_name']) ? 'is-invalid' : '' ?>"
                            >
                            <span class="field-error" id="err-guest_name"><?= e($errors['guest_name'] ?? '') ?></span>
                        </div>

                        <div class="form-row-2col">
                            <div class="form-group">
                                <label for="guest_email" class="form-label">Email Address <span class="required-mark">*</span></label>
                                <input
                                    type="email"
                                    id="guest_email"
                                    name="guest_email"
                                    value="<?= e($form['guest_email']) ?>"
                                    required
                                    placeholder="julien.laurent@example.fr"
                                    class="form-input <?= isset($errors['guest_email']) ? 'is-invalid' : '' ?>"
                                >
                                <span class="field-error" id="err-guest_email"><?= e($errors['guest_email'] ?? '') ?></span>
                            </div>

                            <div class="form-group">
                                <label for="guest_phone" class="form-label">Mobile Telephone <span class="required-mark">*</span></label>
                                <input
                                    type="tel"
                                    id="guest_phone"
                                    name="guest_phone"
                                    value="<?= e($form['guest_phone']) ?>"
                                    required
                                    placeholder="+33 6 98 76 54 32"
                                    class="form-input tabular-nums <?= isset($errors['guest_phone']) ? 'is-invalid' : '' ?>"
                                >
                                <span class="field-error" id="err-guest_phone"><?= e($errors['guest_phone'] ?? '') ?></span>
                            </div>
                        </div>

                        <div class="form-group">
                            <label for="notes" class="form-label">Allergies, Pastry Pre-Requests or Special Occasion</label>
                            <textarea
                                id="notes"
                                name="notes"
                                rows="2"
                                placeholder="e.g. Please set aside 2 Pistachio Paris-Brest, nut-free option for 1 guest..."
                                class="form-textarea"
                            ><?= e($form['notes']) ?></textarea>
                        </div>
                    </fieldset>

                    <button type="submit" class="btn btn-primary btn-lg btn-block">Confirm Table Reservation</button>
                </form>
            </div>

            <!-- Right Column: Salon Atmosphere & Active Session Bookings -->
            <aside class="reservation-info-col">
                <div class="media-frame media-frame-16x9" style="margin-bottom: 1.5rem;">
                    <img
                        src="assets/images/salon-dining.jpg"
                        alt="Maison Moka Salon de Thé interior with leather banquette and warm brass table lamps"
                        referrerPolicy="no-referrer"
                        class="media-img"
                    >
                </div>

                <div class="summary-card">
                    <h2 class="card-subheading">Your Session Reservations (<span class="tabular-nums"><?= count($_SESSION['reservations']) ?></span>)</h2>

                    <?php if (empty($_SESSION['reservations'])): ?>
                        <p class="body-prose" style="margin-bottom: 1rem;">
                            You have no active table reservations in this browser session yet. Completed bookings will appear here immediately with a confirmation code.
                        </p>
                    <?php else: ?>
                        <div class="reservation-list">
                            <?php foreach ($_SESSION['reservations'] as $res): ?>
                                <div class="reservation-ticket">
                                    <div class="ticket-top">
                                        <span class="mono-inline font-semibold"><?= e($res['id']) ?></span>
                                        <span class="tabular-nums"><?= (int) $res['guests'] ?> <?= (int) $res['guests'] === 1 ? 'Guest' : 'Guests' ?></span>
                                    </div>
                                    <p class="ticket-title"><?= e($res['res_date_formatted']) ?> at <span class="tabular-nums"><?= e($res['res_time']) ?></span></p>
                                    <p class="ticket-meta"><?= e($res['seating_area']) ?></p>
                                    <p class="ticket-meta">Reserved for: <strong><?= e($res['guest_name']) ?></strong> (<?= e($res['guest_email']) ?>)</p>
                                    <?php if (!empty($res['notes'])): ?>
                                        <p class="ticket-notes">Note: “<?= e($res['notes']) ?>”</p>
                                    <?php endif; ?>
                                    <form action="reservation.php" method="post" style="margin-top: 0.75rem;">
                                        <?= csrf_field() ?>
                                        <input type="hidden" name="action" value="cancel_reservation">
                                        <input type="hidden" name="reservation_id" value="<?= e($res['id']) ?>">
                                        <button type="submit" class="text-remove-btn">Cancel Reservation</button>
                                    </form>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    <?php endif; ?>

                    <hr class="hairline-divider">

                    <h3 class="card-subheading">Salon Guidelines</h3>
                    <ul class="clean-spec-bullets">
                        <li>Tables are held for 15 minutes past the reserved time.</li>
                        <li> Mondays are dedicated to wholesale and boutique production roasting.</li>
                        <li>Laptop-free tables in the main limestone salon after 11:30; laptops welcome at the morning banquette seating.</li>
                    </ul>
                </div>
            </aside>
        </div>
    </div>
</section>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
