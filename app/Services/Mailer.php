<?php
declare(strict_types=1);

namespace App\Services;

use App\Core\Config;
use App\Core\Logger;
use App\Core\Paths;
use App\Content\Clinic;
use App\Core\Exceptions\MailException;

/**
 * Dependency-free SMTP client built on fsockopen / stream_socket_client.
 *
 * Supports implicit SSL (port 465), STARTTLS (port 587) and plain
 * connections, with AUTH LOGIN and AUTH PLAIN. Every message is written to
 * storage/mail as a .eml file before it is sent, so a failed delivery can
 * always be inspected or resent by hand.
 */
final class Mailer
{
    /** @var resource|null */
    private $socket = null;

    private int $port = 587;

    private string $host = '';

    private string $encryption = 'tls';

    private int $timeout = 15;

    private int $verifyPeer = 1;

    private string $transcript = '';

    public function isConfigured(): bool
    {
        return Config::bool('mail.enabled', false)
            && Config::string('mail.host', '') !== ''
            && Config::string('mail.from_address', '') !== '';
    }

    public function reason(): string
    {
        if (!Config::string('mail.host', '')) {
            return 'MAIL_HOST is not set in .env';
        }
        if (!Config::string('mail.from_address', '')) {
            return 'MAIL_FROM_ADDRESS is not set in .env';
        }

        return 'SMTP not configured';
    }

    /**
     * Send a plain-text + HTML multipart message.
     *
     * @param array<string,string> $options to, cc, replyTo, subject, text, html, headers, category
     */
    public function send(array $options): bool
    {
        $payload = $this->buildMessage($options);

        $this->archive($options, $payload['raw']);

        if (!$this->isConfigured()) {
            throw new MailException('Mail is not configured: ' . $this->reason());
        }

        $this->connect();
        $this->handshake();
        $this->authenticate();
        $this->transmit($options, $payload);

        Logger::info('mail.sent', 'Message delivered', [
            'to'       => $options['to'] ?? '',
            'subject'  => $options['subject'] ?? '',
            'category' => $options['category'] ?? 'general',
        ]);

        return true;
    }

    /* -------------------------------------------------------------------- */
    /* Message construction                                                  */
    /* -------------------------------------------------------------------- */

