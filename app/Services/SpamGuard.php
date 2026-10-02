<?php
declare(strict_types=1);

namespace App\Services;

use App\Core\Config;
use App\Core\Logger;

/**
 * Layered spam defence for public forms.
 *
 * Signals, in order of severity:
 *   honeypot filled            -> certain spam
 *   dwell time too short/long  -> bot
 *   URL / email in free text   -> promotional spam
 *   keyword score              -> commercial spam
 *   link stuffing              -> SEO spam
 *
 * The result is a boolean plus a human-readable reason. Rejected submissions
 * are still stored so a real patient blocked by a false positive can be found
 * in the admin panel.
 */
final class SpamGuard
{
    /** @var string[] */
    private const SPAM_KEYWORDS = [
        'viagra', 'cialis', 'kamagra', 'casino', 'bet365', 'poker', 'payday loan',
        'crypto giveaway', 'bitcoin double', 'forex signals', 'seo services', 'backlinks',
        'guest post', 'article spinning', 'web traffic', 'buy followers', 'cheap drugs',
        'online pharmacy', 'replica watch', 'escort service', 'work from home earn',
        'telegram channel', 'whatsapp group buy', 'investment plan', 'loan without',
        'airdrop', 'nft mint', 'free money', 'click here now', 'order now cheap',
        'discount replica', 'weight loss pill', 'cialis online',
    ];

    /**
     * @param array<string,mixed> $input
     * @return array{spam:bool,score:int,reasons:string[]}
     */
    public static function inspect(array $input, string $ip = ''): array
    {
        $reasons = [];
        $score   = 0;

        // 1. Honeypot ------------------------------------------------------------
        $honeypot = trim((string) ($input['website'] ?? ''));
        if ($honeypot !== '') {
            $reasons[] = 'Honeypot field was filled in.';
            $score    += 100;
        }

        // 2. Dwell time ---------------------------------------------------------
        $startedAt = (int) ($input['form_started_at'] ?? 0);
        $min       = (int) Config::get('forms.min_dwell_seconds', 3);
        $max       = (int) Config::get('forms.max_dwell_seconds', 86400);

        if ($startedAt <= 0) {
            $reasons[] = 'Missing form timestamp.';
            $score    += 40;
        } else {
            $elapsed = time() - $startedAt;
            if ($elapsed < $min) {
                $reasons[] = 'Form submitted in under ' . $min . ' seconds.';
                $score    += 80;
            } elseif ($elapsed > $max) {
                $reasons[] = 'Form page was open for more than 24 hours.';
                $score    += 30;
            }
        }

        // 3. Free-text analysis -------------------------------------------------
        $textParts = [];
        foreach (['full_name', 'reason', 'message', 'email', 'subject'] as $field) {
            $value = (string) ($input[$field] ?? '');
            if ($value !== '') {
                $textParts[] = $value;
            }
        }
        $text = implode(' ', $textParts);
        $low  = mb_strtolower($text);

        $links = \App\Core\Validator::countLinks($text);
        $maxAllowed = (int) Config::get('forms.max_links_allowed', 1);
        if ($links > $maxAllowed) {
            $reasons[] = 'Free text contained ' . $links . ' links or email addresses.';
            $score    += 60;
        }

        foreach (self::SPAM_KEYWORDS as $keyword) {
            if (str_contains($low, $keyword)) {
                $reasons[] = 'Spam keyword detected: "' . $keyword . '".';
                $score    += 50;
                break;
            }
        }

        // 4. Obvious character abuse -------------------------------------------
        if (preg_match('/(.)\1{9,}/u', $text) === 1) {
            $reasons[] = 'Repeated character pattern detected.';
            $score    += 25;
        }

        if (preg_match('/\p{L}/u', $text) === 1) {
            $letters   = preg_match_all('/\p{L}/u', $text);
            $uppercase = preg_match_all('/\p{Lu}/u', $text);
            $isIntl    = preg_match('/[\x{0900}-\x{097F}]/u', $text) === 1;

            if ($letters !== false && $letters > 40 && $uppercase !== false && $letters > 0) {
                $ratio = $uppercase / $letters;
                if ($ratio > 0.85 && !$isIntl) {
                    $reasons[] = 'Free text is almost entirely capital letters.';
                    $score    += 20;
                }
            }
        }

        if (preg_match('/<[a-z][^>]*>/i', $text) === 1) {
            $reasons[] = 'Free text contained HTML markup.';
            $score    += 45;
        }

        // 5. Phone in a name field (a classic lead-scraper trick) ---------------
        $name = (string) ($input['full_name'] ?? '');
        if ($name !== '' && preg_match_all('/\d/', $name) > 6) {
            $reasons[] = 'Name field contains too many digits.';
            $score    += 30;
        }

        // 6. Header-based heuristics -------------------------------------------
        $ua = (string) ($input['user_agent'] ?? '');
        if ($ua === '' ) {
            $reasons[] = 'No browser user agent supplied.';
            $score    += 25;
        } elseif (preg_match('/\b(bot|crawler|spider|curl|wget|python|go-http|scrapy|headless)\b/i', $ua) === 1) {
            $reasons[] = 'Automated client user agent.';
            $score    += 45;
        }

        if (in_array($ip, ['0.0.0.0', '127.0.0.1'], true) && $ip !== '') {
            $score += 5;
        }

        $isSpam = $score >= 60;

        if ($isSpam) {
            Logger::warning('spam.blocked', implode(' | ', $reasons), [
                'score' => $score,
                'ip'    => $ip,
                'form'  => $input['_form'] ?? 'unknown',
            ]);
        }

        return [
            'spam'    => $isSpam,
            'score'   => $score,
            'reasons' => $reasons,
        ];
    }
}
