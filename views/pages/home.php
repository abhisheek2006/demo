<?php
/**
 * Home page.
 *
 * @var array<string,string> $clinic
 * @var array<string,mixed> $doctor
 * @var array<int,array{value:string,label:string,icon:string}> $stats
 * @var array<int,array{icon:string,title:string,text:string}> $pillars
 * @var array<int,array{step:string,title:string,text:string}> $process
 * @var array<int,array<string,mixed>> $services
 * @var array<int,array<string,mixed>> $testimonials
 * @var array<string,string[]> $conditionGroups
 * @var array<int,array{question:string,answer:string}> $faqs
 */

$waBooking = whatsapp_link(
    "Hello {$clinic['name']}, I would like to book a homoeopathic consultation.\n"
    . "Name:\nPreferred day and time:\nMain problem:"
);
$heroSlides = [
    [
        'image' => '/assets/images/hero/hero-1.svg',
        'alt'   => 'Illustration of a calm homoeopathic consultation room',
        'kicker'=> 'Classical homoeopathy in Port Blair',
        'title' => 'Treatment that respects your body’s own healing',
        'text'  => 'Individualised remedies selected from your own symptoms and history — not a fixed package deal for a named disease.',
    ],
    [
        'image' => '/assets/images/hero/hero-2.svg',
        'alt'   => 'Illustration of homoeopathic remedy bottles and a mortar and pestle',
        'kicker'=> 'Licensed pharmacy supply',
        'title' => 'Genuine remedies, clearly labelled, with written instructions',
        'text'  => 'Every prescription comes with potency, dose, timing and a follow-up date — so treatment never depends on memory.',
    ],
    [
        'image' => '/assets/images/hero/hero-3.svg',
        'alt'   => 'Illustration of a family consulting a homoeopathic physician',
        'kicker'=> 'Care for the whole family',
        'title' => 'Safe, gentle homoeopathy for infants, adults and seniors',
        'text'  => 'Potency and dose chosen for age and sensitivity, alongside — never instead of — your medical care.',
    ],
];
?>

<section class="hero" data-hero>
    <div class="hero__bg" aria-hidden="true"></div>
    <div class="container hero__inner">
        <div class="hero__text">
            <p class="eyebrow eyebrow--light"><?= icon('leaf', 'icon icon--xs', 15) ?> Classical Homoeopathic Treatment</p>

            <h1 class="hero__title">Best Homoeopathy Clinic in <span>Port Blair</span></h1>

            <p class="hero__claim">“<?= e($clinic['tagline'] ?? 'We believe in easy, safe and quick recovery') ?>”</p>

            <p class="hero__lead">
                Led by <strong>Dr. <?= e($doctor['name'] ?? 'Dr. Smriti Das') ?></strong>,
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
            <div class="hero__slider" data-slider data-autoplay="6000">
                <div class="hero__slides">
                    <?php foreach ($heroSlides as $index => $slide): ?>
                    <figure class="hero__slide<?= $index === 0 ? ' is-active' : '' ?>" data-slide
                            data-kicker="<?= e($slide['kicker']) ?>"
                            data-title="<?= e($slide['title']) ?>"
                            data-text="<?= e($slide['text']) ?>"
                            aria-hidden="<?= $index === 0 ? 'false' : 'true' ?>">
                        <img src="<?= e(image_url($slide['image'])) ?>" alt="<?= e($slide['alt']) ?>"
                             width="620" height="520" <?= $index === 0 ? '' : 'loading="lazy"' ?> decoding="async">
                    </figure>
                    <?php endforeach; ?>
                </div>

                <div class="hero__caption" data-slider-caption aria-live="polite">
                    <p class="hero__caption-kicker" data-caption-kicker><?= e($heroSlides[0]['kicker']) ?></p>
                    <p class="hero__caption-title" data-caption-title><?= e($heroSlides[0]['title']) ?></p>
                    <p class="hero__caption-text" data-caption-text><?= e($heroSlides[0]['text']) ?></p>
                </div>

                <div class="hero__dots" role="tablist" aria-label="Highlight slides">
                    <?php foreach ($heroSlides as $index => $slide): ?>
                        <button class="hero__dot<?= $index === 0 ? ' is-active' : '' ?>" type="button"
                                role="tab" data-slide-dot="<?= $index ?>"
                                aria-selected="<?= $index === 0 ? 'true' : 'false' ?>"
                                aria-label="Slide <?= $index + 1 ?>: <?= e($slide['title']) ?>"></button>
                    <?php endforeach; ?>
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

<section class="stats" aria-label="Clinic at a glance">
    <div class="container">
        <ul class="stats__grid">
            <?php foreach ($stats as $stat): ?>
            <li class="stat" data-reveal>
                <span class="stat__icon"><?= icon($stat['icon'], 'icon', 22) ?></span>
                <span class="stat__value"><?= e($stat['value']) ?></span>
                <span class="stat__label"><?= e($stat['label']) ?></span>
            </li>
            <?php endforeach; ?>
        </ul>
    </div>
</section>

<section class="section section--about">
    <div class="container split">
        <div class="split__media" data-reveal>
            <div class="split__frame">
                <img src="<?= e(image_url('/assets/images/about/doctor-smriti-das.svg')) ?>"
                     alt="<?= e($doctor['image_alt'] ?? 'Dr. Smriti Das') ?>"
                     width="520" height="620" loading="lazy" decoding="async">
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

