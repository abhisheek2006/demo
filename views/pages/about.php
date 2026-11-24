<?php
/**
 * About Us.
 *
 * @var array<string,mixed> $clinic
 * @var array<string,mixed> $doctor
 * @var array<int,array{heading:string,body:string}> $biography
 * @var array<int,string> $languages
 * @var array<int,array<string,mixed>> $testimonials
 */
?>

<?= component('page-hero', [
    'eyebrow'   => 'About us',
    'heading'   => 'TRUE HOMOEOPATHY',
    'principles' => true,
    'lead'      => '',
    'trail'     => [['name' => 'Home', 'url' => '/'], ['name' => 'About Us']],
]) ?>

<section class="section" id="doctor">
    <div class="container split">
        <div class="split__media" data-reveal>
            <div class="split__frame split__frame--portrait">
                <img src="<?= e(asset('assets/images/about/doctor-smriti-das.jpg')) ?>"
                     alt="Photograph of Dr. Smriti Das, Homoeopathic Physician at Swasti Homoeo Clinic, Sri Vijaya Puram"
                     width="700" height="1050" loading="lazy" decoding="async">
            </div>
            <div class="split__badge">
                <p class="split__badge-value"><?= e((string) $doctor['experience']) ?></p>
                <p class="split__badge-label">Homoeopathic Physician</p>
            </div>
        </div>

        <div class="split__text" data-reveal>
            <p class="eyebrow">The doctor</p>
            <h2 class="section__title"><?= e((string) $doctor['name']) ?></h2>
            <p class="section__title about-doctor__qualification"><?= e((string) $doctor['short_qualification']) ?></p>

            <ul class="tag-list">
                <?php foreach ((array) $doctor['qualifications'] as $qualification): ?>
                    <li class="tag"><?= icon('certificate', 'icon icon--xs', 14) ?> <?= e((string) $qualification) ?></li>
                <?php endforeach; ?>
            </ul>

            <div class="prose">
                <?php foreach ([
                    'Dr. Smriti is a well experienced physician with profound clinical knowledge, she is committed and dedicated towards alleviating patients’ illnesses since she has started her clinical journey.',
                    'She pursued her graduation from D.N.De HMC&H under West Bengal University of Health Sciences.',
                    'She pursued her post-graduation at Mahesh Bhattacharya HMC&H under West Bengal University of Health Sciences.',
                    'She received the prestigious academic excellence award, the Dr. Malati Allen Noble Award in 2017, world’s largest award ceremony in the field of Homoeopathy.',
                ] as $paragraph): ?>
                    <p><?= e($paragraph) ?></p>
                <?php endforeach; ?>
            </div>

            <div class="about-award-gallery" data-hero-slides aria-label="Dr. Smriti Das receiving the Dr. Malati Allen Noble Award">
                <figure class="about-award-gallery__slide hero__slide is-active" aria-hidden="false">
                    <img src="<?= e(asset('assets/images/about/dr-smriti-malati-allen-award-medal.jpg')) ?>"
                         alt="Dr. Smriti Das receiving the Dr. Malati Allen Noble Award medal"
                         width="3216" height="2136" loading="lazy" decoding="async">
                    <figcaption>Receiving the Dr. Malati Allen Noble Award</figcaption>
                </figure>
                <figure class="about-award-gallery__slide hero__slide" aria-hidden="true">
                    <img src="<?= e(asset('assets/images/about/dr-smriti-malati-allen-award-certificate.jpg')) ?>"
                         alt="Dr. Smriti Das receiving her Dr. Malati Allen Noble Award certificate"
                         width="3216" height="2136" loading="lazy" decoding="async">
                    <figcaption>The award presentation</figcaption>
                </figure>
            </div>

            <div class="detail-grid">
                <div class="detail">
                    <p class="detail__label">Registration</p>
                    <p class="detail__value">Registered with the National Commission for Homoeopathy (NCH), National Register of Homoeopathy (NRH), registration no. NRH/0030897; also registered under the Central Council of Homoeopathy (CCH), CRH no. 149349.</p>
                </div>
                <div class="detail">
                    <p class="detail__label">Languages</p>
                    <p class="detail__value">English · Hindi · Bengali</p>
                </div>
            </div>

            <div class="split__actions">
                <a class="btn btn--primary" href="<?= e(url('/book-consultation')) ?>">Book with Dr. Das</a>
                <a class="btn btn--ghost" href="<?= e(tel_link()) ?>">Call <?= e(format_phone()) ?></a>
            </div>
        </div>
    </div>
</section>

<section class="section section--testimonials" id="testimonials">
    <div class="container">
        <header class="section__head" data-reveal>
            <p class="eyebrow">In their words</p>
            <h2 class="section__title">What patients tell us</h2>
        </header>

        <div class="testimonials">
            <div class="testimonials__track" data-testimonials>
                <?php foreach ($testimonials as $testimonial): ?>
                <figure class="testimonial" data-reveal>
                    <?= icon('quote', 'testimonial__quote-mark', 30) ?>
                    <blockquote class="testimonial__quote"><?= e($testimonial['quote']) ?></blockquote>
                    <figcaption class="testimonial__meta">
                        <?php $avatar = (string) ($testimonial['avatar'] ?? ''); ?>
                        <?php if (has_image($avatar)): ?>
                        <img class="testimonial__avatar" src="<?= e(asset($avatar)) ?>" width="108" height="108" loading="lazy" decoding="async" alt="<?= e($testimonial['name']) ?>">
                        <?php else: ?>
                        <span class="testimonial__avatar testimonial__avatar--initials" aria-hidden="true"><?= e(initials((string) $testimonial['name'])) ?></span>
                        <?php endif; ?>
                        <span>
                            <span class="testimonial__name"><?= e($testimonial['name']) ?></span>
                            <span class="testimonial__meta-line"><?= !empty($testimonial['source']) ? e((string) $testimonial['source']) : 'Verified patient' ?><?= ($testimonial['when'] ?? '') !== '' ? ' · ' . e((string) $testimonial['when']) : '' ?></span>
                        </span>
                        <?= stars((int) $testimonial['rating']) ?>
                    </figcaption>
                </figure>
                <?php endforeach; ?>
            </div>
        </div>
    </div>
</section>

