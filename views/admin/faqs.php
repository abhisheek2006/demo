<?php
/**
 * FAQ list with inline edit and delete.
 *
 * @var array<string,mixed> $clinic
 * @var array<int,array<string,mixed>> $rows
 * @var string[] $categories
 */

$rows       = is_array($rows ?? null) ? $rows : [];
$categories = is_array($categories ?? null) ? $categories : [];
$highlight  = filter_var($_GET['highlight'] ?? '', FILTER_VALIDATE_INT) ?: 0;

$groups = [];
foreach ($rows as $row) {
    $groups[(string) ($row['category'] ?? 'General')][] = $row;
}
ksort($groups);
?>

<div class="admin-head">
    <div>
        <h1 class="admin-head__title">FAQs</h1>
        <p class="admin-head__sub">
            <?= e((string) count($rows)) ?> entr<?= count($rows) === 1 ? 'y' : 'ies' ?> across
            <?= e((string) count($groups)) ?> categor<?= count($groups) === 1 ? 'y' : 'ies' ?>. Published entries
            appear on the public FAQ page and in its FAQPage structured data.
        </p>
    </div>
    <div class="admin-head__actions">
        <a class="btn btn--outline btn--sm" href="<?= e(url('/faq')) ?>" target="_blank" rel="noopener">
            <?= icon('eye', 'icon icon--xs', 15) ?> View public page
        </a>
        <a class="btn btn--primary btn--sm" href="<?= e(url('/admin/faqs/new')) ?>">
            <?= icon('plus', 'icon icon--xs', 15) ?> New FAQ
        </a>
    </div>
</div>

<?php if ($rows === []): ?>
    <div class="admin-card">
        <div class="empty">
            <?= icon('list', 'icon', 28) ?>
            <p class="empty__text">
                The database holds no FAQ entries, so the public page is showing the bundled content from
                <code>app/Content/FaqData.php</code>. Add an entry to start managing FAQs here.
            </p>
            <a class="btn btn--primary btn--sm" href="<?= e(url('/admin/faqs/new')) ?>"><?= icon('plus', 'icon icon--xs', 15) ?> Add the first FAQ</a>
        </div>
    </div>
<?php else: ?>

<?php foreach ($groups as $category => $items): ?>
<section class="admin-card">
    <header class="admin-card__head">
        <h2 class="admin-card__title"><?= e($category) ?></h2>
        <span class="muted"><?= e((string) count($items)) ?> entr<?= count($items) === 1 ? 'y' : 'ies' ?></span>
    </header>

    <ul class="faq-admin-list">
        <?php foreach ($items as $row): ?>
            <?php
            $id        = (int) ($row['id'] ?? 0);
            $published = (int) ($row['is_published'] ?? 0) === 1;
            ?>
            <li class="faq-admin<?= $id === $highlight ? ' is-highlight' : '' ?>"<?= $id === $highlight ? ' data-highlight' : '' ?>>
                <div class="faq-admin__body">
                    <p class="faq-admin__q"><?= e((string) ($row['question'] ?? '')) ?></p>
                    <p class="faq-admin__a"><?= e(truncate((string) ($row['answer'] ?? ''), 220)) ?></p>
                    <p class="faq-admin__meta">
                        <span class="<?= $published ? 'badge badge--success' : 'badge badge--muted' ?>">
                            <?= $published ? 'Published' : 'Hidden' ?>
                        </span>
                        <span class="cell-meta">
                            order <?= e((string) ($row['sort_order'] ?? 0)) ?>
                            · updated <?= e(time_ago((string) ($row['updated_at'] ?? $row['created_at'] ?? ''))) ?>
                        </span>
                    </p>
                </div>
                <div class="faq-admin__actions">
                    <a class="btn btn--outline btn--xs" href="<?= e(url('/admin/faqs/' . $id . '/edit')) ?>">
                        <?= icon('edit', 'icon icon--xs', 14) ?> Edit
                    </a>
                    <form method="post" action="<?= e(url('/admin/faqs/' . $id . '/delete')) ?>"
                          data-confirm="Delete this FAQ entry permanently?">
                        <?= csrf_field() ?>
                        <input type="hidden" name="confirm" value="<?= e((string) $id) ?>">
                        <button class="btn btn--danger btn--xs" type="submit" aria-label="Delete FAQ entry">
                            <?= icon('trash', 'icon icon--xs', 14) ?>
                        </button>
                    </form>
                </div>
            </li>
        <?php endforeach; ?>
    </ul>
</section>
<?php endforeach; ?>

<p class="muted">
    Entries without a category are grouped under “General”. To change the available categories, edit
    <code>app/Content/FaqData.php</code>.
</p>

<?php endif; ?>

<?php if ($categories !== []): ?>
<details class="admin-help">
    <summary>Categories in use</summary>
    <p><?= e(implode(' · ', $categories)) ?></p>
</details>
<?php endif; ?>