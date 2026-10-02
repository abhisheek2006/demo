<?php
declare(strict_types=1);

namespace App\Controllers;

use App\Content\Booking;
use App\Content\Clinic;
use App\Content\Doctor;
use App\Core\Config;
use App\Core\Controller;
use App\Core\Logger;
use App\Core\Response;
use App\Core\Session;
use App\Models\Appointment;
use App\Services\Notifications;
use App\Services\RateLimiter;
use App\Services\SpamGuard;

/**
 * Appointment request handling for both new consultations and follow-up
 * reviews. Both forms share the same pipeline: CSRF -> rate limit ->
 * honeypot/dwell time -> validation -> persist -> notify.
 */
final class AppointmentController extends Controller
{
    public function consultation(): Response
    {
        return $this->form(Booking::TYPE_CONSULTATION);
    }

    public function followUp(): Response
    {
        return $this->form(Booking::TYPE_FOLLOW_UP);
    }

    private function form(string $type): Response
    {
        $isFollowUp = $type === Booking::TYPE_FOLLOW_UP;
        $slug       = $isFollowUp ? 'book-follow-up' : 'book-consultation';
        $title      = $isFollowUp ? 'Book a Follow-Up Review' : 'Book a Consultation';
        $heading    = $isFollowUp
            ? 'Book your follow-up review'
            : 'Book your consultation';

        $intro = $isFollowUp
            ? 'Already under treatment at ' . Clinic::NAME . '? Book a short follow-up review so we can '
                . 'check how the prescription is working and continue, reduce or change it as needed.'
            : 'Tell us briefly what is troubling you and when you would like to come in. We will call you '
                . 'to confirm the exact appointment time.';

        $this->setSeo([
            'title'       => ($isFollowUp ? 'Book a Follow-Up Review' : 'Book a Consultation') . ' | ' . Clinic::NAME,
            'description' => ($isFollowUp
                ? 'Book a follow-up review with Dr. Smriti Das at ' . Clinic::NAME . ' in Port Blair. Short, focused review of your current homoeopathic prescription.'
                : 'Request a homoeopathic consultation at ' . Clinic::NAME . ', Port Blair. Choose a convenient morning or evening slot and we will confirm by phone or WhatsApp.'),
            'canonical'   => url('/' . $slug),
            'noindex'     => false,
        ]);

        return $this->view($isFollowUp ? 'pages/book-follow-up' : 'pages/book-consultation', [
            'page'        => $slug,
            'type'        => $type,
            'clinic'      => Clinic::info(),
            'doctor'      => Doctor::profile(),
            'title'       => $title,
            'heading'     => $heading,
            'intro'       => $intro,
            'timeSlots'   => Booking::timeSlots(),
            'reasons'     => Booking::commonReasons(),
            'ages'        => Booking::ageRange(),
            'preparation' => Booking::preparation(),
            'afterBooking'=> Booking::afterBooking(),
            'emergency'   => Clinic::emergencyGuidance(),
            'maxDate'     => date('Y-m-d', strtotime('+' . Booking::maxAdvanceDays() . ' days')),
            'minDate'     => date('Y-m-d'),
        ]);
    }

    public function storeConsultation(): Response
    {
        return $this->store(Booking::TYPE_CONSULTATION);
    }

    public function storeFollowUp(): Response
    {
        return $this->store(Booking::TYPE_FOLLOW_UP);
    }

