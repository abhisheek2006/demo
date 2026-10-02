<?php
/**
 * Single contact message.
 *
 * @var array<string,mixed> $clinic
 * @var array<string,mixed> $message
 */

$id       = (int) ($message['id'] ?? 0);
$name     = (string) ($message['name'] ?? '');
$phone    = (string) ($message['phone'] ?? '');
$email    = (string) ($message['email'] ?? '');
$subject  = (string) ($message['subject'] ?? 'General enquiry');
$body     = (string) ($message['message'] ?? '');
$spam     = (int) ($message['spam_score'] ?? 0);
$whatsApp = whatsapp_link('Hello ' . $name . ', regarding your enquiry to Swasti Homoeo Clinic: ');
?>

<nav class="breadcrumb" aria-label="Breadcrumb">
    <ol class="breadcrumb__list">
        <li><a href="<?= e(url('/admin')) ?>">Dashboard</a></li>
        <li><a href="<?= e(url('/admin/messages')) ?>">Messages</a></li>
        <li aria-current="page">Message #<?= e((string) $id) ?></li>
    </ol>
</nav>

<div class="admin-columns admin-columns--single">
    <section class="admin-card">
        <header class="admin-card__head">
            <div>
                <h1 class="admin-card__title"><?= e($subject) ?></h1>
                <p class="cell-meta">
                    From <?= e($name) ?> · <?= e(format_datetime((string) ($message['created_at'] ?? ''))) ?>
                    (<?= e(time_ago((string) ($message['created_at'] ?? ''))) ?>)
                </p>
            </div>
            <span class="<?= e(status_badge((string) ($message['status'] ?? 'read'))) ?>"><?= e(status_label((string) ($message['status'] ?? 'read'))) ?></span>
        </header>

        <?php if ($spam > 0): ?>
            <div class="flash flash--warning">
                <span class="flash__icon"><?= icon('alert', 'icon icon--sm', 20) ?></span>
                <p class="flash__text">Flagged by the spam filter (score <?= e((string) $spam) ?>). Treat the contents with care and do not click links.</p>
            </div>
        <?php endif; ?>

        <div class="message-body">
            <?= nl2br(e($body)) ?>
        </div>

        <dl class="detail-grid">
            <div class="detail-grid__row">
                <dt>Name</dt>
                <dd><?= e($name) ?></dd>
            </div>
            <div class="detail-grid__row">
                <dt>Phone</dt>
                <dd>
                    <?php if ($phone !== ''): ?>
                        <a href="<?= e(tel_link($phone)) ?>"><?= e(format_phone($phone)) ?></a>
                        <a class="cell-link" href="<?= e($whatsApp) ?>" target="_blank" rel="noopener">WhatsApp</a>
                    <?php else: ?>—<?php endif; ?>
                </dd>
            </div>
            <div class="detail-grid__row">
                <dt>Email</dt>
                <dd>
                    <?php if ($email !== ''): ?>
                        <a href="<?= e(mail_link('Re: ' . $subject)) ?>"><?= e($email) ?></a>
                    <?php else: ?>—<?php endif; ?>
                </dd>
            </div>
            <div class="detail-grid__row">
                <dt>Source</dt>
                <dd><?= e(ucfirst((string) ($message['source'] ?? 'website'))) ?></dd>
            </div>
            <div class="detail-grid__row">
                <dt>Received</dt>
                <dd><?= e(format_datetime((string) ($message['created_at'] ?? ''))) ?></dd>
            </div>
            <?php if (($message['ip_address'] ?? '') !== ''): ?>
            <div class="detail-grid__row">
                <dt>IP address</dt>
                <dd><code><?= e((string) $message['ip_address']) ?></code></dd>
            </div>
            <?php endif; ?>
        </dl>
    </section>

    <aside class="admin-card admin-card--side">
        <header class="admin-card__head">
            <h2 class="admin-card__title">Respond</h2>
        </header>

        <div class="button-column">
            <?php if ($email !== ''): ?>
                <a class="btn btn--primary btn--sm" href="<?= e(mail_link('Re: ' . $subject)) ?>">
                    <?= icon('mail', 'icon icon--xs', 15) ?> Reply by email
                </a>
            <?php endif; ?>
            <?php if ($phone !== ''): ?>
                <a class="btn btn--outline btn--sm" href="<?= e(tel_link($phone)) ?>">
                    <?= icon('phone', 'icon icon--xs', 15) ?> Call <?= e(format_phone($phone)) ?>
                </a>
                <a class="btn btn--outline btn--sm" href="<?= e($whatsApp) ?>" target="_blank" rel="noopener">
                    <?= icon('whatsapp', 'icon icon--xs', 15) ?> WhatsApp
                </a>
            <?php endif; ?>
        </div>

        <div class="admin-card__section">
            <h3 class="admin-card__subtitle">Clinic hours</h3>
            <ul class="hours-list hours-list--compact">
                <?php
                $hoursLabels = [
                    'monday' => 'Monday', 'tuesday' => 'Tuesday', 'wednesday' => 'Wednesday',
                    'thursday' => 'Thursday', 'friday' => 'Friday', 'saturday' => 'Saturday', 'sunday' => 'Sunday',
                ];
                foreach (\App\Content\Clinic::hours() as $day => $slot): ?>
                    <li<?= !empty($slot['closed']) ? ' class="is-closed"' : '' ?>>
                        <span><?= e($hoursLabels[$day] ?? ucfirst((string) $day)) ?></span>
                        <span>
                            <?php if (!empty($slot['closed'])): ?>
                                Closed
                            <?php else: ?>
                                <?= e(minutes_to_label((string) $slot['open'])) ?> – <?= e(minutes_to_label((string) $slot['close'])) ?>
                            <?php endif; ?>
                        </span>
                    </li>
                <?php endforeach; ?>
            </ul>
        </div>

        <div class="admin-card__section">
            <h3 class="admin-card__subtitle">Danger zone</h3>
            <p class="muted">Deleting cannot be undone. The record is removed from the database.</p>
            <form method="post" action="<?= e(url('/admin/messages/' . $id . '/delete')) ?>"
                  data-confirm="Delete this message permanently?">
                <?= csrf_field() ?>
                <input type="hidden" name="confirm" value="<?= e((string) $id) ?>">
                <button class="btn btn--danger btn--sm" type="submit"><?= icon('trash', 'icon icon--xs', 14) ?> Delete message</button>
            </form>
        </div>
    </aside>
</div>