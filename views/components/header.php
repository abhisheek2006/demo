<?php
/**
 * Site header with primary navigation and mobile drawer.
 *
 * @var array<string,string> $clinic
 * @var array<int,array{label:string,url:string,match:string}> $primaryNav
 * @var string $currentPath
 */
$todayIndex = (int) date('w');
$dayNames   = ['sunday', 'monday', 'tuesday', 'wednesday', 'thursday', 'friday', 'saturday'];
$today      = \App\Content\Clinic::hours()[$dayNames[$todayIndex]] ?? ['open' => '10:00', 'close' => '19:00'];
$isOpen     = (int) date('H') * 60 + (int) date('i')
    >= ((int) $today['open'] * 60) && (int) date('H') * 60 + (int) date('i') <= ((int) $today['close'] * 60);
?>
<header class="header" data-site-header>
    <div class="topbar">
        <div class="container topbar__inner">
            <p class="topbar__item topbar__item--muted">
                <?= icon('pin', 'icon icon--xs', 15) ?>
                <span><?= e($clinic['address'] ?? 'Solar Colony, Bhathu Basti, Port Blair') ?></span>
            </p>
            <ul class="topbar__list">
                <li class="topbar__item topbar__item--muted">
                    <span class="status-dot <?= $isOpen ? 'status-dot--open' : 'status-dot--closed' ?>" aria-hidden="true"></span>
                    <span><?= $isOpen ? 'Open now' : 'Closed now' ?> · <?= e(minutes_to_label((string) $today['open'])) ?>–<?= e(minutes_to_label((string) $today['close'])) ?></span>
                </li>
                <li class="topbar__item">
                    <a href="<?= e(tel_link()) ?>"><?= icon('phone', 'icon icon--xs', 15) ?> <?= e(format_phone()) ?></a>
                </li>
                <li class="topbar__item topbar__item--desktop-only">
                    <a href="https://wa.me/<?= e(preg_replace('/\D+/', '', (string) $clinic['whatsapp'])) ?>" rel="noopener nofollow" target="_blank">WhatsApp</a>
                </li>
            </ul>
        </div>
    </div>

    <div class="header__bar">
        <div class="container header__inner">
            <a class="brand" href="<?= e(url('/')) ?>" aria-label="Swasti Homoeo Clinic — home">
                <img class="brand__logo" src="<?= e(url('/assets/images/icons/logo.png')) ?>" width="164" height="58" alt="Swasti Homoeo Clinic">
            </a>

            <nav class="nav" aria-label="Primary">
                <ul class="nav__list">
                    <?php foreach ($primaryNav as $item): ?>
                        <li>
                            <a class="nav__link<?= active_nav($item['url'], $currentPath) ?>" href="<?= e(url($item['url'])) ?>"
                               <?= is_active_nav($item['url'], $currentPath) ? 'aria-current="page"' : '' ?>>
                                <?= e($item['label']) ?>
                            </a>
                        </li>
                    <?php endforeach; ?>
                </ul>
            </nav>

            <div class="header__actions">
                <a class="btn btn--ghost btn--sm header__call" href="<?= e(tel_link()) ?>" data-call-track>
                    <?= icon('phone', 'icon icon--xs', 16) ?><span>Call</span>
                </a>
                <a class="btn btn--primary btn--sm" href="<?= e(url('/book-consultation')) ?>">Book Consultation</a>
                <button class="nav-toggle" type="button" data-nav-toggle aria-expanded="false" aria-controls="mobile-nav" aria-label="Open menu">
                    <span class="nav-toggle__bars" aria-hidden="true"><span></span><span></span><span></span></span>
                </button>
            </div>
        </div>
    </div>

    <div class="mobile-nav" id="mobile-nav" data-mobile-nav hidden>
        <div class="container mobile-nav__inner">
<ul class="mobile-nav__list">
                    <?php foreach ($primaryNav as $item): ?>
                        <li>
                            <a class="mobile-nav__link<?= is_active_nav($item['url'], $currentPath) ? ' is-active' : '' ?>" href="<?= e(url($item['url'])) ?>"
                               <?= is_active_nav($item['url'], $currentPath) ? 'aria-current="page"' : '' ?>>
                                <?= e($item['label']) ?>
                                <?= icon('arrow-right', 'icon icon--xs', 16) ?>
                            </a>
                        </li>
                    <?php endforeach; ?>
                </ul>
            <div class="mobile-nav__actions">
                <a class="btn btn--primary btn--block" href="<?= e(url('/book-consultation')) ?>">Book a Consultation</a>
                <a class="btn btn--outline btn--block" href="<?= e(tel_link()) ?>"><?= icon('phone', 'icon icon--xs', 16) ?> <?= e(format_phone()) ?></a>
                <a class="btn btn--whatsapp btn--block" href="<?= e(whatsapp_link()) ?>" rel="noopener nofollow" target="_blank">
                    <?= icon('whatsapp', 'icon icon--xs', 16) ?> Message on WhatsApp
                </a>
            </div>
        </div>
    </div>
</header>