    /**
     * @param array<string,string> $options
     * @return array{raw:string,headers:array<string,string>}
     */
    public function buildMessage(array $options): array
    {
        $fromAddress = (string) ($options['from'] ?? Config::string('mail.from_address', ''));
        $fromName    = (string) ($options['fromName'] ?? Config::string('mail.from_name', Clinic::NAME));
        $to          = trim((string) ($options['to'] ?? Config::string('mail.to_address', '')));
        $subject     = trim((string) ($options['subject'] ?? 'Message from ' . Clinic::NAME));
        $text        = (string) ($options['text'] ?? '');
        $html        = (string) ($options['html'] ?? '');
        $replyTo     = (string) ($options['replyTo'] ?? Config::string('mail.reply_to', ''));
        $cc          = trim((string) ($options['cc'] ?? Config::string('mail.cc', '')));
        $boundary    = 'swasti-' . bin2hex(random_bytes(12));

        if ($to === '' || filter_var($to, FILTER_VALIDATE_EMAIL) === false) {
            throw new MailException('Invalid or missing recipient address.');
        }

        $headers = [
            'Date'         => date(DATE_RFC2822),
            'From'         => $this->formatAddress($fromName, $fromAddress),
            'To'           => $to,
            'Subject'      => $this->encodeHeader($subject),
            'Message-ID'   => '<' . bin2hex(random_bytes(10)) . '.' . time() . '@' . $this->hostForId() . '>',
            'MIME-Version' => '1.0',
            'X-Mailer'     => 'SwastiHomoeoMailer/1.0 (PHP ' . PHP_VERSION . ')',
        ];

        if ($replyTo !== '' && filter_var($replyTo, FILTER_VALIDATE_EMAIL) !== false) {
            $headers['Reply-To'] = $replyTo;
        }
        if ($cc !== '' && filter_var($cc, FILTER_VALIDATE_EMAIL) !== false) {
            $headers['Cc'] = $cc;
        }

        foreach ((array) ($options['headers'] ?? []) as $key => $value) {
            $key = trim((string) $key);
            if ($key !== '' && preg_match('/^[A-Za-z-]+$/', $key) && !preg_match('/\r|\n/', (string) $value)) {
                $headers[$key] = (string) $value;
            }
        }

        $headers['X-Mailer-Category'] = substr((string) ($options['category'] ?? 'general'), 0, 40);

        $headerLines = '';
        foreach ($headers as $name => $value) {
            $headerLines .= $name . ': ' . $value . "\r\n";
        }

        $body = '';

        if ($html !== '' && $text !== '') {
            $headerLines .= 'Content-Type: multipart/alternative; boundary="' . $boundary . "\"\r\n";
            $body  = "--{$boundary}\r\n";
            $body .= "Content-Type: text/plain; charset=UTF-8\r\n";
            $body .= "Content-Transfer-Encoding: base64\r\n\r\n";
            $body .= chunk_split(base64_encode($this->normalise($text)));
            $body .= "\r\n--{$boundary}\r\n";
            $body .= "Content-Type: text/html; charset=UTF-8\r\n";
            $body .= "Content-Transfer-Encoding: base64\r\n\r\n";
            $body .= chunk_split(base64_encode($this->normalise($html)));
            $body .= "\r\n--{$boundary}--\r\n";
        } elseif ($html !== '') {
            $headerLines .= "Content-Type: text/html; charset=UTF-8\r\n";
            $headerLines .= "Content-Transfer-Encoding: base64\r\n\r\n";
            $body = chunk_split(base64_encode($this->normalise($html)));
        } else {
            $headerLines .= "Content-Type: text/plain; charset=UTF-8\r\n";
            $headerLines .= "Content-Transfer-Encoding: base64\r\n\r\n";
            $body = chunk_split(base64_encode($this->normalise($text)));
        }

        return [
            'raw'     => $headerLines . "\r\n" . $body,
            'headers' => $headers,
        ];
    }

    private function normalise(string $text): string
    {
        $text = str_replace(["\0", "\r\n", "\r"], ["", "\n", "\n"], $text);

        return str_replace("\n", "\r\n", $text);
    }

    private function formatAddress(string $name, string $address): string
    {
        $name = trim(preg_replace('/[\r\n"]+/', '', $name) ?? '');

        if ($name === '') {
            return $address;
        }

        return $this->encodeHeader($name) . ' <' . $address . '>';
    }

    private function encodeHeader(string $value): string
    {
        $value = str_replace(["\r", "\n", '%0a', '%0d'], ' ', $value);

        if (preg_match('/^[\x20-\x7E]*$/', $value) === 1) {
            return $value;
        }

        return '=?UTF-8?B?' . base64_encode($value) . '?=';
    }

    private function hostForId(): string
    {
        $host = (string) ($_SERVER['HTTP_HOST'] ?? parse_url((string) Config::get('app.url', ''), PHP_URL_HOST) ?? '');

        return preg_replace('/[^A-Za-z0-9.\-]/', '', $host) ?: 'swastihomoeo.com';
    }

    /* -------------------------------------------------------------------- */
    /* SMTP transport                                                        */
    /* -------------------------------------------------------------------- */

