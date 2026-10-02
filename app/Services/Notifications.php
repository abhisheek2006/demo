<?php
declare(strict_types=1);

namespace App\Services;

use App\Content\Clinic;
use App\Content\Doctor;
use App\Core\Config;
use App\Core\Logger;

/**
 * Builds and sends the transactional emails for appointments and contact
 * messages, and always records the outcome.
 *
 * Rule: a patient enquiry is never lost because mail failed. Callers persist
 * the record first; this service only reports success or failure, and every
 * message is archived as an .eml file for manual resend.
 */
final class Notifications
{
    /**
     * @param array<string,mixed> $appointment
     * @return array{sent:bool,reason:string}
     */
    public static function appointmentRequested(array $appointment): array
    {
        $clinic   = Clinic::info();
        $mailer   = new Mailer();
        $type     = (string) ($appointment['type'] ?? 'consultation');
        $typeName = $type === 'follow_up' ? 'Follow-up review' : 'New consultation';

        $to = Config::string('mail.to_address', $clinic['email']);
        if ($to === '') {
            $to = $clinic['email'];
        }

        $subject = sprintf('[%s] %s — %s (%s)', $clinic['name'], $typeName, (string) $appointment['full_name'], (string) ($appointment['reference_code'] ?? '—'));

        $text = self::appointmentText($appointment, $typeName, true);
        $html = self::appointmentHtml($appointment, $typeName, true);

        $patientEmail = (string) ($appointment['email'] ?? '');

        $options = [
            'to'       => $to,
            'subject'  => $subject,
            'text'     => $text,
            'html'     => $html,
            'category' => 'appointment-' . $type,
            'replyTo'  => $patientEmail !== '' ? $patientEmail : Config::string('mail.reply_to', $clinic['email']),
            'headers'  => [
                'X-Swasti-Reference' => (string) ($appointment['reference_code'] ?? ''),
                'X-Mailer-Category'  => 'appointment',
                'Auto-Submitted'     => 'auto-generated',
            ],
        ];

        $result = self::deliver($mailer, $options, 'appointment:' . ($appointment['reference_code'] ?? 'unknown'));

        // Acknowledgement to the patient, only if they gave an address.
        if ($result['sent'] && $patientEmail !== '' && filter_var($patientEmail, FILTER_VALIDATE_EMAIL) !== false) {
            $ackSubject = 'We have received your ' . strtolower($typeName) . ' request — ' . $clinic['name'];

            self::deliver($mailer, [
                'to'       => $patientEmail,
                'subject'  => $ackSubject,
                'text'     => self::acknowledgementText($appointment, $typeName),
                'html'     => self::acknowledgementHtml($appointment, $typeName),
                'category' => 'acknowledgement',
                'replyTo'  => Config::string('mail.reply_to', $clinic['email']),
            ], 'acknowledgement:' . ($appointment['reference_code'] ?? 'unknown'));
        }

        return $result;
    }

    /**
     * @param array<string,mixed> $message
     * @return array{sent:bool,reason:string}
     */
    public static function contactReceived(array $message): array
    {
        $clinic = Clinic::info();
        $mailer = new Mailer();

        $to = Config::string('mail.to_address', $clinic['email']);
        if ($to === '') {
            $to = $clinic['email'];
        }

        $subject = sprintf('[%s] Website enquiry from %s', $clinic['name'], (string) $message['name']);

        $result = self::deliver($mailer, [
            'to'       => $to,
            'subject'  => $subject,
            'text'     => self::contactText($message),
            'html'     => self::contactHtml($message),
            'category' => 'contact',
            'replyTo'  => (string) ($message['email'] ?? Config::string('mail.reply_to', $clinic['email'])),
            'headers'  => ['X-Mailer-Category' => 'contact'],
        ], 'contact:' . ((int) ($message['id'] ?? 0)));

        if ($result['sent'] && !empty($message['email']) && filter_var((string) $message['email'], FILTER_VALIDATE_EMAIL) !== false) {
            self::deliver($mailer, [
                'to'       => (string) $message['email'],
                'subject'  => 'Thank you for writing to ' . $clinic['name'],
                'text'     => self::contactAckText($message),
                'html'     => self::contactAckHtml($message),
                'category' => 'acknowledgement',
                'replyTo'  => Config::string('mail.reply_to', $clinic['email']),
            ], 'contact-ack:' . ((int) ($message['id'] ?? 0)));
        }

        return $result;
    }

