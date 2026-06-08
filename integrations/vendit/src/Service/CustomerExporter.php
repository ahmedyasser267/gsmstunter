<?php
declare(strict_types=1);

namespace Integrations\Vendit\Service;

use DateTimeImmutable;
use Integrations\Vendit\Contract\CustomerRepositoryInterface;
use Integrations\Vendit\Contract\FileStorageInterface;
use Integrations\Vendit\Contract\SyncLoggerInterface;
use Integrations\Vendit\Dto\ExportResult;
use Integrations\Vendit\Xml\XmlToolkit;

final class CustomerExporter
{
    public function __construct(
        private readonly FileStorageInterface $fileStorage,
        private readonly XmlToolkit $xmlToolkit,
        private readonly CustomerRepositoryInterface $repository,
        private readonly SyncLoggerInterface $logger,
    ) {
    }

    public function exportCustomers(?string $since = null): ExportResult
    {
        $this->logger->start('customer_export', 'export');

        $customers = $this->repository->listForExport($since);
        if ($customers === []) {
            $this->logger->complete('success', 0, 0, 'No customers found for export.');
            return new ExportResult(0, 0, [], 'success', 'No customers found for export.');
        }

        $fileName = sprintf('customer-%s.xml', (new DateTimeImmutable())->format('Ymd-His'));

        try {
            $xml = $this->xmlToolkit->buildCustomerExport($customers);
            $path = $this->fileStorage->writeExportFile($fileName, $xml);
            foreach ($customers as $customer) {
                if (!isset($customer['id'])) {
                    continue;
                }
                $this->repository->markExported((int) $customer['id'], basename($path));
            }
            $this->logger->complete('success', count($customers), 0, 'Customer export completed.');

            return new ExportResult(count($customers), 0, [basename($path)], 'success');
        } catch (\Throwable $e) {
            $this->logger->error($e->getMessage());
            $this->logger->complete('failed', 0, count($customers), 'Customer export failed.');

            return new ExportResult(0, count($customers), [], 'failed', $e->getMessage());
        }
    }
}
