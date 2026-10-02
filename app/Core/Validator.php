<?php
declare(strict_types=1);

namespace App\Core;

use App\Core\Exceptions\ValidationException;

/**
 * Server-side validation engine.
 *
 * Rules are declared as arrays of pipe-separated strings:
 *   'email'  => 'required|email|max:190'
 *   'phone'  => 'required|phone'
 *   'consent'=> 'required|accepted'
 */
final class Validator
{
    /** @var array<string,string> */
    private array $errors = [];

    /** @var array<string,mixed> */
    private array $valid = [];

    /** @param array<string,mixed> $data */
    public function __construct(private array $data)
    {
    }

    /**
     * @param array<string,mixed> $data
     * @param array<string,string|array<int,string>> $rules
     * @param array<string,string> $labels
     */
    public static function make(array $data, array $rules, array $labels = []): self
    {
        $validator = new self($data);
        $validator->apply($rules, $labels);

        return $validator;
    }

    /**
     * @param array<string,string|array<int,string>> $rules
     * @param array<string,string> $labels
     */
    public function apply(array $rules, array $labels = []): self
    {
        foreach ($rules as $field => $ruleSet) {
            $ruleList = is_array($ruleSet) ? $ruleSet : explode('|', (string) $ruleSet);
            $value    = $this->data[$field] ?? null;
            $label    = $labels[$field] ?? ucwords(str_replace(['_', '.'], ' ', $field));
            $filled   = $this->isFilled($value);
            $failed   = false;

            foreach ($ruleList as $rule) {
                [$name, $parameters] = $this->parseRule((string) $rule);

                if ($name === '') {
                    continue;
                }

                // Implicit rules: only run when a value is present.
                if (!$filled && in_array($name, self::IMPLICIT, true)) {
                    continue;
                }

                $error = $this->runRule($name, $value, $parameters, $label, $field);
                if ($error !== null) {
                    $this->addError($field, $error);
                    $failed = true;
                    break;
                }
            }

            if (!$failed && !$this->hasError($field)) {
                $this->valid[$field] = $this->clean($value, $ruleList);
            }
        }

        return $this;
    }

    private const IMPLICIT = [
        // "nullable" plus the content rules an empty value trivially satisfies:
        // absent or blank optional input must not produce "must be text".
        'nullable', 'string', 'no_html', 'utf8',
        'email', 'phone', 'min', 'max', 'regex', 'in', 'date', 'time',
        'min_digits', 'max_digits', 'alpha_num', 'url', 'between',
        'integer', 'numeric', 'gte', 'lte', 'in_list',
    ];

    /** @return array{string,string[]} */
    private function parseRule(string $rule): array
    {
        if (!str_contains($rule, ':')) {
            return [$rule, []];
        }

        [$name, $rest] = explode(':', $rule, 2);

        return [$name, array_map('trim', explode(',', $rest))];
    }

    private function isFilled(mixed $value): bool
    {
        if ($value === null || $value === false) {
            return false;
        }
        if (is_array($value)) {
            return $value !== [];
        }

        $string = trim((string) $value);

        return $string !== '';
    }

    private function hasError(string $field): bool
    {
        return isset($this->errors[$field]);
    }

    private function addError(string $field, string $message): void
    {
        if (!isset($this->errors[$field])) {
            $this->errors[$field] = $message;
        }
    }

    /** Attach a bespoke error message to a field. */
    public function reject(string $field, string $message): self
    {
        $this->errors[$field] = $message;

        return $this;
    }

    /** Raw submitted value for a field. */
    public function value(string $field, mixed $default = null): mixed
    {
        return $this->data[$field] ?? $default;
    }

    /** Trimmed string value for a field. */
    public function string(string $field, string $default = ''): string
    {
        $value = $this->data[$field] ?? $default;

        return is_scalar($value) ? trim((string) $value) : $default;
    }