    /**
     * @param array<string,string> $options
     * @return array{sent:bool,reason:string}
     */
    private static function deliver(Mailer $mailer, array $options, string $context): array
    {
        if (!$mailer->isConfigured()) {
            Logger::info('mail.skipped', 'SMTP not configured; message archived only', [
                'context' => $context,
                'reason'  => $mailer->reason(),
            ]);

            return ['sent' => false, 'reason' => $mailer->reason()];
        }

        try {
            $mailer->send($options);

            return ['sent' => true, 'reason' => ''];
        } catch (\Throwable $e) {
            Logger::error('mail.failed', $e->getMessage(), ['context' => $context]);

            return ['sent' => false, 'reason' => $e->getMessage()];
        }
    }

    /* -------------------------------------------------------------------- */
    /* Templates                                                             */
    /* -------------------------------------------------------------------- */

    /** @param array<string,mixed> $a */
    private static function appointmentText(array $a, string $typeName, bool $internal): string
    {
        $lines = [];

        if ($internal) {
            $lines[] = strtoupper($typeName) . ' REQUEST';
            $lines[] = str_repeat('=', 40);
        } else {
            $lines[] = 'Your ' . strtolower($typeName) . ' request';
        }

        $lines[] = '';
        $lines[] = 'Reference : ' . (string) ($a['reference_code'] ?? '—');
        $lines[] = 'Received  : ' . date('d M Y, h:i A');
        $lines[] = '';
        $lines[] = 'PATIENT';
        $lines[] = '  Name         : ' . (string) ($a['full_name'] ?? '');
        $lines[] = '  Age          : ' . (string) ($a['age'] ?? '—');
        $lines[] = '  Phone        : ' . (string) ($a['phone'] ?? '');
        $lines[] = '  Email        : ' . (string) (($a['email'] ?? '') !== '' ? $a['email'] : 'not provided');
        $lines[] = '';
        $lines[] = 'APPOINTMENT';
        $lines[] = '  Type         : ' . $typeName;
        $lines[] = '  Preferred    : ' . (string) ($a['preferred_date'] ?? 'to be arranged') . ' at ' . (string) ($a['preferred_time'] ?? 'any time');
        $lines[] = '';
        $lines[] = 'REASON FOR VISIT';
        $lines[] = '  ' . (string) ($a['reason'] ?? '—');
        $lines[] = '';

        if (trim((string) ($a['message'] ?? '')) !== '') {
            $lines[] = 'ADDITIONAL NOTES';
            $lines[] = '  ' . str_replace("\n", "\n  ", (string) $a['message']);
            $lines[] = '';
        }

        $lines[] = 'Consent to contact : ' . ((string) ($a['consent'] ?? '0') === '1' ? 'yes' : 'not recorded');
        $lines[] = 'Source             : ' . (string) ($a['source'] ?? 'website');
        $lines[] = '';
        $lines[] = self::clinicFooterText();

        return implode("\n", $lines);
    }

    /** @param array<string,mixed> $a */
    private static function appointmentHtml(array $a, string $typeName, bool $internal): string
    {
        $rows = [
            ['Reference', (string) ($a['reference_code'] ?? '—')],
            ['Received', date('d M Y, h:i A')],
            ['Name', (string) ($a['full_name'] ?? '')],
            ['Age', (string) ($a['age'] ?? '—')],
            ['Phone', (string) ($a['phone'] ?? '')],
            ['Email', (string) (($a['email'] ?? '') !== '' ? $a['email'] : 'Not provided')],
            ['Appointment type', $typeName],
            ['Preferred date', (string) ($a['preferred_date'] ?? 'To be arranged')],
            ['Preferred time', self::slotLabel((string) ($a['preferred_time'] ?? ''))],
            ['Consent to contact', ((string) ($a['consent'] ?? '0') === '1' ? 'Yes' : 'Not recorded')],
            ['Source', (string) ($a['source'] ?? 'website')],
        ];

        $notes = trim((string) ($a['message'] ?? ''));

        return self::emailShell(
            $internal ? 'New ' . strtolower($typeName) . ' request' : 'Your ' . strtolower($typeName) . ' request',
            $internal
                ? 'A patient has requested an appointment through the website.'
                : 'Thank you — your request has reached the clinic.',
            self::definitionList($rows),
            $notes !== '' ? '<p><strong>Additional notes</strong></p>' . self::paragraphs($notes) : '',
            $internal
        );
    }

