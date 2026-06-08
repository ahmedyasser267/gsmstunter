<?php
declare(strict_types=1);

namespace Integrations\Vendit\Contract;

interface OrderRepositoryInterface
{
    public function findIdByOrderNumber(string $orderNumber): ?int;

    /** @param array<string, mixed> $order */
    public function upsertOrder(array $order): int;

    /** @param list<array<string, mixed>> $items */
    public function replaceItems(int $orderId, array $items): void;

    /** @return list<array<string, mixed>> */
    public function listPendingExport(int $limit = 50): array;

    public function markExported(int $orderId, string $fileName): void;

    public function markExportFailed(int $orderId, string $message): void;
}
