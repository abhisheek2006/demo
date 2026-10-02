<?php
declare(strict_types=1);

namespace App\Core\Exceptions;

/** Raised by the Validator; carries per-field messages and old input. */
final class ValidationException extends AppException
{
    /** @param array<string,string> $errors */
    public function __construct(private array $errors, private array $old = [], string $message = 'The given data was invalid.')
    {
        parent::__construct($message);
    }

    /** @return array<string,string> */
    public function errors(): array
    {
        return $this->errors;
    }

    /** @return array<string,mixed> */
    public function old(): array
    {
        return $this->old;
    }

    public function first(): string
    {
        foreach ($this->errors as $error) {
            return $error;
        }

        return $this->getMessage();
    }
}