    /** @param array<string,mixed> $a */
    private static function acknowledgementText(array $a, string $typeName): string
    {
        $clinic = Clinic::info();

        $lines = [];
        $lines[] = 'Dear ' . (string) ($a['full_name'] ?? 'Patient') . ',';
        $lines[] = '';
        $lines[] = 'Thank you for requesting a ' . strtolower($typeName) . ' at ' . $clinic['name'] . '.';
        $lines[] = 'Your reference number is ' . (string) ($a['reference_code'] ?? '—') . '.';
        $lines[] = '';
        $lines[] = 'We will telephone or WhatsApp you to confirm the exact appointment time. This usually';
        $lines[] = 'happens on the same working day. If you have not heard from us, please call us on';
        $lines[] = $clinic['phone'] . '.';
        $lines[] = '';
        $lines[] = 'PLEASE BRING';
        $lines[] = '  1. All medicines you currently take, in their original packaging or with doses written down.';
        $lines[] = '  2. Any previous prescriptions, blood tests, ultrasound or X-ray reports.';
        $lines[] = '  3. A note of when your symptoms started and what makes them better or worse.';
        $lines[] = '';
        $lines[] = 'Please do not stop any medication you have been prescribed.';
        $lines[] = '';
        $lines[] = 'If your condition is an emergency — chest pain, breathing difficulty, sudden weakness,';
        $lines[] = 'heavy bleeding or loss of consciousness — call 112 or go to the nearest emergency';
        $lines[] = 'department instead of waiting for this appointment.';
        $lines[] = '';
        $lines[] = 'Warm regards,';
        $lines[] = Doctor::profile()['name'] . ', ' . Doctor::profile()['short_qualification'];
        $lines[] = $clinic['name'];
        $lines[] = $clinic['address_full'];
        $lines[] = $clinic['phone'] . ' · ' . $clinic['email'];
        $lines[] = '';
        $lines[] = 'This is an automated acknowledgement of a request received through the website.';

        return implode("\n", $lines);
    }

    /** @param array<string,mixed> $a */
    private static function acknowledgementHtml(array $a, string $typeName): string
    {
        $clinic = Clinic::info();

        return self::emailShell(
            'We have your ' . strtolower($typeName) . ' request',
            'Thank you for getting in touch with ' . $clinic['name'] . '.',
            '<p>Dear ' . self::escape((string) ($a['full_name'] ?? 'Patient')) . ',</p>'
            . '<p>Thank you for requesting a <strong>' . self::escape(strtolower($typeName)) . '</strong> with '
            . '<strong>' . self::escape($clinic['name']) . '</strong>. We will telephone or WhatsApp you to '
            . 'confirm the exact time, usually on the same working day.</p>'
            . self::callout('Your reference number', (string) ($a['reference_code'] ?? '—'))
            . '<p>If you do not hear from us, please call <a href="tel:' . self::escape($clinic['phone']) . '">'
            . self::escape($clinic['phone']) . '</a> or message us on WhatsApp.</p>'
            . '<h2>Please bring</h2>'
            . '<ol><li>All medicines you currently take, in their original packaging or with doses written down.</li>'
            . '<li>Any previous prescriptions, blood tests, ultrasound or X-ray reports.</li>'
            . '<li>A note of when your symptoms started and what makes them better or worse.</li></ol>'
            . '<p>Please do not stop any medication you have been prescribed.</p>'
            . '<p><strong>If your condition is an emergency</strong> — chest pain, breathing difficulty, sudden '
            . 'weakness, heavy bleeding or loss of consciousness — call <strong>112</strong> or go to the nearest '
            . 'emergency department instead of waiting for this appointment.</p>',
            '',
            false
        );
    }

    /** @param array<string,mixed> $m */
    private static function contactText(array $m): string
    {
        $lines = [];
        $lines[] = 'WEBSITE ENQUIRY';
        $lines[] = str_repeat('=', 40);
        $lines[] = '';
        $lines[] = 'Name     : ' . (string) ($m['name'] ?? '');
        $lines[] = 'Phone    : ' . (string) ($m['phone'] ?? '');
        $lines[] = 'Email    : ' . (string) (($m['email'] ?? '') !== '' ? $m['email'] : 'not provided');
        $lines[] = 'Subject  : ' . (string) ($m['subject'] ?? 'General enquiry');
        $lines[] = 'Received : ' . date('d M Y, h:i A');
        $lines[] = '';
        $lines[] = 'MESSAGE';
        $lines[] = str_repeat('-', 40);
        $lines[] = (string) ($m['message'] ?? '');
        $lines[] = '';
        $lines[] = self::clinicFooterText();

        return implode("\n", $lines);
    }

