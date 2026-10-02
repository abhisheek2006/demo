<?php
/**
 * Minimal site footer.
 *
 * Layout: white logo on the left, circular social / action icons on the right,
 * a thin divider and a centred copyright line.
 *
 * Social URLs come from .env (CLINIC_FACEBOOK / CLINIC_INSTAGRAM / CLINIC_X).
 * Any link without a URL falls back to href="#", so nothing is invented and no
 * icon disappears — add the URL to .env and the real link goes live.
 *
 * @var array<string,string> $clinic
 * @var array<string,array<int,array{label:string,url:string}>> $footerNav
 * @var int $appYear
 */
use App\Content\Clinic;

$socials = [
    [
        'label' => 'Facebook',
        'icon'  => 'facebook',
        'url'   => (string) ($clinic['facebook'] ?? ''),
    ],
    [
        'label' => 'Instagram',
        'icon'  => 'instagram',
        'url'   => (string) ($clinic['instagram'] ?? ''),
    ],
    [
        'label' => 'Location',
        'icon'  => 'pin',
        'url'   => Clinic::mapUrl(),
        'external' => true,
    ],
    [
        'label' => 'X',
        'icon'  => 'x',
        'url'   => (string) ($clinic['x'] ?? ''),
        'external' => true,
    ],
];
?>
<footer class="site-footer">
    <div class="site-footer__wave" aria-hidden="true"></div>

    <span class="site-footer__leaf site-footer__leaf--tl" aria-hidden="true"></span>
    <span class="site-footer__leaf site-footer__leaf--bl" aria-hidden="true"></span>
    <span class="site-footer__leaf site-footer__leaf--br" aria-hidden="true"></span>
    <span class="site-footer__leaf site-footer__leaf--tr" aria-hidden="true"></span>

    <div class="site-footer__main">
        <div class="site-footer__container">
            <div class="site-footer__brand">
                <a class="site-footer__logo-link" href="<?= e(url('/')) ?>" aria-label="Swasti Homoeo Clinic — home">
                    <img class="site-footer__logo"
                         src="<?= e(url('/assets/images/icons/logo-white.png')) ?>"
                         width="164" height="58"
                         alt="Swasti Homoeo Clinic">
                </a>
            </div>

            <ul class="site-footer__socials">
                <?php foreach ($socials as $social): ?>
                    <?php
                    $href     = $social['url'] !== '' ? $social['url'] : '#';
                    $external = ($social['external'] ?? false) && $social['url'] !== '';
                    ?>
                    <li>
                        <a class="footer-social"
                           href="<?= e($href) ?>"
                           aria-label="<?= e($social['label']) ?>"
                           <?= $external ? 'target="_blank" rel="noopener nofollow"' : '' ?>>
                            <?= icon($social['icon'], 'footer-social__icon', 24) ?>
                        </a>
                    </li>
                <?php endforeach; ?>
            </ul>
        </div>

        <div class="site-footer__container">
            <hr class="site-footer__divider">
        </div>

        <div class="site-footer__container">
            <p class="site-footer__bottom">
                &copy; <?= e((string) $appYear) ?> Swasti Homoeo Clinic. All rights reserved.
            </p>
        </div>
    </div>
</footer>