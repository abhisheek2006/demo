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
            <p class="eyebrow eyebrow--light"><?= icon('leaf', 'icon icon--xs', 15) ?> Classical Homoeopathic Treatment</p>

            <h1 class="hero__title">Best Homoeopathy Clinic in <span>Port Blair</span></h1>

            <p class="hero__claim">“<?= e($clinic['tagline'] ?? 'We believe in easy, safe and quick recovery') ?>”</p>

            <p class="hero__lead">
                Led by <strong class="hero__doctor"><?= e($doctor['name'] ?? 'Dr. Smriti Das') ?></strong>,
                <span class="nowrap"><?= e($doctor['short_qualification'] ?? 'BHMS, MD (WBUHS)') ?></span> — with
                <?= e($doctor['experience'] ?? '8+ years of clinical experience') ?>. A classical
                homoeopathy practice in Bhathu Basti, a short walk from Garacharma.
            </p>

            <div class="hero__actions">
                <a class="btn btn--white btn--lg" href="<?= e(url('/book-consultation')) ?>">
                    Book a Consultation <?= icon('arrow-right', 'icon icon--xs', 18) ?>
                </a>
                <a class="btn btn--whatsapp btn--lg" href="<?= e($waBooking) ?>" rel="noopener nofollow" target="_blank" data-track="hero-whatsapp">
                    <?= icon('whatsapp', 'icon icon--xs', 18) ?> WhatsApp the clinic
                </a>
            </div>

            <ul class="hero__trust">
                <li><?= icon('certificate', 'icon icon--xs', 16) ?> Registered homoeopathic practice</li>
                <li><?= icon('clock', 'icon icon--xs', 16) ?> Same-day appointment confirmation</li>
                <li><?= icon('note', 'icon icon--xs', 16) ?> Written prescription every visit</li>
            </ul>
        </div>

        <div class="hero__media">
            <div class="hero__figure">
                <img class="hero__image"
                     src="<?= e(image_url('/assets/images/hero/hero-1.svg')) ?>"
                     alt="Illustration of a calm homoeopathic consultation room"
                     width="620" height="520" decoding="async">

                <div class="hero__caption">
                    <p class="hero__caption-kicker">Classical homoeopathy in Port Blair</p>
                    <p class="hero__caption-title">Treatment that respects your body’s own healing</p>
                    <p class="hero__caption-text">Individualised remedies selected from your own symptoms and history — not a fixed package deal for a named disease.</p>
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
                        <p class="hero__card-value hero__card-value--sm">Mon–Fri 10 AM–7 PM · Sat 10 AM–3 PM</p>
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
            <div class="split__frame">
                <img src="<?= e(image_url('/assets/images/about/doctor-smriti-das.jpg')) ?>"
                     alt="<?= e($doctor['image_alt'] ?? 'Dr. Smriti Das') ?>"
                     width="700" height="1050" loading="lazy" decoding="async">
            </div>
            <div class="split__badge">
                <p class="split__badge-value"><?= e($doctor['short_qualification'] ?? 'BHMS, MD (WBUHS)') ?></p>
                <p class="split__badge-label">Principal Homoeopathic Physician</p>
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

<section class="section section--why">
    <div class="container">
        <header class="section__head" data-reveal>
            <p class="eyebrow">How we practise</p>
            <h2 class="section__title">Why patients choose us</h2>
        </header>

        <?php
        $whyPoints = [
            [
                'title' => 'In depth case taking',
                'text'  => 'A full history before any remedy is chosen — symptoms, sleep, digestion, '
                    . 'food and reactions. This is where the difference is made.',
            ],
            [
                'title' => 'Previous reports, properly read',
                'text'  => 'Bring your old reports and prescriptions. We study what you already have '
                    . 'instead of repeating tests you do not need.',
            ],
            [
                'title' => 'Accurate prescription, own pharmacy',
                'text'  => 'Prepared and dispensed from our own pharmacy, so what you take is genuine '
                    . 'and correctly potent.',
            ],
            [
                'title' => 'Authentic',
                'text'  => 'Honest diet and lifestyle advice. You will never be pushed into unwanted '
                    . 'investigations.',
            ],
            [
                'title' => 'Accurate',
                'text'  => 'You are not misled. If a cure is not possible, you will be told so honestly.',
            ],
            [
                'title' => 'Effective',
                'text'  => 'The more accurately you describe your complaints, the sooner you feel relief.',
            ],
        ];
        ?>
        <ul class="why-list" data-reveal>
            <?php foreach ($whyPoints as $why): ?>
            <li class="why-list__item">
                <span class="why-list__mark"><?= icon('check', 'icon icon--xs', 15) ?></span>
                <h3 class="why-list__title"><?= e($why['title']) ?></h3>
                <p class="why-list__text"><?= e($why['text']) ?></p>
            </li>
            <?php endforeach; ?>
        </ul>
    </div>
</section>

<section class="section section--testimonials">
    <div class="container">
        <header class="section__head" data-reveal>
            <p class="eyebrow">Patient experiences</p>
            <h2 class="section__title">What patients say</h2>
        </header>

        <div class="testimonials">
            <div class="testimonials__track" data-testimonials>
            <?php foreach ($testimonials as $testimonial): ?>
            <figure class="testimonial" data-reveal>
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
            <p class="eyebrow">Straight answers</p>
            <h2 class="section__title">Frequently asked questions</h2>
            <p class="section__lead section__lead--center">
                The questions patients ask most, answered without hedging.
            </p>
        </header>

        <?= component('faq-accordion', ['grouped' => ['Common questions' => $faqs], 'id' => 'home-faq']) ?>

        <p class="section__more">
            <a class="btn btn--outline" href="<?= e(url('/faq')) ?>">Read all <?= count(\App\Content\FaqData::all()) ?> questions</a>
        </p>
    </div>
</section>

<?= component('cta-band') ?>