<section class="section section--soft" id="services">
    <div class="container">
        <header class="section__head" data-reveal>
            <p class="eyebrow">What we treat</p>
            <h2 class="section__title">Services offered at the clinic</h2>
            <p class="section__lead section__lead--center">
                Every appointment is individual. These are the areas of practice we focus on, and what each
                appointment actually includes.
            </p>
        </header>

        <div class="cards">
            <?php foreach ($services as $service): ?>
            <article class="card card--service" data-reveal>
                <span class="card__icon"><?= icon($service['icon'], 'icon', 24) ?></span>
                <h3 class="card__title"><?= e($service['title']) ?></h3>
                <p class="card__tagline"><?= e($service['tagline']) ?></p>
                <p class="card__text"><?= e(truncate($service['intro'], 155)) ?></p>
                <ul class="card__list">
                    <?php foreach (array_slice($service['includes'], 0, 3) as $include): ?>
                        <li><?= icon('check', 'icon icon--xs', 14) ?> <?= e($include) ?></li>
                    <?php endforeach; ?>
                </ul>
                <a class="card__link" href="<?= e(url('/services#' . $service['id'])) ?>">
                    Full details <?= icon('arrow-right', 'icon icon--xs', 15) ?>
                </a>
            </article>
            <?php endforeach; ?>
        </div>

        <p class="section__more">
            <a class="btn btn--outline" href="<?= e(url('/services')) ?>">See all services and conditions treated</a>
        </p>
    </div>
</section>

<section class="section section--process">
    <div class="container">
        <header class="section__head" data-reveal>
            <p class="eyebrow">How it works</p>
            <h2 class="section__title">Four steps, no surprises</h2>
            <p class="section__lead section__lead--center">
                We keep the process simple, because treatment that is hard to follow is treatment that does not work.
            </p>
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
    </div>
</section>

<section class="section section--why">
    <div class="container">
        <header class="section__head" data-reveal>
            <p class="eyebrow">How we practise</p>
            <h2 class="section__title">Why patients choose us</h2>
        </header>

        <div class="why-grid">
            <?php
            $whyCards = [
                [
                    'art'   => '/assets/images/features/why-case-taking.png',
                    'title' => 'In depth case taking',
                    'text'  => 'A full history before a single remedy is chosen — your symptoms, your '
                        . 'sleep, your digestion, what you eat and how you react to it. This is where the '
                        . 'difference is actually made.',
                ],
                [
                    'art'   => '/assets/images/features/why-report-analysis.png',
                    'title' => 'Previous reports, properly read',
                    'text'  => 'Bring your old test reports and prescriptions. We study the investigation '
                        . 'reports you already have instead of repeating tests you do not need.',
                ],
                [
                    'art'   => '/assets/images/features/why-own-pharmacy.png',
                    'title' => 'Accurate prescription, own pharmacy',
                    'text'  => 'Medicines are prepared and dispensed from our own pharmacy, so what you '
                        . 'take is genuine, correctly potent and matched to your case.',
                ],
            ];
            ?>
            <?php foreach ($whyCards as $why): ?>
            <article class="why-card" data-reveal>
                <div class="why-card__media">
                    <img src="<?= e(asset($why['art'])) ?>" width="500" height="500" loading="lazy" decoding="async" alt="" aria-hidden="true">
                </div>
                <h3 class="why-card__title"><?= e($why['title']) ?></h3>
                <p class="why-card__text"><?= e($why['text']) ?></p>
            </article>
            <?php endforeach; ?>
        </div>

        <ul class="why-points" data-reveal>
            <li class="why-point">
                <img class="why-point__mark" src="<?= e(asset('/assets/images/features/check.png')) ?>" width="128" height="128" loading="lazy" decoding="async" alt="" aria-hidden="true">
                <span><strong>Authentic</strong> With a proper case taken, all necessary advice on diet and lifestyle is given. You will never be pushed into unwanted investigations.</span>
            </li>
            <li class="why-point">
                <img class="why-point__mark" src="<?= e(asset('/assets/images/features/check.png')) ?>" width="128" height="128" loading="lazy" decoding="async" alt="" aria-hidden="true">
                <span><strong>Accurate</strong> You are not misled. If a cure for your illness is not possible, you will be told so honestly.</span>
            </li>
            <li class="why-point">
                <img class="why-point__mark" src="<?= e(asset('/assets/images/features/check.png')) ?>" width="128" height="128" loading="lazy" decoding="async" alt="" aria-hidden="true">
                <span><strong>Effective</strong> The more accurately you describe your complaints, the more quickly you begin to feel relief.</span>
            </li>
        </ul>
    </div>
</section>

<section class="section section--soft section--conditions" id="conditions">
    <div class="container">
        <header class="section__head" data-reveal>
            <p class="eyebrow">Common concerns</p>
            <h2 class="section__title">Conditions patients ask about most</h2>
            <p class="section__lead section__lead--center">
                A selection of the complaints we are asked about. If yours is not listed, ask us — the answer
                is usually “that needs a proper look first”.
            </p>
        </header>

        <div class="condition-groups">
            <?php foreach ($conditionGroups as $group => $items): ?>
            <div class="condition-group" data-reveal>
                <h3 class="condition-group__title"><?= e($group) ?></h3>
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

<section class="section section--testimonials">
    <div class="container">
        <header class="section__head" data-reveal>
            <p class="eyebrow">Patient experiences</p>
            <h2 class="section__title">What patients say</h2>
        </header>

        <div class="testimonials" data-carousel>
            <div class="testimonials__track" data-carousel-track>
                <?php foreach ($testimonials as $testimonial): ?>
                <figure class="testimonial" data-carousel-slide>
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
            <div class="testimonials__controls">
                <button class="carousel-btn" type="button" data-carousel-prev aria-label="Previous testimonial">
                    <?= icon('arrow-left', 'icon icon--sm', 18) ?>
                </button>
                <div class="testimonials__dots" data-carousel-dots></div>
                <button class="carousel-btn" type="button" data-carousel-next aria-label="Next testimonial">
                    <?= icon('arrow-right', 'icon icon--sm', 18) ?>
                </button>
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
