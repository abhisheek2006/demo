<?php
/**
 * Single article.
 *
 * @var array<string,mixed> $clinic
 * @var array<string,mixed> $article
 * @var array<int,array<string,mixed>> $related
 * @var array<int,string> $booking
 */
?>

<?= component('page-hero', [
    'eyebrow'   => (string) $article['category'],
    'heading'   => (string) $article['title'],
    'lead'      => (string) $article['excerpt'],
    'trail'     => [
        ['name' => 'Home', 'url' => '/'],
        ['name' => 'Articles', 'url' => '/articles'],
        ['name' => (string) $article['title']],
    ],
    'image'     => (string) $article['image'],
    'imageAlt'  => 'Illustration for the article: ' . (string) $article['title'],
]) ?>

<section class="section">
    <div class="container article-layout">
        <article class="article">
            <p class="article__meta">
                <span class="pill"><?= e((string) $article['category']) ?></span>
                <span><?= icon('clock', 'icon icon--xs', 14) ?> <?= e((string) $article['read_minutes']) ?> minute read</span>
                <span>Written by the clinical team at <?= e((string) $clinic['name']) ?></span>
            </p>

            <div class="prose prose--article">
                <?= narrative((string) $article['body']) ?>
            </div>

            <div class="article__disclaimer">
                <?= icon('info', 'icon icon--sm', 20) ?>
                <p>
                    This article is general information, not medical advice for you personally. Do not start,
                    stop or change any medicine because of what you read here.
                </p>
            </div>

            <div class="article__cta">
                <h2 class="article__cta-title">Want this looked at properly?</h2>
                <p class="article__cta-text">
                    A first consultation takes 30 to 45 minutes and includes a full history, an examination and a
                    written prescription.
                </p>
                <ul class="article__cta-list">
                    <?php foreach (array_slice($booking, 0, 3) as $tip): ?>
                        <li><?= icon('check', 'icon icon--xs', 14) ?> <?= e($tip) ?></li>
                    <?php endforeach; ?>
                </ul>
                <div class="article__cta-actions">
                    <a class="btn btn--primary" href="<?= e(url('/book-consultation')) ?>">Book a consultation</a>
                    <a class="btn btn--ghost" href="<?= e(tel_link()) ?>"><?= e(format_phone()) ?></a>
                </div>
            </div>
        </article>

        <aside class="article-aside">
            <div class="panel panel--tint panel--sticky">
                <h2 class="panel__title panel__title--sm">Clinic details</h2>
                <ul class="mini-list">
                    <li><span><?= icon('pin', 'icon icon--xs', 15) ?></span> Solar Colony, Bhathu Basti, Port Blair 744105</li>
                    <li><span><?= icon('phone', 'icon icon--xs', 15) ?></span> <a href="<?= e(tel_link()) ?>"><?= e(format_phone()) ?></a></li>
                    <li><span><?= icon('clock-open', 'icon icon--xs', 15) ?></span> Mon–Fri 10 AM–7 PM · Sat 10 AM–3 PM</li>
                    <li><span><?= icon('mail', 'icon icon--xs', 15) ?></span> <a href="<?= e(mail_link()) ?>"><?= e((string) $clinic['email']) ?></a></li>
                </ul>
                <p class="panel__actions">
                    <a class="btn btn--primary btn--sm btn--block" href="<?= e(url('/book-consultation')) ?>">Book appointment</a>
                    <a class="btn btn--whatsapp btn--sm btn--block" href="<?= e(whatsapp_link('Hello, I read an article on your website and would like to ask about treatment.')) ?>" rel="noopener nofollow" target="_blank">
                        <?= icon('whatsapp', 'icon icon--xs', 15) ?> WhatsApp us
                    </a>
                </p>
            </div>

            <?php if ($related !== []): ?>
            <div class="panel">
                <h2 class="panel__title panel__title--sm">More guides</h2>
                <ul class="link-list">
                    <?php foreach ($related as $item): ?>
                    <li>
                        <a href="<?= e(url('/articles/' . $item['slug'])) ?>">
                            <span class="link-list__title"><?= e(truncate((string) $item['title'], 52)) ?></span>
                            <span class="link-list__meta"><?= e((string) $item['read_minutes']) ?> min · <?= e((string) $item['category']) ?></span>
                        </a>
                    </li>
                    <?php endforeach; ?>
                    <li><a class="link-list__all" href="<?= e(url('/articles')) ?>">All articles <?= icon('arrow-right', 'icon icon--xs', 14) ?></a></li>
                </ul>
            </div>
            <?php endif; ?>
        </aside>
    </div>
</section>