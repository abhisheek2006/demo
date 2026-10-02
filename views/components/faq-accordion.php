<?php
/**
 * Accessible FAQ accordion. Works without JavaScript; JS only adds the
 * search filter and deep-link opening.
 *
 * @var array<string,array<int,array<string,mixed>>> $grouped
 * @var string $id
 * @var bool $searchable
 * @var string $intro
 */
$grouped    = $grouped    ?? [];
$id         = $id         ?? 'faq';
$searchable = $searchable ?? false;
$intro      = $intro      ?? null;
$total      = array_sum(array_map('count', $grouped));
?>
<div class="faq" id="<?= e($id) ?>" data-faq>
    <?php if ($searchable): ?>
    <div class="faq__search">
        <label class="faq__search-label" for="<?= e($id) ?>-search">Search a question</label>
        <div class="faq__search-field">
            <span class="faq__search-icon" aria-hidden="true"><?= icon('search', 'icon icon--sm', 18) ?></span>
            <input class="faq__search-input" type="search" id="<?= e($id) ?>-search"
                   data-faq-search placeholder="Try “fever”, “follow-up”, “pregnancy”…"
                   autocomplete="off" maxlength="80">
            <span class="faq__search-count" data-faq-count aria-live="polite"></span>
        </div>
    </div>
    <?php endif; ?>

    <?php if (!empty($intro)): ?>
        <p class="faq__intro"><?= e($intro) ?></p>
    <?php endif; ?>

    <?php if ($total === 0): ?>
        <p class="faq__empty">No questions have been published yet. Please call the clinic and we will answer you directly.</p>
    <?php endif; ?>

    <?php foreach ($grouped as $category => $items): ?>
    <section class="faq__group" data-faq-group>
        <h3 class="faq__group-title"><?= e((string) $category) ?></h3>
        <div class="accordion">
            <?php foreach ($items as $item):
                $question = (string) ($item['question'] ?? $item['q'] ?? '');
                $answer   = (string) ($item['answer']   ?? $item['a'] ?? '');
                ?>
            <details class="accordion__item" data-faq-item
                     data-search="<?= e(mb_strtolower($question . ' ' . $answer)) ?>">
                <summary class="accordion__summary">
                    <span class="accordion__question"><?= e($question) ?></span>
                    <span class="accordion__marker" aria-hidden="true"><?= icon('chevron', 'icon icon--sm', 18) ?></span>
                </summary>
                <div class="accordion__panel">
                    <?php foreach (preg_split('/\n{2,}/', $answer) ?: [$answer] as $paragraph): ?>
                        <p><?= e(trim($paragraph)) ?></p>
                    <?php endforeach; ?>
                </div>
            </details>
            <?php endforeach; ?>
        </div>
    </section>
    <?php endforeach; ?>

    <p class="faq__empty" data-faq-empty hidden>
        <?= icon('search', 'icon icon--xs', 16) ?> No question matches that search. Try a different word, or
        <a href="<?= e(url('/contact')) ?>">ask us directly</a>.
    </p>
</div>