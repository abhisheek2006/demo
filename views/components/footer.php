<?php
/**
 * Site footer.
 *
 * @var array<string,string> $clinic
 * @var array<string,array<int,array{label:string,url:string}>> $footerNav
 * @var int $appYear
 */
$socials = array_filter([
    'Facebook'  => (string) ($clinic['facebook'] ?? ''),
    'Instagram' => (string) ($clinic['instagram'] ?? ''),
    'X'         => (string) ($clinic['x'] ?? ''),
    'YouTube'   => (string) ($clinic['youtube'] ?? ''),
]);
?>
<footer class="footer">
    <div class="footer__top">
        <div class="container footer__grid">
            <div class="footer__brand">
                <a class="brand brand--footer" href="<?= e(url('/')) ?>">
                    <img class="brand__logo brand__logo--footer" src="<?= e(url('/assets/images/icons/logo-white.png')) ?>" width="164" height="58" alt="Swasti Homoeo Clinic">
                </a>
                <p class="footer__claim">“<?= e($clinic['tagline'] ?? 'We believe in easy, safe and quick recovery') ?>”</p>
                <p class="footer__blurb">
                    A classical homoeopathy practice in Bhathu Basti, Port Blair, led by
                    Dr. Smriti Das (BHMS, MD — WBUHS) with more than eight years of clinical experience.
                </p>
                <ul class="footer__contact">
                    <li><span class="footer__contact-label">Address</span>
                        <span><?= e(\App\Content\Clinic::addressOneLine()) ?></span></li>
                    <li><span class="footer__contact-label">Phone</span>
                        <span><a href="<?= e(tel_link()) ?>"><?= e(format_phone()) ?></a></span></li>
                    <li><span class="footer__contact-label">Email</span>
                        <span><a href="<?= e(mail_link()) ?>"><?= e($clinic['email'] ?? '') ?></a></span></li>
                    <li><span class="footer__contact-label">Hours</span>
                        <span>Mon–Fri 10:00 AM – 7:00 PM · Sat 10:00 AM – 3:00 PM</span></li>
                </ul>
                <?php if ($socials !== []): ?>
                <ul class="footer__social">
                    <?php foreach ($socials as $label => $href): ?>
                        <li><a href="<?= e($href) ?>" rel="noopener nofollow" target="_blank" aria-label="Swasti Homoeo Clinic on <?= e($label) ?>">
                            <?= icon('globe', 'icon icon--sm', 18) ?></a></li>
                    <?php endforeach; ?>
                </ul>
                <?php endif; ?>
            </div>

            <?php foreach ($footerNav as $heading => $links): ?>
            <nav class="footer__col" aria-label="<?= e($heading) ?> navigation">
                <h2 class="footer__heading"><?= e($heading) ?></h2>
                <ul class="footer__links">
                    <?php foreach ($links as $link): ?>
                        <li><a href="<?= e(url($link['url'])) ?>"><?= e($link['label']) ?></a></li>
                    <?php endforeach; ?>
                </ul>
            </nav>
            <?php endforeach; ?>
        </div>
    </div>

    <div class="footer__bottom">
        <div class="container footer__bottom-inner">
            <p class="footer__copy">
                &copy; <?= e((string) $appYear) ?> Swasti Homoeo Clinic · <?= e($clinic['legal_name'] ?? 'Andaman Homoeo Health Care LLP') ?>. All rights reserved.
            </p>
            <p class="footer__disclaimer">
                Information on this website is for general awareness and is not a substitute for a
                consultation with a qualified doctor. In an emergency call <strong>112</strong>.
            </p>
            <p class="footer__meta">
                <a href="<?= e(url('/privacy-policy')) ?>">Privacy Policy</a>
                <span aria-hidden="true">·</span>
                <a href="<?= e(url('/sitemap.xml')) ?>">Sitemap</a>
                <span aria-hidden="true">·</span>
                <a href="<?= e(url('/faq')) ?>">FAQ</a>
            </p>
        </div>
    </div>
</footer>
