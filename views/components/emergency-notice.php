<?php
/**
 * Emergency / scope-of-practice notice.
 *
 * @var array<int,string> $items
 * @var string $title
 */
$items = $items ?? \App\Content\Clinic::emergencyGuidance();
$title = $title ?? 'If this is an emergency';
?>
<aside class="notice notice--warning" role="note" aria-labelledby="emergency-heading">
    <span class="notice__icon"><?= icon('alert', 'icon', 24) ?></span>
    <div class="notice__body">
        <h2 class="notice__title" id="emergency-heading"><?= e($title) ?></h2>
        <ul class="notice__list">
            <?php foreach ($items as $item): ?>
                <li><?= e($item) ?></li>
            <?php endforeach; ?>
        </ul>
        <p class="notice__actions">
            <a class="btn btn--sm btn--outline" href="<?= e(tel_link()) ?>">Call the clinic</a>
        </p>
    </div>
</aside>
