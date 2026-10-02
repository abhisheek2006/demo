<?php
declare(strict_types=1);

namespace App\Core\Exceptions;

use App\Core\Response;

/** Errors that map directly onto an HTTP status code. */
final class HttpException extends AppException
{
    /** @param array<string,string> $headers */
    public function __construct(
        private int $statusCode = 500,
        string $message = 'Error',
        private array $headers = []
    ) {
        parent::__construct($message, $statusCode);
    }

    public function statusCode(): int
    {
        return $this->statusCode;
    }

    /** @return array<string,string> */
    public function headers(): array
    {
        return $this->headers;
    }

    public function toResponse(): Response
    {
        $response = new Response('', $this->statusCode);
        foreach ($this->headers as $name => $value) {
            $response->setHeader($name, $value);
        }

        return $response;
    }
}
