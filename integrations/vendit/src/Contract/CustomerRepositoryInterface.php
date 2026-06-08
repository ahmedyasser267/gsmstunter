<?php
declare(strict_types=1);

namespace Integrations\Vendit\Contract;

interface CustomerRepositoryInterface
{
    public function findIdByCustomerNumber(string $customerNumber): ?int;

    /** @param array<string, mixed> $data */
    public function upsertCustomer(array $data): int;

    public function replaceGroups(int $customerId, array $groups): void;

    public function replaceAddresses(int $customerId, array $addresses): void;

    /** @return list<array<string, mixed>> */
    public function listForExport(?string $since = null): array;

    public function markExported(int $customerId, string $fileName): void;
}