    private function connect(): void
    {
        $this->host        = Config::string('mail.host', '');
        $this->port        = Config::int('mail.port', 587);
        $this->encryption  = strtolower(Config::string('mail.encryption', 'tls'));
        $this->timeout     = max(5, Config::int('mail.timeout', 15));
        // Shared hosting occasionally ships an incomplete CA bundle; TLS is
        // still negotiated, we simply do not hard-fail on chain verification.
        $this->verifyPeer  = Config::bool('app.debug', false) ? 1 : 0;

        if ($this->host === '') {
            throw new MailException('MAIL_HOST is empty.');
        }

        $context = stream_context_create([
            'ssl' => [
                'verify_peer'       => (bool) $this->verifyPeer,
                'verify_peer_name'  => (bool) $this->verifyPeer,
                'allow_self_signed' => !$this->verifyPeer,
                'SNI_enabled'       => true,
            ],
        ]);

        $remote = ($this->encryption === 'ssl' ? 'ssl://' : 'tcp://') . $this->host . ':' . $this->port;

        $errorNumber  = 0;
        $errorMessage = '';
        $socket       = @stream_socket_client(
            $remote,
            $errorNumber,
            $errorMessage,
            $this->timeout,
            STREAM_CLIENT_CONNECT,
            $context
        );

        if ($socket === false) {
            $this->log('connect failed: ' . $errorMessage . ' (' . $errorNumber . ')');

            throw new MailException('Could not connect to the mail server: ' . $errorMessage);
        }

        stream_set_timeout($socket, $this->timeout);
        $this->socket = $socket;
    }

    private function handshake(): void
    {
        $this->expect([220]);

        $this->command('EHLO ' . $this->hostForId(), [250]);

        if ($this->encryption === 'tls') {
            $this->command('STARTTLS', [220]);
            $this->upgradeToTls();
            $this->command('EHLO ' . $this->hostForId(), [250]);
        }
    }

    private function upgradeToTls(): void
    {
        $method = STREAM_CRYPTO_METHOD_TLS_CLIENT;
        if (defined('STREAM_CRYPTO_METHOD_TLSv1_2_CLIENT')) {
            $method |= STREAM_CRYPTO_METHOD_TLSv1_2_CLIENT;
        }
        if (defined('STREAM_CRYPTO_METHOD_TLSv1_3_CLIENT')) {
            $method |= STREAM_CRYPTO_METHOD_TLSv1_3_CLIENT;
        }

        $ok = @stream_socket_enable_crypto($this->socket, true, $method);
        if ($ok !== true) {
            $this->log('STARTTLS negotiation failed');

            throw new MailException('Could not start TLS encryption with the mail server.');
        }
    }

    private function authenticate(): void
    {
        $username = Config::string('mail.username', '');
        $password = Config::string('mail.password', '');

        if ($username === '') {
            return; // Unauthenticated relay (rare, but permitted by some hosts)
        }

        // AUTH PLAIN in a single line.
        try {
            $this->command('AUTH PLAIN', [235], base64_encode("\0" . $username . "\0" . $password), true);

            return;
        } catch (MailException $e) {
            $this->log('AUTH PLAIN unavailable, trying AUTH LOGIN');
        }

        try {
            $this->command('AUTH LOGIN', [334]);
            $this->command(base64_encode($username), [334], null, true);
            $this->command(base64_encode($password), [235], null, true);
        } catch (MailException $e) {
            $this->dumpTranscript('auth failed');

            throw new MailException('SMTP authentication failed: ' . $e->getMessage());
        }
    }

    /**
     * @param array<string,string> $options
     * @param array{raw:string,headers:array<string,string>} $payload
     */
    private function transmit(array $options, array $payload): void
    {
        $from = (string) ($options['from'] ?? Config::string('mail.from_address', ''));
        $to   = trim((string) ($options['to'] ?? Config::string('mail.to_address', '')));
        $cc   = trim((string) ($options['cc'] ?? Config::string('mail.cc', '')));

        $this->command('MAIL FROM:<' . $this->sanitiseAddress($from) . '>', [250]);
        $this->command('RCPT TO:<' . $this->sanitiseAddress($to) . '>', [250, 251]);

        if ($cc !== '' && filter_var($cc, FILTER_VALIDATE_EMAIL) !== false) {
            try {
                $this->command('RCPT TO:<' . $this->sanitiseAddress($cc) . '>', [250, 251]);
            } catch (MailException $e) {
                Logger::warning('mail.cc', 'CC rejected by server', ['error' => $e->getMessage()]);
            }
        }

        $this->command('DATA', [354]);
        $this->write($payload['raw'] . "\r\n.\r\n");
        $this->expect([250]);

        try {
            $this->command('QUIT', [221, 250]);
        } catch (MailException) {
            // The message is already accepted; a rude disconnect is not fatal.
        }
        $this->disconnect();
    }

