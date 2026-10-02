<?php
/**
 * Create / edit a single FAQ entry.
 *
 * @var array<string,mixed> $clinic
 * @var array<string,mixed>|null $faq
 * @var string[] $categories
 */

$faq        = is_array($faq ?? null) ? $faq : [];
$categories = is_array($categories ?? null) ? $categories : [];
$isEdit     = $faq !== [];
$faqId      = (int) ($faq['id'] ?? 0);
$action     = $isEdit
    ? url('/admin/faqs/' . $faqId . '/edit')
    : url('/admin/faqs/new');

// Fallback values from a failed submission take precedence over stored data.
$value = static function (string $key, string $default = '') use ($faq): string {
    $old = old_raw($key, null);

    return is_string($old) ? $old : (string) ($faq[$key] ?? $default);
};
?>

<nav class="breadcrumb" aria-label="Breadcrumb">
    <ol class="breadcrumb__list">
        <li><a href="<?= e(url('/admin')) ?>">Dashboard</a></li>
        <li><a href="<?= e(url('/admin/faqs')) ?>">FAQs</a></li>
        <li aria-current="page"><?= $isEdit ? 'Edit entry' : 'New entry' ?></li>
    </ol>
</nav>

<div class="admin-head">
    <div>
        <h1 class="admin-head__title"><?= $isEdit ? 'Edit FAQ entry' : 'New FAQ entry' ?></h1>
        <p class="admin-head__sub">
            <?= $isEdit
                ? 'Changes appear on the public FAQ page and in its structured data immediately.'
                : 'Published entries appear on the public FAQ page and in its FAQPage structured data.' ?>
        </p>
    </div>
</div>

<?php if (error_for('form') !== ''): ?>
    <div class="flash flash--error" role="alert">
        <span class="flash__icon"><?= icon('alert', 'icon icon--sm', 20) ?></span>
        <p class="flash__text"><?= error_for('form') ?></p>
    </div>
<?php endif; ?>

<div class="admin-columns admin-columns--form">
    <section class="admin-card">
        <form class="form" method="post" action="<?= e($action) ?>" novalidate>
            <?= csrf_field() ?>

            <div class="field<?= has_error('question') ? ' field--error' : '' ?>">
                <label class="field__label" for="question">Question</label>
                <input class="field__input" type="text" id="question" name="question" required
                       minlength="8" maxlength="255" value="<?= e($value('question')) ?>"
                       placeholder="How long does a first consultation take?">
                <p class="field__hint">Write it the way a patient would ask it. 8–255 characters.</p>
                <?php if (has_error('question')): ?><p class="field__error"><?= error_for('question') ?></p><?php endif; ?>
            </div>

            <div class="field<?= has_error('answer') ? ' field--error' : '' ?>">
                <label class="field__label" for="answer">Answer</label>
                <textarea class="field__input field__input--area" id="answer" name="answer" rows="9"
                          required minlength="20" maxlength="5000"
                          placeholder="Answer plainly in two or three sentences. No medical promises."><?= e($value('answer')) ?></textarea>
                <p class="field__hint">20–5000 characters. Keep it factual and avoid guarantees or claims of cure.</p>
                <?php if (has_error('answer')): ?><p class="field__error"><?= error_for('answer') ?></p><?php endif; ?>
            </div>

            <div class="form-row">
                <div class="field<?= has_error('category') ? ' field--error' : '' ?>">
                    <label class="field__label" for="category">Category</label>
                    <input class="field__input" type="text" id="category" name="category" required
                           maxlength="80" list="faq-categories" value="<?= e($value('category', 'General')) ?>">
                    <datalist id="faq-categories">
                        <?php foreach ($categories as $category): ?>
                            <option value="<?= e($category) ?>"></option>
                        <?php endforeach; ?>
                    </datalist>
                    <?php if (has_error('category')): ?><p class="field__error"><?= error_for('category') ?></p><?php endif; ?>
                </div>

                <div class="field<?= has_error('sort_order') ? ' field--error' : '' ?>">
                    <label class="field__label" for="sort_order">Sort order</label>
                    <input class="field__input" type="number" id="sort_order" name="sort_order" required
                           min="0" max="9999" step="1" value="<?= e($value('sort_order', '0')) ?>">
                    <p class="field__hint">Lower numbers appear first.</p>
                    <?php if (has_error('sort_order')): ?><p class="field__error"><?= error_for('sort_order') ?></p><?php endif; ?>
                </div>
            </div>

            <fieldset class="field field--check">
                <legend class="field__label">Visibility</legend>
                <?php $publishedValue = old_raw('is_published', $faq['is_published'] ?? 1); ?>
                <label class="check">
                    <input type="radio" name="is_published" value="1"<?= (string) $publishedValue === '1' ? ' checked' : '' ?>>
                    <span>Published — show on the public FAQ page</span>
                </label>
                <label class="check">
                    <input type="radio" name="is_published" value="0"<?= (string) $publishedValue === '0' ? ' checked' : '' ?>>
                    <span>Hidden — keep as a draft</span>
                </label>
                <?php if (has_error('is_published')): ?><p class="field__error"><?= error_for('is_published') ?></p><?php endif; ?>
            </fieldset>

            <div class="form-actions">
                <button class="btn btn--primary" type="submit">
                    <?= icon('check', 'icon icon--xs', 15) ?> <?= $isEdit ? 'Save changes' : 'Create FAQ' ?>
                </button>
                <a class="btn btn--ghost" href="<?= e(url('/admin/faqs')) ?>">Cancel</a>
            </div>
        </form>
    </section>

    <aside class="admin-card admin-card--side">
        <header class="admin-card__head">
            <h2 class="admin-card__title">Good answers</h2>
        </header>
        <ul class="check-list">
            <li><?= icon('check-circle', 'icon icon--sm', 18) ?> Answer what is asked, in the first sentence.</li>
            <li><?= icon('check-circle', 'icon icon--sm', 18) ?> Say where homoeopathy fits and where it does not.</li>
            <li><?= icon('check-circle', 'icon icon--sm', 18) ?> Never promise a cure or a guaranteed result.</li>
            <li><?= icon('check-circle', 'icon icon--sm', 18) ?> End with “Book a consultation” or the clinic phone number.</li>
            <li><?= icon('alert', 'icon icon--sm', 18) ?> Keep emergency and acute-symptom advice on a dedicated category and tell patients to call or visit an emergency department.</li>
        </ul>
        <p class="muted">
            The FAQ page also emits <code>FAQPage</code> JSON-LD, so published entries are eligible for
            rich results. Hidden entries are skipped.
        </p>
    </aside>
</div>