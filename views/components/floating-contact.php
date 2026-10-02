<?php
/**
 * Floating call / WhatsApp buttons.
 *
 * @var array<string,string> $clinic
 */
$whatsapp = preg_replace('/\D+/', '', (string) ($clinic['whatsapp'] ?? ''));
?>
<div class="floaters">
    <a class="floaters__btn floaters__btn--whatsapp"
       href="<?= e(whatsapp_link('Hello ' . ($clinic['name'] ?? 'Swasti Homoeo Clinic') . ', I would like to book a homoeopathic consultation.')) ?>"
       rel="noopener nofollow" target="_blank" data-track="whatsapp-float"
       aria-label="Message the clinic on WhatsApp">
        <?= icon('whatsapp', 'icon', 24) ?>
        <span class="floaters__label">WhatsApp</span>
    </a>
    <a class="floaters__btn floaters__btn--call" href="<?= e(tel_link()) ?>" data-track="call-float" aria-label="Call the clinic">
        <?= icon('phone', 'icon', 22) ?>
        <span class="floaters__label">Call now</span>
    </a>
</div>