    /** @param array<string,mixed> $m */
    private static function contactHtml(array $m): string
    {
        $rows = [
            ['Name', (string) ($m['name'] ?? '')],
            ['Phone', (string) ($m['phone'] ?? '')],
            ['Email', (string) (($m['email'] ?? '') !== '' ? $m['email'] : 'Not provided')],
            ['Subject', (string) ($m['subject'] ?? 'General enquiry')],
            ['Received', date('d M Y, h:i A')],
        ];

        return self::emailShell(
            'Website enquiry from ' . (string) ($m['name'] ?? 'a visitor'),
            'A visitor sent a message through the website contact form.',
            self::definitionList($rows),
            '<p><strong>Message</strong></p>' . self::paragraphs((string) ($m['message'] ?? '')),
            true
        );
    }

    /** @param array<string,mixed> $m */
    private static function contactAckText(array $m): string
    {
        $clinic = Clinic::info();

        $lines = [];
        $lines[] = 'Dear ' . (string) ($m['name'] ?? 'Patient') . ',';
        $lines[] = '';
        $lines[] = 'Thank you for writing to ' . $clinic['name'] . '. Your message has reached Dr. Smriti Das';
        $lines[] = 'and we will reply personally, usually within one working day.';
        $lines[] = '';
        $lines[] = 'If your query is urgent, please call ' . $clinic['phone'] . ' or send a WhatsApp message to';
        $lines[] = 'the same number. For anything urgent or life-threatening, call 112.';
        $lines[] = '';
        $lines[] = 'Warm regards,';
        $lines[] = $clinic['name'];
        $lines[] = $clinic['address_full'];
        $lines[] = '';
        $lines[] = 'This is an automated acknowledgement of a message received through the website.';

        return implode("\n", $lines);
    }

    /** @param array<string,mixed> $m */
    private static function contactAckHtml(array $m): string
    {
        $clinic = Clinic::info();

        return self::emailShell(
            'Thank you for writing to ' . $clinic['name'],
            'Your message has reached the clinic.',
            '<p>Dear ' . self::escape((string) ($m['name'] ?? 'Patient')) . ',</p>'
            . '<p>Thank you for writing to <strong>' . self::escape($clinic['name']) . '</strong>. Your message '
            . 'has reached Dr. Smriti Das and we will reply personally, usually within one working day.</p>'
            . '<p>If your query is urgent, please call <a href="tel:' . self::escape($clinic['phone']) . '">'
            . self::escape($clinic['phone']) . '</a> or send a WhatsApp message to the same number. For anything '
            . 'urgent or life-threatening, call <strong>112</strong>.</p>',
            '',
            false
        );
    }

    /* -------------------------------------------------------------------- */
    /* HTML helpers                                                          */
    /* -------------------------------------------------------------------- */