    /** @return array<string,string> */
    private function messages(): array
    {
        return [
            'required'    => '{label} is required.',
            'filled'      => '{label} is required.',
            'string'      => '{label} must be text.',
            'email'       => 'Enter a valid email address.',
            'phone'       => 'Enter a valid phone number (10 to 15 digits).',
            'min'         => '{label} must be at least {param} characters.',
            'max'         => '{label} must not exceed {param} characters.',
            'between'     => '{label} must be between {param} characters.',
            'integer'     => '{label} must be a whole number.',
            'numeric'     => '{label} must be a number.',
            'gte'         => '{label} must be {param} or more.',
            'lte'         => '{label} must be {param} or less.',
            'min_digits'  => '{label} is too short.',
            'max_digits'  => '{label} is too long.',
            'date'        => 'Enter a valid date (YYYY-MM-DD).',
            'after_or_equal_today' => '{label} cannot be in the past.',
            'time'        => 'Enter a valid time (HH:MM).',
            'in'          => '{label} contains an invalid option.',
            'in_list'     => 'Choose one of the available options.',
            'accepted'    => 'Please accept the {label}.',
            'regex'       => '{label} contains invalid characters.',
            'no_links'    => '{label} must not contain links or email addresses.',
            'no_html'     => '{label} must not contain HTML markup.',
            'url'         => 'Enter a valid URL.',
            'same'        => '{label} does not match.',
            'alpha_num'   => '{label} may only contain letters, numbers, spaces, dot, dash and apostrophe.',
            'utf8'        => '{label} contains unsupported characters.',
        ];
    }

    /** @param string[] $parameters */
    private function runRule(string $name, mixed $value, array $parameters, string $label, string $field): ?string
    {
        $messages = $this->messages();
        $string   = is_scalar($value) ? (string) $value : '';
        $param    = $parameters[0] ?? '';
        $template = $messages[$name] ?? '{label} is invalid.';

        switch ($name) {
            case 'required':
            case 'filled':
                return $this->isFilled($value) ? null : $this->format($template, $label, $param);

            case 'string':
                return is_scalar($value) ? null : $this->format($template, $label, $param);

            case 'utf8':
                return mb_check_encoding($string, 'UTF-8') ? null : $this->format($template, $label, $param);

            case 'email':
                if (filter_var($string, FILTER_VALIDATE_EMAIL) === false) {
                    return $this->format($template, $label, $param);
                }
                $domain = strtolower((string) substr(strrchr($string, '@') ?: '@', 1));
                if (!str_contains($domain, '.') || in_array($domain, self::disposableDomains(), true)) {
                    return $this->format($template, $label, $param);
                }

                return null;

            case 'phone':
                $digits = preg_replace('/\D+/', '', $string) ?? '';
                if (strlen($digits) < 10 || strlen($digits) > 15) {
                    return $this->format($template, $label, $param);
                }
                $blocked = ['9999999999', '1111111111', '0000000000', '1234567890'];
                if (in_array($digits, $blocked, true)) {
                    return $this->format($template, $label, $param);
                }

                return null;

            case 'min':
                return mb_strlen($string) >= (int) $param ? null : $this->format($template, $label, $param);

            case 'max':
                return mb_strlen($string) <= (int) $param ? null : $this->format($template, $label, $param);

            case 'between':
                $parts = array_map('intval', $parameters);
                $min   = $parts[0] ?? 0;
                $max   = $parts[1] ?? PHP_INT_MAX;
                $len   = mb_strlen($string);

                return ($len >= $min && $len <= $max) ? null : $this->format($template, $label, $param);

            case 'integer':
                return preg_match('/^-?\d+$/', $string) === 1 ? null : $this->format($template, $label, $param);

            case 'numeric':
                return preg_match('/^-?\d+(\.\d+)?$/', $string) === 1 ? null : $this->format($template, $label, $param);

            case 'gte':
                return is_numeric($string) && (float) $string >= (float) $param
                    ? null : $this->format($template, $label, $param);

            case 'lte':
                return is_numeric($string) && (float) $string <= (float) $param
                    ? null : $this->format($template, $label, $param);

            case 'min_digits':
                return (strlen(preg_replace('/\D+/', '', $string) ?? '')) >= (int) $param
                    ? null : $this->format($template, $label, $param);

            case 'max_digits':
                return (strlen(preg_replace('/\D+/', '', $string) ?? '')) <= (int) $param
                    ? null : $this->format($template, $label, $param);

            case 'date':
                $date = \DateTimeImmutable::createFromFormat('!Y-m-d', $string);
                if ($date === false || $date->format('Y-m-d') !== $string) {
                    return $this->format($template, $label, $param);
                }

                return null;

            case 'after_or_equal_today':
                $date = \DateTimeImmutable::createFromFormat('!Y-m-d', $string);
                if ($date === false) {
                    return $this->format($messages['date'], $label, '');
                }
                $today = new \DateTimeImmutable('today');

                return $date >= $today ? null : $this->format($template, $label, $param);

            case 'time':
                return preg_match('/^([01]\d|2[0-3]):[0-5]\d$/', $string) === 1
                    ? null : $this->format($template, $label, $param);

            case 'in':
                return in_array($string, $parameters, true) ? null : $this->format($template, $label, $param);

            case 'in_list':
                $allowed = (array) Config::get('booking.' . $field . '_options', []);
                if ($allowed === []) {
                    return null;
                }

                return in_array($string, array_map('strval', $allowed), true)
                    ? null : $this->format($template, $label, $param);

            case 'accepted':
                $accepted = in_array(strtolower(trim($string)), ['1', 'true', 'yes', 'on', 'accepted'], true);

                return $accepted ? null : $this->format($template, $label, $param);

            case 'regex':
                if ($param === '') {
                    return $this->format($template, $label, $param);
                }
                $ok = @preg_match($param, $string);

                return $ok === 1 ? null : $this->format($template, $label, $param);

            case 'url':
                return filter_var($string, FILTER_VALIDATE_URL) !== false
                    ? null : $this->format($template, $label, $param);

            case 'alpha_num':
                return preg_match("/^[A-Za-z0-9][A-Za-z0-9 .,'\-()\/\. ]*$/u", $string) === 1
                    ? null : $this->format($template, $label, $param);

            case 'no_html':
                return !preg_match('/<\s*\/?\s*[a-z][^>]*>/i', $string)
                    ? null : $this->format($template, $label, $param);

            case 'no_links':
                $links = self::countLinks($string);

                return $links <= (int) Config::get('forms.max_links_allowed', 1)
                    ? null : $this->format($template, $label, $param);

            case 'same':
                $other = (string) ($this->data[$param] ?? '');
                $ok    = hash_equals($other, $string);

                return $ok ? null : $this->format($template, $label, $param);

            case 'csrf':
                return Session::verifyCsrf(is_string($value) ? $value : null)
                    ? null
                    : 'Your session expired. Please refresh the page and try again.';

            default:
                return null;
        }
    }