    public function store(string $type = ''): Response
    {
        $type    = $type === Booking::TYPE_FOLLOW_UP ? Booking::TYPE_FOLLOW_UP : Booking::TYPE_CONSULTATION;
        $isFollow = $type === Booking::TYPE_FOLLOW_UP;
        $path    = $isFollow ? '/book-follow-up' : '/book-consultation';

        if (!$this->request->isPost()) {
            return $this->redirect($path);
        }

        $this->assertCsrf();

        // --- Rate limiting ---------------------------------------------------
        $ip = $this->request->ip();
        $allowed = RateLimiter::attempt($isFollow ? 'follow_up' : 'consultation', $ip);

        if (!$allowed) {
            $retry = RateLimiter::retryAfter($isFollow ? 'follow_up' : 'consultation', $ip);
            $mins  = max(1, (int) ceil($retry / 60));

            Logger::warning('form.rate_limited', 'Submission throttled', ['ip' => $ip, 'form' => $type]);

            $this->respondOrRedirect([
                'ok'    => false,
                'error' => 'Too many requests. Please try again in about ' . $mins . ' minute'
                    . ($mins === 1 ? '' : 's') . ', or call the clinic on ' . Clinic::NAME . '.',
            ], $path, 429);
        }

        // --- Honeypot, dwell time, spam scoring ------------------------------
        $input = $this->request->all();
        $input['user_agent'] = $this->request->userAgent();
        $input['_form']      = $type;

        $spam = SpamGuard::inspect($input, $ip);

        // --- Validation ------------------------------------------------------
        $validator = $this->validate([
            'full_name'      => 'required|string|alpha_num|min:3|max:' . (int) Config::get('forms.max_name_length', 120),
            'phone'          => 'required|phone|max:' . (int) Config::get('forms.max_phone_length', 25),
            'email'          => 'nullable|email|max:' . (int) Config::get('forms.max_email_length', 190),
            'age'            => 'required|integer|gte:0|lte:130',
            'preferred_date' => 'required|date|after_or_equal_today',
            'preferred_time' => 'required|time',
            'reason'         => 'required|string|no_html|max:' . (int) Config::get('forms.max_reason_length', 1500),
            'message'        => 'nullable|string|no_html|max:' . (int) Config::get('forms.max_message_length', 4000),
            'consent'        => 'required|accepted',
        ]);

        // Slot must be one we actually offer.
        if (!in_array($validator->string('preferred_time'), Booking::timeSlots(), true)) {
            $validator->reject('preferred_time', 'Choose one of the available appointment slots.');
        }

        $bookingDate = $validator->string('preferred_date');
        if ($bookingDate !== '') {
            $limit = strtotime('+' . Booking::maxAdvanceDays() . ' days');
            $ts    = strtotime($bookingDate);

            if ($ts !== false && $ts > $limit) {
                $validator->reject(
                    'preferred_date',
                    'Appointments can be booked up to ' . Booking::maxAdvanceDays() . ' days in advance.'
                );
            }
            if ($ts !== false && $ts < strtotime('today')) {
                $validator->reject('preferred_date', 'Choose today or a future date.');
            }
        }

        if ($validator->fails()) {
            $this->validationFailed($validator, $path);
        }

        $clean = $validator->validated();

        // --- Persist ---------------------------------------------------------
        $record = [
            'type'           => $type,
            'full_name'      => $clean['full_name'],
            'phone'          => $clean['phone'],
            'email'          => (string) ($clean['email'] ?? ''),
            'age'            => (string) $clean['age'],
            'preferred_date' => $bookingDate,
            'preferred_time' => (string) $clean['preferred_time'],
            'reason'         => $clean['reason'],
            'message'        => (string) ($clean['message'] ?? ''),
            'consent'        => 1,
            'spam_score'     => $spam['score'],
            'ip_address'     => $ip,
            'user_agent'     => $this->request->userAgent(),
            'source'         => $isFollow ? 'book-follow-up' : 'book-consultation',
            'admin_note'     => $spam['spam']
                ? 'Flagged automatically as spam: ' . implode('; ', $spam['reasons'])
                : null,
        ];

        $result = Appointment::create($record);

        // --- Notify (never blocks the response) -----------------------------
        $mail = ['sent' => false, 'reason' => 'skipped (spam)'];

        if (!$spam['spam']) {
            $mail = Notifications::appointmentRequested($record + [
                'reference_code' => $result['reference'],
                'id'             => $result['id'],
            ]);
        }

        Logger::info('appointment.created', 'Appointment request received', [
            'reference' => $result['reference'],
            'stored'    => $result['stored'],
            'type'      => $type,
            'mail'      => $mail['sent'] ? 'sent' : 'not sent',
        ]);

        Session::flashSuccess(sprintf(
            'Thank you, %s. Your %s request has been received. Your reference number is %s — we will call you to confirm the time.',
            explode(' ', (string) $clean['full_name'])[0],
            $isFollow ? 'follow-up' : 'consultation',
            $result['reference']
        ));

        return $this->respondOrRedirect([
            'ok'        => true,
            'reference' => $result['reference'],
            'message'   => 'Your request has been received. We will call you to confirm the appointment.',
            'stored'    => $result['stored'],
        ], $path);
    }

    /**
     * @param array<string,mixed> $payload
     */
    private function respondOrRedirect(array $payload, string $path, int $status = 200): Response
    {
        if ($this->request->wantsJson()) {
            return (new Response())->json($payload, $status)->noCache();
        }

        if ($status !== 200) {
            Session::flashError((string) ($payload['error'] ?? 'Your request could not be processed.'));
        }

        return $this->redirect($path);
    }
}