    private static function emailShell(string $title, string $intro, string $body, string $extra, bool $internal): string
    {
        $clinic   = Clinic::info();
        $accent   = '#2f7bf6';
        $prehead  = self::escape($intro);

        return '<!DOCTYPE html><html lang="en"><head><meta charset="utf-8">'
            . '<meta name="viewport" content="width=device-width,initial-scale=1">'
            . '<meta name="color-scheme" content="light">'
            . '<title>' . self::escape($title) . '</title></head>'
            . '<body style="margin:0;padding:0;background:#f6f9fd;font-family:Segoe UI,Roboto,Helvetica Neue,Arial,sans-serif;color:#16202e;">'
            . '<div style="display:none;max-height:0;overflow:hidden;opacity:0;">' . $prehead . '</div>'
            . '<table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="background:#f6f9fd;padding:24px 12px;"><tr><td align="center">'
            . '<table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="max-width:620px;background:#ffffff;border:1px solid #e2e8f2;border-radius:14px;overflow:hidden;">'
            . '<tr><td style="background:' . $accent . ';padding:20px 28px;">'
            . '<span style="display:inline-block;font-size:17px;font-weight:700;color:#ffffff;letter-spacing:.2px;">'
            . self::escape($clinic['name']) . '</span>'
            . '<span style="display:block;font-size:12px;color:#dbeafe;margin-top:3px;">'
            . self::escape(Doctor::profile()['short_qualification']) . ' · ' . self::escape($clinic['locality'])
            . '</span></td></tr>'
            . '<tr><td style="padding:28px;">'
            . '<h1 style="margin:0 0 8px;font-size:20px;line-height:1.35;color:#123a8c;">' . self::escape($title) . '</h1>'
            . '<p style="margin:0 0 20px;font-size:15px;line-height:1.6;color:#3c4a5d;">' . self::escape($intro) . '</p>'
            . $body . $extra
            . '</td></tr>'
            . '<tr><td style="background:#f8fafc;border-top:1px solid #e2e8f2;padding:20px 28px;">'
            . '<p style="margin:0 0 4px;font-size:13px;line-height:1.6;color:#3c4a5d;"><strong>'
            . self::escape($clinic['name']) . '</strong><br>' . self::escape($clinic['address_full']) . '</p>'
            . '<p style="margin:0;font-size:13px;line-height:1.6;color:#5b6b80;">'
            . 'Phone <a href="tel:' . self::escape($clinic['phone']) . '" style="color:#1c62d8;">'
            . self::escape($clinic['phone']) . '</a> · Email <a href="mailto:' . self::escape($clinic['email'])
            . '" style="color:#1c62d8;">' . self::escape($clinic['email']) . '</a></p>'
            . '<p style="margin:12px 0 0;font-size:12px;line-height:1.6;color:#6b7a90;">'
            . 'This message was generated automatically by the ' . self::escape($clinic['name'])
            . ' website. Please do not reply to this mailbox if you receive it in error.'
            . '</p></td></tr></table></td></tr></table></body></html>';
    }

    /** @param array<int,array{0:string,1:string}> $rows */
    private static function definitionList(array $rows): string
    {
        $html = '<table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="border-collapse:collapse;font-size:14px;">';

        foreach ($rows as $index => $row) {
            $border = $index === count($rows) - 1 ? '' : 'border-bottom:1px solid #eef2f7;';
            $html  .= '<tr>'
                . '<td style="padding:8px 12px 8px 0;' . $border . 'color:#5b6b80;white-space:nowrap;vertical-align:top;">'
                . self::escape($row[0]) . '</td>'
                . '<td style="padding:8px 0;' . $border . 'color:#16202e;font-weight:600;">'
                . self::escape($row[1]) . '</td></tr>';
        }

        return $html . '</table>';
    }

    private static function callout(string $label, string $value): string
    {
        return '<div style="margin:20px 0;padding:16px 18px;background:#eef5ff;border-left:4px solid #2f7bf6;border-radius:6px;">'
            . '<div style="font-size:12px;letter-spacing:.08em;text-transform:uppercase;color:#1c62d8;">'
            . self::escape($label) . '</div>'
            . '<div style="font-size:22px;font-weight:700;color:#123a8c;margin-top:4px;letter-spacing:.02em;">'
            . self::escape($value) . '</div></div>';
    }

    private static function paragraphs(string $text): string
    {
        $text = trim(strip_tags($text));
        $out  = '';

        foreach (preg_split('/\n{2,}/', $text) ?: [] as $chunk) {
            $chunk = trim($chunk);
            if ($chunk === '') {
                continue;
            }
            $out .= '<p style="margin:0 0 12px;font-size:15px;line-height:1.65;color:#16202e;">'
                . nl2br(self::escape($chunk)) . '</p>';
        }

        return $out;
    }

    private static function clinicFooterText(): string
    {
        $clinic = Clinic::info();

        return str_repeat('-', 40) . "\n"
            . $clinic['name'] . "\n"
            . $clinic['address_full'] . "\n"
            . 'Phone: ' . $clinic['phone'] . '  |  Email: ' . $clinic['email'] . "\n"
            . "Generated automatically by the clinic website.";
    }

    private static function slotLabel(string $time): string
    {
        if ($time === '') {
            return 'Any time';
        }
        $ts = strtotime('2000-01-01 ' . $time);

        return $ts === false ? $time : date('g:i A', $ts);
    }

    /**
     * HTML-escape any value.
     *
     * Mail templates mix strings, numbers and occasionally null (a missing
     * clinic key must not fatal the whole notification), so the parameter is
     * deliberately not typed as string.
     */
    private static function escape(mixed $value): string
    {
        if ($value === null || is_bool($value)) {
            $value = $value === true ? '1' : '';
        }

        return htmlspecialchars((string) $value, ENT_QUOTES | ENT_SUBSTITUTE | ENT_HTML5, 'UTF-8');
    }
}
