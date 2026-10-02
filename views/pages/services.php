<?php
/**
 * Services.
 *
 * @var array<string,mixed> $clinic
 * @var array<int,array<string,mixed>> $services
 * @var array<string,string[]> $conditionGroups
 * @var string $scopeNote
 * @var array<int,array{step:string,title:string,text:string}> $process
 * @var array<int,string> $emergency
 */
?>

<?= component('page-hero', [
    'eyebrow'   => 'Services',
    'heading'   => 'What we treat, and what an appointment includes',
    'lead'      => 'Eight services, each with a clearly stated scope. If your condition is not listed, ask us '
        . '— we would rather answer honestly than claim everything.',
    'trail'     => [['name' => 'Home', 'url' => '/'], ['name' => 'Services']],
    'variant'   => 'teal',
]) ?>

<section class="section">
    <div class="container">
        <aside class="notice notice--info" role="note">
            <span class="notice__icon"><?= icon('info', 'icon', 24) ?></span>
            <div class="notice__body">
                <p class="notice__text"><?= e($scopeNote) ?></p>
            </div>
        </aside>
    </div>
</section>

<section class="section section--tight">
    <div class="container">
        <nav class="anchor-nav" aria-label="Jump to a service">
            <ul class="anchor-nav__list">
                <?php foreach ($services as $service): ?>
                    <li><a class="anchor-nav__link" href="#<?= e($service['id']) ?>"><?= e(truncate($service['title'], 28)) ?></a></li>
                <?php endforeach; ?>
                <li><a class="anchor-nav__link" href="#conditions">Conditions treated</a></li>
            </ul>
        </nav>
    </div>
</section>

<section class="section section--tight section--topless">
    <div class="container service-list">
        <?php foreach ($services as $index => $service): ?>
        <article class="service<?= $index % 2 === 1 ? ' service--flip' : '' ?>" id="<?= e($service['id']) ?>" data-reveal>
            <div class="service__media">
                <img src="<?= e(image_url((string) $service['image'])) ?>"
                     alt="<?= e($service['title']) ?> at <?= e((string) $clinic['name']) ?>"
                     width="520" height="380" loading="lazy" decoding="async">
            </div>
            <div class="service__body">
                <p class="eyebrow"><?= e($service['tagline']) ?></p>
                <h2 class="service__title"><?= icon($service['icon'], 'icon icon--sm', 22) ?> <?= e($service['title']) ?></h2>
                <p class="service__intro"><?= e($service['intro']) ?></p>

                <p class="service__subhead">What the appointment includes</p>
                <ul class="check-list">
                    <?php foreach ($service['includes'] as $include): ?>
                        <li><?= icon('check', 'icon icon--xs', 15) ?> <?= e($include) ?></li>
                    <?php endforeach; ?>
                </ul>

                <div class="detail-grid">
                    <div class="detail">
                        <p class="detail__label">Suitable for</p>
                        <p class="detail__value"><?= e($service['suitable_for']) ?></p>
                    </div>
                    <div class="detail detail--warn">
                        <p class="detail__label">Good to know</p>
                        <p class="detail__value"><?= e($service['note']) ?></p>
                    </div>
                </div>

                <div class="service__actions">
                    <a class="btn btn--primary btn--sm" href="<?= e(url('/book-consultation')) ?>">Book this appointment</a>
                    <a class="btn btn--ghost btn--sm" href="<?= e(whatsapp_link('Hello, I would like to ask about ' . $service['title'] . '.')) ?>" rel="noopener nofollow" target="_blank">
                        <?= icon('whatsapp', 'icon icon--xs', 15) ?> Ask about it
                    </a>
                </div>
            </div>
        </article>
        <?php endforeach; ?>
    </div>
</section>

<section class="section section--soft" id="conditions">
    <div class="container">
        <header class="section__head" data-reveal>
            <p class="eyebrow">Conditions</p>
            <h2 class="section__title">Commonly presented complaints</h2>
            <p class="section__lead section__lead--center">
                This is a sample, not a guarantee. Homoeopathic prescribing always depends on your individual
                symptom picture, and some conditions are referred to hospital care.
            </p>
        </header>

        <div class="condition-groups condition-groups--wide">
            <?php foreach ($conditionGroups as $group => $items): ?>
            <div class="condition-group" data-reveal>
                <h3 class="condition-group__title"><?= e((string) $group) ?></h3>
                <ul class="condition-group__list">
                    <?php foreach ($items as $item): ?>
                        <li><?= icon('check', 'icon icon--xs', 13) ?> <?= e($item) ?></li>
                    <?php endforeach; ?>
                </ul>
            </div>
            <?php endforeach; ?>
        </div>
    </div>
</section>

<section class="section section--process">
    <div class="container">
        <header class="section__head" data-reveal>
            <p class="eyebrow">What happens next</p>
            <h2 class="section__title">From booking to follow-up</h2>
        </header>

        <ol class="process">
            <?php foreach ($process as $step): ?>
            <li class="process__step" data-reveal>
                <span class="process__num"><?= e($step['step']) ?></span>
                <h3 class="process__title"><?= e($step['title']) ?></h3>
                <p class="process__text"><?= e($step['text']) ?></p>
            </li>
            <?php endforeach; ?>
        </ol>

        <div class="container--narrow">
            <?= component('emergency-notice', ['items' => $emergency]) ?>
        </div>
    </div>
</section>

<?= component('cta-band') ?>