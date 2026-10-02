<?php
/**
 * Shared booking page body. Included by pages/book-consultation.php and
 * pages/book-follow-up.php with $isFollowUp already set.
 *
 * @var bool $isFollowUp
 * @var string $heading
 * @var string $intro
 * @var array<string,mixed> $clinic
 * @var array<string,mixed> $doctor
 * @var array<int,string> $timeSlots
 * @var array<int,string> $reasons
 * @var array<int,string> $ages
 * @var array<int,string> $preparation
 * @var array<int,string> $afterBooking
 * @var array<int,string> $emergency
 */
$slug = $isFollowUp ? 'book-follow-up' : 'book-consultation';
?>

<?= component('page-hero', [
    'eyebrow'   => $isFollowUp ? 'Follow-up review' : 'Appointment request',
    'heading'   => $heading,
    'lead'      => $intro,
    'trail'     => [
        ['name' => 'Home', 'url' => '/'],
        ['name' => $isFollowUp ? 'Follow-Up' : 'Booking', 'url' => '/book-consultation'],
        ['name' => $isFollowUp ? 'Book a review' : 'Book a consultation'],
    ],
    'variant'   => $isFollowUp ? 'teal' : 'blue',
]) ?>

<section class="section">
    <div class="container booking-layout">
        <div class="booking-layout__form">
            <?php if ($isFollowUp): ?>
            <aside class="notice notice--info" role="note">
                <span class="notice__icon"><?= icon('refresh', 'icon', 22) ?></span>
                <div class="notice__body">
                    <p class="notice__text">
                        Already under treatment here? Book a review rather than a new consultation — it is
                        shorter, cheaper, and keeps your prescription history intact. Bring your current
                        remedy bottles.
                    </p>
                </div>
            </aside>
            <?php else: ?>
            <aside class="notice notice--info" role="note">
                <span class="notice__icon"><?= icon('stethoscope', 'icon', 22) ?></span>
                <div class="notice__body">
                    <p class="notice__text">
                        First visit? Allow 30 to 45 minutes. Bring your current medicine bottles and any
                        reports — the history is what makes the prescribing accurate.
                    </p>
                </div>
            </aside>
            <?php endif; ?>

            <?= component('booking-form', [
                'clinic'      => $clinic,
                'doctor'      => $doctor,
                'timeSlots'   => $timeSlots,
                'reasons'     => $reasons,
                'ages'        => $ages,
                'isFollowUp'  => $isFollowUp,
            ]) ?>
        </div>

        <aside class="booking-layout__aside">
            <div class="panel">
                <h2 class="panel__title panel__title--sm">Before you come</h2>
                <ul class="check-list check-list--tight">
                    <?php foreach ($preparation as $tip): ?>
                        <li><?= icon('check', 'icon icon--xs', 14) ?> <?= e($tip) ?></li>
                    <?php endforeach; ?>
                </ul>
            </div>

            <div class="panel">
                <h2 class="panel__title panel__title--sm">After you submit</h2>
                <ul class="check-list check-list--tight">
                    <?php foreach ($afterBooking as $note): ?>
                        <li><?= icon('arrow-right', 'icon icon--xs', 14) ?> <?= e($note) ?></li>
                    <?php endforeach; ?>
                </ul>
            </div>

            <div class="panel panel--tint">
                <h2 class="panel__title panel__title--sm">Clinic</h2>
                <ul class="mini-list">
                    <li><span><?= icon('stethoscope', 'icon icon--xs', 15) ?></span> <?= e((string) $doctor['name']) ?>, <?= e((string) $doctor['short_qualification']) ?></li>
                    <li><span><?= icon('pin', 'icon icon--xs', 15) ?></span> Solar Colony, Bhathu Basti, Port Blair 744105</li>
                    <li><span><?= icon('phone', 'icon icon--xs', 15) ?></span> <a href="<?= e(tel_link()) ?>"><?= e(format_phone()) ?></a></li>
                    <li><span><?= icon('clock-open', 'icon icon--xs', 15) ?></span> Mon–Fri 10 AM–7 PM · Sat 10 AM–3 PM</li>
                    <li><span><?= icon('globe', 'icon icon--xs', 15) ?></span> <?= e(implode(', ', (array) ($doctor['languages'] ?? []))) ?></li>
                </ul>
                <p class="panel__actions">
                    <a class="btn btn--whatsapp btn--sm btn--block" href="<?= e(whatsapp_link('Hello, I would like to ' . ($isFollowUp ? 'book a follow-up review' : 'book a consultation') . '.')) ?>" rel="noopener nofollow" target="_blank">
                        <?= icon('whatsapp', 'icon icon--xs', 15) ?> Prefer WhatsApp?
                    </a>
                </p>
            </div>

            <?= component('emergency-notice', ['items' => $emergency]) ?>
        </aside>
    </div>
</section>