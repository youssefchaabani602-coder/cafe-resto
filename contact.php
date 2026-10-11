<?php
/**
 * MAISON MOKA — Contact, Wholesale & Private Cupping Inquiries (contact.php)
 * Validates contact submissions on server and client, stores inquiries in
 * $_SESSION['inquiries'], and displays flagship location details.
 */

declare(strict_types=1);

require_once __DIR__ . '/includes/config.php';
require_once __DIR__ . '/includes/data.php';

$errors = [];
$flash_message = '';

$form = [
    'name'    => clean_input($_POST['name'] ?? ''),
    'email'   => clean_input($_POST['email'] ?? ''),
    'phone'   => clean_input($_POST['phone'] ?? ''),
    'topic'   => clean_input($_POST['topic'] ?? 'private_cupping'),
    'message' => clean_input($_POST['message'] ?? ''),
];

$TOPICS = [
    'private_cupping' => 'Private Group Cupping & Tasting Workshop',
    'wholesale'       => 'Wholesale Roastery Supply (Restaurants & Hotels)',
    'patisserie_event'=> 'Bespoke Patisserie & Event Catering',
    'order_support'   => 'Online Boutique Order Inquiry',
    'press'           => 'Editorial & Press Inquiry',
];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!verify_csrf_token($_POST['csrf_token'] ?? null)) {
        $errors['csrf'] = 'Security token expired. Please submit the form again.';
    } else {
        if (mb_strlen($form['name']) < 2) {
            $errors['name'] = 'Please enter your name.';
        }

        if (!filter_var($form['email'], FILTER_VALIDATE_EMAIL)) {
            $errors['email'] = 'Please enter a valid email address.';
        }

        if ($form['phone'] !== '' && !preg_match('/^[0-9+\s().\-]{8,20}$/', $form['phone'])) {
            $errors['phone'] = 'Please enter a valid phone number or leave optional field blank.';
        }

        if (!isset($TOPICS[$form['topic']])) {
            $errors['topic'] = 'Please choose an inquiry subject.';
        }

        if (mb_strlen($form['message']) < 10) {
            $errors['message'] = 'Please provide a message of at least 10 characters.';
        }

        if (empty($errors)) {
            $inquiry = [
                'id'          => 'INQ-' . random_int(1000, 9999),
                'created_at'  => date('d M Y, H:i'),
                'name'        => $form['name'],
                'email'       => $form['email'],
                'phone'       => $form['phone'],
                'topic_label' => $TOPICS[$form['topic']],
                'message'     => $form['message'],
            ];

            array_unshift($_SESSION['inquiries'], $inquiry);
            $flash_message = 'Thank you, ' . $form['name'] . '. Your inquiry (' . $inquiry['id'] . ') has been validated and recorded in your demo session.';

            $form = [
                'name'    => '',
                'email'   => '',
                'phone'   => '',
                'topic'   => 'private_cupping',
                'message' => '',
            ];
        }
    }
}

$page_title = 'Contact & Private Cuppings';
$page_description = 'Contact Maison Moka at 38 Rue de Turenne, 75003 Paris for private coffee cuppings, wholesale roastery accounts, and patisserie orders.';

require_once __DIR__ . '/includes/header.php';
?>

