<?php
/**
 * 404 — rendered by the kernel for any unmatched route.
 *
 * @var array<string,mixed> $seo
 * @var string $requestPath
 */
?>

<?= component('page-hero', [
    'eyebrow'   => 'Error 404',
    'heading'   => 'We could not find that page',
    'lead'      => 'The link may be old, mistyped, or the page may have been renamed. The pages below cover '
        . 'almost everything on this site.',
    'trail'     => [['name' => 'Home', 'url' => '/'], ['name' => 'Not found']],
    'variant'   => 'slate',
]) ?>

<section class="section">
    <div class="container container--narrow">
        <p class="error-path">
            <?= icon('alert', 'icon icon--xs', 16) ?>
            Requested path: <code><?= e($requestPath ?? '/') ?></code>
        </p>

        <div class="cards cards--three">
            <?php
            $links = [
                ['icon' => 'stethoscope', 'title' => 'Book a consultation', 'text' => 'Request an appointment online and we will call you to confirm.', 'url' => '/book-consultation', 'label' => 'Book now'],
                ['icon' => 'search',      'title' => 'Search the FAQ',     'text' => 'Most questions about treatment, fees and booking are answered already.', 'url' => '/faq', 'label' => 'Read the FAQ'],
                ['icon' => 'chat',        'title' => 'Ask us directly',     'text' => 'Call or WhatsApp the clinic. Somebody reads every message.', 'url' => '/contact', 'label' => 'Contact us'],
            ];
            foreach ($links as $link): ?>
            <article class="card card--service">
                <span class="card__icon"><?= icon($link['icon'], 'icon', 24) ?></span>
                <h2 class="card__title card__title--sm"><?= e($link['title']) ?></h2>
                <p class="card__text"><?= e($link['text']) ?></p>
                <a class="card__link" href="<?= e(url($link['url'])) ?>"><?= e($link['label']) ?> <?= icon('arrow-right', 'icon icon--xs', 15) ?></a>
            </article>
            <?php endforeach; ?>
        </div>

        <p class="section__more">
            <a class="btn btn--ghost" href="<?= e(url('/')) ?>"><?= icon('arrow-left', 'icon icon--xs', 15) ?> Back to the home page</a>
        </p>
    </div>
</section>