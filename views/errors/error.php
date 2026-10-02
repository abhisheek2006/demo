<?php
/**
 * Generic error page used for 400/403/405/419/422/429/500/503.
 *
 * @var int $status
 * @var string $heading
 * @var string $message
 * @var string $requestPath
 * @var Throwable|null $exception
 */
$copy = [
    400 => 'The request could not be understood. Check the address and try again.',
    403 => 'You do not have permission to view this page.',
    405 => 'That method is not allowed for this address.',
    419 => 'Your session expired for security reasons. Reload the page and submit again.',
    422 => 'The submitted data could not be processed. Please review the form.',
    429 => 'Too many requests from your connection. Please wait a moment and try again.',
    500 => 'Something went wrong at our end. Nothing was lost — please try again, or call the clinic.',
    503 => 'The clinic website is temporarily unavailable. Please call or WhatsApp us instead.',
];
?>

<?= component('page-hero', [
    'eyebrow'   => 'Error ' . $status,
    'heading'   => $heading,
    'lead'      => $copy[$status] ?? 'The request could not be completed.',
    'trail'     => [['name' => 'Home', 'url' => '/'], ['name' => 'Error']],
    'variant'   => 'slate',
]) ?>

<section class="section">
    <div class="container container--narrow">
        <?php if (!empty($message) && $status !== 404): ?>
            <p class="error-message"><?= e($message) ?></p>
        <?php endif; ?>

        <div class="notice notice--info" role="note">
            <span class="notice__icon"><?= icon('phone', 'icon', 22) ?></span>
            <div class="notice__body">
                <h2 class="notice__title">Need help right now?</h2>
                <p class="notice__text">
                    Call <?= e(format_phone()) ?> or WhatsApp the same number. We answer both during clinic
                    hours and it is always faster than a form.
                </p>
                <p class="notice__actions">
                    <a class="btn btn--sm btn--outline" href="<?= e(tel_link()) ?>">Call now</a>
                    <a class="btn btn--sm btn--whatsapp" href="<?= e(whatsapp_link()) ?>" rel="noopener nofollow" target="_blank">WhatsApp</a>
                </p>
            </div>
        </div>

        <p class="section__more">
            <a class="btn btn--ghost" href="<?= e(url('/')) ?>"><?= icon('arrow-left', 'icon icon--xs', 15) ?> Back to the home page</a>
        </p>

        <?php if (isset($exception) && $exception instanceof Throwable && config('app.debug')): ?>
        <details class="debug">
            <summary class="debug__summary">Debug details</summary>
            <pre class="debug__body"><?= e($exception->getMessage() . "\n\n" . $exception->getFile() . ':' . $exception->getLine() . "\n\n" . $exception->getTraceAsString()) ?></pre>
        </details>
        <?php endif; ?>
    </div>
</section>