<section class="page-hero-compact">
    <div class="container">
        <p class="eyebrow-meta">
            <span>Le Marais Flagship</span>
            <span aria-hidden="true">·</span>
            <span>38 Rue de Turenne, 75003 Paris</span>
        </p>
        <h1 class="page-title">Contact &amp; Atelier Inquiries</h1>
        <p class="page-subtitle">
            Reach our roastery and patisserie team for private cupping workshops, wholesale restaurant partnerships, or bespoke pastry orders.
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
                <strong>Please check the highlighted form fields:</strong>
                <ul class="error-list">
                    <?php foreach ($errors as $err): ?>
                        <li><?= e($err) ?></li>
                    <?php endforeach; ?>
                </ul>
            </div>
        <?php endif; ?>

        <div class="reservation-grid">
            <!-- Contact Form -->
            <div class="reservation-form-col">
                <form action="contact.php" method="post" class="checkout-form" id="contact-form" novalidate>
                    <?= csrf_field() ?>

                    <fieldset class="form-fieldset">
                        <legend class="fieldset-legend">Send a Direct Message to the Atelier</legend>

                        <div class="form-group">
                            <label for="topic" class="form-label">Subject of Inquiry <span class="required-mark">*</span></label>
                            <select id="topic" name="topic" class="form-select">
                                <?php foreach ($TOPICS as $t_key => $t_label): ?>
                                    <option value="<?= e($t_key) ?>" <?= $form['topic'] === $t_key ? 'selected' : '' ?>>
                                        <?= e($t_label) ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>

                        <div class="form-group">
                            <label for="name" class="form-label">Full Name <span class="required-mark">*</span></label>
                            <input
                                type="text"
                                id="name"
                                name="name"
                                value="<?= e($form['name']) ?>"
                                required
                                placeholder="e.g. Sophie Garnier"
                                class="form-input <?= isset($errors['name']) ? 'is-invalid' : '' ?>"
                            >
                            <span class="field-error" id="err-name"><?= e($errors['name'] ?? '') ?></span>
                        </div>

                        <div class="form-row-2col">
                            <div class="form-group">
                                <label for="contact_email" class="form-label">Email Address <span class="required-mark">*</span></label>
                                <input
                                    type="email"
                                    id="contact_email"
                                    name="email"
                                    value="<?= e($form['email']) ?>"
                                    required
                                    placeholder="sophie.garnier@example.fr"
                                    class="form-input <?= isset($errors['email']) ? 'is-invalid' : '' ?>"
                                >
                                <span class="field-error" id="err-contact_email"><?= e($errors['email'] ?? '') ?></span>
                            </div>

                            <div class="form-group">
                                <label for="contact_phone" class="form-label">Telephone (Optional)</label>
                                <input
                                    type="tel"
                                    id="contact_phone"
                                    name="phone"
                                    value="<?= e($form['phone']) ?>"
                                    placeholder="+33 1 42 78 90 12"
                                    class="form-input tabular-nums <?= isset($errors['phone']) ? 'is-invalid' : '' ?>"
                                >
                                <span class="field-error" id="err-contact_phone"><?= e($errors['phone'] ?? '') ?></span>
                            </div>
                        </div>

                        <div class="form-group">
                            <label for="message" class="form-label">Your Message <span class="required-mark">*</span></label>
                            <textarea
                                id="message"
                                name="message"
                                rows="5"
                                required
                                minlength="10"
                                placeholder="Tell us about your event date, wholesale coffee volume, or question for our roaster..."
                                class="form-textarea <?= isset($errors['message']) ? 'is-invalid' : '' ?>"
                            ><?= e($form['message']) ?></textarea>
                            <span class="field-error" id="err-message"><?= e($errors['message'] ?? '') ?></span>
                        </div>
                    </fieldset>

                    <button type="submit" class="btn btn-primary btn-lg btn-block">Submit Atelier Inquiry</button>
                </form>
            </div>

            <!-- Flagship Address & Session Inquiry Log -->
            <aside class="reservation-info-col">
                <div class="summary-card">
                    <h2 class="card-subheading">Flagship Salon &amp; Roastery</h2>
                    <dl class="pdp-specs-list" style="margin-top: 0.75rem;">
                        <div class="spec-row">
                            <dt>Address</dt>
                            <dd>38 Rue de Turenne, 75003 Paris (Le Marais)</dd>
                        </div>
                        <div class="spec-row">
                            <dt>Métro Access</dt>
                            <dd>Saint-Paul (Line 1) · Chemin Vert (Line 8)</dd>
                        </div>
                        <div class="spec-row">
                            <dt>Telephone</dt>
                            <dd class="tabular-nums">+33 (0)1 42 72 38 19</dd>
                        </div>
                        <div class="spec-row">
                            <dt>Atelier Desk</dt>
                            <dd>bonjour@maisonmoka-paris.fr</dd>
                        </div>
                    </dl>

                    <?php if (!empty($_SESSION['inquiries'])): ?>
                        <hr class="hairline-divider">
                        <h3 class="card-subheading">Submitted Inquiries in This Session (<span class="tabular-nums"><?= count($_SESSION['inquiries']) ?></span>)</h3>
                        <div class="reservation-list">
                            <?php foreach ($_SESSION['inquiries'] as $inq): ?>
                                <div class="reservation-ticket">
                                    <div class="ticket-top">
                                        <span class="mono-inline font-semibold"><?= e($inq['id']) ?></span>
                                        <span class="tabular-nums"><?= e($inq['created_at']) ?></span>
                                    </div>
                                    <p class="ticket-title"><?= e($inq['topic_label']) ?></p>
                                    <p class="ticket-meta">From: <?= e($inq['name']) ?> (<?= e($inq['email']) ?>)</p>
                                    <p class="ticket-notes">“<?= e($inq['message']) ?>”</p>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    <?php endif; ?>
                </div>
            </aside>
        </div>
    </div>
</section>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
