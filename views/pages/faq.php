<?php
/**
 * FAQ page.
 *
 * @var array<string,mixed> $clinic
 * @var array<string,array<int,array<string,mixed>>> $grouped
 * @var int $total
 */
?>

<?= component('page-hero', [
    'eyebrow'   => 'FAQ',
    'heading'   => 'Frequently asked questions',
    'lead'      => 'Straight answers about treatment, fees, booking, children, pregnancy and what homoeopathy '
        . 'cannot do. If your question is not here, call or WhatsApp us.',
    'trail'     => [['name' => 'Home', 'url' => '/'], ['name' => 'FAQ']],
    'variant'   => 'sand',
]) ?>

<section class="section">
    <div class="container container--narrow">
        <p class="faq-lede">
            <?= icon('info', 'icon icon--xs', 16) ?>
            <strong><?= e((string) $total) ?> questions</strong> across <?= e((string) count($grouped)) ?> topics.
            Everything here is editable by the clinic and was written to be read, not skimmed.
        </p>

        <?= component('faq-accordion', [
            'grouped'    => $grouped,
            'id'         => 'faq-page',
            'searchable' => true,
        ]) ?>
    </div>
</section>

<section class="section section--soft section--tight">
    <div class="container container--narrow">
        <div class="panel panel--center">
            <h2 class="panel__title">Still have a question?</h2>
            <p class="panel__lead">
                Call the clinic, message on WhatsApp, or send a short note through the contact form. If your
                question is a good one, we will add it here.
            </p>
            <div class="panel__buttons">
                <a class="btn btn--primary" href="<?= e(tel_link()) ?>"><?= icon('phone', 'icon icon--xs', 16) ?> <?= e(format_phone()) ?></a>
                <a class="btn btn--whatsapp" href="<?= e(whatsapp_link('Hello, I have a question about homoeopathic treatment.')) ?>" rel="noopener nofollow" target="_blank">
                    <?= icon('whatsapp', 'icon icon--xs', 16) ?> WhatsApp
                </a>
                <a class="btn btn--ghost" href="<?= e(url('/contact')) ?>">Send a message</a>
            </div>
        </div>
    </div>
</section>