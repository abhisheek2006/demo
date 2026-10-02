<?php
declare(strict_types=1);

namespace App\Controllers;

use App\Content\Clinic;
use App\Core\Config;
use App\Core\Controller;
use App\Core\Logger;
use App\Core\Response;
use App\Core\Session;
use App\Models\Message;
use App\Services\Notifications;
use App\Services\RateLimiter;
use App\Services\SpamGuard;

/**
 * Contact form handling.
 */
final class ContactController extends Controller
{
    public function store(): Response
    {
        if (!$this->request->isPost()) {
            return $this->redirect('/contact');
        }

        $this->assertCsrf();

        $ip      = $this->request->ip();
        $allowed = RateLimiter::attempt('contact', $ip);

        if (!$allowed) {
            $retry = RateLimiter::retryAfter('contact', $ip);
            $mins  = max(1, (int) ceil($retry / 60));

            Logger::warning('form.rate_limited', 'Contact submission throttled', ['ip' => $ip]);

            if ($this->request->wantsJson()) {
                return (new Response())->json([
                    'ok'    => false,
                    'error' => 'Too many messages. Please try again in about ' . $mins . ' minute'
                        . ($mins === 1 ? '' : 's') . '.',
                ], 429)->noCache();
            }

            Session::flashError('Too many messages from this connection. Please try again in about '
                . $mins . ' minute' . ($mins === 1 ? '' : 's') . ', or call the clinic.');

            return $this->redirect('/contact');
        }

        $input          = $this->request->all();
        $input['user_agent'] = $this->request->userAgent();
        $input['_form']      = 'contact';

        $spam = SpamGuard::inspect($input, $ip);

        $validator = $this->validate([
            'name'    => 'required|string|alpha_num|min:3|max:' . (int) Config::get('forms.max_name_length', 120),
            'phone'   => 'required|phone|max:' . (int) Config::get('forms.max_phone_length', 25),
            'email'   => 'nullable|email|max:' . (int) Config::get('forms.max_email_length', 190),
            'subject' => 'required|string|max:160',
            'message' => 'required|string|no_html|min:10|max:' . (int) Config::get('forms.max_message_length', 4000),
        ]);

        if ($validator->fails()) {
            $this->validationFailed($validator, '/contact');
        }

        $clean = $validator->validated();

        $record = [
            'name'       => $clean['name'],
            'phone'      => $clean['phone'],
            'email'      => (string) ($clean['email'] ?? ''),
            'subject'    => $clean['subject'],
            'message'    => $clean['message'],
            'spam_score' => $spam['score'],
            'ip_address' => $ip,
            'user_agent' => $this->request->userAgent(),
        ];

        $result = Message::create($record);

        $mail = ['sent' => false, 'reason' => 'skipped (spam)'];
        if (!$spam['spam']) {
            $mail = Notifications::contactReceived($record + ['id' => $result['id']]);
        }

        Logger::info('message.created', 'Contact message received', [
            'stored' => $result['stored'],
            'mail'   => $mail['sent'] ? 'sent' : 'not sent',
        ]);

        if ($this->request->wantsJson()) {
            return (new Response())->json([
                'ok'      => true,
                'message' => 'Thank you. Your message has been received and we will reply shortly.',
                'stored'  => $result['stored'],
            ])->noCache();
        }

        Session::flashSuccess('Thank you — your message has reached the clinic. '
            . 'We usually reply within one working day. For anything urgent, call ' . Clinic::info()['phone'] . '.');

        return $this->redirect('/contact');
    }
}
