<?php
/**
 * Page hero with breadcrumb, used by all inner pages.
 *
 * @var string $eyebrow
 * @var string $heading
 * @var string $lead
 * @var array<int,array{name:string,url?:string}> $trail
 * @var string|null $image
 * @var string|null $imageAlt
 * @var string|null $variant
 * @var bool $principles
 */
$eyebrow   = $eyebrow   ?? null;
$heading   = $heading   ?? '';
$lead      = $lead      ?? null;
$trail     = $trail     ?? [];
$image     = $image     ?? null;
$imageAlt  = $imageAlt  ?? null;
$variant   = $variant   ?? 'blue';
$principles = $principles ?? false;
$crumbs    = breadcrumb_trail($trail);
?>
<section class="page-hero page-hero--<?= e($variant) ?>">
    <div class="page-hero__bg" aria-hidden="true"></div>
    <div class="container page-hero__inner">
        <div class="page-hero__text">
            <?php if (!empty($crumbs) && count($crumbs) > 1): ?>
            <nav class="breadcrumb" aria-label="Breadcrumb">
                <ol class="breadcrumb__list">
                    <?php foreach ($crumbs as $crumb): ?>
                        <li class="breadcrumb__item">
                            <?php if ($crumb['isLast'] || $crumb['url'] === ''): ?>
                                <span aria-current="page"><?= e($crumb['name']) ?></span>
                            <?php else: ?>
                                <a href="<?= e(url($crumb['url'])) ?>"><?= e($crumb['name']) ?></a>
                                <span class="breadcrumb__sep" aria-hidden="true"><?= icon('chevron', 'icon icon--xs', 12) ?></span>
                            <?php endif; ?>
                        </li>
                    <?php endforeach; ?>
                </ol>
            </nav>
            <?php endif; ?>

            <?php if (!empty($eyebrow)): ?>
                <p class="eyebrow eyebrow--light"><?= e($eyebrow) ?></p>
            <?php endif; ?>

            <?php if (!empty($heading)): ?>
                <h1 class="page-hero__title"><?= e($heading) ?></h1>
            <?php endif; ?>

            <?php if ($principles): ?>
            <div class="hero-principles">
                <p class="hero-principles__promise"><em>Minimal Medicine.<br><strong>Maximum Results.</strong></em></p>
                <p class="hero-principles__single"><strong>Only Single-Constituent Medicines.</strong></p>
                <div class="hero-principles__standards" aria-label="Our medicine standards">
                    <span>No Patent Medicines.</span>
                    <span>No Combination Medicines.</span>
                    <span>No Syrups.</span>
                    <span>No Combination Drops.</span>
                </div>
                <p class="hero-principles__authentic"><strong><em>Authentic. Individualised Prescriptions.</em></strong></p>
                <p class="hero-principles__closing">Following the principles of <em>Classical Homoeopathy.</em></p>
            </div>
            <?php endif; ?>

            <?php if (!empty($lead)): ?>
                <p class="page-hero__lead"><?= e($lead) ?></p>
            <?php endif; ?>

            <?= $sections['hero_actions'] ?? '' ?>
        </div>

        <?php if (!empty($image)): ?>
        <div class="page-hero__media">
            <img src="<?= e(image_url($image)) ?>" alt="<?= e($imageAlt ?? '') ?>" width="560" height="420" loading="eager" decoding="async">
        </div>
        <?php endif; ?>
    </div>
</section>
