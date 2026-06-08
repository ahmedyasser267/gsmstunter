<?php
declare(strict_types=1);

namespace Integrations\Vendit\Infrastructure;

use PDO;
use PDOException;
use RuntimeException;

final class PdoConnection
{
    private PDO $pdo;

    private function __construct(PDO $pdo)
    {
        $this->pdo = $pdo;
    }

    /** @param array<string, mixed> $dbConfig */
    public static function fromConfig(array $dbConfig): self
    {
        $host = (string) ($dbConfig['host'] ?? '127.0.0.1');
        $port = (int) ($dbConfig['port'] ?? 3306);
        $name = (string) ($dbConfig['name'] ?? 'gsmstunter');
        $user = (string) ($dbConfig['user'] ?? 'root');
        $pass = (string) ($dbConfig['password'] ?? '');
        $charset = (string) ($dbConfig['charset'] ?? 'utf8mb4');
        $dsn = sprintf('mysql:host=%s;port=%d;dbname=%s;charset=%s', $host, $port, $name, $charset);

        try {
            $pdo = new PDO($dsn, $user, $pass, [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::ATTR_EMULATE_PREPARES => false,
            ]);
        } catch (PDOException $e) {
            throw new RuntimeException('Database connection failed: ' . $e->getMessage(), 0, $e);
        }

        return new self($pdo);
    }

    public function pdo(): PDO
    {
        return $this->pdo;
    }

    public function beginTransaction(): void
    {
        if (!$this->pdo->inTransaction()) {
            $this->pdo->beginTransaction();
        }
    }

    public function commit(): void
    {
        if ($this->pdo->inTransaction()) {
            $this->pdo->commit();
        }
    }

    public function rollBack(): void
    {
        if ($this->pdo->inTransaction()) {
            $this->pdo->rollBack();
        }
    }

    /** @param array<int|string, mixed> $params */
    public function query(string $sql, array $params = []): \PDOStatement
    {
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($params);
        return $stmt;
    }

    /** @param array<int|string, mixed> $params */
    public function fetchOne(string $sql, array $params = []): array|false
    {
        return $this->query($sql, $params)->fetch();
    }

    /** @param array<int|string, mixed> $params */
    public function fetchAll(string $sql, array $params = []): array
    {
        return $this->query($sql, $params)->fetchAll();
    }

    public function lastInsertId(): string
    {
        return $this->pdo->lastInsertId();
    }
}
