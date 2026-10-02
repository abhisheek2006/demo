<?php
/**
 * Honeypot + timing fields shared by every public form.
 * The timestamp is rendered by the server and checked on submit, which is how
 * sub-two-second bot submissions are detected without any JavaScript.
 *
 * @var string $prefix namespace for the honeypot id when two forms share a page
 */
$prefix = $prefix ?? '';
?>
<div class="hp" aria-hidden="true">
    <label for="<?= $prefix ?>website">Website (leave this field empty)</label>
    <input type="text" id="<?= $prefix ?>website" name="website" value="" tabindex="-1" autocomplete="off" maxlength="120">
</div>
<input type="hidden" name="form_started_at" value="<?= e((string) time()) ?>">
