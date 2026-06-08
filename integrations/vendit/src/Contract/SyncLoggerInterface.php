<?php
declare(strict_types=1);

namespace Integrations\Vendit\Contract;

interface SyncLoggerInterface
{
    public function start(string $syncType, string $direction, ?string $fileName = null, ?string $filePath = null): int;

    public function setValidation(string $status, ?array $errors = null): void;

    public function complete(string $status, int $processed = 0, int $failed = 0, ?string $message = null): void;

    public function error(string $message): void;

    public function info(string $message): void;

    /** @return list<array<string, mixed>> */
    public function recentLogs(int $limit = 100): array;

    public function lastSyncDate(?string $syncType = null): ?string;
}
