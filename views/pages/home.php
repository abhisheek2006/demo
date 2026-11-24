<?php
/**
 * Home page.
 *
 * @var array<string,string> $clinic
 * @var array<string,mixed> $doctor
 * @var array<int,array{icon:string,title:string,text:string}> $pillars
 * @var array<int,array<string,mixed>> $testimonials
 * @var array<int,array{question:string,answer:string}> $faqs
 */

$waBooking = whatsapp_link(
    "Hello {$clinic['name']}, I would like to book a homoeopathic consultation.\n"
    . "Name:\nPreferred day and time:\nMain problem:"
);
?>

<section class="hero" data-hero>
    <div class="hero__bg" aria-hidden="true"></div>
    <div class="container hero__inner">
        <div class="hero__text">
            <h1 class="hero__title">Homoeopathy Clinic in <span>Sri Vijaya Puram</span></h1>

            <p class="hero__claim">“<?= e($clinic['tagline'] ?? 'We believe in easy, safe and quick recovery') ?>”</p>

            <div class="hero-principles">
                <p class="hero-principles__eyebrow"><strong>TRUE HOMOEOPATHY</strong></p>
                <p class="hero-principles__promise"><em>Minimal Medicine.<br><strong>Maximum Results.</strong></em></p>
                <p class="hero-principles__single">Only Single-Constituent Medicines.</p>
                <div class="hero-principles__standards" aria-label="Our medicine standards">
                    <span>No Patent Medicines.</span>
                    <span>No Combination Medicines.</span>
                    <span>No Syrups.</span>
                    <span>No Combination Drops.</span>
                </div>
                <p class="hero-principles__authentic"><strong><em>Authentic. Individualised Prescriptions.</em></strong></p>
                <p class="hero-principles__closing">Following the principles of <em>Classical Homoeopathy.</em></p>
            </div>

            <div class="hero__actions">
                <a class="btn btn--white btn--lg" href="<?= e(url('/book-consultation')) ?>">
                    Book a Consultation <?= icon('arrow-right', 'icon icon--xs', 18) ?>
                </a>
                <a class="btn btn--whatsapp btn--lg" href="<?= e($waBooking) ?>" rel="noopener nofollow" target="_blank" data-track="hero-whatsapp">
                    <?= icon('whatsapp', 'icon icon--xs', 18) ?> WhatsApp the clinic
                </a>
            </div>

        </div>

        <div class="hero__media">
            <div class="hero__figure">
                <div class="hero__caption">
                    <p class="hero__caption-title">Treatment that respects your body’s own healing</p>
                </div>
            </div>

            <div class="hero__card">
                <div class="hero__card-row">
                    <span class="hero__card-icon"><?= icon('phone', 'icon icon--sm', 20) ?></span>
                    <div>
                        <p class="hero__card-label">Call the clinic</p>
                        <a class="hero__card-value" href="<?= e(tel_link()) ?>"><?= e(format_phone()) ?></a>
                    </div>
                </div>
                <div class="hero__card-row">
                    <span class="hero__card-icon"><?= icon('pin', 'icon icon--sm', 20) ?></span>
                    <div>
                        <p class="hero__card-label">Clinic address</p>
                        <p class="hero__card-value hero__card-value--sm"><?= e($clinic['address'] ?? '') ?></p>
                    </div>
                </div>
                <div class="hero__card-row">
                    <span class="hero__card-icon"><?= icon('clock-open', 'icon icon--sm', 20) ?></span>
                    <div>
                        <p class="hero__card-label">OPD timings</p>
                        <p class="hero__card-value hero__card-value--sm">Mon–Sat 9 AM–12:30 PM and 4:30–7:30 PM · Sun closed</p>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="hero__wave" aria-hidden="true"></div>
</section>

<section class="section section--about">
    <div class="container split">
        <div class="split__media" data-reveal>
            <div class="doctor-profile-card">
                <img class="doctor-profile-card__photo" src="<?= e(asset('assets/images/about/dr-smriti-das-portrait.jpg')) ?>" alt="Dr. Smriti Das at Swasti Homoeo Clinic" width="420" height="520" loading="lazy">
                <p class="doctor-profile-card__name"><?= e((string) ($doctor['name'] ?? 'Dr. Smriti Das')) ?></p>
                <p class="doctor-profile-card__qualification"><?= e($doctor['short_qualification'] ?? 'BHMS, MD (WBUHS)') ?></p>
                <p class="doctor-profile-card__role">Principal Homoeopathic Physician</p>
            </div>
        </div>

        <div class="split__text" data-reveal>
            <p class="eyebrow">About the clinic</p>
            <h2 class="section__title">Honest homoeopathy, practised the classical way</h2>
            <p class="section__lead">
                <?= e(\App\Content\Clinic::intro()) ?>
            </p>

            <ul class="pillars">
                <?php foreach ($pillars as $pillar): ?>
                <li class="pillar">
                    <span class="pillar__icon"><?= icon($pillar['icon'], 'icon', 22) ?></span>
                    <div>
                        <h3 class="pillar__title"><?= e($pillar['title']) ?></h3>
                        <p class="pillar__text"><?= e($pillar['text']) ?></p>
                    </div>
                </li>
                <?php endforeach; ?>
            </ul>

            <div class="split__actions">
                <a class="btn btn--primary" href="<?= e(url('/about-us')) ?>">Read about the doctor <?= icon('arrow-right', 'icon icon--xs', 16) ?></a>
                <a class="btn btn--ghost" href="<?= e(url('/book-consultation')) ?>">Book a consultation</a>
            </div>
        </div>
    </div>
</section>

<section class="section section--testimonials">
    <div class="container">
        <header class="section__head">
            <p class="eyebrow">Patient experiences</p>
            <h2 class="section__title">What patients say</h2>
        </header>

        <div class="testimonials">
            <div class="testimonials__track" data-testimonials>
            <?php foreach ($testimonials as $testimonial): ?>
            <figure class="testimonial">
                <?= icon('quote', 'testimonial__quote-mark', 34) ?>
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
                        <span class="testimonial__meta-line">Verified patient<?= ($testimonial['when'] ?? '') !== '' ? ' · ' . e((string) $testimonial['when']) : '' ?></span>
                    </span>
                    <?= stars((int) $testimonial['rating']) ?>
                </figcaption>
            </figure>
            <?php endforeach; ?>
            </div>
        </div>

        <p class="section__more">
            <a class="btn btn--ghost" href="<?= e(url('/about-us#testimonials')) ?>">More about our patients</a>
        </p>
    </div>
</section>


<section class="section section--faq section--soft">
    <div class="container container--narrow">
        <header class="section__head" data-reveal>
            <h2 class="section__title">Frequently asked questions</h2>
        </header>

        <?= component('faq-accordion', [
            'grouped' => ['Featured questions' => $faqs],
            'id' => 'home-faq',
            'showGroupTitle' => false,
        ]) ?>

        <p class="section__more">
            <a class="btn btn--outline" href="<?= e(url('/faq')) ?>">Read all <?= count(\App\Content\FaqData::all()) ?> questions</a>
        </p>
    </div>
</section>

