<?php
/**
 * Closing call-to-action band.
 *
 * @var string $heading
 * @var string $text
 */
$heading = $heading ?? 'Ready to start feeling better?';
$text    = $text ?? 'Request an appointment online, or call the clinic directly. We will confirm a time that suits you.';
?>
<section class="cta-band">
    <div class="container cta-band__inner">
        <div class="cta-band__text">
            <p class="eyebrow eyebrow--light">Book a consultation</p>
            <h2 class="cta-band__title"><?= e($heading) ?></h2>
            <p class="cta-band__lead"><?= e($text) ?></p>
            <ul class="cta-band__points">
                <li><?= icon('check', 'icon icon--xs', 16) ?> Reply within one working day</li>
                <li><?= icon('check', 'icon icon--xs', 16) ?> Morning and evening slots</li>
                <li><?= icon('check', 'icon icon--xs', 16) ?> Written prescription and dosing plan</li>
            </ul>
        </div>
        <div class="cta-band__actions">
            <a class="btn btn--white btn--lg" href="<?= e(url('/book-consultation')) ?>">Book Consultation</a>
            <a class="btn btn--ghost-light btn--lg" href="<?= e(whatsapp_link('Hello, I would like to book a consultation.')) ?>" rel="noopener nofollow" target="_blank">
                <?= icon('whatsapp', 'icon icon--xs', 18) ?> WhatsApp us
            </a>
            <a class="cta-band__phone" href="<?= e(tel_link()) ?>">
                <?= icon('phone', 'icon icon--xs', 16) ?> <?= e(format_phone()) ?>
            </a>
        </div>
    </div>
</section>