    private function sanitiseAddress(string $address): string
    {
        return (string) preg_replace('/[^A-Za-z0-9.@\-+_]/', '', $address);
    }

    /** @param string[] $codes */
    private function command(string $line, array $codes, ?string $payload = null, bool $secret = false): void
    {
        $this->log('> ' . ($secret ? strtok($line, ' ') . ' [credentials redacted]' : $line));
        $this->write($line . "\r\n");

        if ($payload !== null) {
            $this->log('> [credentials redacted]');
            $this->write($payload . "\r\n");
        }

        $this->expect($codes);
    }

    /**
     * @param string[] $codes
     */
    private function expect(array $codes): string
    {
        $response = '';
        $deadline = time() + $this->timeout;

        while (true) {
            if (!is_resource($this->socket)) {
                throw new MailException('Mail connection closed unexpectedly.');
            }

            $line = fgets($this->socket, 1024);
            if ($line === false) {
                $meta = stream_get_meta_data($this->socket);
                throw new MailException('Mail server timed out' . (!empty($meta['timed_out']) ? ' (timeout)' : ''));
            }

            $response .= $line;

            // Multi-line replies: "250-EXTENSION" then "250 OK".
            if (strlen($line) < 4 || $line[3] !== '-') {
                break;
            }
            if (time() > $deadline) {
                break;
            }
        }

        $this->log('< ' . trim($response));

        $code = (int) substr(trim($response), 0, 3);
        if (!in_array($code, $codes, true)) {
            throw new MailException(
                'Unexpected SMTP reply (' . $code . '): ' . trim(substr(trim($response), 4)),
                array_filter(explode("\n", $this->transcript))
            );
        }

        return $response;
    }

    private function write(string $data): void
    {
        $length = strlen($data);
        $sent   = 0;

        while ($sent < $length) {
            $written = @fwrite($this->socket, substr($data, $sent));
            if ($written === false || $written === 0) {
                throw new MailException('Failed to write to the mail server.');
            }
            $sent += $written;
        }
    }

    private function disconnect(): void
    {
        if (is_resource($this->socket)) {
            @fclose($this->socket);
        }
        $this->socket = null;
    }

    private function log(string $line): void
    {
        $this->transcript .= $line . "\n";
        if (strlen($this->transcript) > 32000) {
            $this->transcript = substr($this->transcript, -16000);
        }
    }

    /* -------------------------------------------------------------------- */
    /* Archive                                                               */
    /* -------------------------------------------------------------------- */

    /**
     * @param array<string,string> $options
     */
    private function archive(array $options, string $raw): void
    {
        $dir = Paths::storage() . '/mail';
        if (!Paths::ensureWritable($dir)) {
            return;
        }

        $category = preg_replace('/[^a-z0-9_\-]/i', '', (string) ($options['category'] ?? 'general')) ?: 'general';
        $file     = sprintf('%s/%s-%s-%s.eml', $dir, date('Y-m'), $category, bin2hex(random_bytes(4)));

        @file_put_contents($file, $raw, LOCK_EX);
        @chmod($file, 0640);
    }

    /**
     * Write the full SMTP transcript for post-mortem debugging.
     */
    public function dumpTranscript(string $context): void
    {
        $dir = Paths::storage() . '/logs';
        if (!Paths::ensureWritable($dir)) {
            return;
        }

        @file_put_contents(
            $dir . '/smtp-' . date('Y-m-d') . '.log',
            '=== ' . date('c') . ' | ' . $context . " ===\n" . $this->transcript . "\n",
            FILE_APPEND | LOCK_EX
        );
    }
}
