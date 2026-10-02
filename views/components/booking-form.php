<?php
/**
 * Appointment form — shared by /book-consultation and /book-follow-up.
 *
 * @var array<string,string> $clinic
 * @var array<int,string> $timeSlots
 * @var array<int,string> $reasons
 * @var array<int,string> $ages
 * @var array<string,mixed> $doctor
 * @var bool $isFollowUp
 */
$isFollowUp = $isFollowUp ?? false;
$formId     = $isFollowUp ? 'followup-form' : 'consultation-form';
?>
<form class="form form--booking"
      id="<?= e($formId) ?>"
      method="post"
      action="/api/booking.php"
      novalidate
      data-ajax-form
      data-csrf-url="/api/csrf.php"
      data-form-name="<?= $isFollowUp ? 'follow-up' : 'consultation' ?>">

    <?= csrf_field() ?>
    <input type="hidden" name="booking_type" value="<?= $isFollowUp ? 'follow_up' : 'consultation' ?>">
    <?= component('form-traps') ?>
    <p class="form__status" data-form-status role="status" aria-live="polite"></p>

    <div class="form__grid">
        <div class="field field--full<?= has_error('full_name') ? ' field--error' : '' ?>">
            <label class="field__label" for="full_name">Full name <span class="field__req" aria-hidden="true">*</span></label>
            <input class="field__input" type="text" id="full_name" name="full_name" required
                   autocomplete="name" maxlength="120" inputmode="text"
                   value="<?= old('full_name') ?>"
                   aria-describedby="full_name-help<?= has_error('full_name') ? ' full_name-err' : '' ?>"
                   <?= has_error('full_name') ? 'aria-invalid="true"' : '' ?>>
            <p class="field__help" id="full_name-help">As it appears on your ID or prescription.</p>
            <?php if (has_error('full_name')): ?>
                <p class="field__error" id="full_name-err"><?= e(error_for('full_name')) ?></p>
            <?php endif; ?>
        </div>

        <div class="field<?= has_error('phone') ? ' field--error' : '' ?>">
            <label class="field__label" for="phone">Phone number <span class="field__req" aria-hidden="true">*</span></label>
            <input class="field__input" type="tel" id="phone" name="phone" required
                   autocomplete="tel" maxlength="25" inputmode="tel" placeholder="10-digit mobile number"
                   value="<?= old('phone') ?>"
                   aria-describedby="phone-help<?= has_error('phone') ? ' phone-err' : '' ?>"
                   <?= has_error('phone') ? 'aria-invalid="true"' : '' ?>>
            <p class="field__help" id="phone-help">We call or WhatsApp this number to confirm your slot.</p>
            <?php if (has_error('phone')): ?>
                <p class="field__error" id="phone-err"><?= e(error_for('phone')) ?></p>
            <?php endif; ?>
        </div>

        <div class="field<?= has_error('age') ? ' field--error' : '' ?>">
            <label class="field__label" for="age">Age <span class="field__req" aria-hidden="true">*</span></label>
            <input class="field__input" type="number" id="age" name="age" required
                   min="0" max="130" step="1" inputmode="numeric" placeholder="Years"
                   value="<?= old('age') ?>"
                   list="age-presets"
                   aria-describedby="age-help<?= has_error('age') ? ' age-err' : '' ?>"
                   <?= has_error('age') ? 'aria-invalid="true"' : '' ?>>
            <datalist id="age-presets">
                <option value="1"></option><option value="5"></option><option value="10"></option>
                <option value="18"></option><option value="35"></option><option value="55"></option>
                <option value="70"></option>
            </datalist>
            <p class="field__help" id="age-help">In years. Helps us choose a safe dose and potency.</p>
            <?php if (has_error('age')): ?>
                <p class="field__error" id="age-err"><?= e(error_for('age')) ?></p>
            <?php endif; ?>
        </div>

        <div class="field<?= has_error('email') ? ' field--error' : '' ?>">
            <label class="field__label" for="email">Email <span class="field__optional">optional</span></label>
            <input class="field__input" type="email" id="email" name="email"
                   autocomplete="email" maxlength="190" placeholder="you@example.com"
                   value="<?= old('email') ?>"
                   aria-describedby="email-help<?= has_error('email') ? ' email-err' : '' ?>"
                   <?= has_error('email') ? 'aria-invalid="true"' : '' ?>>
            <p class="field__help" id="email-help">Only if you want the confirmation in writing.</p>
            <?php if (has_error('email')): ?>
                <p class="field__error" id="email-err"><?= e(error_for('email')) ?></p>
            <?php endif; ?>
        </div>

        <div class="field<?= has_error('preferred_date') ? ' field--error' : '' ?>">
            <label class="field__label" for="preferred_date">Preferred date <span class="field__req" aria-hidden="true">*</span></label>
            <input class="field__input" type="date" id="preferred_date" name="preferred_date" required
                   min="<?= e($minDate ?? date('Y-m-d')) ?>"
                   max="<?= e($maxDate ?? date('Y-m-d', strtotime('+60 days'))) ?>"
                   value="<?= old('preferred_date') ?>"
                   aria-describedby="preferred_date-help<?= has_error('preferred_date') ? ' preferred_date-err' : '' ?>"
                   <?= has_error('preferred_date') ? 'aria-invalid="true"' : '' ?>>
            <p class="field__help" id="preferred_date-help">Monday to Saturday OPD. We confirm the exact time.</p>
            <?php if (has_error('preferred_date')): ?>
                <p class="field__error" id="preferred_date-err"><?= e(error_for('preferred_date')) ?></p>
            <?php endif; ?>
        </div>

        <div class="field field--full<?= has_error('preferred_time') ? ' field--error' : '' ?>">
            <span class="field__label" id="slot-label">Preferred time slot <span class="field__req" aria-hidden="true">*</span></span>
            <div class="chips" role="radiogroup" aria-labelledby="slot-label" data-chip-group="preferred_time">
                <?php
                $selectedSlot = (string) old('preferred_time', $timeSlots[0] ?? '');
                foreach ($timeSlots as $slot): ?>
                    <label class="chip">
                        <input type="radio" name="preferred_time" value="<?= e($slot) ?>"
                               data-chip-input required
                               <?= $selectedSlot === $slot ? 'checked' : '' ?>>
                        <span class="chip__text"><?= e(minutes_to_label($slot)) ?></span>
                    </label>
                <?php endforeach; ?>
            </div>
            <p class="field__help"><?= $isFollowUp ? 'Short review slot.' : 'Morning OPD 10:00–11:30, evening OPD 4:00–6:30 PM.' ?></p>
            <?php if (has_error('preferred_time')): ?>
                <p class="field__error"><?= e(error_for('preferred_time')) ?></p>
            <?php endif; ?>
        </div>

        <div class="field field--full<?= has_error('reason') ? ' field--error' : '' ?>">
            <span class="field__label" id="reason-label">What is the main problem? <span class="field__req" aria-hidden="true">*</span></span>
            <div class="chips chips--soft" aria-labelledby="reason-label" data-chips="reason-suggestions">
                <?php foreach ($reasons as $reason): ?>
                    <button class="chip chip--button" type="button" data-chip-fill="reason" data-chip-value="<?= e($reason) ?>">
                        <?= e($reason) ?>
                    </button>
                <?php endforeach; ?>
            </div>
            <label class="field__label field__label--sub" for="reason">In your own words <span class="field__req" aria-hidden="true">*</span></label>
            <textarea class="field__input field__textarea" id="reason" name="reason" rows="4" required
                      maxlength="1500" data-counter="reason-counter"
                      aria-describedby="reason-help reason-counter<?= has_error('reason') ? ' reason-err' : '' ?>"
                      <?= has_error('reason') ? 'aria-invalid="true"' : '' ?>><?= old('reason') ?></textarea>
            <p class="field__meta">
                <span class="field__help" id="reason-help">Where is it, how long has it been there, what makes it better or worse?</span>
                <span class="field__count" id="reason-counter" data-count-for="reason">0 / 1500</span>
            </p>
            <?php if (has_error('reason')): ?>
                <p class="field__error" id="reason-err"><?= e(error_for('reason')) ?></p>
            <?php endif; ?>
        </div>

        <div class="field field--full<?= has_error('message') ? ' field--error' : '' ?>">
            <label class="field__label" for="message">Anything else we should know? <span class="field__optional">optional</span></label>
            <textarea class="field__input field__textarea" id="message" name="message" rows="3"
                      maxlength="4000" data-counter="message-counter"
                      aria-describedby="message-help message-counter<?= has_error('message') ? ' message-err' : '' ?>"><?= old('message') ?></textarea>
            <p class="field__meta">
                <span class="field__help" id="message-help">Current medicines, past treatments, allergies, pregnancy or breastfeeding.</span>
                <span class="field__count" id="message-counter" data-count-for="message">0 / 4000</span>
            </p>
            <?php if (has_error('message')): ?>
                <p class="field__error" id="message-err"><?= e(error_for('message')) ?></p>
            <?php endif; ?>
        </div>

        <div class="field field--full<?= has_error('consent') ? ' field--error' : '' ?>">
            <label class="checkbox">
                <input type="checkbox" name="consent" value="1" required
                       <?= old('consent') === '1' ? 'checked' : '' ?>
                       aria-describedby="consent-help<?= has_error('consent') ? ' consent-err' : '' ?>"
                       <?= has_error('consent') ? 'aria-invalid="true"' : '' ?>>
                <span class="checkbox__box" aria-hidden="true"><?= icon('check', 'icon icon--xs', 12) ?></span>
                <span class="checkbox__text">
                    I consent to <?= e($clinic['name'] ?? 'Swasti Homoeo Clinic') ?> contacting me by phone or WhatsApp
                    about this appointment, and I understand this form is not for emergencies.
                    <span class="field__req" aria-hidden="true">*</span>
                </span>
            </label>
            <?php if (has_error('consent')): ?>
                <p class="field__error" id="consent-err"><?= e(error_for('consent')) ?></p>
            <?php endif; ?>
        </div>
    </div>

    <div class="form__footer">
        <p class="form__note">
            <?= icon('lock', 'icon icon--xs', 15) ?>
            Your details are used only to arrange and prepare for your visit. Read our
            <a href="<?= e(url('/privacy-policy')) ?>">privacy policy</a>.
        </p>
        <button class="btn btn--primary btn--lg" type="submit" data-submit-button>
            <span data-submit-label><?= $isFollowUp ? 'Request follow-up review' : 'Request consultation' ?></span>
            <span class="btn__spinner" data-submit-spinner hidden aria-hidden="true"></span>
        </button>
    </div>

    <p class="form__status" role="status" aria-live="polite" data-form-status></p>
</form>
