<?php
declare(strict_types=1);

namespace App\Core;

use App\Core\Exceptions\DatabaseException;
use App\Core\Exceptions\DatabaseUnavailableException;
use PDO;
use PDOStatement;
use Throwable;

/**
 * Thin PDO wrapper. Every query goes through prepared statements; the class
 * degrades gracefully (isAvailable() === false) when MySQL is down so the
 * public site keeps working and submissions fall back to JSONL files.
 */
final class Database
{
    private static ?self $instance = null;

    private ?PDO $pdo = null;

    private bool $attempted = false;

    private ?string $lastError = null;

    private int $queryCount = 0;

    private function __construct()
    {
    }

    public static function instance(): self
    {
        return self::$instance ??= new self();
    }

    public static function reset(): void
    {
        self::$instance = null;
    }

    public function pdo(): PDO
    {
        if ($this->pdo instanceof PDO) {
            return $this->pdo;
        }

        if ($this->attempted && $this->lastError !== null) {
            throw new DatabaseUnavailableException($this->lastError);
        }

        $this->attempted = true;

        $host    = (string) Config::get('db.host', 'localhost');
        $port    = (int) Config::get('db.port', 3306);
        $name    = (string) Config::get('db.database', '');
        $user    = (string) Config::get('db.username', '');
        $pass    = (string) Config::get('db.password', '');
        $charset = (string) Config::get('db.charset', 'utf8mb4');
        $socket  = (string) Config::get('db.socket', '');

        if ($name === '' || $user === '') {
            $this->lastError = 'Database is not configured. Set DB_DATABASE and DB_USERNAME in .env.';

            throw new DatabaseUnavailableException($this->lastError);
        }

        $dsn = $socket !== ''
            ? sprintf('mysql:unix_socket=%s;dbname=%s;charset=%s', $socket, $name, $charset)
            : sprintf('mysql:host=%s;port=%d;dbname=%s;charset=%s', $host, $port, $name, $charset);

        try {
            $this->pdo = new PDO($dsn, $user, $pass, [
                PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::ATTR_EMULATE_PREPARES   => false,
                PDO::ATTR_STRINGIFY_FETCHES  => false,
                PDO::ATTR_PERSISTENT         => false,
            ]);
            $this->lastError = null;
        } catch (Throwable $e) {
            $this->lastError = 'Database connection failed: ' . $e->getMessage();
            $this->pdo        = null;
            Logger::error('db.connect', $this->lastError);

            throw new DatabaseUnavailableException($this->lastError, 0, $e);
        }

        return $this->pdo;
    }

    public function isAvailable(): bool
    {
        try {
            $this->pdo();

            return true;
        } catch (Throwable) {
            return false;
        }
    }

    public function lastError(): ?string
    {
        return $this->lastError;
    }

    public function queryCount(): int
    {
        return $this->queryCount;
    }

    public function prepare(string $sql): PDOStatement
    {
        $this->queryCount++;

        $statement = $this->pdo()->prepare($sql);

        if ($statement === false) {
            throw new DatabaseException('Failed to prepare statement.');
        }

        return $statement;
    }

    /** @param array<string|int,mixed> $params */
    public function run(string $sql, array $params = []): PDOStatement
    {
        try {
            $statement = $this->prepare($sql);
            $statement->execute($this->normaliseParams($params));

            return $statement;
        } catch (DatabaseUnavailableException $e) {
            throw $e;
        } catch (Throwable $e) {
            Logger::error('db.query', $e->getMessage(), ['sql' => preg_replace('/\s+/', ' ', $sql) ?? $sql]);

            throw new DatabaseException('Query failed.', 0, $e);
        }
    }

    /**
     * @param array<string|int,mixed> $params
     * @return array<int,array<string,mixed>>
     */
    public function select(string $sql, array $params = []): array
    {
        /** @var array<int,array<string,mixed>> $rows */
        $rows = $this->run($sql, $params)->fetchAll();

        return $rows;
    }

    /**
     * @param array<string|int,mixed> $params
     * @return array<string,mixed>|null
     */
    public function first(string $sql, array $params = []): ?array
    {
        $row = $this->run($sql, $params)->fetch();

        return is_array($row) ? $row : null;
    }

    /** @param array<string|int,mixed> $params */
    public function value(string $sql, array $params = []): mixed
    {
        $value = $this->run($sql, $params)->fetchColumn();

        return $value === false ? null : $value;
    }

    /** @param array<string,mixed> $data */
    public function insert(string $table, array $data): int
    {
        $columns = array_keys($data);
        if ($columns === []) {
            throw new DatabaseException('Insert requires at least one column.');
        }

        $placeholders = array_map(static fn (string $c): string => ':' . $c, $columns);
        $sql = sprintf(
            'INSERT INTO `%s` (`%s`) VALUES (%s)',
            $this->quoteIdent($table),
            implode('`, `', $columns),
            implode(', ', $placeholders)
        );

        $this->run($sql, $data);

        return (int) $this->pdo()->lastInsertId();
    }

    /** @param array<string,mixed> $data */
    public function update(string $table, array $data, string $where, array $whereParams = []): int
    {
        $sets = [];
        $params = [];
        foreach ($data as $column => $value) {
            $sets[] = sprintf('`%s` = :set_%s', $column, $column);
            $params['set_' . $column] = $value;
        }

        $sql = sprintf('UPDATE `%s` SET %s WHERE %s', $this->quoteIdent($table), implode(', ', $sets), $where);
        $statement = $this->run($sql, array_merge($params, $whereParams));

        return $statement->rowCount();
    }

    public function delete(string $table, string $where, array $params = []): int
    {
        $sql = sprintf('DELETE FROM `%s` WHERE %s', $this->quoteIdent($table), $where);

        return $this->run($sql, $params)->rowCount();
    }

    public function transaction(callable $callback): mixed
    {
        $pdo = $this->pdo();
        $pdo->beginTransaction();

        try {
            $result = $callback($this);
            $pdo->commit();

            return $result;
        } catch (Throwable $e) {
            if ($pdo->inTransaction()) {
                $pdo->rollBack();
            }
            throw $e;
        }
    }

    public function tableExists(string $table): bool
    {
        try {
            $found = $this->value(
                'SELECT COUNT(*) FROM information_schema.tables WHERE table_schema = DATABASE() AND table_name = :t',
                ['t' => $table]
            );

            return (int) $found > 0;
        } catch (Throwable) {
            return false;
        }
    }

    public function columnExists(string $table, string $column): bool
    {
        try {
            $found = $this->value(
                'SELECT COUNT(*) FROM information_schema.columns WHERE table_schema = DATABASE() AND table_name = :t AND column_name = :c',
                ['t' => $table, 'c' => $column]
            );

            return (int) $found > 0;
        } catch (Throwable) {
            return false;
        }
    }

    private function normaliseParams(array $params): array
    {
        $out = [];
        foreach ($params as $key => $value) {
            if (is_bool($value)) {
                $value = $value ? 1 : 0;
            } elseif ($value instanceof \DateTimeInterface) {
                $value = $value->format('Y-m-d H:i:s');
            } elseif (is_array($value) || is_object($value)) {
                $value = json_encode($value, JSON_UNESCAPED_UNICODE);
            }
            $out[$key] = $value;
        }

        return $out;
    }

    private function quoteIdent(string $identifier): string
    {
        return str_replace('`', '', $identifier);
    }
}