    private function format(string $template, string $label, string $param): string
    {
        return str_replace(['{label}', '{param}'], [$label, $param], $template);
    }

    /** @param array<int,string> $ruleList */
    private function clean(mixed $value, array $ruleList): mixed
    {
        if (!is_string($value)) {
            return $value;
        }

        $value = str_replace(["\0", "\r"], ['', ''], $value);
        $value = trim(preg_replace('/[ \t]+/u', ' ', $value) ?? $value);

        foreach ($ruleList as $rule) {
            if (str_starts_with((string) $rule, 'max:')) {
                $value = mb_substr($value, 0, (int) substr((string) $rule, 4));
            }
            if ((string) $rule === 'email') {
                $value = mb_strtolower($value);
            }
        }

        return $value;
    }

    public function passes(): bool
    {
        return $this->errors === [];
    }

    public function fails(): bool
    {
        return !$this->passes();
    }

    /** @return array<string,string> */
    public function errors(): array
    {
        return $this->errors;
    }

    public function firstError(): string
    {
        foreach ($this->errors as $error) {
            return $error;
        }

        return 'The given data was invalid.';
    }

    /** @return array<string,mixed> */
    public function validated(): array
    {
        return $this->valid;
    }

    public function throw(): void
    {
        if ($this->fails()) {
            throw new ValidationException($this->errors, $this->data);
        }
    }

    public static function countLinks(string $text): int
    {
        $matches = 0;
        $count   = preg_match_all('#(?:https?://|www\.)[^\s<]+#i', $text);
        $matches += is_int($count) ? $count : 0;

        $emails = preg_match_all('/[A-Z0-9._%+-]+@[A-Z0-9.-]+\.[A-Z]{2,}/i', $text);
        $matches += is_int($emails) ? $emails : 0;

        return $matches;
    }

    /** @return string[] */
    public static function disposableDomains(): array
    {
        return [
            '10minutemail.com', 'tempmail.com', 'temp-mail.org', 'guerrillamail.com',
            'guerrillamail.info', 'mailinator.com', 'yopmail.com', 'trashmail.com',
            'sharklasers.com', 'grr.la', 'dispostable.com', 'maildrop.cc',
            'getnada.com', 'spamgourmet.com', 'throwawaymail.com', 'fakeinbox.com',
            'mailnesia.com', 'moakt.com', 'mytemp.email', 'tempinbox.com',
        ];
    }
}
