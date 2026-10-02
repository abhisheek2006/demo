<?php
/**
 * Article index.
 *
 * @var array<string,mixed> $clinic
 * @var array<int,array<string,mixed>> $articles
 */
?>

<?= component('page-hero', [
    'eyebrow'   => 'Health articles',
    'heading'   => 'Plain-language guides for families in the Andamans',
    'lead'      => 'Written for the questions patients actually ask in this clinic — what helps, what does not, '
        . 'and when to stop treating yourself and go to hospital.',
    'trail'     => [['name' => 'Home', 'url' => '/'], ['name' => 'Articles']],
    'variant'   => 'plum',
]) ?>

<section class="section">
    <div class="container">
        <div class="article-grid">
            <?php foreach ($articles as $article): ?>
            <article class="article-card" data-reveal>
                <a class="article-card__media" href="<?= e(url('/articles/' . $article['slug'])) ?>" tabindex="-1" aria-hidden="true">
                    <img src="<?= e(image_url((string) $article['image'])) ?>" alt="" width="420" height="260"
                         loading="lazy" decoding="async">
                </a>
                <div class="article-card__body">
                    <p class="article-card__meta">
                        <span class="pill"><?= e($article['category']) ?></span>
                        <span class="article-card__time"><?= icon('clock', 'icon icon--xs', 14) ?> <?= e((string) $article['read_minutes']) ?> min read</span>
                    </p>
                    <h2 class="article-card__title">
                        <a href="<?= e(url('/articles/' . $article['slug'])) ?>"><?= e($article['title']) ?></a>
                    </h2>
                    <p class="article-card__excerpt"><?= e(truncate((string) $article['excerpt'], 145)) ?></p>
                    <span class="card__link">
                        Read the guide <?= icon('arrow-right', 'icon icon--xs', 15) ?>
                    </span>
                </div>
            </article>
            <?php endforeach; ?>
        </div>
    </div>
</section>

<section class="section section--soft section--tight">
    <div class="container">
        <div class="panel panel--center">
            <h2 class="panel__title">A word on these articles</h2>
            <p class="panel__lead">
                Everything here is general health information written to help you make better questions at your
                appointment. It is not a diagnosis, and none of it should replace advice from your own doctor.
                Where an article says “go to hospital”, that is not a formality — please do.
            </p>
            <div class="panel__buttons">
                <a class="btn btn--primary" href="<?= e(url('/book-consultation')) ?>">Book a consultation</a>
                <a class="btn btn--ghost" href="<?= e(url('/faq')) ?>">Read the FAQ</a>
            </div>
        </div>
    </div>
</section>