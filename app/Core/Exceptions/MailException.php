<?php
declare(strict_types=1);

namespace App\Core\Exceptions;

/** SMTP delivery failure. Submissions are never discarded because of this. */
final class MailException extends AppException
{
    /** @param string[] $transcript */
    public function __construct(string $message, private array $transcript = [])
    {
        parent::__construct($message);
    }

    /** @return string[] */
    public function transcript(): array
    {
        return $this->transcript;
    }
}
