<?php
declare(strict_types=1);

namespace App\Core\Exceptions;

use PDOException;

/** Database connection / query failure. */
final class DatabaseException extends AppException
{
    public function __construct(string $message, private ?PDOException $previous = null)
    {
        parent::__construct($message, 0, $previous);
    }

    public function pdoException(): ?PDOException
    {
        return $this->previous;
    }
}
